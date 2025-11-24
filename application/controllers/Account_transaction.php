<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CONTROLADOR DEPRECATED - Todas las funciones han sido migradas a Account.php
 * Este controlador existe solo por compatibilidad hacia atrás
 * Las nuevas rutas deben usar Account en lugar de Account_transaction
 */
class Account_transaction extends Cl_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Account_transaction_model');
        $this->load->model('Account_model');
        $this->load->library('form_validation');
        
        if (!$this->session->userdata('user_id')) {
            redirect('Authentication/index');
        }
    }

    /**
     * DEPRECATED - Usar Account/accountTransactions
     */
    public function accountTransactions() {
        redirect('Account/accountTransactions');
    }

    /**
     * DEPRECATED - Usar Account/addAccountTransaction
     */
    public function addAccountTransaction() {
        redirect('Account/addAccountTransaction');
    }

    /**
     * DEPRECATED - Usar Account/saveTransaction
     */
    public function saveTransaction() {
        redirect('Account/addAccountTransaction');
    }

    /**
     * DEPRECATED - Usar Account/deleteTransaction
     */
    public function deleteTransaction($encrypted_id) {
        redirect('Account/deleteTransaction/' . $encrypted_id);
    }

    /**
     * DEPRECATED - Usar Account/viewTransactionDetails
     */
    public function viewTransactionDetails($encrypted_id) {
        redirect('Account/viewTransactionDetails/' . $encrypted_id);
    }

    /**
     * DEPRECATED - Usar Account/getTransactionsSummary
     */
    public function getTransactionsSummary() {
        redirect('Account/getTransactionsSummary');
    }

    /**
     * DEPRECATED - Usar Account/exportTransactions
     */
    public function exportTransactions() {
        redirect('Account/exportTransactions');
    }

    /**
     * DEPRECATED - Usar Account/registerCloseToCofre
     */
    public function registerCloseToCofre() {
        redirect('Account/registerCloseToCofre');
    }
}
