<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function() {
        var cTable = $('#CusData').dataTable({
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
            'sAjaxSource': '<?= site_url('reports/getSalesAssociate') ?>',
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
            }, null, null, null, null, null, null]
        }).dtFilter([{
                column_number: 1,
                filter_default_label: "[<?= lang('name'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 2,
                filter_default_label: "[<?= lang('referal_code'); ?>]",
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
                filter_default_label: "[<?= lang('email_address'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 5,
                filter_default_label: "[<?= lang('total_customer'); ?>]",
                filter_type: "text",
                data: []
            },

        ], "footer");
        $('#myModal').on('hidden.bs.modal', function() {
            cTable.fnDraw(false);
        });
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
        text-align: left;
        width: 25%;
    }

    .table td:nth-child(6) {
        text-align: center;
        width: 15%;
    }
</style>
<?php if ($Owner || $Admin || $GP['bulk_actions']) {
    echo form_open('reports/sales_person_report_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('sales_person_report'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <?php if ($Owner || $Admin || $GP['bulk_actions']) { ?>
                            <li>
                                <a href="#" id="excel" data-action="export_excel">
                                    <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                                </a>
                            </li>
                            <li>
                                <a href="#" id="pdf" data-action="export_pdf">
                                    <i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('list_results'); ?></p>

                <div class="table-responsive">
                    <table id="CusData" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                            <tr class="primary">
                                <th>
                                    <input class="checkbox checkth" type="checkbox" name="check" />
                                </th>
                                <th><?= lang("name"); ?></th>
                                <th><?= lang("referal_code"); ?></th>
                                <th><?= lang("phone"); ?></th>
                                <th><?= lang("email_address"); ?></th>
                                <th><?= lang("total_customer"); ?></th>
                                <th style="min-width:135px !important;"><?= lang("actions"); ?></th>
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
                                <th style="min-width:135px !important;" class="text-center"><?= lang("actions"); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $GP['bulk_actions']) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action" />
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>
<script type="text/javascript">
</script>