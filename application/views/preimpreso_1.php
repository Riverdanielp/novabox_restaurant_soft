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
    $items_per_page = 10;
    $chunks = array_chunk($items_data, $items_per_page);
    foreach ($chunks as $chunk) {
        $page_total = 0;
        foreach ($chunk as $item) {
            $price = isset($item->menu_price_with_discount) ? floatval($item->menu_price_with_discount) : 0;
            $qty = isset($item->qty) ? floatval($item->qty) : 1;
            $page_total += $price; //* $qty
        }
        $page_iva_10 = $page_total / 11;
        $page_iva_5 = 0;
        $pages[] = [
            'items' => $chunk,
            'total' => $page_total,
            'iva_10' => $page_iva_10,
            'iva_5' => $page_iva_5
        ];
    }
} else {
    $pages[] = [
        'items' => is_array($items) ? $items : [],
        'total' => floatval($total),
        'iva_10' => floatval($total) / 11,
        'iva_5' => 0
    ];
}

function item_recibo($cantidad,$concepto,$costo,$monto,$cuota_nros = []){ 
    return '
    <tr>
        <td  style="width: 20%;text-align:center;"> 
            '.($cantidad).' 
        </td>
        <td  style="width: 42%;" class="border-bottom"> 
            <em>' . $concepto. '</em>
        </td>
        <td  style="width: 18%;text-align:center;" class="border-bottom"> 
        ' . getAmtPCustom($costo) . '
        </td>
        <td  style="width: 5%;" class="border-bottom"> 
        </td>
        <td  style="width: 15%;text-align:center;" class="border-bottom"> 
        ' . getAmtPCustom($monto) . '
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
                        <td style="width: 40%;height:5px;<?php echo $bordes ?>"> 
                            <strong><?php echo $texto_relleno ?></strong>
                        </td>
                        <td style="width: 25%;<?php echo $bordes ?> " class="border-bottom"> 
                            <?php echo isset($fecha) ? date('d/m/Y', strtotime($fecha)) : date('d/m/Y') ?>
                        </td>
                        <td style="width: 35%;<?php echo $bordes ?> " class="border-bottom"> 
                            <strong><?php echo isset($ruc) ? htmlspecialchars($ruc) : '' ?></strong>
                        </td>
                        <!-- <td style="width: 8%;<?php echo $bordes ?> " class="border-bottom"> 
                            <?php echo $credito ?>
                        </td> -->
                    </tr>
                </table>
                
                <table cellspacing="0" style="width: 100%;" class="detalle">
                    <tr style="height: 5px;">
                        <td style="width: 40%;<?php echo $bordes ?>"> 
                            <strong><?php echo $texto_relleno ?></strong>
                        </td>
                        <td style="width: 60%;height: 5px;<?php echo $bordes ?> " class="border-bottom"> 
                            <em><?php echo isset($nombre) ? htmlspecialchars($nombre) : 'Cliente Ocasional' ?></em> <br>
                            <em><?php echo isset($direccion) ? htmlspecialchars($direccion) : '' ?></em> <br>
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
                                    <?php foreach ($pageData['items'] as $Item): ?>
                                        <?php echo item_recibo(
                                            isset($Item->qty) ? $Item->qty : (isset($Item->quantity_purchased) ? $Item->quantity_purchased : 0),
                                            isset($Item->menu_name) ? $Item->menu_name : (isset($Item->Producto) ? $Item->Producto : ''),
                                            isset($Item->menu_price_with_discount) ? $Item->menu_price_with_discount : (isset($Item->item_unit_price) ? $Item->item_unit_price : 0),
                                            (isset($Item->menu_price_with_discount) && isset($Item->qty)) ? ($Item->menu_price_with_discount) : (isset($Item->total) ? $Item->total : 0) // * $Item->qty
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
                        <td style="width: 20%;<?php echo $bordes ?> " class="border-bottom"></td>
                        <td style="width: 55%;<?php echo $bordes ?> " class="border-bottom">
                            <?php echo isset($page_total) ? ucfirst(numeroConDecimalesATexto($page_total)) . '.' : 'Cero' ?>
                        </td>
                        <td style="width: 5%;text-align:center;<?php echo $bordes ?> " class="border-bottom">0</td>
                        <td style="width: 5%;text-align:center;<?php echo $bordes ?> " class="border-bottom">0</td>
                        <td style="width: 15%;text-align:center;<?php echo $bordes ?> " class="border-bottom">
                            <?php echo isset($page_total) ? getAmtPCustom($page_total) : '0.00' ?>
                        </td>
                    </tr>
                </table>

                <table cellspacing="0" style="width: 100%;height: 17px;padding:0px;">
                    <tr>
                        <td style="width: 25%;<?php echo $bordes ?> " class="border-bottom"></td>
                        <td style="width: 15%;<?php echo $bordes ?> " class="border-bottom"><?php echo getAmtPCustom($page_iva_5) ?></td>
                        <td style="width: 20%;<?php echo $bordes ?> " class="border-bottom"><?php echo getAmtPCustom($page_iva_10) ?></td>
                        <td style="width: 25%;<?php echo $bordes ?> " class="border-bottom"><?php echo getAmtPCustom($page_iva_5 + $page_iva_10) ?></td>
                        <td style="width: 15%;text-align:center;<?php echo $bordes ?> " class="border-bottom">
                            <?php echo isset($page_total) ? getAmtPCustom($page_total) : '0.00' ?>
                        </td>
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