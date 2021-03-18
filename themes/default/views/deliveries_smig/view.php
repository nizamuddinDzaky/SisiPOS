<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-file"></i><?= lang("Confirmation_Delivery_no") . '. ' . $inv->no_do; ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i></a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <!-- <?php if ($inv->attachment) { ?>
                            <li>
                                <a href="<?= site_url('welcome/download/' . $inv->attachment) ?>">
                                    <i class="fa fa-chain"></i> <?= lang('attachment') ?>
                                </a>
                            </li>
                        <?php } ?>
                        <li>
                            <a href="<?= site_url('quotes/edit/' . $inv->id) ?>">
                                <i class="fa fa-edit"></i> <?= lang('edit_quote') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= site_url('deliveries_smig/email/' . $inv->id) ?>" data-target="#myModal"  data-backdrop="static" data-toggle="modal">
                                <i class="fa fa-envelope-o"></i> <?= lang('send_email') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= site_url('deliveries_smig/excel/' . $inv->id) ?>"><i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>-->
                        <li>
                            <a href="<?= site_url('purchases/add/' . $inv->id) ?>">
                                <i class="fa fa-plus-circle"></i> <?= lang('create_purchase') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= site_url('deliveries_smig/pdf/' . $inv->id) ?>">
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

                <div class="print-only col-xs-12">
                    <img src="<?= base_url() . 'assets/uploads/logos/' . $biller->logo; ?>" alt="<?= $biller->company != '-' ? $biller->company : $biller->name; ?>">
                </div>
                <div class="well well-sm">
                    <div class="col-xs-4 border-right">

                        <div class="col-xs-2"><i class="fa fa-3x fa-building padding010 text-muted"></i></div>
                        <div class="col-xs-10">
                            <h2 class=""><?= $supplier->company ? $supplier->company : $supplier->name; ?></h2>
                            <?= $supplier->company ? "" : "Attn: " . $supplier->name ?>
                            <?php
                            echo $supplier->address . "<br>" . $supplier->city . " " . $supplier->postal_code . " " . $supplier->state . "<br>" . $supplier->country;
                            echo "<p>";
                            if ($supplier->vat_no != "-" && $supplier->vat_no != "") {
                                echo "<br>" . lang("vat_no") . ": " . $supplier->vat_no;
                            }
                            if ($supplier->cf1 != "-" && $supplier->cf1 != "") {
                                echo "<br>" . lang("scf1") . ": " . $supplier->cf1;
                            }
                            if ($supplier->cf2 != "-" && $supplier->cf2 != "") {
                                echo "<br>" . lang("scf2") . ": " . $supplier->cf2;
                            }
                            if ($supplier->cf3 != "-" && $supplier->cf3 != "") {
                                echo "<br>" . lang("scf3") . ": " . $supplier->cf3;
                            }
                            if ($supplier->cf4 != "-" && $supplier->cf4 != "") {
                                echo "<br>" . lang("scf4") . ": " . $supplier->cf4;
                            }
                            if ($supplier->cf5 != "-" && $supplier->cf5 != "") {
                                echo "<br>" . lang("scf5") . ": " . $supplier->cf5;
                            }
                            if ($supplier->cf6 != "-" && $supplier->cf6 != "") {
                                echo "<br>" . lang("scf6") . ": " . $supplier->cf6;
                            }
                            echo "</p>";
                            echo lang("tel") . ": " . $supplier->phone . "<br>" . lang("email") . ": " . $supplier->email;
                            ?>
                        </div>
                        <div class="clearfix"></div>

                    </div>
                    <div class="col-xs-4 border-right">

                        <div class="col-xs-2"><i class="fa fa-3x fa-user padding010 text-muted"></i></div>
                        <div class="col-xs-10">
                            <h2 class=""><?= $biller->company != '-' ? $biller->company : $biller->name; ?></h2>
                            <?= $biller->company ? "" : "Attn: " . $biller->name ?>
                            <?php
                            echo $biller->address . "<br>" . $biller->city . " " . $biller->postal_code . " " . $biller->state . "<br>" . $biller->country;
                            echo "<p>";
                            if ($biller->vat_no != "-" && $biller->vat_no != "") {
                                echo "<br>" . lang("vat_no") . ": " . $biller->vat_no;
                            }
                            if ($biller->cf1 != "-" && $biller->cf1 != "") {
                                echo "<br>" . lang("bcf1") . ": " . $biller->cf1;
                            }
                            if ($biller->cf2 != "-" && $biller->cf2 != "") {
                                echo "<br>" . lang("bcf2") . ": " . $biller->cf2;
                            }
                            if ($biller->cf3 != "-" && $biller->cf3 != "") {
                                echo "<br>" . lang("bcf3") . ": " . $biller->cf3;
                            }
                            if ($biller->cf4 != "-" && $biller->cf4 != "") {
                                echo "<br>" . lang("bcf4") . ": " . $biller->cf4;
                            }
                            if ($biller->cf5 != "-" && $biller->cf5 != "") {
                                echo "<br>" . lang("bcf5") . ": " . $biller->cf5;
                            }
                            if ($biller->cf6 != "-" && $biller->cf6 != "") {
                                echo "<br>" . lang("bcf6") . ": " . $biller->cf6;
                            }
                            echo "</p>";
                            echo lang("tel") . ": " . $biller->phone . "<br>" . lang("email") . ": " . $biller->email;
                            ?>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="col-xs-4">
                        <div class="col-xs-2"><i class="fa fa-3x fa-building-o padding010 text-muted"></i></div>
                        <div class="col-xs-10">
                            <h2 class=""><?= $warehouse->name ?></h2>
                            <?php
                            echo $warehouse->address . "<br>";
                            echo ($warehouse->phone ? lang("tel") . ": " . $warehouse->phone . "<br>" : '') . ($warehouse->email ? lang("email") . ": " . $warehouse->email : '');
                            ?>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="clearfix"></div>
                </div>

                <div class="well well-sm">
                    <div class="col-xs-12">
                        <h2 style="margin-top:10px;"><?php echo $this->lang->line("Detail"); ?> :<br/></h2>
                    </div>
                    <div class="col-xs-6">  
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
                    <div class="col-xs-6">
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
                <div class="col-xs-6 pull-right">
                    <div class="col-xs-12 text-right order_barcodes">
                        <?= $this->sma->save_barcode($inv->no_do, 'code128', 66, false); ?>&emsp;&emsp;
                        <!-- <?= $this->sma->qrcode('link', urlencode(site_url('deliveries_smig/view/' . $inv->id)), 2); ?> -->
                    </div>
                    <div class="clearfix"></div>
                </div>

                <div class="col-xs-6">
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
                <div class="clearfix"></div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped print-table order-table">
                        <thead>
                        <tr>
                            <th><?= lang("no"); ?></th>
                            <th><?= lang("description"); ?> (<?= lang("code"); ?>)</th>
                            <th><?= lang("quantity"); ?></th>
                            <th style="padding-right:20px;"><?= lang("unit_price"); ?></th>
                            <?php
                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                echo '<th style="padding-right:20px; text-align:center; vertical-align:middle;">' . lang("tax") . '</th>';
                            }
                            if ($Settings->product_discount && $inv->product_discount != 0) {
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
                                    <?= $row->product_code.' - '.$row->product_name; ?>
                                    <?= $row->details ? '<br>' . $row->details : ''; ?></td>
                                <td style="width: 120px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code; ?></td>
                                <td style="text-align:right; width:100px; padding-right:10px;"><?= $this->sma->formatMoney($row->unit_price); ?></td>
                                <?php
                                if ($Settings->tax1 && $inv->product_tax > 0) {
                                    echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 && $row->tax_code ? '<small>(' . $row->tax_code . ')</small> ' : '') . $this->sma->formatMoney($row->item_tax) . '</td>';
                                }
                                if ($Settings->product_discount && $inv->product_discount != 0) {
                                    echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->sma->formatMoney($row->item_discount) . '</td>';
                                }
                                ?>
                                <td style="text-align:right; width:120px; padding-right:10px;"><?= $this->sma->formatMoney($row->subtotal); ?></td>
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
                        <?php
                        if ($inv->order_discount != 0) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("order_discount") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">'.($inv->order_discount_id ? '<small>('.$inv->order_discount_id.')</small> ' : '') . $this->sma->formatMoney($inv->order_discount) . '</td></tr>';
                        }
                        if ($Settings->tax2 && $inv->order_tax != 0) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("order_tax") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($inv->order_tax) . '</td></tr>';
                        }
                        if ($inv->shipping != 0) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("shipping") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($inv->shipping) . '</td></tr>';
                        }
                        ?>
                        <tr>
                            <td colspan="<?= $col; ?>"
                                style="text-align:right; padding-right:10px; font-weight:bold;"><?= lang("total_amount"); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td style="text-align:right; padding-right:10px; font-weight:bold;"><?= $this->sma->formatMoney($inv->grand_total); ?></td>
                        </tr>

                        </tfoot>
                    </table>
                </div>
                <div class="row" style="margin-bottom:15px;">
                    <div class="col-xs-12">
                        <?php echo lang("Note_2") ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-7">
                        <?php if ($inv->note || $inv->note != "") { ?>
                            <div class="well well-sm">
                                <p class="bold"><?= lang("note"); ?>:</p>

                                <div><?= $this->sma->decode_html($inv->note); ?></div>
                            </div>
                        <?php } ?>
                    </div>

                    <!-- <div class="col-xs-4 col-xs-offset-1">
                        <div class="well well-sm">
                            <p><?= lang("created_by"); ?>
                                : <?= $created_by->first_name . ' ' . $created_by->last_name; ?> </p>

                            <p><?= lang("date"); ?>: <?= $this->sma->hrsd($inv->date); ?></p>
                            <?php if ($inv->updated_by) { ?>
                                <p><?= lang("updated_by"); ?>
                                    : <?= $updated_by->first_name . ' ' . $updated_by->last_name;; ?></p>
                                <p><?= lang("update_at"); ?>: <?= $this->sma->hrsd($inv->updated_at); ?></p>
                            <?php } ?>
                        </div>
                    </div> -->
                </div>
            </div>
        </div>
        <?php if (!$Supplier) { ?>
             <!-- || !$Customer -->
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
                        <a href="<?= site_url('purchases/add/'. $inv->id.'/smig') ?>" class="tip btn btn-primary" title="<?= lang('create_invoice') ?>">
                            <i class="fa fa-plus-circle"></i> <span class="hidden-sm hidden-xs"><?= lang('create_purchase') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="<?= site_url('deliveries_smig/pdf/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('download_pdf') ?>">
                            <i class="fa fa-download"></i> <span class="hidden-sm hidden-xs"><?= lang('pdf') ?></span>
                        </a>
                    </div>
                    <!-- <div class="btn-group">
                        <a href="<?= site_url('deliveries_smig/email/' . $inv->id) ?>" data-toggle="modal" data-target="#myModal"  data-backdrop="static" class="tip btn btn-info tip" title="<?= lang('email') ?>">
                            <i class="fa fa-envelope-o"></i> <span class="hidden-sm hidden-xs"><?= lang('email') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="<?= site_url('quotes/edit/' . $inv->id) ?>" class="tip btn btn-warning tip" title="<?= lang('edit') ?>">
                            <i class="fa fa-edit"></i> <span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="#" class="tip btn btn-danger bpo" title="<b><?= $this->lang->line("delete_quote") ?></b>" 
                            data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= site_url('quotes/delete/' . $inv->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>" 
                            data-html="true" data-placement="top">
                            <i class="fa fa-trash-o"></i> <span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
                        </a>
                    </div> -->
                </div>
            </div>
        <?php } ?>
    </div>
</div>
