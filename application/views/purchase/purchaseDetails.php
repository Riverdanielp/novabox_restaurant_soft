<section class="main-content-wrapper">
    <section class="content-header">
        <h3 class="top-left-header">
            <?php echo lang('details_purchase'); ?>
        </h3>
    </section>


    <div class="box-wrapper">
        <div class="table-box">
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-12 mb-2 col-md-3">
                        <div class="form-group">
                            <h5><?php echo lang('ref_no'); ?></h5>
                            <p class=""><?php echo escape_output($purchase_details->reference_no) ?></p>
                        </div>
                    </div>

                    <div class="col-sm-12 mb-2 col-md-3">
                        <div class="form-group">
                            <h5><?php echo lang('supplier'); ?></h5>
                            <?php echo escape_output(getSupplierNameById($purchase_details->supplier_id)); ?>
                        </div>
                    </div>

                    <div class="col-sm-12 mb-2 col-md-3">
                        <div class="form-group">
                            <h5><?php echo lang('date'); ?></h5>
                            <p class="">
                                <?php echo escape_output(date($this->session->userdata('date_format'), strtotime($purchase_details->date))); ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-sm-12 mb-2 col-md-3">
                        <div class="form-group">
                            <h5><?php echo lang('ingredients'); ?></h5>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive" id="purchase_cart">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="txt_31"><?php echo lang('sn'); ?></th>
                                        <th class="txt_33">
                                            <?php echo lang('ingredient'); ?>(<?php echo lang('code'); ?>)</th>
                                        <th class="txt_32"><?php echo lang('unit_price'); ?></th>
                                        <th class="txt_32"><?php echo lang('quantity_amount'); ?></th>
                                        <th class="txt_33"><?php echo lang('total'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 0;
                                    if ($purchase_ingredients && !empty($purchase_ingredients)) {
                                        foreach ($purchase_ingredients as $pi) {
                                            $i++;
                                            echo '<tr id="row_' . $i . '">' .
                                            '<td class="txt_24"><p>' . $i . '</p></td>' .
                                            '<td class="ir_w_20"><span class="txt_18">' . getIngredientNameById($pi->ingredient_id) . ' (' . getIngredientCodeById($pi->ingredient_id) . ')</span></td>' .
                                            '<td class="ir_w_15">' . escape_output(getAmtPCustom($pi->unit_price)) . '</td>' .
                                            '<td class="ir_w_15">' . $pi->quantity_amount . ' ' . unitName(getPurchaseUnitIdByIgId($pi->ingredient_id)) . '</td>' .
                                            '<td class="ir_w_20">' . escape_output(getAmtPCustom($pi->total)) . '</td>' .
                                            '</tr>'
                                            ;
                                        }
                                    }
                                    ?>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-7">

                    </div>
                    <div class="col-md-3 pull-right">
                        <div class="form-group">
                            <h5><?php echo lang('g_total'); ?></h5>
                            <p class="">
                                <?php echo escape_output(getAmtPCustom($purchase_details->grand_total)) ?>
                            </p>
                        </div>
                        <div class="form-group">
                            <h5><?php echo lang('paid'); ?></h5>
                            <p class="">
                                <?php echo escape_output(getAmtPCustom($purchase_details->paid)) ?>
                            </p>
                        </div>
                        <div class="form-group">
                            <h5><?php echo lang('due'); ?></h5>
                            <p class="">
                                <?php echo escape_output(getAmtPCustom($purchase_details->due)) ; ?>
                            </p>

                        </div>
                        <div class="form-group">
                            <h5><?php echo lang('payment_method'); ?></h5>
                            <p class="">
                                <?php echo escape_output(getPaymentName($purchase_details->payment_id)) ; ?>
                            </p>

                        </div>
                    </div>
                    <div class="col-md-3">

                    </div>
                </div>
                <div class="row">
                    <div class="col-md-offset-6 col-md-3">

                    </div>
                    <div class="col-md-3">

                    </div>
                </div>
            </div>
            

            <div class="box-footer">
                <a class="btn bg-blue-btn me-2" href="<?php echo base_url() ?>Purchase/addEditPurchase/<?php echo escape_output($encrypted_id); ?>">
                    <i data-feather="edit"></i>
                    <?php echo lang('edit'); ?>
                </a>
                <a class="btn bg-blue-btn" href="<?php echo base_url() ?>Purchase/purchases">
                    <i data-feather="corner-up-left"></i>
                    <?php echo lang('back'); ?>
                </a>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
        
</section>