<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Account extends Cl_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Account_model');
        $this->load->model('Account_transaction_model');
        $this->load->model('Authentication_model');
        $this->load->library('form_validation');
        
        if (!$this->session->userdata('user_id')) {
            redirect('Authentication/index');
        }

        // Crear cuenta Caja Cofre si no existe
        $company_id = $this->session->userdata('company_id');
        $user_id = $this->session->userdata('user_id');
        $this->Account_model->createDefaultAccountIfNotExists($company_id, $user_id);
    }

    /**
     * Listado de movimientos
     */
    public function accountTransactions() {
        $company_id = $this->session->userdata('company_id');
        $data = array();

        // Filtros
        $filters = array(
            'date_from' => $this->input->get('date_from'),
            'date_to' => $this->input->get('date_to'),
            'account_id' => $this->input->get('account_id'),
            'transaction_type' => $this->input->get('transaction_type')
        );

        // Paginación
        $limit = 10000; // registros por página
        $page = $this->input->get('page') ? (int)$this->input->get('page') : 1;
        $offset = ($page - 1) * $limit;

        // Obtener transacciones paginadas
        $data['transactions'] = $this->Account_transaction_model->getAllTransactionsByCompany($company_id, $filters, $limit, $offset);

        // Obtener total para paginación
        $total_transactions = $this->Account_transaction_model->getTotalTransactionsByCompany($company_id, $filters);
        $total_pages = ceil($total_transactions / $limit);

        // Información de paginación
        $data['pagination'] = array(
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_records' => $total_transactions,
            'limit' => $limit,
            'has_previous' => $page > 1,
            'has_next' => $page < $total_pages,
            'previous_page' => $page > 1 ? $page - 1 : null,
            'next_page' => $page < $total_pages ? $page + 1 : null
        );

        $data['accounts'] = $this->Account_model->getAllAccountsByCompany($company_id);
        $data['filters'] = $filters;

        $data['main_content'] = $this->load->view('account/accountTransactions', $data, TRUE);
        $this->load->view('userHome', $data);
    }
    /**
     * Listado de cuentas
     */
    public function accounts() {
        $company_id = $this->session->userdata('company_id');
        $data = array();
        $data['accounts'] = $this->Account_model->getAllAccountsByCompany($company_id);
        $data['main_content'] = $this->load->view('account/accounts', $data, TRUE);
        $this->load->view('userHome', $data);
    }

    /**
     * Formulario para agregar/editar cuenta
     */
    public function addEditAccount($encrypted_id = NULL) {
        $company_id = $this->session->userdata('company_id');
        $data = array();
        
        if ($encrypted_id) {
            $id = $this->custom->encrypt_decrypt($encrypted_id, 'decrypt');
            $data['account'] = $this->Account_model->getAccountById($id);
            
            if (!$data['account'] || $data['account']->company_id != $company_id) {
                $this->session->set_flashdata('exception', 'Cuenta no encontrada');
                redirect('Account/accounts');
            }
        } else {
            $data['account'] = NULL;
        }

        $data['main_content'] = $this->load->view('account/addEditAccount', $data, TRUE);
        $this->load->view('userHome', $data);
    }

    /**
     * Guardar cuenta (crear o editar)
     */
    public function saveAccount() {
        $company_id = $this->session->userdata('company_id');
        $user_id = $this->session->userdata('user_id');
        $id = $this->input->post('id');

        $this->form_validation->set_rules('account_name', 'Nombre de cuenta', 'required|max_length[255]');
        $this->form_validation->set_rules('account_type', 'Tipo de cuenta', 'required');
        $this->form_validation->set_rules('opening_balance', 'Saldo inicial', 'numeric');

        if ($this->form_validation->run() == TRUE) {
            $data = array(
                'account_name' => $this->input->post('account_name'),
                'account_number' => $this->input->post('account_number'),
                'account_type' => $this->input->post('account_type'),
                'description' => $this->input->post('description'),
                'status' => $this->input->post('status') ? $this->input->post('status') : 'Active',
                'company_id' => $company_id
            );

            if (!$id) {
                // Nueva cuenta
                $data['opening_balance'] = $this->input->post('opening_balance') ? $this->input->post('opening_balance') : 0;
                $data['current_balance'] = $data['opening_balance'];
                $data['user_id'] = $user_id;
                $data['added_date'] = date('Y-m-d H:i:s');
                $data['del_status'] = 'Live';
                $data['is_default'] = 0;
            } else {
                // Editar cuenta - no permitir cambiar saldo inicial ni is_default
                // Solo actualizar información básica
            }

            $result = $this->Account_model->insertOrUpdateAccount($data, $id);

            if ($result) {
                $this->session->set_flashdata('exception', $id ? 'Cuenta actualizada exitosamente' : 'Cuenta creada exitosamente');
            } else {
                $this->session->set_flashdata('exception_err', 'Error al guardar la cuenta');
            }
        } else {
            $this->session->set_flashdata('exception_err', validation_errors());
        }

        redirect('Account/accounts');
    }

    /**
     * Eliminar cuenta
     */
    public function deleteAccount($encrypted_id) {
        $id = $this->custom->encrypt_decrypt($encrypted_id, 'decrypt');
        
        // Verificar si se puede eliminar
        if (!$this->Account_model->canDeleteAccount($id)) {
            $this->session->set_flashdata('exception_err', 'No se puede eliminar esta cuenta. Es cuenta principal o tiene movimientos asociados.');
            redirect('Account/accounts');
        }

        $result = $this->Account_model->deleteAccount($id);

        if ($result) {
            $this->session->set_flashdata('exception', 'Cuenta eliminada exitosamente');
        } else {
            $this->session->set_flashdata('exception_err', 'Error al eliminar la cuenta');
        }

        redirect('Account/accounts');
    }

    /**
     * Obtener cuentas para dropdown (Ajax)
     */
    public function getAccountsDropdown() {
        $company_id = $this->session->userdata('company_id');
        $exclude_id = $this->input->get('exclude_id');
        
        $accounts = $this->Account_model->getAccountsForDropdown($company_id, $exclude_id);
        
        $response = array();
        foreach ($accounts as $account) {
            $balance_formatted = number_format($account->current_balance, 2, '.', ',');
            $response[] = array(
                'id' => $account->id,
                'text' => $account->account_name . ' (' . $account->account_type . ') - Saldo: $' . $balance_formatted
            );
        }

        echo json_encode($response);
    }

    /**
     * Ver detalles de cuenta (modal o página separada)
     */
    public function viewAccountDetails($encrypted_id) {
        $id = $this->custom->encrypt_decrypt($encrypted_id, 'decrypt');
        $company_id = $this->session->userdata('company_id');
        
        $data = array();
        $data['account'] = $this->Account_model->getAccountById($id);
        
        if (!$data['account'] || $data['account']->company_id != $company_id) {
            $this->session->set_flashdata('exception_err', 'Cuenta no encontrada');
            redirect('Account/accounts');
        }

        // Cargar transacciones de la cuenta
        $this->load->model('Account_transaction_model');
        $data['transactions'] = $this->Account_transaction_model->getTransactionsByAccount($id);
        
        $data['main_content'] = $this->load->view('account/viewAccountDetails', $data, TRUE);
        $this->load->view('userHome', $data);
    }

    /**
     * Formulario para agregar movimiento
     */
    public function addAccountTransaction() {
        $company_id = $this->session->userdata('company_id');
        $data = array();
        
        $data['accounts'] = $this->Account_model->getAccountsForDropdown($company_id);
        $data['main_content'] = $this->load->view('account/addAccountTransaction', $data, TRUE);
        $this->load->view('userHome', $data);
    }

    /**
     * Guardar movimiento (Transferencia, Depósito, Retiro)
     */
    public function saveTransaction() {
        $company_id = $this->session->userdata('company_id');
        $user_id = $this->session->userdata('user_id');
        $transaction_type = $this->input->post('transaction_type');

        $this->form_validation->set_rules('transaction_type', 'Tipo de movimiento', 'required');
        $this->form_validation->set_rules('amount', 'Monto', 'required|numeric|greater_than[0]');
        // $this->form_validation->set_rules('transaction_date', 'Fecha', 'required');

        // Validaciones según tipo
        if ($transaction_type == 'Transferencia') {
            $this->form_validation->set_rules('from_account_id', 'Cuenta origen', 'required');
            $this->form_validation->set_rules('to_account_id', 'Cuenta destino', 'required');
            
            // Validar que no sean la misma cuenta
            if ($this->input->post('from_account_id') == $this->input->post('to_account_id')) {
                $this->session->set_flashdata('exception_err', 'La cuenta origen y destino no pueden ser la misma');
                redirect('Account/addAccountTransaction');
                return;
            }
        } elseif ($transaction_type == 'Deposito') {
            $this->form_validation->set_rules('to_account_id', 'Cuenta destino', 'required');
        } elseif ($transaction_type == 'Retiro') {
            $this->form_validation->set_rules('from_account_id', 'Cuenta origen', 'required');
        }

        if ($this->form_validation->run() == TRUE) {
            // Verificar saldo suficiente si es retiro o transferencia
            if ($transaction_type == 'Transferencia' || $transaction_type == 'Retiro') {
                $from_account_id = $this->input->post('from_account_id');
                $amount = $this->input->post('amount');
                $account = $this->Account_model->getAccountById($from_account_id);
                
                if ($account->current_balance < $amount) {
                    $this->session->set_flashdata('exception_err', 'Saldo insuficiente en la cuenta origen');
                    redirect('Account/addAccountTransaction');
                    return;
                }
            }

            $data = array(
                'transaction_type' => $transaction_type,
                'from_account_id' => $this->input->post('from_account_id') ? $this->input->post('from_account_id') : NULL,
                'to_account_id' => $this->input->post('to_account_id') ? $this->input->post('to_account_id') : NULL,
                'amount' => $this->input->post('amount'),
                'note' => $this->input->post('note'),
                'transaction_date' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'company_id' => $company_id,
                'user_id' => $user_id,
                'del_status' => 'Live'
            );

            $result = $this->Account_transaction_model->insertTransaction($data);

            if ($result) {
                $this->session->set_flashdata('exception', 'Movimiento registrado exitosamente');
            } else {
                $this->session->set_flashdata('exception_err', 'Error al registrar el movimiento');
            }
        } else {
            $this->session->set_flashdata('exception_err', validation_errors());
        }

        redirect('Account/accountTransactions');
    }

    /**
     * Eliminar movimiento
     */
    public function deleteTransaction($encrypted_id) {
        $id = $this->custom->encrypt_decrypt($encrypted_id, 'decrypt');
        
        // Verificar que el movimiento no tenga referencia (no fue generado automáticamente)
        $transaction = $this->Account_transaction_model->getTransactionById($id);
        
        if ($transaction && ($transaction->reference_type || $transaction->reference_id)) {
            $this->session->set_flashdata('exception_err', 'No se puede eliminar este movimiento porque está vinculado a un documento del sistema');
            redirect('Account/accountTransactions');
            return;
        }

        $result = $this->Account_transaction_model->deleteTransaction($id);

        if ($result) {
            $this->session->set_flashdata('exception', 'Movimiento eliminado exitosamente');
        } else {
            $this->session->set_flashdata('exception_err', 'Error al eliminar el movimiento');
        }

        redirect('Account/accountTransactions');
    }

    /**
     * Ver detalles de movimiento
     */
    public function viewTransactionDetails($encrypted_id) {
        $id = $this->custom->encrypt_decrypt($encrypted_id, 'decrypt');
        $company_id = $this->session->userdata('company_id');
        
        $data = array();
        $data['transaction'] = $this->Account_transaction_model->getTransactionById($id);
        
        if (!$data['transaction'] || $data['transaction']->company_id != $company_id) {
            $this->session->set_flashdata('exception_err', 'Movimiento no encontrado');
            redirect('Account/accountTransactions');
        }

        // Mostrar vista modal o página separada
        echo json_encode($data['transaction']);
    }

    /**
     * Resumen de movimientos (dashboard/widgets)
     */
    public function getTransactionsSummary() {
        $company_id = $this->session->userdata('company_id');
        $date_from = $this->input->get('date_from');
        $date_to = $this->input->get('date_to');
        $account_id = $this->input->get('account_id');

        $summary = $this->Account_transaction_model->getTransactionSummary($company_id, $date_from, $date_to, $account_id);
        
        echo json_encode($summary);
    }

    /**
     * Exportar movimientos a Excel/PDF
     */
    public function exportTransactions() {
        // Por implementar según necesidades
        $company_id = $this->session->userdata('company_id');
        $filters = array(
            'date_from' => $this->input->get('date_from'),
            'date_to' => $this->input->get('date_to'),
            'account_id' => $this->input->get('account_id'),
            'transaction_type' => $this->input->get('transaction_type')
        );

        $transactions = $this->Account_transaction_model->getAllTransactionsByCompany($company_id, $filters);
        
        // Aquí integrar con librería de Excel/PDF existente
        $this->session->set_flashdata('exception', 'Función de exportación en desarrollo');
        redirect('Account/accountTransactions');
    }

    /**
     * Registrar cierre de caja a Caja Cofre (llamado desde Register)
     */
    public function registerCloseToCofre() {
        $company_id = $this->session->userdata('company_id');
        $user_id = $this->session->userdata('user_id');
        
        $amount = $this->input->post('cash_amount');
        $register_id = $this->input->post('register_id');
        $close_date = $this->input->post('close_date');
        $note = $this->input->post('note') ? $this->input->post('note') : 'Cierre de caja #' . $register_id;

        if (!$amount || $amount <= 0) {
            echo json_encode(array('status' => 'error', 'message' => 'Monto inválido'));
            return;
        }

        // Obtener Caja Cofre
        $cofre = $this->Account_model->getDefaultAccount($company_id);
        
        if (!$cofre) {
            echo json_encode(array('status' => 'error', 'message' => 'No se encontró la Caja Cofre'));
            return;
        }

        // Registrar movimiento
        $data = array(
            'transaction_type' => 'Cierre Caja',
            'from_account_id' => NULL,
            'to_account_id' => $cofre->id,
            'amount' => $amount,
            'reference_type' => 'register_close',
            'reference_id' => $register_id,
            'note' => $note,
            'transaction_date' => $close_date ? $close_date : date('Y-m-d'),
            'created_at' => date('Y-m-d H:i:s'),
            'company_id' => $company_id,
            'user_id' => $user_id,
            'del_status' => 'Live'
        );

        $result = $this->Account_transaction_model->insertTransaction($data);

        if ($result) {
            echo json_encode(array('status' => 'success', 'message' => 'Efectivo transferido a Caja Cofre exitosamente'));
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Error al transferir a Caja Cofre'));
        }
    }
}
