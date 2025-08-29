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
				<h3 class="top-left-header">Reporte 606 </h3>
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
					<a onclick="Reporte606()" class="btn btn-success"><i
							class="icon ti-receipt"></i> Reporte Compras: 606</a>

				</form>
			</div>
			<div class="col-sm-12 mb-2 col-md-3">
				<a onclick="TXT_Reporte606()" class="btn btn-primary"><i 
						class="icon ti-receipt"></i> Exportar a TXT</a>
				<a onclick="Excel_Reporte606()" class="btn btn-success"><i
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
							<th class="ir_w_1">TIPO_BIENES_SERVICIOS_COMPRADOS</th>
							<th class="ir_w_1">NUMERO_COMPROBANTE_FISCAL</th>
							<th class="ir_w_1">NUMERO_COMPROBANTE_MODIFICADO</th>
							<th class="ir_w_1">FECHA_COMPROBANTE</th>
							<th class="ir_w_1">DIA_COMPROBANTE</th>
							<th class="ir_w_1">FECHA_PAGO</th>
							<th class="ir_w_1">DIA_PAGO</th>
							<th class="ir_w_1">MONTO_FACTURADO_SERVICIOS</th>
							<th class="ir_w_1">MONTO_FACTURADO_BIENES</th>
							<th class="ir_w_1">MONTO_FACTURADO</th>
							<th class="ir_w_1">ITBIS_FACTURADO</th>
							<th class="ir_w_1">ITBIS_RETENIDO</th>
							<th class="ir_w_1">ITBIS_PROPORCIONALIDAD</th>
							<th class="ir_w_1">ITBIS_LLEVADO_COSTO</th>
							<th class="ir_w_1">ITBIS_ADELANTAR</th>
							<th class="ir_w_1">ITBIS_PERCIBIDO</th>
							<th class="ir_w_1">ISR_TIPO_RETENCION</th>
							<th class="ir_w_1">ISR_RETENCION_RENTA</th>
							<th class="ir_w_1">ISR_PERCIBIDO</th>
							<th class="ir_w_1">IMPUESTO_SELECTIVO_CONSUMO</th>
							<th class="ir_w_1">OTROS_IMPUESTOS_TASAS</th>
							<th class="ir_w_1">MONTO_PROPINA_LEGAL</th>
							<th class="ir_w_1">FORMA_PAGO</th>
							<th class="ir_w_1">% Proporcionalidad</th>
							<th class="ir_w_1">SN - Razon Social o Nombre</th>
							<th class="ir_w_1">Nombre Tipo CYG</th>
							<th class="ir_w_1">Número interno</th>
							<th class="ir_w_1">Código de IVA para recepción de factura de impuestos</th>
							<th class="ir_w_1">Tipo de objeto NCF</th>
							<th class="ir_w_1">Nombre Tipo Ret</th>
						</tr>
					</thead>
					
					<form action="" id="form606" method="post">
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
								<?php if (!($numeracion->nacionalidad == 1)) : ?>
								<tr>
									<td><input type="hidden" name="indice[]" value="<?php echo $i; ?>">
										<?php echo $i; $i++ ?></td> <!-- # -->
									<td> <input type="text" class="form-control form-inps" name="rnc[]" id="rnc"
											value="<?php echo ($numeracion->rnc == ' ') ? '' : $numeracion->rnc ; ?>"></td>
									<!-- RNC_CEDULA -->
									<td> <input type="text" class="form-control form-inps" name="tipo_ident[]"
											id="tipo_ident" value="<?php echo $numeracion->tipo_ident; ?>"></td>
									<!-- TIPO_IDENTIFICACION -->

									<td>
										<select name="TIPO_BIENES[]" class="form-control form-inps" id="TIPO_BIENES"
											required>
											<option value="" selected="selected">Seleccione un tipo</option>
											<?php foreach ($TipoCyG as $Tipo) : ?>
											<?php if($Tipo->id == $numeracion->tipo_cyg ):?>
											<option value="<?php echo ($Tipo->id < 10) ? '0' . $Tipo->id : $Tipo->id ?>"
												selected><?php echo $Tipo->numero ?> - <?php echo $Tipo->nombre ?></option>
											<?php else:?>
											<option value="<?php echo ($Tipo->id < 10) ? '0' . $Tipo->id : $Tipo->id ?>">
												<?php echo $Tipo->numero ?> - <?php echo $Tipo->nombre ?></option>
											<?php endif;?>
											<?php endforeach; ?>
										</select>
									</td> <!-- TIPO_BIENES_SERVICIOS_COMPRADOS -->
									<td> <input type="text" class="form-control form-inps" name="ncf[]" id="ncf"
											value="<?php echo $numeracion->ncf; ?>"> </td>
									<!-- NUMERO_COMPROBANTE_FISCAL -->
									<td> <input type="text" class="form-control form-inps"
											name="NUMERO_COMPROBANTE_MODIFICADO[]" id="NUMERO_COMPROBANTE_MODIFICADO"></td>
									<!-- NUMERO_COMPROBANTE_MODIFICADO -->
									<td> <input type="text" class="form-control form-inps" name="fecha_comprobante[]"
											id="fecha_comprobante"
											value="<?php echo date('Ym',strtotime($numeracion->fecha_comprobante)); ?>">
									</td> <!-- FECHA_COMPROBANTE -->
									<td> <input type="text" class="form-control form-inps" name="dia_comprobante[]"
											id="dia_comprobante"
											value="<?php echo date('d',strtotime($numeracion->fecha_comprobante)); ?>">
									</td> <!-- DIA_COMPROBANTE -->
									<td> <input type="text" class="form-control form-inps" name="fecha_pago[]"
											id="fecha_pago"
											value="<?php if ($numeracion->fecha_comprobante != NULL){ echo date('Ym',strtotime($numeracion->fecha_comprobante));} ?>">
									</td> <!-- FECHA_PAGO -->
									<td> <input type="text" class="form-control form-inps" name="dia_pago[]" id="dia_pago"
											value="<?php if ($numeracion->fecha_comprobante != NULL){ echo date('d',strtotime($numeracion->fecha_comprobante));} ?>">
									</td> <!-- DIA_PAGO -->
									<?php 
											$MONTO_SERVICIOS = 0; 
											$MONTO_BIENES = 0;
											$ITBIS_FACTURADO = 0;
											$ITBIS_RETENIDO = 0; 
											$ITBIS_PROPORCIONALIDAD = 0;
											$ITBIS_LLEVADO_COSTO = 0;
											$ITBIS_ADELANTAR = 0;
											$ITBIS_PERCIBIDO = 0;
											$ISR_TIPO_RETENCION = 0; 
											$ISR_RETENCION_RENTA = 0;
											$ISR_PERCIBIDO = 0;
											$IMPUESTO_SELECTIVO_CONSUMO = 0;
											$OTROS_IMPUESTOS_TASAS = 0;
											$MONTO_PROPINA_LEGAL = 0;
											
											
											$MONTO_BIENES = $numeracion->grand_total;
											$ITBIS_FACTURADO = $numeracion->itbis;
											?>
									<td> <input type="number" step="0.01" class="form-control form-inps"
											name="MONTO_SERVICIOS[]" id="MONTO_SERVICIOS"
											value="<?php echo ($MONTO_SERVICIOS != 0) ? number_format($MONTO_SERVICIOS, 2, '.', '') : ''; ?>">
									</td> <!-- MONTO_FACTURADO_SERVICIOS -->
									<td> <input type="number" step="0.01" class="form-control form-inps"
											name="MONTO_BIENES[]" id="MONTO_BIENES"
											value="<?php echo ($MONTO_BIENES != 0) ? number_format($MONTO_BIENES, 2, '.', '') : ''; ?>">
									</td> <!-- MONTO_FACTURADO_BIENES -->
									<td> <input type="number" step="0.01" class="form-control form-inps"
											name="monto_facturado[]" id="monto_facturado"
											value="<?php echo ($numeracion->grand_total != 0) ? number_format($numeracion->grand_total, 2, '.', '') : ''; ?>">
									</td> <!-- MONTO_FACTURADO -->
									<td> <input type="number" step="0.01" class="form-control form-inps"
											name="itbis_facturado[]" id="itbis_facturado"
											value="<?php echo ($numeracion->itbis != 0) ? number_format($numeracion->itbis, 2, '.', '') : ''; ?>">
									</td> <!-- ITBIS_FACTURADO -->
									<td> <input type="number" step="0.01" class="form-control form-inps"
											name="ITBIS_RETENIDO[]" id="ITBIS_RETENIDO"
											value="<?php echo ($ITBIS_RETENIDO != 0) ? number_format($ITBIS_RETENIDO, 2, '.', '') : ''; ?>">
									</td> <!-- ITBIS_RETENIDO -->
									<td> <input type="number" step="0.01" class="form-control form-inps"
											name="ITBIS_PROPORCIONALIDAD[]" id="ITBIS_PROPORCIONALIDAD"
											value="<?php echo ($ITBIS_PROPORCIONALIDAD != 0) ? number_format($ITBIS_PROPORCIONALIDAD, 2, '.', '') : ''; ?>">
									</td> <!-- ITBIS_PROPORCIONALIDAD -->
									<td> <input type="number" step="0.01" class="form-control form-inps"
											name="ITBIS_LLEVADO_COSTO[]" id="ITBIS_LLEVADO_COSTO"
											value="<?php echo ($ITBIS_LLEVADO_COSTO != 0) ? number_format($ITBIS_LLEVADO_COSTO, 2, '.', '') : ''; ?>">
									</td> <!-- ITBIS_LLEVADO_COSTO -->
									<td> <input type="number" step="0.01" class="form-control form-inps"
											name="ITBIS_ADELANTAR[]" id="ITBIS_ADELANTAR"
											value="<?php echo ($numeracion->itbis != 0) ? number_format($numeracion->itbis, 2, '.', '') : ''; ?>">
									</td> <!-- ITBIS_ADELANTAR -->
									<td> <input type="number" step="0.01" class="form-control form-inps"
											name="ITBIS_PERCIBIDO[]" id="ITBIS_PERCIBIDO"
											value="<?php echo ($ITBIS_PERCIBIDO != 0) ? number_format($ITBIS_PERCIBIDO, 2, '.', '') : ''; ?>">
									</td> <!-- ITBIS_PERCIBIDO -->
									<td> <input type="number" step="0.01" class="form-control form-inps"
											name="ISR_TIPO_RETENCION[]" id="ISR_TIPO_RETENCION"
											value="<?php echo ($ISR_TIPO_RETENCION != 0) ? number_format($ISR_TIPO_RETENCION, 2, '.', '') : ''; ?>">
									</td> <!-- ISR_TIPO_RETENCION -->
									<td> <input type="number" step="0.01" class="form-control form-inps"
											name="ISR_RETENCION_RENTA[]" id="ISR_RETENCION_RENTA"
											value="<?php echo ($ISR_RETENCION_RENTA != 0) ? number_format($ISR_RETENCION_RENTA, 2, '.', '') : ''; ?>">
									</td> <!-- ISR_RETENCION_RENTA -->
									<td> <input type="number" step="0.01" class="form-control form-inps"
											name="ISR_PERCIBIDO[]" id="ISR_PERCIBIDO"
											value="<?php echo ($ISR_PERCIBIDO != 0) ? number_format($ISR_PERCIBIDO, 2, '.', '') : ''; ?>">
									</td> <!-- ISR_PERCIBIDO -->
									<td> <input type="number" step="0.01" class="form-control form-inps"
											name="IMPUESTO_SELECTIVO_CONSUMO[]" id="IMPUESTO_SELECTIVO_CONSUMO"
											value="<?php echo ($IMPUESTO_SELECTIVO_CONSUMO != 0) ? number_format($IMPUESTO_SELECTIVO_CONSUMO, 2, '.', '') : ''; ?>">
									</td> <!-- IMPUESTO_SELECTIVO_CONSUMO -->
									<td> <input type="number" step="0.01" class="form-control form-inps"
											name="OTROS_IMPUESTOS_TASAS[]" id="OTROS_IMPUESTOS_TASAS"
											value="<?php echo ($OTROS_IMPUESTOS_TASAS != 0) ? number_format($OTROS_IMPUESTOS_TASAS, 2, '.', '') : ''; ?>">
									</td> <!-- OTROS_IMPUESTOS_TASAS -->
									<td> <input type="number" step="0.01" class="form-control form-inps"
											name="MONTO_PROPINA_LEGAL[]" id="MONTO_PROPINA_LEGAL"
											value="<?php echo ($MONTO_PROPINA_LEGAL != 0) ? number_format($MONTO_PROPINA_LEGAL, 2, '.', '') : ''; ?>">
									</td> <!-- MONTO_PROPINA_LEGAL -->
									<td>
										<select name="tipo_pago[]" class="form-control form-inps" id="tipo_pago" required>
											<option value="" selected="selected">Seleccione un tipo</option>
											<?php foreach ($TipoPago as $Tipo) : ?>
											<?php if($Tipo->id == $numeracion->tipo_pago ):?>
											<option value="<?php echo ($Tipo->id < 10) ? '0' . $Tipo->id : $Tipo->id ?>"
												selected><?php echo $Tipo->numero ?> - <?php echo $Tipo->nombre ?></option>
											<?php else:?>
											<option value="<?php echo ($Tipo->id < 10) ? '0' . $Tipo->id : $Tipo->id ?>">
												<?php echo $Tipo->numero ?> - <?php echo $Tipo->nombre ?></option>
											<?php endif;?>
											<?php endforeach; ?>
										</select>
									</td> <!-- FORMA_PAGO -->
									<td> <input type="number" step="0.01" class="form-control form-inps"
											name="Proporcionalidad[]" id="Proporcionalidad"
											value="<?php echo number_format(0, 2, '.', ''); ?>"></td>
									<!-- Proporcionalidad -->
									<td> <input type="text" class="form-control form-inps" name="SN[]" id="SN"
											value="<?php echo $numeracion->nombre; ?>"></td> <!-- SN -->
									<td>
										<select name="TipoCyG[]" class="form-control form-inps" id="TipoCyG" required>
											<option value="" selected="selected">Seleccione un tipo</option>
											<?php foreach ($TipoCyG as $Tipo) : ?>
											<?php if($Tipo->id == $numeracion->tipo_cyg ):?>
											<option value="<?php echo $Tipo->nombre ?>" selected><?php echo $Tipo->numero ?>
												- <?php echo $Tipo->nombre ?></option>
											<?php else:?>
											<option value="<?php echo $Tipo->nombre ?>"><?php echo $Tipo->numero ?> -
												<?php echo $Tipo->nombre ?></option>
											<?php endif;?>
											<?php endforeach; ?>
										</select>
									</td> <!-- Nombre Tipo CYG -->
									<td><input type="hidden" name="numero_interno[]"
											value="<?php echo $numeracion->compra_id; ?>">
										0<?php echo $numeracion->compra_id ?></td> <!-- Número interno -->
									<td> <input type="text" class="form-control form-inps" name="codigo_iva[]"
											id="codigo_iva" value="--"> </td>
									<!-- Código de IVA para recepción de factura de impuestos -->
									<td>
										<select name="TipoFact[]" class="form-control form-inps" id="TipoFact" required>
											<?php foreach ($Tipos as $Tipo) : ?>
											<?php if($Tipo->id == $numeracion->numeracion_tipo ):?>
											<option value="<?php echo $Tipo->id ?>" selected><?php echo $Tipo->tipo_nro ?> -
												<?php echo $Tipo->nombre ?></option>
											<?php else:?>
											<option value="<?php echo $Tipo->id ?>"><?php echo $Tipo->tipo_nro ?> -
												<?php echo $Tipo->nombre ?></option>
											<?php endif;?>
											<?php endforeach; ?>
										</select>
									</td> <!-- Tipo de objeto NCF -->
									<td> <input type="text" class="form-control form-inps" name="tipo_ret[]" id="tipo_ret"
											value="--"> </td> <!-- Nombre Tipo Ret -->
								</tr>
								<?php endif; ?>
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

	function Excel_Reporte606() {
		//console.log(id);
		{
			document.getElementById('form606').action = '<?php echo site_url("facturas/excel606/"); ?>';
			document.getElementById('form606').submit();
		}
	}

	function TXT_Reporte606() {
		//console.log(id);
		{
			document.getElementById('form606').action = '<?php echo base_url() ."facturas/txt_606/"; ?>';
			document.getElementById('form606').submit();
		}
	}
</script>

<script>
	// creamos un evento doble click para cada una de las celdas de la tabla
	const tds = document.querySelectorAll("td");
	for (td of tds) {
		td.addEventListener("dblclick", function () {

			// creamos un nuevo input con el valor actual de la celda
			let input = document.createElement('input');
			input.value = this.textContent;

			// evento que se ejecuta cuando el input pierde el foco
			input.addEventListener("blur", function () {
				removeInput(this);
			});

			// evento que se ejecuta cada vez que se deja de pulsar una tecla
			input.addEventListener("keydown", function (e) {

				// la tecla 13, es el Enter
				if (e.which == 13) {
					removeInput(this);
				}
			});

			// quitamos el contenido de la celda de la tabla
			this.textContent = "";

			// Ponemos en la celda el input que hemos creado
			this.appendChild(input);
		});
	}

	// Eliminamos el input y ponemos el valor del mismo
	function removeInput(e) {
		e.parentElement.textContent = e.value;
	}
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
