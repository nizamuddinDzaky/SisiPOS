<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title><?= $title ?></title>
    <script type="text/javascript">
        if (parent.frames.length !== 0) {
            top.location = '<?= site_url('pos') ?>';
        }
        var base_url = '<?=base_url()?>';
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="<?= $assets ?>images/icon.png" />
    <link href="<?= $assets ?>styles/theme.css?v=<?= FORCAPOS_VERSION ?>" rel="stylesheet" />
    <link href="<?= $assets ?>styles/style.css?v=<?= FORCAPOS_VERSION ?>" rel="stylesheet" />
    <link href="<?= $assets ?>styles/helpers/login.css?v=<?= FORCAPOS_VERSION ?>" rel="stylesheet" />
    <link href="<?php echo $assets ?>guide/css/hopscotch.css" rel="stylesheet" />
    <script src="<?php echo $assets ?>guide/js/hopscotch.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/jquery-2.0.3.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/daerah.js?v=<?= FORCAPOS_VERSION ?>"></script>
    <style type="text/css">
        html,
        body {
            height: 100%;
        }

        html {
            display: table;
            margin: auto;
        }

        body {
            display: table-cell;
            vertical-align: middle;
        }

        textarea {
            resize: none;
        }

        .form-horizontal .form-group {
            margin-right: 0;
            margin-left: 0;
        }

        /* Create a custom radio button */
        /* The container */
        .container-radio {
            display: inline;
            position: relative;
            padding-left: 24px;
            margin-bottom: 12px;
            cursor: pointer;
            font-size: 14px;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            font-weight: 400;
            padding-top: 3px;
            margin-right: 20px;
        }

        /* Hide the browser's default radio button */
        .container-radio input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        /* Create a custom radio button */
        .checkmark {
            position: absolute;
            top: 0;
            left: 0;
            height: 20px;
            width: 20px;
            background-color: #eee;
            border-radius: 50%;
        }

        /* On mouse-over, add a grey background color */
        .container-radio:hover input~.checkmark {
            background-color: #ccc;
        }

        /* When the radio button is checked, add a blue background */
        .container-radio input:checked~.checkmark {
            background-color: #2196F3;
        }

        .container-radio input:checked~.pink {
            background-color: #EC407A;
        }

        /* Create the indicator (the dot/circle - hidden when not checked) */
        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
        }

        /* Show the indicator (dot/circle) when checked */
        .container-radio input:checked~.checkmark:after {
            display: block;
        }

        /* Style the indicator (dot/circle) */
        .container-radio .checkmark:after {
            top: 7px;
            left: 7px;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: white;
        }

        .container-checkbox {
            display: block;
            position: relative;
            padding-left: 29px;
            margin-bottom: 12px;
            cursor: pointer;
            font-size: 16px;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        /* Hide the browser's default checkbox */
        .container-checkbox input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }

        /* Create a custom checkbox */
        .checkmark-checkbox {
            position: absolute;
            top: 0;
            left: 0;
            height: 20px;
            width: 20px;
            background-color: #eee;
        }

        /* On mouse-over, add a grey background color */
        .container-checkbox:hover input~.checkmark-checkbox {
            background-color: #ccc;
        }

        /* When the checkbox is checked, add a blue background */
        .container-checkbox input:checked~.checkmark-checkbox {
            background-color: #2196F3;
        }

        /* Create the checkmark/indicator (hidden when not checked) */
        .checkmark-checkbox:after {
            content: "";
            position: absolute;
            display: none;
        }

        /* Show the checkmark when checked */
        .container-checkbox input:checked~.checkmark-checkbox:after {
            display: block;
        }

        /* Style the checkmark/indicator */
        .container-checkbox .checkmark-checkbox:after {
            left: 8px;
            top: 4px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 3px 0;
            -webkit-transform: rotate(45deg);
            -ms-transform: rotate(45deg);
            transform: rotate(45deg);
        }

        @media (min-width: 416px) {
            .reg-cont {
                padding: 0 40px
            }
        }
    </style>
    <script type="text/javascript">
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
                        $("#email-availability-status").html(data);
                        var mail = $("#email").val();
                        $("#username").val(mail);
                        hopscotch.nextStep()
                    } else {
                        var a = "<span class='status-not-available' style='color:red' > Invalid email format.</span>";
                        $("#email-availability-status").html(a);
                    }
                    $("#waitingload").hide();
                },
                error: function() {}
            });
        }

        function checkAvailabilityCompany() {
            $("#waitingload").show();
            hopscotch.nextStep()
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

        function checknext() {
            $("#waitingload").show();
            hopscotch.nextStep()
        }

        var sign_up = {
            id: "sign_up",
            onClose: function() {
                localStorage.setItem('tour-sign_up', true);
            },
            steps: [{
                title: "E-mail",
                content: "Masukkan email anda !!",
                target: "email",
                placement: "left",
            }, {
                title: "Nama Depan",
                content: "Masukkan nama depan anda !!",
                target: "fname",
                placement: "top"
            }, {
                title: "Nama Belakang",
                content: "Masukkan nama belakang anda !!",
                target: "lname",
                placement: "top"
            }, {
                title: "Nama Perusahaan",
                content: "Masukkan nama perusahaan anda !!",
                target: "company",
                placement: "top"
            }, {
                title: "Nomor Telepon",
                content: "Masukkan nomor telepon anda !!",
                target: "phone",
                placement: "top"
            }, {
                title: "Jenis Kelamin",
                content: "Pilih jenis kelamin anda !!",
                target: "gender",
                placement: "top"
            }, {
                title: "Alamat",
                content: "Masukkan alamat anda !!",
                target: "address",
                placement: "left"
            }, {
                title: "Provinsi",
                content: "Dari provinsi mana anda berasal ?",
                target: "provinsi",
                placement: "top"
            }, {
                title: "Kota",
                content: "Dari kota mana anda berasal ?",
                target: "kabupaten",
                placement: "top"
            }, {
                title: "Wilayah",
                content: "Dari wilayah mana anda berasal ?",
                target: "kecamatan",
                placement: "top"
            }, {
                title: "Kode Pos",
                content: "Berapa kode pos wilayah anda ?",
                target: "postalcode",
                placement: "top"
            }, {
                title: "Kata sandi",
                content: "Masukkan kata sandi Anda dan selalu ingat kata sandi anda !!",
                target: "password",
                placement: "top"
            }, {
                title: "Konfirmasi Sandi",
                content: "Masukkan kata sandi sekali lagi untuk mengonfirmasi kata sandi anda !!",
                target: "confirm_password",
                placement: "top"
            }, {
                title: "Syarat dan ketentuan",
                content: "Dengan menyetujui syarat dan ketentuan berikut, Anda dapat melanjutkan ke tahap berikutnya !!",
                target: "termcondition-2",
                placement: "top"
            }, {
                title: "Daftar",
                content: "Daftar sekarang 😊",
                target: "regis",
                placement: "left"
            }, {
                title: "Setel ulang",
                content: "Jika Anda mengalami kesalahan saat mengisi data, silakan tekan tombol berikut untuk mengatur ulang data yang telah Anda masukkan !!",
                target: "reset",
                placement: "left"
            }]
        };
        if (!localStorage.getItem('tour-sign_up')) {
            // Start the tour!
            hopscotch.startTour(sign_up);
        }
    </script>
</head>

<body style="width: 50%">

    <?php if (SERVER_QA) { ?>
        <div id="snackbar">QP SERVER</div>
    <?php } ?>

    <div id="modal-loading" style="display: none;">
        <div class="blackbg"></div>
        <div class="loader"></div>
    </div>
    <div class="content">
        <div class="row" style="margin:15px;">
            <div class="col-md-6 hidden-sm hidden-xs">
                <div class="full" style="background-image: url('<?php echo base_url('assets/images/bg_1.jpg') ?>');">
                    <div class="text-center auths" style="padding: 0 90px 0 100px;">
                        <?php if ($Settings->logo2) {
                            echo '<a href="' . base_url() . '"><img src="' . base_url('themes/default/assets/images/Logo.png') . '" alt="' . $Settings->site_name . '" style="margin-bottom:10px;" /> </a>';
                        } ?>
                        <h1 style="color: white;font-size: 24px">Boosting Your Business Performance</h1>
                        <h3 style="color: white;margin-bottom: 2rem">Forca Point Of Sales is an online
                            inventory management application for
                            cashier and manager store</h3>
                        <hr>
                        <a href="<?php echo base_url('login') ?>" class="" style="color: white">Login</a>
                        <span style="color: white"> • </span>
                        <a href="<?php echo base_url('auth/sign_up') ?>" style="color: white">Register</a>
                    </div>
                    <div class="footer-login">
                        <p> &#169; <?= FORCAPOS_COPYRIGHT ?>, PT. Sinergi Informatika Semen Indonesia (<a href="https://pos.forca.id/manualbook/Manual%20Book_Web_Distributor_v1.pdf"><?= FORCAPOS_VERSION ?></a>)</p>
                    </div>
                </div>



            </div>
            <div class="col-md-6">
                <div style="padding-left: 6%; padding-right: 6%;">
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
                    <?php } ?>
                    <?php if ($message) { ?>
                        <div class="alert alert-success">
                            <button data-dismiss="alert" class="close" type="button">&times;</button>
                            <ul class="list-group"><?= $message; ?></ul>
                        </div>
                    <?php } ?>
                </div>
                <div class="col-sm-12 hiddens-md hiddens-lg text-center">
                    <?php if ($Settings->logo2) {
                        echo '<a href="' . base_url() . '"><img class="text-center on-media" src="' . base_url('assets/uploads/logos/logo3.png') . '" alt="' . $Settings->site_name . '" style="margin-bottom:10px;" /> </a>';
                    } ?>
                </div>
                <!-- <div class="" style="margin-top: 3rem"></div> -->
                <h1 class="text-center register title-content" style="margin-top: 2rem; margin-bottom: 3rem; font-size: 25px">Register New Account</h1>
                <div class="row">
                    <div class="col-lg-12 reg-cont" style="">
                        <!-- <p class="intro-text">
                            Silahkan Lengkapi Data Anda
                        </p> -->
                        <span id="waitingload" class="dataTables_processing" style="display: none;"></span>
                        <?php
                        $attributes = array('class' => 'form-horizontal', 'data-toggle' => 'validator', 'role' => 'form');
                        echo form_open("auth/sign_up", $attributes); ?>
                        <div class="col-md-12">
                            <?php if (!isset($provider)) { ?>
                                <div class="form-group">
                                    Email*
                                    <div class="controls">
                                        <input type="email" name="email" class="form-control" onBlur="checkAvailabilityEmail(this.value)" id="email" required><span id="email-availability-status"></span>
                                        <?php
                                        // echo form_input('email','','class="form-control" id="email" required ');
                                        echo form_hidden('provider', 'email'); ?>
                                    </div>
                                </div>
                            <?php } else {
                                echo form_hidden('fname', $first_name, 'id="fname"');
                                echo form_hidden('email', $email, 'id="email"');
                                echo form_hidden('provider', $provider, 'id="email"');
                            } ?>
                            <?php if (!isset($provider)) { ?>
                                <div class="row">
                                    <div class="col-md-6">

                                        <div class="form-group">
                                            First Name*
                                            <div class="controls">
                                                <?php echo form_input('fname', '', 'class="form-control" id="fname" onBlur="checknext()" required '); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            Last Name*
                                            <div class="controls">
                                                <?php echo form_input('lname', '', 'class="form-control" id="lname" onBlur="checknext()" required '); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            <?php } else {
                                echo form_hidden('lname', $last_name, 'id="lname"');
                            } ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        Company Name*
                                        <div class="controls">
                                            <?php echo form_input('company', '', ' class="form-control" onBlur="checknext()" id="company" required'); ?>
                                            <span id="company-availability-status" style="position:right"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        Phone*
                                        <div class="controls">
                                            <input type="text" name="phone" id="phone" onBlur="checknext()" class="form-control number-only" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="">
                                    <div class="col-md-6" style="margin-bottom: 1.5rem">
                                        <p style="margin-bottom: 3px" id="gender">Gender*</p>
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
                                <div class="col-md-12">
                                    <div class="form-group">
                                        Address*
                                        <div class="controls">
                                            <!-- <textarea name="address" id="address" class="form-control" rows="3" required></textarea> -->
                                            <textarea name="address" id="address" onBlur="checknext()" class="form-control" rows="3" required></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        Province*
                                        <div class="controls">
                                            <select name="provinsi" id="provinsi" onchange="setProvinsi(this.value,this.options[this.selectedIndex].innerHTML)" class="form-control select" required>
                                                <option value="">Choose Province</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        City*
                                        <div class="controls">
                                            <select name="kabupaten" id="kabupaten" onchange="setKabupaten(this.value,this.options[this.selectedIndex].innerHTML)" class="form-control select" required>
                                                <option value="">Choose City</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        District*
                                        <div class="controls">
                                            <select name="kecamatan" id="kecamatan" onchange="setKecamatan(this.value,this.options[this.selectedIndex].innerHTML)" class="form-control select">
                                                <option value="">Choose District</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        Postalcode*
                                        <div class="controls">
                                            <input name="postalcode" id="postalcode" onBlur="checknext()" class="form-control number-only" required />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        Password*
                                        <div class="controls">
                                            <input type="password" name="password" value="" onBlur="checknext()" class="form-control tip" id="password" required="required" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}" data-bv-regexp-message="At least 1 capital, 1 lowercase, 1 number and more than 6 characters long" data-original-title="" title="" data-bv-field="password">

                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        Confirm Password*
                                        <div class="controls">
                                            <input type="password" name="confirm_password" value="" onBlur="checknext()" class="form-control" id="confirm_password" required="required" data-bv-identical="true" data-bv-identical-field="password" data-bv-identical-message="The password and confirm password are not the same" data-bv-field="confirm_password">
                                        </div>
                                        <span id='message'></span>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <span class="help-block"><?= lang('pasword_hint') ?></span>
                                </div>


                            </div>

                            <!-- <div class="form-group">
                                Gender*
                                <div class="controls">
                                    <?php
                                    $gndr = array('male' => 'male', 'female' => 'female');
                                    echo form_dropdown('gender', $gndr, (isset($_POST['gender']) ? $_POST['gender'] : 'male'), 'class="form-control" id="gender" required');

                                    ?>
                                </div>
                            </div> -->
                            <?php if (!isset($provider)) { ?>
                                <input type="hidden" name="username" class="form-control" id="username" onBlur="checkAvailabilityUsername()" required>
                            <?php } else { ?>
                                <input type="hidden" name="username" class="form-control" id="username" value="<?= $first_name . $last_name ?>">
                            <?php } ?>
                            <input id="latitude" name="latitude" type="hidden" />
                            <input id="longitude" name="longitude" type="hidden" />
                            <div class="form-check" id="termcondition">
                                <label class="container-checkbox">I agree to Term and Condition ( <a href="<?= base_url() ?>document/term.pdf"><i class="fa fa-file-text"></i></a> )
                                    <input type="checkbox" class="form-check-input" id="termcondition" onBlur="checknext()" name="termcondition">
                                    <span class="checkmark-checkbox" id="termcondition-2"></span>
                                    <!--                                <label class="form-check-label" for="termcondition"></label>-->
                                </label>

                            </div>
                            <div class="form-group">
                                <br>
                                <!--<button class="btn btn-danger" value="Reset" name="reset" id="reset"> Reset</button> -->
                                <a href="<?php echo base_url('login') ?>" class="hiddens-lg hiddens-md pull-left" style="margin-top: 8px">Back to Login</a>

                                <input type="submit" name="regis" id="regis" value="Register" class="btn btn-primary pull-right" disabled="disabled" style="margin-left: 5px">
                                <input type="reset" name="reset" id="reset" value="Reset" class="btn btn-danger pull-right" style="margin-left: 20px">


                            </div>
                            <!-- <div>
                            <div id="map-canvas" style="width: 100%; height: 19em"></div>
                        </div> -->
                        </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>


            </div>
        </div>

    </div>
</body>
<script type="text/javascript">

    $('#password, #confirm_password').on('keyup', function() {
        if (($('#password').val() !== '') && ($('#confirm_password').val() !== '')) {
            if ($('#password').val().length <= 6) {
                $('#message').html('Less than 6 characters').css('color', 'red');
            } else if ($('#password').val() == $('#confirm_password').val()) {
                $('#message').html('Matched').css('color', 'green');
            } else
                $('#message').html('Not Matched').css('color', 'red');
        } else {
            $('#message').html('Please insert password').css('color', 'red');
        }
    });

    $(document).ready(function() {
        $('[name="termcondition"]').change(function() {
            if ($(this).is(':checked')) {
                // Do something...
                $('#regis').removeAttr('disabled');
            } else {
                $('#regis').attr('disabled', 'disabled');
            }
        });
        $('input.number-only').bind('keypress', function(e) {
            return !(e.which != 8 && e.which != 0 &&
                (e.which < 48 || e.which > 57) && e.which != 46);
        });
        $.getJSON('<?php echo base_url(); ?>daerah/getProvinsi', function(data) {
            var output = "";
            output += '<option value="" data-foo="">Choose Province</option>';
            $.each(data, function(key, val) {

                output += '<option value="' + val.province_name + '" data-foo="">' + val.province_name + '</option>';
            });
            $("#provinsi").html(output);
            $("#provinsi").change(function() {
                hopscotch.nextStep()
            });

        });
        $(':input[required]:visible').keyup(function(event) {

            var empty = false;
            $(':input[required]:visible').each(function() {
                if ($(this).val().length == 0) {
                    empty = true;
                }
            });

            if (empty) {
                $('#regis').attr('disabled', 'disabled');
            } else {
                $('#regis').removeAttr('disabled');
            }
        });
    });
    $('#reset').click(function() {
        location.reload();
    });
    <?php if ($this->session->userdata('registration') && $this->session->userdata('additional_registration')) {
        $personal_data = $this->session->userdata('registration');
        $additional_personal_data = $this->session->userdata('additional_registration'); ?>

        $('#fname').val('<?= $personal_data->first_name ?>');
        $('#lname').val('<?= $personal_data->last_name ?>');
        $('#email').val('<?= $personal_data->email ?>');
        $('#phone').val('<?= $personal_data->phone ?>');
        $('#address').val('<?= $personal_data->address ?>');
        $('#company').val('<?= $personal_data->company ?>');
        $('#gender').val('<?= $personal_data->gender ?>');
        $.getJSON('<?php echo base_url(); ?>daerah/getProvinsi', function(data) {
            var output = "";
            output += '<option value="" data-foo="">Choose Province</option>';
            $.each(data, function(key, val) {
                output += '<option value="' + val.province_name + '" data-foo="">' + val.province_name + '</option>';
            });
            $("#provinsi").html(output);

            $('select[name=provinsi]').val('<?= $personal_data->country ?>').change();
            setTimeout(function() {
                $('select[name=kabupaten]').val('<?= $personal_data->city ?>').change();

            }, 500);
            setTimeout(function() {
                $('select[name=kecamatan]').val('<?= $personal_data->state ?>').change();
            }, 1000);
        });

        $('#postalcode').val('<?= $additional_personal_data->postal_code ?>');
        // $('#latitude').val('<?= $additional_personal_data->latitude ?>');
        // $('#longitude').val('<?= $additional_personal_data->longitude ?>');
    <?php $this->session->unset_userdata('additional_registration');
        $this->session->unset_userdata('registration');
    } ?>
</script>

</html>