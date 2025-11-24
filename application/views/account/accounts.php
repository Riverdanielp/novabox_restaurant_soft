<?php
$wl = getWhiteLabel();
?>

<section class="main-content-wrapper">
<?php
if ($value = $this->session->flashdata('exception')) {
    echo '<section class="content-header px-0"><div class="alert alert-success alert-dismissible fade show"> 
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    <div class="alert-body"><p class="m-0"><i class="m-right fa fa-check"></i>';
    echo escape_output($value);
    echo '</p></div></div></section>';
    unset($_SESSION['exception']);
}
?>
<?php
if ($value = $this->session->flashdata('exception_err')) {
    echo '<section class="content-header px-0"><div class="alert alert-danger alert-dismissible fade show"> 
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    <div class="alert-body"><p class="m-0"><i class="m-right fa fa-times"></i>';
    echo escape_output($value);
    echo '</p></div></div></section>';
    unset($_SESSION['exception_err']);
}
?>

<section class="content-header px-0">
    <div class="row">
        <div class="col-md-6">
            <h2 class="top-left-header">Cuentas Bancarias</h2>
            <input type="hidden" class="datatable_name" data-title="Cuentas Bancarias" data-id_name="datatable">
        </div>
        <div class="col-md-6 text-end">
            <button class="btn bg-blue-btn" data-bs-toggle="modal" data-bs-target="#addTransactionModal" title="Registrar nuevo movimiento">
                <i data-feather="plus"></i> Nuevo Movimiento
            </button>
            <a data-access="add-260" class="btn bg-blue-btn menu_assign_class" href="<?php echo base_url() ?>Account/addEditAccount">
                <i data-feather="plus"></i> Nueva Cuenta
            </a>
        </div>
    </div>
</section>

<!-- Widgets de Estadísticas -->
<section class="content-header mb-3">
    <div class="row">
        <!-- Widget 1: Total Cuentas Activas -->
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white mb-0">Cuentas Activas</h6>
                            <h2 class="text-white mt-2 mb-0"><?php echo count(array_filter($accounts, function($a) { return $a->status == 'Active'; })); ?></h2>
                            <small class="text-white-50">Total activas</small>
                        </div>
                        <div>
                            <i class="fa fa-briefcase fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Widget 2: Saldo Total -->
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white mb-0">Saldo Total</h6>
                            <h2 class="text-white mt-2 mb-0"><?php 
                                $total = array_sum(array_map(function($a) { return $a->current_balance; }, $accounts));
                                echo getAmtCustom($total); 
                            ?></h2>
                            <small class="text-white-50">En todas las cuentas</small>
                        </div>
                        <div>
                            <i class="fa fa-money-bill-wave fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Widget 3: Saldo Promedio -->
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white mb-0">Saldo Promedio</h6>
                            <h2 class="text-white mt-2 mb-0"><?php 
                                $active_count = count(array_filter($accounts, function($a) { return $a->status == 'Active'; }));
                                $total_balance = array_sum(array_map(function($a) { return $a->current_balance; }, $accounts));
                                $average = $active_count > 0 ? $total_balance / $active_count : 0;
                                echo getAmtCustom($average); 
                            ?></h2>
                            <small class="text-white-50">Por cuenta</small>
                        </div>
                        <div>
                            <i class="fa fa-chart-pie fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Widget 4: Caja Cofre -->
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white mb-0">Caja Cofre</h6>
                            <h2 class="text-white mt-2 mb-0"><?php 
                                $caja_cofre = current(array_filter($accounts, function($a) { return $a->is_default == 1; }));
                                echo $caja_cofre ? getAmtCustom($caja_cofre->current_balance) : getAmtCustom(0);
                            ?></h2>
                            <small class="text-white-50">Cuenta principal</small>
                        </div>
                        <div>
                            <i class="fa fa-lock fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="box-wrapper">
    <div class="table-box">
        <div class="table-responsive">
            <table id="datatable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th class="ir_w_1"><?php echo lang('sn'); ?></th>
                        <th class="ir_w_15">Nombre</th>
                        <th class="ir_w_10">Tipo</th>
                        <th class="ir_w_12">Número de Cuenta</th>
                        <th class="ir_w_12">Saldo Inicial</th>
                        <th class="ir_w_12">Saldo Actual</th>
                        <th class="ir_w_10">Estado</th>
                        <th class="ir_w_20">Descripción</th>
                        <th class="ir_w_8 not-export-col"><?php echo lang('action'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    foreach ($accounts as $account) {
                        ?>
                        <tr>
                            <td><?php echo escape_output($i++); ?></td>
                            <td>
                                <?php echo escape_output($account->account_name); ?>
                                <?php if ($account->is_default == 1): ?>
                                    <br><span class="badge bg-warning">Principal</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo escape_output($account->account_type); ?></td>
                            <td><?php echo escape_output($account->account_number ? $account->account_number : 'N/A'); ?></td>
                            <td class="ir_txt_right"><?php echo escape_output(getAmtCustom($account->opening_balance)); ?></td>
                            <td class="ir_txt_right fw-bold">
                                <span class="<?php echo $account->current_balance < 0 ? 'text-danger' : 'text-success'; ?>">
                                    <?php echo escape_output(getAmtCustom($account->current_balance)); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($account->status == 'Active'): ?>
                                    <span class="badge bg-success">Activa</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactiva</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo escape_output(substr($account->description, 0, 30)); ?></td>
                            <td>
                                <div class="btn-group">
                                    <a data-access="view-260" class="btn btn-sm btn-info menu_assign_class" href="<?php echo base_url() ?>Account/viewAccountDetails/<?php echo $this->custom->encrypt_decrypt($account->id, 'encrypt'); ?>" title="Ver Detalles">
                                        <i data-feather="eye"></i>
                                    </a>
                                    <button class="btn btn-sm btn-success open-quick-transaction" data-account-id="<?php echo $account->id; ?>" data-account-name="<?php echo escape_output($account->account_name); ?>" title="Registrar movimiento rápido">
                                        <i data-feather="plus-circle"></i>
                                    </button>
                                    <?php if ($account->is_default != 1): ?>
                                        <a data-access="add-260" class="btn btn-sm btn-warning menu_assign_class" href="<?php echo base_url() ?>Account/addEditAccount/<?php echo $this->custom->encrypt_decrypt($account->id, 'encrypt'); ?>" title="Editar">
                                            <i data-feather="edit"></i>
                                        </a>
                                        <a class="btn btn-sm btn-danger delete-account" href="<?php echo base_url() ?>Account/deleteAccount/<?php echo $this->custom->encrypt_decrypt($account->id, 'encrypt'); ?>" title="Eliminar">
                                            <i data-feather="trash-2"></i>
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled title="No se puede editar/eliminar cuenta principal">
                                            <i data-feather="lock"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</section>

<!-- Modal para Nuevo Movimiento Rápido -->
<div class="modal fade" id="addTransactionModal" tabindex="-1" aria-labelledby="addTransactionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTransactionLabel">Registrar Nuevo Movimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickTransactionForm" method="POST" action="<?php echo base_url('Account/saveTransaction'); ?>">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Tipo de Movimiento <span class="text-danger">*</span></label>
                                <select name="transaction_type" id="modal_transaction_type" class="form-control select2-modal" required>
                                    <option value="">Seleccionar tipo</option>
                                    <option value="Transferencia">Transferencia</option>
                                    <option value="Deposito">Depósito</option>
                                    <option value="Retiro">Retiro</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Monto <span class="text-danger">*</span></label>
                                <input type="number" name="amount" id="modal_amount" class="form-control" step="0.01" placeholder="0.00" required>
                            </div>
                        </div>
                    </div>

                    <!-- Cuenta Origen -->
                    <div class="row" id="modal_from_account_row" style="display: none;">
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label>Cuenta Origen <span class="text-danger">*</span></label>
                                <select name="from_account_id" id="modal_from_account_id" class="form-control select2-modal">
                                    <option value="">Seleccionar cuenta</option>
                                    <?php foreach ($accounts as $acc): ?>
                                        <option value="<?php echo $acc->id; ?>" data-balance="<?php echo $acc->current_balance; ?>">
                                            <?php echo escape_output($acc->account_name); ?> - <?php echo getAmtCustom($acc->current_balance); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Cuenta Destino -->
                    <div class="row" id="modal_to_account_row" style="display: none;">
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label>Cuenta Destino <span class="text-danger">*</span></label>
                                <select name="to_account_id" id="modal_to_account_id" class="form-control select2-modal">
                                    <option value="">Seleccionar cuenta</option>
                                    <?php foreach ($accounts as $acc): ?>
                                        <option value="<?php echo $acc->id; ?>" data-balance="<?php echo $acc->current_balance; ?>">
                                            <?php echo escape_output($acc->account_name); ?> - <?php echo getAmtCustom($acc->current_balance); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label>Nota (Opcional)</label>
                                <textarea name="note" id="modal_note" class="form-control" rows="2" placeholder="Descripción del movimiento..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Campo de fecha oculto (se auto-rellena) -->
                    <input type="hidden" name="transaction_date" id="modal_transaction_date" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn bg-blue-btn">Registrar Movimiento</button>
                </div>
            </form>
        </div>
    </div>
</div>

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
<script src="<?php echo base_url(); ?>assets/bower_components/select2/dist/js/select2.full.min.js"></script>

<script>
    $(function() {
        "use strict";
        
        // Inicializar Select2 en el modal solo si no está inicializado
        if ($('.select2-modal').length > 0 && $('.select2-modal').hasClass('select2-hidden-accessible') === false) {
            $('.select2-modal').select2({
                placeholder: 'Seleccionar',
                allowClear: true,
                dropdownParent: $('#addTransactionModal')
            });
        }
        
        // Evitar reinicialización de DataTable si ya existe
        if (!$.fn.DataTable.isDataTable('#datatable')) {
            var datatable = $('#datatable').DataTable({
                "order": [[0, "asc"]],
                "language": {
                    "url": "<?php echo base_url(); ?>assets/plugins/datatables/Spanish.json"
                },
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
            });
        }

        // Botones rápidos de movimiento por fila
        $(document).on('click', '.open-quick-transaction', function(e) {
            e.preventDefault();
            let accountId = $(this).data('account-id');
            let accountName = $(this).data('account-name');
            
            // Limpiar y resetear el formulario
            $('#quickTransactionForm')[0].reset();
            $('#modal_transaction_type').val('').trigger('change');
            
            // Pre-seleccionar la cuenta para movimientos rápidos
            $('#modal_to_account_id').val(accountId);
            
            // Mostrar el modal
            $('#addTransactionModal').modal('show');
            
            // Enfocar en tipo de movimiento
            setTimeout(function() {
                $('#modal_transaction_type').focus();
            }, 500);
        });

        // Lógica del modal de transacciones
        $('#modal_transaction_type').on('change', function() {
            let type = $(this).val();
            
            $('#modal_from_account_row').hide();
            $('#modal_to_account_row').hide();
            $('#modal_from_account_id').removeAttr('required');
            $('#modal_to_account_id').removeAttr('required');

            if (type == 'Transferencia') {
                $('#modal_from_account_row').show();
                $('#modal_to_account_row').show();
                $('#modal_from_account_id').attr('required', 'required');
                $('#modal_to_account_id').attr('required', 'required');
            } else if (type == 'Deposito') {
                $('#modal_to_account_row').show();
                $('#modal_to_account_id').attr('required', 'required');
            } else if (type == 'Retiro') {
                $('#modal_from_account_row').show();
                $('#modal_from_account_id').attr('required', 'required');
            }
        });

        // Validación del formulario modal
        $('#quickTransactionForm').on('submit', function(e) {
            let type = $('#modal_transaction_type').val();
            let amount = parseFloat($('#modal_amount').val());
            let from_account = $('#modal_from_account_id').val();
            let to_account = $('#modal_to_account_id').val();

            if (!type) {
                swal("Error", "Debe seleccionar un tipo de movimiento", "error");
                e.preventDefault();
                return false;
            }

            if (!amount || amount <= 0) {
                swal("Error", "El monto debe ser mayor a 0", "error");
                e.preventDefault();
                return false;
            }

            if (type == 'Transferencia') {
                if (!from_account || !to_account) {
                    swal("Error", "Debe seleccionar cuenta origen y destino", "error");
                    e.preventDefault();
                    return false;
                }
                if (from_account == to_account) {
                    swal("Error", "La cuenta origen y destino no pueden ser la misma", "error");
                    e.preventDefault();
                    return false;
                }
            } else if (type == 'Deposito' && !to_account) {
                swal("Error", "Debe seleccionar una cuenta destino", "error");
                e.preventDefault();
                return false;
            } else if (type == 'Retiro' && !from_account) {
                swal("Error", "Debe seleccionar una cuenta origen", "error");
                e.preventDefault();
                return false;
            }

            return true;
        });

        // Confirmar eliminación
        $(document).on('click', '.delete-account', function(e) {
            e.preventDefault();
            let url = $(this).attr('href');
            
            swal({
                title: "¿Estás seguro?",
                text: "Esta acción no se puede revertir si la cuenta no tiene movimientos",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar"
            }).then(function() {
                window.location.href = url;
            });
        });

        // Limpiar el formulario modal cuando se cierra
        $('#addTransactionModal').on('hidden.bs.modal', function() {
            $('#quickTransactionForm')[0].reset();
            $('#modal_transaction_type').val('').trigger('change');
            if ($('.select2-modal').hasClass('select2-hidden-accessible')) {
                $('.select2-modal').val(null).trigger('change');
            }
        });

        // Activar íconos Feather
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
</script>
