    <section class="section-product-detail">
      <div class="container">
        <a href="<?=base_url(aksestoko_route("aksestoko/home/main"))?>" class="btn btn-back"><i class="fal fa-angle-left"></i></a>
        <div class="row row-4">
          <div class="col-lg-5 col-md-12 product-detail product-detail-left">
            <div class="container">
              <div class="img-gallery animated fadeIn delayp1">
                <div>
                  <a href="<?= url_image_thumb($product->image, false) ?>" data-lightbox="mygallery" data-title="">
                    <img class="img-fluid" style="margin: auto;" src="<?= url_image_thumb($product->image, false) ?>" onerror="this.src='<?= base_url('assets/uploads/no_image.png')?>'" alt="First slide">
                  </a>
                </div>
                <div>
                  <a href="<?= url_image_thumb($product->image, false) ?>" data-lightbox="mygallery">
                    <img class="img-fluid" style="margin: auto;" src="<?= url_image_thumb($product->image, false) ?>" onerror="this.src='<?= base_url('assets/uploads/no_image.png')?>'" alt="Second slide">
                  </a>
                </div>
                <div>
                  <a href="<?= url_image_thumb($product->image, false) ?>" data-lightbox="mygallery">
                    <img class="img-fluid" style="margin: auto;" src="<?= url_image_thumb($product->image, false) ?>" onerror="this.src='<?= base_url('assets/uploads/no_image.png')?>'" alt="Third slide">
                  </a>
                </div>
                <div>
                  <a href="<?= url_image_thumb($product->image, false) ?>" data-lightbox="mygallery">
                    <img class="img-fluid" style="margin: auto;" src="<?= url_image_thumb($product->image, false) ?>" onerror="this.src='<?= base_url('assets/uploads/no_image.png')?>'" alt="Fourth slide">
                  </a>
                </div>
                <div>
                  <a href="<?= url_image_thumb($product->image, false) ?>" data-lightbox="mygallery">
                    <img class="img-fluid" style="margin: auto;" src="<?= url_image_thumb($product->image, false) ?>" onerror="this.src='<?= base_url('assets/uploads/no_image.png')?>'" alt="Fifth slide">
                  </a>
                </div>
              </div>

              <div class="img-gallery-thumbnail mt-5 animated fadeInUp delayp1">
                <div>
                  <img class="img-fluid" src="<?= url_image_thumb($product->image, false) ?>" onerror="this.src='<?= base_url('assets/uploads/no_image.png')?>'" alt="First slide">
                </div>
                <div>
                  <img class="img-fluid" src="<?= url_image_thumb($product->image, false) ?>" onerror="this.src='<?= base_url('assets/uploads/no_image.png')?>'" alt="Second slide">
                </div>
                <div>
                  <img class="img-fluid" src="<?= url_image_thumb($product->image, false) ?>" onerror="this.src='<?= base_url('assets/uploads/no_image.png')?>'" alt="Third slide">
                </div>
                <div>
                  <img class="img-fluid" src="<?= url_image_thumb($product->image, false) ?>" onerror="this.src='<?= base_url('assets/uploads/no_image.png')?>'" alt="Fourth slide">
                </div>
                <div>
                  <img class="img-fluid" src="<?= url_image_thumb($product->image, false) ?>" onerror="this.src='<?= base_url('assets/uploads/no_image.png')?>'" alt="Fifth slide">
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-7 col-md-12 col-sm-12 bg-light product-detail product-detail-right">
            <div class="container">
              <h2 class="product-detail-title animated fadeInUp delayp1"><?=$product->name?></h2>
              <p class="product-detail-subtitle animated fadeInUp delayp2">
                <?=$product->code?>
                <br>
                <small> Dijual oleh <b> <?=$supplier->company?> </b></small>
                <br>
                <small> <i> Minimal pembelian adalah <?=$product->min_order?> <?=convert_unit($unit->name)?> <?php if($product->is_multiple) { echo "dan berlaku kelipatan"; } ?> </i> </small>
              </p>
              <form method="POST" id='form_keranjang' action="<?= base_url(aksestoko_route('aksestoko/order/add_cart')) ?>">
                <div class="product-detail-action animated fadeInUp delayp3">
                  <div class="row">
                    <?php if($product->price > 0){ ?>
                    <div class="col-12 col-sm-12 col-lg-4">
                      <div class="form-group">
                        <label>Jumlah</label>
                        <div class="input-number-container">
                          <div class="input-number-custom">
                            <span id="kurang" class="input-number-decrement dec button">-</span>
                            <div class="wrapper">
                              <input  class="input-number" id="quantity" type="number" value="<?=$product->min_order?>" data-min-order="<?=$product->min_order?>" min="<?=$product->min_order?>" max="99999" data-multiple="<?=$product->is_multiple?>" name="quantity" required>
                            </div>
                            <span id="tambah" class="input-number-increment inc button">+</span>
                          </div>
                          <small class="input-number-label"><?=convert_unit($unit->name)?></small>
                        </div>
                      </div>
                    </div>
                    <!-- Yang dikirim ke controller -->
                    <input class="input-id" type="hidden" value="<?=$product_id?>" name="product_id">
                    <input class="input-sup-id" type="hidden" value="<?=$supplier_id?>" name="supplier_id">
                    <input class="input-user-id" type="hidden" value="<?=$user_id?>"  name="user_id">
                    <!-- End -->
                    
                    <div class="col-12 col-sm-12 col-lg-8">
                      <div class="form-group">
                        <label>Harga</label>
                        <h4 class="" id="total_price" data-price="<?=$product->price?>">Rp <?= number_format($product->price, 2, ',', '.');?></h4>
                        <small style="color: red"> <i>Harga bisa berubah sewaktu - waktu</i> </small>
                      </div>
                    </div>
                    <?php } ?>
                  </div>
                  <div class="row">
                    <?php if($product->price > 0){ ?>
                    <div class="col-auto responsive-mobile">
                      <button type="submit" class="btn btn-success" id="tambah_keranjang" name="tambah_keranjang">Tambah ke Keranjang</button>
                    </div>
                    <div class="col-auto responsive-mobile">
                      <input type="hidden" name="beli_sekarang" value="0">
                      <input type="submit" class="btn btn-primary" id="beli_sekarang" value="Beli Sekarang">
                    </div>
                    <?php } else { ?>
                      <span style="color: red; padding: 10px">Tidak dapat membeli produk ini, harga belum diset oleh distributor.</span>
                    <?php } ?>
                  </div>
                </div>
              </form>

            </div>
          </div>
        </div>
      </div>
    </section>
    
<script>
  $(window).on('load', function() {
    $("#quantity").change(function () {
      if (parseFloat(this.value) > this.max){
        this.value = this.max;
      } 
      if($(this).val() < <?=$product->min_order?>){
        $(this).val("<?=$product->min_order?>")
      }
      let mod = $(this).val() % <?=$product->min_order?>;
      if($(this).data('multiple') && mod != 0){
        $(this).val((parseInt($(this).val())-mod))
      }

      let price = formatMoney($("#total_price").data("price") * $(this).val());
      $("#total_price").html("Rp "+price).change();
      
    }).change();

    $("#tambah_keranjang").click(function(){
      $(this).attr("disabled", "disabled");
      $(this).html("Memuat...");
      $('#form_keranjang').submit();
    });

    $("#beli_sekarang").click(function(){
      $("input[name=beli_sekarang]").attr("value", "1");
      $(this).attr("disabled", "disabled");
      $(this).val("Memuat...");
      $('#form_keranjang').submit();
    });

  });

 


 </script>
