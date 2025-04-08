<?php
// Variables predefinidas
$texto_relleno = '';
$contado = '<b>X</b>';
$credito = '';
$bordes = '';
$font_size = 'font-size:3px;';

// Calcular IVAs
$iva_5 = '0';
$iva_10 = isset($total) ? (floatval($total) / 11) : 0;


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
                        <?php if(isset($items) && is_array($items)): ?>
                            <?php foreach($items as $Item): ?>
                                <?php echo item_recibo(
                                    isset($Item->quantity_purchased) ? $Item->quantity_purchased : 0,
                                    isset($Item->Producto) ? $Item->Producto : '',
                                    isset($Item->item_unit_price) ? $Item->item_unit_price : 0,
                                    isset($Item->total) ? $Item->total : 0
                                ); ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Mostrar un item vacío si no hay datos -->
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
                    <?php echo isset($total) ? ucfirst(numeroConDecimalesATexto($total)) . '.' : 'Cero' ?>
                </td>
                <td style="width: 5%;text-align:center;<?php echo $bordes ?> " class="border-bottom">0</td>
                <td style="width: 5%;text-align:center;<?php echo $bordes ?> " class="border-bottom">0</td>
                <td style="width: 15%;text-align:center;<?php echo $bordes ?> " class="border-bottom">
                    <?php echo isset($total) ? getAmtPCustom($total) : '0.00' ?>
                </td>
            </tr>
        </table>

        <table cellspacing="0" style="width: 100%;height: 17px;padding:0px;">
            <tr>
                <td style="width: 25%;<?php echo $bordes ?> " class="border-bottom"></td>
                <td style="width: 15%;<?php echo $bordes ?> " class="border-bottom"><?php echo getAmtPCustom($iva_5) ?></td>
                <td style="width: 20%;<?php echo $bordes ?> " class="border-bottom"><?php echo getAmtPCustom($iva_10) ?></td>
                <td style="width: 25%;<?php echo $bordes ?> " class="border-bottom"><?php echo getAmtPCustom($iva_10) ?></td>
                <td style="width: 15%;text-align:center;<?php echo $bordes ?> " class="border-bottom">
                    <?php echo isset($total) ? getAmtPCustom($total) : '0.00' ?>
                </td>
            </tr>
        </table>
    </div>
    <?php $espaciado = 55 - ($i * 12); ?>
    <?php if ($i < 3) : ?>
    <div style="height: <?php echo $espaciado ?>px"></div>
    <?php endif; ?>
<?php endfor; ?>

</page>	

<script>
    window.addEventListener("load", window.print());
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