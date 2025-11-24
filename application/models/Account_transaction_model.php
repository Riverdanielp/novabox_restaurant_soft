<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Account_transaction_model extends CI_Model {

    /**
     * Obtener todas las transacciones de una empresa
     */
    public function getAllTransactionsByCompany($company_id, $filters = array()) {
        $this->db->select('t.*, 
                          fa.account_name as from_account_name, 
                          ta.account_name as to_account_name,
                          u.full_name as user_name');
        $this->db->from('tbl_account_transactions t');
        $this->db->join('tbl_accounts fa', 't.from_account_id = fa.id', 'left');
        $this->db->join('tbl_accounts ta', 't.to_account_id = ta.id', 'left');
        $this->db->join('tbl_users u', 't.user_id = u.id', 'left');
        $this->db->where('t.company_id', $company_id);
        $this->db->where('t.del_status', 'Live');

        // Aplicar filtros
        if (isset($filters['date_from']) && $filters['date_from']) {
            $this->db->where('t.transaction_date >=', $filters['date_from']);
        }
        if (isset($filters['date_to']) && $filters['date_to']) {
            $this->db->where('t.transaction_date <=', $filters['date_to']);
        }
        if (isset($filters['account_id']) && $filters['account_id']) {
            $this->db->where('(t.from_account_id = ' . $filters['account_id'] . ' OR t.to_account_id = ' . $filters['account_id'] . ')');
        }
        if (isset($filters['transaction_type']) && $filters['transaction_type']) {
            $this->db->where('t.transaction_type', $filters['transaction_type']);
        }

        $this->db->order_by('t.created_at', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Obtener transacción por ID
     */
    public function getTransactionById($id) {
        $this->db->select('t.*, 
                          fa.account_name as from_account_name, 
                          ta.account_name as to_account_name,
                          u.full_name as user_name');
        $this->db->from('tbl_account_transactions t');
        $this->db->join('tbl_accounts fa', 't.from_account_id = fa.id', 'left');
        $this->db->join('tbl_accounts ta', 't.to_account_id = ta.id', 'left');
        $this->db->join('tbl_users u', 't.user_id = u.id', 'left');
        $this->db->where('t.id', $id);
        $this->db->where('t.del_status', 'Live');
        return $this->db->get()->row();
    }

    /**
     * Crear nueva transacción
     */
    public function insertTransaction($data) {
        $this->db->trans_start();
        
        // Insertar transacción
        $this->db->insert('tbl_account_transactions', $data);
        $transaction_id = $this->db->insert_id();

        // Actualizar saldos de cuentas
        if (isset($data['from_account_id']) && $data['from_account_id']) {
            $this->db->set('current_balance', 'current_balance - ' . $data['amount'], FALSE);
            $this->db->where('id', $data['from_account_id']);
            $this->db->update('tbl_accounts');
        }

        if (isset($data['to_account_id']) && $data['to_account_id']) {
            $this->db->set('current_balance', 'current_balance + ' . $data['amount'], FALSE);
            $this->db->where('id', $data['to_account_id']);
            $this->db->update('tbl_accounts');
        }

        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            return false;
        }
        return $transaction_id;
    }

    /**
     * Eliminar transacción (soft delete) y revertir saldos
     */
    public function deleteTransaction($id) {
        $this->db->trans_start();

        // Obtener transacción
        $transaction = $this->getTransactionById($id);
        
        if ($transaction) {
            // Revertir saldos
            if ($transaction->from_account_id) {
                $this->db->set('current_balance', 'current_balance + ' . $transaction->amount, FALSE);
                $this->db->where('id', $transaction->from_account_id);
                $this->db->update('tbl_accounts');
            }

            if ($transaction->to_account_id) {
                $this->db->set('current_balance', 'current_balance - ' . $transaction->amount, FALSE);
                $this->db->where('id', $transaction->to_account_id);
                $this->db->update('tbl_accounts');
            }

            // Marcar como eliminada
            $data = array('del_status' => 'Deleted');
            $this->db->where('id', $id);
            $this->db->update('tbl_account_transactions', $data);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /**
     * Obtener transacciones de una cuenta específica
     */
    public function getTransactionsByAccount($account_id, $date_from = NULL, $date_to = NULL) {
        $this->db->select('t.*, 
                          fa.account_name as from_account_name, 
                          ta.account_name as to_account_name,
                          u.full_name as user_name');
        $this->db->from('tbl_account_transactions t');
        $this->db->join('tbl_accounts fa', 't.from_account_id = fa.id', 'left');
        $this->db->join('tbl_accounts ta', 't.to_account_id = ta.id', 'left');
        $this->db->join('tbl_users u', 't.user_id = u.id', 'left');
        $this->db->where('(t.from_account_id = ' . $account_id . ' OR t.to_account_id = ' . $account_id . ')');
        $this->db->where('t.del_status', 'Live');

        if ($date_from) {
            $this->db->where('t.transaction_date >=', $date_from);
        }
        if ($date_to) {
            $this->db->where('t.transaction_date <=', $date_to);
        }

        $this->db->order_by('t.created_at', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Registrar movimiento automático (ventas, compras, gastos, etc.)
     */
    public function registerAutomaticTransaction($type, $account_id, $amount, $reference_type, $reference_id, $company_id, $user_id, $note = '') {
        $data = array(
            'transaction_type' => $type,
            'from_account_id' => ($type == 'Gasto' || $type == 'Compra' || $type == 'Pago Proveedor' || $type == 'Retiro') ? $account_id : NULL,
            'to_account_id' => ($type == 'Venta' || $type == 'Cobro Cliente' || $type == 'Deposito' || $type == 'Cierre Caja') ? $account_id : NULL,
            'amount' => $amount,
            'reference_type' => $reference_type,
            'reference_id' => $reference_id,
            'note' => $note,
            'transaction_date' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'company_id' => $company_id,
            'user_id' => $user_id,
            'del_status' => 'Live'
        );

        return $this->insertTransaction($data);
    }

    /**
     * Obtener resumen de movimientos por tipo
     */
    public function getTransactionSummary($company_id, $date_from = NULL, $date_to = NULL, $account_id = NULL) {
        $this->db->select('transaction_type, SUM(amount) as total_amount, COUNT(*) as total_transactions');
        $this->db->from('tbl_account_transactions');
        $this->db->where('company_id', $company_id);
        $this->db->where('del_status', 'Live');

        if ($date_from) {
            $this->db->where('transaction_date >=', $date_from);
        }
        if ($date_to) {
            $this->db->where('transaction_date <=', $date_to);
        }
        if ($account_id) {
            $this->db->where('(from_account_id = ' . $account_id . ' OR to_account_id = ' . $account_id . ')');
        }

        $this->db->group_by('transaction_type');
        return $this->db->get()->result();
    }

    /**
     * Obtener saldo de una cuenta en una fecha específica
     */
    public function getAccountBalanceAtDate($account_id, $date) {
        // Obtener saldo inicial de la cuenta
        $this->db->select('opening_balance');
        $this->db->from('tbl_accounts');
        $this->db->where('id', $account_id);
        $account = $this->db->get()->row();
        
        if (!$account) {
            return 0;
        }

        $balance = $account->opening_balance;

        // Sumar entradas hasta la fecha
        $this->db->select('SUM(amount) as total_in');
        $this->db->from('tbl_account_transactions');
        $this->db->where('to_account_id', $account_id);
        $this->db->where('transaction_date <=', $date);
        $this->db->where('del_status', 'Live');
        $in = $this->db->get()->row();
        
        if ($in && $in->total_in) {
            $balance += $in->total_in;
        }

        // Restar salidas hasta la fecha
        $this->db->select('SUM(amount) as total_out');
        $this->db->from('tbl_account_transactions');
        $this->db->where('from_account_id', $account_id);
        $this->db->where('transaction_date <=', $date);
        $this->db->where('del_status', 'Live');
        $out = $this->db->get()->row();
        
        if ($out && $out->total_out) {
            $balance -= $out->total_out;
        }

        return $balance;
    }
}
