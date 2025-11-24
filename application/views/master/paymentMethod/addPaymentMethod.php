<section class="main-content-wrapper">
    <section class="content-header">
        <h3 class="top-left-header">
            <?php echo lang('add_payment_method'); ?>
        </h3>
    </section>

    <div class="box-wrapper">
        <!-- general form elements -->
        <div class="table-box">
            <!-- form start -->
            <?php echo form_open(base_url('paymentMethod/addEditPaymentMethod')); ?>
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-12 mb-2 col-md-6">

                        <div class="form-group">
                            <label><?php echo lang('payment_method_name'); ?> <span
                                    class="required_star">*</span></label>
                            <input tabindex="1" type="text" name="name" class="form-control"
                                placeholder="<?php echo lang('payment_method_name'); ?>"
                                value="<?php echo set_value('name'); ?>">
                        </div>
                        <?php if (form_error('name')) { ?>
                        <div class="callout callout-danger my-2">
                            <?php echo form_error('name'); ?>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="col-sm-12 mb-2 col-md-6">

                        <div class="form-group">
                            <label><?php echo lang('description'); ?></label>
                            <input tabindex="2" type="text" name="description" class="form-control"
                                placeholder="<?php echo lang('description'); ?>"
                                value="<?php echo set_value('description'); ?>">
                        </div>
                        <?php if (form_error('description')) { ?>
                        <div class="callout callout-danger my-2">
                            <?php echo form_error('description'); ?>
                        </div>
                        <?php } ?>
                    </div>
                    
                    <div class="mb-3 col-sm-12 col-md-3 col-lg-3">
                            <div class="form-group">
                                <label>Tipo de Transacción </label>
                                <select tabindex="7" class="form-control select2" name="tipo_trx"
                                        id="tipo_trx">
                                    <option
                                        <?= isset($payment_method_information) && $payment_method_information->tipo_trx== "Contado" ? 'selected' : '' ?>
                                            value="Contado">Contado</option>
                                    <option
                                        <?= isset($payment_method_information) && $payment_method_information->tipo_trx== "Crédito" ? 'selected' : '' ?>
                                            value="Crédito">Crédito</option>
                                    <option
                                        <?= isset($payment_method_information) && $payment_method_information->tipo_trx== "Sin" ? 'selected' : '' ?>
                                            value="Sin">Sin Clasificación</option>
                                </select>
                            </div>
                            <?php if (form_error('tipo_trx')) { ?>
                                <div class="callout callout-danger my-2">
                                    <?php echo form_error('tipo_trx'); ?>
                                </div>
                            <?php } ?>
                        </div>

                    <div class="col-sm-12 mb-2 col-md-6">
                        <div class="form-group">
                            <label>Cuenta Asociada <i class="fa fa-info-circle" data-toggle="tooltip" title="Si no selecciona una cuenta, los pagos irán a la cuenta predeterminada del sistema (Caja Cofre)"></i></label>
                            <select tabindex="3" class="form-control select2" name="account_id" id="account_id">
                                <option value="">-- Usar Cuenta Predeterminada (Caja Cofre) --</option>
                                <?php if (isset($accounts) && !empty($accounts)): ?>
                                    <?php foreach ($accounts as $account): ?>
                                        <option value="<?php echo escape_output($account->id); ?>">
                                            <?php echo escape_output($account->account_name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <?php if (form_error('account_id')) { ?>
                        <div class="callout callout-danger my-2">
                            <?php echo form_error('account_id'); ?>
                        </div>
                        <?php } ?>
                    </div>

                </div>
            </div>
            <!-- /.box-body -->
            <div class="box-footer">
                <button type="submit" name="submit" value="submit" class="btn bg-blue-btn me-2">
                    <i data-feather="upload"></i>
                    <?php echo lang('submit'); ?>
                </button>
                <a class="btn bg-blue-btn" href="<?php echo base_url() ?>paymentMethod/paymentMethods">
                    <i data-feather="corner-up-left"></i>
                    <?php echo lang('back'); ?>
                </a>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
        
</section>