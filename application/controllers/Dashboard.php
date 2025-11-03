<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends Cl_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Authentication_model');
        $this->load->model('Common_model');
        $this->load->model('Dashboard_model');
        $this->load->model('Inventory_model');
        $this->load->model('Report_model');
        $this->Common_model->setDefaultTimezone();
        $this->load->library('form_validation');
        
        if (!$this->session->has_userdata('user_id')) {
            redirect('Authentication/index');
        }

        if (!$this->session->has_userdata('outlet_id')) {
            $this->session->set_flashdata('exception_2',lang('please_click_green_button'));

            $this->session->set_userdata("clicked_controller", $this->uri->segment(1));
            $this->session->set_userdata("clicked_method", $this->uri->segment(2));
            redirect('Outlet/outlets');
        }


        $login_session['active_menu_tmp'] = '';
        $this->session->set_userdata($login_session);




    }

    /**
     * bar panel
     * @access public
     * @return void
     * @param no
     */
    public function dashboard() {
        //start check access function
        $segment_2 = $this->uri->segment(2);
        $controller = "1";
        $function = "";

        if($segment_2=="dashboard"){
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

        $first_day_this_month  = isset($_POST['start_date_dashboard']) && $_POST['start_date_dashboard']?$_POST['start_date_dashboard']:date('Y-m-01');
        $last_day_this_month  = isset($_POST['end_date_dashboard']) && $_POST['end_date_dashboard']?$_POST['end_date_dashboard']:date('Y-m-t');

        $outlet_id  = isset($_POST['outlet_id']) && $_POST['outlet_id']?$_POST['outlet_id']:'';
        if(!$outlet_id){
            $outlet_id = $this->session->userdata('outlet_id');
        }

        $data['outlet_id'] =  $outlet_id;

        // OPTIMIZACIÓN: no disparar el inventario completo solo para contar
        // Antes: sizeof($this->Dashboard_model->getInventory($outlet_id));
        $data['ingredient_count'] = (int)$this->Inventory_model->countAllIngredientsByCompany();

        $data['customer_count'] = $this->Dashboard_model->countData('tbl_customers');
        $data['employee_count'] = $this->Dashboard_model->countData('tbl_users');

        $data['start_date_dashboard'] = $first_day_this_month;
        $data['end_date_dashboard'] = $last_day_this_month;

        // Sugerencia adicional: mover esta carga a AJAX si sigue pesado
        $data['low_stock_ingredients'] = $this->Inventory_model->getInventoryAlertList($outlet_id);

        $data['top_ten_food_menu'] = $this->Dashboard_model->top_ten_food_menu($first_day_this_month, $last_day_this_month,$outlet_id);
        $data['top_ten_customer'] = $this->Dashboard_model->top_ten_customer($first_day_this_month, $last_day_this_month,$outlet_id);
        $data['customer_receivable'] = $this->Dashboard_model->customer_receivable($outlet_id);
        $data['supplier_payable'] = $this->Dashboard_model->supplier_payable($outlet_id);

        $data['dinein_count'] = $this->Dashboard_model->dinein_count($first_day_this_month, $last_day_this_month,$outlet_id);
        $data['take_away_count'] = $this->Dashboard_model->take_away_count($first_day_this_month, $last_day_this_month,$outlet_id);
        $data['delivery_count'] = $this->Dashboard_model->delivery_count($first_day_this_month, $last_day_this_month,$outlet_id);

        $data['purchase_sum'] = $this->Dashboard_model->purchase_sum($first_day_this_month, $last_day_this_month,$outlet_id);
        $data['sale_sum'] = $this->Dashboard_model->sale_sum($first_day_this_month, $last_day_this_month,$outlet_id);
        $data['waste_sum'] = $this->Dashboard_model->waste_sum($first_day_this_month, $last_day_this_month,$outlet_id);
        $data['expense_sum'] = $this->Dashboard_model->expense_sum($first_day_this_month, $last_day_this_month,$outlet_id);
        $data['customer_due_receive_sum'] = $this->Dashboard_model->customer_due_receive_sum($first_day_this_month, $last_day_this_month,$outlet_id);
        $data['supplier_due_payment_sum'] = $this->Dashboard_model->supplier_due_payment_sum($first_day_this_month, $last_day_this_month,$outlet_id);
        $data['outlets'] = $this->Common_model->getAllOutlestByAssign();
        $data['sale_by_payments'] = $this->Dashboard_model->sale_by_payments($first_day_this_month, $last_day_this_month,$outlet_id);
        $data['sale_by_paymentsTotal'] = $this->Dashboard_model->sale_by_paymentsTotal($first_day_this_month, $last_day_this_month,$outlet_id);

        $data['main_content'] = $this->load->view('dashboard/dashboard', $data, TRUE);
        $this->load->view('userHome', $data);
    }

    /**
     * bar panel
     * @access public
     * @return void
     * @param no
     */
    function operation_comparision_by_date_ajax(){
        $from_this_day = $this->input->post('from_this_day');
        $to_this_day = $this->input->post('to_this_day');
        
        $data = array();

        $data['purchase_sum'] = $this->Dashboard_model->purchase_sum($from_this_day, $to_this_day);  
        $data['sale_sum'] = $this->Dashboard_model->sale_sum($from_this_day, $to_this_day);  
        $data['waste_sum'] = $this->Dashboard_model->waste_sum($from_this_day, $to_this_day);  
        $data['expense_sum'] = $this->Dashboard_model->expense_sum($from_this_day, $to_this_day);  
        $data['customer_due_receive_sum'] = $this->Dashboard_model->customer_due_receive_sum($from_this_day, $to_this_day);  
        $data['supplier_due_payment_sum'] = $this->Dashboard_model->supplier_due_payment_sum($from_this_day, $to_this_day);
        $data['from_this_day'] = $from_this_day;
        $data['to_this_day'] = $to_this_day;
        echo json_encode($data);
    }

    /**
     * bar panel
     * @access public
     * @return void
     * @param no
     */
    function comparison_sale_report_ajax_get() {
        $selectedMonth = $_GET['months'];
        $finalOutput = array();
        for ($i = $selectedMonth - 1; $i >= 0; $i--) {
            $dateCalculate = $i > 0 ? '-' . $i : $i;
            $sqlStartDate = date('Y-m-01', strtotime($dateCalculate . ' month'));
            // FIX: último día del mes correcto
            $sqlEndDate = date('Y-m-t', strtotime($dateCalculate . ' month'));
            $saleAmount = $this->Common_model->comparison_sale_report($sqlStartDate, $sqlEndDate);
            $finalOutput[] = array(
                'month' => date('M-y', strtotime($dateCalculate . ' month')),
                'saleAmount' => !empty($saleAmount) ? $saleAmount->total_amount : 0.0,
            );
        }
        echo json_encode($finalOutput);
    }

    // OPTIMIZADO: genera series con 1 query por métrica (no 1 por bucket)
    public function get_sale_report_charge(){
        $outlet_id  = isset($_POST['outlet_id']) && $_POST['outlet_id']?$_POST['outlet_id']:'';
        $start_date  = isset($_POST['start_date']) && $_POST['start_date']?$_POST['start_date']:'';
        $end_date  = isset($_POST['end_date']) && $_POST['end_date']?$_POST['end_date']:'';
        $type  = isset($_POST['type']) && $_POST['type']?$_POST['type']:'day'; // day|week|month
        $action_type  = isset($_POST['action_type']) && $_POST['action_type']?$_POST['action_type']:'revenue';

        if(!$outlet_id){
            $outlet_id = $this->session->userdata('outlet_id');
        }

        // Construir el calendario de buckets con tu helper existente (mantiene etiquetas)
        $day_wise_date = (getSaleDate($start_date,$end_date,$type));

        // Mapear series por bucket usando 1 consulta agregada por métrica
        $series_map = [];
        if ($action_type === 'revenue' || $action_type === 'average_receipt') {
            $series_map = $this->Report_model->getRevenueSeries($start_date, $end_date, $outlet_id, $type);
        } else if ($action_type === 'transactions') {
            $series_map = $this->Report_model->getTransactionsSeries($start_date, $end_date, $outlet_id, $type);
        } else if ($action_type === 'customers') {
            $series_map = $this->Report_model->getCustomersSeries($start_date, $end_date, $outlet_id, $type);
        } else if ($action_type === 'profit') {
            $series_map = $this->Report_model->getProfitSeries($start_date, $end_date, $outlet_id, $type);
        }

        // Armar data_points respetando las etiquetas/estructura actual
        $return_date_day = [];
        foreach ($day_wise_date as $value){
            $date_split = explode("||",$value);
            $bucket_start = date("Y-m-d",strtotime($date_split[0])); // clave del mapa
            $y_val = isset($series_map[$bucket_start]) ? (float)$series_map[$bucket_start] : 0.0;

            $inline_array = array();
            $inline_array['y_value'] = getAmtP($y_val);
            $inline_array['y_label'] = $date_split[2];
            $inline_array['x_label'] = $date_split[3];
            $inline_array['x_label_tmp'] = ($action_type==='transactions'?lang('transactions'):
                                            ($action_type==='customers'?lang('customers'):
                                            ($action_type==='average_receipt'?lang('average_receipt'):'')
                                            ));
            $return_date_day[] = $inline_array;
        }

        // Totales del panel (rango completo) con consultas livianas
        $totals = [];
        $totals['revenue']      = $this->Report_model->getRevenueTotal($start_date, $end_date, $outlet_id);
        $totals['profit']       = $this->Report_model->getProfitTotal($start_date, $end_date, $outlet_id);
        $totals['transactions'] = $this->Report_model->getTransactionsTotal($start_date, $end_date, $outlet_id);
        $totals['customers']    = $this->Report_model->getCustomersTotal($start_date, $end_date, $outlet_id);

        $return_array = [];
        $return_array['data_points'] =  $return_date_day;
        $return_array['set_total_1'] =  getAmtPCustom($totals['revenue']);
        $return_array['set_total_2'] =  getAmtPCustom($totals['profit']);
        $return_array['set_total_3'] =  getAmtPCustom($totals['transactions']);
        $return_array['set_total_4'] =  0; //getAmtPCustom($totals['customers']);

        echo json_encode($return_array);
    }

    // OPTIMIZADO: totales “hoy” con agregados simples (sin profitLossReport)
    public function get_sale_report_charge_today(){
        $outlet_id  = isset($_POST['outlet_id']) && $_POST['outlet_id']?$_POST['outlet_id']:'';
        $start_date  = date('Y-m-d');
        $end_date  = date('Y-m-d');

        if(!$outlet_id){
            $outlet_id = $this->session->userdata('outlet_id');
        }

        $revenue      = $this->Report_model->getRevenueTotal($start_date, $end_date, $outlet_id);
        $profit       = $this->Report_model->getProfitTotal($start_date, $end_date, $outlet_id);
        $transactions = $this->Report_model->getTransactionsTotal($start_date, $end_date, $outlet_id);
        $customers    = $this->Report_model->getCustomersTotal($start_date, $end_date, $outlet_id);

        $return_array['set_total_1'] =  getAmtPCustom($revenue);
        $return_array['set_total_2'] =  getAmtPCustom($profit);
        $return_array['set_total_3'] =  getAmtPCustom($transactions);
        $return_array['set_total_4'] =  0; //getAmtPCustom($customers);

        echo json_encode($return_array);
    }
}
