<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<link href="<?= $assets ?>styles/print.css"rel=" stylesheet"/>
<style>
    body,table{
        font-family: "Times New Roman", Times, serif; !important;
    }
    .bold{
        font-weight: bold;
    }
    .form-group{
        padding-bottom: 12px;
        margin-bottom: 12px;
    }
    body{
        font-family: "Calibri", sans-serif;
    }
    table{
        font-family: "Calibri", sans-serif;
    }
    .text-right{
        text-align: right;
    }
    /*@media  print {*/
    /*a [href]:after {content:none !important;}*/
    /*img [src]:after {content:none !important;}*/
    /*}*/
    .borderright b{
        padding-left: 30px;padding-bottom: 0;font-size: 12px;
    }
    .borderright p{
        margin-top: 0;
    }
    .borderright table{
        font-size: 12px;
    }
    .bcimg{
        width: 12rem;
    }
</style>
<div class="box">
    <div class="box-body">
        <table class="table mb-none text-center b" id="">
            <tbody>
            <tr>
                <td>
                    <?php if($po || $inv->delivery_method){ ?>
                        <p style="margin-bottom: 0;"><b style="padding-left: 30px;padding-bottom: 0;font-size: 12px;"><span class="">AksesToko Order Detail</span></b></p>
                        <p class="bold" style="margin: 0; margin-bottom: 5px;"></p>
                        <table style="font-size: 12px; margin-left: 27px">
                            <tr>
                                <td><p style="margin-left:0; margin-top:-10px; font-size: 12px; margin-bottom: 0">TOP</p></td>
                                <td><p style="margin-left:0; margin-top:-10px; font-size: 12px; margin-bottom: 0">:</p></td>
                                <td><p style="margin-left:0; margin-top:-10px; font-size: 12px; margin-bottom: 0"><?= lang($po->payment_duration) ?> Days</p></td>
                            </tr>
                            <tr>
                                <td><p style="margin-left:0; margin-top:-5px; font-size: 12px; margin-bottom: 0"><?= lang('due_date') ?></p></td>
                                <td><p style="margin-left:0; margin-top:-5px; font-size: 12px; margin-bottom: 0">:</p></td>
                                <td><p style="margin-left:0; margin-top:-5px; font-size: 12px; margin-bottom: 0"><?= ($po->payment_deadline && $po->payment_deadline != '0000-00-00 00:00:00' ? $this->sma->hrsd($po->payment_deadline) : '-') ?></p></td>
                            </tr>
                            <tr>
                                <td><p style="margin-left:0; margin-top:-5px; font-size: 12px; margin-bottom: 0"><?= lang('delivery_method') ?></p></td>
                                <td><p style="margin-left:0; margin-top:-5px; font-size: 12px; margin-bottom: 0">:</p></td>
                                <td><p style="margin-left:0; margin-top:-5px; font-size: 12px; margin-bottom: 0"><?= ($inv->delivery_method ? lang($inv->delivery_method) : '-') ?></p></td>
                            </tr>
                            <?php if ($promo) { ?>
                            <tr>
                                <td><p style="margin-left:0; margin-top:-5px; font-size: 12px; margin-bottom: 0"><?= lang('promo_aksestoko') ?></p></td>
                                <td><p style="margin-left:0; margin-top:-5px; font-size: 12px; margin-bottom: 0">:</p></td>
                                <td><p style="margin-left:0; margin-top:-5px; font-size: 12px; margin-bottom: 0"><?= $promo->code_promo ?></p></td>
                            </tr>
                            <?php } ?>
                        </table>
                    <?php } ?>
                </td>
            <td class="pull-right" style="font-size: 12px; float: right;margin-top: 12px">
                <?= $this->sma->save_barcode($inv->reference_no, 'code128', 66, false); ?>
                <!--<?= $this->sma->qrcode('link', urlencode(site_url('sales/view/' . $inv->id)), 2); ?>-->
            </td>
            </tr>
            </tbody>
        </table>
        <table class="table mb-none text-center b" id="">
            <tbody>
            <tr>
                <td class="borderright" style="border-right: 0 dotted !important; vertical-align: top">
                    <p style="margin-bottom: 0;"><b style=""><span class="title"><?php echo $this->lang->line("reference_no"); ?> : <?= $inv->reference_no?></span></b></p>
                    <p style="margin-bottom: 0;"><b style=""><span class="title"><?php echo $this->lang->line("From"); ?> :</span></b></p>
                    <p style="margin-bottom: 0;"><b style=""><span class="title"><?= $biller->company != '-' ? $biller->company : $biller->name; ?></span></b></p>
                    <p style="margin-left:30px; font-size: 12px; margin-bottom: 0">
                        <?= $biller->company ? "" : "Attn: " . $biller->name ?>

                        <?php
                        echo $biller->address . "<br>" . $biller->city . " " . $biller->postal_code . " " . $biller->state . "<br>" . $biller->country."<br>". $biller->phone."<br>".$biller->email;
                        ?>
                    </p>
                    <table style="margin-left: 27px">
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
                    </table>
                </td>
                <td class="borderright" style="width: 50%; border-right: 0 dotted !important; vertical-align: top">

                    <p style="margin-bottom: 0;"><b style=""><span class=""><?php echo $this->lang->line("To"); ?> :</span></b></p>
                    <p style="margin-bottom: 0;"><b style=""><span class=""><?= $customer->company ? $customer->company : $customer->name; ?></span></b></p>
                    <p style="margin-left:30px; font-size: 12px; margin-bottom: 0">
                        <?= $customer->company ? "" : "Attn: " . $customer->name ?>

                        <?php
                        echo $customer->address . "<br>" . $customer->city . " " . $customer->postal_code . " " . $customer->state . "<br>" . $customer->country."<br>".$customer->phone."<br>".$customer->email;
                        ?>
                    </p>
                    <table style="margin-left: 27px">
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
                    </table>
                </td>

            </tr>
            </tbody>
        </table>
        <table class="table table-striped b-light b-t">
            <thead>
            <tr class="grey-200 ">
                <th class="w-xs text-center bordertop2 borderbottom borderright borderleft" style="font-size: 12px;"><?= lang("no"); ?></th>
                <th class="b borderbottom borderright bordertop2" style="font-size: 12px;"><?= lang("description"); ?></th>
                <?php if($inv->sale_status == "closed"){?>
                    <th class="b borderbottom borderright bordertop2" style="font-size: 12px;"><?= lang("quantity_order"); ?></th>
                    <th class="b borderbottom borderright bordertop2" style="font-size: 12px;"><?= lang("quantity_sent"); ?></th>
                <?php }else{?>
                    <th class="b borderbottom borderright bordertop2" style="font-size: 12px;width: 15%"><?= lang("quantity"); ?></th>
                <?php } ?>
                <th class="b borderbottom borderright bordertop2" style="font-size: 12px;width: 14%"><?= lang("unit_price"); ?></th>
                <?php
                if ($Settings->tax1 && $inv->product_tax > 0) {
                    echo '<th class="b borderbottom borderright bordertop2" style="font-size: 12px;width: 15%">' . lang("tax") . '</th>'.'<th></th>';
                }
                if ($Settings->product_discount && $inv->product_discount != 0) {
                    echo '<th class="b borderbottom borderright bordertop2" style="font-size: 12px;width: 15%">' . lang("discount") . '</th>'.'<th></th>';
                }
                ?>
                <th class="b borderbottom borderright bordertop2" style="font-size: 12px;width: 19%"><?= lang("subtotal"); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php $r = 1;
            $tax_summary = array();
            foreach ($rows as $row):
                ?>
                <tr class="row-deletable">
                    <td class="borderright borderleft borderbottom text-center" style="padding-left: 5px;width: 3%;font-size: 12px;"><?= $r; ?></td>
                    <td class="b borderright borderbottom" style="padding: 0;font-size: 12px;">
                        <?= $row->product_code.' - '.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                        <?= $row->details ? '<br>' . $row->details : ''; ?>
                        <?= $row->serial_no ? '<br>' . $row->serial_no : ''; ?>
                    </td>
                    <?php if($inv->sale_status == "closed"){?>
                        <td class="b borderright borderbottom text-center" style="padding-left: 5px;width: 3%;font-size: 12px;"><?= $this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code; ?></td>
                        <td class="b borderright borderbottom text-center" style="padding-left: 5px;width: 3%;font-size: 12px;"><?= $this->sma->formatQuantity($row->sent_quantity)?></td>
                    <?php }else{?>
                        <td class="b borderright borderbottom text-center" style="padding-left: 5px;width: 3%;font-size: 12px;"><?= $this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code; ?></td>
                    <?php } ?>
                    <td class="b borderright borderbottom text-center" style="padding-left: 5px;width: 3%;font-size: 12px;"><?= $this->sma->formatMoney($row->unit_price); ?></td>
                    <?php
                    if ($Settings->tax1 && $inv->product_tax > 0) {
                        echo '<td class="b borderright borderbottom text-center" style="padding-left: 10px;font-size: 12px;">' . ($row->item_tax != 0 && $row->tax_code ? '<small>('.$row->tax_code.')</small>' : '') . ' ' . $this->sma->formatMoney($row->item_tax) . '</td>';
                    }
                    if ($Settings->product_discount && $inv->product_discount != 0) {
                        echo '<td class="b borderright borderbottom text-center" style="padding-left: 10px;font-size: 12px;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->sma->formatMoney($row->item_discount) . '</td>';
                    }
                    ?>

                    <td class="b borderright borderbottom text-center" style="padding-left: 10px;font-size: 12px;"><?= $this->sma->formatMoney($row->subtotal); ?></td>
                </tr>
                <?php
                $r++;
            endforeach;
            ?>
            <?php if ($return_rows) {
                echo '<tr class="warning">
                        <td colspan="100%" class="borderright borderbottom borderleft" style="font-size: 12px"><b>'.lang('returned_items').'</b></td>
                      </tr>';
                foreach ($return_rows as $row):
                    ?>
                    <tr class="warning">
                        <td class="borderright borderleft borderbottom text-center" style="padding-left: 5px;width: 3%;font-size: 12px;"><?= $r; ?></td>
                        <td class="b borderright borderbottom" style="padding: 0;font-size: 12px;">
                            <?= $row->product_code.' - '.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                            <?= $row->details ? '<br>' . $row->details : ''; ?>
                            <?= $row->serial_no ? '<br>' . $row->serial_no : ''; ?>
                        </td>
                        <?php if($inv->sale_status == "closed"){?>
                            <td class="b borderright borderbottom text-center" style="padding-left: 5px;width: 3%;font-size: 12px;"><?= $this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code; ?></td>
                            <td class="b borderright borderbottom text-center" style="padding-left: 5px;width: 3%;font-size: 12px;">-</td>
                        <?php }else{?>
                            <td class="b borderright borderbottom text-center" style="padding-left: 5px;width: 3%;font-size: 12px;"><?= $this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code; ?></td>
                        <?php } ?>
                        <td class="b borderright borderbottom text-center" style="padding-left: 5px;width: 3%;font-size: 12px;"><?= $this->sma->formatMoney($row->unit_price); ?></td>
                        <?php
                        if ($Settings->tax1 && $inv->product_tax > 0) {
                            echo '<td class="b borderright borderbottom text-center" style="padding-left: 10px;font-size: 12px;">' . ($row->item_tax != 0 && $row->tax_code ? '<small>('.$row->tax_code.')</small>' : '') . ' ' . $this->sma->formatMoney($row->item_tax) . '</td>';
                        }
                        if ($Settings->product_discount && $inv->product_discount != 0) {
                            echo '<td class="b borderright borderbottom text-center" style="padding-left: 10px;font-size: 12px;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->sma->formatMoney($row->item_discount) . '</td>';
                        }
                        ?>
                        <td class="b borderright borderbottom text-center" style="padding-left: 25px;font-size: 12px;"><?= $this->sma->formatMoney($row->subtotal); ?></td>
                    </tr>
                    <?php
                    $r++;
                endforeach;
            }?>
            </tbody>
        </table>
        <table class="table table-striped b-light b-t">
            <tbody>
            <?php $r = 1;
            $tax_summary = array();?>
            <?php
            if($inv->sale_status == "closed"){
                $span=4;
            }else{
                $span=3;
            }?>
            <?php if ($inv->grand_total != $inv->total) { ?>
                <tr>
                    <td colspan="<?php echo $span?>" class="noborder borderleft borderbottom" style="padding: 0;font-size: 12px;margin: 0;"></td>
                    <td class="text-right noborder borderbottom" style="padding: 0;font-size: 12px;margin: 0;"><?= lang("total"); ?> (<?= $default_currency->code; ?>)</td>
                    <?php
                    if ($Settings->tax1 && $inv->product_tax > 0) {
                        echo '<td class="text-right bold noborder borderbottom " style="padding: 0;font-size: 12px;margin: 0;">' . $this->sma->formatMoney($return_sale ? ($inv->product_tax+$return_sale->product_tax) : $inv->product_tax) . '</td>';
                    }
                    if ($Settings->product_discount && $inv->product_discount != 0) {
                        echo '<td class="text-right bold noborder borderbottom " style="padding: 0;font-size: 12px;margin: 0;">' . $this->sma->formatMoney($return_sale ? ($inv->product_discount+$return_sale->product_discount) : $inv->product_discount) . '</td>';
                    }
                    ?>
                    <td class="noborder borderbottom borderright" style="padding-left: 10px;font-size: 12px;margin: 0;width: 14.5%"><?= $this->sma->formatMoney($return_sale ? (($inv->total + $inv->product_tax)+($return_sale->total + $return_sale->product_tax)) : ($inv->total + $inv->product_tax)); ?></td>
                </tr>
            <?php } ?>
            <?php
            if ($return_sale) {?>
                <tr>
                    <td colspan="<?php echo $span?>" class="noborder borderleft borderbottom" style="padding: 0;font-size: 12px;margin: 0;"></td>
                    <td class="text-right  noborder borderbottom" style="padding: 0;font-size: 12px;margin: 0;"><?= lang("return_total") . ' (' . $default_currency->code . ')'?></td>
                    <td class=" noborder borderbottom borderright" style="padding-left: 10px;font-size: 12px;margin: 0;"><?= $this->sma->formatMoney($return_sale->grand_total)?></td>
                </tr>
            <?php }
            if ($inv->surcharge != 0) {?>
                <tr>
                    <td colspan="<?php echo $span?>" class="noborder borderleft borderbottom" style="padding: 0;font-size: 12px;margin: 0;"></td>
                    <td class="text-right  noborder borderbottom" style="padding: 0;font-size: 12px;margin: 0;"><?= lang("return_surcharge") . ' (' . $default_currency->code . ')'?></td>
                    <td class=" noborder borderbottom borderright" style="padding-left: 10px;font-size: 12px;margin: 0;"><?= $this->sma->formatMoney($inv->surcharge) ?></td>
                </tr>
            <?php }
            if ((int)$inv->charge != 0) {?>
                <tr>
                    <td colspan="<?php echo $span?>" class="noborder borderleft borderbottom" style="padding: 0;font-size: 12px;margin: 0;"></td>
                    <td class="text-right  noborder borderbottom" style="padding: 0;font-size: 12px;margin: 0;"><?= lang("charge") . ' (' . $default_currency->code . ')'?></td>
                    <td class=" noborder borderbottom borderright" style="padding-left: 10px;font-size: 12px;margin: 0;"><?= $this->sma->formatMoney($inv->charge)?></td>
                </tr>
            <?php }
            if ((int)$inv->correction_price != 0) { ?>
                <tr>
                    <td colspan="<?php echo $span?>" class="noborder borderleft borderbottom" style="padding: 0;font-size: 12px;margin: 0;"></td>
                    <td class="text-right  noborder borderbottom" style="padding: 0;font-size: 12px;margin: 0;"><?= lang("correction_price") . ' (' . $default_currency->code . ')'?></td>
                    <td class=" noborder borderbottom borderright" style="padding-left: 10px;font-size: 12px;margin: 0;"><?= $this->sma->formatMoney($inv->correction_price)?></td>
                </tr>
            <?php }
            if ($inv->order_discount != 0) {?>
                <tr>
                    <td colspan="<?php echo $span?>" class="noborder borderleft borderbottom" style="padding: 0;font-size: 12px;margin: 0;"></td>
                    <td class="text-right  noborder borderbottom" style="padding: 0;font-size: 12px;margin: 0;"><?= lang("order_discount") . ($promo ? ' [AksesToko=' . $promo->code_promo .']' : '') . ' (' . $default_currency->code . ')'?></td>
                    <td class=" noborder borderbottom borderright" style="padding-left: 10px;font-size: 12px;margin: 0;"><?= ($inv->order_discount_id ? '' /* '<small>('.$inv->order_discount_id.')</small> ' */ : '') . $this->sma->formatMoney($return_sale ? ($inv->order_discount+$return_sale->order_discount) : $inv->order_discount) ?></td>
                </tr>
            <?php }
            if ($Settings->tax2 && $inv->order_tax != 0) { ?>
                <tr>
                    <td colspan="<?php echo $span?>" class="noborder borderleft borderbottom" style="padding: 0;font-size: 12px;margin: 0;"></td>
                    <td class="text-right  noborder borderbottom" style="padding: 0;font-size: 12px;margin: 0;"><?= lang("order_tax") . ' (' . $default_currency->code . ')'?></td>
                    <td class=" noborder borderbottom borderright" style="padding-left: 10px;font-size: 12px;margin: 0;"><?= $this->sma->formatMoney($return_sale ? ($inv->order_tax+$return_sale->order_tax) : $inv->order_tax) ?></td>
                </tr>
            <?php }
            if ($inv->shipping != 0) {?>
                <tr>
                    <td colspan="<?php echo $span?>" class="noborder borderleft borderbottom" style="padding: 0;font-size: 12px;margin: 0;"></td>
                    <td class="text-right  noborder borderright borderbottom" style="padding: 0;font-size: 12px;margin: 0;"><?= lang("shipping") . ' (' . $default_currency->code . ')'?></td>
                    <td class=" noborder borderbottom borderright" style="padding-left: 10px;font-size: 12px;margin: 0;"><?= $this->sma->formatMoney($inv->shipping) ?></td>
                </tr>
            <?php }
            ?>
            <tr>
                <td colspan="<?php echo $span?>" class="noborder borderleft borderbottom" style="padding: 0;font-size: 12px;margin: 0;"></td>
                <td class="text-right bold noborder borderbottom" style="padding: 0;font-size: 12px;margin: 0;"><?= lang("total_amount"); ?> (<?= $default_currency->code; ?>)</td>
                <td class=" noborder borderbottom borderright" style="padding-left: 10px;font-size: 12px;margin: 0;width: 19%"><?= $this->sma->formatMoney($return_sale ? ($inv->grand_total+$return_sale->grand_total) : $inv->grand_total); ?></td>
            </tr>
            <tr>
                <td colspan="<?php echo $span?>" class="noborder borderleft borderbottom" style="padding: 0;font-size: 12px;margin: 0;"></td>
                <td class="text-right bold noborder borderbottom" style="padding: 0;font-size: 12px;margin: 0;"><?= lang("paid"); ?> (<?= $default_currency->code; ?>)</td>
                <td class=" noborder borderbottom borderright" style="padding-left: 10px;font-size: 12px;margin: 0;"><?= $this->sma->formatMoney($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid); ?></td>
            </tr>
            <tr>
                <td colspan="<?php echo $span?>" class="noborder borderleft borderbottom" style="padding: 0;font-size: 12px;margin: 0;"></td>
                <td class="text-right bold noborder borderbottom" style="padding: 0;font-size: 12px;margin: 0;"><?= lang("balance"); ?> (<?= $default_currency->code; ?>) </td>
                <td class=" noborder borderright borderbottom" style="padding-left: 10px;font-size: 12px;margin-left: 10px;"><?= $this->sma->formatMoney(($return_sale ? ($inv->grand_total+$return_sale->grand_total) : $inv->grand_total) - ($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid)); ?></td>
            </tr>
            <tr>
                <td colspan="<?php echo $span?>" rowspan="<?php echo $inv->updated_by!=null?4:2 ?>" class="noborder" style="padding: 0;font-size: 12px;margin: 0;">
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
                                '<p>'.lang('this_sale').': '.floor(($inv->grand_total/$Settings->each_spent)*$Settings->ca_point)
                                .'<br>'.
                                lang('total').' '.lang('award_points').': '. $customer->award_points . '</p>';?>
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
                </td>
                <td class="text-right bold noborder" style="padding: 0;font-size: 12px;margin: 0;"><?= lang("created_by"); ?> </td>
                <td class=" noborder" style="padding-left: 10px;font-size: 12px;margin-left: 10px;"><?= $created_by->first_name . ' ' . $created_by->last_name; ?></td>
            </tr>
            <tr>

                <td class="text-right bold noborder" style="padding: 0;font-size: 12px;margin: 0;"><?= lang("date"); ?> </td>
                <td class=" noborder" style="padding-left: 10px;font-size: 12px;margin-left: 10px;"><?= $this->sma->hrld($inv->date); ?></td>
            </tr>
            <?php if ($inv->updated_by) { ?>
                <tr>
                    <td class="text-right bold noborder" style="padding: 0;font-size: 12px;margin: 0;"><?= lang("updated_by"); ?> </td>
                    <td class=" noborder" style="padding-left: 10px;font-size: 12px;margin-left: 10px;"><?= $updated_by->first_name . ' ' . $updated_by->last_name;; ?></td>
                </tr>
                <tr>
                    <td class="text-right bold noborder" style="padding: 0;font-size: 12px;margin: 0;"><?= lang("update_at"); ?></td>
                    <td class=" noborder" style="padding-left: 10px;font-size: 12px;margin-left: 10px;"><?= $this->sma->hrld($inv->updated_at); ?></td>
                </tr>
            <?php }?>

            </tbody>
        </table>
    </div>
</div>
<script>
    window.print();
</script>