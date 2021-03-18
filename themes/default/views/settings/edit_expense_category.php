<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_expense_category'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("system_settings/edit_expense_category/" . $category->id, $attrib); ?>
        <div class="modal-body">
            <?php if($category->client_id!=$this->session->userdata('company_id')){
                echo lang('have_no_right_edit');
            }else{?>
            <p><?= lang('update_info'); ?></p>

            <div class="form-group">
                <?= lang('category_code', 'code'); ?>
                <?= form_input('code', $category->code, 'class="form-control" id="code" required="required"'); ?>
            </div>

            <div class="form-group">
                <?= lang('category_name', 'name'); ?>
                <?= form_input('name', $category->name, 'class="form-control" id="name" required="required"'); ?>
            </div>

            <?php echo form_hidden('id', $category->id); ?>
            <?php }?>
        </div>
        <div class="modal-footer">
            <?php if($category->client_id==$this->session->userdata('company_id')){
                echo form_submit('edit_expense_category', lang('edit_expense_category'), 'class="btn btn-primary"');
            }else{ ?>
                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
            <?php }?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>