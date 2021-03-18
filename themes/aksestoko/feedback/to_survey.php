    <section class="py-main section-status-payment">
      <div class="container">
        <div class="box-status-payment">
          <div class="subheading text-center">
            <h2>Survei Pelanggan</h2>
            <img src="<?=$assets_at?>img/help/question-at.png" class="img-fluid" alt="Success">
            <p>Berikan pengalaman terbaik anda bersama AksesToko agar kami dapat terus memberikan pelayanan yang terbaik bagi anda</p>
          </div>
          <div class="row">
            <?php 
                $redirect = $this->session->userdata('redirect') ?? null;
                $my_page = base_url(aksestoko_route($redirect ?? 'aksestoko/home/select_supplier'));
                $survey = base_url(aksestoko_route('aksestoko/survey/form'));
            ?>
            <div class="col-md-12 text-center">
                <a href="<?= $survey ?>" class="btn btn-primary">Iya</a>
            </div>
            <div class="col-md-12 text-center">
                <a href="<?= $my_page ?>" class="btn">Skip</a>
            </div>
        </div>
      </div>
    </section>

