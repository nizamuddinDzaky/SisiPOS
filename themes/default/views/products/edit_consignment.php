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
        <?php if ($consignment) { ?>
        localStorage.setItem('csgsupplier', '<?=$consignment->supplier_id?>');
        localStorage.setItem('csgdate', '<?= $this->sma->hrld($consignment->date); ?>');
        localStorage.setItem('csgref', '<?= $consignment->reference_no; ?>');
        localStorage.setItem('csgwarehouse', '<?= $consignment->warehouse_id; ?>');
        localStorage.setItem('csgnote', '<?= str_replace(array("\r", "\n"), "", $this->sma->decode_html($consignment->note)); ?>');
        localStorage.setItem('csgitems', JSON.stringify(<?= $consignment_items; ?>));
        localStorage.setItem('remove_csgls', '1');
        <?php } ?>
        
        $("#add_item").autocomplete({
            source: '<?= site_url('products/csg_suggestions'); ?>',
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
                    var row = add_adjustment_item(ui.item);
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
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('edit_consignment'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo form_open_multipart("products/edit_consignment/".$consignment->id, $attrib);
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("date", "csgdate"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : $this->sma->hrld($consignment->date)), 'class="form-control input-tip datetime" id="csgdate" required="required"'); ?>
                                </div>
                            </div>
                        <?php } ?>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("reference_no", "csgref"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $consignment->reference_no), 'class="form-control input-tip" id="csgref" readonly="readonly"'); ?>
                            </div>
                        </div>

                        <?php if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("warehouse", "csgwarehouse"); ?>
                                    <?php
                                    $wh[''] = '';
                                    foreach ($warehouses as $warehouse) {
                                        $wh[$warehouse->id] = $warehouse->name;
                                    }
                                    echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $consignment->warehouse_id), 'id="csgwarehouse" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("warehouse") . '" required="required" style="width:100%;" ');
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
                                                echo '<th class="col-md-2">' . lang("expiry_date") . '</th>';
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
                                class="fprom-group"><?php echo form_submit('edit_consignment', lang("submit"), 'id="edit_consignment" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></div>
                        </div>
                    </div>
                </div>
                <?php echo form_close(); ?>

            </div>

        </div>
    </div>
</div>
