<?php
    // Obtén la moneda principal de la compañía
    $getCompanyInfo = getCompanyInfoById($company_id ?? 1); // Ajusta el id según corresponda
    $companyCurrency = $getCompanyInfo ? $getCompanyInfo->currency : '';
?>
<section class="main-content-wrapper">
    <section class="content-header">
        <h3 class="top-left-header">
            <?php echo isset($MultipleCurrencies) ?lang('edit') :lang('add') ?> <?php echo lang('MultipleCurrency'); ?>
        </h3>
    </section>
    <div class="box-wrapper">
        <div class="table-box">
            <?php echo form_open(base_url('MultipleCurrency/addEditMultipleCurrency/' . (isset($MultipleCurrencies) ? $this->custom->encrypt_decrypt($MultipleCurrencies->id, 'encrypt') : ''))); ?>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label><?php echo lang('currency'); ?> <span class="required_star">*</span></label>
                            <input tabindex="1" autocomplete="off" type="text" name="currency" id="currency" class="form-control" placeholder="<?php echo lang('currency'); ?>" value="<?php echo isset($MultipleCurrencies) && $MultipleCurrencies ? $MultipleCurrencies->currency : set_value('currency') ?>">
                        </div>
                        <?php if (form_error('currency')) { ?>
                            <div class="alert alert-error txt-uh-21">
                                <p><?php echo form_error('currency'); ?></p>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <!-- Inputs virtuales para la conversión -->
                        <div class="form-group d-flex align-items-center mt-3">
                            <div class="input-group me-2">
                            <span class="input-group-text" id="virtual_from_label">Tabla de Conversión</span>
                                <input type="number" step="any" id="virtual_from" class="form-control" value="1" min="0">
                            </div>
                            <span class="mx-2">
                                
                                <span class="input-group-text">
                                    <i data-feather="repeat"></i>
                                    <br>
                                    <i data-feather="arrow-right"></i>
                                </span>
                            </span>
                            <div class="input-group">
                            <span class="input-group-text">A moneda <?php echo $companyCurrency; ?></span>
                                <input type="number" step="any" id="virtual_to" class="form-control" placeholder="<?php echo $companyCurrency ?>" min="0">
                            </div>
                        </div>
                        <div class="text-muted" style="font-size: 0.9em;">
                            <i data-feather="info"></i>
                            <?php echo lang('conversion_helper_text'); /* Ejemplo: "Ingresa cuántos '.$companyCurrency.' equivalen a 1 de la nueva moneda" */ ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label><?php echo lang('conversion_rate'); ?> <span class="required_star">*</span></label>
                            <input tabindex="1" autocomplete="off" type="text" name="conversion_rate" id="conversion_rate" class="form-control" placeholder="<?php echo lang('conversion_rate'); ?>" value="<?php echo isset($MultipleCurrencies) && $MultipleCurrencies ? $MultipleCurrencies->conversion_rate : set_value('conversion_rate') ?>">
                        </div>
                        <?php if (form_error('conversion_rate')) { ?>
                            <div class="alert alert-error txt-uh-21">
                                <p><?php echo form_error('conversion_rate'); ?></p>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="box-footer">
                <button type="submit" name="submit" value="submit" class="btn bg-blue-btn me-2">
                    <i data-feather="upload"></i>
                    <?php echo lang('submit'); ?>
                </button>
                <a class="btn bg-blue-btn" href="<?php echo base_url() ?>MultipleCurrency/MultipleCurrencies">
                    <i data-feather="corner-up-left"></i>
                    <?php echo lang('back'); ?>
                </a>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let currencyInput = document.getElementById('currency');
    let fromLabel = document.getElementById('virtual_from_label');
    let virtualFrom = document.getElementById('virtual_from');
    let virtualTo = document.getElementById('virtual_to');
    let conversionRate = document.getElementById('conversion_rate');

    // Actualiza el label de la moneda nueva
    if(currencyInput && fromLabel) {
        currencyInput.addEventListener('input', function() {
            fromLabel.textContent = currencyInput.value || 'USD$';
        });
    }
    
    // Para evitar recursividad infinita
    let isUpdating = false;

    function setIfChanged(input, value) {
        // Solo actualiza si el valor realmente cambió, para evitar desencadenar eventos innecesarios
        if (input && String(input.value) !== String(value)) {
            input.value = value;
        }
    }

    function updateFromVirtuals() {
        if (isUpdating) return;
        isUpdating = true;
        let fromValue = parseFloat(virtualFrom.value.replace(',', '.')) || 0;
        let toValue = parseFloat(virtualTo.value.replace(',', '.')) || 0;
        if (fromValue > 0 && toValue > 0) {
            let rate = fromValue / toValue;
            setIfChanged(conversionRate, rate.toPrecision(12).replace(/\.?0+$/, ""));
        }
        isUpdating = false;
    }

    function updateToFromRate() {
        if (isUpdating) return;
        isUpdating = true;
        let fromValue = parseFloat(virtualFrom.value.replace(',', '.')) || 0;
        let rateValue = parseFloat(conversionRate.value.replace(',', '.'));
        if (fromValue > 0 && rateValue > 0) {
            let toValue = fromValue / rateValue;
            setIfChanged(virtualTo, toValue);
        }
        isUpdating = false;
    }

    function updateFromToRate() {
        if (isUpdating) return;
        isUpdating = true;
        let toValue = parseFloat(virtualTo.value.replace(',', '.')) || 0;
        let rateValue = parseFloat(conversionRate.value.replace(',', '.'));
        if (toValue > 0 && rateValue > 0) {
            let fromValue = toValue * rateValue;
            setIfChanged(virtualFrom, fromValue);
        }
        isUpdating = false;
    }

    // Eventos de cambio
    if (virtualFrom && virtualTo) {
        virtualFrom.addEventListener('input', updateFromVirtuals);
        virtualTo.addEventListener('input', updateFromVirtuals);
    }
    if (conversionRate) {
        conversionRate.addEventListener('input', updateToFromRate);
    }

    // Cuando la página carga (modo edición), si hay conversion_rate, calcula virtual_to
    if (
        conversionRate &&
        virtualFrom &&
        virtualTo &&
        conversionRate.value &&
        !isNaN(parseFloat(conversionRate.value.replace(',', '.')))
    ) {
        updateToFromRate();
    }

    // Opcionalmente, si el usuario edita conversion_rate y virtual_to, se puede recalcular virtual_from:
    // virtualTo.addEventListener('input', updateFromVirtuals);
    // conversionRate.addEventListener('input', updateFromToRate);

    // Si quieres que cualquier cambio entre los tres campos recalculen los otros dos de forma coherente,
    // podrías agregar lógica adicional, pero lo anterior cubre lo esencial del flujo solicitado.
});
</script>