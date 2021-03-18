<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg no-modal-header">
    <div class="modal-content">
        <div class="modal-body">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <a href="<?= site_url('purchases/modal_view_print/' . $inv->id); ?>" type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" target="_blank">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </a>
            <?php if ($logo) { ?>
                <div class="text-center" style="margin-bottom:20px;">
                    <img src="<?= avatar_image_logo($this->session->userdata('avatar'), $Settings->logo) ?>" height="150px"
                         alt="<?= $Settings->site_name; ?>">
                </div>
            <?php } ?>
            <div class="well well-sm">
                <div class="row bold">
                    <div class="col-xs-5">
                        <table class="bold">
                            <tr>
                                <td><?= lang("date"); ?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= $this->sma->hrld($inv->date); ?></td>
                            </tr>
                            <tr>
                                <td><?=lang("ref")?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= $inv->reference_no ?></td>
                            </tr>
                            <?php if (!empty($inv->return_purchase_ref)) { ?>
                            <tr>
                                <td><?=lang("return_ref")?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= $inv->return_purchase_ref ?></td>
                            </tr>
                            <?php if ($inv->return_id) {
                                    echo ' <a data-target="#myModal2" data-toggle="modal" href="'.site_url('purchases/modal_view/'.$inv->return_id).'"><i class="fa fa-external-link no-print"></i></a><br>';
                                } else {
                                    echo '<br>';
                                }
                            } ?>
                            <tr>
                                <td><?= lang("status") ?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= lang($inv->status); ?></td>
                            </tr>
                            <tr>
                                <td><?= lang("payment_status") ?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= lang($inv->payment_status); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-xs-7 text-right order_barcodes">
                        <?= $this->sma->save_barcode($inv->reference_no, 'code128', 66, false); ?>
                        <!--<?= $this->sma->qrcode('link', urlencode(site_url('purchases/view/' . $inv->id)), 2); ?>-->
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>

            <div class="row" style="margin-bottom:15px;">
                <div class="col-xs-6">
                    <?php echo $this->lang->line("from"); ?> :
                    <h2 style="margin-top:10px;"><?= $customer->company  ? $customer->company : $customer->name; ?></h2>
                    
                    <?php
                    echo $warehouse->name. ' - ' .$warehouse->address .'<br>';
                    ?>
                    <table>
                        <tr>
                            <td>&emsp;</td>
                            <td>&emsp;</td>
                            <td>&emsp;</td>
                        </tr>
                        <tr>
                            <td>&emsp;</td>
                            <td>&emsp;</td>
                            <td>&emsp;</td>
                        </tr>
                        <tr>
                            <td>&emsp;</td>
                            <td>&emsp;</td>
                            <td>&emsp;</td>
                        </tr>
                        <tr>
                            <td>&emsp;</td>
                            <td>&emsp;</td>
                            <td>&emsp;</td>
                        </tr>
                        <tr>
                            <td>&emsp;</td>
                            <td>&emsp;</td>
                            <td>&emsp;</td>
                        </tr>
                        <?php if($warehouse->phone) { ?>
                        <tr>
                            <td><?= lang("tel"); ?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?= $warehouse->phone ?></td>
                        </tr>
                        <?php }?>
                        <?php if($warehouse->email) { ?>
                        <tr>
                            <td><?= lang("email"); ?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?= $warehouse->email ?></td>
                        </tr>
                        <?php }?>
                    </table>
                </div>
                <div class="col-xs-6">
                    <?php echo $this->lang->line("to"); ?> : <br/>
                    <h2 style="margin-top:10px;"><?= $supplier->company ? $supplier->company : $supplier->name; ?></h2>
                    <?= $supplier->company ? "" : "Attn: " . $supplier->name ?>

                    <?php
                    echo $supplier->address . "<br />" . $supplier->city . " " . $supplier->postal_code . " " . $supplier->state . "<br />" . $supplier->country;
                    ?>
                    <table>
                        <br>
                        <?php if ($supplier->vat_no != "-" && $supplier->vat_no != "") { ?>
                        <tr>
                            <td><?= lang("vat_no"); ?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?= $supplier->vat_no ?></td>
                        </tr>
                        <?php } ?>
                        <?php if ($supplier->cf1 != "-" && $supplier->cf1 != "") { ?>
                        <tr>
                            <td><?= lang("bcf1"); ?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?= $supplier->cf1 ?></td>
                        </tr>
                        <?php } ?>
                        <?php if ($supplier->cf2 != "-" && $supplier->cf2 != "") { ?>
                        <tr>
                            <td><?= lang("bcf2"); ?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?= $supplier->cf2 ?></td>
                        </tr>
                        <?php } ?>
                        <?php if ($supplier->cf3 != "-" && $supplier->cf3 != "") { ?>
                        <tr>
                            <td><?= lang("bcf3"); ?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?= $supplier->cf3 ?></td>
                        </tr>
                        <?php } ?>
                        <?php if ($supplier->cf4 != "-" && $supplier->cf4 != "") { ?>
                        <tr>
                            <td><?= lang("bcf4"); ?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?= $supplier->cf4 ?></td>
                        </tr>
                        <?php } ?>
                        <?php if ($supplier->cf5 != "-" && $supplier->cf5 != "") { ?>
                        <tr>
                            <td><?= lang("bcf5"); ?></td>
                            <td> &emsp;:&emsp; </td>
                            <td><?= $supplier->cf5 ?></td>
                        </tr>
                        <?php } ?>
                        <?php if ($supplier->cf6 != "-" && $supplier->cf6 != "") { ?>
                        <tr>
                            <td><?= lang("bcf6"); ?></td>
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
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped print-table order-table">

                    <thead>

                    <tr>
                        <th><?= lang("no"); ?></th>
                        <th><?= lang("description"); ?></th>
                        <th><?= lang("quantity"); ?></th>
                        <?php
                            if ($inv->status == 'partial') {
                                echo '<th>'.lang("received").'</th>';
                            }
                        ?>
                        <th><?= lang("unit_cost"); ?></th>
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
                    foreach ($rows as $row):
                    ?>
                        <tr>
                            <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                            <td style="vertical-align:middle;">
                                <?= $row->product_code.' - '.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                <?= $row->supplier_part_no ? '<br>'.lang('supplier_part_no').': ' . $row->supplier_part_no : ''; ?>
                                <?= $row->details ? '<br>' . $row->details : ''; ?>
                                <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>'.lang('expiry').': ' . $this->sma->hrsd($row->expiry) : ''; ?>
                            </td>
                            <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code; ?></td>
                            <?php
                            if ($inv->status == 'partial') {
                                echo '<td style="text-align:center;vertical-align:middle;width:80px;">'.$this->sma->formatQuantity($row->quantity_received).' '.$row->product_unit_code.'</td>';
                            }
                            ?>
                            <td style="text-align:right; width:100px;"><?= $this->sma->formatMoney($row->net_unit_cost); ?></td>
                            <?php
                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 && $row->tax_code ? '<small>('.$row->tax_code.')</small>' : '') . ' ' . $this->sma->formatMoney($row->item_tax) . '</td>';
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
                        echo '<tr class="warning"><td colspan="100%" class="no-border"><strong>'.lang('returned_items').'</strong></td></tr>';
                        foreach ($return_rows as $row):
                        ?>
                            <tr class="warning">
                                <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                <td style="vertical-align:middle;">
                                    <?= $row->product_code.' - '.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                    <?= $row->supplier_part_no ? '<br>'.lang('supplier_part_no').': ' . $row->supplier_part_no : ''; ?>
                                    <?= $row->details ? '<br>' . $row->details : ''; ?>
                                    <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>'.lang('expiry').': ' . $this->sma->hrsd($row->expiry) : ''; ?>
                                </td>
                                <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code; ?></td>
                                <?php
                                if ($inv->status == 'partial') {
                                    echo '<td style="text-align:center;vertical-align:middle;width:80px;">'.$this->sma->formatQuantity($row->quantity_received).' '.$row->product_unit_code.'</td>';
                                }
                                ?>
                                <td style="text-align:right; width:100px;"><?= $this->sma->formatMoney($row->net_unit_cost); ?></td>
                                <?php
                                if ($Settings->tax1 && $inv->product_tax > 0) {
                                    echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 && $row->tax_code ? '<small>('.$row->tax_code.')</small>' : '') . ' ' . $this->sma->formatMoney($row->item_tax) . '</td>';
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
                    if ($inv->status == 'partial') {
                        $col++;
                    }
                    if ($Settings->product_discount && $inv->product_discount != 0) {
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
                <div <?php echo (($inv->shipping_order || $inv->receiver || $inv->license_plate) ? 'class="col-xs-6 pull-left"' : 'class="col-xs-12"') ?> >
                    <?php if ($inv->note || $inv->note != "") { ?>
                    <div class="well well-sm">
                        <p class="bold"><?= lang("note"); ?></p>
                        <div><?= $this->sma->decode_html($inv->note); ?></div>
                    </div>
                    <?php } ?>
                </div>
                <div class=" <?= $inv->note || $inv->note != '' ? 'col-xs-6 pull-right' : 'col-xs-12' ?>">
                    <div class="well well-sm">
                        <p class="bold"><?= lang('shipping_order'); ?></p>
                        <table>
                            <tr>
                                <td><?= lang("no_si_spj"); ?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= $inv->sino_spj; ?></td>
                            </tr>
                            <tr>
                                <td><?= lang("no_si_do"); ?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= $inv->sino_do; ?></td>
                            </tr>
                            <tr>
                                <td><?= lang("no_si_so"); ?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= $inv->sino_so; ?></td>
                            </tr>
                            <tr>
                                <td><?= lang("no_si_billing"); ?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= $inv->sino_billing; ?></td>
                            </tr>
                            <tr>
                                <td><?= lang("delivery_date"); ?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= $this->sma->hrld($inv->shipping_date); ?></td>
                            </tr>
                            <tr>
                                <td><?= lang("receiver"); ?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= $inv->receiver; ?></td>
                            </tr>
                            <tr>
                                <td><?= lang("license_plate"); ?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= $inv->license_plate; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row">
                <?php if(!empty($Official)){?>
                <div class="col-xs-5 pull-left">
                    <div class="well well-sm">
                        <p><b>Official Partner Reference</b></p>
                        <p>
                            <div class="col-xs-5"><?= lang("Order Request"); ?></div>: <?= $Official->pos_order_request_id ?> <br>
                            <div class="col-xs-5"><?= lang("Sales Order"); ?></div>: <?= $Official->order_reference ?><br>

                          <div class="col-xs-5"><?= lang("Shipment"); ?></div>: <?= lang($Official->shipment_reference); ?><br>
                          <div class="col-xs-5"><?= lang("Invoice"); ?></div>: <?= lang($Official->invoice_reference); ?><br>
                    </div>
                </div>
                <?php } ?>
                <?php if(!empty($inv->cf1)){?>
                <div class="pull-left" style="margin-left: 15px">
                    <div class="well well-sm">
                        <p><b><?=$inv->cf2?> Reference</b></p>
                        Reference Number : <?= $inv->cf1 ?> <br>
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
                                <td><?= lang("created_at"); ?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= $this->sma->hrld($inv->created_at); ?></td>
                            </tr>
                            <?php if ($inv->updated_by) { ?>
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
                    <?php if ($inv->attachment) { ?>
                        <div class="btn-group">
                            <a href="<?= site_url('welcome/download/' . $inv->attachment) ?>" class="tip btn btn-primary" title="<?= lang('attachment') ?>">
                                <i class="fa fa-chain"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('attachment') ?></span>
                            </a>
                        </div>
                    <?php } ?>
                    <div class="btn-group btn-group-justified">
                        <div class="btn-group"> 
                            <a href="<?= site_url('purchases/add_payment/' . $inv->id) ?>" data-toggle="modal" data-target="#myModal2" class="tip btn btn-primary" title="<?= lang('add_payment') ?>">
                                <i class="fa fa-dollar"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('add_payment') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="<?= site_url('purchases/email/' . $inv->id) ?>" data-toggle="modal" data-target="#myModal2" class="tip btn btn-primary" title="<?= lang('email') ?>">
                                <i class="fa fa-envelope-o"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('email') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="<?= site_url('purchases/pdf/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('download_pdf') ?>">
                                <i class="fa fa-download"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('pdf') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="<?= site_url('purchases/edit/' . $inv->id) ?>" class="tip btn btn-warning sledit" title="<?= lang('edit') ?>">
                                <i class="fa fa-edit"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
                            </a>
                        </div>
                        <!-- <div class="btn-group">
                            <a href="#" class="tip btn btn-danger bpo" title="<b><?= $this->lang->line("delete") ?></b>"
                                data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= site_url('purchases/delete/' . $inv->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
                                data-html="true" data-placement="top">
                                <i class="fa fa-trash-o"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
                            </a>
                        </div> -->
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready( function() {
        $('.tip').tooltip();
    });
</script>
