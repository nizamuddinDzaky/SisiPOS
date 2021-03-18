<style type="text/css">
  .py-main {
    padding-top: 0 !important;
    padding-bottom: 5rem;
  }
  .section-products .container {
     margin-top: 0 !important;
      padding-top: 20px; 
}
.box-order-datepicker {
  margin-bottom: 0 !important;
}
span#basic-addon1:hover {
    background-color: #f7f7f7;
    padding-color:#B20838;
}
section.section-cover-dashboard {
    padding-top: 20px;
}

.input-group-prepend .input-group-text {
  border-radius: .25rem !important;
  border-top-left-radius: 0 !important;
  border-bottom-left-radius: 0 !important;
  border-left: hidden;
}

.ui-datepicker-calendar {
    display: none;
}



</style>

<!-- Breadcrumb -->
<section class="section-cover-red" style="min-height: auto !important; padding-top: 4.5rem !important;">
    <div class="container container-sm">
      <ol class="breadcrumb">
        <li><a href="<?=base_url(aksestoko_route("aksestoko/home/main"))?>" class="active"><i class="fal fa-angle-left"></i> Kembali</a></li>
      </ol>
    </div>
</section>

<section class="section-cover-point" style="padding-top: 0!important;">
  <div class="container container-sm">
    <div class="heading-w-link text-white p-0">
      <h2 class="animated fadeInUp" id="daftar-product-title">Pemberitahuan Anda</h2>
      <!-- <div class="">
          <p class="white animated fadeInUp">Data poin didapatkan dari poin toko pada <b>Bisnis Kokoh</b></p>
        </div> -->
    </div>

   <!--  <div class="box p-box box-order-datepicker" style="margin-top: 20px;">
	  <div class="row">
	  	
	    <div class="col-md-12">
	      <div class="input-group mb-3">
	        <input id="months" class="form-control date-picker" placeholder="" aria-describedby="basic-addon1" required=""> 
	        <div class="input-group-prepend">
	          <span class="input-group-text btn" id="basic-addon1"><i class="fal fa-search text-primary"></i></span>
	        </div>
	      </div>
	    </div>

	  </div>
	</div> -->

  </div>
</section>
<!-- End Breadcrumb -->




<!-- Body -->
<section class="section-point">
	<div class="container container-sm">
		<div class="point-content">
			<div class="row row-0">
				<div class="col-md-12">
					<div class="tab-content">
                    
                    <div id="list_notificationsPage">
                    </div>

                    <div id="footeerNotificationsPage">
                        <a id="tampil_lebih_page" href="javascript:void(0)">
                            <div id="footer-notif">
                                Tampilkan Lebih Banyak
                            </div>
                        </a>
                    </div>
						

					</div>
				</div>
			</div>
		</div>	
	</div>
</section>
<!-- End Body -->


    
