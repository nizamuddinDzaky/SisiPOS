<style>
.logo-payment{
 max-width:40px;
}

.button-payment{
    font-size:12px;
}
.button-payment:hover{
    background-color:#B20838;
    color:#fff;
    cursor:pointer;
}
.selected {
    background-color:#B20838;
    color:#fff;
}

a#salinRekening {
    font-size: 14px;
}
input.rekbank {
    border: 0;
    background: none;
    font-weight: 700;
    color: black;
    width: 100%;
}

.small-btn-payment{
    font-size:12px;
}

.font-13{
    font-size:13px !important;
}
</style>

<form action="" method="POST" data-send="not-ready" id="payment_form" enctype="multipart/form-data">
  <section class="section-cover-red">
    <div class="container container-sm">
      <ol class="breadcrumb">
        <li><a href="http://localhost/forca-pos/aksestoko/order/checkout" class="active"><i class="fal fa-angle-left"></i> Kembali</a></li>
      </ol>
      <div class="heading-w-link text-white">
        <h2 class="animated fadeInUp" id="title-pembayaran">Pembayaran</h2>
      </div>
    </div>
  </section>


  <section class="section-content-red py-main animated fadeInUp delayp1">
    <div class="container container-sm">

      <div class="box p-box mb-3">
        <div class="box-header">
            <span>Pemesanan Baru                        
                  </span></div>

            <!-- Start Row / Payment  -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="box-order-details ">    
                        <div class="box p-box mb-3"> 
                            <div class="row">
                                <div class="col-1">
                                    <div class="form-group">
                                        <img src="<?= base_url('assets/uploads/logos/') ?>cbd.png" alt="" class="logo-payment">
                                    </div>
                                </div>
                                <div class="col-7">
                                    <div class="form-group">
                                        <p class="h6" style="line-height: 40px;">Bayar Sebelum Dikirim</p>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <p class="h6" style="line-height: 40px; color:#B20838;">Rp. 4.900.000</p>
                                    </div>
                                </div>
                             
                            </div>
                            <!-- Halaman Collapse 01 -->
                            <div id="collapseTerimaId2083" class="pb-2 collapse" aria-expanded="true" style="">
                                <hr class="mt-0">
                                
                                <div class="row">

                                    <div class="col-md-6">
                                        <h6>Bank</h6>
                                        <div class="row">
                                            <!-- button -->
                                            <div class="col-auto" style="padding:5px !important">
                                                <div id="tunai" class="box button-payment selected px-3 py-1">
                                                    <div class="px-1 py-2">
                                                        <img src="<?= base_url('assets/uploads/logos/') ?>cbd.png" alt="" class="" style="max-width:15px;">
                                                        Tunai
                                                    </div>
                                                </div>    
                                            </div>
                                           
                                            <div class="col-auto" style="padding:5px !important">
                                                <div id="mandiri" class="box button-payment px-3 py-1">
                                                    <div class="px-1 py-2">
                                                        <img src="<?= base_url('assets/uploads/logos/') ?>cbd.png" alt="" class="" style="max-width:15px;">
                                                        Mandiri
                                                    </div>
                                                </div>    
                                            </div>
                                            <div class="col-auto" style="padding:5px !important">
                                                <div id="bca" class="box button-payment px-3 py-1">
                                                    <div class="px-1 py-2">
                                                        <img src="<?= base_url('assets/uploads/logos/') ?>cbd.png" alt="" class="" style="max-width:15px;">
                                                        BCA
                                                    </div>
                                                </div>    
                                            </div>
                                             <!-- End button -->
                                            
                                        </div>
                                        <!-- Content -->
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="" id="detail-bank-mandiri" style="margin-bottom: 30px !important; display:none;">
                                                    <div class="px-2 py-2">
                                                        <!-- <label>Ke nomor rekening tujuan</label> -->
                                                        <p class="h6 mb-1">
                                                            <input type="text" id="rekBankMandiri" class="rekbank" value="1234567890" readonly="">
                                                        </p>
                                                        <p class="mb-1">a/n Helios Corp</p>
                                                        <a id="salinRekening" href="javascript:void(0)" onclick="copyNorekMandiri()" class="text-blue"><i class="fal fa-copy mr-1"></i> Salin rekening</a>
                                                        <img src="http://localhost/forca-pos//assets/uploads//bank_logo/mandiri.png" class="img-fluid mt-3" width="100" alt="Logo">
                                                    </div>
                                                </div>
                                                <div class="" id="detail-bank-bca" style="margin-bottom: 30px !important; display:none;">
                                                    <div class="px-2 py-2">
                                                        <!-- <label>Ke nomor rekening tujuan</label> -->
                                                        <p class="h6 mb-1">
                                                            <input type="text" id="rekBankBca" class="rekbank" value="34234343242" readonly="">
                                                        </p>
                                                        <p class="mb-1">a/n Helios Corp</p>
                                                        <a id="salinRekening" href="javascript:void(0)" onclick="copyNorekBca()" class="text-blue"><i class="fal fa-copy mr-1"></i> Salin rekening</a>
                                                        <img src="http://localhost/forca-pos//assets/uploads//bank_logo/mandiri.png" class="img-fluid mt-3" width="100" alt="Logo">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Detail</h6>
                                        <div class="box">
                                            <div class="p-box px-3 py-3 ">                    
                                                <!-- <hr class="mt-0 mb-2"> -->

                                                <table class="table maintable ringkasan">
                                                    <tbody>
                                                        <tr>
                                                            <td class="no-border font-13"><label>Harga</label></td>
                                                            <td class="no-border bold text-right font-13">Rp 4.500.000</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="no-border font-13"><label>Pengiriman Distributor</label></td>
                                                            <td class="no-border bold text-right font-13">Rp 500.000</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="no-border font-13"><label>Diskon</label></td>
                                                            <td class="no-border bold text-right font-13">Rp 100.000</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-left" style="padding-right: 0;padding-left: 0; padding-top: 5px;padding-bottom: 5px; border-top: 1px solid rgba(0, 0, 0, 0.1);"><label>Yang Perlu Dibayar</label></td>
                                                            <td class="text-primary bold text-right" style="border-top: 1px solid rgba(0, 0, 0, 0.1); padding-right: 0;padding-left: 0; padding-top: 5px;padding-bottom: 5px;"> <h6>Rp 4.900.000</h6> </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group">
                                        <div class="px-2 py-2">
                                            <button type="submit" class="btn btn-primary btn-block small-btn-payment" id="pending">Selesaikan</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <!-- End Halaman Collapse 01 -->
                        <!-- Tombol Collapse 01 -->
                        <div class="order-details-check">
                            <a data-toggle="collapse" data-target="#collapseTerimaId2083" aria-expanded="false" aria-controls="collapseOrder" class="" style="font-size:12px;"></a>
                        </div>
                            <!-- End Tombol Collapse 01 -->
                    </div>
                </div> 
                </div>  
            </div>
            <!-- End Row / Payment -->

             <!-- Start Row / Payment  -->
             <div class="row">
                <div class="col-lg-12">
                    <div class="box-order-details ">    
                        <div class="box p-box mb-3"> 
                            <div class="row">
                                <div class="col-1">
                                    <div class="form-group">
                                        <img src="<?= base_url('assets/uploads/logos/') ?>credit.png" alt="" class="logo-payment">
                                    </div>
                                </div>
                                <div class="col-7">
                                    <div class="form-group">
                                        <p class="h6" style="line-height: 40px;">Tempo Dengan Distributor</p>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <p class="h6" style="line-height: 40px; color:#B20838;">Rp. 7.000.000</p>
                                    </div>
                                </div>
                             
                            </div>
                            <!-- Halaman Collapse 01 -->
                            <div id="collapsePaymentId02" class="pb-2 collapse" aria-expanded="true" style="">
                                <hr class="mt-0">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                    <h6>Bank</h6>
                                        <div class="row">
                                            <!-- button -->
                                            <div class="col-auto" style="padding:5px !important">
                                                <div id="tunai" class="box button-payment selected tunai02 px-3 py-1">
                                                    <div class="px-1 py-2">
                                                        <img src="<?= base_url('assets/uploads/logos/') ?>cbd.png" alt="" class="" style="max-width:15px;">
                                                        Tunai
                                                    </div>
                                                </div>    
                                            </div>
                                           
                                            <div class="col-auto" style="padding:5px !important">
                                                <div id="mandiri" class="box button-payment mandiri02 px-3 py-1">
                                                    <div class="px-1 py-2">
                                                        <img src="<?= base_url('assets/uploads/logos/') ?>cbd.png" alt="" class="" style="max-width:15px;">
                                                        Mandiri
                                                    </div>
                                                </div>    
                                            </div>
                                            <div class="col-auto" style="padding:5px !important">
                                                <div id="bca" class="box button-payment bca02 px-3 py-1">
                                                    <div class="px-1 py-2">
                                                        <img src="<?= base_url('assets/uploads/logos/') ?>cbd.png" alt="" class="" style="max-width:15px;">
                                                        BCA
                                                    </div>
                                                </div>    
                                            </div>
                                             <!-- End button -->
                                            
                                        </div>
                                        <!-- Content -->
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="detail-bank-mandiri02" id="detail-bank-mandiri" style="margin-bottom: 30px !important; display:none;">
                                                    <div class="px-2 py-2">
                                                        <!-- <label>Ke nomor rekening tujuan</label> -->
                                                        <p class="h6 mb-1">
                                                            <input type="text" id="rekBankMandiri" class="rekbank" value="1234567890" readonly="">
                                                        </p>
                                                        <p class="mb-1">a/n Helios Corp</p>
                                                        <a id="salinRekening" href="javascript:void(0)" onclick="copyNorekMandiri()" class="text-blue"><i class="fal fa-copy mr-1"></i> Salin rekening</a>
                                                        <img src="http://localhost/forca-pos//assets/uploads//bank_logo/mandiri.png" class="img-fluid mt-3" width="100" alt="Logo">
                                                    </div>
                                                </div>
                                                <div class="detail-bank-bca02" id="detail-bank-bca" style="margin-bottom: 30px !important; display:none;">
                                                    <div class="px-2 py-2">
                                                        <!-- <label>Ke nomor rekening tujuan</label> -->
                                                        <p class="h6 mb-1">
                                                            <input type="text" id="rekBankBca" class="rekbank" value="34234343242" readonly="">
                                                        </p>
                                                        <p class="mb-1">a/n Helios Corp</p>
                                                        <a id="salinRekening" href="javascript:void(0)" onclick="copyNorekBca()" class="text-blue"><i class="fal fa-copy mr-1"></i> Salin rekening</a>
                                                        <img src="http://localhost/forca-pos//assets/uploads//bank_logo/mandiri.png" class="img-fluid mt-3" width="100" alt="Logo">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="py-4">
                                            <h6>Rencana Pelunasan</h6>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="basic-addon1"><i class="fal fa-credit-card text-primary"></i></span>
                                                </div>
                                                <select class="form-control col-md-12" name="payment_durasi" id="payment_durasi"></select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Detail</h6>
                                        <div class="box">
                                            <div class="p-box px-3 py-3 ">                                                                    
                                                <hr class="mt-0 mb-2">

                                                <table class="table maintable ringkasan">
                                                    <tbody>
                                                        <tr>
                                                            <td class="no-border font-13"><label>Harga</label></td>
                                                            <td class="no-border bold text-right font-13">Rp 4.500.000</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="no-border font-13"><label>Pengiriman Distributor</label></td>
                                                            <td class="no-border bold text-right font-13">Rp 500.000</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="no-border font-13"><label>Diskon</label></td>
                                                            <td class="no-border bold text-right font-13">Rp 100.000</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-left" style="padding-right: 0;padding-left: 0; padding-top: 5px;padding-bottom: 5px; border-top: 1px solid rgba(0, 0, 0, 0.1);"><label>Yang Perlu Dibayar</label></td>
                                                            <td class="text-primary bold text-right" style="border-top: 1px solid rgba(0, 0, 0, 0.1); padding-right: 0;padding-left: 0; padding-top: 5px;padding-bottom: 5px;"> <h6>Rp 4.900.000</h6> </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                                <div class="row">
                                    <div class="form-group">
                                        <div class="px-2 py-2">
                                            <button type="submit" class="btn btn-primary btn-block small-btn-payment" id="pending">Selesaikan</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <!-- End Halaman Collapse 01 -->
                        <!-- Tombol Collapse 01 -->
                        <div class="order-details-check">
                            <a data-toggle="collapse" data-target="#collapsePaymentId02" aria-expanded="false" aria-controls="collapseOrder" class="" style="font-size:12px;"></a>
                        </div>
                            <!-- End Tombol Collapse 01 -->
                    </div>
                </div> 
                </div>  
            </div>
            <!-- End Row / Payment -->

             <!-- Start Row / Payment  -->
             <div class="row">
                <div class="col-lg-12">
                    <div class="box-order-details ">    
                        <div class="box p-box mb-3"> 
                            <div class="row">
                                <div class="col-1">
                                    <div class="form-group">
                                        <img src="<?= base_url('assets/uploads/logos/') ?>cod.png" alt="" class="logo-payment">
                                    </div>
                                </div>
                                <div class="col-7">
                                    <div class="form-group">
                                        <p class="h6" style="line-height: 40px;">Bayar Di Tempat</p>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <p class="h6" style="line-height: 40px; color:#B20838;">Rp. 7.000.000</p>
                                    </div>
                                </div>
                             
                            </div>
                            <!-- Halaman Collapse 03 -->
                            <div id="collapsePaymentId03" class="pb-2 collapse" aria-expanded="true" style="">
                                <hr class="mt-0">
                                
                                <div class="row">
                                    
                                    <div class="col-md-12">
                                    <h6>Detail</h6>
                                        <div class="box">
                                            <div class="p-box px-3 py-3 ">                    
                                                
                                            <table class="table maintable ringkasan">
                                                    <tbody>
                                                        <tr>
                                                            <td class="no-border font-13"><label>Harga</label></td>
                                                            <td class="no-border bold text-right font-13">Rp 4.500.000</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="no-border font-13"><label>Pengiriman Distributor</label></td>
                                                            <td class="no-border bold text-right font-13">Rp 500.000</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="no-border font-13"><label>Diskon</label></td>
                                                            <td class="no-border bold text-right font-13">Rp 100.000</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-left" style="padding-right: 0;padding-left: 0; padding-top: 5px;padding-bottom: 5px; border-top: 1px solid rgba(0, 0, 0, 0.1);"><label>Yang Perlu Dibayar</label></td>
                                                            <td class="text-primary bold text-right" style="border-top: 1px solid rgba(0, 0, 0, 0.1); padding-right: 0;padding-left: 0; padding-top: 5px;padding-bottom: 5px;"> <h6>Rp 4.900.000</h6> </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group">
                                        <div class="px-2 py-2">
                                            <button type="submit" class="btn btn-primary btn-block small-btn-payment" id="pending">Selesaikan</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <!-- End Halaman Collapse 01 -->
                        <!-- Tombol Collapse 01 -->
                        <div class="order-details-check">
                            <a data-toggle="collapse" data-target="#collapsePaymentId03" aria-expanded="false" aria-controls="collapseOrder" class="" style="font-size:12px;"></a>
                        </div>
                            <!-- End Tombol Collapse 01 -->
                    </div>
                </div> 
                </div>  
            </div>
            <!-- End Row / Payment -->

             <!-- Start Row / Payment  -->
             <div class="row">
                <div class="col-lg-12">
                    <div class="box-order-details ">    
                        <div class="box p-box mb-3"> 
                            <div class="row">
                                <div class="col-1">
                                    <div class="form-group">
                                        <img src="<?= base_url('assets/uploads/logos/') ?>cbd.png" alt="" class="logo-payment">
                                    </div>
                                </div>
                                <div class="col-7">
                                    <div class="form-group">
                                        <p class="h6" style="line-height: 40px;">Kreditpro</p>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <p class="h6" style="line-height: 40px; color:#B20838;">Rp. 21.000.000</p>
                                    </div>
                                </div>
                             
                            </div>
                            <!-- Halaman Collapse 04 -->
                            <div id="collapsePaymentId04" class="pb-2 collapse" aria-expanded="true" style="">
                                <hr class="mt-0">
                                
                                <div class="row">
                                    
                                    <div class="col-md-12">
                                    <h6>Detail</h6>
                                        <div class="box">
                                            <div class="p-box px-3 py-3 ">                    
                                                
                                            <table class="table maintable ringkasan">
                                                    <tbody>
                                                        <tr>
                                                            <td class="no-border font-13"><label>Harga</label></td>
                                                            <td class="no-border bold text-right font-13">Rp 4.500.000</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="no-border font-13"><label>Pengiriman Distributor</label></td>
                                                            <td class="no-border bold text-right font-13">Rp 500.000</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="no-border font-13"><label>Diskon</label></td>
                                                            <td class="no-border bold text-right font-13">Rp 100.000</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-left" style="padding-right: 0;padding-left: 0; padding-top: 5px;padding-bottom: 5px; border-top: 1px solid rgba(0, 0, 0, 0.1);"><label>Yang Perlu Dibayar</label></td>
                                                            <td class="text-primary bold text-right" style="border-top: 1px solid rgba(0, 0, 0, 0.1); padding-right: 0;padding-left: 0; padding-top: 5px;padding-bottom: 5px;"> <h6>Rp 4.900.000</h6> </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group">
                                        <div class="px-2 py-2">
                                            <button type="submit" class="btn btn-primary btn-block small-btn-payment" id="pending">Selesaikan</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <!-- End Halaman Collapse 01 -->
                        <!-- Tombol Collapse 01 -->
                        <div class="order-details-check">
                            <a data-toggle="collapse" data-target="#collapsePaymentId04" aria-expanded="false" aria-controls="collapseOrder" class="" style="font-size:12px;"></a>
                        </div>
                            <!-- End Tombol Collapse 01 -->
                    </div>
                </div> 
                </div>  
            </div>
            <!-- End Row / Payment -->
             


    </div>
    <p id="catatan" class="font-size-md text-muted">
        <strong>Catatan:</strong>
        Bila Anda mengalami masalah atau kesulitan saat melakukan pembayaran, Anda bisa menghubungi kami di nomor <a href="tel:+628116065246" target="_blank">+62811 6065 246</a>
    </p>
    <div class="box p-box" id="divForm">
                      <div class="row mb-2" id="uploadForm">
          <input type="hidden" value="25613" name="purchase_id" required="">
        
          <div class="col-md-12">
            <h6>Unggah Bukti Pembayaran</h6>
            
              <div class="custom-file">
                <label for="payment_receipt" class="custom-file-upload" style="margin:0;">
                  <i class="fas fa-copy"></i> Pilih File
                </label>
                <input type="file" accept=".jpg , .png , .JPEG" class="custom-file-input" id="payment_receipt" name="payment_receipt" style="width: 1px;">
                <span id="valHaha" style="font-size: 14px; color: #8B8D8E;"><i>Silakan unggah bukti pembayaran Anda</i></span>
              </div>
              <small class="font-italic text-danger">Disarankan : ekstensi file .jpg - ukuran &lt; 15mb</small>
            

          </div>
          <div class="col-md-12" id="nominalControl">
              <div class="form-group">
                <label>Nominal</label>
                <input type="text" min="1" placeholder="50.000" class="form-control" id="input-payment-amounts" max="50000">
                <input type="text" class="" min="1" name="payment_nominal" id="input-payment-amounts-hidden" style="display: none;">
                <span id="tes-dio-alert" class="hidden">Tidak Boleh Lebih</span>
              </div>
          </div>
        </div>
                      
        
        <input type="hidden" name="purchase_id" value="25613">
        <input type="hidden" name="btn_value" id="btn_value" value="25613">
        <button type="submit" class="btn btn-success btn-block" id="unggah">Unggah Bukti Pembayaran</button>
        
      </div>
    
    </div>
</section>
</form>

<script>
$(document).ready(function(){
    $("#mandiri").click(function(){
        $(this).addClass("selected");
        $("#detail-bank-mandiri").slideDown("slow");
        $("#tunai").removeClass("selected"); 
        $("#bca").removeClass("selected");
        $("#detail-bank-bca").slideUp("slow");
    });

    $("#tunai").click(function(){
        $(this).addClass("selected");
        $("#detail-bank-mandiri").slideUp("slow");
        $("#detail-bank-bca").slideUp("slow");
        $("#mandiri").removeClass("selected");
        $("#bca").removeClass("selected");
    });
    $("#bca").click(function(){
        $(this).addClass("selected");
        $("#detail-bank-bca").slideDown("slow");
        $("#detail-bank-mandiri").slideUp("slow");
        $("#mandiri").removeClass("selected");
        $("#tunai").removeClass("selected");
    });


    // 02
    $(".mandiri02").click(function(){
        $(this).addClass("selected");
        $(".detail-bank-mandiri02").slideDown("slow");
        $(".tunai02").removeClass("selected"); 
        $(".bca02").removeClass("selected");
        $(".detail-bank-bca02").slideUp("slow");
    });

    $(".tunai02").click(function(){
        $(this).addClass("selected");
        $(".detail-bank-mandiri02").slideUp("slow");
        $(".detail-bank-bca02").slideUp("slow");
        $(".mandiri02").removeClass("selected");
        $(".bca02").removeClass("selected");
    });
    $(".bca02").click(function(){
        $(this).addClass("selected");
        $(".detail-bank-bca02").slideDown("slow");
        $(".detail-bank-mandiri02").slideUp("slow");
        $(".mandiri02").removeClass("selected");
        $(".tunai02").removeClass("selected");
    });
});

function copyNorekMandiri() {
  var copyText = document.getElementById("rekBankMandiri");
  copyText.select();
  document.execCommand("copy");
  alertCustom("Berhasil Disalin No Rek : " + copyText.value);
}


function copyNorekBca() {
  var copyText = document.getElementById("rekBankBca");
  copyText.select();
  document.execCommand("copy");
  alertCustom("Berhasil Disalin No Rek : " + copyText.value);
}

</script>