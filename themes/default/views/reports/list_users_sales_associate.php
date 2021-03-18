<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function() {
        var cTable = $('#ListData').dataTable({
            "aaSorting": [
                [1, "asc"]
            ],
            "aLengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "<?= lang('all') ?>"]
            ],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true,
            'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getListUsersSalesAssociate?sp=' . $id) ?>',
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
                "mRender": itd
            }, {
                "mRender": sales_person_status
            }]
        }).dtFilter([{
                column_number: 1,
                filter_default_label: "[<?= lang('company'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 2,
                filter_default_label: "[<?= lang('name'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 3,
                filter_default_label: "[<?= lang('phone'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 4,
                filter_default_label: "[<?= lang('customer_code'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 5,
                filter_default_label: "[<?= lang('created_at'); ?>]",
                filter_type: "text",
                data: []
            }, {
                column_number: 6,
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
    .table td:nth-child(2) {
        text-align: left;
        width: 15%;
    }

    .table td:nth-child(3) {
        text-align: left;
        width: 15%;
    }

    .table td:nth-child(4) {
        text-align: left;
        width: 15%;
    }

    .table td:nth-child(5) {
        text-align: center;
        width: 15%;
    }

    .table td:nth-child(6) {
        text-align: center;
        width: 15%;
    }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('list_customer'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table id="ListData" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                            <tr class="primary">
                                <th>
                                    <input class="checkbox checkth" type="checkbox" name="check" />
                                </th>
                                <th><?= lang("company"); ?></th>
                                <th><?= lang("name"); ?></th>
                                <th><?= lang("phone"); ?></th>
                                <th><?= lang("customer_code"); ?></th>
                                <th><?= lang("registered"); ?></th>
                                <th><?= lang("status"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="10" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                            </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                            <tr class="active">
                                <th style="text-align: center;">
                                    <input class="checkbox checkft" type="checkbox" name="check" />
                                </th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript"></script>