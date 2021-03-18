<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?><!DOCTYPE html>
<html>
<head>
    <meta name="google-signin-client_id" content="826096717681-rq05asf8l5ckshcolifq0kv76esuf7sk.apps.googleusercontent.com">
    <meta name="google-signin-cookiepolicy" content="single_host_origin">
    <meta name="google-signin-scope" content="profile email">
    
    <!-- [START google_config] -->
  <!-- **********************************************
       * TODO(DEVELOPER): Use your Client ID below. *
       ********************************************** -->
    <meta name="google-signin-client_id" content="826096717681-rq05asf8l5ckshcolifq0kv76esuf7sk.apps.googleusercontent.com">
    <meta name="google-signin-cookiepolicy" content="single_host_origin">
    <meta name="google-signin-scope" content="profile email">
    <!-- [END google_config] -->

    <script type="text/javascript">
        var base_url = '<?=base_url()?>';
    </script>

    <?php if (SOCKET_NOTIFICATION) { ?>
    <!-- SOCKET.IO -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.3.0/socket.io.js"></script>
    <script>
        var socket = io.connect('<?= getenv('SOCKET_URL')?>');
        socket.emit("sendClientInfo", {
            company_id : '<?= $this->session->userdata("company_id");?>',
            user_id : '<?= $this->session->userdata("user_id");?>',
            company : '<?= $this->session->userdata("company_name");?>',
            client_type : 'pos',
            code : '<?= $this->session->userdata("kode_distributor");?>',
            name : '<?= $this->session->userdata("company_name");?>',
            token : '<?=SOCKET_TOKEN?>'
        });

        socket.on('message', function (data) {
            console.log(data);
        });

        socket.on('error', function (data) {
            console.error(data);
        });
    </script>
    <!-- END SOCKET -->
    <?php } ?>

    <!-- Google Sign In -->
    <script src="https://apis.google.com/js/platform.js" async defer></script>

    

<!--     <script src="https://www.gstatic.com/firebasejs/4.6.1/firebase.js"></script>
    <script>
    // Initialize Firebase
    var config = {
        apiKey: "AIzaSyBR-VH8xb5pdgdDOsQQRHXjB9AP2vfUykM",
        authDomain: "forcaposretail.firebaseapp.com",
        databaseURL: "https://forcaposretail.firebaseio.com",
        projectId: "forcaposretail",
        storageBucket: "forcaposretail.appspot.com",
        messagingSenderId: "826096717681"
    };
    firebase.initializeApp(config);
    </script> -->

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
    <meta property="og:image" content="<?php echo $assets ?>images/Logo.png" />
    
    <!-- Open Graph / Facebook -->
    <meta property="og:url"                content="<?= current_url() ?>" />
    <meta property="og:type"               content="website" />
    <meta property="og:title"              content="Forca POS - Boosting Your Business Performance" />
    <meta property="og:description"        content="Boosting Your Business Performance - Forca Point Of Sales is an online inventory management application for cashier and manager store." />
    <meta property="og:image"              content="<?php echo $assets ?>images/Logo.png" />
    
    <base href="<?= site_url() ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - <?= $Settings->site_name ?></title>
    <link rel="shortcut icon" href="<?= $assets ?>images/icon.png"/>
    <link href="<?= $assets ?>styles/spinner/css/spinners.css" rel="stylesheet"/>
    <style>
        .wobblebar-loader:not(:required){
            background: #3276B1;
        }
    </style>
    <link href="<?= $assets ?>styles/theme.css?v=<?=FORCAPOS_VERSION?>" rel="stylesheet"/>
    <link href="<?= $assets ?>styles/style.css?v=<?=FORCAPOS_VERSION?>" rel="stylesheet"/>
    <link href="<?= $assets ?>guide/css/hopscotch.css" rel="stylesheet"/>
    <link href="<?= $assets ?>js/pace/loading-bar.css" rel="stylesheet"/>
    <link href="<?= $assets ?>tooltip/dist/jBox.all.min.css" rel="stylesheet">

<!--    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/datepicker/0.6.5/datepicker.css" rel="stylesheet"/>
    <script type="text/javascript" src="<?= $assets ?>guide/js/hopscotch.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/jquery-2.0.3.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/jquery-migrate-1.2.1.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/pace/pace.js"></script>
    <script src="<?= $assets ?>tooltip/dist/jBox.all.min.js"></script>
    
    
    <!-- <script type="text/javascript" src="<?=$assets?>js/map.js"></script> -->
    <!--[if lt IE 9]>
    <script src="<?= $assets ?>js/jquery.js"></script>
    <![endif]-->
<!--    <noscript><style type="text/css">#loading { display: none; }</style></noscript>-->
    <?php if ($Settings->user_rtl) { ?>
        <link href="<?= $assets ?>styles/helpers/bootstrap-rtl.min.css" rel="stylesheet"/>
        <link href="<?= $assets ?>styles/style-rtl.css?v=<?=FORCAPOS_VERSION?>" rel="stylesheet"/>
        <script type="text/javascript">
            $(document).ready(function () { $('.pull-right, .pull-left').addClass('flip'); });
        </script>
    <?php } ?>
    <script type="text/javascript">
        $(document).ajaxStart(function() { Pace.restart(); /* $('.wobblebar-loader').css('visibility', 'visible'); */});
        // $(window).ready(function () {
        //     var elem = document.getElementById("myBar");
        //     var width = 0;
        //     var id = setInterval(frame, 10);
        //     function frame() {
        //         if (width >= 80) {
        //             clearInterval(id);
        //         } else {
        //             width++;
        //             elem.style.width = width + '%';
        //         }
        //     }
        // });
        $(window).load(function () {
            var elem = document.getElementById("myBar");
            var width = 0;
            var id = setInterval(frame, 10);
            function frame() {
                if (width >= 100) {
                    clearInterval(id);
                    $("#loadings").fadeOut("slow");
                } else {
                    width++;
                    elem.style.width = width + '%';
                }
            }

        });
    </script>



    <style type="text/css">
        .wobblebar-loader{
            /* display: none!important; */
            position: absolute!important;
            left: 50%;
            margin-top: 10px;
            margin-left: -69px;
        }

        .dot {
            height: 10px;
            width: 10px;
            background-color: red;
            border-radius: 50%;
            display: inline-block;
            position: absolute;
          }

        #customersurvey {
            visibility: visible;
            min-height: 100px;
            max-width: 400px;
            text-align: justify;
            position: fixed;
            z-index: 10;
            right: 20px;
            bottom: 20px;
            FONT-SIZE: 12PX;
        }
    </style>
</head>

<body>
<noscript>
    <div class="global-site-notice noscript">
        <div class="notice-inner">
            <p><strong>JavaScript seems to be disabled in your browser.</strong><br>You must have JavaScript enabled in
                your browser to utilize the functionality of this website.</p>
        </div>
    </div>
</noscript>

<?php 
    if($activeSurvey){
        if(!$customerResponse){ ?>
            <div id="customersurvey">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel"><?= lang('customer_survey') ?></h4>
                    </div>
                    <div class="modal-body">
                        <p>
                            <?= lang('introduction'); ?><br>
                            <b><?= lang('info_survey'); ?></b>
                        </p>
                    </div>
                    <div class="modal-footer">
                        <a href="<?= site_url('welcome/feedback') ?>" target="_blank" class="btn btn-primary"><?= lang('start_survey') ?></a>
                    </div>
                </div>
            </div>
<?php   }
    } ?>

<?php if (SERVER_QA) { ?>
    <div id="snackbar">QP SERVER</div>
<?php } ?>
<div style="display:none;">
    <audio id="soundNotif">
       <source src="<?= $assets ?>/sounds/notif.mp3" type="audio/mpeg">
    </audio>
</div>
<div id="loadings" style="height: 100%;
    left: 0;
    position: fixed;
    top: 0;
    width: 100%;
    z-index: 999999; background: white">

    <div  class="dots-loader" style="
  margin-left: 50%;
  margin-top: 25%;
  "></div>
    <div class="w3-border" style="width: 30%;
    margin-top: 35px;
    margin-left: 35%;border: 1px solid #ccc!important;background: #cecece;">
        <div id="myBar" class="w3-grey" style="height:24px;width:20%;color: #000!important;
    background-color: #428bca!important;"></div>
    </div>
</div>
<div id="app_wrapper">
    <header id="header" class="navbar">
        <div class="container">
            <a class="navbar-brand" href="<?= site_url() ?>"><span class="logo"><?= $Settings->site_name ?></span></a>

            <div class="btn-group visible-xs pull-right btn-visible-sm">
                <button id="burger-menu-mobile" class="navbar-toggle btn" type="button" data-toggle="collapse" data-target="#sidebar_menu">
                    <span class="fa fa-bars"></span>
                </button>
                <a href="<?= site_url('users/profile/' . $this->session->userdata('user_id')); ?>" class="btn">
                    <span class="fa fa-user"></span>
                </a>
                <a href="<?= site_url('logout'); ?>" class="btn">
                    <span class="fa fa-sign-out"></span>
                </a>
            </div>
            <div class="header-nav">
                <ul class="nav navbar-nav pull-right" style="margin-right: 30px;">
                    <li class="dropdown">
                        <a class="btn account dropdown-toggle" data-toggle="dropdown" href="#">
                            <img alt="" src="<?= avatar_image($this->session->userdata('avatar'), $this->session->userdata('gender')) ?>" class="mini_avatar img-rounded">

                            <div class="user">
                                <!-- <?= lang('welcome') ?> -->
                                <span> <?= $this->session->userdata('username'); ?></span>
                            </div>
                        </a>
                        <ul class="dropdown-menu pull-right">
                            <?php if($Owner || $Admin || $LT){?>
                            <li>
                                <a href="<?= site_url('users/profile/' . $this->session->userdata('user_id')); ?>">
                                    <i class="fa fa-user"></i> <?= lang('profile'); ?>
                                </a>
                            </li>
                            <?php }?>
                            <li>
                                <a href="<?= site_url('users/profile/' . $this->session->userdata('user_id') . '/#cpassword'); ?>"><i class="fa fa-key"></i> <?= lang('change_password'); ?>
                                </a>
                            </li>
                            <li class="divider"></li>
                            <li>
                                <a style="display:none" id="open_modal_update_notif" href="<?= site_url('welcome/update_notif') ?>" data-toggle="modal" data-target="#myModal" data-backdrop="static"></a>
                                <a href="<?= site_url('welcome/log_update') ?>" data-toggle="modal" data-target="#myModal" data-backdrop="static">
                                    <i class="fa fa-info"></i> <?= lang('whats_new'); ?>
                                </a>
                            </li>
                            <li class="divider"></li>
                            <li>
                                <a href="<?= site_url('logout'); ?> " id="signout" name="signout">
                                    <i class="fa fa-sign-out"></i> <?= lang('logout'); ?>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
                <ul class="nav navbar-nav pull-right">
                    <li class="dropdown hidden-xs"><a class="btn tip" title="<?= lang('dashboard') ?>" data-placement="bottom" href="<?= site_url('welcome') ?>"><i class="fa fa-dashboard"></i></a></li>
                    <?php if(SOCKET_NOTIFICATION) { ?>
                    <li class="notif_socket">
                        <span class="label label-danger total_new_notification" style="position: absolute; margin-top: 2px; margin-left: 25px;z-index: 1;">0</span>
                        <a class="btn bblue tip" data-placement="bottom" id="notif" href="#">
                            <i id="loceng" class="fa fa-bell"></i>
                        </a>
                        <div id="dropdown-notif" class="dropdown-notif" style="display:none">
                            <div id="header_notif">
                                <div class="row">
                                    <div class="col-md-4">
                                        <span style="font-weight:900;"><?=lang('notification')?></span>
                                    </div>
                                    <div class="col-md-8">
                                        <div style="text-align:right;">
                                            <button onclick="set_read_all_notification()" id="readNotif" class="readNotif">
                                                <span style="color: #fff;"><?=lang('read_all')?></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="list_notifications" class="row">

                                <!-- <div class="col-md-12">
                                    <a href="#" class="data_notif">     
                                        <div id="bodyNotif" class="body_notif">
                                            <i class="fa fa-heart"></i>
                                            <span style="margin-left:5px;">Lorem Ipsum Dolor Ismet Lorem Ipsum Dolor</span>
                                            <div class="row" style=" margin-top: 10px;" >
                                                <div class="col-md-6">
                                                    <span style="color: #888888;">2 Desember 2019</span>
                                                </div>
                                                <div class="col-md-6" style="text-align:right;">
                                                    <button type="submit" id="readNotif" class="readNotif">
                                                        <span style="color: #428bca;">Read</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </a>      
                                </div>  -->
                                
                            </div>
                            <div id="tampil_lebih" style="cursor:pointer;">
                                <div id="footer_notif">                        
                                    <div style="text-align:center;">
                                        <span><?=lang('see_more')?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    <?php } ?>
                    <!-- <li class="dropdown hidden-xs"><a class="btn tip" title="<?= lang('Ecomerce') ?>" data-placement="bottom" href="<?= site_url('welcome/ecomerce') ?>" data-toggle="modal" data-target="#myModal"  data-backdrop="static"><i class="fa fa-shopping-cart"></i></a></li> -->
                    <?php if ($Owner) { ?>
                    <li class="dropdown hidden-sm">
                        <a class="btn tip" title="<?= lang('settings') ?>" data-placement="bottom" href="<?= site_url('system_settings') ?>">
                            <i class="fa fa-cogs"></i>
                        </a>
                    </li>
                    <?php } ?>
                    <li class="dropdown hidden-xs">
                        <a class="btn tip" title="<?= lang('calculator') ?>" data-placement="bottom" href="#" data-toggle="dropdown">
                            <i class="fa fa-calculator"></i>
                        </a>
                        <ul class="dropdown-menu pull-right calc">
                            <li class="dropdown-content">
                                <span id="inlineCalc"></span>
                            </li>
                        </ul>
                    </li>
                    <?php if ($info) { ?>
                        
                        <li class="dropdown hidden-sm" >
                            <a class="btn tip" title="<?= lang('notifications') ?>" data-placement="bottom" href="#" data-toggle="dropdown">
                                <i class="fa fa-info-circle"></i>
                                <span class="number blightOrange black"><?= sizeof($info) ?></span>
                            </a>
                            <ul class="dropdown-menu pull-right content-scroll">
                                <li class="dropdown-header"><i class="fa fa-info-circle"></i> <?= lang('notifications'); ?></li>
                                <li class="dropdown-content">
                                    <div class="scroll-div">
                                        <div class="top-menu-scroll">
                                            <ol class="oe">
                                                <?php foreach ($info as $n) {
                                                    echo '<li>' . $n->comment . '</li>';
                                                } ?>
                                            </ol>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    <?php } ?>
                    <?php if ($events) { ?>
                        <li class="dropdown hidden-xs">
                            <a class="btn tip" title="<?= lang('calendar') ?>" data-placement="bottom" href="#" data-toggle="dropdown">
                                <i class="fa fa-calendar"></i>
                                <span class="number blightOrange black"><?= sizeof($events) ?></span>
                            </a>
                            <ul class="dropdown-menu pull-right content-scroll">
                                <li class="dropdown-header">
                                <i class="fa fa-calendar"></i> <?= lang('upcoming_events'); ?>
                                </li>
                                <li class="dropdown-content">
                                    <div class="top-menu-scroll">
                                        <ol class="oe">
                                            <?php foreach ($events as $event) {
                                                echo '<li>' . date($dateFormats['php_ldate'], strtotime($event->start)) . ' <strong>' . $event->title . '</strong><br>'.$event->description.'</li>';
                                            } ?>
                                        </ol>
                                    </div>
                                </li>
                                <li class="dropdown-footer">
                                    <a href="<?= site_url('calendar') ?>" class="btn-block link">
                                        <i class="fa fa-calendar"></i> <?= lang('calendar') ?>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php } else { ?>
                    <li class="dropdown hidden-xs">
                        <a class="btn tip" title="<?= lang('calendar') ?>" data-placement="bottom" href="<?= site_url('calendar') ?>">
                            <i class="fa fa-calendar"></i>
                        </a>
                    </li>
                    <?php } ?>
                    <li class="dropdown hidden-sm">
                        <a class="btn tip" title="<?= lang('styles') ?>" data-placement="bottom" data-toggle="dropdown"
                           href="#">
                            <i class="fa fa-css3"></i>
                        </a>
                        <ul class="dropdown-menu pull-right">
                            <li class="bwhite noPadding">
                                <a href="#" id="fixed" class="">
                                    <i class="fa fa-angle-double-left"></i>
                                    <span id="fixedText">Fixed</span>
                                </a>
                                <a href="#" id="cssLight" class="grey">
                                    <i class="fa fa-stop"></i> Grey
                                </a> 
                                <a href="#" id="cssBlue" class="blue">
                                    <i class="fa fa-stop"></i> Blue
                                </a> 
                                <a href="#" id="cssBlack" class="black">
                                   <i class="fa fa-stop"></i> Black
                               </a>
                                <a href="#" id="cssHotPink" class="pink">
                                   <i class="fa fa-stop"></i> HotPink 
                               </a>
                           </li>
                        </ul>
                    </li>
                    <li class="dropdown hidden-xs">
                        <a class="btn tip" title="<?= lang('language') ?>" data-placement="bottom" data-toggle="dropdown"
                           href="#">
                            <img src="<?= base_url('assets/images/' . $Settings->user_language . '.png'); ?>" alt="">
                        </a>
                        <ul class="dropdown-menu pull-right">
                            <?php $scanned_lang_dir = array_map(function ($path) {
                                return basename($path);
                            }, glob(APPPATH . 'language/*', GLOB_ONLYDIR));
                            foreach ($scanned_lang_dir as $entry) { ?>
                                <li>
                                    <a href="<?= site_url('welcome/language/' . $entry); ?>">
                                        <img src="<?= base_url(); ?>assets/images/<?= $entry; ?>.png" class="language-img"> 
                                        &nbsp;&nbsp;<?= ucwords($entry); ?>
                                    </a>
                                </li>
                            <?php } ?>
                            <li class="divider"></li>
                            <li>
                                <a href="<?= site_url('welcome/toggle_rtl') ?>">
                                    <i class="fa fa-align-<?=$Settings->user_rtl ? 'right' : 'left';?>"></i>
                                    <?= lang('toggle_alignment') ?>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php if(!$Owner){ ?>
                    <li class="dropdown hidden-xs">
                        <a class="btn blightOrange tip" title="<?= lang('show_guide') ?>" data-placement="bottom" data-container="body" href="<?= site_url('welcome/reset_guide') ?>">
                                <i class="fa fa-info"></i>
                        </a>
                    </li>
                    <?php } if($Owner){?>
                        <li class="dropdown hidden-sm">
                            <a class="btn blightOrange tip" title="<?= lang('notif_payment_accounts') ?>" 
                                data-placement="bottom" data-container="body" href="<?= site_url('auth/notification_payment') ?>">
                                <i class="fa fa-bell"></i>
                            </a>
                        </li>
                        <?php if ($Settings->update) { ?>
                        <li class="dropdown hidden-sm">
                            <a class="btn blightOrange tip" title="<?= lang('update_available') ?>" 
                                data-placement="bottom" data-container="body" href="<?= site_url('system_settings/updates') ?>">
                                <i class="fa fa-download"></i>
                            </a>
                        </li>
                        <?php } ?>
                    <?php } ?>
                    <?php if (($Owner || $Admin || $GP['reports-quantity_alerts'] || $GP['reports-expiry_alerts']) && ($qty_alert_num > 0 || $exp_alert_num > 0)) { ?>
                        <li class="dropdown hidden-sm">
                            <a class="btn blightOrange tip" title="<?= lang('alerts') ?>" 
                                data-placement="left" data-toggle="dropdown" href="#">
                                <i class="fa fa-exclamation-triangle"></i>
                            </a>
                            <ul class="dropdown-menu pull-right">
                                <li>
                                    <a href="<?= site_url('reports/quantity_alerts') ?>" class="">
                                        <span class="label label-danger pull-right" style="margin-top:3px;"><?= $qty_alert_num; ?></span>
                                        <span style="padding-right: 35px;"><?= lang('quantity_alerts') ?></span>
                                    </a>
                                </li>
                                <?php if ($Settings->product_expiry) { ?>
                                <li>
                                    <a href="<?= site_url('reports/expiry_alerts') ?>" class="">
                                        <span class="label label-danger pull-right" style="margin-top:3px;"><?= $exp_alert_num; ?></span>
                                        <span style="padding-right: 35px;"><?= lang('expiry_alerts') ?></span>
                                    </a>
                                </li>
                                <?php } ?>
                            </ul>
                        </li>
                    <?php } ?>
                    <?php if (POS) { ?>
                    <li class="dropdown hidden-xs">
                        <a class="btn bdarkGreen tip" title="<?= lang('pos') ?>" data-placement="bottom" href="<?= site_url('pos') ?>">
                            <i class="fa fa-th-large"></i> <span class="padding05"><?= lang('pos') ?></span>
                        </a>
                    </li>
                    <?php } ?>
                    <?php if ($Owner) { ?>
                        <li class="dropdown">
                            <a class="btn bdarkGreen tip" id="today_profit" title="<span><?= lang('today_profit') ?></span>" 
                                data-placement="bottom" data-html="true" href="<?= site_url('reports/profit') ?>" 
                                data-toggle="modal" data-target="#myModal"  data-backdrop="static">
                                <i class="fa fa-hourglass-2"></i>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($Owner || $Admin) { ?>
                        <?php if (POS) { ?>
                        <li class="dropdown hidden-xs">
                            <a class="btn bblue tip" title="<?= lang('list_open_registers') ?>" data-placement="bottom" href="<?= site_url('pos/registers') ?>">
                                <i class="fa fa-list"></i>
                            </a>
                        </li>
                        <?php } ?>
                    <?php } ?>
                    <li class="dropdown hidden-xs">
                        <a class="btn bred tip" title="<?= lang('clear_ls') ?>" data-placement="bottom" id="clearLS" href="#">
                            <i class="fa fa-eraser"></i>
                        </a>
                    </li>
                    <?php if ($AdminBilling || $Admin) { ?>
                    <!-- <li class="dropdown hidden-xs">
                        <a class="btn bpurple tip" title="<?= lang('subscription') ?>" data-placement="bottom" href="<?= site_url('billing_portal/subscription') ?>" target="_blank">
                            <i class="fa fa-indent "></i> Subscribe
                            <?php if($Admin && ($getExpiredBill || $getPaymentReject)){ ?>
                                <div class=" spinner4 spinner-4"></div><span class="label label-warning pull-right " style="margin-top: 0px; margin-right: 0px; right: 0; position: absolute;">1</span>
                            <?php } ?>
                        </a>
                    </li> -->
                    <?php } ?>
                </ul>
                <!-- Search Menu -->
                <div class="nav navbar-nav pull-right">
                <!-- style="width:300px;" -->
                    <div class="autocomplete">
                        <input id="searchMenus" type="text" name="myCountry" placeholder="<?= lang('searching_menu'); ?>" autocomplete="off">
                        <span class="icon_search">
                            <i class="fa fa-search" aria-hidden="true"></i>
                        </span>
                    </div>
                    
                </div>
                <!-- End Search Menu -->
            </div>
        </div>
    </header>

    <div class="container" id="container">
        <div class="row" id="main-con">
        <div class="lt">
            <!-- <tr> -->
            <!-- TD 1 -->
            <div class="sidebar-con">
            <div id="sidebar-left" class="overflow-x hideMobile">
                <div class="sidebar-nav nav-collapse collapse navbar-collapse" id="sidebar_menu">
                    <ul class="nav main-menu">
                       <li class="mm_promotion">
                            <a href="<?= site_url('Promo'); ?>">
                                <i class="fa fa-star gold"></i>
                                <span class="text"> <?= lang('promotion_news'); ?></span>
                            </a>
                        </li>
                        <li class="mm_welcome">
                            <a href="<?= site_url() ?>">
                                <i class="fa fa-dashboard"></i>
                                <span class="text"> <?= lang('dashboard'); ?></span>
                            </a>
                        </li>
                        <?=$menu_sidebars?>

                    </ul>
                
                    <a href="#" id="main-menu-act" class="full visible-md visible-lg">
                        <i class="fa fa-angle-double-left"></i>
                    </a>
                </div>
                
            </div>
            </div>
            <!-- </td> -->
            
            <!-- TD 2 -->
            <div class="content-con">
            <div id="content">
                <div class="row">
                    <div class="col-sm-12 col-md-12">
                        <ul class="breadcrumb">
                            <?php
                            foreach ($bc as $b) {
                                if ($b['link'] === '#') {
                                    echo '<li class="active">' . $b['page'] . '</li>';
                                } else {
                                    echo '<li><a href="' . $b['link'] . '">' . $b['page'] . '</a></li>';
                                }
                            }
                            ?>
                            <li class="right_log hidden-xs">
                                <?= lang('your_ip') . ' <span id="my_ip_address">' . $ip_address . "</span> <span class='hidden-sm'>( " . lang('last_login_at') . ": " . date($dateFormats['php_ldate'], $this->session->userdata('old_last_login')) . " " . ($this->session->userdata('last_ip') != $ip_address ? lang('last_ip') . ': ' . $this->session->userdata('last_ip') : '') . " )</span>" ?>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($message) { ?>
                            <div class="alert alert-success">
                                <button data-dismiss="alert" class="close" type="button">&times;</button>
                                <?= $message; ?>
                            </div>
                        <?php } ?>
                        <?php if ($error) { ?>
                            <div class="alert alert-danger">
                                <button data-dismiss="alert" class="close" type="button">&times;</button>
                                <?= $error; ?>
                            </div>
                        <?php } ?>
                        <?php if ($warning) { ?>
                            <div class="alert alert-warning">
                                <button data-dismiss="alert" class="close" type="button">&times;</button>
                                <?= $warning; ?>
                            </div>
                        <?php } ?>
                        <?php
                        if ($info) {
                            foreach ($info as $n) {
                                if (!$this->session->userdata('hidden' . $n->id)) {
                                    ?>
                                    <div class="alert alert-info">
                                        <a href="#" id="<?= $n->id ?>" class="close hideComment external"
                                           data-dismiss="alert">&times;</a>
                                        <?= $n->comment; ?>
                                    </div>
                                <?php }
                            }
                        } ?>
                        <div class="alerts-con"></div>

<script type="text/javascript">
    $("#customers_index a, #suppliers_index a, #billers_index a").click( function() { 
        if($(this).data('toggle')){
            // localStorage.setItem('flag_addCustomers',true);
//            localStorage.setItem('flag_url',true);
        }
    });
</script>
<script type="text/javascript">
    document.getElementById('signout').addEventListener('click', handleSignOut, false);

    function handleSignOut() {
        var googleAuth = gapi.auth2.getAuthInstance();
        googleAuth.signOut().then(function() {
            firebase.auth().signOut();
        });
        googleAuth.disconnect();
        // firebase.auth().signOut().then(function() {
        //     console.log('berhasil');
        // }).catch(function(error) {
        //     console.error(error);
        // });
    }
    
    <?php if($this->session->userdata('last_update') < $last_version){ ?>
        $(window).on('load',function(){
            $('#open_modal_update_notif').trigger( "click" );
        });
    <?php } ?>

    $(document).ready(function() {
        //START - Get Public IP
        $.getJSON('https://api.ipify.org?format=json', function(data) {
            $("span#my_ip_address").html(data.ip);
        });
        //END - Get Public IP
    });
</script>