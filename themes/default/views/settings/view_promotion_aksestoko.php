<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header no-print" >
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?=$promotion->name?></h4>
        </div>
<!--         <div class="content">
            <div class="row" >
                <div>
                    <div class="col-md-12 ">
                    <div style="box-shadow: 0 0.25rem 1rem rgba(0, 0, 0, 0.15); margin: 2%;   padding-bottom: 2%; padding-top: 2%;">
                        <center> 
                        <img class="banner-promo" src="<?=base_url()?>assets/uploads/promotion/contoh.jpeg" alt="" class="img-fluid" alt="Responsive image" style="max-width: 400px;">
                        </center>
                    </div>
                    </div>
                </div>
                    
                    <div class="col-md-12">
                        <div class="" style="margin: 1%;">
                            <div style="box-shadow: 0 0.25rem 1rem rgba(0, 0, 0, 0.15); margin: 2%;   padding-bottom: 2%; padding-top: 2%;">
                            <center>
                                <label>Keterangan</label>
                                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                            </center>
                        </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="" style="margin: 1%;">
                        <div style="box-shadow: 0 0.25rem 1rem rgba(0, 0, 0, 0.15); margin: 2%;   padding-bottom: 2%; padding-top: 2%;">
                        <center>
                            <label>Tanggal</label>
                            <p><?=$promotion->start_date?><span style="font-weight: 700;"> s/d </span><?=$promotion->end_date?></p>
                        </center>
                        </div>
                    </div>
                    </div>

                    <div class="col-md-6" >
                        <div class="" style="margin: 1%;">
                        <div style="box-shadow: 0 0.25rem 1rem rgba(0, 0, 0, 0.15); margin: 2%;   padding-bottom: 2%; padding-top: 2%;">
                        <center>
                            <label>No Promo</label>
                            <p><?= $promotion->code_promo; ?></p>
                        </center>
                    </div>
                    </div>
                    </div>
                
            </div>
        </div> -->

        <div class="container">
            <div class="row">
                <div class="col-md-12 ">
                    <div style="margin: 2%;   padding-bottom: 2%; padding-top: 2%;">
                        <center> 
                        <img class="banner-promo" src="<?=base_url()?>assets/uploads<?=$promotion->url_image?>" alt="" class="img-fluid" alt="Responsive image" style="max-width: 400px;">
                        </center>
                    </div>
                </div>
            </div>
            <table class="table table-borderless">
                <thead>
                  <tr>
                    <th>Keterangan</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><center><?=$promotion->description?></center></td>
                  </tr>
                </tbody>
              </table>

               <table class="table table-borderless">
                <thead>
                  <tr>
                    <th><center>Tanggal</center></th>
                    <th><center>No Promo</center></th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><center><?=$promotion->start_date?> <b>s/d</b> <?=$promotion->end_date?></center></td>
                    <td><center><?= $promotion->code_promo; ?></center></td>
                  </tr>
                </tbody>
              </table>

        </div>

    </div>
</div>
