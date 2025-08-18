<?php

if (!function_exists('get_multi_config')) {
    function get_multi_config() {
        $path = APPPATH . 'config/config_multi.json';
        if (file_exists($path)) {
            $json = file_get_contents($path);
            return json_decode($json, true);
        }
        return null;
    }
}


function get_current_db_system_name() {
    $config_multi = get_multi_config();
    // Supón que 'base' es la clave activa
    $active_key = isset($config_multi['base']) ? $config_multi['base'] : 'default';
    return isset($config_multi['bases'][$active_key]['nombre_sistema'])
        ? $config_multi['bases'][$active_key]['nombre_sistema']
        : $active_key;
}

function get_db_key_by_nombre_sistema($nombre_sistema) {
    $config_multi = get_multi_config();
    foreach ($config_multi['bases'] as $db_key => $info) {
        if (isset($info['nombre_sistema']) && $info['nombre_sistema'] === $nombre_sistema) {
            return $db_key;
        }
    }
    return null; // no encontrado
}

if (!function_exists('get_db_instance')) {
    /**
     * Obtiene una instancia de la base de datos según la clave
     * @param string $db_key
     * @return CI_DB_query_builder
     */
    function get_db_instance($db_key = 'default') {
        $CI =& get_instance();
        // Usa la configuración de database.php, solo cambia el grupo activo
        return $CI->load->database($db_key, TRUE);
    }
}

if (!function_exists('get_all_db_keys')) {
    function get_all_db_keys() {
        $config = get_multi_config();
        return array_keys($config['bases']);
    }
}
/**
 * Devuelve un array de todos los outlets de todas las DBs configuradas,
 * cada uno con la BD entre paréntesis en el nombre.
 */
if (!function_exists('get_all_outlets_multi')) {
    function get_all_outlets_multi() {
        $CI =& get_instance();
        $config_multi = get_multi_config();
        $outlets = [];

        if (!$config_multi || !isset($config_multi['bases'])) {
            return $outlets; // Retorna vacío si no hay configuración
        }
        foreach ($config_multi['bases'] as $db_key => $db_info) {
            if ($db_key === 'default') {
                continue; // Omite la base de datos por defecto
            }
            // Carga la base de datos
            $db = $CI->load->database($db_key, TRUE);

            // Consulta los outlets activos
            $result = $db->query("SELECT id, outlet_name FROM tbl_outlets WHERE del_status = 'Live' ORDER BY outlet_name")->result();

            foreach ($result as $row) {
                // Formato: (NombreBD) Outlet
                // Formato para el value del select: id|db_key|outlet_name|nombre_sistema
                $outlets[] = (object)[
                    'id' => $row->id 
                        . '|' . $db_key
                        . '|' . $row->outlet_name
                        . '|' . (isset($db_info['nombre_sistema']) ? $db_info['nombre_sistema'] : $db_key),
                    'outlet_name' => '(' . $db_info['nombre'] . ') ' . $row->outlet_name,
                    'db_key' => $db_key,
                    'bd_nombre' => $db_info['nombre'],
                    'nombre_sistema' => isset($db_info['nombre_sistema']) ? $db_info['nombre_sistema'] : $db_key,
                    'outlet_name_real' => $row->outlet_name
                ];
            }
        }

        return $outlets;
    }
}



if (!function_exists('crear_transferencia_remota')) {
    /**
     * Crea o actualiza el registro de transferencia en la BD remota y devuelve el ID creado allá.
     * $transfer_id_local: ID de la transferencia aquí (local)
     * $data: Array con los campos principales (usa solo los necesarios)
     * $to_db_key: Clave de la base destino
     */
    function crear_transferencia_remota($transfer_id_local, $data, $to_db_key,$outlet_name, $to_outlet_id_int) {
        $CI =& get_instance();
        $db_remota = $CI->load->database($to_db_key, TRUE);

        // Identificador único de la BD enviante
        $config_multi = get_multi_config();
        $from_db_key = isset($config_multi['bases']['default']['nombre_sistema'])
            ? $config_multi['bases']['default']['nombre_sistema']
            : 'default';

        // Buscar transferencia remota por remote_transfer_id + from_db_key
        $remota_existente = $db_remota->get_where('tbl_transfer', [
            'remote_transfer_id' => $transfer_id_local,
            'from_db_key' => $from_db_key,
            // 'del_status' => 'Live'
        ])->row();

        $data_remota = [
            'reference_no'    => $data['reference_no'],
            'date'            => $data['date'],
            'to_outlet_id'    => $to_outlet_id_int, //$data['to_outlet_id'],
            'from_outlet_id'  => null, //$data['from_outlet_id'],
            'outlet_id'       => null, //$data['to_outlet_id'],
            'remote_outlet_id'       => $data['outlet_id'],
            'remote_outlet_name' => $outlet_name,
            'to_db_key'       => 'default',
            'user_id'         => $data['user_id'],
            'note_for_sender' => isset($data['note_for_sender']) ? $data['note_for_sender'] : '',
            'note_for_receiver' => isset($data['note_for_receiver']) ? $data['note_for_receiver'] : '',
            'status'          => $data['status'],
            'transfer_type'   => isset($data['transfer_type']) ? $data['transfer_type'] : 1,
            'sync_status'     => 1,
            'del_status'      => 'Live'
        ];

        if ($remota_existente) {
            // Actualiza el registro remoto con los datos nuevos
            $db_remota->where('id', $remota_existente->id)
                      ->update('tbl_transfer', $data_remota);
            return $remota_existente->id;
        } else {
            $data_remota['from_db_key'] = $from_db_key;
            $data_remota['remote_transfer_id'] = $transfer_id_local;
            $db_remota->insert('tbl_transfer', $data_remota);
            return $db_remota->insert_id();
        }
    }
}

if (!function_exists('eliminar_transferencia_remota')) {
    /**
     * Elimina (soft delete) la transferencia en la BD remota por remote_transfer_id
     * $remote_transfer_id: ID remoto en la otra BD
     * $to_db_key: Clave de la base destino anterior
     */
    function eliminar_transferencia_remota($remote_transfer_id, $to_db_key) {
        $CI =& get_instance();
        $db_remota = $CI->load->database($to_db_key, TRUE);

        // Elimina (soft delete) la transferencia remota
        $db_remota->where('id', $remote_transfer_id)
                  ->update('tbl_transfer', ['del_status' => 'Deleted', 'remote_deleted' => 1]);
    }
}

/**
 * Busca o crea una categoría de ingredientes en la BD remota por nombre
 */
function get_or_create_remote_ingredient_category($category_name, $company_id, $user_id, $db_remota) {
    $cat = $db_remota->get_where('tbl_ingredient_categories', [
        'category_name' => $category_name,
        'company_id' => $company_id,
        'del_status' => 'Live'
    ])->row();
    if ($cat) return $cat->id;
    $cat_data = [
        'category_name' => $category_name ?: 'Sin Categoría',
        'description' => '',
        'user_id' => $user_id,
        'company_id' => $company_id,
        'del_status' => 'Live'
    ];
    $db_remota->insert('tbl_ingredient_categories', $cat_data);
    return $db_remota->insert_id();
}

/**
 * Busca o crea una unidad en la BD remota por nombre
 */
function get_or_create_remote_unit($unit_name, $company_id, $db_remota) {
    $id = $db_remota->query("SELECT id FROM tbl_units WHERE company_id=$company_id and unit_name='" . $unit_name . "'")->row('id');
    if ($id != '') return $id;
    $data = array('unit_name' => $unit_name, 'company_id' => $company_id);
    $db_remota->insert('tbl_units', $data);
    return $db_remota->insert_id();
}

/**
 * Busca o crea una categoría de food menu en la BD remota por nombre
 */
function get_or_create_remote_foodmenu_category($category_name, $company_id, $user_id, $db_remota) {
    $id = $db_remota->query("SELECT id FROM tbl_food_menu_categories WHERE company_id=$company_id and user_id=$user_id and category_name='" . $category_name . "' and del_status='Live'")->row('id');
    if ($id != '') return $id;
    $data = array('category_name' => $category_name, 'company_id' => $company_id, 'user_id' => $user_id, 'del_status' => 'Live');
    $db_remota->insert('tbl_food_menu_categories', $data);
    return $db_remota->insert_id();
}

/**
 * Actualiza los precios en la BD remota (debes adaptar si tu función updatePrice es diferente)
 */
function updatePriceRemoto($db_remota, $company_id, $item_id, $price, $sale_price_take_away, $delivery_prices, $sale_price_delivery) {
    $outlet_info1 = $db_remota->query("SELECT * FROM tbl_outlets WHERE company_id='$company_id' AND del_status='Live'")->result();
    if ($outlet_info1) {
        foreach ($outlet_info1 as $outlet) {
            // Adaptación: si los campos no existen, inicializarlos
            $foods_prices = json_decode($outlet->food_menu_prices ?: '{}', true);
            $delivery_price = json_decode($outlet->delivery_price ?: '{}', true);
            $data_price_array = [];
            $data_delivery_price_array = [];
            $available_counter = 1;
            foreach ($foods_prices as $key=>$value){
                $key_id = explode("tmp",$key);
                if(($key_id[1]==$item_id)){
                    $data_price_array[$key] = $price."||".$sale_price_take_away."||".$sale_price_delivery;
                    $available_counter++;
                }else{
                    $data_price_array[$key] = $value;
                }
            }
            if($available_counter==1){
                $index_name = "tmp".$item_id;
                $data_price_array[$index_name] = $price."||".$sale_price_take_away."||".$sale_price_delivery;
            }
            $available_counter = 1;
            if (!is_array($delivery_prices)) {
                $delivery_prices = [];
            } else {
                foreach ($delivery_price as $key=>$value){
                    $key_id = explode("index_",$key);
                    if(($key_id[1]==$item_id)){
                        $data_delivery_price_array[$key] = $delivery_prices;
                        $available_counter++;
                    }else{
                        $data_delivery_price_array[$key] = $value;
                    }
                }
            }
            if($available_counter==1){
                $food_menus = ($outlet->food_menus ?: '').",".$item_id;
                $index_name = "index_".$item_id;
                $data_delivery_price_array[$index_name] = $delivery_prices;
            }
            $data_u = array();
            $data_u['food_menu_prices'] = json_encode($data_price_array);
            $data_u['delivery_price'] = json_encode($data_delivery_price_array);
            $data_u['food_menus'] = $food_menus;
            $db_remota->where('id', $outlet->id);
            $db_remota->update("tbl_outlets", $data_u);
        }
    }
}

/**
 * Busca o crea ingrediente en la BD remota, asegurando que existan unidad y categoría
 */
function get_or_create_remote_ingredient($ingredient_id_local, $to_db_key) {
    $CI =& get_instance();
    $ingredient_local = $CI->Common_model->getDataById($ingredient_id_local, "tbl_ingredients");
    if (!$ingredient_local) return false;

    $company_id = $ingredient_local->company_id;
    $user_id = $ingredient_local->user_id;
    $db_remota = $CI->load->database($to_db_key, TRUE);

    // Categoría y unidad
    $ingredient_category_id = get_or_create_remote_ingredient_category($ingredient_local->category_id ? categoryName($ingredient_local->category_id) : '', $company_id, $user_id, $db_remota);
    $unit_id = get_or_create_remote_unit(unitName($ingredient_local->unit_id), $company_id, $db_remota);
    $purchase_unit_id = get_or_create_remote_unit(unitName($ingredient_local->purchase_unit_id), $company_id, $db_remota);

    // Buscar ingrediente por code y name
    $remote_ingredient = $db_remota
        ->where('code', $ingredient_local->code)
        // ->where('name', $ingredient_local->name)
        ->where('del_status', 'Live')
        ->get('tbl_ingredients')->row();

    if ($remote_ingredient) {
        return $remote_ingredient->id;
    }

    // Si tiene food_id, busca/crea el food_menu remoto
    $food_id_remote = null;
    $is_direct_food_remote = 1;
    if ($ingredient_local->food_id > 0) {
        $food_menu_local = $CI->Common_model->getDataById($ingredient_local->food_id, "tbl_food_menus");
        if ($food_menu_local) {
            $is_direct_food_remote = 2;
            $foodmenu_category_id = get_or_create_remote_foodmenu_category(foodMenucategoryName($food_menu_local->category_id), $company_id, $user_id, $db_remota);

            // Buscar food_menu remoto
            $food_menu_remote = $db_remota
                ->where('code', $food_menu_local->code)
                ->where('name', $food_menu_local->name)
                ->where('del_status', 'Live')
                ->get('tbl_food_menus')->row();

            if ($food_menu_remote) {
                $food_id_remote = $food_menu_remote->id;
            } else {
                // Crear food_menu remoto
                $food_menu_info = array(
                    'product_type' => $food_menu_local->product_type,
                    'name' => $food_menu_local->name,
                    'alternative_name' => $food_menu_local->alternative_name,
                    'code' => $food_menu_local->code,
                    'category_id' => $foodmenu_category_id,
                    'veg_item' => $food_menu_local->veg_item,
                    'beverage_item' => $food_menu_local->beverage_item,
                    'description' => $food_menu_local->description,
                    'sale_price' => $food_menu_local->sale_price,
                    'sale_price_take_away' => $food_menu_local->sale_price_take_away,
                    'sale_price_delivery' => $food_menu_local->sale_price_delivery,
                    'total_cost' => $food_menu_local->total_cost,
                    'loyalty_point' => $food_menu_local->loyalty_point,
                    'purchase_price' => $food_menu_local->purchase_price,
                    'alert_quantity' => $food_menu_local->alert_quantity,
                    'ing_category_id' => $food_menu_local->ing_category_id,
                    'tax_information' => $food_menu_local->tax_information,
                    'tax_string' => $food_menu_local->tax_string,
                    'user_id' => $user_id,
                    'company_id' => $company_id,
                    'delivery_price' => $food_menu_local->delivery_price,
                    'photo' => $food_menu_local->photo,
                    'del_status' => 'Live'
                );
                $db_remota->insert('tbl_food_menus', $food_menu_info);
                $food_id_remote = $db_remota->insert_id();
                // Actualiza precios en la remota
                updatePriceRemoto($db_remota, $company_id, $food_id_remote, $food_menu_local->sale_price, $food_menu_local->sale_price_take_away, $food_menu_local->delivery_price, $food_menu_local->sale_price_delivery);

                // Asignar ingredientes al menú recién creado
                $food_ingredients_local = $CI->db
                    ->where('food_menu_id', $food_menu_local->id)
                    ->where('del_status', 'Live')
                    ->get('tbl_food_menus_ingredients')->result();
                foreach ($food_ingredients_local as $fd_ing) {
                    $remote_ing_id = get_or_create_remote_ingredient($fd_ing->ingredient_id, $to_db_key);
                    $db_remota->insert('tbl_food_menus_ingredients', [
                        'food_menu_id' => $food_id_remote,
                        'ingredient_id' => $remote_ing_id,
                        'consumption' => $fd_ing->consumption,
                        'del_status' => 'Live'
                    ]);
                }
            }
        }
    }

    // Crear ingrediente en la remota
    $ingredient_info = array(
        'name' => $ingredient_local->name,
        'code' => $ingredient_local->code,
        'category_id' => $ingredient_category_id,
        'purchase_price' => $ingredient_local->purchase_price,
        'alert_quantity' => $ingredient_local->alert_quantity,
        'unit_id' => $unit_id,
        'purchase_unit_id' => $purchase_unit_id,
        'consumption_unit_cost' => $ingredient_local->purchase_price, // igual que purchase_price
        'average_consumption_per_unit' => $ingredient_local->purchase_price,
        'conversion_rate' => $ingredient_local->conversion_rate,
        'user_id' => $user_id,
        'company_id' => $company_id,
        'is_direct_food' => $is_direct_food_remote,
        'food_id' => $food_id_remote,
        'del_status' => 'Live',
    );
    $db_remota->insert('tbl_ingredients', $ingredient_info);
    $ingredient_id_remote = $db_remota->insert_id();
    return $ingredient_id_remote;
}

function confirmar_transferencia_en_origen($remote_transfer_id, $from_db_key_nombre_sistema) {
    $db_key = get_db_key_by_nombre_sistema($from_db_key_nombre_sistema);
    if (!$db_key) {
        log_message('error', 'No se encontró db_key para nombre_sistema: ' . $from_db_key_nombre_sistema);
        return false;
    }
    $CI =& get_instance();
    $db_origen = $CI->load->database($db_key, TRUE);

    // Cambia el status a Recibida en la cabecera
    $db_origen->where('id', $remote_transfer_id)
        ->update('tbl_transfer', [
            'status' => 1 // Recibida
        ]);
    
    // Actualiza el status de los ingredientes asociados
    $db_origen->where('transfer_id', $remote_transfer_id)
        ->where('del_status', 'Live')
        ->update('tbl_transfer_ingredients', [
            'status' => 1
        ]);
    
    return true;
}