
    <section class="section-cover-dashboard supplier-page">
      <div class="container">
        <div class="heading-w-link text-white">
          <h2 class="animated fadeInUp">Daftar Distributor</h2>
        </div>
        <div class="">
          <p class="white animated fadeInUp hidden">Silahkan pilih distributor yang Anda inginkan</p>
        </div>
      </div>
    </section>

    <section class="section-products py-main">
      <div class="container">
        <div class="product-list-home">
          <div class="row">
            <?php if (count($company)>0){foreach($company as $comp){?>
            <div class="col-sm-12 col-md-12 col-lg-6 mb-2">
              <a href="<?= base_url(aksestoko_route('aksestoko/home/select_supplier/'.$comp->company_id))?>">
                <div class="product-item box animated fadeInUp delayp1 ">
                  <div id="show" class="row align-items-start justify-content-start px-4 py-4" checked>
                    <div class="col-4 align-self-center justify-content-center text-center">
                      <img src="<?= $comp->avatar == '' || !$comp->avatar ? base_url('assets/uploads/logos/') . $comp->logo : base_url('assets/uploads/avatars/') .$comp->avatar ?>" onerror="this.src='<?= base_url('assets/uploads/logos/logo.png')?>'" class="img img-fluid" alt="Product">
                    </div>
                    <div class="col-8">
                        <h5 class="text-truncate my-0 text-dark py-1"><?php echo $comp->company ?></h5>
                      <!-- <input type="text" name="hidden-id-supplier" id="hidden-id-supplier" value="<?= $comp->company_id?>"> -->
                      <p class="text-truncate my-0 text-dark pb-2"><?= $comp->name?> </p>  
                      <p class="text-truncate my-0 text-muted"><?=$comp->address?></p>
                      <p class="text-truncate my-0 text-muted"><?=$comp->phone?></p>
                    </div>
                  </div>
                </div>
              </a>
            </div>
            <?php } }?>
          </div>
        </div>
      </div>  
              
    </section>

    <script>

        // Define the tour!
        var tour = {
            id: "select_distributor",
            onClose: function(){
                callAjax();
            },
            onEnd : function(){
                        callAjax()
            },
            steps: [
                {
                    title: "Pilih Distributor",
                    content: "Klik Distributor",
                    target: ".product-item.box.animated.fadeInUp.delayp1",
                    placement: "top",
                },
                {
                    title: "Lanjutkan ",
                    content: "Tekan tombol Lanjutkan",
                    target: "a#lanjutkan-btn",
                    placement: "left",
                },
                {
                    title: "Melihat menu lain",
                    content: "Tekan tombol ini untuk melihat beberapa menu lagi",
                    target: "a#navbarDropdown",
                    placement: "left",
                },
            ]
        };
        
        <?php
          if (!$guide->select_distributor) {
        ?>
          hopscotch.startTour(tour);      
        <?php
          }
        ?>

        function callAjax(){
          isStart = false;
          $.ajax({
            url      : '<?= base_url(aksestoko_route('aksestoko/auth/set_guide/select_distributor/1')); ?>',
            type     : 'GET',
          }) 
          
        }

    </script>

