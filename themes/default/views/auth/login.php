    <?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?></title>
    <script type="text/javascript">
        if (parent.frames.length !== 0) {
            top.location = '<?= site_url('pos') ?>';
        }
        var base_url = '<?= base_url() ?>';
    </script>

    <!-- CSS -->
    <link href="<?= $assets ?>styles/helpers/bootstrap.o.css" rel="stylesheet">
    <link rel="shortcut icon" href="<?= $assets ?>images/icon.png" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,100,300,500">
    <link rel="stylesheet" href="<?= $assets ?>styles/register/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= $assets ?>styles/register/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?= $assets ?>styles/register/form-elements.css?v=<?= FORCAPOS_VERSION ?>">
    <link rel="stylesheet" href="<?= $assets ?>styles/register/style.css?v=<?= FORCAPOS_VERSION ?>">


    <!-- Favicon and touch icons -->
    <!-- <link rel="shortcut icon" href="assets/ico/favicon.png"> -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="assets/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="assets/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="assets/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="assets/ico/apple-touch-icon-57-precomposed.png">

    <script type="text/javascript" src="<?= $assets ?>js/jquery-2.0.3.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/daerah.js?v=<?= FORCAPOS_VERSION ?>"></script>

</head>

<style>
    body,
    html {

        width: 100%;
        height: 100%;
        margin: 0;
        padding: 0;
        display: table;
        background-image: url("<?php echo base_url('assets/images/bg_1.jpg') ?>");
        background-size: cover;
        background-position: center;
        font-family: 'Lato', 'Helvetica Neue', Helvetica, Arial, sans-serif;
    }

    body {
        display: table-cell;
        vertical-align: middle;
    }

    img.text-center.on-media {
        max-width: 163px;
    }

    .btn.disabled,
    .btn[disabled],
    fieldset[disabled] .btn {
        cursor: auto !important;
    }

    i#show-password {
        right: 32px;
        position: absolute;
        top: 11px;
        cursor: pointer;
        color: #a3a3a3;
        z-index: 2;
        display: none;
    }

    .login-page .checkbox {
        margin-bottom: 0;
        margin-top: 6px;
        padding-left: 1px;
    }

    p {
        font-size: 14px;
    }
    .fade-scale {
        transform: scale(0);
        opacity: 0;
        -webkit-transition: all .25s linear;
        -o-transition: all .25s linear;
        transition: all .25s linear;
    }

    .fade-scale.in {
        opacity: 1;
        transform: scale(1);
    }
        
#snackbar {
  visibility: visible; /* Hidden by default. Visible on click */
  min-height: 100px; /* Set a default minimum width */
  /*margin-left: -125px;  Divide value of min-width by 2 */
  background-color: #428bca; /* Black background color */
  color: #fff; /* White text color */
  text-align: center; /* Centered text */
  border-radius: 2px; /* Rounded borders */
  border-top-left-radius: 5px;
  border-bottom-left-radius: 5px;
  padding: 7px; /* Padding */
  position: fixed; /* Sit on top of the screen */
  z-index: 1; /* Add a z-index if needed */
  right: 0px; /* Center the snackbar */
  bottom: 40%; /* 30px from the bottom */
  writing-mode: vertical-rl;
  text-orientation: upright;
  FONT-SIZE: 12PX;
  line-height: 1.3;
}

a:hover, label:hover {
    cursor: pointer;
    color: #0b4f8a!important;
}

.custom_submit:hover:not([disabled]){ 
    opacity: 1!important;
    background-color: #0b4f8a!important;
}
.custom_submit:active:not([disabled]) { 
    opacity: 1!important; 
    background-color: #0b4f8a!important;
}
.custom_submit:focus:not([disabled]),
.custom_submit:active:focus:not([disabled]),
.custom_submit.active:focus:not([disabled]) { 
    opacity: 1!important;
    background-color: #0b4f8a!important;
}

</style>


<body class="login-page">

<?php if (SERVER_QA) { ?>
        <div id="snackbar">QP SERVER</div>
    <?php } ?>

    <div class="kotak_login">

        <div class="col-sm-12 hiddens-md hiddens-lg text-center">
            <?php if ($Settings->logo2) {
                echo '<a href="' . base_url() . '"><img class="text-center on-media" src="' . base_url('assets/uploads/logos/logo3.png') . '" alt="' . $Settings->site_name . '" style="margin-bottom:0px; margin-top:6px;" /> </a>';
            } ?>
        </div>

        <form action="<?= base_url() . 'auth/login' ?>" class="f1" novalidate role="form" method="post" accept-charset="utf-8" id="form_login">

            <!-- <h3>Register</h3> -->
            <p>Login to access ForcaPOS</p>

            <?php if ($Settings->mmode) { ?>
                <div class="alert alert-warning">
                    <button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">&times;</span></button>
                    <?= lang('site_is_offline') ?>
                </div>
            <?php }
            if ($error) { ?>
                <div class="alert alert-danger">
                    <button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">&times;</span></button>
                    <ul class="list-group" style="margin: 0; padding: 0; line-height: 1.3;"><?= $error; ?></ul>
                </div>
            <?php
            }
            if ($message) { ?>
                <div class="alert alert-success">
                    <button data-dismiss="alert" class="close" type="button">&times;</button>
                    <ul class="list-group" style="margin: 0; padding: 0; line-height: 1.3;"><?= $message; ?></ul>
                </div>
            <?php } ?>
            <span id="waitingload" class="dataTables_processing" style="display: none;"></span>

            <!-- <h4>Tell us who you are:</h4> -->
            <div class="row">
                <div class="col-md-12">

                    <div class="form-group">

                        <input type="text" name="identity" placeholder="<?= lang('username') ?>" value="<?= $this->session->userdata('email') ? $this->session->userdata('email') : "" ?>" class="email form-control" id="identity" autofocus>
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col-md-12">

                    <div class="form-group">

                        <input type="password" value="" required="required" class="form-control " name="password" id="password" placeholder="<?= lang('pw') ?>" />
                        <i id="show-password" class="fa fa-eye"></i>
                    </div>

                </div>
            </div>

            <div class="row">

                <div class="col-xs-6">
                    <div class="form-check" id="plugin">
                        <label class="container-checkbox-login" style="font-size: 11px; color: #428BCA; font-weight: 600;"> <?= lang('remember_me') ?>
                            <input type="checkbox" name="remember" class="form-check-input" id="remember">
                            <span class="checkmark-checkbox" style="top: 6px!important; left: 6px!important;"></span>
                        </label>
                    </div>
                </div>

                <div class="col-xs-6">
                    <div class="forgot" style="text-align:right;">
                        <a href="#forgot_password" class="forgot_password_link" style="font-size: 11px; color: #428BCA; font-weight: 600;" data-toggle="modal" data-target="#exampleModalCenter">
                            <?= lang('forgot_your_password') ?>
                        </a>

                    </div>
                </div>

            </div>
            
            <input type="hidden" name="ip_address" value="">

            <div class="row" style="margin-bottom: 10px;margin-top: 10px;">
                <div class="col-xs-12" style="text-align:left; ">
                    <div style="display: flex;justify-content: space-between;">
                        <a href="<?php echo base_url('auth/sign_up') ?>" style="font-size: 12px; color: #428BCA; font-weight: 600;">Create new account</a>
                
                        <a id="login-button" style="text-decoration: none;">
                            <button class="btn btn-block mb-10 login_btn custom_submit" id="plus_btn" type="submit" style="line-height: 30px; height:30px; background: #428BCA;" disabled>
                                <i class="fa fa-sign-in"></i>&nbsp;&nbsp;&nbsp;<?= lang('login') ?>
                            </button>
                        </a>
                    </div>
                
                </div>
            </div>
            <hr style="
                margin-top: 30px !important;
                margin-bottom: 15px !important;
            ">
           <div class="footer">
                <div class="row">
                    <div class="col-xs-12">
                        <div style="display: flex;justify-content: space-between;">
                            <span style="font-size:11px;">v<?= FORCAPOS_VERSION ?></span>
                            <span style="font-size:11px;">Developed by SISI © <?= FORCAPOS_COPYRIGHT ?></span>
                        </div>
                    </div>
                </div>
           </div>
        </form>

    </div>


    <!-- Modal -->
    <div class="modal fade-scale" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <?php echo form_open("auth/forgot_password", 'class="login" data-toggle="validator"'); ?>
                <div class="modal-content">
                    <div class="modal-header">
                        <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button> -->
                        <h3 class="text-center title-content" style="text-transform: none; margin: 0;" id="exampleModalLongTitle" ><?= lang('forgot_password') ?></h3>
                        
                    </div>
                    <div class="modal-body">
                        <?php if ($error) { ?>
                            <div class="alert alert-danger">
                                <button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">&times;</span></button>
                                <ul class="list-group" style="margin: 0; padding: 0; line-height: 1.3;"><?= $error; ?></ul>
                            </div>
                        <?php }
                        if ($message) { ?>
                            <div class="alert alert-success">
                                <button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">&times;</span></button>
                                <ul class="list-group" style="margin: 0; padding: 0; line-height: 1.3;"><?= $message; ?></ul>
                            </div>
                        <?php } ?>
                        
                        <div class="row">
                            <div class="col-sm-12">
                                <p style="line-height: 1.3; text-align: center;">
                                    <?= lang('type_email_to_reset'); ?>
                                </p>
                                <div class="textbox-wrap form-group">
                                    <div class="input-group">
                                        <span class="input-group-addon "><i class="fa fa-envelope"></i></span>
                                        <input type="email" name="forgot_email" class="form-control " placeholder="<?= lang('email_address') ?>" required="required" />
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                        
                    </div>
                    <div class="modal-footer">
                        <div class="form-action">
                            <a href="javascript:void(0)" style="font-size: 12px; color: #428BCA; line-height: 40px; font-weight: 600;" class="pull-left" data-dismiss="modal">
                                <i class="fa fa-chevron-left"></i> &nbsp; Close
                            </a>
                            <button type="submit" class="btn btn-primary pull-right custom_submit" style="line-height: 30px;height:30px;background: #428BCA;">
                                <i class="fa fa-envelope"></i>&nbsp; <?= lang('submit') ?>
                            </button>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            <?php echo form_close(); ?>
        </div>
    </div>

    <div class="modal fade-scale" id="resend_email" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <?php echo form_open("auth/resend_email", 'class="login" data-toggle="validator"'); ?>
                <div class="modal-content">
                    <div class="modal-header">
                        <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button> -->
                        <h3 class="text-center title-content" style="text-transform: none; margin: 0;" id="exampleModalLongTitle" ><?= lang('resend_email_verification') ?></h3>
                        
                    </div>
                    <?php if ($error) { ?>
                        <div class="alert alert-danger">
                            <button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">&times;</span></button>
                            <ul class="list-group" style="margin: 0; padding: 0; line-height: 1.3;"><?= $error; ?></ul>
                        </div>
                    <?php }
                    if ($message) { ?>
                        <div class="alert alert-success">
                            <button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">&times;</span></button>
                            <ul class="list-group" style="margin: 0; padding: 0; line-height: 1.3;"><?= $message; ?></ul>
                        </div>
                    <?php } ?>
                    <div class="row">
                        <div class="col-sm-12">
                            <p style="line-height: 20px; margin-top:10px;">
                                <?= lang('type_email_to_verification'); ?>
                            </p>
                            <div class="textbox-wrap form-group">
                                <div class="input-group">
                                    <span class="input-group-addon "><i class="fa fa-envelope"></i></span>
                                    <input type="email" name="resend_email" class="form-control " placeholder="<?= lang('email_address') ?>" required="required" />
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <div class="form-action">
                            <a href="javascript:void(0)" style="font-size: 12px; color: #428BCA; line-height: 40px; font-weight: 600;" class="pull-left" data-dismiss="modal">
                                <i class="fa fa-chevron-left"></i> &nbsp; Close
                            </a>
                            <button type="submit" class="btn btn-primary pull-right custom_submit">
                                <i class="fa fa-envelope"></i>&nbsp; <?= lang('submit') ?>
                            </button>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            <?php echo form_close(); ?>
        </div>
    </div>

    <!-- Javascript -->
    <script src="<?= $assets ?>styles/register/js/jquery-1.11.1.min.js"></script>
    <script src="<?= $assets ?>styles/register/bootstrap/js/bootstrap.min.js"></script>
    <script src="<?= $assets ?>styles/register/js/jquery.backstretch.min.js"></script>
    <script src="<?= $assets ?>styles/register/js/retina-1.1.0.min.js"></script>
    <script src="<?= $assets ?>styles/register/js/scripts.js"></script>

    <script>
        var identityStatus = false;
        var passwordStatus = false;
    
        $(document).ready(function() {
            //START - Get Public IP
            $.getJSON('https://api.ipify.org?format=json', function(data) {
                $("input[name=ip_address]").val(data.ip);
            });
            //END - Get Public IP
            
            // SHOW HIDE PASSWORD
            $("#password").on("keyup input change blur", function() {
                passwordStatus = false;
                if ($(this).val()){
                    $("#show-password").show();
                    passwordStatus = true;
                } else {
                    $("#show-password").hide();
                }
                checkLoginButton();
            }).keyup();

            $("#identity").on("keyup input change blur", function() {
                identityStatus = false;
                if ($(this).val()){
                    identityStatus = true;
                }
                checkLoginButton();
            }).keyup();
        
            checkLoginButton();
        });

        function checkLoginButton() {
            if(identityStatus && passwordStatus){
                $('#plus_btn').removeAttr('disabled');
            } else {
                $('#plus_btn').attr('disabled', 'disabled');
            }
        }
        
        

        $('#show-password').click(function() {
            if ($(this).hasClass('fa-eye')) {
                $('#password').attr('type', 'text');
                $(this).removeClass('fa-eye');
                $(this).addClass('fa-eye-slash');

            } else {
                $('#password').attr('type', 'password');
                $(this).removeClass('fa-eye-slash');
                $(this).addClass('fa-eye');
            }
        })

        $("#form_login").submit(function (e) {
            $("#plus_btn").attr('disabled','disabled');
            $("#plus_btn").html(`<i class="fa fa-circle-o-notch fa-spin"></i>&nbsp;&nbsp;&nbsp;<?= lang('login') ?>`);
        });
    </script>
    <script>
        (function(w, d, u) {
            var s = d.createElement('script');
            s.async = true;
            s.src = u + '?' + (Date.now() / 60000 | 0);
            var h = d.getElementsByTagName('script')[0];
            h.parentNode.insertBefore(s, h);
        })(window, document, 'https://cdn.bitrix24.com/b11907515/crm/site_button/loader_4_87vex7.js');
    </script>


</body>

</html>