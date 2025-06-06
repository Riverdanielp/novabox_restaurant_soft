 <link rel="stylesheet" href="<?php echo base_url(); ?>assets/dist/css/custom/report.css">
 <style>
    .swal2-modal .swal2-content #ticket-html-modal {
        background: #fff;
        margin: 0 auto;
        padding: 0;
    }
 </style>
<?php

    $show_register_report = "";
    if(isset($register_info) && count($register_info)>0){
        
        $i = 1;
        $html_p = '';
        foreach($register_info as $single_register_info){
            $payment_methods_sale = json_decode($single_register_info->payment_methods_sale);
            $html_p = '';
            $j=0;
            $total_used_payment = 0;
            if(isset($payment_methods_sale) && $payment_methods_sale){
                foreach ($payment_methods_sale as $key=>$value){
                    $total_used_payment++;
                }
            }
            if(isset($payment_methods_sale) && $payment_methods_sale){
                foreach ($payment_methods_sale as $key=>$value){
                    $html_p .= $key.": ".getAmtPCustom($value);
                    if($j < ($total_used_payment -1)){
                        $html_p .= ", ";
                    }
                    $j++;
                }
            }
            $html_others = '';
            if(isset($single_register_info->others_currency) && $single_register_info->others_currency){

                $others_details = json_decode($single_register_info->others_currency);
                foreach ($others_details as $key=>$vl){
                    $html_others .= $vl->payment_name.": ".($vl->amount);
                    if($key < (sizeof($others_details) -1)){
                        $html_others .= ", ";
                    }
                }
            }

            $show_register_report .= "<tr>";
            $show_register_report .= '<td>'.$i.'</td>';
            // Botón para imprimir ticket, usando el id
            $show_register_report .= '<td>
                <button class="btn btn-warning btn-sm btn-print-thermal" data-register-id="'.$single_register_info->id.'">
                    <i class="fa fa-print"></i> Ticket
                </button>
            </td>';
            $show_register_report .= '<td>'.$single_register_info->counter_name.'</td>';
            $show_register_report .= '<td>'.$single_register_info->opening_balance_date_time.'</td>';
            $show_register_report .= '<td>'.getAmtPCustom($single_register_info->opening_balance).'</td>';
            $show_register_report .= '<td>'.getAmtPCustom($single_register_info->sale_paid_amount).'</td>';
            $show_register_report .= '<td>'.getAmtP($single_register_info->refund_amount).'</td>';
            $show_register_report .= '<td>'.getAmtPCustom($single_register_info->customer_due_receive).'</td>';
            $show_register_report .= '<td>'.getAmtPCustom($single_register_info->total_purchase).'</td>';
            $show_register_report .= '<td>'.getAmtPCustom($single_register_info->total_expense).'</td>';
            $show_register_report .= '<td>'.getAmtPCustom($single_register_info->total_due_payment).'</td>';
            $show_register_report .= '<td>'.$html_others.'</td>';
            $show_register_report .= '<td>'.$single_register_info->closing_balance_date_time.'</td>';
            $show_register_report .= '<td>'.getAmtPCustom($single_register_info->closing_balance).'</td>';
            $show_register_report .= '<td>'.$html_p.'</td>';
            $show_register_report .= "</tr>";        
            $i++;
        }
    }
    $user_option = '';
    foreach($users as $single_user){
        $user_option .= '<option value="'.$single_user->id.'">'.$single_user->full_name.'</option>';
    }

?>

<section class="main-content-wrapper">


    <section class="content-header px-0">
        <div class="d-flex align-items-center">
            <h3 class="top-left-header text-left">
                <?php echo lang('register_report'); ?>
                <input type="hidden" class="datatable_name" data-id_name="datatable">

            </h3>
            <?php if(isLMni() && isset($outlet_id)):?>
                <p class="mx-2 txt-color-grey my-0"> <?php echo lang('outlet'); ?>: <?php echo escape_output(getOutletNameById($outlet_id))?></p>
            <?php endif;?>
        </div>
        <h4 class="ir_txtCenter_mt0 txt-color-grey"><?php
            if (isset($user_id) && $user_id):
                echo "User: " . userName($user_id) . "</span>";
            endif;
            ?>
        </h4>
        <h4 class="txt-color-grey"><?= isset($start_date) && $start_date && isset($end_date) && $end_date ? lang('date').": " . date($this->session->userdata('date_format'), strtotime($start_date)) . " - " . date($this->session->userdata('date_format'), strtotime($end_date)) : '' ?><?= isset($start_date) && $start_date && !$end_date ? lang('date').": " . date($this->session->userdata('date_format'), strtotime($start_date)) : '' ?><?= isset($end_date) && $end_date && !$start_date ? lang('date').": " . date($this->session->userdata('date_format'), strtotime($end_date)) : '' ?>
        </h4>      
    </section>

    
    <div class="box-wrapper">
    <div class="test-filter-modals mb-2">
        <div class="row">
            <div class="col-sm-12 mb-2 col-md-4 col-lg-2">
                <?php echo form_open(base_url() . 'Report/registerReport') ?>
                <div class="form-group">
                    <input tabindex="1" type="text" id="" name="startDate" readonly class="form-control customDatepicker"
                        placeholder="<?php echo lang('start_date'); ?>" value="<?php echo set_value('startDate'); ?>">
                </div>
            </div>
            <div class="col-sm-12 mb-2 col-md-4 col-lg-2">

                <div class="form-group">
                    <input tabindex="2" type="text" id="endMonth" name="endDate" readonly
                        class="form-control customDatepicker" placeholder="<?php echo lang('end_date'); ?>"
                        value="<?php echo set_value('endDate'); ?>">
                </div>
            </div>
            <div class="col-sm-12 mb-2 col-md-4 col-lg-2">

                <div class="form-group">
                    <select tabindex="2" class="form-control select2 ir_w_100" id="user_id" name="user_id">
                        <option value=""><?php echo lang('all'); ?></option>
                        <?php
                        foreach ($users as $value) {
                            ?>
                        <option <?php echo set_select('user_id',$value->id) ?> value="<?php echo escape_output($value->id) ?>"><?php echo escape_output($value->full_name) ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <?php if(isLMni()): ?>
                <div class="col-sm-12 mb-2 col-md-4 col-lg-2">
                        <div class="form-group">
                            <select tabindex="2" class="form-control select2 ir_w_100" id="outlet_id" name="outlet_id">
                                <?php
                                $outlets = getAllOutlestByAssign();
                                foreach ($outlets as $value):
                                    ?>
                                    <option <?= set_select('outlet_id',$value->id)?>  value="<?php echo escape_output($value->id) ?>"><?php echo escape_output($value->outlet_name) ?></option>
                                    <?php
                                endforeach;
                                ?>
                            </select>
                        </div>
                </div>
            <?php endif; ?>
            <div class="col-sm-12 mb-2 col-md-4 col-lg-2">
                <div class="form-group">
                    <button type="submit" name="submit" value="submit"
                        class="btn bg-blue-btn w-100"><?php echo lang('submit'); ?></button>
                </div>
            </div>
        </div>
    </div>
        <div class="table-box">
                <!-- /.box-header -->
                <div class="table-responsive">

                    <table id="datatable" class="table">
                        <thead>
                            <tr>
                                <th class="title" class="ir_w_5"><?php echo lang('sn'); ?></th>
                                <th class="title" class="ir_w_10"></th>
                                <th class="title" class="ir_w_10"><?php echo lang('counter'); ?></th>
                                <th class="title" class="ir_w_10"><?php echo lang('opening_date_time'); ?></th>
                                <th class="title" class="ir_w_15"><?php echo lang('opening_balance'); ?></th>
                                <th class="title" class="ir_w_15"><?php echo lang('sale'); ?>
                                    (<?php echo lang('paid_amount'); ?>)</th>
                                <th class="title" class="ir_w_15"><?php echo lang('refund_amount'); ?></th>
                                <th class="title" class="ir_w_15"><?php echo lang('customer_due_receive'); ?></th>
                                <th class="title" class="ir_w_15"><?php echo lang('purchase'); ?></th>
                                <th class="title" class="ir_w_15"><?php echo lang('expense'); ?></th>
                                <th class="title" class="ir_w_15"><?php echo lang('due_payment'); ?></th>
                                <th class="title" class="ir_w_15"><?php echo lang('others_currency'); ?></th>
                                <th class="title" class="ir_w_10"><?php echo lang('closing_date_time'); ?></th>
                                <th class="title" class="ir_w_15"><?php echo lang('closing_balance'); ?></th>
                                <th class="title" class="ir_w_15"><?php echo lang('sale_in_payment_method'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            /*This variable could not be escaped because this is base url content*/
                            echo ($show_register_report);
                            ?>
                        </tbody>
                       
                    </table>
                </div>
                <!-- /.box-body -->
        </div>
    </div>


</section>
<!-- DataTables -->
<script src="<?php echo base_url(); ?>assets/datatable_custom/jquery-3.3.1.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/jquery.dataTables.min.js"></script>
<script src="<?php echo base_url(); ?>assets/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js">
</script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/dataTables.bootstrap4.min.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/dataTables.buttons.min.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/buttons.html5.min.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/buttons.print.min.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/jszip.min.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/pdfmake.min.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/vfs_fonts.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/newDesign/js/forTable.js"></script>

<script src="<?php echo base_url(); ?>frequent_changing/js/custom_report_no_sorting.js"></script>


<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-print-thermal').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var register_id = this.getAttribute('data-register-id');
            fetch(base_url + 'Report/registerReportTicketJson/' + register_id)
            .then(res => res.json())
            .then(function(data) {
                if (!data.success) {
                    swal({
                        type: 'error',
                        title: 'Error',
                        text: 'No se pudo obtener el ticket'
                    });
                    return;
                }
                var content = data.content;
                var width = data.width || 80;
                // Genera el HTML del ticket con un botón con un ID único
                var ticketHtml = `
                  <div id="ticket-to-print" style="width:${width}mm; font-family:monospace,'Courier New',Courier; font-size:12px; background:#fff;">
                    <button id="btn-print-ticket-from-modal" style="display:block;margin:10px auto 10px auto;padding:6px 16px;font-size:14px;background:#007bff;color:#fff;border:none;border-radius:4px;cursor:pointer;">
                      <i class='fa fa-print'></i> Imprimir Ticket
                    </button>
                `;
                for (var i=0; i<content.length; i++) {
                    var item = content[i];
                    if (item.type === 'text') {
                        ticketHtml += `<div style="text-align:${item.align};white-space:pre-line;">${item.text || ''}</div>`;
                    }
                    if (item.type === 'extremos') {
                        ticketHtml += `<div style="display:flex;justify-content:space-between;"><span>${item.textLeft}</span><span>${item.textRight}</span></div>`;
                    }
                    if (item.type === 'cut') {
                        ticketHtml += `<div style="border-top:2px dashed #000;margin:9px 0;">&nbsp;</div>`;
                    }
                }
                ticketHtml += `</div>`;

                // Guarda para imprimir
                window.__lastTicketHtmlSwal = ticketHtml.replace(/<button[\s\S]*?<\/button>/, ''); // ticket sin botón
                window.__lastTicketWidthSwal = width;

                swal({
                    title: 'Ticket térmico',
                    html: ticketHtml,
                    showCancelButton: true,
                    confirmButtonText: 'Cerrar'
                });

                // Espera a que el DOM del modal esté listo, luego agrega el event listener
                setTimeout(function(){
                    var printBtn = document.getElementById('btn-print-ticket-from-modal');
                    if (printBtn) {
                        printBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            var html = window.__lastTicketHtmlSwal || '';
                            var width = window.__lastTicketWidthSwal || 80;
                            var printWindow = window.open('', '', 'width='+(width*4)+',height=700');
                            printWindow.document.write(
                                '<html><head><title>Imprimir ticket</title>' +
                                '<style>@media print { body, html { width: '+width+'mm; } } body { width: '+width+'mm; font-family: monospace, "Courier New", Courier; font-size: 12px; }</style>' +
                                '</head><body>' + html + '</body></html>'
                            );
                            printWindow.document.close();
                            setTimeout(function() {
                                printWindow.focus();
                                printWindow.print();
                            }, 400);
                        });
                    }
                }, 300); // SweetAlert2 v7: espera un poco a que el DOM esté listo
            });
        });
    });
});
</script>