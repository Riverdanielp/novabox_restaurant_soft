<style>
@media print {
    .no-print, .no-print * {
        display: none !important;
    }
    
    body {
        margin: 0;
        padding: 8px;
        font-family: Arial, sans-serif;
        font-size: 9px;
        line-height: 1.2;
    }
    
    .print-content {
        width: 100%;
        max-width: none;
        margin: 0;
        padding: 0;
    }
    
    .print-header {
        text-align: center;
        margin-bottom: 8px;
        border-bottom: 1px solid #333;
        padding-bottom: 5px;
    }
    
    .print-header h2 {
        font-size: 14px;
        margin: 0 0 2px 0;
    }
    
    .print-header p {
        font-size: 10px;
        margin: 0;
    }
    
    .print-details {
        margin-bottom: 8px;
    }
    
    .print-details .row {
        display: flex;
        flex-wrap: wrap;
        margin-bottom: 3px;
    }
    
    .print-details .col-md-3 {
        flex: 0 0 25%;
        padding: 2px 5px;
        border-right: 1px dotted #ccc;
    }
    
    .print-details .col-md-3:last-child {
        border-right: none;
    }
    
    .print-details h5 {
        font-size: 8px;
        font-weight: bold;
        margin: 0 0 1px 0;
        text-transform: uppercase;
    }
    
    .print-details p {
        font-size: 9px;
        margin: 0;
    }
    
    .print-table {
        width: 100%;
        border-collapse: collapse;
        margin: 5px 0;
        font-size: 8px;
    }
    
    .print-table th {
        border: 1px solid #333;
        padding: 3px 4px;
        text-align: left;
        background-color: #f5f5f5;
        font-weight: bold;
        font-size: 8px;
    }
    
    .print-table td {
        border: 1px solid #666;
        padding: 2px 4px;
        text-align: left;
        font-size: 8px;
    }
    
    .print-totals {
        margin-top: 8px;
        float: right;
        width: 200px;
        clear: both;
    }
    
    .print-totals .total-row {
        display: flex;
        justify-content: space-between;
        padding: 2px 0;
        border-bottom: 1px dotted #ddd;
        font-size: 9px;
    }
    
    .print-totals .total-row.grand-total {
        font-weight: bold;
        border-bottom: 1px solid #333;
        font-size: 10px;
        margin-top: 2px;
    }
    
    .box-wrapper {
        margin: 0;
        padding: 0;
    }
    
    .table-box {
        margin: 0;
        padding: 0;
        box-shadow: none;
    }
    
    .box-body {
        padding: 0;
    }
    
    /* Hacer que los datos de arriba se muestren en línea */
    .print-info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 5px;
        border-bottom: 1px dotted #ccc;
        padding-bottom: 5px;
    }
    
    .print-info-item {
        flex: 1;
        padding-right: 15px;
    }
    
    .print-info-item:last-child {
        padding-right: 0;
    }
    
    /* Ocultar vista de pantalla en impresión y mostrar vista compacta */
    .screen-only {
        display: none !important;
    }
    
    .print-info-row {
        display: flex !important;
    }
}

.print-btn {
    background-color: #28a745;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    margin-right: 10px;
}

.print-btn:hover {
    background-color: #218838;
}

.print-btn i {
    margin-right: 5px;
}
</style>

<section class="main-content-wrapper">
    <section class="content-header">
        <h3 class="top-left-header">
            <?php echo lang('details_purchase'); ?>
        </h3>
        <div class="no-print">
            <button class="print-btn" onclick="window.print()">
                <i data-feather="printer"></i>
                Imprimir Recibo
            </button>
        </div>
    </section>


    <div class="box-wrapper print-content">
        <div class="table-box">
            <!-- Encabezado para impresión -->
            <div class="print-header" style="display: none;">
                <h2>RECIBO DE COMPRA</h2>
                <p>Detalles de la Compra</p>
            </div>
            
            <div class="box-body print-details">
                <!-- Información principal en una sola fila compacta para impresión -->
                <div class="print-info-row" style="display: none;">
                    <div class="print-info-item">
                        <strong>Ref:</strong> <?php echo escape_output($purchase_details->reference_no) ?>
                    </div>
                    <div class="print-info-item">
                        <strong>Factura:</strong> <?php echo escape_output($purchase_details->factura_nro) ?>
                    </div>
                    <div class="print-info-item">
                        <strong>Proveedor:</strong> <?php echo escape_output(getSupplierNameById($purchase_details->supplier_id)); ?>
                    </div>
                    <div class="print-info-item">
                        <strong>Fecha:</strong> <?php echo escape_output(date($this->session->userdata('date_format'), strtotime($purchase_details->date))); ?>
                    </div>
                </div>
                
                <!-- Vista normal para pantalla -->
                <div class="row screen-only">
                    <div class="col-sm-12 mb-2 col-md-3">
                        <div class="form-group">
                            <h5><?php echo lang('ref_no'); ?></h5>
                            <p class=""><?php echo escape_output($purchase_details->reference_no) ?></p>
                        </div>
                    </div>

                    <div class="col-sm-12 mb-2 col-md-3">
                        <div class="form-group">
                            <h5><?php echo lang('supplier'); ?></h5>
                            <?php echo escape_output(getSupplierNameById($purchase_details->supplier_id)); ?>
                        </div>
                    </div>

                    <div class="col-sm-12 mb-2 col-md-3">
                        <div class="form-group">
                            <h5><?php echo lang('date'); ?></h5>
                            <p class="">
                                <?php echo escape_output(date($this->session->userdata('date_format'), strtotime($purchase_details->date))); ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-sm-12 mb-2 col-md-3">
                        <div class="form-group">
                            <h5>N° Factura</h5>
                            <p class=""><?php echo escape_output($purchase_details->factura_nro) ?></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive" id="purchase_cart">
                            <table class="table print-table">
                                <thead>
                                    <tr>
                                        <th class="txt_31"><?php echo lang('sn'); ?></th>
                                        <th class="txt_33">
                                            <?php echo lang('ingredient'); ?>(<?php echo lang('code'); ?>)</th>
                                        <th class="txt_32"><?php echo lang('unit_price'); ?></th>
                                        <th class="txt_32"><?php echo lang('quantity_amount'); ?></th>
                                        <th>
                                            <?php if(tipoFacturacion() != 'RD_AI'): ?>
                                                IVA
                                            <?php else :  ?>
                                                ITBIS
                                            <?php endif; ?>
                                        </th>
                                        <th class="txt_33"><?php echo lang('total'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 0;
                                    if ($purchase_ingredients && !empty($purchase_ingredients)) {
                                        foreach ($purchase_ingredients as $pi) {
                                            $i++;
                                            $itbis = 0;
                                            $total = $pi->total;
                                            if(tipoFacturacion() != 'RD_AI'){
                                            } else {
                                                if (isset($pi->iva_tipo)){
                                                    $itbis = (  $pi->unit_price * $pi->quantity_amount) * ($pi->iva_tipo / 100);
                                                    $total = $pi->total + $itbis;
                                                }
                                            }
                                            echo '<tr id="row_' . $i . '">' .
                                            '<td class="txt_24"><p>' . $i . '</p></td>' .
                                            '<td class="ir_w_20"><p class="txt_18">' . getIngredientNameById($pi->ingredient_id) . ' (' . getIngredientCodeById($pi->ingredient_id) . ')</p></td>' .
                                            '<td class="ir_w_15">' . escape_output(getAmtPCustom($pi->unit_price)) . '</td>' .
                                            '<td class="ir_w_15">' . $pi->quantity_amount . ' ' . unitName(getPurchaseUnitIdByIgId($pi->ingredient_id)) . '</td>' .
                                            '<td class="ir_w_20">' .  escape_output(getAmtPCustom($itbis)) . '</td>' .
                                            '<td class="ir_w_20">' . escape_output(getAmtPCustom($total)) . '</td>' .
                                            '</tr>'
                                            ;
                                        }
                                    }
                                    ?>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-7">

                    </div>
                    <div class="col-md-3 pull-right print-totals">
                        <div class="total-row">
                            <strong><?php echo lang('g_total'); ?>:</strong>
                            <span><?php echo escape_output(getAmtPCustom($purchase_details->grand_total)) ?></span>
                        </div>
                        <div class="total-row">
                            <strong><?php echo lang('paid'); ?>:</strong>
                            <span><?php echo escape_output(getAmtPCustom($purchase_details->paid)) ?></span>
                        </div>
                        <div class="total-row grand-total">
                            <strong><?php echo lang('due'); ?>:</strong>
                            <span><?php echo escape_output(getAmtPCustom($purchase_details->due)) ; ?></span>
                        </div>
                        <div class="total-row">
                            <strong><?php echo lang('payment_method'); ?>:</strong>
                            <span><?php echo escape_output(getPaymentName($purchase_details->payment_id)) ; ?></span>
                        </div>
                    </div>
                    <div class="col-md-3">

                    </div>
                </div>
                <div class="row">
                    <div class="col-md-offset-6 col-md-3">

                    </div>
                    <div class="col-md-3">

                    </div>
                </div>
            </div>
            

            <div class="box-footer no-print">
                <button class="print-btn" onclick="window.print()">
                    <i data-feather="printer"></i>
                    Imprimir Recibo
                </button>
                <a class="btn bg-blue-btn me-2" href="<?php echo base_url() ?>Purchase/addEditPurchase/<?php echo escape_output($encrypted_id); ?>">
                    <i data-feather="edit"></i>
                    <?php echo lang('edit'); ?>
                </a>
                <a class="btn bg-blue-btn" href="<?php echo base_url() ?>Purchase/purchases">
                    <i data-feather="corner-up-left"></i>
                    <?php echo lang('back'); ?>
                </a>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
        
</section>

<script>
// Asegurar que los iconos de Feather se rendericen
if (typeof feather !== 'undefined') {
    feather.replace();
}

// Función mejorada para impresión
function printReceipt() {
    // Mostrar elementos que solo deben aparecer en la impresión
    const printHeaders = document.querySelectorAll('.print-header');
    printHeaders.forEach(header => {
        header.style.display = 'block';
    });
    
    // Imprimir
    window.print();
    
    // Ocultar elementos después de la impresión
    setTimeout(() => {
        printHeaders.forEach(header => {
            header.style.display = 'none';
        });
    }, 1000);
}

// Agregar event listener a todos los botones de imprimir
document.addEventListener('DOMContentLoaded', function() {
    const printButtons = document.querySelectorAll('.print-btn');
    printButtons.forEach(button => {
        button.setAttribute('onclick', 'printReceipt()');
    });
});
</script>