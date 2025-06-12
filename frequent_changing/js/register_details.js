
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

                $(`#datatable`).DataTable({
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

                    $(`#datatable`).DataTable({
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
                    // DataTable para la tabla principal
                    $(`#datatable`).DataTable({
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
    
                    // DataTable para tablas detalle
                    $('.table_sale_details').DataTable({
                        'autoWidth': false,
                        'ordering': false,
                        'paging': false,
                        'bFilter': false,
                        'info': false,
                        'responsive': true,
                        'language': { 'emptyTable': 'Sin datos de ventas' }
                    });
                    $('.table_expense_details').DataTable({
                        'autoWidth': false,
                        'ordering': false,
                        'paging': false,
                        'bFilter': false,
                        'info': false,
                        'responsive': true,
                        'language': { 'emptyTable': 'Sin gastos registrados' }
                    });
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
    
    // Función para renderizar el ticket dentro del modal
    function renderInventarioTicket(inventoryData, ingredientCategories, hora, outletName, username) {
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
                        <th style="width:22%;">Cod</th>
                        <th style="width:48%;">Prod</th>
                        <th style="width:20%;">Cant</th>
                        <th style="width:10%;">...</th>
                    </tr>
                </thead>
                <tbody>`;
            items.forEach(function(item) {
                let conversion = parseFloat(item.conversion_rate) || 1;
                let totalStock = (item.total_purchase * conversion)
                    - item.total_consumption - item.total_modifiers_consumption - item.total_waste
                    + item.total_consumption_plus - item.total_consumption_minus
                    + (item.total_transfer_plus * conversion) - (item.total_transfer_minus * conversion)
                    + (item.total_transfer_plus_2 * conversion) - (item.total_transfer_minus_2 * conversion)
                    + (item.total_production * conversion);
    
                let total_sale_unit = conversion == 0 ? 0 : (totalStock / conversion);
                total_sale_unit = Math.floor(total_sale_unit);
    
                let cantidad = (item.ing_type == "Plain Ingredient" && item.is_direct_food != 2 && conversion != 1)
                    ? total_sale_unit+ " " + (totalStock % conversion)
                    : (parseFloat(total_sale_unit) + ((totalStock) ? (totalStock % conversion) : 0));
    
                html += `
                    <tr>
                        <td style="">${item.code}</td>
                        <td style="">${item.name}</td>
                        <td style="">${cantidad}</td>
                        <td style="font-size:10px;letter-spacing:2px;color:#ccc;text-align:center;">.............</td>
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
    
    // Imprimir el área del ticket (solo el contenido, no el modal completo)
    $(document).on('click', '#btn_print_ticket_inventario', function() {
        let printContents = document.getElementById('ticket_print_area').innerHTML;
        let mywindow = window.open('', 'PRINT', 'height=600,width=400');
        mywindow.document.write(`
            <html>
            <head>
                <title>Reporte de Inventario</title>
                <style>
                    @media print {
                        body, html { width: 80mm; }
                    }
                    body { width: 80mm; font-family: Arial, sans-serif; font-size: 8px; }
                    table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
                    th, td { border-bottom: 1px dotted #ccc; padding: 3px; text-align: left;; font-size: 10px; }
                    th { font-weight: bold; }
                    .dots { letter-spacing: 2px; color: #ccc; font-size: 11px; text-align: center; }
                    .category-title { margin-top: 8px; margin-bottom: 2px; font-weight: bold; }
                </style>
            </head>
            <body>
                ${printContents}
            </body>
            </html>
        `);
        mywindow.document.close();
        setTimeout(function(){
            mywindow.focus();
            mywindow.print();
            // mywindow.close(); // Descomenta si quieres cerrar automáticamente
        }, 200);
    });

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
});
