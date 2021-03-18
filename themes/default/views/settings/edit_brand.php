<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_brand'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("system_settings/edit_brand/" . $brand->id, $attrib); ?>
        <div class="modal-body">
            <?php if($brand->client_id!=$this->session->userdata('company_id')){
//                echo $this->session->flashdata('error');
                echo "You Don't Have Right to ".lang('edit_brand');
            }else{?>
            <p><?= lang('update_info'); ?></p>

            <div class="form-group">
                <?= lang('code', 'code'); ?>
                <?= form_input('code', $brand->code, 'class="form-control" id="code"'); ?>
            </div>

            <div class="form-group">
                <?= lang('name', 'name'); ?>
                <?= form_input('name', $brand->name, 'class="form-control" id="name" required="required"'); ?>
            </div>

            <div class="form-group">
                <?= lang("image", "image") ?>
                <input id="image" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false"
                       class="form-control file">
            </div>
            <?php echo form_hidden('id', $brand->id); ?>
            <?php }?>
        </div>
        <div class="modal-footer">
        <?php if($brand->client_id==$this->session->userdata('company_id')){
            echo form_submit('edit_brand', lang('edit_brand'), 'class="btn btn-primary"');
        } else { ?>
            <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
        <?php } ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?=FORCAPOS_VERSION?>"></script>
<?= $modal_js ?>