<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_addon'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("system_settings/add_addon", $attrib); ?>
        <div class="modal-body ui-front">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
                <label for="name"><?php echo $this->lang->line("name"); ?></label>
                <?php echo form_input('name', '', 'class="form-control" id="name" required="required"'); ?>
            </div>
            <div class="form-group">
                <label for="price"><?php echo $this->lang->line("price"); ?></label>
                <?php echo form_input('price', '', 'class="form-control number-only" id="price" required '); ?>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_addon', lang('add_addon'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script>
$('input.number-only').bind('keypress', function (e) {
    return !(e.which != 8 && e.which != 0 &&
    (e.which < 48 || e.which > 57) && e.which != 46);
});
</script>
<?= $modal_js ?>