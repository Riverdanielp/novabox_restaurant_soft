<link rel="stylesheet" href="<?php echo base_url(); ?>assets/dist/css/custom/foodMenuSales.css">

<section class="main-content-wrapper">

    <section class="content-header">
        <h3 class="text-left top-left-header"><?php echo lang('food_sales_report'); ?></h3>

        <input type="hidden" class="datatable_name" data-title="<?php echo lang('food_sales_report'); ?>" data-id_name="datatable">
       
    </section>
    
    <div class="my-3">
        <?php
        if(isLMni() && isset($outlet_id)):
                            ?>
                            <h4> <?php echo lang('outlet'); ?>: <?php echo escape_output(getOutletNameById($outlet_id))?></h4>
                            <?php
        endif;
        ?>
        <h4>
            <?= isset($start_date) && $start_date && isset($end_date) && $end_date ? lang('date').": " . date($this->session->userdata('date_format'), strtotime($start_date)) . " - " . date($this->session->userdata('date_format'), strtotime($end_date)) : '' ?><?= isset($start_date) && $start_date && !$end_date ? lang('date').": " . date($this->session->userdata('date_format'), strtotime($start_date)) : '' ?><?= isset($end_date) && $end_date && !$start_date ? lang('date').": " . date($this->session->userdata('date_format'), strtotime($end_date)) : '' ?>
        </h4>
    </div>

    <div class="box-wrapper">
        <div class="table-box">
            <div class="row">
                <!-- 1. Fechas -->
                <div class="mb-3 col-md-4 col-lg-2 col-sm-12">
                    <?php echo form_open(base_url() . 'Report/foodMenuSales', array('id' => 'foodMenuSales', 'method' => 'get')) ?>
                    <div class="form-group">
                        <input tabindex="1" type="datetime-local" id="startDate" name="startDate"
                            class="form-control"
                            placeholder="<?php echo lang('start_date'); ?>"
                            value="<?php
                                if (isset($start_date) && $start_date) {
                                    echo str_replace(' ', 'T', substr($start_date, 0, 16));
                                }
                            ?>">
                    </div>
                </div>
                <div class="mb-3 col-md-4 col-lg-2 col-sm-12">
                    <div class="form-group">
                        <input tabindex="2" type="datetime-local" id="endDate" name="endDate"
                            class="form-control"
                            placeholder="<?php echo lang('end_date'); ?>"
                            value="<?php
                                if (isset($end_date) && $end_date) {
                                    echo str_replace(' ', 'T', substr($end_date, 0, 16));
                                }
                            ?>">
                    </div>
                </div>
                
                <!-- 2. Sucursales -->
                <?php if(isLMni()): ?>
                <div class="mb-3 col-md-4 col-lg-2 col-sm-12">
                    <div class="form-group">
                        <select tabindex="3" class="form-control select2 ir_w_100" id="outlet_id" name="outlet_id" onchange="loadKitchensByOutlet()">
                            <option value="">Todas las sucursales</option>
                            <?php
                                $outlets = getAllOutlestByAssign();
                                foreach ($outlets as $value):
                            ?>
                                <option value="<?php echo escape_output($value->id) ?>" <?php echo (isset($outlet_id) && $outlet_id == $value->id) ? 'selected' : ''; ?>>
                                    <?php echo escape_output($value->outlet_name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- 3. Cocinas (dinámicas según sucursal) -->
                <div class="mb-3 col-md-4 col-lg-2 col-sm-12">
                    <div class="form-group">
                        <select tabindex="4" class="form-control select2 ir_w_100" id="kitchen_filter" name="kitchen_filter">
                            <option value="">Todos los productos</option>
                            <option value="all_kitchens" <?php echo (isset($kitchen_filter) && $kitchen_filter == 'all_kitchens') ? "selected" : ""; ?>>Todas las cocinas</option>
                            <option value="no_kitchen" <?php echo (isset($kitchen_filter) && $kitchen_filter == 'no_kitchen') ? "selected" : ""; ?>>Productos sin cocina</option>
                            <!-- Las cocinas específicas se cargarán dinámicamente -->
                        </select>
                    </div>
                </div>
                
                <!-- 4. Métodos de pago -->
                <div class="mb-3 col-md-4 col-lg-2 col-sm-12">
                    <div class="form-group">
                        <select tabindex="5" class="form-control select2 ir_w_100" id="payment_method_id" name="payment_method_id">
                            <option value="">Todos los métodos de pago</option>
                            <?php
                                if (isset($payment_methods) && $payment_methods):
                                    foreach ($payment_methods as $value):
                            ?>
                                <option value="<?php echo escape_output($value->id) ?>" <?php echo (isset($payment_method_id) && $payment_method_id == $value->id) ? "selected" : ""; ?>>
                                    <?php echo escape_output($value->name) ?>
                                </option>
                            <?php 
                                    endforeach; 
                                endif;
                            ?>
                        </select>
                    </div>
                </div>

                
                <div class="col-sm-12 mb-3 col-md-4 col-lg-2">
                    <div class="form-group">
                        <!-- <label for="customer_search"><?php echo lang('customer'); ?></label> -->
                        <select id="customer_search" name="customer" class="form-control select2 ir_w_100">
                            <option value="all">Todos</option>
                        </select>
                    </div>
                </div>

                <div class="col-sm-12 col-md-4 col-lg-2">
                    <div class="form-group">
                        <button type="submit" name="submit" value="submit"
                            class="btn bg-blue-btn w-100"><?php echo lang('submit'); ?></button>
                    </div>
                </div>
                <!-- Recuerda cerrar el form donde corresponde -->
            </div>
        </div>
            <div class="table-box">
                <!-- /.box-header -->
                <div class="table-responsive">
                    
                    <table id="datatable" class="table">
                        <thead>
                            <tr>
                                <th class="ir_w2_txt_center"><?php echo lang('sn'); ?></th>
                                <!-- <th><?php echo lang('code'); ?></th> -->
                                <th><?php echo lang('food_menu'); ?>(<?php echo lang('code'); ?>)</th>
                                <th><?php echo lang('category'); ?></th>
                                <th class="sorting"><?php echo lang('price'); ?></th>
                                <th class="sorting"><?php echo lang('quantity'); ?></th>
                                <th class="sorting">Valor Total</th>
                                <th><?php echo lang('code'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (isset($foodMenuSales)):
                                foreach ($foodMenuSales as $key => $value) {
                                    $key++;
                                    ?>
                            <tr>
                                <td class="ir_txt_center"><?php echo escape_output($key); ?></td>
                                <!-- <td><?php echo escape_output($value->code) ?></td> -->
                                <td><?php echo escape_output($value->menu_name) ?></td>
                                <td><?php echo escape_output($value->category_name) ?></td>
                                <td><?php echo getAmtPCustom($value->menu_unit_price) ?></td>
                                <td data-order="<?php echo str_pad(intval($value->totalQty), 10, '0', STR_PAD_LEFT); ?>" style="text-align: right;"><?php echo ($value->totalQty) ?></td>
                                <td data-order="<?php echo str_pad(intval($value->totalPrice), 10, '0', STR_PAD_LEFT); ?>" style="text-align: right;"><?php echo getAmtPCustom($value->totalPrice) ?></td>
                                <td><?php echo escape_output($value->code) ?></td>
                            </tr>
                            <?php
                                }
                            endif;
                            ?>
                        </tbody>
                    </table>
                </div>
                <!-- /.box-body -->
            </div>
        </div>
    </div>

  
</section>

<!-- DataTables -->
<script src="<?php echo base_url(); ?>assets/datatable_custom/jquery-3.3.1.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/jquery.dataTables.min.js"></script>
<script src="<?php echo base_url(); ?>assets/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js">
</script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/dataTables.bootstrap4.min.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/dataTables.buttons.min.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/buttons.html5.min.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/buttons.print.min.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/jszip.min.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/pdfmake.min.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/vfs_fonts.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/newDesign/js/forTable.js"></script>

<script src="<?php echo base_url(); ?>frequent_changing/js/custom_report.js"></script>

<script>
// Función para cargar cocinas según la sucursal seleccionada
function loadKitchensByOutlet() {
    var outlet_id = $('#outlet_id').val();
    var kitchen_filter = $('#kitchen_filter');
    var selected_kitchen = kitchen_filter.val(); // Guardar selección actual
    
    // Limpiar opciones específicas de cocinas pero mantener las opciones globales
    kitchen_filter.find('option').each(function() {
        if ($(this).val() != '' && $(this).val() != 'all_kitchens' && $(this).val() != 'no_kitchen') {
            $(this).remove();
        }
    });
    
    if (outlet_id) {
        // Cargar cocinas de la sucursal específica
        $.ajax({
            url: '<?php echo base_url(); ?>Report/getKitchensByOutlet',
            type: 'POST',
            data: { outlet_id: outlet_id },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.kitchens) {
                    $.each(response.kitchens, function(index, kitchen) {
                        var selected = (selected_kitchen == kitchen.id) ? 'selected' : '';
                        kitchen_filter.append('<option value="' + kitchen.id + '" ' + selected + '>' + kitchen.name + '</option>');
                    });
                } else {
                    console.error('Error en respuesta del servidor:', response.message || 'Sin mensaje de error');
                    alert('Error al cargar cocinas: ' + (response.message || 'Error desconocido'));
                }
                // Refrescar select2 si está siendo usado
                if (kitchen_filter.hasClass('select2')) {
                    kitchen_filter.trigger('change');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX al cargar cocinas:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
                alert('Error de conexión al cargar cocinas. Revisa la consola para más detalles.');
            }
        });
    }
    // Si no hay sucursal seleccionada (todas las sucursales), no agregamos cocinas específicas
}

// Configuración específica para foodMenuSales con reinicialización forzada
$(document).ready(function() {
    // Cargar cocinas inicial si ya hay una sucursal seleccionada
    if ($('#outlet_id').val()) {
        loadKitchensByOutlet();
    }
    
    // Esperar un poco para que otros scripts se ejecuten primero
    setTimeout(function() {
        // Destruir cualquier DataTable existente
        if ($.fn.DataTable.isDataTable('#datatable')) {
            $('#datatable').DataTable().destroy();
        }
        
        let startDate = $('#startDate').val();
        let endDate = $('#endDate').val();
        let rango = '';
        
        function formatDatetimeLocalForTitle(dt) {
            if (!dt) return '';
            let [date, time] = dt.split('T');
            if (!date || !time) return dt;
            return date + ' ' + time;
        }
        
        if (startDate && endDate) {
            rango = ' | ' + formatDatetimeLocalForTitle(startDate) + ' - ' + formatDatetimeLocalForTitle(endDate);
        } else if (startDate && !endDate) {
            rango = ' | ' + formatDatetimeLocalForTitle(startDate);
        } else if (endDate && !startDate) {
            rango = ' | ' + formatDatetimeLocalForTitle(endDate);
        }
        
        let title = "<?php echo lang('food_sales_report'); ?>";
        let TITLE = title + rango;
        
        // Recrear DataTable con configuración específica
        $('#datatable').DataTable({
            order: [[4, "desc"]], // Columna 4 = Cantidad (SN=0, Código=1, Menú=2, Categoría=3, Cantidad=4)
            ordering: true, // Forzar que el ordenamiento esté habilitado
            columnDefs: [
                {
                    targets: 4, // Columna Cantidad
                    type: 'string', // Usar string para que respete el data-order
                    orderable: true, // Forzar que sea ordenable
                    orderSequence: ['desc', 'asc'] // Permitir desc y asc
                },
                {
                    targets: '_all', // Todas las demás columnas
                    orderable: true // Asegurar que todas sean ordenables
                }
            ],
            dom: '<"top-left-item col-sm-12 col-md-6"lf> <"top-right-item col-sm-12 col-md-6"B> t <"bottom-left-item col-sm-12 col-md-6 "i><"bottom-right-item col-sm-12 col-md-6 "p>',
            buttons: [
                {
                    extend: "print",
                    title: TITLE,
                    text: '<i class="fa-solid fa-print"></i> Print',
                    titleAttr: "print",
                },
                {
                    extend: "copyHtml5",
                    title: TITLE,
                    text: '<i class="fa-solid fa-copy"></i> Copy',
                    titleAttr: "Copy",
                },
                {
                    extend: "excelHtml5",
                    title: TITLE,
                    text: '<i class="fa-solid fa-file-excel"></i> Excel',
                    titleAttr: "Excel",
                },
                {
                    extend: "csvHtml5",
                    title: TITLE,
                    text: '<i class="fa-solid fa-file-csv"></i> CSV',
                    titleAttr: "CSV",
                },
                {
                    extend: "pdfHtml5",
                    title: TITLE,
                    text: '<i class="fa-solid fa-file-pdf"></i> PDF',
                    titleAttr: "PDF",
                },
            ],
            language: {
                paginate: {
                    previous: "Previous",
                    next: "Next",
                },
            },
        });
        
        // Debug: Verificar que los clicks van a la columna correcta
        $('#datatable thead th').each(function(index) {
            console.log('Columna ' + index + ': ' + $(this).text());
        });
        
        // Forzar que la columna 4 sea clickeable
        $('#datatable thead th:eq(4)').removeClass('sorting_disabled').addClass('sorting');
        
        // Agregar event listener manual si es necesario
        $('#datatable thead th:eq(4)').off('click').on('click', function() {
            var table = $('#datatable').DataTable();
            var currentOrder = table.order()[0];
            if (currentOrder[0] == 4) {
                // Si ya está ordenando por esta columna, cambiar dirección
                var newDirection = currentOrder[1] === 'desc' ? 'asc' : 'desc';
                table.order([4, newDirection]).draw();
            } else {
                // Si no, ordenar por esta columna descendente
                table.order([4, 'desc']).draw();
            }
        });
        
    }, 500); // Esperar 500ms
});
</script>
<script>
var base_url = "<?php echo base_url(); ?>";

$(document).ready(function() {
    var selectedCustomer = "<?php echo isset($customer) ? $customer : 'all'; ?>";
    var selectedCustomerText = "<?php echo isset($customer_text) ? addslashes($customer_text) : 'Todos'; ?>";

    // Limpia el select y setea la opción "Todos"
    $('#customer_search').empty();
    $('#customer_search').append('<option value="all">Todos</option>');

    // Si hay cliente seleccionado distinto de "all", agrégalo manualmente como opción seleccionada
    if (selectedCustomer !== "all" && selectedCustomer) {
        // Evita duplicados: si es el mismo texto que "Todos" no lo agregues
        if (selectedCustomerText !== "Todos") {
            $('#customer_search').append('<option value="' + selectedCustomer + '" selected>' + selectedCustomerText + '</option>');
            $('#customer_search').val(selectedCustomer); // Selecciona ese cliente
        }
    } else {
        $('#customer_search').val('all');
    }

    // Inicializa select2
    $('#customer_search').select2({
        placeholder: "Seleccione o Agregue un Cliente",
        allowClear: true,
        minimumInputLength: 1,
        ajax: {
            url: base_url + 'Report/search_customers',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                let results = [{ id: 'all', text: 'Todos' }];
                results = results.concat(data);
                return { results: results };
            },
            cache: true
        },
        templateResult: function(data) {
            if (data.id === 'all') {
                return $('<span style="font-weight:bold;">' + data.text + '</span>');
            }
            return data.text;
        },
        templateSelection: function(data) {
            return data.text || data.id;
        }
    });

    // Si no hay cliente seleccionado, selecciona "Todos"
    if (!selectedCustomer || selectedCustomer === "all") {
        $('#customer_search').val('all').trigger('change');
    }
});
</script>