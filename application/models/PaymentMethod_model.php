<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PaymentMethod_model extends CI_Model {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Get all payment methods by company
     * @param int $company_id
     * @return array
     */
    public function getAllPaymentMethods($company_id) {
        $this->db->select('*');
        $this->db->from('tbl_payment_methods');
        $this->db->where('company_id', $company_id);
        $this->db->where('del_status', 'Live');
        $this->db->order_by('id', 'ASC');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return array();
    }

    /**
     * Get payment method by ID
     * @param int $payment_id
     * @return object|null
     */
    public function getPaymentMethodById($payment_id) {
        $this->db->select('*');
        $this->db->from('tbl_payment_methods');
        $this->db->where('id', $payment_id);
        $this->db->where('del_status', 'Live');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return null;
    }

    /**
     * Get active payment methods
     * @param int $company_id
     * @return array
     */
    public function getActivePaymentMethods($company_id) {
        $this->db->select('*');
        $this->db->from('tbl_payment_methods');
        $this->db->where('company_id', $company_id);
        $this->db->where('del_status', 'Live');
        $this->db->order_by('id', 'ASC');
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return array();
    }
}
