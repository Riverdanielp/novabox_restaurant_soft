<?php

class Inventory_adjustment_model extends CI_Model {

     /**
     * get Ingredient List
     * @access public
     * @return object
     * @param no
     */
    public function getIngredientList() {
        $company_id = $this->session->userdata('company_id');
        $this->db->select("tbl_ingredients.id, tbl_ingredients.name, tbl_ingredients.code, tbl_units.unit_name");
        $this->db->from("tbl_ingredients"); 
        $this->db->join("tbl_units", 'tbl_units.id = tbl_ingredients.unit_id', 'left');
        $this->db->where("tbl_ingredients.company_id", $company_id);
        $this->db->where("tbl_ingredients.del_status", 'Live'); 
        $this->db->order_by("tbl_ingredients.name", "ASC");
        $result = $this->db->get()->result();
        return $result;
    }
     /**
     * get Inventory Adjustment Ingredients
     * @access public
     * @return object
     * @param int
     */
    public function getInventoryAdjustmentIngredients($id) {
        $this->db->select("tbl_inventory_adjustment_ingredients.*, tbl_ingredients.name, tbl_ingredients.code");
        $this->db->from("tbl_inventory_adjustment_ingredients");
        $this->db->join('tbl_ingredients', 'tbl_ingredients.id = tbl_inventory_adjustment_ingredients.ingredient_id', 'left');
        $this->db->order_by('tbl_inventory_adjustment_ingredients.id', 'DESC');
        $this->db->where("tbl_inventory_adjustment_ingredients.inventory_adjustment_id", $id);
        $this->db->where("tbl_inventory_adjustment_ingredients.del_status", 'Live');
        return $this->db->get()->result();
    }
     /**
     * generate Reference No
     * @access public
     * @return string
     * @param int
     */
    public function generateReferenceNo($outlet_id) {
        $inventory_adjustment_count = $this->db->query("SELECT count(id) as inventory_adjustment_count
               FROM tbl_inventory_adjustment where outlet_id=$outlet_id")->row('inventory_adjustment_count');
        $reference_no = str_pad($inventory_adjustment_count + 1, 6, '0', STR_PAD_LEFT);
        return $reference_no;
    }
    public function getIngredientByCode($code) {
        return $this->db->where('code', $code)->where('del_status', 'Live')->get('tbl_ingredients')->row();
    }
    
    public function getLastPurchasePrice($ingredient_id) {
        $row = $this->db->select('purchase_price')->where('ingredient_id', $ingredient_id)->where('del_status', 'Live')->order_by('id', 'DESC')->get('tbl_purchase_ingredients')->row();
        return $row ? $row->purchase_price : 0;
    }

    public function buscarIngredientesPorNombre($term) {
        $company_id = $this->session->userdata('company_id');
        $this->db->select("tbl_ingredients.id, tbl_ingredients.name, tbl_ingredients.code, tbl_units.unit_name");
        $this->db->from("tbl_ingredients");
        $this->db->join("tbl_units", 'tbl_units.id = tbl_ingredients.unit_id', 'left');
        $this->db->where("tbl_ingredients.company_id", $company_id);
        $this->db->where("tbl_ingredients.del_status", 'Live');
        $this->db->group_start();
        $this->db->like("tbl_ingredients.name", $term);
        $this->db->or_like("tbl_ingredients.code", $term);
        $this->db->group_end();
        $this->db->order_by("tbl_ingredients.name", "ASC");
        return $this->db->get()->result();
    }
}

