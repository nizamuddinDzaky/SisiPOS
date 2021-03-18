<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_billing_payment'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("auth/add_billing_payment/".$id, $attrib); ?>
        <div class="modal-body ui-front">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <div class="col-md-6">
                    <img id="bl-image" src="<?= base_url() ?>assets/uploads/proof_payments/<?= $billing->image ?>"
                    alt="<?= $billing->company_name ?>" class="img-responsive img-thumbnail"/>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="reference_no"><?php echo $this->lang->line("reference_no"); ?></label>
                        <?php echo form_input('reference_no', '', 'class="form-control" id="reference_no" readonly'); ?>
                    </div>
                    <div class="form-group">
                        <label for="amount"><?php echo $this->lang->line("amount"); ?></label>
                        <?php echo form_input('amount', '', 'class="form-control input-tip" id="amount" required="required"'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_billing_payment', lang('add_billing_payment'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>