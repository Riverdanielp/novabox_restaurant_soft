<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class NodeWaApi
{
    protected $CI;
    protected $token;
    protected $endpoint;
    protected $numbers_to_reports;

    public function __construct()
    {
        $this->CI =& get_instance();
        
        // Ruta absoluta al archivo de config
        $config_path = APPPATH . 'config/nodewaapi.php';
        if (file_exists($config_path)) {
            $this->CI->load->config('nodewaapi', TRUE);

            $config_nodewaapi = $this->CI->config->item('nodewaapi');
            $this->token = isset($config_nodewaapi['token']) ? $config_nodewaapi['token'] : null;
            $this->endpoint = isset($config_nodewaapi['endpoint']) ? $config_nodewaapi['endpoint'] : null;
            $this->numbers_to_reports = isset($config_nodewaapi['numbers_to_reports']) ? $config_nodewaapi['numbers_to_reports'] : [];
            
        } else {
            // Si no existe, deja las propiedades en null
            $this->token = null;
            $this->endpoint = null;
            $this->numbers_to_reports = [];
        }
    }

    /**
     * Enviar mensaje por la API
     * 
     * @param array $data
     * @return bool
     */
    public function send_message($data = [])
    {
        // Validar config
        if (empty($this->token) || empty($this->endpoint)) {
            // Falta configuración
            return false;
        }

        // Validar número y body obligatorios
        if (empty($data['number']) || empty($data['body'])) {
            return false;
        }

        // Preparar payload
        $payload = [
            'number'        => $data['number'],
            'body'          => $data['body'],
            'userId'        => isset($data['userId']) ? $data['userId'] : '',
            'queueId'       => isset($data['queueId']) ? $data['queueId'] : '',
            'sendSignature' => isset($data['sendSignature']) ? (bool)$data['sendSignature'] : false,
            'closeTicket'   => isset($data['closeTicket']) ? (bool)$data['closeTicket'] : false,
        ];

        // Enviar sin bloquear la app usando CURL básico (fire & forget)
        $ch = curl_init($this->endpoint);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$this->token}",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false); // No esperar respuesta
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1000); // 1 segundo máximo
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 500); // medio segundo conectar
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1); // Para evitar que falle en sistemas sin señales

        @curl_exec($ch); // Ignorar retorno
        curl_close($ch);

        return true;
    }

    public function send_report_to_all($message)
    {
        // Usar la propiedad de la clase en vez de recargar config cada vez
        if (
            empty($this->numbers_to_reports) ||
            !is_array($this->numbers_to_reports) ||
            empty($message)
        ) {
            return false;
        }

        $results = [];
        foreach ($this->numbers_to_reports as $number) {
            $data = [
                'number' => $number,
                'body'   => $message,
            ];
            // Usar el mismo método de envío, pero limitar el tiempo de espera
            $results[$number] = $this->send_message_with_timeout($data, 2); // 2 segundos
            // Puedes poner un usleep(2000000); // para pausar 2 segundos si realmente lo necesitas entre envíos
        }
        return $results;
    }

    /**
     * Enviar mensaje con timeout personalizado en segundos (por default 2s)
     */
    private function send_message_with_timeout($data = [], $timeout_seconds = 2)
    {
        if (empty($this->token) || empty($this->endpoint)) {
            return false;
        }
        if (empty($data['number']) || empty($data['body'])) {
            return false;
        }

        $payload = [
            'number'        => $data['number'],
            'body'          => $data['body'],
            'userId'        => isset($data['userId']) ? $data['userId'] : '',
            'queueId'       => isset($data['queueId']) ? $data['queueId'] : '',
            'sendSignature' => isset($data['sendSignature']) ? (bool)$data['sendSignature'] : false,
            'closeTicket'   => isset($data['closeTicket']) ? (bool)$data['closeTicket'] : false,
        ];

        $ch = curl_init($this->endpoint);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$this->token}",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Obtener respuesta
        // curl_setopt($ch, CURLOPT_TIMEOUT, $timeout_seconds); // Timeout en segundos

        $response = @curl_exec($ch);
        $err      = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err || $httpCode >= 400) {
            return false;
        }
        return $response;
    }

}