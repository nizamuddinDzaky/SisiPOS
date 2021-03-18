<?php

/* 
 * Copyright (c) 2017 adminSISI.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    adminSISI - initial API and implementation and/or initial documentation
 */
?>
 <link rel="stylesheet" href="<?=$assets?>styles/helpers/promo.css?v=<?=FORCAPOS_VERSION?>" type="text/css"/>
<!-- Promo Container -->
<main class="promo-container">  
  <div class="content" id="<?= $promo->link_promo?>">
    <div class="container">
        <!-- Main Content -->
        <div class="col-sm-6 main-column">
          <main class="main-content">
            <!-- Post Image -->
            <div class="post-image">
              <img src="<?= base_url()?>assets/uploads/<?= $promo->url_image ? $promo->url_image :'no_image.jpg'?>" class="img-full" alt="<?= $promo->name?>" scale="0">
            </div>
            <!-- ./Post Image -->
            
            
            <!-- Post Body -->
            <div class="post-body" id="post-body">
              <div class="row">
                <div class="col-sm-12">
                  <div class="post-content">
                    <h1 class="post-content__title"><?= $promo->name?></h1>
                  </div>
                  <div class="post-content">
                    <h2 class="post-content__heading text-primary">Deskripsi</h2>
                    <p><?= $promo->description?></p>
                  </div>
                  <!-- Post Box Mobile -->
                  <div class="postbox-mobile visible-xs">
                    <div class="postbox clearfix">
                      <div class="postbox-header hidden-xs">
                        <h3 class="postbox-header__h3">Info Promo</h3>
                      </div>
                      <div class="postbox-content">
                        <div class="postbox-content-detail postbox-content--period">
                          <img src="<?= base_url()?>assets/images/ic_periode.png" class="img-responsive postbox-content__img" alt="periode" scale="0">
                          <p class="postbox-content-title text-secondary">
                            Periode Promo                          </p>
                          <p class="postbox-content__p">
                            13 Des 2017 - 31 Mar 2018                          </p>
                        </div>
                                              <div class="postbox-content-detail postbox-content--min-transaction">
                          <img src="<?= base_url()?>assets/images/ic_minimum.png" class="img-responsive postbox-content__img" alt="minimum transaksi" scale="0">
                          <p class="postbox-content-title text-secondary">Minimum Transaksi</p>
                          <p class="postbox-content__p">Rp100.000</p>
                        </div>
                                            </div>

                      <div class="postbox-content">
                        
                                              <div class="postbox-content-detail">
                          <div class="postbox-content-title text-secondary">
                            <span class="promotion-code-detail">Kode Promo</span>
                            <div class="promotion-box-label-tooltip">
                              <span class="promotion-box-label-tooltip__icon-info"></span>
                              <span class="promotion-box-label-tooltip__text">Masukkan kode promo di halaman pembayaran</span>
                            </div>
                          </div>
                          <div class="postbox-content-voucher">
                            <input class="postbox-content-voucher__input" data-code-category="official-store" value="OSONGKIR" readonly="readonly" type="text">
                            <button class="btn btn-ghost postbox-content-voucher__btn">Salin Kode</button>
                          </div>
                        </div>
                        
                        
                      </div>
                    </div>
                    <!-- Mobile Toast -->
                    <div class="postbox-toast-mobile">
                      <p class="postbox-toast-mobile__p">Kode Tersalin</p>
                      <div class="postbox-toast-mobile-action">Tutup</div>
                    </div>
                    
                    <!-- Mobile CTA button-->
                                                                                </div>
                  <!-- ./Post Box Mobile -->
                  <div class="post-content post-content-main">
                    <h2 class="post-content__heading post-content__heading--mobile-block text-primary">Syarat dan Ketentuan</h2>
                        <?= str_replace(array("\r", "\n"), "", $this->sma->decode_html($promo->syarat));?>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-sm-12">
                  <div class="post-more">
                    <span class="post-more__text">Read More <span class="glyphicon glyphicon-menu-down" aria-hidden="true"></span></span>
                  </div>
                </div>
              </div>
            </div>
            <!-- ./Post Body -->
          </main>
        </div>
        <!-- ./Main Section -->
        
        <!-- Sidebar -->
        <div class="col-sm-4 hidden-xs">
          <div class="post-sidebar" style="top: 0px;">
            <!-- Promo Info -->
            <div class="postbox clearfix">
              <div class="postbox-header">
                <h3 class="postbox-header__h3">Info Promo</h3>
              </div>
              <div class="postbox-content">
                <div class="postbox-content-detail postbox-content--period">
                  <img src="<?= base_url()?>assets/images/ic_periode.png" class="img-responsive postbox-content__img" alt="periode" scale="0">
                  <p class="postbox-content-title text-secondary">
                    Periode Promo                  </p>
                  <p class="postbox-content__p">
                    <?= $start_date = strtotime($promo->start_date);
                    $end_date = strtotime($promo->end_date);
                    echo date('d M Y',$start_date).' - '.date('d M Y',$end_date);  ?>                 </p>
                </div>
                              <div class="postbox-content-detail postbox-content--min-transaction">
                  <img src="<?= base_url()?>assets/images/ic_minimum.png" class="img-responsive postbox-content__img" alt="minimum transaksi" scale="0">
                  <p class="postbox-content-title text-secondary">Minimum Transaksi</p>
                  <p class="postbox-content__p">Rp100.000</p>
                </div>
                            </div>

              <div class="postbox-content">
                
                              <div class="postbox-content-detail">
                  <div class="postbox-content-title text-secondary">
                    <span class="promotion-code-detail">Kode Promo</span>
                    <div class="promotion-box-label-tooltip">
                      <span class="promotion-box-label-tooltip__icon-info"></span>
                      <span class="promotion-box-label-tooltip__text">Masukkan kode promo di halaman Pembelian</span>
                    </div>
                  </div>
                  <div class="postbox-content-voucher">
                    <input class="postbox-content-voucher__input" data-code-category="official-store" value="<?= $promo->code_promo ?>" readonly="readonly" type="text">
                    <button class="btn btn-ghost postbox-content-voucher__btn">Salin Kode</button>
                  </div>
                </div>
                              
              </div>
            </div>
            <!-- Desktop Toast -->
            <div class="postbox-toast">
              <p class="postbox-toast__p">Kode Tersalin</p>
              <div class="postbox-toast-action">Tutup</div>
            </div>
            
            <!-- CTA Button -->
            <a href="<?= base_url().'/Purchases'?>" target="_self" class="promo-btn btn btn-green btn-medium btn-full hidden-xs">
                Beli Sekarang              </a>
                      </div>
        </div>
      <!-- ./Sidebar -->
    </div>
  </div>      
 </main>   