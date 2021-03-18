<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_shipping_charges'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("system_settings/add_shipping_charges", $attrib); ?>
        <div class="modal-body ui-front">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="min_distance"><?php echo $this->lang->line("minimal_distance"); ?></label>
                        <div class="input-group">
                            <?php echo form_input('min_distance', '', 'class="form-control input-tip" id="min_distance" required="required"'); ?>
                            <span class="input-group-addon" style="padding: 1px 10px;"><strong>Km</strong></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="cost_regular"><?php echo $this->lang->line("cost_regular"); ?></label>
                        <?php echo form_input('cost_regular', '', 'class="form-control input-tip" id="cost_regular" required="required"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="max_distance"><?php echo $this->lang->line("maximum_distance"); ?></label>
                        <div class="input-group">
                            <?php echo form_input('max_distance', '', 'class="form-control input-tip" id="max_distance" required="required"'); ?>
                            <span class="input-group-addon" style="padding: 1px 10px;"><strong>Km</strong></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for=""><?php echo $this->lang->line("cost_member"); ?></label>
                        <?php echo form_input('cost_member', '', 'class="form-control input-tip" id="cost_member"'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_shipping_charges', lang('add_shipping_charges'), 'class="btn btn-primary" disabled id="add_shipping_charges"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script>
    var $mindist = $('#min_distance');
    var $maxdist = $('#max_distance');
    var $costreg = $('#cost_regular');
    var $costmem = $('#cost_member');
    numbering($mindist, 'abc');
    numbering($maxdist, 'abc');
    numbering($costmem);
    numbering($costreg);



    function numbering(inputbox, param = null) {
        $(inputbox).keypress(function(event) {
            if ((event.charCode == 8 || event.charCode == 0 || event.charCode == 13)) {
                return null;
            } else {
                return event.charCode == 46 || event.charCode >= 48 && event.charCode <= 57;
                console.log($(this).val());
            }
        });
    }

    //    $('#min_distance').keypress(function(term){
    //        $.ajax({
    //            type:'get',
    //            url: '<?= site_url('system_settings/gap'); ?>',
    //            data: {
    //                term: $(this).val()
    //            },
    //            dataType: "json",
    //            success: function(data){
    //                console.log(data);
    //                if(data){
    //                    $('#add_shipping_charges').prop('disabled',true);
    //                }else{
    //                    $('#add_shipping_charges').prop('disabled',false);
    //                }
    //            }
    //        });
    //    });
</script>
<?= $modal_js ?>