<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <title>Pendaftaran - AksesToko</title>
  <!-- Primary Meta Tags -->
  <meta name="title" content="Pendaftaran - AksesToko">
  <meta name="description" content="Silakan isi formulir di bawah ini untuk membuat akun.">

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= current_url() ?>">
  <meta property="og:title" content="Pendaftaran - AksesToko">
  <meta property="og:description" content="Silakan isi formulir di bawah ini untuk membuat akun.">
  <meta property="og:image" content="<?= $cms ? base_url('assets/uploads/cms/') . $cms->logo_1 : $assets_at . 'img/logo-at.png?v='.FORCAPOS_VERSION ?>">
  <meta property="og:image:width" content="500">
  <meta property="og:image:height" content="170">

  <!-- Twitter -->
  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="<?= current_url() ?>">
  <meta property="twitter:title" content="Pendaftaran - AksesToko">
  <meta property="twitter:description" content="Silakan isi formulir di bawah ini untuk membuat akun.">
  <meta property="twitter:image" content="<?= $cms ? base_url('assets/uploads/cms/') . $cms->logo_1 : $assets_at . 'img/logo-at.png?v='.FORCAPOS_VERSION ?>">

  <link rel="shortcut icon" href="<?= $assets_at ?>img/logo-at-short.png" type="image/x-icon">

  <!-- Font Awesome Icons -->
  <link href="<?= $assets_at ?>/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Merriweather+Sans:400,700" rel="stylesheet">
  <link href='https://fonts.googleapis.com/css?family=Merriweather:400,300,300italic,400italic,700,700italic' rel='stylesheet' type='text/css'>

  <!-- Plugin CSS -->
  <link href="<?= $assets_at ?>vendor/magnific-popup/magnific-popup.css" rel="stylesheet">

  <!-- Theme CSS - Includes Bootstrap -->
  <link href="<?= $assets_at ?>css/creative.css" rel="stylesheet">
  <style type="text/css">
    .custom-control-input:checked~.custom-control-label::before {
      color: #fff;
      border-color: #7B1FA2;
    }

    .custom-control-input:checked~.custom-control-label.red::before {
      background-color: #cc314d;
    }
  </style>

</head>

<body id="page-top">
  <?php if (SERVER_QA) { ?>
    <div id="snackbar">QA SERVER</div>
  <?php } ?>

  <!-- Masthead2 -->
  <header class="masthead2">

    </div>
    </div>
    <!--  </div>
    </nav> -->
    <div class="container" style="padding-top: 2%;">
      <div class="row align-items-center justify-content-center text-center">
        <div class="col-lg-6 align-self-center">
          <div class="card">
            <a class="back-to back-on-signup" href="<?= base_url(aksestoko_route("aksestoko/home")) ?>"><i class="fa fa-chevron-left"></i></a>
            <div class="card-body">
              <a href="../home">
                <img class="m-4" src="<?= base_url('assets/uploads/cms/') . $cms->logo_1 ?>" onerror="this.src='<?= $assets_at ?>img/logo-at.png?v=<?=FORCAPOS_VERSION?>'" alt="logo" width="250">
              </a>
              <h4 class="card-title text-left py-2">Pendaftaran</h4>
              <h6 class="card-subtitle pb-4 text-muted text-left">Silakan isi formulir di bawah ini untuk membuat akun.
                <br>
                Sudah punya akun? silakan <a href="<?= base_url(aksestoko_route('aksestoko/auth/signin')) ?>"> Login</a>.
              </h6>
              <div class="card-text pt-3">
                <!-- ALERT -->
                <?php if ($this->session->flashdata('error')) { ?>
                  <div class="alert alert-danger alert-dismissible fade show text-left" role="alert">
                    <?= $this->session->flashdata('error') ?>
                    <button class="close" type="button" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">×</span>
                    </button>
                  </div>
                <?php } ?>

                <?php if ($this->session->flashdata('message')) { ?>
                  <div class="alert alert-info alert-dismissible fade show text-left" role="alert">
                    <?= $this->session->flashdata('message') ?>
                    <button class="close" type="button" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">×</span>
                    </button>
                  </div>
                <?php } ?>

                <form class="needs-validation" novalidate method="POST" action="<?= base_url(aksestoko_route('aksestoko/auth/register')) ?>">
                  <input type="hidden" required name="provider" value="email">
                  <div class="form-group pb-1">
                    <input type="text" required class="form-control form-control-custom text-sans-serif" autofocus id="store_code" name="store_code" value="<?= $this->session->flashdata('value')['store_code'] ?>" aria-describedby="store_code" placeholder="ID Bisnis Kokoh (9 Digit)" maxlength="9">
                    <small id="notifValidationId" class="form-text text-left text-sans-serif text-danger font-italic" style="display: none">ID Bisnis Kokoh telah terdaftar</small>
                    <div class="invalid-feedback text-left text-sans-serif">
                      Bidang ini diperlukan.
                    </div>
                  </div>
                  <div id="formRegister" style="display: none;">
                    <div class="form-group pb-1">
                      <input type="text" required class="form-control form-control-custom text-sans-serif" id="store_name" name="store_name" value="<?= $this->session->flashdata('value')['store_name'] ?>" placeholder="Nama Toko">
                      <div class="invalid-feedback text-left text-sans-serif">
                        Bidang ini diperlukan.
                      </div>
                    </div>
                    <div class="form-group pb-1">
                      <div class="input-group-prepend">
                        <input type="text" class="form-control form-control-custom text-sans-serif" id="email" name="email" value="<?= $this->session->flashdata('value')['email'] ?>" placeholder="Email" style="border-right: 0;border-top-right-radius: 0;border-bottom-right-radius: 0;">
                        <span id="generateEmail" style="border-left: 0;border-bottom-left-radius: 0;border-top-left-radius: 0; cursor: pointer;" class="btn btn-primary input-group-text">Generate</span>
                        <div class="invalid-feedback text-left text-sans-serif">
                          Bidang ini diperlukan.
                        </div>
                      </div>
                    </div>
                    <div class="form-group pb-1">

                      <div class="input-group-prepend">

                        <span style="border-right: 0;border-bottom-right-radius: 0;border-top-right-radius: 0;" class="input-group-text"><img src="<?= $assets_at ?>img/common/flag_indonesia.png" class="img-flag-phone">+62</span>
                        <input style="border-left: 0;border-top-left-radius: 0;border-bottom-left-radius: 0;" maxlength="16" id="handphone" type="text" class="form-control form-control-custom text-sans-serif phoneFormat" placeholder="No Handpone" value="<?= $this->session->flashdata('value')['handphone'] ?>" required="">
                      </div>
                      <div style="display: none;">
                        <input type="text" class="phoneFormatHidden" name="handphone" placeholder="No Telepon" required="">
                      </div>

                      <small id="notifValidationPhone" class="form-text text-left text-sans-serif text-danger font-italic">Gunakan No Telepon yang valid untuk menerima SMS Kode Aktivasi.</small>
                      <div class="invalid-feedback text-left text-sans-serif">
                        Bidang ini diperlukan.
                      </div>
                    </div>
                    <div class="form-group pb-1">
                      <input type="text" required class="form-control form-control-custom text-sans-serif" id="firstname" name="firstname" value="<?= $this->session->flashdata('value')['firstname'] ?>" placeholder="Nama Depan">
                      <div class="invalid-feedback text-left text-sans-serif">
                        Bidang ini diperlukan.
                      </div>
                    </div>
                    <div class="form-group pb-1">
                      <input type="text" required class="form-control form-control-custom text-sans-serif" id="lastname" name="lastname" value="<?= $this->session->flashdata('value')['lastname'] ?>" placeholder="Nama Belakang">
                      <div class="invalid-feedback text-left text-sans-serif">
                        Bidang ini diperlukan.
                      </div>
                    </div>

                    <div class="form-group pb-1">
                      <div class="input-group-prepend">
                        <input type="password" required class="form-control form-control-custom text-sans-serif" id="password" name="password" value="<?= $this->session->flashdata('value')['password'] ?>" placeholder="Kata Sandi" style="border-right: 0;border-top-right-radius: 0;border-bottom-right-radius: 0;">
                        <span id="show-password-register" style="width: 50px;border-left: 0;border-bottom-left-radius: 0;border-top-left-radius: 0; cursor: pointer;" class="btn btn-primary input-group-text"><i class="fa fa-eye" style="margin:auto"></i></span>
                      </div>
                      <small id="passwordHelp" class="form-text text-left text-sans-serif text-danger font-italic">Kata Sandi minimal 8 karakter kombinasi dari huruf besar, huruf kecil dan angka</small>
                      <div class="invalid-feedback text-left text-sans-serif">
                        Bidang ini diperlukan.
                      </div>
                    </div>

                    <div class="form-group pb-1">
                      <div class="input-group-prepend">
                        <input type="password" required class="form-control form-control-custom text-sans-serif" id="retype_password" name="retype_password" value="<?= $this->session->flashdata('value')['retype_password'] ?>" placeholder="Ulangi Kata Sandi" style="border-right: 0;border-top-right-radius: 0;border-bottom-right-radius: 0;">
                        <span id="show-retype-password-register" style="width: 50px;border-left: 0;border-bottom-left-radius: 0;border-top-left-radius: 0; cursor: pointer;" class="btn btn-primary input-group-text"><i class="fa fa-eye" style="margin:auto"></i></span>
                      </div>
                      <div class="invalid-feedback text-left text-sans-serif">
                        Bidang ini diperlukan.
                      </div>
                    </div>

                    <div class="form-group pb-1 text-left">
                      <label class="text-muted text-sans-serif" style="padding-right:40px">Didaftarkan oleh</label>
                      <div class="custom-control custom-radio custom-control-inline text-muted" id="icon-registered" style="float: right;">
                        <a href="javascript:;" id="registered" class="demo-button demo-button-click noselect"><i class="fa fa-question-circle"></i></a>
                      </div>
                      <div class="row">
                        <div class="col-xs-12 col-auto">
                          <div class="custom-control custom-radio custom-control-inline text-muted" style="display: table;">
                            <input type="radio" id="rd_1" name="optradio" class="custom-control-input radio_rb" value="smi" required <?= $this->session->flashdata('value')['optradio'] == 'smi' ? 'checked' : '' ?>>
                            <label class="custom-control-label red text-sans-serif" for="rd_1" style="display: table-cell;text-align: center;vertical-align: middle;">
                              <span style="vertical-align: middle;">SIG</span>
                              <div style="width: 30px;height: 30px;background-image: url('<?=base_url('assets/uploads/logos/sp.png')?>');background-position: center;background-size: cover; float: right; vertical-align: middle;"></div>
                              <div style="width: 30px;height: 30px;background-image: url('<?=base_url('assets/uploads/logos/st.png')?>');background-position: center;background-size: cover; float: right; vertical-align: middle;"></div>
                              <div style="width: 30px;height: 30px;background-image: url('<?=base_url('assets/uploads/logos/sg.png')?>');background-position: center;background-size: cover; float: right; vertical-align: middle;"></div>
                            </label>
                          </div>
                        </div>
                        <div class="col-xs-12 col-auto">
                          <div class="custom-control custom-radio custom-control-inline text-muted" style="display: table;">
                            <input type="radio" id="rd_2" name="optradio" class="custom-control-input radio_rb" value="sbi" required <?= $this->session->flashdata('value')['optradio'] == 'sbi' ? 'checked' : '' ?>>
                            <label class="custom-control-label red text-sans-serif" for="rd_2" style="display: table-cell;text-align: center;vertical-align: middle;">
                              <span style="vertical-align: middle;">SBI</span>
                              <div style="width: 60px;height: 30px;background-image: url('<?=base_url('assets/uploads/logos/dynamix.png')?>');background-position: center;background-size: cover; float: right; vertical-align: middle;"></div>
                            </label>
                          </div>
                        </div>
                        <div class="col-xs-12 col-auto">
                          <div class="custom-control custom-radio custom-control-inline text-muted" style="display: table;">
                            <input type="radio" id="rd_3" name="optradio" class="custom-control-input radio_rb" value="sba" required <?= $this->session->flashdata('value')['optradio'] == 'sba' ? 'checked' : '' ?>>
                            <label class="custom-control-label red text-sans-serif" for="rd_3" style="display: table-cell;text-align: center;vertical-align: middle;">
                              <span style="vertical-align: middle;">SBA</span>
                              <div style="width: 30px;height: 30px;background-image: url('<?=base_url('assets/uploads/logos/andalas.png')?>');background-position: center;background-size: cover; float: right; vertical-align: middle;"></div>
                            </label>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="custom-control custom-checkbox pb-4 text-left">
                      <input type="checkbox" class="custom-control-input" id="check_sp" name="check_sp" <?= $this->session->flashdata('value')['sales_person'] != '' ? 'checked' : '' ?>>
                      <label class="custom-control-label text-sans-serif text-muted" for="check_sp">Punya kode referal Salesperson?</label>
                      <div class="custom-control custom-radio custom-control-inline text-muted" id="icon-salesperson" style="float: right;">
                        <a href="javascript:;" id="salesperson"><i class="fa fa-question-circle"></i></a>
                      </div>
                    </div>
                    <div class="form-group pb-1" style="display: none;" id="div_sales_person">
                      <input type="text" class="form-control form-control-custom text-sans-serif" id="sales_person" name="sales_person" placeholder="Kode Referal" value="<?= $this->session->flashdata('value')['sales_person'] ?>">
                    </div>
                    <div class="custom-control custom-checkbox pb-4 text-left ">
                      <input type="checkbox" class="custom-control-input" id="terms_privacy" name="terms_privacy" required>
                      <label class="custom-control-label text-sans-serif text-muted" for="terms_privacy">Saya sudah membaca dan menyetujui
                        <a target="_blank" href="<?= base_url('assets/aksestoko/Syarat%20&%20Ketentuan%20AksesToko.pdf') ?>">Syarat dan Ketentuan</a> &
                        <a target="_blank" href="<?= base_url('assets/aksestoko/Kebijakan%20Privasi%20AksesToko.pdf') ?>">Kebijakan Privasi</a>
                      </label>
                      <div class="invalid-feedback text-left text-sans-serif">
                        Bidang ini diperlukan.
                      </div>
                    </div>
                    <button id="btnDaftar" type="submit" class="btn btn-l btn-block btn-primary">Daftar</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- Bootstrap core JavaScript -->
  <script src="<?= $assets_at ?>vendor/jquery/jquery.min.js"></script>
  <script src="<?= $assets_at ?>vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <!-- Plugin JavaScript -->
  <script src="<?= $assets_at ?>vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="<?= $assets_at ?>vendor/magnific-popup/jquery.magnific-popup.min.js"></script>

  <!-- Custom scripts for this template -->
  <script src="<?= $assets_at ?>js/creative.min.js"></script>

  <script src="<?= $assets_at ?>guide/js/hopscotch.js"></script>
  <script src="<?= $assets_at ?>tooltip/dist/jBox.all.min.js"></script>
  <link href="<?= $assets_at ?>tooltip/dist/jBox.all.min.css" rel="stylesheet">
  <link href="<?= $assets_at ?>guide/css/hopscotch.css" rel="stylesheet" />


  <script>
    $(document).ready(function() {

      $("#check_sp").change(function() {
        if (this.checked) {
          $("#div_sales_person").show(500);
        } else {
          $("#div_sales_person").hide(500);
        }
      }).change();

      $(".phoneFormat").keyup(function(event) {
        // $(this).val(format($(this).val()));  
        $(this).val(format($(this).val()));
      }).keyup();


      $('#handphone').keyup(function(e) {
        if (validatePhone('handphone')) {
          $("#btnDaftar").removeAttr('disabled');
          $('#notifValidationPhone').hide();
        } else {
          $('#notifValidationPhone').show();
          $('#btnDaftar').attr('disabled', 'disabled');
        }
      }).keyup();

      $('#password').keyup(function(e) {
        if (validatePassword('password')) {
          $("#btnDaftar").removeAttr('disabled');
          $('#passwordHelp').hide();
        } else {
          $('#passwordHelp').show();
          $('#btnDaftar').attr('disabled', 'disabled');
        }
      }).keyup();

      // $(".phoneFormat").keydown(function(event){
      //   return limitCharacter(event);
      // });

    });

    var smi_sbi = '<b>Pilih SIG :</b><br> Jika anda dibantu pendaftaran oleh Salesperson dari distributor Semen Tonasa, Semen Gresik dan Semen Padang.<br><br><b>Pilih SBI :</b><br> Jika anda dibantu pendaftaran oleh Salesperson dari distributor Semen Dynamix.<br><br><b>Pilih SBA :</b><br> Jika anda dibantu pendaftaran oleh Salesperson dari distributor Semen Andalas.';
    var salesperson = 'Silahkan dicentang jika anda memiliki kode sales dan masukkan kode referal. <br><br> Jika tidak ada silahkan dilewati';

    new jBox('Tooltip', {
      attach: '#registered',
      target: '#icon-registered',
      theme: 'TooltipBorder',
      trigger: 'click',
      adjustTracker: true,
      closeOnClick: 'body',
      closeButton: 'box',
      animation: 'move',
      position: {
        x: 'left',
        y: 'top'
      },
      width: 300,
      outside: 'y',
      pointer: 'left:20',
      offset: {
        x: 25
      },
      content: smi_sbi,
      onOpen: function() {
        this.source.addClass('active').html('<i class="fa fa-question-circle"></i>');
      },
      onClose: function() {
        this.source.removeClass('active').html('<i class="fa fa-question-circle"></i>');
      }
    });

    new jBox('Tooltip', {
      attach: '#salesperson',
      target: '#icon-salesperson',
      theme: 'TooltipBorder',
      trigger: 'click',
      adjustTracker: true,
      closeOnClick: 'body',
      closeButton: 'box',
      animation: 'move',
      position: {
        x: 'left',
        y: 'top'
      },
      width: 300,
      outside: 'y',
      pointer: 'left:20',
      offset: {
        x: 25
      },
      content: salesperson,
      onOpen: function() {
        this.source.addClass('active').html('<i class="fa fa-question-circle"></i>');
      },
      onClose: function() {
        this.source.removeClass('active').html('<i class="fa fa-question-circle"></i>');
      }
    });

    // $('#registered').jBox('Tooltip', option_registered);
    // $('#salesperson').jBox('Tooltip', option_salesperson);

    // Validation
    function validatePhone(handphone) {
      var a = document.getElementById(handphone).value;
      // var filter = /^((\+[1-9]{1,4}[ \-]*)|(\([0-9]{2,3}\)[ \-]*)|([0-9]{2,4})[ \-]*)*?[0-9]{3,4}?[ \-]*[0-9]{3,4}?$/;
      var filter = /^(([1-9]{1,4}[ \-]*)|(\([0-9]{2,3}\)[ \-]*)|([0-9]{2,4})[ \-]*)*?[0-9]{3,4}?[ \-]*[0-9]{3,4}?$/;
      if (filter.test(a)) {
        return true;
      } else {
        return false;
      }
    }
    function validatePassword(password) {
      var a = document.getElementById(password).value;
      var filter = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z]{8,}$/;
      if (filter.test(a)) {
        return true;
      } else {
        return false;
      }
    }

    var format = function(num) {
      let a = num;
      if (a.charAt(0) == '0') {
        a = a.substr(1);
      } else if (a.charAt(0) == '6' && a.charAt(1) == '2') {
        a = a.substr(2);
      }
      num = a;
      var str = num.toString().replace("", ""),
        parts = false,
        output = [],
        i = 1,
        formatted = null;
      if (str.indexOf(".") > 0) {
        parts = str.split(".");
        str = parts[0];
      }
      str = str.split("").reverse();
      for (var j = 0, len = str.length; j < len; j++) {
        if (str[j] != "-") {
          output.push(str[j]);
          if (i % 3 == 0 && j < (len - 1)) {
            output.push("-");
          }
          i++;
        }
      }
      formatted = output.reverse().join("");

      return ("" + formatted + ((parts) ? "." + parts[1].substr(0, 2) : ""));
    };


    $(".phoneFormat").bind("keyup tap change paste", function() {
      $(".phoneFormatHidden").val("62" + $(this).val());
      $(".phoneFormatHidden").val($(".phoneFormatHidden").val().replace(/-/g, ''));
    });

    $('#generateEmail').click(function makeid() {
      var text = "";
      var idbk = $("#store_code").val();
      var possible = "abcdefghijklmnopqrstuvwxyz";
      var hasil = "";
      for (var i = 0; i < 5; i++) {
        text += possible.charAt(Math.floor(Math.random() * possible.length));
      }
      // return hasil(document.write(idbk+'@'+text+'.com'));
      $("#email").val(idbk + '@' + text + '.com');
    });

    // Example starter JavaScript for disabling form submissions if there are invalid fields
    (function() {
      'use strict';
      window.addEventListener('load', function() {
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.getElementsByClassName('needs-validation');
        // Loop over them and prevent submission
        var validation = Array.prototype.filter.call(forms, function(form) {
          form.addEventListener('submit', function(event) {
            if (form.checkValidity() === false) {
              event.preventDefault();
              event.stopPropagation();
            }
            form.classList.add('was-validated');
          }, false);
        });
      }, false);

      <?php
      if ($this->session->flashdata('error')) {
      ?>
        $("#formRegister").show();
      <?php
      }
      ?>

      $('#show-retype-password-register').click(function() {
        if ($(this).children('i').hasClass('fa-eye')) {
          $('#retype_password').attr('type', 'text');
          $(this).children('i').removeClass('fa-eye');
          $(this).children('i').addClass('fa-eye-slash');

        } else {
          $('#retype_password').attr('type', 'password');
          $(this).children('i').removeClass('fa-eye-slash');
          $(this).children('i').addClass('fa-eye');
        }
      })

      $('#show-password-register').click(function() {
        if ($(this).children('i').hasClass('fa-eye')) {
          $('#password').attr('type', 'text');
          $(this).children('i').removeClass('fa-eye');
          $(this).children('i').addClass('fa-eye-slash');

        } else {
          $('#password').attr('type', 'password');
          $(this).children('i').removeClass('fa-eye-slash');
          $(this).children('i').addClass('fa-eye');
        }
      })

    })();
  </script>

  <script>
    function delay(fn, ms) {
      let timer = 0
      return function(...args) {
        clearTimeout(timer)
        timer = setTimeout(fn.bind(this, ...args), ms || 0)
      }
    }

    $('#store_code').keyup(delay(function(e) {
      let idc = $(this).val()
      $('#notifValidationId').hide();
      if (idc.length == 9 /* || idc.length == 10 */ ) {
        $.ajax({
          type: "GET",
          dataType: "json",
          url: '<?= base_url(aksestoko_route('aksestoko/auth/customer/')) ?>' + idc,
          success: function(response) {
            if (!response.hasOwnProperty('status') || response.status !== false) {
              $('#notifValidationId').hide();
              $("#formRegister").slideDown(1000)
              let name = response.name.split(" ")
              $("#store_name").val(response.company)
              $("#email").val(response.email)
              $("#handphone").val(format(response.phone)).keyup();
              $("#firstname").val(name[0])
              $("#lastname").val(name[1])

            } else {
              alertCustom(response.message)
              $('#notifValidationId').html(response.message);
              $('#notifValidationId').show()
              $("#formRegister").fadeOut(500)
              $("#store_name").val("")
              $("#email").val("")
              $("#handphone").val("")
              $("#firstname").val("")
              $("#lastname").val("")
            }
          }
        });
        return false; //<---- Add this line
      }
    }, 300));
  </script>

  <script>
    // Define the tour!
    var tour = {
      id: "signup",
      onClose: function() {
        localStorage.setItem('signup', true);
        //  console.log('aku');
      },
      steps: [{
          title: "Masukkan ID Bisnis Kokoh",
          content: "Masukkan ID Bisnis Kokoh Anda",
          target: "input#store_code",
          placement: "top",
        },
        {
          title: "Masukkan Biodata Anda",
          content: "Masukkan biodata & password dengan benar",
          target: "input#store_name",
          placement: "top",
        },
        {
          title: "Tekan Tombol Registrasi",
          content: "Tekan tombol registrasi",
          target: "button.btn.btn-l.btn-block.btn-primary",
          placement: "top"
        }
      ]
    };

    if (!localStorage.getItem('signup')) {
      // Start the tour!
      hopscotch.startTour(tour);
    }
  </script>

  <script>
    var counter = 0

    function alertCustom(message) {
      counter++;
      let html = '<div id="alertCustom' + counter + '" class="" style="top: 0; width: 100%; position: fixed; z-index: 1029; margin-top: 10px">'
      html += '<div class="container" style="">'
      html += '<div class="alert alert-warning alert-dismissible show text-left mb-0" role="alert">'
      html += message
      html += '<button class="close" type="button" data-dismiss="alert" aria-label="Close">'
      html += '<span aria-hidden="true">×</span>'
      html += '</button>'
      html += '</div>'
      html += '</div>'
      html += '</div>'
      let $html = $(html)
      $("body").append($html)

      $("#alertCustom" + counter).fadeTo(1000, 500).slideUp(500, function() {
        $(this).slideUp(500);
        $html.remove()
      });
    }
  </script>
  <script>
    (function(w, d, u) {
      var s = d.createElement('script');
      s.async = true;
      s.src = u + '?' + (Date.now() / 60000 | 0);
      var h = d.getElementsByTagName('script')[0];
      h.parentNode.insertBefore(s, h);
    })(window, document, 'https://cdn.bitrix24.id/b11907515/crm/site_button/loader_3_wclev2.js');
  </script>
</body>

</html>