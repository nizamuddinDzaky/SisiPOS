<style>
  .logo-payment {
    max-width: 40px;
  }

  .button-payment {
    font-size: 12px;
  }

  .button-payment:hover {
    background-color: #B20838;
    color: #fff;
    cursor: pointer;
  }

  .selected {
    background-color: #B20838;
    color: #fff;
  }

  a#salinRekening {
    font-size: 14px;
  }

  input.rekBank {
    border: 0 !important;
    background: none !important;
    font-weight: 700 !important;
    color: black !important;
    width: 100% !important;
  }

  .small-btn-payment {
    font-size: 12px;
  }

  .font-13 {
    font-size: 13px;
  }

  .font-13>label {
    font-size: 13px;
  }

  .font-13>h6 {
    font-size: 13px;
  }

  .footer-payment {
    margin-top: 10px;
  }

  .button_payment_right {
    max-width: 100px;
    float: right;
    display: block;
  }

  .detail-bank {
    margin-top: 15px;
    margin-bottom: 15px;
  }

  .table {
    margin-bottom: 5px;
  }

  .py-3 {
    padding-bottom: 0 !important;
  }

  .section-payment {
    margin-top: 4.5rem;
  }

  .title-methode-payment {
    line-height: 40px;
  }

  .price-methode-payment {
    line-height: 40px;
    text-align: right;
  }

  /* .price-kreditpro{
    line-height: 20px;
  } */
  .price-kreditpro>label {
    font-weight: 400;
  }

  .price-kredit>label {
    font-weight: 400;
  }

  .see-more-payment {
    font-size: 12px;
  }

  ul.tabs li {
    background: none;
    color: #222;
    display: inline-block;
    padding: 10px 15px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
  }

  @media (max-width: 768px) {
    .section-payment {
      margin-top: 3.5rem;
    }
  }

  @media (max-width: 450px) {
    .title-methode-payment {
      font-size: 11px !important;
      line-height: 26px;
    }

    .price-methode-payment {
      font-size: 11px !important;
      line-height: 26px;
      text-align: right;
    }

    .price-kreditpro {
      font-size: 11px !important;
    }

    .price-kreditpro>label {
      font-size: 11px !important;
    }

    .price-kredit {
      font-size: 11px !important;
    }

    .price-kredit>label {
      font-size: 11px !important;
    }

    .logo-payment {
      max-width: 17px;
    }

    .see-more-payment {
      font-size: 10px;
    }

    .detail-payment {
      font-size: 11px;
    }

    .font-13 {
      font-size: 11px;
    }

    .font-13>label {
      font-size: 11px;
    }

    .font-13>h6 {
      font-size: 11px;
    }

    .title-mobile {
      font-size: 11px !important;
    }

    .small-btn-payment {
      font-size: 10px;
    }

    ul.tabs li {
      padding: 5px 10px !important;
    }
  }

  @media (max-width: 350px) {
    .title-methode-payment {
      font-size: 9px !important;
      line-height: 26px;
    }

    .price-methode-payment {
      font-size: 9px !important;
      line-height: 26px;
      text-align: right;
    }

    .price-kreditpro {
      font-size: 9px !important;
    }

    .price-kreditpro>label {
      font-size: 9px !important;
    }

    .price-kredit {
      font-size: 9px !important;
    }

    .price-kredit>label {
      font-size: 9px !important;
    }

    .see-more-payment {
      font-size: 9px;
    }

    .detail-payment {
      font-size: 9px;
    }

    .font-13 {
      font-size: 9px;
    }

    .font-13>label {
      font-size: 9px;
    }

    .font-13>h6 {
      font-size: 9px;
    }

    .title-mobile {
      font-size: 9px !important;
    }

    .small-btn-payment {
      font-size: 9px;
    }
  }

  @media (max-width: 320px) {
    .m-hide {
      display: none;
    }
  }
</style>

<form action="<?= $url_save ?>" method="POST" data-send="not-ready" id="payment_form" enctype="multipart/form-data">
  <input type="hidden" name="uuid" value="<?=getUuid()?>">
  <input type="hidden" value="<?= $purchase->id ?>" name="purchase_id" required>
  <input type="hidden" value="" name='bank_id' id='id-bank'>
  <input type="hidden" name="btn_value" id="btn_value" value="">
  <input type="hidden" value="" name='payment_method' id="payment_method">

  <section class="section-cover-red section-payment">
    <div class="container container-sm">
      <ol class="breadcrumb">
        <li>
          <a href="<?= $purchase->id ? base_url(aksestoko_route("aksestoko/order/view/")) . $purchase->id : base_url(aksestoko_route("aksestoko/order/checkout")) ?>" class="active"><i class="fal fa-angle-left"></i> Kembali</a></li>
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
          <?php
          if ($purchase->payment_method == 'cash on delivery') {
            $purchase_payment_method = 'Bayar Di Tempat';
          } elseif ($purchase->payment_method == 'cash before delivery') {
            $purchase_payment_method = 'Bayar Sebelum Dikirim';
          } elseif ($purchase->payment_method == 'kredit_pro') {
            $purchase_payment_method = 'KreditPro';
          } elseif ($purchase->payment_method == 'kredit') {
            $purchase_payment_method = 'Tempo Dengan Distributor';
          } elseif ($purchase->payment_method == 'kredit_mandiri'){
            $purchase_payment_method = 'Kredit Mandiri';
          }
          ?>
          <span><?= $purchase->id && $purchase->payment_method != 'kredit_pro' ? 'Metode Pembayaran Anda : <b>' . $purchase_payment_method . '</b>' : 'Silahkan Pilih Metode Pembayaran' ?></span>
        </div>
        <?php
        if ($purchase->id && $purchase->payment_method != 'kredit_pro') {
          $this->data['payment_method'] = $purchase->payment_method;
        } else {
          if ($purchase->payment_status == 'reject' && $purchase->payment_method == 'kredit_pro') {
            foreach ($payment_methods_reject as $key_payment_methods => $payment_methods_reject) {
              $this->data['payment_method'] = $payment_methods_reject->value;
              $this->load->view('aksestoko/payments/' . $payment_methods_reject->value, $this->data);
            }
          } else {
            foreach ($payment_methods as $key_payment_methods => $payment_method) {
              $this->data['payment_method'] = $payment_method->value;
              $this->load->view('aksestoko/payments/' . $payment_method->value, $this->data);
            }
          }
        }
        ?>

        <?php if ($purchase->id && $purchase->payment_method != 'kredit_pro' ) {
          $this->load->view('aksestoko/payments/' . $this->data['payment_method'], $this->data);
        } ?>
      </div>
      <?php if ($purchase->id && $purchase->payment_method != 'kredit_pro'){?>
        <p id="catatan" class="font-size-md text-muted">
          <strong>Catatan:</strong>
          Bila Anda mengalami masalah atau kesulitan saat melakukan pembayaran, dapat menggunakan Live Chat yang terdapat di pojok kanan bawah pada Halaman Ini.
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
          <button type="submit" class="btn btn-success btn-block" id="unggah">Unggah Bukti Pembayaran</button>

        </div>
    </div>
  <?php } ?>
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
                <?php if (!$purchase->payment_method) { ?>
                  <p class="mb-0">Metode Pembayaran : </p>
                  <h6 id="str-payment-method"></h6>
                  <p>Cara pembayaran tidak dapat diubah kembali. Apakah Anda yakin cara pembayaran yang dipilih telah sesuai?</p>
                <?php } else if ($purchase->payment_status == 'reject' && ($purchase->payment_method == 'kredit_pro' || $purchase->payment_method == 'kredit_mandiri')) { ?>
                  <p class="mb-0">Metode Pembayaran : </p>
                  <h6 id="str-payment-method"></h6>
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
  $(document).ready(function() {
    var modalKondisi = false;
    $("#okAddress").click(function() {
      $("#payment_form").data("send", "ready").submit()
      $(this).attr("disabled", "disabled")
      $(this).html("Memuat...")
    });
    $("#payment_form").submit(function(e) {
      let send = $(this).data("send") == "ready"
      if (!send) {



        if (modalKondisi) {
          $("#confirmOrder").modal("show")
        }
        e.preventDefault();
        return false;
      }
    });

    $('.bank-list').click(function(params) {
      var bank_id = $(this).data('idbank');
      id_payment_method = $(this).data('id-payment-method');
      $('.bank-list-' + id_payment_method).map(function() {
        $(this).removeClass('selected');
      });
      var current = $(this);
      current.addClass("selected");

      if (bank_id == undefined) {
        $("#detail-bank-" + id_payment_method).html('');
      } else {
        $.ajax({
          url: "<?= base_url(aksestoko_route("aksestoko/order/get_detail_bank/")) ?>" + `${bank_id}/${id_payment_method}`,
          method: "GET",
          dataType: "json",
          success: function(data) {
            $("#detail-bank-" + id_payment_method).html(data.output);
          }
        });
      }
      $('#id-bank').val(bank_id);
    });

    $('.pending').click(function(params) {
      modalKondisi = true;
      $('#btn_value').val('pending');
      $('#payment_method').val($(this).data('payment-method'));

      metode_pembayaran = $(this).data('str-payment-method');
      $('#str-payment-method').html(metode_pembayaran);
    })
    $('#unggah').click(function(params) {
      modalKondisi = true;
      $('#btn_value').val('unggah');
      $('#input-payment-amounts').attr('max', parseFloat('<?= $purchase->balance ?>'));
      var min = 1;
      var max = parseFloat('<?= $purchase->balance ?>');
      var nominalHidden = $("#input-payment-amounts-hidden").val();
      if (nominalHidden > max) {
        alertCustom("Nominal Tidak Boleh Melebihi Sisa Pembayaran", "danger");
        modalKondisi = false;
      } else if (nominalHidden == min) {
        alertCustom("Nominal Harus Lebih Dari 1", "danger");
        modalKondisi = false;
      } else if (nominal == 0) {
        alertCustom("Nominal Harus Lebih Dari 0", "danger");
        modalKondisi = false;
      } else if (nominalHidden == 0) {
        alertCustom("Nominal Harus Lebih Dari 0", "danger");
        modalKondisi = false;
      } else if (nominalHidden == 1) {
        alertCustom("Nominal Harus Lebih Dari 1", "danger");
        modalKondisi = false;
      }
    })
    <?php if ($purchase->id) { ?>
      $('.bank-list').click();
    <?php } ?>


    $('.order-details-check a').click(function(e) {
      e.preventDefault();

      var $this = $('this').prev('div.collapse');
      var expanded = $(this).attr("aria-expanded");

      if (expanded == 'true') {
        $this.removeClass('in');
      } else {
        $('.order-details-check a').map(function() {
          var x = $(this).attr("aria-expanded");
          if (x == 'true') {
            $(this).click();
          }
        });
        // $('div.collapse').not($this).removeClass('in').next('.order-details-check').find('a').attr("aria-expanded","false");
        $this.addClass('in');
      }
    });
    // $('.order-details-check a.tutup').click({
    //   $('this').parent().prev('div.collapse').remoceClass('in');
    //   $(this).removeClass('tutup');
    // });


  });

  function copyNorek(type) {
    var copyText = document.getElementById(`rekBank${type}`);
    copyText.select();
    document.execCommand("copy");
    alertCustom("Berhasil Disalin No Rek : " + copyText.value);
  }

  var tanpa_rupiah = document.getElementById('input-payment-amounts');
  tanpa_rupiah.addEventListener('keyup', function(e) {
    // Pake Rp
    tanpa_rupiah.value = formatRupiah(this.value, "Rp ");
    // tanpa_rupiah.value = formatRupiah(this.value);
  });

  tanpa_rupiah.addEventListener('keydown', function(event) {
    limitCharacter(event);
  });

  function formatRupiah(bilangan, prefix) {
    var number_string = bilangan.replace(/[^,\d]/g, '').toString(),
      split = number_string.split(','),
      sisa = split[0].length % 3,
      rupiah = split[0].substr(0, sisa),
      ribuan = split[0].substr(sisa).match(/\d{1,3}/gi);

    if (ribuan) {
      separator = sisa ? '.' : '';
      rupiah += separator + ribuan.join('.');
    }

    rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    return prefix == undefined ? rupiah : (rupiah ? 'Rp ' + rupiah : '');
  }

  function limitCharacter(event) {
    key = event.which || event.keyCode;
    if (key != 188 // Comma
      &&
      key != 8 // Backspace
      &&
      key != 17 && key != 86 & key != 67 // Ctrl c, ctrl v
      &&
      key != 13 && key != 37 && key != 39 &&
      (key < 48 || key > 57) // Non digit
      // Dan masih banyak lagi seperti tombol del, panah kiri dan kanan, tombol tab, dll
    ) {
      event.preventDefault();
      return false;
    }
  }

  $("#input-payment-amounts").bind("keyup tap change paste", function() {
    $("#input-payment-amounts-hidden").val($(this).val());
    $("#input-payment-amounts-hidden").val($("#input-payment-amounts-hidden").val().replace('Rp ', '').replace(/\./g, ''));
  });

  $('#unggah').click(function() {
    $('#input-payment-amounts').attr({
      'min': 1
    });
    $('#payment_receipt').attr('required', true);
    $('#input-payment-amounts').attr('max', parseFloat('<?= $purchase->balance ?>'));
    $('#btn_value').val('unggah');
    var min = 1;
    var max = parseFloat('<?= $purchase->balance ?>');
    // Parcing Data Remove titik
    var nominal = $("#input-payment-amounts").val();
    if ($("#input-payment-amounts").val() != '') {
      var nominal = parseInt($("#input-payment-amounts").val().replace(/\./g, ''));
    }
    var nominalNull = $("#input-payment-amounts-hidden").val() == '';
    var nominalHidden = $("#input-payment-amounts-hidden").val();
    // Cek max nominal
    modalKondisi = true;
    if (nominalHidden > max) {
      alertCustom("Nominal Tidak Boleh Melebihi Sisa Pembayaran", "danger");
      modalKondisi = false;
    } else if (nominalHidden == min) {
      alertCustom("Nominal Harus Lebih Dari 1", "danger");
      modalKondisi = false;
    } else if (nominal == 0) {
      alertCustom("Nominal Harus Lebih Dari 0", "danger");
      modalKondisi = false;
    } else if (nominalHidden == 0) {
      alertCustom("Nominal Harus Lebih Dari 0", "danger");
      modalKondisi = false;
    } else if (nominalHidden == 1) {
      alertCustom("Nominal Harus Lebih Dari 1", "danger");
      modalKondisi = false;
    }
  });

  $("input[type='file']").change(function() {
    $('#valHaha').text(this.value.replace(/C:\\fakepath\\/i, ''))
  })
</script>
<script>
  (function(w, d, u) {
    var s = d.createElement('script');
    s.async = true;
    s.src = u + '?' + (Date.now() / 60000 | 0);
    var h = d.getElementsByTagName('script')[0];
    h.parentNode.insertBefore(s, h);
  })(window, document, 'https://cdn.bitrix24.id/b11907515/crm/site_button/loader_3_wclev2.js');
</script>