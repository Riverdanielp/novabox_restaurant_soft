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
        <h1 class="top-left-header">
        Filtro de Ventas</h1>
    </section>
    <div class="box-wrapper">
        <div class="table-box">
            <div class="row">
                <div class="col-sm-12 col-md-7">
					<form action="" id="form1" method="post">
                        
                            <select name="tipo" id="tipo">
                                <option value="all" selected>Todos</option>
                                    <?php foreach ($Tipos as $Tipo) : ?>
                                        
                                        <option value="<?php echo $Tipo->id ?>" <?php echo ($Tipo->id == $tipo_comprobante) ? 'selected' : '' ;?> ><?php echo $Tipo->prefijo ?> - <?php echo ($Tipo->nombre != 'Comprobante de Régimen especial de tributación') ? $Tipo->nombre : 'Régimen Especial'; ?></option>
                                    <?php endforeach; ?>
                            </select>
                        <label for="fecha_ini">Desde</label>
						<input type="date" name="fecha_ini" id="fecha_ini" value="<?php echo (!empty($fecha_ini)) ? $fecha_ini : ""; ?>" required>
                        <label for="fecha_fin">Hasta</label>
						<input type="date" name="fecha_fin" id="fecha_fin" value="<?php echo (!empty($fecha_fin)) ? $fecha_fin : date('Y-m-d'); ?>" required>
                        
									<input type="hidden" name="buscar" value="buscar">
								<button class="btn btn-success btn-lg" name=""><i class="icon ti-search"></i> Filtrar</button>
							
					</form>
                </div>
                <div class="col-sm-12 col-md-5">
					<form action="" id="form1" method="post">
                        <a href="<?php echo base_url() . 'facturas/resumen_ventas'; ?>" class="btn btn-info btn-lg" name=""><i class="icon ti-search"></i>Ir a Resumen Ventas</a>
                        <label for="id_venta"> ID</label>
                                <input type="text" name="id_venta" id="id_venta" placeholder="Busqueda por ID" value="<?php echo (!empty($id_venta)) ? $id_venta : ""; ?>">
                        
                                <input type="hidden" name="busqueda_id" value="busqueda_id"  id="busqueda_id">
                                <button onclick="Busqueda_ID()" class="btn btn-primary btn-lg" name=""><i class="icon ti-search"></i> Buscar</button>
							
					</form>

                </div>
            </div>
        </div>
    </div>
        <br>
    <div class="row">
        <div class="col-sm-12 col-md-3">
            <div class="box-wrapper">
                <div class="table-box">
                    <table>
                        <?php if (!empty($Numeraciones)): ?>
                            <?php foreach($Numeraciones as $Num):?>
                                <tr>
                                    <th>
                                        <button class="btn_list w-100 m-right btn btn-primary" id="factura_<?php echo $Num->id; ?>" onclick="VerFactura('<?php echo $Num->sale_id; ?>')">
                                            <?php echo "<b>$Num->Prefijo</b>" . "<b>-$Num->numero</b> - <b>ID Venta:</b> $Num->sale_id ";
                                            echo ($Num->nombre != NULL && $Num->nombre != '' && $Num->nombre != '') ? "- <b>Cliente:</b> $Num->nombre " : '';
                                            echo ($Num->rnc != NULL && $Num->rnc != '' && $Num->rnc != '') ? "- <b>RNC:</b> $Num->rnc" : ''; ?>
                                        </button>
                                    </th>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <a class="list-group-item" ></i>Aún no se registraron facturas con esta numeración.</a>

                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-9">
            <div class="box-wrapper">
                <div class="table-box">
                    <div class="row">
                        <h3 class="top-left-header">Factura: </h3>
                        <iframe id="Factura_iframe" width="100%" height="25" frameborder="0"></iframe>
                        <!-- <iframe src="< ?php echo site_url("facturas/view_fact_iframe/1"); ?>" width="100%" height="600" frameborder="0"></iframe> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- <?php //var_dump($Numeraciones); ?> <br><br><br> -->
    
    <?php //var_dump($_POST); ?>
</section>
<script>
    var Factura_url = "<?php echo base_url() . "Sale/print_invoice/"; ?>";
    function VerFactura(id){

        let frame = document.getElementById("Factura_iframe");
        let enlace = Factura_url + id;
        //console.log(enlace);

        frame.height = '700';
        frame.src = enlace;
    };


</script>

<script>
	var busqueda_id = document.getElementById('busqueda_id').value;
	var id_venta = document.getElementById('id_venta').value;
    var Listado_url = "<?php echo base_url("facturas/listado_ventas/"); ?>";

	
    function Busqueda_ID(){
		const form2 = new FormData();
			form2.append('id', busqueda_id);
			form2.append('cliente_id', id_venta);
		//EnviarDatos(form2);
		
		var request = new XMLHttpRequest();
		request.open("POST", Listado_url);
		request.send(form2);
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
