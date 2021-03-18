<script>
    $(document).ready(function() {
        'use strict';
        var oTable = $('#ListAllUsrTable').dataTable({
            "aaSorting": [
                [2, "asc"],
                [3, "asc"]
            ],
            "aLengthMenu": [
                [10, 25, 50, 100, 500],
                [10, 25, 50, 100, 500]
            ],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true,
            'bServerSide': true,
            'sAjaxSource': '<?= site_url('auth/getListAllUsers') ?>',
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
            }, null, null, null, null, null, null, {
                "mRender": user_type
            }, {
                "mRender": all_user_status
            }, {
                "bSortable": false
            }]
        }).fnSetFilteringDelay().dtFilter([{
                column_number: 1,
                filter_default_label: "[<?= lang('username'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 2,
                filter_default_label: "[<?= lang('email_address'); ?>]",
                filter_type: "text",
                data: []
            }, {
                column_number: 3,
                filter_default_label: "[<?= lang('first_name'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 4,
                filter_default_label: "[<?= lang('last_name'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 5,
                filter_default_label: "[<?= lang('company'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 6,
                filter_default_label: "[<?= lang('group'); ?>]",
                filter_type: "text",
                data: []
            }, {
                column_number: 7,
                select_type: 'select2',
                select_type_options: {
                    placeholder: '<?= lang('type'); ?>',
                    width: '100%',
                    style: 'width:100%;',
                    minimumResultsForSearch: -1,
                    allowClear: true
                },
                data: [{
                    value: '1',
                    label: '<?= lang('AksesToko'); ?>'
                }, {
                    value: '0',
                    label: '<?= lang('ForcaPOS'); ?>'
                }]
            },
            {
                column_number: 8,
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
            }
        ], "footer");
    });
</script>
<style>
    .table td:nth-child(4) {
        text-align: left;
        text-transform: uppercase;
    }

    .table td:nth-child(5) {
        text-align: left;
        text-transform: uppercase;
    }

    .table td:nth-child(6) {
        text-align: left;
        text-transform: uppercase;
        width: 10%;
    }

    .table td:nth-child(7) {
        text-align: left;
        text-transform: uppercase;
        width: 10%;
    }

    .table td:nth-child(8) {
        text-align: center;
    }

    .table td:nth-child(9) {
        text-align: center;
    }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('list_all_users'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>

                <div class="table-responsive">
                    <table id="ListAllUsrTable" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                            <tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check" />
                                </th>
                                <th><?php echo lang('username'); ?></th>
                                <th><?php echo lang('email_address'); ?></th>
                                <th><?php echo lang('first_name'); ?></th>
                                <th><?php echo lang('last_name'); ?></th>
                                <th><?php echo lang('company'); ?></th>
                                <th><?php echo lang('group'); ?></th>
                                <th><?php echo lang('type'); ?></th>
                                <th><?php echo lang('status'); ?></th>
                                <th style="min-width:100px !important;"><?php echo lang('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="10" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
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
                                <th></th>
                                <th></th>
                                <th style="width:85px;">
                                    <center><?= lang("actions"); ?></center>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>

        </div>
    </div>
</div>