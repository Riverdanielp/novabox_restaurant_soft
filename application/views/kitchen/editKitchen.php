<link rel="stylesheet" href="<?= base_url() ?>frequent_changing/css/custom_check_box.css">
<!-- Main content -->
<section class="main-content-wrapper">

    <?php
    if ($this->session->flashdata('exception')) {

        echo '<section class="alert-wrapper"><div class="alert alert-success alert-dismissible fade show"> 
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <div class="alert-body"><p><i class="m-right fa fa-check"></i>';
        echo escape_output($this->session->flashdata('exception'));unset($_SESSION['exception']);
        echo '</p></div></div></section>';
    }
    ?>
    <section class="content-header">
        <h3 class="top-left-header">
            <!--  AddKitchen-->
            <?php
            echo lang('edit_kitchen');
            ?>
        </h3>

    </section>
    <input type="hidden" value="<?php echo base_url()?>" id="base_url_hide">
    <input type="hidden" value="<?php echo $encrypted_id?>" id="kitchen_id">

    <!-- left column -->
    <div class="box-wrapper">
        <div class="table-box"> 
        <?php echo form_open(base_url('Kitchen/addEditKitchen/' . $encrypted_id)); ?> 
            <div class="box-body"> 
                <div class="row">
                            <div class="col-sm-12 mb-2 col-md-3">
                                <div class="form-group">
                                    <label><?php echo lang('name'); ?> <span class="required_star">*</span> (<?php echo lang('kitchen_identify'); ?>)</label>
                                    <input tabindex="1" autocomplete="off" type="text" name="name" class="form-control" placeholder="<?php echo lang('kitchen_identify_placeholder'); ?>" value="<?php echo escape_output($kitchen->name); ?>" />
                                </div>
                                <?php if (form_error('name')) { ?>
                                    <div class="callout callout-danger my-2">
                                        <?php echo form_error('name'); ?>
                                    </div>
                                <?php } ?>
                            </div>
                    <div class="clearfix"></div>
                    <?php
                    if(isLMni()):
                    ?>
                    <div class="col-sm-12 mb-3 col-md-6 col-lg-3">
                        <div class="form-group">
                            <label><?php echo lang('outlet'); ?> <span class="required_star">*</span></label>
                            <select tabindex="2" class="form-control select2 ir_w_100" id="outlet_id" name="outlet_id">
                                <option value=""><?php echo lang("select")?></option>
                                <?php
                                $outlets = getAllOutlestByAssign();
                                foreach ($outlets as $value):
                                    ?>
                                    <option <?php echo $kitchen->outlet_id == $value->id ? 'selected' : ''; ?> <?= set_select('outlet_id',$value->id)?>  value="<?php echo escape_output($value->id) ?>"><?php echo escape_output($value->outlet_name) ?></option>
                                    <?php
                                endforeach;
                                ?>
                            </select>
                            <?php if (form_error('outlet_id')) { ?>
                                <div class="callout callout-danger my-2">
                                    <?php echo form_error('outlet_id'); ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php endif;?>
                    <div class="col-sm-12 mb-2 col-md-3">
                        <div class="form-group">
                            <label><?php echo lang('printer'); ?> </label>
                            <select class="form-control select2" name="printer_id">
                                <option value=""><?php echo lang('select'); ?></option>
                                <?php foreach ($printers as $printer):?>
                                    <option <?php echo $kitchen->printer_id == $printer->id ? 'selected' : ''; ?> <?php echo set_select('printer_id',$printer->id)?> value="<?php echo escape_output($printer->id); ?>"><?php echo escape_output($printer->title); ?></option>
                                <?php endforeach;?>
                            </select>
                        </div>
                        <?php if (form_error('printer_id')) { ?>
                            <div class="callout callout-danger my-2">
                                <?php echo form_error('printer_id'); ?>
                            </div>
                        <?php } ?>
                    </div>

                    <?php
                    // En tu controlador, prepara este array:
                    $checked_designations = isset($kitchen->designations) ? explode(',', $kitchen->designations) : [];
                    ?>

                    <div class="col-sm-12 mb-2 col-md-3">
                        <div class="form-group">
                            <label>Usuarios que pueden imprimir <span class="required_star">*</span></label>
                            <div>
                                <label class="container">
                                    <input type="checkbox" name="designation[]" value="Admin" <?php echo in_array('Admin', $checked_designations) ? 'checked' : ''; ?>> <?php echo lang('Admin'); ?>
                                    <span class="checkmark"></span>
                                </label>
                                <label class="container">
                                    <input type="checkbox" name="designation[]" value="Cashier" <?php echo in_array('Cashier', $checked_designations) ? 'checked' : ''; ?>> <?php echo lang('Cashier'); ?>
                                    <span class="checkmark"></span>
                                </label>
                                <label class="container">
                                    <input type="checkbox" name="designation[]" value="Manager" <?php echo in_array('Manager', $checked_designations) ? 'checked' : ''; ?>> <?php echo lang('Manager'); ?>
                                    <span class="checkmark"></span>
                                </label>
                                <label class="container">
                                    <input type="checkbox" name="designation[]" value="Waiter" <?php echo in_array('Waiter', $checked_designations) ? 'checked' : ''; ?>> <?php echo lang('Waiter'); ?>
                                    <span class="checkmark"></span>
                                </label>
                                <label class="container">
                                    <input type="checkbox" name="designation[]" value="Chef" <?php echo in_array('Chef', $checked_designations) ? 'checked' : ''; ?>> <?php echo lang('Chef'); ?>
                                    <span class="checkmark"></span>
                                </label>
                                <label class="container">
                                    <input type="checkbox" name="designation[]" value="Normal User" <?php echo in_array('Normal User', $checked_designations) ? 'checked' : ''; ?>> <?php echo lang('Normal_Users'); ?>
                                    <span class="checkmark"></span>
                                </label>
                                <label class="container">
                                    <input type="checkbox" name="designation[]" value="Others" <?php echo in_array('Others', $checked_designations) ? 'checked' : ''; ?>> <?php echo lang('Others'); ?>
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <?php if (form_error('designation[]')) { ?>
                                <div class="callout callout-danger my-2">
                                    <?php echo form_error('designation[]'); ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>


                </div>
                <div class="row">
                    <div class="col-sm-12 mb-2 col-md-6">
                        <div class="form-group">
                            <label class="label_kitchen"><?php echo lang('categories'); ?><div class="tooltip_custom">
                                    <i data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo lang('kitchen_categories_tooltip'); ?>" data-feather="help-circle"></i>
                                </div> &nbsp;&nbsp;&nbsp;<b>   <a target="_blank" href="<?php echo base_url() ?>Kitchen/kitchens"><?php echo lang('GotoList'); ?></a></b> </label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="clearfix"></div>
                        <div class="col-sm-6 col-md-12">
                            <label class="container txt_48 left_margin_12"> <?php echo lang('select_all'); ?>
                                <input class="checkbox_userAll" type="checkbox" id="checkbox_userAll">
                                <span class="checkmark"></span>
                            </label>
                        </div>
                    </div>

                    <div class="clearfix"></div>
                    <div class="row category_list">
                    </div>
                </div>
            </div>
            <div class="box-footer">
                <button type="submit" name="submit" value="submit" class="btn bg-blue-btn me-2">
                    <i data-feather="upload"></i>
                    <?php echo lang('submit'); ?>
                </button>
                <a class="btn bg-blue-btn" href="<?php echo base_url() ?>Kitchen/kitchens">
                    <i data-feather="corner-up-left"></i>
                    <?php echo lang('back'); ?>
                </a>
            </div>
        <?php echo form_close(); ?>
        </div>
    </div>
</section>
<script src="<?php echo base_url(); ?>frequent_changing/kitchen_panel/js/add_edit_form.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/js/add_outlet.js"></script>