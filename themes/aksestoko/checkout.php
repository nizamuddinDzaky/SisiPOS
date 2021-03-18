<div class="container pt-4 pb-2">
  <h3 class="input-group">
    <a href="<?=base_url(aksestoko_route("aksestoko/order/cart"))?>" class="btn btn-back mr-3"><i class="fal fa-angle-left"></i></a> 
      Periksa
  </h3>
</div>

<form action="<?= base_url(aksestoko_route('aksestoko/order/save_checkout'))?>" method="POST" data-send="not-ready" id="orderForm">
  
<input type="hidden" id="company_id" name="company_id" value="<?=$company->id?>">
<input type="hidden" name="supplier_id" value="<?=$this->session->userdata('supplier_id')?>">
  <section class="section-checkout">
    <div class="container">
        
      <div class="row">
        <div class="col-lg-8">

          <div class="box p-box mb-3">
            <div class="subheading">
              <h3 class="box-subtitle">Alamat Pengiriman</h3>
              <!-- <p>Your orders will be delivered to the address below.</p> -->
            </div>
            <div class="address-box box" style="cursor: default !important;">
              <div class="heading">
                <i class="fal fa-home"></i>
                <h4> <?=$company->company?> </h4>
              </div>
              <p><?=$company->name?>, <?=$company->phone?> <br> <?=trim($company->address)?>, <?=ucwords(strtolower($company->village))?>, <?=ucwords(strtolower($company->state))?>, <?=ucwords(strtolower($company->city))?>, <?=ucwords(strtolower($company->country))?> - <?=$company->postal_code?></p>
            </div>
            <div class="text-right">
              <a href="#modalAddress" data-toggle="modal" class="btn btn-outline-primary btn-sm mt-2 font-button">Ganti Alamat</a>            
            </div>
          </div>

          <div class="box p-box mb-3">

            <div class="row">

              <div class="col-md-12">
                <div class="subheading" style="margin-bottom: 10px !important">
                  <h3 class="box-subtitle">Tanggal Ekspektasi Pengiriman</h3>
                  <small>Pemesanan di atas jam 12 siang dan tanggal ekspektasi pada hari itu juga, berpotensi dikirimkan di hari selanjutnya</small>
                </div>
                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1"><i class="fal fa-calendar text-primary"></i></span>
                  </div>
                  <input type="text" name="delivery_date" class="form-control form-control-datepicker" placeholder="(DD/MM/YYYY)" id="dateKirim" aria-describedby="basic-addon1" required="" autocomplete="off">
                </div>
              </div>

              <!-- <div class="col-md-6">
                <div class="subheading">
                  <h3 class="box-subtitle">Dikirim Oleh</h3>
                </div>
                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1"><i class="fal fa-shipping-fast text-primary"></i></span>
                  </div>
                  <select class="form-control" id="pilih_shipping" name="shipping_by">
                    <option value="Cipta Nugraha">Cipta Nugraha</option>
                    <option value="JNE">Lorem Ipsum</option>
                    <option value="TIKI">Dolor Ismet</option>
                  </select>
                </div>
              </div> -->

              <div class="col-md-12">
                <div class="subheading">
                  <h3 class="box-subtitle">Catatan</h3>
                </div>
                <div class="form-group">
                  <textarea class="form-control" placeholder="Catatan" name="note" rows="5" id="comment" style="height: 80px;"><?=$this->session->userdata('note')?></textarea>
                </div>
              </div>
              
            </div>
          </div>

          <div class="box p-box mb-3">
            <div class="subheading">
              <h3 class="box-subtitle">Produk (<?=count($cart)?>)</h3>
            </div>
            <?php
            $totalQty = 0;
            $totalAmount = 0;
            $totalPoint = 0;
            $arrJson = [];
            foreach ($cart as $item) {
                $arrJson[]=(array)$item;
                $totalQty += $item->cart_qty;
                $totalAmount += $item->price * $item->cart_qty;
                $totalPoint =+ 0; ?>
            
            <input type="hidden" name="product_id[]" value="<?=$item->id?>">
            <input type="hidden" name="quantity[]" value="<?=$item->cart_qty?>" id="qty-<?=$item->id?>">
            
            <div class="product-list box">
              <img class="img-fluid product-list-img px-2 py-2" src="<?= url_image_thumb($item->thumb_image) ?>" onerror="this.src='<?= base_url('assets/uploads/no_image.png')?>'" alt="Product">
              <div class="product-content">
                <h4 class="card-title mb-0">
                  <a href="<?=base_url(aksestoko_route('aksestoko/product/view/')) . $item->id?>">
                    <?=$item->name?>
                  </a>
                </h4>
                <small class="text-muted font-weight-light "> <?=$item->code?></small>
                <?php if($item->price > 0){ ?>
                  <h6 id="price-<?=$item->id?>" data-harga="<?= number_format($item->price, 0, ',', '.');?>" data-product_id = "<?=$item->id?>"> Rp <?= number_format($item->price, 0, ',', '.');?></h6>
                <?php } ?>
                <div class="row mt-3">
                  <div class="col-md-6 mb-sm-down-3">
                    <label class="d-none d-sm-block">Jumlah</label>
                    <p><?=(int)$item->cart_qty . " " . convert_unit($object->__unit($item->sale_unit))?></p>
                  </div>
                  <?php if($item->price > 0){ ?>
                  <div class="col-md-6 col-sm-4 price-hide-on-mobile">
                    <label class="d-none d-sm-block">Harga</label>
                    <p id="subtotal-<?=$item->id?>">Rp <?= number_format($item->price * $item->cart_qty, 0, ',', '.'); ?></p>
                  </div>
                  <?php } ?>
                </div>
              </div>
              <div class="price-show-mobile">
                <div class="row">
                  <div class="col-md-12">
                    <label class="d-none d-sm-block">Harga</label>
                    <p style="text-align: center; font-weight: 700;" id="total-price-mobile-<?=$item->id_cart?>">Rp <?= number_format($item->price * $item->cart_qty, 0, ',', '.');?></p>
                  </div>
                </div>
              </div>
            </div>

            <?php
            } 
            $json = json_encode($arrJson);
            // echo $json;die;
            ?>

          </div>
        </div>

        <div style="padding-bottom: 26%;" class="col-lg-4 custom-checkout">
          <div class="box p-box box-checkout-summary">
            <h3 class="box-subtitle mb-3">Distributor</h3>
            <!-- <div class="p-box box"> -->
              <div class="row">
              <div class="col-12">
                <div class="form-group">
                  <i class="fas fa-address-card "></i>
                  <label>Kode Distributor</label>
                  <p><?=$supplier->cf1 && is_numeric($supplier->cf1) ? str_pad($supplier->cf1, 10, '0', STR_PAD_LEFT) : $supplier->id?></p>
                </div>
              </div>
              <div class="col-12">
                <div class="form-group">
                  <i class="fas fa-warehouse "></i>
                  <label>Nama Distributor</label>
                  <p><?=$supplier->company?></p>
                </div>
              </div>
            </div>
            <!-- </div> -->
          </div>
          <div class="box p-box box-checkout-summary">
            <h3 class="box-subtitle mb-3">Pengiriman</h3>
            <!-- <div class="box p-box mb-3"> -->
            <div class="row">
              <div class="input-group mb-3">
                <div class="input-group-prepend">
                  <span class="input-group-text" id="basic-addon1"><i class="fal fa-money-check-alt text-primary"></i></span>
                </div>
                  <select id="delivery_method" class="form-control" name="delivery_method" required>
                    <option value="delivery">Pengiriman Distributor</option>
                    <option value="pickup">Pengambilan Sendiri</option>
                  </select>
              </div>
            </div>
            <!-- </div> -->
          </div>
          <div class="box p-box box-checkout-summary">
            <h3 class="box-subtitle mb-3">Ringkasan</h3>
            <!-- <div class="box p-box mb-3"> -->
            <div class="row">
              <div class="col-6 col-md-3 col-lg-6">
                <div class="form-group">
                  <label>Jumlah Barang</label>
                  <p><?=$totalQty?></p>
                </div>
              </div>
              <!-- <div class="col-6 col-md-3 col-lg-6">
                <div class="form-group">
                  <label>Total Poin</label>
                  <p><?=$totalPoint?> Pts</p>
                </div>
              </div> -->
            </div>
            <div class="row">
              <?php if($totalAmount > 0) { ?>
              <div class="col-6 col-md-6 col-lg-6">
                <div class="form-group">
                  <label>Total Harga</label>
                  <p class="h5">Rp <?= number_format($totalAmount, 0, ',', '.');?></p>
                </div>
              </div>
              <?php } ?>
              <div class="col-6 col-md-6 col-lg-6 hidden" id="div-potongan-shipment">
                <div class="form-group">
                  <label id="label-potongan-pengiriman"></label>
                  <p class="h5" id="total-potongan-shipment"></p>
                </div>
              </div>
              <div class="col-6 col-md-6 col-lg-6 hidden" id="div-total-akhir">
                <div class="form-group">
                  <label>Total Harga Akhir </label>
                  <p class="h5 text-primary" id="total-akhir"></p>
                </div>
              </div>
            </div>
            <!-- </div> -->
          </div>

          
          
          <?php if (count($promo_data) > 0) { ?>
          <div class="box p-box box-checkout-summary">
            <h3 class="box-subtitle">Diskon</h3>
            <!-- <div class="box p-box mb-3"> -->
               <div class="row">
                <div class="col-12 col-md-12 col-lg-12">
                  <div class="form-group">
                    <label>Kode Promo</label>
                    <p><?=$promo_data->code_promo?></p>
                  </div>
                </div>
                <div class="col-12 col-md-12 col-lg-12">
                  <div class="form-group">
                    <label>Potongan Harga</label>
                    <p class="text-success">Rp
                      <?php 
                        $disc = 0;
                      if ($promo_data->tipe == 0) { //jika persentase
                        $disc = ($promo_data->value * $totalAmount)/100 ;
                        if ($disc > $promo_data->max_total_disc) {
                          $disc = $promo_data->max_total_disc;
                        }
                      }else{
                        $disc = (float)$promo_data->value;
                      } 

                      echo number_format($disc, 0, ',', '.');
                      ?>
                    </p>
                  </div>
                </div>
                <div class="col-12 col-md-6 col-lg-12">
                  <div class="form-group">
                    <label>Total Pembayaran</label>
                    <p class="h5">Rp <?=number_format($totalAmount-$disc, 0, ',', '.')?></p>
                    <!-- <small class="text-muted"><sup>*</sup> Promo code applied</small> -->
                  </div>
                </div>
              <!-- </div> -->
            </div>
            </div>
            <?php } ?>
          
          <div class="box p-box box-checkout-action">
            <!-- <a href="#modalPayment" data-toggle="modal" class="btn btn-success btn-block">Continue to Payment</a> -->
            <button type="submit" class="btn btn-success btn-block font-button">Lanjutkan ke Pembayaran</button>
            <!-- <button type="button" href="#confirmOrder" data-toggle="modal" class="btn btn-success btn-block">Lanjutkan ke Pembayaran</button>           -->
          </div>

        </div>
      </div>
    </div>
  </section>
</form>

<!--Modal Address-->
<div class="modal fade" tabindex="-1" role="dialog" id="modalAddress">
  <div class="modal-dialog" role="document">
    <div class="modal-content">


      <div class="subheading sticky" id="HeaderEditAddress">
        <div>
          <a class="btn btn-back" data-dismiss="modal" id="editAddressMobileBack">
            <i class="fal fa-times"></i>
          </a>
          <h2 class="d-md-block mobile-title">Daftar Alamat Toko</h2>
             <p>Daftar alamat toko yang dimiliki atau menambah alamat baru</p>
        </div>
      </div>
      <div class="modal-body p-box custom-scroll" onscroll="customScroll()">
      
      <!-- <input type="text" id="addr-length" value="<?php echo sizeof($addresses); ?>"> -->

            <?php foreach ($addresses as $i => $address) { ?>

                    <div class="address-box box content-custom" style="cursor: default !important;">
                      
                      <div class="row">
                        <div class="col-md-9">
                          <div class="heading">
                            <i class="fal fa-home"></i>
                            <h4> <a href="<?=base_url(aksestoko_route('aksestoko/order/set_address/')).$address->id?>" class="text-body"><?=$address->company?></a> </h4>
                          </div> 
                        </div>
                        <!-- onclick="window.location.href = '<?=base_url(aksestoko_route('aksestoko/order/set_address/')).$address->id?>'" -->
                        <?php if ($i != 0) { ?> 
                        <div class="col-md-3">
                          <div class="text-right">
                            <a href="javascript:void(0)" class="delete-cart text-primary delete-alamat" data-id="<?=$address->id?>" data-name="<?=$address->company?>">
                            <i class="fal fa-trash-alt delete-cart text-primary delete-alamat" data-id="<?=$address->id?>" data-name="<?=$address->company?>"></i>
                            </a>
                          </div>
                        </div>
                        <?php } ?>
                      </div>

                      <p><a href="<?=base_url(aksestoko_route('aksestoko/order/set_address/')).$address->id?>" class="text-muted"><?=$address->name?>, <?=$address->phone?> <br> <?=trim($address->address)?>, <?=ucwords(strtolower($address->state))?>, <?=ucwords(strtolower($address->city))?>, <?=ucwords(strtolower($address->country))?> - <?=$address->postal_code?></a></p>
                 
                    <div class="text-right">
                      <a href="javascript:void(0)" class="font-button" onclick="$('#updateAddress<?=$address->id?>').toggle(1000)">Perbarui Alamat</a>                    
                    </div>

                    <div class="my-3" id="updateAddress<?=$address->id?>" style="display: none;">
                      <h5>Perbarui Alamat Toko</h5>
                      <!-- START: First Step -->
                      <form class="needs-validation mt-4" action="<?=base_url(aksestoko_route('aksestoko/auth/update_address/')) . $address->id . '?redirect=' . $this->uri->uri_string?>" method="POST">
                        <div class="row">
                          <div class="col-12 col-md-6" style="display: none;">
                            <div class="form-group">
                              <label for="" class="required">Nama Toko/Proyek</label>
                              <input type="text" class="form-control" id="" name="company" placeholder="Nama Toko/Proyek" value="<?=$address->company?>" readonly required="">
                            </div>
                          </div>
                          <div class="col-12 col-md-12">
                            <div class="form-group">
                              <label for="" class="required">Nama Penerima</label>
                              <input type="text" class="form-control" id="" name="name" placeholder="Nama Penerima" value="<?=$address->name?>" required="">
                            </div>
                          </div>
                          <div class="col-12 col-md-12">
                            <div class="form-group">
                              <label for="">Email</label>
                              <input type="email" class="form-control" id="" name="email" placeholder="Email" value="<?=$address->email?>" >
                            </div>
                          </div>
                          <div class="col-12 col-md-12">
                            <div class="form-group">
                              <label for="" class="required">No. Telepon</label>
                              <div class="input-group-prepend">

                                <span style="border-right: 0;" class="input-group-text"><img src="<?=$assets_at?>img/common/flag_indonesia.png" class="img-flag-phone">+62</span>
                                <input style="border-left: 0;border-top-left-radius: 0;border-bottom-left-radius: 0;" maxlength="16" type="text" class="form-control phoneFormat phoneAddress" placeholder="No Telepon" value="<?=$address->phone?>" required="">
                              </div>
                            </div>
                            <div class="validationPhone">
                                <span class="notifValidationPhoneAddress"></span>
                              </div>
                            <div style="display:none;">
                              <input type="text" class="form-control phoneFormatHidden" name="phone" placeholder="No Telepon" value="<?=$address->phone?>" required="">
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-12 col-md-12">
                            <div class="form-group">
                              <label for="" class="required">Alamat</label>
                              <input type="textarea" class="form-control" name="address" placeholder="Alamat" rows="1" value="<?=$address->address?>">
                            </div>
                          </div>
                        
                          <div class="col-6 col-md-6 col-sx-12">
                            <div class="form-group">
                              <label for="" class="required">Provinsi</label>
                              <select id="provinsiAreaUpdate<?=$address->id?>" name="provinsiAreaUpdate" data-id="<?=$address->id?>" class="form-control provUpdate" data-value="<?=$address->country?>" required>
                                
                              </select>
                              <input type="hidden" class="form-control" id="provinsi_update_<?=$address->id?>" name="country" value="<?=$address->country?>">
                            </div>
                          </div>
                          
                          <div class="col-6 col-md-6 col-sx-12">
                            <div class="form-group">
                              <label for="" class="required">Kabupaten</label>
                              <select id="kabupatenAreaUpdate<?=$address->id?>" name="kabupatenAreaUpdate" class="form-control kabUpdate" data-id="<?=$address->id?>" data-value="<?=$address->city?>" required></select>
                              <input type="hidden" class="form-control" id="city_update_<?=$address->id?>" name="city" value="<?=$address->city?>">
                            </div>
                          </div>

                          <div class="col-12 col-md-4 col-sx-12">
                            <div class="form-group">
                              <label for="" class="required">Kecamatan</label>
                              <select id="kecamatanAreaUpdate<?=$address->id?>" class="form-control kecUpdate" data-id="<?=$address->id?>" data-value="<?=$address->state?>" required>
                              </select>
                              <input type="hidden" class="form-control" id="state_update_<?=$address->id?>" name="state" value="<?=$address->state?>">
                            </div>
                          </div>

                          <div class="col-12 col-md-4 col-sx-12">
                            <div class="form-group">
                              <label for="" class="required">Desa</label>
                              <select id="desaAreaUpdate<?=$address->id?>" class="form-control desUpdate" data-id="<?=$address->id?>" data-value="<?=$address->village?>" required>
                              </select>
                              <input type="hidden" class="form-control" id="village_update_<?=$address->id?>" name="village" value="<?=$address->village?>">
                            </div>
                          </div>

                          <div class="col-12 col-md-4 col-sx-12">
                            <div class="form-group">
                              <label for="" class="required">Kode Pos</label>
                              <input type="text" class="form-control" name="postal_code" placeholder="Kode Pos" value="<?=$address->postal_code?>" required="">
                            </div>
                          </div>
                        </div>
                        <div class="clearfix mt-4">
                          <button class="btn btn-success float-right perbaruiAlamat" type="submit">Perbarui Alamat</button>
                        </div>
                      </form>
                      <!-- END: First Step -->
                    </div>
                  </div>
                  
                  <?php } ?>
                  
                  <?php if(count($addresses) <= 3){ ?>
                    <div class="text-right mt-4">

                      <a id="add-address" href="javascript:void(0)" class="btn btn-primary font-button" onclick="$('#newAddress').toggle(1000)" >
                        <i class="fas fa-angle-down font-button"></i> &nbsp; <span class="text-button font-button">Tambah Alamat</span>
                      </a>

                    </div>
                  <?php } ?>

                  <div class="mt-3" id="newAddress" style="display: none">
                    <h5>Tambahkan Alamat Baru</h5>
                    <!-- START: First Step -->
                    <form class="needs-validation mt-4" action="<?=base_url(aksestoko_route('aksestoko/auth/add_address')). "?redirect=" . $this->uri->uri_string?>" method="POST">
                      <div class="row">
                        <div class="col-12 col-md-6" style="display: none;">
                          <div class="form-group">
                            <label for="" class="required">Nama Toko/Proyek</label>
                            <input type="text" readonly class="form-control" id="" name="company" placeholder="Nama Toko/Proyek" value="<?=$this->session->userdata('company_name')?>" required="">
                          </div>
                        </div>
                        <div class="col-12 col-md-12">
                          <div class="form-group">
                            <label for="" class="required">Nama Penerima</label>
                            <input type="text" class="form-control" id="" name="name" placeholder="Nama Penerima" value="" required="">
                          </div>
                        </div>
                        <div class="col-12 col-md-12">
                          <div class="form-group">
                            <label for="">Email</label>
                            <input type="email" class="form-control" id="" name="email" placeholder="Email" value="" >
                          </div>
                        </div>
                        <div class="col-12 col-md-12">
                          <div class="form-group">
                            <label for="" class="required">No. Telepon</label>
                            <div class="input-group-prepend">

                              <span style="border-right: 0;" class="input-group-text"><img src="<?=$assets_at?>img/common/flag_indonesia.png" class="img-flag-phone">+62</span>
                              <input style="border-left: 0;border-top-left-radius: 0;border-bottom-left-radius: 0;" maxlength="16" type="text" class="form-control phoneFormat phoneAddress" placeholder="No Telepon" value="" required="">
                            </div>
                            <div class="validationPhone">
                                <span class="notifValidationPhoneAddress"></span>
                            </div>
                            <div style="display: none;">
                              <input type="text" class="form-control phoneFormatHidden" name="phone" placeholder="No Telepon" value="" required="">
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-12 col-md-12">
                          <div class="form-group">
                            <label for="" class="required">Alamat</label>
                            <input type="textarea" class="form-control" name="address" placeholder="Alamat" rows="1">
                          </div>
                        </div>
                      </div>
                      <div class="row">
                      <div class="col-6 col-md-6 col-xs-12">
                          <div class="form-group">
                            <label for="" class="required">Provinsi</label>
                            <select id="provinsiArea" class="form-control" required=""></select>
                            <input type="hidden" class="form-control" id="country_add" name="country" value="">
                          </div>
                        </div>
                        <div class="col-6 col-md-6 col-xs-12">
                          <div class="form-group">
                            <label for="" class="required">Kabupaten</label>
                            <select id="kabupatenArea" class="form-control" required="">
                              <option selected value="">Pilih Provinsi Terlebih Dahulu</option>
                            </select>
                            <input type="hidden" class="form-control" id="city_add" name="city" placeholder="Kota/Kabupaten" value="">
                          </div>
                        </div>

                        <div class="col-4 col-md-4 col-xs-12">
                          <div class="form-group">
                            <label for="" class="required">Kecamatan</label>
                            <select id="kecamatanArea" class="form-control" required="">
                               <option selected value="">Pilih Kabupaten Terlebih Dahulu</option>
                            </select>
                            <input type="hidden" class="form-control" id="state_add" name="state" placeholder="Kecamatan" value="">
                          </div>
                        </div>

                        <div class="col-4 col-md-4 col-xs-12">
                          <div class="form-group">
                            <label for="" class="required">Desa</label>
                            <select id="desaArea" class="form-control" required="">
                               <option selected value="">Pilih Kecamatan Terlebih Dahulu</option>
                            </select>
                            <input type="hidden" class="form-control" id="village_add" name="village" placeholder="Desa/Kelurahan" value="">
                          </div>
                        </div>

                        <div class="col-12 col-md-4">
                          <div class="form-group">
                            <label for="" class="required">Kode Pos</label>
                            <input type="text" class="form-control" id="" name="postal_code" placeholder="Kode Pos" value="" required="">
                          </div>
                        </div>
                      </div>
                      <div class="clearfix mt-4 mb-3">
                        <button class="btn btn-success float-right font-button perbaruiAlamat" type="submit">Simpan Alamat</button>
                      </div>
                    </form>
                    <!-- END: First Step -->
                  </div>

      </div>
    </div>
  </div>
</div>



      <!-- Modal Delete Address -->
    <div class="modal fade modal-delete" id="deleteAlamat" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title text-center mobile-title" id="myModalLabel">Hapus Alamat</h4>
          </div>
          <div class="modal-body">
            <div class="">
              <div class="row">
                <div class="col-12">
                  <div class="card-body text-center">
                    <p>Apakah Anda yakin menghapus alamat berikut?</p>
                    <h4 class="card-title mobile-caption" id="nameAddress">---</h4>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default font-button" data-dismiss="modal">Tidak</button>
            <a id="okAddress" class="btn btn-primary font-button" href="javascript:void(0)">Iya</a>
          </div>
        </div>
      </div>
    </div>



<!-- Modal Konfirmasi Order Cart -->
<div class="modal fade" id="confirmOrder" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title text-center mobile-title" id="myModalLabel">Konfirmasi Pesanan</h4>
      </div>
      <div class="modal-body">
        <div class="">
          <div class="row">
            <div class="col-12">
              <div class="card-body text-center">
                <p>Apakah Anda yakin pesanan telah sesuai?</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default font-button" data-dismiss="modal">Tidak</button>
        <a id="okOrder" class="btn btn-primary font-button" href="javascript:void(0)" onclick="$('#submitOrder').click()">Iya</a>
      </div>
    </div>
  </div>
</div>

<!-- END MODAL -->

<script type="text/javascript">

  $(document).ready(function(){
      $(".phoneFormat").keyup(function(e){
        // $(this).val(format($(this).val()));
          
          let a = $(this).val();
          if( a.charAt(0) == '0' ){
            a=a.substr(1); 
          }else if (a.charAt(0) == '6' && a.charAt(1) == '2'){
            a=a.substr(2);
          }else if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
             e.preventDefault();
              return false;
          }

       $(this).val(format(a));

      }).keyup();



      $('.phoneAddress').keyup(function(e) {
        let index = $('.phoneAddress').index(this);
        if (validatePhone('phoneAddress', index)) {
          $('.perbaruiAlamat').eq(index).removeAttr('disabled');
            $('.notifValidationPhoneAddress').eq(index).html('');
        }
        else {
            $('.notifValidationPhoneAddress').eq(index).html('Masukkan no telepon yang valid');
            $('.notifValidationPhoneAddress').eq(index).css('color', 'red');
            $('.perbaruiAlamat').eq(index).attr('disabled', 'disabled');
        }
      });


      $(".phoneFormatHidden").keyup(function(event){
        // $(this).val(format($(this).val()));  
        let a =  $(this).val();
        if( a.charAt(0) == '0' ){
          a=a.substr(1);
        }else if (a.charAt(0) == '6' && a.charAt(1) == '2'){
          a=a.substr(2);
        } 
        $(this).val(a).replace(/-/g , '');

      }).keyup();


      $(".phoneFormat").keydown(function(event){
        return limitCharacter(event);

      });
    });



    function validatePhone(phoneUpdateProfile, idx = -1) {
      if (idx != -1) {
        var a = document.getElementsByClassName(phoneUpdateProfile)[idx].value;
      } else {
        var a = document.getElementById(phoneUpdateProfile).value;
      }
      // var filter = /^((\+[1-9]{1,4}[ \-]*)|(\([0-9]{2,3}\)[ \-]*)|([0-9]{2,4})[ \-]*)*?[0-9]{3,4}?[ \-]*[0-9]{3,4}?$/;
      var filter = /^(([1-9]{1,4}[ \-]*)|(\([0-9]{2,3}\)[ \-]*)|([0-9]{2,4})[ \-]*)*?[0-9]{3,4}?[ \-]*[0-9]{3,4}?$/;
      if (filter.test(a)) {
          return true;
      }else {
        return false;
      }
    }



  // function limitCharacter(event)
  // {
  //   key = event.which || event.keyCode;
  //   if ( key != 188 // Comma
  //      && key != 8 // Backspace
  //      && key != 17 && key != 86 & key != 67 // Ctrl c, ctrl v
  //      && key != 13 && key != 37 && key != 39
  //      && (key < 48 || key > 57) // Non digit
  //      // Dan masih banyak lagi seperti tombol del, panah kiri dan kanan, tombol tab, dll
  //     ) 
  //   {
  //     event.preventDefault();
  //     return false;
  //   }
  // }


    var format = function(num){
      var str = num.toString().replace("", ""), parts = false, output = [], i = 1, formatted = null;
      if(str.indexOf(".") > 0) {
        parts = str.split(".");
        str = parts[0];
      }
      str = str.split("").reverse();
      for(var j = 0, len = str.length; j < len; j++) {
        if(str[j] != "-") {
          output.push(str[j]);
          if(i%3 == 0 && j < (len - 1)) {
            output.push("-");
          }
          i++;
        }
      }
      formatted = output.reverse().join("");
      return("" + formatted + ((parts) ? "." + parts[1].substr(0, 2) : ""));
    };


    $(".phoneFormat").bind("keyup tap change paste", function() {
      var phoneFormatHidden = $('.phoneFormatHidden');
      var phoneFormat = $('.phoneFormat');
      for (var i = 0; i < phoneFormatHidden.length; i++) {
        if (this == phoneFormat[i]) {
          let a =  phoneFormatHidden[i].value;
          if( a.charAt(0) == '0' ){
            a=a.substr(1);
          }else if (a.charAt(0) == '6' && a.charAt(1) == '2'){
            a=a.substr(2);
          }
          phoneFormatHidden[i].value = a;
          phoneFormatHidden[i].value = ("62" + $(this).val());
          phoneFormatHidden[i].value = (phoneFormatHidden[i].value.replace(/-/g , ''));
          break;
        }
      }
    }).change();


    var token;
    function getToken()
    {
        $.ajax({
            type:'GET',
            url:"https://x.rajaapi.com/poe",
            success: function(data)
            { 
              
              token=data.token;
              loadProvinsi();
              loadProvinsiUpdate();
            }

        }); 
    }

    function loadProvinsi()
    {

      $.ajax({
        type:'GET',
        url:`https://x.rajaapi.com/MeP7c5ne${token}/m/wilayah/provinsi`,
        success: function(data)
        { 
          if (data.success){
            let provinsi = '<option selected value="">Pilih Provinsi</option>';
          
            for(let prov of data.data){
              provinsi += `<option value="${prov.id}">${prov.name}</option>`;
            } 
           $("#provinsiArea").html(provinsi);
          }        
        }  
      });  

    }
    function loadKabupaten()
    {
        var provinsi = $("#provinsiArea").val();
        $.ajax({
            type:'GET',
            url:`https://x.rajaapi.com/MeP7c5ne${token}/m/wilayah/kabupaten?idpropinsi=${provinsi}`,
            success: function(data)
            { 
              if (data.success){
                let kabupaten = '<option selected value="">Pilih Kabupaten</option>';
                for(let kab of data.data){
                  kabupaten += `<option value="${kab.id}">${kab.name}</option>`;
                }  
                $("#kabupatenArea").html(kabupaten);
              }
            }
        }); 
    }

    function loadKecamatan()
    {
        var kabupaten = $("#kabupatenArea").val();
        $.ajax({
            type:'GET',
            url:`https://x.rajaapi.com/MeP7c5ne${token}/m/wilayah/kecamatan?idkabupaten=${kabupaten}`,
            success: function(data)
            { 
              if (data.success){
                let kecamatan = '<option selected value="">Pilih Kecamatan</option>';
                for(let kec of data.data){
                  kecamatan += `<option value="${kec.id}">${kec.name}</option>`;
                }  
                $("#kecamatanArea").html(kecamatan);
            
              }
            }
        }); 
    }

    function loadDesa()
    {
        var kecamatan = $("#kecamatanArea").val();
        $.ajax({
            type:'GET',
            url:`https://x.rajaapi.com/MeP7c5ne${token}/m/wilayah/kelurahan?idkecamatan=${kecamatan}`,
            success: function(data)
            { 
              if (data.success){
                let desa = '<option selected value="">Pilih Desa</option>';
                for(let des of data.data){
                  desa += `<option value="${des.id}">${des.name}</option>`;
                }  
                $("#desaArea").html(desa);
              }
            }
        }); 
    }

// Update
  function loadProvinsiUpdate()
    {
      let $provUpdate = $('.provUpdate');
      $.ajax({
        type:'GET',
        url:`https://x.rajaapi.com/MeP7c5ne${token}/m/wilayah/provinsi`,
        success: function(data)
        { 
          for (const $prov of $provUpdate) {
            let provinsi = '<option value="">Pilih Provinsi</option>';
            let value = $($prov).data('value');
            for(let pr of data.data){
              if(pr.name === value){
                provinsi += `<option selected value="${pr.id}">${pr.name}</option>`;
              } else {
                provinsi += `<option value="${pr.id}">${pr.name}</option>`;
              }  
            }
            $($prov).html(provinsi);
            loadKabupatenUpdate($($prov).data('id'));
          }
        }  
      });
    }

  function loadKabupatenUpdate(id)
    {
      let idProv = $(`#provinsiAreaUpdate${id}`).val();
      let $element = $(`#kabupatenAreaUpdate${id}`);
      $.ajax({
          type:'GET',
          url:`https://x.rajaapi.com/MeP7c5ne${token}/m/wilayah/kabupaten?idpropinsi=${idProv}`,
          success: function(data)
          { 
            if (data.success){
              let value = $($element).data('value');
              let kabupaten = '<option value="">Pilih Kabupaten</option>';
              for(let kab of data.data){
                if(kab.name === value){
                  kabupaten += `<option selected value="${kab.id}">${kab.name}</option>`;
                } else {
                  kabupaten += `<option value="${kab.id}">${kab.name}</option>`;
                }  
              }
              $($element).html(kabupaten);
              loadKecamatanUpdate($($element).data('id'));
            }
          }
      }); 
    }

  function loadKecamatanUpdate(id)
    {
      let idKab = $(`#kabupatenAreaUpdate${id}`).val();
      let $element = $(`#kecamatanAreaUpdate${id}`);
      $.ajax({
          type:'GET',
          url:`https://x.rajaapi.com/MeP7c5ne${token}/m/wilayah/kecamatan?idkabupaten=${idKab}`,
          success: function(data)
          { 
            if (data.success){
              let value = $($element).data('value');
              let kecamatan = '<option value="">Pilih Kecamatan</option>';
              for(let kec of data.data){
                if(kec.name == value){
                  kecamatan += `<option selected value="${kec.id}">${kec.name}</option>`;
                }else {
                  kecamatan += `<option value="${kec.id}">${kec.name}</option>`
                }
            }
           $($element).html(kecamatan);
           loadDesaUpdate($($element).data('id')); 
          }
        } 
    }); 
  }


  function loadDesaUpdate(id)
    {
      let idKec = $(`#kecamatanAreaUpdate${id}`).val();
      
      let $element = $(`#desaAreaUpdate${id}`);
      $.ajax({
          type:'GET',
          url:`https://x.rajaapi.com/MeP7c5ne${token}/m/wilayah/kelurahan?idkecamatan=${idKec}`,
          success: function(data)
          { 
            if (data.success){
              let value = $($element).data('value');
              let desa = '<option value="">Pilih Desa</option>';
              for (let des of data.data){
                if(des.name == value){
                  desa += `<option selected value="${des.id}">${des.name}</option>`;
                }else {
                  desa += `<option value="${des.id}">${des.name}</option>`;
                }
              }
              $($element).html(desa);
              
            }
          }
      }); 
    }

</script>

<script>

  $(document).ready(function() {
      getToken();
      $("#provinsiArea").change(function(){
        $("#country_add").val($(this).children("option:selected").html())
        $("#kabupatenArea").html(`<option selected value="">Pilih Provinsi Terlebih Dahulu</option>`);
        $("#kecamatanArea").html(`<option selected value="">Pilih Kabupaten Terlebih Dahulu</option>`);
        $("#desaArea").html(`<option selected value="">Pilih Kecamatan Terlebih Dahulu</option>`);
        loadKabupaten();
      });
      $("#kabupatenArea").change(function(){
        $("#city_add").val($(this).children("option:selected").html())
        $("#kecamatanArea").html(`<option selected value="">Pilih Kabupaten Terlebih Dahulu</option>`);
        $("#desaArea").html(`<option selected value="">Pilih Kecamatan Terlebih Dahulu</option>`);
        loadKecamatan();
      });
      $("#kecamatanArea").change(function(){
        $("#state_add").val($(this).children("option:selected").html()) 
        $("#desaArea").html(`<option selected value="">Pilih Kecamatan Terlebih Dahulu</option>`);
        loadDesa();
      });
      $("#desaArea").change(function(){
        $("#village_add").val($(this).children("option:selected").html())
      });


      // Update
      $(".provUpdate").change(function(){
        let id = $(this).data('id');
        $(`#provinsi_update_${id}`).val($(this).children("option:selected").html())
        $(`#kabupatenAreaUpdate${id}`).html(`<option selected value="">Pilih Provinsi Terlebih Dahulu</option>`);
        $(`#kecamatanAreaUpdate${id}`).html(`<option selected value="">Pilih Kabupaten Terlebih Dahulu</option>`);
        $(`#desaAreaUpdate${id}`).html(`<option selected value="">Pilih Kecamatan Terlebih Dahulu</option>`);
        loadKabupatenUpdate(id);
      })

      $(".kabUpdate").change(function(){
        let id = $(this).data('id');
        $(`#city_update_${id}`).val($(this).children("option:selected").html())
        $(`#kecamatanAreaUpdate${id}`).html(`<option selected value="">Pilih Kabupaten Terlebih Dahulu</option>`);
        $(`#desaAreaUpdate${id}`).html(`<option selected value="">Pilih Kecamatan Terlebih Dahulu</option>`);
        loadKecamatanUpdate(id);
      });

      $(".kecUpdate").change(function(){
        let id = $(this).data('id');
        $(`#state_update_${id}`).val($(this).children("option:selected").html())
        $(`#desaAreaUpdate${id}`).html(`<option selected value="">Pilih Kecamatan Terlebih Dahulu</option>`);
        loadDesaUpdate(id);
      });

      $(".desUpdate").change(function(){
        let id = $(this).data('id');
        $(`#village_update_${id}`).val($(this).children("option:selected").html())
      });


  $('#delivery_method').change(function(){
    var data = JSON.parse(`<?=$json?>`);
    var totalAmount = <?=$totalAmount?>;
    // console.log(data);
    $.ajax({
        type:'GET',
        url:'<?= base_url(aksestoko_route('aksestoko/order/shipment_group_price')); ?>/' + $(this).val(),
        success: function(res)
        { 
          res = JSON.parse(res);
          total = 0;
          if(res.length>0){
            for(var i = 0 ; i < data.length ; i++){
              for(var j = 0 ; j < res.length ; j++ ){
                if(data[i].id == res[j].product_id){
                  qty = $('#qty-'+data[i].id).val();
                  subtotal = qty * res[j].price;
                  total += subtotal;
                }
              }
            }
            
          }
          // console.log();
          if(total != 0){
            var label ;
            var minus ='';
            if(total < 0){
              minus ='-';
              label = 'Potongan Harga Pengiriman';
              removeClass = 'text-danger';
              addClass = 'text-success';
            }else{
              minus='';
              label = 'Penambahan Harga Pengiriman';
              removeClass = 'text-success';
              addClass = 'text-danger';
            }
            $('#label-potongan-pengiriman').html(label);
            $('#total-potongan-shipment').html(minus+' Rp '+ formatMoney(Math.abs(total.toFixed(2))));
            $('#total-akhir').html('Rp '+ (formatMoney(totalAmount+total)));
            $('#total-potongan-shipment').removeClass(removeClass);
            $('#total-potongan-shipment').addClass(addClass);
            
            $('#div-total-akhir').removeClass('hidden');
            $('#div-potongan-shipment').removeClass('hidden');
          }else{
            $('#total-potongan-shipment').html('');
            $('#div-total-akhir').addClass('hidden');
            $('#div-potongan-shipment').addClass('hidden');
          }
        }
    });
  }).change();
})

  $('#add-address').click(function(){
  if ($(this).find('i').hasClass('fas fa-angle-down')) {
    $(this).find('i').removeClass('fas fa-angle-down');
    $(this).find('i').addClass('fas fa-angle-up');

  }else{
    $(this).find('i').removeClass('fas fa-angle-up');
    $(this).find('i').addClass('fas fa-angle-down');
  }
  })

    $("#add-address").click(function(){
    $(this).find('.text-button').text(function(i, v){
       return v === 'Batal' ? 'Tambah Alamat' : 'Batal'
    });
    });



  $("#orderForm").submit(function(e){
    let send = $(this).data("send") == "ready"
    // alert(send)
    if(!send){
      $("#confirmOrder").modal("show")
      e.preventDefault();
      return false;      
    }  
  })
  $("#okOrder").click(function(){
    $("#orderForm").data("send", "ready").submit()
  })
  $("#dateKirim").keypress(function(e) {
    e.preventDefault();
    return false;
  });
  $(".delete-alamat").click(function(){
        let id = $(this).data('id')
        let name = $(this).data('name')
        $("#nameAddress").html(name)
        $("#deleteAlamat").modal('show')
        $("#okAddress").attr("href", "<?=base_url(aksestoko_route('aksestoko/auth/delete_address/'))?>" + id + "?redirect=<?=$this->uri->uri_string?>")
      })


  $("#dateKirim").val("<?=$this->session->userdata('delivery_date')?>");

</script>
    <script>
        // Define the tour!
        var tour = {
            id: "checkout",
            onClose: function(){
                callAjax();
            },
            onEnd : function(){
                        callAjax()
            },
            steps: [
                {
                    title: "Pilih Alamat",
                    content: "Klik tombol Ganti Alamat",
                    target: "a.btn.btn-outline-primary.btn-sm.mt-2",
                    placement: "top",
                },
                {
                    title: "Pilih Tanggal Pengiriman",
                    content: "Pilih Tanggal Pengiriman",
                    target: "input#dateKirim",
                    placement: "top",
                },
                {
                    title: "Pilih Shipping",
                    content: "Pilih Tanggal Pengiriman",
                    target: "select#shipping",
                    placement: "top",
                },
                {
                    title: "Pilih Methode Pembayaran",
                    content: "Pilih metodhe pembayaran",
                    target: "select#pilih_payment",
                    placement: "top",
                },
                {
                    title: "Pilih Pengiriman",
                    content: "Pilih Pengiriman",
                    target: "select#pilih_shipping",
                    placement: "top",
                },  
                {
                    title: "Lanjutkan Pembayaran",
                    content: "Proses pesananan kamu sekarang",
                    target: "button.btn.btn-success.btn-block",
                    placement: "top",
                }
                
            ]
        };
        
        <?php
          if (!$guide->checkout) {
        ?>
          hopscotch.startTour(tour);      
        <?php
          }
        ?>


        function callAjax(){
          isStart = false;
          $.ajax({
            url      : '<?= base_url(aksestoko_route('aksestoko/auth/set_guide/checkout/1')); ?>',
            type     : 'GET',
          }) 
          
        }
        
    </script>
