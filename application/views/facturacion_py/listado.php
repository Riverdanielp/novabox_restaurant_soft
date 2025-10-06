<section class="main-content-wrapper">
    <style>
        .log-json-btn {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }
        .json-formatted-content pre {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }
        .badge-status {
            font-size: 0.75rem;
        }
        #logsModal .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }
        #cdcModal .modal-body {
            max-height: 80vh;
            overflow-y: auto;
        }
        .log-card {
            border-left: 4px solid #007bff;
        }
        .log-card.error {
            border-left-color: #dc3545;
        }
        .log-card.success {
            border-left-color: #28a745;
        }
        .log-card.warning {
            border-left-color: #ffc107;
        }
        #cdcInput {
            font-family: 'Courier New', monospace;
            letter-spacing: 1px;
        }
        .cdc-validation {
            font-size: 0.875rem;
        }
    </style>
    <section class="content-header" style="height: 80px;">
        <h3 class="top-left-header">Listado de Facturas Electrónicas</h3>
        <a href="<?php echo base_url('Facturacion_py/formulario'); ?>" class="btn btn-info float-end">
            <i data-feather="plus"></i> Crear Factura Manual
        </a>
        <button type="button" class="btn btn-warning float-end me-2" data-bs-toggle="modal" data-bs-target="#cdcModal">
            <i data-feather="search"></i> Consultar Estado CDC
        </button>
        <a href="<?php echo base_url('Facturacion_py/sync_estados_pendientes'); ?>" class="btn btn-primary float-end me-2">
            <i data-feather="refresh-cw"></i> Sincronizar Estados Pendientes
        </a>
    </section>

    <!-- Filtros -->
    <div class="box-wrapper">
        <div class="table-box">
                    
            <!-- Manejo de mensajes de error -->
            <?php if ($this->session->flashdata('error_custom')): ?>
                <div class="alert alert-danger  fade show" role="alert">
                    <i data-feather="alert-circle"></i>
                    <strong>Error:</strong> <?php echo escape_output($this->session->flashdata('error_custom')); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Manejo de mensajes de éxito -->
            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success  fade show" role="alert">
                    <i data-feather="check-circle"></i>
                    <strong>Éxito:</strong> <?php echo escape_output($this->session->flashdata('success')); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Manejo de mensajes de información -->
            <?php if ($this->session->flashdata('info')): ?>
                <div class="alert alert-info  fade show" role="alert">
                    <i data-feather="info"></i>
                    <strong>Información:</strong> <?php echo escape_output($this->session->flashdata('info')); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Manejo de mensajes de advertencia -->
            <?php if ($this->session->flashdata('warning')): ?>
                <div class="alert alert-warning  fade show" role="alert">
                    <i data-feather="alert-triangle"></i>
                    <strong>Advertencia:</strong> <?php echo escape_output($this->session->flashdata('warning')); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <h4><i data-feather="filter"></i> Filtros</h4>
            <?php echo form_open(base_url('Facturacion_py/listado'), ['method' => 'GET']); ?>
            <div class="row mb-3">
                <div class="col-md-2">
                    <label class="form-label">Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" class="form-control" value="<?php echo $filters['fecha_inicio']; ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Fecha Fin</label>
                    <input type="date" name="fecha_fin" class="form-control" value="<?php echo $filters['fecha_fin']; ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sucursal</label>
                    <select name="sucursal_id" class="form-control select2">
                        <option value="">Todas las Sucursales</option>
                        <?php foreach($sifen_sucursales as $suc): ?>
                        <option value="<?php echo $suc->id; ?>" <?php echo ($filters['sucursal_id'] == $suc->id) ? 'selected' : ''; ?>><?php echo $suc->nombre; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Usuario</label>
                    <select name="usuario_id" class="form-control select2">
                        <option value="">Todos los Usuarios</option>
                         <?php foreach($usuarios as $user): ?>
                        <option value="<?php echo $user->id; ?>" <?php echo ($filters['usuario_id'] == $user->id) ? 'selected' : ''; ?>><?php echo $user->nombre; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select name="estado_id" class="form-control select2">
                        <option value="">Todos los Estados</option>
                        <?php foreach($estados_documentos as $estado): ?>
                        <option value="<?php echo $estado->id; ?>" <?php echo ($filters['estado_id'] == $estado->id) ? 'selected' : ''; ?>><?php echo $estado->descripcion; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i data-feather="search"></i> Filtrar
                        </button>
                        <a href="<?php echo base_url('Facturacion_py/listado'); ?>" class="btn btn-secondary btn-sm">
                            <i data-feather="x"></i> Limpiar
                        </a>
                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>

    <!-- Tabla de resultados -->
    <div class="box-wrapper">
        <div class="table-box">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th># Factura</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Usuario</th>
                            <th>Estado</th>
                            <th>CDC</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($facturas as $factura): ?>
                        <tr>
                            <td><?php echo $factura->numero_formateado; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($factura->fecha)); ?></td>
                            <td><?php echo $factura->cliente_nombre; ?></td>
                            <td><?php echo $factura->usuario_nombre; ?></td>
                            <td>
                                <span class="badge 
                                    <?php 
                                    switch($factura->estado) {
                                        case -1: // Borrador
                                            echo 'bg-secondary';
                                            break;
                                        case 0: // Generado
                                            echo 'bg-info';
                                            break;
                                        case 1: // Enviado en Lote
                                            echo 'bg-primary';
                                            break;
                                        case 2: // Aprobado
                                            echo 'bg-success';
                                            break;
                                        case 3: // Aprobado con Observación
                                            echo 'bg-success';
                                            break;
                                        case 4: // Rechazado
                                            echo 'bg-danger';
                                            break;
                                        case 98: // Inexistente
                                            echo 'bg-dark';
                                            break;
                                        case 99: // Cancelado
                                            echo 'bg-warning text-dark';
                                            break;
                                        default:
                                            echo 'bg-warning';
                                    }
                                    ?>">
                                    <?php echo $factura->estado_descripcion; ?>
                                </span>
                            </td>
                            <td><?php echo $factura->cdc; ?></td>
                            <td class="text-center">
                                <a href="<?php echo base_url('Facturacion_py/formulario/'.$factura->id); ?>" class="btn btn-sm btn-info"><i data-feather="eye"></i> Ver</a>
                                <button type="button" class="btn btn-sm btn-secondary btn-logs" data-factura-id="<?php echo $factura->id; ?>" data-factura-numero="<?php echo $factura->numero_formateado; ?>">
                                    <i data-feather="terminal"></i> Logs
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="row">
                <div class="col-sm-12 col-md-7">
                    <div class="dataTables_paginate paging_simple_numbers">
                         <?php echo $links; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal de Logs -->
<div class="modal fade" id="logsModal" tabindex="-1" aria-labelledby="logsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logsModalLabel">
                    <i data-feather="terminal"></i> Logs de Factura
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="logs-loading" class="text-center" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando logs...</p>
                </div>
                <div id="logs-content">
                    <!-- Contenido de logs se cargará aquí -->
                </div>
                <div id="logs-error" class="alert alert-danger" style="display: none;">
                    <i data-feather="alert-circle"></i>
                    <span id="logs-error-message">Error al cargar los logs</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Consulta CDC -->
<div class="modal fade" id="cdcModal" tabindex="-1" aria-labelledby="cdcModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cdcModalLabel">
                    <i data-feather="search"></i> Consultar Estado por CDC
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="cdcForm">
                    <div class="mb-3">
                        <label for="cdcInput" class="form-label">Código de Control (CDC):</label>
                        <input type="text" class="form-control" id="cdcInput" name="cdc" placeholder="Ingrese el CDC de 44 dígitos" maxlength="44" required>
                        <div class="form-text cdc-validation">
                            <span id="cdc-counter">0/44</span> - El CDC debe tener exactamente 44 dígitos numéricos
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i data-feather="search"></i> Consultar Estado
                        </button>
                    </div>
                </form>
                
                <div id="cdc-loading" class="text-center mt-3" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Consultando...</span>
                    </div>
                    <p class="mt-2">Consultando estado del CDC...</p>
                </div>
                
                <div id="cdc-results" class="mt-3" style="display: none;">
                    <!-- Resultados de la consulta -->
                </div>
                
                <div id="cdc-error" class="alert alert-danger mt-3" style="display: none;">
                    <i data-feather="alert-circle"></i>
                    <span id="cdc-error-message">Error al consultar el CDC</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
function formatJsonForDisplay(jsonData, tipoAccion, isFromCdc = false) {
    var html = '<div class="row">';
    
    // Información general
    html += '<div class="col-md-6">';
    html += '<div class="card">';
    html += '<div class="card-header"><strong>Información General</strong></div>';
    html += '<div class="card-body">';
    
    if (!isFromCdc && tipoAccion) {
        html += '<p><strong>Tipo de Acción:</strong> <span class="badge bg-info">' + tipoAccion + '</span></p>';
    }
    
    if (jsonData.status) {
        var statusClass = jsonData.status === 200 ? 'bg-success' : 'bg-danger';
        html += '<p><strong>Status HTTP:</strong> <span class="badge ' + statusClass + '">' + jsonData.status + '</span></p>';
    }
    
    if (jsonData.body && jsonData.body.success !== undefined) {
        var successClass = jsonData.body.success ? 'bg-success' : 'bg-danger';
        html += '<p><strong>Éxito:</strong> <span class="badge ' + successClass + '">' + (jsonData.body.success ? 'Sí' : 'No') + '</span></p>';
    }
    
    if (jsonData.body && jsonData.body.error) {
        html += '<p><strong>Error Principal:</strong></p>';
        html += '<div class="alert alert-danger">' + jsonData.body.error + '</div>';
    }
    
    html += '</div></div></div>';
    
    // Para consultas CDC, mostrar información específica de SIFEN
    if (isFromCdc && jsonData.body && jsonData.body.deList && jsonData.body.deList.length > 0) {
        html += '<div class="col-md-6">';
        html += '<div class="card">';
        html += '<div class="card-header"><strong>Información del Documento SIFEN</strong></div>';
        html += '<div class="card-body">';
        
        var doc = jsonData.body.deList[0]; // Tomar el primer documento
        
        if (doc.cdc) {
            html += '<p><strong>CDC:</strong> <code>' + doc.cdc + '</code></p>';
        }
        if (doc.numero) {
            html += '<p><strong>Número:</strong> <span class="badge bg-primary">' + doc.numero + '</span></p>';
        }
        if (doc.situacion !== undefined) {
            var situacionClass = '';
            var situacionText = '';
            switch(parseInt(doc.situacion)) {
                case 0: situacionClass = 'bg-secondary'; situacionText = 'Generado'; break;
                case 1: situacionClass = 'bg-info'; situacionText = 'Enviado en Lote'; break;
                case 2: situacionClass = 'bg-success'; situacionText = 'Aprobado'; break;
                case 3: situacionClass = 'bg-warning'; situacionText = 'Aprobado con Observación'; break;
                case 4: situacionClass = 'bg-danger'; situacionText = 'Rechazado'; break;
                case 98: situacionClass = 'bg-dark'; situacionText = 'Inexistente'; break;
                case 99: situacionClass = 'bg-secondary'; situacionText = 'Cancelado'; break;
                default: situacionClass = 'bg-secondary'; situacionText = 'Estado ' + doc.situacion;
            }
            html += '<p><strong>Estado/Situación:</strong> <span class="badge ' + situacionClass + '">' + situacionText + ' (' + doc.situacion + ')</span></p>';
        }
        if (doc.fecha) {
            html += '<p><strong>Fecha:</strong> ' + doc.fecha + '</p>';
        }
        if (doc.respuesta_codigo) {
            html += '<p><strong>Código de Respuesta:</strong> <span class="badge bg-warning">' + doc.respuesta_codigo + '</span></p>';
        }
        if (doc.respuesta_mensaje) {
            html += '<p><strong>Mensaje de Respuesta:</strong></p>';
            var mensajeClass = doc.respuesta_codigo && doc.respuesta_codigo !== '0000' ? 'alert-danger' : 'alert-info';
            html += '<div class="alert ' + mensajeClass + '">' + doc.respuesta_mensaje + '</div>';
        }
        
        html += '</div></div></div>';
    } else if (isFromCdc && jsonData.body) {
        // Fallback para el formato anterior de respuesta SIFEN
        var body = jsonData.body;
        html += '<div class="col-md-6">';
        html += '<div class="card">';
        html += '<div class="card-header"><strong>Información SIFEN (Formato Anterior)</strong></div>';
        html += '<div class="card-body">';
        
        if (body.dEstado) {
            html += '<p><strong>Estado SIFEN:</strong> <span class="badge bg-primary">' + body.dEstado + '</span></p>';
        }
        if (body.dFecProc) {
            html += '<p><strong>Fecha Procesamiento:</strong> ' + body.dFecProc + '</p>';
        }
        if (body.dCodRes) {
            html += '<p><strong>Código Resultado:</strong> ' + body.dCodRes + '</p>';
        }
        if (body.dMsgRes) {
            html += '<p><strong>Mensaje Resultado:</strong></p>';
            html += '<div class="alert alert-info">' + body.dMsgRes + '</div>';
        }
        
        // Información del documento si está disponible
        if (body.xContRec && body.xContRec.dFeEmiDE) {
            html += '<p><strong>Fecha Emisión:</strong> ' + body.xContRec.dFeEmiDE + '</p>';
        }
        if (body.xContRec && body.xContRec.dNumDoc) {
            html += '<p><strong>Número Documento:</strong> ' + body.xContRec.dNumDoc + '</p>';
        }
        
        html += '</div></div></div>';
    }
    
    // Errores detallados
    if (jsonData.body && jsonData.body.errores && jsonData.body.errores.length > 0) {
        html += '<div class="col-md-6">';
        html += '<div class="card">';
        html += '<div class="card-header"><strong>Errores Detallados</strong></div>';
        html += '<div class="card-body">';
        
        jsonData.body.errores.forEach(function(error, index) {
            html += '<div class="alert alert-warning mb-2">';
            html += '<strong>Error ' + (index + 1) + ':</strong><br>';
            html += error.error || error;
            if (error.index !== undefined) {
                html += '<br><small class="text-muted">Índice: ' + error.index + '</small>';
            }
            html += '</div>';
        });
        
        html += '</div></div></div>';
    }
    
    html += '</div>';
    
    // JSON Raw
    html += '<div class="mt-3">';
    html += '<div class="card">';
    html += '<div class="card-header">';
    html += '<strong>Respuesta Completa (JSON)</strong>';
    html += '<button class="btn btn-sm btn-outline-secondary float-end" onclick="toggleJsonRaw()">Mostrar/Ocultar</button>';
    html += '<button class="btn btn-sm btn-outline-primary float-end me-2" onclick="copyJsonToClipboard()"><i data-feather="copy"></i> Copiar</button>';
    html += '</div>';
    html += '<div class="card-body" id="json-raw-content" style="display: none;">';
    html += '<pre class="bg-light p-3" style="max-height: 300px; overflow-y: auto;"><code>' + JSON.stringify(jsonData, null, 2) + '</code></pre>';
    html += '</div></div></div>';
    
    return html;
}

function toggleJsonRaw() {
    var rawContent = document.getElementById('json-raw-content');
    if (rawContent.style.display === 'none') {
        rawContent.style.display = 'block';
    } else {
        rawContent.style.display = 'none';
    }
}

function copyJsonToClipboard() {
    var jsonText = document.querySelector('#json-raw-content code').textContent;
    
    if (navigator.clipboard) {
        navigator.clipboard.writeText(jsonText).then(function() {
            // Mostrar feedback visual
            var btn = event.target.closest('button');
            var originalHtml = btn.innerHTML;
            btn.innerHTML = '<i data-feather="check"></i> Copiado!';
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-success');
            
            setTimeout(function() {
                btn.innerHTML = originalHtml;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-primary');
                if (typeof feather !== 'undefined') {
                    feather.replace();
                }
            }, 2000);
        });
    } else {
        // Fallback para navegadores más antiguos
        var textArea = document.createElement('textarea');
        textArea.value = jsonText;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('JSON copiado al portapapeles');
    }
}

$(document).ready(function() {
    // Manejar click del botón logs
    $('.btn-logs').on('click', function() {
        var facturaId = $(this).data('factura-id');
        var facturaNumero = $(this).data('factura-numero');
        
        // Actualizar título del modal
        $('#logsModalLabel').html('<i data-feather="terminal"></i> Logs de Factura ' + facturaNumero);
        
        // Mostrar modal
        $('#logsModal').modal('show');
        
        // Mostrar loading
        $('#logs-loading').show();
        $('#logs-content').hide();
        $('#logs-error').hide();
        
        // Realizar petición AJAX
        $.ajax({
            url: '<?php echo base_url("Facturacion_py/ajax_get_logs"); ?>',
            type: 'POST',
            data: {
                factura_id: facturaId
            },
            dataType: 'json',
            success: function(response) {
                $('#logs-loading').hide();
                
                if (response.success) {
                    var logsHtml = '';
                    
                    if (response.logs && response.logs.length > 0) {
                        response.logs.forEach(function(log, index) {
                            // Determinar la clase CSS basada en el tipo de acción
                            var cardClass = 'log-card';
                            if (log.tipo_accion && log.tipo_accion.includes('ERROR')) {
                                cardClass += ' error';
                            } else if (log.tipo_accion && (log.tipo_accion.includes('SUCCESS') || log.tipo_accion.includes('APROBADO'))) {
                                cardClass += ' success';
                            } else if (log.tipo_accion && log.tipo_accion.includes('WARNING')) {
                                cardClass += ' warning';
                            }
                            
                            logsHtml += '<div class="card mb-3 ' + cardClass + '">';
                            logsHtml += '<div class="card-header">';
                            logsHtml += '<div class="row align-items-center">';
                            logsHtml += '<div class="col-md-6">';
                            logsHtml += '<h6 class="mb-0"><strong>Log #' + (index + 1) + '</strong></h6>';
                            logsHtml += '<small class="text-muted">' + (log.fecha_modificacion ? new Date(log.fecha_modificacion).toLocaleString('es-ES') : 'N/A') + '</small>';
                            logsHtml += '</div>';
                            logsHtml += '<div class="col-md-6 text-end">';
                            logsHtml += '<span class="badge bg-info me-2">' + (log.tipo_accion || 'N/A') + '</span>';
                            var usuarioInfo = 'N/A';
                            if (log.usuario_nombre) {
                                usuarioInfo = log.usuario_nombre + ' (ID: ' + (log.usuario_id || 'N/A') + ')';
                            } else if (log.usuario_id) {
                                usuarioInfo = 'Usuario ID: ' + log.usuario_id;
                            }
                            logsHtml += '<small class="text-muted">' + usuarioInfo + '</small>';
                            logsHtml += '</div>';
                            logsHtml += '</div>';
                            logsHtml += '</div>';
                            
                            if (log.json_backup) {
                                try {
                                    var jsonData = JSON.parse(log.json_backup);
                                    logsHtml += '<div class="card-body">';
                                    logsHtml += formatJsonForDisplay(jsonData, log.tipo_accion || 'N/A', false);
                                    logsHtml += '</div>';
                                } catch (e) {
                                    logsHtml += '<div class="card-body">';
                                    logsHtml += '<div class="alert alert-warning">JSON malformado: ' + e.message + '</div>';
                                    logsHtml += '</div>';
                                }
                            } else {
                                logsHtml += '<div class="card-body">';
                                logsHtml += '<div class="alert alert-info">No hay información JSON disponible para este log</div>';
                                logsHtml += '</div>';
                            }
                            
                            logsHtml += '</div>';
                        });
                    } else {
                        logsHtml = '<div class="alert alert-info">';
                        logsHtml += '<i data-feather="info"></i> ';
                        logsHtml += (response.message || 'No se encontraron logs para esta factura');
                        logsHtml += '</div>';
                    }
                    
                    $('#logs-content').html(logsHtml).show();
                    
                    // Reactivar iconos de feather
                    if (typeof feather !== 'undefined') {
                        feather.replace();
                    }
                } else {
                    $('#logs-error-message').text(response.message || 'Error al cargar los logs');
                    $('#logs-error').show();
                }
            },
            error: function(xhr, status, error) {
                $('#logs-loading').hide();
                $('#logs-error-message').text('Error de conexión: ' + error);
                $('#logs-error').show();
            }
        });
    });
    
    // Manejar formulario CDC
    $('#cdcForm').on('submit', function(e) {
        e.preventDefault();
        
        var cdc = $('#cdcInput').val().trim();
        
        // Validación básica
        if (!cdc) {
            alert('Por favor ingrese un CDC');
            return;
        }
        
        if (cdc.length !== 44) {
            alert('El CDC debe tener exactamente 44 dígitos');
            return;
        }
        
        if (!/^\d{44}$/.test(cdc)) {
            alert('El CDC solo debe contener números');
            return;
        }
        
        // Mostrar loading
        $('#cdc-loading').show();
        $('#cdc-results').hide();
        $('#cdc-error').hide();
        
        // Realizar petición AJAX
        $.ajax({
            url: '<?php echo base_url("Facturacion_py/ajax_consultar_cdc"); ?>',
            type: 'POST',
            data: {
                cdc: cdc
            },
            dataType: 'json',
            success: function(response) {
                $('#cdc-loading').hide();
                
                if (response.success && response.data) {
                    var resultsHtml = formatJsonForDisplay(response.data, 'CONSULTA_CDC', true);
                    $('#cdc-results').html(resultsHtml).show();
                    
                    // Reactivar iconos de feather
                    if (typeof feather !== 'undefined') {
                        feather.replace();
                    }
                } else {
                    $('#cdc-error-message').text(response.message || 'Error al consultar el CDC');
                    $('#cdc-error').show();
                }
            },
            error: function(xhr, status, error) {
                $('#cdc-loading').hide();
                $('#cdc-error-message').text('Error de conexión: ' + error);
                $('#cdc-error').show();
            }
        });
    });
    
    // Limpiar modal CDC al cerrarlo
    $('#cdcModal').on('hidden.bs.modal', function() {
        $('#cdcForm')[0].reset();
        $('#cdc-loading').hide();
        $('#cdc-results').hide();
        $('#cdc-error').hide();
        updateCdcCounter();
    });
    
    // Validación en tiempo real del input CDC
    $('#cdcInput').on('input', function() {
        var value = $(this).val();
        
        // Solo permitir números
        value = value.replace(/\D/g, '');
        $(this).val(value);
        
        updateCdcCounter();
    });
    
    function updateCdcCounter() {
        var length = $('#cdcInput').val().length;
        var counter = $('#cdc-counter');
        var input = $('#cdcInput');
        
        counter.text(length + '/44');
        
        if (length === 0) {
            counter.removeClass('text-success text-danger').addClass('text-muted');
            input.removeClass('is-valid is-invalid');
        } else if (length === 44) {
            counter.removeClass('text-muted text-danger').addClass('text-success');
            input.removeClass('is-invalid').addClass('is-valid');
        } else {
            counter.removeClass('text-muted text-success').addClass('text-danger');
            input.removeClass('is-valid').addClass('is-invalid');
        }
    }
});
</script>