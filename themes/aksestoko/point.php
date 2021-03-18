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
      <h2 class="animated fadeInUp" id="daftar-product-title">Poin Toko</h2>
      <div class="">
          <p class="white animated fadeInUp">Data poin didapatkan dari poin toko pada <b>Bisnis Kokoh</b></p>
        </div>
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

						<div class="box p-box" style="border-radius: 20px; color: #fff;background-image: url('<?= base_url().'assets/uploads/bg_card.png' ?>'); background-size: cover;">
							<div style="padding:20px;">
								<div class="row">
									<div class="col-1">
										<i class="fal fa-coins large-size"></i>
									</div>
									<div class="col-6">
										<div class="nama-toko">
											<span class="point_namatoko"><?= $company ?></span><br>
											<span class="point_idbk"><?= $identity ?></span>
										</div>
									</div>
									<div class="col-5">
									<?php if($poin['num_rows'] != 0) { ?>
										<div class="tanggal-point" style="text-align: right;">
											<span class="point_date"><?= date('F Y', strtotime($poin['data'][0]['TAHUN'].'-'.$poin['data'][0]['BULAN'].'-01'))?></span>
										</div>
									<?php }else{ ?>
										<div class="tanggal-point" style="text-align: center;">
											<span style="font-size: 1.5rem; font-weight: 700;"> − </span>
										</div>
									<?php }?>
									</div>
									
								</div>
								<div class="row" style="margin-top: 40px;">
									<div class="col-12">
										<div style="background-color: #fff; border-radius: 20px; color: #7b7b7b;  padding: 20px;">
											<div class="row">

												<div class="col-12" style="">
													<!-- Point -->
													<?php if($poin['num_rows'] != 0) { ?>
													<div id="pointAvailable">
														<table style="border:0; margin:auto;">
															<tr>
																<td rowspan="2" style="padding-right: 10px;"><img class="point-image" src="<?php echo base_url().'assets/uploads/smile_red.png' ?>"></td>
																<td><span class="title_point">POIN</span></td>
															</tr>
															<tr>
																<td><span class="jml_point"><?= $poin['data'][0]['JML_POIN']; ?></span></td>
															</tr>
														</table>
													</div>
													<!-- End Point -->
													<?php } else { ?>
													<!-- No Point -->
													<div id="noPoint" style="text-align: center;">
														<img class="noPoint-image" src="<?php echo base_url().'assets/uploads/no_point.png' ?>">
														<span class="no_point">Tidak ada data Poin</span>
													</div>
													<!-- End No Point -->
													<?php } ?>
												</div>
											
												
											</div>
											
										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- Banner -->
						<!-- <div class="banner-point">
							<span style="font-weight: 700">Lorem Ipsum ?</span><br>Lorem ipsum dolor ismet
							<div class="img-responsive">
								<a href="<?=base_url(aksestoko_route("aksestoko/home/main"))?>">
									<img src="<?php echo base_url().'assets/uploads/banner.jpeg' ?>" class="img-fluid" alt="Responsive image">
								</a>
							</div>
						</div> -->
						<!-- End banner -->

					</div>
				</div>
			</div>
		</div>	
	</div>
</section>
<!-- End Body -->