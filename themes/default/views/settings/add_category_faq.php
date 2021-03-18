<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript">
    $(document).ready(function() {

    });
</script>

<style>
.kv-fileinput-upload {
    display: none;
}
</style>

<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel">Add Category Faq</h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'bankForm');
        echo form_open_multipart("system_settings/add_category_faq", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="form-group">
                Menu
                <input id="menu" type="text" placeholder="Nama Kategori" name="menu" class="form-control file" value="">
            </div>
            <div class="form-group">
                Image
                <input type="file" data-browse-label="<?= lang('browse'); ?>" name="image" id="image" data-show-upload="true" data-show-preview="true" accept="image/*" class="form-control file" />
            </div>
            <input type="checkbox" name="active" class="checkbox multi-select" style="padding:2px;height:auto;" id="cms-active">
            <label for="cms-active">Active</label>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('tambah', "Tambah", 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?= FORCAPOS_VERSION ?>"></script>
<?= $modal_js ?>