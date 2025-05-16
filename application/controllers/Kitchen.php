<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Kitchen extends Cl_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Common_model');
        $this->load->model('Kitchen_model');
        $this->load->model('Sale_model');
        $this->Common_model->setDefaultTimezone();
        $this->load->library('form_validation'); 
        if (!$this->session->has_userdata('user_id')) {
            redirect('Authentication/index');
        }
        if (!$this->session->has_userdata('outlet_id')) {
            $this->session->set_flashdata('exception_2', lang('please_click_green_button'));

            $this->session->set_userdata("clicked_controller", $this->uri->segment(1));
            $this->session->set_userdata("clicked_method", $this->uri->segment(2));
            redirect('Outlet/outlets');
        }
        $login_session['active_menu_tmp'] = '';
        $this->session->set_userdata($login_session);

    }

     /**
     * kitchen panel
     * @access public
     * @return void
     * @param no
     */
    public function panel($id=''){
        //start check access function
        $segment_2 = $this->uri->segment(2);
        $segment_3 = $this->uri->segment(3);
        $controller = "98";
        $function = "";

        if($segment_2=="kitchens"){
            $function = "view";
        }elseif($segment_2=="panel" && $segment_3){
            $function = "enter";
        }elseif($segment_2=="addEditKitchen" && $segment_3){
            $function = "update";
        }elseif($segment_2=="addEditKitchen"){
            $function = "add";
        }elseif($segment_2=="deleteKitchen"){
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

        if($id==''){
            redirect('Kitchen/kitchens');
        }
        $id = d($id,2);
        $data = array();
        $kitchen = $this->Common_model->getDataById($id, "tbl_kitchens");
        $printer = $this->Common_model->getPrinterInfoById($kitchen->printer_id);
        $printer = isset($printer) ? $printer : new stdClass(); 
        $data['kitchen'] = $kitchen;
        $data['printer'] = $printer;
        $data['kitchen_id'] = $id;
        $data['notifications'] = $this->get_new_notification($id);
        $this->load->view('kitchen/panel', $data);
    }
    /**
     * kitchens info
     * @access public
     * @return void
     * @param no
     */
    public function kitchens() {
        //start check access function
        $segment_2 = $this->uri->segment(2);
        $segment_3 = $this->uri->segment(3);
        $controller = "98";
        $function = "";

        if($segment_2=="kitchens"){
            $function = "view";
        }elseif($segment_2=="panel" && $segment_3){
            $function = "enter";
        }elseif($segment_2=="addEditKitchen" && $segment_3){
            $function = "update";
        }elseif($segment_2=="addEditKitchen"){
            $function = "add";
        }elseif($segment_2=="deleteKitchen"){
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

        $outlet_id = $this->session->userdata('outlet_id');
        $data = array();
        $data['kitchens'] = $this->Common_model->getAllByOutletId($outlet_id, "tbl_kitchens");
       
        foreach ($data['kitchens'] as $key=>$value){
            $txt_cates = '';
            $categories = $this->Common_model->getKitchenCategoriesById($value->id);
            foreach ($categories as $k=>$category){
                $txt_cates.=$category->category_name;
                if($k<sizeof($categories)-1){
                    $txt_cates.=", ";
                }
            }
            $data['kitchens'][$key]->categories = $txt_cates;
        }
        $data['main_content'] = $this->load->view('kitchen/kitchens', $data, TRUE);
        $this->load->view('userHome', $data);
    }
    /**
     * delete Kitchen
     * @access public
     * @return void
     * @param int
     */
    public function deleteKitchen($id) {
        //start check access function
        $segment_2 = $this->uri->segment(2);
        $segment_3 = $this->uri->segment(3);
        $controller = "98";
        $function = "";
        if($segment_2=="kitchens"){
            $function = "view";
        }elseif($segment_2=="panel" && $segment_3){
            $function = "enter";
        }elseif($segment_2=="addEditKitchen" && $segment_3){
            $function = "update";
        }elseif($segment_2=="addEditKitchen"){
            $function = "add";
        }elseif($segment_2=="deleteKitchen"){
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

        $id = $this->custom->encrypt_decrypt($id, 'decrypt');

        $this->db->delete("tbl_kitchens", array("id" => $id));
        $this->db->delete("tbl_kitchen_categories", array("kitchen_id" => $id)); 
        
        $this->session->set_flashdata('exception',lang('delete_success'));
        redirect('Kitchen/kitchens');
    }
    /**
     * add/Edit Kitchen
     * @access public
     * @return void
     * @param int
     */
    public function addEditKitchen($encrypted_id = "") {
        if(isLMni()):
    
          else:
            if (!$this->session->has_userdata('outlet_id')) {
                $this->session->set_flashdata('exception_2',lang('please_click_green_button'));
                $this->session->set_userdata("clicked_controller", $this->uri->segment(1));
                $this->session->set_userdata("clicked_method", $this->uri->segment(2));
                redirect('Outlet/outlets');
            }
       endif;

        //start check access function
        $segment_2 = $this->uri->segment(2);
        $segment_3 = $this->uri->segment(3);
        $controller = "98";
        $function = "";

        if($segment_2=="kitchens"){
            $function = "view";
        }elseif($segment_2=="panel" && $segment_3){
            $function = "enter";
        }elseif($segment_2=="addEditKitchen" && $segment_3){
            $function = "update";
        }elseif($segment_2=="addEditKitchen"){
            $function = "add";
        }elseif($segment_2=="deleteKitchen"){
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

        $id = $this->custom->encrypt_decrypt($encrypted_id, 'decrypt');
        $company_id = $this->session->userdata('company_id');
        $language_manifesto = $this->session->userdata('language_manifesto');

        if (htmlspecialcharscustom($this->input->post('submit'))) {
            $this->form_validation->set_rules('name',lang('name'), 'required|max_length[50]');
            if ($this->form_validation->run() == TRUE) {
                $kitchen_info = array();
                $kitchen_info['name'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('name')));
                $kitchen_info['printer_id'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('printer_id')));
                // Antes de guardar
                $designation_arr = $this->input->post('designation');
                $kitchen_info['designations'] = $designation_arr && is_array($designation_arr) ? implode(',', $designation_arr) : '';

                if(isLMni()):
                    $kitchen_info['outlet_id'] = htmlspecialcharscustom($this->input->post($this->security->xss_clean('outlet_id')));
                  else:
                    $kitchen_info['outlet_id'] = $this->session->userdata("outlet_id");
               endif;
                $kitchen_info['company_id'] = $company_id;
                if ($id == "") {
                    $id = $this->Common_model->insertInformation($kitchen_info, "tbl_kitchens");
                    $this->session->set_flashdata('exception', lang('insertion_success'));
                } else {
                    $this->Common_model->updateInformation($kitchen_info, $id, "tbl_kitchens");
                    $this->session->set_flashdata('exception', lang('update_success'));
                }
                $this->Common_model->deleteStatusChangeByCustomRow($id, "kitchen_id","tbl_kitchen_categories");
                //This variable could not be escaped because this is html content
                $item_check =$this->input->post($this->security->xss_clean('item_check'));
                if($item_check){
                    foreach ($item_check as $key=>$vl){
                        $kitchen_food_categories = array();
                        $kitchen_food_categories['kitchen_id'] = $id;
                        $kitchen_food_categories['cat_id'] = $vl;
                        $kitchen_food_categories['outlet_id'] = $kitchen_info['outlet_id'];
                        $this->Common_model->insertInformation($kitchen_food_categories, "tbl_kitchen_categories");
                    }
                }
                redirect('Kitchen/kitchens');

            } else {
                if ($id == "") {
                    $data = array();
                     $data['printers'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_printers");
                    $data['categories'] = $this->Common_model->getKitchenCategories('');
                    $data['main_content'] = $this->load->view('kitchen/addKitchen', $data, TRUE);
                    $this->load->view('userHome', $data);
                } else {
                    $data = array();
                     $data['printers'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_printers");
                    $data['encrypted_id'] = $encrypted_id;
                    $data['kitchen'] = $this->Common_model->getDataById($id, "tbl_kitchens");
                    $data['categories'] = $this->Common_model->getKitchenCategories($encrypted_id);
                    foreach ($data['categories'] as $key=>$value){
                        $is_checked = $this->Common_model->checkForExist($value->id);
                        if(isset($is_checked) && $is_checked){
                            $data['categories'][$key]->checker = 'checked';
                        }else{
                            $data['categories'][$key]->checker = '';
                        }
                    }
                    $data['main_content'] = $this->load->view('kitchen/editKitchen', $data, TRUE);
                    $this->load->view('userHome', $data);
                }
            }
        } else {
            if ($id == "") {
                $data = array();
                 $data['printers'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_printers");
                $data['categories'] = $this->Common_model->getKitchenCategories('');
                $data['main_content'] = $this->load->view('kitchen/addKitchen', $data, TRUE);
                $this->load->view('userHome', $data);
            } else {
                $data = array();
                 $data['printers'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_printers");
                $data['encrypted_id'] = $encrypted_id;
                $data['kitchen'] = $this->Common_model->getDataById($id, "tbl_kitchens");
                $data['categories'] = $this->Common_model->getKitchenCategories($encrypted_id);
                foreach ($data['categories'] as $key=>$value){
                    $is_checked = $this->Common_model->checkForExist($value->id);
                    if(isset($is_checked) && $is_checked){
                        $data['categories'][$key]->checker = 'checked';
                    }else{
                        $data['categories'][$key]->checker = '';
                    }
                }
                $data['main_content'] = $this->load->view('kitchen/editKitchen', $data, TRUE);
                $this->load->view('userHome', $data);
            }
        }
    }
     /**
     * get new orders
     * @access public
     * @return object
     * @param no
     */
    public function get_new_orders_ajax(){
        $kitchen_id = escape_output($_POST['kitchen_id']);
        $data1 = $this->get_new_orders($kitchen_id);
        echo json_encode($data1);        
    }
     /**
     * get order details kitchen
     * @access public
     * @return object
     * @param no
     */
    public function get_order_details_kitchen_ajax(){
        $sale_id = $this->input->post('sale_id');
        $kitchen_id = $this->input->post('kitchen_id');
        $sale_object = $this->get_all_information_of_a_sale_kitchen_type($sale_id,$kitchen_id);
        echo json_encode($sale_object);
    }
     /**
     * get all information of a sale kitchen type
     * @access public
     * @return object
     * @param int
     */
    public function get_all_information_of_a_sale_kitchen_type($sales_id,$kitchen_id){
        $sales_information = $this->Kitchen_model->getSaleBySaleId($sales_id);
        $items_by_sales_id = $this->Kitchen_model->getAllKitchenItemsFromSalesDetailBySalesId($sales_id,$kitchen_id);
        // foreach($items_by_sales_id as $single_item_by_sale_id){
        //     $modifier_information = $this->Kitchen_model->getModifiersBySaleAndSaleDetailsId($sales_id,$single_item_by_sale_id->sales_details_id);
        //     $single_item_by_sale_id->modifiers = $modifier_information;
        // }
        $sales_details_objects = $items_by_sales_id;
        $sale_object = $sales_information[0];
        $sale_object->items = $sales_details_objects;
        return $sale_object;
    }
     /**
     * get new orders
     * @access public
     * @return string
     * @param no
     */
    public function get_new_orders($kitchen_id) {
        $outlet_id = $this->session->userdata('outlet_id');
        // $kitchen = $this->Common_model->getDataById($kitchen_id, "tbl_kitchens");
        
        // Obtener todas las órdenes con datos completos
        $orders = $this->Kitchen_model->getNewOrders($outlet_id, $kitchen_id);
        
        // Recolectar IDs de órdenes que necesitan actualización
        $orders_to_update = [];
        
        foreach ($orders as &$order) {
            // Crear objeto DateTime con la fecha/hora del pedido
            $orderDate = new DateTime($order->date_time);
            // Fecha/hora actual
            $now = new DateTime();
            
            // Calcular la diferencia
            $diff = $now->diff($orderDate);
            
            // Calcular el total de minutos transcurridos
            $totalMinutes = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
            
            // Formatear como mm:ss (asegurando 2 dígitos para los segundos)
            $order->minutos = sprintf("%d:%02d", $totalMinutes, $diff->s);
            // Verificar si necesita actualización de campana
            if ($order->is_kitchen_bell == 1) {
                $orders_to_update[] = $order->sales_id;
            }
            
            // Obtener items con modificadores
            $order->items = $this->Kitchen_model->getAllKitchenItemsFromSalesDetailBySalesId(
                $order->sales_id, 
                $kitchen_id
            );
            
            // Procesar mesas
            $tables = [];
            if (!empty($order->table_ids)) {
                $table_ids = explode(',', $order->table_ids);
                foreach ($table_ids as $table_id) {
                    if (!empty($table_id)) {
                        $table = $this->Common_model->getDataById($table_id, 'tbl_tables');
                        if ($table) {
                            $tables[] = $table->name;
                        }
                    }
                }
            }
            $order->orders_table_text = implode(', ', $tables);
        }
        
        // Actualizar todas las órdenes necesarias en una sola consulta
        if (!empty($orders_to_update)) {
            $this->db->where_in('id', $orders_to_update);
            $this->db->update('tbl_kitchen_sales', ['is_kitchen_bell' => 2]);
        }
        return $orders;
    }

    public function get_new_ordersOld($kitchen_id){
        $outlet_id = $this->session->userdata('outlet_id');
        $user_id = $this->session->userdata('user_id');
        $kitchen = $this->Common_model->getDataById($kitchen_id, "tbl_kitchens");
        $data1 = $this->Kitchen_model->getNewOrders($kitchen->outlet_id,$kitchen_id);
        $i = 0;
        for($i;$i<count($data1);$i++){
            //update for bell
            $data_bell = array();
            $data_bell['is_kitchen_bell'] = 2;
            $this->Common_model->updateInformation($data_bell, $data1[$i]->sale_id, "tbl_kitchen_sales");

            $data1[$i]->total_kitchen_type_items = $this->Kitchen_model->get_total_kitchen_type_items($data1[$i]->sale_id);
            $data1[$i]->total_kitchen_type_done_items = $this->Kitchen_model->get_total_kitchen_type_done_items($data1[$i]->sale_id);
            $data1[$i]->total_kitchen_type_started_cooking_items = $this->Kitchen_model->get_total_kitchen_type_started_cooking_items($data1[$i]->sale_id);
            $data1[$i]->tables_booked = $this->Kitchen_model->get_all_tables_of_a_sale_items($data1[$i]->sale_id);
            $items_by_sales_id = $this->Kitchen_model->getAllKitchenItemsFromSalesDetailBySalesId($data1[$i]->sale_id,$kitchen_id);

            foreach($items_by_sales_id as $single_item_by_sale_id){
                $modifier_information = $this->Kitchen_model->getModifiersBySaleAndSaleDetailsId($data1[$i]->sale_id,$single_item_by_sale_id->sales_details_id);
                $single_item_by_sale_id->modifiers = $modifier_information;
            }
            $data1[$i]->items = $items_by_sales_id;
        }
        return $data1;
    }
     /**
     * update cooking status
     * @access public
     * @return void
     * @param no
     */
    public function first_item_sales_id($previous_id)
    {
        // $previous_id = $this->input->post('previous_id');
        // $kitchen_id = $this->input->post('kitchen_id');
        $previous_id_array = explode(",", $previous_id);
        // $cooking_status = $this->input->post('cooking_status');
        
        // Obtenemos el sale_id del primer ítem (todos pertenecen a la misma orden)
        $first_item = $this->db->select('sales_id')
                            ->where('previous_id', $previous_id_array[0])
                            ->get('tbl_kitchen_sales_details')
                            ->row();
        
        if (!$first_item) {
            echo '<pre>';
            var_dump($first_item); 
            echo '<pre>';
            
            return false; // No hay ítems válidos
        }
        
        $sale_id = $first_item->sales_id;
        echo '<pre>';
        echo 'Sale ID: '.$sale_id.'<br>';
        echo 'Previous ID: '.$previous_id_array[0].'<br>';
        var_dump($first_item); 
        echo '<pre>';
        $this->updateIsKitchenBySale($sale_id);
        
    }
    public function update_cooking_status_ajaxOld()
    {
        $previous_id = $this->input->post('previous_id');
        $kitchen_id = $this->input->post('kitchen_id');
        $previous_id_array = explode(",", $previous_id);
        $cooking_status = $this->input->post('cooking_status');
        
        // Obtenemos el sale_id del primer ítem (todos pertenecen a la misma orden)
        $first_item = $this->db->select('sales_id')
                            ->where('previous_id', $previous_id_array[0])
                            ->get('tbl_kitchen_sales_details')
                            ->row();
        
        if (!$first_item) {
            return false; // No hay ítems válidos
        }
        
        $sale_id = $first_item->sales_id;
        
        // Llamamos a la función para marcar los ítems de cocina
        $this->updateIsKitchenBySale($sale_id);
        // Procesamos cada ítem marcado
        foreach ($previous_id_array as $single_previous_id) {
            $previous_id = $single_previous_id;
            $item_info = $this->Kitchen_model->getItemInfoByPreviousId($previous_id);
            
            // Si es el primer ítem, obtenemos la info de la venta
            if (!isset($sale_info)) {
                $sale_info = $this->Kitchen_model->getSaleBySaleId($sale_id);
                $tables_booked = $sale_info[0]->orders_table_text;
            }

            if ($cooking_status == "Started Cooking") {
                $cooking_status_update_array = [
                    'cooking_status' => $cooking_status, 
                    'cooking_start_time' => date('Y-m-d H:i:s')
                ];
                
                $this->db->where('previous_id', $previous_id);
                $this->db->update('tbl_kitchen_sales_details', $cooking_status_update_array);
                
                if ($sale_info[0]->date_time == strtotime('0000-00-00 00:00:00')) {
                    $this->db->where('id', $sale_id);
                    $this->db->update('tbl_kitchen_sales', [
                        'cooking_start_time' => date('Y-m-d H:i:s')
                    ]);
                }
            } else {
                $cooking_status_update_array = [
                    'cooking_status' => $cooking_status, 
                    'cooking_done_time' => date('Y-m-d H:i:s')
                ];
                
                $this->db->where('previous_id', $previous_id);
                $this->db->update('tbl_kitchen_sales_details', $cooking_status_update_array);

                $this->db->where('id', $sale_id);
                $this->db->update('tbl_kitchen_sales', [
                    'cooking_done_time' => date('Y-m-d H:i:s')
                ]);

                $order_name = $sale_info[0]->sale_no;
                $notification = "Mesa: ".$tables_booked.', Cliente: '.$sale_info[0]->customer_name.', Item: '.$item_info->menu_name.' está listo para servir, Orden: '.$order_name;
                
                $this->db->insert('tbl_notifications', [
                    'notification' => $notification,
                    'sale_id' => $sale_id,
                    'waiter_id' => $sale_info[0]->waiter_id,
                    'outlet_id' => $this->session->userdata('outlet_id')
                ]);
            }
        }
        
        
        return true;
    }

    public function updateIsKitchenBySale($sales_id)
    {
        // Primero, selecciona los detalles de la venta y determina si tiene cocina
        $sql = "
            UPDATE tbl_kitchen_sales_details AS d
            LEFT JOIN tbl_food_menus AS m ON m.id = d.food_menu_id
            LEFT JOIN tbl_kitchen_categories AS c ON c.cat_id = m.category_id AND c.del_status = 'Live'
            SET d.is_kitchen = IF(c.kitchen_id IS NULL OR c.kitchen_id = '', 0, 1)
            WHERE d.sales_id = ?
        ";
        $this->db->query($sql, array($sales_id));
    }

    public function update_cooking_status_ajax()
    {
        $previous_id = $this->input->post('previous_id');
        $kitchen_id = $this->input->post('kitchen_id');
        $previous_id_array = explode(",",$previous_id);
        $cooking_status = $this->input->post('cooking_status');
        $total_item = count($previous_id_array); 

        if ($previous_id_array){
            $first_item = $this->db->select('sales_id')
                                ->where('previous_id', $previous_id_array[0])
                                ->get('tbl_kitchen_sales_details')
                                ->row();
            if ($first_item){
                $sale_id = $first_item->sales_id;
                $this->updateIsKitchenBySale($sale_id);
            }
        }
        foreach($previous_id_array as $single_previous_id){
            $previous_id = $single_previous_id;
            $item_info = $this->Kitchen_model->getItemInfoByPreviousId($previous_id);
            $sale_id = $item_info->sales_id;
            $sale_info = $this->Kitchen_model->getSaleBySaleId($sale_id);

            $tables_booked = $sale_info[0]->orders_table_text;

            if($cooking_status=="Started Cooking"){
                $cooking_status_update_array = array('cooking_status' => $cooking_status, 'cooking_start_time' => date('Y-m-d H:i:s'));
                
                $this->db->where('previous_id', $previous_id);
                $this->db->update('tbl_kitchen_sales_details', $cooking_status_update_array);
                
                if($sale_info[0]->date_time==strtotime('0000-00-00 00:00:00')){
                    $cooking_update_array_sales_tbl = array('cooking_start_time' => date('Y-m-d H:i:s'));
                    $this->db->where('id', $sale_id);
                    $this->db->update('tbl_kitchen_sales', $cooking_update_array_sales_tbl);
                }
                
            }else{

                $cooking_status_update_array = array('cooking_status' => $cooking_status, 'cooking_done_time' => date('Y-m-d H:i:s'));
                
                $this->db->where('previous_id', $previous_id);
                $this->db->update('tbl_kitchen_sales_details', $cooking_status_update_array);

                $cooking_update_array_sales_tbl = array('cooking_done_time' => date('Y-m-d H:i:s'));
                $this->db->where('id', $sale_id);
                $this->db->update('tbl_kitchen_sales', $cooking_update_array_sales_tbl);

                $order_name = $sale_info[0]->sale_no;

                $notification = "Mesa: ".$tables_booked.', Cliente: '.$sale_info[0]->customer_name.', Item: '.$item_info->menu_name.' está listo para servir, Orden: '.$order_name;
                $notification_data = array();        
                $notification_data['notification'] = $notification;
                $notification_data['sale_id'] = $sale_id;
                $notification_data['waiter_id'] = $sale_info[0]->waiter_id;
                $notification_data['outlet_id'] = $this->session->userdata('outlet_id');
                $this->db->insert('tbl_notifications', $notification_data); 
            }
        } 
    }


    public function get_update_kitchen_status_ajax()
    {
        $sale_no = $this->input->post('sale_no');
        $sale = getKitchenSaleDetailsBySaleNo($sale_no);
        $result = $this->Kitchen_model->get_all_kitchen_items($sale->id);
        echo json_encode($result);
    }
    public function check_update_kitchen_status_ajax()
    {
        $sale_no = $this->input->post('sale_no');
        $sale = getKitchenSaleDetailsBySaleNo($sale_no);
        $is_done = $this->Kitchen_model->get_total_kitchen_type_done_items($sale->id);
        $is_cooked = $this->Kitchen_model->get_total_kitchen_type_started_cooking_items($sale->id);
        $data['status'] = false;
        $data['is_done'] = false;
        $data['is_cooked'] = false;
        if($is_done){
            $data['status'] = true;
            $data['is_done'] = true;
        }
        if($is_cooked){
            $data['status'] = true;
            $data['is_cooked'] = true;
        }
       echo json_encode($data);
    }
     /**
     * update cooking status, delivery, take away
     * @access public
     * @return void
     * @param no
     */
    public function update_cooking_status_delivery_take_away_ajax() {
        $previous_id = $this->input->post('previous_id');
        $kitchen_id = $this->input->post('kitchen_id');
        $previous_id_array = explode(",", $previous_id);
        $cooking_status = $this->input->post('cooking_status');
        
        if (!$previous_id_array) {
            echo json_encode(['status' => 'error', 'msg' => 'No se ha recibido items a cambiar!']);
            return;
        }
        // Obtener el sale_id una sola vez (del primer item)
        $first_item_info = $this->Kitchen_model->getItemInfoByPreviousId($previous_id_array[0]);
        if (!$first_item_info) {
            echo json_encode(['status' => 'error', 'msg' => 'No se encontró el item']);
            return;
        }
        $sale_id = $first_item_info->sales_id;

        // Llamamos a la función para marcar los ítems de cocina
        $this->updateIsKitchenBySale($sale_id);
        
        $update_data = [
            'cooking_status' => $cooking_status,
            ($cooking_status == "Started Cooking" ? 'cooking_start_time' : 'cooking_done_time') => date('Y-m-d H:i:s')
        ];
        
        // Actualizar todos los items de la orden seleccionados
        $this->db->where_in('previous_id', $previous_id_array);
        $this->db->update('tbl_kitchen_sales_details', $update_data);
        
        // Actualizar la venta (tbl_kitchen_sales)
        $this->db->where('id', $sale_id);
        $this->db->update('tbl_kitchen_sales', [
            ($cooking_status == "Started Cooking" ? 'cooking_start_time' : 'cooking_done_time') => date('Y-m-d H:i:s')
        ]);
        
        // Solo para pedidos completados
        if ($cooking_status == "Done") {
            $sale_info = $this->get_all_information_of_a_sale_kitchen_type($sale_id, $kitchen_id);
            $order_types = [
                1 => '',
                2 => 'El pedido para llevar está listo',
                3 => 'Orden de Delivery está listo para llevar'
            ];
            $order_name = $sale_info->sale_no;
            $order_type_operation = $order_types[$sale_info->order_type] ?? '';
            $notification = sprintf(
                'Cliente: %s, Orden Número: %s %s', 
                $sale_info->customer_name, 
                $order_name, 
                $order_type_operation
            );
            $notification_data = [
                'notification' => $notification,
                'sale_id' => $sale_id,
                'waiter_id' => $sale_info->waiter_id,
                'outlet_id' => $this->session->userdata('outlet_id')
            ];
            $this->db->insert('tbl_notifications', $notification_data);
        }
        
        echo json_encode(['status' => 'success']);
    }


    public function update_cooking_status_delivery_take_away_ajaxOld2() {
        $previous_id = $this->input->post('previous_id');
        $kitchen_id = $this->input->post('kitchen_id');
        $previous_id_array = explode(",", $previous_id);
        $cooking_status = $this->input->post('cooking_status');
        $total_item = count($previous_id_array);
        
        foreach ($previous_id_array as $single_previous_id) {
            $item_info = $this->Kitchen_model->getItemInfoByPreviousId($single_previous_id);
            if (!$item_info) continue;
            
            $sale_id = $item_info->sales_id;
            
            $cooking_status_update_array = [
                'cooking_status' => $cooking_status,
                ($cooking_status == "Started Cooking" ? 'cooking_start_time' : 'cooking_done_time') => date('Y-m-d H:i:s')
            ];
            
            // Actualizar el item
            $this->db->where('previous_id', $single_previous_id);
            $this->db->update('tbl_kitchen_sales_details', $cooking_status_update_array);
            
            // Actualizar la venta
            $this->db->where('id', $sale_id);
            $this->db->update('tbl_kitchen_sales', [
                ($cooking_status == "Started Cooking" ? 'cooking_start_time' : 'cooking_done_time') => date('Y-m-d H:i:s')
            ]);
            
            // Solo para pedidos completados
            if ($cooking_status == "Done" && $single_previous_id == end($previous_id_array)) {
                $sale_info = $this->get_all_information_of_a_sale_kitchen_type($sale_id, $kitchen_id);
                
                $order_types = [
                    1 => '',
                    2 => 'El pedido para llevar está listo',
                    3 => 'Orden de Delivery está listo para llevar'
                ];
                
                $order_name = $sale_info->sale_no;
                $order_type_operation = $order_types[$sale_info->order_type] ?? '';
                
                $notification = sprintf('Cliente: %s, Orden Número: %s %s', 
                                      $sale_info->customer_name, 
                                      $order_name, 
                                      $order_type_operation);
                
                $notification_data = [
                    'notification' => $notification,
                    'sale_id' => $sale_id,
                    'waiter_id' => $sale_info->waiter_id,
                    'outlet_id' => $this->session->userdata('outlet_id')
                ];
                
                $this->db->insert('tbl_notifications', $notification_data);
            }
        }
        
        echo json_encode(['status' => 'success']);
    }

    public function update_cooking_status_delivery_take_away_ajaxOld(){
        $previous_id = $this->input->post('previous_id');
        $kitchen_id = $this->input->post('kitchen_id');
        $previous_id_array = explode(",",$previous_id);
        $cooking_status = $this->input->post('cooking_status');
        $total_item = count($previous_id_array);
        $i = 1;
        foreach($previous_id_array as $single_previous_id){
            $previous_id = $single_previous_id;
            $item_info = $this->Kitchen_model->getItemInfoByPreviousId($previous_id);
            $sale_id = $item_info->sales_id;
            if($cooking_status=="Started Cooking"){
                $cooking_status_update_array = array('cooking_status' => $cooking_status, 'cooking_start_time' => date('Y-m-d H:i:s'));
                
                $this->db->where('previous_id', $previous_id);
                $this->db->update('tbl_kitchen_sales_details', $cooking_status_update_array);
                
                $cooking_update_array_sales_tbl = array('cooking_start_time' => date('Y-m-d H:i:s'));
                $this->db->where('id', $sale_id);
                $this->db->update('tbl_kitchen_sales', $cooking_update_array_sales_tbl);
            }else{
                $cooking_status_update_array = array('cooking_status' => $cooking_status, 'cooking_done_time' => date('Y-m-d H:i:s'));
                
                $this->db->where('previous_id', $previous_id);
                $this->db->update('tbl_kitchen_sales_details', $cooking_status_update_array);

                $cooking_update_array_sales_tbl = array('cooking_done_time' => date('Y-m-d H:i:s'));
                $this->db->where('id', $sale_id);
                $this->db->update('tbl_kitchen_sales', $cooking_update_array_sales_tbl);

                if($i==$total_item){
                    $sale_info = $this->get_all_information_of_a_sale_kitchen_type($sale_id,$kitchen_id);
                    $order_type_operation = '';
                    if($sale_info->order_type==1){
                        $order_name = $sale_info->sale_no;
                    }elseif($sale_info->order_type==2){
                        $order_name = $sale_info->sale_no;
                        $order_type_operation = 'El pedido para llevar está listo';
                    }elseif($sale_info->order_type==3){
                        $order_name = $sale_info->sale_no;
                        $order_type_operation = 'Orden de Delivery está listo para llevar';
                    }
                    $notification = 'Cliente: '.$sale_info->customer_name.', Orden Número: '.$order_name.' '.$order_type_operation;
                    $notification_data = array();        
                    $notification_data['notification'] = $notification;
                    $notification_data['sale_id'] = $sale_id;
                    $notification_data['waiter_id'] = $sale_info->waiter_id;
                    $notification_data['outlet_id'] = $this->session->userdata('outlet_id');
                    $this->db->insert('tbl_notifications', $notification_data);           
                }
            }
            $i++;
        }
    }
     /**
     * get Food Menu By Sale Id
     * @access public
     * @return object
     * @param no
     */
    public function getFoodMenuBySaleId(){
        $sale_id = $this->input->get('sale_id');
        $data = $this->Kitchen_model->getFoodMenuBySaleId($sale_id);
        echo  json_encode($data);
    }
     /**
     * get Current Food
     * @access public
     * @return object
     * @param no
     */
    public function getCurrentFood(){
        $data = $this->Kitchen_model->getUnReadyOrders();
        echo  json_encode($data);
    }
     /**
     * check Unready Food Menus
     * @access public
     * @return object
     * @param no
     */
    public function checkUnreadyFoodMenus(){
        $data['TotalUnreadyFood'] = '';
        echo json_encode($data);
    }
     /**
     * set Order Ready
     * @access public
     * @return object
     * @param no
     */
    public function setOrderReady(){
        $sale_details_id = $this->input->get('sale_details_id');
        $data = $this->Kitchen_model->setOrderReady($sale_details_id);
         $data['status'] = 'true';
        echo json_encode($data);
    }
     /**
     * set Order Ready All
     * @access public
     * @return void
     * @param no
     */
    public function setOrderReadyAll(){
        $sale_id = $this->input->get('sale_id');
        $data = $this->Kitchen_model->setOrderReadyAll($sale_id);
         $data['status'] = 'true';
        echo json_encode($data);
    }
     /**
     * get new notification
     * @access public
     * @return object
     * @param no
     */
    public function get_new_notification($id)
    {
        $outlet_id = $this->session->userdata('outlet_id');
        $notifications = $this->Kitchen_model->getNotificationByOutletId($outlet_id,$id);
        return $notifications;
    }
     /**
     * get new notifications
     * @access public
     * @return object
     * @param no
     */
    public function get_new_notifications_ajax()
    {
        $id = escape_output($_POST['kitchen_id']);
        echo json_encode($this->get_new_notification($id));
    }
     /**
     * remove notification
     * @access public
     * @return void
     * @param no
     */
    public function remove_notication_ajax()
    {
        $notification_id = $this->input->post('notification_id');
        $this->db->delete('tbl_notification_bar_kitchen_panel', array('id' => $notification_id));
        echo escape_output($notification_id);
    }
     /**
     * remove multiple notification
     * @access public
     * @return void
     * @param no
     */
    public function remove_multiple_notification_ajax()
    {
        $notifications = $this->input->post('notifications');
        $notifications_array = explode(",",$notifications);
        foreach($notifications_array as $single_notification){
            $this->db->delete('tbl_notification_bar_kitchen_panel', array('id' => $single_notification));
        } 
    }
    public function getKitchenCategoriesByAjax()
    {
        /*This all variables could not be escaped because this is an array field*/
        $id = isset($_POST['kitchen_id']) && $_POST['kitchen_id']?$_POST['kitchen_id']:'';

        if(isLMni()):
            $outlet_id = isset($_POST['outlet_id']) && $_POST['outlet_id']?$_POST['outlet_id']:'';
        else:
            $outlet_id = $this->session->userdata('outlet_id');
        endif;

        $categories = $this->Common_model->getKitchenCategoriesByAjax($id);
        $html = '';
        foreach ($categories as $key=>$value){
            $is_checked = $this->Common_model->checkForExistUpdate($value->id,$outlet_id);
            $checked = '';
            if($is_checked){
                $checked = "checked";
            }
            $html.='<div class="col-sm-12 mb-3 col-md-6 col-lg-3">
                                <div class="border_custom">
                                <label class="container txt_47" for="checker_'.$value->id.'"><b>'.$value->category_name.'</b>
                                    <input class="checkbox_user parent_class" '.$checked.' id="checker_'.$value->id.'" data-name="'.$value->category_name.'" value="'.$value->id.'" type="checkbox" name="item_check[]">
                                    <span class="checkmark"></span>
                                </label>
                                <br>
                                </div>
                            </div>';
        }

        echo json_encode($html);
    }

    
    
    public function printer_app_kot_by_sale_id($sale_no, $kitchen_id) {
        $printers_array = [];
    
        // Obtener información de la venta
        // $sale_object = getKitchenSaleDetailsBySaleNo($sale_id);
        $sale_object = $this->get_all_information_of_a_sale($sale_no);
        // echo '<pre>';
        // var_dump($sale_object); 
        // echo '<pre>';
        
        if(!$sale_object) {
            echo json_encode([]);
            return;
        }
        $sale_id = $sale_object->id;
        // Traer solo los items de la venta para esa cocina
        $outlet_id = $this->session->userdata('outlet_id');

        $this->db->select('tbl_kitchen_sales_details.*, tbl_kitchens.name as kitchen_name, tbl_kitchens.id as kitchen_id');
        $this->db->from('tbl_kitchen_sales_details');
        $this->db->join('tbl_food_menus', 'tbl_food_menus.id = tbl_kitchen_sales_details.food_menu_id', 'left');
        $this->db->join('tbl_kitchen_categories', 'tbl_kitchen_categories.cat_id = tbl_food_menus.category_id AND tbl_kitchen_categories.del_status = "Live" AND tbl_kitchen_categories.outlet_id = ' . intval($outlet_id), 'left');
        $this->db->join('tbl_kitchens', 'tbl_kitchens.id = tbl_kitchen_categories.kitchen_id AND tbl_kitchens.outlet_id = ' . intval($outlet_id), 'left');
        $this->db->where('tbl_kitchen_sales_details.sales_id', $sale_id);
        $this->db->where('tbl_kitchens.id', $kitchen_id);
        // $this->db->where('tbl_kitchen_categories.del_status', 'Live');
        // Solo items no terminados, si lo deseas:
        // $this->db->where('tbl_kitchen_sales_details.cooking_status !=', 'Done');
        $sale_items = $this->db->get()->result();
    
        

        // Traer los modificadores para cada item
        foreach ($sale_items as $item) {
            $item->modifiers = $this->Kitchen_model->getModifiersBySaleAndSaleDetailsId($sale_id, $item->id);
        }
    
        // echo '<pre>';
        // var_dump($sale_items); 
        // echo '<pre>';

        if($sale_object->order_type==1){
            $order_type = lang('dine');
        }else if($sale_object->order_type==2){
            $order_type = lang('take_away');
        }else if($sale_object->order_type==3){
            $order_type = lang('delivery');
        }
        // Construcción del contenido del ticket
        $content = [
            ['type' => 'text', 'align' => 'center', 'text' => $this->session->userdata('outlet_name')],
            ['type' => 'text', 'align' => 'center', 'text' => 'TICKET DE COCINA'],
            ['type' => 'text', 'align' => 'center', 'text' => ''],
    
            ['type' => 'text', 'align' => 'left', 'text' => 'Tipo: ' . $order_type],
            ['type' => 'text', 'align' => 'left', 'text' => 'Comanda #' . $sale_object->selected_number_name],
            ['type' => 'text', 'align' => 'left', 'text' => 'Orden: ' . $sale_object->sale_no],
            ['type' => 'text', 'align' => 'left', 'text' =>
                'Fecha: ' . date($this->session->userdata('date_format'), strtotime($sale_object->sale_date)) .
                ' ' . date('H:i', strtotime($sale_object->order_time)) ],
            ['type' => 'text', 'align' => 'left', 'text' => 'Cliente: ' . $sale_object->customer_name]
        ];
        if (!empty($sale_object->customer_phone)) {
            $content[] = ['type' => 'text', 'align' => 'left', 'text' => 'CEL: ' . $sale_object->customer_phone];
        }
        if (!empty($sale_object->customer_address)) {
            $content[] = ['type' => 'text', 'align' => 'left', 'text' => 'Dir: ' . $sale_object->customer_address];
        }
        
        $content[] = ['type' => 'text', 'align' => 'left', 'text' => 'Mesero: ' . $sale_object->waiter_name];
        $content[] =     ['type' => 'text', 'align' => 'center', 'text' => ''];
        $content[] =    ['type' => 'text', 'align' => 'center', 'text' => '------------------------------'];
    
        // Listado de productos
        foreach ($sale_items as $row) {
            $content[] = ['type' => 'text', 'align' => 'left', 'text' => $row->qty . '    ' . $row->menu_name];
            if (!empty($row->modifiers)) {
                foreach ($row->modifiers as $modifier) {
                    $content[] = ['type' => 'text', 'align' => 'left', 'text' => '   + ' . $modifier->name];
                }
            }
            if (!empty($row->menu_note)) {
                $content[] = ['type' => 'text', 'align' => 'left', 'text' => 'Nota: ' . $row->menu_note];
            }
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => '---'];
        }
    
        // Pie del ticket
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => '******************************'];
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'Impreso: ' . date('H:i')];
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => ''];
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => ''];
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => ''];
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => ''];
        $content[] = ['type' => 'cut'];
    
        // Configuración de la impresora
        // El path de la impresora lo puedes definir fijo o según la cocina:
        $kitchen_data = $this->Common_model->getDataById($kitchen_id, "tbl_kitchens");
        $printer = $this->Common_model->getPrinterInfoById($kitchen_data->printer_id);
        $printer_path = isset($printer->path) ? $printer->path : 'kitchen_' . $kitchen_id;
        $print_format = isset($printer->print_format) ? $printer->print_format : '80mm';
        $width = ($print_format == "80mm") ? 80 : 58;
    
        // echo '<pre>';
        // var_dump($kitchen_data); 
        // var_dump($printer); 
        // var_dump($printer_path); 
        // var_dump($print_format); 
        // echo '<pre>';
        $printRequest = [
            'printer' => $printer_path,
            'width' => $width,
            'content' => filterArrayRecursivelyEscPos($content)
        ];
    
        // echo '<pre>';
        // var_dump($printRequest); 
        // echo '<pre>';
        
        // Codificación y compresión
        $data = json_encode($printRequest);
        $compressed = gzdeflate($data, 9);
        $base64 = base64_encode($compressed);
    
        $printers_array[] = $base64;
    
        echo empty($printers_array) ? json_encode([]) : json_encode($printers_array);
        return;
    }

     /**
     * get all information of a sale
     * @access public
     * @return object
     * @param int
     */
    public function get_all_information_of_a_sale($sale_no){
        $sales_information = $this->get_all_information_of_a_sale_kitchen($sale_no);
        //     echo 'get_all_information_of_a_sale';
        //    echo '<pre>';
        //    var_dump($sales_information); 
        //    echo '<pre>';
        

        @$sales_information->selected_number = $sales_information->number_slot ?? '';
        @$sales_information->selected_number_name = $sales_information->number_slot_name ?? '';
        $sales_information->sub_total = getAmtP(isset($sales_information->sub_total) && $sales_information->sub_total?$sales_information->sub_total:0);
        $sales_information->paid_amount = getAmtP(isset($sales_information->paid_amount) && $sales_information->paid_amount?$sales_information->paid_amount:0);
        $sales_information->due_amount = getAmtP(isset($sales_information->due_amount) && $sales_information->due_amount?$sales_information->due_amount:0);
        $sales_information->vat = getAmtP(isset($sales_information->vat) && $sales_information->vat?$sales_information->vat:0);
        $sales_information->total_payable = getAmtP(isset($sales_information->total_payable) && $sales_information->total_payable?$sales_information->total_payable:0);
        $sales_information->total_item_discount_amount = getAmtP(isset($sales_information->total_item_discount_amount) && $sales_information->total_item_discount_amount?$sales_information->total_item_discount_amount:0);
        $sales_information->sub_total_with_discount = getAmtP(isset($sales_information->sub_total_with_discount) && $sales_information->sub_total_with_discount?$sales_information->sub_total_with_discount:0);
        $sales_information->sub_total_discount_amount = getAmtP(isset($sales_information->sub_total_discount_amount) && $sales_information->sub_total_discount_amount?$sales_information->sub_total_discount_amount:0);
        $sales_information->total_discount_amount = getAmtP(isset($sales_information->total_discount_amount) && $sales_information->total_discount_amount?$sales_information->total_discount_amount:0);
        $sales_information->delivery_charge = (isset($sales_information->delivery_charge) && $sales_information->delivery_charge?$sales_information->delivery_charge:0);
        $this_value = $sales_information->sub_total_discount_value;
        $disc_fields = explode('%',$this_value);
        $discP = isset($disc_fields[1]) && $disc_fields[1]?$disc_fields[1]:'';
        if ($discP == "") {
        } else {
            $sales_information->sub_total_discount_value = getAmtP(isset($sales_information->sub_total_discount_value) && $sales_information->sub_total_discount_value?$sales_information->sub_total_discount_value:0);
        }
        $items_by_sales_id = $this->Sale_model->getAllItemsFromSalesDetailBySalesIdKitchen($sales_information->id);

        foreach($items_by_sales_id as $single_item_by_sale_id){
            $modifier_information = $this->Sale_model->getModifiersBySaleAndSaleDetailsIdKitchen($sales_information->id,$single_item_by_sale_id->sales_details_id);
            $single_item_by_sale_id->modifiers = $modifier_information;

            $modifiers_id = '';
            $modifiers_name = '';
            $modifiers_price = '';
            foreach($modifier_information as $ky1=>$val){
                $modifiers_id.=$val->modifier_id;
                $modifiers_name.=$val->name;
                $modifiers_price.=$val->modifier_price;

                if($ky1<(sizeof($modifier_information))-1){
                    $modifiers_id.=",";
                    $modifiers_name.=",";
                    $modifiers_price.=",";
                }
            }
            $single_item_by_sale_id->modifiers_id = $modifiers_id;
            $single_item_by_sale_id->modifiers_name = $modifiers_name;
            $single_item_by_sale_id->modifiers_price = $modifiers_price;
            
        }
        $sales_details_objects = $items_by_sales_id;
        if(isset($sales_details_objects[0]) && $sales_details_objects[0]){
            $sales_details_objects[0]->menu_price_without_discount = getAmtP(isset($sales_details_objects[0]->menu_price_without_discount) && $sales_details_objects[0]->menu_price_without_discount?$sales_details_objects[0]->menu_price_without_discount:0);
            $sales_details_objects[0]->menu_price_with_discount = getAmtP(isset($sales_details_objects[0]->menu_price_with_discount) && $sales_details_objects[0]->menu_price_with_discount?$sales_details_objects[0]->menu_price_with_discount:0);
            $sales_details_objects[0]->menu_unit_price = getAmtP(isset($sales_details_objects[0]->menu_unit_price) && $sales_details_objects[0]->menu_unit_price?$sales_details_objects[0]->menu_unit_price:0);
            $sales_details_objects[0]->menu_vat_percentage = getAmtP(isset($sales_details_objects[0]->menu_vat_percentage) && $sales_details_objects[0]->menu_vat_percentage?$sales_details_objects[0]->menu_vat_percentage:0);
            $sales_details_objects[0]->discount_amount = getAmtP(isset($sales_details_objects[0]->discount_amount) && $sales_details_objects[0]->discount_amount?$sales_details_objects[0]->discount_amount:0);
    
            $this_value = $sales_details_objects[0]->menu_discount_value;
            $disc_fields = explode('%',$this_value);
            $discP = isset($disc_fields[1]) && $disc_fields[1]?$disc_fields[1]:'';
            if ($discP == "") {
            } else {
                $sales_details_objects[0]->menu_discount_value = getAmtP(isset($sales_details_objects[0]->menu_discount_value) && $sales_information->menu_discount_value?$sales_details_objects[0]->menu_discount_value:0);
            }
    
        }
      
        $sale_object = $sales_information;
        $sale_object->items = $sales_details_objects;
        $sale_object->tables_booked = '';
        return $sale_object;
    }
    public function get_all_information_of_a_sale_kitchen($sale_no){
        $sales_information = getKitchenSaleDetailsBySaleNoWithDeleted($sale_no);
        return $sales_information;
    }

    public function get_content_data_direct_print() {
        $sale_no = $this->input->get('sale_no');
        $kitchen_id = $this->input->get('kitchen_id');
        $all = $this->input->get('all');
        $this->load->model('Kitchen_model');
        $data = $this->get_printers_direct_print_array($sale_no, $kitchen_id, $all); // La función que te armé antes
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    
    public function get_printers_direct_print_array($sale_no, $kitchen_id,$all = "1") {
        $result = [];
    
        // Obtener la información de la venta
        $sale_object = $this->get_all_information_of_a_sale($sale_no);
        if(!$sale_object) return [];
    
        $sale_id = $sale_object->id;
    
        // NUEVO SELECT Y JOINS
        $this->db->select("tbl_printers.*,tbl_kitchen_sales_details.*, tbl_kitchens.name as kitchen_name, tbl_kitchens.id as kitchen_id, tbl_kitchen_sales_details.outlet_id");
        $this->db->from('tbl_kitchen_sales_details');
        $this->db->join('tbl_food_menus', 'tbl_food_menus.id = tbl_kitchen_sales_details.food_menu_id', 'left');
        $this->db->join('tbl_kitchen_categories', 'tbl_kitchen_categories.cat_id = tbl_food_menus.category_id', 'left');
        $this->db->join('tbl_kitchens', 'tbl_kitchens.id = tbl_kitchen_categories.kitchen_id', 'left');
        $this->db->join('tbl_printers', 'tbl_printers.id = tbl_kitchens.printer_id', 'left');
        $this->db->where('tbl_kitchen_sales_details.sales_id', $sale_id);
        $this->db->where('tbl_kitchens.id', $kitchen_id);
        $this->db->where("tbl_kitchen_categories.del_status", "Live");
        $this->db->order_by('tbl_kitchen_sales_details.id', 'ASC');
        $this->db->group_by('tbl_kitchen_sales_details.id');
        $sale_items = $this->db->get()->result();
    
        // echo '<pre>';
        // var_dump($sale_items); 
        // echo '<pre>';
        
        // Traer los modificadores para cada item y armar campos
        foreach ($sale_items as $item) {
            $modifiers = $this->Kitchen_model->getModifiersBySaleAndSaleDetailsId($sale_id, $item->id);
            // echo '<pre>';
            // var_dump($sale_id); 
            // var_dump($item->id); 
            // var_dump($modifiers); 
            // echo '<pre>';
            
            $item->modifiers = $modifiers;
    
            $modifiers_id = [];
            $modifiers_name = [];
            $modifiers_price = [];
            foreach ($modifiers as $mod) {
                $modifiers_id[] = $mod->modifier_id;
                $modifiers_name[] = $mod->name;
                $modifiers_price[] = $mod->modifier_price;
            }
            $item->modifiers_id = implode(',', $modifiers_id);
            $item->modifiers_name = implode(',', $modifiers_name);
            $item->modifiers_price = implode(',', $modifiers_price);
        }
    
        // Determinar order_type
        $order_type = '';
        if($sale_object->order_type==1){
            $order_type = lang('dine');
        }else if($sale_object->order_type==2){
            $order_type = lang('take_away');
        }else if($sale_object->order_type==3){
            $order_type = lang('delivery');
        }
    
        // Tomar datos de impresora y cocina del primer item (ya que todos los items tienen esta info)
        $first_item = isset($sale_items[0]) ? $sale_items[0] : null;
        $ipvfour_address = $first_item ? (isset($first_item->ipvfour_address) ? $first_item->ipvfour_address : 'http://127.0.0.1/') : 'http://127.0.0.1/';
        $store_name = $first_item ? (lang('KOT') . ':' . $first_item->kitchen_name) : '';
        $printer_name = $first_item ? (isset($first_item->printer_name) ? $first_item->printer_name : '') : '';
        $printer_path = $first_item ? (isset($first_item->path) ? $first_item->path : '') : '';
        $characters_per_line = $first_item ? (isset($first_item->characters_per_line) ? $first_item->characters_per_line : '') : '';
        $open_cash_drawer_when_printing_invoice = $first_item ? (isset($first_item->open_cash_drawer_when_printing_invoice) ? $first_item->open_cash_drawer_when_printing_invoice : '') : '';
        $printer_type = $first_item ? (isset($first_item->type) ? $first_item->type : '') : '';
        $printer_width = $first_item ? (isset($first_item->width) ? $first_item->width : '') : '';
        $type = $first_item ? (isset($first_item->type) ? $first_item->type : '') : '';
        $outlet_id = $first_item ? (isset($first_item->outlet_id) ? $first_item->outlet_id : '') : '';
        $printer_port = $first_item ? (isset($first_item->printer_port) ? $first_item->printer_port : '') : '';
        $profile_ = $first_item ? (isset($first_item->profile_) ? $first_item->profile_ : '') : '';
        $printer_ip_address = $first_item ? (isset($first_item->printer_ip_address) ? $first_item->printer_ip_address : '') : '';
        // $data['type'] = $printer->type;
        // $data['printer_ip_address'] = $printer->printer_ip_address;
        // $data['printer_port'] = $printer->printer_port;
        // $data['path'] = $printer->path;
        // $data['characters_per_line'] = $printer->characters_per_line;
        // $data['profile_'] = $printer->profile_;
    
        // Formato del ticket (items)
        $items = "\n";
        $count = 1;
        $count_item_to_print = 0;
        foreach ($sale_items as $item) {
            // $items .= "#{$count} ".($item->menu_name).": " .($item->qty) . "\n";
            // $count++;
            // if($item->menu_combo_items && $item->menu_combo_items!=null){
            //     $items .= "Combo: ".$item->menu_combo_items."\n";
            // }
            // if(isset($item->menu_note)){
            //     $items .= "Nota: ".$item->menu_note."\n";
            // }
            // if(count($item->modifiers)>0){
            //     foreach($item->modifiers as $modifier){
            //         $items .= "   ".($modifier->name).": ".$item->qty."\n";
            //     }
            // }
            
            $pass = false;
            if ($all == "1"){
                $pass = true;
            } else {
                if ($item->tmp_qty > 0){
                    $pass = true;
                }
            }
            if ($pass == true) {
                if ($all == "1") {
                    $qty = $item->qty;
                } else {
                    $qty = $item->tmp_qty;
                }
                $items .= printText((($qty) . " * ".(getPlanData($item->menu_name))), $characters_per_line)."\n";
                $count++;
                $count_item_to_print++;
                
                if ($item->menu_combo_items && $item->menu_combo_items != null) {
                    $items .= (printText(lang('combo_txt') . ': ' . $item->menu_combo_items, $characters_per_line) . "\n");
                }
                
                if (isset($item->menu_note) && strlen($item->menu_note) > 0) {
                    $items .= (printText(lang('note') . ': ' . $item->menu_note, $characters_per_line) . "\n");
                }
                
                if (isset($item->item_note) && strlen($item->item_note) > 0) {
                    $items .= (printText(lang('note') . ': ' . $item->item_note, $characters_per_line) . "\n");
                }
                
                if (count($item->modifiers) > 0) {
                    foreach ($item->modifiers as $modifier) {
                        $items .= "   " . printText( ($qty). " * " .(getPlanData($modifier->name))  , ($characters_per_line - 3)) . "\n";
                    }
                }
                
                $count++;
            }
        }
    
        $result[] = [
            'printer_port' => $printer_port,
            'profile_' => $profile_,
            'printer_ip_address' => $printer_ip_address,
            'ipvfour_address' => $ipvfour_address,
            'store_name' => $store_name,
            'printer_name' => $printer_name,
            'path' => $printer_path,
            'characters_per_line' => $characters_per_line,
            'open_cash_drawer_when_printing_invoice' => $open_cash_drawer_when_printing_invoice,
            'printer_type' => $printer_type,
            'printer_width' => $printer_width,
            'type' => $type,
            'outlet_id' => $outlet_id,
            'sale_type' => $order_type,
            'sale_no_p' => $sale_object->sale_no,
            'date' => escape_output(date($this->session->userdata('date_format'), strtotime($sale_object->sale_date))),
            'time_inv' => $sale_object->order_time,
            'sales_associate' => $sale_object->user_name,
            'customer_name' => $sale_object->customer_name,
            'customer_phone' => isset($sale_object->customer_phone) && $sale_object->customer_phone?$sale_object->customer_phone:'',
            'selected_number_name' => isset($sale_object->selected_number_name) && $sale_object->selected_number_name?$sale_object->selected_number_name:'',
            'selected_number' => isset($sale_object->selected_number) && $sale_object->selected_number?$sale_object->selected_number:'',
            'customer_address' => getCustomerAddress($sale_object->customer_id),
            'waiter_name' => $sale_object->waiter_name,
            'customer_table' => $sale_object->orders_table_text,
            'lang_order_type' => lang('order_type'),
            'lang_Invoice_No' => lang('Invoice_No'),
            'lang_date' => lang('date'),
            'lang_Sales_Associate' => lang('Sales_Associate'),
            'lang_customer' => lang('customer'),
            'lang_address' => lang('address'),
            'lang_gst_number' => lang('gst_number'),
            'lang_waiter' => lang('waiter'),
            'lang_table' => lang('table'),
            'print_type' => 'KOT',
            'items' => $items
        ];
    
        return $result;
    }
}
