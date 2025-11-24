<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Account_model extends CI_Model {

    /**
     * Obtener todas las cuentas activas de una empresa
     */
    public function getAllAccountsByCompany($company_id) {
        $this->db->select('*');
        $this->db->from('tbl_accounts');
        $this->db->where('company_id', $company_id);
        $this->db->where('del_status', 'Live');
        $this->db->order_by('is_default', 'DESC');
        $this->db->order_by('account_name', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Obtener cuenta por ID
     */
    public function getAccountById($id) {
        $this->db->select('*');
        $this->db->from('tbl_accounts');
        $this->db->where('id', $id);
        $this->db->where('del_status', 'Live');
        return $this->db->get()->row();
    }

    /**
     * Obtener cuenta principal (Caja Cofre)
     */
    public function getDefaultAccount($company_id) {
        $this->db->select('*');
        $this->db->from('tbl_accounts');
        $this->db->where('company_id', $company_id);
        $this->db->where('is_default', 1);
        $this->db->where('del_status', 'Live');
        return $this->db->get()->row();
    }

    /**
     * Crear o actualizar cuenta
     */
    public function insertOrUpdateAccount($data, $id = NULL) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->update('tbl_accounts', $data);
        } else {
            return $this->db->insert('tbl_accounts', $data);
        }
    }

    /**
     * Eliminar cuenta (soft delete)
     */
    public function deleteAccount($id) {
        $data = array('del_status' => 'Deleted');
        $this->db->where('id', $id);
        return $this->db->update('tbl_accounts', $data);
    }

    /**
     * Actualizar saldo de una cuenta
     */
    public function updateBalance($account_id, $new_balance) {
        $data = array('current_balance' => $new_balance);
        $this->db->where('id', $account_id);
        return $this->db->update('tbl_accounts', $data);
    }

    /**
     * Incrementar saldo de una cuenta
     */
    public function incrementBalance($account_id, $amount) {
        $this->db->set('current_balance', 'current_balance + ' . $amount, FALSE);
        $this->db->where('id', $account_id);
        return $this->db->update('tbl_accounts');
    }

    /**
     * Decrementar saldo de una cuenta
     */
    public function decrementBalance($account_id, $amount) {
        $this->db->set('current_balance', 'current_balance - ' . $amount, FALSE);
        $this->db->where('id', $account_id);
        return $this->db->update('tbl_accounts');
    }

    /**
     * Verificar si existe una cuenta predeterminada
     */
    public function hasDefaultAccount($company_id) {
        $this->db->select('id');
        $this->db->from('tbl_accounts');
        $this->db->where('company_id', $company_id);
        $this->db->where('is_default', 1);
        $this->db->where('del_status', 'Live');
        return $this->db->count_all_results() > 0;
    }

    /**
     * Crear cuenta Caja Cofre si no existe
     */
    public function createDefaultAccountIfNotExists($company_id, $user_id) {
        if (!$this->hasDefaultAccount($company_id)) {
            $data = array(
                'account_name' => 'Caja Cofre',
                'account_type' => 'Caja',
                'opening_balance' => 0.00,
                'current_balance' => 0.00,
                'is_default' => 1,
                'description' => 'Cuenta principal para recibir efectivo de cierres de caja',
                'status' => 'Active',
                'company_id' => $company_id,
                'user_id' => $user_id,
                'added_date' => date('Y-m-d H:i:s'),
                'del_status' => 'Live'
            );
            return $this->db->insert('tbl_accounts', $data);
        }
        return true;
    }

    /**
     * Obtener cuentas activas para dropdown
     */
    public function getAccountsForDropdown($company_id, $exclude_id = NULL) {
        $this->db->select('id, account_name, account_type, current_balance');
        $this->db->from('tbl_accounts');
        $this->db->where('company_id', $company_id);
        $this->db->where('status', 'Active');
        $this->db->where('del_status', 'Live');
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        $this->db->order_by('is_default', 'DESC');
        $this->db->order_by('account_name', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Verificar si la cuenta se puede eliminar
     */
    public function canDeleteAccount($id) {
        // No permitir eliminar si es cuenta predeterminada
        $account = $this->getAccountById($id);
        if ($account && $account->is_default == 1) {
            return false;
        }
        
        // Verificar si tiene transacciones
        $this->db->from('tbl_account_transactions');
        $this->db->where('(from_account_id = ' . $id . ' OR to_account_id = ' . $id . ')');
        $this->db->where('del_status', 'Live');
        $count = $this->db->count_all_results();
        
        return $count == 0;
    }
}
