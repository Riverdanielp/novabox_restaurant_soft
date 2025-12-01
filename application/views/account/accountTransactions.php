<!-- Main content -->
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
            <h2 class="top-left-header">Movimientos de Cuentas</h2>
            <input type="hidden" class="datatable_name" data-title="Movimientos de Cuentas" data-id_name="datatable">
        </div>
        <div class="col-md-6 text-end">
            <a data-access="add-260" class="btn bg-blue-btn menu_assign_class" href="<?php echo base_url() ?>Account/addAccountTransaction">
                <i data-feather="plus"></i> Nuevo Movimiento
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
                                    if (isset($trans->to_account_id) && $trans->to_account_id) {
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
                                    if (isset($trans->from_account_id) && $trans->from_account_id) {
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

        <!-- Widget 4: Neto -->
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white mb-0">Neto</h6>
                            <h2 class="text-white mt-2 mb-0"><?php 
                                $total_in = 0;
                                $total_out = 0;
                                foreach ($transactions as $trans) {
                                    if (isset($trans->to_account_id) && $trans->to_account_id) {
                                        $total_in += $trans->amount;
                                    }
                                    if (isset($trans->from_account_id) && $trans->from_account_id) {
                                        $total_out += $trans->amount;
                                    }
                                }
                                echo getAmtCustom($total_in - $total_out);
                            ?></h2>
                            <small class="text-white-50">Diferencia</small>
                        </div>
                        <div>
                            <i class="fa fa-calculator fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Filtros -->
<div class="box-wrapper mb-3">
    <div class="table-box">
        <div class="box-body">
            <form id="filter_form" method="get" action="<?php echo base_url('Account/accountTransactions'); ?>" class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Fecha Desde</label>
                        <input type="date" name="date_from" class="form-control" 
                               value="<?php echo isset($filters['date_from']) && $filters['date_from'] ? $filters['date_from'] : ''; ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Fecha Hasta</label>
                        <input type="date" name="date_to" class="form-control" 
                               value="<?php echo isset($filters['date_to']) && $filters['date_to'] ? $filters['date_to'] : ''; ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Cuenta</label>
                        <select name="account_id" class="form-control select2">
                            <option value="">Todas</option>
                            <?php foreach ($accounts as $acc): ?>
                                <option value="<?php echo $acc->id; ?>" <?php echo isset($filters['account_id']) && $filters['account_id'] == $acc->id ? 'selected' : ''; ?>>
                                    <?php echo escape_output($acc->account_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tipo</label>
                        <select name="transaction_type" class="form-control select2">
                            <option value="">Todos</option>
                            <option value="Transferencia" <?php echo isset($filters['transaction_type']) && $filters['transaction_type'] == 'Transferencia' ? 'selected' : ''; ?>>Transferencia</option>
                            <option value="Deposito" <?php echo isset($filters['transaction_type']) && $filters['transaction_type'] == 'Deposito' ? 'selected' : ''; ?>>Depósito</option>
                            <option value="Retiro" <?php echo isset($filters['transaction_type']) && $filters['transaction_type'] == 'Retiro' ? 'selected' : ''; ?>>Retiro</option>
                            <option value="Gasto" <?php echo isset($filters['transaction_type']) && $filters['transaction_type'] == 'Gasto' ? 'selected' : ''; ?>>Gasto</option>
                            <option value="Compra" <?php echo isset($filters['transaction_type']) && $filters['transaction_type'] == 'Compra' ? 'selected' : ''; ?>>Compra</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 mt-4">
                    <button type="submit" class="btn bg-blue-btn w-100">
                        <i data-feather="filter"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="box-wrapper">
    <div class="table-box">
        <div class="table-responsive">
            <table id="datatable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th></th>
                        <th class="ir_w_1"><?php echo lang('sn'); ?></th>
                        <th class="ir_w_8">Fecha</th>
                        <th class="ir_w_10">Tipo</th>
                        <th class="ir_w_15">Desde Cuenta</th>
                        <th class="ir_w_15">Hacia Cuenta</th>
                        <th class="ir_w_10">Monto</th>
                        <th class="ir_w_12">Usuario</th>
                        <th class="ir_w_20">Nota</th>
                        <th class="ir_w_9 not-export-col"><?php echo lang('action'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    foreach ($transactions as $trans) {
                        ?>
                        <tr>
                            <td></td>
                            <td><?php echo escape_output($i++); ?></td>
                            <td><?php echo escape_output(date('d/m/Y', strtotime($trans->transaction_date))); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    switch($trans->transaction_type) {
                                        case 'Transferencia': echo 'info'; break;
                                        case 'Deposito': echo 'success'; break;
                                        case 'Retiro': echo 'warning'; break;
                                        case 'Gasto': echo 'danger'; break;
                                        default: echo 'secondary'; break;
                                    }
                                ?>">
                                    <?php echo escape_output($trans->transaction_type); ?>
                                </span>
                            </td>
                            <td><?php echo isset($trans->from_account_name) && $trans->from_account_name ? escape_output($trans->from_account_name) : '-'; ?></td>
                            <td><?php echo isset($trans->to_account_name) && $trans->to_account_name ? escape_output($trans->to_account_name) : '-'; ?></td>
                            <td class="ir_txt_right fw-bold"><?php echo escape_output(getAmtCustom($trans->amount)); ?></td>
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
</div>

<!-- Paginación -->
<?php if ($pagination['total_pages'] > 1): ?>
<div class="row mt-3">
    <div class="col-12">
        <nav aria-label="Navegación de páginas">
            <ul class="pagination justify-content-center">
                <!-- Primera página -->
                <?php if ($pagination['current_page'] > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo base_url('Account/accountTransactions?' . http_build_query(array_merge($filters, ['page' => 1]))); ?>">
                            <i class="fa fa-angle-double-left"></i>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Página anterior -->
                <?php if ($pagination['has_previous']): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo base_url('Account/accountTransactions?' . http_build_query(array_merge($filters, ['page' => $pagination['previous_page']]))); ?>">
                            <i class="fa fa-angle-left"></i>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Páginas numeradas -->
                <?php
                $start_page = max(1, $pagination['current_page'] - 2);
                $end_page = min($pagination['total_pages'], $pagination['current_page'] + 2);

                for ($i = $start_page; $i <= $end_page; $i++):
                ?>
                    <li class="page-item <?php echo $i == $pagination['current_page'] ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo base_url('Account/accountTransactions?' . http_build_query(array_merge($filters, ['page' => $i]))); ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <!-- Página siguiente -->
                <?php if ($pagination['has_next']): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo base_url('Account/accountTransactions?' . http_build_query(array_merge($filters, ['page' => $pagination['next_page']]))); ?>">
                            <i class="fa fa-angle-right"></i>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Última página -->
                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo base_url('Account/accountTransactions?' . http_build_query(array_merge($filters, ['page' => $pagination['total_pages']]))); ?>">
                            <i class="fa fa-angle-double-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>

        <!-- Información de paginación -->
        <div class="text-center mt-2">
            <small class="text-muted">
                Mostrando <?php echo count($transactions); ?> de <?php echo $pagination['total_records']; ?> registros
                (Página <?php echo $pagination['current_page']; ?> de <?php echo $pagination['total_pages']; ?>)
            </small>
        </div>
    </div>
</div>
<?php endif; ?>

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
<script src="<?php echo base_url(); ?>assets/bower_components/select2/dist/js/select2.full.min.js"></script>

<script>
    $(function() {
        "use strict";
        
        // Inicializar Select2 solo si no está inicializado
        if ($('.select2').length > 0 && $('.select2').hasClass('select2-hidden-accessible') === false) {
            $('.select2').select2({
                placeholder: 'Seleccionar',
                allowClear: true
            });
        }
        
        // Evitar reinicialización de DataTable si ya existe
        if (!$.fn.DataTable.isDataTable('#datatable')) {
            var datatable = $('#datatable').DataTable({
                // "order": [[0, "desc"]],
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
