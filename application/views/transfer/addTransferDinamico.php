<script src="<?php echo base_url(); ?>frequent_changing/js/add_transfer_dinamico.js<?php echo VERS() ?>"></script>
<section class="main-content-wrapper">
    <section class="content-header">
        <h3 class="top-left-header"><?php echo lang('add_transfer'); ?></h3>
    </section>
    <div class="box-wrapper">
        <div class="table-box">
            <form id="transfer_form_dinamico">
                <div class="box-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label><?php echo lang('ref_no'); ?></label>
                            <input type="text" id="reference_no" name="reference_no" class="form-control" readonly value="<?php echo escape_output($pur_ref_no); ?>">
                        </div>
                        <div class="col-md-3">
                            <label><?php echo lang('date'); ?></label>
                            <input type="text" id="date" name="date" class="form-control" readonly value="<?=date('Y-m-d',strtotime('today'))?>">
                        </div>
                        <div class="col-md-3">
                            <label><?php echo lang('to_outlet'); ?></label>
                            <!-- <select class="form-control select2" name="to_outlet_id" id="to_outlet_id" <?= $disable_to_outlet ? 'disabled' : '' ?>>
                                <option value=""><?php echo lang('select'); ?></option>
                                <?php foreach ($outlets as $value) {
                                    $outlet_id = $this->session->userdata('outlet_id');
                                    if($outlet_id!=$value->id): ?>
                                    <option value="<?php echo escape_output($value->id) ?>"
                                        <?php echo (isset($transfer_details) && $transfer_details->to_outlet_id == $value->id) ? 'selected' : ''; ?>>
                                        <?php echo escape_output($value->outlet_name) ?>
                                    </option>
                                <?php endif; } ?>
                            </select> -->

                            <select class="form-control select2" name="to_outlet_id" id="to_outlet_id" <?= $disable_to_outlet ? 'disabled' : '' ?>>
                                <option value=""><?php echo lang('select'); ?></option>
                                <?php
                                    $outlet_id_actual = $this->session->userdata('outlet_id');
                                    $db_key_actual = isset($db_key_actual) ? $db_key_actual : 'default';

                                    // Datos de la transferencia actual
                                    $to_outlet_id_selected   = isset($transfer_details) ? $transfer_details->to_outlet_id : '';
                                    $to_db_key_selected     = isset($transfer_details) ? $transfer_details->to_db_key : '';
                                    $remote_outlet_id_selected   = isset($transfer_details) ? $transfer_details->remote_outlet_id : '';

                                    foreach ($outlets as $value):
                                        $id_parts = explode('|', $value->id);

                                        // LOCAL: solo id numérico
                                        if (count($id_parts) == 1) {
                                            $solo_id = $id_parts[0];
                                            // Omitir si es el outlet actual
                                            if ($outlet_id_actual == $solo_id && $db_key_actual == 'default') {
                                                continue;
                                            }

                                            // Marcar seleccionado si coincide el id y la transferencia es local
                                            $is_selected = (
                                                $to_outlet_id_selected == $solo_id &&
                                                (empty($to_db_key_selected) || $to_db_key_selected == 'default')
                                            );
                                        }
                                        // MULTI-DB: id|db_key|outlet_name|nombre_sistema
                                        else {

                                            $solo_id = $id_parts[0];
                                            $db_key = $id_parts[1];
                                            $outlet_name = isset($id_parts[2]) ? $id_parts[2] : '';
                                            $nombre_sistema = isset($id_parts[3]) ? $id_parts[3] : '';

                                            // Omitir si es el outlet actual en la misma db
                                            if ($outlet_id_actual == $solo_id && $db_key_actual == $db_key) {
                                                continue;
                                            }

                                            $is_selected =
                                                ($remote_outlet_id_selected == $solo_id) &&
                                                ($to_db_key_selected == $db_key);
                                        }
                                ?>
                                    <option value="<?php echo escape_output($value->id); ?>"
                                        <?php echo $is_selected ? 'selected' : ''; ?>>
                                        <?php echo escape_output($value->outlet_name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($disable_to_outlet): ?>
                            <input type="hidden" name="to_outlet_id" id="to_outlet_id_hidden" value="<?= $transfer_details ? $transfer_details->to_outlet_id : '' ?>">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-3">
                            <label><?php echo lang('status'); ?></label>
                            <select class="form-control select2" name="status" id="status"
                                <?= $status_editable ? '' : 'disabled' ?>>
                                <?php if($status_editable_emisor): ?>
                                    <option value="2" <?= $status==2?'selected':''; ?>><?php echo lang('Draft'); ?></option>
                                    <option value="3" <?= $status==3?'selected':''; ?>><?php echo lang('Sent'); ?></option>
                                <?php endif; ?>
                                <?php if($status_editable_receptor): ?>
                                    <option value="1" <?= $status==1?'selected':''; ?>><?php echo lang('Received'); ?></option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <input type="hidden" id="transfer_id_dinamico" value="<?= isset($transfer_id)?$transfer_id:'' ?>">
 
                    <div class="row mb-3">
                        <div class="col-md-6 mt-2">
                            <?php if ($es_emisor): ?>
                                <label><?php echo lang('note_for_receiver'); ?></label>
                                <textarea class="form-control" id="note_for_receiver_dinamico" name="note_for_receiver" <?= $nota_editable?'':'readonly' ?>><?php echo isset($note_for_receiver)?$note_for_receiver:''; ?></textarea>
                            <?php elseif ($es_receptor): ?>
                                <label><?php echo lang('note_for_sender'); ?></label>
                                <textarea class="form-control" id="note_for_sender_dinamico" name="note_for_sender" <?= $nota_editable?'':'readonly' ?>><?php echo isset($note_for_sender)?$note_for_sender:''; ?></textarea>
                            <?php endif; ?>
                        </div>
        
                        <div class="col-md-6 ">
                            <br><br>
                            <button type="button" class="btn btn-primary" id="btn_guardar_transfer" <?= $transfer_editable?'':'disabled' ?>>
                                <i class="fa fa-save"></i> Guardar Cambios
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            <?php if($detalle_editable): ?>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Código</label>
                        <div style="position:relative;">
                            <div class="input-group">
                                <input type="text" id="codigo_busqueda" class="form-control" placeholder="Scan o teclear código/nombre" autocomplete="off" autofocus>
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
                        <label>Stock</label>
                        <input type="text" id="qty_stock" class="form-control" readonly>
                    </div>
                    <div class="col-md-2">
                        <label>Cantidad</label>
                        <input type="number" min="0.01" step="any" id="qty_transfer" class="form-control"
                            <?= $detalle_editable ? '' : 'readonly' ?>>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" id="btn_agregar_detalle" class="btn btn-success w-100" <?= $detalle_editable ? '' : 'disabled' ?>>Agregar</button>
                    </div>
                </div>
            <?php endif; ?>
            <div class="table-responsive">
                <table class="table" id="tabla_transfer_dinamico">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Producto</th>
                            <th>Código</th>
                            <th>Cantidad</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<script>
window.detalle_editable = <?= $detalle_editable ? 'true' : 'false' ?>;
window.es_emisor = <?= $es_emisor ? 'true' : 'false' ?>;
window.es_receptor = <?= $es_receptor ? 'true' : 'false' ?>;
</script>