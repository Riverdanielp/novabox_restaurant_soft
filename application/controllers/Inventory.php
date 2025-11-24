<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Inventory extends Cl_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Authentication_model');
        $this->load->model('Common_model');
        $this->load->model('Inventory_model');
        $this->load->model('Master_model');
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
     * inventory info
     * @access public
     * @return void
     * @param no
     */
    public function index() {
        //start check access function
        $segment_1 = $this->uri->segment(1);
        $segment_2 = $this->uri->segment(2);

        $controller = "129";
        $function = "";

        if($segment_1=="Inventory" || $segment_2=="index"){
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


        $data = array();
        $ingredient_id = $this->input->post('ingredient_id');
        $category_id = $this->input->post('category_id');
        $food_id = $this->input->post('food_id');
        $outlet = getOutletById($this->session->userdata('outlet_id'));
        $data['hora'] = date('d/m/Y H:i:s'); 
        $data['outlet_name'] = $outlet->outlet_name; 
        $data['username'] = $this->session->userdata('full_name'); 
        $data['ingredient_id'] = $ingredient_id;
        $company_id = $this->session->userdata('company_id');
        $data['ingredient_categories'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, "tbl_ingredient_categories");
        $data['ingredients'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, "tbl_ingredients");

        $data['foodMenus'] = $this->Sale_model->getAllFoodMenus();
        $data['inventory'] = $this->Inventory_model->getInventory($category_id, $ingredient_id, $food_id);
        $data['main_content'] = $this->load->view('inventory/inventory', $data, TRUE);
        $this->load->view('userHome', $data);
    }
    /**
     * inventory info
     * @access public
     * @return void
     * @param no
     */
    public function inventoryFoodMenu() {
        //start check access function
        $segment_1 = $this->uri->segment(1);
        $segment_2 = $this->uri->segment(2);

        $controller = "346";
        $function = "";

        if($segment_1=="Inventory" || $segment_2=="inventoryFoodMenu"){
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

        $data = array();
        $food_id = $this->input->post('food_id');
        $company_id = $this->session->userdata('company_id');
        $data['foodMenus'] = $this->Sale_model->getAllFoodMenus();
        $category_id = $this->input->post('category_id');
        $data['ingredient_categories'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, "tbl_food_menu_categories");
        $data['inventory'] = $this->Inventory_model->getInventoryFoodMenu($food_id,$category_id);
        $data['main_content'] = $this->load->view('inventory/inventory_food_menu', $data, TRUE);
        $this->load->view('userHome', $data);
    }
     /**
     * inventory info
     * @access public
     * @return void
     * @param no
     */
     /**
     * get Inventory Alert List
     * @access public
     * @return void
     * @param no
     */
    public function getInventoryAlertList() {
        //start check access function
        $segment_1 = $this->uri->segment(1);
        $segment_2 = $this->uri->segment(2);

        $controller = "173";
        $function = "";

        if($segment_1=="Inventory" || $segment_2=="getInventoryAlertList"){
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

        $data = array();
        $data['inventory'] = $this->Inventory_model->getInventoryAlertList();
        $data['main_content'] = $this->load->view('inventory/inventoryAlertList', $data, TRUE);
        $this->load->view('userHome', $data);
    }
     /**
     * get Ingredient Info Ajax
     * @access public
     * @return object
     * @param no
     */
    public function getIngredientInfoAjax() {
        $cat_id = $_GET['category_id'];
        $outlet_id = $this->session->userdata('outlet_id');
        if ($cat_id) {
            $results = $this->Inventory_model->getDataByCatId($cat_id, "tbl_ingredients");
        } else {
            $results = $this->Common_model->getAllByOutletIdForDropdown($outlet_id, "tbl_ingredients");
        }
        echo json_encode($results);
    }
    public function getCurrentInventory() {
        $ing_id = $_POST['ing_id'];
        $data =  $this->Inventory_model->getCurrentInventory($ing_id);
        echo json_encode($data);
    }

    public function getStockByOutlets() {
        $ingredient_id = $this->input->post('ingredient_id');
        $company_id = $this->session->userdata('company_id');
        $outlets = $this->Common_model->getAllByCompanyIdForDropdown($company_id, "tbl_outlets");
        $html = '<table class="table table-striped"><thead><tr><th>Sucursal</th><th>Stock</th></tr></thead><tbody>';
        foreach ($outlets as $outlet) {
            $stock = $this->Inventory_model->getCurrentInventoryByOutlet($ingredient_id, $outlet->id);
            $html .= '<tr><td>' . $outlet->outlet_name . '</td><td>' . $stock['total_stock'] . ' ' . $stock['stock_unit'] . '</td></tr>';
        }
        $html .= '</tbody></table>';
        echo $html;
    }

    public function getIngredientMovements() {
        $ingredient_id = $this->input->post('ingredient_id');
        $page = $this->input->post('page') ?: 1;
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        $per_page = 10;
        $offset = ($page - 1) * $per_page;

        $movements = $this->Inventory_model->getIngredientMovements($ingredient_id, $per_page, $offset, $start_date, $end_date);
        $total_movements = $this->Inventory_model->countIngredientMovements($ingredient_id, $start_date, $end_date);
        $total_pages = ceil($total_movements / $per_page);

        $html = '<table class="table table-striped"><thead><tr><th>Fecha</th><th>Tipo</th><th>Cantidad</th><th>Acción</th></tr></thead><tbody>';
        foreach ($movements as $movement) {
            $button = '';
            if ($movement->type == 'Compra') {
                $button = '<a href="' . base_url() . 'Purchase/purchaseDetails/' . $movement->movement_id . '" class="btn btn-primary btn-sm" target="_blank">Ver Compra</a>';
            } elseif ($movement->type == 'Consumo' || $movement->type == 'Consumo Modificador') {
                $button = '<a href="' . base_url() . 'Sale/print_invoice/' . $movement->movement_id . '" class="btn btn-primary btn-sm" target="_blank">Ver Venta</a>';
            } elseif ($movement->type == 'Desperdicio') {
                $button = '<a href="' . base_url() . 'Waste/wasteDetails/' . $movement->movement_id . '" class="btn btn-primary btn-sm" target="_blank">Ver Desperdicio</a>';
            } elseif (strpos($movement->type, 'Ajuste') !== false) {
                $button = '<a href="' . base_url() . 'Inventory_adjustment/inventoryAdjustmentDetails/' . $movement->movement_id . '" class="btn btn-primary btn-sm" target="_blank">Ver Ajuste</a>';
            } elseif ($movement->type == 'Producción') {
                $button = '<a href="' . base_url() . 'Production/productionDetails/' . $movement->movement_id . '" class="btn btn-primary btn-sm" target="_blank">Ver Producción</a>';
            } elseif (strpos($movement->type, 'Transferencia') !== false) {
                $button = '<a href="' . base_url() . 'Transfer/transferDetails/' . $movement->movement_id . '" class="btn btn-primary btn-sm" target="_blank">Ver Transferencia</a>';
            }
            $html .= '<tr><td>' . $movement->date . '</td><td>' . $movement->type . '</td><td>' . $movement->quantity . '</td><td>' . $button . '</td></tr>';
        }
        $html .= '</tbody></table>';

        $pagination = '';
        for ($i = 1; $i <= $total_pages; $i++) {
            $active = ($i == $page) ? 'active' : '';
            $pagination .= '<li class="page-item ' . $active . '"><a class="page-link" href="#" onclick="loadMovements(' . $i . ', \'' . $start_date . '\', \'' . $end_date . '\')">' . $i . '</a></li>';
        }

        echo json_encode(['html' => $html, 'pagination' => $pagination]);
    }

    public function ingredientSalesHistory($ingredient_id) {
        $data = array();
        $data['ingredient_id'] = $ingredient_id;
        // Obtener el nombre del ingrediente
        $this->db->select('name');
        $this->db->from('tbl_ingredients');
        $this->db->where('id', $ingredient_id);
        $ingredient = $this->db->get()->row();
        $data['ingredient_name'] = $ingredient ? $ingredient->name : 'Ingrediente desconocido';
        $data['sales_history'] = $this->Inventory_model->getIngredientSalesHistory($ingredient_id);
        $data['main_content'] = $this->load->view('inventory/ingredient_sales_history', $data, TRUE);
        $this->load->view('userHome', $data);
    }

}
