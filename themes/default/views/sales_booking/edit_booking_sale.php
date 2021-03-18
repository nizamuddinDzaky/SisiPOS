<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript">
    var count = 1,
        an = 1,
        product_variant = 0,
        DT = <?= $Settings->default_tax_rate ?>,
        product_tax = 0,
        invoice_tax = 0,
        total_discount = 0,
        total = 0,
        allow_discount = <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? 1 : 0; ?>,
        tax_rates = <?php echo json_encode($tax_rates); ?>;
    //var audio_success = new Audio('<?= $assets ?>sounds/sound2.mp3');
    //var audio_error = new Audio('<?= $assets ?>sounds/sound3.mp3');
    $(document).ready(function() {
        <?php if ($inv) { ?>

            localStorage.setItem('sldate', '<?= $this->sma->hrld($inv->date) ?>');
            localStorage.setItem('slcustomer', '<?= $inv->customer_id ?>');
            localStorage.setItem('slbiller', '<?= $inv->biller_id ?>');
            localStorage.setItem('slcharge', '<?= $inv->charge ?>');
            localStorage.setItem('slreason', '<?= str_replace(array("\r", "\n"), "", $this->sma->decode_html($inv->reason)); ?>');
            localStorage.setItem('slref', '<?= $inv->reference_no ?>');
            localStorage.setItem('slwarehouse', '<?= $customer_warehouse ?>');
            localStorage.setItem('slprice_type', '<?= $inv->price_type ?>');
            localStorage.setItem('slsale_status', '<?= $inv->sale_status ?>');
            localStorage.setItem('slpayment_status', '<?= $inv->payment_status ?>');
            localStorage.setItem('slpayment_term', '<?= $inv->payment_term ?>');
            localStorage.setItem('slnote', '<?= str_replace(array("\r", "\n"), "", $this->sma->decode_html($inv->note)); ?>');
            localStorage.setItem('slinnote', '<?= str_replace(array("\r", "\n"), "", $this->sma->decode_html($inv->staff_note)); ?>');
            localStorage.setItem('sldiscount', '<?= $inv->order_discount_id ?>');
            localStorage.setItem('sltax2', '<?= $inv->order_tax_id ?>');
            localStorage.setItem('slshipping', '<?= $inv->shipping ?>');
            localStorage.setItem('slitems', JSON.stringify(<?= $inv_items; ?>));
        <?php } ?>
        <?php if ($Owner || $Admin) { ?>
            $(document).on('change', '#sldate', function(e) {
                localStorage.setItem('sldate', $(this).val());
            });
            if (sldate = localStorage.getItem('sldate')) {
                $('#sldate').val(sldate);
            }
        <?php } ?>
        $(document).on('change', '#slbiller', function(e) {
            localStorage.setItem('slbiller', $(this).val());
        });
        if (slbiller = localStorage.getItem('slbiller')) {
            $('#slbiller').val(slbiller);
        }
        ItemnTotals();
        $("#add_item").autocomplete({
            source: function(request, response) {
                if (!$('#slcustomer').val()) {
                    $('#add_item').val('').removeClass('ui-autocomplete-loading');
                    bootbox.alert('<?= lang('select_above'); ?>');
                    $('#add_item').focus();
                    return false;
                }
                $.ajax({
                    type: 'get',
                    url: '<?= site_url('sales/suggestions'); ?>',
                    dataType: "json",
                    data: {
                        term: request.term,
                        warehouse_id: $("#slwarehouse").val(),
                        customer_id: $("#slcustomer").val()
                    },
                    success: function(data) {
                        response(data);
                    }
                });
            },
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function(event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function() {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                } else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                } else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function() {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');

                }
            },
            select: function(event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_invoice_item(ui.item);
                    if (row)
                        $(this).val('');
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });

        $(window).bind('beforeunload', function(e) {
            localStorage.setItem('remove_slls', true);
            if (count > 1) {
                var message = "You will loss data!";
                return message;
            }
        });
        $('#reset').click(function(e) {
            $(window).unbind('beforeunload');
        });


        $.ajax({
            type: 'get',
            url: '<?= site_url('auth/check_expired'); ?>',
            dataType: "json",
            success: function(data) {
                var now = moment().format(site.dateFormats.js_sdate.toUpperCase());
                var expDate = fld(data.authorized.expired_date).split(' ');
                if (data.authorized.plan_name != 'Free' && (expDate[0] < now)) {
                    $('#expModal').modal({
                        remote: site.base_url + 'welcome/exp_account/',
                        backdrop: 'static'
                    });
                    $('#expModal').modal('show');
                } else if (data.total_trx >= 100) {
                    $('#expModal').modal({
                        remote: site.base_url + 'welcome/exp_account/',
                        backdrop: 'static'
                    });
                    $('#expModal').modal('show');
                }
            }
        });


    });

    $(window).on('load', function() {
        var saleStatus = $('#slsale_status').val();
        $('#slcharge').change(function() {
            let val = $(this).val();
            if (saleStatus === "pending") {
                if (val != 0) {
                    $('#slsale_status').val('pending').change();
                    $('#slsale_status').attr('readonly', 'readonly')
                    $('#notif_status').show()
                    $('.reason_note').show()
                    $('.charge_reason').show()
                    $('.cancel_reason').hide()
                } else {
                    $('#slsale_status').removeAttr('readonly');
                    $('#notif_status').hide()
                    $('.reason_note').hide()
                    $('.charge_reason').hide()
                    $('.cancel_reason').show()
                }
            }
        }).change();
        $('#slsale_status').change(function() {
            let status = $(this).val();
            let charge = $('#slcharge').val();
            if (status === "canceled" || charge != 0) {
                $('.reason_note').show()
            } else {
                $('.reason_note').hide()
            }
        }).change();

        $(document).on('change', '#sltype', function(e) {
            let sltype = $('#sltype').val();
            if (sltype == 'booking') {
                $('#select2-chosen-4').text('<?= lang('reserved') ?>');
                var opt = '<option value="reserved" selected ><?= lang('reserved') ?></option>';
                opt += '<option value="pending"><?= lang('pending') ?></option>';
                $('#slsale_status').html(opt);
            } else {
                $('#select2-chosen-4').text('<?= lang('completed') ?>');
                var opt = '<option value="completed" selected><?= lang('completed') ?></option>';
                opt += '<option value="pending"><?= lang('pending') ?></option>';
                $('#slsale_status').html(opt);
            }
        });


        var filter_html_version = '<?= getenv('FORCAPOS_VERSION') ?>';
        var filter_html = {
            /*             sb_reference_no : {
                            field : "reference_no",
                            label : "<?= lang("reference_no"); ?>",
                            status:1
                        }, */
            sb_remaining_credit_limit: {
                field: "remaining_credit_limit",
                label: "<?= lang("remaining_credit_limit"); ?>",
                status: 1
            },
            sb_order_tax: {
                field: "order_tax",
                label: "<?= lang("order_tax"); ?>",
                status: 1
            },
            sb_order_discount: {
                field: "order_discount",
                label: "<?= lang("order_discount"); ?>",
                status: 1
            },
            sb_shipping_Price: {
                field: "shipping_Price",
                label: "<?= lang("Shipping_Price"); ?>",
                status: 1
            },
            sb_document: {
                field: "document",
                label: "<?= lang("document") ?>",
                status: 1
            },
            sb_payment_term: {
                field: "payment_term",
                label: "<?= lang("payment_term"); ?>",
                status: 1
            },
            sb_reason: {
                field: "reason",
                label: "<?= lang("Charge reason"); ?> <?= lang("Canceled reason"); ?>",
                status: 1
            },
            sb_sale_note: {
                field: "sale_note",
                label: "<?= lang("sale_note"); ?>",
                status: 1
            },
            sb_staff_note: {
                field: "staff_note",
                label: "<?= lang("staff_note"); ?>",
                status: 1
            },
            sb_create_delivery: {
                field: "create_delivery",
                label: "<?= lang("create_delivery"); ?>",
                status: 1
            }
        };

        var filter_add_sales_booking = JSON.parse(localStorage.getItem('filter_add_sales_booking'));
        var filter_html_version_local = localStorage.getItem('filter_add_sales_booking_version');
        if ((filter_add_sales_booking == null || Object.keys(filter_add_sales_booking).length != Object.keys(filter_html).length) || (filter_html_version_local == null || filter_html_version != filter_html_version_local)) {
            localStorage.setItem('filter_add_sales_booking', JSON.stringify(filter_html));
            localStorage.setItem('filter_add_sales_booking_version', filter_html_version);
        }

        filter_html = JSON.parse(localStorage.getItem('filter_add_sales_booking'));
        var field_filter_key = Object.keys(filter_html);

        for (var i = 0; i < field_filter_key.length; i++) {
            var check = filter_html[field_filter_key[i]];
            if (check.status == 1) {
                $(`#${field_filter_key[i]}`).iCheck('check');
                $("#display_input_" + filter_html[field_filter_key[i]].field).show();
            } else {
                $(`#${field_filter_key[i]}`).iCheck('uncheck');
                $("#display_input_" + filter_html[field_filter_key[i]].field).hide();
            }
        }

        $(document).on('ifChecked', '.check_filter_html', function(event) {
            filter_html[this.id].status = 1;
            if (!($('#slsale_status').val() == "pending" && this.id == "sb_create_delivery"))
                $("#display_input_" + filter_html[this.id].field).show();
            localStorage.setItem('filter_add_sales_booking', JSON.stringify(filter_html));
        });

        $(document).on('ifUnchecked', '.check_filter_html', function(event) {
            filter_html[this.id].status = 0;
            $("#display_input_" + filter_html[this.id].field).hide();
            localStorage.setItem('filter_add_sales_booking', JSON.stringify(filter_html));
        });

        $('#slsale_status').change();
    });
</script>
<?php
$disabled = $inv->client_id == 'aksestoko' || $inv->client_id == 'atl' ? 'disabled' : '';
$readonly = $inv->client_id == 'aksestoko' || $inv->client_id == 'atl' ? 'readonly' : '';
$hidden = $inv->client_id == 'aksestoko' || $inv->client_id == 'atl' ? 'hidden' : '';
// $disabledWarehouse = 
?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('edit_booking_sale'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <?php echo anchor($mb_add_booking_sale, '<i class="icon fa fa-book tip" data-placement="left" title="' . lang("manual_book") . '"></i> ', 'target="_blank"') ?>

                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-columns tip" data-placement="left" title="<?= lang("column_filter") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel" style="height: 250px; overflow-y: scroll;">
                        <!-- <li>
                            <a href="javascript:void(0)">
                                <label for="sb_reference_no" style="width: 100%;">
                                    <input id="sb_reference_no" class="checkbox check_filter_html" type="checkbox" checked/> <?= lang("reference_no", "slref"); ?>
                                </label>
                            </a>
                        </li> -->
                        <li>
                            <a href="javascript:void(0)">
                                <label for="sb_remaining_credit_limit" style="width: 100%;">
                                    <input id="sb_remaining_credit_limit" class="checkbox check_filter_html" type="checkbox" checked /> <?= lang("remaining_credit_limit", "slkredit"); ?>
                                </label>
                            </a>
                        </li>

                        <!-- <li>
                            <a href="javascript:void(0)">
                                <label for="sb_product_name" style="width: 100%;">
                                    <input id="sb_product_name" class="checkbox check_filter_datatable" type="checkbox" checked/> <?= lang("product_name") . " (" . lang("product_code") . ")"; ?>
                                </label>
                            </a>
                        </li>
                        <?php if ($Settings->product_serial) { ?>
                            <li>
                                <a href="javascript:void(0)">
                                    <label for="sb_serial_no" style="width: 100%;">
                                        <input id="sb_serial_no" class="checkbox check_filter_datatable" type="checkbox" checked/> <?= lang("serial_no") ?>
                                    </label>
                                </a>
                            </li>
                        <?php } ?>
                        <li>
                            <a href="javascript:void(0)">
                                <label for="sb_net_unit_price" style="width: 100%;">
                                    <input id="sb_net_unit_price" class="checkbox check_filter_datatable" type="checkbox" checked/> <?= lang("net_unit_price"); ?>
                                </label>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <label for="sb_quantity" style="width: 100%;">
                                    <input id="sb_quantity" class="checkbox check_filter_datatable" type="checkbox" checked/> <?= lang("quantity"); ?>
                                </label>
                            </a>
                        </li>
                        <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) { ?>
                            <li>
                                <a href="javascript:void(0)">
                                    <label for="sb_discount" style="width: 100%;">
                                        <input id="sb_discount" class="checkbox check_filter_datatable" type="checkbox" checked/> <?= lang("discount"); ?>
                                    </label>
                                </a>
                            </li>
                        <?php } ?>
                        <?php if ($Settings->tax1) { ?>
                            <li>
                                <a href="javascript:void(0)">
                                    <label for="sb_product_tax" style="width: 100%;">
                                        <input id="sb_product_tax" class="checkbox check_filter_datatable" type="checkbox" checked/> <?= lang("product_tax"); ?>
                                    </label>
                                </a>
                            </li>
                        <?php } ?>
                        <li>
                            <a href="javascript:void(0)">
                                <label for="sb_subtotal" style="width: 100%;">
                                    <input id="sb_subtotal" class="checkbox check_filter_datatable" type="checkbox" checked/> <?= lang("subtotal"); ?>
                                </label>
                            </a>
                        </li> -->

                        <?php if ($Settings->tax2) { ?>
                            <li>
                                <a href="javascript:void(0)">
                                    <label for="sb_order_tax" style="width: 100%;">
                                        <input id="sb_order_tax" class="checkbox check_filter_html" type="checkbox" checked /> <?= lang("order_tax", "sltax2"); ?>
                                    </label>
                                </a>
                            </li>
                        <?php } ?>

                        <li>
                            <a href="javascript:void(0)">
                                <label for="sb_order_discount" style="width: 100%;">
                                    <input id="sb_order_discount" class="checkbox check_filter_html" type="checkbox" checked /> <?= lang("order_discount", "sldiscount"); ?>
                                </label>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <label for="sb_shipping_Price" style="width: 100%;">
                                    <input id="sb_shipping_Price" class="checkbox check_filter_html" type="checkbox" checked /> <?= lang("Shipping_Price", "slshipping"); ?>
                                </label>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <label for="sb_document" style="width: 100%;">
                                    <input id="sb_document" class="checkbox check_filter_html" type="checkbox" checked /> <?= lang("document", "document") ?>
                                </label>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <label for="sb_payment_term" style="width: 100%;">
                                    <input id="sb_payment_term" class="checkbox check_filter_html" type="checkbox" checked /> <?= lang("payment_term", "slpayment_term"); ?>
                                </label>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <label for="sb_sale_note" style="width: 100%;">
                                    <input id="sb_sale_note" class="checkbox check_filter_html" type="checkbox" checked /> <?= lang("sale_note", "slnote"); ?>
                                </label>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <label for="sb_reason" style="width: 100%;">
                                    <input id="sb_reason" class="checkbox check_filter_html" type="checkbox" checked /> <?= lang("Charge reason", "slreason"); ?> <?= lang("Canceled reason", "slreason"); ?>
                                </label>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <label for="sb_staff_note" style="width: 100%;">
                                    <input id="sb_staff_note" class="checkbox check_filter_html" type="checkbox" checked /> <?= lang("staff_note", "slinnote"); ?>
                                </label>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <label for="sb_create_delivery" style="width: 100%;">
                                    <input id="sb_create_delivery" class="checkbox check_filter_html" type="checkbox" checked /> <?= lang("create_delivery"); ?>
                                </label>
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

                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'class' => 'edit-so-form', 'id' => 'formfield-edit');
                echo form_open_multipart("sales_booking/edit_booking_sale/" . $inv->id, $attrib)
                ?>


                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("date", "sldate"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : $this->sma->hrld($inv->date)), 'class="form-control input-tip datetime" ' . $readonly . ' id="sldate" required="required"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-md-4" id="display_input_reference_no">
                            <div class="form-group">
                                <?= lang("reference_no", "slref"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control input-tip" ' . $readonly . ' id="slref" required="required"'); ?>
                            </div>
                        </div>
                        <?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("biller", "slbiller"); ?>
                                    <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $inv->biller_id), 'id="slbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } else {
                            $biller_input = array(
                                'type' => 'hidden',
                                'name' => 'biller',
                                'id' => 'slbiller',
                                'value' => $this->session->userdata('biller_id'),
                            );
                            echo form_input($biller_input);
                        } ?>

                        <div class="clearfix"></div>
                        <div class="col-md-12">
                            <div class="panel panel-warning">
                                <div class="panel-heading"><?= lang('please_select_these_before_adding_product') ?></div>
                                <div class="panel-body" style="padding: 5px;">

                                    <?php if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) { ?>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang("warehouse", "slwarehouse"); ?>
                                                <?php
                                                $wh[''] = '';
                                                foreach ($warehouses as $warehouse) {
                                                    $wh[$warehouse->id] = $warehouse->name.'|'.$warehouse->code.'|'.$warehouse->address;
                                                }
                                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $customer_warehouse), 'id="slwarehouse" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("warehouse") . '" required="required" style="width:100%;" ' . ($inv->sale_status != 'pending' ? 'disabled"' : '') . '');
                                                ?>
                                            </div>
                                        </div>
                                    <?php } else {
                                        $warehouse_input = array(
                                            'type' => 'hidden',
                                            'name' => 'warehouse',
                                            'id' => 'slwarehouse',
                                            'value' => $this->session->userdata('warehouse_id'),
                                        );
                                        echo form_input($warehouse_input);
                                    } ?>

                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <?= lang("customer", "slcustomer"); ?> <span><i class="icon fa fa-info-circle tip" data-toggle="tooltip" data-placement="right" title="<?= lang('percarian_toko_dan_orang') ?>"></i></span>
                                            <div class="input-group">
                                                <?php
                                                echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'id="slcustomer" data-placeholder="' . lang("select") . ' ' . lang("customer") . '" required="required" class="form-control input-tip" style="width:100%;" ' . $readonly . '');
                                                ?>
                                                <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                                    <a href="javascript:void(0)" id="<?= $inv->client_id == 'aksestoko' || $inv->client_id == 'atl' ? '' : 'removeReadonly' ?>">
                                                        <i class="fa fa-unlock" id="unLock"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3" id="display_input_remaining_credit_limit">
                                        <div class="form-group">
                                            <?= lang("remaining_credit_limit", "slkredit"); ?>
                                            <?php echo form_input('kredit_limit', '0', 'disabled class="form-control tip" id="slkredit"'); ?>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 <?= $inv->client_id != 'aksestoko' ? '' : 'hidden'?>">
                                        <div class="form-group">
                                            <?= lang("price_type", "slprice_type"); ?> *
                                            <div class="input-group col-md-12">
                                                <div class="col-md-6">
                                                    <input type="radio" name="price_type" value="cash" <?= (is_null($inv->price_type) || $inv->price_type=='cash') ? 'checked' : '' ?> > Cash 
                                                </div>
                                                <div class="col-md-6">
                                                    <input type="radio" name="price_type" value="credit" <?= $inv->price_type=='credit' ? 'checked' : '' ?> > Credit
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?= form_hidden('sale_type', 'booking'); ?>
                                </div>
                            </div>

                        </div>

                        <div class="col-md-12" id="sticker">
                            <div class="well well-sm">
                                <div class="form-group" style="margin-bottom:0;">
                                    <div class="input-group wide-tip">
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <i class="fa fa-2x fa-barcode addIcon"></i></a></div>

                                        <?php echo form_input('add_item', '', 'class="form-control input-lg" ' . $disabled . ' id="add_item" placeholder="' . lang("add_product_to_order") . '" '); ?>
                                        <!-- <?php if ($Owner || $Admin || $GP['products-add']) { ?>
                                            <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                                <a href="#" id="addManually" class="<?= $inv->client_id == 'aksestoko' ? 'hide' : '' ?>">
                                                    <i class="fa fa-2x fa-plus-circle addIcon" id="addIcon"></i>
                                                </a>
                                            </div>
                                        <?php } ?> -->
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("order_items"); ?> *</label>

                                <div class="controls table-controls">
                                    <table id="slTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                        <thead>
                                            <tr>
                                                <th class="col-md-4"><?= lang("product_name") . " (" . lang("product_code") . ")"; ?></th>
                                                <?php
                                                if ($Settings->product_serial) {
                                                    echo '<th class="col-md-2">' . lang("serial_no") . '</th>';
                                                }
                                                ?>
                                                <th class="col-md-1"><?= lang("net_unit_price"); ?></th>
                                                <th class="col-md-1"><?= lang("remaining_stock"); ?> <span><i class="icon fa fa-info-circle tip" data-toggle="tooltip" data-placement="right" title="Quantity in Selected Warehouse"></i></span></th>
                                                <th class="col-md-1"><?= lang("quantity"); ?></th>
                                                <?php
                                                if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount') || $inv->product_discount)) {
                                                    echo '<th class="col-md-1">' . lang("discount") . '</th>';
                                                }
                                                ?>
                                                <?php
                                                if ($Settings->tax1) {
                                                    echo '<th class="col-md-1">' . lang("product_tax") . '</th>';
                                                }
                                                ?>
                                                <th><?= lang("subtotal"); ?> (<span class="currency"><?= $default_currency->code ?></span>)
                                                </th>
                                                <th style="width: 30px !important; text-align: center;"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot></tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <?php if ($Settings->tax2) { ?>
                                <div class="col-md-4" id="display_input_order_tax">
                                    <div class="form-group">
                                        <?= lang("order_tax", "sltax2"); ?>
                                        <?php
                                        $tr[""] = "";
                                        foreach ($tax_rates as $tax) {
                                            $tr[$tax->id] = $tax->name;
                                        }
                                        echo form_dropdown('order_tax', $tr, (isset($_POST['order_tax']) ? $_POST['order_tax'] : $Settings->default_tax_rate2), 'id="sltax2" data-placeholder="' . lang("select") . ' ' . lang("order_tax") . '" class="form-control input-tip select" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if (($Owner || $Admin || $this->session->userdata('allow_discount')) || $inv->order_discount_id) { ?>
                                <?php if ($inv->client_id == 'aksestoko') { ?>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <?= lang("Charge", "slcharge"); ?>
                                            <?php echo form_input('charge', (float) $inv->charge, 'class="form-control tip" data-trigger="focus" data-placement="top" title="' . lang('Charge') . '" id="slcharge" ' . ($inv->sale_status != 'pending' ? 'readonly="true"' : '') . ''); ?>

                                        </div>
                                    </div>
                                <?php } ?>
                                <div class="col-md-4" id="display_input_order_discount">
                                    <div class="form-group">
                                        <?= lang("order_discount", "sldiscount"); ?>
                                        <?php echo form_input('order_discount', (float) $inv->order_discount, 'class="form-control input-tip" id="sldiscount" ' . ((($Owner || $Admin || $this->session->userdata('allow_discount')) && $inv->client_id != 'aksestoko') ? '' : 'readonly="true"')); ?>
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="col-md-4" id="display_input_shipping_Price">
                                <div class="form-group">
                                    <?= lang("shipping", "slshipping"); ?>
                                    <?php echo form_input('shipping', (int) $inv->shipping, 'class="form-control input-tip" id="slshipping" ' . ($inv->client_id != 'aksestoko' ? '' : 'readonly="true"') . ''); ?>

                                </div>
                            </div>

                            <div class="col-md-4" id="display_input_document">
                                <div class="form-group">
                                    <?= lang("document", "document") ?>
                                    <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false" data-show-preview="false" class="form-control file">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <?= lang("sale_status", "slsale_status"); ?>
                                    <?php
                                    if ($inv->client_id == 'aksestoko') {
                                        $sst['pending'] = lang('pending');
                                        $sst['reserved'] = lang('reserved');
                                        $sst['confirmed'] = lang('confirmed');
                                        $sst['canceled'] = lang('canceled');

                                    } else if ($inv->client_id == 'atl') {
                                        $sst['pending'] = lang('pending');
                                        $sst['reserved'] = lang('reserved');
                                        $sst['canceled'] = lang('canceled');

                                    } else {
                                        $sst['pending'] = lang('pending');
                                        $sst['reserved'] = lang('reserved');
                                    }
                                    echo form_dropdown('sale_status', $sst, '', 'class="form-control input-tip" required="required" id="slsale_status"');
                                    ?>
                                    <small class="text-danger" style="display: none" id="notif_status"> <i>If `Charge` field value is not zero, you can not change sale status </i></small>
                                </div>
                            </div>
                            <div class="col-sm-4" id="display_input_payment_term">
                                <div class="form-group">
                                    <?= lang("payment_term", "slpayment_term"); ?>
                                    <div class="input-group">
                                        <?php echo form_dropdown('payment_term', $top, $inv->payment_term, 'class="form-control input-tip" id="slpayment_term"'); ?>
                                        <?php echo form_input('payment_term', $inv->payment_term, 'class="form-control input-tip"  data-trigger="focus" data-placement="top" title="'.lang('payment_term_tip').'" id="slpayment_term_m"'); ?>
                                        <div class="input-group-addon no-print" style="padding: 2px 8px; border-left: 0;">
                                            <a id="slpayment_term_check" class="external">
                                                <i class="fa fa-pencil" style="font-size: 1.2em;"></i>
                                            </a>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <?= form_hidden('payment_status', $inv->payment_status); ?>
                            <div class="clearfix"></div>
                        </div>

                        <input type="hidden" name="total_items" value="" id="total_items" required="required" />

                        <div class="row" id="bt">
                            <div class="col-md-12">
                                <?php if ($inv->client_id == 'aksestoko') { ?>
                                    <div id="display_input_reason" class="col-md-12 reason_note" style="display: none">
                                        <div class="form-group">
                                            <span class="charge_reason"><?= lang("Charge reason", "slreason"); ?></span>
                                            <span class="cancel_reason"><?= lang("Canceled reason", "slreason"); ?></span>
                                            <?php echo form_textarea('reason', ($_POST['reason']), 'class="form-control" id="slreason" style="margin-top: 10px; height: 100px;"'); ?>
                                        </div>
                                    </div>
                                <?php } ?>
                                <div class="col-md-6" id="display_input_sale_note">
                                    <div class="form-group">
                                        <?= lang("sale_note", "slnote"); ?>
                                        <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="slnote" style="margin-top: 10px; height: 100px;"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-6" id="display_input_staff_note">
                                    <div class="form-group">
                                        <?= lang("staff_note", "slinnote"); ?>
                                        <?php echo form_textarea('staff_note', (isset($_POST['staff_note']) ? $_POST['staff_note'] : ""), 'class="form-control" id="slinnote" style="margin-top: 10px; height: 100px;"'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row" id="display_input_create_delivery">
                            <div class="col-md-12">
                                <div class="form-group" style="padding-left: 20px;">
                                    <label for="create_delivery" style="width: 100%;">
                                        <input id="create_delivery" class="checkbox" type="checkbox" name="create_delivery" /> <?= lang("create_delivery"); ?>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="fprom-group"><?php echo form_button('edit_booking_sale', lang("submit"), 'id="edit_booking_sale" class="btn btn-primary"  style="padding: 6px 15px; margin:15px 0;"'); ?>
                                <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="bottom-total" class="well well-sm" style="margin-bottom: 0;">
                    <table class="table table-bordered table-condensed totals" style="margin-bottom:0;">
                        <tr class="warning">
                            <td><?= lang('items') ?> <span class="totals_val pull-right" id="titems">0</span></td>
                            <td><?= lang('total') ?> <span class="totals_val pull-right" id="total">0.00</span></td>
                            <td><?= lang('Charge') ?> <span class="totals_val pull-right" id="charge">0.00</span></td>
                            <?php if (($Owner || $Admin || $this->session->userdata('allow_discount')) || $inv->total_discount) { ?>
                                <td><?= lang('order_discount') ?> <span class="totals_val pull-right" id="tds">0.00</span></td>
                            <?php } ?>
                            <?php if ($Settings->tax2) { ?>
                                <td><?= lang('order_tax') ?> <span class="totals_val pull-right" id="ttax2">0.00</span></td>
                            <?php } ?>
                            <td><?= lang('shipping') ?> <span class="totals_val pull-right" id="tship">0.00</span></td>
                            <td><?= lang('grand_total') ?> <span class="totals_val pull-right" id="gtotal">0.00</span></td>
                        </tr>
                    </table>
                </div>

                <?php echo form_close(); ?>

            </div>

        </div>
    </div>
</div>

<div class="modal" id="prModal" tabindex="-1" role="dialog" aria-labelledby="prModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-2x">&times;</i></span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="prModalLabel"></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                    <?php if ($Settings->tax1) { ?>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?= lang('product_tax') ?></label>
                            <div class="col-sm-8">
                                <?php
                                $tr[""] = "";
                                foreach ($tax_rates as $tax) {
                                    $tr[$tax->id] = $tax->name;
                                }
                                echo form_dropdown('ptax', $tr, "", 'id="ptax" class="form-control pos-input-tip" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="<?=$hidden?>">
                        <?php if ($Settings->product_serial) { ?>
                            <div class="form-group">
                                <label for="pserial" class="col-sm-4 control-label"><?= lang('serial_no') ?></label>

                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="pserial">
                                </div>
                            </div>
                        <?php } ?>
                        <div class="form-group">
                            <label for="pquantity" class="col-sm-4 control-label"><?= lang('quantity') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pquantity">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="punit" class="col-sm-4 control-label"><?= lang('product_unit') ?></label>
                            <div class="col-sm-8">
                                <div id="punits-div"></div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="poption" class="col-sm-4 control-label"><?= lang('product_option') ?></label>
                        <div class="col-sm-8">
                            <div id="poptions-div"></div>
                        </div>
                    </div>
                    <?php if ($Settings->product_discount) { ?>
                        <div class="form-group">
                            <label for="pdiscount" class="col-sm-4 control-label"><?= lang('product_discount') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pdiscount" <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? '' : 'readonly="true"'; ?>>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="pprice" class="col-sm-4 control-label"><?= lang('unit_price') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pprice" <?= ($Owner || $Admin || $GP['edit_price']) ? '' : 'readonly'; ?>>
                        </div>
                    </div>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?= lang('net_unit_price'); ?></th>
                            <th style="width:25%;"><span id="net_price"></span></th>
                            <th style="width:25%;"><?= lang('product_tax'); ?></th>
                            <th style="width:25%;"><span id="pro_tax"></span></th>
                        </tr>
                    </table>
                    <input type="hidden" id="punit_price" value="" />
                    <input type="hidden" id="old_tax" value="" />
                    <input type="hidden" id="old_qty" value="" />
                    <input type="hidden" id="old_price" value="" />
                    <input type="hidden" id="row_id" value="" />
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editItem"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="mModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-2x">&times;</i></span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="mModalLabel"><?= lang('add_product_manually') ?></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                    <div class="form-group">
                        <label for="mcode" class="col-sm-4 control-label"><?= lang('product_code') ?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mcode">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mname" class="col-sm-4 control-label"><?= lang('product_name') ?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mname">
                        </div>
                    </div>
                    <?php if ($Settings->tax1) { ?>
                        <div class="form-group">
                            <label for="mtax" class="col-sm-4 control-label"><?= lang('product_tax') ?> *</label>

                            <div class="col-sm-8">
                                <?php
                                $tr[""] = "";
                                foreach ($tax_rates as $tax) {
                                    $tr[$tax->id] = $tax->name;
                                }
                                echo form_dropdown('mtax', $tr, "", 'id="mtax" class="form-control input-tip select" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="mquantity" class="col-sm-4 control-label"><?= lang('quantity') ?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mquantity">
                        </div>
                    </div>
                    <?php if ($Settings->product_serial) { ?>
                        <div class="form-group">
                            <label for="mserial" class="col-sm-4 control-label"><?= lang('product_serial') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="mserial">
                            </div>
                        </div>
                    <?php } ?>
                    <?php if ($Settings->product_discount) { ?>
                        <div class="form-group">
                            <label for="mdiscount" class="col-sm-4 control-label">
                                <?= lang('product_discount') ?>
                            </label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="mdiscount" <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? '' : 'readonly="true"'; ?>>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="mprice" class="col-sm-4 control-label"><?= lang('unit_price') ?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mprice">
                        </div>
                    </div>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?= lang('net_unit_price'); ?></th>
                            <th style="width:25%;"><span id="mnet_price"></span></th>
                            <th style="width:25%;"><?= lang('product_tax'); ?></th>
                            <th style="width:25%;"><span id="mpro_tax"></span></th>

                        </tr>
                    </table>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="addItemManually"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal bootbox  fade bootbox-confirm in" id="modal-confirm" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-large">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="bootbox-close-button close" data-dismiss="modal" aria-hidden="true" style="margin-top: -10px;">
                    <i class="fa fa-2x">×</i></button>
                <br>
                <div class="bootbox-body">
                    <div id="bookingnotif" style="text-align: center;">
                        <?= "quantity realstock kurang";  ?>
                    </div><br>
                    <div id="creditlimit" style="text-align: center;">
                        <?= lang('notif_credit_limit'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" aria-hidden="true">Cancel</button>
                <button type="button" class="btn btn-primary" id="submit-sale" onclick="go()">Ok</button>
            </div>
        </div>
    </div>

    <div class="modal fade in" id="expModal" tabindex="-1" role="dialog" aria-labelledby="expModalLabel" aria-hidden="true"></div>

    <script type="text/javascript">
        function go() {
            $('input[name="price_type"]').removeAttr("disabled");
            document.getElementById('formfield-edit').submit();
        };

        <?php if ($inv->client_id == 'aksestoko' || $inv->client_id == 'atl') { ?>
        function refreshProduct() {
            let products = JSON.parse(localStorage.getItem('slitems'));
            for(let [key, value] of Object.entries(products)){
                $.ajax({
                    type: 'get',
                    url: '<?= site_url('sales/suggestions'); ?>',
                    dataType: "json",
                    data: {
                        term: value.label,
                        warehouse_id: $("#slwarehouse").val(),
                        customer_id: $("#slcustomer").val()
                    },
                    success: function(data) {
                        if (data[0].id === 0) {
                            slitems[key].row.qty_wh = "0";
                            slitems[key].row.qty_book_wh = "0";
                        } else {
                            slitems[key].row.qty_wh = data[0].row.qty_wh;
                            slitems[key].row.qty_book_wh = data[0].row.qty_book_wh;
                        }
                        $('#slwarehouse').select2("readonly", false);
                        localStorage.setItem('slitems', JSON.stringify(slitems));
			            loadItems();
                    }
                });
            }
        }
        <?php } ?>

        let pay_term = '<?= (int)$inv->payment_term ?>';
        let arr_pay_term = '<?= json_encode($pay_term) ?>';
        $(document).ready(function() {
            $('#slsale_status').change(function() {
                $('#display_input_create_delivery').iCheck('uncheck');
                var filter_add_sales_booking = JSON.parse(localStorage.getItem('filter_add_sales_booking'));

                if (this.value == 'reserved' && filter_add_sales_booking['sb_create_delivery'].status == 1) {
                    $('#display_input_create_delivery').show();
                } else {
                    $('#display_input_create_delivery').hide();
                }
            });
        
        
            $('#slwarehouse').change(function() {

                <?php if ($inv->client_id == 'aksestoko' || $inv->client_id == 'atl') { ?>
                    if(typeof slitems !== 'undefined') {
                        refreshProduct();
                    }
                <?php } ?> 

                <?php if (!($inv->client_id == 'aksestoko' || $inv->client_id == 'atl')) { ?>
                $('#modal-loading').show();
                
                $('#slcustomer').val('');
                $('#slkredit').val('');
            
                $('#slcustomer').select2({
                    minimumInputLength: 1,
                    ajax: {
                        url: `<?= base_url("customers/suggestions") ?>`,
                        dataType: 'json',
                        quietMillis: 15,
                        data: function(term, page) {
                            return {
                                term: term,
                                limit: 10,
                                warehouse_id: $('#slwarehouse').val()
                            };
                        },
                        results: function(data, page) {
                            if (data.results != null) {
                                return {
                                    results: data.results
                                };
                            } else {
                                return {
                                    results: [{
                                        id: '',
                                        text: 'No Match Found'
                                    }]
                                };
                            }
                        }
                    },
                    formatResult: formatAddress,
                });
                $('#modal-loading').hide();
                <?php } ?>

            }).change();
        });
        
    </script>