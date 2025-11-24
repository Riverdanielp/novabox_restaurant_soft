<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Register extends Cl_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->library('excel'); //load PHPExcel library 
        $this->load->model('Authentication_model');
        $this->load->model('Common_model');
        $this->load->model('Master_model');
        $this->load->model('Register_model');
        $this->load->model('Account_model');
        $this->load->model('Account_transaction_model');
        $this->load->model('PaymentMethod_model');
        $this->load->library('form_validation');
        
        $this->Common_model->setDefaultTimezone();
        
        if (!$this->session->has_userdata('user_id')) {
            redirect('Authentication/index');
        }
        
        if (!$this->session->has_userdata('outlet_id')) {
            $this->session->set_flashdata('exception_2', 'Please click on green Enter button of an outlet');
            redirect('Outlet/outlets');
        }
        
        $login_session['active_menu_tmp'] = '';
        $this->session->set_userdata($login_session);
    }

     /**
     * open Register
     * @access public
     * @return void
     * @param no
     */
    public function openRegister(){
        $data = array();
        $company_id = $this->session->userdata('company_id'); 
        $outlet_id = $this->session->userdata('outlet_id'); 
        $user_id = $this->session->userdata('user_id'); 
        // Verifica si ya hay una caja abierta para ese counter y outlet
        $where = array(
            'user_id'    => $user_id,
            'outlet_id'     => $outlet_id,
            'company_id'    => $company_id,
            'register_status' => 1 // Abierta
        );
        $this->db->where($where);
        $exists = $this->db->get('tbl_register')->result();
        if($exists && count($exists) > 0){
            redirect('POSChecker/posAndWaiterMiddleman');
            return;
        }
        // fin verificación de caja abierta

        $data['counters'] = $this->Common_model->getAllByCustomResultsId($outlet_id,"outlet_id","tbl_counters",$order='ASC');
        $data['payment_methods'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_payment_methods");
        $data['main_content'] = $this->load->view('register/openRegister', $data, TRUE);
        $this->load->view('userHome', $data);
    }
     /**
     * open Register
     * @access public
     * @return void
     * @param no
     */
    public function registerDetails(){ 
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

        $data = array();
        $data['main_content'] = $this->load->view('register/registerDetails', $data, TRUE);
        $this->load->view('userHome', $data);
    }
     /**
     * add Balance
     * @access public
     * @return void
     * @param int
     */
    public function addBalance($encrypted_id = ""){
        $company_id = $this->session->userdata('company_id'); 
        $outlet_id = $this->session->userdata('outlet_id'); 
        $user_id = $this->session->userdata('user_id'); 
        // Verifica si ya hay una caja abierta para ese counter y outlet
        $where = array(
            'user_id'    => $user_id,
            'outlet_id'     => $outlet_id,
            'company_id'    => $company_id,
            'register_status' => 1 // Abierta
        );
        $this->db->where($where);
        $exists = $this->db->get('tbl_register')->result();
        if($exists && count($exists) > 0){
            redirect('POSChecker/posAndWaiterMiddleman');
            return;
        }
        // fin verificación de caja abierta

        $id = $this->custom->encrypt_decrypt($encrypted_id, 'decrypt');
        if (htmlspecialcharscustom($this->input->post('submit'))) {
            $this->form_validation->set_rules('opening_balance', lang('opening_balance'), 'required');
            $this->form_validation->set_rules('counter_id', lang('counter_name'), 'required|max_length[11]');
            if ($this->form_validation->run() == TRUE) {
                $register_info = array();
                $register_info['opening_balance'] = htmlspecialcharscustom($this->input->post($this->security->xss_clean('opening_balance')));
                $register_info['closing_balance'] = 0.00;
                $register_info['counter_id'] = htmlspecialcharscustom($this->input->post($this->security->xss_clean('counter_id')));
                $register_info['opening_balance_date_time'] = date('Y-m-d H:i:s');
                $register_info['register_status'] = 1;
                $register_info['user_id'] = $this->session->userdata('user_id');
                $register_info['outlet_id'] = $this->session->userdata('outlet_id');
                $register_info['company_id'] = $this->session->userdata('company_id');

                //This variable could not be escaped because this is array content
                $payment_names = $this->input->post($this->security->xss_clean('payment_names'));
                $payment_ids = $this->input->post($this->security->xss_clean('payment_ids'));
                $payments = $this->input->post($this->security->xss_clean('payments'));
                $arr = array();

                foreach ($payment_ids as $key=>$value){
                    $arr[] = $value."||".$payment_names[$key]."||".$payments[$key];
                }
                $register_info['opening_details'] = json_encode($arr);

                   $register_id = $this->Common_model->insertInformation($register_info, "tbl_register");
                   
                   // ===== NUEVA LÓGICA: Verificar si debe afectar cuentas en apertura =====
                   $counter_details = $this->Common_model->getDataById($register_info['counter_id'], "tbl_counters");
                   
                   if (isset($counter_details->affect_opening_to_accounts) && $counter_details->affect_opening_to_accounts == 1) {
                       // Generar movimientos contables por cada método de pago
                       foreach ($payment_ids as $key => $payment_id) {
                           $amount = (float)$payments[$key];
                           
                           if ($amount > 0) {
                               // Obtener información del método de pago
                               $payment_method = $this->PaymentMethod_model->getPaymentMethodById($payment_id);
                               
                               if ($payment_method && $payment_method->account_id) {
                                   // Crear movimiento contable: DESCONTAR de la cuenta
                                   $transaction_data = array(
                                       'transaction_type' => 'Apertura de Caja',
                                       'from_account_id' => $payment_method->account_id, // Se descuenta de esta cuenta
                                       'to_account_id' => NULL,
                                       'amount' => $amount,
                                       'reference_type' => 'register_opening',
                                       'reference_id' => $register_id,
                                       'register_id' => $register_id,
                                       'note' => 'Apertura de Caja #' . $register_id . ' - Método de Pago: ' . $payment_names[$key] . ' - Monto: $' . number_format($amount, 2),
                                       'transaction_date' => date('Y-m-d H:i:s'),
                                       'created_at' => date('Y-m-d H:i:s'),
                                       'company_id' => $company_id,
                                       'user_id' => $user_id,
                                       'del_status' => 'Live'
                                   );
                                   
                                   $this->Account_transaction_model->insertTransaction($transaction_data);
                               }
                           }
                       }
                   }
                   // ===== FIN NUEVA LÓGICA =====
                   
                   // Printer Session Data Set
                   $printer_info = $this->Common_model->getPrinterInfoById($counter_details->invoice_printer_id);
                   $print_arr = [];
                   $print_arr['counter_id'] = $register_info['counter_id'];
                   $print_arr['counter_name'] = $counter_details->name;
                   $print_arr['printer_id'] = $counter_details->invoice_printer_id;
                   if($printer_info):
                        $print_arr['path'] = $printer_info->path;
                        $print_arr['title'] = $printer_info->title;
                        $print_arr['type'] = $printer_info->type;
                        $print_arr['characters_per_line'] = $printer_info->characters_per_line;
                        $print_arr['printer_ip_address'] = $printer_info->printer_ip_address;
                        $print_arr['printer_port'] = $printer_info->printer_port;
                        $print_arr['printing_choice'] = $printer_info->printing_choice;
                        $print_arr['ipvfour_address'] = $printer_info->ipvfour_address;
                        $print_arr['print_format'] = $printer_info->print_format;
                        $print_arr['inv_qr_code_enable_status'] = $printer_info->inv_qr_code_enable_status;
                   endif;
                   //bill
                   $printer_info_bill = $this->Common_model->getPrinterInfoById($counter_details->bill_printer_id);
                   $print_arr['bill_printer_id'] = $counter_details->bill_printer_id;
                   if($printer_info_bill):
                        $print_arr['path_bill'] = $printer_info_bill->path;
                        $print_arr['title_bill'] = $printer_info_bill->title;
                        $print_arr['type_bill'] = $printer_info_bill->type;
                        $print_arr['characters_per_line_bill'] = $printer_info_bill->characters_per_line;
                        $print_arr['printer_ip_address_bill'] = $printer_info_bill->printer_ip_address;
                        $print_arr['printer_port_bill'] = $printer_info_bill->printer_port;
                        $print_arr['printing_choice_bill'] = $printer_info_bill->printing_choice;
                        $print_arr['ipvfour_address_bill'] = $printer_info_bill->ipvfour_address;
                        $print_arr['print_format_bill'] = $printer_info_bill->print_format;
                        $print_arr['inv_qr_code_enable_status_bill'] = $printer_info_bill->inv_qr_code_enable_status;
                   endif;
                   $this->session->set_userdata($print_arr);
                   
                   redirect('POSChecker/posAndWaiterMiddleman');
                // if (!$this->session->has_userdata('clicked_controller')) {
                //     if ($this->session->userdata('role') == 'Admin') {
                //         redirect('Dashboard/dashboard');
                //     } else {
                //         redirect('Authentication/userProfile');
                //     }
                // } else {
                //     $controller = $this->session->userdata('clicked_controller');
                //     $function = $this->session->userdata('clicked_method');
                //     if($function=="getWaiterOrders.html"){
                //         redirect('Dashboard/dashboard');
                //     }else{
                //         if($function=="get_new_notifications_ajax" || $function=="getWaiterOrders"){
                //             redirect('Dashboard/dashboard');
                //         }else{
                //             redirect($controller."/".$function);
                //         }
                        
                //     }
                // }
            }else {
                $this->session->set_flashdata('error_message', validation_errors());
                $data = array();
                $outlet_id = $this->session->userdata('outlet_id'); 
                $data['counters'] = $this->Common_model->getAllByCustomResultsId($outlet_id,"outlet_id","tbl_counters",$order='ASC');
                $data['main_content'] = $this->load->view('register/openRegister', $data, TRUE);
                $this->load->view('userHome', $data);
            }
        }else{
            $this->session->set_flashdata('error_message', 'No se ha recibido variable submit');
            $data = array();
            $outlet_id = $this->session->userdata('outlet_id'); 
            $data['counters'] = $this->Common_model->getAllByCustomResultsId($outlet_id,"outlet_id","tbl_counters",$order='ASC');
            $data['main_content'] = $this->load->view('register/openRegister', $data, TRUE);
            $this->load->view('userHome', $data);
        }
    }
    public function addExistingBalance($encrypted_id = ""){
        $id = $this->custom->encrypt_decrypt($encrypted_id, 'decrypt');
        if (htmlspecialcharscustom($this->input->post('submit'))) {
            $this->form_validation->set_rules('counter_id', lang('counter_name'), 'required|max_length[11]');
            if ($this->form_validation->run() == TRUE) {
                $register_info = array();
                $register_info['counter_id'] = htmlspecialcharscustom($this->input->post($this->security->xss_clean('counter_id')));
  
                   // Printer Session Data Set
                   $counter_details = $this->Common_model->getPrinterIdByCounterId($register_info['counter_id']);
                   $printer_info = $this->Common_model->getPrinterInfoById($counter_details->invoice_printer_id);
                   $print_arr = [];
                   $print_arr['counter_id'] = $register_info['counter_id'];
                   $print_arr['counter_name'] = $counter_details->name;
                   $print_arr['printer_id'] = $counter_details->invoice_printer_id;
                   if($printer_info):
                        $print_arr['path'] = $printer_info->path;
                        $print_arr['title'] = $printer_info->title;
                        $print_arr['type'] = $printer_info->type;
                        $print_arr['characters_per_line'] = $printer_info->characters_per_line;
                        $print_arr['printer_ip_address'] = $printer_info->printer_ip_address;
                        $print_arr['printer_port'] = $printer_info->printer_port;
                        $print_arr['printing_choice'] = $printer_info->printing_choice;
                        $print_arr['ipvfour_address'] = $printer_info->ipvfour_address;
                        $print_arr['print_format'] = $printer_info->print_format;
                        $print_arr['inv_qr_code_enable_status'] = $printer_info->inv_qr_code_enable_status;
                   endif;
                   //bill
                   $printer_info_bill = $this->Common_model->getPrinterInfoById($counter_details->bill_printer_id);
                   $print_arr['bill_printer_id'] = $counter_details->bill_printer_id;
                   if($printer_info_bill):
                        $print_arr['path_bill'] = $printer_info_bill->path;
                        $print_arr['title_bill'] = $printer_info_bill->title;
                        $print_arr['type_bill'] = $printer_info_bill->type;
                        $print_arr['characters_per_line_bill'] = $printer_info_bill->characters_per_line;
                        $print_arr['printer_ip_address_bill'] = $printer_info_bill->printer_ip_address;
                        $print_arr['printer_port_bill'] = $printer_info_bill->printer_port;
                        $print_arr['printing_choice_bill'] = $printer_info_bill->printing_choice;
                        $print_arr['ipvfour_address_bill'] = $printer_info_bill->ipvfour_address;
                        $print_arr['print_format_bill'] = $printer_info_bill->print_format;
                        $print_arr['inv_qr_code_enable_status_bill'] = $printer_info_bill->inv_qr_code_enable_status;
                   endif;
                   $this->session->set_userdata($print_arr);
                   
                if (!$this->session->has_userdata('clicked_controller')) {
                    if ($this->session->userdata('role') == 'Admin') {
                        redirect('Dashboard/dashboard');
                    } else {
                        redirect('Authentication/userProfile');
                    }
                } else {
                    $controller = $this->session->userdata('clicked_controller');
                    $function = $this->session->userdata('clicked_method');
                    if($function=="getWaiterOrders.html"){
                        redirect('Dashboard/dashboard');
                    }else{
                        if($function=="get_new_notifications_ajax" || $function=="getWaiterOrders"){
                            redirect('Dashboard/dashboard');
                        }else{
                            redirect($controller."/".$function);
                        }
                        
                    }
                }
            }else {
                $this->session->set_flashdata('error_message', validation_errors());
                $data = array();
                $outlet_id = $this->session->userdata('outlet_id'); 
                $data['counters'] = $this->Common_model->getAllByCustomResultsId($outlet_id,"outlet_id","tbl_counters",$order='ASC');
                $data['main_content'] = $this->load->view('register/openRegister', $data, TRUE);
                $this->load->view('userHome', $data);
            }
        }else{
            $this->session->set_flashdata('error_message', 'No se ha recibido variable submit');
            $data = array();
            $outlet_id = $this->session->userdata('outlet_id'); 
            $data['counters'] = $this->Common_model->getAllByCustomResultsId($outlet_id,"outlet_id","tbl_counters",$order='ASC');
            $data['main_content'] = $this->load->view('register/openRegister', $data, TRUE);
            $this->load->view('userHome', $data);
        }
    }
     /**
     * check Register Ajax
     * @access public
     * @return void
     * @param no
     */
    public function checkRegisterAjax()
    {
        $user_id = $this->session->userdata('user_id');
        $outlet_id = $this->session->userdata('outlet_id');
        $checkRegister = $this->Register_model->checkRegister($user_id,$outlet_id);
        if(!is_null($checkRegister)){
            echo escape_output($checkRegister->status);
        }else{
            echo "";
        }
                
    }

    /**
     * getCounterPreset - Endpoint AJAX para obtener configuración del contador
     * @access public
     * @param int
     * @return json
     */
    public function getCounterPreset($counter_id = null) {
        if (!$counter_id) {
            $counter_id = $this->input->post('counter_id');
        }
        
        if (!$counter_id) {
            echo json_encode(['error' => 'No counter ID provided']);
            return;
        }

        $counter = $this->Common_model->getDataById($counter_id, "tbl_counters");
        
        if (!$counter) {
            echo json_encode(['error' => 'Counter not found']);
            return;
        }

        // Decodificar el JSON de default_opening_payments
        $default_payments = [];
        if (!empty($counter->default_opening_payments)) {
            $decoded = json_decode($counter->default_opening_payments, true);
            if (is_array($decoded)) {
                $default_payments = $decoded;
            }
        }

        $response = [
            'affect_opening_to_accounts' => (int)$counter->affect_opening_to_accounts,
            'default_opening_payments' => $default_payments
        ];

        echo json_encode($response);
    }
}
