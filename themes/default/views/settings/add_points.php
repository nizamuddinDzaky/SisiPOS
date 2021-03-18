<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_points'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("system_settings/add_points", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <!--            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="each_spent"><?php echo $this->lang->line("customer_award_points"); ?></label>
                        <?php echo form_input('each_spent', '', 'class="form-control input-tip" id="each_spent"'); ?>
                    </div>
                    <div class="form-group">
                        <label for="each_sale"><?php echo $this->lang->line("staff_award_points"); ?></label>
                        <?php echo form_input('each_sale', '', 'class="form-control input-tip" id="each_sale"'); ?>
                    </div>
                </div>
                <div class="col-md-4">
                    
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="ca_point"><?php echo $this->lang->line("award_points"); ?></label>
                        <?php echo form_input('ca_point', '', 'class="form-control input-tip" id="ca_point"'); ?>
                    </div>
                    <div class="form-group">
                        <label for="sa_point"><?php echo $this->lang->line("award_points"); ?></label>
                        <?php echo form_input('sa_point', '', 'class="form-control input-tip" id="sa_point"'); ?>
                    </div>
                </div>
            </div>-->
            <div class="row">
                <div class="col-lg-12">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label"><?= lang("customer_award_points"); ?></label>

                            <div class="row">
                                <div class="col-sm-5 col-xs-6">
                                    <?= lang('each_spent'); ?><br>
                                    <?= form_input('each_spent', '', 'class="form-control"'); ?>
                                </div>
                                <div class="col-sm-1 col-xs-1 text-center"><i class="fa fa-arrow-right"></i>
                                </div>
                                <div class="col-sm-5 col-xs-5">
                                    <?= lang('award_points'); ?><br>
                                    <?= form_input('ca_point', '', 'class="form-control"'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label"><?= lang("staff_award_points"); ?></label>

                            <div class="row">
                                <div class="col-sm-5 col-xs-6">
                                    <?= lang('price'); ?><br>
                                    <?= form_input('price_exchange', '', 'class="form-control"'); ?>
                                </div>
                                <div class="col-sm-1 col-xs-1 text-center"><i class="fa fa-arrow-right"></i>
                                </div>
                                <div class="col-sm-5 col-xs-5">
                                    <?= lang('point'); ?><br>
                                    <?= form_input('point_exchange', '', 'class="form-control"'); ?>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_points', lang('add_points'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>