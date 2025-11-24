$(function() {
    "use strict";
    function cal_opening_balance(){
        let total_amount = 0;
        $(".cal_row").each(function() {
            let this_value = Number($(this).val());
            total_amount+=this_value;
        });

        let ir_precision = $("#ir_precision").val();

        $(".opening_balance_hidden").val(total_amount);
        $(".total_opening_balance").html(total_amount.toFixed(ir_precision));
    }

    $(document).on('keyup', '.cal_row', function() {
        cal_opening_balance();
    });
    $(document).on('click', '.cal_row', function() {
        let value = Number($(this).val());
        if(value===0){
            $(this).val('');
        }
    });

    $(window).click(function() {
        $(".cal_row").each(function() {
            let this_value = Number($(this).val());
            if(this_value=='' && !$(this).is(":focus")){
                $(this).val(0);
            }
        });

    });

    // Cargar valores predeterminados al seleccionar contador
    $(document).on('change', '#counter_id', function() {
        const counter_id = $(this).val();
        
        if (!counter_id) {
            // Si no hay contador seleccionado, resetear valores a 0
            $('.payment-input').val(0);
            cal_opening_balance();
            return;
        }

        // Llamada AJAX para obtener preset del contador
        $.ajax({
            url: base_url + 'Register/getCounterPreset/' + counter_id,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.error) {
                    console.error('Error loading counter preset:', response.error);
                    return;
                }

                // Cargar valores predeterminados en los inputs
                if (response.default_opening_payments && typeof response.default_opening_payments === 'object') {
                    $('.payment-input').each(function() {
                        const payment_id = $(this).data('payment-id');
                        const default_amount = response.default_opening_payments[payment_id] || 0;
                        $(this).val(default_amount);
                    });
                }

                // Recalcular total
                cal_opening_balance();

                // Opcional: Mostrar mensaje informativo
                if (response.affect_opening_to_accounts == 1) {
                    console.log('Este contador afectará cuentas en la apertura');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en AJAX:', error);
            }
        });
    });

      // JavaScript to handle tab switching
      document.querySelectorAll('.ds-tab-button').forEach(button => {
        button.addEventListener('click', () => {
            const tabId = button.getAttribute('data-tab');

            // Remove active class from all buttons and content
            document.querySelectorAll('.ds-tab-button').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.ds-tab-content').forEach(content => content.classList.remove('active'));

            // Add active class to the clicked button and corresponding content
            button.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });
});