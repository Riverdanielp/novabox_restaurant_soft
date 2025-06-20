<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Outlet extends Cl_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Authentication_model');
        $this->load->model('Common_model');
        $this->load->model('Outlet_model');
        $this->load->model('Sale_model');
        $this->load->library('form_validation');
        $this->Common_model->setDefaultTimezone();

        if (!$this->session->has_userdata('user_id')) {
            redirect('Authentication/index');
        }


        //start check access function
        $segment_2 = $this->uri->segment(2);
        $segment_3 = $this->uri->segment(3);
        $controller = "67";
        $function = "";
       
        if($segment_2=="outlets"){
            $function = "view";
        }elseif($segment_2=="addEditOutlet" && $segment_3){
            $function = "update";
        }elseif($segment_2=="setOutletSession" && $segment_3){
            $function = "enter";
        }elseif($segment_2=="addEditOutlet"){
            $function = "add";
        }elseif($segment_2=="deleteOutlet"){
            $function = "delete";
        }elseif($segment_2=="phpinfo"){
            $function = "phpinfo";
        }else{
           
            $this->session->set_flashdata('exception_er', lang('menu_not_permit_access'));
            redirect('Authentication/userProfile');
        }
        if(!checkAccess($controller,$function)){
            $this->session->set_flashdata('exception_er', lang('menu_not_permit_access'));
            redirect('Authentication/userProfile');
        }
        //end check access function
    }

    public function phpinfo() {
        phpinfo();
    }
    /**
     * outlets info
     * @access public
     * @return void
     * @param no
     */
    public function outlets() {
        //unset outlet data
        $language_manifesto = $this->session->userdata('language_manifesto');

        if(str_rot13($language_manifesto)=="fgjgldkfg"){
            $outlet_id = $this->session->userdata('outlet_id');
            redirect("Outlet/addEditOutlet/".$outlet_id);
        }
        $data = array();
        $data['outlets'] = $this->Common_model->getAllOutlestByAssign();
        $data['main_content'] = $this->load->view('outlet/outlets', $data, TRUE);
        $this->load->view('userHome', $data);
    }
    /**
     * delete Outlet
     * @access public
     * @return void
     * @param int
     */
    public function deleteOutlet($id) {
        $id = $this->custom->encrypt_decrypt($id, 'decrypt');

        $this->Common_model->deleteStatusChange($id, "tbl_outlets");

        $this->session->set_flashdata('exception',lang('delete_success'));
        redirect('Outlet/outlets');
    }
    /**
     * add/Edit Outlet
     * @access public
     * @return void
     * @param int
     */
    public function addEditOutlet($encrypted_id = "") {

        if(isServiceAccessOnly('sGmsJaFJE')){
            if($encrypted_id==''){
                if(!checkCreatePermissionOutlet()){
                    $data_c = getLanguageManifesto();
                    $this->session->set_flashdata('exception_1',lang('not_permission_outlet_create_error'));
                    redirect($data_c[1]);
                }
            }

        }
        $id = $this->custom->encrypt_decrypt($encrypted_id, 'decrypt');
        $company_id = $this->session->userdata('company_id');
        $language_manifesto = $this->session->userdata('language_manifesto');


        if (htmlspecialcharscustom($this->input->post('submit'))) {
            $this->form_validation->set_rules('outlet_name',lang('outlet_name'), 'required|max_length[50]');
            $this->form_validation->set_rules('address',lang('address'), 'required|max_length[200]');
            $this->form_validation->set_rules('phone', lang('phone'), 'required');
            if(str_rot13($language_manifesto)=="eriutoeri"):
                $this->form_validation->set_rules('default_waiter', lang('Default_Waiter'), 'max_length[11]');
            endif;
            if ($this->form_validation->run() == TRUE) {
                $outlet_info = array();
                $outlet_info['outlet_name'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('outlet_name')));
                $c_address =htmlspecialcharscustom($this->input->post($this->security->xss_clean('address'))); #clean the address
                $outlet_info['address'] = preg_replace("/[\n\r]/"," ",$c_address); #remove new line from address
                $outlet_info['phone'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('phone')));
                $outlet_info['email'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('email')));
                $outlet_info['online_order_module'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('online_order_module')));
                $outlet_info['comanda_required'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('comanda_required')));
                $outlet_info['preimpreso_printer_id'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('preimpreso_printer_id')));
                $outlet_info['registro_ocultar'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('registro_ocultar')));
                $outlet_info['registro_detallado'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('registro_detallado')));
                if(str_rot13($language_manifesto)=="eriutoeri"):
                    $outlet_info['default_waiter'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('default_waiter')));
                    $outlet_info['active_status'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('active_status')));
                endif;
                $this->session->set_userdata($outlet_info);
                if ($id == "") {
                    $outlet_info['company_id'] = $this->session->userdata('company_id');
                    $outlet_info['created_date'] = date("Y-m-d");
                    if(str_rot13($language_manifesto)=="eriutoeri") {
                        $outlet_info['outlet_code'] = htmlspecialcharscustom($this->input->post($this->security->xss_clean('outlet_code')));
                    }
                }
                if ($id == "") {
                    $id = $this->Common_model->insertInformation($outlet_info, "tbl_outlets");
                    $this->session->set_flashdata('exception', lang('insertion_success'));

                    //update user
                    $user_id = $this->session->userdata('user_id');
                    $user_details = $this->Common_model->getDataById($user_id, "tbl_users");
                    $data_user = array();
                    $data_user['outlets'] = isset($user_details->outlets) && $user_details->outlets?$user_details->outlets.",".$id:$id;
                    $login_session['session_outlets'] = $data_user['outlets'];
                    $this->session->set_userdata($login_session);
                    //end update user

                    $this->Common_model->updateInformation($data_user, $user_id, "tbl_users");
                } else {
                    $this->Common_model->updateInformation($outlet_info, $id, "tbl_outlets");
                    $this->session->set_flashdata('exception', lang('update_success'));
                }
                $language_manifesto = $this->session->userdata('language_manifesto');
                if(str_rot13($language_manifesto)=="eriutoeri"):
                    $item_check =$this->input->post($this->security->xss_clean('item_check'));
                    if($item_check){
                        $main_arr = '';
                        $total_selected = sizeof($item_check);
                        $data_price_array = array();
                        $json_data = array();

                        for($i=0;$i<$total_selected;$i++){
                            $main_arr.=$item_check[$i];
                            if($i <= ($total_selected) -1){
                                $main_arr.=",";
                                $name_generate = "price_".$item_check[$i];
                                $price_ta_name_generate = "price_ta_".$item_check[$i];
                                $price_de_name_generate = "price_de_".$item_check[$i];
                                $data_price_array["tmp".$item_check[$i]] = htmlspecialcharscustom($this->input->post($this->security->xss_clean($name_generate)))."||".htmlspecialcharscustom($this->input->post($this->security->xss_clean($price_ta_name_generate)))."||".htmlspecialcharscustom($this->input->post($this->security->xss_clean($price_de_name_generate)));
                            }

                            $field_name = "sale_price_delivery_json".$item_check[$i];
                            $delivery_person_field_name = "delivery_person".$item_check[$i];
                            $del_price_total = $this->input->post($this->security->xss_clean($field_name));
                            $delivery_person_field_name_value = $this->input->post($this->security->xss_clean($delivery_person_field_name));

                            if(isset($del_price_total) && $del_price_total){
                                $tmp_array = array();
                                foreach ($del_price_total as $row => $value_1):
                                    $tmp_array["index_".$delivery_person_field_name_value[$row]] = $value_1;
                                endforeach;
                                $json_data["index_".$item_check[$i]] = json_encode($tmp_array);
                            }

                        }
                        //set food menu for this outlet
                        $data_food_menus['food_menus'] = $main_arr;
                        $data_food_menus['food_menu_prices'] = json_encode($data_price_array);
                        $data_food_menus['delivery_price'] = json_encode($json_data);
                        $this->Common_model->updateInformation($data_food_menus, $id, "tbl_outlets");
                    }
                endif;
                $data_c = getLanguageManifesto();
                redirect($data_c[1]);
            } else {
                if ($id == "") {
                    $data = array();
                    $data['outlet_information'] = $this->Common_model->getDataById($id, "tbl_outlets");
                    $data['deliveryPartners'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_delivery_partners");
                    $data['items'] = $this->Common_model->getFoodMenuForOutlet($company_id, "tbl_food_menus");
                    $data['outlet_code'] = $this->Outlet_model->generateOutletCode();
                    $data['waiters'] = $this->Sale_model->getWaitersForThisCompanyForOutlet($company_id,'tbl_users');
                    $data['printers'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_printers");
                    $data['main_content'] = $this->load->view('outlet/addOutlet', $data, TRUE);
                    $this->load->view('userHome', $data);
                } else {
                    $data = array();
                    $data['encrypted_id'] = $encrypted_id;
                    $data['deliveryPartners'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_delivery_partners");
                    $data['items'] = $this->Common_model->getFoodMenuForOutlet($company_id, "tbl_food_menus");
                    $data['outlet_information'] = $this->Common_model->getDataById($id, "tbl_outlets");
                    $data['waiters'] = $this->Sale_model->getWaitersForThisCompanyForOutlet($company_id,'tbl_users');
                    $data['printers'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_printers");
                    $data['main_content'] = $this->load->view('outlet/editOutlet', $data, TRUE);
                    $this->load->view('userHome', $data);
                }
            }
        } else {
            $language_manifesto = $this->session->userdata('language_manifesto');
            if(str_rot13($language_manifesto)=="fgjgldkfg"){
                $outlet_id = $this->session->userdata('outlet_id');
                if($outlet_id != $id){
                    redirect("Outlet/addEditOutlet/".$outlet_id);
                }
            }
            if ($id == "") {
                $data = array();
                $data['outlet_information'] = $this->Common_model->getDataById($id, "tbl_outlets");
                $data['deliveryPartners'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_delivery_partners");
                $data['items'] = $this->Common_model->getFoodMenuForOutlet($company_id, "tbl_food_menus");
                $data['outlet_code'] = $this->Outlet_model->generateOutletCode();
                $data['waiters'] = $this->Sale_model->getWaitersForThisCompanyForOutlet($company_id,'tbl_users');
                $data['printers'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_printers");
                $data['main_content'] = $this->load->view('outlet/addOutlet', $data, TRUE);
                $this->load->view('userHome', $data);
            } else {
                $data = array();
                $data['encrypted_id'] = $encrypted_id;
                $data['deliveryPartners'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_delivery_partners");
                $data['items'] = $this->Common_model->getFoodMenuForOutlet($company_id, "tbl_food_menus");
                $data['outlet_information'] = $this->Common_model->getDataById($id, "tbl_outlets");
                $data['waiters'] = $this->Sale_model->getWaitersForThisCompanyForOutlet($company_id,'tbl_users');
                $selected_modules =  explode(',',$data['outlet_information']->food_menus);
                $selected_modules_arr = array();
                foreach ($selected_modules as $value) {
                    $selected_modules_arr[] = $value;
                }
                $data['selected_modules_arr'] = $selected_modules_arr;
                $data['printers'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_printers");
                $data['main_content'] = $this->load->view('outlet/editOutlet', $data, TRUE);
                $this->load->view('userHome', $data);
            }
        }
    }
    /**
     * set Outlet Session
     * @access public
     * @return void
     * @param int
     */
    public function setOutletSession($encrypted_id) {
        $outlet_id = $this->custom->encrypt_decrypt($encrypted_id, 'decrypt');
        $language_manifesto = $this->session->userdata('language_manifesto');
        $outlet_details = $this->Common_model->getDataById($outlet_id, 'tbl_outlets');

        $outlet_session = array();
        $outlet_session['outlet_id'] = $outlet_details->id;
        $outlet_session['outlet_name'] = $outlet_details->outlet_name;
        $outlet_session['address'] = $outlet_details->address;
        $outlet_session['phone'] = $outlet_details->phone;
        $outlet_session['email'] = $outlet_details->email;
        $outlet_session['online_order_module'] = $outlet_details->online_order_module;
        $outlet_session['comanda_required'] = $outlet_details->comanda_required;
        $outlet_session['registro_ocultar'] = $outlet_details->registro_ocultar;
        $outlet_session['registro_detallado'] = $outlet_details->registro_detallado;

        if(str_rot13($language_manifesto)=="eriutoeri"):
            $outlet_session['default_waiter'] = $outlet_details->default_waiter;
        else:
            $setting = getCompanyInfo();
            $outlet_session['default_waiter'] = $setting->default_waiter;
        endif;
        $this->session->set_userdata($outlet_session);
        
        if (!$this->session->has_userdata('clicked_controller')) {
            
            if ($this->session->userdata('designation') == 'Super Admin') {
                redirect('Dashboard/dashboard');
            } else if ($this->session->userdata('designation') == 'Admin') {
                redirect('Dashboard/dashboard');
            } else if($this->session->userdata('designation') == 'Chef') {
                redirect('Kitchen/kitchens');
            } else {
               redirect('POSChecker/posAndWaiterMiddleman');
            }
        } else {
            $clicked_controller = $this->session->userdata('clicked_controller');
            $clicked_method = $this->session->userdata('clicked_method');
            $this->session->unset_userdata('clicked_controller');
            $this->session->unset_userdata('clicked_method');
            if($clicked_method=="get_new_notifications_ajax"){
                redirect('POSChecker/posAndWaiterMiddleman');
            }else{
                redirect($clicked_controller . '/' . $clicked_method);
            }
            
        }
    }
}
