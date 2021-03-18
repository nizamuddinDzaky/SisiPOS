<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-md no-modal-header">
    <div class="modal-content">
        <div class="modal-body">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
<!--            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>-->
            <div class="text-center" style="margin-bottom:20px;">
                <img src="<?= avatar_image_logo($this->session->userdata('avatar'), $Settings->logo) ?>"
                     alt="<?= $biller->company != '-' ? $biller->company : $biller->name; ?>" height="150px">
            </div>
            <div class="well well-sm">
                <div class="row bold">
                    <div class="col-xs-5">
                    <p class="bold">
                        <?= lang("date"); ?>: <?= $this->sma->hrld($inv->date); ?><br>
                        <?= lang("ref"); ?>: <?= $inv->reference_no; ?><br>
                        <?= lang("payment_status"); ?>: <?= lang($inv->payment_status); ?>
                    </p>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>

            <div class="row" style="margin-bottom:15px;">
                <div class="col-xs-6">
                <?php foreach($biller as $row){?>
                    <?php echo $this->lang->line("company"); ?>:
                    <h2 style="margin-top:10px;"><?= $row->company != '-' ? $row->company : $row->name; ?></h2>
                    <?= $row->company ? "" : "Attn: " . $row->name ?>
                    
                    <?php
                    echo $row->address . "<br>" . $row->city . " " . $row->postal_code . " " . $row->state . "<br>" . $row->country;

                    echo "<p>";

                    if ($row->vat_no != "-" && $row->vat_no != "") {
                        echo "<br>" . lang("vat_no") . ": " . $biller->vat_no;
                    }
                    if ($row->cf1 != "-" && $row->cf1 != "") {
                        echo "<br>" . lang("bcf1") . ": " . $biller->cf1;
                    }
                    if ($row->cf2 != "-" && $row->cf2 != "") {
                        echo "<br>" . lang("bcf2") . ": " . $biller->cf2;
                    }
                    if ($row->cf3 != "-" && $row->cf3 != "") {
                        echo "<br>" . lang("bcf3") . ": " . $biller->cf3;
                    }
                    if ($row->cf4 != "-" && $row->cf4 != "") {
                        echo "<br>" . lang("bcf4") . ": " . $biller->cf4;
                    }
                    if ($row->cf5 != "-" && $row->cf5 != "") {
                        echo "<br>" . lang("bcf5") . ": " . $biller->cf5;
                    }
                    if ($row->cf6 != "-" && $row->cf6 != "") {
                        echo "<br>" . lang("bcf6") . ": " . $biller->cf6;
                    }

                    echo "</p>";
                    echo lang("tel") . ": " . $row->phone . "<br>" . lang("email") . ": " . $row->email;
                    ?>
                <?php } ?>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped print-table order-table">
                    <thead>
                        <tr>
                            <th><?= lang("no"); ?></th>
                            <th><?= lang("description"); ?></th>
                            <!--<th><?= lang("quantity"); ?></th>-->
                            <th><?= lang("price"); ?></th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php $r = 1;?>
                        <tr>
                            <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                            <td style="vertical-align:middle;">
                                <?= lang('upgrade_account_to_premium'); ?>
                            </td>
                            <td style="text-align:right; width:120px;"><?= $this->sma->formatMoney($inv->total); ?></td>
                        </tr>
                    <?php
                    $r++;
                    ?>
                    </tbody>
                    <tfoot>
                    <?php
                    $col = 2;
                    ?>
                        <tr>
                            <td colspan="<?= $col; ?>"
                                style="text-align:right; font-weight:bold;"><?= lang("total_amount"); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td style="text-align:right; padding-right:10px; font-weight:bold;"><?= $this->sma->formatMoney($inv->total); ?></td>
                        </tr>
                        <tr>
                            <td colspan="<?= $col; ?>"
                                style="text-align:right; font-weight:bold;"><?= lang("paid"); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney($inv->payment_amount); ?></td>
                        </tr>
                        <tr>
                            <td colspan="<?= $col; ?>"
                                style="text-align:right; font-weight:bold;"><?= lang("balance"); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney($inv->payment_amount-$inv->total); ?></td>
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
<script type="text/javascript">
    $(document).ready( function() {
        $('.tip').tooltip();
    });
</script>
