<link rel="stylesheet" href="<?= base_url() ?>frequent_changing/css/custom_check_box.css">
<!-- Main content -->
<section class="main-content-wrapper">

    <?php
    if ($this->session->flashdata('exception')) {

        echo '<section class="alert-wrapper"><div class="alert alert-success alert-dismissible fade show"> 
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <div class="alert-body"><p><i class="m-right fa fa-check"></i>';
        echo escape_output($this->session->flashdata('exception'));unset($_SESSION['exception']);
        echo '</p></div></div></section>';
    }
    ?>
    <section class="content-header">
        <h3 class="top-left-header">
            <?php
            $data_c = getLanguageManifesto();
            if(str_rot13($data_c[0])=="eriutoeri"){
                echo lang('edit_outlet');
            }else if(str_rot13($data_c[0])=="fgjgldkfg"){
                echo lang('outlet_setting');
            }
            ?>
        </h3>

    </section>



    <div class="box-wrapper">
        <div class="table-box">
            <!-- /.box-header -->
            <!-- form start -->
            <?php echo form_open(base_url('Outlet/addEditOutlet/' . $encrypted_id)); ?>
            <div class="box-body">
                <div class="row">
                    <?php
                    if(str_rot13($data_c[0])=="eriutoeri") {
                        ?>
                        <div class="col-sm-12 mb-2 col-md-3">
                            <div class="form-group">
                                <label><?php echo lang('outlet_code'); ?> <span
                                            class="required_star">*</span></label>
                                <input tabindex="1" autocomplete="off" type="text" name="outlet_code"
                                        class="form-control" onfocus="select();"
                                        placeholder="<?php echo lang('outlet_code'); ?>"
                                        value="<?php echo escape_output($outlet_information->outlet_code) ?>"/>
                            </div>
                            <?php if (form_error('outlet_code')) { ?>
                                <div class="callout callout-danger my-2">
                                    <?php echo form_error('outlet_code'); ?>
                                </div>
                            <?php } ?>
                        </div>
                        <?php
                    }
                    ?>
                    <div class="col-sm-12 mb-2 col-md-3">
                        <div class="form-group">
                            <label><?php echo lang('outlet_name'); ?> <span class="required_star">*</span></label>
                            <input tabindex="1" autocomplete="off" type="text" name="outlet_name" class="form-control" placeholder="<?php echo lang('outlet_name'); ?>" value="<?php echo escape_output($outlet_information->outlet_name); ?>">
                        </div>
                        <?php if (form_error('outlet_name')) { ?>
                            <div class="callout callout-danger my-2">
                                <?php echo form_error('outlet_name'); ?>
                            </div>
                        <?php } ?>

                    </div>

                    <div class="col-sm-12 mb-2 col-md-3">

                        <div class="form-group">
                            <label><?php echo lang('phone'); ?> <span class="required_star">*</span></label>
                            <input tabindex="4" autocomplete="off" type="text" name="phone" class="form-control" placeholder="<?php echo lang('phone'); ?>" value="<?php echo escape_output($outlet_information->phone); ?>">
                        </div>
                        <?php if (form_error('phone')) { ?>
                            <div class="callout callout-danger my-2">
                                <?php echo form_error('phone'); ?>
                            </div>
                        <?php } ?>

                    </div>
                    <div class="col-sm-12 mb-2 col-md-3">

                        <div class="form-group">
                            <label><?php echo lang('email'); ?> </label>
                            <input tabindex="4" autocomplete="off" type="text" name="email" class="form-control" placeholder="<?php echo lang('email'); ?>" value="<?php echo escape_output($outlet_information->email); ?>" />
                        </div>
                        <?php if (form_error('email')) { ?>
                            <div class="callout callout-danger my-2">
                                <?php echo form_error('email'); ?>
                            </div>
                        <?php } ?>

                    </div>

                    <?php
                    $language_manifesto = $this->session->userdata('language_manifesto');
                    if(str_rot13($language_manifesto)=="eriutoeri"):
                        ?>
                    <div class="col-sm-12 mb-2 col-md-3">
                        <div class="form-group">
                            <label><?php echo lang('Active_Status'); ?> <span class="required_star">*</span></label>
                            <select class="form-control select2" name="active_status" id="active_status">
                                <option <?php echo isset($outlet_information->active_status) && $outlet_information->active_status=="active"?'selected':''?> value="active"><?php echo lang('Active'); ?></option>
                                <option <?php echo isset($outlet_information->active_status) && $outlet_information->active_status=="inactive"?'selected':''?> value="inactive"><?php echo lang('Inactive'); ?></option>
                            </select>
                        </div>
                        <?php if (form_error('active_status')) { ?>
                            <div class="callout callout-danger my-2">
                                <?php echo form_error('active_status'); ?>
                            </div>
                        <?php } ?>
                    </div>

                        <div class="col-sm-12 mb-2 col-md-3">

                            <div class="form-group">
                                <label> <?php echo lang('Default_Waiter'); ?></label>
                                <select tabindex="2" class="form-control select2" name="default_waiter" id="default_waiter">
                                    <option value=""><?php echo lang('select'); ?></option>
                                    <?php
                                    foreach ($waiters as $value):
                                        if($value->designation=="Waiter"):
                                            ?>
                                            <option <?=($outlet_information->default_waiter==$value->id?'selected':'')?>  value="<?=$value->id?>"><?=$value->full_name?></option>
                                            <?php
                                        endif;
                                    endforeach;
                                    ?>
                                </select>
                            </div>
                            <?php if (form_error('default_waiter')) { ?>
                                <div class="callout callout-danger my-2">
                                    <?php echo form_error('default_waiter'); ?>
                                </div>
                            <?php } ?>
                        </div>
                        <?php
                    endif;
                    ?>

                    <div class="col-sm-12 mb-2 col-md-3">

                        <div class="form-group">
                            <label><?php echo lang('address'); ?> <span class="required_star">*</span></label>
                            <textarea tabindex="3" autocomplete="off" name="address" class="form-control" placeholder="<?php echo lang('address'); ?>"><?php echo escape_output($outlet_information->address); ?></textarea>
                        </div>
                        <?php if (form_error('address')) { ?>
                            <div class="callout callout-danger my-2">
                                <?php echo form_error('address'); ?>
                            </div>
                        <?php } ?>

                    </div>
                        

                    <div class="mb-3 col-sm-12 col-md-3 col-lg-3">
                        <div class="form-group">
                            <label> Comanda de Números Obligatoria  </label>
                            <select tabindex="7" class="form-control select2" name="comanda_required"
                                    id="comanda_required">
                                <option
                                    <?= isset($outlet_information) && $outlet_information->comanda_required== "1" ? 'selected' : '' ?>
                                        value="1"><?php echo lang('no')?></option>
                                <option
                                    <?= isset($outlet_information) && $outlet_information->comanda_required== "2" ? 'selected' : '' ?>
                                        value="2"><?php echo lang('yes')?></option>
                            </select>
                        </div>
                        <?php if (form_error('comanda_required')) { ?>
                            <div class="callout callout-danger my-2">
                                <?php echo form_error('comanda_required'); ?>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="mb-3 col-sm-12 col-md-3 col-lg-3">
                        <div class="form-group">
                            <label> Ocultar Total Antes Cierre de Caja  </label>
                            <select tabindex="7" class="form-control select2" name="registro_ocultar"
                                    id="registro_ocultar">
                                <option
                                    <?= isset($outlet_information) && $outlet_information->registro_ocultar== "No" ? 'selected' : '' ?>
                                        value="No"><?php echo lang('no')?></option>
                                <option
                                    <?= isset($outlet_information) && $outlet_information->registro_ocultar== "Yes" ? 'selected' : '' ?>
                                        value="Yes"><?php echo lang('yes')?></option>
                            </select>
                        </div>
                        <?php if (form_error('registro_ocultar')) { ?>
                            <div class="callout callout-danger my-2">
                                <?php echo form_error('registro_ocultar'); ?>
                            </div>
                        <?php } ?>
                    </div>


                    <div class="mb-3 col-sm-12 col-md-3 col-lg-3">
                        <div class="form-group">
                            <label> Mostrar todas las ventas en Cierre de Caja  </label>
                            <select tabindex="7" class="form-control select2" name="registro_detallado"
                                    id="registro_detallado">
                                <option
                                    <?= isset($outlet_information) && $outlet_information->registro_detallado== "No" ? 'selected' : '' ?>
                                        value="No"><?php echo lang('no')?></option>
                                <option
                                    <?= isset($outlet_information) && $outlet_information->registro_detallado== "Yes" ? 'selected' : '' ?>
                                        value="Yes"><?php echo lang('yes')?></option>
                            </select>
                        </div>
                        <?php if (form_error('registro_detallado')) { ?>
                            <div class="callout callout-danger my-2">
                                <?php echo form_error('registro_detallado'); ?>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="mb-3 col-sm-12 col-md-3 col-lg-3">
                        <div class="form-group">
                            <label> Factura Pre-impresa  </label>
                            <select tabindex="7" class="form-control select2" name="preimpreso_printer_id"
                                    id="preimpreso_printer_id">
                                        <option value="">Usar Impresion de Navegador</option>
                                        <?php foreach ($printers as $printer):?>
                                            <option <?php echo $outlet_information->preimpreso_printer_id == $printer->id ? 'selected' : ''; ?> <?php echo set_select('preimpreso_printer_id',$printer->id)?> value="<?php echo escape_output($printer->id); ?>"><?php echo escape_output($printer->title); ?></option>
                                        <?php endforeach;?>
                            </select>
                        </div>
                        <?php if (form_error('preimpreso_printer_id')) { ?>
                            <div class="callout callout-danger my-2">
                                <?php echo form_error('preimpreso_printer_id'); ?>
                            </div>
                        <?php } ?>
                    </div>

                        
                    <div class="mb-3 col-sm-12 col-md-3 col-lg-3">
                        <div class="form-group">
                            <label> <?php echo lang('online_order_module'); ?> </label>
                            <select tabindex="7" class="form-control select2" name="online_order_module"
                                    id="online_order_module">
                                <option
                                    <?= isset($outlet_information) && $outlet_information->online_order_module== "1" ? 'selected' : '' ?>
                                        value="1"><?php echo lang('no')?></option>
                                <option
                                    <?= isset($outlet_information) && $outlet_information->online_order_module== "2" ? 'selected' : '' ?>
                                        value="2"><?php echo lang('yes')?></option>
                            </select>
                        </div>
                        <?php if (form_error('online_order_module')) { ?>
                            <div class="callout callout-danger my-2">
                                <?php echo form_error('online_order_module'); ?>
                            </div>
                        <?php } ?>
                    </div>

                </div>

            </div>
            <!-- /.box-body -->
            <?php
            $data_c = getLanguageManifesto();
            ?>
            <div class="box-footer">
                <button type="submit" name="submit" value="submit" class="btn bg-blue-btn me-2">
                    <i data-feather="upload"></i>
                    <?php echo lang('submit'); ?>
                </button>
            
                <a class="btn bg-blue-btn me-2" href="<?php echo base_url() ?>Outlet/outlets">
                    <i data-feather="corner-up-left"></i>
                    <?php echo lang('back'); ?>
                </a>
            
                <a class="btn bg-blue-btn me-2" href="<?php echo base_url() ?>Outlet/editOutletItems/<?php echo $encrypted_id; ?>">
                    <i data-feather="check-square"></i>
                    Modificar Items de esta sucursal
                </a>

                
                <?php if (tipoFacturacion() == "Py_FE") : ?>
                    <a class="btn bg-blue-btn me-2" href="<?php echo base_url() ?>Outlet/configuracionSifen/<?php echo $encrypted_id; ?>">
                        <i data-feather="settings"></i> Configuración SIFEN
                    </a>
                <?php endif; ?>

            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</section>
<script src="<?php echo base_url(); ?>frequent_changing/js/edit_outlet.js"></script>