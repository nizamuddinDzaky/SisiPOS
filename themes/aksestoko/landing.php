<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  
  <title>Selamat Datang di AksesToko</title>
  <!-- Primary Meta Tags -->
  <meta name="title" content="Selamat Datang di AksesToko">
  <meta name="description" content="<?=  $cms ? $cms->header_title : 'CEPAT. MUDAH. LEBIH MENGUNTUNGKAN.' ?> <?=  $cms ? $cms->header_caption : 'Selamat datang di Solusi Digital Semen Indonesia. Segera daftar dengan ID Bisnis Kokoh Anda!' ?>">

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?=current_url()?>">
  <meta property="og:title" content="Selamat Datang di AksesToko">
  <meta property="og:description" content="<?=  $cms ? $cms->header_title : 'CEPAT. MUDAH. LEBIH MENGUNTUNGKAN.' ?> <?=  $cms ? $cms->header_caption : 'Selamat datang di Solusi Digital Semen Indonesia. Segera daftar dengan ID Bisnis Kokoh Anda!' ?>">
  <meta property="og:image" content="<?= $cms ? base_url('assets/uploads/cms/') . $cms->header_bg : $assets_at.'img/bg-masthead.jpg'?>">
  <meta property="og:image:width" content="500">
  <meta property="og:image:height" content="250">

  <!-- Twitter -->
  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="<?=current_url()?>">
  <meta property="twitter:title" content="Selamat Datang di AksesToko">
  <meta property="twitter:description" content="<?=  $cms ? $cms->header_title : 'CEPAT. MUDAH. LEBIH MENGUNTUNGKAN.' ?> <?=  $cms ? $cms->header_caption : 'Selamat datang di Solusi Digital Semen Indonesia. Segera daftar dengan ID Bisnis Kokoh Anda!' ?>">
  <meta property="twitter:image" content="<?= $cms ? base_url('assets/uploads/cms/') . $cms->header_bg : $assets_at.'img/bg-masthead.jpg'?>">

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

  <link href="<?=$assets_at?>guide/css/hopscotch.css" rel="stylesheet"/>

</head>

<style>
.faq:hover{
  color:#c5c5c5 !important;
  text-decoration:none;
}
.carousel-caption-custom{
  position: absolute;
    top: 27%;
    left: 11%;
    z-index: 10;
    padding-top: 20px;
    padding-bottom: 20px;
    color: #fff;
    text-align: left;

}
</style>

<body id="page-top">

<?php if (SERVER_QA) { ?>
  <div id="snackbar">QA SERVER</div>
<?php } ?>

  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg navbar-light fixed-top" id="mainNav" style="background-color: white !important">
    <div class="container">
      <a class="navbar-brand js-scroll-trigger" href="javascript:void(0)">
        <img src="<?=base_url('assets/uploads/cms/') . $cms->logo_1?>" onerror="this.src='<?=$assets_at?>img/logo-at.png?v=<?=FORCAPOS_VERSION?>'" alt="logo" height="60">
      </a>

      <!-- MENU BURGER TOGGLE -->
      
      <!-- <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarResponsive">
        <div class="ml-auto my-2 my-lg-0"> -->

      <!-- END MENU BURGER TOGGLE -->

          <!-- Ganti Bahasa -->

          <!-- <div class="dropdown float-left" style="border-right: 1px solid #b9b9b9;">
            <button class="btn btn-l dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <img src="<?=$assets_at?>img/flag/id.svg" alt="ID"> ID
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
              <a class="dropdown-item text-sans-serif" href="#"><img src="<?=$assets_at?>img/flag/id.svg" alt="ID"> ID</a>
              <a class="dropdown-item text-sans-serif" href="#"><img src="<?=$assets_at?>img/flag/en.svg" alt="EN"> EN</a>
            </div>
          </div> -->
          
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class=" btn btn-l js-scroll-trigger text-primary"  href="<?= base_url(aksestoko_route('aksestoko/auth/signin'))?>">Masuk</a>
            </li>
            <li class="nav-item hide-on-mobile">
              <a class="btn btn-primary btn-l js-scroll-trigger " href="<?= base_url(aksestoko_route('aksestoko/auth/signup'))?>">Daftar</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </nav>

  <header class="masthead" style="background-image:linear-gradient(to bottom, rgba(0, 0, 0, 0.1) 0%, rgba(0, 0, 0, 0.8) 100%), url('<?= $cms ? base_url('assets/uploads/cms/') . $cms->header_bg : $assets_at.'/img/bg-masthead.jpg'?>');">
    <div class="container h-100" style="padding-left: 10%; padding-right: 10%;">
      <div class="row h-100 align-items-start justify-content-start text-left">
        <div class="col-lg-10 align-self-end">
          <h1 class="text-uppercase text-white font-weight-bold"><?=  $cms ? $cms->header_title : 'CEPAT. MUDAH. LEBIH MENGUNTUNGKAN.' ?></h1>
        </div>
        <div class="col-lg-8 align-self-baseline">
          <p class="text-white-75 mb-5"><?=  $cms ? $cms->header_caption : 'Selamat datang di Solusi Digital Semen Indonesia. Segera daftar dengan ID Bisnis Kokoh Anda!' ?></p>
            <a class="btn btn-primary btn-xl js-scroll-trigger" href="<?= base_url(aksestoko_route('aksestoko/auth/signup'))?>">Daftar Sekarang</a>
        </div>
        <div class="col-lg-12 align-self-center justify-content-center text-center">
          <a class="start-welcome js-scroll-trigger" href="#about">
            <i class="fa fa-chevron-down fa-3x"></i>
          </a>
        </div>
      </div>
    </div>
  </header>

    <!-- SLIDER -->
  <!-- <header>
    <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
      <ol class="carousel-indicators">
        <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
        <li data-target="#carouselExampleIndicators" data-slide-to="1"></li> -->
        <!-- <li data-target="#carouselExampleIndicators" data-slide-to="2"></li> -->
      <!-- </ol>
      <div class="carousel-inner" style="background:#000;">
        <div class="carousel-item active">
           <header class="masthead" style="background-image:linear-gradient(to bottom, rgba(0, 0, 0, 0.1) 0%, rgba(0, 0, 0, 0.8) 100%), url('<?= $cms ? base_url('assets/uploads/cms/') . $cms->header_bg : $assets_at.'/img/bg-masthead.jpg'?>');">
            <div class="container h-100" style="padding-left: 10%; padding-right: 10%;">
              <div class="row h-100 align-items-start justify-content-start text-left">
                <div class="col-lg-10 align-self-end">
                  <h1 class="text-uppercase text-white font-weight-bold"><?=  $cms ? $cms->header_title : 'CEPAT. MUDAH. LEBIH MENGUNTUNGKAN.' ?></h1>
                </div>
                <div class="col-lg-8 align-self-baseline">
                  <p class="text-white-75 font-weight-light mb-5"><?=  $cms ? $cms->header_caption : 'Selamat datang di Solusi Digital Semen Indonesia. Segera daftar dengan ID Bisnis Kokoh Anda!' ?></p>
                    <a class="btn btn-primary btn-xl js-scroll-trigger" href="<?= base_url(aksestoko_route('aksestoko/auth/signup'))?>">Daftar Sekarang</a>
                </div>
                <div class="col-lg-12 align-self-center justify-content-center text-center">
                  <a class="start-welcome js-scroll-trigger" href="#about">
                    <i class="fa fa-chevron-down fa-3x"></i>
                  </a>
                </div>
              </div>
            </div>
          </header>
        </div>
        <div class="carousel-item">
          <header class="masthead" style="background-image:linear-gradient(to bottom, rgba(0, 0, 0, 0.1) 0%, rgba(0, 0, 0, 0.8) 100%), url('<?= $cms ? base_url('assets/uploads/cms/') . $cms->header_bg : $assets_at.'/img/rmx-retail-banner-online.jpg'?>');">
            <div class="container h-100" style="padding-left: 10%; padding-right: 10%;">
              <div class="row h-100 align-items-start justify-content-start text-left">
                <div class="col-lg-10 align-self-end">
                  <h1 class="text-uppercase text-white font-weight-bold"><?=  $cms ? $cms->header_title : 'Ready Mix Beton' ?></h1>
                </div>
                <div class="col-lg-8 align-self-baseline">
                  <p class="text-white-75 font-weight-light mb-5"><?=  $cms ? $cms->header_caption : 'Pemesanan Ready Mix Beton Bisa Disini. Bangun Rumah Lebih Praktis' ?></p>
                    <a class="btn btn-primary btn-xl js-scroll-trigger" href="<?= base_url(aksestoko_route('aksestoko/auth/signup'))?>">Daftar Sekarang</a>
                </div>
                <div class="col-lg-12 align-self-center justify-content-center text-center">
                  <a class="start-welcome js-scroll-trigger" href="#about">
                    <i class="fa fa-chevron-down fa-3x"></i>
                  </a>
                </div>
              </div>
            </div>
        </header>
      </div>
  
    </div>
      <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
      </a>
      <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
      </a>
    </div>
  </header>  -->
  <!-- END-SLIDER -->

  <!-- About Section -->
  <section class="page-section bg-white" id="about">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8 text-center">
          <h2 class="text-primary mt-0"><?=  $cms ? $cms->about_title : 'Tentang Solusi Digital Semen Indonesia'?></h2>
          <hr class="divider my-4">
          <p class="text-muted mb-4"> <?=  $cms ? $cms->about_caption : 'Semen Indonesia berkomitmen untuk selalu memberikan layanan terbaik kepada mitra kami dengan menggunakan teknologi dan integrasi semua proses ke dalam satu solusi. Anda dapat melihat dan memesan semua produk Semen Indonesia, membayar dengan berbagai metode pembayaran, memperbarui alamat pengiriman, mendapatkan diskon tambahan, semua di satu tempat.'?></p>
        </div>
      </div>
    </div>
  </section>

  <!-- Services Section -->
  <section class="page-section" style="background-color: #fafafa" id="services">
    <div class="container">
      <h2 class="text-primary text-center mt-0"> <?=  $cms ? $cms->how_title : 'Cara Penggunaan'?></h2>
      <hr class="divider my-4">
      <div class="row">
        <div class="col-lg-4 col-md-4 text-center">
          <div class="mt-5">
            <img class="img-fluid" src="<?=base_url('assets/uploads/cms/') . $cms->how_image_1?>" onerror="this.src='<?=$assets_at?>img/langkah-1.png'" alt="">
            <h3 class="text-primary h4 mt-3 mb-2"><?=  $cms ? $cms->how_title_1 : 'Langkah 1'?></h3>
            <p class="text-muted mb-0"><?=  $cms ? $cms->how_caption_1 : 'Daftar dengan ID Bisnis Kokoh Anda'?></p>
          </div>
        </div>
        <div class="col-lg-4 col-md-4 text-center">
          <div class="mt-5">
            <img class="img-fluid" src="<?=base_url('assets/uploads/cms/') . $cms->how_image_2?>" onerror="this.src='<?=$assets_at?>img/langkah-2.png'" alt="">
            <h3 class="text-primary h4 mt-3 mb-2"><?=  $cms ? $cms->how_title_2 : 'Langkah 2'?></h3>
            <p class="text-muted mb-0"><?=  $cms ? $cms->how_caption_2 : 'Pilih & Bayar pesanan Anda'?></p>
          </div>
        </div>
        <div class="col-lg-4 col-md-4 text-center">
          <div class="mt-5">
            <img class="img-fluid" src="<?=base_url('assets/uploads/cms/') . $cms->how_image_3?>" onerror="this.src='<?=$assets_at?>img/langkah-3.png'" alt="">
            <h3 class="text-primary h4 mt-3 mb-2"><?=  $cms ? $cms->how_title_3 : 'Langkah 3'?></h3>
            <p class="text-muted mb-0"><?=  $cms ? $cms->how_caption_3 : 'Pesanan dikirim ke toko atau proyek'?></p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Portfolio Section -->
  <section class="bg-white page-section" id="portfolio">
    <div class="container">
      <h2 class="text-primary text-center mt-0"><?=  $cms ? $cms->benefit_title : 'Keuntungan'?></h2>
      <hr class="divider my-4">
      <div class="row">
        <div class="col-lg-4 col-md-4">
          <div class="mt-5 text-center">
            <img class="img-fluid" src="<?=base_url('assets/uploads/cms/') . $cms->benefit_image_1?>" onerror="this.src='<?=$assets_at?>img/lebih-cepat.png'" alt="">
            <h3 class="h4 mb-2 px-4"><?=  $cms ? $cms->benefit_title_1 : 'Lebih Cepat'?></h3>
            <p class="text-muted px-4 mb-0"><?=  $cms ? $cms->benefit_caption_1 : 'Pengiriman langsung dan dapat dilacak, ke toko atau proyek.'?></p>
          </div>
        </div>
        <div class="col-lg-4 col-md-4">
          <div class="mt-5 text-center">
            <img class="img-fluid" src="<?=base_url('assets/uploads/cms/') . $cms->benefit_image_2?>" onerror="this.src='<?=$assets_at?>img/lebih-mudah.png'" alt="">
            <h3 class="h4 px-4 mb-2"><?=  $cms ? $cms->benefit_title_2 : 'Lebih Mudah'?></h3>
            <p class="text-muted px-4 mb-0"><?=  $cms ? $cms->benefit_caption_2 : 'Lihat semua produk Semen Indonesia lengkap dengan stok, bayar menggunakan berbagai metode pembayaran, diskon menarik dan persyaratan mudah.'?><p>
          </div>
        </div>
        <div class="col-lg-4 col-md-4">
          <div class="mt-5 text-center">
            <img class="img-fluid" src="<?=base_url('assets/uploads/cms/') . $cms->benefit_image_3?>" onerror="this.src='<?=$assets_at?>img/lebih-menguntungkan.png'" alt="">
            <h3 class="h4 px-4 mb-2"><?=  $cms ? $cms->benefit_title_3 : 'Lebih Menguntungkan'?></h3>
            <p class="text-muted px-4 mb-0"><?=  $cms ? $cms->benefit_caption_3 : 'Lihat hadiah kontraktual Anda.'?></p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-dark py-5">
    <div class="container">
      <div class="row">
        <div class="col-lg-3 col-md-3 mt-5">
          <img class="rounded d-block ml-3 px-4" src="<?= $cms ? base_url('assets/uploads/cms/') . $cms->logo_2 : $assets_at . 'img/logo-at-putih.png' ?>" onerror="this.src='<?= $assets_at ?>img/logo-at-putih.png'" alt="Logo" width="200">
          <div class="mt-3 ml-3 px-4">
            <a target="_blank" href="<?=  $cms ? $cms->footer_link_wa : 'https://wa.me/'?>" class="text-dark d-inline-block">
              <span class="fa-stack">
                <i class="fab fa-whatsapp fa-stack-1x fa-inverse"></i>
              </span>
            </a>
            <a target="_blank" href="<?=  $cms ? $cms->footer_link_fb : 'https://www.facebook.com/aksestokoid'?>" class="text-dark d-inline-block">
              <span class="fa-stack">
                <i class="fab fa-facebook-f fa-stack-1x fa-inverse"></i>
              </span>
            </a>
            <a target="_blank" href="<?=  $cms ? $cms->footer_link_twitter : 'https://twitter.com/'?>" class="text-dark d-inline-block">
              <span class="fa-stack">
                <i class="fab fa-twitter fa-stack-1x fa-inverse"></i>
              </span>
            </a>
            <a target="_blank" href="<?=  $cms ? $cms->footer_link_ig : 'https://www.instagram.com/aksestokoid'?>" class="text-dark d-inline-block">
              <span class="fa-stack">
                <i class="fab fa-instagram fa-stack-1x fa-inverse"></i>
              </span>
            </a>
          </div>
        </div>
        <div class="col-lg-2 col-md-2 mt-5">
          <h5 class="text-white mb-2 px-4 ml-3">Memulai</h5>
          <ul class="text-white" style="list-style-type: none;">
              <li class="mt-1">
                <a href="<?=base_url(aksestoko_route('aksestoko/auth/signup'))?>" class="text-white">
                  Daftar
                </a>
              </li>
              <li class="mt-1">
                <a href="<?=base_url(aksestoko_route('aksestoko/auth/signin'))?>" class="text-white">
                  Masuk
                </a>
              </li>
              <li class="mt-1">
                <a href="<?=base_url(aksestoko_route('aksestoko/auth/profile'))?>" class="text-white">
                  Akun
                </a>
              </li>
              <li class="mt-1">
                <a href="<?=base_url(aksestoko_route('aksestoko/order'))?>" class="text-white">
                  Pesanan
                </a>
              </li>
            </ul>
        </div>
        
        <div class="col-lg-2 col-md-2">
          <div class="mt-5">
            <h5 class="text-white mb-2 px-4 ml-3">Bantuan</h5>
            <ul class="text-white" style="list-style-type: none;">
              <li class="mt-1">
                <a href="<?=base_url(aksestoko_route('aksestoko/home/faq'))?>" class="text-white">
                  FAQ
                </a>
              </li>
              <li class="mt-1">
                <a href="<?=base_url(aksestoko_route('aksestoko/home/cs'))?>" class="text-white">
                  Layanan Pelanggan
                </a>
              </li>
              <li class="mt-1">
                <a href="javascript:void(0)" onclick="$('.b24-widget-button-block').click();" class="text-white">
                  Live Chat
                </a>
              </li>
            </ul>
          </div>
        </div>
        <div class="col-lg-3 col-md-3 mt-5">
          <h5 class="text-white mb-2 px-4 ml-3">Tentang Kami</h5>
          <ul class="text-white" style="list-style-type: none;">
              <li class="mt-1">
                <a href="https://sig.id" target="_blank" class="text-white">
                  PT Semen Indonesia (Persero) Tbk (SIG)
                </a>
              </li>
              <li class="mt-1">
                <a href="https://sisi.id" target="_blank" class="text-white">
                  PT Sinergi Informatika Semen Indonesia (SISI)
                </a>
              </li>
          </ul>
        </div>
        <div class="col-lg-2 col-md-2 mt-5">
          <h5 class="text-white mb-2 px-4 ml-3">Aplikasi</h5>
          <div class="ml-3 mt-3 px-4">
            <a target="_blank" href="https://play.google.com/store/apps/details?id=id.sisi.aksestokomobile"> 
              <img src="<?=$assets_at . 'img/gplay-id.png'?>" alt="gplay" width="150">            
            </a>
          </div>
        </div>
      </div>
    </div>
    
  </footer>
  <footer class="bg-dark p-0" style="background-color: #23282df5 !important;">
    <div class="container">
      <div class="row p-4">
          <div class="col-12 text-center">
            <small class="text-white mx-auto d-block"> <?=  $cms ? $cms->footer_right : '© '.date('Y').' PT Sinergi Informatika Semen Indonesia, anak usaha dari PT Semen Indonesia TBK. All rights reserved.'?></small>
          </div>
      </div>
    </div>
  </footer>

  <!-- Bootstrap core JavaScript -->
  <script src="<?=$assets_at?>vendor/jquery/jquery.min.js"></script>
  <script src="<?=$assets_at?>vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <!-- Plugin JavaScript -->
  <script src="<?=$assets_at?>vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="<?=$assets_at?>vendor/magnific-popup/jquery.magnific-popup.min.js"></script>

  <!-- Custom scripts for this template -->
  <script src="<?=$assets_at?>js/creative.min.js"></script>

  <script src="<?=$assets_at?>guide/js/hopscotch.js"></script>

  <script>
        // Define the tour!
        var tour = {
            id: "landingpage",
            onClose: function(){
                localStorage.setItem('landingpage',true);
                //  console.log('aku');
            },
            steps: [
                {
                    title: "Selamat Datang!!",
                    content: "di AksesToko",
                    target: "a.navbar-brand",
                    placement: "bottom",
                },
                {
                    title: "Belum Punya Akun ?",
                    content: "Tekan Tombol Daftar di Samping",
                    target: "a.btn.btn-primary.btn-l.js-scroll-trigger",
                    placement: "left",
                },
                {
                    title: "Mulai!!",
                    content: "Mari mulai",
                    target: "a.btn.btn-l.js-scroll-trigger.text-primary",
                    placement: "left",
                    multipage: true,
                    onNext: function() {
                        window.location = "<?= base_url(aksestoko_route('aksestoko/auth/signin'))?>"
                    }
                },
                {
                    title: "Isikan username",
                    content: "",
                    target: "identity",
                    placement: "bottom"
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
