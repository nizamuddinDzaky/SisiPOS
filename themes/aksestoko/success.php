<section class="py-main section-status-payment">
  <div class="container">
    <div class="box-status-payment">
      <div class="subheading text-center">
        <h2>Berhasil</h2>
        <img src="<?=$assets_at?>img/common/ic_success.png" class="img-fluid" alt="Success">
        <p>Terima kasih atas pemesanan Anda. Segera lakukan penerimaan barang di menu <b>Pemesanan</b> ketika barang telah sampai di toko Anda</p>
        <div class="box py-2 px-4">
          <div class="row mb-2">
            <div class="col-md-6 col-lg-6 col-sm-12 text-left">
              <span class="h6">
                ID Pemesanan
              </span>
            </div>
            <div class="col-md-6 col-lg-6 col-sm-12 text-right">
              <span class="text-muted">
                <?=$purchase_data->cf1?>
              </span>
            </div>
          </div>
          <div class="row mb-2">
            <div class="col-md-6 col-lg-6 col-sm-12 text-left">
              <span class="h6">
                Cara Pembayaran
              </span>
            </div>
            <div class="col-md-6 col-lg-6 col-sm-12 text-right">
              <span class="text-muted">
                <?= $object->__status($purchase_data->payment_method)[0]?>
              </span>
            </div>
          </div>
          <?php if ($bank_data && $purchase_data->payment_method != 'cash on delivery') { ?>
          <div class="row mb-2">
            <div class="col-md-6 col-lg-6 col-sm-12 text-left">
              <span class="h6">
                Bank
              </span>
            </div>
            <div class="col-md-6 col-lg-6 col-sm-12 text-right">
              <span class="text-muted">
                <?=strtoupper($bank_data->bank_name)?>
              </span>
            </div>
          </div>
          <div class="row mb-2">
            <div class="col-md-6 col-lg-6 col-sm-12 text-left">
              <span class="h6">
                No. Rekening
              </span>
            </div>
            <div class="col-md-6 col-lg-6 col-sm-12 text-right">
              <span class="text-muted">
              <?=$bank_data->no_rekening?> a/n <?=$bank_data->name?>
              </span>
            </div>
          </div>
          <?php } ?>
        </div>
      </div>
      <div class="clearfix mt-3 text-center">
        <a href="<?=base_url(aksestoko_route('aksestoko/order/view/')).$purchase_data->id?>" class="btn btn-primary">OK</a>
      </div>
    </div>
  </div>
</section>