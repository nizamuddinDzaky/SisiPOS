<!DOCTYPE html>
<html>
<head>
	<title><?= $page_title ?> - <?= $Settings->site_name ?></title>
	<link rel="shortcut icon" href="<?= $assets ?>images/icon.png"/>
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<!-- All --> 
    <meta charset="utf-8">
    <meta name="author" content="PT. Sinergi Informatika Semen Indonesia">
    <meta name="description" content="<?= $page_title ?> - <?= $Settings->site_name ?>">
    <meta name="keywords" content="<?= htmlspecialchars($caption); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary" />
    <meta name="twitter:site" content="@ForcaPos" />
    <meta name="twitter:creator" content="@ForcaPos" />
    <meta property="og:url" content="<?= current_url() ?>" />
    <meta property="og:title" content="<?= $page_title ?> - <?= $Settings->site_name ?>" />
    <meta property="og:description" content="<?=  htmlspecialchars($caption); ?>" />
    <meta property="og:image" content="<?=$first_image ?>" />
    
    <!-- Open Graph / Facebook -->
    <meta property="og:url"                content="<?= current_url() ?>" />
    <meta property="og:type"               content="website" />
    <meta property="og:title"              content="<?= $page_title ?> - <?= $Settings->site_name ?>" />
    <meta property="og:description"        content="<?=  htmlspecialchars($caption); ?>" />
    <meta property="og:image"              content="<?=$first_image ?>" />
    
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
	<link rel="stylesheet" type="text/css" href="<?= $assets ?>help/css/style.css">
	<link rel="stylesheet" type="text/css" href="<?= $assets ?>help/css/jquery-ui.css">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
	
	<script src="<?= $assets ?>help/js/jquery-ui.js"></script>
	<script src="<?= $assets ?>help/js/jquery-3.3.1.min.js"></script>
</head>
<style>
    .nav_logo {
        text-indent: -9999px!important;
        display: inline-block;
        background: url(<?=$assets?>images/login_logo.png);
        width: 138px;
        height: 34px;
        background-size: cover;
		background-position: center;
    }

    .heading_help{
        width: 100%;
        height: 300px;
        padding-top: 64px;
        margin-bottom: 40px;
        background-image: url(<?=$assets?>help/banner-bakground.png);
        background-repeat: no-repeat;
        background-position: center;
        background-size: 100%;
        max-width: 1440px;
        margin: 0 auto 40px;
    }
</style>


<body>

	<header class="header">
		
        <div class="nav_brand">
            <a href="<?php echo site_url();?>" class="nav_logo">Hubungi Forca POS</a>
        </div>
        <ul class="nav_menu">   
            <li class="nav_item ">
                <div class="nav_item_wrapper ">
				<?php if(!$this->session->userdata('user_id')){ ?>
                    <a href="<?php echo site_url('login');?>" class="btn_login">
                        <span class="nav_menu-outline">Masuk</span>
                    </a>
                    <span class="nav_item_divider"></span>
                    <a href="<?php echo site_url('auth/sign_up');?>" class="btn_register">
                        <span class="nav_menu_outline">Daftar</span>
					</a>
				<?php } ?>
                </div>
            </li>
        </ul>
    
	</header>


	<section class="content_help">
		<div class="menu-header hide-mb">
		    
			<h1 class="menu-header-title">
			    <a href="<?php echo site_url('helps/');?>">Pusat Bantuan</a>
			</h1>
			<div class="menu-header-tab">
			    <!-- <div class="nhc-carousel__arrow nhc-carousel__prev" aria-disabled="false" id="left-arrow" style="display: none"></div> -->
			    <ul style="margin-bottom: 0rem;">
			        
			        <li class="menu-header-tab__item active" data-id="pg-11">Utama</li>
			        
			    </ul>
			    <!-- <div class="nhc-carousel__arrow nhc-carousel__next" aria-disabled="false" id="right-arrow"></div> -->
			</div>

		</div>

		<div class="article-container">
			<div class="article-sidebar hide-mb" style="top: 107px;height: 100vh;">
				
				<div class="article-back-link">
			        <a href="<?php echo site_url('helps/');?>"><img src="<?=$assets?>images/helps/back-arrow.png" style="filter: grayscale(100%) brightness(100);">
					Kembali ke Pusat Bantuan</a>
			    </div>

			    <div class="article-menus">
					<ul style="padding-left:20px;">
					<?= $menu_sidebars ?>
						

						
					</ul>
				</div>
			</div>
			<!-- End SideBar -->