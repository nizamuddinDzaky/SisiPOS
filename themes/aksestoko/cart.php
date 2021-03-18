    <div class="container pt-4 pb-2">
      <h3 class="input-group">
        <a href="<?=base_url(aksestoko_route("aksestoko/home/main"))?>" class="btn btn-back mr-3"><i class="fal fa-angle-left"></i></a> 
         Keranjang Belanja
      </h3>
      
    </div>

    <section class="section-cart">
      <div class="container">
        
        <div class="row">

          <div class="col-lg-8">
            <div class="box p-box mb-3">

              <div class="subheading">
                <h3 class="box-subtitle">Produk (<?=count($cart)?>)</h3>
              </div>

                <?php 
                $totalQty = 0;
                $totalAmount = 0;
                $totalPoint = 0;
                foreach ($cart as $item) {
                  $totalQty += $item->cart_qty;
                  $totalAmount += $item->price * $item->cart_qty;
                  $totalPoint =+ 0;
                ?> 
                <input type="hidden" name="id[]" value="<?=$item->id_cart?>">
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
                      <h6 class="cart-top"> Rp <?= number_format($item->price, 0, ',', '.');?></h6>
                    <?php } ?>
                    <i id="delete-cart" class="fal fa-trash-alt delete-cart" data-id="<?=$item->id_cart?>" data-toggle="modal" data-target="#myModal"></i>

                    <div class="row mt-3">
                      <div class="col-md-6 mb-sm-down-12">
                        <label class="d-none d-sm-block">Jumlah</label>
                        <div class="input-number-container">
                          <div class="input-number-custom">
                            <span class="input-number-decrement dec button">–</span>
                              <div class="wrapper"><input class="input-number input-qty" data-id="<?=$item->id_cart?>" data-price="<?=$item->price?>" name="qty[]" type="text" value="<?=(int)$item->cart_qty?>" data-min-order="<?=$item->min_order?>" data-multiple="<?=$item->is_multiple?>" min="<?=$item->min_order?>" max="99999"></div>
                            <span class="input-number-increment inc button" >+</span>
                          </div>
                          <small class="input-number-label unit-cart-<?=$item->id_cart?>"><?=convert_unit($object->__unit($item->sale_unit))?></small>
                        </div>
                      </div>
                      <?php if($item->price > 0) { ?>
                      <div class="col-md-6 col-sm-12 jarak-mobile price-hide-on-mobile">
                        <label class="d-none d-sm-block">Harga</label>
                        <p id="total-price-<?=$item->id_cart?>">Rp <?= number_format($item->price * $item->cart_qty, 0, ',', '.');?></p>
                        <p id="error-qty-<?=$item->id_cart?>" style="display:none;">Melebihi jumlah max qty</p>
                      </div>
                      <?php } ?>
                      <!-- <div class="col-6 col-sm-4">
                        <label class="d-none d-sm-block">Poin</label>
                        <p>0</p>
                      </div> -->
                    </div>
                  </div>
                  <?php if($item->price > 0) { ?>

                  <div class="price-show-mobile">
                    <div class="row">
                      <div class="col-md-12">
                        <label class="d-none d-sm-block">Harga</label>
                        <p style="text-align: center; font-weight: 700;" id="total-price-mobile-<?=$item->id_cart?>">Rp <?= number_format($item->price * $item->cart_qty, 0, ',', '.');?></p>
                      </div>
                    </div>
                  </div>

                  <?php } ?>

                </div>
                <?php
                } ?>
            </div>
          </div>
          <div class="col-lg-4 col-lg-last" style="padding-bottom: 14%;">
            <div class="box p-box box-checkout-summary">
              <h3 class="box-subtitle">Ringkasan Pesanan</h3>
              <div class="row spacing-mobile">
                <div class="col-6 col-md-3 col-lg-6">
                  <div class="form-group">
                    <label>Jumlah Barang</label>
                    <p id="total-qty"><?=$totalQty?></p>
                  </div>
                </div>
                <!-- <div class="col-6 col-md-3 col-lg-6">
                  <div class="form-group">
                    <label>Total Poin</label>
                    <p id="total-point"><?=$totalPoint?> Pts</p>
                  </div>
                </div> -->
                <?php if($totalAmount > 0){ ?>
                <div class="col-12 col-md-12 col-lg-12">
                  <div class="form-group">
                    <label>Total Harga</label>
                    <p class="h5" id="total-amount">Rp <?= number_format($totalAmount, 0, ',', '.');?></p>
                  </div>
                </div>
                <?php } ?>

                <div class="col-12 pt-4">
                  <div class="form-group form-group-switch" <?=count($promo_data) > 0 ? 'style="display: none;"' : ''?>>
                    <h6 class="text-primary">Punya kode promo?</h6>
                    <p class="text-muted font-weight-light"> Daftar Kode Promo pada halaman awal</p>
                    <label class="switch">
                      <input type="checkbox" <?=count($promo_data) > 0 ? 'checked disabled' : ''?> id="codePromoCheck">
                      <span class="slider round"></span>
                    </label>
                  </div>
                  <form action="<?=base_url(aksestoko_route('aksestoko/order/add_promo'))?>" method="POST" <?=count($promo_data) == 0 ? 'style="display: none"' : ''?> id="div_promo">
                    <div class="input-group">
                      <input type="hidden" name="total_pembelian" value="<?=$totalAmount?>">
                      <input type="text" class="form-control" required placeholder="Kode Promo" id="code-promo" value="<?=count($promo_data)> 0 ? $promo_data->code_promo : ''?>" id="promoCode" name="code_promo" <?php if(count($promo_data)> 0) {echo "disabled";}?>>
                      <div class="input-group-append">
                        
                          <button class="btn btn-primary" type="button" id="btn-delete-promo" onclick="window.location.href='<?=base_url(aksestoko_route('aksestoko/order/delete_promo'))?>'" <?php if(count($promo_data)== 0){echo "style=\"display: none;\"";}?> >Hapus</button>
                          <button class="btn btn-primary" type="submit" id="btn-submit-promo" <?php if(count($promo_data)> 0){echo "style=\"display: none;\"";}?>>Gunakan</button>
                        
                      </div>
                    </div>
                  </form>
                </div>
                <div class="col-md-12 mt-3" id="div-discount" <?php if(count($promo_data)== 0){echo "style=\"display: none;\"";}?> >
                  <div class="form-group">
                    <label>Diskon</label>
                    <p class="text-success" id="discount-amount">- Rp
                      <?php 
                        $disc = 0;
                      if ($promo_data->tipe == 0) { //jika persentase
                        $disc = ($promo_data->value * $totalAmount)/100 ;
                        if ($disc > $promo_data->max_total_disc) {
                          $disc = $promo_data->max_total_disc;
                        }
                      }else{
                        $disc = $promo_data->value;
                      } 

                      echo number_format($disc, 0, ',', '.');
                      ?>
                    </p>
                  </div>
                  <div class="divider mb-3"></div>
                  <div class="form-group mb-0">
                    <label>Total Pembayaran</label>
                    <p class="h5" id="total-payment">Rp <?=number_format($totalAmount-$disc, 0, ',', '.')?></p>
                  </div>
                </div>
              </div>
            </div>
            <div class="box p-box box-checkout-action">
              <a href="../order/checkout" class="btn btn-success btn-block font-button">Lanjutkan</a>
            </div>
          </div>
        </div>
      </div>
    </section>

 <script>

$(document).ready(function(){

  $("#codePromoCheck").change(function() {
    if(this.checked) {
      $("#div_promo").slideDown(300);
    }else{
      $("#div_promo").slideUp(300);
    }
  }).change();

});
   

        
        // Define the tour!
        var tour = {
            id: "cart",
            onClose: function(){
                callAjax();
            },
            onEnd : function(){
                        callAjax()
            },
            
            steps: [
                {
                    title: "Hapus Item",
                    content: "Klik tombol ini",
                    target: "#delete-cart",
                    placement: "top",
                },
                {
                    title: "Masukkan kode promo",
                    content: "Masukkan kode promo",
                    target: "input#code-promo",
                    placement: "top",
                },
                {
                    title: "Terapkan kode promo",
                    content: "Tekan tombol ini",
                    target: "button#btn-submit-promo",
                    placement: "left",
                },
                {
                    title: "Lanjutkan !",
                    content: "Lanjutkan ke halaman Checkout",
                    target: "a.btn.btn-success.btn-block",
                    placement: "top",
                },
            ]
            
        };

        <?php
          if (!$guide->cart) {
        ?>
          hopscotch.startTour(tour);      
        <?php
          }
        ?>


        function callAjax(){
          isStart = false;
          $.ajax({
            url      : '<?= base_url(aksestoko_route('aksestoko/auth/set_guide/cart/1')); ?>',
            type     : 'GET',
          }) 
          
        }
 </script>