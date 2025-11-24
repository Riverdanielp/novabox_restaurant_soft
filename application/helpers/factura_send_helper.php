<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Helper para consultas directas a tablas de facturación electrónica (Devuelve objetos)
 * Prefijo de funciones: fs_
 * Compatible con CodeIgniter 3
 * Desarrollado por NOVABOX
 */

/**
 * Retorna una instancia del objeto CI
 */
if (!function_exists('fs_ci')) {
    function fs_ci()
    {
        $CI =& get_instance();
        return $CI;
    }
}

// Departamentos
if (!function_exists('fs_get_departamentos')) {
    function fs_get_departamentos($id = null)
    {
        $ci = fs_ci();
        if ($id !== null) {
            $q = $ci->db->get_where('py_departamentos', ['id' => $id]);
            return $q->row();
        }
        $q = $ci->db->get('py_departamentos');
        return $q->result();
    }
}

// Distritos
if (!function_exists('fs_get_distritos')) {
    function fs_get_distritos($id = null, $departamento_id = null)
    {
        $ci = fs_ci();
        if ($id !== null) {
            $q = $ci->db->get_where('py_distritos', ['id' => $id]);
            return $q->row();
        }
        if ($departamento_id !== null) {
            $q = $ci->db->get_where('py_distritos', ['departamento_id' => $departamento_id]);
            return $q->result();
        }
        $q = $ci->db->get('py_distritos');
        return $q->result();
    }
}

// Ciudades
if (!function_exists('fs_get_ciudades')) {
    function fs_get_ciudades($id = null, $distrito_id = null)
    {
        $ci = fs_ci();
        if ($id !== null) {
            $q = $ci->db->get_where('py_ciudades', ['id' => $id]);
            return $q->row();
        }
        if ($distrito_id !== null) {
            $q = $ci->db->get_where('py_ciudades', ['distrito_id' => $distrito_id]);
            return $q->result();
        }
        $q = $ci->db->get('py_ciudades');
        return $q->result();
    }
}

// Medidas
if (!function_exists('fs_get_medidas')) {
    function fs_get_medidas($id = null)
    {
        $ci = fs_ci();
        if ($id !== null) {
            $q = $ci->db->get_where('py_medidas', ['id' => $id]);
            return $q->row();
        }
        $q = $ci->db->get('py_medidas');
        return $q->result();
    }
}

// Monedas
if (!function_exists('fs_get_monedas')) {
    function fs_get_monedas($id = null)
    {
        $ci = fs_ci();
        if ($id !== null) {
            $q = $ci->db->get_where('py_monedas', ['id' => $id]);
            return $q->row();
        }
        $q = $ci->db->get('py_monedas');
        return $q->result();
    }
}

// Paises
if (!function_exists('fs_get_paises')) {
    function fs_get_paises($id = null)
    {
        $ci = fs_ci();
        if ($id !== null) {
            $q = $ci->db->get_where('py_paises', ['id' => $id]);
            return $q->row();
        }
        $q = $ci->db->get('py_paises');
        return $q->result();
    }
}

// Sucursales
if (!function_exists('fs_get_sucursales')) {
    function fs_get_sucursales($id = null)
    {
        $ci = fs_ci();
        if ($id !== null) {
            $q = $ci->db->get_where('py_sifen_sucursales', ['id' => $id]);
            return $q->row();
        }
        $q = $ci->db->get('py_sifen_sucursales');
        return $q->result();
    }
}

// Puntos de expedición
if (!function_exists('fs_get_puntos_expedicion')) {
    function fs_get_puntos_expedicion($id = null, $sucursal_id = null)
    {
        $ci = fs_ci();
        if ($id !== null) {
            $q = $ci->db->get_where('py_sifen_puntos_expedicion', ['id' => $id]);
            return $q->row();
        }
        if ($sucursal_id !== null) {
            $q = $ci->db->get_where('py_sifen_puntos_expedicion', ['sucursal_id' => $sucursal_id]);
            return $q->result();
        }
        $q = $ci->db->get('py_sifen_puntos_expedicion');
        return $q->result();
    }
}

// Timbrados
if (!function_exists('fs_get_timbrados')) {
    function fs_get_timbrados($id = null)
    {
        $ci = fs_ci();
        if ($id !== null) {
            $q = $ci->db->get_where('py_sifen_timbrados', ['id' => $id]);
            return $q->row();
        }
        $q = $ci->db->get('py_sifen_timbrados');
        return $q->result();
    }
}

// Timbrados puntos
if (!function_exists('fs_get_timbrados_puntos')) {
    function fs_get_timbrados_puntos($id = null, $timbrado_id = null, $punto_expedicion_id = null)
    {
        $ci = fs_ci();
        if ($id !== null) {
            $q = $ci->db->get_where('py_sifen_timbrados_puntos', ['id' => $id]);
            return $q->row();
        }
        if ($timbrado_id !== null) {
            $q = $ci->db->get_where('py_sifen_timbrados_puntos', ['timbrado_id' => $timbrado_id]);
            return $q->result();
        }
        if ($punto_expedicion_id !== null) {
            $q = $ci->db->get_where('py_sifen_timbrados_puntos', ['punto_expedicion_id' => $punto_expedicion_id]);
            return $q->result();
        }
        $q = $ci->db->get('py_sifen_timbrados_puntos');
        return $q->result();
    }
}

// Estados de documento electrónico
if (!function_exists('fs_get_documentos_estados')) {
    function fs_get_documentos_estados($id = null)
    {
        $ci = fs_ci();
        if ($id !== null) {
            $q = $ci->db->get_where('py_sifen_documentos_estados', ['id' => $id]);
            return $q->row();
        }
        $q = $ci->db->get('py_sifen_documentos_estados');
        return $q->result();
    }
}

// Clientes
if (!function_exists('fs_get_clientes')) {
    function fs_get_clientes($id = null, $codigo = null)
    {
        $ci = fs_ci();
        if ($id !== null) {
            $q = $ci->db->get_where('py_factura_cliente', ['id' => $id]);
            return $q->row();
        }
        if ($codigo !== null) {
            $q = $ci->db->get_where('py_factura_cliente', ['codigo' => $codigo]);
            return $q->row();
        }
        $q = $ci->db->get('py_factura_cliente');
        return $q->result();
    }
}

// Usuarios
if (!function_exists('fs_get_usuarios')) {
    function fs_get_usuarios($id = null)
    {
        $ci = fs_ci();
        if ($id !== null) {
            $q = $ci->db->get_where('py_factura_usuario', ['id' => $id]);
            return $q->row();
        }
        $q = $ci->db->get('py_factura_usuario');
        return $q->result();
    }
}

// Facturas electrónicas
if (!function_exists('fs_get_facturas_electronicas')) {
    function fs_get_facturas_electronicas($id = null, $cliente_id = null)
    {
        $ci = fs_ci();
        if ($id !== null) {
            $q = $ci->db->get_where('py_facturas_electronicas', ['id' => $id]);
            return $q->row();
        }
        if ($cliente_id !== null) {
            $q = $ci->db->get_where('py_facturas_electronicas', ['cliente_id' => $cliente_id]);
            return $q->result();
        }
        $q = $ci->db->get('py_facturas_electronicas');
        return $q->result();
    }
}

// Items de factura
if (!function_exists('fs_get_factura_items')) {
    function fs_get_factura_items($id = null, $factura_id = null)
    {
        $ci = fs_ci();
        if ($id !== null) {
            $q = $ci->db->get_where('py_factura_items', ['id' => $id]);
            return $q->row();
        }
        if ($factura_id !== null) {
            $q = $ci->db->get_where('py_factura_items', ['factura_id' => $factura_id]);
            return $q->result();
        }
        $q = $ci->db->get('py_factura_items');
        return $q->result();
    }
}

// Condiciones de factura
if (!function_exists('fs_get_factura_condiciones')) {
    function fs_get_factura_condiciones($id = null, $factura_id = null)
    {
        $ci = fs_ci();
        if ($id !== null) {
            $q = $ci->db->get_where('py_factura_condiciones', ['id' => $id]);
            return $q->row();
        }
        if ($factura_id !== null) {
            $q = $ci->db->get_where('py_factura_condiciones', ['factura_id' => $factura_id]);
            return $q->result();
        }
        $q = $ci->db->get('py_factura_condiciones');
        return $q->result();
    }
}

// Entregas de condición de factura
if (!function_exists('fs_get_factura_condicion_entregas')) {
    function fs_get_factura_condicion_entregas($id = null, $condicion_id = null)
    {
        $ci = fs_ci();
        if ($id !== null) {
            $q = $ci->db->get_where('py_factura_condicion_entregas', ['id' => $id]);
            return $q->row();
        }
        if ($condicion_id !== null) {
            $q = $ci->db->get_where('py_factura_condicion_entregas', ['condicion_id' => $condicion_id]);
            return $q->result();
        }
        $q = $ci->db->get('py_factura_condicion_entregas');
        return $q->result();
    }
}

// Info tarjetas
if (!function_exists('fs_get_factura_condicion_tarjetas')) {
    function fs_get_factura_condicion_tarjetas($id = null)
    {
        $ci = fs_ci();
        if ($id !== null) {
            $q = $ci->db->get_where('py_factura_condicion_tarjetas', ['id' => $id]);
            return $q->row();
        }
        $q = $ci->db->get('py_factura_condicion_tarjetas');
        return $q->result();
    }
}

// Info cheques
if (!function_exists('fs_get_factura_condicion_cheques')) {
    function fs_get_factura_condicion_cheques($id = null)
    {
        $ci = fs_ci();
        if ($id !== null) {
            $q = $ci->db->get_where('py_factura_condicion_cheques', ['id' => $id]);
            return $q->row();
        }
        $q = $ci->db->get('py_factura_condicion_cheques');
        return $q->result();
    }
}

// Crédito
if (!function_exists('fs_get_factura_condicion_credito')) {
    function fs_get_factura_condicion_credito($id = null, $condicion_id = null)
    {
        $ci = fs_ci();
        if ($id !== null) {
            $q = $ci->db->get_where('py_factura_condicion_credito', ['id' => $id]);
            return $q->row();
        }
        if ($condicion_id !== null) {
            $q = $ci->db->get_where('py_factura_condicion_credito', ['condicion_id' => $condicion_id]);
            return $q->result();
        }
        $q = $ci->db->get('py_factura_condicion_credito');
        return $q->result();
    }
}

// Cuotas de crédito
if (!function_exists('fs_get_factura_condicion_credito_cuotas')) {
    function fs_get_factura_condicion_credito_cuotas($id = null, $credito_id = null)
    {
        $ci = fs_ci();
        if ($id !== null) {
            $q = $ci->db->get_where('py_factura_condicion_credito_cuotas', ['id' => $id]);
            return $q->row();
        }
        if ($credito_id !== null) {
            $q = $ci->db->get_where('py_factura_condicion_credito_cuotas', ['credito_id' => $credito_id]);
            return $q->result();
        }
        $q = $ci->db->get('py_factura_condicion_credito_cuotas');
        return $q->result();
    }
}

// Auditoría
if (!function_exists('fs_get_facturas_auditoria')) {
    function fs_get_facturas_auditoria($id = null, $factura_id = null)
    {
        $ci = fs_ci();
        if ($id !== null) {
            $q = $ci->db->get_where('py_facturas_auditoria', ['id' => $id]);
            return $q->row();
        }
        if ($factura_id !== null) {
            $q = $ci->db->get_where('py_facturas_auditoria', ['factura_id' => $factura_id]);
            return $q->result();
        }
        $q = $ci->db->get('py_facturas_auditoria');
        return $q->result();
    }
}

    
if (!function_exists('fs_default_departamento')) {
    function fs_default_departamento(){
        $CI =& get_instance();
        $CI->load->config('config');  // Cargar el archivo de configuración

        // Obtener las configuraciones desde config.php
        $return = $CI->config->item('sifen_default_departamento') ?? '';
        return $return;
    }
}

if (!function_exists('fs_default_distrito')) {
    function fs_default_distrito(){
        $CI =& get_instance();
        $CI->load->config('config');  // Cargar el archivo de configuración

        // Obtener las configuraciones desde config.php
        $return = $CI->config->item('sifen_default_distrito') ?? '';
        return $return;
    }
}

if (!function_exists('fs_default_ciudad')) {
    function fs_default_ciudad(){
        $CI =& get_instance();
        $CI->load->config('config');  // Cargar el archivo de configuración

        // Obtener las configuraciones desde config.php
        $return = $CI->config->item('sifen_default_ciudad') ?? '';
        return $return;
    }
}

if (!function_exists('fs_create_and_send_invoice')) {
    function fs_create_and_send_invoice($data)
    {
        $ci = fs_ci();
        $ci->load->library('facturasend');
        
        // ===== SOLUCIÓN 1: Configuración de reintentos para deadlocks =====
        $maxRetries = 3;
        $retryDelay = 100; // milisegundos (0.1 segundos)
        $attempt = 0;
        
        while ($attempt < $maxRetries) {
            $attempt++;
            
            try {
                // ===== Variables iniciales =====
                $factura_py_id = $data['factura_py_id_existente'] ?? null;
                $is_resend = ($factura_py_id !== null);

                // <<<<<<< INICIO: Variables para el tipo de numeración >>>>>>>
                $numero_factura_tipo = $data['numero_factura_tipo'] ?? 'correlativo';
                $es_numero_personalizado = ($numero_factura_tipo === 'personalizado');
                // <<<<<<< FIN: Variables para el tipo de numeración >>>>>>>

                // Asegurar que tipo_documento tenga un valor por defecto
                if (!isset($data['tipo_documento']) || $data['tipo_documento'] === null) {
                    $data['tipo_documento'] = 1; // 1 = Factura Electrónica (valor por defecto)
                }

                // Validaciones adicionales de datos críticos
                if (empty($data['fecha'])) {
                    return [
                        'status' => 'error',
                        'message' => 'La fecha es requerida para crear la factura'
                    ];
                }

                if (empty($data['moneda'])) {
                    $data['moneda'] = 'PYG'; // Valor por defecto
                }

                if (empty($data['punto_expedicion']) || !isset($data['punto_expedicion']->id)) {
                    return [
                        'status' => 'error',
                        'message' => 'El punto de expedición es requerido para crear la factura'
                    ];
                }

                $ci->db->trans_begin();

                // ===== SOLUCIÓN 2: Bloquear el punto de expedición dentro de la transacción =====
                $punto_exp_bloqueado = fs_get_and_lock_punto_expedicion($data['punto_expedicion']->sucursal_id);
                
                if (!$punto_exp_bloqueado) {
                    throw new Exception("No se pudo bloquear el punto de expedición para la sucursal");
                }
                
                // Usar el punto bloqueado en vez del que viene en los datos
                $punto_exp = $punto_exp_bloqueado;

                // ===== SOLUCIÓN 3: Orden consistente de acceso a recursos =====
                // 1. Guardar/actualizar cliente
                $cliente_py_id = fs_save_or_update_cliente($data['cliente']);
                
                // 2. Guardar/actualizar usuario
                $usuario_py_id = fs_save_or_update_usuario($data['usuario']);

                // 3. Verificar que se obtuvieron IDs válidos
                if (!$cliente_py_id || $cliente_py_id == 0) {
                    throw new Exception("Error al guardar o actualizar el cliente: No se obtuvo un ID válido");
                }
                
                if (!$usuario_py_id || $usuario_py_id == 0) {
                    throw new Exception("Error al guardar o actualizar el usuario: No se obtuvo un ID válido");
                }

                $json_original = $data;
                if (!(isset($data['ruc']))){
                    $json_original['ruc'] = $data['cliente']['documentoNumero'] ?? '0';
                }
                
                // 4. Insertar/actualizar factura principal
                if ($is_resend) {
                    // REENVÍO: Usar el número de factura existente.
                    $existing_invoice = $ci->db->get_where('py_facturas_electronicas', ['id' => $factura_py_id])->row();
                    if (!$existing_invoice) {
                        throw new Exception("No se encontró la factura a reenviar con ID: $factura_py_id");
                    }
                    $numero_factura = $existing_invoice->numero;

                    // Actualizar el registro existente
                    $factura_db = [
                        'tipo_documento'      => $data['tipo_documento'],
                        'sucursal_id'         => $punto_exp->sucursal_id,
                        'punto_expedicion_id' => $punto_exp->id,
                        'timbrado_id'         => $punto_exp->timbrado_id,
                        'fecha'               => $data['fecha'],
                        'moneda'              => $data['moneda'],
                        'cliente_id'          => $cliente_py_id,
                        'usuario_id'          => $usuario_py_id,
                        'estado'              => 0, // Volver a estado "Generado"
                        'id_sale'             => $data['venta_id_sistema'] ?? $existing_invoice->id_sale,
                        'json_original'       => json_encode($data),
                        'cdc'                 => null, // Limpiar datos de envío anterior
                        'lote_id'             => null,
                        'qr'                  => null
                    ];
                    $ci->db->where('id', $factura_py_id)->update('py_facturas_electronicas', $factura_db);

                    } else {
                    // CREACIÓN: Obtener un nuevo número de factura.
                    // <<<<<<< INICIO: Lógica para determinar el número de factura >>>>>>>
                    if ($es_numero_personalizado) {
                        $numero_factura = $data['numero_factura_personalizado'];
                    } else {
                        if ($data['tipo_documento'] == 4) { // Nota de Débito
                            $numero_factura = $punto_exp->numerador_nota_debito + 1;
                        } elseif ($data['tipo_documento'] == 5) { // Nota de Crédito
                            $numero_factura = $punto_exp->numerador_nota_credito + 1;
                        } elseif ($data['tipo_documento'] == 6) { // Nota de Remisión
                            $numero_factura = $punto_exp->numerador_nota_remision + 1;
                        } elseif ($data['tipo_documento'] == 7) { // Recibo
                            $numero_factura = $punto_exp->numerador_recibo + 1;
                        } else { // Factura Electrónica u otros tipos
                            $numero_factura = $punto_exp->numerador + 1;
                        }
                    }
                    // <<<<<<< FIN: Lógica para determinar el número de factura >>>>>>>

                    $factura_db = [
                        'tipo_documento'      => $data['tipo_documento'],
                        'sucursal_id'         => $punto_exp->sucursal_id,
                        'punto_expedicion_id' => $punto_exp->id,
                        'numero'              => $numero_factura,
                        'timbrado_id'         => $punto_exp->timbrado_id,
                        'fecha'               => $data['fecha'],
                        'moneda'              => $data['moneda'],
                        'cliente_id'          => $cliente_py_id,
                        'usuario_id'          => $usuario_py_id,
                        'estado'              => 0, // 0 = Generado
                        'id_sale'             => $data['venta_id_sistema'],
                        'json_original'       => json_encode($data)
                    ];
                    $ci->db->insert('py_facturas_electronicas', $factura_db);
                    
                    // Verificar si hubo errores en la inserción
                    if ($ci->db->error()['code'] != 0) {
                        throw new Exception("Error de base de datos al insertar factura: " . $ci->db->error()['message']);
                    }
                    
                    $factura_py_id = $ci->db->insert_id();
                    
                    // Verificar que se obtuvo un ID válido
                    if (!$factura_py_id || $factura_py_id == 0) {
                        throw new Exception("Error al insertar la factura electrónica: No se obtuvo un ID válido");
                    }
                }

                // 5. Limpiar items y condiciones relacionadas (DELETEs)
                $ci->db->where('factura_id', $factura_py_id)->delete('py_factura_items');
                $ci->db->where('factura_id', $factura_py_id)->delete('py_factura_condiciones');
                
                // 6. Insertar nuevos items y condiciones
                $items_api = fs_save_invoice_items($factura_py_id, $data['items']);
                fs_save_invoice_condition($factura_py_id, $data['condicion_venta']);
                
                // Construir payload para la API
                $lote_para_api = fs_build_api_payload($data, $numero_factura, $cliente_py_id, $items_api);
                $response = $ci->facturasend->crear_lote_documentos($lote_para_api);

                    if (isset($response['status']) && $response['status'] == 200 && ($response['body']['success'] ?? false)) {
                    $cdc = $response['body']['result']['deList'][0]['cdc'];
                    $loteId = $response['body']['result']['loteId'];
                    $qr = $response['body']['result']['deList'][0]['qr'];
                    $fechaEmision = $response['body']['result']['deList'][0]['fechaEmision'];
                    $dIVA5 = $response['body']['result']['deList'][0]['dIVA5'];
                    $dIVA10 = $response['body']['result']['deList'][0]['dIVA10'];

                    $update_data = [
                        'cdc' => $cdc, 
                        'estado' => 1,
                        'lote_id' => $loteId,
                        'qr' => $qr,
                        'fecha_emision' => $fechaEmision,
                        'iva5' => $dIVA5,
                        'iva10' => $dIVA10,
                        'json_enviado' => json_encode($lote_para_api)
                    ];
                    
                    $ci->db->where('id', $factura_py_id)->update('py_facturas_electronicas', $update_data);

                    // 7. Actualizar numerador del punto (si aplica)
                    // Incrementar el numerador SÓLO si es una NUEVA factura Y NO es un número personalizado.
                    if (!$is_resend && !$es_numero_personalizado) {
                        if ($data['tipo_documento'] == 4) { // Nota de Débito
                            $ci->db->set('numerador_nota_debito', 'numerador_nota_debito + 1', FALSE)->where('id', $punto_exp->id)->update('py_sifen_puntos_expedicion');
                        } elseif ($data['tipo_documento'] == 5) { // Nota de Crédito
                            $ci->db->set('numerador_nota_credito', 'numerador_nota_credito + 1', FALSE)->where('id', $punto_exp->id)->update('py_sifen_puntos_expedicion');
                        } elseif ($data['tipo_documento'] == 6) { // Nota de Remisión
                            $ci->db->set('numerador_nota_remision', 'numerador_nota_remision + 1', FALSE)->where('id', $punto_exp->id)->update('py_sifen_puntos_expedicion');
                        } elseif ($data['tipo_documento'] == 7) { // Recibo
                            $ci->db->set('numerador_recibo', 'numerador_recibo + 1', FALSE)->where('id', $punto_exp->id)->update('py_sifen_puntos_expedicion');
                        } else { // Factura Electrónica u otros tipos
                            $ci->db->set('numerador', 'numerador + 1', FALSE)->where('id', $punto_exp->id)->update('py_sifen_puntos_expedicion');
                        }
                    }
                    
                    // 8. Commit de la transacción
                    $ci->db->trans_commit();
                    
                    // ===== SOLUCIÓN 4: Auditoría FUERA de la transacción =====
                    fs_log_auditoria($factura_py_id, $usuario_py_id, $is_resend ? 'API_REENVIO_EXITO' : 'API_EXITO', $response['body']);

                    return [
                        'status'        => 'success', 
                        'factura_py_id' => $factura_py_id,
                        'loteId'        => $loteId, 
                        'cdc'           => $cdc, 
                        'qr'            => $qr,
                        'fechaEmision'  => $fechaEmision,
                        'iva5'          => $dIVA5,
                        'iva10'         => $dIVA10
                    ];

                } else {
                    // ERROR: Actualizar estado a "Rechazado" y NO consumir el número.
                    $ci->db->where('id', $factura_py_id)->update('py_facturas_electronicas', ['estado' => 4,'json_enviado' => json_encode($lote_para_api)]);
                    
                    // Confirmamos la transacción (guardando el estado de error), pero el numerador no se tocó.
                    $ci->db->trans_commit();
                    
                    // ===== SOLUCIÓN 4: Auditoría FUERA de la transacción =====
                    fs_log_auditoria($factura_py_id, $usuario_py_id, $is_resend ? 'API_REENVIO_ERROR' : 'API_ERROR', $response);

                    return [
                        'status'         => 'error', 
                        'factura_py_id'  => $factura_py_id,
                        'message'        => 'La API de facturación devolvió un error. El intento ha sido registrado.', 
                        'api_response'   => $response
                    ];
                }

            } catch (Exception $e) {
                $ci->db->trans_rollback();
                
                // ===== SOLUCIÓN 1: Verificar si es deadlock y reintentar =====
                $isDeadlock = (
                    stripos($e->getMessage(), 'deadlock') !== false ||
                    stripos($e->getMessage(), 'lock wait timeout') !== false
                );
                
                if ($isDeadlock && $attempt < $maxRetries) {
                    // Esperar con backoff exponencial antes de reintentar
                    $waitTime = $retryDelay * pow(2, $attempt - 1);
                    usleep($waitTime * 1000); // Convertir a microsegundos
                    
                    // Log del reintento
                    log_message('warning', "Deadlock detectado en facturación. Reintento {$attempt}/{$maxRetries}. Error: " . $e->getMessage());
                    
                    continue; // Reintentar
                }
                
                // Si no es deadlock o se agotaron reintentos, registrar y fallar
                // ===== SOLUCIÓN 4: Auditoría FUERA de la transacción (en caso de error) =====
                fs_log_auditoria($factura_py_id, $usuario_py_id ?? null, 'EXCEPCION', [
                    'error' => $e->getMessage(),
                    'data' => $data,
                    'attempt' => $attempt
                ]);
                
                return [
                    'status'        => 'error', 
                    'factura_py_id' => $factura_py_id,
                    'message'       => "Excepción Capturada (intento {$attempt}/{$maxRetries}): " . $e->getMessage()
                ];
            }
        }
        
        // ===== No debería llegar aquí, pero por seguridad =====
        return [
            'status' => 'error',
            'message' => 'Error: Se agotaron los reintentos sin éxito'
        ];
    }
}

// application/helpers/factura_send_helper.php

/**
 * Obtiene el punto de expedición activo y lo bloquea para actualización
 * dentro de una transacción para evitar condiciones de carrera.
 *
 * @param int $sucursal_id ID de la sucursal SIFEN.
 * @return object|null El objeto del punto de expedición o null si no se encuentra.
 */
if (!function_exists('fs_get_and_lock_punto_expedicion')) {
    function fs_get_and_lock_punto_expedicion($sucursal_id) {
        $ci = fs_ci();
        
        // Consulta corregida para usar la tabla pívot py_sifen_timbrados_puntos
        $sql = "
            SELECT 
                pe.id, 
                pe.sucursal_id,
                pe.codigo_punto,
                pe.numerador,
                pe.numerador_autofactura,
                pe.numerador_nota_debito,
                pe.numerador_nota_credito,
                pe.numerador_nota_remision,
                pe.numerador_recibo,
                t.id as timbrado_id,
                t.numero_timbrado as timbrado_numero,
                t.fecha_inicio as timbrado_inicio,
                t.fecha_fin as timbrado_fin
            FROM 
                py_sifen_puntos_expedicion pe
            JOIN 
                py_sifen_timbrados_puntos tp ON pe.id = tp.punto_expedicion_id
            JOIN 
                py_sifen_timbrados t ON tp.timbrado_id = t.id
            WHERE 
                pe.sucursal_id = ? 
                AND pe.activo = 1
                AND t.activo = 1
                AND tp.fecha_baja IS NULL  -- Asegura que la asignación esté activa
                AND CURDATE() BETWEEN t.fecha_inicio AND t.fecha_fin
            ORDER BY 
                pe.id DESC
            LIMIT 1
            FOR UPDATE
        ";

        $query = $ci->db->query($sql, [$sucursal_id]);

        if (!$query) {
            log_message('error', 'Fallo en la consulta de bloqueo de punto de expedición: ' . $ci->db->error()['message']);
            return null;
        }

        if ($query->num_rows() > 0) {
            return $query->row();
        }

        return null;
    }
}

/**
 * Registra un evento en la tabla de auditoría.
 */
if (!function_exists('fs_log_auditoria')) {
    function fs_log_auditoria($factura_id, $usuario_id, $accion, $data_json) {
        $ci = fs_ci();
        $log_data = [
            'factura_id'  => $factura_id,
            'usuario_id'  => fs_get_or_create_py_usuario($usuario_id),
            'tipo_accion' => $accion,
            'json_backup' => json_encode($data_json)
        ];
        // Se ejecuta fuera de la transacción principal si es necesario, pero aquí está bien
        $ci->db->insert('py_facturas_auditoria', $log_data);
    }
}

/**
 * Obtiene o crea un usuario en py_factura_usuario basado en el usuario del sistema
 * @param int $system_user_id ID del usuario del sistema
 * @return int ID del usuario en py_factura_usuario
 */
if (!function_exists('fs_get_or_create_py_usuario')) {
    function fs_get_or_create_py_usuario($system_user_id = null) {
        $ci = fs_ci();
        
        if (!$system_user_id) {
            $system_user_id = $ci->session->userdata('user_id') ?? 0;
        }
        
        if (!$system_user_id) {
            return fs_create_default_py_usuario();
        }
        
        // Buscar si ya existe el usuario en py_factura_usuario
        $existing_user = $ci->db->get_where('py_factura_usuario', ['id' => $system_user_id])->row();
        if ($existing_user) {
            return $existing_user->id;
        }
        
        // Buscar datos del usuario en el sistema principal
        $system_user = $ci->db->get_where('tbl_users', ['id' => $system_user_id])->row();
        
        $py_user_data = [
            'id' => $system_user_id,
            'documento_numero' => $system_user ? ($system_user->documento ?? '' . $system_user_id) : '' . $system_user_id,
            'nombre' => $system_user ? ($system_user->full_name ?? $system_user->name ?? 'Usuario Sistema') : 'Usuario Sistema',
            'cargo' => 'Usuario Sistema'
        ];
        
        // Crear nuevo usuario en py_factura_usuario
        $ci->db->insert('py_factura_usuario', $py_user_data);
        return $ci->db->insert_id();
    }
}

/**
 * Crea un usuario por defecto en py_factura_usuario
 * @return int ID del usuario creado
 */
if (!function_exists('fs_create_default_py_usuario')) {
    function fs_create_default_py_usuario() {
        $ci = fs_ci();
        
        $default_user = $ci->db->get_where('py_factura_usuario', ['documento_numero' => 'SISTEMA_DEFAULT'])->row();
        if ($default_user) {
            return $default_user->id;
        }
        
        $default_data = [
            'sistema_user_id' => null,
            'documento_numero' => 'SISTEMA_DEFAULT',
            'nombre' => 'Sistema por Defecto',
            'cargo' => 'Sistema'
        ];
        
        $ci->db->insert('py_factura_usuario', $default_data);
        return $ci->db->insert_id();
    }
}



// ---- FUNCIONES DEL HELPER ACTUALIZADAS ----

if (!function_exists('fs_build_api_payload')) {
    function fs_build_api_payload($data, $numero_factura, $cliente_py_id, $items_api) {
        $cliente_data = $data['cliente']; // Usar el array normalizado
        $usuario_data = $data['usuario']; // Usar el array normalizado

        // echo '<pre>';
        // echo '<h1>Cliente Data:</h1>';
        // var_dump($cliente_data); 
        // echo '</pre>';

        // --- Objeto Cliente para la API ---
        $es_contribuyente = filter_var($cliente_data['es_contribuyente'], FILTER_VALIDATE_BOOLEAN);
        $tipoOperacion = fs_calculate_tipo_operacion($cliente_data);
        $cliente_api = [
            "contribuyente"     => $cliente_data['es_contribuyente'],
            "razonSocial"       => $cliente_data['nombre'],
            // "nombreFantasia"    => $cliente_data['nombre_fantasia'],
            "tipoOperacion"     => $tipoOperacion,
            "tipoContribuyente" => $cliente_data['tipo_contribuyente'], // CORREGIDO: Campo añadido
            "numeroCasa"        => (string)intval($cliente_data['numero_casa']),
            "direccion"         => $cliente_data['direccion'],
            "departamento"      => $cliente_data['departamento_id'],
            "distrito"          => $cliente_data['distrito_id'],
            "ciudad"            => $cliente_data['ciudad_id'],
            "pais"              => $cliente_data['pais_codigo'],
            "documentoTipo"     => $cliente_data['tipo_documento'],
            "documentoNumero"   => $cliente_data['documentoNumero'], // Asumimos que el RUC es el nro. doc. para contribuyentes
            "email"             => $cliente_data['email'],
            "celular"           => $cliente_data['celular'],
            "codigo"            => str_pad((string)$cliente_py_id, 3, '0', STR_PAD_LEFT)
        ];

        if ($es_contribuyente == true){
            $cliente_api['ruc']               = $cliente_data['ruc'];
            $cliente_api['nombre_fantasia']   = $cliente_data['nombreFantasia'] ?? '';
        } 
        // echo '<pre>';
        // echo '<h1>Cliente API:</h1>';
        // var_dump($cliente_api); 
        // echo '</pre>';

        // --- Objeto Usuario para la API ---
        $usuario_api = [
            "documentoTipo"   => 1, // CORREGIDO: Añadido por defecto (1 = Cédula Paraguaya)
            "documentoNumero" => $usuario_data['documento'],
            "nombre"          => $usuario_data['nombre'],
            "cargo"           => $usuario_data['cargo']
        ];

        // --- Payload Principal ---
        $payload = [
            // --- Datos del Documento ---
            "tipoDocumento"   => $data['tipo_documento'], // Usar el tipo de documento desde los datos
            "establecimiento" => $data['punto_expedicion']->codigo_establecimiento,
            "punto"           => $data['punto_expedicion']->codigo_punto,
            "numero"          => $numero_factura,
            "descripcion"     => "Venta de productos/servicios", // Descripción genérica
            "observacion"     => "", // Observación genérica
            "fecha"           => $data['fecha'],

            // --- Campos Fijos Requeridos ---
            "tipoEmision"     => 1, // 1 = Normal
            "tipoTransaccion" => 1, // 1 = Venta de mercadería
            "tipoImpuesto"    => 1, // CORREGIDO: 1 = IVA

            "moneda"          => $data['moneda'],

            // --- Objetos Anidados ---
            "cliente"         => $cliente_api,
            "usuario"         => $usuario_api,
            "factura"         => [
                "presencia" => 1 // 1 = Operación presencial
            ],
            "condicion"       => $data['condicion_venta'],
            "items"           => $items_api
        ];
        if ($data['tipo_documento'] == 5 || $data['tipo_documento'] == 6){
            $payload['documentoAsociado'] = [
                'formato' => 1,
                'cdc'    => $data['documentoAsociado']['cdc']
            ];
            $payload['notaCreditoDebito'] = [
                'motivo'    => $data['documentoAsociado']['motivo']
            ];
        }

        // Retornar el payload encapsulado en un array, como lo espera la API de lotes
        return [$payload];
    }
}

if (!function_exists('fs_save_invoice_items')) {
    function fs_save_invoice_items($factura_py_id, $items_normalizados) {
        $ci = fs_ci();
        $items_api = [];
        foreach ($items_normalizados as $item) {
            $iva_tipo = ($item['iva'] > 0) ? 1 : 3;
            $iva_base = ($item['iva'] > 0) ? 100 : 0;
            $item_db = [
                'factura_id'      => $factura_py_id,
                'codigo'          => $item['codigo'],
                'descripcion'     => $item['descripcion'],
                'unidad_medida_id'=> 77,
                'cantidad'        => $item['cantidad'],
                'precio_unitario' => $item['precio_unitario'],
                'iva_tipo'        => $iva_tipo,
                'iva_base'        => $iva_base,
                'iva'             => $item['iva']
            ];
            $ci->db->insert('py_factura_items', $item_db);
            
            unset($item_db['factura_id']);
            $items_api[] = $item_db;
        }
        return $items_api;
    }
}

if (!function_exists('fs_save_or_update_cliente')) {
    function fs_save_or_update_cliente($cliente_normalizado) {
        $ci = fs_ci();
        $data = [
            'contribuyente'    => $cliente_normalizado['es_contribuyente'],
            'ruc'              => $cliente_normalizado['ruc'],
            'razon_social'     => $cliente_normalizado['nombre'],
            'nombre_fantasia'  => $cliente_normalizado['nombre_fantasia'],
            'direccion'        => $cliente_normalizado['direccion'],
            'departamento'     => $cliente_normalizado['departamento_id'],
            'distrito'         => $cliente_normalizado['distrito_id'],
            'ciudad'           => $cliente_normalizado['ciudad_id'],
            'pais'             => $cliente_normalizado['pais_codigo'],
            'documento_tipo'   => $cliente_normalizado['tipo_documento'],
            'documento_numero' => $cliente_normalizado['ruc'],
            'email'            => $cliente_normalizado['email'],
            'celular'          => $cliente_normalizado['celular'],
            'codigo'           => $cliente_normalizado['id_sistema']
        ];
        
        $existing = $ci->db->where('codigo', $cliente_normalizado['id_sistema'])->get('py_factura_cliente')->row();
        if ($existing) {
            $ci->db->where('id', $existing->id)->update('py_factura_cliente', $data);
            return $existing->id;
        } else {
            $ci->db->insert('py_factura_cliente', $data);
            return $ci->db->insert_id();
        }
    }
}

if (!function_exists('fs_save_or_update_usuario')) {
    function fs_save_or_update_usuario($usuario_normalizado) {
        $ci = fs_ci();
        $data = [
            'documento_numero' => $usuario_normalizado['documento'],
            'nombre'           => $usuario_normalizado['nombre'],
            'cargo'            => $usuario_normalizado['cargo']
        ];

        $existing = $ci->db->where('documento_numero', $usuario_normalizado['documento'])->get('py_factura_usuario')->row();
        if ($existing) {
            return $existing->id;
        } else {
            $ci->db->insert('py_factura_usuario', $data);
            return $ci->db->insert_id();
        }
    }
}

if (!function_exists('fs_calculate_tipo_operacion')) {
    function fs_calculate_tipo_operacion($cliente_normalizado) {
        if ($cliente_normalizado['es_proveedor_estado']) return 3; // B2G
        if ($cliente_normalizado['es_contribuyente']) {
            return ($cliente_normalizado['tipo_contribuyente'] == 1) ? 2 : 1; // Física -> B2C, Jurídica -> B2B
        } else {
            return ($cliente_normalizado['tipo_documento'] == 3) ? 4 : 2; // Extranjera -> B2F, resto -> B2C
        }
    }
}


/**
 * Mapea el ID del método de pago del sistema al tipo de SIFEN.
 * @return int Tipo de entrega SIFEN (1=Efectivo, 2=Cheque, 3=Tarjeta, etc.).
 */
if (!function_exists('fs_map_payment_method')) {
    function fs_map_payment_method($payment_id_sistema) {
        // Este mapeo es un ejemplo, debes adaptarlo a tus IDs.
        switch ($payment_id_sistema) {
            case '1': return 1; // Efectivo
            case '2': return 3; // Tarjeta de Crédito
            case '3': return 3; // Tarjeta de Débito
            case '4': return 2; // Cheque
            default: return 9; // Otro
        }
    }
}

/**
 * Guarda la condición de venta y sus detalles en las tablas correspondientes.
 */
if (!function_exists('fs_save_invoice_condition')) {
    function fs_save_invoice_condition($factura_py_id, $condicion_venta) {
        $ci = fs_ci();
        
        // Limpiar condiciones anteriores para esta factura
        $existing_cond = $ci->db->get_where('py_factura_condiciones', ['factura_id' => $factura_py_id])->result();
        foreach ($existing_cond as $ec) {
            $ci->db->where('condicion_id', $ec->id)->delete('py_factura_condicion_entregas');
            $ci->db->where('condicion_id', $ec->id)->delete('py_factura_condicion_credito');
        }
        $ci->db->where('factura_id', $factura_py_id)->delete('py_factura_condiciones');

        // Insertar nueva condición
        $cond_db = ['factura_id' => $factura_py_id, 'tipo' => $condicion_venta['tipo']];
        $ci->db->insert('py_factura_condiciones', $cond_db);
        $condicion_id = $ci->db->insert_id();

        if ($condicion_venta['tipo'] == 1 && isset($condicion_venta['entregas'])) { // Contado
            foreach ($condicion_venta['entregas'] as $entrega) {
                $entrega['condicion_id'] = $condicion_id;
                // Aquí se debería guardar info de tarjeta/cheque si existe
                $ci->db->insert('py_factura_condicion_entregas', $entrega);
            }
        } elseif ($condicion_venta['tipo'] == 2 && isset($condicion_venta['credito'])) { // Crédito
            fs_process_credit_condition($condicion_id, $condicion_venta['credito']);
        }
    }
}


/**
 * Obtiene el primer punto de expedición activo y con timbrado vigente para una sucursal.
 * @param int $sucursal_id_sistema ID de la sucursal (outlet_id)
 * @return object|null Objeto con datos del punto y timbrado, o null si no se encuentra.
 */
if (!function_exists('fs_get_punto_expedicion_activo')) {
    function fs_get_punto_expedicion_activo($sucursal_id_sistema)
    {
        $ci = fs_ci();
        $hoy = date('Y-m-d');

        $query = $ci->db->select('pe.*, s.codigo_establecimiento, t.id as timbrado_id, t.numero_timbrado')
            ->from('py_sifen_puntos_expedicion pe')
            ->join('py_sifen_sucursales s', 's.id = pe.sucursal_id', 'left')
            ->join('py_sifen_timbrados_puntos tp', 'tp.punto_expedicion_id = pe.id')
            ->join('py_sifen_timbrados t', 't.id = tp.timbrado_id')
            ->where('pe.sucursal_id', $sucursal_id_sistema)
            ->where('pe.activo', 1)
            ->where('t.activo', 1)
            ->where('t.fecha_inicio <=', $hoy)
            ->where('t.fecha_fin >=', $hoy)
            ->limit(1)
            ->get();

        return $query->row();
    }
}

// application/helpers/factura_send_helper.php

/**
 * Obtiene los detalles completos de la factura electrónica a partir de un ID de venta.
 * Realiza los JOINs necesarios y formatea el número de factura.
 *
 * @param int $sale_id El ID de la venta desde 'tbl_sales'.
 * @return object|null Un objeto con todos los datos de la factura o null si no se encuentra.
 */

if (!function_exists('fs_get_factura_details_by_sale_id')) {
    function fs_get_factura_details_by_sale_id($sale_id)
    {
        $ci = fs_ci();

        if (!$sale_id) {
            return null;
        }

        // Consulta corregida SIN comentarios SQL dentro del string
        $ci->db->select("
            CONCAT_WS(
                '-',
                LPAD(suc.codigo_establecimiento, 3, '0'),
                LPAD(pe.codigo_punto, 3, '0'),
                LPAD(fe.numero, 7, '0')
            ) as numero_factura_formateado,
            
            t.numero_timbrado,
            t.fecha_fin as timbrado_vencimiento,
            t.fecha_inicio as timbrado_vigente,
            
            fe.id as factura_py_id,
            fe.numero as numero_correlativo,
            fe.cdc,
            fe.qr,
            fe.fecha_emision,
            fe.iva5,
            fe.iva10,
            fe.moneda,
            fe.fecha as fecha_emision,
            
            s.id as venta_id,
            s.sale_date,
            s.total_payable
        ");

        $ci->db->from('tbl_sales s');
        $ci->db->join('py_facturas_electronicas fe', 's.py_factura_id = fe.id', 'inner');
        $ci->db->join('py_sifen_sucursales suc', 'fe.sucursal_id = suc.id', 'left');
        $ci->db->join('py_sifen_puntos_expedicion pe', 'fe.punto_expedicion_id = pe.id', 'left');
        $ci->db->join('py_sifen_timbrados t', 'fe.timbrado_id = t.id', 'left');
        
        $ci->db->where('s.id', $sale_id);
        
        $query = $ci->db->get();

        if (!$query) {
            // Manejo de error si la consulta falla por otra razón
            log_message('error', 'Error en la consulta fs_get_factura_details_by_sale_id: ' . $ci->db->error()['message']);
            return null;
        }

        if ($query->num_rows() > 0) {
            return $query->row();
        }

        return null;
    }
}

/**
 * Obtiene los detalles completos de la factura electrónica a partir de un SALE_NO dado.
 * Realiza los JOINs necesarios y formatea el número de factura.
 *
 * @param int $sale_no El SALE_NO de la venta desde 'tbl_kitchen_sales'.
 * @return object|null Un objeto con todos los datos de la factura o null si no se encuentra.
 */

if (!function_exists('fs_get_factura_details_by_sale_no')) {
    function fs_get_factura_details_by_sale_no($sale_no)
    {
        $ci = fs_ci();

        if (!$sale_no) {
            return null;
        }

        // Consulta corregida SIN comentarios SQL dentro del string
        $ci->db->select("
            CONCAT_WS(
                '-',
                LPAD(suc.codigo_establecimiento, 3, '0'),
                LPAD(pe.codigo_punto, 3, '0'),
                LPAD(fe.numero, 7, '0')
            ) as numero_factura_formateado,
            
            t.numero_timbrado,
            t.fecha_fin as timbrado_vencimiento,
            t.fecha_inicio as timbrado_vigente,
            
            fe.id as factura_py_id,
            fe.numero as numero_correlativo,
            fe.cdc,
            fe.qr,
            fe.fecha_emision,
            fe.iva5,
            fe.iva10,
            fe.moneda,
            fe.fecha as fecha_emision,
            
            s.id as venta_id,
            s.sale_date,
            s.total_payable
        ");

        $ci->db->from('tbl_kitchen_sales s');
        $ci->db->join('py_facturas_electronicas fe', 's.py_factura_id = fe.id', 'inner');
        $ci->db->join('py_sifen_sucursales suc', 'fe.sucursal_id = suc.id', 'left');
        $ci->db->join('py_sifen_puntos_expedicion pe', 'fe.punto_expedicion_id = pe.id', 'left');
        $ci->db->join('py_sifen_timbrados t', 'fe.timbrado_id = t.id', 'left');
        
        $ci->db->where('s.sale_no', $sale_no);
        
        $query = $ci->db->get();

        if (!$query) {
            // Manejo de error si la consulta falla por otra razón
            log_message('error', 'Error en la consulta fs_get_factura_details_by_sale_no: ' . $ci->db->error()['message']);
            return null;
        }

        if ($query->num_rows() > 0) {
            return $query->row();
        }

        return null;
    }
}

/**
 * Formatea la fecha al formato requerido por SIFEN (yyyy-MM-ddTHH:mm:ss)
 */
if (!function_exists('fs_format_date_for_sifen')) {
    function fs_format_date_for_sifen($date) {
        // Si la fecha ya tiene el formato correcto, la devolvemos
        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/', $date)) {
            return $date;
        }
        
        // Si es un string de fecha en otro formato, convertirlo
        if (is_string($date)) {
            $timestamp = strtotime($date);
            if ($timestamp === false) {
                // Si no se puede convertir, devolver la fecha actual formateada
                $timestamp = time();
            }
        } else if (is_numeric($date)) {
            // Si es un timestamp
            $timestamp = $date;
        } else {
            // En cualquier otro caso, usamos la fecha actual
            $timestamp = time();
        }
        
        return date('Y-m-d\TH:i:s', $timestamp);
    }
}

/**
 * Prepara los datos del cliente para la API, normalizando los valores
 */
if (!function_exists('fs_prepare_cliente_data')) {
    function fs_prepare_cliente_data($cliente_data) {
        $cliente = [];
        
        // Valores por defecto
        $defaults = [
            'es_contribuyente' => false,
            'ruc' => '',
            'nombre' => 'CONSUMIDOR FINAL',
            'nombre_fantasia' => '',
            'direccion' => '',
            'es_proveedor_estado' => false,
            'tipo_contribuyente' => 1, // 1 = Persona Física, 2 = Persona Jurídica
            'tipo_documento' => 5, // 5 = Sin Documento
            'departamento_id' => fs_default_departamento(),
            'distrito_id' => fs_default_distrito(),
            'ciudad_id' => fs_default_ciudad(),
            'pais_codigo' => 'PRY',
            'numero_casa' => '0',
            'email' => '',
            'id_sistema' => '0'
        ];
        
        // Combinar valores predeterminados con los proporcionados
        foreach ($defaults as $key => $value) {
            $cliente[$key] = $cliente_data[$key] ?? $value;
        }
        
        // Validar RUC para contribuyentes
        if ($cliente['es_contribuyente'] && empty($cliente['ruc'])) {
            $cliente['es_contribuyente'] = false;
        }
        
        // Asegurar que el nombre del cliente no esté vacío
        if (empty($cliente['nombre'])) {
            $cliente['nombre'] = $defaults['nombre'];
        }
        
        return $cliente;
    }
}

/**
 * Prepara los datos del usuario para la API, normalizando los valores
 */
if (!function_exists('fs_prepare_usuario_data')) {
    function fs_prepare_usuario_data($usuario_data) {
        $usuario = [];
        
        // Valores por defecto
        $defaults = [
            'id_sistema' => '0',
            'nombre' => 'Administrador',
            'cargo' => 'Vendedor',
            'documento' => '0'
        ];
        
        // Combinar valores predeterminados con los proporcionados
        foreach ($defaults as $key => $value) {
            $usuario[$key] = $usuario_data[$key] ?? $value;
        }
        
        return $usuario;
    }
}

/**
 * Prepara los datos de los items para la API, normalizando los valores
 */
if (!function_exists('fs_prepare_items_data')) {
    function fs_prepare_items_data($items_data) {
        $items = [];
        
        foreach ($items_data as $item) {
            $normalized_item = [
                'codigo' => $item['codigo'] ?? '',
                'descripcion' => $item['descripcion'] ?? 'Producto',
                'cantidad' => floatval($item['cantidad'] ?? 1),
                'precio_unitario' => floatval($item['precio_unitario'] ?? 0),
                'iva' => intval($item['iva'] ?? 10),
                'ivaBase' => 100, // Por defecto 100%
                'ivaTipo' => ($item['iva'] > 0) ? 1 : 3, // 1 = Gravado, 3 = Exento
            ];
            
            // Validar valores
            if ($normalized_item['cantidad'] <= 0) {
                $normalized_item['cantidad'] = 1;
            }
            
            if ($normalized_item['precio_unitario'] < 0) {
                $normalized_item['precio_unitario'] = 0;
            }
            
            // Si el IVA es 0, entonces es exento y el ivaBase debe ser 0
            if ($normalized_item['iva'] == 0) {
                $normalized_item['ivaBase'] = 0;
            }
            
            $items[] = $normalized_item;
        }
        
        return $items;
    }
}

/**
 * Maneja la creación de una Factura Electrónica (tipo 1)
 */
if (!function_exists('fs_create_factura_electronica')) {
    function fs_create_factura_electronica($data) {
        return fs_create_and_send_invoice($data);
    }
}

/**
 * Maneja la creación de una Autofactura Electrónica (tipo 4)
 */
if (!function_exists('fs_create_autofactura_electronica')) {
    function fs_create_autofactura_electronica($data) {
        $data['tipo_documento'] = 4;
        
        // Aquí irían validaciones específicas para autofacturas
        if (empty($data['autoFactura'])) {
            return [
                'status' => 'error',
                'message' => 'Los datos de autoFactura son requeridos para este tipo de documento'
            ];
        }
        
        return fs_create_and_send_invoice($data);
    }
}

/**
 * Maneja la creación de una Nota de Crédito Electrónica (tipo 5)
 */
if (!function_exists('fs_create_nota_credito_electronica')) {
    function fs_create_nota_credito_electronica($data) {
        $data['tipo_documento'] = 5;
        
        // Aquí irían validaciones específicas para notas de crédito
        if (empty($data['documentoAsociado']['cdc'])) {
            return [
                'status' => 'error',
                'message' => 'El CDC del documento asociado es requerido para notas de crédito'
            ];
        }
        
        return fs_create_and_send_invoice($data);
    }
}

/**
 * Maneja la creación de una Nota de Débito Electrónica (tipo 6)
 */
if (!function_exists('fs_create_nota_debito_electronica')) {
    function fs_create_nota_debito_electronica($data) {
        $data['tipo_documento'] = 6;
        
        // Aquí irían validaciones específicas para notas de débito
        if (empty($data['documentoAsociado']['cdc'])) {
            return [
                'status' => 'error',
                'message' => 'El CDC del documento asociado es requerido para notas de débito'
            ];
        }
        
        return fs_create_and_send_invoice($data);
    }
}

/**
 * Maneja la creación de una Nota de Remisión Electrónica (tipo 7)
 */
if (!function_exists('fs_create_nota_remision_electronica')) {
    function fs_create_nota_remision_electronica($data) {
        $data['tipo_documento'] = 7;
        
        // Aquí irían validaciones específicas para notas de remisión
        if (empty($data['remision'])) {
            return [
                'status' => 'error',
                'message' => 'Los datos de remisión son requeridos para este tipo de documento'
            ];
        }
        
        if (empty($data['transporte'])) {
            return [
                'status' => 'error',
                'message' => 'Los datos de transporte son requeridos para notas de remisión'
            ];
        }
        
        return fs_create_and_send_invoice($data);
    }
}

/**
 * Aplica un descuento global a los items
 */
if (!function_exists('fs_apply_global_discount')) {
    function fs_apply_global_discount($items, $discount_amount) {
        if ($discount_amount <= 0) {
            return $items;
        }
        
        $total_items = array_sum(array_map(function($item) {
            return $item['cantidad'] * $item['precio_unitario'];
        }, $items));
        
        if ($total_items == 0) {
            return $items;
        }
        
        $discount_ratio = $discount_amount / $total_items;
        
        foreach ($items as &$item) {
            $item_total = $item['cantidad'] * $item['precio_unitario'];
            $item_discount = round($item_total * $discount_ratio, 2);
            $item['descuento'] = $item_discount / $item['cantidad'];
        }
        
        return $items;
    }
}

/**
 * Calcula los totales de la factura
 */
if (!function_exists('fs_calculate_invoice_totals')) {
    function fs_calculate_invoice_totals($items) {
        $total = 0;
        $total_iva_5 = 0;
        $total_iva_10 = 0;
        $total_exento = 0;
        
        foreach ($items as $item) {
            $subtotal = $item['cantidad'] * $item['precio_unitario'];
            $descuento = isset($item['descuento']) ? $item['descuento'] * $item['cantidad'] : 0;
            $neto = $subtotal - $descuento;
            
            $total += $neto;
            
            // Calcular IVA según el tipo y tasa
            if ($item['ivaTipo'] == 1 || $item['ivaTipo'] == 4) { // Gravado o parcialmente gravado
                $base_imponible = $neto * ($item['ivaBase'] / 100);
                
                if ($item['iva'] == 5) {
                    $total_iva_5 += $base_imponible * 0.05;
                } elseif ($item['iva'] == 10) {
                    $total_iva_10 += $base_imponible * 0.10;
                }
            } else {
                $total_exento += $neto;
            }
        }
        
        return [
            'total' => $total,
            'total_iva_5' => $total_iva_5,
            'total_iva_10' => $total_iva_10,
            'total_exento' => $total_exento,
            'total_iva' => $total_iva_5 + $total_iva_10
        ];
    }
}

/**
 * Procesa los datos de condición de crédito
 */
if (!function_exists('fs_process_credit_condition')) {
    function fs_process_credit_condition($condicion_id, $condicion_credito) {
        $ci = fs_ci();
        
        $credito_db = [
            'condicion_id' => $condicion_id,
            'tipo' => $condicion_credito['tipo'],
            'plazo' => $condicion_credito['plazo'] ?? null,
            'cuotas' => $condicion_credito['cuotas'] ?? null,
            'monto_entrega' => $condicion_credito['montoEntrega'] ?? null
        ];
        
        $ci->db->insert('py_factura_condicion_credito', $credito_db);
        $credito_id = $ci->db->insert_id();
        
        // Procesar cuotas si existen
        if (isset($condicion_credito['infoCuotas']) && is_array($condicion_credito['infoCuotas'])) {
            foreach ($condicion_credito['infoCuotas'] as $cuota) {
                $cuota_db = [
                    'credito_id' => $credito_id,
                    'moneda' => $cuota['moneda'],
                    'monto' => $cuota['monto'],
                    'vencimiento' => $cuota['vencimiento']
                ];
                $ci->db->insert('py_factura_condicion_credito_cuotas', $cuota_db);
            }
        }
        
        return $credito_id;
    }
}

/**
 * Verifica si un documento electrónico ya existe
 */
if (!function_exists('fs_document_exists')) {
    function fs_document_exists($tipo_documento, $establecimiento, $punto, $numero) {
        $ci = fs_ci();
        
        $exists = $ci->db->where([
            'tipo_documento' => $tipo_documento,
            'sucursal_id' => $establecimiento,
            'punto_expedicion_id' => $punto,
            'numero' => $numero
        ])->get('py_facturas_electronicas')->row();
        
        return $exists !== null;
    }
}

/**
 * Mapea el estado de la API (string) a nuestro ID de estado local.
 * 0 = Generado, 1 = Enviado en Lote, 2 = Aprobado, 3 = Rechazado, 4 = Error/Local
 */
if (!function_exists('fs_map_estado_api_to_local')) {
    function fs_map_estado_api_to_local($estado_api) {
        $estado_api = trim(strtolower((string)$estado_api));
        switch ($estado_api) {
            case 'aprobado':
                return 2;
            case 'aprobado con observación':
                return 3;
            case 'rechazado':
                return 4;
            case 'cancelado':
                return 99;
            case 'inexistente':
                return 98;
            case 'enviado':
            case 'pendiente':
            case 'procesando':
                return 1;
            case 'generado':
                return 0;
            default:
                // Desconocido: mantener como "enviado" para seguir monitoreando
                return 1;
        }
    }
}

/**
 * Consulta a FacturaSend el estado de los documentos con estado 0 o 1 y actualiza la BD.
 * - Filtra de py_facturas_electronicas los registros con estado IN (0,1) y CDC no vacío.
 * - Llama a Facturasend->consultar_estados_documentos en lotes.
 * - Actualiza estado y, si existen, columnas adicionales como situacion, respuesta_codigo y respuesta_mensaje.
 *
 * @param int $batch_size Cantidad de CDC por lote a consultar (por defecto 100)
 * @return array Resumen del proceso
 */
if (!function_exists('fs_facturasend_actualizar_estados_pendientes')) {
    function fs_facturasend_actualizar_estados_pendientes($batch_size = 50) {
        $ci = fs_ci();
        $ci->load->library('facturasend');

        // 1) Obtener CDCs pendientes (estado 0 o 1) y no vacíos
        $pendientes = $ci->db
            ->select('id, cdc, estado')
            ->from('py_facturas_electronicas')
            ->where_in('estado', [0, 1])
            ->where('cdc IS NOT NULL', null, false)
            ->where("TRIM(cdc) <> ''", null, false)
            ->get()
            ->result();

        if (empty($pendientes)) {
            return [
                'success' => true,
                'message' => 'No hay documentos con estado 0 o 1 y CDC válido para consultar.',
                'procesados' => 0,
                'actualizados' => 0,
                'errores' => 0,
                'detalles' => []
            ];
        }

        // Comprobar columnas opcionales una sola vez
        // Según la estructura de tabla proporcionada, estas columnas NO existen:
        // $has_situacion, $has_resp_cod, $has_resp_msg, $has_fecha_est
        // Solo trabajaremos con las columnas que existen: estado, fecha_modificacion

        $procesados = 0;
        $actualizados = 0;
        $errores = 0;
        $detalles = [];

        // 2) Preparar lotes
        $total = count($pendientes);
        for ($i = 0; $i < $total; $i += $batch_size) {
            $slice = array_slice($pendientes, $i, $batch_size);
            $cdcList = [];
            foreach ($slice as $row) {
                $cdcList[] = ['cdc' => $row->cdc];
            }

            // 3) Llamar a la API
            $response = $ci->facturasend->consultar_estados_documentos($cdcList);

            $procesados += count($cdcList);

            if (!isset($response['status']) || $response['status'] != 200 || !($response['body']['success'] ?? false)) {
                $errores++;
                $detalles[] = [
                    'lote' => [$i + 1, min($i + count($cdcList), $total)],
                    'error' => 'Respuesta inválida de la API',
                    'response' => $response
                ];
                
                // Registrar error en auditoría
                $audit_data = [
                    'factura_id' => null, // No está asociado a una factura específica
                    'fecha_modificacion' => date('Y-m-d H:i:s'),
                    'usuario_id' => fs_get_or_create_py_usuario(),
                    'tipo_accion' => 'CONSULTA_CDC_ERROR',
                    'json_backup' => json_encode([
                        'lote' => [$i + 1, min($i + count($cdcList), $total)],
                        'cdcs' => array_column($cdcList, 'cdc'),
                        'response' => $response
                    ])
                ];
                $ci->db->insert('py_facturas_auditoria', $audit_data);
                
                continue;
            }

            $deList = $response['body']['deList'] ?? $response['body']['result']['deList'] ?? [];
            if (!is_array($deList)) {
                $deList = [];
            }

            // 4) Mapear por CDC para updates rápidos
            $por_cdc = [];
            foreach ($deList as $de) {
                if (!empty($de['cdc'])) {
                    $por_cdc[$de['cdc']] = $de;
                }
            }

            // 5) Actualizar cada registro del slice si hay respuesta para su CDC
            foreach ($slice as $row) {
                if (!isset($por_cdc[$row->cdc])) {
                    // No vino en la respuesta, registrar y continuar
                    $detalles[] = [
                        'id' => $row->id,
                        'cdc' => $row->cdc,
                        'warning' => 'CDC no retornado por la API en este lote'
                    ];
                    
                    // Registrar warning en auditoría
                    $audit_data = [
                        'factura_id' => $row->id,
                        'fecha_modificacion' => date('Y-m-d H:i:s'),
                        'usuario_id' => fs_get_or_create_py_usuario(),
                        'tipo_accion' => 'CONSULTA_CDC_WARNING',
                        'json_backup' => json_encode([
                            'cdc' => $row->cdc,
                            'warning' => 'CDC no retornado por la API en este lote',
                            'response_deList' => $deList
                        ])
                    ];
                    $ci->db->insert('py_facturas_auditoria', $audit_data);
                    
                    continue;
                }

                $de = $por_cdc[$row->cdc];
                
                // El estado viene en el campo 'situacion' como número
                $situacion_api = $de['situacion'] ?? null;
                
                // Mapear la situación API a nuestro estado local
                $estado_local = $situacion_api;
                
                // Si la situación API no es válida, mantener el estado actual
                if ($situacion_api === null || !is_numeric($situacion_api)) {
                    $detalles[] = [
                        'id' => $row->id,
                        'cdc' => $row->cdc,
                        'warning' => 'Campo situacion no válido en respuesta API',
                        'situacion_recibida' => $situacion_api
                    ];
                    
                    // Registrar warning en auditoría
                    $audit_data = [
                        'factura_id' => $row->id,
                        'fecha_modificacion' => date('Y-m-d H:i:s'),
                        'usuario_id' => $ci->session->userdata('user_id') ?? 0,
                        'tipo_accion' => 'CONSULTA_CDC_WARNING',
                        'json_backup' => json_encode([
                            'cdc' => $row->cdc,
                            'warning' => 'Campo situacion no válido en respuesta API',
                            'situacion_recibida' => $situacion_api,
                            'response_completa' => $de
                        ])
                    ];
                    $ci->db->insert('py_facturas_auditoria', $audit_data);
                    
                    continue;
                }

                // Preparar datos para actualizar (solo columnas que existen en la tabla)
                $update = [
                    'estado' => $estado_local,
                    'fecha_modificacion' => date('Y-m-d H:i:s')
                ];

                // Realizar la actualización
                $ci->db->where('cdc', $row->cdc)->update('py_facturas_electronicas', $update);
                
                $fue_actualizado = false;
                if ($ci->db->affected_rows() > 0) {
                    $actualizados++;
                    $fue_actualizado = true;
                }

                // Registrar en auditoría
                $audit_data = [
                    'factura_id' => $row->id,
                    'fecha_modificacion' => date('Y-m-d H:i:s'),
                    'usuario_id' => fs_get_or_create_py_usuario(),
                    'tipo_accion' => 'CONSULTA_CDC_EXITO',
                    'json_backup' => json_encode([
                        'cdc' => $row->cdc,
                        'situacion_api' => $situacion_api,
                        'estado_anterior' => $row->estado,
                        'estado_nuevo' => $estado_local,
                        'actualizado' => $fue_actualizado,
                        'response_completa' => $de
                    ])
                ];
                $ci->db->insert('py_facturas_auditoria', $audit_data);

                $detalles[] = [
                    'id' => $row->id,
                    'cdc' => $row->cdc,
                    'situacion_api' => $situacion_api,
                    'estado_anterior' => $row->estado,
                    'estado_nuevo' => $estado_local,
                    'actualizado' => $fue_actualizado
                ];
            }
        }

        return [
            'success' => true,
            'message' => 'Sincronización finalizada',
            'procesados' => $procesados,
            'actualizados' => $actualizados,
            'errores' => $errores,
            'detalles' => $detalles
        ];
    }
}