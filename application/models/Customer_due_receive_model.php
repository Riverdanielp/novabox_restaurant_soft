<?php
/*
  ###########################################################
  # PRODUCT NAME: 	iRestora PLUS - Next Gen Restaurant POS
  ###########################################################
  # AUTHER:		Doorsoft
  ###########################################################
  # EMAIL:		info@doorsoft.co
  ###########################################################
  # COPYRIGHTS:		RESERVED BY Door Soft
  ###########################################################
  # WEBSITE:		http://www.doorsoft.co
  ###########################################################
  # This is Customer_due_receive_model Model
  ###########################################################
 */
class Customer_due_receive_model extends CI_Model {

    /**
     * get Customer Due
     * @access public
     * @return float
     * @param int
     */
    public function getCustomerDue($customer_id) {
        // Validar que customer_id no esté vacío
        if (empty($customer_id) || !is_numeric($customer_id)) {
            return 0;
        }
        
        $outlet_id = $this->session->userdata('outlet_id');
        
        // Validar que outlet_id no esté vacío
        if (empty($outlet_id) || !is_numeric($outlet_id)) {
            return 0;
        }
        
        // Usar consultas preparadas para evitar SQL injection
        $customer_due_query = $this->db->query("SELECT SUM(due_amount) as due FROM tbl_sales WHERE customer_id=? and outlet_id=? and del_status='Live'", array($customer_id, $outlet_id));
        
        // Verificar que la consulta fue exitosa
        if (!$customer_due_query) {
            return 0;
        }
        
        $customer_due = $customer_due_query->row();
        
        $customer_payment_query = $this->db->query("SELECT SUM(amount) as amount FROM tbl_customer_due_receives WHERE customer_id=? and outlet_id=? and del_status='Live'", array($customer_id, $outlet_id));
        
        // Verificar que la consulta fue exitosa
        if (!$customer_payment_query) {
            return $customer_due->due ? $customer_due->due : 0;
        }
        
        $customer_payment = $customer_payment_query->row();
        
        // Manejar valores nulos
        $due_amount = $customer_due->due ? $customer_due->due : 0;
        $payment_amount = $customer_payment->amount ? $customer_payment->amount : 0;
        
        $remaining_due = $due_amount - $payment_amount;
        return $remaining_due;
 
    }
    /**
     * generate Reference No
     * @access public
     * @return string
     * @param int
     */
    public function generateReferenceNo($outlet_id) {
        $reference_no = $this->db->query("SELECT count(id) as reference_no
               FROM tbl_customer_due_receives where outlet_id=$outlet_id")->row('reference_no');
        $reference_no = str_pad($reference_no + 1, 6, '0', STR_PAD_LEFT);
        return $reference_no;
    }

    /**
     * Obtener ventas pendientes con saldo del cliente
     * INCLUYE pagos negativos (ajustes de saldo) como items para balancear
     * @access public
     * @return array
     * @param int $customer_id
     * @param int $outlet_id (opcional, si no se proporciona se obtiene de la sesión)
     */
    public function getPendingSales($customer_id, $outlet_id = null) {
        if (!$customer_id) {
            return [];
        }
        
        if (!$outlet_id) {
            $outlet_id = $this->session->userdata('outlet_id');
        }
        
        $all_items = [];
        
        // 1. Obtener todos los pagos negativos (saldos iniciales)
        $this->db->select('id, reference_no, only_date, amount');
        $this->db->from('tbl_customer_due_receives');
        $this->db->where('customer_id', $customer_id);
        $this->db->where('outlet_id', $outlet_id);
        $this->db->where('del_status', 'Live');
        $this->db->where('amount <', 0);
        $this->db->order_by('only_date', 'ASC');
        
        $negative_payments = $this->db->get()->result();
        
        foreach ($negative_payments as $neg_payment) {
            $original_amount = abs($neg_payment->amount);
            
            // El saldo inicial restante = Original - Lo que se ha pagado a VENTAS REALES
            // NO restamos los pagos totales, solo lo que fue a ventas
            
            // Calcular cuánto se ha abonado a VENTAS de este cliente
            // (esto se registra en tbl_customer_due_receives_sales)
            $this->db->select('COALESCE(SUM(drs.amount), 0) as total_paid_to_sales');
            $this->db->from('tbl_customer_due_receives_sales drs');
            $this->db->join('tbl_customer_due_receives dr', 'dr.id = drs.due_receive_id');
            $this->db->where('dr.customer_id', $customer_id);
            $this->db->where('dr.outlet_id', $outlet_id);
            $this->db->where('dr.del_status', 'Live');
            $total_paid_to_sales = $this->db->get()->row()->total_paid_to_sales;
            
            // Calcular cuánto se ha pagado en total (pagos positivos)
            $this->db->select('COALESCE(SUM(amount), 0) as total_paid');
            $this->db->from('tbl_customer_due_receives');
            $this->db->where('customer_id', $customer_id);
            $this->db->where('outlet_id', $outlet_id);
            $this->db->where('del_status', 'Live');
            $this->db->where('amount >', 0);
            $total_payments = $this->db->get()->row()->total_paid;
            
            // Lo que NO fue a ventas, fue al saldo inicial
            $paid_to_initial_debt = $total_payments - $total_paid_to_sales;
            
            // Saldo inicial restante
            $remaining = max(0, $original_amount - $paid_to_initial_debt);
            
            if ($remaining > 0.01) {
                $all_items[] = [
                    'id' => 'NEG-' . $neg_payment->id,
                    'sale_no' => $neg_payment->reference_no . ' (Saldo Inicial)',
                    'sale_date' => date($this->session->userdata('date_format'), strtotime($neg_payment->only_date)),
                    'total_payable' => $original_amount,
                    'due_amount' => $original_amount,
                    'paid_due_amount' => $paid_to_initial_debt,
                    'remaining_due' => $remaining,
                    'remaining_due_formatted' => getAmtP($remaining),
                    'is_negative_payment' => true,
                    'original_payment_id' => $neg_payment->id,
                    'sort_date' => $neg_payment->only_date
                ];
            }
        }
        
        // 2. Obtener VENTAS con saldo pendiente
        $this->db->select('id, sale_no, sale_date, total_payable, due_amount, paid_due_amount, remaining_due');
        $this->db->from('tbl_sales');
        $this->db->where('customer_id', $customer_id);
        $this->db->where('outlet_id', $outlet_id);
        $this->db->where('del_status', 'Live');
        $this->db->where('order_status', '3');
        $this->db->where('remaining_due >', 0);
        $this->db->order_by('sale_date', 'ASC');
        
        $sales = $this->db->get()->result();
        
        foreach ($sales as $sale) {
            $all_items[] = [
                'id' => $sale->id,
                'sale_no' => $sale->sale_no,
                'sale_date' => date($this->session->userdata('date_format'), strtotime($sale->sale_date)),
                'total_payable' => $sale->total_payable,
                'due_amount' => $sale->due_amount,
                'paid_due_amount' => $sale->paid_due_amount,
                'remaining_due' => $sale->remaining_due,
                'remaining_due_formatted' => getAmtP($sale->remaining_due),
                'is_negative_payment' => false,
                'sort_date' => $sale->sale_date
            ];
        }
        
        // Ordenar todos los items por fecha
        usort($all_items, function($a, $b) {
            return strtotime($a['sort_date']) - strtotime($b['sort_date']);
        });
        
        return $all_items;
    }

}

