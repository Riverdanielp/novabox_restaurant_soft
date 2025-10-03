<section class="main-content-wrapper">
<?php
if ($value =$this->session->flashdata('exception')) {

    echo '<section class="content-header px-0"><div class="alert alert-success alert-dismissible fade show"> 
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    <div class="alert-body"><p class="m-0"><i class="m-right fa fa-check"></i>';
    echo escape_output($value);
    echo '</p></div></div></section>';
}
?>

<section class="content-header px-0">
    <div class="row">
        <div class="col-md-6">
            <h2 class="top-left-header"><?php echo lang('customer_due_receives'); ?> </h2>
            <input type="hidden" class="datatable_name" data-title="<?php echo lang('customer_due_receives'); ?>" data-id_name="datatable">
        </div>
        <div class="col-md-6">

        </div>
    </div>
</section>

    <div class="box-wrapper">
        
            <div class="table-box">
                <!-- /.box-header -->
                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th class="ir_w_1"> <?php echo lang('sn'); ?></th>
                                <th class="ir_w_10"><?php echo lang('ref_no'); ?></th>
                                <th class="ir_w_10"><?php echo lang('date'); ?></th>
                                <th class="ir_w_10"><?php echo lang('customer'); ?></th>
                                <th class="ir_w_10"><?php echo lang('amount'); ?></th>
                                <th class="ir_w_10"><?php echo lang('payment_method'); ?></th>
                                <th class="ir_w_28"><?php echo lang('note'); ?></th>
                                <th class="ir_w_19"><?php echo lang('added_by'); ?></th>
                                <th class="ir_w_6 not-export-col"><?php echo lang('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($customerDueReceives && !empty($customerDueReceives)) {
                                $i = count($customerDueReceives);
                            }
                            foreach ($customerDueReceives as $value) {
                                // Preparamos los datos para el ticket en un array
                                $ticketData = [
                                    'ref_no' => escape_output($value->reference_no),
                                    'date' => escape_output(date($this->session->userdata('date_format'), strtotime($value->only_date))),
                                    'customer' => escape_output(getCustomerName($value->customer_id)),
                                    'amount' => escape_output(getAmtPCustom($value->amount)),
                                    'payment_method' => escape_output(getPaymentName($value->payment_id)),
                                    'note' => escape_output($value->note ?? '')
                                ];
                                ?>
                            <tr>
                                <td><?php echo escape_output($i--); ?></td>
                                <td><?php echo $ticketData['ref_no']; ?></td>
                                <td><?php echo $ticketData['date']; ?></td>
                                <td><?php echo $ticketData['customer']; ?></td>
                                <td><?php echo $ticketData['amount']; ?></td>
                                <td><?php echo $ticketData['payment_method']; ?></td>
                                <td><?php if ($value->note != NULL) echo escape_output($value->note) ?></td>
                                <td><?php echo escape_output(userName($value->user_id)); ?></td>

                                <td>
                                    <div class="btn_group_wrap d-flex">
                                        
                                        <!-- BOTÓN PARA IMPRIMIR TICKET -->
                                        <button type="button" class="btn btn-primary me-2" 
                                                onclick='printTicket(<?php echo json_encode($ticketData); ?>)' 
                                                data-bs-toggle="tooltip" data-bs-placement="top" title="Imprimir Ticket">
                                            <i class="fa fa-print"></i>
                                        </button>
                                        
                                        <a class="delete btn btn-danger" href="<?php echo base_url() ?>Customer_due_receive/deleteCustomerDueReceive/<?php echo escape_output($this->custom->encrypt_decrypt($value->id, 'encrypt')); ?>" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="<?php echo lang('delete'); ?>">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <!-- /.box-body -->
            </div>
      
    </div>
</section>


<?php $this->view('common/footer_js')?>

<!-- INICIO DEL SCRIPT DE IMPRESIÓN -->
<script>
function printTicket(data) {
    // --- Datos del Outlet (simulados desde PHP a JS) ---
    const outlet = {
        name: '<?php echo escape_output($this->session->userdata("outlet_name")); ?>',
        address: '<?php echo escape_output($this->session->userdata("address")); ?>',
        phone: '<?php echo escape_output($this->session->userdata("phone")); ?>',
        tax_reg_no: '<?php echo escape_output($this->session->userdata("tax_registration_no")); ?>',
        invoice_logo: '<?php $logo = $this->session->userdata("invoice_logo"); echo $logo ? base_url("images/".$logo) : ""; ?>',
        tax_name: '<?php echo isset($identImpuestoName) ? $identImpuestoName : "Tax Reg. No"; ?>'
    };

    // --- HTML del Ticket ---
                    // width: 300px;
    let ticketHTML = `
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Recibo de Pago</title>
            <style>
                body {
                    font-family: 'Courier New', Courier, monospace;
                    margin: 0 auto;
                    font-size: 14px;
                }
                .ticket-header, .ticket-footer {
                    text-align: center;
                }
                .ticket-header img {
                    max-width: 150px;
                    margin-bottom: 10px;
                }
                .ticket-header h3 {
                    margin: 5px 0;
                }
                .ticket-body {
                    margin-top: 20px;
                }
                .ticket-body table {
                    width: 100%;
                    border-collapse: collapse;
                }
                .ticket-body td {
                    padding: 2px 0;
                }
                .ticket-body .label {
                    font-weight: bold;
                }
                .total {
                    font-size: 16px;
                    font-weight: bold;
                    margin-top: 10px;
                }
                .divider {
                    border-top: 1px dashed #000;
                    margin: 10px 0;
                }
            </style>
        </head>
        <body>
            <div class="ticket">
                <header class="ticket-header">
                    ${outlet.invoice_logo ? `<img src="${outlet.invoice_logo}" alt="Logo">` : ''}
                    <h3>${outlet.name}</h3>
                    <p>
                        ${outlet.address}<br>
                        ${outlet.phone}<br>
                        ${outlet.tax_reg_no ? `${outlet.tax_name}: ${outlet.tax_reg_no}` : ''}
                    </p>
                </header>

                <div class="divider"></div>

                <section class="ticket-body">
                    <h4 style="text-align:center;">RECIBO DE PAGO</h4>
                    <table>
                        <tr>
                            <td class="label">Fecha:</td>
                            <td>${data.date}</td>
                        </tr>
                        <tr>
                            <td class="label">Ref No:</td>
                            <td>${data.ref_no}</td>
                        </tr>
                        <tr>
                            <td class="label">Cliente:</td>
                            <td>${data.customer}</td>
                        </tr>
                        <tr>
                            <td class="label">Método:</td>
                            <td>${data.payment_method}</td>
                        </tr>
                    </table>

                    <div class="divider"></div>

                    <table>
                        <tr>
                            <td class="label total">TOTAL PAGADO:</td>
                            <td class="total" style="text-align:right;">${data.amount}</td>
                        </tr>
                    </table>

                     ${data.note ? `<div class="divider"></div><p><b>Nota:</b> ${data.note}</p>` : ''}
                </section>

                <div class="divider"></div>

                <footer class="ticket-footer">
                    <p>¡Gracias por su pago!</p>
                </footer>
            </div>
        </body>
        </html>
    `;

    // --- Lógica para abrir la ventana emergente e imprimir ---
    const printWindow = window.open('', 'PRINT', 'height=600,width=400');
    printWindow.document.write(ticketHTML);
    printWindow.document.close();
    printWindow.focus();
    
    // Esperar a que el contenido se cargue completamente (especialmente imágenes)
    printWindow.onload = function() {
        printWindow.print();
        // printWindow.close();
    };
}
</script>
<!-- FIN DEL SCRIPT DE IMPRESIÓN -->