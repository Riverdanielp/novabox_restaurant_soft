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
  # This is Promotion_model Model
  ###########################################################
 */
class Promotion_model extends CI_Model {

    /**
     * get Ingredient List
     * @access public
     * @return object
     * @param no
     */
    public function getIngredientList() {
        $outlet_id = $this->session->userdata('outlet_id');
        $company_id = $this->session->userdata('company_id');
        $this->db->select("tbl_ingredients.id, tbl_ingredients.name, tbl_ingredients.code, tbl_purchase_ingredients.unit_price as purchase_price, tbl_units.unit_name");
        $this->db->from("tbl_ingredients");
        $this->db->join("tbl_purchase_ingredients", 'tbl_purchase_ingredients.ingredient_id = tbl_ingredients.id', 'left');
        $this->db->join("tbl_units", 'tbl_units.id = tbl_ingredients.unit_id', 'left');
        $this->db->order_by("tbl_ingredients.name", "ASC");
        $this->db->where("tbl_ingredients.company_id", $company_id);
        $this->db->where("tbl_purchase_ingredients.del_status", 'Live');
        $this->db->where("tbl_purchase_ingredients.outlet_id", $outlet_id);
        $result = $this->db->get()->result();
        return $result;
    }
    /**
     * get Food Menu List
     * @access public
     * @return object
     * @param no
     */
    public function getFoodMenuList() {
        $outlet_id = $this->session->userdata('outlet_id');
        $company_id = $this->session->userdata('company_id');
        $this->db->select("*");
        $this->db->from("tbl_food_menus");

        $this->db->order_by("tbl_food_menus.name", "ASC");
        $this->db->where("tbl_food_menus.company_id", $company_id);
        $this->db->where("tbl_food_menus.del_status", 'Live');

        $result = $this->db->get()->result();
        return $result;
    }
    public function checkPromotionWithinDate($start_date,$end_date,$food_menu_id) {
        $outlet_id = $this->session->userdata('outlet_id');

        $this->db->select('*');
        $this->db->from('tbl_promotions');
        if ($start_date != '' && $end_date != '') {
            $this->db->where('start_date>=', $start_date);
            $this->db->where('start_date <=', $end_date);
        }
        $this->db->where('food_menu_id', $food_menu_id);

        $this->db->where('outlet_id', $outlet_id);
        $this->db->where("del_status", 'Live');
        $query_result = $this->db->get();
        $result = $query_result->result();

        if(isset($result) && $result){
            return $result;
        }

        $this->db->select('*');
        $this->db->from('tbl_promotions');
        if ($start_date != '' && $end_date != '') {
            $this->db->where('end_date>=', $start_date);
            $this->db->where('end_date <=', $end_date);
        }
        $this->db->where('food_menu_id', $food_menu_id);
        $this->db->where('outlet_id', $outlet_id);
        $this->db->where("del_status", 'Live');
        $query_result = $this->db->get();
        $result = $query_result->result();

        return $result;
    }
    /**
     * get Promotion Ingredients
     * @access public
     * @return object
     * @param int
     */
    public function getPromotionIngredients($id) {
        $this->db->select("*");
        $this->db->from("tbl_promotion_ingredients");
        $this->db->order_by('id', 'ASC');
        $this->db->where("promotion_id", $id);
        $this->db->where("del_status", 'Live');
        return $this->db->get()->result();
    }
    /**
     * generate Promotion Ref No
     * @access public
     * @return object
     * @param int
     */
    public function generatePromotionRefNo($outlet_id) {
        $promotion_count = $this->db->query("SELECT count(id) as promotion_count
               FROM tbl_promotions where outlet_id=$outlet_id")->row('promotion_count');
        $ingredient_code = str_pad($promotion_count + 1, 6, '0', STR_PAD_LEFT);
        return $ingredient_code;
    }

}

