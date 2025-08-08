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