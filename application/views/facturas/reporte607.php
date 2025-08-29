<section class="main-content-wrapper">
	<?php
        if ($this->session->flashdata('exception')) {

            echo '<section class="alert-wrapper">
            <div class="alert alert-success alert-dismissible fade show"> 
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <div class="alert-body">
            <p class="m-0"><i class="icon fa fa-check"></i>';
            echo escape_output($this->session->flashdata('exception'));
            echo '</p></div></div></section>';
        }
        $plusSVG= '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus me-50 font-small-4"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>';

    ?>

	<section class="content-header">
		<div class="row">
			<div class="col-sm-12 col-md-3">
				<h3 class="top-left-header">Reporte 607 </h3>
				<input type="hidden" class="datatable_name" data-title="Facturación" data-id_name="datatable">
			</div>
			<div class="col-sm-12 mb-2 col-md-6">
				<form action="" id="form1" method="post">
					<label for="fecha_ini">Desde</label>
					<input type="date" name="fecha_ini" id="fecha_ini"
						value="<?php echo date('Y-m-d',strtotime($fecha_ini)); ?>">
					<label for="fecha_fin">Hasta</label>
					<input type="date" name="fecha_fin" id="fecha_fin"
						value="<?php echo date('Y-m-d',strtotime($fecha_fin)); ?>">
					<a onclick="Reporte607()" class="btn btn-success hidden-sm hidden-xs"><i
							class="icon ti-receipt"></i> Reporte Compras: 607</a>

				</form>
			</div>
			<div class="col-sm-12 mb-2 col-md-3">
				<a onclick="TXT_Reporte607()" class="btn btn-primary"><i 
						class="icon ti-receipt"></i> Exportar a TXT</a>
				<a onclick="Excel_Reporte607()" class="btn btn-success"><i
						class="icon ti-receipt"></i> Exportar a Excel</a>
			</div>
		</div>
	</section>

	<div class="box-wrapper">
		<div class="table-box">
			<!-- /.box-header -->
			<div class="table-responsive">
				<table id="" class="table table-responsive">
					<thead>
						<tr>

							<th class="ir_w_1">#</th>
							<th class="ir_w_1">RNC_CEDULA</th>
							<th class="ir_w_1">TIPO_IDENTIFICACION</th>
							<th class="ir_w_1">NUMERO_COMPROBANTE_FISCAL</th>
							<th class="ir_w_1">NUMERO_COMPROBANTE_MODIFICADO</th>
							<th class="ir_w_1">TIPO_INGRESO</th>
							<th class="ir_w_1">FECHA_COMPROBANTE</th>
							<th class="ir_w_1">FECHA_RETENCION</th>
							<th class="ir_w_1">MONTO_FACTURADO</th>
							<th class="ir_w_1">ITBIS_FACTURADO</th>
							<th class="ir_w_1">ITBIS_RETENIDO</th>
							<th class="ir_w_1">ITBIS_PERCIBIDO</th>
							<th class="ir_w_1">ISR_RETENCION_RENTA</th>
							<th class="ir_w_1">ISR_PERCIBIDO</th>
							<th class="ir_w_1">IMPUESTO_SELECTIVO_CONSUMO</th>
							<th class="ir_w_1">OTROS_IMPUESTOS_TASAS</th>
							<th class="ir_w_1">MONTO_PROPINA_LEGAL</th>
							<th class="ir_w_1">EFECTIVO/DINERO</th>
							<th class="ir_w_1">CHEQUE/TRANFERENCIA/DEPOSITO</th>
							<th class="ir_w_1">TARJETA_DEBITO/CREDITO</th>
							<th class="ir_w_1">VENTA_CREDITO</th>
							<th class="ir_w_1">BONOS/REGALOS</th>
							<th class="ir_w_1">PERMUTA</th>
							<th class="ir_w_1">OTRAS_FORMAS</th>
							<th class="ir_w_1">SN - Razon Social o Nombre</th>
							<th class="ir_w_1">Numero Interno</th>
							
						</tr>
					</thead>
					
					<form action="" id="form607" method="post">
						<tbody>

							<?php if (empty($Numeraciones)) : ?>
							<tr>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td colspan='1000'><span class='col-md-12 text-center text-info'>No hay Numeraciones activas
										disponibles para su visualización</span></td>
							</tr>
							<?php else : ?>
								<?php $i = 1; ?>
								<input type="hidden" name="mes" value="<?php echo date('Ym',strtotime($fecha_fin)); ?>">
								<?php foreach ($Numeraciones as $numeracion) : ?>
									<?php //if (!($numeracion->id_Tipo == 2 && abs($numeracion->total_payable) < 250000)) : ?>
										<tr>
											<td><input type="hidden" name="indice[]" value="<?php echo $i; ?>"> <?php echo $i; $i++ ?></td> <!-- # -->
											<td> <input type="text" class="form-control form-inps" name="rnc[]" id="rnc" value="<?php echo ($numeracion->rnc == ' ') ? '' : $numeracion->rnc ; ?>"></td> <!-- RNC_CEDULA -->
											<td> <input type="text" class="form-control form-inps" name="tipo_ident[]" id="tipo_ident" value="<?php echo($numeracion->tipo_ident != NULL) ? $numeracion->tipo_ident : '2'; ?>"></td> <!-- TIPO_IDENTIFICACION -->
											<td> <input type="text" class="form-control form-inps" name="ncf[]" id="ncf" value="<?php echo $numeracion->Prefijo . rellenar_num($numeracion->numero); ?>"> </td> <!-- NUMERO_COMPROBANTE_FISCAL -->
											<td> <input type="text" class="form-control form-inps" name="NUMERO_COMPROBANTE_MODIFICADO[]" id="NUMERO_COMPROBANTE_MODIFICADO" value="<?php echo $numeracion->modificado; ?>"></td> <!-- NUMERO_COMPROBANTE_MODIFICADO -->
											<td> 
												<select name="TIPO_INGRESO[]" class="form-control form-inps" id="TIPO_INGRESO" required>
													<option value="" selected="selected">Seleccione un tipo</option>
														<?php foreach ($TipoIngresos as $Tipo) : ?>
															<?php if($Tipo->id == $numeracion->tipo_ingreso ):?>
																<option value="<?php echo $Tipo->id ?>" selected><?php echo $Tipo->numero ?> - <?php echo $Tipo->nombre ?></option>
															<?php else:?>
																<option value="<?php echo $Tipo->id ?>"><?php echo $Tipo->numero ?> - <?php echo $Tipo->nombre ?></option>
															<?php endif;?>
														<?php endforeach; ?>
												</select>
											</td> <!-- TIPO_INGRESO -->
											<td> <input type="text" class="form-control form-inps" name="fecha_comprobante[]" id="fecha_comprobante" value="<?php echo date('Ymd',strtotime($numeracion->fecha_comprobante)); ?>"> </td> <!-- FECHA_COMPROBANTE -->
											<td> <input type="text" class="form-control form-inps" name="fecha_retencion[]" id="fecha_retencion" placeholder="YYYYMMDD" value="<?php //if ($numeracion->fecha_pago != NULL){ echo date('Ym',strtotime($numeracion->fecha_pago));} ?>"> </td> <!-- FECHA_RETENCION -->

											<?php 
												$items = $this->Facturas_model->getItems_Venta($numeracion->sale_id);

												$ITBIS_FACTURADO = 0;
												$ITBIS_RETENIDO = 0; 
												$ITBIS_PERCIBIDO = 0;
												$ISR_TIPO_RETENCION = 0; 
												$ISR_RETENCION_RENTA = 0;
												$ISR_PERCIBIDO = 0;
												$IMPUESTO_SELECTIVO_CONSUMO = 0;
												$OTROS_IMPUESTOS_TASAS = 0;
												$MONTO_PROPINA_LEGAL = 0;

												// *** Calculos de Impuestos *** ///

												$MONTO_FACTURADO = $numeracion->total_payable - $numeracion->vat;
												
        											$taxes = json_decode($numeracion->sale_vat_objects); //
													
													foreach ($taxes as $tax){
														if ($tax->tax_field_type == 'ITBIS'){
															$ITBIS_FACTURADO = $ITBIS_FACTURADO + floatval($tax->tax_field_amount);
														}
														elseif ($tax->tax_field_type == '% Ley'){
															$MONTO_PROPINA_LEGAL = $MONTO_PROPINA_LEGAL + floatval($tax->tax_field_amount);
														}
														
													}
											?>
											<td> <input type="number"  step="0.01" class="form-control form-inps" name="monto_facturado[]" id="monto_facturado" value="<?php echo ($MONTO_FACTURADO != 0) ? number_format(abs($MONTO_FACTURADO), 2, '.', '') : ''; ?>"></td> <!-- MONTO_FACTURADO -->
											<td> <input type="number"  step="0.01" class="form-control form-inps" name="itbis_facturado[]" id="itbis_facturado" value="<?php echo ($ITBIS_FACTURADO != 0) ? number_format(abs($ITBIS_FACTURADO), 2, '.', '') : ''; ?>"></td> <!-- ITBIS_FACTURADO -->
											<td> <input type="number"  step="0.01" class="form-control form-inps" name="ITBIS_RETENIDO[]" id="ITBIS_RETENIDO" value="<?php echo ($ITBIS_RETENIDO != 0) ? number_format(abs($ITBIS_RETENIDO), 2, '.', '') : ''; ?>"></td> <!-- ITBIS_RETENIDO -->
											<td> <input type="number"  step="0.01" class="form-control form-inps" name="ITBIS_PERCIBIDO[]" id="ITBIS_PERCIBIDO" value="<?php echo ($ITBIS_PERCIBIDO != 0) ? number_format(abs($ITBIS_PERCIBIDO), 2, '.', '') : ''; ?>"></td> <!-- ITBIS_PERCIBIDO -->
											<td> <input type="number"  step="0.01" class="form-control form-inps" name="ISR_RETENCION_RENTA[]" id="ISR_RETENCION_RENTA" value="<?php echo ($ISR_RETENCION_RENTA != 0) ? number_format(abs($ISR_RETENCION_RENTA), 2, '.', '') : ''; ?>"></td> <!-- ISR_RETENCION_RENTA -->
											<td> <input type="number"  step="0.01" class="form-control form-inps" name="ISR_PERCIBIDO[]" id="ISR_PERCIBIDO" value="<?php echo ($ISR_PERCIBIDO != 0) ? number_format(abs($ISR_PERCIBIDO), 2, '.', '') : ''; ?>"></td> <!-- ISR_PERCIBIDO -->
											<td> <input type="number"  step="0.01" class="form-control form-inps" name="IMPUESTO_SELECTIVO_CONSUMO[]" id="IMPUESTO_SELECTIVO_CONSUMO" value="<?php echo ($IMPUESTO_SELECTIVO_CONSUMO != 0) ? number_format(abs($IMPUESTO_SELECTIVO_CONSUMO), 2, '.', '') : ''; ?>"></td> <!-- IMPUESTO_SELECTIVO_CONSUMO -->
											<td> <input type="number"  step="0.01" class="form-control form-inps" name="OTROS_IMPUESTOS_TASAS[]" id="OTROS_IMPUESTOS_TASAS" value="<?php echo ($OTROS_IMPUESTOS_TASAS != 0) ? number_format(abs($OTROS_IMPUESTOS_TASAS), 2, '.', '') : ''; ?>"></td> <!-- OTROS_IMPUESTOS_TASAS -->
											<td> <input type="number"  step="0.01" class="form-control form-inps" name="MONTO_PROPINA_LEGAL[]" id="MONTO_PROPINA_LEGAL" value="<?php echo ($MONTO_PROPINA_LEGAL != 0) ? number_format(abs($MONTO_PROPINA_LEGAL), 2, '.', '') : ''; ?>"></td> <!-- MONTO_PROPINA_LEGAL -->
											<?php 
											//$pagos = $this->factura->getPagosxFactura($numeracion->sale_id);
											//$pagos_venta = $this->factura->getPagosxFactura($numeracion->sale_id);
											$efectivo = 0;
											$credito = 0;
											$cheque = 0;
											$tarjeta = 0;
											$bono = 0;
											$permuta = 0;
											$otras = 0;

											//*** CALCULOS DE PAGOS ***//

											$credito = $numeracion->due_amount;

												if ($numeracion->TipoPago == 'Efectivo' || $numeracion->TipoPago == 'Cash' || $numeracion->TipoPago == 'Dinero'){
													$efectivo = $efectivo + $numeracion->paid_amount;
												} elseif ($numeracion->TipoPago == 'Cheque' || $numeracion->TipoPago == 'Check'){
													$cheque = $cheque + $numeracion->paid_amount;
												} elseif ($numeracion->TipoPago == 'Card' || $numeracion->TipoPago == 'Tarjeta de débito' || $numeracion->TipoPago == 'Tarjeta de crédito' || $numeracion->TipoPago == 'Credit Card' || $numeracion->TipoPago == 'Debit Card'){
													$tarjeta = $tarjeta + $numeracion->paid_amount;
												} elseif ($numeracion->TipoPago == 'Tarjeta de regalo' || $numeracion->TipoPago == 'Gift Card'){
													$bono = $bono + $numeracion->paid_amount;
												} else {
													$otras = $otras +$numeracion->paid_amount ;
												};
											?>
											<td> <input type="number"  step="0.01" class="form-control form-inps" name="EFECTIVO[]" id="EFECTIVO" value="<?php echo ($efectivo != 0) ? number_format(abs($efectivo), 2, '.', '') : ''; ?>"></td> <!-- EFECTIVO -->
											<td> <input type="number"  step="0.01" class="form-control form-inps" name="CHEQUE[]" id="CHEQUE" value="<?php echo ($cheque != 0) ? number_format(abs($cheque), 2, '.', '') : ''; ?>"></td> <!-- CHEQUE/TRANFERENCIA/DEPOSITO -->
											<td> <input type="number"  step="0.01" class="form-control form-inps" name="TARJETA[]" id="TARJETA" value="<?php echo ($tarjeta != 0) ? number_format(abs($tarjeta), 2, '.', '') : ''; ?>"></td> <!-- TARJETA_DEBITO/CREDITO -->
											<td> <input type="number"  step="0.01" class="form-control form-inps" name="CREDITO[]" id="CREDITO" value="<?php echo ($credito != 0) ? number_format(abs($credito), 2, '.', '') : ''; ?>"></td> <!-- VENTA_CREDITO -->
											<td> <input type="number"  step="0.01" class="form-control form-inps" name="BONOS[]" id="BONOS" value="<?php echo ($bono != 0) ? number_format(abs($bono), 2, '.', '') : ''; ?>"></td> <!-- BONOS -->
											<td> <input type="number"  step="0.01" class="form-control form-inps" name="PERMUTA[]" id="PERMUTA" value="<?php echo ($permuta != 0) ? number_format(abs($permuta), 2, '.', '') : ''; ?>"></td> <!-- PERMUTA -->
											<td> <input type="number"  step="0.01" class="form-control form-inps" name="OTRAS_FORMAS[]" id="OTRAS_FORMAS" value="<?php echo ($otras != 0) ? number_format(abs($otras), 2, '.', '') : ''; ?>"></td> <!-- OTRAS_FORMAS -->
											
											<td> <input type="text" class="form-control form-inps" name="SN[]" id="SN" value="<?php echo $numeracion->nombre; ?>"></td> <!-- SN -->
											<td><input type="hidden" name="numero_interno[]" value="<?php echo $numeracion->sale_id; ?>"> 0<?php echo $numeracion->sale_id ?></td> <!-- Número interno -->
										
										</tr>
									<?php //endif; ?>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</form>

				</table>
			</div>
			<!-- /.box-body -->
		</div>
	</div>
	<?php 
		// echo '<pre>';
		// var_dump($Numeraciones);
		// echo '</pre>';
	?>
</section>

<script>
	function Reporte606() {
		//console.log(id);
		{
			let fecha_ini = document.getElementById('fecha_ini').value;
			let fecha_fin = document.getElementById('fecha_fin').value;

			if (fecha_ini == '' || fecha_fin == '') {
				alert('Completa la fecha para ver el reporte!')
			} else {
				document.getElementById('form1').action = '<?php echo base_url("facturas/reporte606/"); ?>';
				document.getElementById('form1').submit();
			}
		}
	}

	function Reporte607() {
		//console.log(id);
		{
			let fecha_ini = document.getElementById('fecha_ini').value;
			let fecha_fin = document.getElementById('fecha_fin').value;

			if (fecha_ini == '' || fecha_fin == '') {
				alert('Completa la fecha para ver el reporte!')
			} else {
				document.getElementById('form1').action = '<?php echo base_url("facturas/reporte607/"); ?>';
				document.getElementById('form1').submit();
			}
		}
	}
	
	function TXT_Reporte607() {
		//console.log(id);
		{
			document.getElementById('form607').action = '<?php echo base_url("facturas/txt_607/"); ?>';
			document.getElementById('form607').submit();
		}
	}


	function Excel_Reporte607() {
		document.getElementById('form607').action = '<?php echo base_url("facturas/excel607/"); ?>';
		document.getElementById('form607').submit();
	}

</script>

<script>
	// // creamos un evento doble click para cada una de las celdas de la tabla
	// const tds=document.querySelectorAll("td");
	// for(td of tds) {
	// td.addEventListener("dblclick",function() {
	
	// // creamos un nuevo input con el valor actual de la celda
	// let input=document.createElement('input');
	// input.value=this.textContent;
	
	// // evento que se ejecuta cuando el input pierde el foco
	// input.addEventListener("blur",function() {
	// removeInput(this);
	// });
	
	// // evento que se ejecuta cada vez que se deja de pulsar una tecla
	// input.addEventListener("keydown",function(e) {
	
	// // la tecla 13, es el Enter
	// if(e.which==13) {
	// removeInput(this);
	// }
	// });
	
	// // quitamos el contenido de la celda de la tabla
	// this.textContent="";
	
	// // Ponemos en la celda el input que hemos creado
	// this.appendChild(input);
	// });
	// }
	
	// // Eliminamos el input y ponemos el valor del mismo
	// function removeInput(e) {
	// e.parentElement.textContent=e.value;
	//}
</script>

<script src="<?php echo base_url(); ?>frequent_changing/js/inventory.js"></script>

<!-- DataTables -->
<script src="<?php echo base_url(); ?>assets/datatable_custom/jquery-3.3.1.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/jquery.dataTables.min.js"></script>
<script src="<?php echo base_url(); ?>assets/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js">
</script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/dataTables.bootstrap4.min.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/dataTables.buttons.min.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/buttons.html5.min.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/buttons.print.min.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/jszip.min.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/pdfmake.min.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/vfs_fonts.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/buttons.colVis.min.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/newDesign/js/forTable.js"></script>



<script src="<?php echo base_url(); ?>frequent_changing/js/custom_report.js"></script>
