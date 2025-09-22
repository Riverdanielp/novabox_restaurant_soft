<style>
    .seccion-factura { border: 1px solid #ddd; border-radius: 5px; padding: 20px; margin-bottom: 25px; background: #fdfdfd; }
    .seccion-factura h4 { border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; margin-bottom: 20px; font-size: 1.1rem; }
    label { font-weight: 600; margin-bottom: 0.5rem; }
    .autocomplete-results { position: absolute; background-color: white; border: 1px solid #ddd; z-index: 1000; max-height: 200px; overflow-y: auto; width: 100%; }
    .autocomplete-item { padding: 8px 12px; cursor: pointer; }
    .autocomplete-item:hover { background-color: #f0f0f0; }
    .position-relative { position: relative; }
    .item-detalles-avanzados, .pago-detalles-avanzados { display: flex; background-color: #f7f7f7; padding: 15px; margin-top: 10px; border-radius: 4px; }
    .seccion-documento-referencia { display: none; }
    .seccion-campos-especiales { display: none; }
    .tipo-documento-alerta { display: none; padding: 10px; margin: 10px 0; border-radius: 4px; }
    .seccion-oculta { display: none; }
    
    /* Estilos para las alertas de flashdata */
    .alert {
        position: relative;
        padding: 0.75rem 1.25rem;
        margin-bottom: 1rem;
        border: 1px solid transparent;
        border-radius: 0.375rem;
    }
    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
    }
    .alert-success {
        color: #155724;
        background-color: #d4edda;
        border-color: #c3e6cb;
    }
    .alert-info {
        color: #0c5460;
        background-color: #d1ecf1;
        border-color: #bee5eb;
    }
    .alert-warning {
        color: #856404;
        background-color: #fff3cd;
        border-color: #ffeaa7;
    }
    .alert-dismissible .btn-close {
        position: absolute;
        top: 0;
        right: 0;
        z-index: 2;
        padding: 1.25rem 1.25rem;
        background: transparent;
        border: none;
        cursor: pointer;
        font-size: 1.25rem;
        color: inherit;
        opacity: 0.5;
    }
    .alert-dismissible .btn-close:hover {
        opacity: 1;
    }
    .alert i[data-feather] {
        margin-right: 0.5rem;
        width: 16px;
        height: 16px;
    }
    .fade {
        transition: opacity 0.15s linear;
    }
    .fade.show {
        opacity: 1;
    }
</style>

<section class="main-content-wrapper">
    <section class="content-header">
        <h3 class="top-left-header"><?php echo $form_title; ?></h3>
    </section>

    <div class="box-wrapper">
    
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
    
    <?php 
        // Construir la URL de acción dinámicamente
        $action_url = $is_edit 
            ? base_url('Facturacion_py/procesar_formulario/' . $factura->id) 
            : base_url('Facturacion_py/procesar_formulario');
            
        echo form_open($action_url, ['id' => 'form-factura']); 
    ?>
        
        <!-- SECCIÓN 1: DATOS DEL DOCUMENTO -->
        <div class="seccion-factura">
            <h4><i data-feather="file-text"></i> Datos del Documento</h4>

            <div class="row g-3">
                <div class="col-md-3">
                    <label>Tipo Documento (*)</label>
                    <select name="tipoDocumento" id="tipoDocumento" class="form-control" required>
                        <?php foreach($tipos_documento as $k => $v) echo "<option value='{$k}'>{$v}</option>"; ?>
                    </select>
                </div>
                
                <!-- Alertas específicas por tipo de documento -->
                <div class="col-12">
                    <div class="alert alert-danger fade show" role="alert" id="alerta-desarrollo" style="display:none;">
                        <i data-feather="alert-circle"></i> 
                        <strong>Error:</strong> El módulo de facturación aún se encuentra en desarrollo. Por el momento, no es posible enviar este tipo de documento debido a que faltan validaciones necesarias para evitar su rechazo por parte de la SIFEN.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>

                    <div id="alerta-factura" class="tipo-documento-alerta alert alert-info">
                        <i data-feather="info"></i> Creando una Factura Electrónica
                    </div>
                    <div id="alerta-autofactura" class="tipo-documento-alerta alert alert-warning">
                        <i data-feather="alert-triangle"></i> Las Autofacturas se emiten cuando se compran productos primarios a personas que no emiten facturas
                    </div>
                    <div id="alerta-nota-credito" class="tipo-documento-alerta alert alert-primary">
                        <i data-feather="file-minus"></i> Las Notas de Crédito se emiten para anular o devolver facturas existentes
                    </div>
                    <div id="alerta-nota-debito" class="tipo-documento-alerta alert alert-secondary">
                        <i data-feather="file-plus"></i> Las Notas de Débito se emiten para aumentar el valor de facturas existentes
                    </div>
                    <div id="alerta-remision" class="tipo-documento-alerta alert alert-warning">
                        <i data-feather="truck"></i> Las Notas de Remisión se emiten para amparar el traslado de mercaderías
                    </div>
                </div>
                
                <!-- CORREGIDO: Selector de Punto de Expedición agrupado por Sucursal -->
                <div class="col-md-3">
                    <label>Sucursal y Punto (*)</label>
                    <select name="punto" class="form-control" required>
                        <?php foreach($sucursales_con_puntos as $sucursal): ?>
                            <optgroup label="<?php echo html_escape($sucursal->nombre); ?>">
                                <?php foreach($sucursal->puntos as $punto): ?>
                                    <option value="<?php echo $punto->codigo_punto; ?>"><?php echo "Punto {$punto->codigo_punto} - {$punto->nombre}"; ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Fecha Emisión (*)</label>
                    <input type="datetime-local" name="fecha" class="form-control" 
                        value="<?php echo date('Y-m-d\TH:i:s'); ?>" 
                        step="1" required>
                </div>
                <div class="col-md-3"><label>Moneda (*)</label><select name="moneda" class="form-control" required><option value="PYG">PYG - Guaraní</option></select></div>
                <div class="col-md-3"><label>Tipo Emisión (*)</label><select name="tipoEmision" class="form-control" required><?php foreach($tipos_emision as $k => $v) echo "<option value='{$k}'>{$v}</option>"; ?></select></div>
                <div class="col-md-3"><label>Tipo Transacción (*)</label><select name="tipoTransaccion" class="form-control" required><?php foreach($tipos_transaccion as $k => $v) echo "<option value='{$k}'>{$v}</option>"; ?></select></div>
                <div class="col-md-3"><label>Tipo Impuesto (*)</label><select name="tipoImpuesto" class="form-control" required><?php foreach($tipos_impuesto as $k => $v) echo "<option value='{$k}'>{$v}</option>"; ?></select></div>
                <div class="col-md-3" id="seccion-presencia"><label>Indicador Presencia (*)</label><select name="factura[presencia]" class="form-control" required><?php foreach($tipos_presencia as $k => $v) echo "<option value='{$k}'>{$v}</option>"; ?></select></div>
                <div class="col-md-6"><label>Descripción (Opcional)</label><input type="text" name="descripcion" class="form-control" placeholder="Ej: Venta de productos varios"></div>
                <div class="col-md-6"><label>Observación (Opcional)</label><input type="text" name="observacion" class="form-control" placeholder="Ej: Promociones, marketing, etc."></div>
            </div>
        </div>

        <!-- SECCIÓN: DOCUMENTO DE REFERENCIA (para Notas de Crédito/Débito) -->
        <div class="seccion-factura seccion-documento-referencia">
            <h4><i data-feather="file-text"></i> Documento de Referencia</h4>
            <div class="row g-3">
                <div class="col-md-4">
                    <label>CDC del Documento (*)</label>
                    <input type="text" name="documento_referencia[cdc]" id="documento_referencia_cdc" class="form-control" placeholder="Código CDC del documento a referenciar">
                    <small class="form-text text-muted">CDC de la factura o documento que se está modificando</small>
                </div>
                <div class="col-md-4">
                    <label>Fecha Emisión Doc. Original (*)</label>
                    <input type="date" name="documento_referencia[fecha]" id="documento_referencia_fecha" class="form-control">
                </div>
                <div class="col-md-4">
                    <label>Motivo (*)</label>
                    <select name="documento_referencia[motivo]" id="documento_referencia_motivo" class="form-control">
                        <option value="1">Devolución/Anulación de productos</option>
                        <option value="2">Descuento posterior a la emisión</option>
                        <option value="3">Bonificación</option>
                        <option value="4">Corrección de datos</option>
                        <option value="5">Otro</option>
                    </select>
                </div>
                <div class="col-12" id="motivo_adicional" style="display:none;">
                    <label>Descripción del Motivo (*)</label>
                    <input type="text" name="documento_referencia[motivo_descripcion]" class="form-control" placeholder="Detalles específicos del motivo">
                </div>
            </div>
        </div>

        <!-- SECCIÓN: CAMPOS ESPECIALES PARA AUTOFACTURA -->
        <div class="seccion-factura seccion-campos-especiales" id="campos-autofactura">
            <h4><i data-feather="alert-triangle"></i> Información Especial para Autofactura</h4>
            <div class="row g-3">
                <div class="col-md-4">
                    <label>Tipo de Vendedor (*)</label>
                    <select name="autofactura[tipo_vendedor]" class="form-control">
                        <option value="1">Persona Física</option>
                        <option value="2">Comunidad Indígena</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Ubicación del Vendedor (*)</label>
                    <input type="text" name="autofactura[ubicacion]" class="form-control" placeholder="Dirección completa del vendedor">
                </div>
                <div class="col-md-4">
                    <label>Registro INDERT/INDI</label>
                    <input type="text" name="autofactura[registro]" class="form-control" placeholder="Número de registro (si aplica)">
                </div>
            </div>
        </div>
        
        <!-- SECCIÓN: CAMPOS ESPECIALES PARA NOTA DE REMISIÓN -->
        <div class="seccion-factura seccion-campos-especiales" id="campos-remision">
            <h4><i data-feather="truck"></i> Información para Nota de Remisión</h4>
            <div class="row g-3">
                <div class="col-md-4">
                    <label>Motivo de Traslado (*)</label>
                    <select name="remision[motivo_traslado]" class="form-control">
                        <option value="1">Traslado por venta</option>
                        <option value="2">Traslado por consignación</option>
                        <option value="3">Exportación</option>
                        <option value="4">Traslado por compra</option>
                        <option value="5">Importación</option>
                        <option value="6">Traslado entre establecimientos</option>
                        <option value="7">Traslado para reparación</option>
                        <option value="8">Traslado por emisor móvil</option>
                        <option value="9">Traslado a depósito</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Fecha Inicio Traslado (*)</label>
                    <input type="datetime-local" name="remision[fecha_inicio]" class="form-control">
                </div>
                <div class="col-md-4">
                    <label>Fecha Fin Traslado (*)</label>
                    <input type="datetime-local" name="remision[fecha_fin]" class="form-control">
                </div>
                
                <div class="col-md-12 mt-3">
                    <h5>Datos del Vehículo</h5>
                </div>
                <div class="col-md-3">
                    <label>Tipo Vehículo (*)</label>
                    <select name="remision[vehiculo_tipo]" class="form-control">
                        <option value="1">Transporte propio</option>
                        <option value="2">Transporte tercero</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Marca</label>
                    <input type="text" name="remision[vehiculo_marca]" class="form-control">
                </div>
                <div class="col-md-3">
                    <label>Número de Chasis</label>
                    <input type="text" name="remision[vehiculo_chasis]" class="form-control">
                </div>
                <div class="col-md-3">
                    <label>Número de Matrícula (*)</label>
                    <input type="text" name="remision[vehiculo_matricula]" class="form-control" placeholder="Ej: ABC123">
                </div>
                
                <div class="col-md-12 mt-3">
                    <h5>Datos del Conductor</h5>
                </div>
                <div class="col-md-4">
                    <label>Nombre del Conductor (*)</label>
                    <input type="text" name="remision[conductor_nombre]" class="form-control">
                </div>
                <div class="col-md-4">
                    <label>RUC/C.I. del Conductor (*)</label>
                    <input type="text" name="remision[conductor_documento]" class="form-control" placeholder="Documento sin guión">
                </div>
                <div class="col-md-4">
                    <label>Dirección del Conductor</label>
                    <input type="text" name="remision[conductor_direccion]" class="form-control">
                </div>
            </div>
        </div>

        <!-- SECCIÓN 2: DATOS DEL CLIENTE -->
        <div class="seccion-factura" id="seccion-cliente">
            <h4><i data-feather="user"></i> Datos del Cliente</h4>
            <div class="row g-3">
                <div class="col-md-3 position-relative"><label>Buscar (RUC/Nombre)</label><input type="text" id="cliente_search_input" class="form-control" placeholder="Escriba para buscar..."><div id="cliente_results" class="autocomplete-results"></div></div>
                <div class="col-md-3"><label>RUC / C.I. (*)</label><div class="input-group"><input type="text" name="cliente[ruc]" id="cliente_ruc" class="form-control" required><button class="btn btn-outline-secondary" type="button" id="ruc_search_btn">API</button></div><small id="ruc_message" class="form-text"></small></div>
                <div class="col-md-3"><label>Razón Social (*)</label><input type="text" name="cliente[razonSocial]" id="cliente_razonSocial" class="form-control" required></div>
                <div class="col-md-3"><label>Nombre Fantasía</label><input type="text" name="cliente[nombreFantasia]" id="cliente_nombreFantasia" class="form-control"></div>
                <div class="col-md-2"><label>Tipo Operación (*)</label><select name="cliente[tipoOperacion]" class="form-control" required><?php foreach($tipos_operacion_cliente as $k => $v) echo "<option value='{$k}'>{$v}</option>"; ?></select></div>
                <div class="col-md-2"><label>Tipo Contribuyente (*)</label><select name="cliente[tipoContribuyente]" id="cliente_tipoContribuyente" class="form-control" required><option value="1">Persona Física</option><option value="2">Persona Jurídica</option></select></div>
                <div class="col-md-2"><label>Tipo Documento (*)</label><select name="cliente[documentoTipo]" class="form-control" required><?php foreach($tipos_doc_cliente as $k => $v) echo "<option value='{$k}'>{$v}</option>"; ?></select></div>
                <div class="col-md-3"><label>Email</label><input type="email" name="cliente[email]" id="cliente_email" class="form-control"></div>
                <div class="col-md-3"><label>Teléfono/Celular</label><input type="text" name="cliente[telefono]" id="cliente_telefono" class="form-control"></div>
                <div class="col-md-4"><label>Dirección</label><input type="text" name="cliente[direccion]" id="cliente_direccion" class="form-control"></div>
                <div class="col-md-1"><label>N° Casa (*)</label><input type="text" name="cliente[numeroCasa]" id="cliente_numeroCasa" class="form-control" value="0" required></div>
                <div class="col-md-2"><label>Departamento</label><select name="cliente[departamento]" id="cliente_departamento" class="form-control"><?php foreach($departamentos as $d) echo "<option value='{$d->id}'>{$d->nombre}</option>"; ?></select></div>
                <div class="col-md-2"><label>Distrito</label><select name="cliente[distrito]" id="cliente_distrito" class="form-control" disabled></select></div>
                <div class="col-md-2"><label>Ciudad</label><select name="cliente[ciudad]" id="cliente_ciudad" class="form-control" disabled></select></div>
                <input type="hidden" name="cliente[codigo]" id="cliente_codigo">
            </div>
        </div>

        <!-- SECCIÓN 3: DATOS DEL USUARIO (Vendedor) -->
        <div class="seccion-factura" id="seccion-usuario">
            <h4><i data-feather="briefcase"></i> <span id="usuario-seccion-titulo">Datos del Usuario (Vendedor)</span></h4>
            <div class="row g-3">
                <div class="col-md-4 position-relative"><label>Buscar Usuario</label><input type="text" id="usuario_search_input" class="form-control" placeholder="Buscar por Nombre o Documento..."><div id="usuario_results" class="autocomplete-results"></div></div>
                <div class="col-md-2"><label>Documento N° (*)</label><input type="text" name="usuario[documentoNumero]" id="usuario_documentoNumero" class="form-control" required></div>
                <div class="col-md-3"><label>Nombre (*)</label><input type="text" name="usuario[nombre]" id="usuario_nombre" class="form-control" required></div>
                <div class="col-md-3"><label>Cargo (*)</label><input type="text" name="usuario[cargo]" id="usuario_cargo" class="form-control" value="Vendedor" required></div>
            </div>
        </div>

        <!-- SECCIÓN 4: ITEMS -->
        <div class="seccion-factura" id="seccion-items">
            <h4><i data-feather="shopping-cart"></i> Items de la Factura</h4>
            <div id="items-container"></div>
            <button type="button" class="btn btn-default mt-2" id="add-item-btn"><i data-feather="plus"></i> Añadir Item</button>
            <h3 class="text-end mt-3">Total Factura: <span id="total_general">0.00</span> Gs.</h3>
        </div>

        <!-- SECCIÓN 5: CONDICIÓN DE VENTA -->
        <div class="seccion-factura" id="seccion-condicion">
            <h4><i data-feather="dollar-sign"></i> Condición de Venta</h4>
            <div class="row">
                <div class="col-md-4">
                    <label>Condición (*)</label>
                    <select name="condicion[tipo]" id="condicion_tipo" class="form-control" required>
                        <option value="1">Contado</option>
                        <option value="2">Crédito</option>
                    </select>
                </div>
            </div>
            <div id="contado-fields">
                <hr>
                <h5>Pagos Recibidos (Entregas)</h5>
                <div id="pagos-container"></div>
                <button type="button" class="btn btn-default btn-sm mt-2" id="add-pago-btn">
                    <i data-feather="plus"></i> Añadir Forma de Pago
                </button>
                <h5 class="text-end mt-3 text-success">Total Pagado: <span id="total_pagado">0.00</span> Gs.</h5>
                <h5 class="text-end text-info">Vuelto: <span id="vuelto">0.00</span> Gs.</h5>
                <p id="mensaje-validacion-pago" class="text-center mt-2"></p>
            </div>
            <div id="credito-fields" style="display: none;">
                <hr>
                <h5>Detalles del Crédito</h5>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label>Tipo Crédito (*)</label>
                        <select name="credito[tipo]" class="form-control">
                            <option value="1">Plazo</option>
                            <option value="2">Cuotas</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Plazo</label>
                        <input type="text" name="credito[plazo]" class="form-control" placeholder="Ej: 30 días, 60 días">
                    </div>
                    <div class="col-md-3">
                        <label>N° Cuotas</label>
                        <input type="number" name="credito[cuotas]" id="credito_nro_cuotas" class="form-control" value="1">
                    </div>
                    <div class="col-md-3">
                        <label>Monto Entrega Inicial</label>
                        <input type="number" name="credito[montoEntrega]" class="form-control" value="0">
                    </div>
                </div>
                <div id="cuotas-container" class="mt-3"></div>
                <button type="button" class="btn btn-default btn-sm mt-2" id="add-cuota-btn">
                    <i data-feather="plus"></i> Añadir Cuota
                </button>
            </div>
        </div>

        <div class="box-footer">
            <button type="submit" name="submit" value="submit" class="btn bg-blue-btn me-2">
                <i data-feather="save"></i> <span id="btn-generar-texto">Generar Factura</span>
            </button>
            <a class="btn bg-blue-btn" href="<?php echo base_url('Facturacion_py/listado'); ?>">
                <i data-feather="corner-up-left"></i> Volver
            </a>
        </div>
    <?php echo form_close(); ?>
    </div>
</section>

<!-- TEMPLATES PARA JS -->
<template id="item-row-template">
    <div class="item-row-wrapper mb-3 border p-3 rounded">
        <div class="row item-row align-items-center">
            <div class="col-md-3 position-relative"><label>Descripción (*)</label><input type="text" name="items[{index}][descripcion]" class="form-control item-description-input" required><div class="autocomplete-results item-results"></div></div>
            <div class="col-md-2"><label>Codigo (*)</label><input type="text" name="items[{index}][codigo]" placeholder="Al menos 3 digitos" class="form-control item-codigo" value="" step="any" required></div>
            <div class="col-md-1"><label>Cant. (*)</label><input type="number" name="items[{index}][cantidad]" class="form-control item-qty" value="1" step="any" required></div>
            <div class="col-md-2"><label>P. Unit. (*)</label><input type="number" name="items[{index}][precioUnitario]" class="form-control item-price" step="any" required></div>
            <div class="col-md-2"><label>Subtotal</label><p class="form-control-static item-subtotal fw-bold">0.00</p></div>
            <div class="col-auto ms-auto"><label>&nbsp;</label>
            <div>
                <button type="button" class="btn btn-secondary btn-sm btn-toggle-advanced" title="Más Opciones">
                    <i data-feather="more-horizontal"></i>
                </button>
                    <button type="button" class="btn btn-danger btn-sm btn-remove-item" title="Eliminar Item">
                        <i data-feather="trash"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="item-detalles-avanzados row g-3">

            <div class="col-md-2">
                <label>Tipo IVA (*)</label>
                <select name="items[{index}][ivaTipo]" class="form-control item-iva-tipo" required>
                    <?php foreach($tipos_iva_item as $k => $v) echo "<option value='{$k}'>{$v}</option>"; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label>% IVA (*)</label>
                <select name="items[{index}][iva]" class="form-control item-iva" required>
                    <option value="10" selected>10%</option>
                    <option value="5">5%</option>
                    <option value="0">Exenta (0%)</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>Base Imponible % (*)</label>
                <input type="number" name="items[{index}][ivaBase]" class="form-control item-iva-base" 
                value="100" min="1" max="100" required>
            </div>
            <div class="col-md-2">
                <label>NCM</label><input type="text" name="items[{index}][ncm]" class="form-control">
            </div>
            <div class="col-md-2">
                <label>Lote</label><input type="text" name="items[{index}][lote]" class="form-control">
            </div>
            <div class="col-md-2">
                <label>Vencimiento</label><input type="date" name="items[{index}][vencimiento]" class="form-control">
            </div>
            <div class="col-md-3">
                <label>Observación Item</label><input type="text" name="items[{index}][observacion]" class="form-control">
            </div>
        </div>
    </div>
</template>
<template id="pago-row-template">
    <div class="row pago-row align-items-end g-3 mb-2">
        <div class="col-md-3">
            <label>Forma de Pago</label>
            <select name="condicion[entregas][{index}][tipo]" class="form-control pago-tipo">
                <?php foreach($tipos_pago as $k => $v) echo "<option value='{$k}'>{$v}</option>"; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label>Monto (*)</label>
            <input type="number" name="condicion[entregas][{index}][monto]" class="form-control pago-monto" step="any" required>
        </div>
        <div class="col-md-5 pago-detalles-avanzados"></div>
        <div class="col-md-1">
            <button type="button" class="btn btn-danger btn-sm btn-remove-pago w-100">
                <i data-feather="trash"></i>
            </button>
        </div>
    </div>
</template>
<template id="cuota-row-template">
    <div class="row cuota-row align-items-end g-3 mb-2">
        <div class="col-md-4">
            <label>Vencimiento (*)</label>
            <input type="date" name="credito[infoCuotas][{index}][vencimiento]" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label>Monto (*)</label>
            <input type="number" name="credito[infoCuotas][{index}][monto]" class="form-control" step="any" required>
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-danger btn-sm btn-remove-cuota w-100">
                <i data-feather="trash"></i>
            </button>
        </div>
    </div>
</template>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrf_token_name = '<?php echo $this->security->get_csrf_token_name(); ?>';
    const csrf_hash = '<?php echo $this->security->get_csrf_hash(); ?>';
    let searchTimeout;

    // --- LÓGICA DE TIPO DE DOCUMENTO Y SECCIONES VISIBLES ---
    function actualizarInterfazSegunTipoDocumento() {
        const tipoDocumento = parseInt(document.getElementById('tipoDocumento').value);
        
        // Ocultar todas las alertas y secciones especiales primero
        document.querySelectorAll('.tipo-documento-alerta').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.seccion-campos-especiales').forEach(el => el.style.display = 'none');
        document.querySelector('.seccion-documento-referencia').style.display = 'none';
        
        // Mostrar todas las secciones principales por defecto
        document.getElementById('seccion-cliente').classList.remove('seccion-oculta');
        document.getElementById('seccion-usuario').classList.remove('seccion-oculta');
        document.getElementById('seccion-items').classList.remove('seccion-oculta');
        document.getElementById('seccion-condicion').classList.remove('seccion-oculta');
        document.getElementById('seccion-presencia').style.display = 'block';
        
        // Configurar según el tipo de documento
        switch (tipoDocumento) {
            case 1: // Factura Electrónica
                document.getElementById('alerta-factura').style.display = 'block';
                document.getElementById('usuario-seccion-titulo').textContent = 'Datos del Usuario (Vendedor)';
                document.getElementById('btn-generar-texto').textContent = 'Generar Factura';
                document.getElementById('alerta-desarrollo').style.display = 'none';
                break;
                
            case 4: // Autofactura
                document.getElementById('alerta-autofactura').style.display = 'block';
                document.getElementById('campos-autofactura').style.display = 'block';
                document.getElementById('usuario-seccion-titulo').textContent = 'Datos del Usuario (Comprador)';
                document.getElementById('btn-generar-texto').textContent = 'Generar Autofactura';
                document.getElementById('alerta-desarrollo').style.display = 'block';
                break;
                
            case 5: // Nota de Crédito
                document.getElementById('alerta-nota-credito').style.display = 'block';
                document.querySelector('.seccion-documento-referencia').style.display = 'block';
                document.getElementById('btn-generar-texto').textContent = 'Generar Nota de Crédito';
                document.getElementById('alerta-desarrollo').style.display = 'block';
                break;
                
            case 6: // Nota de Débito
                document.getElementById('alerta-nota-debito').style.display = 'block';
                document.querySelector('.seccion-documento-referencia').style.display = 'block';
                document.getElementById('btn-generar-texto').textContent = 'Generar Nota de Débito';
                document.getElementById('alerta-desarrollo').style.display = 'block';
                break;
                
            case 7: // Nota de Remisión
                document.getElementById('alerta-remision').style.display = 'block';
                document.getElementById('campos-remision').style.display = 'block';
                document.getElementById('seccion-condicion').classList.add('seccion-oculta'); // No requiere condición de venta
                document.getElementById('seccion-presencia').style.display = 'none'; // No requiere indicador de presencia
                document.getElementById('btn-generar-texto').textContent = 'Generar Nota de Remisión';
                document.getElementById('alerta-desarrollo').style.display = 'block';
                break;
        }
    }
    
    // Asociar el cambio de tipo de documento con la actualización de la interfaz
    document.getElementById('tipoDocumento').addEventListener('change', actualizarInterfazSegunTipoDocumento);
    
    // Ejecutar la lógica inicial para configurar la interfaz según el tipo de documento seleccionado
    actualizarInterfazSegunTipoDocumento();
    
    // Evento para el motivo en documentos de referencia
    document.getElementById('documento_referencia_motivo').addEventListener('change', function() {
        const mostrarAdicional = (this.value === '5'); // Mostrar detalles adicionales para "Otro"
        document.getElementById('motivo_adicional').style.display = mostrarAdicional ? 'block' : 'none';
    });

    // --- LÓGICA DE BÚSQUEDA GENÉRICA (AUTOCOMPLETE) ---
    function setupAutocomplete(inputId, resultsId, url, processFn, selectFn) {
        const input = document.getElementById(inputId);
        const results = document.getElementById(resultsId);
        if (!input) return;

        input.addEventListener('keyup', function() {
            clearTimeout(searchTimeout);
            const term = input.value;
            if (term.length < 2) {
                results.style.display = 'none';
                return;
            }
            searchTimeout = setTimeout(() => {
                const formData = new FormData();
                formData.append('term', term);
                formData.append(csrf_token_name, csrf_hash);
                fetch(url, { method: 'POST', body: formData }).then(r => r.json()).then(data => {
                    results.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(item => {
                            const div = document.createElement('div');
                            div.className = 'autocomplete-item';
                            div.innerHTML = processFn(item);
                            div.addEventListener('click', () => {
                                selectFn(item);
                                results.style.display = 'none';
                            });
                            results.appendChild(div);
                        });
                        results.style.display = 'block';
                    } else {
                        results.innerHTML = '<div class="autocomplete-item text-muted">No se encontraron resultados.</div>';
                        results.style.display = 'block';
                    }
                });
            }, 300);
        });
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.position-relative')) {
                results.style.display = 'none';
            }
        });
    }

    // --- SECCIÓN: CLIENTE Y UBICACIÓN ---
    setupAutocomplete('cliente_search_input', 'cliente_results', "<?php echo base_url('Facturacion_py/ajax_buscar_py_clientes'); ?>",
        item => `${item.razon_social} (${item.ruc})`,
        item => {
            document.getElementById('cliente_search_input').value = item.razon_social;
            document.getElementById('cliente_ruc').value = item.ruc;
            document.getElementById('cliente_razonSocial').value = item.razon_social;
            document.getElementById('cliente_tipoContribuyente').value = tipoContribuyente(item.ruc);
            document.getElementById('cliente_direccion').value = item.direccion;
            document.getElementById('cliente_numeroCasa').value = item.numero_casa || '0';
            document.getElementById('cliente_email').value = item.email;
            document.getElementById('cliente_telefono').value = item.telefono || item.celular;
            document.getElementById('cliente_codigo').value = item.codigo;
            document.getElementById('cliente_departamento').value = item.departamento;
            fetchDistritos(item.departamento, item.distrito);
        }
    );

    document.getElementById('ruc_search_btn').addEventListener('click', function() {
        const rucInput = document.getElementById('cliente_ruc');
        const ruc = rucInput.value.split('-')[0];
        const msg = document.getElementById('ruc_message');

        if (ruc.length < 5) return;
        msg.textContent = 'Buscando...';
        msg.className = 'form-text text-warning';
        
        const formData = new FormData();
        formData.append('ruc', ruc);
        formData.append(csrf_token_name, csrf_hash);

        fetch("<?php echo base_url('Facturacion_py/ajax_consultar_ruc'); ?>", { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.error || !data.ruc) {
                msg.textContent = data.error || 'No encontrado.';
                msg.className = 'form-text text-danger';
            } else {
                rucInput.value = `${data.ruc}-${data.dv}`;
                document.getElementById('cliente_razonSocial').value = data.nombre + ' ' + data.apellido || '';
                document.getElementById('cliente_search_input').value = data.nombre + ' ' + data.apellido || '';
                document.getElementById('cliente_tipoContribuyente').value = tipoContribuyente(rucInput.value);
                msg.textContent = 'RUC encontrado!';
                msg.className = 'form-text text-success';
            }
        });
    });
    
    document.getElementById('cliente_departamento').addEventListener('change', function() { fetchDistritos(this.value); });
    document.getElementById('cliente_distrito').addEventListener('change', function() { fetchCiudades(this.value); });

    function fetchDistritos(deptoId, selectedId = null) {
        const distSelect = document.getElementById('cliente_distrito');
        const ciuSelect = document.getElementById('cliente_ciudad');
        if (!deptoId) {
            distSelect.innerHTML = '';
            distSelect.disabled = true;
            ciuSelect.innerHTML = '';
            ciuSelect.disabled = true;
            return;
        }
        fetch(`<?php echo base_url('Facturacion_py/ajax_get_distritos'); ?>?departamento_id=${deptoId}`)
        .then(res => res.json()).then(data => {
            distSelect.innerHTML = '<option value="">Seleccione Distrito</option>';
            data.forEach(d => distSelect.innerHTML += `<option value="${d.id}">${d.nombre}</option>`);
            distSelect.disabled = false;
            if (selectedId) {
                distSelect.value = selectedId;
                // Almacenar el ID de la ciudad para usarlo después de que los distritos carguen
                document.getElementById('cliente_ciudad').dataset.selected = ''; // Limpiar
                if (document.querySelector(`option[value='${selectedId}']`)) {
                    fetchCiudades(selectedId, null); // Pasar el ID de ciudad guardado si existe
                }
            }
        });
    }

    function fetchCiudades(distId, selectedId = null) {
        const ciuSelect = document.getElementById('cliente_ciudad');
        if (!distId) {
            ciuSelect.innerHTML = '';
            ciuSelect.disabled = true;
            return;
        }
        fetch(`<?php echo base_url('Facturacion_py/ajax_get_ciudades'); ?>?distrito_id=${distId}`)
        .then(res => res.json()).then(data => {
            ciuSelect.innerHTML = '<option value="">Seleccione Ciudad</option>';
            data.forEach(c => ciuSelect.innerHTML += `<option value="${c.id}">${c.nombre}</option>`);
            ciuSelect.disabled = false;
            if (selectedId) {
                ciuSelect.value = selectedId;
            }
        });
    }

    function tipoContribuyente(ruc) {
        let limpio = ruc.toString().replace(/[\s\.\-]/g, "");
        let numero = parseInt(limpio, 10);
        return (isNaN(numero) || numero < 80000000) ? 1 : 2;
    }

    // --- SECCIÓN: USUARIO ---
    setupAutocomplete('usuario_search_input', 'usuario_results', "<?php echo base_url('Facturacion_py/ajax_buscar_py_usuarios'); ?>",
        item => `${item.nombre} (${item.documento_numero})`,
        item => {
            document.getElementById('usuario_search_input').value = item.nombre;
            document.getElementById('usuario_documentoNumero').value = item.documento_numero;
            document.getElementById('usuario_nombre').value = item.nombre;
            document.getElementById('usuario_cargo').value = item.cargo;
        }
    );

    // --- SECCIÓN: ITEMS ---
    let itemIndex = 0;
    document.getElementById('add-item-btn').addEventListener('click', addNewItem);
    function addNewItem() {
        let template = document.getElementById('item-row-template').innerHTML.replace(/{index}/g, itemIndex++);
        document.getElementById('items-container').insertAdjacentHTML('beforeend', template);
        feather.replace();
    }
    addNewItem();

    document.getElementById('items-container').addEventListener('click', function(e){
        const advancedBtn = e.target.closest('.btn-toggle-advanced');
        if(advancedBtn){
            const wrapper = advancedBtn.closest('.item-row-wrapper');
            const advancedFields = wrapper.querySelector('.item-detalles-avanzados');
            advancedFields.style.display = advancedFields.style.display === 'none' ? 'flex' : 'none';
        }
    });

    document.getElementById('items-container').addEventListener('keyup', function(e) {
        if (e.target && e.target.classList.contains('item-description-input')) {
            const input = e.target;
            const resultsDiv = input.nextElementSibling;
            const row = input.closest('.item-row-wrapper');
            
            clearTimeout(searchTimeout);
            if (input.value.length < 2) { resultsDiv.style.display = 'none'; return; }
            
            searchTimeout = setTimeout(() => {
                const formData = new FormData();
                formData.append('term', input.value);
                formData.append(csrf_token_name, csrf_hash);

                fetch("<?php echo base_url('Facturacion_py/ajax_buscar_py_items'); ?>", { method: 'POST', body: formData })
                .then(res => res.json()).then(data => {
                    resultsDiv.innerHTML = '';
                    data.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'autocomplete-item';
                        div.innerHTML = `(${item.codigo}) ${item.descripcion}`;
                        div.addEventListener('click', () => {
                            console.log(item);
                            input.value = item.descripcion;
                            row.querySelector('.item-codigo').value = item.codigo;
                            row.querySelector('.item-price').value = parseFloat(item.precio_unitario).toFixed(2);
                            row.querySelector('.item-iva-tipo').value = item.iva_tipo;
                            row.querySelector('.item-iva').value = Number(item.iva) || '10';
                            resultsDiv.style.display = 'none';
                            updateTotals();
                        });
                        resultsDiv.appendChild(div);
                    });
                    resultsDiv.style.display = 'block';
                });
            }, 300);
        }
    });

    // --- SECCIÓN: PAGOS Y CUOTAS ---
    let pagoIndex = 0;
    document.getElementById('add-pago-btn').addEventListener('click', addNewPago);

    // Agregar esta función para obtener el total pendiente de pago
    function getTotalPendiente() {
        const totalGeneral = parseFloat(document.getElementById('total_general').textContent) || 0;
        let totalPagado = 0;
        document.querySelectorAll('.pago-monto').forEach(pago => {
            if (pago === document.activeElement) return; // Ignorar el campo actual si está en foco
            totalPagado += parseFloat(pago.value) || 0;
        });
        return Math.max(0, totalGeneral - totalPagado);
    }

    // Modificar la función addNewPago para autocompletar con el total pendiente
    function addNewPago() {
        let template = document.getElementById('pago-row-template').innerHTML.replace(/{index}/g, pagoIndex++);
        document.getElementById('pagos-container').insertAdjacentHTML('beforeend', template);
        
        // Auto-completar con el total pendiente
        const newPagoInput = document.querySelector('.pago-row:last-child .pago-monto');
        if (newPagoInput) {
            newPagoInput.value = getTotalPendiente().toFixed(2);
            updateTotals(); // Actualizar totales después de añadir
        }
        
        feather.replace();
    }

    // Agregar validación al formulario antes de enviar
    document.getElementById('form-factura').addEventListener('submit', function(e) {
        const tipoDocumento = parseInt(document.getElementById('tipoDocumento').value);
        
        // 1. Verificar que hay al menos un item
        if (document.querySelectorAll('.item-row-wrapper').length === 0) {
            e.preventDefault();
            alert('Debe agregar al menos un ítem a la factura.');
            return false;
        }
        
        // 2. Si es nota de crédito/débito, validar campos de documento referencia
        if (tipoDocumento === 5 || tipoDocumento === 6) {
            const cdc = document.getElementById('documento_referencia_cdc').value;
            const fecha = document.getElementById('documento_referencia_fecha').value;
            
            if (!cdc || cdc.length !== 44) {
                e.preventDefault();
                alert('El CDC debe tener 44 caracteres.');
                return false;
            }
            
            if (!fecha) {
                e.preventDefault();
                alert('Debe especificar la fecha del documento de referencia.');
                return false;
            }
        }
        
        // 3. Si es nota de remisión, validar campos específicos
        if (tipoDocumento === 7) {
            const fechaInicio = document.querySelector('input[name="remision[fecha_inicio]"]').value;
            const fechaFin = document.querySelector('input[name="remision[fecha_fin]"]').value;
            const matricula = document.querySelector('input[name="remision[vehiculo_matricula]"]').value;
            
            if (!fechaInicio || !fechaFin) {
                e.preventDefault();
                alert('Debe especificar fechas de inicio y fin del traslado.');
                return false;
            }
            
            if (!matricula) {
                e.preventDefault();
                alert('La matrícula del vehículo es obligatoria.');
                return false;
            }
        }
        
        // 4. Si es autofactura, validar campos específicos
        if (tipoDocumento === 4) {
            const ubicacion = document.querySelector('input[name="autofactura[ubicacion]"]').value;
            
            if (!ubicacion) {
                e.preventDefault();
                alert('Debe especificar la ubicación del vendedor.');
                return false;
            }
        }
        
        // 5. Verificar condición de venta para facturas y notas de débito/crédito
        if ((tipoDocumento === 1 || tipoDocumento === 5 || tipoDocumento === 6) && 
            !document.getElementById('seccion-condicion').classList.contains('seccion-oculta')) {
            
            const condicionTipo = document.getElementById('condicion_tipo').value;
            
            if (condicionTipo == 1) { // Si es contado
                const totalGeneral = parseFloat(document.getElementById('total_general').textContent) || 0;
                const totalPagado = parseFloat(document.getElementById('total_pagado').textContent) || 0;
                
                if (totalPagado < totalGeneral) {
                    e.preventDefault();
                    alert(`El total pagado (${totalPagado.toFixed(2)}) debe ser igual o mayor al total de la factura (${totalGeneral.toFixed(2)})`);
                    return false;
                }
                
                // Verificar que hay al menos una forma de pago
                if (document.querySelectorAll('.pago-row').length === 0) {
                    e.preventDefault();
                    alert('Debe agregar al menos una forma de pago.');
                    return false;
                }
            }
            
            // Si es crédito, verificar que hay al menos una cuota
            if (condicionTipo == 2 && document.querySelectorAll('.cuota-row').length === 0) {
                e.preventDefault();
                alert('Debe agregar al menos una cuota para la condición de crédito.');
                return false;
            }
        }
        
        // Todo está correcto, permitir envío
        return true;
    });

    // Función para cerrar alertas manualmente
    document.querySelectorAll('.btn-close').forEach(function(button) {
        button.addEventListener('click', function() {
            const alert = this.closest('.alert');
            if (alert) {
                alert.style.display = 'none';
            }
        });
    });

    // Auto-cerrar alertas después de 10 segundos
    document.querySelectorAll('.alert').forEach(function(alert) {
        setTimeout(function() {
            if (alert.style.display !== 'none') {
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 300);
            }
        }, 10000);
    });

    // Activar feather icons en las alertas
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // Alternativamente, mostrar errores con SweetAlert
    <?php if ($this->session->flashdata('error_custom')): ?>
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?php echo addslashes($this->session->flashdata('error_custom')); ?>',
                confirmButtonText: 'Entendido'
            });
        }
    <?php endif; ?>

    <?php if ($this->session->flashdata('success')): ?>
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: '<?php echo addslashes($this->session->flashdata('success')); ?>',
                confirmButtonText: 'Perfecto'
            });
        }
    <?php endif; ?>

    <?php if ($this->session->flashdata('warning')): ?>
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Advertencia',
                text: '<?php echo addslashes($this->session->flashdata('warning')); ?>',
                confirmButtonText: 'Entendido'
            });
        }
    <?php endif; ?>

    <?php if ($this->session->flashdata('info')): ?>
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'info',
                title: 'Información',
                text: '<?php echo addslashes($this->session->flashdata('info')); ?>',
                confirmButtonText: 'Entendido'
            });
        }
    <?php endif; ?>

    // Mejorar la función updateTotals para mostrar mensajes de validación en tiempo real
    function updateTotals() {
        let totalGeneral = 0;
        document.querySelectorAll('.item-row-wrapper').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const subtotal = qty * price;
            row.querySelector('.item-subtotal').textContent = subtotal.toFixed(2);
            totalGeneral += subtotal;
        });
        document.getElementById('total_general').textContent = totalGeneral.toFixed(2);

        // Solo actualizar totales de pago si la sección de condición está visible
        if (!document.getElementById('seccion-condicion').classList.contains('seccion-oculta')) {
            let totalPagado = 0;
            document.querySelectorAll('.pago-monto').forEach(pago => {
                totalPagado += parseFloat(pago.value) || 0;
            });
            document.getElementById('total_pagado').textContent = totalPagado.toFixed(2);
            
            const vueltoElement = document.getElementById('vuelto');
            const vuelto = totalPagado - totalGeneral;
            vueltoElement.textContent = (vuelto > 0) ? vuelto.toFixed(2) : '0.00';
            
            // Mostrar advertencia si el pago es insuficiente
            const mensajeValidacion = document.getElementById('mensaje-validacion-pago');
            if (mensajeValidacion) {
                if (totalPagado < totalGeneral) {
                    mensajeValidacion.textContent = `Faltan ${(totalGeneral - totalPagado).toFixed(2)} Gs. por pagar`;
                    mensajeValidacion.className = 'text-danger';
                } else {
                    mensajeValidacion.textContent = 'Pago completo';
                    mensajeValidacion.className = 'text-success';
                }
            }
        }
    }

    document.getElementById('pagos-container').addEventListener('change', function(e) {
        if(e.target.classList.contains('pago-tipo')) {
            const detailsContainer = e.target.closest('.pago-row').querySelector('.pago-detalles-avanzados');
            detailsContainer.innerHTML = '';
            const index = e.target.name.match(/\[(\d+)\]/)[1];
            const tipoPago = e.target.value;

            if (tipoPago == 3 || tipoPago == 4) { // Tarjeta C/D
                detailsContainer.style.display = 'block';
                detailsContainer.innerHTML = `<div class="row g-3"><div class="col-md-4"><label>Procesadora</label><select name="condicion[entregas][${index}][infoTarjeta][tipo]" class="form-control"><?php foreach($tipos_tarjeta_procesadora as $k => $v) echo "<option value='{$k}'>{$v}</option>"; ?></select></div><div class="col-md-4"><label>RUC Procesadora</label><input type="text" name="condicion[entregas][${index}][infoTarjeta][ruc]" class="form-control"></div><div class="col-md-4"><label>Cód. Autorización</label><input type="text" name="condicion[entregas][${index}][infoTarjeta][codigoAutorizacion]" class="form-control"></div></div>`;
            } else if (tipoPago == 2) { // Cheque
                detailsContainer.style.display = 'block';
                detailsContainer.innerHTML = `<div class="row g-3"><div class="col-md-6"><label>N° Cheque</label><input type="text" name="condicion[entregas][${index}][infoCheque][numeroCheque]" class="form-control"></div><div class="col-md-6"><label>Banco</label><input type="text" name="condicion[entregas][${index}][infoCheque][banco]" class="form-control"></div></div>`;
            } else {
                detailsContainer.style.display = 'none';
            }
        }
    });
    
    let cuotaIndex = 0;
    document.getElementById('add-cuota-btn').addEventListener('click', addNewCuota);
    function addNewCuota() {
        let template = document.getElementById('cuota-row-template').innerHTML.replace(/{index}/g, cuotaIndex++);
        document.getElementById('cuotas-container').insertAdjacentHTML('beforeend', template);
        feather.replace();
    }
    
    document.getElementById('credito_nro_cuotas').addEventListener('change', function() {
        document.getElementById('cuotas-container').innerHTML = '';
        const nroCuotas = parseInt(this.value) || 0;
        for (let i = 0; i < nroCuotas; i++) { addNewCuota(); }
    });
    
    document.addEventListener('input', e => {
        if(e.target.matches('.item-qty, .item-price, .pago-monto')) {
            updateTotals();
        }
    });
    
    document.addEventListener('click', e => { 
        const target = e.target.closest('.btn-remove-item, .btn-remove-pago, .btn-remove-cuota');
        if (target) {
            target.closest('.item-row-wrapper, .pago-row, .cuota-row').remove();
            updateTotals();
        }
    });

    // --- SECCIÓN: LÓGICA CONDICIONAL DE VISTA ---
    document.getElementById('condicion_tipo').addEventListener('change', function() {
        if (this.value == 2) { // Crédito
            document.getElementById('credito-fields').style.display = 'block';
            document.getElementById('contado-fields').style.display = 'none';
        } else { // Contado
            document.getElementById('credito-fields').style.display = 'none';
            document.getElementById('contado-fields').style.display = 'block';
        }
    });
    document.getElementById('condicion_tipo').dispatchEvent(new Event('change'));
});
</script>