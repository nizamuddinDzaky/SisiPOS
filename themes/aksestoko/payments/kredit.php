<?php
$harga_tempo      = 0;
if ($purchase->id) {
    $total_purchase   = $purchase->total;
    $disc             = $purchase->total_discount;
    $total            = ($total_purchase + $purchase->charge + $purchase->correction_price - $disc - $purchase->paid);
} else if ($purchase->payment_status == 'reject' && $purchase->payment_method == 'kredit_pro') {
    $total_purchase   = $purchase->total;
    $disc             = $purchase->total_discount;
    $total            = ($total_purchase + $purchase->charge + $purchase->correction_price - $disc - $purchase->paid);
} else {
    $total_purchase   = $purchase->grand_total;
    $harga_tempo      = $purchase->grand_total_tempo - $purchase->grand_total;
    $disc             = $purchase->total_discount_tempo;
    $total            = ($total_purchase + $purchase->charge - $disc - $purchase->paid + $harga_tempo);
}

$clasBank = str_replace(" ", "-", $payment_method);

?>
<div class="row">
    <div class="col-lg-12">
        <div class="box-order-details ">
            <div class="box p-box mb-3">
                <div class="row" id="kredit" style="cursor: pointer;">
                    <div class="col-auto m-hide">
                        <div class="form-group">
                            <img src="<?= base_url('assets/uploads/logos/') ?>credit.png" alt="" class="logo-payment">
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="form-group">
                            <p class="h6 title-methode-payment">Tempo Dengan Distributor</p>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group" id="TOP"></div>
                    </div>
                </div>
                <!-- Halaman Collapse 01 -->
                <div id="collapsePaymentId02" class="pb-2 collapse <?= $purchase->id && $purchase->payment_method != 'kredit_pro' ? 'in' : '' ?> div-collapse" aria-expanded="true">
                    <hr class="mt-0">

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="detail-payment">Bank</h6>
                            <div class="row">
                                <!-- button -->
                                <?php if (!$purchase->id || $purchase->bank_id == 0) { ?>
                                    <div class="col-auto" style="padding:5px;">
                                        <div id="tunai" class="box button-payment selected bank-list py-1 bank-list-<?= $id_payment_method ?>" data-id-payment-method="<?= $id_payment_method ?>">
                                            <div class="px-3 py-2 detail-payment">
                                                <i class="fas fa-money-bill-alt"></i>
                                                Tunai
                                            </div>
                                        </div>
                                    </div>
                                <?php } elseif ($purchase->payment_status == 'reject' && $purchase->payment_method == 'kredit_pro') { ?>
                                    <div class="col-auto" style="padding:5px;">
                                        <div id="tunai" class="box button-payment selected bank-list py-1 bank-list-<?= $id_payment_method ?>" data-id-payment-method="<?= $id_payment_method ?>">
                                            <div class="px-3 py-2 detail-payment">
                                                <i class="fas fa-money-bill-alt"></i>
                                                Tunai
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>

                                <?php
                                if (!$purchase->id) {
                                    foreach ($banks as $keyBank => $bank) { ?>
                                        <div class="col-auto" style="padding:5px;">
                                            <div class="box button-payment bank-list py-1 bank-list-<?= $id_payment_method ?>" data-idbank="<?= $bank->id ?>" data-id-payment-method="<?= $id_payment_method ?>">
                                                <div class="px-3 py-2 detail-payment">
                                                    <i class="fas fa-credit-card"></i>
                                                    <?= strtoupper($bank->bank_name) ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php }
                                } elseif ($purchase->payment_status == 'reject' && $purchase->payment_method == 'kredit_pro') {
                                    foreach ($banks as $keyBank => $bank) { ?>
                                        <div class="col-auto" style="padding:5px;">
                                            <div class="box button-payment bank-list py-1 bank-list-<?= $id_payment_method ?>" data-idbank="<?= $bank->id ?>" data-id-payment-method="<?= $id_payment_method ?>">
                                                <div class="px-3 py-2 detail-payment">
                                                    <i class="fas fa-credit-card"></i>
                                                    <?= strtoupper($bank->bank_name) ?>
                                                </div>
                                            </div>
                                        </div>
                                <?php }
                                } else {
                                    foreach ($banks as $keyBank => $bank) {
                                        if ($bank->id == $purchase->bank_id) {
                                            $bank_id = $bank->id;
                                            $bank_name = strtoupper($bank->bank_name);
                                        }
                                    }
                                } ?>
                                <?php if ($purchase->id && $purchase->bank_id != 0 && $purchase->payment_method != 'kredit_pro') { ?>
                                    <div class="col-auto" style="padding:5px;">
                                        <div class="box button-payment bank-list py-1 bank-list-<?= $id_payment_method ?>" data-idbank="<?= $bank_id ?>" data-id-payment-method="<?= $id_payment_method ?>">
                                            <div class="px-3 py-2 detail-payment">
                                                <i class="fas fa-credit-card"></i>
                                                <?= strtoupper($bank_name) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                            <!-- Content -->
                            <div class="row">
                                <div class="col-12 detail-bank" id="detail-bank-<?= $id_payment_method ?>">

                                </div>
                            </div>
                            <div class="py-3">
                                <h6 class="detail-payment">Rencana Pelunasan</h6>
                                <small class="detail-payment">Hanya sebagai pengingat Toko, tidak berimbas menjadi batas pembayaran Toko</small>
                                <div class="input-group mb-3" style="margin-top: 15px;">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1"><i class="fal fa-credit-card text-primary"></i></span>
                                    </div>

                                    <?php if ($purchase->id && $purchase->payment_method != 'kredit_pro') { ?>
                                        <select class="form-control col-md-12" name="payment_durasi" id="payment_durasi" disabled>
                                            <option value="<?= $purchase->payment_duration ?>">
                                                <?= $purchase->payment_duration . ' ' . lang('hari') ?>
                                            </option>;
                                        <?php } else { ?>
                                            <select class="form-control col-md-12" name="payment_durasi" id="payment_durasi">
                                                <?php foreach ($TOP as $row) {
                                                    echo "<option value='" . $row->duration . "'>" . $row->duration . ' ' . lang('hari') . "</option>";
                                                } ?>
                                            <?php } ?>
                                            </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="detail-payment">Detail</h6>
                            <div class="box">
                                <div class="p-box px-3 py-3 ">
                                    <table class="table maintable ringkasan">
                                        <tbody>
                                            <?php if ($total_purchase != 0) { ?>
                                                <tr>
                                                    <td class="no-border font-13"><label>Harga</label></td>
                                                    <td class="no-border bold text-right font-13">Rp <?= number_format($total_purchase, 0, ',', '.') ?></td>
                                                </tr>
                                            <?php } ?>
                                            <?php if ($harga_tempo != 0) { ?>
                                                <tr>
                                                    <td class="no-border font-13"><label>Biaya Tempo</label></td>
                                                    <td class="no-border bold text-right font-13">Rp <?= number_format($harga_tempo, 0, ',', '.') ?></td>
                                                </tr>
                                            <?php } ?>

                                            <?php if ($purchase->id && $purchase->payment_method != 'kredit_pro') { ?>
                                                <?php if ($purchase->charge && $purchase->charge != 0) { ?>
                                                    <tr>
                                                        <td class="no-border font-13"><label><?= $purchase->charge > 0 ? 'Biaya lain-lain' : 'Potongan harga' ?></label></td>
                                                        <td class="no-border bold text-right font-13 <?= $purchase->charge > 0 ? 'text-danger' : 'text-success' ?>"><?= $purchase->charge > 0 ? '' : '-' ?> Rp <?= number_format(abs($purchase->charge), 0, ',', '.') ?></td>
                                                    </tr>
                                                <?php } ?>
                                                <?php if ($purchase->correction_price && $purchase->correction_price != 0) { ?>
                                                    <tr>
                                                        <td class="no-border font-13"><label><?= $purchase->correction_price > 0 ? 'Penambahan harga' : 'Pengurangan harga' ?></label></td>
                                                        <td class="no-border bold text-right font-13">Rp <?= number_format($purchase->correction_price, 0, ',', '.') ?></td>
                                                    </tr>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <?php if ($purchase->charge != 0) { ?>
                                                    <tr>
                                                        <td class="no-border font-13"><label>Pengiriman Distributor</label></td>
                                                        <td class="no-border bold text-right font-13">Rp <?= number_format($purchase->charge, 0, ',', '.') ?></td>
                                                    </tr>
                                                <?php } ?>
                                            <?php } ?>

                                            <?php if ($disc != 0) { ?>
                                                <tr>
                                                    <td class="no-border font-13"><label>Diskon</label></td>
                                                    <td class="no-border bold text-right font-13">- Rp <?= number_format($disc, 0, ',', '.') ?></td>
                                                </tr>

                                            <?php } ?>
                                            <?php if ($purchase->paid != 0) { ?>
                                                <tr>
                                                    <td class="no-border font-13"><label>Sudah Dibayar</label></td>
                                                    <td class="no-border bold text-right font-13 text-success">- Rp <?= number_format($purchase->paid, 0, ',', '.') ?></td>
                                                </tr>
                                            <?php } ?>
                                            <tr>
                                                <td class="text-left font-13" style="padding-right: 0;padding-left: 0; padding-top: 5px;padding-bottom: 5px; border-top: 1px solid rgba(0, 0, 0, 0.1);"><label>Yang Perlu Dibayar</label></td>
                                                <td class="text-primary bold text-right font-13" style="border-top: 1px solid rgba(0, 0, 0, 0.1); padding-right: 0;padding-left: 0; padding-top: 5px;padding-bottom: 5px;" id="jumlahsisa">
                                                    <h6>Rp <?= number_format($total, 0, ',', '.') ?> <a onClick="salinsisa()" href="javascript:void(0)" class="text-blue2 <?= !$purchase->id ? 'hidden' : '' ?>"><i class="fal fa-copy mr-1"></i></a></h6>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>

                                </div>
                            </div>
                            <div class="footer-payment">
                                <div class="form-group">
                                    <div class="px-2 py-2">
                                        <?php if (!$purchase->id) { ?>
                                            <button type="submit" class="btn button_payment_right btn-primary btn-block small-btn-payment pending" data-payment-method="<?= $payment_method ?>" data-str-payment-method="Tempo Dengan Distributor">Selesaikan</button>
                                        <?php } else if ($purchase->payment_status == 'reject' && $purchase->payment_method == 'kredit_pro') { ?>
                                            <button type="submit" class="btn btn-primary btn-block small-btn-payment pending button_payment_right" data-payment-method="<?= $payment_method ?>" data-str-payment-method="Bayar Sebelum Dikirim">Selesaikan</button>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
                <!-- End Halaman Collapse 01 -->
                <!-- Tombol Collapse 01 -->
                <div class="order-details-check">
                    <a id="kredit_detail" data-toggle="collapse" data-target="#collapsePaymentId02" aria-expanded="<?= $purchase->id ? 'true' : 'false' ?>" aria-controls="collapseOrder" class="see-more-payment"></a>
                </div>
                <!-- End Tombol Collapse 01 -->
            </div>
        </div>
    </div>
</div>
<script>
    $('#kredit').click(function(event) {
        $('#kredit_detail').click();
    })
    var purchase = <?php echo json_encode($purchase->id); ?>;
    if (purchase) {
        var a = document.getElementById("payment_durasi").value;
        var hasil = document.getElementById("TOP");
        hasil.innerHTML = "<p class='h6 price-kredit' style='color:#B20838; text-align:right;'>Rp <?= number_format($total, 0, ',', '.') ?><br><label>&nbsp;(" + a + " Hari)</label></p>";
    } else {
        $("select")
            .change(function() {
                var str = "";
                $("select option:selected").each(function() {
                    str += $(this).text() + " ";
                });
                var hasil = document.getElementById("TOP");
                hasil.innerHTML = "<p class='h6 price-kredit' style='color:#B20838; text-align:right;'>Rp <?= number_format($total, 0, ',', '.') ?><br><label>&nbsp;(" + str.substr(0, str.indexOf(' ')) + " Hari)</label></p>";
            })
            .trigger("change");
    }

    function salinsisa() {
        element = document.getElementById("jumlahsisa");
        var selection = window.getSelection();
        var range = document.createRange();
        range.selectNodeContents(element);
        selection.removeAllRanges();
        selection.addRange(range);
        try {
            var successful = document.execCommand('copy');
            if (successful) {
                alertCustom("Sisa Pembayaran telah disalin : " + selection);
            } else {
                alertCustom("Tidak dapat menyalin !");
            }
        } catch (err) {
            alertCustom("Error !!");
        }
    }
</script>