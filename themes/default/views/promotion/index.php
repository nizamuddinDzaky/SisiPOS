<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

?>
 <link rel="stylesheet" href="<?=$assets?>styles/helpers/promo.css?v=<?=FORCAPOS_VERSION?>" type="text/css"/>
    <div class="row promo-row">
        <?php foreach($promo as $read){ ?>      
        <div class="col-md-3 col-sm-6">
            <div class="promotion-box sticky-promo" data-promo-id="<?= $read->id?>" data-promo-name="<?= $read->code_promo?>"  data-promo-position="belanja-1">
            <div class="promotion-image">
              <a href="<?= site_url('Promo/detail'); ?>">
                <img src="<?= base_url()?>assets/uploads/<?= $read->url_image ? $read->url_image :'no_image.jpg' ?>" class="img-responsive img-full" alt="<?= $promo->name?>" scale="0">
              </a>
            </div>
            <div class="promotion-description">
              <p><?= $read->name?></p>
              <div class="promotion-date">
                <div class="promotion-date-detail">
                  <div class="promotion-box-label text-secondary">Periode Promo</div>
                  <div class="promotion-box__value">
                    <?php
                    $start_date = strtotime($read->start_date);
                    $end_date = strtotime($read->end_date);
                    echo date('d M Y',$start_date).' - '.date('d M Y',$end_date); ?>
                  </div>
                </div>
              </div>
              <div class="promotion-code">
              
                <div class="promotion-code-detail">
                  <div class="promotion-box-label text-secondary">
                    Kode Promo
                  </div>
                  <input class="sticky-code-voucher__text" value="<?= $read->code_promo ?>" readonly="readonly" type="text">
                </div>
                <a class="btn btn-ghost btn-small promotion-code__btn" href="#" data-code-category="belanja">Salin Kode</a>
                              
              </div>
            </div>
            <div class="promotion-cta">
              <a href="<?= site_url('Promo/detail/'.$read->id); ?>" class="promotion__btn">Lihat Detail</a>
            </div>
          </div>

        </div>
          <?php } ?>
    </div>
       