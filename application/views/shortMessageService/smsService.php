<!-- Main content -->
<section class="main-content-wrapper">

    <?php
    if ($this->session->flashdata('exception_2')) {

        echo '<section class="alert-wrapper">
        <div class="alert alert-danger alert-dismissible"> 
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <div class="alert-body">
        <p><i class="m-right fa fa-times"></i>';
        echo escape_output($this->session->flashdata('exception_2'));
        echo '</p></div></div></section>';
    }
    ?>  

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
            <?php echo lang('sms_service_chose_option'); ?>
        </h3>  
    </section>


    <div class="box-wrapper">
        
            <div class="table-box">

                <!-- /.box-header -->
                <!-- form start --> 
                <div class="box-body">
                    <div class="row">
                        <div class="col-sm-6 col-md-4">
                            <div class="form-group my-2">
                                <a class="new-btn w-100" href="<?php echo base_url();?>Authentication/SMSSetting"><?php echo lang('configure_sms'); ?></a>
                            </div>  
                        </div> 
                        <div class="col-sm-6 col-md-4">
                            <div class="form-group my-2">
                                <a class="new-btn w-100" href="<?php echo base_url();?>Short_message_service/sendSMS/custom"><?php echo lang('send_test_sms'); ?></a>
                            </div>  
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6 col-md-4"> 
                            <div class="form-group my-2">
                                <a class="new-btn w-100" href="<?php echo base_url();?>Short_message_service/sendSMS/birthday"><?php echo lang('sms_birthday_customer'); ?></a>
                            </div>  
                        </div> 
                        <div class="col-sm-6 col-md-4"> 
                            <div class="form-group my-2">
                                <a class="new-btn w-100" href="<?php echo base_url();?>Short_message_service/sendSMS/anniversary"><?php echo lang('sms_anniversary_customer'); ?></a>
                            </div>  
                        </div>   

                        <div class="col-sm-6 col-md-4"> 
                            <div class="form-group my-2">
                                <a class="new-btn w-100" href="<?php echo base_url();?>Short_message_service/sendSMS/customAll"><?php echo lang('send_custom_sms_all_customer'); ?></a>
                            </div>  
                        </div>  
                    </div>
                    <!-- /.box-body -->
                </div>  
            </div>
        
    </div>
</section>