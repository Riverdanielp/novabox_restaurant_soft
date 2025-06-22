<style>
    .img-port {
        max-width: 50px;
        height: 50px;
        object-fit: cover;
        border: 2px solid #d8d8d8;
        border-radius: 5px;
        object-fit: cover;
    }
</style>
 <!-- Agrega estos estilos para los botones -->
<style>
    #headerSelectIVA {
        min-width: 120px; /* o lo que sea cómodo */
        min-height: 32px;
        z-index: 10;
        position: relative;
    }

    .btn-activar-balanza {
        background: #28a745; color: white; border: none; margin-right: 5px;
    }
    .btn-desactivar-balanza {
        background: #dc3545; color: white; border: none;
    }
    .check-col { width: 30px; text-align: center; }

    .switch {
    display: inline-flex;
    align-items: center;
    min-width: 90px;
    height: 28px;
}
.switch-label {
    font-size: 13px;
    font-weight: bold;
    color: #222;
    width: 50px;
    text-align: center;
    transition: opacity 0.2s;
    opacity: 0.5;
    user-select: none;
    display: none;
}
.switch-label.active {
    display: contents;
    opacity: 1;
}
.switch input {
    display: none;
}
.slider {
    position: relative;
    width: 40px;
    height: 22px;
    background: #ccc;
    border-radius: 22px;
    margin: 0 5px;
    transition: background 0.3s;
    flex-shrink: 0;
    display: inline-block;
    vertical-align: middle;
}
.slider:before {
    content: "";
    display: block;
    position: absolute;
    top: 2px;
    left: 2px;
    width: 18px;
    height: 18px;
    background: #fff;
    border-radius: 50%;
    transition: left 0.3s;
    box-shadow: 0 1px 4px rgba(0,0,0,0.12);
}
input:checked + .slider { background: #28a745; }
input:checked + .slider.pesable { background: #007bff; }
input:checked + .slider.unitario { background: #ffc107; }
input:checked + .slider:before {
    left: 20px;
}
</style>
<section class="main-content-wrapper">
    <?php
    if ($this->session->flashdata('exception')) {
        echo '<section class="alert-wrapper"><div class="alert alert-success alert-dismissible fade show"> 
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <div class="alert-body"><p><i class="m-right fa fa-check"></i>';
        echo escape_output($this->session->flashdata('exception')); unset($_SESSION['exception']);
        echo '</p></div></div></section>';
    }
    ?>

    <section class="content-header">
        <div class="row">
            <div class="col-md-auto">
                <h2 class="top-left-header"><?php echo lang('food_menus'); ?> - Config. Rápida </h2>
                <input type="hidden" class="datatable_name" data-title="<?php echo lang('food_menus'); ?> - Config. Rápida" data-id_name="datatable">
            </div>
            <div class=" col-md-auto">
                <div class="btn_list m-right d-flex">
                    <!-- Filtro de Categoría select2 -->
                    <form method="get" action="<?= base_url('FoodMenu/conf_rapida') ?>" class="form-inline d-flex align-items-end" id="filter-category-form" style="gap: 8px;">
                        <div class="form-group">
                            <label><?= lang('category'); ?></label>
                            <select class="form-control ir_w_100" id="category_id" name="category_id">
                                <option value=""><?= lang('select'); ?></option>
                                <?php foreach ($categories as $ctry) { ?>
                                <option value="<?= escape_output($ctry->id) ?>" <?= (isset($_GET['category_id']) && $_GET['category_id'] == $ctry->id) ? 'selected' : '' ?>>
                                    <?= escape_output($ctry->category_name) ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>
                        <button type="submit" class="btn bg-blue-btn menu_assign_class me-2" style="height: 38px;">Filtrar</button>
                        <?php if (!empty($_GET['category_id'])): ?>
                            <a href="<?= base_url('FoodMenu/conf_rapida') ?>" class="btn btn-secondary" style="height: 38px;">Limpiar</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <div class=" col-md-auto">
                <div class="btn_list m-right d-flex align-items-end" style="gap: 8px;">
                    <div class="form-group">
                        <a href="<?php echo base_url('FoodMenu/export_balanza_txt') ?>" class="btn btn-success menu_assign_class me-2">
                            Exportar productos para balanza (TXT)
                        </a>
                    </div>
                </div>
            </div>


            <div class=" col-md-auto">
                <?php
                    $showBalanza = (isset($_GET['balanza']) && $_GET['balanza'] == 1);
                    $baseParams = $_GET;
                    unset($baseParams['balanza']); // para el botón de 'ver todos'
                    $allUrl = base_url('FoodMenu/conf_rapida') . (!empty($baseParams) ? '?' . http_build_query($baseParams) : '');
                    $balanzaParams = $_GET;
                    $balanzaParams['balanza'] = 1;
                    $balanzaUrl = base_url('FoodMenu/conf_rapida') . '?' . http_build_query($balanzaParams);
                ?>
                <div class="btn_list m-right d-flex align-items-end" style="gap: 8px;">
                    <div class="form-group">
                        <label>&nbsp;</label><br>
                        <?php if ($showBalanza): ?>
                            <a href="<?= $allUrl ?>" class="btn bg-blue-btn menu_assign_class me-2" style="height: 38px;">Ver todos los productos</a>
                        <?php else: ?>
                            <a href="<?= $balanzaUrl ?>" class="btn bg-blue-btn menu_assign_class me-2" style="height: 38px;">Ver solo productos de Balanza</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <div class="box-wrapper">
        
        <div class="table-box">
            <div class="table-responsive">
                <table id="datatable_sv" class="table table-sm table-striped  table-horizontal-lines table-hover" style="width:100%;">
                    <thead>
                        <tr>
                            <th class="ir_w_1">ID</th>
                            <th class="ir_w_1">
                                <input type="checkbox" id="selectAll">
                            </th>
                            <th class="ir_w_6"><?php echo lang('code'); ?></th>
                            <th class="ir_w_15"><?php echo lang('name'); ?></th>
                            <th class="ir_w_10"><?php echo lang('category'); ?></th>
                            <th class="ir_w_10"><?php echo lang('sale_price'); ?></th>
                            <th class="ir_w_10">
                                Es p/ Balanza?
                                <div id="headerSwitchBalanza" style="display:none; margin-top:2px;"></div>
                            </th>
                            <th class="ir_w_10">
                                P/U
                                <div id="headerSwitchPU" style="display:none; margin-top:2px;"></div>
                            </th>
                            <th class="ir_w_10">Validez
                                <div id="headerInputValidez" style="display:none; margin-top:2px;"></div>
                            </th>
                            <th class="ir_w_10">
                                IVA
                                <div id="headerSelectIVA" style="display:none; margin-top:2px;"></div>
                            </th>
                            <th class="ir_w_1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($foodMenus && !empty($foodMenus)) {
                            $i = count($foodMenus);
                            foreach ($foodMenus as $value) {
                                ?>
                                <tr>
                                    <td class="ir_txt_center"><?php echo escape_output($value->id); ?></td>
                                    <td>
                                        <input type="checkbox" class="row-checkbox" value="<?php echo ($value->id); ?>">
                                    </td>
                                    <td><?php echo escape_output($value->code) ?></td>
                                    <td><?php echo escape_output($value->name) ?></td>
                                    <td>
                                        <!-- SELECT DE CATEGORIAS AL DARLE DOBLE CLICK -->
                                        <?php echo (($value->category_id)) ?>
                                    </td>
                                    <td>
                                        <!-- DARLE DOBLE CLICK QUE SE CONVIERTA EN UN INPUT NUMERO -->
                                        <?php echo escape_output(($value->sale_price)) ?>
                                    </td>
                                    <td><?php echo ($value->is_balanza); // solo el valor numérico ?></td>
                                    <td><?php echo ($value->balanza_tipo); // solo P o U ?></td>
                                    <td><?php echo ($value->balanza_validez); ?></td>
                                    <td><?php echo ($value->iva_tipo); // solo el valor numérico ?></td>
                                    <td></td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php $this->view('common/footer_js')?>
<!-- JavaScript para DataTables y funcionalidades -->

<script>
    var base_url = '<?= base_url(); ?>';
    document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const table = document.getElementById('datatable_sv');
    let selectedIds = [];

    // Delegación: maneja los checks individuales rápido, incluso con muchas filas
    table.addEventListener('change', function(e) {
        if (e.target.classList.contains('row-checkbox')) {
            let id = e.target.value;
            if (e.target.checked) {
                if (!selectedIds.includes(id)) selectedIds.push(id);
            } else {
                selectedIds = selectedIds.filter(x => x !== id);
            }
            syncSelectAll();
            showHideBalanzaActions();
        }
    });

    // Seleccionar todos de manera eficiente, incluso con 10000+
    selectAll.addEventListener('change', function() {
        const checkboxes = table.querySelectorAll('tbody .row-checkbox');
        checkboxes.forEach(chk => {
            chk.checked = selectAll.checked;
            let id = chk.value;
            if (selectAll.checked) {
                if (!selectedIds.includes(id)) selectedIds.push(id);
            } else {
                selectedIds = [];
            }
        });
        showHideBalanzaActions();
    });

    // Sincroniza el selectAll visualmente cuando se marcan a mano
    function syncSelectAll() {
        const checkboxes = table.querySelectorAll('tbody .row-checkbox');
        selectAll.checked = checkboxes.length > 0 &&
            Array.from(checkboxes).every(chk => chk.checked);
    }

    function addHeaderMassiveListeners() {
    const massSwitchBalanza = document.getElementById('massSwitchBalanza');
    const massSwitchPU = document.getElementById('massSwitchPU');
    const massSelectIVA = document.getElementById('massSelectIVA');
    const btnMassValidez = document.getElementById('btnMassValidez');
    // Es p/ Balanza?
    if (massSwitchBalanza) {
        massSwitchBalanza.addEventListener('change', function() {
            updateSelectedRows('is_balanza', this.checked ? '1' : '0');
        });
    }

    // P/U
    if (massSwitchPU) {
        massSwitchPU.addEventListener('change', function() {
            updateSelectedRows('balanza_tipo', this.checked ? 'P' : 'U');
        });
    }

    // Input de Validez
    if (btnMassValidez) {
        btnMassValidez.addEventListener('click', function() {
            const validez = document.getElementById('massInputValidez').value;
            if (validez === "") return alert("Ingrese un valor");
            updateSelectedRows('balanza_validez', validez);
        });
    }

    // IVA
    if (massSelectIVA) {
        massSelectIVA.addEventListener('change', function() {
            if(this.value !== "") {
                updateSelectedRows('iva_tipo', this.value);
            }
        });
    }

    function updateSelectedRows(field, value) {
    // Recoge todos los IDs seleccionados
        let ids = [];
        document.querySelectorAll('.row-checkbox:checked').forEach(chk => {
            ids.push(chk.value);
            // Actualiza el DOM visualmente (opcional)
            let row = chk.closest('tr');
            if (field === 'is_balanza') {
                let balanzaTd = row.children[6];
                let input = balanzaTd.querySelector('input[type="checkbox"]');
                if(input) input.checked = value === '1';
                let left = balanzaTd.querySelector('.switch-label.left');
                let right = balanzaTd.querySelector('.switch-label.right');
                if(input && left && right) {
                    if(input.checked) {
                        left.classList.remove('active');
                        right.classList.add('active');
                    } else {
                        left.classList.add('active');
                        right.classList.remove('active');
                    }
                }
            }
            if (field === 'balanza_tipo') {
                let puTd = row.children[7];
                let input = puTd.querySelector('input[type="checkbox"]');
                let slider = puTd.querySelector('.slider');
                if(input) input.checked = value === 'P';
                if(slider) slider.className = 'slider ' + (value === 'P' ? 'pesable' : 'unitario');
                let left = puTd.querySelector('.switch-label.left');
                let right = puTd.querySelector('.switch-label.right');
                if(input && left && right) {
                    if(input.checked) {
                        left.classList.remove('active');
                        right.classList.add('active');
                    } else {
                        left.classList.add('active');
                        right.classList.remove('active');
                    }
                }
            }
            if (field === 'iva_tipo') {
                let ivaTd = row.children[9];
                let select = ivaTd.querySelector('select');
                if(select) select.value = value;
            }
            if (field === 'balanza_validez') {
                let validezTd = row.children[8];
                validezTd.textContent = value;
            }

        });

        // Solo una petición para todos los IDs
        if (ids.length === 0) return;
        fetch(base_url+'FoodMenu/batch_update_field_ajax', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ids, field, value})
        })
        .then(res => res.json())
        .then(resp => {
            // Si quieres feedback visual o mensaje de éxito puedes ponerlo aquí
            // Por ejemplo: alert('¡Registros actualizados!');
        });
    }

}

// Llama esto después de renderHeaderMassiveControls:
function showHideBalanzaActions() {
    renderHeaderMassiveControls(selectedIds.length);
    setTimeout(addHeaderMassiveListeners, 10); // asegura que los elementos existen
}

    
    // --------- ACCIONES MASIVAS con checkboxes y botones batch
    document.getElementById('activarBalanzaBtn').onclick = function(){
        if (!window.confirm('¿Estás seguro de activar balanza para los seleccionados?')) return;
        batchUpdate('is_balanza', 1);
    }
    document.getElementById('desactivarBalanzaBtn').onclick = function(){
        if (!window.confirm('¿Estás seguro de desactivar balanza para los seleccionados?')) return;
        batchUpdate('is_balanza', 0);
    }
    function batchUpdate(field, value) {
        // Selecciona los IDs marcados
        let ids = [];
        document.querySelectorAll('.row-checkbox:checked').forEach(chk=>{
            ids.push(chk.value);
        });
        if (ids.length == 0) return;
        fetch(base_url+'FoodMenu/batch_update_field_ajax', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ids, field, value})
        }).then(r=>r.json()).then(resp=>{
            window.location.reload();
        });
    }

});
function renderHeaderMassiveControls(selectedCount) {
    // Es p/ Balanza
    const hBalanza = document.getElementById('headerSwitchBalanza');
    const hPU = document.getElementById('headerSwitchPU');
    const hIVA = document.getElementById('headerSelectIVA');
    const hValidez = document.getElementById('headerInputValidez');

    // Mostrar solo si hay seleccionados
    if (selectedCount > 0) {
        // Switch "Es p/ Balanza?"
        hBalanza.innerHTML = `
        <span class="switch-label left" >No</span>
        <span class="switch-label right" >Sí</span>
        <label class="switch">
            <input type="checkbox" id="massSwitchBalanza">
            <span class="slider"></span>
        </label>
        `;
        hBalanza.style.display = '';

        // Switch P/U
        hPU.innerHTML = `
        <span class="switch-label left">Unitario</span>
        <span class="switch-label right">Pesable</span>
        <label class="switch">
            <input type="checkbox" id="massSwitchPU">
            <span class="slider"></span>
        </label>
        `;
        hPU.style.display = '';

        // Select IVA
        hIVA.innerHTML = `
        <select id="massSelectIVA" class="form-control">
            <option value="">Cambiar IVA</option>
            <option value="10">IVA 10</option>
            <option value="5">IVA 5</option>
            <option value="0">IVA Exonerado</option>
        </select>
        `;
        hIVA.style.display = '';
        hValidez.innerHTML = `
        <input type="number" id="massInputValidez" class="form-control" style="width:85px;display:inline-block;" placeholder="Validez">
        <button id="btnMassValidez" class="btn btn-success btn-sm" style="margin-left:4px;">Guardar</button>
        `;
        hValidez.style.display = '';
    } else {
        hBalanza.style.display = 'none';
        hPU.style.display = 'none';
        hIVA.style.display = 'none';
        hBalanza.innerHTML = '';
        hPU.innerHTML = '';
        hIVA.innerHTML = '';
        hValidez.style.display = 'none';
        hValidez.innerHTML = '';
    }
}
</script>

<script>
    // Usamos vanilla JS y fetch, no jQuery extra
document.addEventListener('DOMContentLoaded', function() {
    // -- CATEGORÍAS (columna)
    let categoriesCache = null;
    async function fetchCategories() {
        if (categoriesCache) return categoriesCache;
        let res = await fetch(base_url+'FoodMenu/get_categories_ajax');
        categoriesCache = await res.json();
        return categoriesCache;
    }

    document.querySelectorAll('#datatable_sv tbody tr').forEach(function(row) {
        // --------- CATEGORY: Doble click y select dinámico
        let catTd = row.children[4];
        let currentCatId = catTd.textContent.trim();
        let currentCatName = catTd.textContent.trim();
        catTd.innerHTML = `<span class="category-view">${currentCatName}</span>`;
        catTd.addEventListener('dblclick', async function() {
            if (catTd.querySelector('select')) return;
            let categories = await fetchCategories();
            const currentId = row.children[0].textContent.trim();
            let select = document.createElement('select');
            select.style.width = '100%';
            categories.forEach(cat => {
                let opt = document.createElement('option');
                opt.value = cat.id;
                opt.textContent = cat.name;
                if (cat.id == currentCatId) opt.selected = true;
                select.appendChild(opt);
            });
            catTd.innerHTML = '';
            catTd.appendChild(select);
            select.focus();
            select.addEventListener('change', function() {
                // Llama AJAX para guardar en BD
                fetch(base_url+'FoodMenu/update_field_ajax', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id: currentId, field: 'category_id', value: select.value})
                }).then(r=>r.json()).then(resp=>{
                    catTd.innerHTML = `<span class="category-view">${select.options[select.selectedIndex].text}</span>`;
                });
            });
            select.addEventListener('blur', function(){
                catTd.innerHTML = `<span class="category-view">${select.options[select.selectedIndex].text}</span>`;
            });
        });

        // --------- CODE: Doble click y input inline
        let codeTd = row.children[2];
        let codeVal = codeTd.textContent.trim();
        codeTd.addEventListener('dblclick', function() {
            if (codeTd.querySelector('input')) return;
            let input = document.createElement('input');
            input.type = 'text';
            input.value = codeVal;
            input.style.width = '90px';
            let btn = document.createElement('button');
            btn.textContent = 'Guardar';
            btn.className = 'btn btn-success btn-sm';
            codeTd.innerHTML = '';
            codeTd.appendChild(input);
            codeTd.appendChild(btn);
            input.focus();
            btn.addEventListener('click', function(){
                let currentId = row.children[0].textContent.trim();
                fetch(base_url+'FoodMenu/update_field_ajax', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id: currentId, field: 'code', value: input.value})
                }).then(r=>r.json()).then(resp=>{
                    codeTd.textContent = input.value;
                    codeVal = input.value;
                });
            });
            input.addEventListener('blur', function(){
                setTimeout(()=>{codeTd.textContent = codeVal;}, 200);
            });
        });

        // --------- NAME: Doble click y input inline
        let nameTd = row.children[3];
        let nameVal = nameTd.textContent.trim();
        nameTd.addEventListener('dblclick', function() {
            if (nameTd.querySelector('input')) return;
            let input = document.createElement('input');
            input.type = 'text';
            input.value = nameVal;
            input.style.width = '160px';
            let btn = document.createElement('button');
            btn.textContent = 'Guardar';
            btn.className = 'btn btn-success btn-sm';
            nameTd.innerHTML = '';
            nameTd.appendChild(input);
            nameTd.appendChild(btn);
            input.focus();
            btn.addEventListener('click', function(){
                let currentId = row.children[0].textContent.trim();
                fetch(base_url+'FoodMenu/update_field_ajax', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id: currentId, field: 'name', value: input.value})
                }).then(r=>r.json()).then(resp=>{
                    nameTd.textContent = input.value;
                    nameVal = input.value;
                });
            });
            input.addEventListener('blur', function(){
                setTimeout(()=>{nameTd.textContent = nameVal;}, 200);
            });
        });


        // --------- SALE PRICE: Doble click y input inline
        let priceTd = row.children[5];
        let priceVal = priceTd.textContent.trim();
        priceTd.addEventListener('dblclick', function() {
            if (priceTd.querySelector('input')) return;
            let input = document.createElement('input');
            input.type = 'number';
            input.value = priceVal;
            input.style.width = '70px';
            let btn = document.createElement('button');
            btn.textContent = 'Guardar';
            btn.className = 'btn btn-success btn-sm';
            priceTd.innerHTML = '';
            priceTd.appendChild(input);
            priceTd.appendChild(btn);
            input.focus();
            btn.addEventListener('click', function(){
                let currentId = row.children[0].textContent.trim();
                fetch(base_url+'FoodMenu/update_field_ajax', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id: currentId, field: 'sale_price', value: input.value})
                }).then(r=>r.json()).then(resp=>{
                    priceTd.textContent = input.value;
                    priceVal = input.value;
                });
            });
            input.addEventListener('blur', function(){
                setTimeout(()=>{priceTd.textContent = priceVal;}, 200);
            });
        });

        // --------- ES BALANZA: Switch
        let balanzaTd = row.children[6];
        let isBalanza = balanzaTd.textContent.includes('1');
        // ES BALANZA
        balanzaTd.innerHTML = `
        <span class="switch-label left" >No</span>
        <span class="switch-label right" >Sí</span>
        <label class="switch">
            <input type="checkbox" ${isBalanza ? 'checked' : ''}>
            <span class="slider"></span>
        </label>
        `;
        let balanzaInput = balanzaTd.querySelector('input[type="checkbox"]');
        let leftLabel = balanzaTd.querySelector('.switch-label.left');
        let rightLabel = balanzaTd.querySelector('.switch-label.right');

        // Activa visualmente el label correcto
        function activateSwitchLabel(input, leftLabel, rightLabel) {
            if (input.checked) {
                leftLabel.classList.remove('active');
                rightLabel.classList.add('active');
            } else {
                leftLabel.classList.add('active');
                rightLabel.classList.remove('active');
            }
        }
        activateSwitchLabel(balanzaInput, leftLabel, rightLabel);

        balanzaInput.addEventListener('change', function() {
            activateSwitchLabel(balanzaInput, leftLabel, rightLabel);
            let currentId = row.children[0].textContent.trim();
            fetch(base_url+'FoodMenu/update_field_ajax', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id: currentId, field: 'is_balanza', value: this.checked ? 1 : 0})
            });
        });

        // --------- P/U: Switch Pesable (azul) / Unitario (amarillo)
        let puTd = row.children[7];
        let isPesable = puTd.textContent.includes('P');

        // P/U Pesable Unitario
        puTd.innerHTML = `
        <span class="switch-label left">Unitario</span>
        <span class="switch-label right">Pesable</span>
        <label class="switch">
            <input type="checkbox" ${isPesable ? 'checked' : ''}>
            <span class="slider ${isPesable ? 'pesable' : 'unitario'}"></span>
        </label>
        `;
        let puInput = puTd.querySelector('input[type="checkbox"]');
        let puSlider = puTd.querySelector('.slider');
        let puLeftLabel = puTd.querySelector('.switch-label.left');
        let puRightLabel = puTd.querySelector('.switch-label.right');

        activateSwitchLabel(puInput, puLeftLabel, puRightLabel);
        puInput.addEventListener('change', function() {
            activateSwitchLabel(puInput, puLeftLabel, puRightLabel);
            puSlider.className = 'slider ' + (this.checked ? 'pesable' : 'unitario');
            let val = this.checked ? 'P' : 'U';
            puSlider.className = 'slider ' + (val === 'P' ? 'pesable' : 'unitario');
            let currentId = row.children[0].textContent.trim();
            fetch(base_url+'FoodMenu/update_field_ajax', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id: currentId, field: 'balanza_tipo', value: val})
            });
        });

        // --------- VALIDEZ: Input numérico inline
        let validezTd = row.children[8];
        let validezVal = validezTd.textContent.trim();
        validezTd.addEventListener('dblclick', function() {
            if (validezTd.querySelector('input')) return;
            let input = document.createElement('input');
            input.type = 'number';
            input.value = validezVal;
            input.style.width = '70px';
            let btn = document.createElement('button');
            btn.textContent = 'Guardar';
            btn.className = 'btn btn-success btn-sm';
            validezTd.innerHTML = '';
            validezTd.appendChild(input);
            validezTd.appendChild(btn);
            input.focus();
            btn.addEventListener('click', function(){
                let currentId = row.children[0].textContent.trim();
                fetch(base_url+'FoodMenu/update_field_ajax', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id: currentId, field: 'balanza_validez', value: input.value})
                }).then(r=>r.json()).then(resp=>{
                    validezTd.textContent = input.value;
                    validezVal = input.value;
                });
            });
            input.addEventListener('blur', function(){
                setTimeout(()=>{validezTd.textContent = validezVal;}, 200);
            });
        });

        // --------- IVA: Select inline, update on change
        let ivaTd = row.children[9];
        let ivaVal = ivaTd.textContent.trim();
        const ivaOptions = [
          {value: '10', text: 'IVA 10'},
          {value: '5', text: 'IVA 5'},
          {value: '0', text: 'IVA Exonerado'}
        ];
        let select = document.createElement('select');
        select.className = 'form-control'; // <-- agrega esto
        ivaOptions.forEach(opt=>{
            let option = document.createElement('option');
            option.value = opt.value;
            option.text = opt.text;
            if (ivaVal === opt.value) option.selected = true; // <-- cambio aquí
            select.appendChild(option);
        });
        ivaTd.innerHTML = '';
        ivaTd.appendChild(select);
        select.addEventListener('change', function(){
            let currentId = row.children[0].textContent.trim();
            fetch(base_url+'FoodMenu/update_field_ajax', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id: currentId, field: 'iva_tipo', value: select.value})
            });
        });
    });

});
</script>