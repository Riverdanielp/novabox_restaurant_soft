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
                <div class="btn_list m-right d-flex">
                    <a data-access="add-234" class="btn bg-blue-btn menu_assign_class me-2" href="<?php echo base_url() ?>foodMenu/addEditFoodMenu">
                        <i data-feather="plus"></i> <?php echo lang('Add'); ?> <?php echo lang('food_menu'); ?>
                    </a>
                    <a data-access="upload_food_menu-234" class="btn bg-blue-btn menu_assign_class me-2" href="<?php echo base_url() ?>foodMenu/uploadFoodMenu">
                        <i data-feather="upload"></i> <?php echo lang('upload'); ?>
                    </a>
                    <a data-access="upload_food_menu_ingredients-234" class="btn bg-blue-btn menu_assign_class me-2" href="<?php echo base_url() ?>foodMenu/uploadFoodMenuIngredients">
                        <i data-feather="upload-cloud"></i> <?php echo lang('upload_food_menu_ingredients'); ?>
                    </a>
                    <a data-access="add-234" class="btn bg-blue-btn menu_assign_class me-2" href="<?php echo base_url() ?>foodMenu/assign">
                        <i data-feather="plus"></i> Menús sin Ingredientes
                    </a>
                    <a data-access="item_barcode-234" class="btn bg-blue-btn menu_assign_class" href="<?php echo base_url() ?>foodMenu/foodMenuBarcode">
                        <i class="m-right fa fa-qrcode"></i> <?php echo lang('barcode'); ?>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="box-wrapper">
        
        <div class="table-box">
            <div class="table-responsive">
                <table id="datatable" class="table">
                    <thead>
                        <tr>
                            <th class="ir_w_1"><?php echo lang('sn'); ?></th>
                            <th class="ir_w_5"><?php echo lang('image'); ?></th>
                            <th class="ir_w_6"><?php echo lang('code'); ?></th>
                            <th class="ir_w_15"><?php echo lang('name'); ?></th>
                            <th class="ir_w_10"><?php echo lang('category'); ?></th>
                            <th class="ir_w_10"><?php echo lang('sale_price'); ?></th>
                            <th class="ir_w_10"><?php echo lang('alternative_name'); ?></th>
                            <th class="ir_w_10"><?php echo lang('added_by'); ?></th>
                            <th class="ir_w_1 ir_txt_center not-export-col"><?php echo lang('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($foodMenus && !empty($foodMenus)) {
                            $i = count($foodMenus);
                            foreach ($foodMenus as $value) {
                                $img_size = "images/".$value->photo;
                                if(file_exists($img_size) && $value->photo!=""){
                                    $image_path = base_url().'images/'.$value->photo;
                                }else{
                                    $image_path = base_url().'images/image_thumb.png';
                                }
                                ?>
                                <tr>
                                    <td class="ir_txt_center"><?php echo $i--; ?></td>
                                    <td>
                                        <img src="<?= $image_path ?>" class="img-port" alt="<?= escape_output($value->name) ?>" >
                                    </td>
                                    <td><?php echo escape_output($value->code) ?></td>
                                    <td><?php echo escape_output($value->name) ?></td>
                                    <td><?php echo escape_output(getFoodMenuCateCodeById($value->category_id)) ?></td>
                                    <td><?php echo escape_output(getAmtPCustom($value->sale_price)) ?></td>
                                    <td><?php echo escape_output(getAlternativeNameById($value->id)) ?></td>
                                    <td><?php echo escape_output(userName($value->user_id)); ?></td>
                                    <td>
                                        <div class="btn_group_wrap">
                                            <a class="btn btn-warning" href="<?php echo base_url() ?>foodMenu/addEditFoodMenu/<?php echo escape_output($this->custom->encrypt_decrypt($value->id, 'encrypt')); ?>" data-bs-toggle="tooltip" data-bs-placement="top"
                                            data-bs-original-title="<?php echo lang('edit'); ?>">
                                                <i class="far fa-edit"></i>
                                            </a>
                                            <a class="delete btn btn-danger" href="<?php echo base_url() ?>foodMenu/deleteFoodMenu/<?php echo escape_output($this->custom->encrypt_decrypt($value->id, 'encrypt')); ?>" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="<?php echo lang('delete'); ?>">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </a>
                                            <a class="btn btn-info" href="<?php echo base_url() ?>foodMenu/assignFoodMenuModifier/<?php echo escape_output($this->custom->encrypt_decrypt($value->id, 'encrypt')); ?>" data-bs-toggle="tooltip" data-bs-placement="top"
                                            data-bs-original-title="<?php echo lang('assign_modifier'); ?>">
                                                <i class="far fa-plus"></i>
                                            </a>
                                            <!-- <a class="btn btn-primary view-details" data-id="<?php echo $value->id; ?>" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="<?php echo lang('view_details'); ?>">
                                                <i class="far fa-eye"></i>
                                            </a> -->
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
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

// $(document).ready(function() {
//     // Inicializar DataTable
//     var table = $('#food-menu-table').DataTable({
//         "columnDefs": [
//             { "orderable": false, "targets": [0, 1, 8] }, // Columnas no ordenables
//             { "searchable": false, "targets": [0, 1, 8] } // Columnas no buscables
//         ],
//         "order": [[2, 'asc']], // Orden inicial por código
//         "language": {
//             "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
//         }
//     });

//     // Filtrado por categoría
//     $('#filter-category').change(function() {
//         var category = $(this).val();
//         if (category === '') {
//             table.columns(4).search('').draw();
//         } else {
//             table.columns(4).search(category).draw();
//         }
//     });

//     // Función para mostrar detalles (reemplaza el call_details original)
//     $(document).on('click', '.view-details', function() {
//         var id = $(this).data('id');
//         $.ajax({
//             url: '<?php echo base_url(); ?>foodMenu/foodMenuDetails/' + id,
//             type: 'GET',
//             dataType: 'html',
//             success: function(response) {
//                 $('#product_details .modal-title').text('<?php echo lang("food_menu_details"); ?>');
//                 $('#product_details .show_html_content').html(response);
//                 $('#product_details').modal('show');
//             },
//             error: function(xhr, status, error) {
//                 alert('Error al cargar los detalles: ' + error);
//             }
//         });
//     });

//     // Reordenar la numeración cuando se filtra o se cambia de página
//     table.on('draw.dt', function() {
//         var info = table.page.info();
//         table.column(0, {search: 'applied', order: 'applied', page: 'applied'}).nodes().each(function(cell, i) {
//             cell.innerHTML = info.start + i + 1;
//         });
//     });
// });
</script>

<?php $this->view('common/footer_js')?>
<!-- JavaScript para DataTables y funcionalidades -->