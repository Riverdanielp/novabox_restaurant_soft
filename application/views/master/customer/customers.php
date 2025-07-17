<section class="main-content-wrapper">

        <?php
        if ($this->session->flashdata('exception')) {
            echo '<section class="alert-wrapper">
                <div class="alert alert-success alert-dismissible fade show"> 
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <div class="alert-body">
            <p><i class="m-right fa fa-check"></i>';
            echo escape_output($this->session->flashdata('exception'));unset($_SESSION['exception']);
            echo '</p></div></div></section>';
        }
        ?>

        <section class="content-header">
            <div class="row">
                <div class="col-md-6">
                    <h2 class="top-left-header"><?php echo lang('customers'); ?> </h2>
                    <input type="hidden" class="datatable_name" data-title="<?php echo lang('customers'); ?>" data-id_name="datatable">
                </div>
                <div class="col-md-offset-2 col-md-4">
                    <div class="btn_list m-right d-flex">
                            <a data-access="upload_customer-249" class="btn bg-blue-btn menu_assign_class" href="<?php echo base_url() ?>customer/uploadCustomer">
                            <i data-feather="upload"></i> <?php echo lang('upload_customer'); ?>
                            </a>
                        
                    </div>

                </div>
            </div>
        </section>


        <div class="box-wrapper">
            <!-- general form elements -->
            <div class="table-box">
                <!-- /.box-header -->
                <div class="table-responsive">
                    <?php $is_loyalty_enable = $this->session->userdata('is_loyalty_enable');?>
                    <table id="datatable" class="table">
                        <thead>
                            <tr>
                                <th><?php echo lang('sn'); ?></th>
                                <th><?php echo lang('customer_name'); ?></th>
                                <th><?php echo lang('phone'); ?></th>
                                <th><?php echo lang('email'); ?></th>
                                <th><?php echo lang('dob'); ?></th>
                                <th><?php echo lang('default_discount_t'); ?></th>
                                <th><?php echo lang('address'); ?></th>
                                <th><?php echo lang('current_due'); ?></th>
                                <?php if(isset($is_loyalty_enable) && $is_loyalty_enable=="enable"):?>
                                    <th><?php echo lang('is_loyalty_enable'); ?></th>
                                <?php endif;?>
                                <th><?php echo lang('added_by'); ?></th>
                                <th><?php echo lang('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <!-- /.box-body -->
            </div>
        </div>
    
</section>

<?php //$this->view('common/footer_js')?>


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
<script src="<?php echo base_url(); ?>frequent_changing/newDesign/js/forTable.js"></script>

<script>
    "use strict";
    let base_url = $("#base_url_").val();
    $(document).ready(function(){
        $("#datatable").DataTable({
            autoWidth: false,
            ordering: true,
            processing: true,
            serverSide: true,
            order: [[0, "desc"]],
            lengthMenu: [
                [10, 20, 50, 100, 200, 300, 500, -1],
                [10, 20, 50, 100, 200, 300, 500, "Todos"]
            ],
            pageLength: 10, // opción inicial
            ajax: {
                url: base_url + "customer/getAjaxData",
                type: "POST",
                dataType: "json",
                data: {},
            },
            columnDefs: [
                { orderable: true, targets: [5, 7, 8] }
            ],
            dom: '<"top-left-item col-sm-12 col-md-6"lf> <"top-right-item col-sm-12 col-md-6"B> t <"bottom-left-item col-sm-12 col-md-6 "i><"bottom-right-item col-sm-12 col-md-6 "p>',
            buttons: [
                { extend: 'print', text: '<i class="fa-solid fa-print"></i> Print', titleAttr: 'print' },
                { extend: 'copyHtml5', text: '<i class="fa-solid fa-copy"></i> Copy', titleAttr: 'Copy' },
                { extend: 'excelHtml5', text: '<i class="fa-solid fa-file-excel"></i> Excel', titleAttr: 'Excel' },
                { extend: 'csvHtml5', text: '<i class="fa-solid fa-file-csv"></i> CSV', titleAttr: 'CSV' },
                { extend: 'pdfHtml5', text: '<i class="fa-solid fa-file-pdf"></i> PDF', titleAttr: 'PDF' }
            ],
            language: {
                paginate: {
                    previous: "Previous",
                    next: "Next",
                },
                lengthMenu: "Mostrar _MENU_ registros por página"
            },
        });
    });

</script>