<?php
class Inventory_model extends CI_Model {

    /**
     * get Data By Cat Id
     * @access public
     * @return object
     * @param int
     * @param string
     */
    public function getDataByCatId($cat_id, $table_name) {
        $this->db->select("id,name,code");
        $this->db->from($table_name);
        $this->db->where("category_id", $cat_id);
        $this->db->order_by("name", "ASC");
        $this->db->where("del_status", 'Live');
        return $this->db->get()->result();
    }
    /**
     * get Inventory
     * @access public
     * @return object
     * @param string
     * @param string
     * @param string
     */
    public function getInventory($category_id = "", $ingredient_id = "", $food_id = "") {

        $outlet_id = $this->session->userdata('outlet_id');
        $company_id = $this->session->userdata('company_id');

        $where = '';
        $where1 = '';
        if($food_id!=''){
            $food = foodMenuRow($food_id);
            if($food->product_type==2){
                $getFMIds = $food->combo_ids;
            }else{
                $getFMIds = $food_id;
            }
        }else{
            $getFMIds = getFMIds($outlet_id);
        }
        if($category_id!=''){
            $where1.= "  AND ingr_tbl.category_id = '$category_id'";
        }
        if($ingredient_id!=''){
            $where1.= "  AND i.id = '$ingredient_id'";
        }
        //get selected food menu ids
        if($food_id){
            $result = $this->db->query("SELECT ingr_tbl.*,i.food_menu_id,ingr_cat_tbl.category_name,ingr_unit_tbl.unit_name, ingr_unit_tbl2.unit_name as unit_name2, 
                    (select SUM(quantity_amount) from tbl_purchase_ingredients where ingredient_id=i.ingredient_id AND outlet_id=$outlet_id AND del_status='Live') total_purchase, 
                    (select SUM(consumption) from tbl_sale_consumptions_of_menus where ingredient_id=i.ingredient_id AND outlet_id=$outlet_id AND del_status='Live') total_consumption,
                    (select SUM(consumption) from tbl_sale_consumptions_of_modifiers_of_menus where ingredient_id=i.ingredient_id AND outlet_id=$outlet_id AND  del_status='Live') total_modifiers_consumption,
                    (select SUM(waste_amount) from tbl_waste_ingredients  where ingredient_id=i.ingredient_id AND outlet_id=$outlet_id AND tbl_waste_ingredients.del_status='Live') total_waste,
                    (select SUM(consumption_amount) from tbl_inventory_adjustment_ingredients  where ingredient_id=i.ingredient_id AND outlet_id=$outlet_id AND  tbl_inventory_adjustment_ingredients.del_status='Live' AND  tbl_inventory_adjustment_ingredients.consumption_status='Plus') total_consumption_plus,
                    (select SUM(consumption_amount) from tbl_inventory_adjustment_ingredients  where ingredient_id=i.ingredient_id AND outlet_id=$outlet_id AND  tbl_inventory_adjustment_ingredients.del_status='Live' AND  tbl_inventory_adjustment_ingredients.consumption_status='Minus') total_consumption_minus,
                    (select SUM(quantity_amount) from tbl_production_ingredients  where ingredient_id=i.id AND outlet_id=$outlet_id AND  tbl_production_ingredients.del_status='Live' AND tbl_production_ingredients.status=1) total_production,
                    (select SUM(quantity_amount) from tbl_transfer_ingredients  where ingredient_id=i.id AND to_outlet_id=$outlet_id AND  tbl_transfer_ingredients.del_status='Live' AND  tbl_transfer_ingredients.status=1 AND tbl_transfer_ingredients.transfer_type=1) total_transfer_plus,
                    (select SUM(quantity_amount) from tbl_transfer_ingredients  where ingredient_id=i.id AND from_outlet_id=$outlet_id AND  tbl_transfer_ingredients.del_status='Live' AND (tbl_transfer_ingredients.status=1) AND tbl_transfer_ingredients.transfer_type=1) total_transfer_minus,
                    (select SUM(quantity_amount) from tbl_transfer_received_ingredients  where ingredient_id=i.id AND to_outlet_id=$outlet_id AND  tbl_transfer_received_ingredients.del_status='Live' AND  tbl_transfer_received_ingredients.status=1) total_transfer_plus_2,
                    (select SUM(quantity_amount) from tbl_transfer_received_ingredients  where ingredient_id=i.id AND from_outlet_id=$outlet_id AND  tbl_transfer_received_ingredients.del_status='Live' AND (tbl_transfer_received_ingredients.status=1)) total_transfer_minus_2
                    FROM tbl_food_menus_ingredients i  LEFT JOIN (select * from tbl_ingredients where del_status='Live') ingr_tbl ON ingr_tbl.id = i.ingredient_id LEFT JOIN (select * from tbl_ingredient_categories where del_status='Live') ingr_cat_tbl ON ingr_cat_tbl.id = ingr_tbl.category_id LEFT JOIN (select * from tbl_units where del_status='Live') ingr_unit_tbl ON ingr_unit_tbl.id = ingr_tbl.unit_id  LEFT JOIN (select * from tbl_units where del_status='Live') ingr_unit_tbl2 ON ingr_unit_tbl2.id = ingr_tbl.purchase_unit_id WHERE FIND_IN_SET(`food_menu_id`, '$getFMIds') AND i.company_id= '$company_id' AND i.del_status='Live' $where  GROUP BY i.ingredient_id")->result();
            return $result;
        }else{
            $result = $this->db->query("SELECT ingr_tbl.*,i.id as food_menu_id,ingr_cat_tbl.category_name,ingr_unit_tbl.unit_name, ingr_unit_tbl2.unit_name as unit_name2, 
                    (select SUM(quantity_amount) from tbl_purchase_ingredients where ingredient_id=i.id AND outlet_id=$outlet_id AND del_status='Live') total_purchase, 
                    (select SUM(consumption) from tbl_sale_consumptions_of_menus where ingredient_id=i.id AND outlet_id=$outlet_id AND del_status='Live') total_consumption,
                    (select SUM(consumption) from tbl_sale_consumptions_of_modifiers_of_menus where ingredient_id=i.id AND outlet_id=$outlet_id AND  del_status='Live') total_modifiers_consumption,
                    (select SUM(waste_amount) from tbl_waste_ingredients  where ingredient_id=i.id AND outlet_id=$outlet_id AND tbl_waste_ingredients.del_status='Live') total_waste,
                    (select SUM(consumption_amount) from tbl_inventory_adjustment_ingredients  where ingredient_id=i.id AND outlet_id=$outlet_id AND  tbl_inventory_adjustment_ingredients.del_status='Live' AND  tbl_inventory_adjustment_ingredients.consumption_status='Plus') total_consumption_plus,
                    (select SUM(consumption_amount) from tbl_inventory_adjustment_ingredients  where ingredient_id=i.id AND outlet_id=$outlet_id AND  tbl_inventory_adjustment_ingredients.del_status='Live' AND  tbl_inventory_adjustment_ingredients.consumption_status='Minus') total_consumption_minus,
                    (select SUM(quantity_amount) from tbl_production_ingredients  where ingredient_id=i.id AND outlet_id=$outlet_id AND  tbl_production_ingredients.del_status='Live' AND tbl_production_ingredients.status=1) total_production,
                    (select SUM(quantity_amount) from tbl_transfer_ingredients  where ingredient_id=i.id AND to_outlet_id=$outlet_id AND  tbl_transfer_ingredients.del_status='Live' AND tbl_transfer_ingredients.status=1  AND tbl_transfer_ingredients.transfer_type=1) total_transfer_plus,
                    (select SUM(quantity_amount) from tbl_transfer_ingredients  where ingredient_id=i.id AND from_outlet_id=$outlet_id AND  tbl_transfer_ingredients.del_status='Live' AND (tbl_transfer_ingredients.status=1) AND tbl_transfer_ingredients.transfer_type=1) total_transfer_minus,
                    (select SUM(quantity_amount) from tbl_transfer_received_ingredients  where ingredient_id=i.id AND to_outlet_id=$outlet_id AND  tbl_transfer_received_ingredients.del_status='Live' AND  tbl_transfer_received_ingredients.status=1) total_transfer_plus_2,
                    (select SUM(quantity_amount) from tbl_transfer_received_ingredients  where ingredient_id=i.id AND from_outlet_id=$outlet_id AND  tbl_transfer_received_ingredients.del_status='Live' AND (tbl_transfer_received_ingredients.status=1)) total_transfer_minus_2
                    FROM tbl_ingredients i  LEFT JOIN (select * from tbl_ingredients where del_status='Live') ingr_tbl ON ingr_tbl.id = i.id LEFT JOIN (select * from tbl_ingredient_categories where del_status='Live') ingr_cat_tbl ON ingr_cat_tbl.id = ingr_tbl.category_id LEFT JOIN (select * from tbl_units where del_status='Live') ingr_unit_tbl ON ingr_unit_tbl.id = ingr_tbl.unit_id LEFT JOIN (select * from tbl_units where del_status='Live') ingr_unit_tbl2 ON ingr_unit_tbl2.id = ingr_tbl.purchase_unit_id  WHERE i.company_id= '$company_id' AND i.del_status='Live' $where1 GROUP BY i.id")->result();

            return $result;
        }

    }
    public function getCurrentInventory($ingredient_id) {

        $outlet_id = $this->session->userdata('outlet_id');
        $company_id = $this->session->userdata('company_id');

        $where1 = '';
        if($ingredient_id!=''){
            $where1.= "  AND i.id = '$ingredient_id'";
        }
        $value = $this->db->query("SELECT ingr_tbl.*,i.id as food_menu_id,ingr_cat_tbl.category_name,ingr_unit_tbl.unit_name, ingr_unit_tbl2.unit_name as unit_name2, (select SUM(quantity_amount) from tbl_purchase_ingredients where ingredient_id=i.id AND outlet_id=$outlet_id AND del_status='Live') total_purchase, 
        (select SUM(consumption) from tbl_sale_consumptions_of_menus where ingredient_id=i.id AND outlet_id=$outlet_id AND del_status='Live') total_consumption,
        (select SUM(consumption) from tbl_sale_consumptions_of_modifiers_of_menus where ingredient_id=i.id AND outlet_id=$outlet_id AND  del_status='Live') total_modifiers_consumption,
        (select SUM(waste_amount) from tbl_waste_ingredients  where ingredient_id=i.id AND outlet_id=$outlet_id AND tbl_waste_ingredients.del_status='Live') total_waste,
        (select SUM(consumption_amount) from tbl_inventory_adjustment_ingredients  where ingredient_id=i.id AND outlet_id=$outlet_id AND  tbl_inventory_adjustment_ingredients.del_status='Live' AND  tbl_inventory_adjustment_ingredients.consumption_status='Plus') total_consumption_plus,
        (select SUM(consumption_amount) from tbl_inventory_adjustment_ingredients  where ingredient_id=i.id AND outlet_id=$outlet_id AND  tbl_inventory_adjustment_ingredients.del_status='Live' AND  tbl_inventory_adjustment_ingredients.consumption_status='Minus') total_consumption_minus,
        (select SUM(quantity_amount) from tbl_production_ingredients  where ingredient_id=i.id AND outlet_id=$outlet_id AND  tbl_production_ingredients.del_status='Live' AND tbl_production_ingredients.status=1) total_production,
        (select SUM(quantity_amount) from tbl_transfer_ingredients  where ingredient_id=i.id AND to_outlet_id=$outlet_id AND  tbl_transfer_ingredients.del_status='Live' AND tbl_transfer_ingredients.status=1  AND tbl_transfer_ingredients.transfer_type=1) total_transfer_plus,
        (select SUM(quantity_amount) from tbl_transfer_ingredients  where ingredient_id=i.id AND from_outlet_id=$outlet_id AND  tbl_transfer_ingredients.del_status='Live' AND (tbl_transfer_ingredients.status=1) AND tbl_transfer_ingredients.transfer_type=1) total_transfer_minus,
        (select SUM(quantity_amount) from tbl_transfer_received_ingredients  where ingredient_id=i.id AND to_outlet_id=$outlet_id AND  tbl_transfer_received_ingredients.del_status='Live' AND  tbl_transfer_received_ingredients.status=1) total_transfer_plus_2,
        (select SUM(quantity_amount) from tbl_transfer_received_ingredients  where ingredient_id=i.id AND from_outlet_id=$outlet_id AND  tbl_transfer_received_ingredients.del_status='Live' AND (tbl_transfer_received_ingredients.status=1)) total_transfer_minus_2
        FROM tbl_ingredients i  LEFT JOIN (select * from tbl_ingredients where del_status='Live') ingr_tbl ON ingr_tbl.id = i.id LEFT JOIN (select * from tbl_ingredient_categories where del_status='Live') ingr_cat_tbl ON ingr_cat_tbl.id = ingr_tbl.category_id LEFT JOIN (select * from tbl_units where del_status='Live') ingr_unit_tbl ON ingr_unit_tbl.id = ingr_tbl.unit_id LEFT JOIN (select * from tbl_units where del_status='Live') ingr_unit_tbl2 ON ingr_unit_tbl2.id = ingr_tbl.purchase_unit_id  WHERE i.company_id= '$company_id' AND i.del_status='Live' $where1 GROUP BY i.id")->row();

        $conversion_rate = (int)$value->conversion_rate?$value->conversion_rate:1;
        $new_stock = 0;
        if($value->id):
            $totalStock = ($value->total_purchase*$value->conversion_rate)  - $value->total_consumption - $value->total_modifiers_consumption - $value->total_waste + $value->total_consumption_plus - $value->total_consumption_minus + ($value->total_transfer_plus*$value->conversion_rate) - ($value->total_transfer_minus*$value->conversion_rate)  +  ($value->total_transfer_plus_2*$value->conversion_rate) -  ($value->total_transfer_minus_2*$value->conversion_rate)+ ($value->total_production*$value->conversion_rate);
            if($value->conversion_rate==0 || $value->conversion_rate==''){
                $total_sale_unit = isset($value->conversion_rate) && (int)$value->conversion_rate?(float)($totalStock/1):'0';
            }else{
                $total_sale_unit = isset($value->conversion_rate) && (int)$value->conversion_rate?(float)($totalStock/$value->conversion_rate):'0';
            }
            $new_stock = (float)($total_sale_unit.".".$totalStock%$conversion_rate);
        endif;
        $return_array = array();
        $return_array['total_stock'] = $new_stock;
        $return_array['stock_unit'] = $value->unit_name2;
        return $return_array;

    }
    public function getCurrentInventoryByOutlet($ingredient_id, $outlet_id) {

        $company_id = $this->session->userdata('company_id');

        $where1 = '';
        if($ingredient_id!=''){
            $where1.= "  AND i.id = '$ingredient_id'";
        }
        $value = $this->db->query("SELECT ingr_tbl.*,i.id as food_menu_id,ingr_cat_tbl.category_name,ingr_unit_tbl.unit_name, ingr_unit_tbl2.unit_name as unit_name2, (select SUM(quantity_amount) from tbl_purchase_ingredients where ingredient_id=i.id AND outlet_id=$outlet_id AND del_status='Live') total_purchase, 
        (select SUM(consumption) from tbl_sale_consumptions_of_menus where ingredient_id=i.id AND outlet_id=$outlet_id AND del_status='Live') total_consumption,
        (select SUM(consumption) from tbl_sale_consumptions_of_modifiers_of_menus where ingredient_id=i.id AND outlet_id=$outlet_id AND  del_status='Live') total_modifiers_consumption,
        (select SUM(waste_amount) from tbl_waste_ingredients  where ingredient_id=i.id AND outlet_id=$outlet_id AND tbl_waste_ingredients.del_status='Live') total_waste,
        (select SUM(consumption_amount) from tbl_inventory_adjustment_ingredients  where ingredient_id=i.id AND outlet_id=$outlet_id AND  tbl_inventory_adjustment_ingredients.del_status='Live' AND  tbl_inventory_adjustment_ingredients.consumption_status='Plus') total_consumption_plus,
        (select SUM(consumption_amount) from tbl_inventory_adjustment_ingredients  where ingredient_id=i.id AND outlet_id=$outlet_id AND  tbl_inventory_adjustment_ingredients.del_status='Live' AND  tbl_inventory_adjustment_ingredients.consumption_status='Minus') total_consumption_minus,
        (select SUM(quantity_amount) from tbl_production_ingredients  where ingredient_id=i.id AND outlet_id=$outlet_id AND  tbl_production_ingredients.del_status='Live' AND tbl_production_ingredients.status=1) total_production,
        (select SUM(quantity_amount) from tbl_transfer_ingredients  where ingredient_id=i.id AND to_outlet_id=$outlet_id AND  tbl_transfer_ingredients.del_status='Live' AND tbl_transfer_ingredients.status=1  AND tbl_transfer_ingredients.transfer_type=1) total_transfer_plus,
        (select SUM(quantity_amount) from tbl_transfer_ingredients  where ingredient_id=i.id AND from_outlet_id=$outlet_id AND  tbl_transfer_ingredients.del_status='Live' AND (tbl_transfer_ingredients.status=1) AND tbl_transfer_ingredients.transfer_type=1) total_transfer_minus,
        (select SUM(quantity_amount) from tbl_transfer_received_ingredients  where ingredient_id=i.id AND to_outlet_id=$outlet_id AND  tbl_transfer_received_ingredients.del_status='Live' AND  tbl_transfer_received_ingredients.status=1) total_transfer_plus_2,
        (select SUM(quantity_amount) from tbl_transfer_received_ingredients  where ingredient_id=i.id AND from_outlet_id=$outlet_id AND  tbl_transfer_received_ingredients.del_status='Live' AND (tbl_transfer_received_ingredients.status=1)) total_transfer_minus_2
        FROM tbl_ingredients i  LEFT JOIN (select * from tbl_ingredients where del_status='Live') ingr_tbl ON ingr_tbl.id = i.id LEFT JOIN (select * from tbl_ingredient_categories where del_status='Live') ingr_cat_tbl ON ingr_cat_tbl.id = ingr_tbl.category_id LEFT JOIN (select * from tbl_units where del_status='Live') ingr_unit_tbl ON ingr_unit_tbl.id = ingr_tbl.unit_id LEFT JOIN (select * from tbl_units where del_status='Live') ingr_unit_tbl2 ON ingr_unit_tbl2.id = ingr_tbl.purchase_unit_id  WHERE i.company_id= '$company_id' AND i.del_status='Live' $where1 GROUP BY i.id")->row();

        $conversion_rate = (int)$value->conversion_rate?$value->conversion_rate:1;
        $new_stock = 0;
        if($value->id):
            $totalStock = ($value->total_purchase*$value->conversion_rate)  - $value->total_consumption - $value->total_modifiers_consumption - $value->total_waste + $value->total_consumption_plus - $value->total_consumption_minus + ($value->total_transfer_plus*$value->conversion_rate) - ($value->total_transfer_minus*$value->conversion_rate)  +  ($value->total_transfer_plus_2*$value->conversion_rate) -  ($value->total_transfer_minus_2*$value->conversion_rate)+ ($value->total_production*$value->conversion_rate);
            if($value->conversion_rate==0 || $value->conversion_rate==''){
                $total_sale_unit = isset($value->conversion_rate) && (int)$value->conversion_rate?(int)($totalStock/1):'0';
            }else{
                $total_sale_unit = isset($value->conversion_rate) && (int)$value->conversion_rate?(int)($totalStock/$value->conversion_rate):'0';
            }
            $new_stock = (float)($total_sale_unit.".".$totalStock%$conversion_rate);
        endif;
        $return_array = array();
        $return_array['total_stock'] = $new_stock;
        $return_array['stock_unit'] = $value->unit_name2;
        return $return_array;

    }
    public function getIngredientMovements($ingredient_id, $limit, $offset, $start_date = '', $end_date = '') {
        $company_id = $this->session->userdata('company_id');
        $where_date = '';
        $where_purchase = $start_date && $end_date ? " AND p.date BETWEEN '$start_date' AND '$end_date'" : '';
        $where_sale = $start_date && $end_date ? " AND s.date_time BETWEEN '$start_date' AND '$end_date'" : '';
        $where_waste = $start_date && $end_date ? " AND w.date BETWEEN '$start_date' AND '$end_date'" : '';
        $where_adjustment = $start_date && $end_date ? " AND ia.date BETWEEN '$start_date' AND '$end_date'" : '';
        $where_production = $start_date && $end_date ? " AND p.date BETWEEN '$start_date' AND '$end_date'" : '';
        $where_transfer = $start_date && $end_date ? " AND t.date BETWEEN '$start_date' AND '$end_date'" : '';

        $query = "
            (SELECT 'Compra' COLLATE utf8mb4_general_ci as type, ROUND(pi.quantity_amount, 2) as quantity, p.date, CONCAT('Compra de ', pi.quantity_amount, ' unidades') COLLATE utf8mb4_general_ci as description, p.id as movement_id
             FROM tbl_purchase_ingredients pi
             JOIN tbl_purchase p ON pi.purchase_id = p.id
             WHERE pi.ingredient_id = $ingredient_id AND pi.del_status = 'Live' $where_purchase)
            UNION ALL
            (SELECT 'Consumo' COLLATE utf8mb4_general_ci as type, ROUND(-sc.consumption, 2) as quantity, s.date_time as date, CONCAT('Venta: ', fm.name COLLATE utf8mb4_general_ci) COLLATE utf8mb4_general_ci as description, s.id as movement_id
             FROM tbl_sale_consumptions_of_menus sc
             JOIN tbl_sales s ON sc.sales_id = s.id
             JOIN tbl_food_menus fm ON sc.food_menu_id = fm.id
             WHERE sc.ingredient_id = $ingredient_id AND sc.del_status = 'Live' $where_sale)
            UNION ALL
            (SELECT 'Consumo Modificador' COLLATE utf8mb4_general_ci as type, ROUND(-scm.consumption, 2) as quantity, s.date_time as date, CONCAT('Modificador en venta: ', fm.name COLLATE utf8mb4_general_ci) COLLATE utf8mb4_general_ci as description, s.id as movement_id
             FROM tbl_sale_consumptions_of_modifiers_of_menus scm
             JOIN tbl_sales s ON scm.sales_id = s.id
             JOIN tbl_food_menus fm ON scm.food_menu_id = fm.id
             WHERE scm.ingredient_id = $ingredient_id AND scm.del_status = 'Live' $where_sale)
            UNION ALL
            (SELECT 'Desperdicio' COLLATE utf8mb4_general_ci as type, ROUND(-wi.waste_amount, 2) as quantity, w.date, 'Desperdicio' COLLATE utf8mb4_general_ci as description, w.id as movement_id
             FROM tbl_waste_ingredients wi
             JOIN tbl_wastes w ON wi.waste_id = w.id
             WHERE wi.ingredient_id = $ingredient_id AND wi.del_status = 'Live' $where_waste)
            UNION ALL
            (SELECT CASE WHEN iai.consumption_status = 'Plus' THEN 'Ajuste +' COLLATE utf8mb4_general_ci ELSE 'Ajuste -' COLLATE utf8mb4_general_ci END as type, 
                    ROUND(CASE WHEN iai.consumption_status = 'Plus' THEN iai.consumption_amount ELSE -iai.consumption_amount END, 2) as quantity, 
                    ia.date, ia.note COLLATE utf8mb4_general_ci as description, ia.id as movement_id
             FROM tbl_inventory_adjustment_ingredients iai
             JOIN tbl_inventory_adjustment ia ON iai.inventory_adjustment_id = ia.id
             WHERE iai.ingredient_id = $ingredient_id AND iai.del_status = 'Live' $where_adjustment)
            UNION ALL
            (SELECT 'Producción' COLLATE utf8mb4_general_ci as type, ROUND(pi.quantity_amount, 2) as quantity, p.date, 'Producción' COLLATE utf8mb4_general_ci as description, p.id as movement_id
             FROM tbl_production_ingredients pi
             JOIN tbl_production p ON pi.production_id = p.id
             WHERE pi.ingredient_id = $ingredient_id AND pi.del_status = 'Live' AND p.status = 1 $where_production)
            UNION ALL
            (SELECT CASE WHEN ti.transfer_type = 1 THEN 'Transferencia Entrada' COLLATE utf8mb4_general_ci ELSE 'Transferencia Salida' COLLATE utf8mb4_general_ci END as type, 
                    ROUND(CASE WHEN ti.transfer_type = 1 THEN ti.quantity_amount ELSE -ti.quantity_amount END, 2) as quantity, 
                    t.date, CONCAT('Transferencia a/de ', o.outlet_name COLLATE utf8mb4_general_ci) COLLATE utf8mb4_general_ci as description, t.id as movement_id
             FROM tbl_transfer_ingredients ti
             JOIN tbl_transfer t ON ti.transfer_id = t.id
             JOIN tbl_outlets o ON (ti.to_outlet_id = o.id OR ti.from_outlet_id = o.id)
             WHERE ti.ingredient_id = $ingredient_id AND ti.del_status = 'Live' AND ti.status = 1 $where_transfer)
            ORDER BY date DESC
            LIMIT $limit OFFSET $offset
        ";

        return $this->db->query($query)->result();
    }

    public function countIngredientMovements($ingredient_id, $start_date = '', $end_date = '') {
        $where_purchase = $start_date && $end_date ? " AND p.date BETWEEN '$start_date' AND '$end_date'" : '';
        $where_sale = $start_date && $end_date ? " AND s.date_time BETWEEN '$start_date' AND '$end_date'" : '';
        $where_waste = $start_date && $end_date ? " AND w.date BETWEEN '$start_date' AND '$end_date'" : '';
        $where_adjustment = $start_date && $end_date ? " AND ia.date BETWEEN '$start_date' AND '$end_date'" : '';
        $where_production = $start_date && $end_date ? " AND p.date BETWEEN '$start_date' AND '$end_date'" : '';
        $where_transfer = $start_date && $end_date ? " AND t.date BETWEEN '$start_date' AND '$end_date'" : '';

        $query = "
            SELECT COUNT(*) as total FROM (
                (SELECT p.date
                 FROM tbl_purchase_ingredients pi
                 JOIN tbl_purchase p ON pi.purchase_id = p.id
                 WHERE pi.ingredient_id = $ingredient_id AND pi.del_status = 'Live' $where_purchase)
                UNION ALL
                (SELECT s.date_time
                 FROM tbl_sale_consumptions_of_menus sc
                 JOIN tbl_sales s ON sc.sales_id = s.id
                 WHERE sc.ingredient_id = $ingredient_id AND sc.del_status = 'Live' $where_sale)
                UNION ALL
                (SELECT s.date_time
                 FROM tbl_sale_consumptions_of_modifiers_of_menus scm
                 JOIN tbl_sales s ON scm.sales_id = s.id
                 WHERE scm.ingredient_id = $ingredient_id AND scm.del_status = 'Live' $where_sale)
                UNION ALL
                (SELECT w.date
                 FROM tbl_waste_ingredients wi
                 JOIN tbl_wastes w ON wi.waste_id = w.id
                 WHERE wi.ingredient_id = $ingredient_id AND wi.del_status = 'Live' $where_waste)
                UNION ALL
                (SELECT ia.date
                 FROM tbl_inventory_adjustment_ingredients iai
                 JOIN tbl_inventory_adjustment ia ON iai.inventory_adjustment_id = ia.id
                 WHERE iai.ingredient_id = $ingredient_id AND iai.del_status = 'Live' $where_adjustment)
                UNION ALL
                (SELECT p.date
                 FROM tbl_production_ingredients pi
                 JOIN tbl_production p ON pi.production_id = p.id
                 WHERE pi.ingredient_id = $ingredient_id AND pi.del_status = 'Live' AND p.status = 1 $where_production)
                UNION ALL
                (SELECT t.date
                 FROM tbl_transfer_ingredients ti
                 JOIN tbl_transfer t ON ti.transfer_id = t.id
                 WHERE ti.ingredient_id = $ingredient_id AND ti.del_status = 'Live' AND ti.status = 1 $where_transfer)
            ) as movements
        ";

        $result = $this->db->query($query)->row();
        return $result->total;
    }
    public function getIngredientSalesHistory($ingredient_id, $limit = 50, $offset = 0) {
        $company_id = $this->session->userdata('company_id');
        $outlet_id = $this->session->userdata('outlet_id');

        $query = "
            SELECT s.date_time, s.sale_no, fm.name as food_menu_name, ROUND(sc.consumption, 2) as consumption, u.unit_name, ROUND(sd.menu_unit_price, 2) as menu_unit_price, ROUND((sd.qty * sd.menu_unit_price), 2) as total_sale_price, s.id as sale_id
            FROM tbl_sale_consumptions_of_menus sc
            JOIN tbl_sales s ON sc.sales_id = s.id
            JOIN tbl_food_menus fm ON sc.food_menu_id = fm.id
            JOIN tbl_sales_details sd ON sd.sales_id = s.id AND sd.food_menu_id = fm.id
            JOIN tbl_ingredients i ON sc.ingredient_id = i.id
            JOIN tbl_units u ON i.unit_id = u.id
            WHERE sc.ingredient_id = $ingredient_id AND sc.del_status = 'Live' AND s.outlet_id = $outlet_id
            ORDER BY s.date_time DESC
            LIMIT $limit OFFSET $offset
        ";

        return $this->db->query($query)->result();
    }
    public function getInventoryFoodMenu($food_id = "",$category_id='') {

        $outlet_id = $this->session->userdata('outlet_id');
        $company_id = $this->session->userdata('company_id');
        $where = '';
        $where1 = '';
        if($food_id!=''){
            $getFMIds = $food_id;
        }else{
            $getFMIds = getFMIds($outlet_id);
        }
        if($category_id!=''){
            $where1.= "  AND i.category_id = '$category_id'";
        }
        //get selected food menu ids
        $result = $this->db->query("SELECT i.*,
        (select SUM(quantity_amount) from tbl_transfer_ingredients  where ingredient_id=i.id AND to_outlet_id=$outlet_id AND  tbl_transfer_ingredients.del_status='Live' AND  tbl_transfer_ingredients.status=1 AND tbl_transfer_ingredients.transfer_type=2) total_transfer_plus_2,
        (select SUM(quantity_amount) from tbl_transfer_ingredients  where ingredient_id=i.id AND from_outlet_id=$outlet_id AND  tbl_transfer_ingredients.del_status='Live' AND (tbl_transfer_ingredients.status=1) AND tbl_transfer_ingredients.transfer_type=2) total_transfer_minus_2,
        (select SUM(qty) from tbl_sales_details  where food_menu_id=i.id AND outlet_id=$outlet_id AND del_status='Live') sale_total
         FROM tbl_food_menus i  WHERE FIND_IN_SET(`id`, '$getFMIds') AND i.company_id= '$company_id' AND i.del_status='Live' $where1")->result();
        return $result;
    }
    public function checkInventory($food_id = "") {

        $outlet_id = $this->session->userdata('outlet_id');
        $company_id = $this->session->userdata('company_id');
        //get selected food menu ids
        $result = $this->db->query("SELECT i.*,
        (select SUM(quantity_amount) from tbl_transfer_ingredients  where ingredient_id=i.id AND to_outlet_id=$outlet_id AND  tbl_transfer_ingredients.del_status='Live' AND  tbl_transfer_ingredients.status=1 AND tbl_transfer_ingredients.transfer_type=2) total_transfer_plus_2,
        (select SUM(quantity_amount) from tbl_transfer_ingredients  where ingredient_id=i.id AND from_outlet_id=$outlet_id AND  tbl_transfer_ingredients.del_status='Live' AND (tbl_transfer_ingredients.status=1) AND tbl_transfer_ingredients.transfer_type=2) total_transfer_minus_2,
        (select SUM(qty) from tbl_sales_details  where food_menu_id=i.id AND outlet_id=$outlet_id AND del_status='Live') sale_total
         FROM tbl_food_menus i  WHERE id='$food_id' AND i.company_id= '$company_id' AND i.del_status='Live'")->row();
        return $result;
    }
    /**
     * get Inventory Alert List
     * @access public
     * @return object
     * @param no
     */
    public function getInventoryAlertList() {
        $outlet_id = $this->session->userdata('outlet_id');
        $company_id = $this->session->userdata('company_id');

        $where = '';
        $getFMIds = getFMIds($outlet_id);

        $result = $this->db->query("SELECT ingr_tbl.*,i.id as food_menu_id,ingr_cat_tbl.category_name,ingr_unit_tbl.unit_name,ingr_unit_tbl2.unit_name as unit_name2, (select SUM(quantity_amount) from tbl_purchase_ingredients where ingredient_id=i.id AND outlet_id=$outlet_id AND del_status='Live') total_purchase, 
(select SUM(consumption) from tbl_sale_consumptions_of_menus where ingredient_id=i.id AND outlet_id=$outlet_id AND del_status='Live') total_consumption,
(select SUM(consumption) from tbl_sale_consumptions_of_modifiers_of_menus where ingredient_id=i.id AND outlet_id=$outlet_id AND  del_status='Live') total_modifiers_consumption,
(select SUM(waste_amount) from tbl_waste_ingredients  where ingredient_id=i.id AND outlet_id=$outlet_id AND tbl_waste_ingredients.del_status='Live') total_waste,
(select SUM(consumption_amount) from tbl_inventory_adjustment_ingredients  where ingredient_id=i.id AND outlet_id=$outlet_id AND  tbl_inventory_adjustment_ingredients.del_status='Live' AND  tbl_inventory_adjustment_ingredients.consumption_status='Plus') total_consumption_plus,
(select SUM(consumption_amount) from tbl_inventory_adjustment_ingredients  where ingredient_id=i.id AND outlet_id=$outlet_id AND  tbl_inventory_adjustment_ingredients.del_status='Live' AND  tbl_inventory_adjustment_ingredients.consumption_status='Minus') total_consumption_minus,
(select SUM(quantity_amount) from tbl_production_ingredients  where ingredient_id=i.id AND outlet_id=$outlet_id AND  tbl_production_ingredients.del_status='Live' AND tbl_production_ingredients.status=1) total_production,
(select SUM(quantity_amount) from tbl_transfer_ingredients  where ingredient_id=i.id AND to_outlet_id=$outlet_id AND  tbl_transfer_ingredients.del_status='Live' AND  tbl_transfer_ingredients.status=1 AND tbl_transfer_ingredients.transfer_type=1) total_transfer_plus,
(select SUM(quantity_amount) from tbl_production_ingredients  where ingredient_id=i.id AND outlet_id=$outlet_id AND  tbl_production_ingredients.del_status='Live' AND tbl_production_ingredients.status=1) total_production,
(select SUM(quantity_amount) from tbl_transfer_ingredients  where ingredient_id=i.id AND from_outlet_id=$outlet_id AND  tbl_transfer_ingredients.del_status='Live' AND (tbl_transfer_ingredients.status=1) AND tbl_transfer_ingredients.transfer_type=1) total_transfer_minus,
(select SUM(quantity_amount) from tbl_transfer_received_ingredients  where ingredient_id=i.id AND to_outlet_id=$outlet_id AND  tbl_transfer_received_ingredients.del_status='Live' AND  tbl_transfer_received_ingredients.status=1) total_transfer_plus_2,
(select SUM(quantity_amount) from tbl_transfer_received_ingredients  where ingredient_id=i.id AND from_outlet_id=$outlet_id AND  tbl_transfer_received_ingredients.del_status='Live' AND (tbl_transfer_received_ingredients.status=1)) total_transfer_minus_2

FROM tbl_ingredients i  LEFT JOIN (select * from tbl_ingredients where del_status='Live') ingr_tbl ON ingr_tbl.id = i.id LEFT JOIN (select * from tbl_ingredient_categories where del_status='Live') ingr_cat_tbl ON ingr_cat_tbl.id = ingr_tbl.category_id LEFT JOIN (select * from tbl_units where del_status='Live') ingr_unit_tbl ON ingr_unit_tbl.id = ingr_tbl.unit_id  LEFT JOIN (select * from tbl_units where del_status='Live') ingr_unit_tbl2 ON ingr_unit_tbl2.id = ingr_tbl.purchase_unit_id WHERE  i.company_id= '$company_id' AND i.del_status='Live' $where  GROUP BY i.id")->result();

        return $result;
    }
    /**
     * get All By Company Id For Dropdown
     * @access public
     * @return object
     * @param int
     * @param string
     */
    public function getAllByCompanyIdForDropdown($company_id, $table_name) {
        $result = $this->db->query("SELECT * 
          FROM $table_name 
          WHERE company_id=$company_id AND del_status = 'Live'  
          ORDER BY name ASC")->result();
        return $result;
    }


        // OPTIMIZACIÓN: contador liviano para el Dashboard
    public function countAllIngredientsByCompany() {
        $company_id = $this->session->userdata('company_id');
        $this->db->select('COUNT(*) AS c');
        $this->db->from('tbl_ingredients');
        $this->db->where('company_id', $company_id);
        $this->db->where('del_status', 'Live');
        $row = $this->db->get()->row();
        return (int)($row ? $row->c : 0);
    }
}

?>
