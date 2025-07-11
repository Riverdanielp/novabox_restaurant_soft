<?php
//get company information
$getCompanyInfo = getCompanyInfo();
$inv_label = isset($getCompanyInfo->invoice_label_setting) && $getCompanyInfo->invoice_label_setting?json_decode($getCompanyInfo->invoice_label_setting):'';
$waiter_app_status = $this->session->userdata('is_waiter');
$is_self_order = $this->session->userdata('is_self_order');
$language_manifesto = $this->session->userdata('language_manifesto');
$waiter_app_status=isset($waiter_app_status) && $waiter_app_status?$waiter_app_status:'';
$default_waiter_id = 0;
$outlet = getOutletById($this->session->userdata('outlet_id'));
$role = $this->session->userdata('designation');
$user_id = $this->session->userdata('user_id');
$user_data = $this->Common_model->getDataById($user_id, "tbl_users");
$getOutletInfo = $this->Common_model->getDataById($this->session->userdata('outlet_id'), "tbl_outlets");
$getPreimpresoPrinter = $this->Common_model->getDataById($getOutletInfo->preimpreso_printer_id, "tbl_printers");
$registro_ocultar = $getOutletInfo->registro_ocultar;
$registro_detallado = $getOutletInfo->registro_detallado;

if ($getPreimpresoPrinter) {
    $preimpreso_printer_name = $getPreimpresoPrinter->path;
    $preimpreso_printer_ipv4 = $getPreimpresoPrinter->ipvfour_address;
} else {
    $preimpreso_printer_name = '';
    $preimpreso_printer_ipv4 = '';
}

foreach ($waiters as $waiter){

    if(str_rot13($language_manifesto)=="eriutoeri"):
        $default_waiter = $outlet->default_waiter;
    else:
        $default_waiter = $getCompanyInfo->default_waiter;
    endif;

    if(isset($role) && $role == "Waiter"){
        if($waiter->id==$user_id){
            $default_waiter_id = $user_id;
        }
    }else{
        if($waiter->id==$default_waiter){
            $default_waiter_id = $waiter->id;
        }
    }
}


?>


<input type="hidden" id="product_label" value="<?php echo isset($inv_label->product_label) && $inv_label->product_label ? $inv_label->product_label : '' ?>">
<input type="hidden" id="quantity_label" value="<?php echo isset($inv_label->quantity_label) && $inv_label->quantity_label ? $inv_label->quantity_label : '' ?>">
<input type="hidden" id="unit_price_label" value="<?php echo isset($inv_label->unit_price_label) && $inv_label->unit_price_label ? $inv_label->unit_price_label : '' ?>">
<input type="hidden" id="subtotal_label" value="<?php echo isset($inv_label->subtotal_label) && $inv_label->subtotal_label ? $inv_label->subtotal_label : '' ?>">
<input type="hidden" id="category_label" value="<?php echo isset($inv_label->category_label) && $inv_label->category_label ? $inv_label->category_label : '' ?>">
<input type="hidden" id="total_quantity_label" value="<?php echo isset($inv_label->total_quantity_label) && $inv_label->total_quantity_label ? $inv_label->total_quantity_label : '' ?>">
<input type="hidden" id="item_discount_label" value="<?php echo isset($inv_label->item_discount_label) && $inv_label->item_discount_label ? $inv_label->item_discount_label : '' ?>">
<input type="hidden" id="discount_unit_price_label" value="<?php echo isset($inv_label->discount_unit_price_label) && $inv_label->discount_unit_price_label ? $inv_label->discount_unit_price_label : '' ?>">


<!--hidden fields for js usages-->
<input type="hidden" id="print_kitchen" value="<?php echo escape_output($user_data->print_kitchen)?>">
<input type="hidden" id="print_pos_id" value="<?php echo escape_output($user_data->print_pos_id)?>">
<input type="hidden" id="preimpreso_mode" value="<?php echo escape_output($getOutletInfo->preimpreso_mode)?>">
<input type="hidden" id="preimpreso_printer_id" value="<?php echo escape_output($getOutletInfo->preimpreso_printer_id)?>">
<input type="hidden" id="preimpreso_printer_name" value="<?php echo escape_output($preimpreso_printer_name)?>">
<input type="hidden" id="preimpreso_printer_ipv4" value="<?php echo escape_output($preimpreso_printer_ipv4)?>">

<input type="hidden" id="registro_ocultar" value="<?php echo escape_output($getOutletInfo->registro_ocultar)?>">
<input type="hidden" id="registro_detallado" value="<?php echo escape_output($getOutletInfo->registro_detallado)?>">

<input type="hidden" id="base_url_pos" value="<?php echo base_url()?>">
<input type="hidden" id="waiter_app_status" value="<?php echo escape_output($waiter_app_status)?>">
<input type="hidden" id="is_self_order" value="<?php echo escape_output($is_self_order)?>">
<input type="hidden" id="is_self_order_tmp" value="<?php echo escape_output($is_self_order)?>">
<input type="hidden" id="ur_role" value="<?php echo escape_output($this->session->userdata('role'))?>">
<input type="hidden" id="inv_collect_tax" value="<?php echo escape_output($this->session->userdata('collect_tax'))?>">
<input type="hidden" id="tax_is_gst" value="<?php echo escape_output($this->session->userdata('tax_is_gst'))?>">
<input type="hidden" id="decimals_separator" value="<?php echo escape_output($this->session->userdata('decimals_separator'))?>">
<input type="hidden" id="thousands_separator" value="<?php echo escape_output($this->session->userdata('thousands_separator'))?>">
<input type="hidden" id="ir_precision" value="<?php echo escape_output($getCompanyInfo->precision)?>">
<input type="hidden" id="comanda_required" value="<?php echo escape_output($outlet->comanda_required)?>">
<input type="hidden" id="user_designation" value="<?php echo $this->session->userdata('designation') ?>">


<input type="hidden" id="currency_position" value="<?php echo escape_output($this->session->userdata('currency_position'))?>">
<input type="hidden" id="same_or_diff_state" value="">
<input type="hidden" id="username_short" value="<?php echo escape_output($this->session->userdata('code_short'))?>">
<input type="hidden" id="hidden_currency" value="<?php echo escape_output($this->session->userdata('currency'))?>">
<input type="hidden" id="food_menu_tooltip" value="<?php echo escape_output($this->session->userdata('food_menu_tooltip'))?>">
<input type="hidden" id="when_clicking_on_item_in_pos" value="<?php echo escape_output($getCompanyInfo->when_clicking_on_item_in_pos)?>">
<input type="hidden" id="default_order_type" value="<?php echo escape_output($this->session->userdata('default_order_type'))?>">
<input type="hidden" id="is_loyalty_enable" value="<?php echo escape_output($this->session->userdata('is_loyalty_enable'))?>">
<input type="hidden" id="pre_or_post_payment" value="<?php echo escape_output($this->session->userdata('pre_or_post_payment'))?>">
<input type="hidden" id="minimum_point_to_redeem" value="<?php echo escape_output($this->session->userdata('minimum_point_to_redeem'))?>">
<input type="hidden" id="loyalty_rate" value="<?php echo escape_output($this->session->userdata('loyalty_rate'))?>">
<input type="hidden" id="open_cash_drawer_when_printing_invoice" value="<?php echo escape_output($this->session->userdata('open_cash_drawer_when_printing_invoice'))?>">
<input type="hidden" id="is_rounding_enable" value="<?php echo escape_output($this->session->userdata('is_rounding_enable'))?>">
<input type="hidden" id="split_bill" value="<?php echo escape_output($this->session->userdata('split_bill'))?>">
<input type="hidden" id="register_close" value="<?php echo lang('register_close'); ?>">
<input type="hidden" id="alert_free_item" value="<?php echo lang('alert_free_item'); ?>">
<input type="hidden" id="loyalty_point_is_not_available" value="<?php echo lang('loyalty_point_is_not_available'); ?>">
<input type="hidden" id="tool_tip_loyalty_point" value="<?php echo lang('tool_tip_loyalty_point'); ?>">
<input type="hidden" id="minimum_point_to_redeem_is" value="<?php echo lang('minimum_point_to_redeem_is'); ?>">
<input type="hidden" id="alert_free_item_edit" value="<?php echo lang('alert_free_item_edit'); ?>">
<input type="hidden" id="loyalty_point_error" value="<?php echo lang('loyalty_point_error'); ?>">
<input type="hidden" id="sales_currently_in_local" value="<?php echo lang('sales_currently_in_local'); ?>">
<input type="hidden" id="login_first_msg" value="<?php echo lang('login_first_msg'); ?>">
<input type="hidden" id="txt_balance" value="<?php echo lang('balance'); ?>">
<input type="hidden" id="draft_error" value="<?php echo lang('draft_error'); ?>">
<input type="hidden" id="action_error" value="<?php echo lang('action_error'); ?>">
<input type="hidden" id="register_error" value="<?php echo lang('register_error'); ?>">
<input type="hidden" id="alert_free_item_increase" value="<?php echo lang('alert_free_item_increase'); ?>">
<input type="hidden" id="you_need_to_add_at_least_1_item_on_right_selected_customer" value="<?php echo lang('you_need_to_add_at_least_1_item_on_right_selected_customer'); ?>">
<input type="hidden" id="please_add_at_least_1_item_before_checkout" value="<?php echo lang('please_add_at_least_1_item_before_checkout'); ?>">
<input type="hidden" id="warning" value="<?php echo lang('alert'); ?>">
<input type="hidden" id="a_error" value="<?php echo lang('error'); ?>">
<input type="hidden" id="ok" value="<?php echo lang('ok'); ?>">
<input type="hidden" id="cancel" value="<?php echo lang('cancel'); ?>">
<input type="hidden" id="please_select_order_to_proceed" value="<?php echo lang('please_select_order_to_proceed'); ?>">
<input type="hidden" id="status_txt" value="<?php echo lang('status'); ?>">
<input type="hidden" id="feature_sales" value="<?php echo lang('feature_sales'); ?>">
<input type="hidden" id="you_cant_modify_the_order" value="<?php echo lang('you_cant_modify_the_order'); ?>">
<input type="hidden" id="my_orders" value="<?php echo lang('my_orders'); ?>">
<input type="hidden" id="self_orders" value="<?php echo lang('self_orders'); ?>">
<input type="hidden" id="set_as_approved" value="<?php echo lang('set_as_approved'); ?>">
<input type="hidden" id="exceeciding_seat" value="<?php echo lang('exceeding_sit'); ?>">
<input type="hidden" id="set_as_running_order" value="<?php echo lang('set_as_running_order'); ?>">
<input type="hidden" id="date_txt" value="<?php echo lang('date'); ?>">
<input type="hidden" id="seat_greater_than_zero" value="<?php echo lang('seat_greater_than_zero'); ?>">
<input type="hidden" id="are_you_sure_cancel_booking" value="<?php echo lang('are_you_sure_cancel_booking'); ?>">
<input type="hidden" id="are_you_sure" value="<?php echo lang('are_you_sure'); ?>">
<input type="hidden" id="are_you_delete_notification" value="<?php echo lang('are_you_delete_notification'); ?>">
<input type="hidden" id="stock_not_available" value="<?php echo lang('stock_not_available'); ?>">
<input type="hidden" id="no_notification_select" value="<?php echo lang('no_notification_select'); ?>">
<input type="hidden" id="are_you_delete_all_hold_sale" value="<?php echo lang('are_you_delete_all_hold_sale'); ?>">
<input type="hidden" id="no_hold" value="<?php echo lang('no_hold'); ?>">
<input type="hidden" id="sure_delete_this_hold" value="<?php echo lang('sure_delete_this_hold'); ?>">
<input type="hidden" id="please_select_hold_sale" value="<?php echo lang('please_select_hold_sale'); ?>">
<input type="hidden" id="delete_only_for_admin" value="<?php echo lang('delete_only_for_admin'); ?>">
<input type="hidden" id="this_item_is_under_cooking_please_contact_with_admin" value="<?php echo lang('this_item_is_under_cooking_please_contact_with_admin'); ?>">
<input type="hidden" id="this_item_already_cooked_please_contact_with_admin" value="<?php echo lang('this_item_already_cooked_please_contact_with_admin'); ?>">
<input type="hidden" id="sure_delete_this_order" value="<?php echo lang('sure_delete_this_order'); ?>">
<input type="hidden" id="sure_remove_this_order" value="<?php echo lang('sure_remove_this_order'); ?>">
<input type="hidden" id="sure_cancel_this_order" value="<?php echo lang('sure_cancel_this_order'); ?>">
<input type="hidden" id="sure_close_this_order" value="<?php echo lang('sure_close_this_order'); ?>">
<input type="hidden" id="please_select_an_order" value="<?php echo lang('please_select_an_order'); ?>">
<input type="hidden" id="cart_not_empty" value="<?php echo lang('cart_not_empty'); ?>">
<input type="hidden" id="cart_not_empty_want_to_clear" value="<?php echo lang('cart_not_empty_want_to_clear'); ?>">
<input type="hidden" id="progress_or_done_kitchen" value="<?php echo lang('progress_or_done_kitchen'); ?>">
<input type="hidden" id="order_in_progress_or_done" value="<?php echo lang('order_in_progress_or_done'); ?>">
<input type="hidden" id="close_order_without" value="<?php echo lang('close_order_without'); ?>">
<input type="hidden" id="want_to_close_order" value="<?php echo lang('want_to_close_order'); ?>">
<input type="hidden" id="please_select_open_order" value="<?php echo lang('please_select_open_order'); ?>">
<input type="hidden" id="cart_empty" value="<?php echo lang('cart_empty'); ?>">
<input type="hidden" id="select_a_customer" value="<?php echo lang('select_a_customer'); ?>">
<input type="hidden" id="loyalty_point_not_applicable" value="<?php echo lang('loyalty_point_not_applicable_for_walk_in_customer'); ?>">
<input type="hidden" id="select_a_waiter" value="<?php echo lang('select_a_waiter'); ?>">
<input type="hidden" id="your_added_payment_method_will_remove" value="<?php echo lang('your_added_payment_method_will_remove'); ?>">
<input type="hidden" id="some_of_your_cart_amounts_are_not_spitted_yet" value="<?php echo lang('some_of_your_cart_amounts_are_not_spitted_yet'); ?>">
<input type="hidden" id="tax_type" value="<?php echo escape_output($getCompanyInfo->tax_type)?>">
<input type="hidden" id="attendance_type" value="<?php echo escape_output($getCompanyInfo->attendance_type)?>">
<input type="hidden" id="delivery_not_possible_walk_in"
       value="<?php echo lang('delivery_not_possible_walk_in'); ?>">
<input type="hidden" id="delivery_for_customer_must_address"
       value="<?php echo lang('delivery_for_customer_must_address'); ?>">
<input type="hidden" id="select_dine_take_delivery" value="<?php echo lang('select_dine_take_delivery'); ?>">
<input type="hidden" id="added_running_order" value="<?php echo lang('added_running_order'); ?>">
<input type="hidden" id="txt_err_pos_1" value="<?php echo lang('txt_err_pos_1'); ?>">
<input type="hidden" id="txt_err_pos_2" value="<?php echo lang('txt_err_pos_2'); ?>">
<input type="hidden" id="txt_err_pos_3" value="<?php echo lang('txt_err_pos_3'); ?>">
<input type="hidden" id="txt_err_pos_4" value="<?php echo lang('txt_err_pos_4'); ?>">
<input type="hidden" id="txt_err_pos_5" value="<?php echo lang('txt_err_pos_5'); ?>">
<input type="hidden" id="fullscreen_1" value="<?php echo lang('fullscreen_1'); ?>">
<input type="hidden" id="fullscreen_2" value="<?php echo lang('fullscreen_2'); ?>">
<input type="hidden" id="maximum_spit_is" value="<?php echo lang('maximum_spit_is'); ?>">
<input type="hidden" id="amount_txt" value="<?php echo lang('amount'); ?>">
<input type="hidden" id="loyalty_point_txt" value="<?php echo lang('loyalty_point'); ?>">
<input type="hidden" id="place_order" value="<?php echo lang('place_order'); ?>">
<input type="hidden" id="update_order" value="<?php echo lang('update_order'); ?>">
<input type="hidden" id="price_txt" value="<?php echo lang('price'); ?>">
<input type="hidden" id="note_txt" value="<?php echo lang('note'); ?>">
<input type="hidden" id="note_txt" value="<?php echo lang('note'); ?>">
<input type="hidden" id="combo_txt" value="<?php echo lang('combo_txt'); ?>">
<input type="hidden" id="inv_total_item" value="<?php echo lang('Total_Item_s') ?>">
<input type="hidden" id="inv_server" value="<?php echo lang('server') ?>">
<input type="hidden" id="inv_total" value="<?php echo lang('total') ?>">
<input type="hidden" id="inv_paid" value="<?php echo lang('paid') ?>">
<input type="hidden" id="inv_sale_date" value="<?php echo lang('sale_date') ?>">
<input type="hidden" id="inv_sub_total" value="<?php echo lang('sub_total') ?>">
<input type="hidden" id="inv_discount" value="<?php echo lang('Disc_Amt_p') ?>">
<input type="hidden" id="inv_given_amount" value="<?php echo lang('given_amount') ?>">
<input type="hidden" id="inv_change_amount" value="<?php echo lang('change_amount') ?>">
<input type="hidden" id="inv_service_charge" value="<?php echo lang('service_charge') ?>">
<input type="hidden" id="inv_delivery_charge" value="<?php echo lang('inv_delivery_charge') ?>">
<input type="hidden" id="inv_charge" value="<?php echo lang('charge') ?>">
<input type="hidden" id="inv_offline" value="<?php echo lang('offline') ?>">
<input type="hidden" id="inv_online" value="<?php echo lang('online') ?>">
<input type="hidden" id="inv_order_number" value="<?php echo lang('order_number') ?>">
<input type="hidden" id="inv_checkout" value="<?php echo lang('Checkout') ?>">
<input type="hidden" id="inv_vat" value="<?php echo lang('vat') ?>">
<input type="hidden" id="inv_tips" value="<?php echo lang('tips') ?>">
<input type="hidden" id="inv_grand_total" value="<?php echo lang('grand_total') ?>">
<input type="hidden" id="inv_paid_amount" value="<?php echo lang('paid_amount') ?>">
<input type="hidden" id="inv_due_amount" value="<?php echo lang('due_amount') ?>">
<input type="hidden" id="inv_total_payable" value="<?php echo lang('total_payable') ?>">
<input type="hidden" id="inv_payment_method" value="<?php echo lang('payment_method') ?>">
<input type="hidden" id="inv_invoice_no" value="<?php echo lang('Invoice_No') ?>">
<input type="hidden" id="inv_phone" value="<?php echo lang('phone') ?>">
<input type="hidden" id="inv_tax_registration_no" value="<?php echo escape_output($this->session->userdata('tax_title'))?>">
<input type="hidden" id="inv_date" value="<?php echo lang('date') ?>">
<input type="hidden" id="inv_sales_associate" value="<?php echo lang('Sales_Associate') ?>">
<input type="hidden" id="inv_customer" value="<?php echo lang('customer') ?>">
<input type="hidden" id="inv_address" value="<?php echo lang('address') ?>">
<input type="hidden" id="inv_gst_number" value="<?php echo lang('gst_number') ?>">
<input type="hidden" id="inv_waiter" value="<?php echo lang('waiter') ?>">
<input type="hidden" id="inv_table" value="<?php echo lang('table') ?>">
<input type="hidden" id="inv_delivery_status" value="<?php echo lang('delivery_status') ?>">
<input type="hidden" id="inv_order_type" value="<?php echo lang('order_type') ?>">
<input type="hidden" id="inv_usage_points" value="<?php echo lang('UsagePoints') ?>">
<input type="hidden" id="inv_dine" value="<?php echo lang('dine') ?>">
<input type="hidden" id="inv_take_away" value="<?php echo lang('take_away') ?>">
<input type="hidden" id="inv_delivery" value="<?php echo lang('delivery') ?>">
<input type="hidden" id="inv_bill_no" value="<?php echo lang('Bill_No') ?>">
<input type="hidden" id="inv_token_number" value="<?php echo lang('token_number') ?>">
<input type="hidden" id="order_type_changing_alert" value="<?php echo lang('order_type_changing_alert') ?>">
<input type="hidden" id="assets_vers" value="<?php echo VERS() ?>">
<input type="hidden" id="selected_number" value="">
<input type="hidden" id="selected_number_name" value="">

<input type="hidden" id="modifiers_txt" value="<?php echo lang('modifiers'); ?>">
<input type="hidden" id="quantity_not_available" value="<?php echo lang('quantity_not_available'); ?>">
<input type="hidden" id="amount_not_available" value="<?php echo lang('amount_not_available'); ?>">
<input type="hidden" id="item_add_success" value="<?php echo lang('item_add_success'); ?>">
<input type="hidden" id="already_added" value="<?php echo lang('Already_added'); ?>">
<input type="hidden" id="close_order_msg" value="<?php echo lang('close_order_msg'); ?>">
<input type="hidden" id="cancel_order_msg" value="<?php echo lang('cancel_order_msg'); ?>">
<input type="hidden" id="default_customer_hidden" value="<?php echo escape_output($getCompanyInfo->default_customer); ?>">
<input type="hidden" id="default_waiter_hidden" value="<?php echo escape_output($default_waiter_id); ?>">
<input type="hidden" id="default_payment_hidden"
       value="<?php echo escape_output($getCompanyInfo->default_payment); ?>">
<input type="hidden" id="selected_invoice_sale_customer" value="">
<input type="hidden" id="saas_m_ch" value="<?php echo file_exists(APPPATH.'controllers/Service.php')?'yes':''?>">
<input type="hidden" id="not_closed_yet" value="<?php echo lang('not_closed_yet'); ?>">
<input type="hidden" id="opening_balance" value="<?php echo lang('opening_balance'); ?>">
<input type="hidden" id="paid_amount" value="<?php echo lang('paid_amount'); ?>">
<input type="hidden" id="customer_due_receive" value="<?php echo lang('customer_due_receive'); ?>">
<input type="hidden" id="more_then_original_amount" value="<?php echo lang('more_then_original_amount'); ?>">
<input type="hidden" id="this_item_not_added_on_your_selected_customer" value="<?php echo lang('this_item_not_added_on_your_selected_customer'); ?>">
<input type="hidden" id="in_" value="<?php echo lang('in'); ?>">
<input type="hidden" id="cash" value="<?php echo lang('cash'); ?>">
<input type="hidden" id="paypal" value="<?php echo lang('paypal'); ?>">
<input type="hidden" id="sale" value="<?php echo lang('sale'); ?>">
<input type="hidden" id="card" value="<?php echo lang('card'); ?>">
<input type="hidden" id="edit_profile" value="<?php echo lang('edit_profile'); ?>">
<input type="hidden" id="indexdb_err" value="<?php echo lang('indexdb_err'); ?>">
<input type="hidden" id="invoiced_error" value="<?php echo lang('invoiced_error'); ?>">
<input type="hidden" id="order_close_error" value="<?php echo lang('order_close_error'); ?>">
<input type="hidden" id="please_select_a_box_on_right_side_for_assign_item" value="<?php echo lang('please_select_a_box_on_right_side_for_assign_item'); ?>">
<input type="hidden" id="selected_variation" value="<?php echo lang('selected_variation'); ?>">
<input type="hidden" id="please_click_a_payment_method_before_add" value="<?php echo lang('please_click_a_payment_method_before_add'); ?>">
<input type="hidden" id="pleaseselectordertypebeforeaddtocart" value="<?php echo lang('pleaseselectordertypebeforeaddtocart'); ?>">
<input type="hidden" id="add_to_cart_txt" value="<?php echo lang('add_to_cart'); ?>">
<input type="hidden" id="add_to_cart_pos" value="<?php echo lang('add_to_cart_pos'); ?>">
<input type="hidden" id="please_add_your_table_person_number" value="<?php echo lang('please_add_your_table_person_number'); ?>">
<input type="hidden" id="you_need_to_add_address_with_your_selected_customer" value="<?php echo lang('you_need_to_add_address_with_your_selected_customer'); ?>">
<input type="hidden" id="menu_not_permit_access" value="<?php echo lang('menu_not_permit_access'); ?>">
<div class="modalOverlay"></div>
<input type="hidden" id="base_url_customer" value="<?php echo base_url()?>">
<input type="hidden" id="csrf_name_" value="<?php echo escape_output($this->security->get_csrf_token_name()); ?>">
<input type="hidden" id="csrf_value_" value="<?php echo escape_output($this->security->get_csrf_hash()); ?>">
<input type="hidden" name="print_status" id="" value="">
<input type="hidden"  id="status_for_self_order" value="">
<input type="hidden" name="last_invoice_id" class="last_invoice_id" id="last_invoice_id"
       value="<?php echo escape_output(getLastSaleId()) ?>">
<input type="hidden" name="last_sale_id" class="last_sale_id" id="last_sale_id" value="">
<input type="hidden" name="last_future_sale_id" class="last_future_sale_id" id="last_future_sale_id" value="">
<input type="hidden" id="temp_sale_no" value="">
<input type="hidden" id="current_sale_no" value="">
<input type="hidden" name="print_type" class="print_type" id="print_type" value="">
<?php
    $print_type_invoice = escape_output($this->session->userdata('printing_choice'));
    $print_type_bill = escape_output($this->session->userdata('printing_choice_bill'));
?>
<input type="hidden" name="print_type_invoice" class="print_type_invoice" id="print_type_invoice" value="<?php echo $print_type_invoice?$print_type_invoice:"web_browser_popup"; ?>">
<input type="hidden" name="print_type_bill" class="print_type_bill" id="print_type_bill" value="<?php echo $print_type_bill?$print_type_bill:"web_browser_popup"; ?>">
<input type="hidden" name="print_format" class="print_format" id="print_format" value="<?php echo escape_output($this->session->userdata('print_format')); ?>">
<input type="hidden" name="service_type" class="service_type" id="service_type" value="<?php echo isset($getCompanyInfo->service_type) && $getCompanyInfo->service_type?$getCompanyInfo->service_type:'delivery'; ?>">
<input type="hidden" name="service_amount" class="service_amount" id="service_amount" value="<?php echo isset($getCompanyInfo->service_amount) && $getCompanyInfo->service_amount?$getCompanyInfo->service_amount:'0'; ?>">
<input type="hidden" name="delivery_amount" class="delivery_amount" id="delivery_amount" value="<?php echo isset($getCompanyInfo->delivery_amount) && $getCompanyInfo->delivery_amount?$getCompanyInfo->delivery_amount:'0'; ?>">
<input type="hidden" name="sale_id_for_print" class="sale_id_for_print" id="sale_id_for_print" value="">

<input type="hidden" id="outlet_name" value="<?php echo escape_output($this->session->userdata('outlet_name')); ?>">
<input type="hidden" id="txt_kot" value="<?php echo lang('KOT'); ?>">
<input type="hidden" id="outlet_address" value="<?php echo escape_output($this->session->userdata('address')); ?>">
<input type="hidden" id="outlet_phone" value="<?php echo escape_output($this->session->userdata('phone')); ?>">
<input type="hidden" id="invoice_footer" value="<?php echo escape_output($this->session->userdata('invoice_footer')); ?>">
<input type="hidden" id="user_name" value="<?php echo escape_output($this->session->userdata('full_name')); ?>">
<input type="hidden" id="user_id" value="<?php echo escape_output($this->session->userdata('user_id')); ?>">
<input type="hidden" id="outlet_id_indexdb" value="<?php echo escape_output($this->session->userdata('outlet_id')); ?>">
<input type="hidden" id="company_id_indexdb" value="<?php echo escape_output($this->session->userdata('company_id')); ?>">
<input type="hidden" id="sale_no_new_hidden" value="">
<input type="hidden" id="random_code_hidden" value="">
<input type="hidden" id="update_sale_id" value="">
<div class="total_split_sale ir_display_none"></div>
<input type="hidden" id="outlet_tax_registration_no" value="<?php echo escape_output($this->session->userdata('tax_registration_no')); ?>">
<input type="hidden" id="token_no" value="">
<input type="hidden" id="associate_user_name" value="<?php echo escape_output($this->session->userdata('full_name')); ?>">
<input type="hidden" id="self_order_table_id" value="<?php echo escape_output($this->session->userdata('self_order_table_id')); ?>">
<input type="hidden" id="is_online_order" value="<?php echo escape_output($this->session->userdata('is_online_order')); ?>">
<input type="hidden" id="online_customer_id" value="<?php echo escape_output($this->session->userdata('online_customer_id')); ?>">
<input type="hidden" id="online_customer_name" value="<?php echo escape_output($this->session->userdata('online_customer_name')); ?>">
<input type="hidden" id="orders_table_text_hide" value="<?php echo escape_output(getTableName($this->session->userdata('self_order_table_id'))); ?>">
<input type="hidden" id="default_date" value="<?php echo date("Y-m-d"); ?>">
<input type="hidden" id="delivery_partner" value="<?php echo sizeof($deliveryPartners); ?>">
<?php
$sms_send_auto = $this->session->userdata('sms_send_auto');
$self_order_table_name =  $this->session->userdata('self_order_table_name');
$self_order_table_id = $this->session->userdata('self_order_table_id');  
?>
<input type="hidden" id="sms_send_auto_checker" value="<?php echo isset($sms_send_auto) && $sms_send_auto==2?1:0?>">
<input type="hidden" id="hidden_given_amount" value="0">
<input type="hidden" id="hidden_change_amount" value="0">

<input type="hidden" id="table_id" value="<?php echo escape_output($self_order_table_id)?>">
<input type="hidden" id="hidden_table_capacity" value="1">
<input type="hidden" id="hidden_table_name" value="<?php echo escape_output($self_order_table_name)?>">
<input type="hidden" id="ordered_border_color_hidden" value="">
<input type="hidden" id="ordered_bg_color_hidden" value="">
<input type="hidden" id="ordered_text_color_hidden" value="">

<!--pos screen access list-->
<input type="hidden" id="pos_1" value="<?php echo getPOSChecker("73","pos_1"); ?>">
<input type="hidden" id="pos_2" value="<?php echo getPOSChecker("73","pos_2"); ?>">
<input type="hidden" id="pos_3" value="<?php echo getPOSChecker("73","pos_3"); ?>">
<input type="hidden" id="pos_4" value="<?php echo getPOSChecker("73","pos_4"); ?>">
<input type="hidden" id="pos_5" value="<?php echo getPOSChecker("73","pos_5"); ?>">
<input type="hidden" id="pos_6" value="<?php echo getPOSChecker("73","pos_6"); ?>">
<input type="hidden" id="pos_7" value="<?php echo getPOSChecker("73","pos_7"); ?>">
<input type="hidden" id="pos_8" value="<?php echo getPOSChecker("73","pos_8"); ?>">
<input type="hidden" id="pos_9" value="<?php echo getPOSChecker("73","pos_9"); ?>">
<input type="hidden" id="pos_10" value="<?php echo getPOSChecker("73","pos_10"); ?>">
<input type="hidden" id="pos_11" value="<?php echo getPOSChecker("73","pos_11"); ?>">
<input type="hidden" id="pos_12" value="<?php echo getPOSChecker("73","pos_12"); ?>">
<input type="hidden" id="pos_13" value="<?php echo getPOSChecker("73","pos_13"); ?>">
<input type="hidden" id="pos_14" value="<?php echo getPOSChecker("73","pos_14"); ?>">
<input type="hidden" id="pos_15" value="<?php echo getPOSChecker("73","pos_15"); ?>">
<input type="hidden" id="pos_16" value="<?php echo getPOSChecker("73","pos_16"); ?>">
<input type="hidden" id="pos_17" value="<?php echo getPOSChecker("73","pos_17"); ?>">
<input type="hidden" id="pos_18" value="<?php echo getPOSChecker("73","pos_18"); ?>">
<input type="hidden" id="pos_19" value="<?php echo getPOSChecker("73","pos_19"); ?>">
<input type="hidden" id="pos_20" value="<?php echo getPOSChecker("73","pos_20"); ?>">
<input type="hidden" id="pos_21" value="<?php echo getPOSChecker("73","pos_21"); ?>">
<input type="hidden" id="pos_22" value="<?php echo getPOSChecker("73","pos_22"); ?>">
<input type="hidden" id="pos_23" value="<?php echo getPOSChecker("73","pos_23"); ?>">
<input type="hidden" id="pos_24" value="<?php echo getPOSChecker("73","pos_24"); ?>">
<input type="hidden" id="pos_25" value="<?php echo getPOSChecker("73","pos_25"); ?>">
<input type="hidden" id="alert_running_order" value="<?php echo lang('alert_running_order'); ?>">
<input type="hidden" id="alert_running_order1" value="<?php echo lang('alert_running_order1'); ?>">
<input type="hidden" id="customer_address_msg" value="<?php echo lang('customer_address_msg'); ?>">
<input type="hidden" id="is_direct_sale_check" value="2">
<input type="hidden" id="kot_print" value="">
<input type="hidden" id="counter_id" value="<?php echo escape_output($this->session->userdata('counter_id')); ?>">
<input type="hidden" id="counter_name" value="<?php echo escape_output($this->session->userdata('counter_name')); ?>">
<input type="hidden" id="inv_qr_code_enable_status" value="<?php echo escape_output($this->session->userdata('inv_qr_code_enable_status')); ?>">
<input type="hidden" id="apply_on_delivery_charge" value="<?php echo escape_output($this->session->userdata('apply_on_delivery_charge')); ?>">
<input type="hidden" id="is_click_transfer_table" value="">
<input type="hidden" id="active_transfer_table" value="">
<input type="hidden" id="active_transfer_sale_id" value="">
<input type="hidden" id="is_first" value="1">
<input type="hidden" id="alert_running_order" value="<?php echo lang('alert_running_order'); ?>">
<input type="hidden" id="alert_running_order1" value="<?php echo lang('alert_running_order1'); ?>">
<input type="hidden" id="customer_address_msg" value="<?php echo lang('customer_address_msg'); ?>">
<input type="hidden" id="please_select_a_table_for_action" value="<?php echo lang('please_select_a_table_for_action'); ?>">
<input type="hidden" id="you_are_ordering_now_on_your_selected_table" value="<?php echo lang('you_are_ordering_now_on_your_selected_table'); ?>">
<input type="hidden" id="not_booked_yet" value="<?php echo lang('not_booked_yet'); ?>">
<input type="hidden" id="transfer_transferred_msg" value="<?php echo lang('transfer_transferred_msg'); ?>">
<?php if($is_self_order!="Yes"){?>
    <input type="hidden" id="edit_sale_id" value="<?php echo $sale_details->id??''?>">
    <input type="hidden" id="edit_sale_no" value="<?php echo $sale_details->sale_no??''?>">
    <input type="hidden" id="edit_sale_date" value="<?php echo $sale_details->sale_date??''?>">
    <input type="hidden" id="edit_date_time" value="<?php echo $sale_details->date_time??''?>">
    <div style="display:none" class="edit_content_object"><?php echo $sale_details->self_order_content??''?></div>
    <?php } ?>
<input type="hidden" id="no_item_error" value="<?php echo lang('no_item_error'); ?>">
<input type="hidden" id="please_select_your_kitchen_for_print" value="<?php echo lang('please_select_your_kitchen_for_print'); ?>">
<input type="hidden" id="inv_paid_ticket" value="<?php echo lang('paid_ticket'); ?>">
<input type="hidden" id="write_your_reason" value="<?php echo lang('write_your_reason'); ?>">
<input type="hidden" id="write_your_reason_remove" value="<?php echo lang('write_your_reason_remove'); ?>">
<input type="hidden" id="lang_order" value="<?php echo lang('order'); ?>">
<input type="hidden" id="lang_order_type" value="<?php echo lang('order_type'); ?>">
<input type="hidden" id="lang_table" value="<?php echo lang('table'); ?>">
<input type="hidden" id="lang_waiter" value="<?php echo lang('waiter'); ?>">
<input type="hidden" id="lang_customer" value="<?php echo lang('customer'); ?>">
<input type="hidden" id="not_merge_yet" value="<?php echo lang('not_merge_yet'); ?>">
<input type="hidden" id="order_success_updated" value="<?php echo lang('order_success_updated'); ?>">
<input type="hidden" id="order_success_added" value="<?php echo lang('order_success_added'); ?>">
<input type="hidden" id="is_click_merge_table" value="">
<input type="hidden" id="already_merge" value="<?php echo lang('already_merge'); ?>">
<input type="hidden" id="at_least_select_two_table" value="<?php echo lang('at_least_select_two_table'); ?>">
<input type="hidden" id="are_you_sure_for_pull" value="<?php echo lang('are_you_sure_for_pull'); ?>">
<input type="hidden" id="pulled_successfully" value="<?php echo lang('pulled_successfully'); ?>">
<input type="hidden" id="this_order_engage" value="<?php echo lang('this_order_engage'); ?>">
<input type="hidden" id="zatca_invoice_value" value="">

<script>
// Función que formatea un número como cadena de moneda
function formatNumberToCurrency(number) {
    // console.log(number);
  // Obtenemos los valores de los inputs ocultos
  const decimalsSeparator = document.getElementById('decimals_separator').value;
  const thousandsSeparator = document.getElementById('thousands_separator').value;
  const precision = parseInt(document.getElementById('ir_precision').value, 10);

  number = Number(number);
  // Convertimos el número a string con la precisión deseada
  const fixedNumber = number.toFixed(precision);

  // Separamos la parte entera y la parte decimal
  let [integerPart, fractionPart] = fixedNumber.split('.');

  // Aplicamos el separador de miles a la parte entera
  integerPart = integerPart.split("").reverse().join("")
    .replace(/(\d{3})(?=\d)/g, "$1" + thousandsSeparator)
    .split("").reverse().join("");

  // Reconstruimos la cadena final usando el separador de decimales
  const formatted = fractionPart ? integerPart + decimalsSeparator + fractionPart : integerPart;
  return formatted;
}

// Función que convierte una cadena de moneda en un número
function parseCurrencyToNumber(formattedString) {
  const decimalsSeparator = document.getElementById('decimals_separator').value;
  const thousandsSeparator = document.getElementById('thousands_separator').value;

  // Eliminamos el separador de miles
  let cleanedString = formattedString.split(thousandsSeparator).join('');
  // Reemplazamos el separador de decimales por el punto para que parseFloat lo interprete correctamente
  if (decimalsSeparator !== '.') {
    cleanedString = cleanedString.replace(decimalsSeparator, '.');
  }

  // Convertimos la cadena a número
  const number = parseFloat(cleanedString);
  return number;
}

function clearButtonNumber(sale_no = null){
    const comanda_required = document.getElementById('comanda_required').value;
    if (sale_no != null){
        $(".number_buttons").each(function () {
            let numId = $(this).attr("data-sale_no");
            if (numId == sale_no) {
                $(this).removeClass('btn-danger').addClass('btn-success')
                    .attr({ 'data-sale_id': '', 'data-sale_no': '', 'data-user_id': '' });
                    // .css('transform', 'scale(1.2)');
                setTimeout(() => $(this).css('transform', 'scale(1)'), 300);
            }
        });
    }
    if (comanda_required == 2){
        $("#numbers_button").click();
    }
}
function printKitchenTickets(sale_no, print_all = true) {
    let base_url = $("base").attr("data-base");
    let url = base_url + "Sale/printer_app_kot/" + sale_no + "?print_all=" + print_all;
    $.ajax({
        url: url,
        method: "GET",
        dataType: 'json',
        success: function(printersArray) {
            if (printersArray && printersArray.length > 0) {
                // Función para imprimir secuencialmente
                function printSequentially(index) {
                    if (index < printersArray.length) {
                        console.log('Imprimiendo ticket de cocina ' + (index + 1));
                        window.location.href = 'print://' + printersArray[index];
                        
                        // Esperar un breve momento antes de imprimir el siguiente
                        setTimeout(function() {
                            printSequentially(index + 1);
                        }, 500); // 500ms de espera entre impresiones
                    }
                }
                
                // Iniciar el proceso de impresión secuencial
                printSequentially(0);
            } else {
                console.warn('No se recibieron tickets de cocina para imprimir');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al generar tickets de cocina:', error);
            alert("Error al generar los tickets para la cocina.");
        }
    });
}
</script>

<script>

document.addEventListener('DOMContentLoaded', function() {
  
  setTimeout(function () {
      fetch('<?php echo site_url("sale/get_food_menus_ajax")?>')
        .then(response => response.json())
        .then(function(data) {
          // Helpers simulados (adapta según tu lógica real)
          function getPlanText(text) { return text || ""; }
          function getAmtP(amount) { return amount || "0.00"; }
          function getParentNameTemp(parent_id) { return ""; /* adapta si lo necesitas */ }
          function getDetailsCombo(id) { return ""; /* adapta si lo necesitas */ }
          function checkPromotionWithinDatePOS(today, menu_id) { return null; /* adapta si lo necesitas */ }
          function getSalePriceDetails(raw) { return raw || ""; }
          function getFoodMenuNameCodeById(id) { return ""; /* adapta si lo necesitas */ }

          // Si necesitas datos adicionales (outlet_information, delivery_price_outlet, language_manifesto, etc.),
          // debes traerlos también en el endpoint o definir valores por defecto aquí.

          const food_menus = data.food_menus || [];
          const menu_modifiers = data.menu_modifiers || [];
          const only_modifiers = data.only_modifiers || []; // Si no viene, puedes armarlo igual que antes

          // Ordena por categoría igual que usort($food_menus, "cmp");
          food_menus.sort((a, b) => (a.category_id+"").localeCompare(b.category_id+""));

          window.items = [];
          let i = 1;
          let total_menus = food_menus.length;

          for (const single_menus of food_menus) {
            let sale_price_take = single_menus.sale_price;
            let sale_price_delivery = single_menus.sale_price;
            let sale_price = 0;

            // new_added_zak
            let sale_price_delivery_details = getSalePriceDetails(single_menus.delivery_price);

            // TODO: lógica de precios previos y delivery_price_outlet si lo necesitas, deberías traerlos del endpoint

            // TODO: lógica de language_manifesto, previous_price, sale_price_tmp (si lo necesitas, trae estas variables)
            // De momento solo uso los precios por defecto:
            sale_price = single_menus.sale_price;
            if(single_menus.sale_price_take_away && single_menus.sale_price_take_away !== '0.00'){
              sale_price_take = single_menus.sale_price_take_away;
            } else {
              sale_price_take = single_menus.sale_price;
            }
            if(single_menus.sale_price_delivery && single_menus.sale_price_delivery !== '0.00'){
              sale_price_delivery = single_menus.sale_price_delivery;
            } else {
              sale_price_delivery = single_menus.sale_price;
            }

            let is_variation = single_menus.is_variation;
            let veg_status1 = single_menus.veg_item == "Veg Yes" ? "yes" : "no";
            let beverage_status = single_menus.beverage_item == "Bev Yes" ? "yes" : "no";
            let is_promo = '';
            let modifiers = [];

            // Construir modifiers para cada menú
            for (const single_menu_modifier of menu_modifiers) {
              if (single_menu_modifier.food_menu_id == single_menus.id) {
                modifiers.push({
                  menu_modifier_id: single_menu_modifier.modifier_id,
                  modifier_row_id: single_menu_modifier.id,
                  menu_modifier_name: getPlanText(single_menu_modifier.name),
                  tax_information: single_menu_modifier.tax_information,
                  menu_modifier_price: getAmtP(single_menu_modifier.price)
                });
              }
            }
            if (modifiers.length > 0) is_promo = "Yes";

            // item_name_tmp
            let item_name_tmp = single_menus.parent_id != '0'
              ? (getParentNameTemp(single_menus.parent_id) + (single_menus.name ? getPlanText(single_menus.name) : ''))
              : getPlanText(single_menus.name);

            // new_added_zak: combos
            let product_comb = '';
            if(single_menus.product_type == 2){
              product_comb = getDetailsCombo(single_menus.id);
            }

            // Promociones (debes adaptar la lógica si tienes promociones reales)
            let today = (new Date()).toISOString().slice(0,10);
            let promo_checker = checkPromotionWithinDatePOS(today, single_menus.id) || {};
            let get_food_menu_id = '';
            let string_text = '';
            let get_qty = 0;
            let qty = 0;
            let discount = '';
            let promo_type = '';
            let modal_item_name_row = '';

            if(promo_checker && promo_checker.status){
              get_food_menu_id = promo_checker.get_food_menu_id;
              string_text = promo_checker.string_text;
              get_qty = promo_checker.get_qty;
              qty = promo_checker.qty;
              discount = promo_checker.discount;
              promo_type = promo_checker.type;
              modal_item_name_row = getParentNameTemp(single_menus.parent_id) + getFoodMenuNameCodeById(get_food_menu_id);
              is_promo = "Yes";
            }

            // Imagen
            let image_path = single_menus.photo
              ? "<?php echo base_url()?>images/" + single_menus.photo
              : "<?php echo base_url()?>images/image_thumb.png";

            // Otros campos
            let veg_status = single_menus.veg_item == 'Veg Yes' ? "VEG" : "";
            let soft_status = single_menus.beverage_item == 'Beverage Yes' ? "BEV" : "";

            // Armado del objeto FINAL, igual que el que hacías en PHP
            window.items.push({
              item_id: single_menus.id,
              kitchen_id: single_menus.kitchen_id,
              kitchen_name: single_menus.kitchen_name,
              is_promo: is_promo,
              qty: qty,
              modal_item_name_row: modal_item_name_row,
              promo_type: promo_type,
              get_food_menu_id: get_food_menu_id,
              string_text: string_text,
              get_qty: get_qty,
              discount: discount,
              parent_id: single_menus.parent_id,
              product_type: single_menus.product_type,
              product_comb: product_comb,
              is_variation: is_variation,
              item_code: getPlanText(single_menus.code),
              category_name: getPlanText(single_menus.category_name),
              item_name: getPlanText(single_menus.name),
              alternative_name: getPlanText(single_menus.alternative_name),
              item_name_tmp: getPlanText(item_name_tmp),
              price: getAmtP(sale_price),
              price_take: getAmtP(sale_price_take),
              price_delivery: getAmtP(sale_price_delivery),
              price_delivery_details: sale_price_delivery_details,
              image: image_path,
              tax_information: single_menus.tax_information,
              vat_percentage: "0",
              veg_item: veg_status,
              beverage_item: soft_status,
              sold_for: single_menus.item_sold,
              veg_item_status: veg_status1,
              beverage_item_status: beverage_status,
              modifiers: modifiers
            });
            i++;
          }

          // Construye window.only_modifiers igual que antes
          window.only_modifiers = [];
          let mods = only_modifiers.length ? only_modifiers : menu_modifiers;
          for (const mod of mods) {
            window.only_modifiers.push({
              menu_modifier_id: mod.id || mod.modifier_id,
              menu_modifier_name: getPlanText(mod.name),
              tax_information: mod.tax_information,
              menu_modifier_price: getAmtP(mod.price)
            });
          }

          // Ya tienes window.items y window.only_modifiers igual que antes de tu PHP.
          // Puedes usar el resto de tu lógica JS como antes.
        });
  }, 10000);

});

function numeroATexto(numero) {
    const unidad = ['', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'];
    const decena = ['', 'diez', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
    const centena = ['', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos'];
    const especial = {10: 'diez', 11: 'once', 12: 'doce', 13: 'trece', 14: 'catorce', 15: 'quince'};

    numero = parseInt(numero, 10);

    if (numero < 0) {
        return 'negativo ' + numeroATexto(Math.abs(numero));
    } else if (numero < 10) {
        return unidad[numero];
    } else if (numero < 20) {
        if (numero <= 15) {
            return especial[numero];
        } else {
            return 'dieci' + unidad[numero % 10];
        }
    } else if (numero < 100) {
        if (numero % 10 === 0) {
            return decena[Math.floor(numero / 10)];
        } else if (numero < 30) {
            return 'veinti' + unidad[numero % 10];
        } else {
            return decena[Math.floor(numero / 10)] + ' y ' + unidad[numero % 10];
        }
    } else if (numero < 1000) {
        if (numero === 100) {
            return 'cien';
        } else {
            return centena[Math.floor(numero / 100)] + ' ' + numeroATexto(numero % 100);
        }
    } else if (numero < 1000000) {
        if (numero < 2000) {
            return 'mil ' + (numero % 1000 !== 0 ? numeroATexto(numero % 1000) : '');
        } else {
            return numeroATexto(Math.floor(numero / 1000)) + ' mil ' + (numero % 1000 > 0 ? numeroATexto(numero % 1000) : '');
        }
    } else if (numero < 1000000000) {
        if (numero < 2000000) {
            return 'un millón ' + (numero % 1000000 > 0 ? numeroATexto(numero % 1000000) : '');
        } else {
            return numeroATexto(Math.floor(numero / 1000000)) + ' millones ' + (numero % 1000000 > 0 ? numeroATexto(numero % 1000000) : '');
        }
    }

    return 'Número fuera de rango';
}

function numeroConDecimalesATexto(numero) {
    let partes = String(numero).split('.');
    let entero = parseInt(partes[0], 10);

    let texto;
    if (entero < 0) {
        texto = 'negativo ' + numeroATexto(Math.abs(entero));
    } else if (entero < 1 && entero >= 0) {
        texto = 'cero';
    } else {
        texto = numeroATexto(entero);
    }

    if (partes.length > 1) {
        let parteDecimal = partes[1].replace(/0+$/, '');
        if (parteDecimal !== '') {
            texto += ' con ' + numeroATexto(parseInt(parteDecimal, 10));
        }
    }

    return texto.trim();
}

async function getUniqueSaleNo(sale_no) {
    let base_url = $("base").attr("data-base");
    let csrf_value_ = $('#csrf-token').val(); // o como corresponda en tu HTML

    return new Promise((resolve, reject) => {
        $.ajax({
            url: base_url + "Sale/get_unique_sale_no",
            method: "POST",
            data: { sale_no: sale_no, csrf_irestoraplus: csrf_value_ },
            dataType: "json",
            success: function(resp) {
                resolve(resp.sale_no);
            },
            error: function() {
                // Fallback: si hay error, devolver el original pero deberías manejar este caso
                resolve(sale_no);
            }
        });
    });
}
function getUniqueSaleNoSync(sale_no) {
    let base_url = $("base").attr("data-base");
    let csrf_value_ = $('#csrf-token').val();
    let final_sale_no = sale_no;

    $.ajax({
        url: base_url + "Sale/get_unique_sale_no",
        method: "POST",
        data: { sale_no: sale_no, csrf_irestoraplus: csrf_value_ },
        dataType: "json",
        async: false, // <-- Esto es lo importante
        success: function(resp) {
            final_sale_no = resp.sale_no;
        },
        error: function() {
            // Si hay error, regresa el original
            final_sale_no = sale_no;
        }
    });
    return final_sale_no;
}

function showLoaderAll($text = ' Cargando...') {
        document.getElementById("fullScreenLoader").style.display = "flex";
        $("#fullScreenLoaderCounter").html($text);
            
    }


function hideLoaderAll() {
    document.getElementById("fullScreenLoader").style.display = "none";
}


</script>