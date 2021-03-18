<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
<head>
	<title><?= $title ?></title>
    <script type="text/javascript">
        if (parent.frames.length !== 0) {
            top.location = '<?= site_url('pos') ?>';
        }
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="<?= $assets ?>images/icon.png" />
    <link href="<?= $assets ?>styles/theme.css?v=<?= FORCAPOS_VERSION ?>" rel="stylesheet" />
    <link href="<?= $assets ?>styles/style.css?v=<?= FORCAPOS_VERSION ?>" rel="stylesheet" />
    <link href="<?= $assets ?>styles/helpers/login.css?v=<?= FORCAPOS_VERSION ?>" rel="stylesheet" />
    <link href="<?= $assets ?>guide/css/hopscotch.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="<?= $assets ?>js/jquery-2.0.3.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/jquery-migrate-1.2.1.min.js"></script>

    <meta name="google-signin-client_id" content="826096717681-rq05asf8l5ckshcolifq0kv76esuf7sk.apps.googleusercontent.com">
    <meta name="google-signin-cookiepolicy" content="single_host_origin">
    <meta name="google-signin-scope" content="profile email">
</head>
<body class="login-page">
    <?php if (SERVER_QA) { ?>
        <div id="snackbar">QP SERVER</div>
    <?php } ?>
<style>
    body, html {
    
        width: 100%;
        height: 100%;
        margin: 0;
        padding: 0;
        display:table;
        background-image: url("<?php echo base_url('assets/images/bg_1.jpg') ?>");
        background-size: cover;
    	background-position: center;
    	font-family: 'Lato', 'Helvetica Neue', Helvetica, Arial, sans-serif;
    }
    body {
        display:table-cell;
        vertical-align:middle;
    }
 
    .kotak_login{
		width: 350px;
		background: white;
        margin:auto;
		display:table;
		text-align: center;
	}
	.logo{
		max-width: 100px;
	}

	h1.title-login {
	    margin-bottom: 10px;
	    font-size: 23px;
	    font-weight: 500;
	    margin-top: 0px;
	}

	.box-login{
		margin-top: 20px;
	}
	input#identity {
	    width: 350px;
	    padding: 15px 15px;
	    border-radius: 10px;
	    border: solid 2px #c1c1c1ad;
	}
	input#password {
	    width: 350px;
	    padding: 15px 15px;
	    border-radius: 10px;
	    border: solid 2px #c1c1c1ad;
	}
	.box-title {
    	margin-bottom: 18px;
	}
	.forgot{
		text-decoration: none;
		color: #3975a8;
		font-weight: 600;
    	font-size: 14px;
	}
	.new_account{
		text-decoration: none;
		color: #3975a8;
		font-weight: 600;
    	font-size: 14px;
	}
	.login_btn{
		color: #fff;
    	background-color: #3975a8;
    	border: 1px solid #2e6697;
	}
	.btn{
		padding: 6px 20px;
	}


</style>

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

<body>

	<div class="kotak_login">
		<div class="box-login">
			<img class="logo" src="login_logo.png">
		</div>
		<div class="box-title">
			<h1 class="title-login">Sign In</h1>
			<span>Login to Access ForcaPOS</span>
		</div>	
		<?php echo form_open("auth/login", 'class="login" data-toggle="validator"'); ?>
	        <div style="padding:5px 20px;">
	            <input type="text" name="identity" id="identity" placeholder="<?= lang('username') ?>" required="required" placeholder="username" value="<?= $this->session->userdata('email') ? $this->session->userdata('email') : "" ?>"/>
	        </div>
	        <div style="padding:5px 20px;">
	            <input type="password" value="" required="required" name="password" id="password" placeholder="<?= lang('pw') ?>"  />\
				<i id="show-password" class="fa fa-eye"></i>
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

	        <div style="padding:10px 20px;display: flex;justify-content: space-between;">

                <div>
                    <?php echo form_checkbox('remember', '1', FALSE, 'id="remember"','claas="remember"'); ?>
	        		<span><label for="remember"><?= lang('remember_me') ?></label></span>
	        	</div>

	            <a href="#forgot_password" class="forgot"><?= lang('forgot_your_password') ?></a>
	        </div>

	        <div style="padding:10px 20px;display: flex;justify-content: space-between;">
	        	 <a href="<?php echo base_url('auth/sign_up') ?>" class="new_account">Create New account</a>
	        	<button class="btn login_btn" id="login_btn" type="submit">
	        		Login
	        	</button>
	           
	        </div>

		<?php echo form_close(); ?>
		<?php $attributes = array('name' => 'auth_google', 'id' => 'auth_google');
		echo form_open('auth/login', $attributes); ?>
		<input type="hidden" name="fname" id="fname" value="">
		<input type="hidden" name="lname" id="lname" value="">
		<input type="hidden" name="email" id="email" value="">
		<input type="hidden" name="provider" id="provider" value="">
		<input type="hidden" name="login_hint" id="login_hint" value="">
		<input type="hidden" name="uuid" id="uuid" value="">
		<input type="hidden" name="picture" id="picture" value="">
		<input type="submit" name="submit" id="clickSubmit" style="display: none;">
		<?php echo form_close(); ?>
		
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
							<h1 class="text-center title-content" style="margin-top: 3.5rem"><?= lang('forgot_password') ?></h1>
						</div>
						<?php echo form_open("auth/forgot_password", 'class="login" data-toggle="validator"'); ?>
						<div class="col-sm-12">
							<p>
								<?= lang('type_email_to_reset'); ?>
							</p>
							<div class="textbox-wrap form-group">
								<div class="input-group">
									<span class="input-group-addon "><i class="fa fa-envelope"></i></span>
									<input type="email" name="forgot_email" class="form-control " placeholder="<?= lang('email_address') ?>" required="required" />
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
									<input type="email" name="resend_email" class="form-control " placeholder="<?= lang('email_address') ?>" required="required" />
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

</body>
<script>
	$(document).ready(function() {
        localStorage.clear();
        var hash = window.location.hash;
        if (hash && hash != '') {
            $("#login").hide();
            $(hash).show();
        }
    });

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

</script>
<!-- <script src="https://apis.google.com/js/platform.js" async defer></script> -->
<script src="https://apis.google.com/js/api:client.js"></script>
<script>
    var googleUser = {};
    var startApp = function() {
        gapi.load('auth2', function() {
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
            },
            function(error) {
                alert(JSON.stringify(error, undefined, 2));
            });
    }

</script>

<script>
    startApp();
</script>

<!-- Facebook -->
<!-- [START facebookconfig] -->
<script src="//connect.facebook.net/en_US/sdk.js"></script>
<script>
    FB.init({
        /**********************************************************************
         * TODO(Developer): Change the value below with your Facebook app ID. *
         **********************************************************************/
        appId: '303308856816744',
        status: true,
        xfbml: true,
        version: 'v2.6'
    });
    // [START_EXCLUDE silent]
    // Observe the change in Facebook login status
    // [START facebookauthlistener]
    FB.Event.subscribe('auth.authResponseChange', checkLoginState);
    // [END facebookauthlistener]
    // [END_EXCLUDE]
    function fb_login() {
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
    $("#password").on("keyup", function() {
        if ($(this).val())
            $("#show-password").show();
        else
            $("#show-password").hide();
    });

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
</html>