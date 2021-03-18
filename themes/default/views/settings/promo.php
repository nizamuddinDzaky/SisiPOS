<?php

/* 
 * Copyright (c) 2017 adminSISI.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    adminSISI - initial API and implementation and/or initial documentation
 */
?>
<script>
    $(document).ready(function() {
        $('#PromoTable').dataTable({
            "aaSorting": [
                [3, "asc"]
            ],
            "aLengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "<?= lang('all') ?>"]
            ],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true,
            'bServerSide': true,
            'sAjaxSource': '<?= site_url('system_settings/getPromo') ?>',
            'fnServerData': function(sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({
                    'dataType': 'json',
                    'type': 'POST',
                    'url': sSource,
                    'data': aoData,
                    'success': fnCallback
                });
            },
            "aoColumns": [{
                "bSortable": false,
                "mRender": checkbox
            }, {
                "mRender": fld
            }, null, null, {
                "mRender": fsd
            }, {
                "mRender": fsd
            }, {
                "bSortable": false
            }]
        });
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue">
            <i class="fa-fw fa fa-barcode"></i> List Promo
        </h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a title="<?= lang('add_promo') ?>" class="tip" href="<?php echo site_url('system_settings/add_promo'); ?>">
                        <i class="icon fa fa-plus"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table id="PromoTable" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr class="active">
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkft" type="checkbox" name="check" />
                                </th>
                                <th><?= lang("date"); ?></th>
                                <th><?= lang("title"); ?></th>
                                <th><?= lang("code_promo"); ?></th>
                                <th><?= lang("start_date"); ?></th>
                                <th><?= lang("end_date"); ?></th>
                                <th style="width:100px;"><?= lang("actions"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="11" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                            </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                            <tr class="active">
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkft" type="checkbox" name="check" />
                                </th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th style="width:100px; text-align: center;"><?= lang("actions"); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>