<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('add_feedback_category') ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'CategoryForm');
        echo form_open_multipart("system_settings/add_feedback_category", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
                <label class="control-label" for="category"><?= lang("category"); ?></label>
                <?= form_input('category', '', 'class="form-control" id="category" required="required"'); ?>
                <input name="code" id="category" type="hidden" />
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><input name="is_active" type="checkbox"/> <?= lang("active"); ?></label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label><input name="flag" type="checkbox"/> <?= lang("Aksestoko"); ?></label>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <?php echo form_submit('add_feedback_category', lang('add_feedback_category'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?= FORCAPOS_VERSION ?>"></script>
<?= $modal_js ?>