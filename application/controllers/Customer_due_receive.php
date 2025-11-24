<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Customer_due_receive extends Cl_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Authentication_model');
        $this->load->model('Common_model'); 
        $this->load->model('Customer_due_receive_model');
        $this->load->library('form_validation');
        $this->Common_model->setDefaultTimezone();

        if (!$this->session->has_userdata('user_id')) {
            redirect('Authentication/index');
        }
        if (!$this->session->has_userdata('outlet_id')) {
            $this->session->set_flashdata('exception_2', lang('please_click_green_button'));

            $this->session->set_userdata("clicked_controller", $this->uri->segment(1));
            $this->session->set_userdata("clicked_method", $this->uri->segment(2));
            redirect('Outlet/outlets');
        }

        //start check access function
        $segment_2 = $this->uri->segment(2);
        $segment_3 = $this->uri->segment(3);
        $controller = "151";
        $function = "";

        if($segment_2=="customerDueReceives"){
            $function = "view";
        }elseif($segment_2=="addCustomerDueReceive" ||  $segment_2=="getCustomerDue" || $segment_2=="getPendingSales" || $segment_2=="getCustomersWithDue"){
            $function = "add";
        }elseif($segment_2=="deleteCustomerDueReceive"){
            $function = "delete";
        }else{
            $this->session->set_flashdata('exception_er', lang('menu_not_permit_access'));
            redirect('Authentication/userProfile');
        }

        if(!checkAccess($controller,$function)){
            $this->session->set_flashdata('exception_er', lang('menu_not_permit_access'));
            redirect('Authentication/userProfile');
        }
        //end check access function

        //check register is open or not
        $user_id = $this->session->userdata('user_id');
        $outlet_id = $this->session->userdata('outlet_id');
        if($this->Common_model->isOpenRegister($user_id,$outlet_id)==0){
            $this->session->set_flashdata('exception_3', lang('register_not_open'));

            $this->session->set_userdata("clicked_controller", $this->uri->segment(1));
            $this->session->set_userdata("clicked_method", $this->uri->segment(2));
            redirect('Register/openRegister');   
        }
        $login_session['active_menu_tmp'] = '';
        $this->session->set_userdata($login_session);
    }


     /**
     * bar panel
     * @access public
     * @return void
     * @param no
     */
    public function customerDueReceives() {
        $outlet_id = $this->session->userdata('outlet_id');
        $data = array();
        
        // Obtener filtros desde GET
        $date_from = $this->input->get('date_from') ? $this->input->get('date_from') : date('Y-m-01');
        $date_to = $this->input->get('date_to') ? $this->input->get('date_to') : date('Y-m-t');
        $customer_id = $this->input->get('customer_id');
        $user_id = $this->input->get('user_id');
        
        // Construir query con filtros
        $this->db->select('*');
        $this->db->from('tbl_customer_due_receives');
        $this->db->where('outlet_id', $outlet_id);
        $this->db->where('del_status', 'Live');
        $this->db->where('only_date >=', $date_from);
        $this->db->where('only_date <=', $date_to);
        
        if ($customer_id) {
            $this->db->where('customer_id', $customer_id);
        }
        
        if ($user_id) {
            $this->db->where('user_id', $user_id);
        }
        
        $this->db->order_by('only_date', 'DESC');
        $data['customerDueReceives'] = $this->db->get()->result();
        
        // Pasar filtros actuales a la vista
        $data['filter_date_from'] = $date_from;
        $data['filter_date_to'] = $date_to;
        $data['filter_customer_id'] = $customer_id;
        $data['filter_user_id'] = $user_id;
        
        // Obtener lista de usuarios para el select
        $this->db->select('id, full_name');
        $this->db->from('tbl_users');
        $this->db->where('company_id', $this->session->userdata('company_id'));
        $this->db->where('del_status', 'Live');
        $this->db->order_by('full_name', 'ASC');
        $data['users'] = $this->db->get()->result();

        $data['main_content'] = $this->load->view('customerDueReceive/customerDueReceives', $data, TRUE);
        $this->load->view('userHome', $data);
    }
     /**
     * bar panel
     * @access public
     * @return void
     * @param no
     */
    /**
     * Eliminar pago de cliente
     * @access public
     * @param int $id
     * @return void
     */
    public function deleteCustomerDueReceive($id) {
        $id = $this->custom->encrypt_decrypt($id, 'decrypt');

        // Obtener datos del cobro antes de eliminarlo para revertir movimiento contable
        $customer_due_receive = $this->Common_model->getDataById($id, "tbl_customer_due_receives");

        // Revertir movimiento contable si existía
        if ($customer_due_receive && !empty($customer_due_receive->account_id) && floatval($customer_due_receive->amount) > 0) {
            $this->load->model('Account_transaction_model');

            // Obtener nombre del cliente para descripción
            $customer = $this->Common_model->getDataById($customer_due_receive->customer_id, 'tbl_customers');
            $customer_name = $customer ? $customer->name : 'Cliente';

            // Registrar transacción de reversión - Retiro (dinero sale de la cuenta)
            $transaction_data = [
                'transaction_date' => date('Y-m-d H:i:s'),
                'from_account_id' => $customer_due_receive->account_id,
                'transaction_type' => 'Retiro',
                'amount' => floatval($customer_due_receive->amount),
                'reference_type' => 'customer_payment_reversal',
                'reference_id' => $id,
                'note' => 'Reversión de cobro de ' . $customer_name . ' - ' . $customer_due_receive->reference_no . ' (eliminado)',
                'user_id' => $this->session->userdata('user_id'),
                'company_id' => $this->session->userdata('company_id')
            ];
            $this->Account_transaction_model->insertTransaction($transaction_data);
        }

        // Obtener los registros de ventas afectadas por este pago
        $this->db->select('sale_id, amount');
        $this->db->from('tbl_customer_due_receives_sales');
        $this->db->where('due_receive_id', $id);
        $affected_sales = $this->db->get()->result();

        // Restar los montos del paid_due_amount de cada venta
        foreach ($affected_sales as $sale) {
            $this->db->set('paid_due_amount', 'paid_due_amount - ' . $sale->amount, FALSE);
            $this->db->where('id', $sale->sale_id);
            $this->db->update('tbl_sales');
        }

        // Eliminar los registros de tbl_customer_due_receives_sales
        $this->db->where('due_receive_id', $id);
        $this->db->delete('tbl_customer_due_receives_sales');

        // Marcar el pago como eliminado
        $this->Common_model->deleteStatusChange($id, "tbl_customer_due_receives");

        $this->session->set_flashdata('exception', lang('delete_success'));
        redirect('Customer_due_receive/customerDueReceives');
    }
     /**
     * bar panel
     * @access public
     * @return void
     * @param no
     */
    public function addCustomerDueReceive() {
         //check register is open or not
         $is_waiter = $this->session->userdata('is_waiter');
         $designation = $this->session->userdata('designation');
         if($designation!="Waiter" && $this->session->has_userdata('is_online_order')!="Yes" && !isFoodCourt()){
             $user_id = $this->session->userdata('user_id');
             $outlet_id = $this->session->userdata('outlet_id');
             if($this->Common_model->isOpenRegister($user_id,$outlet_id)==0){
                 $this->session->set_flashdata('exception_3', lang('register_open_msg'));
                 if($this->uri->segment(2)=='registerDetailCalculationToShowAjax' || $this->uri->segment(2)=='closeRegister'){
                     redirect('Register/openRegister');
                 }else{
                     $this->session->set_userdata("clicked_controller", $this->uri->segment(1));
                     $this->session->set_userdata("clicked_method", $this->uri->segment(2));
                     redirect('Register/openRegister');
                 }
 
             }
         }
         
        $company_id = $this->session->userdata('company_id');
        $outlet_id = $this->session->userdata('outlet_id');
        if (htmlspecialcharscustom($this->input->post('submit'))) {
            $this->form_validation->set_rules('date', lang('date'), 'required|max_length[50]');
            $this->form_validation->set_rules('reference_no', lang('ref_no'), 'required|max_length[50]');
            $this->form_validation->set_rules('amount', lang('amount'), 'required|max_length[50]');
            $this->form_validation->set_rules('customer_id', lang('customer'), 'required|max_length[10]');
            $this->form_validation->set_rules('payment_id', lang('payment_method'), 'required|max_length[10]');
            $this->form_validation->set_rules('note', lang('note'), 'max_length[200]');
            if ($this->form_validation->run() == TRUE) {
                $splr_payment_info = array();
                $splr_payment_info['date'] = date("Y-m-d H:i:s");
                $splr_payment_info['only_date'] = date("Y-m-d", strtotime($this->input->post($this->security->xss_clean('date'))));
                $splr_payment_info['amount'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('amount')));
                $splr_payment_info['reference_no'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('reference_no')));
                $splr_payment_info['customer_id'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('customer_id')));
                $splr_payment_info['payment_id'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('payment_id')));
                $splr_payment_info['note'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('note')));
                $splr_payment_info['account_id'] = !empty($this->input->post('account_id')) ? $this->input->post('account_id') : NULL;
                $splr_payment_info['counter_id'] = $this->session->userdata('counter_id');
                $splr_payment_info['user_id'] = $this->session->userdata('user_id');
                $splr_payment_info['outlet_id'] = $this->session->userdata('outlet_id');
                $splr_payment_info['company_id'] = $this->session->userdata('company_id');

                // Insertar el pago principal
                $due_receive_id = $this->Common_model->insertInformation($splr_payment_info, "tbl_customer_due_receives");
                
                // ============ LÓGICA DE REGISTRO CONTABLE ============
                // Los cobros a clientes SUMAN al saldo de la cuenta (son ingresos)
                if (!empty($splr_payment_info['account_id']) && floatval($splr_payment_info['amount']) > 0) {
                    $this->load->model('Account_model');
                    $this->load->model('Account_transaction_model');
                    
                    $account = $this->Account_model->getAccountById($splr_payment_info['account_id']);
                    if ($account) {
                        $balance_before = floatval($account->current_balance);
                        $amount = floatval($splr_payment_info['amount']);
                        $balance_after = $balance_before + $amount; // Los cobros SUMAN al saldo
                        
                        // Actualizar saldo de la cuenta
                        $this->Account_model->updateBalance($splr_payment_info['account_id'], $balance_after);
                        
                        // Obtener nombre del cliente para descripción
                        $customer = $this->Common_model->getDataById($splr_payment_info['customer_id'], 'tbl_customers');
                        $customer_name = $customer ? $customer->name : 'Cliente';
                        
                        // Crear registro de transacción
                        $transaction_data = [
                            'to_account_id' => $splr_payment_info['account_id'], // Cobro a cliente = entrada de dinero
                            'transaction_type' => 'Cobro Cliente',
                            'amount' => $amount,
                            'reference_type' => 'customer_payment',
                            'reference_id' => $due_receive_id,
                            'note' => 'Cobro de ' . $customer_name . ' - Ref: ' . $splr_payment_info['reference_no'],
                            'transaction_date' => date('Y-m-d H:i:s') ,
                            'user_id' => $this->session->userdata('user_id'),
                            'company_id' => $this->session->userdata('company_id')
                        ];
                        $this->Account_transaction_model->insertTransaction($transaction_data);
                    }
                }
                
                // Procesar sales_details (ventas afectadas por este pago)
                $sales_details = $this->input->post('sales_details');
                if ($sales_details && is_array($sales_details)) {
                    foreach ($sales_details as $sale_id => $payment_amount) {
                        $payment_amount = floatval($payment_amount);
                        
                        if ($payment_amount > 0) {
                            // Verificar si es un pago negativo (saldo inicial) - ID empieza con "NEG-"
                            if (strpos($sale_id, 'NEG-') === 0) {
                                // Es un pago negativo - NO guardar en tbl_customer_due_receives_sales
                                // Solo sirve para balancear, los pagos positivos y negativos se anulan
                                // NO hacemos nada en BD, solo lo usamos en la UI para mostrar distribución
                                continue;
                            }
                            
                            // Es una venta real - procesar normalmente
                            // Insertar en tbl_customer_due_receives_sales
                            $sale_payment_data = [
                                'due_receive_id' => $due_receive_id,
                                'sale_id' => $sale_id,
                                'amount' => $payment_amount
                            ];
                            $this->db->insert('tbl_customer_due_receives_sales', $sale_payment_data);
                            
                            // Actualizar paid_due_amount en tbl_sales (SUMAR al existente)
                            $this->db->set('paid_due_amount', 'paid_due_amount + ' . $payment_amount, FALSE);
                            $this->db->where('id', $sale_id);
                            $this->db->update('tbl_sales');
                        }
                    }
                }
                
                $this->session->set_flashdata('exception', lang('insertion_success'));
                redirect('Customer_due_receive/customerDueReceives');
            } else {
                $data = array();
                $data['reference_no'] = $this->Customer_due_receive_model->generateReferenceNo($outlet_id);
                $data['customers'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, "tbl_customers");
                $data['payment_methods'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, "tbl_payment_methods");
                $this->load->model('Account_model');
                $data['accounts'] = $this->Account_model->getAllAccountsByCompany($company_id);
                $data['main_content'] = $this->load->view('customerDueReceive/addCustomerDueReceive', $data, TRUE);
                $this->load->view('userHome', $data);
            }
        } else {
            $data = array();
            $data['reference_no'] = $this->Customer_due_receive_model->generateReferenceNo($outlet_id);

            $data['customers'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, "tbl_customers");
            $data['payment_methods'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, "tbl_payment_methods");
            $this->load->model('Account_model');
            $data['accounts'] = $this->Account_model->getAllAccountsByCompany($company_id);
            $data['main_content'] = $this->load->view('customerDueReceive/addCustomerDueReceive', $data, TRUE);
            $this->load->view('userHome', $data);
        }
    }
     /**
     * bar panel
     * @access public
     * @return float
     * @param no
     */
    public function getCustomerDue() {
        $customer_id = $_GET['customer_id']; 

        $remaining_due = $this->Customer_due_receive_model->getCustomerDue($customer_id);

        echo (isset($remaining_due) && $remaining_due?getAmtP($remaining_due):getAmtP(0));
    }

    /**
     * Obtener ventas pendientes con saldo del cliente
     * INCLUYE pagos negativos (ajustes de saldo) como items para balancear
     * @access public
     * @return json
     * @param no
     */
    public function getPendingSales() {
        $customer_id = $this->input->get('customer_id');
        $outlet_id = $this->session->userdata('outlet_id');
        
        if (!$customer_id) {
            echo json_encode(['status' => 'error', 'sales' => []]);
            return;
        }
        
        // Llamar al modelo para obtener las ventas pendientes
        $pending_sales = $this->Customer_due_receive_model->getPendingSales($customer_id, $outlet_id);
        
        echo json_encode(['status' => 'success', 'sales' => $pending_sales]);
    }

    /**
     * Obtener clientes con deuda para Select2 Ajax
     * @access public
     * @return json
     * @param no
     */
    public function getCustomersWithDue() {
        $search = $this->input->get('q');
        $outlet_id = $this->session->userdata('outlet_id');
        $company_id = $this->session->userdata('company_id');
        
        // Obtener todos los clientes activos de la compañía
        $this->db->select('id, name, phone, gst_number');
        $this->db->from('tbl_customers');
        $this->db->where('company_id', $company_id);
        $this->db->where('id !=', 1); // Excluir Walk-in Customer
        $this->db->where('del_status', 'Live');
        
        // Búsqueda por nombre, teléfono o gst_number
        if ($search) {
            $this->db->group_start();
            $this->db->like('name', $search);
            $this->db->or_like('phone', $search);
            $this->db->or_like('gst_number', $search);
            $this->db->group_end();
        }
        
        $this->db->limit(20);
        $customers = $this->db->get()->result();
        
        // Obtener filtros de fecha si existen
        $date_from = $this->input->get('date_from');
        $date_to = $this->input->get('date_to');

        $results = [];
        foreach ($customers as $customer) {
            // Calcular deuda considerando el rango de fechas si se proporcionó
            if ($date_from && $date_to) {
                // Deuda solo en el rango de fechas especificado (pagos positivos)
                $this->db->select('SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END) as total_paid');
                $this->db->from('tbl_customer_due_receives');
                $this->db->where('customer_id', $customer->id);
                $this->db->where('only_date >=', $date_from);
                $this->db->where('only_date <=', $date_to);
                $this->db->where('del_status', 'Live');
                $result = $this->db->get()->row();
                $customer_due = (float)($result->total_paid ?? 0);
            } else {
                $customer_due = getCustomerDue($customer->id, $outlet_id);
            }
            
            // Solo incluir clientes con pagos en el período
            if ($customer_due > 0) {
                $text = $customer->name;
                if ($customer->phone) {
                    $text .= ' - ' . $customer->phone;
                }
                $text .= ' (' . getAmtP($customer_due) . ')';
                
                $results[] = [
                    'id' => $customer->id,
                    'text' => $text,
                    'due_amount' => $customer_due
                ];
            }
        }
        
        echo json_encode(['results' => $results]);
    }

}
