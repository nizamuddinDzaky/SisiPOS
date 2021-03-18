<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<link href="<?= $assets ?>styles/print.css" rel="stylesheet"/>
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
        padding-left: 30px;padding-bottom: 0;font-size: 13px;
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
    .table > tbody > tr.warning > td{
        color: black;
        background-color: white;
    }
</style>
<div class="box">
    <div class="box-body">
        <h5 style="margin-bottom: 0;text-align: center;"><?=lang("purchase");?></h5>
        <table class="table mb-none text-center b" id="">
            <tbody>
            <tr>
                <td>
                        <table style="font-size: 12px; margin-left: 27px">
                            <tr>
                                <td><p style="margin-left:0; margin-top:-8px; font-size: 12px; margin-bottom: 0"><?= lang("date"); ?></p></td>
                                <td><p style="margin-left:0; margin-top:-8px; font-size: 12px; margin-bottom: 0">:</p></td>
                                <td><p style="margin-left:0; margin-top:-8px; font-size: 12px; margin-bottom: 0"><?= $this->sma->hrld($inv->date); ?></p></td>
                            </tr>
                            <tr>
                                <td><p style="margin-left:0; margin-top:-8px; font-size: 12px; margin-bottom: 0"><?= lang("ref"); ?></p></td>
                                <td><p style="margin-left:0; margin-top:-8px; font-size: 12px; margin-bottom: 0">:</p></td>
                                <td><p style="margin-left:0; margin-top:-8px; font-size: 12px; margin-bottom: 0"><?= $inv->reference_no; ?></p></td>
                            </tr>
                            <?php if (!empty($inv->return_purchase_ref)) {?>
                                <tr>
                                    <td><p style="margin-left:0; margin-top:-8px; font-size: 12px; margin-bottom: 0"><?= lang("return_ref")?></p></td>
                                    <td><p style="margin-left:0; margin-top:-8px; font-size: 12px; margin-bottom: 0">:</p></td>
                                    <td><p style="margin-left:0; margin-top:-8px; font-size: 12px; margin-bottom: 0"><?= $inv->return_purchase_ref; ?></p></td>
                                </tr>
                            <?php } ?>
                            <tr>
                                <td><p style="margin-left:0; margin-top:-8px; font-size: 12px; margin-bottom: 0"><?= lang("status"); ?></p></td>
                                <td><p style="margin-left:0; margin-top:-8px; font-size: 12px; margin-bottom: 0">:</p></td>
                                <td><p style="margin-left:0; margin-top:-8px; font-size: 12px; margin-bottom: 0"><?= lang($inv->status); ?></p></td>
                            </tr>
                            <tr>
                                <td><p style="margin-left:0; margin-top:-8px; font-size: 12px; margin-bottom: 0"><?= lang("payment_status"); ?></p></td>
                                <td><p style="margin-left:0; margin-top:-8px; font-size: 12px; margin-bottom: 0">:</p></td>
                                <td><p style="margin-left:0; margin-top:-8px; font-size: 12px; margin-bottom: 0"><?= lang($inv->payment_status); ?></p></td>
                            </tr>
                        </table>
                </td>
                <td class="pull-right" style="font-size: 13px; float: right;margin-top: 12px; margin-bottom: 12px">
                    <?= $this->sma->save_barcode($inv->reference_no, 'code128', 66, false); ?>
                    <!--<?= $this->sma->qrcode('link', urlencode(site_url('purchases/view/' . $inv->id)), 2); ?>-->
                </td>
            </tr>
            </tbody>
        </table>
        <table class="table mb-none text-center b" id="">
            <tbody>
            <tr>
                <td class="borderright" style="border-right: 0 dotted !important; vertical-align: top">
                    <p style="margin-bottom: 0;"><b style=""><span class="title"><?php echo $this->lang->line("From"); ?> :</span></b></p>
                    <p style="margin-bottom: 0;"><b style=""><span class="title"><?= $Settings->site_name; ?></span></b></p>
                    <p style="margin-left:30px; font-size: 12px; margin-bottom: 0">
                        <?= $warehouse->name ?>

                        <?php
                        echo $warehouse->address;
                        echo ($warehouse->phone ? lang("tel") . ": " . $warehouse->phone . "<br>" : '') . ($warehouse->email ? lang("email") . ": " . $warehouse->email : '');
                        ?>
                    </p>
                </td>
                <td class="borderright" style="width: 50%; border-right: 0 dotted !important; vertical-align: top">

                    <p style="margin-bottom: 0;"><b style=""><span class=""><?php echo $this->lang->line("To"); ?> :</span></b></p>
                    <p style="margin-bottom: 0;"><b style=""><span class=""><?= $supplier->company ? $supplier->company : $supplier->name; ?></span></b></p>
                    <p style="margin-left:30px; font-size: 12px; margin-bottom: 0">
                        <?= $supplier->company ? "" : "Attn: " . $supplier->name ?>

                        <?php
                        echo $supplier->address . "<br />" . $supplier->city . " " . $supplier->postal_code . " " . $supplier->state . "<br />" . $supplier->country;

                        echo "<p style='margin-left:30px; font-size: 12px; margin-bottom: 0'>";

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
                        echo "</p><p style='margin-left:30px; font-size: 12px; margin-bottom: 0'>";
                        echo lang("tel") . ": " . $supplier->phone . "<br />" . lang("email") . ": " . $supplier->email;
                        echo "</p>";
                        ?>
                    </p>
                </td>
            </tr>
            </tbody>
        </table>
        <table class="table table-striped b-light b-t">
            <thead>
            <tr class="grey-200 ">
                <th class="w-xs text-center bordertop2 borderbottom borderright borderleft" style="font-size: 12px;"><?= lang("no"); ?></th>
                <th class="b borderbottom borderright bordertop2" style="font-size: 12px;"><?= lang("description"); ?></th>
                <th class="b borderbottom borderright bordertop2" style="font-size: 12px;width: 15%"><?= lang("quantity"); ?></th>
                <?php
                if ($inv->status == 'partial') {
                    echo '<th class="b borderbottom borderright bordertop2" style="font-size: 12px;width: 15%">'.lang("received").'</th>';
                }
                ?>
                <th class="b borderbottom borderright bordertop2" style="font-size: 12px;width: 14%"><?= lang("unit_cost"); ?></th>
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
                        <?= $row->supplier_part_no ? '<br>'.lang('supplier_part_no').': ' . $row->supplier_part_no : ''; ?>
                        <?= $row->details ? '<br>' . $row->details : ''; ?>
                        <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>'.lang('expiry').': ' . $this->sma->hrsd($row->expiry) : ''; ?>
                    </td>
                    <td class="b borderright borderbottom text-center" style="padding-left: 5px;width: 3%;font-size: 12px;"><?= $this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code; ?></td>
                    <?php
                    if ($inv->status == 'partial') {?>
                        <td class="b borderright borderbottom text-center" style="padding-left: 5px;width: 3%;font-size: 12px;"><?= $this->sma->formatQuantity($row->quantity_received).' '.$row->product_unit_code ?></td>
                    <?php }
                    ?>

                    <td class="b borderright borderbottom text-center" style="padding-left: 5px;width: 3%;font-size: 12px;"><?= $this->sma->formatMoney($row->net_unit_cost); ?></td>
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
                            <?= $row->supplier_part_no ? '<br>'.lang('supplier_part_no').': ' . $row->supplier_part_no : ''; ?>
                            <?= $row->details ? '<br>' . $row->details : ''; ?>
                            <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>'.lang('expiry').': ' . $this->sma->hrsd($row->expiry) : ''; ?>
                        </td>
                        <td class="b borderright borderbottom text-center" style="padding-left: 5px;width: 3%;font-size: 12px;"><?= $this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code; ?></td>
                        <?php
                        if ($inv->status == 'partial') {?>
                            <td class="b borderright borderbottom text-center" style="padding-left: 5px;width: 3%;font-size: 12px;"><?= $this->sma->formatQuantity($row->quantity_received).' '.$row->product_unit_code ?></td>
                        <?php }
                        ?>

                        <td class="b borderright borderbottom text-center" style="padding-left: 5px;width: 3%;font-size: 12px;"><?= $this->sma->formatMoney($row->net_unit_cost); ?></td>
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
            }?>
            </tbody>
        </table>
        <table class="table table-striped b-light b-t">
            <tbody>
            <?php $r = 1;
            $tax_summary = array();?>
            <?php
            if($inv->sale_status == "partial"){
                $span=6;
            }else{
                $span=5;
            }?>
            <?php if ($inv->grand_total != $inv->total) { ?>
                <tr>
                    <td colspan="<?= $span-1; ?>" class="noborder borderleft" style="padding: 0;font-size: 13px;margin: 0;"><?= lang("total"); ?> (<?= $default_currency->code; ?>)</td>
                    <?php
                    if ($Settings->tax1 && $inv->product_tax > 0) {
                        echo '<td class="text-right bold noborder borderbottom " style="padding: 0;font-size: 12px;margin: 0;">' . $this->sma->formatMoney($return_sale ? ($inv->product_tax+$return_sale->product_tax) : $inv->product_tax) . '</td>';
                    }
                    if ($Settings->product_discount && $inv->product_discount != 0) {
                        echo '<td class="text-right bold noborder borderbottom " style="padding: 0;font-size: 12px;margin: 0;">' . $this->sma->formatMoney($return_sale ? ($inv->product_discount+$return_sale->product_discount) : $inv->product_discount) . '</td>';
                    }
                    ?>
                    <td class="noborder borderbottom borderright" style="padding-left: 10px;font-size: 12px;margin: 0;width: 14.5%"><?= $this->sma->formatMoney($return_purchase ? (($inv->total + $inv->product_tax)+($return_purchase->total + $return_purchase->product_tax)) : ($inv->total + $inv->product_tax)); ?></td>
                </tr>
            <?php } ?>
            <?php
            if ($return_purchase) {?>
                <tr>
                    <td colspan="<?php echo $span?>" class="noborder borderleft borderbottom" style="padding: 0;font-size: 13px;margin: 0;"></td>
                    <td class="text-right noborder borderbottom" style="padding: 0;font-size: 12px;margin: 0;"><?= lang("return_total") . ' (' . $default_currency->code . ')'?></td>
                    <td class=" noborder borderbottom borderright" style="padding-left: 10px;font-size: 12px;margin: 0;"><?= $this->sma->formatMoney($return_purchase->grand_total)?></td>
                </tr>
            <?php }
            if ($inv->surcharge != 0) {?>
                <tr>
                    <td colspan="<?php echo $span?>" class="noborder borderleft borderbottom" style="padding: 0;font-size: 13px;margin: 0;"></td>
                    <td class="text-right  noborder borderbottom" style="padding: 0;font-size: 12px;margin: 0;"><?= lang("return_surcharge") . ' (' . $default_currency->code . ')'?></td>
                    <td class=" noborder borderbottom borderright" style="padding-left: 10px;font-size: 12px;margin: 0;"><?= $this->sma->formatMoney($inv->surcharge) ?></td>
                </tr>
            <?php }
            if ($inv->order_discount != 0) {?>
                <tr>
                    <td colspan="<?php echo $span?>" class="noborder borderleft borderbottom" style="padding: 0;font-size: 13px;margin: 0;"></td>
                    <td class="text-right  noborder borderbottom" style="padding: 0;font-size: 12px;margin: 0;"><?= lang("order_discount") . ' (' . $default_currency->code . ')'?></td>
                    <td class=" noborder borderbottom borderright" style="padding-left: 10px;font-size: 12px;margin: 0;"><?= ($inv->order_discount_id ? '<small>('.$inv->order_discount_id.')</small> ' : '') . $this->sma->formatMoney($return_purchase ? ($inv->order_discount+$return_purchase->order_discount) : $inv->order_discount) ?></td>
                </tr>
            <?php }
            if ($Settings->tax2 && $inv->order_tax != 0) { ?>
                <tr>
                    <td colspan="<?php echo $span?>" class="noborder borderleft borderbottom" style="padding: 0;font-size: 13px;margin: 0;"></td>
                    <td class="text-right  noborder borderbottom" style="padding: 0;font-size: 12px;margin: 0;"><?= lang("order_tax") . ' (' . $default_currency->code . ')'?></td>
                    <td class=" noborder borderbottom borderright" style="padding-left: 10px;font-size: 12px;margin: 0;"><?= $this->sma->formatMoney($return_purchase ? ($inv->order_tax+$return_purchase->order_tax) : $inv->order_tax) ?></td>
                </tr>
            <?php }
            if ($inv->shipping != 0) {?>
                <tr>
                    <td colspan="<?php echo $span?>" class="noborder borderleft borderbottom" style="padding: 0;font-size: 13px;margin: 0;"></td>
                    <td class="text-right  noborder borderright borderbottom" style="padding: 0;font-size: 12px;margin: 0;"><?= lang("shipping") . ' (' . $default_currency->code . ')'?></td>
                    <td class=" noborder borderbottom borderright" style="padding-left: 10px;font-size: 12px;margin: 0;"><?= $this->sma->formatMoney($inv->shipping) ?></td>
                </tr>
            <?php }
            ?>
            <tr>
                <td colspan="<?php echo $span?>" class="noborder borderleft borderbottom" style="padding: 0;font-size: 13px;margin: 0;"></td>
                <td class="text-right bold noborder borderbottom" style="padding: 0;font-size: 12px;margin: 0;"><?= lang("total_amount"); ?> (<?= $default_currency->code; ?>)</td>
                <td class=" noborder borderbottom borderright" style="padding-left: 10px;font-size: 12px;margin: 0;width: 19%"><?= $this->sma->formatMoney($return_purchase ? ($inv->grand_total+$return_purchase->grand_total) : $inv->grand_total); ?></td>
            </tr>
            <tr>
                <td colspan="<?php echo $span?>" class="noborder borderleft borderbottom" style="padding: 0;font-size: 13px;margin: 0;"></td>
                <td class="text-right bold noborder borderbottom" style="padding: 0;font-size: 12px;margin: 0;"><?= lang("paid"); ?> (<?= $default_currency->code; ?>)</td>
                <td class=" noborder borderbottom borderright" style="padding-left: 10px;font-size: 12px;margin: 0;"><?= $this->sma->formatMoney($return_purchase ? ($inv->paid+$return_purchase->paid) : $inv->paid); ?></td>
            </tr>
            <tr>
                <td colspan="<?php echo $span?>" class="noborder borderleft borderbottom" style="padding: 0;font-size: 13px;margin: 0;"></td>
                <td class="text-right bold noborder borderbottom" style="padding: 0;font-size: 12px;margin: 0;"><?= lang("balance"); ?> (<?= $default_currency->code; ?>) </td>
                <td class=" noborder borderright borderbottom" style="padding-left: 10px;font-size: 12px;margin-left: 10px;"><?= $this->sma->formatMoney(($return_purchase ? ($inv->grand_total+$return_purchase->grand_total) : $inv->grand_total) - ($return_purchase ? ($inv->paid+$return_purchase->paid) : $inv->paid)); ?></td>
            </tr>
            <tr>
                <td colspan="<?php echo $span?>" rowspan="<?php echo $inv->updated_by!=null?4:2 ?>" class="noborder" style="padding: 0;font-size: 13px;margin: 0;">
                    <div class="row">
                        <div <?php echo (($inv->shipping_order || $inv->receiver) ? 'class="col-xs-6 pull-left"' : 'class="col-xs-12"') ?> >
                            <?php if ($inv->note || $inv->note != "") { ?>
                                <div class="well well-sm">
                                    <p class="bold"><?= lang("note"); ?>:</p>
                                    <div><?= $this->sma->decode_html($inv->note); ?></div>
                                </div>
                            <?php } ?>
                        </div>
                        <?php if($inv->shipping_order || $inv->receiver) {?>
                            <div class="col-xs-6 pull-right">
                                <div class="well well-sm">
                                    <p class="bold"><?= lang('shipping_order'); ?></p>
                                    <p><?= lang("delivery_date"); ?>: <?= $this->sma->hrld($inv->shipping_date); ?></p>
                                    <p><?= lang("receiver"); ?>: <?= $inv->receiver; ?></p>
                                </div>
                            </div>
                        <?php } ?>
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
                            <div class="clearfix"></div>
                        <?php } ?>
                    </div>

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

            <?php if ($inv->sino_spj != "-" && $inv->sino_spj != "") { ?>
                <tr>
                    <td colspan="<?php echo $span?>" class="noborder text-right"></td>
                    <td class="text-right bold noborder" style="padding: 0;font-size: 12px;margin: 0;"><?= lang("no_si_spj"); ?></td>
                    <td class=" noborder" style="padding-left: 10px;font-size: 12px;margin-left: 10px;"><?= lang($inv->sino_spj); ?></td>
                </tr>
            <?php } ?>
            <?php if ($inv->sino_do != "-" && $inv->sino_do != "") { ?>
                <tr>
                    <td colspan="<?php echo $span?>" class="noborder text-right"></td>
                    <td class="text-right bold noborder" style="padding: 0;font-size: 12px;margin: 0;"><?= lang("no_si_do"); ?></td>
                    <td class=" noborder" style="padding-left: 10px;font-size: 12px;margin-left: 10px;"><?= lang($inv->sino_do); ?></td>
                </tr>
            <?php } ?>
            <?php if ($inv->sino_so != "-" && $inv->sino_so != "") { ?>
                <tr>
                    <td colspan="<?php echo $span?>" class="noborder text-right"></td>
                    <td class="text-right bold noborder" style="padding: 0;font-size: 12px;margin: 0;"><?= lang("no_si_so"); ?></td>
                    <td class=" noborder" style="padding-left: 10px;font-size: 12px;margin-left: 10px;"><?= lang($inv->sino_so); ?></td>
                </tr>
            <?php } ?>
            <?php if ($inv->sino_billing != "-" && $inv->sino_billing != "") { ?>
                <tr>
                    <td colspan="<?php echo $span?>" class="noborder text-right"></td>
                    <td class="text-right bold noborder" style="padding: 0;font-size: 12px;margin: 0;"><?= lang("no_si_billing"); ?></td>
                    <td class=" noborder" style="padding-left: 10px;font-size: 12px;margin-left: 10px;"><?= lang($inv->sino_billing); ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<script>
    window.print();
</script>