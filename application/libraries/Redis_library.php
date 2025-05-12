<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Redis_library {
    private $redis;
    private $enabled = false; // Indicador de uso de Redis
    private $prefix = '';     // Prefijo para las claves de Redis

    public function __construct() {
        $CI =& get_instance();
        $CI->load->config('config');  // Cargar el archivo de configuración

        // Obtener las configuraciones desde config.php
        $host = $CI->config->item('redis_host') ?? '127.0.0.1';
        $port = $CI->config->item('redis_port') ?? 6379;
        $this->prefix = $CI->config->item('redis_prefix') ?? '';

        // Intentar conectarse a Redis solo si está habilitado en config.php
        if ($CI->config->item('redis_enabled') === true) {
            try {
                $this->redis = new Redis();
                $this->redis->connect($host, $port);
                $this->enabled = true; // Redis habilitado
            } catch (Exception $e) {
                log_message('error', 'Redis no está disponible: ' . $e->getMessage());
                $this->enabled = false; // Redis deshabilitado si falla la conexión
            }
        }
    }

    // Método para almacenar datos en Redis
    public function set($key, $value, $ttl = 0) {
        if (!$this->enabled) {
            return; // Si Redis no está habilitado, no hacer nada
        }

        $key = $this->prefix . $key; // Añadir el prefijo a la clave
        if ($ttl > 0) {
            $this->redis->setex($key, $ttl, json_encode($value));
        } else {
            $this->redis->set($key, json_encode($value));
        }
    }

    // Método para recuperar datos desde Redis
    public function get($key) {
        if (!$this->enabled) {
            return null; // Si Redis no está habilitado, devolver null
        }

        $key = $this->prefix . $key; // Añadir el prefijo a la clave
        $data = $this->redis->get($key);
        return $data ? json_decode($data, true) : null;
    }

    // Método para eliminar una clave en Redis
    public function delete($key) {
        if (!$this->enabled) {
            return; // Si Redis no está habilitado, no hacer nada
        }

        $key = $this->prefix . $key; // Añadir el prefijo a la clave
        $this->redis->del($key);
    }
}