<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">

  <title>Reset Kata Sandi - AksesToko</title>

  <link rel="shortcut icon" href="<?=$assets_at?>img/logo-at-short.png" type="image/x-icon">

  <!-- Font Awesome Icons -->
  <link href="<?=$assets_at?>vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Merriweather+Sans:400,700" rel="stylesheet">
  <link href='https://fonts.googleapis.com/css?family=Merriweather:400,300,300italic,400italic,700,700italic' rel='stylesheet' type='text/css'>

  <!-- Plugin CSS -->
  <link href="<?=$assets_at?>vendor/magnific-popup/magnific-popup.css" rel="stylesheet">

  <!-- Theme CSS - Includes Bootstrap -->
  <link href="<?=$assets_at?>css/creative.css" rel="stylesheet">

  <style type="text/css">
    .close {
    float: right;
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
    color: #000000;
    text-shadow: 0 1px 0 #fff;
    opacity: 0.2;
    filter: alpha(opacity=20);
}


.modal-dialog .close {
    position: absolute;
    right: 1rem;
    top: .8rem;
    -webkit-transition: all 0.2s ease-in-out;
    transition: all 0.2s ease-in-out;
}
  </style>

</head>

<body id="page-top">

<?php if (SERVER_QA) { ?>
  <div id="snackbar">QA SERVER</div>
<?php } ?>

  <!-- Masthead2 -->
  <header class="masthead2">
    <!-- Navigation -->
    <div class="container" style="padding-top: 2%;">
      <div class="row align-items-center justify-content-center text-center">
        <div class="col-lg-6 align-self-center">
          <div class="card">
            <a class="back-to" href="<?=base_url(aksestoko_route("aksestoko/auth/signin"))?>"><i class="fa fa-chevron-left"></i></a>
            <div class="card-body">
              <a href="../home">
                <img class="m-4" src="<?=base_url('assets/uploads/cms/') . $cms->logo_1?>" onerror="this.src='<?=$assets_at?>img/logo-at.png?v=<?=FORCAPOS_VERSION?>'" alt="logo" width="250">
              </a>
              <h4 class="card-title text-left py-2">Reset Kata Sandi</h4>
              <h6 class="card-subtitle pb-4 text-muted text-left">Kode OTP valid sampai dengan <span class="text-info"><?=date('d F Y H:i', strtotime($reset_password->valid_until))?></span> </h6>
              <!-- <h6 class="text-muted text-left" id="otpError" style="display:none;">Tidak menerima Kode OTP? <a href="javascript:void(0)" id="mintaBantuan">Klik disini</a> untuk meminta bantuan kami.</h6> -->
              <div class="card-text">

                <div class="form-group py-3">
                  <input type="hidden" value="<?=$this->session->userdata('id_forget_password')?>" id="id_fp">
                  <input type="text" autofocus required class="form-control form-control-custom text-sans-serif text-center" id="otp" name="otp" placeholder="Masukkan kode OTP" maxlength="5">
                </div>
                
                <button class="btn btn-l btn-block btn-primary mb-1" onclick="checkOtp()">Cek OTP</button>
                <form style="display: none" id="rerequest" action="<?=base_url(aksestoko_route('aksestoko/auth/forget_password'))?>" method="POST">
                  <input type="hidden" value="<?=$this->session->userdata('store_code')?>" name="store_code">
                  <input type="hidden" value="<?=$this->session->userdata('phone')?>" name="phone">

                  <button class="btn btn-l mb-1 text-primary">Minta Kode Baru</button>                
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>
   <!--Modal Address-->
  <div class="modal fade" tabindex="-1" role="dialog" id="inputPass">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-body p-box">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
        <h4 class="modal-title mb-2">Reset Kata Sandi</h4>
        <div class="container">
          <!-- ALERT -->
          <?php if($this->session->flashdata('error')) { ?>
          <div class="alert alert-danger alert-dismissible fade show text-left" role="alert">
              <?= $this->session->flashdata('error') ?>
              <button class="close" type="button" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">×</span>
              </button>
          </div>
          <?php } ?>
          <form action="<?=base_url(aksestoko_route('aksestoko/auth/change_password'))?>" method="POST">
            <input type="hidden" value="<?=$this->session->userdata('id_forget_password')?>" name="id_forget_password">
            <div class="row">
              <div class="col-md-12 mt-3">
                <div class="form-group pb-1">
                  <div class="input-group-prepend">
                    <input type="password" required class="form-control form-control-custom text-sans-serif" id="new_password" name="new_password" placeholder="Kata Sandi Baru" style="border-right: 0;border-top-right-radius: 0;border-bottom-right-radius: 0;">
                    <span id="show-password-new" style="width: 50px;border-left: 0;border-bottom-left-radius: 0;border-top-left-radius: 0; cursor: pointer;" class="btn btn-primary input-group-text"><i class="fa fa-eye" style="margin:auto"></i></span>
                  </div>
                  <small id="passwordHelp" class="form-text text-left text-sans-serif text-danger font-italic">Kata Sandi Baru minimal 8 karakter kombinasi dari huruf besar, huruf kecil dan angka</small>
                </div>

                <div class="form-group pb-1">
                  <div class="input-group-prepend">
                    <input type="password" required class="form-control form-control-custom text-sans-serif" id="retype_new_password" name="retype_new_password" placeholder="Ulangi Kata Sandi Baru" style="border-right: 0;border-top-right-radius: 0;border-bottom-right-radius: 0;">
                    <span id="show-retype-password-new" style="width: 50px;border-left: 0;border-bottom-left-radius: 0;border-top-left-radius: 0; cursor: pointer;" class="btn btn-primary input-group-text"><i class="fa fa-eye" style="margin:auto"></i></span>
                  </div>
                  <small id="retypePasswordHelp" class="form-text text-left text-sans-serif text-danger font-italic">Kata Sandi Baru dan Ulang Kata Sandi Baru tidak sama</small>
                </div>
              </div>
              <div class="col-md-12 mt-3 mb-2">
                <button type="submit" class="btn btn-l btn-block btn-primary">Kirim</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- END MODAL -->

  <!-- Bootstrap core JavaScript -->
  <script src="<?=$assets_at?>/vendor/jquery/jquery.min.js"></script>
  <script src="<?=$assets_at?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <!-- Plugin JavaScript -->
  <script src="<?=$assets_at?>/vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="<?=$assets_at?>/vendor/magnific-popup/jquery.magnific-popup.min.js"></script>

  <!-- Custom scripts for this template -->
  <script src="<?=$assets_at?>/js/creative.min.js"></script>

  <script src="<?=$assets_at?>guide/js/hopscotch.js"></script>
  <link href="<?=$assets_at?>guide/css/hopscotch.css" rel="stylesheet"/>

  <script>
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

      $('#show-retype-password-new').click(function() {
        if ($(this).children('i').hasClass('fa-eye')) {
          $('#retype_new_password').attr('type', 'text');
          $(this).children('i').removeClass('fa-eye');
          $(this).children('i').addClass('fa-eye-slash');

        } else {
          $('#retype_new_password').attr('type', 'password');
          $(this).children('i').removeClass('fa-eye-slash');
          $(this).children('i').addClass('fa-eye');
        }
      })

      $('#show-password-new').click(function() {
        if ($(this).children('i').hasClass('fa-eye')) {
          $('#new_password').attr('type', 'text');
          $(this).children('i').removeClass('fa-eye');
          $(this).children('i').addClass('fa-eye-slash');

        } else {
          $('#new_password').attr('type', 'password');
          $(this).children('i').removeClass('fa-eye-slash');
          $(this).children('i').addClass('fa-eye');
        }
      })


      function validatePassword(password) {
        var filter = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z]{8,}$/;
        if (filter.test(password)) {
          return true;
        } else {
          return false;
        }
      }

      $('#new_password').keyup(function(e) {
        if (validatePassword(this.value)) {
          $('#passwordHelp').hide();
          $(this).removeClass('is-invalid');
        } else {
          $('#passwordHelp').show();
          $(this).addClass('is-invalid');
        }
        $('#retype_new_password').keyup();
      }).keyup();

      $('#retype_new_password').keyup(function(e) {
        if ($('#new_password').val() === this.value) {
          $('#retypePasswordHelp').hide();
          $(this).removeClass('is-invalid');
        } else {
          $('#retypePasswordHelp').show();
          $(this).addClass('is-invalid');
        }
      }).keyup();
    })();

    function goBack() {
      window.history.back();
    }

  </script>
  <script>


    function checkOtp(){
      if($("#otp").val().length != 5) {
        return alert("Panjang Kode OTP adalah 5 digit")
      }
      let data = {
        id_forget_password : $("#id_fp").val(),
        otp: $("#otp").val()
      }
      $.ajax({
          type: "POST",
          dataType: "json",
          data,
          url: '<?=base_url(aksestoko_route('aksestoko/auth/check_otp'))?>',
          success: function(response){
            $("#inputPass").modal('show');
          }, 
          error: function(jqXHR, textStatus, errorThrown){
            if(jqXHR.status == 401){
              $("#rerequest").show(200)
            }
            return alert(jqXHR.responseJSON)
          }

      });
    }
    
    let otp_code = "<?=$this->session->userdata('remembered_otp')?>"
    if(otp_code.length > 0){
      $("#otp").val(otp_code)
      checkOtp()
    }

    setTimeout(function(){ 
      $('#otpError').fadeIn(1000)
      $("#mintaBantuan").click(function () {
        $.ajax({
            type: "GET",
            dataType: "json",
            url: '<?=base_url(aksestoko_route("aksestoko/auth/send_otp/helpdesk"))?>',
            success: function(response){
              $('#otpError').html("Silakan menghubungi kami melalui <a target='_blank' href='https://wa.me/628116065246?text=Saya+tidak+mendapatkan+Kode+OTP.+ID+Bisnis+Kokoh+:+<?=$reset_password->store_code?>'>0811-6065-246 (WhatsApp)</a>")
            }
        });
      })
    }, 120000);

  </script>
      <script>
        (function(w,d,u){
                var s=d.createElement('script');s.async=true;s.src=u+'?'+(Date.now()/60000|0);
                var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);
        })(window,document,'https://cdn.bitrix24.id/b11907515/crm/site_button/loader_3_wclev2.js');
</script>
</body>

</html>
