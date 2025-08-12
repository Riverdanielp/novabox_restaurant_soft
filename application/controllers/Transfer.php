<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Transfer extends Cl_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Authentication_model');
        $this->load->model('Sale_model');
        $this->load->model('Transfer_model');
        $this->load->model('Master_model');
        $this->load->model('Common_model');
        $this->load->model('Inventory_adjustment_model');
        $this->load->model('Inventory_model');
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

        //start check access function
        $segment_2 = $this->uri->segment(2);
        $segment_3 = $this->uri->segment(3);
        $controller = "112";
        $function = "";
    
        if($segment_2=="transfers" || 
            $segment_2=="transferTicketJson" ||
            $segment_2=="addTransferDinamico" ||
            $segment_2=="ajaxBorrarTransferDetalle" ||
            $segment_2=="getTransferIngredients" ||
            $segment_2=="ajaxListarTransferDetalles" ||
            $segment_2=="ajaxAgregarTransferDetalle" ||
            $segment_2=="ajaxBuscarIngredientesPorNombre" ||
            $segment_2=="ajaxBuscarIngredientePorCodigo"||
            $segment_2=="ajaxGuardarTransferInfo"  ||
            $segment_2=="transferDinamico"
            ){
            $function = "view";
        }elseif($segment_2=="addEditTransfer" && $segment_3){
            $function = "update";
        }elseif($segment_2=="transferDetails" && $segment_3){
            $function = "view_details";
        }elseif($segment_2=="addEditTransfer"){
            $function = "add";
        }elseif($segment_2=="deleteTransfer"){
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
     * transfers info
     * @access public
     * @return void
     * @param no
     */
    public function transfers() {
        $outlet_id = $this->session->userdata('outlet_id');
        $data = array();
        $data['transfers'] = $this->Transfer_model->getAllTrasferData($outlet_id);
        $data['main_content'] = $this->load->view('transfer/transfers', $data, TRUE);
        $this->load->view('userHome', $data);
    }
     /**
     * delete Transfer
     * @access public
     * @return void
     * @param int
     */
    public function deleteTransfer($id) {
        $role = $this->session->userdata('role');
        if($role=="Admin"){
            $id = $this->custom->encrypt_decrypt($id, 'decrypt');
            $this->Common_model->deleteStatusChangeWithChild($id, $id, "tbl_transfer", "tbl_transfer_ingredients", 'id', 'transfer_id');
            $this->session->set_flashdata('exception', lang('delete_success'));
        }else{
            $this->session->set_flashdata('exception_error', lang('error_transfer'));
        }
        redirect('Transfer/transfers');
    }
     /**
     * add/Edit Transfer
     * @access public
     * @return void
     * @param int
     */
    public function addEditTransfer($encrypted_id = "") {


        $id = $this->custom->encrypt_decrypt($encrypted_id, 'decrypt');
        $outlet_id = $this->session->userdata('outlet_id');
        $company_id = $this->session->userdata('company_id');

        $transfer_info = array();

        if ($id == "") {
            $transfer_info['reference_no'] = $this->Transfer_model->generatePurRefNo($outlet_id);
        } else {
            $transfer_info['reference_no'] = $this->Common_model->getDataById($id, "tbl_transfer")->reference_no;
        }

        if (htmlspecialcharscustom($this->input->post('submit'))) {

            $this->form_validation->set_rules('reference_no', lang('ref_no'), 'required|max_length[50]');
            if ($id == "") {
                $this->form_validation->set_rules('to_outlet_id', lang('to_outlet'), 'required|max_length[50]');
            }
            $this->form_validation->set_rules('status', "Status", 'required');
            $this->form_validation->set_rules('date', lang('date'), 'required|max_length[50]');
            if ($this->form_validation->run() == TRUE) {
                $transfer_info['reference_no'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('reference_no')));
                $transfer_info['date'] = date('Y-m-d', strtotime($this->input->post($this->security->xss_clean('date'))));
                $transfer_info['note_for_sender'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('note_for_sender')));
                $transfer_info['note_for_receiver'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('note_for_receiver')));
                $transfer_info['status'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('status')));
                $transfer_info['transfer_type'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('transfer_type')));
                $transfer_info['user_id'] = $this->session->userdata('user_id');
                if($this->input->post($this->security->xss_clean('received_date'))){
                    $transfer_info['received_date'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('received_date')));
                }
                if ($id == "") {
                    $transfer_info['from_outlet_id'] = $this->session->userdata('outlet_id');
                    $transfer_info['to_outlet_id'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('to_outlet_id')));
                    $transfer_info['outlet_id'] = $this->session->userdata('outlet_id');

                    $transfer_id = $this->Common_model->insertInformation($transfer_info, "tbl_transfer");
                    /*This all variables could not be escaped because this is an array field*/
                    $this->saveTransferIngredients($_POST['ingredient_id'], $transfer_id, $this->session->userdata('outlet_id'),$transfer_info['to_outlet_id'],$transfer_info['status'],'');
                    $this->session->set_flashdata('exception', lang('insertion_success'));
                } else {
                    $transfer_details = $this->Common_model->getDataById($id, "tbl_transfer");
                    $outlet_id = $this->session->userdata('outlet_id');
                    if($outlet_id!=$transfer_details->to_outlet_id  && $outlet_id==$transfer_details->outlet_id){
                        $transfer_info['from_outlet_id'] = $this->session->userdata('outlet_id');
                        $transfer_info['to_outlet_id'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('to_outlet_id')));
                        $transfer_info['outlet_id'] = $this->session->userdata('outlet_id');
                    }
                    $this->Common_model->updateInformation($transfer_info, $id, "tbl_transfer");
                    $this->Common_model->deletingMultipleFormData('transfer_id', $id, 'tbl_transfer_ingredients');
                    $this->Common_model->deletingMultipleFormData('transfer_id', $id, 'tbl_transfer_received_ingredients');
                    $to_outlet_id = isset($transfer_info['to_outlet_id']) ? $transfer_info['to_outlet_id'] : $transfer_details->to_outlet_id;
                    /*This variable could not be escaped because this is an array field*/
                    // --- Confirmar en la BD origen si es multiDB y status = 1 ---
                    if (
                        isset($transfer_details->remote_transfer_id) && 
                        $transfer_details->remote_transfer_id && 
                        isset($transfer_details->from_db_key) &&
                        $transfer_info['status'] == '1'
                    ) {
                        // Actualiza el status a 1 en la BD origen, y los ingredientes
                        confirmar_transferencia_en_origen(
                            $transfer_details->remote_transfer_id,
                            $transfer_details->from_db_key // este es nombre_sistema (ej: 'MiAbuelaMarket')
                        );
                    }
                    $this->saveTransferIngredients($_POST['ingredient_id'], $id, $transfer_details->outlet_id,$to_outlet_id,$transfer_info['status'],$transfer_details->to_outlet_id);
                    $this->session->set_flashdata('exception',lang('update_success'));
                }

                redirect('Transfer/transfers');
            } else {
                if ($id == "") {
                    $data = array();
                    $data['pur_ref_no'] = $this->Transfer_model->generatePurRefNo($outlet_id);
                    $data['ingredients'] = $this->Transfer_model->getIngredientListWithUnitAndPrice($company_id);
                    $data['outlets'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, "tbl_outlets");
                    $data['food_menus'] = $this->Common_model->getAllByTable("tbl_food_menus");

                    foreach ($data['food_menus'] as $key=>$value){
                        $total = 0;
                        $all_ings = $this->Transfer_model->getTotalCostAmount($value->id);
                        foreach ($all_ings as $vl){
                            $last_purchase_price = getLastPurchaseAmount($vl->ingredient_id);
                            $conversion_rate = 1;
                            if($vl->conversion_rate){
                                $conversion_rate =  $vl->conversion_rate;
                            }
                            $inline_total = ($last_purchase_price/$conversion_rate)*$vl->consumption;
                            $total+=$inline_total;
                        }
                           if ($this->session->userdata('collect_tax')=='Yes'){
                                $total_return_amount = getTaxAmount($value->sale_price,$value->tax_information);
                            }else{
                                $total_return_amount = 0;
                            }

                        $data['food_menus'][$key]->ings_total_cost = $total;
                        $data['food_menus'][$key]->total_tax = $total_return_amount;
                    }
                    $data['main_content'] = $this->load->view('transfer/addTransfer', $data, TRUE);
                    $this->load->view('userHome', $data);
                } else {
                    $data = array();
                    $data['encrypted_id'] = $encrypted_id;
                    $data['transfer_details'] = $this->Common_model->getDataById($id, "tbl_transfer");
                    $data['food_details'] = $this->Transfer_model->getFoodDetails($id);
                    $data['ingredients'] = $this->Transfer_model->getIngredientListWithUnitAndPrice($company_id);
                    $data['food_menus'] = $this->Common_model->getAllByTable("tbl_food_menus");

                    foreach ($data['food_menus'] as $key=>$value){
                        $total = 0;
                        $all_ings = $this->Transfer_model->getTotalCostAmount($value->id);
                        foreach ($all_ings as $vl){
                            $last_purchase_price = getLastPurchaseAmount($vl->ingredient_id);
                            $conversion_rate = 1;
                            if($vl->conversion_rate){
                                $conversion_rate =  $vl->conversion_rate;
                            }
                            $inline_total = ($last_purchase_price/$conversion_rate)*$vl->consumption;
                            $total+=$inline_total;
                        }
                           if ($this->session->userdata('collect_tax')=='Yes'){
                                $total_return_amount = getTaxAmount($value->sale_price,$value->tax_information);
                            }else{
                                $total_return_amount = 0;
                            }

                        $data['food_menus'][$key]->ings_total_cost = $total;
                        $data['food_menus'][$key]->total_tax = $total_return_amount;
                    }
                    $data['outlets'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, "tbl_outlets");
                    $data['main_content'] = $this->load->view('transfer/editTransfer', $data, TRUE);
                    $this->load->view('userHome', $data);
                }
            }
        } else {
            if ($id == "") {
                $data = array();
                $data['pur_ref_no'] = $this->Transfer_model->generatePurRefNo($outlet_id);
                $data['ingredients'] = $this->Transfer_model->getIngredientListWithUnitAndPrice($company_id);
                
                $outlets1 = $this->Common_model->getAllByCompanyIdForDropdown($company_id, "tbl_outlets");
                $outlets2 = get_all_outlets_multi();
                $data['outlets'] =  array_merge($outlets1, $outlets2);

                $data['food_menus'] = $this->Common_model->getAllByTable("tbl_food_menus");

                foreach ($data['food_menus'] as $key=>$value){
                    $total = 0;
                    $all_ings = $this->Transfer_model->getTotalCostAmount($value->id);
                    foreach ($all_ings as $vl){
                        $last_purchase_price = getLastPurchaseAmount($vl->ingredient_id);
                        $conversion_rate = 1;
                        if($vl->conversion_rate){
                            $conversion_rate =  $vl->conversion_rate;
                        }
                        $inline_total = ($last_purchase_price/$conversion_rate)*$vl->consumption;
                        $total+=$inline_total;
                    }
                    if ($this->session->userdata('collect_tax')=='Yes'){
                        $total_return_amount = getTaxAmount($value->sale_price,$value->tax_information);
                    }else{
                        $total_return_amount = 0;
                    }
                    $data['food_menus'][$key]->ings_total_cost = $total;
                    $data['food_menus'][$key]->total_tax = $total_return_amount;
                }
                $data['main_content'] = $this->load->view('transfer/addTransfer', $data, TRUE);
                $this->load->view('userHome', $data);
            } else {
                $data = array();
                $data['encrypted_id'] = $encrypted_id;
                $data['transfer_details'] = $this->Common_model->getDataById($id, "tbl_transfer");
                $data['food_details'] = $this->Transfer_model->getFoodDetails($id);
                $data['ingredients'] = $this->Transfer_model->getIngredientListWithUnitAndPrice($company_id);
                $data['food_menus'] = $this->Common_model->getAllByTable("tbl_food_menus");
                foreach ($data['food_menus'] as $key=>$value){
                    $total = 0;
                    $all_ings = $this->Transfer_model->getTotalCostAmount($value->id);
                    foreach ($all_ings as $vl){
                        $last_purchase_price = getLastPurchaseAmount($vl->ingredient_id);
                        $conversion_rate = 1;
                        if($vl->conversion_rate){
                            $conversion_rate =  $vl->conversion_rate;
                        }
                        $inline_total = ($last_purchase_price/$conversion_rate)*$vl->consumption;
                        $total+=$inline_total;
                    }
                    if ($this->session->userdata('collect_tax')=='Yes'){
                        $total_return_amount = getTaxAmount($value->sale_price,$value->tax_information);
                    }else{
                        $total_return_amount = 0;
                    }

                    $data['food_menus'][$key]->ings_total_cost = $total;
                    $data['food_menus'][$key]->total_tax = $total_return_amount;
                }

                $outlets1 = $this->Common_model->getAllByCompanyIdForDropdown($company_id, "tbl_outlets");
                $outlets2 = get_all_outlets_multi();
                $data['outlets'] =  array_merge($outlets1, $outlets2);

                $data['main_content'] = $this->load->view('transfer/editTransfer', $data, TRUE);
                $this->load->view('userHome', $data);
            }
        }
    }
     /**
     * save Transfer Ingredients
     * @access public
     * @return void
     * @param string
     * @param int
     * @param string
     */
    public function saveTransferIngredients($transfer_ingredients, $transfer_id, $from_outlet,$to_outlet,$status,$to_outlet_id='') {
        foreach ($transfer_ingredients as $row => $ingredient_id):
            $data_sale_consumptions_detail = array();
            $data_sale_consumptions_detail['status'] = $status;
            /*This all variables could not be escaped because this is an array field*/
            $data_sale_consumptions_detail['ingredient_id'] = $_POST['ingredient_id'][$row];
            $data_sale_consumptions_detail['quantity_amount'] = $_POST['quantity_amount'][$row];
            $data_sale_consumptions_detail['total_cost'] = isset($_POST['total_cost'][$row]) && $_POST['total_cost'][$row]?$_POST['total_cost'][$row]:0;
            $data_sale_consumptions_detail['single_cost_total'] = isset($_POST['single_cost_total'][$row]) && $_POST['single_cost_total'][$row]?$_POST['single_cost_total'][$row]:0;
            $data_sale_consumptions_detail['total_sale_amount'] = isset($_POST['total_sale_amount'][$row]) && $_POST['total_sale_amount'][$row]?$_POST['total_sale_amount'][$row]:0;
            $data_sale_consumptions_detail['total_tax'] = isset($_POST['total_tax'][$row]) && $_POST['total_tax'][$row]?$_POST['total_tax'][$row]:0;
            $data_sale_consumptions_detail['single_total_sale_amount'] = isset($_POST['single_total_sale_amount'][$row]) && $_POST['single_total_sale_amount'][$row]?$_POST['single_total_sale_amount'][$row]:0;
            $data_sale_consumptions_detail['single_total_tax'] = isset($_POST['single_total_tax'][$row]) && $_POST['single_total_tax'][$row]?$_POST['single_total_tax'][$row]:0;
            $data_sale_consumptions_detail['transfer_id'] = $transfer_id;
            $data_sale_consumptions_detail['from_outlet_id'] = $from_outlet;
            $data_sale_consumptions_detail['transfer_type'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('transfer_type')));
            if($to_outlet_id!=''){
                $data_sale_consumptions_detail['to_outlet_id'] = $to_outlet_id;
            }else{
                $data_sale_consumptions_detail['to_outlet_id'] = $to_outlet;
            }
            $data_sale_consumptions_detail['del_status'] = 'Live';

            $this->db->insert('tbl_transfer_ingredients',$data_sale_consumptions_detail);
        endforeach;

    }
     /**
     * transfer Details
     * @access public
     * @return void
     * @param int
     */
    public function transferDetails($id) {
        $encrypted_id = $id;
        $id = $this->custom->encrypt_decrypt($id, 'decrypt');

        $data = array();
        $data['encrypted_id'] = $encrypted_id;
        $data['transfer_details'] = $this->Common_model->getDataById($id, "tbl_transfer");
        $data['food_details'] = $this->Transfer_model->getFoodDetails($id);
        $data['main_content'] = $this->load->view('transfer/transferDetails', $data, TRUE);
        $this->load->view('userHome', $data);
    }

    public function transferTicketJson($encrypted_id)
    {
        $id = $this->custom->encrypt_decrypt($encrypted_id, 'decrypt');
        $transfer = $this->Common_model->getDataById($id, "tbl_transfer");
        $food_details = $this->Transfer_model->getFoodDetails($id);
        if (!$transfer) {
            echo json_encode(['success' => false, 'error' => 'No data found']);
            return;
        }

        $session_outlet_id = $this->session->userdata('outlet_id');
        $is_envio = true;//($transfer->from_outlet_id == $session_outlet_id);

        // FROM OUTLET
        if ($transfer->from_db_key == 'default') {
            $from_outlet_name = getOutletNameById($transfer->from_outlet_id);
        } else {
            // En multiDB, si es envío, mostramos el local, si es recepción mostramos el remote
            $from_outlet_name = (isset($transfer->remote_outlet_name) ? $transfer->remote_outlet_name : getOutletNameById($transfer->from_outlet_id));
        }

        // TO OUTLET
        if ($transfer->to_db_key == 'default') {
            $to_outlet_name = getOutletNameById($transfer->to_outlet_id);
        } else {
            // En multiDB, si es envío mostramos remote_outlet_name, si es recepción el local
            $to_outlet_name = $transfer->remote_outlet_name;
        }

        // Construye el array de líneas para el ticket
        $content = [];
        $content[] = [
            'type' => 'text',
            'align' => 'center',
            'text' => '<h5>Transferencia</h5>' . "\n" . lang('ref_no') . ': ' . $transfer->reference_no
        ];
        $content[] = [
            'type' => 'text',
            'align' => 'left',
            'text' => lang('date') . ': ' . date($this->session->userdata('date_format'), strtotime($transfer->date))
        ];
        $content[] = [
            'type' => 'text',
            'align' => 'left',
            'text' => lang('added_by') . ': ' . userName($transfer->user_id)
        ];
        $content[] = [
            'type' => 'text',
            'align' => 'left',
            'text' => lang('from_outlet') . ': ' . escape_output($from_outlet_name)
        ];
        $content[] = [
            'type' => 'text',
            'align' => 'left',
            'text' => lang('to_outlet') . ': ' . escape_output($to_outlet_name)
        ];
        $content[] = [
            'type' => 'text',
            'align' => 'left',
            'text' => lang('status') . ': ' . (($transfer->status==1)?lang("Received"): (($transfer->status==2)?lang("Draft"):lang("Sent")))
        ];
        if ($transfer->received_date) {
            $content[] = [
                'type' => 'text',
                'align' => 'left',
                'text' => lang('received_date') . ': ' . date($this->session->userdata('date_format'), strtotime($transfer->received_date))
            ];
        }
        $content[] = [
            'type' => 'text',
            'align' => 'center',
            'text' => lang('details')
        ];

        $sn = '-';
        foreach ($food_details as $fd) {
            if ($fd->transfer_type == 1) {
                $name = getIngredientNameById($fd->ingredient_id) . " (" . getIngredientCodeById($fd->ingredient_id) . ")";
            } else {
                $name = getFoodMenuNameById($fd->ingredient_id) . " (" . getFoodMenuCodeById($fd->ingredient_id) . ")";
            }
            $content[] = [
                'type' => 'extremos',
                'textLeft' => "{$sn} {$name}",
                'textRight' => number_format($fd->quantity_amount,2,",", "."),
            ];
            $sn++;
        }

        // Notas
        if (strlen($transfer->note_for_sender) > 0) {
            $content[] = [
                'type' => 'text',
                'align' => 'left',
                'text' => "\n" . "\n" . lang('note_for_sender') . ': ' . $transfer->note_for_sender
            ];
        }
        $content[] = ['type' => 'text','align' => 'left','text' => ''];
        if (strlen($transfer->note_for_receiver) > 0) {
            $content[] = ['type' => 'text','align' => 'left','text' => "\n" . "\n" . lang('note_for_receiver'). ':' . "\n" . $transfer->note_for_receiver];
        }
        $content[] = ['type' => 'text','align' => 'left','text' => '' . "\n" . "\n" . "\n" . "\n" . "\n"];
        $content[] = ['type' => 'cut'];
        $content[] = ['type' => 'text','align' => 'center','text' => 'FIRMA DE ENVÍO'];

        $content[] = ['type' => 'text','align' => 'left','text' => '' . "\n" . "\n" . "\n" . "\n" . "\n"];
        $content[] = ['type' => 'cut'];
        $content[] = ['type' => 'text','align' => 'center','text' => 'FIRMA DE TRANSPORTE'];

        $content[] = ['type' => 'text','align' => 'left','text' => '' . "\n" . "\n" . "\n" . "\n" . "\n"];
        $content[] = ['type' => 'cut'];
        $content[] = ['type' => 'text','align' => 'center','text' => 'FIRMA DE RECEPCIÓN'];

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'content' => $content,
            'width' => '72',
        ]);
    }

    public function transferDinamico($id = null) {
        $outlet_id  = $this->session->userdata('outlet_id');
        $company_id = $this->session->userdata('company_id');
        $user_id    = $this->session->userdata('user_id');

        $transfer_details = null;
        $status = 2; // Draft por defecto
        $disable_to_outlet = false;
        $status_editable = true;
        $status_editable_emisor = true;
        $status_editable_receptor = false;
        $detalle_editable = true;
        $nota_editable = true;
        $transfer_editable = true;
        $transfer_id = '';
        $pur_ref_no = '';
        $note_for_sender = '';
        $note_for_receiver = '';
        $es_emisor = false;
        $es_receptor = false;
        if ($id) {
            $id = $this->custom->encrypt_decrypt($id, 'decrypt');
            $transfer_details = $this->Common_model->getDataById($id, "tbl_transfer");
            if ($transfer_details) {
                $transfer_id = $transfer_details->id;
                $pur_ref_no = $transfer_details->reference_no;
                $status = intval($transfer_details->status);
                $note_for_sender = $transfer_details->note_for_sender;
                $note_for_receiver = $transfer_details->note_for_receiver;
                $to_outlet_id = $transfer_details->to_outlet_id;

                // --- AQUÍ CALCULA EMISOR/RECEPTOR ---
                if ($outlet_id == $transfer_details->from_outlet_id) {
                    $es_emisor = true;
                } else if ($outlet_id == $transfer_details->to_outlet_id) {
                    $es_receptor = true;
                }

                if ($es_receptor && $transfer_id) {
                    // Redirecciona al flujo clásico de recepción
                    redirect('Transfer/addEditTransfer/' . $this->custom->encrypt_decrypt($transfer_id, 'encrypt'));
                    return;
                }
                // Lógica de permisos y editable igual que ya tienes...
                if ($es_emisor) {
                    $status_editable_emisor = ($status != 1); // Puede editar si no está recibido
                    $status_editable_receptor = false;
                    $status_editable = ($status != 1);
                    $disable_to_outlet = ($status != 2); // Solo en Draft puede cambiar outlet
                    $detalle_editable = ($status != 1);
                    $nota_editable = ($status != 1);
                    $transfer_editable = ($status != 1);
                } else if ($es_receptor) {
                    $status_editable_emisor = false;
                    $status_editable_receptor = ($status == 3); // Solo si está en Sent puede marcar como Received
                    $status_editable = ($status == 3);
                    $disable_to_outlet = true;
                    $detalle_editable = false;
                    $nota_editable = ($status == 3);
                    $transfer_editable = ($status == 3);
                } else {
                    // Otro usuario: bloqueado
                    $status_editable = false;
                    $status_editable_emisor = false;
                    $status_editable_receptor = false;
                    $disable_to_outlet = true;
                    $detalle_editable = false;
                    $nota_editable = false;
                    $transfer_editable = false;
                }
            }
        } else {
            // Nueva transferencia: el usuario es emisor
            $pur_ref_no = $this->Transfer_model->generatePurRefNo($outlet_id);
            $transfer_id = '';
            $status = 2; // Draft
            $note_for_sender = '';
            $note_for_receiver = '';
            $disable_to_outlet = false;
            $status_editable = true;
            $status_editable_emisor = true;
            $status_editable_receptor = false;
            $detalle_editable = true;
            $nota_editable = true;
            $transfer_editable = true;
            $es_emisor = true; // <<--- AQUÍ
        }

        $ingredients = $this->Transfer_model->getIngredientListWithUnitAndPrice($company_id);
        $outlets1 = $this->Common_model->getAllByCompanyIdForDropdown($company_id, "tbl_outlets");
        $outlets2 = get_all_outlets_multi();
        $outlets = array_merge($outlets1, $outlets2);

        // echo '<pre>';
        // var_dump($outlets);
        // echo '</pre>';

        $data = [
            'pur_ref_no' => $pur_ref_no,
            'ingredients' => $ingredients,
            'outlets' => $outlets,
            'transfer_id' => $transfer_id,
            'status' => $status,
            'note_for_sender' => $note_for_sender,
            'note_for_receiver' => $note_for_receiver,
            'disable_to_outlet' => $disable_to_outlet,
            'status_editable' => $status_editable,
            'status_editable_emisor' => $status_editable_emisor,
            'status_editable_receptor' => $status_editable_receptor,
            'detalle_editable' => $detalle_editable,
            'nota_editable' => $nota_editable,
            'transfer_editable' => $transfer_editable,
            'transfer_details' => $transfer_details,
            'es_emisor' => $es_emisor,               // <--- AGREGA ESTO
            'es_receptor' => $es_receptor,           // <--- Y ESTO
        ];
        $data['main_content'] = $this->load->view('transfer/addTransferDinamico', $data, TRUE);
        $this->load->view('userHome', $data);
    }

    public function addTransferDinamico() {
        $outlet_id = $this->session->userdata('outlet_id');
        $company_id = $this->session->userdata('company_id');

        // Genera número de referencia
        $pur_ref_no = $this->Transfer_model->generatePurRefNo($outlet_id);
        $ingredients = $this->Transfer_model->getIngredientListWithUnitAndPrice($company_id);
        $outlets = $this->Common_model->getAllByCompanyIdForDropdown($company_id, "tbl_outlets");

        $data = [
            'pur_ref_no' => $pur_ref_no,
            'ingredients' => $ingredients,
            'outlets' => $outlets,
        ];
        $data['main_content'] = $this->load->view('transfer/addTransferDinamico', $data, TRUE);
        $this->load->view('userHome', $data);
    }

public function ajaxAgregarTransferDetalle() {
    $transfer_id = $this->input->post('transfer_id', true);
    $to_outlet_id_raw = $this->input->post('to_outlet_id');

    // Detecta destino multi-DB y también recupera el nombre del outlet si está en el ID enriquecido
    if (strpos($to_outlet_id_raw, '|') !== false) {
        $id_parts = explode('|', $to_outlet_id_raw);
        $to_outlet_id_int = $id_parts[0];
        $to_db_key = isset($id_parts[1]) ? $id_parts[1] : 'default';
        $remote_outlet_name = isset($id_parts[2]) ? $id_parts[2] : '';
    } else {
        $to_outlet_id_int = $to_outlet_id_raw;
        $to_db_key = 'default';
        $remote_outlet_name = '';
    }

    // Si no existe, crea el transfer en Draft con los nuevos campos
    $is_new_transfer = false;
    if (!$transfer_id) {
        $data = [
            'reference_no' => $this->input->post('reference_no'),
            'date' => $this->input->post('date'),
            'status' => 2, // Draft
            'transfer_type' => $this->input->post('transfer_type'),
            'user_id' => $this->session->userdata('user_id'),
            'from_outlet_id' => $this->session->userdata('outlet_id'),
            'to_outlet_id' => isset($id_parts[1]) ? null : $to_outlet_id_int,
            'to_db_key' => $to_db_key,
            'from_db_key' => 'default',
            'outlet_id' => $this->session->userdata('outlet_id'),
            'remote_outlet_id'    => isset($id_parts[1]) ? $to_outlet_id_int : null,
            'remote_outlet_name' => $remote_outlet_name,
            'del_status' => 'Live',
        ];
        if ($this->input->post('note_for_sender')) {
            $data['note_for_sender'] = $this->input->post('note_for_sender');
        }
        if ($this->input->post('note_for_receiver')) {
            $data['note_for_receiver'] = $this->input->post('note_for_receiver');
        }
        $transfer_id = $this->Common_model->insertInformation($data, "tbl_transfer");
        $is_new_transfer = true;
    } else {
        $this->db->where('id', $transfer_id)->update('tbl_transfer', ['remote_outlet_name' => $remote_outlet_name]);
    }

    // Guardar detalle en la BD local
    $detalle = [
        'ingredient_id' => $this->input->post('ingredient_id'),
        'quantity_amount' => $this->input->post('quantity_amount'),
        'total_cost' => $this->input->post('total_cost'),
        'single_cost_total' => $this->input->post('single_cost_total'),
        'transfer_id' => $transfer_id,
        'from_outlet_id' => $this->session->userdata('outlet_id'),
        'to_outlet_id' => isset($id_parts[1]) ? null : $to_outlet_id_int,
        'transfer_type' => $this->input->post('transfer_type'),
        'del_status' => 'Live'
    ];
    $detalle_id = $this->Common_model->insertInformation($detalle, "tbl_transfer_ingredients");

    // --- Sincronización multiDB: SIEMPRE si $to_db_key != 'default' ---
    if ($to_db_key != 'default') {
        // Buscar el remote_transfer_id en la local
        $transfer_local = $this->db->get_where('tbl_transfer', ['id' => $transfer_id])->row();
        $remote_transfer_id = isset($transfer_local->remote_transfer_id) ? $transfer_local->remote_transfer_id : null;
        $from_outlet_id = $this->session->userdata('outlet_id');
        $outlet_name = getOutletNameById($from_outlet_id);

        // Si no existe en la remota, crearla
        if (!$remote_transfer_id) {
            $data = (array)$transfer_local;
            $remote_transfer_id = crear_transferencia_remota($transfer_id, $data, $to_db_key, $outlet_name, $to_outlet_id_int);
            // Guardar el id remoto en la local
            $this->db->where('id', $transfer_id)->update('tbl_transfer', [
                'remote_transfer_id' => $remote_transfer_id,
                'sync_status' => 1
            ]);
        }

        // Crear el ingrediente remoto (y food_menu si aplica)
        $ingredient_id_remote = get_or_create_remote_ingredient($detalle['ingredient_id'], $to_db_key);

        // Insertar el detalle en la remota
        $db_remota = $this->load->database($to_db_key, TRUE);
        $detalle_remoto = [
            'ingredient_id' => $ingredient_id_remote,
            'quantity_amount' => $detalle['quantity_amount'],
            'total_cost' => $detalle['total_cost'],
            'single_cost_total' => $detalle['single_cost_total'],
            'transfer_id' => $remote_transfer_id, // id de la transferencia remota
            'from_outlet_id' => null, //$detalle['from_outlet_id'],
            'to_outlet_id' => $to_outlet_id_int, // id del outlet destino
            'transfer_type' => $detalle['transfer_type'],
            'del_status' => 'Live'
        ];
        $db_remota->insert('tbl_transfer_ingredients', $detalle_remoto);
    }

    echo json_encode(['success' => true, 'transfer_id' => $transfer_id, 'detalle_id' => $detalle_id]);
}

public function ajaxGuardarTransferInfo() {
    $reload = false;
    $transfer_id   = $this->input->post('transfer_id', true);
    $to_outlet_id  = $this->input->post('to_outlet_id', true);
    $reference_no  = $this->input->post('reference_no', true);
    $date          = $this->input->post('date', true);
    $status        = $this->input->post('status', true);

    // --- Manejo de to_outlet_id y to_db_key y remote_outlet_name ---
    if (strpos($to_outlet_id, '|') !== false) {
        $id_parts = explode('|', $to_outlet_id);
        $to_outlet_id_int = $id_parts[0];
        $to_db_key = isset($id_parts[1]) ? $id_parts[1] : 'default';
        $remote_outlet_name = isset($id_parts[2]) ? $id_parts[2] : '';
    } else {
        $to_outlet_id_int = $to_outlet_id;
        $to_db_key = 'default';
        $remote_outlet_name = '';
    }

    // Si no viene el outlet_id en el post y ya existe la transferencia, cargarlo de la base de datos:
    if (!$to_outlet_id && $transfer_id) {
        $transfer = $this->db->get_where('tbl_transfer', ['id' => $transfer_id])->row();
        if ($transfer) {
            $to_outlet_id_int = $transfer->to_outlet_id;
            $to_db_key = $transfer->to_db_key;
            $remote_outlet_name = $transfer->remote_outlet_name;
        }
    }

    if (!$reference_no) {
        echo json_encode(['success' => false, 'msg' => 'Referencia inválida.']);
        return;
    }
    if (!$date) {
        echo json_encode(['success' => false, 'msg' => 'Fecha inválida.']);
        return;
    }
    if (!$status || !in_array($status, ['1','2','3'])) {
        echo json_encode(['success' => false, 'msg' => 'Status inválida.']);
        return;
    }

    $data = [
        'reference_no'    => $reference_no,
        'date'            => $date,
        'to_outlet_id'    => isset($id_parts[1]) ? null : $to_outlet_id_int,
        'to_db_key'       => $to_db_key,
        'from_db_key'     => 'default', // o la actual si usas multi-DB para emisor
        'remote_outlet_id'    => isset($id_parts[1]) ? $to_outlet_id_int : null,
        'remote_outlet_name' => $remote_outlet_name,
        'status'          => $status,
    ];

    if ($this->input->post('note_for_sender')) {
        $data['note_for_sender'] = $this->input->post('note_for_sender');
    }
    if ($this->input->post('note_for_receiver')) {
        $data['note_for_receiver'] = $this->input->post('note_for_receiver');
    }

    // ---- Si se está editando, verifica si cambió la locación ----
    if ($transfer_id) {
        $transfer_old = $this->db->get_where('tbl_transfer', ['id' => $transfer_id])->row();
        $old_to_db_key = $transfer_old ? $transfer_old->to_db_key : null;
        $reload = $transfer_old && $transfer_old->status != $status ? true : false;
        $old_remote_transfer_id = $transfer_old ? $transfer_old->remote_transfer_id : null;

        // Si el destino cambió y había transferencia remota, elimínala en la otra BD
        if ($old_to_db_key && $old_to_db_key != $to_db_key && $old_remote_transfer_id) {
            eliminar_transferencia_remota($old_remote_transfer_id, $old_to_db_key);
            $this->db->where('id', $transfer_id)
                ->update('tbl_transfer', [
                    'remote_deleted' => 1,
                    'remote_transfer_id' => null,
                    'sync_status' => 0
                ]);
        }

        // UPDATE
        $this->db->where('id', $transfer_id)->update('tbl_transfer', $data);
    } else {
        // INSERT (crear cabecera nueva)
        $user_id    = $this->session->userdata('user_id');
        $company_id = $this->session->userdata('company_id');
        $from_outlet_id = $this->session->userdata('outlet_id');
        $data['reference_no'] = $reference_no;
        $data['from_outlet_id'] = $from_outlet_id;
        $data['outlet_id'] = $from_outlet_id;
        $data['user_id'] = $user_id;
        $this->db->insert('tbl_transfer', $data);
        $transfer_id = $this->db->insert_id();
    }

    // --- Sincronización multiDB: SIEMPRE si $to_db_key != 'default' ---
    if ($to_db_key != 'default') {
        $transfer_local = $this->db->get_where('tbl_transfer', ['id' => $transfer_id])->row();
        $from_outlet_id = $this->session->userdata('outlet_id');
        $outlet_name = getOutletNameById($from_outlet_id);
        $remote_transfer_id = $transfer_local && isset($transfer_local->remote_transfer_id) ? $transfer_local->remote_transfer_id : null;

        // Si no existe en la remota, crearla
        if (!$remote_transfer_id) {
            $remote_transfer_id = crear_transferencia_remota($transfer_id, (array)$transfer_local, $to_db_key, $outlet_name, $to_outlet_id_int);
            $this->db->where('id', $transfer_id)->update('tbl_transfer', [
                'remote_transfer_id' => $remote_transfer_id,
                'sync_status' => 1
            ]);
        } else {
            // Actualiza la transferencia remota si ya existe
            crear_transferencia_remota($transfer_id, (array)$transfer_local, $to_db_key, $outlet_name, $to_outlet_id_int);
        }
    }

    echo json_encode(['success' => true, 'transfer_id' => $transfer_id, 'reload' => $reload]);
}

    public function ajaxListarTransferDetalles() {
        $transfer_id = $this->input->post('transfer_id', true);
        $items = $this->getTransferIngredients($transfer_id);
        echo json_encode(['success' => true, 'items' => $items]);
    }

    public function getTransferIngredients($transfer_id) {
        $this->db->select("tbl_transfer_ingredients.*, tbl_ingredients.name, tbl_ingredients.code");
        $this->db->from("tbl_transfer_ingredients");
        $this->db->join('tbl_ingredients', 'tbl_ingredients.id = tbl_transfer_ingredients.ingredient_id', 'left');
        $this->db->where("tbl_transfer_ingredients.transfer_id", $transfer_id);
        $this->db->where("tbl_transfer_ingredients.del_status", 'Live');
        $this->db->order_by('tbl_transfer_ingredients.id', 'DESC');
        return $this->db->get()->result();
    }

    public function ajaxBorrarTransferDetalle() { 
        $detalle_id = $this->input->post('detalle_id', true);
        if ($detalle_id) {
            $this->db->where('id', $detalle_id)
                    ->update('tbl_transfer_ingredients', ['del_status' => 'Deleted']);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }


    public function ajaxBuscarIngredientesPorNombre() {
        $term = $this->input->post('term', true);
        $result = $this->Inventory_adjustment_model->buscarIngredientesPorNombre($term);
        $sugerencias = [];
        foreach ($result as $row) {
            $sugerencias[] = [
                'id' => $row->id,
                'code' => $row->code,
                'name' => $row->name,
                'label' => $row->code . ' - ' . $row->name,
                'unit_name' => $row->unit_name,
            ];
        }
        echo json_encode(['success' => true, 'items' => $sugerencias]);
    }

    /**
     * Ajax: Buscar ingrediente por código
     */
    public function ajaxBuscarIngredientePorCodigo() {
        $code = $this->input->post('codigo', true);
        $row = $this->Inventory_adjustment_model->getIngredientByCode($code);
        if ($row) {
            $stock = $this->Inventory_model->getCurrentInventory($row->id);
            // $last_purchase_cost = $this->Inventory_adjustment_model->getLastPurchasePrice($row->id);
            echo json_encode([
                'success' => true,
                'id' => $row->id,
                'code' => $row->code,
                'name' => $row->name,
                'unit' => $row->unit_id,
                'stock' => $stock['total_stock'],
                'costo' => $row->consumption_unit_cost,
            ]);
        } else {
            echo json_encode(['success' => false]);
        }
    }

}
