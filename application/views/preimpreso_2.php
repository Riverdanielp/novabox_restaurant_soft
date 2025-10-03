<?php
// Variables predefinidas (sin cambios)
$bordes = 'border: solid 1px #000000ff;';
$font_size = 'font-size:3px;';

// Simulación de datos (puedes borrarlos)
// $data = new stdClass(); $data->tipo = 'todos'; $data->items_data = [ (object)['qty'=>2, 'menu_name'=>'Producto de prueba largo para ver el texto', 'menu_price_with_discount'=>50000, 'iva_tipo'=>'10'], (object)['qty'=>1, 'menu_name'=>'Otro item', 'menu_price_with_discount'=>25000, 'iva_tipo'=>'5'] ];
// $fecha = '2025-09-30'; $ruc = '80012345-6'; $nombre = 'Daniel Riveros'; $direccion = 'Asunción, Paraguay'; $telefono = '0981123456';

// Recibes los datos como objetos directamente
$tipo = isset($tipo) ? $tipo : (isset($data->tipo) ? $data->tipo : null);
$items_data = isset($items_data) ? $items_data : (isset($data->items_data) ? $data->items_data : []);
$items = isset($items) ? $items : (isset($data->items) ? $data->items : []);
$total = isset($total) ? $total : (isset($data->total) ? $data->total : 0);

// Funciones auxiliares (asegúrate de que estén definidas en tu proyecto)
if (!function_exists('getAmtPCustom')) {
    function getAmtPCustom($amount) { return number_format($amount, 0, ',', '.'); }
}
if (!function_exists('numeroConDecimalesATexto')) {
    function numeroConDecimalesATexto($num) { return '...'; /* Placeholder */ }
}

// Bloque de paginación y cálculo (sin cambios)
$pages = [];
if ($tipo === 'todos' && is_array($items_data) && !empty($items_data)) {
    $items_per_page = 8; // Máximo de items por página. AJUSTA SI ES NECESARIO.
    $chunks = array_chunk($items_data, $items_per_page);
    foreach ($chunks as $chunk) {
        $subtotal_10 = 0; $subtotal_5 = 0; $subtotal_exenta = 0;
        foreach ($chunk as $item) {
            $precio = isset($item->menu_price_with_discount) ? floatval($item->menu_price_with_discount) : (isset($item->total) ? floatval($item->total) : 0);
            $iva_tipo = isset($item->iva_tipo) ? $item->iva_tipo : "10";
            if ($iva_tipo == "10") { $subtotal_10 += $precio; } 
            elseif ($iva_tipo == "5") { $subtotal_5 += $precio; } 
            else { $subtotal_exenta += $precio; }
        }
        $pages[] = [
            'items' => $chunk, 'total' => $subtotal_10 + $subtotal_5 + $subtotal_exenta,
            'iva_10' => $subtotal_10 / 11, 'iva_5' => $subtotal_5 / 21,
            'subtotal_10' => $subtotal_10, 'subtotal_5' => $subtotal_5, 'subtotal_exenta' => $subtotal_exenta,
        ];
    }
} else {
    $pages[] = [
        'items' => is_array($items) ? $items : [], 'total' => floatval($total), 'iva_10' => floatval($total) / 11, 'iva_5' => 0,
        'subtotal_10' => floatval($total), 'subtotal_5' => 0, 'subtotal_exenta' => 0,
    ];
}

function item_recibo($cantidad, $concepto, $costo_unitario, $monto, $iva_tipo = 10){ 
    $bordes_item = '';
    return '
    <tr >
        <td style="width: 5%; text-align: center;">'. substr($cantidad, 0, 7) .'</td>
        <td style="width: 70%;">'.  substr($concepto, 0, 70) .'</td>
        <td style="width: 10%; text-align: right;">'. getAmtPCustom($costo_unitario) .'</td>
        <td style="width: 10%; text-align: right;">'.  getAmtPCustom($monto) .'</td>
        <td style="width: 5%; text-align: center;">'. $iva_tipo .'%</td>
    </tr>';
}; 
?>

<style type="text/css">
    table { vertical-align: top; border-collapse: collapse; width: 100%; }
    tr, td { vertical-align: top; padding: 1px; } /* Padding reducido para compactar */
    .text-center { text-align:center; }
    .text-right { text-align:right; }
    table th, td { font-size:10px; line-height: 1.1; } /* Tamaño de fuente y altura de línea ajustados */
    .border-bottom { border-bottom: solid 0.5px #000; }
    .total-border { border: solid 0.5px #000; }
</style>

<?php foreach ($pages as $pageIndex => $pageData): ?>
    <?php
        $page_total = $pageData['total'];
        $page_iva_10 = $pageData['iva_10'];
        $page_iva_5 = $pageData['iva_5'];
    ?>
    <page backtop="10mm" backbottom="10mm" backleft="10mm" backright="10mm" style="font-family: Lucida-Console, monospace;">

        <?php for ($i=1; $i < 4; $i++) : ?>
            <div>
                <table cellspacing="0">
                    <tr>
                        <td style="height: 20mm; text-align:center;"> 
                            <strong style="font-size:11px;">
                                
                            </strong>
                        </td>
                    </tr>
                </table>
                
                <table cellspacing="0">
                    <tr>
                        <!-- CONTENEDOR PRINCIPAL: Altura fija 65mm y sin padding para control total -->
                        <td style="width: 100%; border: solid 0.5px #000; height: 65mm; max-height: 65mm; padding: 0;">
                            <!-- INICIO: CONTENIDO DE FACTURA AJUSTADO -->
                            
                            <!-- 1. SECCIÓN DATOS CLIENTE (Altura reducida a 15mm) -->
                            <table cellspacing="0" style="height: 15mm;">
                                <tr>
                                    <td style="width: 5%; padding: 3px;"></td>
                                    <td style="width: 45%; padding: 3px;"> 
                                        <span style="font-size:11px;">Fecha: <?php echo isset($fecha) ? date('d/m/Y', strtotime($fecha)) : date('d/m/Y') ?></span><br>
                                        <span style="font-size:11px;">RUC/CI: <?php echo isset($ruc) ? htmlspecialchars($ruc) : '' ?></span><br>
                                        <span style="font-size:11px;">Cliente: <?php echo isset($nombre) ? htmlspecialchars($nombre) : 'Cliente Ocasional' ?></span><br>
                                        <span style="font-size:11px;">Dirección: <?php echo isset($direccion) ? htmlspecialchars($direccion) : '' ?></span>
                                    </td>
                                    <!--width: 30%;  Celda del teléfono: display flex para posicionar contenido -->
                                    <td style="padding: 3px; display: flex; flex-direction: column; justify-content: space-between; height: 14mm;"> 
                                        <span style="font-size:11px;">Condición de venta: CONTADO</span>
                                        <span style="font-size:11px;">Teléfono: <?php echo isset($telefono) ? htmlspecialchars($telefono) : '' ?></span>
                                    </td>
                                    <td style="width: 20%; padding: 3px;"> 
                                        <span style="font-size:11px;">Moneda: GUARANIES</span>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- 2. SECCIÓN DE ITEMS (Altura fija 35mm) -->
                            <table cellspacing="0" style="height: 35mm; max-height: 35mm;">
                                <thead style="display: table; width: 100%; table-layout: fixed;">
                                    <tr>
                                        <th style="width: 5%;" class="border-bottom">Cant.</th>
                                        <th style="width: 70%; text-align:left;" class="border-bottom">Descripción</th>
                                        <th style="width: 10%; text-align: right;" class="border-bottom">Unitario</th>
                                        <th style="width: 10%; text-align: right;" class="border-bottom">Valor</th>
                                        <th style="width: 5%; text-align: center;" class="border-bottom">IVA</th>
                                    </tr>
                                </thead>
                                <tbody style="display: table;width: 100%;table-layout: fixed;">
                                    <?php if (isset($pageData['items']) && is_array($pageData['items'])): ?>
                                        <?php foreach ($pageData['items'] as $item): ?>
                                            <?php echo item_recibo(
                                                isset($item->qty) ? $item->qty : 0,
                                                isset($item->menu_name) ? $item->menu_name : '',
                                                (isset($item->menu_price_with_discount, $item->qty) && floatval($item->qty) > 0) ? (floatval($item->menu_price_with_discount) / floatval($item->qty)) : 0,
                                                (isset($item->menu_price_with_discount)) ? $item->menu_price_with_discount : 0,
                                                isset($item->iva_tipo) ? $item->iva_tipo : '10'
                                            ); ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            
                            <!-- 3. SECCIÓN DE TOTALES (Altura reducida a 14mm) -->
                            <table cellspacing="0" style="height: 14mm;">
                                <tr>
                                    <td style="width: 60%; padding: 2px;border-top: solid 0.5px #000000;" colspan="3" class="border-top">
                                        En letras: Guaranies <?php echo isset($page_total) ? ucfirst(numeroConDecimalesATexto($page_total)) . '.' : 'Cero' ?>
                                    </td>
                                    <td style="width: 30%;" rowspan="2" class="total-border">
                                        <table style="height: 100%;">
                                            <tr>
                                                <td style="width: 60%; text-align:right;">
                                                    Sub-Total Exentas:<br>
                                                    Sub-Total 5%:<br>
                                                    Sub-Total 10%:<br>
                                                    <b>Total a Pagar:</b>
                                                </td>
                                                <td style="width: 40%; text-align:right; padding-right: 3px;">
                                                    <?php echo getAmtPCustom($pageData['subtotal_exenta']); ?><br>
                                                    <?php echo getAmtPCustom($pageData['subtotal_5']); ?><br>
                                                    <?php echo getAmtPCustom($pageData['subtotal_10']); ?><br>
                                                    <b><?php echo getAmtPCustom($pageData['total']); ?></b>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr class="total-border">
                                    <td style="width: 25%; padding: 2px;" class="">Liq. IVA 5%: <?php echo getAmtPCustom($pageData['iva_5']); ?></td>
                                    <td style="width: 15%; padding: 2px;" class="">10%: <?php echo getAmtPCustom($pageData['iva_10']); ?></td>
                                    <td style="width: 20%; padding: 2px;" class="">Total IVA: <?php echo getAmtPCustom($pageData['iva_5'] + $pageData['iva_10']); ?></td>
                                </tr>
                            </table>
                            <!-- FIN: CONTENIDO DE FACTURA -->
                        </td>
                    </tr>
                </table>
            </div>
            <?php $espaciado = 2 + ($i * 5); ?>
            <?php if ($i < 3) : ?>
                <div style="height: <?php echo $espaciado ?>mm"></div>
            <?php endif; ?>
        <?php endfor; ?>

    </page>	
    <?php if ($pageIndex < count($pages) - 1): ?>
        <div style="page-break-after: always;"></div>
    <?php endif; ?>
<?php endforeach; ?>

<script>
    // window.addEventListener("load", () => window.print());
    if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
        window.addEventListener('click', () => window.close());
    } else {
        // window.addEventListener('afterprint', () => window.close());
    }
</script>