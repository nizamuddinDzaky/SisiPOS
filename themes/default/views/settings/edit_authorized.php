<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_authorized'); ?></h4>
        </div>
        <?php echo form_open("system_settings/edit_authorized/" . $id); ?>
        <div class="modal-body">
           
            <p><?= lang('enter_info'); ?></p>

            <div class="form-group">
                <label class="control-label" for="company"><?php echo $this->lang->line("company"); ?></label>

                <div
                    class="controls"> <?php echo form_input('company', $authorized[0]->company, 'class="form-control" id="company" readonly'); ?> </div>
            </div>
            
           <div class="form-group">
                <label class="control-label" for="email"><?php echo $this->lang->line("email"); ?></label>

                <div
                    class="controls"> <?php echo form_input('email', $authorized[0]->email, 'class="form-control" id="email" readonly'); ?> </div>
            </div>
            
            <div class="form-group">
                <label class="control-label" for="users"><?php echo $this->lang->line("users"); ?></label>

                <div
                    class="controls"> <?php echo form_input('users', $authorized[0]->users, 'class="form-control" id="users"'); ?> </div>
            </div>
            <div class="form-group">
                <label class="control-label" for="warehouses"><?php echo $this->lang->line("warehouses"); ?></label>

                <div
                    class="controls"> <?php echo form_input('warehouses', $authorized[0]->warehouses, 'class="form-control" id="warehouses" '); ?> </div>
            </div>
            <div class="form-group">
                <label for="biller"><?php echo $this->lang->line("biller"); ?></label>

               <div 
                    class="controls"> <?php echo form_input('biller', $authorized[0]->biller, 'class="form-control" id="biller" readonly'); ?> </div>
            </div>
            <div class="form-group">
                <label for="create_on"><?php echo $this->lang->line("create_on"); ?></label>

               <div 
                    class="controls"> <?php echo form_input('create_on', $authorized[0]->create_on, 'class="form-control" id="create_on" readonly'); ?> </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_authorized', lang('edit_authorized'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>