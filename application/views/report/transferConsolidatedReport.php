<link rel="stylesheet" href="<?php echo base_url(); ?>assets/dist/css/custom/foodMenuSales.css">

<section class="main-content-wrapper">
    <div class="row">
        <div class="col-md-12">
            <!-- general form elements -->
            <div class="table-box">
                <!-- /.box-header -->
                <div class="box-body table-responsive">
                    <h3 class="top-left-header text-left"><?php echo lang('transferReport'); ?> - Consolidado</h3>
                    <section class="content-header">
                        <input type="hidden" class="datatable_name" data-title="<?php echo lang('transferReport'); ?> - Consolidado" data-id_name="datatable">
                    </section>

                    <div class="my-2">
                        <?php
                        if(isLMni() && isset($outlet_id)):
                            ?>
                            <h4> <?php echo lang('outlet'); ?>: <?php echo escape_output(getOutletNameById($outlet_id))?></h4>
                            <?php
                        endif;
                        ?>
                        <h4><?= isset($start_date) && $start_date && isset($end_date) && $end_date ? lang('date').": " . date($this->session->userdata('date_format'), strtotime($start_date)) . " - " . date($this->session->userdata('date_format'), strtotime($end_date)) : '' ?><?= isset($start_date) && $start_date && !$end_date ? lang('date').": " . date($this->session->userdata('date_format'), strtotime($start_date)) : '' ?><?= isset($end_date) && $end_date && !$start_date ? lang('date').": " . date($this->session->userdata('date_format'), strtotime($end_date)) : '' ?>
                        </h4>
                        <?php if(isset($category_id) && $category_id): ?>
                            <h4><?php echo lang('category'); ?>: <?php echo escape_output(categoryName($category_id)) ?></h4>
                        <?php endif; ?>
                        <?php if(isset($from_outlet_id) && $from_outlet_id): ?>
                            <h4><?php echo lang('SendingOutlet'); ?>: <?php 
                                // Mostrar nombre de sucursal de envío
                                if (strpos($from_outlet_id, '|') !== false) {
                                    $id_parts = explode('|', $from_outlet_id);
                                    echo escape_output($id_parts[2]); // outlet_name
                                } else {
                                    echo escape_output(getOutletNameById($from_outlet_id));
                                }
                            ?></h4>
                        <?php endif; ?>
                        <?php if(isset($to_outlet_id) && $to_outlet_id): ?>
                            <h4><?php echo lang('ReceivingOutlet'); ?>: <?php 
                                // Mostrar nombre de sucursal de recepción
                                if (strpos($to_outlet_id, '|') !== false) {
                                    $id_parts = explode('|', $to_outlet_id);
                                    echo escape_output($id_parts[2]); // outlet_name
                                } else {
                                    echo escape_output(getOutletNameById($to_outlet_id));
                                }
                            ?></h4>
                        <?php endif; ?>
                    </div>

                    <div class="box-wrapper">
                        <div class="row mb-3">
                            <div class="col-sm-12 col-md-4 col-lg-2 mb-3">
                                <?php echo form_open(base_url() . 'Report/transferConsolidatedReport', $arrayName = array('id' => 'transferConsolidatedReport')) ?>
                                <div class="form-group">
                                    <input tabindex="1" type="text" id="" name="startDate" readonly class="form-control customDatepicker"
                                           placeholder="<?php echo lang('start_date'); ?>" value="<?php echo set_value('startDate'); ?>">
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-4 col-lg-2 mb-3">
                                <div class="form-group">
                                    <input tabindex="2" type="text" id="endMonth" name="endDate" readonly
                                           class="form-control customDatepicker" placeholder="<?php echo lang('end_date'); ?>"
                                           value="<?php echo set_value('endDate'); ?>">
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-4 col-lg-2 mb-3">
                                <div class="form-group">
                                    <select tabindex="3" class="form-control select2" id="from_outlet_id" name="from_outlet_id">
                                        <option value=""><?php echo lang('SendingOutlet')?></option>
                                        <?php
                                        foreach ($outlets as $value):
                                            ?>
                                            <option <?= set_select('from_outlet_id',$value->id)?>  value="<?php echo escape_output($value->id) ?>"><?php echo escape_output($value->outlet_name) ?></option>
                                            <?php
                                        endforeach;
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-4 col-lg-2 mb-3">
                                <div class="form-group">
                                    <select tabindex="4" class="form-control select2" id="to_outlet_id" name="to_outlet_id">
                                        <option value=""><?php echo lang('ReceivingOutlet')?></option>
                                        <?php
                                        foreach ($outlets as $value):
                                            ?>
                                            <option <?= set_select('to_outlet_id',$value->id)?>  value="<?php echo escape_output($value->id) ?>"><?php echo escape_output($value->outlet_name) ?></option>
                                            <?php
                                        endforeach;
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-4 col-lg-2 mb-3">
                                <div class="form-group">
                                    <select tabindex="5" class="form-control select2" id="category_id" name="category_id">
                                        <option value=""><?php echo lang('category')?></option>
                                        <?php
                                        foreach ($ingredient_categories as $value):
                                            ?>
                                            <option <?= set_select('category_id',$value->id)?>  value="<?php echo escape_output($value->id) ?>"><?php echo escape_output($value->category_name) ?></option>
                                            <?php
                                        endforeach;
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-4 col-lg-2 mb-3">
                                <div class="form-group">
                                    <button type="submit" name="submit" value="submit"
                                            class="btn bg-blue-btn w-100"><?php echo lang('submit'); ?></button>
                                </div>
                            </div>
                        </div>
                        <div class="table-box">
                            <!-- /.box-header -->
                            <div class="table-responsive">
                                <table id="datatable" class="table">
                                    <thead>
                                    <tr>
                                        <th class="ir_w2_txt_center"><?php echo lang('sn'); ?></th>
                                        <th><?php echo lang('ingredient'); ?></th>
                                        <th><?php echo lang('code'); ?></th>
                                        <th><?php echo lang('category'); ?></th>
                                        <th><?php echo lang('unit'); ?></th>
                                        <th class="ir_txt_right"><?php echo lang('total_quantity'); ?></th>
                                        <th class="ir_txt_center"># Transferencias</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $total_general = 0;
                                    if (isset($transferConsolidatedReport)):
                                        foreach ($transferConsolidatedReport as $key => $value) {
                                            $key++;
                                            $total_general += $value->total_quantity;
                                            ?>
                                            <tr>
                                                <td class="ir_txt_center"><?php echo escape_output($key); ?></td>
                                                <td><?php echo escape_output($value->ingredient_name) ?></td>
                                                <td><?php echo escape_output($value->ingredient_code) ?></td>
                                                <td><?php echo escape_output($value->category_name) ?></td>
                                                <td><?php echo escape_output($value->unit_name) ?></td>
                                                <td class="ir_txt_right"><?php echo escape_output(number_format($value->total_quantity, 2)) ?></td>
                                                <td class="ir_txt_center"><?php echo escape_output($value->transfer_count) ?></td>
                                            </tr>
                                            <?php
                                        }
                                    endif;
                                    ?>
                                    </tbody>
                                    <?php if (isset($transferConsolidatedReport) && !empty($transferConsolidatedReport)): ?>
                                    <tfoot>
                                        <tr class="table-secondary">
                                            <td colspan="5" class="ir_txt_right"><strong><?php echo lang('total'); ?></strong></td>
                                            <td class="ir_txt_right"><strong><?php echo number_format($total_general, 2) ?></strong></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                    <?php endif; ?>
                                </table>
                            </div>
                            <!-- /.box-body -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- DataTables -->
<script src="<?php echo base_url(); ?>assets/datatable_custom/jquery-3.3.1.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/jquery.dataTables.min.js"></script>
<script src="<?php echo base_url(); ?>assets/datatable_custom/dataTables.buttons.min.js"></script>
<script src="<?php echo base_url(); ?>assets/datatable_custom/buttons.flash.min.js"></script>
<script src="<?php echo base_url(); ?>assets/datatable_custom/jszip.min.js"></script>
<script src="<?php echo base_url(); ?>assets/datatable_custom/pdfmake.min.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/dataTables.bootstrap4.min.js"></script>
<script src="<?php echo base_url(); ?>assets/datatable_custom/vfs_fonts.js"></script>
<script src="<?php echo base_url(); ?>assets/datatable_custom/buttons.html5.min.js"></script>
<script src="<?php echo base_url(); ?>assets/datatable_custom/buttons.print.min.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/newDesign/js/forTable.js"></script>

<script src="<?php echo base_url(); ?>frequent_changing/js/custom_report.js"></script>