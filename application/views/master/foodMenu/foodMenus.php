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
        </div>
    </section>

    <div class="box-wrapper">
        
        <div class="table-box">
            <div class="table-responsive">
                <table id="datatable_sv" class="table">
                    <thead>
                        <tr>
                            <th class="ir_w_1"><?php echo lang('sn'); ?></th>
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
    });
</script>