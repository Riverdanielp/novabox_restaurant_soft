<script src="<?php echo base_url(); ?>assets/plugins/barcode/JsBarcode.all.js"></script>
<link rel="stylesheet" href="<?php echo base_url(); ?>frequent_changing/css/inline_priority.css">
<style>
@media print {
    @page {
        size: 80mm 30mm;
        margin: 0;
    }
    body, html {
        width: 80mm;
        height: 30mm;
        margin: 0 !important;
        padding: 0 !important;
        background: white;
    }
    #printableArea {
        width: 80mm;
        height: 30mm;
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 0;
        padding: 0;
        background: white;
    }
    .etiqueta {
        width: 76mm;
        height: 26mm;
        margin: 2mm auto;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: center;
        border-radius: 3mm;
        background: white;
        font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;
        /* Sombra opcional para pruebas visuales */
        /* box-shadow: 0 0 2mm #ccc; */
    }
    .sucursal {
        font-size: 13pt;
        font-weight: bold;
        text-align: center;
        color: #222;
        margin-bottom: 1.5mm;
        letter-spacing: 0.5mm;
    }
    .barcode {
        display: block;
        margin: 0 auto;
        height: 8mm !important;
        width: 58mm !important;
    }
    .codigo {
        font-size: 10pt;
        font-family: 'Courier New', Courier, monospace;
        letter-spacing: 2px;
        text-align: center;
        margin: 0.8mm 0 1.2mm 0;
        color: #444;
    }
    .producto {
        font-size: 11pt;
        font-weight: 500;
        text-align: center;
        color: #222;
        margin: 0.8mm 0 1mm 0;
    }
    .precio {
        font-size: 17pt;
        font-weight: bold;
        text-align: center;
        color: #007bff;
        margin-top: 1mm;
        letter-spacing: 1px;
    }
}
</style>
<section class="main-content-wrapper">
    <div class="box-wrapper">
        <div class="table-box">
            <div class="row">
                <div class="col-md-6">
                    <div id="printableArea">
                    <?php for($i=0;$i<sizeof($items);$i++):
                        for($j=0;$j<$items[$i]['qty'];$j++): ?>
                            <div class="etiqueta">
                                <div class="sucursal"><?= $this->session->userdata('outlet_name') ?></div>
                                <img class="barcode" id="barcode<?=$items[$i]['id']?><?=$j?>"/>
                                <div class="codigo"><?= $items[$i]['code'] ?></div>
                                <div class="producto"><?= $items[$i]['item_name'] ?></div>
                                <div class="precio"><?= getAmtCustom($items[$i]['sale_price']) ?></div>
                            </div>
                            <script>
                            JsBarcode("#barcode<?=$items[$i]['id']?><?=$j?>", "<?=$items[$i]['code']?>", {
                                width: 1,
                                height: 10, // 8mm aprox, ajusta según resultado
                                fontSize: 10,
                                margin: 0,
                                displayValue: false
                            });
                            </script>
                    <?php endfor; endfor; ?>
                    </div>
                </div>
                
                <div class="col-md-3">
                        <a class="btn bg-blue-btn w-100" href="<?php echo base_url() ?>foodMenu/foodMenuBarcode">
                            <?php echo lang('back'); ?>
                        </a>
                </div>

                <div class="col-md-3">
                        <a class="btn bg-blue-btn w-100" onclick="printDiv('printableArea')"><?php echo lang('print')?></a>
                   
                </div>
            </div>
        </div>
    </div>
        
</section>
<script src="<?php echo base_url(); ?>frequent_changing/js/barcode_preview.js"></script>