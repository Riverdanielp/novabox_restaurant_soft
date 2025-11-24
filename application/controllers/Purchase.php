<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Purchase extends Cl_Controller {

    public function __construct() {
        parent::__construct();
         

        $this->load->model('Authentication_model');
        $this->load->model('Purchase_model');
        $this->load->model('Master_model');
        $this->load->model('Common_model');
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
        $controller = "106";
        $function = "";

        if($segment_2=="purchases" || $segment_2=="barcode"){
            $function = "view";
        }elseif(($segment_2=="addEditPurchase" || $segment_2=="getSupplierList" || $segment_2=="addNewSupplierByAjax") && $segment_3){
            $function = "update";
        }elseif($segment_2=="purchaseDetails" && $segment_3){
            $function = "view_details";
        }elseif($segment_2=="addEditPurchase" || $segment_2=="getSupplierList" || $segment_2=="addNewSupplierByAjax" || 
                $segment_2=="ajax_save_ingredient_and_product" 
                || $segment_2=="ajaxCrearCompraYAgregarItem" 
                || $segment_2=="ajaxAgregarItemCompra" 
                || $segment_2=="ajaxEditarItemCompra" 
                || $segment_2=="ajaxGuardarDatosCompra"
                || $segment_2=="ajaxCheckFacturaNro"
                 ){
            $function = "add";
        }elseif($segment_2=="deletePurchase" || $segment_2=="ajaxEliminarItemCompra"){
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
     * purchases info
     * @access public
     * @return void
     * @param no
     */
    public function purchases() {
        $outlet_id = $this->session->userdata('outlet_id');
        $data = array();
        $data['purchases'] = $this->Common_model->getAllByOutletId($outlet_id, "tbl_purchase");
        $data['main_content'] = $this->load->view('purchase/purchases', $data, TRUE);
        $this->load->view('userHome', $data);
    }
     /**
     * delete Purchase
     * @access public
     * @return void
     * @param int
     */
    public function barcode($id='') {
        if (htmlspecialcharscustom($this->input->post('submit'))) {
            $food_menu_id = $this->input->post($this->security->xss_clean('food_menu_id'));
            $qty = $this->input->post($this->security->xss_clean('qty'));
            $expire_date = $this->input->post($this->security->xss_clean('expire_date'));
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
                        'qty' => $qty[$i],
                        'expire_date' => $value[3]
                    );
                }
            }
            $data = array();
            $data['id'] = $id;
            $data['items'] = $arr;
            $data['barcode_width'] = $barcode_width;
            $data['barcode_height'] = $barcode_height;
            $data['main_content'] = $this->load->view('purchase/barcode_preview', $data, TRUE);
            $this->load->view('userHome', $data);
        } else {
            $data = array();
            $data['id'] = $id;
            $data['purchase_ingredients'] = $this->Purchase_model->getPurchaseIngredients($id);
            $data['main_content'] = $this->load->view('purchase/BarcodeGenerator', $data, TRUE);
            $this->load->view('userHome', $data);
        }

    }
    public function deletePurchase($id) {
        $id = $this->custom->encrypt_decrypt($id, 'decrypt');

        // Obtener datos de la compra antes de eliminarla para revertir movimiento contable
        $purchase = $this->Common_model->getDataById($id, "tbl_purchase");

        // Revertir movimiento contable si existía
        if ($purchase && !empty($purchase->account_id) && floatval($purchase->paid) > 0) {
            $this->load->model('Account_model');
            $this->load->model('Account_transaction_model');

            $account = $this->Account_model->getAccountById($purchase->account_id);
            if ($account) {
                // Devolver el dinero a la cuenta (revertir la compra)
                $new_balance = floatval($account->current_balance) + floatval($purchase->paid);
                $this->Account_model->updateBalance($purchase->account_id, $new_balance);

                // Obtener nombre del proveedor para descripción
                $supplier = $this->Common_model->getDataById($purchase->supplier_id, 'tbl_suppliers');
                $supplier_name = $supplier ? $supplier->name : 'Proveedor';

                // Registrar transacción de reversión
                $transaction_data = [
                    'to_account_id' => $purchase->account_id, // Reversión de compra = entrada de dinero
                    'transaction_type' => 'Deposito',
                    'amount' => floatval($purchase->paid),
                    'reference_type' => 'purchase_reversal',
                    'reference_id' => $id,
                    'note' => 'Reversión de compra #' . $purchase->reference_no . ' (eliminada)',
                    'transaction_date' => date('Y-m-d H:i:s'),
                    'user_id' => $this->session->userdata('user_id'),
                    'company_id' => $this->session->userdata('company_id')
                ];
                $this->Account_transaction_model->insertTransaction($transaction_data);
            }
        }

        $this->Common_model->deleteStatusChangeWithChild($id, $id, "tbl_purchase", "tbl_purchase_ingredients", 'id', 'purchase_id');
        $this->session->set_flashdata('exception', lang('delete_success'));
        redirect('Purchase/purchases');
    }
     /**
     * add/Edit Purchase
     * @access public
     * @return void
     * @param int
     */
    public function addEditPurchase($encrypted_id = "") {

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

        $id = $this->custom->encrypt_decrypt($encrypted_id, 'decrypt');
        $outlet_id = $this->session->userdata('outlet_id');
        $company_id = $this->session->userdata('company_id');

        $purchase_info = array();

        if ($id == "") {
            $purchase_info['reference_no'] = $this->Purchase_model->generatePurRefNo($outlet_id);
        } else {
            $info_purchase = $this->Common_model->getDataById($id, "tbl_purchase");
            if ($info_purchase){
                $purchase_info['reference_no'] = $info_purchase->reference_no;
            } else {
                $this->session->set_flashdata('exception_3', 'No se ha encontrado el registro de compra!');
                redirect('Purchase/purchases');
            }
        }

        if (htmlspecialcharscustom($this->input->post('submit'))) {

            $this->form_validation->set_rules('reference_no', lang('ref_no'), 'required|max_length[50]');
            $this->form_validation->set_rules('supplier_id', lang('supplier'), 'required|max_length[50]');
            $this->form_validation->set_rules('date', lang('date'), 'required|max_length[50]');
            $this->form_validation->set_rules('note', lang('note'), 'max_length[200]');
            $this->form_validation->set_rules('paid', lang('paid_amount'), 'required|numeric|max_length[50]');
            $this->form_validation->set_rules('payment_id', lang('payment_method'), 'required|numeric|max_length[50]');
           

            if ($this->form_validation->run() == TRUE) {
                $purchase_info['reference_no'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('reference_no')));
                $purchase_info['supplier_id'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('supplier_id')));
                $purchase_info['date'] = date('Y-m-d', strtotime($this->input->post($this->security->xss_clean('date'))));
                $purchase_info['note'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('note')));
                $purchase_info['grand_total'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('grand_total')));
                $purchase_info['paid'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('paid')));
                $purchase_info['due'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('due')));
                $purchase_info['payment_id'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('payment_id')));
                $purchase_info['account_id'] = !empty($this->input->post('account_id')) ? $this->input->post('account_id') : NULL;
                $purchase_info['counter_id'] = $this->session->userdata('counter_id');
                $purchase_info['user_id'] = $this->session->userdata('user_id');
                $purchase_info['outlet_id'] = $this->session->userdata('outlet_id');
                
                // Cargar modelos necesarios para registro contable
                $this->load->model('Account_model');
                $this->load->model('Account_transaction_model');
                
                if ($id == "") {
                    // NUEVA COMPRA
                    $purchase_info['added_date_time'] = date('Y-m-d H:i:s');
                    $purchase_id = $this->Common_model->insertInformation($purchase_info, "tbl_purchase");
                    $this->savePurchaseIngredients($_POST['ingredient_id'], $purchase_id, 'tbl_purchase_ingredients');
                    
                    // Registrar movimiento contable si se especificó cuenta y hay monto pagado
                    if (!empty($purchase_info['account_id']) && floatval($purchase_info['paid']) > 0) {
                        $account = $this->Account_model->getAccountById($purchase_info['account_id']);
                        if ($account) {
                            $balance_before = floatval($account->current_balance);
                            $amount = floatval($purchase_info['paid']);
                            $balance_after = $balance_before - $amount; // Las compras RESTAN del saldo
                            
                            // Actualizar saldo de la cuenta
                            $this->Account_model->updateBalance($purchase_info['account_id'], $balance_after);
                            
                            // Crear registro de transacción
                            $transaction_data = [
                                'from_account_id' => $purchase_info['account_id'], // Compra = salida de dinero
                                'transaction_type' => 'Compra',
                                'amount' => $amount,
                                'reference_type' => 'purchase',
                                'reference_id' => $purchase_id,
                                'note' => 'Compra #' . $purchase_info['reference_no'],
                                'transaction_date' => date('Y-m-d H:i:s'),
                                'user_id' => $this->session->userdata('user_id'),
                                'company_id' => $this->session->userdata('company_id')
                            ];
                            $this->Account_transaction_model->insertTransaction($transaction_data);
                        }
                    }
                    
                    $this->session->set_flashdata('exception', lang('insertion_success'));
                } else {
                    // EDITAR COMPRA EXISTENTE
                    // Obtener datos antiguos de la compra
                    $old_purchase = $this->Common_model->getDataById($id, "tbl_purchase");

                    // Verificar si la cuenta o el monto cambió
                    $account_changed = ($old_purchase->account_id != $purchase_info['account_id']);
                    $amount_changed = (floatval($old_purchase->paid) != floatval($purchase_info['paid']));

                    // Solo procesar movimientos contables si algo cambió
                    if ($account_changed || $amount_changed) {
                        // Si había un account_id anterior y monto pagado, revertir la transacción
                        if ($old_purchase && !empty($old_purchase->account_id) && floatval($old_purchase->paid) > 0) {
                            $old_account = $this->Account_model->getAccountById($old_purchase->account_id);
                            if ($old_account) {
                                $balance_before_reversal = floatval($old_account->current_balance);
                                $old_amount = floatval($old_purchase->paid);
                                $balance_after_reversal = $balance_before_reversal + $old_amount; // DEVOLVER el dinero

                                $this->Account_model->updateBalance($old_purchase->account_id, $balance_after_reversal);

                                // Registrar transacción de reversión
                                $reversal_data = [
                                    'to_account_id' => $old_purchase->account_id, // Reversión de compra = entrada de dinero
                                    'transaction_type' => 'Deposito',
                                    'amount' => $old_amount,
                                    'reference_type' => 'purchase',
                                    'reference_id' => $id,
                                    'note' => 'Reversión de compra #' . $old_purchase->reference_no . ' (editada)',
                                    'transaction_date' => date('Y-m-d H:i:s'),
                                    'user_id' => $this->session->userdata('user_id'),
                                    'company_id' => $this->session->userdata('company_id')
                                ];
                                $this->Account_transaction_model->insertTransaction($reversal_data);
                            }
                        }

                        // Aplicar nueva transacción si hay cuenta y monto
                        if (!empty($purchase_info['account_id']) && floatval($purchase_info['paid']) > 0) {
                            $new_account = $this->Account_model->getAccountById($purchase_info['account_id']);
                            if ($new_account) {
                                $balance_before_new = floatval($new_account->current_balance);
                                $new_amount = floatval($purchase_info['paid']);
                                $balance_after_new = $balance_before_new - $new_amount; // Las compras RESTAN

                                $this->Account_model->updateBalance($purchase_info['account_id'], $balance_after_new);

                                $new_transaction_data = [
                                    'from_account_id' => $purchase_info['account_id'], // Compra = salida de dinero
                                    'transaction_type' => 'Compra',
                                    'amount' => $new_amount,
                                    'reference_type' => 'purchase',
                                    'reference_id' => $id,
                                    'note' => 'Compra #' . $purchase_info['reference_no'],
                                    'transaction_date' => date('Y-m-d H:i:s'),
                                    'user_id' => $this->session->userdata('user_id'),
                                    'company_id' => $this->session->userdata('company_id')
                                ];
                                $this->Account_transaction_model->insertTransaction($new_transaction_data);
                            }
                        }
                    }
                    
                    $this->session->set_flashdata('exception',lang('update_success'));
                }

                redirect('Purchase/purchases');
            } else {
                if ($id == "") {
                    $data = array();
                    $data['payment_methods'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, "tbl_payment_methods");
                    $data['pur_ref_no'] = $this->Purchase_model->generatePurRefNo($outlet_id);
                    $data['suppliers'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, 'tbl_suppliers');
                    $data['ingredients'] = $this->Purchase_model->getIngredientListWithUnitAndPrice($company_id);
                    $data['categories'] = $this->Common_model->getAllByCompanyId($company_id, 'tbl_food_menu_categories');
                    $data['ing_categories'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, 'tbl_ingredient_categories');
                    $this->load->model('Account_model');
                    $data['accounts'] = $this->Account_model->getAllAccountsByCompany($company_id);
                    $data['main_content'] = $this->load->view('purchase/addEditPurchase', $data, TRUE);
                    $this->load->view('userHome', $data);
                } else {
                    $data = array();
                    $data['payment_methods'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, "tbl_payment_methods");
                    $data['encrypted_id'] = $encrypted_id;
                    $data['purchase_details'] = $this->Common_model->getDataById($id, "tbl_purchase");
                    $data['suppliers'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, 'tbl_suppliers');
                    $data['ingredients'] = $this->Purchase_model->getIngredientListWithUnitAndPrice($company_id);
                    $data['purchase_ingredients'] = $this->Purchase_model->getPurchaseIngredients($id);
                    $data['categories'] = $this->Common_model->getAllByCompanyId($company_id, 'tbl_food_menu_categories');
                    $data['ing_categories'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, 'tbl_ingredient_categories');
                    $this->load->model('Account_model');
                    $data['accounts'] = $this->Account_model->getAllAccountsByCompany($company_id);
                    $data['main_content'] = $this->load->view('purchase/addEditPurchase', $data, TRUE);
                    $this->load->view('userHome', $data);
                }
            }
        } else {
            if ($id == "") {
                $data = array();
                $data['payment_methods'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, "tbl_payment_methods");
                $data['pur_ref_no'] = $this->Purchase_model->generatePurRefNo($outlet_id);
                $data['suppliers'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, 'tbl_suppliers');
                $data['ingredients'] = $this->Purchase_model->getIngredientListWithUnitAndPrice($company_id);
                $data['categories'] = $this->Common_model->getAllByCompanyId($company_id, 'tbl_food_menu_categories');
                $data['ing_categories'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, 'tbl_ingredient_categories');
                $data['detalles_factura'] = [];
                $this->load->model('Account_model');
                $data['accounts'] = $this->Account_model->getAllAccountsByCompany($company_id);
                $data['main_content'] = $this->load->view('purchase/addEditPurchase', $data, TRUE);
                $this->load->view('userHome', $data);
            } else {
                $data = array();
                $data['encrypted_id'] = $encrypted_id;
                $data['detalles_factura'] = (tipoFacturacion() == 'RD_AI') ? verificar_compra($id) : [];
                $data['payment_methods'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, "tbl_payment_methods");
                $data['purchase_details'] = $this->Common_model->getDataById($id, "tbl_purchase");
                $data['suppliers'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, 'tbl_suppliers');
                $data['ingredients'] = $this->Purchase_model->getIngredientListWithUnitAndPrice($company_id);
                $data['purchase_ingredients'] = $this->Purchase_model->getPurchaseIngredients($id);
                $data['categories'] = $this->Common_model->getAllByCompanyId($company_id, 'tbl_food_menu_categories');
                $data['ing_categories'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, 'tbl_ingredient_categories');
                $this->load->model('Account_model');
                $data['accounts'] = $this->Account_model->getAllAccountsByCompany($company_id);
                $data['main_content'] = $this->load->view('purchase/addEditPurchase', $data, TRUE);
                $this->load->view('userHome', $data);
            }
        }
    }
     /**
     * save Purchase Ingredients
     * @access public
     * @return void
     * @param string
     * @param int
     * @param string
     */
    public function savePurchaseIngredients($purchase_ingredients, $purchase_id, $table_name) {
        $unit_prices = $this->input->post('unit_price');
        $quantities = $this->input->post('quantity_amount');
        $sale_prices = $this->input->post('sale_price');
        $iva_tipos = $this->input->post('iva_tipo');
        //This variable could not be escaped because this is array content
        foreach ($purchase_ingredients as $row => $ingredient_id):
            $ingredient = getIngredient($_POST['ingredient_id'][$row]);
            $conversion_rate = isset($ingredient->conversion_rate) && $ingredient->conversion_rate?$ingredient->conversion_rate:1;
            $inline_cost = ($_POST['unit_price'][$row]/$conversion_rate);
            $fmi = array();
            $fmi['ingredient_id'] = $_POST['ingredient_id'][$row];
            $fmi['unit_price'] = $_POST['unit_price'][$row];
            $fmi['quantity_amount'] = $_POST['quantity_amount'][$row];
            $fmi['total'] = $_POST['total'][$row];
            $fmi['cost_per_unit'] = getAmtP($inline_cost);
            $fmi['purchase_id'] = $purchase_id;
            $fmi['outlet_id'] = $this->session->userdata('outlet_id');
            $this->Common_model->insertInformation($fmi, "tbl_purchase_ingredients");
                    // --- ACTUALIZAR INGREDIENTE ---
            $ingredient_update = array(
                'purchase_price' => $unit_prices[$row],
                'iva_tipo' => $iva_tipos[$row]
            );
            $this->Common_model->updateInformation($ingredient_update, $ingredient_id, 'tbl_ingredients');

            // --- SI TIENE food_id, actualizar food_menu ---
            $ingredient = $this->Common_model->getDataById($ingredient_id, 'tbl_ingredients');
            if ($ingredient && $ingredient->food_id) {
                $food_menu_update = array(
                    'sale_price' => $sale_prices[$row],
                    'sale_price_take_away' => $sale_prices[$row],
                    'sale_price_delivery' => $sale_prices[$row],
                    'iva_tipo' => $iva_tipos[$row],
                    'purchase_price' => $unit_prices[$row]
                );
                $this->Common_model->updateInformation($food_menu_update, $ingredient->food_id, 'tbl_food_menus');
            }
            //set average cost for profit loss report
            setAverageCost($_POST['ingredient_id'][$row]);
            //update ingredenits purchase amount
            $data = array();
            $data['consumption_unit_cost'] = $inline_cost;
            $data['purchase_price'] = $fmi['unit_price'];
            $this->db->where('id', $fmi['ingredient_id']);
            $this->db->update("tbl_ingredients", $data);
            
            updatedFoodCost($_POST['ingredient_id'][$row]);
        endforeach;
    }
     /**
     * purchase Details
     * @access public
     * @return void
     * @param int
     */
    public function purchaseDetails($id) {
        $encrypted_id = $id;
        $id = $this->custom->encrypt_decrypt($id, 'decrypt');

        $data = array();
        $data['encrypted_id'] = $encrypted_id;
        $data['purchase_details'] = $this->Common_model->getDataById($id, "tbl_purchase");
        $data['purchase_ingredients'] = $this->Purchase_model->getPurchaseIngredients($id);
        $data['main_content'] = $this->load->view('purchase/purchaseDetails', $data, TRUE);
        $this->load->view('userHome', $data);
    }
     /**
     * add New Supplier By Ajax
     * @access public
     * @return object
     * @param no
     */
    public function addNewSupplierByAjax() {
        $data['name'] = $_GET['name'];
        $data['doc_num'] = $_GET['doc_num'];
        $data['contact_person'] = $_GET['contact_person'];
        $data['phone'] = $_GET['phone'];
        $data['email'] = $_GET['emailAddress'];
        $data['address'] = $_GET['supAddress'];
        $data['description'] = $_GET['description'];
        $data['user_id'] = $this->session->userdata('user_id');
        $data['company_id'] = $this->session->userdata('company_id');
        $this->db->insert('tbl_suppliers', $data);
        $supplier_id = $this->db->insert_id();
        $data1 = array('supplier_id' => $supplier_id);
        echo json_encode($data1);
    }
     /**
     * get Supplier List
     * @access public
     * @return void
     * @param no
     */
    public function getSupplierList() {
        $company_id = $this->session->userdata('company_id');
        $data1 = $this->db->query("SELECT * FROM tbl_suppliers 
              WHERE company_id=$company_id")->result();
        //generate html content for view
        echo '<option value="">Select</option>';
        foreach ($data1 as $value) {
            echo '<option value="' . $value->id . '" >' . $value->name . '</option>';
        }
        exit;
    }

    public function ajax_save_ingredient_and_product() {
        $company_id = $this->session->userdata('company_id');
        $user_id = $this->session->userdata('user_id');
        $data = $this->input->post();
    
        // Guarda o actualiza ingrediente (usa setIngredients)
        $ingredient_data = [
            'name' => $data['name'],
            'code' => $data['code'],
            'category_id' => $data['category_id'],
            'purchase_price' => $data['purchase_price'],
            'alert_quantity' => $data['alert_quantity'],
            'unit_id' => 5, // o lo que corresponda
            'purchase_unit_id' => 5,
            'consumption_unit_cost' => $data['purchase_price'],
            'conversion_rate' => 1,
            'is_direct_food' => isset($data['product_for_sale']) ? 2 : 1,
            'user_id' => $user_id,
            'company_id' => $company_id,
            'del_status' => 'Live',
            'sale_price' => $data['sale_price'],
        ];
    
        $food_id = null;
    
        if (isset($data['product_for_sale'])) {
            // Crear o actualizar food_menu y conectar
            $food_menu_info = [
                'name' => $data['name'],
                'code' => $data['code'],
                'category_id' => $data['food_menu_category_id'],
                'sale_price' => $data['sale_price'],
                'sale_price_take_away' => $data['sale_price_take_away'],
                'sale_price_delivery' => $data['sale_price_delivery'],
                'product_type' => 3,
                'purchase_price' => $data['purchase_price'],
                'alert_quantity' => $data['alert_quantity'],
                'description' => '', // Opcional
                'user_id' => $user_id,
                'company_id' => $company_id,
                'del_status' => 'Live'
            ];
            // Verifica si existe food_menu usando code o name
            $this->db->where('code', $data['code']);
            $this->db->where('company_id', $company_id);
            $food_menu = $this->db->get('tbl_food_menus')->row();
            if ($food_menu) {
                $food_id = $food_menu->id;
                $this->Common_model->updateInformation($food_menu_info, $food_id, 'tbl_food_menus');
            } else {
                $food_id = $this->Common_model->insertInformation($food_menu_info, 'tbl_food_menus');
            }
            $ingredient_data['food_id'] = $food_id;
            $ingredient_data['is_direct_food'] = 2;
            if(isLMni()):
                $json_data = array();
                updatePrice($this->session->userdata('company_id'),$food_id,$food_menu_info['sale_price'],$food_menu_info['sale_price_take_away'],json_encode($json_data),$food_menu_info['sale_price_delivery']);
            endif;
        }
        // Guardar ingrediente (crea o actualiza)
        $ingredient_id = setIngredients($food_id, $ingredient_data);
    
        // Responde con el id del ingrediente y mensaje de éxito// Al final de ajax_save_ingredient_and_product
        echo json_encode([
            'success' => true,
            'ingredient' => [
                'id' => $ingredient_id,
                'name' => $ingredient_data['name'],
                'code' => $ingredient_data['code'],
                'unit_name' => 'UNI', // O lo que corresponda
                'purchase_price' => $ingredient_data['purchase_price'],
                'sale_price' => $data['sale_price'],
                'iva_tipo' => $ingredient_data['iva_tipo'] ?? '10'
            ]
        ]);
    }

    public function ajaxCrearCompraYAgregarItem() {
        $data = $this->input->post();
        $outlet_id = $this->session->userdata('outlet_id');

        // Crear compra
        $purchase_info = [
            'reference_no' => $data['reference_no'],
            // 'factura_nro' => $data['factura_nro'],
            'supplier_id' => $data['supplier_id'],
            'date' => $data['date'],
            'paid' => $data['paid'],
            'payment_id' => $data['payment_id'],
            'account_id' => !empty($data['account_id']) ? $data['account_id'] : NULL,
            'user_id' => $this->session->userdata('user_id'),
            'outlet_id' => $outlet_id
        ];
        if (tipoFacturacion() == 'RD_AI'){
            $prefijo = htmlspecialchars($this->input->post($this->security->xss_clean('prefijo')));
            $ncf = htmlspecialchars($this->input->post($this->security->xss_clean('ncf')));
            $purchase_info['factura_nro'] = $ncf;
            $purchase_info['itbis'] = $this->input->post('itbis');
            $purchase_id = $this->Common_model->insertInformation($purchase_info, "tbl_purchase");

            $factura_info = array();
            $factura_info['id_proveedor'] =htmlspecialchars($this->input->post($this->security->xss_clean('supplier_id')));
            $factura_info['fecha_comprobante'] =htmlspecialchars($this->input->post($this->security->xss_clean('date')));
            $factura_info['ncf'] =htmlspecialchars($this->input->post($this->security->xss_clean('ncf')));
            $factura_info['numeracion_tipo'] =htmlspecialchars($this->input->post($this->security->xss_clean('tipo_numeracion')));
            $factura_info['fecha_venc'] =htmlspecialchars($this->input->post($this->security->xss_clean('fecha_venc')));
            $factura_info['tipo_cyg'] =htmlspecialchars($this->input->post($this->security->xss_clean('tipo_cyg')));
            $factura_info['tipo_pago'] =htmlspecialchars($this->input->post($this->security->xss_clean('tipo_pago')));
            facturar_compra($purchase_id,$factura_info);
        } else {
            $purchase_info['factura_nro'] = $this->input->post('factura_nro');
            $purchase_id = $this->Common_model->insertInformation($purchase_info, "tbl_purchase");
        }

        // Guardar primer item
        $item = $data['item'];
        $item_data = [
            'ingredient_id' => $item['ingredient_id'],
            'unit_price' => $item['unit_price'],
            'quantity_amount' => $item['quantity_amount'],
            // 'sale_price' => $item['sale_price'],
            'iva_tipo' => $item['iva_tipo'],
            'purchase_id' => $purchase_id,
            'outlet_id' => $outlet_id,
            'total' => floatval($item['unit_price']) * floatval($item['quantity_amount'])
        ];
        $item_id = $this->Common_model->insertInformation($item_data, "tbl_purchase_ingredients");

        $ingrediente = getIngredient($item['ingredient_id']);
        echo json_encode([
            'purchase_id' => $purchase_id,
            'item' => [
                'id' => $item_id,
                'ingredient_id' => $item['ingredient_id'],
                'name' => $ingrediente->name,
                'unit_price' => $item['unit_price'],
                'quantity_amount' => $item['quantity_amount'],
                'sale_price' => $item['sale_price'],
                'iva_tipo' => $item['iva_tipo'],
                'total' => $item_data['total'],
                'sn' => 1
            ]
        ]);
    }

    // Agregar ítem a compra existente
    public function ajaxAgregarItemCompra() {
        $data = $this->input->post();
        $item = $data['item'];
        $purchase_id = $data['purchase_id'];
        $item_data = [
            'ingredient_id' => $item['ingredient_id'],
            'unit_price' => $item['unit_price'],
            'quantity_amount' => $item['quantity_amount'],
            // 'sale_price' => $item['sale_price'],
            'iva_tipo' => $item['iva_tipo'],
            'purchase_id' => $purchase_id,
            'outlet_id' => $this->session->userdata('outlet_id'),
            'total' => floatval($item['unit_price']) * floatval($item['quantity_amount'])
        ];
        $item_id = $this->Common_model->insertInformation($item_data, "tbl_purchase_ingredients");
        $ingrediente = getIngredient($item['ingredient_id']);
        // Puedes calcular el número de fila buscando los existentes
        echo json_encode([
            'item' => [
                'id' => $item_id,
                'ingredient_id' => $item['ingredient_id'],
                'name' => $ingrediente->name,
                'unit_price' => $item['unit_price'],
                'quantity_amount' => $item['quantity_amount'],
                'sale_price' => $item['sale_price'],
                'iva_tipo' => $item['iva_tipo'],
                'total' => $item_data['total'],
                'sn' => 1 // Calcula si lo deseas
            ]
        ]);
    }

    // Eliminar ítem
    public function ajaxEliminarItemCompra() {
        $item_id = $this->input->post('purchase_item_id');
        $this->Common_model->deleteStatusChangeWithChild($item_id, $item_id, "tbl_purchase_ingredients", "", 'id', '');
        echo json_encode(['success' => true]);
    }


    // Editar ítem
    public function ajaxEditarItemCompra() {
        $item_id = $this->input->post('purchase_item_id');
        $unit_price = $this->input->post('unit_price');
        $quantity_amount = $this->input->post('quantity_amount');
        $sale_price = $this->input->post('sale_price');
        $iva_tipo = $this->input->post('iva_tipo');

        // 1. Actualizar tbl_purchase_ingredients
        $data = [
            'unit_price' => $unit_price,
            'quantity_amount' => $quantity_amount,
            // 'sale_price' => $sale_price,
            'iva_tipo' => $iva_tipo,
            'total' => floatval($unit_price) * floatval($quantity_amount)
        ];
        $this->Common_model->updateInformation($data, $item_id, "tbl_purchase_ingredients");

        // 2. Obtener el ingrediente_id de este purchase_ingredient
        $item_row = $this->Common_model->getDataById($item_id, 'tbl_purchase_ingredients');
        $ingredient_id = $item_row ? $item_row->ingredient_id : null;
        if ($ingredient_id) {
            // 2.1 Actualizar tbl_ingredients
            $ingredient_update = [
                'sale_price' => $sale_price,
                'purchase_price' => $unit_price,
                'iva_tipo' => $iva_tipo,
            ];
            $this->Common_model->updateInformation($ingredient_update, $ingredient_id, 'tbl_ingredients');

            // --- SI TIENE food_id, actualizar food_menu ---
            $ingredient = $this->Common_model->getDataById($ingredient_id, 'tbl_ingredients');
            if ($ingredient && $ingredient->food_id) {
                $food_menu_update = [
                    'sale_price' => $sale_price,
                    'sale_price_take_away' => $sale_price,
                    'sale_price_delivery' => $sale_price,
                    'iva_tipo' => $iva_tipo,
                    'purchase_price' => $unit_price
                ];
                $this->Common_model->updateInformation($food_menu_update, $ingredient->food_id, 'tbl_food_menus');
            }

            // set average cost for profit loss report
            setAverageCost($ingredient_id);
            // update ingredenits purchase amount
            $conversion_rate = isset($ingredient->conversion_rate) && $ingredient->conversion_rate ? $ingredient->conversion_rate : 1;
            $inline_cost = ($unit_price / $conversion_rate);
            $data2 = [
                'consumption_unit_cost' => $inline_cost,
                'purchase_price' => $unit_price
            ];
            $this->db->where('id', $ingredient_id);
            $this->db->update("tbl_ingredients", $data2);

            updatedFoodCost($ingredient_id);
        }

        echo json_encode(['success' => true]);
    }

    public function ajaxGuardarDatosCompra() {
        $purchase_id = $this->input->post('purchase_id');
        
        // Cargar modelos necesarios para registro contable
        $this->load->model('Account_model');
        $this->load->model('Account_transaction_model');
        
        // Obtener datos antiguos de la compra antes de actualizar
        $old_purchase = $this->Common_model->getDataById($purchase_id, "tbl_purchase");
        
        $data = [
            'reference_no' => $this->input->post('reference_no'),
            'supplier_id' => $this->input->post('supplier_id'),
            // 'factura_nro' => $this->input->post('factura_nro'),
            'date' => $this->input->post('date'),
            'paid' => $this->input->post('paid'),
            'payment_id' => $this->input->post('payment_id'),
            'account_id' => !empty($this->input->post('account_id')) ? $this->input->post('account_id') : NULL,
            'grand_total' => $this->input->post('grand_total'),
            'due' => $this->input->post('due'),
        ];
        if (tipoFacturacion() == 'RD_AI'){
            $factura_info = array();
            $factura_info['id_proveedor'] =htmlspecialchars($this->input->post($this->security->xss_clean('supplier_id')));
            $factura_info['fecha_comprobante'] =htmlspecialchars($this->input->post($this->security->xss_clean('date')));
            $factura_info['ncf'] =htmlspecialchars($this->input->post($this->security->xss_clean('ncf')));
            $factura_info['numeracion_tipo'] =htmlspecialchars($this->input->post($this->security->xss_clean('tipo_numeracion')));
            $factura_info['fecha_venc'] =htmlspecialchars($this->input->post($this->security->xss_clean('fecha_venc')));
            $factura_info['tipo_cyg'] =htmlspecialchars($this->input->post($this->security->xss_clean('tipo_cyg')));
            $factura_info['tipo_pago'] =htmlspecialchars($this->input->post($this->security->xss_clean('tipo_pago')));
            facturar_compra($purchase_id,$factura_info);
            $prefijo = htmlspecialchars($this->input->post($this->security->xss_clean('prefijo')));
            $ncf = htmlspecialchars($this->input->post($this->security->xss_clean('ncf')));
            $data['factura_nro'] = $ncf;
            $data['itbis'] = $this->input->post('itbis');
        } else {
            $data['factura_nro'] = $this->input->post('factura_nro');
        }
        
        // ============ LÓGICA DE REGISTRO CONTABLE ============
        
        // Verificar si la cuenta o el monto cambió
        $account_changed = ($old_purchase->account_id != $data['account_id']);
        $amount_changed = (floatval($old_purchase->paid) != floatval($data['paid']));
        
        // Solo procesar movimientos contables si algo cambió
        if ($account_changed || $amount_changed) {
            // Si había un account_id anterior y monto pagado, revertir la transacción
            if ($old_purchase && !empty($old_purchase->account_id) && floatval($old_purchase->paid) > 0) {
                $old_account = $this->Account_model->getAccountById($old_purchase->account_id);
                if ($old_account) {
                    $balance_before_reversal = floatval($old_account->current_balance);
                    $old_amount = floatval($old_purchase->paid);
                    $balance_after_reversal = $balance_before_reversal + $old_amount; // DEVOLVER el dinero
                    
                    $this->Account_model->updateBalance($old_purchase->account_id, $balance_after_reversal);
                    
                    // Registrar transacción de reversión
                    $reversal_data = [
                        'to_account_id' => $old_purchase->account_id, // Reversión de compra = entrada de dinero
                        'transaction_type' => 'Deposito',
                        'amount' => $old_amount,
                        'reference_type' => 'purchase',
                        'reference_id' => $purchase_id,
                        'note' => 'Reversión de compra #' . $old_purchase->reference_no . ' (datos finales actualizados)',
                        'transaction_date' => date('Y-m-d H:i:s'),
                        'user_id' => $this->session->userdata('user_id'),
                        'company_id' => $this->session->userdata('company_id')
                    ];
                    $this->Account_transaction_model->insertTransaction($reversal_data);
                }
            }
            
            // Actualizar los datos de la compra
            $this->Common_model->updateInformation($data, $purchase_id, "tbl_purchase");
            
            // Aplicar nueva transacción si hay cuenta y monto
            if (!empty($data['account_id']) && floatval($data['paid']) > 0) {
                $new_account = $this->Account_model->getAccountById($data['account_id']);
                if ($new_account) {
                    $balance_before_new = floatval($new_account->current_balance);
                    $new_amount = floatval($data['paid']);
                    $balance_after_new = $balance_before_new - $new_amount; // Las compras RESTAN
                    
                    $this->Account_model->updateBalance($data['account_id'], $balance_after_new);
                    
                    $new_transaction_data = [
                        'from_account_id' => $data['account_id'], // Compra = salida de dinero
                        'transaction_type' => 'Compra',
                        'amount' => $new_amount,
                        'reference_type' => 'purchase',
                        'reference_id' => $purchase_id,
                        'note' => 'Compra #' . $data['reference_no'],
                        'transaction_date' => date('Y-m-d H:i:s'),
                        'user_id' => $this->session->userdata('user_id'),
                        'company_id' => $this->session->userdata('company_id')
                    ];
                    $this->Account_transaction_model->insertTransaction($new_transaction_data);
                }
            }
        } else {
            // Solo actualizar los datos sin movimientos contables
            $this->Common_model->updateInformation($data, $purchase_id, "tbl_purchase");
        }
        
        echo json_encode(['success' => true]);
    }

    /**
     * AJAX: Verificar si un N° de factura ya existe
     * - Restringido por company_id
     * - Excluye la compra actual si se envía purchase_id
     */
    public function ajaxCheckFacturaNro() {
        $factura_nro = trim($this->input->post('factura_nro'));
        $provider_id = trim($this->input->post('provider_id'));
        $purchase_id = $this->input->post('purchase_id'); // puede venir null cuando es alta

        if ($factura_nro === '') {
            echo json_encode(['found' => false, 'message' => '']);
            return;
        }

        $this->db->from('tbl_purchase');
        $this->db->where('factura_nro', $factura_nro);
        $this->db->where('supplier_id', $provider_id);
        // Mantén el filtro por estado si tu borrado es lógico
        $this->db->where('del_status !=', 'Deleted');
        // Excluir el propio registro si estás editando
        if (!empty($purchase_id)) {
            $this->db->where('id !=', $purchase_id);
        }

        $row = $this->db->get()->row();

        if ($row) {
            $supplier_name = '';
            if (!empty($row->supplier_id)) {
                $sp = $this->Common_model->getDataById($row->supplier_id, 'tbl_suppliers');
                $supplier_name = $sp ? $sp->name : '';
            }
            echo json_encode([
                'found' => true,
                'message' => 'Este N° de factura ya está registrado.',
                'purchase' => [
                    'id' => $row->id,
                    'reference_no' => $row->reference_no,
                    'date' => $row->date,
                    'supplier' => $supplier_name
                ]
            ]);
        } else {
            echo json_encode([
                'found' => false,
                'message' => 'Este N° de factura está disponible.'
            ]);
        }
    }
}
