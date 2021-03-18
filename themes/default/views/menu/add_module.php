<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript">
    $(document).ready(function() {

    });
</script>

<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('add_module') ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'bankForm');
        echo form_open_multipart("menu_permissions/add_module", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="form-group">
                <label class="control-label" for="name"><?= lang('name') ?></label>
                <input id="name" type="text" placeholder="<?= lang('name') ?>" name="name" class="form-control file" value="">
            </div>
            <div class="form-group">
                <label class="control-label" for="code"><?= lang('code') ?></label>
                <input id="code" type="text" placeholder="<?= lang('code') ?>" name="code" class="form-control file" value="">
            </div>
            <div class="form-group">
                <label class="control-label" for="icon"><?= lang('icon') ?></label>
                <input id="icon" type="text" placeholder="<?= lang('icon') ?>" name="icon" class="form-control file" value="">
            </div>
            <div class="form-group">
                <label class="control-label" for="priority"><?= lang('priority') ?></label>
                <input id="priority" type="number" placeholder="<?= lang('priority') ?>" name="priority" class="form-control file" value="" required>
            </div>
            <div class="form-group">
                <input type="checkbox" name="active" class="checkbox multi-select" style="padding:2px;height:auto;" id="cms-active">
                <label for="cms-active">&nbsp;&nbsp;Active</label>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('tambah', "Tambah", 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?= FORCAPOS_VERSION ?>"></script>
<?= $modal_js ?>