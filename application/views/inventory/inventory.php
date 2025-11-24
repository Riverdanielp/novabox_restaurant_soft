<link rel="stylesheet" href="<?php echo base_url(); ?>assets/dist/css/custom/inventory.css">
<section class="main-content-wrapper">

    <section class="content-header">
        <div class="row">
            <div class="col-sm-12 mb-3 col-md-6">
                <h3 class="top-left-header"><?php echo lang('inventory'); ?> </h3>
            </div>
            <div class="col-sm-12 mb-2 col-md-3">
                <button type="button" class="btn bg-blue-btn" id="printTicketBtn">
                    Imprimir ticket
                </button>

            </div>
            <div class="col-sm-12 mb-2 col-md-3">
                <strong class="margin_10" id="stockValue"></strong>
            </div>
        </div>
    </section>
 
    <div class="box-wrapper">

        <div class="table-box">
            <!-- /.box-header -->
            <div class="table-responsive">
                <input type="hidden" class="datatable_name" data-filter="yes" data-title="<?php echo lang('inventory'); ?>" data-id_name="datatable">
                <table id="datatable" class="table">
                    <thead>
                    <tr>
                        <th class="title" class="ir_w_5">#</th>
                        <th class="title" class="ir_w_5"><?php echo lang('code'); ?></th>
                        <th class="title" class="ir_w_37">
                            <?php echo lang('ingredient'); ?></th>
                        <th class="title" class="ir_w_20"><?php echo lang('category'); ?></th>
                        <th class="title" class="ir_w_20"><?php echo lang('stock_qty_amount'); ?></th>
                        <th class="title" class="ir_w_20"><?php echo lang('alert_qty_amount'); ?></th>
                        <th class="title" class="ir_w_10"><?php echo lang('actions'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $totalStock = 0;
                    $grandTotal = 0;
                    $alertCount = 0;
         
                    if (!empty($inventory) && isset($inventory)):
                        foreach ($inventory as $key => $value):
                            $conversion_rate = (float)$value->conversion_rate?$value->conversion_rate:1;
                            if($value->id):
                                $totalStock = ($value->total_purchase*$value->conversion_rate)  - $value->total_consumption - $value->total_modifiers_consumption - $value->total_waste + $value->total_consumption_plus - $value->total_consumption_minus + ($value->total_transfer_plus*$value->conversion_rate) - ($value->total_transfer_minus*$value->conversion_rate)  +  ($value->total_transfer_plus_2*$value->conversion_rate) -  ($value->total_transfer_minus_2*$value->conversion_rate)+ ($value->total_production*$value->conversion_rate);
                                $last_purchase_price = getLastPurchaseAmount($value->id);

                                if($value->conversion_rate==0 || $value->conversion_rate==''){
                                    $total_sale_unit = isset($value->conversion_rate) && (float)$value->conversion_rate?(float)($totalStock/1):'0';
                                }else{
                                    $total_sale_unit = isset($value->conversion_rate) && (float)$value->conversion_rate?(float)($totalStock/$value->conversion_rate):'0';
                                }

                                $total_stock_in_float = ((float)(((float)$total_sale_unit).".".((float)$totalStock%$conversion_rate)));
                                if ($totalStock >= 0) {
                                    $grandTotal += ($total_stock_in_float*$last_purchase_price);
                                }

                                $key++;

                                ?>
                                <tr>
                                    <td class="ir_txt_center"><?php echo escape_output($key); ?></td>
                                    <td class="ir_txt_center"><?php echo escape_output($value->code); ?></td>
                                    <td><?= escape_output($value->name) ?></td>
                                    <td><?php echo escape_output($value->category_name); ?></td>
                                    <?php if(($value->ing_type=="Plain Ingredient" && $value->is_direct_food!=2) && $value->conversion_rate!=1):?>
                                            <td style="<?= ($totalStock <= ($value->alert_quantity*$value->conversion_rate)) ? 'color:red' : '' ?>">
                                                 <?php echo floatval($total_sale_unit); ?><?php echo " " . $value->unit_name2 ?> <span><?php echo ($totalStock) ? floatval($totalStock%$conversion_rate) : getAmtP(0) ?><?php echo " " . escape_output($value->unit_name)?></span>
                                            </td>
                                    <?php else:
                                        $stock_float = (float)($total_sale_unit + (($totalStock) ? ($totalStock%$conversion_rate) : (0)));
                                        ?>
                                        <td style="<?= ($totalStock <= ($value->alert_quantity*$value->conversion_rate)) ? 'color:red' : '' ?>">
                                           <?php echo round(floatval($stock_float),2) ?> <?php echo " " . escape_output($value->unit_name)?>
                                        </td>
                                    <?php
                                    endif
                                    ?>
                                    <td><?= escape_output(getAmtP($value->alert_quantity) . " ") ?>
                                        <?php if($value->ing_type=="Plain Ingredient" && $value->is_direct_food!=2  && $value->conversion_rate!=1):?>
                                            <?php echo " " . $value->unit_name2 ?>
                                        <?php else:
                                            ?>
                                            <?= " " . escape_output($value->unit_name)?>
                                            <?php
                                        endif
                                        ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info open-modal-btn" data-ingredient-id="<?php echo $value->id; ?>">Ver Detalles</button>
                                        <a href="<?php echo base_url(); ?>Inventory/ingredientSalesHistory/<?php echo $value->id; ?>" class="btn btn-sm btn-primary">Histórico Ventas</a>
                                    </td>
                                </tr>
                                <?php
                            endif;
                        endforeach;
                    endif;
                    ?>
                    </tbody>

                </table>
                <input type="hidden" value="<?php echo escape_output(getAmtP($grandTotal)); ?>" id="grandTotal" name="grandTotal">
            </div>
            <!-- /.box-body -->
        </div>

    </div>



    <div class="modal fade" id="filterModal" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"><?php echo lang('inventory'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i data-feather="x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <?php echo form_open(base_url() . 'Inventory/index') ?>
                    <div class="row">
                        <input type="hidden" name="<?php echo escape_output($this->security->get_csrf_token_name()); ?>"
                               value="<?php echo escape_output($this->security->get_csrf_hash()); ?>">
                        <input type="hidden" name="hiddentIngredientID" id="hiddentIngredientID"
                               value="<?= isset($ingredient_id) ? $ingredient_id : '' ?>">
                        <div class="col-sm-12 mb-2">
                            <div class="form-group">
                                <select class="form-control select2 category_id ir_w_100" name="category_id" id="category_id">
                                    <option value=""><?php echo lang('category'); ?></option>
                                    <?php foreach ($ingredient_categories as $value) { ?>
                                        <option value="<?php echo escape_output($value->id) ?>" <?php echo set_select('category_id', $value->id); ?>>
                                            <?php echo escape_output($value->category_name) ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12 mb-2">
                            <div class="form-group">
                                <select class="form-control select2 ir_w_100" name="ingredient_id" id="ingredient_id">
                                    <option value=""><?php echo lang('ingredient'); ?></option>
                                    <?php foreach ($ingredients as $value) { ?>
                                        <option value="<?php echo escape_output($value->id) ?>" <?php echo set_select('ingredient_id', $value->id); ?>>
                                            <?php echo escape_output($value->name) . "(" . $value->code . ")" ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12 mb-2">
                            <div class="form-group">
                                <select class="form-control select2 ir_w_100" name="food_id" id="food_id">
                                    <option value=""><?php echo lang('food_menu'); ?></option>
                                    <?php foreach ($foodMenus as $value) {
                                    if($value->is_variation!=1){
                                        $p_name = '';
                                        if($value->parent_id!='0'){
                                            $p_name = getVariationName($value->parent_id);
                                        }
                                        ?>
                                        <option value="<?php echo escape_output($value->id) ?>" <?php echo set_select('food_id', $value->id); ?>>
                                            <?php echo substr(ucwords(strtolower((isset($p_name) && $p_name?$p_name." ":'').$value->name)), 0, 18) . "(" . $value->code . ")" ?>
                                        </option>
                                    <?php
                                      }
                                    }

                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12 mb-2">
                            <button type="submit" name="submit" value="submit"
                                    class="btn w-100 bg-blue-btn"><?php echo lang('submit'); ?></button>
                        </div>
                    </div>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ingredientModal" tabindex="-1" role="dialog" aria-labelledby="ingredientModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ingredientModalLabel">Detalles del Ingrediente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="ingredientTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="stock-tab" data-bs-toggle="tab" href="#stock" role="tab" aria-controls="stock" aria-selected="true">Stock por Sucursal</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="movements-tab" data-bs-toggle="tab" href="#movements" role="tab" aria-controls="movements" aria-selected="false">Últimos Movimientos</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="ingredientTabContent">
                        <div class="tab-pane fade show active" id="stock" role="tabpanel" aria-labelledby="stock-tab">
                            <div id="stockContent">
                                <!-- Stock por sucursales se cargará aquí -->
                            </div>
                        </div>
                        <div class="tab-pane fade" id="movements" role="tabpanel" aria-labelledby="movements-tab">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="startDate">Fecha Desde:</label>
                                    <input type="date" class="form-control" id="startDate">
                                </div>
                                <div class="col-md-4">
                                    <label for="endDate">Fecha Hasta:</label>
                                    <input type="date" class="form-control" id="endDate">
                                </div>
                                <div class="col-md-4">
                                    <button type="button" class="btn btn-primary mt-4" onclick="loadMovements(1, $('#startDate').val(), $('#endDate').val())">Filtrar</button>
                                </div>
                            </div>
                            <div id="movementsContent">
                                <!-- Movimientos se cargarán aquí -->
                            </div>
                            <nav aria-label="Movements pagination">
                                <ul class="pagination" id="movementsPagination">
                                    <!-- Paginación se generará aquí -->
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</section>

<script src="<?php echo base_url(); ?>frequent_changing/js/inventory.js"></script>
<!-- DataTables -->
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
  var outlet_name = "<?php echo ($outlet_name); ?>";
  var username = "<?php echo ($username); ?>";
  var hora = "<?php echo ($hora); ?>";
  var inventoryData = <?php echo json_encode($inventory); ?>;
  var ingredientCategories = <?php echo json_encode($ingredient_categories); ?>;
</script>
<script>
jQuery(document).ready(function() {
    function openIngredientModal(ingredientId) {
        currentIngredientId = ingredientId;
        jQuery('#ingredientModal').modal('show');
        loadStockByOutlets();
        loadMovements();
    }

    function loadStockByOutlets() {
        jQuery.ajax({
            url: '<?php echo base_url(); ?>Inventory/getStockByOutlets',
            type: 'POST',
            data: { ingredient_id: currentIngredientId },
            success: function(response) {
                jQuery('#stockContent').html(response);
            }
        });
    }

    function loadMovements(page = 1, startDate = '', endDate = '') {
        currentPage = page;
        jQuery.ajax({
            url: '<?php echo base_url(); ?>Inventory/getIngredientMovements',
            type: 'POST',
            data: { 
                ingredient_id: currentIngredientId, 
                page: page, 
                start_date: startDate, 
                end_date: endDate 
            },
            success: function(response) {
                const data = JSON.parse(response);
                jQuery('#movementsContent').html(data.html);
                jQuery('#movementsPagination').html(data.pagination);
            }
        });
    }

    // Event listener para los botones de abrir modal
    jQuery(document).on('click', '.open-modal-btn', function() {
        const ingredientId = jQuery(this).data('ingredient-id');
        openIngredientModal(ingredientId);
    });

    jQuery('#startDate, #endDate').change(function() {
        loadMovements(1, jQuery('#startDate').val(), jQuery('#endDate').val());
    });

    // Hacer loadMovements global para el onclick del botón filtrar
    window.loadMovements = loadMovements;
});

</script>