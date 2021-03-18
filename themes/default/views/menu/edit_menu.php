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
            <h4 class="modal-title" id="myModalLabel"><?= lang('edit_menu') ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'bankForm');
        echo form_open_multipart("menu_permissions/edit_menu/" . $menus->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
                <label class="control-label" for="module"><?= lang('module') ?></label>
                <?php
                $m = array();
                $m[''] = lang("select") . ' ' . lang("module");
                foreach ($modules as $module) {
                    $m[$module->id] = $module->name;
                }
                echo form_dropdown('parent_id', $m, $menus->parent_id, 'id="module" class="form-control input-tip select" style="width:100%;" required ');
                ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="name"><?= lang('name') ?></label>
                <input id="name" type="text" placeholder="<?= lang('name') ?>" name="name" class="form-control" value="<?= $menus->name ?>" required>
            </div>
            <div class="form-group">
                <label class="control-label" for="code"><?= lang('code') ?></label>
                <input id="code" type="text" placeholder="<?= lang('code') ?>" name="code" class="form-control " value="<?= $menus->code ?>" required>
            </div>
            <div class="form-group">
                <label class="control-label" for="icon"><?= lang('icon') ?></label>
                <input id="icon" type="text" placeholder="<?= lang('icon') ?>" name="icon" class="form-control " value="<?= $menus->icon ?>" required>
            </div>
            <div class="form-group">
                <label class="control-label" for="url"><?= lang('url') ?></label>
                <input id="url" type="text" placeholder="<?= lang('url') ?>" name="url" class="form-control " value="<?= $menus->url ?>" required>
            </div>
            <div class="form-group">
                <label class="control-label" for="priority"><?= lang('priority') ?></label>
                <input id="priority" type="number" placeholder="<?= lang('priority') ?>" name="priority" class="form-control file" value="<?= $menus->priority ?>">
            </div>
            <div class="form-group">
                <input type="checkbox" name="active" class="checkbox multi-select" style="padding:2px;height:auto;" id="menu-active" <?= $menus->is_active == 1 ? 'checked' : '' ?>>
                <label for="menu-active">&nbsp;&nbsp;<?= lang('active') ?></label>
            </div>
            <div class="form-group">
                <input type="checkbox" name="is_display" class="checkbox" style="padding:2px;height:auto;" id="menu-display" <?= $menus->is_displayed == 1 ? 'checked' : '' ?>>
                <label for="menu-display">&nbsp;&nbsp;<?= lang('is_display') ?></label>
            </div>
            <div class="form-group">
                <input type="checkbox" name="is_new_feature" class="checkbox" style="padding:2px;height:auto;" id="is_new_feature" <?= $menus->is_new_feature == 1 ? 'checked' : '' ?>>
                <label for="is_new_feature">&nbsp;&nbsp;<?= lang('is_new_feature') ?></label>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit', "Edit", 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?= FORCAPOS_VERSION ?>"></script>
<?= $modal_js ?>