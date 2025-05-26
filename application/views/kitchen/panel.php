
<input type="hidden" id="csrf_name_" value="<?php echo escape_output($this->security->get_csrf_token_name()); ?>">
<input type="hidden" id="csrf_value_" value="<?php echo escape_output($this->security->get_csrf_hash()); ?>">
<?php
    $notification_number = 0;
    if(count($notifications)>0){
        $notification_number = count($notifications);
    }
    $notification_list_show = '';
    foreach ($notifications as $single_notification){
        $notification_list_show .= '<div class="single_row_notification fix" id="single_notification_row_'.$single_notification->id.'">';
        $notification_list_show .= '<div class="fix single_notification_check_box">';
        $notification_list_show .= '<input class="single_notification_checkbox" type="checkbox" id="single_notification_'.$single_notification->id.'" value="'.$single_notification->id.'">';
        $notification_list_show .= '</div>';
        $notification_list_show .= '<div class="fix single_notification">'.$single_notification->notification.'</div>';
        $notification_list_show .= '<div class="single_serve_button">';
        $notification_list_show .= '<button class="btn bg-blue-btn single_serve_b" id="notification_serve_button_'.$single_notification->id.'">Delete</button>';
        $notification_list_show .= '</div>';
        $notification_list_show .= '</div>';
    }

    $base_color = '#8d5df3'; //old
    $base_color2 = '#8b5cf61a';
    // $base_color = '#FF1010'; //new
    // $base_color2 = '#FF10101A';
    $dashboard_chart_color = '#7367f045';
?>
<?php
    $wl = getWhiteLabel();
    $favicon = '';
    if($wl){
        if($wl->site_name){
            $site_name = $wl->site_name;
        }
        if($wl->footer){
            $footer = $wl->footer;
        }
        if($wl->system_logo){
            $system_logo = base_url()."images/".$wl->system_logo;
        }
        if($wl->favicon){
            $favicon = base_url()."images/".$wl->favicon;
        }else{
            $favicon = base_url()."images/favicon.ico";
        }
    }
    $mode = APPLICATION_lcl; 
    ?>
<!DOCTYPE html>
<html class="gr__localhost">

<head>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title><?php echo escape_output($site_name); ?></title>
    <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>frequent_changing/bar_panel/css/style.css<?php echo VERS() ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>frequent_changing/bar_panel/css/sweetalert2.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/bower_components/font-awesome/v5/all.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/bower_components/select2/dist/css/select2.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>asset/plugins/iCheck/minimal/color-scheme.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/dist/css/common.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/dist/css/custom/login.css">
    <script src="<?php echo base_url()?>frequent_changing/bar_panel/js/jquery-3.3.1.min.js"></script>
    <script src="<?php echo base_url()?>frequent_changing/js/jquery-ui.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>frequent_changing/bar_panel/js/jquery.slimscroll.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>frequent_changing/bar_panel/js/sweetalert2.all.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/bower_components/select2/dist/js/select2.full.min.js"></script>
    <base data-base="<?php echo base_url(); ?>"></base>
    <base data-collect-vat="<?php echo escape_output($this->session->userdata('collect_vat')); ?>"></base>
    <base data-currency=""></base>
    <base data-role="<?php echo escape_output($this->session->userdata('role')); ?>"></base>
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo escape_output($favicon) ?>" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css-framework/bootstrap-new/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>frequent_changing/newDesign/style.css<?php echo VERS() ?>">
    <link rel="stylesheet" href="<?php echo base_url(); ?>frequent_changing/kitchen_panel/css/custom_kitchen_panel.css<?php echo VERS() ?>">
    <style>
        <?php if ($this->session->has_userdata('language')) {
                $font_detect=$this->session->userdata('language');
        }?>
        <?php if($font_detect=="arabic"):?>
            @font-face {
                font-family: arabic_font;
                src: url(<?php echo base_url()?>/assets/Cairo-VariableFont_wght.ttf);
            }
            .arabic_font {
                font-family: arabic_font !important
            }
            h1,
            h2,
            h3,
            h4,
            h5,
            span,
            p,
            div {
                font-family: arabic_font !important
            }
        <?php endif;?>
        #main_kitchen_header{
            background-color: <?php echo $base_color;?>;
        }
        .single_row_notification .bg-blue-btn, #notification_list_modal .modal-footer .bg-blue-btn, #help_modal .modal-footer .bg-blue-btn{
            background-color: <?php echo $base_color?>;
        }
        .print_kitchen_ticket {
            background-color:rgb(0, 77, 144);
            color: #fff;
            margin-left: 5px;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>

<body class="arabic_font">
    <input type="hidden" id="csrf_name_" value="<?php echo escape_output($this->security->get_csrf_token_name()); ?>">
    <input type="hidden" id="csrf_value_" value="<?php echo escape_output($this->security->get_csrf_hash()); ?>">
    <input type="hidden" id="kitchen_id" value="<?php echo escape_output($kitchen_id); ?>">
    <input type="hidden" id="note_text" value="<?php echo lang("note") ?>">
    <input type="hidden" id="sale_no" value="<?php echo lang("sale_no") ?>">
    <input type="hidden" id="table" value="<?php echo lang("table") ?>">
    <input type="hidden" id="order_type" value="<?php echo lang("order_type") ?>">
    <input type="hidden" id="inv_dine" value="<?php echo lang('dine') ?>">
    <input type="hidden" id="inv_take_away" value="<?php echo lang('take_away') ?>">
    <input type="hidden" id="inv_delivery" value="<?php echo lang('delivery') ?>">
    <input type="hidden" id="text_not_ready" value="<?php echo lang('text_not_ready') ?>">
    <input type="hidden" id="text_ready" value="<?php echo lang('text_ready') ?>">
    <input type="hidden" id="text_in_preparation" value="<?php echo lang('text_in_preparation') ?>">
    <input type="hidden" id="text_done" value="<?php echo lang('text_done') ?>">
    <input type="hidden" id="quantity_ln" value="<?php echo lang('quantity') ?>">
    <input type="hidden" id="note_ln" value="<?php echo lang('note') ?>">
    <input type="hidden" id="modifiers_ln" value="<?php echo lang('modifiers') ?>">
    <input type="hidden" id="Qty_Old" value="<?php echo lang('Qty_Old') ?>">
    <input type="hidden" id="Qty_New" value="<?php echo lang('Qty_New') ?>">
    <input type="hidden" id="dine_ln" value="<?php echo lang('dine') ?>">
    <input type="hidden" id="take_away_ln" value="<?php echo lang('take_away') ?>">
    <input type="hidden" id="delivery_ln" value="<?php echo lang('delivery') ?>">
    <input type="hidden" id="customer_name_ln" value="<?php echo lang('customer_name') ?>">
    <input type="hidden" id="text_not_ready_ln" value="<?php echo lang('text_not_ready') ?>">
    <input type="hidden" id="text_ready_ln" value="<?php echo lang('text_ready') ?>">
    <input type="hidden" id="text_in_preparation_ln" value="<?php echo lang('text_in_preparation') ?>">
    <input type="hidden" id="fullscreen_1" value="<?php echo lang('fullscreen_1'); ?>">
    <input type="hidden" id="fullscreen_2" value="<?php echo lang('fullscreen_2'); ?>">

    <input type="hidden" id="printer-id" value="<?php echo isset($printer->id) ? $printer->id : ''; ?>">
    <input type="hidden" id="printer-path" value="<?php echo isset($printer->path) ? $printer->path : ''; ?>">
    <input type="hidden" id="printer-title" value="<?php echo isset($printer->title) ? $printer->title : ''; ?>">
    <input type="hidden" id="printer-type" value="<?php echo isset($printer->type) ? $printer->type : ''; ?>">
    <input type="hidden" id="printer-profile_" value="<?php echo isset($printer->profile_) ? $printer->profile_ : ''; ?>">
    <input type="hidden" id="printer-characters_per_line" value="<?php echo isset($printer->characters_per_line) ? $printer->characters_per_line : ''; ?>">
    <input type="hidden" id="printer-printer_ip_address" value="<?php echo isset($printer->printer_ip_address) ? $printer->printer_ip_address : ''; ?>">
    <input type="hidden" id="printer-printer_port" value="<?php echo isset($printer->printer_port) ? $printer->printer_port : ''; ?>">
    <input type="hidden" id="printer-company_id" value="<?php echo isset($printer->company_id) ? $printer->company_id : ''; ?>">
    <input type="hidden" id="printer-outlet_id" value="<?php echo isset($printer->outlet_id) ? $printer->outlet_id : ''; ?>">
    <input type="hidden" id="printer-printing_choice" value="<?php echo isset($printer->printing_choice) ? $printer->printing_choice : ''; ?>">
    <input type="hidden" id="printer-ipvfour_address" value="<?php echo isset($printer->ipvfour_address) ? $printer->ipvfour_address : ''; ?>">
    <input type="hidden" id="printer-print_format" value="<?php echo isset($printer->print_format) ? $printer->print_format : ''; ?>">
    <input type="hidden" id="printer-printer_ip_address" value="<?php echo isset($printer->printer_ip_address) ? $printer->printer_ip_address : ''; ?>">
    <input type="hidden" id="printer-inv_qr_code_enable_status" value="<?php echo isset($printer->inv_qr_code_enable_status) ? $printer->inv_qr_code_enable_status : ''; ?>">
    <input type="hidden" id="printer-open_cash_drawer_when_printing_invoice" value="<?php echo isset($printer->open_cash_drawer_when_printing_invoice) ? $printer->open_cash_drawer_when_printing_invoice : ''; ?>">
    <input type="hidden" id="printer-del_status" value="<?php echo isset($printer->del_status) ? $printer->del_status : ''; ?>">


    <span class="ir_display_none" id="selected_order_for_refreshing_help"></span>
    <span class="ir_display_none" id="refresh_it_or_not"><?php echo lang('yes'); ?></span>
    <div class="wrapper fix">
        <div class="fix main_top">
            <div class="row" id="main_kitchen_header">
                <div class="top_header col-sm-12 col-md-2">
                    <h1><?php echo escape_output($kitchen->name); ?></h1>
                </div>
                <div class="top_menu col-sm-12 col-md-10 d-flex align-items-center justify-content-end">
                <?php if($mode!='lcl'):?>
                    <?php $language=$this->session->userdata('language'); ?>
                    <?php echo form_open(base_url() . 'Authentication/setlanguage', $arrayName = array('id' => 'language')) ?>
                    <select tabindex="2" class="form-control select2 ir_w_100" name="language"
                            onchange='this.form.submit()'>
                            <?php
                        $dir = glob("application/language/*",GLOB_ONLYDIR);
                        $language = $this->session->userdata('language');
                        foreach ($dir as $value):
                            $separete = explode("language/",$value);?>
                            <option value="<?php echo escape_output($separete[1])?>" <?php if(isset($language)){
                            if ($language == $separete[1])
                                echo "selected";
                        }
                        ?>><?php echo ucfirstcustom($separete[1])?></option>
                            <?php
                        endforeach;
                        ?>

                    </select>
                    </form>
                   <?php endif?>
                    <a class="btn bg-blue-btn me-2"href="<?php echo base_url(); ?>Kitchen/kitchens" id="logout_button"><i
                                class="fas me-2 fas-caret-square-left"></i><?php echo lang('back'); ?></a>

                    <div class="top_menu_right" id="group_by_order_item_holder ir_h_float_m"></div>
                    
                    <div class="top_menu_right me-2 btn bg-blue-btn">
                        <p class="m-0">
                            <i class="fas fa-sync-alt ir_mouse_pointer" id="refresh_orders_button"></i>
                        </p>
                    </div>

                    <button id="notification_button" data-bs-toggle="modal" data-bs-target="#notification_list_modal" class="btn me-2 bg-blue-btn">
                        <i class="fa me-2 fa-bell"></i>
                        <?php echo lang('alert'); ?> (<span id="notification_counter"><?php echo escape_output($notification_number); ?></span>)
                    </button>

                    <button type="button" class="btn me-2 bg-blue-btn fullscreen" data-tippy-content="Pantalla completa">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </button>

                    <button id="help_button"  data-bs-toggle="modal" data-bs-target="#help_modal" class="btn me-2 bg-blue-btn">
                        <i class="fa me-2 fa-question-circle"></i>
                        <?php echo lang('help'); ?></button>

                    <a href="<?php echo base_url(); ?>Authentication/logOut" class="btn bg-blue-btn" id="logout_button">
                        <i class="fas me-2 fa-sign-out-alt"></i> <?php echo lang('logout'); ?></a>
                </div>
            </div>
        </div>

        <div class="fix main_bottom">
            <div class="fix order_holder mt-2" id="order_holder">
            </div>
        </div>
    </div>

  

    <div class="modal fade" id="help_modal" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"><?php echo lang('help'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p class="help_content">
                <?php echo lang('kitchen_help_text_first_para'); ?> 
                </p>
                <p class="help_content">
                <?php echo lang('kitchen_help_text_second_para'); ?>
                </p>
                <p class="help_content">
                    <?php echo lang('kitchen_help_text_third_para'); ?>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-blue-btn" data-bs-dismiss="modal"><?php echo lang('close');?></button>
            </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="notification_list_modal" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"><?php echo lang('notification_list'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div id="notification_list_header_holder">
                    <div class="single_row_notification_header fix ir_h_bm">
                        <div class="fix single_notification_check_box">
                            <input type="checkbox" id="select_all_notification">
                        </div>
                        <div class="fix single_notification"><strong><?php echo lang('select_all'); ?></strong></div>
                        <div class="fix single_serve_button">
                        </div>
                    </div>
                </div>

                <div id="notification_list_holder" class="fix">
                    <!--This variable could not be escaped because this is html content-->
                    <?php echo ($notification_list_show);?>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn bg-blue-btn" id="notification_remove_all"><?php echo lang('remove'); ?></button>
                <button class="btn bg-blue-btn" data-bs-dismiss="modal" id="notification_close"><?php echo lang('close'); ?></button>
                
            </div>
            </div>
        </div>
    </div>

    
    <script src="<?php echo base_url(); ?>assets/css-framework/bootstrap-new/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>frequent_changing/kitchen_panel/js/marquee.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>frequent_changing/kitchen_panel/js/datable.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>frequent_changing/kitchen_panel/js/jquery.cookie.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/POS/js/howler.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>frequent_changing/kitchen_panel/js/custom.js<?php echo VERS() ?>"></script>
    <!-- material icon -->
    <!-- Incluye estas librerías en tu HTML -->
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/pako/2.1.0/pako.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/js-base64@3.7.5/base64.min.js"></script> -->

    <script>
        
    
        function printKitchenKOTBySaleId(sale_id, kitchen_id) {
            let base_url = $("base").attr("data-base");
            let url = base_url + "Kitchen/printer_app_kot_by_sale_id/" + sale_id + "/" + kitchen_id;
            $.ajax({
                url: url,
                method: "GET",
                dataType: 'json',
                success: function(printersArray) {
                    if (printersArray && printersArray.length > 0) {
                        // Imprime secuencialmente si hay varias impresoras (por si acaso)
                        function printSequentially(index) {
                            if (index < printersArray.length) {
                                window.location.href = 'print://' + printersArray[index];
                                setTimeout(function() {
                                    printSequentially(index + 1);
                                }, 500);
                            }
                        }
                        printSequentially(0);
                    } else {
                        alert("No hay ticket de cocina para imprimir.");
                    }
                },
                error: function(xhr, status, error) {
                    alert("Error al generar el ticket de cocina: " + error);
                }
            });
        }


        function printKitchenOrderByKitchen(sale_no, kitchen_id) {
            let base_url = $("base").attr("data-base");

            $.ajax({
                url: base_url + "Kitchen/getPrintDataForOrder",
                method: "POST",
                dataType: 'json',
                data: {
                    sale_no: sale_no,
                    kitchen_id: kitchen_id,
                    csrf_irestoraplus: $('#csrf_value_').val()
                },
                success: function(data) {
                    let content_data_direct_print = data.content_data_direct_print;
                    for (let key in content_data_direct_print) {
                        if(content_data_direct_print[key].ipvfour_address) {
                            fetch(content_data_direct_print[key].ipvfour_address + "print_server/novabox_printer_server.php", {
                                method: 'POST',
                                mode: 'no-cors',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: new URLSearchParams({
                                    content_data: "["+(JSON.stringify(content_data_direct_print[key]))+"]",
                                    print_type: data.print_type,
                                })
                            })
                            .then(response => {
                                console.log('Orden de impresión sale_no:' + sale_no);
                            })
                            .catch(error => console.error('Error:', error));
                        }
                    }
                    // Si quieres mostrar popup solo para esa cocina:
                    // print_kot_popup_print(data.content_data_popup_print, 1);
                },
                error: function() {
                    console.log('Error al obtener datos para impresión');
                }
            });
        }


        async function fetchAndPrint(sale_no, kitchen_id, all = 0) {
            let base_url = $("base").attr("data-base");
            // 1. Trae los datos de content_data_direct_print desde PHP vía fetch
            const url = `${base_url}kitchen/get_content_data_direct_print?sale_no=${sale_no}&kitchen_id=${kitchen_id}&all=${all}`;
            const response = await fetch(url);
            if (!response.ok) {
                console.error('No se pudieron obtener los datos de impresión');
                return;
            }
            const content_data_direct_print = await response.json();

            // 2. Por cada printer, ejecuta el fetch de impresión
            content_data_direct_print.forEach(data => {
                fetch(data.ipvfour_address + "print_server/novabox_printer_server.php", {
                    method: 'POST',
                    mode: 'no-cors',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        content_data: "[" + JSON.stringify(data) + "]",
                        print_type: 'KOT',
                    })
                })
                .then(() => {
                    console.log('Orden de impresión enviada sale_no:' + data.sale_no_p);
                })
                .catch(error => console.error('Error al imprimir:', error));
            });
        }

        // function printDirectlyFromOrderData(orderInfo, kitchen_id, all = 0) {
        //     // Obtener configuración de la impresora desde los campos ocultos
        //     const printerConfig = {
        //         printer_port: $('#printer-printer_port').val(),
        //         profile_: $('#printer-profile_').val(),
        //         printer_ip_address: $('#printer-printer_ip_address').val(),
        //         ipvfour_address: $('#printer-ipvfour_address').val(),
        //         printer_name: $('#printer-title').val(),
        //         path: $('#printer-path').val(),
        //         characters_per_line: $('#printer-characters_per_line').val(),
        //         open_cash_drawer_when_printing_invoice: $('#printer-open_cash_drawer_when_printing_invoice').val(),
        //         printer_type: $('#printer-type').val(),
        //         printer_width: '',
        //         type: $('#printer-type').val(),
        //         outlet_id: $('#printer-outlet_id').val()
        //     };

        //     // Preparar los datos para la impresión
        //     const printData = {
        //         ...printerConfig,
        //         store_name: `COCINA:AREA ${kitchen_id} - ${orderInfo.waiter_name || 'SIN MESERO'}`,
        //         sale_type: getOrderTypeText(orderInfo.order_type),
        //         sale_no_p: orderInfo.sale_no,
        //         date: formatDate(orderInfo.sale_date),
        //         time_inv: orderInfo.order_time.split(' ')[1] || orderInfo.order_time,
        //         sales_associate: orderInfo.full_name || 'Administrado',
        //         customer_name: orderInfo.customer_name || 'Cliente Ocacional',
        //         customer_phone: orderInfo.customer_phone || '',
        //         selected_number_name: orderInfo.number_slot_name || '',
        //         selected_number: orderInfo.number_slot || '',
        //         customer_address: orderInfo.del_address || '',
        //         waiter_name: orderInfo.waiter_name || 'POS1',
        //         customer_table: orderInfo.table_name || orderInfo.orders_table_text || '',
        //         lang_order_type: 'Tipo de pedido',
        //         lang_Invoice_No: 'Factura nro',
        //         lang_date: 'Fecha',
        //         lang_Sales_Associate: 'Asociado de ventas',
        //         lang_customer: 'Cliente',
        //         lang_address: 'Dirección',
        //         lang_gst_number: 'Número Doc.',
        //         lang_waiter: 'Mesero',
        //         lang_table: 'Mesa',
        //         print_type: 'KOT',
        //         items: formatItemsForPrint(orderInfo.items, all)
        //     };
        //     // console.log('items',formatItemsForPrint(orderInfo.items, all));

        //     // Enviar directamente a la impresora
        //     return fetch(printerConfig.ipvfour_address + "print_server/novabox_printer_server.php", {
        //         method: 'POST',
        //         mode: 'no-cors',
        //         headers: {
        //             'Content-Type': 'application/x-www-form-urlencoded',
        //         },
        //         body: new URLSearchParams({
        //             content_data: "[" + JSON.stringify(printData) + "]",
        //             print_type: 'KOT',
        //         })
        //     })
        //     .then(() => {
        //         console.log('Orden de impresión enviada sale_no:', orderInfo.sale_no);
        //     })
        //     .catch(error => {
        //         console.error('Error al imprimir:', error);
        //         throw error;
        //     });
        // }

        // Funciones auxiliares (las mismas que antes)
        function getOrderTypeText(orderType) {
            const orderTypes = {
                "1": "Para comer aquí",
                "2": "Para llevar",
                "3": "Delivery"
            };
            return orderTypes[orderType] || "Tipo desconocido";
        }

        function formatDate(dateString) {
            if (!dateString) return '';
            const [year, month, day] = dateString.split('-');
            return `${day}/${month}/${year}`;
        }

        function printDirectlyFromOrderData(orderInfo, kitchen_id, all = 0) {
            // Validar que orderInfo.items existe y no está vacío
            if (!orderInfo.items || !Array.isArray(orderInfo.items)) {
                console.error('No hay items para imprimir o el formato no es válido');
                return Promise.resolve(); // Resuelve sin error para no romper el flujo
            }

            // Formatear items primero para validar si hay contenido
            const formattedItems = formatItemsForPrint(orderInfo.items, all);
            
            // Validar si hay items para imprimir
            if (!formattedItems.trim()) {
                console.log('No hay items nuevos para imprimir, se omite la impresión');
                return Promise.resolve(); // Resuelve sin error para no romper el flujo
            }

            let modificated = '';
            if (all === 0) {
                modificated = ' MODIFICADO';
            }
            // Obtener configuración de la impresora desde los campos ocultos
            const printerConfig = {
                printer_port: $('#printer-printer_port').val(),
                profile_: $('#printer-profile_').val(),
                printer_ip_address: $('#printer-printer_ip_address').val(),
                ipvfour_address: $('#printer-ipvfour_address').val(),
                printer_name: $('#printer-title').val(),
                path: $('#printer-path').val(),
                characters_per_line: $('#printer-characters_per_line').val(),
                open_cash_drawer_when_printing_invoice: $('#printer-open_cash_drawer_when_printing_invoice').val(),
                printer_type: $('#printer-type').val(),
                printer_width: '',
                type: $('#printer-type').val(),
                outlet_id: $('#printer-outlet_id').val()
            };

            // Preparar los datos para la impresión
            const printData = {
                ...printerConfig,
                store_name: `COCINA:AREA ${kitchen_id} - ${orderInfo.waiter_name || 'SIN MESERO'}`,
                sale_type: getOrderTypeText(orderInfo.order_type),
                sale_no_p: orderInfo.sale_no + modificated,
                date: formatDate(orderInfo.sale_date),
                time_inv: orderInfo.order_time.split(' ')[1] || orderInfo.order_time,
                sales_associate: orderInfo.full_name || 'Administrado',
                customer_name: orderInfo.customer_name || 'Cliente Ocacional',
                customer_phone: orderInfo.customer_phone || '',
                selected_number_name: orderInfo.number_slot_name || '',
                selected_number: orderInfo.number_slot || '',
                customer_address: orderInfo.del_address || '',
                waiter_name: orderInfo.waiter_name || 'POS1',
                customer_table: orderInfo.table_name || orderInfo.orders_table_text || '',
                lang_order_type: 'Tipo de pedido',
                lang_Invoice_No: 'Factura nro',
                lang_date: 'Fecha',
                lang_Sales_Associate: 'Asociado de ventas',
                lang_customer: 'Cliente',
                lang_address: 'Dirección',
                lang_gst_number: 'Número Doc.',
                lang_waiter: 'Mesero',
                lang_table: 'Mesa',
                print_type: 'KOT',
                items: formattedItems
            };

            // Enviar directamente a la impresora solo si hay contenido
            // console.log('Enviando a impresión:', printData);
            return fetch(printerConfig.ipvfour_address + "print_server/novabox_printer_server.php", {
                method: 'POST',
                mode: 'no-cors',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    content_data: "[" + JSON.stringify(printData) + "]",
                    print_type: 'KOT',
                })
            })
            .then(() => {
                console.log('Orden de impresión enviada sale_no:', orderInfo.sale_no);
            })
            .catch(error => {
                console.error('Error al imprimir:', error);
                throw error;
            });
        }

        // Modificación en formatItemsForPrint para mejor manejo de casos vacíos
        function formatItemsForPrint(items, all) {
            if (!items || !Array.isArray(items)) return '';
            
            const showTmpQty = (all === 0); // Variable de scope para manejar tmp_qty
            
            const filteredItems = items.filter(item => {
                // Calcular la cantidad relevante (tmp_qty o qty)
                const relevantQty = showTmpQty ? parseFloat(item.tmp_qty || 0) : parseFloat(item.qty);

                // Si all=1, incluir solo items con qty >= 1
                if (all === 1) return relevantQty >= 1;

                // Si all=0, incluir solo items nuevos/sin cocción con cantidad relevante >= 1
                return (!item.cooking_status || item.cooking_status === 'New') && relevantQty >= 1;
            });

            if (filteredItems.length === 0) return '';

            return filteredItems
                .map(item => {
                    // Usar tmp_qty si showTmpQty es true y tmp_qty > 0, sino usar qty
                    const quantity = showTmpQty && parseFloat(item.tmp_qty || 0) > 0 
                        ? item.tmp_qty 
                        : item.qty;
                    
                    let itemText = `${quantity} * ${item.menu_name}`;
                    
                    if (item.modifiers?.length > 0) {
                        item.modifiers.forEach(modifier => {
                            itemText += `\n    + ${modifier.name}`;
                        });
                    }
                    
                    if (item.menu_note?.trim()) {
                        itemText += `\n    NOTA: ${item.menu_note.trim()}`;
                    }
                    
                    return itemText;
                })
                .join('\n');
        }

    </script>
</body>

</html>