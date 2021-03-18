<!DOCTYPE html>
<html class="no-js" lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title><?= $title_at ?></title>
  <!-- Primary Meta Tags -->
  <meta name="title" content="<?= $title_at ?>">
  <meta name="description" content="<?= $cms ? $cms->header_title : 'CEPAT. MUDAH. LEBIH MENGUNTUNGKAN.' ?> <?= $cms ? $cms->header_caption : 'Selamat datang di Solusi Digital Semen Indonesia. Segera daftar dengan ID Bisnis Kokoh Anda!' ?>">

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= current_url() ?>">
  <meta property="og:title" content="<?= $title_at ?>">
  <meta property="og:description" content="<?= $cms ? $cms->header_title : 'CEPAT. MUDAH. LEBIH MENGUNTUNGKAN.' ?> <?= $cms ? $cms->header_caption : 'Selamat datang di Solusi Digital Semen Indonesia. Segera daftar dengan ID Bisnis Kokoh Anda!' ?>">
  <meta property="og:image" content="<?= $cms ? base_url('assets/uploads/cms/') . $cms->header_bg : $assets_at . 'img/bg-masthead.jpg' ?>">
  <meta property="og:image:width" content="500">
  <meta property="og:image:height" content="250">

  <!-- Twitter -->
  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="<?= current_url() ?>">
  <meta property="twitter:title" content="<?= $title_at ?>">
  <meta property="twitter:description" content="<?= $cms ? $cms->header_title : 'CEPAT. MUDAH. LEBIH MENGUNTUNGKAN.' ?> <?= $cms ? $cms->header_caption : 'Selamat datang di Solusi Digital Semen Indonesia. Segera daftar dengan ID Bisnis Kokoh Anda!' ?>">
  <meta property="twitter:image" content="<?= $cms ? base_url('assets/uploads/cms/') . $cms->header_bg : $assets_at . 'img/bg-masthead.jpg' ?>">

  <link rel="shortcut icon" href="<?= $assets_at ?>img/logo-at-short.png" type="image/x-icon">
  <link rel="apple-touch-icon" href="<?= $assets_at ?>ico/apple-touch-icon.png">
  <link rel="stylesheet" href="<?= $assets_at ?>css/main.css">
  <link rel="stylesheet" href="<?= $assets_at ?>css/custom.css">

  
  <!--Lightbox CSS-->
  <link rel="stylesheet" type="text/css" href="<?= $assets_at ?>plugins/lightbox/css/lightbox.css">
  <!-- END -->

  <!-- Custom Search Css -->
  <link rel="stylesheet" type="text/css" href="<?= $assets_at ?>css/custom-search/component.css">


  <link href="<?= $assets_at ?>guide/css/hopscotch.css" rel="stylesheet" />

  <?php if (SOCKET_NOTIFICATION) { ?>
    <!-- SOCKET.IO -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.3.0/socket.io.js"></script>
    <script>
      var socket = io.connect('<?= getenv('SOCKET_URL') ?>');
      socket.emit("sendClientInfo", {
        company_id: '<?= $this->session->userdata("company_id"); ?>',
        user_id: '<?= $this->session->userdata("user_id"); ?>',
        company: '<?= $this->session->userdata("company_name"); ?>',
        client_type: 'aksestoko',
        code: '<?= $this->session->userdata("username"); ?>',
        name: '<?= $this->session->userdata("company_name"); ?>',
        token: '<?= SOCKET_TOKEN ?>'
      });

      socket.on('message', function(data) {
        console.log(data);
      });

      socket.on('error', function(data) {
        console.error(data);
      });
    </script>
    <!-- END SOCKET -->
  <?php } ?>

  <script src="<?= $assets_at ?>guide/js/hopscotch.js"></script>
  <!-- <script src="<?= $assets_at ?>plugins/js/hopscotch.js"></script> -->

   <!--Bootstrap 3.7.7 dependency-->
  <script src="<?= $assets_at ?>plugins/jquery-3.3.1/jquery.min.js"></script>
  <script src="<?= $assets_at ?>plugins/modernizr-2.6.1/modernizr.min.js"></script>
  <script src="<?= $assets_at ?>plugins/bootstrap-3.3.7/js/bootstrap.min.js"></script>


  <!--Bootstrap 4.1.0 dependency-->
  <script src="<?= $assets_at ?>plugins/popper-1.14.0/popper.min.js"></script>

  <!--Lightbox JS-->
  <script src="<?= $assets_at ?>plugins/lightbox/js/lightbox.js"></script>

  <!-- Mask -->
  <!-- <script src="<?= $assets_at ?>js/jquery.mask.min.js"></script> -->

  <!-- Custom Search -->
  <script src="<?= $assets_at ?>js/custom-search/modernizr.custom.js"></script>

  <!-- Combodate -->
  <script src="<?= $assets_at ?>plugins/combodate-1.0.7/combodate.js"></script>
  <script src="<?= $assets_at ?>plugins/combodate-1.0.7/moment.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/id.min.js" integrity="sha512-he8U4ic6kf3kustvJfiERUpojM8barHoz0WYpAUDWQVn61efpm3aVAD8RWL8OloaDDzMZ1gZiubF9OSdYBqHfQ==" crossorigin="anonymous"></script>
  <!-- End -->

  <style>
        .close-modal-slider {
            color: #000 !important;
            opacity: 1 !important;
        }
        
        .close-modal-slider:hover,
        .close-modal-slider:focus {
            color: #000 !important;
            opacity: 1;
        }
        
        button.close-modal-slider {
            padding: 0;
            cursor: pointer;
            background: #fff !important;
            border: 0;
            -webkit-appearance: none;
            padding-bottom: 7px;
            padding-left: 7px;
            padding-right: 7px;
            border-radius: 50%;
        }
        
        .modal-dialog .close-modal-slider {
            position: absolute;
            right: -1rem;
            top: 1.8rem;
            -webkit-transition: all 0.2s ease-in-out;
            transition: all 0.2s ease-in-out;
        }
        .close-modal-slider{
          font-size: 25px !important;
        }
        
    </style>

    

</head>

<body>
  <nav class="navbar navbar-dashboard navbar-expand-lg fixed-top">
    <div class="container">
      <div class="d-flex justify-content-between w-100">

        <button class="navbar-toggler" type="button">
          <i class="fal fa-bars"></i>
        </button>

        <a class="navbar-brand center-sm-down-both" href="<?= base_url(aksestoko_route('aksestoko/home/')) ?>">
          <img src="<?= $cms ? base_url('assets/uploads/cms/') . $cms->logo_2 : $assets_at . 'img/logo-at-putih.png' ?>" onerror="this.src='<?= $assets_at ?>img/logo-at-putih.png'" alt="Logo">
        </a>

        <ul class="navbar-nav d-flex justify-content-end align-items-center flex-row">
        </ul>

      </div>
    </div>
  </nav>

<section class="py-main section-status-payment">
  <div class="container">
    <div class="box-status-payment">
      <div class="subheading text-center">
        <h2>Berhasil</h2>
        <img src="<?=$assets_at?>img/common/ic_success.png" class="img-fluid" alt="Success">
        <p>Terima kasih atas pengajuan kredit Anda. Mohon menunggu data Anda sedang diproses.</p>
      </div>
      <div class="clearfix mt-3 text-center">
        <a href="#" target="_blank" class="btn btn-primary">OK</a>
      </div>
    </div>
  </div>
</section>