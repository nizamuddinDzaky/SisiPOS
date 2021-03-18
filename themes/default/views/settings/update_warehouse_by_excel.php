<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('import_warehouses'); ?></h4>
        </div>
        <?php
        $attrib = array('class' => 'form-horizontal', 'data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("system_settings/update_warehouse_by_excel", $attrib)
        ?>
        <div class="modal-body">
            
            <p><?= lang('images_location_tip'); ?></p>

             <div class="col-md-12">
                <div class="form-group">
                    <label for="csv_file"><?= lang("upload_file"); ?></label>
                    <input type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" class="form-control file" data-show-upload="false"
                    data-show-preview="false" required="required"/>
                </div>
            </div>
    
            <div class="clearfix"></div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('import', lang('import'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?=FORCAPOS_VERSION?>"></script>
<?= $modal_js ?>

