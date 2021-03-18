<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('update_by_excel'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("customers/update_by_excel", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="well well-small">
                <?= lang("csv2"); ?> <span class="text-info">(<?= lang("Id") . ', ' .lang("company") . ', ' . lang("name") . ', ' . lang("email") . ', ' . lang("phone") . ', ' . lang("address") . ', ' . lang("city"); ?>
                    ,  <?= lang("state") . ', ' . lang("postal_code") . ', ' . lang("country") . ', ' . lang("vat_no") . ', ' .lang("deposits") . ', ' . lang("ccf1") . ', ' . lang("ccf2") . ', ' . lang("ccf3") . ', ' . lang("ccf4") . ', ' . lang("ccf5") . ', ' . lang("ccf6"); ?>
                    )</span> <?= lang("csv3"); ?><br>
            </div>
            <div class="form-group">
                <?= lang("upload_file", "excel_file") ?>
                <input id="excel_file" type="file" data-browse-label="<?= lang('browse'); ?>" name="excel_file" data-bv-notempty="true" data-show-upload="false"
                       data-show-preview="false" class="form-control file">
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('import', lang('Update'), 'class="btn btn-primary"'); ?>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>