<link rel="stylesheet" href="<?php echo base_url(); ?>frequent_changing/css/add_purchase.css">
<script type="text/javascript" src="<?php echo base_url('frequent_changing/supplier.js'); ?>"></script>

<input type="hidden" id="ingredient_already_remain" value="<?php echo lang('ingredient_already_remain'); ?>">
<input type="hidden" id="supplier_field_required" value="<?php echo lang('supplier_field_required'); ?>">
<input type="hidden" id="date_field_required" value="<?php echo lang('date_field_required'); ?>">
<input type="hidden" id="at_least_ingredient" value="<?php echo lang('at_least_ingredient'); ?>">
<input type="hidden" id="paid_field_required" value="<?php echo lang('paid_field_required'); ?>">
<input type="hidden" id="payment_id_field_required" value="<?php echo lang('payment_id_field_required'); ?>">
<input type="hidden" id="base_url" value="<?php echo base_url(); ?>">
<input type="hidden" id="are_you_sure" value="<?php echo lang('are_you_sure'); ?>">
<input type="hidden" id="alert" value="<?php echo lang('alert'); ?>">

<script type="text/javascript" src="<?php echo base_url('frequent_changing/js/add_purchase.js'); ?>"></script>

<style>
    .callout.callout-success {
        background-color: #00a65a !important;
    }
</style>
<section class="main-content-wrapper">
    <section class="content-header">
        <h3 class="top-left-header">
            <?php echo isset($purchase_details) ? lang('edit_purchase') : lang('add_purchase'); ?>
        </h3>
    </section>

    <div class="box-wrapper">
        <div class="table-box">
            <div class="box-body">
                <div class="row">
                    <!-- Reference No -->
                    <div class="col-sm-12 col-md-6 mb-2 col-lg-4">
                        <div class="form-group">
                            <label><?php echo lang('ref_no'); ?></label>
                            <input tabindex="1" type="text" id="reference_no" name="reference_no"
                                class="form-control" placeholder="<?php echo lang('ref_no'); ?>"
                                value="<?php echo set_value('reference_no', isset($purchase_details) ? escape_output($purchase_details->reference_no) : (isset($pur_ref_no) ? escape_output($pur_ref_no) : '')); ?>">
                        </div>
                        <?php if (form_error('reference_no')) { ?>
                        <div class="callout callout-danger my-2">
                            <?php echo form_error('reference_no'); ?>
                        </div>
                        <?php } ?>
                        <div class="callout callout-danger my-2 error-msg name_err_msg_contnr">
                            <p id="name_err_msg"></p>
                        </div>
                    </div>
                    <!-- Supplier -->
                    <div class="col-sm-12 col-md-6 mb-2 col-lg-4">
                        <div class="form-group">
                            <label><?php echo lang('supplier'); ?> <span class="required_star">*</span></label>
                            <div class="d-flex align-items-center">
                                <div class="w-100">
                                    <select tabindex="2" class="form-control select2" id="supplier_id"
                                            name="supplier_id">
                                        <option value=""><?php echo lang('select'); ?></option>
                                        <?php foreach ($suppliers as $splrs): ?>
                                        <option value="<?php echo escape_output($splrs->id) ?>"
                                            <?php echo set_select('supplier_id', $splrs->id, (isset($purchase_details) && $purchase_details->supplier_id == $splrs->id)); ?>>
                                            <?php echo escape_output($splrs->name) ?><?php echo (strlen($splrs->doc_num) > 0) ? ' ('.$splrs->doc_num . ')' : '' ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <span class="plus-custom p-2 p-cursor" data-bs-toggle="modal" data-bs-target="#supplierModal">
                                        <i data-feather="plus"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php if (form_error('supplier_id')) { ?>
                        <div class="callout callout-danger my-2">
                            <?php echo form_error('supplier_id'); ?>
                        </div>
                        <?php } ?>
                        <div class="callout callout-danger my-2 error-msg supplier_id_err_msg_contnr">
                            <p id="supplier_id_err_msg"></p>
                        </div>
                    </div>



                    <?php if(tipoFacturacion() == 'RD_AI'): ?>

                        <!-- Tipo numeracion -->
                        <div class="col-xs-4 col-sm-4 col-md-2 mb-2 col-lg-1">
                            <div class="form-group">
                                <label for="tipo_numeracion">Prefijo:</label>
                                <select name="tipo_numeracion" class="form-control form-inps" id="tipo_numeracion" required>
                                        <?php $TiposNumeracion = TipoNumeracion();
                                        foreach ($TiposNumeracion as $Tipo) : ?>
                                                <option value="<?php echo $Tipo->id ?>"<?php if ($detalles_factura != NULL && $Tipo->id == $detalles_factura->numeracion_tipo) {echo ' selected';}; ?>><small><?php echo $Tipo->prefijo ?></small></option>
                                        <?php endforeach; ?>
                                    
                                </select>
                            </div>
                        </div>
  
                        <div class="col-xs-4 col-sm-8 col-md-4 mb-2 col-lg-3">
                            <div class="form-group">
                                <label>NCF <span class="required_star">*</span></label>
                                <input tabindex="1" type="text" id="ncf" name="ncf"
                                    class="form-control" placeholder="Ejemplo: B0200000001."
                                    value="<?php echo ($detalles_factura != NULL) ? $detalles_factura->ncf : '' ?>" required>
                            </div>
                        </div>

                        <div class="col-sm-12 col-md-6 mb-2 col-lg-4">
                            <div class="form-group">
                                <label>Vencimiento Factura:</label>
                                <input tabindex="4" type="date" id="fecha_venc" name="fecha_venc" class="form-control"
                                    placeholder="Ingrese fecha de vencimiento de la factura." value="<?php echo ($detalles_factura != NULL) ? date('Y-m-d', strtotime($detalles_factura->fecha_venc)) : ''; ?>">
                            </div>
                            <?php if (form_error('fecha_venc')) { ?>
                            <div class="callout callout-danger my-2">
                                <?php echo form_error('fecha_venc'); ?>
                            </div>
                            <?php } ?>
                            <div class="callout callout-danger my-2 error-msg date_err_msg_contnr"">
                                <p id="date_err_msg">
                                </p>
                            </div>
                        </div>

                        <div class="col-sm-12 col-md-6 mb-2 col-lg-4">
                            <div class="form-group">
                                <label for="tipo_cyg">Tipo Costo/Gasto: <span class="required_star">*</span></label>
                                <select name="tipo_cyg" class="form-control form-inps" id="tipo_cyg" required>
                                        <?php $TipoCyG = TipoCyG();
                                        foreach ($TipoCyG as $Tipo) : ?>
                                                <option value="<?php echo $Tipo->id ?>"<?php if ($detalles_factura != NULL && $Tipo->id == $detalles_factura->tipo_cyg) {echo ' selected';}; ?>><?php echo $Tipo->nombre ?></option>
                                        <?php endforeach; ?>
                                    
                                </select>
                            </div>
                        </div>

                    <?php else: ?>
                        
                        <!-- Factura nro -->
                        <div class="col-sm-12 col-md-6 mb-2 col-lg-4">
                            <div class="form-group">
                                <label>N° Factura</label>
                                <input tabindex="1" type="text" id="factura_nro" name="factura_nro"
                                    class="form-control" placeholder="N° Factura"
                                    value="<?php echo set_value('factura_nro', isset($purchase_details) ? escape_output($purchase_details->factura_nro) : ''); ?>">
                            </div>
                            <?php if (form_error('factura_nro')) { ?>
                            <div class="callout callout-danger my-2">
                                <?php echo form_error('factura_nro'); ?>
                            </div>
                            <?php } ?>
                            <div class="callout callout-danger my-2 error-msg factura_nro_msg_contnr">
                                <p id="factura_nro_msg"></p>
                            </div>
                        </div>

                    <?php endif; ?>

                    <!-- Date -->
                    <div class="col-sm-12 col-md-6 mb-2 col-lg-4">
                        <div class="form-group">
                            <label><?php echo lang('date'); ?> <span class="required_star">*</span></label>
                            <input tabindex="3" readonly type="text" id="date" name="date" class="form-control"
                                placeholder="<?php echo lang('date'); ?>"
                                value="<?php echo set_value('date', isset($purchase_details) ? escape_output($purchase_details->date) : date("Y-m-d",strtotime('today'))); ?>">
                        </div>
                        <?php if (form_error('date')) { ?>
                        <div class="callout callout-danger my-2">
                            <?php echo form_error('date'); ?>
                        </div>
                        <?php } ?>
                        <div class="callout callout-danger my-2 error-msg date_err_msg_contnr">
                            <p id="date_err_msg"></p>
                        </div>
                    </div>
                    <!-- Ingredient selector -->
                    <div class="col-sm-12 col-md-6 mb-2 col-lg-4">
                        <div class="form-group">
                            <label><?php echo lang('ingredients'); ?> <span class="required_star">*</span></label>
                            <div class="d-flex align-items-center">
                                <div class="w-100">
                                    <select tabindex="4" class="form-control select2 select2-hidden-accessible ir_w_100"
                                        name="ingredient_id" id="ingredient_id">
                                        <option value=""><?php echo lang('select'); ?></option>
                                        <?php foreach ($ingredients as $ingnts): ?>
                                        <option value="<?php echo escape_output($ingnts->id . "|" . $ingnts->name . " (" . $ingnts->code . ")|" . $ingnts->unit_name . "|" . $ingnts->purchase_price. "|" . $ingnts->unit_name) ."|" . $ingnts->sale_price ."|" . $ingnts->iva_tipo ?>"
                                            <?php echo set_select('unit_id', $ingnts->id); ?>>
                                            <?php echo escape_output($ingnts->name . "(" . $ingnts->code . ")") ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <span class="plus-custom p-2 p-cursor" data-bs-toggle="modal" data-bs-target="#ingredientModal">
                                        <i data-feather="plus"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php if (form_error('ingredient_id')) { ?>
                        <div class="callout callout-danger my-2">
                            <?php echo form_error('ingredient_id'); ?>
                        </div>
                        <?php } ?>
                        <div class="callout callout-danger my-2 error-msg ingredient_id_err_msg_contnr">
                            <p id="ingredient_id_err_msg"></p>
                        </div>
                    </div>
                    <!-- Notice -->
                    <div class="col-sm-12 col-md-6 mb-2 col-lg-4">
                        <div class="hidden-xs hidden-sm mt-2">&nbsp;</div>
                        <a class="btn bg-red-btn" data-bs-toggle="modal"
                            data-bs-target="#noticeModal"><?php echo lang('read_me_first'); ?></a>
                    </div>
                </div>

                <!-- Purchase Cart Table -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive" id="purchase_cart">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th><?php echo lang('sn'); ?></th>
                                        <th><?php echo lang('ingredient'); ?>(<?php echo lang('code'); ?>)</th>
                                        <th><?php echo lang('unit_price'); ?></th>
                                        <th><?php echo lang('quantity_amount'); ?></th>
                                        <th><?php echo lang('sale_price'); ?></th>
                                        <th>
                                            <?php if(tipoFacturacion() != 'RD_AI'): ?>
                                                IVA
                                            <?php else :  ?>
                                                ITBIS
                                            <?php endif; ?>
                                        </th>
                                        <th><?php echo lang('total'); ?></th>
                                        <th><?php echo lang('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Totals & payment -->
                <div class="row">
                    <?php if(tipoFacturacion() == 'RD_AI'): ?>
                        
                        <div class="col-md-8"></div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="tipo_pago">Tipo Pago <span class="required_star">*</span></label>
                                <select name="tipo_pago" class="form-control form-inps" id="tipo_pago" required>
                                        <?php $TipoPago = TipoPago();
                                        foreach ($TipoPago as $Tipo) : ?>
                                                <option value="<?php echo $Tipo->id ?>"<?php if ($detalles_factura != NULL && $Tipo->id == $detalles_factura->tipo_pago) {echo ' selected';}; ?>><?php echo $Tipo->nombre ?></option>
                                        <?php endforeach; ?>
                                    
                                </select>
                            </div>
                            <br>
                        </div>
                        
                        <div class="col-md-8"></div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo 'ITBIS' ?> <span class="required_star">*</span></label>
                                <input class="form-control" type="text" name="itbis"
                                       id="itbis" value="<?php echo set_value('itbis', isset($purchase_details) ? escape_output($purchase_details->itbis) : ''); ?>">
                            </div>
                        </div>

                    <?php endif; ?>
                    <div class="col-md-8"></div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><?php echo lang('g_total'); ?> <span class="required_star">*</span></label>
                            <input class="form-control" readonly type="text" name="grand_total"
                                   id="grand_total" value="<?php echo set_value('grand_total', isset($purchase_details) ? escape_output($purchase_details->grand_total) : ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-1"></div>
                    <div class="clearfix"></div>
                    <div class="col-md-8"></div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><?php echo lang('paid'); ?> <span class="required_star">*</span></label>
                            <input tabindex="3" class="form-control integerchk" type="text" name="paid"
                                   id="paid" onfocus="this.select();" onkeyup="return calculateAll()"
                                   value="<?php echo set_value('paid', isset($purchase_details) ? escape_output($purchase_details->paid) : ''); ?>">
                        </div>
                        <?php if (form_error('paid')) { ?>
                        <div class="callout callout-danger my-2">
                            <?php echo form_error('paid'); ?>
                        </div>
                        <?php } ?>
                        <div class="callout callout-danger my-2 error-msg paid_err_msg_contnr">
                            <p id="paid_err_msg"></p>
                        </div>
                    </div>
                    <div class="col-md-1"></div>
                    <div class="clearfix"></div>
                    <div class="col-md-8"></div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><?php echo lang('due'); ?></label>
                            <input class="form-control" type="text" name="due" id="due" readonly
                                value="<?php echo set_value('due', isset($purchase_details) ? escape_output($purchase_details->due) : ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-1"></div>
                    <div class="clearfix"></div>
                    <div class="col-md-8"></div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><?php echo lang('payment_method'); ?> <span class="required_star">*</span></label>
                            <select tabindex="3" class="form-control select2 ir_w_100" id="payment_id"
                                    name="payment_id">
                                <option value=""><?php echo lang('select'); ?></option>
                                <?php foreach ($payment_methods as $value): ?>
                                    <option value="<?php echo escape_output($value->id) ?>"
                                        <?php echo set_select('payment_id', $value->id, (isset($purchase_details) && $purchase_details->payment_id == $value->id)); ?>>
                                        <?php echo escape_output($value->name)?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php if (form_error('payment_id')) { ?>
                            <div class="callout callout-danger my-2">
                                <?php echo form_error('payment_id'); ?>
                            </div>
                        <?php } ?>
                        <div class="callout callout-danger my-2 error-msg payment_id_err_msg_contnr">
                            <p id="payment_id_err_msg"></p>
                        </div>
                    </div>
                    <div class="col-md-1"></div>
                    <div class="clearfix"></div>
                    <div class="col-md-8"></div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><?php echo lang('account'); ?></label>
                            <select tabindex="4" class="form-control select2 ir_w_100" id="account_id" name="account_id">
                                <option value=""><?php echo lang('default'); ?> (Caja Abierta)</option>
                                <?php if(isset($accounts)) { foreach ($accounts as $account) { ?>
                                    <option value="<?php echo escape_output($account->id) ?>"
                                        <?php echo set_select('account_id', $account->id, (isset($purchase_details) && $purchase_details->account_id == $account->id)); ?>>
                                        <?php echo escape_output($account->account_name)?></option>
                                <?php }} ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-1"></div>
                </div>
                <div class="row">
                    <input class="form-control" readonly type="hidden" name="subtotal" id="subtotal"
                        value="<?php echo set_value('subtotal', isset($purchase_details) ? escape_output($purchase_details->subtotal) : ''); ?>">
                </div>
            </div>
            <input type="hidden" name="suffix_hidden_field" id="suffix_hidden_field" />
            <div class="box-footer">
                <button type="button" id="guardarCompraFinal" class="btn bg-blue-btn me-2">
                    <i data-feather="upload"></i>
                    Guardar compra
                </button>
                <a class="btn bg-blue-btn" href="<?php echo base_url() ?>Purchase/purchases">
                    <i data-feather="corner-up-left"></i>
                    <?php echo lang('back'); ?>
                </a>
            </div>
        </div>
        
    </div>

    <?php // --- Supplier Modal --- ?>
    <div class="modal fade" id="supplierModal" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">
                        <?php echo lang('add_supplier'); ?></h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i data-feather="x"></i></span>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="form-horizontal">
                        <div class="row">
                            <div class="col-sm-12 col-md-6 mb-2">
                                <div class="form-group">
                                    <label class="control-label"><?php echo lang('supplier_name'); ?><span
                                            class="ir_color_red"> *</span></label>
                                    <div>
                                        <input type="text" class="form-control" name="name" id="name"
                                            placeholder="<?php echo lang('supplier_name'); ?>" value="">
                                        <div class="callout callout-danger my-2 error-msg supplier_err_msg_contnr">
                                            <p class="supplier_err_msg"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6 mb-2">
                                <div class="form-group">
                                    <label class="control-label"><?php echo tipoConsultaRuc() ?><span
                                            class="ir_color_red"> *</span></label>
                                    <div>
                                        <input type="text" class="form-control" name="doc_num" id="doc_num"
                                            placeholder="<?php echo tipoConsultaRuc() ?>" value="">
                                        <div class="callout callout-danger my-2 error-msg customer_err_msg_contnr">
                                            <p class="customer_err_msg"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6 mb-2">
                                <div class="form-group">
                                    <label class="control-label"><?php echo lang('contact_person'); ?><span
                                            class="ir_color_red"> *</span></label>
                                    <div>
                                        <input type="text" class="form-control" name="contact_person" id="contact_person"
                                            placeholder="<?php echo lang('contact_person'); ?>" value="">
                                        <div class="callout callout-danger my-2 error-msg customer_err_msg_contnr">
                                            <p class="customer_err_msg"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6 mb-2">
                                <div class="form-group">
                                    <label class="control-label"><?php echo lang('phone'); ?> <span
                                            class="ir_color_red">
                                            *</span></label>
                                    <div>
                                        <input type="text" class="form-control integerchk" id="phone" name="phone"
                                            placeholder="<?php echo lang('phone'); ?>" value="">
                                        <div class="callout callout-danger my-2 error-msg customer_phone_err_msg_contnr ir_p_5">
                                            <p class="customer_phone_err_msg"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6 mb-2">
                                <div class="form-group">
                                    <label class="control-label"><?php echo lang('email'); ?></label>
                                    <div>
                                        <input type="text" class="form-control" id="emailAddress" name="emailAddress"
                                            placeholder="<?php echo lang('email'); ?>" value="">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6 mb-2">
                                <div class="form-group">
                                    <label class="control-label"><?php echo lang('address'); ?></label>
                                    <div>
                                        <textarea tabindex="4" class="form-control" rows="3" name="supAddress"
                                            placeholder="<?php echo lang('address'); ?>"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6 mb-2">
                                <div class="form-group">
                                    <label class="control-label"><?php echo lang('description'); ?></label>
                                    <div>
                                        <textarea tabindex="4" class="form-control" rows="4" name="description"
                                            placeholder="<?php echo lang('enter'); ?> ..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn bg-blue-btn" id="addSupplier">
                        <i class="fa fa-save m-right"></i> <?php echo lang('submit'); ?></button>
                </div>
            </div>
        </div>
    </div>

    <?php // --- Notice Modal --- ?>
    <div class="modal fade" id="noticeModal" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="noticeModal">
            <div class="modal-content">
                <div class="modal-header">
                <h4 class="modal-title"><?php echo lang('notice'); ?></h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true"><i data-feather="x"></i></span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 hidden-lg hidden-sm">
                            <p class="foodMenuCartNotice">
                                <strong class="ir_ml39"><?php echo lang('notice'); ?></strong><br>
                                <?php echo lang('notice_text_1'); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php // --- Ingredient Modal --- ?>
    <div class="modal fade" id="ingredientModal" tabindex="-1" aria-labelledby="ingredientModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form id="ingredientForm">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="ingredientModalLabel">Nuevo Ingrediente / Producto</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <!-- Nombre -->
              <div class="mb-2">
                <label>Nombre</label>
                <input type="text" class="form-control" name="name" required>
              </div>
              <!-- Código -->
              <div class="mb-2">
                <label>Código</label>
                <input type="text" class="form-control" name="code">
              </div>
              <!-- Categoría de Ingrediente -->
              <div class="mb-2">
                <label>Categoría de ingrediente</label>
                <select class="form-control" name="category_id">
                  <?php foreach ($ing_categories as $cat): ?>
                    <option value="<?= $cat->id ?>"><?= $cat->category_name ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <!-- Precio de compra -->
              <div class="mb-2">
                <label>Precio de compra</label>
                <input type="number" class="form-control" name="purchase_price" min="0" step="any">
              </div>
              <!-- Stock mínimo -->
              <div class="mb-2">
                <label>Cantidad mínima (alerta)</label>
                <input type="number" class="form-control" name="alert_quantity" min="0" step="1">
              </div>
              <!-- Producto de venta -->
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" value="1" id="productForSaleCheck" name="product_for_sale" checked>
                <label class="form-check-label" for="productForSaleCheck">
                  Producto para venta
                </label>
              </div>
              <!-- Campos de producto de venta, ocultos por defecto -->
              <div id="saleFields" style="display:none;">
                <div class="mb-2">
                  <label>Categoría de menú</label>
                  <select class="form-control" name="food_menu_category_id">
                    <?php foreach ($categories as $cat): ?>
                      <option value="<?= $cat->id ?>"><?= $cat->category_name ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="mb-2">
                  <label>Precio de venta</label>
                  <input type="number" class="form-control" name="sale_price" min="0" step="any">
                </div>
                <div class="mb-2">
                  <label>Precio para llevar</label>
                  <input type="number" class="form-control" name="sale_price_take_away" min="0" step="any">
                </div>
                <div class="mb-2">
                  <label>Precio delivery</label>
                  <input type="number" class="form-control" name="sale_price_delivery" min="0" step="any">
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary">Guardar ingrediente/producto</button>
            </div>
          </div>
        </form>
      </div>
    </div>
</section>

<div class="modal fade" id="modalCantidad" tabindex="-1" aria-labelledby="modalCantidadLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formCantidad">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalCantidadLabel">Cantidad del producto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <input type="number" min="0.01" step="any" class="form-control" id="inputCantidad" autocomplete="off" required>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
    
    var purchase_id = <?php echo isset($purchase_details) ? (int)$purchase_details->id : 'null'; ?>;

    $('#ingredientModal').on('show.bs.modal', function () {
        $('#productForSaleCheck').prop('checked', true);
        $('#saleFields').show();
    });
    $('#productForSaleCheck').on('change', function() {
        if ($(this).is(':checked')) {
            $('#saleFields').show();
        } else {
            $('#saleFields').hide();
        }
    });
    $('#ingredientForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: base_url + 'Purchase/ajax_save_ingredient_and_product',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#ingredientModal').modal('hide');
                let data = JSON.parse(response);
                let ing = data.ingredient;
                let optionVal = ing.id + "|" + ing.name + " (" + ing.code + ")|" + ing.unit_name + "|" + ing.purchase_price + "|" + ing.unit_name + "|" + ing.sale_price + "|" + ing.iva_tipo;
                let newOption = new Option(ing.name + " (" + ing.code + ")", optionVal, false, true);
                $('#ingredient_id').append(newOption);
                $('#ingredient_id').val(optionVal).trigger('change');
            }
        });
    });
</script>


<script>
    

    let ingredient_already_remain = $("#ingredient_already_remain").val();
    let supplier_field_required = $("#supplier_field_required").val();
    let date_field_required = $("#date_field_required").val();
    let at_least_ingredient = $("#at_least_ingredient").val();
    let paid_field_required = $("#paid_field_required").val();
    let payment_id_field_required = $("#payment_id_field_required").val();
    let are_you_sure = $("#are_you_sure").val();
    let warning = $("#warning").val();
    let a_error = $("#a_error").val();
    let ok = $("#ok").val();
    let cancel = $("#cancel").val();
    let alert2 = $("#alert").val();

let pendingIngredient = null;

$(document).on('change', '#ingredient_id', function() {
    let ingredient_details = $('#ingredient_id').val();
    if (ingredient_details !== '') {
        let ingredient_details_array = ingredient_details.split('|');
        let repeated = false;
        $(".rowCount").each(function() {
            if($(this).attr('data-item_id') == ingredient_details_array[0]){
                repeated = true;
                swal({ title: alert2+"!", text: ingredient_already_remain, confirmButtonText: ok, confirmButtonColor: '#3c8dbc' });
                $('#ingredient_id').val('').change();
            }
        });
        if (repeated) return;

        // Guardar pendiente para cuando el usuario confirme el modal
        pendingIngredient = {
            ingredient_id: ingredient_details_array[0],
            name: ingredient_details_array[1],
            unit_price: ingredient_details_array[3],
            sale_price: ingredient_details_array[5],
            iva_tipo: ingredient_details_array[6]
        };
        // Mostrar modal de cantidad
        $('#modalCantidad').modal('show');
        $('#inputCantidad').val(1).focus();
    }
});

// Evento al guardar cantidad
$('#formCantidad').on('submit', function(e){
    e.preventDefault();
    if (!pendingIngredient) return;
    let cantidad = parseFloat($('#inputCantidad').val());
    if (isNaN(cantidad) || cantidad <= 0) { $('#inputCantidad').focus(); return; }
    pendingIngredient.quantity_amount = cantidad;
    agregarItemAJAX(pendingIngredient);
    pendingIngredient = null;
    $('#modalCantidad').modal('hide');
    $('#ingredient_id').val('').change();
});

$('#modalCantidad').on('shown.bs.modal', function () {
    $('#inputCantidad').trigger('focus');
});

function agregarItemAJAX(item) {
    let ajax_url = '';
    let ajax_data = {};
    let was_new = !purchase_id; // Guardar estado antes

    if (!purchase_id) {
        ajax_url = base_url + 'Purchase/ajaxCrearCompraYAgregarItem';
        ajax_data = {
            reference_no: $('#reference_no').val(),
            // factura_nro: $('#factura_nro').val(),
            supplier_id: $('#supplier_id').val(),
            date: $('#date').val(),
            paid: $('#paid').val(),
            payment_id: $('#payment_id').val(),
            account_id: $('#account_id').val(),
            item: item,
            
            <?php if(tipoFacturacion() == 'RD_AI'): ?>
                tipo_numeracion: $('#tipo_numeracion').val(),
                prefijo: $('#tipo_numeracion option:selected').text(),
                ncf: $('#ncf').val(),
                fecha_venc: $('#fecha_venc').val(),
                tipo_cyg: $('#tipo_cyg').val(),
                tipo_pago: $('#tipo_pago').val(),
                itbis: $('#itbis').val(),
            <?php else: ?>
                factura_nro: $('#factura_nro').val(),
            <?php endif; ?>
        };
    } else {
        ajax_url = base_url + 'Purchase/ajaxAgregarItemCompra';
        ajax_data = { purchase_id: purchase_id, item: item };
    }
    $.ajax({
        url: ajax_url,
        method: 'POST',
        data: ajax_data,
        success: function(response) {
            let res = JSON.parse(response);
            if (was_new && res.purchase_id) {
                // Solo redirecciona la primera vez
                window.location.href = base_url + "Purchase/addEditPurchase/" + res.purchase_id;
                return;
            }
            agregarFilaHTML(res.item);
            calculateAll();
        }
    });
}

function agregarFilaHTML(item) {
    let iva = '';
    
    <?php if(tipoFacturacion() == 'RD_AI'): ?>
        // iva = '<td><input type="hidden" value="' + item.iva_tipo + '" class="edit-iva-tipo" /></td>';
        iva = '<td><select class="form-control edit-iva-tipo">' +
            '<option value="18"' + (item.iva_tipo == '18' ? ' selected' : '') + '>ITBIS 18</option>' +
            '<option value="16"' + (item.iva_tipo == '16' ? ' selected' : '') + '>ITBIS 16</option>' +
            '<option value="0"' + (item.iva_tipo == '0' ? ' selected' : '') + '>ITBIS Exonerado</option>' +
        '</select></td>';
    <?php else : ?>
        iva = '<td><select class="form-control edit-iva-tipo">' +
            '<option value="10"' + (item.iva_tipo == '10' ? ' selected' : '') + '>IVA 10</option>' +
            '<option value="5"' + (item.iva_tipo == '5' ? ' selected' : '') + '>IVA 5</option>' +
            '<option value="0"' + (item.iva_tipo == '0' ? ' selected' : '') + '>IVA Exonerado</option>' +
        '</select></td>';
    <?php endif; ?>

    let cart_row = '<tr class="rowCount" data-item_id="' + item.ingredient_id + '" data-purchase_item_id="' + item.id + '" id="row_' + item.id + '">' +
        '<td style="padding-left: 10px;"><p>' + item.sn + '</p></td>' +
        '<td>' + item.name + '</td>' +
        '<td><input type="text" value="' + item.unit_price + '" class="form-control aligning edit-unit-price" /></td>' +
        '<td><input type="text" value="' + item.quantity_amount + '" class="form-control aligning edit-quantity" /></td>' +
        '<td><input type="text" value="' + item.sale_price + '" class="form-control aligning edit-sale-price" /></td>' +
        iva +
        '<td><input type="text" value="' + item.total + '" class="form-control aligning edit-total" readonly /></td>' +
        '<td>' +
            '<a href="#" class="btn btn-danger btn-xs btn-delete-row" data-id="' + item.id + '"><i class="fa fa-trash"></i></a>' +
        '</td>' +
    '</tr>';
    $('#purchase_cart tbody').prepend(cart_row);
}

// Eliminar ítem
$(document).on('click', '.btn-delete-row', function(e) {
    e.preventDefault();
    let item_id = $(this).data('id');
    $.ajax({
        url: base_url + 'Purchase/ajaxEliminarItemCompra',
        method: 'POST',
        data: {purchase_item_id: item_id},
        success: function(response) {
            // Eliminar de la tabla
            $('#row_' + item_id).remove();
            calculateAll();
        }
    });
});

// Editar ítem (actualiza en la BD al perder foco)
$(document).on('change', '.edit-unit-price, .edit-quantity, .edit-sale-price, .edit-iva-tipo', function() {
    let $row = $(this).closest('tr');
    let item_id = $row.data('purchase_item_id');
    let unit_price = $row.find('.edit-unit-price').val();
    let quantity = $row.find('.edit-quantity').val();
    let sale_price = $row.find('.edit-sale-price').val();
    let iva_tipo = $row.find('.edit-iva-tipo').val();
    $.ajax({
        url: base_url + 'Purchase/ajaxEditarItemCompra',
        method: 'POST',
        data: {
            purchase_item_id: item_id,
            unit_price: unit_price,
            quantity_amount: quantity,
            sale_price: sale_price,
            iva_tipo: iva_tipo
        },
        success: function(response) {
            // Podrías actualizar el total y otros datos visuales si lo deseas
            calculateAll();
        }
    });
});
</script>

<script>
<?php if (isset($purchase_ingredients) && is_array($purchase_ingredients)): ?>
    <?php foreach ($purchase_ingredients as $i => &$item){
        // Compose the display name as in JS
        $ingrediente = getIngredient($item->ingredient_id);
        $item->name = escape_output($ingrediente->name ?? '');
        $item->code = escape_output($ingrediente->code ?? '');
        $item->unit = unitName($ingrediente->unit_id ?? '');
        $item->ingredient_id = escape_output($item->ingredient_id ?? '');
        $item->unit_price = $item->unit_price ?? 0;
        $item->sale_price = $ingrediente->sale_price ?? 0;
        $item->iva_tipo = $ingrediente->iva_tipo ?? 10;
        $item->quantity_amount = $item->quantity_amount ?? 1;
        $item->total = $item->total ?? 0;
    } ?>
var initial_items = <?php echo json_encode($purchase_ingredients); ?>;
<?php else: ?>
var initial_items = [];
<?php endif; ?>
$(function(){
    // Renderiza usando la misma función para que los botones funcionen igual
    initial_items.reverse().forEach(function(item, idx){
        // let ingrediente = <?php /* aquí puedes hacer en PHP el array de ingredientes con sus datos, por id */ ?>;
        // Prepara el objeto igual al que devuelve AJAX
        let rowObj = {
            id: item.id,
            ingredient_id: item.ingredient_id,
            name: item.name,
            unit_price: item.unit_price,
            quantity_amount: item.quantity_amount,
            sale_price: item.sale_price,
            iva_tipo: item.iva_tipo,
            total: item.total,
            sn: idx+1
        };
        // console.log(rowObj);
        agregarFilaHTML(rowObj);
    });
            calculateAll();
});

$('#guardarCompraFinal').on('click', function() {
    let proveedor = $('#supplier_id').val();
    let nroFactura = $('#factura_nro').val();
    let fecha = $('#date').val();
    let pago = $('#paid').val();
    let metodoPago = $('#payment_id').val();
    let nItems = $('#purchase_cart tbody tr').length;

    let error = false;

    if (proveedor == "") {
        $("#supplier_id_err_msg").text(supplier_field_required);
        $(".supplier_id_err_msg_contnr").show(200);
        error = true;
    }
    if (fecha == "") {
        $("#date_err_msg").text(date_field_required);
        $(".date_err_msg_contnr").show(200);
        error = true;
    }
    if (nItems < 1) {
        $("#ingredient_id_err_msg").text(at_least_ingredient);
        $(".ingredient_id_err_msg_contnr").show(200);
        error = true;
    }
    if (pago == "") {
        $("#paid_err_msg").text(paid_field_required);
        $(".paid_err_msg_contnr").show(200);
        error = true;
    }
    if (metodoPago == "") {
        $("#payment_id_err_msg").text(payment_id_field_required);
        $(".payment_id_err_msg_contnr").show(200);
        error = true;
    }
    if (error) return;

    // Preparar datos finales para guardar
    guardarCompraFinalAjax();
});
function guardarCompraFinalAjax() {
    $.ajax({
        url: base_url + 'Purchase/ajaxGuardarDatosCompra',
        method: 'POST',
        data: {
            purchase_id: purchase_id,
            reference_no: $('#reference_no').val(),
            supplier_id: $('#supplier_id').val(),
            date: $('#date').val(),
            paid: $('#paid').val(),
            payment_id: $('#payment_id').val(),
            account_id: $('#account_id').val(),
            grand_total: $('#grand_total').val(),
            due: $('#due').val(),
            
            <?php if(tipoFacturacion() == 'RD_AI'): ?>
                tipo_numeracion: $('#tipo_numeracion').val(),
                prefijo: $('#tipo_numeracion option:selected').text(),
                ncf: $('#ncf').val(),
                fecha_venc: $('#fecha_venc').val(),
                tipo_cyg: $('#tipo_cyg').val(),
                tipo_pago: $('#tipo_pago').val(),
                itbis: $('#itbis').val(),
            <?php else: ?>
                factura_nro: $('#factura_nro').val(),
            <?php endif; ?>
        },
        success: function(response) {
            let res = JSON.parse(response);
            if(res.success) {
                swal({
                    title: "¡Guardado!",
                    text: "La compra se actualizó correctamente.",
                    confirmButtonText: ok,
                    confirmButtonColor: '#3c8dbc',
                    type: "success"
                }).then(function() {
                    window.location.href = base_url + "Purchase/purchases";
                });
            } else {
                swal({
                    title: "Error",
                    text: "Ocurrió un error al guardar la compra.",
                    confirmButtonText: ok,
                    confirmButtonColor: '#d33'
                });
            }
        }
    });
}

function calculateAll() {
    let subtotal = 0;
    let total_itbis = 0;
    let i = 1;
    $(".rowCount").each(function() {
        let $row = $(this);
        let unit_price = parseFloat($row.find('.edit-unit-price').val()) || 0;
        let quantity_amount = parseFloat($row.find('.edit-quantity').val()) || 0;

        $row.find("td:first p").html(i);
        i++;

        let total = unit_price * quantity_amount;
        <?php if(tipoFacturacion() == 'RD_AI'): ?>
            let iva_tipo = parseFloat($row.find('.edit-iva-tipo').val()) || 0;
            if (iva_tipo > 0) {
                // let base_imponible = total / (1 + iva_tipo / 100);
                // total_itbis += base_imponible * (iva_tipo / 100);
                total_itbis += total * (iva_tipo / 100);
                total += (total * (iva_tipo / 100));
            }
        <?php endif; ?>
        $row.find('.edit-total').val(total);
        subtotal += total;

    });

    if (isNaN(subtotal)) subtotal = 0;
    $("#subtotal").val(subtotal);

    <?php if(tipoFacturacion() == 'RD_AI'): ?>
        $("#itbis").val(total_itbis);
    <?php endif; ?>

    let other = parseFloat($.trim($("#other").val()));
    if ($.trim(other) == "" || $.isNumeric(other) == false) other = 0;

    let grand_total = parseFloat(subtotal) + parseFloat(other);
    <?php if(tipoFacturacion() == 'RD_AI'): ?>
        // grand_total = grand_total + total_itbis;
    <?php else : ?>
        // grand_total = grand_total;
    <?php endif; ?>
    $("#grand_total").val(grand_total);

    let paid = $("#paid").val();
    if ($.trim(paid) == "" || $.isNumeric(paid) == false) paid = 0;

    let due = parseFloat(grand_total) - parseFloat(paid);
    $("#due").val(due);
}

</script>

<script>
// Verificación en tiempo real del N° de factura (con chequeo por mismo registro)
(function(){
    var debounceTimer = null;
    var lastValue = '';
    function setFacturaMsg(text, type) {
        var $box = $('.factura_nro_msg_contnr');
        var $p = $('#factura_nro_msg');
        if (!text) {
            $box.hide();
            $p.text('');
            return;
        }
        $box.removeClass('callout-danger callout-success callout-info')
            .addClass('callout callout-' + type)
            .show();
        $p.text(text);
    }

    $('#factura_nro').on('input', function(){
        var val = $.trim($(this).val());
        lastValue = val;
        clearTimeout(debounceTimer);
        if (!val) {
            setFacturaMsg('', '');
            return;
        }
        setFacturaMsg('Buscando N° de factura...', 'info');
        debounceTimer = setTimeout(function(){
            $.ajax({
                url: base_url + 'Purchase/ajaxCheckFacturaNro',
                method: 'POST',
                data: { 
                    factura_nro: val, 
                    purchase_id: purchase_id,
                    provider_id: $('#supplier_id').val()
                 },
                success: function(resp){
                    var res;
                    try { res = JSON.parse(resp); } catch(e) { return; }
                    if (val !== lastValue) return;

                    // Por robustez: si el backend devolviera el mismo id (no debería por el where id !=),
                    // lo tratamos como disponible en el cliente también.
                    if (res.found && res.purchase && purchase_id && parseInt(res.purchase.id, 10) === parseInt(purchase_id, 10)) {
                        setFacturaMsg('Este N° de factura está disponible.', 'success');
                        return;
                    }

                    if (res.found) {
                        var extra = '';
                        if (res.purchase) {
                            var ref = res.purchase.reference_no ? (' Ref: ' + res.purchase.reference_no) : '';
                            var fch = res.purchase.date ? (' | Fecha: ' + res.purchase.date) : '';
                            var sup = res.purchase.supplier ? (' | Proveedor: ' + res.purchase.supplier) : '';
                            extra = ref + fch + sup;
                        }
                        setFacturaMsg('Este N° de factura ya está registrado.' + extra, 'danger');
                    } else {
                        setFacturaMsg('Este N° de factura está disponible.', 'success');
                    }
                },
                error: function(){
                    if (val === lastValue) {
                        setFacturaMsg('No se pudo verificar el N° de factura. Intente nuevamente.', 'danger');
                    }
                }
            });
        }, 400);
    });
})();
</script>