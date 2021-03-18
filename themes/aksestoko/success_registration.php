<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <title>Berhasil Registrasi - AksesToko</title>
  <!-- Primary Meta Tags -->
  <meta name="title" content="Masuk - AksesToko">
  <meta name="description" content="Silakan isi dengan ID Bisnis Kokoh atau email atau nomer ponsel dan kata sandi yang digunakan ketika mendaftar.">

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= current_url() ?>">
  <meta property="og:title" content="Masuk - AksesToko">
  <meta property="og:description" content="Silakan isi dengan ID Bisnis Kokoh atau email atau nomer ponsel dan kata sandi yang digunakan ketika mendaftar.">
  <meta property="og:image" content="<?= $cms ? base_url('assets/uploads/cms/') . $cms->logo_1 : $assets_at . 'img/logo-at.png?v=' . FORCAPOS_VERSION ?>">
  <meta property="og:image:width" content="500">
  <meta property="og:image:height" content="170">

  <!-- Twitter -->
  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="<?= current_url() ?>">
  <meta property="twitter:title" content="Masuk - AksesToko">
  <meta property="twitter:description" content="Silakan isi dengan ID Bisnis Kokoh atau email atau nomer ponsel dan kata sandi yang digunakan ketika mendaftar.">
  <meta property="twitter:image" content="<?= $cms ? base_url('assets/uploads/cms/') . $cms->logo_1 : $assets_at . 'img/logo-at.png?v=' . FORCAPOS_VERSION ?>">

  <link rel="shortcut icon" href="<?= $assets_at ?>img/logo-at-short.png" type="image/x-icon">

  <!-- Font Awesome Icons -->
  <link href="<?= $assets_at ?>vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Merriweather+Sans:400,700" rel="stylesheet">
  <link href='https://fonts.googleapis.com/css?family=Merriweather:400,300,300italic,400italic,700,700italic' rel='stylesheet' type='text/css'>

  <!-- Plugin CSS -->
  <link href="<?= $assets_at ?>vendor/magnific-popup/magnific-popup.css" rel="stylesheet">

  <!-- Theme CSS - Includes Bootstrap -->
  <link href="<?= $assets_at ?>css/creative.css" rel="stylesheet">

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
            <div class="card-body">
              <a href="../home">
                <img class="m-4" src="<?= base_url('assets/uploads/cms/') . $cms->logo_1 ?>" onerror="this.src='<?= $assets_at ?>img/logo-at.png?v=<?= FORCAPOS_VERSION ?>'" alt="logo" width="250">
              </a>
              <?php if ($this->session->flashdata('error')) { ?>
                <div>
                  <h4 class="card-title text-center py-2 ">Gagal</h4>
                </div>
                <div class="card-text">
                  <img src="<?= $assets_at ?>img/common/failed.png" class="img-fluid success-regis" alt="Success" style="width: 200px;">
                </div>

                <div class="clearfix mt-3 text-center">
                  <div class="alert alert-danger alert-dismissible fade show text-left" role="alert">
                    <?= $this->session->flashdata('error') ?>
                    <button class="close" type="button" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">×</span>
                    </button>
                  </div>
                </div>
                <div class="clearfix mt-3 text-center">
                  <a href="#" target="_blank" class="btn btn-primary" style="width: 150px; border-radius: 40px;padding: .5rem 1.5rem;">OK</a>
                </div>
              <?php } ?>
              <?php if ($this->session->flashdata('success')) { ?>
                <div>
                  <h4 class="card-title text-center py-2 ">Berhasil</h4>
                </div>
                <div class="card-text">
                  <img src="<?= $assets_at ?>img/common/ic_success.png" class="img-fluid success-regis" alt="Success" style="width: 200px;">
                </div>
                <div class="clearfix mt-3 text-center">
                  <h6 class="card-subtitle pb-4 text-muted text-center mt-3"><b>Selamat</b>, anda telah terdaftar di <b>AksesToko</b>. <br> Jika ada kendala, mohon menghubungi call center kami melalui website kami.</h6>
                </div>
                <div class="clearfix mt-3 text-center">
                  <a href="#" target="_blank" class="btn btn-primary" style="width: 150px; border-radius: 40px;padding: .5rem 1.5rem;">OK</a>
                </div>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>




  <!-- Bootstrap core JavaScript -->
  <script src="<?= $assets_at ?>/vendor/jquery/jquery.min.js"></script>
  <script src="<?= $assets_at ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <!-- Plugin JavaScript -->
  <script src="<?= $assets_at ?>/vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="<?= $assets_at ?>/vendor/magnific-popup/jquery.magnific-popup.min.js"></script>

  <!-- Custom scripts for this template -->
  <script src="<?= $assets_at ?>/js/creative.min.js"></script>

  <script src="<?= $assets_at ?>guide/js/hopscotch.js"></script>
  <link href="<?= $assets_at ?>guide/css/hopscotch.css" rel="stylesheet" />


  <script>
    $("#form-send-code").submit(function(event) {
      $("#kirimAktivasi").html("Memuat...");
      $("#kirimAktivasi").attr("disabled", "disabled");
    });
  </script>


</body>

</html>