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
            color: white!important;
            opacity: 1 !important;
            top: 15px !important;
        }
        
        .close-modal-slider:hover,
        .close-modal-slider:focus {
            opacity: 1;
        }
        
        button.close-modal-slider {
            padding: 0;
            cursor: pointer;
            border: 0;
            -webkit-appearance: none;
            color: white!important;
        }
        
        .modal-dialog .close-modal-slider {
            position: absolute;
            right: -1rem;
            top: 1.8rem;
            -webkit-transition: all 0.2s ease-in-out;
            transition: all 0.2s ease-in-out;
        }
        
    </style>

    

</head>

<body>


  <!-- ALERT -->

  <?php if ($this->session->flashdata('warning')) { ?>
    <div id="alert" class="" style="width: 100%; position: fixed; z-index: 1029; margin-top: 80px">
      <div class="container" style="">
        <div class="alert alert-warning alert-dismissible show text-left mb-0" role="alert">
          <?= $this->session->flashdata('warning') ?>
          <button class="close" type="button" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
      </div>
    </div>
  <?php } ?>

  <?php if ($this->session->flashdata('message')) { ?>
    <div id="alert" class="" style="width: 100%; position: fixed; z-index: 1029; margin-top: 80px">
      <div class="container" style="">
        <div class="alert alert-info alert-dismissible show text-left mb-0" role="alert">
          <?= $this->session->flashdata('message') ?>
          <button class="close" type="button" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
      </div>
    </div>
  <?php } ?>

  <?php if ($this->session->flashdata('error')) { ?>
    <div id="alert" class="" style="width: 100%; position: fixed; z-index: 1029; margin-top: 80px">
      <div class="container" style="">
        <div class="alert alert-danger alert-dismissible show text-left mb-0" role="alert">
          <?= $this->session->flashdata('error') ?>
          <button class="close" type="button" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
      </div>
    </div>
  <?php } ?>

  <!-- END ALERT -->


  <nav class="navbar navbar-dashboard navbar-expand-lg fixed-top">
    <div class="container">
      <div class="d-flex justify-content-between w-100">

        <button class="navbar-toggler" type="button">
          <i class="fal fa-bars"></i>
        </button>

        <a class="navbar-brand center-sm-down-both" href="<?= base_url(aksestoko_route('aksestoko/home/main')) ?>">
          <img src="<?= $cms ? base_url('assets/uploads/cms/') . $cms->logo_2 : $assets_at . 'img/logo-at-putih.png' ?>" onerror="this.src='<?= $assets_at ?>img/logo-at-putih.png'" alt="Logo">
        </a>

        <ul class="navbar-nav d-flex justify-content-end align-items-center flex-row">
          <li class="nav-lang nav-lang-dark nav-item dropdown d-none d-lg-inline-block" id="navLang">
          </li>


          <?php if (!($m == "home" && $v == "select_supplier")) { ?>
            <li class="nav-item d-none d-lg-inline-block" id="navHome">
              <a class="nav-link" href="<?= base_url(aksestoko_route('aksestoko/home/main')) ?>">
                <i class="fal fa-home"></i>
              </a>
            </li>
            <?php if (SOCKET_NOTIFICATION) { ?>
              <!-- Start Notif -->
              <li class="nav-item d-lg-inline-block" id="navNotif">
                <span class="text-primary total_new_notification" id="notif">0</span>
                <a class="nav-link" href="#" id="notification" onclick="openNavNotif()">
                  <i id="locengNotif" class="fal fa-bell"></i>
                </a>
                <div id="dropdown-notif" class="dropdown-notif" style="display:none;">
                  <div id="header-notif">

                    <div class="row">
                      <div class="col-md-4">
                        <span class="header-notif">Notifikasi</span>
                      </div>
                      <div class="col-md-8">
                        <div style="text-align:right;">
                          <button onclick="set_read_all_notification()" id="readNotif" class="readNotif">
                            <span style="color: #b20838;">Baca Semua</span>
                          </button>
                        </div>
                      </div>
                    </div>

                  </div>
                  <div id="body-notif">
                    <div class="row">
                      <div id="list_notifications">

                        <!-- <div class="col-md-12 border-notif unread">
                  <a href="#">
                    <div id="bodyNotif" class="body_notif ">
                      <i class="fa fa-heart"></i>
                      <span style="margin-left:5px;">Lorem Ipsum Dolor Ismet Lorem Ipsum Dolor</span>
                      <div class="row" style=" margin-top: 10px;" >
                          <div class="col-md-6">
                              <span style="color: #888888;">2 Desember 2019</span>
                          </div>
                          <div class="col-md-6" style="text-align:right;">
                              <button type="submit" id="readNotif" class="readNotif">
                                  <span style="color: #b20838;">Baca</span>
                              </button>
                          </div>
                      </div>
                    </div>
                  </a>
                </div> -->

                      </div>
                    </div>
                  </div>
                  <a id="tampil_lebih" href="<?= base_url(aksestoko_route('aksestoko/home/allnotif')) ?>">
                    <div id="footer-notif">
                      Lihat Semua
                    </div>
                  </a>
                </div>
              </li>
              <!-- End Notif -->
            <?php } ?>
            <li class="nav-item" id="navShoppingCart">
              <a class="nav-link nav-cart" href="JavaScript:Void(0)" id="navShoppingCarttes" onclick="openNav()">
                <i id="ikonKeranjang" class="fal fa-shopping-cart"></i>
                <?php if ($cart && count($cart) > 0) { ?>
                  <span class="text-primary" id="jumlah-cart-on-header"><?= count($cart) ?></span>
                <?php } ?>
              </a>
            </li>
          <?php } ?>

          <li class="nav-item dropdown d-none d-lg-inline-block" id="navMenu">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="fal fa-bars"></i>
              <?php if ($this->session->userdata('group_customer') == 'lt') { ?>
                <?php if ($sales_booking_pending_total || $get_bad_qty_confirm_pending) { ?>
                  <span class="label label-warning" style="position: absolute; top:0;border-radius: 10px;"><?= ($sales_booking_pending_total + $get_bad_qty_confirm_pending) ?></span>
                <?php } ?>
              <?php } ?>
            </a>
            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
              <?php if (count($list_distributor) > 1) { ?>
                <li>
                  <a class="dropdown-item" href="<?= base_url(aksestoko_route('aksestoko/home/select_supplier')) ?>"><i class="fal fa-truck"></i> Pilih Distributor</a>
                </li>
              <?php } ?>
              <!-- <li>
              <div class="dropdown-item" href="javascript:void(0)" style="color: #B20838"><img src="<?= $assets_at ?>img/atp.png" alt="atp" width="24" height="24" style="margin-right:5px"> 0 Points</div>
              <hr style="margin: 3px 0">
            </li> -->
              <li>
                <a class="dropdown-item" href="<?= base_url(aksestoko_route('aksestoko/order')) ?>"><i class="fal fa-shopping-basket"></i> Pemesanan</a>
              </li>
              <!--
            <li>
               <a class="dropdown-item" href="<?= base_url(aksestoko_route('aksestoko/home/point')) ?>"><i class="fal fa-coins"></i> Poin Toko</a>
            </li>
            -->
              <!-- <li>
              <a class="dropdown-item" href="<?= base_url(aksestoko_route('aksestoko/home/reward')) ?>"><i class="fal fa-trophy"></i> Loyalty</a>
            </li> -->
              <li>
                <a class="dropdown-item" href="<?= base_url(aksestoko_route('aksestoko/auth/profile')) ?>"><i class="fal fa-user-alt"></i> Akun</a>
              </li>
              <li>
                <a class="dropdown-item" href="<?= base_url(aksestoko_route('aksestoko/home/programs')) ?>"><i class="fal fa-credit-card-front"></i> Program Kredit</a>
              </li>
              <li>
                <a class="dropdown-item" href="<?= base_url(aksestoko_route('aksestoko/home/cs')) ?>"><i class="fal fa-comments"></i> Layanan Pelanggan</a>
              </li>
              <li>
                <a class="dropdown-item" href="<?= base_url(aksestoko_route('aksestoko/home/faq')) ?>"><i class="fal fa-info-circle"></i> FAQ</a>
              </li>
              <?php if ($this->session->userdata('group_customer') == 'lt') { ?>
                <div class="divider"></div>
                <li>
                  <?php if ($get_bad_qty_confirm_pending) { ?>
                    <span class="label label-warning" style="position: absolute;right: 6px;bottom: 33px;border-radius: 10px;"><?= $get_bad_qty_confirm_pending ?></span>
                  <?php } ?>
                  <?php if ($sales_booking_pending_total) { ?>
                    <span class="label label-success" style="position: absolute;right: 33px;bottom: 33px;border-radius: 10px;"><?= $sales_booking_pending_total ?></span>
                  <?php } ?>
                  <a class="dropdown-item" href="#" data-toggle="modal" data-target="#myModaltoPOS"><i class="fal fa-paper-plane"></i> Menuju Forca POS</a>
                </li>
              <?php } ?>
            </ul>
          </li>
          <li class="nav-item d-none d-lg-inline-block">
            <a class="nav-link logout" href="javascript:;" url="<?= base_url(aksestoko_route('aksestoko/auth/logout')) ?>">Keluar</a>
          </li>

        </ul>

      </div>
    </div>

    <div class="navbar-slide">
      <div class="navbar-slide-close">
        <span class="icon-bar icon-bar-1"></span>
        <span class="icon-bar icon-bar-2"></span>
        <span class="icon-bar icon-bar-3"></span>
      </div>
      <div class="content">
        <ul class="nav-slide-list">
          <li class="nav-slide-item nav-slide-item-profile">
            <a class="nav-link" href="<?= base_url(aksestoko_route('aksestoko/auth/profile')) ?>">
              <div class="profile-img">
                <!-- <img src="<?= $assets_at ?>img/common/ic_profile.html" class="img-fluid" alt="Profile"> -->
              </div>
              <span><?= $this->session->userdata('username') ?></span>
            </a>
          </li>
          <li class="nav-slide-item" id="navHome-m">
            <a class="nav-link" href="<?= base_url(aksestoko_route('aksestoko/home/main')) ?>">
              <i class="fal fa-home"></i>
              <span>Beranda</span>
            </a>
          </li>

          <?php if (count($list_distributor) > 1) { ?>
            <li class="nav-slide-item" id="navOrders-m">
              <a class="nav-link" href="<?= base_url(aksestoko_route('aksestoko/home/select_supplier')) ?>">
                <i class="fal fa-truck"></i>
                <span>Pilih Distributor</span>
              </a>
            </li>
          <?php } ?>

          <li class="nav-slide-item" id="navOrders-m">
            <a class="nav-link" href="<?= base_url(aksestoko_route('aksestoko/order')) ?>">
              <i class="fal fa-shopping-basket"></i>
              <span>Pemesanan</span>
            </a>
          </li>

          <!-- <li class="nav-slide-item" id="navOrders-m">
          <a class="nav-link" href="<?= base_url(aksestoko_route('aksestoko/home/point')) ?>">
          <i class="fal fa-coins"></i>
            <span>Poin Toko</span>
          </a>
        </li> -->
          <!-- <li class="nav-slide-item" id="navLoyalty-m">
          <a class="nav-link" href="<?= base_url(aksestoko_route('aksestoko/home/reward')) ?>">
            <i class="fal fa-trophy"></i>
            <span>Loyalty</span>
          </a>
        </li> -->
          <li class="nav-slide-item" id="navAccount-m">
            <a class="nav-link" href="<?= base_url(aksestoko_route('aksestoko/auth/profile')) ?>">
              <i class="fal fa-user-alt"></i>
              <span>Akun</span>
            </a>
          </li>
          <li class="nav-slide-item" id="navAccount-m">
            <a class="nav-link" href="<?= base_url(aksestoko_route('aksestoko/home/programs')) ?>">
              <i class="fal fa-credit-card-front"></i>
              <span>Program Kredit</span>
            </a>
          </li>
          <li class="nav-slide-item" id="navCustomerService-m">
            <a class="nav-link" href="<?= base_url(aksestoko_route('aksestoko/home/cs')) ?>">
              <i class="fal fa-comments"></i>
              <span>Layanan Pelanggan</span>
            </a>
          </li>
          <li class="nav-slide-item" id="navFAQ-m">
            <a class="nav-link" href="<?= base_url(aksestoko_route('aksestoko/home/faq')) ?>">
              <i class="fal fa-info-circle"></i>
              <span>FAQ</span>
            </a>
          </li>
          <?php if ($this->session->userdata('group_customer') == 'lt') { ?>
            <li class="nav-slide-item" id="navPOS-m">
              <a class="nav-link" href="#" data-toggle="modal" data-target="#myModaltoPOS">
                <i class="fal fa-paper-plane"></i>
                <?php if ($get_bad_qty_confirm_pending) { ?>
                  <span class="label label-warning" style="position: absolute; margin-top: 2px; margin-left: 60%;"><?= $get_bad_qty_confirm_pending ?></span>
                <?php } ?>
                <?php if ($sales_booking_pending_total) { ?>
                  <span class="label label-success" style="position: absolute; margin-top: 2px; margin-left: 75%;"><?= $sales_booking_pending_total ?></span>
                <?php } ?>
                <span>Menuju Forca POS</span>
              </a>
            </li>
          <?php } ?>
          <li class="nav-slide-item" id="navLogout-m">
            <a class="nav-link logout" href="javascript:;" url="<?= base_url(aksestoko_route('aksestoko/auth/logout')) ?>">
              <i class="fal fa-sign-out-alt"></i>
              <span>Keluar</span>
            </a>
          </li>
        </ul>
      </div>
    </div>

    <!-- Cart Right Bar -->

    <div id="mySidenav" class="sidenav">
      <div class="header-cart" style="position: fixed;top: 0;width: 100%;">
        <p>Total : <?= count($cart) ?> Barang</p>
        <a style="top: -5px;padding: 0;" href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
      </div>
      <?php if (count($cart) <= 0) { ?>
        <div id="cartEmpty" style="min-height: 100%; padding-top: 25%;">
          <div class="content-empty text-center">
            <img src="<?= $assets_at ?>img/common/cart-empty.png" class="img-fluid" alt="cart empty">
            <p class="mb-0">Keranjang belanjaan kosong</p>
          </div>
        </div>
      <?php } else { ?>
        <div id="cartAvailable" style="min-height: 100%;padding-top: 15%;">
          <?php foreach ($cart as $c) { ?>
            <div class="content" style="padding: .5rem 0 .5rem .5rem;border-bottom: 1px solid #eee;">
              <div class="cart-information d-inline-block" style="width: 100%;">
                <div class="container">
                  <div class="row">
                    <div style="width: 35%">
                      <a style="max-width: 100px;padding: 0;" href="<?= base_url(aksestoko_route('aksestoko/product/view/')) . $c->id ?>">
                        <img class="img-fluid product-list-img px-2 py-2" src="<?= url_image_thumb($c->thumb_image) ?>" onerror="this.src='<?= base_url('assets/uploads/no_image.png') ?>'" alt="Product">
                      </a>
                    </div>

                    <div style="width: 55%">
                      <a href="<?= base_url(aksestoko_route('aksestoko/product/view/')) . $c->id ?>" style="padding: 0;">
                        <p style="color:#333; font-size: 17px;" class="label-detail card-title"><?= $c->name ?></p>
                      </a>

                      <div class="row">
                        <?php if ($c->price > 0) { ?>
                          <div style="width: 50%">
                            <span style="color:#333;" class="label">Harga</span>
                            <br>
                            <p style="color:#333;" class="label font-weight-bold total-price-<?= $c->id_cart ?>">Rp <?= penyebut($c->price * $c->cart_qty); ?></p>
                          </div>
                        <?php } ?>
                        <div style="width: 50%">
                          <span style="color:#333;" class="label">Jumlah</span>
                          <br>
                          <p style="color:#333; white-space: unset;" class="label font-weight-bold cart-header-<?= $c->id_cart ?>"><?= (int) $c->cart_qty . " " . convert_unit($my_controller->__unit($c->sale_unit)) ?></p>
                        </div>
                      </div>
                    </div>

                    <div style="width: 10%">
                      <i class="fal fa-trash-alt delete-cart text-primary" id="delete-carts" data-id="<?= $c->id_cart ?>" data-toggle="modal" data-target="#myModal"></i>
                    </div>


                  </div>
                </div>
                <!-- <i class="fas fa-trash-alt delete-cart"></i> -->
              </div>
            </div>
          <?php } ?>
          <div class="clearfix mt-3">

          </div>
        </div>
      <?php } ?>


      <div id="footer-cart" style="position: fixed;bottom: 0;width: 100%;">
        <a style="font-size: 12px;color: #fff;" href="<?= base_url(aksestoko_route('aksestoko/order/cart')) ?>" class="btn btn-primary btn-block btn-cart">Buka Keranjang Belanja</a>
      </div>
    </div>

    </div>
    <!-- END Cart Right Bar -->


    <!-- Star Nav Notif -->

    <div id="mySidenavNotif" class="sidenav">
      <div class="header-cart" style="position: fixed;top: 0;width: 100%; z-index:1;border-bottom: 1px solid #cacbcc;padding-bottom:10px;">
        <span>Notifikasi</span>
        <span>
          <button onclick="set_read_all_notification()" id="readNotif" class="readNotif">
            <span style="color: #b20838;">Baca Semua</span>
          </button>
        </span>
        <a style="top: -5px;padding: 0;" href="javascript:void(0)" class="closebtn" onclick="closeNavNotif()">&times;</a>
      </div>

      <!-- Kosong -->
      <!-- <div id="cartEmpty" style="min-height: 100%; padding-top: 25%;">
      <div class="content-empty text-center" >
        <img src="img/common/cart-empty.png" class="img-fluid" alt="cart empty">
        <p class="mb-0">Keranjang belanjaan kosong</p>
      </div>
    </div> -->

      <div id="notifAvailable" style="min-height: 100%;padding-top: 6%;">
        <div id="list_notificationsMobile">
          <!-- <div class="col-md-12 border-notif unread">
            <a href="#">
              <div id="bodyNotif" class="body_notif ">
                <i class="fa fa-heart"></i>
                <span style="margin-left:5px;">Lorem Ipsum Dolor Ismet Lorem Ipsum Dolor</span>
                <div class="row" style=" margin-top: 10px;" >
                    <div class="col-md-6">
                        <span style="color: #888888;">2 Desember 2019</span>
                    </div>
                    <div class="col-md-6" style="text-align:right;">
                        <button type="submit" id="readNotif" class="readNotif">
                            <span style="color: #b20838;">Baca</span>
                        </button>
                    </div>
                </div>
              </div>
            </a>
          </div> -->
        </div>
      </div>



      <div id="footer-cart" style="position: fixed;bottom: 0;width: 100%;">
        <a style="font-size: 12px;color: #fff;" href="<?= base_url(aksestoko_route('aksestoko/home/allnotif')) ?>" class="btn btn-primary btn-block btn-cart">Lihat Lebih</a>
      </div>
    </div>

    </div>

    <!-- End Nav notif -->


    <?php if (!($m == "home" && $v == "select_supplier")) { ?>
      <div class="cart-dropdown" id="cart-dropdown" style="display: none">
        <div class="header-cart">
          <p>Total : <?= count($cart) ?> Barang</p>
        </div>
        <!-- START: looping here -->
        <?php if (count($cart) <= 0) { ?>
          <div id="cartEmpty">
            <div class="content-empty text-center">
              <img src="<?= $assets_at ?>img/common/cart-empty.png" class="img-fluid" alt="cart empty">
              <p class="mb-0">Keranjang belanjaan kosong</p>
            </div>
          </div>
        <?php } else { ?>
          <div id="cartAvailable">
            <?php foreach ($cart as $c) { ?>
              <div class="content">
                <div class="cart-information d-inline-block" style="width: 100%;">
                  <div class="container">
                    <div class="row">
                      <div class="col-md-3 col-sm-3">
                        <a href="<?= base_url(aksestoko_route('aksestoko/product/view/')) . $c->id ?>">
                          <img class="img-fluid product-list-img px-2 py-2" src="<?= url_image_thumb($c->thumb_image) ?>" onerror="this.src='<?= base_url('assets/uploads/no_image.png') ?>'" alt="Product">
                        </a>
                      </div>


                      <div class="col-md-9 col-sm-9">

                        <a href="<?= base_url(aksestoko_route('aksestoko/product/view/')) . $c->id ?>">
                          <p class="label-detail card-title"><?= $c->name ?></p>
                        </a>
                        <i class="fal fa-trash-alt delete-cart text-primary" id="delete-carts" data-id="<?= $c->id_cart ?>" data-toggle="modal" data-target="#myModal"></i>

                        <div class="row">
                          <?php if ($c->price > 0) { ?>
                            <div class="col-md-6">
                              <span class="label">Harga</span>
                              <br>
                              <p class="label font-weight-bold total-price-<?= $c->id_cart ?>">Rp <?= penyebut($c->price * $c->cart_qty); ?></p>
                            </div>
                          <?php } ?>

                          <div class="col-md-2">
                            <span class="label">Jumlah</span>
                            <p class="label font-weight-bold cart-header-<?= $c->id_cart ?>"><?= (int) $c->cart_qty . " " . convert_unit($my_controller->__unit($c->sale_unit)) ?></p>
                          </div>
                          <!-- <div class="col-md-5">
                <span class="label">Total Harga</span>
                <p class="label">Rp <?= number_format($c->price * $c->cart_qty, 2, ',', '.'); ?></p>
              </div> -->
                        </div>

                      </div>
                    </div>
                  </div>
                  <!-- <i class="fas fa-trash-alt delete-cart"></i> -->
                </div>
              </div>
            <?php } ?>
            <div class="clearfix mt-3">

            </div>
          </div>
        <?php } ?>
        <!-- START: looping here -->

        <div class="footer-cart" id="">
          <div class="see-all"><a href="<?= base_url(aksestoko_route('aksestoko/order/cart')) ?>" class="btn btn-primary btn-block btn-cart font-button">Buka Keranjang Belanja</a></div>
        </div>
      </div>

    <?php } ?>

  </nav>