<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $this->lang->line('purchase') . ' ' . $po ? $inv->cf1 : $inv->reference_no; ?></title>
    <link href="<?php echo $assets ?>styles/style.css?v=<?=FORCAPOS_VERSION?>" rel="stylesheet">
    <style type="text/css">
        html, body {
            height: 100%;
            background: #FFF;
        }
        body:before, body:after {
            display: none !important;
        }
        .table th {
            text-align: center;
            padding: 5px;
        }
        .table td {
            padding: 4px;
        }
    </style>
</head>

<body>
<div id="wrap">
    <div class="row">
        <div class="col-lg-12">
            <?php if ($logo) { ?>
                <div class="text-center" style="margin-bottom:20px;">
                    <img src="<?= base_url('assets/uploads/logos/' . $biller->logo); ?>" alt="<?= $biller->company != '-' ? $biller->company : $biller->name; ?>" width="200">
                </div>
            <?php } ?>
            <div class="text-center" style="margin-bottom:20px;">
                <img src="<?= $this->session->userdata('avatar') ? site_url() . 'assets/uploads/avatars/thumbs/' . $this->session->userdata('avatar') : base_url() . 'assets/uploads/logos/logo3.png'; ?>">
            </div>
            <div class="clearfix"></div>
            <div class="row padding11">
                <div class="col-xs-6">
                    <h2 class=""><?= $biller->company != '-' ? $biller->company : $biller->name; ?></h2>
                    <?= $biller->company ? '' : 'Attn: ' . $biller->name; ?>
                    <?php
                        echo $biller->address . '<br />' . $biller->city . ' ' . $biller->postal_code . ' ' . $biller->state . '<br />' . $biller->country.'<br />';
                    ?>
                    <?= lang("tel").'  :  '.$biller->phone .'<br />'?>
                    <?= lang("email").'  :  '.$biller->email .'<br />'?>
                    <div class="clearfix"></div>
                </div>
            </div>
            <h2 class="text-center">Subscription Invoice</h2>
            <br>
            <div class="table-responsive">
                <table class="">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>&nbsp; : &nbsp; </th>
                        <th><?= $rows->date ?></th>
                    </tr>
                    <tr>
                        <th>No Reff</th>
                        <th>&nbsp; : &nbsp; </th>
                        <th><?= $rows->reference_no ?></th>
                    </tr>
                    <tr>
                        <th>Payment Status</th>
                        <th>&nbsp; : &nbsp; </th>
                        <th><?= $rows->payment_status ?></th>
                    </tr>
                    </thead>
                </table>
                <br>
                <table class="table table-bordered table-hover table-striped">
                    <thead>
                    <tr>
                        <th class="text-center"><?= lang("no"); ?></th>
                        <th class="text-center"><?= lang("description"); ?></th>
                        <th class="text-center"><?= lang("item_price"); ?></th>
                        <th class="text-center"><?= lang("quantity"); ?></th>
                        <th class="text-center"><?= lang("price"); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center">1</td>
                            <td><?= lang('Change_subscription_to').' '.$rows->plan_name ;?> </td>
                            <td class="text-center"><?= $this->sma->formatMoney($rows->price) ?></td>
                            <td class="text-center">1</td>
                            <td class="text-right"><?= $this->sma->formatMoney($rows->price) ?></td>
                        </tr>
                    <?php $r = 2; $grand_total = 0;
                        if($item){
                            foreach ($item as $row){
                    ?>
                        <tr>
                            <td class="text-center"><?= $r ?></td>
                            <td><?= $row->addon_name ;?> </td>
                            <td class="text-center"><?= $this->sma->formatMoney($row->price).' For 5 Item ' ;?> </td>
                            <td class="text-center"><?= $this->sma->formatDecimal($row->quantity) ;?> </td>
                            <td class="text-right"><?= $this->sma->formatMoney($row->subtotal) ?></td>
                        </tr>
                    <?php
                                $r++;
                            }
                        }
                    ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="text-center"></td>
                            <td colspan="3" class="text-right"><?= lang('amount').' ('.$this->Settings->default_currency.')' ;?> </td>
                            <td class="text-right"><?= $this->sma->formatMoney($rows->subtotal) ?></td>
                        </tr>
                        <tr>
                            <td class="text-center"></td>
                            <td colspan="3" class=""><?= lang('Payment_period').' : '.$this->sma->formatMoney($rows->subtotal).' x '.$rows->payment_period.' Month' ;?> </td>
                            <td class="text-right"><?= $this->sma->formatMoney($rows->total) ?></td>
                        </tr>
                        <tr >
                            <td class="text-center"></td>
                            <td colspan="3" class="text-right" style="font-weight: bold;"><b><?= lang('total_amount').' ('.$this->Settings->default_currency.')' ;?> </b></td>
                            <td class="text-right" style="font-weight: bold;"><?= $this->sma->formatMoney($rows->amount) ?></td>
                        </tr>
                        <tr >
                            <td class="text-center"></td>
                            <td colspan="3" class="text-right" style="font-weight: bold;"><b><?= lang('paid').' ('.$this->Settings->default_currency.')' ;?> </b></td>
                            <td class="text-right" style="font-weight: bold;"><?= $this->sma->formatMoney($rows->amount) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- <div class="row">
                <div class="col-xs-4  pull-left">

                </div>
                <div class="col-xs-4  pull-right">
                    <p style="height: 80px;"><?= lang('Distributor'); ?>
                        : <?= $biller->company ? $biller->company : $biller->name; ?> </p>
                    <hr>
                    <p><?= lang('stamp_sign'); ?></p>
                </div>
                <div class="clearfix"></div>
            </div> -->

        </div>
    </div>
</div>
</body>
</html>