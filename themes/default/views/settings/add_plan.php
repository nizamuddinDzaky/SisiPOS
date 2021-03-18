<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_plan'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("system_settings/add_plan", $attrib); ?>
        <div class="modal-body ui-front">
            <p><?= lang('enter_info'); ?></p>
            <!--<div class="row">-->
                <!--<div class="col-md-6">-->
                    <div class="form-group">
                        <label for="name_plan"><?php echo $this->lang->line("name"); ?></label>
                        <?php echo form_input('name_plan', '', 'class="form-control input-tip" id="name_plan" required');?>
                    </div>
                    <div class="form-group">
                        <label for="users_plan"><?php echo $this->lang->line("users"); ?></label>
                        <?php echo form_input('users_plan', '', 'class="form-control number-only" id="users_plan" required');?>
                    </div>
                    <div class="form-group">
                        <label for="warehouses_plan"><?php echo $this->lang->line("warehouses"); ?></label>
                        <?php echo form_input('warehouses_plan', '', 'class="form-control number-only" id="warehouses_plan" required');?>
                    </div>
                <!--</div>-->
                <!--<div class="col-md-6">-->
                    <div class="form-group">
                        <label for="price_plan"><?php echo $this->lang->line("price"); ?></label>
                        <?php echo form_input('price_plan', '', 'class="form-control number-only" id="price_plan" required'); ?>
                    </div>
                    <div class="form-group">
                        <label for="description_plan"><?php echo $this->lang->line("description"); ?></label>
                        <div class="controls">
                            <textarea name="decription_plan" id="description_plan" class="form-contol skip" style="resize: none; width: 100%" rows="3"></textarea>
                        </div>
                        <?php // echo form_input('start_date', '', 'class="form-control" id="start_date" required="required"'); ?>
                    </div>
                <!--</div>-->
            <!--</div>-->
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_plan', lang('add_plan'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script>
    $('input.number-only').bind('keypress', function (e) {
        return !(e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57) && e.which != 46);
    });
</script>
<?= $modal_js ?>