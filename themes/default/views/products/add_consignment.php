<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    var count = 1, an = 1;
    $(document).ready(function () {
        if (localStorage.getItem('remove_csgls')) {
            if (localStorage.getItem('csgitems')) {
                localStorage.removeItem('csgitems');
            }
            if (localStorage.getItem('csgref')) {
                localStorage.removeItem('csgref');
            }
            if (localStorage.getItem('csgsupplier')) {
                localStorage.removeItem('csgsupplier');
            }
            if (localStorage.getItem('csgwarehouse')) {
                localStorage.removeItem('csgwarehouse');
            }
            if (localStorage.getItem('csgnote')) {
                localStorage.removeItem('csgnote');
            }
            if (localStorage.getItem('csgdate')) {
                localStorage.removeItem('csgdate');
            }
            localStorage.removeItem('remove_csgls');
        }
        <?php if ($consignment_items) { ?>
//        localStorage.setItem('csgitems', JSON.stringify(<?= $consignment_items; ?>));
        <?php } ?>
        <?php if ($warehouse_id) { ?>
//        localStorage.setItem('csgwarehouse', '<?= $warehouse_id; ?>');
//        $('#csgwarehouse').select2('readonly', true);
        <?php } ?>

        <?php if ($Owner || $Admin) { ?>
        if (!localStorage.getItem('csgdate')) {
            $("#csgdate").datetimepicker({
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
        $(document).on('change', '#csgdate', function (e) {
            localStorage.setItem('csgdate', $(this).val());
        });
        if (csgdate = localStorage.getItem('csgdate')) {
            $('#csgdate').val(csgdate);
        }
        <?php } ?>
        
        $("#add_item").autocomplete({
//            source: '<?= site_url('products/csg_suggestions'); ?>',
            source: function (request, response) {
                $.ajax({
                    type: 'get',
                    url: '<?= site_url('products/csg_suggestions'); ?>',
                    dataType: "json",
                    data: {
                        term: request.term,
                        supplier_id: $("#csgsupplier").val()
                    },
                    success: function (data) {
                        response(data);
                    }
                });
            },
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                }
                else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                }
                else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_consignment_item(ui.item);
                    if (row)
                        $(this).val('');
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_consignment'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo form_open_multipart("products/add_consignment", $attrib);
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("date", "csgdate"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="csgdate" required="required"'); ?>
                                </div>
                            </div>
                        <?php } ?>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("reference_no", "csgref"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control input-tip" id="csgref" readonly="readonly"'); ?>
                            </div>
                        </div>
                        <?= form_hidden('count_id', $count_id); ?>

                        <?php if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("warehouse", "csgwarehouse"); ?>
                                    <?php
                                    $wh[''] = '';
                                    foreach ($warehouses as $warehouse) {
                                        $wh[$warehouse->id] = $warehouse->name;
                                    }
                                    echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $this->session->userdata('warehouse_id')), 'id="csgwarehouse" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("warehouse") . '" required="required" '.($warehouse_id ? 'readonly' : '').' style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <?php } else {
                                $warehouse_input = array(
                                    'type' => 'hidden',
                                    'name' => 'warehouse',
                                    'id' => 'csgwarehouse',
                                    'value' => $this->session->userdata('warehouse_id'),
                                    );

                                echo form_input($warehouse_input);
                            } ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("supplier", "csgsupplier"); ?>
                                <?php if ($Owner || $Admin || $GP['suppliers-add'] || $GP['suppliers-index']) { ?><div class="input-group"><?php } ?>
                                    <input type="hidden" name="supplier" value="" id="csgsupplier"
                                           class="form-control" style="width:100%;"
                                           placeholder="<?= lang("select") . ' ' . lang("supplier") ?>">
                                    <input type="hidden" name="supplier_id" value="" id="supplier_id"
                                           class="form-control">
                                    <?php if ($Owner || $Admin || $GP['suppliers-index']) { ?>
                                        <div class="input-group-addon no-print" style="padding: 2px 5px; border-left: 0;">
                                            <a href="#" id="view-supplier" class="external" data-toggle="modal" data-target="#myModal"  data-backdrop="static">
                                                <i class="fa fa-2x fa-user" id="addIcon"></i>
                                            </a>
                                        </div>
                                    <?php } ?>
                                    <?php if ($Owner || $Admin || $GP['suppliers-add']) { ?>
                                    <div class="input-group-addon no-print" style="padding: 2px 5px;">
                                        <a href="<?= site_url('suppliers/add'); ?>" id="add-supplier" class="external" data-toggle="modal" data-target="#myModal"  data-backdrop="static">
                                            <i class="fa fa-2x fa-plus-circle" id="addIcon"></i>
                                        </a>
                                    </div>
                                <?php } ?>
                                <?php if ($Owner || $Admin || $GP['suppliers-add'] || $GP['suppliers-index']) { ?></div><?php } ?>
                            </div>
                        </div>
                        <div class="clearfix"></div>


                        <div class="col-md-12" id="sticker">
                            <div class="well well-sm">
                                <div class="form-group" style="margin-bottom:0;">
                                    <div class="input-group wide-tip">
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <i class="fa fa-2x fa-barcode addIcon"></i></a></div>
                                        <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . lang("add_product_to_order") . '"'); ?>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("products"); ?> *</label>

                                <div class="controls table-controls">
                                    <table id="csgTable" class="table items table-striped table-bordered table-condensed table-hover">
                                        <thead>
                                        <tr>
                                            <th class="col-md-5"><?= lang("product_name") . " (" . lang("product_code") . ")"; ?></th>
                                            <th class="col-md-2"><?= lang("variant"); ?></th>
                                            <th><?= lang("net_unit_cost"); ?></th>
                                            <th class="col-md-1"><?= lang("quantity"); ?></th>
                                            <?php
                                            if ($Settings->product_expiry) {
                                                echo '<th class="col-md-2">' . $this->lang->line("expiry_date") . '</th>';
                                            }
                                            ?>
                                            <th style="max-width: 30px !important; text-align: center;">
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

                        <div class="clearfix"></div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <?= lang("note", "csgnote"); ?>
                                    <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="csgnote" style="margin-top: 10px; height: 100px;"'); ?>
                                </div>
                            </div>
                            <div class="clearfix"></div>

                        <div class="col-md-12">
                            <div
                                class="fprom-group"><?php echo form_submit('add_adjustment', lang("submit"), 'id="add_adjustment" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></div>
                        </div>
                    </div>
                </div>
                <?php echo form_close(); ?>

            </div>

        </div>
    </div>
</div>

<div class="modal fade in" id="expModal" tabindex="-1" role="dialog" aria-labelledby="expModalLabel" aria-hidden="true"></div>

<div class="modal" id="prModal" tabindex="-1" role="dialog" aria-labelledby="prModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
                <h4 class="modal-title" id="prModalLabel"></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                    <div class="form-group">
                        <label for="cquantity" class="col-sm-4 control-label"><?= lang('quantity') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="cquantity">
                        </div>
                    </div>
                    <?php if ($Settings->product_expiry) { ?>
                        <div class="form-group">
                            <label for="cexpiry" class="col-sm-4 control-label"><?= lang('product_expiry') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control date" id="cexpiry">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="cunit" class="col-sm-4 control-label"><?= lang('product_unit') ?></label>
                        <div class="col-sm-8">
                            <div id="cunits-div"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="coption" class="col-sm-4 control-label"><?= lang('product_option') ?></label>
                        <div class="col-sm-8">
                            <div id="coptions-div"></div>
                        </div>
                    </div>
                    <?php if ($Settings->product_discount) { ?>
                        <div class="form-group">
                            <label for="cdiscount" class="col-sm-4 control-label"><?= lang('product_discount') ?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="cdiscount">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="cprice" class="col-sm-4 control-label"><?= lang('unit_price') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="cprice">
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
                    <input type="hidden" id="cunit_cost" value=""/>
                    <input type="hidden" id="old_qty" value=""/>
                    <input type="hidden" id="old_cost" value=""/>
                    <input type="hidden" id="row_id" value=""/>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editItem"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>