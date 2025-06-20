<style>
    .img-port {
        max-width: 50px;
        height: 50px;
        object-fit: cover;
        border: 2px solid #d8d8d8;
        border-radius: 5px;
        object-fit: cover;
    }
</style>
 <!-- Agrega estos estilos para los botones -->
<style>
    .btn-activar-balanza {
        background: #28a745; color: white; border: none; margin-right: 5px;
    }
    .btn-desactivar-balanza {
        background: #dc3545; color: white; border: none;
    }
    .check-col { width: 30px; text-align: center; }
</style>
<section class="main-content-wrapper">
    <?php
    if ($this->session->flashdata('exception')) {
        echo '<section class="alert-wrapper"><div class="alert alert-success alert-dismissible fade show"> 
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <div class="alert-body"><p><i class="m-right fa fa-check"></i>';
        echo escape_output($this->session->flashdata('exception')); unset($_SESSION['exception']);
        echo '</p></div></div></section>';
    }
    ?>

    <section class="content-header">
        <div class="row">
            <div class="col-md-auto">
                <h2 class="top-left-header"><?php echo lang('food_menus'); ?> </h2>
                <input type="hidden" class="datatable_name" data-title="<?php echo lang('food_menus'); ?>" data-id_name="datatable">
            </div>
            <div class=" col-md-auto">
                <div class="btn_list2 m-right d-flex">
                    <a data-access="add-234" class="btn bg-blue-btn menu_assign_class me-2" href="<?php echo base_url() ?>foodMenu/addEditFoodMenu">
                        <i data-feather="plus"></i> <?php echo lang('Add'); ?> <?php echo lang('food_menu'); ?>
                    </a>
                </div>
            </div>
            <div class=" col-md-auto">
                <div class="btn_list2 m-right d-flex">
                    <a data-access="upload_food_menu-234" class="btn bg-blue-btn menu_assign_class me-2" href="<?php echo base_url() ?>foodMenu/uploadFoodMenu">
                        <i data-feather="upload"></i> <?php echo lang('upload'); ?>
                    </a>
                </div>
            </div>
            <div class=" col-md-auto">
                <div class="btn_list2 m-right d-flex">
                    <a data-access="upload_food_menu_ingredients-234" class="btn bg-blue-btn menu_assign_class me-2" href="<?php echo base_url() ?>foodMenu/uploadFoodMenuIngredients">
                        <i data-feather="upload-cloud"></i> <?php echo lang('upload_food_menu_ingredients'); ?>
                    </a>
                </div>
            </div>
            <div class=" col-md-auto">
                <div class="btn_list2 m-right d-flex">
                    <a data-access="add-234" class="btn bg-blue-btn menu_assign_class me-2" href="<?php echo base_url() ?>foodMenu/assign">
                        <i data-feather="plus"></i> Menús sin Ingredientes
                    </a>
                </div>
            </div>
            <div class=" col-md-auto">
                <div class="btn_list2 m-right d-flex">
                    <a data-access="item_barcode-234" class="btn bg-blue-btn menu_assign_class" href="<?php echo base_url() ?>foodMenu/foodMenuBarcode">
                        <i class="m-right fa fa-qrcode"></i> <?php echo lang('barcode'); ?>
                    </a>
                </div>
            </div>
            <div class=" col-md-auto">
                <div class="btn_list2 m-right d-flex">
                    <a data-access="item_barcode-234" class="btn bg-blue-btn menu_assign_class" href="<?php echo base_url() ?>foodMenu/conf_rapida">
                    <i data-feather="tool" class="m-right "></i> Conf. Rápida
                    </a>
                </div>
            </div>
            <div class=" col-md-auto">
                <div class="btn_list m-right d-flex">
                    <!-- Filtro de Categoría select2 -->
                    <div class="form-group">
                        <label><?php echo lang('category'); ?> <span class="required_star">*</span></label>
                        <select class="form-control  ir_w_100" id="category_id" name="category_id">
                            <option value=""><?php echo lang('select'); ?></option>
                            <?php foreach ($categories as $ctry) { ?>
                            <option value="<?php echo escape_output($ctry->id) ?>"
                                <?php echo set_select('category_id', $ctry->id); ?>>
                                <?php echo escape_output($ctry->category_name) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class=" col-md-auto">
                <div class="btn_list m-right d-flex">
                    <!-- Botones ocultos por defecto -->
                    <div id="balanza-actions" style="display:none; margin-bottom:10px;">
                        <button class="btn-activar-balanza" id="activarBalanzaBtn">Activar Balanza</button>
                        <button class="btn-desactivar-balanza" id="desactivarBalanzaBtn">Desac. Balanza</button>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <div class="box-wrapper">
        
        <div class="table-box">
            <div class="table-responsive">
                <table id="datatable_sv" class="table">
                    <thead>
                        <tr>
                            <th class="ir_w_1"><?php echo lang('sn'); ?></th>
                            <th class="ir_w_1">
                                <input type="checkbox" id="selectAll">
                            </th>
                            <th class="ir_w_5"><?php echo lang('image'); ?></th>
                            <th class="ir_w_6"><?php echo lang('code'); ?></th>
                            <th class="ir_w_15"><?php echo lang('name'); ?></th>
                            <th class="ir_w_10"><?php echo lang('category'); ?></th>
                            <th class="ir_w_10"><?php echo lang('sale_price'); ?></th>
                            <!-- <th class="ir_w_10"><?php echo lang('alternative_name'); ?></th> -->
                            <th class="ir_w_10"><?php echo lang('description'); ?></th>
                            <th class="ir_w_1 ir_txt_center not-export-col"><?php echo lang('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Modal para detalles del producto -->
<div class="modal fade" id="product_details" aria-hidden="true" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel"></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i data-feather="x"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 show_html_content"> 
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-blue-btn" data-bs-dismiss="modal"><?php echo lang('cancel'); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
    // var TITLE = "Inventory Report " + today;

    });

</script>

<?php $this->view('common/footer_js')?>
<!-- JavaScript para DataTables y funcionalidades -->

<script>
    var base_url = '<?= base_url(); ?>';
    var selectedIds = [];
    // let jqry = $.noConflict();
    
    // if (typeof window.jqry === "undefined") {
    //     window.jqry = $.noConflict(true);
    // }
    // if (typeof window.jqry === "undefined") {
    //     window.jqry = $.noConflict();
    // }
    jqry(document).ready(function() {
        //use for every report view
        let today = new Date();
        let dd = today.getDate();
        let mm = today.getMonth() + 1; //January is 0!
        let yyyy = today.getFullYear();

        if (dd < 10) {
            dd = "0" + dd;
        }

        if (mm < 10) {
            mm = "0" + mm;
        }
        today = yyyy + "-" + mm + "-" + dd;

        //get title and datatable id name from hidden input filed that is before in the table in view page for every datatable
        let datatable_name = $(".datatable_name").attr("data-id_name");
        let title = $(".datatable_name").attr("data-title");
        let TITLE = title + " " +
            "" + today;
        var table_sv = jqry('#datatable_sv').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": base_url + "foodMenu/ajax_list",
                "type": "GET",
                "data": function(d) {
                    d.category_id = jqry('#category_id').val();
                }
            },
            "lengthMenu": [
                [20, 50, 100, 500, -1],
                [20, 50, 100, 500, "Todos"]
            ],
            "columnDefs": [
                {
                    "targets": 1, // primera columna
                    "orderable": false,
                    "searchable": false,
                    "render": function(data, type, row) {
                        // Asume que el ID está en una propiedad, por ejemplo row[1] o row.id
                        // Ajusta según tu estructura de datos
                        var id = row[0]; // o row.id
                        return '<input type="checkbox" class="row-checkbox" value="'+id+'">';
                    }
                },
            ],
            "order": [[0, "desc"]],
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
            "language": {
                "paginate": {
                    "previous": "Anterior",
                    "next": "Siguiente"
                }
            }
        });

        // Delegación por si acaso
        jqry(document).on('change', '#category_id', function() {
            console.log("Category ID changed to: " + jqry(this).val());
            table_sv.ajax.reload(null, true);
        });

        // Si usas select2, puedes agregar esto:
        jqry('#category_id').on('select2:select', function() {
            console.log("Category ID (select2) changed to: " + jqry(this).val());
            table_sv.ajax.reload(null, true);
        });
            
        // Seleccionar todos los checkboxes visibles (solo los de esta página)
        jqry('#selectAll').on('click', function() {
            var rows = table_sv.rows({ page: 'current' }).nodes();
            jqry('input[type="checkbox"].row-checkbox', rows).prop('checked', this.checked).trigger('change');
        });

        // Manejar selección individual
        jqry('#datatable_sv tbody').on('change', '.row-checkbox', function() {
            var id = jqry(this).val();
            if(jqry(this).is(':checked')) {
                if(!selectedIds.includes(id)) selectedIds.push(id);
            } else {
                selectedIds = selectedIds.filter(function(e){return e!==id});
            }
            // Sincroniza el selectAll
            var rows = table_sv.rows({ page: 'current' }).nodes();
            var allChecked = jqry('input[type="checkbox"].row-checkbox', rows).length === jqry('input[type="checkbox"].row-checkbox:checked', rows).length;
            jqry('#selectAll').prop('checked', allChecked);
            showHideBalanzaActions();
        });

        // Cuando se recarga la tabla, hay que volver a marcar los checkboxes seleccionados
        table_sv.on('draw', function(){
            var rows = table_sv.rows({ page: 'current' }).nodes();
            jqry('input[type="checkbox"].row-checkbox', rows).each(function() {
                if(selectedIds.includes(jqry(this).val())){
                    jqry(this).prop('checked', true);
                }
            });
            // Sincroniza selectAll
            var allChecked = jqry('input[type="checkbox"].row-checkbox', rows).length === jqry('input[type="checkbox"].row-checkbox:checked', rows).length;
            jqry('#selectAll').prop('checked', allChecked);
            showHideBalanzaActions();
        });

        function showHideBalanzaActions() {
            if(selectedIds.length > 0) {
                jqry('#balanza-actions').show();
            } else {
                jqry('#balanza-actions').hide();
            }
        }

        // Botón Activar Balanza
        jqry('#activarBalanzaBtn').on('click', function(){
            Swal.fire({
                title: '¿Activar balanza?',
                text: '¿Desea activar balanza para ' + selectedIds.length + ' registros?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, activar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Aquí llamas a tu función o AJAX con selectedIds
                    console.log('Activando balanza para:', selectedIds);
                }
            });
        });

        // Botón Desactivar Balanza
        jqry('#desactivarBalanzaBtn').on('click', function(){
            Swal.fire({
                title: '¿Desactivar balanza?',
                text: '¿Desea desactivar balanza para ' + selectedIds.length + ' registros?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, desactivar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Aquí llamas a tu función o AJAX con selectedIds
                    console.log('Desactivando balanza para:', selectedIds);
                }
            });
        });
    });
</script>