<link rel="stylesheet" href="<?php echo base_url(); ?>assets/dist/css/custom/report.css">

<section class="main-content-wrapper">
    <section class="content-header">
        <div>
        <h3 class="top-left-header d-inline-block"><?php echo lang('customer_due_report'); ?></h3>
        <?php
        if(isLMni() && isset($outlet_id)):
            ?>
            <small> <?php echo lang('outlet'); ?>: <?php echo escape_output(getOutletNameById($outlet_id))?></small>
            <?php
        endif;
        ?>
        <input type="hidden" class="datatable_name" data-title="<?php echo lang('customer_due_report'); ?>" data-id_name="datatable">
    </div>
    </section>

    
    <div class="box-wrapper">
            <!-- general form elements -->
            <div class="table-box">
            <?php
                    if(isLMni()):
                    ?>
                        <?php echo form_open(base_url() . 'Report/customerDueReport') ?>
                        <div class="row">

                        <div class="col-sm-12 col-md-4 col-lg-3 mb-3">
                                <div class="form-group">
                                    <select tabindex="2" class="form-control select2 ir_w_100" id="outlet_id" name="outlet_id">
                                        <?php
                                        $outlets = getAllOutlestByAssign();
                                        foreach ($outlets as $value):
                                            ?>
                                            <option <?= set_select('outlet_id',$value->id)?>  value="<?php echo escape_output($value->id) ?>"><?php echo escape_output($value->outlet_name) ?></option>
                                            <?php
                                        endforeach;
                                        ?>
                                    </select>
                                </div>
                        </div>
                        
                        <div class="col-sm-12 col-md-3 col-lg-2">
                            <div class="form-group">
                                <button type="submit" name="submit" value="submit"
                                        class="btn bg-blue-btn w-100"><?php echo lang('submit'); ?></button>
                            </div>
                        </div>
                    </div>
                        <?php
                    endif;
                    ?>
                <div class="table-responsive">
                   
                    
                    <table id="datatable" class="table">
                        <thead>
                            <tr>
                                <th class="ir_w2_txt_center"><?php echo lang('sn'); ?></th>
                                <th><?php echo lang('customer'); ?></th>
                                <th><?php echo lang('payable_due'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $pGrandTotal = 0;
                            $i = 1;
                            if (isset($customers)):
                                foreach ($customers as $key => $value) {
                                    $total_due = getCustomerDue($value->customer_id);
                                    if ($total_due > 0):
                                        $pGrandTotal+=$total_due;
                                        ?>
                            <tr>
                                <td class="ir_txt_center"><?php echo escape_output($i); ?></td>
                                <td><?php echo escape_output($value->name) ?></td>
                                <td><?php echo escape_output(getAmtCustom($total_due)) ?></td>
                            </tr>
                            <?php
                                    endif;
                                    $i++;
                                }
                            endif;
                            ?>
                        </tbody>
                    </table>
                    <br>
                </div>
                <!-- /.box-body -->
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