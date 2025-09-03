<?php

class Sale_model extends CI_Model {

    /**
     * get Sale List
     * @access public
     * @return object
     * @param int
     */
    public function getSaleList($outlet_id) {
        $result = $this->db->query("SELECT s.*,u.full_name,c.name as customer_name,m.name,c.phone as customer_phone
          FROM tbl_sales s
          INNER JOIN tbl_customers c ON(s.customer_id=c.id)
          LEFT JOIN tbl_users u ON(s.user_id=u.id)
          LEFT JOIN tbl_payment_methods m ON(s.payment_method_id=m.id) 
          WHERE s.order_status = '3' AND s.del_status = 'Live' AND s.outlet_id=$outlet_id ORDER BY s.id DESC")->result();
        return $result;
    }
    /**
     * get Item Menu Categories
     * @access public
     * @return object
     * @param int
     */
    public function getItemMenuCategories($company_id) {
        $result = $this->db->query("SELECT * 
          FROM tbl_ingredient_categories 
          WHERE company_id=$company_id AND del_status = 'Live'  
          ORDER BY category_name");
        $result = $this->db->get();

        if($result != false){
          return $result->result();
        }else{
          return false;
        }
    }
    /**
     * export Daily Sales
     * @access public
     * @return object
     * @param no
     */
    public function exportDailySale() {
        $outlet_id = $this->session->userdata('outlet_id');
        $this->db->select('tbl_sales.*,tbl_users.full_name,tbl_payment_methods.name,tbl_customers.name as customer_name');
        $this->db->from('tbl_sales');
        $this->db->join('tbl_users', 'tbl_users.id = tbl_sales.user_id', 'left');
        $this->db->join('tbl_customers', 'tbl_customers.id = tbl_sales.customer_id', 'left');
        $this->db->join('tbl_payment_methods', 'tbl_payment_methods.id = tbl_sales.payment_method_id', 'left');
        $this->db->where('order_status', '3');
        $this->db->where('tbl_sales.outlet_id', $outlet_id);
        $this->db->where('tbl_sales.del_status', 'Live');
        $this->db->order_by('sale_date', 'ASC');
        $query_result = $this->db->get();
        $result = $query_result->result();
        return $result;
    }
    /**
     * get Item Menus
     * @access public
     * @return object
     * @param int
     */
    public function getItemMenus($outlet_id) {
        $result = $this->db->query("SELECT * 
          FROM tbl_ingredients 
          WHERE outlet_id=$outlet_id AND del_status = 'Live'  
          ORDER BY name");
        $result = $this->db->get();

        if($result != false){
          return $result->result();
        }else{
          return false;
        }
    }
    /**
     * get Item List With Unit
     * @access public
     * @return object
     * @param int
     */
    public function getItemListWithUnit($outlet_id) {
        $result = $this->db->query("SELECT tbl_ingredients.id, tbl_ingredients.name, tbl_units.unit_name 
          FROM tbl_ingredients 
          JOIN tbl_units ON tbl_ingredients.unit_id = tbl_units.id
          WHERE outlet_id=$outlet_id AND tbl_ingredients.del_status = 'Live'  
          ORDER BY tbl_ingredients.name ASC");
        $result = $this->db->get();

        if($result != false){
          return $result->result();
        }else{
          return false;
        }
    }
    /**
     * get Food Menu Ingredients
     * @access public
     * @return object
     * @param int
     */
    public function getFoodMenuIngredients($id) {
        $outlet_id = $this->session->userdata('outlet_id');
        $this->db->select("*");
        $this->db->from("tbl_food_menus_ingredients");
        $this->db->order_by('id', 'ASC');
        $this->db->where("food_menu_id", $id);
        $this->db->where("del_status", 'Live');
        $result = $this->db->get();

        if($result != false){
          return $result->result();
        }else{
          return false;
        }
    }
    /**
     * get Item Menu Items
     * @access public
     * @return object
     * @param int
     */
    public function getItemMenuItems($id) {
        $outlet_id = $this->session->userdata('outlet_id');
        $this->db->select("*");
        $this->db->from("tbl_ingredients_items");
        $this->db->order_by('id', 'ASC');
        $this->db->where("ingredient_id", $id);
        $this->db->where("outlet_id", $outlet_id);
        $this->db->where("del_status", 'Live');
        $result = $this->db->get();

        if($result != false){
          return $result->result();
        }else{
          return false;
        };
    }
    /**
     * get All Item menus
     * @access public
     * @return object
     * @param no
     */
    public function getAllItemmenus() {
        $company_id = $this->session->userdata('company_id');
        $result = $this->db->query("SELECT tbl_food_menus.id, tbl_food_menus.code, tbl_food_menus.name, tbl_food_menus.sale_price, tbl_food_menus.photo, tbl_food_menu_categories.category_name
          FROM tbl_food_menus 
          LEFT JOIN tbl_food_menu_categories ON tbl_food_menus.category_id = tbl_food_menu_categories.id
          WHERE tbl_food_menus.company_id=$company_id AND tbl_food_menus.del_status = 'Live' AND parent_id =  '0' 
          ORDER BY tbl_food_menus.name ASC");
        $result = $this->db->get();

        if($result != false){
            return $result->result();
        }else{
            return false;
        }
    }
    /**
     * get Food Menu Categories
     * @access public
     * @return object
     * @param int
     */
    public function getFoodMenuCategories($company_id) {
        $this->db->select("*");
        $this->db->from("tbl_food_menu_categories");
        $this->db->where("company_id", $company_id);
        $this->db->where("del_status", 'Live');
        $this->db->order_by("category_name", 'ASC');
        $result = $this->db->get();

        if($result != false){
          return $result->result();
        }else{
          return false;
        }
    }
    /**
     * get Sale Info
     * @access public
     * @return object
     * @param int
     */
    public function getSaleInfo($sales_id) {
        $outlet_id = $this->session->userdata('outlet_id');
        $result = $this->db->query("SELECT s.*,u.full_name,c.name as customer_name,m.name,tbl.name as table_name
          FROM tbl_sales s
          INNER JOIN tbl_customers c ON(s.customer_id=c.id)
          LEFT JOIN tbl_users u ON(s.user_id=u.id)
          LEFT JOIN tbl_payment_methods m ON(s.payment_method_id=m.id)
          LEFT JOIN tbl_tables tbl ON(s.table_id=tbl.id) 
          WHERE s.id=$sales_id AND s.del_status = 'Live' AND s.outlet_id=$outlet_id")->row();
        return $result;
    }
    /**
     * get Sale Details
     * @access public
     * @return boolean
     * @param int
     */
    public function getSaleDetails($sales_id) {
        $outlet_id = $this->session->userdata('outlet_id');
        $result = $this->db->query("SELECT sd.*,fm.code
          FROM tbl_sales_details sd
          LEFT JOIN tbl_food_menus fm ON(sd.food_menu_id=fm.id)
          WHERE sd.sales_id=$sales_id AND sd.outlet_id=$outlet_id AND sd.del_status = 'Live'  
          ORDER BY sd.id ASC");
        $result = $this->db->get();

        if($result != false){
          return $result->result();
        }else{
          return false;
        }
    }
    /**
     * generate Token no
     * @access public
     * @return object
     * @param int
     */
    public function generateToken_no($outlet_id) {
        $year = date('ymd', strtotime('today'));
        $sale_count = $this->db->query("SELECT count(id) as sale_count
               FROM tbl_sales where outlet_id=$outlet_id")->row('sale_count');
        $token_no = $year . str_pad($sale_count + 1, 2, '0', STR_PAD_LEFT);
        return $token_no;
    }
    /**
     * get All Food Menus
     * @access public
     * @return boolean
     * @param no
     */
    public function getAllFoodMenus(){
      $outlet_id = $this->session->userdata('outlet_id');
      $getFM = getFMIds($outlet_id);
      $result = $this->db->query("SELECT fm.*,fmc.category_name, COUNT(sd.food_menu_id) as item_sold,kitchen_id 
      FROM tbl_food_menus fm  LEFT JOIN (select * from tbl_food_menu_categories where del_status='Live') fmc ON fmc.id = fm.category_id LEFT JOIN (select * from tbl_sales_details where del_status='Live') sd ON sd.food_menu_id = fm.id LEFT JOIN (select kitchen_id,cat_id from tbl_kitchen_categories where del_status='Live') kt_cat ON kt_cat.cat_id = fm.category_id WHERE FIND_IN_SET(fm.id, '$getFM') AND fm.del_status='Live' GROUP BY fm.id order BY name ASC")->result();
      if($result != false){
        return $result;
      }else{
        return false;
      }
    }

      public function getTopFoodMenus($limit = 20) {
        $outlet_id = $this->session->userdata('outlet_id');
        $getFM = getFMIds($outlet_id);
        $result = $this->db->query("
            SELECT 
                fm.*, 
                fmc.category_name, 
                IFNULL(SUM(sd.qty), 0) AS item_sold,
                kt_cat.kitchen_id 
            FROM tbl_food_menus fm
            LEFT JOIN (SELECT * FROM tbl_food_menu_categories WHERE del_status='Live') fmc ON fmc.id = fm.category_id
            LEFT JOIN (
                SELECT food_menu_id, SUM(qty) as qty 
                FROM tbl_sales_details 
                WHERE del_status='Live' 
                GROUP BY food_menu_id
            ) sd ON sd.food_menu_id = fm.id
            LEFT JOIN (SELECT kitchen_id, cat_id FROM tbl_kitchen_categories WHERE del_status='Live') kt_cat ON kt_cat.cat_id = fm.category_id
            WHERE 
                FIND_IN_SET(fm.id, '$getFM') 
                AND fm.del_status='Live'
            GROUP BY fm.id
            ORDER BY item_sold DESC
            LIMIT $limit
        ")->result();
        return $result ? $result : false;
    }
    // public function attachModifiersToMenus(&$food_menus) {
    //     if (!$food_menus || !is_array($food_menus)) return;
    //     $ids = array_map(function($m){ return $m->id; }, $food_menus);
    //     if (empty($ids)) return;

    //     $modifiers = $this->db->query("
    //         SELECT 
    //             tmm.*, tm.name, tm.price, tm.tax_information, tmm.food_menu_id
    //         FROM tbl_food_menus_modifiers tmm
    //         LEFT JOIN tbl_modifiers tm ON tm.id = tmm.modifier_id
    //         WHERE tmm.del_status = 'Live'
    //         AND tmm.food_menu_id IN (".implode(',', $ids).")
    //     ")->result();

    //     $mod_arr = [];
    //     foreach ($modifiers as $mod) {
    //         $mod_arr[$mod->food_menu_id][] = $mod;
    //     }
    //     foreach ($food_menus as &$menu) {
    //         $menu->modifiers = isset($mod_arr[$menu->id]) ? $mod_arr[$menu->id] : [];
    //     }
    // }
    public function attachModifiersToMenus(&$menus) {
      if (!$menus || !is_array($menus)) return;
      $ids = array_map(function($m){ return $m->id; }, $menus);
      if (empty($ids)) return;
  
      $this->db->select('tmm.food_menu_id, tmm.modifier_id as menu_modifier_id, tmm.id as modifier_row_id, tm.name as menu_modifier_name, tm.tax_information, tm.price as menu_modifier_price');
      $this->db->from('tbl_food_menus_modifiers tmm');
      $this->db->join('tbl_modifiers tm', 'tm.id = tmm.modifier_id', 'left');
      $this->db->where_in('tmm.food_menu_id', $ids);
      $this->db->where('tmm.del_status', 'Live');
      $query = $this->db->get();
  
      $mod_map = [];
      foreach ($query->result() as $mod) {
          $mod_map[$mod->food_menu_id][] = $mod;
      }
  
      foreach ($menus as &$menu) {
          $menu->modifiers = isset($mod_map[$menu->id]) ? array_values($mod_map[$menu->id]) : [];
          $menu->is_promo = isset($mod_map[$menu->id]) ? 'Yes' : 'No';
      }
  }
  public function getModifiersByMenuId($menu_id) {
    $this->db->select('tmm.modifier_id, tmm.id, tm.name, tm.tax_information, tm.price');
    $this->db->from('tbl_food_menus_modifiers tmm');
    $this->db->join('tbl_modifiers tm', 'tm.id = tmm.modifier_id', 'left');
    $this->db->where('tmm.food_menu_id', $menu_id);
    $this->db->where('tmm.del_status', 'Live');
    return $this->db->get()->result();
}
  //   public function getModifiersByMenuId($menu_id) {
  //     $this->db->select('tmm.modifier_id as menu_modifier_id, tmm.id as modifier_row_id, tm.name as menu_modifier_name, tm.tax_information, tm.price as menu_modifier_price, tm.type');
  //     $this->db->from('tbl_food_menus_modifiers tmm');
  //     $this->db->join('tbl_modifiers tm', 'tm.id = tmm.modifier_id', 'left');
  //     $this->db->where('tmm.food_menu_id', $menu_id);
  //     $this->db->where('tmm.del_status', 'Live');
  //     return $this->db->get()->result();
  // }

    public function searchFoodMenus($term = '', $category_id = '', $type = '', $outlet_id = '')
    {
        // Obtiene los ids de menús válidos para el outlet
        $fm_ids = getFMIds($outlet_id);
        if (!$fm_ids) {
            return []; // Si no hay menús disponibles, retorna vacío
        }

        $this->db->select('fm.*, fmc.category_name');
        $this->db->from('tbl_food_menus as fm');
        $this->db->join('tbl_food_menu_categories as fmc', 'fmc.id = fm.category_id', 'left');
        $this->db->where('fm.del_status', 'Live');
        $this->db->where("FIND_IN_SET(fm.id, '$fm_ids')");

        $col = "utf8mb4_general_ci";
        $column_search = array('fm.name', 'fm.code');
        // Búsqueda por término, nombre, código o categoría
        if ($term) {
          
            $delimiter = ' ';
            $palabras = explode($delimiter, $term);
    
            $concat_search = '';
            $i_cs = 0;
            foreach ($column_search as $item){
                if ($i_cs > 0){
                    $concat_search = $concat_search . ", ";
                }
                $concat_search .= "IFNULL(CONCAT($item, ' '), '') COLLATE $col";
                $i_cs++;
            }
          
            $this->db->group_start();
            foreach ($palabras as $palabra) {
              // Escapa el valor para SQL y agrega el marcador de collation
              $search = $this->db->escape_like_str($palabra);
              $this->db->where("CONCAT($concat_search) LIKE " . "_utf8mb4'%$search%' COLLATE $col", null, false);
          }
            $this->db->group_end(); //close bracket

            // $this->db->group_start();
            // $this->db->like('fm.name', $term);
            // $this->db->or_like('fm.code', $term);
            // $this->db->or_like('fmc.category_name', $term);
            // $this->db->group_end();
        }
        // Filtro por categoría
        if ($category_id) {
            $this->db->where('fm.category_id', $category_id);
        }
        // Filtro por tipo
        if ($type === 'veg') {
            $this->db->where('fm.veg_item', 'Veg Yes');
        }
        if ($type === 'bev') {
            $this->db->where('fm.beverage_item', 'Bev Yes');
        }
        if ($type === 'combo') {
            $this->db->where('fm.product_type', 2);
        }

        $this->db->limit(24);

        $query = $this->db->get();
        return $query->result();
    }
    public function getFoodMenuById($id) {
        $this->db->select('fm.*, fmc.category_name');
        $this->db->from('tbl_food_menus fm');
        $this->db->join('tbl_food_menu_categories fmc', 'fmc.id = fm.category_id', 'left');
        $this->db->where('fm.del_status', 'Live');
        $this->db->where('fm.id', $id);
        $query = $this->db->get();
        $food_menu = $query->row();
    
        if ($food_menu) {
            // Traer los modificadores asociados
            $this->db->select('tmm.*, tm.name, tm.price, tm.tax_information');
            $this->db->from('tbl_food_menus_modifiers tmm');
            $this->db->join('tbl_modifiers tm', 'tm.id = tmm.modifier_id', 'left');
            $this->db->where('tmm.food_menu_id', $id);
            $this->db->where('tmm.del_status', 'Live');
            $modifiers = $this->db->get()->result();
    
            $food_menu->modifiers = $modifiers;
        }
    
        return $food_menu;
    }

    /**
     * get All Menu Categories
     * @access public
     * @return boolean
     * @param no
     */
    public function getAllMenuCategories(){
      $company_id = $this->session->userdata('company_id');
      $this->db->select("*");
      $this->db->from("tbl_food_menu_categories");
      $this->db->where("company_id", $company_id);
      $this->db->where("del_status", 'Live');
      $this->db->order_by('id', 'ASC');
      $result = $this->db->get();

      if($result != false){
        return $result->result();
      }else{
        return false;
      }
    }
    /**
     * get All Menu Modifiers
     * @access public
     * @return boolean
     * @param no
     */
    public function getAllMenuModifiers(){
     $company_id = $this->session->userdata('company_id');
      $this->db->select("tbl_food_menus_modifiers.*,tbl_modifiers.name,tbl_modifiers.price,tbl_modifiers.tax_information");
      $this->db->from("tbl_food_menus_modifiers");
      $this->db->join('tbl_modifiers', 'tbl_modifiers.id = tbl_food_menus_modifiers.modifier_id', 'left');
      $this->db->where("tbl_food_menus_modifiers.company_id", $company_id);
      $this->db->where("tbl_food_menus_modifiers.del_status", 'Live');
      $this->db->order_by('id', 'ASC');
      $result = $this->db->get();

      if($result != false){
        return $result->result();
      }else{
        return false;
      }
    }
    /**
     * get Waiters For This Company
     * @access public
     * @return object
     * @param int
     * @param string
     */
    public function getWaitersForThisCompany($company_id,$table){
        $language_manifesto = $this->session->userdata('language_manifesto');
        if(str_rot13($language_manifesto)=="eriutoeri"){
          $value = $this->session->userdata("outlet_id");
          if(!$value){
            $value = 1;
          }
          // Build the query
          $this->db->select('*');
          $this->db->from('tbl_users');
          $this->db->where("FIND_IN_SET($value, outlets) >", 0);
          $this->db->where("designation", 'Waiter');
          $this->db->where("company_id", $company_id);
          $this->db->where("del_status", 'Live');
          $this->db->order_by('full_name', 'ASC');
          $query = $this->db->get();
          $data =  $query->result();
          return $data;
        }else{
            $outlet_id = $this->session->userdata('outlet_id');
            if(!$outlet_id){
              $outlet_id = 1;
            }

            $this->db->select("*");
            $this->db->from($table);
            $this->db->where("company_id", $company_id);
            $this->db->where("outlet_id", $outlet_id);
            $this->db->where("designation", 'Waiter');
            $this->db->where("del_status", 'Live');
            $this->db->order_by('full_name', 'ASC');
            $result = $this->db->get();
            if($result != false){
                return $result->result();
            }else{
                return false;
            }
        }

    }
    public function getWaitersForThisCompanyForOutlet($company_id,$table){
      $language_manifesto = $this->session->userdata('language_manifesto');
      if(str_rot13($language_manifesto)=="eriutoeri"){
        $value = $this->session->userdata("outlet_id");
        if($value==''){
          $value = 0;
        }
        // Build the query
        $this->db->select('*');
        $this->db->from('tbl_users');
        $this->db->where("FIND_IN_SET($value, outlets) >", 0);
        $this->db->where("company_id", $company_id);
        $this->db->where("del_status", 'Live');
        $this->db->order_by('full_name', 'ASC');
        $query = $this->db->get();
        $data =  $query->result();
        return $data;
      }else{
          $outlet_id = $this->session->userdata('outlet_id');
          if($outlet_id==''){
            $outlet_id = 0;
          }
          $this->db->select("*");
          $this->db->from($table);
          $this->db->where("company_id", $company_id);
          $this->db->where("outlet_id", $outlet_id);
          $this->db->where("del_status", 'Live');
          $this->db->order_by('full_name', 'ASC');
          $result = $this->db->get();
          if($result != false){
              return $result->result();
          }else{
              return false;
          }
      }

  }
    /**
     * get New Orders
     * @access public
     * @return object
     * @param int
     */
    public function getNewOrders($outlet_id){
        $today_date = date('Y-m-d');
        $is_waiter = $this->session->userdata('is_waiter');
        $user_id = $this->session->userdata('user_id');
        $role = $this->session->userdata('role');

      $this->db->select("*,tbl_sales.id as sale_id,tbl_customers.name as customer_name,tbl_customers.phone as customer_phone, tbl_sales.id as sales_id,tbl_users.full_name as waiter_name,tbl_tables.name as table_name");
      $this->db->from('tbl_sales');
      $this->db->join('tbl_tables', 'tbl_tables.id = tbl_sales.table_id', 'left');
      $this->db->join('tbl_users', 'tbl_users.id = tbl_sales.waiter_id', 'left');
      $this->db->join('tbl_customers', 'tbl_customers.id = tbl_sales.customer_id', 'left');
      if(isset($is_waiter) && $is_waiter=="Yes"){
          $this->db->where("tbl_sales.waiter_id", $user_id);
      }else{
          if(isset($role) && $role!="Admin"){
              $this->db->where("tbl_sales.user_id", $user_id);
          }
      }
        $this->db->where("tbl_sales.outlet_id", $outlet_id);
        $this->db->where("(order_status='1' OR order_status='2')");
        $this->db->where("(future_sale_status='1' OR future_sale_status='3')");
      $this->db->order_by('tbl_sales.id', 'ASC');
      $this->db->where('tbl_sales.del_status', 'Live');
      $result = $this->db->get();

        if($result != false){
          return $result->result();
        }else{
          return false;
        }

    }

    public function future_sales($outlet_id){
        $today_date = date('Y-m-d');
        $this->db->select("tbl_sales.*,tbl_customers.name as customer_name,tbl_customers.phone as customer_phone,tbl_tables.name as table_name,tbl_customers.phone");
        $this->db->from('tbl_sales');
        $this->db->join('tbl_customers', 'tbl_customers.id = tbl_sales.customer_id', 'left');
        $this->db->join('tbl_tables', 'tbl_tables.id = tbl_sales.table_id', 'left');
        $this->db->where("tbl_sales.outlet_id", $outlet_id);
        $this->db->where("tbl_sales.del_status", "Live");
        $this->db->where("(order_status='1' OR order_status='2')");
        $this->db->where("(future_sale_status!='1')");
        $this->db->order_by('tbl_sales.id', 'DESC');
        $result = $this->db->get();

        if($result != false){
            return $result->result();
        }else{
            return false;
        }

    }
    public function self_order_sales($outlet_id){
        $type = escape_output($_GET['type']);

        $self_order_ran_code = $this->session->userdata('self_order_ran_code');
        $online_customer_id = $this->session->userdata('online_customer_id');

        $this->db->select("tbl_kitchen_sales.*,tbl_customers.name as customer_name,tbl_customers.phone");
        $this->db->from('tbl_kitchen_sales');
        $this->db->join('tbl_customers', 'tbl_customers.id = tbl_kitchen_sales.customer_id', 'left');
        $this->db->where("tbl_kitchen_sales.outlet_id", $outlet_id);
        if($type==1 && $self_order_ran_code){
            $this->db->where("tbl_kitchen_sales.self_order_ran_code", $self_order_ran_code);
        }else{
            $this->db->where("tbl_kitchen_sales.customer_id", $online_customer_id);
        }
        $this->db->where("tbl_kitchen_sales.del_status", "Live");
        $this->db->order_by('tbl_kitchen_sales.id', 'DESC');
        $result = $this->db->get();
        if($result != false){
            return $result->result();
        }else{
            return false;
        }

    }
    public function self_order_sales_admin($outlet_id){
        $role = $this->session->userdata('role');
        $user_id = $this->session->userdata('user_id');
        $this->db->select("tbl_kitchen_sales.*,tbl_customers.name as customer_name,tbl_customers.phone");
        $this->db->from('tbl_kitchen_sales');
        $this->db->join('tbl_customers', 'tbl_customers.id = tbl_kitchen_sales.customer_id', 'left');
        $this->db->where("tbl_kitchen_sales.outlet_id", $outlet_id);
        if($role!="Admin"){
            $this->db->where("tbl_kitchen_sales.online_self_order_receiving_id", $user_id);
        }
        $this->db->where("tbl_kitchen_sales.del_status", "Live");
        $this->db->where("is_self_order","Yes");
        $this->db->order_by('tbl_kitchen_sales.id', 'DESC');
        $result = $this->db->get();
        if($result != false){
            return $result->result();
        }else{
            return false;
        }
    }
    public function online_order_sales_admin($outlet_id){
        $role = $this->session->userdata('role');
        $user_id = $this->session->userdata('user_id');
        $this->db->select("tbl_kitchen_sales.*,tbl_customers.name as customer_name,tbl_customers.phone");
        $this->db->from('tbl_kitchen_sales');
        $this->db->join('tbl_customers', 'tbl_customers.id = tbl_kitchen_sales.customer_id', 'left');
        $this->db->where("tbl_kitchen_sales.outlet_id", $outlet_id);
        if($role!="Admin"){
            $this->db->where("tbl_kitchen_sales.online_order_receiving_id", $user_id);
        }
        $this->db->where("tbl_kitchen_sales.del_status", "Live");
        $this->db->where("is_online_order","Yes");
        $this->db->order_by('tbl_kitchen_sales.id', 'DESC');
        $result = $this->db->get();
        if($result != false){
            return $result->result();
        }else{
            return false;
        }
    }
    /**
     * get All Tables With New Status
     * @access public
     * @return object
     * @param int
     */
    public function getAllTablesWithNewStatus($outlet_id){
      $this->db->select("*");
      $this->db->from('tbl_sales');
      $this->db->where("(order_status='1' OR order_status='2')");
      $this->db->where("outlet_id", $outlet_id);
      $this->db->where('del_status', 'Live');
      $this->db->order_by('id', 'ASC');
      $result = $this->db->get();

        if($result != false){
          return $result->result();
        }else{
          return false;
        }
    }
    /**
     * get Sale By Sale Id
     * @access public
     * @return object
     * @param int
     */
    public function getSaleBySaleId($sales_id){
      $this->db->select("tbl_sales.*,w.full_name as waiter_name,tbl_customers.name as customer_name,tbl_customers.address as customer_address,tbl_tables.name as table_name,tbl_users.full_name as user_name,tbl_companies.invoice_footer as invoice_footer");
      $this->db->from('tbl_sales');
      $this->db->join('tbl_customers', 'tbl_customers.id = tbl_sales.customer_id', 'left');
      $this->db->join('tbl_users', 'tbl_users.id = tbl_sales.user_id', 'left');
      $this->db->join('tbl_tables', 'tbl_tables.id = tbl_sales.table_id', 'left');
      $this->db->join('tbl_companies', 'tbl_companies.id = tbl_sales.outlet_id', 'left');
      $this->db->join('tbl_users w', 'w.id = tbl_sales.waiter_id', 'left');
      $this->db->where("tbl_sales.id", $sales_id);
      $this->db->where('tbl_sales.del_status', 'Live');
      $this->db->order_by('tbl_sales.id', 'ASC');
      $result = $this->db->get();

        if($result != false){
          return $result->result();
        }else{
          return false;
        }
    }
    /**
     * get Single Sale By Sale Id
     * @access public
     * @return object
     * @param int
     */
    public function getSingleSaleBySaleId($sales_id){
      $this->db->select("tbl_sales.*,w.full_name as waiter_name,tbl_customers.name as customer_name,tbl_users.full_name as user_name,tbl_companies.invoice_footer as invoice_footer");
      $this->db->from('tbl_sales');
      $this->db->join('tbl_customers', 'tbl_customers.id = tbl_sales.customer_id', 'left');
      $this->db->join('tbl_users', 'tbl_users.id = tbl_sales.user_id', 'left');
      $this->db->join('tbl_companies', 'tbl_companies.id = tbl_sales.outlet_id', 'left');
      $this->db->join('tbl_users w', 'w.id = tbl_sales.waiter_id', 'left');
      $this->db->where("tbl_sales.id", $sales_id);
      $this->db->where('tbl_sales.del_status', 'Live');
      $this->db->order_by('tbl_sales.id', 'ASC');
      return $this->db->get()->row();
    }
    /**
     * get Holds By Outlet And User Id
     * @access public
     * @return object
     * @param int
     * @param int
     */
    public function getHoldsByOutletAndUserId($outlet_id,$user_id){
      $this->db->select("tbl_holds.*,tbl_customers.name as customer_name,tbl_tables.name as table_name,tbl_customers.phone");
      $this->db->from('tbl_holds');
      $this->db->join('tbl_customers', 'tbl_customers.id = tbl_holds.customer_id', 'left');
      $this->db->join('tbl_tables', 'tbl_tables.id = tbl_holds.table_id', 'left');
      $this->db->where("tbl_holds.outlet_id", $outlet_id);
      $this->db->where("tbl_holds.user_id", $user_id);
      $this->db->where("tbl_holds.del_status", "Live");
      $this->db->order_by('tbl_holds.id', 'ASC');
      $result = $this->db->get();

        if($result != false){
          return $result->result();
        }else{
          return false;
        }
    }
    /**
     * get Last Ten Sales By Outlet And User Id
     * @access public
     * @return object
     * @param int
     */
    public function getLastTenSalesByOutletAndUserId($outlet_id){
      $this->db->select("tbl_sales.*,tbl_customers.name as customer_name,tbl_tables.name as table_name,tbl_customers.phone");
      $this->db->from('tbl_sales');
      $this->db->join('tbl_customers', 'tbl_customers.id = tbl_sales.customer_id', 'left');
      $this->db->join('tbl_tables', 'tbl_tables.id = tbl_sales.table_id', 'left');
      $this->db->where("tbl_sales.outlet_id", $outlet_id);
      $this->db->where("tbl_sales.del_status", "Live");
      $this->db->where("(order_status='2' OR order_status='3')");
      $this->db->limit(20);
      $this->db->order_by('tbl_sales.id', 'DESC');
      $result = $this->db->get();

        if($result != false){
          return $result->result();
        }else{
          return false;
        }
    }
    /**
     * get My Todays Sales By Outlet And User Id
     * @access public
     * @return object
     * @param int
     * @param int
     */
    public function getMyTodaysSalesByOutletAndUserId($outlet_id,$user_id){
      $this->db->select("tbl_sales.*,tbl_customers.name as customer_name,tbl_tables.name as table_name");
      $this->db->from('tbl_sales');
      $this->db->join('tbl_customers', 'tbl_customers.id = tbl_sales.customer_id', 'left');
      $this->db->join('tbl_tables', 'tbl_tables.id = tbl_sales.table_id', 'left');
      $this->db->where("tbl_sales.outlet_id", $outlet_id);
      $this->db->where("tbl_sales.user_id", $user_id);
      $this->db->where("DATE(tbl_sales.date_time)", date('Y-m-d'));
      $this->db->where("tbl_sales.del_status", "Live");
      $this->db->where("(order_status='2' OR order_status='3')");
      $this->db->order_by('tbl_sales.id', 'DESC');
      $result = $this->db->get();

        if($result != false){
          return $result->result();
        }else{
          return false;
        }
    }
    /**
     * get All Items From Sales Detail By Sales Id
     * @access public
     * @return object
     * @param int
     */
    public function getAllItemsFromSalesDetailBySalesId($sales_id){
      $this->db->select("tbl_sales_details.*,tbl_sales_details.id as sales_details_id,tbl_food_menus.code as code");
      $this->db->from('tbl_sales_details');
      $this->db->join('tbl_food_menus', 'tbl_food_menus.id = tbl_sales_details.food_menu_id', 'left');
      $this->db->where("sales_id", $sales_id);
      $this->db->where('tbl_sales_details.del_status', 'Live');
      $this->db->order_by('tbl_sales_details.id', 'ASC');
      $result = $this->db->get();

        if($result != false){
          return $result->result();
        }else{
          return false;
        }
    }
    public function getAllItemsFromSalesDetailBySalesIdKitchen($sales_id){
      $this->db->select("tbl_kitchen_sales_details.*,tbl_kitchen_sales_details.id as sales_details_id,tbl_food_menus.code as code");
      $this->db->from('tbl_kitchen_sales_details');
      $this->db->join('tbl_food_menus', 'tbl_food_menus.id = tbl_kitchen_sales_details.food_menu_id', 'left');
      $this->db->where("sales_id", $sales_id);
      $this->db->where('tbl_kitchen_sales_details.del_status', 'Live');
      $this->db->order_by('tbl_kitchen_sales_details.id', 'ASC');
      $result = $this->db->get();

        if($result != false){
          return $result->result();
        }else{
          return false;
        }
    }
    /**
     * get All Items From Sales Detail By Sales Id
     * @access public
     * @return object
     * @param int
     */
    public function getAllItemsFromSalesDetailBySalesIdModify($sales_id){
      $this->db->select("tbl_sales_details.*,tbl_sales_details.id as sales_details_id,tbl_food_menus.code as code");
      $this->db->from('tbl_sales_details');
      $this->db->join('tbl_food_menus', 'tbl_food_menus.id = tbl_sales_details.food_menu_id', 'left');
      $this->db->where("sales_id", $sales_id);
      $this->db->where("is_free_item", "0");
      $this->db->where('tbl_sales_details.del_status', 'Live');
      $this->db->order_by('tbl_sales_details.id', 'ASC');
      $result = $this->db->get();

        if($result != false){
          return $result->result();
        }else{
          return false;
        }
    }
    /**
     * get All Items From Sales Detail By Sales Id
     * @access public
     * @return object
     * @param int
     */
    public function getAllItemsFromSalesDetailBySalesIdModifyChild($row_id,$sale_id){
      $this->db->select("tbl_sales_details.*,tbl_sales_details.id as sales_details_id,tbl_food_menus.code as code");
      $this->db->from('tbl_sales_details');
      $this->db->join('tbl_food_menus', 'tbl_food_menus.id = tbl_sales_details.food_menu_id', 'left');
      $this->db->where("is_free_item", $row_id);
      $this->db->where("sales_id", $sale_id);
      $this->db->where('tbl_sales_details.del_status', 'Live');
      $this->db->order_by('tbl_sales_details.id', 'ASC');
      $result = $this->db->get();

        if($result != false){
          return $result->result();
        }else{
          return false;
        }
    }
    /**
     * get Modifiers By Sale And Sale Details Id
     * @access public
     * @return object
     * @param int
     * @param int
     */
    public function getModifiersBySaleAndSaleDetailsId($sales_id,$sale_details_id){
      $this->db->select("tbl_sales_details_modifiers.*,tbl_modifiers.name");
      $this->db->from('tbl_sales_details_modifiers');
      $this->db->join('tbl_modifiers', 'tbl_modifiers.id = tbl_sales_details_modifiers.modifier_id', 'left');
      $this->db->where("tbl_sales_details_modifiers.sales_id", $sales_id);
      $this->db->where("tbl_sales_details_modifiers.sales_details_id", $sale_details_id);
      $this->db->order_by('tbl_sales_details_modifiers.id', 'ASC');
      $result = $this->db->get();
        if($result != false){
          return $result->result();
        }else{
          return false;
        }
    }
    /**
     * get Modifiers By Sale And Sale Details Id
     * @access public
     * @return object
     * @param int
     * @param int
     */
    public function getModifiersBySaleAndSaleDetailsIdKitchen($sales_id,$sale_details_id){
      $this->db->select("tbl_kitchen_sales_details_modifiers.*,tbl_modifiers.name");
      $this->db->from('tbl_kitchen_sales_details_modifiers');
      $this->db->join('tbl_modifiers', 'tbl_modifiers.id = tbl_kitchen_sales_details_modifiers.modifier_id', 'left');
      $this->db->where("tbl_kitchen_sales_details_modifiers.sales_id", $sales_id);
      $this->db->where("tbl_kitchen_sales_details_modifiers.sales_details_id", $sale_details_id);
      $this->db->order_by('tbl_kitchen_sales_details_modifiers.id', 'ASC');
      $result = $this->db->get();

        if($result != false){
          return $result->result();
        }else{
          return false;
        }
    }
    /**
     * get Modifiers By Sale And Sale Details Id
     * @access public
     * @return object
     * @param int
     * @param int
     */
    public function getModifiersBySaleAndSaleDetailsIdKitchenAuto($sales_id,$sale_details_id){
      $this->db->select("tbl_kitchen_sales_details_modifiers.*,tbl_modifiers.name");
      $this->db->from('tbl_kitchen_sales_details_modifiers');
      $this->db->join('tbl_modifiers', 'tbl_modifiers.id = tbl_kitchen_sales_details_modifiers.modifier_id', 'left');
      $this->db->where("tbl_kitchen_sales_details_modifiers.sales_id", $sales_id);
      $this->db->where("tbl_kitchen_sales_details_modifiers.is_print", 1);
      $this->db->where("tbl_kitchen_sales_details_modifiers.sales_details_id", $sale_details_id);
      $this->db->order_by('tbl_kitchen_sales_details_modifiers.id', 'ASC');
      $result = $this->db->get();

        if($result != false){
          return $result->result();
        }else{
          return false;
        }
    }
    /**
     * get Number Of Holds By User And Outlet Id
     * @access public
     * @return object
     * @param int
     * @param int
     */
    public function getNumberOfHoldsByUserAndOutletId($outlet_id,$user_id)
    {
      $this->db->select('id');
      $this->db->from('tbl_holds');
      $this->db->where("outlet_id", $outlet_id);
      $this->db->where("user_id", $user_id);
      $this->db->where('del_status', 'Live');
      return $this->db->get()->num_rows();
    }
    /**
     * get new sale by table id
     * @access public
     * @return object
     * @param int
     */
    public function get_new_sale_by_table_id($table_id)
    {
      $this->db->select("*");
      $this->db->from('tbl_sales');
      $this->db->where("table_id", $table_id);
      $this->db->where("order_status", 1);
      $this->db->where('del_status', 'Live');
      return $this->db->get()->row();
    }
    /**
     * get hold info by hold id
     * @access public
     * @return object
     * @param int
     */
    public function get_hold_info_by_hold_id($hold_id)
    {
      $this->db->select("tbl_holds.*,tbl_users.full_name as waiter_name,tbl_customers.name as customer_name,tbl_tables.name as table_name");
      $this->db->from('tbl_holds');
      $this->db->join('tbl_customers', 'tbl_customers.id = tbl_holds.customer_id', 'left');
      $this->db->join('tbl_users', 'tbl_users.id = tbl_holds.waiter_id', 'left');
      $this->db->join('tbl_tables', 'tbl_tables.id = tbl_holds.table_id', 'left');
      $this->db->where("tbl_holds.id", $hold_id);
      $this->db->where('tbl_holds.del_status', 'Live');
      $this->db->order_by('tbl_holds.id', 'ASC');
      $result = $this->db->get();

        if($result != false){
          return $result->result();
        }else{
          return false;
        }
    }
    /**
     * get All Items From Holds Detail By Holds Id
     * @access public
     * @return object
     * @param int
     */
    public function getAllItemsFromHoldsDetailByHoldsId($hold_id)
    {
      $this->db->select("tbl_holds_details.*,tbl_holds_details.id as holds_details_id");
      $this->db->from('tbl_holds_details');
      $this->db->join('tbl_food_menus', 'tbl_food_menus.id = tbl_holds_details.food_menu_id', 'left');
      $this->db->where("holds_id", $hold_id);
      $this->db->where('tbl_holds_details.del_status', 'Live');
      $this->db->order_by('tbl_holds_details.id', 'ASC');
      $result = $this->db->get();

        if($result != false){
          return $result->result();
        }else{
          return false;
        }
    }
    /**
     * get Modifiers By Hold And Holds Details Id
     * @access public
     * @return object
     * @param int
     * @param int
     */
    public function getModifiersByHoldAndHoldsDetailsId($hold_id,$holds_details_id)
    {
      $this->db->select("tbl_holds_details_modifiers.*,tbl_modifiers.name");
      $this->db->from('tbl_holds_details_modifiers');
      $this->db->join('tbl_modifiers', 'tbl_modifiers.id = tbl_holds_details_modifiers.modifier_id', 'left');
      $this->db->where("tbl_holds_details_modifiers.holds_id", $hold_id);
      $this->db->where("tbl_holds_details_modifiers.holds_details_id", $holds_details_id);
      $this->db->order_by('tbl_holds_details_modifiers.id', 'ASC');
      $result = $this->db->get();

        if($result != false){
          return $result->result();
        }else{
          return false;
        }
    }
    /**
     * get Customer Info By Id
     * @access public
     * @return object
     * @param int
     */
    public function getCustomerInfoById($customer_id)
    {
      $this->db->select("*");
      $this->db->from('tbl_customers');
      $this->db->where("id", $customer_id);
      $this->db->where('del_status', 'Live');
      $this->db->order_by('id', 'ASC');
      return $this->db->get()->row();
    }
    /**
     * get All Payment Methods
     * @access public
     * @return boolean
     * @param no
     */
    public function getAllPaymentMethods()
    {
      $company_id = $this->session->userdata('company_id');
      $this->db->select('*');
      $this->db->from('tbl_payment_methods');
      $this->db->where("company_id", $company_id);
      $this->db->where("del_status", 'Live');
      $result = $this->db->get();

        if($result != false){
          return $result->result();
        }else{
          return false;
        }
    }
    /**
     * get All Payment Methods
     * @access public
     * @return boolean
     * @param no
     */
    public function getAllPaymentMethodsFinalize()
    {
        $company_id = $this->session->userdata('company_id');

        $this->db->select('*');
        $this->db->from('tbl_payment_methods');
        $this->db->group_start(); // (
            $this->db->where('company_id', $company_id);
            $this->db->or_where('name', 'Cash');
        $this->db->group_end(); // )
        $this->db->where('del_status', 'Live'); // Fuera del grupo, se aplica a todo lo anterior
        $this->db->order_by("order_by", 'ASC');

        $result = $this->db->get();

        if ($result->num_rows() > 0) {
            return $result->result();
        } else {
            return false;
        }
    }
    /**
     * get Summation Of Paid Purchase
     * @access public
     * @return object
     * @param int
     * @param int
     * @param string
     */
	public function getSummationOfPaidPurchase($user_id, $outlet_id, $date)
    {
      $this->db->select("SUM(paid) as purchase_paid");
      $this->db->from('tbl_purchase');
      $this->db->where("user_id", $user_id);
      $this->db->where("outlet_id", $outlet_id);
      $this->db->where("date", $date);
      $this->db->where('del_status', 'Live');
      return $this->db->get()->row();
    }
    /**
     * get Summation Of Supplier Payment
     * @access public
     * @return object
     * @param int
     * @param int
     * @param string
     */
    public function getSummationOfSupplierPayment($user_id, $outlet_id, $date)
    {
      $this->db->select("SUM(amount) as payment_amount");
      $this->db->from('tbl_supplier_payments');
      $this->db->where("user_id", $user_id);
      $this->db->where("outlet_id", $outlet_id);
      $this->db->where("date", $date);
      $this->db->where('del_status', 'Live');
      return $this->db->get()->row();
    }
    /**
     * get Summation Of Customer Due Receive
     * @access public
     * @return object
     * @param int
     * @param int
     * @param string
     */
    public function getSummationOfCustomerDueReceive($user_id, $outlet_id, $date)
    {
      $this->db->select("SUM(amount) as receive_amount");
      $this->db->from('tbl_customer_due_receives');
      $this->db->where("user_id", $user_id);
      $this->db->where("outlet_id", $outlet_id);
      $this->db->where("date>=", $date);
      $this->db->where("date<=", date('Y-m-d H:i:s'));
      $this->db->where('del_status', 'Live');
      return $this->db->get()->row();
    }
    /**
     * get Expense Amount Sum
     * @access public
     * @return object
     * @param int
     * @param int
     * @param string
     */
    public function getExpenseAmountSum($user_id, $outlet_id, $date)
    {
      $this->db->select("SUM(amount) as amount");
      $this->db->from('tbl_expenses');
      $this->db->where("user_id", $user_id);
      $this->db->where("outlet_id", $outlet_id);
      $this->db->where("date", $date);
      $this->db->where('del_status', 'Live');
      return $this->db->get()->row();
    }
    /**
     * get Sale Paid Sum
     * @access public
     * @return object
     * @param int
     * @param int
     * @param string
     */
    public function getSalePaidSum($user_id, $outlet_id, $date)
    {
      $this->db->select("SUM(paid_amount) as amount");
      $this->db->from('tbl_sales');
      $this->db->where("user_id", $user_id);
      $this->db->where("outlet_id", $outlet_id);
      $this->db->where("date_time>=", $date);
      $this->db->where("date_time<=", date('Y-m-d H:i:s'));
      $this->db->where('del_status', 'Live');
      return $this->db->get()->row();
    }
    /**
     * get Sale Due Sum
     * @access public
     * @return object
     * @param int
     * @param int
     * @param string
     */
    public function getSaleDueSum($user_id, $outlet_id, $date)
    {
      $this->db->select("SUM(due_amount) as amount");
      $this->db->from('tbl_sales');
      $this->db->where("user_id", $user_id);
      $this->db->where("outlet_id", $outlet_id);
      $this->db->where("date_time>=", $date);
      $this->db->where("date_time<=", date('Y-m-d H:i:s'));
      $this->db->where('del_status', 'Live');
      return $this->db->get()->row();
    }
    /**
     * get Payable Aomount Sum
     * @access public
     * @return object
     * @param int
     * @param int
     * @param string
     */
    public function getPayableAomountSum($user_id,$outlet_id='', $date='')
    {
      $this->db->select("SUM(total_payable) as amount");
      $this->db->from('tbl_sales');
      $this->db->where("user_id", $user_id);
      $this->db->where("outlet_id", $outlet_id);
      $this->db->where("date_time>=", $date);
      $this->db->where("date_time<=", date('Y-m-d H:i:s'));
      $this->db->where('del_status', 'Live');
      return $this->db->get()->row();
    }
    /**
     * get SaleIn Cash Sum
     * @access public
     * @return object
     * @param int
     * @param int
     * @param string
     */
    public function getSaleInCashSum($user_id, $outlet_id, $date)
    {
      $this->db->select("SUM(paid_amount) as amount");
      $this->db->from('tbl_sales');
      $this->db->where("user_id", $user_id);
      $this->db->where("outlet_id", $outlet_id);
      $this->db->where("date_time>=", $date);
      $this->db->where("date_time<=", date('Y-m-d H:i:s'));
      $this->db->where("payment_method_id", 3);
      $this->db->where('del_status', 'Live');
      return $this->db->get()->row();
    }
    /**
     * get SaleIn Cash Sum
     * @access public
     * @return object
     * @param int
     * @param int
     * @param string
     */
    public function getAllSaleByDateForRegister($date)
    {
        $user_id = $this->session->userdata('user_id');
        $outlet_id = $this->session->userdata('outlet_id');
        $this->db->select("tbl_sale_payments.amount as paid_amount,tbl_sale_payments.payment_id,tbl_sales.user_id,tbl_sales.outlet_id,tbl_payment_methods.name as payment_name");
        $this->db->from('tbl_sale_payments');
        $this->db->join('tbl_sales', 'tbl_sales.id = tbl_sale_payments.sale_id', 'left');
        $this->db->join('tbl_payment_methods', 'tbl_payment_methods.id = tbl_sale_payments.payment_id', 'left');
        $this->db->where("tbl_sales.user_id", $user_id);
        $this->db->where("tbl_sales.outlet_id", $outlet_id);
        $this->db->where("tbl_sales.date_time>=", $date);
        $this->db->where("tbl_sales.date_time<=", date('Y-m-d H:i:s'));
        $this->db->where('del_status', 'Live');
        return $this->db->get()->result();
    }
    public function getAllSalePayment($date,$payment_id,$end = null,$user_id=null,$outlet_id=null)
    {
        if (!$user_id) {
          $user_id = $this->session->userdata('user_id');
        }
        if (!$outlet_id) {
          $outlet_id = $this->session->userdata('outlet_id');
        }
        if (!$end) {
          $end = date('Y-m-d H:i:s');
        }
        $this->db->select("tbl_sale_payments.amount as paid_amount,tbl_sale_payments.payment_id,tbl_sales.user_id,tbl_sales.outlet_id,tbl_payment_methods.name as payment_name");
        $this->db->from('tbl_sale_payments');
        $this->db->join('tbl_sales', 'tbl_sales.id = tbl_sale_payments.sale_id', 'left');
        $this->db->join('tbl_payment_methods', 'tbl_payment_methods.id = tbl_sale_payments.payment_id', 'left');
        $this->db->where("tbl_sales.user_id", $user_id);
        $this->db->where("tbl_sales.outlet_id", $outlet_id);
        $this->db->where("tbl_sale_payments.payment_id", $payment_id);
        $this->db->where("tbl_sales.date_time>=", $date);
        $this->db->where("tbl_sales.date_time<=", $end);
        $this->db->where('del_status', 'Live');
        return $this->db->get()->result();
    }
    public function getAllPurchaseByPayment($date,$payment_id,$end = null,$user_id=null,$outlet_id=null)
    {
      if (!$user_id) {
        $user_id = $this->session->userdata('user_id');
      }
      if (!$outlet_id) {
        $outlet_id = $this->session->userdata('outlet_id');
      }
      if (!$end) {
        $end = date('Y-m-d H:i:s');
      }
      $this->db->select("sum(paid) as total_amount");
      $this->db->from('tbl_purchase');
      $this->db->where("user_id", $user_id);
      $this->db->where("outlet_id", $outlet_id);
      $this->db->where("payment_id", $payment_id);
      $this->db->where("added_date_time>=", $date);
      $this->db->where("added_date_time<=", $end);
      $this->db->where('del_status', 'Live');
      $data =  $this->db->get()->row();
      return (isset($data->total_amount) && $data->total_amount?$data->total_amount:0);
    }
    public function getAllDueReceiveByPayment($date,$payment_id,$end = null,$user_id=null,$outlet_id=null)
    {
      if (!$user_id) {
        $user_id = $this->session->userdata('user_id');
      }
      if (!$outlet_id) {
        $outlet_id = $this->session->userdata('outlet_id');
      }
      if (!$end) {
        $end = date('Y-m-d H:i:s');
      }
      $this->db->select("sum(amount) as total_amount");
      $this->db->from('tbl_customer_due_receives');
      $this->db->where("user_id", $user_id);
      $this->db->where("outlet_id", $outlet_id);
      $this->db->where("payment_id", $payment_id);
      $this->db->where("date>=", $date);
      $this->db->where("date<=", $end);
      $this->db->where('del_status', 'Live');
      $data =  $this->db->get()->row();
      return (isset($data->total_amount) && $data->total_amount?$data->total_amount:0);
    }
    public function getAllDuePaymentByPayment($date,$payment_id,$end = null,$user_id=null,$outlet_id=null)
    {
      if (!$user_id) {
        $user_id = $this->session->userdata('user_id');
      }
      if (!$outlet_id) {
        $outlet_id = $this->session->userdata('outlet_id');
      }
      if (!$end) {
        $end = date('Y-m-d H:i:s');
      }
      $this->db->select("sum(amount) as total_amount");
      $this->db->from('tbl_supplier_payments');
      $this->db->where("user_id", $user_id);
      $this->db->where("outlet_id", $outlet_id);
      $this->db->where("payment_id", $payment_id);
      $this->db->where("added_date_time	>=", $date);
      $this->db->where("added_date_time	<=", $end);
      $this->db->where('del_status', 'Live');
      $data =  $this->db->get()->row();
      return (isset($data->total_amount) && $data->total_amount?$data->total_amount:0);
    }
    public function getAllExpenseByPayment($date,$payment_id,$end = null,$user_id=null,$outlet_id=null)
    {
      if (!$user_id) {
        $user_id = $this->session->userdata('user_id');
      }
      if (!$outlet_id) {
        $outlet_id = $this->session->userdata('outlet_id');
      }
      if (!$end) {
        $end = date('Y-m-d H:i:s');
      }
      $this->db->select("sum(amount) as total_amount");
      $this->db->from('tbl_expenses');
      $this->db->where("user_id", $user_id);
      $this->db->where("outlet_id", $outlet_id);
      $this->db->where("payment_id", $payment_id);
      $this->db->where("added_date_time	>=", $date);
      $this->db->where("added_date_time	<=", $end);
      $this->db->where('del_status', 'Live');
      $data =  $this->db->get()->row();
      return (isset($data->total_amount) && $data->total_amount?$data->total_amount:0);
    }
    public function getAllSaleByPayment($date,$payment_id,$end = null, $user_id=null,$outlet_id=null)
    {
      if (!$user_id) {
        $user_id = $this->session->userdata('user_id');
      }
      if (!$outlet_id) {
        $outlet_id = $this->session->userdata('outlet_id');
      }
      if (!$end) {
        $end = date('Y-m-d H:i:s');
      }
      $this->db->select("sum(amount) as total_amount");
      $this->db->from('tbl_sale_payments');
      $this->db->where("user_id", $user_id);
      $this->db->where("outlet_id", $outlet_id);
      $this->db->where("payment_id", $payment_id);
      $this->db->where("date_time	>=", $date);
      $this->db->where("date_time	<=", $end);
      $this->db->where("currency_type", null);
      $this->db->where('del_status', 'Live');
      $data =  $this->db->get()->row();
      return (isset($data->total_amount) && $data->total_amount?$data->total_amount:0);
    }
    public function getAllRefundByPayment($date,$payment_id,$end = null,$user_id=null,$outlet_id=null)
    {
      if (!$user_id) {
        $user_id = $this->session->userdata('user_id');
      }
      if (!$outlet_id) {
        $outlet_id = $this->session->userdata('outlet_id');
      }
      if (!$end) {
        $end = date('Y-m-d H:i:s');
      }
      $this->db->select("sum(total_refund) as total_amount");
      $this->db->from('tbl_sales');
      $this->db->where("user_id", $user_id);
      $this->db->where("outlet_id", $outlet_id);
      $this->db->where("refund_date_time	>=", $date);
      $this->db->where("refund_date_time	<=", $end);
      $this->db->where("refund_payment_id", $payment_id);
      $this->db->where("del_status", "Live");
      $data =  $this->db->get()->row();
      return (isset($data->total_amount) && $data->total_amount?$data->total_amount:0);
    }
    public function getAllSaleByPaymentMultiCurrency($date,$payment_id,$end = null,$user_id=null,$outlet_id=null)
    {
      if (!$user_id) {
        $user_id = $this->session->userdata('user_id');
      }
      if (!$outlet_id) {
        $outlet_id = $this->session->userdata('outlet_id');
      }
      if (!$end) {
        $end = date('Y-m-d H:i:s');
      }
      $this->db->select("sum(amount) as total_amount");
      $this->db->from('tbl_sale_payments');
      $this->db->where("user_id", $user_id);
      $this->db->where("outlet_id", $outlet_id);
      $this->db->where("payment_id", $payment_id);
      $this->db->where("date_time	>=", $date);
      $this->db->where("date_time	<=", $end);
      $this->db->where("currency_type", 1);
      $this->db->where('del_status', 'Live');
      $data =  $this->db->get()->row();
      return (isset($data->total_amount) && $data->total_amount?$data->total_amount:0);
    }
    public function getAllSaleByPaymentMultiCurrencyRows($date,$payment_id,$end = null,$user_id=null,$outlet_id=null)
    {
      if (!$user_id) {
        $user_id = $this->session->userdata('user_id');
      }
      if (!$outlet_id) {
        $outlet_id = $this->session->userdata('outlet_id');
      }
      if (!$end) {
        $end = date('Y-m-d H:i:s');
      }
      $this->db->select("sum(amount) as total_amount,multi_currency");
      $this->db->from('tbl_sale_payments');
      $this->db->where("user_id", $user_id);
      $this->db->where("outlet_id", $outlet_id);
      $this->db->where("payment_id", $payment_id);
      $this->db->where("date_time	>=", $date);
      $this->db->where("date_time	<=", $end);
      $this->db->where("currency_type", 1);
      $this->db->where('del_status', 'Live');
      $this->db->group_by('multi_currency');
      $data =  $this->db->get()->result();
      return $data;
    }
    public function allSaleByDateTime($date,$end = null,$user_id=null,$outlet_id=null)
    {
        if (!$user_id) {
          $user_id = $this->session->userdata('user_id');
        }
        if (!$outlet_id) {
          $outlet_id = $this->session->userdata('outlet_id');
        }
        if (!$end) {
          $end = date('Y-m-d H:i:s');
        }
        $this->db->select("tbl_sales.paid_amount,tbl_sales.payment_method_id,tbl_sales.user_id,tbl_sales.outlet_id");
        $this->db->from('tbl_sales');
        $this->db->where("tbl_sales.user_id", $user_id);
        $this->db->where("tbl_sales.outlet_id", $outlet_id);
        $this->db->where("tbl_sales.date_time>=", $date);
        $this->db->where("tbl_sales.date_time<=", $end);
        $this->db->where('del_status', 'Live');
        return $this->db->get()->result();
    }
    /**
     * get Sale In Paypal Sum
     * @access public
     * @return object
     * @param int
     * @param int
     * @param string
     */
    public function getSaleInPaypalSum($user_id, $outlet_id, $date)
    {
      $this->db->select("SUM(paid_amount) as amount");
      $this->db->from('tbl_sales');
      $this->db->where("user_id", $user_id);
      $this->db->where("outlet_id", $outlet_id);
      $this->db->where("date_time>=", $date);
      $this->db->where("date_time<=", date('Y-m-d H:i:s'));
      $this->db->where("payment_method_id", 5);
      $this->db->where('del_status', 'Live');
      return $this->db->get()->row();
    }
    /**
     * get Sale In Card Sum
     * @access public
     * @return object
     * @param int
     * @param int
     * @param string
     */
    public function getSaleInCardSum($user_id, $outlet_id, $date)
    {
      $this->db->select("SUM(paid_amount) as amount");
      $this->db->from('tbl_sales');
      $this->db->where("user_id", $user_id);
      $this->db->where("outlet_id", $outlet_id);
      $this->db->where("date_time>=", $date);
      $this->db->where("date_time<=", date('Y-m-d H:i:s'));
      $this->db->where("payment_method_id", 4);
      $this->db->where('del_status', 'Live');
      return $this->db->get()->row();
    }
    /**
     * get Sale In Stripe Sum
     * @access public
     * @return object
     * @param int
     * @param int
     * @param string
     */
    public function getSaleInStripeSum($user_id, $outlet_id, $date)
    {
      $this->db->select("SUM(paid_amount) as amount");
      $this->db->from('tbl_sales');
      $this->db->where("user_id", $user_id);
      $this->db->where("outlet_id", $outlet_id);
      $this->db->where("sale_date", $date);
      $this->db->where("payment_method_id", null);
      $this->db->where('del_status', 'Live');
      return $this->db->get()->row();
    }
    /**
     * get Opening Balance
     * @access public
     * @return object
     * @param int
     * @param int
     * @param string
     */
    public function getOpeningBalance($user_id, $outlet_id, $date)
    {
      $this->db->select("opening_balance as amount");
      $this->db->from('tbl_register');
      $this->db->where("user_id", $user_id);
      $this->db->where("outlet_id", $outlet_id);
      $this->db->where("register_status", 1);
      $this->db->order_by('id', 'DESC');
      return $this->db->get()->row();
    }
    /**
     * get Opening Date Time
     * @access public
     * @return object
     * @param int
     * @param int
     * @param string
     */
    public function getOpeningDateTime($user_id, $outlet_id, $date)
    {
      $this->db->select("opening_balance_date_time as opening_date_time");
      $this->db->from('tbl_register');
      $this->db->where("user_id", $user_id);
      $this->db->where("outlet_id", $outlet_id);
      $this->db->where("register_status", 1);
      $this->db->order_by('id', 'DESC');
      return $this->db->get()->row();
    }
    /**
     * get Opening Date Time
     * @access public
     * @return object
     * @param int
     * @param int
     * @param string
     */
    public function getOpeningDetails($user_id, $outlet_id, $date)
    {
      $this->db->select("opening_details");
      $this->db->from('tbl_register');
      $this->db->where("user_id", $user_id);
      $this->db->where("outlet_id", $outlet_id);
      $this->db->where("register_status", 1);
      $this->db->order_by('id', 'DESC');
      return $this->db->get()->row();
    }
    /**
     * get Closing Date Time
     * @access public
     * @return object
     * @param int
     * @param int
     * @param string
     */
    public function getClosingDateTime($user_id, $outlet_id, $date)
    {
      $this->db->select("closing_balance_date_time as closing_date_time");
      $this->db->from('tbl_register');
      $this->db->where("user_id", $user_id);
      $this->db->where("outlet_id", $outlet_id);
      $this->db->where("register_status", 1);
      $this->db->order_by('id', 'DESC');
      return $this->db->get()->row();
    }
    /**
     * get Item Type
     * @access public
     * @return object
     * @param int
     */
    public function getItemType($item_id)
    {
      $this->db->select('bar_item as item_type');
      $this->db->from('tbl_food_menus');
      $this->db->where('id',$item_id);
      $this->db->where('del_status', 'Live');
      return $this->db->get()->row();
    }
    /**
     * get total kitchen type items
     * @access public
     * @return object
     * @param int
     */
    public function get_total_kitchen_type_items($sale_id)
    {
        $this->db->select('id');
        $this->db->from('tbl_sales_details');
        $this->db->where("sales_id", $sale_id);
        $this->db->where('del_status', 'Live');
        return $this->db->get()->num_rows();
    }
    /**
     * get total kitchen type done items
     * @access public
     * @return object
     * @param int
     */
    public function get_total_kitchen_type_done_items($sale_id)
    {
        $this->db->select('id');
        $this->db->from('tbl_sales_details');
        $this->db->where("sales_id", $sale_id);
        $this->db->where("cooking_status", "Done");
        $this->db->where('del_status', 'Live');
        return $this->db->get()->num_rows();
    }
    /**
     * get total kitchen type started cooking items
     * @access public
     * @return object
     * @param int
     */
    public function get_total_kitchen_type_started_cooking_items($sale_id)
    {
        $this->db->select('id');
        $this->db->from('tbl_sales_details');
        $this->db->where("sales_id", $sale_id);
        $this->db->where("cooking_status", "Started Cooking");
        $this->db->where('del_status', 'Live');
        return $this->db->get()->num_rows();
    }
    /**
     * get Notification By Outlet Id
     * @access public
     * @return object
     * @param int
     */
    public function getNotificationByOutletId($outlet_id)
    {
        $designation = $this->session->userdata('designation');
        $user_id = $this->session->userdata('user_id');
      $this->db->select('*');
      $this->db->from('tbl_notifications');
        if($designation=="Waiter"){
            $this->db->where("waiter_id", $user_id);
        }
      $this->db->where("outlet_id", $outlet_id);
      $this->db->order_by('id', 'DESC');
      $result = $this->db->get();

        if($result != false){
          return $result->result();
        }else{
          return false;
        }
    }
    /**
     * get Notification By Outlet Id And User Id
     * @access public
     * @return object
     * @param int
     * @param int
     */
    public function getNotificationByOutletIdAndUserId($outlet_id,$user_id)
    {
      $this->db->select('*,tbl_notifications.id as notification_id');
      $this->db->from('tbl_notifications');
      $this->db->join('tbl_sales', 'tbl_sales.id = tbl_notifications.sale_id', 'left');
      $this->db->where("tbl_notifications.outlet_id", $outlet_id);
      $this->db->where("tbl_sales.waiter_id", $user_id);
      $this->db->order_by('tbl_notifications.id', 'ASC');
      $result = $this->db->get();

        if($result != false){
          return $result->result();
        }else{
          return false;
        }
    }
    /**
     * get Tables By Outlet Id
     * @access public
     * @return object
     * @param int
     */
    public function getTablesByOutletId($outlet_id) {
      $this->db->select('*');
      $this->db->from('tbl_tables');
      $this->db->where("outlet_id", $outlet_id);
      $this->db->order_by('id', 'ASC');
      $this->db->where("del_status", "Live");

      $result = $this->db->get();

        if($result != false){
          return $result->result();
        }else{
          return false;
        }
    }
    /**
     * get Orders Of Table By Table Id
     * @access public
     * @return object
     * @param int
     */
    public function getOrdersOfTableByTableId($table_id)
    {
      $this->db->select('*');
      $this->db->from('tbl_orders_table');
      $this->db->where("table_id", $table_id);
      $this->db->where("del_status", "Live");

      $result = $this->db->get();

        if($result != false){
          return $result->result();
        }else{
          return false;
        }
    }
    /**
     * get Table Availability
     * @access public
     * @return object
     * @param int
     */
    public function getTableAvailability($outlet_id)
    {
      $this->db->select('SUM(persons) as persons_number,table_id');
      $this->db->from('tbl_orders_table');
      $this->db->where("outlet_id", $outlet_id);
      $this->db->where("del_status", "Live");
      $this->db->group_by('table_id');

      $result = $this->db->get();

        if($result != false){
          return $result->result();
        }else{
          return false;
        }
    }
    /**
     * get all assets
     * @access public
     * @return object
     * @param int
     */
    public function get_all_assets($outlet_id)
    {
      $this->db->select('*');
      $this->db->from('tbl_assets');
      $this->db->where("outlet_id", $outlet_id);
      $this->db->where("del_status", 'Live');
      $result = $this->db->get();

        if($result != false){
          return $result->result();
        }else{
          return false;
        }
    }
    /**
     * get Games Of Asset By Asset Id
     * @access public
     * @return object
     * @param int
     */
    public function getGamesOfAssetByAssetId($asset_id)
    {
      $this->db->select('*,tbl_games.name');
      $this->db->from('tbl_assets_games');
      $this->db->join('tbl_games', 'tbl_games.id = tbl_assets_games.game_id', 'left');
      $this->db->where("asset_id", $asset_id);

      $result = $this->db->get();

        if($result != false){
          return $result->result();
        }else{
          return false;
        }
    }
    /**
     * get First User Information
     * @access public
     * @return object
     * @param int
     * @param string
     */
    public function getFirstUserInformationBy($outlet_id,$user_type)
    {
      $this->db->select('*');
      $this->db->from('tbl_users');
      $this->db->where("outlet_id", $outlet_id);
      $this->db->where("role", $user_type);
      $this->db->where("del_status", 'Live');
      $this->db->order_by('id', 'ASC');
      $this->db->limit(1);
      return $this->db->get()->row();
    }
    /**
     * get all tables of a sale items
     * @access public
     * @return object
     * @param int
     */
    public function get_all_tables_of_a_sale_items($sale_id)
    {
      $this->db->select('tbl_tables.name as table_name');
      $this->db->from('tbl_orders_table');
      $this->db->join('tbl_tables', 'tbl_tables.id = tbl_orders_table.table_id', 'left');
      $this->db->where("sale_id", $sale_id);
      $this->db->where("tbl_orders_table.del_status", 'Live');
      $result = $this->db->get();

        if($result != false){
          return $result->result();
        }else{
          return false;
        }

    }
    public function get_all_tables_of_a_sale_items_persons($sale_id)
    {
      $this->db->select('sum(persons) as total_persons');
      $this->db->from('tbl_orders_table');
      $this->db->join('tbl_tables', 'tbl_tables.id = tbl_orders_table.table_id', 'left');
      $this->db->where("sale_id", $sale_id);
      $this->db->where("tbl_orders_table.del_status", 'Live');
      $result = $this->db->get();
        if($result != false){
            $data =  $result->row();
            if(isset($data) && $data->total_persons){
                return $data->total_persons;
            }else{
                return 1;
            }
        }else{
            return 1;
        }
    }
    /**
     * get all tables of a sale items
     * @access public
     * @return object
     * @param int
     */
    public function get_all_tables_of_a_hold_items($hold_id)
    {
      $this->db->select('tbl_holds_table.*,tbl_tables.name as table_name');
      $this->db->from('tbl_holds_table');
      $this->db->join('tbl_tables', 'tbl_tables.id = tbl_holds_table.table_id', 'left');
      $this->db->where("hold_id", $hold_id);
      $result = $this->db->get();
        if($result != false){
          return $result->result();
        }else{
          return false;
        }

    }
    /**
     * get all tables of a last sale
     * @access public
     * @return object
     * @param int
     */
    public function get_all_tables_of_a_last_sale($sale_id)
    {
      $this->db->select('tbl_tables.name as table_name');
      $this->db->from('tbl_orders_table');
      $this->db->join('tbl_tables', 'tbl_tables.id = tbl_orders_table.table_id', 'left');
      $this->db->where("sale_id", $sale_id);
      $result = $this->db->get();

        if($result != false){
          return $result->result();
        }else{
          return false;
        }

    }
    /**
     * delete status orders table
     * @access public
     * @return object
     * @param int
     */
    public function delete_status_orders_table($sale_id)
    {
        $this->db->set('del_status', "Deleted");
        $this->db->where('sale_id', $sale_id);
        $this->db->update('tbl_orders_table');
    }
    /**
     * get Cash Method
     * @access public
     * @return object
     * @param no
     */
    public function getCashMethod()
    {
      $this->db->select('*');
      $this->db->from('tbl_payment_methods');
      $this->db->where("name", 'Cash');
      return $this->db->get()->row();
    }
    /**
     * get Running Orders By Outlet And Waiter Id
     * @access public
     * @return object
     * @param int
     * @param int
     */
    public function getRunningOrdersByOutletAndWaiterId($outlet_id,$waiter_id){
      $this->db->select("*,tbl_sales.id as sale_id,tbl_customers.name as customer_name, tbl_sales.id as sales_id,tbl_users.full_name as waiter_name,tbl_tables.name as table_name");
      $this->db->from('tbl_sales');
      $this->db->where("tbl_sales.outlet_id", $outlet_id);
      $this->db->where("tbl_sales.waiter_id", $waiter_id);
      $this->db->where("(order_status='1' OR order_status='2')");
      $this->db->join('tbl_tables', 'tbl_tables.id = tbl_sales.table_id', 'left');
      $this->db->join('tbl_users', 'tbl_users.id = tbl_sales.waiter_id', 'left');
      $this->db->join('tbl_customers', 'tbl_customers.id = tbl_sales.customer_id', 'left');
      $this->db->where('tbl_sales.del_status', 'Live');
      $this->db->order_by('tbl_sales.id', 'ASC');
      $result = $this->db->get();

        if($result != false){
          return $result->result();
        }else{
          return false;
        }
    }
    
    public function make_query($outlet_id){
      $this->db->select("tbl_sales.*,tbl_users.full_name,tbl_customers.name as customer_name,tbl_payment_methods.name as payment_method_name,tbl_customers.phone as customer_phone");
      $this->db->from('tbl_sales');
      $this->db->join('tbl_customers', 'tbl_customers.id = tbl_sales.customer_id', 'left');
      $this->db->join('tbl_users', 'tbl_users.id = tbl_sales.user_id', 'left');
      $this->db->join('tbl_payment_methods', 'tbl_payment_methods.id = tbl_sales.payment_method_id', 'left');
      // Busqueda global
      if (!empty($_POST["search"]["value"])) {
          $search = $_POST["search"]["value"];
          $this->db->group_start();
          $this->db->like("sale_no", $search);
          $this->db->or_like("tbl_customers.name", $search);
          $this->db->or_like("tbl_customers.phone", $search);
          $this->db->or_like("tbl_users.full_name", $search);
          $this->db->group_end();
      }
      $this->db->where("tbl_sales.outlet_id", $outlet_id);
      $this->db->where("tbl_sales.order_status", '3');
      $this->db->where("tbl_sales.del_status", "Live");
  
      // Orden dinámico desde DataTables
      if(isset($_POST['order'])){
          // Define aquí tus columnas de DataTables (ajusta a tus columnas visibles)
          $columns = [
              0 => 'tbl_sales.id',
              1 => 'tbl_sales.sale_no',
              2 => 'tbl_sales.order_type',
              3 => 'tbl_sales.sale_date',
              4 => 'tbl_customers.name',
              5 => 'tbl_sales.total_payable',
              6 => 'tbl_sales.total_refund',
              7 => 'tbl_payment_methods.name',
              8 => 'tbl_users.full_name'
          ];
          $colIndex = $_POST['order'][0]['column'];
          $colName = isset($columns[$colIndex]) ? $columns[$colIndex] : 'tbl_sales.id';
          $colDir = $_POST['order'][0]['dir'];
          $this->db->order_by($colName, $colDir);
      } else {
          $this->db->order_by('tbl_sales.id', 'DESC');
      }
  }
  
  public function make_datatables($outlet_id){
      $this->make_query($outlet_id);
      // Solo limita si length != -1
      if(isset($_POST["length"]) && $_POST["length"] != -1){
          $this->db->limit(intval($_POST["length"]), intval($_POST["start"]));
      }
      return $this->db->get()->result();
  }
  
  public function getDrawData() {
      return isset($_POST["draw"]) ? intval($_POST["draw"]) : 1;
  }
  
  public function get_filtered_data($outlet_id){
      $this->make_query($outlet_id);
      return $this->db->count_all_results();
  }
  
  public function get_all_data($outlet_id){
      $this->db->from('tbl_sales');
      $this->db->where("outlet_id", $outlet_id);
      $this->db->where("order_status", '3');
      $this->db->where("del_status", "Live");
      return $this->db->count_all_results();
  }
  
  public function getDetailedExpenses($from_datetime, $to_datetime, $user_id, $outlet_id) {
      $this->db->select("amount, note, payment_id, date, category_id, employee_id,added_date_time");
      $this->db->from("tbl_expenses");
      $this->db->where("del_status", "Live");
      $this->db->where("outlet_id", $outlet_id);
      $this->db->where("user_id", $user_id);
      // Rango de fechas/hora
      $this->db->where("added_date_time >=", $from_datetime);
      $this->db->where("added_date_time <=", $to_datetime);
      $query = $this->db->get();
      return $query->result();
  }

  public function getDetailedSales($outlet_id, $from_datetime, $to_datetime = null,$user_id = null) {
      if (!$user_id) {
          $user_id = $this->session->userdata('user_id');
      }
      $this->db->select("date_time, paid_date_time, number_slot_name, sale_no, paid_amount as amount");
      $this->db->from('tbl_sales');
      $this->db->where("outlet_id", $outlet_id);
      $this->db->where("user_id", $user_id);
      $this->db->where("paid_date_time >=", $from_datetime);
      if ($to_datetime) {
          $this->db->where("paid_date_time <=", $to_datetime);
      }
      $this->db->where('del_status', 'Live');
      $this->db->order_by('paid_date_time', 'asc');
      return $this->db->get()->result();
  }
}

