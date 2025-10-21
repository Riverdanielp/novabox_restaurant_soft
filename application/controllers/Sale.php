<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Sale extends Cl_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Authentication_model');
        $this->load->model('Common_model');
        $this->load->model('Sale_model');
        $this->load->model('Kitchen_model');
        $this->load->model('Waiter_model');
        $this->load->model('Master_model');
        $this->load->model('Inventory_model');
        $this->load->model('Customer_due_receive_model');
        $this->load->library('form_validation');

        $this->load->library('facturasend');

        // $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
        $this->Common_model->setDefaultTimezone();
        if (!$this->session->has_userdata('user_id') && $this->session->has_userdata('is_online_order')!="Yes") {
            redirect('Authentication/index');
        }

        if (!$this->session->has_userdata('outlet_id')) {
            $this->session->set_flashdata('exception_2', 'Please click on green Enter button of an outlet');
            redirect('Outlet/outlets');
        }
        
        $is_waiter = $this->session->userdata('is_waiter');
        $designation = $this->session->userdata('designation');
        //check register is open or not
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

        $login_session['active_menu_tmp'] = '';
        $this->session->set_userdata($login_session);
    }

     /**
     * sales info
     * @access public
     * @return void
     * @param no
     */
    public function sales($id='') {
        //start check access function
        $segment_2 = $this->uri->segment(2);
        $segment_3 = $this->uri->segment(3);
        $controller = "123";
        $function = "";

        if($segment_2=="sales"){
            $function = "view";
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
        $data['edit_return_id'] = $id;
        $data['main_content'] = $this->load->view('sale/sales', $data, TRUE);
        $this->load->view('userHome', $data);
    }
    public function refund($encrypted_id = "") {
        //start check access function
        $segment_2 = $this->uri->segment(2);
        $segment_3 = $this->uri->segment(3);
        $controller = "123";
        $function = "";

        if($segment_2=="refund"){
            $function = "refund";
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
        $purchase_info = array();
        if (htmlspecialcharscustom($this->input->post('submit'))) {

            $this->form_validation->set_rules('total_refund', lang('total_refund'), 'required|max_length[50]');
            if ($this->form_validation->run() == TRUE) {
                $purchase_info['counter_id'] = $this->session->userdata('counter_id');
                $purchase_info['refund_date_time'] = date('Y-m-d H:i:s');
                $purchase_info['refund_payment_id'] = htmlspecialcharscustom($this->input->post($this->security->xss_clean('payment_id')));
                $purchase_info['total_refund'] = htmlspecialcharscustom($this->input->post($this->security->xss_clean('total_refund')));
                $this->Common_model->updateInformation($purchase_info, $id, "tbl_sales");
                /*This variable could not be escaped because this is an array field*/
                $this->saveRefundItems($_POST['qty'], $id, 'tbl_sales_details');
                $this->session->set_flashdata('exception',lang('update_success'));

                redirect('Sale/sales');
            } else {
                $data = array();
                $data['encrypted_id'] = $encrypted_id;
                $data['sale'] = $this->Common_model->getDataById($id, "tbl_sales");
                $data['sale_details'] = $this->Sale_model->getAllItemsFromSalesDetailBySalesId($id);
                $data['main_content'] = $this->load->view('sale/refund', $data, TRUE);
                $this->load->view('userHome', $data);
            }
        } else {
            $data = array();
            $data['encrypted_id'] = $encrypted_id;
            $data['sale'] = $this->Common_model->getDataById($id, "tbl_sales");
            $data['sale_details'] = $this->Sale_model->getAllItemsFromSalesDetailBySalesId($id);
            $data['main_content'] = $this->load->view('sale/refund', $data, TRUE);
            $this->load->view('userHome', $data);
        }
    }
    public function saveRefundItems($qtys, $sale_id, $table_name) {
        $main_arry = array();
        $tmp_txt ="<br><b>Items:</b><br>";
        foreach ($qtys as $row => $qty):
            /*This all variables could not be escaped because this is an array field*/
            $fmi = array();
            $fmi['qty'] = $qty;
            $fmi['item_id'] = $_POST['item_id'][$row];
            $fmi['name'] = $_POST['name'][$row];
            $fmi['price'] = $_POST['price'][$row];
            $fmi['vat'] = $_POST['vat'][$row];
            $fmi['discount'] = $_POST['discount'][$row];
            $fmi['refund_qty'] = $_POST['refund_qty'][$row];
            $main_arry[] = $fmi;
            $price = $_POST['price'][$row];
            $tmp_txt.=$_POST['name'][$row]."("."$qty X $price".")";

            if($row < (sizeof($qtys) -1)){
                $tmp_txt.=", ";
            }

        endforeach;
        $sale['refund_content'] = json_encode($main_arry);
        $this->Common_model->updateInformation($sale, $sale_id, "tbl_sales");

        $txt = '';
        $sale = $this->Common_model->getDataById($sale_id, "tbl_sales");
        $customer_info = getCustomerData($sale->customer_id);
        $txt.="Sale No: ".$sale->sale_no.", ";
        $txt.="Sale Date: ".date($this->session->userdata('date_format'), strtotime($sale->sale_date)).", ";
        $txt.="Refund Date: ".date($this->session->userdata('date_format'), strtotime($sale->refund_date_time)).", ";
        $txt.="Customer: ".(isset($customer_info) && $customer_info->name?$customer_info->name:'---')." - ".(isset($customer_info) && $customer_info->phone?$customer_info->phone:'').", ";
        $txt.="Total Payable: ".$sale->total_payable.", ";
        $txt.="Total Refund: ".$sale->total_refund;
        $txt.=$tmp_txt;
        putAuditLog($this->session->userdata('user_id'),$txt,"Refund Sale",date('Y-m-d H:i:s'));
    }
    public function getDetailsRefund()
    {
        $sale_id = $this->input->post('sale_id');
        $sale = $this->Common_model->getDataById($sale_id, "tbl_sales");
        $html = '';
        $g_total = 0;
        $sale_json = (Object)json_decode($sale->refund_content);
        if ($sale_json && !empty($sale_json)) {
            foreach ($sale_json as $pi) {
                $total = ((float)$pi->price*(float)$pi->refund_qty) - ($pi->discount?$pi->discount:0) + ((float)$pi->vat*(float)$pi->refund_qty);
                $html .= '<tr class="rowCount">
                                            <td>'.$pi->name.'</td>
                                            <td>'.$pi->qty.'</td>
                                            <td>'.getAmtP($pi->price).'</td>
                                            <td>'.getAmtP($pi->vat).'</td>
                                            <td>'.getAmtP($pi->discount).'</td>
                                            <td>'.$pi->refund_qty.'</td>
                                            <td>'.getAmtP($total).'</td>
                                        </tr>';
                $g_total+=$total;
            }
            $html .= '<tr class="rowCount">
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <th class="pull-right">'.lang("total").'=</th>
                                            <th>'.getAmtP($g_total).'</th>
                                        </tr>';
        }
        //This variable could not be escaped because this is html content
        $return['refund_date_time'] = $sale->refund_date_time;
        $return['html'] = $html;
        echo json_encode($return);
    }
     /**
     * sales info
     * @access public
     * @return void
     * @param no
     */
    public function exportDailySales() {
        //start check access function
        $segment_2 = $this->uri->segment(2);
        $segment_3 = $this->uri->segment(3);
        $controller = "123";
        $function = "";

        if($segment_2=="exportDailySales"){
            $function = "exportDailySales";
        }else{
            $this->session->set_flashdata('exception_er', lang('menu_not_permit_access'));
            redirect('Authentication/userProfile');
        }
        if(!checkAccess($controller,$function)){
            $this->session->set_flashdata('exception_er', lang('menu_not_permit_access'));
            redirect('Authentication/userProfile');
        }
        //end check access function

        $fileName = 'Sale Data-'.(date("Y-m-d")).'.xlsx';

        // load excel library
        $this->load->library('excel');
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        // set Header
        $objPHPExcel->getActiveSheet()->SetCellValue('A1', lang('customer'));
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', lang('date'));
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', lang('reference'));
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', lang('items'));
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', lang('subtotal'));
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', lang('discount'));
        $objPHPExcel->getActiveSheet()->SetCellValue('G1', lang('vat'));
        $objPHPExcel->getActiveSheet()->SetCellValue('H1', lang('g_total'));
        $objPHPExcel->getActiveSheet()->SetCellValue('I1', lang('payment_method'));
        // set Row
        $rowCount = 2;
        $sales = $this->Sale_model->exportDailySale();
        foreach ($sales as $key=>$value){
            $items = '';
            $details = $this->Sale_model->getAllItemsFromSalesDetailBySalesId($value->id);
            foreach ($details as $key1=>$value1){
                $items.= $value1->menu_name." X ".$value1->qty;
                if($key1 < (sizeof($details) -1)){
                    $items.= "\n";
                }
            }
            $payment_details = '';
            $outlet_id = $this->session->userdata('outlet_id');
            $salePaymentDetails = salePaymentDetails($value->id,$outlet_id);
            if(isset($salePaymentDetails) && $salePaymentDetails):
                ?>
                <?php foreach ($salePaymentDetails as $ky=>$payment):
                $txt_point = '';
                if($payment->id==5){
                    $txt_point = " (Usage Point:".$payment->usage_point.")";
                }
                $payment_details.= escape_output($payment->payment_name.$txt_point).":".escape_output(getAmtPCustom($payment->amount));
                if($ky<sizeof($salePaymentDetails)-1){
                    $payment_details.=" - ";
                }
            endforeach;
            endif;


            $objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, escape_output($value->customer_name));
            $objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, escape_output(date($this->session->userdata('date_format'), strtotime($value->sale_date))));
            $objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, escape_output($value->sale_no));
            $objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $items);
            $objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, escape_output(getAmtP($value->sub_total)));
            $objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, escape_output(getAmtP($value->total_discount_amount)));
            $objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, escape_output(getAmtP($value->vat)));
            $objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, escape_output(getAmtP($value->total_payable)));
            $objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, escape_output($payment_details));
            $rowCount++;
        }
        $objPHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setWrapText(true);
        $objWriter  = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save("asset/excel/".$fileName);
        // download file
        header("Content-Type: application/vnd.ms-excel");
        redirect(base_url()."asset/excel/".$fileName);
    }

    /**
     * reset Daily Sales Data
     * @access public
     * @return void
     * @param no
     */
    public function resetDailySales() {
        //start check access function
        $segment_2 = $this->uri->segment(2);
        $segment_3 = $this->uri->segment(3);
        $controller = "123";
        $function = "";

        if($segment_2=="resetDailySales"){
            $function = "resetDailySales";
        }else{
            $this->session->set_flashdata('exception_er', lang('menu_not_permit_access'));
            redirect('Authentication/userProfile');
        }
        if(!checkAccess($controller,$function)){
            $this->session->set_flashdata('exception_er', lang('menu_not_permit_access'));
            redirect('Authentication/userProfile');
        }
        //end check access function
        //truncate all transactional data
        $outlet_id = $this->session->userdata('outlet_id');
        // $this->db->delete('tbl_sales', array('outlet_id ' => $outlet_id));
        // $this->db->delete('tbl_sales_details', array('outlet_id ' => $outlet_id));
        // $this->db->delete('tbl_sales_details_modifiers', array('outlet_id ' => $outlet_id));
        // $this->db->delete('tbl_sale_consumptions', array('outlet_id ' => $outlet_id));
        // $this->db->delete('tbl_sale_consumptions_of_menus', array('outlet_id ' => $outlet_id));
        // $this->db->delete('tbl_sale_consumptions_of_modifiers_of_menus', array('outlet_id ' => $outlet_id));
        // $this->db->delete('tbl_sale_payments', array('outlet_id ' => $outlet_id));
        // $this->db->delete('tbl_kitchen_sales', array('outlet_id ' => $outlet_id));
        // $this->db->delete('tbl_kitchen_sales_details', array('outlet_id ' => $outlet_id));
        // $this->db->delete('tbl_kitchen_sales_details_modifiers', array('outlet_id ' => $outlet_id));
        
        $this->session->set_flashdata('exception', lang('truncate_sale_update_success'));
        redirect('Sale/sales');
    }
     /**
     * delete Sale
     * @access public
     * @return void
     * @param int
     */
    public function deleteSale($id) {
        //start check access function
        $segment_2 = $this->uri->segment(2);
        $segment_3 = $this->uri->segment(3);
        $controller = "123";
        $function = "";

        if($segment_2=="deleteSale"){
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

        $event_txt = getSaleText($id);
        putAuditLog($this->session->userdata('user_id'),$event_txt,"Deleted Sale",date('Y-m-d H:i:s'));

        $isDeleted = $this->delete_specific_order_by_sale_id($id);
        if($isDeleted){
            $this->session->set_flashdata('exception', 'Information has been deleted successfully!');
            redirect('Sale/sales');
        }else{
            $this->session->set_flashdata('exception_2', 'Something went wrong!');
            redirect('Sale/sales');
        }

    }
     /**
     * POS screen
     * @access public
     * @return void
     * @param int
     */
    public function POS($user_id='', $outlet_id='',$sale_id=""){
        $is_waiter = $this->session->userdata('is_waiter');
        $counter_id = $this->session->userdata('counter_id');
        $company_id = $this->session->userdata('company_id');
        $is_self_order = $this->session->userdata('is_self_order');
        $is_online_order = $this->session->userdata('is_online_order');
        if($is_self_order=="Yes" && !$outlet_id){
            echo "<h1 style='color:red;padding:10px;text-align:center'>".lang('self_order_scan_error')."</h1>";exit;
        }

        if($is_self_order=="Yes"){
            $company = $this->Common_model->getDataById($company_id, "tbl_companies");
              if(isset($company) && $company->sos_enable_self_order == "No"){
                    echo "<h1 style='color:red;padding:10px;text-align:center'>".lang('self_order_scan_error_1')."</h1>";exit;
              }
        }
       
        if($counter_id){
                   $counter_details = $this->Common_model->getPrinterIdByCounterId($counter_id);
                   $printer_info = $this->Common_model->getPrinterInfoById($counter_details->invoice_printer_id);
                   $print_arr = [];
                   $print_arr['counter_id'] = $counter_id;
                   $print_arr['printer_id'] = $counter_details->invoice_printer_id;
                   if($printer_info){
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
                   }
                   //bill
                   $printer_info_bill = $this->Common_model->getPrinterInfoById($counter_details->bill_printer_id);
              
                   $print_arr['bill_printer_id'] = $counter_details->bill_printer_id;
                   if($printer_info_bill){
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
                   }
                   $this->session->set_userdata($print_arr);
        }

        if(isset($is_waiter) && $is_waiter!="Yes" && $is_online_order!="Yes"){
          
            //start check access function
            $segment_2 = $this->uri->segment(2);
            $segment_3 = $this->uri->segment(3);
            $controller = "73";
            $function = "";
            if($segment_2=="POS" || $segment_2=="pos"){
                $function = "pos_1";
            }else{
                $this->session->set_flashdata('exception_er', lang('menu_not_permit_access'));
                redirect('Authentication/userProfile');
            }

            if(!checkAccess($controller,$function)){
                $this->session->set_flashdata('exception_er', lang('menu_not_permit_access'));
                redirect('Authentication/userProfile');
            }
        }

        if(!$user_id || !$outlet_id){
            redirect('POSChecker/posAndWaiterMiddleman');
        }


        if(isServiceAccessOnly('sGmsJaFJE')){
            if($sale_id==''){
                if(!checkCreatePermissionInvoice()){
                    $this->session->set_flashdata('exception_1',lang('not_permission_invoice_create_error'));
                    redirect("Sale/sales");
                }
            }

        }
        
        $company_id = $this->session->userdata('company_id');

        $outlet_id = $this->session->userdata('outlet_id');

        $data = array();
        $data['customers'] = $this->Common_model->getAllCustomersByCompany($company_id, 1);
        $data['food_menus'] = $this->Sale_model->getTopFoodMenus(24); //getAllFoodMenus(); //[];
        $this->Sale_model->attachModifiersToMenus($data['food_menus']);
        if(isset($data['food_menus']) && $data['food_menus']){
            foreach ($data['food_menus'] as $key=>$value){
                $variations = $this->Common_model->getAllByCustomId($value->id,"parent_id","tbl_food_menus",$order='');
                $data['food_menus'][$key]->is_variation = isset($variations) && $variations?'Yes':'No';
                $data['food_menus'][$key]->variations = $variations;
                    $kitchen = getKitchenNameAndId($value->category_id);
                    $data['food_menus'][$key]->kitchen_id =$kitchen[0];
                    $data['food_menus'][$key]->kitchen_name =$kitchen[1];
            }
        }
        $data['denominations'] = $this->Common_model->getDenomination($company_id);
        $data['menu_categories'] = $this->Common_model->getSortingForPOS();
        $data['menu_modifiers'] = $this->Sale_model->getAllMenuModifiers();
        $data['waiters'] = $this->Sale_model->getWaitersForThisCompany($company_id,'tbl_users');
        $data['MultipleCurrencies'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_multiple_currencies");
        $data['users'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_users");
        $data['outlet_information'] = $this->Common_model->getDataById($outlet_id, "tbl_outlets");
        $data['payment_methods'] = $this->Sale_model->getAllPaymentMethods();
        $data['payment_method_finalize'] = $this->Sale_model->getAllPaymentMethodsFinalize();
        $data['deliveryPartners'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_delivery_partners");
        $data['areas'] = $this->Common_model->getAllByOutletId($outlet_id, 'tbl_areas');
        $data['only_modifiers'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, 'tbl_modifiers');
        $data['kitchens'] = $this->Common_model->getAllByOutletId($outlet_id, "tbl_kitchens");
        $data['notifications'] = $this->get_new_notification();
        $data['sale_details'] = $this->Common_model->getDataById($sale_id, "tbl_sales");
        $data['ing_categories'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, 'tbl_ingredient_categories');
        $data['tables'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_tables");
        $this->db->where("outlet_id", $outlet_id);
        $this->db->where("del_status", "Live");
        // $this->db->order_by("name", "ASC");
        $data['numbers'] = $this->db->get("tbl_numeros")->result();
        $this->load->view('sale/POS/main_screen', $data);
    }

    public function get_food_menus_ajax() {
        $this->load->model('Sale_model');
        $food_menus = $this->Sale_model->getAllFoodMenus();
        if(isset($data['food_menus']) && $data['food_menus']){
            foreach ($data['food_menus'] as $key=>$value){
                $variations = $this->Common_model->getAllByCustomId($value->id,"parent_id","tbl_food_menus",$order='');
                $data['food_menus'][$key]->is_variation = isset($variations) && $variations?'Yes':'No';
                $data['food_menus'][$key]->variations = $variations;
                    $kitchen = getKitchenNameAndId($value->category_id);
                    $data['food_menus'][$key]->kitchen_id =$kitchen[0];
                    $data['food_menus'][$key]->kitchen_name =$kitchen[1];
            }
        }
        $menu_modifiers = $this->Sale_model->getAllMenuModifiers();
        // Si necesitas otras variables, agrégalas aquí y pásalas a json_encode
    
        // Puedes filtrar/simplificar los datos si son muy grandes
    
        echo json_encode([
            'food_menus' => $food_menus,
            'menu_modifiers' => $menu_modifiers
            // agrega otros datos si los necesitas
        ]);
    }

    public function search_food_menus_ajax()
    {
        // Solo permitir AJAX
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        $term = $this->input->get('q', true);
        $category_id = $this->input->get('category_id', true);
        $type = $this->input->get('type', true); // opcional: veg, bev, combo
    
        $outlet_id = $this->session->userdata('outlet_id');
    
        $results = $this->Sale_model->searchFoodMenus($term, $category_id, $type, $outlet_id);
        $this->Sale_model->attachModifiersToMenus($results);
        if(isset($results) && $results){
            foreach ($results as $key=>$value){
                $variations = $this->Common_model->getAllByCustomId($value->id,"parent_id","tbl_food_menus",$order='');
                $results[$key]->is_variation = isset($variations) && $variations?'Yes':'No';
                $results[$key]->variations = $variations;
                    $kitchen = getKitchenNameAndId($value->category_id);
                    $results[$key]->kitchen_id =$kitchen[0];
                    $results[$key]->kitchen_name =$kitchen[1];
            }
        }
    
        // Puedes convertir aquí los objetos en arrays, si lo deseas
        echo json_encode($results);
    }

    public function get_modifiers_by_menu_id() {
        $menu_id = $this->input->get('menu_id');
        $this->load->model('Sale_model');
        $modifiers = $this->Sale_model->getModifiersByMenuId($menu_id);

        // Arma el string exactamente igual a tu formato
        $modifiers_str = '';
        $total = count($modifiers);
        $j = 1;
        foreach ($modifiers as $mod) {
            $modifiers_str .= "{menu_modifier_id:'".$mod->modifier_id."',modifier_row_id:'".$mod->id."',menu_modifier_name:'".getPlanText($mod->name)."',tax_information:'".$mod->tax_information."',menu_modifier_price:'".getAmtP($mod->price)."' }";
            if($j < $total) $modifiers_str .= ",";
            $j++;
        }
        echo $modifiers_str; // responde exactamente como tú lo usabas
    }

    public function debugQueryTimesPOS($user_id='', $outlet_id='', $company_id='', $is_waiter=''){
        $this->load->helper('url'); // por si falta
        $this->load->helper('date'); // para timestamps si necesitás
        $this->load->model('Common_model');
        $this->load->model('Sale_model');
    
        function log_query_time($label, $start_time) {
            $end_time = microtime(true);
            $duration = round($end_time - $start_time, 4);
            // log_message('debug', "[QUERY TIME] $label: {$duration} segundos");
            echo "[QUERY TIME] $label: {$duration} segundos" . "<br>";
            return $duration;
        }
        $total_duration = 0;
    
        if (!$outlet_id) $outlet_id = $this->session->userdata('outlet_id');
        if (!$company_id) $company_id = $this->session->userdata('company_id');
        if (!$user_id) $user_id = $this->session->userdata('user_id');
        if (!$is_waiter) $is_waiter = 'No';
    
        if ($is_waiter == 'Yes') {
            $start = microtime(true);
            $getCompanyInfo = getCompanyInfoById($company_id);
            $total_duration += log_query_time("getCompanyInfoById", $start);
    
            $start = microtime(true);
            $outlet_info = $this->Common_model->getDataById($outlet_id, "tbl_outlets");
            $total_duration += log_query_time("getDataById tbl_outlets", $start);
    
            $start = microtime(true);
            $user = $this->Common_model->getDataById($user_id, "tbl_users");
            $total_duration += log_query_time("getDataById tbl_users", $start);
        }
    
        // Simula POS():
        $start = microtime(true);
        $data['customers'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, 'tbl_customers');
        $total_duration += log_query_time("getAllByCompanyIdForDropdown tbl_customers", $start);
    
        $start = microtime(true);
        $data['food_menus'] = $this->Sale_model->getAllFoodMenus();
        $total_duration += log_query_time("getAllFoodMenus", $start);
    
        if (!empty($data['food_menus'])) {
            foreach ($data['food_menus'] as $key => $value) {
                $start = microtime(true);
                $variations = $this->Common_model->getAllByCustomId($value->id, "parent_id", "tbl_food_menus");
                $total_duration += log_query_time("getAllByCustomId tbl_food_menus (ID {$value->id})", $start);
    
                $start = microtime(true);
                $kitchen = getKitchenNameAndId($value->category_id);
                $total_duration += log_query_time("getKitchenNameAndId ({$value->category_id})", $start);
            }
        }
    
        $start = microtime(true);
        $data['denominations'] = $this->Common_model->getDenomination($company_id);
        $total_duration += log_query_time("getDenomination", $start);
    
        $start = microtime(true);
        $data['menu_categories'] = $this->Common_model->getSortingForPOS();
        $total_duration += log_query_time("getSortingForPOS", $start);
    
        $start = microtime(true);
        $data['menu_modifiers'] = $this->Sale_model->getAllMenuModifiers();
        $total_duration += log_query_time("getAllMenuModifiers", $start);
    
        $start = microtime(true);
        $data['waiters'] = $this->Sale_model->getWaitersForThisCompany($company_id, 'tbl_users');
        $total_duration += log_query_time("getWaitersForThisCompany", $start);
    
        $start = microtime(true);
        $data['MultipleCurrencies'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_multiple_currencies");
        $total_duration += log_query_time("getAllByCompanyId tbl_multiple_currencies", $start);
    
        $start = microtime(true);
        $data['users'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_users");
        $total_duration += log_query_time("getAllByCompanyId tbl_users", $start);
    
        $start = microtime(true);
        $data['outlet_information'] = $this->Common_model->getDataById($outlet_id, "tbl_outlets");
        $total_duration += log_query_time("getDataById tbl_outlets", $start);
    
        $start = microtime(true);
        $data['payment_methods'] = $this->Sale_model->getAllPaymentMethods();
        $total_duration += log_query_time("getAllPaymentMethods", $start);
    
        $start = microtime(true);
        $data['payment_method_finalize'] = $this->Sale_model->getAllPaymentMethodsFinalize();
        $total_duration += log_query_time("getAllPaymentMethodsFinalize", $start);
    
        $start = microtime(true);
        $data['deliveryPartners'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_delivery_partners");
        $total_duration += log_query_time("getAllByCompanyId tbl_delivery_partners", $start);
    
        $start = microtime(true);
        $data['areas'] = $this->Common_model->getAllByOutletId($outlet_id, 'tbl_areas');
        $total_duration += log_query_time("getAllByOutletId tbl_areas", $start);
    
        $start = microtime(true);
        $data['only_modifiers'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, 'tbl_modifiers');
        $total_duration += log_query_time("getAllByCompanyIdForDropdown tbl_modifiers", $start);
    
        $start = microtime(true);
        $data['kitchens'] = $this->Common_model->getAllByOutletId($outlet_id, "tbl_kitchens");
        $total_duration += log_query_time("getAllByOutletId tbl_kitchens", $start);
    
        $start = microtime(true);
        $data['sale_details'] = $this->Common_model->getDataById(null, "tbl_sales"); // Si no hay $sale_id
        $total_duration += log_query_time("getDataById tbl_sales (null)", $start);
    
        $start = microtime(true);
        $this->db->where("outlet_id", $outlet_id);
        $this->db->where("del_status", "Live");
        $data['numbers'] = $this->db->get("tbl_numeros")->result();
        $total_duration += log_query_time("get tbl_numeros (where outlet_id & del_status)", $start);
    
        echo "Debug completado. Total duración: {$total_duration} segundos";
    }

    
    public function getKitchenStatus()
        {
            $table_id = $this->input->post('table_id');
            $this->db->select("tbl_kitchen_sales.is_merge,tbl_kitchen_sales.self_order_content,tbl_kitchen_sales.id as sale_id,tbl_kitchen_sales.sale_no,tbl_kitchen_sales.waiter_id,tbl_kitchen_sales.total_payable,tbl_kitchen_sales_details.menu_name, tbl_kitchen_sales_details.qty, tbl_kitchen_sales_details.cooking_status,tbl_kitchen_sales_details.cooking_start_time,tbl_kitchen_sales_details.cooking_done_time");
            $this->db->from('tbl_kitchen_sales_details');
            $this->db->join('tbl_kitchen_sales', 'tbl_kitchen_sales.id = tbl_kitchen_sales_details.sales_id', 'left');
            $this->db->where("tbl_kitchen_sales.table_id", $table_id);
            $this->db->where("tbl_kitchen_sales_details.del_status", "Live");
            $data =  $this->db->get()->result();
          
            $return_josn = array();
            $str = '';
            $sale_no = '';
            $total_payable = 0;
            $sale_id = 0;
            $waiter_id = 0;
            $is_merge = 0;
            $self_order_content = '';
            if(isset($data) && $data){
                $str .= "<br>".lang('kitchen_status').":<hr>";
                $i  =1;
                foreach($data as $value){
                    $times = '';
                    $self_order_content = $value->self_order_content;
                    $is_merge = $value->is_merge;
                    $sale_no = $value->sale_no;
                    $total_payable = $value->total_payable;
                    $sale_id = $value->sale_id;
                    $waiter_id = $value->waiter_id;
                    if($value->cooking_status=="Done"){
                        $times = timeElapsed($value->cooking_done_time);
                    }
        
                    if($value->cooking_status=="Started Cooking"){
                        $times = timeElapsed($value->cooking_start_time);
                    }
                    $str.= "#".$i." ".$value->menu_name."-".$value->qty." Qty, Status: (".$value->cooking_status." ".$times.")<br>";
                    $i++;
                }
            }
        
             $return_josn['html_content'] = $str;
             $return_josn['self_order_content'] = $self_order_content;
             $return_josn['sale_id'] = $sale_id;
             $return_josn['order_number'] = $sale_no;
             $return_josn['is_merge'] = $is_merge;
             $return_josn['waiter_name'] = userName($waiter_id);
             $return_josn['total_payable'] = getAmtCustom($total_payable);
             $return_josn['html_content'] = $str;
             echo (json_encode($return_josn));
        }

     /**
     * get Tables Details
     * @access public
     * @return object
     * @param string
     */
    public function getTablesDetails($tables){
        foreach($tables as $table){
            $table->orders_table = $this->Sale_model->getOrdersOfTableByTableId($table->id);
            foreach($table->orders_table as $order_table){

                $to_time = strtotime(date('Y-m-d H:i:s'));
                $from_time = strtotime($order_table->booking_time);
                $minutes = floor(abs($to_time - $from_time) / 60);
                $seconds = abs($to_time - $from_time) % 60;

                $order_table->booked_in_minute = $minutes;
            }
        }
        return $tables;
    }
     /**
     * Save sales data
     * @access public
     * @return void
     * @param no
     */
    public function Save() {
        $data = array();
        $data['customer_id'] = $this->input->get('customer_id');
        $data['total_items'] = $this->input->get('total_items');
        $data['sub_total'] = $this->input->get('sub_total');
        $data['disc'] = $this->input->get('disc');
        $data['disc_actual'] = $this->input->get('disc_actual');
        $data['vat'] = $this->input->get('vat');
        $data['paid_amount'] = $this->input->get('paid_amount');
        $data['due_amount'] = $this->input->get('due_amount');
        $data['table_id'] = $this->input->get('table_id');
        $data['token_no'] = $this->input->get('token_no');
        if ($this->input->get('due_payment_date')) {
            $data['due_payment_date'] = $this->input->get('due_payment_date');
        } else {
            $data['due_payment_date'] = Null;
        }

        $data['total_payable'] = $this->input->get('total_payable');
        $data['payment_method_id'] = $this->input->get('payment_method_id');
        $data['user_id'] = $this->session->userdata('user_id');
        $data['outlet_id'] = $this->session->userdata('outlet_id');
        $data['sale_date'] = $this->input->get('sale_date');
        $data['sale_time'] = date('h:i A');
        $outlet_id = $this->session->userdata('outlet_id');
        $sale_no = $this->db->query("SELECT count(id) as bno
               FROM tbl_sales WHERE outlet_id=$outlet_id")->row('bno');
        $sale_no = str_pad($sale_no + 1, 6, '0', STR_PAD_LEFT);
        $data['sale_no'] = $sale_no;
        ////////////
        $food_menu_id = $this->input->get('food_menu_id');
        $menu_name = $this->input->get('menu_name');
        $price = $this->input->get('price');
        $qty = $this->input->get('qty');
        $discount_amount = $this->input->get('discountNHiddenTotal');
        $total = $this->input->get('total');
        /////////////////////
        $i = 0;
        $this->db->trans_begin();
        $query = $this->db->insert('tbl_sales', $data);
        $sales_id = $this->db->insert_id();

        $comsump = array();
        $comsump['outlet_id'] = $this->session->userdata('outlet_id');
        $comsump['date'] = date('Y-m-d');
        $comsump['date_time'] = date('h:i A');
        $comsump['user_id'] = $this->session->userdata('user_id');
        $comsump['sale_id'] = $sales_id;
        $query = $this->db->insert('tbl_sale_consumptions', $comsump);
        $sale_consumption_id = $this->db->insert_id();

        //////////////////////////////////
        foreach ($food_menu_id as $value) {
            $data1['food_menu_id'] = $value;
            $data1['sales_id'] = $sales_id;
            $data1['menu_name'] = $menu_name[$i];
            $data1['price'] = $price[$i];
            $data1['qty'] = $qty[$i];
            $data1['discount_amount'] = $discount_amount[$i];
            $data1['total'] = $total[$i];
            $data1['user_id'] = $this->session->userdata('user_id');
            $data1['outlet_id'] = $this->session->userdata('outlet_id');
            $data1['cooking_status'] = 'New';
            $this->db->insert('tbl_sales_details', $data1);
            //////////////////////

            $ingredlist = $this->Sale_model->getFoodMenuIngredients($value);
            foreach ($ingredlist as $inrow) {
                $data3 = array();
                $data3['sale_consumption_id'] = $sale_consumption_id;
                $data3['ingredient_id'] = $inrow->ingredient_id;
                $data3['consumption'] = $inrow->consumption * $qty[$i];
                $data3['user_id'] = $this->session->userdata('user_id');
                $data3['outlet_id'] = $this->session->userdata('outlet_id');
                $this->db->insert('tbl_sale_consumptions_of_menus', $data3);
            }
            //////////////////////
            $i++;
        }
        $returndata = array('sales_id' => $sales_id);
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
        } else {
            echo json_encode($returndata);
            $this->db->trans_commit();
        }
    }
     /**
     * delete Suspend
     * @access public
     * @return object
     * @param no
     */
    public function deleteSuspend() {
        $suspendID = $this->input->get('minusSuspendID');
        $this->session->unset_userdata('customer_id_' . $suspendID);
        $this->session->unset_userdata('total_item_hidden_' . $suspendID);
        $this->session->unset_userdata('sub_total_' . $suspendID);
        $this->session->unset_userdata('disc_' . $suspendID);
        $this->session->unset_userdata('disc_actual_' . $suspendID);
        $this->session->unset_userdata('vat_' . $suspendID);
        $this->session->unset_userdata('gTotalDisc_' . $suspendID);
        $this->session->unset_userdata('total_payable_' . $suspendID);
        $this->session->unset_userdata('tables_' . $suspendID);
        $this->session->unset_userdata('countSuspend_' . $suspendID);
        $this->session->unset_userdata('countTimeSuspend_' . $suspendID);
        $this->session->unset_userdata('countSuspendCurrent');
        echo json_encode("success");
    }
     /**
     * get Suspend
     * @access public
     * @return object
     * @param no
     */
    public function getSuspend() {
        $suspendID = $this->input->get('suspendID');
        $checkSuspend = $this->session->userdata('countSuspend_' . $suspendID);
        if ($checkSuspend) {
            $data['status'] = true;
            $data['sus_id'] = $suspendID;
            $data['customer_id'] = $this->session->userdata('customer_id_' . $suspendID);
            $data['total_item_hidden'] = $this->session->userdata('total_item_hidden_' . $suspendID);
            $data['sub_total'] = $this->session->userdata('sub_total_' . $suspendID);
            $data['disc'] = $this->session->userdata('disc_' . $suspendID);
            $data['disc_actual'] = $this->session->userdata('disc_actual_' . $suspendID);
            $data['gTotalDisc'] = $this->session->userdata('gTotalDisc_' . $suspendID);
            $data['vat'] = $this->session->userdata('vat_' . $suspendID);
            $data['total_payable'] = $this->session->userdata('total_payable_' . $suspendID);
            $data['tables'] = $this->session->userdata('tables_' . $suspendID);
        } else {
            $data['status'] = false;
        }
        echo json_encode($data);
    }
     /**
     * get Suspend Current
     * @access public
     * @return object
     * @param no
     */
    public function getSuspendCurrent() {

        $checkSuspend = $this->session->userdata('countSuspendCurrent');
        $suspendID = "current";

        $data['status'] = true;
        $data['customer_id'] = $this->session->userdata('customer_id_' . $suspendID);
        $data['total_item_hidden'] = $this->session->userdata('total_item_hidden_' . $suspendID);
        $data['sub_total'] = $this->session->userdata('sub_total_' . $suspendID);
        $data['disc'] = $this->session->userdata('disc_' . $suspendID);
        $data['disc_actual'] = $this->session->userdata('disc_actual_' . $suspendID);
        $data['vat'] = $this->session->userdata('vat_' . $suspendID);
        $data['gTotalDisc'] = $this->session->userdata('gTotalDisc_' . $suspendID);
        $data['total_payable'] = $this->session->userdata('total_payable_' . $suspendID);
        $data['tables'] = $this->session->userdata('tables_' . $suspendID);
        echo json_encode($data);
    }
     /**
     * set Suspend
     * @access public
     * @return object
     * @param no
     */
    public function setSuspend() {
        $check1 = $this->session->userdata('countSuspend_1');
        $check2 = $this->session->userdata('countSuspend_2');
        $check3 = $this->session->userdata('countSuspend_3');

        $checkTime1 = $this->session->userdata('countTimeSuspend_1');
        $checkTime2 = $this->session->userdata('countTimeSuspend_2');
        $checkTime3 = $this->session->userdata('countTimeSuspend_3');

        $times = date('Y-m-d h:i:s');

        if (!$check1) {
            $temp = 1;
            $this->session->set_userdata('countSuspend_1', 1);
            $this->session->set_userdata('countTimeSuspend_1', $times);
        } elseif (!$check2) {
            $temp = 2;
            $this->session->set_userdata('countSuspend_2', 2);
            $this->session->set_userdata('countTimeSuspend_2', $times);
        } elseif (!$check3) {
            $this->session->set_userdata('countSuspend_3', 3);
            $this->session->set_userdata('countTimeSuspend_3', $times);
            $temp = 3;
        } else {

            if ($checkTime1 < $checkTime2) {
                if ($checkTime1 < $checkTime3) {
                    $temp = 1;
                    $this->session->unset_userdata('countSuspend_' . $temp);
                    $this->session->set_userdata('countSuspend_1', 1);
                    $this->session->unset_userdata('countTimeSuspend_' . $temp);
                    $this->session->set_userdata('countTimeSuspend_1', $times);
                } else {
                    $temp = 3;
                    $this->session->unset_userdata('countSuspend_' . $temp);
                    $this->session->set_userdata('countSuspend_3', 3);
                    $this->session->unset_userdata('countTimeSuspend_' . $temp);
                    $this->session->set_userdata('countTimeSuspend_3', $times);
                }
            } else {
                if ($checkTime2 < $checkTime3) {
                    $temp = 2;
                    $this->session->unset_userdata('countSuspend_' . $temp);
                    $this->session->set_userdata('countSuspend_2', 2);
                    $this->session->unset_userdata('countTimeSuspend_' . $temp);
                    $this->session->set_userdata('countTimeSuspend_2', $times);
                } else {
                    $temp = 3;
                    $this->session->unset_userdata('countSuspend_' . $temp);
                    $this->session->set_userdata('countSuspend_3', 3);
                    $this->session->unset_userdata('countTimeSuspend_' . $temp);
                    $this->session->set_userdata('countTimeSuspend_3', $times);
                }
            }
        }

        //set session value
        $i = 0;
        $food_menu_id = $this->input->get('food_menu_id');
        $menu_name = $this->input->get('menu_name');
        $price = $this->input->get('price');
        $qty = $this->input->get('qty');
        $VATHidden = $this->input->get('VATHidden');
        $VATHiddenTotal = $this->input->get('VATHiddenTotal');
        $discountN = $this->input->get('discountN');
        $discountNHidden = $this->input->get('discountNHidden');
        $discountNHiddenTotal = $this->input->get('discountNHiddenTotal');
        $total = $this->input->get('total');
        $tableRow = "";
        foreach ($food_menu_id as $value) {
            $trID = "row_" . $i;
            $inputID = "food_menu_id_" . $i;
            $tableRow .= "<tr data-id='$i' class='clRow' id='row_$i'><input id='food_menu_id_$i' name='food_menu_id[]' value='$value' type='hidden'><input id='$inputID' name='menu_name[]' value='$menu_name[$i]' type='hidden'><input id='discountNHidden_$i' name='discountNHidden[]' value='$discountNHidden[$i]' type='hidden'><input id='discountNHiddenTotal_$i' name='discountNHiddenTotal[]' value='$discountNHiddenTotal[$i]' type='hidden'><input id='VATHidden_$i' name='VATHidden[]' value='$VATHidden[$i]' type='hidden'><input id='VATHiddenTotal_$i' name='VATHiddenTotal[]' value='$VATHiddenTotal[$i]' type='hidden'><td>$menu_name[$i]</td><td><input class='pri-size txtboxToFilter' onfocus='this.select();' id='price_$i' name='price[]' value='$price[$i]' onblur='return calculateRow($i);' onkeyup='return calculateRow($i)' type='text'></td><td><input class='qty-size txtboxToFilter' onfocus='this.select();' min='1' id='qty_$i' name='qty[]' value='$qty[$i]' onmouseup='return helloThere($i)' onblur='return calculateRow($i);' onkeyup='return checkQuantity($i);' onkeydown='return calculateRow($i);' type='number'></td><td><input class='qty-size discount' onfocus='this.select();'  id='discountN_$i' name='discountN[]' value='$discountN[$i]' onmouseup='return helloThere($i)' onblur='return calculateRow($i);' onkeyup='return checkQuantity($i);' onkeydown='return calculateRow($i);' type='text'></td><td><input class='pri-size' readonly='' id='total_$i' name='total[]' style='background-color: #dddddd;border:1px solid #7e7f7f;' value='$total[$i]' type='text'></td><td style='text-align: center'><a class='btn btn-danger btn-xs' onclick='return deleter($i,$value);'><i style='color:white' class='fa fa-trash'></i></a></td></tr>";
            $i++;
        }
        $customer_id = $this->input->get('customer_id');
        $total_item_hidden = $this->input->get('total_items');
        $sub_total = $this->input->get('sub_total');
        $disc = $this->input->get('disc');
        $disc_actual = $this->input->get('disc_actual');
        $vat = $this->input->get('vat');
        $gTotalDisc = $this->input->get('gTotalDisc');
        $total_payable = $this->input->get('total_payable');
        $tables = $tableRow;
        $this->session->set_userdata('customer_id_' . $temp, $customer_id);
        $this->session->set_userdata('total_item_hidden_' . $temp, $total_item_hidden);
        $this->session->set_userdata('sub_total_' . $temp, $sub_total);
        $this->session->set_userdata('disc_' . $temp, $disc);
        $this->session->set_userdata('disc_actual_' . $temp, $disc_actual);
        $this->session->set_userdata('vat_' . $temp, $vat);
        $this->session->set_userdata('gTotalDisc_' . $temp, $gTotalDisc);
        $this->session->set_userdata('total_payable_' . $temp, $total_payable);
        $this->session->set_userdata('tables_' . $temp, $tables);
        $data['suspend_id'] = $temp;
        echo json_encode($data);
    }
     /**
     * set Suspend Current
     * @access public
     * @return object
     * @param no
     */
    public function setSuspendCurrent() {

        $currentStatus = $this->input->get('currentStatus');
        if ($currentStatus == "1") {
            $temp = "current";
            $this->session->set_userdata('countSuspendCurrent', 1);
            //set session value
            $i = 0;
            $ingredient_id = $this->input->get('ingredient_id');
            $menu_name = $this->input->get('menu_name');
            $price = $this->input->get('price');
            $qty = $this->input->get('qty');
            $VATHidden = $this->input->get('VATHidden');
            $VATHiddenTotal = $this->input->get('VATHiddenTotal');
            $discountN = $this->input->get('discountN');
            $discountNHidden = $this->input->get('discountNHidden');
            $discountNHiddenTotal = $this->input->get('discountNHiddenTotal');
            $total = $this->input->get('total');
            $tableRow = "";
            foreach ($ingredient_id as $value) {
                $trID = "row_" . $i;
                $inputID = "ingredient_id_" . $i;
                $tableRow .= "<tr data-id='$i' class='clRow' id='row_$i'><input id='ingredient_id_$i' name='ingredient_id[]' value='$value' type='hidden'><input id='$inputID' name='menu_name[]' value='$menu_name[$i]' type='hidden'><input id='discountNHidden_$i' name='discountNHidden[]' value='$discountNHidden[$i]' type='hidden'><input id='discountNHiddenTotal_$i' name='discountNHiddenTotal[]' value='$discountNHiddenTotal[$i]' type='hidden'><input id='VATHidden_$i' name='VATHidden[]' value='$VATHidden[$i]' type='hidden'><input id='VATHiddenTotal_$i' name='VATHiddenTotal[]' value='$VATHiddenTotal[$i]' type='hidden'><td>$menu_name[$i]</td><td><input class='pri-size txtboxToFilter' onfocus='this.select();' id='price_$i' name='price[]' value='$price[$i]' onblur='return calculateRow($i);' onkeyup='return calculateRow($i)' type='text'></td><td><input class='qty-size txtboxToFilter' onfocus='this.select();' min='1' id='qty_$i' name='qty[]' value='$qty[$i]' onmouseup='return helloThere($i)' onblur='return calculateRow($i);' onkeyup='return checkQuantity($i);' onkeydown='return calculateRow($i);' type='number'></td><td><input class='qty-size discount' onfocus='this.select();'  id='discountN_$i' name='discountN[]' value='$discountN[$i]' onmouseup='return helloThere($i)' onblur='return calculateRow($i);' onkeyup='return checkQuantity($i);' onkeydown='return calculateRow($i);' type='text'></td><td><input class='pri-size' readonly='' id='total_$i' name='total[]' style='background-color: #dddddd;border:1px solid #7e7f7f;' value='$total[$i]' type='text'></td><td style='text-align: center'><a class='btn btn-danger btn-xs' onclick='return deleter($i,$value);'><i style='color:white' class='fa fa-trash'></i></a></td></tr>";
                $i++;
            }
            $customer_id = $this->input->get('customer_id');
            $total_item_hidden = $this->input->get('total_items');
            $sub_total = $this->input->get('sub_total');
            $disc = $this->input->get('disc');
            $disc_actual = $this->input->get('disc_actual');
            $vat = $this->input->get('vat');
            $total_payable = $this->input->get('total_payable');
            $tables = $tableRow;

            $this->session->set_userdata('customer_id_' . $temp, $customer_id);
            $this->session->set_userdata('total_item_hidden_' . $temp, $total_item_hidden);
            $this->session->set_userdata('sub_total_' . $temp, $sub_total);
            $this->session->set_userdata('disc_' . $temp, $disc);
            $this->session->set_userdata('disc_actual_' . $temp, $disc_actual);
            $this->session->set_userdata('vat_' . $temp, $vat);
            $this->session->set_userdata('total_payable_' . $temp, $total_payable);
            $this->session->set_userdata('tables_' . $temp, $tables);
            $data['suspend_id'] = $temp;

            echo json_encode($data);
        }
    }
     /**
     * set Service Session
     * @access public
     * @return object
     * @param no
     */
    public function setServiceSession() {
        $serviceValue = $this->input->get('serviceValue');
        $this->session->set_userdata('serviceSession', $serviceValue);
    }
     /**
     * get Service Session
     * @access public
     * @return object
     * @param no
     */
    public function getServiceSession() {
        $serviceValue = $this->session->userdata['serviceSession'];
        $data['serviceData'] = $serviceValue;
        echo json_encode($data);
    }
     /**
     * view invoice
     * @access public
     * @return void
     * @param int
     */
    public function view($sales_id=3) {
        $sales_id = $this->custom->encrypt_decrypt($sales_id, 'decrypt');
        $data = array();
        $data['info'] = $this->Sale_model->getSaleInfo($sales_id);
        $data['details'] = $this->Sale_model->getSaleDetails($sales_id);
        $this->load->view('sale/print', $data);
    }
     /**
     * view A4 size invoice
     * @access public
     * @return void
     * @param int
     */
    public function view_A4($sales_id) {
        $sales_id = $this->custom->encrypt_decrypt($sales_id, 'decrypt');
        $data = array();
        $data['info'] = $this->Sale_model->getSaleInfo($sales_id);
        $data['details'] = $this->Sale_model->getSaleDetails($sales_id);
        $this->load->view('sale/print_A4', $data);
    }
     /**
     * view invoice
     * @access public
     * @return void
     * @param int
     */
    public function view_invoice($sales_id) {
        $sales_id = $this->custom->encrypt_decrypt($sales_id, 'decrypt');
        $outlet_id = $this->session->userdata('outlet_id');
        $data = array();
        $data['info'] = $this->Sale_model->getSaleInfo($sales_id);
        $data['details'] = $this->Sale_model->getSaleDetails($sales_id);
        $print_format = $this->session->userdata('print_format');
        if($print_format=="80mm"){
            $this->load->view('sale/print_invoice', $data);
        }else{
            $this->load->view('sale/print_invoice_56mm', $data);
        }
    }
     /**
     * save Sales Items
     * @access public
     * @return void
     * @param string
     * @param int
     * @param string
     */
    public function saveSalesItems($item_menu_items, $ingredient_id, $table_name) {
        /*This all variables could not be escaped because this is an array field*/
        foreach ($item_menu_items as $row => $ingredient_id):
            $fmi = array();
            $fmi['ingredient_id'] = $ingredient_id;
            $fmi['consumption'] = $_POST['consumption'][$row];
            $fmi['ingredient_id'] = $ingredient_id;
            $fmi['user_id'] = $this->session->userdata('user_id');
            $fmi['outlet_id'] = $this->session->userdata('outlet_id');
            $this->Common_model->insertInformation($fmi, "tbl_sales_items");
        endforeach;
    }
     /**
     * item Menu Details
     * @access public
     * @return void
     * @param int
     */
    public function itemMenuDetails($id) {
        $encrypted_id = $id;
        $id = $this->custom->encrypt_decrypt($id, 'decrypt');

        $data = array();
        $data['encrypted_id'] = $encrypted_id;
        $data['item_menu_details'] = $this->Common_model->getDataById($id, "tbl_sales");
        $data['main_content'] = $this->load->view('sale/itemMenuDetails', $data, TRUE);
        $this->load->view('userHome', $data);
    }
     /**
     * add New Customer By Ajax
     * @access public
     * @return object
     * @param no
     */
    public function addNewCustomerByAjax() {
        $data['name'] = $_GET['customer_name'];
        $data['phone'] = $_GET['mobile_no'];
        $data['email'] = $_GET['customerEmail'];
        $data['date_of_birth'] = $_GET['customerDateOfBirth'];
        $data['date_of_anniversary'] = $_GET['customerDateOfAnniversary'];
        $data['address'] = $_GET['customerAddress'];
        $data['user_id'] = $this->session->userdata('user_id');
        $data['company_id'] = $this->session->userdata('company_id');
        $this->db->insert('tbl_customers', $data);
        $customer_id = $this->db->insert_id();
        $data1 = array('customer_id' => $customer_id);
        echo json_encode($data1);
    }
     /**
     * getEncriptValue
     * @access public
     * @return object
     * @param no
     */
    public function getEncriptValue() {
        $id = $this->custom->encrypt_decrypt($_GET['sales_id'], 'encrypt');
        $data['encriptID'] = $id;
        echo json_encode($data);
    }
     /**
     * get Customer List
     * @access public
     * @return object
     * @param no
     */
    public function getCustomerList() {
        $company_id = $this->session->userdata('company_id');
        $data1 = $this->db->query("SELECT * FROM tbl_customers 
              WHERE company_id=$company_id")->result();
        //generate html content for view
        foreach ($data1 as $value) {
            if ($value->name == "Walk-in Customer") {
                echo '<option value="' . $value->id . '" >' . $value->name . '</option>';
            }
        }
        //generate html content for view
        foreach ($data1 as $value) {
            if ($value->name != "Walk-in Customer") {
                echo '<option value="' . $value->id . '" >' . $value->name . ' (' . $value->phone . ')' . '</option>';
            }
        }
        exit;
    }
    /**
     * add customer by ajax
     * @access public
     * @return int
     * @param no
     */
    public function add_customer_by_ajax(){
        $customer_id = htmlspecialcharscustom($this->input->post($this->security->xss_clean('customer_id')));
        $data['name'] = trim_checker(htmlspecialcharscustom($this->input->post($this->security->xss_clean('customer_name'))));
        $data['phone'] = trim_checker(htmlspecialcharscustom($this->input->post($this->security->xss_clean('customer_phone'))));
        $data['default_discount'] = trim_checker(htmlspecialcharscustom($this->input->post($this->security->xss_clean('customer_default_discount'))));
        $data['email'] = trim_checker($this->input->post($this->security->xss_clean('customer_email')));
        $data['password_online_user'] = md5(trim_checker($this->input->post($this->security->xss_clean('customer_password'))));
        if($this->input->post($this->security->xss_clean('customer_dob'))){
            $data['date_of_birth'] = date('Y-m-d',strtotime($this->input->post($this->security->xss_clean('customer_dob'))));
        }
        if($this->input->post($this->security->xss_clean('customer_doa'))){
            $data['date_of_anniversary'] = date('Y-m-d',strtotime($this->input->post($this->security->xss_clean('customer_doa'))));
        }
        $data['address'] = trim_checker(preg_replace('/\s+/', ' ',htmlspecialcharscustom($this->input->post($this->security->xss_clean('customer_delivery_address')))));
        $data['gst_number'] = trim_checker($this->input->post($this->security->xss_clean('customer_gst_number')));
        $data['same_or_diff_state'] = trim_checker($this->input->post($this->security->xss_clean('same_or_diff_state')));
        $is_new_address = trim_checker($this->input->post($this->security->xss_clean('is_new_address')));
        $data['user_id'] = $this->session->userdata('user_id');
        $data['company_id'] = $this->session->userdata('company_id');
        if (tipoFacturacion() == 'RD_AI') {
            $data['tipo_ident'] = $this->input->post($this->security->xss_clean('customer_tipo_ident'));
            $data['tipo_numeracion'] = $this->input->post($this->security->xss_clean('customer_tipo_numeracion'));
        }

        // ==== NUEVOS CAMPOS SIFEN SI EXISTEN EN POST ====
        $fields_map = [
            'nombre_fantasia' => 'customer_nombre_fantasia',
            'es_proveedor_estado' => 'customer_es_proveedor_estado',
            'es_contribuyente' => 'customer_es_contribuyente',
            'tipo_documento' => 'customer_tipo_documento',
            'numero_casa' => 'customer_numero_casa',
            'tipo_contribuyente' => 'customer_tipo_contribuyente',
            'codigo_pais' => 'codigo_pais',
            'departamento_id' => 'departamento_id',
            'distrito_id' => 'distrito_id',
            'ciudad_id' => 'ciudad_id',
        ];
        foreach ($fields_map as $db_field => $post_field) {
            $value = $this->input->post($this->security->xss_clean($post_field));
            // Solo guarda si viene en el POST (puede ser '0' en checkboxes)
            if($value !== null && $value !== false){
                $data[$db_field] = trim_checker(htmlspecialcharscustom($value));
            }
        }
        // ==== FIN NUEVOS CAMPOS ====

        $id_return = 0;
        $gst_number = isset($data['gst_number']) ? $data['gst_number'] : '';
        $customer_name = isset($data['name']) ? $data['name'] : ''; // MODIFICACIÓN: Obtener el nombre para la búsqueda
        $existing_customer = null;

        // Prioriza la búsqueda por gst_number si se proporciona
        if ($gst_number != '') {
            $existing_customer = $this->db->select('id')->from('tbl_customers')->where('gst_number', $gst_number)->where('company_id', $data['company_id'])->get()->row();
        } else if ($customer_name != '') { // MODIFICACIÓN: Si gst_number está vacío, busca por nombre
            $existing_customer = $this->db->select('id')->from('tbl_customers')->where('name', $customer_name)->where('company_id', $data['company_id'])->get()->row();
        }

        if ($existing_customer) {
            // Si se encontró un cliente (por gst_number o por nombre), se actualiza
            $this->db->where('id', $existing_customer->id);
            $this->db->update('tbl_customers', $data);
            $id_return = $existing_customer->id;
        } else {
            // Si no se encontró ningún cliente, se crea uno nuevo
            $this->db->insert('tbl_customers', $data);
            $id_return = $this->db->insert_id();
        }
        $customer_delivery_address_modal_id = trim_checker($this->input->post($this->security->xss_clean('customer_delivery_address_modal_id')));
        if($is_new_address=="Yes"){
            $customer_address = array();
            $customer_address['customer_id'] = $id_return;
            $customer_address['address'] = $data['address'];
            $customer_address['is_active'] = 1;
            if($data['address']){
                $this->Common_model->insertInformation($customer_address, "tbl_customer_address");
            }
        }else if ($customer_delivery_address_modal_id){
            $data_old['is_active'] = '0';
            $this->db->where('customer_id', $id_return);
            $this->db->update('tbl_customer_address', $data_old);

            $customer_address = array();
            $customer_address['customer_id'] = $id_return;
            $customer_address['address'] = $data['address'];
            $customer_address['is_active'] = 1;
            if($data['address']){
                $this->Common_model->updateInformation($customer_address, $customer_delivery_address_modal_id, "tbl_customer_address");
            }
        }
        $is_online_order =  $this->session->userdata('is_online_order');

        $customer_return['customer_id'] = $id_return;
        $customer_return['online_customer_id'] = '';
        if($is_online_order=="Yes"){
            $customer_return['online_customer_id'] = $id_return;
            $this->session->set_userdata('online_customer_id',$id_return);
            $this->session->set_userdata('online_customer_name',$data['name']);
            $this->session->set_userdata('short_name', strtolower(substr($data['name'],0, 1)));
        }
        $customer_data = $this->db->get_where('tbl_customers', ['id' => $id_return])->row_array();
        $customer_return['customer_data'] = $customer_data;
        echo json_encode($customer_return) ;
    }

    public function search_customers() {
        $term = $this->input->get('q');
        $this->db->like('name', $term);
        // $this->db->or_like('phone', $term);
        $this->db->limit(10); // Solo 10 por búsqueda
        $this->db->where('del_status', 'Live');
        $customers = $this->db->get('tbl_customers')->result();

        $results = [];
        foreach ($customers as $c) {
            $results[] = [
                'id' => $c->id,
                'text' => $c->name . ' ' . $c->phone,
                'default_discount' => $c->default_discount,
                'address' => $c->address,
                'gst_number' => $c->gst_number,
                'same_or_diff_state' => $c->same_or_diff_state,
                'email' => $c->email,
                'date_of_birth' => $c->date_of_birth ? date('Y-m-d', strtotime($c->date_of_birth)) : '',
                'date_of_anniversary' => $c->date_of_anniversary ? date('Y-m-d', strtotime($c->date_of_anniversary)) : '',
                'customer_id' => $c->id,
                'phone' => $c->phone,
                'name' => $c->name,
                'current_due' => getCustomerDue($c->id),

                // ...otros campos útiles...
            ];
        }

        echo json_encode($results);
    }

    public function search_customers_by_gts() {
        // Asegurarse de que sea una petición AJAX
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }

        $term = $this->input->post('term');
        $company_id = $this->session->userdata('company_id');

        if (!$term) {
            echo json_encode([]);
            return;
        }

        $this->db->select('id, name, gst_number');
        $this->db->from('tbl_customers');
        $this->db->where('company_id', $company_id);
        $this->db->group_start();
        $this->db->like('gst_number', $term, 'after'); // 'after' busca coincidencias al inicio (ej: 432... )
        $this->db->group_end();
        $this->db->where('del_status', 'Live');
        $this->db->limit(10); // Limitar a 10 resultados para no sobrecargar

        $query = $this->db->get();
        $customers = $query->result();

        // Devolver solo los datos necesarios para la lista
        echo json_encode($customers);
    }

    public function online_customer_login_by_ajax(){
        $online_login_phone = trim_checker(htmlspecialcharscustom($this->input->post($this->security->xss_clean('online_login_phone'))));
        $online_login_password = trim_checker(htmlspecialcharscustom($this->input->post($this->security->xss_clean('online_login_password'))));

        $get_customer_details = get_customer_details($online_login_phone,$online_login_password);
        $customer_return['status'] = false;
        $customer_return['customer_id'] = '';
        $customer_return['online_customer_id'] = '';
        if($get_customer_details){
            $customer_return['status'] = true;
            $customer_return['customer_id'] = $get_customer_details->id;
            $customer_return['online_customer_id'] = $get_customer_details->id;
            $this->session->set_userdata('online_customer_id', $get_customer_details->id);
            $this->session->set_userdata('online_customer_name', $get_customer_details->name);
            $this->session->set_userdata('short_name', strtolower(substr($get_customer_details->name,0, 1)));
        }
        echo json_encode($customer_return) ;
    }
     /**
     * get all customers for this user
     * @access public
     * @return object
     * @param no
     */
    public function get_all_customers_for_this_user(){
        $company_id = $this->session->userdata('company_id');
        $data1 = $this->db->query("SELECT * FROM tbl_customers 
              WHERE company_id=$company_id AND del_status='Live'")->result();
        echo json_encode($data1);
    }
    // Agrega este método al controlador
    public function get_customer_by_id($id) {
        $company_id = $this->session->userdata('company_id');
        $customer = $this->db->get_where('tbl_customers', [
            'id' => $id,
            'company_id' => $company_id,
            'del_status' => 'Live'
        ])->row();
        echo json_encode($customer);
    }
     /**
     * add sale by ajax
     * @access public
     * @return int
     * @param no
     */
    public function add_kitchen_sale_by_ajax(){
        //check creating invoice
        $status_creating_invocie = true;
        if(isServiceAccessOnly('sGmsJaFJE')){
            if(!checkCreatePermissionInvoice()){
                $status_creating_invocie = false;
            }
        }
        if($status_creating_invocie==false){
            $return_data['invoice_status'] = '1';
            $return_data['invoice_msg'] = lang('not_permission_invoice_create_error');
            echo json_encode($return_data);
            return;
        };
        $order = $this->input->post('order');
        $order_details = (json_decode($order));
        if(!empty($order_details)){
            $sale_no = $order_details->sale_no;
            $sale_d = getKitchenSaleDetailsBySaleNo($sale_no);
    
            $data = array();
            $data['customer_id'] = trim_checker($order_details->customer_id);
            $data['counter_id'] = trim_checker($order_details->counter_id);
    
            $is_self_order = $this->session->userdata('is_self_order');
            $is_online_order = $this->session->userdata('is_online_order');
    
            $self_order_table_person = htmlspecialcharscustom($order_details->self_order_table_person);
            $self_order_table_id = htmlspecialcharscustom($order_details->self_order_table_id);
    
            $designation = $this->session->userdata('designation');
            if($designation!="Admin" && $designation!="Super Admin"){
                $data['order_receiving_id'] = getOrderReceivingId($this->session->userdata('user_id'));
                $data['order_receiving_id_admin'] = getOrderReceivingIdAdmin();
            } 
            $data['self_order_content'] = $order;
            $data['del_address'] = trim_checker($order_details->customer_address)!="undefined"?trim_checker($order_details->customer_address):"";
            $data['delivery_partner_id'] = trim_checker($order_details->delivery_partner_id);
            $data['rounding_amount_hidden'] = trim_checker($order_details->rounding_amount_hidden);
            $data['previous_due_tmp'] = trim_checker($order_details->previous_due_tmp);
            $data['total_items'] = trim_checker($order_details->total_items_in_cart);
            $data['sub_total'] = trim_checker($order_details->sub_total);
            $data['charge_type'] = trim_checker($order_details->charge_type);
            $data['vat'] = trim_checker($order_details->total_vat);
            $data['total_payable'] = trim_checker($order_details->total_payable);
            $data['total_item_discount_amount'] = trim_checker($order_details->total_item_discount_amount);
            $data['sub_total_with_discount'] = trim_checker($order_details->sub_total_with_discount);
            $data['sub_total_discount_amount'] = trim_checker($order_details->sub_total_discount_amount);
            $data['total_discount_amount'] = trim_checker($order_details->total_discount_amount);
            $data['delivery_charge'] = trim_checker($order_details->delivery_charge);
            $data['delivery_charge_actual_charge'] = trim_checker($order_details->delivery_charge_actual_charge);
            $data['tips_amount'] = trim_checker($order_details->tips_amount);
            $data['tips_amount_actual_charge'] = trim_checker($order_details->tips_amount_actual_charge);
            $data['sub_total_discount_value'] = trim_checker($order_details->sub_total_discount_value);
            $data['sub_total_discount_type'] = trim_checker($order_details->sub_total_discount_type);
            $data['orders_table_text'] = trim_checker($order_details->orders_table_text);
            $data['waiter_id'] = trim_checker($order_details->waiter_id);
            $data['outlet_id'] = $this->session->userdata('outlet_id');
            $data['company_id'] = $this->session->userdata('company_id');
            $data['sale_date'] = trim_checker(isset($order_details->open_invoice_date_hidden) && $order_details->open_invoice_date_hidden?$order_details->open_invoice_date_hidden:date('Y-m-d'));
            $data['table_id'] = trim_checker($order_details->table_id);
            $data['is_merge'] = trim_checker(@$order_details->is_merge);
            $data['zatca_value'] = trim_checker($order_details->zatca_invoice_value);
            $data['number_slot'] = trim_checker($order_details->selected_number );
            $data['number_slot_name'] = trim_checker($order_details->selected_number_name );
            $data['sale_no'] = $sale_no;
            $today_ = date('Y-m-d');
            $data['is_pickup_sale'] = 1;
            $total_tax = 0;
            if(isset($order_details->sale_vat_objects) && $order_details->sale_vat_objects){
                foreach ($order_details->sale_vat_objects as $keys=>$val){
                    $total_tax+=$val->tax_field_amount;
                }
            }
            $data['vat'] = $total_tax;
            $data['sale_vat_objects'] = json_encode($order_details->sale_vat_objects);
            $data['order_type'] = trim_checker($order_details->order_type);
    
            $this->db->trans_begin();
            $sale_id = isset($sale_d->id) && $sale_d->id?$sale_d->id:'';
            $is_new = 1;
            if($sale_id>0){
                $is_new = 0;
                $data['user_id'] = $sale_d->user_id;
                $data['date_time'] = $sale_d->date_time;
                $data['order_time'] = $sale_d->order_time;
                $data['order_status'] = $sale_d->order_status;
                $data['is_online_order'] = $sale_d->is_online_order;
                $data['is_accept'] = $sale_d->is_accept;
                $data['online_order_receiving_id'] = $sale_d->online_order_receiving_id;
                $data['modified'] = 'Yes';
                $data['is_update_sender'] = 1;
                $data['is_update_receiver'] = 1;
                $data['is_update_receiver_admin'] = 1;
                $data['last_update'] = gmdate('Y-m-d H:i:s');
                $previous_number = $this->db->select('number_slot')
                    ->where('id', $sale_id)->get('tbl_kitchen_sales')->row()->number_slot;
                if($previous_number > 0) {
                    $this->db->where("id", $previous_number)
                    ->update("tbl_numeros", [
                        "sale_id" => NULL,
                        "sale_no" => NULL,
                        "user_id" => NULL
                    ]); 
                }
                if (isset($order_details->selected_number) && $order_details->selected_number > 0) {
                    $this->db->where("id", $order_details->selected_number)
                            ->update("tbl_numeros", [
                                "sale_id" => $sale_id,
                                "sale_no" => $sale_no,
                                "user_id" => $this->session->userdata('user_id')
                            ]);
                }
                $this->db->where('id', $sale_id);
                $this->db->update('tbl_kitchen_sales', $data);
                // checkAndRemoveAllRemovedItem($order_details->items,$sale_id);
                $user_id = $this->session->userdata('user_id');
                reconcile_kitchen_sale_items($order_details->items,$sale_id, $user_id);
            }else{
                $is_new = 1;
                $data['date_time'] = date('Y-m-d H:i:s');
                $data['order_time'] = date("H:i:s");
                $data['order_status'] = trim_checker($order_details->order_status);
                if($is_self_order=="Yes" && $is_online_order!="Yes"){
                    $data['is_self_order'] = "Yes";
                    $data['is_accept'] = 2;
                    $data['self_order_ran_code'] = $this->session->userdata('self_order_ran_code');
                    $data['online_self_order_receiving_id'] = getOnlineSelfOrderReceivingId($this->session->userdata('outlet_id'));
                }
                if($is_online_order=="Yes"){
                    $data['is_online_order'] = "Yes";
                    $data['is_accept'] = 2;
                    $data['online_order_receiving_id'] = getOnlineOrderReceivingId($this->session->userdata('outlet_id'));
                }
                $data['user_id'] = $this->session->userdata('user_id');
                $data['random_code'] = trim_checker(isset($order_details->random_code) && $order_details->random_code?$order_details->random_code:'');
                $this->db->insert('tbl_kitchen_sales', $data);
                $sale_id = $this->db->insert_id();
                if (isset($order_details->selected_number) && $order_details->selected_number > 0) {
                    $this->db->where("id", $order_details->selected_number)
                    ->update("tbl_numeros", [
                        "sale_id" => $sale_id,
                        "sale_no" => $sale_no,
                        "user_id" => $this->session->userdata('user_id')
                    ]);
                }
                if($is_self_order=="Yes" && $is_online_order!="Yes"){
                    $notification = "se ha realizado un nuevo autopedido, el número de pedido es: ".$sale_no;
                    $notification_data = array();
                    $notification_data['notification'] = $notification;
                    $notification_data['sale_id'] = $sale_id;
                    $notification_data['waiter_id'] = trim_checker($order_details->waiter_id);
                    $notification_data['outlet_id'] = $this->session->userdata('outlet_id');
                    $this->db->insert('tbl_notifications', $notification_data);
                }
                if($is_online_order=="Yes"){
                    $notification = "se ha realizado un nuevo pedido en línea, el número de pedido es: ".$sale_no;
                    $notification_data = array();
                    $notification_data['notification'] = $notification;
                    $notification_data['sale_id'] = $sale_id;
                    $notification_data['waiter_id'] = trim_checker($order_details->waiter_id);
                    $notification_data['outlet_id'] = $this->session->userdata('outlet_id');
                    $this->db->insert('tbl_notifications', $notification_data);
                }
            }
            if($is_self_order=="Yes" || $is_online_order=="Yes"){
                $order_table_info = array();
                $order_table_info['persons'] = $self_order_table_person;
                $order_table_info['booking_time'] = date('Y-m-d H:i:s');
                $order_table_info['sale_id'] = $sale_id;
                $order_table_info['sale_no'] = $sale_no;
                $order_table_info['outlet_id'] = $this->session->userdata('outlet_id');
                $order_table_info['table_id'] = $self_order_table_id;
                $this->db->insert('tbl_orders_table',$order_table_info);
    
                $data_update_text['orders_table_text'] = getTableName($self_order_table_id);
                $this->db->where('id', $sale_id);
                $this->db->update('tbl_kitchen_sales', $data_update_text);
            }else{
                foreach($order_details->orders_table as $single_order_table){
                    $order_table_info = array();
                    $order_table_info['persons'] = $single_order_table->persons;
                    $order_table_info['booking_time'] = date('Y-m-d H:i:s');
                    $order_table_info['sale_id'] = $sale_id;
                    $order_table_info['sale_no'] = $sale_no;
                    $order_table_info['outlet_id'] = $this->session->userdata('outlet_id');
                    $order_table_info['table_id'] = $single_order_table->table_id;
                    $this->db->insert('tbl_orders_table',$order_table_info);
                }
            }
            // ESTE ES EL NUEVO BLOQUE QUE DEBES PEGAR
            // ==================================================================
            // === INICIO DE LA MODIFICACIÓN PRINCIPAL: PROCESAMIENTO DE ITEMS ===
            // ==================================================================
            if ($sale_id > 0 && count($order_details->items) > 0) {
                
                // 1. AGRUPAR ITEMS DEL CARRITO POR FIRMA
                $cart_items_grouped_by_signature = [];
                foreach ($order_details->items as $item) {
                    $signature = generate_item_signature($item);
                    if (!isset($cart_items_grouped_by_signature[$signature])) {
                        $cart_items_grouped_by_signature[$signature] = [
                            'item' => $item, // Usamos el primer item como representante
                            'qty' => 0
                        ];
                    }
                    $cart_items_grouped_by_signature[$signature]['qty'] += intval($item->qty);
                }

                // 2. OBTENER ITEMS DE LA BD Y AGRUPARLOS POR FIRMA
                $existing_items_live = getAllOrderItemsWithModifiers($sale_id); // Necesitamos una función que traiga modificadores
                $db_items_grouped_by_signature = [];
                foreach($existing_items_live as $db_item) {
                    $signature = generate_item_signature($db_item, $db_item->modifiers_ids);
                    if (!isset($db_items_grouped_by_signature[$signature])) {
                        $db_items_grouped_by_signature[$signature] = [
                            'db_item' => $db_item, // El registro principal de la BD
                            'qty' => 0
                        ];
                    }
                    $db_items_grouped_by_signature[$signature]['qty'] += intval($db_item->qty);
                }

                // 3. PROCESAR: COMPARAR CARRITO VS BD
                foreach ($cart_items_grouped_by_signature as $signature => $cart_group) {
                    $item = $cart_group['item'];
                    $new_total_qty = $cart_group['qty'];

                    $item_data = [
                        'food_menu_id'                  => sanitize_font_html($item->food_menu_id),
                        'menu_name'                     => sanitize_font_html($item->menu_name),
                        'menu_price_without_discount'   => sanitize_font_html($item->menu_price_without_discount),
                        'menu_price_with_discount'      => sanitize_font_html($item->menu_price_with_discount),
                        'menu_unit_price'               => sanitize_font_html($item->menu_unit_price),
                        'menu_taxes'                    => json_encode($item->item_vat),
                        'menu_discount_value'           => sanitize_font_html($item->menu_discount_value),
                        'discount_type'                 => sanitize_font_html($item->discount_type),
                        'menu_note'                     => sanitize_font_html($item->item_note),
                        'menu_combo_items'              => sanitize_font_html($item->menu_combo_items),
                        'discount_amount'               => sanitize_font_html($item->item_discount_amount),
                        'item_type'                     => "Kitchen Item",
                        'sales_id'                      => $sale_id,
                        'user_id'                       => $this->session->userdata('user_id'),
                        'outlet_id'                     => $this->session->userdata('outlet_id'),
                        'del_status'                    => 'Live',
                    ];

                    if (isset($db_items_grouped_by_signature[$signature])) {
                        // --- EL GRUPO DE ITEMS YA EXISTE: ACTUALIZAR ---
                        $db_group = $db_items_grouped_by_signature[$signature];
                        $db_item = $db_group['db_item'];
                        $old_total_qty = $db_group['qty'];
                        $sales_details_id = $db_item->id;

                        $qty_diff = $new_total_qty - $old_total_qty;
                        $item_data['tmp_qty'] = ($qty_diff > 0) ? $qty_diff : 0;
                        $item_data['qty'] = $new_total_qty;

                        // Conservar estado y tiempos de cocina
                        $item_data['cooking_status'] = ($qty_diff > 0) ? 'New' : $db_item->cooking_status;
                        $item_data['cooking_start_time'] = $db_item->cooking_start_time;
                        $item_data['cooking_done_time'] = $db_item->cooking_done_time;

                        $this->Common_model->updateInformation($item_data, $sales_details_id, "tbl_kitchen_sales_details");
                        // <-- Añadir esto aquí
                        $this->db->where('id', $sales_details_id);
                        $this->db->update('tbl_kitchen_sales_details', ['previous_id' => $sales_details_id]);
                                            
                        // Marcar este grupo de la BD como ya procesado
                        unset($db_items_grouped_by_signature[$signature]);

                    } else {
                        // --- GRUPO DE ITEMS NUEVO: INSERTAR ---
                        $item_data['qty'] = $new_total_qty;
                        $item_data['tmp_qty'] = $new_total_qty;
                        $item_data['cooking_status'] = 'New';
                        $item_data['cooking_start_time'] = '0000-00-00 00:00:00';
                        $item_data['cooking_done_time'] = '0000-00-00 00:00:00';

                        $this->db->insert('tbl_kitchen_sales_details', $item_data);
                        $sales_details_id = $this->db->insert_id();
                        $this->db->where('id', $sales_details_id);
                        $this->db->update('tbl_kitchen_sales_details', ['previous_id' => $sales_details_id]);
                    }
                    
                    // --- Procesar Modificadores (Borrar y Re-insertar es lo más seguro) ---
                    $this->db->where('sales_details_id', $sales_details_id)->delete('tbl_kitchen_sales_details_modifiers');
                    $modifier_id_array = ($item->modifiers_id != "") ? explode(",", $item->modifiers_id) : [];
                    if (!empty($modifier_id_array)) {
                        $modifier_price_array = ($item->modifiers_price != "") ? explode(",", $item->modifiers_price) : [];
                        $modifier_vat_array = (isset($item->modifier_vat) && $item->modifier_vat != "") ? explode("|||", $item->modifier_vat) : [];
                        foreach ($modifier_id_array as $i => $single_modifier_id) {
                            $this->db->insert('tbl_kitchen_sales_details_modifiers', [
                                'modifier_id' => $single_modifier_id, 'modifier_price' => $modifier_price_array[$i], 'food_menu_id' => $item->food_menu_id,
                                'sales_id' => $sale_id, 'sales_details_id' => $sales_details_id, 'user_id' => $this->session->userdata('user_id'),
                                'outlet_id' => $this->session->userdata('outlet_id'), 'customer_id' => $order_details->customer_id,
                                'menu_taxes' => isset($modifier_vat_array[$i]) ? $modifier_vat_array[$i] : ''
                            ]);
                        }
                    }
                }

                // 4. PROCESAR ITEMS ELIMINADOS
                // Cualquier grupo que quede en $db_items_grouped_by_signature fue eliminado del carrito.
                foreach ($db_items_grouped_by_signature as $signature => $db_group) {
                    $db_item_to_delete = $db_group['db_item'];
                    $this->db->where('id', $db_item_to_delete->id);
                    $this->db->update('tbl_kitchen_sales_details', [
                        'del_status' => 'Deleted',
                        'user_id' => $this->session->userdata('user_id'),
                        'cooking_status' => 'Cancelled'
                    ]);
                }
            }
            // ================================================================
            // === FIN DE LA MODIFICACIÓN PRINCIPAL: PROCESAMIENTO DE ITEMS ===
            // ================================================================

            // if($sale_id>0 && count($order_details->items)>0){
            //     $previous_food_id = 0;
            //     $arr_item_id = array();
            //     $existing_items = getAllOrderItems($sale_id);
            //     $existing_items_by_id = [];
            //     foreach ($existing_items as $ex_item) {
            //         $fid = $ex_item->food_menu_id;
            //         if (!isset($existing_items_by_id[$fid])) $existing_items_by_id[$fid] = [];
            //         $existing_items_by_id[$fid][] = $ex_item;
            //     }
            //     $counter_by_id = [];
            //     foreach($order_details->items as $key_counter=>$item){
            //         $fid = $item->food_menu_id;
            //         if (!isset($counter_by_id[$fid])) $counter_by_id[$fid] = 0;
            //         $occurrence = $counter_by_id[$fid]++;
            //         $existing_item = isset($existing_items_by_id[$fid][$occurrence]) ? $existing_items_by_id[$fid][$occurrence] : null;
            //         if ($is_new > 0) { // Es una orden nueva
            //             $tmp_var = intval($item->qty);
            //         } else {
            //             if ($existing_item) {
            //                 $tmp = intval($item->qty) - intval($existing_item->qty);
            //                 $tmp_var = ($tmp > 0) ? $tmp : 0;
            //             } else {
            //                 $tmp_var = intval($item->qty);
            //             }
            //         }
            //         $item_data = array();
            //         $item_data['food_menu_id'] = sanitize_font_html($item->food_menu_id);
            //         $item_data['menu_name'] = sanitize_font_html($item->menu_name);
            //         if ($item->is_free==1) {
            //             $item_data['is_free_item'] = $previous_food_id;
            //         } else {
            //             $item_data['is_free_item'] = 0;
            //         }
            //         $item_data['qty'] = sanitize_font_html($item->qty);
            //         $item_data['tmp_qty'] = sanitize_font_html($tmp_var);
            //         $item_data['menu_price_without_discount'] = sanitize_font_html($item->menu_price_without_discount);
            //         $item_data['menu_price_with_discount'] = sanitize_font_html($item->menu_price_with_discount);
            //         $item_data['menu_unit_price'] = sanitize_font_html($item->menu_unit_price);
            //         $item_data['menu_taxes'] = json_encode($item->item_vat);
            //         $item_data['menu_discount_value'] = sanitize_font_html($item->menu_discount_value);
            //         $item_data['discount_type'] = sanitize_font_html($item->discount_type);
            //         $item_data['menu_note'] = sanitize_font_html($item->item_note);
            //         $item_data['menu_combo_items'] = sanitize_font_html($item->menu_combo_items);
            //         $item_data['discount_amount'] = sanitize_font_html($item->item_discount_amount);
            //         $item_data['item_type'] = "Kitchen Item";
            //         $item_data['cooking_start_time'] = ($item->item_cooking_start_time=="" || $item->item_cooking_start_time=="0000-00-00 00:00:00")?'0000-00-00 00:00:00':date('Y-m-d H:i:s',strtotime($item->item_cooking_start_time));
            //         $item_data['cooking_done_time'] = ($item->item_cooking_done_time=="" || $item->item_cooking_done_time=="0000-00-00 00:00:00")?'0000-00-00 00:00:00':date('Y-m-d H:i:s',strtotime($item->item_cooking_done_time));
            //         $item_data['previous_id'] = ($item->item_previous_id=="")?0:$item->item_previous_id;
            //         $item_data['sales_id'] = $sale_id;
            //         $item_data['user_id'] = $this->session->userdata('user_id');
            //         $item_data['outlet_id'] = $this->session->userdata('outlet_id');
            //         $item_data['is_print'] = 0;
            //         if($order_details->customer_id!=1){
            //             $item_data['loyalty_point_earn'] = ($item->qty * getLoyaltyPointByFoodMenu($item->food_menu_id,''));
            //         }
            //         $item_data['del_status'] = 'Live';
            //         $sales_details_id = '';
            //         if ($sale_id) {
            //             if ($existing_item) {
            //                 $sales_details_id = $existing_item->id;
            //                 if ($item->qty > $existing_item->qty) {
            //                     $item_data['cooking_status'] = 'New';
            //                 } else {
            //                     $item_data['cooking_status'] = $existing_item->cooking_status;
            //                 }
            //                 if ($item->qty != $existing_item->qty) {
            //                     $updated_notifications = $this->Common_model->getOrderedKitchens($sale_id);
            //                     foreach ($updated_notifications as $k=>$kitchen){
            //                         $notification_message = 'La Orden:'.$sale_no.' fue modificada. Item Modificado: '.$item->menu_name.", Cant:".$item->qty;
            //                         $bar_kitchen_notification_data = array();
            //                         $bar_kitchen_notification_data['notification'] = $notification_message;
            //                         $bar_kitchen_notification_data['sale_id'] = $sale_id;
            //                         $bar_kitchen_notification_data['outlet_id'] = $this->session->userdata('outlet_id');
            //                         $bar_kitchen_notification_data['kitchen_id'] = $kitchen->kitchen_id;
            //                         $this->db->insert('tbl_notification_bar_kitchen_panel', $bar_kitchen_notification_data);
            //                     }
            //                 }
            //                 $this->Common_model->updateInformation($item_data, $sales_details_id, "tbl_kitchen_sales_details");
            //             } else {
            //                 $item_data['cooking_status'] = 'New';
            //                 $this->db->insert('tbl_kitchen_sales_details', $item_data);
            //                 $sales_details_id = $this->db->insert_id();
            //             }
            //         } else {
            //             $item_data['cooking_status'] = 'New';
            //             $this->db->insert('tbl_kitchen_sales_details', $item_data);
            //             $sales_details_id = $this->db->insert_id();
            //         }
            //         $previous_food_id = $sales_details_id;
            //         $update_previous_id = array();
            //         $update_previous_id['previous_id'] = $previous_food_id;
            //         $this->Common_model->updateInformation($update_previous_id, $sales_details_id, "tbl_kitchen_sales_details");
            //         $modifier_id_array = ($item->modifiers_id!="")?explode(",",$item->modifiers_id):null;
            //         $modifier_price_array = ($item->modifiers_price!="")?explode(",",$item->modifiers_price):null;
            //         $modifier_vat_array = (isset($item->modifier_vat) && $item->modifier_vat!="")?explode("|||",$item->modifier_vat):null;
            //         if(!empty($modifier_id_array)>0){
            //             $i = 0;
            //             foreach($modifier_id_array as $key1=>$single_modifier_id){
            //                 $modifier_data = array();
            //                 $modifier_data['modifier_id'] = sanitize_font_html($single_modifier_id);
            //                 $modifier_data['modifier_price'] = sanitize_font_html($modifier_price_array[$i]);
            //                 $modifier_data['food_menu_id'] = sanitize_font_html($item->food_menu_id);
            //                 $modifier_data['sales_id'] = $sale_id;
            //                 $modifier_data['sales_details_id'] = $sales_details_id;
            //                 $modifier_data['menu_taxes'] = isset($modifier_vat_array[$key1]) && $modifier_vat_array[$key1]?sanitize_font_html($modifier_vat_array[$key1]):'';
            //                 $modifier_data['user_id'] = $this->session->userdata('user_id');
            //                 $modifier_data['outlet_id'] = $this->session->userdata('outlet_id');
            //                 $modifier_data['customer_id'] = sanitize_font_html($order_details->customer_id);
            //                 if($sale_id){
            //                     $check_exist_modifer = checkExistItemModifer($sale_id,$item->food_menu_id,$sales_details_id,$single_modifier_id);
            //                     if(isset($check_exist_modifer) && $check_exist_modifer){
            //                         $sales_details_modifier_id = $check_exist_modifer->id;
            //                         if($existing_item && $item->qty!=$existing_item->qty){
            //                             $modifier_data['is_print'] = 1;
            //                         }
            //                         $this->Common_model->updateInformation($modifier_data, $sales_details_modifier_id, "tbl_kitchen_sales_details_modifiers");
            //                     }else{
            //                         $this->db->insert('tbl_kitchen_sales_details_modifiers', $modifier_data);
            //                     }
            //                 }else{
            //                     $this->db->insert('tbl_kitchen_sales_details_modifiers', $modifier_data);
            //                 }
            //                 $i++;
            //             }
            //         }
            //     }
            // }
            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
            } else {
                $this->db->trans_commit();
                $user_id = $this->session->userdata('user_id');
                $user_data = $this->Common_model->getDataById($user_id, "tbl_users");
                if ($user_data->print_kitchen == 'Yes'){
                    $printers_popup_print = $this->Common_model->getOrderedPrinter($sale_id,1);
                    $printers_direct_print = $this->Common_model->getOrderedPrinter($sale_id,2);
                    $printers_printer_app = $this->Common_model->getOrderedPrinter($sale_id,3);
                } else {
                    $printers_popup_print = [];
                    $printers_direct_print = [];
                    $printers_printer_app = [];
                }
                $is_printing_return = 1;
                $printer_app_qty = 0;
                foreach ($printers_popup_print as $ky=>$value){
                    if(isset($value->id) && $value->id){
                        $is_printing_return++;
                        $sale_items = $this->Common_model->getAllKitchenItemsAuto($sale_id,$value->id);
                        foreach($sale_items as $single_item_by_sale_id){
                            $modifier_information = $this->Sale_model->getModifiersBySaleAndSaleDetailsIdKitchenAuto($sale_id,$single_item_by_sale_id->sales_details_id);
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
                        if($sale_items){
                            $printers_popup_print[$ky]->ipvfour_address = "Yes";
                            $order_type = '';
                            if($order_details->order_type==1){
                                $order_type = lang('dine');
                            }else if($order_details->order_type==2){
                                $order_type = lang('take_away');
                            }else if($order_details->order_type==3){
                                $order_type = lang('delivery');
                            }
                            $printers_popup_print[$ky]->store_name = lang('KOT').":".($value->kitchen_name);
                            $printers_popup_print[$ky]->sale_type = $order_type;
                            $printers_popup_print[$ky]->sale_no_p = $sale_no;
                            $printers_popup_print[$ky]->date = escape_output(date($this->session->userdata('date_format'), strtotime($data['sale_date'])));
                            $printers_popup_print[$ky]->time_inv = $data['order_time'];
                            $printers_popup_print[$ky]->sales_associate = $order_details->user_name;
                            $printers_popup_print[$ky]->customer_name = $order_details->customer_name;
                            $printers_popup_print[$ky]->customer_address = getCustomerAddress($order_details->customer_id);
                            $printers_popup_print[$ky]->waiter_name = $order_details->waiter_name;
                            $printers_popup_print[$ky]->customer_table = $order_details->orders_table_text;
                            $printers_popup_print[$ky]->lang_order_type = lang('order_type');
                            $printers_popup_print[$ky]->lang_Invoice_No = lang('Invoice_No');
                            $printers_popup_print[$ky]->lang_date = lang('date');
                            $printers_popup_print[$ky]->lang_Sales_Associate = lang('Sales_Associate');
                            $printers_popup_print[$ky]->lang_customer = lang('customer');
                            $printers_popup_print[$ky]->lang_address = lang('address');
                            $printers_popup_print[$ky]->lang_gst_number = lang('gst_number');
                            $printers_popup_print[$ky]->lang_waiter = lang('waiter');
                            $printers_popup_print[$ky]->lang_table = lang('table');
                            $printers_popup_print[$ky]->items = $sale_items;
                        }else{
                            $printers_popup_print[$ky]->ipvfour_address = "";
                        }
                    }
                }

                foreach ($printers_direct_print as $ky=>$value){
                    if(isset($value->id) && $value->id){
                        $is_printing_return++;
                        $sale_items = $this->Common_model->getAllKitchenItemsAuto($sale_id,$value->id);
                        foreach($sale_items as $single_item_by_sale_id){
                            $modifier_information = $this->Sale_model->getModifiersBySaleAndSaleDetailsIdKitchenAuto($sale_id,$single_item_by_sale_id->sales_details_id);
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
                        if($sale_items){
                            $printers_direct_print[$ky]->ipvfour_address = ($value->ipvfour_address);
                            $order_type = '';
                            if($order_details->order_type==1){
                                $order_type = lang('dine');
                            }else if($order_details->order_type==2){
                                $order_type = lang('take_away');
                            }else if($order_details->order_type==3){
                                $order_type = lang('delivery');
                            }
                            $printers_direct_print[$ky]->store_name = lang('KOT').":".($value->kitchen_name);
                            $printers_direct_print[$ky]->sale_type = $order_type;
                            $is_modified = ($is_new==0)?' MODIFICADO':'';
                            $printers_direct_print[$ky]->sale_no_p = $sale_no .$is_modified;
                            $printers_direct_print[$ky]->date = escape_output(date($this->session->userdata('date_format'), strtotime($data['sale_date'])));
                            $printers_direct_print[$ky]->time_inv = $data['order_time'];
                            $printers_direct_print[$ky]->sales_associate = $order_details->user_name;
                            $printers_direct_print[$ky]->customer_name = $order_details->customer_name;
                            $printers_direct_print[$ky]->customer_phone = isset($order_details->customer_phone) && $order_details->customer_phone?$order_details->customer_phone:'';
                            $printers_direct_print[$ky]->selected_number_name = isset($order_details->selected_number_name) && $order_details->selected_number_name?$order_details->selected_number_name:'';
                            $printers_direct_print[$ky]->selected_number = isset($order_details->selected_number) && $order_details->selected_number?$order_details->selected_number:'';
                            $printers_direct_print[$ky]->customer_address = getCustomerAddress($order_details->customer_id);
                            $printers_direct_print[$ky]->waiter_name = $order_details->waiter_name;
                            $printers_direct_print[$ky]->customer_table = $order_details->orders_table_text;
                            $printers_direct_print[$ky]->lang_order_type = lang('order_type');
                            $printers_direct_print[$ky]->lang_Invoice_No = lang('Invoice_No');
                            $printers_direct_print[$ky]->lang_date = lang('date');
                            $printers_direct_print[$ky]->lang_Sales_Associate = lang('Sales_Associate');
                            $printers_direct_print[$ky]->lang_customer = lang('customer');
                            $printers_direct_print[$ky]->lang_address = lang('address');
                            $printers_direct_print[$ky]->lang_gst_number = lang('gst_number');
                            $printers_direct_print[$ky]->lang_waiter = lang('waiter');
                            $printers_direct_print[$ky]->lang_table = lang('table');
                            $items = "\n";
                            $count = 1;
                            $count_item_to_print = 0;
                            foreach ($sale_items as $item){
                                if($item->tmp_qty):
                                    $items .= printText((($item->tmp_qty) . " * ".(getPlanData($item->menu_name))), $value->characters_per_line)."\n";
                                    $count++;
                                    if($item->menu_combo_items && $item->menu_combo_items!=null){
                                        $items.= (printText(lang('combo_txt').': '.$item->menu_combo_items,$value->characters_per_line)."\n");
                                    }
                                    if(isset($item->menu_note) && strlen($item->menu_note) > 0){
                                        $items.= (printText(lang('note').': '.$item->menu_note,$value->characters_per_line)."\n");
                                    }
                                    if(isset($item->item_note) && strlen($item->item_note) > 0){
                                        $items.= (printText(lang('note').': '.$item->item_note,$value->characters_per_line)."\n");
                                    }
                                    if(count($item->modifiers)>0){
                                        foreach($item->modifiers as $modifier){
                                            $items .= "   " . printText(" + " .(getPlanData($modifier->name))  , ($value->characters_per_line - 3)) . "\n";
                                        }
                                    }
                                    $count++;
                                    $count_item_to_print++;
                                endif;
                            }
                            if ($count_item_to_print > 0) {
                                $printers_direct_print[$ky]->items = $items;
                            }
                        }else{
                            $printers_direct_print[$ky]->ipvfour_address = '';
                        }
                    }
                }

                foreach ($printers_printer_app as $ky=>$value){
                    
                    if(isset($value->id) && $value->id){
                        $sale_items = $this->Common_model->getAllKitchenItemsAuto($sale_id,$value->id);
                        if (!empty($sale_items)) {
                            foreach ($sale_items as $row) {
                                $printer_app_qty++;
                            }
                        }
                    }
                }

                $company_id = $this->session->userdata('company_id');
                $company = $this->Common_model->getDataById($company_id, "tbl_companies");
                $web_type = $company->printing_kot;

                $return_status = true;
                $kitchens = $this->Common_model->checkPrinterForKOT($sale_id);
                $status_message = '';
                if($kitchens){
                    foreach ($kitchens as $kitchen){
                        if($kitchen->id){
                        }else{
                            $base_url = base_url()."Kitchen/panel/".$kitchen->kitchen_id;
                            $status_message.="<a target='_blank' style='text-decoration: none' href='$base_url'>KOT print failed of ".$kitchen->kitchen_name." because the printer is not connected. You may go to the kitchen panel or click here to got to kitchen panel</a>";
                            $status_message.="|||";
                            $return_status = false;
                        }
                    }
                }
                if($web_type=="web_browser_popup"){

                }else{
                    if($this->session->has_userdata('is_online_order')!="Yes" && !isFoodCourt()){
                        $return_data['printer_server_url'] = ($company->print_server_url_kot);
                        $return_data['content_data_popup_print'] = $printers_popup_print;
                        $return_data['content_data_direct_print'] = $printers_direct_print;
                        $return_data['content_data_printer_app'] = $printers_printer_app;
                        $return_data['printer_app_qty'] = $printer_app_qty;
                        $return_data['print_type'] = "KOT";
                        $return_data['status'] = $return_status;
                        $return_data['sale_id'] = $sale_id;
                        $return_data['status_message'] = $status_message;
                        $return_data['invoice_status'] = '';
                        $return_data['invoice_msg'] = '';
                        echo json_encode($return_data);
                    }
                }
            }
        } else {
            $return_data['invoice_status'] = '1';
            $return_data['invoice_msg'] = 'No se ha enviado datos de ninguna orden';
            echo json_encode($return_data);
            return;
        }
    }


    /**
     * Actualiza los datos del cliente de una venta existente vía AJAX.
     * @access public
     * @return json
     */
    public function update_customer_for_sale_ajax() {
        // Validación de permisos (si es necesario)
        if (isServiceAccessOnly('sGmsJaFJE') && !checkCreatePermissionInvoice()) {
            echo json_encode(['status' => 'error', 'message' => lang('not_permission_invoice_create_error')]);
            return;
        }

        $sale_no = $this->input->post('sale_no');
        $customer_id = $this->input->post('customer_id');
        $customer_name = $this->input->post('customer_name');
        $customer_address = $this->input->post('customer_address');
        $customer_gst_number = $this->input->post('customer_gst_number');

        if (empty($sale_no) || empty($customer_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Faltan datos requeridos (Nº de venta o cliente).']);
            return;
        }

        // Busca la venta en la tabla de cocina
        $sale_details = getKitchenSaleDetailsBySaleNo($sale_no);

        if (!$sale_details || !$sale_details->id) {
            echo json_encode(['status' => 'error', 'message' => 'La venta no fue encontrada.']);
            return;
        }

        $this->db->trans_begin();

        try {
            // 1. Actualizar los campos principales en tbl_kitchen_sales
            $update_data = [
                'customer_id' => trim_checker($customer_id),
                'del_address' => trim_checker($customer_address) ?: "",
                'is_update_sender' => 1,
                'is_update_receiver' => 1,
                'is_update_receiver_admin' => 1,
                'last_update' => gmdate('Y-m-d H:i:s'),
                'modified' => 'Yes'
            ];
            $this->db->where('id', $sale_details->id);
            $this->db->update('tbl_kitchen_sales', $update_data);

            // 2. Actualizar el JSON 'self_order_content'
            $order_content = json_decode($sale_details->self_order_content);
            if ($order_content) {
                $order_content->customer_id = trim_checker($customer_id);
                $order_content->customer_name = trim_checker($customer_name);
                $order_content->customer_address = trim_checker($customer_address) ?: "";
                $order_content->customer_gst_number = trim_checker($customer_gst_number) ?: "";

                $updated_json = json_encode($order_content);
                $this->db->where('id', $sale_details->id);
                $this->db->update('tbl_kitchen_sales', ['self_order_content' => $updated_json]);
            }

            // 3. Opcional: Notificar a la cocina sobre el cambio de cliente (si es relevante)
            $notification_message = "El cliente del pedido {$sale_no} ha sido cambiado a: {$customer_name}";
            $kitchens = $this->Common_model->getOrderedKitchens($sale_details->id);
            foreach ($kitchens as $kitchen) {
                $notification_data = [
                    'notification' => $notification_message,
                    'sale_id' => $sale_details->id,
                    'outlet_id' => $this->session->userdata('outlet_id'),
                    'kitchen_id' => $kitchen->kitchen_id
                ];
                $this->db->insert('tbl_notification_bar_kitchen_panel', $notification_data);
            }

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                echo json_encode(['status' => 'error', 'message' => 'Error en la transacción de la base de datos.']);
            } else {
                $this->db->trans_commit();
                echo json_encode(['status' => 'success', 'message' => 'Cliente actualizado con éxito.']);
            }

        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 'error', 'message' => 'Ocurrió una excepción: ' . $e->getMessage()]);
        }
    }

    /**
     * Actualiza los datos del cliente de una venta existente vía AJAX.
     * @access public
     * @return json
     */
    public function update_customer_for_sale_and_proc_fe_ajax() {
        // Validación de permisos (si es necesario)
        if (isServiceAccessOnly('sGmsJaFJE') && !checkCreatePermissionInvoice()) {
            echo json_encode(['status' => 'error', 'message' => lang('not_permission_invoice_create_error')]);
            return;
        }

        $sale_no = $this->input->post('sale_no');
        $customer_id = $this->input->post('customer_id');
        $customer_name = $this->input->post('customer_name');
        $customer_address = $this->input->post('customer_address');
        $customer_gst_number = $this->input->post('customer_gst_number');

        if (empty($sale_no) || empty($customer_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Faltan datos requeridos (Nº de venta o cliente).']);
            return;
        }

        // Busca la venta en la tabla de cocina
        $sale_detail = getSaleDetails($sale_no);
        if (!$sale_detail || !$sale_detail->id) {
            echo json_encode(['status' => 'error', 'message' => 'La venta no fue encontrada.']);
            return;
        }
        $sale_details = $this->get_all_information_of_a_sale_modify($sale_detail->id);

        if (!$sale_details || !$sale_details->id) {
            echo json_encode(['status' => 'error', 'message' => 'La venta no fue encontrada.']);
            return;
        }

        $this->db->trans_begin();

        try {
            // 1. Actualizar los campos principales en tbl_kitchen_sales
            $update_data = [
                'customer_id' => trim_checker($customer_id),
                'del_address' => trim_checker($customer_address) ?: "",
                // 'is_update_sender' => 1,
                // 'is_update_receiver' => 1,
                // 'is_update_receiver_admin' => 1,
                // 'last_update' => gmdate('Y-m-d H:i:s'),
                // 'modified' => 'Yes'
            ];
            $this->db->where('id', $sale_details->id);
            $this->db->update('tbl_sales', $update_data);

            // 2. Actualizar el JSON 'self_order_content'
            $order_content = json_decode($sale_details->self_order_content);
            if ($order_content) {
                $order_content->customer_id = trim_checker($customer_id);
                $order_content->customer_name = trim_checker($customer_name);
                $order_content->customer_address = trim_checker($customer_address) ?: "";
                $order_content->customer_gst_number = trim_checker($customer_gst_number) ?: "";

                $updated_json = json_encode($order_content);
                $this->db->where('id', $sale_details->id);
                $this->db->update('tbl_sales', ['self_order_content' => $updated_json]);
            }

            // // 3. Opcional: Notificar a la cocina sobre el cambio de cliente (si es relevante)
            // $notification_message = "El cliente del pedido {$sale_no} ha sido cambiado a: {$customer_name}";
            // $kitchens = $this->Common_model->getOrderedKitchens($sale_details->id);
            // foreach ($kitchens as $kitchen) {
            //     $notification_data = [
            //         'notification' => $notification_message,
            //         'sale_id' => $sale_details->id,
            //         'outlet_id' => $this->session->userdata('outlet_id'),
            //         'kitchen_id' => $kitchen->kitchen_id
            //     ];
            //     $this->db->insert('tbl_notification_bar_kitchen_panel', $notification_data);
            // }

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                echo json_encode(['status' => 'error', 'message' => 'Error en la transacción de la base de datos.']);
            } else {
                $this->db->trans_commit();

                //generar factura electrónica
                $estado_factura = $this->generar_factura_electronica($sale_details->id);
                echo json_encode(
                    [
                        // 'printer_app' => $this->printer_app_invoice_prepare($sale_no),
                        'status' => 'success', 
                        'message' => 'Cliente actualizado con  éxito.',
                        'estado_factura' => $estado_factura['status'],
                        'message_factura' => $estado_factura['message'],
                        'details_factura' => $estado_factura['details'] ?? 'Sin detalles disponibles'
                    ]
                );
            }

        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 'error', 'message' => 'Ocurrió una excepción: ' . $e->getMessage()]);
        }
    }

    public function getPrintDataForOrder($sale_no = null) {
        $sale_no = ($sale_no == null) ? $this->input->post('sale_no') : $sale_no;
        $all = $this->input->post('all') ?? '0';
        
        // Obtener los datos de la orden (sin modificarla)
        $sale_d = getKitchenSaleDetailsBySaleNo($sale_no);
        // echo '<pre>';
        // var_dump($sale_d); 
        // echo '<pre>';
        
        if (empty($sale_d)) {
            $return_data = [
                'printer_app_qty' => [],
                'content_data_popup_print' => [],
                'content_data_direct_print' => [],
                'print_type' => 'KOT',
                'status' => [],
                'invoice_status' => '',
                'invoice_msg' => ''
            ];
            
            echo json_encode($return_data);
            return;
        }
        
        $sale_id = $sale_d->id;
        
        // Decodificar el contenido de la orden para obtener los detalles
        $order_details = json_decode($sale_d->self_order_content);
        
        // Preparar datos básicos de la venta
        $data = [
            'sale_date' => $sale_d->sale_date,
            'order_time' => $sale_d->order_time
        ];
        
        // Obtener datos de impresión usando la función helper
        $printData = $this->preparePrintData($sale_id, $order_details, $data, $all);
        
        // Preparar respuesta
        $return_data = [
            'printer_app_qty' => $printData['printer_app_qty'],
            'content_data_popup_print' => $printData['printers_popup_print'],
            'content_data_direct_print' => $printData['printers_direct_print'],
            'print_type' => 'KOT',
            'status' => $printData['return_status'],
            'invoice_status' => '',
            'invoice_msg' => ''
        ];
        
        echo json_encode($return_data);
    }


    /**
     * Prepara los datos de impresión para una orden existente
     * @param int $sale_id ID de la venta
     * @param object $order_details Detalles de la orden
     * @param array $data Datos adicionales de la venta
     * @return array Datos preparados para impresión
     */
    private function preparePrintData($sale_id, $order_details, $data, $all = "0") {
        $printers_popup_print = $this->Common_model->getOrderedPrinter($sale_id, 1);
        $printers_direct_print = $this->Common_model->getOrderedPrinter($sale_id, 2, $all);
        $printers_printer_app = $this->Common_model->getOrderedPrinter($sale_id, 3);
        
        $printer_app_qty = 0;
        
        // Procesar impresoras popup
        foreach ($printers_popup_print as $ky => $value) {
            if (isset($value->id) && $value->id) {
                $sale_items = $this->Common_model->getAllKitchenItemsAuto($sale_id, $value->id);
                
                foreach ($sale_items as $single_item_by_sale_id) {
                    $modifier_information = $this->Sale_model->getModifiersBySaleAndSaleDetailsIdKitchenAuto($sale_id, $single_item_by_sale_id->sales_details_id);
                    $single_item_by_sale_id->modifiers = $modifier_information;

                    $modifiers_id = '';
                    $modifiers_name = '';
                    $modifiers_price = '';
                    
                    foreach ($modifier_information as $ky1 => $val) {
                        $modifiers_id .= $val->modifier_id;
                        $modifiers_name .= $val->name;
                        $modifiers_price .= $val->modifier_price;

                        if ($ky1 < (sizeof($modifier_information)) - 1) {
                            $modifiers_id .= ",";
                            $modifiers_name .= ",";
                            $modifiers_price .= ",";
                        }
                    }
                    
                    $single_item_by_sale_id->modifiers_id = $modifiers_id;
                    $single_item_by_sale_id->modifiers_name = $modifiers_name;
                    $single_item_by_sale_id->modifiers_price = $modifiers_price;
                }
                
                if ($sale_items) {
                    $printers_popup_print[$ky]->ipvfour_address = "Yes";
                    $order_type = '';
                    
                    if ($order_details->order_type == 1) {
                        $order_type = lang('dine');
                    } else if ($order_details->order_type == 2) {
                        $order_type = lang('take_away');
                    } else if ($order_details->order_type == 3) {
                        $order_type = lang('delivery');
                    }
                    
                    $printers_popup_print[$ky]->store_name = lang('KOT') . ":" . ($value->kitchen_name);
                    $printers_popup_print[$ky]->sale_type = $order_type;
                    $printers_popup_print[$ky]->sale_no_p = $order_details->sale_no;
                    $printers_popup_print[$ky]->date = escape_output(date($this->session->userdata('date_format'), strtotime($data['sale_date'])));
                    $printers_popup_print[$ky]->time_inv = $data['order_time'];
                    $printers_popup_print[$ky]->sales_associate = $order_details->user_name;
                    $printers_popup_print[$ky]->customer_name = $order_details->customer_name;
                    $printers_popup_print[$ky]->customer_address = getCustomerAddress($order_details->customer_id);
                    $printers_popup_print[$ky]->waiter_name = $order_details->waiter_name;
                    $printers_popup_print[$ky]->customer_table = $order_details->orders_table_text;
                    $printers_popup_print[$ky]->lang_order_type = lang('order_type');
                    $printers_popup_print[$ky]->lang_Invoice_No = lang('Invoice_No');
                    $printers_popup_print[$ky]->lang_date = lang('date');
                    $printers_popup_print[$ky]->lang_Sales_Associate = lang('Sales_Associate');
                    $printers_popup_print[$ky]->lang_customer = lang('customer');
                    $printers_popup_print[$ky]->lang_address = lang('address');
                    $printers_popup_print[$ky]->lang_gst_number = lang('gst_number');
                    $printers_popup_print[$ky]->lang_waiter = lang('waiter');
                    $printers_popup_print[$ky]->lang_table = lang('table');
                    $printers_popup_print[$ky]->items = $sale_items;
                } else {
                    $printers_popup_print[$ky]->ipvfour_address = "";
                }
            }
        }

        // Procesar impresoras directas
        foreach ($printers_direct_print as $ky => $value) {
            if (isset($value->id) && $value->id) {
                $sale_items = $this->Common_model->getAllKitchenItemsAuto($sale_id, $value->id);
                
                foreach ($sale_items as $single_item_by_sale_id) {
                    $modifier_information = $this->Sale_model->getModifiersBySaleAndSaleDetailsIdKitchenAuto($sale_id, $single_item_by_sale_id->sales_details_id);
                    $single_item_by_sale_id->modifiers = $modifier_information;
                    
                    $modifiers_id = '';
                    $modifiers_name = '';
                    $modifiers_price = '';
                    
                    foreach ($modifier_information as $ky1 => $val) {
                        $modifiers_id .= $val->modifier_id;
                        $modifiers_name .= $val->name;
                        $modifiers_price .= $val->modifier_price;

                        if ($ky1 < (sizeof($modifier_information)) - 1) {
                            $modifiers_id .= ",";
                            $modifiers_name .= ",";
                            $modifiers_price .= ",";
                        }
                    }
                    
                    $single_item_by_sale_id->modifiers_id = $modifiers_id;
                    $single_item_by_sale_id->modifiers_name = $modifiers_name;
                    $single_item_by_sale_id->modifiers_price = $modifiers_price;
                }
                
                if ($sale_items) {
                    $printers_direct_print[$ky]->ipvfour_address = ($value->ipvfour_address);
                    $order_type = '';
                    
                    if ($order_details->order_type == 1) {
                        $order_type = lang('dine');
                    } else if ($order_details->order_type == 2) {
                        $order_type = lang('take_away');
                    } else if ($order_details->order_type == 3) {
                        $order_type = lang('delivery');
                    }
                    
                    $printers_direct_print[$ky]->store_name = lang('KOT') . ":" . ($value->kitchen_name);
                    $printers_direct_print[$ky]->sale_type = $order_type;
                    $printers_direct_print[$ky]->sale_no_p = $order_details->sale_no;
                    $printers_direct_print[$ky]->date = escape_output(date($this->session->userdata('date_format'), strtotime($data['sale_date'])));
                    $printers_direct_print[$ky]->time_inv = $data['order_time'];
                    $printers_direct_print[$ky]->sales_associate = $order_details->user_name;
                    $printers_direct_print[$ky]->customer_name = $order_details->customer_name;
                    $printers_direct_print[$ky]->customer_phone = isset($order_details->customer_phone) && $order_details->customer_phone ? $order_details->customer_phone : '';
                    $printers_direct_print[$ky]->selected_number = isset($order_details->selected_number) && $order_details->selected_number ? $order_details->selected_number : '';
                    $printers_direct_print[$ky]->selected_number_name = isset($order_details->selected_number_name) && $order_details->selected_number_name ? $order_details->selected_number_name : '';
                    $printers_direct_print[$ky]->customer_address = getCustomerAddress($order_details->customer_id);
                    $printers_direct_print[$ky]->waiter_name = $order_details->waiter_name;
                    $printers_direct_print[$ky]->customer_table = $order_details->orders_table_text;
                    $printers_direct_print[$ky]->lang_order_type = lang('order_type');
                    $printers_direct_print[$ky]->lang_Invoice_No = lang('Invoice_No');
                    $printers_direct_print[$ky]->lang_date = lang('date');
                    $printers_direct_print[$ky]->lang_Sales_Associate = lang('Sales_Associate');
                    $printers_direct_print[$ky]->lang_customer = lang('customer');
                    $printers_direct_print[$ky]->lang_address = lang('address');
                    $printers_direct_print[$ky]->lang_gst_number = lang('gst_number');
                    $printers_direct_print[$ky]->lang_waiter = lang('waiter');
                    $printers_direct_print[$ky]->lang_table = lang('table');
                    
                    $items = "\n";
                    $count = 1;
                    $count_item_to_print = 0;
                    
                    foreach ($sale_items as $item) {
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
                            $items .= printText((($qty) . " * ".(getPlanData($item->menu_name))), $value->characters_per_line)."\n";
                            $count++;
                            $count_item_to_print++;
                            
                            if ($item->menu_combo_items && $item->menu_combo_items != null) {
                                $items .= (printText(lang('combo_txt') . ': ' . $item->menu_combo_items, $value->characters_per_line) . "\n");
                            }
                            
                            if (isset($item->menu_note) && strlen($item->menu_note) > 0) {
                                $items .= (printText(lang('note') . ': ' . $item->menu_note, $value->characters_per_line) . "\n");
                            }
                            
                            if (isset($item->item_note) && strlen($item->item_note) > 0) {
                                $items .= (printText(lang('note') . ': ' . $item->item_note, $value->characters_per_line) . "\n");
                            }
                            
                            if (count($item->modifiers) > 0) {
                                foreach ($item->modifiers as $modifier) {
                                    $items .= "   " . printText(" + " .(getPlanData($modifier->name))  , ($value->characters_per_line - 3)) . "\n";
                                }
                            }
                            
                            $count++;
                        }
                    }
                    
                    if ($count_item_to_print > 0) {
                        $printers_direct_print[$ky]->items = $items;
                    }
                } else {
                    $printers_direct_print[$ky]->ipvfour_address = '';
                }
            }
        }

        // Procesar impresoras de app
        foreach ($printers_printer_app as $ky => $value) {
            if (isset($value->id) && $value->id) {
                $sale_items = $this->Common_model->getAllKitchenItemsAuto($sale_id, $value->id,0);
                
                if (!empty($sale_items)) {
                    foreach ($sale_items as $row) {
                        $printer_app_qty++;
                    }
                }
            }
        }

        // Verificar estado de impresoras
        $return_status = true;
        $kitchens = $this->Common_model->checkPrinterForKOT($sale_id);
        $status_message = '';
        
        if ($kitchens) {
            foreach ($kitchens as $kitchen) {
                if (!$kitchen->id) {
                    $base_url = base_url() . "Kitchen/panel/" . $kitchen->kitchen_id;
                    $status_message .= "<a target='_blank' style='text-decoration: none' href='$base_url'>KOT print failed of " . $kitchen->kitchen_name . " because the printer is not connected. You may go to the kitchen panel or click here to got to kitchen panel</a>";
                    $status_message .= "|||";
                    $return_status = false;
                }
            }
        }

        // Obtener configuración de la compañía
        $company_id = $this->session->userdata('company_id');
        $company = $this->Common_model->getDataById($company_id, "tbl_companies");
        $web_type = $company->printing_kot;

        return [
            'printers_popup_print' => $printers_popup_print,
            'printers_direct_print' => $printers_direct_print,
            'printers_printer_app' => $printers_printer_app,
            'printer_app_qty' => $printer_app_qty,
            'return_status' => $return_status,
            'status_message' => $status_message,
            'company' => $company
        ];
    }

    public function pull_running_order(){
        /*This variable could not be escaped because this is json data*/
        $order = $this->input->post('order');
        $user_id = $this->input->post('user_id');
        $order_details = (json_decode($order));
        $sale_no = $order_details->sale_no;
        $sale_d = getExistOrderInfo($sale_no);

        $order_info = array();
        $order_info['sale_no'] = $sale_no;
        $order_info['order_content'] = $order;
        $order_info['user_id'] = $user_id;

        if(isset($sale_d) && $sale_d){
            $this->db->where('id', $sale_d->id);
            $this->db->update("tbl_running_orders", $order_info);
        }else{
            $this->db->insert('tbl_running_orders',$order_info);
        }
        echo json_encode("success");
    }
    public function put_table_content(){
        /*This variable could not be escaped because this is json data*/
        $table_info = $this->input->post('table_info');
        $table_id = $this->input->post('table_id');
        $order_details = (json_decode($table_info));
        $sale_no = $order_details->sale_no;
        $persons = $order_details->persons;

        $sale_d = getExistOrderInfoTable($sale_no,$table_id);

        $order_info = array();
        $order_info['sale_no'] = $sale_no;
        $order_info['table_id'] = $table_id;
        $order_info['persons'] = $persons;
        $order_info['table_content'] = $table_info;
        $order_info['outlet_id'] = $this->session->userdata('outlet_id');

        if(isset($sale_d) && $sale_d){
            $order_info['persons'] = ($sale_d->persons + $persons);
            $this->db->where('id', $sale_d->id);
            $this->db->update("tbl_running_order_tables", $order_info);
        }else{
            $this->db->insert('tbl_running_order_tables',$order_info);
        }
        echo json_encode("success");
    }
    public function pull_running_order_server(){
        /*This variable could not be escaped because this is json data*/
        $user_id = $this->session->userdata('user_id');
        $data = getRunningOrders($user_id);
        echo json_encode($data);
    }

   
    public function add_cancel_audit_report(){
        /*This variable could not be escaped because this is json data*/
        $order = $this->input->post('order');
        $reason = $this->input->post('reason');
        $order_details = (json_decode($order));

        $select_kitchen_row = getKitchenSaleDetailsBySaleNo($order_details->sale_no);
        if($select_kitchen_row){
            // 1. Obtener el número asociado antes de eliminar
            $number_slot = $select_kitchen_row->number_slot;
            $this->db->delete("tbl_kitchen_sales_details", array("sales_id" => $select_kitchen_row->id));
            $this->db->delete("tbl_kitchen_sales_details_modifiers", array("sales_id" => $select_kitchen_row->id));
            $this->db->delete("tbl_kitchen_sales", array("id" => $select_kitchen_row->id));
            
            // 2. Resetear el número si existe
            if($number_slot && $number_slot > 0) {
                $this->db->where('id', $number_slot)
                        ->update('tbl_numeros', [
                            'sale_id' => NULL,
                            'sale_no' => NULL,
                            "user_id" => NULL
                        ]);
            }
        }

        $txt = '<b>Reason: '.$reason."</b>";
        $txt .= '<br>';

        $customer_info = getCustomerData($order_details->customer_id);
        $txt.="Sale No: ".$order_details->sale_no.", ";
        $txt.="Sale Date: ".date($this->session->userdata('date_format'), strtotime($order_details->date_time)).", ";
        $txt.="Customer: ".(isset($customer_info) && $customer_info->name?$customer_info->name:'---')." - ".(isset($customer_info) && $customer_info->phone?$customer_info->phone:'').", ";

        if(isset($order_details->total_vat) && $order_details->total_vat){
            $txt.="VAT: ".$order_details->total_vat.",";
        }
        if(isset($order_details->total_discount_amount) && $order_details->total_discount_amount){
            $txt.="Discount: ".$order_details->total_discount_amount.", ";
        }
        if(isset($order_details->delivery_charge) && $order_details->delivery_charge){
            $txt.="Charge: ".$order_details->delivery_charge.", ";
        }
        if(isset($order_details->tips_amount) && $order_details->tips_amount){
            $txt.="Tips: ".$order_details->tips_amount.", ";
        }
        $txt.="Total Payable: ".$order_details->total_payable;
        if(count($order_details->items)>0){
            $txt.="<br><b>Items:</b><br>";
            foreach($order_details->items as $key=>$item){

                $txt.=$item->menu_name."("."$item->qty X $item->menu_unit_price".")";
                if($item->menu_combo_items  && $item->menu_combo_items!='undefined'){
                    $txt.="=><b>Combo Items: </b>";
                    $txt.=$item->menu_combo_items;
                }
                if($key < (sizeof($order_details->items) -1)){
                    $txt.=", ";
                }
                $modifier_id_array = ($item->modifiers_id!="")?explode(",",$item->modifiers_id):null;
                if(!empty($modifier_id_array)>0){
                    $i = 0;
                    $txt.=", <b>&nbsp;&nbsp;Modifier:</b>";
                    foreach($modifier_id_array as $key1=>$single_modifier_id){
                        $txt.="&nbsp;&nbsp;".getModifierNameById($single_modifier_id);
                        if($key1 < (sizeof($modifier_id_array) -1)){
                            $txt.=", ";
                        }
                        $i++;
                    }
                }
            }
        }

        $notification = "Se ha eliminado un pedido, el número de pedido es: ".$order_details->sale_no;
        $notification_data = array();
        $notification_data['notification'] = $notification;
        $notification_data['sale_id'] = $select_kitchen_row->id;
        $notification_data['waiter_id'] = trim_checker($order_details->waiter_id);
        $notification_data['outlet_id'] = $this->session->userdata('outlet_id');
        $this->db->insert('tbl_notifications', $notification_data);

        //store audit log data
        putAuditLog($this->session->userdata('user_id'),$txt,"Cancelled Sale",date('Y-m-d H:i:s'));

    }
    public function add_sale_by_ajax_split(){
        $order_details = json_decode(json_decode($this->input->post('order')));
        //this id will be 0 when there is new order, but will be greater then 0 when there is modification
        //on previous order
        $sale_id_old_sale_id = $this->input->post('sale_id_old_sale_id');
        $is_last_split = trim_checker($order_details->is_last_split);
        $split_sale = $this->Common_model->getDataById($sale_id_old_sale_id, "tbl_sales");

        $data = array();
        $data['split_sale_id'] = $sale_id_old_sale_id;
        $data['customer_id'] = trim_checker($order_details->customer_id);
        $data['counter_id'] = trim_checker($order_details->counter_id);
        $data['delivery_partner_id'] = trim_checker($order_details->delivery_partner_id);
        $data['rounding_amount_hidden'] = trim_checker($order_details->rounding_amount_hidden);
        $data['previous_due_tmp'] = trim_checker($order_details->previous_due_tmp);
        $data['total_items'] = trim_checker($order_details->total_items_in_cart);
        $data['sub_total'] = trim_checker($order_details->sub_total);
        $data['charge_type'] = trim_checker($split_sale->charge_type);
        $data['vat'] = trim_checker($order_details->total_vat);
        $data['total_payable'] = trim_checker($order_details->total_payable);

        $data['total_item_discount_amount'] = trim_checker($order_details->total_item_discount_amount);
        $data['sub_total_with_discount'] = trim_checker($order_details->sub_total_with_discount);
        $data['sub_total_discount_amount'] = trim_checker($order_details->sub_total_discount_amount);
        $data['total_discount_amount'] = trim_checker($order_details->total_discount_amount);
        $data['delivery_charge'] = trim_checker($order_details->delivery_charge);
        $data['delivery_charge_actual_charge'] = trim_checker($order_details->delivery_charge_actual_charge);
        $data['tips_amount'] = trim_checker($order_details->tips_amount);
        $data['tips_amount_actual_charge'] = trim_checker($order_details->tips_amount_actual_charge);
        $data['sub_total_discount_value'] = trim_checker($order_details->sub_total_discount_value);
        $data['sub_total_discount_type'] = trim_checker($order_details->sub_total_discount_type);

        $data['user_id'] = $this->session->userdata('user_id');
        $data['waiter_id'] = trim_checker($split_sale->waiter_id);
        $data['outlet_id'] = $this->session->userdata('outlet_id');
        $data['company_id'] = $this->session->userdata('company_id');
        $data['sale_date'] = trim_checker(isset($order_details->open_invoice_date_hidden) && $order_details->open_invoice_date_hidden?$order_details->open_invoice_date_hidden:date('Y-m-d'));
        $data['date_time'] = date('Y-m-d H:i:s',strtotime($split_sale->date_time));
        $data['order_time'] = date('Y-m-d H:i:s',strtotime($split_sale->date_time));
        $data['order_status'] = trim_checker($order_details->order_status);

        $total_tax = 0;
        if(isset($order_details->sale_vat_objects) && $order_details->sale_vat_objects){
            foreach ($order_details->sale_vat_objects as $keys=>$val){
                $total_tax+=$val->tax_field_amount;
            }
        }
        $data['vat'] = $total_tax;
        $data['sale_vat_objects'] = json_encode($order_details->sale_vat_objects);

        $data['order_type'] = trim_checker($split_sale->order_type);
        $this->db->trans_begin();
        $data['random_code'] = getRandomCode(15);
        $query = $this->db->insert('tbl_sales', $data);
        $sales_id = $this->db->insert_id();

        $split_total_bill = getSplitTotalBill($sale_id_old_sale_id);
        $sale_no = str_pad($split_total_bill, 3, '0', STR_PAD_LEFT);

        $sale_no_update_array = array();
        $sale_no_update_array['sale_no'] = $split_sale->sale_no."-".$sale_no;
        $this->db->where('id', $sales_id);
        $this->db->update('tbl_sales', $sale_no_update_array);

            $old_update_array = array();
            $old_update_array['sub_total'] = $split_sale->sub_total - trim_checker($order_details->sub_total);
            $old_update_array['vat'] = (float)$split_sale->vat - trim_checker($order_details->total_vat);
            $old_update_array['total_payable'] = $split_sale->total_payable - trim_checker($order_details->total_payable);
            $old_update_array['total_item_discount_amount'] = (float)$split_sale->total_item_discount_amount - trim_checker($order_details->total_item_discount_amount);
            $old_update_array['sub_total_with_discount'] = (float)$split_sale->sub_total_with_discount - (trim_checker(($order_details->sub_total)- trim_checker($order_details->sub_total_discount_amount)));
            $old_update_array['sub_total_discount_amount'] = $split_sale->sub_total_discount_amount - trim_checker($order_details->sub_total_discount_amount);
            $old_update_array['total_discount_amount'] = (float)$split_sale->total_discount_amount - trim_checker($order_details->total_discount_amount);
            $old_update_array['delivery_charge'] = (float)$split_sale->delivery_charge_actual_charge - trim_checker($order_details->delivery_charge_actual_charge);
            $old_update_array['delivery_charge_actual_charge'] = (float)$split_sale->delivery_charge_actual_charge - trim_checker($order_details->delivery_charge_actual_charge);
            $old_update_array['tips_amount'] = (float)$split_sale->tips_amount_actual_charge - trim_checker($order_details->tips_amount_actual_charge);
            $old_update_array['tips_amount_actual_charge'] = (float)$split_sale->tips_amount_actual_charge - trim_checker($order_details->tips_amount_actual_charge);
            $old_update_array['sub_total_discount_value'] = (float)$split_sale->sub_total_discount_value - trim_checker($order_details->sub_total_discount_value);
            $old_update_array['sub_total_discount_type'] = "fixed";
            $this->db->where('id', $sale_id_old_sale_id);
            $this->db->update('tbl_sales', $old_update_array);

        //update table
        $exist_tables = $this->Common_model->getAllByCustomId($sale_id_old_sale_id,'sale_id',"tbl_orders_table",'');
        $order_table_info = array();
        $is_update = 1;
        $table_update_id = 0;
        foreach ($exist_tables as $vl_tbl){
            if($is_update==1){
                $is_update++;
                $order_table_info['persons'] = $vl_tbl->persons - 1;
                if($vl_tbl->persons==1){
                    $this->db->delete('tbl_orders_table', array('id' => $vl_tbl->id));
                }else{
                    $table_update_id = $vl_tbl->id;
                }
            }
        }

        if($table_update_id){
            $this->Common_model->updateInformation($order_table_info, $table_update_id, "tbl_orders_table");
        }


        $data_sale_consumptions = array();
        $data_sale_consumptions['sale_id'] = $sales_id;
        $data_sale_consumptions['user_id'] = $this->session->userdata('user_id');
        $data_sale_consumptions['outlet_id'] = $this->session->userdata('outlet_id');
        $data_sale_consumptions['del_status'] = 'Live';
        $this->db->insert('tbl_sale_consumptions',$data_sale_consumptions);
        $sale_consumption_id = $this->db->insert_id();

        if($sales_id>0 && count($order_details->items)>0){
            $previous_food_id = 0;
            foreach($order_details->items as $item){
                $exist_food_menu = getExistFoodMenu($sale_id_old_sale_id,$item->item_id);
                 if(isset($exist_food_menu) && $exist_food_menu->qty){
                     $tamp_split_qty_remaining = $exist_food_menu->qty - $item->item_quantity;
                     $tamp_split_discount_remaining = $exist_food_menu->discount_amount - $item->item_discount;
                     $tamp_split_item_price_without_discount_remaining = $exist_food_menu->menu_price_without_discount - $item->menu_price_without_discount;
                     $tamp_split_item_price_with_discount_remaining = $exist_food_menu->menu_price_with_discount - $item->item_price_with_discount;

                     $update_arr = array();
                         $update_arr['qty'] = $tamp_split_qty_remaining;
                         $update_arr['tmp_qty'] = $tamp_split_qty_remaining;
                         $update_arr['menu_price_without_discount'] =$tamp_split_item_price_without_discount_remaining;
                         $update_arr['menu_price_with_discount'] =$tamp_split_item_price_with_discount_remaining;
                         $update_arr['menu_discount_value'] =$tamp_split_discount_remaining;
                         $update_arr['discount_amount'] =$tamp_split_discount_remaining;
                         $this->Common_model->updateInformation($update_arr, $exist_food_menu->id, "tbl_sales_details");


                     $item_data = array();
                     $item_data['food_menu_id'] = $item->item_id;
                     $item_data['menu_name'] = $exist_food_menu->menu_name;
                     if($item->is_free==1){
                         $item_data['is_free_item'] = $previous_food_id;
                     }else{
                         $item_data['is_free_item'] = 0;
                     }

                     $item_data['qty'] = $item->item_quantity;
                     $item_data['tmp_qty'] = $item->item_quantity;
                     $item_data['menu_price_without_discount'] = $item->menu_price_without_discount;
                     $item_data['menu_price_with_discount'] = $item->item_price_with_discount;
                     $item_data['menu_unit_price'] = $item->item_unit_price;
                     $item_data['menu_taxes'] = '';
                     $item_data['menu_discount_value'] = $item->item_discount;
                     $item_data['discount_type'] = $item->discount_type;
                     $item_data['menu_note'] = $exist_food_menu->menu_note;;
                     $item_data['menu_combo_items'] = $exist_food_menu->menu_combo_items;;
                     $item_data['is_free_item'] = $exist_food_menu->is_free_item;;
                     $item_data['discount_amount'] = $item->item_discount_amount;
                     $item_data['item_type'] = "Kitchen Item";
                     $item_data['cooking_status'] = ($item->item_cooking_status=="")?NULL:$item->item_cooking_status;
                     $item_data['cooking_start_time'] = ($item->item_cooking_start_time=="" || $item->item_cooking_start_time=="0000-00-00 00:00:00")?'0000-00-00 00:00:00':date('Y-m-d H:i:s',strtotime($item->item_cooking_start_time));
                     $item_data['cooking_done_time'] = ($item->item_cooking_done_time=="" || $item->item_cooking_done_time=="0000-00-00 00:00:00")?'0000-00-00 00:00:00':date('Y-m-d H:i:s',strtotime($item->item_cooking_done_time));
                     $item_data['previous_id'] = ($item->item_previous_id=="")?0:$item->item_previous_id;
                     $item_data['sales_id'] = $sales_id;
                     $item_data['user_id'] = $this->session->userdata('user_id');
                     $item_data['outlet_id'] = $this->session->userdata('outlet_id');
                     if($order_details->customer_id!=1){
                         $item_data['loyalty_point_earn'] = ($item->item_quantity * getLoyaltyPointByFoodMenu($item->item_id,''));
                     }
                     $item_data['del_status'] = 'Live';
                     $item_data['cooking_status'] = 'New';
                     $this->db->insert('tbl_sales_details', $item_data);
                     $sales_details_id = $this->db->insert_id();
                     $previous_food_id = $sales_details_id;
                     if($item->item_previous_id==""){
                         $previous_id_update_array = array('previous_id' => $sales_details_id);
                         $this->db->where('id', $sales_details_id);
                         $this->db->update('tbl_sales_details', $previous_id_update_array);
                     }

                     $item_details = $this->db->query("SELECT * FROM tbl_food_menus WHERE id=$item->item_id")->row();

                     if(isset($item_details->product_type) && $item_details->product_type==1){
                         $food_menu_ingredients = $this->db->query("SELECT * FROM tbl_food_menus_ingredients WHERE food_menu_id=$item->item_id")->result();
                         foreach($food_menu_ingredients as $single_ingredient){
                             $data_sale_consumptions_detail = array();
                             $data_sale_consumptions_detail['ingredient_id'] = $single_ingredient->ingredient_id;
                             $data_sale_consumptions_detail['consumption'] = $item->item_quantity*$single_ingredient->consumption;
                             $data_sale_consumptions_detail['sale_consumption_id'] = $sale_consumption_id;
                             $data_sale_consumptions_detail['sales_id'] = $sales_id;
                             $data_sale_consumptions_detail['food_menu_id'] = $item->item_id;
                             $data_sale_consumptions_detail['user_id'] = $this->session->userdata('outlet_id');
                             $data_sale_consumptions_detail['outlet_id'] = $this->session->userdata('outlet_id');
                             $data_sale_consumptions_detail['del_status'] = 'Live';
                             $this->db->insert('tbl_sale_consumptions_of_menus',$data_sale_consumptions_detail);
                         }
                     }else{
                         $combo_food_menus = $this->db->query("SELECT * FROM tbl_combo_food_menus WHERE food_menu_id=$item->item_id AND del_status='Live'")->result();
                         if(isset($combo_food_menus) && $combo_food_menus){
                             foreach ($combo_food_menus as $single_combo_fm){
                                 $food_menu_ingredients = $this->db->query("SELECT * FROM tbl_food_menus_ingredients WHERE food_menu_id=$single_combo_fm->added_food_menu_id")->result();
                                 foreach($food_menu_ingredients as $single_ingredient){
                                     $data_sale_consumptions_detail = array();
                                     $data_sale_consumptions_detail['ingredient_id'] = $single_ingredient->ingredient_id;
                                     $data_sale_consumptions_detail['consumption'] = $item->item_quantity * ($single_combo_fm->quantity*$single_ingredient->consumption);
                                     $data_sale_consumptions_detail['sale_consumption_id'] = $sale_consumption_id;
                                     $data_sale_consumptions_detail['sales_id'] = $sales_id;
                                     $data_sale_consumptions_detail['food_menu_id'] = $item->item_id;
                                     $data_sale_consumptions_detail['user_id'] = $this->session->userdata('outlet_id');
                                     $data_sale_consumptions_detail['outlet_id'] = $this->session->userdata('outlet_id');
                                     $data_sale_consumptions_detail['del_status'] = 'Live';
                                     $this->db->insert('tbl_sale_consumptions_of_menus',$data_sale_consumptions_detail);
                                 }
                             }

                         }
                     }

                     $exist_food_menu_modifier = getExistFoodMenuModifier($sale_id_old_sale_id,$item->item_id);
                     if(isset($exist_food_menu_modifier) && $exist_food_menu_modifier){
                         foreach($exist_food_menu_modifier as $key1=>$single_modifier_value){
                             $modifier_data = array();
                             $modifier_data['modifier_id'] =$single_modifier_value->modifier_id;
                             $modifier_data['modifier_price'] = $single_modifier_value->modifier_price;
                             $modifier_data['food_menu_id'] = $item->item_id;
                             $modifier_data['sales_id'] = $sales_id;
                             $modifier_data['sales_details_id'] = $sales_details_id;
                             $modifier_data['menu_taxes'] = $single_modifier_value->menu_taxes;
                             $modifier_data['user_id'] = $this->session->userdata('user_id');
                             $modifier_data['outlet_id'] = $this->session->userdata('outlet_id');
                             $modifier_data['customer_id'] =$order_details->customer_id;
                             $query = $this->db->insert('tbl_sales_details_modifiers', $modifier_data);

                            //  $modifier_ingredients = $this->db->query("SELECT * FROM tbl_modifier_ingredients WHERE modifier_id=$single_modifier_value->modifier_id")->result();
                             $clean_modifier_id = sanitize_font_html($single_modifier_value->modifier_id);
                             $modifier_ingredients = $this->db->query("SELECT * FROM tbl_modifier_ingredients WHERE modifier_id=" . intval($clean_modifier_id))->result();
                             foreach($modifier_ingredients as $single_ingredient){
                                 $data_sale_consumptions_detail = array();
                                 $data_sale_consumptions_detail['ingredient_id'] = $single_ingredient->ingredient_id;
                                 $data_sale_consumptions_detail['consumption'] = $item->item_quantity*$single_ingredient->consumption;
                                 $data_sale_consumptions_detail['sale_consumption_id'] = $sale_consumption_id;
                                 $data_sale_consumptions_detail['sales_id'] = $sales_id;
                                 $data_sale_consumptions_detail['food_menu_id'] = $item->item_id;
                                 $data_sale_consumptions_detail['user_id'] = $this->session->userdata('user_id');
                                 $data_sale_consumptions_detail['outlet_id'] = $this->session->userdata('outlet_id');
                                 $data_sale_consumptions_detail['del_status'] = 'Live';
                                 $query = $this->db->insert('tbl_sale_consumptions_of_modifiers_of_menus',$data_sale_consumptions_detail);
                             }
                         }
                     }

                 }
            }
        }
        if($is_last_split==1){
            $status_update_array = array('order_status' => "3");
            $this->db->where('id', $sale_id_old_sale_id);
            $this->db->update('tbl_sales', $status_update_array);

            $status_update_array = array('del_status' => "Deleted");
            $this->db->where('id', $sale_id_old_sale_id);
            $this->db->update('tbl_sales', $status_update_array);

            $this->db->delete('tbl_sales_details', array('sales_id' => $sale_id_old_sale_id));
            $this->db->delete('tbl_sales_details_modifiers', array('sales_id' => $sale_id_old_sale_id));
            $this->db->delete('tbl_sale_consumptions', array('sale_id' => $sale_id_old_sale_id));
            $this->db->delete('tbl_sale_consumptions_of_menus', array('sales_id' => $sale_id_old_sale_id));
            $this->db->delete('tbl_sale_consumptions_of_modifiers_of_menus', array('sales_id' => $sale_id_old_sale_id));
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
        } else {
            echo escape_output($sales_id);
            $this->db->trans_commit();
        }

    }

    public function push_online(){
        // $this->load->helper('sanitize_font_html');
        $sale_id_offline = $this->input->post('sales_id');
        $order_details = json_decode(($this->input->post('orders')));
        $sale_no = $order_details->sale_no;
    
        // --- 2. Preparar el array de respuesta ---
        $response_data = [
            'status' => 'error', // Estado por defecto
            'sale_id_offline' => $sale_id_offline,
            'factura_electronica' => null // Espacio para el resultado de la FE
        ];

        $sale_id = '';
        $check_existing = getSaleDetailsBySaleNo($sale_no);
        $select_kitchen_row = getKitchenSaleDetailsBySaleNo($sale_no);
    
        // $kitchen_sale = $this->db->select('number_slot,number_slot_name')
        // ->where('sale_no', $sale_no)
        // ->get('tbl_kitchen_sales')
        // ->row();

        // Obtener la orden de cocina correspondiente para revisar si ya tiene factura
        $kitchen_sale = $this->db->where('sale_no', $sale_no)->get('tbl_kitchen_sales')->row();

        if(isset($check_existing) && $check_existing){
            $sale_id = $check_existing->id;
        }
        $data = array();
        $data['self_order_content'] = $this->input->post('orders');
        $data['number_slot'] = (isset($kitchen_sale) && $kitchen_sale->number_slot) ? $kitchen_sale->number_slot : '';
        $data['number_slot_name'] = (isset($kitchen_sale) && $kitchen_sale->number_slot_name) ? $kitchen_sale->number_slot_name : '';
        $data['customer_id'] = trim_checker($order_details->customer_id);
        $data['counter_id'] = trim_checker($order_details->counter_id);
        $data['delivery_partner_id'] = trim_checker($order_details->delivery_partner_id);
        $data['total_items'] = trim_checker($order_details->total_items_in_cart);
        $data['sub_total'] = trim_checker($order_details->sub_total);
        $data['charge_type'] = trim_checker($order_details->charge_type);
        $data['previous_due_tmp'] = trim_checker($order_details->previous_due_tmp);
        $data['vat'] = trim_checker($order_details->total_vat);
        $data['total_payable'] = trim_checker($order_details->total_payable);
        $data['total_item_discount_amount'] = trim_checker($order_details->total_item_discount_amount);
        $data['sub_total_with_discount'] = trim_checker($order_details->sub_total_with_discount);
        $data['sub_total_discount_amount'] = trim_checker($order_details->sub_total_discount_amount);
        $data['total_discount_amount'] = trim_checker($order_details->total_discount_amount);
        $data['tips_amount'] = trim_checker($order_details->tips_amount);
        $data['tips_amount_actual_charge'] = trim_checker($order_details->tips_amount_actual_charge);
        $data['delivery_charge'] = trim_checker($order_details->delivery_charge);
        $data['delivery_charge_actual_charge'] = trim_checker($order_details->delivery_charge_actual_charge);
        $data['sub_total_discount_value'] = trim_checker($order_details->sub_total_discount_value);
        $data['sub_total_discount_type'] = trim_checker($order_details->sub_total_discount_type);
        $data['given_amount'] = trim_checker($order_details->hidden_given_amount);
        $data['change_amount'] = trim_checker($order_details->hidden_change_amount);
        $data['token_number'] = trim_checker($order_details->token_number);
        $data['random_code'] = trim_checker(isset($order_details->random_code) && $order_details->random_code?$order_details->random_code:'');
        $data['user_id'] = $this->session->userdata('user_id');
        $data['waiter_id'] = trim_checker($order_details->waiter_id);
        $data['outlet_id'] = $this->session->userdata('outlet_id');
        $data['company_id'] = $this->session->userdata('company_id');
        $data['sale_date'] = trim_checker(isset($order_details->open_invoice_date_hidden) && $order_details->open_invoice_date_hidden?$order_details->open_invoice_date_hidden:date('Y-m-d'));
        $data['date_time'] = date('Y-m-d H:i:s',strtotime($order_details->date_time));
        $data['order_time'] = date('Y-m-d H:i:s',strtotime($order_details->date_time));
        $data['paid_date_time'] = date('Y-m-d H:i:s');
        $data['order_status'] = 3;
        $data['orders_table_text'] = ($order_details->orders_table_text);
        $data['payment_method_id'] = trim_checker($order_details->payment_method_type);
        $data['paid_amount'] = trim_checker($order_details->paid_amount);
        $data['due_amount'] = trim_checker($order_details->due_amount);
        $data['zatca_value'] = trim_checker($order_details->zatca_invoice_value);

        $total_tax = 0;
        if(isset($order_details->sale_vat_objects) && $order_details->sale_vat_objects){
            foreach ($order_details->sale_vat_objects as $keys=>$val){
                $total_tax+=$val->tax_field_amount;
            }
        }
        $data['vat'] = $total_tax;
        $data['sale_vat_objects'] = json_encode($order_details->sale_vat_objects);
        $data['order_type'] = trim_checker($order_details->order_type);
        if (tipoFacturacion() == 'RD_AI') {
            // $data['numeracion'] = trim_checker($order_details->numeracion);
            $numeracion = trim_checker($order_details->numeracion);
        }

        // --- INICIO DE LA MODIFICACIÓN ---
        // Si encontramos una orden de cocina y ya tiene una factura generada,
        // copiamos sus datos a la nueva venta final.
        if ($kitchen_sale && $kitchen_sale->fe_estado === 'EXITO' && !empty($kitchen_sale->fe_cdc)) {
            $data['py_factura_id'] = $kitchen_sale->py_factura_id;
            $data['fe_estado']     = $kitchen_sale->fe_estado;
            $data['fe_cdc']        = $kitchen_sale->fe_cdc;
            $data['fe_lote_id']    = $kitchen_sale->fe_lote_id;
            $data['fe_error_log']  = $kitchen_sale->fe_error_log;
        }
        // --- FIN DE LA MODIFICACIÓN ---

        $this->db->trans_begin();
        if($sale_id>0){
            $data['modified'] = 'Yes';
            $this->db->where('id', $sale_id);
            $this->db->update('tbl_sales', $data);
    
            $this->db->delete('tbl_sales_details', array('sales_id' => $sale_id));
            $this->db->delete('tbl_sales_details_modifiers', array('sales_id' => $sale_id));
            $this->db->delete('tbl_sale_consumptions', array('sale_id' => $sale_id));
            $this->db->delete('tbl_sale_consumptions_of_menus', array('sales_id' => $sale_id));
            $this->db->delete('tbl_sale_consumptions_of_modifiers_of_menus', array('sales_id' => $sale_id));
            $this->db->delete('tbl_sale_payments', array('sale_id' => $sale_id));
            $sales_id = $sale_id;
    
            $paymentarray = array();
            $paymentarray['payment_id'] = 1;
            $paymentarray['payment_name'] = "Cash";
            $paymentarray['amount'] = $order_details->total_payable;
            $paymentarray['date_time'] = date('Y-m-d H:i:s');
            $paymentarray['sale_id'] = $sales_id;
            $paymentarray['user_id'] = $this->session->userdata('user_id');
            $paymentarray['outlet_id'] = $data['outlet_id'] ;
            $paymentarray['counter_id'] = $this->session->userdata('counter_id');
            $this->Common_model->insertInformation($paymentarray, "tbl_sale_payments");
        }else{
            $this->db->insert('tbl_sales', $data);
            $sales_id = $this->db->insert_id();
            $sale_no_update_array = array('sale_no' => $sale_no);
            $this->db->where('id', $sales_id);
            $this->db->update('tbl_sales', $sale_no_update_array);
            
            $this->db->where('sale_no', $sale_no)
                ->update('tbl_numeros', [
                    'sale_id' => NULL,
                    'sale_no' => NULL,
                    "user_id" => NULL
                ]);
                    
            $data_sale_kitchen = [
                'table_id' => '0',
                'orders_table_text' => '',
            ];
            $this->db->where('sale_no', $sale_no);
            $this->db->update('tbl_kitchen_sales', $data_sale_kitchen);

            
        }
        foreach($order_details->orders_table as $single_order_table){
            $order_table_info = array();
            $order_table_info['persons'] = $single_order_table->persons;
            $order_table_info['booking_time'] = date('Y-m-d H:i:s');
            $order_table_info['sale_id'] = $sales_id;
            $order_table_info['sale_no'] = $sale_no;
            $order_table_info['outlet_id'] = $this->session->userdata('outlet_id');
            $order_table_info['table_id'] = $single_order_table->table_id;
            $this->db->insert('tbl_orders_table',$order_table_info);
        }
        $data_sale_consumptions = array();
        $data_sale_consumptions['sale_id'] = $sales_id;
        $data_sale_consumptions['user_id'] = $this->session->userdata('user_id');
        $data_sale_consumptions['outlet_id'] = $this->session->userdata('outlet_id');
        $data_sale_consumptions['del_status'] = 'Live';
        $this->db->insert('tbl_sale_consumptions',$data_sale_consumptions);
        $sale_consumption_id = $this->db->insert_id();
    
        if($sales_id>0 && count($order_details->items)>0){
            foreach($order_details->items as $item){
                // $tmp_var_111 = isset($item->p_qty) && $item->p_qty && $item->p_qty!='undefined'?$item->p_qty:0;
                // $tmp = $item->qty-$tmp_var_111;
                $item->qty = isset($item->qty) ? sanitize_font_html($item->qty) : 0;
                $item->p_qty = isset($item->p_qty) ? sanitize_font_html($item->p_qty) : 0;
                $qty = isset($item->qty) && is_numeric($item->qty) ? (int)$item->qty : 0;
                $p_qty = isset($item->p_qty) && is_numeric($item->p_qty) ? (int)$item->p_qty : 0;
                $tmp = $qty - $p_qty;
                $tmp_var = 0;
                if($tmp>0){
                    $tmp_var = $tmp;
                }
                $food_details =  $this->Common_model->getDataById($item->food_menu_id, "tbl_food_menus");
                $item_data = array();
                $item_data['food_menu_id'] = sanitize_font_html($item->food_menu_id);
                $p_name = getParentNameOnly($food_details->parent_id);
                $item_data['menu_name'] = sanitize_font_html(isset($p_name[0]) ? $p_name[0] . (isset($food_details->name) && $food_details->name ? " " . $food_details->name : '') : (isset($food_details->name) && $food_details->name ? $food_details->name : ''));
                $item_data['qty'] = ($item->qty);
                $item_data['tmp_qty'] = ($tmp_var);
                $item_data['menu_price_without_discount'] = sanitize_font_html($item->menu_price_without_discount);
                $item_data['menu_price_with_discount'] = sanitize_font_html($item->menu_price_with_discount);
                $item_data['menu_combo_items'] = sanitize_font_html(isset($item->menu_combo_items) && $item->menu_combo_items && $item->menu_combo_items!="undefined"?$item->menu_combo_items:'');
                $item_data['is_free_item'] = sanitize_font_html($item->is_free);
                $item_data['menu_unit_price'] = sanitize_font_html($item->menu_unit_price);
                $item_data['menu_taxes'] = json_encode($item->item_vat);
                $item_data['menu_discount_value'] = sanitize_font_html($item->menu_discount_value);
                $item_data['discount_type'] = sanitize_font_html($item->discount_type);
                $item_data['menu_note'] = sanitize_font_html(isset($item->item_note) && $item->item_note?$item->item_note:'');
                $item_data['discount_amount'] = sanitize_font_html($item->item_discount_amount);
                $item_data['item_type'] = sanitize_font_html(($this->Sale_model->getItemType($item->food_menu_id)->item_type=="Bar No")?"Kitchen Item":"Bar Item");
                $item_data['cooking_status'] = ($item->item_cooking_status=="")?NULL:$item->item_cooking_status;
    
                if(isset($select_kitchen_row->id) && $select_kitchen_row->id){
                    $kitchen_data = getKitchenItemDetails($select_kitchen_row->id,$item->food_menu_id);
                    $item_data['cooking_start_time'] = isset($kitchen_data->cooking_start_time) && $kitchen_data->cooking_start_time?$kitchen_data->cooking_start_time:'0000-00-00 00:00:00';
                    $item_data['cooking_done_time'] = isset($kitchen_data->cooking_done_time) && $kitchen_data->cooking_done_time?$kitchen_data->cooking_done_time:'0000-00-00 00:00:00';
                }else{
                    $item_data['cooking_start_time'] = ($item->item_cooking_start_time=="" || $item->item_cooking_start_time=="0000-00-00 00:00:00")?'0000-00-00 00:00:00':date('Y-m-d H:i:s',strtotime($item->item_cooking_start_time));
                    $item_data['cooking_done_time'] = ($item->item_cooking_done_time=="" || $item->item_cooking_done_time=="0000-00-00 00:00:00")?'0000-00-00 00:00:00':date('Y-m-d H:i:s',strtotime($item->item_cooking_done_time));
                }
                $item_data['previous_id'] = ($item->item_previous_id=="")?0:$item->item_previous_id;
                $item_data['sales_id'] = $sales_id;
                $item_data['user_id'] = $this->session->userdata('user_id');
                $item_data['outlet_id'] = $this->session->userdata('outlet_id');
                if($order_details->customer_id!=1){
                    $item_data['loyalty_point_earn'] = ($item->qty * getLoyaltyPointByFoodMenu($item->food_menu_id,''));
                }
                $item_data['del_status'] = 'Live';
                $this->db->insert('tbl_sales_details', $item_data);
                $sales_details_id = $this->db->insert_id();
    
                if($item->item_previous_id==""){
                    $previous_id_update_array = array('previous_id' => $sales_details_id);
                    $this->db->where('id', $sales_details_id);
                    $this->db->update('tbl_sales_details', $previous_id_update_array);
                }

                if(isset($food_details->product_type) && $food_details->product_type==1){
                    $food_menu_ingredients = $this->db->query("SELECT * FROM tbl_food_menus_ingredients WHERE food_menu_id=$item->food_menu_id")->result();
                    foreach($food_menu_ingredients as $single_ingredient){
                        $inline_total = $single_ingredient->cost;
                        $data_sale_consumptions_detail = array();
                        $data_sale_consumptions_detail['ingredient_id'] = $single_ingredient->ingredient_id;
                        $data_sale_consumptions_detail['consumption'] = $item->qty*$single_ingredient->consumption;
                        $data_sale_consumptions_detail['sale_consumption_id'] = $sale_consumption_id;
                        $data_sale_consumptions_detail['sales_id'] = $sales_id;
                        $data_sale_consumptions_detail['cost'] = $inline_total;
                        $data_sale_consumptions_detail['food_menu_id'] = $item->food_menu_id;
                        $data_sale_consumptions_detail['user_id'] = $this->session->userdata('outlet_id');
                        $data_sale_consumptions_detail['outlet_id'] = $this->session->userdata('outlet_id');
                        $data_sale_consumptions_detail['del_status'] = 'Live';
                        $query = $this->db->insert('tbl_sale_consumptions_of_menus',$data_sale_consumptions_detail);
                    }
                }else if(isset($food_details->product_type) && $food_details->product_type==3){
                    $food_menu_ingredients = $this->db->query("SELECT * FROM tbl_ingredients WHERE food_id=$item->food_menu_id")->result();
                    foreach($food_menu_ingredients as $single_ingredient){
                        $inline_total = $single_ingredient->consumption_unit_cost;
                        $data_sale_consumptions_detail = array();
                        $data_sale_consumptions_detail['ingredient_id'] = $single_ingredient->id;
                        $data_sale_consumptions_detail['consumption'] = $item->qty;
                        $data_sale_consumptions_detail['sale_consumption_id'] = $sale_consumption_id;
                        $data_sale_consumptions_detail['sales_id'] = $sales_id;
                        $data_sale_consumptions_detail['cost'] = $inline_total;
                        $data_sale_consumptions_detail['food_menu_id'] = $item->food_menu_id;
                        $data_sale_consumptions_detail['user_id'] = $this->session->userdata('outlet_id');
                        $data_sale_consumptions_detail['outlet_id'] = $this->session->userdata('outlet_id');
                        $data_sale_consumptions_detail['del_status'] = 'Live';
                         $this->db->insert('tbl_sale_consumptions_of_menus',$data_sale_consumptions_detail);
                    }
                }else{
                    $combo_food_menus = $this->db->query("SELECT * FROM tbl_combo_food_menus WHERE food_menu_id=$item->food_menu_id AND del_status='Live'")->result();
                    if(isset($combo_food_menus) && $combo_food_menus){
                        foreach ($combo_food_menus as $single_combo_fm){
                            $food_menu_ingredients = $this->db->query("SELECT * FROM tbl_food_menus_ingredients WHERE food_menu_id=$single_combo_fm->added_food_menu_id")->result();
                            foreach($food_menu_ingredients as $single_ingredient){
                                $inline_total = $single_ingredient->cost*($item->qty*$single_combo_fm->quantity);
                                $data_sale_consumptions_detail = array();
                                $data_sale_consumptions_detail['ingredient_id'] = $single_ingredient->ingredient_id;
                                $data_sale_consumptions_detail['consumption'] = ($item->qty*$single_combo_fm->quantity)*$single_ingredient->consumption;
                                $data_sale_consumptions_detail['sale_consumption_id'] = $sale_consumption_id;
                                $data_sale_consumptions_detail['sales_id'] = $sales_id;
                                $data_sale_consumptions_detail['cost'] = $inline_total;
                                $data_sale_consumptions_detail['food_menu_id'] = $item->food_menu_id;
                                $data_sale_consumptions_detail['user_id'] = $this->session->userdata('outlet_id');
                                $data_sale_consumptions_detail['outlet_id'] = $this->session->userdata('outlet_id');
                                $data_sale_consumptions_detail['del_status'] = 'Live';
                                $this->db->insert('tbl_sale_consumptions_of_menus',$data_sale_consumptions_detail);
                            }
                        }

                    }
                }

                // Modifiers
                $modifier_id_array = isset($item->modifiers_id) && ($item->modifiers_id!="")?explode(",",$item->modifiers_id):null;
                $modifiers_mul_id_array = isset($item->modifiers_mul_id) && ($item->modifiers_mul_id!="")?explode(",",$item->modifiers_mul_id):null;
                $modifier_price_array = isset($item->modifiers_price) && ($item->modifiers_price!="")?explode(",",$item->modifiers_price):null;
                $modifier_vat_array = (isset($item->modifier_vat) && $item->modifier_vat!="")?explode("|||",$item->modifier_vat):null;
                if(!empty($modifier_id_array)>0){
                    $i = 0;
                    foreach($modifier_id_array as $key1=>$single_modifier_id){
                        $modifiers_mul_id_array_value = isset($modifiers_mul_id_array[$key1]) && $modifiers_mul_id_array[$key1]?explode('_',$modifiers_mul_id_array[$key1]):'';
                        $modifier_data = array();
                        $modifier_data['modifier_id'] = sanitize_font_html($single_modifier_id);
                        $modifier_data['modifier_price'] = sanitize_font_html($modifier_price_array[$i]);
                        $modifier_data['food_menu_id'] = sanitize_font_html($item->food_menu_id);
                        $modifier_data['sales_id'] = $sales_id;
                        $modifier_data['sales_details_id'] = $sales_details_id;
                        $modifier_data['menu_taxes'] = isset($modifier_vat_array[$key1]) && $modifier_vat_array[$key1]?sanitize_font_html($modifier_vat_array[$key1]):'';
                        $modifier_data['user_id'] = $this->session->userdata('user_id');
                        $modifier_data['outlet_id'] = $this->session->userdata('outlet_id');
                        $modifier_data['customer_id'] =sanitize_font_html($order_details->customer_id);
    
                        $this->db->insert('tbl_sales_details_modifiers', $modifier_data);
    
                        // ... resto de la lógica para ingredientes de modificadores ...
                        // $modifier_ingredients = $this->db->query("SELECT * FROM tbl_modifier_ingredients WHERE modifier_id=$single_modifier_id")->result();
                        
                        $clean_modifier_id = sanitize_font_html($single_modifier_id);
                        $modifier_ingredients = $this->db->query("SELECT * FROM tbl_modifier_ingredients WHERE modifier_id=" . intval($clean_modifier_id))->result();
                        foreach($modifier_ingredients as $single_ingredient){
                            $data_sale_consumptions_detail = array();
                            $data_sale_consumptions_detail['ingredient_id'] = $single_ingredient->ingredient_id;
                            $data_sale_consumptions_detail['consumption'] = $item->qty*$single_ingredient->consumption;
                            $data_sale_consumptions_detail['sale_consumption_id'] = $sale_consumption_id;
                            $data_sale_consumptions_detail['sales_id'] = $sales_id;
                            $data_sale_consumptions_detail['food_menu_id'] = $item->food_menu_id;
                            $data_sale_consumptions_detail['user_id'] = $this->session->userdata('user_id');
                            $data_sale_consumptions_detail['outlet_id'] = $this->session->userdata('outlet_id');
                            $data_sale_consumptions_detail['del_status'] = 'Live';
                            $this->db->insert('tbl_sale_consumptions_of_modifiers_of_menus',$data_sale_consumptions_detail);
                        }
                        $i++;
                    }
                }
            }
        }

        if(!$sale_id){
            $this->db->delete('tbl_sale_payments', array('sale_id' => $sales_id));
        } 
        //put payment details
        if(isset($order_details->payment_object)){
            $payment_details = json_decode($order_details->payment_object);
        
            // Si por alguna razón aún es string, decodifica de nuevo (caso datos viejos)
            if (is_string($payment_details)) {
                $payment_details = json_decode($payment_details);
            }
            // if(isset($order_details->split_sale_id) && $order_details->split_sale_id){
            //     $payment_details = json_decode(($order_details->payment_object));
            // }else{
            //     $payment_details = json_decode(json_decode($order_details->payment_object));
            // }

            $currency_type = trim_checker($order_details->is_multi_currency);
            $multi_currency = trim_checker($order_details->multi_currency);
            $multi_currency_rate = trim_checker($order_details->multi_currency_rate);
            $multi_currency_amount = trim_checker($order_details->multi_currency_amount);
           

            if($currency_type==1){
                $check_existing_payment = getPaymentInfo($sales_id,1);
                if(!$check_existing_payment){
                    $data = array();
                    $data['payment_id'] = 1;
                    $data['payment_name'] = "Cash";
                    $data['amount'] = $multi_currency_amount;
                    $data['multi_currency'] = $multi_currency;
                    $data['multi_currency_rate'] = $multi_currency_rate;
                    $data['currency_type'] = $currency_type;
                    $data['date_time'] = date('Y-m-d H:i:s'); //date('Y-m-d H:i:s',strtotime($order_details->date_time));
                    $data['sale_id'] = $sales_id;
                    $data['counter_id'] = $this->session->userdata('counter_id');
                    $data['user_id'] = $this->session->userdata('user_id');
                    $data['outlet_id'] = $this->session->userdata('outlet_id');
                    $this->Common_model->insertInformation($data, "tbl_sale_payments");
                }
            }else{
                foreach ($payment_details as $value){
                    $check_existing_payment = getPaymentInfo($sales_id,$value->payment_id);
                    if(!$check_existing_payment){
                        $data = array();
                        $data['payment_id'] = $value->payment_id;
                        $data['payment_name'] = $value->payment_name;
                        if($value->payment_id==5){
                            $data['usage_point'] = $value->usage_point;
                            $previous_id_update_array = array('loyalty_point_earn' => 0);
                            $this->db->where('sales_id', $sales_id);
                            $this->db->update('tbl_sales_details', $previous_id_update_array);
                        }
                        $data['amount'] = $value->amount;
                        $data['date_time'] = date('Y-m-d H:i:s'); //date('Y-m-d H:i:s',strtotime($order_details->date_time));
                        $data['sale_id'] = $sales_id;
                        $data['counter_id'] = $this->session->userdata('counter_id');
                        $data['user_id'] = $this->session->userdata('user_id');
                        $data['outlet_id'] = $this->session->userdata('outlet_id');
                        $this->Common_model->insertInformation($data, "tbl_sale_payments");
                    }
                  
                }
            }
        }

        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $response_data['message'] = 'Error al sincronizar la venta en la base de datos.';
        } else {
            // $this->db->where('sale_no', $sale_no)
            //     ->update('tbl_numeros', [
            //         'sale_id' => NULL,
            //         'sale_no' => NULL,
            //         "user_id" => NULL
            //     ]);
            $this->db->trans_commit();

            $response_data['sale_id_offline'] = $sale_id_offline;
            $response_data['status'] = 'success';
            $response_data['message'] = 'Venta sincronizada correctamente.';

            // ***************************** Insertar Aqui codigo de Facturas usando $sales_id ********************//
            if (tipoFacturacion() == 'RD_AI') {
                nueva_factura_venta($sales_id,$numeracion);
            }

            if (tipoFacturacion() == 'Py_FE') {
                // // Llamamos a la función refactorizada y guardamos su resultado
                // $fe_result = $this->generar_factura_electronica($sales_id);
                // $response_data['factura_electronica'] = $fe_result;
                
                // Obtenemos la venta recién creada/actualizada
                $final_sale = $this->db->where('id', $sales_id)->get('tbl_sales')->row();

                // Solo intentamos facturar si AÚN NO tiene datos de una factura previa.
                if (empty($final_sale->fe_cdc)) {
                    $final_sale_details = $this->db->where('sales_id', $sales_id)->get('tbl_sales_details')->result();
                    
                    // Llamamos a la función universal
                    $fe_result = $this->generar_factura_electronica_universal($final_sale, $final_sale_details, 'tbl_sales');
                    $response_data['factura_electronica'] = $fe_result;

                } else {
                    // Si ya tenía datos, simplemente informamos que se usó la factura existente.
                    $response_data['factura_electronica'] = [
                        'status'  => 'info',
                        'message' => 'Se ha utilizado la factura electrónica generada previamente.',
                        'cdc'     => $final_sale->fe_cdc
                    ];
                }
                
            }
            // ***************************** Fin del código ********************************************************//
        }
        
        // --- 5. Enviar la respuesta JSON final ---
        $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response_data));
    }

     /**
     * add sale by ajax
     * @access public
     * @return int
     * @param no
     */
    public function set_as_running_order(){
        $sale_no = $this->input->post('sale_no');
        $sale_details = getKitchenSaleDetailsBySaleNoWithDeleted($sale_no);

        $status = $this->input->post('status');
        $data = array();
        $data['is_self_order'] = "No";
        $data['is_online_order'] = "No";
        $data['is_accept'] = 1;
        $data['future_sale_status'] = $status;
        $data['self_order_status'] = "Approved";
        $this->db->where('id', $sale_details->id);
        $this->db->update('tbl_kitchen_sales', $data);

        
        $sale_object = $this->get_all_information_of_a_sale($sale_no);
        echo json_encode($sale_object);


    }
     /**
     * get new orders
     * @access public
     * @return object
     * @param no
     */
    public function get_new_orders_ajax(){
        $data1 = $this->get_new_orders();
        echo json_encode($data1);
    }
     /**
     * get new orders
     * @access public
     * @return object
     * @param no
     */
    public function get_new_orders(){
        $outlet_id = $this->session->userdata('outlet_id');
        $data1 = $this->Sale_model->getNewOrders($outlet_id);
        $i = 0;
        for($i;$i<count($data1);$i++){
            $data1[$i]->total_kitchen_type_items = $this->Sale_model->get_total_kitchen_type_items($data1[$i]->sale_id);
            $data1[$i]->total_kitchen_type_done_items = $this->Sale_model->get_total_kitchen_type_done_items($data1[$i]->sale_id);
            $data1[$i]->total_kitchen_type_started_cooking_items = $this->Sale_model->get_total_kitchen_type_started_cooking_items($data1[$i]->sale_id);
            $data1[$i]->tables_booked = $this->Sale_model->get_all_tables_of_a_sale_items($data1[$i]->sale_id);

            $to_time = strtotime(date('Y-m-d H:i:s'));
            $from_time = strtotime($data1[$i]->date_time);
            $minutes = floor(abs($to_time - $from_time) / 60);
            $seconds = abs($to_time - $from_time) % 60;

            $data1[$i]->minute_difference = str_pad(floor($minutes), 2, "0", STR_PAD_LEFT);
            $data1[$i]->second_difference = str_pad(floor($seconds), 2, "0", STR_PAD_LEFT);
        }
        return $data1;
    }
     /**
     * get all tables with new status
     * @access public
     * @return object
     * @param no
     */
    public function get_all_tables_with_new_status_ajax(){
        $outlet_id = $this->session->userdata('outlet_id');
        $tables = $this->Sale_model->getTablesByOutletId($outlet_id);
        $data1 = new \stdClass();
        $data1->table_details = $this->getTablesDetails($tables);
        $data1->table_availability = $this->Sale_model->getTableAvailability($outlet_id);
        echo json_encode($data1);
    }
     /**
     * get all information of a sale ajax
     * @access public
     * @return object
     * @param no
     */
    public function get_all_information_of_a_sale_ajax(){
        $sale_no = $this->input->post('sale_no');
        $sale_object = $this->get_all_information_of_a_sale($sale_no);
        echo json_encode($sale_object);
    }
     /**
     * get all information of a sale ajax
     * @access public
     * @return object
     * @param no
     */
    public function get_all_information_of_a_sale_ajax_modify(){
        $sales_id = $this->input->post('sale_id');
        $sale_object = $this->get_all_information_of_a_sale_modify($sales_id);
        echo json_encode($sale_object);
    }
     /**
     * get all information of a sale by table id
     * @access public
     * @return object
     * @param no
     */
    public function get_all_information_of_a_sale_by_table_id_ajax()
    {
        $table_id = $this->input->post('table_id');
        $sale_info = $this->Sale_model->get_new_sale_by_table_id($table_id);
        $sale_id = $sale_info->id;
        $sale_object = $this->get_all_information_of_a_sale($sale_info->sale_no);
        echo json_encode($sale_object);
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
     /**
     * get all information of a sale
     * @access public
     * @return object
     * @param int
     */
    public function get_all_information_of_a_sale_modify($sales_id){
        $sales_information = $this->Sale_model->getSaleBySaleId($sales_id);
        //     echo 'get_all_information_of_a_sale_modify';
        //    echo '<pre>';
        //    var_dump($sales_information); 
        //    echo '<pre>';
       
        $sales_information[0]->selected_number = isset($sales_information[0]->number_slot) && $sales_information[0]->number_slot?$sales_information[0]->number_slot:'';
        $sales_information[0]->selected_number_name = isset($sales_information[0]->number_slot_name) && $sales_information[0]->number_slot_name?$sales_information[0]->number_slot_name:'';
        $sales_information[0]->sub_total = getAmtP(isset($sales_information[0]->sub_total) && $sales_information[0]->sub_total?$sales_information[0]->sub_total:0);
        $sales_information[0]->paid_amount = getAmtP(isset($sales_information[0]->paid_amount) && $sales_information[0]->paid_amount?$sales_information[0]->paid_amount:0);
        $sales_information[0]->due_amount = getAmtP(isset($sales_information[0]->due_amount) && $sales_information[0]->due_amount?$sales_information[0]->due_amount:0);
        $sales_information[0]->vat = getAmtP(isset($sales_information[0]->vat) && $sales_information[0]->vat?$sales_information[0]->vat:0);
        $sales_information[0]->total_payable = getAmtP(isset($sales_information[0]->total_payable) && $sales_information[0]->total_payable?$sales_information[0]->total_payable:0);
        $sales_information[0]->total_item_discount_amount = getAmtP(isset($sales_information[0]->total_item_discount_amount) && $sales_information[0]->total_item_discount_amount?$sales_information[0]->total_item_discount_amount:0);
        $sales_information[0]->sub_total_with_discount = getAmtP(isset($sales_information[0]->sub_total_with_discount) && $sales_information[0]->sub_total_with_discount?$sales_information[0]->sub_total_with_discount:0);
        $sales_information[0]->sub_total_discount_amount = getAmtP(isset($sales_information[0]->sub_total_discount_amount) && $sales_information[0]->sub_total_discount_amount?$sales_information[0]->sub_total_discount_amount:0);
        $sales_information[0]->total_discount_amount = getAmtP(isset($sales_information[0]->total_discount_amount) && $sales_information[0]->total_discount_amount?$sales_information[0]->total_discount_amount:0);
        $sales_information[0]->delivery_charge = (isset($sales_information[0]->delivery_charge) && $sales_information[0]->delivery_charge?$sales_information[0]->delivery_charge:0);
        $this_value = $sales_information[0]->sub_total_discount_value;
        $disc_fields = explode('%',$this_value);
        $discP = isset($disc_fields[1]) && $disc_fields[1]?$disc_fields[1]:'';
          if ($discP == "") {
          } else {
              $sales_information[0]->sub_total_discount_value = getAmtP(isset($sales_information[0]->sub_total_discount_value) && $sales_information[0]->sub_total_discount_value?$sales_information[0]->sub_total_discount_value:0);
          }
          
        $items_by_sales_id = $this->Sale_model->getAllItemsFromSalesDetailBySalesIdModify($sales_id);
        
        foreach($items_by_sales_id as $single_item_by_sale_id){
            $modifier_information = $this->Sale_model->getModifiersBySaleAndSaleDetailsId($sales_id,$single_item_by_sale_id->sales_details_id);
            $single_item_by_sale_id->modifiers = $modifier_information;
            $free_items = $this->Sale_model->getAllItemsFromSalesDetailBySalesIdModifyChild($single_item_by_sale_id->id,$sales_id);
            $single_item_by_sale_id->free_items = $free_items;
        }
        
        $sales_details_objects = $items_by_sales_id;
        if (isset($sales_details_objects[0])) {
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
                $sales_details_objects[0]->menu_discount_value = getAmtP(isset($sales_details_objects[0]->menu_discount_value) && $sales_information[0]->menu_discount_value?$sales_details_objects[0]->menu_discount_value:0);
            }
        }
        // $sales_details_objects[0]->menu_price_without_discount = getAmtP(isset($sales_details_objects[0]->menu_price_without_discount) && $sales_details_objects[0]->menu_price_without_discount?$sales_details_objects[0]->menu_price_without_discount:0);
        // $sales_details_objects[0]->menu_price_with_discount = getAmtP(isset($sales_details_objects[0]->menu_price_with_discount) && $sales_details_objects[0]->menu_price_with_discount?$sales_details_objects[0]->menu_price_with_discount:0);
        // $sales_details_objects[0]->menu_unit_price = getAmtP(isset($sales_details_objects[0]->menu_unit_price) && $sales_details_objects[0]->menu_unit_price?$sales_details_objects[0]->menu_unit_price:0);
        // $sales_details_objects[0]->menu_vat_percentage = getAmtP(isset($sales_details_objects[0]->menu_vat_percentage) && $sales_details_objects[0]->menu_vat_percentage?$sales_details_objects[0]->menu_vat_percentage:0);
        // $sales_details_objects[0]->discount_amount = getAmtP(isset($sales_details_objects[0]->discount_amount) && $sales_details_objects[0]->discount_amount?$sales_details_objects[0]->discount_amount:0);

        // $this_value = $sales_details_objects[0]->menu_discount_value;
        // $disc_fields = explode('%',$this_value);
        // $discP = isset($disc_fields[1]) && $disc_fields[1]?$disc_fields[1]:'';
        // if ($discP == "") {
        // } else {
        //     $sales_details_objects[0]->menu_discount_value = getAmtP(isset($sales_details_objects[0]->menu_discount_value) && $sales_information[0]->menu_discount_value?$sales_details_objects[0]->menu_discount_value:0);
        // }

        $sale_object = $sales_information[0];
        $sale_object->items = $sales_details_objects;
        $sale_object->tables_booked = $this->Sale_model->get_all_tables_of_a_sale_items($sales_id);
        return $sale_object;
    } 
     /**
     * print invoice
     * @access public
     * @return void
     * @param int
     */
    public function print_invoice($sale_id){
        
        $data['sale_object'] = $this->get_all_information_of_a_sale_modify($sale_id);
        
        $inv_qr_code_enable_status = $this->session->userdata('inv_qr_code_enable_status');
        $data['inv_qr_code_enable_status'] = $inv_qr_code_enable_status;
        
        $print_format = $this->session->userdata('print_format');
    
        //remove all old qrcode
        removeQrCode();
        //generate qrcode
        $url_patient = base_url().'invoice/'.$data['sale_object']->random_code;
        $rand_id = $sale_id;
        $this->load->library('phpqrcode/qrlib');
        $qr_codes_path = "qr_code/";
        $folder = $qr_codes_path;
        $file_name1 = $folder.$rand_id.".png";
        $file_name = $file_name1;
        QRcode::png($url_patient,$file_name,'',4,1);
        if($print_format=="80mm"){
            $this->load->view('sale/print_invoice', $data);
        }else{
            $this->load->view('sale/print_invoice_56mm', $data);
        }
    }
      
     /**
     * print invoice
     * @access public
     * @return void
     * @param int
     */
    public function print_bill($sale_no){
        
        // $sale_details = getSaleDetails($sale_no);
        // if (empty($sale_details)) {
        //     $sale_id = '';
        //     $sale_info = $this->get_all_information_of_a_sale($sale_no);
        //     $payments = [];
        // } else {
        //     $sale_id = $sale_details->id;
        //     $sale_info = $this->get_all_information_of_a_sale_modify($sale_id);
        //     $payments = salePaymentDetails($sale_id, $this->session->userdata('outlet_id'));
        // }
        $data['sale_object'] = $this->get_all_information_of_a_sale($sale_no);
        
        $inv_qr_code_enable_status = $this->session->userdata('inv_qr_code_enable_status');
        $data['inv_qr_code_enable_status'] = $inv_qr_code_enable_status;
        
        $print_format = $this->session->userdata('print_format');
    
        // //remove all old qrcode
        // removeQrCode();
        // //generate qrcode
        // $url_patient = base_url().'invoice/'.$data['sale_object']->random_code;
        // $rand_id = $sale_id;
        // $this->load->library('phpqrcode/qrlib');
        // $qr_codes_path = "qr_code/";
        // $folder = $qr_codes_path;
        // $file_name1 = $folder.$rand_id.".png";
        // $file_name = $file_name1;
        // QRcode::png($url_patient,$file_name,'',4,1);
        $data['qr'] = false; // Desactivado temporalmente
        $data['bill'] = true; 
        if($print_format=="80mm"){
            $this->load->view('sale/print_invoice', $data);
        }else{
            $this->load->view('sale/print_invoice_56mm', $data);
        }
    }

    public function print_ticket($sale_no){
        $sale_info = $this->Sale_model->getSaleBySaleNo($sale_no);
        if (isset($sale_info) && isset($sale_info->id)){
            $sale_id = $sale_info->id;
            
            $this->print_invoice($sale_id);
        }
    }

     /**
     * print bill
     * @access public
     * @return void
     * @param int
     */
    public function getBillDetails(){
        $sale_no = escape_output($_POST['sale_no']);
        $sale_object = $this->get_all_information_of_a_sale($sale_no);
        $order_type = '';
        if($sale_object->order_type == 1){
            $order_type = 'A';
        }elseif($sale_object->order_type == 2){
            $order_type = 'B';
        }elseif($sale_object->order_type == 3){
            $order_type = 'C';
        }
        $time = (date('H:i',strtotime($sale_object->order_time)));
        $tables = '';
        if(isset($sale_object->orders_table_text) && $sale_object->orders_table_text):
            $tables .= $sale_object->orders_table_text;
            endif;
        $html = '<header>';
             $invoice_logo = $this->session->userdata('invoice_logo');
              if($invoice_logo):
                $html.='<img src="'.base_url().'images/'.$invoice_logo.'">';
              endif;

              $html.='<h3 class="title">'.($this->session->userdata('outlet_name')).'</h3>
                    <p>'.lang('Bill_No').': <span id="b_bill_no">'.($order_type.' '.$sale_object->sale_no).'</span></p>
                </header>
                <ul class="simple-content">
                    <li>'.lang('date').': <span id="b_bill_date">'.(date($this->session->userdata('date_format'), strtotime($sale_object->sale_date))).' '.$time.' </span></li>
                    <li>'.lang('Sales_Associate').': <span id="b_bill_creator">'.($sale_object->user_name).'</span></li>
                    <li>'.lang('customer').': <b><span id="b_bill_customer">'.("$sale_object->customer_name").'</span></b></li>';
                     if(isset($sale_object->tables_booked) && $sale_object->tables_booked):
                         $html .='<li>'.lang('table').': <b><span id="b_bill_customer">'.$tables.'</span></b></li>';
                         endif;

                $html .='</ul>
                <ul class="main-content-list">';

                if (isset($sale_object->items)) {
                    $i = 1;
                    $totalItems = 0;
                    foreach ($sale_object->items as $row) {
                        $totalItems += $row->qty;
                        $menu_unit_price = getAmtP($row->menu_unit_price);
                        $html .= '<li>
                                <span># '.($i++).': '.$row->menu_name.' '.$row->qty.' X '.$menu_unit_price.'</span>
                                <span>'.(getAmt($row->menu_price_without_discount)).'</span>
                                </li>';
                    }
                }

                if(count($row->modifiers)){
                    $l = 1;
                    $html_modifier = '';
                    $modifier_price = 0;
                    foreach($row->modifiers as $modifier){
                        if($l==count($row->modifiers)){
                            $html_modifier .= escape_output($modifier->name);
                        }else{
                            $html_modifier .= escape_output($modifier->name).',';
                        }
                        $modifier_price+=$modifier->modifier_price;
                        $l++;
                    }
                    $html .= '<li>
                                <span>'.lang('modifier').' : '.$html_modifier.'</span>
                                <span>'.(getAmt($modifier_price)).'</span>
                                </li>';
                }
        $html .= '<li>
                        <span><b>'.lang('Total_Item_s').': <span id="b_bill_total_item">'.$totalItems.'</span></b></span>
                        <span></span>
                    </li>
                    <li>
                        <span>'.lang('sub_total').'</span>
                        <span><b><span id="b_bill_subtotal">'.(getAmt($sale_object->sub_total)).'</span></b></span>
                    </li>
                    <li>
                        <span>'.lang('grand_total').'</span>
                        <span><b><span id="b_bill_gtotal">'.(getAmt($sale_object->total_payable)).'</span></b></span>
                    </li>
                    <li>
                        <span>'.lang('total_payable').'</span>
                        <span><span id="b_bill_total_payable">'.(getAmt($sale_object->total_payable)).'</span></span>
                    </li>
                </ul>';

              echo json_encode($html);

    }
     /**
     * get new hold number
     * @access public
     * @return void
     * @param no
     */
    public function get_new_hold_number_ajax(){
        $number_of_holds_of_this_user_and_outlet = $this->get_current_hold();
        $number_of_holds_of_this_user_and_outlet++;
        /*This variable could not be escaped because this is html content*/
        echo $number_of_holds_of_this_user_and_outlet;
    }

    public function get_current_hold(){
        $outlet_id = $this->session->userdata('outlet_id');
        $user_id = $this->session->userdata('user_id');
        $number_of_holds = $this->Sale_model->getNumberOfHoldsByUserAndOutletId($outlet_id,$user_id);
        return $number_of_holds;
    }
     /**
     * add hold by ajax
     * @access public
     * @return void
     * @param int
     */
    public function add_hold_by_ajax()
    {
        $order_details = json_decode(json_decode($this->input->post('order')));
        $hold_number = trim_checker($this->input->post('hold_number'));
        $data = array();
        $data['customer_id'] = trim_checker($order_details->customer_id);
        $data['delivery_partner_id'] = trim_checker($order_details->delivery_partner_id);
        $data['total_items'] = trim_checker($order_details->total_items_in_cart);
        $data['sub_total'] = trim_checker($order_details->sub_total);
        $data['charge_type'] = trim_checker($order_details->charge_type);
        $data['table_id'] = trim_checker($order_details->selected_table);
        $data['total_payable'] = trim_checker($order_details->total_payable);
        $data['total_item_discount_amount'] = trim_checker($order_details->total_item_discount_amount);
        $data['sub_total_with_discount'] = trim_checker($order_details->sub_total_with_discount);
        $data['sub_total_discount_amount'] = trim_checker($order_details->sub_total_discount_amount);
        $data['total_discount_amount'] = trim_checker($order_details->total_discount_amount);
        $data['delivery_charge'] = trim_checker($order_details->delivery_charge);
        $data['delivery_charge_actual_charge'] = trim_checker($order_details->delivery_charge_actual_charge);
        $data['tips_amount'] = trim_checker($order_details->tips_amount);
        $data['tips_amount_actual_charge'] = trim_checker($order_details->tips_amount_actual_charge);

        $data['sub_total_discount_value'] = trim_checker($order_details->sub_total_discount_value);
        $data['sub_total_discount_type'] = trim_checker($order_details->sub_total_discount_type);
        $data['user_id'] = $this->session->userdata('user_id');
        $data['waiter_id'] = trim_checker($order_details->waiter_id);
        $data['outlet_id'] = $this->session->userdata('outlet_id');
        $data['sale_date'] = trim_checker(isset($order_details->open_invoice_date_hidden) && $order_details->open_invoice_date_hidden?$order_details->open_invoice_date_hidden:date('Y-m-d'));
        $data['sale_time'] = date('Y-m-d h:i A');
        $data['order_status'] = trim_checker($order_details->order_status);

        $total_tax = 0;
        if(isset($order_details->sale_vat_objects) && $order_details->sale_vat_objects){
            foreach ($order_details->sale_vat_objects as $keys=>$val){
                $total_tax+=$val->tax_field_amount;
            }
        }
        $data['vat'] = $total_tax;

        $data['sale_vat_objects'] = json_encode($order_details->sale_vat_objects);
        $data['order_type'] = trim_checker($order_details->order_type);
        if($hold_number===0 || $hold_number===""){
            $current_hold_order = $this->get_current_hold();
            echo "current hold".$current_hold_order."<br/>";
            $hold_number = $current_hold_order+1;
        }
        $data['hold_no'] = $hold_number;
        $query = $this->db->insert('tbl_holds', $data);
        $holds_id = $this->db->insert_id();
        if($holds_id>0 && count($order_details->items)>0){
            foreach($order_details->items as $item){
                $item_data = array();
                $item_data['food_menu_id'] = $item->item_id;
                $item_data['menu_name'] = $item->item_name;
                $item_data['qty'] = $item->item_quantity;
                $item_data['menu_price_without_discount'] = $item->item_price_without_discount;
                $item_data['menu_price_with_discount'] = $item->item_price_with_discount;
                $item_data['menu_unit_price'] = $item->item_unit_price;
                $item_data['menu_taxes'] = json_encode($item->item_vat);
                $item_data['menu_discount_value'] = $item->item_discount;
                $item_data['discount_type'] = $item->discount_type;
                $item_data['menu_note'] = $item->item_note;
                $item_data['discount_amount'] = $item->item_discount_amount;
                $item_data['holds_id'] = $holds_id;
                $item_data['user_id'] = $this->session->userdata('user_id');
                $item_data['outlet_id'] = $this->session->userdata('outlet_id');
                $item_data['del_status'] = 'Live';
                $query = $this->db->insert('tbl_holds_details', $item_data);
                $holds_details_id = $this->db->insert_id();

                $modifier_id_array = ($item->modifiers_id!="")?explode(",",$item->modifiers_id):null;
                $modifier_price_array = ($item->modifiers_price!="")?explode(",",$item->modifiers_price):null;
                $modifier_vat_array = ($item->modifier_vat!="")?explode("|||",$item->modifier_vat):null;

                if(!empty($modifier_id_array)>0){
                    $i = 0;
                    foreach($modifier_id_array as $key1=>$single_modifier_id){
                        $modifier_data = array();
                        $modifier_data['modifier_id'] =$single_modifier_id;
                        $modifier_data['modifier_price'] = $modifier_price_array[$i];
                        $modifier_data['food_menu_id'] = $item->item_id;
                        $modifier_data['holds_id'] = $holds_id;
                        $modifier_data['holds_details_id'] = $holds_details_id;
                        $modifier_data['menu_taxes'] = isset($modifier_vat_array[$key1]) && $modifier_vat_array[$key1]?$modifier_vat_array[$key1]:'';
                        $modifier_data['user_id'] = $this->session->userdata('user_id');
                        $modifier_data['outlet_id'] = $this->session->userdata('outlet_id');
                        $modifier_data['customer_id'] =$order_details->customer_id;
                        $query = $this->db->insert('tbl_holds_details_modifiers', $modifier_data);

                        $i++;
                    }
                }
            }
            foreach($order_details->orders_table as $single_order_table){
                $order_table_info = array();
                $order_table_info['persons'] = $single_order_table->persons;
                $order_table_info['booking_time'] = date('Y-m-d H:i:s');
                $order_table_info['hold_id'] = $holds_id;
                $order_table_info['hold_no'] = $hold_number;
                $order_table_info['outlet_id'] = $this->session->userdata('outlet_id');
                $order_table_info['table_id'] = $single_order_table->table_id;
                $this->db->insert('tbl_holds_table',$order_table_info);
            }
        }

        echo escape_output($holds_id);
    }
     /**
     * get all holds ajax
     * @access public
     * @return object
     * @param no
     */
    public function get_all_holds_ajax(){
        $outlet_id = $this->session->userdata('outlet_id');
        $user_id = $this->session->userdata('user_id');
        $holds_information = $this->Sale_model->getHoldsByOutletAndUserId($outlet_id,$user_id);
        foreach($holds_information as $key=>$single_hold_information){
            $holds_information[$key]->tables_booked = $this->Sale_model->get_all_tables_of_a_hold_items($single_hold_information->id);
        }
        echo json_encode($holds_information);
    }
     /**
     * get last 10 sales ajax
     * @access public
     * @return object
     * @param no
     */
    public function get_last_10_sales_ajax(){
        $outlet_id = $this->session->userdata('outlet_id');
        $sales_information = $this->Sale_model->getLastTenSalesByOutletAndUserId($outlet_id);
        foreach($sales_information as $single_sale_information){
            $single_sale_information->tables_booked = $this->Sale_model->get_all_tables_of_a_last_sale($single_sale_information->id);
        }
        echo json_encode($sales_information);
    }

    public function get_last_10_future_sales_ajax(){
        $outlet_id = $this->session->userdata('outlet_id');
        $sales_information = $this->Sale_model->future_sales($outlet_id);
        foreach($sales_information as $single_sale_information){
            $single_sale_information->tables_booked = $this->Sale_model->get_all_tables_of_a_last_sale($single_sale_information->id);
        }
        echo json_encode($sales_information);
    }
    public function get_last_10_self_order_sales_ajax(){
        $outlet_id = $this->session->userdata('outlet_id');
        $sales_information = $this->Sale_model->self_order_sales($outlet_id);
        if(isset($sales_information) && $sales_information){
            foreach($sales_information as $single_sale_information){
                $single_sale_information->tables_booked = $this->Sale_model->get_all_tables_of_a_last_sale($single_sale_information->id);
            }
        }

        echo json_encode($sales_information);
    }
    public function get_last_10_self_order_sales_ajax_admin(){
        $outlet_id = $this->session->userdata('outlet_id');
        $sales_self = $this->Sale_model->self_order_sales_admin($outlet_id);
        $sales_online = $this->Sale_model->online_order_sales_admin($outlet_id);
        $return_data['self_orders'] = $sales_self;
        $return_data['online_orders'] = $sales_online;
        echo json_encode($return_data);
    }
    public function set_as_running_order_decline(){
        $sale_no = $this->input->post('sale_no');
        $status = $this->input->post('status');

        $data = array();
        $data['is_self_order'] = "No";
        $data['is_online_order'] = "No";
        $data['future_sale_status'] = $status;
        $data['self_order_status'] = "Decline";
        $this->db->where('sale_no', $sale_no);
        $this->db->update('tbl_kitchen_sales', $data);
        echo json_encode("success");
    }
     /**
     * get single hold info by ajax
     * @access public
     * @return object
     * @param no
     */
    public function get_single_hold_info_by_ajax()
    {
        $hold_id = $this->input->post('hold_id');
        $hold_information = $this->Sale_model->get_hold_info_by_hold_id($hold_id);
        $items_by_holds_id = $this->Sale_model->getAllItemsFromHoldsDetailByHoldsId($hold_id);
        foreach($items_by_holds_id as $single_item_by_hold_id){
            $modifier_information = $this->Sale_model->getModifiersByHoldAndHoldsDetailsId($hold_id,$single_item_by_hold_id->holds_details_id);
            $single_item_by_hold_id->modifiers = $modifier_information;
        }
        $holds_details_objects = $items_by_holds_id;
        $hold_object = $hold_information[0];
        $hold_object->items = $holds_details_objects;
        $hold_object->tables_booked = json_encode($this->Sale_model->get_all_tables_of_a_hold_items($hold_id));
        echo json_encode($hold_object);

    }
     /**
     * delete all information of hold by ajax
     * @access public
     * @return object
     * @param no
     */
    public function delete_all_information_of_hold_by_ajax()
    {
        $hold_id = $this->input->post('hold_id');
        $this->db->delete('tbl_holds', array('id' => $hold_id));
        $this->db->delete('tbl_holds_details', array('holds_id' => $hold_id));
        $this->db->delete('tbl_holds_details_modifiers', array('holds_id' => $hold_id));
    }
     /**
     * check customer address ajax
     * @access public
     * @return object
     * @param no
     */
    public function check_customer_address_ajax()
    {
        $customer_id = $this->input->post('customer_id');
        $customer_info = $this->Sale_model->getCustomerInfoById($customer_id);
        echo json_encode($customer_info);
    }
     /**
     * get customer ajax
     * @access public
     * @return object
     * @param no
     */
    public function get_customer_ajax()
    {
        $customer_id = $this->input->post('customer_id');
        $customer_info = $this->Sale_model->getCustomerInfoById($customer_id);
        $customer_address = $this->Common_model->getAllByCustomId($customer_id,"customer_id","tbl_customer_address",$order='');
        $html = '';
        foreach ($customer_address as $value){
            $checked = '';
            if($value->is_active==1){
                $checked = "checked";
            }
            $html.='<tr><td><label class="pointer_class"><input type="radio" '.$checked.' data-id="'.$value->id.'" class="radio_class customer_del_address search_result_address" data-value="'.$value->address.'" name="customer_del_address"> '.$value->address.'</label></td></tr>';
        }
        $checked = '';
        $is_new_address = "No";
        if($html==''){
            $checked = "checked";
            $is_new_address = "Yes";
        }
        $html.='<tr><td><label class="pointer_class"><input type="radio" '.$checked.' data-id=="" class="radio_class customer_del_address search_result_address" data-value="New" name="customer_del_address"> New</label></td></tr>';

        $customer_info->is_new_address = $is_new_address;
        $customer_info->addresses = $html;
        echo json_encode($customer_info);
    }
     /**
     * cancel particular order
     * @access public
     * @return void
     * @param no
     */
    public function cancel_particular_order_ajax()
    {
        $sale_id = $this->input->post('sale_id');
        $event_txt = getSaleText($sale_id);
        putAuditLog($this->session->userdata('user_id'),$event_txt,"Cancelled Sale",date('Y-m-d H:i:s'));
        $this->delete_specific_order_by_sale_id($sale_id);
        echo "success";
    }
     /**
     * delete specific order by sale id
     * @access public
     * @return boolean
     * @param int
     */
    public function delete_specific_order_by_sale_id($sale_id){
        $this->db->delete('tbl_sales', array('id' => $sale_id));
        $this->db->delete('tbl_sales_details', array('sales_id' => $sale_id));
        $this->db->delete('tbl_sale_payments', array('sale_id' => $sale_id));
        $this->db->delete('tbl_sales_details_modifiers', array('sales_id' => $sale_id));
        $this->db->delete('tbl_sale_consumptions', array('sale_id' => $sale_id));
        $this->db->delete('tbl_sale_consumptions_of_menus', array('sales_id' => $sale_id));
        $this->db->delete('tbl_sale_consumptions_of_modifiers_of_menus', array('sales_id' => $sale_id));
        $this->db->delete('tbl_orders_table', array('sale_id' => $sale_id));
        return true;
    }
     /**
     * update order status ajax
     * @access public
     * @return void
     * @param int
     */
    public function update_order_status_ajax()
    {
        $payment_details = json_decode(json_decode($this->input->post('payment_object')));

        $sale_no = $this->input->post('sale_no');
        $close_order = $this->input->post('close_order');
        $paid_amount = $this->input->post('paid_amount');
        $due_amount = $this->input->post('due_amount');
        $given_amount_input = $this->input->post('given_amount_input');
        $change_amount_input = $this->input->post('change_amount_input');
        $payment_method_type = $this->input->post('payment_method_type');
        $currency_type = $this->input->post('is_multi_currency');
        $multi_currency = $this->input->post('multi_currency');
        $multi_currency_rate = $this->input->post('multi_currency_rate');
        $multi_currency_amount = $this->input->post('multi_currency_amount');


        $is_just_cloase = ($payment_method_type=='0')? true:false;

        $sale =getSaleDetails($sale_no);

        if($sale){
            $this->db->delete('tbl_sale_payments', array('sale_id' => $sale->id));

            if($currency_type==1){
                $data = array();
                $data['payment_id'] = 1;
                $data['payment_name'] = "Cash";
                $data['amount'] = $multi_currency_amount;
                $data['multi_currency'] = $multi_currency;
                $data['multi_currency_rate'] = $multi_currency_rate;
                $data['currency_type'] = $currency_type;
                $data['date_time'] = date('Y-m-d H:i:s'); //$sale->date_time;
                $data['sale_id'] = $sale->id;
                $data['counter_id'] = $this->session->userdata('counter_id');
                $data['user_id'] = $this->session->userdata('user_id');
                $data['outlet_id'] = $this->session->userdata('outlet_id');
                $this->Common_model->insertInformation($data, "tbl_sale_payments");
            }else{
                foreach ($payment_details as $value){
    
                    $data = array();
                    $data['payment_id'] = $value->payment_id;
                    $data['payment_name'] = $value->payment_name;
                        if($value->payment_id==5){
                            $data['usage_point'] = $value->usage_point;
    
                            $previous_id_update_array = array('loyalty_point_earn' => 0);
                            $this->db->where('sales_id', $sale->id);
                            $this->db->update('tbl_sales_details', $previous_id_update_array);
                        }
                    $data['amount'] = $value->amount;
                    $data['date_time'] = date('Y-m-d H:i:s'); //$sale->date_time;
                    $data['sale_id'] = $sale->id;
                    $data['counter_id'] = $this->session->userdata('counter_id');
                    $data['user_id'] = $this->session->userdata('user_id');
                    $data['outlet_id'] = $this->session->userdata('outlet_id');
                    $this->Common_model->insertInformation($data, "tbl_sale_payments");
                }
            }

            $sub_total_discount_finalize = $this->input->post('sub_total_discount_finalize');
            $total_payable = 0;
            $sub_total_discount_amount = 0;
            $total_discount_amount = 0;
    
            $sale_details = $this->Common_model->getDataById($sale->id, "tbl_sales");
            if((int)$sub_total_discount_finalize){
                $sub_total_discount_type = "fixed";
                $total_payable = $sale_details->total_payable - $sub_total_discount_finalize;
                $sub_total_discount_amount = $sale_details->sub_total_discount_amount + $sub_total_discount_finalize;
                $total_discount_amount = $sale_details->total_discount_amount + $sub_total_discount_finalize;
            }else{
                $sub_total_discount_type = "percentage";
                $total_payable = $sale_details->total_payable;
                $sub_total_discount_amount = $sale_details->sub_total_discount_amount;
                $total_discount_amount = $sale_details->total_discount_amount;
            }
    
            if($close_order=='true'){
                $this->Sale_model->delete_status_orders_table($sale->id);
                if($is_just_cloase){
                    $order_status = array('order_status' => 3,'total_payable' => $total_payable,'sub_total_discount_amount' => $sub_total_discount_amount,'total_discount_amount' => $total_discount_amount,'sub_total_discount_type' => $sub_total_discount_type,'given_amount' => $given_amount_input,'change_amount' => $change_amount_input,'close_time'=>date('H:i:s'));
                }else{
                    $order_status = array('paid_amount' =>  $paid_amount,'total_payable' => $total_payable,'sub_total_discount_amount' => $sub_total_discount_amount,'total_discount_amount' => $total_discount_amount,'sub_total_discount_type' => $sub_total_discount_type,'given_amount' => $given_amount_input,'change_amount' => $change_amount_input, 'due_amount' => $due_amount, 'order_status' => 3,'payment_method_id'=>$payment_method_type,'close_time'=>date('H:i:s'));
                }
            }else{
                $order_status = array('paid_amount' => $paid_amount,'total_payable' => $total_payable,'sub_total_discount_amount' => $sub_total_discount_amount,'total_discount_amount' => $total_discount_amount,'sub_total_discount_type' => $sub_total_discount_type,'given_amount' => $given_amount_input,'change_amount' => $change_amount_input,'due_amount' => $due_amount,'order_status' => 2,'payment_method_id'=>$payment_method_type);
            }
            $this->db->where('id', $sale->id);
            $this->db->update('tbl_sales', $order_status);
            // Resetear número asociado si la orden se está cerrando
            if($close_order == 'true') {
                $kitchen_sale = $this->db->select('number_slot')
                                        ->where('sale_no', $sale_no)
                                        ->get('tbl_kitchen_sales')
                                        ->row();
                if($kitchen_sale && $kitchen_sale->number_slot > 0) {
                    // Liberar el número en tbl_numeros
                    $this->db->where('id', $kitchen_sale->number_slot)
                            ->update('tbl_numeros', [
                                'sale_id' => NULL,
                                'sale_no' => NULL,
                                "user_id" => NULL
                            ]);

                }
            }
            echo escape_output($sale->id);


        }
     


    }
     /**
     * delete all holds with information by ajax
     * @access public
     * @return int
     * @param no
     */
    public function delete_all_holds_with_information_by_ajax()
    {
        $outlet_id = $this->session->userdata('outlet_id');
        $user_id = $this->session->userdata('user_id');
        $this->db->delete('tbl_holds', array('user_id' => $user_id,'outlet_id' => $outlet_id));
        $this->db->delete('tbl_holds_details', array('user_id' => $user_id,'outlet_id' => $outlet_id));
        $this->db->delete('tbl_holds_details_modifiers', array('user_id' => $user_id,'outlet_id' => $outlet_id));
        echo 1;
    }
     /**
     * change date of a sale ajax
     * @access public
     * @return void
     * @param no
     */
    public function change_date_of_a_sale_ajax()
    {
        $sale_id = $this->input->post('sale_id');
        $change_date = $this->input->post('change_date');
        $data['sale_date'] = date('Y-m-d',strtotime($change_date));
        $data['order_time'] = date("H:i:s");
        $changes = array(
            'sale_date' => date('Y-m-d',strtotime($change_date)),
            'order_time' => date("H:i:s"),
            'date_time' => date('Y-m-d H:i:s',strtotime($change_date.' '.date("H:i:s")))
        );

        $this->db->where('id', $sale_id);
        $this->db->update('tbl_sales', $changes);
    }
     /**
     * change date of a sale ajax
     * @access public
     * @return void
     * @param no
     */
    public function change_status_of_a_sale_ajax()
    {
        $sale_id = $this->input->post('sale_id');
        $status = $this->input->post('status');
        $data['status'] = $status;
        $changes = array(
            'status' => $status
        );

        $this->db->where('id', $sale_id);
        $this->db->update('tbl_sales', $changes);
    }
     /**
     * get Opening Balance
     * @access public
     * @return float
     * @param no
     */
	public function getOpeningBalance(){
        $user_id = $this->session->userdata('user_id');
        $outlet_id = $this->session->userdata('outlet_id');
        $date = date('Y-m-d');
        $getOpeningBalance = $this->Sale_model->getOpeningBalance($user_id,$outlet_id,$date);
        return isset($getOpeningBalance->amount) && $getOpeningBalance->amount?$getOpeningBalance->amount:'';
    }
     /**
     * get Opening Date Time
     * @access public
     * @return string
     * @param no
     */
    public function getOpeningDateTime(){
        $user_id = $this->session->userdata('user_id');
        $outlet_id = $this->session->userdata('outlet_id');
        $date = date('Y-m-d');
        $getOpeningDateTime = $this->Sale_model->getOpeningDateTime($user_id,$outlet_id,$date);
        return isset($getOpeningDateTime->opening_date_time) && $getOpeningDateTime->opening_date_time?$getOpeningDateTime->opening_date_time:'';
    }
     /**
     * get Opening Date Time
     * @access public
     * @return string
     * @param no
     */
    public function getOpeningDetails(){
        $user_id = $this->session->userdata('user_id');
        $outlet_id = $this->session->userdata('outlet_id');
        $date = date('Y-m-d');
        $getOpeningDetails = $this->Sale_model->getOpeningDetails($user_id,$outlet_id,$date);
        return isset($getOpeningDetails->opening_details) && $getOpeningDetails->opening_details?$getOpeningDetails->opening_details:'';
    }
     /**
     * get Closing Date Time
     * @access public
     * @return string
     * @param no
     */
    public function getClosingDateTime(){
        $user_id = $this->session->userdata('user_id');
        $outlet_id = $this->session->userdata('outlet_id');
        $date = date('Y-m-d');
        $getClosingDateTime = $this->Sale_model->getClosingDateTime($user_id,$outlet_id,$date);
        return isset($getClosingDateTime->closing_date_time) && $getClosingDateTime->closing_date_time?$getClosingDateTime->closing_date_time:'';
    }
     /**
     * get Purchase Paid Sum
     * @access public
     * @return float
     * @param no
     */
    public function getPurchasePaidSum(){
        $user_id = $this->session->userdata('user_id');
        $outlet_id = $this->session->userdata('outlet_id');
        $date = date('Y-m-d');
        $summationOfPaidPurchase = $this->Sale_model->getSummationOfPaidPurchase($user_id,$outlet_id,$date);
        return $summationOfPaidPurchase->purchase_paid;
    }
     /**
     * get Supplier Payment Sum
     * @access public
     * @return float
     * @param no
     */
    public function getSupplierPaymentSum(){
        $user_id = $this->session->userdata('user_id');
        $outlet_id = $this->session->userdata('outlet_id');
        $date = date('Y-m-d');
        $summationOfSupplierPayment = $this->Sale_model->getSummationOfSupplierPayment($user_id,$outlet_id,$date);
        return $summationOfSupplierPayment->payment_amount;
    }
     /**
     * get Customer Due Receive Amount Sum
     * @access public
     * @return float
     * @param string
     */
    public function getCustomerDueReceiveAmountSum($date){
        $user_id = $this->session->userdata('user_id');
        $outlet_id = $this->session->userdata('outlet_id');
        $summationOfCustomerDueReceive = $this->Sale_model->getSummationOfCustomerDueReceive($user_id,$outlet_id,$date);
        return $summationOfCustomerDueReceive->receive_amount;
    }
     /**
     * get Expense Amount Sum
     * @access public
     * @return float
     * @param no
     */
    public function getExpenseAmountSum(){
        $user_id = $this->session->userdata('user_id');
        $outlet_id = $this->session->userdata('outlet_id');
        $date = date('Y-m-d');
        $getExpenseAmountSum = $this->Sale_model->getExpenseAmountSum($user_id,$outlet_id,$date);
        return $getExpenseAmountSum->amount;
    }
     /**
     * get Sale Paid Sum
     * @access public
     * @return float
     * @param string
     */
    public function getSalePaidSum($date){
        $user_id = $this->session->userdata('user_id');
        $outlet_id = $this->session->userdata('outlet_id');
        $getSalePaidSum = $this->Sale_model->getSalePaidSum($user_id,$outlet_id,$date);
        return $getSalePaidSum->amount;
    }
     /**
     * get Sale Due Sum
     * @access public
     * @return float
     * @param string
     */
    public function getSaleDueSum($date){
        $user_id = $this->session->userdata('user_id');
        $outlet_id = $this->session->userdata('outlet_id');
        $getSaleDueSum = $this->Sale_model->getSaleDueSum($user_id,$outlet_id,$date);
        return $getSaleDueSum->amount;
    }
     /**
     * get Sale In Cash Sum
     * @access public
     * @return float
     * @param string
     */
    public function getSaleInCashSum($date){
        $user_id = $this->session->userdata('user_id');
        $outlet_id = $this->session->userdata('outlet_id');
        $getSaleInCashSum = $this->Sale_model->getSaleInCashSum($user_id,$outlet_id,$date);
        return $getSaleInCashSum->amount;
    }
     /**
     * get Sale In Paypal Sum
     * @access public
     * @return float
     * @param string
     */
    public function getSaleInPaypalSum($date){
        $user_id = $this->session->userdata('user_id');
        $outlet_id = $this->session->userdata('outlet_id');
        $getSaleInPaypalSum = $this->Sale_model->getSaleInPaypalSum($user_id,$outlet_id,$date);
        return $getSaleInPaypalSum->amount;
    }
     /**
     * get Sale In Card Sum
     * @access public
     * @return float
     * @param string
     */
    public function getSaleInCardSum($date){
        $user_id = $this->session->userdata('user_id');
        $outlet_id = $this->session->userdata('outlet_id');
        $getSaleInCardSum = $this->Sale_model->getSaleInCardSum($user_id,$outlet_id,$date);
        return $getSaleInCardSum->amount;
    }
     /**
     * get Sale In Stripe Sum
      * @access public
      * @return float
      * @param string
     */
    public function getSaleInStripeSum(){
        $user_id = $this->session->userdata('user_id');
        $outlet_id = $this->session->userdata('outlet_id');
        $date = date('Y-m-d');
        $getSaleInStripeSum = $this->Sale_model->getSaleInStripeSum($user_id,$outlet_id,$date);
        return $getSaleInStripeSum->amount;
    }
     /**
     * get Payable Amount Sum
      * @access public
      * @return float
      * @param string
     */
    public function getPayableAomountSum($opening_date_time)
    {
        $user_id = $this->session->userdata('user_id');
        $outlet_id = $this->session->userdata('outlet_id');
        $getPayableAomountSum = $this->Sale_model->getPayableAomountSum($user_id,$outlet_id,$opening_date_time);
        return $getPayableAomountSum->amount;
    }
     /**
     * register Detail Calculation To Show
     * @access public
     * @return array
     * @param no
     */
    public function registerDetailCalculationToShow() {
        $opening_balances = [];
        $opening_date_time = $this->getOpeningDateTime();
        $closing_date_time = $this->getClosingDateTime();
        $outlet_id = $this->session->userdata('outlet_id');
        $counter_id = $this->session->userdata('counter_id');
        $user_id = $this->session->userdata('user_id');
    
        // Obtener opciones de outlet
        $getOutletInfo = $this->Common_model->getDataById($outlet_id, "tbl_outlets");
        $registro_ocultar = $getOutletInfo->registro_ocultar;
        $registro_detallado = $getOutletInfo->registro_detallado;
    
        $opening_details = $this->getOpeningDetails();
        $opening_details_decode = json_decode($opening_details);

        if (is_array($opening_details_decode) || is_object($opening_details_decode)) {
            foreach ($opening_details_decode as $key=>$value){
                $payments = explode("||",$value);
                $opening_balances[$key]['payment_name'] = $payments[1];
                $opening_balances[$key]['payment_amount'] = isset($payments[2]) ? $payments[2] : 0;
                $opening_balances[$key]['payment_id'] = isset($payments[0]) ? $payments[0] : 0;
            }
        }
    
        $show_main_table = true;
        if($registro_ocultar === "Yes" && !$closing_date_time){
            $show_main_table = false;
        }
    
        $html_content = "";
    
        // Tabla de gastos detallados
        $fromDate = $opening_date_time;
        $toDate = $closing_date_time ? $closing_date_time : date('Y-m-d H:i:s');
        
        $html_content .= '<table class="table_register_details top_margin_15"><thead>
        <tr><th></th><th></th><th></th><th></th></tr></thead><tbody>
        <tr>
            <th>'.lang('user').'</th>
            <th>'.$this->session->userdata('full_name').'</th>
            <th></th><th></th>
        </tr>
        <tr>
            <th>'.lang('counter').'</th>
            <th>'.getCounterName($counter_id).'</th>
            <th></th><th></th>
        </tr>
        <tr>
            <th>'.lang('Time_Range').'</th>
            <th>'.(date("Y-m-d h:i:s A",strtotime($opening_date_time))).' hasta las '.($closing_date_time ? date("Y-m-d h:i:s A",strtotime($closing_date_time)) : date("Y-m-d h:i:s A")).'</th>
            <th></th><th></th>
        </tr>
        <tr><td>&nbsp;</td><td>&nbsp;</td><th>&nbsp;</th><th class="text_right">&nbsp;</th></tr>
                </tbody></table>
        ';

        $detailed_expenses = $this->Sale_model->getDetailedExpenses($fromDate, $toDate, $user_id, $outlet_id);
        if ($detailed_expenses && count($detailed_expenses) > 0) {
            $html_content .= '<h3>Registro de Gastos</h3>';
            $html_content .= '<table class="table_register_details table_expense_details top_margin_15">
                <thead>
                    <tr>
                        <th>Fecha/Hora</th>
                        <th>Descripción</th>
                        <th class="text_right">Monto</th>
                    </tr>
                </thead>
                <tbody>';
            foreach ($detailed_expenses as $exp) {
                $html_content .= '<tr>
                    <td>'.date("Y-m-d H:i", strtotime($exp->added_date_time)).'</td>
                    <td>'.htmlspecialchars($exp->note).'</td>
                    <td class="text_right">'.getAmtPCustom($exp->amount).'</td>
                </tr>';
            }
            $html_content .= '</tbody></table>';
        }
    
        // Tabla de ventas detalladas si "registro_detallado"
        if($registro_detallado === "Yes"){
            $html_content .= '<h3>Registro de Ventas</h3>';
            $detailed_sales = $this->Sale_model->getDetailedSales($outlet_id, $fromDate, $toDate);
            
            if ($detailed_sales && count($detailed_sales) > 0) {
                $html_content .= '<table class="table_register_details table_sale_details top_margin_15">
                    <thead>
                        <tr>
                            <th>Fecha/Hora</th>
                            <th>Número</th>
                            <th class="text_right">Monto</th>
                        </tr>
                    </thead>
                    <tbody>';
                $total_ventas_detalladas = 0;
                foreach ($detailed_sales as $sale) {
                    $html_content .= '<tr>
                        <td>'.date("Y-m-d H:i", strtotime($sale->paid_date_time)).'</td>
                        <td>#'.$sale->number_slot_name.' '.$sale->sale_no.'</td>
                        <td class="text_right">'.getAmtPCustom($sale->amount).'</td>
                    </tr>';
                    $total_ventas_detalladas += $sale->amount;
                }
                $html_content .= '<tr>
                    <td></td>
                    <td><b>TOTAL</b></td>
                    <td class="text_right"><b>'.getAmtPCustom($total_ventas_detalladas).'</b></td>
                </tr>';
                $html_content .= '</tbody></table>';
            }
        }

        // Tabla principal (solo si corresponde mostrarla)
        if($show_main_table){
            $html_content .= '<h3>Resumen de Cierre</h3>';
            $html_content .= '<table id="datatable" class="table_register_details top_margin_15"><thead>
                <tr><th></th><th></th><th></th><th></th></tr></thead><tbody>
                <tr><td>&nbsp;</td><td>&nbsp;</td><th>&nbsp;</th><th class="text_right">&nbsp;</th></tr>
                <tr>
                    <th>'.lang('sn').'</th>
                    <th>'.lang('payment_method').'</th>
                    <th>'.lang('Transactions').'</th>
                    <th class="text_right">'.lang('amount').'</th>
                </tr>';
    
            $array_p_name = array();
            $array_p_amount = array();
            if(isset($opening_details_decode) && $opening_details_decode){
                foreach ($opening_details_decode as $key=>$value){
                    $key++;
                    $payments = explode("||",$value);
    
                    $total_purchase = $this->Sale_model->getAllPurchaseByPayment($opening_date_time,$payments[0]);
                    $total_due_receive = $this->Sale_model->getAllDueReceiveByPayment($opening_date_time,$payments[0]);
                    $total_due_payment = $this->Sale_model->getAllDuePaymentByPayment($opening_date_time,$payments[0]);
                    $total_expense = $this->Sale_model->getAllExpenseByPayment($opening_date_time,$payments[0]);
                    $refund_amount = $this->Sale_model->getAllRefundByPayment($opening_date_time,$payments[0]);
                    $total_sale =  $this->Sale_model->getAllSaleByPayment($opening_date_time,$payments[0]);
    
                    $inline_total = $payments[2] - $total_purchase + $total_sale + $total_due_receive - $total_due_payment - $total_expense - $refund_amount;
    
                    $array_p_name[] = $payments[1];
                    $array_p_amount[] = $inline_total;
    
                    $html_content .= '<tr>
                                <td>'.$key.'</td>
                                <td>'.$payments[1].'</td>
                                <td>'.lang('register_detail_1').'</td>
                                <td class="text_right">'.getAmtPCustom($payments[2]).'</td>
                            </tr>
                            <tr>
                                <td></td><td></td>
                                <td>'.lang('register_detail_2').'</td>
                                <td class="text_right">'.getAmtPCustom($total_purchase).'</td>
                            </tr>
                            <tr>
                                <td></td><td></td>
                                <td>'.lang('register_detail_3').'</td>
                                <td class="text_right">'.getAmtPCustom($total_sale).'</td>
                            </tr>';
                    if($payments[0]==1):
                        $total_sale_mul_c_rows =  $this->Sale_model->getAllSaleByPaymentMultiCurrencyRows($opening_date_time,$payments[0]);
                        if($total_sale_mul_c_rows){
                            foreach ($total_sale_mul_c_rows as $value1):
                                $html_content .= '<tr>
                                            <td></td><td></td>
                                            <td>&nbsp;&nbsp;&nbsp;&nbsp;'.$value1->multi_currency.'</td>
                                            <td class="text_right">'.getAmtPCustom($value1->total_amount).'</td>
                                        </tr>';
                            endforeach;
                        }
                    endif;
                    $html_content .= '<tr>
                                <td></td><td></td>
                                <td>'.lang('register_detail_5').'</td>
                                <td class="text_right">'.getAmtPCustom($total_due_receive).'</td>
                            </tr>
                            <tr>
                                <td></td><td></td>
                                <td>'.lang('register_detail_6').'</td>
                                <td class="text_right">'.getAmtPCustom($total_due_payment).'</td>
                            </tr>
                            <tr>
                                <td></td><td></td>
                                <td>'.lang('register_detail_7').'</td>
                                <td class="text_right">'.getAmtPCustom($total_expense).'</td>
                            </tr>
                            <tr>
                                <td></td><td></td>
                                <td>'.lang('refund_amount').'(-)</td>
                                <td class="text_right">'.getAmtPCustom($refund_amount).'</td>
                            </tr>
                            <tr>
                                <td></td><td></td>
                                <th>'.lang('closing_balance').'</th>
                                <th class="text_right">'.getAmtPCustom($inline_total).'</th>
                            </tr>
                             <tr>
                                <td>&nbsp;</td><td>&nbsp;</td>
                                <th>&nbsp;</th>
                                <th class="text_right">&nbsp;</th>
                            </tr>';
                }
            }
    
            $html_content .= '<tr>
                                    <th></th>
                                    <th></th>
                                    <th>'.lang('summary').'</th>
                                    <th></th>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <th>&nbsp;</th>
                                <th class="text_right">&nbsp;</th>
                            </tr>';
            foreach ($array_p_name as $key=>$value){
                $html_content .= '<tr>
                                    <th></th>
                                    <th></th>
                                    <th>'.$value.'</th>
                                    <th class="text_right">'.getAmtPCustom($array_p_amount[$key]).'</th>
                            </tr>';
            }
    
            $html_content.='</tbody></table>';
        }
    
    
        $register_detail = array(
            'opening_balances' => $opening_balances,
            'opening_date_time' => date('Y-m-d h:i A', strtotime($opening_date_time)),
            'closing_date_time' => $closing_date_time,
            'html_content_for_div' => $html_content,
        );
        return $register_detail;
    }

    public function registerDetailCalculationToShowOld(){
        $opening_date_time = $this->getOpeningDateTime();
        $opening_details= $this->getOpeningDetails();

        $opening_details_decode = json_decode($opening_details);

        $html_content = '<table id="datatable" class="table_register_details top_margin_15"> <thead>
        <tr>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr> </thead>
                    <tbody>
                    <tr>
                        <th>'.lang('counter').' '.lang('name').'</th>
                        <th>'.getCounterName($this->session->userdata('counter_id')).'</th>
                        <th></th>
                        <th></th>
                    </tr>
                    <tr>
                            <th>'.lang('Time_Range').'</th>
                            <th>'.(date("Y-m-d h:m:s A",strtotime($opening_date_time))).' to '.(date("Y-m-d h:i:s A")).'</th>
                            <th></th>
                            <th></th>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <th>&nbsp;</th>
                            <th class="text_right">&nbsp;</th>
                        </tr>
                        <tr>
                            <th>'.lang('sn').'</th>
                            <th>'.lang('payment_method').'</th>
                            <th>'.lang('Transactions').'</th>
                            <th class="text_right">'.lang('amount').'</th>
                        </tr>';
        $array_p_name = array();
        $array_p_amount = array();
        if(isset($opening_details_decode) && $opening_details_decode){
            foreach ($opening_details_decode as $key=>$value){
                $key++;
                $payments = explode("||",$value);

                $total_purchase = $this->Sale_model->getAllPurchaseByPayment($opening_date_time,$payments[0]);
                $total_due_receive = $this->Sale_model->getAllDueReceiveByPayment($opening_date_time,$payments[0]);
                $total_due_payment = $this->Sale_model->getAllDuePaymentByPayment($opening_date_time,$payments[0]);
                $total_expense = $this->Sale_model->getAllExpenseByPayment($opening_date_time,$payments[0]);
                $refund_amount = $this->Sale_model->getAllRefundByPayment($opening_date_time,$payments[0]);
                 
                $total_sale =  $this->Sale_model->getAllSaleByPayment($opening_date_time,$payments[0]);

                $inline_total = $payments[2] - $total_purchase + $total_sale  + $total_due_receive - $total_due_payment - $total_expense - $refund_amount;

                $array_p_name[] = $payments[1];
                $array_p_amount[] = $inline_total;

                $html_content .= '<tr>
                            <td>'.$key.'</td>
                            <td>'.$payments[1].'</td>
                            <td>'.lang('register_detail_1').'</td>
                            <td class="text_right">'.getAmtPCustom($payments[2]).'</td>
                        </tr>
                        
                        <tr>
                            <td></td>
                            <td></td>
                            <td>'.lang('register_detail_2').'</td>
                            <td class="text_right">'.getAmtPCustom($total_purchase).'</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td>'.lang('register_detail_3').'</td>
                            <td class="text_right">'.getAmtPCustom($total_sale).'</td>
                        </tr>';
                if($payments[0]==1):
                    $total_sale_mul_c_rows =  $this->Sale_model->getAllSaleByPaymentMultiCurrencyRows($opening_date_time,$payments[0]);

                    if($total_sale_mul_c_rows){
                        foreach ($total_sale_mul_c_rows as $value1):
                            $html_content .= '<tr>
                                        <td></td>
                                        <td></td>
                                        <td>&nbsp;&nbsp;&nbsp;&nbsp;'.$value1->multi_currency.'</td>
                                        <td class="text_right">'.getAmtPCustom($value1->total_amount).'</td>
                                    </tr>';
                        endforeach;

                    }

                endif;
                $html_content .= '<tr>
                            <td></td>
                            <td></td>
                            <td>'.lang('register_detail_5').'</td>
                            <td class="text_right">'.getAmtPCustom($total_due_receive).'</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td>'.lang('register_detail_6').'</td>
                            <td class="text_right">'.getAmtPCustom($total_due_payment).'</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td>'.lang('register_detail_7').'</td>
                            <td class="text_right">'.getAmtPCustom($total_expense).'</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td>'.lang('refund_amount').'(-)</td>
                            <td class="text_right">'.getAmtPCustom($refund_amount).'</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <th>'.lang('closing_balance').'</th>
                            <th class="text_right">'.getAmtPCustom($inline_total).'</th>
                        </tr>
                         <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <th>&nbsp;</th>
                            <th class="text_right">&nbsp;</th>
                        </tr>';
            }
        }

        $html_content .= '<tr>
                                <th></th>
                                <th></th>
                                <th>'.lang('summary').'</th>
                                <th></th>
                        </tr>';
        $html_content .= '<tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <th>&nbsp;</th>
                            <th class="text_right">&nbsp;</th>
                        </tr>';
        foreach ($array_p_name as $key=>$value){
            $html_content .= '<tr>
                                <th></th>
                                <th></th>
                                <th>'.$value.'</th>
                                <th class="text_right">'.getAmtPCustom($array_p_amount[$key]).'</th>
                        </tr>';
        }


        $html_content.='</tbody>
                    </table>';


        $register_detail = array(
            'opening_date_time' => date('Y-m-d h:m A', strtotime($opening_date_time)),
            'closing_date_time' => $this->getClosingDateTime(),
            'html_content_for_div' => $html_content,
        );
        return $register_detail;
    }
     /**
     * get Balance
     * @access public
     * @return float
     * @param no
     */
    public function getBalance(){
        $opening_date_time = $this->getOpeningDateTime();
        $balance = $this->getOpeningBalance()+$this->getSalePaidSum($opening_date_time)+$this->getCustomerDueReceiveAmountSum($opening_date_time);
        return  $balance;
    }
     /**
     * register Detail Calculation To Show Ajax
     * @access public
     * @return object
     * @param no
     */
    public function registerDetailCalculationToShowAjax(){
        $all_register_info_values = $this->registerDetailCalculationToShow();
        // return $all_register_info_values;
        echo json_encode($all_register_info_values);
    }
     /**
     * print All Calculation
     * @access public
     * @return void
     * @param no
     */
    public function printAllCalculation()
    {
        //generate html content for view
        echo 'opening balance: '.$this->getOpeningBalance().'<br/>';
        echo 'purchase paid sum: '.$this->getPurchasePaidSum().'<br/>';
        echo 'supplier payment sum: '.$this->getSupplierPaymentSum().'<br/>';
        echo 'customer due receive amount sum: '.$this->getCustomerDueReceiveAmountSum("").'<br/>';
        echo 'expense amount sum: '.$this->getExpenseAmountSum().'<br/>';
        // echo 'sale amount sum: '.$this->getSaleAmountSum().'<br/>';
        echo 'sale in cash sum: '.$this->getSaleInCashSum("").'<br/>';
        echo 'sale in paypal sum: '.$this->getSaleInPaypalSum("").'<br/>';
        // echo 'sale in paypal sum: '.$this->getSaleInPaypalSum().'<br/>';
        echo 'sale in card sum: '.$this->getSaleInCardSum("").'<br/>';
        echo 'sale in stripe sum: '.$this->getSaleInStripeSum().'<br/>';
    }
     /**
     * close Register
     * @access public
     * @return void
     * @param no
     */
    public function closeRegister()
    {
        $user_id = $this->session->userdata('user_id');
        $outlet_id = $this->session->userdata('outlet_id');
        $counter_id = $this->session->userdata('counter_id');
        $opening_date_time = $this->getOpeningDateTime();
        $opening_details = $this->getOpeningDetails();
        $closing_date_time = date('Y-m-d H:i:s');

        $opening_details_decode = json_decode($opening_details);
        $total_closing = 0;
        $total_sale_all = 0;
        $total_purchase_all = 0;
        $total_refund_all = 0;
        $total_due_receive_all = 0;
        $total_due_payment_all = 0;
        $total_expense_all = 0;
        $payment_details = array();
        $others_currency = array();

        // Armar detalles por método de pago
        $detalles_metodo_pago = [];
        $resumen_final = [];
        $metodos_pago_cierre = []; // Para declaración

        foreach ($opening_details_decode as $key => $value) {
            $payments = explode("||", $value);
            $payment_id = $payments[0];
            $payment_name = $payments[1];
            $opening_balance = (float) $payments[2];

            $total_sale = (float) $this->Sale_model->getAllSaleByPayment($opening_date_time, $payment_id);
            $total_purchase = (float) $this->Sale_model->getAllPurchaseByPayment($opening_date_time, $payment_id);
            $total_due_receive = (float) $this->Sale_model->getAllDueReceiveByPayment($opening_date_time, $payment_id);
            $total_due_payment = (float) $this->Sale_model->getAllDuePaymentByPayment($opening_date_time, $payment_id);
            $total_expense = (float) $this->Sale_model->getAllExpenseByPayment($opening_date_time, $payment_id);
            $refund_amount = (float) $this->Sale_model->getAllRefundByPayment($opening_date_time, $payment_id);

            $total_sale_all += $total_sale;
            $total_purchase_all += $total_purchase;
            $total_refund_all += $refund_amount;
            $total_due_receive_all += $total_due_receive;
            $total_due_payment_all += $total_due_payment;
            $total_expense_all += $total_expense;

            // $opening_balance - 
            $inline_closing =  $total_sale - $total_purchase + $total_due_receive - $total_due_payment - $total_expense - $refund_amount;
            $total_closing += $inline_closing;

            $preview_amount = isset($payment_details[$payment_name]) && $payment_details[$payment_name] ? $payment_details[$payment_name] : 0;
            $payment_details[$payment_name] = $preview_amount + $inline_closing;

            // Para otros/currency multi-moneda (usualmente efectivo)
            if ($payment_id == 1) {
                $total_sale_mul_c_rows = $this->Sale_model->getAllSaleByPaymentMultiCurrencyRows($opening_date_time, $payment_id);
                if ($total_sale_mul_c_rows) {
                    foreach ($total_sale_mul_c_rows as $value1) {
                        $tmp_arr = array();
                        $tmp_arr['payment_name'] = $value1->multi_currency;
                        $tmp_arr['amount'] = getAmtPCustom($value1->total_amount);
                        $others_currency[] = $tmp_arr;
                    }
                }
            }

            // Guardar detalle para WhatsApp
            $detalles_metodo_pago[] = [
                'nombre'        => $payment_name,
                'saldo_inicial' => $opening_balance,
                'compra'        => $total_purchase,
                'venta'         => $total_sale,
                'ingresos'      => $total_due_receive,
                'egresos'       => $total_due_payment + $total_expense,
                'devolucion'    => $refund_amount,
                'otros'         => 0, // Puedes sumar aquí otros posibles movimientos
                'cierre'        => $inline_closing,
            ];
            $resumen_final[] = [
                'nombre' => $payment_name,
                'valor'  => $inline_closing
            ];
            // Para declaración
            $metodos_pago_cierre[$payment_id] = [
                'nombre' => $payment_name,
                'cierre' => $inline_closing
            ];
        }

        $changes = array(
            'closing_balance' => $total_closing,
            'closing_balance_date_time' => $closing_date_time,
            'customer_due_receive' => $total_due_receive_all,
            'total_purchase' => $total_purchase_all,
            'refund_amount' => $total_refund_all,
            'total_due_payment' => $total_due_payment_all,
            'total_expense' => $total_expense_all,
            'sale_paid_amount' => $total_sale_all,
            'others_currency' => json_encode($others_currency),
            'payment_methods_sale' => json_encode($payment_details),
            'register_status' => 2
        );

        // // Actualiza la caja
        $this->db->where('outlet_id', $outlet_id);
        $this->db->where('user_id', $user_id);
        $this->db->where('register_status', 1);
        $this->db->update('tbl_register', $changes);

        // OBTENER EL ID DEL REGISTRO DE CAJA (register_id)
        $register = $this->db
            ->where('outlet_id', $outlet_id)
            ->where('user_id', $user_id)
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get('tbl_register')
            ->row();
        $register_id = $register ? $register->id : null;

        // GASTOS DETALLADOS
        $from_datetime = $opening_date_time;
        $to_datetime = $closing_date_time;
        $gastos = $this->Sale_model->getDetailedExpenses($from_datetime, $to_datetime, $user_id, $outlet_id);

        // Recibe el array de declaración de cierre (statement)
        $statement = $this->input->post('statement');
        $array_declaracion = [];
        if ($statement) {
            if (is_string($statement)) {
                $statement = json_decode($statement, true); // Si viene como JSON string
            }
            foreach ($statement as $item) {
                $data = array(
                    'register_id' => $register_id,
                    'user_id' => $user_id,
                    'user_txt' => $this->session->userdata('full_name'),
                    'payment_id' => isset($item['payment_method_id']) ? $item['payment_method_id'] : null,
                    'payment_txt' => isset($item['payment_method_name']) ? $item['payment_method_name'] : null,
                    'mount' => isset($item['amount']) ? $item['amount'] : null,
                    'datetime' => date('Y-m-d H:i:s'),
                );
                if ($item['amount'] > 0) {
                    $this->db->insert('tbl_register_statement', $data);
                }
                // Para declaración de cierre
                $id = isset($item['payment_method_id']) ? $item['payment_method_id'] : null;
                $nombre = isset($item['payment_method_name']) ? $item['payment_method_name'] : '';
                $declarado = isset($item['amount']) ? (float) $item['amount'] : 0;
                $esperado = isset($metodos_pago_cierre[$id]['cierre']) ? $metodos_pago_cierre[$id]['cierre'] : 0;
                $array_declaracion[] = [
                    'nombre'    => $nombre,
                    'declarado' => $declarado,
                    'esperado'  => $esperado
                ];
            }
        }

        $mensaje = $this->generarMensajeCierreCaja([
            'company_name'         => $this->session->userdata('outlet_name'),
            'counter_name'         => getCounterName($counter_id),
            'apertura'             => date('Y-m-d h:i A', strtotime($opening_date_time)),
            'cierre'               => date('Y-m-d h:i A', strtotime($closing_date_time)),
            'usuario'              => $this->session->userdata('full_name'),
            'gastos'               => $gastos,
            'detalles_metodo_pago' => $detalles_metodo_pago,
            'resumen_final'        => $resumen_final,
            'declaracion'          => $array_declaracion,
            'total_sale_all' => $total_sale_all,
        ]);

        // ENVÍA EL MENSAJE A TODOS LOS NÚMEROS CONFIGURADOS
        $this->load->library('NodeWaApi');
        $this->nodewaapi->send_report_to_all($mensaje);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'printerApp' => $this->printer_app_register_report_data($register_id),
                'status' => 'success'
            ]));
        return;
    }

    private function generarMensajeCierreCaja($data)
    {
        extract($data);

        $line = "------------------------------";
        $msg = [];
        $msg[] = "*REPORTE DE CIERRE DE CAJA*";
        $msg[] = "";
        $msg[] = "Sucursal: $company_name";
        $msg[] = "Usuario: $usuario";
        $msg[] = "Contador: $counter_name";
        $msg[] = "Apertura: $apertura";
        $msg[] = "Cierre: $cierre";
        $msg[] = $line;

        // GASTOS
        $hay_gastos = false;
        $total_gastos = 0;
        if (!empty($gastos)) {
            foreach ($gastos as $gasto) {
                if (floatval($gasto->amount) != 0) {
                    if (!$hay_gastos) {
                        $msg[] = "*DETALLE DE GASTOS*";
                        $hay_gastos = true;
                    }
                    $total_gastos += floatval($gasto->amount);
                    $nota = $gasto->note ? $gasto->note : '(Sin nota)';
                    $msg[] = str_pad($nota, 20) . " " . str_pad(getAmtPCustom($gasto->amount), 10, " ", STR_PAD_LEFT);
                }
            }
            if ($hay_gastos) {
                $msg[] = $line;
                $msg[] = "TOTAL GASTOS:   " . getAmtPCustom($total_gastos);
                $msg[] = "";
            }
        }

        // DETALLE POR MÉTODO DE PAGO
        foreach ($detalles_metodo_pago as $dmp) {
            // Sólo mostramos el método si al menos uno de sus montos es distinto de cero
            $mostrar = (
                $dmp['saldo_inicial'] != 0 ||
                $dmp['compra'] != 0 ||
                $dmp['venta'] != 0 ||
                $dmp['ingresos'] != 0 ||
                $dmp['egresos'] != 0 ||
                $dmp['devolucion'] != 0 ||
                $dmp['otros'] != 0 ||
                $dmp['cierre'] != 0
            );
            if (!$mostrar) continue;

            $msg[] = $line;
            $msg[] = "*" . strtoupper($dmp['nombre']) . "*";
            if ($dmp['saldo_inicial'] != 0) $msg[] = "Saldo inicial:  " . getAmtPCustom($dmp['saldo_inicial']);
            if ($dmp['compra']        != 0) $msg[] = "Compras:        -" . getAmtPCustom($dmp['compra']);
            if ($dmp['venta']         != 0) $msg[] = "Ventas:         +" . getAmtPCustom($dmp['venta']);
            if ($dmp['ingresos']      != 0) $msg[] = "Ingresos:       +" . getAmtPCustom($dmp['ingresos']);
            if ($dmp['egresos']       != 0) $msg[] = "Egresos:        -" . getAmtPCustom($dmp['egresos']);
            if ($dmp['devolucion']    != 0) $msg[] = "Devoluciones:   -" . getAmtPCustom($dmp['devolucion']);
            if ($dmp['otros']         != 0) $msg[] = "Otros:           " . getAmtPCustom($dmp['otros']);
            if ($dmp['cierre']        != 0) $msg[] = "TOTAL " . strtoupper($dmp['nombre']) . ": " . getAmtPCustom($dmp['cierre']);
            $msg[] = "";
        }

        // RESUMEN FINAL
        $hay_resumen = false;
        foreach ($resumen_final as $i => $r) {
            if ($r['valor'] != 0) {
                if (!$hay_resumen) {
                    $msg[] = $line;
                    $msg[] = "*RESUMEN FINAL*";
                    $hay_resumen = true;
                }
                $msg[] = str_pad($r['nombre'], 20) . str_pad(getAmtPCustom($r['valor']), 10, " ", STR_PAD_LEFT);
            }
        }
        if ($hay_resumen) $msg[] = $line;

        
        // VENTA TOTAL CAJA
        $msg[] = "";
        $msg[] = "*VENTA TOTAL CAJA*";
        $msg[] = getAmtPCustom($total_sale_all);
        $msg[] = "";


        // DECLARACION DE CIERRE
        $hay_declaracion = false;
        foreach ($declaracion as $d) {
            if ($d['declarado'] != 0 || $d['esperado'] != 0) {
                if (!$hay_declaracion) {
                    $msg[] = "*DECLARACION DE CIERRE*";
                    $hay_declaracion = true;
                }
                $msg[] = str_pad($d['nombre'], 20) . str_pad(getAmtPCustom($d['declarado']), 10, " ", STR_PAD_LEFT);
                $diferencia = $d['declarado'] - $d['esperado'];
                if (abs($diferencia) > 0.01) {
                    $tipo = $diferencia > 0 ? "SOBRANTE" : "FALTANTE";
                    $msg[] = "    $tipo: " . getAmtPCustom(abs($diferencia));
                }
            }
        }
        // DIFERENCIAL TOTAL FINAL
        $diferencial_total = 0;
        foreach ($declaracion as $d) {
            $diferencial_total += ($d['declarado'] - $d['esperado']);
        }
        $msg[] = $line;
        if (abs($diferencial_total) > 0.01) {
            $tipo = $diferencial_total > 0 ? "SOBRANTE TOTAL" : "FALTANTE TOTAL";
            $msg[] = "*$tipo*: " . getAmtPCustom(abs($diferencial_total));
        } else {
            $msg[] = "*DIFERENCIAL TOTAL*: 0";
        }
        $msg[] = "";
        
        return implode("\n", $msg);
    }


     /**
     * get new notification
     * @access public
     * @return object
     * @param no
     */
    public function get_new_notification()
    {
        $outlet_id = $this->session->userdata('outlet_id');
        $notifications = $this->Sale_model->getNotificationByOutletId($outlet_id);
        return $notifications;
    }
     /**
     * get new notifications ajax
     * @access public
     * @return object
     * @param no
     */
    public function get_new_notifications_ajax()
    {
        echo json_encode($this->get_new_notification());
    }
     /**
     * remove notification
     * @access public
     * @return int
     * @param no
     */
    public function remove_notication_ajax()
    {
        $notification_id = $this->input->post('notification_id');
        $this->db->delete('tbl_notifications', array('id' => $notification_id));
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
            $this->db->delete('tbl_notifications', array('id' => $single_notification));
        }
    }

     /**
     * add temp bot
     * @access public
     * @return int
     * @param no
     */
    public function getTotalLoyaltyPoint()
    {
        $customer_id = json_decode($this->input->post('customer_id'));
        if($customer_id==1){
            $data['status'] = false;
            $data['alert_txt'] = lang('loyalty_point_not_applicable_for_walk_in_customer');
        }else{
            $data['status'] = true;
        }

        $return_data = getTotalLoyaltyPoint($customer_id,$this->session->userdata('outlet_id'));
        $available_point = $return_data[1];

        $data['total_point'] = $available_point;

        echo json_encode($data);
    }
     /**
     * remove a table booking ajax
     * @access public
     * @return object
     * @param no
     */
    public function remove_a_table_booking_ajax()
    {
        $orders_table_id = $this->input->post('orders_table_id');
        $orders_table_single_info = $this->Common_model->getDataById($orders_table_id, "tbl_orders_table");
        $this->db->delete('tbl_orders_table', array('id' => $orders_table_id));
        echo json_encode($orders_table_single_info);
    }
     /**
     * get all assets info by ajax
     * @access public
     * @return object
     * @param no
     */
    public function get_all_assets_info_by_ajax()
    {
        $outlet_id = $this->session->userdata('outlet_id');
        // echo $outlet_id;
        $assets = $this->Sale_model->get_all_assets($outlet_id);
        $data = new \stdClass();
        $data->assets_info = $this->assets_details($assets);
        echo json_encode($data);
    }
     /**
     * assets details
     * @access public
     * @return object
     * @param string
     */
    public function assets_details($assets)
    {
        foreach($assets as $asset){
            $asset->asset_games = $this->Sale_model->getGamesOfAssetByAssetId($asset->id);
        }
        return $assets;
    }
 
    /**
     * get Waiter Orders
     * @access public
     * @return object
     * @param no
     */
    public function updateOrderForWaiter(){
        $return_data = array();
        
        // 1. Obtenemos el último sync del frontend (puede ser NULL en primera carga)
        $sale_no = $this->input->post('sale_no');
        $return_data['order'] = $this->Common_model->getOrderBySaleNo($sale_no);
        echo json_encode($return_data);
    }

    /**
     * get Waiter Orders
     * @access public
     * @return object
     * @param no
     */
    public function getWaiterOrders(){
        // $this->load->library('Redis_library'); // Carga la librería de Redis
    
        $return_data = array();
        $last_sync = $this->input->post('last_sync');
        $return_data['server_time'] = gmdate('Y-m-d H:i:s');
    
        // Clave de caché para las órdenes de los meseros
        $company_id = $this->session->userdata('company_id');
        $outlet_id = $this->session->userdata('outlet_id');
        // $cache_key = "waiter_orders_" . $company_id . "_" . $outlet_id;
        // $cached_data = $this->redis_library->get($cache_key);
    
        // if ($cached_data) {
        //     // Si hay datos en el caché, los usamos
        //     $return_data = $cached_data;
        //     $return_data['redis_status'] = 'Cargado desde REDIS...';
        // } else {

            $get_waiter_orders = []; //$this->Common_model->getWaiterOrders();
            // $get_waiter_invoice_orders = $this->Common_model->getWaiterInvoiceOrders();
            // $get_waiter_orders_for_update_sender = $this->Common_model->getWaiterOrdersForUpdateSender();
            // $get_waiter_orders_for_update_receiver = $this->Common_model->getWaiterOrdersForUpdateReceiver();
            $get_waiter_orders_for_delete_sender = $this->Common_model->getWaiterOrdersForDeleteSender();
            $already_invoiced_orders = $this->Common_model->alreadyInvoicedOrders();
            $user_id = $this->session->userdata('user_id');

            $return_data['get_waiter_orders'] = $get_waiter_orders;
            // $return_data['get_waiter_invoice_orders'] = $get_waiter_invoice_orders;
            // $return_data['get_waiter_orders_for_update_sender'] = $get_waiter_orders_for_update_sender;
            // $return_data['get_waiter_orders_for_update_receiver'] = $get_waiter_orders_for_update_receiver;
            $return_data['get_waiter_orders_for_update_receiver'] = $this->Common_model->getFilteredUpdates($last_sync);
            $return_data['get_waiter_orders_for_delete_sender'] = $get_waiter_orders_for_delete_sender; 
            $return_data['already_invoiced_orders'] = $already_invoiced_orders;
            // $return_data['get_all_running_order_for_new_pc'] = get_all_running_order_for_new_pc($user_id);
            $return_data['get_all_running_order_for_new_pc'] = get_all_running_order_for_new_pc_all();
            $return_data['occupied_numbers'] = $this->getUpdatedNumbers();
            $return_data['redis_status'] = 'Cargado desde BD...';


            // Guardamos los datos en Redis con un TTL de 5 segundos
            // $this->redis_library->set($cache_key, $return_data, 7);
        // }

        echo json_encode($return_data);
    }
    
    public function getOrderedTable(){
        $getOrderedTable = $this->Common_model->getOrderedTable();
        echo json_encode($getOrderedTable);
    }

    /**
     * get Waiter Invoice Orders
     * @access public
     * @return object
     * @param no
     */
    public function getWaiterInvoiceOrders(){
        $waiter_database = $this->Common_model->getWaiterInvoiceOrders();
        echo json_encode($waiter_database);
    }

    /**
     * set Order Pulled
     * @access public
     * @return void
     * @param no
     */
    public function setOrderPulled(){
        $sale_id = escape_output($_POST['sale_id']);
        $role = $this->session->userdata('role');
        $designation = $this->session->userdata('designation');
        if($role=="Admin"){
            $data['pull_update_admin'] = 2;
        }else{
            if($designation=="Cashier"){
                $data['pull_update_cashier'] = 2;
            }else{
                $data['pull_update'] = 2;
            }
        }

        $this->db->where('id', $sale_id);
        $this->db->update('tbl_kitchen_sales', $data);
    }

    /**
     * set Order Invoice Pulled
     * @access public
     * @return void
     * @param no
     */
    public function setOrderInvoicePulled(){
        $sale_id = escape_output($_POST['sale_id']);
        $role = $this->session->userdata('role');
        $designation = $this->session->userdata('designation');
        if($role=="Admin"){
            $data['pull_update_admin'] = 3;
            $data['pull_update'] = 2;
            $data['is_delete_receiver'] = 1;
        }else{
            if($designation=="Cashier"){
                $data['pull_update_cashier'] = 3;
                $data['pull_update'] = 2;
            }else{
                $data['pull_update'] = 3;
            }
        }
        $this->db->where('id', $sale_id);
        $this->db->update('tbl_kitchen_sales', $data);
    }
    /**
     * set Order Invoice Updated
     * @access public
     * @return void
     * @param no
     */
    public function setOrderInvoiceUpdated(){
        $sale_id = escape_output($_POST['sale_id']);
        $type = escape_output($_POST['type']);
        if($type==1){
            $data['is_update_sender'] = 3;
        }else{
            $role = $this->session->userdata('role');
            if($role=="Admin"){
                $data['is_update_receiver_admin'] = 3;
            }else{
                $data['is_update_receiver'] = 3;
            }
        }
        $this->db->where('id', $sale_id);
        $this->db->update('tbl_kitchen_sales', $data);
    }
    public function removePulledData(){
        $id = escape_output($_POST['id']);
        $this->db->delete("tbl_running_orders", array("id" => $id));
    }
    public function removePulledTableData(){
        $sale_no = escape_output($_POST['sale_no']);
        $this->db->delete("tbl_running_order_tables", array("sale_no" => $sale_no));
    }
    public function remove_table(){
        $sale_no = escape_output($_POST['sale_no']);
        $table_id = escape_output($_POST['table_id']);
        $this->db->delete("tbl_running_order_tables", array("sale_no" => $sale_no,"table_id" => $table_id));
        echo json_encode("success");
    }
    /**
     * set Order Invoice Deleted
     * @access public
     * @return void
     * @param no
     */
    public function setOrderInvoiceDeleted(){
        $sale_id = escape_output($_POST['sale_id']);
        $type = escape_output($_POST['type']);
        if($type==1){
            $data['is_delete_sender'] = 3;
        }else{
            $role = $this->session->userdata('role');
            if($role=="Admin"){
                $data['is_deletxe_receiver_admin'] = 3;
            }else{
                $data['is_delete_receiver'] = 3;
            }
        }
        $this->db->where('id', $sale_id);
        $this->db->update('tbl_kitchen_sales', $data);
    }
    public function setMergeDelete(){
            $sale_no = escape_output($_POST['sale_no']);
            $select_kitchen_row = getKitchenSaleDetailsBySaleNo($sale_no);
            if($select_kitchen_row){
                $this->db->delete("tbl_kitchen_sales_details", array("sales_id" => $select_kitchen_row->id));
                $this->db->delete("tbl_kitchen_sales_details_modifiers", array("sales_id" => $select_kitchen_row->id));
                $this->db->delete("tbl_kitchen_sales", array("id" => $select_kitchen_row->id));
            }

            echo 'success';
        }
    /**
     * get data for ajax datatale
     * @access public
     * @return json
     */
    public function getAjaxData() {
        $outlet_id = $this->session->userdata('outlet_id');
        $user_id = $this->session->userdata('user_id');
        $sales = $this->Sale_model->make_datatables($outlet_id);
        $data = array();
    
        $i = $_POST['start'] + 1;
        foreach ($sales as $value){
            if($value->del_status=="Live"):
                $order_type = "";
                if($value->order_type=='1'){
                    $order_type = "Mesa";
                }elseif($value->order_type=='2'){
                    $order_type = "Para Llevar";
                }elseif($value->order_type=='3'){
                    $order_type = "Delivery";
                }
    
                $html = '';
                $html .= '<a data-access="refund-123" class="btn btn-deep-purple menu_assign_class" href="'.base_url().'Sale/refund/'.($this->custom->encrypt_decrypt($value->id, 'encrypt')).'" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="'.lang('refund').'"><i class="fas fa-money-bill-alt"></i></a>';
                $html .= '<a data-access="view_invoice-123" class="btn btn-unique menu_assign_class" onclick="viewInvoice('.$value->id.')" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="'.lang('view_invoice').'"><i class="fas fa-print"></i></a>';
                if($order_type=="Delivery"):
                    $html .= '<a data-access="change_delivery_address-123" class="btn btn-cyan menu_assign_class change_delivery_details" data-status="'.escape_output($value->status).'" data-id="'.escape_output($value->id).'" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="'.lang('change_delivery_address').'"><i class="fa fa-truck tiny-icon"></i></a>';
                endif;
                $html .= '<a data-access="view_invoice-123" class="btn btn-warning menu_assign_class" href="'.base_url().'Sale/POS/'.$user_id.'/'.$outlet_id.'/'.$value->id.'" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="'.lang('edit_sale').'"><i class="far fa-edit"></i></a>';
                $html .= '<a class="delete btn btn-danger menu_assign_class" href="'.base_url().'Sale/deleteSale/'.($this->custom->encrypt_decrypt($value->id, 'encrypt')).'" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="'.lang('delete').'"><i class="fa-regular fa-trash-can"></i></a>';
    
                $sub_array =  array();
                $sub_array[] = escape_output($i++);
                $sub_array[] = escape_output($value->sale_no);
                $sub_array[] = escape_output($order_type);
                $sub_array[] = escape_output(date($this->session->userdata['date_format'], strtotime($value->sale_date)))." ".escape_output($value->order_time);
                $sub_array[] = escape_output($value->customer_name).''.escape_output($value->customer_phone?' ('.$value->customer_phone.')':'');
                $sub_array[] = escape_output(getAmtPCustom($value->total_payable));
                $sub_array[] = (($value->total_refund?escape_output(getAmtPCustom($value->total_refund)).' <i data-id="'.$value->id.'" class="fa fa-eye getDetailsRefund pointer_class"></i>':''));
                $payment_details = '';
                $outlet_id = $this->session->userdata('outlet_id');
                $salePaymentDetails = salePaymentDetails($value->id,$outlet_id);
                if(isset($salePaymentDetails) && $salePaymentDetails):
                    foreach ($salePaymentDetails as $ky=>$payment):
                        $txt_point = '';
                        if($payment->id==5){
                            $txt_point = " (Usage Point:".$payment->usage_point.")";
                        }
                        $payment_details.=(escape_output($payment->payment_name.$txt_point).":".escape_output(getAmtPCustom($payment->amount)));
                        if($ky<(sizeof($salePaymentDetails))-1){
                            $payment_details.=" - ";
                        }
                    endforeach;
                endif;
                $sub_array[] = $payment_details;
                $sub_array[] = escape_output($value->full_name);
                $sub_array[] = '<div class="btn_group_wrap">'.$html.'</div>';
                $data[] = $sub_array;
            endif;
        }
    
        $output = array(
            "draw" => intval($this->Sale_model->getDrawData()),
            "recordsTotal" => $this->Sale_model->get_all_data($outlet_id),
            "recordsFiltered" => $this->Sale_model->get_filtered_data($outlet_id),
            "data" => $data
        );
        echo json_encode($output);
    }

    public function getAjaxDataOld() {
        $outlet_id = $this->session->userdata('outlet_id');
        $user_id = $this->session->userdata('user_id');
        $sales = $this->Sale_model->make_datatables($outlet_id);
        $data = array();

        if ($sales && !empty($sales)) {
            $i = count($sales);
        }
        $row_count = 0;
        foreach ($sales as $value){
            if($value->del_status=="Live"):
                $order_type = "";
                if($value->order_type=='1'){
                    $order_type = "Dine In";
                }elseif($value->order_type=='2'){
                    $order_type = "Take Away";
                }elseif($value->order_type=='3'){
                    $order_type = "Delivery";
                }
                $row_count++;
                $html = '';

                $html .= '<a data-access="refund-123" class="btn btn-deep-purple menu_assign_class" href="'.base_url().'Sale/refund/'.($this->custom->encrypt_decrypt($value->id, 'encrypt')).'" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="'.lang('refund').'">
                <i class="fas fa-money-bill-alt"></i>
                </a>';

                $html .= '<a data-access="view_invoice-123" class="btn btn-unique menu_assign_class" onclick="viewInvoice('.$value->id.')" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="'.lang('view_invoice').'">
                <i class="fas fa-print"></i>
                </a>';

               if($order_type=="Delivery"):
                $html .= '<a data-access="change_delivery_address-123" class="btn btn-cyan menu_assign_class change_delivery_details" data-status="'.escape_output($value->status).'" data-id="'.escape_output($value->id).'" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="'.lang('change_delivery_address').'">
                 <i class="fa fa-truck tiny-icon"></i>
                </a>';
               endif;

                $html .= '<a data-access="view_invoice-123" class="btn btn-warning menu_assign_class" href="'.base_url().'Sale/POS/'.$user_id.'/'.$outlet_id.'/'.$value->id.'" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="'.lang('edit_sale').'">
                 <i class="far fa-edit"></i>
                </a>';

                $html .= '<a class="delete btn btn-danger menu_assign_class" href="'.base_url().'Sale/deleteSale/'.($this->custom->encrypt_decrypt($value->id, 'encrypt')).'" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="'.lang('delete').'">
                 <i class="fa-regular fa-trash-can"></i>
                </a>';

                $sub_array =  array();
                $sub_array[] = escape_output($i--);
                $sub_array[] = escape_output($value->sale_no);
                $sub_array[] = escape_output($order_type);
                $sub_array[] = escape_output(date($this->session->userdata['date_format'], strtotime($value->sale_date)))." ".escape_output($value->order_time);
                $sub_array[] = escape_output($value->customer_name).''.escape_output($value->customer_phone?' ('.$value->customer_phone.')':'');
                $sub_array[] = escape_output(getAmtPCustom($value->total_payable));
                $sub_array[] = (($value->total_refund?escape_output(getAmtPCustom($value->total_refund)).' <i data-id="'.$value->id.'" class="fa fa-eye getDetailsRefund pointer_class"></i>':''));
                    $payment_details = '';
                    $outlet_id = $this->session->userdata('outlet_id');
                    $salePaymentDetails = salePaymentDetails($value->id,$outlet_id);
                    if(isset($salePaymentDetails) && $salePaymentDetails):
                        foreach ($salePaymentDetails as $ky=>$payment):
                        $txt_point = '';
                        if($payment->id==5){
                            $txt_point = " (Usage Point:".$payment->usage_point.")";
                        }
                        $payment_details.=(escape_output($payment->payment_name.$txt_point).":".escape_output(getAmtPCustom($payment->amount)));
                        
                        if($ky<(sizeof($salePaymentDetails))-1){
                            $payment_details.=" - ";
                        }

                     endforeach;
                    endif;

                $sub_array[] = $payment_details;
                $sub_array[] = escape_output($value->full_name);
                $sub_array[] = '<div class="btn_group_wrap">
                                    '.$html.'
                                </div>';
                $data[] = $sub_array;
            endif;
        }
        $output = array(
            "draw" => intval($this->Sale_model->getDrawData()),
            "recordsTotal" => $this->Sale_model->get_all_data($outlet_id),
            "recordsFiltered" => $this->Sale_model->get_filtered_data($outlet_id),
            "data" => $data
        );
        echo json_encode($output);
    }
    
    public function test_print_curso(){
        $sale = [
            'id' => 123,
            'total' => 100.50,
            'items' => 3,
            'cash' => 150.00,
            'change' => 49.50,
            'user_id' => 'riverdanielp',
            'created_at' => '2023-10-01 12:34:56',
        ];
        
        $details = [
            ['name' => 'Producto 1', 'price' => '25.00', 'quantity' => '2'],
            ['name' => 'Producto 2', 'price' => '50.50', 'quantity' => '1']
        ];

        $company = [
            'name' => 'Novabox EAS',
            'address' => 'CDE, Alto Parana, Py',
            'phone' => '(0972) 22 99 58'
        ];
        
        // Convertir los datos a JSON
        $saleJson = json_encode($sale);
        $detailsJson = json_encode($details); // Extraer los detalles de la venta
        $companyJson = json_encode($company);
        
        // Unir los tres JSON en una cadena separada por "|"
        $data = $saleJson . "|" . $detailsJson . "|" . $companyJson;
        
        // Comprimir y codificar en Base64
        $compressed = gzdeflate($data, 9);
        $base64 = base64_encode($compressed);
        
        // Redirigir a la aplicación C#
        echo "<script>window.location.href = 'print://$base64';</script>";
    }
    
    public function test_app_printer(){
        $sale = [
            'id' => 123,
            'total' => 100.50,
            'items' => 3,
            'cash' => 150.00,
            'change' => 49.50,
            'user_id' => 'riverdanielp',
            'created_at' => '2023-10-01 12:34:56',
        ];
        
        $details = [
            ['name' => 'Producto 1', 'price' => '25.00', 'quantity' => '2'],
            ['name' => 'Producto 2', 'price' => '50.50', 'quantity' => '1']
        ];
        
        $company = [
            'name' => 'Novabox EAS',
            'address' => 'CDE, Alto Parana, Py',
            'phone' => '(0972) 22 99 58'
        ];

        
        // Crear el contenido del ticket
        $content = [
            // Encabezado de la empresa
            ['type' => 'text', 'align' => 'center', 'text' => $company['name']],
            ['type' => 'text', 'align' => 'center', 'text' => $company['address']],
            ['type' => 'text', 'align' => 'center', 'text' => 'Tel: ' . $company['phone']],
            ['type' => 'text', 'align' => 'center', 'text' => ''],

            // Información de la venta
            ['type' => 'text', 'align' => 'left', 'text' => 'FOLIO #' . $sale['id']],
            ['type' => 'text', 'align' => 'left', 'text' => 'FECHA: ' . $sale['created_at']],
            ['type' => 'text', 'align' => 'left', 'text' => 'ATIENDE: ' . $sale['user_id']],
            ['type' => 'text', 'align' => 'center', 'text' => ''],

            // Detalles de los productos
            ['type' => 'text', 'align' => 'left', 'text' => 'Producto               Cant     Importe'],
            ['type' => 'text', 'align' => 'center', 'text' => ''],
        ];

        // Agregar cada producto al contenido
        foreach ($details as $detail) {
            $subtotal = floatval($detail['price']) * intval($detail['quantity']);
            $content[] = ['type' => 'text', 'align' => 'left', 'text' => $detail['name']];
            $content[] = ['type' => 'text', 'align' => 'left', 'text' => 'Cant: ' . $detail['quantity'] . ' Subt: $' . number_format($subtotal, 2)];
        }

        // Totales
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => ''];
        $content[] = ['type' => 'text', 'align' => 'left', 'text' => 'ARTICULOS: ' . $sale['items']];
        $content[] = ['type' => 'text', 'align' => 'left', 'text' => 'TOTAL: $' . number_format($sale['total'], 2)];
        $content[] = ['type' => 'text', 'align' => 'left', 'text' => 'EFECTIVO: $' . number_format($sale['cash'], 2)];
        $content[] = ['type' => 'text', 'align' => 'left', 'text' => 'CAMBIO: $' . number_format($sale['change'], 2)];
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => ''];

        // Pie del ticket
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'Gracias por su compra'];
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'Novabox EAS'];
        $content[] = ['type' => 'cut'];

        $printRequest = [
            'printer' => 'POS80 Printer', // Envía el nombre de la impresora
            'width' => 80, // 58 o 80
            'content' => filterArrayRecursivelyEscPos($content)
        ];

        // Convertir a JSON
        $data = json_encode($printRequest);

        // Comprimir y codificar en Base64
        $compressed = gzdeflate($data, 9);
        $base64 = base64_encode($compressed);

        // Redirigir a la aplicación C#
        echo "<script>window.location.href = 'print://$base64';</script>";
    }

    public function printer_app_etiquetas($sale_id) {
        $data['sale_object'] = $this->get_all_information_of_a_sale($sale_id);
        $sale = $data['sale_object'];
    
        // Crear el contenido de las etiquetas
        $content = [];
    
        // Verificar si hay items en la venta
        if (isset($sale->items)) {
            $total_qty = 0;
            foreach ($sale->items as $row) {
                $total_qty += $row->tmp_qty;
            }
    
            $i = 0;
            foreach ($sale->items as $row) {
                for ($index = 0; $index < $row->tmp_qty; $index++) {
                    $i += 1;
    
                    // Encabezado de la etiqueta
                    $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'N° Pedido: ' . $sale->id];
                    $content[] = ['type' => 'text', 'align' => 'center', 'text' => $sale->customer_name];
                    $content[] = ['type' => 'subtitle', 'align' => 'center', 'text' => $row->menu_name];
                    $content[] = ['type' => 'text', 'align' => 'center', 'text' => $i . '/' . $total_qty];
                    $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'Fecha: ' . date($this->session->userdata('date_format') . ' H:i:s', strtotime($sale->date_time))];
                    // $content[] = ['type' => 'text', 'align' => 'center', 'text' => '']; // Separador entre etiquetas
    
                    // Si no es la última etiqueta, agregar un salto de página
                    // if ($i < $total_qty) {
                        $content[] = ['type' => 'cut']; // Cortar la etiqueta
                    // }
                }
            }
        }
    
        // Obtener la configuración de la impresora
        $company_id = $this->session->userdata('company_id');
        $company_data = $this->Common_model->getDataById($company_id, "tbl_companies");
        // $printer = getPrinterInfo(isset($company_data->receipt_printer_kot) && $company_data->receipt_printer_kot ? $company_data->receipt_printer_kot : '');
        // $printer_id = $this->session->userdata('printer_id');
        // $printer = getPrinterInfo(isset($printer_id) && $printer_id?$printer_id:'');
        
        $counter_details = $this->Common_model->getPrinterIdByCounterId($this->session->userdata('counter_id'));
        $printer = $this->Common_model->getPrinterInfoById($counter_details->invoice_printer_id);
        $path = @$printer->path;
    
        // Ancho de la etiqueta (58mm para etiquetas pequeñas)
        $print_format = $company_data->print_format_kot;
        if($print_format=="80mm"){
            $width = 80;
        } else {
            $width = 58;
        }
    
        // Crear el objeto de solicitud de impresión
        $printRequest = [
            'printer' => $path, // Nombre de la impresora
            'width' => $width, // Ancho de impresión (58mm para etiquetas)
            'content' => filterArrayRecursivelyEscPos($content)
        ];
    
        // Convertir a JSON
        $data = json_encode($printRequest);
    
        // Comprimir y codificar en Base64
        $compressed = gzdeflate($data, 9);
        $base64 = base64_encode($compressed);
    
        // Devolver el contenido codificado
        echo $base64;
    }

    public function printer_app_invoice($sale_no) {
        $return = $this->printer_app_invoice_prepare($sale_no);
        if ($return){
            echo $return;
        }
    }

    public function printer_app_invoice_prepare($sale_no) {
        $sale_details = getSaleDetails($sale_no);
        if (empty($sale_details)) {
            $sale_id = '';
            $sale_info = $this->get_all_information_of_a_sale($sale_no);
            $payments = [];
        } else {
            $sale_id = $sale_details->id;
            $sale_info = $this->get_all_information_of_a_sale_modify($sale_id);
            $payments = salePaymentDetails($sale_id, $this->session->userdata('outlet_id'));
        }
        if (empty($sale_info)) {
            return;
        }

        $data['sale_object'] = $sale_info;
        $sale = $data['sale_object'];
    
        // Cargar los datos de la factura electrónica al principio
        $datos_fe = null;
        if (tipoFacturacion() == 'Py_FE') {
            $this->load->helper('factura_send');
            $datos_fe = fs_get_factura_details_by_sale_id($sale->id);
        }
        $customer = getCustomerData($sale->customer_id);
        $identImpuestoName = (tipoConsultaRuc() == 'RNC') ? 'RNC' : 'RUC' ;

        // Obtener la información de la empresa
        $company = [
            'name' => $this->session->userdata('outlet_name'),
            'address' => $this->session->userdata('address'),
            'phone' => $this->session->userdata('phone'),
            'invoice_logo' => $this->session->userdata('invoice_logo'),
            'footer' => $this->session->userdata('invoice_footer'),
            'tax_registration_no' => $this->session->userdata('tax_registration_no')
        ];
    
        // Obtener la información de la venta
        $order_type = '';
        if ($sale->order_type == 1) {
            $order_type = 'A';
        } elseif ($sale->order_type == 2) {
            $order_type = 'B';
        } elseif ($sale->order_type == 3) {
            $order_type = 'C';
        }
    
        $customer_phone = (isset($sale->customer_phone) && $sale->customer_phone != '') ? ' (' . $sale->customer_phone . ')' : '';
        
        // Crear el contenido del ticket
        $content = [
            // Encabezado de la empresa
            ['type' => 'text', 'align' => 'center', 'text' => $company['name']],
            ['type' => 'text', 'align' => 'center', 'text' => $company['address']],
            ['type' => 'text', 'align' => 'center', 'text' => 'Tel: ' . $company['phone']],
        ];
        
        if ($company['tax_registration_no']) {
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => $identImpuestoName . ': ' . $company['tax_registration_no']];
        }
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => ''];

    
        // ✅ Bloque para facturación RD_AI
        if (tipoFacturacion() == 'RD_AI') {
            $df = datos_factura($sale_id);
            if (!empty($df)) {
                $content[] = ['type' => 'text', 'align' => 'center', 'text' => '----------------------'];
                $content[] = ['type' => 'text', 'align' => 'center', 'text' => $df->Tipo];
                $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'NCF: ' . $df->Prefijo . rellenar_num($df->numero)];
                if ($df->Vencimiento != NULL) {
                    $date = date_create($df->Vencimiento);
                    $newDate = date_format($date, "d/m/Y");
                    $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'Vencimiento: ' . $newDate];
                }
                $content[] = ['type' => 'text', 'align' => 'center', 'text' => '----------------------'];
                $content[] = ['type' => 'text', 'align' => 'center', 'text' => ''];
            }
        }

        // ✅ Bloque para Factura Electrónica (Py_FE)
        if ($datos_fe) {
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => '----------------------'];
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'FACTURA ELECTRÓNICA'];
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'Timbrado Nº: ' . escape_output($datos_fe->numero_timbrado)];
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'Fecha Inicio Vigencia: ' . date('d/m/Y', strtotime($datos_fe->timbrado_vigente))];
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'Factura Electrónica: ' . escape_output($datos_fe->numero_factura_formateado)];
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'Fecha y hora de emisión: ' . date('d/m/Y H:i:s', strtotime($datos_fe->fecha_emision))];
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => '----------------------'];
            if ($customer) {
                $content[] = ['type' => 'text', 'align' => 'left', 'text' => 'Razón Social: ' . escape_output($customer->name)];
                $content[] = ['type' => 'text', 'align' => 'left', 'text' => 'RUC: ' . escape_output($customer->gst_number)];
                $content[] = ['type' => 'text', 'align' => 'left', 'text' => 'Condicion: Contado'];
                $content[] = ['type' => 'text', 'align' => 'center', 'text' => '----------------------'];
            }
        }

        // Información de la venta
        $content[] = ['type' => 'text', 'align' => 'left', 'text' => 'Orden: ' . $sale->sale_no];
        if (!empty($sale->selected_number_name)) {
            $content[] = ['type' => 'text', 'align' => 'left', 'text' => 'Comanda #' . $sale->selected_number_name];
        }
        $content[] = ['type' => 'text', 'align' => 'left', 'text' => 'Fecha: ' . date($this->session->userdata('date_format'), strtotime($sale->sale_date)) . ' ' . date('H:i', strtotime($sale->order_time))];
        $content[] = ['type' => 'text', 'align' => 'left', 'text' => 'Cliente: ' . $sale->customer_name . $customer_phone];
        $content[] = ['type' => 'text', 'align' => 'left', 'text' => 'Vendedor: ' . $sale->waiter_name];
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => ''];
    
        // Detalles de los productos
        $content[] = ['type' => 'extremos', 'textLeft' => 'Descripción', 'textRight' => 'Importe'];
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => ''];
    
        $total_exonerado = 0;
        $total_gravado_5 = 0;
        $total_gravado_10 = 0;

        if (isset($sale->items)) {
            $totalItems = 0;
            foreach ($sale->items as $row) {
                // Cálculo de totales por tipo de IVA para FE
                $iva_txt = '';
                if ($datos_fe) {
                    $iva_tipo = floatval($row->iva_tipo);
                    $iva_txt = ' (10%)';
                    if ($iva_tipo == 5) {
                        $total_gravado_5 += $row->menu_price_with_discount;
                    $iva_txt = ' (5%)';
                    } elseif ($iva_tipo == 0) {
                        $total_exonerado += $row->menu_price_with_discount;
                        $iva_txt = ' (Exenta)';
                    } else {
                        $total_gravado_10 += $row->menu_price_with_discount;
                    }
                }

                $totalItems += $row->qty;
                $menu_unit_price = getAmtPCustom($row->menu_unit_price);
                $content[] = ['type' => 'text', 'align' => 'left', 'text' => $row->code . ' - ' . $row->menu_name];
                $content[] = ['type' => 'extremos', 'textLeft' => $row->qty . ' x ' . $menu_unit_price . $iva_txt, 'textRight' => getAmtPCustom($row->menu_price_without_discount)];
    
                if (count($row->modifiers) > 0) {
                    foreach ($row->modifiers as $modifier) {
                        $content[] = ['type' => 'extremos', 'textLeft' => ' + ' . $modifier->name, 'textRight' => getAmtPCustom($modifier->modifier_price)];
                    }
                }
            }
        }
    
        // Totales
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => ''];
        if ($sale->sub_total != $sale->total_payable) {
            $content[] = ['type' => 'extremos', 'textLeft' => 'Subtotal:', 'textRight' => getAmtPCustom($sale->sub_total)];
        }
    
        if ($sale->total_discount_amount && $sale->total_discount_amount != "0.00") {
            $content[] = ['type' => 'extremos', 'textLeft' => 'Descuento:', 'textRight' => getAmtPCustom($sale->total_discount_amount)];
        }
    
        if ($sale->delivery_charge && $sale->delivery_charge != "0.00") {
            $content[] = ['type' => 'extremos', 'textLeft' => 'Serv. Delivery:', 'textRight' => getPlanTextOrP($sale->delivery_charge)];
        }
    
        if ($this->session->userdata('collect_tax') == 'Yes' && $sale->sale_vat_objects != NULL) {
            foreach (json_decode($sale->sale_vat_objects) as $single_tax) {
                if ($single_tax->tax_field_amount && $single_tax->tax_field_amount != "0.00") {
                    $content[] = ['type' => 'extremos', 'textLeft' => $single_tax->tax_field_type . ':', 'textRight' => getAmtPCustom($single_tax->tax_field_amount)];
                }
            }
        }
        $content[] = ['type' => 'text', 'align' => 'right', 'text' => '----------' ];
        $content[] = ['type' => 'extremos', 'textLeft' => 'TOTAL:', 'textRight' => getAmtPCustom($sale->total_payable)];
    
        // ✅ Bloque de desglose de impuestos para Factura Electrónica
        if ($datos_fe) {
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => '----------------------'];
            $content[] = ['type' => 'extremos', 'textLeft' => 'Gravada 5%', 'textRight' => getAmtCustom($total_gravado_5)];
            $content[] = ['type' => 'extremos', 'textLeft' => 'Gravada 10%', 'textRight' => getAmtCustom($total_gravado_10)];
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'Detalle de Impuesto'];
            $content[] = ['type' => 'extremos', 'textLeft' => 'Exenta', 'textRight' => getAmtCustom(0)];
            $content[] = ['type' => 'extremos', 'textLeft' => 'IVA 5%', 'textRight' => getAmtCustom($datos_fe->iva5)];
            $content[] = ['type' => 'extremos', 'textLeft' => 'IVA 10%', 'textRight' => getAmtCustom($datos_fe->iva10)];
            $content[] = ['type' => 'extremos', 'textLeft' => 'Liquidación Total de IVA', 'textRight' => getAmtCustom(floatval($datos_fe->iva10) + floatval($datos_fe->iva5))];
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => '----------------------'];
        }

        if ($payments && !empty($payments)) {
            $content[] = ['type' => 'text', 'align' => 'right', 'text' => '----------' ];
            foreach ($payments as $payment) {
                $txt_point = '';
                if ($payment->id == 5) {
                    $txt_point = " (Puntos: " . $payment->usage_point . ")";
                }
                $content[] = ['type' => 'extremos', 'textLeft' => $payment->payment_name . $txt_point, 'textRight' => getAmtPCustom($payment->amount)];
            }
        }
        if($sale->due_amount && $sale->due_amount!="0.00"){
            $content[] = ['type' => 'extremos', 'textLeft' => lang('due_amount'), 'textRight' => getAmtCustom($sale->due_amount)];
        }
        
        if ($payments && !empty($payments)) {
            $content[] = ['type' => 'text', 'align' => 'right', 'text' => '----------' ];
        }
        // Pie del ticket
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => $company['footer']];

        $content[] = ['type' => 'text', 'align' => 'center', 'text' => "\n"];

        // ✅ Bloque final para Factura Electrónica (QR y textos)
        if ($datos_fe) {
            // $content[] = [
            //     "type" => "qr",
            //     "qrUrl" => $datos_fe->qr, // URL dinámica del QR
            //     "align" => "center"
            // ];
            // $content[] = ['type' => 'text', 'align' => 'center', 'text' => "\n"];
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'Consulte la validez de este documento'];
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'con el número de CDC:'];
            // Separar el CDC en dos líneas para mejor visualización
            if (isset($datos_fe->cdc) && strlen($datos_fe->cdc) == 44) {
                // Primera línea: 6 grupos de 4 (24 dígitos)
                $cdc_linea1 = substr($datos_fe->cdc, 0, 24);
                $content[] = ['type' => 'text', 'align' => 'center', 'text' => implode(' ', str_split($cdc_linea1, 4))];

                // Segunda línea: 5 grupos de 4 (20 dígitos)
                $cdc_linea2 = substr($datos_fe->cdc, 24);
                $content[] = ['type' => 'text', 'align' => 'center', 'text' => implode(' ', str_split($cdc_linea2, 4))];
            } else if (isset($datos_fe->cdc)) {
                // Si el CDC no tiene 44 dígitos, lo imprimimos como estaba para evitar errores
                $content[] = ['type' => 'text', 'align' => 'center', 'text' => chunk_split($datos_fe->cdc, 4, ' ')];
            }
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'En el portal E-kuatia:'];
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'https://ekuatia.set.gov.py/consultas'];
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => '----------------------'];
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'ESTE DOCUMENTO ES UNA REPRESENTACIÓN'];
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'GRÁFICA DE UN DOCUMENTO ELECTRÓNICO (XML)'];

        }

        $content[] = ['type' => 'text', 'align' => 'center', 'text' => "\n"];
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => "\n"];
        $content[] = ['type' => 'cut'];
    
        // echo '<pre>';
        // var_dump($content); 
        // echo '<pre>';
        
        // Obtener la configuración de la impresora
        $company_id = $this->session->userdata('company_id');
        $company_data = $this->Common_model->getDataById($company_id, "tbl_companies");
        
        $counter_details = $this->Common_model->getPrinterIdByCounterId($this->session->userdata('counter_id'));
        $printer = $this->Common_model->getPrinterInfoById($counter_details->invoice_printer_id);
        $path = @$printer->path;
    
        $print_format = $company_data->print_format_bill;
        if($print_format=="80mm"){
            $width = 80;
        } else {
            $width = 58;
        }
    
        // Crear el objeto de solicitud de impresión
        $printRequest = [
            'printer' => $path,
            'width' => $width,
            'content' => filterArrayRecursivelyEscPos($content)
        ];
    
        $data = json_encode($printRequest);
        $compressed = gzdeflate($data, 9);
        $base64 = base64_encode($compressed);
    
        return $base64;
    }

    public function printer_app_bill($sale_no) {
        // $sale_info = $this->get_all_information_of_a_sale($sale_no);
        // if (empty($sale_info)) {
        //     $sale_info = $this->get_all_information_of_a_sale_modify($sale_no);
        // }
        $data['sale_object'] = $this->get_all_information_of_a_sale($sale_no);
        $sale = $data['sale_object'];
    
        // Cargar los datos de la factura electrónica al principio
        $datos_fe = null;
        if (tipoFacturacion() == 'Py_FE') {
            $this->load->helper('factura_send');
            $datos_fe = fs_get_factura_details_by_sale_no($sale_no);
        }
        $customer = getCustomerData($sale->customer_id);
        $identImpuestoName = (tipoConsultaRuc() == 'RNC') ? 'RNC' : 'RUC' ;

        // Obtener la información de la empresa
        $company = [
            'name' => $this->session->userdata('outlet_name'),
            'address' => $this->session->userdata('address'),
            'phone' => $this->session->userdata('phone'),
            'invoice_logo' => $this->session->userdata('invoice_logo'),
            'footer' => $this->session->userdata('invoice_footer'),
        ];
    
        // Obtener la información de la venta
        $order_type = '';
        if ($sale->order_type == 1) {
            $order_type = 'A';
        } elseif ($sale->order_type == 2) {
            $order_type = 'B';
        } elseif ($sale->order_type == 3) {
            $order_type = 'C';
        }
    
        $customer_phone = (isset($sale->customer_phone) && $sale->customer_phone != '') ? ' (' . $sale->customer_phone . ')' : '';
        // Crear el contenido del ticket
        $content = [
            // Encabezado de la empresa
            ['type' => 'text', 'align' => 'center', 'text' => $company['name']],
            ['type' => 'text', 'align' => 'center', 'text' => $company['address']],
            ['type' => 'text', 'align' => 'center', 'text' => 'Tel: ' . $company['phone']],
            ['type' => 'text', 'align' => 'center', 'text' => ''],
        ];
        
        // ✅ Bloque para Factura Electrónica (Py_FE)
        if ($datos_fe) {
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => '----------------------'];
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'FACTURA ELECTRÓNICA'];
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'Timbrado Nº: ' . escape_output($datos_fe->numero_timbrado)];
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'Fecha Inicio Vigencia: ' . date('d/m/Y', strtotime($datos_fe->timbrado_vigente))];
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'Factura Electrónica: ' . escape_output($datos_fe->numero_factura_formateado)];
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'Fecha y hora de emisión: ' . date('d/m/Y H:i:s', strtotime($datos_fe->fecha_emision))];
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => '----------------------'];
            if ($customer) {
                $content[] = ['type' => 'text', 'align' => 'left', 'text' => 'Razón Social: ' . escape_output($customer->name)];
                $content[] = ['type' => 'text', 'align' => 'left', 'text' => 'RUC: ' . escape_output($customer->gst_number)];
                $content[] = ['type' => 'text', 'align' => 'left', 'text' => 'Condicion: Contado'];
                $content[] = ['type' => 'text', 'align' => 'center', 'text' => '----------------------'];
            }
        }

        // Información de la venta
        $content[] = ['type' => 'text', 'align' => 'left', 'text' => 'Orden: ' . $sale->sale_no];
        if (!empty($sale->selected_number_name)) {
            $content[] = ['type' => 'text', 'align' => 'left', 'text' => 'Comanda #' . $sale->selected_number_name];
        }
        $content[] = ['type' => 'text', 'align' => 'left', 'text' => 'Fecha: ' . date($this->session->userdata('date_format'), strtotime($sale->sale_date)) . ' ' . date('H:i', strtotime($sale->order_time))];
        $content[] = ['type' => 'text', 'align' => 'left', 'text' => 'Cliente: ' . $sale->customer_name . $customer_phone];
        $content[] = ['type' => 'text', 'align' => 'left', 'text' => 'Vendedor: ' . $sale->waiter_name];
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => ''];
    
        // Detalles de los productos
        $content[] = ['type' => 'extremos', 'textLeft' => 'Descripción', 'textRight' => 'Importe'];
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => ''];
    
        $total_exonerado = 0;
        $total_gravado_5 = 0;
        $total_gravado_10 = 0;
    
        if (isset($sale->items)) {
            $totalItems = 0;
            foreach ($sale->items as $row) {
                // Cálculo de totales por tipo de IVA para FE
                $iva_txt = '';
                if ($datos_fe) {
                    $iva_tipo = floatval($row->iva_tipo);
                    $iva_txt = ' (10%)';
                    if ($iva_tipo == 5) {
                        $total_gravado_5 += $row->menu_price_with_discount;
                    $iva_txt = ' (5%)';
                    } elseif ($iva_tipo == 0) {
                        $total_exonerado += $row->menu_price_with_discount;
                        $iva_txt = ' (Exenta)';
                    } else {
                        $total_gravado_10 += $row->menu_price_with_discount;
                    }
                }
                $totalItems += $row->qty;
                $menu_unit_price = getAmtPCustom($row->menu_unit_price);
                $content[] = ['type' => 'text', 'align' => 'left', 'text' => $row->menu_name];
                $content[] = ['type' => 'extremos', 'textLeft' => $row->qty . ' x ' . $menu_unit_price . $iva_txt, 'textRight' => getAmtPCustom($row->menu_price_without_discount)];
    
                // Agregar modificadores si existen
                if (count($row->modifiers) > 0) {
                    foreach ($row->modifiers as $modifier) {
                        $content[] = ['type' => 'extremos', 'textLeft' => ' + ' . $modifier->name, 'textRight' => getAmtPCustom($modifier->modifier_price)];
                    }
                }
            }
        }
    
        // Totales
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => ''];
        // $content[] = ['type' => 'text', 'align' => 'left', 'text' => 'Total Items: ' . $totalItems];
        $content[] = ['type' => 'text', 'align' => 'right', 'text' => 'Subtotal: ' . getAmtPCustom($sale->sub_total)];
    
        if ($sale->total_discount_amount && $sale->total_discount_amount != "0.00") {
            $content[] = ['type' => 'text', 'align' => 'right', 'text' => 'Descuento: ' . getAmtPCustom($sale->total_discount_amount)];
        }
    
        if ($sale->delivery_charge && $sale->delivery_charge != "0.00") {
            $content[] = ['type' => 'text', 'align' => 'right', 'text' => 'Serv. Delivery: ' . getPlanTextOrP($sale->delivery_charge)];
        }
    
        if ($this->session->userdata('collect_tax') == 'Yes' && $sale->sale_vat_objects != NULL) {
            foreach (json_decode($sale->sale_vat_objects) as $single_tax) {
                if ($single_tax->tax_field_amount && $single_tax->tax_field_amount != "0.00") {
                    $content[] = ['type' => 'text', 'align' => 'right', 'text' => $single_tax->tax_field_type . ': ' . getAmtPCustom($single_tax->tax_field_amount)];
                }
            }
        }
        
        $content[] = ['type' => 'text', 'align' => 'right', 'text' => '----------' ];
        $content[] = ['type' => 'extremos', 'textLeft' => 'TOTAL:', 'textRight' => getAmtPCustom($sale->total_payable)];
    
        // ✅ Bloque de desglose de impuestos para Factura Electrónica
        if ($datos_fe) {
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => '----------------------'];
            $content[] = ['type' => 'extremos', 'textLeft' => 'Gravada 5%', 'textRight' => getAmtCustom($total_gravado_5)];
            $content[] = ['type' => 'extremos', 'textLeft' => 'Gravada 10%', 'textRight' => getAmtCustom($total_gravado_10)];
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'Detalle de Impuesto'];
            $content[] = ['type' => 'extremos', 'textLeft' => 'Exenta', 'textRight' => getAmtCustom(0)];
            $content[] = ['type' => 'extremos', 'textLeft' => 'IVA 5%', 'textRight' => getAmtCustom($datos_fe->iva5)];
            $content[] = ['type' => 'extremos', 'textLeft' => 'IVA 10%', 'textRight' => getAmtCustom($datos_fe->iva10)];
            $content[] = ['type' => 'extremos', 'textLeft' => 'Liquidación Total de IVA', 'textRight' => getAmtCustom(floatval($datos_fe->iva10) + floatval($datos_fe->iva5))];
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => '----------------------'];
        }
    
        // Pie del ticket
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => $company['footer']];
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => "\n"];
        
        // ✅ Bloque final para Factura Electrónica (QR y textos)
        if ($datos_fe) {
            // $content[] = [
            //     "type" => "qr",
            //     "qrUrl" => $datos_fe->qr, // URL dinámica del QR
            //     "align" => "center"
            // ];
            // $content[] = ['type' => 'text', 'align' => 'center', 'text' => "\n"];
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'Consulte la validez de este documento'];
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'con el número de CDC:'];
            // Separar el CDC en dos líneas para mejor visualización
            if (isset($datos_fe->cdc) && strlen($datos_fe->cdc) == 44) {
                // Primera línea: 6 grupos de 4 (24 dígitos)
                $cdc_linea1 = substr($datos_fe->cdc, 0, 24);
                $content[] = ['type' => 'text', 'align' => 'center', 'text' => implode(' ', str_split($cdc_linea1, 4))];

                // Segunda línea: 5 grupos de 4 (20 dígitos)
                $cdc_linea2 = substr($datos_fe->cdc, 24);
                $content[] = ['type' => 'text', 'align' => 'center', 'text' => implode(' ', str_split($cdc_linea2, 4))];
            } else if (isset($datos_fe->cdc)) {
                // Si el CDC no tiene 44 dígitos, lo imprimimos como estaba para evitar errores
                $content[] = ['type' => 'text', 'align' => 'center', 'text' => chunk_split($datos_fe->cdc, 4, ' ')];
            }
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'En el portal E-kuatia:'];
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'https://ekuatia.set.gov.py/consultas'];
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => '----------------------'];
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'ESTE DOCUMENTO ES UNA REPRESENTACIÓN'];
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'GRÁFICA DE UN DOCUMENTO ELECTRÓNICO (XML)'];

        }

        $content[] = ['type' => 'text', 'align' => 'center', 'text' => "\n"];
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => "\n"];
        $content[] = ['type' => 'cut'];
    
        // Obtener la configuración de la impresora
        $company_id = $this->session->userdata('company_id');
        $company_data = $this->Common_model->getDataById($company_id, "tbl_companies");
        // $printer = getPrinterInfo(isset($company_data->receipt_printer_bill) && $company_data->receipt_printer_bill ? $company_data->receipt_printer_bill : '');
        // $printer_id = $this->session->userdata('printer_id');
        // $printer = getPrinterInfo(isset($printer_id) && $printer_id?$printer_id:'');
        
        $counter_details = $this->Common_model->getPrinterIdByCounterId($this->session->userdata('counter_id'));
        $printer = $this->Common_model->getPrinterInfoById($counter_details->invoice_printer_id);
        $path = @$printer->path;
    
        $print_format = $company_data->print_format_bill;
        if($print_format=="80mm"){
            $width = 80;
        } else {
            $width = 58;
        }
    
        // echo '<pre>';
        // var_dump($printer); 
        // echo '<pre>';
        
        // Crear el objeto de solicitud de impresión
        $printRequest = [
            'printer' => $path, // Nombre de la impresora
            'width' => $width, // Ancho de impresión (80mm)
            'content' => filterArrayRecursivelyEscPos($content)
        ];
    
        // Convertir a JSON
        $data = json_encode($printRequest);
        // echo '<pre>';
        // var_dump($printRequest); 
        // echo '<pre>';
        
        // Comprimir y codificar en Base64
        $compressed = gzdeflate($data, 9);
        $base64 = base64_encode($compressed);
    
        // Devolver el contenido codificado
        echo $base64;
    }

    public function printer_app_kot($sale_no) {
        // Obtener el parámetro print_all (true por defecto si no se especifica)
        // $print_all = (isset($_GET['print_all']) && $_GET['print_all'] === 'true') ? true : false;
        // $is_print = ($print_all == true) ? 'all' : 0;
        $print_all = true;
        $is_print = 'all';
        // log_message('error', json_encode($_GET['print_all']));
        $sale_d = getKitchenSaleDetailsBySaleNo($sale_no);
        if (empty($sale_d)) {
            $sale_id = '';
            return;
        } else {
            $sale_id = $sale_d->id;
            $printers_array = [];
            $sale_object = $this->get_all_information_of_a_sale($sale_no);
            $printers_printer_app = $this->Common_model->getOrderedPrinter($sale_id,3);
            
            foreach ($printers_printer_app as $ky=>$value) {
                if(isset($value->id) && $value->id) {
                    $sale_items = $this->Common_model->getAllKitchenItemsAuto($sale_id,$value->id,$is_print);
                    
                    // Filtrar items si print_all es false
                    $items_to_print = [];
                    $items_count = 0;
                    
                    if (!$print_all) {
                        foreach($sale_items as $single_item_by_sale_id) {
                            if ($single_item_by_sale_id->is_print == 0) {
                                $items_to_print[] = $single_item_by_sale_id;
                                $items_count++;
                                
                                // Actualizar el estado de impresión
                                $item_data['is_print'] = 1;
                                $this->Common_model->updateInformation($item_data, $single_item_by_sale_id->sales_details_id, "tbl_kitchen_sales_details");
                            }
                        }
                    } else {
                        $items_to_print = $sale_items;
                        $items_count = count($sale_items);
                    }
                    
                    // Si no hay items para imprimir y no es print_all, saltar esta impresora
                    if ($items_count == 0 && !$print_all) {
                        continue;
                    }
                    
                    // Obtener modificadores para cada ítem
                    foreach($items_to_print as $single_item_by_sale_id) {
                        $modifier_information = $this->Sale_model->getModifiersBySaleAndSaleDetailsIdKitchenAuto($sale_id,$single_item_by_sale_id->sales_details_id);
                        $single_item_by_sale_id->modifiers = $modifier_information;
                    }
                    
                    // Crear el contenido del ticket de cocina
                    $content = [
                        // Encabezado de la empresa (solo nombre)
                        ['type' => 'text', 'align' => 'center', 'text' => $this->session->userdata('outlet_name')],
                        ['type' => 'text', 'align' => 'center', 'text' => 'TICKET DE COCINA'],
                        ['type' => 'text', 'align' => 'center', 'text' => ''],
                        
                        // Información de la venta
                        ['type' => 'text', 'align' => 'left', 'text' => 'Orden: ' . $sale_object->sale_no],
                        ['type' => 'text', 'align' => 'left', 'text' => 'Comanda #' . $sale_object->selected_number_name],
                        ['type' => 'text', 'align' => 'left', 'text' => 'Fecha: ' . date($this->session->userdata('date_format'), strtotime($sale_object->sale_date)) . ' ' . date('H:i', strtotime($sale_object->order_time))],
                        ['type' => 'text', 'align' => 'left', 'text' => 'Cliente: ' . $sale_object->customer_name],
                        ['type' => 'text', 'align' => 'left', 'text' => 'Mesero: ' . $sale_object->waiter_name],
                        ['type' => 'text', 'align' => 'center', 'text' => ''],
                        
                        // Encabezado de items
                        ['type' => 'text', 'align' => 'center', 'text' => '------------------------------'],
                    ];
                    
                    // Detalles de los productos con sus modificadores
                    if (!empty($items_to_print)) {
                        foreach ($items_to_print as $row) {
                            $content[] = ['type' => 'text', 'align' => 'left', 'text' => $row->qty . '    ' . $row->menu_name];
                            
                            // Agregar modificadores si existen
                            if (!empty($row->modifiers)) {
                                foreach ($row->modifiers as $modifier) {
                                    $content[] = ['type' => 'text', 'align' => 'left', 'text' => '   + ' . $modifier->name];
                                }
                            }
                            
                            // Agregar nota del item si existe
                            if (!empty($row->item_note)) {
                                $content[] = ['type' => 'text', 'align' => 'left', 'text' => 'Nota: ' . $row->item_note];
                            }
                            
                            $content[] = ['type' => 'text', 'align' => 'center', 'text' => '---'];
                        }
                    }
                    
                    // Pie del ticket
                    $content[] = ['type' => 'text', 'align' => 'center', 'text' => '******************************'];
                    $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'Impreso: ' . date('H:i')];
                    $content[] = ['type' => 'text', 'align' => 'center', 'text' => ''];
                    $content[] = ['type' => 'cut'];
                    
                    // Configuración de la impresora
                    $company_id = $this->session->userdata('company_id');
                    $company_data = $this->Common_model->getDataById($company_id, "tbl_companies");
                    $path = $value->path;
                    
                    $print_format = $value->print_format; // Formato estándar para tickets de cocina
                    $width = ($print_format == "80mm") ? 80 : 58;
                    
                    // Crear el objeto de solicitud de impresión
                    $printRequest = [
                        'printer' => $path,
                        'width' => $width,
                        'content' => filterArrayRecursivelyEscPos($content)
                    ];
                    
                    // Convertir a JSON, comprimir y codificar en Base64
                    $data = json_encode($printRequest);
                    $compressed = gzdeflate($data, 9);
                    $base64 = base64_encode($compressed);
                    
                    $printers_array[] = $base64;
                }
            }
            
            // Si no hay nada para imprimir, devolver array vacío
            echo empty($printers_array) ? json_encode([]) : json_encode($printers_array);
        }
        return;
    }
    
    public function printer_app_kot_qz($sale_no, $print_all = true) {
        // Obtener el parámetro print_all
        $print_all = filter_var($print_all, FILTER_VALIDATE_BOOLEAN);
        $is_print = $print_all ? 'all' : 0;
        
        // Obtener información de la venta
        $sale_d = getKitchenSaleDetailsBySaleNo($sale_no);
        if (empty($sale_d)) {
            return json_encode([
                'success' => false,
                'message' => 'Venta no encontrada',
                'tickets' => []
            ]);
        }
    
        $sale_id = $sale_d->id;
        $printers_array = [];
        $sale_object = $this->get_all_information_of_a_sale($sale_no);
        $printers_printer_app = $this->Common_model->getOrderedPrinter($sale_id, 3);
        
        foreach ($printers_printer_app as $ky => $value) {
            if (!isset($value->id) || !$value->id) {
                continue;
            }
    
            // Obtener items para imprimir
            $sale_items = $this->Common_model->getAllKitchenItemsAuto($sale_id, $value->id, $is_print);
            $items_to_print = [];
            
            if (!$print_all) {
                // Filtrar solo items no impresos
                foreach ($sale_items as $single_item_by_sale_id) {
                    if ($single_item_by_sale_id->is_print == 0) {
                        $items_to_print[] = $single_item_by_sale_id;
                        
                        // Actualizar estado de impresión
                        $item_data['is_print'] = 1;
                        $this->Common_model->updateInformation(
                            $item_data, 
                            $single_item_by_sale_id->sales_details_id, 
                            "tbl_kitchen_sales_details"
                        );
                    }
                }
            } else {
                $items_to_print = $sale_items;
            }
    
            // Si no hay items para imprimir y no es print_all, saltar
            if (empty($items_to_print) && !$print_all) {
                continue;
            }
    
            // Obtener modificadores para cada ítem
            foreach ($items_to_print as $single_item_by_sale_id) {
                $modifier_information = $this->Sale_model->getModifiersBySaleAndSaleDetailsIdKitchenAuto(
                    $sale_id,
                    $single_item_by_sale_id->sales_details_id
                );
                $single_item_by_sale_id->modifiers = $modifier_information;
            }
    
            // Construir contenido del ticket para QZ Tray
            $content = [];
            
            // Encabezado
            $content[] = [
                'type' => 'text',
                'data' => $this->session->userdata('outlet_name') . "\n",
                'alignment' => 'center',
                'bold' => true,
                'fontsize' => 2
            ];
            
            $content[] = [
                'type' => 'text',
                'data' => "TICKET DE COCINA\n\n",
                'alignment' => 'center',
                'bold' => true
            ];
            
            // Información de la venta
            $content[] = [
                'type' => 'text',
                'data' => "Orden: " . $sale_object->sale_no . "\n",
                'alignment' => 'left'
            ];
            
            $content[] = [
                'type' => 'text',
                'data' => "Comanda #" . $sale_object->selected_number_name . "\n",
                'alignment' => 'left'
            ];
            
            $content[] = [
                'type' => 'text',
                'data' => "Fecha: " . date($this->session->userdata('date_format'), strtotime($sale_object->sale_date)) . 
                         " " . date('H:i', strtotime($sale_object->order_time)) . "\n",
                'alignment' => 'left'
            ];
            
            $content[] = [
                'type' => 'text',
                'data' => "Cliente: " . $sale_object->customer_name . "\n",
                'alignment' => 'left'
            ];
            
            $content[] = [
                'type' => 'text',
                'data' => "Mesero: " . $sale_object->waiter_name . "\n\n",
                'alignment' => 'left'
            ];
            
            $content[] = [
                'type' => 'text',
                'data' => "------------------------------\n",
                'alignment' => 'center'
            ];
            
            // Detalles de los productos
            if (!empty($items_to_print)) {
                foreach ($items_to_print as $row) {
                    $content[] = [
                        'type' => 'text',
                        'data' => $row->qty . "    " . $row->menu_name . "\n",
                        'alignment' => 'left'
                    ];
                    
                    // Modificadores
                    if (!empty($row->modifiers)) {
                        foreach ($row->modifiers as $modifier) {
                            $content[] = [
                                'type' => 'text',
                                'data' => "   + " . $modifier->name . "\n",
                                'alignment' => 'left'
                            ];
                        }
                    }
                    
                    // Notas del item
                    if (!empty($row->item_note)) {
                        $content[] = [
                            'type' => 'text',
                            'data' => "Nota: " . $row->item_note . "\n",
                            'alignment' => 'left'
                        ];
                    }
                    
                    $content[] = [
                        'type' => 'text',
                        'data' => "---\n",
                        'alignment' => 'center'
                    ];
                }
            }
            
            // Pie del ticket
            $content[] = [
                'type' => 'text',
                'data' => "******************************\n",
                'alignment' => 'center'
            ];
            
            $content[] = [
                'type' => 'text',
                'data' => "Impreso: " . date('H:i') . "\n\n",
                'alignment' => 'center'
            ];
            
            $content[] = [
                'type' => 'cut'
            ];
            
            // Configuración de impresión
            $company_id = $this->session->userdata('company_id');
            $company_data = $this->Common_model->getDataById($company_id, "tbl_companies");
            $print_format = $value->print_format; // Formato estándar para tickets de cocina
            
            // Agregar ticket a la lista
            $printers_array[] = [
                'printer' => $value->path, // Nombre o IP de la impresora
                'content' => $content,
                'config' => [
                    'encoding' => 'UTF-8',
                    'endOfDoc' => '\x1B@\x1Bm', // Reset printer
                    'perSpool' => 1,
                    'paperWidth' => $print_format == "80mm" ? 80 : 58
                ],
                'metadata' => [
                    'sale_no' => $sale_no,
                    'printer_id' => $value->id,
                    'printer_name' => $value->printer_name
                ]
            ];
        }
        
        echo json_encode([
            'success' => !empty($printers_array),
            'message' => !empty($printers_array) ? 
                        count($printers_array) . ' tickets generados' : 
                        'No hay tickets para imprimir',
            'tickets' => $printers_array
        ]);
    }

    public function printer_app_register_report() {
        $base64 = $this->printer_app_register_report_data();
        echo $base64;
    }
    
    public function printer_app_register_report_data($register_id = null) {
        if ($register_id) {
            // Trae los datos del registro con la ID específica
            $register = $this->db->where('id', $register_id)->get('tbl_register')->row();
            if (!$register) {
                return null; // O maneja error
            }
            $outlet_id = $register->outlet_id;
            $user_id = $register->user_id;
            $opening_date_time = $register->opening_balance_date_time;
            $closing_date_time = $register->closing_balance_date_time;
            $opening_details = $register->opening_details;
        } else {
            // Datos actuales de sesión
            $outlet_id = $this->session->userdata('outlet_id');
            $user_id = $this->session->userdata('user_id');
            $opening_date_time = $this->getOpeningDateTime();
            $closing_date_time = $this->getClosingDateTime();
            $opening_details = $this->getOpeningDetails();
        }
        $opening_details_decode = json_decode($opening_details);
    
        // Obtener opciones de outlet
        $getOutletInfo = $this->Common_model->getDataById($outlet_id, "tbl_outlets");
        $registro_ocultar = $getOutletInfo->registro_ocultar;
        $registro_detallado = $getOutletInfo->registro_detallado;

        // Información de la empresa
        $company = [
            'name' => $this->session->userdata('outlet_name'),
            'address' => $this->session->userdata('address'),
            'phone' => $this->session->userdata('phone'),
            'invoice_logo' => $this->session->userdata('invoice_logo'),
            'footer' => $this->session->userdata('invoice_footer'),
        ];
    
        $content = [
            ['type' => 'text', 'align' => 'center', 'text' => 'REPORTE DE CIERRE DE CAJA'],
            ['type' => 'text', 'align' => 'center', 'text' => ''],
            ['type' => 'text', 'align' => 'left', 'text' => 'Sucursal: ' . $company['name']],
            ['type' => 'text', 'align' => 'left', 'text' => 'Caja: ' . getCounterName($this->session->userdata('counter_id'))],
            ['type' => 'text', 'align' => 'left', 'text' => 'Apertura: ' . date('Y-m-d h:i A', strtotime($opening_date_time))],
            ['type' => 'text', 'align' => 'left', 'text' => 'Cierre: ' . ($closing_date_time ? $closing_date_time : date('Y-m-d h:i A') )], //date('Y-m-d h:i A')
            ['type' => 'text', 'align' => 'left', 'text' => 'Usuario: ' . $this->session->userdata('full_name')],
            ['type' => 'text', 'align' => 'center', 'text' => ''],
            // ['type' => 'text', 'align' => 'center', 'text' => '------------------------------'],
            // ['type' => 'text', 'align' => 'center', 'text' => ''],
        ];
    
        $fromDate = $opening_date_time;
        $toDate = $closing_date_time ? $closing_date_time : date('Y-m-d H:i:s');
        // Tabla de ventas detalladas si "registro_detallado"
        if($registro_detallado === "Yes"){
            $detailed_sales = $this->Sale_model->getDetailedSales($outlet_id, $fromDate, $toDate);
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'DETALLE DE VENTAS'];
            $total_ventas_detalladas = 0;
            foreach ($detailed_sales as $sale) {
                $content[] = [
                    'type' => 'extremos',
                    'textLeft' => '#'.$sale->number_slot_name.' '.$sale->sale_no,
                    'textRight' => getAmtPCustom($sale->amount)
                ];
                $total_ventas_detalladas += $sale->amount;
            }
            $content[] = [
                'type' => 'extremos',
                'textLeft' => '---------------',
                'textRight' => '---------------'
            ];
            $content[] = [
                'type' => 'extremos',
                'textLeft' => 'TOTAL VENTAS',
                'textRight' => getAmtPCustom($total_ventas_detalladas)
            ];
        }
        $summary_names = [];
        $summary_amounts = [];
        
        if($registro_ocultar != "Yes"){
            if ($opening_details_decode) {
                foreach ($opening_details_decode as $key => $value) {
                    $payments = explode('||', $value);
        
                    $payment_id = $payments[0];
                    $payment_name = $payments[1];
                    $opening_balance = (float) $payments[2];
        
                    $total_purchase = (float) $this->Sale_model->getAllPurchaseByPayment($opening_date_time, $payment_id);
                    $total_due_receive = (float) $this->Sale_model->getAllDueReceiveByPayment($opening_date_time, $payment_id);
                    $total_due_payment = (float) $this->Sale_model->getAllDuePaymentByPayment($opening_date_time, $payment_id);
                    $total_expense = (float) $this->Sale_model->getAllExpenseByPayment($opening_date_time, $payment_id);
                    $refund_amount = (float) $this->Sale_model->getAllRefundByPayment($opening_date_time, $payment_id);
                    $total_sale = (float) $this->Sale_model->getAllSaleByPayment($opening_date_time, $payment_id);
        
                    $closing_balance = $opening_balance - $total_purchase + $total_sale + $total_due_receive - $total_due_payment - $total_expense - $refund_amount;
        
                    // Checar si todos los valores están en 0
                    $all_zeros = (
                        $opening_balance == 0 &&
                        $total_purchase == 0 &&
                        $total_due_receive == 0 &&
                        $total_due_payment == 0 &&
                        $total_expense == 0 &&
                        $refund_amount == 0 &&
                        $total_sale == 0 &&
                        $closing_balance == 0
                    );
        
                    // Si todos los valores son 0, no imprimir este método de pago
                    if ($all_zeros) {
                        continue;
                    }
        
                    // Guardar para resumen final
                    $summary_names[] = $payment_name;
                    $summary_amounts[] = $closing_balance;
        
                    // Detalle por método de pago
                    $detail_section = [];
        
                    $detail_section[] = ['type' => 'text', 'align' => 'center', 'text' => '------------------------------'];
                    $detail_section[] = ['type' => 'text', 'align' => 'center', 'text' => strtoupper($payment_name)];
        
                    if ($opening_balance != 0) {
                        $detail_section[] = ['type' => 'extremos', 'textLeft' => 'Saldo inicial', 'textRight' => getAmtPCustom($opening_balance)];
                    }
        
                    if ($total_purchase != 0) {
                        $detail_section[] = ['type' => 'extremos', 'textLeft' => lang('register_detail_2'), 'textRight' => getAmtPCustom($total_purchase)];
                    }
        
                    if ($total_sale != 0) {
                        $detail_section[] = ['type' => 'extremos', 'textLeft' => lang('register_detail_3'), 'textRight' => getAmtPCustom($total_sale)];
                    }
        
                    // Multimoneda solo si es efectivo (id==1)
                    if ($payment_id == 1) {
                        $total_sale_mul_c_rows = $this->Sale_model->getAllSaleByPaymentMultiCurrencyRows($opening_date_time, $payment_id);
                        if ($total_sale_mul_c_rows) {
                            foreach ($total_sale_mul_c_rows as $value1) {
                                if ((float)$value1->total_amount != 0) {
                                    $detail_section[] = [
                                        'type' => 'extremos',
                                        'textLeft' => '    ' . $value1->multi_currency,
                                        'textRight' => number_format($value1->total_amount,2, ',', '.')
                                    ];
                                }
                            }
                        }
                    }
        
                    if ($total_due_receive != 0) {
                        $detail_section[] = ['type' => 'extremos', 'textLeft' => lang('register_detail_5'), 'textRight' => getAmtPCustom($total_due_receive)];
                    }
                    if ($total_due_payment != 0) {
                        $detail_section[] = ['type' => 'extremos', 'textLeft' => lang('register_detail_6'), 'textRight' => getAmtPCustom($total_due_payment)];
                    }
                    if ($total_expense != 0) {
                        $detail_section[] = ['type' => 'extremos', 'textLeft' => lang('register_detail_7'), 'textRight' => getAmtPCustom($total_expense)];
                    }
                    if ($refund_amount != 0) {
                        $detail_section[] = ['type' => 'extremos', 'textLeft' => lang('refund_amount') . '(-)', 'textRight' => getAmtPCustom($refund_amount)];
                    }
        
                    // Línea de total solo si el closing_balance es distinto de 0
                    if ($closing_balance != 0) {
                        // $detail_section[] = ['type' => 'text', 'align' => 'center', 'text' => '------------------------------'];
                        $detail_section[] = ['type' => 'text', 'align' => 'center', 'text' => ''];
                        $detail_section[] = ['type' => 'extremos', 'textLeft' => 'TOTAL ' . strtoupper($payment_name), 'textRight' => getAmtPCustom($closing_balance)];
                    }
        
                    $detail_section[] = ['type' => 'text', 'align' => 'center', 'text' => ''];
                    // Añadir sección de detalles al contenido final
                    $content = array_merge($content, $detail_section);
                }
            }
        }

        // Obtener gastos detallados
        $from_datetime = $opening_date_time;
        $to_datetime = $closing_date_time ? $closing_date_time : date('Y-m-d H:i:s');
        $counter_id = $this->session->userdata('counter_id');
        $user_id = $this->session->userdata('user_id');
        $outlet_id = $this->session->userdata('outlet_id');

        $gastos = $this->Sale_model->getDetailedExpenses($from_datetime, $to_datetime, $user_id, $outlet_id);

        if (!empty($gastos)) {
            $total_gastos = 0;
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'DETALLE DE GASTOS'];
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => '------------------------------'];
            foreach ($gastos as $gasto) {
                if (floatval($gasto->amount) != 0) {
                    $total_gastos += floatval($gasto->amount);
                    $nota = $gasto->note ? $gasto->note : '(Sin nota)';
                    $content[] = [
                        'type' => 'extremos',
                        'textLeft' => $nota,
                        'textRight' => getAmtPCustom($gasto->amount)
                    ];
                }
            }
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => '------------------------------'];
            $content[] = [
                'type' => 'extremos',
                'textLeft' => 'TOTAL GASTOS',
                'textRight' => getAmtPCustom($total_gastos)
            ];
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => ''];
        }

        if($registro_ocultar != "Yes"){
            // Agregar resumen final (sólo los mayores a 0 o menores a 0)
            if (!empty($summary_names)) {
                $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'RESUMEN FINAL'];
                $content[] = ['type' => 'text', 'align' => 'center', 'text' => '------------------------------'];
                foreach ($summary_names as $i => $name) {
                    if ($summary_amounts[$i] != 0) {
                        $content[] = [
                            'type' => 'extremos',
                            'textLeft' => $name,
                            'textRight' => getAmtPCustom($summary_amounts[$i])
                        ];
                    }
                }
                $content[] = ['type' => 'text', 'align' => 'center', 'text' => '------------------------------'];
                $content[] = ['type' => 'text', 'align' => 'center', 'text' => ''];
            }
        }

        if ($register_id) {
            $declaraciones = $this->db
                ->where('register_id', $register_id)
                ->order_by('id', 'asc')
                ->get('tbl_register_statement')
                ->result();
            if (!empty($declaraciones)) {
                $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'DECLARACION DE CIERRE'];
                $content[] = ['type' => 'text', 'align' => 'center', 'text' => '------------------------------'];
                foreach ($declaraciones as $declaracion) {
                    $content[] = [
                        'type' => 'extremos',
                        'textLeft' => $declaracion->payment_txt ?: $declaracion->payment_id,
                        'textRight' => getAmtPCustom($declaracion->mount)
                    ];
                }
                $content[] = ['type' => 'text', 'align' => 'center', 'text' => '------------------------------'];
                $content[] = ['type' => 'text', 'align' => 'center', 'text' => ''];
            }
        }
    
        // Pie del ticket
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'FIN REPORTE DE CAJA'];
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => ''];
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => ''];
        $content[] = ['type' => 'cut'];
    
        // Configuración de la impresora (igual que antes)
        $company_id = $this->session->userdata('company_id');
        $company_data = $this->Common_model->getDataById($company_id, "tbl_companies");
        $counter_details = $this->Common_model->getPrinterIdByCounterId($this->session->userdata('counter_id'));
        $printer = $this->Common_model->getPrinterInfoById($counter_details->invoice_printer_id);
        $path = @$printer->path;
    
        $print_format = $company_data->print_format_bill;
        $width = ($print_format == "80mm") ? 80 : 58;
    
        $printRequest = [
            'printer' => $path,
            'width' => $width,
            'content' => filterArrayRecursivelyEscPos($content)
        ];
    
        $data = json_encode($printRequest);
        $compressed = gzdeflate($data, 9);
        $base64 = base64_encode($compressed);
    
        return $base64;
    }

    public function printer_app_register_reportOld() {
        // Obtener los datos del informe de caja
        $register_detail = $this->registerDetailCalculationToShow();
        
        // Obtener la información de la empresa
        $company = [
            'name' => $this->session->userdata('outlet_name'),
            'address' => $this->session->userdata('address'),
            'phone' => $this->session->userdata('phone'),
            'invoice_logo' => $this->session->userdata('invoice_logo'),
            'footer' => $this->session->userdata('invoice_footer'),
        ];
    
        // Crear el contenido del ticket
        $content = [
            // Encabezado de la empresa
            ['type' => 'text', 'align' => 'center', 'text' => $company['name']],
            ['type' => 'text', 'align' => 'center', 'text' => $company['address']],
            ['type' => 'text', 'align' => 'center', 'text' => 'Tel: ' . $company['phone']],
            ['type' => 'text', 'align' => 'center', 'text' => ''],
            
            // Título del reporte
            ['type' => 'text', 'align' => 'center', 'text' => 'REPORTE DE CIERRE DE CAJA'],
            ['type' => 'text', 'align' => 'center', 'text' => ''],
            
            // Información del cierre
            ['type' => 'text', 'align' => 'left', 'text' => 'Caja: ' . getCounterName($this->session->userdata('counter_id'))],
            ['type' => 'text', 'align' => 'left', 'text' => 'Apertura: ' . $register_detail['opening_date_time']],
            ['type' => 'text', 'align' => 'left', 'text' => 'Cierre: ' . $register_detail['closing_date_time']],
            ['type' => 'text', 'align' => 'center', 'text' => ''],
            ['type' => 'text', 'align' => 'center', 'text' => '------------------------------'],
            ['type' => 'text', 'align' => 'center', 'text' => ''],
        ];
    
        // Parsear el HTML para extraer los datos relevantes
        $dom = new DOMDocument();
        @$dom->loadHTML($register_detail['html_content_for_div']);
        $rows = $dom->getElementsByTagName('tr');
        
        $payment_methods = [];
        $current_payment = null;
        $payment_index = -1;
        
        foreach ($rows as $row) {
            $cells = $row->getElementsByTagName('td');
            $th_cells = $row->getElementsByTagName('th');
            
            // Buscar filas que contengan métodos de pago (filas con th)
            if ($th_cells->length >= 4 && trim($th_cells->item(1)->nodeValue) != '' && 
                !in_array(trim($th_cells->item(2)->nodeValue), ['Transactions', 'summary'])) {
                // Es un método de pago nuevo
                $payment_index++;
                $payment_methods[$payment_index] = [
                    'name' => trim($th_cells->item(1)->nodeValue),
                    'opening_balance' => trim($th_cells->item(3)->nodeValue),
                    'transactions' => []
                ];
                $current_payment = &$payment_methods[$payment_index];
            }
            // Buscar transacciones (filas con td)
            elseif ($cells->length >= 4 && trim($cells->item(2)->nodeValue) != '') {
                // Es una transacción
                $transaction = [
                    'description' => trim($cells->item(2)->nodeValue),
                    'amount' => trim($cells->item(3)->nodeValue)
                ];
                if ($current_payment) {
                    $current_payment['transactions'][] = $transaction;
                }
            }
            // Buscar closing balance (filas con th que contengan "closing_balance")
            elseif ($th_cells->length >= 4 && strpos($th_cells->item(2)->getAttribute('class'), 'text_right') !== false) {
                if ($current_payment) {
                    $current_payment['closing_balance'] = trim($th_cells->item(3)->nodeValue);
                }
            }
        }
    
        // Agregar detalles de cada método de pago al contenido del ticket
        foreach ($payment_methods as $payment) {
            $content[] = ['type' => 'text', 'align' => 'left', 'text' => strtoupper($payment['name'])];
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => '------------------------------'];
            
            // Saldo inicial
            $content[] = ['type' => 'extremos', 
                         'textLeft' => 'Saldo inicial', 
                         'textRight' => $payment['opening_balance']];
            
            // Transacciones
            foreach ($payment['transactions'] as $transaction) {
                $content[] = ['type' => 'extremos', 
                             'textLeft' => $transaction['description'], 
                             'textRight' => $transaction['amount']];
            }
            
            // Saldo final
            if (isset($payment['closing_balance'])) {
                $content[] = ['type' => 'text', 'align' => 'center', 'text' => '------------------------------'];
                $content[] = ['type' => 'extremos', 
                             'textLeft' => 'TOTAL ' . $payment['name'], 
                             'textRight' => $payment['closing_balance']];
            }
            
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => ''];
        }
    
        // Resumen final (extraer del HTML)
        $summary_rows = $dom->getElementsByTagName('tr');
        $summary_data = [];
        
        foreach ($summary_rows as $row) {
            $th_cells = $row->getElementsByTagName('th');
            if ($th_cells->length >= 4 && trim($th_cells->item(2)->nodeValue) == 'summary') {
                // Encontramos el inicio del resumen, ahora capturamos las filas siguientes
                $next_rows = [];
                $sibling = $row->nextSibling;
                
                while ($sibling) {
                    if ($sibling->nodeName == 'tr') {
                        $summary_th = $sibling->getElementsByTagName('th');
                        if ($summary_th->length >= 4 && trim($summary_th->item(2)->nodeValue) != '') {
                            $summary_data[] = [
                                'name' => trim($summary_th->item(2)->nodeValue),
                                'amount' => trim($summary_th->item(3)->nodeValue)
                            ];
                        }
                    }
                    $sibling = $sibling->nextSibling;
                }
                break;
            }
        }
    
        // Agregar resumen final al ticket
        if (!empty($summary_data)) {
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'RESUMEN FINAL'];
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => '------------------------------'];
            
            foreach ($summary_data as $summary) {
                $content[] = ['type' => 'extremos', 
                             'textLeft' => $summary['name'], 
                             'textRight' => $summary['amount']];
            }
            
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => '------------------------------'];
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => ''];
        }
        
        // Pie del ticket
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => $company['footer']];
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => "\n"];
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => "\n"];
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => ''];
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => ''];
        $content[] = ['type' => 'cut'];
    
        // Configuración de la impresora (como la tenías)
        $company_id = $this->session->userdata('company_id');
        $company_data = $this->Common_model->getDataById($company_id, "tbl_companies");
        
        $counter_details = $this->Common_model->getPrinterIdByCounterId($this->session->userdata('counter_id'));
        $printer = $this->Common_model->getPrinterInfoById($counter_details->invoice_printer_id);
        $path = @$printer->path;
    
        $print_format = $company_data->print_format_bill;
        $width = ($print_format == "80mm") ? 80 : 58;
    
        // Crear el objeto de solicitud de impresión
        $printRequest = [
            'printer' => $path,
            'width' => $width,
            'content' => filterArrayRecursivelyEscPos($content)
        ];
    
        // Convertir a JSON, comprimir y codificar en Base64
        $data = json_encode($printRequest);
        $compressed = gzdeflate($data, 9);
        $base64 = base64_encode($compressed);
    
        // Devolver el contenido codificado
        echo $base64;
    }

    public function get_order_details_for_whatsapp($sale_id) {
        // Obtener la información de la venta
        $data['sale_object'] = $this->get_all_information_of_a_sale($sale_id);
        
        $sale = $data['sale_object'];
    
        // Obtener la información de la empresa
        $company = [
            'name' => $this->session->userdata('outlet_name'),
            'phone' => $this->session->userdata('phone'),
        ];
    
        // Determinar el tipo de orden
        $order_type = '';
        if ($sale->order_type == 1) {
            $order_type = 'A';
        } elseif ($sale->order_type == 2) {
            $order_type = 'B';
        } elseif ($sale->order_type == 3) {
            $order_type = 'C';
        }
    
        // Preparar los detalles de los ítems
        $items = [];
        if (isset($sale->items)) {
            foreach ($sale->items as $row) {
                $item = [
                    'name' => $row->menu_name,
                    'quantity' => $row->qty,
                    'price' => getAmtPCustom($row->menu_price_without_discount),
                ];
    
                // Agregar modificadores si existen
                if (count($row->modifiers) > 0) {
                    $item['modifiers'] = [];
                    foreach ($row->modifiers as $modifier) {
                        $item['modifiers'][] = [
                            'name' => $modifier->name,
                            'price' => getAmtPCustom($modifier->modifier_price),
                        ];
                    }
                }
    
                $items[] = $item;
            }
        }
    
        // Preparar la respuesta
        $response = [
            'customer_name' => $sale->customer_name,
            'phone' => $sale->customer_phone, // Asegúrate de que el campo 'phone' esté disponible en $sale
            'order_number' => $order_type . ' ' . $sale->sale_no,
            'items' => $items,
            'subtotal' => getAmtPCustom($sale->sub_total),
            'total_discount' => getAmtPCustom($sale->total_discount_amount),
            'delivery_charge' => getAmtPCustom($sale->delivery_charge),
            'total_tax' => 0, // Inicializar el total de impuestos
            'total_payable' => getAmtPCustom($sale->total_payable),
            'company_name' => $company['name'],
            'company_phone' => $company['phone'],
        ];
    
        // Calcular el total de impuestos si es necesario
        if ($this->session->userdata('collect_tax') == 'Yes' && $sale->sale_vat_objects != NULL) {
            $total_tax = 0;
            foreach (json_decode($sale->sale_vat_objects) as $single_tax) {
                if ($single_tax->tax_field_amount && $single_tax->tax_field_amount != "0.00") {
                    $total_tax += $single_tax->tax_field_amount;
                }
            }
            $response['total_tax'] = getAmtPCustom($total_tax);
        }
    
        // Devolver la respuesta en formato JSON
        echo json_encode($response);
    }

    public function test(){
        // Ejemplo de uso
        $precio = 12345.678;

        $formateado = getAmtPCustom($precio); // "12,345.68"
        $sinFormato = ($formateado); // 12345.68

        echo "Formateado: $formateado\n"; // "12,345.68"
        echo "<br>";
        echo "Desformateado: $sinFormato\n"; // 12345.68
    }

    // public function getUpdatedNumbers() {
    //     $outlet_id = $this->session->userdata('outlet_id');
    //     $this->db->select('id, sale_id, sale_no, user_id, name');
    //     $this->db->where("outlet_id", $outlet_id);
    //     $this->db->where("del_status", "Live");
    //     $this->db->where("sale_id IS NOT NULL"); // Solo números ocupados
    //     $numbers = $this->db->get("tbl_numeros")->result();
        
    //     return ($numbers);
    //     // return json_encode($numbers);
    // }
    public function liberar_numeros_old() {
        $outlet_id = $this->session->userdata('outlet_id');
        
        // Primero: Liberar números que ya tienen ventas concretadas
        $this->db->select('n.id, n.sale_id, n.sale_no, n.user_id, n.name');
        $this->db->from('tbl_numeros n');
        $this->db->join('tbl_sales s', 'n.sale_no = s.sale_no AND s.outlet_id = n.outlet_id AND s.del_status = "Live"', 'left');
        // $this->db->where("n.outlet_id", $outlet_id);
        $this->db->where("n.del_status", "Live");
        $this->db->where("n.sale_id IS NOT NULL"); // Solo números ocupados
        $this->db->where("s.id IS NOT NULL"); // Que tengan venta existente
        
        $numbersToFree = $this->db->get()->result();
        
        // Liberar los números que cumplen la condición
        if (!empty($numbersToFree)) {
            $idsToFree = array_column($numbersToFree, 'id');
            $this->db->where_in('id', $idsToFree);
            $this->db->update('tbl_numeros', array(
                'sale_id' => NULL,
                'sale_no' => NULL,
                'user_id' => NULL
            ));
        }
        echo '<pre>';
        var_dump($numbersToFree); 
        echo '<pre>';
        
    }

    public function liberar_numeros() {
        $outlet_id = $this->session->userdata('outlet_id');

        // 1. Traer todos los números ocupados y activos
        $this->db->select('id, name, sale_id, sale_no, user_id, outlet_id');
        $this->db->from('tbl_numeros');
        $this->db->where('sale_no IS NOT NULL');
        $this->db->where('del_status', 'Live');
        // Si quieres filtrar por outlet: $this->db->where('outlet_id', $outlet_id);
        $numeros = $this->db->get()->result();

        $numbersToFree = [];
        $idsToLiberate = [];

        foreach ($numeros as $num) {
            // 2. Verificar si sale_no existe en tbl_kitchen_sales con del_status = 'Live'
            $this->db->select('id');
            $this->db->from('tbl_kitchen_sales');
            $this->db->where('sale_no', $num->sale_no);
            $this->db->where('outlet_id', $num->outlet_id);
            $this->db->where('del_status', 'Live');
            $kitchen = $this->db->get()->row();

            if (!$kitchen) {
                // No existe en kitchen_sales, marcar para liberar
                $numbersToFree[] = $num;
                $idsToLiberate[] = $num->id;
            } else {
                // Sí existe en kitchen_sales, verificar si existe en tbl_sales con del_status = 'Live'
                $this->db->select('id');
                $this->db->from('tbl_sales');
                $this->db->where('sale_no', $num->sale_no);
                $this->db->where('outlet_id', $num->outlet_id);
                $this->db->where('del_status', 'Live');
                $sale = $this->db->get()->row();

                if ($sale) {
                    // Existe en sales, marcar para liberar
                    $numbersToFree[] = $num;
                    $idsToLiberate[] = $num->id;
                }
            }
        }

        // Liberar los números que cumplen la condición
        if (!empty($idsToLiberate)) {
            $this->db->where_in('id', $idsToLiberate);
            $this->db->update('tbl_numeros', array(
                'sale_id' => NULL,
                'sale_no' => NULL,
                'user_id' => NULL
            ));
        }

        echo '<pre>';
        var_dump($numbersToFree); 
        // var_dump($idsToLiberate); 
        echo '</pre>';
    }

    public function getUpdatedNumbers() {
        $outlet_id = $this->session->userdata('outlet_id');
        
        // Segundo: Obtener los números ocupados actuales (incluyendo los recién liberados)
        $this->db->select('id, sale_id, sale_no, user_id, name');
        $this->db->where("outlet_id", $outlet_id);
        $this->db->where("del_status", "Live");
        $this->db->where("sale_id IS NOT NULL"); // Solo números ocupados
        $numbers = $this->db->get("tbl_numeros")->result();
        
        return $numbers;
    }

    public function preimpreso() {
        $outlet_id = $this->session->userdata('outlet_id');
        $user_id = $this->session->userdata('user_id');
        $user_txt = $this->session->userdata('full_name');
        $data_base64 = $this->input->post('data_base64');
        // $data_base64 = 'eyJwcmVpbXByZXNvX21vZGUiOiIxIiwiZmVjaGEiOiIyMDI1LTA5LTI5IiwicnVjIjoiNDE2MTQxNS0xIiwibm9tYnJlIjoiQ0xBVURJTyBEQU5JRUwgUElOVE8gTlXDkUVaIiwiZGlyZWNjaW9uIjoiIiwidG90YWwiOiI5NTAwMCIsInRpcG8iOiJ0b2RvcyIsImVzcGVjaWZpY28iOiIiLCJzYWxlX25vIjoiVFdUMjUwOTI5LTAwMSIsIml0ZW1zX2RhdGEiOlt7ImZvb2RfbWVudV9pZCI6IjIzMCIsImlzX3ByaW50IjoiMSIsImlzX2tvdF9wcmludCI6IjEiLCJtZW51X25hbWUiOiJQSVpaQSBNRURJQU5BIC0gQ09OIEJPUkRFIiwia2l0Y2hlbl9pZCI6IiIsImtpdGNoZW5fbmFtZSI6IiIsImlzX2ZyZWUiOiIwIiwicm91bmRpbmdfYW1vdW50X2hpZGRlbiI6MCwiaXRlbV92YXQiOiIiLCJtZW51X2Rpc2NvdW50X3ZhbHVlIjoiMCIsImRpc2NvdW50X3R5cGUiOiJmaXhlZCIsIm1lbnVfcHJpY2Vfd2l0aG91dF9kaXNjb3VudCI6NTUwMDAsIml2YV90aXBvIjoiMTAiLCJtZW51X3VuaXRfcHJpY2UiOjU1MDAwLCJxdHkiOjEsInRtcF9xdHkiOjAsInBfcXR5IjowLCJpdGVtX3ByZXZpb3VzX2lkIjoiIiwiaXRlbV9jb29raW5nX2RvbmVfdGltZSI6IiIsIml0ZW1fY29va2luZ19zdGFydF90aW1lIjoiIiwiaXRlbV9jb29raW5nX3N0YXR1cyI6IiIsIml0ZW1fdHlwZSI6IiIsIm1lbnVfcHJpY2Vfd2l0aF9kaXNjb3VudCI6NTUwMDAsIml0ZW1fZGlzY291bnRfYW1vdW50IjoiMCIsIm1vZGlmaWVyc19pZCI6IjUsNyIsIm1vZGlmaWVyc19uYW1lIjoiRnVnYXp6YSwgTG9tYm8iLCJtb2RpZmllcnNfcHJpY2UiOiIwLDAiLCJtb2RpZmllcl92YXQiOiJbXXx8fFtdIiwiaXRlbV9ub3RlIjoiIiwibWVudV9jb21ib19pdGVtcyI6IiJ9LHsiZm9vZF9tZW51X2lkIjoiMjMyIiwiaXNfcHJpbnQiOiIxIiwiaXNfa290X3ByaW50IjoiMSIsIm1lbnVfbmFtZSI6IlBJWlpBIFBFUlNPTkFMIC0gQ09OIEJPUkRFIiwia2l0Y2hlbl9pZCI6IiIsImtpdGNoZW5fbmFtZSI6IiIsImlzX2ZyZWUiOiIwIiwicm91bmRpbmdfYW1vdW50X2hpZGRlbiI6MCwiaXRlbV92YXQiOltdLCJtZW51X2Rpc2NvdW50X3ZhbHVlIjoiMCIsImRpc2NvdW50X3R5cGUiOiJmaXhlZCIsIm1lbnVfcHJpY2Vfd2l0aG91dF9kaXNjb3VudCI6NDAwMDAsIml2YV90aXBvIjoiMTAiLCJtZW51X3VuaXRfcHJpY2UiOjQwMDAwLCJxdHkiOjEsInRtcF9xdHkiOjAsInBfcXR5IjoxLCJpdGVtX3ByZXZpb3VzX2lkIjoiIiwiaXRlbV9jb29raW5nX2RvbmVfdGltZSI6IiIsIml0ZW1fY29va2luZ19zdGFydF90aW1lIjoiIiwiaXRlbV9jb29raW5nX3N0YXR1cyI6IiIsIml0ZW1fdHlwZSI6IiIsIm1lbnVfcHJpY2Vfd2l0aF9kaXNjb3VudCI6NDAwMDAsIml0ZW1fZGlzY291bnRfYW1vdW50IjoiMCIsIm1vZGlmaWVyc19pZCI6IjgsMTciLCJtb2RpZmllcnNfbmFtZSI6IkNhbGFicmVzYSwgSXRhbGlhbmEiLCJtb2RpZmllcnNfcHJpY2UiOiIwLDAiLCJtb2RpZmllcl92YXQiOiJbXXx8fFtdIiwiaXRlbV9ub3RlIjoiIiwibWVudV9jb21ib19pdGVtcyI6IiJ9XSwiaXRlbXMiOlt7InF1YW50aXR5X3B1cmNoYXNlZCI6MSwiUHJvZHVjdG8iOiJ0b2RvcyIsIml0ZW1fdW5pdF9wcmljZSI6Ijk1MDAwIiwidG90YWwiOiI5NTAwMCJ9XX0=';
        $data_base64 = urldecode($data_base64);
        $data_json = urldecode(base64_decode($data_base64));
        $data = json_decode($data_json);
    
        $tipo = isset($data->tipo) ? $data->tipo : null;
        $items_data = isset($data->items_data) ? $data->items_data : [];
        $items = isset($data->items) ? $data->items : [];
        $total = isset($data->total) ? $data->total : 0;
        $fecha = isset($data->fecha) ? $data->fecha : date('Y-m-d');
        $nombre = isset($data->nombre) ? $data->nombre : '';
        $direccion = isset($data->direccion) ? $data->direccion : '';
        $ruc = isset($data->ruc) ? $data->ruc : '';
        $sale_no = isset($data->sale_no) ? $data->sale_no : null;
    
        $pages = [];
        if ($tipo === 'todos' && is_array($items_data)) {
            $items_per_page = 12;
            $chunks = array_chunk($items_data, $items_per_page);
            foreach ($chunks as $chunk) {
                $subtotal_diez = 0;
                $subtotal_cinco = 0;
                $subtotal_exenta = 0;
                foreach ($chunk as $item) {
                    $precio = isset($item->menu_price_with_discount) ? floatval($item->menu_price_with_discount) : 0;
                    $qty = isset($item->qty) ? floatval($item->qty) : 1;
                    $iva_tipo = isset($item->iva_tipo) ? $item->iva_tipo : "10"; // Por defecto 10
    
                    $total_item = $precio; // Si el precio ya es total, si no usa $precio * $qty
                    if ($iva_tipo == "0") {
                        $subtotal_exenta += $total_item;
                    } elseif ($iva_tipo == "5") {
                        $subtotal_cinco += $total_item;
                    } else {
                        $subtotal_diez += $total_item;
                    }
                }
                $subtotal = $subtotal_diez + $subtotal_cinco + $subtotal_exenta;
                $iva_diez = $subtotal_diez / 11;
                $iva_cinco = $subtotal_cinco / 21;
    
                $pages[] = [
                    'items' => $chunk,
                    'total' => $subtotal,
                    // 'iva_0' => 0,
                    'iva_10' => $iva_diez,
                    'iva_5' => $iva_cinco,
                    'subtotal_10' => $subtotal_diez,
                    'subtotal_5' => $subtotal_cinco,
                    'subtotal_exenta' => $subtotal_exenta,
                ];
            }
        } else {
            $pages[] = [
                'items' => is_array($items) ? $items : [],
                'total' => floatval($total),
                'iva_10' => floatval($total) / 11,
                'iva_5' => 0,
                // 'iva_0' => 0,
                'subtotal_10' => floatval($total),
                'subtotal_5' => 0,
                'subtotal_exenta' => 0,
            ];
        }
    
        // --- Guardar en BD cada página ---
        $total_paginas = count($pages);
        foreach ($pages as $idx => $pageData) {
            $page_items = [];
            foreach ($pageData['items'] as $item) {
                $page_items[] = [
                    'cantidad' => isset($item->qty) ? $item->qty : (isset($item->quantity_purchased) ? $item->quantity_purchased : 0),
                    'detalle' => isset($item->menu_name) ? $item->menu_name : (isset($item->Producto) ? $item->Producto : ''),
                    'precio_unitario' => (
                        isset($item->menu_price_with_discount, $item->qty) && floatval($item->qty) > 0
                    )
                        ? (floatval($item->menu_price_with_discount) / floatval($item->qty))
                        : (isset($item->item_unit_price) ? floatval($item->item_unit_price) : 0),
                    // 'precio_unitario' => isset($item->menu_price_with_discount) ? $item->menu_price_with_discount : (isset($item->item_unit_price) ? $item->item_unit_price : 0),
                    'total' => (isset($item->menu_price_with_discount) && isset($item->qty)) ? $item->menu_price_with_discount : (isset($item->total) ? $item->total : 0),
                    'iva_tipo' => isset($item->iva_tipo) ? $item->iva_tipo : "10",
                ];
            }
            $this->db->insert('tbl_facturas_preimpresas', [
                'fecha' => $fecha,
                'tipo' => 'CONTADO',
                'documento' => $ruc,
                'nombre' => $nombre,
                'direccion' => $direccion,
                // 'iva_excenta' => $pageData['iva_0'],
                'iva_cinco' => $pageData['iva_5'],
                'iva_diez' => $pageData['iva_10'],
                'iva_total' => $pageData['iva_5'] + $pageData['iva_10'],
                'subtotal' => $pageData['total'],
                // 'subtotal_exenta' => $pageData['subtotal_exenta'],
                'subtotal_cinco' => $pageData['subtotal_5'],
                'subtotal_diez' => $pageData['subtotal_10'],
                'total_texto' => numeroConDecimalesATexto(round($pageData['total'],0)),
                'pagina' => $idx + 1,
                'total_paginas' => $total_paginas,
                'items' => json_encode($page_items),
                'sale_no' => $sale_no,
                'outlet_id' => $outlet_id,
                'user_id' => $user_id,
                'user_name' => $user_txt,
            ]);
        }

        // $this->load->config('config');
        if ( $this->config->item('plantilla_pre_impreso') == '2'){
            $this->load->view("preimpreso_2", $data);
        } else {
            $this->load->view("preimpreso_1", $data);
        }
    }

    // public function preimpreso() {
    //     // Decodificar los datos
    //     $data_base64 = $this->input->post('data_base64');
    //     $data_base64 = urldecode($data_base64);
    //     $data_json = urldecode(base64_decode($data_base64));

    //     $data = json_decode($data_json);
    //     // echo '<pre>';
    //     // var_dump($data); 
    //     // echo '<pre>';
        
        
    //     // // Cargar vista con los datos
    //     $this->load->view("preimpreso_1", $data);
    // }


    public function preimpreso_format_dataGOT() {
        // Recibir los datos por POST
        $data_order = $this->input->post('data_order');
        $data = json_decode($data_order, true);
    
        // Extraer los datos principales
        $fecha = ($data['fecha']) ? date('d/m/Y',strtotime($data['fecha'])) : '';
        $tipo = 'CONTADO'; //$data['tipo'] ?? '';
        $documento = $data['ruc'] ?? '';
        $nombre = $data['nombre'] ?? '';
        $direccion = $data['direccion'] ?? '';
        // $total_texto = $this->num2letras($data['total'] ?? 0); // Implementa esta función si quieres
        $items = $data['items_data'] ?? [];
    
        // Agrupa los items en lotes de 13
        $items_por_hoja = 13;
        $facturas = [];
        $total_items = count($items);
    
        for ($i = 0; $i < $total_items; $i += $items_por_hoja) {
            $hoja_items = array_slice($items, $i, $items_por_hoja);
    
            // Inicializar subtotales/IVA
            $iva_cinco = 0;
            $iva_diez = 0;
            $subtotal_cinco = 0;
            $subtotal_diez = 0;
            $subtotal = 0;
    
            // Formatear los items
            $detalle_items = [];
            foreach ($hoja_items as $item) {
                $cantidad = (float)($item['qty'] ?? 0);
                $detalle = $item['menu_name'] ?? '';
                $precio_unitario = (float)($item['menu_unit_price'] ?? 0);
                $excenta = 0; // En tu caso siempre 0
                $cinco = 0;   // Si hay rubros con 5%, aquí asignas el valor
                $diez = number_format($cantidad * $precio_unitario, 0, '', ''); // Todo va a diez
    
                $subtotal += $cantidad * $precio_unitario;
                $subtotal_diez += $cantidad * $precio_unitario;
    
                // IVA 10% (asumiendo que el precio es IVA incluido)
                $iva10_item = round(($cantidad * $precio_unitario) / 11);
    
                $iva_diez += $iva10_item;
    
                $detalle_items[] = [
                    'codigo' => $item['food_menu_id'] ?? '',
                    'cantidad' => $cantidad,
                    'detalle' => $detalle,
                    'precio_unitario' => number_format($precio_unitario, 0, '', '.'),
                    'excenta' => $excenta,
                    'cinco' => $cinco ? number_format($cinco, 0, '', '.') : '',
                    'diez' => $diez ? number_format($diez, 0, '', '.') : '',
                ];
            }
    
            // Formatear totales
            $total_iva_cinco = round(($subtotal_cinco / 11),0);
            $total_iva_diez = round(($subtotal_diez / 11),0);
            $facturas[] = [
                'fecha' => $fecha,
                'tipo' => $tipo,
                'documento' => $documento,
                'nombre' => $nombre,
                'iva_cinco' => $total_iva_cinco ? number_format($total_iva_cinco, 0, '', '.') : '',
                'iva_diez' => $total_iva_diez ? number_format($total_iva_diez, 0, '', '.') : '',
                'iva_total' => number_format($total_iva_cinco + $total_iva_diez, 0, '', '.'),
                'subtotal' => number_format($subtotal, 0, '', '.'),
                'total_texto' => numeroConDecimalesATexto(round($subtotal,0)),
                'subtotal_cinco' => $subtotal_cinco ? number_format($subtotal_cinco, 0, '', '.') : '',
                'subtotal_diez' => $subtotal_diez ? number_format($subtotal_diez, 0, '', '.') : '',
                'items' => $detalle_items,
            ];
        }
    
        // Responder en formato JSON
        echo json_encode(['content_data' => $facturas]);
    }
    
    public function preimpreso_format_data() {
        // Recibir los datos por POST
        $data_order = $this->input->post('data_order');
        $data = json_decode($data_order, true);
    
        // Agregar datos de usuario y outlet
        $outlet_id = $this->session->userdata('outlet_id');
        $user_id = $this->session->userdata('user_id');
        $user_txt = $this->session->userdata('full_name');
        $sale_no = isset($data['sale_no']) ? $data['sale_no'] : null;
    
        // Extraer los datos principales
        $fecha = ($data['fecha']) ? date('d/m/Y',strtotime($data['fecha'])) : '';
        $tipo_factura = 'CONTADO'; // El tipo de factura (contado/credito), siempre CONTADO aquí
        $documento = $data['ruc'] ?? '';
        $nombre = $data['nombre'] ?? '';
        $direccion = $data['direccion'] ?? '';
    
        // Si se pasa un tipo diferente de "todos", forzar a items especiales
        if (isset($data['tipo']) && $data['tipo'] !== 'todos') {
            $item_nombre = ($data['tipo'] === 'Especifico') ? ($data['especifico'] ?? '') : $data['tipo'];
            $item_total = (float)($data['total'] ?? 0);
            $items = [[
                'food_menu_id' => '',
                'qty' => 1,
                'menu_name' => $item_nombre,
                'menu_unit_price' => $item_total,
            ]];
        } else {
            $items = $data['items_data'] ?? [];
        }
    
        // Agrupa los items en lotes de 13
        $items_por_hoja = 13;
        $facturas = [];
        $total_items = count($items);
        $total_paginas = ceil($total_items / $items_por_hoja);
    
        for ($i = 0; $i < $total_items; $i += $items_por_hoja) {
            $hoja_items = array_slice($items, $i, $items_por_hoja);
    
            // Inicializar subtotales/IVA
            $iva_cinco = 0;
            $iva_diez = 0;
            $subtotal_cinco = 0;
            $subtotal_diez = 0;
            $subtotal = 0;
    
            // Formatear los items
            $detalle_items = [];
            foreach ($hoja_items as $item) {
                $cantidad = (float)($item['qty'] ?? 0);
                $detalle = $item['menu_name'] ?? '';
                $precio_unitario = (float)($item['menu_unit_price'] ?? 0);
                $excenta = 0; // Siempre 0 excepto rubros especiales
                $cinco = 0;   // Si hay rubros con 5%, aquí asignas el valor
                $diez = number_format($cantidad * $precio_unitario, 0, '', ''); // Todo va a diez
    
                $subtotal += $cantidad * $precio_unitario;
                $subtotal_diez += $cantidad * $precio_unitario;
    
                // IVA 10% (asumiendo que el precio es IVA incluido)
                $iva10_item = round(($cantidad * $precio_unitario) / 11);
    
                $iva_diez += $iva10_item;
    
                $detalle_items[] = [
                    'codigo' => $item['food_menu_id'] ?? '',
                    'cantidad' => $cantidad,
                    'detalle' => $detalle,
                    'precio_unitario' => number_format($precio_unitario, 0, '', '.'),
                    'excenta' => $excenta,
                    'cinco' => $cinco ? number_format($cinco, 0, '', '.') : '',
                    'diez' => $diez ? number_format($diez, 0, '', '.') : '',
                ];
            }
    
            // Formatear totales
            $total_iva_cinco = round(($subtotal_cinco / 11),0);
            $total_iva_diez = round(($subtotal_diez / 11),0);
    
            $factura = [
                'fecha' => $fecha,
                'tipo' => $tipo_factura,
                'documento' => $documento,
                'nombre' => $nombre,
                'direccion' => $direccion,
                'iva_cinco' => $total_iva_cinco ? number_format($total_iva_cinco, 0, '', '.') : '',
                'iva_diez' => $total_iva_diez ? number_format($total_iva_diez, 0, '', '.') : '',
                'iva_total' => number_format($total_iva_cinco + $total_iva_diez, 0, '', '.'),
                'subtotal' => number_format($subtotal, 0, '', '.'),
                'total_texto' => numeroConDecimalesATexto(round($subtotal,0)),
                'subtotal_cinco' => $subtotal_cinco ? number_format($subtotal_cinco, 0, '', '.') : '',
                'subtotal_diez' => $subtotal_diez ? number_format($subtotal_diez, 0, '', '.') : '',
                'items' => $detalle_items,
                'pagina' => intval($i / $items_por_hoja) + 1,
                'total_paginas' => $total_paginas,
                'sale_no' => $sale_no,
                'outlet_id' => $outlet_id,
                'user_id' => $user_id,
                'user_name' => $user_txt,
            ];
    
            // Insertar en la BD
            $this->db->insert('tbl_facturas_preimpresas', [
                'fecha' => date('Y-m-d', strtotime($fecha)),
                'tipo' => $factura['tipo'],
                'documento' => $factura['documento'],
                'nombre' => $factura['nombre'],
                'direccion' => $factura['direccion'],
                'iva_cinco' => $factura['iva_cinco'],
                'iva_diez' => $factura['iva_diez'],
                'iva_total' => $factura['iva_total'],
                'subtotal' => $factura['subtotal'],
                'subtotal_cinco' => $factura['subtotal_cinco'],
                'subtotal_diez' => $factura['subtotal_diez'],
                'total_texto' => $factura['total_texto'],
                'pagina' => $factura['pagina'],
                'total_paginas' => $factura['total_paginas'],
                'items' => json_encode($factura['items']),
                'sale_no' => $sale_no,
                'outlet_id' => $outlet_id,
                'user_id' => $user_id,
                'user_name' => $user_txt,
            ]);
    
            $facturas[] = $factura;
        }
    
        // Responder en formato JSON
        echo json_encode(['content_data' => $facturas]);
    }

    public function check_sale_no_exists() {
        $sale_no = $this->input->post('sale_no');
        $exists = $this->db->where('sale_no', $sale_no)->get('tbl_kitchen_sales')->num_rows() > 0;
        echo json_encode(['exists' => $exists]);
    }

    public function get_unique_sale_no() {
        $sale_no = $this->input->post('sale_no');
        $original_sale_no = $sale_no;
        $letters = range('A', 'Z');
        $prefixIndex = 0;
    
        // Verifica si el sale_no existe
        while ($this->db->where('sale_no', $sale_no)->get('tbl_kitchen_sales')->num_rows() > 0) {
            if ($prefixIndex < count($letters)) {
                $sale_no = $letters[$prefixIndex] . $original_sale_no;
                $prefixIndex++;
            } else {
                // Si se acaban las letras, usa timestamp como prefijo (garantiza unicidad)
                $sale_no = time() . $original_sale_no;
                break;
            }
        }
        echo json_encode(['sale_no' => $sale_no]);
    }

    public function set_short_code(){
        $code = 'FKX';
        
        $this->session->set_userdata([
            'code_short' => $code,
        ]);
        echo $this->session->userdata('code_short');
    }

    
    public function addExpenseAjax() {
        $company_id = $this->session->userdata('company_id');
        $data['payment_methods'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, "tbl_payment_methods");
        $data['expense_categories'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_expense_items");
        $data['employees'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_users");
        $this->load->view('expense/addExpense_ajax', $data); // crea esta vista
    }
    
    public function addExpenseAjaxSubmit() {
        // Validaciones igual que en addEditExpense, pero responde JSON
        $this->form_validation->set_rules('date',lang('date'), 'required|max_length[50]');
        $this->form_validation->set_rules('amount',lang('amount'), 'required|max_length[50]');
        $this->form_validation->set_rules('category_id',lang('category'), 'required|max_length[10]');
        $this->form_validation->set_rules('employee_id',lang('responsible_person'), 'required|max_length[10]');
        $this->form_validation->set_rules('payment_id', lang('payment_method'), 'required|numeric|max_length[50]');
        $this->form_validation->set_rules('note',lang('note'), 'max_length[200]');
        if ($this->form_validation->run() == TRUE) {
            $expnse_info = array();
            $expnse_info['date'] = date("Y-m-d", strtotime($this->input->post('date')));
            $expnse_info['amount'] = htmlspecialcharscustom($this->input->post('amount'));
            $expnse_info['category_id'] = htmlspecialcharscustom($this->input->post('category_id'));
            $expnse_info['employee_id'] = htmlspecialcharscustom($this->input->post('employee_id'));
            $expnse_info['note'] = htmlspecialcharscustom($this->input->post('note'));
            $expnse_info['counter_id'] = $this->session->userdata('counter_id');
            $expnse_info['user_id'] = $this->session->userdata('user_id');
            $expnse_info['outlet_id'] = $this->session->userdata('outlet_id');
            $expnse_info['payment_id'] = htmlspecialcharscustom($this->input->post('payment_id'));
            $expnse_info['added_date_time'] = date('Y-m-d H:i:s');
            $this->Common_model->insertInformation($expnse_info, "tbl_expenses");
            echo json_encode(['status'=>'ok', 'msg'=>'Gasto registrado exitosamente']);
        } else {
            echo json_encode(['status'=>'error', 'msg'=>validation_errors()]);
        }
        exit;
    }

    public function getInventarioTicketAjax() {
        // Acceso restringido si usas session
        if (!$this->session->userdata('user_id')) {
            show_404();
        }
    
        $company_id = $this->session->userdata('company_id');
        $inventory = $this->Inventory_model->getInventory(null, null, null); // O filtra por params si deseas
        $ingredient_categories = $this->Common_model->getAllByCompanyIdForDropdown($company_id, "tbl_ingredient_categories");

        $outlet = getOutletById($this->session->userdata('outlet_id'));
        $outlet_name = $outlet->outlet_name; 
        $username = $this->session->userdata('full_name'); 
        $hora = date('d/m/Y H:i:s'); // Formato de fecha y hora
    
        // Puedes enviar solo los datos necesarios para reducir el payload
        echo json_encode([
            'hora' => $hora,
            'outlet_name' => $outlet_name,
            'username' => $username,
            'inventory' => $inventory,
            'ingredient_categories' => $ingredient_categories
        ]);
    }


    public function send()
    {
        $this->load->library('NodeWaApi');

        $data = [
            // 'number' => '595972229958', // Número en formato correcto
            // 'number' => '595973515853', 
            'number' => '595984026821', 
            'body'   => 'Mensaje de prueba desde CodeIgniter',
        ];

        $ok = $this->nodewaapi->send_message($data);

        if ($ok) {
            echo 'Mensaje enviado (o intento realizado)';
        } else {
            echo 'Error: Configuración faltante o datos incompletos';
        }
    }

    public function send_report()
    {
        $this->load->library('NodeWaApi');
        $mensaje = "¡Este es otro mensaje automatico de reporte de NOVABOX para todos!";

        $resultados = $this->nodewaapi->send_report_to_all($mensaje);

        echo '<pre>';
        print_r($resultados);
        echo '</pre>';
    }

    
	function NumeracionesActivasByJson()
	{
		echo json_encode(NumeracionesActivas());
	}
    
    function factura_send(){

        $this->facturasend->test();
        $resultado = $this->facturasend->get_tipos_regimenes();
        echo '<pre>';
        var_dump($resultado);
        echo '</pre>';

    }
        
    function ruc($ruc){

        $resultado = $this->facturasend->consulta_ruc($ruc);
        echo '<pre>';
        var_dump($resultado);
        echo '</pre>';

    }

    public function crear_factura($tipo_documento = 1, $numero_factura = 1, $cdc_asociado = '')
    {
        // ... (el array $documentos es el mismo que antes)
        // $documentos = $this->crear_lote(); //[ /* ... */ ];

        $data = [];
        if ($tipo_documento == 1) {
            // Factura Electrónica
            $data = $this->data_demo_facturacion_electronica($numero_factura);
        } elseif ($tipo_documento == 4) {
            // Autofactura Electrónica
            $data = [$this->data_demo_autofactura_electronica($numero_factura, $cdc_asociado)];
        } elseif ($tipo_documento == 5) {
            // Nota de Crédito Electrónica
            $data = [$this->data_demo_nota_credito_electronica($numero_factura, $cdc_asociado)];
        } elseif ($tipo_documento == 6) {
            // Nota de Débito Electrónica
            $data = [$this->data_demo_nota_debito_electronica($numero_factura, $cdc_asociado)];
        } elseif ($tipo_documento == 7) {
            // Nota de Remisión Electrónica
            $data = [$this->data_demo_nota_remision_electronica($numero_factura, $cdc_asociado)];
        }
        
        $resultado = $this->facturasend->crear_lote_documentos($data);

        // --- MANEJO DE ERRORES ---
        // Si el estado es 503, significa que la configuración no está completa.
        if (isset($resultado['status']) && $resultado['status'] === 503) {
            // Aquí puedes decidir qué hacer:
            // 1. Mostrar un error amigable en una vista.
            // 2. Registrar el error en un log.
            // 3. Simplemente devolver el JSON de error.
            
            // Enviamos la cabecera con el código de error
            $this->output->set_status_header(503); 
        }

        // Imprimir la respuesta para depuración
        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode($resultado, JSON_PRETTY_PRINT));
    }

    // // Reemplaza estos datos con los tuyos reales
    // private $api_url = "https://api.facturasend.com.py/";
    // private $tenantId = "TU_TENANT_ID";
    // private $api_key = "TU_API_KEY";

    public function data_demo_facturacion_electronica($numero_factura)
    {
        //tipo documento 1=Factura Electronica
        $data = [
            [
                "tipoDocumento" => 1,
                    // Tipo de documento
                    // 1=Factura Electronica
                    // 4=Autofactura electrónica
                    // 5=Nota de Crédito Electrónica
                    // 6=Nota de Débito Electrónica
                    // 7=Nota de Remisión Electrónica

                "establecimiento" => 1,
                "punto" => "001",
                "numero" => $numero_factura,
                "descripcion" => "Aparece en el documento",
                "observacion" => "Cualquier información de marketing, promociones, etc.",
                "fecha" => "2025-09-22T09:11:00",
                "tipoEmision" => 1,
                    // Tipo de emisión del DE (1 o 2)
                    // Valores:
                    // 1=Normal (Por defecto).
                    // 2=Contingencia.

                "tipoTransaccion" => 1,
                    // Tipo de Transacción.
                    // Valores:
                    // 1=Venta de mercadería (Por defecto).
                    // 2=Prestación de servicios.
                    // 3=Mixto (Venta de mercadería y servicios).
                    // 4=Venta de activo fijo.
                    // 5=Venta de divisas.
                    // 6=Compra de divisas.
                    // 7=Promoción o entrega de muestras.
                    // 8=Donación.
                    // 9=Anticipo.
                    // 10=Compra de Productos.
                    // 11=Venta de Crédito fiscal.
                    // 12=Compra de Crédito fiscal.

                    // 13=Muestras médicas (Art. 3 RG 24/2014).
                    // tipoTransaccion es obligatorio para el tipo de documento (tipoDocumento) igual a 1-Factura Electrónica o 4-AutoFactura.
                "tipoImpuesto" => 1,
                    // Tipo de impuesto afectado.
                    // Valores:
                    // 1=IVA.
                    // 2=ISC.
                    // 3=Renta.
                    // 4=Ninguno.
                    // 5=IVA – Renta.
                "moneda" => "PYG",
                "cliente" => [
                    // Consideraciones especiales en objeto data.cliente.tipoOperacion
                    //
                    // Si el Cliente es un contribuyente (data.cliente.contribuyente = true), entonces:
                    // a) si el tipo de cliente es Persona Física (data.cliente.tipoContribuyente = 1), entonces éste campo puede enviarse con el valor 1-B2B (Business to Business), o;
                    // b) si el tipo de cliente es Persona Jurídica (data.cliente.tipoContribuyente = 2), entonces éste campo puede enviarse con el valor 2-B2C (Business to Consumer), o;
                    // c) si el RUC pertenece a un proveedor del estado, entonces éste valor debe enviarse como 3-B2G (Business to Goverment).
                    // Si el Cliente NO es contribuyente (data.cliente.contribuyente = false), entonces:
                    // a) Si el tipo de documento es Cédula de Identidad, Pasaporte, Carnet de residencia, Innominado, Tarjeta Diplomática de exoneración fiscal u otro (data.cliente.documentoTipo = 1,2,4,5,6 o 9), entonces éste campo puede enviarse con el valor 2-B2C (Business to Consumer), o;
                    // b) Si el tipo de documento es Cédula Extranjera (data.cliente.documentoTipo = 3), entonces éste campo puede enviarse con el valor 4-B2F (Business to Foreign).
                    
                    "contribuyente" => true,
                    "ruc" => "4430335-1",
                    "razonSocial" => "Sevelio Vera",
                    "nombreFantasia" => "Sevelio Vera",
                    "tipoOperacion" => 1,
                        // Tipo de operación(1= B2B, 2= B2C, 3= B2G, 4= B2F)
                        // Se debe enviar 3=B2G si es una Institucion del Estado, ya sea si es una operación sin licitacion o si tiene datos de licitacion de la DNCP, caso contrario enviar como 1=B2B*
                    "direccion" => "Avda Calle Segunda y Proyectada",
                    	// Direccion del Cliente, Campo obligatorio cuando tipoDocumento =7 o tipoOperacion=4
                    "numeroCasa" => "1515", //obligatorio, si no lleve 0
                    "departamento" => 11, //desde la tabla de departamentos 
                    // Código del departamento,Campo obligatorio si se informa la dirección y tipoOperacion ≠ 4, no se debe informar cuando tipoOperacion = 4.
                    // "departamentoDescripcion" => "ALTO PARANA",
                    "distrito" => 143,
                    // "distritoDescripcion" => "DOMINGO MARTINEZ DE IRALA",
                    "ciudad" => 3344,
                    // Código de la ciudad del Cliente. Campo obligatorio si se informa la dirección y tipoOperacion≠4, no se debe informar cuando tipoOperacion = 4.
                    // "ciudadDescripcion" => "PASO ITA (INDIGENA)",
                    "pais" => "PRY",
                    "paisDescripcion" => "Paraguay",
                    "tipoContribuyente" => 2, 
                        // Tipo de contribuyente
                        // 1=Persona Física
                        // 2=Persona Jurídica
                    "documentoTipo" => 1,
                        // 1=Cédula paraguaya
                        // 2=Pasaporte
                        // 3=Cédula extranjera
                        // 4=Carnet de residencia
                        // 5=Innominado
                        // 6=Tarjeta Diplomática de exoneración fiscal
                        // 9=Informar
                    "documentoNumero" => "2324234",
                    'telefono' => '061507903',
                    'celular' => '0973837201',
                    'email' => 'cliente@cliente.com',
                    "codigo" => "1548" //obligatario, por lomenos 3 cifras, rellenar en caso de que sea de un solo codigo
                ],
                "usuario" => [
                    "documentoTipo" => 1,
                    "documentoNumero" => "157264",
                    "nombre" => "Marcos Jara",
                    "cargo" => "Vendedor"
                ],
                "factura" => [
                    "presencia" => 1
                    	// Indicador de presencia
                        // 1= Operación presencial
                        // 2= Operación electrónica
                        // 3= Operación telemarketing
                        // 4= Venta a domicilio
                        // 5= Operación bancaria
                        // 6= Operación cíclica
                        // 9= Otro
                ],
                "condicion" => [
                    "tipo" => 1,
                        // Condición de la operación.
                        // 1= Contado
                        // 2= Crédito

                        // Datos que describen la forma de pago al contado o del monto de la entrega inicial.
                    "entregas" => [
                        [
                            "tipo" => 1,
                            // Tipo de pago Ej.:1= Efectivo, 2= Cheque,3= Tarjeta de crédito, 4= Tarjeta de débito.
                            "monto" => "150000",
                            "moneda" => "PYG",
                            "monedaDescripcion" => "Guarani",
                            "cambio" => 0.0
                        ],
                        [
                            "tipo" => 3,
                            // Tipo de pago Ej.:1= Efectivo, 2= Cheque,3= Tarjeta de crédito, 4= Tarjeta de débito.
                            "monto" => "150000",
                            "moneda" => "PYG",
                            "monedaDescripcion" => "Guarani",
                            "cambio" => 0.0,
                            "infoTarjeta" => [
                                "numero" => 1234,
                                "tipo" => 1,
                                "tipoDescripcion" => "Dinelco",
                                "titular" => "Marcos Jara",
                                "ruc" => "69695654-1",
                                "razonSocial" => "Bancard",
                                "medioPago" => 1,
                                "codigoAutorizacion" => 232524234
                            ]
                        ],
                        [
                            "tipo" => 2,
                                // Tipo de pago Ej.:1= Efectivo, 2= Cheque,3= Tarjeta de crédito, 4= Tarjeta de débito.
                            "monto" => "150000",
                            "moneda" => "PYG",
                            "monedaDescripcion" => "Guarani",
                            "cambio" => 0.0,
                            "infoCheque" => [
                                "numeroCheque" => "32323232",
                                "banco" => "Sudameris"
                            ]
                        ]
                    ],
                        // Datos que describen la forma de pago a crédito.
                    "credito" => [
                        "tipo" => 1,
                        "plazo" => "30 días",
                        "cuotas" => 2,
                        "montoEntrega" => 1500000.00,
                        "infoCuotas" => [
                            [
                                "moneda" => "PYG",
                                "monto" => 800000.00,
                                "vencimiento" => "2021-10-30"
                            ],
                            [
                                "moneda" => "PYG",
                                "monto" => 800000.00,
                                "vencimiento" => "2021-11-30"
                            ]
                        ]
                    ]
                ],
                "items" => [
                    [
                        "codigo" => "A-001",
                            // Código interno de identificación de la mercadería o servicio de responsabilidad del emisor
                        "descripcion" => "Producto A",
                            // Descripción del producto y/o servicio. Equivalente a nombre del producto establecido en la RG 24/2019
                        "observacion" => "",
                        // "ncm" => "123456",
                            // Nomenclatura común del Mercosur (NCM) 
                        "unidadMedida" => 77,
                        "cantidad" => 1,
                        "precioUnitario" => 199000,
                        "cambio" => 0.0,
                        "ivaTipo" => 1,
                        	// Forma de afectación tributaria del IVA
                            // 1= Gravado IVA
                            // 2= Exonerado (Art. 83- Ley 125/91)
                            // 3= Exento
                            // 4= Gravado parcial Grav-Exento)
                        "ivaBase" => 100,
                            // Base gravada del IVA por ítem
                        "iva" => 10,
                            // Tasa del IVA
                            // Posibles valores = 0, 5 o 10
                        "lote" => "058",
                        "vencimiento" => "2022-10-30",
                        "numeroSerie" => "", //opcional
                        "numeroPedido" => "", //opcional
                        "numeroSeguimiento" => "", //opcional
                        // "registroSenave" => "123456789"
                            // 	Número de registro del producto otorgado por el SENAVE
                            // Obligados por la RG N° 16/2019 y la RG N° 24/2019 – Agroquímicos

                    ]
                ]
            ]
        ];
        return $data;
    }

    public function data_demo_autofactura_electronica($numero_factura = 1, $doc_asociado = '')
    {
        //tipo documento 4=Autofactura electrónica
        $data = [
            'tipoDocumento' => 4,
            'establecimiento' => 1,
            'punto' => '001',
            'numero' => $numero_factura,
            'descripcion' => 'Aparece en el documento',
            'observacion' => 'Cualquier información de interés',
            'tipoContribuyente' => 1,
            'fecha' => '2025-09-22T09:50:00',
            'tipoEmision' => 1,
            'tipoTransaccion' => 1,
            'tipoImpuesto' => 2,
            'moneda' => 'PYG',
            'cambio' => 6700,
            'cliente' => [
                'contribuyente' => true,
                'ruc' => '4430335-1',
                'razonSocial' => 'Sevelio Vera',
                'nombreFantasia' => 'Sevelio Vera',
                'tipoOperacion' => 2,
                'direccion' => 'Avda Calle Segunda y Proyectada',
                'numeroCasa' => '1515',
                'departamento' => 11,
                'departamentoDescripcion' => 'ALTO PARANA',
                'distrito' => 143,
                'distritoDescripcion' => 'DOMINGO MARTINEZ DE IRALA2',
                'ciudad' => 3344,
                'ciudadDescripcion' => 'PASO ITA (INDIGENA)',
                'pais' => 'PRY',
                'paisDescripcion' => 'Paraguay',
                'tipoContribuyente' => 1,
                'documentoTipo' => 1,
                'documentoNumero' => '2324234',
                'telefono' => '061507903',
                'celular' => '0973837201',
                'email' => 'cliente@cliente.com',
                'codigo' => '1548'
            ],
            'usuario' => [
                'documentoTipo' => 1,
                'documentoNumero' => '157264',
                'nombre' => 'Marcos Jara',
                'cargo' => 'Vendedor'
            ],
            'autoFactura' => [
                'tipoVendedor' => 1,
                'documentoTipo' => 1,
                'documentoNumero' => '4161415',
                'nombre' => 'Claudio Daniel Pinto',
                'direccion' => 'Area 4',
                'numeroCasa' => '001',
                'departamento' => 11,
                'departamentoDescripcion' => 'ALTO PARANA',
                'distrito' => 145,
                'distritoDescripcion' => 'CIUDAD DEL ESTE',
                'ciudad' => 3420,
                'ciudadDescripcion' => 'DON BOSCO',
                'ubicacion' => [
                    'lugar' => 'Donde se realiza la transacción',
                    'departamento' => 11,
                    'departamentoDescripcion' => 'ALTO PARANA',
                    'distrito' => 145,
                    'distritoDescripcion' => 'CIUDAD DEL ESTE',
                    'ciudad' => 3420,
                    'ciudadDescripcion' => 'DON BOSCO'
                ]
            ],
            'condicion' => [
                'tipo' => 1,
                'entregas' => [
                    [
                        'tipo' => 1,
                        'monto' => '150000',
                        'moneda' => 'PYG',
                        'monedaDescripcion' => 'Guarani',
                        'cambio' => 0
                    ]
                ]
            ],
            'items' => [
                [
                    'codigo' => 'B-001',
                    'descripcion' => 'Producto B',
                    'observacion' => 'Cualquier información de interés',
                    'partidaArancelaria' => 4444,
                    'ncm' => '123456',
                    'unidadMedida' => 77,
                    'cantidad' => 10.5,
                    'precioUnitario' => 10800,
                    'pais' => 'PRY',
                    'paisDescripcion' => 'Paraguay',
                    'ivaTipo' => 1,
                    'ivaBase' => 100,
                    'iva' => 5
                ]
            ],
            'documentoAsociado' => [
                'formato' => 3,
                'constanciaTipo' => 1,
                'constanciaNumero' => 42804571677,
                'constanciaControl' => '8f9708d2'
            ]
        ];
        return $data;
    }
    
    public function data_demo_nota_credito_electronica($numero_factura = 1, $cdc_asociado = '')
    {
        //tipo documento 5=Nota de Crédito Electrónica
        $data = [
            'tipoDocumento' => 5,
            'establecimiento' => 1,
            'punto' => '001',
            'numero' => $numero_factura,
            'descripcion' => 'Aparece en el documento',
            'observacion' => 'Cualquier información de interés',
            'tipoContribuyente' => 1,
            'fecha' => '2025-09-22T09:50:00',
            'tipoEmision' => 1,
            'tipoTransaccion' => 1,
            'tipoImpuesto' => 1,
            'moneda' => 'PYG',
            'cliente' => [
                'contribuyente' => true,
                'ruc' => '4430335-1',
                'razonSocial' => 'Sevelio Vera',
                'nombreFantasia' => 'Sevelio Vera',
                'tipoOperacion' => 1,
                'direccion' => 'Avda Calle Segunda y Proyectada',
                'numeroCasa' => '1515',
                'departamento' => 11,
                'distrito' => 143,
                'ciudad' => 3344,
                'pais' => 'PRY',
                'tipoContribuyente' => 1,
                'documentoTipo' => 1,
                'documentoNumero' => '2324234',
                'telefono' => '061987544',
                'celular' => '0983524132',
                'email' => 'cliente@cliente.com',
                'codigo' => '1548'
            ],
            'usuario' => [
                'documentoTipo' => 1,
                'documentoNumero' => '157264',
                'nombre' => 'Marcos Jara',
                'cargo' => 'Vendedor'
            ],
            'notaCreditoDebito' => [
                'motivo' => 1
            ],
            'items' => [
                [
                    'codigo' => 'B-001',
                    'descripcion' => 'Producto B',
                    'observacion' => 'Cualquier informacion de interés',
                    'unidadMedida' => 77,
                    'cantidad' => 10.5,
                    'precioUnitario' => 10800,
                    'pais' => 'PRY',
                    'ivaTipo' => 1,
                    'ivaBase' => 100,
                    'iva' => 5
                ]
            ],
            'documentoAsociado' => [
                'formato' => 1,
                'cdc' => $cdc_asociado
            ]
        ];
        return $data;
    }

    public function data_demo_nota_debito_electronica($numero_factura = 1, $cdc_asociado = '')
    {
        //tipo documento 6=Nota de Débito Electrónica
        $data = [
            'tipoDocumento' => 6,
            'establecimiento' => 1,
            'punto' => '001',
            'numero' => $numero_factura,
            'descripcion' => 'Aparece en el documento',
            'observacion' => 'Cualquier informacion de interes',
            'tipoContribuyente' => 1,
            'fecha' => '2025-09-22T09:50:00',
            'tipoEmision' => 1,
            'tipoTransaccion' => 1,
            'tipoImpuesto' => 1,
            'moneda' => 'PYG',
            'condicionAnticipo' => null,
            'condicionTipoCambio' => null,
            'cambio' => 6700,
            'cliente' => [
                'contribuyente' => true,
                'ruc' => '4430335-1',
                'razonSocial' => 'Sevelio Vera',
                'nombreFantasia' => 'Sevelio Vera',
                'tipoOperacion' => 1,
                'direccion' => 'Avda Calle Segunda y Proyectada',
                'numeroCasa' => '1515',
                'departamento' => 11,
                'departamentoDescripcion' => 'ALTO PARANA',
                'distrito' => 143,
                'distritoDescripcion' => 'DOMINGO MARTINEZ DE IRALA',
                'ciudad' => 3344,
                'ciudadDescripcion' => 'PASO ITA (INDIGENA)',
                'pais' => 'PRY',
                'paisDescripcion' => 'Paraguay',
                'tipoContribuyente' => 1,
                'documentoTipo' => 1,
                'documentoNumero' => '2324234',
                'telefono' => '061507903',
                'celular' => '0973837201',
                'email' => 'cliente@cliente.com',
                'codigo' => '1548'
            ],
            'usuario' => [
                'documentoTipo' => 1,
                'documentoNumero' => '157264',
                'nombre' => 'Marcos Jara',
                'cargo' => 'Vendedor'
            ],
            'notaCreditoDebito' => [
                'motivo' => 1
            ],
            'items' => [
                [
                    'codigo' => 'B-001',
                    'descripcion' => 'Producto B',
                    'observacion' => 'Cualquier informacion de interes',
                    'partidaArancelaria' => 4444,
                    'ncm' => '123456',
                    'unidadMedida' => 77,
                    'cantidad' => 10.5,
                    'precioUnitario' => 10800,
                    'pais' => 'PRY',
                    'paisDescripcion' => 'Paraguay',
                    'ivaTipo' => 1,
                    'ivaBase' => 100,
                    'iva' => 5,
                    'lote' => 'A-001',
                    'vencimiento' => '2022-10-30'
                ]
            ],
            'documentoAsociado' => [
                'formato' => 1,
                'cdc' => $cdc_asociado
            ]
        ];
        return $data;
    }

    public function data_demo_nota_remision_electronica($numero_factura = 1, $cdc_asociado = '')
    {
        //tipo documento 7=Nota de Remisión Electrónica
        $data = [
            'tipoDocumento' => 7,
            'establecimiento' => 1,
            'punto' => '001',
            'numero' => $numero_factura,
            'descripcion' => 'Aparece en el documento',
            'observacion' => 'Cualquier informacion de interes',
            'tipoContribuyente' => 1,
            'fecha' => '2025-09-22T09:50:00',
            'tipoEmision' => 1,
            'tipoTransaccion' => 1,
            'tipoImpuesto' => 1,
            'moneda' => 'PYG',
            'cambio' => 6700,
            'cliente' => [
                'contribuyente' => true,
                'ruc' => '4430335-1',
                'razonSocial' => 'Sevelio Vera',
                'nombreFantasia' => 'Sevelio Vera',
                'tipoOperacion' => 1,
                'direccion' => 'Avda Calle Segunda y Proyectada',
                'numeroCasa' => '1515',
                'departamento' => 11,
                // 'departamentoDescripcion' => 'ALTO PARANA',
                'distrito' => 143,
                'distritoDescripcion' => 'DOMINGO MARTINEZ DE IRALA',
                'ciudad' => 3344,
                'ciudadDescripcion' => 'PASO ITA (INDIGENA)',
                'pais' => 'PRY',
                'paisDescripcion' => 'Paraguay',
                'tipoContribuyente' => 1,
                'documentoTipo' => 1,
                'documentoNumero' => '2324234',
                'telefono' => '0973123456',
                'celular' => '0973123456',
                'email' => 'cliente@cliente.com',
                'codigo' => '1548'
            ],
            'usuario' => [
                'documentoTipo' => 1,
                'documentoNumero' => '157264',
                'nombre' => 'Marcos Jara',
                'cargo' => 'Vendedor'
            ],
            'remision' => [
                'motivo' => 1,
                'tipoResponsable' => 1,
                'kms' => 150,
                'fechaFactura' => '2025-09-22'
            ],
            'items' => [
                [
                    'codigo' => 'A-001',
                    'descripcion' => 'Producto A',
                    'observacion' => 'Cualquier informacion de interes',
                    'ncm' => '123456',
                    'unidadMedida' => 77,
                    'cantidad' => 10.5,
                    'precioUnitario' => 10800,
                    'pais' => 'PRY',
                    'paisDescripcion' => 'Paraguay',
                    'tolerancia' => 1,
                    'toleranciaCantidad' => 1,
                    'toleranciaPorcentaje' => 1,
                    'cdcAnticipo' => $cdc_asociado,
                    'ivaTipo' => 1,
                    'ivaBase' => 100,
                    'iva' => 5,
                    'lote' => 'A-001',
                    // 'vencimiento' => '2022-10-30'
                ]
            ],
            'transporte' => [
                'tipo' => 1,
                'modalidad' => 1,
                'tipoResponsable' => 1,
                'condicionNegociacion' => 'FOB',
                'numeroManifiesto' => 'AF-2541',
                'numeroDespachoImportacion' => '153223232332',
                'inicioEstimadoTranslado' => '2025-09-22',
                'finEstimadoTranslado' => '2025-09-22',
                'paisDestino' => 'PRY',
                'paisDestinoNombre' => 'Paraguay',
                'salida' => [
                    'direccion' => 'Paraguay',
                    'numeroCasa' => '3232',
                    'complementoDireccion1' => 'Entre calle 2',
                    'complementoDireccion2' => 'y Calle 7',
                    'departamento' => 11,
                    // 'departamentoDescripcion' => 'ALTO PARANA',
                    'distrito' => 143,
                    // 'distritoDescripcion' => 'DOMINGO MARTINEZ DE IRALA',
                    'ciudad' => 3344,
                    // 'ciudadDescripcion' => 'PASO ITA (INDIGENA)',
                    'pais' => 'PRY',
                    'paisDescripcion' => 'Paraguay',
                    'telefonoContacto' => '097x'
                ],
                'entrega' => [
                    'direccion' => 'Paraguay',
                    'numeroCasa' => '3232',
                    'complementoDireccion1' => 'Entre calle 2',
                    'complementoDireccion2' => 'y Calle 7',
                    'departamento' => 11,
                    // 'departamentoDescripcion' => 'ALTO PARANA',
                    'distrito' => 143,
                    // 'distritoDescripcion' => 'DOMINGO MARTINEZ DE IRALA',
                    'ciudad' => 3344,
                    // 'ciudadDescripcion' => 'PASO ITA (INDIGENA)',
                    'pais' => 'PRY',
                    'paisDescripcion' => 'Paraguay',
                    'telefonoContacto' => '097x'
                ],
                'vehiculo' => [
                    'tipo' => 'Camioneta',
                    'marca' => 'Nissan',
                    'documentoTipo' => 1,
                    'documentoNumero' => '232323-1',
                    'obs' => '',
                    'numeroMatricula' => 'AABC123',
                    'numeroVuelo' => '32123'
                ],
                'transportista' => [
                    'contribuyente' => true,
                    'nombre' => 'EMPRESA DE TRANSPORTE VANGUARDIA S A C I',
                    'ruc' => '80011235-0',
                    'documentoTipo' => 1,
                    'documentoNumero' => '235306',
                    'direccion' => 'y Calle 7',
                    'obs' => 11,
                    'pais' => 'PRY',
                    'paisDescripcion' => 'Paraguay',
                    'chofer' => [
                        'documentoNumero' => '32324253',
                        'nombre' => 'Jose Benitez',
                        'direccion' => 'Jose Benitez'
                    ],
                    'agente' => [
                        'nombre' => 'Jose Benitez',
                        'ruc' => '251458-1',
                        'direccion' => 'Jose Benitez'
                    ]
                ]
            ],
            'documentoAsociado' => [
                'formato' => 1,
                'cdc' => $cdc_asociado, // '4280457167700000001000000000001'
            ]
        ];
        return $data;
    }

    public function crear_lote($tipo_documento = 1, $numero_factura = 1, $cdc_asociado = '')
    {
        if ($tipo_documento == 1) {
            // Factura Electrónica
            $data = $this->data_demo_facturacion_electronica($numero_factura);
        } elseif ($tipo_documento == 4) {
            // Autofactura Electrónica
            $data = $this->data_demo_autofactura_electronica($numero_factura, $cdc_asociado);
        } elseif ($tipo_documento == 5) {
            // Nota de Crédito Electrónica
            $data = $this->data_demo_nota_credito_electronica($numero_factura, $cdc_asociado);
        } elseif ($tipo_documento == 6) {
            // Nota de Débito Electrónica
            $data = $this->data_demo_nota_debito_electronica($numero_factura, $cdc_asociado);
        } elseif ($tipo_documento == 7) {
            // Nota de Remisión Electrónica
            $data = $this->data_demo_nota_remision_electronica($numero_factura, $cdc_asociado);
        }

        return $data;
        // Puedes ajustar los query params según tu necesidad
        $queryParams = '?draft=true&xml=true&qr=true&tax=true';

        $endpoint = $this->api_url . $this->tenantId . "/lote/create" . $queryParams;

        $headers = [
            "Authorization: Bearer api_key_" . $this->api_key,
            "Content-Type: application/json; charset=utf-8"
        ];

        // Inicializar cURL
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        // Mostrar respuesta para debug
        header('Content-Type: application/json');
        if ($http_status == 200) {
            $result = json_decode($response, true);
            if (isset($result['success']) && $result['success']) {
                echo json_encode([
                    "ok" => true,
                    "loteId" => $result['result']['loteId'],
                    "deList" => $result['result']['deList']
                ]);
            } else {
                echo json_encode([
                    "ok" => false,
                    "error" => isset($result['error']) ? $result['error'] : 'Error desconocido',
                    "errores" => isset($result['errores']) ? $result['errores'] : []
                ]);
            }
        } else {
            echo json_encode([
                "ok" => false,
                "error" => "HTTP $http_status",
                "curl_error" => $curl_error,
                "response" => $response
            ]);
        }
    }

    
    public function get_departamentos(){
        $this->output->set_content_type('application/json')->set_output(json_encode(fs_get_departamentos()));
    }
    public function get_distritos($departamento_id){
        $this->output->set_content_type('application/json')->set_output(json_encode(fs_get_distritos(null, $departamento_id)));
    }
    public function get_ciudades($distrito_id){
        $this->output->set_content_type('application/json')->set_output(json_encode(fs_get_ciudades(null, $distrito_id)));
    }
    public function get_paises(){
        $this->output->set_content_type('application/json')->set_output(json_encode(fs_get_paises()));
    }

    function test_venta($id){
            $Sale = $this->Sale_model->getSaleInfo($id);

            
            // Obtener detalles de la venta
            $result = $this->db->query("SELECT s.*,u.full_name,c.name as customer_name,c.gst_number as nro_documento,c.tipo_ident,m.name as payment_method
            FROM tbl_sales s
            LEFT JOIN tbl_customers c ON(s.customer_id=c.id)
            LEFT JOIN tbl_users u ON(s.user_id=u.id)
            LEFT JOIN tbl_payment_methods m ON(s.payment_method_id=m.id)
            -- LEFT JOIN tbl_tables tbl ON(s.table_id=tbl.id) 
            WHERE s.id=$id AND s.del_status = 'Live'")->row();
            $sale = $result;

            
            $customer_info = $this->Sale_model->getCustomerInfoById($sale->customer_id);

            // Obtener detalles de items de la venta
            $this->db->select("l.*, fm.iva_tipo, fm.code");
            $this->db->from("tbl_sales_details l");
            $this->db->join("tbl_food_menus fm", "fm.id = l.food_menu_id", "left");
            $this->db->where("l.sales_id", $id);
            $resultados = $this->db->get();
			$sale_detalles = $resultados->result();
            echo '<pre>';
            var_dump($sale); 
            var_dump($customer_info); 
            var_dump($sale_detalles); 
            echo '<pre>';
            
    }

    public function test_sucursal($sifen_sucursal_id = 1){
        // ; // Asigna un ID de sucursal válido
        $punto_expedicion_valido = fs_get_punto_expedicion_activo($sifen_sucursal_id);
        echo '<pre>';
        var_dump($punto_expedicion_valido); 
        echo '</pre>';

    }
    public function generar_factura_electronica($sale_id)
    {
        $this->load->helper('factura_send');
        $this->load->library('facturasend');

        // --- 1. Validaciones Previas (con manejo de errores JSON) ---
        $sale = $this->db->where('id', $sale_id)->get('tbl_sales')->row();
        if (!$sale) {
            return ['status' => 'error', 'message' => "Venta con ID '$sale_id' no encontrada."];
        }

        $customer = $this->db->where('id', $sale->customer_id)->get('tbl_customers')->row();
        if (!$customer) {
            return ['status' => 'error', 'message' => "Cliente con ID '$sale->customer_id' no encontrado."];
        }

        if (!isset($customer->es_contribuyente) || !$customer->es_contribuyente) {
            return ['status' => 'error', 'message' => 'Facturación cancelada: El cliente no es contribuyente o su RUC no es válido.'];
        }
        
        // --- Validación de Punto de Expedición ---
        $outlet_id = $sale->outlet_id;
        if (!$outlet_id) {
            return ['status' => 'error', 'message' => 'La venta no tiene una sucursal (outlet) asignada.'];
        }
        
        $outlet_info = $this->Common_model->getDataById($outlet_id, 'tbl_outlets');
        if (!$outlet_info || !isset($outlet_info->sifen_sucursal_id) || empty($outlet_info->sifen_sucursal_id)) {
            return ['status' => 'error', 'message' => "La sucursal del sistema (Outlet ID: $outlet_id) no tiene un ID de sucursal SIFEN configurado ('sifen_sucursal_id')."];
        }
        
        $sifen_sucursal_id = $outlet_info->sifen_sucursal_id;
        
        $punto_expedicion_valido = fs_get_punto_expedicion_activo($sifen_sucursal_id);

        // Usamos la nueva función de bloqueo
        // $punto_expedicion_valido = fs_get_and_lock_punto_expedicion($sifen_sucursal_id);

        if (!$punto_expedicion_valido) { 
            $this->db->trans_rollback(); // Liberamos cualquier bloqueo potencial
            return ['status' => 'error', 'message' => "No se encontró un punto de expedición activo y bloqueable para la sucursal SIFEN ID: $sifen_sucursal_id."];
        }

        
        if (!$punto_expedicion_valido) { 
            return ['status' => 'error', 'message' => "No se encontró un punto de expedición activo con timbrado vigente para la sucursal SIFEN ID: $sifen_sucursal_id."];
        }

        // --- 2. Recopilar Datos (Igual que antes) ---
        // (Tu código de recolección de datos es correcto y no necesita cambios)
        $sale_details = $this->db->select("sd.*, fm.code, fm.iva_tipo")
                                ->from("tbl_sales_details sd")
                                ->join("tbl_food_menus fm", "fm.id = sd.food_menu_id", "left")
                                ->where("sd.sales_id", $sale_id)
                                ->get()->result();
        $user = $this->db->where('id', $sale->user_id)->get('tbl_users')->row();
        $payment_methods_json =  '[{"payment_id":"1","amount":"'.$sale->total_payable.'"}]';
        // json_decode($sale->self_order_content, true)['payment_object'] ??
        $payment_methods = json_decode($payment_methods_json, true);

        // --- 3. NORMALIZACIÓN DE DATOS ---
        // (Tu código de normalización es correcto y no necesita cambios)
        
        $es_contribuyente = ((strpos($customer->gst_number, '-') !== false));

        $cliente_normalizado = [
            'documentoNumero'   => $customer->gst_number,
            'id_sistema'        => $customer->id,
            'es_contribuyente'  => $es_contribuyente,
            'ruc'               => $customer->gst_number,
            'nombre'            => $customer->name,
            'nombre_fantasia'   => $customer->nombre_fantasia,
            'email'             => $customer->email,
            'direccion'         => $customer->address,
            'es_proveedor_estado' => (bool)$customer->es_proveedor_estado,
            'tipo_contribuyente'=> (int)$customer->tipo_contribuyente,
            'tipo_documento'    => (int)$customer->tipo_documento,
            'departamento_id'   => (int)$customer->departamento_id,
            'distrito_id'       => (int)$customer->distrito_id,
            'ciudad_id'         => (int)$customer->ciudad_id,
            'pais_codigo'       => $customer->codigo_pais,
            'numero_casa'       => (string)intval($customer->numero_casa),
        ];
        $usuario_normalizado = [
            'id_sistema' => $user->id,
            'nombre'     => $user->full_name,
            'documento'  => $user->documento,
            'cargo'      => 'Vendedor'
        ];
        $items_normalizados = array_map(function($item) {
            $iva_valor = 0;
            if ($item->iva_tipo == '10') $iva_valor = 10;
            elseif ($item->iva_tipo == '5') $iva_valor = 5;
            return [
                'codigo'          => $item->code,
                'descripcion'     => $item->menu_name,
                'cantidad'        => $item->qty,
                'precio_unitario' => $item->menu_unit_price,
                'iva'             => $iva_valor,
            ];
        }, $sale_details);
        $condicion_normalizada = [
            'tipo' => 1,
            'entregas' => array_map(function($pago) {
                return [
                    'tipo'   => fs_map_payment_method($pago['payment_id']),
                    'monto'  => $pago['amount'],
                    'moneda' => 'PYG'
                ];
            }, $payment_methods)
        ];

        $invoice_data = [
            'venta_id_sistema'  => $sale->id,
            'fecha'             => date('Y-m-d\TH:i:s'),
            'moneda'            => 'PYG',
            'tipo_documento'    => 1, // 1 = Factura Electrónica
            'punto_expedicion'  => $punto_expedicion_valido,
            'cliente'           => $cliente_normalizado,
            'usuario'           => $usuario_normalizado,
            'items'             => $items_normalizados,
            'condicion_venta'   => $condicion_normalizada
        ];

        // --- 4. Llamar al Helper ---
        $resultado_helper = fs_create_and_send_invoice($invoice_data);

        // --- 5. Finalizar Transacción y Devolver Resultado ---
        $fe_result_data = []; // Datos específicos de la FE para devolver

        // --- 5. Devolver Respuesta Final ---
        if (isset($resultado_helper['status']) && $resultado_helper['status'] === 'success') {
            $this->db->trans_commit(); // Éxito, se confirman todos los cambios y se libera el bloqueo.
            // --- Caso de Éxito ---
            $update_data_sales = [
                'py_factura_id' => $resultado_helper['factura_py_id'],
                'fe_estado'     => 'EXITO',
                'fe_cdc'        => $resultado_helper['cdc'],
                'fe_lote_id'    => $resultado_helper['loteId'],
                'fe_error_log'  => NULL // Limpiar cualquier error previo
            ];

            // Actualizar la tabla de ventas
            $this->db->where('id', $sale_id)->update('tbl_sales', $update_data_sales);

            $fe_result_data = [
                'status'  => 'success',
                'message' => 'Factura generada y enviada correctamente.',
                'loteId'  => $resultado_helper['loteId'],
                'cdc'     => $resultado_helper['cdc']
            ];
        } else {
            $this->db->trans_rollback(); 
            $fe_result_data = [
                'status'  => 'error',
                'message' => $resultado_helper['message'] ?? 'Error desconocido en el helper.',
                'details' => $resultado_helper['api_response'] ?? null
            ];
        }
        
        return $fe_result_data; // Devuelve siempre un array
    }

        /**
     * Genera una factura electrónica de forma universal.
     *
     * @param object $sale El objeto de la venta (puede ser de tbl_sales o tbl_kitchen_sales).
     * @param array $sale_details El array con los detalles/items de la venta.
     * @param string $source_table El nombre de la tabla de origen ('tbl_sales' o 'tbl_kitchen_sales').
     * @return array Resultado de la operación de facturación.
     */
    public function generar_factura_electronica_universal($sale, $sale_details, $source_table)
    {
        $this->load->helper('factura_send');
        $this->load->library('facturasend');

        $sale_id = $sale->id; // El ID de la venta, ya sea de kitchen o sales.

        // --- 1. Validaciones Previas ---
        if (!$sale) {
            return ['status' => 'error', 'message' => "El objeto de venta proporcionado está vacío."];
        }

        // Validación de Cliente
        $customer = $this->db->where('id', $sale->customer_id)->get('tbl_customers')->row();
        if (!$customer) {
            return ['status' => 'error', 'message' => "Cliente con ID '$sale->customer_id' no encontrado."];
        }
        if (!isset($customer->es_contribuyente) || !$customer->es_contribuyente) {
            return ['status' => 'info', 'message' => 'Facturación omitida: El cliente no es contribuyente o su RUC no es válido.'];
        }
        
        // Validación de Punto de Expedición
        $outlet_info = $this->Common_model->getDataById($sale->outlet_id, 'tbl_outlets');
        if (!$outlet_info || !isset($outlet_info->sifen_sucursal_id) || empty($outlet_info->sifen_sucursal_id)) {
            return ['status' => 'error', 'message' => "La sucursal (Outlet ID: $sale->outlet_id) no tiene un ID de SIFEN configurado."];
        }
        
        $punto_expedicion_valido = fs_get_punto_expedicion_activo($outlet_info->sifen_sucursal_id);
        if (!$punto_expedicion_valido) { 
            return ['status' => 'error', 'message' => "No se encontró un punto de expedición activo para la sucursal SIFEN ID: {$outlet_info->sifen_sucursal_id}."];
        }

        // --- 2. Recopilar y Normalizar Datos ---
        $user = $this->db->where('id', $sale->user_id)->get('tbl_users')->row();
        $payment_methods = json_decode('[{"payment_id":"1","amount":"'.$sale->total_payable.'"}]', true);

        if (!((int)$customer->departamento_id > 0 && (int)$customer->distrito_id > 0 && (int)$customer->ciudad_id > 0)) {
            $customer->departamento_id = $this->config->item('sifen_default_departamento');
            $customer->distrito_id = $this->config->item('sifen_default_distrito');
            $customer->ciudad_id = $this->config->item('sifen_default_ciudad');
        }
        // Normalización (tu código existente es correcto)
        $es_contribuyente = ((strpos($customer->gst_number, '-') !== false));
        $cliente_normalizado = [
            'id_sistema'        => $customer->id, 'es_contribuyente'  => $es_contribuyente,
            'ruc'               => $customer->gst_number, 
            'documentoNumero'   => $customer->gst_number, 
            'nombre'            => $customer->name,
            'nombre_fantasia'   => $customer->nombre_fantasia, 'email'             => $customer->email,
            'direccion'         => $customer->address, 'es_proveedor_estado' => (bool)$customer->es_proveedor_estado,
            'tipo_contribuyente'=> (int)$customer->tipo_contribuyente, 'tipo_documento'    => (int)$customer->tipo_documento,
            'departamento_id'   => (int)$customer->departamento_id, 'distrito_id'       => (int)$customer->distrito_id,
            'ciudad_id'         => (int)$customer->ciudad_id, 'pais_codigo'       => $customer->codigo_pais,
            'numero_casa'       => (string)intval($customer->numero_casa),
        ];
        $usuario_normalizado = [ 'id_sistema' => $user->id, 'nombre' => $user->full_name, 'documento' => $user->documento, 'cargo' => 'Vendedor' ];
        $items_normalizados = array_map(function($item) {
            $iva_valor = 0;
            if (isset($item->iva_tipo)) { // Desde tbl_sales_details
                if ($item->iva_tipo == '10') $iva_valor = 10; elseif ($item->iva_tipo == '5') $iva_valor = 5;
            } else { // Desde tbl_kitchen_sales_details (asumiendo estructura similar)
                $food_menu = $this->db->select('iva_tipo')->where('id', $item->food_menu_id)->get('tbl_food_menus')->row();
                if($food_menu){
                    if ($food_menu->iva_tipo == '10') $iva_valor = 10; elseif ($food_menu->iva_tipo == '5') $iva_valor = 5;
                }
            }
            return [
                'codigo' => $this->db->where('id', $item->food_menu_id)->get('tbl_food_menus')->row()->code ?? 'N/A',
                'descripcion' => $item->menu_name, 'cantidad' => $item->qty,
                'precio_unitario' => $item->menu_unit_price, 'iva' => $iva_valor,
            ];
        }, $sale_details);
        $condicion_normalizada = [ 'tipo' => 1, 'entregas' => array_map(function($pago) { return ['tipo' => fs_map_payment_method($pago['payment_id']),'monto' => $pago['amount'],'moneda' => 'PYG']; }, $payment_methods)];

        $invoice_data = [
            'venta_id_sistema'  => $sale_id, 'fecha' => date('Y-m-d\TH:i:s'),
            'moneda' => 'PYG', 'tipo_documento' => 1, // 1 = Factura Electrónica
            'punto_expedicion' => $punto_expedicion_valido,
            'cliente' => $cliente_normalizado, 'usuario' => $usuario_normalizado,
            'items' => $items_normalizados, 'condicion_venta' => $condicion_normalizada
        ];
        
        // --- 4. Llamar al Helper ---
        $resultado_helper = fs_create_and_send_invoice($invoice_data);

        // --- 5. Procesar Resultado ---
        if (isset($resultado_helper['status']) && $resultado_helper['status'] === 'success') {
            $update_data = [
                'py_factura_id' => $resultado_helper['factura_py_id'], 'fe_estado' => 'EXITO',
                'fe_cdc' => $resultado_helper['cdc'], 'fe_lote_id' => $resultado_helper['loteId'],
                'fe_error_log' => NULL
            ];
            // Actualizar la tabla de origen (sea kitchen o sales)
            $this->db->where('id', $sale_id)->update($source_table, $update_data);
            
            return [
                'status' => 'success', 'message' => 'Factura generada y enviada.',
                'loteId' => $resultado_helper['loteId'], 'cdc' => $resultado_helper['cdc']
            ];
        } else {
            // Guardar el error en la tabla de origen
            $update_data = [
                'fe_estado' => 'ERROR_API',
                'fe_error_log' => json_encode($resultado_helper['message'] ?? 'Error desconocido en el helper.')
            ];
            $this->db->where('id', $sale_id)->update($source_table, $update_data);

            return [
                'status' => 'error', 'message' => $resultado_helper['message'] ?? 'Error desconocido.',
                'details' => $resultado_helper['api_response'] ?? null
            ];
        }
    }

    /**
     * Endpoint para generar una factura electrónica para una orden en cocina (delivery)
     * buscando por el número de venta (sale_no).
     */
    public function generar_factura_desde_cocina()
    {
        // Define el tipo de contenido de la respuesta como JSON desde el principio.
        $this->output->set_content_type('application/json');

        $sale_no = $this->input->post('sale_no');
        if (!$sale_no) {
            $this->output->set_output(json_encode(['status' => 'error', 'message' => 'El número de orden (sale_no) no fue proporcionado.']));
            return;
        }

        $this->db->trans_begin();

        // 1. Obtener los datos de la orden de cocina usando sale_no
        $kitchen_sale = $this->db->where('sale_no', $sale_no)->get('tbl_kitchen_sales')->row();
        if (!$kitchen_sale) {
            $this->db->trans_rollback();
            $this->output->set_output(json_encode(['status' => 'error', 'message' => "Orden con número '$sale_no' no encontrada en la cocina."]));
            return;
        }

        // // 2. Verificar si ya tiene una factura exitosa
        // if ($kitchen_sale->fe_estado === 'EXITO' && !empty($kitchen_sale->fe_cdc)) {
        //     $this->db->trans_rollback();
        //     $this->output->set_output(json_encode([
        //         'status' => 'info', 
        //         'message' => 'Esta orden ya tiene una factura generada.', 
        //         'cdc' => $kitchen_sale->fe_cdc
        //     ]));
        //     return;
        // }

        // 3. Obtener los detalles de la orden usando el ID de la orden encontrada
        $kitchen_sale_details = $this->db->where('sales_id', $kitchen_sale->id)->get('tbl_kitchen_sales_details')->result();
        if (empty($kitchen_sale_details)) {
            $this->db->trans_rollback();
            $this->output->set_output(json_encode(['status' => 'error', 'message' => 'La orden no tiene items para facturar.']));
            return;
        }

        // 4. Llamar a la función universal de facturación
        $fe_result = $this->generar_factura_electronica_universal($kitchen_sale, $kitchen_sale_details, 'tbl_kitchen_sales');

        if ($fe_result['status'] === 'success') {
            $this->db->trans_commit();
        } else {
            $this->db->trans_rollback();
        }

        // 5. Devolver el resultado de la facturación
        $this->output->set_output(json_encode($fe_result));
    }

    public function getCustomerDue() {
        $customer_id = $_GET['customer_id']; 

        $customer = $this->db->get_where('tbl_customers', [
            'id' => $customer_id,
            'company_id' => '1',
            'del_status' => 'Live'
        ])->row();

        $remaining_due = $this->Customer_due_receive_model->getCustomerDue($customer_id);
        if ($remaining_due === null) {
            $remaining_due = 0;
        }
        $formatted_due = (isset($remaining_due) && $remaining_due ? getAmtP($remaining_due) : getAmtP(0));


        $response = [
            'customer' => $customer,
            'remaining_due' => $remaining_due,
            'formatted_due' => $formatted_due
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    
    /**
     * Procesa el pago de una deuda de cliente a través de AJAX desde la vista POS.
     */
    public function addCustomerDueReceiveAjax() {
        // Establecer el tipo de contenido de la respuesta a JSON
        header('Content-Type: application/json');

        // Función para enviar respuestas JSON y terminar la ejecución
        $json_response = function($status, $msg, $data = []) {
            echo json_encode(['status' => $status, 'msg' => $msg, 'data' => $data]);
            exit;
        };

        // 1. Verificación de seguridad y estado del registro (casi idéntico al original)
        $is_waiter = $this->session->userdata('is_waiter');
        $designation = $this->session->userdata('designation');
        if ($designation != "Waiter" && $this->session->has_userdata('is_online_order') != "Yes" && !isFoodCourt()) {
            $user_id = $this->session->userdata('user_id');
            $outlet_id = $this->session->userdata('outlet_id');
            if ($this->Common_model->isOpenRegister($user_id, $outlet_id) == 0) {
                $json_response('error', lang('register_open_msg'));
            }
        }

        // 2. Reglas de validación del formulario
        $this->form_validation->set_rules('date', lang('date'), 'required');
        $this->form_validation->set_rules('amount', lang('amount'), 'required|numeric');
        $this->form_validation->set_rules('customer_id', lang('customer'), 'required|integer');
        $this->form_validation->set_rules('payment_id', lang('payment_method'), 'required|integer');
        $this->form_validation->set_rules('note', lang('note'), 'max_length[200]');
        
        // Asignar mensajes de error personalizados si lo deseas
        $this->form_validation->set_error_delimiters('', ''); // Para limpiar los delimitadores por defecto

        if ($this->form_validation->run() == TRUE) {// Asumiendo que las reglas ya están seteadas
            try {
                $outlet_id = $this->session->userdata('outlet_id');

                $due_receive_info = [
                    'date' => date("Y-m-d H:i:s"),
                    'only_date' => date("Y-m-d", strtotime($this->input->post('date'))),
                    'amount' => $this->input->post('amount'),
                    'reference_no' => $this->Customer_due_receive_model->generateReferenceNo($outlet_id),
                    'customer_id' => $this->input->post('customer_id'),
                    'payment_id' => $this->input->post('payment_id'),
                    'note' => $this->input->post('note'),
                    'counter_id' => $this->session->userdata('counter_id'),
                    'user_id' => $this->session->userdata('user_id'),
                    'outlet_id' => $outlet_id,
                    'company_id' => $this->session->userdata('company_id')
                ];

                $due_receive_info = $this->security->xss_clean($due_receive_info);
                
                // Usamos insertInformationAndGetId para obtener el ID del nuevo registro
                $payment_id = $this->Common_model->insertInformationAndGetId($due_receive_info, "tbl_customer_due_receives");

                if ($payment_id) {
                    // --- ¡NUEVO! CALCULAR EL SALDO RESTANTE ---
                    $new_remaining_due = getCustomerDue($this->input->post('customer_id'));

                    // Preparamos los datos para la impresión y los devolvemos
                    $customer_info = $this->Common_model->getDataById($due_receive_info['customer_id'], 'tbl_customers');
                    $payment_method_info = $this->Common_model->getDataById($due_receive_info['payment_id'], 'tbl_payment_methods');
                    
                    $print_data = [
                        'payment_id' => $payment_id,
                        'ref_no' => $due_receive_info['reference_no'],
                        'date' => date($this->session->userdata('date_format'), strtotime($due_receive_info['only_date'])),
                        'customer' => $customer_info->name,
                        'payment_method' => $payment_method_info->name,
                        'amount' => getAmtP($due_receive_info['amount']),
                        'note' => $due_receive_info['note'],
                        // --- ¡NUEVO! AÑADIMOS EL SALDO FORMATEADO A LA RESPUESTA ---
                        'new_balance' => getAmtP($new_remaining_due)
                    ];
                    $json_response('ok', lang('insertion_success'), $print_data);
                } else {
                    $json_response('error', 'No se pudo guardar el pago.');
                }

            } catch (Exception $e) {
                $json_response('error', $e->getMessage());
            }

        } else {
            // 6. Si la validación falla, enviar los errores
            $errors = [
                'date' => form_error('date'),
                'amount' => form_error('amount'),
                'customer_id' => form_error('customer_id'),
                'payment_id' => form_error('payment_id'),
                'note' => form_error('note')
            ];
            $json_response('validation_error', 'Por favor, corrige los errores.', $errors);
        }
    }
    // En tu controlador: Customer_due_receive.php

    /**
     * Genera el hash para la app de impresión para un recibo de pago específico.
     * @param int $payment_id El ID del pago desde tbl_customer_due_receives
     */
    public function printer_app_due_receive($payment_id) {
        // 1. OBTENER LOS DATOS DEL PAGO
        // Llama a la función del modelo para obtener todos los detalles necesarios.
        $payment = $this->Common_model->get_customer_due_receive_details($payment_id);

        // Validar si el pago existe
        if (!$payment) {
            // Puedes manejar el error como prefieras.
            // Por ahora, simplemente detenemos la ejecución.
            echo "Error: Pago no encontrado.";
            return;
        }

        // 2. OBTENER LA INFORMACIÓN DE LA EMPRESA DESDE LA SESIÓN
        $company = [
            'name' => $this->session->userdata('outlet_name'),
            'address' => $this->session->userdata('address'),
            'phone' => $this->session->userdata('phone'),
            'footer' => $this->session->userdata('invoice_footer'),
        ];

        // --- ¡NUEVO! CALCULAR EL SALDO RESTANTE ---
        $new_remaining_due = $this->Customer_due_receive_model->getCustomerDue($payment->customer_id);

        // 3. CONSTRUIR EL ARRAY DE CONTENIDO (réplica de la lógica de printer_app_bill)
        $content = [];

        // Encabezado de la empresa
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => $company['name']];
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => $company['address']];
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'Tel: ' . $company['phone']];
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => '']; // Espacio

        // Título del documento
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => '--------------------------------'];
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => 'RECIBO DE PAGO'];
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => '--------------------------------'];

        // Información del recibo
        $content[] = ['type' => 'text', 'align' => 'left', 'text' => 'Fecha: ' . date($this->session->userdata('date_format'), strtotime($payment->only_date))];
        $content[] = ['type' => 'text', 'align' => 'left', 'text' => 'Ref. No: 0' . escape_output($payment_id)];
        $content[] = ['type' => 'text', 'align' => 'left', 'text' => 'Cliente: ' . escape_output($payment->customer_name)];
        $content[] = ['type' => 'text', 'align' => 'left', 'text' => 'Recibido por: ' . escape_output($payment->user_name)];
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => '']; // Espacio

        // Detalles del pago
        $content[] = ['type' => 'extremos', 'textLeft' => 'Método de Pago', 'textRight' => escape_output($payment->payment_method_name)];

        // Nota (si existe)
        if ($payment->note) {
            $content[] = ['type' => 'text', 'align' => 'center', 'text' => '----------'];
            $content[] = ['type' => 'text', 'align' => 'left', 'text' => 'Nota: ' . escape_output($payment->note)];
        }

        // Total
        $content[] = ['type' => 'text', 'align' => 'right', 'text' => '----------'];
        $content[] = ['type' => 'extremos', 'textLeft' => 'TOTAL PAGADO:', 'textRight' => getAmtPCustom($payment->amount)];
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => '']; // Espacio

        // Pie del ticket
        // --- SECCIÓN DE SALDO AÑADIDA ---
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => '--------------------------------'];
        $content[] = ['type' => 'extremos', 
            'textLeft' => 'Saldo actual:', 
            'textRight' => getAmtPCustom($new_remaining_due)
        ];
        $content[] = ['type' => 'text', 'align' => 'left', 'text' => '('.date($this->session->userdata('date_format')." H:i").')'];
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => '']; // Espacio
        

        $content[] = ['type' => 'text', 'align' => 'center', 'text' => "\n"];
        $content[] = ['type' => 'text', 'align' => 'center', 'text' => "\n"];
        $content[] = ['type' => 'cut'];

        // 4. OBTENER CONFIGURACIÓN DE LA IMPRESORA (lógica idéntica a printer_app_bill)
        $company_id = $this->session->userdata('company_id');
        $company_data = $this->Common_model->getDataById($company_id, "tbl_companies");

        $counter_details = $this->Common_model->getPrinterIdByCounterId($this->session->userdata('counter_id'));
        $printer = $this->Common_model->getPrinterInfoById($counter_details->invoice_printer_id);
        $path = @$printer->path;

        $print_format = $company_data->print_format_bill;
        $width = ($print_format == "80mm") ? 80 : 58;

        // 5. CREAR EL OBJETO DE SOLICITUD DE IMPRESIÓN
        $printRequest = [
            'printer' => $path,
            'width' => $width,
            'content' => filterArrayRecursivelyEscPos($content) // Usamos la misma función de filtrado
        ];
        
        // 6. CONVERTIR A JSON, COMPRIMIR Y CODIFICAR EN BASE64
        $data = json_encode($printRequest);
        $compressed = gzdeflate($data, 9);
        $base64 = base64_encode($compressed);

        // 7. DEVOLVER EL HASH FINAL
        echo $base64;
    }

    
    /**
     * OBTIENE LOS ÚLTIMOS 10 PAGOS DE DEUDA DE LA SUCURSAL ACTUAL
     * Devuelve una lista en formato JSON para ser usada por AJAX.
     */
    public function getLastTenDueReceives() {
        header('Content-Type: application/json');
        $outlet_id = $this->session->userdata('outlet_id');
        
        $this->db->select('r.*, c.name as customer_name, u.full_name as user_name, pm.name as payment_method_name');
        $this->db->from('tbl_customer_due_receives r');
        $this->db->join('tbl_customers c', 'c.id = r.customer_id', 'left');
        $this->db->join('tbl_users u', 'u.id = r.user_id', 'left');
        $this->db->join('tbl_payment_methods pm', 'pm.id = r.payment_id', 'left');
        $this->db->where('r.outlet_id', $outlet_id);
        $this->db->where('r.del_status', 'Live');
        $this->db->order_by('r.id', 'DESC');
        $this->db->limit(10);
        
        $query = $this->db->get();
        $result = $query->result();

        // Limpiar datos para el cliente
        foreach ($result as $row) {
            $row->only_date = date($this->session->userdata('date_format'), strtotime($row->only_date));
            $row->amount = getAmtP($row->amount);
        }

        echo json_encode($result);
        exit;
    }

    /**
     * NUEVO: Obtiene los detalles de un pago de deuda específico para reimpresión.
     * @param int $payment_id El ID del pago a buscar.
     */
    public function getDueReceiveDetailsForPrint($payment_id) {
        header('Content-Type: application/json');

        // 1. Obtener los detalles del pago (usando un join similar al de la lista)
        $this->db->select('r.*, c.name as customer_name, pm.name as payment_method_name');
        $this->db->from('tbl_customer_due_receives r');
        $this->db->join('tbl_customers c', 'c.id = r.customer_id', 'left');
        $this->db->join('tbl_payment_methods pm', 'pm.id = r.payment_id', 'left');
        $this->db->where('r.id', $payment_id);
        $this->db->where('r.del_status', 'Live');
        $payment = $this->db->get()->row();

        if (!$payment) {
            echo json_encode(['status' => 'error', 'msg' => 'Pago no encontrado.']);
            exit;
        }

        // 2. Calcular el saldo ACTUAL del cliente
        $current_balance = $this->Customer_due_receive_model->getCustomerDue($payment->customer_id);

        // 3. Construir el objeto de datos para la impresión (misma estructura que antes)
        $print_data = [
            'payment_id'      => (int)$payment->id,
            'customer_id'     => (int)$payment->customer_id,
            'ref_no'          => $payment->reference_no,
            'date'            => date($this->session->userdata('date_format'), strtotime($payment->only_date)),
            'customer'        => $payment->customer_name,
            'payment_method'  => $payment->payment_method_name,
            'amount'          => getAmtP($payment->amount),
            'note'            => $payment->note,
            'new_balance'     => getAmtP($current_balance)
        ];

        // 4. Devolver los datos en un formato JSON consistente
        echo json_encode(['status' => 'ok', 'data' => $print_data]);
        exit;
    }

}
