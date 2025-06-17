<?php

class Food_menu_model extends CI_Model {
    public function __construct(){
        parent::__construct();
        if ($this->session->has_userdata('language')) {
            $language = $this->session->userdata('language');
        }else{
            $language = 'english';
        }
        $this->lang->load("$language", "$language");
        $this->config->set_item('language', $language);
    }
    
    public function get_datatables($company_id, $category_id, $start, $length, $search) {
        $this->db->from('tbl_food_menus');
        $this->db->where('company_id', $company_id);
        $this->db->where('parent_id', '0');
        $this->db->where('del_status', 'Live');
        if ($category_id) {
            $this->db->where('category_id', $category_id);
        }
        if ($search) {
            $this->db->group_start()
                ->like('name', $search)
                ->or_like('code', $search)
                ->group_end();
        }
        $order_column_index = $this->input->get('order')[0]['column'];
        $order_direction = $this->input->get('order')[0]['dir'];
        
        // Define los nombres de columnas según el orden de tu datatable
        $order_columns = [
            0 => 'id', // o el campo correspondiente
            1 => 'photo',
            2 => 'code',
            3 => 'name',
            4 => 'category_id',
            5 => 'sale_price',
            // 6 => 'id', // alternative_name, cambia si tienes el campo
            6 => 'description',
            // 8 => acciones (no ordenar)
        ];
        
        // Por defecto si no existe
        $order_by_col = isset($order_columns[$order_column_index]) ? $order_columns[$order_column_index] : 'id';
        $order_by_dir = in_array($order_direction, ['asc', 'desc']) ? $order_direction : 'desc';
        
        $this->db->order_by($order_by_col, $order_by_dir);
        if ($length != -1) {
            $this->db->limit($length, $start);
        }
        return $this->db->get()->result();
    }
    
    public function count_all($company_id, $category_id) {
        $this->db->from('tbl_food_menus');
        $this->db->where('company_id', $company_id);
        $this->db->where('parent_id', '0');
        $this->db->where('del_status', 'Live');
        if ($category_id) {
            $this->db->where('category_id', $category_id);
        }
        return $this->db->count_all_results();
    }
    
    public function count_filtered($company_id, $category_id, $search) {
        $this->db->from('tbl_food_menus');
        $this->db->where('company_id', $company_id);
        $this->db->where('parent_id', '0');
        $this->db->where('del_status', 'Live');
        if ($category_id) {
            $this->db->where('category_id', $category_id);
        }
        if ($search) {
            $this->db->group_start()
                ->like('name', $search)
                ->or_like('code', $search)
                ->group_end();
        }
        return $this->db->count_all_results();
    }

}

?>
