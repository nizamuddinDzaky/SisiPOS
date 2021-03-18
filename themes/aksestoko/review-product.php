<div class="container pt-4 pb-2">
  <h3 class="input-group">
    <a href="<?= base_url(aksestoko_route("aksestoko/order/view/")) . $order->id ?>" class="btn btn-back mr-3"><i class="fal fa-angle-left"></i></a>
    Ulasan Penerimaan Barang (<?= $order->cf1 ?>)
  </h3>
</div>

<section class="section-cart">
  <div class="container">
    <form id="reviewForm" action="<?= base_url(aksestoko_route('aksestoko/order/confirm_received')) ?>" data-send="not-ready" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="payment_method" value="<?= $order->payment_method ?>">
      <input type="hidden" name="purchase_id" value="<?= $order->id ?>">
      <input type="hidden" name="do_id" value="<?= $delivery->id ?>">
      <input type="hidden" name="do_ref" value="<?= $delivery->do_reference_no ?>">

      <div class="row">

        <div class="col-lg-8">

          <div class="box p-box mb-3">

            <!-- <div class="subheading">
                <h3 class="box-subtitle">Review Penerimaan Barang</h3>
              </div> -->
            <div class="row">
              <div class="col-6">
                <label class="title-review h6">No SPJ</label>
                <p class="caption-review"><?= $delivery->do_reference_no ?></p>
              </div>
              <div class="col-6">
                <label class="title-review h6">Status Pengiriman</label>
                <p class="caption-review text-<?= $object->__status($delivery->status)[1] ?>"><?= $object->__status($delivery->status)[0] ?></p>
              </div>
              <div class="col-6">
                <label class="title-review h6">Tanggal Dikirim</label>
                <p class="caption-review"><?= $my_controller->__convertDate($delivery->date) ?></p>
              </div>
              <div class="col-6">
                <label class="title-review h6">Dikirim Oleh</label>
                <p class="caption-review"><?= strlen($delivery->delivered_by) > 0 ? $delivery->delivered_by : "−" ?></p>
              </div>
              <div class="col-12">
                <label class="title-review h6">Barang Diterima</label>
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th style="width: 45%; text-align: center;">Nama Barang</th>
                      <th style="text-align: center;">Unit</th>
                      <th style="text-align: center;">Qty</th>
                      <th style="text-align: center;">Barang Baik</th>
                      <th style="text-align: center;">Barang Rusak</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $totalSent = 0;
                    foreach ($delivery->items as $i => $item) { ?>
                      <input type="hidden" name="product_id[]" value="<?= $item->product_id ?>">
                      <input type="hidden" name="product_name[]" value="<?= $item->product_name ?>">
                      <input type="hidden" name="product_code[]" value="<?= $item->product_code ?>">
                      <input type="hidden" name="quantity_received[]" value="<?= (int) $item->quantity_sent ?>">
                      <input type="hidden" name="delivery_item_id[]" value="<?= $item->id ?>">

                      <tr>
                        <td style="vertical-align:middle"><?= $item->product_code . " - " . $item->product_name ?></td>
                        <td style="text-align: center; vertical-align:middle"><?= convert_unit($item->product_unit_code) ?></td>
                        <td style="text-align: center; vertical-align:middle" class="quantity"><?= (int) $item->quantity_sent ?></td>
                        <td class="good">
                          <input type="text" value="<?= (int) $item->good_quantity ?>" class="form-control text-center good" name="good[]" id="good" readonly>
                        </td>
                        <td class="bad">
                          <input type="text" value="<?= (int) $item->bad_quantity ?>" class="form-control text-center bad" name="bad[]" id="bad">
                        </td>
                      </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <h6 class="title-review">Catatan</h6>
                  <textarea class="form-control" placeholder="Catatan" name="note" rows="5" id="comment"><?= $delivery->note ?></textarea>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <h6 class="title-review" id="title-unggah-spj">Unggah SPJ</h6>

                  <div class="custom-file">
                    <label for="upload-spj-hidden" class="custom-file-upload" style="margin:0;">
                      <i class="fas fa-copy"></i> Pilih File
                    </label>
                    <input id="upload-spj-hidden" style="width: 1px;" type="file" accept=".jpg , .png , .JPEG" class="custom-file-input" id="file" name="fileUpload" accept=".jpg,.jpeg">
                    <span id='valHaha' style="font-size: 14px; color: #8B8D8E;"><i>Silakan unggah SPJ</i></span>
                    <br>
                    <!--  <small id="notif-barang-jelek" class="hidden font-italic text-danger">Foto fisik SPJ Diperlukan Jika ada barang rusak</small> -->
                  </div>

                  <small class="font-italic text-danger">Disarankan : ekstensi file .jpg - ukuran < 15mb</small> </div> </div> </div> </div> </div> <div class="col-lg-4 col-lg-last">
                      <div class="box p-box box-checkout-summary">
                        <h3 class="box-subtitle">Ringkasan Barang</h3>
                        <div class="row spacing-mobile">
                          <div class="col-sm-12 col-md-6 col-lg-6">
                            <div class="form-group">
                              <label>Jumlah Baik</label>
                              <p><span id="good_total">0</span></p>
                            </div>
                          </div>
                          <div class="col-sm-12 col-md-6 col-lg-6">
                            <div class="form-group">
                              <label class="title-review">Jumlah Rusak</label>
                              <p><span id="bad_total" class="caption-review">0</span></p>
                            </div>
                          </div>
                          <div class="col-12 text-left">
                            <div class="form-group">
                              <label class="title-review">Jumlah Barang</label>
                              <p class="h5"><span id="all_total" class="caption-review">0</span></p>
                            </div>
                          </div>

                        </div>
                      </div>
                      <?php //if($order->payment_method == 'cash on delivery'){
                      ?>
                      <!-- <div class="box p-box box-checkout-summary">
              <h6>Unggah Bukti Pembayaran</h6>
              <div class="custom-file">
                <label for="payment_receipt" class="custom-file-upload" style="margin:0;">
                  <i class="fas fa-copy"></i> Pilih File
                </label>
                <input type="file" accept=".jpg , .png , .JPEG" class="custom-file-input" id="payment_receipt" name="payment_receipt" style="width: 1px;">
                <span id='valHaha' style="font-size: 14px; color: #8B8D8E;"><i>Silakan unggah bukti pembayaran Anda</i></span>
              </div>
              <small class="font-italic text-danger">Disarankan : ekstensi file .jpg - ukuran < 15mb</small>
              <input type="text" class="" min = "1"  name="payment_nominal" id="input-payment-amounts-hidden"  style="display: none;" value="<?= $order->grand_total ?>">
            </div> -->
                      <?php //}
                      ?>
                      <div class="box p-box box-checkout-summary">
                        <button id="terima-barang-button" type="submit" class="tombol-review btn btn-success btn-block">Terima Barang</button>
                      </div>
                </div>
              </div>
    </form>
  </div>
</section>

<!-- Modal Konfirmasi Received -->
<div class="modal fade" id="confirmReceived" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title text-center" id="myModalLabel">Konfirmasi Penerimaan</h4>
      </div>
      <div class="modal-body">
        <div class="">
          <div class="row">
            <div class="col-12">
              <div class="card-body text-center">
                <p>Apakah ulasan penerimaan barang telah sesuai?</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Tidak</button>
        <button id="okReceived" class="btn btn-primary">Iya</button>
      </div>
    </div>
  </div>
</div>


<script>
  $(document).ready(function() {
    $("#bad").on('change keyup paste', function() {
      if ($('#bad').val() > 0) {
        $("#upload-spj-hidden").attr('required', true);
        // $("#notif-barang-jelek").removeClass('hidden');
      }
      if ($('#bad').val() == 0) {
        $("#upload-spj-hidden").attr('required', false);
        // $("#notif-barang-jelek").addClass('hidden');
      }
    });
  });
</script>

<script>
  // Add the following code if you want the name of the file appear on select
  $(".custom-file-input").on("change", function() {
    var fileName = $(this).val().split("\\").pop();
    $(this).siblings("#valHaha").addClass("selected").html(fileName);
  });
</script>

<script>
  $("#reviewForm").submit(function(e) {
    let send = $(this).data("send") == "ready"

    if (!send) {
      $("#confirmReceived").modal("show")
      e.preventDefault();
      return false;
    }
  });

  $("#okReceived").click(function() {
    $("#reviewForm").data("send", "ready").submit()
    $(this).attr("disabled", "disabled")
    $(this).html("Memuat...")
  });

  var old_value;
  $(document).on("focus", 'input.good', function() {
    old_value = $(this).val();
  }).on("change", 'input.good', function() {
    var new_value = $(this).val() ? $(this).val() : 0;
    let parent_tr = $(this).parent().parent();
    let quantity = parseInt(parent_tr.find("td.quantity").html())
    let badSelector = parent_tr.find("td.bad").find("input.bad")

    if (!is_numeric(new_value)) {
      $(this).val(old_value);
      // return;
    } else if (new_value > quantity) {
      $(this).val(quantity);
      // return;
    } else if (new_value <= 0) {
      $(this).val(0);
      // return
    }

    badSelector.val(quantity - $(this).val())

    setSummary()

    return
  });

  $(document).on("focus", 'input.bad', function() {
    old_value = $(this).val();
  }).on("change", 'input.bad', function() {
    var new_value = $(this).val() ? $(this).val() : 0;
    let parent_tr = $(this).parent().parent();
    let quantity = parseInt(parent_tr.find("td.quantity").html())
    let goodSelector = parent_tr.find("td.good").find("input.good")

    if (!is_numeric(new_value)) {
      $(this).val(old_value);
      // return;
    } else if (new_value > quantity) {
      $(this).val(quantity);
      // return;
    } else if (new_value <= 0) {
      $(this).val(0);
      // return
    }

    goodSelector.val(quantity - $(this).val())

    setSummary()

    return
  });

  function setSummary() {
    var total = 0;
    $("input.bad").each(function(index) {
      total += parseInt(this.value, 10) || 0;
    });

    $("#bad_total").html(total)

    if(total > 0) {
      $("#title-unggah-spj").html(`Unggah SPJ <span style="color: red">*</span>`)
      $('#upload-spj-hidden').attr('required', 'required')
    } else {
      $("#title-unggah-spj").html("Unggah SPJ")
      $('#upload-spj-hidden').removeAttr('required')
    }

    var totalG = 0;
    $("input.good").each(function(index) {
      totalG += parseInt(this.value, 10) || 0;
    });

    $("#good_total").html(totalG)

    $("#all_total").html(total + totalG)
  }

  function is_numeric(n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
  }


  setSummary()
</script>
<!-- 
     <script>
        // Define the tour!
        var tour = {
            id: "cart",
            onClose: function(){
                localStorage.setItem('cart',true);
                //  console.log('aku');
            },
            steps: [
                {
                    title: "Tambah quantity",
                    content: "Menambah jumlah pesanan anda",
                    target: "span.input-number-increment.inc.button",
                    placement: "top",
                },
                {
                    title: "Mengurangi jumlah quantity",
                    content: "Mengurangi jumlah pesanan anda",
                    target: "span.input-number-decrement.dec.button",
                    placement: "top",
                },
                {
                    title: "Hapus Pesanan",
                    content: "Menghapus pesanan anda",
                    target: "i.fal.fa-trash-alt.delete-cart",
                    placement: "top",
                },
                {
                    title: "Update Cart",
                    content: "Update cart terlebih dahulu setelah edit quantity pesanan anda",
                    target: "a#updateCart",
                    placement: "top",
                },
                {
                    title: "Masukan Kode Promo",
                    content: "Ketikan kode promo di form berikut",
                    target: "input#promoCode",
                    placement: "top",
                },
                {
                    title: "Apply Promo Anda",
                    content: "Tekan tombol Apply",
                    target: "button#btnPromo",
                    placement: "left",
                },
                {
                    title: "Pergi ke Halaman Checkout",
                    content: "Pergi ke Halaman Checkout",
                    target: "a.btn.btn-success.btn-block",
                    placement: "top",
                },
               
            ]
        };
        
        if(!localStorage.getItem('tour-homepage')){
            // Start the tour!
            hopscotch.startTour(tour);
        }
    </script> -->