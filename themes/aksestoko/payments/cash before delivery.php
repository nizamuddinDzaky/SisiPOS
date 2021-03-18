<?php
if ($purchase->id && $purchase->payment_method != 'kredit_pro') {
    $total = ($purchase->total + $purchase->charge + $purchase->correction_price - $purchase->total_discount - $purchase->paid);
} else {
    $total = ($purchase->grand_total + $purchase->charge - $purchase->total_discount - $purchase->paid);
}
$clasBank = str_replace(" ", "-", $payment_method);
?>
<div class="row">
    <div class="col-lg-12">
        <div class="box-order-details ">
            <div class="box p-box mb-3">
                <div class="row" id="cbd" style="cursor: pointer;">
                    <div class="col-auto m-hide">
                        <div class="form-group">
                            <img src="<?= base_url('assets/uploads/logos/') ?>cbd.png" alt="" class="logo-payment">
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="form-group">
                            <p class="h6 title-methode-payment">Bayar Sebelum Dikirim</p>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <p class="h6 price-methode-payment" style="color:#B20838; text-align:right;">Rp <?= number_format($total, 0, ',', '.') ?></p>
                        </div>
                    </div>

                </div>
                <!-- Halaman Collapse 01 -->
                <div id="collapseTerimaId2083" class="pb-3 collapse <?= $purchase->id && $purchase->payment_method != 'kredit_pro' ? 'in' : '' ?> div-collapse" aria-expanded="true" style="">
                    <hr class="mt-0">

                    <div class="row">

                        <div class="col-md-6">
                            <h6 class="detail-payment">Bank</h6>
                            <div class="row">
                                <!-- button -->
                                <?php if (!$purchase->id || $purchase->bank_id == 0) { ?>
                                    <div class="col-auto" style="padding:5px;">
                                        <div id="tunai" class="box button-payment selected bank-list py-1 bank-list-<?= $clasBank ?>" data-id-payment-method="<?= $clasBank ?>">
                                            <div class="px-3 py-2 detail-payment">
                                                <i class="fas fa-money-bill-alt"></i>
                                                Tunai
                                            </div>
                                        </div>
                                    </div>
                                <?php } elseif ($purchase->payment_status == 'reject' && $purchase->payment_method == 'kredit_pro') { ?>
                                    <div class="col-auto" style="padding:5px;">
                                        <div id="tunai" class="box button-payment selected bank-list py-1 bank-list-<?= $clasBank ?>" data-id-payment-method="<?= $clasBank ?>">
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
                                            <div class="box button-payment bank-list py-1 bank-list-<?= $clasBank ?>" data-idbank="<?= $bank->id ?>" data-id-payment-method="<?= $clasBank ?>">
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
                                            <div class="box button-payment bank-list py-1 bank-list-<?= $clasBank ?>" data-idbank="<?= $bank->id ?>" data-id-payment-method="<?= $clasBank ?>">
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
                                }
                                ?>
                                <?php
                                if ($purchase->id && $purchase->bank_id != 0 && $purchase->payment_method != 'kredit_pro') { ?>
                                    <div class="col-auto" style="padding:5px;">
                                        <div class="box button-payment bank-list py-1 bank-list-<?= $clasBank ?>" data-idbank="<?= $bank_id ?>" data-id-payment-method="<?= $clasBank ?>">
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
                                <div class="col-12 detail-bank" id="detail-bank-<?= $clasBank ?>">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="detail-payment">Detail</h6>
                            <div class="box">
                                <div class="p-box px-3 py-3 ">
                                    <!-- <hr class="mt-0 mb-2"> -->

                                    <table class="table maintable ringkasan">
                                        <tbody>
                                            <?php if ($purchase->id && $purchase->payment_method != 'kredit_pro') { ?>
                                                <?php if ($purchase->total != 0) { ?>
                                                    <tr>
                                                        <td class="no-border font-13"><label>Harga</label></td>
                                                        <td class="no-border bold text-right font-13">Rp <?= number_format($purchase->total, 0, ',', '.') ?></td>
                                                    </tr>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <?php if ($purchase->grand_total != 0) { ?>
                                                    <tr>
                                                        <td class="no-border font-13"><label>Harga</label></td>
                                                        <td class="no-border bold text-right font-13">Rp <?= number_format($purchase->grand_total, 0, ',', '.') ?></td>
                                                    </tr>
                                                <?php } ?>
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
                                            <?php if ($purchase->total_discount != 0) { ?>
                                                <tr>
                                                    <td class="no-border font-13"><label>Diskon</label></td>
                                                    <td class="no-border bold text-right font-13">Rp <?= number_format($purchase->total_discount, 0, ',', '.') ?></td>
                                                </tr>
                                            <?php } ?>
                                            <?php if ($paid != 0) { ?>
                                                <tr>
                                                    <td class="no-border font-13"><label>Sudah Dibayar</label></td>
                                                    <td class="no-border bold text-right font-13 text-success">- Rp <?= number_format($paid, 0, ',', '.') ?></td>
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
                                            <button type="submit" class="btn btn-primary btn-block small-btn-payment pending button_payment_right" data-payment-method="<?= $payment_method ?>" data-str-payment-method="Bayar Sebelum Dikirim">Selesaikan</button>
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
                    <a id="cbd_detail" data-toggle="collapse" data-target="#collapseTerimaId2083" aria-expanded="<?= $purchase->id ? 'true' : 'false' ?>" aria-controls="collapseOrder" class="see-more-payment"></a>
                </div>
                <!-- End Tombol Collapse 01 -->
            </div>
        </div>
    </div>
</div>

<script>
    $('#cbd').click(function(event) {
        $('#cbd_detail').click();
    })

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