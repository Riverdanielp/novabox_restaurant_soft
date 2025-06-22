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




<section class="main-content-wrapper">

    <section class="content-header">
        <h3 class="top-left-header">
            <?php echo lang('add_purchase'); ?>
        </h3>
    </section>

        <div class="box-wrapper">
            
            <div class="table-box">
                <?php echo form_open_multipart(base_url() . 'Purchase/addEditPurchase', $arrayName = array('id' => 'purchase_form')) ?>
                <div class="box-body">
                    <div class="row">
                        <div class="col-sm-12 col-md-6 mb-2 col-lg-4">
                            <div class="form-group">
                                <label><?php echo lang('ref_no'); ?></label>
                                <input tabindex="1" type="text" id="reference_no" name="reference_no"
                                    class="form-control" placeholder="<?php echo lang('ref_no'); ?>"
                                    value="<?php echo escape_output($pur_ref_no); ?>">
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

                        <div class="col-sm-12 col-md-6 mb-2 col-lg-4">
                            <div class="form-group">
                                <label><?php echo lang('supplier'); ?> <span class="required_star">*</span></label>
                                
                                    <div class="d-flex align-items-center">
                                        <div class="w-100">
                                            <select tabindex="2" class="form-control select2" id="supplier_id"
                                                name="supplier_id">
                                                <option value=""><?php echo lang('select'); ?></option>
                                                <?php
                                                foreach ($suppliers as $splrs) {
                                                    ?>
                                                <option value="<?php echo escape_output($splrs->id) ?>"
                                                    <?php echo set_select('supplier_id', $splrs->id); ?>>
                                                    <?php echo escape_output($splrs->name) ?> <?php echo (strlen($splrs->doc_num) > 0) ? ' ('.$splrs->doc_num . ')' : '' ?></option>
                                                <?php } ?>
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

                        <div class="col-sm-12 col-md-6 mb-2 col-lg-4">
                            <div class="form-group">
                                <label>N° Factura</label>
                                <input tabindex="1" type="text" id="factura_nro" name="factura_nro"
                                    class="form-control" placeholder="N° Factura"
                                    value="">
                            </div>
                            <?php if (form_error('factura_nro')) { ?>
                            <div class="callout callout-danger my-2">
                                <?php echo form_error('factura_nro'); ?>
                            </div>
                            <?php } ?>
                            <div class="callout callout-danger my-2 error-msg name_err_msg_contnr">
                                <p id="name_err_msg"></p>
                            </div>
                        </div>

                        <div class="col-sm-12 col-md-6 mb-2 col-lg-4">
                            <div class="form-group">
                                <label><?php echo lang('date'); ?> <span class="required_star">*</span></label>
                                <input tabindex="3" readonly type="text" id="date" name="date" class="form-control"
                                    placeholder="<?php echo lang('date'); ?>" value="<?php echo date("Y-m-d",strtotime('today')); ?>">
                            </div>
                            <?php if (form_error('date')) { ?>
                            <div class="callout callout-danger my-2">
                                <?php echo form_error('date'); ?>
                            </div>
                            <?php } ?>
                            <div class="callout callout-danger my-2 error-msg date_err_msg_contnr"">
                                <p id="date_err_msg">
                                </p>
                            </div>
                        </div>
                        
                        <div class="col-sm-12 col-md-6 mb-2 col-lg-4">

                            <div class="form-group">
                                <label><?php echo lang('ingredients'); ?> <span class="required_star">*</span></label>
                                
                                    <div class="d-flex align-items-center">
                                        <div class="w-100">
                                            <select tabindex="4" class="form-control select2 select2-hidden-accessible ir_w_100"
                                                name="ingredient_id" id="ingredient_id">
                                                <option value=""><?php echo lang('select'); ?></option>
                                                <?php foreach ($ingredients as $ingnts) { ?>
                                                <option
                                                    value="<?php echo escape_output($ingnts->id . "|" . $ingnts->name . " (" . $ingnts->code . ")|" . $ingnts->unit_name . "|" . $ingnts->purchase_price. "|" . $ingnts->unit_name) ."|" . $ingnts->sale_price ."|" . $ingnts->iva_tipo ?>"
                                                    <?php echo set_select('unit_id', $ingnts->id); ?>>
                                                    <?php echo escape_output($ingnts->name . "(" . $ingnts->code . ")") ?></option>
                                                <?php } ?>
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

                        <div class="col-sm-12 col-md-6 mb-2 col-lg-4">
                            <div class="hidden-xs hidden-sm mt-2">&nbsp;</div>
                            <a class="btn bg-red-btn" data-bs-toggle="modal"
                                data-bs-target="#noticeModal"><?php echo lang('read_me_first'); ?></a>
                        </div>
                        
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive" id="purchase_cart">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th><?php echo lang('sn'); ?></th>
                                            <th>
                                                <?php echo lang('ingredient'); ?>(<?php echo lang('code'); ?>)</th>
                                            <th><?php echo lang('unit_price'); ?> <span
                                                    class="ir_c_transparent">&nbsp;</span></th>
                                            <th><?php echo lang('quantity_amount'); ?></th>
                                            <th><?php echo lang('sale_price'); ?> <span
                                                    class="ir_c_transparent">&nbsp;</span></th>
                                            <th>IVA <span class="ir_c_transparent">&nbsp;</span></th>
                                            <th><?php echo lang('total'); ?> <span class="ir_c_transparent">&nbsp;</span></th>
                                            <th><?php echo lang('actions'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-md-8"></div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo lang('g_total'); ?> <span class="required_star">*</span></label>
                                <input class="form-control" readonly type="text" name="grand_total"
                                       id="grand_total" <?php echo set_value('grand_total'); ?>>
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
                                    <?php echo set_value('paid'); ?>>
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
                                    <?php echo set_value('due'); ?>>
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
                                    <?php foreach (getAllPaymentMethods(5) as $value) {
                                        ?>
                                        <option value="<?php echo escape_output($value->id) ?>"
                                            <?php echo set_select('payment_id', $value->id); ?>>
                                            <?php echo escape_output($value->name)?></option>
                                        <?php
                                    } ?>
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
                    </div>

                    <div class="row">

                        <input class="form-control" readonly type="hidden" name="subtotal" id="subtotal">

                    </div>
                </div>

                <input type="hidden" name="suffix_hidden_field" id="suffix_hidden_field" />

                <div class="box-footer">
                    <button type="submit" name="submit" value="submit" class="btn bg-blue-btn me-2">
                        <i data-feather="upload"></i>
                        <?php echo lang('submit'); ?>
                    </button>

                    <a class="btn bg-blue-btn" href="<?php echo base_url() ?>Purchase/purchases">
                        <i data-feather="corner-up-left"></i>
                        <?php echo lang('back'); ?>
                    </a>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>


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

<script>
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
                // Actualiza el select de ingredientes con el nuevo/actualizado ingrediente
                // Puedes refrescarlo o agregar la opción, según tu lógica
                $('#ingredientModal').modal('hide');
                // Recarga el select2 ingredients aquí...
                // Agrega el ingrediente al select y selecciónalo
                let data = JSON.parse(response);

                // Ahora puedes acceder a data.ingredient
                let ing = data.ingredient;
                console.log('Nuevo ingrediente agregado:', ing);
                let optionVal = ing.id + "|" + ing.name + " (" + ing.code + ")|" + ing.unit_name + "|" + ing.purchase_price + "|" + ing.unit_name + "|" + ing.sale_price + "|" + ing.iva_tipo;
                // Si usas select2, puedes hacer:
                let newOption = new Option(ing.name + " (" + ing.code + ")", optionVal, false, true);
                $('#ingredient_id').append(newOption);
                // Ahora selecciona la opción recién agregada y dispara el evento change
                $('#ingredient_id').val(optionVal).trigger('change');
            }
        });
    });

</script>