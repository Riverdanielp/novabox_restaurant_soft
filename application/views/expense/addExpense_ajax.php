<div class="row">
    <div class="mb-2 col-md-4">
        <div class="form-group">
            <label><?php echo lang('date'); ?> <span class="required_star">*</span></label>
            <input tabindex="1" type="text" id="expense_date" name="date" readonly class="form-control"
                placeholder="<?php echo lang('date'); ?>" value="<?php echo date("Y-m-d",strtotime('today')); ?>">
        </div>
        <?php if (form_error('date')) { ?>
        <div class="callout callout-danger my-2">
            <?php echo form_error('date'); ?>
        </div>
        <?php } ?>
    </div>

    <div class="mb-2 col-md-4">
        <div class="form-group">
            <label><?php echo lang('category'); ?> <span class="required_star">*</span></label>
            <select tabindex="3" class="form-control select2 ir_w_100" id="expense_category_id" name="category_id">
                <option value=""><?php echo lang('select'); ?></option>
                <?php foreach ($expense_categories as $ec) { ?>
                <option value="<?php echo escape_output($ec->id) ?>"
                    <?php echo set_select('category_id', $ec->id); ?>>
                    <?php echo escape_output($ec->name) ?>
                </option>
                <?php } ?>
            </select>
        </div>
        <?php if (form_error('category_id')) { ?>
        <div class="callout callout-danger my-2">
            <?php echo form_error('category_id'); ?>
        </div>
        <?php } ?>
    </div>

    <div class="mb-2 col-md-4">
        <div class="form-group">
            <label><?php echo lang('responsible_person'); ?> <span class="required_star">*</span></label>
            <select tabindex="4" class="form-control select2 ir_w_100" id="expense_employee_id" name="employee_id">
                <option value=""><?php echo lang('select'); ?></option>
                <?php $id_user = $this->session->userdata('user_id') ?>
                <?php foreach ($employees as $empls) { ?>
                <option value="<?php echo escape_output($empls->id) ?>"
                    <?php echo ($id_user == $empls->id) ? 'selected' : '' ?>
                    <?php echo set_select('employee_id', $empls->id); ?>>
                    <?php echo escape_output($empls->full_name) ?>
                </option>
                <?php } ?>
            </select>
        </div>
        <?php if (form_error('employee_id')) { ?>
        <div class="callout callout-danger my-2-2">
            <?php echo form_error('employee_id'); ?>
        </div>
        <?php } ?>
    </div>

    <div class="mb-2 col-md-4">
        <div class="form-group">
            <label><?php echo lang('payment_method'); ?> <span class="required_star">*</span></label>
            <select tabindex="3" class="form-control select2 ir_w_100" id="expense_payment_id" name="payment_id">
                <option value=""><?php echo lang('select'); ?></option>
                <?php foreach (getAllPaymentMethods(5) as $value) { ?>
                <option value="<?php echo escape_output($value->id) ?>"
                    <?php echo ('1' == $value->id) ? 'selected' : '' ?>
                    <?php echo set_select('payment_id', $value->id); ?>>
                    <?php echo escape_output($value->name)?>
                </option>
                <?php } ?>
            </select>
        </div>
        <?php if (form_error('payment_id')) { ?>
        <div class="callout callout-danger my-2">
            <?php echo form_error('payment_id'); ?>
        </div>
        <?php } ?>
    </div>

    <div class="mb-2 col-md-4">
        <div class="form-group">
            <label>Monto <span class="required_star">*</span></label>
            <input tabindex="2" type="text" id="expense_amount" name="amount" onfocus="this.select();"
                class="form-control integerchk" placeholder="Monto"
                value="<?php echo set_value('amount'); ?>">
        </div>
        <?php if (form_error('amount')) { ?>
        <div class="callout callout-danger my-2">
            <?php echo form_error('amount'); ?>
        </div>
        <?php } ?>
    </div>

    <div class="mb-2 col-md-4">
        <div class="form-group">
            <label><?php echo lang('description'); ?> <span class="required_star">*</span></label>
            <input type="text" class="form-control"  id="expense_note" name="note"
                placeholder="<?php echo lang('description'); ?>">
        </div>
        <?php if (form_error('note')) { ?>
        <div class="callout callout-danger my-2">
            <?php echo form_error('note'); ?>
        </div>
        <?php } ?>
    </div>
</div>

<div class="row">
    <div class="mb-2 col-md-3"></div>
    <div class="mb-2 col-md-3"></div>
    <div class="mb-2 col-md-3"></div>
    <div class="mb-2 col-md-3">
        <button type="button" id="expense_submit_btn" class="btn btn-primary btn-block me-2">
            <i data-feather="upload"></i>
            <?php echo lang('submit'); ?>
        </button>
    </div>
</div>