<?php
/*
  ###########################################################
  # PRODUCT NAME: 	iRestora PLUS - Next Gen Restaurant POS
  ###########################################################
  # AUTHER:		Doorsoft
  ###########################################################
  # EMAIL:		info@doorsoft.co
  ###########################################################
  # COPYRIGHTS:		RESERVED BY Door Soft
  ###########################################################
  # WEBSITE:		http://www.doorsoft.co
  ###########################################################
  # This is SupplierPayment Controller
  ###########################################################
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class SupplierPayment extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Authentication_model');
        $this->load->model('Common_model');
        $this->load->model('Supplier_payment_model');
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
        $controller = "147";
        $function = "";

        if($segment_2=="supplierPayments"){
            $function = "view";
        }elseif($segment_2=="addSupplierPayment" ||  $segment_2=="getSupplierDue"){
            $function = "add";
        }elseif($segment_2=="deleteSupplierPayment"){
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

        $login_session['active_menu_tmp'] = '';
        $this->session->set_userdata($login_session);
    }


     /**
     * supplier Payments
     * @access public
     * @return void
     * @param no
     */
    public function supplierPayments() {
        $outlet_id = $this->session->userdata('outlet_id');
        $data = array();
        $data['supplierPayments'] = $this->Common_model->getAllByOutletId($outlet_id, "tbl_supplier_payments");
        $data['main_content'] = $this->load->view('supplierPayment/supplierPayments', $data, TRUE);
        $this->load->view('userHome', $data);
    }
     /**
     * delete Supplier Payment
     * @access public
     * @return void
     * @param int
     */
    public function deleteSupplierPayment($id) {
        $id = $this->custom->encrypt_decrypt($id, 'decrypt');

        // Obtener datos del pago antes de eliminarlo para revertir movimiento contable
        $supplier_payment = $this->Common_model->getDataById($id, "tbl_supplier_payments");

        // Revertir movimiento contable si existía
        if ($supplier_payment && !empty($supplier_payment->account_id) && floatval($supplier_payment->amount) > 0) {
            $this->load->model('Account_model');
            $this->load->model('Account_transaction_model');

            $account = $this->Account_model->getAccountById($supplier_payment->account_id);
            if ($account) {
                // Devolver el dinero a la cuenta (revertir el pago)
                $new_balance = floatval($account->current_balance) + floatval($supplier_payment->amount);
                $this->Account_model->updateBalance($supplier_payment->account_id, $new_balance);

                // Obtener nombre del proveedor para descripción
                $supplier = $this->Common_model->getDataById($supplier_payment->supplier_id, 'tbl_suppliers');
                $supplier_name = $supplier ? $supplier->name : 'Proveedor';

                // Registrar transacción de reversión
                $transaction_data = [
                    'to_account_id' => $supplier_payment->account_id, // Reversión de pago = entrada de dinero
                    'transaction_type' => 'Deposito',
                    'amount' => floatval($supplier_payment->amount),
                    'reference_type' => 'supplier_payment_reversal',
                    'reference_id' => $id,
                    'note' => 'Reversión de pago a ' . $supplier_name . ' (eliminado)',
                    'transaction_date' => date('Y-m-d H:i:s'),
                    'user_id' => $this->session->userdata('user_id'),
                    'company_id' => $this->session->userdata('company_id')
                ];
                $this->Account_transaction_model->insertTransaction($transaction_data);
            }
        }

        $this->Common_model->deleteStatusChange($id, "tbl_supplier_payments");
        $this->session->set_flashdata('exception', lang('delete_success'));
        redirect('SupplierPayment/supplierPayments');
    }
     /**
     * add Supplier Payment
     * @access public
     * @return void
     * @param no
     */
    public function addSupplierPayment() {
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
         
        $outlet_id = $this->session->userdata('outlet_id');
        $company_id = $this->session->userdata('company_id');

        if (htmlspecialcharscustom($this->input->post('submit'))) {
            $this->form_validation->set_rules('date', lang('date'), 'required|max_length[50]');
            $this->form_validation->set_rules('amount', lang('amount'), 'required|max_length[50]');
            $this->form_validation->set_rules('supplier_id', lang('supplier'), 'required|max_length[10]');
            $this->form_validation->set_rules('payment_id', lang('payment_method'), 'required|max_length[10]');
            $this->form_validation->set_rules('note', lang('note'), 'max_length[200]');
            if ($this->form_validation->run() == TRUE) {
                $splr_payment_info = array();
                $splr_payment_info['date'] = date("Y-m-d", strtotime($this->input->post($this->security->xss_clean('date'))));
                $splr_payment_info['amount'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('amount')));
                $splr_payment_info['supplier_id'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('supplier_id')));
                $splr_payment_info['payment_id'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('payment_id')));
                $splr_payment_info['note'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('note')));
                $splr_payment_info['account_id'] = !empty($this->input->post('account_id')) ? $this->input->post('account_id') : NULL;
                $splr_payment_info['counter_id'] = $this->session->userdata('counter_id');
                $splr_payment_info['user_id'] = $this->session->userdata('user_id');
                $splr_payment_info['outlet_id'] = $this->session->userdata('outlet_id');
                $splr_payment_info['added_date_time'] = date('Y-m-d H:i:s');

                $payment_id = $this->Common_model->insertInformation($splr_payment_info, "tbl_supplier_payments");
                
                // ============ LÓGICA DE REGISTRO CONTABLE ============
                // Los pagos a proveedores RESTAN del saldo de la cuenta (son egresos)
                if (!empty($splr_payment_info['account_id']) && floatval($splr_payment_info['amount']) > 0) {
                    $this->load->model('Account_model');
                    $this->load->model('Account_transaction_model');
                    
                    $account = $this->Account_model->getAccountById($splr_payment_info['account_id']);
                    if ($account) {
                        $balance_before = floatval($account->current_balance);
                        $amount = floatval($splr_payment_info['amount']);
                        $balance_after = $balance_before - $amount; // Los pagos RESTAN del saldo
                        
                        // Actualizar saldo de la cuenta
                        $this->Account_model->updateBalance($splr_payment_info['account_id'], $balance_after);
                        
                        // Obtener nombre del proveedor para descripción
                        $supplier = $this->Common_model->getDataById($splr_payment_info['supplier_id'], 'tbl_suppliers');
                        $supplier_name = $supplier ? $supplier->name : 'Proveedor';
                        
                        // Crear registro de transacción
                        $transaction_data = [
                            'from_account_id' => $splr_payment_info['account_id'], // Pago a proveedor = salida de dinero
                            'transaction_type' => 'Pago Proveedor',
                            'amount' => $amount,
                            'reference_type' => 'supplier_payment',
                            'reference_id' => $payment_id,
                            'note' => 'Pago a ' . $supplier_name,
                            'transaction_date' => date('Y-m-d H:i:s'),
                            'user_id' => $this->session->userdata('user_id'),
                            'company_id' => $this->session->userdata('company_id')
                        ];
                        $this->Account_transaction_model->insertTransaction($transaction_data);
                    }
                }
                
                $this->session->set_flashdata('exception', lang('insertion_success'));

                redirect('SupplierPayment/supplierPayments');
            } else {
                $data = array();
                $data['payment_methods'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, "tbl_payment_methods");
                $data['suppliers'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, "tbl_suppliers");
                $this->load->model('Account_model');
                $data['accounts'] = $this->Account_model->getAllAccountsByCompany($company_id);
                $data['main_content'] = $this->load->view('supplierPayment/addSupplierPayment', $data, TRUE);
                $this->load->view('userHome', $data);
            }
        } else {
            $data = array();
            $data['payment_methods'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, "tbl_payment_methods");
            $data['suppliers'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, "tbl_suppliers");
            $this->load->model('Account_model');
            $data['accounts'] = $this->Account_model->getAllAccountsByCompany($company_id);
            $data['main_content'] = $this->load->view('supplierPayment/addSupplierPayment', $data, TRUE);
            $this->load->view('userHome', $data);
        }
    }
     /**
     * get Supplier Due
     * @access public
     * @return float
     * @param no
     */
    public function getSupplierDue() {
        $supplier_id = $_GET['supplier_id'];
        $remaining_due = $this->Supplier_payment_model->getSupplierDue($supplier_id);
        echo escape_output(getAmtP($remaining_due));
    }

}
