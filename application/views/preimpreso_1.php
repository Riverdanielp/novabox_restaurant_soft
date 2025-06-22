<?php
// Variables predefinidas
$texto_relleno = '';
$contado = '<b>X</b>';
$credito = '';
$bordes = '';
$font_size = 'font-size:3px;';

// // Calcular IVAs
// $iva_5 = '0';
// $iva_10 = isset($total) ? (floatval($total) / 11) : 0;

// Recibes los datos como objetos directamente
// Variables previas (pueden venir del objeto $data que pasaste desde el controlador)
$tipo = isset($tipo) ? $tipo : (isset($data->tipo) ? $data->tipo : null);
$items_data = isset($items_data) ? $items_data : (isset($data->items_data) ? $data->items_data : []);
$items = isset($items) ? $items : (isset($data->items) ? $data->items : []);
$total = isset($total) ? $total : (isset($data->total) ? $data->total : 0);

// Aquí va el bloque de paginación y cálculo:
$pages = [];
if ($tipo === 'todos' && is_array($items_data)) {
    $items_per_page = 12;
    $chunks = array_chunk($items_data, $items_per_page);
    foreach ($chunks as $chunk) {
        // Inicializa los acumuladores de cada tipo de subtotal e IVA
        $subtotal_10 = 0;
        $subtotal_5 = 0;
        $subtotal_exenta = 0;

        foreach ($chunk as $item) {
            $precio = isset($item->menu_price_with_discount) ? floatval($item->menu_price_with_discount) : 0;
            $qty = isset($item->qty) ? floatval($item->qty) : 1;
            $total_item = $precio; // O usa $precio * $qty si corresponde
            $iva_tipo = isset($item->iva_tipo) ? $item->iva_tipo : "10";

            if ($iva_tipo == "10") {
                $subtotal_10 += $total_item;
            } elseif ($iva_tipo == "5") {
                $subtotal_5 += $total_item;
            } else {
                $subtotal_exenta += $total_item;
            }
        }
        $page_total = $subtotal_10 + $subtotal_5 + $subtotal_exenta;
        $page_iva_10 = $subtotal_10 / 11;
        $page_iva_5 = $subtotal_5 / 21;

        $pages[] = [
            'items' => $chunk,
            'total' => $page_total,
            'iva_10' => $page_iva_10,
            'iva_5' => $page_iva_5,
            'subtotal_10' => $subtotal_10,
            'subtotal_5' => $subtotal_5,
            'subtotal_exenta' => $subtotal_exenta,
        ];
    }
} else {
    $pages[] = [
        'items' => is_array($items) ? $items : [],
        'total' => floatval($total),
        'iva_10' => floatval($total) / 11,
        'iva_5' => 0,
        'subtotal_10' => floatval($total),
        'subtotal_5' => 0,
        'subtotal_exenta' => 0,
    ];
}

function item_recibo($cantidad,$concepto,$costo_unitario,$monto,$iva_tipo = 10){ 
    if ($iva_tipo == 0) {
        $monto_10 = '';
        $monto_5 = '';
        $monto_0 = getAmtPCustom($monto);
    } else if ($iva_tipo == 5) {
        $monto_10 = '';
        $monto_5 = getAmtPCustom($monto);
        $monto_0 = '';
    } else {
        $monto_10 = getAmtPCustom($monto);
        $monto_5 = '';
        $monto_0 = '';
    }
    return '
    <tr>
        <td  style="width: 6%;text-align:center;"> 
            '.
            // substr($codigo, 0, 50).
            ' 
        </td>
        <td  style="width: 8%;text-align:center;"> 
            '.substr($cantidad, 0, 7).' 
        </td>
        <td  style="width: 4%;text-align:center;"> 
        </td>
        <td  style="width: 38%;" class="border-bottom"> 
            ' . substr($concepto, 0, 50). '
        </td>
        <td  style="width: 10%;text-align:right; font-size:9px" class="border-bottom"> 
        ' . getAmtPCustom($costo_unitario) . '
        </td>
        <td  style="width: 8%;text-align:right; font-size:9px" class="border-bottom"> 
            '. ($monto_0) . '
        </td>
        <td  style="width: 6%;text-align:right; font-size:9px" class="border-bottom"> 
            '. ($monto_5) . '
        </td>
        <td  style="width: 10%;text-align:right; font-size:9px" class="border-bottom"> 
        ' . ($monto_10) . '
        </td>
        <td  style="width: 10%;" class="border-bottom"> 
        </td>
    </tr>
    ';
}; 
?>

<style type="text/css">
    table { vertical-align: top; }
    tr    { vertical-align: top; }
    td    { vertical-align: top; }
    .text-center { text-align:center; }
    .text-right { text-align:right; }
    table th, td { font-size:11px; }
    .detalle td { padding:3px; }
    .border-bottom { border-bottom: solid 0px #bdc3c7; }
</style>

<?php foreach ($pages as $pageIndex => $pageData): ?>
    <?php
        // Variables de cabecera por página
        $page_total = $pageData['total'];
        $page_iva_10 = $pageData['iva_10'];
        $page_iva_5 = $pageData['iva_5'];
    ?>
    <page backtop="10mm" backbottom="10mm" backleft="10mm" backright="10mm" style="<?php echo $font_size ?> font-family: Calibri">

        <?php for ($i=1; $i < 4; $i++) : ?>
            <div>
                <table cellspacing="0" style="width: 100%;">
                    <tr>
                        <td style="width: 63%;height: 20px;padding:5px;text-align:center;<?php echo $bordes ?><?php echo $font_size ?>"> 
                            <strong style="font-size:14px;"></strong>
                        </td>
                        <td style="width: 37%;height: 0px;padding:5px;text-align:center;<?php echo $bordes ?><?php echo $font_size ?>"> 
                            <strong style="font-size:14px;"></strong>
                        </td>
                    </tr>
                </table>
                
                <table cellspacing="0" style="width: 100%;" class="detalle">
                    <tr style="height: 5px;">
                        <td style="width: 35%;height:5px;<?php echo $bordes ?>"> 
                            <strong><?php echo $texto_relleno ?></strong>
                        </td>
                        <td style="width: 25%;<?php echo $bordes ?> " class="border-bottom"> 
                            <?php echo isset($fecha) ? date('d/m/Y', strtotime($fecha)) : date('d/m/Y') ?>
                        </td>
                        <td style="width: 40%;<?php echo $bordes ?> " class="border-bottom"> 
                            <strong><?php echo isset($ruc) ? htmlspecialchars($ruc) : '' ?></strong>
                        </td>
                        <!-- <td style="width: 8%;<?php echo $bordes ?> " class="border-bottom"> 
                            <?php //echo $credito ?>
                        </td> -->
                    </tr>
                </table>
                
                <table cellspacing="0" style="width: 100%;" class="detalle">
                    <tr style="height: 5px;">
                        <td style="width: 35%;<?php echo $bordes ?>"> 
                            <strong><?php echo $texto_relleno ?></strong>
                        </td>
                        <td style="width: 65%;height: 5px;<?php echo $bordes ?> " class="border-bottom"> 
                            <?php echo isset($nombre) ? htmlspecialchars($nombre) : 'Cliente Ocasional' ?> <br>
                            <?php echo isset($direccion) ? htmlspecialchars($direccion) : '' ?> <br>
                        </td>
                    </tr>
                </table>
                
                <table cellspacing="0" style="width: 100%;height: 50px;padding:0px;">
                    <tr>
                        <td style="width: 5%;<?php echo $bordes ?>"></td>
                        <td style="width: 12%;<?php echo $bordes ?> " class="border-bottom"></td>
                        <td style="width: 44%;<?php echo $bordes ?> " class="border-bottom"></td>
                        <td style="width: 10%;<?php echo $bordes ?> " class="border-bottom"></td>
                        <td style="width: 10%;<?php echo $bordes ?> " class="border-bottom"></td>
                        <td style="width: 10%;<?php echo $bordes ?> " class="border-bottom"></td>
                        <td style="width: 10%;<?php echo $bordes ?> " class="border-bottom"></td>
                    </tr>
                </table>
                    
                <table cellspacing="0" style="width: 100%;height: 170px;max-height: 170px;padding:0px;">
                    <tr>
                        <td>
                            <table cellspacing="0" style="width: 100%;padding:0px;">
                                <?php if (isset($pageData['items']) && is_array($pageData['items'])): ?>
                                        <?php 
                                        // echo item_recibo(
                                        //     '1', // Placeholder for quantity, can be replaced with actual value
                                        //     '1234567890123456789012345678901234567890123456789012345678901234567890',
                                        //     '150000',
                                        //     '150000' // * $item->qty
                                        // ); 
                                        ?>
                                    <?php foreach ($pageData['items'] as $item): ?>
                                        <?php echo item_recibo(
                                            isset($item->qty) ? $item->qty : (isset($item->quantity_purchased) ? $item->quantity_purchased : 0),
                                            isset($item->menu_name) ? $item->menu_name : (isset($item->Producto) ? $item->Producto : ''),
                                            (
                                                isset($item->menu_price_with_discount, $item->qty) && floatval($item->qty) > 0
                                            )
                                                ? (floatval($item->menu_price_with_discount) / floatval($item->qty))
                                                : (isset($item->item_unit_price) ? floatval($item->item_unit_price) : 0),
                                            (isset($item->menu_price_with_discount) && isset($item->qty)) ? ($item->menu_price_with_discount) : (isset($item->total) ? $item->total : 0),
                                            isset($item->iva_tipo) ? $item->iva_tipo : '10' // * $item->qty
                                        ); ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <?php echo item_recibo(0, 'No hay items', 0, 0); ?>
                                <?php endif; ?>
                            </table>
                        </td>
                    </tr>
                </table>
                
                <table cellspacing="0" style="width: 100%;height: 21px;padding:0px;">
                    <tr>
                        <td>
                            <table cellspacing="0" style="width: 100%;padding:0px;">
                                <tr>
                                    <td style="width: 5%;<?php echo $bordes ?>"></td>
                                    <td style="width: 12%;<?php echo $bordes ?> " class="border-bottom"></td>
                                    <td style="width: 44%;<?php echo $bordes ?> " class="border-bottom"></td>
                                    <td style="width: 10%;text-align:center;<?php echo $bordes ?> " class="border-bottom"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <table cellspacing="0" style="width: 100%;height: 15px;padding:0px;">
                    <tr>
                        <td style="width: 18%;<?php echo $bordes ?> " class="border-bottom"></td>
                        <td style="width: 48%;<?php echo $bordes ?> " class="border-bottom">
                            <?php echo isset($page_total) ? ucfirst(numeroConDecimalesATexto($page_total)) . '.' : 'Cero' ?>
                        </td>
                        <td style="width: 8%;text-align:right; font-size:9px;<?php echo $bordes ?> " class="border-bottom">
                            <?php echo getAmtPCustom($pageData['subtotal_exenta']) ?></td>
                        <td style="width: 6%;text-align:right; font-size:9px;<?php echo $bordes ?> " class="border-bottom">
                            <?php echo getAmtPCustom($pageData['subtotal_5']) ?>
                        </td>
                        <td style="width: 10%;text-align:right; font-size:9px;<?php echo $bordes ?> " class="border-bottom">
                            <?php echo getAmtPCustom($pageData['subtotal_10']) ?>
                        </td>
                        <td style="width: 10%;<?php echo $bordes ?> " class="border-bottom"></td>
                    </tr>
                </table>

                <table cellspacing="0" style="width: 100%;height: 17px;padding:0px;">
                    <tr>
                        <td style="width: 20%;<?php echo $bordes ?> " class="border-bottom"></td>
                        <td style="width: 15%;<?php echo $bordes ?> " class="border-bottom"><?php echo getAmtPCustom($page_iva_5) ?></td>
                        <td style="width: 20%;<?php echo $bordes ?> " class="border-bottom"><?php echo getAmtPCustom($page_iva_10) ?></td>
                        <td style="width: 25%;<?php echo $bordes ?> " class="border-bottom"><?php echo getAmtPCustom($page_iva_5 + $page_iva_10) ?></td>
                        <td style="width: 10%;text-align:right;<?php echo $bordes ?> " class="border-bottom">
                            <?php echo isset($page_total) ? getAmtPCustom($page_total) : '0.00' ?>
                        </td>
                        <td style="width: 10%;<?php echo $bordes ?> " class="border-bottom"></td>
                    </tr>
                </table>
            </div>
            <?php $espaciado = 45 - ($i * 15); ?>
            <?php if ($i < 3) : ?>
            <div style="height: <?php echo $espaciado ?>px"></div>
            <?php endif; ?>
        <?php endfor; ?>

    </page>	
    <?php if ($pageIndex < count($pages) - 1): ?>
        <div style="page-break-after: always;"></div>
    <?php endif; ?>
<?php endforeach; ?>

<script>
    // window.addEventListener("load", window.print());
</script>
<script>
    if( navigator.userAgent.match(/Android/i)
        || navigator.userAgent.match(/webOS/i)
        || navigator.userAgent.match(/iPhone/i)
        || navigator.userAgent.match(/iPad/i)
        || navigator.userAgent.match(/iPod/i)
        || navigator.userAgent.match(/BlackBerry/i)
        || navigator.userAgent.match(/Windows Phone/i)){
            window.addEventListener('click', (event) => {
                window.close();
            });
    } else {
        // window.addEventListener('afterprint', (event) => {
        //     window.close();
        // });
    }
</script>