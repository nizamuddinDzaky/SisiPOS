<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg no-modal-header">
    <div class="modal-content">
        <div class="modal-body">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <a href="<?= site_url('sales/modal_view_print/' . $inv->id); ?>" target="_blank" type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </a>
            <?php if ($logo) { ?>
                <div class="text-center" style="margin-bottom:20px;">
                    <img src="<?= avatar_image_logo($this->session->userdata('avatar'), $Settings->logo) ?>" alt="<?= $biller->company != '-' ? $biller->company : $biller->name; ?>" height="150px">
                </div>
            <?php } ?>
            <div class="well well-sm">
                <div class="row bold">
                    <div class="col-xs-6">
                        <table class="bold">
                            <tr>
                                <td><?= lang("date"); ?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= $this->sma->hrld($inv->date); ?></td>
                            </tr>
                            <tr>
                                <td><?= lang("reference_no") ?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= $inv->reference_no ?></td>
                            </tr>
                            <?php if (!empty($inv->return_sale_ref)) { ?>
                                <tr>
                                    <td><?= lang("return_ref") ?></td>
                                    <td> &emsp;:&emsp; </td>
                                    <td><?= $inv->return_sale_ref ?></td>
                                </tr>
                            <?php if ($inv->return_id) {
                                    echo ' <a data-target="#myModal2" data-toggle="modal" href="' . site_url('sales/modal_view/' . $inv->return_id) . '"><i class="fa fa-external-link no-print"></i></a><br>';
                                } else {
                                    echo '<br>';
                                }
                            } ?>
                            <tr>
                                <td><?= lang("sale_status") ?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= lang($inv->sale_status); ?></td>
                            </tr>
                            <tr>
                                <td><?= lang("payment_status") ?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= lang($inv->payment_status); ?></td>
                            </tr>
                            <?php if ($inv->payment_status != 'paid') {?>
                            <tr>
                                <td><?= lang("payment_term") ?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= lang($inv->top); ?></td>
                            </tr>
                            <?php } ?>
                        </table>
                    </div>
                    <div class="col-xs-6 text-right order_barcodes">
                        <?= $this->sma->save_barcode($inv->reference_no, 'code128', 66, false); ?>
                        <!--<?= $this->sma->qrcode('link', urlencode(site_url('sales/view/' . $inv->id)), 2); ?>-->
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>

            <?php if ($po || $inv->delivery_method) { ?>
                <div class="well well-sm">
                    <div class="row bold">
                        <div class="col-xs-12">
                            <h2 class="bold" style="margin: 0; margin-bottom: 5px;">AksesToko Order Detail</h2>
                            <table>
                                <tr>
                                    <td>TOP</td>
                                    <td> &emsp;:&emsp; </td>
                                    <td><?= lang($po->payment_duration) ?> Days</td>
                                </tr>
                                <tr>
                                    <td><?= lang('due_date') ?></td>
                                    <td> &emsp;:&emsp; </td>
                                    <td><?= ($po->payment_deadline && $po->payment_deadline != '0000-00-00 00:00:00' ? $this->sma->hrsd($po->payment_deadline) : '-') ?></td>
                                </tr>
                                <tr>
                                    <td><?= lang('delivery_method') ?></td>
                                    <td> &emsp;:&emsp; </td>
                                    <td><?= ($inv->delivery_method ? lang($inv->delivery_method) : '-') ?></td>
                                </tr>
                                <tr>
                                    <td><?= lang('payment_method') ?></td>
                                    <td> &emsp;:&emsp; </td>
                                    <td><?= lang($po->payment_method) ?></td>
                                </tr>
                                <?php
                                if ($po->payment_method == 'kredit_pro') { ?>
                                    <tr>
                                        <td>Status Kredit Pro</td>
                                        <td> &emsp;:&emsp; </td>
                                        <td>
                                            <?php
                                            if ($po->payment_status == 'waiting') {
                                                echo lang('credit_reviewed');
                                            } else if ($po->payment_status == 'accept') {
                                                echo lang('credit_received');
                                            } else if ($po->payment_status == 'reject') {
                                                echo lang('credit_declined');
                                            } else if ($po->payment_status == 'partial') {
                                                echo lang('kredit_partial');
                                            } else if ($po->payment_status == 'paid') {
                                                echo lang('already_paid');
                                            } else if ($po->payment_status == 'pending') {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </table>
                        </div>
                    </div>
                </div>
            <?php } else if ($inv->client_id == 'atl') { ?>
                <div class="well well-sm">
                    <div class="row bold">
                        <div class="col-xs-12">
                            <h2 class="bold" style="margin: 0; margin-bottom: 5px;">AksesToko Order Detail</h2>
                            <table>
                                <tr>
                                    <td><?= lang("reference_no") ?></td>
                                    <td> &emsp;:&emsp; </td>
                                    <td><?= $atl_order->ordercode?></td>
                                </tr>
                                <tr>
                                    <td>TOP</td>
                                    <td> &emsp;:&emsp; </td>
                                    <td><?= (int) $atl_order->tempo . ' ' . lang('day') ?></td>
                                </tr>
                                <tr>
                                    <td><?= lang('delivery_method') ?></td>
                                    <td> &emsp;:&emsp; </td>
                                    <td><?= lang($atl_order->delivery_method) ?></td>
                                </tr>
                                <tr>
                                    <td><?= lang('payment_method') ?></td>
                                    <td> &emsp;:&emsp; </td>
                                    <td><?= lang($atl_order->payment_method) ?></td>
                                </tr>
                                <?php
                                if ($atl_order->payment_method == 'kredit_pro') { ?>
                                    <tr>
                                        <td>Status Kredit Pro</td>
                                        <td> &emsp;:&emsp; </td>
                                        <td>
                                            <?php
                                            switch ($atl_kreditpro_status->status) {
                                                case 'waiting':
                                                    echo lang('credit_reviewed');
                                                    break;
                                                case 'accept':
                                                    echo lang('credit_received');
                                                    break;
                                                case 'reject':
                                                    echo lang('credit_declined');
                                                    break;
                                                default:
                                                    echo '-';
                                                    break;
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </table>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <div class="row" style="margin-bottom:15px;">
                <div class="col-xs-6">
                    <h2 style="margin-top:10px;"><?php echo $this->lang->line("From"); ?> :<br /></h2>
                    <h2 style="margin-top:10px;"><?= $biller->company != '-' ? $biller->company : $biller->name; ?></h2>
                    <?= $biller->company ? "" : "Attn: " . $biller->name ?>

                    <?php
                    echo $biller->address . "<br>" . $biller->city . " " . $biller->postal_code . " " . $biller->state . "<br>" . $biller->country;
                    ?>
                    <table>
                        <br>
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
                <div class="col-xs-6">
                    <h2 style="margin-top:10px;"><?php echo $this->lang->line("To"); ?> :<br /></h2>
                    <h2 style="margin-top:10px;"><?= $customer->company ? $customer->company : $customer->name; ?></h2>
                    <?= $customer->company ? "" : "Attn: " . $customer->name ?>

                    <?php
                    echo $customer->address . "<br>" . $customer->city . " " . $customer->postal_code . " " . $customer->state . "<br>" . $customer->country;
                    ?>
                    <table>
                        <br>
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

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped print-table order-table">

                    <thead>

                        <tr>
                            <th><?= lang("no"); ?></th>
                            <th><?= lang("description"); ?></th>
                            <?php if ($inv->sale_status == "closed") { ?>
                                <th><?= lang("quantity_order"); ?></th>
                                <th><?= lang("quantity_sent"); ?></th>
                            <?php } else { ?>
                                <th><?= lang("quantity"); ?></th>
                            <?php } ?>
                            <th><?= lang("unit_price"); ?></th>
                            <?php
                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                echo '<th>' . lang("tax") . '</th>';
                            }
                            if ($Settings->product_discount && $inv->product_discount != 0) {
                                echo '<th>' . lang("discount") . '</th>';
                            }
                            ?>
                            <th><?= lang("subtotal"); ?></th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php $r = 1;
                        $tax_summary = array();
                        foreach ($rows as $row) :
                        ?>
                            <tr>
                                <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                <td style="vertical-align:middle;">
                                    <?= $row->product_code . ' - ' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                    <?= $row->details ? '<br>' . $row->details : ''; ?>
                                    <?= $row->serial_no ? '<br>' . $row->serial_no : ''; ?>
                                </td>
                                <?php if ($inv->sale_status == "closed") { ?>
                                    <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->unit_quantity) . ' ' . $row->product_unit_code; ?></td>
                                    <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->sent_quantity) . ' ' . $row->product_unit_code; ?></td>
                                <?php } else { ?>
                                    <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->unit_quantity) . ' ' . $row->product_unit_code; ?></td>
                                <?php } ?>
                                <td style="text-align:right; width:100px;"><?= $this->sma->formatMoney($row->unit_price); ?></td>
                                <?php
                                if ($Settings->tax1 && $inv->product_tax > 0) {
                                    echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 && $row->tax_code ? '<small>(' . $row->tax_code . ')</small>' : '') . ' ' . $this->sma->formatMoney($row->item_tax) . '</td>';
                                }
                                if ($Settings->product_discount && $inv->product_discount != 0) {
                                    echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->sma->formatMoney($row->item_discount) . '</td>';
                                }
                                ?>
                                <td style="text-align:right; width:120px;"><?= $this->sma->formatMoney($row->subtotal); ?></td>
                            </tr>
                            <?php
                            $r++;
                        endforeach;
                        if ($return_rows) {
                            echo '<tr class="warning"><td colspan="100%" class="no-border"><strong>' . lang('returned_items') . '</strong></td></tr>';
                            foreach ($return_rows as $row) :
                            ?>
                                <tr class="warning">
                                    <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                    <td style="vertical-align:middle;">
                                        <?= $row->product_code . ' - ' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                        <?= $row->details ? '<br>' . $row->details : ''; ?>
                                        <?= $row->serial_no ? '<br>' . $row->serial_no : ''; ?>
                                    </td>
                                    <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->quantity) . ' ' . $row->product_unit_code; ?></td>
                                    <td style="text-align:right; width:100px;"><?= $this->sma->formatMoney($row->unit_price); ?></td>
                                    <?php
                                    if ($Settings->tax1 && $inv->product_tax > 0) {
                                        echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 && $row->tax_code ? '<small>(' . $row->tax_code . ')</small>' : '') . ' ' . $this->sma->formatMoney($row->item_tax) . '</td>';
                                    }
                                    if ($Settings->product_discount && $inv->product_discount != 0) {
                                        echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->sma->formatMoney($row->item_discount) . '</td>';
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
                        <?php
                        $col = 4;
                        if ($Settings->product_discount && $inv->product_discount != 0) {
                            $col++;
                        }
                        if ($Settings->tax1 && $inv->product_tax > 0) {
                            $col++;
                        }
                        if ($inv->sale_status == 'closed') {
                            $col++;
                        }
                        if ($Settings->product_discount && $inv->product_discount != 0 && $Settings->tax1 && $inv->product_tax > 0) {
                            $tcol = $col - 2;
                        } elseif ($Settings->product_discount && $inv->product_discount != 0) {
                            $tcol = $col - 1;
                        } elseif ($Settings->tax1 && $inv->product_tax > 0) {
                            $tcol = $col - 1;
                        } else {
                            $tcol = $col;
                        }
                        ?>
                        <?php if ($inv->grand_total != $inv->total) { ?>
                            <tr>
                                <td colspan="<?= $tcol; ?>" style="text-align:right; padding-right:10px;"><?= lang("total"); ?>
                                    (<?= $default_currency->code; ?>)
                                </td>
                                <?php
                                if ($Settings->tax1 && $inv->product_tax > 0) {
                                    echo '<td style="text-align:right;">' . $this->sma->formatMoney($return_sale ? ($inv->product_tax + $return_sale->product_tax) : $inv->product_tax) . '</td>';
                                }
                                if ($Settings->product_discount && $inv->product_discount != 0) {
                                    echo '<td style="text-align:right;">' . $this->sma->formatMoney($return_sale ? ($inv->product_discount + $return_sale->product_discount) : $inv->product_discount) . '</td>';
                                }
                                ?>
                                <td style="text-align:right; padding-right:10px;"><?= $this->sma->formatMoney($return_sale ? (($inv->total + $inv->product_tax) + ($return_sale->total + $return_sale->product_tax)) : ($inv->total + $inv->product_tax)); ?></td>
                            </tr>
                        <?php } ?>
                        <?php
                        if ($return_sale) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("return_total") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($return_sale->grand_total) . '</td></tr>';
                        }
                        if ($inv->surcharge != 0) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("return_surcharge") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($inv->surcharge) . '</td></tr>';
                        }
                        if ((int) $inv->charge != 0) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("charge") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($inv->charge) . '</td></tr>';
                        }
                        if ((int) $inv->correction_price != 0) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("correction_price") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($inv->correction_price) . '</td></tr>';
                        }
                        ?>
                        <?php if ($inv->order_discount != 0) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("order_discount") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . ($inv->order_discount_id ? '<small>(' . $inv->order_discount_id . ')</small> ' : '') . $this->sma->formatMoney($return_sale ? ($inv->order_discount + $return_sale->order_discount) : $inv->order_discount) . '</td></tr>';
                        }
                        ?>
                        <?php if ($Settings->tax2 && $inv->order_tax != 0) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;">' . lang("order_tax") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($return_sale ? ($inv->order_tax + $return_sale->order_tax) : $inv->order_tax) . '</td></tr>';
                        }
                        ?>
                        <?php if ($inv->shipping != 0) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("shipping") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($inv->shipping) . '</td></tr>';
                        }
                        ?>
                        <tr>
                            <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;"><?= lang("total_amount"); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td style="text-align:right; padding-right:10px; font-weight:bold;"><?= $this->sma->formatMoney($return_sale ? ($inv->grand_total + $return_sale->grand_total) : $inv->grand_total); ?></td>
                        </tr>
                        <tr>
                            <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;"><?= lang("paid"); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney($return_sale ? ($inv->paid + $return_sale->paid) : $inv->paid); ?></td>
                        </tr>
                        <tr>
                            <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;"><?= lang("balance"); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney(($return_sale ? ($inv->grand_total + $return_sale->grand_total) : $inv->grand_total) - ($return_sale ? ($inv->paid + $return_sale->paid) : $inv->paid)); ?></td>
                        </tr>

                    </tfoot>
                </table>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <?php
                    if ($inv->reason && $inv->reason != "") { ?>
                        <div class="well well-sm">
                            <p class="bold"><?= $inv->charge ? lang("Charge reason") : lang("Canceled reason"); ?>:</p>
                            <div><?= $this->sma->decode_html($inv->reason); ?></div>
                        </div>
                    <?php
                    }
                    if ($inv->note || $inv->note != "") { ?>
                        <div class="well well-sm">
                            <p class="bold"><?= lang("note"); ?>:</p>
                            <div><?= $this->sma->decode_html($inv->note); ?></div>
                        </div>
                    <?php
                    }
                    if ($inv->staff_note || $inv->staff_note != "") { ?>
                        <div class="well well-sm staff_note">
                            <p class="bold"><?= lang("staff_note"); ?>:</p>
                            <div><?= $this->sma->decode_html($inv->staff_note); ?></div>
                        </div>
                    <?php } ?>
                </div>

                <?php if ($customer->award_points != 0 && $Settings->each_spent > 0) { ?>
                    <div class="col-xs-5 pull-left">
                        <div class="well well-sm">
                            <?=
                                '<p>' . lang('this_sale') . ': ' . floor(($inv->grand_total / $Settings->each_spent) * $Settings->ca_point)
                                    . '<br>' .
                                    lang('total') . ' ' . lang('award_points') . ': ' . $customer->award_points . '</p>'; ?>
                        </div>
                    </div>
                <?php } ?>

                <?php if ($inv->client_id == "IMOS") { ?>
                    <div class="col-xs-5 pull-left">
                        <div class="well well-sm">
                            Ordered from Order Online (via Middleware SI)
                        </div>
                    </div>
                <?php } ?>

                <div class="col-xs-5 pull-right">
                    <div class="well well-sm">
                        <table>
                            <tr>
                                <td><?= lang("created_by"); ?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= $created_by->first_name . ' ' . $created_by->last_name; ?></td>
                            </tr>
                            <tr>
                                <td><?= lang("date"); ?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= $this->sma->hrld($inv->date); ?></td>
                            </tr>
                            <?php if ($inv->updated_by) { ?>
                                <tr>
                                    <td>&emsp;</td>
                                    <td>&emsp;</td>
                                    <td>&emsp;</td>
                                </tr>
                                <tr>
                                    <td><?= lang("updated_by"); ?></td>
                                    <td> &emsp;:&emsp; </td>
                                    <td><?= $updated_by->first_name . ' ' . $updated_by->last_name;; ?></td>
                                </tr>
                                <tr>
                                    <td><?= lang("update_at"); ?></td>
                                    <td> &emsp;:&emsp; </td>
                                    <td><?= $this->sma->hrld($inv->updated_at); ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                </div>
            </div>
            <?php if (!$Supplier || !$Customer) { ?>
                <div class="buttons">
                    <div class="btn-group btn-group-justified">
                        <div class="btn-group">
                            <a href="<?= site_url('sales/add_payment/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('view') ?>" data-toggle="modal" data-target="#myModal2" data-backdrop="static">
                                <i class="fa fa-dollar"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('add_payment') ?></span>
                            </a>
                        </div>
                        <?php if ($inv->attachment) { ?>
                            <div class="btn-group">
                                <a href="<?= site_url('welcome/download/' . $inv->attachment) ?>" class="tip btn btn-primary" title="<?= lang('attachment') ?>">
                                    <i class="fa fa-chain"></i>
                                    <span class="hidden-sm hidden-xs"><?= lang('attachment') ?></span>
                                </a>
                            </div>
                        <?php } ?>
                        <div class="btn-group">
                            <a href="<?= site_url('sales/email/' . $inv->id) ?>" data-toggle="modal" data-target="#myModal2" data-backdrop="static" class="tip btn btn-primary" title="<?= lang('email') ?>">
                                <i class="fa fa-envelope-o"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('email') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="<?= site_url('sales/pdf/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('download_pdf') ?>">
                                <i class="fa fa-download"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('pdf') ?></span>
                            </a>
                        </div>
                        <?php if (!$inv->sale_id) { ?>
                            <div class="btn-group">
                                <?php if ($inv->sale_type == 'booking') { ?>
                                    <a href="<?= site_url('sales_booking/edit_booking_sale/' . $inv->id) ?>" class="tip btn btn-warning sledit" title="<?= lang('edit') ?>">
                                        <i class="fa fa-edit"></i>
                                        <span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
                                    </a>
                                <?php } else { ?>
                                    <a href="<?= site_url('sales/edit/' . $inv->id) ?>" class="tip btn btn-warning sledit" title="<?= lang('edit') ?>">
                                        <i class="fa fa-edit"></i>
                                        <span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
                                    </a>
                                <?php } ?>
                            </div>
                            <!-- <div class="btn-group">
                                <a href="#" class="tip btn btn-danger bpo" title="<b><?= $this->lang->line("delete_sale") ?></b>"
                                    data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= site_url('sales/delete/' . $inv->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
                                    data-html="true" data-placement="top">
                                    <i class="fa fa-trash-o"></i>
                                    <span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
                                </a>
                            </div> -->
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('.tip').tooltip();
    });
</script>