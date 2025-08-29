<?php 
$date = date('Y-m-d_His');
header("Pragma: public");
header("Expires: 0");
$filename = "Reporte_606_$date.xls";
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
            <th>TIPO_BIENES_SERVICIOS_COMPRADOS</th>
            <th>NUMERO_COMPROBANTE_FISCAL</th>
            <th>NUMERO_COMPROBANTE_MODIFICADO</th>
            <th>FECHA_COMPROBANTE</th>
            <th>DIA_COMPROBANTE</th>
            <th>FECHA_PAGO</th>
            <th>DIA_PAGO</th>
            <th>MONTO_FACTURADO_SERVICIOS</th>
            <th>MONTO_FACTURADO_BIENES</th>
            <th>MONTO_FACTURADO</th>
            <th>ITBIS_FACTURADO</th>
            <th>ITBIS_RETENIDO</th>
            <th>ITBIS_PROPORCIONALIDAD</th>
            <th>ITBIS_LLEVADO_COSTO</th>
            <th>ITBIS_ADELANTAR</th>
            <th>ITBIS_PERCIBIDO</th>
            <th>ISR_TIPO_RETENCION</th>
            <th>ISR_RETENCION_RENTA</th>
            <th>ISR_PERCIBIDO</th>
            <th>IMPUESTO_SELECTIVO_CONSUMO</th>
            <th>OTROS_IMPUESTOS_TASAS</th>
            <th>MONTO_PROPINA_LEGAL</th>
            <th>FORMA_PAGO</th>
            <!-- <th>% Proporcionalidad</th>
            <th>SN - Razon Social o Nombre</th>
            <th>Nombre Tipo CYG</th>
            <th>Numero interno</th>
            <th>Codigo de IVA para recepcion de factura de impuestos</th>
            <th>Tipo de objeto NCF</th>
            <th>Nombre Tipo Ret</th> -->
		</tr>
            <?php if (!(empty($indice))) : ?>
                <?php for ($i=0; $i < count($indice); $i++) : ?>
                    <tr>
                        <td><?php echo $indice[$i]; ?></td>
                        <td><?php echo $rnc[$i]; ?></td>
                        <td><?php echo $tipo_ident[$i]; ?></td>
                        <td><?php echo $TIPO_BIENES[$i]; ?></td>
                        <td><?php echo $ncf[$i]; ?></td>
                        <td><?php echo $NUMERO_COMPROBANTE_MODIFICADO[$i]; ?></td>
                        <td><?php echo $fecha_comprobante[$i]; ?></td>
                        <td><?php echo $dia_comprobante[$i]; ?></td>
                        <td><?php echo $fecha_pago[$i]; ?></td>
                        <td><?php echo $dia_pago[$i]; ?></td>
                        <td><?php echo $MONTO_SERVICIOS[$i]; ?></td>
                        <td><?php echo $MONTO_BIENES[$i]; ?></td>
                        <td><?php echo $monto_facturado[$i]; ?></td>
                        <td><?php echo $itbis_facturado[$i]; ?></td>
                        <td><?php echo $ITBIS_RETENIDO[$i]; ?></td>
                        <td><?php echo $ITBIS_PROPORCIONALIDAD[$i]; ?></td>
                        <td><?php echo $ITBIS_LLEVADO_COSTO[$i]; ?></td>
                        <td><?php echo $ITBIS_ADELANTAR[$i]; ?></td>
                        <td><?php echo $ITBIS_PERCIBIDO[$i]; ?></td>
                        <td><?php echo $ISR_TIPO_RETENCION[$i]; ?></td>
                        <td><?php echo $ISR_RETENCION_RENTA[$i]; ?></td>
                        <td><?php echo $ISR_PERCIBIDO[$i]; ?></td>
                        <td><?php echo $IMPUESTO_SELECTIVO_CONSUMO[$i]; ?></td>
                        <td><?php echo $OTROS_IMPUESTOS_TASAS[$i]; ?></td>
                        <td><?php echo $MONTO_PROPINA_LEGAL[$i]; ?></td>
                        <td><?php echo $tipo_pago[$i]; ?></td>
                        <!-- <td><?php echo $Proporcionalidad[$i]; ?></td>
                        <td><?php echo $SN[$i]; ?></td>
                        <td><?php echo $TipoCyG[$i]; ?></td>
                        <td><?php echo $numero_interno[$i]; ?></td>
                        <td><?php echo $codigo_iva[$i]; ?></td>
                        <td><?php echo $TipoFact[$i]; ?></td>
                        <td><?php echo $tipo_ret[$i]; ?></td> -->
                    </tr>
                <?php endfor; ?>
            <?php endif; ?>
	</tbody>
</table>