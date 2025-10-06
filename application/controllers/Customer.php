<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Customer extends Cl_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('excel'); //load PHPExcel library
        $this->load->model('Common_model');
        $this->load->model('Customer_due_receive_model'); // Cargar el modelo
        $this->load->library('form_validation');
        $this->Common_model->setDefaultTimezone();

        if (!$this->session->has_userdata('user_id')) {
            redirect('Authentication/index');
        }
        //start check access function
        $segment_2 = $this->uri->segment(2);
        $segment_3 = $this->uri->segment(3);
        $controller = "249";
        $function = "";

        if($segment_2=="customers" || $segment_2=="getAjaxData" || $segment_2=="deleteCustomer" || $segment_2=="uploadCustomer" || $segment_2=="ExcelDataAddCustomers" || $segment_2=="addEditCustomer" || $segment_2=="viewCustomer" || $segment_2=="downloadPDF"){
            $function = "view";
        }elseif($segment_2=="addEditCustomer" && $segment_3){
            $function = "update";
        }elseif($segment_2=="addEditCustomer"){
            $function = "add";
        }elseif($segment_2=="uploadCustomer" || $segment_2=="ExcelDataAddCustomers" || $segment_2=="downloadPDF"){
            $function = "upload_customer";
        }elseif($segment_2=="deleteCustomer"){
            $function = "delete";
        }else{
            $this->session->set_flashdata('exception_er', lang('menu_not_permit_access'));
            redirect('Authentication/userProfile');
        }

        if($segment_2=="downloadPDF"){

        }else{
            if(!checkAccess($controller,$function)){
                $this->session->set_flashdata('exception_er', lang('menu_not_permit_access'));
                redirect('Authentication/userProfile');
            }
        }

        //end check access function
        $login_session['active_menu_tmp'] = '';
        $this->session->set_userdata($login_session);
    }

     /**
     * customers info
     * @access public
     * @return void
     * @param no
     */
    public function customers() {
        $company_id = $this->session->userdata('company_id');

        $data = array();
        $data['customers'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_customers");
        $data['main_content'] = $this->load->view('master/customer/customers', $data, TRUE);
        $this->load->view('userHome', $data);
    }

    public function getAjaxData()
    {
        $company_id = $this->session->userdata('company_id');
        $is_loyalty_enable = $this->session->userdata('is_loyalty_enable');
        $outlet_id = $this->session->userdata('outlet_id');

        // Obtén los datos paginados SIN ordenar por current_due
        $customers = $this->Common_model->make_datatables_customers($company_id, $is_loyalty_enable, $outlet_id);

        $data = array();
        $rows = array();
        $i = $_POST['start'] + 1;
        foreach ($customers as $cust) {
            if ($cust->del_status == "Live") {
                $current_due = 0;
                $redeemed_point = 0;
                $available_point = 0;
                if ($cust->id != 1) {
                    $current_due = getCustomerDue($cust->id);
                    if ($is_loyalty_enable == "enable") {
                        $return_data = getTotalLoyaltyPoint($cust->id, $outlet_id);
                        $redeemed_point = $return_data[0];
                        $available_point = $return_data[1];
                    }
                }
                $actions = '';
                if ($cust->name != "Walk-in Customer") {
                    $actions .= '<div class="btn_group_wrap">';
                    $actions .= '<a class="btn btn-warning" href="'.base_url().'customer/addEditCustomer/'.escape_output($this->custom->encrypt_decrypt($cust->id, "encrypt")).'" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="'.lang('edit').'"><i class="far fa-edit"></i></a>';
                    $actions .= '<a class="delete btn btn-danger" href="'.base_url().'customer/deleteCustomer/'.escape_output($this->custom->encrypt_decrypt($cust->id, "encrypt")).'" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="'.lang('delete').'"><i class="fa-regular fa-trash-can"></i></a>';
                    $actions .= '</div>';
                }

                $row = array();
                $row[] = escape_output($i++); // 0
                $row[] = escape_output($cust->name); // 1
                $row[] = escape_output($cust->phone); // 2
                $row[] = escape_output($cust->email); // 3
                $row[] = ($cust->date_of_birth != '1970-01-01' ? escape_output($cust->date_of_birth) : ''); // 4
                $row[] = escape_output($cust->default_discount); // 5
                $row[] = escape_output($cust->address); // 6
                $row[] = escape_output(($current_due)); // 7 current_due
                $row['_current_due_raw'] = $current_due; // Campo auxiliar para ordenar
                if ($is_loyalty_enable == "enable") {
                    $row[] = escape_output($available_point);
                }
                $row[] = userName($cust->user_id);
                $row[] = $actions;
                $rows[] = $row;
            }
        }

        // Detecta si ordenan por current_due
        $order_column = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : null;
        $order_dir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'asc';
        // Supongamos que 'current_due' es la columna 7 (ajusta si cambia el orden)
        $current_due_column_index = 7;

        if ($order_column === $current_due_column_index) {
            usort($rows, function($a, $b) use ($order_dir) {
                // Ordena por campo auxiliar
                if ($a['_current_due_raw'] == $b['_current_due_raw']) return 0;
                if ($order_dir == 'asc')
                    return ($a['_current_due_raw'] < $b['_current_due_raw']) ? -1 : 1;
                else
                    return ($a['_current_due_raw'] > $b['_current_due_raw']) ? -1 : 1;
            });
        }

        // Elimina el campo auxiliar antes de enviar a DataTables
        foreach ($rows as &$row) {
            unset($row['_current_due_raw']);
            $data[] = $row;
        }

        $output = array(
            "draw" => intval($this->Common_model->getDrawDataCustomers()),
            "recordsTotal" => $this->Common_model->get_all_data_customers($company_id),
            "recordsFiltered" => $this->Common_model->get_filtered_data_customers($company_id),
            "data" => $data
        );
        echo json_encode($output);
    }


     /**
     * delete customer
     * @access public
     * @return void
     * @param int
     */
    public function deleteCustomer($id) {
        $id = $this->custom->encrypt_decrypt($id, 'decrypt');

        $this->Common_model->deleteStatusChange($id, "tbl_customers");

        $this->session->set_flashdata('exception',lang('delete_success'));
        redirect('customer/customers');
    }
     /**
     * upload customer from excel file
     * @access public
     * @return void
     * @param no
     */
    public function uploadCustomer()
    {
        $company_id = $this->session->userdata('company_id');
        $data = array();
        $data['main_content'] = $this->load->view('master/customer/uploadsCustomer', $data, TRUE);
        $this->load->view('userHome', $data);
    }
     /**
     * add/edit customer
     * @access public
     * @return void
     * @param int
     */
    public function addEditCustomer($encrypted_id = "") {
        $id = $this->custom->encrypt_decrypt($encrypted_id, 'decrypt');
        if (htmlspecialcharscustom($this->input->post('submit'))) {
            $this->form_validation->set_rules('name', lang('category_name'), 'required|max_length[50]');
            // $this->form_validation->set_rules('phone', lang('phone'), 'required|max_length[50]');
            if(collectGST()=="Yes"){
                $this->form_validation->set_rules('gst_number', lang('gst_number'), 'required|max_length[50]');
                $this->form_validation->set_rules('same_or_diff_state', lang('same_or_diff_state'), 'required|max_length[50]');
            }
            
            if ($this->form_validation->run() == TRUE) {
                $customer_info = array();
                $customer_info['name'] = htmlspecialcharscustom($this->input->post($this->security->xss_clean('name')));
                $customer_info['phone'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('phone')));
                $customer_info['email'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('email')));
                $customer_info['default_discount'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('default_discount')));
                
                if(htmlspecialcharscustom($this->input->post($this->security->xss_clean('date_of_birth')))){
                    $customer_info['date_of_birth'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('date_of_birth')));
                }
                if(htmlspecialcharscustom($this->input->post($this->security->xss_clean('date_of_anniversary')))){
                    $customer_info['date_of_anniversary'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('date_of_anniversary')));
                }

                $online_order_login_password = (htmlspecialcharscustom($this->input->post($this->security->xss_clean('online_order_login_password'))));
                if($online_order_login_password){
                    $customer_info['password_online_user'] = md5($online_order_login_password);
                }
                $c_address = htmlspecialcharscustom($this->input->post($this->security->xss_clean('address')));
                $customer_info['address'] = preg_replace("/[\n\r]/"," ",escape_output($c_address)); #remove new line from address

                if(collectGST()=="Yes"){
                    $customer_info['gst_number'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('gst_number')));
                    $customer_info['same_or_diff_state'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('same_or_diff_state')));
                }
                $customer_info['user_id'] = $this->session->userdata('user_id');
                $customer_info['company_id'] = $this->session->userdata('company_id');
                if ($id == "") {
                    $id = $this->Common_model->insertInformation($customer_info, "tbl_customers");
                    $customer_address = array();
                    $customer_address['customer_id'] = $id;
                    $customer_address['address'] = $customer_info['address'];
                    $customer_address['is_active'] = 1;
                    $this->Common_model->insertInformation($customer_address, "tbl_customer_address");

                    $this->session->set_flashdata('exception', lang('insertion_success'));
                } else {
                    $this->Common_model->updateInformation($customer_info, $id, "tbl_customers");

                    $customer_address = array();
                    $customer_address['customer_id'] = $id;
                    $customer_address['address'] = $customer_info['address'];
                    $customer_address['is_active'] = 1;

                    $getActiveAddress = $this->Common_model->getActiveAddress($id);
                    if(isset($getActiveAddress) && $getActiveAddress){
                        $this->Common_model->updateInformation($customer_address, $getActiveAddress->id, "tbl_customer_address");
                    }else{
                        $this->Common_model->insertInformation($customer_address, "tbl_customer_address");
                    }

                    $this->session->set_flashdata('exception',lang('update_success'));
                }



                redirect('customer/customers');
            } else {
                if ($id == "") {
                    $data = array();
                    $data['main_content'] = $this->load->view('master/customer/addCustomer', $data, TRUE);
                    $this->load->view('userHome', $data);
                } else {
                    $data = array();
                    $data['encrypted_id'] = $encrypted_id;
                    $data['customer_information'] = $this->Common_model->getDataById($id, "tbl_customers");
                    $data['main_content'] = $this->load->view('master/customer/editCustomer', $data, TRUE);
                    $this->load->view('userHome', $data);
                }
            }
        } else {
            if ($id == "") {
                $data = array();
                $data['main_content'] = $this->load->view('master/customer/addCustomer', $data, TRUE);
                $this->load->view('userHome', $data);
            } else {
                $data = array();
                $data['encrypted_id'] = $encrypted_id;
                $data['customer_information'] = $this->Common_model->getDataById($id, "tbl_customers");
                $data['main_content'] = $this->load->view('master/customer/editCustomer', $data, TRUE);
                $this->load->view('userHome', $data);
            }
        }
    }
     /**
     * excel data add form
     * @access public
     * @return void
     * @param no
     */
    public function ExcelDataAddCustomers()
    {
        $company_id = $this->session->userdata('company_id');
        if ($_FILES['userfile']['name'] != "") {
            if ($_FILES['userfile']['name'] == "Customer_Upload.xlsx") {
                //Path of files were you want to upload on localhost (C:/xampp/htdocs/ProjectName/uploads/excel/)
                $configUpload['upload_path'] = FCPATH . 'asset/excel/';
                $configUpload['allowed_types'] = 'xls|xlsx';
                $configUpload['max_size'] = '5000';
                $this->load->library('upload', $configUpload);
                if ($this->upload->do_upload('userfile')) {
                    $upload_data = $this->upload->data(); //Returns array of containing all of the data related to the file you uploaded.
                    $file_name = $upload_data['file_name']; //uploded file name
                    $extension = $upload_data['file_ext'];    // uploded file extension
                    //$objReader =PHPExcel_IOFactory::createReader('Excel5');     //For excel 2003
                    $objReader = PHPExcel_IOFactory::createReader('Excel2007'); // For excel 2007
                    //Set to read only
                    $objReader->setReadDataOnly(true);
                    //Load excel file
                    $objPHPExcel = $objReader->load(FCPATH . 'asset/excel/' . $file_name);
                    $totalrows = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();   //Count Numbe of rows avalable in excel
                    $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
                    //loop from first data untill last data
                    if ($totalrows < 54) {
                        $arrayerror = '';
                        for ($i = 4; $i <= $totalrows; $i++) {
                            $name = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(0, $i)->getValue()));
                            $phone = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(1, $i)->getValue()));
                            $email = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(2, $i)->getValue()));
                            $dob = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(3, $i)->getValue()));
                            $doa = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(4, $i)->getValue()));
                            $delivery_address = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(5, $i)->getValue()));
                            $documento = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(7, $i)->getValue()));
                            $deuda = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(8, $i)->getValue()));

                            if ($name == '') {
                                if ($arrayerror == '') {
                                    $arrayerror.="En la linea $i columna A es requerido";
                                } else {
                                    $arrayerror.="<br>En la linea $i columna A es requerido";
                                }
                            }

                            // if ($phone == '') {
                            //     if ($arrayerror == '') {
                            //         $arrayerror.="En la linea $i columna B es requerido";
                            //     } else {
                            //         $arrayerror.="<br>En la linea $i columna B es requerido";
                            //     }
                            // }

                            if ($email != '' && $this->validateEmail($email)==false) {
                                if ($arrayerror == '') {
                                    $arrayerror.="En la linea $i columna C debe ser un correo electrónico válido.";
                                } else {
                                    $arrayerror.="<br>En la linea $i columna C debe ser un correo electrónico válido.";
                                }
                            }

                            if ($dob != '' && $this->isValidDate($dob)==false) {
                                if ($arrayerror == '') {
                                    $arrayerror.="En la linea $i columna D debe estar en el formato YYYY-MM-DD";
                                } else {
                                    $arrayerror.="<br>En la linea $i columna D debe estar en el formato YYYY-MM-DD";
                                }
                            }

                            if ($doa != '' && $this->isValidDate($doa)==false) {
                                if ($arrayerror == '') {
                                    $arrayerror.="En la linea $i columna E debe estar en el formato YYYY-MM-DD";
                                } else {
                                    $arrayerror.="<br>En la linea $i columna E debe estar en el formato YYYY-MM-DD";
                                }
                            }
                        }
                        if ($arrayerror == '') {

                            for ($i = 4; $i <= $totalrows; $i++) {
                                $name = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(0, $i)->getValue()));
                                $phone = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(1, $i)->getValue()));
                                $email = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(2, $i)->getValue()));
                                $dob = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(3, $i)->getValue()));
                                $doa = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(4, $i)->getValue()));
                                $delivery_address = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(5, $i)->getValue()));
                                $default_discount = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(6, $i)->getValue()));

                                $documento = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(7, $i)->getValue()));
                                $deuda = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(8, $i)->getValue()));

                                $customer_info = array();
                                $customer_info['name'] = $name;
                                $customer_info['phone'] = $phone;
                                $customer_info['email'] = $email;
                                $customer_info['date_of_birth'] = $dob;
                                $customer_info['date_of_anniversary'] = $doa;
                                $customer_info['address'] = $delivery_address;
                                $customer_info['default_discount'] = $default_discount;
                                $customer_info['gst_number'] = $documento;
                                $customer_info['user_id'] = $this->session->userdata('user_id');
                                $customer_info['company_id'] = $this->session->userdata('company_id');

                                $id_customer = $this->Common_model->insertInformation($customer_info, "tbl_customers");
                                if ($id_customer && floatval($deuda) > 0) {
                                    $outlet_id = $this->session->userdata('outlet_id');
                                    $due = 0 - floatval($deuda);
                                    $due_receive_info = [
                                        'date' => date("Y-m-d H:i:s"),
                                        'only_date' => date("Y-m-d"),
                                        'amount' => $due,
                                        'reference_no' => 0,
                                        'customer_id' => $id_customer,
                                        'payment_id' => 1,
                                        'note' => 'Deuda inicial al importar cliente',
                                        'counter_id' => null,
                                        'user_id' => $this->session->userdata('user_id'),
                                        'outlet_id' => $outlet_id,
                                        'company_id' => $this->session->userdata('company_id')
                                    ];

                                    $due_receive_info = $this->security->xss_clean($due_receive_info);
                                    
                                    // Usamos insertInformationAndGetId para obtener el ID del nuevo registro
                                    $payment_id = $this->Common_model->insertInformationAndGetId($due_receive_info, "tbl_customer_due_receives");
                                }
                                
  

                            }
                            unlink(FCPATH . 'asset/excel/' . $file_name); //File Deleted After uploading in database .
                            $this->session->set_flashdata('exception', 'Imported successfully!');
                            redirect('customer/customers');
                        } else {
                            unlink(FCPATH . 'asset/excel/' . $file_name); //File Deleted After uploading in database .
                            $this->session->set_flashdata('exception_err', "Required Data Missing:$arrayerror");
                        }
                    } else {
                        unlink(FCPATH . 'asset/excel/' . $file_name); //File Deleted After uploading in database .
                        $this->session->set_flashdata('exception_err', "Entry is more than 50 or No entry found.");
                    }
                } else {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('exception_err', "$error");
                }
            } else {
                $this->session->set_flashdata('exception_err', "No podemos aceptar otros archivos, descargue el archivo de muestra 'Customer_Upload.xlsx', complételo correctamente y cárguelo o cambie el nombre del archivo a 'Customer_Upload.xlsx' y luego complételo.");
            }
        } else {
            $this->session->set_flashdata('exception_err', 'File is required');
        }
        redirect('customer/uploadCustomer');
    }
     /**
     * download file
     * @access public
     * @return void
     * @param string
     */
    public function downloadPDF($file = "") {
        // load ci download helder
        $file = $file.".xlsx";
        $this->load->helper('download');
        $data = file_get_contents("asset/sample/" . $file); // Read the file's
        $name = $file;
        force_download($name, $data);
    }
     /**
     * check validate email address
     * @access public
     * @return object
     * @param string
     */
    function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
     /**
     * check valid dat4e
     * @access public
     * @return boolean
     * @param string
     */
    function isValidDate($date){
        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$date)) {
            return true;
        } else {
            return false;
        }
    }

}
