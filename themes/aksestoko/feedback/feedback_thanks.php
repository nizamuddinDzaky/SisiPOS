
    <section class="py-main section-status-payment">
      <div class="container">
        <div class="box-status-payment">
          <div class="subheading text-center">
            <h4>Berhasil mengisi survei</h4>
            <img src="<?=$assets_at?>img/common/ic_success.png" class="img-fluid" alt="Success">
            <p>Terima kasih telah berpatisipasi dalam survei ini</p>
          </div>
          <div class="row">
            <?php 
                $redirect = $this->session->userdata('redirect') ?? null;
                $my_page = base_url(aksestoko_route($redirect ?? 'aksestoko/home/select_supplier'));
            ?>
            <div class="col-md-12 text-center">
                <a href="<?= $my_page ?>" class="btn btn-primary">Selesai</a>
            </div>
        </div>
      </div>
    </section>
