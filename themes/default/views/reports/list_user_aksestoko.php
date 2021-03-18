<!-- <?php

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
  <-- Nav tabs -->

<script>
    $(document).ready(function() {
        'use strict';
        var oTable = $('#UsrTable').dataTable({
            "aaSorting": [
                [2, "asc"],
                [3, "asc"]
            ],
            "aLengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "<?= lang('all') ?>"]
            ],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true,
            'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getListUserAksesToko') ?>',
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
                "bVisible": false,
                "mRender": checkbox
            }, null, null, null, null, {
                "mRender": user_aksestoko_status
            }, {
                "mRender": phone_aksestoko_status
            }, {
                "bSortable": false
            }]
        }).fnSetFilteringDelay().dtFilter([{
                column_number: 1,
                filter_default_label: "[<?= lang('customer_code'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 2,
                filter_default_label: "[<?= lang('company'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 3,
                filter_default_label: "[<?= lang('email_address'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 4,
                filter_default_label: "[<?= lang('phone'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 5,
                select_type: 'select2',
                select_type_options: {
                    placeholder: '<?= lang('status'); ?>',
                    width: '100%',
                    style: 'width:100%;',
                    minimumResultsForSearch: -1,
                    allowClear: true
                },
                data: [{
                    value: '1',
                    label: '<?= lang('active'); ?>'
                }, {
                    value: '0',
                    label: '<?= lang('inactive'); ?>'
                }]
            },
            {
                column_number: 6,
                select_type: 'select2',
                select_type_options: {
                    placeholder: '<?= lang('phone_status'); ?>',
                    width: '100%',
                    style: 'width:100%;',
                    minimumResultsForSearch: -1,
                    allowClear: true
                },
                data: [{
                    value: '1',
                    label: '<?= lang('verified'); ?>'
                }, {
                    value: '0',
                    label: '<?= lang('unverified'); ?>'
                }]
            }
            // {column_number: 5, filter_default_label: "[<?= lang('status'); ?>]", filter_type: "text", data: []},
            // {column_number: 6, filter_default_label: "[<?= lang('phone_status'); ?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>
<style>
    .table td:nth-child(6) {
        text-align: right;
        width: 10%;
    }

    .table td:nth-child(8) {
        text-align: center;
    }
</style>
<?php //if ($Owner) {
echo form_open("reports/getListUserAksesToko");
//} 
?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('reports_list_user_aksestoko'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" id="excel" class="tip" title="<?= lang('download_xls') ?>" data-action="export_excel">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>

                <div class="table-responsive">
                    <table id="UsrTable" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check" />
                                </th>
                                <th class="col-xs-2">ID Bisnis Kokoh</th>
                                <th class="col-xs-2"><?php echo lang('company'); ?></th>
                                <th class="col-xs-2"><?php echo lang('email_address'); ?></th>
                                <th class="col-xs-2"><?php echo lang('phone'); ?></th>
                                <th style="width:100px;"><?php echo lang('status'); ?></th>
                                <th class="col-xs-1"><?php echo lang('phone_status'); ?></th>
                                <th class="col-xs-1"><?php echo lang('address'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
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
                                <th></th>
                                <th><?= lang('address'); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>

        </div>
    </div>
</div>
<?php
// if ($Owner) { 
?>
<div style="display: none;">
    <input type="hidden" name="form_action" value="" id="form_action" />
    <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
</div>
<?= form_close() ?>

<script language="javascript">
    $(document).ready(function() {
        $('#set_admin').click(function() {
            $('#usr-form-btn').trigger('click');
        });

    });
</script>