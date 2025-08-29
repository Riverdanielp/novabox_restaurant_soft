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

	<section class="content-header px-0">
		<h3 class="top-left-header">Resumen Ventas </h3>
        <input type="hidden" class="datatable_name" data-title="Resumen Ventas" data-id_name="datatable">
	</section>

	<div class="box-wrapper">
		<div class="table-box">
			<div class="row">
				<div class="col-sm-12 col-md-9">
					<form action="" id="form1" method="post">

						<select name="tipo" id="tipo">
							<option value="all" selected>Todos</option>
							<?php foreach ($Tipos as $Tipo) : ?>

							<option value="<?php echo $Tipo->id ?>"
								<?php echo ($Tipo->id == $tipo_comprobante) ? 'selected' : '' ;?>>
								<?php echo $Tipo->prefijo ?> -
								<?php echo ($Tipo->nombre != 'Comprobante de Régimen especial de tributación') ? $Tipo->nombre : 'Régimen Especial'; ?>
							</option>
							<?php endforeach; ?>
						</select>
						<label for="fecha_ini">Desde</label>
						<input type="date" name="fecha_ini" id="fecha_ini"
							value="<?php echo (!empty($fecha_ini)) ? $fecha_ini : ""; ?>" required>
						<label for="fecha_fin">Hasta</label>
						<input type="date" name="fecha_fin" id="fecha_fin"
							value="<?php echo (!empty($fecha_fin)) ? $fecha_fin : date('Y-m-d'); ?>" required>

						<input type="hidden" name="buscar" value="buscar">
						<button class="btn btn-success btn-lg" name=""><i class="icon ti-search"></i> Filtrar</button>

					</form>
				</div>
				<div class="col-sm-12 col-md-3">
					<a href="<?php echo base_url() . 'facturas/listado_ventas'; ?>" class="btn btn-info btn-lg"
						name=""><i class="icon ti-search"></i>Ir a Filtro Ventas</a>
				</div>
			</div>
		</div>
	</div>
	<br>
	<div class="box-wrapper">
		<div class="table-box">
			<!-- /.box-header -->
			<div class="table-responsive">
				<table id="datatable" class="table table-responsive">
					<thead>
						<tr>
							<th class="ir_w_1">#</th>
							<th class="ir_w_3">ID venta</th>
							<th class="ir_w_2">Fecha</th>
							<th class="ir_w_8">RNC/Cédula</th>
							<th class="ir_w_8">NCF</th>
							<th class="ir_w_12">Tipo de NCF</th>
							<th class="ir_w_9">Vendido a</th>
							<th class="ir_w_9">Total parcial</th>
							<th class="ir_w_9">Totales</th>
							<th class="ir_w_9">Impuesto</th>
							<th class="ir_w_9">Tipo de pago</th>
						</tr>
					</thead>
					<tbody>

						<?php if (empty($Numeraciones)) : ?>
						<tr>
							<td></td>
							<td></td>
							<td colspan='1000'><span class='col-md-12 text-center text-info'>No hay Numeraciones activas
									disponibles para su visualización</span></td>
						</tr>
						<?php else : 
							$i = 0; ?>
						<?php foreach ($Numeraciones as $numeracion) : ?>
						<tr>
							<td><?php $i++;  echo $i; ?></td>
							<td><a href="<?php echo base_url() . 'Sale/print_invoice/' . $numeracion->sale_id ?>"
									target="_BLANK"><?php echo $numeracion->sale_id; ?></a> </td>
							<td><?php echo date('d/m/Y H:i:s',strtotime($numeracion->date_time)); ?></td>
							<td><?php echo $numeracion->rnc; ?></td>
							<td><a href="<?php echo base_url() . 'Sale/print_invoice/' . $numeracion->sale_id ?>"
									target="_BLANK"><?php echo $numeracion->Prefijo . rellenar_num($numeracion->numero); ?></a>
							</td>
							<td><?php echo $numeracion->Prefijo . ' - ' . $numeracion->Tipo; ?></td>
							<td><?php echo $numeracion->nombre; ?></td>
							<td><?php echo $numeracion->sub_total; ?></td>
							<td><?php echo $numeracion->total_payable; ?></td>
							<td><?php echo $numeracion->vat; ?></td>
							<td><?php echo $numeracion->TipoPago; ?></td>
						</tr>
						<?php endforeach; ?>
						<?php endif; ?>
					</tbody>

				</table>
			</div>
			<!-- /.box-body -->
		</div>
	</div>
	<?php 
	// echo '<pre>';
	// var_dump($Numeraciones);
	// echo '</pre>'; ?> <br><br><br>

	<?php //var_dump($_POST); ?>
</section>


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
