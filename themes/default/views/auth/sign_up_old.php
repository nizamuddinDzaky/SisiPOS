<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= $title ?></title>
    <script type="text/javascript">
        if (parent.frames.length !== 0) {
            top.location = '<?=site_url('pos')?>';
        }
        var base_url = '<?=base_url()?>';
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="<?= $assets ?>images/icon.png"/>
    <link href="<?= $assets ?>styles/theme.css?v=<?=FORCAPOS_VERSION?>" rel="stylesheet"/>
    <link href="<?= $assets ?>styles/style.css?v=<?=FORCAPOS_VERSION?>" rel="stylesheet"/>
    <link href="<?= $assets ?>styles/helpers/login.css?v=<?=FORCAPOS_VERSION?>" rel="stylesheet"/>
    <script type="text/javascript" src="<?= $assets ?>js/jquery-2.0.3.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/daerah.js?v=<?=FORCAPOS_VERSION?>"></script>
    <style type="text/css">
        html, body {
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
        textarea{
            resize: none;
        }
    </style>
    <script type="text/javascript">
        function isEmail(email) {
            var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            return regex.test(email);
        }
        function checkAvailabilityEmail(email) {
            $("#waitingload").show();
            var result=isEmail(email);
            jQuery.ajax({
                url: "<?= site_url('auth/availableEmail') ?>",
                data:'email='+$("#email").val(),
                type: "POST",
                success:function(data){
                    if(result){
                        $("#email-availability-status").html(data);
                        var mail = $("#email").val();
                        $("#username").val(mail);
                    }
                    else{
                        var a = "<span class='status-not-available' style='color:red' > Invalid email format.</span>";
                        $("#email-availability-status").html(a);
                    }
                    $("#waitingload").hide();
                },
                error:function (){}
            });
        }
        function checkAvailabilityCompany(){
            $("#waitingload").show();
            jQuery.ajax({
                url: "<?= site_url('auth/availableCompany') ?>",
                data:'company='+$("#company").val(),
                type: "POST",
                success:function(data){
                    $("#company-availability-status").html(data);
                    $("#waitingload").hide();
                },
                error:function (){}
            });
        }
        function checkAvailabilityUsername(){
            $("#waitingload").show();
            jQuery.ajax({
                url: "<?= site_url('auth/availableUsername') ?>",
                data:'username='+$("#username").val(),
                type: "POST",
                success:function(data){
                    $("#username-availability-status").html(data);
                    $("#waitingload").hide();
                },
                error:function (){}
            });
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
    <div class="text-center">
        <?php if ($Settings->logo2) {
            echo '<a href="'.base_url().'"><img src="' . base_url('assets/uploads/logos/' . $Settings->logo2) . '" alt="' . $Settings->site_name . '" style="margin-bottom:10px;" /> </a>';
        } ?>
    </div>
    <div style="padding-left: 20%; padding-right: 20%;">
        <?php if ($Settings->mmode) { ?>
            <div class="alert alert-warning">
                <button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">&times;</span></button>
                <?= lang('site_is_offline') ?>
            </div>
        <?php }
        if ($error) {?>
            <div class="alert alert-danger">
                <button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">&times;</span></button>
                <ul class="list-group"><?= $error; ?></ul>
            </div>
        <?php }
        if ($message) { ?>
            <div class="alert alert-success">
                <button data-dismiss="alert" class="close" type="button">&times;</button>
                <ul class="list-group"><?= $message; ?></ul>
            </div>
        <?php } ?>
    </div>
    <div class="row" style="margin:15px;">
        <div class="col-lg-12">
            <div class="box" style="margin:0 15px;">
                <div class="box-header">
                    <h2>Registrasi</h2>
                </div>
                <div class="box-content" >
                    <div class="row">
                        <div class="col-lg-12">
                            <p class="intro-text">
                                Silahkan Lengkapi Data Anda
                            </p>
                            <span id="waitingload" class="dataTables_processing" style="display: none;"></span>
                            <?php
                            $attributes=array('class' => 'form-horizontal', 'data-toggle' => 'validator', 'role' => 'form');
                            echo form_open("auth/sign_up", $attributes );?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="col-md-5">
                                        <?php if(!isset($provider)){?>

                                            <div class="form-group">
                                                Email*
                                                <div class="controls">
                                                    <input type="email" name="email" class="form-control" onBlur="checkAvailabilityEmail(this.value)" id="email" required><span id="email-availability-status"></span>
                                                    <?php
                                                    // echo form_input('email','','class="form-control" id="email" required ');
                                                    echo form_hidden('provider','email');?>
                                                </div>
                                            </div>
                                        <?php }
                                        else{
                                            echo form_hidden('fname',$first_name,'id="fname"');
                                            echo form_hidden('email',$email,'id="email"');
                                            echo form_hidden('provider',$provider,'id="email"');
                                        }?>
                                        <div class="form-group">
                                            Phone*
                                            <div class="controls">
                                                <input type="text" name="phone" id="phone" class="form-control number-only" required>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            Province*
                                            <div class="controls">
                                                <select name="provinsi" id="provinsi" onchange="setProvinsi(this.value,this.options[this.selectedIndex].innerHTML)" class="form-control select" required>
                                                    <option value="">Choose Province</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            City*
                                            <div class="controls">
                                                <select name="kabupaten" id="kabupaten" onchange="setKabupaten(this.value,this.options[this.selectedIndex].innerHTML)" class="form-control select" required>
                                                    <option value="">Choose City</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            District*
                                            <div class="controls">
                                                <select name="kecamatan" id="kecamatan" onchange="setKecamatan(this.value,this.options[this.selectedIndex].innerHTML)" class="form-control select">
                                                    <option value="">Choose District</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            Address*
                                            <div class="controls">
                                                <!--                                                                                            <textarea name="address" id="address" class="form-control" rows="3" required></textarea>-->
                                                <input name="address" id="address" class="form-control" rows="3" required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            Postalcode*
                                            <div class="controls">
                                                <input name="postalcode" id="postalcode" class="form-control number-only" required/>
                                            </div>
                                        </div>
                                        <!-- <div>
                                            <div id="map-canvas" style="width: 100%; height: 19em"></div>
                                        </div> -->
                                    </div>
                                    <div class="col-md-5 col-md-offset-1">
                                        <?php if(!isset($provider)){?>
                                            <div class="form-group">
                                                First Name*
                                                <div class="controls">
                                                    <?php echo form_input('fname','','class="form-control" id="fname" required ');?>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                Last Name*
                                                <div class="controls">
                                                    <?php echo form_input('lname','','class="form-control" id="lname" required ');?>
                                                </div>
                                            </div>
                                        <?php }
                                        else{
                                            echo form_hidden('lname',$last_name,'id="lname"');
                                        }?>

                                        <div class="form-group">
                                            Company Name*
                                            <div class="controls">
                                                <?php echo form_input('company','',' class="form-control" id="company" required');?>
                                                <span id="company-availability-status" style="position:right"></span>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            Gender*
                                            <div class="controls">
                                                <?php
                                                $gndr = array('male' => 'male', 'female' => 'female');
                                                echo form_dropdown('gender', $gndr, (isset($_POST['gender']) ? $_POST['gender'] : 'male'), 'class="form-control" id="gender" required');
                                                ?>
                                            </div>
                                        </div>
                                        <input type="hidden" name="username" class="form-control" id="username" onBlur="checkAvailabilityUsername()" required>
                                        <div class="form-group">
                                            Password*
                                            <div class="controls">
                                                <input type="password" name="password" value="" class="form-control tip" id="password" required="required" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}" data-bv-regexp-message="At least 1 capital, 1 lowercase, 1 number and more than 6 characters long" data-original-title="" title="" data-bv-field="password">
                                                <span class="help-block"><?= lang('pasword_hint') ?></span>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            Confirm Password*
                                            <div class="controls">
                                                <input type="password" name="confirm_password" value="" class="form-control" id="confirm_password" required="required" data-bv-identical="true" data-bv-identical-field="password" data-bv-identical-message="The password and confirm password are not the same" data-bv-field="confirm_password">
                                            </div>
                                            <span id='message'></span>
                                        </div>


                                        <input id="latitude" name="latitude" type="hidden"/>
                                        <input id="longitude" name="longitude" type="hidden"/>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="termcondition" name="termcondition">
                                            <label class="form-check-label" for="termcondition">I agree to Term and Condition  ( <a href="<?= base_url()?>document/term.pdf"><i class="fa fa-file-text"></i></a> )</label>
                                        </div>
                                        <div class="form-group">
                                            <br>
                                            <!--<button class="btn btn-danger" value="Reset" name="reset" id="reset"> Reset</button> -->
                                            <input type="reset" name="reset" id="reset" value="Reset" class="btn btn-danger">
                                            <input type="submit" name="regis" id="regis" value="Register" class="btn btn-primary" disabled="disabled" >

                                        </div>

                                    </div>
                                    <!--                                                                <div class="col-md-12">


                                                                                                    </div>-->
                                </div>
                            </div>
                            <?php echo form_close(); ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
</body>
<script type="text/javascript">
    $('#password, #confirm_password').on('keyup', function () {
        if( ( $('#password').val()!=='' ) && ( $('#confirm_password').val()!=='') ){
            if($('#password').val().length<=6){
                $('#message').html('Less than 6 characters').css('color', 'red');
            }
            else if ($('#password').val() == $('#confirm_password').val()) {
                $('#message').html('Matched').css('color', 'green');
            }else
                $('#message').html('Not Matched').css('color', 'red');
        }else{
            $('#message').html('Please insert password').css('color', 'red');
        }
    });

    $(document).ready(function() {
        $('[name="termcondition"]').change(function()
        {
            if ($(this).is(':checked')) {
                // Do something...
                $('#regis').removeAttr('disabled');
            }else{
                $('#regis').attr('disabled', 'disabled');
            }
        });
        $('input.number-only').bind('keypress', function (e) {
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
    <?php if($this->session->userdata('registration') && $this->session->userdata('additional_registration')){
    $personal_data=$this->session->userdata('registration');
    $additional_personal_data = $this->session->userdata('additional_registration');?>

    $('#fname').val('<?= $personal_data->first_name?>');
    $('#lname').val('<?= $personal_data->last_name?>');
    $('#email').val('<?= $personal_data->email?>');
    $('#phone').val('<?= $personal_data->phone?>');
    $('#address').val('<?= $personal_data->address?>');
    $('#company').val('<?= $personal_data->company?>');
    $('#gender').val('<?= $personal_data->gender?>');
    $.getJSON('<?php echo base_url(); ?>daerah/getProvinsi', function(data) {
        var output = "";
        output += '<option value="" data-foo="">Choose Province</option>';
        $.each(data, function(key, val) {
            output += '<option value="' + val.province_name + '" data-foo="">' + val.province_name + '</option>';
        });
        $("#provinsi").html(output);

        $('select[name=provinsi]').val('<?= $personal_data->country?>').change();
        setTimeout(function() {
            $('select[name=kabupaten]').val('<?= $personal_data->city?>').change();
        }, 500);
        setTimeout(function() {
            $('select[name=kecamatan]').val('<?= $personal_data->state?>').change();
        },1000);
    });

    $('#postalcode').val('<?= $additional_personal_data->postal_code?>');
    // $('#latitude').val('<?= $additional_personal_data->latitude?>');
    // $('#longitude').val('<?= $additional_personal_data->longitude?>');
    <?php $this->session->unset_userdata('additional_registration');
    $this->session->unset_userdata('registration'); } ?>
</script>
<!-- <script async defer src="https://maps.googleapis.com/maps/api/js?callback=initMap&libraries=places&key=<?= _KEY_MAP ?>" type="text/javascript"></script>
<script type="text/javascript" src="<?=$assets?>js/map.js"></script> -->
</html>