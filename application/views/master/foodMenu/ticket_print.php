<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/js/barcode.css?v=1.01">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo base_url(); ?>assets/landing/img/favicon.ico?v=1.02">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono&family=Space+Mono&display=swap" rel="stylesheet">
    
    <?php 
    // Obtener configuración del formato
    $config_formato = isset($formatos_disponibles[$formato]) ? $formatos_disponibles[$formato] : $formatos_disponibles['58x22'];
    ?>
    
    <style>
        body {
            font-family: 'Roboto Mono', monospace;
            margin: 0;
            padding: 0;
            background: white;
            text-align: center;
        }
        
        .centrado {
            text-align: center;
            display: block;
            margin: 2px 0;
        }
        
        .codigo {
            width: 100%;
            max-width: calc(<?php echo $config_formato['width']; ?> - 4mm);
            margin: 0 auto;
        }
        
        .ticket {
            width: <?php echo $config_formato['width']; ?>;
            height: <?php echo $config_formato['height']; ?>;
            margin: 0 auto;
            padding: 1mm;
            border: 1px solid #ddd;
            background: white;
            box-sizing: border-box;
            overflow: hidden;
            font-size: <?php echo $config_formato['font_size']; ?>;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        .product-name {
            font-weight: bold;
            margin: 1px 0;
            font-size: calc(<?php echo $config_formato['font_size']; ?> - 1px);
            line-height: 1;
            max-height: calc(<?php echo $config_formato['font_size']; ?> * 2);
            /* overflow: hidden; */
            text-overflow: ellipsis;
            white-space: nowrap;
            width: 100%;
        }
        
        .price {
            font-weight: bold;
            margin: 1px 0;
            font-size: 18px;
            line-height: 1;
        }
        
        .barcode-container {
            margin: 1px 0;
            width: 100%;
            height: auto;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        /* Formatos específicos */
        <?php if ($formato == '32x19'): ?>
        .product-name { font-size: 7px; }
        .price { font-size: 20px; }
        <?php elseif ($formato == '50x25'): ?>
        .product-name { font-size: 9px; }
        .price { font-size: 20px; }
        <?php elseif ($formato == '100x35'): ?>
        .product-name { font-size: 12px; max-height: 24px; white-space: normal; }
        .price { font-size: 22px; }
        <?php endif; ?>
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            
            .ticket {
                border: none;
                box-shadow: none;
                page-break-inside: avoid;
            }
            
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="ticket">
        <div class="centrado product-name">
            <b><?php echo escape_output($this->session->userdata('outlet_name')); ?></b> 
        </div>

        <div class="barcode-container">
            <svg data-value="<?php echo ($producto->code) ?>" 
                 data-text="<?php echo ($producto->code) ?>" 
                 class="codigo"></svg>
        </div>
        
        <div class="centrado product-name">
            <b><?php echo escape_output($producto->name) ?></b> 
        </div>
        
        <div class="centrado price"> 
            <?php echo asGs($producto->sale_price) ?> 
        </div>
        
        <?php if (MONEDAS() == 2 && isset($producto->sale_price2) && $producto->sale_price2 > 0) : ?>
            <div class="centrado price"> 
                <?php echo asRs($producto->sale_price2) ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="<?php echo base_url(); ?>assets/plugins/barcode/JsBarcode.all.js?v=1.01"></script>
    <script>
        JsBarcode(".codigo")
        .options({
            format: "CODE128",
            width: <?php echo $config_formato['barcode_width']; ?>,
            height: <?php echo $config_formato['barcode_height']; ?>,
            displayValue: true,
            fontOptions: "bold",
            textAlign: "center",
            textPosition: "bottom",
            textMargin: 1,
            fontSize: <?php echo intval($config_formato['font_size']); ?>,
            marginTop: 2,
            marginRight: 2,
            marginBottom: 2,
            marginLeft: 2,
        })
        .init();
    </script>
    
    <script>
        if( navigator.userAgent.match(/Android/i)
            || navigator.userAgent.match(/webOS/i)
            || navigator.userAgent.match(/iPhone/i)
            || navigator.userAgent.match(/iPad/i)
            || navigator.userAgent.match(/iPod/i)
            || navigator.userAgent.match(/BlackBerry/i)
            || navigator.userAgent.match(/Windows Phone/i)){
                window.print();
               
                window.addEventListener('click', (event) => {
                    window.close();
                });
            } else {
                window.print();
                window.addEventListener('afterprint', (event) => {
                   window.close();
                });
            }
    </script>
</body>
</html>