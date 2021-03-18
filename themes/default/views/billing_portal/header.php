<!doctype html>
<html class="no-js" lang="">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title><?= $page_title ?> - <?= $Settings->site_name ?></title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- favicon
    ============================================ -->
    <link rel="shortcut icon" href="<?= $assets ?>images/icon.png"/>
    <link href="<?= $assets ?>styles/spinner/css/spinners.css" rel="stylesheet"/>
    <!-- Google Fonts
    ============================================ -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,700,900" rel="stylesheet">
    <!-- Bootstrap CSS
    ============================================ -->
    <link rel="stylesheet" href="<?=$assets_ab?>css/bootstrap.min.css">
    <!-- Bootstrap CSS
    ============================================ -->
    <link rel="stylesheet" href="<?=$assets_ab?>css/font-awesome.min.css">
    <!-- owl.carousel CSS
    ============================================ -->
    <link rel="stylesheet" href="<?=$assets_ab?>css/owl.carousel.css">
    <link rel="stylesheet" href="<?=$assets_ab?>css/owl.theme.css">
    <link rel="stylesheet" href="<?=$assets_ab?>css/owl.transitions.css">
    <!-- meanmenu CSS
    ============================================ -->
    <link rel="stylesheet" href="<?=$assets_ab?>css/meanmenu/meanmenu.min.css">
    <!-- animate CSS
    ============================================ -->
    <link rel="stylesheet" href="<?=$assets_ab?>css/animate.css">
    <!-- normalize CSS
    ============================================ -->
    <link rel="stylesheet" href="<?=$assets_ab?>css/normalize.css">
    <!-- mCustomScrollbar CSS
    ============================================ -->
    <link rel="stylesheet" href="<?=$assets_ab?>css/scrollbar/jquery.mCustomScrollbar.min.css">
    <!-- jvectormap CSS
    ============================================ -->
    <link rel="stylesheet" href="<?=$assets_ab?>css/jvectormap/jquery-jvectormap-2.0.3.css">
    <!-- notika icon CSS
    ============================================ -->
    <link rel="stylesheet" href="<?=$assets_ab?>css/notika-custom-icon.css">
    <!-- bootstrap select CSS
        ============================================ -->
    <link rel="stylesheet" href="<?=$assets_ab?>css/bootstrap-select/bootstrap-select.css">
    <!-- datapicker CSS
        ============================================ -->
    <link rel="stylesheet" href="<?=$assets_ab?>css/datapicker/datepicker3.css">
    <!-- main CSS
        ============================================ -->
    <link rel="stylesheet" href="<?=$assets_ab?>css/chosen/chosen.css">
    <!-- notification CSS
    ============================================ -->
    <link rel="stylesheet" href="<?=$assets_ab?>css/notification/notification.css">
    <!-- wave CSS
    ============================================ -->
    <link rel="stylesheet" href="<?=$assets_ab?>css/wave/waves.min.css">
    <link rel="stylesheet" href="<?=$assets_ab?>css/wave/button.css">
    <!-- Data Table JS
        ============================================ -->
    <link rel="stylesheet" href="<?=$assets_ab?>css/jquery.dataTables.min.css">
    <!-- main CSS
    ============================================ -->
    <link rel="stylesheet" href="<?=$assets_ab?>css/main.css">
    <!-- style CSS
    ============================================ -->
    <link rel="stylesheet" href="<?=$assets_ab?>style.css">
    <!-- responsive CSS
    ============================================ -->
    <link rel="stylesheet" href="<?=$assets_ab?>css/responsive.css">
    <!-- modernizr JS
    ============================================ -->
    <script src="<?=$assets_ab?>js/vendor/modernizr-2.8.3.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/jquery-2.0.3.min.js"></script>
    
</head>

<body>
    <!-- Start Header Top Area -->
    <div class="header-top-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                    <div class="logo-area">
                        <a href="#"><img src="<?=base_url('themes/default/assets/images/Logo.png')?>" alt="logo" style="height: 34px;" /></a>
                    </div>
                </div>
                <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                    <div class="header-top-menu">
                        <ul class="nav navbar-nav notika-top-nav">
                            <!-- <li class="nav-item dropdown">
                                <a href="#" data-toggle="dropdown" role="button" aria-expanded="false" class="nav-link dropdown-toggle"><span><i class="notika-icon notika-search"></i></span></a>
                                <div role="menu" class="dropdown-menu search-dd animated flipInX">
                                    <div class="search-input">
                                        <i class="notika-icon notika-left-arrow"></i>
                                        <input type="text" />
                                    </div>
                                </div>
                            </li> -->
                            <?php if (!$this->AdminBilling) { ?>
                            <li class="nav-item dropdown">
                                <a href="<?= site_url() ?>" title="Back To Forca POS" class="nav-link dropdown-toggle"><span><i class="fa fa-arrow-left"></i></span></a>
                            </li>
                            <?php } ?>

                            <?php if ($this->AdminBilling) { 
                                if($get_waiting){
                                    $num = $get_waiting->num_rows();
                            ?>
                            <li class="nav-item nc-al">
                                <a href="<?= site_url('billing_portal/subscription') ?>" role="button" class="nav-link dropdown-toggle"><span><i class="notika-icon notika-alarm"></i></span><div class="spinner4 spinner-4"></div><div class="ntd-ctn"><span><?= $num ?></span></div></a>
                            </li>
                            <?php }} ?>

                            <li class="nav-item"><a href="#" data-toggle="dropdown" role="button" aria-expanded="false" class="nav-link dropdown-toggle"><span><i class="notika-icon notika-support"></i></span></a>
                                <div role="menu" class="dropdown-menu message-dd chat-dd animated zoomIn">
                                    <div class="hd-mg-tt">
                                        <b><h2><?= $this->session->userdata('username') ?></h2></b>
                                    </div>
                                    <div class="hd-message-info">
                                        <a href="<?= site_url('logout'); ?>">
                                            <div class="hd-message-sn">
                                                <div class="hd-mg-ctn">
                                                    <h3><?= lang("logout") ?></h3>
                                                    <p><?= lang("drop_session") ?></p>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Header Top Area -->
    <!-- Mobile Menu start -->
    <div class="mobile-menu-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="mobile-menu">
                        <nav id="dropdown">
                            <ul class="mobile-menu-nav">
                                <?php if ($this->AdminBilling || $this->Owner) { ?>

                                <li><a data-toggle="collapse" data-target="#homeM" href="#"><?= lang("dashboard") ?></a>
                                    <ul id="homeM" class="collapse dropdown-header-top">
                                        <li><a href="<?= site_url('welcome') ?>"><?= lang("dashboard") ?></a></li>
                                    </ul>
                                </li>
                                <li><a data-toggle="collapse" data-target="#subscriptionM" href="#"><?= lang("subscription") ?></a>
                                    <ul id="subscriptionM" class="collapse dropdown-header-top">
                                        <li><a href="<?= site_url('billing_portal/subscription') ?>"><?= lang("subscription") ?></a></li>
                                        <li><a href="<?= site_url('billing_portal/subscription/set_billing') ?>"><?= lang("set_billing") ?></a>
                                        <!-- <li><a href="<?= site_url('billing_portal/subscription/set_billing_history') ?>"><?= lang("set_billing_history") ?></a>
                                        </li> -->
                                    </ul>
                                </li>
                                <li><a data-toggle="collapse" data-target="#planM" href="#"><?= lang("plan") ?></a>
                                    <ul id="planM" class="collapse dropdown-header-top">
                                        <li><a href="<?= site_url('billing_portal/plan') ?>"><?= lang("list_plan") ?></a></li>
                                        <li><a href="<?= site_url('billing_portal/plan/add') ?>"><?= lang("add_plan") ?></a></li>
                                    </ul>
                                </li>
                                <li><a data-toggle="collapse" data-target="#pluginM" href="#"><?= lang("addon") ?></a>
                                    <ul id="pluginM" class="collapse dropdown-header-top">
                                        <li><a href="<?= site_url('billing_portal/plugin') ?>"><?= lang("list_addon") ?></a></li>
                                        <li><a href="<?= site_url('billing_portal/plugin/add') ?>"><?= lang("add_addon") ?></a></li>
                                    </ul>
                                </li>
                                <li><a data-toggle="collapse" data-target="#authorizedM" href="#"><?= lang("user") ?></a>
                                    <ul id="authorizedM" class="collapse dropdown-header-top">
                                        <li><a href="<?= site_url('billing_portal/authorized') ?>"><?= lang("list_user") ?></a></li>
                                    </ul>
                                </li>
                                <li><a data-toggle="collapse" data-target="#settingM" href="#"><?= lang("setting") ?></a>
                                    <ul id="settingM" class="collapse dropdown-header-top">
                                        <li><a href="#"><?= lang("account") ?></a></li>
                                        <li><a href="#"><?= lang("change_password") ?></a></li>
                                        <li><a href="#"><?= lang("cost") ?></a></li>
                                        <li><a href="#"><?= lang("ovo_integration") ?></a></li>
                                    </ul>
                                </li>

                                <?php }else{ ?>
                                <li><a href="<?= site_url('billing_portal/subscription') ?>"><?= lang("subscription") ?></a>
                                    <ul id="subscriptionM" class="collapse dropdown-header-top">
                                        <li><a href="<?= site_url('billing_portal/subscription') ?>"><?= lang("subscription") ?></a></li>
                                    </ul>
                                </li>
                                <?php } ?>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Mobile Menu end -->
    <!-- Main Menu area start-->
    <div class="main-menu-area mg-tb-40">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <?php $uri = $this->uri->segment(2); ?>
                    <ul class="nav nav-tabs notika-menu-wrap menu-it-icon-pro">
                        <?php if ($this->AdminBilling || $this->Owner) { ?>

                        <li><a data-toggle="tab" href="#Home"><i class="fa fa-home"></i> <?= lang("dashboard") ?></a></li>

                        <li class="<?= $uri=='subscription'?'active':'' ?>">
                            <a data-toggle="tab" href="#subscription"><i class="fa fa-shopping-bag"></i> <?= lang("subscription") ?></a>
                        </li>

                        <li class="<?= $uri=='plan'?'active':'' ?>">
                            <a data-toggle="tab" href="#plan"><i class="fa fa-suitcase"></i> <?= lang("plan") ?></a>
                        </li>

                        <li class="<?= $uri=='plugin'?'active':'' ?>">
                            <a data-toggle="tab" href="#plugin"><i class="notika-icon notika-app"></i> <?= lang("addon") ?></a>
                        </li>

                        <li class="<?= $uri=='authorized'?'active':'' ?>">
                            <a href="<?= site_url('billing_portal/authorized') ?>"><i class="fa fa-user"></i> <?= lang("user") ?></a>
                        </li>

                        <li><a data-toggle="tab" href="#setting"><i class="notika-icon notika-settings"></i> <?= lang("setting") ?></a></li>

                        <?php }else{ ?>

                        <li class="<?= $uri=='subscription'?'active':'' ?>"><a href="<?= site_url('billing_portal/subscription') ?>"><i class="notika-icon notika-app"></i> <?= lang("subscription") ?></a></li>

                        <?php } ?>
                    </ul>
                    <div class="tab-content custom-menu-content">
                        <?php if ($this->AdminBilling || $this->Owner) { ?>

                        <div id="Home" class="tab-pane in notika-tab-menu-bg animated flipInX">
                            <ul class="notika-main-menu-dropdown">
                                <li><a href="<?= site_url('welcome') ?>"><?= lang("dashboard") ?></a></li>
                            </ul>
                        </div>

                        <div id="subscription" class="tab-pane notika-tab-menu-bg animated flipInX <?= $uri=='subscription'?'active':'' ?>">
                            <ul class="notika-main-menu-dropdown">
                                <li><a href="<?= site_url('billing_portal/subscription') ?>"><?= lang("subscription") ?></a></li>
                                <li><a href="<?= site_url('billing_portal/subscription/set_billing') ?>"><?= lang("set_billing") ?></a></li>
                                <!-- <li><a href="<?= site_url('billing_portal/subscription/set_billing_history') ?>"><?= lang("set_billing_history") ?></a></li> -->
                            </ul>
                        </div>

                        <div id="plan" class="tab-pane notika-tab-menu-bg animated flipInX <?= $uri=='plan'?'active':'' ?>">
                            <ul class="notika-main-menu-dropdown">
                                <li><a href="<?= site_url('billing_portal/plan') ?>"><?= lang("list_plan") ?></a></li>
                                <li><a href="<?= site_url('billing_portal/plan/add') ?>"><?= lang("add_plan") ?></a></li>
                            </ul>
                        </div>
                        
                        <div id="plugin" class="tab-pane notika-tab-menu-bg animated flipInX <?= $uri=='plugin'?'active':'' ?>">
                            <ul class="notika-main-menu-dropdown">
                                <li><a href="<?= site_url('billing_portal/plugin') ?>"><?= lang("list_addon") ?></a></li>
                                <li><a href="<?= site_url('billing_portal/plugin/add') ?>"><?= lang("add_addon") ?></a></li>
                            </ul>
                        </div>
                        
                        <div id="authorized" class="tab-pane notika-tab-menu-bg animated flipInX <?= $uri=='authorized'?'active':'' ?>">
                            <ul class="notika-main-menu-dropdown">
                                <li><a href="<?= site_url('billing_portal/authorized') ?>"><?= lang("list_user") ?></a></li>
                            </ul>
                        </div>
                        <div id="setting" class="tab-pane notika-tab-menu-bg animated flipInX">
                            <ul class="notika-main-menu-dropdown">
                                <li><a href="#"><?= lang("account") ?></a></li>
                                <li><a href="#"><?= lang("change_password") ?></a></li>
                                <li><a href="#"><?= lang("cost") ?></a></li>
                                <li><a href="#"><?= lang("ovo_integration") ?></a></li>
                            </ul>
                        </div>

                        <?php }else{ ?>

                        <div id="subscription" class="tab-pane notika-tab-menu-bg animated flipInX <?= $uri=='subscription'?'active':'' ?>">
                            <ul class="notika-main-menu-dropdown">
                                <li><a href="<?= site_url('billing_portal/subscription') ?>"><?= lang("subscription") ?></a></li>
                            </ul>
                        </div>

                        <?php } ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Main Menu area End-->
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <?php if ($error) { ?>
                <div class="alert alert-danger">
                    <button data-dismiss="alert" class="close" type="button">&times;</button>
                    <?= $error; ?>
                </div>
            <?php } ?>
            <?php if ($message) { ?>
                <div class="alert alert-success">
                    <button data-dismiss="alert" class="close" type="button">&times;</button>
                    <?= $message; ?>
                </div>
            <?php } ?>
            </div>
        </div>
    </div>