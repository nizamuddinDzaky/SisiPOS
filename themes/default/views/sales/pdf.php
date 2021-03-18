<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $this->lang->line('purchase') . ' ' . $po ? $po->cf1 : $inv->reference_no; ?></title>
    <link href="<?php echo $assets ?>styles/style.css?v=<?=FORCAPOS_VERSION?>" rel="stylesheet">
    <style type="text/css">
        html, body {
            height: 100%;
            background: #FFF;
        }
        body:before, body:after {
            display: none !important;
        }
        .table th {
            text-align: center;
            padding: 5px;
        }
        .table td {
            padding: 4px;
        }
    </style>
</head>

<body>
<div id="wrap">
    <div class="row">
        <div class="col-lg-12">
            <?php if ($logo) { ?>
                <div class="text-center" style="margin-bottom:20px;">
                    <img src="<?= base_url('assets/uploads/logos/' . $biller->logo); ?>" alt="<?= $biller->company != '-' ? $biller->company : $biller->name; ?>" width="200">
                </div>
            <?php }
            ?>
            <div class="clearfix"></div>
            <div class="row padding11">
                <div class="col-xs-6">
                    <h2 class=""><?= $biller->company != '-' ? $biller->company : $biller->name; ?></h2>
                    <?= $biller->company ? '' : 'Attn: ' . $biller->name; ?>
                    <?php
                        echo $biller->address . '<br />' . $biller->city . ' ' . $biller->postal_code . ' ' . $biller->state . '<br />' . $biller->country;
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
                    <div class="clearfix"></div>
                </div>
                <div class="col-xs-5">
                    <h2 class=""><?= $customer->company ? $customer->company : $customer->name; ?></h2>
                    <?= $customer->company ? '' : 'Attn: ' . $customer->name; ?>
                    <?php
                        echo $customer->address . '<br />' . $customer->city . ' ' . $customer->postal_code . ' ' . $customer->state . '<br />' . $customer->country;
                    ?>
                    <br>
                    <br>
                    <table>
                        <?php if ($customer->vat_no != "-" && $customer->vat_no != "") { ?>
                        <tr>
                            <td><?= lang("vat_no"); ?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?= $customer->vat_no ?></td>
                        </tr>
                        <?php } ?>
                        <?php if ($customer->cf1 != "-" && $customer->cf1 != "") { ?>
                        <tr>
                            <td><?= lang("ccf1"); ?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?= $customer->cf1 ?></td>
                        </tr>
                        <?php } ?>
                        <?php if ($customer->cf2 != "-" && $customer->cf2 != "") { ?>
                        <tr>
                            <td><?= lang("ccf2"); ?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?= $customer->cf2 ?></td>
                        </tr>
                        <?php } ?>
                        <?php if ($customer->cf3 != "-" && $customer->cf3 != "") { ?>
                        <tr>
                            <td><?= lang("ccf3"); ?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?= $customer->cf3 ?></td>
                        </tr>
                        <?php } ?>
                        <?php if ($customer->cf4 != "-" && $customer->cf4 != "") { ?>
                        <tr>
                            <td><?= lang("ccf4"); ?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?= $customer->cf4 ?></td>
                        </tr>
                        <?php } ?>
                        <?php if ($customer->cf5 != "-" && $customer->cf5 != "") { ?>
                        <tr>
                            <td><?= lang("ccf5"); ?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?= $customer->cf5 ?></td>
                        </tr>
                        <?php } ?>
                        <?php if ($customer->cf6 != "-" && $customer->cf6 != "") { ?>
                        <tr>
                            <td><?= lang("ccf6"); ?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?= $customer->cf6 ?></td>
                        </tr>
                        <?php } ?>
                        <?php if ($customer->vat_no == null && $customer->cf1 == null && $customer->cf2 == null && $customer->cf3 == null && $customer->cf4 == null && $customer->cf5 == null && $customer->cf6 == null) { ?>
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
                            <td><?= $customer->phone ?></td>
                        </tr>
                        <tr>
                            <td><?= lang("email"); ?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?= $customer->email ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="clearfix"></div>
            <br>
            <br>
            <div class="row padding11">
                <div class="col-xs-6">
                    <span class="bold"><?= $Settings->site_name; ?></span><br>
                    <?= $warehouse->name ?>

                    <?php
                        echo $warehouse->address . '<br>';
                    ?>
                    <table>
                        <tr>
                            <td><?= lang("tel"); ?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?= $warehouse->phone ?></td>
                        </tr>
                        <tr>
                            <td><?= lang("email"); ?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?= $warehouse->email ?></td>
                        </tr>
                    </table>
                    <div class="clearfix"></div>
                    <?php if($po || $inv->delivery_method){?>
                    <div class="row padding10">
                        <div class="col-xs-12">
                            <span class="bold">AksesToko</span><br>
                            <?php
                                echo lang('payment_method') .': ' . lang($po->payment_method);
                                if($po->payment_method == 'kredit'){
                                    echo '<br>'. 'TOP' .': ' . lang($po->payment_duration).' Days';
                                    echo '<br>'. lang('due_date') .': ' . ($po->payment_deadline && $po->payment_deadline != '0000-00-00 00:00:00' ? $this->sma->hrsd($po->payment_deadline) : '-');
                                }
                                echo '<br>'. lang('delivery_method') .': ' . ($inv->delivery_method ? lang($inv->delivery_method) : '-');
                                if($promo){
                                    echo '<br>'. lang('promo_aksestoko') .': ' . ($promo->code_promo);
                                }
                            ?>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                    <?php }?>
                </div>
                <div class="col-xs-5">
                    <span class="bold"></span><br>
                    <table class="bold">
                        <tr>
                            <td>&emsp;</td>
                            <td>&emsp;</td>
                            <td>&emsp;</td>
                        </tr>
                        <tr>
                            <td><?= lang("date"); ?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?= $this->sma->hrld($inv->date); ?></td>
                        </tr>
                        <tr>
                            <td><?= lang("ref"); ?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?= $po ? $po->cf1 : $inv->reference_no ?></td>
                        </tr>
                        <tr>
                            <td><?= lang("biller_id"); ?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?= $po ? $po->supplier_id :$inv->biller_id ?></td>
                        </tr>
                        <?php if (!empty($inv->return_sale_ref)) { ?>
                        <tr>
                            <td><?= lang("return_ref"); ?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?= $inv->return_sale_ref ?></td>
                        </tr>
                        <?php } ?>
                        <tr>
                            <td>&emsp;</td>
                            <td>&emsp;</td>
                            <td>&emsp;</td>
                        </tr>
                    </table>
                    <div class="clearfix"></div>
                    <div class="order_barcodes">
                        <br>
                            <?= $this->sma->save_barcode($po ? $po->cf1 : $inv->reference_no, 'code128', 66, false); ?>
                        <br>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
            <br>
            

            <div class="clearfix"></div>
            <?php
                $col = 4;
                if ( $Settings->product_discount && $inv->product_discount != 0) {
                    $col++;
                }
                if ($Settings->tax1 && $inv->product_tax > 0) {
                    $col++;
                }
                if ( $Settings->product_discount && $inv->product_discount != 0 && $Settings->tax1 && $inv->product_tax > 0) {
                    $tcol = $col - 2;
                } elseif ( $Settings->product_discount && $inv->product_discount != 0) {
                    $tcol = $col - 1;
                } elseif ($Settings->tax1 && $inv->product_tax > 0) {
                    $tcol = $col - 1;
                } else {
                    $tcol = $col;
                }
            ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead>
                    <tr>
                        <th><?= lang('no'); ?></th>
                        <th><?= lang('description'); ?> (<?= lang('code'); ?>)</th>
                        <th><?= lang("quantity"); ?></th>
                        <th><?= lang('unit_price'); ?></th>
                        <?php
                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                echo '<th>' . lang('tax') . '</th>';
                            }
                            if ( $Settings->product_discount && $inv->product_discount != 0) {
                                echo '<th>' . lang('discount') . '</th>';
                            }
                        ?>
                        <th><?= lang('subtotal'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $r = 1;
                        foreach ($rows as $row):
                            ?>
                            <tr>
                                <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                <td style="vertical-align:middle;">
                                    <?= $row->product_code.' - '.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                    <?= $row->details ? '<br>' . $row->details : ''; ?>
                                    <?= $row->serial_no ? '<br>' . $row->serial_no : ''; ?>
                                </td>
                                <?php if($inv->sale_type != 'booking' || $inv->sale_status != 'closed'){ ?>
                                    <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code; ?></td>
                                    <td style="text-align:right; width:90px;"><?= $this->sma->formatMoney(($po ? $row->unit_cost : $row->unit_price)); ?></td>
                                <?php }else{?>
                                    <td style="text-align:right; width:90px;"><?= $this->sma->formatQuantity($row->quantity).' '.$row->product_unit_code; ?></td>
                                    <td style="text-align:right; width:90px;"><?= $this->sma->formatMoney(($po ? $row->unit_cost : $row->unit_price)); ?></td>
                                <?php
                                } ?>
                                
                                <?php
                                    if ($Settings->tax1 && $inv->product_tax > 0) {
                                        echo '<td style="width: 90px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 && $row->tax_code ? '<small>(' . $row->tax_code . ')</small> ' : '') . $this->sma->formatMoney($row->item_tax) . '</td>';
                                    }
                                    if ( $Settings->product_discount && $inv->product_discount != 0) {
                                        echo '<td style="width: 90px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->sma->formatMoney($row->item_discount) . '</td>';
                                    }
                                ?>
                                
                                <td style="vertical-align:middle; text-align:right; width:110px;"><?= $this->sma->formatMoney($row->subtotal); ?></td>
                            </tr>
                            <?php
                            $r++;
                        endforeach;
                        if ($return_rows) {
                            echo '<tr class="warning"><td colspan="'.($col+1).'" class="no-border"><strong>'.lang('returned_items').'</strong></td></tr>';
                            foreach ($return_rows as $row):
                            ?>
                                <tr class="warning">
                                    <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                    <td style="vertical-align:middle;">
                                        <?= $row->product_code.' - '.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                        <?= $row->details ? '<br>' . $row->details : ''; ?>
                                        <?= $row->serial_no ? '<br>' . $row->serial_no : ''; ?>
                                    </td>
                                    <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->quantity).' '.$row->product_unit_code; ?></td>
                                    <td style="text-align:right; width:100px;"><?= $this->sma->formatMoney($row->unit_price); ?></td>
                                    <?php
                                    if ($Settings->tax1 && $inv->product_tax > 0) {
                                        echo '<td style="text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 && $row->tax_code ? '<small>('.$row->tax_code.')</small>' : '') . ' ' . $this->sma->formatMoney($row->item_tax) . '</td>';
                                    }
                                    if ($Settings->product_discount && $inv->product_discount != 0) {
                                        echo '<td style="text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->sma->formatMoney($row->item_discount) . '</td>';
                                    }
                                    ?>
                                    <td style="text-align:right; width:120px;"><?= $this->sma->formatMoney($row->subtotal); ?></td>
                                </tr>
                                <?php
                                $r++;
                            endforeach;
                        }
                    ?>
                    </tbody>
                    <tfoot>
                    
                    <?php if ($inv->grand_total != $inv->total) {
                        ?>
                        <tr>
                            <td colspan="<?= $tcol; ?>" style="text-align:right;"><?= lang('total'); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <?php
                                if ($Settings->tax1 && $inv->product_tax > 0) {
                                    echo '<td style="text-align:right;">' . $this->sma->formatMoney($return_sale ? ($inv->product_tax+$return_sale->product_tax) : $inv->product_tax) . '</td>';
                                }
                                if ( $Settings->product_discount && $inv->product_discount != 0) {
                                    echo '<td style="text-align:right;">' . $this->sma->formatMoney($return_sale ? ($inv->product_discount+$return_sale->product_discount) : $inv->product_discount) . '</td>';
                                }
                            ?>
                            <td style="text-align:right;"><?= $this->sma->formatMoney($return_sale ? (($inv->total + $inv->product_tax)+($return_sale->total + $return_sale->product_tax)) : ($inv->total + $inv->product_tax)); ?></td>
                        </tr>
                    <?php }
                    ?>
                    <?php
                    if ($return_sale) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right;">' . lang("return_total") . ' (' . $default_currency->code . ')</td><td style="text-align:right;">' . $this->sma->formatMoney($return_sale->grand_total) . '</td></tr>';
                    }
                    if ($inv->surcharge != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right;">' . lang("return_surcharge") . ' (' . $default_currency->code . ')</td><td style="text-align:right;">' . $this->sma->formatMoney($inv->surcharge) . '</td></tr>';
                    }
                    if ((int)$inv->charge != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right;">' . lang("charge") . ' (' . $default_currency->code . ')</td><td style="text-align:right;">' . $this->sma->formatMoney($inv->charge) . '</td></tr>';
                    }
                    if ((int)$inv->charge_third_party != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right;">' . lang("charge_third_party") . ' (' . $default_currency->code . ')</td><td style="text-align:right;">' . $this->sma->formatMoney($inv->charge_third_party) . '</td></tr>';
                    }
                    if ((int)$inv->correction_price != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right;">' . lang("correction_price") . ' (' . $default_currency->code . ')</td><td style="text-align:right;">' . $this->sma->formatMoney($inv->correction_price) . '</td></tr>';
                    }
                    ?>
                    <?php if ($inv->order_discount != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right;">' . lang('order_discount') . ($promo ? ' [AksesToko=' . $promo->code_promo . ']' : ''). ' (' . $default_currency->code . ')</td><td style="text-align:right;">'.($inv->order_discount_id ? '' /* <small>('.$inv->order_discount_id.')</small> */ : '') . $this->sma->formatMoney($return_sale ? ($inv->order_discount+$return_sale->order_discount) : $inv->order_discount) . '</td></tr>';
                    }
                    ?>
                    <?php if ($Settings->tax2 && $inv->order_tax != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right;">' . lang('order_tax') . ' (' . $default_currency->code . ')</td><td style="text-align:right;">' . $this->sma->formatMoney($return_sale ? ($inv->order_tax+$return_sale->order_tax) : $inv->order_tax) . '</td></tr>';
                    }
                    ?>
                    <?php if ($inv->shipping != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right;">' . lang('shipping') . ' (' . $default_currency->code . ')</td><td style="text-align:right;">' . $this->sma->formatMoney($inv->shipping) . '</td></tr>';
                    }
                    ?>
                    <tr>
                        <td colspan="<?= $col; ?>"
                            style="text-align:right; font-weight:bold;"><?= lang('total_amount'); ?>
                            (<?= $default_currency->code; ?>)
                        </td>
                        <?php if($inv->sale_type != 'booking' || $inv->sale_status != 'closed'){ ?>
                            <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney($return_sale ? ($inv->grand_total+$return_sale->grand_total) : $inv->grand_total); ?></td>
                        <?php }else { ?>
                            <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney($inv->grand_total); ?></td>
                        <?php } ?>
                        
                    </tr>

                    <tr>
                        <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;"><?= lang('paid'); ?>
                            (<?= $default_currency->code; ?>)
                        </td>
                        <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid); ?></td>
                    </tr>
                    <tr>
                        <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;"><?= lang('balance'); ?>
                            (<?= $default_currency->code; ?>)
                        </td>
                        <?php if($inv->sale_type != 'booking' || $inv->sale_status != 'closed'){ ?>
                            <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney(($return_sale ? ($inv->grand_total+$return_sale->grand_total) : $inv->grand_total) - ($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid)); ?></td>
                        <?php }else { ?>
                            <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney($inv->grand_total - $inv->paid); ?></td>
                        <?php } ?>
                    </tr>

                    </tfoot>
                </table>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <?php if ($inv->note || $inv->note != '') { ?>
                        <div class="well well-sm">
                            <p class="bold"><?= lang('note'); ?>:</p>

                            <div><?= $this->sma->decode_html($inv->note); ?></div>
                        </div>
                    <?php }
                    ?>
                </div>
                <div class="clearfix"></div>
                <div class="col-xs-4  pull-left">
                    <p style="height: 80px;"><?= lang('seller'); ?>
                        : <?= $biller->company != '-' ? $biller->company : $biller->name; ?> </p>
                    <hr>
                    <p><?= lang('stamp_sign'); ?></p>
                </div>
                <div class="col-xs-4  pull-right">
                    <p style="height: 80px;"><?= lang('customer'); ?>
                        : <?= $customer->company ? $customer->company : $customer->name; ?> </p>
                    <hr>
                    <p><?= lang('stamp_sign'); ?></p>
                </div>
                <div class="clearfix"></div>
                <?php if ($customer->award_points != 0 && $Settings->each_spent > 0) { ?>
                <div class="col-xs-4 pull-right">
                    <div class="well well-sm">
                        <?=
                        '<p>'.lang('this_sale').': '.floor(($inv->grand_total/$Settings->each_spent)*$Settings->ca_point)
                        .'<br>'.
                        lang('total').' '.lang('award_points').': '. $customer->award_points . '</p>';?>
                    </div>
                </div>
                <?php } ?>
            </div>

        </div>
    </div>
</div>
</body>
</html>