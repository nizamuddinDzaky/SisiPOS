<?php defined('BASEPATH') or exit('No direct script access allowed');

/* 
 * Copyright (c) 2017 adminSISI.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    adminSISI - initial API and implementation and/or initial documentation
 */
?>
<!-- <script src="<?= $assets ?>/ckeditor/ckeditor.js" type="text/javascript"></script>-->
<script src="<?= $assets ?>/js/sinergi.js?v=<?= FORCAPOS_VERSION ?>" type="text/javascript"></script>
<script type="text/javascript">
    <?php if ($this->session->userdata('remove_pols')) { ?>
        if (localStorage.getItem('moitems')) {
            localStorage.removeItem('moitems');
        }
        if (localStorage.getItem('modiscount')) {
            localStorage.removeItem('modiscount');
        }
        if (localStorage.getItem('motax2')) {
            localStorage.removeItem('motax2');
        }
        if (localStorage.getItem('monote')) {
            localStorage.removeItem('monote');
        }
        if (localStorage.getItem('mosupplier')) {
            localStorage.removeItem('mosupplier');
        }
        if (localStorage.getItem('mocurrency')) {
            localStorage.removeItem('mocurrency');
        }
        if (localStorage.getItem('moextras')) {
            localStorage.removeItem('moextras');
        }
    <?php $this->sma->unset_data('remove_pols');
    } ?>
    moitems_temp = {
        "data": []
    };

    var count = 1,
        an = 1,
        po_edit = false,
        product_variant = 0,
        DT = <?= $Settings->default_tax_rate ?>,
        DC = '<?= $default_currency->code ?>',
        shipping = 0,
        product_tax = 0,
        invoice_tax = 0,
        total_discount = 0,
        total = 0,
        tax_rates = <?php echo json_encode($tax_rates); ?>,
        moitems = {},
        audio_success = new Audio('<?= $assets ?>sounds/sound2.mp3'),
        audio_error = new Audio('<?= $assets ?>sounds/sound3.mp3');
    $(document).ready(function() {
        <?php if ($this->input->get('supplier')) { ?>
            if (!localStorage.getItem('moitems')) {
                localStorage.setItem('mosupplier', <?= $this->input->get('supplier'); ?>);
            }
        <?php } ?>
        if (!localStorage.getItem('motax2')) {
            localStorage.setItem('motax2', <?= $Settings->default_tax_rate2; ?>);
            setTimeout(function() {
                $('#extras').iCheck('check');
            }, 1000);
        }
        ItemnTotals();
        $("#add_item").autocomplete({
            // source: '<?= site_url('purchases/suggestions'); ?>',
            source: function(request, response) {
                if (!$('#mosupplier').val()) {
                    $('#add_item').val('').removeClass('ui-autocomplete-loading');
                    bootbox.alert('<?= lang('select_above'); ?>');
                    $('#add_item').focus();
                    return false;
                }
                console.log($('#mosupplier').val());
                $.ajax({
                    type: 'get',
                    url: '<?= site_url('purchases/suggestions'); ?>/' + $('#mosupplier').val(),
                    dataType: "json",
                    data: {
                        term: request.term,
                        supplier_id: $("#mosupplier").val()
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
                    //audio_error.play();
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
                    //audio_error.play();
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
                    var row = add_purchase_item(ui.item);
                    if (row)
                        $(this).val('');
                } else {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });

        $(document).on('click', '#addItemManually', function(e) {
            if (!$('#mcode').val()) {
                $('#mError').text('<?= lang('product_code_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#mname').val()) {
                $('#mError').text('<?= lang('product_name_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#mcategory').val()) {
                $('#mError').text('<?= lang('product_category_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#munit').val()) {
                $('#mError').text('<?= lang('product_unit_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#mcost').val()) {
                $('#mError').text('<?= lang('product_cost_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#mprice').val()) {
                $('#mError').text('<?= lang('product_price_is_required') ?>');
                $('#mError-con').show();
                return false;
            }

            var msg, row = null,
                product = {
                    type: 'standard',
                    code: $('#mcode').val(),
                    name: $('#mname').val(),
                    tax_rate: $('#mtax').val(),
                    tax_method: $('#mtax_method').val(),
                    category_id: $('#mcategory').val(),
                    unit: $('#munit').val(),
                    cost: $('#mcost').val(),
                    price: $('#mprice').val()
                };

            $.ajax({
                type: "get",
                async: false,
                url: site.base_url + "products/addByAjax",
                data: {
                    token: "<?= $csrf; ?>",
                    product: product
                },
                dataType: "json",
                success: function(data) {
                    if (data.msg == 'success') {
                        row = add_purchase_item(data.result);
                    } else {
                        msg = data.msg;
                    }
                }
            });
            if (row) {
                $('#mModal').modal('hide');
                //audio_success.play();
            } else {
                $('#mError').text(msg);
                $('#mError-con').show();
            }
            return false;

        });
        $('input.number-only').bind('keypress', function(e) {
            return !(e.which != 8 && e.which != 0 &&
                (e.which < 48 || e.which > 57) && e.which != 46);
        });

        $('#directoutside').on('ifChecked', function(e) {
            $('#outsidelink').slideDown();
        });
        $('#directoutside').on('ifUnchecked', function(e) {
            $('#outsidelink').slideUp();
        });
        $("#judul").on("change paste keyup", function() {
            var judul = $(this).val().substr(0, 30);
            judul = judul.replace(/\s+/g, '-');
            $("#linkpromo").val(judul);
        });
        $("#start_date").on("change paste keyup", function() {
            var start_dt = $(this).val();
            if (start_dt != '') {
                $("#end_date").prop("disabled", false);
            }
        });
        $("#end_date").change(function() {

            var endDate = $(this).val().split('/');
            var startDate = $("#start_date").val().split('/');
            endDate = new Date(endDate[2] + '-' + endDate[1] + '-' + endDate[0]);
            startDate = new Date(startDate[2] + '-' + startDate[1] + '-' + startDate[0]);
            if (endDate.toDateString() <= startDate.toDateString()) {
                $("#end_date").val('');
                $(".messageContainer").html("Kesalahan pada tanggal");
            } else {
                $(".messageContainer").html('');
                $("#promo_price").prop("disabled", false);
                $("#promo_diskon").prop("disabled", false);
                $("#promo_qty").prop("disabled", false);
            }
        });
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_promo'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>

                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo form_open_multipart("system_settings/add_promo", $attrib)
                ?>
                <div class="col-md-4">
                    <div class="form-group all">
                        <?= lang("title", "title") ?>
                        <?= form_input('Judul', (isset($_POST['Judul']) ? $_POST['Judul'] : ''), 'class="form-control" id="judul" required="required"'); ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <?= lang('linkpromo', 'linkpromo'); ?>
                        <?= form_input('linkpromo', (isset($_POST['linkpromo']) ? $_POST['linkpromo'] : ''), 'class="form-control tip" id="linkpromo" required="required"'); ?>
                    </div>

                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <?= lang("image", "image") ?>
                        <input id="image" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false" class="form-control file">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group all">
                        <?= lang("description", "Description") ?>
                        <?= form_input('description', (isset($_POST['description']) ? $_POST['description'] : ''), 'class="form-control" id="Description" required="required"'); ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <?= lang('code_promo', 'Kode Promo'); ?>
                        <?= form_input('kodepromo', (isset($_POST['kodepromo']) ? $_POST['kodepromo'] : ''), 'class="form-control tip" id="kode_promo"'); ?>
                    </div>
                </div>
                <div class="col-md-4"></div>
                <div class="col-md-5">
                    <label for="promotion" class="padding05">
                        <?= lang('promotion'); ?>
                    </label>
                    <div id="promo">
                        <div class="well well-sm">
                            <div class="form-group">
                                <?= lang('start_date', 'start_date'); ?>
                                <?= form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ''), 'class="form-control tip date" required="required" id="start_date"'); ?>
                            </div>
                            <div class="form-group">
                                <?= lang('end_date', 'end_date'); ?><span class="messageContainer" style="margin-left: 10px; color:red;"></span>
                                <?= form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ''), 'class="form-control tip date" required="required" disabled="disabled" id="end_date"'); ?>

                            </div>
                            <div class="form-group">
                                <input type="checkbox" class="checkbox" value="1" name="directoutside" id="directoutside" <?= $this->input->post('directoutside') ? 'checked="checked"' : ''; ?>>
                                <label for="directoutside" class="padding05">
                                    <?= lang('link_promo_outside'); ?>
                                </label>

                                <div id="outsidelink" style="display:none;">
                                    <?= form_input('linkout', (isset($_POST['linkout']) ? $_POST['linkout'] : ''), 'class="form-control tip" id="linkout" required="required"'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="panel panel-warning">
                        <div class="panel-heading"><?= lang('please_select_these_before_adding_product') ?></div>
                        <div class="panel-body" style="padding: 5px;">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("supplier", "mosupplier"); ?>
                                    <?php if ($Owner || $Admin || $GP['suppliers-add'] || $GP['suppliers-index']) { ?><div class="input-group"><?php } ?>
                                        <input type="hidden" name="supplier" value="" id="mosupplier" class="form-control" style="width:100%;" placeholder="<?= lang("select") . ' ' . lang("supplier") ?>">
                                        <input type="hidden" name="supplier_id" value="" id="supplier_id" class="form-control">
                                        <?php if ($Owner || $Admin || $GP['suppliers-index']) { ?>
                                            <div class="input-group-addon no-print" style="padding: 2px 5px; border-left: 0;">
                                                <a href="#" id="view-supplier" class="external" data-toggle="modal" data-target="#myModal" data-backdrop="static">
                                                    <i class="fa fa-2x fa-user" id="addIcon"></i>
                                                </a>
                                            </div>
                                        <?php } ?>
                                        <?php if ($Owner || $Admin || $GP['suppliers-add']) { ?>
                                            <div class="input-group-addon no-print" style="padding: 2px 5px;">
                                                <a href="<?= site_url('suppliers/add'); ?>" id="add-supplier" class="external" data-toggle="modal" data-target="#myModal" data-backdrop="static">
                                                    <i class="fa fa-2x fa-plus-circle" id="addIcon"></i>
                                                </a>
                                            </div>
                                        <?php } ?>
                                        <?php if ($Owner || $Admin || $GP['suppliers-add'] || $GP['suppliers-index']) { ?></div><?php } ?>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="col-md-12" id="sticker">
                    <div class="well well-sm">
                        <div class="form-group" style="margin-bottom:0;">
                            <div class="input-group wide-tip">
                                <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                    <i class="fa fa-2x fa-barcode addIcon"></i></a></div>
                                <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . lang("Promo Product") . '"'); ?>
                                <!-- <?php if ($Owner || $Admin || $GP['products-add']) { ?>
                                    <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                        <a href="<?= site_url('products/add') ?>" id="addManually" class="tip" title="<?= lang('add_product_manually') ?>">
                                            <i class="fa fa-2x fa-plus-circle addIcon" id="addIcon"></i>
                                        </a>
                                    </div>
                                <?php }  ?> -->
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="control-group table-group">
                        <label class="table-label"><?= lang("order_items"); ?> *</label>

                        <div class="controls table-controls">
                            <table id="moTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                <thead>
                                    <tr>
                                        <th class="col-md-5"><?= lang("product_name") . " (" . $this->lang->line("product_code") . ")"; ?></th>
                                        <th class="col-md-2"><?= lang("net_unit_cost"); ?>(<span class="currency"><?= $default_currency->code ?></span>)</th>
                                        <th class="col-md-2">Min<?= lang("quantity"); ?></th>
                                        <?php
                                        if ($Settings->product_discount) {
                                            echo '<th class="col-md-1">' . $this->lang->line("discount") . '</th>';
                                        }
                                        ?>
                                        <?php
                                        if ($Settings->tax1) {
                                            echo '<th class="col-md-1">' . $this->lang->line("product_tax") . '</th>';
                                        }
                                        ?>
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
                    <h3 class="blue">
                        <i class="fa fa-check-square-o"></i><?= lang('terms_and_conditions'); ?>
                    </h3>
                    <div class="clearfix"></div>
                    <div class="form-group">
                        <?= lang("note", "monote"); ?>
                        <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="monoteta" style="margin-top: 10px; height: 100px;"'); ?>
                    </div>
                    <div style="clear:both;"></div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <?php echo form_submit('add_promo', $this->lang->line("add_promo"), 'class="btn btn-primary"'); ?>
                        <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></button>
                    </div>
                </div>
                <?= form_close(); ?>
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
                    <div class="form-group">
                        <label for="pquantity" class="col-sm-4 control-label">Min<?= lang('quantity') ?></label>

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
                    <?php if ($Settings->product_discount) { ?>
                        <div class="form-group">
                            <label for="pdiscount" class="col-sm-4 control-label"><?= lang('product_discount') ?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pdiscount">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="pcost" class="col-sm-4 control-label"><?= lang('unit_cost') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pcost">
                        </div>
                    </div>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?= lang('net_unit_cost'); ?></th>
                            <th style="width:25%;"><span id="net_cost"></span></th>
                            <th style="width:25%;"><?= lang('product_tax'); ?></th>
                            <th style="width:25%;"><span id="pro_tax"></span></th>
                        </tr>
                    </table>
                    <input type="hidden" id="punit_cost" value="" />
                    <input type="hidden" id="old_tax" value="" />
                    <input type="hidden" id="old_qty" value="" />
                    <input type="hidden" id="old_cost" value="" />
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-2x">&times;</i></span><span class="sr-only"><?= lang('close'); ?></span></button>
                <h4 class="modal-title" id="mModalLabel"><?= lang('add_standard_product') ?></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <div class="alert alert-danger" id="mError-con" style="display: none;">
                    <!--<button data-dismiss="alert" class="close" type="button">×</button>-->
                    <span id="mError"></span>
                </div>
                <div class="row">
                    <div class="col-md-6 col-sm-6">
                        <div class="form-group">
                            <?= lang('product_code', 'mcode') ?> *
                            <input type="text" class="form-control" id="mcode">
                        </div>
                        <div class="form-group">
                            <?= lang('product_name', 'mname') ?> *
                            <input type="text" class="form-control" id="mname">
                        </div>
                        <div class="form-group">
                            <?= lang('category', 'mcategory') ?> *
                            <?php
                            $cat[''] = "";
                            foreach ($categories as $category) {
                                $cat[$category->id] = $category->name;
                            }
                            echo form_dropdown('category', $cat, '', 'class="form-control select" id="mcategory" placeholder="' . lang("select") . " " . lang("category") . '" style="width:100%"')
                            ?>
                        </div>
                        <div class="form-group">
                            <?= lang('unit', 'munit') ?> *
                            <input type="text" class="form-control" id="munit">
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <div class="form-group">
                            <?= lang('cost', 'mcost') ?> *
                            <input type="text" class="form-control" id="mcost">
                        </div>
                        <div class="form-group">
                            <?= lang('price', 'mprice') ?> *
                            <input type="text" class="form-control" id="mprice">
                        </div>

                        <?php if ($Settings->tax1) { ?>
                            <div class="form-group">
                                <?= lang('product_tax', 'mtax') ?>
                                <?php
                                $tr[""] = "";
                                foreach ($tax_rates as $tax) {
                                    $tr[$tax->id] = $tax->name;
                                }
                                echo form_dropdown('mtax', $tr, "", 'id="mtax" class="form-control input-tip select" style="width:100%;"');
                                ?>
                            </div>
                            <div class="form-group all">
                                <?= lang("tax_method", "mtax_method") ?>
                                <?php
                                $tm = array('0' => lang('inclusive'), '1' => lang('exclusive'));
                                echo form_dropdown('tax_method', $tm, '', 'class="form-control select" id="mtax_method" placeholder="' . lang("select") . ' ' . lang("tax_method") . '" style="width:100%"')
                                ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="addItemManually"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>