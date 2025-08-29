<!-- Main content -->
<section class="main-content-wrapper">
    <section class="content-header">
        <h3 class="top-left-header">
            Editar secuencia de facturación
        </h3>
    </section>

    <div class="box-wrapper">
        <div class="table-box">

            <?php echo form_open(base_url('facturas/update_numeracion')); ?>
            <input type="hidden" value="<?php echo $numeracion->id; ?>" name="id">
            <div>
                <div class="row">
                    <div class="col-sm-12 mb-2 col-md-4">
                        <div class="form-group">
                            <label for="tipo_doc">Tipo Documento:*</label>
                            <select name="tipo_doc" class="form-control form-inps" id="tipo_doc" required>
                            <?php foreach ($TipoDocumentos as $Tipo) : ?>
                                <?php $id_tipo_doc = !empty(set_value('tipo_doc')) ? set_value('tipo_doc') : $numeracion->tipo_doc ?>
                                <?php if($Tipo->id == $id_tipo_doc):?>
                                    <option value="<?php echo $Tipo->id ?>" selected><?php echo $Tipo->nombre ?></option>
                                <?php else:?>
                                    <option value="<?php echo $Tipo->id ?>"><?php echo $Tipo->nombre ?></option>
                                <?php endif;?>
                            <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-12 mb-2 col-md-4">
                        <div class="form-group">
                            <label for="tipo">Tipo Numeración:*</label>
							<select name="tipo" class="form-control form-inps" id="tipo" required>
								<option selected="selected">Seleccione un tipo</option> 
                                <?php foreach ($Tipos as $Tipo) : ?>
                                    <?php $id_tipo = !empty(set_value('tipo')) ? set_value('tipo') : $numeracion->tipo ?>
                                    <?php if($Tipo->id == $id_tipo):?>
                                        <option value="<?php echo $Tipo->id ?>" selected><?php echo $Tipo->prefijo ?> - <?php echo $Tipo->nombre ?></option>
                                    <?php else:?>
                                        <option value="<?php echo $Tipo->id ?>"><?php echo $Tipo->prefijo ?> - <?php echo $Tipo->nombre ?></option>
                                    <?php endif;?>
                                <?php endforeach; ?>
                                
							</select>
                        </div>
                    </div>
                    <div class="col-sm-12 mb-2 col-md-4">

                        <div class="form-group">
                            <label>Nombre Factura <span class="required_star">*</span></label>
                            <input tabindex="1" type="text" name="nombre" class="form-control"
                                placeholder="Nombre de la Factura para su identificación."
                                value="<?php echo !empty(set_value('nombre')) ? set_value('nombre') : $numeracion->nombre ?>" id="nombre" require>
                        </div>
                        <?php if (form_error('nombre')) { ?>
                        <div class="callout callout-danger my-2">
                            <?php echo form_error('nombre'); ?>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="col-sm-12 mb-2 col-md-4">

                        <div class="form-group">
                            <label>Número de inicio <span class="required_star">*</span></label>
                            <small></small>
                            <input tabindex="2" type="text" name="num_ini" class="form-control integerchk"
                                placeholder="Inicio de secuencia." value="<?php echo !empty(set_value('num_ini')) ? set_value('num_ini') : $numeracion->num_ini ?>">
                        </div>
                        <?php if (form_error('num_ini')) { ?>
                        <div class="callout callout-danger my-2">
                            <?php echo form_error('num_ini'); ?>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="col-sm-12 mb-2 col-md-4">

                        <div class="form-group">
                            <label>Número final <span class="required_star">*</span></label>
                            <small></small>
                            <input tabindex="2" type="text" name="num_fin" class="form-control integerchk"
                                placeholder="Fin de secuencia, debe ser mayor al número inicial." value="<?php echo !empty(set_value('num_fin')) ? set_value('num_fin') : $numeracion->num_fin ?>">
                        </div>
                        <?php if (form_error('num_fin')) { ?>
                        <div class="callout callout-danger my-2">
                            <?php echo form_error('num_fin'); ?>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="col-sm-12 mb-2 col-md-4">

                        <div class="form-group">
                            <label>Número siguiente <span class="required_star">*</span></label>
                            <small></small>
                            <input tabindex="2" type="text" name="num_sig" class="form-control integerchk"
                                placeholder="" value="<?php echo !empty(set_value('num_sig')) ? set_value('num_sig') : $numeracion->num_sig ?>">
                        </div>
                        <?php if (form_error('num_sig')) { ?>
                        <div class="callout callout-danger my-2">
                            <?php echo form_error('num_sig'); ?>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="col-sm-12 mb-2 col-md-4">
                        <div class="form-group">
                            <label>Fecha de Vencimiento</label>
                            <input tabindex="5" type="text" id="dates2" name="fecha_venc" class="form-control "
                                placeholder="Fecha vencimiento de la secuencia." value="<?php echo !empty(set_value('fecha_venc')) ? set_value('fecha_venc') :  $numeracion->fecha_venc ?>">
                        </div>

                    </div>

                    <div class="col-sm-12 mb-2 col-md-3">
                        <div class="form-group">
                            <label for="sucursal">Sucursal: <span class="required_star">*</span></label>
							<select name="sucursal" class="form-control form-inps" id="sucursal" required>
								<option value="0">Todas</option> 
                                <?php foreach ($sucursales as $sucursal) : ?>
                                    <?php $id_suc = !empty(set_value('sucursal')) ? set_value('sucursal') : $numeracion->sucursal ?>
                                    <?php if($sucursal->id == $id_suc):?>
                                        <option value="<?php echo $sucursal->id ?>" selected><?php echo $sucursal->outlet_name ?></option>
                                    <?php else: ?>
                                        <option value="<?php echo $sucursal->id ?>"><?php echo $sucursal->outlet_name ?></option>
                                    <?php endif;?>
                                <?php endforeach; ?>
                                
							</select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-sm-12 col-md-2 mb-2">
                    <button type="submit" name="submit" value="submit"
                        class="btn bg-blue-btn w-100"><?php echo lang('submit'); ?></button>
                </div>
                <div class="col-sm-12 col-md-2 mb-2">
                    <a class="btn bg-blue-btn w-100" href="<?php echo base_url() ?>facturas">
                        <?php echo lang('back'); ?>
                    </a>
                </div>
            </div>
            <?php echo form_close(); ?>

            <script>

                const txt_rnc = document.getElementsByTagName('form')[0];

                // Escuchamos el keydown y prevenimos el evento
                txt_rnc.addEventListener("keydown", (evento) => {
                    if (evento.key == "Enter") {
                        // Prevenir
                        evento.preventDefault();
                        return false;
                    }
                });

            </script>
        </div>
    </div>

</section>