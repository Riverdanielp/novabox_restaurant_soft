<!-- Main content -->
<section class="main-content-wrapper">

    <section class="content-header">
        <h3 class="top-left-header">
            Nuevo Movimiento de Cuenta
        </h3>
    </section>

    <div class="box-wrapper">
        <div class="table-box">
            <?php echo form_open(base_url('Account/saveTransaction'), array('id' => 'transaction_form')); ?>
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

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tipo de Movimiento <span class="text-danger">*</span></label>
                            <select name="transaction_type" id="transaction_type" class="form-control select2" required>
                                <option value="">Seleccionar tipo</option>
                                <option value="Transferencia" <?php echo set_select('transaction_type', 'Transferencia'); ?>>Transferencia</option>
                                <option value="Deposito" <?php echo set_select('transaction_type', 'Deposito'); ?>>Depósito</option>
                                <option value="Retiro" <?php echo set_select('transaction_type', 'Retiro'); ?>>Retiro</option>
                            </select>
                            <?php if (form_error('transaction_type')) { ?>
                                <div class="callout callout-danger my-2">
                                    <?php echo form_error('transaction_type'); ?>
                                </div>
                            <?php } ?>
                            <small class="form-text text-muted d-block mt-2">
                                <strong>Nota:</strong> Los movimientos de Venta, Compra, Gasto, etc. se registran automáticamente desde sus módulos
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Monto <span class="text-danger">*</span></label>
                            <input type="number" name="amount" id="amount" class="form-control" step="0.01" 
                                   placeholder="0.00"
                                   value="<?php echo escape_output($this->input->post('amount')); ?>" required>
                            <?php if (form_error('amount')) { ?>
                                <div class="callout callout-danger my-2">
                                    <?php echo form_error('amount'); ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <!-- Cuenta Origen (Transferencia y Retiro) -->
                <div class="row" id="from_account_row" style="display: none;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Cuenta Origen <span class="text-danger">*</span></label>
                            <select name="from_account_id" id="from_account_id" class="form-control select2">
                                <option value="">Seleccionar cuenta</option>
                                <?php foreach ($accounts as $acc): ?>
                                    <option value="<?php echo $acc->id; ?>" data-balance="<?php echo $acc->current_balance; ?>" 
                                        <?php echo set_select('from_account_id', $acc->id); ?>>
                                        <?php echo escape_output($acc->account_name); ?> - <?php echo getAmtCustom($acc->current_balance); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div id="from_balance_info" class="mt-2"></div>
                            <?php if (form_error('from_account_id')) { ?>
                                <div class="callout callout-danger my-2">
                                    <?php echo form_error('from_account_id'); ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <!-- Cuenta Destino (Transferencia y Depósito) -->
                <div class="row" id="to_account_row" style="display: none;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Cuenta Destino <span class="text-danger">*</span></label>
                            <select name="to_account_id" id="to_account_id" class="form-control select2">
                                <option value="">Seleccionar cuenta</option>
                                <?php foreach ($accounts as $acc): ?>
                                    <option value="<?php echo $acc->id; ?>" data-balance="<?php echo $acc->current_balance; ?>" 
                                        <?php echo set_select('to_account_id', $acc->id); ?>>
                                        <?php echo escape_output($acc->account_name); ?> - <?php echo getAmtCustom($acc->current_balance); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div id="to_balance_info" class="mt-2"></div>
                            <?php if (form_error('to_account_id')) { ?>
                                <div class="callout callout-danger my-2">
                                    <?php echo form_error('to_account_id'); ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Nota (Opcional)</label>
                            <textarea name="note" id="note" class="form-control" rows="3" 
                                      placeholder="Descripción o referencia del movimiento..."><?php echo escape_output($this->input->post('note')); ?></textarea>
                            <?php if (form_error('note')) { ?>
                                <div class="callout callout-danger my-2">
                                    <?php echo form_error('note'); ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="box-footer">
                <a href="<?php echo base_url() ?>Account/accountTransactions" class="btn btn-secondary">
                    <i data-feather="x"></i> Cancelar
                </a>
                <button type="submit" class="btn bg-blue-btn">
                    <i data-feather="save"></i> Registrar Movimiento
                </button>
            </div>

            <?php echo form_close(); ?>
        </div>
    </div>

    <!-- Guía de ayuda -->
    <div class="box-wrapper mt-3">
        <div class="table-box">
            <div class="box-body">
                <h5 class="mb-3"><i data-feather="info"></i> Guía de Uso</h5>
                <div class="row">
                    <div class="col-md-4">
                        <h6 class="fw-bold">Transferencia</h6>
                        <p class="small">Mueve dinero de una cuenta a otra. Requiere seleccionar cuenta origen y destino. El saldo debe ser suficiente en la cuenta origen.</p>
                    </div>
                    <div class="col-md-4">
                        <h6 class="fw-bold">Depósito</h6>
                        <p class="small">Agrega dinero a una cuenta específica. Solo requiere seleccionar la cuenta destino. El saldo de esa cuenta aumentará.</p>
                    </div>
                    <div class="col-md-4">
                        <h6 class="fw-bold">Retiro</h6>
                        <p class="small">Retira dinero de una cuenta. Solo requiere seleccionar la cuenta origen. El saldo debe ser suficiente para el retiro.</p>
                    </div>
                </div>
            </div>
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

        // Mostrar/ocultar campos según tipo de movimiento
        $('#transaction_type').on('change', function() {
            let type = $(this).val();
            
            $('#from_account_row').hide();
            $('#to_account_row').hide();
            $('#from_account_id').removeAttr('required');
            $('#to_account_id').removeAttr('required');

            if (type == 'Transferencia') {
                $('#from_account_row').show();
                $('#to_account_row').show();
                $('#from_account_id').attr('required', 'required');
                $('#to_account_id').attr('required', 'required');
            } else if (type == 'Deposito') {
                $('#to_account_row').show();
                $('#to_account_id').attr('required', 'required');
            } else if (type == 'Retiro') {
                $('#from_account_row').show();
                $('#from_account_id').attr('required', 'required');
            }
        });

        // Mostrar saldo de cuenta origen
        $('#from_account_id').on('change', function() {
            let balance = $(this).find(':selected').data('balance');
            if (balance !== undefined) {
                $('#from_balance_info').html('<strong><i data-feather="check-circle" class="text-success"></i> Saldo disponible:</strong> <span class="fw-bold">' + formatCurrency(balance) + '</span>');
                feather.replace();
            }
        });

        // Mostrar saldo de cuenta destino
        $('#to_account_id').on('change', function() {
            let balance = $(this).find(':selected').data('balance');
            if (balance !== undefined) {
                $('#to_balance_info').html('<strong><i data-feather="info" class="text-info"></i> Saldo actual:</strong> <span class="fw-bold">' + formatCurrency(balance) + '</span>');
                feather.replace();
            }
        });

        // Validación del formulario
        $('#transaction_form').on('submit', function(e) {
            let type = $('#transaction_type').val();
            let amount = parseFloat($('#amount').val());
            let from_account = $('#from_account_id').val();
            let to_account = $('#to_account_id').val();

            if (!type) {
                swal("Error", "Debe seleccionar un tipo de movimiento", "error");
                return false;
            }

            if (!amount || amount <= 0) {
                swal("Error", "El monto debe ser mayor a 0", "error");
                return false;
            }

            if (type == 'Transferencia') {
                if (!from_account || !to_account) {
                    swal("Error", "Debe seleccionar cuenta origen y destino", "error");
                    return false;
                }
                if (from_account == to_account) {
                    swal("Error", "La cuenta origen y destino no pueden ser la misma", "error");
                    return false;
                }

                // Verificar saldo suficiente
                let balance = parseFloat($('#from_account_id').find(':selected').data('balance'));
                if (amount > balance) {
                    swal("Error", "Saldo insuficiente en la cuenta origen", "error");
                    return false;
                }
            } else if (type == 'Deposito' && !to_account) {
                swal("Error", "Debe seleccionar una cuenta destino", "error");
                return false;
            } else if (type == 'Retiro') {
                if (!from_account) {
                    swal("Error", "Debe seleccionar una cuenta origen", "error");
                    return false;
                }
                
                let balance = parseFloat($('#from_account_id').find(':selected').data('balance'));
                if (amount > balance) {
                    swal("Error", "Saldo insuficiente en la cuenta", "error");
                    return false;
                }
            }

            return true;
        });

        // Función para formatear moneda
        function formatCurrency(amount) {
            return '<?php echo $this->session->userdata('currency'); ?> ' + 
                   parseFloat(amount).toLocaleString('es-DO', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        // Activar íconos Feather
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
</script>

