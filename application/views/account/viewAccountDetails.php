<!-- Main content -->
<section class="main-content-wrapper">

    <!-- Header con título y botones -->
    <section class="content-header px-0">
        <div class="row">
            <div class="col-md-6">
                <h2 class="top-left-header">Detalles de Cuenta</h2>
                <input type="hidden" class="datatable_name" data-title="Historial de Movimientos" data-id_name="datatable">
            </div>
            <div class="col-md-6 text-end">
                <a href="<?php echo base_url('Account/addEditAccount/' . $this->custom->encrypt_decrypt($account->id, 'encrypt')); ?>" class="btn bg-blue-btn" data-access="edit-260">
                    <i data-feather="edit-2"></i> Editar Cuenta
                </a>
                <a href="<?php echo base_url('Account/addAccountTransaction'); ?>" class="btn bg-blue-btn">
                    <i data-feather="plus"></i> Nuevo Movimiento
                </a>
                <a href="<?php echo base_url() ?>Account/accounts" class="btn btn-secondary">
                    <i data-feather="arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </section>

    <!-- Widgets de Estadísticas -->
    <section class="content-header mb-3">
    <div class="row">
        <!-- Widget 1: Total Entradas -->
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white mb-0">Total Entradas</h6>
                            <h2 class="text-white mt-2 mb-0"><?php 
                                $total_in = 0;
                                foreach ($transactions as $trans) {
                                    if (isset($trans->to_account_id) && $trans->to_account_id == $account->id) {
                                        $total_in += $trans->amount;
                                    }
                                }
                                echo getAmtCustom($total_in);
                            ?></h2>
                            <small class="text-white-50">Depósitos y transferencias</small>
                        </div>
                        <div>
                            <i class="fa fa-arrow-down fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Widget 2: Total Salidas -->
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white mb-0">Total Salidas</h6>
                            <h2 class="text-white mt-2 mb-0"><?php 
                                $total_out = 0;
                                foreach ($transactions as $trans) {
                                    if (isset($trans->from_account_id) && $trans->from_account_id == $account->id) {
                                        $total_out += $trans->amount;
                                    }
                                }
                                echo getAmtCustom($total_out);
                            ?></h2>
                            <small class="text-white-50">Retiros y transferencias</small>
                        </div>
                        <div>
                            <i class="fa fa-arrow-up fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Widget 3: Total Movimientos -->
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white mb-0">Total Movimientos</h6>
                            <h2 class="text-white mt-2 mb-0"><?php echo count($transactions); ?></h2>
                            <small class="text-white-50">Transacciones registradas</small>
                        </div>
                        <div>
                            <i class="fa fa-exchange-alt fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Widget 4: Saldo Actual -->
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white mb-0">Saldo Actual</h6>
                            <h2 class="text-white mt-2 mb-0"><?php echo getAmtCustom($account->current_balance); ?></h2>
                            <small class="text-white-50">Balance de la cuenta</small>
                        </div>
                        <div>
                            <i class="fa fa-wallet fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </section>

    <!-- Información General de la Cuenta -->
    <section class="box-wrapper mb-3">
        <div class="table-box">
            <div class="box-header with-border">
                <h3 class="box-title">Información de la Cuenta</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Nombre de la Cuenta</label>
                            <p class="text-muted"><?php echo escape_output($account->account_name); ?></p>
                        </div>
                        <div class="form-group mb-3">
                            <label class="fw-bold">Tipo de Cuenta</label>
                            <p class="text-muted"><?php echo escape_output($account->account_type); ?></p>
                        </div>
                        <div class="form-group mb-3">
                            <label class="fw-bold">Número de Cuenta</label>
                            <p class="text-muted"><?php echo $account->account_number ? escape_output($account->account_number) : '<em>No asignado</em>'; ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Saldo Inicial</label>
                            <p class="text-muted"><?php echo getAmtCustom($account->opening_balance); ?></p>
                        </div>
                        <div class="form-group mb-3">
                            <label class="fw-bold">Estado</label>
                            <p>
                                <?php if ($account->status == 'Active'): ?>
                                    <span class="badge bg-success">Activa</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactiva</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="form-group mb-3">
                            <label class="fw-bold">Fecha de Creación</label>
                            <p class="text-muted"><?php echo $account->added_date ? date('d/m/Y H:i', strtotime($account->added_date)) : 'N/A'; ?></p>
                        </div>
                    </div>
                </div>
                <?php if ($account->description): ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="fw-bold">Descripción</label>
                            <p class="text-muted"><?php echo escape_output($account->description); ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Historial de Movimientos -->
    <section class="box-wrapper">
        <div class="table-box">
            <div class="box-header with-border">
                <h3 class="box-title">Historial de Movimientos</h3>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th class="ir_w_1"><?php echo lang('sn'); ?></th>
                            <th class="ir_w_10">Fecha</th>
                            <th class="ir_w_12">Tipo</th>
                            <th class="ir_w_15">Desde</th>
                            <th class="ir_w_15">Hacia</th>
                            <th class="ir_w_12">Entrada</th>
                            <th class="ir_w_12">Salida</th>
                            <th class="ir_w_12">Usuario</th>
                            <th class="ir_w_15">Nota</th>
                            <th class="ir_w_10 not-export-col"><?php echo lang('action'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        foreach ($transactions as $trans) {
                            $entrada = 0;
                            $salida = 0;
                            
                            if ($trans->to_account_id == $account->id) {
                                $entrada = $trans->amount;
                            }
                            if ($trans->from_account_id == $account->id) {
                                $salida = $trans->amount;
                            }
                        ?>
                        <tr>
                            <td><?php echo escape_output($i++); ?></td>
                            <td><?php echo escape_output(date('d/m/Y H:i', strtotime($trans->transaction_date))); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    switch($trans->transaction_type) {
                                        case 'Transferencia': echo 'info'; break;
                                        case 'Deposito': echo 'success'; break;
                                        case 'Retiro': echo 'warning'; break;
                                        case 'Gasto': echo 'danger'; break;
                                        case 'Compra': echo 'danger'; break;
                                        default: echo 'secondary'; break;
                                    }
                                ?>">
                                    <?php echo escape_output($trans->transaction_type); ?>
                                </span>
                            </td>
                            <td><?php echo isset($trans->from_account_name) && $trans->from_account_name ? escape_output($trans->from_account_name) : '-'; ?></td>
                            <td><?php echo isset($trans->to_account_name) && $trans->to_account_name ? escape_output($trans->to_account_name) : '-'; ?></td>
                            <td class="ir_txt_right fw-bold text-success">
                                <?php echo $entrada > 0 ? '+' . escape_output(getAmtCustom($entrada)) : '-'; ?>
                            </td>
                            <td class="ir_txt_right fw-bold text-danger">
                                <?php echo $salida > 0 ? '-' . escape_output(getAmtCustom($salida)) : '-'; ?>
                            </td>
                            <td><?php echo isset($trans->user_name) ? escape_output($trans->user_name) : '-'; ?></td>
                            <td><?php echo isset($trans->note) ? escape_output($trans->note) : '-'; ?></td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <?php if (!isset($trans->reference_type) || !$trans->reference_type || !isset($trans->reference_id) || !$trans->reference_id): ?>
                                        <a class="btn btn-sm btn-danger delete-transaction" href="<?php echo base_url() ?>Account/deleteTransaction/<?php echo $this->custom->encrypt_decrypt($trans->id, 'encrypt'); ?>" title="Eliminar">
                                            <i data-feather="trash-2"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

</section>

<script>
    $(function() {
        "use strict";

        // Inicializar DataTable solo si no está ya inicializado
        if (!$.fn.DataTable.isDataTable('#datatable')) {
            var datatable = $('#datatable').DataTable({
                "order": [[1, "desc"]],
                "language": {
                    "url": "<?php echo base_url(); ?>assets/plugins/datatables/Spanish.json"
                },
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
            });
        }

        // Confirmar eliminación
        $(document).on('click', '.delete-transaction', function(e) {
            e.preventDefault();
            let url = $(this).attr('href');
            
            swal({
                title: "¿Estás seguro?",
                text: "Esta acción revertirá los saldos de las cuentas involucradas",
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

        // Activar íconos Feather
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
</script>
