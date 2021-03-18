<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_tempo'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("system_settings/edit_tempo/" . $id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="form-group">
                <label class="control-label" for="duration"><?php echo $this->lang->line("tempo_duration"); ?></label>
                <?php echo form_input('duration', $top->duration, 'class="form-control" id="duration" required="required"'); ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="description"><?php echo $this->lang->line("description"); ?></label>
                <?php echo form_input('description', $top->description, 'class="form-control" id="description" required="required"'); ?>
            </div>
            <div>
                <div class="form-group">
                    <input type="checkbox" class="checkbox" name="is_active" id="is_active" <?= $top->is_active == 1 ? 'checked="checked"' : ''; ?>>
                    <label for="is_active" class="padding05"><?= lang('active') ?></label>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_tempo', lang('edit_tempo'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>