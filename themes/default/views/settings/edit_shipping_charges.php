<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_shipping_charges'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("system_settings/edit_shipping_charges/".$id, $attrib); ?>
        <div class="modal-body ui-front">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="min_distance"><?php echo $this->lang->line("minimal_distance"); ?></label>
                        <?php echo form_input('min_distance', $this->sma->formatDecimal($shipping->min_distance), 'class="form-control input-tip" id="min_distance" required="required"');?>
                    </div>
                    <div class="form-group">
                        <label for="cost_regular"><?php echo $this->lang->line("cost_regular"); ?></label>
                        <?php echo form_input('cost_regular', $this->sma->formatDecimal($shipping->cost), 'class="form-control input-tip" id="cost_regular" required="required"');?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="max_distance"><?php echo $this->lang->line("maximum_distance"); ?></label>
                        <?php echo form_input('max_distance', $this->sma->formatDecimal($shipping->max_distance), 'class="form-control input-tip" id="max_distance" required="required"'); ?>
                    </div>
                    <div class="form-group">
                        <label for=""><?php echo $this->lang->line("cost_member"); ?></label>
                        <?php echo form_input('cost_member', $this->sma->formatDecimal($shipping->cost_member), 'class="form-control input-tip" id="cost_member"'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_shipping_charges', lang('edit_shipping_charges'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script>
    $("#max_distance,#min_distance,#cost_regular,#cost_member").keypress(function(event){
        if((event.charCode == 8 || event.charCode == 0 || event.charCode == 13)){
            return null;
        }else{
            return event.charCode >= 48 && event.charCode <= 57;
        }
    });
</script>
<?= $modal_js ?>