<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-file"></i><?= lang("purchase_no") . '. ' . $inv->id; ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <?php if ($inv->attachment) { ?>
                            <li>
                                <a href="<?= site_url('welcome/download/' . $inv->attachment) ?>">
                                    <i class="fa fa-chain"></i> <?= lang('attachment') ?>
                                </a>
                            </li>
                        <?php } ?>
                        <li>
                            <a href="<?= site_url('purchases/payments/' . $inv->id) ?>" data-target="#myModal"  data-backdrop="static" data-toggle="modal">
                                <i class="fa fa-money"></i> <?= lang('view_payments') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= site_url('purchases/add_payment/' . $inv->id) ?>" data-target="#myModal"  data-backdrop="static" data-toggle="modal">
                                <i class="fa fa-money"></i> <?= lang('add_payment') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= site_url('purchases/edit/' . $inv->id) ?>">
                                <i class="fa fa-edit"></i> <?= lang('edit_purchase') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= site_url('purchases/email/' . $inv->id) ?>">
                                <i class="fa fa-envelope-o"></i> <?= lang('send_email') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= site_url('purchases/pdf/' . $inv->id) ?>">
                                <i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <?php if (!empty($inv->return_purchase_ref) && $inv->return_id) {
                    echo '<div class="alert alert-info no-print"><p>'.lang("purchase_is_returned").': '.$inv->return_purchase_ref;
                    echo ' <a data-target="#myModal2" data-toggle="modal" href="'.site_url('purchases/modal_view/'.$inv->return_id).'"><i class="fa fa-external-link no-print"></i></a><br>';
                    echo '</p></div>';
                } ?>
                <div class="clearfix"></div>
                <div class="print-only col-xs-12">
                    <img src="<?= base_url() . 'assets/uploads/logos/' . $Settings->logo; ?>"
                         alt="<?= $Settings->site_name; ?>">
                </div>
                <div class="well well-sm">
                    <div class="col-xs-6 border-right">

                        <div class="col-xs-2"><i class="fa fa-3x fa-building padding010 text-muted"></i></div>
                        <div class="col-xs-10">
                            <h2 class=""><?= $supplier->company ? $supplier->company : $supplier->name; ?></h2>
                            <?= $supplier->company ? "" : "Attn: " . $supplier->name ?>

                            <?php
                            echo $supplier->address . "<br />" . $supplier->city . " " . $supplier->postal_code . " " . $supplier->state . "<br />" . $supplier->country;

                            echo "<p>";

                            if ($supplier->vat_no != "-" && $supplier->vat_no != "") {
                                echo "<br>" . lang("vat_no") . " : " . $supplier->vat_no;
                            }
                            if ($supplier->cf1 != "-" && $supplier->cf1 != "") {
                                echo "<br>" . lang("scf1") . " : " . $supplier->cf1;
                            }
                            if ($supplier->cf2 != "-" && $supplier->cf2 != "") {
                                echo "<br>" . lang("scf2") . " : " . $supplier->cf2;
                            }
                            if ($supplier->cf3 != "-" && $supplier->cf3 != "") {
                                echo "<br>" . lang("scf3") . " : " . $supplier->cf3;
                            }
                            if ($supplier->cf4 != "-" && $supplier->cf4 != "") {
                                echo "<br>" . lang("scf4") . " : " . $supplier->cf4;
                            }
                            if ($supplier->cf5 != "-" && $supplier->cf5 != "") {
                                echo "<br>" . lang("scf5") . " : " . $supplier->cf5;
                            }
                            if ($supplier->cf6 != "-" && $supplier->cf6 != "") {
                                echo "<br>" . lang("scf6") . " : " . $supplier->cf6;
                            }

                            echo "</p>";
                            echo lang("tel") . "&emsp;&nbsp;&emsp;: " . $supplier->phone . "<br>" . lang("email") . "&emsp;: " . $supplier->email;
                            ?>
                        </div>
                        <div class="clearfix"></div>

                    </div>
                    <div class="col-xs-6 border-left">

                        <div class="col-xs-2"><i class="fa fa-3x fa-truck padding010 text-muted"></i></div>
                        <div class="col-xs-10">
                            <h2 class=""><?= $customer->company; ?></h2>
                            <?php
                            echo $warehouse->name .' - '. $warehouse->address . "<br><br>";
                            echo ($warehouse->phone ? lang("tel") . "&emsp;&nbsp;&emsp;: " . $warehouse->phone . "<br>" : '') . ($warehouse->email ? lang("email") . "&emsp;: " . $warehouse->email : '');
                            ?>
                        </div>
                        <div class="clearfix"></div>


                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="col-xs-7 pull-right">
                    <div class="col-xs-12 text-right order_barcodes">
                        <?= $this->sma->save_barcode($inv->reference_no, 'code128', 66, false); ?>
                        <!-- <?= $this->sma->qrcode('link', urlencode(site_url('purchases/view/' . $inv->id)), 2); ?> -->
                    </div>
                    <div class="clearfix"></div>
                </div>

                <div class="col-xs-5">
                    <div class="col-xs-2"><i class="fa fa-3x fa-file-text-o padding010 text-muted"></i></div>
                    <div class="col-xs-10">
                        <table class="bold">
                            <tr>
                                <td><h2><?= lang("ref"); ?></h2></td>
                                <td><h2> &emsp;:&emsp; </h2></td>
                                <td><h2><?= lang($inv->reference_no); ?></h2></td>
                            </tr>
                            <?php if (!empty($inv->return_purchase_ref)) { ?>
                            <tr>
                                <td><?= lang("return_ref"); ?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= lang($inv->return_purchase_ref); ?>
                                    <?php if ($inv->return_id) { ?>
                                        <a data-target="#myModal2" data-toggle="modal" href="'.site_url('purchases/modal_view/'.$inv->return_id).'"><i class="fa fa-external-link no-print"></i></a><br>
                                    <?php } ?>
                                </td>
                            </tr>
                            <?php } ?>
                            <tr>
                                <td><?= lang("date"); ?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= $this->sma->hrld($inv->date); ?></td>
                            </tr>
                            <tr>
                                <td><?= lang("status"); ?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= lang($inv->status); ?></td>
                            </tr>
                            <tr>
                                <td><?= lang("payment_status"); ?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= lang($inv->payment_status); ?></td>
                            </tr>
                        </table>

                        <p>&nbsp;</p>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped print-table order-table">
                        <thead>
                        <tr>
                            <th><?= lang("no"); ?></th>
                            <th><?= lang("description"); ?> (<?= lang("code"); ?>)</th>
                            <th><?= lang("quantity"); ?></th>
                            <?php
                                if ($inv->status == 'partial') {
                                    echo '<th>'.lang("received").'</th>';
                                }
                            ?>
                            <th style="padding-right:20px;"><?= lang("unit_cost"); ?></th>
                            <?php
                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                echo '<th style="padding-right:20px; text-align:center; vertical-align:middle;">' . lang("tax") . '</th>';
                            }
                            if ($Settings->product_discount != 0 && $inv->product_discount != 0) {
                                echo '<th style="padding-right:20px; text-align:center; vertical-align:middle;">' . lang("discount") . '</th>';
                            }
                            ?>
                            <th style="padding-right:20px;"><?= lang("subtotal"); ?></th>
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
                                    <?= $row->supplier_part_no ? '<br>'.lang('supplier_part_no').': ' . $row->supplier_part_no : ''; ?>
                                    <?= $row->details ? '<br>' . $row->details : ''; ?>
                                    <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>' .lang('expiry').': ' . $this->sma->hrsd($row->expiry) : ''; ?></td>
                                <td style="width: 120px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code; ?></td>
                                <?php
                                if ($inv->status == 'partial') {
                                    echo '<td style="text-align:center;vertical-align:middle;width:120px;">'.$this->sma->formatQuantity($row->unit_quantity_received).' '.$row->product_unit_code.'</td>';
                                }
                                ?>
                                <td style="text-align:right; width:120px; padding-right:10px;"><?= $this->sma->formatMoney($row->net_unit_cost); ?></td>
                                <?php
                                if ($Settings->tax1 && $inv->product_tax > 0) {
                                    echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 && $row->tax_code? '<small>(' . $row->tax_code . ')</small> ' : '') . $this->sma->formatMoney($row->item_tax) . '</td>';
                                }
                                if ($Settings->product_discount != 0 && $inv->product_discount != 0) {
                                    echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>('.$row->discount.')</small>' : '') . ' ' . $this->sma->formatMoney($row->item_discount) . '</td>';
                                }
                                ?>
                                <td style="text-align:right; width:120px; padding-right:10px;"><?= $this->sma->formatMoney($row->subtotal); ?></td>
                            </tr>
                            <?php
                            $r++;
                        endforeach;
                        if ($return_rows) {
                            echo '<tr class="warning"><td colspan="100%" class="no-border"><strong>'.lang('returned_items').'</strong></td></tr>';
                            foreach ($return_rows as $row):
                            ?>
                                <tr class="warning">
                                    <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                    <td style="vertical-align:middle;">
                                        <?= $row->product_code.' - '.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                        <?= $row->supplier_part_no ? '<br>'.lang('supplier_part_no').': ' . $row->supplier_part_no : ''; ?>
                                        <?= $row->details ? '<br>' . $row->details : ''; ?>
                                        <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>' .lang('expiry').': ' . $this->sma->hrsd($row->expiry) : ''; ?></td>
                                    <td style="width: 120px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code; ?></td>
                                    <?php
                                    if ($inv->status == 'partial') {
                                        echo '<td style="text-align:center;vertical-align:middle;width:120px;">'.$this->sma->formatQuantity($row->quantity_received).' '.$row->product_unit_code.'</td>';
                                    }
                                    ?>
                                    <td style="text-align:right; width:120px; padding-right:10px;"><?= $this->sma->formatMoney($row->net_unit_cost); ?></td>
                                    <?php
                                    if ($Settings->tax1 && $inv->product_tax > 0) {
                                        echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 && $row->tax_code? '<small>(' . $row->tax_code . ')</small> ' : '') . $this->sma->formatMoney($row->item_tax) . '</td>';
                                    }
                                    if ($Settings->product_discount != 0 && $inv->product_discount != 0) {
                                        echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>('.$row->discount.')</small>' : '') . ' ' . $this->sma->formatMoney($row->item_discount) . '</td>';
                                    }
                                    ?>
                                    <td style="text-align:right; width:120px; padding-right:10px;"><?= $this->sma->formatMoney($row->subtotal); ?></td>
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
                        if ($inv->status == 'partial') {
                            $col++;
                        }
                        if ($Settings->product_discount && $inv->product_discount != 0) {
                            $col++;
                        }
                        if ($Settings->tax1 && $inv->product_tax > 0) {
                            $col++;
                        }
                        if (($Settings->product_discount  && $inv->product_discount != 0) && ($Settings->tax1 && $inv->product_tax > 0)) {
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
                                <td colspan="<?= $tcol; ?>"
                                    style="text-align:right; padding-right:10px;"><?= lang("total"); ?>
                                    (<?= $default_currency->code; ?>)
                                </td>
                                <?php
                                if ($Settings->tax1 && $inv->product_tax > 0) {
                                    echo '<td style="text-align:right;">' . $this->sma->formatMoney($return_purchase ? ($inv->product_tax+$return_purchase->product_tax) : $inv->product_tax) . '</td>';
                                }
                                if ($Settings->product_discount && $inv->product_discount != 0) {
                                    echo '<td style="text-align:right;">' . $this->sma->formatMoney($return_purchase ? ($inv->product_discount+$return_purchase->product_discount) : $inv->product_discount) . '</td>';
                                }
                                ?>
                                <td style="text-align:right; padding-right:10px;"><?= $this->sma->formatMoney($return_purchase ? (($inv->total + $inv->product_tax)+($return_purchase->total + $return_purchase->product_tax)) : ($inv->total + $inv->product_tax)); ?></td>
                            </tr>
                        <?php } ?>
                        <?php
                        if ($return_purchase) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("return_total") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($return_purchase->grand_total) . '</td></tr>';
                        }
                        if ($inv->surcharge != 0) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("return_surcharge") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($inv->surcharge) . '</td></tr>';
                        }
                        ?>
                        <?php if ($inv->order_discount != 0) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("order_discount") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">'.($inv->order_discount_id ? '<small>('.$inv->order_discount_id.')</small> ' : '') . $this->sma->formatMoney($return_purchase ? ($inv->order_discount+$return_purchase->order_discount) : $inv->order_discount) . '</td></tr>';
                        }
                        ?>
                        <?php if ($Settings->tax2 && $inv->order_tax != 0) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;">' . lang("order_tax") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($return_purchase ? ($inv->order_tax+$return_purchase->order_tax) : $inv->order_tax) . '</td></tr>';
                        }
                        ?>
                        <?php if ($inv->shipping != 0) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("shipping") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($inv->shipping) . '</td></tr>';
                        }
                        ?>
                        <tr>
                            <td colspan="<?= $col; ?>"
                                style="text-align:right; font-weight:bold;"><?= lang("total_amount"); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td style="text-align:right; padding-right:10px; font-weight:bold;"><?= $this->sma->formatMoney($return_purchase ? ($inv->grand_total+$return_purchase->grand_total) : $inv->grand_total); ?></td>
                        </tr>
                        <tr>
                            <td colspan="<?= $col; ?>"
                                style="text-align:right; font-weight:bold;"><?= lang("paid"); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney($return_purchase ? ($inv->paid+$return_purchase->paid) : $inv->paid); ?></td>
                        </tr>
                        <tr>
                            <td colspan="<?= $col; ?>"
                                style="text-align:right; font-weight:bold;"><?= lang("balance"); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney(($return_purchase ? ($inv->grand_total+$return_purchase->grand_total) : $inv->grand_total) - ($return_purchase ? ($inv->paid+$return_purchase->paid) : $inv->paid)); ?></td>
                        </tr>

                        </tfoot>
                    </table>

                </div>

                <div class="row">
                    <div class="col-xs-7">
                        <?php if($inv->shipping_date){?>
                        <div class="well well-sm">
                            <p class="bold"><?= lang('shipping_order'); ?></p>
                            <p><?= lang("delivery_date"); ?>: <?= $this->sma->hrld($inv->shipping_date); ?></p>
                            <?php if ($inv->receiver != "-" && $inv->receiver != "") { ?>
                                <p><?= lang("receiver"); ?>: <?= $inv->receiver; ?></p>
                            <?php } ?>
                        </div>
                        <?php }?>
                        <?php // if ($inv->note || $inv->note != "") { ?>
<!--                            <div class="well well-sm">
                                <p class="bold"><?= lang("note"); ?>:</p>

                                <div><?= $this->sma->decode_html($inv->note); ?></div>
                            </div>-->
                        <?php // } ?>
                    </div>

                    <div class="col-xs-4 col-xs-offset-1">
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
                                <?php if ($inv->sino_spj != "-" && $inv->sino_spj != "") { ?>
                                <tr>
                                    <td><?= lang("no_si_spj"); ?></td>
                                    <td> &emsp;:&emsp; </td>
                                    <td><?= $inv->sino_spj; ?></td>
                                </tr>
                                <?php } ?>
                                <?php if ($inv->sino_do != "-" && $inv->sino_do != "") { ?>
                                <tr>
                                    <td><?= lang("no_si_do"); ?></td>
                                    <td> &emsp;:&emsp; </td>
                                    <td><?= $inv->sino_do; ?></td>
                                </tr>
                                <?php } ?>
                                <?php if ($inv->sino_so != "-" && $inv->sino_so != "") { ?>
                                <tr>
                                    <td><?= lang("no_si_so"); ?></td>
                                    <td> &emsp;:&emsp; </td>
                                    <td><?= $inv->sino_so; ?></td>
                                </tr>
                                <?php } ?>
                                <?php if ($inv->sino_billing != "-" && $inv->sino_billing != "") { ?>
                                <tr>
                                    <td><?= lang("no_si_billing"); ?></td>
                                    <td> &emsp;:&emsp; </td>
                                    <td><?= $inv->no_si_billing; ?></td>
                                </tr>
                                <?php } ?>
                            </table>
                        </div>

                    </div>
                </div>
                <?php if ($inv->note || $inv->note != "") { ?>
                    <div class="well well-sm">
                        <p class="bold"><?= lang("note"); ?>:</p>

                        <div><?= $this->sma->decode_html($inv->note); ?></div>
                    </div>
                <?php } ?>
            </div>
        </div>

        <?php if (!empty($payments)) { ?>
            <div class="row">
                <div class="col-xs-12">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-condensed">
                            <thead>
                            <tr>
                                <th><?= lang('date') ?></th>
                                <th><?= lang('payment_reference') ?></th>
                                <?php if($Official){ ?>
                                <th><?= lang('date_partner') ?></th>
                                <th><?= lang('reference_partner') ?></th>
                                <?php } ?>
                                <th><?= lang('paid_by') ?></th>
                                <th><?= lang('amount') ?></th>
                                <th><?= lang('created_by') ?></th>
                                <th><?= lang('type') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($payments as $payment) { ?>
                                <tr>
                                    <td><?= $this->sma->hrld($payment->date) ?></td>
                                    <td><?= $payment->reference_no; ?></td>
                                    <?php if($Official){ ?>
                                    <td><?= $this->sma->hrsd($payment->date_dist); ?></td>
                                    <td><?= $payment->reference_dist; ?></td>
                                    <?php } ?>
                                    <td><?= $payment->paid_by; ?></td>
                                    <td><?= $this->sma->formatMoney($payment->amount); ?></td>
                                    <td><?= $payment->first_name . ' ' . $payment->last_name; ?></td>
                                    <td><?= $payment->type; ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php } ?>

        <?php if (!$Supplier || !$Customer) { ?>
            <div class="buttons">
                <?php if ($inv->attachment) { ?>
                    <div class="btn-group">
                        <a href="<?= site_url('welcome/download/' . $inv->attachment) ?>" class="tip btn btn-primary" title="<?= lang('attachment') ?>">
                            <i class="fa fa-chain"></i> <span class="hidden-sm hidden-xs"><?= lang('attachment') ?></span>
                        </a>
                    </div>
                <?php } ?>
                <div class="btn-group btn-group-justified">
                    <div class="btn-group">
                        <a href="<?= site_url('purchases/payments/' . $inv->id) ?>" data-toggle="modal" data-target="#myModal"  data-backdrop="static" class="tip btn btn-primary tip" title="<?= lang('view_payments') ?>">
                            <i class="fa fa-money"></i> <span class="hidden-sm hidden-xs"><?= lang('view_payments') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="<?= site_url('purchases/add_payment/' . $inv->id) ?>" class="tip btn btn-primary tip" title="<?= lang('add_payment') ?>" data-target="#myModal"  data-backdrop="static" data-toggle="modal">
                            <i class="fa fa-money"></i> <span class="hidden-sm hidden-xs"><?= lang('add_payment') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="<?= site_url('purchases/email/' . $inv->id) ?>" data-toggle="modal" data-target="#myModal"  data-backdrop="static" class="tip btn btn-primary tip" title="<?= lang('email') ?>">
                            <i class="fa fa-envelope-o"></i> <span class="hidden-sm hidden-xs"><?= lang('email') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="<?= site_url('purchases/pdf/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('download_pdf') ?>">
                            <i class="fa fa-download"></i> <span class="hidden-sm hidden-xs"><?= lang('pdf') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="<?= site_url('purchases/edit/' . $inv->id) ?>" class="tip btn btn-warning tip" title="<?= lang('edit') ?>">
                            <i class="fa fa-edit"></i> <span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
                        </a>
                    </div>
                    <!-- <div class="btn-group">
                        <a href="#" class="tip btn btn-danger bpo" title="<b><?= $this->lang->line("delete_purchase") ?></b>"
                           data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= site_url('purchases/delete/' . $inv->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
                           data-html="true" data-placement="top">
                            <i class="fa fa-trash-o"></i> <span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
                        </a>
                    </div> -->
                </div>
            </div>
        <?php } ?>
    </div>
</div>
