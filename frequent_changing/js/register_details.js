$(function () {
    "use strict";
    let base_url = $("#base_url_customer").val();
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
    
                    $(".modal_loader").hide();
                    $(".html_content").html(response.html_content_for_div);
    
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
