<section class="main-content-wrapper">
    <style>
        .correlativo-card {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            margin: 2px;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .correlativo-card:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .estado-borrador { background-color: #6c757d; color: white; }
        .estado-generado { background-color: #007bff; color: white; }
        .estado-enviado { background-color: #ffc107; color: black; }
        .estado-aprobado { background-color: #28a745; color: white; }
        .estado-aprobado-obs { background-color: #20c997; color: white; }
        .estado-rechazado { background-color: #dc3545; color: white; }
        .estado-inexistente { background-color: #e83e8c; color: white; }
        .estado-cancelado { background-color: #6f42c1; color: white; }
        .correlativo-card-faltante { background-color: #dc3545; color: white; }
        .month-nav {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        .month-nav button {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            margin: 0 10px;
        }
        .resumen-box {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>

    <section class="content-header" style="height: 80px;">
        <h3 class="top-left-header">Control de Correlativos</h3>
        <a href="<?php echo base_url('Facturacion_py/listado'); ?>" class="btn btn-secondary float-end">
            <i data-feather="arrow-left"></i> Volver al Listado
        </a>
    </section>

    <!-- Filtros -->
    <div class="box-wrapper">
        <div class="table-box">
            <h4><i data-feather="filter"></i> Filtros</h4>
            <?php echo form_open(base_url('Facturacion_py/correlativos'), ['method' => 'GET']); ?>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="punto_id" class="form-label">Punto de Expedición</label>
                    <select name="punto_id" id="punto_id" class="form-select">
                        <option value="">Seleccionar Punto</option>
                        <?php foreach ($puntos_expedicion as $punto): 
                            $sucursal_nombre = isset($sucursales_index[$punto->sucursal_id]) ? $sucursales_index[$punto->sucursal_id]->nombre : 'Sucursal ' . $punto->sucursal_id;
                        ?>
                            <option value="<?php echo $punto->id; ?>" <?php echo ($punto->id == $this->input->get('punto_id')) ? 'selected' : ''; ?>>
                                <?php echo $sucursal_nombre . ' - ' . str_pad($punto->codigo_punto, 3, '0', STR_PAD_LEFT) . ' (' . $punto->nombre . ')'; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="mes" class="form-label">Mes</label>
                    <select name="mes" id="mes" class="form-select">
                        <?php 
                        $meses = [
                            '01' => 'Enero',
                            '02' => 'Febrero', 
                            '03' => 'Marzo',
                            '04' => 'Abril',
                            '05' => 'Mayo',
                            '06' => 'Junio',
                            '07' => 'Julio',
                            '08' => 'Agosto',
                            '09' => 'Septiembre',
                            '10' => 'Octubre',
                            '11' => 'Noviembre',
                            '12' => 'Diciembre'
                        ];
                        for ($i = 1; $i <= 12; $i++): 
                            $mes_num = str_pad($i, 2, '0', STR_PAD_LEFT);
                        ?>
                            <option value="<?php echo $mes_num; ?>" <?php echo ($mes_num == $mes) ? 'selected' : ''; ?>>
                                <?php echo $meses[$mes_num]; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="anio" class="form-label">Año</label>
                    <select name="anio" id="anio" class="form-select">
                        <?php for ($i = date('Y') - 2; $i <= date('Y') + 1; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo ($i == $anio) ? 'selected' : ''; ?>>
                                <?php echo $i; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="tipo_documento" class="form-label">Tipo de Comprobante</label>
                    <select name="tipo_documento" id="tipo_documento" class="form-select">
                        <option value="1" <?php echo ($tipo_documento == '1') ? 'selected' : ''; ?>>Factura Electrónica</option>
                        <option value="4" <?php echo ($tipo_documento == '4') ? 'selected' : ''; ?>>Autofactura</option>
                        <option value="5" <?php echo ($tipo_documento == '5') ? 'selected' : ''; ?>>Nota de Crédito</option>
                        <option value="6" <?php echo ($tipo_documento == '6') ? 'selected' : ''; ?>>Nota de Débito</option>
                        <option value="7" <?php echo ($tipo_documento == '7') ? 'selected' : ''; ?>>Nota de Remisión</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary form-control">
                        <i data-feather="search"></i> Filtrar
                    </button>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>

    <?php if (isset($punto_seleccionado)): ?>
    <!-- Resumen -->
    <div class="resumen-box">
        <div class="row">
            <div class="col-md-4">
                <strong>Punto Seleccionado:</strong> <?php echo $punto_seleccionado ? $punto_seleccionado->sucursal_nombre . ' - ' . str_pad($punto_seleccionado->codigo_punto, 3, '0', STR_PAD_LEFT) : 'N/A'; ?>
            </div>
            <div class="col-md-4">
                <strong>Período:</strong> <?php echo $mes_actual . ' ' . $anio; ?>
            </div>
            <div class="col-md-4">
                <strong>Correlativos:</strong>
                <?php if ($resumen && $resumen->total_facturas > 0): ?>
                    Del <?php echo $resumen->min_numero; ?> al <?php echo $resumen->max_numero; ?> (<?php echo $resumen->total_facturas; ?> facturas)
                <?php else: ?>
                    No hay facturas en este período
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Navegación de meses -->
    <div class="month-nav">
        <a href="<?php echo base_url('Facturacion_py/correlativos?punto_id=' . $this->input->get('punto_id') . '&mes=' . $mes_anterior . '&anio=' . $anio_anterior . '&tipo_documento=' . $tipo_documento); ?>" class="btn btn-outline-secondary">
            <i data-feather="chevron-left"></i>
        </a>
        <?php 
        $meses = [
            '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
            '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
            '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
        ];
        $mes_actual = isset($meses[$mes]) ? $meses[$mes] : $mes;
        ?>
        <h4><?php echo $mes_actual . ' ' . $anio; ?></h4>
        <a href="<?php echo base_url('Facturacion_py/correlativos?punto_id=' . $this->input->get('punto_id') . '&mes=' . $mes_siguiente . '&anio=' . $anio_siguiente . '&tipo_documento=' . $tipo_documento); ?>" class="btn btn-outline-secondary">
            <i data-feather="chevron-right"></i>
        </a>
    </div>

    <!-- Grid de correlativos -->
    <div class="box-wrapper">
        <div class="table-box">
            <div class="d-flex flex-wrap justify-content-start">
                <?php
                $max_numero = $resumen ? $resumen->max_numero : 0;
                $min_numero = $resumen ? $resumen->min_numero : 0;

                // Crear array de prioridades de estados (menor número = mayor prioridad)
                $estado_prioridades = [
                    2 => 1,  // Aprobado - prioridad más alta
                    3 => 2,  // Aprobado con observaciones
                    1 => 3,  // Enviado
                    0 => 4,  // Generado
                    4 => 5,  // Rechazado
                    98 => 6, // Inexistente
                    99 => 7, // Cancelado
                    -1 => 8  // Borrador - prioridad más baja
                ];

                // Crear array de facturas indexado por numero
                // Si hay múltiples facturas con el mismo número, seleccionar la de mejor estado según jerarquía
                $facturas_index = [];
                foreach ($facturas as $factura) {
                    $num = $factura->numero;
                    $prioridad_actual = isset($estado_prioridades[$factura->estado]) ? $estado_prioridades[$factura->estado] : 99;

                    if (!isset($facturas_index[$num])) {
                        $facturas_index[$num] = $factura;
                    } else {
                        $prioridad_existente = isset($estado_prioridades[$facturas_index[$num]->estado]) ? $estado_prioridades[$facturas_index[$num]->estado] : 99;
                        // Menor prioridad número = mejor estado
                        if ($prioridad_actual < $prioridad_existente) {
                            $facturas_index[$num] = $factura;
                        }
                    }
                }

                // Mostrar cuadritos desde el menor hasta el mayor
                for ($num = $min_numero; $num <= $max_numero; $num++) {
                    $factura = isset($facturas_index[$num]) ? $facturas_index[$num] : null;
                    $estado_class = 'correlativo-card-faltante'; // Por defecto faltante (rojo)

                    if ($factura) {
                        switch ($factura->estado) {
                            case -1: $estado_class = 'estado-borrador'; break;
                            case 0: $estado_class = 'estado-generado'; break;
                            case 1: $estado_class = 'estado-enviado'; break;
                            case 2: $estado_class = 'estado-aprobado'; break;
                            case 3: $estado_class = 'estado-aprobado-obs'; break;
                            case 4: $estado_class = 'estado-rechazado'; break;
                            case 98: $estado_class = 'estado-inexistente'; break;
                            case 99: $estado_class = 'estado-cancelado'; break;
                        }
                    }

                    $data_attrs = $factura ? 'data-factura-id="' . $factura->id . '" data-numero="' . $factura->numero . '"' : '';
                    echo '<div class="correlativo-card ' . $estado_class . '" ' . $data_attrs . ' onclick="' . ($factura ? 'abrirModalFactura(' . $factura->id . ')' : '') . '">' . $num . '</div>';
                }
                ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</section>

<!-- Modal de Detalles de Factura -->
<div class="modal fade" id="facturaModal" tabindex="-1" aria-labelledby="facturaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="facturaModalLabel">Detalles de Factura</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="facturaModalBody">
                <!-- Contenido cargado por AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
function abrirModalFactura(facturaId) {
    $('#facturaModal').modal('show');
    $('#facturaModalBody').html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando...</div>');

    $.ajax({
        url: '<?php echo base_url("Facturacion_py/ajax_get_factura_detalle"); ?>',
        type: 'POST',
        data: { factura_id: facturaId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                mostrarDetallesFactura(response.data);
            } else {
                $('#facturaModalBody').html('<div class="alert alert-danger">' + response.message + '</div>');
            }
        },
        error: function() {
            $('#facturaModalBody').html('<div class="alert alert-danger">Error al cargar los detalles</div>');
        }
    });
}

function mostrarDetallesFactura(data) {
    var html = '';
    
    // Mapeo de tipos de documentos
    var tiposDocumento = {
        '1': 'Factura Electrónica',
        '4': 'Autofactura',
        '5': 'Nota de Crédito',
        '6': 'Nota de Débito',
        '7': 'Nota de Remisión'
    };
    var tipoDocTexto = tiposDocumento[data.factura.tipo_documento] || 'Tipo ' + data.factura.tipo_documento;

    // Información básica
    html += '<div class="row mb-3">';
    html += '<div class="col-md-6">';
    html += '<h5>Datos de Factura</h5>';
    html += '<p><strong>Tipo:</strong> ' + tipoDocTexto + '</p>';
    html += '<p><strong>Número:</strong> ' + data.factura.numero_formateado + '</p>';
    html += '<p><strong>Fecha:</strong> ' + data.factura.fecha + '</p>';
    html += '<p><strong>Estado:</strong> <span class="badge bg-primary">' + data.factura.estado_descripcion + '</span></p>';
    if (data.factura.cdc) {
        html += '<p><strong>CDC:</strong> <code>' + data.factura.cdc + '</code></p>';
    }
    html += '</div>';

    // Cliente
    html += '<div class="col-md-6">';
    html += '<h5>Datos del Cliente</h5>';
    if (data.cliente) {
        html += '<p><strong>Razón Social:</strong> ' + data.cliente.razon_social + '</p>';
        html += '<p><strong>RUC:</strong> ' + data.cliente.ruc + '</p>';
    }
    html += '</div>';
    html += '</div>';

    // Items
    if (data.items && data.items.length > 0) {
        html += '<h5>Items</h5>';
        html += '<div class="table-responsive">';
        html += '<table class="table table-sm">';
        html += '<thead><tr><th>Código</th><th>Descripción</th><th>Cantidad</th><th>Precio</th><th>Total</th></tr></thead>';
        html += '<tbody>';
        data.items.forEach(function(item) {
            html += '<tr>';
            html += '<td>' + (item.codigo || '') + '</td>';
            html += '<td>' + (item.descripcion || '') + '</td>';
            html += '<td>' + (item.cantidad || 0) + '</td>';
            html += '<td>' + (item.precio_unitario || 0) + '</td>';
            html += '<td>' + ((item.cantidad || 0) * (item.precio_unitario || 0)) + '</td>';
            html += '</tr>';
        });
        html += '</tbody></table>';
        html += '</div>';
    }

    // Si es rechazada, mostrar logs
    if (data.factura.estado == 4 && data.logs && data.logs.length > 0) {
        html += '<h5>Motivo de Rechazo</h5>';
        html += '<div class="alert alert-danger">';
        data.logs.forEach(function(log) {
            if (log.json_response) {
                var jsonResp = JSON.parse(log.json_response);
                if (jsonResp.body && jsonResp.body.error) {
                    html += '<p>' + jsonResp.body.error + '</p>';
                }
            }
        });
        html += '</div>';
    }

    // Botones de acción
    html += '<div class="mt-3">';
    html += '<a href="<?php echo base_url("Facturacion_py/formulario/"); ?>' + data.factura.id + '" class="btn btn-warning me-2" target="_blank">';
    html += '<i data-feather="edit"></i> Editar</a>';
    html += '<button class="btn btn-info me-2" onclick="verFactura(' + data.factura.id + ')">';
    html += '<i data-feather="eye"></i> Ver</button>';
    if (data.factura.cdc) {
        html += '<button class="btn btn-success me-2" id="btn-pdf-ticket-' + data.factura.id + '" onclick="descargarPDFFactura(\'' + data.factura.cdc + '\', \'' + data.factura.numero_formateado + '\', \'btn-pdf-ticket-' + data.factura.id + '\', \'ticket\')">'; 
        html += '<i data-feather="download"></i> PDF Ticket</button>';
        html += '<button class="btn btn-success me-2" id="btn-pdf-a4-' + data.factura.id + '" onclick="descargarPDFFactura(\'' + data.factura.cdc + '\', \'' + data.factura.numero_formateado + '\', \'btn-pdf-a4-' + data.factura.id + '\', \'a4\')">'; 
        html += '<i data-feather="file-text"></i> PDF A4</button>';
    }
    html += '<button class="btn btn-primary" onclick="copiarFactura(' + data.factura.id + ')">';
    html += '<i data-feather="copy"></i> Copiar</button>';
    html += '</div>';    $('#facturaModalBody').html(html);

    // Reactivar iconos
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}

function verFactura(id) {
    window.open('<?php echo base_url("Facturacion_py/formulario/"); ?>' + id, '_blank');
}

function copiarFactura(id) {
    window.open('<?php echo base_url("Facturacion_py/formulario/"); ?>' + id + '?action=duplicate', '_blank');
}

function descargarPDFFactura(cdc, facturaNumero, buttonId, formato) {
    // Validar CDC
    if (!cdc || cdc.trim() === '') {
        alert('CDC no disponible para esta factura');
        return;
    }

    // Obtener el botón
    const button = document.getElementById(buttonId);
    if (!button) {
        alert('Error interno: botón no encontrado');
        return;
    }

    // Deshabilitar botón y mostrar estado de carga
    button.disabled = true;
    const originalHtml = button.innerHTML;
    button.innerHTML = '<i data-feather="loader" class="spinner"></i> Descargando...';
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // Realizar petición AJAX
    $.ajax({
        url: '<?php echo base_url("Facturacion_py/descargar_pdf_factura"); ?>',
        type: 'POST',
        dataType: 'json',
        data: {
            cdc: cdc,
            format: formato || 'ticket'
        },
        success: function(response) {
            if (response.success) {
                // Descargar el PDF
                var linkSource = 'data:application/pdf;base64,' + response.pdf_base64;
                var downloadLink = document.createElement('a');
                downloadLink.href = linkSource;
                downloadLink.download = response.filename;
                downloadLink.click();
                
                // Mostrar mensaje de éxito
                if (typeof mostrarExito !== 'undefined') {
                    mostrarExito('PDF de factura ' + facturaNumero + ' descargado correctamente');
                } else {
                    alert('PDF de factura ' + facturaNumero + ' descargado correctamente');
                }
            } else {
                var errorMsg = 'Error al descargar PDF de factura ' + facturaNumero + ': ' + (response.message || 'Error desconocido');
                if (typeof mostrarError !== 'undefined') {
                    mostrarError(errorMsg);
                } else {
                    alert(errorMsg);
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX al descargar PDF:', error);
            var errorMessage = 'Error de conexión al servidor';
            
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.status === 404) {
                errorMessage = 'Servicio no encontrado';
            } else if (xhr.status === 500) {
                errorMessage = 'Error interno del servidor';
            }
            
            var fullError = 'Error al descargar PDF de factura ' + facturaNumero + ': ' + errorMessage;
            if (typeof mostrarError !== 'undefined') {
                mostrarError(fullError);
            } else {
                alert(fullError);
            }
        },
        complete: function() {
            // Rehabilitar botón
            if (button) {
                button.disabled = false;
                button.innerHTML = originalHtml;
                // Reactivar iconos de feather
                if (typeof feather !== 'undefined') {
                    feather.replace();
                }
            }
        }
    });
}

$(document).ready(function() {
    // Reactivar iconos al cargar
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});
</script></content>