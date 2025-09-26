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
     */
    public function listado() {
        $filters = [
            'fecha_inicio' => $this->input->get('fecha_inicio'),
            'fecha_fin' => $this->input->get('fecha_fin'),
            'sucursal_id' => $this->input->get('sucursal_id'),
            'punto_id' => $this->input->get('punto_id'),
            'usuario_id' => $this->input->get('usuario_id'),
        ];

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
            $data['factura'] = $this->Facturacion_py_model->get_factura_completa($id);
            $data['form_title'] = 'Detalles de la Factura Electrónica';
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
        // La lógica para diferenciar edición y creación
        if ($id) {
            $this->session->set_flashdata('error_custom', "La funcionalidad de edición para la factura ID: $id aún no está implementada.");
            redirect('Facturacion_py/listado');
            return;
        }

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
            redirect('Facturacion_py/formulario');
            return;
        }

        // Validar el tipo de documento y realizar validaciones específicas
        $tipo_documento = isset($post_data['tipoDocumento']) ? (int)$post_data['tipoDocumento'] : 1;
        
        switch ($tipo_documento) {
            case 5: // Nota de Crédito
                if (empty($post_data['documento_referencia']) || empty($post_data['documento_referencia']['cdc'])) {
                    $this->session->set_flashdata('error_custom', "Para notas de crédito debe especificar el CDC del documento asociado");
                    redirect('Facturacion_py/formulario');
                    return;
                }
                break;
            case 6: // Nota de Débito
                if (empty($post_data['documento_referencia']) || empty($post_data['documento_referencia']['cdc'])) {
                    $this->session->set_flashdata('error_custom', "Para notas de débito debe especificar el CDC del documento asociado");
                    redirect('Facturacion_py/formulario');
                    return;
                }
                break;
            case 7: // Nota de Remisión
                if (empty($post_data['remision']) || empty($post_data['remision']['motivo_traslado'])) {
                    $this->session->set_flashdata('error_custom', "Para notas de remisión debe especificar el motivo de traslado");
                    redirect('Facturacion_py/formulario');
                    return;
                }
                if (empty($post_data['remision']['vehiculo_matricula'])) {
                    $this->session->set_flashdata('error_custom', "Debe especificar la matrícula del vehículo para la nota de remisión");
                    redirect('Facturacion_py/formulario');
                    return;
                }
                if (empty($post_data['remision']['fecha_inicio']) || empty($post_data['remision']['fecha_fin'])) {
                    $this->session->set_flashdata('error_custom', "Debe especificar las fechas de inicio y fin de traslado");
                    redirect('Facturacion_py/formulario');
                    return;
                }
                break;
        }

        // 2. Obtener el punto de expedición
        $punto_exp_codigo = $post_data['punto'];
        $punto_expedicion = $this->db->select('pe.*, s.codigo_establecimiento')
            ->from('py_sifen_puntos_expedicion pe')
            ->join('py_sifen_sucursales s', 's.id = pe.sucursal_id', 'left')
            ->where('pe.codigo_punto', $punto_exp_codigo)
            ->where('pe.activo', 1)
            ->get()
            ->row();
        
        if (!$punto_expedicion) {
            $this->session->set_flashdata('error_custom', "Punto de expedición '{$punto_exp_codigo}' no es válido o está inactivo.");
            redirect('Facturacion_py/formulario');
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
            $this->session->set_flashdata('error_custom', "El punto de expedición '{$punto_exp_codigo}' no tiene un timbrado activo y vigente.");
            redirect('Facturacion_py/formulario');
            return;
        }
        
        // Completar el objeto punto_expedicion con la información adicional necesaria
        $punto_expedicion->timbrado_id = $timbrado_info->id;
        $punto_expedicion->numero_timbrado = $timbrado_info->numero_timbrado;

        // 3. Normalizar datos para el cliente
        // Determinar si es contribuyente basado en el tipo o si tiene RUC con guión
        $es_contribuyente = ($post_data['cliente']['tipoContribuyente'] == 2 || (strpos($post_data['cliente']['ruc'], '-') !== false));

        $cliente_normalizado = [
            'id_sistema'        => $post_data['cliente']['codigo'] ?? null,
            'es_contribuyente'  => $es_contribuyente,
            'ruc'               => $post_data['cliente']['ruc'],
            'nombre'            => $post_data['cliente']['razonSocial'],
            'nombre_fantasia'   => $post_data['cliente']['nombreFantasia'] ?? $post_data['cliente']['razonSocial'],
            'email'             => $post_data['cliente']['email'] ?? '',
            'direccion'         => $post_data['cliente']['direccion'] ?? '',
            'es_proveedor_estado' => ($post_data['cliente']['tipoOperacion'] == 3), // B2G
            'tipo_contribuyente'=> (int)$post_data['cliente']['tipoContribuyente'],
            'tipo_documento'    => (int)$post_data['cliente']['documentoTipo'],
            'departamento_id'   => (int)$post_data['cliente']['departamento'],
            'distrito_id'       => (int)$post_data['cliente']['distrito'],
            'ciudad_id'         => (int)$post_data['cliente']['ciudad'],
            'pais_codigo'       => 'PRY', // Default para Paraguay
            'numero_casa'       => (string)($post_data['cliente']['numeroCasa'] ?? '0'),
        ];

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
            redirect('Facturacion_py/formulario');
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

        // 7. Construir estructura final para el helper
        $invoice_data = [
            'venta_id_sistema'  => null, // No aplica para factura manual
            'fecha'             => $post_data['fecha'],
            'moneda'            => $post_data['moneda'],
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

        // 8. Llamar al helper para procesar la factura
        $resultado_helper = fs_create_and_send_invoice($invoice_data);

        // 9. Manejar la respuesta
        if (isset($resultado_helper['status']) && $resultado_helper['status'] === 'success') {
            // Si hay un ID de venta en el sistema, actualizar la referencia
            if (!empty($post_data['id_sistema'])) {
                $this->db->where('id', $post_data['id_sistema'])
                         ->update('tbl_sales', ['py_factura_id' => $resultado_helper['factura_py_id']]);
            }
            
            $this->session->set_flashdata('exception', "Factura generada y enviada correctamente. CDC: " . ($resultado_helper['cdc'] ?? 'N/A'));
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
}