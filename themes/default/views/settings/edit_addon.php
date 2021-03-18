<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_addon'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("system_settings/edit_addon/".$id, $attrib); ?>
        <div class="modal-body ui-front">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
                <label for="price"><?php echo $this->lang->line("price"); ?></label>
                <?php echo form_input('price', $this->sma->formatQuantity($addon->price), 'class="form-control number-only" id="quantity" required="required" '); ?>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_addon', lang('edit_addon'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>