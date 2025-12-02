
$(function () {
    "use strict";
    let base_url = $("#base_url_customer").val();
    let registro_ocultar = $("#registro_ocultar").val();
    let registro_detallado = $("#registro_detallado").val();
    let txt_err_pos_1 = $("#txt_err_pos_1").val();
    let txt_err_pos_2 = $("#txt_err_pos_2").val();
    let txt_err_pos_3 = $("#txt_err_pos_3").val();
    let txt_err_pos_4 = $("#txt_err_pos_4").val();
    let txt_err_pos_5 = $("#txt_err_pos_5").val();
    let warning = $("#warning").val();
    let a_error = $("#a_error").val();
    let ok = $("#ok").val();
    let cancel = $("#cancel").val();

    function showLoader() {
        document.getElementById("fullScreenLoader").style.display = "flex";
        $("#fullScreenLoaderCounter").html(' Procesando Cierre de Caja.');
            
    }

    
    // Manejador del cierre de caja modificado
    $(document).on("click", "#register_close", function (e) {
        let pos_21 = Number($("#pos_21").val());
        if(pos_21){
            if (registro_ocultar != "Yes") {
                let csrf_name_ = $("#csrf_name_").val();
                let csrf_value_ = $("#csrf_value_").val();
                swal(
                    {
                        title: warning + "!",
                        text: txt_err_pos_2,
                        confirmButtonColor: "#3c8dbc",
                        confirmButtonText: ok,
                        showCancelButton: true,
                    },
                    function () {
                        showLoader();
                        // Cerrar el registro después de la impresión
                        $.ajax({
                            url: base_url + "Sale/closeRegister",
                            method: "POST",
                            dataType: "json", 
                            data: {
                                csrf_name_: csrf_value_,
                            },
                            success: function (response) {
                                let base64 = response.printerApp; 
                                // Crear un iframe temporal para la impresión
                                const iframe = document.createElement('iframe');
                                iframe.style.display = 'none';
                                iframe.src = 'print://' + base64;
                                document.body.appendChild(iframe);
                                
                                // Eliminar el iframe después de un tiempo y resolver la promesa
                                setTimeout(() => {
                                    document.body.removeChild(iframe);
                                }, 300);
                                
                                // Mostrar notificación
                                toastr['success']((register_close), '');
                                $("#close_register_button").hide();
                                
                                // Redireccionar después de un breve retraso
                                setTimeout(() => {
                                    window.location.href = base_url + "Register/openRegister";
                                }, 2000);
                            },
                            error: function () {
                                hideLoaderAll();
                                toastr['error']("Ocurrió un error al cerrar la caja.", '');
                            },
                        });
                        
                    }
                );
            } else {
                e.preventDefault();
                $("#statement_modal_registro").addClass("active");
                setTimeout(function() {
                    $("#statement_modal_registro .statement_input").first().focus().select();
                }, 300); // Un pequeño delay para asegurar que el modal está visible
            }
        } else {
            toastr['error']((menu_not_permit_access + "!"), '');
        }
    });

    // Cerrar modal
    $(document).on("click", ".close_statement_modal", function() {
        $("#statement_modal_registro").removeClass("active");
    });

    // Al ingresar valores, formatea y muestra en el span
    $(document).on("input", ".statement_input", function() {
        let value = $(this).val();
        let id = $(this).data("id");
        let op_value = Number($(`#statement_op_${id}`).text());
        let parsedValue = formatNumberToCurrency(Number(value) + op_value);
        $("#statement_input_" + id).text(parsedValue);
    });

    // Seleccionar todo el texto al enfocar el input
    $(document).on("focus", ".statement_input", function() {
        $(this).select();
    });

    // Al presionar Enter, pasa al siguiente input
    $(document).on("keydown", ".statement_input", function(e) {
        if (e.key === "Enter") {
            e.preventDefault();
            let $inputs = $(".statement_input");
            let idx = $inputs.index(this);
            if (idx < $inputs.length - 1) {
                $inputs.eq(idx + 1).focus().select();
            } else {
                $("#btn_cerrar_caja").focus();
            }
        }
    });

    // Botón cerrar caja - recolecta y envía los datos
    $(document).on("click", "#btn_cerrar_caja", function() {
        showLoader();
        let data = [];
        $(".statement_input").each(function() {
            data.push({
                payment_method_id: $(this).data("id"),
                payment_method_name: $(this).data("name"),
                amount: parseFloat($(this).val()) || 0
            });
        });
        console.log(data);

        // Aquí puedes hacer el AJAX para enviar el cierre
        $.ajax({
            url: base_url + "Sale/closeRegister",
            method: "POST",
            dataType: "json", 
            data: { 
                statement: data,
                csrf_name_: $("#csrf_name_").val(),
                csrf_value_: $("#csrf_value_").val()
            },
            success: function(response) {
                let base64 = response.printerApp; 
                // Crear un iframe temporal para la impresión
                const iframe = document.createElement('iframe');
                iframe.style.display = 'none';
                iframe.src = 'print://' + base64;
                document.body.appendChild(iframe);
                
                // Eliminar el iframe después de un tiempo y resolver la promesa
                setTimeout(() => {
                    document.body.removeChild(iframe);
                }, 300);

                // Cierra modal, muestra notificación, etc.
                // $("#statement_modal_registro").removeClass("active");
                toastr['success']("Cierre de caja realizado correctamente", '');
                setTimeout(() => {
                    window.location.href = base_url + "Register/openRegister";
                }, 2000);
            },
            error: function() {
                hideLoaderAll();
                toastr['error']("Ocurrió un error al cerrar la caja.", '');
            }
        });
    });

    function show_details_for_details_page() {
        let csrf_value_ = $("#csrf_value_").val();
        $.ajax({
            url: base_url + "Sale/registerDetailCalculationToShowAjax",
            method: "POST",
            data: {
                csrf_name_: csrf_value_,
            },
            success: function (response) {
                response = JSON.parse(response);

                $(".html_content").html(response.html_content_for_div);

                
                if ($(`#datatable`).length > 0) {
                    // No usar DataTables en esta tabla debido a su estructura mixta de <th> y <td>
                    // try {
                    //     $(`#datatable`).DataTable({...});
                    // } catch(e) {
                    //     console.error("Error al inicializar DataTable #datatable:", e);
                    // }
                }
            },
            error: function () {
                alert("error");
            },
        });
    }
    show_details_for_details_page();

    $(document).on("click", "#register_close_details", function (e) {
        let menu_not_permit_access = $("#menu_not_permit_access").val();
        let pos_21 = Number($("#pos_21").val());
        let txt_err_pos_2 = $("#txt_err_pos_2").val();
        let warning = $("#warning").val();
        let ok = $("#ok").val();

        if(pos_21){
            let csrf_name_ = $("#csrf_name_").val();
            let csrf_value_ = $("#csrf_value_").val();
            swal(
                {
                    title: warning + "!",
                    text: txt_err_pos_2,
                    confirmButtonColor: "#3c8dbc",
                    confirmButtonText: ok,
                    showCancelButton: true,
                },
                function () {
                    $.ajax({
                        url: base_url + "Sale/closeRegister",
                        method: "POST",
                        data: {
                            csrf_name_: csrf_value_,
                        },
                        success: function (response) {
                            $("#close_register_button").hide();
                            window.location.href = base_url + "Register/openRegister";
                        },
                        error: function () {
                            alert("error");
                        },
                    });
                }
            );
        }else{
           
        }
  
    });

    function checkInternetConnection(){
        let base_url_r = $("#base_url_customer").val();
        let status = false;
        $.ajax({
            url: base_url_r+"authentication/is_online",
            async: false,
            error: function(jqXHR) {
                if(jqXHR.status==0) {
                    status = false;
                }
            },
            success: function() {
                status = true;
            }
        });
        return status;
    }
    $(document).on("click", ".register_details_old", function (e) {
        let status = true;
        if(!checkInternetConnection()){
            toastr.options = {
                positionClass:'toast-bottom-right'
            };
            let register_error = $("#register_error").val();
            status = false;
            toastr['error']((register_error), '');
        }
        if(status){
            let not_closed_yet = $("#not_closed_yet").val();
            let base_url = $("#base_url_customer").val();
            let csrf_value_ = $("#csrf_value_").val();
            $.ajax({
                url: base_url + "Sale/registerDetailCalculationToShowAjax",
                method: "POST",
                data: {
                    csrf_name_: csrf_value_,
                },
                success: function (response) {
                    response = JSON.parse(response);

                    $("#register_modal").addClass("active");
                    $(".pos__modal__overlay").fadeIn(200);
                    $("#opening_register_time").html(response.opening_date_time);
                    $(".html_content").html(response.html_content_for_div);

                    if ($(`#datatable`).length > 0) {
                        // No usar DataTables en esta tabla debido a su estructura mixta de <th> y <td>
                        // try {
                        //     $(`#datatable`).DataTable({...});
                        // } catch(e) {
                        //     console.error("Error al inicializar DataTable #datatable:", e);
                        // }
                    }


                },
                error: function () {
                    alert("error");
                },
            });
        }

    });
    
    $(document).on("click", ".register_details", function (e) {
        // Mostrar modal y loader de inmediato
        $("#register_modal").addClass("active");
        $(".pos__modal__overlay").fadeIn(200);
        $(".modal_loader").show();
        $(".html_content").html(""); // Limpia el contenido hasta que llegue
    
        let status = true;
        if(!checkInternetConnection()){
            toastr.options = { positionClass:'toast-bottom-right' };
            let register_error = $("#register_error").val();
            status = false;
            toastr['error']((register_error), '');
        }
        if(status){
            let not_closed_yet = $("#not_closed_yet").val();
            let base_url = $("#base_url_customer").val();
            let csrf_value_ = $("#csrf_value_").val();
            $.ajax({
                url: base_url + "Sale/registerDetailCalculationToShowAjax",
                method: "POST",
                data: { csrf_name_: csrf_value_ },
                success: function (response) {
                    response = JSON.parse(response);
                    console.log(response.opening_balances);
                    $(".modal_loader").hide();
                    $(".html_content").html(response.html_content_for_div);
                        // Rellenar los montos de apertura en la tabla usando los payment_id
                    if (Array.isArray(response.opening_balances)) {
                        response.opening_balances.forEach(function (item) {
                            // Asegúrate que payment_id y payment_amount existen
                            if (item.payment_id && typeof item.payment_amount !== 'undefined') {
                                // Actualiza el contenido del span correspondiente
                                $(`#statement_op_${item.payment_id}`).text(item.payment_amount);
                            }
                        });
                    }
                    // DataTable para la tabla principal - NO USAR DataTables en esta tabla
                    // La tabla #datatable tiene estructura mixta con <th> y <td>, no es compatible con DataTables
                    // if ($(`#datatable`).length > 0) {
                    //     try {
                    //         $(`#datatable`).DataTable({...});
                    //     } catch(e) {
                    //         console.error("Error al inicializar DataTable #datatable:", e);
                    //     }
                    // }
    
                    // DataTable para tablas detalle - verificar que existan primero
                    if ($('.table_sale_details').length) {
                        $('.table_sale_details').DataTable({
                            'autoWidth': false,
                            'ordering': false,
                            'paging': false,
                            'bFilter': false,
                            'info': false,
                            'responsive': true,
                            'language': { 'emptyTable': 'Sin datos de ventas' }
                        });
                    }
                    if ($('.table_expense_details').length) {
                        $('.table_expense_details').DataTable({
                            'autoWidth': false,
                            'ordering': false,
                            'paging': false,
                            'bFilter': false,
                            'info': false,
                            'responsive': true,
                            'language': { 'emptyTable': 'Sin gastos registrados' }
                        });
                    }
                    if ($('.table_credit_sales').length) {
                        $('.table_credit_sales').DataTable({
                            'autoWidth': false,
                            'ordering': false,
                            'paging': false,
                            'bFilter': false,
                            'info': false,
                            'responsive': true,
                            'language': { 'emptyTable': 'Sin ventas a crédito pendientes' }
                        });
                    }
                },
                error: function () {
                    $(".modal_loader").hide();
                    $(".html_content").html('<div class="alert alert-danger">Error al cargar los datos</div>');
                },
            });
        }
    });

    $(document).on('click', '#register_expense_add', function() {
        // Cierra el modal de caja 
        $("#register_modal").removeClass("active");
        $(".pos__modal__overlay").hide();
        // Abre el modal de gasto y muestra un loader
        $("#expense_modal_registro").addClass("active");
        // $("#").show();
        $(".expense_form_content").html('<div class="modal_loader" style="text-align:center;padding:30px;"><i class="fa fa-spinner fa-spin fa-3x fa-fw"></i></div>');
    
        // Carga el formulario vía AJAX
        $.ajax({
            url: $("#base_url_customer").val() + "Sale/addExpenseAjax", // Crea este endpoint en PHP
            method: "GET",
            success: function(html) {
                $(".expense_form_content").html(html);
                // Inicializa select2 si usas
                if ($.fn.select2) $('.select2').select2();
            },
            error: function(){
                $(".expense_form_content").html('<div class="alert alert-danger">Error al cargar el formulario</div>');
            }
        });
    });
    
    // Cerrar modal de gasto
    $(document).on('click', '.close_expense_modal', function() {
        $("#expense_modal_registro").removeClass("active");
        $(".expense_form_content").html('');
    });

    $(document).on('click', '#expense_submit_btn', function() {
        let $btn = $(this);
        $btn.prop('disabled', true);
    

        // Obteniendo todos los campos
        let dateVal = $('#expense_date').val();
        let amountVal = $('#expense_amount').val();
        let categoryVal = $('#expense_category_id').val();
        let employeeVal = $('#expense_employee_id').val();
        let paymentVal = $('#expense_payment_id').val();
        let noteVal = $('#expense_note').val();

        // Validación local
        if(!dateVal || !amountVal || !categoryVal || !employeeVal || !paymentVal || !noteVal) {
            toastr['error']('Por favor completa los campos requeridos', '');
            $btn.prop('disabled', false);
            return;
        }

        let formData = {
            date: dateVal,
            amount: amountVal,
            category_id: categoryVal,
            employee_id: employeeVal,
            payment_id: paymentVal,
            note: noteVal
            // agrega CSRF si lo necesitas
        };
    
        $.ajax({
            url: $("#base_url_customer").val() + "Sale/addExpenseAjaxSubmit",
            method: "POST",
            data: formData,
            dataType: "json",
            success: function(resp) {
                if (resp.status == "ok") {
                    toastr['success'](resp.msg, '');
                    $("#expense_modal_registro").removeClass("active");
                    $(".expense_form_content").html('');
                    // Opcional: recargar la caja automáticamente
                    $(".register_details").trigger("click");
                } else {
                    toastr['error'](resp.msg, '');
                    // Puedes mostrar validaciones campo a campo aquí.
                }
            },
            error: function(){
                toastr['error']('Error en el servidor', '');
            },
            complete: function(){
                $("#expense_modal_registro").removeClass("active");
                $btn.prop('disabled', false);
            }
        });
    });

    $(document).on('click', '#inventario_print', function() {
        $("#control_inventario_modal").addClass("active");
        $(".inventario_form_content").html('<div style="text-align:center;padding:30px;"><i class="fa fa-spinner fa-spin fa-2x"></i> Cargando...</div>');
    
        $.ajax({
            url: $("#base_url_customer").val() + "Sale/getInventarioTicketAjax", // Ajusta el path según tu estructura
            method: "GET",
            dataType: "json",
            success: function(res) {
                if (!res.inventory || res.inventory.length === 0) {
                    $(".inventario_form_content").html('<div class="alert alert-warning">No hay datos de inventario.</div>');
                    return;
                }
                renderInventarioTicket(res.inventory, res.ingredient_categories, res.hora, res.outlet_name, res.username);
            },
            error: function() {
                $(".inventario_form_content").html('<div class="alert alert-danger">Error al cargar inventario.</div>');
            }
        });
    });
    
    // Cerrar modal
    $(document).on('click', '.close_inventario_modal', function() {
        $("#control_inventario_modal").removeClass("active");
        $(".inventario_form_content").html('');
    });
    // Guardamos globalmente los datos para fácil acceso desde cualquier botón
    let _inventoryData = [];
    let _ingredientCategories = [];
    let _hora = "";
    let _outletName = "";
    let _username = "";
    // Función para renderizar el ticket dentro del modal
    function renderInventarioTicket(inventoryData, ingredientCategories, hora, outletName, username) {
        _inventoryData = inventoryData;
        _ingredientCategories = ingredientCategories;
        _hora = hora;
        _outletName = outletName;
        _username = username;
        // Agrupa por categoría
        let grouped = {};
        inventoryData.forEach(function(item) {
            let cat = item.category_name || 'Sin categoría';
            if (!grouped[cat]) grouped[cat] = [];
            grouped[cat].push(item);
        });
    
        let ticketWidth = 80; // mm
        let html = `
            <div id="ticket_print_area" style="width:${ticketWidth}mm; margin:auto; font-size:8px; font-family:Arial, sans-serif;">
                <div class="center" style="text-align:center;">
                    <h3 style="margin:5px 0;font-size:14px;">Reporte de inventario</h3>
                    <h3>${outletName}</h3>
                </div>
                <h3>USUARIO: ${username}</h3>
                <h3>HORA: ${hora}</h3>
        `;
    
        for (const [category, items] of Object.entries(grouped)) {
            html += `<div style="margin-top:8px;font-weight:bold;">${category}</div>`;
            html += `<table style="width:100%;border-collapse:collapse;margin-bottom:8px;">
                <thead>
                    <tr>
                        <th style="width:20%;">Cod</th>
                        <th style="width:50%;">Prod</th>
                        <th style="width:20%;">Cant</th>
                        <th style="width:10%;">...</th>
                    </tr>
                </thead>
                <tbody>`;
            items.forEach(function(item) {
                var cantidad = calcularStock(item);
                html += `
                    <tr>
                        <td style="">${item.code}</td>
                        <td style="">${item.name}</td>
                        <td style="text-align: right;">${cantidad}</td>
                        <td class="dots">......</td>
                    </tr>
                `;
            });
            html += `</tbody></table>`;
        }
    
        // html += `<div style="text-align:center;margin-top:10px;">
        //     <button id="btn_print_ticket_inventario" class="btn btn-primary btn-sm">Imprimir Ticket</button>
        // </div>`;
        html += `</div>`;
    
        $(".inventario_form_content").html(html);
    }
    
    // Función para imprimir usando iframe invisible
    function printHtmlWithIframe(html, ticketWidth = 80) {
        let iframe = document.createElement('iframe');
        iframe.style.position = 'fixed';
        iframe.style.right = '0';
        iframe.style.bottom = '0';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = '0';
        document.body.appendChild(iframe);

        let doc = iframe.contentWindow.document;
        doc.open();
        doc.write(`
            <html>
            <head>
                <title>Reporte de Inventario</title>
                <style>
                    @media print {
                        body, html { width: ${ticketWidth}mm; }
                    }
                    body { width: ${ticketWidth}mm; font-family: Arial, sans-serif; font-size: 8px; }
                    table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
                    th, td { border-bottom: 1px dotted #ccc; padding: 3px; text-align: left; font-size: 10px; }
                    th { font-weight: bold; }
                    .dots { letter-spacing: 2px; color: #ccc; font-size: 11px; text-align: center; }
                    .category-title { margin-top: 8px; margin-bottom: 2px; font-weight: bold; }
                </style>
            </head>
            <body>
                ${html}
            </body>
            </html>
        `);
        doc.close();

        // Espera un poco para que el iframe cargue antes de imprimir
        setTimeout(function () {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
            document.body.removeChild(iframe);
        }, 250);
    }

    // Genera el HTML del ticket para una categoría específica
    function generarHtmlTicketCategoria(catId) {
        let ticketWidth = 80;
        let catObj = _ingredientCategories.find(c => c.id == catId);
        let catName = catObj ? catObj.category_name : 'Sin categoría';
        let items = _inventoryData.filter(i => i.category_name == catName);

        if (!items.length) return '<div>No hay productos en esta categoría.</div>';

        let html = `
            <div style="width:${ticketWidth}mm; margin:auto; font-size:10px; font-family:Arial, sans-serif;">
                <div class="center" style="text-align:center;">
                    <h3 style="margin:5px 0;font-size:14px;">Reporte de inventario</h3>
                    <h3>${_outletName}</h3>
                </div>
                <h3>USUARIO: ${_username}</h3>
                <h3>HORA: ${_hora}</h3>
                <div style="margin-top:8px;font-weight:bold;">${catName}</div>
                <table style="width:100%;border-collapse:collapse;margin-bottom:8px;">
                    <thead>
                        <tr>
                            <th style="width:20%;">Cod</th>
                            <th style="width:50%;">Prod</th>
                            <th style="width:20%;">Cant</th>
                            <th style="width:10%;">...</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        items.forEach(function(item) {
            var cantidad = calcularStock(item);
            html += `
                <tr>
                    <td>${item.code}</td>
                    <td>${item.name}</td>
                    <td style="text-align: right;">${cantidad}</td>
                    <td class="dots">......</td>
                </tr>
            `;
        });
        html += `</tbody></table></div>`;
        return html;
    }

    // Genera el HTML para todas las categorías (como lo hacías antes)
    function generarHtmlTicketTodos() {
        let grouped = {};
        _inventoryData.forEach(function(item) {
            let cat = item.category_name || 'Sin categoría';
            if (!grouped[cat]) grouped[cat] = [];
            grouped[cat].push(item);
        });

        let ticketWidth = 80;
        let html = `
            <div style="width:${ticketWidth}mm; margin:auto; font-size:8px; font-family:Arial, sans-serif;">
                <div class="center" style="text-align:center;">
                    <h3 style="margin:5px 0;font-size:14px;">Reporte de inventario</h3>
                    <h3>${_outletName}</h3>
                </div>
                <h3>USUARIO: ${_username}</h3>
                <h3>HORA: ${_hora}</h3>
        `;
        for (const [category, items] of Object.entries(grouped)) {
            html += `<div style="margin-top:8px;font-weight:bold;">${category}</div>`;
            html += `<table style="width:100%;border-collapse:collapse;margin-bottom:8px;">
                <thead>
                    <tr>
                        <th style="width:20%;">Cod</th>
                        <th style="width:50%;">Prod</th>
                        <th style="width:20%;">Cant</th>
                        <th style="width:10%;">...</th>
                    </tr>
                </thead>
                <tbody>`;
            items.forEach(function(item) {
                var cantidad = calcularStock(item);
                html += `
                    <tr>
                        <td>${item.code}</td>
                        <td>${item.name}</td>
                        <td style="text-align: right;">${cantidad}</td>
                        <td class="dots">......</td>
                    </tr>
                `;
            });
            html += `</tbody></table>`;
        }
        html += `</div>`;
        return html;
    }

    // Evento para imprimir todos (igual que antes, pero usando el iframe)
    $(document).on('click', '#btn_print_ticket_inventario', function() {
        let html = generarHtmlTicketTodos();
        printHtmlWithIframe(html);
    });

    // Evento para imprimir solo una categoría
    $(document).on('click', '.btn_print_cat', function() {
        let catId = $(this).data('id');
        let html = generarHtmlTicketCategoria(catId);
        printHtmlWithIframe(html);
    });

    function toNumber(valor) {
        var n = parseFloat(valor);
        return isNaN(n) ? 0 : n;
    }
    function calcularStock(item) {
        var conversion = toNumber(item.conversion_rate) ? toNumber(item.conversion_rate) : 1;
        var totalStock = (toNumber(item.total_purchase) * conversion)
            - toNumber(item.total_consumption)
            - toNumber(item.total_modifiers_consumption)
            - toNumber(item.total_waste)
            + toNumber(item.total_consumption_plus)
            - toNumber(item.total_consumption_minus)
            + (toNumber(item.total_transfer_plus) * conversion)
            - (toNumber(item.total_transfer_minus) * conversion)
            + (toNumber(item.total_transfer_plus_2) * conversion)
            - (toNumber(item.total_transfer_minus_2) * conversion)
            + (toNumber(item.total_production) * conversion);

        var total_sale_unit;
        if (!toNumber(item.conversion_rate)) {
            total_sale_unit = totalStock / 1;
        } else {
            total_sale_unit = totalStock / conversion;
        }

        var cantidad;
        if(item.ing_type == "Plain Ingredient" && item.is_direct_food != 2 && conversion != 1){
            cantidad = parseFloat(total_sale_unit) + " " + (totalStock % conversion);
        } else {
            var stock_float = parseFloat(total_sale_unit) + ((totalStock) ? (totalStock % conversion) : 0);
            cantidad = parseFloat(stock_float).toFixed(2);
        }
        return cantidad;
    }
    // // Imprimir el área del ticket (solo el contenido, no el modal completo)
    // $(document).on('click', '#btn_print_ticket_inventario', function() {
    //     let printContents = document.getElementById('ticket_print_area').innerHTML;
    //     let mywindow = window.open('', 'PRINT', 'height=600,width=400');
    //     mywindow.document.write(`
    //         <html>
    //         <head>
    //             <title>Reporte de Inventario</title>
    //             <style>
    //                 @media print {
    //                     body, html { width: 80mm; }
    //                 }
    //                 body { width: 80mm; font-family: Arial, sans-serif; font-size: 8px; }
    //                 table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
    //                 th, td { border-bottom: 1px dotted #ccc; padding: 3px; text-align: left;; font-size: 10px; }
    //                 th { font-weight: bold; }
    //                 .dots { letter-spacing: 2px; color: #ccc; font-size: 11px; text-align: center; }
    //                 .category-title { margin-top: 8px; margin-bottom: 2px; font-weight: bold; }
    //             </style>
    //         </head>
    //         <body>
    //             ${printContents}
    //         </body>
    //         </html>
    //     `);
    //     mywindow.document.close();
    //     setTimeout(function(){
    //         mywindow.focus();
    //         mywindow.print();
    //         // mywindow.close(); // Descomenta si quieres cerrar automáticamente
    //     }, 200);
    // });

    $(document).on("click", ".reservation_list", function (e) {
        let title = $(this).attr('data-title');
        $(".title_custom").html(title);
        $("#register_close").hide();

        let status = true;
        if(!checkInternetConnection()){
            toastr.options = {
                positionClass:'toast-bottom-right'
            };
            let reservation_list_error = $("#reservation_list_error").val();
            status = false;
            toastr['error']((reservation_list_error), '');
        }

        if(status){
            let not_closed_yet = $("#not_closed_yet").val();
            let base_url = $("#base_url_customer").val();
            let csrf_value_ = $("#csrf_value_").val();
            $.ajax({
                url: base_url + "authentication/getReservations",
                method: "POST",
                data: {
                    csrf_name_: csrf_value_,
                },
                success: function (response) {
                    response = JSON.parse(response);

                    $("#reservation_modal").addClass("active");
                    $(".pos__modal__overlay").fadeIn(200);
                    $(".html_content").html(response.html_content_for_div);

                    $(`#datatable1`).DataTable({
                        'autoWidth'   : false,
                        'ordering'    : false,
                        'paging'    : false,
                        'bFilter'    : false,
                        dom: 'Blfrtip',
                        buttons: [
                            {
                                extend: "print",
                                text: '<i class="fa fa-print"></i> Print',
                                titleAttr: "print",
                            },
                            {
                                extend: "excelHtml5",
                                text: '<i class="fa fa-file-excel-o"></i> Excel',
                                titleAttr: "Excel",
                            },
                            {
                                extend: "csvHtml5",
                                text: '<i class="fa fa-file-text-o"></i> CSV',
                                titleAttr: "CSV",
                            },
                            {
                                extend: "pdfHtml5",
                                text: '<i class="fa fa-file-pdf-o"></i> PDF',
                                titleAttr: "PDF",
                            },
                        ]
                    });


                },
                error: function () {
                    alert("error");
                },
            });
        }

    });
    $(document).on("change", ".change_status_reservation", function (e) {
        let status = $(this).val();
        let id = $(this).find(':selected').attr('data-id');
        let base_url = $("#base_url_customer").val();
        $.ajax({
            url: base_url + "authentication/changeReservation",
            method: "POST",
            dataType:'json',
            data: {
                id:id,status:status,
            },
            success: function (response) {
                toastr.options = {
                    positionClass:'toast-bottom-right'
                };
                toastr['success']((response.msg), '');
            },
            error: function () {
                alert("error");
            },
        });
    });
    $(document).on("click", ".remove_reservation_row", function (e) {
        let id = $(this).attr('data-id');
        let base_url = $("#base_url_customer").val();
        let warning = $("#warning").val();
        let a_error = $("#a_error").val();
        let ok = $("#ok").val();
        let cancel = $("#cancel").val();
        let are_you_sure = $("#are_you_sure").val();
        let this_action = $(this);
        swal(
            {
                title: warning + "!",
                text: are_you_sure,
                confirmButtonColor: "#3c8dbc",
                confirmButtonText: ok,
                showCancelButton: true,
            },
            function () {
                this_action.parent().parent().remove();
                $.ajax({
                    url: base_url + "authentication/removeReservation",
                    method: "POST",
                    dataType:'json',
                    data: {
                        id:id,
                    },
                    success: function (response) {
                        toastr.options = {
                            positionClass:'toast-bottom-right'
                        };
                        toastr['success']((response.msg), '');
                    },
                    error: function () {
                        alert("error");
                    },
                });
            }
        );
    });

    let print_type = $(".print_type_bill").val(); 

    
    // Abrir modal, limpiar y cargar pagos recientes
    $(document).on('click', '#remaining_due', function() {
        let customerId = $("#walk_in_customer").val();
        let customerName = $("#walk_in_customer option:selected").text();

        if (!customerId) {
            toastr['error']('Por favor, selecciona un cliente primero.', '');
            return;
        }

        $("#pay_due_modal_customer_id").val(customerId);
        $("#pay_due_modal_customer_name").text(customerName);

        // Limpiar formulario
        $('#pay_due_modal_amount').val('');
        $('#pay_due_modal_note').val('');
        $('#pay_due_modal_payment_id').val('').trigger('change');

        $("#pay_due_modal_registro").addClass("active");
        
        // Cargar las ventas pendientes para distribución
        loadPendingSalesForPayment(customerId);
        
        // Cargar la lista de pagos recientes
        loadRecentDuePayments();
    });
    
    // Cerrar modal de gasto
    $(document).on('click', '.close_pay_due_modal', function() {
        $("#pay_due_modal_registro").removeClass("active");
    });

    // --- LÓGICA DE PAGOS Y IMPRESIÓN ---

    /**
     * Carga las ventas pendientes del cliente en la tabla del modal
     */
    function loadPendingSalesForPayment(customerId) {
        let tbody = $('#modal_pending_sales_body');
        tbody.html('<tr><td colspan="5" class="text-center">Cargando...</td></tr>');
        
        let base_url = $("#base_url_customer").val();
        
        $.ajax({
            url: base_url + "Sale/getPendingSalesAjax",
            method: "GET",
            data: { customer_id: customerId },
            dataType: "json",
            success: function(response) {
                tbody.empty();
                
                if (response.status === 'success' && response.data && response.data.length > 0) {
                    response.data.forEach(function(sale) {
                        let isNegative = sale.is_negative_payment || false;
                        let saleId = isNegative ? 'NEG-' + sale.id : sale.id;
                        let saleNo = sale.sale_no;
                        let totalDue = parseFloat(sale.total_payable || sale.due_amount || 0);
                        let remainingDue = parseFloat(sale.remaining_due || 0);
                        
                        let row = `
                            <tr data-sale-id="${saleId}" style="padding: 0;">
                                <td style="padding: 4px 6px; border: 1px solid #ddd; font-size: 12px;">${saleNo}</td>
                                <td style="padding: 4px 6px; border: 1px solid #ddd; font-size: 12px;">${sale.sale_date}</td>
                                <td style="padding: 4px 6px; border: 1px solid #ddd; font-size: 12px; text-align: right;">${formatNumberToCurrency(totalDue)}</td>
                                <td style="padding: 4px 6px; border: 1px solid #ddd; font-size: 12px; text-align: right;">${formatNumberToCurrency(remainingDue)}</td>
                                <td style="padding: 3px 3px; border: 1px solid #ddd; background: red;">
                                    <input type="number" 
                                           class="form-control text-right sale-payment-amount" 
                                           data-sale-id="${saleId}"
                                           data-max="${remainingDue}"
                                           min="0" 
                                           max="${remainingDue}"
                                           step="0.01"
                                           value="${remainingDue.toFixed(2)}"
                                           style="padding: 3px 4px; font-size: 12px; height: 28px;">
                                </td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                    
                    // Recalcular total cuando cambien los montos individuales
                    calculateModalTotalPayment();
                } else {
                    tbody.html('<tr><td colspan="5" class="text-center" style="padding: 8px; font-size: 12px;">No hay ventas pendientes</td></tr>');
                }
            },
            error: function() {
                tbody.html('<tr><td colspan="5" class="text-center text-danger">Error al cargar ventas</td></tr>');
            }
        });
    }

    /**
     * Calcula el total del pago sumando los montos individuales de cada venta
     */
    function calculateModalTotalPayment() {
        let total = 0;
        $('.sale-payment-amount').each(function() {
            let val = parseFloat($(this).val()) || 0;
            total += val;
        });
        $('#pay_due_modal_amount').val(total.toFixed(2));
    }

    /**
     * Cuando el usuario escribe en un campo de monto individual, recalcular el total
     */
    $(document).on('input', '.sale-payment-amount', function() {
        let maxAmount = parseFloat($(this).data('max')) || 0;
        let currentValue = parseFloat($(this).val()) || 0;
        
        // Validar que no exceda el máximo
        if (currentValue > maxAmount) {
            $(this).val(maxAmount.toFixed(2));
        }
        
        calculateModalTotalPayment();
    });

    /**
     * Si el usuario modifica el monto total, distribuir proporcionalmente
     */
    $(document).on('input', '#pay_due_modal_amount', function() {
        let totalPayment = parseFloat($(this).val()) || 0;
        let remainingPayment = totalPayment;
        
        // Distribuir a cada venta pendiente en orden
        $('.sale-payment-amount').each(function() {
            if (remainingPayment <= 0) {
                $(this).val('0');
                return;
            }
            
            let maxAmount = parseFloat($(this).data('max')) || 0;
            let amountToAssign = Math.min(remainingPayment, maxAmount);
            
            $(this).val(amountToAssign.toFixed(2));
            remainingPayment -= amountToAssign;
        });
    });

    // Enviar formulario de pago
    $(document).on('click', '#pay_due_modal_submit', function() {
        let $btn = $(this);
        $btn.prop('disabled', true);
    
        let amountVal = $('#pay_due_modal_amount').val();
        let paymentVal = $('#pay_due_modal_payment_id').val();
        let base_url = $("#base_url_customer").val();
        
        // Construir sales_details (ventas con sus montos)
        let salesDetails = {};
        $('.sale-payment-amount').each(function() {
            let saleId = $(this).data('sale-id');
            let amount = parseFloat($(this).val()) || 0;
            
            if (amount > 0) {
                salesDetails[saleId] = amount;
            }
        });
        
        let formData = {
            customer_id: $('#pay_due_modal_customer_id').val(),
            date: $('#pay_due_modal_date').val(),
            amount: $('#pay_due_modal_amount').val(),
            payment_id: $('#pay_due_modal_payment_id').val(),
            note: $('#pay_due_modal_note').val(),
            sales_details: salesDetails
        };

        // Validación local rápida
        let error = false;
        if (!amountVal || parseFloat(amountVal) <= 0) {
            toastr['error']('El campo Monto es requerido y debe ser mayor a cero.', '');
            error = true;
        }
        if (!paymentVal) {
            toastr['error']('El campo Método de Pago es requerido.', '');
            error = true;
        }
        if (error) {
            $btn.prop('disabled', false);
            return;
        }

        $.ajax({
            url: base_url + "Sale/addCustomerDueReceiveAjax",
            method: "POST",
            data: formData,
            dataType: "json",
            success: function(resp) {
                if (resp.status == "ok") {
                    toastr['success'](resp.msg, 'Éxito');
                    
                    // Disparar impresión con los datos recibidos del servidor
                    trigger_due_receipt_print(resp.data);
                    
                    // Actualizar UI
                    fetch_customer_due(formData.customer_id); // Actualiza la deuda del cliente
                    loadRecentDuePayments(); // Recarga la lista de pagos en el modal
                    loadPendingSalesForPayment(formData.customer_id); // Recargar ventas pendientes

                    // Limpiar formulario para el siguiente pago
                    $('#pay_due_modal_amount').val('');
                    $('#pay_due_modal_note').val('');

                } else {
                    toastr['error'](resp.msg, 'Error');
                }
            },
            error: function(jqXHR, textStatus, errorThrown){
                toastr['error']('Error de comunicación con el servidor.', 'Error');
            },
            complete: function(){
                $btn.prop('disabled', false);
            }
        });
    });

    // --- FUNCIONES AUXILIARES ---

    // Carga los últimos 10 pagos en la tabla del modal
    function loadRecentDuePayments() {
        let listContainer = $('#recent_due_payments_list');
        listContainer.html('<tr><td colspan="5" class="text-center">Cargando...</td></tr>');

        $.ajax({
            url: base_url + "Sale/getLastTenDueReceives",
            method: "GET",
            dataType: "json",
            success: function(payments) {
                listContainer.empty();
                if (payments && payments.length > 0) {
                    payments.forEach(function(p) {
                        let row = `
                            <tr>
                                <td>${p.reference_no}</td>
                                <td>${p.only_date}</td>
                                <td>${p.customer_name}</td>
                                <td>${formatNumberToCurrency(p.amount)}</td>
                                <td>
                                    <button class="btn btn-xs btn-primary reprint-due-receipt" data-payment-id="${p.id}">
                                        <i class="fa fa-print"></i> Reimprimir
                                    </button>
                                </td>
                            </tr>`;
                        listContainer.append(row);
                    });
                } else {
                    listContainer.html('<tr><td colspan="5" class="text-center">No hay pagos recientes.</td></tr>');
                }
            },
            error: function() {
                listContainer.html('<tr><td colspan="5" class="text-center text-danger">Error al cargar pagos</td></tr>');
            }
        });
    }

    // Evento para el botón de reimprimir
    $(document).on('click', '.reprint-due-receipt', function() {
        let $btn = $(this);
        let payment_id = $btn.data('payment-id');

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>'); // Muestra un loader

        // 1. Llamada AJAX para obtener los datos completos del recibo
        $.ajax({
            url: base_url + "Sale/getDueReceiveDetailsForPrint/" + payment_id,
            method: "GET",
            dataType: "json",
            success: function(resp) {
                if (resp.status == "ok") {
                    // 2. Si tenemos los datos, llamamos a la función de impresión central
                    // resp.data ahora contiene todo lo que necesitamos (fecha, monto, cliente, saldo, etc.)
                    trigger_due_receipt_print(resp.data);
                } else {
                    toastr['error'](resp.msg, 'Error');
                }
            },
            error: function() {
                toastr['error']('No se pudieron obtener los detalles para la reimpresión.', 'Error');
            },
            complete: function() {
                // Restaura el botón
                $btn.prop('disabled', false).html('<i class="fa fa-print"></i> Reimprimir');
            }
        });
    });


    // Función central para decidir qué método de impresión usar
    function trigger_due_receipt_print(data) {
        if (print_type == "printer_app") {
            $.ajax({
                url: base_url + "Sale/printer_app_due_receive/" + data.payment_id,
                method: "GET",
                success: function(base64) {
                    if (base64.startsWith('Error:')) {
                         toastr['error'](base64, 'Error de Impresión');
                    } else {
                         window.location.href = 'print://' + base64;
                    }
                },
                error: function() {
                    alert("Error al generar el ticket para la impresora.");
                }
            });
        } else {
            // Para impresión web, obtener los detalles completos del pago (incluyendo ventas afectadas)
            $.ajax({
                url: base_url + "Sale/getPaymentDetailAjax",
                method: "GET",
                data: { payment_id: data.payment_id },
                dataType: "json",
                success: function(response) {
                    if (response.status === 'success') {
                        print_pay_due_receipt(response.data);
                    } else {
                        print_pay_due_receipt(data); // Fallback a datos básicos
                    }
                },
                error: function() {
                    print_pay_due_receipt(data); // Fallback a datos básicos
                }
            });
        }
    }

    function print_pay_due_receipt(data) {
        
      let outlet_address = $("#outlet_address").val();
      let outlet_phone = $("#outlet_phone").val();
      let outlet_name = $("#outlet_name").val();
      let inv_tax_registration_no = $("#inv_tax_registration_no").val();
      let outlet_tax_registration_no = $("#outlet_tax_registration_no").val();
      let invoice_logo = base_url+`images/`+$("#invoice_logo").val();
        // --- Datos del Outlet (simulados desde PHP a JS) ---
        const outlet = {
            name: outlet_name,
            address: outlet_address,
            phone: outlet_phone,
            tax_reg_no: outlet_tax_registration_no,
            invoice_logo: invoice_logo,
            tax_name: inv_tax_registration_no
        };

        // --- HTML del Ticket ---
        let salesTableHTML = '';
        if (data.sales && data.sales.length > 0) {
            salesTableHTML = `
                <div class="divider"></div>
                <h5 style="text-align: center; margin-bottom: 10px;">VENTAS PAGADAS</h5>
                <table style="width: 100%; font-size: 11px; border-collapse: collapse;">
                    <thead style="border-bottom: 1px solid #000;">
                        <tr>
                            <th style="text-align: left; padding: 3px 0;">Venta</th>
                            <th style="text-align: center; padding: 3px 0;">Fecha</th>
                            <th style="text-align: right; padding: 3px 0;">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.sales.map(sale => `
                            <tr style="border-bottom: 1px dotted #ccc;">
                                <td style="text-align: left; padding: 3px 0;">${sale.sale_no}</td>
                                <td style="text-align: center; padding: 3px 0;">${sale.sale_date}</td>
                                <td style="text-align: right; padding: 3px 0;">${sale.amount}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
        }

        let ticketHTML = `
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <title>Recibo de Pago</title>
                <style>
                    body {
                        font-family: 'Courier New', Courier, monospace;
                        margin: 0 auto;
                        font-size: 12px;
                    }
                    .ticket-header, .ticket-footer {
                        text-align: center;
                    }
                    .ticket-header img {
                        max-width: 150px;
                        margin-bottom: 10px;
                    }
                    .ticket-header h3 {
                        margin: 5px 0;
                    }
                    .ticket-body {
                        margin-top: 20px;
                    }
                    .ticket-body table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    .ticket-body td {
                        padding: 2px 0;
                    }
                    .ticket-body .label {
                        font-weight: bold;
                    }
                    .total {
                        font-size: 16px;
                        font-weight: bold;
                        margin-top: 10px;
                    }
                    .divider {
                        border-top: 1px dashed #000;
                        margin: 10px 0;
                    }
                </style>
            </head>
            <body>
                <div class="ticket">
                    <header class="ticket-header">
                        ${outlet.invoice_logo ? `<img src="${outlet.invoice_logo}" alt="Logo">` : ''}
                        <h3>${outlet.name}</h3>
                        <p>
                            ${outlet.address}<br>
                            ${outlet.phone}<br>
                            ${outlet.tax_reg_no ? `${outlet.tax_name}: ${outlet.tax_reg_no}` : ''}
                        </p>
                    </header>

                    <div class="divider"></div>

                    <section class="ticket-body">
                        <h4 style="text-align:center;">RECIBO DE PAGO</h4>
                        <table class="table_register_details table_sale_details top_margin_15 dataTable no-footer" id="DataTables_Table_0" role="grid">
                            <tr>
                                <td class="label">Fecha:</td>
                                <td>${data.date}</td>
                            </tr>
                            <tr>
                                <td class="label">Ref No:</td>
                                <td>${data.ref_no}</td>
                            </tr>
                            <tr>
                                <td class="label">Cliente:</td>
                                <td>${data.customer}</td>
                            </tr>
                            <tr>
                                <td class="label">Método:</td>
                                <td>${data.payment_method}</td>
                            </tr>
                        </table>

                        <div class="divider"></div>

                        <table>
                            <tr>
                                <td class="label total">TOTAL PAGO:</td>
                                <td class="total" style="text-align:right;">${data.amount}</td>
                            </tr>
                        </table>

                        ${salesTableHTML}

                        ${data.note ? `<div class="divider"></div><p><b>Nota:</b> ${data.note}</p>` : ''}
                    </section>

                    <div class="divider"></div>

                    <footer class="ticket-footer">
                        <p>¡Gracias por su pago!</p>
                    </footer>
                </div>
            </body>
            </html>
        `;

        // --- Lógica para abrir la ventana emergente e imprimir ---
        const printWindow = window.open('', 'PRINT', 'height=600,width=400');
        printWindow.document.write(ticketHTML);
        printWindow.document.close();
        printWindow.focus();
        
        // Esperar a que el contenido se cargue completamente (especialmente imágenes)
        printWindow.onload = function() {
            printWindow.print();
            // printWindow.close();
        };

    }

});
