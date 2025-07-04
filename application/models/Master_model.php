<?php

class Master_model extends CI_Model {

     /**
     * generate Ingredient Code
     * @access public
     * @return object
     * @param no
     */
    public function generateIngredientCode() {
        $company_id = $this->session->userdata('company_id');
        $ingredient_count = $this->db->query("SELECT count(id) as ingredient_count
               FROM tbl_ingredients where company_id='$company_id'")->row('ingredient_count');
        $ingredient_code = str_pad($ingredient_count + 1, 3, '0', STR_PAD_LEFT);
        return $ingredient_code;
    }
     /**
     * generate Food Menu Code
     * @access public
     * @return object
     * @param no
     */
    public function generateFoodMenuCode() {
        $company_id = $this->session->userdata('company_id');
        $food_menu_count = $this->db->query("SELECT count(id) as food_menu_count
             FROM tbl_food_menus where company_id='$company_id'")->row('food_menu_count');
        $food_menu_code = str_pad($food_menu_count + 1, 3, '0', STR_PAD_LEFT);
        return $food_menu_code;
    }
     /**
     * get Ingredient List With Unit
     * @access public
     * @return object
     * @param int
     */
    public function getIngredientListWithUnit($company_id) {
        $result = $this->db->query("SELECT tbl_ingredients.id, tbl_ingredients.name, tbl_ingredients.code, tbl_units.unit_name,tbl_ingredients.purchase_price,tbl_ingredients.consumption_unit_cost 
          FROM tbl_ingredients 
          JOIN tbl_units ON tbl_ingredients.unit_id = tbl_units.id
          WHERE tbl_ingredients.company_id=$company_id AND tbl_ingredients.del_status = 'Live'  
          ORDER BY tbl_ingredients.name ASC")->result();
        return $result;
    }
     /**
     * get Food Menu Ingredients
     * @access public
     * @return object
     * @param int
     */
    public function getFoodMenuIngredients($id) {
        $this->db->select("*");
        $this->db->from("tbl_food_menus_ingredients");
        $this->db->order_by('id', 'ASC');
        $this->db->where("food_menu_id", $id);
        $this->db->where("del_status", 'Live');
        return $this->db->get()->result();
    }
     /**
     * get Modifier Ingredients
     * @access public
     * @return object
     * @param int
     */
    public function getModifierIngredients($id) {
        $this->db->select("*");
        $this->db->from("tbl_modifier_ingredients");
        $this->db->order_by('id', 'ASC');
        $this->db->where("modifier_id", $id);
        $this->db->where("del_status", 'Live');
        return $this->db->get()->result();
    }
     /**
     * get Food Menu Modifiers
     * @access public
     * @return object
     * @param int
     */
    public function getFoodMenuModifiers($id) {
        $this->db->select("*");
        $this->db->from("tbl_food_menus_modifiers");
        $this->db->where("food_menu_id", $id);
        $this->db->where("del_status", 'Live');
        $this->db->order_by('id', 'ASC');
        return $this->db->get()->result();
    }
     /**
     * mulit featured photo
     * @access public
     * @return object
     * @param string
     * @param string
     */
    public function mulit_featured_photo($width = '', $sessionName = '') {
        $config['upload_path'] = './assets/uploads/';
        $config['allowed_types'] = 'jpg|jpeg|png';
        $config['max_size'] = '2048';
        $config['encrypt_name'] = TRUE;
        $config['detect_mime'] = TRUE;
        $this->load->library('upload', $config);
        $this->load->library('image_lib', $config);
        if ($this->upload->do_upload("featured_photo")) {
            $upload_info = $this->upload->data();
            $pc_thumb = $upload_info['file_name'];
            $config['image_library'] = 'gd2';
            $config['source_image'] = './assets/uploads/' . $pc_thumb;
            $config['maintain_ratio'] = TRUE;
            $config['width'] = $width;
            $this->image_lib->clear();
            $this->image_lib->initialize($config);
            $this->image_lib->resize();
            $this->session->set_userdata($sessionName, $pc_thumb);
        }
    }
     /**
     * get Image Upload Configuration
     * @access public
     * @return object
     * @param no
     */
    public function getImageUploadConfiguaration(){
        // $config['upload_path']       =       $this->gallery_path.'/upload';
        $config['upload_path']      =       realpath(APPPATH.'../assets').'POS';
        $config['allowed_types']    =       'gif|png|jpg|jpeg';
        $config['max_size']         =       '6000';
        $config['max_width']        =       '0';
        $config['max_height']       =       '0';
        return $config;
    }
     /**
     * upload Image
     * @access public
     * @return object
     * @param array
     * @param string
     */
    public function uploadImage($config,$photoFieldName=''){
        $this->load->library('upload');
        $this->upload->initialize($config);
        $photoFieldName=($photoFieldName!='')?$photoFieldName:'photo';
        if(!$this->upload->do_upload($photoFieldName)){
            $error      =       array(
                'error'     =>      $this->upload->display_error()
            );
            echo $error['error'];
        }else{
            return $this->upload->data();           
        }
    }
    public function getPremadeIngredients($id) {
        $this->db->select("*");
        $this->db->from("tbl_premade_ingredients");
        $this->db->order_by('id', 'ASC');
        $this->db->where("pre_made_id", $id);
        $this->db->where("del_status", 'Live');
        return $this->db->get()->result();
    }
    
    public function get_food_menus_without_ingredients_grouped_by_category()
    {
        $this->db->select('fm.*, fmc.category_name');
        $this->db->from('tbl_food_menus fm');
        $this->db->join('tbl_food_menu_categories fmc', 'fmc.id = fm.category_id AND fmc.del_status = "Live"', 'left');
        $this->db->where('fm.del_status', 'Live');
        $this->db->where('(
            SELECT COUNT(*) FROM tbl_food_menus_ingredients fmi
            WHERE fmi.food_menu_id = fm.id AND fmi.del_status = "Live"
        ) =', 0, FALSE);
        // No tiene ingrediente hermano en tbl_ingredients
        $this->db->where('(
            SELECT COUNT(*) FROM tbl_ingredients ti
            WHERE ti.food_id = fm.id AND ti.del_status = "Live"
        ) =', 0, FALSE);
        $this->db->order_by('fmc.order_by', 'ASC');
        $this->db->order_by('fmc.category_name', 'ASC');
        $this->db->order_by('fm.name', 'ASC');
        $query = $this->db->get();
        $result = $query->result();

        // Agrupar por categoría
        $grouped = [];
        foreach ($result as $row) {
            $cat = $row->category_name ?: 'Sin Categoría';
            $grouped[$cat][] = $row;
        }
        return $grouped;
    }
    

}

