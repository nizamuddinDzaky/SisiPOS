<style>
    ul.tabs {
        margin: 0px;
        padding: 0px;
        list-style: none;
    }


    ul.tabs li.current {
        background: #B20838;
        color: #fff;
        font-weight: 800;
        font-size: 14px;

    }

    .tab-content {
        display: none;
        background: none;
        padding: 15px;
        border-top: 1px solid #efefef;
        padding-bottom: 0;
        /* -webkit-box-shadow: 0 0.075rem 0.35rem rgba(0, 0, 0, 0.075);
        box-shadow: 0 0.075rem 0.35rem rgba(0, 0, 0, 0.075); */
    }

    .tab-content.current {
        display: inherit;
    }
</style>
<?php
if ($purchase->id) {
    $total          = ($purchase->total + $purchase->charge + $purchase->correction_price + $purchase->charge_third_party - $purchase->total_discount - $purchase->paid);
} else {
    $total          = $purchase->grand_total + $purchase->charge - $purchase->total_discount - $paid;
}
$kreditpro30hari    = ($total * 0.9 / 100);
$kreditpro45hari    = ($total * 1.3 / 100);
$kreditpro60hari    = ($total * 2 / 100);

$strCurrentTotal = 'kreditpro'.$current_kreditpro.'hari';

// var_dump($term_payment_kredit_pro);die;
?>
<div class="row">
    <div class="col-lg-12">
        <div class="box-order<?=count($term_payment_kredit_pro) > 0 ? '-details' : ''?> ">
            <div class="box p-box mb-3">
                <div class="row" id="kreditpro" style="cursor: pointer;">
                    <div class="col-auto m-hide">
                        <div class="form-group">
                            <img src="<?= base_url('assets/uploads/logos/') ?>kreditpro.png" alt="" class="logo-payment">
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="form-group">
                            <p class="h6 title-methode-payment">KreditPro</p>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group" id="price-kreditpro">
                            <?php if (count($term_payment_kredit_pro) > 0) {?>
                            <p class='h6 price-kreditpro' style='color:#B20838; text-align:right;'> Rp <?= number_format($total + ${$strCurrentTotal}, 0, ',', '.') ?><br><label>&nbsp;(<?=$default_term_payment_kredit_pro[$current_kreditpro]?>)</label></p>
                            <?php }else{?>
                            <p class='h6 price-kreditpro' style='color:#B20838; text-align:right;'> Durasi Pembayaran Tidak Tersedia</p>
                            <?php }?>
                        </div>
                    </div>

                </div>
                <!-- Halaman Collapse 04 -->
                <div id="collapsePaymentId04" class="pb-3 collapse <?= $purchase->id ? 'in' : '' ?> div-collapse" aria-expanded="true" style="">
                    <hr class="mt-0">

                    <div class="row">

                        <div class="col-md-12">
                            <h6 class="detail-payment">Detail</h6>
                            <div class="box">
                                <div class="p-box px-3 py-3 ">

                                    <ul class="tabs">
                                    <?php
                                        $tab = 1;
                                        foreach ($default_term_payment_kredit_pro as $key => $value) {
                                            if (in_array($key, $term_payment_kredit_pro)) {
                                    ?>
                                            <li class="tab-link <?= $current_kreditpro == $key ? 'current' : ''?> title-mobile" data-tab="tab-<?=$tab?>" id="tab-<?=$tab?>0"><?=$value?></li>
                                    <?php
                                            }
                                        $tab ++;
                                        }
                                    ?>
                                    
                                    </ul>

                                    <div id="tab-1" class="tab-content <?= $current_kreditpro == '30' ? 'current' : ''?>">
                                        <table class="table maintable ringkasan">
                                            <tbody>
                                                <?php if ($purchase->id) { ?>
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
                                                <?php if ($purchase->id) { ?>
                                                    <?php if ($purchase->charge && $purchase->charge != 0) { ?>
                                                        <tr>
                                                            <td class="no-border font-13"><label><?= $purchase->charge > 0 ? 'Biaya lain-lain' : 'Potongan harga' ?></label></td>
                                                            <td class="no-border bold text-right font-13">Rp <?= number_format($purchase->charge, 0, ',', '.') ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($purchase->correction_price && $purchase->correction_price != 0) { ?>
                                                        <tr>
                                                            <td class="no-border font-13"><label><?= $purchase->correction_price > 0 ? 'Penambahan harga' : 'Pengurangan harga' ?></label></td>
                                                            <td class="no-border bold text-right font-13">Rp <?= number_format($purchase->correction_price, 0, ',', '.') ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($purchase->charge_third_party && $purchase->charge_third_party != 0) { ?>
                                                        <tr>
                                                            <td class="no-border font-13"><label>Biaya Kredit</label></td>
                                                            <td class="no-border bold text-right font-13">Rp <?= number_format($purchase->charge_third_party, 0, ',', '.') ?></td>
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
                                                <tr>
                                                    <td class="text-left font-13" style="padding-right: 0;padding-left: 0; padding-top: 5px;padding-bottom: 5px; border-top: 1px solid rgba(0, 0, 0, 0.1);"><label>Sub Total</label></td>
                                                    <td class="text-primary bold text-right font-13" style="border-top: 1px solid rgba(0, 0, 0, 0.1); padding-right: 0;padding-left: 0; padding-top: 5px;padding-bottom: 5px;">
                                                        <h6>Rp <?= number_format($total, 0, ',', '.') ?></h6>
                                                    </td>
                                                </tr>
                                                <?php if ($kreditpro30hari != 0) { ?>
                                                    <tr>
                                                        <td class="no-border font-13"><label>Interest Rate <strong>(0,9%)</strong></label></td>
                                                        <td class="no-border bold text-right font-13">Rp. <?= number_format($kreditpro30hari, 0, ',', '.') ?></td>
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
                                                    <td class="text-primary bold text-right font-13" style="border-top: 1px solid rgba(0, 0, 0, 0.1); padding-right: 0;padding-left: 0; padding-top: 5px;padding-bottom: 5px;" id="jumlahsisa30">
                                                        <h6>Rp <?= number_format($total + $kreditpro30hari, 0, ',', '.') ?> <a onClick="salinsisa30()" href="javascript:void(0)" class="text-blue2 <?= !$purchase->id ? 'hidden' : '' ?>"><i class="fal fa-copy mr-1"></i></a></h6>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div id="tab-2" class="tab-content <?= $current_kreditpro == '45' ? 'current' : ''?>">
                                        <table class="table maintable ringkasan">
                                            <tbody>
                                                <?php if ($purchase->id) { ?>
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

                                                <?php if ($purchase->id) { ?>
                                                    <?php if ($purchase->charge && $purchase->charge != 0) { ?>
                                                        <tr>
                                                            <td class="no-border font-13"><label><?= $purchase->charge > 0 ? 'Biaya lain-lain' : 'Potongan harga' ?></label></td>
                                                            <td class="no-border bold text-right font-13">Rp <?= number_format($purchase->charge, 0, ',', '.') ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($purchase->correction_price && $purchase->correction_price != 0) { ?>
                                                        <tr>
                                                            <td class="no-border font-13"><label><?= $purchase->correction_price > 0 ? 'Penambahan harga' : 'Pengurangan harga' ?></label></td>
                                                            <td class="no-border bold text-right font-13">Rp <?= number_format($purchase->correction_price, 0, ',', '.') ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($purchase->charge_third_party && $purchase->charge_third_party != 0) { ?>
                                                        <tr>
                                                            <td class="no-border font-13"><label>Biaya Kredit</label></td>
                                                            <td class="no-border bold text-right font-13">Rp <?= number_format($purchase->charge_third_party, 0, ',', '.') ?></td>
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
                                                <tr>
                                                    <td class="text-left font-13" style="padding-right: 0;padding-left: 0; padding-top: 5px;padding-bottom: 5px; border-top: 1px solid rgba(0, 0, 0, 0.1);"><label>Sub Total</label></td>
                                                    <td class="text-primary bold text-right font-13" style="border-top: 1px solid rgba(0, 0, 0, 0.1); padding-right: 0;padding-left: 0; padding-top: 5px;padding-bottom: 5px;">
                                                        <h6>Rp <?= number_format($total, 0, ',', '.') ?></h6>
                                                    </td>
                                                </tr>
                                                <?php if ($kreditpro45hari != 0) { ?>
                                                    <tr>
                                                        <td class="no-border font-13"><label>Interest Rate <strong>(1,3%)</strong></label></td>
                                                        <td class="no-border bold text-right font-13">Rp. <?= number_format($kreditpro45hari, 0, ',', '.') ?></td>
                                                    </tr>
                                                <?php } ?>
                                                <tr>
                                                    <td class="text-left font-13" style="padding-right: 0;padding-left: 0; padding-top: 5px;padding-bottom: 5px; border-top: 1px solid rgba(0, 0, 0, 0.1);"><label>Yang Perlu Dibayar</label></td>
                                                    <td class="text-primary bold text-right font-13" style="border-top: 1px solid rgba(0, 0, 0, 0.1); padding-right: 0;padding-left: 0; padding-top: 5px;padding-bottom: 5px;" id="jumlahsisa45">
                                                        <h6>Rp <?= number_format($total + $kreditpro45hari, 0, ',', '.') ?> <a onClick="salinsisa45()" href="javascript:void(0)" class="text-blue2 <?= !$purchase->id ? 'hidden' : '' ?>"><i class="fal fa-copy mr-1"></i></a></h6>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div id="tab-3" class="tab-content <?= $current_kreditpro == '60' ? 'current' : ''?>">
                                        <table class="table maintable ringkasan">
                                            <tbody>
                                                <?php if ($purchase->id) { ?>
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

                                                <?php if ($purchase->id) { ?>
                                                    <?php if ($purchase->charge && $purchase->charge != 0) { ?>
                                                        <tr>
                                                            <td class="no-border font-13"><label><?= $purchase->charge > 0 ? 'Biaya lain-lain' : 'Potongan harga' ?></label></td>
                                                            <td class="no-border bold text-right font-13">Rp <?= number_format($purchase->charge, 0, ',', '.') ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($purchase->correction_price && $purchase->correction_price != 0) { ?>
                                                        <tr>
                                                            <td class="no-border font-13"><label><?= $purchase->correction_price > 0 ? 'Penambahan harga' : 'Pengurangan harga' ?></label></td>
                                                            <td class="no-border bold text-right font-13">Rp <?= number_format($purchase->correction_price, 0, ',', '.') ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($purchase->charge_third_party && $purchase->charge_third_party != 0) { ?>
                                                        <tr>
                                                            <td class="no-border font-13"><label>Biaya Kredit</label></td>
                                                            <td class="no-border bold text-right font-13">Rp <?= number_format($purchase->charge_third_party, 0, ',', '.') ?></td>
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
                                                <tr>
                                                    <td class="text-left font-13" style="padding-right: 0;padding-left: 0; padding-top: 5px;padding-bottom: 5px; border-top: 1px solid rgba(0, 0, 0, 0.1);"><label>Sub Total</label></td>
                                                    <td class="text-primary bold text-right font-13" style="border-top: 1px solid rgba(0, 0, 0, 0.1); padding-right: 0;padding-left: 0; padding-top: 5px;padding-bottom: 5px;">
                                                        <h6>Rp <?= number_format($total, 0, ',', '.') ?></h6>
                                                    </td>
                                                </tr>
                                                <?php if ($kreditpro60hari != 0) { ?>
                                                    <tr>
                                                        <td class="no-border font-13"><label>Interest Rate <strong>(2%)</strong></label></td>
                                                        <td class="no-border bold text-right font-13">Rp. <?= number_format($kreditpro60hari, 0, ',', '.') ?></td>
                                                    </tr>
                                                <?php } ?>
                                                <tr>
                                                    <td class="text-left font-13" style="padding-right: 0;padding-left: 0; padding-top: 5px;padding-bottom: 5px; border-top: 1px solid rgba(0, 0, 0, 0.1);"><label>Yang Perlu Dibayar</label></td>
                                                    <td class="text-primary bold text-right font-13" style="border-top: 1px solid rgba(0, 0, 0, 0.1); padding-right: 0;padding-left: 0; padding-top: 5px;padding-bottom: 5px;" id="jumlahsisa60">
                                                        <h6>Rp <?= number_format($total + $kreditpro60hari, 0, ',', '.') ?> <a onClick="salinsisa60()" href="javascript:void(0)" class="text-blue2 <?= !$purchase->id ? 'hidden' : '' ?>"><i class="fal fa-copy mr-1"></i></a></h6>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-warning mt-4" role="alert" style="color: #856404; background-color: #fff3cd; border-color: #ffeeba;">
                                <h6 class="alert-heading">Perhatian</h6>
                                <p style="font-size: 13px;">Konfirmasi pengajuan kredit oleh KreditPro hanya dapat diproses pada jam kerja. Setiap hari Senin - Jum'at (kecuali hari libur) jam 08:30 - 17:00 (WIB).</p>
                            </div>
                            <div class="footer-payment">
                                <div class="form-group">
                                    <div class="px-2 py-2">
                                        <?php
                                        if (!$purchase->id) { 
                                            if ($total >= 1000000) { ?>
                                                <button type="submit" class="btn button_payment_right btn-primary btn-block small-btn-payment pending" data-payment-method="<?= $payment_method ?>" data-str-payment-method="KreditPro">Selesaikan</button>
                                            <?php } else { ?>
                                                <small class="text-danger font-italic">Total pesanan harus melebihi Rp 1.000.000 untuk dapat memilih metode ini.</small>
                                            <?php } ?>
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
                    <a id="kreditpro_detail" data-toggle="collapse" data-target="#collapsePaymentId04" aria-expanded="<?= $purchase->id ? 'true' : 'false' ?>" aria-controls="collapseOrder" class="see-more-payment"></a>
                </div>
                <!-- End Tombol Collapse 01 -->
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        $('ul.tabs li').click(function() {
            var tab_id = $(this).attr('data-tab');

            $('ul.tabs li').removeClass('current');
            $('.tab-content').removeClass('current');

            $(this).addClass('current');
            $("#" + tab_id).addClass('current');
        })

    })
</script>

<script>
    <?php if(count($term_payment_kredit_pro) > 0){ ?>
    $('#kreditpro').click(function(event) {
        $('#kreditpro_detail').click();
    })
    <?php }?>
    $('#tab-10').click(function(event) {
        var hasil = document.getElementById("price-kreditpro");
        hasil.innerHTML = "<p class = 'h6 price-kreditpro' style = 'color:#B20838; text-align:right;' > Rp <?= number_format($total + $kreditpro30hari, 0, ',', '.') ?><br><label>&nbsp;(30 Hari)</label></p>";
    })
    $('#tab-20').click(function(event) {
        var hasil = document.getElementById("price-kreditpro");
        hasil.innerHTML = "<p class = 'h6 price-kreditpro' style = 'color:#B20838; text-align:right;' > Rp <?= number_format($total + $kreditpro45hari, 0, ',', '.') ?><br><label>&nbsp;(45 Hari)</label></p>";
    })
    $('#tab-30').click(function(event) {
        var hasil = document.getElementById("price-kreditpro");
        hasil.innerHTML = "<p class = 'h6 price-kreditpro' style = 'color:#B20838; text-align:right;' > Rp <?= number_format($total + $kreditpro60hari, 0, ',', '.') ?><br><label>&nbsp;(60 Hari)</label></p>";
    })

    function salinsisa30() {
        element = document.getElementById("jumlahsisa30");
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

    function salinsisa45() {
        element = document.getElementById("jumlahsisa45");
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

    function salinsisa60() {
        element = document.getElementById("jumlahsisa60");
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