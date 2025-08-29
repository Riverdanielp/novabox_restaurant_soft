<?php
$name_file = 'DGII_F_606_' . $RNC_EMPRESA . '_' . $mes;
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);
header("Content-Description: File Transfer");
header("Content-Type: application/force-download");
header("Content-Disposition: attachment; filename=$name_file.txt");

$cantidad = count($indice);
echo "606|" . "$RNC_EMPRESA" . "|" . "$mes" . "|" . "$cantidad
";

if (!(empty($indice))) : 
for ($i=0; $i < count($indice); $i++) : 
echo 
$rnc[$i] . "|" . //1
$tipo_ident[$i] . "|" . //2
$TIPO_BIENES[$i] . "|" . //3
$ncf[$i] . "|" . //4
$NUMERO_COMPROBANTE_MODIFICADO[$i] . "|" . //5
$fecha_comprobante[$i] . $dia_comprobante[$i] . "|" . //6
$fecha_pago[$i] . $dia_pago[$i] . "|" . //7
$MONTO_SERVICIOS[$i] . "|" . //8
$MONTO_BIENES[$i]. "|" . //9
$monto_facturado[$i] . "|" . //10
$itbis_facturado[$i] . "|" . //11
$ITBIS_RETENIDO[$i] . "|" . //12
$ITBIS_PROPORCIONALIDAD[$i] . "|" . //13
$ITBIS_LLEVADO_COSTO[$i] . "|" . //14
$ITBIS_ADELANTAR[$i] . "|" . //15
$ITBIS_PERCIBIDO[$i] . "|" . //16
$ISR_TIPO_RETENCION[$i] . "|" . //17
$ISR_RETENCION_RENTA[$i] . "|" . //18
$ISR_PERCIBIDO[$i] . "|" . //19
$IMPUESTO_SELECTIVO_CONSUMO[$i] . "|" . //20
$OTROS_IMPUESTOS_TASAS[$i] . "|" . //21
$MONTO_PROPINA_LEGAL[$i] . "|" . //22
$tipo_pago[$i] . "
";
endfor;
endif;