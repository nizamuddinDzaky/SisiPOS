<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo (!$payment->image? lang('add_proof_of_payment'):lang('proof_of_payment')) ?></h4>
        </div>
        <?php if(!$payment->image){?>

        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("auth/add_proof_payment/".$id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="bank_name"><?php echo $this->lang->line("bank_name"); ?></label>
                        <?php echo form_input('bank_name', '', 'class="form-control" required="required"');?>
                    </div>
                    <div class="form-group">
                        <label for="valid_no"><?php echo $this->lang->line("validation_no"); ?></label>
                        <?php echo form_input('valid_no', '', 'class="form-control" required="required"');?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="account_name"><?php echo $this->lang->line("account_name"); ?></label>
                        <?php echo form_input('account_name', '', 'class="form-control" required="required"');?>
                    </div>
                    <div class="form-group">
                        <label for="account_number"><?php echo $this->lang->line("account_number"); ?></label>
                        <?php echo form_input('account_number', '', 'class="form-control" required="required"');?>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <?= lang("image", "image") ?>
                        <input id="add_proof" type="file" data-browse-label="<?= lang('browse'); ?>" name="add_proof" data-show-upload="false" data-show-preview="false" class="form-control file" accept="image/*" required>
                        <span id="InfoImageBrand"><i style="color:red;"><sup><strong>*Recomended : </strong><?= "Width:".$this->Settings->twidth."px, Height:".$this->Settings->theight."px, Max File Size: 500" ?>Kb</sup></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_payment', lang('add_payment'), 'class="btn btn-primary"'); ?>
        </div>
        <?php echo form_close(); ?>

        <?php }else{?>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12" style="text-align: center">
                    <img id="bl-image" src="<?= base_url() ?>assets/uploads/proof_payments/<?= $payment->image ?>"
                    alt="<?= $payment->company_name ?>" class="img-responsive img-thumbnail"/>
                </div>
            </div>
        </div>
        <?php }?>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>