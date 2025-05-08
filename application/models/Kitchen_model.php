<?php

class Kitchen_model extends CI_Model {

     /**
     * get New Orders
     * @access public
     * @return object
     * @param int
     */
    public function getNewOrders($outlet_id, $kitchen_id = null) {
        $this->db->select("
                        s.id, s.customer_id, s.sale_no, s.number_slot, s.number_slot_name, 
                        s.total_items, s.sub_total, s.paid_amount, s.due_amount, s.disc, 
                        s.disc_actual, s.vat, s.total_payable, s.payment_method_id, s.close_time, 
                        s.table_id, s.total_item_discount_amount, s.sub_total_with_discount, 
                        s.sub_total_discount_amount, s.total_discount_amount, s.charge_type, 
                        s.delivery_charge, s.tips_amount, s.tips_amount_actual_charge, 
                        s.delivery_charge_actual_charge, s.sub_total_discount_value, 
                        s.sub_total_discount_type, s.sale_date, s.date_time, s.order_time, 
                        s.cooking_start_time, s.cooking_done_time, s.modified, s.user_id, 
                        s.waiter_id, s.outlet_id, s.company_id, s.order_status, s.order_type, 
                        s.del_status, s.is_merge, s.counter_id, s.is_accept, s.given_amount, 
                        s.change_amount, s.sale_vat_objects, s.future_sale_status, s.random_code, 
                        s.is_kitchen_bell, s.del_address, s.delivery_partner_id, 
                        s.rounding_amount_hidden, s.status, s.previous_due_tmp, 
                        s.used_loyalty_point, s.split_sale_id, s.orders_table_text, 
                        s.is_self_order, s.is_online_order, s.self_order_ran_code, 
                        s.self_order_status, s.token_number, s.is_pickup_sale, 
                        s.order_receiving_id, s.pull_update, s.is_delete_sender, 
                        s.is_delete_receiver, s.is_update_sender, s.is_update_receiver, 
                        s.online_self_order_receiving_id, s.is_update_receiver_admin, 
                        s.is_delete_receiver_admin, s.order_receiving_id_admin, s.pull_update_admin, 
                        s.pull_update_cashier, s.combo_items, s.online_payment_details, 
                        s.online_order_receiving_id, s.is_invoice, s.is_kitchen, s.zatca_value, 
                        s.last_update,
                          c.name as customer_name, 
                          c.phone as customer_phone,
                          c.email as customer_email,
                          c.address as customer_address,
                          s.id as sales_id,
                          u.full_name as waiter_name,
                          t.name as table_name,
                          (SELECT COUNT(*) FROM tbl_kitchen_sales_details sd 
                           WHERE sd.sales_id = s.id) as total_kitchen_type_items,
                          (SELECT COUNT(*) FROM tbl_kitchen_sales_details sd 
                           WHERE sd.sales_id = s.id AND sd.cooking_status = 'Done') as total_kitchen_type_done_items,
                          (SELECT COUNT(*) FROM tbl_kitchen_sales_details sd 
                           WHERE sd.sales_id = s.id AND sd.cooking_status = 'Started Cooking') as total_kitchen_type_started_cooking_items,
                          (SELECT GROUP_CONCAT(DISTINCT ot.table_id) FROM tbl_orders_table ot 
                           WHERE ot.sale_id = s.id) as table_ids");
        
        $this->db->from('tbl_kitchen_sales s');
        $this->db->where("s.is_self_order", "No");
        $this->db->where("s.outlet_id", $outlet_id);
        $this->db->where("(s.order_status='1' OR s.order_status='2')");
        $this->db->where("s.is_accept", 1);
        $this->db->where("s.date_time >=", "DATE_SUB(NOW(), INTERVAL 6 HOUR)", false);
        
        $this->db->join('tbl_tables t', 't.id = s.table_id', 'left');
        $this->db->join('tbl_users u', 'u.id = s.waiter_id', 'left');
        $this->db->join('tbl_customers c', 'c.id = s.customer_id', 'left');
        
        if ($kitchen_id) {
            // Cambiamos el JOIN por una subconsulta en el WHERE
            $this->db->where("EXISTS (
                SELECT 1 FROM tbl_kitchen_sales_details sd
                JOIN tbl_food_menus fm ON fm.id = sd.food_menu_id
                JOIN tbl_kitchen_categories kc ON kc.cat_id = fm.category_id AND kc.outlet_id = $outlet_id
                JOIN tbl_kitchens k ON k.id = kc.kitchen_id AND k.outlet_id = $outlet_id
                WHERE sd.sales_id = s.id 
                AND k.id = $kitchen_id
                AND sd.cooking_status != 'Done'
            )");
        }
        
        $this->db->order_by('s.id', 'ASC');
        return $this->db->get()->result();
    }

     public function getNewOrdersOld($outlet_id, $kitchen_id = null){
        $this->db->select("*,tbl_kitchen_sales.id as sale_id, tbl_customers.name as customer_name, tbl_kitchen_sales.id as sales_id,tbl_users.full_name as waiter_name,tbl_tables.name as table_name");
        $this->db->from('tbl_kitchen_sales');
        $this->db->where("tbl_kitchen_sales.is_self_order", "No");
        $this->db->where("tbl_kitchen_sales.outlet_id", $outlet_id);
        $this->db->where("(order_status='1' OR order_status='2')");
        $this->db->where("tbl_kitchen_sales.is_accept", 1);
        $this->db->join('tbl_tables', 'tbl_tables.id = tbl_kitchen_sales.table_id', 'left');
        $this->db->join('tbl_users', 'tbl_users.id = tbl_kitchen_sales.waiter_id', 'left');
        $this->db->join('tbl_customers', 'tbl_customers.id = tbl_kitchen_sales.customer_id', 'left');
        // Filtro de las últimas 6 horas
        $this->db->where("tbl_kitchen_sales.date_time >=", "DATE_SUB(NOW(), INTERVAL 6 HOUR)", false);
    
        // FILTRO CLAVE: Solo órdenes con items para esta cocina y outlet
        if ($kitchen_id) {
            $this->db->where("EXISTS (
                SELECT 1 FROM tbl_kitchen_sales_details
                LEFT JOIN tbl_food_menus ON tbl_food_menus.id = tbl_kitchen_sales_details.food_menu_id
                LEFT JOIN tbl_kitchen_categories ON tbl_kitchen_categories.cat_id = tbl_food_menus.category_id
                    AND tbl_kitchen_categories.outlet_id = " . intval($outlet_id) . "
                LEFT JOIN tbl_kitchens ON tbl_kitchens.id = tbl_kitchen_categories.kitchen_id
                    AND tbl_kitchens.outlet_id = " . intval($outlet_id) . "
                WHERE tbl_kitchen_sales_details.sales_id = tbl_kitchen_sales.id
                AND tbl_kitchens.id = " . intval($kitchen_id) . "
                AND tbl_kitchen_sales_details.cooking_status != 'Done'
            )");
        }
    
        $this->db->order_by('tbl_kitchen_sales.id', 'ASC');
        return $this->db->get()->result();
    }

    public function getKitchenItemsWithModifiers($sales_ids, $kitchen_id) {
        if (empty($sales_ids)) return [];
        
        $this->db->select('sd.*, fm.name as menu_name, 
                      GROUP_CONCAT(DISTINCT CONCAT(m.id, ":::", m.name)) as modifiers');
        $this->db->from('tbl_kitchen_sales_details sd');
        $this->db->join('tbl_food_menus fm', 'fm.id = sd.food_menu_id', 'left');
        $this->db->join('tbl_kitchen_sales_details_modifiers sdm', 'sdm.sales_details_id = sd.id AND sdm.sales_id = sd.sales_id', 'left');
        $this->db->join('tbl_modifiers m', 'm.id = sdm.modifier_id', 'left');
        $this->db->where_in('sd.sales_id', $sales_ids);
        
        if ($kitchen_id) {
            $this->db->join('tbl_kitchen_categories kc', 'kc.cat_id = fm.category_id', 'left');
            $this->db->join('tbl_kitchens k', 'k.id = kc.kitchen_id', 'left');
            $this->db->where('k.id', $kitchen_id);
        }
        
        $this->db->group_by('sd.id');
        $this->db->order_by('sd.sales_id, sd.id', 'ASC');
        
        $result = $this->db->get()->result();
        
        // Procesar los modificadores
        foreach ($result as &$item) {
            $modifiers = [];
            if (!empty($item->modifiers)) {
                $mods = explode(',', $item->modifiers);
                foreach ($mods as $mod) {
                    if (!empty($mod)) {
                        list($id, $name) = explode(':::', $mod);
                        $modifiers[] = (object)['id' => $id, 'name' => $name];
                    }
                }
            }
            $item->modifiers = $modifiers;
        }
        
        return $result;
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
        $this->db->from('tbl_kitchen_sales_details');
        $this->db->where("sales_id", $sale_id);
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
        $this->db->from('tbl_kitchen_sales_details');
        $this->db->where("sales_id", $sale_id);
        $this->db->where("cooking_status", "Done");
        return $this->db->get()->num_rows();    
    }
    public function get_all_kitchen_items($sale_id)
    {
        $this->db->select('*');
        $this->db->from('tbl_kitchen_sales_details');
        $this->db->where("sales_id", $sale_id);
        $this->db->where("del_status", "Live");
        return $this->db->get()->result();
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
        $this->db->from('tbl_kitchen_sales_details');
        $this->db->where("sales_id", $sale_id);
        $this->db->where("cooking_status", "Started Cooking");
        return $this->db->get()->num_rows();    
    }
     /**
     * get Item Info By Previous Id
     * @access public
     * @return object
     * @param int
     */
    public function getItemInfoByPreviousId($previous_id)
    {
        $this->db->select('tbl_kitchen_sales_details.*,tbl_food_menus.code as code,tbl_food_menus.name as menu_name');
        $this->db->from('tbl_kitchen_sales_details');
        $this->db->join('tbl_food_menus', 'tbl_food_menus.id = tbl_kitchen_sales_details.food_menu_id', 'left');
        $this->db->where("previous_id", $previous_id);
        return $this->db->get()->row();   
    }
     /**
     * get Sale By Sale Id
     * @access public
     * @return object
     * @param int
     */
    public function getSaleBySaleId($sales_id){
        $this->db->select("tbl_kitchen_sales.*,tbl_users.full_name as waiter_name,tbl_customers.name as customer_name,tbl_tables.name as table_name,tbl_users.full_name as user_name");
        $this->db->from('tbl_kitchen_sales');
        $this->db->join('tbl_customers', 'tbl_customers.id = tbl_kitchen_sales.customer_id', 'left');
        $this->db->join('tbl_users', 'tbl_users.id = tbl_kitchen_sales.user_id', 'left');
        $this->db->join('tbl_tables', 'tbl_tables.id = tbl_kitchen_sales.table_id', 'left');
        $this->db->join('tbl_users w', 'w.id = tbl_kitchen_sales.waiter_id', 'left');
        $this->db->where("tbl_kitchen_sales.id", $sales_id);
        $this->db->order_by('tbl_kitchen_sales.id', 'ASC');
        return $this->db->get()->result();
    }
     /**
     * get All Kitchen Items From Sales Detail By Sales Id
     * @access public
     * @return object
     * @param int
     */
    public function getAllKitchenItemsFromSalesDetailBySalesId($sales_id, $kitchen_id) {
        $this->db->select('sd.*, 
                        sd.id as sales_details_id,  
                          fm.name as menu_name,
                          (SELECT GROUP_CONCAT(CONCAT(m.id, ":::", m.name)) 
                           FROM tbl_kitchen_sales_details_modifiers sdm
                           JOIN tbl_modifiers m ON m.id = sdm.modifier_id
                           WHERE sdm.sales_id = sd.sales_id AND sdm.sales_details_id = sd.id) as modifiers_str');
        
        $this->db->from('tbl_kitchen_sales_details sd');
        $this->db->join('tbl_food_menus fm', 'fm.id = sd.food_menu_id', 'left');
        $this->db->where('sd.sales_id', $sales_id);
        
        if ($kitchen_id) {
            $this->db->join('tbl_kitchen_categories kc', 'kc.cat_id = fm.category_id', 'left');
            $this->db->join('tbl_kitchens k', 'k.id = kc.kitchen_id', 'left');
            $this->db->where("kc.del_status", "Live");
            $this->db->where('k.id', $kitchen_id);
        }
        
        $this->db->group_by('sd.id');
        $this->db->order_by('sd.id', 'ASC');
        $result = $this->db->get()->result();
        
        // Procesar los modificadores
        foreach ($result as &$item) {
            $modifiers = [];
            if (!empty($item->modifiers_str)) {
                $mods = explode(',', $item->modifiers_str);
                foreach ($mods as $mod) {
                    if (!empty($mod)) {
                        list($id, $name) = explode(':::', $mod);
                        $modifiers[] = (object)['id' => $id, 'name' => $name];
                    }
                }
            }
            $item->modifiers = $modifiers;
            unset($item->modifiers_str); // Eliminamos el campo temporal
        }
        
        return $result;
    }

    public function getAllKitchenItemsFromSalesDetailBySalesIdOld($sales_id,$kitchen_id){
        $this->db->select("tbl_kitchen_sales_details.*,tbl_kitchen_sales_details.id as sales_details_id,tbl_food_menus.code as code,tbl_food_menus.alternative_name,tbl_kitchen_categories.kitchen_id");
        $this->db->from('tbl_kitchen_sales_details');
        $this->db->join('tbl_food_menus', 'tbl_food_menus.id = tbl_kitchen_sales_details.food_menu_id', 'left');
        $this->db->join('tbl_kitchen_categories', 'tbl_kitchen_categories.cat_id = tbl_food_menus.category_id', 'left');
        $this->db->where("sales_id", $sales_id);
        $this->db->where("tbl_kitchen_categories.kitchen_id", $kitchen_id);
        $this->db->where("tbl_kitchen_sales_details.cooking_status!=", "Done");
        $this->db->where("tbl_kitchen_categories.del_status", "Live");
        $this->db->order_by('tbl_kitchen_sales_details.id', 'ASC');
        $data =  $this->db->get()->result();
        return $data;
    }
     /**
     * get Modifiers By Sale And Sale Details Id
     * @access public
     * @return object
     * @param int
     * @param int
     */
    public function getModifiersBySaleAndSaleDetailsId($sales_id,$sale_details_id){
        $this->db->select("tbl_kitchen_sales_details_modifiers.*,tbl_modifiers.name");
        $this->db->from('tbl_kitchen_sales_details_modifiers');
        $this->db->join('tbl_modifiers', 'tbl_modifiers.id = tbl_kitchen_sales_details_modifiers.modifier_id', 'left');
        $this->db->where("tbl_kitchen_sales_details_modifiers.sales_id", $sales_id);
        $this->db->where("tbl_kitchen_sales_details_modifiers.sales_details_id", $sale_details_id);
        $this->db->order_by('tbl_kitchen_sales_details_modifiers.id', 'ASC');
        return $this->db->get()->result(); 
    }
     /**
     * get Notification By Outlet Id
     * @access public
     * @return object
     * @param int
     */
    public function getNotificationByOutletId($outlet_id,$kitchen_id)
    {
      $this->db->select('*');
      $this->db->from('tbl_notification_bar_kitchen_panel');
      $this->db->where("outlet_id", $outlet_id);
      $this->db->where("kitchen_id", $kitchen_id);
      $this->db->order_by('id', 'DESC');
      return $this->db->get()->result(); 
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
      // $this->db->where("tbl_orders_table.del_status", 'Live');
      return $this->db->get()->result();
      
    }

}

