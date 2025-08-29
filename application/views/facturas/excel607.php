<?php 
$date = date('Y-m-d_His');
header("Pragma: public");
header("Expires: 0");
$filename = "Reporte_607_$date.xls";
header("Content-type: application/x-msdownload");
header("Content-Disposition: attachment; filename=$filename");
header("Pragma: no-cache");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

?>
<table>
	<tbody>
		<tr>
            <th>#</th>
            <th>RNC_CEDULA</th>
            <th>TIPO_IDENTIFICACION</th>
            <th>NUMERO_COMPROBANTE_FISCAL</th>
            <th>NUMERO_COMPROBANTE_MODIFICADO</th>
            <th>TIPO_INGRESO</th>
            <th>FECHA_COMPROBANTE</th>
            <th>FECHA_RETENCION</th>
            <th>MONTO_FACTURADO</th>
            <th>ITBIS_FACTURADO</th>
            <th>ITBIS_RETENIDO</th>
            <th>ITBIS_PERCIBIDO</th>
            <th>ISR_RETENCION_RENTA</th>
            <th>ISR_PERCIBIDO</th>
            <th>IMPUESTO_SELECTIVO_CONSUMO</th>
            <th>OTROS_IMPUESTOS_TASAS</th>
            <th>MONTO_PROPINA_LEGAL</th>
            <th>EFECTIVO</th>
            <th>CHEQUE/TRANFERENCIA/DEPOSITO</th>
            <th>TARJETA_DEBITO/CREDITO</th>
            <th>VENTA_CREDITO</th>
            <th>BONOS</th>
            <th>PERMUTA</th>
            <th>OTRAS_FORMAS</th>
            <!-- <th>SN - Razon Social o Nombre</th>
            <th>Numero Interno</th> -->
		</tr>
            <?php if (!(empty($indice))) : ?>
                <?php for ($i=0; $i < count($indice); $i++) : ?>
                    <tr>
                        <td><?php echo $indice[$i]; ?></td>
                        <td><?php echo $rnc[$i]; ?></td>
                        <td><?php echo $tipo_ident[$i]; ?></td>
                        <td><?php echo $ncf[$i]; ?></td>
                        <td><?php echo $NUMERO_COMPROBANTE_MODIFICADO[$i]; ?></td>
                        <td><?php echo $TIPO_INGRESO[$i]; ?></td>
                        <td><?php echo $fecha_comprobante[$i]; ?></td>
                        <td><?php echo $fecha_retencion[$i]; ?></td>
                        <td><?php echo $monto_facturado[$i]; ?></td>
                        <td><?php echo $itbis_facturado[$i]; ?></td>
                        <td><?php echo $ITBIS_RETENIDO[$i]; ?></td>
                        <td><?php echo $ITBIS_PERCIBIDO[$i]; ?></td>
                        <td><?php echo $ISR_RETENCION_RENTA[$i]; ?></td>
                        <td><?php echo $ISR_PERCIBIDO[$i]; ?></td>
                        <td><?php echo $IMPUESTO_SELECTIVO_CONSUMO[$i]; ?></td>
                        <td><?php echo $OTROS_IMPUESTOS_TASAS[$i]; ?></td>
                        <td><?php echo $MONTO_PROPINA_LEGAL[$i]; ?></td>
                        <td><?php echo $EFECTIVO[$i]; ?></td>
                        <td><?php echo $CHEQUE[$i]; ?></td>
                        <td><?php echo $TARJETA[$i]; ?></td>
                        <td><?php echo $CREDITO[$i]; ?></td>
                        <td><?php echo $BONOS[$i]; ?></td>
                        <td><?php echo $PERMUTA[$i]; ?></td>
                        <td><?php echo $OTRAS_FORMAS[$i]; ?></td>
                        <!-- <td><?php echo $SN[$i]; ?></td>
                        <td><?php echo $numero_interno[$i]; ?></td> -->
                    </tr>
                <?php endfor; ?>
            <?php endif; ?>
	</tbody>
</table>