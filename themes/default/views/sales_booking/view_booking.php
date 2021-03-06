<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    $(document).ready(function () {
        $(document).on('click', '.sledit', function (e) {
            if (localStorage.getItem('slitems')) {
                e.preventDefault();
                var href = $(this).attr('href');
                bootbox.confirm("<?=lang('you_will_loss_sale_data')?>", function (result) {
                    if (result) {
                        window.location.href = href;
                    }
                });
            }
        });
        var oTable = $('#DOData').dataTable({
            "aaSorting": [[1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('sales_booking/getDeliveries_booking/null/null?sale_id='.$inv->id) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                if(parseInt(aData[8]) > 0 && aData[9] == null && aData[10] == null && aData[6] != 'returned'){
                    $('td', nRow).addClass('reject1');
                    nRow.className = "delivery_link";
                }else if(parseInt(aData[8]) > 0 && aData[9] == 2 && aData[10] == null && aData[6] != 'returned'){
                    $('td', nRow).addClass('reject2');
                    nRow.className = "delivery_link";
                }else if(aData[9] == 3 || (aData[9] == 2 && aData[10] == 1)){
                    nRow.className = "delivery_link";
                }else{
                    nRow.className = "delivery_link";
                }
                return nRow;
            },
            "aoColumns": [{"bSortable": false,"mRender": checkbox}, {"mRender": fld}, null, null, null, null, {"mRender": ds}, {"bSortable": false,"mRender": attachment}, {"bVisible": false}, {"bVisible": false}, {"bVisible": false}, {"bSortable": false}]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('do_reference_no');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('sale_reference_no');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('address');?>]", filter_type: "text", data: []},
            {
                column_number: 6, select_type: 'select2',
                select_type_options: {
                    placeholder: '<?= lang('delivery_status'); ?>',
                    width: '100%',
                    style: 'width:100%;',
                    minimumResultsForSearch: -1,
                    allowClear: true
                },
                data: [{value: 'packing', label: '<?=lang('packing');?>'}, {value: 'delivering', label: '<?=lang('delivering');?>'}, {value: 'delivered', label: '<?=lang('delivered');?>'}, {value: 'returned', label: '<?=lang('returned');?>'}]
            },
        ], "footer");
    });
</script>
<div class="row">
    <div class="col-sm-12">
        <ul id="myTab" class="nav nav-tabs">
            <li class=""><a href="#sales" class="tab-grey"><?= lang('sales') ?></a></li>
            <li class=""><a href="#deliveries" class="tab-grey"><?= lang('deliveries') ?></a></li>
        </ul>
        <div class="tab-content">
            <div id="sales" class="tab-pane fade in active">
                <div class="box">
                    <div class="box-header">
                        <h2 class="blue"><i class="fa-fw fa fa-file"></i><?= lang("sale_no") . ' ' . $inv->id; ?></h2>

                        <div class="box-icon">
                            <ul class="btn-tasks">
                                <li class="dropdown">
                                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>">
                                        </i>
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
                                            <a href="<?= site_url('sales_booking/edit_booking_sale/' . $inv->id) ?>" class="sledit">
                                                <i class="fa fa-edit"></i> <?= lang('edit_sale') ?>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?= site_url('sales/payments/' . $inv->id) ?>" data-target="#myModal"  data-backdrop="static" data-toggle="modal">
                                                <i class="fa fa-money"></i> <?= lang('view_payments') ?>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?= site_url('sales/add_payment/' . $inv->id) ?>" data-target="#myModal"  data-backdrop="static" data-toggle="modal">
                                                <i class="fa fa-dollar"></i> <?= lang('add_payment') ?>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?= site_url('sales/email/' . $inv->id) ?>" data-target="#myModal"  data-backdrop="static" data-toggle="modal">
                                                <i class="fa fa-envelope-o"></i> <?= lang('send_email') ?>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?= site_url('sales/pdf/' . $inv->id) ?>">
                                                <i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?>
                                            </a>
                                        </li>
                                        <?php if ( ! $inv->sale_id) { ?>
                                        <li>
                                            <a href="<?= site_url('sales/add_delivery/' . $inv->id) ?>" data-target="#myModal"  data-backdrop="static" data-toggle="modal">
                                                <i class="fa fa-truck"></i> <?= lang('add_delivery') ?>
                                            </a>
                                        </li>
                                        <li>
                                            <?php if ( $sale_type == 'booking') { ?>
                                                <a href="<?= site_url('sales/close_sale/' . $inv->id) ?>">
                                                    <i class="fa fa-close"></i> <?= lang('close_sale') ?>
                                                </a>
                                            <?php }else{?>
                                                <a href="<?= site_url('sales/return_sale/' . $inv->id) ?>">
                                                    <i class="fa fa-angle-double-left"></i> <?= lang('return_sale') ?>
                                                </a>
                                            <?php } ?>
                                        </li>
                                        <?php } ?>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="box-content">
                        <div class="row">
                            <div class="col-lg-12">
                                <?php if (!empty($inv->return_sale_ref) && $inv->return_id) {
                                    echo '<div class="alert alert-info no-print"><p>'.lang("sale_is_returned").': '.$inv->return_sale_ref;
                                    echo ' <a data-target="#myModal2" data-toggle="modal" href="'.site_url('sales/modal_view/'.$inv->return_id).'"><i class="fa fa-external-link no-print"></i></a><br>';
                                    echo '</p></div>';
                                } ?>
                                <div class="print-only col-xs-12">
                                    <img src="<?= base_url() . 'assets/uploads/logos/' . $biller->logo; ?>" alt="<?= $biller->company != '-' ? $biller->company : $biller->name; ?>">
                                </div>
                                <div class="well well-sm">
                                    <div class="col-xs-4 border-right">

                                        <div class="col-xs-2"><i class="fa fa-3x fa-building padding010 text-muted"></i></div>
                                        <div class="col-xs-10">
                                            <h5><?= lang("biller")?></h5>
                                            <h2 class="" style="margin-top: 0px;"><?= $biller->company != '-' ? $biller->company : $biller->name; ?></h2>
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
                                    <div class="col-xs-4 border-right">

                                        <div class="col-xs-2"><i class="fa fa-3x fa-user padding010 text-muted"></i></div>
                                        <div class="col-xs-10">
                                            <h5><?= lang("customer")?></h5>
                                            <h2 class="" style="margin-top: 0px;"><?= $customer->company ? $customer->company : $customer->name; ?></h2>
                                            <?= $customer->company ? "" : "Attn: " . $customer->name ?>

                                            <?php
                                            echo $customer->address . "<br>" . $customer->city . " " . $customer->postal_code . " " . $customer->state . "<br>" . $customer->country;

                                            echo "<p>";

                                            if ($customer->vat_no != "-" && $customer->vat_no != "") {
                                                echo "<br>" . lang("vat_no") . ": " . $customer->vat_no;
                                            }
                                            if ($customer->cf1 != "-" && $customer->cf1 != "") {
                                                echo "<br>" . lang("ccf1") . ": " . $customer->cf1;
                                            }
                                            if ($customer->cf2 != "-" && $customer->cf2 != "") {
                                                echo "<br>" . lang("ccf2") . ": " . $customer->cf2;
                                            }
                                            if ($customer->cf3 != "-" && $customer->cf3 != "") {
                                                echo "<br>" . lang("ccf3") . ": " . $customer->cf3;
                                            }
                                            if ($customer->cf4 != "-" && $customer->cf4 != "") {
                                                echo "<br>" . lang("ccf4") . ": " . $customer->cf4;
                                            }
                                            if ($customer->cf5 != "-" && $customer->cf5 != "") {
                                                echo "<br>" . lang("ccf5") . ": " . $customer->cf5;
                                            }
                                            if ($customer->cf6 != "-" && $customer->cf6 != "") {
                                                echo "<br>" . lang("ccf6") . ": " . $customer->cf6;
                                            }

                                            echo "</p>";
                                            echo lang("tel") . ": " . $customer->phone . "<br>" . lang("email") . ": " . $customer->email;
                                            ?>
                                        </div>
                                        <div class="clearfix"></div>

                                    </div>
                                    <div class="col-xs-4">
                                        <div class="col-xs-2"><i class="fa fa-3x fa-building-o padding010 text-muted"></i></div>
                                        <div class="col-xs-10">
                                            <h5><?= lang("warehouse")?></h5>
                                            <h2 class="" style="margin-top: 0px;"><?= $warehouse->name ?></h2>
                                            <?php
                                            echo $warehouse->address . "<br>";
                                            echo ($warehouse->phone ? lang("tel") . ": " . $warehouse->phone . "<br>" : '') . ($warehouse->email ? lang("email") . ": " . $warehouse->email : '');
                                            ?>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="clearfix"></div>
                                <?php if ($Settings->invoice_view == 1) { ?>
                                    <div class="col-xs-12 text-center">
                                        <h1><?= lang('tax_invoice'); ?></h1>
                                    </div>
                                <?php } ?>
                                <div class="clearfix"></div>
                                <div class="col-xs-7 pull-right">
                                    <div class="col-xs-12 text-right order_barcodes">
                                        <?= $this->sma->save_barcode($inv->reference_no, 'code128', 66, false); ?>
                                        <!-- <?= $this->sma->qrcode('link', urlencode(site_url('sales/view/' . $inv->id)), 2); ?> -->
                                    </div>
                                    <div class="clearfix"></div>
                                </div>

                                <div class="col-xs-5">
                                    <div class="col-xs-2"><i class="fa fa-3x fa-file-text-o padding010 text-muted"></i></div>
                                    <div class="col-xs-10">
                                        <h2 class=""><?= lang("ref"); ?>: <?= $inv->reference_no; ?></h2>
                                        <?php if (!empty($inv->return_sale_ref)) {
                                            echo '<p>'.lang("return_ref").': '.$inv->return_sale_ref;
                                            if ($inv->return_id) {
                                                echo ' <a data-target="#myModal2" data-toggle="modal" href="'.site_url('sales/modal_view/'.$inv->return_id).'"><i class="fa fa-external-link no-print"></i></a><br>';
                                            } else {
                                                echo '</p>';
                                            }
                                        } ?>

                                        <p style="font-weight:bold;"><?= lang("date"); ?>: <?= $this->sma->hrld($inv->date); ?></p>

                                        <p style="font-weight:bold;"><?= lang("sale_status"); ?>: <?= lang($inv->sale_status); ?></p>

                                        <p style="font-weight:bold;"><?= lang("payment_status"); ?>: <?= lang($inv->payment_status); ?></p>

                                        <?php if ($po->payment_method == 'kredit_pro') { ?>
                                            <p style="font-weight:bold;">Status Kredit Pro: 
                                                <?php
                                                    if ($po->status == 'confirmed' && $po->payment_status == 'waiting') {
                                                        echo lang('credit_reviewed');
                                                    } else if (($po->status == 'confirmed' && $po->payment_status == 'accept') || ($po->status == 'received' && $po->payment_status == 'accept')) {
                                                        echo lang('credit_received');
                                                    } else if ($po->status == 'confirmed' && $po->payment_status == 'reject') {
                                                        echo lang('credit_declined');
                                                    } else if ($po->status == 'received' && $po->payment_status == 'partial') {
                                                        echo lang('kredit_partial');
                                                    } else if ($po->status == 'received' && $po->payment_status == 'paid') {
                                                        echo lang('already_paid');
                                                    } else if ($po->status == 'confirmed' && $po->payment_status == 'pending') {
                                                        echo '-';
                                                    }
                                                ?>
                                            </p>
                                        <?php } ?>

                                        <p>&nbsp;</p>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>

                                

                            <div class="clearfix"></div>
                            <?php if($po){ ?>
                            <div class="well well-sm">
                                <div class="row bold">
                                    <div class="col-xs-12">
                                    <h2 class="bold" style="margin: 0; margin-bottom: 5px;">AksesToko Order Detail</h2>
                                    <p class="bold">
                                        <?php 
                                            echo lang("payment_method"); ?>: <?= lang($po->payment_method);
                                            if($po->payment_method == 'kredit'){
                                                echo '<br>'. 'TOP' .': ' . lang($po->payment_duration).' Days';
                                                echo '<br>'. lang('due_date') .': ' . ($po->payment_deadline ? date('Y-m-d', strtotime($po->payment_deadline)) : '-');
                                            }
                                        ?>
                                    </p>
                                    </div>
                                </div>
                            </div>
                            <?php } else if($inv->client_id == 'atl'){ ?>
                            <div class="well well-sm">
                                <div class="row bold">
                                    <div class="col-xs-12">
                                    <h2 class="bold" style="margin: 0; margin-bottom: 5px;">AksesToko Order Detail</h2>
                                    <table>
                                        <tr>
                                            <td><?= lang("reference_no") ?></td>
                                            <td> &emsp;:&emsp; </td>
                                            <td><?= $atl_order->ordercode?></td>
                                        </tr>
                                        <tr>
                                            <td>TOP</td>
                                            <td> &emsp;:&emsp; </td>
                                            <td><?= (int) $atl_order->tempo . ' ' . lang('day') ?></td>
                                        </tr>
                                        <tr>
                                            <td><?= lang('delivery_method') ?></td>
                                            <td> &emsp;:&emsp; </td>
                                            <td><?= lang($atl_order->delivery_method) ?></td>
                                        </tr>
                                        <tr>
                                            <td><?= lang('payment_method') ?></td>
                                            <td> &emsp;:&emsp; </td>
                                            <td><?= lang($atl_order->payment_method) ?></td>
                                        </tr>
                                        <?php
                                        if ($atl_order->payment_method == 'kredit_pro') { ?>
                                            <tr>
                                                <td>Status Kredit Pro</td>
                                                <td> &emsp;:&emsp; </td>
                                                <td>
                                                    <?php
                                                    switch ($atl_kreditpro_status->status) {
                                                        case 'waiting':
                                                            echo lang('credit_reviewed');
                                                            break;
                                                        case 'accept':
                                                            echo lang('credit_received');
                                                            break;
                                                        case 'reject':
                                                            echo lang('credit_declined');
                                                            break;
                                                        default:
                                                            echo '-';
                                                            break;
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </table>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                            <div class="clearfix"></div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-striped print-table order-table">

                                        <thead>

                                        <tr>
                                            <th><?= lang("no"); ?></th>
                                            <th><?= lang("description"); ?> (<?= lang("code"); ?>)</th>
                                            <?php if($sale_type != 'booking' || $inv->sale_status != 'closed') { ?>
                                                <th><?= lang("quantity"); ?></th>
                                            <?php }else{?>
                                                <th><?= lang("quantity_order"); ?></th>
                                                <th><?= lang("quantity_sent"); ?></th>
                                            <?php
                                            }
                                            if ($Settings->product_serial) {
                                                echo '<th style="text-align:center; vertical-align:middle;">' . lang("serial_no") . '</th>';
                                            }
                                            ?>
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
                                        $tax_summary = array();
                                        foreach ($rows as $row):
                                            if (isset($tax_summary[$row->tax_code])) {
                                                $tax_summary[$row->tax_code]['items'] += $row->quantity;
                                                $tax_summary[$row->tax_code]['tax'] += $row->item_tax;
                                                $tax_summary[$row->tax_code]['amt'] += ($row->quantity * $row->net_unit_price) - $row->item_discount;
                                            } else {
                                                $tax_summary[$row->tax_code]['items'] = $row->quantity;
                                                $tax_summary[$row->tax_code]['tax'] = $row->item_tax;
                                                $tax_summary[$row->tax_code]['amt'] = ($row->quantity * $row->net_unit_price) - $row->item_discount;
                                                $tax_summary[$row->tax_code]['name'] = $row->tax_name;
                                                $tax_summary[$row->tax_code]['code'] = $row->tax_code;
                                                $tax_summary[$row->tax_code]['rate'] = $row->tax_rate;
                                            }
                                            ?>
                                            <tr>
                                                <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                                <td style="vertical-align:middle;">
                                                    <?= $row->product_code.' - '.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                                    <?= $row->details ? '<br>' . $row->details : ''; ?> </td>
                                                <?php if($sale_type != 'booking' || $inv->sale_status != 'closed'){ ?>
                                                    <td style="width: 100px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code; ?></td>
                                                <?php }else{?>
                                                    <td style="width: 100px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code; ?></td>
                                                    <td style="width: 100px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->quantity).' '.$row->product_unit_code; ?></td>

                                                <?php
                                                }
                                                if ($Settings->product_serial) {
                                                    echo '<td>' . $row->serial_no . '</td>';
                                                }
                                                ?>
                                                <td style="text-align:right; width:120px; padding-right:10px;"><?= $this->sma->formatMoney($row->unit_price); ?></td>
                                                <?php
                                                if ($Settings->tax1 && $inv->product_tax > 0) {
                                                    echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 && $row->tax_code ? '<small>('.$row->tax_code.')</small>' : '') . ' ' . $this->sma->formatMoney($row->item_tax) . '</td>';
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

                                        if ($return_rows) {
                                            echo '<tr class="warning"><td colspan="100%" class="no-border"><strong>'.lang('returned_items').'</strong></td></tr>';
                                            foreach ($return_rows as $row):
                                                if (isset($tax_summary[$row->tax_code])) {
                                                    $tax_summary[$row->tax_code]['items'] += $row->quantity;
                                                    $tax_summary[$row->tax_code]['tax'] += $row->item_tax;
                                                    $tax_summary[$row->tax_code]['amt'] += ($row->quantity * $row->net_unit_price) - $row->item_discount;
                                                } else {
                                                    $tax_summary[$row->tax_code]['items'] = $row->quantity;
                                                    $tax_summary[$row->tax_code]['tax'] = $row->item_tax;
                                                    $tax_summary[$row->tax_code]['amt'] = ($row->quantity * $row->net_unit_price) - $row->item_discount;
                                                    $tax_summary[$row->tax_code]['name'] = $row->tax_name;
                                                    $tax_summary[$row->tax_code]['code'] = $row->tax_code;
                                                    $tax_summary[$row->tax_code]['rate'] = $row->tax_rate;
                                                }
                                                ?>
                                                <tr class="warning">
                                                    <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                                    <td style="vertical-align:middle;">
                                                        <?= $row->product_code.' - '.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                                        <?= $row->details ? '<br>' . $row->details : ''; ?> </td>
                                                    <td style="width: 100px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->quantity).' '.$row->product_unit_code; ?></td>
                                                    <?php
                                                    if ($Settings->product_serial) {
                                                        echo '<td>' . $row->serial_no . '</td>';
                                                    }
                                                    ?>
                                                    <td style="text-align:right; width:120px; padding-right:10px;"><?= $this->sma->formatMoney($row->unit_price); ?></td>
                                                    <?php
                                                    if ($Settings->tax1 && $inv->product_tax > 0) {
                                                        echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 && $row->tax_code ? '<small>('.$row->tax_code.')</small>' : '') . ' ' . $this->sma->formatMoney($row->item_tax) . '</td>';
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
                                        }

                                        ?>
                                        </tbody>
                                        <tfoot>
                                        <?php
                                        $col = 4;
                                        if ($Settings->product_serial) {
                                            $col++;
                                        }
                                        if ($Settings->product_discount && $inv->product_discount != 0) {
                                            $col++;
                                        }
                                        if ($Settings->tax1 && $inv->product_tax > 0) {
                                            $col++;
                                        }
                                        if ($sale_type == 'booking' && $inv->sale_status == 'closed') {
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
                                                    echo '<td style="text-align:right;">' . $this->sma->formatMoney($return_sale ? ($inv->product_tax+$return_sale->product_tax) : $inv->product_tax) . '</td>';
                                                }
                                                if ($Settings->product_discount && $inv->product_discount != 0) {
                                                    echo '<td style="text-align:right;">' . $this->sma->formatMoney($return_sale ? ($inv->product_discount+$return_sale->product_discount) : $inv->product_discount) . '</td>';
                                                }
                                                ?>
                                                <?php if($sale_type != 'booking' || $inv->sale_status != 'closed'){ ?>
                                                    <td style="text-align:right; padding-right:10px;"><?= $this->sma->formatMoney($return_sale ? (($inv->total + $inv->product_tax)+($return_sale->total + $return_sale->product_tax)) : ($inv->total + $inv->product_tax)); ?></td>
                                                <?php }else { ?>
                                                    <td style="text-align:right; padding-right:10px;"><?= $this->sma->formatMoney(($inv->total + $inv->product_tax)); ?></td>
                                                    
                                            </tr>
                                                <?php } 
                                        } ?>
                                        <?php
                                        if ($return_sale) {
                                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("return_total") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($return_sale->grand_total) . '</td></tr>';
                                        }
                                        if ($inv->surcharge != 0) {
                                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("return_surcharge") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($inv->surcharge) . '</td></tr>';
                                        }
                                        if ((int)$inv->charge != 0) {
                                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("charge") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($inv->charge) . '</td></tr>';
                                        }
                                        ?>
                                        <?php if ($inv->order_discount != 0) {
                                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("order_discount") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">'.($inv->order_discount_id ? '<small>('.$inv->order_discount_id.')</small> ' : '') . $this->sma->formatMoney($return_sale ? ($inv->order_discount+$return_sale->order_discount) : $inv->order_discount) . '</td></tr>';
                                        }
                                        ?>
                                        <?php if ($Settings->tax2 && $inv->order_tax != 0) {
                                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;">' . lang("order_tax") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($return_sale ? ($inv->order_tax+$return_sale->order_tax) : $inv->order_tax) . '</td></tr>';
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
                                            <?php if($sale_type != 'booking'|| $inv->sale_status != 'closed'){ ?>
                                                <td style="text-align:right; padding-right:10px; font-weight:bold;"><?= $this->sma->formatMoney($return_sale ? ($inv->grand_total+$return_sale->grand_total) : $inv->grand_total); ?></td>
                                            <?php }else {?> 
                                                <td style="text-align:right; padding-right:10px;"><?= $this->sma->formatMoney(($inv->grand_total)); ?></td>
                                            <?php } ?>
                                                    
                                        </tr>
                                        <tr>
                                            <td colspan="<?= $col; ?>"
                                                style="text-align:right; font-weight:bold;"><?= lang("paid"); ?>
                                                (<?= $default_currency->code; ?>)
                                            </td>
                                            <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid); ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="<?= $col; ?>"
                                                style="text-align:right; font-weight:bold;"><?= lang("balance"); ?>
                                                (<?= $default_currency->code; ?>)
                                            </td>
                                            <?php if($sale_type != 'booking' || $inv->sale_status != 'closed'){ ?>
                                            <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney(($return_sale ? ($inv->grand_total+$return_sale->grand_total) : $inv->grand_total) - ($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid)); ?></td>
                                            <?php }else {?> 
                                                <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney($inv->grand_total - $inv->paid); ?></td>
                                            <?php } ?>

                                        </tr>

                                        </tfoot>
                                    </table>
                                </div>

                                <div class="row">
                                    <div class="col-xs-6">
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

                                        <?php if ($customer->award_points != 0 && $Settings->each_spent > 0) { ?>
                                        <div class="col-xs-12 col-sm-10 col-md-8 col-lg-6">
                                            <div class="well well-sm">
                                                <?=
                                                '<p>'.lang('this_sale').': '.floor(($inv->grand_total/$Settings->each_spent)*$Settings->ca_point)
                                                .'<br>'.
                                                lang('total').' '.lang('award_points').': '. $customer->award_points . '</p>';?>
                                            </div>
                                        </div>
                                        <?php } ?>
                                    </div>

                                    <div class="col-xs-6">
                                        <?php
                                        if ($Settings->invoice_view == 1) {
                                            if (!empty($tax_summary)) {
                                                echo '<h3 class="bold">' . lang('tax_summary') . '</h3>';
                                                echo '<table class="table table-bordered table-condensed"><thead><tr><th>' . lang('name') . '</th><th>' . lang('code') . '</th><th>' . lang('qty') . '</th><th>' . lang('tax_excl') . '</th><th>' . lang('tax_amt') . '</th></tr></td><tbody>';
                                                foreach ($tax_summary as $summary) {
                                                    echo '<tr><td>' . $summary['name'] . '</td><td class="text-center">' . $summary['code'] . '</td><td class="text-center">' . $this->sma->formatQuantity($summary['items']) . '</td><td class="text-right">' . $this->sma->formatMoney($summary['amt']) . '</td><td class="text-right">' . $this->sma->formatMoney($summary['tax']) . '</td></tr>';
                                                }
                                                echo '</tbody></tfoot>';
                                                echo '<tr><th colspan="4" class="text-right">' . lang('total_tax_amount') . '</th><th class="text-right">' . $this->sma->formatMoney($return_sale ? $inv->product_tax+$return_sale->product_tax : $inv->product_tax) . '</th></tr>';
                                                echo '</tfoot></table>';
                                            }
                                        }
                                        ?>
                                        <div class="well well-sm">
                                            <p><?= lang("created_by"); ?>
                                                : <?= $created_by->first_name . ' ' . $created_by->last_name; ?> </p>

                                            <p><?= lang("date"); ?>: <?= $this->sma->hrld($inv->date); ?></p>
                                            <?php if ($inv->updated_by) { ?>
                                                <p><?= lang("updated_by"); ?>
                                                    : <?= $updated_by->first_name . ' ' . $updated_by->last_name;; ?></p>
                                                <p><?= lang("update_at"); ?>: <?= $this->sma->hrld($inv->updated_at); ?></p>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($inv->payment_status != 'paid') { ?>
                                    <div id="payment_buttons" class="row text-center padding10 no-print">

                                        <?php if ($paypal->active == "1" && $inv->grand_total != "0.00") {
                                            if (trim(strtolower($customer->country)) == $biller->country) {
                                                $paypal_fee = $paypal->fixed_charges + ($inv->grand_total * $paypal->extra_charges_my / 100);
                                            } else {
                                                $paypal_fee = $paypal->fixed_charges + ($inv->grand_total * $paypal->extra_charges_other / 100);
                                            }
                                            ?>
                                            <div class="col-xs-6 text-center">
                                                <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
                                                    <input type="hidden" name="cmd" value="_xclick">
                                                    <input type="hidden" name="business" value="<?= $paypal->account_email; ?>">
                                                    <input type="hidden" name="item_name" value="<?= $inv->reference_no; ?>">
                                                    <input type="hidden" name="item_number" value="<?= $inv->id; ?>">
                                                    <input type="hidden" name="image_url"
                                                        value="<?= base_url() . 'assets/uploads/logos/' . $Settings->logo; ?>">
                                                    <input type="hidden" name="amount"
                                                        value="<?= ($inv->grand_total - $inv->paid) + $paypal_fee; ?>">
                                                    <input type="hidden" name="no_shipping" value="1">
                                                    <input type="hidden" name="no_note" value="1">
                                                    <input type="hidden" name="currency_code" value="<?= $default_currency->code; ?>">
                                                    <input type="hidden" name="bn" value="FC-BuyNow">
                                                    <input type="hidden" name="rm" value="2">
                                                    <input type="hidden" name="return"
                                                        value="<?= site_url('sales/view/' . $inv->id); ?>">
                                                    <input type="hidden" name="cancel_return"
                                                        value="<?= site_url('sales/view/' . $inv->id); ?>">
                                                    <input type="hidden" name="notify_url"
                                                        value="<?= site_url('payments/paypalipn'); ?>"/>
                                                    <input type="hidden" name="custom"
                                                        value="<?= $inv->reference_no . '__' . ($inv->grand_total - $inv->paid) . '__' . $paypal_fee; ?>">
                                                    <button type="submit" name="submit" class="btn btn-primary btn-lg btn-block"><i
                                                            class="fa fa-money"></i> <?= lang('pay_by_paypal') ?></button>
                                                </form>
                                            </div>
                                        <?php } ?>


                                        <?php if ($skrill->active == "1" && $inv->grand_total != "0.00") {
                                            if (trim(strtolower($customer->country)) == $biller->country) {
                                                $skrill_fee = $skrill->fixed_charges + ($inv->grand_total * $skrill->extra_charges_my / 100);
                                            } else {
                                                $skrill_fee = $skrill->fixed_charges + ($inv->grand_total * $skrill->extra_charges_other / 100);
                                            }
                                            ?>
                                            <div class="col-xs-6 text-center">
                                                <form action="https://www.moneybookers.com/app/payment.pl" method="post">
                                                    <input type="hidden" name="pay_to_email" value="<?= $skrill->account_email; ?>">
                                                    <input type="hidden" name="status_url"
                                                        value="<?= site_url('payments/skrillipn'); ?>">
                                                    <input type="hidden" name="cancel_url"
                                                        value="<?= site_url('sales/view/' . $inv->id); ?>">
                                                    <input type="hidden" name="return_url"
                                                        value="<?= site_url('sales/view/' . $inv->id); ?>">
                                                    <input type="hidden" name="language" value="EN">
                                                    <input type="hidden" name="ondemand_note" value="<?= $inv->reference_no; ?>">
                                                    <input type="hidden" name="merchant_fields" value="item_name,item_number">
                                                    <input type="hidden" name="item_name" value="<?= $inv->reference_no; ?>">
                                                    <input type="hidden" name="item_number" value="<?= $inv->id; ?>">
                                                    <input type="hidden" name="amount"
                                                        value="<?= ($inv->grand_total - $inv->paid) + $skrill_fee; ?>">
                                                    <input type="hidden" name="currency" value="<?= $default_currency->code; ?>">
                                                    <input type="hidden" name="detail1_description" value="<?= $inv->reference_no; ?>">
                                                    <input type="hidden" name="detail1_text"
                                                        value="Payment for the sale invoice <?= $inv->reference_no . ': ' . $inv->grand_total . '(+ fee: ' . $skrill_fee . ') = ' . $this->sma->formatMoney($inv->grand_total + $skrill_fee); ?>">
                                                    <input type="hidden" name="logo_url"
                                                        value="<?= base_url() . 'assets/uploads/logos/' . $Settings->logo; ?>">
                                                    <button type="submit" name="submit" class="btn btn-primary btn-lg btn-block"><i
                                                            class="fa fa-money"></i> <?= lang('pay_by_skrill') ?></button>
                                                </form>
                                            </div>
                                        <?php } ?>
                                        <div class="clearfix"></div>
                                    </div>
                                <?php } ?>
                                <?php if ($payments) { ?>
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-striped table-condensed print-table">
                                                    <thead>
                                                    <tr>
                                                        <th><?= lang('date') ?></th>
                                                        <th><?= lang('payment_reference') ?></th>
                                                        <th><?= lang('paid_by') ?></th>
                                                        <th><?= lang('amount') ?></th>
                                                        <th><?= lang('created_by') ?></th>
                                                        <th><?= lang('type') ?></th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php foreach ($payments as $payment) { ?>
                                                        <tr <?= $payment->type == 'returned' ? 'class="warning"' : ''; ?>>
                                                            <td><?= $this->sma->hrld($payment->date) ?></td>
                                                            <td><?= $payment->reference_no; ?></td>
                                                            <td><?= lang($payment->paid_by);
                                                                if ($payment->paid_by == 'gift_card' || $payment->paid_by == 'CC') {
                                                                    echo ' (' . $payment->cc_no . ')';
                                                                } elseif ($payment->paid_by == 'Cheque') {
                                                                    echo ' (' . $payment->cheque_no . ')';
                                                                }
                                                                ?></td>
                                                            <td><?= $this->sma->formatMoney($payment->amount); ?></td>
                                                            <td><?= $payment->first_name . ' ' . $payment->last_name; ?></td>
                                                            <td><?= lang($payment->type); ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <?php if (!$Supplier || !$Customer) { ?>
                            <div class="buttons">
                                <div class="btn-group btn-group-justified">
                                    <?php if ($inv->attachment) { ?>
                                        <div class="btn-group">
                                            <a href="<?= site_url('welcome/download/' . $inv->attachment) ?>" class="tip btn btn-primary" title="<?= lang('attachment') ?>">
                                                <i class="fa fa-chain"></i>
                                                <span class="hidden-sm hidden-xs"><?= lang('attachment') ?></span>
                                            </a>
                                        </div>
                                    <?php } ?>
                                    <div class="btn-group">
                                        <a href="<?= site_url('sales/payments/' . $inv->id) ?>" data-toggle="modal" data-target="#myModal"  data-backdrop="static" class="tip btn btn-primary tip" title="<?= lang('view_payments') ?>">
                                            <i class="fa fa-money"></i> <span class="hidden-sm hidden-xs"><?= lang('view_payments') ?></span>
                                        </a>
                                    </div>
                                    <div class="btn-group">
                                        <a href="<?= site_url('sales/add_payment/' . $inv->id) ?>" data-toggle="modal" data-target="#myModal"  data-backdrop="static" class="tip btn btn-primary tip" title="<?= lang('add_payment') ?>">
                                            <i class="fa fa-money"></i> <span class="hidden-sm hidden-xs"><?= lang('add_payment') ?></span>
                                        </a>
                                    </div>
                                    <div class="btn-group">
                                        <a href="<?= site_url('sales/email/' . $inv->id) ?>" data-toggle="modal" data-target="#myModal"  data-backdrop="static" class="tip btn btn-primary tip" title="<?= lang('email') ?>">
                                            <i class="fa fa-envelope-o"></i> <span class="hidden-sm hidden-xs"><?= lang('email') ?></span>
                                        </a>
                                    </div>
                                    <div class="btn-group">
                                        <a href="<?= site_url('sales/pdf/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('download_pdf') ?>">
                                            <i class="fa fa-download"></i> <span class="hidden-sm hidden-xs"><?= lang('pdf') ?></span>
                                        </a>
                                    </div>
                                    <?php if ( ! $inv->sale_id) { ?>
                                    <div class="btn-group">
                                        <a href="<?= site_url('sales/add_delivery/' . $inv->id) ?>" data-toggle="modal" data-target="#myModal"  data-backdrop="static" class="tip btn btn-primary tip" title="<?= lang('add_delivery') ?>">
                                            <i class="fa fa-truck"></i> <span class="hidden-sm hidden-xs"><?= lang('add_delivery') ?></span>
                                        </a>
                                    </div>
                                    <div class="btn-group">
                                        <a href="<?= site_url('sales_booking/edit_booking_sale/' . $inv->id) ?>" class="tip btn btn-warning tip sledit" title="<?= lang('edit') ?>">
                                            <i class="fa fa-edit"></i> <span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
                                        </a>
                                    </div>
                                    <!-- <div class="btn-group">
                                        <a href="#" class="tip btn btn-danger bpo"
                                            title="<b><?= $this->lang->line("delete_sale") ?></b>"
                                            data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= site_url('sales/delete/' . $inv->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
                                            data-html="true" data-placement="top"><i class="fa fa-trash-o"></i> 
                                            <span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
                                        </a>
                                    </div> -->
                                    <?php } ?>
                                    <!--<div class="btn-group"><a href="<?= site_url('sales/excel/' . $inv->id) ?>" class="tip btn btn-primary"  title="<?= lang('download_excel') ?>"><i class="fa fa-download"></i> <?= lang('excel') ?></a></div>-->
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div id="deliveries" class="tab-pane fade in active">
                <?php if ($Owner || $Admin) { ?><?= form_open('sales/delivery_actions', 'id="action-form"') ?><?php } ?>
                <div class="box">
                    <div class="box-header">
                        <h2 class="blue"><i class="fa-fw fa fa-truck"></i><?= lang('deliveries_booking'); ?> <?=$inv->reference_no?></h2>

                        <div class="box-icon">
                            <ul class="btn-tasks">
                                <li class="dropdown">
                                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                                    </a>
                                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                                        <li><a href="#" id="excel" data-action="export_excel"><i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?></a></li>
                                        <li>
                                            <a href="<?= site_url('sales/add_delivery/' . $inv->id) ?>" data-target="#myModal"  data-backdrop="static" data-toggle="modal">
                                                <i class="fa fa-truck"></i> <?= lang('add_delivery') ?>
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
                                <p class="introtext"><?= lang('list_results'); ?></p>

                                <table id="DOData" class="table table-bordered table-hover table-striped table-condensed">
                                    <thead>
                                    <tr>
                                        <th style="min-width:30px; width: 30px; text-align: center;">
                                            <input class="checkbox checkft" type="checkbox" name="check"/>
                                        </th>
                                        <th><?= lang("date"); ?></th>
                                        <th><?= lang("do_reference_no"); ?></th>
                                        <th><?= lang("sale_reference_no"); ?></th>
                                        <th><?= lang("customer"); ?></th>
                                        <th><?= lang("address"); ?></th>
                                        <th><?= lang("status"); ?></th>
                                        <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                                        <th></th><th></th><th></th>
                                        <th style="width:100px; text-align:center;"><?= lang("actions"); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td colspan="9" class="dataTables_empty"><?= lang("loading_data"); ?></td>
                                    </tr>
                                    </tbody>
                                    <tfoot class="dtFilter">
                                    <tr class="active">
                                        <th style="min-width:30px; width: 30px; text-align: center;">
                                            <input class="checkbox checkft" type="checkbox" name="check"/>
                                        </th>
                                        <th></th><th></th><th></th><th></th><th></th><th></th>
                                        <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                                        <th></th><th></th><th></th>
                                        <th style="width:100px; text-align:center;"><?= lang("actions"); ?></th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if ($Owner || $Admin) { ?>
                    <div style="display: none;">
                        <input type="hidden" name="form_action" value="" id="form_action"/>
                        <?= form_submit('perform_action', 'perform_action', 'id="action-form-submit"') ?>
                    </div>
                    <?= form_close() ?>
                    <script type="text/javascript" charset="utf-8">
                        $(document).ready(function() {
                            $(document).on('click', '#delete', function(e) {
                                e.preventDefault();
                                $('#form_action').val($(this).attr('data-action'));
                                //$('#action-form').submit();
                                $('#action-form-submit').click();
                            });
                        });
                    </script>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
