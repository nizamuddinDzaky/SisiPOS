<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript">
    var count = 1,
        an = 1,
        product_variant = 0,
        DT = <?= $Settings->default_tax_rate ?>,
        product_tax = 0,
        invoice_tax = 0,
        product_discount = 0,
        order_discount = 0,
        total_discount = 0,
        total = 0,
        allow_discount = <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? 1 : 0; ?>,
        tax_rates = <?php echo json_encode($tax_rates); ?>;
    //var audio_success = new Audio('<?= $assets ?>sounds/sound2.mp3');
    //var audio_error = new Audio('<?= $assets ?>sounds/sound3.mp3');
    $(document).ready(function() {
        console.log(site.dateFormats.js_ldate);
        if (localStorage.getItem('remove_slls')) {
            if (localStorage.getItem('sltrash')) {
                localStorage.removeItem('sltrash');
            }
            if (localStorage.getItem('slitems_temp')) {
                localStorage.removeItem('slitems_temp');
            }
            if (localStorage.getItem('slitems')) {
                localStorage.removeItem('slitems');
            }
            if (localStorage.getItem('sldiscount')) {
                localStorage.removeItem('sldiscount');
            }
            if (localStorage.getItem('sltax2')) {
                localStorage.removeItem('sltax2');
            }
            if (localStorage.getItem('slref')) {
                localStorage.removeItem('slref');
            }
            if (localStorage.getItem('slshipping')) {
                localStorage.removeItem('slshipping');
            }
            if (localStorage.getItem('slwarehouse')) {
                localStorage.removeItem('slwarehouse');
            }
            if (localStorage.getItem('slnote')) {
                localStorage.removeItem('slnote');
            }
            if (localStorage.getItem('slinnote')) {
                localStorage.removeItem('slinnote');
            }
            if (localStorage.getItem('slcustomer')) {
                localStorage.removeItem('slcustomer');
            }
            if (localStorage.getItem('slbiller')) {
                localStorage.removeItem('slbiller');
            }
            if (localStorage.getItem('slcurrency')) {
                localStorage.removeItem('slcurrency');
            }
            if (localStorage.getItem('slprice_type')) {
                localStorage.removeItem('slprice_type');
            }
            if (localStorage.getItem('sldate')) {
                localStorage.removeItem('sldate');
            }
            if (localStorage.getItem('slsale_status')) {
                localStorage.removeItem('slsale_status');
            }
            if (localStorage.getItem('slpayment_status')) {
                localStorage.removeItem('slpayment_status');
            }
            if (localStorage.getItem('paid_by')) {
                localStorage.removeItem('paid_by');
            }
            if (localStorage.getItem('amount_1')) {
                localStorage.removeItem('amount_1');
            }
            if (localStorage.getItem('paid_by_1')) {
                localStorage.removeItem('paid_by_1');
            }
            if (localStorage.getItem('pcc_holder_1')) {
                localStorage.removeItem('pcc_holder_1');
            }
            if (localStorage.getItem('pcc_type_1')) {
                localStorage.removeItem('pcc_type_1');
            }
            if (localStorage.getItem('pcc_month_1')) {
                localStorage.removeItem('pcc_month_1');
            }
            if (localStorage.getItem('pcc_year_1')) {
                localStorage.removeItem('pcc_year_1');
            }
            if (localStorage.getItem('pcc_no_1')) {
                localStorage.removeItem('pcc_no_1');
            }
            if (localStorage.getItem('cheque_no_1')) {
                localStorage.removeItem('cheque_no_1');
            }
            if (localStorage.getItem('payment_note_1')) {
                localStorage.removeItem('payment_note_1');
            }
            if (localStorage.getItem('slpayment_term')) {
                localStorage.removeItem('slpayment_term');
            }
            localStorage.removeItem('remove_slls');
        }
        slitems_temp = {
            "data": []
        };
        <?php if ($quote_id) { ?>
            // localStorage.setItem('sldate', '<?= $this->sma->hrld($quote->date) ?>');
            localStorage.setItem('slcustomer', '<?= $quote->customer_id ?>');
            localStorage.setItem('slbiller', '<?= $quote->biller_id ?>');
            localStorage.setItem('slwarehouse', '<?= $quote->warehouse_id ?>');
            localStorage.setItem('slnote', '<?= str_replace(array("\r", "\n"), "", $this->sma->decode_html($quote->note)); ?>');
            localStorage.setItem('sldiscount', '<?= $quote->order_discount_id ?>');
            localStorage.setItem('sltax2', '<?= $quote->order_tax_id ?>');
            localStorage.setItem('slshipping', '<?= $quote->shipping ?>');
            localStorage.setItem('slitems', JSON.stringify(<?= $quote_items; ?>));

            localStorage.setItem('sltrash', JSON.stringify(<?= $rand_id ?>));
            var d = JSON.parse(localStorage.getItem('sltrash'));
            for (var i = 0; i < Object.keys(d).length; i++) {
                slitems_temp.data.push({
                    "trx_id": d[i].trx_id,
                    "product_id": d[i].product_id
                });
            }
            localStorage.setItem('slitems_temp', JSON.stringify(slitems_temp));

        <?php } ?>
        <?php if ($this->input->get('customer')) { ?>
            if (!localStorage.getItem('slitems')) {
                localStorage.setItem('slcustomer', <?= $this->input->get('customer'); ?>);
            }
        <?php } ?>
        <?php if ($Owner || $Admin || $LT) { ?>
            if (!localStorage.getItem('sldate')) {
                $("#sldate").datetimepicker({
                    format: site.dateFormats.js_ldate,
                    fontAwesome: true,
                    language: 'sma',
                    weekStart: 1,
                    todayBtn: 1,
                    autoclose: 1,
                    todayHighlight: 1,
                    startView: 2,
                    forceParse: 0
                }).datetimepicker('update', new Date());
            }
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
        if (!localStorage.getItem('slref')) {
            localStorage.setItem('slref', '<?= $slnumber ?>');
        }
        if (!localStorage.getItem('sltax2')) {
            localStorage.setItem('sltax2', <?= $Settings->default_tax_rate2; ?>);
        }
        ItemnTotals();
        $('.bootbox').on('hidden.bs.modal', function(e) {
            $('#add_item').focus();
        });
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
        $(document).on('change', '#gift_card_no', function() {
            var cn = $(this).val() ? $(this).val() : '';
            if (cn != '') {
                $.ajax({
                    type: "get",
                    async: false,
                    url: site.base_url + "sales/validate_gift_card/" + cn,
                    dataType: "json",
                    success: function(data) {
                        if (data === false) {
                            $('#gift_card_no').parent('.form-group').addClass('has-error');
                            bootbox.alert('<?= lang('incorrect_gift_card') ?>');
                        } else if (data.customer_id !== null && data.customer_id !== $('#slcustomer').val()) {
                            $('#gift_card_no').parent('.form-group').addClass('has-error');
                            bootbox.alert('<?= lang('gift_card_not_for_customer') ?>');

                        } else {
                            $('#gc_details').html('<small>Card No: ' + data.card_no + '<br>Value: ' + data.value + ' - Balance: ' + data.balance + '</small>');
                            $('#gift_card_no').parent('.form-group').removeClass('has-error');
                        }
                    }
                });
            }
        });
        $(document).on('change', '#sltype', function(e) {
            let sltype = $('#sltype').val();
            if (sltype == 'booking') {
                $('#select2-chosen-5').text('<?= lang('reserved') ?>');
                var opt = '<option value="reserved" selected ><?= lang('reserved') ?></option>';
                opt += '<option value="pending"><?= lang('pending') ?></option>';
                $('#slsale_status').html(opt);
            } else {
                $('#select2-chosen-5').text('<?= lang('completed') ?>');
                var opt = '<option value="completed" selected><?= lang('completed') ?></option>';
                opt += '<option value="pending"><?= lang('pending') ?></option>';
                $('#slsale_status').html(opt);
            }
        });

        var filter_html_version = '<?= getenv('FORCAPOS_VERSION') ?>';
        var filter_html = {
            sb_reference_no : {
                field : "reference_no",
                label : "<?= lang("reference_no"); ?>",
                status:1
            },
            sb_remaining_credit_limit : {
                field : "remaining_credit_limit",
                label : "<?= lang("remaining_credit_limit"); ?>",
                status:1
            },
            sb_order_tax : {
                field : "order_tax",
                label : "<?= lang("order_tax"); ?>",
                status:1
            },
            sb_order_discount: {
                field : "order_discount",
                label : "<?= lang("order_discount"); ?>",
                status:1
            },
            sb_shipping_Price : {
                field : "shipping_Price",
                label : "<?= lang("Shipping_Price"); ?>",
                status:1
            },
            sb_document : {
                field : "document",
                label : "<?= lang("document") ?>",
                status:1
            },
            sb_payment_term : {
                field : "payment_term",
                label : "<?= lang("payment_term"); ?>",
                status:1
            },
            sb_sale_note : {
                field : "sale_note",
                label : "<?= lang("sale_note"); ?>",
                status:1
            },
            sb_staff_note : {
                field : "staff_note",
                label : "<?= lang("staff_note"); ?>",
                status:1
            },
            sb_create_delivery : {
                field : "create_delivery",
                label : "<?= lang("create_delivery"); ?>",
                status:1
            }
        };

        var filter_add_sales_booking = JSON.parse(localStorage.getItem('filter_add_sales_booking'));
        var filter_html_version_local = localStorage.getItem('filter_add_sales_booking_version');
        if((filter_add_sales_booking == null ||  Object.keys(filter_add_sales_booking).length != Object.keys(filter_html).length) || (filter_html_version_local == null || filter_html_version != filter_html_version_local)){
            localStorage.setItem('filter_add_sales_booking', JSON.stringify(filter_html));
            localStorage.setItem('filter_add_sales_booking_version', filter_html_version);
        }
        
        filter_html = JSON.parse(localStorage.getItem('filter_add_sales_booking'));
        var field_filter_key = Object.keys(filter_html);

        for (var i = 0; i < field_filter_key.length; i++) {
            var check = filter_html[field_filter_key[i]];
            if(check.status == 1){
                $(`#${field_filter_key[i]}`).iCheck('check');
                $("#display_input_"+filter_html[field_filter_key[i]].field).show();
            }else{
                $(`#${field_filter_key[i]}`).iCheck('uncheck');
                $("#display_input_"+filter_html[field_filter_key[i]].field).hide();
            }
        }

        $(document).on('ifChecked', '.check_filter_html', function(event) {
            filter_html[this.id].status = 1;
            if(!($('#slsale_status').val() == "pending" && this.id == "sb_create_delivery"))
                $("#display_input_"+filter_html[this.id].field).show();
            localStorage.setItem('filter_add_sales_booking', JSON.stringify(filter_html));
        });

        $(document).on('ifUnchecked', '.check_filter_html', function(event) {
            filter_html[this.id].status = 0;
            $("#display_input_"+filter_html[this.id].field).hide();
            localStorage.setItem('filter_add_sales_booking', JSON.stringify(filter_html));
        });
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_booking_sale'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <?php echo anchor($mb_add_booking_sale, '<i class="icon fa fa-book tip" data-placement="left" title="' . lang("manual_book") . '"></i> ', 'target="_blank"') ?>
            
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-columns tip" data-placement="left" title="<?= lang("column_filter") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel" style="height: 250px; overflow-y: scroll;">
                        <li>
                            <a href="javascript:void(0)">
                                <label for="sb_reference_no" style="width: 100%;">
                                    <input id="sb_reference_no" class="checkbox check_filter_html" type="checkbox" checked/> <?= lang("reference_no", "slref"); ?>
                                </label>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <label for="sb_remaining_credit_limit" style="width: 100%;">
                                    <input id="sb_remaining_credit_limit" class="checkbox check_filter_html" type="checkbox" checked/> <?= lang("remaining_credit_limit", "slkredit"); ?>
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
                        <?php if ($Settings->product_serial){ ?>
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
                        <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))){ ?>
                            <li>
                                <a href="javascript:void(0)">
                                    <label for="sb_discount" style="width: 100%;">
                                        <input id="sb_discount" class="checkbox check_filter_datatable" type="checkbox" checked/> <?= lang("discount"); ?>
                                    </label>
                                </a>
                            </li>
                        <?php } ?>
                        <?php if ($Settings->tax1){ ?>
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
                                        <input id="sb_order_tax" class="checkbox check_filter_html" type="checkbox" checked/> <?= lang("order_tax", "sltax2"); ?>
                                    </label>
                                </a>
                            </li>
                        <?php } ?>

                        <li>
                            <a href="javascript:void(0)">
                                <label for="sb_order_discount" style="width: 100%;">
                                    <input id="sb_order_discount" class="checkbox check_filter_html" type="checkbox" checked/> <?= lang("order_discount", "sldiscount"); ?>
                                </label>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <label for="sb_shipping_Price" style="width: 100%;">
                                    <input id="sb_shipping_Price" class="checkbox check_filter_html" type="checkbox" checked/> <?= lang("Shipping_Price", "slshipping"); ?>
                                </label>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <label for="sb_document" style="width: 100%;">
                                    <input id="sb_document" class="checkbox check_filter_html" type="checkbox" checked/> <?= lang("document", "document") ?>
                                </label>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <label for="sb_payment_term" style="width: 100%;">
                                    <input id="sb_payment_term" class="checkbox check_filter_html" type="checkbox" checked/> <?= lang("payment_term", "slpayment_term"); ?>
                                </label>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <label for="sb_sale_note" style="width: 100%;">
                                    <input id="sb_sale_note" class="checkbox check_filter_html" type="checkbox" checked/> <?= lang("sale_note", "slnote"); ?>
                                </label>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <label for="sb_staff_note" style="width: 100%;">
                                    <input id="sb_staff_note" class="checkbox check_filter_html" type="checkbox" checked/> <?= lang("staff_note", "slinnote"); ?>
                                </label>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <label for="sb_create_delivery" style="width: 100%;">
                                    <input id="sb_create_delivery" class="checkbox check_filter_html" type="checkbox" checked/> <?= lang("create_delivery"); ?>
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
                $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'formfield');
                echo form_open_multipart("sales_booking/add_booking_sale", $attrib);
                if ($quote_id) {
                    echo form_hidden('quote_id', $quote_id);
                }
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin || $LT) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("date", "sldate"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="sldate" required="required"'); ?>
                                </div>
                            </div>
                        <?php } ?>

                        <div class="col-md-4" id="display_input_reference_no">
                            <div class="form-group">
                                <?= lang("reference_no", "slref"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $slnumber), 'class="form-control input-tip" id="slref" readonly="readonly"'); ?>
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
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $this->session->userdata('biller_id')), 'id="slbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
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
                                                    echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $this->session->userdata('warehouse_id')), 'id="slwarehouse" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("warehouse") . '" required="required" style="width:100%;" ');
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
                                                    echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : 1), 'id="slcustomer" data-placeholder="' . lang("select") . ' ' . lang("customer") . '" required="required" class="form-control input-tip" style="width:100%;"');
                                                ?>
                                                <div class="input-group-addon no-print" style="padding: 2px 8px; border-left: 0;">
                                                    <a href="#" id="toogle-customer-read-attr" class="external">
                                                        <i class="fa fa-pencil" id="addIcon" style="font-size: 1.2em;"></i>
                                                    </a>
                                                </div>
                                                <div class="input-group-addon no-print" style="padding: 2px 7px; border-left: 0;" id="viewCustomer">
                                                    <a href="#" id="view-customer" class="external" data-toggle="modal" data-target="#myModal" data-backdrop="static">
                                                        <i class="fa fa-eye" id="addIcon" style="font-size: 1.2em;"></i>
                                                    </a>
                                                </div>
                                                <?php if ($Owner || $Admin || $GP['customers-add']) { ?>
                                                    <div class="input-group-addon no-print" style="padding: 2px 8px;" id="addCustomer">
                                                        <a href="<?= site_url('customers/add'); ?>" id="add-customer" class="external" data-toggle="modal" data-target="#myModal" data-backdrop="static">
                                                            <i class="fa fa-plus-circle" id="addIcon" style="font-size: 1.2em;"></i>
                                                        </a>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <!-- <span><i><sup><?= lang('percarian_toko_dan_orang') ?></sup></i></span> -->
                                        </div>
                                    </div>
                                    <div class="col-md-3" id="display_input_remaining_credit_limit">
                                        <div class="form-group">
                                            <?= lang("remaining_credit_limit", "slkredit"); ?>
                                            <?php echo form_input('kredit_limit', '0', 'disabled class="form-control tip" id="slkredit"'); ?>
                                        </div>
                                    </div>

                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <?= lang("price_type", "slprice_type"); ?> *
                                            <div class="input-group col-md-12">
                                                <div class="col-md-6">
                                                    <input type="radio" name="price_type" value="cash" checked="checked"> Cash 
                                                </div>
                                                <div class="col-md-6">
                                                    <input type="radio" name="price_type" value="credit"> Credit
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
                                        <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . lang("add_product_to_order") . '"'); ?>
                                        <!-- <?php if ($Owner || $Admin || $GP['products-add']) { ?>
                                            <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                                <a href="#" id="addManually" class="tip" title="<?= lang('add_product_manually') ?>">
                                                    <i class="fa fa-2x fa-plus-circle addIcon" id="addIcon"></i>
                                                </a>
                                            </div>
                                        <?php } ?> -->
                                        <?php if ($Owner || $Admin || $GP['sales-add_gift_card']) { ?>
                                            <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                                <a href="#" id="sellGiftCard" class="tip" title="<?= lang('sell_gift_card') ?>">
                                                    <i class="fa fa-2x fa-credit-card addIcon" id="addIcon"></i>
                                                </a>
                                            </div>
                                        <?php } ?>
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
                                                <th class="col-md-1" id="qty_item"><?= lang("quantity"); ?></th>
                                                <?php
                                                if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) {
                                                    echo '<th class="col-md-1">' . lang("discount") . '</th>';
                                                }
                                                ?>
                                                <?php
                                                if ($Settings->tax1) {
                                                    echo '<th class="col-md-1">' . lang("product_tax") . '</th>';
                                                }
                                                ?>
                                                <th>
                                                    <?= lang("subtotal"); ?>
                                                    (<span class="currency"><?= $default_currency->code ?></span>)
                                                </th>
                                                <th style="width: 30px !important; text-align: center;">
                                                    <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                                </th>
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

                            <?php if ($Owner || $Admin || $this->session->userdata('allow_discount')) { ?>
                                <div class="col-md-4" id="display_input_order_discount">
                                    <div class="form-group">
                                        <?= lang("order_discount", "sldiscount"); ?>
                                        <?php echo form_input('order_discount', '', 'class="form-control input-tip" id="sldiscount"'); ?>
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="col-md-4" id="display_input_shipping_Price">
                                <div class="form-group">
                                    <?= lang("Shipping_Price", "slshipping"); ?>
                                    <?php echo form_input('shipping', '', 'class="form-control input-tip" id="slshipping"'); ?>

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
                                    <?php $sst = array('reserved' => lang('reserved'), 'pending' => lang('pending'));
                                    echo form_dropdown('sale_status', $sst, 'reserved', 'class="form-control input-tip" required="required" id="slsale_status"'); ?>
                                </div>
                            </div>
                            <?php if ($Owner || $Admin || $GP['sales-payments']) { ?>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <?= lang("payment_status", "slpayment_status"); ?> 
                                        <?php $pst = array('pending' => lang('pending'), 'due' => lang('due'), 'partial' => lang('partial'), 'paid' => lang('paid'));
                                        echo form_dropdown('payment_status', $pst, '', 'class="form-control input-tip" required="required" id="slpayment_status"'); ?>

                                    </div>
                                </div>
                            <?php
                            } else {
                                echo form_hidden('payment_status', 'pending');
                            }
                            ?>
                                                
                            <div class="col-sm-4" id="display_input_payment_term">
                                <div class="form-group">
                                    <?= lang("payment_term", "slpayment_term"); ?>
                                    <div class="input-group">
                                        <?php echo form_dropdown('payment_term', $top, '', 'class="form-control input-tip" id="slpayment_term"'); ?>
                                        <?php echo form_input('', '', 'class="form-control input-tip"  data-trigger="focus" data-placement="top" title="'.lang('payment_term_tip').'" id="slpayment_term_m"'); ?>
                                        <div class="input-group-addon no-print" style="padding: 2px 8px; border-left: 0;">
                                            <a id="slpayment_term_check" class="external">
                                                <i class="fa fa-pencil" style="font-size: 1.2em;"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>

                        <div id="payments" style="display: none;">
                            <div class="col-md-12">
                                <div class="well well-sm well_1">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <?= lang("payment_reference_no", "payment_reference_no"); ?>
                                                    <?= form_input('payment_reference_no', (isset($_POST['payment_reference_no']) ? $_POST['payment_reference_no'] : $payment_ref), 'class="form-control tip" id="payment_reference_no"'); ?>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="payment">
                                                    <div class="form-group ngc">
                                                        <?= lang("amount", "amount_1"); ?>
                                                        <input name="amount-paid" type="text" id="amount_1" class="pa form-control kb-pad amount" />
                                                    </div>
                                                    <div class="form-group gc" style="display: none;">
                                                        <?= lang("gift_card_no", "gift_card_no"); ?>
                                                        <input name="gift_card_no" type="text" id="gift_card_no" class="pa form-control kb-pad" />

                                                        <div id="gc_details"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <?= lang("paying_by", "paid_by_1"); ?>
                                                    <select name="paid_by" id="paid_by_1" class="form-control paid_by">
                                                        <?= $this->sma->paid_opts(); ?>
                                                    </select>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="pcc_1" style="display:none;">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <input name="pcc_no" type="text" id="pcc_no_1" class="form-control" placeholder="<?= lang('cc_no') ?>" />
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <input name="pcc_holder" type="text" id="pcc_holder_1" class="form-control" placeholder="<?= lang('cc_holder') ?>" />
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <select name="pcc_type" id="pcc_type_1" class="form-control pcc_type" placeholder="<?= lang('card_type') ?>">
                                                            <option value="Visa"><?= lang("Visa"); ?></option>
                                                            <option value="MasterCard"><?= lang("MasterCard"); ?></option>
                                                            <option value="Amex"><?= lang("Amex"); ?></option>
                                                            <option value="Discover"><?= lang("Discover"); ?></option>
                                                        </select>
                                                        <!-- <input type="text" id="pcc_type_1" class="form-control" placeholder="<?= lang('card_type') ?>" />-->
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <input name="pcc_month" type="text" id="pcc_month_1" class="form-control" placeholder="<?= lang('month') ?>" />
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">

                                                        <input name="pcc_year" type="text" id="pcc_year_1" class="form-control" placeholder="<?= lang('year') ?>" />
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">

                                                        <input name="pcc_ccv" type="text" id="pcc_cvv2_1" class="form-control" placeholder="<?= lang('cvv2') ?>" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="pcheque_1" style="display:none;">
                                            <div class="form-group"><?= lang("cheque_no", "cheque_no_1"); ?>
                                                <input name="cheque_no" type="text" id="cheque_no_1" class="form-control cheque_no" />
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <?= lang('payment_note', 'payment_note_1'); ?>
                                            <textarea name="payment_note" id="payment_note_1" class="pa form-control kb-text payment_note"></textarea>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="total_items" value="" id="total_items" required="required" />

                        <div class="row" id="bt">
                            <div class="col-md-12">
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
                                        <input id="create_delivery" class="checkbox" type="checkbox" name="create_delivery"/> <?= lang("create_delivery"); ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12" id="submit-guide">
                            <input type="hidden" name="uuid" value="<?=getUuid()?>">
                            <div class="fprom-group"><?php echo form_button('add_booking_sale', lang("submit"), 'id="add_booking_sale" class="btn btn-primary"  style="padding: 6px 15px; margin:15px 0;"'); ?>
                                <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></div>
                        </div>
                    </div>
                </div>
                <div id="bottom-total" class="well well-sm" style="margin-bottom: 0;">
                    <table class="table table-bordered table-condensed totals" style="margin-bottom:0;">
                        <tr class="warning">
                            <td><?= lang('items') ?> <span class="totals_val pull-right" id="titems">0</span></td>
                            <td><?= lang('total') ?> <span class="totals_val pull-right" id="total">0.00</span></td>
                            <?php if ($Owner || $Admin || $this->session->userdata('allow_discount')) { ?>
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
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-2x">&times;</i></span><span class="sr-only"><?= lang('close'); ?></span></button>
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
                    <div class="form-group">
                        <label for="poption" class="col-sm-4 control-label"><?= lang('product_option') ?></label>
                        <div class="col-sm-8">
                            <div id="poptions-div"></div>
                        </div>
                    </div>
                    <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) { ?>
                        <div class="form-group">
                            <label for="pdiscount" class="col-sm-4 control-label"><?= lang('product_discount') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pdiscount">
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
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-2x">&times;</i></span><span class="sr-only"><?= lang('close'); ?></span></button>
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
                    <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) { ?>
                        <div class="form-group">
                            <label for="mdiscount" class="col-sm-4 control-label"><?= lang('product_discount') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="mdiscount">
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

<div class="modal" id="gcModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
                <h4 class="modal-title" id="myModalLabel"><?= lang('sell_gift_card'); ?></h4>
            </div>
            <div class="modal-body">
                <p><?= lang('enter_info'); ?></p>

                <div class="alert alert-danger gcerror-con" style="display: none;">
                    <button data-dismiss="alert" class="close" type="button">?</button>
                    <span id="gcerror"></span>
                </div>
                <div class="form-group">
                    <?= lang("card_no", "gccard_no"); ?> *
                    <div class="input-group">
                        <?php echo form_input('gccard_no', '', 'class="form-control" id="gccard_no"'); ?>
                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;"><a href="#" id="genNo"><i class="fa fa-cogs"></i></a></div>
                    </div>
                </div>
                <input type="hidden" name="gcname" value="<?= lang('gift_card') ?>" id="gcname" />

                <div class="form-group">
                    <?= lang("value", "gcvalue"); ?> *
                    <?php echo form_input('gcvalue', '', 'class="form-control" id="gcvalue"'); ?>
                </div>
                <div class="form-group">
                    <?= lang("price", "gcprice"); ?> *
                    <?php echo form_input('gcprice', '', 'class="form-control" id="gcprice"'); ?>
                </div>
                <div class="form-group">
                    <?= lang("customer", "gccustomer"); ?>
                    <?php echo form_input('gccustomer', '', 'class="form-control" id="gccustomer"'); ?>
                </div>
                <div class="form-group">
                    <?= lang("expiry_date", "gcexpiry"); ?>
                    <?php echo form_input('gcexpiry', $this->sma->hrsd(date("Y-m-d", strtotime("+2 year"))), 'class="form-control date" id="gcexpiry"'); ?>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" id="addGiftCard" class="btn btn-primary"><?= lang('sell_gift_card') ?></button>
            </div>
        </div>
    </div>
</div>



<div class="modal bootbox  fade bootbox-confirm in" id="modal-confirm" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="width: 25%;">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="bootbox-close-button close" data-dismiss="modal" aria-hidden="true" style="margin-top: -10px;">
                    <i class="fa fa-2x">×</i></button>
                <br>
                <div class="bootbox-body">
                    <div id="bookingnotif" style="text-align: center;">
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
        let pay_term = 'add';

        function go() {
            $('input[name="price_type"]').removeAttr("disabled");
            $("#submit-sale").attr("disabled", "disabled");
            document.getElementById('formfield').submit();
        };

        $(document).ready(function() {
            $('#gccustomer').select2({
                minimumInputLength: 1,
                ajax: {
                    url: site.base_url + "customers/suggestions",
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
                }
            });
            $('#genNo').click(function() {
                var no = generateCardNo();
                $(this).parent().parent('.input-group').children('input').val(no);
                return false;
            });

            $.ajax({
                type: 'get',
                url: site.base_url + 'welcome/experience_guide',
                dataType: "json",
                success: function(data) {
                    if (!data["sales-add"]) {
                        hopscotch.startTour(tour);
                    }
                }
            });

            $('#slsale_status').change(function(){
                $('#display_input_create_delivery').iCheck('uncheck');
                var filter_add_sales_booking = JSON.parse(localStorage.getItem('filter_add_sales_booking'));
                
                if(this.value == 'reserved' && filter_add_sales_booking['sb_create_delivery'].status == 1){
                    $('#display_input_create_delivery').show();
                }else{
                    $('#display_input_create_delivery').hide();
                }
            });

            $('#slwarehouse').change(function() {
                $('#modal-loading').show();
                $('#slcustomer').val('');
                $('#slkredit').val('');
                $('#slcustomer').select2({
                    minimumInputLength: 1,
                    ajax: {
                        url: site.base_url + "customers/suggestions",
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
            }).change();
        });

        var tour = {
            id: "guide-sales-add",
            onClose: function() {
                complete_guide('sales-add');
            },
            onEnd: function() {
                complete_guide('sales-add');
            },
            steps: [
                <?php if ($Owner || $Admin || $LT) { ?> {
                        title: "Tanggal",
                        content: "Silahkan isi tanggal transaksi",
                        target: "sldate",
                        placement: "top"
                    },
                <?php } ?> {
                    title: "Pelanggan",
                    content: "Pilih pelanggan yang ingin membeli produk",
                    target: "s2id_slcustomer",
                    placement: "bottom"
                },
                <?php if ($Owner || $Admin || $GP['customers-add']) { ?> {
                        title: "Tambah Pelanggan",
                        content: "Tidak ada data pelanggan? Silahkan tambahkan data pelanggan baru disini",
                        target: "addCustomer",
                        placement: "right",
                        arrowOffset: "-1px"
                    },
                <?php } ?> {
                    title: "Detil Pelanggan",
                    content: "Ketahui detil pelanggan dengan menekan tombol bersimbol mata",
                    target: "viewCustomer",
                    placement: "bottom",
                    arrowOffset: "-1px"
                },
                {
                    title: "Pilih Produk",
                    content: "Ketikkan nama atau kode produk yang ingin dijual",
                    target: "add_item",
                    placement: "top"
                },
                {
                    title: "Jumlah Produk",
                    content: "Sesuaikan jumlah produk yang dijual",
                    target: "qty_item",
                    placement: "top"
                },
                {
                    title: "Submit Data",
                    content: "Tekan tombol biru untuk submit data penjualan",
                    target: "submit-guide",
                    placement: "top"
                }
            ]
        };
    </script>