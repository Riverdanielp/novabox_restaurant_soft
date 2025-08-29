<?php
$name_file = 'DGII_F_607_' . $RNC_EMPRESA . '_' . $mes;
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);
header("Content-Description: File Transfer");
header("Content-Type: application/force-download");
header("Content-Disposition: attachment; filename=$name_file.txt");

$cantidad = count($indice);
echo "607|" . "$RNC_EMPRESA" . "|" . "$mes" . "|" . "$cantidad
";

if (!(empty($indice))) : 
for ($i=0; $i < count($indice); $i++) : 
echo 
$rnc[$i] . "|" . //1
$tipo_ident[$i] . "|" . //2
$ncf[$i] . "|" . //3
$NUMERO_COMPROBANTE_MODIFICADO[$i] . "|" . //4
$TIPO_INGRESO[$i] . "|" . //5
$fecha_comprobante[$i] . "|" . //6
$fecha_retencion[$i] . "|" . //7
$monto_facturado[$i] . "|" . //8
$itbis_facturado[$i] . "|" . //9
$ITBIS_RETENIDO[$i] . "|" . //10
$ITBIS_PERCIBIDO[$i] . "|" . //11
$ISR_RETENCION_RENTA[$i] . "|" . //12
$ISR_PERCIBIDO[$i] . "|" . //13
$IMPUESTO_SELECTIVO_CONSUMO[$i] . "|" . //14
$OTROS_IMPUESTOS_TASAS[$i] . "|" . //15
$MONTO_PROPINA_LEGAL[$i] . "|" . //16
$EFECTIVO[$i] . "|" . //17
$CHEQUE[$i] . "|" . //18
$TARJETA[$i] . "|" . //19
$CREDITO[$i] . "|" . //20
$BONOS[$i] . "|" . //21
$PERMUTA[$i] . "|" . //22
$OTRAS_FORMAS[$i] . " 
";
endfor;
endif;