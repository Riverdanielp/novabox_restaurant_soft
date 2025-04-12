<?php
$wl = getWhiteLabel();
$site_name = '';
$footer = '';
$favicon = '';
if($wl){
    if($wl->site_name){
        $site_name = $wl->site_name;
    }
    if($wl->footer){
        $footer = $wl->footer;
    }
    if($wl->system_logo){
        $system_logo = base_url()."images/".$wl->system_logo;
    }
    if($wl->favicon){
        $favicon = base_url()."images/".$wl->favicon;
    }else{
        $favicon = base_url()."images/favicon.ico";
    }
}
$company = getMainCompany();
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo escape_output($site_name); ?></title>
    <meta name="description" content="Tu sistema de gestión de restaurant desde la nube!">

    <meta name="theme-color" content="#ff9710">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Novabox - Restaurant Soft">
    <link rel="apple-touch-icon" href="<?php echo base_url(); ?>assets/logos/android-launchericon-192-192.png">

    <!-- Manifest -->
    <link rel="manifest" href="<?php echo base_url(); ?>manifest.json?v=1.01">
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo base_url(); ?>images/favicon.ico" type="image/x-icon">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <script src="<?php echo base_url(); ?>assets/bower_components/jquery/dist/jquery.min.js"></script>
    <link rel="stylesheet"
          href="<?php echo base_url(); ?>assets/bower_components/font-awesome/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/bower_components/Ionicons/css/ionicons.min.css">
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="<?php echo base_url(); ?>frequent_changing/css/bootstrap.min.css">
    <!-- Latest compiled and minified JavaScript -->
    <script src="<?php echo base_url(); ?>frequent_changing/js/bootstrap.min.js"></script>
    
    <script src="<?php echo base_url(); ?>assets/pin_login/dist/jquery.pinlogin.min.js"></script>
    <link href="<?php echo base_url(); ?>assets/pin_login/src/jquery.pinlogin.css" rel="stylesheet" type="text/css" />
    
    <script src="<?php echo base_url(); ?>frequent_changing/js/login.js"></script>
    <link rel="stylesheet" href="<?php echo base_url(); ?>frequent_changing/css/login_new.css">
    <link rel="icon" href="<?php echo escape_output($favicon) ?>" type="image/x-icon">
    <link href="<?php echo base_url(); ?>frequent_changing/notify/toastr.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div class="container">
    <div class="row">
        <input type="hidden" value="<?php echo escape_output($company->login_type)?>" id="login_button_type">
        <!-- Mixins-->
        <!-- Pen Title-->
        <div class="pen-title">
            <img src="<?php echo escape_output($system_logo); ?>" style="max-width: 350px;">
        </div>
        <?php
            $active_login_button = $this->session->flashdata('active_login_button');;unset($_SESSION['active_login_button']);
            if(!$active_login_button){
                $active_login_button = $company->active_login_button;
            }
            $div_hide = 'none';
             
            if($company->login_type==1){
                $div_hide = ''; 
            }
           
        

        ?>
        <table class="btn_login_pin_table" style="display:<?php echo $div_hide?>">
            <tr>
                <td><a href="#" class="btn_login_pin_type <?php echo isset($active_login_button) && $active_login_button==1?'active_login_btn':''?> active_login_button" data-id="1">Acceso con Usuario</a> <a class="btn_pin_trigger btn_login_pin_type active_login_button  <?php echo isset($active_login_button) && $active_login_button==2?'active_login_btn':''?>" data-id="2" href="#">Acceso con Pin</a></td>
            </tr>
        </table>
        <div class="container">

            <div class="card"></div>
            <div class="card">
                <?php
                if ($this->session->flashdata('exception_1')) {
                    echo '<p class="red_error"><i  class="fa fa-times"></i> ';echo escape_output($this->session->flashdata('exception_1'));unset($_SESSION['exception_1']);
                    echo '</p>';
                }
                ?>

                <?php
                if ($this->session->flashdata('exception')) {
                    echo '<p class="green_error"><i  class="fa fa-check"></i> ';echo escape_output($this->session->flashdata('exception'));unset($_SESSION['exception']);
                    echo '</p>';
                }
                ?>
                <h1 class="title">Iniciar Sesión</h1>

                <?php echo form_open(base_url() . 'Authentication/loginCheck', $arrayName = array('novalidate' => 'novalidate')) ?>
                <div class="div_1 text_center display_none_login" id="loginpin"></div>
                <input type="hidden" class="form-control display_none_login" name="login_pin" id="login_pin" placeholder="<?php echo lang('login_pin'); ?>"     value="">
                <input type="hidden" class="form-control display_none_login" name="active_login_button_hidden" id="active_login_button_hidden" value="<?php echo $active_login_button?>">

                <div class="input-container margin_login_top div_2">
                    <input name="email_address" type="text" value="<?php echo set_value('email_address'); ?>" id="email_address" required="required"/>
                    <label for="email_address">Correo o Usuario</label>
                    <div class="bar"></div>

                </div>
                <?php if (form_error('email_address')) { ?>
                    <div class="error_txt div_2">
                        <?php echo form_error('email_address'); ?>
                    </div>
                <?php } ?>
                <br>
                <div class="input-container  div_2">
                    <input id="password" type="password" name="password" value="<?php  echo set_value('password'); ?>" required="required"/>
                    <label for="password">Contraseña</label>
                    <div class="bar"></div>
                </div>
                <?php if (form_error('password')) { ?>
                    <div class="error_txt div_2">
                        <?php echo form_error('password'); ?>
                    </div>
                <?php } ?>
                <div class="button-container div_2">
                    <button type="submit" class="submit_login" name="submit" value="submit"><span>Acceder</span></button>
                </div>
                
                <?php
                if(isServiceAccessOnly('sGmsJaFJE') && $company->saas_landing_page==1): ?>
                <p class="text-center div_signup_link div_2">
                    <a class="txt-color-primary div_signup_link_a" href="<?php echo base_url()?>#pricing"><?php echo lang('Signup'); ?></a>
                </p>
                <?php endif;?>
                <?php echo form_close();?>

            
            </div>
            <?php $system_version_number = $this->session->userdata('system_version_number');?>
            <p class="pull-right"></p>
            <div class="card alt">
                <!--<div class="toggle"></div>-->
                <h1 class="title">Register
                    <div class="close"></div>
                </h1>
                <form>
                    <div class="input-container">
                        <input type="text" id="Username" required="required"/>
                        <label for="Username">Username</label>
                        <div class="bar"></div>
                    </div>
                    <div class="input-container">
                        <input type="password" id="Password" required="required"/>
                        <label for="Password">Password</label>
                        <div class="bar"></div>
                    </div>
                    <div class="input-container">
                        <input type="password" id="Repeat Password" required="required"/>
                        <label for="Repeat Password">Repeat Password</label>
                        <div class="bar"></div>
                    </div>
                    <div class="button-container">
                        <button><span>Next</span></button>
                    </div>
                </form>
            </div>
            
        </div>
    </div>
</div>
<script src="<?php echo base_url();?>pwa.js?v=1.003"></script>
<script src="<?php echo base_url(); ?>frequent_changing/notify/toastr.js"></script>
</body>
</html>