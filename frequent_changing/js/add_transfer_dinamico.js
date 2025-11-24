$(function() {
    let transfer_id = $("#transfer_id_dinamico").val() || "";
    let base_url = $("#base_url").val() || window.base_url;

    // --- Autocompletar por código/nombre (igual que antes) ---
    let sugerencias = [];
    let seleccion_sugerencia = -1;
    let is_menu_selected = false;

    $("#codigo_busqueda").on('input', function(e) {
        let valor = $(this).val().trim();
        if (valor.length < 2) {
            $("#sugerencias").hide();
            return;
        }
        $.post(base_url + "Transfer/ajaxBuscarIngredientesPorNombre", { term: valor }, function(res) {
            if (res.success && res.items.length > 0) {
                sugerencias = res.items;
                let html = '';
                res.items.forEach((item, i) => {
                    let tipo = item.is_menu ? ' (Menú)' : '';
                    html += `<div class="sugerencia-item" data-index="${i}" data-id="${item.id}" data-is-menu="${item.is_menu}" style="padding:6px;cursor:pointer;">
                        <b>${item.code}</b> - ${item.name}${tipo} <small>(${item.unit_name})</small>
                    </div>`;
                });
                $("#sugerencias").html(html).show();
                seleccion_sugerencia = -1;
            } else {
                $("#sugerencias").hide();
            }
        }, 'json');
    });

    // Funciones para código variable de balanza
    function esCodigoVariable(codigo) {
        let limpio = codigo.replace(/\D/g, '');
        return limpio.length === 13 && limpio.startsWith("2");
    }
    function decodificarCodigoVariable(codigo) {
        let limpio = codigo.replace(/\D/g, '');
        let plu = limpio.substr(1, 6); // Quita ceros a la izquierda .replace(/^0+/, '')
        let valor = limpio.substr(7, 5);
        let cantidad;
        if (valor === "00001") {
            cantidad = 1;
        } else {
            cantidad = parseInt(valor, 10) / 1000;
        }
        return {
            plu: plu,
            cantidad: cantidad
        };
    }

    // --- Manejo de sugerencias y códigos variables en keydown ---
    $("#codigo_busqueda").on('keydown', function(e) {
        let valor = $(this).val().trim();
        let items = $("#sugerencias .sugerencia-item");
        let sugerenciasVisibles = $("#sugerencias").is(":visible") && items.length > 0;

        if (sugerenciasVisibles) {
            if (e.key === "ArrowDown") {
                e.preventDefault();
                if (seleccion_sugerencia < items.length - 1) {
                    seleccion_sugerencia++;
                }
                items.removeClass("bg-primary text-white");
                $(items[seleccion_sugerencia]).addClass("bg-primary text-white");
                return;
            } else if (e.key === "ArrowUp") {
                e.preventDefault();
                if (seleccion_sugerencia > 0) {
                    seleccion_sugerencia--;
                }
                items.removeClass("bg-primary text-white");
                $(items[seleccion_sugerencia]).addClass("bg-primary text-white");
                return;
            } else if (e.key === "Enter") {
                if (seleccion_sugerencia >= 0 && items.length > 0) {
                    e.preventDefault();
                    $(items[seleccion_sugerencia]).trigger('mousedown');
                    return;
                } else {
                    // Si no hay selección pero sí sugerencias, buscar por código normal
                    buscarPorCodigo();
                    return;
                }
            } else if (e.key === "Escape") {
                $("#sugerencias").hide();
                return;
            }
            // Si hay sugerencias, no procesamos nada más
            return;
        }

        // Si NO hay sugerencias, procesar código variable o normal
        if (e.key === "Enter" && valor.length > 0) {
            if (esCodigoVariable(valor)) {
                e.preventDefault();
                let datos = decodificarCodigoVariable(valor);
                $.post(base_url + "Transfer/ajaxBuscarIngredientePorCodigo", { codigo: datos.plu }, function(res) {
                    if (res.success) {
                        $("#producto_nombre").val(res.name);
                        $("#ingrediente_id").val(res.id);
                        $("#qty_stock").val(res.stock);
                        $("#qty_transfer").val(datos.cantidad);
                        setTimeout(function() {
                            $("#btn_agregar_detalle").click();
                            $("#codigo_busqueda").val('').focus();
                        }, 100);
                    } else {
                        alert("Producto de balanza no encontrado (PLU: " + datos.plu + ")");
                    }
                }, 'json');
                return;
            }
            // Si no es código variable, buscar por código normal
            buscarPorCodigo();
            return;
        }
    });

    $("#sugerencias").on('mousedown', '.sugerencia-item', function(e) {
        let idx = $(this).data('index');
        let item = sugerencias[idx];
        cargarProductoDesdeSugerencia(item);
        $("#sugerencias").hide();
    });

    function cargarProductoDesdeSugerencia(item) {
        $("#producto_nombre").val(item.name);
        $("#ingrediente_id").val(item.id);
        $("#qty_stock").val(item.is_menu ? 'Menú' : 'Cargando...');
        $("#qty_transfer").focus();
        is_menu_selected = item.is_menu;
        if (!item.is_menu) {
            // Si no es menú, cargar stock
            $.post(base_url + "Transfer/ajaxBuscarIngredientePorCodigo", { codigo: item.code }, function(res) {
                if (res.success) {
                    $("#qty_stock").val(res.stock);
                }
            }, 'json');
        }
    }

    $(document).on('click', function(e) {
        if (!$(e.target).closest("#codigo_busqueda, #sugerencias").length) {
            $("#sugerencias").hide();
        }
    });

    // --- Buscar por código ---
    function buscarPorCodigo() {
        let codigo = $("#codigo_busqueda").val();
        cargarProducto(codigo);
    }

    function cargarProducto(codigo) {
        $.post(base_url + "Transfer/ajaxBuscarIngredientePorCodigo", { codigo: codigo }, function(res) {
            if (res.success) {
                $("#producto_nombre").val(res.name);
                $("#ingrediente_id").val(res.id);
                $("#qty_stock").val(res.is_menu ? 'Menú' : res.stock);
                $("#qty_transfer").focus();
                is_menu_selected = res.is_menu;
            } else {
                alert("Producto no encontrado");
            }
        }, 'json');
    }

    $("#btn_buscar_codigo").on('click', buscarPorCodigo);

    function validarCampos() {
        let to_outlet_id = $("#to_outlet_id").prop("disabled") ? $("#to_outlet_id_hidden").val() : $("#to_outlet_id").val();
        let reference_no = $("#reference_no").val();
        let date = $("#date").val();
        let status = $("#status").val();
        let note_for_sender = $("#note_for_sender_dinamico").val();

        if (!to_outlet_id) {
            alert("Debes seleccionar una sucursal destino.");
            $("#to_outlet_id").focus();
            return false;
        }
        if (!reference_no) {
            alert("Referencia faltante.");
            return false;
        }
        if (!date) {
            alert("Fecha faltante.");
            return false;
        }
        return true;
    }

    // --- Agregar detalles ---
    $("#btn_agregar_detalle").on('click', function() {
        if (!validarCampos()) return;
        let ingrediente_id = $("#ingrediente_id").val();
        let cantidad = parseFloat($("#qty_transfer").val());
        if (!ingrediente_id || !cantidad || cantidad <= 0) {
            alert("Completa todos los campos.");
            return;
        }
        let data = {
            transfer_id: transfer_id,
            reference_no: $("#reference_no").val(),
            date: $("#date").val(),
            transfer_type: is_menu_selected ? 2 : 1,
            to_outlet_id: $("#to_outlet_id").val(),
            ingredient_id: ingrediente_id,
            quantity_amount: cantidad
        };
        if (window.es_emisor) {
            data.note_for_receiver = $("#note_for_receiver_dinamico").val();
        } else if (window.es_receptor) {
            data.note_for_sender = $("#note_for_sender_dinamico").val();
        }
        $.post(base_url + "Transfer/ajaxAgregarTransferDetalle", data, function(res) {
            if (res.success) {
                transfer_id = res.transfer_id;
                $("#transfer_id_dinamico").val(transfer_id);
                if (!window.location.pathname.match(/transferDinamico\/\d+$/)) {
                    window.location.href = base_url + "Transfer/transferDinamico/" + transfer_id;
                    return;
                }
                refrescarTablaTransfer();
                limpiarCampos();
            } else {
                alert("No se pudo agregar el ingrediente.");
            }
        }, 'json');
    });

    // --- Guardar cambios generales ---
    $("#btn_guardar_transfer").on('click', function() {
        if (!validarCampos()) return;
        let data = {
            transfer_id: transfer_id,
            reference_no: $("#reference_no").val(),
            date: $("#date").val(),
            to_outlet_id: $("#to_outlet_id").val(),
            status: $("#status").val()
        };
        if (window.es_emisor) {
            data.note_for_receiver = $("#note_for_receiver_dinamico").val();
        } else if (window.es_receptor) {
            data.note_for_sender = $("#note_for_sender_dinamico").val();
        }
        $.post(base_url + "Transfer/ajaxGuardarTransferInfo", data, function(res) {
            if (res.success) {
                if (res.transfer_id && res.transfer_id != transfer_id) {
                    window.location.href = base_url + "Transfer/transferDinamico/" + res.transfer_id;
                    return;
                }
                if (res.reload && res.reload == true) {
                    window.location.href = base_url + "Transfer/transferDinamico/" + res.transfer_id;
                    return;
                }
                alert("Guardado correctamente.");
            } else {
                alert(res.msg || "No se pudo guardar.");
            }
        }, 'json');
    });

    // --- Tabla y borrar detalle igual que antes ---
    function refrescarTablaTransfer() {
        if (!transfer_id) return;
        $.post(base_url + "Transfer/ajaxListarTransferDetalles", {
            transfer_id: transfer_id
        }, function(res) {
            let tbody = $("#tabla_transfer_dinamico tbody");
            tbody.empty();
            if (res.success && res.items.length > 0) {
                res.items.forEach(function(item, idx) {
                    tbody.append(`
                        <tr>
                            <td>${idx + 1}</td>
                            <td>${item.name || ''}</td>
                            <td>${item.code || ''}</td>
                            <td>${item.quantity_amount || ''}</td>
                            <td>
                                <button class="btn btn-danger btn-sm btn-borrar-detalle" data-id="${item.id}" ${detalle_editable?'':'disabled'}>
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `);
                });
            }
        }, 'json');
    }

    $("#tabla_transfer_dinamico").on('click', '.btn-borrar-detalle', function() {
        if (!confirm("¿Estás seguro de borrar este registro?")) return;
        let id = $(this).data('id');
        $.post(base_url + "Transfer/ajaxBorrarTransferDetalle", {detalle_id: id}, function(res) {
            if (res.success) refrescarTablaTransfer();
        }, 'json');
    });

    function limpiarCampos() {
        $("#codigo_busqueda").val('');
        $("#producto_nombre").val('');
        $("#ingrediente_id").val('');
        $("#qty_stock").val('');
        $("#qty_transfer").val('');
    }

    if (transfer_id) refrescarTablaTransfer();

    $("#qty_transfer").on("keydown", function(e) {
        if (e.key === "Enter") {
            e.preventDefault();
            $("#btn_agregar_detalle").click();
            setTimeout(function() {
                $("#codigo_busqueda").focus();
            }, 100);
        }
    });

    $("#codigo_busqueda").focus();
});