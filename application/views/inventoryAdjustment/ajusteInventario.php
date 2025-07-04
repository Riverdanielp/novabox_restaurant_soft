

<link rel="stylesheet" href="<?php echo base_url(); ?>assets/dist/css/custom/addInventoryAdjustment.css">
<style>
    .sugerencia-item.bg-primary { background: #007bff !important; color: #fff !important; }
    .sugerencia-item:hover { background: #007bff; color: #fff; }
</style>
<script src="<?php echo base_url(); ?>frequent_changing/js/ajuste_inventario.js<?php echo VERS() ?>"></script>

<section class="main-content-wrapper">

    <section class="content-header">
        <h3 class="top-left-header">
            <?php echo lang('add_inventory_Adjustment'); ?>
            
                <a class="btn bg-blue-btn" href="<?php echo base_url() ?>Inventory_adjustment/inventoryAdjustments" style="display: inline;">
                    <i data-feather="corner-up-left"></i>
                    <?php echo lang('back'); ?>
                </a>
        </h3>
    </section>


        <div class="box-wrapper">

            <form id="ajuste_form">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Referencia</label>
                        <input type="text" name="reference_no" class="form-control" value="<?= escape_output($reference_no) ?>" readonly>
                    </div>
                    <div class="col-md-3">
                        <label>Fecha</label>
                        <input type="date" name="date" class="form-control" value="<?= escape_output($date) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label>Nota</label>
                        <input type="text" name="note" class="form-control" value="<?= escape_output($note) ?>">
                    </div>
                </div>
                <input type="hidden" name="ajuste_id" id="ajuste_id" value="<?= isset($ajuste->id) ? $ajuste->id : '' ?>">
            </form>

            <div class="box-wrapper">
                <div class="table-box">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>Código</label>
                            <div style="position:relative;">
                                <div class="input-group">
                                    <input type="text" id="codigo_busqueda" class="form-control" placeholder="Scan o teclear código" autocomplete="off" autofocus>
                                    <button type="button" id="btn_buscar_codigo" class="btn btn-outline-primary">
                                        <i class="fa fa-search" style="color:#007bff;"></i>
                                    </button>
                                </div>
                            <div id="sugerencias" style="position:absolute;z-index:99;width:100%;display:none;background:white;border:1px solid #ccc;max-height:200px;overflow-y:auto;"></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label>Producto</label>
                            <input type="text" id="producto_nombre" class="form-control" readonly>
                            <input type="hidden" id="ingrediente_id">
                        </div>
                        <div class="col-md-2">
                            <label>Cant. Anterior</label>
                            <input type="text" id="qty_old" class="form-control" readonly>
                        </div>
                        <div class="col-md-2">
                            <label>Cant. Nueva</label>
                            <input type="number" id="qty_new" class="form-control" step="any">
                        </div>
                        <div class="col-md-1">
                            <label>Costo</label>
                            <input type="text" id="costo" class="form-control" readonly>
                        </div>
                        <div class="col-md-1">
                            <button type="button" id="btn_agregar_ajuste" class="btn btn-success mt-4">Agregar</button>
                        </div>
                    </div>

                    <!-- Tabla detalles -->
                    <div class="table-responsive">
                        <table class="table" id="tabla_ajustes">
                            <thead>
                                <tr>
                                    <th>Fecha/Hora</th>
                                    <th>Código</th>
                                    <th>Producto</th>
                                    <th>Cant. Anterior</th>
                                    <th>Cant. Nueva</th>
                                    <th>Diferencia</th>
                                    <th>Costo Unit.</th>
                                    <th>Costo Dif.</th>
                                    <th>Usuario</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-end">Totales:</th>
                                    <th id="total_diferencia" class="text-end"></th>
                                    <th></th>
                                    <th id="total_costo_dif" class="text-end"></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>
            </div>
       
        </div>

</section>