<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<link href="<?= $assets ?>styles/print.css"rel=" stylesheet"/>
<style>
    body,table{
        font-family: Arial, Helvetica, sans-serif !important;
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
    table.bb tr td{
        border-bottom: 1px dotted;
    }
    .mt{
        margin-top: 60px;
    }
</style>
<div class="box">
    <div class="box-body">
        <table style="font-size: 12px" class="table mb-none text-center b" id="">
            <tbody>
            <tr>
                <td width="30%"><?= lang("date"); ?></td>
                <td width="70%"><?= $this->sma->hrld($delivery->date); ?></td>
            </tr>
            <?php if($delivery->status == "returned") { ?>
                <tr>
                    <td><?= lang("return_reference_no"); ?></td>
                    <td><?= $delivery->do_reference_no; ?></td>
                </tr>

                <tr>
                    <td><?= lang("do_reference_no"); ?></td>
                    <td><?= $delivery->return_reference_no; ?></td>
                </tr>
            <?php }
            else{?>
                <tr>
                    <td><?= lang("do_reference_no"); ?></td>
                    <td><?= $delivery->do_reference_no; ?></td>
                </tr>
                <?php
            }

            if($delivery->status != "returned" && $delivery->return_reference_no) { ?>
                <tr>
                    <td><?= lang("return_reference_no"); ?></td>
                    <td><?= $delivery->return_reference_no; ?></td>
                </tr>
            <?php }?>
            <tr>
                <td><?= lang("sale_reference_no"); ?></td>
                <td><?= $delivery->sale_reference_no; ?></td>
            </tr>
            <tr>
                <td><?= lang("customer"); ?></td>
                <td><?= $delivery->customer; ?></td>
            </tr>
            <tr>
                <td><?= lang("address"); ?></td>
                <td><?= $delivery->address; ?></td>
            </tr>
            <tr>
                <td><?= lang("status"); ?></td>
                <td><?= lang($delivery->status); ?></td>
            </tr>
            <!-- need lang -->
            <?php if($delivery->status == "delivered" || $delivery->status == "delivering" ) { ?>
                <tr>
                    <td width="30%"><?= lang("delivering_date"); ?></td>
                    <td width="70%"><?= $this->sma->hrld($delivery->delivering_date); ?></td>
                </tr>
            <?php } ?>
            <?php if($delivery->status == "delivered") { ?>
                <tr>
                    <td width="30%"><?= lang("delivered_date"); ?></td>
                    <td width="70%"><?= $this->sma->hrld($delivery->delivered_date); ?></td>
                </tr>
            <?php } ?>
            <tr>
                <td><?= lang("shipping"); ?></td>
                <td><?php echo $this->sma->formatDecimal($shipping); ?></td>
            </tr>
            <?php if($delivery->spj_file) { ?>
                <tr>
                    <td>View Documents</td>
                    <td>
                        <a href="<?=$delivery->spj_file?>" target="_blank">SPJ Document</a>
                    </td>
                </tr>
            <?php } ?>
            <?php if ($delivery->note) { ?>
                <tr>
                    <td><?= lang("note"); ?></td>
                    <td><?= $this->sma->decode_html($delivery->note); ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <p style="font-weight: bold;margin: 2px;font-size: 12px">Items</p>
        <table class="table table-striped b-light b-t">
            <thead>
            <tr class="grey-200 ">
                <th class="w-xs text-center bordertop2 borderbottom borderright borderleft" style="font-size: 12px;"><?= lang("no"); ?></th>
                <th class="b borderbottom borderright bordertop2" style="font-size: 12px;"><?= lang("description"); ?></th>
                <th class="b borderbottom borderright bordertop2" style="font-size: 12px;width: 15%"><?= lang("quantity"); ?></th>
                <?php if(in_array($delivery->client_id, $new_view) && $delivery->status != "returned") { ?>
                    <th class="b borderbottom borderright bordertop2" style="font-size: 12px;width: 15%"><?= lang("good"); ?></th>
                    <th class="b borderbottom borderright bordertop2" style="font-size: 12px;width: 15%"><?= lang("bad"); ?></th>
                <?php } ?>
            </tr>
            </thead>
            <tbody>
            <?php
            if(!in_array($delivery->client_id, $new_view)){
                $r = 1;
                foreach ($rows as $row):
                    ?>
                    <tr class="row-deletable">
                        <td class="borderright borderleft borderbottom text-center" style="padding-left: 5px;width: 3%;font-size: 12px;"><?= $r; ?></td>
                        <td class="b borderright borderbottom" style="padding: 0;font-size: 12px;">
                            <?= $row->product_code ." - " .$row->product_name .($row->variant ? ' (' . $row->variant . ')' : '');
                            if ($row->details) {
                                echo '<br><strong>' . lang("product_details") . '</strong> ' .
                                    html_entity_decode($row->details);
                            }
                            ?>
                        </td>
                        <td class="b borderright borderbottom text-center" style="padding-left: 10px;font-size: 12px;"><?= $this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code; ?></td>
                    </tr>
                    <?php
                    $r++;
                endforeach;?>
            <?php
            }else{
                $r = 1; $bad = [];
                foreach ($delivery_items as $i => $delivery_item):
                    if((int)$delivery_item->bad_quantity > 0){
                        $bad [] = (int)$delivery_item->bad_quantity;
                    } ?>
                    <tr class="row-deletable">
                        <td class="borderright borderleft borderbottom text-center" style="padding-left: 5px;width: 3%;font-size: 12px;"><?= $i + 1; ?></td>
                        <td class="b borderright borderbottom" style="padding: 0;font-size: 12px;">
                            <?= $delivery_item->product_code ." - " .$delivery_item->product_name?>
                        </td>
                        <td class="b borderright borderbottom" style="padding: 0;font-size: 12px;"><?= $this->sma->formatQuantity($delivery_item->quantity_sent).' '.$delivery_item->product_unit_code; ?></td>
                        <?php if(in_array($delivery->client_id, $new_view) && $delivery->status != "returned") { ?>
                            <td class="b borderright borderbottom" style="padding: 0;font-size: 12px;"><?= $this->sma->formatQuantity($delivery_item->good_quantity ).' '.$delivery_item->product_unit_code; ?></td>
                            <td class="b borderright borderbottom" style="padding: 0;font-size: 12px;"><?= $this->sma->formatQuantity($delivery_item->bad_quantity ).' '.$delivery_item->product_unit_code; ?></td>
                        <?php } ?>
                    </tr>
                    <?php
                    $r++;
                endforeach;?>
            <?php
            }
            ?>
            </tbody>
        </table>
        <table style="font-size: 12px; margin-top: 5px;" class="table mb-none text-center b" id="">
            <?php if ($delivery->status == 'delivered') { ?>
                <tr>
                    <td style="vertical-align: top">
                        <p><?= lang("prepared_by"); ?>:<br> <?= $user->first_name . ' ' . $user->last_name; ?> </p>
                    </td>
                    <td style="vertical-align: top">
                        <p><?= lang("delivered_by"); ?>:<br> <?= $delivery->delivered_by; ?></p>
                    </td>
                    <td style="vertical-align: top">
                        <p><?= lang("received_by"); ?>:<br> <?= $delivery->received_by; ?></p>
                    </td>
                </tr>
            <?php } else { ?>
                <tr>
                    <td style="vertical-align: top">
                        <p><?= lang("prepared_by"); ?> : <?= $user->first_name . ' ' . $user->last_name; ?> </p>
                        <p class="mt"><?= lang("stamp_sign"); ?></p>
                    </td>
                    <td style="vertical-align: top">
                        <p><?= lang("delivered_by"); ?></p>
                        <p class="mt"><?= lang("stamp_sign"); ?></p>
                    </td>
                    <td style="vertical-align: top">
                        <p><?= lang("received_by"); ?></p>
                        <p class="mt"><?= lang("stamp_sign"); ?></p>
                    </td>
                </tr>
            <?php } ?>

        </table>
    </div>
</div>
<script>
    window.print();
</script>