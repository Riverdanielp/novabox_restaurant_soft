<style>
    .seccion-sifen { border: 1px solid #ddd; border-radius: 5px; padding: 20px; margin-bottom: 25px; }
    .seccion-sifen h4 { border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; margin-bottom: 20px; }
    .punto-row { background-color: #f9f9f9; border: 1px solid #eee; padding: 15px; margin-bottom: 10px; border-radius: 5px; }
</style>

<section class="main-content-wrapper">
    
    <?php if ($this->session->flashdata('exception')) {
        echo '<section class="alert-wrapper"><div class="alert alert-success alert-dismissible fade show"> 
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <div class="alert-body"><p><i class="m-right fa fa-check"></i>';
        echo escape_output($this->session->flashdata('exception'));
        echo '</p></div></div></section>';
    } ?>

    <section class="content-header">
        <h3 class="top-left-header">
            Configuración SIFEN para el Outlet: <?php echo escape_output($outlet_info->outlet_name); ?>
        </h3>
    </section>

    <div class="box-wrapper">
        <!-- SECCIÓN 1: VINCULACIÓN DE SUCURSAL SIFEN -->
        <div class="seccion-sifen">
            <h4><i data-feather="link"></i> 1. Vincular a Sucursal Fiscal (SIFEN)</h4>
            <?php echo form_open(base_url('Outlet/configuracionSifen/' . $encrypted_id)); ?>
                <div class="row align-items-end">
                    <div class="col-md-6">
                        <label>Seleccione la Sucursal Fiscal a la que pertenece este Outlet</label>
                        <select name="sifen_sucursal_id" class="form-control select2">
                            <option value="">Ninguna (No emite factura electrónica)</option>
                            <?php foreach($sifen_sucursales as $suc): ?>
                                <option value="<?php echo $suc->id; ?>" <?php echo ($outlet_info->sifen_sucursal_id == $suc->id) ? 'selected' : ''; ?>>
                                    <?php echo $suc->codigo_establecimiento; ?> - <?php echo $suc->nombre; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" name="submit_link" value="submit" class="btn bg-blue-btn"><i data-feather="save"></i> Guardar Vinculación</button>
                        <button type="button" class="btn btn-default" data-bs-toggle="modal" data-bs-target="#modalSucursal" data-id="" data-codigo="" data-nombre="" data-direccion="" data-telefono="">
                            <i data-feather="plus"></i> Añadir Sucursal Fiscal
                        </button>
                    </div>
                </div>
            <?php echo form_close(); ?>
        </div>

        <?php if ($sucursal_vinculada): // SOLO SI HAY UNA SUCURSAL VINCULADA, MOSTRAMOS EL RESTO ?>
        <hr>
        <!-- SECCIÓN 2: PUNTOS DE EXPEDICIÓN -->
        <?php echo form_open(base_url('Outlet/configuracionSifen/' . $encrypted_id)); ?>
            <input type="hidden" name="sucursal_id_hidden" value="<?php echo $sucursal_vinculada->id; ?>">
            <div class="seccion-sifen">
                <h4>
                    <i data-feather="printer"></i> 2. Puntos de Expedición de "<?php echo $sucursal_vinculada->nombre; ?>"
                    <button type="button" class="btn btn-sm btn-default float-end" data-bs-toggle="modal" data-bs-target="#modalSucursal"
                        data-id="<?php echo $sucursal_vinculada->id; ?>"
                        data-codigo="<?php echo $sucursal_vinculada->codigo_establecimiento; ?>"
                        data-nombre="<?php echo $sucursal_vinculada->nombre; ?>"
                        data-direccion="<?php echo $sucursal_vinculada->direccion; ?>"
                        data-telefono="<?php echo $sucursal_vinculada->telefono; ?>">
                        <i data-feather="edit"></i> Editar Datos de esta Sucursal Fiscal
                    </button>
                </h4>
                <div id="puntos-expedicion-container">
                    <?php if (!empty($puntos_expedicion)): ?>
                        <?php foreach ($puntos_expedicion as $index => $punto): ?>
                            <div class="punto-row">
                                <input type="hidden" name="puntos[<?php echo $index; ?>][id]" value="<?php echo $punto->id; ?>">
                                <div class="row align-items-end">
                                    <div class="col-md-2"><div class="form-group"><label>Código Punto <span class="required_star">*</span></label><input type="text" name="puntos[<?php echo $index; ?>][codigo_punto]" class="form-control" value="<?php echo $punto->codigo_punto; ?>" required></div></div>
                                    <div class="col-md-3"><div class="form-group"><label>Nombre Punto</label><input type="text" name="puntos[<?php echo $index; ?>][nombre]" class="form-control" value="<?php echo $punto->nombre; ?>"></div></div>
                                    <div class="col-md-2"><div class="form-group"><label>Correlativo Actual <span class="required_star">*</span></label><input type="number" name="puntos[<?php echo $index; ?>][numerador]" class="form-control" value="<?php echo $punto->numerador; ?>" required></div></div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Timbrado a Usar</label>
                                            <select name="puntos[<?php echo $index; ?>][timbrado_id]" class="form-control select2">
                                                <option value="">Ninguno</option>
                                                <?php foreach($timbrados_activos as $t_activo){ 
                                                    $selected = (isset($mapa_timbrados[$punto->id]) && $mapa_timbrados[$punto->id] == $t_activo->id) ? 'selected' : '';
                                                    $fecha_inicio_f = date('d/m/Y', strtotime($t_activo->fecha_inicio));
                                                    $fecha_fin_f = date('d/m/Y', strtotime($t_activo->fecha_fin));
                                                    echo '<option value="'.$t_activo->id.'" '.$selected.'>'.$t_activo->numero_timbrado.' ('.$fecha_inicio_f.' - '.$fecha_fin_f.')</option>';
                                                } ?>
                                            </select>
                                            <?php
                                            $timbrado_asociado_id = $mapa_timbrados[$punto->id] ?? null;
                                            $es_valido = false;
                                            if ($timbrado_asociado_id) { foreach($timbrados_activos as $t_activo) { if ($t_activo->id == $timbrado_asociado_id) { $es_valido = true; break; } } }
                                            if ($es_valido) { echo '<small class="text-success fw-bold">Timbrado Activo</small>'; } 
                                            else { echo '<small class="text-danger fw-bold">Sin timbrado activo asignado</small>'; }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-md-1"><div class="form-group"><label>Activo</label><input type="checkbox" name="puntos[<?php echo $index; ?>][activo]" value="1" <?php echo $punto->activo ? 'checked' : ''; ?>></div></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn btn-default" id="add-punto-btn"><i data-feather="plus"></i> Añadir Punto de Expedición</button>
            </div>
            <div class="box-footer">
                <button type="submit" name="submit_puntos" value="submit" class="btn bg-blue-btn me-2"><i data-feather="save"></i> Guardar Puntos de Expedición</button>
            </div>
        <?php echo form_close(); ?>

        <!-- SECCIÓN 3: GESTIÓN DE TIMBRADOS (sin cambios mayores) -->
        <div class="seccion-sifen">
            <h4><i data-feather="award"></i> 3. Gestión de Timbrados
                <button type="button" class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#modalAddTimbrado">
                    <i data-feather="plus"></i> Añadir Nuevo Timbrado
                </button>
            </h4>
            <table class="table">
                <thead><tr><th>Número</th><th>Inicio</th><th>Fin</th><th>Estado</th><th class="text-center">Acciones</th></tr></thead>
                <tbody>
                    <?php foreach($todos_los_timbrados as $t): ?>
                    <tr>
                        <td><?php echo $t->numero_timbrado; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($t->fecha_inicio)); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($t->fecha_fin)); ?></td>
                        <td><?php echo $t->activo ? '<span class="text-success">Activo</span>' : '<span class="text-danger">Inactivo</span>'; ?></td>
                        <td class="text-center">
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-primary btn-edit-timbrado" data-bs-toggle="modal" data-bs-target="#modalEditTimbrado" data-id="<?php echo $t->id; ?>" data-numero="<?php echo $t->numero_timbrado; ?>" data-inicio="<?php echo $t->fecha_inicio; ?>" data-fin="<?php echo $t->fecha_fin; ?>"><i data-feather="edit"></i></button>
                                <button type="button" class="btn btn-sm <?php echo $t->activo ? 'btn-danger' : 'btn-success'; ?> btn-toggle-status" data-id="<?php echo $t->id; ?>" data-status="<?php echo $t->activo ? '0' : '1'; ?>"><?php echo $t->activo ? '<i data-feather="x-circle"></i>' : '<i data-feather="check-circle"></i>'; ?></button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; // Fin del if($sucursal_vinculada) ?>

        <div class="box-footer">
            <a class="btn bg-blue-btn" href="<?php echo base_url() ?>Outlet/outlets"><i data-feather="corner-up-left"></i> Volver a Outlets</a>
        </div>
    </div>
</section>

<!-- MODALES -->
<!-- Modal para Añadir/Editar SUCURSAL SIFEN -->
<div class="modal fade" id="modalSucursal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Sucursal Fiscal (SIFEN)</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
          <input type="hidden" id="sifen_sucursal_id">
          <div class="form-group"><label>Código Establecimiento <span class="required_star">*</span></label><input type="text" id="sifen_codigo" class="form-control" required></div>
          <div class="form-group"><label>Nombre Sucursal <span class="required_star">*</span></label><input type="text" id="sifen_nombre" class="form-control" required></div>
          <div class="form-group"><label>Dirección</label><input type="text" id="sifen_direccion" class="form-control"></div>
          <div class="form-group"><label>Teléfono</label><input type="text" id="sifen_telefono" class="form-control"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="btn-save-sifen-sucursal">Guardar Sucursal</button>
      </div>
    </div>
  </div>
</div>

<!-- Modales para Timbrados (Add y Edit) van aquí, sin cambios -->
<!-- ... (pega aquí los dos modales de timbrados que ya tenías) ... -->
<!-- Modal para añadir Timbrado -->
<div class="modal fade" id="modalAddTimbrado" tabindex="-1" aria-labelledby="modalAddTimbradoLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalAddTimbradoLabel">Añadir Nuevo Timbrado</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="form-add-timbrado">
            <div class="form-group"><label>Número de Timbrado <span class="required_star">*</span></label><input type="text" id="numero_timbrado" class="form-control" required></div>
            <div class="form-group"><label>Fecha Inicio Vigencia <span class="required_star">*</span></label><input type="date" id="fecha_inicio" class="form-control" required></div>
            <div class="form-group"><label>Fecha Fin Vigencia <span class="required_star">*</span></label><input type="date" id="fecha_fin" class="form-control" required></div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="btn-save-timbrado">Guardar Timbrado</button>
      </div>
    </div>
  </div>
</div>
<!-- Modal para EDITAR Timbrado -->
<div class="modal fade" id="modalEditTimbrado" tabindex="-1" aria-labelledby="modalEditTimbradoLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEditTimbradoLabel">Editar Timbrado</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="form-edit-timbrado">
            <input type="hidden" id="edit_timbrado_id" value="">
            <div class="form-group">
                <label>Número de Timbrado <span class="required_star">*</span></label>
                <input type="text" id="edit_numero_timbrado" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Fecha Inicio Vigencia <span class="required_star">*</span></label>
                <input type="date" id="edit_fecha_inicio" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Fecha Fin Vigencia <span class="required_star">*</span></label>
                <input type="date" id="edit_fecha_fin" class="form-control" required>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="btn-update-timbrado">Guardar Cambios</button>
      </div>
    </div>
  </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfTokenName = '<?php echo $this->security->get_csrf_token_name(); ?>';
    const csrfTokenHash = '<?php echo $this->security->get_csrf_hash(); ?>';

    // --- LÓGICA PARA GESTIÓN DE SUCURSALES SIFEN ---
    const modalSucursal = document.getElementById('modalSucursal');
    modalSucursal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        document.getElementById('sifen_sucursal_id').value = button.dataset.id;
        document.getElementById('sifen_codigo').value = button.dataset.codigo;
        document.getElementById('sifen_nombre').value = button.dataset.nombre;
        document.getElementById('sifen_direccion').value = button.dataset.direccion;
        document.getElementById('sifen_telefono').value = button.dataset.telefono;
    });

    document.getElementById('btn-save-sifen-sucursal').addEventListener('click', function() {
        const formData = new FormData();
        formData.append('id', document.getElementById('sifen_sucursal_id').value);
        formData.append('codigo_establecimiento', document.getElementById('sifen_codigo').value);
        formData.append('nombre', document.getElementById('sifen_nombre').value);
        formData.append('direccion', document.getElementById('sifen_direccion').value);
        formData.append('telefono', document.getElementById('sifen_telefono').value);
        formData.append(csrfTokenName, csrfTokenHash);

        fetch('<?php echo base_url("Outlet/ajax_save_sifen_sucursal"); ?>', { method: 'POST', headers: {'X-Requested-With': 'XMLHttpRequest'}, body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') { alert(data.message); location.reload(); } 
            else { alert('Error: ' + data.message); }
        }).catch(err => console.error(err));
    });

    // --- LÓGICA PARA PUNTOS DE EXPEDICIÓN Y TIMBRADOS (si existen) ---
    <?php if ($sucursal_vinculada): ?>
        let puntoIndex = <?php echo count($puntos_expedicion); ?>;
        const timbrados_activos_json = <?php echo json_encode($timbrados_activos); ?>;
        
        document.getElementById('add-punto-btn').addEventListener('click', function() {
            const container = document.getElementById('puntos-expedicion-container');
            let timbradoOptions = '<option value="">Ninguno</option>';
            timbrados_activos_json.forEach(t => {
                const inicio_f = new Date(t.fecha_inicio + 'T00:00:00').toLocaleDateString('es-PY');
                const fin_f = new Date(t.fecha_fin + 'T00:00:00').toLocaleDateString('es-PY');
                timbradoOptions += `<option value="${t.id}">${t.numero_timbrado} (${inicio_f} - ${fin_f})</option>`;
            });

            const newRow = document.createElement('div');
            newRow.className = 'punto-row';
            newRow.innerHTML = `<input type="hidden" name="puntos[${puntoIndex}][id]" value=""><div class="row align-items-end">...</div>`; // (El innerHTML completo va aquí, es largo)
            const puntoHTML = `<input type="hidden" name="puntos[${puntoIndex}][id]" value=""><div class="row align-items-end"><div class="col-md-2"><div class="form-group"><label>Código Punto <span class="required_star">*</span></label><input type="text" name="puntos[${puntoIndex}][codigo_punto]" class="form-control" required></div></div><div class="col-md-3"><div class="form-group"><label>Nombre Punto</label><input type="text" name="puntos[${puntoIndex}][nombre]" class="form-control"></div></div><div class="col-md-2"><div class="form-group"><label>Correlativo Actual <span class="required_star">*</span></label><input type="number" name="puntos[${puntoIndex}][numerador]" class="form-control" value="0" required></div></div><div class="col-md-3"><div class="form-group"><label>Timbrado a Usar</label><select name="puntos[${puntoIndex}][timbrado_id]" class="form-control">${timbradoOptions}</select></div></div><div class="col-md-1"><div class="form-group"><label>Activo</label><input type="checkbox" name="puntos[${puntoIndex}][activo]" value="1" checked></div></div></div>`;
            newRow.innerHTML = puntoHTML;
            container.appendChild(newRow);
            puntoIndex++;
        });

        // El resto del JS para timbrados (add, edit, toggle) va aquí, sin cambios
        // Guardar nuevo timbrado
        document.getElementById('btn-save-timbrado').addEventListener('click', function() {
            const formData = new FormData();
            formData.append('numero_timbrado', document.getElementById('numero_timbrado').value);
            formData.append('fecha_inicio', document.getElementById('fecha_inicio').value);
            formData.append('fecha_fin', document.getElementById('fecha_fin').value);
            formData.append(csrfTokenName, csrfTokenHash);

            fetch('<?php echo base_url("Outlet/ajax_add_timbrado"); ?>', { method: 'POST', headers: {'X-Requested-With': 'XMLHttpRequest'}, body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') { alert(data.message); location.reload(); } 
                else { alert('Error: ' + data.message); }
            }).catch(err => console.error(err));
        });

        // Llenar modal de edición al hacer clic en el botón "Editar"
        document.querySelectorAll('.btn-edit-timbrado').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('edit_timbrado_id').value = this.dataset.id;
                document.getElementById('edit_numero_timbrado').value = this.dataset.numero;
                document.getElementById('edit_fecha_inicio').value = this.dataset.inicio;
                document.getElementById('edit_fecha_fin').value = this.dataset.fin;
            });
        });

        // Guardar cambios del timbrado editado
        document.getElementById('btn-update-timbrado').addEventListener('click', function() {
            const formData = new FormData();
            formData.append('id', document.getElementById('edit_timbrado_id').value);
            formData.append('numero_timbrado', document.getElementById('edit_numero_timbrado').value);
            formData.append('fecha_inicio', document.getElementById('edit_fecha_inicio').value);
            formData.append('fecha_fin', document.getElementById('edit_fecha_fin').value);
            formData.append(csrfTokenName, csrfTokenHash);

            fetch('<?php echo base_url("Outlet/ajax_edit_timbrado"); ?>', { method: 'POST', headers: {'X-Requested-With': 'XMLHttpRequest'}, body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') { alert(data.message); location.reload(); }
                else { alert('Error: ' + data.message); }
            }).catch(err => console.error(err));
        });

        // Cambiar estado (Activar/Desactivar)
        document.querySelectorAll('.btn-toggle-status').forEach(button => {
            button.addEventListener('click', function() {
                const newStatus = this.dataset.status;
                const actionText = newStatus === '1' ? 'activar' : 'desactivar';
                if (confirm(`¿Estás seguro de que quieres ${actionText} este timbrado?`)) {
                    const formData = new FormData();
                    formData.append('id', this.dataset.id);
                    formData.append('status', newStatus);
                    formData.append(csrfTokenName, csrfTokenHash);

                    fetch('<?php echo base_url("Outlet/ajax_toggle_timbrado_status"); ?>', { method: 'POST', headers: {'X-Requested-With': 'XMLHttpRequest'}, body: formData })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') { alert(data.message); location.reload(); }
                        else { alert('Error: ' + data.message); }
                    }).catch(err => console.error(err));
                }
            });
        });
    <?php endif; ?>
});
</script>