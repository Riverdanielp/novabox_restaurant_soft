<link rel="stylesheet" href="<?php echo base_url(); ?>assets/dist/css/custom/report.css">



<section class="main-content-wrapper">
    <section class="content-header">
        <h3 class="top-left-header text-left"><?php echo lang('detailed_sale_report'); ?></h3>
        <input type="hidden" class="datatable_name" data-title="<?php echo lang('detailed_sale_report'); ?>" data-id_name="datatable">

    </section>

    <div>
        <?php
        if(isLMni() && isset($outlet_id)):
            ?>
            <h4 class="txt-color-grey"> <?php echo lang('outlet'); ?>: <?php echo escape_output(getOutletNameById($outlet_id))?></h4>
            <?php
        endif;
        ?>
        <h4 class="txt-color-grey"><?= isset($start_date) && $start_date && isset($end_date) && $end_date ? lang('date').": " . date($this->session->userdata('date_format'), strtotime($start_date)) . " - " . date($this->session->userdata('date_format'), strtotime($end_date)) : '' ?><?= isset($start_date) && $start_date && !$end_date ? lang('date').": " . date($this->session->userdata('date_format'), strtotime($start_date)) : '' ?><?= isset($end_date) && $end_date && !$start_date ? lang('date').": " . date($this->session->userdata('date_format'), strtotime($end_date)) : '' ?>
        </h4>
        <h4 class="txt-color-grey ir_txtCenter_mt0"><?php
            if (isset($user_id) && $user_id):
                echo lang('user').": " . userName($user_id);
            else:
                echo lang('user').": ".lang('all');
            endif;
            ?></h4>
        <h4 class="txt-color-grey ir_txtCenter_mt0"><?php
            if (isset($waiter_id) && $waiter_id):
                echo lang('waiter').": " . userName($waiter_id);
            else:
                echo lang('waiter').": ".lang('all');
            endif;
            ?></h4>

    </div>


    <div class="box-wrapper">
        <div class="row">
            <div class="col-sm-12 mb-3 col-md-4 col-lg-2">
                <?php echo form_open(base_url() . 'Report/detailedSaleReport', array('method' => 'get')) ?>
                <div class="form-group">
                    <input tabindex="1" type="text" id="" name="startDate" readonly class="form-control customDatepicker"
                           placeholder="<?php echo lang('start_date'); ?>" value="<?php echo isset($start_date) ? $start_date : ''; ?>">
                </div>
            </div>
            <div class="col-sm-12 mb-3 col-md-4 col-lg-2">

                <div class="form-group">
                    <input tabindex="2" type="text" id="endMonth" name="endDate" readonly
                           class="form-control customDatepicker" placeholder="<?php echo lang('end_date'); ?>"
                           value="<?php echo isset($end_date) ? $end_date : ''; ?>">
                </div>
            </div>
            <div class="col-sm-12 mb-3 col-md-4 col-lg-2">
                <div class="form-group">
                    <select tabindex="2" class="form-control select2 ir_w_100" id="user_id" name="user_id">
                        <option value=""><?php echo lang('all'); ?></option>
                        <option value="<?= escape_output($this->session->userdata['user_id']); ?>">
                            <?= escape_output($this->session->userdata['full_name']); ?></option>
                        <?php
                        foreach ($users as $value) {
                            ?>
                            <option value="<?php echo escape_output($value->id) ?>" <?php echo ($user_id == $value->id) ? 'selected' : ''; ?>>
                                <?php echo escape_output($value->full_name) ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="col-sm-12 mb-3 col-md-4 col-lg-2">
                <div class="form-group">
                    <select tabindex="2" class="form-control select2 ir_w_100" id="waiter_id" name="waiter_id">
                        <option value=""><?php echo lang('waiter'); ?></option>
                        <?php
                        foreach ($users as $value) {
                            if($value->designation=="Waiter"):
                                ?>
                                <option value="<?php echo escape_output($value->id) ?>" <?php echo ($waiter_id == $value->id) ? 'selected' : ''; ?>>
                                    <?php echo escape_output($value->full_name) ?></option>
                                <?php
                            endif;
                        } ?>
                    </select>
                </div>
            </div>
            <div class="col-sm-12 mb-3 col-md-4 col-lg-2">
                <div class="form-group">
                    <select id="customer_search" name="customer" class="form-control select2 ir_w_100">
                        <option value="all">Todos</option>
                    </select>
                </div>
            </div>
            <?php
            if(isLMni()):
                ?>
                <div class="col-sm-12 mb-3 col-md-4 col-lg-2">
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
                <?php
            endif;
            ?>
            <div class="col-sm-12 mb-3 col-md-2 pull-right">
                <div class="form-group">
                    <button type="submit" name="submit" value="submit"
                            class="btn bg-blue-btn w-100"><?php echo lang('submit'); ?></button>
                </div>
            </div>
        </div>

        <div class="table-box">

            <div class="table-responsive">

                <table id="datatable" class="table">
                    <thead>
                    <tr>
                        <th class="ir_w2_txt_center"><?php echo lang('sn'); ?></th>
                        <th><?php echo lang('date'); ?></th>
                        <th><?php echo lang('sale_no'); ?></th>
                        <th><?php echo lang('total_items'); ?></th>
                        <th><?php echo lang('subtotal'); ?></th>
                        <th><?php echo lang('delivery'); ?> <?php echo lang('delivery_charge'); ?></th>
                        <th><?php echo lang('service_charge'); ?></th>
                        <th><?php echo lang('discount'); ?></th>
                        <th><?php echo lang('vat'); ?></th>
                        <th><?php echo lang('g_total'); ?></th>
                        <th><?php echo lang('payment_method'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $pGrandTotal = 0;
                    $subGrandTotal = 0;
                    $itemsGrandTotal = 0;
                    $disGrandTotal = 0;
                    $vatGrandTotal = 0;
                    $deliveryTotal = 0;
                    $serviceTotal = 0;
                    $payments_arr = array();
                    if (isset($detailedSaleReport)):
                        foreach ($detailedSaleReport as $key => $value) {
                            $total_qty = getTotalItem($value->id);
                            $pGrandTotal+=$value->total_payable;
                            $subGrandTotal+=$value->sub_total;
                            $itemsGrandTotal+=$total_qty;
                            $disGrandTotal+=$value->total_discount_amount;
                            $vatGrandTotal+=$value->vat;
                            $service_row_total = 0;
                            $delivery_row_total = 0;
                            if($value->charge_type=="service"){
                                $service_row_total = getPercentageValue($value->delivery_charge,$value->sub_total);
                                $serviceTotal+=$service_row_total;

                            }else{
                                $delivery_row_total = getPercentageValue($value->delivery_charge,$value->sub_total);
                                $deliveryTotal+= $delivery_row_total;
                            }
                            $key++;
                            ?>
                            <tr>
                                <td class="ir_txt_center"><?php echo escape_output($key); ?></td>
                                <td><?= escape_output(date($this->session->userdata('date_format'), strtotime($value->sale_date))) ?>
                                </td>
                                <td><?php echo escape_output($value->sale_no) ?></td>
                                <td><?php echo escape_output($total_qty) ?></td>
                                <td><?php echo escape_output(getAmt($value->sub_total)) ?></td>
                                <td><?php echo escape_output(getAmt($delivery_row_total)) ?></td>
                                <td><?php echo escape_output(getAmt($service_row_total)) ?></td>
                                <td><?php echo escape_output(getAmt($value->total_discount_amount)) ?></td>
                                <td><?php echo escape_output(getAmt($value->vat)) ?></td>
                                <td><?php echo escape_output(getAmt($value->total_payable)) ?></td>
                                <td>
                                    <?php
                                    $outlet_id = $this->session->userdata('outlet_id');
                                    $salePaymentDetails = salePaymentDetails($value->id,$outlet_id);
                                    if(isset($salePaymentDetails) && $salePaymentDetails):
                                        ?>
                                        <?php foreach ($salePaymentDetails as $ky=>$payment):
                                        $txt_point = '';
                                        if($payment->id==5){
                                            $txt_point = " (Usage Point:".$payment->usage_point.")";
                                        }
                                        echo escape_output($payment->payment_name.$txt_point).":".escape_output(getAmtPCustom($payment->amount));
                                        if($ky<sizeof($salePaymentDetails)-1){
                                            echo " - ";
                                        }
                                        $previous_amount = isset($payments_arr[$payment->payment_name]) && $payments_arr[$payment->payment_name]?$payments_arr[$payment->payment_name]:0;
                                        $payments_arr[$payment->payment_name] = $previous_amount + $payment->amount;

                                    endforeach;
                                    endif;
                                    ?>
                                </td>
                            </tr>
                            <?php
                        }
                    endif;
                    ?>
                    <tr>
                        <td class="ir_w2_txt_center"></td>
                        <td></td>
                        <td class="ir_txt_right"><b><?php echo lang('total'); ?></b> </td>
                        <td><?= escape_output($itemsGrandTotal) ?></td>
                        <td>
                            <?php echo escape_output(getAmt($subGrandTotal)) ?></td>
                        <td><?php echo escape_output(getAmt($deliveryTotal)) ?></td>
                        <td><?php echo escape_output(getAmt($serviceTotal)) ?></td>
                        <td>
                            <?php echo escape_output(getAmt($disGrandTotal)) ?></td>
                        <td>
                            <?php echo escape_output(getAmt($vatGrandTotal)) ?></td>
                        <td>
                            <?php echo escape_output(getAmt($pGrandTotal)) ?></td>
                        <td>
                        <?php  
                            foreach($payments_arr as $key=>$amount){
                                echo "<b>".escape_output($key).":</b>".escape_output(getAmtPCustom($amount))."<br>";
                            }
                        ?>
                        </td>
                    </tr>
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

<script src="<?php echo base_url(); ?>frequent_changing/js/custom_report.js"></script>


<script>
    
    var base_url = "<?php echo base_url(); ?>";

$(document).ready(function() {
    var selectedCustomer = "<?php echo isset($customer) ? $customer : 'all'; ?>";
    var selectedCustomerText = "Todos"; // Puedes buscar el nombre si no es 'all'
    
    // Si no es 'all', puedes hacer una petición AJAX para obtener el nombre.
    if(selectedCustomer !== "all" && selectedCustomer) {
        $.ajax({
            url: base_url + "Report/search_customers",
            data: { q: selectedCustomer },
            dataType: "json",
            success: function(data) {
                if(data && data.length > 0) {
                    selectedCustomerText = data[0].text;
                }
                setCustomerSelect2();
            }
        });
    } else {
        setCustomerSelect2();
    }

    function setCustomerSelect2() {
        var option = new Option(selectedCustomerText, selectedCustomer, true, true);
        $('#customer_search').append(option).trigger('change');
        
        $('#customer_search').select2({
            placeholder: "Seleccione o Agregue un Cliente",
            allowClear: true,
            minimumInputLength: 1,
            ajax: {
                url: base_url + 'Report/search_customers',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    let results = [{ id: 'all', text: 'Todos' }];
                    results = results.concat(data);
                    return { results: results };
                },
                cache: true
            },
            templateResult: function(data) {
                if (data.id === 'all') {
                    return $('<span style="font-weight:bold;">' + data.text + '</span>');
                }
                return data.text;
            },
            templateSelection: function(data) {
                return data.text || data.id;
            }
        });
    }
});
</script>