<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $this->lang->line("purchase") . " " . $inv->no_do; ?></title>
    <link href="<?php echo $assets ?>styles/style.css?v=<?=FORCAPOS_VERSION?>" rel="stylesheet">
    <style type="text/css">
        html, body { height: 100%; background: #FFF; }
        body:before, body:after { display: none !important; }
        .table th { text-align: center; padding: 5px; }
        .table td { padding: 4px; }
    </style>
</head>

<body>
<div id="wrap">
    <div class="row">
        <div class="col-lg-12">
            <?php if ($logo) { ?>
                <div class="text-center" style="margin-bottom:20px;">
                    <img src="<?= base_url() . 'assets/uploads/logos/' . $biller->logo; ?>"
                         alt="<?= $biller->company ?>">
                </div>
            <?php } ?>
            <div class="clearfix"></div>
            <div class="row padding10">
                <div class="col-xs-5">
                    <h2 class=""><?= $supplier->company ? $supplier->company : $supplier->name; ?></h2>
                        <?= $supplier->company ? "" : "Attn: " . $supplier->name ?>
                        <?php
                        echo $supplier->address . "<br>" . $supplier->city . " " . $supplier->postal_code . " " . $supplier->state . "<br>" . $supplier->country;
                        ?>
                        <br>
                        <br>
                        <table>
                            <?php if ($supplier->vat_no != "-" && $supplier->vat_no != "") { ?>
                            <tr>
                                <td><?= lang("vat_no"); ?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= $supplier->vat_no ?></td>
                            </tr>
                            <?php } ?>
                            <?php if ($supplier->cf1 != "-" && $supplier->cf1 != "") { ?>
                            <tr>
                                <td><?= lang("scf1"); ?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= $supplier->cf1 ?></td>
                            </tr>
                            <?php } ?>
                            <?php if ($supplier->cf2 != "-" && $supplier->cf2 != "") { ?>
                            <tr>
                                <td><?= lang("scf2"); ?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= $supplier->cf2 ?></td>
                            </tr>
                            <?php } ?>
                            <?php if ($supplier->cf3 != "-" && $supplier->cf3 != "") { ?>
                            <tr>
                                <td><?= lang("scf3"); ?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= $supplier->cf3 ?></td>
                            </tr>
                            <?php } ?>
                            <?php if ($supplier->cf4 != "-" && $supplier->cf4 != "") { ?>
                            <tr>
                                <td><?= lang("scf4"); ?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= $supplier->cf4 ?></td>
                            </tr>
                            <?php } ?>
                            <?php if ($supplier->cf5 != "-" && $supplier->cf5 != "") { ?>
                            <tr>
                                <td><?= lang("scf5"); ?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= $supplier->cf5 ?></td>
                            </tr>
                            <?php } ?>
                            <?php if ($supplier->cf6 != "-" && $supplier->cf6 != "") { ?>
                            <tr>
                                <td><?= lang("scf6"); ?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= $supplier->cf6 ?></td>
                            </tr>
                            <?php } ?>
                            <?php if ($supplier->vat_no == null && $supplier->cf1 == null && $supplier->cf2 == null && $supplier->cf3 == null && $supplier->cf4 == null && $supplier->cf5 == null && $supplier->cf6 == null) { ?>
                            <tr>
                                <td>&emsp;</td>
                                <td>&emsp;</td>
                                <td>&emsp;</td>
                            </tr>
                            <?php } ?>
                            <br>
                            <tr>
                                <td>&emsp;</td>
                                <td>&emsp;</td>
                                <td>&emsp;</td>
                            </tr>
                            <tr>
                                <td><?= lang("tel"); ?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= $supplier->phone ?></td>
                            </tr>
                            <tr>
                                <td><?= lang("email"); ?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= $supplier->email ?></td>
                            </tr>
                        </table>
                    <div class="clearfix"></div>
                </div>
                <div class="col-xs-5">
                    <h2 class=""><?= $biller->company != '-' ? $biller->company : $biller->name; ?></h2>
                    <?= $biller->company ? "" : "Attn: " . $biller->name ?>
                    <h3 class=""><?= $warehouse->name ?></h3>
                    <?php
                    echo $biller->address . "<br>" . $biller->city . " " . $biller->postal_code . " " . $biller->state . "<br>" . $biller->country;
                    ?>
                    <br>
                    <br>
                    <table>
                        <?php if ($biller->vat_no != "-" && $biller->vat_no != "") { ?>
                        <tr>
                            <td><?= lang("vat_no"); ?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?= $biller->vat_no ?></td>
                        </tr>
                        <?php } ?>
                        <?php if ($biller->cf1 != "-" && $biller->cf1 != "") { ?>
                        <tr>
                            <td><?= lang("bcf1"); ?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?= $biller->cf1 ?></td>
                        </tr>
                        <?php } ?>
                        <?php if ($biller->cf2 != "-" && $biller->cf2 != "") { ?>
                        <tr>
                            <td><?= lang("bcf2"); ?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?= $biller->cf2 ?></td>
                        </tr>
                        <?php } ?>
                        <?php if ($biller->cf3 != "-" && $biller->cf3 != "") { ?>
                        <tr>
                            <td><?= lang("bcf3"); ?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?= $biller->cf3 ?></td>
                        </tr>
                        <?php } ?>
                        <?php if ($biller->cf4 != "-" && $biller->cf4 != "") { ?>
                        <tr>
                            <td><?= lang("bcf4"); ?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?= $biller->cf4 ?></td>
                        </tr>
                        <?php } ?>
                        <?php if ($biller->cf5 != "-" && $biller->cf5 != "") { ?>
                        <tr>
                            <td><?= lang("bcf5"); ?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?= $biller->cf5 ?></td>
                        </tr>
                        <?php } ?>
                        <?php if ($biller->cf6 != "-" && $biller->cf6 != "") { ?>
                        <tr>
                            <td><?= lang("bcf6"); ?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?= $biller->cf6 ?></td>
                        </tr>
                        <?php } ?>
                        <?php if ($biller->vat_no == null && $biller->cf1 == null && $biller->cf2 == null && $biller->cf3 == null && $biller->cf4 == null && $biller->cf5 == null && $biller->cf6 == null) { ?>
                        <tr>
                            <td>&emsp;</td>
                            <td>&emsp;</td>
                            <td>&emsp;</td>
                        </tr>
                        <?php } ?>
                        <br>
                        <tr>
                            <td>&emsp;</td>
                            <td>&emsp;</td>
                            <td>&emsp;</td>
                        </tr>
                        <tr>
                            <td><?= lang("tel"); ?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?= $biller->phone ?></td>
                        </tr>
                        <tr>
                            <td><?= lang("email"); ?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?= $biller->email ?></td>
                        </tr>
                    </table>
                </div>

            </div>
            <div class="clearfix"></div>
            <div class="row padding10">
                <div class="col-xs-12">
                    <h2 style="margin-top:10px;"><?php echo $this->lang->line("Detail"); ?> :<br/></h2>
                </div>
                <div class="col-xs-5">  
                    <table>
                        <tr>
                            <td><?=lang("pp_number")?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?=$inv->no_pp?></td>
                        </tr>
                        <tr>
                            <td><?=lang("date_pp")?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?=$this->sma->hrsd($inv->tanggal_pp)?></td>
                        </tr>
                        <tr>
                            <td><?=lang("So_Number")?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?=$inv->no_so?></td>
                        </tr>
                        <tr>
                            <td><?=lang("date_so")?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?=$this->sma->hrsd($inv->tanggal_so)?></td>
                        </tr>
                        <tr>
                            <td><?=lang("order_type")?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?=$inv->tipe_order?></td>
                        </tr>
                        <tr>
                            <td><?=lang("transaction_number")?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?=$inv->no_transaksi?></td>
                        </tr>
                    </table>
                    <div class="clearfix"></div>
                </div>
                <div class="col-xs-5">
                    <table>
                        <tr>
                            <td><?=lang("Spj_Number")?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?=$inv->no_spj?></td>
                        </tr>
                        <tr>
                            <td><?=lang("date_spj")?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?=$this->sma->hrsd($inv->tanggal_spj)?></td>
                        </tr>
                        <tr>
                            <td><?=lang("Police_No")?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?=$inv->no_polisi?></td>
                        </tr>
                        <tr>
                            <td><?=lang("Driver_Name")?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?=$inv->nama_sopir?></td>
                        </tr>
                        <tr>
                            <td><?=lang("plant")?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?=$inv->kode_plant?></td>
                        </tr>
                        <tr>
                            <td><?=lang("plant_name")?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?=$inv->nama_plant?></td>
                        </tr>
                    </table>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="clearfix"></div>
            <div class="row padding10">
                <div class="col-xs-5">
                <div class="col-xs-2"><i class="fa fa-3x fa-file-text-o padding010 text-muted"></i></div>
                    <div class="col-xs-10">
                        <table class="bold">
                        <tr>
                            <td><?=lang("Do_Number")?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?=$inv->no_do?></td>
                        </tr>
                        <tr>
                            <td><?=lang("date_do")?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?=$this->sma->hrsd($inv->tanggal_do)?></td>
                        </tr>
                        <tr>
                            <td><?=lang("status")?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?=$inv->status_penerimaan?></td>
                        </tr>
                    </table>
                    <p>&nbsp;</p>
                </div>
                <div class="clearfix"></div>
                </div>
                <div class="col-xs-5 pull-right">
                    <div class="col-xs-12 text-right order_barcodes">
                        <?= $this->sma->save_barcode($inv->no_do, 'code128', 66, false); ?>&emsp;&emsp;
                        <!-- <?= $this->sma->qrcode('link', urlencode(site_url('deliveries_smig/view/' . $inv->id)), 2); ?> -->
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>


            <div class="clearfix"></div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped print-table order-table">
                    <thead>
                    <tr>
                        <th><?= lang("no"); ?></th>
                        <th><?= lang("description"); ?> (<?= lang("code"); ?>)</th>
                        <th><?= lang("quantity"); ?></th>
                        <th><?= lang("unit_price"); ?></th>
                        <?php
                        if ($Settings->tax1 && $inv->product_tax > 0) {
                            echo '<th>' . lang("tax") . '</th>';
                        }
                        if ($Settings->product_discount) {
                            echo '<th>' . lang("discount") . '</th>';
                        }
                        ?>
                        <th><?= lang("subtotal"); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $r = 1;
                    foreach ($rows as $row):
                        ?>
                        <tr>
                            <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                            <td style="vertical-align:middle;">
                                <?= $row->product_code.' - '.$row->product_name; ?>
                                <?= $row->details ? '<br>' . $row->details : ''; ?></td>
                            <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code; ?></td>
                            <td style="text-align:right; width:100px;"><?= $this->sma->formatMoney($row->unit_price); ?></td>
                            <?php
                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 && $row->tax_code ? '<small>(' . $row->tax_code . ')</small> ' : '') . $this->sma->formatMoney($row->item_tax) . '</td>';
                            }
                            if ($Settings->product_discount) {
                                echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->sma->formatMoney($row->item_discount) . '</td>';
                            }
                            ?>
                            <td style="text-align:right; width:120px;"><?= $this->sma->formatMoney($row->subtotal); ?></td>
                        </tr>
                        <?php
                        $r++;
                    endforeach;
                    ?>
                    </tbody>
                    <tfoot>
                    <?php
                    $col = 4;
                    if ($Settings->product_discount) {
                        $col++;
                    }
                    if ($Settings->tax1 && $inv->product_tax > 0) {
                        $col++;
                    }
                    if ($Settings->product_discount && $Settings->tax1 && $inv->product_tax > 0) {
                        $tcol = $col - 2;
                    } elseif ($Settings->product_discount) {
                        $tcol = $col - 1;
                    } elseif ($Settings->tax1 && $inv->product_tax > 0) {
                        $tcol = $col - 1;
                    } else {
                        $tcol = $col;
                    }
                    ?>
                    <tr>
                        <td colspan="<?= $tcol; ?>" style="text-align:right;"><?= lang("total"); ?>
                            (<?= $default_currency->code; ?>)
                        </td>
                        <?php
                        if ($Settings->tax1 && $inv->product_tax > 0) {
                            echo '<td style="text-align:right;">' . $this->sma->formatMoney($inv->product_tax) . '</td>';
                        }
                        if ($Settings->product_discount) {
                            echo '<td style="text-align:right;">' . $this->sma->formatMoney($inv->product_discount) . '</td>';
                        }
                        ?>
                        <td style="text-align:right;"><?= $this->sma->formatMoney($inv->total + $inv->product_tax); ?></td>
                    </tr>
                    <?php
                    if ($inv->order_discount != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right;">' . lang("order_discount") . ' (' . $default_currency->code . ')</td><td style="text-align:right;">'.($inv->order_discount_id ? '<small>('.$inv->order_discount_id.')</small> ' : '') . $this->sma->formatMoney($inv->order_discount) . '</td></tr>';
                    }
                    if ($Settings->tax2 && $inv->order_tax != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right;">' . lang("order_tax") . ' (' . $default_currency->code . ')</td><td style="text-align:right;">' . $this->sma->formatMoney($inv->order_tax) . '</td></tr>';
                    }
                    if ($inv->shipping != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right;">' . lang("shipping") . ' (' . $default_currency->code . ')</td><td style="text-align:right;">' . $this->sma->formatMoney($inv->shipping) . '</td></tr>';
                    }
                    ?>
                    <tr>
                        <td colspan="<?= $col; ?>"
                            style="text-align:right; font-weight:bold;"><?= lang("total_amount"); ?>
                            (<?= $default_currency->code; ?>)
                        </td>
                        <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney($inv->grand_total); ?></td>
                    </tr>

                    </tfoot>
                </table>
            </div>
            <div class="clearfix"></div>
            <div class="row">
                <div class="col-xs-12">
                    <?php if ($inv->note || $inv->note != "") { ?>
                        <div class="well well-sm">
                            <p class="bold"><?= lang("note"); ?>:</p>

                            <div><?= $this->sma->decode_html($inv->note); ?></div>
                        </div>
                    <?php } ?>
                </div>
                <div class="clearfix"></div>
                <div class="col-xs-4  pull-left">
                    <p><?= lang("biller"); ?>: <?= $biller->company != '-' ? $biller->company : $biller->name; ?> </p>

                    <p>&nbsp;</p>

                    <p>&nbsp;</p>
                    <hr>
                    <p><?= lang("stamp_sign"); ?></p>
                </div>
                <div class="col-xs-4  pull-right">
                    <p><?= lang("supplier"); ?>: <?= $supplier->company ? $supplier->company : $supplier->name; ?> </p>

                    <p>&nbsp;</p>

                    <p>&nbsp;</p>
                    <hr>
                    <p><?= lang("stamp_sign"); ?></p>
                </div>
            </div>

        </div>
    </div>
</div>
</body>
</html>