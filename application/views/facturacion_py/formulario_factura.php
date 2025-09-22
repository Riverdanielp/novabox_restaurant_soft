<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Emisión de Factura Electrónica</h4>
                    </div>
                    <div class="card-body">
                        <form id="factura-form" method="post" action="<?= base_url('factura/emitir') ?>">
                            
                            <!-- Tipo de Documento -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Tipo de Documento</label>
                                        <select class="form-control" name="tipo_documento" id="tipo_documento" required>
                                            <option value="1" selected>Factura Electrónica</option>
                                            <option value="4">Autofactura Electrónica</option>
                                            <option value="5">Nota de Crédito Electrónica</option>
                                            <option value="6">Nota de Débito Electrónica</option>
                                            <option value="7">Nota de Remisión Electrónica</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Datos Generales -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Fecha de Emisión</label>
                                        <input type="datetime-local" class="form-control" name="fecha" id="fecha" value="<?= date('Y-m-d\TH:i:s') ?>" required>
                                        <small class="text-muted">Formato: YYYY-MM-DDTHH:MM:SS</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Sucursal</label>
                                        <select class="form-control" name="sucursal_id" id="sucursal_id" required>
                                            <?php foreach (fs_get_sucursales() as $sucursal): ?>
                                            <option value="<?= $sucursal->id ?>"><?= $sucursal->nombre ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Punto de Expedición</label>
                                        <select class="form-control" name="punto_expedicion_id" id="punto_expedicion_id" required>
                                            <option value="">Seleccione la sucursal primero</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Moneda</label>
                                        <select class="form-control" name="moneda" id="moneda" required>
                                            <option value="PYG" selected>Guaraní (PYG)</option>
                                            <option value="USD">Dólar Americano (USD)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Cambio (si aplica)</label>
                                        <input type="number" class="form-control" name="cambio" id="cambio" value="0" step="0.01">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Datos del Cliente -->
                            <div class="card mt-4">
                                <div class="card-header bg-light">
                                    <h5>Datos del Cliente</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Tipo de Cliente</label>
                                                <select class="form-control" name="cliente[es_contribuyente]" id="cliente_contribuyente" required>
                                                    <option value="0">No Contribuyente</option>
                                                    <option value="1">Contribuyente</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>RUC/Documento</label>
                                                <input type="text" class="form-control" name="cliente[ruc]" id="cliente_ruc">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Nombre/Razón Social</label>
                                                <input type="text" class="form-control" name="cliente[nombre]" id="cliente_nombre" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Nombre Fantasía</label>
                                                <input type="text" class="form-control" name="cliente[nombre_fantasia]" id="cliente_nombre_fantasia">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Email</label>
                                                <input type="email" class="form-control" name="cliente[email]" id="cliente_email">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Dirección</label>
                                                <input type="text" class="form-control" name="cliente[direccion]" id="cliente_direccion">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Número Casa</label>
                                                <input type="text" class="form-control" name="cliente[numero_casa]" id="cliente_numero_casa" value="0">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Teléfono</label>
                                                <input type="text" class="form-control" name="cliente[telefono]" id="cliente_telefono">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Departamento</label>
                                                <select class="form-control" name="cliente[departamento_id]" id="cliente_departamento_id">
                                                    <?php foreach (fs_get_departamentos() as $departamento): ?>
                                                    <option value="<?= $departamento->id ?>" <?= $departamento->id == fs_default_departamento() ? 'selected' : '' ?>><?= $departamento->nombre ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Distrito</label>
                                                <select class="form-control" name="cliente[distrito_id]" id="cliente_distrito_id">
                                                    <option value="">Seleccione un departamento</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Ciudad</label>
                                                <select class="form-control" name="cliente[ciudad_id]" id="cliente_ciudad_id">
                                                    <option value="">Seleccione un distrito</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>País</label>
                                                <select class="form-control" name="cliente[pais_codigo]" id="cliente_pais_codigo">
                                                    <option value="PRY" selected>Paraguay</option>
                                                    <option value="ARG">Argentina</option>
                                                    <option value="BRA">Brasil</option>
                                                    <!-- Otros países aquí -->
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Código de Cliente</label>
                                                <input type="text" class="form-control" name="cliente[id_sistema]" id="cliente_id_sistema" value="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Autofactura - Solo visible cuando el tipo es 4 -->
                            <div class="card mt-4" id="autofactura_section" style="display:none;">
                                <div class="card-header bg-light">
                                    <h5>Datos de Autofactura</h5>
                                </div>
                                <div class="card-body">
                                    <!-- Campos específicos de autofactura -->
                                </div>
                            </div>
                            
                            <!-- Items de la Factura -->
                            <div class="card mt-4">
                                <div class="card-header bg-light d-flex justify-content-between">
                                    <h5>Items de la Factura</h5>
                                    <button type="button" class="btn btn-sm btn-primary" id="add-item">Agregar Item</button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="items-table">
                                            <thead>
                                                <tr>
                                                    <th>Código</th>
                                                    <th>Descripción</th>
                                                    <th>Cantidad</th>
                                                    <th>Precio Unitario</th>
                                                    <th>% IVA</th>
                                                    <th>IVA Base %</th>
                                                    <th>Subtotal</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr id="item-row-template" style="display:none;">
                                                    <td>
                                                        <input type="text" class="form-control item-codigo" name="items[__INDEX__][codigo]" required>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control item-descripcion" name="items[__INDEX__][descripcion]" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control item-cantidad" name="items[__INDEX__][cantidad]" value="1" min="0.001" step="0.001" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control item-precio" name="items[__INDEX__][precio_unitario]" value="0" min="0" step="1" required>
                                                    </td>
                                                    <td>
                                                        <select class="form-control item-iva" name="items[__INDEX__][iva]" required>
                                                            <option value="10">10%</option>
                                                            <option value="5">5%</option>
                                                            <option value="0">Exenta</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control item-iva-base" name="items[__INDEX__][ivaBase]" value="100" min="0" max="100" required>
                                                    </td>
                                                    <td>
                                                        <span class="item-subtotal">0</span>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-danger remove-item">Eliminar</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="6" class="text-right"><strong>Total IVA 5%:</strong></td>
                                                    <td colspan="2" id="total-iva5">0</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="6" class="text-right"><strong>Total IVA 10%:</strong></td>
                                                    <td colspan="2" id="total-iva10">0</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="6" class="text-right"><strong>Total Exento:</strong></td>
                                                    <td colspan="2" id="total-exento">0</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="6" class="text-right"><strong>Total General:</strong></td>
                                                    <td colspan="2" id="total-general">0</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Condición de Venta -->
                            <div class="card mt-4">
                                <div class="card-header bg-light">
                                    <h5>Condición de Venta</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Tipo de Condición</label>
                                                <select class="form-control" name="condicion_venta[tipo]" id="condicion_tipo" required>
                                                    <option value="1" selected>Contado</option>
                                                    <option value="2">Crédito</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Medios de Pago (Contado) -->
                                    <div id="medios-pago-contado">
                                        <div class="row">
                                            <div class="col-12">
                                                <h6>Medios de Pago</h6>
                                                <button type="button" class="btn btn-sm btn-primary mb-3" id="add-payment">Agregar Medio de Pago</button>
                                            </div>
                                        </div>
                                        <div id="payments-container">
                                            <!-- Aquí se agregarán dinámicamente los medios de pago -->
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <strong>Total a Pagar:</strong>
                                                <span id="total-a-pagar">0</span>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Total Pagado:</strong>
                                                <span id="total-pagado">0</span>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-12">
                                                <div class="alert alert-warning" id="payment-warning" style="display:none;">
                                                    El total pagado debe ser igual al total a pagar.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Datos de Crédito -->
                                    <div id="datos-credito" style="display:none;">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Tipo de Crédito</label>
                                                    <select class="form-control" name="condicion_venta[credito][tipo]" id="credito_tipo">
                                                        <option value="1">Con plazo</option>
                                                        <option value="2">Con cuotas</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4" id="plazo-container">
                                                <div class="form-group">
                                                    <label>Plazo</label>
                                                    <input type="text" class="form-control" name="condicion_venta[credito][plazo]" id="credito_plazo" value="30 días">
                                                </div>
                                            </div>
                                            <div class="col-md-4" id="cuotas-container" style="display:none;">
                                                <div class="form-group">
                                                    <label>Número de Cuotas</label>
                                                    <input type="number" class="form-control" name="condicion_venta[credito][cuotas]" id="credito_cuotas" min="1" value="1">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div id="entrega-inicial-container">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Monto de Entrega Inicial</label>
                                                        <input type="number" class="form-control" name="condicion_venta[credito][montoEntrega]" id="credito_monto_entrega" min="0" value="0">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <button type="button" class="btn btn-primary mt-4" id="agregar-entrega">Agregar Medio de Pago para Entrega</button>
                                                </div>
                                            </div>
                                            <div id="entregas-container">
                                                <!-- Aquí se agregarán dinámicamente las entregas -->
                                            </div>
                                        </div>
                                        
                                        <div id="cuotas-detalle-container" style="display:none;">
                                            <h6 class="mt-3">Detalle de Cuotas</h6>
                                            <button type="button" class="btn btn-sm btn-primary mb-3" id="generate-cuotas">Generar Cuotas</button>
                                            <div class="table-responsive">
                                                <table class="table table-bordered" id="cuotas-table">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Moneda</th>
                                                            <th>Monto</th>
                                                            <th>Vencimiento</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <!-- Aquí se generarán las cuotas -->
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Botones de Acción -->
                            <div class="row mt-4">
                                <div class="col-md-12 text-center">
                                    <button type="submit" class="btn btn-primary" id="submit-btn">Emitir Factura</button>
                                    <button type="button" class="btn btn-secondary" onclick="window.history.back();">Cancelar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Template para medios de pago -->
<div id="payment-template" style="display:none;">
    <div class="payment-row row mb-3">
        <div class="col-md-3">
            <div class="form-group">
                <label>Tipo de Pago</label>
                <select class="form-control payment-type" name="condicion_venta[entregas][__INDEX__][tipo]" required>
                    <option value="1">Efectivo</option>
                    <option value="2">Cheque</option>
                    <option value="3">Tarjeta</option>
                    <option value="9">Otros</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Moneda</label>
                <select class="form-control payment-currency" name="condicion_venta[entregas][__INDEX__][moneda]" required>
                    <option value="PYG" selected>Guaraní (PYG)</option>
                    <option value="USD">Dólar Americano (USD)</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Monto</label>
                <input type="number" class="form-control payment-amount" name="condicion_venta[entregas][__INDEX__][monto]" required min="0">
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label>Cambio</label>
                <input type="number" class="form-control payment-exchange" name="condicion_venta[entregas][__INDEX__][cambio]" value="0" step="0.01">
            </div>
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-danger btn-sm remove-payment" style="margin-top: 30px;">
                <i class="fa fa-trash"></i>
            </button>
        </div>
        
        <!-- Campos adicionales para cheque -->
        <div class="col-md-12 payment-check-fields" style="display:none;">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Número de Cheque</label>
                        <input type="text" class="form-control" name="condicion_venta[entregas][__INDEX__][infoCheque][numeroCheque]">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Banco</label>
                        <input type="text" class="form-control" name="condicion_venta[entregas][__INDEX__][infoCheque][banco]">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Campos adicionales para tarjeta -->
        <div class="col-md-12 payment-card-fields" style="display:none;">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Número de Tarjeta</label>
                        <input type="text" class="form-control" name="condicion_venta[entregas][__INDEX__][infoTarjeta][numero]">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tipo de Tarjeta</label>
                        <select class="form-control" name="condicion_venta[entregas][__INDEX__][infoTarjeta][tipo]">
                            <option value="1">Dinelco</option>
                            <option value="2">Visa</option>
                            <option value="3">Mastercard</option>
                            <option value="4">American Express</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Titular</label>
                        <input type="text" class="form-control" name="condicion_venta[entregas][__INDEX__][infoTarjeta][titular]">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Código Autorización</label>
                        <input type="text" class="form-control" name="condicion_venta[entregas][__INDEX__][infoTarjeta][codigoAutorizacion]">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para la funcionalidad del formulario -->
<script>
$(document).ready(function() {
    let itemCounter = 0;
    let paymentCounter = 0;
    
    // Cambiar visibilidad de secciones según tipo de documento
    $('#tipo_documento').change(function() {
        const tipoDoc = $(this).val();
        if (tipoDoc === '4') { // Autofactura
            $('#autofactura_section').show();
        } else {
            $('#autofactura_section').hide();
        }
    });
    
    // Cargar distritos al cambiar departamento
    $('#cliente_departamento_id').change(function() {
        const deptoId = $(this).val();
        if (!deptoId) return;
        
        $.ajax({
            url: '<?= base_url("factura/get_distritos") ?>',
            type: 'GET',
            data: { departamento_id: deptoId },
            dataType: 'json',
            success: function(data) {
                let options = '<option value="">Seleccione un distrito</option>';
                $.each(data, function(i, item) {
                    options += `<option value="${item.id}">${item.nombre}</option>`;
                });
                $('#cliente_distrito_id').html(options);
                $('#cliente_ciudad_id').html('<option value="">Seleccione un distrito</option>');
            }
        });
    });
    
    // Cargar ciudades al cambiar distrito
    $('#cliente_distrito_id').change(function() {
        const distritoId = $(this).val();
        if (!distritoId) return;
        
        $.ajax({
            url: '<?= base_url("factura/get_ciudades") ?>',
            type: 'GET',
            data: { distrito_id: distritoId },
            dataType: 'json',
            success: function(data) {
                let options = '<option value="">Seleccione una ciudad</option>';
                $.each(data, function(i, item) {
                    options += `<option value="${item.id}">${item.nombre}</option>`;
                });
                $('#cliente_ciudad_id').html(options);
            }
        });
    });
    
    // Cargar puntos de expedición al cambiar sucursal
    $('#sucursal_id').change(function() {
        const sucursalId = $(this).val();
        if (!sucursalId) return;
        
        $.ajax({
            url: '<?= base_url("factura/get_puntos_expedicion") ?>',
            type: 'GET',
            data: { sucursal_id: sucursalId },
            dataType: 'json',
            success: function(data) {
                let options = '<option value="">Seleccione un punto de expedición</option>';
                $.each(data, function(i, item) {
                    options += `<option value="${item.id}">${item.nombre} (${item.codigo_punto})</option>`;
                });
                $('#punto_expedicion_id').html(options);
            }
        });
    });
    
    // Agregar nuevo item
    $('#add-item').click(function() {
        const newRow = $('#item-row-template').clone();
        newRow.attr('id', '').css('display', '');
        
        // Reemplazar __INDEX__ con el contador actual
        newRow.html(newRow.html().replace(/__INDEX__/g, itemCounter));
        
        // Agregar el nuevo item a la tabla
        $('#items-table tbody').append(newRow);
        
        // Asociar eventos a los campos del nuevo item
        bindItemEvents(newRow);
        
        itemCounter++;
        updateTotals();
    });
    
    // Eliminar item
    $(document).on('click', '.remove-item', function() {
        $(this).closest('tr').remove();
        updateTotals();
    });
    
    // Cambiar entre contado y crédito
    $('#condicion_tipo').change(function() {
        const tipo = $(this).val();
        if (tipo === '1') { // Contado
            $('#medios-pago-contado').show();
            $('#datos-credito').hide();
        } else { // Crédito
            $('#medios-pago-contado').hide();
            $('#datos-credito').show();
        }
    });
    
    // Cambiar entre tipo de crédito
    $('#credito_tipo').change(function() {
        const tipo = $(this).val();
        if (tipo === '1') { // Con plazo
            $('#plazo-container').show();
            $('#cuotas-container, #cuotas-detalle-container').hide();
        } else { // Con cuotas
            $('#plazo-container').hide();
            $('#cuotas-container, #cuotas-detalle-container').show();
        }
    });
    
    // Agregar medio de pago
    $('#add-payment').click(function() {
        const newPayment = $('#payment-template').children().clone();
        
        // Reemplazar __INDEX__ con el contador actual
        newPayment.html(newPayment.html().replace(/__INDEX__/g, paymentCounter));
        
        // Agregar el nuevo medio de pago al contenedor
        $('#payments-container').append(newPayment);
        
        // Asociar eventos a los campos del nuevo medio de pago
        bindPaymentEvents(newPayment);
        
        paymentCounter++;
        updatePaymentTotals();
    });
    
    // Eliminar medio de pago
    $(document).on('click', '.remove-payment', function() {
        $(this).closest('.payment-row').remove();
        updatePaymentTotals();
    });
    
    // Generar cuotas
    $('#generate-cuotas').click(function() {
        const numCuotas = parseInt($('#credito_cuotas').val()) || 1;
        const totalGeneral = parseFloat($('#total-general').text()) || 0;
        const montoEntrega = parseFloat($('#credito_monto_entrega').val()) || 0;
        const montoRestante = totalGeneral - montoEntrega;
        
        if (montoRestante <= 0 || numCuotas <= 0) {
            alert('Verifique el monto de entrega y el número de cuotas');
            return;
        }
        
        const montoPorCuota = Math.round(montoRestante / numCuotas);
        let html = '';
        
        for (let i = 0; i < numCuotas; i++) {
            const vencimiento = new Date();
            vencimiento.setMonth(vencimiento.getMonth() + i + 1);
            const fechaVencimiento = vencimiento.toISOString().split('T')[0];
            
            html += `
                <tr>
                    <td>${i + 1}</td>
                    <td>
                        <select class="form-control" name="condicion_venta[credito][infoCuotas][${i}][moneda]">
                            <option value="PYG" selected>PYG</option>
                            <option value="USD">USD</option>
                        </select>
                    </td>
                    <td>
                        <input type="number" class="form-control" name="condicion_venta[credito][infoCuotas][${i}][monto]" value="${montoPorCuota}">
                    </td>
                    <td>
                        <input type="date" class="form-control" name="condicion_venta[credito][infoCuotas][${i}][vencimiento]" value="${fechaVencimiento}">
                    </td>
                </tr>
            `;
        }
        
        $('#cuotas-table tbody').html(html);
    });
    
    // Validación del formulario
    $('#factura-form').submit(function(e) {
        // Verificar que hay al menos un item
        if ($('#items-table tbody tr:visible').length === 0) {
            e.preventDefault();
            alert('Debe agregar al menos un item a la factura');
            return false;
        }
        
        // Verificar condición de venta
        const tipoCondicion = $('#condicion_tipo').val();
        
        if (tipoCondicion === '1') { // Contado
            // Verificar que hay al menos un medio de pago
            if ($('#payments-container .payment-row').length === 0) {
                e.preventDefault();
                alert('Debe agregar al menos un medio de pago');
                return false;
            }
            
            // Verificar que el total pagado es igual al total a pagar
            const totalAPagar = parseFloat($('#total-a-pagar').text()) || 0;
            const totalPagado = parseFloat($('#total-pagado').text()) || 0;
            
            if (Math.abs(totalAPagar - totalPagado) > 0.01) {
                e.preventDefault();
                alert('El total pagado debe ser igual al total a pagar');
                return false;
            }
        } else { // Crédito
            const tipoCreditoInt = parseInt($('#credito_tipo').val());
            
            if (tipoCreditoInt === 2) { // Con cuotas
                // Verificar que se generaron las cuotas
                if ($('#cuotas-table tbody tr').length === 0) {
                    e.preventDefault();
                    alert('Debe generar las cuotas del crédito');
                    return false;
                }
            }
        }
        
        // Formatear fecha según el formato requerido
        const fechaInput = $('#fecha');
        const fechaValue = fechaInput.val();
        
        // Asegurarse que la fecha tiene segundos
        if (fechaValue && !fechaValue.match(/T\d{2}:\d{2}:\d{2}$/)) {
            let newDate = fechaValue;
            if (newDate.match(/T\d{2}:\d{2}$/)) {
                newDate += ':00';
            } else if (!newDate.includes('T')) {
                newDate += 'T00:00:00';
            }
            fechaInput.val(newDate);
        }
        
        // Continuar con el envío
        return true;
    });
    
    // Agregar el primer item al cargar la página
    $('#add-item').click();
    $('#add-payment').click();
    
    // Funciones auxiliares
    function bindItemEvents(row) {
        row.find('.item-cantidad, .item-precio, .item-iva').change(function() {
            updateItemSubtotal(row);
            updateTotals();
        });
        
        row.find('.item-iva').change(function() {
            const ivaValue = $(this).val();
            const ivaBaseInput = row.find('.item-iva-base');
            
            // Si es exenta (0%), el ivaBase debe ser 0
            if (ivaValue === '0') {
                ivaBaseInput.val(0);
                ivaBaseInput.prop('readonly', true);
            } else {
                if (ivaBaseInput.val() === '0') {
                    ivaBaseInput.val(100);
                }
                ivaBaseInput.prop('readonly', false);
            }
        });
    }
    
    function bindPaymentEvents(paymentRow) {
        // Cambiar campos visibles según tipo de pago
        paymentRow.find('.payment-type').change(function() {
            const type = $(this).val();
            const checkFields = paymentRow.find('.payment-check-fields');
            const cardFields = paymentRow.find('.payment-card-fields');
            
            checkFields.hide();
            cardFields.hide();
            
            if (type === '2') { // Cheque
                checkFields.show();
            } else if (type === '3') { // Tarjeta
                cardFields.show();
            }
        });
        
        // Actualizar totales al cambiar monto
        paymentRow.find('.payment-amount').change(function() {
            updatePaymentTotals();
        });
    }
    
    function updateItemSubtotal(row) {
        const cantidad = parseFloat(row.find('.item-cantidad').val()) || 0;
        const precio = parseFloat(row.find('.item-precio').val()) || 0;
        const subtotal = cantidad * precio;
        
        row.find('.item-subtotal').text(subtotal.toLocaleString('es-PY'));
    }
    
    function updateTotals() {
        let total = 0;
        let totalIva5 = 0;
        let totalIva10 = 0;
        let totalExento = 0;
        
        $('#items-table tbody tr:visible').each(function() {
            const cantidad = parseFloat($(this).find('.item-cantidad').val()) || 0;
            const precio = parseFloat($(this).find('.item-precio').val()) || 0;
            const iva = parseInt($(this).find('.item-iva').val()) || 0;
            const ivaBase = parseInt($(this).find('.item-iva-base').val()) || 0;
            
            const subtotal = cantidad * precio;
            const baseImponible = subtotal * (ivaBase / 100);
            
            total += subtotal;
            
            if (iva === 5) {
                totalIva5 += baseImponible * 0.05;
            } else if (iva === 10) {
                totalIva10 += baseImponible * 0.10;
            } else {
                totalExento += subtotal;
            }
        });
        
        $('#total-iva5').text(totalIva5.toLocaleString('es-PY'));
        $('#total-iva10').text(totalIva10.toLocaleString('es-PY'));
        $('#total-exento').text(totalExento.toLocaleString('es-PY'));
        $('#total-general').text(total.toLocaleString('es-PY'));
        $('#total-a-pagar').text(total.toLocaleString('es-PY'));
        
        updatePaymentTotals();
    }
    
    function updatePaymentTotals() {
        let totalPagado = 0;
        
        $('#payments-container .payment-row').each(function() {
            const monto = parseFloat($(this).find('.payment-amount').val()) || 0;
            totalPagado += monto;
        });
        
        $('#total-pagado').text(totalPagado.toLocaleString('es-PY'));
        
        // Mostrar advertencia si los totales no coinciden
        const totalAPagar = parseFloat($('#total-general').text().replace(/\./g, '').replace(',', '.')) || 0;
        
        if (Math.abs(totalAPagar - totalPagado) > 0.01) {
            $('#payment-warning').show();
            $('#submit-btn').prop('disabled', true);
        } else {
            $('#payment-warning').hide();
            $('#submit-btn').prop('disabled', false);
        }
    }
});
</script>