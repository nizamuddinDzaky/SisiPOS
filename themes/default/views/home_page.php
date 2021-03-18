<!DOCTYPE html>
<html lang="en">

<head>

  <!-- All -->
  <meta charset="utf-8">
  <meta name="author" content="PT. Sinergi Informatika Semen Indonesia">
  <meta name="description" content="Boosting Your Business Performance - Forca Point Of Sales is an online inventory management application for cashier and manager store">
  <meta name="keywords" content="Forca, Forca POS, Forca Point Of Sales, Point Of Sales, Kasir, Cashier, Forca Kasir, Cashier Forca, Forca Cashier, POS, Business Performance, Boosting Your Business Performance, Boosting Your Business">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <!-- Twitter -->
  <meta name="twitter:card" content="summary" />
  <meta name="twitter:site" content="@ForcaPos" />
  <meta name="twitter:creator" content="@ForcaPos" />
  <meta property="og:url" content="<?= current_url() ?>" />
  <meta property="og:title" content="Forca POS - Boosting Your Business Performance" />
  <meta property="og:description" content="Boosting Your Business Performance - Forca Point Of Sales is an online inventory management application for cashier and manager store." />
  <meta property="og:image" content="<?php echo $assets ?>images/Background.jpg" />

  <!-- Open Graph / Facebook -->
  <meta property="og:url"                content="<?= current_url() ?>" />
  <meta property="og:type"               content="website" />
  <meta property="og:title"              content="Forca POS - Boosting Your Business Performance" />
  <meta property="og:description"        content="Boosting Your Business Performance - Forca Point Of Sales is an online inventory management application for cashier and manager store." />
  <meta property="og:image"              content="<?php echo $assets ?>images/Background.jpg" />


  <link rel="shortcut icon" href="<?= $assets ?>images/icon.png" />

  <title>Forca PoS</title>

  <!-- Bootstrap core CSS -->
  <link href="<?php echo $assets ?>js/bootstrap/css/bootstrap.css" rel="stylesheet">

  <!-- Custom fonts for this template -->
  <link href="<?php echo $assets ?>styles/helpers/font-awesome.min.css" rel="stylesheet" type="text/css">

  <!-- Custom styles for this template -->
  <link href="<?php echo $assets ?>styles/agency/agency.css" rel="stylesheet" />

  <link href="<?php echo $assets ?>guide/css/hopscotch.css" rel="stylesheet" />

</head>

<body id="page-top">
  <?php if (SERVER_QA) { ?>
    <div id="snackbar">QP SERVER</div>
  <?php } ?>
  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
    <div class="container">
      <div class="box-logo">
        <a class="navbar-brand js-scroll-trigger" href="#page-top"> <img src="<?php echo $assets ?>images/Logo.png" alt="Forca POS" width="50%" height="50%" /></a>
      </div>
      <div class="box-menu-header">
        <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
          Menu
          <i class="fa fa-bars"></i>
        </button>
      </div>
      <div class="collapse navbar-collapse" id="navbarResponsive">
        <ul class="navbar-nav text-uppercase ml-auto">
          <li class="nav-item">
            <a class="nav-link js-scroll-trigger" href="#services">Feature</a>
          </li>
          <li class="nav-item">
            <a class="nav-link js-scroll-trigger" href="#contactus">Contact us</a>
          </li>
          <li class="nav-item" id="btn-login">
            <a class="nav-link js-scroll-trigger" href="<?= site_url('login'); ?>">Login</a>
          </li>
          <li class="nav-item" id="btn-userguide">
            <a class="nav-link js-scroll-trigger" href="<?= base_url() . 'helps/' ?>">FAQ</a>
            <!-- <?= site_url('manualbook/Manual Book Forca Point of Sales v.4.0.pdf'); ?> -->
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Header -->
  <header class="masthead">
    <div class="container">
      <div class="intro-text">
        <div class="intro-lead-in">Boosting Your<br />Business Performance</div>
        <br />
        <br />
        <br />
        <br />
        <br />

        <p style="opacity: 0.8;font-size:18px;">Forca Point Of Sales is an online <br />inventory management application for <br />cashier and manager store</p>
        <!--          <a class="btn btn-light btn-xl text-uppercase js-scroll-trigger" href="#services">Get Started</a>
		  <a class="btn btn-primary btn-xl text-uppercase js-scroll-trigger" href="#services">Learn More</a>-->
      </div>
    </div>
  </header>

  <!-- Services -->
  <section id="services" style="background-color:white;">
    <div class="container">
      <div class="row">
        <div class="col-lg-12 text-center">
          <h2 class="section-heading text-uppercase">Feature</h2>
          <center>
            <div class="col-lg-6 col-md-12">
              <hr align="center" style="display: block;border-color:black;border-width: 3px;" />
            </div>
          </center>
          <div class="clearfix"></div>
        </div>
      </div>
      <div class="row  text-center">
        <div class="col-md-3">
          <span class="fa-stack fa-3x">
            <img src="<?php echo $assets ?>images/eccomerce.png" alt="E-Commerce" height="72" width="72">
          </span>
          <h5 class="service-heading">E-Commerce</h5>
          <p class="text-muted">Langsung terintegrasi dengan Ecomerce materia.id</p>
        </div>
        <div class="col-md-3">
          <span class="fa-stack fa-3x">
            <img src="<?php echo $assets ?>images/supportmobile.png" alt="Mobile POS" height="72" width="72">
          </span>
          <h5 class="service-heading">Mobile POS</h5>
          <p class="text-muted" style="text-align: justify;">Compatible dengan device Android.</p>
        </div>
        <div class="col-md-3">
          <span class="fa-stack fa-3x">
            <img src="<?php echo $assets ?>images/Inventory.png" alt="inventory" height="72" width="72">
          </span>
          <h5 class="service-heading">Inventory</h5>
          <p class="text-muted">Kemudahan dalam management dan monitoring barang</p>
        </div>
        <div class="col-md-3">
          <span class="fa-stack fa-3x">
            <img src="<?php echo $assets ?>images/promotion.png" alt="promotion" height="72" width="72">
          </span>
          <h5 class="service-heading">Pricing</h5>
          <p class="text-muted">Kemudahan dalam pengaturan diskon terhadap barang.</p>
        </div>
        <div class="col-md-3">
          <span class="fa-stack fa-3x">
            <img src="<?php echo $assets ?>images/membership.png" alt="membership" height="72" width="72">
          </span>
          <h5 class="service-heading">People</h5>
          <p class="text-muted">Management data pemasok, pelanggan dan karyawan.</p>
        </div>
        <div class="col-md-3">
          <span class="fa-stack fa-3x">
            <img src="<?php echo $assets ?>images/Loyalty.png" alt="Loyalty" height="72" width="72">
          </span>
          <h5 class="service-heading">Loyalty Program</h5>
          <p class="text-muted">Kemudahan dalam perhitungan point pelanggan.</p>
        </div>
        <div class="col-md-3">
          <span class="fa-stack fa-3x">
            <img src="<?php echo $assets ?>images/mailbill.png" alt="Digital Bill" height="72" width="72">
          </span>
          <h5 class="service-heading">Email Receipt</h5>
          <p class="text-muted">Struk Penjualan dapat di kirimkan melalui email.</p>
        </div>
        <div class="col-md-3">
          <span class="fa-stack fa-3x">
            <img src="<?php echo $assets ?>images/Analyze.png" alt="Analyze" height="72" width="72">
          </span>
          <h5 class="service-heading">Analyze</h5>
          <p class="text-muted">Grafik terhadap performa karyawan, penjualan product, dsb.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Team -->
  <section class="bg-light" id="contactus">
    <div class="container">
      <div class="row">
        <div class="col-lg-12 text-center">
          <h2 class="section-heading text-uppercase">Hubungi Kami</h2>
          <center>
            <div class="col-lg-6 col-md-12">
              <hr align="center" style="display: block;border-color:black;border-width: 3px;" />
            </div>
          </center>
          <div class="clearfix"></div>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-6 col-md-12">
          <p>Hubungi kami melalui kontak berikut ini </p>
          <p><img src="<?php echo $assets ?>images/phone.png" alt="Phone" height="48" width="48"> + 62-21-5213711 Ext. 200 </p>
          <p><img src="<?php echo $assets ?>images/mail.png" alt="Mail" height="48" width="48"> ptsisi@sisi.id </p>
          <p>Media sosial resmi kami</p>
          <p>
            <a href=" https://play.google.com/store/apps/developer?id=PT.+Sinergi+Informatika+Semen+Indonesia" target="_blank" style="text-decoration: none;">
              <img src="<?php echo $assets ?>images/google_play.png" alt="Google Play" height="48" width="48" id="btn-possmig">
            </a>
            <a href="https://www.instagram.com/lifeatsisi/" target="_blank" style="text-decoration: none;">
              <img src="<?php echo $assets ?>images/instagram.png" alt="Instagram" height="48" width="48">
            </a>
            <a href="https://www.youtube.com/channel/UChoRoF5e-XoxgdvGwo-b24A" target="_blank" style="text-decoration: none;">
              <img src="<?php echo $assets ?>images/youtube.png" alt="Youtube" height="48" width="48">
            </a>
          </p>
          <p>
            <a href="<?=$mobile_android_ps->uri ?? '#' ?>" target="_blank" rel="noopener"><img src="<?= base_url('assets/uploads/logos/google-play.png') ?>" alt="Google Play" width="40%" id="btn-posretail"></a>
            <a href="<?=$mobile_android_as->uri ?? '#' ?>" target="_blank" rel="noopener"><img src="<?= base_url('assets/uploads/logos/app-store.png') ?>" alt="AppStore" width="40%" id="btn-posretail-ios"></a>
          </p>
        </div>
        <div class="col-lg-6 col-md-12">
          <p>Anda juga dapat menghubungi kami dengan mengisi form di bawah ini</p>
          <div class="row">
            <div class="col-md-10">
              <div class="form-group">
                <input class="form-control" id="name" type="text" placeholder="Your Name *" required data-validation-required-message="Please enter your name.">
                <p class="help-block text-danger"></p>
              </div>
              <div class="form-group">
                <input class="form-control" id="email" type="email" placeholder="Your Email *" required data-validation-required-message="Please enter your email address.">
                <p class="help-block text-danger"></p>
              </div>
              <div class="form-group">
                <input class="form-control" id="phone" type="tel" placeholder="Your Phone *" required data-validation-required-message="Please enter your phone number.">
                <p class="help-block text-danger"></p>
              </div>
            </div>
            <div class="col-md-10">
              <div class="form-group">
                <textarea class="form-control" id="message" placeholder="Your Message *" required data-validation-required-message="Please enter a message."></textarea>
                <p class="help-block text-danger"></p>
              </div>
            </div>
            <div class="clearfix"></div>
            <div class="col-lg-12 text-center">
              <div id="success"></div>
              <button id="sendMessageButton" class="btn btn-primary btn-xl text-uppercase" type="submit">Send Message</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>



  <!-- Footer -->
  <footer>
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <span class="copyright"><a href="https://sisi.id/" target="_blank" style="font-weight: 200">© Powered by PT. Sinergi Informatika Semen Indonesia </a></span>
        </div>
      </div>
    </div>
  </footer>

  <!-- Bootstrap core JavaScript -->
  <script src="<?php echo $assets ?>js/jquery/jquery.min.js"></script>
  <script src="<?php echo $assets ?>js/bootstrap/js/bootstrap.bundle.min.js"></script>

  <!-- Plugin JavaScript -->
  <script src="<?php echo $assets ?>js/jquery-easing/jquery.easing.min.js"></script>

  <!-- Contact form JavaScript -->
  <script src="<?php echo $assets ?>js/agency/jqBootstrapValidation.js"></script>
  <!-- <script src="js/contact_me.js"></script>-->

  <!-- Custom scripts for this template -->
  <script src="<?php echo $assets ?>js/agency/agency.min.js"></script>


  <script src="<?php echo $assets ?>guide/js/hopscotch.js"></script>
  <script>
    // Define the tour!
    var tour = {
      id: "guide-home",
      onClose: function() {
        localStorage.setItem('tour-homepage', true);
        //                console.log('aku');
      },
      steps: [{
          title: "Selamat Datang!!",
          content: "di <?= $Settings->site_name ?>",
          target: "a.navbar-brand",
          placement: "bottom",
        },
        {
          title: "FAQ",
          content: "Klik untuk panduan penggunaan <?= $Settings->site_name ?>",
          target: "btn-userguide",
          placement: "left"
        },
        {
          title: "Unduh Mobile <?= $Settings->site_name ?>",
          content: "<?= $Settings->site_name ?> juga tersedia dalam aplikasi mobile",
          target: "btn-posretail",
          placement: "right"
        },
        {
          title: "Unduh Mobile iOS <?= $Settings->site_name ?>",
          content: "<?= $Settings->site_name ?> juga tersedia dalam aplikasi mobile iOS",
          target: "btn-posretail-ios",
          placement: "right"
        },
        {
          title: "Distributor Semen Indonesia?",
          content: "Khusus untuk anda silahkan unduh aplikasi mobile disini.",
          target: "btn-possmig",
          placement: "top",
        },
        {
          title: "Mulai!!",
          content: "Mari mulai",
          target: "btn-login",
          placement: "bottom",
          multipage: true,
          onNext: function() {
            window.location = "<?php echo site_url(); ?>login"
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

    if (!localStorage.getItem('tour-homepage')) {
      // Start the tour!
      hopscotch.startTour(tour);
    }
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