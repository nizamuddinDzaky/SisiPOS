<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
function row_status($x)
{
    if ($x == null) {
        return '';
    } elseif ($x == 'pending') {
        return '<div class="text-center"><span class="label label-warning">' . lang($x) . '</span></div>';
    } elseif ($x == 'completed' || $x == 'paid' || $x == 'sent' || $x == 'received' || $x == 'closed') {
        return '<div class="text-center"><span class="label label-success">' . lang($x) . '</span></div>';
    } elseif ($x == 'partial' || $x == 'transferring' || $x == 'reserved') {
        return '<div class="text-center"><span class="label label-primary">' . lang($x) . '</span></div>';
    } elseif ($x == 'due' || $x == 'canceled') {
        return '<div class="text-center"><span class="label label-danger">' . lang($x) . '</span></div>';
    } else {
        return '<div class="text-center"><span class="label label-default">' . lang($x) . '</span></div>';
    }
}

?>
<?php if (($Owner || $Admin || $Principal)) { ?>

    <script type="text/javascript" src="<?= $assets ?>js/fusionchart/fusioncharts.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/fusionchart/fusioncharts.maps.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/fusionchart/fusioncharts.indonesia.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/fusionchart/fusioncharts.theme.fusion.js"></script>
    <style>
        .carousel .item {
            min-height: 100px;
        }

        .carousel-inner>.item>.link_promo>img {
            margin: auto;
            border-radius: 8px;
            max-height: 200px;
            min-height: 200px;
        }

        .carousel-indicators {
            bottom: -50px;
        }

        .modal.left .modal-dialog,
        .modal.right .modal-dialog {
            position: fixed;
            margin: auto;
            width: 30%;
            height: 100%;
            -webkit-transform: translate3d(0%, 0, 0);
            -ms-transform: translate3d(0%, 0, 0);
            -o-transform: translate3d(0%, 0, 0);
            transform: translate3d(0%, 0, 0);
        }

        .modal.left .modal-content,
        .modal.right .modal-content {

            overflow-y: auto;
        }

        .modal.left .modal-body,
        .modal.right .modal-body {
            padding: 15px 15px 80px;
        }

        /*Right*/
        .modal.right.fade .modal-dialog {
            right: -320px;
            -webkit-transition: opacity 0.3s linear, right 0.3s ease-out;
            -moz-transition: opacity 0.3s linear, right 0.3s ease-out;
            -o-transition: opacity 0.3s linear, right 0.3s ease-out;
            transition: opacity 0.3s linear, right 0.3s ease-out;
        }

        .modal.right.fade.in .modal-dialog {
            right: 0;
        }

        /* ----- MODAL STYLE ----- */
        .transparent {
            border-radius: 0;
            border: none;
            background-color: transparent;
            border-radius: 6px;
            -webkit-border-radius: 6px;
            -moz-border-radius: 6px;
            box-shadow: none;
        }
    </style>
    <!-- Modal -->
    <!--	<div class="modal right fade" id="promoModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2">
		<div class="modal-dialog" role="document">
			<div class="modal-content transparent">

                            <div class="modal-body">
                                <div id="PromoSlide" class="carousel slide" data-ride="carousel">
                                 Indicators 
                                <ol class="carousel-indicators">
                                  <li data-target="#PromoSlide" data-slide-to="0" class="active"></li>
                                   <?php
                                    for ($i = 1; $i < count($promo); $i++) {
                                        if ($promo[$i]->url_image) {
                                            echo '<li data-target="#PromoSlide" data-slide-to="' . $i . '"></li>';
                                        }
                                    } ?>
                                </ol>
                                 Wrapper for slides 
                                <div class="carousel-inner">
                                  <div class="item active">
                                     <a href="<?= site_url('Promo/detail/' . $promo[0]->link_promo); ?>" class="link_promo">
                                        <img src="<?= base_url() ?>assets/uploads/<?= $promo[0]->url_image ? $promo[0]->url_image : 'no_image.jpg' ?>" >
                                     </a>
                                  </div>
                                  <?php
                                    for ($i = 1; $i < count($promo); $i++) {
                                        if ($promo[$i]->url_image) {
                                            echo '<div class="item">';
                                            echo '<a href="' . site_url('Promo/detail/' . $promo[$i]->link_promo) . '" class="link_promo">';
                                            echo '<img src="' . base_url() . '/assets/uploads/' . $promo[$i]->url_image . '" alt="' . $promo[$i]->name . '" >';
                                            echo '</a>';
                                            echo '</div>';
                                        }
                                    }
                                    ?>
                               
                                </div>
                              </div>
                                <div class="pull-left">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                </div>
                            </div>
			</div> modal-content        
		</div> modal-dialog 
	</div> modal -->
    <div class="box" style="margin-bottom: 15px;">
        <div class="box-header">
            <h2 class="blue"><i class="fa-fw fa fa-bar-chart-o"></i><?= lang('overview_chart'); ?></h2>
            <?php if ($Principal) { ?>
                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown">
                            <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                                <i id="eye" class="icon fa fa-eye-slash tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                            </a>
                            <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                                <li>
                                    <a style="cursor: pointer;" id="chart_1">
                                        <i class="fa fa-eye-slash"></i> <?= lang('show_grafik_chart') ?>
                                    </a>
                                </li>
                                <li>
                                    <a style="cursor: pointer;" id="chart_2">
                                        <i class="fa fa-eye-slash"></i> <?= lang('show_map_chart') ?>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            <?php } ?>
        </div>
        <div class="box-content" style="margin-bottom: 20px">
            <div class="row">
                <div class="col-md-12">
                    <?php if ($Principal) { ?>
                        <p class="introtext" id="info_text"></p>
                    <?php } else { ?>
                        <p class="introtext"><?php echo lang('overview_chart_heading'); ?></p>
                    <?php } ?>
                    <div class="row" id="chart_principal_1">
                        <?php if ($Principal) { ?>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <select name="product" class="form-control" id="product">
                                        <option value=""><?= lang('pilih_product') ?></option>
                                        <?php foreach ($product as $row) {
                                            echo '<option value="' . $row->code . '">' . $row->name . '(' . $row->code . ')</option>';
                                        } ?>
                                    </select>

                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <select name="brand" class="form-control" id="brands">
                                        <option value=""><?= lang('all_brand') ?></option>
                                        <?php foreach ($brand as $row) {
                                            echo '<option value="' . $row->id . '">' . $row->name . '</option>';
                                        } ?>
                                    </select>

                                </div>
                            </div>
                            <div class="col-md-1">
                                <button class="btn btn-success" id="distri"><?= lang('change_filter') ?></button>
                            </div>
                        <?php } ?>
                        <div id="ov-chart" style="width:100%; height:450px;"></div>
                        <p class="text-center"><?= lang("chart_lable_toggle"); ?></p>
                    </div>
                </div>
                <?php if ($Principal) { ?>
                    <div class="col-md-12" style="margin-top: 20px; margin-bottom: 20px" id="chart_principal_2">
                        <div class="row" style="margin-bottom: 20px">
                            <div class="col-md-4">
                                <select name="change-opt" class="form-control" id="change">
                                    <option value="toko"><?= lang('persebaran') ?></option>
                                    <option value="quantum"><?= lang('quantum_penjulan') ?></option>
                                </select>
                            </div>
                            <div id="prod">
                                <div class="col-md-4">
                                    <select class="form-control" id="product2" name="product2">
                                        <?php foreach ($product as $row) {
                                            echo '<option value="' . $row->code . '">' . $row->name . '(' . $row->code . ')</option>';
                                        } ?>
                                    </select>
                                </div>

                                <div class="col-md-1">
                                    <button id="prod_map" class="btn btn-success">Submit</button>
                                </div>
                            </div>

                        </div>
                        <div id="chart-container"></div>
                    </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
    <?php if ($Owner || $Admin) { ?>
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-lg-12">
                <div class="box">
                    <div class="box-header">
                        <h2 class="blue"><i class="fa fa-th"></i><span class="break"></span><?= lang('quick_links') ?></h2>
                    </div>
                    <div class="box-content">
                        <!-- start masih develop -->
                        <div class="col-lg-1 col-md-2 col-xs-6">
                            <a class="bblue white quick-button small" href="<?= site_url('target') ?>">
                                <i class="fa fa-barcode"></i>
                                <p>Target</p>
                            </a>
                        </div>
                        <!-- end masih develop -->
                        <div class="col-lg-1 col-md-2 col-xs-6">
                            <a class="bblue white quick-button small" href="<?= site_url('products') ?>">
                                <i class="fa fa-barcode"></i>

                                <p><?= lang('products') ?></p>
                            </a>
                        </div>
                        <div class="col-lg-1 col-md-2 col-xs-6">
                            <a class="bdarkGreen white quick-button small" href="<?= site_url('sales') ?>">
                                <i class="fa fa-heart"></i>

                                <p><?= lang('sales') ?></p>
                            </a>
                        </div>

                        <div class="col-lg-1 col-md-2 col-xs-6">
                            <a class="blightOrange white quick-button small" href="<?= site_url('quotes') ?>">
                                <i class="fa fa-heart-o"></i>

                                <p><?= lang('quotes') ?></p>
                            </a>
                        </div>

                        <div class="col-lg-1 col-md-2 col-xs-6">
                            <a class="bred white quick-button small" href="<?= site_url('purchases') ?>">
                                <i class="fa fa-star"></i>

                                <p><?= lang('purchases') ?></p>
                            </a>
                        </div>

                        <div class="col-lg-1 col-md-2 col-xs-6">
                            <a class="bpink white quick-button small" href="<?= site_url('transfers') ?>">
                                <i class="fa fa-star-o"></i>

                                <p><?= lang('transfers') ?></p>
                            </a>
                        </div>

                        <div class="col-lg-1 col-md-2 col-xs-6">
                            <a class="bgrey white quick-button small" href="<?= site_url('customers') ?>">
                                <i class="fa fa-users"></i>

                                <p><?= lang('customers') ?></p>
                            </a>
                        </div>

                        <div class="col-lg-1 col-md-2 col-xs-6">
                            <a class="bgrey white quick-button small" href="<?= site_url('suppliers') ?>">
                                <i class="fa fa-users"></i>

                                <p><?= lang('suppliers') ?></p>
                            </a>
                        </div>

                        <div class="col-lg-1 col-md-2 col-xs-6">
                            <a class="blightBlue white quick-button small" href="<?= site_url('notifications') ?>">
                                <i class="fa fa-comments"></i>

                                <p><?= lang('notifications') ?></p>
                                <!--<span class="notification green">4</span>-->
                            </a>
                        </div>
                        <div class="col-lg-1 col-md-2 col-xs-6">
                            <a class="blightBlue white quick-button small" href="faq-t/index.php" target="_blank">
                                <i class="fa fa-comments"></i>
                                <p><?= lang('FAQ') ?></p>
                            </a>
                            <!--<span class="notification green">4</span>-->

                        </div>

                        <?php if ($Owner) { ?>
                            <div class="col-lg-1 col-md-2 col-xs-6">
                                <a class="bblue white quick-button small" href="<?= site_url('auth/users') ?>">
                                    <i class="fa fa-group"></i>
                                    <p><?= lang('users') ?></p>
                                </a>
                            </div>
                            <div class="col-lg-1 col-md-2 col-xs-6">
                                <a class="bblue white quick-button small" href="<?= site_url('system_settings') ?>">
                                    <i class="fa fa-cogs"></i>

                                    <p><?= lang('settings') ?></p>
                                </a>
                            </div>
                        <?php } ?>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php } else if (!($Owner || $Admin) && !$Principal) { ?>
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-lg-12">
                <div class="box">

                    <div class="box-header">
                        <h2 class="blue"><i class="fa fa-th"></i><span class="break"></span><?= lang('quick_links') ?></h2>
                    </div>
                    <div class="box-content">
                        <?php if ($GP['products-index']) { ?>
                            <div class="col-lg-1 col-md-2 col-xs-6">
                                <a class="bblue white quick-button small" href="<?= site_url('products') ?>">
                                    <i class="fa fa-barcode"></i>
                                    <p><?= lang('products') ?></p>
                                </a>
                            </div>
                        <?php }
                        if ($GP['sales-index']) { ?>
                            <div class="col-lg-1 col-md-2 col-xs-6">
                                <a class="bdarkGreen white quick-button small" href="<?= site_url('sales') ?>">
                                    <i class="fa fa-heart"></i>
                                    <p><?= lang('sales') ?></p>
                                </a>
                            </div>
                        <?php }
                        if ($GP['quotes-index']) { ?>
                            <div class="col-lg-1 col-md-2 col-xs-6">
                                <a class="blightOrange white quick-button small" href="<?= site_url('quotes') ?>">
                                    <i class="fa fa-heart-o"></i>
                                    <p><?= lang('quotes') ?></p>
                                </a>
                            </div>
                        <?php }
                        if ($GP['purchases-index']) { ?>
                            <div class="col-lg-1 col-md-2 col-xs-6">
                                <a class="bred white quick-button small" href="<?= site_url('purchases') ?>">
                                    <i class="fa fa-star"></i>
                                    <p><?= lang('purchases') ?></p>
                                </a>
                            </div>
                        <?php }
                        if ($GP['transfers-index']) { ?>
                            <div class="col-lg-1 col-md-2 col-xs-6">
                                <a class="bpink white quick-button small" href="<?= site_url('transfers') ?>">
                                    <i class="fa fa-star-o"></i>
                                    <p><?= lang('transfers') ?></p>
                                </a>
                            </div>
                        <?php }
                        if ($GP['customers-index']) { ?>
                            <div class="col-lg-1 col-md-2 col-xs-6">
                                <a class="bgrey white quick-button small" href="<?= site_url('customers') ?>">
                                    <i class="fa fa-users"></i>
                                    <p><?= lang('customers') ?></p>
                                </a>
                            </div>
                        <?php }
                        if ($GP['sales-index']) { ?>
                            <div class="col-lg-1 col-md-2 col-xs-6">
                                <a class="blightBlue white quick-button small" href="faq-t/index.php" target="_blank">
                                    <i class="fa fa-comments"></i>
                                    <p><?= lang('FAQ') ?></p>
                                </a>
                                <!--<span class="notification green">4</span>-->

                            </div>
                        <?php }
                        if ($GP['suppliers-index']) { ?>
                            <div class="col-lg-1 col-md-2 col-xs-6">
                                <a class="bgrey white quick-button small" href="<?= site_url('suppliers') ?>">
                                    <i class="fa fa-users"></i>

                                    <p><?= lang('suppliers') ?></p>
                                </a>
                            </div>
                        <?php } ?>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
    <?php if (!$Principal) { ?>
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-header">
                        <h2 class="blue"><i class="fa-fw fa fa-tasks"></i> <?= lang('latest_five') ?></h2>
                    </div>
                    <div class="box-content">
                        <div class="row">
                            <div class="col-md-12">
                                <ul id="dbTab" class="nav nav-tabs">
                                    <?php if ($Owner || $Admin || $GP['sales-index']) { ?>
                                        <li class=""><a href="#sales"><?= lang('sales') ?></a></li>
                                    <?php }
                                    if ($Owner || $Admin || $GP['quotes-index']) { ?>
                                        <li class=""><a href="#quotes"><?= lang('quotes') ?></a></li>
                                    <?php }
                                    if ($Owner || $Admin || $GP['purchases-index']) { ?>
                                        <li class=""><a href="#purchases"><?= lang('purchases') ?></a></li>
                                    <?php }
                                    if ($Owner || $Admin || $GP['transfers-index']) { ?>
                                        <li class=""><a href="#transfers"><?= lang('transfers') ?></a></li>
                                    <?php }
                                    if ($Owner || $Admin || $GP['customers-index']) { ?>
                                        <li class=""><a href="#customers"><?= lang('customers') ?></a></li>
                                    <?php }
                                    if ($Owner || $Admin || $GP['suppliers-index']) { ?>
                                        <li class=""><a href="#suppliers"><?= lang('suppliers') ?></a></li>
                                    <?php } ?>
                                </ul>

                                <div class="tab-content">
                                    <?php if ($Owner || $Admin || $GP['sales-index']) { ?>

                                        <div id="sales" class="tab-pane fade in">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="table-responsive">
                                                        <table id="sales-tbl" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-striped" style="margin-bottom: 0;">
                                                            <thead>
                                                                <tr>
                                                                    <th style="width:30px !important;">#</th>
                                                                    <th><?= $this->lang->line("date"); ?></th>
                                                                    <th><?= $this->lang->line("reference_no"); ?></th>
                                                                    <th><?= $this->lang->line("customer"); ?></th>
                                                                    <th><?= $this->lang->line("status"); ?></th>
                                                                    <th><?= $this->lang->line("total"); ?></th>
                                                                    <th><?= $this->lang->line("payment_status"); ?></th>
                                                                    <th><?= $this->lang->line("paid"); ?></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php if (!empty($sales)) {
                                                                    $r = 1;
                                                                    foreach ($sales as $order) {
                                                                        echo    '<tr id="' . $order->id . '" class="' . ($order->pos ? "receipt_link" : "invoice_link") . '"><td>' . $r . '</td>
                                                                                    <td>' . $this->sma->hrld($order->date) . '</td>
                                                                                    <td>' . $order->reference_no . '</td>
                                                                                    <td>' . $order->customer . '</td>
                                                                                    <td>' . row_status($order->sale_status) . '</td>
                                                                                    <td class="text-right">' . $this->sma->formatMoney($order->grand_total) . '</td>
                                                                                    <td>' . row_status($order->payment_status) . '</td>
                                                                                    <td class="text-right">' . $this->sma->formatMoney($order->paid) . '</td>
                                                                                </tr>';
                                                                        $r++;
                                                                    }
                                                                } else { ?>
                                                                    <tr>
                                                                        <td colspan="7" class="dataTables_empty"><?= lang('no_data_available') ?></td>
                                                                    </tr>
                                                                <?php } ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    <?php }
                                    if ($Owner || $Admin || $GP['quotes-index']) { ?>

                                        <div id="quotes" class="tab-pane fade">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="table-responsive">
                                                        <table id="quotes-tbl" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-striped" style="margin-bottom: 0;">
                                                            <thead>
                                                                <tr>
                                                                    <th style="width:30px !important;">#</th>
                                                                    <th><?= $this->lang->line("date"); ?></th>
                                                                    <th><?= $this->lang->line("reference_no"); ?></th>
                                                                    <th><?= $this->lang->line("customer"); ?></th>
                                                                    <th><?= $this->lang->line("status"); ?></th>
                                                                    <th><?= $this->lang->line("amount"); ?></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php if (!empty($quotes)) {
                                                                    $r = 1;
                                                                    foreach ($quotes as $quote) {
                                                                        echo '<tr id="' . $quote->id . '" class="quote_link"><td>' . $r . '</td>
                                                                                    <td>' . $this->sma->hrld($quote->date) . '</td>
                                                                                    <td>' . $quote->reference_no . '</td>
                                                                                    <td>' . $quote->customer . '</td>
                                                                                    <td>' . row_status($quote->status) . '</td>
                                                                                    <td class="text-right">' . $this->sma->formatMoney($quote->grand_total) . '</td>
                                                                                </tr>';
                                                                        $r++;
                                                                    }
                                                                } else { ?>
                                                                    <tr>
                                                                        <td colspan="6" class="dataTables_empty"><?= lang('no_data_available') ?></td>
                                                                    </tr>
                                                                <?php } ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    <?php }
                                    if ($Owner || $Admin || $GP['purchases-index']) { ?>

                                        <div id="purchases" class="tab-pane fade in">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="table-responsive">
                                                        <table id="purchases-tbl" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-striped" style="margin-bottom: 0;">
                                                            <thead>
                                                                <tr>
                                                                    <th style="width:30px !important;">#</th>
                                                                    <th><?= $this->lang->line("date"); ?></th>
                                                                    <th><?= $this->lang->line("reference_no"); ?></th>
                                                                    <th><?= $this->lang->line("supplier"); ?></th>
                                                                    <th><?= $this->lang->line("status"); ?></th>
                                                                    <th><?= $this->lang->line("amount"); ?></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php if (!empty($purchases)) {
                                                                    $r = 1;
                                                                    foreach ($purchases as $purchase) {
                                                                        echo    '<tr id="' . $purchase->id . '" class="purchase_link"><td>' . $r . '</td>
                                                                                    <td>' . $this->sma->hrld($purchase->date) . '</td>
                                                                                    <td>' . $purchase->reference_no . '</td>
                                                                                    <td>' . $purchase->supplier . '</td>
                                                                                    <td>' . row_status($purchase->status) . '</td>
                                                                                    <td class="text-right">' . $this->sma->formatMoney($purchase->grand_total) . '</td>
                                                                                </tr>';
                                                                        $r++;
                                                                    }
                                                                } else { ?>
                                                                    <tr>
                                                                        <td colspan="6" class="dataTables_empty"><?= lang('no_data_available') ?></td>
                                                                    </tr>
                                                                <?php } ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    <?php }
                                    if ($Owner || $Admin || $GP['transfers-index']) { ?>

                                        <div id="transfers" class="tab-pane fade">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="table-responsive">
                                                        <table id="transfers-tbl" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-striped" style="margin-bottom: 0;">
                                                            <thead>
                                                                <tr>
                                                                    <th style="width:30px !important;">#</th>
                                                                    <th><?= $this->lang->line("date"); ?></th>
                                                                    <th><?= $this->lang->line("reference_no"); ?></th>
                                                                    <th><?= $this->lang->line("from"); ?></th>
                                                                    <th><?= $this->lang->line("to"); ?></th>
                                                                    <th><?= $this->lang->line("status"); ?></th>
                                                                    <th><?= $this->lang->line("amount"); ?></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php if (!empty($transfers)) {
                                                                    $r = 1;
                                                                    foreach ($transfers as $transfer) {
                                                                        echo    '<tr id="' . $transfer->id . '" class="transfer_link"><td>' . $r . '</td>
                                                                                    <td>' . $this->sma->hrld($transfer->date) . '</td>
                                                                                    <td>' . $transfer->transfer_no . '</td>
                                                                                    <td>' . $transfer->from_warehouse_name . '</td>
                                                                                    <td>' . $transfer->to_warehouse_name . '</td>
                                                                                    <td>' . row_status($transfer->status) . '</td>
                                                                                    <td class="text-right">' . $this->sma->formatMoney($transfer->grand_total) . '</td>
                                                                                </tr>';
                                                                        $r++;
                                                                    }
                                                                } else { ?>
                                                                    <tr>
                                                                        <td colspan="7" class="dataTables_empty"><?= lang('no_data_available') ?></td>
                                                                    </tr>
                                                                <?php } ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    <?php }
                                    if ($Owner || $Admin || $GP['customers-index']) { ?>

                                        <div id="customers" class="tab-pane fade in">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="table-responsive">
                                                        <table id="customers-tbl" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-striped" style="margin-bottom: 0;">
                                                            <thead>
                                                                <tr>
                                                                    <th style="width:30px !important;">#</th>
                                                                    <th><?= $this->lang->line("company"); ?></th>
                                                                    <th><?= $this->lang->line("name"); ?></th>
                                                                    <th><?= $this->lang->line("email"); ?></th>
                                                                    <th><?= $this->lang->line("phone"); ?></th>
                                                                    <th><?= $this->lang->line("address"); ?></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php if (!empty($customers)) {
                                                                    $r = 1;
                                                                    foreach ($customers as $customer) {
                                                                        echo    '<tr id="' . $customer->id . '" class="customer_link pointer"><td>' . $r . '</td>
                                                                                    <td>' . $customer->company . '</td>
                                                                                    <td>' . $customer->name . '</td>
                                                                                    <td>' . $customer->email . '</td>
                                                                                    <td>' . $customer->phone . '</td>
                                                                                    <td>' . $customer->address . '</td>
                                                                                </tr>';
                                                                        $r++;
                                                                    }
                                                                } else { ?>
                                                                    <tr>
                                                                        <td colspan="6" class="dataTables_empty"><?= lang('no_data_available') ?></td>
                                                                    </tr>
                                                                <?php } ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    <?php }
                                    if ($Owner || $Admin || $GP['suppliers-index']) { ?>

                                        <div id="suppliers" class="tab-pane fade">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="table-responsive">
                                                        <table id="suppliers-tbl" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-striped" style="margin-bottom: 0;">
                                                            <thead>
                                                                <tr>
                                                                    <th style="width:30px !important;">#</th>
                                                                    <th><?= $this->lang->line("company"); ?></th>
                                                                    <th><?= $this->lang->line("name"); ?></th>
                                                                    <th><?= $this->lang->line("email"); ?></th>
                                                                    <th><?= $this->lang->line("phone"); ?></th>
                                                                    <th><?= $this->lang->line("address"); ?></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php if (!empty($suppliers)) {
                                                                    $r = 1;
                                                                    foreach ($suppliers as $supplier) {
                                                                        echo    '<tr id="' . $supplier->id . '" class="supplier_link pointer"><td>' . $r . '</td>
                                                                                    <td>' . $supplier->company . '</td>
                                                                                    <td>' . $supplier->name . '</td>
                                                                                    <td>' . $supplier->email . '</td>
                                                                                    <td>' . $supplier->phone . '</td>
                                                                                    <td>' . $supplier->address . '</td>
                                                                                </tr>';
                                                                        $r++;
                                                                    }
                                                                } else { ?>
                                                                    <tr>
                                                                        <td colspan="6" class="dataTables_empty"><?= lang('no_data_available') ?></td>
                                                                    </tr>
                                                                <?php } ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    <?php } ?>

                                </div>


                            </div>

                        </div>

                    </div>
                </div>
            </div>

        </div>
    <?php } ?>

    <script type="text/javascript">
        $(document).ready(function() {

            var nowDate = new Date();
            var today = new Date(nowDate.getFullYear(), nowDate.getMonth(), nowDate.getDate(), 0, 0, 0, 0);
            $('.order').click(function() {
                window.location.href = '<?= site_url() ?>orders/view/' + $(this).attr('id') + '#comments';
            });
            $('.invoice').click(function() {
                window.location.href = '<?= site_url() ?>orders/view/' + $(this).attr('id');
            });
            $('.quote').click(function() {
                window.location.href = '<?= site_url() ?>quotes/view/' + $(this).attr('id');
            });

            <?php if ($Principal) { ?>
                document.getElementById('info_text').innerHTML = '<?php echo lang('info_chart_principal'); ?>';
                $('#prod').hide();
                $("#dateranges2").daterangepicker({
                    maxDate: today,
                    opens: 'left',
                    autoUpdateInput: true,
                    locale: {
                        format: 'DD/MM/YYYY',
                    }
                });
                $('#prod_map').click(function() {
                    var product = document.getElementById('product2').value;
                    $.ajax({
                        'dataType': 'json',
                        'url': '<?= site_url('welcome/getDashboardByMap/') ?>' + '?prod=' + product,
                        'success': function(data) {
                            let datas = data;

                            var ids = []; //creating array for storing browser type in array.
                            var values = [];
                            var showLabel = [];
                            var max = [];
                            var lenght = [];

                            for (var i = 0; i < datas.length; i++) {
                                ids.push(datas[i]['id'].replace(" ", ""));
                                values.push(parseInt(datas[i]['value']) || 0);

                            }
                            showLabel.push('TON');
                            console.log(ids);
                            lenght.push(parseInt(datas.length));
                            if (parseInt(datas.length) != 0) {
                                max.push(parseInt(datas[0]['value']));
                            } else {
                                max.push(1);
                            }
                            // console.log(max);
                            mapChart(datas, ids, values, showLabel, max);
                        }
                    });
                });
                $('#change').on('change', function(e) {
                    if ($('#change').val() == 'toko') {
                        $('#prod').hide();
                        $.ajax({
                            'dataType': 'json',
                            'url': '<?= site_url('welcome/getDashboardByMap/') ?>',
                            'success': function(data) {
                                let datas = data;

                                var ids = []; //creating array for storing browser type in array.
                                var values = [];
                                var showLabel = [];
                                var max = [];
                                var lenght = [];

                                for (var i = 0; i < datas.length; i++) {
                                    ids.push(datas[i]['id']);
                                    values.push(parseInt(datas[i]['value']) || 0);
                                }
                                // console.log(showLabel);
                                lenght.push(parseInt(datas.length));
                                showLabel.push('Toko');
                                if (parseInt(datas.length) != 0) {
                                    max.push(parseInt(datas[0]['value']));
                                } else {
                                    max.push(1);
                                }

                                mapChart(datas, ids, values, showLabel, max);
                            }
                        });
                    } else {
                        $('#prod').show();
                    }
                });
                $("#chart_principal_2").hide();

                $("#chart_2").click(function() {
                    $("#chart_principal_2").toggle("slow", function() {
                        if ($("#chart_principal_2").is(":hidden")) {
                            document.getElementById('chart_2').innerHTML = '<i class="fa fa-eye-slash"></i> <?= lang('show_map_chart'); ?>';
                            if ($("#chart_principal_1").is(":hidden")) {
                                $("#eye").addClass('fa-eye-slash');
                                $("#eye").removeClass('fa-eye');
                                document.getElementById('info_text').innerHTML = '<?php echo lang('info_chart_principal'); ?>';
                            } else {
                                $("#eye").removeClass('fa-eye-slash');
                                $("#eye").addClass('fa-eye');
                                document.getElementById('info_text').innerHTML = '<?php echo lang('overview_chart_heading'); ?>';
                            }
                        } else {
                            $("#eye").removeClass('fa-eye-slash');
                            $("#eye").addClass('fa-eye');
                            document.getElementById('chart_2').innerHTML = '<i class="fa fa-eye"></i> <?= lang('hide_map_chart'); ?>';
                            document.getElementById('info_text').innerHTML = '<?php echo lang('overview_chart_heading'); ?>';
                            $.ajax({
                                'dataType': 'json',
                                'url': '<?= site_url('welcome/getDashboardByMap/') ?>',
                                'success': function(data) {
                                    let datas = data;

                                    var ids = []; //creating array for storing browser type in array.
                                    var values = [];
                                    var showLabel = [];
                                    var max = [];
                                    var lenght = [];

                                    for (var i = 0; i < datas.length; i++) {
                                        ids.push(datas[i]['id']);
                                        values.push(parseInt(datas[i]['value']) || 0);
                                    }
                                    lenght.push(parseInt(datas.length));
                                    showLabel.push('Toko');
                                    if (parseInt(datas.length) != 0) {
                                        max.push(parseInt(datas[0]['value']));
                                    } else {
                                        max.push(1);
                                    }
                                    // console.log(datas);
                                    mapChart(datas, ids, values, showLabel, max);
                                }
                            });
                        }
                    });
                });

                function mapChart(datas, ids, values, showLabel, max) {
                    // console.log(max);
                    FusionCharts.ready(function() {
                        var annualPopulation = new FusionCharts({
                            "type": "maps/indonesia",
                            "renderAt": "chart-container",
                            "width": "100%",
                            "height": "550",
                            "dataFormat": "json",
                            "dataSource": {
                                // Map Configuration
                                "chart": {
                                    "caption": "<?= lang('peta_indonesia') ?>",
                                    "includevalueinlabels": "1",
                                    "labelsepchar": ": ",
                                    "entityFillHoverColor": "#FFF9C4",
                                    "theme": "fusion"
                                },
                                // Aesthetics; ranges synced with the slider
                                "colorrange": {
                                    "minvalue": "0",
                                    "code": "#FFE0B2",
                                    "gradient": "1",
                                    "color": [{
                                        "minvalue": "20",
                                        "maxvalue": max,
                                        "color": "#E65100"
                                    }],
                                    "endlabel": showLabel,
                                    "startlabel": showLabel,
                                },
                                // Source data as JSON --> id represents countries of world.
                                "data": datas
                            }
                        });
                        annualPopulation.render();
                    });
                }
            <?php } ?>

        });
    </script>

    <?php if (($Owner || $Admin || $Principal)) { ?>
        <style type="text/css" media="screen">
            .tooltip-inner {
                max-width: 500px;
            }
        </style>
        <script src="<?= $assets; ?>js/hc/highcharts.js"></script>
        <script type="text/javascript">
            $(function() {
                $('#distri').click(function() {
                    var product = document.getElementById('product').value;
                    var brand = document.getElementById('brands').value;
                    console.log(brand);
                    $.ajax({
                        'dataType': 'json',
                        'url': '<?= site_url('welcome/getDashboardChartData/') ?>?prod=' + product + '&brand=' + brand,
                        'success': function(data) {
                            let datas = data;

                            var mtax1 = []; //creating array for storing browser type in array.
                            var mtax2 = [];
                            var msales = [];
                            var unit_qty = [];
                            var unit_qtyz = [];
                            var mpurchases = [];
                            var mtax3 = [];
                            var month = [];

                            for (var i = 0; i < datas.length; i++) {
                                month.push(datas[i]['month']);
                                mtax1.push(parseInt(datas[i]['tax1']) || 0);
                                mtax2.push(parseInt(datas[i]['tax2']) || 0);
                                msales.push(parseInt(datas[i]['sales']) || 0);
                                unit_qty.push(parseInt(datas[i]['unit_qty']) || 0);
                                unit_qtyz.push(parseInt(datas[i]['unit_qtyz']) || 0);
                                mpurchases.push(parseInt(datas[i]['purchases']) || 0);
                                mtax3.push(parseInt(datas[i]['ptax']) || 0);
                            }
                            // console.log(month);
                            firstChart(month, mtax1, mtax2, msales, unit_qty, unit_qtyz, mpurchases, mtax3);
                        }
                    });
                    // console.log(product);
                });
                Highcharts.getOptions().colors = Highcharts.map(Highcharts.getOptions().colors, function(color) {
                    return {
                        radialGradient: {
                            cx: 0.5,
                            cy: 0.3,
                            r: 0.7
                        },
                        stops: [
                            [0, color],
                            [1, Highcharts.Color(color).brighten(-0.3).get('rgb')]
                        ]
                    };
                });
                <?php if ($Principal) { ?>

                    $("#chart_principal_1").hide();
                    $("#chart_1").click(function() {
                        $("#chart_principal_1").toggle("slow", function() {
                            if ($("#chart_principal_1").is(":hidden")) {
                                document.getElementById('chart_1').innerHTML = '<i class="fa fa-eye-slash"></i> <?= lang('show_grafik_chart'); ?>';
                                if ($("#chart_principal_2").is(":hidden")) {
                                    $("#eye").addClass('fa-eye-slash');
                                    $("#eye").removeClass('fa-eye');
                                    document.getElementById('info_text').innerHTML = '<?php echo lang('info_chart_principal'); ?>';
                                } else {
                                    $("#eye").removeClass('fa-eye-slash');
                                    $("#eye").addClass('fa-eye');
                                    document.getElementById('info_text').innerHTML = '<?php echo lang('overview_chart_heading'); ?>';
                                }
                            } else {
                                document.getElementById('chart_1').innerHTML = '<i class="fa fa-eye"></i> <?= lang('hide_grafik_chart'); ?>';
                                $("#eye").removeClass('fa-eye-slash');
                                $("#eye").addClass('fa-eye');
                                document.getElementById('info_text').innerHTML = '<?php echo lang('overview_chart_heading'); ?>';
                                $.ajax({
                                    'dataType': 'json',
                                    'url': '<?= site_url('welcome/getDashboardChartData/') ?>',
                                    'success': function(data) {
                                        let datas = data;

                                        var mtax1 = []; //creating array for storing browser type in array.
                                        var mtax2 = [];
                                        var msales = [];
                                        var unit_qty = [];
                                        var unit_qtyz = [];
                                        var mpurchases = [];
                                        var mtax3 = [];
                                        var month = [];

                                        for (var i = 0; i < datas.length; i++) {
                                            month.push(datas[i]['month']);
                                            mtax1.push(parseInt(datas[i]['tax1']) || 0);
                                            mtax2.push(parseInt(datas[i]['tax2']) || 0);
                                            msales.push(parseInt(datas[i]['sales']) || 0);
                                            unit_qty.push(parseInt(datas[i]['unit_qty']) || 0);
                                            unit_qtyz.push(parseInt(datas[i]['unit_qtyz']) || 0);
                                            mpurchases.push(parseInt(datas[i]['purchases']) || 0);
                                            mtax3.push(parseInt(datas[i]['ptax']) || 0);
                                        }
                                        // console.log(month);
                                        firstChart(month, mtax1, mtax2, msales, unit_qty, unit_qtyz, mpurchases, mtax3);
                                    }
                                });
                            }
                        });
                    });
                <?php } else { ?>
                    $.ajax({
                        'dataType': 'json',
                        'url': '<?= site_url('welcome/getDashboardChartData/') ?>?tipe=not',
                        'success': function(data) {
                            let datas = data;

                            var mtax1 = []; //creating array for storing browser type in array.
                            var mtax2 = [];
                            var msales = [];

                            var mpurchases = [];
                            var mtax3 = [];
                            var month = [];

                            for (var i = 0; i < datas.length; i++) {
                                month.push(datas[i]['month']);
                                mtax1.push(parseInt(datas[i]['tax1']) || 0);
                                mtax2.push(parseInt(datas[i]['tax2']) || 0);
                                msales.push(parseInt(datas[i]['sales']) || 0);
                                mpurchases.push(parseInt(datas[i]['purchases']) || 0);
                                mtax3.push(parseInt(datas[i]['ptax']) || 0);
                            }
                            // console.log(month);
                            secondChart(month, mtax1, mtax2, msales, mpurchases, mtax3);
                        }
                    });
                <?php } ?>
                Highcharts.setOptions({
                    lang: {
                        numericSymbols: [' K', ' Jt', ' M', ' T', ' Kuard', ' Kuant']
                    }
                });

                function firstChart(month, mtax1, mtax2, msales, unit_qty, unit_qtyz, mpurchases, mtax3) {
                    $('#ov-chart').highcharts({
                        chart: {},
                        credits: {
                            enabled: false
                        },
                        title: {
                            text: ''
                        },
                        xAxis: {
                            categories: month
                        },
                        yAxis: [{
                                min: 0,
                                title: {
                                    text: ' <?= lang("rupiah"); ?>',
                                    style: {
                                        color: Highcharts.getOptions().colors[0]
                                    }
                                },
                            }
                            <?php if ($Principal) { ?>, { // Secondary yAxis
                                    min: 0,
                                    title: {
                                        text: ' <?= lang("tonase"); ?>',
                                        style: {
                                            color: Highcharts.getOptions().colors[0]
                                        }
                                    },
                                    opposite: true

                                }
                            <?php } ?>
                        ],

                        tooltip: {
                            shared: true,
                            followPointer: true,
                            formatter: function() {
                                if (this.key) {
                                    return '<div class="tooltip-inner hc-tip" style="margin-bottom:0;">' + this.key + '<br><strong>' + currencyFormat(this.y) + '</strong> (' + formatNumber(this.percentage) + '%)';
                                } else {
                                    var s = '<div class="well well-sm hc-tip" style="margin-bottom:0;"><h2 style="margin-top:0;">' + this.x + '</h2><table class="table table-striped"  style="margin-bottom:0;">';
                                    $.each(this.points, function() {
                                        s += '<tr><td style="color:{series.color};padding:0">' + this.series.name + ': </td><td style="color:{series.color};padding:0;text-align:right;"> <b>' +
                                            currencyFormat(this.y) + '</b></td></tr>';
                                    });
                                    s += '</table></div>';
                                    return s;
                                }
                            },
                            useHTML: true,
                            borderWidth: 0,
                            shadow: false,
                            valueDecimals: site.settings.decimals,
                            style: {
                                fontSize: '14px',
                                padding: '0',
                                color: '#000000'
                            }
                        },
                        series: [
                            <?php if (!$Principal) { ?> {
                                    type: 'column',
                                    name: '<?= lang("sp_tax"); ?>',
                                    data: mtax1
                                },
                                {
                                    type: 'column',
                                    name: '<?= lang("order_tax"); ?>',
                                    data: mtax2
                                },
                            <?php } ?> {
                                type: 'column',
                                name: '<?= lang("sales"); ?>',
                                data: msales
                            },
                            <?php if ($Principal) { ?> {
                                    type: 'spline',
                                    name: '<?= lang("tonase_sale"); ?>',
                                    yAxis: 1,
                                    data: unit_qty,
                                    tooltip: {
                                        valueSuffix: ' TON'
                                    }
                                },
                                {
                                    type: 'spline',
                                    name: '<?= lang("tonase_purchase"); ?>',
                                    yAxis: 1,
                                    data: unit_qtyz,
                                    tooltip: {
                                        valueSuffix: ' TON'
                                    }
                                },
                            <?php } ?> {
                                type: 'column',
                                name: '<?= lang("purchases"); ?>',
                                data: mpurchases,
                                marker: {
                                    lineWidth: 2,
                                    states: {
                                        hover: {
                                            lineWidth: 4
                                        }
                                    },
                                    lineColor: Highcharts.getOptions().colors[3],
                                    fillColor: 'white'
                                }
                            },
                            <?php if (!$Principal) { ?> {
                                    type: 'spline',
                                    name: '<?= lang("pp_tax"); ?>',
                                    data: mtax3,
                                    marker: {
                                        lineWidth: 2,
                                        states: {
                                            hover: {
                                                lineWidth: 4
                                            }
                                        },
                                        lineColor: Highcharts.getOptions().colors[3],
                                        fillColor: 'white'
                                    }
                                },
                            <?php } ?> {
                                type: 'pie',
                                name: '<?= lang("stock_value"); ?>',
                                data: [
                                    ['', 0],
                                    ['', 0],
                                    ['<?= lang("stock_value_by_price"); ?>', <?php echo $stock->stock_by_price; ?>],
                                    ['<?= lang("stock_value_by_cost"); ?>', <?php echo $stock->stock_by_cost; ?>],
                                ],
                                center: [80, 42],
                                size: 80,
                                showInLegend: false,
                                dataLabels: {
                                    enabled: false
                                }
                            }
                        ]
                    });
                }

                function secondChart(month, mtax1, mtax2, msales, mpurchases, mtax3) {
                    $('#ov-chart').highcharts({
                        chart: {},
                        credits: {
                            enabled: false
                        },
                        title: {
                            text: ''
                        },
                        xAxis: {
                            categories: month
                        },
                        yAxis: [{
                            min: 0,
                            title: {
                                text: ' <?= lang("rupiah"); ?>',
                                style: {
                                    color: Highcharts.getOptions().colors[0]
                                }
                            },
                        }],

                        tooltip: {
                            shared: true,
                            followPointer: true,
                            formatter: function() {
                                if (this.key) {
                                    return '<div class="tooltip-inner hc-tip" style="margin-bottom:0;">' + this.key + '<br><strong>' + currencyFormat(this.y) + '</strong> (' + formatNumber(this.percentage) + '%)';
                                } else {
                                    var s = '<div class="well well-sm hc-tip" style="margin-bottom:0;"><h2 style="margin-top:0;">' + this.x + '</h2><table class="table table-striped"  style="margin-bottom:0;">';
                                    $.each(this.points, function() {
                                        s += '<tr><td style="color:{series.color};padding:0">' + this.series.name + ': </td><td style="color:{series.color};padding:0;text-align:right;"> <b>' +
                                            currencyFormat(this.y) + '</b></td></tr>';
                                    });
                                    s += '</table></div>';
                                    return s;
                                }
                            },
                            useHTML: true,
                            borderWidth: 0,
                            shadow: false,
                            valueDecimals: site.settings.decimals,
                            style: {
                                fontSize: '14px',
                                padding: '0',
                                color: '#000000'
                            }
                        },
                        series: [
                            <?php if (!$Principal) { ?> {
                                    type: 'column',
                                    name: '<?= lang("sp_tax"); ?>',
                                    data: mtax1
                                },
                                {
                                    type: 'column',
                                    name: '<?= lang("order_tax"); ?>',
                                    data: mtax2
                                },
                            <?php } ?> {
                                type: 'column',
                                name: '<?= lang("sales"); ?>',
                                data: msales
                            },
                            {
                                type: 'column',
                                name: '<?= lang("purchases"); ?>',
                                data: mpurchases,
                                marker: {
                                    lineWidth: 2,
                                    states: {
                                        hover: {
                                            lineWidth: 4
                                        }
                                    },
                                    lineColor: Highcharts.getOptions().colors[3],
                                    fillColor: 'white'
                                }
                            },
                            <?php if (!$Principal) { ?> {
                                    type: 'spline',
                                    name: '<?= lang("pp_tax"); ?>',
                                    data: mtax3,
                                    marker: {
                                        lineWidth: 2,
                                        states: {
                                            hover: {
                                                lineWidth: 4
                                            }
                                        },
                                        lineColor: Highcharts.getOptions().colors[3],
                                        fillColor: 'white'
                                    }
                                },
                            <?php } ?> {
                                type: 'pie',
                                name: '<?= lang("stock_value"); ?>',
                                data: [
                                    ['', 0],
                                    ['', 0],
                                    ['<?= lang("stock_value_by_price"); ?>', <?php echo $stock->stock_by_price; ?>],
                                    ['<?= lang("stock_value_by_cost"); ?>', <?php echo $stock->stock_by_cost; ?>],
                                ],
                                center: [80, 42],
                                size: 80,
                                showInLegend: false,
                                dataLabels: {
                                    enabled: false
                                }
                            }
                        ]
                    });
                }
            });
        </script>

        <script type="text/javascript">
            $(function() {
                <?php if ($lmbs) { ?>
                    $('#lmbschart').highcharts({
                        chart: {
                            type: 'column'
                        },
                        title: {
                            text: ''
                        },
                        credits: {
                            enabled: false
                        },
                        xAxis: {
                            type: 'category',
                            labels: {
                                rotation: -60,
                                style: {
                                    fontSize: '13px'
                                }
                            }
                        },
                        yAxis: {
                            min: 0,
                            title: {
                                text: ''
                            }
                        },
                        legend: {
                            enabled: false
                        },
                        series: [{
                            name: '<?= lang('sold'); ?>',
                            data: [<?php
                                    foreach ($lmbs as $r) {
                                        if ($r->quantity > 0) {
                                            echo "['" . $r->product_name . "<br>(" . $r->product_code . ")', " . $r->quantity . "],";
                                        }
                                    }
                                    ?>],
                            dataLabels: {
                                enabled: true,
                                rotation: -90,
                                color: '#000',
                                align: 'right',
                                y: -25,
                                style: {
                                    fontSize: '12px'
                                }
                            }
                        }]
                    });
                <?php }
                if ($bs) { ?>
                    $('#bschart').highcharts({
                        chart: {
                            type: 'column'
                        },
                        title: {
                            text: ''
                        },
                        credits: {
                            enabled: false
                        },
                        xAxis: {
                            type: 'category',
                            labels: {
                                rotation: -60,
                                style: {
                                    fontSize: '13px'
                                }
                            }
                        },
                        yAxis: {
                            min: 0,
                            title: {
                                text: ''
                            }
                        },
                        legend: {
                            enabled: false
                        },
                        series: [{
                            name: '<?= lang('sold'); ?>',
                            data: [<?php
                                    foreach ($bs as $r) {
                                        if ($r->quantity > 0) {
                                            echo "['" . $r->product_name . "<br>(" . $r->product_code . ")', " . $r->quantity . "],";
                                        }
                                    }
                                    ?>],
                            dataLabels: {
                                enabled: true,
                                rotation: -90,
                                color: '#000',
                                align: 'right',
                                y: -25,
                                style: {
                                    fontSize: '12px'
                                }
                            }
                        }]
                    });
                <?php } ?>
                //     $('#promoModal').modal('show');
            });
        </script>
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-sm-6">
                <div class="box">
                    <div class="box-header">
                        <h2 class="blue"><i class="fa-fw fa fa-line-chart"></i><?= lang('best_sellers'), ' (' . date('M-Y', time()) . ')'; ?>
                        </h2>
                    </div>
                    <div class="box-content">
                        <div class="row">
                            <div class="col-md-12">
                                <div id="bschart" style="width:100%; height:450px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="box">
                    <div class="box-header">
                        <h2 class="blue"><i class="fa-fw fa fa-line-chart"></i><?= lang('best_sellers') . ' (' . date('M-Y', strtotime('-1 month')) . ')'; ?>
                        </h2>
                    </div>
                    <div class="box-content">
                        <div class="row">
                            <div class="col-md-12">
                                <div id="lmbschart" style="width:100%; height:450px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>