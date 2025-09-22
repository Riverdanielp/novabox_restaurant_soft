<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Facturasend
 *
 * Librería para interactuar con la API de FacturaSend en CodeIgniter 3.
 *
 * @property CI_Controller $CI
 */
class Facturasend {

    private $CI;
    private $base_url;
    private $tenant_id;
    private $api_key;
    private $auth_header;
    private $is_configured = false; // Flag para verificar la configuración

    public function __construct()
    {
        $this->CI =& get_instance();

        // Cargar el archivo de configuración
        // $this->CI->load->config('facturasend');

        // Asignar valores desde la configuración
        $this->base_url   = $this->CI->config->item('facturasend_url');
        $this->tenant_id  = $this->CI->config->item('facturasend_tenant_id');
        $this->api_key    = $this->CI->config->item('facturasend_api_key');

        // Verificar que la configuración esencial no esté vacía
        if (!empty($this->base_url) && !empty($this->tenant_id) && !empty($this->api_key)) {
            $this->is_configured = true;
            // Preparar la cabecera de autenticación solo si está configurado
            $this->auth_header = 'Authorization: Bearer api_key_' . $this->api_key;
        }
    }

    public function test(){
        echo '<pre>';
        var_dump($this->base_url); 
        var_dump($this->tenant_id); 
        var_dump($this->api_key); 
        var_dump($this->auth_header); 
        var_dump($this->is_configured); 
        echo '</pre>';
    }
    /**
     * Realiza la petición cURL a la API.
     * @param string $method 'GET', 'POST', etc.
     * @param string $endpoint El endpoint de la API a llamar.
     * @param array|null $data El cuerpo de la petición para POST/PUT.
     * @return array Respuesta decodificada de la API.
     */
    private function _make_request($method, $endpoint, $data = null)
    {
        // Si la configuración no está completa, devuelve un error controlado.
        if (!$this->is_configured) {
            return [
                'status' => 503, // Service Unavailable
                'body'   => [
                    'error' => 'Servicio no configurado',
                    'message' => 'La configuración de la API FacturaSend está incompleta. Por favor, revise el archivo application/config/facturasend.php'
                ]
            ];
        }

        $ch = curl_init();
        
        $url = $this->base_url . '/' . $endpoint;

        $headers = [
            'Content-Type: application/json',
            $this->auth_header
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($method === 'POST' && $data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status' => $http_code,
            'body'   => json_decode($response, true)
        ];
    }

    // --- Métodos de la API ---

    /**
     * Test de servicio sin autenticación.
     */
    public function test_servicio()
    {
        if (empty($this->base_url)) {
            return [
                'status' => 503,
                'body'   => [
                    'error' => 'Configuración incompleta.',
                    'message' => 'La URL de FacturaSend (facturasend_url) no está configurada.'
                ]
            ];
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url . '/test');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['status' => $http_code, 'body' => json_decode($response, true)];
    }

    /**
     * Test de servicio con autenticación de empresa.
     */
    public function test_servicio_empresa()
    {
        return $this->_make_request('GET', $this->tenant_id . '/test');
    }

    /**
     * Crea uno o varios documentos electrónicos en un lote.
     * @param array $documentos Array de documentos a crear.
     */
    public function crear_lote_documentos($documentos)
    {
        return $this->_make_request('POST', $this->tenant_id . '/lote/create?qr=true&tax=true', $documentos);
        // xml=true&
    }

    /**
     * Consulta un documento por su ID interno.
     * @param int $id El ID del documento.
     */
    public function consultar_documento_por_id($id)
    {
        return $this->_make_request('GET', $this->tenant_id . '/de/id/' . $id);
    }

    /**
     * Consulta un documento por su CDC.
     * @param string $cdc El CDC del documento.
     */
    public function consultar_documento_por_cdc($cdc)
    {
        return $this->_make_request('GET', $this->tenant_id . '/de/cdc/' . $cdc);
    }

    /**
     * Consulta los estados de una lista de documentos por sus CDC.
     * @param array $cdcList Array de CDCs. Ej: [['cdc' => '...'], ['cdc' => '...']]
     */
    public function consultar_estados_documentos($cdcList)
    {
        $data = ['cdcList' => $cdcList];
        return $this->_make_request('POST', $this->tenant_id . '/de/estado', $data);
    }

    /**
     * Obtiene el XML de un DE a partir de su CDC.
     * @param string $cdc El CDC del documento.
     */
    public function obtener_xml_de($cdc)
    {
        return $this->_make_request('POST', $this->tenant_id . '/de/xml/' . $cdc, []);
    }

    /**
     * Obtiene el PDF de un DE.
     * @param array $cdcList Array de CDCs. Ej: [['cdc' => '...']]
     */
    public function obtener_pdf_de($cdcList)
    {
        $data = ['cdcList' => $cdcList];
        return $this->_make_request('POST', $this->tenant_id . '/de/pdf', $data);
    }

    /**
     * Re-envía por email los documentos asociados a una lista de CDCs.
     * @param string $email El email de destino.
     * @param array $cdcList Array de CDCs. Ej: [['cdc' => '...'], ['cdc' => '...']]
     */
    public function reenviar_email_documento($email, $cdcList)
    {
        $data = [
            'email'   => $email,
            'cdcList' => $cdcList
        ];
        return $this->_make_request('POST', $this->tenant_id . '/de/email', $data);
    }

    /**
     * SIFEN - Consulta de RUC.
     * @param string $ruc El RUC a consultar.
     */
    public function sifen_consulta_ruc($ruc)
    {
        return $this->_make_request('GET', $this->tenant_id . '/sifen/ruc/' . $ruc);
    }

    /**
     * SIFEN - Consulta de RUC.
     * @param string $ruc El RUC a consultar.
     */
    public function consulta_ruc($ruc)
    {
        return $this->_make_request('GET', $this->tenant_id . '/ruc/' . $ruc);
    }

    /**
     * SIFEN - Consulta por CDC.
     * @param string $cdc El CDC a consultar en SIFEN.
     */
    public function sifen_consulta_cdc($cdc)
    {
        return $this->_make_request('POST', $this->tenant_id . '/dte/cdc/' . $cdc);
    }

    /**
     * Obtiene la lista de departamentos.
     */
    public function get_departamentos()
    {
        return $this->_make_request('GET', $this->tenant_id . '/departamentos');
    }

    /**
     * Obtiene la lista de distritos para un departamento.
     * @param int $departamento_id ID del departamento.
     */
    public function get_distritos($departamento_id)
    {
        return $this->_make_request('GET', $this->tenant_id . '/distritos/' . $departamento_id);
    }
    
    /**
     * Obtiene la lista de ciudades para un distrito.
     * @param int $distrito_id ID del distrito.
     */
    public function get_ciudades($distrito_id)
    {
        return $this->_make_request('GET', $this->tenant_id . '/ciudades/' . $distrito_id);
    }
    
    /**
     * Obtiene los tipos de regímenes.
     */
    public function get_tipos_regimenes()
    {
        return $this->_make_request('GET', $this->tenant_id . '/tiposRegimenes');
    }
    

    /**
     * Obtiene el PDF de una factura electrónica en formato base64 desde la API.
     *
     * @param string $cdc El Código de Control (CDC) del documento a consultar.
     * @param string|null $format Opcional. El formato del PDF ('ticket' o 'A4'). Por defecto es 'A4'.
     * @return array Un array con 'status' (código HTTP) y 'body' (la respuesta de la API).
     */
    public function get_invoice_pdf_base64($cdc, $format = 'A4')
    {
        $endpoint = "{$this->tenant_id}/de/pdf";

        $payload = [
            "cdcList" => [
                ["cdc" => $cdc]
            ],
            // "type"   => "base64",
            // "format" => $format // 'ticket' o 'A4'
        ];

        return $this->_make_request('POST', $endpoint, $payload);
    }

    /**
     * Obtiene el PDF de una factura electrónica en formato base64 desde la API.
     *
     * @param string $cdc El Código de Control (CDC) del documento a consultar.
     * @return array Un array con 'status' y 'data' (el string base64) o 'message' (el error).
     */
    public function get_invoice_pdf_base64OLD($cdc)
    {
        $url = "{$this->base_url}/{$this->tenant_id}/de/pdf";

        $body = [
            "cdcList" => [
                ["cdc" => $cdc]
            ],
            "type"   => "base64",
            // "format" => "ticket" // O 'A4' si prefieres ese formato
        ];

        $headers = [
            'Authorization' => 'Bearer ' . $this->api_key,
            'Content-Type'  => 'application/json'
        ];

        try {
            $response = $this->client->request('POST', $url, [
                'headers' => $headers,
                'body'    => json_encode($body)
            ]);

            if ($response->getStatusCode() == 200) {
                // La API devuelve el base64 directamente en el cuerpo de la respuesta
                $base64_data = $response->getBody()->getContents();
                // Puede que la API lo devuelva dentro de un JSON, ajusta si es necesario.
                // Ejemplo: $json_response = json_decode($base64_data, true); $base64_data = $json_response['data'];
                return ['status' => 'success', 'data' => $base64_data];
            } else {
                return ['status' => 'error', 'message' => 'La API respondió con estado: ' . $response->getStatusCode()];
            }

        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Excepción al llamar a la API: ' . $e->getMessage()];
        }
    }
}