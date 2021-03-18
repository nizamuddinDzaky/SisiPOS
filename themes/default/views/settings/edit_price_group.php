<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_price_group'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("system_settings/edit_price_group/" . $id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="form-group">
                <label class="control-label" for="name"><?php echo $this->lang->line("group_name"); ?></label>
                <?php echo form_input('name', $price_group->name, 'class="form-control" id="name" required="required"'); ?>
            </div>
            <!-- <div class="form-group">
                <?= lang("warehouse"); ?>
                <?php
                $wh[''] = '';
                foreach ($warehouses as $warehouse) {
                    $wh[$warehouse->id] = $warehouse->name;
                }
                echo form_dropdown('warehouse', $wh, $price_group->warehouse_id, 'class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("warehouse") . '" required="required" style="width:100%;" ');
                ?>
            </div> -->
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_price_group', lang('edit_price_group'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>