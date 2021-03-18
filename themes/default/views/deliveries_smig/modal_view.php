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
                    </div>
                    <div class="col-xs-7 text-right order_barcodes">
                        <?= $this->sma->save_barcode($inv->no_do, 'code128', 66, false); ?>&emsp;&emsp;
                        <!-- <?= $this->sma->qrcode('link', urlencode(site_url('deliveries_smig/view/' . $inv->id)), 2); ?> -->
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>

            <div class="row" style="margin-bottom:15px;">
                <div class="col-xs-6">
                    <h2 style="margin-top:10px;"><?php echo $this->lang->line("From"); ?> :<br/></h2>
                    <h2 style="margin-top:10px;"><?= $supplier->company ? $supplier->company : $supplier->name; ?></h2>
                    <?= $supplier->company ? "" : "Attn: " . $supplier->name ?>

                    <?php
                    echo $supplier->address . "<br>" . $supplier->city . " " . $supplier->postal_code . " " . $supplier->state . "<br>" . $supplier->country;
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
                </div>
                <div class="col-xs-6">
                    <h2 style="margin-top:10px;"><?php echo $this->lang->line("To"); ?> :<br/></h2>
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
            </div>
            <div class="row" style="margin-bottom:15px;">
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
                                <?= $row->product_code.' - '.$row->product_name ; ?>
                            </td>
                            <td style="width: 80px; text-align:center; vertical-align:middle;">
                                <?= $this->sma->formatQuantity($row->unit_quantity)?>
                            </td>
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
            <div class="row" style="margin-bottom:15px;">
                <div class="col-xs-12">
                    <?php
                    echo lang("Note_2");
                    ?>
                </div>
                <br>
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
                            <a href="<?= site_url('purchases/add/' . $inv->id.'/smig') ?>" class="tip btn btn-primary" title="<?= lang('create_purchase') ?>">
                                <i class="fa fa-star"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('create_purchase') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="<?= site_url('deliveries_smig/pdf/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('download_pdf') ?>">
                                <i class="fa fa-download"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('pdf') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a 
                                <?php if($url) { ?> 
                                    href="<?=$url?>" target="_blank" 
                                <?php } else{ ?>
                                    data-toggle="modal" data-target="#statusmodal" id="status"
                                <?php } ?>
                                class="tip btn btn-warning sledit" title="<?= lang('Live_Tracking') ?>">
                                <i class="fa fa-truck"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('Live_Tracking') ?></span>
                            </a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<!-- remove brand modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="statusmodal">
    <div class="modal-dialog" role="document" style="width: 20%;">
        <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" aria-label="Close" id="close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
            <center> 
                <p><?= lang('status_tracking') ?></p>
            </center>
        </div>
        <div class="modal-footer">
            <center>    
                <button type="button" class="btn btn-primary" id="ok">Ok</button>
            </center>
        </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
  
<script type="text/javascript">
    $(document).ready( function() {
        $('.tip').tooltip();
        $("#status").click(function() {
            $("#statusmodal").show();
            $('#statusmodal').modal({backdrop: false}); 
        });
        $("#ok").click(function() {
            $("#statusmodal").hide(); 
        });
        $("#close").click(function() {
            $("#statusmodal").hide(); 
        });
    });
</script>
