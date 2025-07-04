<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class FoodMenu extends Cl_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('excel'); //load PHPExcel library
        $this->load->model('Common_model');
        $this->load->model('Master_model');
        $this->load->model('Food_menu_model');
        $this->load->library('form_validation');
        $this->Common_model->setDefaultTimezone();

        if (!$this->session->has_userdata('user_id')) {
            redirect('Authentication/index');
        }

        //start check access function
        $segment_2 = $this->uri->segment(2);
        $segment_3 = $this->uri->segment(3);
        $controller = "234";
        $function = "";

        if($segment_2=="foodMenus" ||
            $segment_2=="ajax_list" ||
            $segment_2=="ajax_food_menus" ||
            $segment_2=="ajax_ingredients" ||
            $segment_2=="conf_rapida" ||
            $segment_2=="get_categories_ajax" ||
            $segment_2=="update_field_ajax" ||
            $segment_2=="batch_update_field_ajax" ||
            $segment_2=="export_balanza_txt" 
         ){
            $function = "view";
        }elseif($segment_2=="addEditFoodMenu" && $segment_3){
            $function = "update";
        }elseif($segment_2=="addEditFoodMenu"){
            $function = "add";
        }elseif($segment_2=="foodMenuBarcode"){
            $function = "item_barcode";
        }elseif($segment_2=="uploadFoodMenu" || $segment_2=="ExcelDataAddFoodmenus" || $segment_2=="downloadPDF"){
            $function = "upload_food_menu";
        }elseif($segment_2=="uploadFoodMenuIngredients" || $segment_2=="ExcelDataAddFoodmenusIngredients" || $segment_2=="downloadPDF"){
            $function = "upload_food_menu_ingredients";
        }elseif($segment_2=="assignFoodMenuModifier"){
            $function = "assign_modifier";
        }elseif($segment_2=="foodMenuDetails"){
            $function = "view_details";
        }elseif($segment_2=="deleteFoodMenu"){
            $function = "delete";
        }elseif($segment_2=="assign"){
            $function = "assign";
        }elseif($segment_2=="assign_submit"){
            $function = "assign_submit";
        }else{
            $this->session->set_flashdata('exception_er', lang('menu_not_permit_access'));
            redirect('Authentication/userProfile');
        }

        if($segment_2=="downloadPDF"){

        }elseif($segment_2=="assign" || $segment_2=="assign_submit" ){

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
     * food menu info
     * @access public
     * @return void
     * @param no
     */
    public function foodMenus() {
        $company_id = $this->session->userdata('company_id');
        $data['categories'] = $this->Common_model->getAllByCompanyId($company_id, 'tbl_food_menu_categories');
        $data['foodMenus'] = [];
        $data['main_content'] = $this->load->view('master/foodMenu/foodMenus', $data, TRUE);
        $this->load->view('userHome', $data);
    }

    public function conf_rapida() {
        $company_id = $this->session->userdata('company_id');
    
        $category_id = $this->input->get('category_id');
        $solo_balanza = $this->input->get('balanza');
    
        // Filtros combinados
        if (!empty($category_id) && $solo_balanza == 1) {
            $data['foodMenus'] = $this->Common_model->getAllFoodMenusByCategoryBalanza($category_id, $company_id,'code');
        } elseif (!empty($category_id)) {
            $data['foodMenus'] = $this->Common_model->getAllFoodMenusByCategory($category_id, 'tbl_food_menus','code','asc');
        } elseif ($solo_balanza == 1) {
            $data['foodMenus'] = $this->Common_model->getAllFoodMenusBalanza($company_id,'code');
        } else {
            $data['foodMenus'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_food_menus",'code');
        }
    
        $data['categories'] = $this->Common_model->getAllByCompanyId($company_id, 'tbl_food_menu_categories');
        $data['main_content'] = $this->load->view('master/foodMenu/conf_rapida', $data, TRUE);
        $this->load->view('userHome', $data);
    }
    
    public function foodMenusOld() {
        $company_id = $this->session->userdata('company_id');

        if (htmlspecialcharscustom($this->input->post('submit'))) {
            $this->form_validation->set_rules('category_id', lang('category'), 'required|max_length[50]');
            if ($this->form_validation->run() == TRUE) {
                $category_id =htmlspecialcharscustom($this->input->post($this->security->xss_clean('category_id')));
                $data = array();
                $data['foodMenus'] = $this->Common_model->getAllFoodMenusByCategory($category_id, "tbl_food_menus");
                foreach ($data['foodMenus'] as $key=>$value){
                    $data['foodMenus'][$key]->variations = $this->Common_model->getAllByCustomId($value->id,"parent_id","tbl_food_menus",$order='');
                }
                $data['foodMenuCategories'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, "tbl_food_menu_categories");
                $data['main_content'] = $this->load->view('master/foodMenu/foodMenus', $data, TRUE);
                $this->load->view('userHome', $data);
            } else {
                $data = array();

                $data['foodMenus'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_food_menus");
                $data['foodMenuCategories'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_food_menu_categories");
            
                    foreach($data['foodMenuCategories'] as $key=>$value){
                        $total_counter = getTotalFoodMenu($value->id);
                        $data['foodMenuCategories'][$key]->total_item = $total_counter;
                    }
                $data['main_content'] = $this->load->view('master/foodMenu/foodMenus', $data, TRUE);
                $this->load->view('userHome', $data);
            }
        } else {
            $data = array();
            $data['foodMenus'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_food_menus");
             
            $data['foodMenuCategories'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_food_menu_categories");
            
            foreach($data['foodMenuCategories'] as $key1=>$value){
                $total_counter = getTotalFoodMenu($value->id);
                $data['foodMenuCategories'][$key1]->total_item = $total_counter;
            }
            $data['main_content'] = $this->load->view('master/foodMenu/foodMenus', $data, TRUE);
            $this->load->view('userHome', $data);
        }
    }

    // Endpoint para DataTables server-side
    public function ajax_list() {
        $company_id = $this->session->userdata('company_id');
        $category_id = $this->input->get('category_id');
        $draw = intval($this->input->get('draw'));
        $start = intval($this->input->get('start'));
        $length = intval($this->input->get('length'));
        $search_value = $this->input->get('search')['value'];

        $result = $this->Food_menu_model->get_datatables($company_id, $category_id, $start, $length, $search_value);
        $total = $this->Food_menu_model->count_all($company_id, $category_id);
        $filtered = $this->Food_menu_model->count_filtered($company_id, $category_id, $search_value);

        $data = [];
        $i = $start + 1;
        foreach ($result as $row) {
            $img_size = "images/".$row->photo;
            $image_path = (file_exists($img_size) && $row->photo!="") ? base_url().'images/'.$row->photo : base_url().'images/image_thumb.png';

            $data[] = [
                $i++,
                '',
                '<img src="'.$image_path.'" class="img-port" alt="'.escape_output($row->name).'">',
                escape_output($row->code),
                escape_output($row->name),
                escape_output(getFoodMenuCateCodeById($row->category_id)),
                escape_output(getAmtPCustom($row->sale_price)),
                // escape_output(getAlternativeNameById($row->id)),
                // escape_output(userName($row->user_id)),
                escape_output($row->description),
                '<div class="btn_group_wrap">
                    <a class="btn btn-warning" href="'.base_url().'foodMenu/addEditFoodMenu/'.escape_output($this->custom->encrypt_decrypt($row->id, "encrypt")).'" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="'.lang('edit').'">
                        <i class="far fa-edit"></i>
                    </a>
                    <a class="delete btn btn-danger" href="'.base_url().'foodMenu/deleteFoodMenu/'.escape_output($this->custom->encrypt_decrypt($row->id, "encrypt")).'" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="'.lang('delete').'">
                        <i class="fa-regular fa-trash-can"></i>
                    </a>
                    <a class="btn btn-info" href="'.base_url().'foodMenu/assignFoodMenuModifier/'.escape_output($this->custom->encrypt_decrypt($row->id, "encrypt")).'" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="'.lang('assign_modifier').'">
                        <i class="far fa-plus"></i>
                    </a>
                </div>'
            ];
        }

        echo json_encode([
            "draw" => $draw,
            "recordsTotal" => $total,
            "recordsFiltered" => $filtered,
            "data" => $data
        ]);
        exit;
    }

     /**
     * delete food menu
     * @access public
     * @return void
     * @param int
     */
    public function deleteFoodMenu($id) {
        $id = $this->custom->encrypt_decrypt($id, 'decrypt');
        //check and remove product type ingredient
        $this->db->select('*');
        $this->db->from('tbl_ingredients');
        $this->db->where('food_id', $id);
        $this->db->where('del_status', 'Live');
        $query_result = $this->db->get();
        $selected_row = $query_result->row();
        if($selected_row){
            $this->Common_model->deleteStatusChange($selected_row->id, "tbl_ingredients");
        }

        $this->Common_model->deleteStatusChangeWithChild($id, $id, "tbl_food_menus", "tbl_food_menus_ingredients", 'id', 'food_menu_id');
        $this->Common_model->deleteStatusChangeWithChild($id, $id, "tbl_food_menus", "tbl_food_menus_modifiers", 'id', 'food_menu_id');
        $this->Common_model->deleteStatusChangeWithChild($id, $id, "tbl_food_menus", "tbl_food_menus", 'id', 'parent_id');
        $this->Common_model->deletingMultipleFormData('food_menu_id', $id, 'tbl_food_menus_ingredients');
        $this->Common_model->deletingMultipleFormData('food_menu_id', $id, 'tbl_combo_food_menus');
        $this->session->set_flashdata('exception', lang('delete_success'));
        redirect('foodMenu/foodMenus');
    }
     /**
     * add/edit food menu
     * @access public
     * @return int
     * @param int
     */

    public function get_unit_id($ingredint_unit) {
        $company_id = $this->session->userdata('company_id');
        $user_id = $this->session->userdata('user_id');
        $id = $this->db->query("SELECT id FROM tbl_units WHERE company_id=$company_id and unit_name='" . $ingredint_unit . "'")->row('id');
        if ($id != '') {
            return $id;
        } else {
            $data = array('unit_name' => $ingredint_unit, 'company_id' => $company_id);
            $query = $this->db->insert('tbl_units', $data);
            $id = $this->db->insert_id();
            return $id;
        }
    }
    public function addEditFoodMenu($encrypted_id = "") {
        $id = $this->custom->encrypt_decrypt($encrypted_id, 'decrypt');
        $company_id = $this->session->userdata('company_id');
        if (htmlspecialcharscustom($this->input->post('submit'))) {
            $product_type = htmlspecialcharscustom($this->input->post($this->security->xss_clean('product_type')));

            $tax_information = array();
            $tax_string ='';
            if(!empty($_POST['tax_field_percentage'])){
                foreach($this->input->post('tax_field_percentage') as $key=>$value){
                    $single_info = array(
                        'tax_field_id' => $this->input->post('tax_field_id')[$key],
                        'tax_field_company_id' => $this->input->post('tax_field_company_id')[$key],
                        'tax_field_name' => $this->input->post('tax_field_name')[$key],
                        'tax_field_percentage' => ($this->input->post('tax_field_percentage')[$key]=="")?0:$this->input->post('tax_field_percentage')[$key]
                    );
                    $tax_string.=($this->input->post('tax_field_name')[$key]).":";
                    array_push($tax_information,$single_info);
                }
            }
            $tax_information = json_encode($tax_information);
            $this->form_validation->set_rules('name', lang('name'), 'required|max_length[50]');
            $this->form_validation->set_rules('category_id', lang('category'), 'required|max_length[50]');
            $this->form_validation->set_rules('veg_item', lang('is_it_veg'), 'required|max_length[50]');
            $this->form_validation->set_rules('beverage_item', lang('is_it_beverage'), 'required|max_length[50]');
            $this->form_validation->set_rules('description', lang('description'), 'max_length[200]');
            $this->form_validation->set_rules('sale_price', lang('sale_price')." ".lang('dine'), 'required|max_length[50]');
            $this->form_validation->set_rules('sale_price_take_away', lang('sale_price')." ".lang('take_away'), 'required|max_length[50]');
            if ($_FILES['photo']['name'] != "") {
                $this->form_validation->set_rules('photo', lang('photo'), 'callback_validate_photo');
            }

            if($product_type==3){
                $this->form_validation->set_rules('purchase_price', lang('purchase_price'), 'required|numeric|max_length[15]');
                $this->form_validation->set_rules('alert_quantity',lang('alert_quantity'), 'required|numeric|max_length[15]');
                $this->form_validation->set_rules('ing_category_id',lang('ingredient')." ".lang('category'), 'required|numeric|max_length[15]');
            }
            if ($this->form_validation->run() == TRUE) {
                $food_menu_info = array();
                $food_menu_info['product_type'] = htmlspecialcharscustom($this->input->post($this->security->xss_clean('product_type')));
                $food_menu_info['name'] = htmlspecialcharscustom($this->input->post($this->security->xss_clean('name')));
                $food_menu_info['alternative_name'] = htmlspecialcharscustom($this->input->post($this->security->xss_clean('alternative_name')));
                $food_menu_info['code'] = htmlspecialcharscustom($this->input->post($this->security->xss_clean('code')));
                $food_menu_info['category_id'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('category_id')));
                $food_menu_info['veg_item'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('veg_item')));
                $food_menu_info['beverage_item'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('beverage_item')));
                $food_menu_info['description'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('description')));
                $food_menu_info['sale_price'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('sale_price')));
                $food_menu_info['sale_price_take_away'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('sale_price_take_away')));
                $food_menu_info['sale_price_delivery'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('sale_price_delivery')));
                if($product_type==3) {
                    $food_menu_info['total_cost'] = htmlspecialcharscustom($this->input->post($this->security->xss_clean('purchase_price')));
                }else{
                    $food_menu_info['total_cost'] = htmlspecialcharscustom($this->input->post($this->security->xss_clean('grand_total_cost')));
                }
                $food_menu_info['loyalty_point'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('loyalty_point')));
                //this fields for product category
                    $food_menu_info['purchase_price'] = htmlspecialcharscustom($this->input->post($this->security->xss_clean('purchase_price')));
                    $food_menu_info['alert_quantity'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('alert_quantity')));
                    $food_menu_info['ing_category_id'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('ing_category_id')));
                //end
                $food_menu_info['tax_information'] = $tax_information;
                $food_menu_info['tax_string'] = $tax_string;
                $food_menu_info['user_id'] = $this->session->userdata('user_id');
                $food_menu_info['company_id'] = $this->session->userdata('company_id');

                //This variable could not be escaped because this is an array field
                $delivery_person = $this->input->post($this->security->xss_clean('delivery_person'));
                $json_data = array();
                if(isset($delivery_person) && $delivery_person){
                    foreach ($delivery_person as $row => $value):
                        $json_data["index_".$value] = $_POST['sale_price_delivery_json'][$row];
                    endforeach;
                }
                $food_menu_info['delivery_price'] = json_encode($json_data);

                if ($_FILES['photo']['name'] != "") {
                    $food_menu_info['photo'] = $this->session->userdata('photo');
                    $this->session->unset_userdata('photo');
                }
                if ($id == "") {
                    $id = $this->Common_model->insertInformation($food_menu_info, "tbl_food_menus");
                    if (isset($_POST['ingredient_id'])){
                        $this->saveFoodMenusIngredients($_POST['ingredient_id'], $id, 'tbl_food_menus_ingredients');
                    }
                    $data['autoCode'] = $this->Master_model->generateFoodMenuCode();
                    $this->session->set_flashdata('exception',lang('insertion_success'));
                    if(isLMni()):
                        updatePrice($this->session->userdata('company_id'),$id,$food_menu_info['sale_price'],$food_menu_info['sale_price_take_away'],json_encode($json_data),$food_menu_info['sale_price_delivery']);
                    endif;
                } else {
                    $this->Common_model->updateInformation($food_menu_info, $id, "tbl_food_menus");
                    if(isLMni()):
                        updatePrice($this->session->userdata('company_id'),$id,$food_menu_info['sale_price'],$food_menu_info['sale_price_take_away'],json_encode($json_data),$food_menu_info['sale_price_delivery']);
                    endif;
                    $this->Common_model->deletingMultipleFormData('food_menu_id', $id, 'tbl_food_menus_ingredients');
                    $this->Common_model->deleteStatusChangeWithCustom($id, "parent_id", "tbl_food_menus");

                    $data['autoCode'] = $this->Master_model->generateFoodMenuCode();
                    if (isset($_POST['ingredient_id'])){
                        $this->saveFoodMenusIngredients($_POST['ingredient_id'], $id, 'tbl_food_menus_ingredients');
                    }
                    $this->session->set_flashdata('exception', lang('update_success'));
                }


                $vr_tax_counter = $this->input->post($this->security->xss_clean('vr_tax_counter'));
                $is_variation = 0;
                if(isset($vr_tax_counter) && $vr_tax_counter){
                    $is_variation = 1;
                    if(!empty($_POST['vr_tax_counter'])){
                        $i = 1;
                        foreach($this->input->post('vr_tax_counter') as $key=>$value){
                            $vr01_tax_field_id = "vr01_tax_field_id".$i;
                            $vr01_tax_field_company_id = "vr01_tax_field_company_id".$i;
                            $vr01_tax_field_name = "vr01_tax_field_name".$i;
                            $vr01_tax_field_percentage = "vr01_tax_field_percentage".$i;

                            $tax_information_variation = array();
                            $tax_string ='';

                            foreach($this->input->post($vr01_tax_field_percentage) as $key1=>$value1){
                                $single_info = array(
                                    'tax_field_id' => $this->input->post($vr01_tax_field_id)[$key1],
                                    'tax_field_company_id' => $this->input->post($vr01_tax_field_company_id)[$key1],
                                    'tax_field_name' => $this->input->post($vr01_tax_field_name)[$key1],
                                    'tax_field_percentage' => ($this->input->post($vr01_tax_field_percentage)[$key1]=="")?0:$this->input->post($vr01_tax_field_percentage)[$key1]
                                );
                                $tax_string.=($this->input->post($vr01_tax_field_name)[$key1]).":";
                                array_push($tax_information_variation,$single_info);
                            }


                            $food_menu_info_vr = array();
                            $food_menu_info_vr['name'] = $this->input->post('variation_name')[$key];
                            $food_menu_info_vr['alternative_name'] = $this->input->post('alternative_name_variation')[$key];
                            $food_menu_info_vr['total_cost'] = $this->input->post('var01_grand_total_cost_arr')[$key];
                            $food_menu_info_vr['code'] = $food_menu_info['code']."-".(generateCode($i));
                            $food_menu_info_vr['category_id'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('category_id')));
                            $food_menu_info_vr['veg_item'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('veg_item')));
                            $food_menu_info_vr['beverage_item'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('beverage_item')));
                            $food_menu_info_vr['description'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('description')));
                            $food_menu_info_vr['sale_price'] =$this->input->post('m_sale_price')[$key];
                            $food_menu_info_vr['sale_price_take_away'] =$this->input->post('m_sale_price_take_away')[$key];
                            $food_menu_info_vr['sale_price_delivery'] =$this->input->post('m_sale_price_delivery')[$key];
                            $food_menu_info_vr['vr_ingr'] =$this->input->post('variation_ingrs')[$key];
                            $food_menu_info_vr['vr_del_details'] =$this->input->post('hidden_delivery_html')[$key];
                            $food_menu_info_vr['loyalty_point'] =$this->input->post('vr01_loyalty_point_arr')[$key];
                            $food_menu_info_vr['tax_information'] = json_encode($tax_information_variation);
                            $food_menu_info_vr['tax_string'] = $tax_string;
                            $food_menu_info_vr['user_id'] = $this->session->userdata('user_id');
                            $food_menu_info_vr['company_id'] = $this->session->userdata('company_id');
                            $food_menu_info_vr['photo'] =  $food_menu_info['photo'];
                            $food_menu_info_vr['parent_id'] =  $id;
                            $food_menu_info_vr['del_status'] =  "Live";

                            $hidden_delivery_html = $this->input->post($this->security->xss_clean('hidden_delivery_html'));
                            $json_data = array();

                            if(isset($hidden_delivery_html) && $hidden_delivery_html){
                                $variation_ingrs_total = explode('|||',$hidden_delivery_html[0]);
                                foreach ($variation_ingrs_total as $row => $value_1):
                                    $data_value = explode("||",$variation_ingrs_total[$row]);
                                    $json_data["index_".$data_value[1]] = $data_value[0];
                                endforeach;
                            }
                            $food_menu_info_vr['delivery_price'] = json_encode($json_data);
                            $i++;
                            $old_id = $this->input->post('variation_row_update')[$key];
                            if(isset($old_id) && $old_id){
                                $new_variation_id = $this->Common_model->updateInformation($food_menu_info_vr, $old_id, "tbl_food_menus");
                                if(isLMni()):
                                    updatePrice($this->session->userdata('company_id'),$old_id,$this->input->post('m_sale_price')[$key],$this->input->post('m_sale_price_take_away')[$key],$food_menu_info_vr['delivery_price'],$food_menu_info_vr['sale_price_delivery']);
                                endif;
                            }else{
                                $new_variation_id = $this->Common_model->insertInformation($food_menu_info_vr, "tbl_food_menus");
                                if(isLMni()):
                                    updatePrice($this->session->userdata('company_id'),$new_variation_id,$this->input->post('m_sale_price')[$key],$this->input->post('m_sale_price_take_away')[$key],$food_menu_info_vr['delivery_price'],$food_menu_info_vr['sale_price_delivery']);
                                endif;
                            }

                            $this->Common_model->deletingMultipleFormData('food_menu_id', $new_variation_id, 'tbl_food_menus_ingredients');


                            $variation_ingrs = $this->input->post($this->security->xss_clean('variation_ingrs'));

                            if(isset($variation_ingrs) && $variation_ingrs){
                                $variation_ingrs_arr = explode("|||",$variation_ingrs[$key]);
                                if(isset($variation_ingrs_arr) && $variation_ingrs_arr){
                                    foreach ($variation_ingrs_arr as $row => $single_array):
                                        $single_array_arr = explode("||",$single_array);
                                    if(isset($single_array_arr[1]) && $single_array_arr[1]){
                                        $fmi = array();
                                        $fmi['ingredient_id'] = $single_array_arr[1];
                                        $fmi['consumption'] = $single_array_arr[3];
                                        $fmi['cost'] = $single_array_arr[4];
                                        $fmi['total'] = $single_array_arr[5];
                                        $fmi['food_menu_id'] = $new_variation_id;
                                        $fmi['user_id'] = $this->session->userdata('user_id');
                                        $fmi['company_id'] = $this->session->userdata('company_id');
                                        if(isset($single_array_arr[1]) && $single_array_arr[1]){
                                            $this->Common_model->insertInformation($fmi, "tbl_food_menus_ingredients");
                                        }
                                    }
                                    endforeach;
                                }

                            }
                        }
                    }
                }

                $this->Common_model->deletingMultipleFormData('food_menu_id', $id, 'tbl_combo_food_menus');


                $food_menu_id_hidden = $this->input->post($this->security->xss_clean('food_menu_id_hidden'));
                $combo_ids = '';
                if(isset($food_menu_id_hidden) && $food_menu_id_hidden && $product_type==2){
                    foreach ($food_menu_id_hidden as $row => $single_array):
                        $single_array_arr = explode("||",$single_array);
                        $fmi = array();
                        $fmi['name'] = trim_checker($single_array_arr[1]);
                        $fmi['food_menu_id'] = $id;
                        $fmi['added_food_menu_id'] = $single_array_arr[0];
                        $fmi['quantity'] = $_POST['qty_food_menu'][$row];
                        $fmi['user_id'] = $this->session->userdata('user_id');
                        $fmi['company_id'] = $this->session->userdata('company_id');
                        $this->Common_model->insertInformation($fmi, "tbl_combo_food_menus");
                        $combo_ids.=$single_array_arr[0];
                        $combo_ids.=",";
                    endforeach;
                }
                $data_combo_ids['is_variation'] = $is_variation;
                $data_combo_ids['combo_ids'] = $combo_ids;
                $this->Common_model->updateInformation($data_combo_ids, $id, "tbl_food_menus");

                if($product_type==3){
                    // Busca el ingrediente asociado
                    $this->db->select('*');
                    $this->db->from('tbl_ingredients');
                    $this->db->where('food_id', $id);
                    $this->db->where('del_status', 'Live');
                    $ingredient = $this->db->get()->row();
                
                    // Usa el mismo código del ingrediente si ya existe, si no genera uno nuevo
                    $ingredient_code =  htmlspecialcharscustom($this->input->post($this->security->xss_clean('code')));
                
                    $fmc_info = array();
                    $fmc_info['name'] = htmlspecialcharscustom($this->input->post($this->security->xss_clean('name')));
                    $fmc_info['code'] = $ingredient_code;
                    $fmc_info['purchase_price'] = htmlspecialcharscustom($this->input->post($this->security->xss_clean('purchase_price')));
                    $fmc_info['alert_quantity'] = htmlspecialcharscustom($this->input->post($this->security->xss_clean('alert_quantity')));
                    $fmc_info['unit_id'] = $this->get_unit_id("u.");
                    $fmc_info['purchase_unit_id'] = $this->get_unit_id("u.");
                    $fmc_info['consumption_unit_cost'] = htmlspecialcharscustom($this->input->post($this->security->xss_clean('purchase_price')));
                    $fmc_info['category_id'] = htmlspecialcharscustom($this->input->post($this->security->xss_clean('ing_category_id')));
                    $fmc_info['conversion_rate'] = 1;
                    $fmc_info['is_direct_food'] = 2;
                    $fmc_info['food_id'] = $id;
                    $fmc_info['user_id'] = $this->session->userdata('user_id');
                    $fmc_info['company_id'] = $this->session->userdata('company_id');
                    $fmc_info['sale_price'] = htmlspecialcharscustom($this->input->post($this->security->xss_clean('sale_price')));
                    setIngredients($id,$fmc_info);
                }

                redirect('foodMenu/foodMenus');
            } else {
                if ($id == "") {
                    $data = array();
                    $data['ing_categories'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, 'tbl_ingredient_categories');
                    $data['categories'] = $this->Common_model->getAllByCompanyId($company_id, 'tbl_food_menu_categories');
                    $data['autoCode'] = $this->Master_model->generateFoodMenuCode();
                    $data['ingredients'] = $this->Master_model->getIngredientListWithUnit($company_id);
                    $data['food_menus'] = $this->Common_model->getFoodMenuWithVariations($company_id, 'tbl_food_menus');
                    foreach ($data['food_menus'] as $key=>$value){
                        $variations = $this->Common_model->getAllByCustomId($value->id,"parent_id","tbl_food_menus",$order='');
                        $data['food_menus'][$key]->is_variation = isset($variations) && $variations?'Yes':'No';
                    }
                    $data['deliveryPartners'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_delivery_partners");
                    $data['main_content'] = $this->load->view('master/foodMenu/addFoodMenu', $data, TRUE);
                    $this->load->view('userHome', $data);
                } else {
                    $data = array();
                    $data['ing_categories'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, 'tbl_ingredient_categories');
                    $data['encrypted_id'] = $encrypted_id;
                    $data['categories'] = $this->Common_model->getAllByCompanyId($company_id, 'tbl_food_menu_categories');
                    $data['autoCode'] = $this->Master_model->generateFoodMenuCode();
                    $data['ingredients'] = $this->Master_model->getIngredientListWithUnit($company_id);
                     $data['food_menus'] = $this->Common_model->getFoodMenuWithVariations($company_id, 'tbl_food_menus');
                    foreach ($data['food_menus'] as $key=>$value){
                        $variations = $this->Common_model->getAllByCustomId($value->id,"parent_id","tbl_food_menus",$order='');
                        $data['food_menus'][$key]->is_variation = isset($variations) && $variations?'Yes':'No';
                    }
                    $data['food_menu_details'] = $this->Common_model->getDataById($id, "tbl_food_menus");
                    // Busca el ingrediente asociado si product_type == 3
                    if ($data['food_menu_details']->product_type == 3) {
                        $this->db->select('*');
                        $this->db->from('tbl_ingredients');
                        $this->db->where('food_id', $data['food_menu_details']->id);
                        $this->db->where('del_status', 'Live');
                        $ingredient = $this->db->get()->row();
                    
                        if ($ingredient) {
                            $data['ingredient_purchase_price'] = $ingredient->purchase_price;
                            $data['ingredient_alert_quantity'] = $ingredient->alert_quantity;
                            $data['ingredient_ing_category_id'] = $ingredient->category_id;
                        } else {
                            // En caso de que aún no tenga ingrediente asociado
                            $data['ingredient_purchase_price'] = '';
                            $data['ingredient_alert_quantity'] = '';
                            $data['ingredient_ing_category_id'] = '';
                        }
                    } else {
                        $ingredient = [];
                        $data['ingredient_purchase_price'] = '';
                        $data['ingredient_alert_quantity'] = '';
                        $data['ingredient_ing_category_id'] = '';
                    }
                    $data['ingredient'] = $ingredient;
                    $data['variation_food_menus'] = $this->Common_model->getAllByCustomId($id,"parent_id","tbl_food_menus",$order='');
                    $data['added_combo_menus'] = $this->Common_model->getAllByCustomId($id,"food_menu_id","tbl_combo_food_menus",$order='');
                    $data['deliveryPartners'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_delivery_partners");
                    $data['food_menu_ingredients'] = $this->Master_model->getFoodMenuIngredients($data['food_menu_details']->id);
                    $data['main_content'] = $this->load->view('master/foodMenu/editFoodMenu', $data, TRUE);
                    $this->load->view('userHome', $data);
                }
            }
        } else {
            if ($id == "") {
                $data = array();
                $data['ing_categories'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, 'tbl_ingredient_categories');
                $data['categories'] = $this->Common_model->getAllByCompanyId($company_id, 'tbl_food_menu_categories');
                $data['autoCode'] = $this->Master_model->generateFoodMenuCode();
                $data['ingredients'] = []; //$this->Master_model->getIngredientListWithUnit($company_id);
                 $data['food_menus'] = []; // $this->Common_model->getFoodMenuWithVariations($company_id, 'tbl_food_menus');
                    foreach ($data['food_menus'] as $key=>$value){
                        $variations = $this->Common_model->getAllByCustomId($value->id,"parent_id","tbl_food_menus",$order='');
                        $data['food_menus'][$key]->is_variation = isset($variations) && $variations?'Yes':'No';
                    }
                $data['deliveryPartners'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_delivery_partners");
                $data['main_content'] = $this->load->view('master/foodMenu/addFoodMenu', $data, TRUE);
                $this->load->view('userHome', $data);
            } else {
                $data = array();
                $data['ing_categories'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, 'tbl_ingredient_categories');
                $data['encrypted_id'] = $encrypted_id;
                $data['categories'] = $this->Common_model->getAllByCompanyId($company_id, 'tbl_food_menu_categories');
                $data['ingredients'] = []; // $this->Master_model->getIngredientListWithUnit($company_id);
                 $data['food_menus'] = []; // $this->Common_model->getFoodMenuWithVariations($company_id, 'tbl_food_menus');
                    foreach ($data['food_menus'] as $key=>$value){
                        $variations = $this->Common_model->getAllByCustomId($value->id,"parent_id","tbl_food_menus",$order='');
                        $data['food_menus'][$key]->is_variation = isset($variations) && $variations?'Yes':'No';
                    }
                $data['autoCode'] = $this->Master_model->generateFoodMenuCode();
                $data['food_menu_details'] = $this->Common_model->getDataById($id, "tbl_food_menus");
                // Busca el ingrediente asociado si product_type == 3
                if ($data['food_menu_details']->product_type == 3) {
                    $this->db->select('*');
                    $this->db->from('tbl_ingredients');
                    $this->db->where('food_id', $data['food_menu_details']->id);
                    $this->db->where('del_status', 'Live');
                    $ingredient = $this->db->get()->row();
                
                    if ($ingredient) {
                        $data['ingredient_purchase_price'] = $ingredient->purchase_price;
                        $data['ingredient_alert_quantity'] = $ingredient->alert_quantity;
                        $data['ingredient_ing_category_id'] = $ingredient->category_id;
                    } else {
                        // En caso de que aún no tenga ingrediente asociado
                        $data['ingredient_purchase_price'] = '';
                        $data['ingredient_alert_quantity'] = '';
                        $data['ingredient_ing_category_id'] = '';
                    }
                } else {
                    $ingredient = [];
                    $data['ingredient_purchase_price'] = '';
                    $data['ingredient_alert_quantity'] = '';
                    $data['ingredient_ing_category_id'] = '';
                }
                $data['ingredient'] = $ingredient;
                $data['variation_food_menus'] = $this->Common_model->getAllByCustomId($id,"parent_id","tbl_food_menus",$order='');
                $data['added_combo_menus'] = $this->Common_model->getAllByCustomId($id,"food_menu_id","tbl_combo_food_menus",$order='');
                $data['food_menu_ingredients'] = $this->Master_model->getFoodMenuIngredients($data['food_menu_details']->id);
                $data['deliveryPartners'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_delivery_partners");
                $data['main_content'] = $this->load->view('master/foodMenu/editFoodMenu', $data, TRUE);
                $this->load->view('userHome', $data);
            }
        }
    }

    public function foodMenuBarcode() {
        $company_id = $this->session->userdata('company_id');
        $outlet_id = $this->session->userdata('outlet_id');
        if (htmlspecialcharscustom($this->input->post('submit'))) {
            $food_menu_id = $this->input->post($this->security->xss_clean('food_menu_id'));
            $qty = $this->input->post($this->security->xss_clean('qty'));
            $barcode_width = $this->input->post($this->security->xss_clean('barcode_width'));
            $barcode_height = $this->input->post($this->security->xss_clean('barcode_height'));
            $arr = array();
            if($food_menu_id){
                for ($i=0;$i<sizeof($food_menu_id);$i++){
                    $value = explode("|",$food_menu_id[$i]);
                    $arr[] = array(
                        'id' => $value[0],
                        'item_name' => $value[1],
                        'code' => $value[2],
                        'sale_price' => $value[3],
                        'qty' => $qty[$i]
                    );
                }
            }
            $data = array();
            $data['items'] = $arr;
            $data['barcode_width'] = $barcode_width;
            $data['barcode_height'] = $barcode_height;
            $data['main_content'] = $this->load->view('master/foodMenu/barcode_preview', $data, TRUE);
            $this->load->view('userHome', $data);
        } else {
            $data = array();
            $data['outlet_information'] = $this->Common_model->getDataById($outlet_id, "tbl_outlets");
            $data['foodMenus'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_food_menus");
            $data['main_content'] = $this->load->view('master/foodMenu/foodMenuBarcodeGenerator', $data, TRUE);
            $this->load->view('userHome', $data);
        }

    }
     /**
     * validate photo
     * @access public
     * @return string
     * @param no
     */
    public function validate_photo() {

        if ($_FILES['photo']['name'] != "") {
            $config['upload_path'] = './images';
            $config['allowed_types'] = 'jpg|jpeg|png|webp';
            $config['max_size'] = '2048';
            $config['maintain_ratio'] = TRUE;
            $config['encrypt_name'] = TRUE;
            $config['detect_mime'] = TRUE;
            $this->load->library('upload', $config);
            if ($this->upload->do_upload("photo")) {
                $upload_info = $this->upload->data();
                $photo = $upload_info['file_name'];

                $config['image_library'] = 'gd2';
                $config['source_image'] = './images/'.$photo;
                $config['maintain_ratio'] = TRUE;
                $config['width'] = 200;
                $config['height'] = 100;

                $this->load->library('image_lib', $config);

                $this->image_lib->resize();
                $this->session->set_userdata('photo', $upload_info['file_name']);

            } else {
                $this->form_validation->set_message('validate_photo', $this->upload->display_errors());
                return FALSE;
            }
        }
    }
     /**
     * save food menus ingredients
     * @access public
     * @return void
     * @param string
     * @param int
     * @param string
     */
    public function saveFoodMenusIngredients($food_menu_ingredients, $food_menu_id, $table_name) {
        if(isset($food_menu_ingredients) && $food_menu_ingredients){
            foreach ($food_menu_ingredients as $row => $ingredient_id):
                $fmi = array();
                $fmi['ingredient_id'] = $ingredient_id;
                $fmi['consumption'] = $_POST['consumption'][$row];
                $fmi['cost'] = $_POST['cost'][$row];
                $fmi['total'] = $_POST['total_cost'][$row];
                $fmi['food_menu_id'] = $food_menu_id;
                $fmi['user_id'] = $this->session->userdata('user_id');
                $fmi['company_id'] = $this->session->userdata('company_id');
                $this->Common_model->insertInformation($fmi, "tbl_food_menus_ingredients");
            endforeach;
        }

    }
     /**
     * save Food Menus Modifiers
     * @access public
     * @return void
     * @param string
     * @param int
     * @param string
     */
    public function saveFoodMenusModifiers($food_menu_modifiers, $food_menu_id, $table_name) {
        foreach ($food_menu_modifiers as $row => $modifier_id):
            $fmm = array();
            $fmm['modifier_id'] = $modifier_id;
            $fmm['food_menu_id'] = $food_menu_id;
            $fmm['user_id'] = $this->session->userdata('user_id');
            $fmm['company_id'] = $this->session->userdata('company_id');
            $this->Common_model->insertInformation($fmm, "tbl_food_menus_modifiers");
        endforeach;
    }
     /**
     * upload Food Menu
     * @access public
     * @return void
     * @param no
     */
    public function uploadFoodMenu() {
        $company_id = $this->session->userdata('company_id');
        $data = array();
        $data['foodMenus'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_food_menus");
        $data['foodMenuCategories'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, "tbl_food_menu_categories");
        $data['main_content'] = $this->load->view('master/foodMenu/uploadsfoodMenus', $data, TRUE);
        $this->load->view('userHome', $data);
    }
     /**
     * download file
     * @access public
     * @return void
     * @param string
     */
    public function downloadPDF($file = "") {
        $file = $file.".xlsx";
        $this->load->helper('download');
        $data = file_get_contents("asset/sample/" . $file); // Read the file's
        $name = $file;
        force_download($name, $data);
    }
     /**
     * Excel Data Add Food menus
     * @access public
     * @return void
     * @param no
     */
    public function ExcelDataAddFoodmenus() {
        $company_id = $this->session->userdata('company_id');
        if ($_FILES['userfile']['name'] != "") {
            if ($_FILES['userfile']['name'] == "Food_Menu_Upload.xlsx") {
                $configUpload['upload_path'] = FCPATH . 'asset/excel/';
                $configUpload['allowed_types'] = 'xls|xlsx';
                $configUpload['max_size'] = '5000';
                $this->load->library('upload', $configUpload);
                if ($this->upload->do_upload('userfile')) {
                    $upload_data = $this->upload->data();
                    $file_name = $upload_data['file_name'];
                    $extension = $upload_data['file_ext'];
                    $objReader = PHPExcel_IOFactory::createReader('Excel2007');
                    $objReader->setReadDataOnly(true);
                    $objPHPExcel = $objReader->load(FCPATH . 'asset/excel/' . $file_name);
                    $totalrows = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
                    $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
    
                    // Helper para limpiar números
                    function limpiar_num_php($valor) {
                        $v = trim($valor);
                        if ($v === "" || $v === "-" || strtolower($v) == "none" || strtolower($v) == "nan") {
                            return 0;
                        }
                        return is_numeric($v) ? $v : 0;
                    }
    
                    if ($totalrows > 2 && $totalrows < 504) {
                        $arrayerror = '';
                        for ($i = 4; $i <= $totalrows; $i++) {
                            $name = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(0, $i)->getValue()));
                            $code = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(1, $i)->getValue()));
                            $category = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(2, $i)->getValue()));
                            $sale_prices = limpiar_num_php(htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(3, $i)->getValue())));
                            $take_sale_prices = limpiar_num_php(htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(4, $i)->getValue())));
                            $del_sale_prices = limpiar_num_php(htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(5, $i)->getValue())));
                            $vat_name = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(6, $i)->getValue()));
                            $vat_percent = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(7, $i)->getValue()));
                            $description = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(8, $i)->getValue()));
                            $isVegItem = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(9, $i)->getValue()));
                            $isBeverage = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(10, $i)->getValue()));
    
                            if ($name == '') {
                                $arrayerror .= ($arrayerror == '') ? "Row Number $i column A required" : "<br>Row Number $i column A required";
                            }
                            if ($code == '') {
                                $arrayerror .= ($arrayerror == '') ? "Row Number $i column B required" : "<br>Row Number $i column B required";
                            }
                            if ($category == '') {
                                $arrayerror .= ($arrayerror == '') ? "Row Number $i column C required" : "<br>Row Number $i column C required";
                            }
                            // Ya no mostramos error por D/E/F porque los limpiamos arriba
    
                            $tmp_vat_name = explode(',',$vat_name);
                            $tmp_vat_percent = explode(',',$vat_percent);
    
                            if ($vat_name || $tmp_vat_percent) {
                                if(sizeof($tmp_vat_name) != sizeof($tmp_vat_percent)) {
                                    $arrayerror .= ($arrayerror == '') ? "Row Number $i column G & H does not match" : "<br>Row Number $i column G & H does not match";
                                }
                            }
    
                            if (($isVegItem == '')) {
                                $arrayerror .= ($arrayerror == '') ? "Row Number $i column J required" : "<br>Row Number $i column J required";
                            }
    
                            if (($isVegItem != 'Veg Yes') && ($isVegItem != 'Veg No')) {
                                $arrayerror .= ($arrayerror == '') ? "Row Number $i column J required or should be Veg Yes or Veg No" : "<br>Row Number $i column J required or should be Veg Yes or Veg No";
                            }
    
                            if (($isBeverage == '')) {
                                $arrayerror .= ($arrayerror == '') ? "Row Number $i column K required" : "<br>Row Number $i column K required";
                            }
    
                            if (($isBeverage != 'Bev Yes') && ($isBeverage != 'Bev No')) {
                                $arrayerror .= ($arrayerror == '') ? "Row Number $i column K required or should be Bev Yes or Bev No" : "<br>Row Number $i column K required or should be Bev Yes or Bev No";
                            }
                        }
                        if ($arrayerror == '') {
                            $company = getCompanyInfo();
                            $outlet_taxes = json_decode($company->tax_setting);
    
                            for ($i = 4; $i <= $totalrows; $i++) {
                                $name = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(0, $i)->getValue()));
                                $code = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(1, $i)->getValue()));
                                $category = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(2, $i)->getValue()));
                                $sale_prices = limpiar_num_php(htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(3, $i)->getValue())));
                                $take_sale_prices = limpiar_num_php(htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(4, $i)->getValue())));
                                $del_sale_prices = limpiar_num_php(htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(5, $i)->getValue())));
                                $vat_name = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(6, $i)->getValue()));
                                $vat_percent = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(7, $i)->getValue()));
                                $description = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(8, $i)->getValue()));
                                $isVegItem = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(9, $i)->getValue()));
                                $isBeverage = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(10, $i)->getValue()));
                                $image = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(11, $i)->getValue()));
                                $loyalty_point = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(12, $i)->getValue()));
                                $alternative_name = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(13, $i)->getValue()));
                                $tmp_vat_name = explode(',',$vat_name);
                                $tmp_vat_percent = explode(',',$vat_percent);
    
                                $tax_information = array();
                                $tax_string = '';
                                foreach($outlet_taxes as $key=>$value){
                                    foreach($tmp_vat_name as $key1=>$value1){
                                        if($value->tax==$value1){
                                            $get_tax = isset($tmp_vat_percent[$key1]) && $tmp_vat_percent[$key1]?$tmp_vat_percent[$key1]:0;
                                            $single_info = array(
                                                'tax_field_id' => $value->id,
                                                'tax_field_outlet_id' => 0,
                                                'tax_field_company_id' => 1,
                                                'tax_field_name' => $value->tax,
                                                'tax_field_percentage' => $get_tax
                                            );
                                            $tax_string.=($value->tax).":";
                                            array_push($tax_information,$single_info);
                                        }
                                    }
                                }
                                $ct_id = $this->get_foodmenu_ct_id_byname($category);
                                $fmc_info = array();
                                $fmc_info['name'] = $name;
                                $fmc_info['alternative_name'] = $alternative_name;
                                $fmc_info['code'] = $code;
                                $fmc_info['category_id'] = $ct_id;
                                $fmc_info['sale_price'] = $sale_prices;
                                $fmc_info['sale_price_take_away'] = $take_sale_prices;
                                $fmc_info['loyalty_point'] = $loyalty_point;
    
                                $delivery_partners = $this->Common_model->getAllByCompanyId($company_id, "tbl_delivery_partners");
                                $json_data = array();
                                if($delivery_partners){
                                    if(isset($delivery_partners) && $delivery_partners){
                                        foreach ($delivery_partners as $key=>$value){
                                            $data_value = explode(',',$del_sale_prices);
                                            foreach ($data_value as $k1=>$val){
                                                if($key==$k1){
                                                    $json_data["index_".$value->id] = $val;
                                                }
    
                                            }
                                        }
                                        $fmc_info['delivery_price'] = json_encode($json_data);
                                    }
                                }else{
                                    $fmc_info['sale_price_delivery'] = $del_sale_prices;
                                }
    
                                $fmc_info['description'] = $description;
                                $fmc_info['veg_item'] = $isVegItem;
                                $fmc_info['beverage_item'] = $isBeverage;
                                if($image){
                                    $fmc_info['photo'] = $image;
                                }
                                $fmc_info['tax_information'] = json_encode($tax_information);
                                $fmc_info['tax_string'] = $tax_string;
                                $fmc_info['user_id'] = $this->session->userdata('user_id');
                                $fmc_info['company_id'] = $this->session->userdata('company_id');
                                $id = $this->Common_model->insertInformation($fmc_info, "tbl_food_menus");
    
                                if(isLMni()):
                                    updatePrice($this->session->userdata('company_id'),$id,$fmc_info['sale_price'],$fmc_info['sale_price_take_away'],json_encode($json_data),$fmc_info['sale_price_delivery']);
                                endif;
    
                            }
                            unlink(FCPATH . 'asset/excel/' . $file_name);
                            $this->session->set_flashdata('exception', 'Imported successfully!');
                            redirect('foodMenu/foodMenus');
                        } else {
                            unlink(FCPATH . 'asset/excel/' . $file_name);
                            $this->session->set_flashdata('exception_err', "Required Data Missing:$arrayerror");
                        }
                    } else {
                        unlink(FCPATH . 'asset/excel/' . $file_name);
                        $this->session->set_flashdata('exception_err', "Entry is more than 500 or No entry found.");
                    }
                } else {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('exception_err', "$error");
                }
            } else {
                $this->session->set_flashdata('exception_err', "No podemos aceptar otros archivos, descargue el archivo de muestra 'Food_Menu_Upload.xlsx', complételo correctamente y cárguelo o cambie el nombre del archivo a 'Food_Menu_Upload.xlsx' y luego complételo.");
            }
        } else {
            $this->session->set_flashdata('exception_err', 'File is required');
        }
        redirect('foodMenu/uploadFoodMenu');
    }
     /**
     * assign Food Menu Modifier
     * @access public
     * @return void
     * @param int
     */
    public function assignFoodMenuModifier($encrypted_id = "") {
        $id = $this->custom->encrypt_decrypt($encrypted_id, 'decrypt');
        $company_id = $this->session->userdata('company_id');
        $food_menu_modifiers = $this->Master_model->getFoodMenuModifiers($id);
        if (!empty($food_menu_modifiers)) {
            foreach ($food_menu_modifiers as $value) {
                $user_menu_modifier_arr[] = $value->modifier_id;
            }
        } else {
            $user_menu_modifier_arr = '';
        }

        if (htmlspecialcharscustom($this->input->post('submit'))) {
            $this->Common_model->deletingMultipleFormData('food_menu_id', $id, 'tbl_food_menus_modifiers');
            $this->saveFoodMenusModifiers($_POST['modifier_id'], $id, 'tbl_food_menus_modifiers');
            $this->session->set_flashdata('exception', 'Information has been updated successfully!');
            redirect('foodMenu/foodMenus');
        } else {
            $data['encrypted_id'] = $encrypted_id;
            $data['modifiers'] = $this->Common_model->getAllModifierByCompanyId($company_id, 'tbl_modifiers');
            $data['food_menu_details'] = $this->Common_model->getDataById($id, "tbl_food_menus");
            $data['food_menu_modifiers'] = $user_menu_modifier_arr;
            $data['main_content'] = $this->load->view('master/foodMenu/assignFoodMenuModifier', $data, TRUE);
            $this->load->view('userHome', $data);
        }
    }
     /**
     * food Menu Details
     * @access public
     * @return void
     * @param int
     */
    public function foodMenuDetails($id) {
        $encrypted_id = $id;
        $id = $this->custom->encrypt_decrypt($id, 'decrypt');
        $company_id = $this->session->userdata('company_id');
        $data = array();
        $data['encrypted_id'] = $encrypted_id;
        $data['food_menu_details'] = $this->Common_model->getDataById($id, "tbl_food_menus");
        $data['added_combo_menus'] = $this->Common_model->getAllByCustomId($id,"food_menu_id","tbl_combo_food_menus",$order='');
        $data['food_menu_ingredients'] = $this->Master_model->getFoodMenuIngredients($data['food_menu_details']->id);
        $data['deliveryPartners'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_delivery_partners");
        $data['variation_food_menus'] = $this->Common_model->getAllByCustomId($id,"parent_id","tbl_food_menus",$order='');
        $data['main_content'] = $this->load->view('master/foodMenu/foodMenuDetails', $data, TRUE);
        $this->load->view('userHome', $data);
    }
     /**
     * get food menu category by name
     * @access public
     * @return int
     * @param string
     */
    public function get_foodmenu_ct_id_byname($category) {
        $company_id = $this->session->userdata('company_id');
        $user_id = $this->session->userdata('user_id');
        $id = $this->db->query("SELECT id FROM tbl_food_menu_categories WHERE company_id=$company_id and user_id=$user_id and category_name='" . $category . "' and del_status='Live'")->row('id');
        if ($id != '') {
            return $id;
        } else {
            $data = array('category_name' => $category, 'company_id' => $company_id, 'user_id' => $user_id);
            $query = $this->db->insert('tbl_food_menu_categories', $data);
            $id = $this->db->insert_id();
            return $id;
        }
    }
     /**
     * get food menu vat id by name
     * @access public
     * @return int
     * @param string
     * @param float
     */
    public function get_foodmenu_vat_id_byname($vat_name, $vat_percent) {
        $company_id = $this->session->userdata('company_id');
        $user_id = $this->session->userdata('user_id');
        $id = $this->db->query("SELECT id FROM tbl_vats WHERE company_id=$company_id and name='" . $vat_name . "'")->row('id');
        if ($id) {
            return $id;
        } else {
            $data = array('name' => $vat_name, 'company_id' => $company_id, 'percentage' => $vat_percent);
            $query = $this->db->insert('tbl_vats', $data);
            $id = $this->db->insert_id();
            return $id;
        }
    }
     /**
     * upload Food Menu Ingredients
     * @access public
     * @return void
     * @param no
     */
    public function uploadFoodMenuIngredients() {
        $company_id = $this->session->userdata('company_id');
        $data = array();
        $data['main_content'] = $this->load->view('master/foodMenu/uploadsfoodMenusIngredients', $data, TRUE);
        $this->load->view('userHome', $data);
    }
     /**
     * Excel Data Add Food menus Ingredients
     * @access public
     * @return void
     * @param no
     */
    public function ExcelDataAddFoodmenusIngredients()
    {
        $company_id = $this->session->userdata('company_id');
        $user_id = $this->session->userdata('user_id');
        if ($_FILES['userfile']['name'] != "") {
            if ($_FILES['userfile']['name'] == "Food_Menu_Ingredients_Upload.xlsx") {
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
                    $totalFoodMenuToUpload = 0;

                    if ($totalrows > 2) {
                        $arrayerror = '';
                        for ($i = 3; $i <= $totalrows; $i++) {
                            $menuOrIngredient = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(0, $i)->getValue()));
                            //it counts total number of food menus
                            if($menuOrIngredient=='FM'){
                                $totalFoodMenuToUpload++;
                            }
                        }
                        if($totalFoodMenuToUpload<10){
                            for ($i = 3; $i <= $totalrows; $i++) {
                                $menuOrIngredient = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(0, $i)->getValue()));
                                $menuOrIngredientName = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(1, $i)->getValue()));
                                $IngredCost = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(3, $i)->getValue()));
                                $consumption = null;

                                $currentRowFor = ''; //it hold current row wether menu or ingredient
                                //it counts total number of food menus
                                if($menuOrIngredient=='FM'){
                                    $totalFoodMenuToUpload++;
                                    $record = $this->Common_model->getMenuByMenuName($menuOrIngredientName);
                                    $currentRowFor = 'Menu';
                                }else{
                                    $consumption = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(2, $i)->getValue()));
                                    $record = $this->Common_model->getIngredientByIngredientName($menuOrIngredientName);
                                    $currentRowFor = 'Ingredient';
                                }

                                //get next menu or ingredient
                                $isNextMenuOrIngredient = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(0, $i+1)->getValue()));

                                // if any record is not found then set this message
                                if ($record==NULL) {
                                    if ($arrayerror == '') {
                                        $arrayerror.="Row Number $i column B required & must be valid menu or ingredient name";
                                    } else {
                                        $arrayerror.="<br>Row Number $i column B required & must be valid menu or ingredient name";
                                    }
                                }


                                // //it sets message when it's not menu and ingredient as well
                                if ($menuOrIngredient!="FM" && $menuOrIngredient!="IG") {
                                    if ($arrayerror == '') {
                                        $arrayerror.="Row Number $i column A required & must be 'FM' or 'IG'";
                                    } else {
                                        $arrayerror.="<br>Row Number $i column A required & must be 'FM' or 'IG'";
                                    }
                                }

                                if ($menuOrIngredient == 'IG' && ($consumption == null || $consumption == '' || !is_numeric($consumption))) {
                                    if ($arrayerror == '') {
                                        $arrayerror.=" $i Row Number column C required, it must be numeric";
                                    } else {
                                        $arrayerror.="<br> $i Row Number column C required, it must be numeric";
                                    }
                                }

                                if ($menuOrIngredient == 'IG') {
                                    if ($IngredCost == '' || !is_numeric($IngredCost)) {
                                        if ($arrayerror == '') {
                                            $arrayerror.="Row Number $i column D required or can not be text";
                                        } else {
                                            $arrayerror.="<br>Row Number $i column D required or can not be text";
                                        }
                                    }
                                }

                                //it sets message when food menu number is greater than 10
                                if ($totalFoodMenuToUpload>10) {
                                    if ($arrayerror == '') {
                                        $arrayerror.="You can not upload more than 10 food menus at a time.";
                                    } else {
                                        $arrayerror.="<br>You can not upload more than 10 food menus at a time.";
                                    }
                                }

                                //it checks next one is food menu or ingredient. if current one is food menu and next one
                                //is food menu then it means current food menu doesn't have ingredients
                                if($menuOrIngredient=='FM' && $isNextMenuOrIngredient=='FM'){
                                    if ($arrayerror == '') {
                                        $arrayerror.="row number $i is a Food Menu, no ingredient found for $menuOrIngredientName";
                                    } else {
                                        $arrayerror.="<br>row number $i is a Food Menu, no ingredient found for $menuOrIngredientName";
                                    }
                                }
                            }
                            if ($arrayerror == '') {

                                for ($i = 3; $i <= $totalrows; $i++) {
                                    $menuOrIngredient = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(0, $i)->getValue()));
                                    $menuOrIngredientName = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(1, $i)->getValue()));
                                    $IngredCost = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(3, $i)->getValue()));
                                    $consumption = null;
                                    if($menuOrIngredient=='FM'){
                                        $food_menu_record = $this->Common_model->getMenuByMenuName($menuOrIngredientName);
                                        $info_session['food_id_custom'] = $food_menu_record->id;
                                        $this->session->set_userdata($info_session);
                                    }else{
                                        $ingredient_record = $this->Common_model->getIngredientByIngredientName($menuOrIngredientName);
                                        $consumption = htmlspecialcharscustom(trim_checker($objWorksheet->getCellByColumnAndRow(2, $i)->getValue()));
                                        $food_menu_ingredient_info = array();
                                        $food_menu_ingredient_info['ingredient_id'] = $ingredient_record->id;
                                        $food_menu_ingredient_info['consumption'] = $consumption;
                                        $food_menu_ingredient_info['cost'] = $IngredCost;
                                        $food_menu_ingredient_info['total'] = ($IngredCost*$consumption);
                                        $food_menu_ingredient_info['food_menu_id'] = $this->session->userdata('food_id_custom');
                                        $food_menu_ingredient_info['user_id'] = $this->session->userdata('user_id');
                                        $food_menu_ingredient_info['company_id'] = $this->session->userdata('company_id');
                                        $food_menu_ingredient_info['del_status'] = 'Live';
                                        $this->Common_model->insertInformation($food_menu_ingredient_info, "tbl_food_menus_ingredients");
                                    }
                                }
                                unlink(FCPATH . 'asset/excel/' . $file_name); //File Deleted After uploading in database .
                                $this->session->set_flashdata('exception', 'Imported successfully!');
                                redirect('foodMenu/foodMenus');
                            } else {
                                unlink(FCPATH . 'asset/excel/' . $file_name); //File Deleted After uploading in database .
                                $this->session->set_flashdata('exception_err', "Required Data Missing:$arrayerror");
                            }
                        }else{
                            unlink(FCPATH . 'asset/excel/' . $file_name); //File Deleted After uploading in database .
                            $this->session->set_flashdata('exception_err', "You can not upload more than 10 food menus at a time.");
                        }
                    } else {
                        unlink(FCPATH . 'asset/excel/' . $file_name); //File Deleted After uploading in database .
                        $this->session->set_flashdata('exception_err', "No entry found.");
                    }
                } else {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('exception_err', "$error");
                }
            } else {
                $this->session->set_flashdata('exception_err', "No podemos aceptar otros archivos, descargue el archivo de muestra 'Food_Menu_Ingredients_Upload.xlsx', complételo correctamente y cárguelo o cambie el nombre del archivo a 'Food_Menu_Ingredients_Upload.xlsx' y luego complételo.");
            }
        } else {
            $this->session->set_flashdata('exception_err', 'File is required');
        }
        redirect('foodMenu/uploadFoodMenuIngredients');
    }


    // Mostrar formulario
    public function assign() {
        $data['grouped_menus'] = $this->Master_model->get_food_menus_without_ingredients_grouped_by_category();
        $data['main_content'] = $this->load->view('master/foodMenu/assign_ingredients', $data, TRUE);
        $this->load->view('userHome', $data);
    }

    public function assign_submit() {
        $menu_options = $this->input->post('menu_option'); // [food_menu_id => opcion]
        $company_id = $this->session->userdata('company_id');
        $user_id = $this->session->userdata('user_id');
        $unit_id = 5; // unitario
    
        // 1. Traer todos los food_menus "Live"
        $this->db->where('del_status', 'Live');
        $food_menus = $this->db->get('tbl_food_menus')->result();
    
        foreach ($food_menus as $food_menu) {
            $menu_id = $food_menu->id;
    
            if (isset($menu_options[$menu_id])) {
                $option = $menu_options[$menu_id];
                if ($option == 'no_change') {
                    continue;
                }
    
                $ing_type = ($option == 'pre_production') ? 'Pre-made Item' : 'Plain Ingredient';
    
                // Obtener el nombre de categoría del menú
                $category_name = '';
                if ($food_menu->category_id) {
                    $cat = $this->db->get_where('tbl_food_menu_categories', [
                        'id' => $food_menu->category_id,
                        'del_status' => 'Live'
                    ])->row();
                    $category_name = $cat ? $cat->category_name : '';
                }
    
                // Buscar categoría de ingredientes con ese nombre
                $ingredient_cat = $this->db->get_where('tbl_ingredient_categories', [
                    'category_name' => $category_name,
                    'company_id' => $company_id,
                    'del_status' => 'Live'
                ])->row();
    
                if ($ingredient_cat) {
                    $ingredient_category_id = $ingredient_cat->id;
                } else {
                    // Si no existe, crearla
                    $cat_data = [
                        'category_name' => $category_name ?: 'Sin Categoría',
                        'description' => '',
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                        'del_status' => 'Live'
                    ];
                    $ingredient_category_id = $this->Common_model->insertInformation($cat_data, 'tbl_ingredient_categories');
                }
    
                // --- CAMBIO CLAVE: armar el array igual que el flujo de type 3 ---
                $ingredient_data = [
                    'name' => $food_menu->name,
                    'code' => $food_menu->code,
                    'category_id' => $ingredient_category_id,
                    'purchase_price' => $food_menu->purchase_price ?: 0,
                    'alert_quantity' => $food_menu->alert_quantity ?: 0, 
                    'unit_id' => $unit_id,
                    'purchase_unit_id' => $unit_id,
                    'consumption_unit_cost' => $food_menu->purchase_price ?: $food_menu->sale_price,
                    'conversion_rate' => 1,
                    'is_direct_food' => 2, // como en type 3
                    'food_id' => $menu_id, // <- CONEXIÓN CLAVE
                    'user_id' => $user_id,
                    'company_id' => $company_id,
                    'del_status' => 'Live',
                    // Opcionales:
                    'ing_type' => $ing_type,
                    'unit_type' => 1,
                    'iva_tipo' => $food_menu->iva_tipo ?: '10',
                    'sale_price' => $food_menu->sale_price ?: 0,
                ];
    
                // Usar la función setIngredients() para evitar duplicados y actualizar si ya existe
                setIngredients($menu_id, $ingredient_data);
    
                // Actualizar el product_type a 3 para este food_menu
                $this->db->where('id', $menu_id);
                $this->db->update('tbl_food_menus', ['product_type' => 3]);
                // --- NO crear asociación en tbl_food_menus_ingredients ---
                // Ya que el ingrediente está "fusionado" con el food_menu, la relación es por food_id
            }
        }
    
        $this->session->set_flashdata('exception', 'Los ingredientes fueron generados y asociados correctamente.');
        redirect('foodMenu/assign');
    }

    public function ajax_ingredients() {
        $company_id = $this->session->userdata('company_id');
        $term = $this->input->get('term'); // texto que escribe el usuario
        $this->db->select("tbl_ingredients.id, tbl_ingredients.name, tbl_ingredients.code, tbl_units.unit_name, tbl_ingredients.consumption_unit_cost");
        $this->db->from("tbl_ingredients");
        $this->db->join('tbl_units', 'tbl_ingredients.unit_id = tbl_units.id');
        $this->db->where('tbl_ingredients.company_id', $company_id);
        $this->db->where('tbl_ingredients.del_status', 'Live');
        if ($term) {
            $this->db->like('tbl_ingredients.name', $term);
            $this->db->or_like('tbl_ingredients.code', $term);
        }
        $this->db->order_by('tbl_ingredients.name', 'ASC');
        $this->db->limit(20); // puedes ajustar el límite
        $result = $this->db->get()->result();
    
        $res = [];
        foreach ($result as $row) {
            $res[] = [
                "id" => $row->id . "|" . $row->name . "|" . $row->unit_name . "|" . $row->consumption_unit_cost,
                "text" => $row->name . " (" . $row->code . ") - " . $row->unit_name
            ];
        }
        echo json_encode(["results" => $res]);
    }

    public function ajax_food_menus() {
        $company_id = $this->session->userdata('company_id');
        $term = $this->input->get('term');
        $this->db->select("id, name, code, parent_id");
        $this->db->from("tbl_food_menus");
        $this->db->where('company_id', $company_id);
        $this->db->where('del_status', 'Live');
        $this->db->where('parent_id', 0); // Opcional: solo menús principales
        if ($term) {
            $this->db->like('name', $term);
            $this->db->or_like('code', $term);
        }
        $this->db->order_by('name', 'ASC');
        $this->db->limit(20);
        $result = $this->db->get()->result();
    
        $res = [];
        foreach ($result as $row) {
            $text = getParentNameOnly($row->parent_id) . " " . $row->name . " (" . $row->code . ")";
            $res[] = [
                "id" => $row->id,
                "text" => $text,
                "food_menu_only" => getParentNameOnly($row->parent_id) . " " . $row->name, // Ajusta según lo que necesites
            ];
        }
        echo json_encode(["results" => $res]);
    }

    // // Procesar formulario (pendiente de implementar inserciones)
    // public function assign_submit() {
    //     $selection = $this->input->post('menu_option'); // array: [id_menu => opcion]
    //     // Aquí iría la lógica para crear ingredientes y asociarlos, según la opción por menú
    //     // Por ahora solo mostrar lo recibido:
    //     echo "<pre>"; print_r($selection); echo "</pre>";
    //     // Redirigir o mostrar mensaje según tu lógica
    // }

    public function get_categories_ajax() {
        $company_id = $this->session->userdata('company_id');
        $categories = $this->Common_model->getAllByCompanyId($company_id, 'tbl_food_menu_categories');
        $data = [];
        foreach ($categories as $cat) {
            $data[] = ["id" => $cat->id, "name" => $cat->category_name];
        }
        echo json_encode($data);
    }

    // AJAX para actualizar campo individual
    public function update_field_ajax() {
        $input = json_decode(file_get_contents('php://input'), true);
        if(!$input || !isset($input['id'], $input['field'], $input['value']))
            show_404();

        $id = $input['id'];
        $field = $input['field'];
        $value = $input['value'];
        $this->db->where('id', $id)->update('tbl_food_menus', [$field => $value]);
        if ($field == 'iva_tipo' || $field == 'sale_price') {
            $this->db->where('food_id', $id)->update('tbl_ingredients', [$field => $value]);
        }
        echo json_encode(['status' => 'ok']);
    }

    // AJAX para batch update
    public function batch_update_field_ajax() {
        $input = json_decode(file_get_contents('php://input'), true);
        if(!$input || !isset($input['ids'], $input['field'], $input['value']))
            show_404();
    
        $ids = $input['ids'];
        $field = $input['field'];
        $value = $input['value'];
    
        // Seguridad: solo campos permitidos
        $allowed_fields = ['is_balanza', 'balanza_tipo', 'iva_tipo', 'balanza_validez'];
        if (!in_array($field, $allowed_fields)) show_404();
    
        // Seguridad: solo IDs numéricos
        $ids = array_filter($ids, 'is_numeric');
        if (empty($ids)) show_404();
    
        $this->db->where_in('id', $ids)->update('tbl_food_menus', [$field => $value]);
        if ($field == 'iva_tipo') {
            $this->db->where_in('food_id', $ids)->update('tbl_ingredients', [$field => $value]);
        }
        echo json_encode(['status' => 'ok']);
    }

    public function export_balanza_txt()
    {
        // 1. Consulta los productos balanza ordenados por código
        $this->load->database();
        $productos = $this->db->select('code, name, sale_price, balanza_tipo, balanza_validez')
            ->from('tbl_food_menus')
            ->where('is_balanza', 1)
            ->order_by('code', 'asc')
            ->get()->result_array();

        // 2. Armar el TXT
        $txtResult = '';
        foreach ($productos as $prod) {
            // CODIGO: solo 6 dígitos numéricos
            $codigo_numerico = preg_replace('/\D/', '', $prod['code']);
            $codigo_numerico = substr($codigo_numerico, 0, 6); // Limitar a 6 dígitos
            $codigo = str_pad($codigo_numerico, 6, "0", STR_PAD_LEFT);
    
            // TIPO: solo 1 caracter
            $tipo = (isset($prod['balanza_tipo']) && strtoupper(substr($prod['balanza_tipo'], 0, 1)) == 'U') ? 'U' : 'P';
    
            // DESCRIPCION: máximo 22 caracteres
            $descripcion = mb_substr($prod['name'], 0, 22, 'UTF-8');
            $descripcion = mb_str_pad83($descripcion, 22, ' ', STR_PAD_RIGHT, 'UTF-8');
    
            // PRECIO: solo 7 dígitos numéricos
            $precio_numerico = preg_replace('/\D/', '', $prod['sale_price']);
            $precio_numerico = substr($precio_numerico, 0, 7); // Limitar a 7 dígitos
            $precio = str_pad($precio_numerico, 7, "0", STR_PAD_LEFT);
    
            // PLAZO: solo 3 dígitos numéricos
            $plazo_val = isset($prod['balanza_validez']) ? (int)$prod['balanza_validez'] : 0;
            $plazo_num = substr(str_pad($plazo_val, 3, "0", STR_PAD_LEFT), 0, 3);
    
            $linea = $codigo . $tipo . $descripcion . $precio . $plazo_num . "\r\n";
            $txtResult .= $linea;
        }

        // 3. Descargar como archivo
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="CADTXT.TXT"');
        echo $txtResult;
        exit;
    }
}
