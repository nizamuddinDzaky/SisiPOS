<div class="container pt-4 pb-2">
  <h3 class="input-group">
    <a href="<?= base_url(aksestoko_route("aksestoko/order")) ?>" class="btn btn-back mr-3"><i class="fal fa-angle-left"></i></a>
    Detail Pemesanan
  </h3>
</div>

<?php
$now = time();
$end_date = strtotime(date('Y-m-d', strtotime($order->payment_deadline)));
$datediff = $now - $end_date;
$duration = round($datediff / (60 * 60 * 24));

if ($duration < -3 && $duration > -7) {
  $bg = 'bg-warning';
} else if ($duration > -3) {
  $bg = 'bg-danger';
} else {
  $bg = 'bg-info';
}
?>

<section class="section-checkout">
  <div class="container">
    <?php if ($order->payment_status != 'paid' && $order->status != 'canceled' && in_array($order->payment_method, array_merge($array_payment_method, ["kredit_pro"])) && $order->payment_deadline != null) { ?>
      <div class="box-header <?= $bg ?> p-3 mb-3 mt-2">
        <span class="text-white"><i>Sisa Durasi Waktu Pembayaran :</i> </span> <strong class="text-white"><?= $duration ?> Hari</strong>
      </div>
    <?php } ?>

    <?php if ($order->grand_total == 0 && $sale->sale_status != 'closed') { ?>
      <div class="box-header bg-warning p-3 mb-3 mt-2">
        <span class="text-white">Seluruh harga dan total pembayaran akan ditampilkan ketika Sales/Distibutor telah mengonfirmasi</span>
      </div>
    <?php } ?>
    <?php if ($sale->is_updated_price == 1) { ?>
      <div class="box-header bg-info p-3 mb-3 mt-2">
        <div class="row justify-content-between">
          <div class="col-auto">
            <?php if ($sale->charge != 0 || $order->total_discount >= 0) { ?>
              <span class="text-white">Harga pesanan telah diperbarui oleh distributor. Apakah Anda menyetujuinya?</span>
            <?php } ?>
          </div>
          <div class="col-auto" style="text-align: right">
            <a data-toggle="modal" href="#div-confirm" class="btn-sm btn-success py-2 px-3" style="font-size: 12px; border-radius: 40px;">Konfirmasi Harga Pesanan</a>
            <a data-toggle="modal" href="#cancelOrder" class="btn-sm btn-danger py-2 px-3" id="btn-cancel-update-price" style="font-size: 12px; border-radius: 40px;">Batalkan Pesanan</a>
          </div>
        </div>
      </div>
    <?php } ?>
    <div class="row">
      <?php if ($sale->charge != 0 || $order->status == "canceled") { ?>
        <div class="col-lg-12 font-weight-bold">
          <div class="box p-box mb-3">
            <h3 class="box-subtitle">Informasi</h3>
            <?php if ($order->status == "canceled" && $order->payment_method != 'kredit_pro') { ?>
              <p class="mb-0">Pemesanan telah dibatalkan oleh <?= $order->created_by != $order->updated_by ? 'distributor' : 'toko' ?>. <?php if (strlen($sale->reason)) { ?> Dengan alasan : <?= $this->sma->decode_html($sale->reason); ?> <?php } ?> </p>
            <?php } else if($order->status == "canceled" && $order->payment_method == 'kredit_pro'){?>
              <p class="mb-0">Pemesanan telah dibatalkan, karena pengajuan kredit ditolak</p>
            <?php }else { ?>
              <p class="mb-0">
                Total Pembayaran telah diperbarui oleh distributor menjadi Rp <?= number_format($order->grand_total, 0, ',', '.'); ?>.
                <?php if ($order->charge != 0) { ?>
                  <?= $order->charge > 0 ? 'Biaya lain-lain' : 'Potongan harga' ?>
                  sebesar Rp <?= number_format((abs($order->charge)), 0, ',', '.'); ?>.
                <?php } ?>
                <?php if ($order->correction_price != 0) { ?>
                  <?= $order->correction_price > 0 ? 'Penambahan harga' : 'Pengurangan harga' ?>
                  sebesar Rp <?= number_format((abs($order->correction_price)), 0, ',', '.'); ?>.
                <?php } ?>
                <?php if (strlen($sale->reason)) { ?> Dengan alasan : <?= $this->sma->decode_html($sale->reason); ?>
                <?php } ?>
                <?php if ($order->charge_third_party != 0) { ?>
                  Biaya Kredit : Rp <?= number_format((abs($order->charge_third_party)), 0, ',', '.'); ?>
                <?php   } ?>
              </p>
            <?php } ?>
          </div>
        </div>
      <?php } ?>
      <div class="col-lg-5 custom-checkout">
        <div class="box p-box box-checkout-summary">
          <h3 class="box-subtitle">Detail Pesanan</h3>
          <!-- <div class="box p-box mb-3"> -->
          <div class="row">
            <!-- <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="form-group">
                  <label>ID Pemesanan</label>
                  <p class="h6"><?= $order->reference_no ?></p>
                </div>
              </div> -->
            <div class="col-lg-6 col-md-6 col-sm-12">
              <div class="form-group">
                <label>ID Pemesanan</label>
                <p class="h6"><?= $order->cf1 ?></p>
              </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12">
              <div class="form-group">
                <label>ID Bisnis Kokoh</label>
                <p class="h6"><?= str_replace("IDC-", "", $company->cf1) ?></p>
              </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12">
              <div class="form-group">
                <label>Tanggal Pemesanan</label>
                <p class="h6"><?= $my_controller->__convertDate($order->date) ?></p>
              </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12">
              <div class="form-group">
                <label>Ekspektasi Tanggal Pengiriman</label>
                <p class="h6"><?= $my_controller->__convertDate($order->shipping_date) ?></p>
              </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12">
              <div class="form-group">
                <label>Kode Distributor</label>
                <p class="h6"><?= $distributor->cf1  && is_numeric($distributor->cf1) ? str_pad($distributor->cf1, 10, '0', STR_PAD_LEFT) : $order->supplier_id ?></p>
              </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12">
              <div class="form-group">
                <label>Nama Distributor</label>
                <p class="h6"><?= $order->supplier ?></p>
              </div>
            </div>
            <!-- <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="form-group">
                  <label>Jasa Pengiriman</label>
                  <p class="h6"><?= $order->shipping_by ?></p>
                </div>
              </div> -->
            <div class="col-lg-6 col-md-6 col-sm-12">
              <div class="form-group">
                <label>Cara Pengiriman</label>
                <p class="h6"><?= $object->__status($order->delivery_method)[0] ?></p>
              </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12">
              <div class="form-group">
                <label>Cara Pembayaran</label>
                <p class="h6"><?= $object->__status($order->payment_method)[0] ?></p>
              </div>
            </div>
            <?php if ($order->payment_type != '' && $order->payment_type != null) { ?>
              <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="form-group">
                  <label>Jenis Pembayaran</label>
                  <p class="h6"><?= $order->payment_type ?></p>
                </div>
              </div>
            <?php } ?>
          </div>
          <hr class="mt-0 mb-2">
          <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-12">
              <div class="form-group">
                <label>Status Pesanan</label>
                <p class="h6 text-<?= $object->__status($order->status)[1] ?> mb-3"><?= $object->__status($order->status)[0] ?></p>
                <?php if ($order->status == "received" && $order->payment_status == "paid") { ?>
                  <ul class="order-details-download p-0 mt-3" style="list-style-type:none;">
                    <!-- <li class="m-0"><a href="<?= base_url(aksestoko_route("aksestoko/order/invoice/")) . $sale->id ?>"><i class="fal fa-download mr-1"></i>Unduh Invoice<i class="fal fa-angle-right"></i></a></li> -->
                    <li class="m-0"><a href="<?= base_url(aksestoko_route("aksestoko/order/invoice/")) . $sale->id . "/" . $order->id ?>"><i class="fal fa-download mr-1"></i>Unduh Invoice<i class="fal fa-angle-right"></i></a></li>
                  </ul>
                <?php } else if ($sale->sale_status == "pending" && $order->status == "ordered") { ?>
                  <a data-toggle="modal" href="#cancelOrder" class="btn-sm btn-danger py-2 px-3" id="btn-cancel-order" style="font-size: 12px; border-radius: 40px;">Batalkan Pesanan</a>
                <?php } ?>
              </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12">
              <div class="form-group">
                <label>Status Pembayaran</label>
                <?php
                // if($order->payment_status != 'pending'){
                //   $param = ($order->payment_method == 'kredit_pro'? (($order->grand_total - $order->paid) > 0 ? 2 : 1) : 0);
                // }else{
                $param = $order->payment_method=='kredit_pro' ? 1 : 0;
                // }
                ?>
                <p class="h6 text-<?= $object->__status($order->payment_status, $param)[1] ?> mb-3">
                  <?= $object->__status($order->payment_status, $param)[0] ?>
                </p>
                <?php if ($distributor->cf2 == 'SID') {
                  if ($sale->is_updated_price == 2) { ?>
                    <?php if (($order->grand_total > 0) && ($order->grand_total > $payment_total && $order->status != "canceled") && $payment_pending) {
                      if (in_array($order->payment_method, $array_payment_method)) {
                        if ($order->status == "received") { ?>
                          <a id="konfirm_pembayaran" href="<?= base_url($url_payment) ?>/<?= $order->id ?>" class="btn-sm btn-success py-2 px-3 " style="font-size: 11px; border-radius: 40px;">Selesaikan Pembayaran</a>
                        <?php }
                      } else if (($order->status == 'confirmed' || $order->status == 'received' || $order->status == 'partial') && $order->payment_method != 'kredit_pro') { if($order->payment_method == 'kredit_mandiri'){} else { ?>
                        <a id="konfirm_pembayaran" href="<?= base_url(aksestoko_route($url_payment)) ?>/<?= $order->id ?>" class="btn-sm btn-success py-2 px-3 " style="font-size: 11px; border-radius: 40px;">Selesaikan Pembayaran</a>
                      <?php }} elseif ($order->payment_method == 'kredit_pro' && ($order->status == 'confirmed' || $order->status == 'received') && ($order->payment_status == 'pending' || $order->payment_status == 'reject')) { ?>
                        <a id="konfirm_pembayaran" href="<?= base_url(aksestoko_route($url_payment)) ?>/<?= $order->id ?>" class="btn-sm btn-success py-2 px-3 " style="font-size: 11px; border-radius: 40px;">Ajukan Kredit</a>
                      <?php } ?>
                    <?php } ?>
                  <?php
                  }
                  ?>

                <?php
                } else if (in_array($distributor->cf2, ['BIG', 'JBU', 'BPP'])) {
                  echo "";
                } else { ?>
                  <?php
                  if (($order->grand_total > 0) && ($order->grand_total > $payment_total && $order->status != "canceled") && $payment_pending) {
                    if (in_array($order->payment_method, $array_payment_method)) {
                      if ($order->status == "received") {
                  ?>
                        <a id="konfirm_pembayaran" href="<?= base_url($url_payment) ?>/<?= $order->id ?>" class="btn-sm btn-success py-2 px-3 " style="font-size: 11px; border-radius: 40px;">Selesaikan Pembayaran</a>
                      <?php
                      }
                    } else if (($order->status == 'confirmed' || $order->status == 'received' || $order->status == 'partial') && $order->payment_method != 'kredit_pro') { if($order->payment_method == 'kredit_mandiri'){} else { ?>
                      <a id="konfirm_pembayaran" href="<?= base_url(aksestoko_route($url_payment)) ?>/<?= $order->id ?>" class="btn-sm btn-success py-2 px-3 " style="font-size: 11px; border-radius: 40px;">Selesaikan Pembayaran</a>
                    <?php }} elseif ($order->payment_method == 'kredit_pro' && ($order->status == 'confirmed' || $order->status == 'received') && ($order->payment_status == 'pending' || $order->payment_status == 'reject')) { ?>
                      <a id="konfirm_pembayaran" href="<?= base_url(aksestoko_route($url_payment)) ?>/<?= $order->id ?>" class="btn-sm btn-success py-2 px-3 " style="font-size: 11px; border-radius: 40px;"><?= $order->payment_status == 'reject' ? 'Pilih Metode Pembayaran' : 'Ajukan Kredit' ?></a>
                    <?php } ?>
                  <?php } ?>
                <?php
                }
                ?>

                <?php
                if ($pt = $this->payment->getPaymentTempByPurchaseId($order->id)) {
                ?>
                  <ul class="order-details-download p-0 mt-3" style="list-style-type:none;">
                    <li class="m-0"><a data-toggle="modal" href="#modalPayment"><i class="fal fa-list mr-1"></i>Daftar Pembayaran<i class="fal fa-angle-right"></i></a></li>

                  </ul>
                <?php }  ?>
              </div>
            </div>

          </div>

          <!-- <hr class="mt-0 mb-2"> -->
          <!-- </div> -->

          <?php
          $totalPaymentAccepted = 0;
          foreach ($payments_temp as $i => $payment_temp) {
            $totalPaymentAccepted += ($payment_temp->status == "accept" ? (int) $payment_temp->nominal : 0);
          }

          if ($order->payment_method == "kredit_pro" && in_array($order->payment_status, ['accept', 'paid', 'partial'])) {
          ?>

            <div class="box p-box" style="padding: 10px 20px;">
              <div class="row" style="margin-bottom: .4rem;">
                <div class="col-8 vcenter">
                  <div class="vcenter">
                    <h6 class="box-subtitle" style="
                    font-size: 1rem;
                    margin-bottom: 0!important;">
                      Pembayaran Kredit Pro
                    </h6>
                  </div>
                </div>
                <div class="col-4 vcenter" style="text-align: right">
                  <div>
                    <img src="<?= base_url('assets/images/kreditpro.png') ?>" alt="KreditPro" width="80">
                  </div>
                </div>
              </div>
              <?php
              $diffPayment = ($totalPaymentAccepted / $order->grand_total) * 100;
              ?>
              <div class="progress" style="margin-bottom: 0!important;">
                <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="<?= $diffPayment ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $diffPayment ?>%">
                  <span style="font-size: 90%; font-weight: 700;"><?= $diffPayment == 100 ? "Lunas" : "" ?></span>
                </div>
              </div>
              <div style="text-align: center;">
                <label>Rp <?= number_format($totalPaymentAccepted, 0, ',', '.') ?> / Rp <?= number_format($order->grand_total, 0, ',', '.') ?></label>
              </div>
            </div>

          <?php
          }
          ?>

        </div>

        <div class="box p-box box-checkout-summary <?= $deliveries ? "" : "hidden" ?>">
          <h3 class="box-subtitle">Penerimaan</h3>
          <!-- <div class="box p-box mb-3"> -->
          <div class="row">
            <table class="table">
              <thead class="small">
                <tr>
                  <th>Nama Barang</th>
                  <th>Jumlah Pesanan</th>
                  <th>Jumlah Diterima</th>
                  <th>Sisa Pesanan</th>
                </tr>
              </thead>
              <tbody class="small">
                <?php
                $totalReceived = 0;
                $totalOrdered = 0;
                $totalLeft = 0;
                foreach ($order->items as $i => $r) {
                  $unit = $object->__unit($r->product_unit_id);
                ?>
                  <tr>
                    <td style="width: 50%"><?= $r->product_code . " - " . $r->product_name ?>
                    </td>
                    <td class="text-center">
                      <?php $totalOrdered += $r->quantity;
                      echo (int) $r->quantity . " <br> " . convert_unit($unit); ?>
                    </td>
                    <td class="text-center">
                      <a href="javascript:void(0)" data-toggle="popover" data-trigger="focus" data-placement="bottom" data-html="true" title="Detail Diterima" data-content="<p>Barang Baik : <?= (int) $r->good_quantity ?><br>Barang Rusak : <?= (int) $r->bad_quantity ?></p>">
                        <?php $totalReceived += $r->quantity_received;
                        echo (int) $r->quantity_received . " <br> " . convert_unit($unit); ?>
                      </a>
                    </td>
                    <td class="text-center">
                      <?php $totalLeft += ($r->quantity - $r->quantity_received);
                      echo (int) ($r->quantity - $r->quantity_received) . " <br> " . convert_unit($unit); ?>
                    </td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>

          </div>
          <!-- </div> -->
        </div>



        <div class="box p-box box-checkout-summary">
          <h3 class="box-subtitle">Ringkasan</h3>
          <!-- <div class="box p-box mb-3"> -->
          <div class="row">
            <div class="col-lg-4 col-md-12">
              <div class="form-group">
                <label>Jumlah Pesanan</label>
                <p><?= $totalOrdered ?></p>
              </div>
            </div>
            <div class="col-lg-4 col-md-12">
              <div class="form-group">
                <label>Jumlah Diterima</label>
                <p><?= $totalReceived ?></p>
              </div>
            </div>

            <div class="col-lg-4 col-md-12">
              <div class="form-group">
                <label>Sisa Pesanan</label>
                <p><?= $totalLeft ?></p>
              </div>
            </div>
          </div>
          <hr class="mt-0 mb-2">

          <?php if ($order->grand_total > 0) { ?>
            <table class="table maintable ringkasan">
              <tr>
                <td class="no-border"><label>Total Harga</label></td>
                <td class="no-border bold text-right">Rp <?= number_format($order->total, 0, ',', '.'); ?></td>
              </tr>
              <?php if ($order->total_discount != 0) { ?>
                <tr>
                  <td class="no-border"><label>Promo</label></td>
                  <td class="no-border bold text-right <?= $order->total_discount >= 0 ? 'text-success' : 'text-danger' ?>">
                    <?= $order->total_discount > 0 ? '-' : '' ?> Rp <?= number_format($order->total_discount, 0, ',', '.'); ?></td>
                </tr>
              <?php } ?>
              <?php if ($order->charge < 0) {
                $notifCharge = 'Potongan Harga';
              } else {
                $notifCharge = 'Biaya Lain-lain';
              } ?>
              <?php if ($order->correction_price < 0) {
                $notifCorrection = 'Pengurangan Harga';
              } else {
                $notifCorrection = 'Penambahan Harga';
              } ?>
              <?php if ($order->charge != 0) { ?>
                <tr>
                  <td class="no-border"><label><?= $notifCharge ?></label></td>
                  <td class="no-border bold text-right <?= $order->charge <= 0 ? 'text-success' : 'text-danger' ?>"><?= $order->charge < 0 ? '-' : '' ?> Rp <?= number_format((abs($order->charge)), 0, ',', '.'); ?></td>
                </tr>
              <?php } ?>
              <?php if ($order->correction_price != 0) { ?>
                <tr>
                  <td class="no-border"><label><?= $notifCorrection ?></label></td>
                  <td class="no-border bold text-right <?= $order->correction_price <= 0 ? 'text-success' : 'text-danger' ?>">
                    <?= $order->correction_price < 0 ? '-' : '' ?> Rp <?= number_format((abs($order->correction_price)), 0, ',', '.'); ?></td>
                </tr>
              <?php } ?>
              <?php if ($order->charge_third_party != 0) { ?>
                <tr>
                  <td class="no-border"><label>Biaya Tambahan Kredit</label></td>
                  <td class="no-border bold text-right <?= $order->charge_third_party <= 0 ? 'text-success' : 'text-danger' ?>">
                    <?= $order->charge_third_party < 0 ? '-' : '' ?> Rp <?= number_format((abs($order->charge_third_party)), 0, ',', '.'); ?></td>
                </tr>
              <?php } ?>
              <tr>
                <td class="text-left" style="padding-right: 0;padding-left: 0; padding-top: 5px;padding-bottom: 5px; border-top: 1px solid rgba(0, 0, 0, 0.1);"><label>Total Pembayaran</label></td>
                <td class="text-primary bold text-right" style="border-top: 1px solid rgba(0, 0, 0, 0.1); padding-right: 0;padding-left: 0; padding-top: 5px;padding-bottom: 5px;">
                  <h5>Rp <?= number_format($order->grand_total, 0, ',', '.'); ?></h5>
                </td>
              </tr>
            </table>
          <?php } ?>
          <!-- </div> -->
          <!-- </div> -->
        </div>
      </div>

      <div class="col-lg-7">
        <div class="box box-order-details p-box mb-3">
          <div class="order-details-header">
            <div class="subheading">
              <h3 class="box-subtitle">Pengiriman</h3>
            </div>
            <div class="address-box box mb-3">
              <div class="heading">
                <i class="fal fa-home"></i>
                <h4><?= $company->company ?></h4>
              </div>
              <p><?= $company->name . ", " . $company->phone ?> <br> <?= trim($company->address) . ', ' . ucwords(strtolower($company->state)) . ', ' . ucwords(strtolower($company->city)) . ', ' . ucwords(strtolower($company->country)) . ' - ' . $company->postal_code ?></p>
            </div>
            <?php if ($deliveries) {
              $totalGoodProduct = 0;
              $totalBadProduct = 0;
            ?>
              <?php foreach ($deliveries as $i => $delivery) { ?>
                <!-- <form action="<?= base_url(aksestoko_route('aksestoko/order/confirm_received')) ?>" method="POST"> -->
                <div class="box p-box mb-3">
                  <div class="row">
                    <div class="col-6">
                      <div class="form-group">
                        <label>No SPJ</label>
                        <p class="h5"><?= $delivery->do_reference_no ?></p>
                      </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12">
                      <div class="form-group">
                        <label>Status Pengiriman</label>
                        <?php if ($delivery->receive_status == "received") { ?>
                          <h5 class="text-success">Barang Diterima</h5>
                        <?php } else { ?>
                          <p class="h5 text-<?= $object->__status($delivery->status)[1] ?>"><?= $object->__status($delivery->status)[0] ?></p>
                        <?php } ?>
                      </div>
                    </div>
                    <div class="col-6">
                      <div class="form-group">
                        <label>Tanggal Dikirim</label>
                        <p class="h5"><?= $my_controller->__convertDate($delivery->date) ?></p>
                      </div>
                    </div>
                    <div class="col-6">
                      <div class="form-group">
                        <label>Dikirim Oleh</label>
                        <p class="h5"><?= strlen($delivery->delivered_by) > 0 ? $delivery->delivered_by : "−" ?></p>
                      </div>
                    </div>
                    <div class="col-12">
                      <div class="row mt-2 mb-3">
                        <?php if ($delivery->receive_status != "received" && $delivery->status != "packing") { ?>
                          <div class="col-md-12">
                            <a href="<?= base_url(aksestoko_route("aksestoko/order/review/")) . $order->id . '/' . $delivery->id ?>" class="btn btn-success btn-block">Konfirmasi Penerimaan</a>
                          </div>
                        <?php } else if ($delivery->receive_status == "received") { ?>
                          <!-- <div class="col-md-12 text-center">
                            <h5 class="text-success">Barang telah diterima</h5>
                          </div> -->
                          <?php if ($sale->sale_type == "booking" && $delivery->is_reject == 1) {  ?>
                            <div class="col-md-12">
                              <a href="<?= base_url(aksestoko_route("aksestoko/order/review/")) . $order->id . '/' . $delivery->id ?>" class="btn btn-danger btn-block">Konfirmasi Bad Quantity</a>
                            </div>
                          <?php } elseif ($delivery->spj_file) { ?>
                            <ul class="order-details-download p-0 text-center" style="list-style-type:none; margin:auto">
                              <li class="m-0"><a data-lightbox="mygallery" href="<?= $delivery->spj_file ?>"><i class="fal fa-download mr-1"></i>Unduh SPJ<i class="fal fa-angle-right"></i></a></li>
                            </ul>
                          <?php } ?>
                        <?php } ?>
                      </div>
                    </div>
                  </div>
                  <!-- Halaman Collapse 01 -->
                  <div id="collapseTerimaId<?= $delivery->id ?>" class="collapse pb-2" aria-expanded="false" style="height: 0px;">
                    <hr class="mt-0">
                    <h3 class="box-subtitle mb-3">Barang yang Dikirim</h3>
                    <!-- <input type="hidden" name="purchase_id" value="<?= $order->id ?>">
                    <input type="hidden" name="do_ref" value="<?= $delivery->do_reference_no ?>">
                    <input type="hidden" name="do_id" value="<?= $delivery->id ?>"> -->
                    <?php

                    foreach ($delivery->items as $i => $item) {
                      $unit = $object->__unit($item->product_unit_id);
                    ?>
                      <!-- <input type="hidden" name="product_code[]" value="<?= $item->product_code ?>">
                      <input type="hidden" name="quantity_received[]" value="<?= (int) $item->quantity_sent ?>"> -->
                      <div class="product-list box">
                        <div class="p-box px-3 py-3 ">
                          <?php if ($delivery->receive_status == "received") {
                            $totalGoodProduct += $item->good_quantity;
                            $totalBadProduct += $item->bad_quantity;
                          ?>

                            <div class="row">
                              <div class="col-6">
                                <h5 class="card-title mb-0">
                                  <a href="<?= base_url(aksestoko_route('aksestoko/product/view/')) . $item->product_id ?>">
                                    <?= $item->product_name ?>
                                  </a>
                                </h5>
                                <small class="text-muted font-weight-light "> <?= $item->product_code ?></small>
                              </div>
                              <div class="col-2">
                                <label class="mb-0">Jumlah</label>
                                <p class="mb-0"><?= (int) $item->quantity_sent . " " . convert_unit($unit) ?></p>
                              </div>
                              <div class="col-2">
                                <label class="mb-0">Baik</label>
                                <p class="mb-0"><?= (int) $item->good_quantity . " " . convert_unit($unit) ?></p>
                              </div>
                              <div class="col-2">
                                <label class="mb-0">Rusak</label>
                                <p class="mb-0"><?= (int) $item->bad_quantity . " " . convert_unit($unit) ?></p>
                              </div>
                            </div>

                          <?php } else { ?>

                            <div class="row">
                              <div class="col-9">
                                <h5 class="card-title mb-0">
                                  <a href="<?= base_url(aksestoko_route('aksestoko/product/view/')) . $item->product_id ?>">
                                    <?= $item->product_name ?>
                                  </a>
                                </h5>
                                <small class="text-muted font-weight-light "> <?= $item->product_code ?></small>
                              </div>
                              <div class="col-3">
                                <label class="mb-0">Jumlah</label>
                                <p class="mb-0 qty"><?= (int) $item->quantity_sent . " " . convert_unit($unit) ?></p>
                              </div>
                            </div>

                          <?php } ?>
                        </div>
                      </div>
                    <?php } ?>
                  </div>
                  <!-- End Halaman Collapse 01 -->

                  <!-- Tombol Collapse 01 -->
                  <div class="order-details-check">
                    <a data-toggle="collapse" data-target="#collapseTerimaId<?= $delivery->id ?>" aria-expanded="false" aria-controls="collapseOrder" class="collapsed"></a>
                  </div>
                  <!-- End Tombol Collapse 01 -->
                </div>
                <!-- </form> -->
              <?php } ?>
            <?php } ?>
          </div>
        </div>

        <div class="box p-box mb-3">
          <div class="subheading">
            <h3 class="box-subtitle">Daftar Belanja (<?= count($order->items) ?>)</h3>
          </div>
          <?php
          $totalQty = 0;
          $totalAmount = 0;
          foreach ($order->items as $i => $item) {
            $totalQty += $item->quantity;
            $totalAmount += $item->subtotal;
            $product = $object->product->getProductByCodeAndSupplierId($item->product_code, $order->supplier_id);
          ?>
            <div class="product-list box">
              <img class="img-fluid product-list-img px-2 py-2" src="<?= url_image_thumb($product->thumb_image) ?>" onerror="this.src='<?= base_url('assets/uploads/no_image.png') ?>'" alt="Product">
              <div class="product-content">
                <h4 class="card-title mb-0">
                  <a href="<?= base_url(aksestoko_route('aksestoko/product/view/')) . $product->id ?>?supplier_id=<?=$order->supplier_id?>">
                    <?= $item->product_name ?>
                  </a>
                </h4>
                <small class="text-muted font-weight-light "> <?= $item->product_code ?></small>
                <?php if ($item->unit_cost > 0) { ?>
                  <h6 class=""> Rp <?= number_format($item->unit_cost, 0, ',', '.'); ?></h6>
                <?php } ?>

                <div class="row mt-3">
                  <div class="col-6 mb-sm-down-3">
                    <label class="d-none d-sm-block">Jumlah</label>
                    <p class="jumlah"><?= (int) $item->quantity . " " . convert_unit($object->__unit($product->unit)) ?></p>
                  </div>
                  <?php if ($item->subtotal > 0) { ?>
                    <div class="col-6 price-hide-on-mobile">
                      <label class="d-none d-sm-block">Harga</label>
                      <p>Rp <?= number_format($item->subtotal, 0, ',', '.'); ?></p>
                    </div>
                  <?php } ?>
                </div>
              </div>
              <div class="price-show-mobile">
                <div class="row">
                  <div class="col-md-12">
                    <label class="d-none d-sm-block">Harga</label>
                    <p style="text-align: center; font-weight: 700;">Rp <?= number_format($item->subtotal, 0, ',', '.'); ?></p>
                  </div>
                </div>
              </div>
            </div>
          <?php } ?>
        </div>
      </div>


    </div>
  </div>
</section>

<!--Modal Bukti Pembayaran-->
<div class="modal fade" tabindex="-1" role="dialog" id="modalPayment">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-body p-box">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true"><i class="fal fa-times"></i></span>
        </button>

        <h4 class="modal-title mb-2">Daftar Bukti Pembayaran</h4>
        <div class="row">
          <table class="table table-hover">
            <thead class="small">
              <tr>
                <th style="vertical-align:middle;">#</th>
                <th style="vertical-align:middle;">Tanggal Unggah</th>
                <th style="vertical-align:middle;">Nominal</th>
                <th style="vertical-align:middle;">Status</th>
                <th style="vertical-align:middle;">Foto</th>
              </tr>
            </thead>
            <tbody class="small" id="data-delivery">
              <?php if ($payments_temp) {
                $nominalTotal = 0;
                foreach ($payments_temp as $i => $pt) {
                  $nominalTotal += ($pt->status != 'reject' ? $pt->nominal : 0); ?>
                  <tr>
                    <td style="vertical-align:middle;"><?= ($i + 1) ?></td>
                    <td style="vertical-align:middle;"><?= $object->__convertDate($pt->created_at) ?></td>
                    <td style="vertical-align:middle; <?= $pt->status == 'reject' ? 'text-decoration: line-through;' : '' ?>">Rp <?= number_format($pt->nominal, 0, ',', '.') ?></td>
                    <td style="vertical-align:middle;" class="text-<?= $object->__status($pt->status, $order->payment_method == 'kredit_pro' ? 1001 : 0)[1] ?>"><?= $object->__status($pt->status, $order->payment_method == 'kredit_pro' ? 1001 : 0)[0] ?></td>
                    <td style="vertical-align:middle;">
                      <?php if ($pt->url_image) { ?>
                        <a data-lightbox="mygallery" href="<?= $pt->url_image ?>"><i class="fal fa-eye mr-1"></i>Bukti Pembayaran<i class="fal fa-angle-right"></i></a>
                      <?php } ?>
                    </td>
                    <!--  -->

                  </tr>
                <?php
                }
              } else { ?>
                <tr>
                  <td style="vertical-align:middle" colspan="5">Tidak ada data pembayaran</td>
                </tr>
              <?php } ?>
            </tbody>
            <tfoot class="small">
              <tr>
                <td>[#]</td>
                <td>[Tanggal Unggah]</td>
                <td style="vertical-align:middle;" class="font-weight-bold">Rp <?= number_format($nominalTotal, 0, ',', '.') ?></td>
                <td>[Status]</td>
                <td>[Foto]</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
      <div class="footer-popup">
        <span id="total-harga-order"><b>Total Pembayaran</b></span>
        <span id="total-harga-order"> Rp <?= number_format($nominalTotal, 0, ',', '.'); ?> / Rp <?= number_format($order->grand_total, 0, ',', '.'); ?></span>
      </div>
    </div>
  </div>
</div>

<!-- Modal Konfirmasi Batal Order -->
<div class="modal fade" id="cancelOrder" tabindex="-1" role="dialog" aria-labelledby="cancelOrder">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title text-center" id="cancelOrderTitle">Batalkan Pesanan</h4>
      </div>
      <div class="modal-body">
        <div class="">
          <div class="row">
            <div class="col-12">
              <div class="card-body text-center">
                <p>Apakah Anda yakin membatalkan pesanan ini?</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
        <a id="btn-ok" class="btn btn-primary" href="">Iya</a>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="div-confirm" tabindex="-1" role="dialog" aria-labelledby="cancelOrder">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title text-center" id="cancelOrderTitle">Konfirmasi Harga Pesanan</h4>
      </div>
      <div class="modal-body">
        <div class="">
          <div class="row">
            <div class="col-12">
              <div class="card-body text-center">
                <p>Apakah Anda yakin mengonfirmasi harga pesanan ini?</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
        <a id="okAddress" class="btn btn-primary" href="<?= base_url(aksestoko_route('aksestoko/order/confirm_update_price/')) . $order->id ?>">Iya</a>
      </div>
    </div>
  </div>
</div>

<script>
  $(function() {
    $(document).off("click.fb-start", "[data-trigger]");
    $('[data-toggle="popover"]').popover()
  })

  var tour = {
    id: "order_detail",
    onClose: function() {
      hopscotch.endTour(tour);
      callAjax();
    },
    onEnd: function() {
      callAjax()
    },

    steps: [{
        title: "Selesaikan Pembayaran",
        content: "Klik tombol ini",
        target: "a#konfirm_pembayaran",
        placement: "top",
      },

    ]

  };

  <?php
  if (!$guide->order_detail) {
  ?>
    hopscotch.startTour(tour);
  <?php
  }
  ?>

  function callAjax() {
    isStart = false;
    $.ajax({
      url: '<?= base_url(aksestoko_route('aksestoko/auth/set_guide/order_detail/1')); ?>',
      type: 'GET',
    })

  }

  $('#btn-cancel-update-price').click(function() {
    $("#btn-ok").attr("href", "<?= base_url(aksestoko_route('aksestoko/order/cancel_update_price/')) . $order->id ?>");
  });

  $('#btn-cancel-order').click(function() {
    $("#btn-ok").attr("href", "<?= base_url(aksestoko_route('aksestoko/order/cancel_order/')) . $order->id ?>");
  });
</script>