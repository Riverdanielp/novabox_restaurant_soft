<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Inventory_adjustment extends Cl_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Authentication_model');
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
        $controller = "131";
        $function = "";

        if($segment_2=="inventoryAdjustments"){
            $function = "view";
        }elseif(($segment_2=="addEditInventoryAdjustment") && $segment_3){
            $function = "update";
        }elseif($segment_2=="inventoryAdjustmentDetails" && $segment_3){
            $function = "view_details";
        }elseif($segment_2=="addEditInventoryAdjustment"){
            $function = "add";
        }elseif($segment_2=="deleteInventoryAdjustment"){
            $function = "delete";
        }elseif($segment_2=="ajuste"){
            $function = "ajuste";
        }elseif($segment_2=="ajaxBuscarIngredientePorCodigo"){
            $function = "ajaxBuscarIngredientePorCodigo";
        }elseif($segment_2=="ajaxGuardarAjusteDinamico"){
            $function = "ajaxGuardarAjusteDinamico";
        }elseif($segment_2=="ajaxListarAjusteDetalles"){
            $function = "ajaxListarAjusteDetalles";
        }else{
            $this->session->set_flashdata('exception_er', lang('menu_not_permit_access'));
            redirect('Authentication/userProfile');
        }

        
        if( $segment_2=="ajuste" ||
            $segment_2=="ajaxBuscarIngredientePorCodigo" ||
            $segment_2=="ajaxGuardarAjusteDinamico" ||
            $segment_2=="ajaxListarAjusteDetalles"
            ){
        } else {
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
     * inventory Adjustments
     * @access public
     * @return void
     * @param no
     */
    public function inventoryAdjustments() {
        $outlet_id = $this->session->userdata('outlet_id');

        $data = array();
        $data['inventory_adjustments'] = $this->Common_model->getAllByOutletId($outlet_id, "tbl_inventory_adjustment");
        $data['main_content'] = $this->load->view('inventoryAdjustment/inventoryAdjustments', $data, TRUE);
        $this->load->view('userHome', $data);
    }
     /**
     * delete Inventory Adjustment
     * @access public
     * @return void
     * @param int
     */
    public function deleteInventoryAdjustment($id) {
        $id = $this->custom->encrypt_decrypt($id, 'decrypt');
        $this->Common_model->deleteStatusChangeWithChild($id, $id, "tbl_inventory_adjustment", "tbl_inventory_adjustment_ingredients", 'id', 'inventory_adjustment_id');
        $this->session->set_flashdata('exception', lang('delete_success'));
        redirect('Inventory_adjustment/inventoryAdjustments');
    }
     /**
     * add/Edit Inventory Adjustment
     * @access public
     * @return void
     * @param int
     */
    public function addEditInventoryAdjustment($encrypted_id = "") {

        $id = $this->custom->encrypt_decrypt($encrypted_id, 'decrypt');
        $outlet_id = $this->session->userdata('outlet_id');
        $company_id = $this->session->userdata('company_id');

        if (htmlspecialcharscustom($this->input->post('submit'))) {
            $this->form_validation->set_rules('date',  lang('date'), 'required|max_length[50]');
            $this->form_validation->set_rules('note',  lang('note'), 'max_length[200]');
            $this->form_validation->set_rules('employee_id',  lang('responsible_person'), 'required|max_length[50]');
            if ($this->form_validation->run() == TRUE) {
                $ia_info = array();
                $ia_info['reference_no'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('reference_no')));
                $ia_info['date'] = date('Y-m-d', strtotime($this->input->post($this->security->xss_clean('date'))));
                $ia_info['note'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('note')));
                $ia_info['employee_id'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('employee_id')));
                $ia_info['user_id'] = $this->session->userdata('user_id');
                $ia_info['outlet_id'] = $this->session->userdata('outlet_id');
                if ($id == "") {
                    $inventory_adjustment_id = $this->Common_model->insertInformation($ia_info, "tbl_inventory_adjustment");
                    $this->saveInventoryAdjustmentIngredients($_POST['ingredient_id'], $inventory_adjustment_id, 'tbl_inventory_adjustment_ingredients');
                    $this->session->set_flashdata('exception',  lang('insertion_success'));
                } else {
                    $this->Common_model->updateInformation($ia_info, $id, "tbl_inventory_adjustment");
                    $this->Common_model->deletingMultipleFormData('inventory_adjustment_id', $id, 'tbl_inventory_adjustment_ingredients');
                    $this->saveInventoryAdjustmentIngredients($_POST['ingredient_id'], $id, 'tbl_inventory_adjustment_ingredients');
                    $this->session->set_flashdata('exception',  lang('update_success'));
                }

                redirect('Inventory_adjustment/inventoryAdjustments');
            } else {
                if ($id == "") {
                    $data = array();
                    $data['reference_no'] = $this->Inventory_adjustment_model->generateReferenceNo($outlet_id);
                    $data['ingredients'] = $this->Inventory_adjustment_model->getIngredientList();
                    $data['employees'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, "tbl_users");
                    $data['main_content'] = $this->load->view('inventoryAdjustment/addInventoryAdjustment', $data, TRUE);
                    $this->load->view('userHome', $data);
                } else {
                    $data = array();
                    $data['encrypted_id'] = $encrypted_id;
                    $data['ingredients'] = $this->Inventory_adjustment_model->getIngredientList();
                    $data['employees'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, "tbl_users");
                    $data['inventory_adjustment_details'] = $this->Common_model->getDataById($id, "tbl_inventory_adjustment");
                    $data['inventory_adjustment_ingredients'] = $this->Inventory_adjustment_model->getInventoryAdjustmentIngredients($id);
                    $data['main_content'] = $this->load->view('inventoryAdjustment/editInventoryAdjustment', $data, TRUE);
                    $this->load->view('userHome', $data);
                }
            }
        } else {
            if ($id == "") {
                $data = array();
                $data['reference_no'] = $this->Inventory_adjustment_model->generateReferenceNo($outlet_id);
                $data['ingredients'] = $this->Inventory_adjustment_model->getIngredientList();
                $data['employees'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, "tbl_users");
                $data['main_content'] = $this->load->view('inventoryAdjustment/addInventoryAdjustment', $data, TRUE);
                $this->load->view('userHome', $data);
            } else {
                $data = array();
                $data['encrypted_id'] = $encrypted_id;
                $data['ingredients'] = $this->Inventory_adjustment_model->getIngredientList();
                $data['employees'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, "tbl_users");
                $data['inventory_adjustment_details'] = $this->Common_model->getDataById($id, "tbl_inventory_adjustment");
                $data['inventory_adjustment_ingredients'] = $this->Inventory_adjustment_model->getInventoryAdjustmentIngredients($id);
                $data['main_content'] = $this->load->view('inventoryAdjustment/editInventoryAdjustment', $data, TRUE);
                $this->load->view('userHome', $data);
            }
        }
    }
     /**
     * save Inventory Adjustment Ingredients
     * @access public
     * @return void
     * @param string
     * @param int
     * @param string
     */
    public function saveInventoryAdjustmentIngredients($inventory_adjustment_ingredients, $inventory_adjustment_id, $table_name) {
        foreach ($inventory_adjustment_ingredients as $row => $ingredient_id):
            $fmi = array();
            $fmi['ingredient_id'] = $ingredient_id;
            $fmi['consumption_amount'] = $_POST['consumption_amount'][$row];
            $fmi['consumption_status'] = $_POST['consumption_status'][$row];
            $fmi['inventory_adjustment_id'] = $inventory_adjustment_id;
            $fmi['outlet_id'] = $this->session->userdata('outlet_id');
            $this->Common_model->insertInformation($fmi, "tbl_inventory_adjustment_ingredients");
        endforeach;
    }
     /**
     * inventory Adjustment Details
     * @access public
     * @return void
     * @param int
     */
    public function inventoryAdjustmentDetails($id) {
        $encrypted_id = $id;
        $id = $this->custom->encrypt_decrypt($id, 'decrypt');
        $data = array();
        $data['encrypted_id'] = $encrypted_id;
        $data['inventory_adjustment_details'] = $this->Common_model->getDataById($id, "tbl_inventory_adjustment");
        $data['inventory_adjustment_ingredients'] = $this->Inventory_adjustment_model->getInventoryAdjustmentIngredients($id);
        $data['main_content'] = $this->load->view('inventoryAdjustment/inventoryAdjustmentDetails', $data, TRUE);
        $this->load->view('userHome', $data);
    }

    public function ajuste($id = null) {
        $this->load->model('Inventory_adjustment_model');
        $outlet_id = $this->session->userdata('outlet_id');
        $company_id = $this->session->userdata('company_id');
        $user_id = $this->session->userdata('user_id');
        $user_txt = $this->session->userdata('full_name');
    
        if ($id) {
            $ajuste = $this->Common_model->getDataById($id, "tbl_inventory_adjustment");
            $ajuste_items = $this->Inventory_adjustment_model->getInventoryAdjustmentIngredients($id);
        } else {
            $ajuste = null;
            $ajuste_items = [];
        }
    
        $data = [
            'ajuste' => $ajuste,
            'ajuste_items' => $ajuste_items,
            'reference_no' => isset($ajuste->reference_no) ? $ajuste->reference_no : $this->Inventory_adjustment_model->generateReferenceNo($outlet_id),
            'date' => isset($ajuste->date) ? $ajuste->date : date('Y-m-d'),
            'note' => isset($ajuste->note) ? $ajuste->note : '',
            'outlet_id' => $outlet_id,
            'user_id' => $user_id,
            'user_txt' => $user_txt,
        ];
        $data['main_content'] = $this->load->view('inventoryAdjustment/ajusteInventario', $data, TRUE);
        $this->load->view('userHome', $data);
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
    
    /**
     * Ajax: Guardar ajuste dinámico
     */
    public function ajaxGuardarAjusteDinamico() {
        $ajuste_id = $this->input->post('ajuste_id', true);
    
        // Si $ajuste_id es null, creamos el ajuste
        if (!$ajuste_id) {
            $data = [
                'reference_no' => $this->input->post('reference_no'),
                'date' => $this->input->post('date'),
                'note' => $this->input->post('note'),
                'user_id' => $this->session->userdata('user_id'),
                'outlet_id' => $this->session->userdata('outlet_id'),
                'employee_id' => $this->session->userdata('user_id'),
                'del_status' => 'Live'
            ];
            $ajuste_id = $this->Common_model->insertInformation($data, "tbl_inventory_adjustment");
        }
    
        // Guardar el detalle
        $detalle = [
            'ingredient_id'      => $this->input->post('ingredient_id'),
            'consumption_amount' => abs($this->input->post('qty_dif')),
            'inventory_adjustment_id' => $ajuste_id,
            'consumption_status' => ($this->input->post('qty_dif') >= 0) ? 'Plus' : 'Minus',
            'outlet_id'          => $this->session->userdata('outlet_id'),
            'del_status'         => 'Live',
            'codigo'             => $this->input->post('codigo'),
            'qty_old'            => $this->input->post('qty_old'),
            'qty_new'            => $this->input->post('qty_new'),
            'qty_dif'            => $this->input->post('qty_dif'),
            'costo'              => $this->input->post('costo'),
            'costo_dif'          => $this->input->post('costo_dif'),
            'user'               => $this->session->userdata('user_id'),
            'user_txt'           => $this->session->userdata('full_name'),
            'datetime'           => date('Y-m-d H:i:s')
        ];
        $this->Common_model->insertInformation($detalle, "tbl_inventory_adjustment_ingredients");
    
        echo json_encode([
            'success' => true,
            'ajuste_id' => $ajuste_id
        ]);
    }
    
    /**
     * Ajax: Listar detalles del ajuste (para refrescar la tabla)
     */
    public function ajaxListarAjusteDetalles() {
        $ajuste_id = $this->input->post('ajuste_id');
        $items = $this->Inventory_adjustment_model->getInventoryAdjustmentIngredients($ajuste_id);
        echo json_encode(['success' => true, 'items' => $items]);
    }

}
