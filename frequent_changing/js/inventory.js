jQuery(function() {
    "use strict";
    let base_url = jQuery("#base_url_").val();
    let stock_value = jQuery("#stock_value").val();
    let currency = '';
    jQuery('#stockValue').html(stock_value+': '+currency + jQuery(
        '#grandTotal').val() +
        '<a class="top" title="" data-placement="top" data-toggle="tooltip" style="cursor:pointer" data-original-title="Calculado en base al último precio de compra y no se considera el ingrediente con cantidad/cantidad de stock negativa"><i data-feather="help-circle"></i></a>'
    );
    // //iCheck for checkbox and radio inputs
    // jQuery('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
    //     checkboxClass: 'icheckbox_minimal-blue',
    //     radioClass: 'iradio_minimal-blue'
    // });

    jQuery(document).on('change','#food_id' , function(e){
        let value = this.value;
        if (value) {
            jQuery('#category_id').prop('disabled', true);
            jQuery('#ingredient_id').prop('disabled', true);
        } else {
            jQuery('#category_id').prop('disabled', false);
            jQuery('#ingredient_id').prop('disabled', false);
        }
    });
    jQuery(document).on('change','#ingredient_id' , function(e){
        let ingredient_id = this.value;
        let category_id = jQuery('select.category_id').find(':selected').val();
        if (ingredient_id || category_id) {
            jQuery('#food_id').prop('disabled', true);
        } else {
            jQuery('#food_id').prop('disabled', false);
        }
    });
    jQuery(document).on('change','#category_id' , function(e){
        let category_id = this.value;
        if (category_id) {
            jQuery('#food_id').prop('disabled', true);
        } else {
            jQuery('#food_id').prop('disabled', false);
        }
        let options = '';
        let csrf_name_= jQuery("#csrf_name_").val();
        let csrf_value_= jQuery("#csrf_value_").val();
        let ingredient= jQuery("#ingredient").val();
        jQuery.ajax({
            type: 'get',
            url: base_url+'Inventory/getIngredientInfoAjax',
            data: {
                category_id: category_id,
                csrf_name_: csrf_value_
            },
            datatype: 'json',
            success: function(data) {
                let json = jQuery.parseJSON(data);
                options += '<option  value="">'+ingredient+'</option>';
                jQuery.each(json, function(i, v) {
                    options += '<option  value="' + v.id + '">' + v.name + '(' + v
                        .code + ')</option>';
                });
                jQuery('#ingredient_id').html(options);
            }
        });
    });
    let category_id = jQuery('select.category_id').find(':selected').val();
    let ingredient_id = jQuery('select.ingredient_id').find(':selected').val();
    let food_id = jQuery('select.food_id').find(':selected').val();
    if (food_id) {
        jQuery('#category_id').prop('disabled', false);
        jQuery('#ingredient_id').prop('disabled', false);

    } else if (ingredient_id || category_id) {
        jQuery('#category_id').prop('disabled', false);
        jQuery('#ingredient_id').prop('disabled', false);
    } else {
        if (food_id) {
            jQuery('#category_id').prop('disabled', true);
            jQuery('#ingredient_id').prop('disabled', true);
        }

    }
    if (category_id) {
        let selectedID = jQuery("#hiddentIngredientID").val();
        let options = '';
        let csrf_name_= jQuery("#csrf_name_").val();
        let csrf_value_= jQuery("#csrf_value_").val();
        let ingredient= jQuery("#ingredient").val();
        jQuery.ajax({
            type: 'get',
            url: base_url+'Inventory/getIngredientInfoAjax',
            data: {
                category_id: category_id,
                csrf_name_: csrf_value_
            },
            datatype: 'json',
            success: function(data) {
                let json = jQuery.parseJSON(data);
                options += '<option  value="">'+ingredient+'</option>';
                jQuery.each(json, function(i, v) {
                    options += '<option  value="' + v.id + '">' + v.name + '(' + v.code +
                        ')</option>';
                });
                jQuery('#ingredient_id').html(options);
                jQuery('#ingredient_id').val(selectedID).change();
            }
        });
    }

});
