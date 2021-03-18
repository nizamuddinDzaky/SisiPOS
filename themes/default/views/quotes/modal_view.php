<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg no-modal-header">
    <div class="modal-content">
        <div class="modal-body">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <?php if ($logo) { ?>
                <div class="text-center" style="margin-bottom:20px;">
                    <img src="<?= base_url() . 'assets/uploads/logos/' . $biller->logo; ?>"
                         alt="<?= $biller->company != '-' ? $biller->company : $biller->name; ?>">
                </div>
            <?php } ?>
            <div class="well well-sm">
                <div class="row bold">
                    <div class="col-xs-5">
                        <table class="bold">
                            <tr>
                                <td><?=lang("reference_no")?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= $inv->reference_no ?></td>
                            </tr>
                            <tr>
                                <td><?= lang("date"); ?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= $this->sma->hrld($inv->date); ?></td>
                            </tr>
                            <tr>
                                <td><?=lang("status")?></td>
                                <td> &emsp;:&emsp; </td>
                                <td><?= $inv->status ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-xs-7 text-right order_barcodes">
                        <?= $this->sma->save_barcode($inv->reference_no, 'code128', 66, false); ?>
                        <!-- <?= $this->sma->qrcode('link', urlencode(site_url('quotes/view/' . $inv->id)), 2); ?> -->
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>

            <div class="row" style="margin-bottom:15px;">
                <div class="col-xs-6">
                    <?php echo $this->lang->line("from"); ?> :
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
                    <?php echo $this->lang->line("to"); ?> :<br/>
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
                        <th><?= lang("quantity"); ?></th>
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
                    foreach ($rows as $row):
                    ?>
                        <tr>
                            <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                            <td style="vertical-align:middle;">
                                <?= $row->product_code.' - '.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                <?= $row->details ? '<br>' . $row->details : ''; ?>
                            </td>
                            <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code; ?></td>
                            <td style="text-align:right; width:100px;"><?= $this->sma->formatMoney($row->unit_price); ?></td>
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
                            <td colspan="<?= $tcol; ?>"
                                style="text-align:right; padding-right:10px;"><?= lang("total"); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <?php
                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                echo '<td style="text-align:right;">' . $this->sma->formatMoney($inv->product_tax) . '</td>';
                            }
                            if ($Settings->product_discount && $inv->product_discount != 0) {
                                echo '<td style="text-align:right;">' . $this->sma->formatMoney($inv->product_discount) . '</td>';
                            }
                            ?>
                            <td style="text-align:right; padding-right:10px;"><?= $this->sma->formatMoney($inv->total + $inv->product_tax); ?></td>
                        </tr>
                    <?php } ?>

                    <?php if ($inv->order_discount != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("order_discount") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">'.($inv->order_discount_id ? '<small>('.$inv->order_discount_id.')</small> ' : '') . $this->sma->formatMoney($inv->order_discount) . '</td></tr>';
                    }
                    ?>
                    <?php if ($Settings->tax2 && $inv->order_tax != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;">' . lang("order_tax") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($inv->order_tax) . '</td></tr>';
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
                        <td style="text-align:right; padding-right:10px; font-weight:bold;"><?= $this->sma->formatMoney($inv->grand_total); ?></td>
                    </tr>
                    </tfoot>
                </table>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <?php
                        if ($inv->note || $inv->note != "") { ?>
                            <div class="well well-sm">
                                <p class="bold"><?= lang("note"); ?>:</p>
                                <div><?= $this->sma->decode_html($inv->note); ?></div>
                            </div>
                        <?php } ?>
                </div>

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
                            <a href="<?= site_url('sales/add/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('create_sale') ?>">
                                <i class="fa fa-heart"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('create_sale') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="<?= site_url('purchases/add/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('create_purchase') ?>">
                                <i class="fa fa-star"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('create_purchase') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="<?= site_url('quotes/email/' . $inv->id) ?>" data-toggle="modal" data-target="#myModal2" class="tip btn btn-primary" title="<?= lang('email') ?>">
                                <i class="fa fa-envelope-o"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('email') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="<?= site_url('quotes/pdf/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('download_pdf') ?>">
                                <i class="fa fa-download"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('pdf') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="<?= site_url('quotes/edit/' . $inv->id) ?>" class="tip btn btn-warning sledit" title="<?= lang('edit') ?>">
                                <i class="fa fa-edit"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
                            </a>
                        </div>
                        <!-- <div class="btn-group">
                            <a href="#" class="tip btn btn-danger bpo" title="<b><?= $this->lang->line("delete") ?></b>"
                                data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= site_url('quotes/delete/' . $inv->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
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
