<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo $this->lang->line("add_tempo"); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form','id'=>'tempoForm');
        echo form_open_multipart("system_settings/add_tempo", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
                <label class="control-label" for="duration"><?php echo $this->lang->line("tempo_duration"); ?> *</label>
                <?= form_input('duration', '', 'class="form-control" id="brn_name" required="required"'); ?>
                <input name="code" id="brn_code" type="hidden"/>
            </div>
            <div class="form-group">
                <label class="control-label" for="description"><?php echo $this->lang->line("description"); ?> *</label>
                <?= form_input('description', '', 'class="form-control" id="brn_name" required="required"'); ?>
                <input name="code" id="brn_code" type="hidden"/>
            </div>
            <div>
                <div class="form-group">
                    <input type="checkbox" class="checkbox" value="1" name="is_active" id="is_active" <?= $this->input->post() ? ($this->input->post('is_active') ? 'checked="checked"' : '') : 'checked="checked"'; ?>>
                    <label for="is_active" class="padding05"><?= lang('active') ?></label>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_tempo', lang('add_tempo'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?=FORCAPOS_VERSION?>"></script>
<?= $modal_js ?>