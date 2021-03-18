<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">

  <title>Kirim OTP - AksesToko</title>

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

  <!-- Masthead2 -->
  <header class="masthead2">
    <div class="container" style="padding-top: 2%;">
      <div class="row align-items-center justify-content-center text-center">
        <div class="col-lg-6 align-self-center">
          <div class="card">
            <a href="<?=base_url(aksestoko_route("aksestoko/auth/signin"))?>" class="back-to"><i class="fa fa-chevron-left"></i></a>
            <div class="card-body">   
            <a href="../home">
                <img class="m-4" src="<?=base_url('assets/uploads/cms/') . $cms->logo_1?>" onerror="this.src='<?=$assets_at?>img/logo-at.png?v=<?=FORCAPOS_VERSION?>'" alt="logo" width="250">
              </a>
              <h4 class="card-title text-left py-2">Kirim OTP</h4>
              <h6 class="card-subtitle pb-4 text-muted text-left">
                Silakan pilih layanan untuk mengirimkan kode OTP.
              </h6>

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
                <div class="container mt-4">
                  <div class="row">
                    <div class="col-md-12">
                      <div class="list-send-otp box p-box shadow mb-3 animated fadeInUp delayp1">
                        <a class="text-decorat-none sms" href="<?=$user->phone_is_verified ? base_url(aksestoko_route("aksestoko/auth/send_otp/sms")) : 'javascript:void(0)'?>">
                          <div id="show" class="row p-4 align-items-start justify-content-start" checked="">
                            <div class="col-3 align-self-center justify-content-center text-center">
                              <i class="fas fa-sms fa-4x"></i>
                            </div>
                            <div class="col-9 text-left">
                              <h5 class="my-0 text-dark py-1">SMS Ke <?=$user->phone?></h5> 
                              <span class="my-0 text-muted">Mengirimkan kode OTP melalui SMS</span>
                              <?php if(!$user->phone_is_verified){ ?>
                                <br>                              
                                <small class="text-danger">Nomor Telepon belum terverifikasi tidak dapat memilih layanan ini</small>
                              <?php } ?>
                            </div>
                          </div>
                        </a>
                      </div>
                    </div>

                    <div class="col-md-12">
                      <div class="list-send-otp box p-box shadow mb-3 animated fadeInUp delayp1">
                        <a class="text-decorat-none wa" href="<?=$user->phone_is_verified ? base_url(aksestoko_route("aksestoko/auth/send_otp/wa")) : 'javascript:void(0)'?>">
                          <div id="show" class="row p-4 align-items-start justify-content-start" checked="">
                            <div class="col-3 align-self-center justify-content-center text-center">
                              <i class="fab fa-whatsapp fa-4x"></i>
                            </div>
                            <div class="col-9 text-left">
                              <h5 class="my-0 text-dark py-1">WhatsApp Ke <?=$user->phone?></h5> 
                              <span class="my-0 text-muted">Mengirimkan kode OTP melalui WhatsApp</span>
                              <?php if(!$user->phone_is_verified){ ?>
                                <br>                              
                                <small class="text-danger">Nomor Telepon belum terverifikasi tidak dapat memilih layanan ini</small>
                              <?php } ?></div>
                          </div>
                        </a>
                      </div>
                    </div>

                    <!-- <div class="col-md-12">
                      <div class="list-send-otp box p-box shadow mb-3 animated fadeInUp delayp1">
                        <a class="text-decorat-none cs" href="<?=base_url(aksestoko_route("aksestoko/auth/send_otp/helpdesk"))?>">
                          <div id="show" class="row align-items-start justify-content-start p-4" checked="">
                            <div class="col-3 align-self-center justify-content-center text-center">
                              <i class="fas fa-user-astronaut fa-4x"></i>
                            </div>
                            <div class="col-9 text-left">
                              <h5 class="my-0 text-otp text-dark py-1">Layanan Pelanggan</h5> 
                              <span class="my-0 text-muted">Tanyakan langsung kode OTP dengan menghubungi 0811-6065-246 (WhatsApp) </span >
                            </div>
                          </div>
                        </a>
                      </div>
                    </div> -->

                    <div class="col-md-12">
                      <div class="list-send-otp box p-box shadow mb-3 animated fadeInUp delayp1">
                        <a class="text-decorat-none cs" href="<?=$user->recovery_code ? base_url(aksestoko_route("aksestoko/auth/send_otp/recovery_code")) : 'javascript:void(0)'?>">
                          <div id="show" class="row align-items-start justify-content-start p-4" checked="">
                            <div class="col-3 align-self-center justify-content-center text-center">
                              <i class="fa fa-code fa-3x"></i>
                            </div>
                            <div class="col-9 text-left">
                              <h5 class="my-0 text-dark py-1">Kode Pemulihan</h5> 
                              <span class="my-0 text-muted">Reset kata sandi dengan memasukkan kode pemulihan yang didapatkan saat pendaftaran.</span>
                              <?php if(!$user->recovery_code){ ?>
                                <br>                              
                                <small class="text-danger">Kode pemulihan belum diset tidak dapat memilih layanan ini</small>
                              <?php } ?></div>
                            </div>
                          </div>
                        </a>
                      </div>
                    </div>

                  </div>                      
                </div>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>



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

    function goBack() {
      window.history.back();
    }

    </script>

 <script>

        // Define the tour!
        var tour = {
            id: "send-otp",
            onClose: function(){
                localStorage.setItem('send-otp',true);
                //  console.log('aku');
            },
            steps: [
                {
                    title: "-",
                    content: "-",
                    target: "-",
                    placement: "left",
                }
            ]
        };
        
        if(!localStorage.getItem('send-otp')){
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
