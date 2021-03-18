
<form action="<?= $url_save?>" method="POST" data-send="not-ready" id="payment_form" enctype="multipart/form-data">
  <section class="section-cover-red">
    <div class="container container-sm">
      <ol class="breadcrumb">
        <li><a href="<?= $purchase->id ? base_url(aksestoko_route("aksestoko/order/view/")) . $purchase->id : base_url(aksestoko_route("aksestoko/order/checkout"))?>" class="active"><i class="fal fa-angle-left"></i> Kembali</a></li>
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
          <?php if($purchase->cf1) { ?>
            <span>ID Pemesanan</span> <strong class="purchase-id"><?=$purchase->cf1?></strong>
          <?php } else { ?>
            <!-- <span>Nomor Pesanan</span> <strong><?=$purchase->reference_no?></strong>             -->
            <span>Pemesanan Baru</strong>                        
          <?php } ?>
        </div>
        <!-- <p class="font-size-md">Mohon melakukan pembayaran dalam waktu <span class="payment-time badge bg-warning">01:05:20</span> dari waktu pemesanan.</p> -->
        <hr>
        <div class="row">
            <div class="col-md-12" >
              <div class="subheading" style="margin-bottom: 10px !important">
                <h3 class="box-subtitle">Pilih Cara Pembayaran</h3>
              </div>
              <div class="input-group mb-3">
                <div class="input-group-prepend">
                  <span class="input-group-text" id="basic-addon1"><i class="fal fa-money-check-alt text-primary"></i></span>
                </div>
                  <select id="pilih_payment" class="form-control" name="payment_method" required>
                    <option value="">Pilih Cara Pembayaran</option>
                    <?php foreach ($payment_methods as $key_payment_methods => $payment_method) {?>
                      <?php if($purchase->payment_status == 'reject' && $payment_method->value == 'kredit_pro'){continue;}?>
                      <option value="<?=$payment_method->value?>"><?=ucwords($payment_method->name)?></option>
                    <?php } ?>
                  </select>
              </div>
            </div>
<!-- 
            
            <div class="col-md-12">
              <div id="hidden_div_payment_kredit" style="display: none;">
                <div class="subheading">
                  <h3 class="box-subtitle">Form Untuk Kredit</h3>
                </div>
                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1"><i class="fal fa-credit-card text-primary"></i></span>
                  </div>
                   <select class="form-control" name="shipping_by">
                      <option value="">Lorem Ipsum</option>
                      <option value="">Dolor Ismet</option>
                      <option value="">Ismet Ipsum</option>
                    </select>
                </div>
              </div>
            </div>
             -->
            <div class="col-md-12">
              <div id="hidden_div_payment_transfer" style="display: none;">
                <div class="subheading" style="margin-bottom: 10px !important">
                  <h3 class="box-subtitle">Pilih Bank</h3>
                </div>
                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1"><i class="fal fa-money-check text-primary"></i></span>
                  </div>

                   <select id="bank_id" class="form-control" name="bank_id">
                    <option></option>
                    <?php foreach ($banks as $key_bank => $bank) {?>
                      <option value="<?=$bank->id?>"><?= strtoupper($bank->bank_name)?></option>
                    <?php } ?>
                    </select>
                </div>
                <div class="form-group" id="detail-bank" style="margin-bottom: 30px !important"></div>
              </div>
            </div>

            <!-- <div class="col-md-12">
              <div id="hidden_div_payment_virtual" style="display: none;">
                <div class="subheading">
                  <h3 class="box-subtitle">Form Untuk Virtual Account</h3>
                </div>
                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1"><i class="fal fa-credit-card text-primary"></i></span>
                  </div>
                   <select class="form-control" name="shipping_by">
                      <option value="BCA">BCA</option>
                      <option value="BNI">BNI</option>
                      <option value="BRI">BRI</option>
                      <option value="Mandiri">Mandiri</option>
                    </select>
                </div>
              </div>
            </div> -->
            
          </div>
        <div class="row">
          <div class="col-md-12">
              <div id="hidden_div_payment_durasi" style="display: none;">
                <div class="subheading" style="margin-bottom: 10px !important">
                  <h3 class="box-subtitle" style="margin-bottom: 4px !important">Rencana Pelunasan</h3>
                  <small>Hanya sebagai pengingat Toko, tidak berimbas menjadi batas pembayaran Toko</small>
                </div>
                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1"><i class="fal fa-credit-card text-primary"></i></span>
                  </div>
                   <select class="form-control col-md-12" name="payment_durasi" id="payment_durasi">
                      <?php
                          foreach ($TOP as $row) {
                            echo "<option value='" . $row->duration . "'>" . $row->duration . ' ' . lang('hari') . "</option>";
                          }
                      ?>
                    </select>
                    <!-- <div class="row"> -->
                    <!-- </div> -->
                </div>
                <div class="input-group mb-3">
                      <input type="text" name="input_payment_durasi" id="input_payment_durasi" class="hidden col-md-12 form-control">
                      <label id="label-payment-duration" class="hidden" style="padding: 2%;">Hari</label>
                </div>

              </div>
            </div>
        </div>
        <?php if ($purchase->grand_total > 0) { ?>
        <hr>
        <div class="form-group">
          <div class="row">
            <!-- <div class="col-md-6">
              <label>Total pembayaran</label>
              <p class="h5" style="display: flex;">
                <span>Rp&nbsp;</span>
                <input class="jumlahBayar" type="text" id="jumlahBayar" data-total="<?=$purchase->grand_total?>" value="<?= number_format($purchase->grand_total, 0, ',', '.');?>" readOnly>
              </p>
              <a id="#salinRekening" onclick="salinJumlah()" href="javascript:void(0)" class="text-blue2"><i class="fal fa-copy mr-1"></i> Salin jumlah</a>
            </div>   -->
            <div class="col-md-12">
              <label>Yang perlu dibayar</label>
              <p class="h5" style="display: flex;">
                <span>Rp&nbsp;</span>
                <input class="jumlahBayar" type="text" id="jumlahSisa" data-total="<?=$purchase->grand_total - $purchase->paid?>" value="<?= number_format($purchase->grand_total - $purchase->paid, 0, ',', '.');?>" readOnly>
              </p>
              <a id="#salinRekening" onclick="salinSisa()" href="javascript:void(0)" class="text-blue2"><i class="fal fa-copy mr-1"></i> Salin jumlah</a>
            </div>
          </div>
        </div>

        <!-- <hr>
        <div class="form-group" id="kreditLimit" style="display:none;">
          <div class="row">
            <div class="col-12">
              <label>Sisa Kredit Limit Anda</label>
              <p class="h5">
                Rp <?= number_format($kredit_limit->kredit_limit - $debt->total, 0, ',', '.');?>
              </p>
            </div>  
          </div>
        </div> -->
        <?php }?>
        
      </div>
      <p id="catatan" class="font-size-md text-muted">
        <strong>Catatan:</strong>
        Bila Anda mengalami masalah atau kesulitan saat melakukan pembayaran, Anda bisa menghubungi kami di nomor <a href="tel:+628116065246" target="_blank">+62811 6065 246</a>
      </p>
      <div class="box p-box" id="divForm">
      <?php if($purchase->payment_method) { ?>
        <?php if($purchase->payment_method != 'kredit_pro') { ?>
        <div class="row mb-2" id="uploadForm">
          <input type="hidden" value="<?=$purchase->id?>" name="purchase_id" required>
        
          <div class="col-md-12">
            <h6>Unggah Bukti Pembayaran</h6>
            <form>
              <div class="custom-file">
                <label for="payment_receipt" class="custom-file-upload" style="margin:0;">
                  <i class="fas fa-copy"></i> Pilih File
                </label>
                <input type="file" accept=".jpg , .png , .JPEG" class="custom-file-input" id="payment_receipt" name="payment_receipt" style="width: 1px;">
                <span id='valHaha' style="font-size: 14px; color: #8B8D8E;"><i>Silakan unggah bukti pembayaran Anda</i></span>
              </div>
              <small class="font-italic text-danger">Disarankan : ekstensi file .jpg - ukuran < 15mb</small>
            </form>

          </div>
          <div class="col-md-12" id="nominalControl">
              <div class="form-group">
                <label>Nominal</label>
                <input type="text" min = "1" placeholder="<?= number_format($purchase->grand_total - $purchase->paid, 0, ',', '.');?>" min = "1"  class="form-control" id="input-payment-amounts" >
                <input type="text" class="" min = "1"  name="payment_nominal" id="input-payment-amounts-hidden"  style="display: none;" >
                <span id="tes-dio-alert" class="hidden">Tidak Boleh Lebih</span>
              </div>
          </div>
        </div>
        <?php }?>
      <?php }?>
        
        
        <input type="hidden" name="purchase_id" value="<?=$purchase->id?>">
        <input type="hidden" name="btn_value" id="btn_value" value="<?=$purchase->id?>">
        <?php if($purchase->payment_method && $purchase->payment_method != 'kredit_pro') { ?>
          <button type="submit" class="btn btn-success btn-block" id="unggah">Unggah Bukti Pembayaran</button>
        <?php } else { ?>
          <button type="submit" class="btn btn-primary btn-block" id="pending">Selesaikan</button>
        <?php }?>

      </div>
    </div>
  </section>
</form>
<!-- Modal Konfirmasi Order Cart -->
<div class="modal fade" id="confirmOrder" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title text-center" id="myModalLabel">Konfirmasi Pembayaran</h4>
      </div>
      <div class="modal-body">
        <div class="">
          <div class="row">
            <div class="col-12">
              <div class="card-body text-center">
              <?php if(!$purchase->payment_method) { ?>
                <p>Cara pembayaran tidak dapat diubah kembali. Apakah Anda yakin cara pembayaran yang dipilih telah sesuai?</p>
              <?php } else { ?>
                <p>Apakah file bukti dan nominal pembayaran telah sesuai?</p>
              <?php } ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Tidak</button>
        <button id="okAddress" class="btn btn-primary">Iya</button>
      </div>
    </div>
  </div>
</div>
<script>
    var tanpa_rupiah = document.getElementById('input-payment-amounts');
    tanpa_rupiah.addEventListener('keyup', function(e)
    {
      // Pake Rp
      tanpa_rupiah.value = formatRupiah(this.value, "Rp ");
      // tanpa_rupiah.value = formatRupiah(this.value);
    });
  
    tanpa_rupiah.addEventListener('keydown', function(event)
    {
      limitCharacter(event);
    });

  function formatRupiah(bilangan, prefix)
  {
    var number_string = bilangan.replace(/[^,\d]/g, '').toString(),
      split = number_string.split(','),
      sisa  = split[0].length % 3,
      rupiah  = split[0].substr(0, sisa),
      ribuan  = split[0].substr(sisa).match(/\d{1,3}/gi);
      
    if (ribuan) {
      separator = sisa ? '.' : '';
      rupiah += separator + ribuan.join('.');
    }
    
    rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    return prefix == undefined ? rupiah : (rupiah ? 'Rp ' + rupiah : '');
  }
  
  function limitCharacter(event)
  {
    key = event.which || event.keyCode;
    if ( key != 188 // Comma
       && key != 8 // Backspace
       && key != 17 && key != 86 & key != 67 // Ctrl c, ctrl v
       && key != 13 && key != 37 && key != 39
       && (key < 48 || key > 57) // Non digit
       // Dan masih banyak lagi seperti tombol del, panah kiri dan kanan, tombol tab, dll
      ) 
    {
      event.preventDefault();
      return false;
    }
  }

$("#input-payment-amounts").bind("keyup tap change paste", function() {
    $("#input-payment-amounts-hidden").val($(this).val());
    $("#input-payment-amounts-hidden").val($("#input-payment-amounts-hidden").val().replace('Rp ','').replace(/\./g , ''));
});
  </script>


<script>
// Add the following code if you want the name of the file appear on select
$(".custom-file-input").on("change", function() {
  var fileName = $(this).val().split("\\").pop();
  $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
});

$("input[type='file']").change(function () {
  $('#valHaha').text(this.value.replace(/C:\\fakepath\\/i, ''))
})

$('#payment_durasi').change(function(){
  if ($(this).val() == 'other') {
    $('#input_payment_durasi').removeClass('hidden');
    $('#label-payment-duration').removeClass('hidden');
    
  }else{
    $('#input_payment_durasi').addClass('hidden');
    $('#label-payment-duration').addClass('hidden');
  }
});

$("#input_payment_durasi").change(function(){
  if ($(this).val()>60) {
    alertCustom("Maximal 60 Hari", "danger");
    $(this).val(60);    
  }
});

$("#input_payment_durasi").keypress(function (e) {
  if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
      return false;
  }
});

  var modalKondisi = false;

  $("#payment_form").submit(function(e){
    let send = $(this).data("send") == "ready"
    if(!send){
      
      

      if (modalKondisi){
        $("#confirmOrder").modal("show")
      }
      e.preventDefault();
      return false;      
    }  
  });

  $("#okAddress").click(function(){
    $("#payment_form").data("send", "ready").submit()
    $(this).attr("disabled", "disabled")
    $(this).html("Memuat...")
  });

  $('#unggah').click(function(){
    $('#input-payment-amounts').attr({'min': 1});
    $('#payment_receipt').attr('required', true);
    $('#input-payment-amounts').attr('max',parseFloat('<?=$purchase->balance?>'));
    $('#btn_value').val('unggah');
    var min = 1;
      var max = parseFloat('<?=$purchase->balance?>');
      // Parcing Data Remove titik
      var nominal = $("#input-payment-amounts").val();
      if ($("#input-payment-amounts").val() != '') {
        var nominal = parseInt($("#input-payment-amounts").val().replace(/\./g , ''));
      }
      var nominalNull = $("#input-payment-amounts-hidden").val() == '';
      var nominalHidden = $("#input-payment-amounts-hidden").val();
      // Cek max nominal
      modalKondisi = true;
      if (nominalHidden > max){
          alertCustom("Nominal Tidak Boleh Melebihi Sisa Pembayaran", "danger");
          modalKondisi = false;
      }else if (nominalHidden == min){
          alertCustom("Nominal Harus Lebih Dari 1", "danger");
          modalKondisi = false;
      }else if (nominal == 0){
          alertCustom("Nominal Harus Lebih Dari 0", "danger");
          modalKondisi = false;
      }else if (nominalHidden == 0 ){
        alertCustom("Nominal Harus Lebih Dari 0", "danger");
        modalKondisi = false;
      }else if (nominalHidden == 1 ){
        alertCustom("Nominal Harus Lebih Dari 1", "danger");
        modalKondisi = false;
      }
  });

  $('#pending').click(function(){
    $('#payment_receipt').removeAttr('required');
    $('#input-payment-amounts').removeAttr('min');
    $('#input-payment-amounts').removeAttr('max');
    $('#btn_value').val('pending');
    modalKondisi = true;
  });


function copyNorek() {
  var copyText = document.getElementById("rekBank");
  copyText.select();
  document.execCommand("copy");
  alertCustom("Berhasil Disalin No Rek : " + copyText.value);
}

function getKreditPayment(){
  $.ajax({
    url: "<?= base_url(aksestoko_route("aksestoko/order/get_kredit_payment"))?>",
    method : "GET",
    dataType : "json",
    success : function(data){
      let price = formatMoney(data.grand_total)
      $("#jumlahBayar").val(price);
      $("#jumlahSisa").val(price);
    }
  })
}

$(document).ready(function(){
  
  <?php if($purchase->grand_total == 0){ ?>
    $('#pilih_payment').val("kredit").change();
    $('#pilih_payment option[value=""]').attr("disabled", true);
    $('#pilih_payment option[value="cash before delivery"]').attr("disabled", true).val("");
  <?php } ?>

  $('#pilih_payment').change(function(){
    // if () {}
    modalKondisi = false;
    // $("#payment_form").data("send", "not-ready");

    $("#divForm").slideDown("slow");
    $("#kreditLimit").hide();
    <?php if(!$purchase->id){ ?>
      $("#jumlahBayar").val(formatMoney($("#jumlahBayar").data("total")));
      $("#jumlahSisa").val(formatMoney($("#jumlahSisa").data("total")));  
    <?php } ?>
 
    if ($(this).val() == 'cash before delivery') {
      $('#hidden_div_payment_durasi').slideUp("slow");
      $('#hidden_div_payment_kredit').slideUp("slow");
      $('#hidden_div_payment_transfer').slideDown("slow");
      // $('#input-payment-amounts').val(parseFloat('<?=$purchase->grand_total?>'))
      // $('#input-payment-amounts').attr('readOnly', true);
      $("#uploadForm").slideDown("slow");
      $("#unggah").slideDown("slow");
      // $('#bank_id').attr('required', '');
      
      $("#pending").html("Selesaikan");  
    }else if ($(this).val() == 'cash on delivery') {
      $('#hidden_div_payment_durasi').slideUp("slow");
      $('#hidden_div_payment_kredit').slideUp("slow");
      $('#hidden_div_payment_transfer').slideUp("slow");
      $("#uploadForm").slideDown("slow");
      $("#unggah").slideDown("slow");
      $("#pending").html("Selesaikan");  
    }else if ($(this).val() == 'kredit') {
      $('#hidden_div_payment_kredit').hide();
      $('#hidden_div_payment_durasi').slideDown("slow");
      $('#hidden_div_payment_transfer').slideDown("slow");
      $('#input-payment-amounts').attr('readOnly', false);
      $('#input-payment-amounts').val(parseFloat('0'));
      $('#nominalControl').slideDown("slow");
      $("#kreditLimit").slideDown("slow");
      $("#pending").html("Selesaikan");
      // $('#bank_id').attr('required', '');
      // $("#payment_form").data("send", "ready");
      modalKondisi = true;
      <?php if(!$purchase->id){ ?>
        getKreditPayment()
      <?php } ?>
      
      <?php if ($purchase->payment_method != 'kredit_pro') {?>
        <?php if ($purchase->payment_method == 'kredit') { ?> 
          $("#uploadForm").show();
          $("#unggah").show();      
        <?php } else { ?>
          $("#uploadForm").hide();
          $("#unggah").hide();   
        <?php } ?>
      <?php }?>
      
    }else if ($(this).val() == ''){
      $('#input-payment-amounts').attr('readOnly', false);
      // $('#input-payment-amounts').val(parseFloat('0'));
      $('#hidden_div_payment_durasi').slideUp('2000');
      $('#hidden_div_payment_kredit').slideUp('2000');
      $('#hidden_div_payment_transfer').slideUp('2000');
      $("#detail-bank").html('');
      $("#uploadForm").hide();
      $("#divForm").hide();      
    }else if($(this).val()=='kredit_pro'){
      // console.log('asd');
      $("#pending").html("Selesaikan");
      $('#hidden_div_payment_durasi').slideUp("slow");
      $('#hidden_div_payment_kredit').slideUp("slow");
      $('#hidden_div_payment_transfer').slideUp("slow");
      // $("#unggah").slideDown("slow");
    }else{
      $('#input-payment-amounts').attr('readOnly', false);
      // $('#input-payment-amounts').val(parseFloat('0'));
      $('#hidden_div_payment_durasi').slideUp("slow");
      $('#hidden_div_payment_kredit').hide();
      $('#hidden_div_payment_transfer').hide();
      $("#detail-bank").html('');
      $("#uploadForm").hide();
      $("#divForm").hide();      
    }
    if ($(this).val()!='') {
      $('select[name="bank_id"]').change(function(){
        $.ajax({
          url: "<?= base_url(aksestoko_route("aksestoko/order/get_detail_bank/"))?>" + $(this).val(),
          method : "GET",
          dataType : "json",
          success : function(data){
            $("#detail-bank").html(data.output);
          }
        })
      }).change();
    }
  }).change();
});

function salinJumlah() {
  var copyText = document.getElementById("jumlahBayar");
  copyText.select();
  document.execCommand("copy");
  // alert("Berhasil Disalin Jumlah : " + copyText.value);
  counter++
  alertCustom("Jumlah Pembayaran telah disalin : " + copyText.value)
}

function salinSisa() {
  var copyText = document.getElementById("jumlahSisa");
  copyText.select();
  document.execCommand("copy");
  // alert("Berhasil Disalin Jumlah : " + copyText.value);
  counter++
  alertCustom("Sisa Pembayaran telah disalin : " + copyText.value)
}

        // Define the tour!
        var tour = {
            id: "payment",
            onClose: function(){
                callAjax();
            },
            onEnd : function(){
                        callAjax()
            },
            
            steps: [
                {
                    title: "Pilih Metode Pembayaran",
                    content: "Pilih Metode Pembayaran",
                    target: "select#pilih_payment",
                    placement: "top",
                },
                {
                    title: "Pilih Bank",
                    content: "Pilih Bank",
                    target: "select#bank_id",
                    placement: "top",
                },
                {
                    title: "Pilih Bank",
                    content: "Pilih Bank",
                    target: "select#shipping_by",
                    placement: "top",
                }
            ]
            
        };

        <?php
          if (!$guide->payment) {
        ?>
          hopscotch.startTour(tour);      
        <?php
          }
        ?>


        function callAjax(){
          isStart = false;
          $.ajax({
            url      : '<?= base_url(aksestoko_route('aksestoko/auth/set_guide/payment/1')); ?>',
            type     : 'GET',
          }) 
          
        }
        <?php if ($purchase->bank_id != null) { ?>
          $("#hidden_div_payment_transfer").show(function(){
              $("#bank_id").val("<?=$purchase->bank_id?>");
              <?php if (strtoupper(trim($supplier->cf2)) != 'SID') {?>
              $("#bank_id").attr("disabled", true);
              <?php } ?>
          });
          $.ajax({
            url: "<?= base_url(aksestoko_route("aksestoko/order/get_detail_bank/"))?>" + $("#bank_id").val(),
            method : "GET",
            dataType : "json",
            success : function(data){
              $("#detail-bank").html(data.output);
            }
          })
        <?php } ?>
        $('#input-payment-amounts').attr('max',parseFloat('<?=$purchase->balance?>'));

        <?php if ($purchase->payment_method != '') { ?>
          <?php if ($purchase->payment_method != 'kredit_pro') { ?>
            $('#pilih_payment').val("<?= $purchase->payment_method?>");
            $("#pilih_payment").attr("disabled", true);
          <?php }?>
          <?php if ($purchase->payment_method == 'kredit') { ?>
            $("#hidden_div_payment_durasi").show();
            <?php if ($purchase->payment_duration != 30 && $purchase->payment_duration != 15 && $purchase->payment_duration != 45) {?>
              $('#payment_durasi').val("other");
              $('#input_payment_durasi').removeClass('hidden');
              $('#input_payment_durasi').val("<?= $purchase->payment_duration?>");
              $("#input_payment_durasi").attr("disabled", true);
              $('#label-payment-duration').removeClass('hidden');
            <?php }else{ ?>
              $('#payment_durasi').val("<?= $purchase->payment_duration?>");
            <?php } ?>
            $("#payment_durasi").attr("disabled", true);
          <?php } ?>
        <?php } ?>   
 </script>