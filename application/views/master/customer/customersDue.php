<section class="main-content-wrapper">

    <?php
    if ($this->session->flashdata('exception')) {
        echo '<section class="alert-wrapper">
            <div class="alert alert-success alert-dismissible fade show"> 
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <div class="alert-body">
        <p><i class="m-right fa fa-check"></i>';
        echo escape_output($this->session->flashdata('exception'));unset($_SESSION['exception']);
        echo '</p></div></div></section>';
    }
    ?>

    <section class="content-header">
        <div class="row">
            <div class="col-md-12">
                <h2 class="top-left-header"><?php echo lang('customers'); ?> - Deudas Pendientes</h2>
                <input type="hidden" class="datatable_name" data-title="<?php echo lang('customers'); ?> Deudas" data-id_name="datatable">
            </div>
        </div>
    </section>

    <!-- Widgets de Estadísticas -->
    <section class="content-header mb-3">
        <div class="row">
            <!-- Widget 1: Clientes con Deuda -->
            <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white mb-0">Clientes con Deuda</h6>
                                <h2 class="text-white mt-2 mb-0">
                                    <?php echo number_format($due_stats['total_customers_with_due'], 0, ',', '.'); ?>
                                </h2>
                                <small class="text-white-50">
                                    <?php echo number_format($due_stats['percentage_with_due'], 0); ?>% del total
                                </small>
                            </div>
                            <div>
                                <i class="fa fa-users fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Widget 2: Deuda Total -->
            <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white mb-0">Deuda Total</h6>
                                <h2 class="text-white mt-2 mb-0">
                                    <?php echo getAmtCustom($due_stats['total_due_amount']); ?>
                                </h2>
                                <small class="text-white-50">Acumulado pendiente</small>
                            </div>
                            <div>
                                <i class="fa fa-money-bill-wave fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Widget 3: Promedio de Deuda -->
            <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white mb-0">Promedio por Cliente</h6>
                                <h2 class="text-white mt-2 mb-0">
                                    <?php echo getAmtCustom($due_stats['average_due']); ?>
                                </h2>
                                <small class="text-white-50">Deuda promedio</small>
                            </div>
                            <div>
                                <i class="fa fa-chart-line fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Widget 4: Deuda Máxima -->
            <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white mb-0">Deuda Máxima</h6>
                                <h2 class="text-white mt-2 mb-0">
                                    <?php echo getAmtCustom($due_stats['max_due_amount']); ?>
                                </h2>
                                <small class="text-white-50">Mayor deuda individual</small>
                            </div>
                            <div>
                                <i class="fa fa-exclamation-triangle fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tabla de Clientes con Deuda -->
    <div class="box-wrapper">
        <div class="table-box">
            <div class="table-responsive">
                <?php $is_loyalty_enable = $this->session->userdata('is_loyalty_enable');?>
                <table id="datatable" class="table">
                    <thead>
                        <tr>
                            <th><?php echo lang('sn'); ?></th>
                            <th><?php echo lang('customer_name'); ?></th>
                            <th><?php echo lang('phone'); ?></th>
                            <th><?php echo lang('email'); ?></th>
                            <th><?php echo lang('address'); ?></th>
                            <th><?php echo lang('current_due'); ?></th>
                            <th>Último Pago</th>
                            <?php if(isset($is_loyalty_enable) && $is_loyalty_enable=="enable"):?>
                                <th><?php echo lang('is_loyalty_enable'); ?></th>
                            <?php endif;?>
                            <th><?php echo lang('added_by'); ?></th>
                            <th><?php echo lang('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

</section>

<!-- Modal de Registro de Pago -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="paymentModalLabel">
                    <i class="fa fa-dollar-sign"></i> Registrar Pago de Deuda
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="paymentForm">
                    <input type="hidden" id="modal_customer_id" name="customer_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label><strong>Cliente:</strong></label>
                                <p id="modal_customer_name" class="form-control-plaintext"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label><strong>Deuda Actual:</strong></label>
                                <p id="modal_customer_due" class="form-control-plaintext text-danger fw-bold"></p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Monto a Pagar <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="modal_total_amount" name="amount" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Método de Pago <span class="text-danger">*</span></label>
                                <select class="form-control" id="modal_payment_id" name="payment_id" required>
                                    <option value="">Seleccionar...</option>
                                    <?php foreach (getAllPaymentMethods(5) as $value) { ?>
                                        <option value="<?php echo escape_output($value->id) ?>">
                                            <?php echo escape_output($value->name) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Cuenta Bancaria</label>
                                <select class="form-control" id="modal_account_id" name="account_id">
                                    <option value="">Caja Abierta (Predeterminado)</option>
                                    <?php
                                    if (isset($accounts) && !empty($accounts)) {
                                        foreach ($accounts as $account) {
                                            echo '<option value="' . escape_output($account->id) . '">' . escape_output($account->account_name) . ' (' . escape_output($account->account_type) . ')</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Nota</label>
                                <textarea class="form-control" name="note" rows="1"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Sección de Ventas Pendientes -->
                    <div class="row" id="modal_pending_sales_section" style="display:none;">
                        <div class="col-md-12">
                            <h6 class="border-bottom pb-2 mb-3">Ventas Pendientes</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-sm" id="modal_pending_sales_table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No. Venta</th>
                                            <th>Fecha</th>
                                            <th class="text-end">Total</th>
                                            <th class="text-end">Deuda</th>
                                            <th class="text-end">Ya Pagado</th>
                                            <th class="text-end">Saldo</th>
                                            <th style="width: 150px;" class="text-end">Abonar</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modal_pending_sales_body">
                                    </tbody>
                                </table>
                            </div>
                            <div class="alert alert-info mt-2">
                                <small><i class="fa fa-info-circle"></i> El monto se distribuye automáticamente entre las ventas. Puede modificar manualmente cada abono.</small>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnSavePayment">
                    <i class="fa fa-save"></i> Guardar Pago
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Historial de Pagos -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="historyModalLabel">
                    <i class="fa fa-history"></i> Historial de Pagos
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6 id="history_customer_name" class="mb-3"></h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="historyTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Ref. No.</th>
                                <th>Fecha</th>
                                <th>Monto</th>
                                <th>Método</th>
                                <th>Nota</th>
                                <th>Usuario</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="historyTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/jquery.dataTables.min.js"></script>
<script src="<?php echo base_url(); ?>assets/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/dataTables.bootstrap4.min.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/dataTables.buttons.min.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/buttons.html5.min.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/buttons.print.min.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/jszip.min.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/pdfmake.min.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/vfs_fonts.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/newDesign/js/forTable.js"></script>

<style>
    .card {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }
    .opacity-50 {
        opacity: 0.5;
    }
    .bg-primary {
        background-color: #007bff !important;
    }
    .bg-danger {
        background-color: #dc3545 !important;
    }
    .bg-warning {
        background-color: #ffc107 !important;
    }
    .bg-info {
        background-color: #17a2b8 !important;
    }
</style>

<script>
    "use strict";
    let base_url = $("#base_url_").val();
    let is_loyalty_enable = "<?php echo $is_loyalty_enable; ?>";
    
    $(document).ready(function(){
        let columnDefs = [
            { orderable: true, targets: [5] }, // Columna de deuda ordenable
            { orderable: false, targets: [6] } // Columna de "Último Pago" no ordenable
        ];
        
        // Configurar columna de acciones según si hay loyalty habilitado
        if (is_loyalty_enable === "enable") {
            columnDefs.push({ orderable: false, targets: [9] }); // Columna de acciones cuando hay loyalty
        } else {
            columnDefs.push({ orderable: false, targets: [8] }); // Columna de acciones sin loyalty
        }
        
        $("#datatable").DataTable({
            autoWidth: false,
            ordering: true,
            processing: true,
            serverSide: true,
            order: [[5, "desc"]], // Ordenar por deuda DESC por defecto
            lengthMenu: [
                [10, 20, 50, 100, 200, 300, 500, -1],
                [10, 20, 50, 100, 200, 300, 500, "Todos"]
            ],
            pageLength: 20, // Mostrar más registros por defecto
            ajax: {
                url: base_url + "Customer_due/getAjaxDataDue",
                type: "POST",
                dataType: "json",
                data: {},
            },
            columnDefs: columnDefs,
            dom: '<"top-left-item col-sm-12 col-md-6"lf> <"top-right-item col-sm-12 col-md-6"B> t <"bottom-left-item col-sm-12 col-md-6 "i><"bottom-right-item col-sm-12 col-md-6 "p>',
            buttons: [
                { extend: 'print', text: '<i class="fa-solid fa-print"></i> Print', titleAttr: 'print' },
                { extend: 'copyHtml5', text: '<i class="fa-solid fa-copy"></i> Copy', titleAttr: 'Copy' },
                { extend: 'excelHtml5', text: '<i class="fa-solid fa-file-excel"></i> Excel', titleAttr: 'Excel' },
                { extend: 'csvHtml5', text: '<i class="fa-solid fa-file-csv"></i> CSV', titleAttr: 'CSV' },
                { extend: 'pdfHtml5', text: '<i class="fa-solid fa-file-pdf"></i> PDF', titleAttr: 'PDF' }
            ],
            language: {
                paginate: {
                    previous: "Anterior",
                    next: "Siguiente",
                },
                lengthMenu: "Mostrar _MENU_ registros por página",
                search: "Buscar:",
                info: "Mostrando _START_ a _END_ de _TOTAL_ clientes con deuda",
                infoEmpty: "Mostrando 0 a 0 de 0 clientes",
                infoFiltered: "(filtrado de _MAX_ clientes totales)",
                zeroRecords: "No se encontraron clientes con deuda",
                emptyTable: "No hay clientes con deuda registrados",
                loadingRecords: "Cargando...",
                processing: "Procesando..."
            },
        });
    });
    
    // ========== MODAL DE PAGO ==========
    let modalPendingSales = [];
    
    // Abrir modal de pago
    $(document).on('click', '.btn-payment-modal', function() {
        let customerId = $(this).data('customer-id');
        let customerName = $(this).data('customer-name');
        
        $('#modal_customer_id').val(customerId);
        $('#modal_customer_name').text(customerName);
        $('#paymentForm')[0].reset();
        $('#modal_customer_id').val(customerId);
        modalPendingSales = [];
        
        // Cargar ventas pendientes
        $.ajax({
            url: base_url + 'Customer_due_receive/getPendingSales',
            type: 'GET',
            data: { customer_id: customerId },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' && response.sales.length > 0) {
                    modalPendingSales = response.sales;
                    renderModalPendingSales();
                    calculateModalTotalAmount();
                    $('#modal_pending_sales_section').show();
                    
                    // Calcular deuda total
                    let totalDue = 0;
                    $.each(modalPendingSales, function(i, sale) {
                        totalDue += parseFloat(sale.remaining_due);
                    });
                    $('#modal_customer_due').text('$' + totalDue.toFixed(2));
                } else {
                    $('#modal_pending_sales_section').hide();
                    $('#modal_customer_due').text('$0.00');
                }
            }
        });
        
        $('#paymentModal').modal('show');
    });
    
    // Renderizar ventas en modal
    function renderModalPendingSales() {
        let html = '';
        $.each(modalPendingSales, function(index, sale) {
            html += '<tr>';
            html += '<td><strong>' + sale.sale_no + '</strong></td>';
            html += '<td>' + sale.sale_date + '</td>';
            html += '<td class="text-end">' + formatCurrency(sale.total_payable) + '</td>';
            html += '<td class="text-end">' + formatCurrency(sale.due_amount) + '</td>';
            html += '<td class="text-end">' + formatCurrency(sale.paid_due_amount) + '</td>';
            html += '<td class="text-end"><strong>' + sale.remaining_due_formatted + '</strong></td>';
            html += '<td class="text-end">';
            html += '<input type="number" class="form-control form-control-sm sale-payment-input-modal text-end" ';
            html += 'data-sale-id="' + sale.id + '" ';
            html += 'data-remaining="' + sale.remaining_due + '" ';
            html += 'name="sales_details[' + sale.id + ']" ';
            html += 'value="' + sale.remaining_due + '" ';
            html += 'min="0" max="' + sale.remaining_due + '" step="0.01">';
            html += '</td>';
            html += '</tr>';
        });
        $('#modal_pending_sales_body').html(html);
    }
    
    function calculateModalTotalAmount() {
        let total = 0;
        $('.sale-payment-input-modal').each(function() {
            let value = parseFloat($(this).val()) || 0;
            total += value;
        });
        $('#modal_total_amount').val(total.toFixed(2));
    }
    
    function formatCurrency(value) {
        return parseFloat(value).toFixed(2);
    }
    
    // Cuando cambia un input individual
    $(document).on('input', '.sale-payment-input-modal', function() {
        let input = $(this);
        let value = parseFloat(input.val()) || 0;
        let maxValue = parseFloat(input.attr('max'));
        
        if (value > maxValue) {
            input.val(maxValue);
            alert('El monto no puede exceder el saldo restante');
        }
        
        calculateModalTotalAmount();
    });
    
    // Cuando cambia el total
    $(document).on('input', '#modal_total_amount', function() {
        let totalAmount = parseFloat($(this).val()) || 0;
        
        if (modalPendingSales.length === 0) return;
        
        let remainingToDistribute = totalAmount;
        
        $('.sale-payment-input-modal').each(function() {
            let input = $(this);
            let maxValue = parseFloat(input.attr('max'));
            
            if (remainingToDistribute <= 0) {
                input.val(0);
            } else if (remainingToDistribute >= maxValue) {
                input.val(maxValue);
                remainingToDistribute -= maxValue;
            } else {
                input.val(remainingToDistribute.toFixed(2));
                remainingToDistribute = 0;
            }
        });
    });
    
    // Guardar pago
    $('#btnSavePayment').click(function() {
        let formData = $('#paymentForm').serialize();
        
        if (!$('#modal_customer_id').val() || !$('#modal_total_amount').val() || !$('#modal_payment_id').val()) {
            alert('Por favor complete todos los campos obligatorios');
            return;
        }
        
        $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');
        
        $.ajax({
            url: base_url + 'Customer_due/saveCustomerDueReceive',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    alert('Pago registrado correctamente: ' + response.reference_no);
                    $('#paymentModal').modal('hide');
                    $('#datatable').DataTable().ajax.reload();
                    
                    // Mostrar historial después de guardar
                    setTimeout(function() {
                        $('.btn-history-modal[data-customer-id="' + $('#modal_customer_id').val() + '"]').click();
                    }, 500);
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error al guardar el pago');
            },
            complete: function() {
                $('#btnSavePayment').prop('disabled', false).html('<i class="fa fa-save"></i> Guardar Pago');
            }
        });
    });
    
    // ========== MODAL DE HISTORIAL ==========
    $(document).on('click', '.btn-history-modal', function() {
        let customerId = $(this).data('customer-id');
        let customerName = $(this).data('customer-name');
        
        $('#history_customer_name').html('<strong>Cliente:</strong> ' + customerName);
        $('#historyTableBody').html('<tr><td colspan="8" class="text-center">Cargando...</td></tr>');
        
        $.ajax({
            url: base_url + 'Customer_due/getPaymentHistory',
            type: 'GET',
            data: { customer_id: customerId },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' && response.data.length > 0) {
                    let html = '';
                    $.each(response.data, function(index, payment) {
                        html += '<tr>';
                        html += '<td>' + (index + 1) + '</td>';
                        html += '<td>' + payment.reference_no + '</td>';
                        html += '<td>' + payment.date + '</td>';
                        html += '<td>' + payment.amount + '</td>';
                        html += '<td>' + payment.payment_method + '</td>';
                        html += '<td>' + (payment.note || '-') + '</td>';
                        html += '<td>' + payment.user_name + '</td>';
                        html += '<td>';
                        html += '<button class="btn btn-primary btn-sm btn-print-ticket" data-payment-id="' + payment.id + '">';
                        html += '<i class="fa fa-print"></i></button>';
                        html += '</td>';
                        html += '</tr>';
                    });
                    $('#historyTableBody').html(html);
                } else {
                    $('#historyTableBody').html('<tr><td colspan="8" class="text-center">No hay pagos registrados</td></tr>');
                }
            }
        });
        
        $('#historyModal').modal('show');
    });
    
    // Imprimir ticket desde historial
    $(document).on('click', '.btn-print-ticket', function() {
        let paymentId = $(this).data('payment-id');
        
        $.ajax({
            url: base_url + 'Customer_due/getPaymentDetail',
            type: 'GET',
            data: { payment_id: paymentId },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    printTicket(response.data);
                }
            }
        });
    });
    
    // Función de impresión de ticket (reutilizada)
    function printTicket(data) {
        const outlet = {
            name: '<?php echo escape_output($this->session->userdata("outlet_name")); ?>',
            address: '<?php echo escape_output($this->session->userdata("address")); ?>',
            phone: '<?php echo escape_output($this->session->userdata("phone")); ?>',
            tax_reg_no: '<?php echo escape_output($this->session->userdata("tax_registration_no")); ?>',
            invoice_logo: '<?php $logo = $this->session->userdata("invoice_logo"); echo $logo ? base_url("images/".$logo) : ""; ?>',
            tax_name: 'Tax Reg. No'
        };

        let ticketHTML = `
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <title>Recibo de Pago</title>
                <style>
                    body { font-family: 'Courier New', Courier, monospace; margin: 0 auto; font-size: 14px; }
                    .ticket-header, .ticket-footer { text-align: center; }
                    .ticket-header img { max-width: 150px; margin-bottom: 10px; }
                    .ticket-header h3 { margin: 5px 0; }
                    .ticket-body { margin-top: 20px; }
                    .ticket-body table { width: 100%; border-collapse: collapse; }
                    .ticket-body td { padding: 2px 0; }
                    .ticket-body .label { font-weight: bold; }
                    .total { font-size: 16px; font-weight: bold; margin-top: 10px; }
                    .divider { border-top: 1px dashed #000; margin: 10px 0; }
                </style>
            </head>
            <body>
                <div class="ticket">
                    <header class="ticket-header">
                        ${outlet.invoice_logo ? '<img src="' + outlet.invoice_logo + '" alt="Logo">' : ''}
                        <h3>${outlet.name}</h3>
                        <p>${outlet.address}<br>${outlet.phone}<br>
                        ${outlet.tax_reg_no ? outlet.tax_name + ': ' + outlet.tax_reg_no : ''}</p>
                    </header>
                    <div class="divider"></div>
                    <section class="ticket-body">
                        <h4 style="text-align:center;">RECIBO DE PAGO</h4>
                        <table>
                            <tr><td class="label">Fecha:</td><td>${data.date}</td></tr>
                            <tr><td class="label">Ref No:</td><td>${data.ref_no}</td></tr>
                            <tr><td class="label">Cliente:</td><td>${data.customer}</td></tr>
                            <tr><td class="label">Método:</td><td>${data.payment_method}</td></tr>
                        </table>
                        <div class="divider"></div>
                        <table>
                            <tr>
                                <td class="label total">TOTAL PAGADO:</td>
                                <td class="total" style="text-align:right;">${data.amount}</td>
                            </tr>
                        </table>
                        ${data.sales && data.sales.length > 0 ? `
                            <div class="divider"></div>
                            <h5 style="text-align:center; margin: 10px 0;">VENTAS ABONADAS</h5>
                            <table style="font-size: 12px;">
                                <thead>
                                    <tr>
                                        <td class="label">Venta</td>
                                        <td class="label">Fecha</td>
                                        <td class="label" style="text-align:right;">Abonado</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.sales.map(sale => `
                                        <tr>
                                            <td>${sale.sale_no}</td>
                                            <td>${sale.sale_date}</td>
                                            <td style="text-align:right;">${sale.amount}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        ` : ''}
                        ${data.note ? '<div class="divider"></div><p><b>Nota:</b> ' + data.note + '</p>' : ''}
                    </section>
                    <div class="divider"></div>
                    <footer class="ticket-footer">
                        <p>¡Gracias por su pago!</p>
                    </footer>
                </div>
            </body>
            </html>
        `;

        const printWindow = window.open('', 'PRINT', 'height=600,width=400');
        printWindow.document.write(ticketHTML);
        printWindow.document.close();
        printWindow.focus();
        printWindow.onload = function() {
            printWindow.print();
        };
    }
</script>
