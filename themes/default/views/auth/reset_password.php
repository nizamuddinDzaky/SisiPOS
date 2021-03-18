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
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Roboto:400,100,300,500">
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

    i#show-password-new {
        right: 14px;
        position: absolute;
        top: 11px;
        cursor: pointer;
        color: #a3a3a3;
        z-index: 10;
        /* display: none; */
    }

    i#show-password-new-confirm {
        right: 14px;
        position: absolute;
        top: 11px;
        cursor: pointer;
        color: #a3a3a3;
        z-index: 10;
        /* display: none; */
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

a:hover {
    cursor: pointer;
}
</style>


<body class="login-page">

<?php if (SERVER_QA) { ?>
        <div id="snackbar">QP SERVER</div>
    <?php } ?>

    <div class="kotak_login" style="padding-top:20px">

        <div class="col-sm-12 hiddens-md hiddens-lg text-center">
            <?php if ($Settings->logo2) {
                echo '<a href="' . base_url() . '"><img class="text-center on-media" src="' . base_url('assets/uploads/logos/logo3.png') . '" alt="' . $Settings->site_name . '" style="margin-bottom:0px; margin-top:6px;" /> </a>';
            } ?>
        </div>

        <?php echo form_open('auth/reset_password/' . $code, 'class="f1" novalidate role="form" method="post" accept-charset="utf-8"'); ?>

            <!-- <h3>Register</h3> -->
            <p><?php echo sprintf(lang('reset_password_email'), $identity_label); ?></p>

            <?php if ($Settings->mmode) { ?>
                <div class="alert alert-warning">
                    <button data-dismiss="alert" class="close" type="button">×</button>
                    <?= lang('site_is_offline') ?>
                </div>
            <?php }
            if ($error) { ?>
                <div class="alert alert-danger">
                    <button data-dismiss="alert" class="close" type="button">×</button>
                    <ul class="list-group" style="margin: 0; padding: 0; line-height: 1.3;"><?= $error; ?></ul>
                </div>
            <?php }
            if ($message) { ?>
                <div class="alert alert-success">
                    <button data-dismiss="alert" class="close" type="button">×</button>
                    <ul class="list-group" style="margin: 0; padding: 0; line-height: 1.3;"><?= $message; ?></ul>
                </div>
            <?php } ?>

            <!-- <h4>Tell us who you are:</h4> -->
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-key"></i></span>
                            <?php echo form_input($new_password); ?>
                            <i id="show-password-new" class="fa fa-eye"></i>
                        </div>
                    </div>

                </div>
                <div class="col-md-12">

                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-key"></i></span>
                            <?php echo form_input($new_password_confirm); ?>
                            <i id="show-password-new-confirm" class="fa fa-eye"></i>
                        </div>
                    </div>

                </div>

                <div class="col-xs-12">
                    <div style="font-size: 14px; line-height: 1.3;">
                        <small id='message'><?= lang('pasword_hint') ?></small>
                    </div>
                </div>
            </div>

            <?php echo form_input($user_id); ?>
            <?php echo form_hidden($csrf); ?>

            <div class="form-action clearfix" style="margin-top: 20px;">
                <a href="<?= site_url('login') ?>" class="pull-left" style="font-size: 12px; line-height: 30px; color: #428BCA; font-weight: 600;">
                    <?= lang('back_to_login') ?>
                </a>
                <button type="submit" class="btn btn-primary pull-right" disabled style="line-height: 30px;height:30px;background: #428BCA;">
                    <i class="fa fa-send"></i>&nbsp;&nbsp;   
                    <?= lang('submit') ?>   
                </button>
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


        <?php echo form_close(); ?>

    </div>


    <!-- Javascript -->
    <script src="<?= $assets ?>styles/register/js/jquery-1.11.1.min.js"></script>
    <script src="<?= $assets ?>styles/register/bootstrap/js/bootstrap.min.js"></script>
    <script src="<?= $assets ?>styles/register/js/jquery.backstretch.min.js"></script>
    <script src="<?= $assets ?>styles/register/js/retina-1.1.0.min.js"></script>
    <script src="<?= $assets ?>styles/register/js/scripts.js"></script>

    <script>

        $('#show-password-new').click(function() {
            if ($(this).hasClass('fa-eye')) {
                $('#new').attr('type', 'text');
                $(this).removeClass('fa-eye');
                $(this).addClass('fa-eye-slash');

            } else {
                $('#new').attr('type', 'password');
                $(this).removeClass('fa-eye-slash');
                $(this).addClass('fa-eye');
            }
        })

        $('#show-password-new-confirm').click(function() {
            if ($(this).hasClass('fa-eye')) {
                $('#new_confirm').attr('type', 'text');
                $(this).removeClass('fa-eye');
                $(this).addClass('fa-eye-slash');

            } else {
                $('#new_confirm').attr('type', 'password');
                $(this).removeClass('fa-eye-slash');
                $(this).addClass('fa-eye');
            }
        })

        $('#new, #new_confirm').on('keyup', function() {
            var regex =  /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z]{8,}$/;
            var pass = $('#new').val();
            var pass_confirm = $('#new_confirm').val();
            $(`button[type="submit"]`).attr('disabled', 'disabled');
            if ((pass !== '') && (pass_confirm !== '')) {
                if (pass.length <= 7) {
                    $('#message').html('Less than 8 characters').css('color', 'red');
                } else if (!pass.match(regex)){
                    $('#message').html('<?= lang('pasword_hint') ?>').css('color', 'red');
                } else if (pass == pass_confirm) {
                    $('#message').html('Matched').css('color', 'green');
                    $(`button[type="submit"]`).removeAttr('disabled');
                } else if (pass != pass_confirm){
                    $('#message').html('Not Matched').css('color', 'red');
                } 
            } else {
                if(pass === ''){
                    $('#message').html('Please insert New Password').css('color', 'red');
                } else if(pass_confirm === ''){
                    $('#message').html('Please insert New Password Confirm').css('color', 'red');
                }
            }
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