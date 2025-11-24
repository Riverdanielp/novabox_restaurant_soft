<section class="main-content-wrapper">
<?php
if ($value =$this->session->flashdata('exception')) {

    echo '<section class="content-header px-0"><div class="alert alert-success alert-dismissible fade show"> 
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    <div class="alert-body"><p class="m-0"><i class="m-right fa fa-check"></i>';
    echo escape_output($value);
    echo '</p></div></div></section>';
}
?>

<section class="content-header px-0">
    <div class="row">
        <div class="col-md-12">
            <h2 class="top-left-header"><?php echo lang('customer_due_receives'); ?> </h2>
            <input type="hidden" class="datatable_name" data-title="<?php echo lang('customer_due_receives'); ?>" data-id_name="datatable">
        </div>
    </div>
</section>

<!-- Widgets de Estadísticas -->
<section class="content-header mb-3">
    <div class="row">
        <!-- Widget 1: Total Pagos -->
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white mb-0">Total Pagos</h6>
                            <h2 class="text-white mt-2 mb-0" id="widget_total_payments">$0.00</h2>
                            <small class="text-white-50">Período seleccionado</small>
                        </div>
                        <div>
                            <i class="fa fa-credit-card fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Widget 2: Cantidad de Pagos -->
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white mb-0">Cantidad de Pagos</h6>
                            <h2 class="text-white mt-2 mb-0" id="widget_payment_count">0</h2>
                            <small class="text-white-50">Transacciones</small>
                        </div>
                        <div>
                            <i class="fa fa-chart-bar fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Widget 3: Promedio por Pago -->
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white mb-0">Promedio por Pago</h6>
                            <h2 class="text-white mt-2 mb-0" id="widget_average_payment">$0.00</h2>
                            <small class="text-white-50">Valor promedio</small>
                        </div>
                        <div>
                            <i class="fa fa-calculator fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Widget 4: Pago Máximo -->
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white mb-0">Pago Máximo</h6>
                            <h2 class="text-white mt-2 mb-0" id="widget_max_payment">$0.00</h2>
                            <small class="text-white-50">Mayor transacción</small>
                        </div>
                        <div>
                            <i class="fa fa-money-bill fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Filtros -->
<section class="content-header mb-3">
    <div class="box-wrapper">
        <div class="row">
            <div class="col-md-2">
                <div class="form-group">
                    <label>Fecha Inicio</label>
                    <input type="date" id="filter_date_from" class="form-control" value="<?php echo $filter_date_from; ?>">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Fecha Fin</label>
                    <input type="date" id="filter_date_to" class="form-control" value="<?php echo $filter_date_to; ?>">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Cliente</label>
                    <select id="filter_customer" class="form-control" style="width: 100%;">
                        <option value="">Todos los clientes</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Usuario</label>
                    <select id="filter_user" class="form-control" style="width: 100%;">
                        <option value="">Todos los usuarios</option>
                        <?php foreach($users as $user): ?>
                            <option value="<?php echo $user->id; ?>" <?php echo ($filter_user_id == $user->id) ? 'selected' : ''; ?>>
                                <?php echo escape_output($user->full_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="button" id="btn_apply_filters" class="btn btn-primary form-control">
                        <i class="fa fa-filter"></i> Aplicar Filtros
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

    <div class="box-wrapper">
        
            <div class="table-box">
                <!-- /.box-header -->
                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th class="ir_w_1"> <?php echo lang('sn'); ?></th>
                                <th class="ir_w_10"><?php echo lang('ref_no'); ?></th>
                                <th class="ir_w_10"><?php echo lang('date'); ?></th>
                                <th class="ir_w_10"><?php echo lang('customer'); ?></th>
                                <th class="ir_w_10"><?php echo lang('amount'); ?></th>
                                <th class="ir_w_10"><?php echo lang('payment_method'); ?></th>
                                <th class="ir_w_28"><?php echo lang('note'); ?></th>
                                <th class="ir_w_19"><?php echo lang('added_by'); ?></th>
                                <th class="ir_w_6 not-export-col"><?php echo lang('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($customerDueReceives && !empty($customerDueReceives)) {
                                $i = count($customerDueReceives);
                            }
                            foreach ($customerDueReceives as $value) {
                                // Obtener ventas afectadas por este pago
                                $this->db->select('s.sale_no, s.sale_date, drs.amount');
                                $this->db->from('tbl_customer_due_receives_sales drs');
                                $this->db->join('tbl_sales s', 's.id = drs.sale_id', 'left');
                                $this->db->where('drs.due_receive_id', $value->id);
                                $this->db->order_by('s.sale_date', 'ASC');
                                $affected_sales = $this->db->get()->result();
                                
                                $sales_list = [];
                                foreach ($affected_sales as $sale) {
                                    $sales_list[] = [
                                        'sale_no' => escape_output($sale->sale_no),
                                        'sale_date' => escape_output(date($this->session->userdata('date_format'), strtotime($sale->sale_date))),
                                        'amount' => escape_output(getAmtPCustom($sale->amount))
                                    ];
                                }
                                
                                // Preparamos los datos para el ticket en un array
                                $ticketData = [
                                    'ref_no' => escape_output($value->reference_no),
                                    'date' => escape_output(date($this->session->userdata('date_format'), strtotime($value->only_date))),
                                    'customer' => escape_output(getCustomerName($value->customer_id)),
                                    'amount' => escape_output(getAmtPCustom($value->amount)),
                                    'payment_method' => escape_output(getPaymentName($value->payment_id)),
                                    'note' => escape_output($value->note ?? ''),
                                    'sales' => $sales_list
                                ];
                                ?>
                            <tr>
                                <td><?php echo escape_output($i--); ?></td>
                                <td><?php echo $ticketData['ref_no']; ?></td>
                                <td><?php echo $ticketData['date']; ?></td>
                                <td><?php echo $ticketData['customer']; ?></td>
                                <td><?php echo $ticketData['amount']; ?></td>
                                <td><?php echo $ticketData['payment_method']; ?></td>
                                <td><?php if ($value->note != NULL) echo escape_output($value->note) ?></td>
                                <td><?php echo escape_output(userName($value->user_id)); ?></td>

                                <td>
                                    <div class="btn_group_wrap d-flex">
                                        
                                        <!-- BOTÓN PARA IMPRIMIR TICKET -->
                                        <button type="button" class="btn btn-primary me-2" 
                                                onclick='printTicket(<?php echo json_encode($ticketData); ?>)' 
                                                data-bs-toggle="tooltip" data-bs-placement="top" title="Imprimir Ticket">
                                            <i class="fa fa-print"></i>
                                        </button>
                                        
                                        <a class="delete btn btn-danger" href="<?php echo base_url() ?>Customer_due_receive/deleteCustomerDueReceive/<?php echo escape_output($this->custom->encrypt_decrypt($value->id, 'encrypt')); ?>" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="<?php echo lang('delete'); ?>">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <!-- /.box-body -->
            </div>
      
    </div>
</section>


<?php $this->view('common/footer_js')?>

<!-- Select2 CSS y JS -->
<link href="<?php echo base_url(); ?>assets/bower_components/select2/dist/css/select2.min.css" rel="stylesheet" />
<script src="<?php echo base_url(); ?>assets/bower_components/select2/dist/js/select2.min.js"></script>

<!-- INICIO DEL SCRIPT DE IMPRESIÓN -->
<script>
function printTicket(data) {
    // --- Datos del Outlet (simulados desde PHP a JS) ---
    const outlet = {
        name: '<?php echo escape_output($this->session->userdata("outlet_name")); ?>',
        address: '<?php echo escape_output($this->session->userdata("address")); ?>',
        phone: '<?php echo escape_output($this->session->userdata("phone")); ?>',
        tax_reg_no: '<?php echo escape_output($this->session->userdata("tax_registration_no")); ?>',
        invoice_logo: '<?php $logo = $this->session->userdata("invoice_logo"); echo $logo ? base_url("images/".$logo) : ""; ?>',
        tax_name: '<?php echo isset($identImpuestoName) ? $identImpuestoName : "Tax Reg. No"; ?>'
    };

    // --- HTML del Ticket ---
                    // width: 300px;
    let ticketHTML = `
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Recibo de Pago</title>
            <style>
                body {
                    font-family: 'Courier New', Courier, monospace;
                    margin: 0 auto;
                    font-size: 14px;
                }
                .ticket-header, .ticket-footer {
                    text-align: center;
                }
                .ticket-header img {
                    max-width: 150px;
                    margin-bottom: 10px;
                }
                .ticket-header h3 {
                    margin: 5px 0;
                }
                .ticket-body {
                    margin-top: 20px;
                }
                .ticket-body table {
                    width: 100%;
                    border-collapse: collapse;
                }
                .ticket-body td {
                    padding: 2px 0;
                }
                .ticket-body .label {
                    font-weight: bold;
                }
                .total {
                    font-size: 16px;
                    font-weight: bold;
                    margin-top: 10px;
                }
                .divider {
                    border-top: 1px dashed #000;
                    margin: 10px 0;
                }
            </style>
        </head>
        <body>
            <div class="ticket">
                <header class="ticket-header">
                    ${outlet.invoice_logo ? `<img src="${outlet.invoice_logo}" alt="Logo">` : ''}
                    <h3>${outlet.name}</h3>
                    <p>
                        ${outlet.address}<br>
                        ${outlet.phone}<br>
                        ${outlet.tax_reg_no ? `${outlet.tax_name}: ${outlet.tax_reg_no}` : ''}
                    </p>
                </header>

                <div class="divider"></div>

                <section class="ticket-body">
                    <h4 style="text-align:center;">RECIBO DE PAGO</h4>
                    <table>
                        <tr>
                            <td class="label">Fecha:</td>
                            <td>${data.date}</td>
                        </tr>
                        <tr>
                            <td class="label">Ref No:</td>
                            <td>${data.ref_no}</td>
                        </tr>
                        <tr>
                            <td class="label">Cliente:</td>
                            <td>${data.customer}</td>
                        </tr>
                        <tr>
                            <td class="label">Método:</td>
                            <td>${data.payment_method}</td>
                        </tr>
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

                     ${data.note ? `<div class="divider"></div><p><b>Nota:</b> ${data.note}</p>` : ''}
                </section>

                <div class="divider"></div>

                <footer class="ticket-footer">
                    <p>¡Gracias por su pago!</p>
                </footer>
            </div>
        </body>
        </html>
    `;

    // --- Lógica para abrir la ventana emergente e imprimir ---
    const printWindow = window.open('', 'PRINT', 'height=600,width=400');
    printWindow.document.write(ticketHTML);
    printWindow.document.close();
    printWindow.focus();
    
    // Esperar a que el contenido se cargue completamente (especialmente imágenes)
    printWindow.onload = function() {
        printWindow.print();
        // printWindow.close();
    };
}
</script>
<!-- FIN DEL SCRIPT DE IMPRESIÓN -->

<!-- Script para inicializar Select2 y Widgets -->
<script>
$(document).ready(function() {
    let base_url = $("#base_url_").val();
    console.log('Base URL:', base_url);
    
    // Inicializar Select2 para clientes con opción "Todos" siempre visible
    $('#filter_customer').select2({
        ajax: {
            url: base_url + 'Customer_due_receive/getCustomersWithDue',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term,
                    date_from: $('#filter_date_from').val(),
                    date_to: $('#filter_date_to').val()
                };
            },
            processResults: function(data) {
                // Siempre incluir "Todos los clientes" al inicio
                let results = [{id: '', text: 'Todos los clientes'}];
                if (data.results && data.results.length > 0) {
                    results = results.concat(data.results);
                }
                return { results: results };
            }
        },
        placeholder: 'Todos los clientes',
        allowClear: true,
        minimumResultsForSearch: 0 // Siempre mostrar búsqueda
    });
    
    // Preseleccionar cliente si viene del filtro
    <?php if ($filter_customer_id): ?>
        let customerName = '<?php echo getCustomerName($filter_customer_id); ?>';
        let option = new Option(customerName, '<?php echo $filter_customer_id; ?>', true, true);
        $('#filter_customer').append(option).trigger('change');
    <?php endif; ?>
    
    // Función para actualizar widgets
    function updateWidgets() {
        let dateFrom = $('#filter_date_from').val();
        let dateTo = $('#filter_date_to').val();
        let customerId = $('#filter_customer').val();
        let userId = $('#filter_user').val();
        
        $.ajax({
            url: base_url + 'Customer_due/getPaymentsStatistics',
            type: 'GET',
            data: {
                date_from: dateFrom,
                date_to: dateTo,
                customer_id: customerId || '',
                user_id: userId || ''
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#widget_total_payments').text(response.data.total_payments_formatted);
                    $('#widget_payment_count').text(response.data.payment_count);
                    $('#widget_average_payment').text(response.data.average_payment_formatted);
                    $('#widget_max_payment').text(response.data.max_payment_formatted);
                }
            },
            error: function() {
                $('#widget_total_payments').text('$0.00');
                $('#widget_payment_count').text('0');
                $('#widget_average_payment').text('$0.00');
                $('#widget_max_payment').text('$0.00');
            }
        });
    }
    
    // Botón Aplicar Filtros - RECARGAR PÁGINA con parámetros
    $('#btn_apply_filters').click(function() {
        let dateFrom = $('#filter_date_from').val();
        let dateTo = $('#filter_date_to').val();
        let customerId = $('#filter_customer').val();
        let userId = $('#filter_user').val();
        
        // Construir URL con parámetros GET
        let url = base_url + 'Customer_due_receive/customerDueReceives?';
        url += 'date_from=' + dateFrom;
        url += '&date_to=' + dateTo;
        if (customerId) url += '&customer_id=' + customerId;
        if (userId) url += '&user_id=' + userId;
        
        // Recargar página con filtros
        window.location.href = url;
    });
    
    // Actualizar widgets al cargar
    updateWidgets();
    
    // Actualizar widgets cuando cambian las fechas
    $('#filter_date_from, #filter_date_to').on('change', function() {
        updateWidgets();
    });
    
    // DataTable - verificar si ya está inicializado
    if (!$.fn.DataTable.isDataTable('#datatable')) {
        $('#datatable').DataTable({
            autoWidth: false,
            ordering: true,
            language: {
                lengthMenu: "Mostrar _MENU_ registros por página",
                search: "Buscar:",
                info: "Mostrando _START_ a _END_ de _TOTAL_ pagos",
                infoEmpty: "Mostrando 0 a 0 de 0 pagos",
                zeroRecords: "No se encontraron pagos",
                emptyTable: "No hay pagos registrados",
                loadingRecords: "Cargando...",
                processing: "Procesando..."
            }
        });
    }
});
</script>