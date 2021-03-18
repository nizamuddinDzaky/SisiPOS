<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('subscription'); ?></h4>
        </div>
        <div class="modal-body ui-front">
            <section>
                <div class="wizard">
                    <div class="wizard-inner">
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
                    </div>

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
                                        if ($plan->name != 'Free')
                                            echo '<h2>Call Us !</h2>';
                                        else
                                            echo '<h2>' . $default_currency->code . ' ' . $this->sma->formatMoney($plan->price) . '</h2>';
                                        ?>
                                        <ul class="list-unstyled">
                                            <li><?php echo ($plan->name != 'Free' ? 'Multi' : $plan->users) ?> Users</li>
                                            <li><?php echo ($plan->name != 'Free' ? 'Multi' : $plan->warehouses) ?> Warehouses</li>
                                            <li><?php echo ($plan->master ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>') . ' ' . lang('master_data') ?></li>
                                            <li><?php echo ($plan->pos ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>') . ' ' . lang('pos') ?></li>
                                            <li><?php echo ($plan->purchases ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>') . ' ' . lang('purchases') ?></li>
                                            <li><?php echo ($plan->sales ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>') . ' ' . lang('sales') ?></li>
                                            <li><?php echo ($plan->quotes ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>') . ' ' . lang('quotes') ?></li>
                                            <li><?php echo ($plan->expenses ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>') . ' ' . lang('expenses') ?></li>
                                            <li><?php echo ($plan->reports ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>') . ' ' . lang('reports') ?></li>
                                            <li><?php echo ($plan->transfers ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>') . ' ' . lang('transfers') ?></li>
                                        </ul>
                                <?= '<button type="button" class="btn btn-primary choose_plan next-step" style="width: 100%" onclick="choose_plan(' . $plan->id . ')" ' . (($plan->name == $current->plan_name) ? 'disabled' : '') . '>' . (($plan->name == $current->plan_name) ? 'Now' : 'Subscribe') . '</button>'; ?>
                                    </div>
                                </div>
                                <?php }$i++;} ?>
                            </div>
                        </div>
                        <div class="tab-pane" role="tabpanel" id="addons">
                            <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'planForm');
                            echo form_open("", $attrib); ?>
                            <div class="form-group">
                                <h3><label>Tambahan Add-on (optional):</label></h3>
                            </div>
                            <?php  foreach ($addons as $aon) { ?>
                            <div class="col-md-6">
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
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;"><i class="fa fa-plus-square-o"></i></div>
                                        <?= form_input('p_qty_'.$aon->id, NULL, 'class="form-control number-only add_on" id="p_qty_'.$aon->id.'"') ?>
                                    </div>
                                <?php } ?>
                                </div>
                            </div>
                            <?php } ?>
                            <div class="col-md-12">
                                <ul class="list-inline pull-right">
                                    <li><button type="button" class="btn btn-default prev-step"><?= lang('previous') ?></button></li>
                                    <!--<li><button type="button" class="btn btn-primary" onclick=""><?= lang('continue') ?></button></li>-->
                                    <li><?php echo form_submit('add_plan', lang('continue'), 'class="btn btn-primary"'); ?></li>
                                </ul>
                            </div>
                            <?php echo form_close(); ?>
                        </div>
                        <div class="tab-pane" role="tabpanel" id="invoice">
                            <div class="text-center" style="margin-bottom:20px;">
                                <img src="<?= avatar_image_logo($this->session->userdata('avatar'), $Settings->logo) ?>" height="150px">
                            </div>
                            <div class="well well-sm">
                                <div class="row bold">
                                    <div class="col-xs-5">
                                        <p class="bold">
                                            <?= lang("date"); ?>: <span id="bidate"></span><br>
                                            <?= lang("ref"); ?>: <span id="biref"></span><br>
                                            <?= lang("payment_status"); ?>: <span id="bipayment"></span>
                                        </p>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="clearfix"></div>
                            </div>

                            <div class="row" style="margin-bottom:15px;">
                                <div class="col-xs-6">
                                    <?php echo $this->lang->line("company"); ?>:
                                    <h2 style="margin-top:10px;" id="bicompany"></h2>
                                    <?= $billing ? ($row->company ? "" : "Attn: " . $row->name) : '' ?>
                                    <span id="biaddress"></span><br>
                                    <span id="bicity"></span><br>
                                    <span id="bicountry"></span><p></p>
                                        
                                    <?php // echo $row->address."<br>".$row->city." ".$row->postal_code." ".$row->state."<br>".$row->country; ?>

                                    <?php echo lang("tel") . ": <span id='bitel'>" . $row->phone . "</span><br>" . lang("email") . ": <span id='biemail'>" . $row->email . "</span>"; ?>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-striped print-table order-table">
                                    <thead>
                                        <tr>
                                            <th><?= lang("no"); ?></th>
                                            <th><?= lang("description"); ?></th>
                                            <th><?= lang("price"); ?></th>
                                        </tr>
                                    </thead>

                                    <tbody id="biitem">
                                        <tr>
                                            <td style="text-align:center; width:40px; vertical-align:middle;">1</td>
                                            <td style="vertical-align:middle;">
                                            <?= lang('upgrade_account_to'); ?> <span id="binameplan"></span>
                                            </td>
                                            <td style="text-align:right; width:120px;" id="bitotal"></td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <?php $col = 2; ?>
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
                                <li><button type="button" class="btn btn-default prev-step">Previous</button></li>
                                <li><button type="button" class="btn btn-primary next-step"><?= lang('continue') ?></button></li>
                            </ul>
                        </div>
                        <div class="tab-pane" role="tabpanel" id="payment">
                            <div class="col-md-12">
                                
                            </div>
                            <?php $attribute = array('data-toggle' => 'validator', 'role' => 'form');
                           echo form_open_multipart("auth/add_proof_payment/" . $billing->id, $attribute);
                            ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="bank_name"><?php echo $this->lang->line("bank_name"); ?></label>
                                        <?php echo form_input('bank_name', '', 'class="form-control" required="required"');?>
                                    </div>
                                    <div class="form-group">
                                        <label for="valid_no"><?php echo $this->lang->line("validation_no"); ?></label>
                                        <?php echo form_input('valid_no', '', 'class="form-control" required="required"');?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="account_name"><?php echo $this->lang->line("account_name"); ?></label>
                                        <?php echo form_input('account_name', '', 'class="form-control" required="required"');?>
                                    </div>
                                    <div class="form-group">
                                        <label for="account_number"><?php echo $this->lang->line("account_number"); ?></label>
                                        <?php echo form_input('account_number', '', 'class="form-control" required="required"');?>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <?= lang("image", "image") ?>
                                        <input id="add_proof" type="file" data-browse-label="<?= lang('browse'); ?>" name="add_proof" data-show-upload="false" data-show-preview="false" class="form-control file" accept="image/*">
                                        <span id="InfoImageBrand"><i style="color:red;"><sup><strong>*Recomended : </strong><?= "Width:" . $this->Settings->twidth . "px, Height:" . $this->Settings->theight . "px, Max File Size: 500" ?>Kb</sup></i></span>
                                    </div>
                                </div>
                            </div>
                            <ul class="list-inline pull-right">
                                <li><?php echo form_submit('add_payment', lang('submit'), 'class="btn btn-primary" id="btnAddProof"'); ?></li>
                                <!--<button type="button" class="btn btn-primary"><?= lang('submit') ?></button>-->
                            </ul>
                            <?php // echo form_close(); ?>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </section>

        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?=FORCAPOS_VERSION?>"></script>
<script type="text/javascript">
    var url = $('#payment').find('form').attr('action');
    var pid;
    $(document).ready(function () {
        $.ajax({
            type: "get", async: false,
            url: site.base_url + "auth/get_billing_invoice/",
            dataType: "json",
            success: function (data) {
                try {
                    var now=moment().format(site.dateFormats.js_sdate.toUpperCase());
                    var dueDate=fld(data.billing.due_date).split(' ');
                    if (data.billing && dueDate>now) {
                        nextTab();
                        var active_now = $('.wizard .nav-tabs li.active');
                        active_now.prev().addClass('disabled');
                        nextTab();
                        var active_now = $('.wizard .nav-tabs li.active');
                        active_now.prev().addClass('disabled');
                        setDataInvoice(data);
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
    }

    $('#planForm').unbind('submit').submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: 'auth/add_billing_invoice/'+pid,
            type: 'POST',
            data: new FormData(this),
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function (data) {
                if(data){
                    nextTab();
                    setDataInvoice(data);
                }
            }
            
        });
        return false;
    });

    function choose_plan(id = null) {
        pid = id;
//        $('.rear-flip').css("position", "");
//        $('.front-flip').css("position", "absolute");
    }

    function setDataInvoice(data) {
        var price_items=0;

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

        $('#bitotal').text(formatMoney(data.billing.price));

        if (data.item) {
            var i = 2;
            $.each(data.item, function () {
                var enhance = '<tr><td style="text-align:center; width:40px; vertical-align:middle;">' + i + '</td><td style="vertical-align:middle;">'+ formatDecimal(this.quantity) +'x '+ this.addon_name +'</td><td style="text-align:right; width:120px;">' + formatMoney(this.subtotal) + '</td></tr>';
                $('#biitem').append(enhance);
                i++;
            });
        }
        $('#bitotal2').text(formatMoney(data.billing.total));
        $('#biamount').text(formatMoney(data.billing.payment_amount ? data.billing.payment_amount : 0));
        $('#bibalance').text(formatMoney((data.billing.total) - data.billing.payment_amount));
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
<?= $modal_js ?>