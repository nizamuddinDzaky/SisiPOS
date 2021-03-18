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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,100,300,500">
    <link rel="stylesheet" href="<?= $assets ?>styles/register/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= $assets ?>styles/register/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?= $assets ?>styles/register/form-elements.css?v=<?= FORCAPOS_VERSION ?>">
    <link rel="stylesheet" href="<?= $assets ?>styles/register/style.css?v=<?= FORCAPOS_VERSION ?>">


    <!-- Favicon and touch icons -->
    <link rel="shortcut icon" href="assets/ico/favicon.png">
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

    i#show-password-confirm {
        right: 32px;
        position: absolute;
        top: 11px;
        cursor: pointer;
        color: #a3a3a3;
        z-index: 2;
        display: none;
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
</style>


<body>
<?php if (SERVER_QA) { ?>
        <div id="snackbar">QP SERVER</div>
    <?php } ?>

    <div class="kotak_register">
        <div style="padding-left: 6%; padding-right: 6%;">
            <?php if ($Settings->mmode) { ?>
                <div class="alert alert-warning">
                    <button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">&times;</span></button>
                    <?= lang('site_is_offline') ?>
                </div>
            <?php } ?>
            
        </div>
        <div class="col-sm-12 hiddens-md hiddens-lg text-center">
            <?php if ($Settings->logo2) {
                echo '<a href="' . base_url() . '"><img class="text-center on-media" src="' . base_url('assets/uploads/logos/logo3.png') . '" alt="' . $Settings->site_name . '" style="margin-bottom:0px; margin-top:6px;" /> </a>';
            } ?>
        </div>


        <form action="<?= base_url() . 'auth/sign_up' ?>" class="f1" novalidate role="form" method="post" style="padding-bottom: 10px;" accept-charset="utf-8">

            <!-- <h3>Register</h3> -->
            <p>Sign up to register ForcaPOS</p>
            <?php if ($error) { ?>
                <div class="alert alert-danger">
                    <button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">&times;</span></button>
                    <ul class="list-group" style="margin: 0; padding: 0; line-height: 1.3;"><?= $error; ?></ul>
                </div>
            <?php } ?>
            <?php if ($message) { ?>
                <div class="alert alert-success">
                    <button data-dismiss="alert" class="close" type="button">&times;</button>
                    <ul class="list-group" style="margin: 0; padding: 0; line-height: 1.3;"><?= $message; ?></ul>
                </div>
            <?php } ?>
            <div class="f1-steps">
                <div class="f1-progress">
                    <div class="f1-progress-line" data-now-value="16.66" data-number-of-steps="3" style="width: 16.66%;"></div>
                </div>
                <div class="f1-step active">
                    <div class="f1-step-icon"><i class="fa fa-user"></i></div>
                    <p>About</p>
                </div>
                <div class="f1-step">
                    <div class="f1-step-icon"><i class="fa fa-map-marker"></i></div>
                    <p>Address</p>
                </div>
                <div class="f1-step">
                    <div class="f1-step-icon"><i class="fa fa-key"></i></div>
                    <p>Account</p>
                </div>
                <!-- <div class="f1-step">
                    <div class="f1-step-icon"><i class="fa fa-cogs"></i></div>
                    <p>Plugins</p>
                </div> -->
            </div>

            <span id="waitingload" class="dataTables_processing" style="display: none;"></span>

            <fieldset>
                <!-- <h4>Tell us who you are:</h4> -->

                <div class="row">
                    <?php if (!isset($provider)) { ?>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="sr-only" for="f1-first-name">First name</label>
                                <input type="text" name="fname" placeholder="First name" id="fname" class="fname form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="sr-only" for="f1-last-name">Last name</label>
                                <input type="text" name="lname" placeholder="Last name" class="lname form-control" id="lname">
                            </div>
                        </div>
                    <?php } else {
                        echo form_hidden('lname', $last_name, 'id="lname"');
                    } ?>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="sr-only" for="f1-company">Company</label>
                            <input type="text" name="company" placeholder="Company" class="f1-company form-control" id="company">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="sr-only" for="f1-phone">Phone</label>
                            <input type="text" name="phone" placeholder="Phone" class="phone form-control number-only" id="phone">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12" style="margin-bottom: 1.5rem">
                        <p style="margin-bottom: 3px" id="gender">Gender</p>
                        <label class="container-radio">Male
                            <input type="radio" checked="checked" value="male" onBlur="checknext()" name="gender">
                            <span class="checkmark"></span>
                        </label>
                        <label class="container-radio">Female
                            <input type="radio" value="female" onBlur="checknext()" name="gender">
                            <span class="checkmark pink"></span>
                        </label>
                    </div>
                </div>

                <div class="row" style="margin-top: 10px;">
                    <div class="col-xs-6">
                        <div style="text-align: left;">
                            <a href="<?php echo base_url('login') ?>">
                                <button type="button" class="btn" style="background: transparent;color: #428BCA;font-weight: 1000;">Back to Login</button>
                            </a>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="f1-buttons">
                            <button type="button" class="btn btn-next">Next</button>
                        </div>
                    </div>
                </div>
            </fieldset>

            <!-- Address -->
            <fieldset>
                <!-- <h4>Tell us who you are:</h4> -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="controls">
                                <select name="provinsi" dropdownDirection="bottom" id="provinsi" class="form-control" onchange="setProvinsi(this.value,this.options[this.selectedIndex].innerHTML)" class="form-control select" required>
                                    <option value="">Choose Province</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="sr-only" for="f1-about-yourself">City</label>
                            <div class="controls">
                                <select name="kabupaten" id="kabupaten" onchange="setKabupaten(this.value,this.options[this.selectedIndex].innerHTML)" class="form-control select" required>
                                    <option value="">Choose City</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="sr-only" for="f1-about-yourself">District</label>
                            <div class="controls">
                                <select name="kecamatan" id="kecamatan" onchange="setKecamatan(this.value,this.options[this.selectedIndex].innerHTML)" class="form-control select">
                                    <option value="">Choose District</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="sr-only" for="f1-about-yourself">Postalcode</label>
                            <input type="text" name="postalcode" placeholder="Postalcode" class="postalcode form-control number-only" id="postalcode">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="sr-only" for="f1-about-yourself">Address</label>
                            <textarea name="address" placeholder="Address" class="address form-control" id="address"></textarea>
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-top: 10px;">
                    <div class="col-xs-6">
                        <div style="text-align: left;">
                            <button type="button" class="btn btn-previous">Previous</button>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="f1-buttons">
                            <button type="button" class="btn btn-next">Next</button>
                        </div>
                    </div>

                </div>
            </fieldset>

            <!-- Account -->
            <fieldset>
                <div class="row">
                    <div class="col-md-12">
                        <?php if (!isset($provider)) { ?>
                            <div class="form-group">
                                <label class="sr-only" for="f1-email">Email</label>
                                <input type="text" name="email" placeholder="Email" class="email f1-email form-control" onBlur="checkAvailabilityEmail(this.value)" id="email">
                                <span id="email-availability-status" style="font-size:14px;"></span>
                                <?php
                                    echo form_hidden('provider', 'email'); 
                                ?>
                            </div>
                        <?php } else {
                            echo form_hidden('fname', $first_name, 'id="fname"');
                            echo form_hidden('email', $email, 'id="email"');
                            echo form_hidden('provider', $provider, 'id="email"');
                        } ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-6">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="sr-only" for="f1-password">Password</label>
                            <input type="password" name="password" value="" onBlur="checknext()" placeholder="Password" class="f1-password form-control tip" id="password" required="required" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}" data-bv-regexp-message="At least 1 capital, 1 lowercase, 1 number and more than 6 characters long" data-original-title="" title="" data-bv-field="password">
                            <i id="show-password" class="fa fa-eye"></i>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="sr-only" for="f1-confirm-password">Confirm Password</label>
                            <input type="password" name="confirm_password" placeholder="Confirm Password" class="confirm_password f1-confirm-password form-control" id="confirm_password">
                            <i id="show-password-confirm" class="fa fa-eye"></i>
                       </div>
                    </div>
                    <div class="col-xs-12">
                        <div style="font-size: 14px;">
                            <small id='message'></small>
                        </div>
                    </div>
                </div>
                
                <!-- <div class="row" style="margin-top: 10px;">
                    <div class="col-xs-6">
                        <div style="text-align: left;">
                            <button type="button" class="btn btn-previous">Previous</button>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="f1-buttons">
                            <button type="button" class="btn btn-next">Next</button>
                        </div>
                    </div>

                </div> -->

                <?php if (!isset($provider)) { ?>
                    <input type="hidden" name="username" class="form-control" id="username" onBlur="checkAvailabilityUsername()" required>
                <?php } else { ?>
                    <input type="hidden" name="username" class="form-control" id="username" value="<?= $first_name . $last_name ?>">
                <?php } ?>
                <input id="latitude" name="latitude" type="hidden" />
                <input id="longitude" name="longitude" type="hidden" />
                
                <div class="row" style="margin-top: 10px;">
                    <div class="form-check" id="termcondition">
                        <label class="container-checkbox" style="font-size:14px;">I agree to Term and Condition ( <a href="<?= base_url("assets/documents/20200605_FORCA PoS_Terms_and_Conditions.pdf") ?>" target="_blank"><i class="fa fa-file-text"></i></a> )
                            <input type="checkbox" class="form-check-input" id="termcondition" onBlur="checknext()" name="termcondition" required>
                            <span class="checkmark-checkbox" id="termcondition-2"></span>
                        </label>

                    </div>
                </div>

                <div class="row" style="margin-top: 10px;">
                    <div class="col-xs-6">
                        <div style="text-align: left;">
                            <button type="button" class="btn btn-previous">Previous</button>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="f1-buttons">
                            <input type="hidden" name="ip_address" value="">
                            <button type="submit" id="regis" disabled="disabled" class="btn btn-submit next-one">Register</button>
                        </div>
                    </div>

                </div>

            </fieldset>

            <!-- <fieldset>
                <div class="row">
                    <div class="form-check" id="plugin">
                        <label class="container-checkbox"> Add Plugin
                            <input type="checkbox" class="form-check-input" id="ao" checked="checked" disabled="" >
                            <span class="checkmark-checkbox"></span>
                        </label>
                    </div>
                </div>

                <div class="row add_plugin">
                    <div class="col-md-1"></div>
                    <div class="col-md-11">
                        
                        <div class="form-group">
                            <?php if(!empty($plugin)){?>
                            <table style="width:100%; overflow:hidden;">
                                <?php foreach ($plugin_default as $v) { ?>
                                <tr>
                                    <td>
                                        <label class="container-checkbox" style="margin-bottom: 24px;">
                                            <input type="checkbox" checked="checked" class="form-check-input check_plugin" disabled="">
                                            <span class="checkmark-checkbox"></span>
                                        </label>
                                    </td>
                                    <td class="title-pluugins"><?= $v->name ?></td> 
                                    <td class="title-pluugins"><?= number_format($v->price) ?></td>
                                    <td class="title-pluugins"><?= $v->description ?></td>
                                </tr>
                                <?php } ?>

                                <?php foreach ($plugin as $v) { ?>
                                <tr>
                                    <td>
                                        <label class="container-checkbox" style="margin-bottom: 24px;">
                                            <input type="checkbox" value="<?= $v->id ?>" name="plugin[]" class="form-check-input check_plugin">
                                            <span class="checkmark-checkbox"></span>
                                        </label>
                                    </td>
                                    <td class="title-pluugins"><?= $v->name ?></td> 
                                    <td class="title-pluugins"><?= number_format($v->price) ?></td>
                                    <td class="title-pluugins"><?= $v->description ?></td>

                                </tr>
                                <?php } ?>
                            </table>
                            <?php }else{ ?>
                            <h4>No Plugin here, Click Register to submit</h4>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                
                <?php if (!isset($provider)) { ?>
                    <input type="hidden" name="username" class="form-control" id="username" onBlur="checkAvailabilityUsername()" required>
                <?php } else { ?>
                    <input type="hidden" name="username" class="form-control" id="username" value="<?= $first_name . $last_name ?>">
                <?php } ?>
                <input id="latitude" name="latitude" type="hidden" />
                <input id="longitude" name="longitude" type="hidden" />
                <div class="row">
                <div class="form-check" id="termcondition">
                    <label class="container-checkbox">I agree to Term and Condition ( <a href="<?= base_url() ?>document/term.pdf"><i class="fa fa-file-text"></i></a> )
                        <input type="checkbox" class="form-check-input" id="termcondition" onBlur="checknext()" name="termcondition">
                        <span class="checkmark-checkbox" id="termcondition-2"></span>
                    </label>

                </div>
                </div>

                <div class="row">
                    <div class="col-xs-6">
                        <div style="text-align: left;">
                            <button type="button" class="btn btn-previous">Previous</button>
                        </div>
                    </div>
                    <div class="col-xs-6">
                            <div class="f1-buttons">
                            <button type="submit" id="regis" class="btn btn-submit" disabled="disabled">Register</button>
                        </div>
                    </div>

                </div>
                
            </fieldset> -->
        
        </form>

        </div>


            <!-- Javascript -->
            <script src="<?= $assets ?>styles/register/js/jquery-1.11.1.min.js"></script>
            <script src="<?= $assets ?>styles/register/bootstrap/js/bootstrap.min.js"></script>
            <script src="<?= $assets ?>styles/register/js/jquery.backstretch.min.js"></script>
            <script src="<?= $assets ?>styles/register/js/retina-1.1.0.min.js"></script>
            <script src="<?= $assets ?>styles/register/js/scripts.js"></script>

            <script>

                var emailStatus = false;
                var passwordStatus = false;
                var termStatus = false;

                function checkRegisButton() {
                    if(emailStatus && passwordStatus && termStatus){
                        $('#regis').removeAttr('disabled');
                    } else {
                        $('#regis').attr('disabled', 'disabled');
                    }
                }

                // SHOW HIDE PASSWORD
                $("#password").on("keyup", function() {
                    if ($(this).val())
                        $("#show-password").show();
                    else
                        $("#show-password").hide();
                });
                $("#confirm_password").on("keyup", function() {
                    if ($(this).val())
                        $("#show-password-confirm").show();
                    else
                        $("#show-password-confirm").hide();
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
                $('#show-password-confirm').click(function() {
                    if ($(this).hasClass('fa-eye')) {
                        $('#confirm_password').attr('type', 'text');
                        $(this).removeClass('fa-eye');
                        $(this).addClass('fa-eye-slash');

                    } else {
                        $('#confirm_password').attr('type', 'password');
                        $(this).removeClass('fa-eye-slash');
                        $(this).addClass('fa-eye');
                    }
                })

                // $('#password').on('keyup', function() {

                // });
            </script>

            <script>
                $('#password, #confirm_password').on('keyup', function() {
                    passwordStatus = false;
                    var regex =  /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z]{8,}$/;
                    var pass = $('#password').val();
                    if (($('#password').val() !== '') && ($('#confirm_password').val() !== '')) {
                        if ($('#password').val().length <= 7) {
                            $('#message').html('Less than 8 characters').css('color', 'red');
                        } else if (!pass.match(regex)){
                            $('#message').html('<?= lang('pasword_hint') ?>').css('color', 'red');
                        } else if ($('#password').val() == $('#confirm_password').val()) {
                            $('#message').html('Matched').css('color', 'green');
                            passwordStatus = true;
                        } else if ($('#password').val() != $('#confirm_password').val()){
                            $('#message').html('Not Matched').css('color', 'red');
                        } 
                    } else {
                        $('#message').html('Please insert password').css('color', 'red');
                    }
                    checkRegisButton();
                });


                function isEmail(email) {
                    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                    return regex.test(email);
                }

                function checkAvailabilityEmail(email) {
                    $("#waitingload").show();
                    var result = isEmail(email);
                    jQuery.ajax({
                        url: "<?= site_url('auth/availableEmail') ?>",
                        data: 'email=' + $("#email").val(),
                        type: "POST",
                        success: function(data) {
                            if (result) {
                                console.log(data.trim());
                                
                                if(data.trim() == "0"){
                                    $("#email-availability-status").html(`<small class='status-not-available' style='color:red' > Email Not Available. Please Input Another Email</small>`);
                                    emailStatus = false;
                                } else if (data.trim() == "1") {
                                    $("#email-availability-status").html(`<small class='status-available' style='color:green'> Email Available.</small>`);
                                    emailStatus = true;
                                }
                                var mail = $("#email").val();
                                $("#username").val(mail);
                                // hopscotch.nextStep()

                            } else {
                                var a = "<small class='status-not-available' style='color:red' > Invalid email format.</small>";
                                $("#email-availability-status").html(a);
                                $('.next-one').attr("disabled", "disabled");
                                emailStatus = false;
                            }
                            $("#waitingload").hide();
                            checkRegisButton();
                        },
                        error: function() {}
                    });
                }


                function checkAvailabilityCompany() {
                    $("#waitingload").show();
                    // hopscotch.nextStep()
                    jQuery.ajax({
                        url: "<?= site_url('auth/availableCompany') ?>",
                        data: 'company=' + $("#company").val(),
                        type: "POST",
                        success: function(data) {
                            $("#company-availability-status").html(data);
                            $("#waitingload").hide();
                        },
                        error: function() {}
                    });
                }

                function checkAvailabilityUsername() {
                    $("#waitingload").show();
                    jQuery.ajax({
                        url: "<?= site_url('auth/availableUsername') ?>",
                        data: 'username=' + $("#username").val(),
                        type: "POST",
                        success: function(data) {
                            $("#username-availability-status").html(data);
                            $("#waitingload").hide();
                        },
                        error: function() {}
                    });
                }

                $.getJSON('<?php echo base_url(); ?>daerah/getProvinsi', function(data) {
                    var output = "";
                    output += '<option value="" data-foo="">Choose Province</option>';
                    $.each(data, function(key, val) {

                        output += '<option value="' + val.province_name + '" data-foo="">' + val.province_name + '</option>';
                    });
                    $("#provinsi").html(output);
                    $("#provinsi").change(function() {
                        // hopscotch.nextStep()
                    });

                });

                $(document).ready(function() {

                    //START - Get Public IP
                    $.getJSON('https://api.ipify.org?format=json', function(data) {
                        $("input[name=ip_address]").val(data.ip);
                    });
                    //END - Get Public IP


                    $('.add_plugin').hide();
                    $('#ao').change(function() {
                        if ($(this).is(':checked')) {
                            $('.add_plugin').show(600);
                            $('#modal_plugin').modal('toggle');

                        } else {
                            $('.add_plugin').hide(600);
                            $('.check_plugin').removeAttr('checked');
                        }
                    });

                    $('[name="termcondition"]').change(function() {
                        if ($(this).is(':checked')) {
                            termStatus = true;
                        } else {
                            termStatus = false;
                        }
                        checkRegisButton();
                    });

                });
                $('input.number-only').bind('keypress', function(e) {
                    return !(e.which != 8 && e.which != 0 &&
                        (e.which < 48 || e.which > 57) && e.which != 46);
                });

                <?php if ($this->session->userdata('registration') && $this->session->userdata('additional_registration')) {
                    $personal_data = $this->session->userdata('registration');
                    $additional_personal_data = $this->session->userdata('additional_registration'); ?>

                    $('#fname').val('<?= $personal_data->first_name ?>');
                    $('#lname').val('<?= $personal_data->last_name ?>');
                    $('#email').val('<?= $personal_data->email ?>').blur();
                    $('#phone').val('<?= $personal_data->phone ?>');
                    $('#address').val('<?= $personal_data->address ?>');
                    $('#company').val('<?= $personal_data->company ?>');
                    $('#gender').val('<?= $personal_data->gender ?>');
                    $('#postalcode').val('<?= $additional_personal_data->postal_code ?>');
                    // $('#latitude').val('<?= $additional_personal_data->latitude ?>');
                    // $('#longitude').val('<?= $additional_personal_data->longitude ?>');
                    $.getJSON('<?php echo base_url(); ?>daerah/getProvinsi', function(data) {
                        var output = "";
                        output += '<option value="" data-foo="">Choose Province</option>';
                        $.each(data, function(key, val) {
                            output += '<option value="' + val.province_name + '" data-foo="">' + val.province_name + '</option>';
                        });
                        $("#provinsi").html(output);

                        $('select[name=provinsi]').val('<?= $personal_data->country ?>').change();
                        setTimeout(function() {
                            console.log("masuk sini");
                            
                            $('select[name=kabupaten]').val('<?= $personal_data->city ?>').change();
                            setTimeout(function() {
                                console.log("masuk sini 2");
                                $('select[name=kecamatan]').val('<?= $personal_data->state ?>').change();
                            }, 2000);
                        }, 2000);
                        
                    });
                <?php $this->session->unset_userdata('additional_registration');
                    $this->session->unset_userdata('registration');
                } ?>
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