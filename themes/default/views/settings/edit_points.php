<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_points'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("system_settings/edit_points/".$id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <div class="col-lg-12">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label"><?= lang("customer_award_points"); ?></label>
 
                            <div class="row">
                                <div class="col-sm-5 col-xs-6">
                                    <?= lang('each_spent'); ?><br>
                                    <?= form_input('each_spent', $this->sma->formatDecimal($pts->spent), 'class="form-control"'); ?>
                                </div>
                                <div class="col-sm-1 col-xs-1 text-center"><i class="fa fa-arrow-right"></i>
                                </div>
                                <div class="col-sm-5 col-xs-5">
                                    <?= lang('award_points'); ?><br>
                                    <?= form_input('ca_point', $pts->customer_award_point, 'class="form-control"'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label"><?= lang("exchange_rate_points"); ?></label>
                            <div class="row">
                                <div class="col-sm-5 col-xs-6">
                                    <?= lang('price'); ?><br>
                                    <?= form_input('price_exchange', $this->sma->formatDecimal($pts->price_exchange), 'class="form-control"'); ?>
                                </div>
                                <div class="col-sm-1 col-xs-1 text-center"><i class="fa fa-arrow-right"></i>
                                </div>
                                <div class="col-sm-5 col-xs-5">
                                    <?= lang('point'); ?><br>
                                    <?= form_input('point_exchange', $pts->point_exchange, 'class="form-control"'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_points', lang('edit_points'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>

<?= $modal_js ?>