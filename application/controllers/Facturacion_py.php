<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Facturacion_py extends Cl_Controller {

    public function __construct() {
        parent::__construct();
        // Cargar modelos y librerías necesarios
        $this->load->model('Authentication_model');
        $this->load->model('Common_model');
        $this->load->model('Facturacion_py_model'); // Nuestro nuevo modelo
        $this->load->library('form_validation');
        $this->load->library('pagination');
        $this->load->helper('factura_send_helper');
        $this->Common_model->setDefaultTimezone();

        if (!$this->session->has_userdata('user_id')) {
            redirect('Authentication/index');
        }
        // Aquí iría tu lógica de permisos de acceso si la necesitas
    }

    /**
     * Muestra el listado paginado y filtrado de facturas electrónicas.
     * Estados disponibles:
     * (-1, 'Borrador'), (0, 'Generado'), (1, 'Enviado en Lote'), (2, 'Aprobado'),
     * (3, 'Aprobado con Observación'), (4, 'Rechazado'), (98, 'Inexistente'), (99, 'Cancelado')
     */
    public function listado() {
        $filters = [
            'fecha_inicio' => $this->input->get('fecha_inicio'),
            'fecha_fin' => $this->input->get('fecha_fin'),
            'sucursal_id' => $this->input->get('sucursal_id'),
            'punto_id' => $this->input->get('punto_id'),
            'usuario_id' => $this->input->get('usuario_id'),
            'estado_id' => $this->input->get('estado_id'),
            'tipo_documento' => $this->input->get('tipo_documento'), 
        ];
        $sort = $this->input->get('sort') ?: 'fecha';
        $order = $this->input->get('order') ?: 'desc';
        $filters['sort'] = $sort;
        $filters['order'] = $order;

        $config = [];
        $config['base_url'] = base_url('Facturacion_py/listado');
        $config['total_rows'] = $this->Facturacion_py_model->count_facturas($filters);
        $config['per_page'] = 20; // 20 facturas por página
        $config['reuse_query_string'] = TRUE;
        $this->pagination->initialize($config);
        
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        
        $data = [];
        $data['facturas'] = $this->Facturacion_py_model->get_facturas_list($config['per_page'], $page, $filters);
        $data['links'] = $this->pagination->create_links();
        
        // Datos para los filtros
        $data['sifen_sucursales'] = fs_get_sucursales();
        $data['puntos_expedicion'] = fs_get_puntos_expedicion();
        $data['usuarios'] = fs_get_usuarios();
        $data['estados_documentos'] = fs_get_documentos_estados();
        $data['filters'] = $filters;

        $data['main_content'] = $this->load->view('facturacion_py/listado', $data, TRUE);
        $this->load->view('userHome', $data);
    }

    /**
     * Muestra el formulario para crear o editar una factura.
     */
    public function formulario($id = null) {
        $data = [];
        if ($id) {
            // Se asume que get_factura_completa devuelve un objeto con toda la data necesaria
            $factura_data = $this->Facturacion_py_model->get_factura_completa($id);
            if(!$factura_data){
                $this->session->set_flashdata('error_custom', "No se encontró la factura con ID: $id");
                redirect('Facturacion_py/listado');
            }
            $data['factura'] = $factura_data;
            $data['form_title'] = 'Editar Factura Electrónica N° ' . $factura_data->numero;
            $data['is_edit'] = true;
        } else {
            $data['factura'] = null;
            $data['form_title'] = 'Crear Nueva Factura Manual';
            $data['is_edit'] = false;
        }
        // --- Datos para los Selects del Formulario ---
        $data['tipos_documento'] = [1 => 'Factura Electrónica', 4 => 'Autofactura', 5 => 'Nota de Crédito', 6 => 'Nota de Débito', 7 => 'Nota de Remisión'];
        $data['tipos_emision'] = [1 => 'Normal', 2 => 'Contingencia'];
        $data['tipos_transaccion'] = [1 => 'Venta de mercadería', 2 => 'Prestación de servicios', 3 => 'Mixto', 4 => 'Venta de activo fijo', 9 => 'Anticipo', 10 => 'Compra de Productos'];
        $data['tipos_impuesto'] = [1 => 'IVA', 2 => 'ISC', 3 => 'Renta', 4 => 'Ninguno', 5 => 'IVA–Renta'];
        $data['tipos_operacion_cliente'] = [1 => 'B2B', 2 => 'B2C', 3 => 'B2G', 4 => 'B2F'];
        $data['tipos_doc_cliente'] = [1 => 'Cédula Paraguaya', 2 => 'Pasaporte', 3 => 'Cédula Extranjera', 4 => 'Carnet de Residencia', 5 => 'Innominado'];
        $data['tipos_presencia'] = [1 => 'Operación presencial', 2 => 'Operación electrónica', 3 => 'Telemarketing', 4 => 'Venta a domicilio', 9 => 'Otro'];
        $data['tipos_iva_item'] = [1 => 'Gravado IVA', 2 => 'Exonerado', 3 => 'Exento'];

        $data['tipos_pago'] = [1 => 'Efectivo', 3 => 'Tarjeta de Crédito', 4 => 'Tarjeta de Débito', 2 => 'Cheque', 9 => 'Otro'];
        $data['tipos_tarjeta_procesadora'] = [1 => 'Dinelco', 2 => 'Bancard', 3 => 'Infonet', 4 => 'Panal', 5 => 'Procard', 6 => 'Red Activa', 7 => 'Otra'];

        // --- Cargar sucursales y puntos para el selector agrupado ---
        $sucursales = $this->db->get('py_sifen_sucursales')->result();
        foreach ($sucursales as &$sucursal) {
            $sucursal->puntos = $this->db->where('sucursal_id', $sucursal->id)->where('activo', 1)->get('py_sifen_puntos_expedicion')->result();
        }
        $data['sucursales_con_puntos'] = $sucursales;

        $data['unidades_medida'] = fs_get_medidas();
        $data['departamentos'] = fs_get_departamentos();

        $data['main_content'] = $this->load->view('facturacion_py/formulario', $data, TRUE);
        $this->load->view('userHome', $data);
    }

    /**
     * Procesa los datos del formulario de factura manual.
     */
    public function procesar_formulario($id = null)
    {
        // Determinar la acción del usuario (crear, reenviar, duplicar)
        $action = $this->input->post('submit'); // 'submit', 'resend', 'duplicate'
    
        // Recolectar datos del POST
        $post_data = $this->input->post();
        
        // Asegurar que la fecha tenga el formato correcto (añadir segundos si faltan)
        if (isset($post_data['fecha']) && strpos($post_data['fecha'], ':ss') === false) {
            $post_data['fecha'] = date('Y-m-d\TH:i:s', strtotime($post_data['fecha']));
        }
    
        $this->load->helper('factura_send_helper');
        $this->load->library('facturasend');

        // Validaciones básicas
        if (empty($post_data['punto']) || empty($post_data['fecha']) || empty($post_data['moneda'])) {
            $this->session->set_flashdata('error_custom', "Faltan campos obligatorios básicos: punto de expedición, fecha o moneda");
            redirect($id ? 'Facturacion_py/formulario/' . $id : 'Facturacion_py/formulario');
            return;
        }

        // Validar el tipo de documento y realizar validaciones específicas
        $tipo_documento = isset($post_data['tipoDocumento']) ? (int)$post_data['tipoDocumento'] : 1;
        
        switch ($tipo_documento) {
            case 5: // Nota de Crédito
                if (empty($post_data['documento_referencia']) || empty($post_data['documento_referencia']['cdc'])) {
                    $this->session->set_flashdata('error_custom', "Para notas de crédito debe especificar el CDC del documento asociado");
                    redirect($id ? 'Facturacion_py/formulario/' . $id : 'Facturacion_py/formulario');
                    return;
                }
                break;
            case 6: // Nota de Débito
                if (empty($post_data['documento_referencia']) || empty($post_data['documento_referencia']['cdc'])) {
                    $this->session->set_flashdata('error_custom', "Para notas de débito debe especificar el CDC del documento asociado");
                    redirect($id ? 'Facturacion_py/formulario/' . $id : 'Facturacion_py/formulario');
                    return;
                }
                break;
            case 7: // Nota de Remisión
                if (empty($post_data['remision']) || empty($post_data['remision']['motivo_traslado'])) {
                    $this->session->set_flashdata('error_custom', "Para notas de remisión debe especificar el motivo de traslado");
                    redirect($id ? 'Facturacion_py/formulario/' . $id : 'Facturacion_py/formulario');
                    return;
                }
                if (empty($post_data['remision']['vehiculo_matricula'])) {
                    $this->session->set_flashdata('error_custom', "Debe especificar la matrícula del vehículo para la nota de remisión");
                    redirect($id ? 'Facturacion_py/formulario/' . $id : 'Facturacion_py/formulario');
                    return;
                }
                if (empty($post_data['remision']['fecha_inicio']) || empty($post_data['remision']['fecha_fin'])) {
                    $this->session->set_flashdata('error_custom', "Debe especificar las fechas de inicio y fin de traslado");
                    redirect($id ? 'Facturacion_py/formulario/' . $id : 'Facturacion_py/formulario');
                    return;
                }
                break;
        }

        // 2. Obtener el punto de expedición
        $punto_exp_id = $post_data['punto'];
        $punto_expedicion = $this->db->select('pe.*, s.codigo_establecimiento')
        ->from('py_sifen_puntos_expedicion pe')
        ->join('py_sifen_sucursales s', 's.id = pe.sucursal_id', 'left')
        ->where('pe.id', $punto_exp_id) // <-- Cambiado de 'pe.codigo_punto' a 'pe.id'
        ->where('pe.activo', 1)
        ->get()
        ->row();
        
        if (!$punto_expedicion) {
            // El mensaje de error también podría ser más claro
            $this->session->set_flashdata('error_custom', "El punto de expedición seleccionado no es válido o está inactivo.");
            redirect($id ? 'Facturacion_py/formulario/' . $id : 'Facturacion_py/formulario');
            return;
        }
        
        // Obtener información del timbrado para este punto
        $timbrado_info = $this->db->select('t.*')
            ->from('py_sifen_timbrados t')
            ->join('py_sifen_timbrados_puntos tp', 't.id = tp.timbrado_id')
            ->where('tp.punto_expedicion_id', $punto_expedicion->id)
            ->where('t.activo', 1)
            ->where('CURDATE() BETWEEN t.fecha_inicio AND t.fecha_fin')
            ->get()->row();

        if (!$timbrado_info) {
            $this->session->set_flashdata('error_custom', "El punto de expedición '{$punto_exp_id}' no tiene un timbrado activo y vigente.");
            redirect($id ? 'Facturacion_py/formulario/' . $id : 'Facturacion_py/formulario');
            return;
        }
        
        // Completar el objeto punto_expedicion con la información adicional necesaria
        $punto_expedicion->timbrado_id = $timbrado_info->id;
        $punto_expedicion->numero_timbrado = $timbrado_info->numero_timbrado;

        // 3. Normalizar datos para el cliente
        // Determinar si es contribuyente basado en el tipo o si tiene RUC con guión
        $es_contribuyente = ($post_data['cliente']['tipoContribuyente'] == 2 || (strpos($post_data['cliente']['ruc'], '-') !== false));

        if (!((int)$post_data['cliente']['departamento'] > 0 && (int)$post_data['cliente']['distrito'] > 0 && (int)$post_data['cliente']['ciudad'] > 0)) {
            $post_data['cliente']['departamento'] = $this->config->item('sifen_default_departamento');
            $post_data['cliente']['distrito'] = $this->config->item('sifen_default_distrito');
            $post_data['cliente']['ciudad'] = $this->config->item('sifen_default_ciudad');
        }
        $cliente_normalizado = [
            'id_sistema'        => $post_data['cliente']['codigo'] ?? null,
            'es_contribuyente'  => $es_contribuyente,
            // 'es_proveedor_estado' => ($post_data['cliente']['tipoOperacion'] == 3), // B2G
            
            'documentoNumero'   => $post_data['cliente']['ruc'] ?? '0',
            'nombre'            => $post_data['cliente']['razonSocial'],
            'email'             => $post_data['cliente']['email'] ?? '',
            'direccion'         => $post_data['cliente']['direccion'] ?? '',
            'tipo_contribuyente'=> (int)$post_data['cliente']['tipoContribuyente'],
            'tipo_documento'    => (int)$post_data['cliente']['documentoTipo'],
            'departamento_id'   => (int)$post_data['cliente']['departamento'],
            'distrito_id'       => (int)$post_data['cliente']['distrito'],
            'ciudad_id'         => (int)$post_data['cliente']['ciudad'],
            'pais_codigo'       => 'PRY', // Default para Paraguay
            'numero_casa'       => (string)($post_data['cliente']['numeroCasa'] ?? '0'),

            'ruc'               => $post_data['cliente']['ruc'],
            'nombre_fantasia'   => $post_data['cliente']['nombreFantasia'] ?? $post_data['cliente']['razonSocial'],
            'es_proveedor_estado' => ($post_data['cliente']['tipoOperacion'] == 3), // B2G
        ];

        // echo '<pre>';
        // var_dump($cliente_normalizado); 
        // echo '<pre>';
        // return;
        // 4. Normalizar datos para el usuario/vendedor
        $usuario_normalizado = [
            'id_sistema' => $this->session->userdata('user_id'), // ID del usuario logueado
            'nombre'     => $post_data['usuario']['nombre'],
            'documento'  => $post_data['usuario']['documentoNumero'],
            'cargo'      => $post_data['usuario']['cargo']
        ];

        // 5. Normalizar datos de los items
        $items_normalizados = [];
        if (isset($post_data['items']) && is_array($post_data['items'])) {
            foreach ($post_data['items'] as $item) {
                // Verificar campos obligatorios del item
                if (empty($item['descripcion']) || !isset($item['cantidad']) || !isset($item['precioUnitario'])) {
                    continue; // Saltar items incompletos
                }
                
                $items_normalizados[] = [
                    'codigo'          => $item['codigo'] ?? '',
                    'descripcion'     => $item['descripcion'],
                    'cantidad'        => (float)$item['cantidad'],
                    'precio_unitario' => (float)$item['precioUnitario'],
                    'iva'             => isset($item['iva']) ? (int)$item['iva'] : 10,
                    'iva_base'        => isset($item['ivaBase']) ? (int)$item['ivaBase'] : 100, // Valor predeterminado 100%
                    'ncm'             => $item['ncm'] ?? null,
                    'lote'            => $item['lote'] ?? null,
                    'vencimiento'     => $item['vencimiento'] ?? null,
                    'iva_tipo'        => isset($item['ivaTipo']) ? (int)$item['ivaTipo'] : 1, // 1 = Gravado IVA por defecto
                ];
            }
        }
        
        if (empty($items_normalizados)) {
            $this->session->set_flashdata('error_custom', "La factura debe contener al menos un ítem válido.");
            redirect($id ? 'Facturacion_py/formulario/' . $id : 'Facturacion_py/formulario');
            return;
        }

        // 6. Normalizar condición de venta (contado o crédito)
        $condicion_tipo = isset($post_data['condicion']['tipo']) ? (int)$post_data['condicion']['tipo'] : 1;
        $condicion_normalizada = ['tipo' => $condicion_tipo];
        
        // Si es contado, procesar las entregas (formas de pago)
        if ($condicion_tipo == 1 && isset($post_data['condicion']['entregas'])) {
            $entregas_normalizadas = [];
            foreach ($post_data['condicion']['entregas'] as $entrega) {
                // Verificar campos obligatorios de la entrega
                if (!isset($entrega['tipo']) || !isset($entrega['monto']) || (float)$entrega['monto'] <= 0) {
                    continue; // Saltar entregas incompletas
                }
                
                $entrega_normalizada = [
                    'tipo'   => (int)$entrega['tipo'],
                    'monto'  => (float)$entrega['monto'],
                    'moneda' => $post_data['moneda']
                ];
                
                // Procesar información adicional específica por tipo de pago
                if ($entrega['tipo'] == 3 || $entrega['tipo'] == 4) { // Tarjeta de crédito/débito
                    if (isset($entrega['infoTarjeta'])) {
                        $entrega_normalizada['infoTarjeta'] = $entrega['infoTarjeta'];
                    }
                } else if ($entrega['tipo'] == 2) { // Cheque
                    if (isset($entrega['infoCheque'])) {
                        $entrega_normalizada['infoCheque'] = $entrega['infoCheque'];
                    }
                }
                
                $entregas_normalizadas[] = $entrega_normalizada;
            }
            
            if (!empty($entregas_normalizadas)) {
                $condicion_normalizada['entregas'] = $entregas_normalizadas;
            }
        }
        // Si es crédito, procesar la información de crédito
        else if ($condicion_tipo == 2 && isset($post_data['credito'])) {
            $condicion_normalizada['credito'] = [
                'tipo' => isset($post_data['credito']['tipo']) ? (int)$post_data['credito']['tipo'] : 1,
                'plazo' => $post_data['credito']['plazo'] ?? '',
                'cuotas' => isset($post_data['credito']['cuotas']) ? (int)$post_data['credito']['cuotas'] : 1,
                'montoEntrega' => isset($post_data['credito']['montoEntrega']) ? (float)$post_data['credito']['montoEntrega'] : 0
            ];
            
            // Procesar cuotas si existen
            if (isset($post_data['credito']['infoCuotas']) && is_array($post_data['credito']['infoCuotas'])) {
                $cuotas_normalizadas = [];
                foreach ($post_data['credito']['infoCuotas'] as $cuota) {
                    if (!isset($cuota['monto']) || !isset($cuota['vencimiento'])) continue;
                    
                    $cuotas_normalizadas[] = [
                        'moneda' => $post_data['moneda'],
                        'monto' => (float)$cuota['monto'],
                        'vencimiento' => $cuota['vencimiento']
                    ];
                }
                
                if (!empty($cuotas_normalizadas)) {
                    $condicion_normalizada['credito']['infoCuotas'] = $cuotas_normalizadas;
                }
            }
        }

        // Información adicional específica por tipo de documento
        
        // 7. Información específica por tipo de documento
        $doc_adicional = [];
        
        // Procesamiento específico según tipo de documento
        switch ($tipo_documento) {
            case 5: // Nota de Crédito
            case 6: // Nota de Débito
                if (isset($post_data['documento_referencia'])) {
                    $doc_referencia = $post_data['documento_referencia'];
                    $doc_adicional['documentoAsociado'] = [
                        'cdc' => $doc_referencia['cdc'],
                        'tipo' => $tipo_documento == 5 ? 1 : 2, // 1 para NC, 2 para ND
                        'fecha' => $doc_referencia['fecha'],
                        'motivo' => (int)$doc_referencia['motivo'],
                        'observacion' => ($doc_referencia['motivo'] == 5 && isset($doc_referencia['motivo_descripcion'])) 
                                      ? $doc_referencia['motivo_descripcion'] : ''
                    ];
                }
                break;
                
            case 7: // Nota de Remisión
                if (isset($post_data['remision'])) {
                    $remision_data = $post_data['remision'];
                    $doc_adicional['remision'] = [
                        'motivoTraslado' => (int)$remision_data['motivo_traslado'],
                        'responsableTraslado' => 1, // Por defecto: Emisor del documento
                        'fechaInicioTraslado' => fs_format_date_for_sifen($remision_data['fecha_inicio']),
                        'fechaFinTraslado' => fs_format_date_for_sifen($remision_data['fecha_fin']),
                    ];
                    
                    // Datos del transporte/conductor
                    $doc_adicional['transporte'] = [
                        'tipo' => (int)$remision_data['vehiculo_tipo'], // 1=Propio, 2=Tercero
                        'conductor' => [
                            'nombre' => $remision_data['conductor_nombre'],
                            'documento' => $remision_data['conductor_documento'],
                            'direccion' => $remision_data['conductor_direccion'] ?? ''
                        ],
                        'vehiculo' => [
                            'marca' => $remision_data['vehiculo_marca'] ?? '',
                            'chasis' => $remision_data['vehiculo_chasis'] ?? '',
                            'matricula' => $remision_data['vehiculo_matricula']
                        ]
                    ];
                }
                break;
                
            case 4: // Autofactura
                if (isset($post_data['autofactura'])) {
                    $autofactura_data = $post_data['autofactura'];
                    $doc_adicional['autoFactura'] = [
                        'tipoVendedor' => (int)$autofactura_data['tipo_vendedor'],
                        'ubicacion' => $autofactura_data['ubicacion'],
                        'registroIndert' => $autofactura_data['registro'] ?? '',
                    ];
                }
                break;
        }

        // <<<<<<< INICIO: Lógica para número de factura personalizado >>>>>>>
        $numero_factura_tipo = $post_data['numero_factura_tipo'] ?? 'correlativo';
        $numero_factura_personalizado = null;

        if ($numero_factura_tipo === 'personalizado') {
            if (empty($post_data['numero_factura_personalizado']) || !is_numeric($post_data['numero_factura_personalizado'])) {
                $this->session->set_flashdata('error_custom', "Debe ingresar un número de factura válido para la opción 'Número Personalizado'.");
                redirect($id ? 'Facturacion_py/formulario/' . $id : 'Facturacion_py/formulario');
                return;
            }
            $numero_factura_personalizado = (int)$post_data['numero_factura_personalizado'];
        }
        // <<<<<<< FIN: Lógica para número de factura personalizado >>>>>>>

        // 7. Construir estructura final para el helper
        $invoice_data = [
            'venta_id_sistema'  => null, // No aplica para factura manual
            'fecha'             => $post_data['fecha'],
            'moneda'            => $post_data['moneda'],
            // <<<<<<< INICIO: Añadir los nuevos datos al array >>>>>>>
            'numero_factura_tipo' => $numero_factura_tipo,
            'numero_factura_personalizado' => $numero_factura_personalizado,
            // <<<<<<< FIN: Añadir los nuevos datos al array >>>>>>>
            'punto_expedicion'  => $punto_expedicion,
            'cliente'           => $cliente_normalizado,
            'usuario'           => $usuario_normalizado,
            'items'             => $items_normalizados,
            'condicion_venta'   => $condicion_normalizada,
            // Campos adicionales requeridos
            'tipo_documento'    => $tipo_documento,
            'tipo_transaccion'  => isset($post_data['tipoTransaccion']) ? (int)$post_data['tipoTransaccion'] : 1,
            'tipo_impuesto'     => isset($post_data['tipoImpuesto']) ? (int)$post_data['tipoImpuesto'] : 1,
            'tipo_emision'      => isset($post_data['tipoEmision']) ? (int)$post_data['tipoEmision'] : 1,
            'observacion'       => $post_data['observacion'] ?? '',
            'descripcion'       => $post_data['descripcion'] ?? 'Venta de productos/servicios',
        ];
        
        // Agregar información adicional específica del tipo de documento
        if (!empty($doc_adicional)) {
            $invoice_data = array_merge($invoice_data, $doc_adicional);
        }

        // Determinar si es una operación de reenvío o duplicado
        if ($action === 'resend' && $id) {
            $invoice_data['factura_py_id_existente'] = $id;
        } elseif ($action === 'duplicate' && $id) {
            // No se necesita nada especial, el helper lo tratará como nuevo
        }
        // echo '<pre>';
        // var_dump($invoice_data); 
        // echo '<pre>';
        // return;
        // 8. Llamar al helper para procesar la factura
        $resultado_helper = fs_create_and_send_invoice($invoice_data);

        // 9. Manejar la respuesta
        if (isset($resultado_helper['status']) && $resultado_helper['status'] === 'success') {
            // Si hay un ID de venta en el sistema, actualizar la referencia
            if (!empty($post_data['id_sistema'])) {
                $this->db->where('id', $post_data['id_sistema'])
                         ->update('tbl_sales', ['py_factura_id' => $resultado_helper['factura_py_id']]);
            }
            
            $this->session->set_flashdata('exception', "Factura procesada y enviada correctamente. CDC: " . ($resultado_helper['cdc'] ?? 'N/A'));
        } else {
            $error_message = $resultado_helper['message'] ?? 'Error desconocido al procesar la factura.';
            if (isset($resultado_helper['api_response'])) {
                $error_message .= ' Detalles: ' . json_encode($resultado_helper['api_response']);
            }
            $this->session->set_flashdata('error_custom', $error_message);
        }
        
        redirect('Facturacion_py/listado');
    }

    /**
     * Genera y envía factura electrónica desde una venta existente
     */
    public function facturar_venta($sale_id) {
        if (!$sale_id) {
            $this->session->set_flashdata('error_custom', "ID de venta no proporcionado");
            redirect('Sale/sales');
            return;
        }

        // Cargar información de la venta
        $sale_info = $this->Common_model->getDataById($sale_id, "tbl_sales");
        
        if (!$sale_info) {
            $this->session->set_flashdata('error_custom', "Venta no encontrada");
            redirect('Sale/sales');
            return;
        }
        
        // Verificar si ya tiene factura asociada
        if (!empty($sale_info->py_factura_id)) {
            $factura = $this->db->get_where('py_facturas_electronicas', ['id' => $sale_info->py_factura_id])->row();
            if ($factura) {
                $this->session->set_flashdata('error_custom', "Esta venta ya tiene una factura electrónica asociada (CDC: {$factura->cdc})");
                redirect('Sale/sales');
                return;
            }
        }
        
        // Cargar detalles adicionales necesarios
        $outlet_id = $sale_info->outlet_id;
        $punto_expedicion = fs_get_punto_expedicion_activo($outlet_id);
        
        if (!$punto_expedicion) {
            $this->session->set_flashdata('error_custom', "No hay un punto de expedición activo para la sucursal");
            redirect('Sale/sales');
            return;
        }
        
        // Obtener información del cliente
        $customer = $this->Common_model->getDataById($sale_info->customer_id, "tbl_customers");
        if (!$customer) {
            $this->session->set_flashdata('error_custom', "No se encontró información del cliente");
            redirect('Sale/sales');
            return;
        }
        
        // Obtener items de la venta
        $sale_details = $this->db->select('tbl_sale_consumptions.*')
                                ->from('tbl_sale_consumptions')
                                ->where('sales_id', $sale_id)
                                ->get()
                                ->result();
                                
        if (empty($sale_details)) {
            $this->session->set_flashdata('error_custom', "La venta no tiene items asociados");
            redirect('Sale/sales');
            return;
        }
        
        // Obtener información del usuario
        $user = $this->Common_model->getDataById($sale_info->user_id, "tbl_users");
        
        // Normalizar datos para el cliente
        $cliente_normalizado = [
            'id_sistema'        => $customer->id,
            'es_contribuyente'  => !empty($customer->tax_number), // Si tiene RUC asumimos que es contribuyente
            'ruc'               => $customer->tax_number ?: $customer->phone,
            'nombre'            => $customer->name,
            'nombre_fantasia'   => $customer->name,
            'email'             => $customer->email,
            'direccion'         => $customer->address,
            'es_proveedor_estado' => false, // Por defecto no es proveedor del estado
            'tipo_contribuyente'=> empty($customer->tax_number) ? 1 : 2, // 1 = Físico, 2 = Jurídico
            'tipo_documento'    => 1, // 1 = Cédula Paraguaya por defecto
            'departamento_id'   => fs_default_departamento(),
            'distrito_id'       => fs_default_distrito(),
            'ciudad_id'         => fs_default_ciudad(),
            'pais_codigo'       => 'PRY', // Default para Paraguay
            'numero_casa'       => '0',
        ];

        // Normalizar datos para el usuario/vendedor
        $usuario_normalizado = [
            'id_sistema' => $user->id,
            'nombre'     => $user->full_name,
            'documento'  => $user->phone, // Usar teléfono como documento por defecto
            'cargo'      => 'Vendedor'
        ];

        // Normalizar datos de los items
        $items_normalizados = [];
        foreach ($sale_details as $item) {
            // Obtener información del producto
            $producto = $this->Common_model->getDataById($item->food_menu_id, "tbl_food_menus");
            if (!$producto) continue;
            
            // Determinar el IVA (asumimos 10% por defecto)
            $iva_valor = $producto->tax_information == "1 Tax" ? 10 : 5;
            
            $items_normalizados[] = [
                'codigo'          => $producto->code,
                'descripcion'     => $producto->name,
                'cantidad'        => $item->qty,
                'precio_unitario' => $item->menu_price, // Precio unitario
                'iva'             => $iva_valor,
                'iva_base'        => 100, // Base imponible 100%
                'iva_tipo'        => 1, // 1 = Gravado IVA
            ];
        }
        
        // Determinar condición de venta
        $es_credito = false;
        $pagos_registrados = $this->db->get_where('tbl_sale_payments', ['sale_id' => $sale_id])->result();
        
        if ($sale_info->due_amount > 0) {
            $es_credito = true;
        }
        
        $condicion_normalizada = [];
        
        if ($es_credito) {
            $condicion_normalizada = [
                'tipo' => 2, // Crédito
                'credito' => [
                    'tipo' => 1, // Por plazo
                    'plazo' => '30 días',
                    'cuotas' => 1,
                    'montoEntrega' => $sale_info->paid_amount,
                    'infoCuotas' => [
                        [
                            'moneda' => 'PYG',
                            'monto' => $sale_info->due_amount,
                            'vencimiento' => date('Y-m-d', strtotime('+30 days'))
                        ]
                    ]
                ]
            ];
        } else {
            $entregas = [];
            
            foreach ($pagos_registrados as $pago) {
                $tipo_pago_sifen = fs_map_payment_method($pago->payment_method_id);
                
                $entregas[] = [
                    'tipo' => $tipo_pago_sifen,
                    'monto' => $pago->amount,
                    'moneda' => 'PYG'
                ];
            }
            
            $condicion_normalizada = [
                'tipo' => 1, // Contado
                'entregas' => $entregas
            ];
        }

        // Construir estructura final para el helper
        $invoice_data = [
            'venta_id_sistema'  => $sale_id,
            'fecha'             => date('Y-m-d\TH:i:s'),
            'moneda'            => 'PYG',
            'punto_expedicion'  => $punto_expedicion,
            'cliente'           => $cliente_normalizado,
            'usuario'           => $usuario_normalizado,
            'items'             => $items_normalizados,
            'condicion_venta'   => $condicion_normalizada,
            // Campos adicionales requeridos
            'tipo_documento'    => 1, // Factura electrónica
            'tipo_transaccion'  => 1, // Venta de mercadería
            'tipo_impuesto'     => 1, // IVA
            'tipo_emision'      => 1, // Normal
            'observacion'       => '',
            'descripcion'       => 'Venta de productos/servicios',
        ];

        // Llamar al helper para procesar la factura
        $resultado_helper = fs_create_and_send_invoice($invoice_data);

        // Manejar la respuesta
        if (isset($resultado_helper['status']) && $resultado_helper['status'] === 'success') {
            // Actualizar la venta con el ID de la factura electrónica
            $this->db->where('id', $sale_id)
                     ->update('tbl_sales', ['py_factura_id' => $resultado_helper['factura_py_id']]);
                     
            $this->session->set_flashdata('exception', "Factura electrónica generada y enviada correctamente. CDC: " . ($resultado_helper['cdc'] ?? 'N/A'));
            redirect('Sale/POS/' . $sale_id); // Redirigir a la vista detallada de la venta
        } else {
            $error_message = $resultado_helper['message'] ?? 'Error desconocido al procesar la factura.';
            if (isset($resultado_helper['api_response'])) {
                $error_message .= ' Detalles: ' . json_encode($resultado_helper['api_response']);
            }
            $this->session->set_flashdata('error_custom', $error_message);
            redirect('Sale/sales');
        }
    }

    /**
     * Descarga KUDE (PDF) de factura
     */
    public function descargar_kude($factura_id) {
        $factura = $this->Facturacion_py_model->get_factura_completa($factura_id);
        
        if (!$factura || !$factura->cdc) {
            $this->session->set_flashdata('error_custom', "Factura no encontrada o sin CDC asignado");
            redirect('Facturacion_py/listado');
            return;
        }
        
        $this->load->library('facturasend');
        
        // Obtener el PDF desde el servicio
        $response = $this->facturasend->obtener_kude($factura->cdc);
        
        if (isset($response['status']) && $response['status'] == 200 && ($response['body']['success'] ?? false)) {
            // Decodificar el PDF base64
            $pdf_content = base64_decode($response['body']['result']['pdf']);
            
            // Forzar descarga del archivo
            $this->output
                ->set_content_type('application/pdf')
                ->set_header('Content-Disposition: attachment; filename="Factura_' . $factura->cdc . '.pdf"')
                ->set_output($pdf_content);
        } else {
            $error_message = 'Error al obtener el KUDE. Detalles: ' . json_encode($response);
            $this->session->set_flashdata('error_custom', $error_message);
            redirect('Facturacion_py/listado');
        }
    }

    /**
     * Muestra los logs de auditoría para una factura específica.
     */
    public function logs($factura_id) {
        $data = [];
        $data['logs'] = $this->Facturacion_py_model->get_auditoria_logs($factura_id);
        $data['factura_id'] = $factura_id;
        $data['main_content'] = $this->load->view('facturacion_py/logs', $data, TRUE);
        $this->load->view('userHome', $data);
    }

    public function ajax_get_distritos_by_departamento(){
        $depto_id = $this->input->get('departamento_id');
        $distritos = fs_get_distritos(null, $depto_id);
        echo json_encode($distritos);
    }

    public function ajax_get_ciudades_by_distrito(){
        $distrito_id = $this->input->get('distrito_id');
        $ciudades = fs_get_ciudades(null, $distrito_id);
        echo json_encode($ciudades);
    }

    public function ajax_buscar_py_clientes() {
        $term = $this->input->post('term');
        $results = $this->Facturacion_py_model->buscar_py_clientes($term);
        echo json_encode($results);
    }

    public function ajax_buscar_py_items() {
        $term = $this->input->post('term');
        $results = $this->Facturacion_py_model->buscar_py_items($term);
        echo json_encode($results);
    }
    
    public function ajax_buscar_py_usuarios() {
        $term = $this->input->post('term');
        $results = $this->Facturacion_py_model->buscar_py_usuarios($term);
        echo json_encode($results);
    }

    /**
     * Método AJAX para obtener los logs de una factura
     */
    public function ajax_get_logs() {
        $factura_id = $this->input->post('factura_id');
        
        if (!$factura_id) {
            echo json_encode(['success' => false, 'message' => 'ID de factura requerido']);
            return;
        }
        
        // Obtener logs tanto de la factura específica como los logs generales que puedan estar relacionados
        $logs = $this->Facturacion_py_model->get_auditoria_logs($factura_id);
        
        // También obtener logs que puedan estar relacionados por CDC
        $factura = $this->db->get_where('py_facturas_electronicas', ['id' => $factura_id])->row();
        
        if ($factura && !empty($factura->cdc)) {
            // Buscar logs adicionales que contengan este CDC en el json_backup
            $this->db->select('pa.*, u.full_name as usuario_nombre');
            $this->db->from('py_facturas_auditoria pa');
            $this->db->join('tbl_users u', 'pa.usuario_id = u.id', 'left');
            $this->db->group_start();
            $this->db->where('pa.factura_id IS NULL');
            $this->db->or_where('pa.factura_id !=', $factura_id);
            $this->db->group_end();
            $this->db->like('pa.json_backup', $factura->cdc);
            $this->db->order_by('pa.fecha_modificacion', 'DESC');
            $this->db->order_by('pa.id', 'DESC');
            
            $query_adicionales = $this->db->get();
            
            if ($query_adicionales) {
                $logs_adicionales = $query_adicionales->result();
                
                // Combinar los logs
                if (!empty($logs_adicionales)) {
                    $logs = array_merge($logs, $logs_adicionales);
                    
                    // Ordenar por fecha de modificación descendente
                    usort($logs, function($a, $b) {
                        $timestamp_a = strtotime($a->fecha_modificacion);
                        $timestamp_b = strtotime($b->fecha_modificacion);
                        
                        if ($timestamp_a == $timestamp_b) {
                            return $b->id - $a->id; // Si las fechas son iguales, ordenar por ID descendente
                        }
                        
                        return $timestamp_b - $timestamp_a;
                    });
                }
            }
        }
        
        if ($logs) {
            echo json_encode(['success' => true, 'logs' => $logs]);
        } else {
            echo json_encode(['success' => true, 'logs' => [], 'message' => 'No se encontraron logs para esta factura']);
        }
    }

    /**
     * Método AJAX para consultar el estado de un CDC específico
     */
    public function ajax_consultar_cdc() {
        $cdc = $this->input->post('cdc');
        
        if (!$cdc) {
            echo json_encode(['success' => false, 'message' => 'CDC requerido']);
            return;
        }
        
        // Validar formato del CDC
        if (strlen($cdc) !== 44 || !ctype_digit($cdc)) {
            echo json_encode(['success' => false, 'message' => 'El CDC debe tener exactamente 44 dígitos numéricos']);
            return;
        }
        
        // Buscar la factura por CDC en la base de datos
        $factura = $this->db->get_where('py_facturas_electronicas', ['cdc' => $cdc])->row();
        $factura_id = $factura ? $factura->id : null;
        
        $this->load->library('facturasend');
        
        try {
            // Realizar consulta del CDC usando el método correcto de la librería
            $cdcs = [['cdc' => $cdc]];
            $resultado = $this->facturasend->consultar_estados_documentos($cdcs);

            // Registrar en auditoría con el factura_id encontrado
            $system_user_id = $this->session->userdata('user_id');
            $py_usuario_id = $this->get_or_create_py_usuario($system_user_id);
            
            $audit_data = [
                'factura_id' => $factura_id,
                'fecha_modificacion' => date('Y-m-d H:i:s'),
                'usuario_id' => $py_usuario_id,
                'tipo_accion' => 'CONSULTA_CDC_MANUAL',
                'json_backup' => json_encode([
                    'cdc_consultado' => $cdc,
                    'factura_encontrada' => $factura_id ? true : false,
                    'resultado_api' => $resultado
                ])
            ];
            $this->db->insert('py_facturas_auditoria', $audit_data);
            
            // Si encontramos la factura y la API devolvió un resultado válido, actualizar el estado
            if ($factura_id && isset($resultado['status']) && $resultado['status'] == 200 && ($resultado['body']['success'] ?? false)) {
                $deList = $resultado['body']['deList'] ?? $resultado['body']['result']['deList'] ?? [];
                
                if (!empty($deList) && is_array($deList)) {
                    foreach ($deList as $de) {
                        if (isset($de['cdc']) && $de['cdc'] === $cdc && isset($de['situacion'])) {
                            // Actualizar el estado de la factura
                            $update_data = [
                                'estado' => $de['situacion'],
                                'fecha_modificacion' => date('Y-m-d H:i:s')
                            ];
                            $this->db->where('id', $factura_id)->update('py_facturas_electronicas', $update_data);
                            break;
                        }
                    }
                }
            }
            
            echo json_encode(['success' => true, 'data' => $resultado, 'factura_id' => $factura_id]);
            
        } catch (Exception $e) {
            // Registrar el error en auditoría también
            $system_user_id = $this->session->userdata('user_id');
            $py_usuario_id = $this->get_or_create_py_usuario($system_user_id);
            
            $audit_data = [
                'factura_id' => $factura_id,
                'fecha_modificacion' => date('Y-m-d H:i:s'),
                'usuario_id' => $py_usuario_id,
                'tipo_accion' => 'CONSULTA_CDC_ERROR',
                'json_backup' => json_encode([
                    'cdc_consultado' => $cdc,
                    'factura_encontrada' => $factura_id ? true : false,
                    'error' => $e->getMessage()
                ])
            ];
            $this->db->insert('py_facturas_auditoria', $audit_data);
            
            echo json_encode(['success' => false, 'message' => 'Error al consultar CDC: ' . $e->getMessage()]);
        }
    }

    public function ajax_get_distritos() {
        $depto_id = $this->input->get('departamento_id');
        echo json_encode(fs_get_distritos(null, $depto_id));
    }

    public function ajax_get_ciudades() {
        $distrito_id = $this->input->get('distrito_id');
        echo json_encode(fs_get_ciudades(null, $distrito_id));
    }

    /**
     * Proxy para consultar RUC y evitar problemas de CORS.
     */
    public function ajax_consultar_ruc() {
        $ruc = $this->input->post('ruc');
        if (!$ruc) {
            echo json_encode(['error' => 'RUC no proporcionado']);
            return;
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://ruc.novabox.work/consultas/ruc',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => ['ruc' => $ruc],
        ]);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            echo json_encode(['error' => 'Error en cURL: ' . $err]);
        } else {
            header('Content-Type: application/json');
            echo $response;
        }
    }

    /**
     * Consulta estado de documentos enviados
     */
    public function consultar_estado($lote_id = null) {
        if (!$lote_id) {
            $this->session->set_flashdata('error_custom', "ID de lote no proporcionado");
            redirect('Facturacion_py/listado');
            return;
        }
        
        $this->load->library('facturasend');
        $response = $this->facturasend->consultar_estado_lote($lote_id);
        
        if (isset($response['status']) && $response['status'] == 200 && ($response['body']['success'] ?? false)) {
            $facturas_actualizadas = 0;
            
            foreach ($response['body']['result']['deList'] as $de) {
                $cdc = $de['cdc'];
                $estado = $de['estado'];
                
                // Mapear el estado de la API a nuestro sistema
                $estado_id = 1; // Por defecto "Enviado en Lote"
                
                if ($estado == 'Aprobado') {
                    $estado_id = 2; // Aprobado
                } else if ($estado == 'Rechazado') {
                    $estado_id = 3; // Rechazado por SET
                }
                
                // Actualizar estado en nuestra BD
                $this->db->where('cdc', $cdc)
                         ->update('py_facturas_electronicas', ['estado' => $estado_id]);
                
                if ($this->db->affected_rows() > 0) {
                    $facturas_actualizadas++;
                }
            }
            
            $this->session->set_flashdata('exception', "Consulta exitosa. Se actualizaron {$facturas_actualizadas} facturas.");
        } else {
            $error_message = 'Error al consultar estado del lote. Detalles: ' . json_encode($response);
            $this->session->set_flashdata('error_custom', $error_message);
        }
        
        redirect('Facturacion_py/listado');
    }

    /**
     * Sincroniza los estados de facturas con estado 0 o 1 contra la API de FacturaSend.
     * Usa el helper fs_facturasend_actualizar_estados_pendientes.
     */
    public function sync_estados_pendientes() {
        $this->load->helper('factura_send_helper');

        try {
            $resultado = fs_facturasend_actualizar_estados_pendientes(50);
            if ($resultado['success']) {
                $msg = sprintf('Sincronización OK. Procesados: %d, Actualizados: %d, Errores de lote: %d',
                    $resultado['procesados'] ?? 0,
                    $resultado['actualizados'] ?? 0,
                    $resultado['errores'] ?? 0
                );
                $this->session->set_flashdata('success', $msg);
            } else {
                $this->session->set_flashdata('error_custom', 'Sincronización fallida: ' . ($resultado['message'] ?? 'Error desconocido'));
            }
        } catch (Exception $e) {
            $this->session->set_flashdata('error_custom', 'Excepción durante la sincronización: ' . $e->getMessage());
        }

        redirect('Facturacion_py/listado');
    }

        
    /**
     * Método de debug para probar la API de FacturaSend directamente
     */
    public function debug_pdf_api($cdc = null)
    {
        if (!$cdc) {
            $cdc = '01801605580004001000108922025101310365299908'; // CDC de prueba
        }
        
        $this->load->library('facturasend');
        
        echo '<h2>Debug API FacturaSend - PDF</h2>';
        echo '<hr>';
        
        // Test de configuración
        echo '<h3>1. Configuración:</h3>';
        $this->facturasend->test();
        echo '<hr>';
        
        // Test directo con cURL
        echo '<h3>2. Test directo con cURL (igual a PowerShell):</h3>';
        
        $url = 'https://fs-api.novabox.net.py/miabuelaeas/de/pdf';
        $data = [
            'cdcList' => [
                ['cdc' => $cdc]
            ],
            'type' => 'base64',
            'format' => 'ticket'
        ];
        
        $headers = [
            'Authorization: Bearer api_key_09408306-92AB-4CBB-A18B-7B9D08B9C469',
            'Content-Type: application/json; charset=utf-8'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $error = curl_error($ch);
        curl_close($ch);
        
        echo '<pre>';
        echo "URL: $url\n";
        echo "HTTP Code: $http_code\n";
        echo "Content-Type: $content_type\n";
        echo "cURL Error: " . ($error ?: 'None') . "\n";
        echo "Response Length: " . strlen($response) . "\n";
        echo "Response Type: " . gettype($response) . "\n";
        
        if (strlen($response) < 1000) {
            echo "Response Content: " . $response . "\n";
        } else {
            echo "Response Sample (first 200 chars): " . substr($response, 0, 200) . "...\n";
            
            // Verificar si es base64 válido
            $decoded = base64_decode($response, true);
            if ($decoded !== false) {
                echo "✓ Response is valid base64\n";
                echo "Decoded length: " . strlen($decoded) . "\n";
                if (substr($decoded, 0, 4) === '%PDF') {
                    echo "✓ Decoded content is a PDF\n";
                } else {
                    echo "✗ Decoded content is NOT a PDF (starts with: " . substr($decoded, 0, 10) . ")\n";
                }
            } else {
                echo "✗ Response is NOT valid base64\n";
            }
        }
        echo '</pre>';
        echo '<hr>';
        
        // Test usando el método de la librería
        echo '<h3>3. Test usando método get_invoice_pdf_base64:</h3>';
        $result = $this->facturasend->get_invoice_pdf_base64($cdc, 'ticket');
        echo '<pre>';
        echo "Status: " . $result['status'] . "\n";
        echo "Body Type: " . gettype($result['body']) . "\n";
        echo "Body Length: " . (is_string($result['body']) ? strlen($result['body']) : 'N/A') . "\n";
        if (is_string($result['body']) && strlen($result['body']) < 500) {
            echo "Body Content: " . $result['body'] . "\n";
        } elseif (is_array($result['body'])) {
            echo "Body Structure: " . print_r($result['body'], true) . "\n";
        }
        echo '</pre>';
    }

    /**
     * Descargar PDF de factura electrónica
     */
    public function descargar_pdf_factura()
    {
        // Verificar que sea una petición AJAX
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        // Configurar header para JSON
        header('Content-Type: application/json');

        $cdc = $this->input->post('cdc');
        $format = $this->input->post('format') ?? 'ticket'; // Usar 'ticket' por defecto
        
        if (empty($cdc)) {
            $response = [
                'success' => false,
                'message' => 'CDC es requerido'
            ];
            echo json_encode($response);
            return;
        }

        // Validar formato del CDC (44 dígitos numéricos)
        if (!preg_match('/^\d{44}$/', $cdc)) {
            $response = [
                'success' => false,
                'message' => 'El CDC debe tener exactamente 44 dígitos numéricos'
            ];
            echo json_encode($response);
            return;
        }

        try {
            // Cargar la librería FacturaSend
            $this->load->library('facturasend');
            
            // Obtener el PDF en base64 usando el método corregido
            $result = $this->facturasend->get_invoice_pdf_base64($cdc, $format);
            
            // Log de depuración más detallado
            log_message('debug', 'FacturaSend PDF Response Status: ' . $result['status']);
            log_message('debug', 'FacturaSend PDF Response Body Type: ' . gettype($result['body']));
            log_message('debug', 'FacturaSend PDF Response Body Length: ' . (is_string($result['body']) ? strlen($result['body']) : 'N/A'));
            
            if ($result['status'] == 200 && isset($result['body']) && !empty($result['body'])) {
                $pdf_base64 = null;
                
                // La API de FacturaSend devuelve el base64 directamente como string
                if (is_string($result['body'])) {
                    // Limpiar y validar el base64
                    $cleaned_base64 = trim($result['body']);
                    $cleaned_base64 = preg_replace('/[^A-Za-z0-9+\/=]/', '', $cleaned_base64);
                    
                    // Verificar que sea base64 válido y tenga contenido suficiente
                    if (strlen($cleaned_base64) > 100) {
                        $decoded_test = base64_decode($cleaned_base64, true);
                        if ($decoded_test !== false && substr($decoded_test, 0, 4) === '%PDF') {
                            $pdf_base64 = $cleaned_base64;
                        }
                    }
                }
                // Si body es array (por si la API cambia el formato)
                elseif (is_array($result['body'])) {
                    // Buscar en diferentes posibles ubicaciones
                    if (isset($result['body']['data'])) {
                        $pdf_base64 = $result['body']['data'];
                    } elseif (isset($result['body']['pdf'])) {
                        $pdf_base64 = $result['body']['pdf'];
                    } elseif (isset($result['body']['result'])) {
                        $pdf_base64 = $result['body']['result'];
                    } elseif (isset($result['body'][0]) && is_string($result['body'][0])) {
                        $pdf_base64 = $result['body'][0];
                    } else {
                        // Buscar recursivamente
                        $pdf_base64 = $this->_extract_pdf_from_response($result['body']);
                    }
                }
                
                if (!empty($pdf_base64)) {
                    // Limpiar el base64 de posibles caracteres no válidos
                    $pdf_base64 = trim($pdf_base64);
                    $pdf_base64 = preg_replace('/[^A-Za-z0-9+\/=]/', '', $pdf_base64);
                    
                    // Validar que sea base64 válido
                    $decoded_pdf = base64_decode($pdf_base64, true);
                    if ($decoded_pdf !== false && strlen($decoded_pdf) > 100) {
                        // Verificar que sea realmente un PDF
                        if (substr($decoded_pdf, 0, 4) === '%PDF') {
                            $response = [
                                'success' => true,
                                'pdf_base64' => $pdf_base64,
                                'filename' => 'factura_' . $cdc . '.pdf',
                                'size' => strlen($decoded_pdf)
                            ];
                        } else {
                            $response = [
                                'success' => false,
                                'message' => 'El contenido recibido no es un PDF válido'
                            ];
                        }
                    } else {
                        $response = [
                            'success' => false,
                            'message' => 'El PDF recibido no tiene un formato base64 válido o está vacío'
                        ];
                    }
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'No se pudo obtener el PDF de la respuesta de la API.',
                        'debug_info' => [
                            'status' => $result['status'],
                            'body_type' => gettype($result['body']),
                            'body_is_empty' => empty($result['body']),
                            'body_length' => is_string($result['body']) ? strlen($result['body']) : 'N/A',
                            'body_sample' => is_string($result['body']) ? substr($result['body'], 0, 100) : (is_array($result['body']) ? array_keys($result['body']) : 'not_string_or_array'),
                            'content_type' => $result['content_type'] ?? 'unknown'
                        ]
                    ];
                }
            } else {
                // Manejar diferentes códigos de error o respuesta vacía
                $error_message = 'Error al obtener el PDF';
                
                // Si el status es 200 pero el body está vacío
                if ($result['status'] == 200 && empty($result['body'])) {
                    $error_message = 'La API devolvió una respuesta vacía. Posibles causas: CDC inválido, documento no encontrado, o problema en el servidor de FacturaSend.';
                } elseif (isset($result['body']['error'])) {
                    $error_message = $result['body']['error'];
                } elseif (isset($result['body']['message'])) {
                    $error_message = $result['body']['message'];
                } elseif (is_string($result['body']) && !empty($result['body'])) {
                    $error_message = $result['body'];
                }
                
                switch ($result['status']) {
                    case 404:
                        $error_message = 'Documento no encontrado con el CDC proporcionado';
                        break;
                    case 401:
                        $error_message = 'Error de autenticación con la API';
                        break;
                    case 403:
                        $error_message = 'No tiene permisos para acceder a este documento';
                        break;
                    case 422:
                        $error_message = 'Datos de solicitud inválidos - CDC: ' . $cdc;
                        break;
                    case 500:
                        $error_message = 'Error interno del servidor de FacturaSend';
                        break;
                    case 503:
                        $error_message = 'Servicio de FacturaSend no disponible';
                        break;
                    default:
                        $error_message .= ' (Código: ' . $result['status'] . ')';
                }
                
                $response = [
                    'success' => false,
                    'message' => $error_message,
                    'debug_info' => [
                        'status' => $result['status'],
                        'response' => $result
                    ]
                ];
            }
        } catch (Exception $e) {
            $response = [
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ];
            
            // Log del error para depuración
            log_message('error', 'Error al descargar PDF factura CDC ' . $cdc . ': ' . $e->getMessage());
        }
        
        echo json_encode($response);
    }

    /**
     * Función auxiliar para extraer PDF de respuesta anidada
     * @param array $data
     * @return string|null
     */
    private function _extract_pdf_from_response($data)
    {
        if (is_array($data)) {
            // Primero buscar keys específicos que probablemente contengan el PDF
            $priority_keys = ['data', 'pdf', 'result', 'base64', 'content'];
            foreach ($priority_keys as $priority_key) {
                if (isset($data[$priority_key])) {
                    if (is_string($data[$priority_key]) && !empty($data[$priority_key])) {
                        // Verificar si parece ser base64 válido
                        $cleaned = preg_replace('/[^A-Za-z0-9+\/=]/', '', $data[$priority_key]);
                        if (strlen($cleaned) > 100 && base64_decode($cleaned, true) !== false) {
                            return $cleaned;
                        }
                    } elseif (is_array($data[$priority_key])) {
                        $result = $this->_extract_pdf_from_response($data[$priority_key]);
                        if ($result !== null) {
                            return $result;
                        }
                    }
                }
            }
            
            // Si no encontramos en keys prioritarios, buscar en todos
            foreach ($data as $key => $value) {
                if (is_string($value) && !empty($value)) {
                    // Si la key contiene términos relacionados con PDF
                    if (stripos($key, 'pdf') !== false || 
                        stripos($key, 'base64') !== false || 
                        stripos($key, 'data') !== false ||
                        stripos($key, 'content') !== false) {
                        
                        // Verificar si parece ser base64 válido
                        $cleaned = preg_replace('/[^A-Za-z0-9+\/=]/', '', $value);
                        if (strlen($cleaned) > 100 && base64_decode($cleaned, true) !== false) {
                            return $cleaned;
                        }
                    }
                } elseif (is_array($value)) {
                    $result = $this->_extract_pdf_from_response($value);
                    if ($result !== null) {
                        return $result;
                    }
                }
            }
            
            // Como último recurso, buscar cualquier string que parezca base64 válido
            foreach ($data as $value) {
                if (is_string($value) && strlen($value) > 100) {
                    $cleaned = preg_replace('/[^A-Za-z0-9+\/=]/', '', $value);
                    $decoded = base64_decode($cleaned, true);
                    if ($decoded !== false && substr($decoded, 0, 4) === '%PDF') {
                        return $cleaned;
                    }
                }
            }
        }
        return null;
    }

    /**
     * Obtiene o crea un usuario en py_factura_usuario basado en el usuario del sistema
     * @param int $system_user_id ID del usuario en tbl_users
     * @return int ID del usuario en py_factura_usuario
     */
    private function get_or_create_py_usuario($system_user_id) {
        // Buscar si ya existe el usuario en py_factura_usuario
        $existing_user = $this->db->get_where('py_factura_usuario', ['sistema_user_id' => $system_user_id])->row();
        
        if ($existing_user) {
            return $existing_user->id;
        }
        
        // Si no existe, obtener datos del usuario del sistema
        $system_user = $this->db->get_where('tbl_users', ['id' => $system_user_id])->row();
        
        if (!$system_user) {
            // Si no existe el usuario del sistema, usar un usuario por defecto o crear uno
            return $this->create_default_py_usuario();
        }
        
        // Crear nuevo usuario en py_factura_usuario
        $py_user_data = [
            'sistema_user_id' => $system_user_id,
            'nombre' => $system_user->full_name ?? 'Usuario Sistema',
            'documento' => $system_user->phone ?? '0000000',
            'cargo' => 'Operador Sistema',
            'activo' => 1,
            'fecha_creacion' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('py_factura_usuario', $py_user_data);
        return $this->db->insert_id();
    }
    
    /**
     * Crea un usuario por defecto en py_factura_usuario
     * @return int ID del usuario creado
     */
    private function create_default_py_usuario() {
        // Verificar si ya existe un usuario por defecto
        $default_user = $this->db->get_where('py_factura_usuario', ['documento' => 'SISTEMA_DEFAULT'])->row();
        
        if ($default_user) {
            return $default_user->id;
        }
        
        // Crear usuario por defecto
        $default_data = [
            'sistema_user_id' => null,
            'nombre' => 'Usuario Sistema Por Defecto',
            'documento' => 'SISTEMA_DEFAULT',
            'cargo' => 'Sistema',
            'activo' => 1,
            'fecha_creacion' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('py_factura_usuario', $default_data);
        return $this->db->insert_id();
    }

    /**
     * Inicializa la tabla py_factura_usuario con usuarios necesarios
     * Método para resolver problemas de foreign key constraints
     */
    public function inicializar_usuarios_py() {
        // Solo permitir acceso a administradores
        if (!$this->session->userdata('user_id')) {
            show_404();
            return;
        }
        
        $resultado = [
            'usuarios_creados' => 0,
            'usuario_defecto_id' => null,
            'errores' => []
        ];
        
        try {
            // 1. Verificar si la tabla tiene la columna sistema_user_id
            $columns = $this->db->list_fields('py_factura_usuario');
            if (!in_array('sistema_user_id', $columns)) {
                // Añadir la columna si no existe
                $this->db->query('ALTER TABLE py_factura_usuario ADD COLUMN sistema_user_id INT DEFAULT NULL');
                $resultado['columna_agregada'] = true;
            }
            
            // 2. Crear usuario por defecto
            $resultado['usuario_defecto_id'] = $this->create_default_py_usuario();
            
            // 3. Mapear usuarios existentes del sistema
            $system_users = $this->db->limit(10)->get('users')->result();
            foreach ($system_users as $user) {
                $existing = $this->db->get_where('py_factura_usuario', ['sistema_user_id' => $user->id])->row();
                if (!$existing) {
                    $py_user_data = [
                        'sistema_user_id' => $user->id,
                        'documento_numero' => isset($user->document) ? $user->document : '' . $user->id,
                        'nombre' => isset($user->full_name) ? $user->full_name : (isset($user->name) ? $user->name : 'Usuario Sistema'),
                        'cargo' => 'Usuario Sistema'
                    ];
                    
                    $this->db->insert('py_factura_usuario', $py_user_data);
                    $resultado['usuarios_creados']++;
                }
            }
            
            // 4. Verificar estado de foreign keys
            $audit_count = $this->db->count_all('py_facturas_auditoria');
            $invalid_fks = $this->db->query('
                SELECT COUNT(*) as invalid_count 
                FROM py_facturas_auditoria pa 
                WHERE pa.usuario_id NOT IN (SELECT id FROM py_factura_usuario)
            ')->row();
            
            $resultado['total_audit_records'] = $audit_count;
            $resultado['invalid_foreign_keys'] = $invalid_fks->invalid_count;
            
            echo json_encode(['success' => true, 'data' => $resultado]);
            
        } catch (Exception $e) {
            $resultado['errores'][] = $e->getMessage();
            echo json_encode(['success' => false, 'error' => $e->getMessage(), 'data' => $resultado]);
        }
    }

}