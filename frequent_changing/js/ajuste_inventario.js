$(function() {
    let ajuste_id = $("#ajuste_id").val();

    // Buscar producto por código
    $("#codigo_busqueda").on('keypress', function(e) {
        if (e.which == 13) {
            e.preventDefault();
            let codigo = $(this).val();
            $.post(base_url + "Inventory_adjustment/ajaxBuscarIngredientePorCodigo", {codigo: codigo}, function(res) {
                if (res.success) {
                    $("#producto_nombre").val(res.name);
                    $("#ingrediente_id").val(res.id);
                    $("#qty_old").val(res.stock);
                    $("#costo").val(res.costo);
                    $("#qty_new").focus();
                } else {
                    alert("Producto no encontrado");
                }
            }, 'json');
        }
    });

    // Calcular diferencia y costo dif automáticamente
    $("#qty_new").on('input', function() {
        let qty_old = parseFloat($("#qty_old").val()) || 0;
        let qty_new = parseFloat($(this).val()) || 0;
        let costo = parseFloat($("#costo").val()) || 0;
        let qty_dif = qty_new - qty_old;
        let costo_dif = qty_dif * costo;
        $("#qty_dif").val(qty_dif);
        $("#costo_dif").val(costo_dif);
    });

    // Agregar ajuste
    $("#btn_agregar_ajuste").on('click', function() {
        let ingrediente_id = $("#ingrediente_id").val();
        let codigo = $("#codigo_busqueda").val();
        let producto_nombre = $("#producto_nombre").val();
        let qty_old = parseFloat($("#qty_old").val());
        let qty_new = parseFloat($("#qty_new").val());
        let qty_dif = qty_new - qty_old;
        let costo = parseFloat($("#costo").val());
        let costo_dif = qty_dif * costo;
        let reference_no = $("input[name='reference_no']").val();
        let date = $("input[name='date']").val();
        let note = $("input[name='note']").val();
        if (!ingrediente_id || isNaN(qty_new)) {
            alert("Faltan datos");
            return;
        }
        $.post(base_url + "Inventory_adjustment/ajaxGuardarAjusteDinamico", {
            ajuste_id: ajuste_id,
            ingredient_id: ingrediente_id,
            codigo: codigo,
            qty_old: qty_old,
            qty_new: qty_new,
            qty_dif: qty_dif,
            consumo: Math.abs(qty_dif),
            consumo_status: (qty_dif >= 0 ? 'Plus' : 'Minus'),
            costo: costo,
            costo_dif: costo_dif,
            reference_no: reference_no,
            date: date,
            note: note
        }, function(response) {
            if (response.success) {
                ajuste_id = response.ajuste_id;
                $("#ajuste_id").val(ajuste_id);
                refrescarTablaAjustes();
                limpiarCampos();
            }
        }, 'json');
        $('#codigo_busqueda').focus();
    });

    // Botón buscar por código (funciona igual que enter en input)
    $("#btn_buscar_codigo").on('click', function() {
        $("#codigo_busqueda").trigger($.Event('keypress', {which: 13}));
    });

    // Formato y totales en la tabla
    function refrescarTablaAjustes() {
        $.post(base_url + "Inventory_adjustment/ajaxListarAjusteDetalles", {
            ajuste_id: ajuste_id
        }, function(res) {
            let tbody = $("#tabla_ajustes tbody");
            let totalDif = 0, totalCostoDif = 0;
            tbody.empty();
            if (res.success && res.items.length > 0) {
                res.items.forEach(function(item) {
                    // Formato para diferencia
                    let dif = parseFloat(item.qty_dif) || 0;
                    let costoDif = parseFloat(item.costo_dif) || 0;
                    totalDif += dif;
                    totalCostoDif += costoDif;

                    let difClass = dif > 0 ? 'text-success fw-bold' : (dif < 0 ? 'text-danger fw-bold' : '');
                    let difSign = dif > 0 ? '+' : '';
                    let costoClass = costoDif > 0 ? 'text-success fw-bold' : (costoDif < 0 ? 'text-danger fw-bold' : '');
                    let costoSign = costoDif > 0 ? '+' : '';

                    tbody.append(
                        `<tr>
                            <td>${item.datetime || ''}</td>
                            <td>${item.codigo || ''}</td>
                            <td>${item.name || ''}</td>
                            <td>${item.qty_old || ''}</td>
                            <td>${item.qty_new || ''}</td>
                            <td class="${difClass}">${difSign}${dif}</td>
                            <td>${formatNumberToCurrency(item.costo) || ''}</td>
                            <td class="${costoClass}">${costoSign}${formatNumberToCurrency(costoDif)}</td>
                            <td>${item.user_txt || ''}</td>
                            <td>
                            <button class="btn btn-danger btn-sm btn-borrar-ajuste" data-id="${item.id}" title="Borrar">
                                <i class="fa fa-trash"></i>
                            </button>
                            </td>
                        </tr>`
                    );
                });
            }
            // Footer con totales
            let totalDifClass = totalDif > 0 ? 'text-success fw-bold' : (totalDif < 0 ? 'text-danger fw-bold' : '');
            let totalDifSign = totalDif > 0 ? '+' : '';
            let totalCostoClass = totalCostoDif > 0 ? 'text-success fw-bold' : (totalCostoDif < 0 ? 'text-danger fw-bold' : '');
            let totalCostoSign = totalCostoDif > 0 ? '+' : '';
            $("#total_diferencia").attr('class', totalDifClass).html(formatNumberToCurrency(totalDifSign + totalDif));
            $("#total_costo_dif").attr('class', totalCostoClass).html(formatNumberToCurrency(totalCostoSign + totalCostoDif));
        }, 'json');
    }
    
    // Borrar ajuste detalle
    $("#tabla_ajustes").on('click', '.btn-borrar-ajuste', function() {
        if (!confirm("¿Estás seguro de borrar este registro?")) return;
        let id = $(this).data('id');
        $.post(base_url + "Inventory_adjustment/ajaxBorrarAjusteDetalle", {detalle_id: id}, function(res) {
            if (res.success) {
                refrescarTablaAjustes();
            } else {
                alert("No se pudo borrar");
            }
        }, 'json');
    });

    function limpiarCampos() {
        $("#codigo_busqueda").val('');
        $("#producto_nombre").val('');
        $("#ingrediente_id").val('');
        $("#qty_old").val('');
        $("#qty_new").val('');
        $("#costo").val('');
        $("#qty_dif").val('');
        $("#costo_dif").val('');
    }
    // Asumiendo que #qty_new es el input de cantidad nueva
    $("#qty_new").on('keypress', function(e) {
        if (e.which == 13) { // Tecla Enter
            e.preventDefault(); // Previene el submit del formulario o recarga
            $("#btn_agregar_ajuste").trigger('click'); // Simula click del botón Agregar
        }
    });

    if (ajuste_id) refrescarTablaAjustes();


    let seleccion_sugerencia = -1;
    let sugerencias = [];

    // AUTOCOMPLETADO POR NOMBRE
    $("#codigo_busqueda").on('input', function(e) {
        let valor = $(this).val().trim();
        if (valor.length < 2) {
            $("#sugerencias").hide();
            return;
        }
        $.post(base_url + "Inventory_adjustment/ajaxBuscarIngredientesPorNombre", { term: valor }, function(res) {
            if (res.success && res.items.length > 0) {
                sugerencias = res.items;
                let html = '';
                res.items.forEach((item, i) => {
                    html += `<div class="sugerencia-item" data-index="${i}" data-id="${item.id}" style="padding:6px;cursor:pointer;">
                        <b>${item.code}</b> - ${item.name} <small>(${item.unit_name})</small>
                    </div>`;
                });
                $("#sugerencias").html(html).show();
                seleccion_sugerencia = -1;
            } else {
                $("#sugerencias").hide();
            }
        }, 'json');
    });

    // NAVEGACIÓN CON FLECHAS Y ENTER
    $("#codigo_busqueda").on('keydown', function(e) {
        let items = $("#sugerencias .sugerencia-item");
        if ($("#sugerencias").is(":visible")) {
            if (e.key === "ArrowDown") {
                e.preventDefault();
                if (seleccion_sugerencia < items.length - 1) {
                    seleccion_sugerencia++;
                }
                items.removeClass("bg-primary text-white");
                $(items[seleccion_sugerencia]).addClass("bg-primary text-white");
            } else if (e.key === "ArrowUp") {
                e.preventDefault();
                if (seleccion_sugerencia > 0) {
                    seleccion_sugerencia--;
                }
                items.removeClass("bg-primary text-white");
                $(items[seleccion_sugerencia]).addClass("bg-primary text-white");
            } else if (e.key === "Enter") {
                if (seleccion_sugerencia >= 0 && items.length > 0) {
                    e.preventDefault();
                    $(items[seleccion_sugerencia]).trigger('mousedown');
                } else {
                    // Si no hay sugerencia seleccionada, busca por código (comportamiento actual)
                    // Tu código original:
                    let codigo = $(this).val();
                    $.post(base_url + "Inventory_adjustment/ajaxBuscarIngredientePorCodigo", {codigo: codigo}, function(res) {
                        if (res.success) {
                            $("#producto_nombre").val(res.name);
                            $("#ingrediente_id").val(res.id);
                            $("#qty_old").val(res.stock);
                            $("#costo").val(res.costo);
                            $("#qty_new").focus();
                        } else {
                            alert("Producto no encontrado");
                        }
                    }, 'json');
                }
            } else if (e.key === "Escape") {
                $("#sugerencias").hide();
            }
        }
    });

    // SELECCIÓN CON CLICK
    $("#sugerencias").on('mousedown', '.sugerencia-item', function(e) {
        let idx = $(this).data('index');
        let item = sugerencias[idx];
        // Aquí puedes cargar los datos como si hubieras buscado por código
        $.post(base_url + "Inventory_adjustment/ajaxBuscarIngredientePorCodigo", {codigo: item.code}, function(res) {
            if (res.success) {
                $("#codigo_busqueda").val(res.code); // Poner el código en el input
                $("#producto_nombre").val(res.name);
                $("#ingrediente_id").val(res.id);
                $("#qty_old").val(res.stock);
                $("#costo").val(res.costo);
                $("#qty_new").focus();
            } else {
                alert("Producto no encontrado");
            }
        }, 'json');
        $("#sugerencias").hide();
    });

    // OCULTAR SUGERENCIAS AL PERDER FOCO
    $(document).on('click', function(e) {
        if (!$(e.target).closest("#codigo_busqueda, #sugerencias").length) {
            $("#sugerencias").hide();
        }
    });


});