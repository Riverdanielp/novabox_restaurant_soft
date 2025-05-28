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
                <div class="mb-3 col-md-4 col-lg-2 col-sm-12">
                    <?php echo form_open(base_url() . 'Report/foodMenuSales', array('id' => 'foodMenuSales', 'method' => 'get')) ?>
                    <div class="form-group">
                        <input tabindex="1" type="datetime-local" id="startDate" name="startDate"
                            class="form-control"
                            placeholder="<?php echo lang('start_date'); ?>"
                            value="<?php
                                // Si hay valor y tiene espacio, formatear a datetime-local
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
                <div class="mb-3 col-md-4 col-lg-2 col-sm-12">
                    <div class="form-group">
                        <select tabindex="2" class="form-control select2 ir_w_100" id="top_less" name="top_less">
                            <option value="DESC" <?php echo (isset($top_less) && $top_less == "DESC") ? "selected" : ""; ?>><?php echo lang('Less'); ?></option>
                            <option value="ASC" <?php echo (isset($top_less) && $top_less == "ASC") ? "selected" : ""; ?>><?php echo lang('Top'); ?></option>
                        </select>
                    </div>
                </div>
                <div class="mb-3 col-md-4 col-lg-2 col-sm-12">
                    <div class="form-group">
                        <select tabindex="2" class="form-control select2 ir_w_100" id="product_type" name="product_type">
                            <option value=""><?php echo lang('select_product_type'); ?></option>
                            <option value="1" <?php echo (isset($product_type) && $product_type == "1") ? "selected" : ""; ?>><?php echo lang('Regular'); ?></option>
                            <option value="2" <?php echo (isset($product_type) && $product_type == "2") ? "selected" : ""; ?>><?php echo lang('Combo'); ?></option>
                            <option value="3" <?php echo (isset($product_type) && $product_type == "3") ? "selected" : ""; ?>><?php echo lang('Product'); ?></option>
                        </select>
                    </div>
                </div>
                <?php if(isLMni()): ?>
                <div class="mb-3 col-md-4 col-lg-2 col-sm-12">
                    <div class="form-group">
                        <select tabindex="2" class="form-control select2 ir_w_100" id="outlet_id" name="outlet_id">
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
                                <th><?php echo lang('code'); ?></th>
                                <th><?php echo lang('food_menu'); ?>(<?php echo lang('code'); ?>)</th>
                                <th><?php echo lang('category'); ?></th>
                                <th><?php echo lang('quantity'); ?></th>
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
                                <td><?php echo escape_output($value->code) ?></td>
                                <td><?php echo escape_output($value->menu_name) ?></td>
                                <td><?php echo escape_output($value->category_name) ?></td>
                                <td><?php echo escape_output($value->totalQty) ?></td>
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

<script src="<?php echo base_url(); ?>frequent_changing/js/custom_report_full.js<?php echo VERS() ?>"></script>