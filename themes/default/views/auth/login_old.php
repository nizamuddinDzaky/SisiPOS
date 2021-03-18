<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= $title ?></title>
    <script type="text/javascript">if (parent.frames.length !== 0) {
        top.location = '<?=site_url('pos')?>';
    }</script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="<?= $assets ?>images/icon.png"/>
    <link href="<?= $assets ?>styles/theme.css?v=<?=FORCAPOS_VERSION?>" rel="stylesheet"/>
    <link href="<?= $assets ?>styles/style.css?v=<?=FORCAPOS_VERSION?>" rel="stylesheet"/>
    <link href="<?= $assets ?>styles/helpers/login.css?v=<?=FORCAPOS_VERSION?>" rel="stylesheet"/>
    <link href="<?= $assets ?>guide/css/hopscotch.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="<?= $assets ?>js/jquery-2.0.3.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/jquery-migrate-1.2.1.min.js"></script>
    <!--[if lt IE 9]>
    <script src="<?= $assets ?>js/respond.min.js"></script>
    <![endif]-->

    <!-- [START google_config] -->
  <!-- **********************************************
       * TODO(DEVELOPER): Use your Client ID below. *
       ********************************************** -->
    <meta name="google-signin-client_id" content="826096717681-rq05asf8l5ckshcolifq0kv76esuf7sk.apps.googleusercontent.com">
    <meta name="google-signin-cookiepolicy" content="single_host_origin">
    <meta name="google-signin-scope" content="profile email">
    <!-- [END google_config] -->

    <!-- Google Sign In -->
</head>

<body class="login-page">
<?php if (SERVER_QA) { ?>
    <div id="snackbar">QP SERVER</div>
<?php } ?>
    <noscript>
        <div class="global-site-notice noscript">
            <div class="notice-inner">
                <p>
                    <strong>JavaScript seems to be disabled in your browser.</strong><br>You must have JavaScript enabled in
                    your browser to utilize the functionality of this website.
                </p>
            </div>
        </div>
    </noscript>
    <div class="page-back">
        <div class="text-center">
            <?php if ($Settings->logo2) {
                echo '<img src="' . base_url('assets/uploads/logos/' . $Settings->logo2) . '" alt="' . $Settings->site_name . '" style="margin-bottom:10px;" />';
            } ?>
        </div>

        <div id="login">
            <div class="container">
                <div class="login-form-div">
                    <div class="login-content">
                        <?php if ($Settings->mmode) { ?>
                            <div class="alert alert-warning">
                                <button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">&times;</span></button>
                                <?= lang('site_is_offline') ?>
                            </div>
                        <?php }
                        if ($error) { ?>
                            <div class="alert alert-danger">
                                <button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">&times;</span></button>
                                <ul class="list-group"><?= $error; ?></ul>
                            </div>
                            <?php
                        } 
                        if ($message) { ?>
                            <div class="alert alert-success">
                                <button data-dismiss="alert" class="close" type="button">&times;</button>
                                <ul class="list-group"><?= $message; ?></ul>
                            </div>
                        <?php } ?>
                        <div class="div-title col-sm-12">
                            <!--<h3 class="text-primary"><?= lang('login_to_your_account') ?></h3> -->
                                <h2 class="text-center">Masuk Forca Pos</h2>
                          	<p class="text-center fs-14">Belum punya akun Forca PoS? Daftar
                                    <a href="<?= base_url(); ?>auth/sign_up" class="text-danger underline" id="link-page">di sini</a>
                                </p>
                                <!--<p class="text-center fs-14">Atau <a href="<?= base_url(); ?>login#resend_email" class="resend_email_link underline">di sini</a> untuk yang ingin mendapatkan email lagi.</p>-->
                        </div>
                        <div class="col-sm-5">
                            <a id="gplus-button" data-onsuccess="onSignIn" style="text-decoration: none;">
                                <button class="btn btn-block btn-white mb-10 login_btn" id="plus_btn" type="button">
                                                <i class="reg-icon-google"></i>&nbsp;&nbsp;&nbsp;Masuk dengan Google
                                </button>
                            </a>
                            <a id="fb-button" onclick="fb_login();" style="text-decoration: none;">
                                <button class="btn btn-block btn-facebook mb-10 login_btn" id="fb_btn" type="button" >
                                    <i class="icon-facebook icon-large"></i>&nbsp;&nbsp;&nbsp;Masuk dengan Facebook
                                </button>
                            </a>
                            <a style="text-decoration: none;" href="https://play.google.com/store/apps/details?id=id.forca.posretail">
                                <button class="btn btn-block btn-white mb-10 login_btn" type="button" >
                                    <i class="icon-playstore"></i>
                                </button>
                            </a>
<!--                            <a id="link-page" style="text-decoration: none;">
                                    <button class="btn btn-block btn-white mb-10 login_btn" type="button" >
                                            <i class="fa fa-mobile fa-lg"> </i> &nbsp;&nbsp;&nbsp;Masuk dengan Nomor Ponsel
                                    </button>
                            </a>
                            <a id="yahoo-button" style="text-decoration: none;">
                                    <button class="btn btn-block btn-yahoo login_btn" id="yahoo_btn" type="button">
                                            <i class="fa fa-yahoo"></i>&nbsp;&nbsp;&nbsp;Masuk dengan Yahoo
                                    </button>
                            </a>-->
                        </div>
                        <div class="col-sm-2">
                            <div class="line1"></div>
                            <p class="text-center mt-20 mb-20 small-gray">atau</p>
                            <div class="line2"></div>
                        </div>
                        <?php echo form_open("auth/login", 'class="login" data-toggle="validator"'); ?>
                        <div class="col-sm-5">
                            <div class="textbox-wrap form-group">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                    <input type="text" value="<?= $this->session->userdata('email') ? $this->session->userdata('email') : "" ?>" required="required" class="form-control" name="identity" id="identity"
                                    placeholder="<?= lang('username') ?>"/>
                                </div>
                            </div>
                            <div class="textbox-wrap form-group">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-key"></i></span>
                                    <input type="password" value="" required="required" class="form-control " name="password" id="password"
                                    placeholder="<?= lang('pw') ?>"/>
                                    <i id="show-password" class="fa fa-eye"></i>
                                </div>
                            </div>
                            <?php if ($Settings->captcha) { ?>
                            <div class="col-sm-12">
                                <div class="textbox-wrap form-group">
                                    <div class="row">
                                        <div class="col-sm-6 div-captcha-left">
                                            <span class="captcha-image"><?php echo $image; ?></span>
                                        </div>
                                        <div class="col-sm-6 div-captcha-right">
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    <a href="<?= base_url(); ?>auth/reload_captcha" class="reload-captcha">
                                                        <i class="fa fa-refresh"></i>
                                                    </a>
                                                </span>
                                                <?php echo form_input($captcha); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php  } /* echo $recaptcha_html; */  ?>
                            <div class="form-action col-sm-12 mb-10">
                                <div class="checkbox">
                                    <div class="custom-checkbox">
                                        <?php echo form_checkbox('remember', '1', FALSE, 'id="remember"'); ?>
                                    </div>
                                    <span class="checkbox-text pull-left"><label for="remember"><?= lang('remember_me') ?></label></span>
                                </div>
                                <div class="pull-right">
                                    <a href="#forgot_password" class="text-danger forgot_password_link"><?= lang('forgot_your_password') ?></a>
                                </div>
                            </div>
<!--                        <div class="col-sm-12">-->
                            <a id="login-button" style="text-decoration: none;">
                                <button class="btn btn-block btn-success mb-10 login_btn" id="plus_btn" type="submit">
                                    <i class="fa fa-sign-in"></i>&nbsp;&nbsp;&nbsp;<?= lang('login') ?>
                                </button>
                            </a>
                        <?php echo form_close(); ?>
                            <div class="clearfix"></div>
                        </div>
                        <?php $attributes=array('name'=>'auth_google','id'=>'auth_google');
                        echo form_open('auth/login',$attributes);?>
                            <input type="hidden" name="fname" id="fname" value="">
                            <input type="hidden" name="lname" id="lname" value="">
                            <input type="hidden" name="email" id="email" value="">
                            <input type="hidden" name="provider" id="provider" value="">
                            <input type="hidden" name="login_hint" id="login_hint" value="">
                            <input type="hidden" name="uuid" id="uuid" value="">
                            <input type="hidden" name="picture" id="picture" value="">
                            <input type="submit" name="submit" id="clickSubmit" style="display: none;">
                        <?php echo form_close();?>
                    </div>
                </div>
            </div>
<!--            <div class="login-form-div">
                <div class="col-sm-12">
                    <h2 class="text-center">Unduh Aplikasi kami di:</h2>
                    <div class="badge-mobile">
                        <a href="https://play.google.com/store/apps/details?id=id.forca.posretail">
                            <img src="<?php echo $assets ?>images/forca_login/temukandigp.png" alt="Google Play">
                        </a>
                    </div>
                </div>
            </div>-->
        </div>
        <div id="forgot_password" style="display: none;">
            <div class=" container">
                <div class="login-form-div">
                    <div class="login-content">
                        <?php if ($error) { ?>
                            <div class="alert alert-danger">
                                <button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">&times;</span></button>
                                <ul class="list-group"><?= $error; ?></ul>
                            </div>
                        <?php }
                        if ($message) { ?>
                            <div class="alert alert-success">
                                <button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">&times;</span></button>
                                <ul class="list-group"><?= $message; ?></ul>
                            </div>
                        <?php } ?>
                        <div class="div-title col-sm-12">
                            <h3 class="text-primary"><?= lang('forgot_password') ?></h3>
                        </div>
                        <?php echo form_open("auth/forgot_password", 'class="login" data-toggle="validator"'); ?>
                        <div class="col-sm-12">
                            <p>
                                <?= lang('type_email_to_reset'); ?>
                            </p>
                            <div class="textbox-wrap form-group">
                                <div class="input-group">
                                    <span class="input-group-addon "><i class="fa fa-envelope"></i></span>
                                    <input type="email" name="forgot_email" class="form-control "
                                    placeholder="<?= lang('email_address') ?>" required="required"/>
                                </div>
                            </div>
                            <div class="form-action">
                                <a class="btn btn-success pull-left login_link" href="#login">
                                    <i class="fa fa-chevron-left"></i> <?= lang('back') ?>
                                </a>
                                <button type="submit" class="btn btn-primary pull-right">
                                    <?= lang('submit') ?> &nbsp;&nbsp; <i class="fa fa-envelope"></i>
                                </button>
                            </div>
                        </div>
                        <?php echo form_close(); ?>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
        </div> 
        <div id="resend_email" style="display: none">
            <div class="container">
                <div class="login-form-div">
                    <div class="login-content">
                        <?php if ($error) { ?>
                            <div class="alert alert-danger">
                                <button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">&times;</span></button>
                                <ul class="list-group"><?= $error; ?></ul>
                            </div>
                        <?php }
                        if ($message) { ?>
                            <div class="alert alert-success">
                                <button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">&times;</span></button>
                                <ul class="list-group"><?= $message; ?></ul>
                            </div>
                        <?php } ?>
                        
                        <div class="div-title col-sm-12">
                            <h3 class="text-primary"><?= lang('resend_email_verification') ?></h3>
                        </div>
                        <?php echo form_open("auth/resend_email", 'class="login" data-toggle="validator"'); ?>
                        <div class="col-sm-12">
                            <p>
                                <?= lang('type_email_to_verification'); ?>
                            </p>
                            <div class="textbox-wrap form-group">
                                <div class="input-group">
                                    <span class="input-group-addon "><i class="fa fa-envelope"></i></span>
                                    <input type="email" name="resend_email" class="form-control "
                                    placeholder="<?= lang('email_address') ?>" required="required"/>
                                </div>
                            </div>
                            <div class="form-action">
                                <a class="btn btn-success pull-left login_link" href="#login">
                                    <i class="fa fa-chevron-left"></i> <?= lang('back') ?>
                                </a>
                                <button type="submit" class="btn btn-primary pull-right">
                                    <?= lang('submit') ?> &nbsp;&nbsp; <i class="fa fa-envelope"></i>
                                </button>
                            </div>
                        </div>
                        <?php echo form_close(); ?>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
.footer {
   position: fixed;
   left: 0;
   bottom: 0;
   width: 100%;

   color: black;
   text-align: center;
}
@media(max-width: 991px){
    .footer {
        display: none !important;
    }

}

</style>
<div class="footer">
    <div class="pull-left">
        <p> &#169; <?=FORCAPOS_COPYRIGHT?>, PT. Sinergi Informatika Semen Indonesia (<a href="https://pos.forca.id/manualbook/Manual%20Book_Web_Distributor_v1.pdf"><?=FORCAPOS_VERSION?></a>)</p>
    </div>
</div>	
    <script src="<?= $assets ?>js/jquery.js"></script>
    <script src="<?= $assets ?>js/bootstrap.min.js"></script>
    <script src="<?= $assets ?>js/jquery.cookie.js"></script>
    <script src="<?= $assets ?>js/login.js?v=<?=FORCAPOS_VERSION?>"></script>
    <script src="<?php echo $assets ?>guide/js/hopscotch.js"></script>
    <script>
        var tour = {
            id: "guide-login",
            onClose: function(){
                localStorage.setItem('tour-homepage',true);
            },
            onEnd: function() {
                localStorage.setItem('tour-homepage',true);
            },
            steps: [
                {
                    title: "Isikan e-mail anda",
                    content: "",
                    target: "identity",
                    placement: "bottom"
                },
                {
                    title: "Isikan sandi anda",
                    content: "",
                    target: "password",
                    placement: "bottom"
                }
            ]
        };
        if (hopscotch.getState() === "guide-home:5") {
            hopscotch.startTour(tour);
        }
    </script>

</body>
<script type="text/javascript">
        $(document).ready(function () {
            localStorage.clear();
            var hash = window.location.hash;
            if (hash && hash != '') {
                $("#login").hide();
                $(hash).show();
            }
        });
//    function onSignIn(googleUser) {
//        document.getElementById("fname").value=googleUser.w3.ofa;
//        document.getElementById("lname").value=googleUser.w3.wea;
//        // if(googleUser.w3.U3 == <?=$this->session->userdata('email')?>){
//            document.getElementById("email").value=googleUser.w3.U3;
//            document.getElementById("provider").value=googleUser.Zi.idpId;
//            document.getElementById("login_hint").value=googleUser.Zi.login_hint;
//            document.getElementById("uuid").value=googleUser.El;
//            document.getElementById("picture").value=googleUser.w3.Paa;    
//        // }
//    }

    function isUserEqual(googleUser, firebaseUser) {
        if (firebaseUser) {
            var providerData = firebaseUser.providerData;
            for (var i = 0; i < providerData.length; i++) {
                if (providerData[i].providerId === firebase.auth.GoogleAuthProvider.PROVIDER_ID && providerData[i].uid === googleUser.getBasicProfile().getId()) {
                    return true;
                }
            }
        }
        return false;
    }

    function handleSignOut() {
        // var googleAuth = gapi.auth2.getAuthInstance();
        // googleAuth.signOut().then(function() {
            // firebase.auth().signOut();
            // firebase.auth().signOut().then(function(){
                // console.log('Signed Out');
            // }, function(error){
                // console.error('signOut Error', error);
            // });
        // });

    }
        //     var googleAuth = gapi.auth2.getAuthInstance();
        // googleAuth.signOut().then(function() {
        //     firebase.auth().signOut();
        // });
        // googleAuth.disconnect();
    window.onload = function() {
        // var googleAuth = gapi.auth2.getAuthInstance();
        firebase.auth().signOut();
        googleAuth.disconnect();
        // var googleAuth = gapi.auth2.getAuthInstance();
        // googleAuth.signOut().then(function() {
        //     firebase.auth().signOut();
        // });
        // googleAuth.disconnect();
    };
//$('#login-Google').click(function(e) {
//    $("#clickSubmit").click();
//});
</script>
 <script src="https://apis.google.com/js/platform.js" async defer></script>
  <script src="https://apis.google.com/js/api:client.js"></script>
  <script>
  var googleUser = {};
  var startApp = function() {
    gapi.load('auth2', function(){
      // Retrieve the singleton for the GoogleAuth library and set up the client.
      auth2 = gapi.auth2.init({
        client_id: '826096717681-rq05asf8l5ckshcolifq0kv76esuf7sk.apps.googleusercontent.com',
        cookiepolicy: 'single_host_origin',
        // Request scopes in addition to 'profile' and 'email'
        //scope: 'additional_scope'
      });
      attachSignin(document.getElementById('gplus-button'));
    });
  };

  function attachSignin(element) {
    auth2.attachClickHandler(element, {},
        function(googleUser) {
           $("#fname").val(googleUser.w3.ofa);
           $("#lname").val(googleUser.w3.wea);
           $("#email").val(googleUser.w3.U3);
           $("#provider").val(googleUser.Zi.idpId);
           $("#login_hint").val(googleUser.Zi.login_hint);
           $("#uuid").val(googleUser.El);
           $("picture").val(googleUser.w3.Paa);
           $("#clickSubmit").click();
        }, function(error) {
          alert(JSON.stringify(error, undefined, 2));
        });
  }  
  </script>
  <script>startApp();</script>


<!-- Facebook -->
<!-- [START facebookconfig] -->
<script src="//connect.facebook.net/en_US/sdk.js"></script>
<script>
    FB.init({
        /**********************************************************************
        * TODO(Developer): Change the value below with your Facebook app ID. *
        **********************************************************************/
        appId      : '303308856816744',
        status     : true,
        xfbml      : true,
        version    : 'v2.6'
    });
        // [START_EXCLUDE silent]
        // Observe the change in Facebook login status
        // [START facebookauthlistener]
        FB.Event.subscribe('auth.authResponseChange', checkLoginState);
        // [END facebookauthlistener]
        // [END_EXCLUDE]
        function fb_login(){
        FB.login(function(response) {

            if (response.authResponse) {
                console.log('Welcome!  Fetching your information.... ');
                //console.log(response); // dump complete info
                access_token = response.authResponse.accessToken; //get access token
                user_id = response.authResponse.userID; //get FB UID
                
                FB.api('/me', function(response) {
                    user_email = response.email; //get user email
              // you can store this data into your database
                });

            } else {
                //user hit cancel button
                console.log('User cancelled login or did not fully authorize.');

            }
        }, {
            scope: 'publish_stream,email'
        });
    }
$(function() {
    var e = document.createElement('script');
    e.src = document.location.protocol + '//connect.facebook.net/en_US/all.js';
    e.async = true;
    document.getElementById('fb-root').appendChild(e);
}());
</script>
<!-- [END facebookconfig] -->

<script>
    // SHOW HIDE PASSWORD 
$("#password").on("keyup",function(){
    if($(this).val())
        $("#show-password").show();
    else
        $("#show-password").hide();
    });

$('#show-password').click(function(){
  if ($(this).hasClass('fa-eye')) {
    $('#password').attr('type','text');
    $(this).removeClass('fa-eye');
    $(this).addClass('fa-eye-slash');

  }else{
    $('#password').attr('type','password');
    $(this).removeClass('fa-eye-slash');
    $(this).addClass('fa-eye');
  }
})




</script>
</html>
