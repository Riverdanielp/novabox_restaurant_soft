<?php
$wl = getWhiteLabel();
$is_edit = isset($account) && $account;
?>

<!-- Main content -->
<section class="main-content-wrapper">

    <section class="content-header">
        <h3 class="top-left-header">
            <?php echo $is_edit ? 'Editar Cuenta' : 'Agregar Nueva Cuenta'; ?>
        </h3>
    </section>

    <div class="box-wrapper">
        <div class="table-box">
            <?php echo form_open(base_url('Account/saveAccount'), array('id' => 'account_form')); ?>
            <div class="box-body">
                <?php
                if ($this->session->flashdata('exception')) {
                    echo '<div class="alert alert-success alert-dismissible fade show">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                        <p><i class="icon fa fa-check"></i> ' . $this->session->flashdata('exception') . '</p>
                    </div>';
                    unset($_SESSION['exception']);
                }
                if ($this->session->flashdata('exception_err')) {
                    echo '<div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                        <p><i class="icon fa fa-times"></i> ' . $this->session->flashdata('exception_err') . '</p>
                    </div>';
                    unset($_SESSION['exception_err']);
                }
                ?>

                <?php if ($is_edit): ?>
                    <input type="hidden" name="id" value="<?php echo $account->id; ?>">
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><?php echo lang('name'); ?> <span class="text-danger">*</span></label>
                            <input type="text" name="account_name" id="account_name" class="form-control" 
                                   placeholder="Ej: Banco Popular, Caja Chica"
                                   value="<?php echo $is_edit ? escape_output($account->account_name) : escape_output($this->input->post('account_name')); ?>" required>
                            <?php if (form_error('account_name')) { ?>
                                <div class="callout callout-danger my-2">
                                    <?php echo form_error('account_name'); ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tipo de Cuenta <span class="text-danger">*</span></label>
                            <select name="account_type" id="account_type" class="form-control select2" required>
                                <option value="">Seleccionar tipo</option>
                                <option value="Caja" <?php echo ($is_edit && $account->account_type == 'Caja') ? 'selected' : set_select('account_type', 'Caja'); ?>>Caja</option>
                                <option value="Banco" <?php echo ($is_edit && $account->account_type == 'Banco') ? 'selected' : set_select('account_type', 'Banco'); ?>>Banco</option>
                                <option value="Otro" <?php echo ($is_edit && $account->account_type == 'Otro') ? 'selected' : set_select('account_type', 'Otro'); ?>>Otro</option>
                            </select>
                            <?php if (form_error('account_type')) { ?>
                                <div class="callout callout-danger my-2">
                                    <?php echo form_error('account_type'); ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Número de Cuenta (Opcional)</label>
                            <input type="text" name="account_number" id="account_number" class="form-control" 
                                   placeholder="Ej: 123456789"
                                   value="<?php echo $is_edit ? escape_output($account->account_number) : escape_output($this->input->post('account_number')); ?>">
                            <?php if (form_error('account_number')) { ?>
                                <div class="callout callout-danger my-2">
                                    <?php echo form_error('account_number'); ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php if (!$is_edit): ?>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Saldo Inicial</label>
                            <input type="number" name="opening_balance" id="opening_balance" 
                                   class="form-control" step="0.01" value="<?php echo escape_output($this->input->post('opening_balance', true) ? $this->input->post('opening_balance', true) : '0.00'); ?>">
                            <small class="form-text text-muted">
                                El saldo inicial solo se puede establecer al crear la cuenta
                            </small>
                            <?php if (form_error('opening_balance')) { ?>
                                <div class="callout callout-danger my-2">
                                    <?php echo form_error('opening_balance'); ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Saldo Inicial</label>
                            <input type="text" class="form-control" 
                                   value="<?php echo getAmtCustom($account->opening_balance); ?>" disabled>
                            <small class="form-text text-muted">No se puede modificar</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Saldo Actual</label>
                            <input type="text" class="form-control fw-bold" 
                                   value="<?php echo getAmtCustom($account->current_balance); ?>" disabled>
                            <small class="form-text text-muted">Se actualiza automáticamente</small>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Estado</label>
                            <select name="status" id="status" class="form-control select2">
                                <option value="Active" <?php echo ($is_edit && $account->status == 'Active') ? 'selected' : set_select('status', 'Active'); ?>>Activa</option>
                                <option value="Inactive" <?php echo ($is_edit && $account->status == 'Inactive') ? 'selected' : set_select('status', 'Inactive'); ?>>Inactiva</option>
                            </select>
                            <?php if (form_error('status')) { ?>
                                <div class="callout callout-danger my-2">
                                    <?php echo form_error('status'); ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Descripción (Opcional)</label>
                            <textarea name="description" id="description" class="form-control" rows="3" 
                                      placeholder="Descripción breve de la cuenta..."><?php echo $is_edit ? escape_output($account->description) : escape_output($this->input->post('description')); ?></textarea>
                            <?php if (form_error('description')) { ?>
                                <div class="callout callout-danger my-2">
                                    <?php echo form_error('description'); ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="box-footer">
                <a href="<?php echo base_url() ?>Account/accounts" class="btn btn-secondary">
                    <i data-feather="x"></i> Cancelar
                </a>
                <button type="submit" class="btn bg-blue-btn">
                    <i data-feather="save"></i> <?php echo $is_edit ? 'Actualizar' : 'Guardar'; ?> Cuenta
                </button>
            </div>

            <?php echo form_close(); ?>
        </div>
    </div>
</section>


<script>
    $(function() {
        "use strict";

        // Inicializar Select2 solo si no está ya inicializado
        if ($('.select2').length > 0 && $('.select2').hasClass('select2-hidden-accessible') === false) {
            $('.select2').select2({
                placeholder: 'Seleccionar',
                allowClear: true
            });
        }

        // Validación del formulario
        $('#account_form').on('submit', function(e) {
            let account_name = $('#account_name').val().trim();
            let account_type = $('#account_type').val();

            if (!account_name) {
                swal("Error", "El nombre de la cuenta es requerido", "error");
                return false;
            }

            if (!account_type) {
                swal("Error", "Debe seleccionar un tipo de cuenta", "error");
                return false;
            }

            return true;
        });

        // Activar íconos Feather
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
</script>