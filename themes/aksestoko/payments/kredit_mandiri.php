<?php
if ($purchase->id && $purchase->payment_method != 'kredit_mandiri') {
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
                <div class="row" id="kreditmandiri" style="cursor: pointer;">
                    <div class="col-auto m-hide">
                        <div class="form-group">
                            <img src="<?= base_url('assets/uploads/logos/mandiri.png') ?>" alt="" class="logo-payment">
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="form-group">
                            <p class="h6 title-methode-payment">Kredit Mandiri</p>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <p class="h6 price-methode-payment" style="color:#B20838; text-align:right;">Rp <?= number_format($total, 0, ',', '.') ?></p>
                        </div>
                    </div>

                </div>
                <!-- Halaman Collapse 01 -->
                <div id="collapseKreditMandiri" class="pb-3 collapse <?= $purchase->id && $purchase->payment_method != 'kredit_pro' ? 'in' : '' ?> div-collapse" aria-expanded="true" style="">
                    <hr class="mt-0">

                    <div class="row">
                        <div class="col-xs-12 col-md-5 mb-2">
                            <h6 class="detail-payment">Limit</h6>
                            <h5 class="text-primary"><?= ($kredit_mandiri=='-' ? $kredit_mandiri : 'Rp '.number_format($kredit_mandiri, 0, ',', '.')) ?></h5>
                        </div>

                        <div class="col-xs-12 col-md-7">
                            <h6 class="detail-payment">Detail</h6>
                            <div class="box">
                                <div class="p-box px-3 py-3 ">

                                    <div>
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
                            </div>
                            
                        </div>
                        <div class="col-md-12">
                            <div class="alert alert-warning mt-4" role="alert" style="color: #856404; background-color: #fff3cd; border-color: #ffeeba;">
                                <h6 class="alert-heading">Perhatian</h6>
                                <p style="font-size: 13px;">Untuk mendapatkan kredit limit Bank Mandiri Anda perlu mengajukan pinjaman melalui menu <a href="<?= base_url(aksestoko_route('aksestoko/home/kredit_bank_mandiri')) ?>">Kredit Bank Mandiri </a></p>
                            </div>
                            <div class="footer-payment">
                                <div class="form-group">
                                    <div class="px-2 py-2">
                                       <?php
                                        $sisa_limit = ($kredit_mandiri=='-' ? 0 : (float)$kredit_mandiri);
                                        if (($sisa_limit - $purchase->grand_total) >= 0) {
                                       ?>
                                        <button type="submit" class="btn button_payment_right btn-primary btn-block small-btn-payment pending" data-payment-method="<?= $payment_method ?>" data-str-payment-method="Kredit Mandiri">Selesaikan</button>
                                        <?php } else {?>
                                        <small class="text-danger font-italic">Limit Anda tidak mencukupi untuk dapat memilih metode ini.</small>
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
                    <a id="kreditmandiri_detail" data-toggle="collapse" data-target="#collapseKreditMandiri" aria-expanded="<?= $purchase->id ? 'true' : 'false' ?>" aria-controls="collapseOrder" class="see-more-payment"></a>
                </div>
                <!-- End Tombol Collapse 01 -->
            </div>
        </div>
    </div>
</div>

<script>
    $('#kreditmandiri').click(function(event) {
        $('#kreditmandiri_detail').click();
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