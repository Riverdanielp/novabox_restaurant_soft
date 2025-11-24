/*checking menu access and hide*/
let company_id_indexdb  = jQuery("#company_id_indexdb").val();
let user_id  = jQuery("#user_id").val();
jQuery(".menu_assign_class").each(function() {
    let this_access = jQuery(this).attr("data-access");
    if((window.menu_objects).indexOf(this_access) > -1) {

    } else {
        if(this_access=="saas"){
            if(company_id_indexdb!=1 && user_id!=1){
                jQuery(this).remove();
            }
        }else{
            if(this_access!=undefined){
                jQuery(this).remove();
            }
        }

    }

});

jQuery(".treeview").each(function() {
    if(!(jQuery(this).find(".treeview-menu").find("li").length)){
        jQuery(this).remove();
    }
});

jQuery(".check_main_menu").each(function() {
    if(!(jQuery(this).find(".menu_assign_class").length)){
        jQuery(this).remove();
    }
});

jQuery(".sub_sub").each(function() {
    if(!(jQuery(this).find(".menu_assign_class").length)){
        jQuery(this).remove();
    }
});

jQuery(".setting_report").each(function() {
    if(!(jQuery(this).find(".menu_assign_class").length)){
        jQuery(this).remove();
    }
});
// material icon init
feather.replace();
  
let ir_precision_h = jQuery("#ir_precision").val();
let window_height = jQuery(window).height();
let main_header_height = jQuery('.main-header').height();
let user_panel_height = jQuery('.user-panel').height();
let left_menu_height_should_be = (parseFloat(window_height) - (parseFloat(main_header_height) + parseFloat(
    user_panel_height))).toFixed(ir_precision_h);
left_menu_height_should_be = (parseFloat(left_menu_height_should_be) - parseFloat(60)).toFixed(ir_precision_h);

base_url= jQuery("#base_url_").val();
let csrf_name_= jQuery("#csrf_name_").val();
let csrf_value_= jQuery("#csrf_value_").val();
let not_closed_yet= jQuery("#not_closed_yet").val();
let opening_balance= jQuery("#opening_balance").val();
let customer_due_receive= jQuery("#customer_due_receive").val();
let paid_amount= jQuery("#paid_amount").val();
let in_ = jQuery("#in_").val();
let cash= jQuery("#cash").val();
let paypal= jQuery("#paypal").val();
let sale= jQuery("#sale").val();
let card= jQuery("#card").val();
let register_not_open= jQuery("#register_not_open").val();
let currency = '';

jQuery.ajax({
    url: base_url+"Register/checkRegisterAjax",
    method: "POST",
    data: {
        csrf_name_: csrf_value_
    },
    success: function(response) {
        if (response == '2') {
            jQuery('#close_register_button').css('display', 'none');
        } else {
            jQuery('#close_register_button').css('display', 'block');

        }
    },
    error: function() {
        alert("error");
    }
});

jQuery('#register_close').on('click', function() {
    let r = confirm("Are you sure to close register?");

    if (r == true) {
        jQuery.ajax({
            url: base_url+"Sale/closeRegister",
            method: "POST",
            data: {
                csrf_name_: csrf_value_
            },
            success: function(response) {
                swal({
                    title: 'Alert',
                    text: 'Register closed successfully!!',
                    confirmButtonColor: '#b6d6f6'
                });
                jQuery('#close_register_button').hide();

            },
            error: function() {
                alert("error");
            }
        });
    }
});

jQuery('.set_collapse').on('click', function() {
    let status = Number(jQuery(this).attr("data-status"));
    let status_tmp = '';
    if(status==1){
        jQuery(this).attr('data-status',2);
        status_tmp = "No";
    }else{
        jQuery(this).attr('data-status',1);
        status_tmp = "Yes";
    }
    jQuery.ajax({
        url: base_url+"authentication/set_collapse",
        method: "POST",
        data: {
            status: status_tmp,
            csrf_name_: csrf_value_
        },
        success: function(response) {

        },
        error: function() {
            alert("error");
        }
    });
});

function IsJsonString(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}

function todaysSummary() {

    jQuery.ajax({
        url: base_url+"Report/todayReport",
        method: 'get',
        dataType: 'json',
        data: {
        csrf_name_: csrf_value_
    },
    success: function(data) {
        jQuery("#purchase_today_").text(currency + data
            .total_purchase_amount);
        jQuery("#sale_today").text(currency + data
            .total_sales_amount);
        jQuery("#totalVat").text(currency + data
            .total_sales_vat);
        jQuery("#Expense").text(currency + data
            .expense_amount);
        jQuery("#supplierDuePayment").text(currency + data
            .supplier_payment_amount);
        jQuery("#customerDueReceive").text(currency + data
            .customer_receive_amount);
        jQuery("#waste_today").text(currency + data
            .total_loss_amount);
        jQuery("#balance").text(currency + data.balance);
        jQuery("#sale_return_amount").text(currency + data.total_total_refund);
       
    }
});
    jQuery("#todaysSummary").modal("show");
}

function draw_modal() {
    let area_id = Number(jQuery(".area_id").val());
    if(area_id){
        jQuery("#draw_modal").modal("show");
    }
}
function image_object_modal() {
    let area_id = Number(jQuery(".area_id").val());
    if(area_id){
        jQuery("#image_object_modal").modal("show");
    }
}

display_date_time();
function getNewDateTime() {
    let refresh = 1000; // Refresh rate in milli seconds
    setTimeout(display_date_time, refresh);
}
function display_date_time() {
    //for date and time
    let today = new Date();
    let dd = today.getDate();
    let mm = today.getMonth() + 1; //January is 0!
    let yyyy = today.getFullYear();
    if (dd < 10) {
        dd = "0" + dd;
    }
    if (mm < 10) {
        mm = "0" + mm;
    }
    let time_a = new Date().toLocaleTimeString();
    let today_date = yyyy + "-" + mm + "-" + dd;

    jQuery("#closing_register_time").html(today_date+" "+time_a);
    /* recursive call for new time*/
    getNewDateTime();
}



