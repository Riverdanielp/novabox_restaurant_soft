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

}

