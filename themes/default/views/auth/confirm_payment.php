<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form','id'=>'brandForm');
        echo form_open_multipart("snap/confirm_payment", $attrib); ?>
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('payment_confirmation'); ?></h4>
        </div>
        <div class="modal-body">
            <p><?= lang('confirm_info'); ?></p>
            <div class="text-center">
                <input type="hidden" name="id" value="<?php echo $id ?>"/>
                <input type="submit" name="payments_confirmation" class="btn btn-primary" value="<?php echo lang('confirm') ?>" name="confirm_payment"/>
            </div>
        </div>
        <div class="modal-footer">
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>