<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('add_updates_notif') ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'UpdatesNotifForm');
        echo form_open_multipart("system_settings/add_updates_notif", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <div class="form-group col-md-4">
                    <label class="control-label" for="type"><?= lang("type"); ?></label>
                    <?php $typeList = array('bugfix'=>lang('bugfix'), 'new_feature'=>lang('new_feature'), 'enhancement'=>lang('enhancement'), 'other'=>lang('other'));
                          echo form_dropdown('type', $typeList, "", 'id="type" class="form-control input-tip select" style="width:100%;" required '); ?>
                </div>
                <div class="form-group col-md-8">
                    <label class="control-label" for="name"><?= lang("name"); ?></label>
                    <?= form_input('name', "", 'class="form-control" id="name" required="required"'); ?>
                    <input name="code" id="name" type="hidden" />
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-3">
                    <label class="control-label" for="version"><?= lang("version"); ?></label>
                    <?= form_input('version', '', 'class="form-control" id="version" required="required"'); ?>
                    <input name="code" id="version" type="hidden" />
                </div>
                <div class="form-group col-md-3">
                    <label class="control-label" for="version_number"><?= lang("version_num"); ?></label>
                    <?= form_input('version_num', '', 'class="form-control" id="version_number" required="required"'); ?>
                    <input name="code" id="version_number" type="hidden" />
                </div>
                <div class="form-group col-md-4">
                    <label class="control-label" for="release_at"><?= lang("release_date"); ?></label>
                    <?= form_input('release_at', '', 'class="form-control tip datetime" required="required" id="release_at"'); ?>
                    <input name="code" id="release_at" type="hidden" />
                </div>
                <div class="form-group col-md-2">
                    <label for="is_active" class="padding05"><?= lang('active') ?></label>
                    <input type="checkbox" class="checkbox" name="is_active" id="is_active" checked="checked">
                </div>
            </div>

            <div class="form-group">
                <div class="form-group">
                    <label class="control-label" for="link"><?= lang("link"); ?></label>
                    <?= form_input('link', "", 'class="form-control" id="link"'); ?>
                    <input name="code" id="link" type="hidden" />
                </div>
                <div class="form-group">
                    <label class="control-label" for="description"><?= lang("description"); ?></label>
                    <?= form_textarea('desc', "", 'class="form-control" id="description"'); ?>
                    <input name="code" id="description" type="hidden" />
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <?php echo form_submit('add_updates_notif', lang('add_updates_notif'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?= FORCAPOS_VERSION ?>"></script>
<?= $modal_js ?>