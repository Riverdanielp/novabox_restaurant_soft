<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Customer_due extends Cl_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('excel'); //load PHPExcel library
        $this->load->model('Common_model');
        $this->load->model('Customer_due_receive_model');
        $this->load->library('form_validation');
        $this->Common_model->setDefaultTimezone();

        if (!$this->session->has_userdata('user_id')) {
            redirect('Authentication/index');
        }
        
        //start check access function
        $segment_2 = $this->uri->segment(2);
        $segment_3 = $this->uri->segment(3);
        $controller = "249"; // Usar el mismo controlador que Customer para permisos
        $function = "";

        if($segment_2=="customersDue" || $segment_2=="getAjaxDataDue" || $segment_2=="getPaymentsStatistics") {
            $function = "view";
        } elseif($segment_2=="saveCustomerDueReceive" || $segment_2=="getPaymentHistory" || $segment_2=="getPaymentDetail") {
            $function = "add";
        } else {
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
     * customers due info - Vista principal con widgets y tabla
     * @access public
     * @return void
     * @param no
     */
    public function customersDue() {
        $company_id = $this->session->userdata('company_id');
        $outlet_id = $this->session->userdata('outlet_id');

        $data = array();
        
        // Obtener estadísticas de deudas
        $data['due_stats'] = $this->getDueStatistics($company_id, $outlet_id);
        
        // Obtener cuentas bancarias para el modal de pagos
        $this->load->model('Account_model');
        $data['accounts'] = $this->Account_model->getAllAccountsByCompany($company_id);
        
        $data['main_content'] = $this->load->view('master/customer/customersDue', $data, TRUE);
        $this->load->view('userHome', $data);
    }

    /**
     * Obtener estadísticas de deudas de clientes
     * @access private
     * @return array
     * @param int $company_id
     * @param int $outlet_id
     */
    private function getDueStatistics($company_id, $outlet_id = null) {
        $stats = array();
        
        // Obtener todos los clientes de la compañía (excluyendo Walk-in Customer)
        $this->db->select('id');
        $this->db->from('tbl_customers');
        $this->db->where('company_id', $company_id);
        $this->db->where('id !=', 1); // Excluir Walk-in Customer
        $this->db->where('del_status', 'Live');
        $customers = $this->db->get()->result();
        
        $total_customers_with_due = 0;
        $total_due_amount = 0;
        $max_due_amount = 0;
        $total_customers = count($customers);
        
        foreach ($customers as $customer) {
            $customer_due = getCustomerDue($customer->id, $outlet_id);
            
            if ($customer_due > 0) {
                $total_customers_with_due++;
                $total_due_amount += $customer_due;
                
                if ($customer_due > $max_due_amount) {
                    $max_due_amount = $customer_due;
                }
            }
        }
        
        $stats['total_customers'] = $total_customers;
        $stats['total_customers_with_due'] = $total_customers_with_due;
        $stats['total_due_amount'] = $total_due_amount;
        $stats['max_due_amount'] = $max_due_amount;
        $stats['average_due'] = $total_customers_with_due > 0 ? ($total_due_amount / $total_customers_with_due) : 0;
        $stats['percentage_with_due'] = $total_customers > 0 ? (($total_customers_with_due / $total_customers) * 100) : 0;
        
        return $stats;
    }

    /**
     * AJAX Data para DataTables - Solo clientes con deudas
     * @access public
     * @return json
     * @param no
     */
    public function getAjaxDataDue()
    {
        $company_id = $this->session->userdata('company_id');
        $is_loyalty_enable = $this->session->userdata('is_loyalty_enable');
        $outlet_id = $this->session->userdata('outlet_id');

        // Obtener TODOS los clientes de la compañía (sin paginación)
        $this->db->select('*');
        $this->db->from('tbl_customers');
        $this->db->where('company_id', $company_id);
        $this->db->where('del_status', 'Live');
        $this->db->where('id !=', 1); // Excluir Walk-in Customer
        
        // Aplicar búsqueda si existe
        if (isset($_POST['search']['value']) && $_POST['search']['value'] != '') {
            $search = $_POST['search']['value'];
            $this->db->group_start();
            $this->db->like('name', $search);
            $this->db->or_like('phone', $search);
            $this->db->or_like('email', $search);
            $this->db->or_like('address', $search);
            $this->db->group_end();
        }
        
        $all_customers = $this->db->get()->result();

        // Filtrar solo clientes con deuda y preparar datos
        $all_rows = array();
        $counter = 1;
        
        foreach ($all_customers as $cust) {
            $current_due = getCustomerDue($cust->id, $outlet_id);
            
            // SOLO INCLUIR SI TIENE DEUDA
            if ($current_due > 0) {
                $redeemed_point = 0;
                $available_point = 0;
                
                if ($is_loyalty_enable == "enable") {
                    $return_data = getTotalLoyaltyPoint($cust->id, $outlet_id);
                    $redeemed_point = $return_data[0];
                    $available_point = $return_data[1];
                }
                
                $actions = '';
                $actions .= '<div class="btn_group_wrap">';
                $actions .= '<button class="btn btn-success btn-sm me-1 btn-payment-modal" data-customer-id="'.$cust->id.'" data-customer-name="'.escape_output($cust->name).'" data-bs-toggle="tooltip" title="Registrar Pago"><i class="fa fa-dollar-sign"></i></button>';
                $actions .= '<button class="btn btn-primary btn-sm me-1 btn-history-modal" data-customer-id="'.$cust->id.'" data-customer-name="'.escape_output($cust->name).'" data-bs-toggle="tooltip" title="Ver Historial"><i class="fa fa-history"></i></button>';
                $actions .= '<a class="btn btn-warning btn-sm" href="'.base_url().'customer/addEditCustomer/'.escape_output($this->custom->encrypt_decrypt($cust->id, "encrypt")).'" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="'.lang('edit').'"><i class="far fa-edit"></i></a>';
                $actions .= '</div>';

                $row = array();
                $row['counter'] = $counter++;
                $row['name'] = escape_output($cust->name);
                $row['phone'] = escape_output($cust->phone);
                $row['email'] = escape_output($cust->email);
                $row['address'] = escape_output($cust->address);
                $row['current_due_display'] = getAmtCustom($current_due);
                $row['current_due_raw'] = $current_due; // Para ordenar
                
                // Obtener fecha del último pago (solo pagos positivos)
                $this->db->select('MAX(only_date) as last_payment_date');
                $this->db->from('tbl_customer_due_receives');
                $this->db->where('customer_id', $cust->id);
                $this->db->where('amount >', 0); // Solo pagos positivos
                $this->db->where('del_status', 'Live');
                $payment_result = $this->db->get()->row();
                
                if ($payment_result && $payment_result->last_payment_date) {
                    $row['last_payment_date'] = date($this->session->userdata('date_format'), strtotime($payment_result->last_payment_date));
                } else {
                    $row['last_payment_date'] = 'N/A';
                }
                
                if ($is_loyalty_enable == "enable") {
                    $row['available_point'] = escape_output($available_point);
                }
                
                $row['user_name'] = userName($cust->user_id);
                $row['actions'] = $actions;
                $all_rows[] = $row;
            }
        }

        // Ordenar si es necesario
        $order_column = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : null;
        $order_dir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'desc';
        $current_due_column_index = 5; // Columna de deuda actual

        if ($order_column === $current_due_column_index) {
            usort($all_rows, function($a, $b) use ($order_dir) {
                if ($a['current_due_raw'] == $b['current_due_raw']) return 0;
                if ($order_dir == 'asc')
                    return ($a['current_due_raw'] < $b['current_due_raw']) ? -1 : 1;
                else
                    return ($a['current_due_raw'] > $b['current_due_raw']) ? -1 : 1;
            });
        }

        // Aplicar paginación manualmente
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $total_records = count($all_rows);
        
        $paginated_rows = array_slice($all_rows, $start, $length == -1 ? $total_records : $length);
        
        // Preparar datos para DataTables
        $data = array();
        $i = $start + 1;
        foreach ($paginated_rows as $row) {
            $dt_row = array();
            $dt_row[] = $i++;
            $dt_row[] = $row['name'];
            $dt_row[] = $row['phone'];
            $dt_row[] = $row['email'];
            $dt_row[] = $row['address'];
            $dt_row[] = $row['current_due_display'];
            $dt_row[] = $row['last_payment_date']; // Nueva columna
            
            if ($is_loyalty_enable == "enable") {
                $dt_row[] = $row['available_point'];
            }
            
            $dt_row[] = $row['user_name'];
            $dt_row[] = $row['actions'];
            $data[] = $dt_row;
        }

        $output = array(
            "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
            "recordsTotal" => $total_records,
            "recordsFiltered" => $total_records,
            "data" => $data
        );
        echo json_encode($output);
    }

    /**
     * Guardar pago de deuda desde modal (Ajax)
     * @access public
     * @return json
     * @param no
     */
    public function saveCustomerDueReceive() {
        $outlet_id = $this->session->userdata('outlet_id');
        $company_id = $this->session->userdata('company_id');
        
        // Validaciones
        $customer_id = $this->input->post('customer_id');
        $amount = $this->input->post('amount');
        $payment_id = $this->input->post('payment_id');
        $account_id = $this->input->post('account_id');
        $note = $this->input->post('note');
        $sales_details = $this->input->post('sales_details');
        
        if (!$customer_id || !$amount || !$payment_id) {
            echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
            return;
        }
        
        // Generar número de referencia
        $this->load->model('Customer_due_receive_model');
        $reference_no = $this->Customer_due_receive_model->generateReferenceNo($outlet_id);
        
        // Determinar la cuenta a usar
        $final_account_id = $account_id;
        if (empty($account_id)) {
            // Si no se seleccionó cuenta específica, usar la cuenta del método de pago (Caja Abierta)
            $payment_method = $this->Common_model->getDataById($payment_id, 'tbl_payment_methods');
            if ($payment_method && !empty($payment_method->account_id)) {
                $final_account_id = $payment_method->account_id;
            }
        }
        
        // Preparar datos del pago
        $splr_payment_info = array();
        $splr_payment_info['date'] = date("Y-m-d H:i:s");
        $splr_payment_info['only_date'] = date("Y-m-d");
        $splr_payment_info['amount'] = $amount;
        $splr_payment_info['reference_no'] = $reference_no;
        $splr_payment_info['customer_id'] = $customer_id;
        $splr_payment_info['payment_id'] = $payment_id;
        $splr_payment_info['account_id'] = $final_account_id; // Usar la cuenta final (seleccionada o del método de pago)
        $splr_payment_info['note'] = $note;
        $splr_payment_info['counter_id'] = $this->session->userdata('counter_id');
        $splr_payment_info['user_id'] = $this->session->userdata('user_id');
        $splr_payment_info['outlet_id'] = $outlet_id;
        $splr_payment_info['company_id'] = $company_id;
        
        // Insertar el pago principal
        $due_receive_id = $this->Common_model->insertInformation($splr_payment_info, "tbl_customer_due_receives");
        
        // Registrar transacción contable - Cobro Cliente (Deposito)
        $this->load->model('Account_transaction_model');
        $transaction_data = [
            'transaction_date' => date("Y-m-d H:i:s"),
            'to_account_id' => $final_account_id,
            'transaction_type' => 'Cobro Cliente',
            'amount' => $amount,
            'reference_type' => 'customer_due_receive',
            'reference_id' => $due_receive_id,
            'note' => 'Cobro de cliente - ' . $reference_no . ($note ? ' - ' . $note : ''),
            'user_id' => $this->session->userdata('user_id'),
            'company_id' => $company_id
        ];
        $this->Account_transaction_model->insertTransaction($transaction_data);
        
        // Procesar sales_details (ventas afectadas por este pago)
        if ($sales_details && is_array($sales_details)) {
            foreach ($sales_details as $sale_id => $payment_amount) {
                $payment_amount = floatval($payment_amount);
                
                if ($payment_amount > 0) {
                    // Verificar si es un pago negativo (saldo inicial) - ID empieza con "NEG-"
                    if (strpos($sale_id, 'NEG-') === 0) {
                        // Es un pago negativo - NO guardar en tbl_customer_due_receives_sales
                        // Solo sirve para balancear, los pagos positivos y negativos se anulan
                        // NO hacemos nada en BD, solo lo usamos en la UI para mostrar distribución
                        continue;
                    }
                    
                    // Es una venta real - procesar normalmente
                    // Insertar en tbl_customer_due_receives_sales
                    $sale_payment_data = [
                        'due_receive_id' => $due_receive_id,
                        'sale_id' => $sale_id,
                        'amount' => $payment_amount
                    ];
                    $this->db->insert('tbl_customer_due_receives_sales', $sale_payment_data);
                    
                    // Actualizar paid_due_amount en tbl_sales (SUMAR al existente)
                    $this->db->set('paid_due_amount', 'paid_due_amount + ' . $payment_amount, FALSE);
                    $this->db->where('id', $sale_id);
                    $this->db->update('tbl_sales');
                }
            }
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Pago registrado correctamente',
            'reference_no' => $reference_no,
            'due_receive_id' => $due_receive_id
        ]);
    }

    /**
     * Obtener historial de pagos del cliente (Ajax)
     * @access public
     * @return json
     * @param no
     */
    public function getPaymentHistory() {
        $customer_id = $this->input->get('customer_id');
        $outlet_id = $this->session->userdata('outlet_id');
        
        if (!$customer_id) {
            echo json_encode(['status' => 'error', 'data' => []]);
            return;
        }
        
        // Obtener pagos del cliente
        $this->db->select('id, reference_no, date, only_date, amount, payment_id, note, user_id');
        $this->db->from('tbl_customer_due_receives');
        $this->db->where('customer_id', $customer_id);
        $this->db->where('outlet_id', $outlet_id);
        $this->db->where('del_status', 'Live');
        $this->db->order_by('date', 'DESC');
        
        $payments = $this->db->get()->result();
        
        $formatted_payments = [];
        foreach ($payments as $payment) {
            $formatted_payments[] = [
                'id' => $payment->id,
                'reference_no' => $payment->reference_no,
                'date' => date($this->session->userdata('date_format'), strtotime($payment->only_date)),
                'amount' => getAmtP($payment->amount),
                'amount_raw' => $payment->amount,
                'payment_method' => getPaymentName($payment->payment_id),
                'note' => $payment->note,
                'user_name' => userName($payment->user_id)
            ];
        }
        
        echo json_encode(['status' => 'success', 'data' => $formatted_payments]);
    }

    /**
     * Obtener detalle de un pago específico para imprimir
     * @access public
     * @return json
     * @param no
     */
    public function getPaymentDetail() {
        $payment_id = $this->input->get('payment_id');
        
        if (!$payment_id) {
            echo json_encode(['status' => 'error']);
            return;
        }
        
        // Obtener datos del pago
        $this->db->select('*');
        $this->db->from('tbl_customer_due_receives');
        $this->db->where('id', $payment_id);
        $payment = $this->db->get()->row();
        
        if (!$payment) {
            echo json_encode(['status' => 'error']);
            return;
        }
        
        // Obtener ventas afectadas
        $this->db->select('s.sale_no, s.sale_date, drs.amount');
        $this->db->from('tbl_customer_due_receives_sales drs');
        $this->db->join('tbl_sales s', 's.id = drs.sale_id', 'left');
        $this->db->where('drs.due_receive_id', $payment_id);
        $this->db->order_by('s.sale_date', 'ASC');
        $affected_sales = $this->db->get()->result();
        
        $sales_list = [];
        foreach ($affected_sales as $sale) {
            $sales_list[] = [
                'sale_no' => $sale->sale_no,
                'sale_date' => date($this->session->userdata('date_format'), strtotime($sale->sale_date)),
                'amount' => getAmtP($sale->amount)
            ];
        }
        
        $ticket_data = [
            'ref_no' => $payment->reference_no,
            'date' => date($this->session->userdata('date_format'), strtotime($payment->only_date)),
            'customer' => getCustomerName($payment->customer_id),
            'amount' => getAmtP($payment->amount),
            'payment_method' => getPaymentName($payment->payment_id),
            'note' => $payment->note ?? '',
            'sales' => $sales_list
        ];
        
        echo json_encode(['status' => 'success', 'data' => $ticket_data]);
    }

    /**
     * Obtener estadísticas de pagos con filtros (excluye pagos negativos)
     * @access public
     * @return void
     */
    public function getPaymentsStatistics() {
        header('Content-Type: application/json');
        
        $outlet_id = $this->session->userdata('outlet_id');
        $date_from = $this->input->get('date_from');
        $date_to = $this->input->get('date_to');
        $customer_id = $this->input->get('customer_id');
        $user_id = $this->input->get('user_id');

        // Validar fechas
        if (!$date_from) $date_from = date('Y-m-01');
        if (!$date_to) $date_to = date('Y-m-t');

        try {
            // Construir query - Solo incluir pagos positivos (amount > 0)
            $this->db->select('
                SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END) as total_payments,
                COUNT(CASE WHEN amount > 0 THEN 1 END) as payment_count,
                AVG(CASE WHEN amount > 0 THEN amount ELSE NULL END) as average_payment,
                MAX(CASE WHEN amount > 0 THEN amount ELSE NULL END) as max_payment
            ');
            $this->db->from('tbl_customer_due_receives');
            $this->db->where('outlet_id', $outlet_id);
            $this->db->where('only_date >=', $date_from);
            $this->db->where('only_date <=', $date_to);
            $this->db->where('del_status', 'Live');

            if ($customer_id) {
                $this->db->where('customer_id', $customer_id);
            }
            
            if ($user_id) {
                $this->db->where('user_id', $user_id);
            }

            $result = $this->db->get()->row();

            // Preparar respuesta
            $total_payments = (float)($result->total_payments ?? 0);
            $payment_count = (int)($result->payment_count ?? 0);
            $average_payment = (float)($result->average_payment ?? 0);
            $max_payment = (float)($result->max_payment ?? 0);

            // Formatear montos con separador de miles (punto) y sin decimales
            $currency = $this->session->userdata('currency');
            
            $response = [
                'status' => 'success',
                'data' => [
                    'total_payments' => $total_payments,
                    'total_payments_formatted' => $currency . ' ' . number_format($total_payments, 0, ',', '.'),
                    'payment_count' => $payment_count,
                    'average_payment' => $average_payment,
                    'average_payment_formatted' => $currency . ' ' . number_format($average_payment, 0, ',', '.'),
                    'max_payment' => $max_payment,
                    'max_payment_formatted' => $currency . ' ' . number_format($max_payment, 0, ',', '.')
                ]
            ];
        } catch(Exception $e) {
            $response = [
                'status' => 'error',
                'message' => 'Error al obtener estadísticas',
                'error' => $e->getMessage()
            ];
        }

        echo json_encode($response);
    }
}
