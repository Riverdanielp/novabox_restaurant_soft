<style>
    .seccion-factura { border: 1px solid #ddd; border-radius: 5px; padding: 20px; margin-bottom: 25px; background: #fdfdfd; }
    .seccion-factura h4 { border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; margin-bottom: 20px; font-size: 1.1rem; }
    label { font-weight: 600; margin-bottom: 0.5rem; }
    .autocomplete-results { position: absolute; background-color: white; border: 1px solid #ddd; z-index: 1000; max-height: 200px; overflow-y: auto; width: 100%; }
    .autocomplete-item { padding: 8px 12px; cursor: pointer; }
    .autocomplete-item:hover { background-color: #f0f0f0; }
    .position-relative { position: relative; }
    .item-detalles-avanzados, .pago-detalles-avanzados { display: none; background-color: #f7f7f7; padding: 15px; margin-top: 10px; border-radius: 4px; }
</style>

<section class="main-content-wrapper">
    <section class="content-header">
        <h3 class="top-left-header"><?php echo $form_title; ?></h3>
    </section>

    <div class="box-wrapper">
    <?php 
        // Construir la URL de acción dinámicamente
        $action_url = $is_edit 
            ? base_url('Facturacion_py/procesar_formulario/' . $factura->id) 
            : base_url('Facturacion_py/procesar_formulario');
            
        echo form_open($action_url, ['id' => 'form-factura']); 
    ?>
        
        <!-- SECCIÓN 1: DATOS DEL DOCUMENTO -->
        <div class="seccion-factura"><h4><i data-feather="file-text"></i> Datos del Documento</h4><div class="row g-3">
            <div class="col-md-3"><label>Tipo Documento (*)</label><select name="tipoDocumento" class="form-control" required><?php foreach($tipos_documento as $k => $v) echo "<option value='{$k}'>{$v}</option>"; ?></select></div>
            <!-- CORREGIDO: Selector de Punto de Expedición agrupado por Sucursal -->
            <div class="col-md-3">
                <label>Sucursal y Punto (*)</label>
                <select name="punto" class="form-control" required>
                    <?php foreach($sucursales_con_puntos as $sucursal): ?>
                        <optgroup label="<?php echo html_escape($sucursal->nombre); ?>">
                            <?php foreach($sucursal->puntos as $punto): ?>
                                <option value="<?php echo $punto->codigo_punto; ?>"><?php echo "Punto {$punto->codigo_punto} - {$punto->nombre}"; ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label>Fecha Emisión (*)</label>
                <input type="datetime-local" name="fecha" class="form-control" 
                    value="<?php echo date('Y-m-d\TH:i:s'); ?>" 
                    step="1" required>
            </div>
            <div class="col-md-3"><label>Moneda (*)</label><select name="moneda" class="form-control" required><option value="PYG">PYG - Guaraní</option></select></div>
            <div class="col-md-3"><label>Tipo Emisión (*)</label><select name="tipoEmision" class="form-control" required><?php foreach($tipos_emision as $k => $v) echo "<option value='{$k}'>{$v}</option>"; ?></select></div>
            <div class="col-md-3"><label>Tipo Transacción (*)</label><select name="tipoTransaccion" class="form-control" required><?php foreach($tipos_transaccion as $k => $v) echo "<option value='{$k}'>{$v}</option>"; ?></select></div>
            <div class="col-md-3"><label>Tipo Impuesto (*)</label><select name="tipoImpuesto" class="form-control" required><?php foreach($tipos_impuesto as $k => $v) echo "<option value='{$k}'>{$v}</option>"; ?></select></div>
            <div class="col-md-3"><label>Indicador Presencia (*)</label><select name="factura[presencia]" class="form-control" required><?php foreach($tipos_presencia as $k => $v) echo "<option value='{$k}'>{$v}</option>"; ?></select></div>
            <div class="col-md-6"><label>Descripción (Opcional)</label><input type="text" name="descripcion" class="form-control" placeholder="Ej: Venta de productos varios"></div>
            <div class="col-md-6"><label>Observación (Opcional)</label><input type="text" name="observacion" class="form-control" placeholder="Ej: Promociones, marketing, etc."></div>
        </div></div>

        <!-- SECCIÓN 2: DATOS DEL CLIENTE -->
        <div class="seccion-factura"><h4><i data-feather="user"></i> Datos del Cliente</h4><div class="row g-3">
            <div class="col-md-3 position-relative"><label>Buscar (RUC/Nombre)</label><input type="text" id="cliente_search_input" class="form-control" placeholder="Escriba para buscar..."><div id="cliente_results" class="autocomplete-results"></div></div>
            <div class="col-md-3"><label>RUC / C.I. (*)</label><div class="input-group"><input type="text" name="cliente[ruc]" id="cliente_ruc" class="form-control" required><button class="btn btn-outline-secondary" type="button" id="ruc_search_btn">API</button></div><small id="ruc_message" class="form-text"></small></div>
            <div class="col-md-3"><label>Razón Social (*)</label><input type="text" name="cliente[razonSocial]" id="cliente_razonSocial" class="form-control" required></div>
            <div class="col-md-3"><label>Nombre Fantasía</label><input type="text" name="cliente[nombreFantasia]" id="cliente_nombreFantasia" class="form-control"></div>
            <div class="col-md-2"><label>Tipo Operación (*)</label><select name="cliente[tipoOperacion]" class="form-control" required><?php foreach($tipos_operacion_cliente as $k => $v) echo "<option value='{$k}'>{$v}</option>"; ?></select></div>
            <div class="col-md-2"><label>Tipo Contribuyente (*)</label><select name="cliente[tipoContribuyente]" id="cliente_tipoContribuyente" class="form-control" required><option value="1">Persona Física</option><option value="2">Persona Jurídica</option></select></div>
            <div class="col-md-2"><label>Tipo Documento (*)</label><select name="cliente[documentoTipo]" class="form-control" required><?php foreach($tipos_doc_cliente as $k => $v) echo "<option value='{$k}'>{$v}</option>"; ?></select></div>
            <div class="col-md-3"><label>Email</label><input type="email" name="cliente[email]" id="cliente_email" class="form-control"></div>
            <div class="col-md-3"><label>Teléfono/Celular</label><input type="text" name="cliente[telefono]" id="cliente_telefono" class="form-control"></div>
            <div class="col-md-4"><label>Dirección</label><input type="text" name="cliente[direccion]" id="cliente_direccion" class="form-control"></div>
            <div class="col-md-1"><label>N° Casa (*)</label><input type="text" name="cliente[numeroCasa]" id="cliente_numeroCasa" class="form-control" value="0" required></div>
            <div class="col-md-2"><label>Departamento</label><select name="cliente[departamento]" id="cliente_departamento" class="form-control"><?php foreach($departamentos as $d) echo "<option value='{$d->id}'>{$d->nombre}</option>"; ?></select></div>
            <div class="col-md-2"><label>Distrito</label><select name="cliente[distrito]" id="cliente_distrito" class="form-control" disabled></select></div>
            <div class="col-md-2"><label>Ciudad</label><select name="cliente[ciudad]" id="cliente_ciudad" class="form-control" disabled></select></div>
            <input type="hidden" name="cliente[codigo]" id="cliente_codigo">
        </div></div>

        <!-- SECCIÓN 3: DATOS DEL USUARIO (Vendedor) -->
        <div class="seccion-factura"><h4><i data-feather="briefcase"></i> Datos del Usuario (Vendedor)</h4><div class="row g-3">
            <div class="col-md-4 position-relative"><label>Buscar Usuario</label><input type="text" id="usuario_search_input" class="form-control" placeholder="Buscar por Nombre o Documento..."><div id="usuario_results" class="autocomplete-results"></div></div>
            <div class="col-md-2"><label>Documento N° (*)</label><input type="text" name="usuario[documentoNumero]" id="usuario_documentoNumero" class="form-control" required></div>
            <div class="col-md-3"><label>Nombre (*)</label><input type="text" name="usuario[nombre]" id="usuario_nombre" class="form-control" required></div>
            <div class="col-md-3"><label>Cargo (*)</label><input type="text" name="usuario[cargo]" id="usuario_cargo" class="form-control" value="Vendedor" required></div>
        </div></div>

        <!-- SECCIÓN 4: ITEMS -->
        <div class="seccion-factura"><h4><i data-feather="shopping-cart"></i> Items de la Factura</h4><div id="items-container"></div><button type="button" class="btn btn-default mt-2" id="add-item-btn"><i data-feather="plus"></i> Añadir Item</button><h3 class="text-end mt-3">Total Factura: <span id="total_general">0.00</span> Gs.</h3></div>

        <!-- SECCIÓN 5: CONDICIÓN DE VENTA -->
        <div class="seccion-factura"><h4><i data-feather="dollar-sign"></i> Condición de Venta</h4><div class="row"><div class="col-md-4"><label>Condición (*)</label><select name="condicion[tipo]" id="condicion_tipo" class="form-control" required><option value="1">Contado</option><option value="2">Crédito</option></select></div></div>
            <!-- <div id="contado-fields"><hr><h5>Pagos Recibidos (Entregas)</h5><div id="pagos-container"></div><button type="button" class="btn btn-default btn-sm mt-2" id="add-pago-btn"><i data-feather="plus"></i> Añadir Forma de Pago</button><h5 class="text-end mt-3 text-success">Total Pagado: <span id="total_pagado">0.00</span> Gs.</h5><h5 class="text-end text-info">Vuelto: <span id="vuelto">0.00</span> Gs.</h5></div> -->
             <!-- Agregar después del total pagado -->
            <div id="contado-fields">
                <hr>
                <h5>Pagos Recibidos (Entregas)</h5>
                <div id="pagos-container"></div>
                <button type="button" class="btn btn-default btn-sm mt-2" id="add-pago-btn">
                    <i data-feather="plus"></i> Añadir Forma de Pago
                </button>
                <h5 class="text-end mt-3 text-success">Total Pagado: <span id="total_pagado">0.00</span> Gs.</h5>
                <h5 class="text-end text-info">Vuelto: <span id="vuelto">0.00</span> Gs.</h5>
                <p id="mensaje-validacion-pago" class="text-center mt-2"></p>
            </div>
            <div id="credito-fields" style="display: none;"><hr><h5>Detalles del Crédito</h5><div class="row g-3"><div class="col-md-3"><label>Tipo Crédito (*)</label><select name="credito[tipo]" class="form-control"><option value="1">Plazo</option><option value="2">Cuotas</option></select></div><div class="col-md-3"><label>Plazo</label><input type="text" name="credito[plazo]" class="form-control" placeholder="Ej: 30 días, 60 días"></div><div class="col-md-3"><label>N° Cuotas</label><input type="number" name="credito[cuotas]" id="credito_nro_cuotas" class="form-control" value="1"></div><div class="col-md-3"><label>Monto Entrega Inicial</label><input type="number" name="credito[montoEntrega]" class="form-control" value="0"></div></div><div id="cuotas-container" class="mt-3"></div><button type="button" class="btn btn-default btn-sm mt-2" id="add-cuota-btn"><i data-feather="plus"></i> Añadir Cuota</button></div>
        </div>

        <div class="box-footer"><button type="submit" name="submit" value="submit" class="btn bg-blue-btn me-2"><i data-feather="save"></i> Generar Factura</button><a class="btn bg-blue-btn" href="<?php echo base_url('Facturacion_py/listado'); ?>"><i data-feather="corner-up-left"></i> Volver</a></div>
    <?php echo form_close(); ?>
    </div>
</section>

<!-- TEMPLATES PARA JS -->
<template id="item-row-template">
    <div class="item-row-wrapper mb-3 border p-3 rounded">
        <div class="row item-row align-items-center">
            <input type="hidden" name="items[{index}][codigo]" class="item-codigo">
            <div class="col-md-3 position-relative"><label>Descripción (*)</label><input type="text" name="items[{index}][descripcion]" class="form-control item-description-input" required><div class="autocomplete-results item-results"></div></div>
            <div class="col-md-1"><label>Cant. (*)</label><input type="number" name="items[{index}][cantidad]" class="form-control item-qty" value="1" step="any" required></div>
            <div class="col-md-2"><label>P. Unit. (*)</label><input type="number" name="items[{index}][precioUnitario]" class="form-control item-price" step="any" required></div>
            <div class="col-md-2"><label>Subtotal</label><p class="form-control-static item-subtotal fw-bold">0.00</p></div>
            <div class="col-auto ms-auto"><label>&nbsp;</label>
            <div>
                <button type="button" class="btn btn-secondary btn-sm btn-toggle-advanced" title="Más Opciones">
                    <i data-feather="more-horizontal"></i>
                </button>
                    <button type="button" class="btn btn-danger btn-sm btn-remove-item" title="Eliminar Item">
                        <i data-feather="trash"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="item-detalles-avanzados row g-3">

            <div class="col-md-2">
                <label>Tipo IVA (*)</label>
                <select name="items[{index}][ivaTipo]" class="form-control item-iva-tipo" required>
                    <?php foreach($tipos_iva_item as $k => $v) echo "<option value='{$k}'>{$v}</option>"; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label>% IVA (*)</label>
                <select name="items[{index}][iva]" class="form-control item-iva" required>
                    <option value="10">10%</option>
                    <option value="5">5%</option>
                    <option value="0">Exenta (0%)</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>Base Imponible % (*)</label>
                <input type="number" name="items[{index}][ivaBase]" class="form-control item-iva-base" 
                value="100" min="1" max="100" required>
            </div>
            <div class="col-md-2">
                <label>NCM</label><input type="text" name="items[{index}][ncm]" class="form-control">
            </div>
            <div class="col-md-2">
                <label>Lote</label><input type="text" name="items[{index}][lote]" class="form-control">
            </div>
            <div class="col-md-2">
                <label>Vencimiento</label><input type="date" name="items[{index}][vencimiento]" class="form-control">
            </div>
            <div class="col-md-3">
                <label>Observación Item</label><input type="text" name="items[{index}][observacion]" class="form-control">
            </div>
        </div>
    </div>
</template>
<template id="pago-row-template"><div class="row pago-row align-items-end g-3 mb-2"><div class="col-md-3"><label>Forma de Pago</label><select name="condicion[entregas][{index}][tipo]" class="form-control pago-tipo"><?php foreach($tipos_pago as $k => $v) echo "<option value='{$k}'>{$v}</option>"; ?></select></div><div class="col-md-3"><label>Monto (*)</label><input type="number" name="condicion[entregas][{index}][monto]" class="form-control pago-monto" step="any" required></div><div class="col-md-5 pago-detalles-avanzados"></div><div class="col-md-1"><button type="button" class="btn btn-danger btn-sm btn-remove-pago w-100"><i data-feather="trash"></i></button></div></div></template>
<template id="cuota-row-template"><div class="row cuota-row align-items-end g-3 mb-2"><div class="col-md-4"><label>Vencimiento (*)</label><input type="date" name="credito[infoCuotas][{index}][vencimiento]" class="form-control" required></div><div class="col-md-4"><label>Monto (*)</label><input type="number" name="credito[infoCuotas][{index}][monto]" class="form-control" step="any" required></div><div class="col-md-1"><button type="button" class="btn btn-danger btn-sm btn-remove-cuota w-100"><i data-feather="trash"></i></button></div></div></template>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrf_token_name = '<?php echo $this->security->get_csrf_token_name(); ?>';
    const csrf_hash = '<?php echo $this->security->get_csrf_hash(); ?>';
    let searchTimeout;

    // --- LÓGICA DE BÚSQUEDA GENÉRICA (AUTOCOMPLETE) ---
    function setupAutocomplete(inputId, resultsId, url, processFn, selectFn) {
        const input = document.getElementById(inputId);
        const results = document.getElementById(resultsId);
        if (!input) return;

        input.addEventListener('keyup', function() {
            clearTimeout(searchTimeout);
            const term = input.value;
            if (term.length < 2) {
                results.style.display = 'none';
                return;
            }
            searchTimeout = setTimeout(() => {
                const formData = new FormData();
                formData.append('term', term);
                formData.append(csrf_token_name, csrf_hash);
                fetch(url, { method: 'POST', body: formData }).then(r => r.json()).then(data => {
                    results.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(item => {
                            const div = document.createElement('div');
                            div.className = 'autocomplete-item';
                            div.innerHTML = processFn(item);
                            div.addEventListener('click', () => {
                                selectFn(item);
                                results.style.display = 'none';
                            });
                            results.appendChild(div);
                        });
                        results.style.display = 'block';
                    } else {
                        results.innerHTML = '<div class="autocomplete-item text-muted">No se encontraron resultados.</div>';
                        results.style.display = 'block';
                    }
                });
            }, 300);
        });
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.position-relative')) {
                results.style.display = 'none';
            }
        });
    }

    // --- SECCIÓN: CLIENTE Y UBICACIÓN ---
    setupAutocomplete('cliente_search_input', 'cliente_results', "<?php echo base_url('Facturacion_py/ajax_buscar_py_clientes'); ?>",
        item => `${item.razon_social} (${item.ruc})`,
        item => {
            document.getElementById('cliente_search_input').value = item.razon_social;
            document.getElementById('cliente_ruc').value = item.ruc;
            document.getElementById('cliente_razonSocial').value = item.razon_social;
            document.getElementById('cliente_tipoContribuyente').value = tipoContribuyente(item.ruc);
            document.getElementById('cliente_direccion').value = item.direccion;
            document.getElementById('cliente_numeroCasa').value = item.numero_casa || '0';
            document.getElementById('cliente_email').value = item.email;
            document.getElementById('cliente_telefono').value = item.telefono || item.celular;
            document.getElementById('cliente_codigo').value = item.codigo;
            document.getElementById('cliente_departamento').value = item.departamento;
            fetchDistritos(item.departamento, item.distrito);
        }
    );

    document.getElementById('ruc_search_btn').addEventListener('click', function() {
        const rucInput = document.getElementById('cliente_ruc');
        const ruc = rucInput.value.split('-')[0];
        const msg = document.getElementById('ruc_message');

        if (ruc.length < 5) return;
        msg.textContent = 'Buscando...';
        msg.className = 'form-text text-warning';
        
        const formData = new FormData();
        formData.append('ruc', ruc);
        formData.append(csrf_token_name, csrf_hash);

        fetch("<?php echo base_url('Facturacion_py/ajax_consultar_ruc'); ?>", { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.error || !data.ruc) {
                msg.textContent = data.error || 'No encontrado.';
                msg.className = 'form-text text-danger';
            } else {
                rucInput.value = `${data.ruc}-${data.dv}`;
                document.getElementById('cliente_razonSocial').value = data.nombre + ' ' + data.apellido || '';
                document.getElementById('cliente_search_input').value = data.nombre + ' ' + data.apellido || '';
                document.getElementById('cliente_tipoContribuyente').value = tipoContribuyente(rucInput.value);
                msg.textContent = 'RUC encontrado!';
                msg.className = 'form-text text-success';
            }
        });
    });
    
    document.getElementById('cliente_departamento').addEventListener('change', function() { fetchDistritos(this.value); });
    document.getElementById('cliente_distrito').addEventListener('change', function() { fetchCiudades(this.value); });

    function fetchDistritos(deptoId, selectedId = null) {
        const distSelect = document.getElementById('cliente_distrito');
        const ciuSelect = document.getElementById('cliente_ciudad');
        if (!deptoId) {
            distSelect.innerHTML = '';
            distSelect.disabled = true;
            ciuSelect.innerHTML = '';
            ciuSelect.disabled = true;
            return;
        }
        fetch(`<?php echo base_url('Facturacion_py/ajax_get_distritos'); ?>?departamento_id=${deptoId}`)
        .then(res => res.json()).then(data => {
            distSelect.innerHTML = '<option value="">Seleccione Distrito</option>';
            data.forEach(d => distSelect.innerHTML += `<option value="${d.id}">${d.nombre}</option>`);
            distSelect.disabled = false;
            if (selectedId) {
                distSelect.value = selectedId;
                // Almacenar el ID de la ciudad para usarlo después de que los distritos carguen
                document.getElementById('cliente_ciudad').dataset.selected = ''; // Limpiar
                if (document.querySelector(`option[value='${selectedId}']`)) {
                    fetchCiudades(selectedId, null); // Pasar el ID de ciudad guardado si existe
                }
            }
        });
    }

    function fetchCiudades(distId, selectedId = null) {
        const ciuSelect = document.getElementById('cliente_ciudad');
        if (!distId) {
            ciuSelect.innerHTML = '';
            ciuSelect.disabled = true;
            return;
        }
        fetch(`<?php echo base_url('Facturacion_py/ajax_get_ciudades'); ?>?distrito_id=${distId}`)
        .then(res => res.json()).then(data => {
            ciuSelect.innerHTML = '<option value="">Seleccione Ciudad</option>';
            data.forEach(c => ciuSelect.innerHTML += `<option value="${c.id}">${c.nombre}</option>`);
            ciuSelect.disabled = false;
            if (selectedId) {
                ciuSelect.value = selectedId;
            }
        });
    }

    function tipoContribuyente(ruc) {
        let limpio = ruc.toString().replace(/[\s\.\-]/g, "");
        let numero = parseInt(limpio, 10);
        return (isNaN(numero) || numero < 80000000) ? 1 : 2;
    }

    // --- SECCIÓN: USUARIO ---
    setupAutocomplete('usuario_search_input', 'usuario_results', "<?php echo base_url('Facturacion_py/ajax_buscar_py_usuarios'); ?>",
        item => `${item.nombre} (${item.documento_numero})`,
        item => {
            document.getElementById('usuario_search_input').value = item.nombre;
            document.getElementById('usuario_documentoNumero').value = item.documento_numero;
            document.getElementById('usuario_nombre').value = item.nombre;
            document.getElementById('usuario_cargo').value = item.cargo;
        }
    );

    // --- SECCIÓN: ITEMS ---
    let itemIndex = 0;
    document.getElementById('add-item-btn').addEventListener('click', addNewItem);
    function addNewItem() {
        let template = document.getElementById('item-row-template').innerHTML.replace(/{index}/g, itemIndex++);
        document.getElementById('items-container').insertAdjacentHTML('beforeend', template);
        feather.replace();
    }
    addNewItem();

    document.getElementById('items-container').addEventListener('click', function(e){
        const advancedBtn = e.target.closest('.btn-toggle-advanced');
        if(advancedBtn){
            const wrapper = advancedBtn.closest('.item-row-wrapper');
            const advancedFields = wrapper.querySelector('.item-detalles-avanzados');
            advancedFields.style.display = advancedFields.style.display === 'none' ? 'flex' : 'none';
        }
    });

    document.getElementById('items-container').addEventListener('keyup', function(e) {
        if (e.target && e.target.classList.contains('item-description-input')) {
            const input = e.target;
            const resultsDiv = input.nextElementSibling;
            const row = input.closest('.item-row-wrapper');
            
            clearTimeout(searchTimeout);
            if (input.value.length < 2) { resultsDiv.style.display = 'none'; return; }
            
            searchTimeout = setTimeout(() => {
                const formData = new FormData();
                formData.append('term', input.value);
                formData.append(csrf_token_name, csrf_hash);

                fetch("<?php echo base_url('Facturacion_py/ajax_buscar_py_items'); ?>", { method: 'POST', body: formData })
                .then(res => res.json()).then(data => {
                    resultsDiv.innerHTML = '';
                    data.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'autocomplete-item';
                        div.innerHTML = `(${item.codigo}) ${item.descripcion}`;
                        div.addEventListener('click', () => {
                            input.value = item.descripcion;
                            row.querySelector('.item-codigo').value = item.codigo;
                            row.querySelector('.item-price').value = parseFloat(item.precio_unitario).toFixed(2);
                            row.querySelector('.item-iva-tipo').value = item.iva_tipo;
                            row.querySelector('.item-iva').value = item.iva;
                            resultsDiv.style.display = 'none';
                            updateTotals();
                        });
                        resultsDiv.appendChild(div);
                    });
                    resultsDiv.style.display = 'block';
                });
            }, 300);
        }
    });

    // --- SECCIÓN: PAGOS Y CUOTAS ---
    let pagoIndex = 0;
    document.getElementById('add-pago-btn').addEventListener('click', addNewPago);

        // Agregar esta función para obtener el total pendiente de pago
    function getTotalPendiente() {
        const totalGeneral = parseFloat(document.getElementById('total_general').textContent) || 0;
        let totalPagado = 0;
        document.querySelectorAll('.pago-monto').forEach(pago => {
            if (pago === document.activeElement) return; // Ignorar el campo actual si está en foco
            totalPagado += parseFloat(pago.value) || 0;
        });
        return Math.max(0, totalGeneral - totalPagado);
    }

    // Modificar la función addNewPago para autocompletar con el total pendiente
    function addNewPago() {
        let template = document.getElementById('pago-row-template').innerHTML.replace(/{index}/g, pagoIndex++);
        document.getElementById('pagos-container').insertAdjacentHTML('beforeend', template);
        
        // Auto-completar con el total pendiente
        const newPagoInput = document.querySelector('.pago-row:last-child .pago-monto');
        if (newPagoInput) {
            newPagoInput.value = getTotalPendiente().toFixed(2);
            updateTotals(); // Actualizar totales después de añadir
        }
        
        feather.replace();
    }

    // Agregar validación al formulario antes de enviar
    document.getElementById('form-factura').addEventListener('submit', function(e) {
        // 1. Verificar que hay al menos un item
        if (document.querySelectorAll('.item-row-wrapper').length === 0) {
            e.preventDefault();
            alert('Debe agregar al menos un ítem a la factura.');
            return false;
        }
        
        // 2. Verificar si la condición es contado, que el total de pagos sea igual al total de la factura
        const condicionTipo = document.getElementById('condicion_tipo').value;
        if (condicionTipo == 1) { // Si es contado
            const totalGeneral = parseFloat(document.getElementById('total_general').textContent) || 0;
            const totalPagado = parseFloat(document.getElementById('total_pagado').textContent) || 0;
            
            if (totalPagado < totalGeneral) {
                e.preventDefault();
                alert(`El total pagado (${totalPagado.toFixed(2)}) debe ser igual o mayor al total de la factura (${totalGeneral.toFixed(2)})`);
                return false;
            }
            
            // 3. Verificar que hay al menos una forma de pago
            if (document.querySelectorAll('.pago-row').length === 0) {
                e.preventDefault();
                alert('Debe agregar al menos una forma de pago.');
                return false;
            }
        }
        
        // 4. Si es crédito, verificar que hay al menos una cuota
        if (condicionTipo == 2 && document.querySelectorAll('.cuota-row').length === 0) {
            e.preventDefault();
            alert('Debe agregar al menos una cuota para la condición de crédito.');
            return false;
        }
        
        // Todo está correcto, permitir envío
        return true;
    });

    // Mejorar la función updateTotals para mostrar mensajes de validación en tiempo real
    function updateTotals() {
        let totalGeneral = 0;
        document.querySelectorAll('.item-row-wrapper').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const subtotal = qty * price;
            row.querySelector('.item-subtotal').textContent = subtotal.toFixed(2);
            totalGeneral += subtotal;
        });
        document.getElementById('total_general').textContent = totalGeneral.toFixed(2);

        let totalPagado = 0;
        document.querySelectorAll('.pago-monto').forEach(pago => {
            totalPagado += parseFloat(pago.value) || 0;
        });
        document.getElementById('total_pagado').textContent = totalPagado.toFixed(2);
        
        const vueltoElement = document.getElementById('vuelto');
        const vuelto = totalPagado - totalGeneral;
        vueltoElement.textContent = (vuelto > 0) ? vuelto.toFixed(2) : '0.00';
        
        // Mostrar advertencia si el pago es insuficiente
        const mensajeValidacion = document.getElementById('mensaje-validacion-pago');
        if (mensajeValidacion) {
            if (totalPagado < totalGeneral) {
                mensajeValidacion.textContent = `Faltan ${(totalGeneral - totalPagado).toFixed(2)} Gs. por pagar`;
                mensajeValidacion.className = 'text-danger';
            } else {
                mensajeValidacion.textContent = 'Pago completo';
                mensajeValidacion.className = 'text-success';
            }
        }
    }

    document.getElementById('pagos-container').addEventListener('change', function(e) {
        if(e.target.classList.contains('pago-tipo')) {
            const detailsContainer = e.target.closest('.pago-row').querySelector('.pago-detalles-avanzados');
            detailsContainer.innerHTML = '';
            const index = e.target.name.match(/\[(\d+)\]/)[1];
            const tipoPago = e.target.value;

            if (tipoPago == 3 || tipoPago == 4) { // Tarjeta C/D
                detailsContainer.style.display = 'block';
                detailsContainer.innerHTML = `<div class="row g-3"><div class="col-md-4"><label>Procesadora</label><select name="condicion[entregas][${index}][infoTarjeta][tipo]" class="form-control"><?php foreach($tipos_tarjeta_procesadora as $k => $v) echo "<option value='{$k}'>{$v}</option>"; ?></select></div><div class="col-md-4"><label>RUC Procesadora</label><input type="text" name="condicion[entregas][${index}][infoTarjeta][ruc]" class="form-control"></div><div class="col-md-4"><label>Cód. Autorización</label><input type="text" name="condicion[entregas][${index}][infoTarjeta][codigoAutorizacion]" class="form-control"></div></div>`;
            } else if (tipoPago == 2) { // Cheque
                detailsContainer.style.display = 'block';
                detailsContainer.innerHTML = `<div class="row g-3"><div class="col-md-6"><label>N° Cheque</label><input type="text" name="condicion[entregas][${index}][infoCheque][numeroCheque]" class="form-control"></div><div class="col-md-6"><label>Banco</label><input type="text" name="condicion[entregas][${index}][infoCheque][banco]" class="form-control"></div></div>`;
            } else {
                detailsContainer.style.display = 'none';
            }
        }
    });
    
    let cuotaIndex = 0;
    document.getElementById('add-cuota-btn').addEventListener('click', addNewCuota);
    function addNewCuota() {
        let template = document.getElementById('cuota-row-template').innerHTML.replace(/{index}/g, cuotaIndex++);
        document.getElementById('cuotas-container').insertAdjacentHTML('beforeend', template);
        feather.replace();
    }
    
    document.getElementById('credito_nro_cuotas').addEventListener('change', function() {
        document.getElementById('cuotas-container').innerHTML = '';
        const nroCuotas = parseInt(this.value) || 0;
        for (let i = 0; i < nroCuotas; i++) { addNewCuota(); }
    });

    // --- SECCIÓN: CÁLCULOS Y EVENTOS GLOBALES ---
    // function updateTotals() {
    //     let totalGeneral = 0;
    //     document.querySelectorAll('.item-row-wrapper').forEach(row => {
    //         const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
    //         const price = parseFloat(row.querySelector('.item-price').value) || 0;
    //         const subtotal = qty * price;
    //         row.querySelector('.item-subtotal').textContent = subtotal.toFixed(2);
    //         totalGeneral += subtotal;
    //     });
    //     document.getElementById('total_general').textContent = totalGeneral.toFixed(2);

    //     let totalPagado = 0;
    //     document.querySelectorAll('.pago-monto').forEach(pago => {
    //         totalPagado += parseFloat(pago.value) || 0;
    //     });
    //     document.getElementById('total_pagado').textContent = totalPagado.toFixed(2);
    //     document.getElementById('vuelto').textContent = (totalPagado > totalGeneral) ? (totalPagado - totalGeneral).toFixed(2) : '0.00';
    // }
    
    document.addEventListener('input', e => {
        if(e.target.matches('.item-qty, .item-price, .pago-monto')) {
            updateTotals();
        }
    });
    
    document.addEventListener('click', e => { 
        const target = e.target.closest('.btn-remove-item, .btn-remove-pago, .btn-remove-cuota');
        if (target) {
            target.closest('.item-row-wrapper, .pago-row, .cuota-row').remove();
            updateTotals();
        }
    });

    // --- SECCIÓN: LÓGICA CONDICIONAL DE VISTA ---
    document.getElementById('condicion_tipo').addEventListener('change', function() {
        if (this.value == 2) { // Crédito
            document.getElementById('credito-fields').style.display = 'block';
            document.getElementById('contado-fields').style.display = 'none';
        } else { // Contado
            document.getElementById('credito-fields').style.display = 'none';
            document.getElementById('contado-fields').style.display = 'block';
        }
    });
    document.getElementById('condicion_tipo').dispatchEvent(new Event('change'));
});
</script>