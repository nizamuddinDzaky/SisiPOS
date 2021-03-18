<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_payment_method'); ?> <?= $company->company ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("system_settings/list_payment_method/" . $company->id, $attrib); ?>
        <div class="modal-body">

            <div class="table-responsive">
                <table id="CompTable" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-striped">
                    <thead>
                        <tr>
                            <th><?= lang('payment_method') ?></th>
                            <th><?= lang('active') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payment_method as $keyPm => $pm) {
                            $checked = '';
                            $key = array_search($pm->id, array_column($company_payment_method, 'payment_method_id'));
                            if (gettype($key) != 'boolean') {
                                if ($company_payment_method[$key]->is_active == 1)
                                    $checked = 'checked';
                            }
                        ?>
                            <tr>
                                <td><?= $pm->name ?></td>
                                <td class="text-center">
                                    <div class="form-group">
                                        <input type="checkbox" class="checkbox" name="active[<?= $pm->id ?>]" value="" <?= $checked ?> />
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

            </div>

            <div class="modal-footer">
                <?php echo form_submit('deactivate', lang('submit'), 'class="btn btn-primary"'); ?>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<?= $modal_js ?>