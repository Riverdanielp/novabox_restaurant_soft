$(function() {
    "use strict";
    
    // Variable global para almacenar las ventas pendientes
    let pendingSales = [];
    
    // Inicializar Select2 con Ajax para clientes con deuda
    if ($('.select2-ajax-customers').length) {
        $('.select2-ajax-customers').select2({
            ajax: {
                url: base_url + 'Customer_due_receive/getCustomersWithDue',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term, // término de búsqueda
                        page: params.page
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.results
                    };
                },
                cache: true
            },
            placeholder: 'Buscar por nombre, teléfono o GST...',
            minimumInputLength: 0,
            allowClear: true,
            language: {
                inputTooShort: function () {
                    return 'Escriba para buscar clientes con deuda';
                },
                noResults: function () {
                    return 'No se encontraron clientes con deuda';
                },
                searching: function () {
                    return 'Buscando...';
                }
            }
        });
    }
    
    // Cuando cambia el cliente
    $(document).on('change','#customer_id' , function(e){
        let customer_id = $('#customer_id').val();
        let csrf_name_= $("#csrf_name_").val();
        let csrf_value_= $("#csrf_value_").val();
        let current_due = $("#current_due").val();

        if (!customer_id) {
            $("#remaining_due").hide();
            $("#pending_sales_section").hide();
            $("#total_amount").val('');
            pendingSales = [];
            return;
        }

        // Obtener deuda total del cliente
        $.ajax({
            type: "GET",
            url: base_url+'Customer_due_receive/getCustomerDue',
            data: {
                customer_id: customer_id,
                csrf_name_: csrf_value_
            },
            success: function(data) {
                $("#remaining_due").show();
                $("#remaining_due").text(current_due+": " + data );
            }
        });

        // Obtener ventas pendientes con saldo
        $.ajax({
            type: "GET",
            url: base_url+'Customer_due_receive/getPendingSales',
            data: {
                customer_id: customer_id,
                csrf_name_: csrf_value_
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' && response.sales.length > 0) {
                    pendingSales = response.sales;
                    renderPendingSales();
                    calculateTotalAmount();
                    $("#pending_sales_section").show();
                } else {
                    $("#pending_sales_section").hide();
                    pendingSales = [];
                }
            }
        });
    });

    // Renderizar tabla de ventas pendientes
    function renderPendingSales() {
        let html = '';
        $.each(pendingSales, function(index, sale) {
            html += '<tr>';
            html += '<td><strong>' + sale.sale_no + '</strong></td>';
            html += '<td>' + sale.sale_date + '</td>';
            html += '<td class="text-end">' + formatCurrency(sale.total_payable) + '</td>';
            html += '<td class="text-end">' + formatCurrency(sale.due_amount) + '</td>';
            html += '<td class="text-end">' + formatCurrency(sale.paid_due_amount) + '</td>';
            html += '<td class="text-end"><strong>' + sale.remaining_due_formatted + '</strong></td>';
            html += '<td>';
            html += '<input type="number" class="form-control sale-payment-input text-end" ';
            html += 'data-sale-id="' + sale.id + '" ';
            html += 'data-remaining="' + sale.remaining_due + '" ';
            html += 'data-index="' + index + '" ';
            html += 'name="sales_details[' + sale.id + ']" ';
            html += 'value="' + sale.remaining_due + '" ';
            html += 'min="0" max="' + sale.remaining_due + '" step="0.01">';
            html += '</td>';
            html += '</tr>';
        });
        $('#pending_sales_body').html(html);
    }

    // Formatear moneda (función auxiliar)
    function formatCurrency(value) {
        return parseFloat(value).toFixed(2);
    }

    // Calcular monto total desde los inputs de ventas
    function calculateTotalAmount() {
        let total = 0;
        $('.sale-payment-input').each(function() {
            let value = parseFloat($(this).val()) || 0;
            total += value;
        });
        $('#total_amount').val(total.toFixed(2));
    }

    // Cuando cambia un input de pago individual
    $(document).on('input', '.sale-payment-input', function() {
        let input = $(this);
        let value = parseFloat(input.val()) || 0;
        let maxValue = parseFloat(input.attr('max'));
        
        // Validar que no exceda el saldo restante
        if (value > maxValue) {
            input.val(maxValue);
            alert('El monto no puede exceder el saldo restante de la venta');
        }
        
        // Recalcular total
        calculateTotalAmount();
    });

    // Cuando cambia el monto total manualmente
    $(document).on('input', '#total_amount', function() {
        let totalAmount = parseFloat($(this).val()) || 0;
        
        if (pendingSales.length === 0) {
            return;
        }
        
        // Distribuir el monto total entre las ventas pendientes
        let remainingToDistribute = totalAmount;
        
        $('.sale-payment-input').each(function(index) {
            let input = $(this);
            let maxValue = parseFloat(input.attr('max'));
            
            if (remainingToDistribute <= 0) {
                input.val(0);
            } else if (remainingToDistribute >= maxValue) {
                input.val(maxValue);
                remainingToDistribute -= maxValue;
            } else {
                input.val(remainingToDistribute.toFixed(2));
                remainingToDistribute = 0;
            }
        });
    });

    // Validar antes de enviar el formulario
    $('form').on('submit', function(e) {
        let totalAmount = parseFloat($('#total_amount').val()) || 0;
        let calculatedTotal = 0;
        
        $('.sale-payment-input').each(function() {
            calculatedTotal += parseFloat($(this).val()) || 0;
        });
        
        // Verificar que los montos coincidan
        if (Math.abs(totalAmount - calculatedTotal) > 0.01) {
            e.preventDefault();
            alert('Error: El monto total no coincide con la suma de los abonos a las ventas');
            return false;
        }
    });
});