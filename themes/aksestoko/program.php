<style type="text/css">
span#basic-addon1:hover {
    background-color: #f7f7f7;
}

.input-group-prepend .input-group-text {
  border-radius: .25rem !important;
  border-top-left-radius: 0 !important;
  border-bottom-left-radius: 0 !important;
  border-left: hidden;
}
</style>

<section class="section-cover-red" style="min-height: auto !important; padding-top: 4.5rem !important;">
  <div class="container container-md">
    <ol class="breadcrumb">
      <li><a href="<?=base_url(aksestoko_route("aksestoko/home/main"))?>" class="active"><i class="fal fa-angle-left"></i> Kembali</a></li>
    </ol>
  </div>
</section>

<section class="section-orders-header p-0">
  <div class="container container-md pb-3">
    <div class="heading">
      <h2 class="title-pemesanan animated fadeInUp">Program Kredit</h2>
    </div>
    <div class="">
      <p class="white animated fadeInUp">Layanan Program Kredit yang tersedia di AksesToko dengan kerjasama dengan perusahaan perbankan dan finansial teknologi</p>
    </div>
  </div>
</section>

<section class="section-orders-content">
  <div class="container container-md">
    <div class="row pt-3 pb-5 animated fadeInDown">
        <?php foreach ($programs as $key => $p) { ?>
          <div class="col-md-6">
            <div class="card">
              <img class="card-img-top" src="<?= $p['image'] ?>" alt="Card image cap">
              <div class="card-body">
                <h5 class="card-title"><?= $p['title'] ?></h5>
                <p class="card-text text-justify"><?= $p['description'] ?></p>
                <a href="<?= $p['link'] ?>" class="btn btn-primary float-right" style="font-size: 14px;padding: 7px 12px;">Lihat selengkapnya <i class="fa fa-chevron-right" aria-hidden="true"></i></a>
              </div>
              <div class="card-footer">
                <small class="text-muted">Oleh <span class="font-weight-bold"><?= $p['provided_by'] ?></span></small>
              </div>
            </div>
          </div>
        <?php }?>
        <?php if (count($programs) == 0) { ?>
          <div id="product-not-found" style="margin: auto;"><div class="box-cart"><img src="<?=$assets_at?>img/common/order-empty.png" class="img-fluid" alt="Produk Empty"><p>Tidak ada program kredit yang tersedia</p></div></div>
        <?php } ?>
    </div>
  </div>
</section>

<script type="text/javascript">
  $(document).ready(function(){

  
  })
</script>