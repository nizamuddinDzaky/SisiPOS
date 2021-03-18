<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style type="text/css">
    /* Progress Bar Step
============================================= */
.wizard {
    margin: 10px auto;
    background: #fff;
}

.wizard .nav-tabs {
    position: relative;
    margin-bottom: 0;
    border-bottom-color: #e0e0e0;
}

.wizard>div.wizard-inner {
    position: relative;
}

.connecting-line {
    height: 2px;
    background: #e0e0e0;
    position: absolute;
    width: 0%;
    margin: 0 auto;
    left: 0;
    right: 0;
    top: 50%;
    z-index: 1;
}

.wizard .nav-tabs>li.active>a,
.wizard .nav-tabs>li.active>a:hover,
.wizard .nav-tabs>li.active>a:focus {
    color: #555555;
    cursor: default;
    border: 0;
    border-bottom-color: transparent;
}

span.round-tab {
    width: 70px;
    height: 70px;
    line-height: 70px;
    display: inline-block;
    border-radius: 100px;
    background: #fff;
    border: 2px solid #e0e0e0;
    z-index: 2;
    position: absolute;
    left: 0;
    text-align: center;
    font-size: 25px;
}

span.round-tab i {
    color: #555555;
}

.wizard li.active span.round-tab {
    background: #fff;
    border: 2px solid #5bc0de;
}

.wizard li.active span.round-tab i {
    color: #5bc0de;
}

span.round-tab:hover {
    color: #333;
    border: 2px solid #333;
}

.wizard .nav-tabs>li {
    width: 20%;
}

.wizard li:after {
    content: " ";
    position: absolute;
    left: 46%;
    opacity: 0;
    margin: 0 auto;
    bottom: 0px;
    border: 5px solid transparent;
    border-bottom-color: #5bc0de;
    transition: 0.1s ease-in-out;
}

.wizard li.active:after {
    content: " ";
    position: absolute;
    left: 46%;
    opacity: 1;
    margin: 0 auto;
    bottom: 0px;
    border: 10px solid transparent;
    border-bottom-color: #5bc0de;
}

.wizard .nav-tabs>li a {
    width: 70px;
    height: 70px;
    margin: 20px auto;
    border-radius: 100%;
    padding: 0;
}

.wizard .nav-tabs>li a:hover {
    background: transparent;
}

.wizard .tab-pane {
    position: relative;
    padding-top: 50px;
}

.wizard h3 {
    margin-top: 0;
}

.table_custom {
    border: 2px solid;
    padding: 100px;
    box-shadow: 5px 7px 5px #cfddfc;
}

@media(max-width: 900px) {
    .wizard {
        width: 90%;
        height: auto !important;
    }

    span.round-tab {
        font-size: 16px;
        width: 50px;
        height: 50px;
        line-height: 50px;
    }

    .wizard .nav-tabs>li a {
        width: 50px;
        height: 50px;
        line-height: 50px;
    }

    .wizard li.active:after {
        content: " ";
        position: absolute;
        left: 35%;
    }
}
</style>

<div id="loader" style="height: 100%;left: 0;position: fixed; top: 0; width: 100%; z-index: 999999; background: rgba(0, 0, 0, 0.5);">
    <div  class="dots-loader" style="margin-left: 50%; margin-top: 25%;"></div>
</div>
<!-- <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-body ui-front"> -->
<div class="form-example-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="form-example-wrap">
                    <section>
                        <div class="wizard">
                            <center><div class="wizard-inner">
                                <div class="connecting-line"></div>

                                <ul class="nav nav-tabs" role="tablist">
                                    <li role="presentation" class="active">
                                        <a href="#plans" data-toggle="tab" aria-controls="plans" role="tab" title=<?= lang('view_plans') ?>>
                                            <span class="round-tab"><i class="fa fa-table"></i></span>
                                        </a>
                                    </li>
                                    <li role="presentation" class="disabled">
                                        <a href="#addons" data-toggle="tab" aria-controls="addons" role="tab" title=<?= lang('addons') ?>>
                                            <span class="round-tab"><i class="fa fa-cogs"></i></span>
                                        </a>
                                    </li>
                                    <li role="presentation" class="disabled">
                                        <a href="#checkout" data-toggle="tab" aria-controls="checkout" role="tab" title=<?= lang('checkout') ?>>
                                            <span class="round-tab"><i class="fa fa-credit-card"></i></span>
                                        </a>
                                    </li>
                                    <li role="presentation" class="disabled">
                                        <a href="#invoice" data-toggle="tab" aria-controls="invoice" role="tab" title=<?= lang('invoice') ?>>
                                            <span class="round-tab"><i class="fa fa-file-text-o"></i></span>
                                        </a>
                                    </li>
                                    <li role="presentation" class="disabled">
                                        <a href="#payment" data-toggle="tab" aria-controls="payment" role="tab" title=<?= lang('payment') ?>>
                                            <span class="round-tab">
                                                <i class="fa fa-dollar"></i>
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </div></center>

                            <div class="tab-content">
                                <div class="tab-pane active" role="tabpanel" id="plans">
                                    <div class="col-md-12">
                                    <?php $i=0;
                                    foreach ($plans as $plan) {
                                        if($i<2){?>
                                        <div class="col-md-6">
                                            <div class="well text-center">
                                                <?php
                                                echo '<h1>' . $plan->name . '</h1>';
                                                /*if ($plan->name != 'Free')
                                                    echo '<h2>Call Us !</h2>';
                                                else*/
                                                    echo '<h2>' . $default_currency->code . ' ' . $this->sma->formatMoney($plan->price) . '</h2>';
                                                ?>
                                                <center>
                                                <table style="text-align: center;">
                                                    <tr>
                                                        <th><?= $plan->users ?></th>
                                                        <th> &nbsp;&nbsp; </th>
                                                        <th>Users</th>
                                                    </tr>
                                                    <tr>
                                                        <th><?= $plan->warehouses ?></th>
                                                        <th> &nbsp;&nbsp;</th>
                                                        <th>Warehouses</th>
                                                    </tr>
                                                    <tr>
                                                        <th><?= ($plan->master ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>') ?></th>
                                                        <th> &nbsp;&nbsp;</th>
                                                        <th><?= lang('master_data') ?></th></th>
                                                    </tr>
                                                    <tr>
                                                        <th><?= ($plan->pos ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>') ?></th>
                                                        <th> &nbsp;&nbsp;</th>
                                                        <th><?= lang('pos') ?></th></th>
                                                    </tr>
                                                    <tr>
                                                        <th><?= ($plan->purchases ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>') ?></th>
                                                        <th> &nbsp;&nbsp;</th>
                                                        <th><?= lang('purchases') ?></th></th>
                                                    </tr>
                                                    <tr>
                                                        <th><?= ($plan->sales ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>') ?></th>
                                                        <th> &nbsp;&nbsp;</th>
                                                        <th><?= lang('sales') ?></th></th>
                                                    </tr>
                                                    <tr>
                                                        <th><?= ($plan->quotes ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>') ?></th>
                                                        <th> &nbsp;&nbsp;</th>
                                                        <th><?= lang('quotes') ?></th></th>
                                                    </tr>
                                                    <tr>
                                                        <th><?= ($plan->expenses ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>') ?></th>
                                                        <th> &nbsp;&nbsp;</th>
                                                        <th><?= lang('expenses') ?></th></th>
                                                    </tr>
                                                    <tr>
                                                        <th><?= ($plan->reports ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>') ?></th>
                                                        <th> &nbsp;&nbsp;</th>
                                                        <th><?= lang('reports') ?></th></th>
                                                    </tr>
                                                    <tr>
                                                        <th><?= ($plan->transfers ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>') ?></th>
                                                        <th> &nbsp;&nbsp;</th>
                                                        <th><?= lang('transfers') ?></th></th>
                                                    </tr>
                                                </table></center><br>
                                                <!-- <ul class="list-unstyled">
                                                    <li> </li>
                                                    <li><?php echo $plan->warehouses ?> Warehouses</li>
                                                    <li></li>
                                                    <li><?php echo ($plan->pos ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>') . ' ' . lang('pos') ?></li>
                                                    <li><?php echo ($plan->purchases ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>') . ' ' . lang('purchases') ?></li>
                                                    <li><?php echo ($plan->sales ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>') . ' ' . lang('sales') ?></li>
                                                    <li><?php echo ($plan->quotes ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>') . ' ' . lang('quotes') ?></li>
                                                    <li><?php echo ($plan->expenses ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>') . ' ' . lang('expenses') ?></li>
                                                    <li><?php echo ($plan->reports ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>') . ' ' . lang('reports') ?></li>
                                                    <li><?php echo ($plan->transfers ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>') . ' ' . lang('transfers') ?></li>
                                                </ul> -->
                                        <?= '<button type="button" class="btn btn-primary choose_plan next-step" style="width: 100%" onclick="choose_plan(' . $plan->id . ')" ' . (($plan->name == $current->plan_name) ? 'disabled' : '') . '>' . (($plan->name == $current->plan_name) ? 'Now' : 'Subscribe') . '</button>'; ?>
                                            </div>
                                        </div>
                                        <?php }$i++;} ?>
                                    </div>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="addons">
                                    <h2 class="text-center">Add-On & Payment Period</h2><br><br>
                                    <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'planForm');
                                    echo form_open("", $attrib); ?>
                                    <div class="row">
                                        <div class="col-md-6" >
                                            <div class="form-group">
                                                <h4><label>Add-on (optional) : </label>&nbsp;&nbsp; <span><i class="fa fa-question-circle" data-toggle="tooltip" data-placement="right" title="Penambahan user atau warehouse diluar paket plan"></i></span></h4>
                                            </div>
                                            <?php  foreach ($addons as $aon) { ?>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                    <?php if($aon->name!='user' && $aon->name!='warehouse'){?>
                                                        <?= lang($aon->name, 'p_price_'.$aon->id) ?>
                                                        <div class="input-group">
                                                            <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;"><?= $this->Settings->default_currency ?></div>
                                                            <?= form_input('p_price_'.$aon->id, $this->sma->formatMoney($aon->price), 'class="form-control tip number-only" readonly') ?>
                                                            <span class="input-group-addon">
                                                                <input type="checkbox" name="p_<?= $aon->id ?>" id="p_<?= $aon->id ?>" class="add_on">
                                                            </span>
                                                        </div>
                                                    <?php } else{ ?>
                                                        <?= lang($aon->name, 'p_qty_'.$aon->id) ?>
                                                        <div class="input-group">
                                                            <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;"><?= $this->sma->formatMoney($aon->price) ?>. /5 Item</div>

                                                            <?= form_input('p_qty_'.$aon->id, NULL, 'class="form-control number-only add_on" placeholder="Jumlah" id="p_qty_'.$aon->id.'"') ?>
                                                        </div>
                                                        <span id="InfoImageBrand"><i style="color:red;"><strong>* Price : </strong><?= $this->Settings->default_currency. ' ' .$this->sma->formatMoney($aon->price) ?> For 5 Item</i></span>
                                                    <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php } ?>
                                        </div>
                                        <div class="col-md-1"></div>
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <h4><label>Payment :</label></h4>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                                                        <div class="fm-checkbox">
                                                            <label><input type="radio" value="1" name="payment_period" class="i-checks"> <i></i><b> Monthly</b></label>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                                                        <div class="fm-checkbox">
                                                            <label><input type="radio" value="6" name="payment_period" class="i-checks"> <i></i><b> Per 6 Month</b></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="col-md-12">
                                        <ul class="list-inline pull-right">
                                            <li><?php echo form_input('add_plan', lang('continue'), 'hidden'); ?></li>
                                            <li><button type="button" class="btn btn-default prev-step"><?= lang('previous') ?></button></li>
                                            <!--<li><button type="button" class="btn btn-primary" onclick=""><?= lang('continue') ?></button></li>-->
                                            <li><button type="submit" name="add_plan" value="continue" class="btn btn-primary"><?= lang('continue') ?></button></li>
                                        </ul>
                                    </div>
                                    <?php echo form_close(); ?>
                                </div>

                                <div class="tab-pane" role="tabpanel" id="checkout">
                                    <form data-toggle='validator' role='form' id='checkout_form'>
                                    <h2 class="text-center">Checkout</h2><br>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover table-striped print-table order-table table_custom">
                                            <thead>
                                                <tr>
                                                    <th class="text-center"><?= lang("no"); ?></th>
                                                    <th class="text-center"><?= lang("description"); ?></th>
                                                    <th class="text-center"><?= lang("item_price"); ?></th>
                                                    <th class="text-center"><?= lang("quantity"); ?></th>
                                                    <th class="text-center"><?= lang("price"); ?></th>
                                                </tr>
                                            </thead>

                                            <tbody id="c_item"></tbody>

                                            <tfoot>
                                                <?php $col = 4; ?>
                                                <tr>
                                                    <td colspan="<?= $col; ?>"
                                                        style="text-align:right; font-weight:bold;"><?= lang("total_amount"); ?>
                                                        (<?= $default_currency->code; ?>)
                                                    </td>
                                                    <td style="text-align:right; padding-right:10px; font-weight:bold;" id="c_total2"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <ul class="list-inline pull-right" style="margin-top: 25px;">
                                        <?php if($this->uri->segment(4) == ''){ ?>
                                        <li><button type="button" class="btn btn-default prev-step">Previous</button></li>
                                        <?php } ?>
                                        <li><button class="btn btn-primary checkout"><?= lang('go_to_invoice') ?></button></li>
                                    </ul>
                                    </form>
                                </div>

                                <div class="tab-pane" role="tabpanel" id="invoice">
                                    <h2 class="text-center">Invoice</h2><br>
                                    <div class="text-center" style="margin-bottom:20px;">
                                        <img src="<?= $this->session->userdata('avatar') ? site_url() . 'assets/uploads/avatars/thumbs/' . $this->session->userdata('avatar') : base_url() . 'assets/uploads/logos/logo3.png'; ?>">
                                    </div>
                                    <div class="well well-sm">
                                        <div class="row bold">
                                            <div class="col-xs-5">
                                                <p class="bold">
                                                    <table>
                                                        <tr>
                                                            <th><b><?= lang("date"); ?></b> </th>
                                                            <th>&nbsp; : &nbsp;</th>
                                                            <th><span id="bidate"></span></th>
                                                        </tr>
                                                        <tr>
                                                            <th><b><?= lang("ref"); ?></b> </th>
                                                            <th>&nbsp; : &nbsp;</th>
                                                            <th><span id="biref"></span></th>
                                                        </tr>
                                                        <tr>
                                                            <th><b><?= lang("payment_status"); ?></b> </th>
                                                            <th>&nbsp; : &nbsp;</th>
                                                            <th><span id="bipayment"></span></th>
                                                        </tr>
                                                    </table>
                                                </p>
                                            </div>
                                            <div class="clearfix"></div>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>

                                    <div class="row" style="margin-bottom:15px;">
                                        <div class="col-xs-7">
                                            <h3 style="margin-top:10px;" id="bicompany"></h3>
                                            <div class="row">
                                                <div class="col-xs-5"><span id="biaddress"></span></div>
                                                <div class="col-xs-2"></div>
                                                <div class="col-xs-5"></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-xs-5"><span id="bicity"></span></div>
                                                <div class="col-xs-2"></div>
                                                <div class="col-xs-5"></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-xs-5"><span id="bicountry"></span></div>
                                                <div class="col-xs-2"></div>
                                                <div class="col-xs-5"></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-xs-5"><?= lang("tel").' : ' ?><span id="bitel"></span></div>
                                                <div class="col-xs-2"></div>
                                                <div class="col-xs-5"></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-xs-5"><?= lang("mail").' : ' ?><span id="biemail"></span></div>
                                                <div class="col-xs-2"></div>
                                                <div class="col-xs-5"></div>
                                            </div>

                                            <?= $billing ? ($row->company ? "" : "Attn: " . $row->name) : '' ?>
                                                
                                            <?php // echo $row->address."<br>".$row->city." ".$row->postal_code." ".$row->state."<br>".$row->country; ?>

                                            <!-- <?php echo lang("tel") . ": <span id='bitel'>" . $row->phone . "</span><br>" . lang("email") . ": <span id='biemail'>" . $row->email . "</span>"; ?> -->
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover table-striped print-table order-table">
                                            <thead>
                                                <tr>
                                                    <th class="text-center"><?= lang("no"); ?></th>
                                                    <th class="text-center"><?= lang("description"); ?></th>
                                                    <th class="text-center"><?= lang("item_price"); ?></th>
                                                    <th class="text-center"><?= lang("quantity"); ?></th>
                                                    <th class="text-center"><?= lang("price"); ?></th>
                                                </tr>
                                            </thead>

                                            <tbody id="biitem"></tbody>

                                            <tfoot>
                                                <?php $col = 4; ?>
                                                <tr>
                                                    <td colspan="<?= $col; ?>"
                                                        style="text-align:right; font-weight:bold;"><?= lang("total_amount"); ?>
                                                        (<?= $default_currency->code; ?>)
                                                    </td>
                                                    <td style="text-align:right; padding-right:10px; font-weight:bold;" id="bitotal2"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="<?= $col; ?>"
                                                        style="text-align:right; font-weight:bold;"><?= lang("paid"); ?>
                                                        (<?= $default_currency->code; ?>)
                                                    </td>
                                                    <td style="text-align:right; font-weight:bold;" id="biamount"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="<?= $col; ?>"
                                                        style="text-align:right; font-weight:bold;"><?= lang("balance"); ?>
                                                        (<?= $default_currency->code; ?>)
                                                    </td>
                                                    <td style="text-align:right; font-weight:bold;" id="bibalance"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <ul class="list-inline pull-right">
                                        <!-- <li><button type="button" class="btn btn-default prev-step">Previous</button></li> -->
                                        <li><button type="button" class="btn btn-primary next-step"><?= lang('continue') ?></button></li>
                                    </ul>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="payment">
                                    <h2 class="text-center">Payment</h2><br>

                                    <?php  $attribute = array('data-toggle' => 'validator', 'role' => 'form');
                                        if($author->status == 'activated'){
                                            echo form_open_multipart("billing_portal/subscription/add_proof_payment_renewal/", $attribute);
                                        }else{
                                            echo form_open_multipart("billing_portal/subscription/add_proof_payment/", $attribute);
                                        }
                                    ?>
                                    <div id="use_pay">
                                        <div class="row" style="padding: 20px 20px 20px 20px; margin:20px -40px 20px 20px;">
                                            <div class="col-md-5" style="box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2); border-radius: 5px; padding: 20px 20px 20px 20px; margin:20px 20px 20px 20px;">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="nk-int-mk sl-dp-mn sm-res-mg-t-10">
                                                            <h2>Payment Method</h2>
                                                        </div>
                                                        <div class="bootstrap-select fm-cmp-mg">
                                                            <select class="selectpicker">
                                                                <option>Transfer Bank</option>
                                                                <option disabled="disabled">Other</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12 text-center">
                                                        <img src="<?= base_url() . 'assets/uploads/bank_logo/bca.png'; ?>" style="max-height: 50px; margin: 20px;"><br>
                                                        <h4>No : 123-456-789</h4>
                                                        <h4>A.n : PT. Sinergi Informatika Semen Indonesia</h4>

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6" >
                                                <div class="row" style="box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2); border-radius: 5px; padding: 20px 20px 20px 20px; margin:20px 20px 20px 20px;">
                                                    <div class="col-md-12">
                                                        <div class="nk-int-mk sl-dp-mn sm-res-mg-t-10">
                                                            <h2>Payment Detail</h2><hr>
                                                        </div><br>
                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                                                <label class="hrzn-fm">Total Amount</label>
                                                            </div>
                                                            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                                                                <div class="nk-int-st">
                                                                    <input type="text" class="form-control" id="nominal" disabled="">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="row pay_before">
                                                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                                                <label class="hrzn-fm">Pay Before</label>
                                                            </div>
                                                            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                                                                <div class="nk-int-st">
                                                                    <input type="text" class="form-control" id="due_date" disabled="">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <br>
                                                    </div>
                                                </div>
                                                <div class="row" style="box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2); border-radius: 5px; padding: 20px 20px 20px 20px; margin:20px 20px 20px 20px;">
                                                    <div class="col-md-12">
                                                        <div class="nk-int-mk sl-dp-mn sm-res-mg-t-10">
                                                            <h2><?= lang("upload_payment_receipt", "upload_payment_receipt") ?></h2>
                                                        </div>
                                                        <input id="add_proof" type="file" data-browse-label="<?= lang('browse'); ?>" name="add_proof" data-show-upload="false" data-show-preview="false" class="form-control file" accept="image/*">
                                                        <span id="InfoImageBrand"><i style="color:red;"><sup><strong>*Recomended : </strong><?= "Width:" . $this->Settings->iwidth . "px, Height:" . $this->Settings->iheight . "px, Max File Size: 500" ?>Kb</sup></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <ul class="list-inline pull-right">
                                        <li><a href="<?= site_url().'billing_portal/subscription'?>" class="btn btn-default"><?= lang('pay_later') ?></a></li>
                                        <button type="submit" class="btn btn-primary" id="btnAddProof"><?= lang('submit') ?></button>
                                    </ul>
                                    <?php  echo form_close(); ?>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade checkout_modal" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <h2>Go To Invoice</h2>
                <p>Are You Sure ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" id="checkout_button" class="btn btn-default">Yes</button>
                <button type="button" class="btn" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade how_to_pay_modal" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-large">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <center>
                    <div class="row" >
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12"  style="margin-bottom: 40px;">
                            <h2>Support</h2><hr>
                            <div class="form-group nk-datapk-ctm form-elet-mg ">
                                <div class="input-group nk-int-st">
                                    <span class="input-group-addon"></span>
                                    <h2>Phone : 1234567890</h2>
                                </div>
                            </div>
                            <div class="form-group nk-datapk-ctm form-elet-mg ">
                                <div class="input-group nk-int-st">
                                    <span class="input-group-addon"></span>
                                    <h2>Mail : support@mail.com</h2>
                                </div>
                            </div>
                        </div>

                    </div>
                </center>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var url = $('#payment').find('form').attr('action');
    var pid;
    $('.choose_plan').hide();
    $('#loader').hide();

    function how_to_pay() {
        let param = 'how_to_pay';
        $('.'+param+'_modal').modal('show'); 
    };

    $(document).ready(function () {
        var renew = '<?= $this->uri->segment(4); ?>';
        var url_renew = '<?= $url_renew ?>';
        var url_pay_reject = '<?= @$url_pay_reject ?>';

        if(url_renew != ''){
            var get_bill = url_renew;
        }else if(url_pay_reject != ''){
            var get_bill = url_pay_reject;
        }else{
            var get_bill = "billing_portal/subscription/get_billing_invoice/";
        }

        $.ajax({
            type: "get", 
            async: false,
            url: site.base_url + get_bill,
            dataType: "json",
            success: function (data) {
                $('.choose_plan').show();
                try {
                    // var now = Date.parse(moment().format(site.dateFormats.js_sdate.toUpperCase()));
                    
                    if(data.billing || data.author){
                        var now = Date.now();
                        var dueDate = Date.parse(data.billing.due_date);
                        let author_status = data.author.status;
                        let bill_status = data.billing.billing_status;
                        let payment_status = data.billing.payment_status;
                        
                        if(renew == 'renew'){
                            nextTab();
                            var active_now = $('.wizard .nav-tabs li.active');
                            active_now.prev().addClass('disabled');
                            nextTab();
                            var active_now = $('.wizard .nav-tabs li.active');
                            active_now.prev().addClass('disabled');
                            setDataTemporary(data);
                        }
                        else if (data.billing && (bill_status == 'pending' && payment_status == 'rejected')) {
                            nextTab();
                            var active_now = $('.wizard .nav-tabs li.active');
                            active_now.prev().addClass('disabled');
                            nextTab();
                            var active_now = $('.wizard .nav-tabs li.active');
                            active_now.prev().addClass('disabled');
                            nextTab();
                            var active_now = $('.wizard .nav-tabs li.active');
                            active_now.prev().addClass('disabled');
                            nextTab();
                            var active_now = $('.wizard .nav-tabs li.active');
                            active_now.prev().addClass('disabled');
                            $("#nominal").attr({value:formatMoney(data.billing.total), readonly:"readonly"} );
                            $(".pay_before").hide();
                            $('#payment').find('form').attr('action', url + data.billing.id);
                        }
                        else if (data.billing && (bill_status == 'pending' || bill_status == 'pending renewal') ) {
                            nextTab();
                            var active_now = $('.wizard .nav-tabs li.active');
                            active_now.prev().addClass('disabled');
                            nextTab();
                            var active_now = $('.wizard .nav-tabs li.active');
                            active_now.prev().addClass('disabled');
                            nextTab();
                            var active_now = $('.wizard .nav-tabs li.active');
                            active_now.prev().addClass('disabled');

                            setDataInvoice(data);
                        }
                    }
                } catch (exception) {
                    alert("Error!! Data Tidak Bisa Diproses");
                }
            }
        });

        $('.nav-tabs > li a[title]').tooltip();

        $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
            var $target = $(e.target);
            if ($target.parent().hasClass('disabled')) {
                return false;
            }
        });

        $(".next-step").click(function (e) {
            nextTab();
        });

        $(".prev-step").click(function (e) {
            var active = $('.wizard .nav-tabs li.active');
            $(active).prev().find('a[data-toggle="tab"]').click();
        });

//        $('.choose_plan').click(function (event) {
//            $('.overturn').find('.front-flip').toggleClass('flipped');
//            $('.overturn').find('.rear-flip').toggleClass('flipped');
//            if($(event.target).closest('.well').find('h1').text().toLowerCase()=='free'){
//                $('.add_on').prop('disabled', true);
//            }
//        });

    });

    function nextTab() {
        var active = $('.wizard .nav-tabs li.active');
        active.next().removeClass('disabled');
        $(active).next().find('a[data-toggle="tab"]').click();

        let tot = $('#bitotal2').text();
        var total_amount = tot.replace(".", "");
        var total_amount = total_amount.replace(".", "");
        if(total_amount > 0){
            $('#use_pay').show();
            $("#add_proof").attr("name", "add_proof");
            $("#nominal").attr({value:formatMoney(total_amount), readonly:"readonly"} );
            // $("#add_proof").attr("required", "required");
        }
    }

    $('#planForm').unbind('submit').submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: "<?= site_url(). 'billing_portal/subscription/add_billing_temp/' ?>" +pid,
            type: 'POST',
            data: new FormData(this),
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function (data) {
                if(data){
                    nextTab();
                    setDataTemporary(data);
                }
            }
            
        });
        return false;
    });

    $(".checkout").click(function (e) {
        e.preventDefault();
        
        var renew = '<?= $this->uri->segment(4); ?>';
        if(renew == 'renew'){
            var action = "<?= site_url(). 'billing_portal/subscription/add_billing_invoice/renew' ?>";
        } else{
            var action = "<?= site_url(). 'billing_portal/subscription/add_billing_invoice' ?>";
        }

        let param = 'checkout';
        $('.'+param+'_modal').modal('show'); 

        $('#'+param+'_button').off('click').click(function(){ 
            $('#'+param+'_button').attr('disabled',false);
            $('.'+param+'_modal').modal('hide'); 
            var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
            var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';

            $.ajax({
                url: action,
                type: 'POST',
                data : {[csrfName] : csrfHash},
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function (data) {
                    if(data){
                        nextTab();
                        var disabled = $('.wizard .nav-tabs li').addClass('disabled');
                        setDataInvoice(data);
                    }
                }
            });
        });
    });
    
    $("#btnAddProof").click(function () {
        // e.preventDefault();
        $('#loader').show();
    });

    function choose_plan(id = null) {
        pid = id;
//        $('.rear-flip').css("position", "");
//        $('.front-flip').css("position", "absolute");
    }

    function setDataTemporary(data) {
        var price_items=0;
        var renew = '<?= $this->uri->segment(4); ?>';

        $('#c_item').html('');
        var changeplan = '<tr><td style="text-align:center; width:40px; vertical-align:middle;">1</td>';
            changeplan += '<td style="vertical-align:middle;"><?= lang('change_subscription_to'); ?> <span>'+data.billing.plan_name+'</span></td>';
            changeplan += '<td style="text-align:center; width:220px;">'+formatMoney(data.billing.price)+'</td>';
            changeplan += '<td style="text-align:center; width:120px;" >1</td>';
            changeplan += '<td style="text-align:right; width:120px;">'+formatMoney(data.billing.price)+'</td></tr>';
        $('#c_item').append(changeplan);

        if (data.item) {
            var i = 2;
            $.each(data.item, function () {
                var enhance = '<tr><td style="text-align:center; width:40px; vertical-align:middle;">' + i + '</td>';
                    enhance += '<td style="text-align:left;">'+ this.addon_name +'</td>';
                    enhance += '<td style="text-align:center;">'+ formatMoney(this.price) +' For 5 Item </td>';
                    enhance += '<td style="text-align:center;">'+ formatDecimal(this.quantity) +'</td>';
                    enhance += '<td style="text-align:right; width:120px;">' + formatMoney(this.subtotal) + '</td></tr>';
                $('#c_item').append(enhance);
                i++;
            });
        }

        var subtotal_bill = '<tr><td></td><td colspan="3" style="text-align:right;">Amount (<?= $default_currency->code; ?>)</td><td style="text-align:right; width:120px; font-weight:bold;">' + formatMoney(data.billing.subtotal) + '</td></tr>';
            subtotal_bill += '<tr><td></td><td colspan="3" style="vertical-align:middle;"> <b>Payment Period : </b>'+formatMoney(data.billing.subtotal)+' x '+ formatDecimal(data.billing.payment_period) +' Month</td><td style="text-align:right; width:120px;">' + formatMoney(data.billing.total) + '</td></tr>';
        $('#c_item').append(subtotal_bill);
        $('#c_total2').text(formatMoney(data.billing.total));
    }

    function setDataInvoice(data) {
        var price_items=0;
        var renew = '<?= $this->uri->segment(4); ?>';

        $('#biitem').html('');
        var changeplan = '<tr><td style="text-align:center; width:40px; vertical-align:middle;">1</td>';
            changeplan += '<td style="vertical-align:middle;"><?= lang('change_subscription_to'); ?> <span id="binameplan"></span></td>';
            changeplan += '<td style="text-align:center; width:220px;">'+formatMoney(data.billing.price)+'</td>';
            changeplan += '<td style="text-align:center; width:120px;" >1</td>';
            changeplan += '<td style="text-align:right; width:120px;" id="bitotal"></td></tr>';
            
        $('#biitem').append(changeplan);
        
        $('#bidate').text(data.billing.date);
        $('#biref').text(data.billing.reference_no);
        $('#bipayment').text(data.billing.payment_status);
        $('#binameplan').text(data.billing.plan_name);
        $('#payment').find('form').attr('action', url + data.billing.id);

        $('#bicompany').text(data.company[0].company);
        $('#biaddress').text(data.company[0].address);
        $('#bicity').text(data.company[0].city + ' ' + data.company[0].postal_code + ' ' + data.company[0].state);
        $('#bicountry').text(data.company[0].country);
        $('#bitel').text(data.company[0].phone);
        $('#biemail').text(data.company[0].email);

        $('#due_date').val(fld(data.billing.due_date));
        $('#bitotal').text(formatMoney(data.billing.price));

        if (data.item) {
            var i = 2;
            $.each(data.item, function () {
                var enhance = '<tr><td style="text-align:center; width:40px; vertical-align:middle;">' + i + '</td>';
                    enhance += '<td style="text-align:left;">'+ this.addon_name +'</td>';
                    enhance += '<td style="text-align:center;">'+ formatMoney(this.price) +' For 5 Item </td>';
                    enhance += '<td style="text-align:center;">'+ formatDecimal(this.quantity) +'</td>';
                    enhance += '<td style="text-align:right; width:120px;">' + formatMoney(this.subtotal) + '</td></tr>';
                $('#biitem').append(enhance);
                i++;
            });
        }

        var subtotal_bill = '<tr><td></td><td colspan="3" style="text-align:right;">Amount (<?= $default_currency->code; ?>)</td><td style="text-align:right; width:120px; font-weight:bold;">' + formatMoney(data.billing.subtotal) + '</td></tr>';
            subtotal_bill += '<tr><td></td><td colspan="3" style="vertical-align:middle;"> <b>Payment Period : </b>'+formatMoney(data.billing.subtotal)+' x '+ formatDecimal(data.billing.payment_period) +' Month</td><td style="text-align:right; width:120px;">' + formatMoney(data.billing.total) + '</td></tr>';

        $('#bibalance').text(formatMoney(data.billing.total));
        $('#biamount').text(formatMoney(0));
        $('#biitem').append(subtotal_bill);
        $('#bitotal2').text(formatMoney(data.billing.total));
        
    }
    $('input.number-only').bind('keypress', function (e) {
        return !(e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57) && e.which != 46);
    });
    
    $('#add_proof').change(function() {
        if(!$(this).val()){
            $('#btnAddProof').prop('disabled',true);
        }else {
            $('#btnAddProof').prop('disabled',false);
        }
    });
</script>
