<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <title>Masuk - AksesToko</title>
  <!-- Primary Meta Tags -->
  <meta name="title" content="Masuk - AksesToko">
  <meta name="description" content="Silakan isi dengan ID Bisnis Kokoh atau email atau nomer ponsel dan kata sandi yang digunakan ketika mendaftar.">

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?=current_url()?>">
  <meta property="og:title" content="Masuk - AksesToko">
  <meta property="og:description" content="Silakan isi dengan ID Bisnis Kokoh atau email atau nomer ponsel dan kata sandi yang digunakan ketika mendaftar.">
  <meta property="og:image" content="<?= $cms ? base_url('assets/uploads/cms/') . $cms->logo_1 : $assets_at .'img/logo-at.png?v='.FORCAPOS_VERSION?>">
  <meta property="og:image:width" content="500">
  <meta property="og:image:height" content="170">
  
  <!-- Twitter -->
  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="<?=current_url()?>">
  <meta property="twitter:title" content="Masuk - AksesToko">
  <meta property="twitter:description" content="Silakan isi dengan ID Bisnis Kokoh atau email atau nomer ponsel dan kata sandi yang digunakan ketika mendaftar.">
  <meta property="twitter:image" content="<?= $cms ? base_url('assets/uploads/cms/') . $cms->logo_1 : $assets_at .'img/logo-at.png?v='.FORCAPOS_VERSION?>">

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
  
    <div class="container" style="padding-top: 2%;">
      <div class="row align-items-center justify-content-center text-center">
        <div class="col-lg-6 align-self-center">
          <div class="card">
            <a class="back-to back-on-login" href="<?=base_url(aksestoko_route("aksestoko/home"))?>"><i class="fa fa-chevron-left"></i></a>
            <div class="card-body">
              <a href="../home">
                <img class="m-4" src="<?=base_url('assets/uploads/cms/') . $cms->logo_1?>" onerror="this.src='<?=$assets_at?>img/logo-at.png?v=<?=FORCAPOS_VERSION?>'" alt="logo" width="250">
              </a>
              <h4 class="card-title text-left py-2">Masuk</h4>
              <h6 class="card-subtitle pb-4 text-muted text-left">Silakan isi dengan ID Bisnis Kokoh atau email atau nomer ponsel dan kata sandi yang digunakan ketika mendaftar.</h6>
              <div class="card-text">
                <!-- ALERT -->
                <?php if($this->session->flashdata('error')) { ?>
                <div class="alert alert-danger alert-dismissible fade show text-left" role="alert">
                    <?= $this->session->flashdata('error') ?>
                    <button class="close" type="button" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <?php } ?>

                <?php if($this->session->flashdata('message')) { ?>
                <div class="alert alert-info alert-dismissible fade show text-left" role="alert">
                    <?= $this->session->flashdata('message') ?>
                    <button class="close" type="button" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <?php } ?>

                <form class="needs-validation" novalidate method="POST" action="<?= base_url(aksestoko_route('aksestoko/auth/login')) ?>">
                  <div class="form-group pt-3 pb-1">
                    <input type="text" autofocus required class="form-control form-control-custom text-sans-serif" id="username" name="username" value="<?=$this->session->flashdata('username')?>" aria-describedby="usernameHelp" placeholder="ID Bisnis Kokoh atau Email atau Nomor Ponsel">
                    <div class="invalid-feedback text-left text-sans-serif">
                      Bidang ini diperlukan.
                    </div>
                  </div>

                  <div class="form-group pt-1 pb-1">
                    <input type="password" required class="form-control form-control-custom text-sans-serif" id="password" name="password" placeholder="Password">
                    <i id="show-password" class="fa fa-eye"></i>
                    <div class="invalid-feedback text-left text-sans-serif">
                      Bidang ini diperlukan.
                    </div>
                    
                  </div>
                  
                  <div class="pt-1 pb-3 text-right">
                    <div class="custom-control custom-checkbox text-left" style="width: 50%; float: left;">
                      <input type="checkbox" class="custom-control-input" id="remember" name="remember" >
                      <label class="custom-control-label text-sans-serif text-muted" for="remember">Ingat Saya</label>
                    </div>
                    <a href="#" data-toggle="modal" data-target="#modalLupaPass" class="text-muted lupa-sandi text-sans-serif">Lupa kata sandi?</a>
                  </div>
                  <button type="submit" class="btn btn-l btn-block btn-primary">Masuk</button>
                  <a href="<?= base_url(aksestoko_route('aksestoko/auth/signup'))?>" class="btn btn-l btn-block text-primary">Daftar</a>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>

<!--Modal Lupa Sandi-->
<div class="modal fade" tabindex="-1" role="dialog" id="modalLupaPass">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-body p-box">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
        <h4 class="modal-title mb-2 text-center">Lupa Kata Sandi</h4>
        <div class="container pb-3">
          <form action="<?=base_url(aksestoko_route('aksestoko/auth/forget_password'))?>" method="POST">
            <div class="row">
              <div class="col-md-12 mt-3">
                <label>ID Bisnis Kokoh</label>
                <input type="text" autofocus required class="form-control form-control-custom text-sans-serif" id="store_code" name="store_code" value="" placeholder="Masukkan ID Bisnis Kokoh Anda" >
              </div>
              <div class="col-md-12 mt-3" >
                <label>4 Digit Terakhir No Telepon</label>
                <input id="hanyaDigit" maxlength="4" onkeyup="angka(this);" type="text" class="form-control form-control-custom text-sans-serif hanyaDigit" name="phone" placeholder="4 Digit Terakhir No Telepon" value="" required="">
                <!-- <input style="border-left: 0;border-top-left-radius: 0;border-bottom-left-radius: 0;" required maxlength="16" id="new_phone" type="text" class="form-control form-control-custom text-sans-serif phoneFormat" placeholder="No Handpone" value="<?=$user_temp->phone?>" required=""> -->
                  
              </div>
              <div class="col-md-12 mt-3">
                <button id="kirim" type="submit" class="btn btn-l btn-block btn-primary kirim">Kirim</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- END MODAL -->

<?php if($user_temp){ ?>

<!--Modal Kode Aktivasi-->
<div class="modal fade" tabindex="-1" role="dialog" id="modalActivation">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-body p-box">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
        <h4 class="modal-title mb-2 text-center">Kirim Kode Aktivasi</h4>
        <div class="container pb-3">
          <form action="<?=base_url(aksestoko_route('aksestoko/auth/send_activation_code'))?>" method="POST" id="form-send-code">
            <input type="hidden" name="user_id" value="<?=$user_temp->id?>">
            <div class="row">
              <div class="col-md-12 mt-3" >
                <label>Kirim ke No Telepon berikut atau <a href="javascript:void(0)" id="changePhone">ganti</a></label>
                <!-- <input type="text" required readonly class="form-control form-control-custom text-sans-serif" id="new_phone" name="new_phone" value="<?=$user_temp->phone?>" placeholder="Masukkan No Telepon Anda" > -->
                <div class="input-group-prepend">
                  <span style="border-right: 0;border-bottom-right-radius: 0;border-top-right-radius: 0;" class="input-group-text"><img src="<?=$assets_at?>img/common/flag_indonesia.png" class="img-flag-phone">+62</span>
                  <input style="border-left: 0;border-top-left-radius: 0;border-bottom-left-radius: 0;" readonly required maxlength="16" id="new_phone" type="text" class="form-control form-control-custom text-sans-serif phoneFormat" placeholder="No Handpone" value="<?=$user_temp->phone?>" required="">
                  
                </div>
                <div class="validationPhone">
                  <span id="notifValidationPhone"></span>
                </div>
                <div style="display:none;">
                  <input type="text" class="phoneFormatHidden" name="new_phone" placeholder="No Telepon" required="">
                </div>
                <small id="emailHelp" class="form-text text-left text-sans-serif text-danger font-italic">Gunakan No Telepon yang valid untuk menerima SMS Kode Aktivasi.</small>              
              </div>
              <div class="col-md-12 mt-3" id="sendActivation">
                <?php if($timeleft >= 0){ ?>
                  <button class="btn btn-l btn-block btn-primary" disabled>Mohon tunggu (<span id="timeleft_register"><?=$timeleft?></span>)</button>
                  <small class="form-text text-left text-sans-serif text-danger font-italic">Sudah melakukan pengiriman kode aktivasi. Perlu menunggu untuk dapat mengirim kode aktivasi kembali.</small>
                <?php } else { ?>
                  <button id="kirimAktivasi" type="submit" class="btn btn-l btn-block btn-primary">Kirim</button>
                <?php } ?>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- END MODAL -->

<?php } ?>

<?php if($new_register){ ?>

<!--Modal Kode Aktivasi-->
<div class="modal fade" tabindex="-1" role="dialog" id="modalRecoveryCode" data-keyboard="false" data-backdrop="static">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-body p-box">
        <h4 class="modal-title mb-2 text-center">Kode Pemulihan</h4>
        <div class="container pb-3">
            <div class="row">
              <div class="col-md-12 mt-3" >
                <label for="recovery_code">Simpan kode pemulihan berikut dengan melakukan tangkapan layar atau dicatat secara terpisah, kode ini untuk membantu apabila anda mengalami lupa kata sandi.</label>
                
                <div class="form-group text-center">
                  <div class="input-group-prepend">
                    <input type="text" class="form-control form-control-custom text-sans-serif text-center bg-white" id="recovery_code" readonly value="<?=$user_temp->recovery_code?>" style="border-right: 0;border-top-right-radius: 0;border-bottom-right-radius: 0; font-size: 12px;">
                    <span style="border-left: 0;border-bottom-left-radius: 0;border-top-left-radius: 0; cursor: pointer;" class="btn btn-primary input-group-text" onclick="copyCode()">Salin</span>
                  </div>
                  <small class="text-danger">Kode ini hanya ditampilkan sekali pada saat ini. Jadi, mohon benar-benar disimpan dengan baik.</small>
                </div>
                  
              </div>
              <div class="col-md-12 mt-3">
                <button class="btn btn-l btn-block btn-primary" data-dismiss="modal">Oke, Saya paham</button>
              </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- END MODAL -->

<?php } ?>

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

$(document).ready(function(){
  $(".phoneFormat").keyup(function(event){
    $(this).val(format($(this).val()));

  }).keyup();

  $('#new_phone').blur(function(e) {
    if (validatePhone('new_phone')) {
        $("#kirimAktivasi").removeAttr('disabled');
        $('#notifValidationPhone').html('');
    }
    else {
        $('#notifValidationPhone').html('Masukkan no telepon yang valid');
        $('#notifValidationPhone').css('color', 'red');
        $('#kirimAktivasi').attr('disabled', 'disabled');
    }
  });

});


function copyCode() {
    var copyText = document.getElementById(`recovery_code`);
    copyText.select();
    document.execCommand("copy");
    alert("Berhasil Disalin Kode Pemulihan : " + copyText.value);
  }

function convertTime(second) {
    let minutes = parseInt(second / 60).toString().padStart(2, '0');
    let seconds = parseInt(second % 60).toString().padStart(2, '0');
    return `${minutes}:${seconds}`
  }

function angka(e) {
  if (!/^[0-9]+$/.test(e.value)) {
    e.value = e.value.substring(0,e.value.length-1);
  }
}

function validatePhone(new_phone, idx = -1) {
  
  if (idx != -1){
    var a = document.getElementsByClassName(hanyaDigit).value; 
    // var filter = /(\ [0-9]*)$/;
    var filter = /^[0-9]+$/;
    
  }else {
    var a = document.getElementById(new_phone).value;
    // var filter = /^((\+[1-9]{1,4}[ \-]*)|(\([0-9]{2,3}\)[ \-]*)|([0-9]{2,4})[ \-]*)*?[0-9]{3,4}?[ \-]*[0-9]{3,4}?$/;
      var filter = /^(([1-9]{1,4}[ \-]*)|(\([0-9]{2,3}\)[ \-]*)|([0-9]{2,4})[ \-]*)*?[0-9]{3,4}?[ \-]*[0-9]{3,4}?$/;
  }
  console.log(filter);
  if (filter.test(a)) {
      return true;
    }
    else {
      return false;
    }
  
}

$("#form-send-code").submit(function( event ) {
  $("#kirimAktivasi").html("Memuat...");
  $("#kirimAktivasi").attr("disabled", "disabled");
});

// Sementara Tidak DiPakai
function limitCharacter(event)
{
  key = event.which || event.keyCode;
  if ( key != 188 // Comma
     && key != 8 // Backspace
     && key != 17 && key != 86 & key != 67 // Ctrl c, ctrl v
     && key != 13 && key != 37 && key != 39
     && (key < 48 || key > 57) // Non digit
     // Dan masih banyak lagi seperti tombol del, panah kiri dan kanan, tombol tab, dll
    ) 
  {
    event.preventDefault();
    return false;
  }
}


var format = function(num){
  let a = num;
  if( a.charAt(0) == '0' ){
    a=a.substr(1); 
  }else if (a.charAt(0) == '6' && a.charAt(1) == '2'){
    a=a.substr(2);
  }
  num=a;
  var str = num.toString().replace("", ""), parts = false, output = [], i = 1, formatted = null;
  if(str.indexOf(".") > 0) {
    parts = str.split(".");
    str = parts[0];
  }
  str = str.split("").reverse();
  for(var j = 0, len = str.length; j < len; j++) {
    if(str[j] != "-") {
      output.push(str[j]);
      if(i%3 == 0 && j < (len - 1)) {
        output.push("-");
      }
      i++;
    }
  }
  formatted = output.reverse().join("");

  return("" + formatted + ((parts) ? "." + parts[1].substr(0, 2) : ""));
};


$(".phoneFormat").bind("keyup tap change paste", function() {
  let a =  $(this).val().replace(/-/g , '');
    if( a.charAt(0) == '0' ){
      a=a.substr(1);
    }else if (a.charAt(0) == '6' && a.charAt(1) == '2' ){
      a=a.substr(2);
    }
  $(".phoneFormatHidden").val("62" + a);
}).change();


 // SHOW HIDE PASSWORD 
$("#password").on("keyup",function(){
  if($(this).val()){
    $(".fa-eye").show();
  } else {
    $(".fa-eye").hide();
  }
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

  $("#changePhone").click(function () {
    $("#new_phone").removeAttr('readonly');
    // $("#kirimAktivasi").attr('disabled', 'disabled' );
  })

  $("#activationBtn").click(function () {
    $("#modalActivation").modal('show')
  }).click();

  $("#recoveryBtn").click(function () {
    $("#modalRecoveryCode").modal('show')
  }).click();

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

  var timeleft = $('#timeleft_register').html();
  var first = false;

  setInterval(function(){
    timeleft = timeleft > 0 ? timeleft-1 : 0;
    if(timeleft > 0){
      $("#timeleft_register").html(`${convertTime(timeleft)}`)
    } else {
      $('#sendActivation').html(`<button id="kirimAktivasi" type="submit" class="btn btn-l btn-block btn-primary">Kirim</button>`);
    }
  }, 1000);
})();

</script>

<script>

  // Define the tour!
  var tour = {
      id: "guide-home",
      onClose: function(){
          localStorage.setItem('tour-homepage',true);
          //  console.log('aku');
      },
      steps: [
          {
              title: "Masukkan Username Anda",
              content: "Masukkan ID Bisnis Kokoh, Email & No Tlp",
              target: "input#username",
              placement: "left",
          },
          {
              title: "Masukkan Password Anda",
              content: "Masukkan Password Anda",
              target: "input#password",
              placement: "left",
          },
          {
              title: "Mulai!!",
              content: "Tekan tombol <strong>Masuk</strong> jika <strong>Email & Password </strong> sudah benar",
              target: "button.btn.btn-l.btn-block.btn-primary",
              placement: "left",
              
          },
          {
              title: "Lupa Kata Sandi ?",
              content: "Tekan tombol ini",
              target: "a.text-muted.text-sans-serif",
              placement: "top"
          },
          {
              title: "Belum Punya Akun ?",
              content: "Tekan tombol <strong>Daftar</strong>",
              target: "a.btn.btn-l.btn-block.text-primary",
              placement: "left"
          },
          {
              title: "Ingat Saya",
              content: "Tekan tombol ini",
              target: "input#remember",
              placement: "top"
          }
      ]
  };
  
  if(!localStorage.getItem('tour-homepage')){
      // Start the tour!
      hopscotch.startTour(tour);
  }

</script>
<script>
  (function(w,d,u){
    var s=d.createElement('script');s.async=true;s.src=u+'?'+(Date.now()/60000|0);
    var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);
  })(window,document,'https://cdn.bitrix24.id/b11907515/crm/site_button/loader_3_wclev2.js');
</script>
</body>

</html>
