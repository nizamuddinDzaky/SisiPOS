<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('edit_feedback_category') ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'CategoryForm');
        echo form_open_multipart("system_settings/edit_feedback_category/" . $category->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
                <label class="control-label" for="category"><?= lang("category"); ?></label>
                <?= form_input('category', $category->category, 'class="form-control" id="category" required="required"'); ?>
                <input name="code" id="category" type="hidden" />
            </div>

            <div class="row">
                <label class="col-md-4">
                    <input name="is_active" <?= $category->is_active ? 'checked' : '' ?> type="checkbox"/> <?= lang("active"); ?>
                </label>
                <label class="col-md-4">
                    <input name="repeat" type="checkbox"/> <?= lang("repeat_this_survey"); ?>
                </label>
                <label class="col-md-4">
                    <input name="flag" <?= $category->flag ? 'checked' : '' ?> type="checkbox"/> <?= lang("Aksestoko"); ?>
                </label>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_feedback_category', lang('edit_feedback_category'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
</div>
<?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?= FORCAPOS_VERSION ?>"></script>
<?= $modal_js ?>