


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
                    <h3 class="top-left-header">Facturación </h3>
                    <input type="hidden" class="datatable_name" data-title="Facturación" data-id_name="datatable">
                </div>
                <div class="col-sm-12 mb-2 col-md-9">
					<form action="" id="form1" method="post">
                        <label for="fecha_ini">Desde</label>
						<input type="date" name="fecha_ini" id="fecha_ini" value="">
                        <label for="fecha_fin">Hasta</label>
						<input type="date" name="fecha_fin" id="fecha_fin" value="<?php echo date('Y-m-d'); ?>">
                        <a onclick="Reporte606()" class="btn btn-success"><i class="icon ti-receipt"></i> Reporte Compras: 606</a>
                
                        <a onclick="Reporte607()" class="btn btn-success"><i class="icon ti-receipt"></i> Reporte Ventas: 607</a>

                        <a href="<?php echo base_url() . "facturas/listado_ventas" ?>" class="btn btn-primary"><i class="icon ti-receipt"></i> Historial de Ventas</a>
							
					</form>
                </div>
                <div class="col-sm-12 col-md-4">
                    <a class="btn_list m-right btn btn-primary" href="<?php echo base_url() ?>facturas/inactivos">
                         Listado numeraciones inactivas
                    </a>
                    <a class="btn_list m-right btn bg-blue-btn" href="<?php echo base_url() ?>facturas/add_numeracion">
                        <?php echo $plusSVG?> Nueva Numeración
                    </a>
                </div>
            </div>
        </section>

     <div class="box-wrapper">
        <div class="table-box">
                 <!-- /.box-header -->
                 <div class="table-responsive">
                     <table id="datatable" class="table table-responsive">
                         <thead>
                             <tr>
                                 <th class="ir_w_1">ID</th>
                                 <th class="ir_w_11">Tipo</th>
                                 <th class="ir_w_8">Sucursal</th>
                                 <th class="ir_w_18">Nombre</th>
                                 <th class="ir_w_12">Vigencia Hasta</th>
                                 <th class="ir_w_9">Prefijo</th>
                                 <th class="ir_w_9">Secuencia</th>
                                 <th class="ir_w_9">Sig. Número</th>
                                 <th class="ir_w5_txt_center not-export-col">Acciones</th>
                             </tr>
                         </thead>
                         <tbody>
                             
								<?php if (empty($Numeraciones)) : ?>
									<tr>
                                        <td></td>
                                        <td></td>
										<td colspan='1000'><span class='col-md-12 text-center text-info' >No hay Numeraciones activas disponibles para su visualización</span></td>
									</tr>
                                <?php else : ?>
									<?php foreach ($Numeraciones as $numeracion) : ?>
									<tr>
										<td><a class="" href="<?php echo base_url() . 'facturas/view_numeracion/' . $numeracion->id; ?>"><?php echo $numeracion->id; ?></a></td>
										<td><?php echo $numeracion->Tipo; ?></td>
										<td><?php echo $numeracion->Sucursal; ?></td>
										<td><a class="" href="<?php echo base_url() . 'facturas/view_numeracion/' . $numeracion->id; ?>"><?php echo $numeracion->nombre; ?></a></td>
										<td>
											<?php if ($numeracion->fecha_venc != NULL) {
											echo date("d/m/Y", strtotime($numeracion->fecha_venc));
											}; ?>
										</td>
										<td>
											<?php echo $numeracion->prefijo; ?>
										</td>
										<td>
											<a class="" href="<?php echo base_url() . 'facturas/view_numeracion/' . $numeracion->id; ?>"><?php echo $numeracion->num_ini ;
												if ($numeracion->num_fin != NULL) { echo " - " . $numeracion->num_fin; } ?></a>
										</td>
										<td>
											<a class="" href="<?php echo base_url() . 'facturas/view_numeracion/' . $numeracion->id; ?>"><?php echo $numeracion->num_sig; ?></a>
										</td>
                                        
										<td class="ir_txt_center">
											<div class="btn-group actionDropDownBtn">
												<button type="button" class="btn bg-blue-color dropdown-toggle" id="dropdownMenuButton1"
													data-bs-toggle="dropdown" aria-expanded="false">
													<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
														stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
														class="feather feather-more-vertical">
														<circle cx="12" cy="12" r="1"></circle>
														<circle cx="12" cy="5" r="1"></circle>
														<circle cx="12" cy="19" r="1"></circle>
													</svg>
												</button>
												<ul class="dropdown-menu dropdown-menu-lg-end" aria-labelledby="dropdownMenuButton1" role="menu">
													<li><a href="<?php echo base_url() . 'facturas/view_numeracion/' . $numeracion->id; ?>" class="ir_mouse_pointer"><i
																class="fa fa-eye tiny-icon"></i>Ver Listado</a>
													</li>
													<li><a href="<?php echo base_url() . 'facturas/edit_numeracion/' . $numeracion->id; ?>" class="ir_mouse_pointer"><i
																class="fa fa-edit tiny-icon"></i>Editar</a>
													</li>
													<li><a href="<?php echo base_url() . 'facturas/desactivar_numeracion/' . $numeracion->id; ?>"  class="delete"><i
																class="fa fa-trash tiny-icon"></i>Desactivar</a>
													</li>
												</ul>
											</div>
										</td>
									</tr>
									<?php endforeach; ?>
								<?php endif; ?>
                         </tbody>
                         
                     </table>
                 </div>
                 <!-- /.box-body -->
        </div>
     </div>
 </section>
 
<script>
	function Reporte606(){
		//console.log(id);
		{
			let fecha_ini = document.getElementById('fecha_ini').value;
			let fecha_fin = document.getElementById('fecha_fin').value;

			if (fecha_ini == '' || fecha_fin == ''){
				alert('Completa la fecha para ver el reporte!')
			} else {
				document.getElementById('form1').action = '<?php echo base_url("facturas/reporte606/"); ?>';
				document.getElementById('form1').submit();
			}
		}
	}
	
	function Reporte607(){
		//console.log(id);
		{
			let fecha_ini = document.getElementById('fecha_ini').value;
			let fecha_fin = document.getElementById('fecha_fin').value;

			if (fecha_ini == '' || fecha_fin == ''){
				alert('Completa la fecha para ver el reporte!')
			} else {
				document.getElementById('form1').action = '<?php echo base_url("facturas/reporte607/"); ?>';
				document.getElementById('form1').submit();
			}
		}
	}

	async function EnviarDatosReporte606(form){
			//const formulario = new FormData(document.getElementById('num_form'));
			const EnlaceJson = '<?php echo site_url("facturas/reporte606/"); ?>';
			
	}
</script>

 <script src="<?php echo base_url(); ?>frequent_changing/js/inventory.js"></script>
 
 <!-- DataTables -->
<script src="<?php echo base_url(); ?>assets/datatable_custom/jquery-3.3.1.js"></script>
<script src="<?php echo base_url(); ?>frequent_changing/js/dataTable/jquery.dataTables.min.js"></script>
<script src="<?php echo base_url(); ?>assets/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
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

