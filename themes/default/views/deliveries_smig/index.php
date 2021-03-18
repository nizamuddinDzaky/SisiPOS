<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function() {
        var oTable = $('#QUData').dataTable({
            "aaSorting": [
                [1, "desc"]
            ],
            "aLengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "<?= lang('all') ?>"]
            ],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true,
            'bServerSide': true,
            'sAjaxSource': '<?= site_url('deliveries_smig/getdeliveries_smig' . ($warehouse_id ? '/' . $warehouse_id : '')) ?>',
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
            'fnRowCallback': function(nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.className = "deliveries_smig_link";
                return nRow;
            },
            "aoColumns": [{
                "bSortable": false,
                "mRender": checkbox
            }, {
                "mRender": fldd
            }, null, null, null, null, null, null, {
                "mRender": row_status
            }, {
                "bSortable": false
            }],
        }).fnSetFilteringDelay().dtFilter([{
                column_number: 1,
                filter_default_label: "[<?= lang('date'); ?> (yyyy-mm-dd)]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 2,
                filter_default_label: "[<?= lang("So_Number"); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 3,
                filter_default_label: "[<?= lang('Do_Number'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 4,
                filter_default_label: "[<?= lang('Spj_Number'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 5,
                filter_default_label: "[<?= lang('Quantity'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 6,
                filter_default_label: "[<?= lang('Police_No'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 7,
                filter_default_label: "[<?= lang('Driver_Name'); ?>]",
                filter_type: "text",
                data: []
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
                    value: 'delivering',
                    label: '<?= lang('delivering'); ?>'
                }, {
                    value: 'received',
                    label: '<?= lang('received'); ?>'
                }]
            },
        ], "footer");

        var old_sent;
        $(document).on("focus", '.sent', function() {
            old_sent = $(this).val();
        }).on("change", '.sent', function() {
            var new_sent = $(this).val() ? $(this).val() : 1;
            if (!is_numeric(new_sent)) {
                $(this).val(old_sent);
                return;
            } else if (new_sent > $(this).data('remaining')) {
                $(this).val($(this).data('remaining'));
                return;
            } else if (new_sent < 1) {
                $(this).val(1);
                return;
            }
        });
        <?php if ($this->session->userdata('remove_quls')) { ?>
            if (localStorage.getItem('quitems')) {
                localStorage.removeItem('quitems');
            }
            if (localStorage.getItem('quref')) {
                localStorage.removeItem('quref');
            }
            if (localStorage.getItem('quwarehouse')) {
                localStorage.removeItem('quwarehouse');
            }
            if (localStorage.getItem('qusupplier')) {
                localStorage.removeItem('qusupplier');
            }
            if (localStorage.getItem('qunote')) {
                localStorage.removeItem('qunote');
            }
            if (localStorage.getItem('qudate')) {
                localStorage.removeItem('qudate');
            }
            if (localStorage.getItem('qustatus')) {
                localStorage.removeItem('qustatus');
            }
        <?php $this->sma->unset_data('remove_quls');
        } ?>
    });
</script>

<?php if ($Owner || $GP['bulk_actions']) {
    echo form_open('deliveries_smig/deliveries_smig_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa fa-truck"></i><?= lang('Confirmation_Delivery') . ' (' . ($warehouse_id ? $warehouse->name : lang('all_warehouses')) . ')'; ?>
        </h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i></a>
                    <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="#" id="excel" data-action="export_excel"><i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="pdf" data-action="export_pdf"><i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="combine" data-action="combine">
                                <i class="fa fa-file-pdf-o"></i> <?= lang('combine_to_pdf') ?>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php if (!empty($warehouses)) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                        <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= site_url('deliveries_smig') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($warehouses as $warehouse) {
                                echo '<li><a href="' . site_url('deliveries_smig/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <?php echo anchor('deliveries_smig/search_delivery', '<i class="icon fa fa-refresh tip" data-placement="left" title="' . lang("synchron") . '"></i> ', 'data-toggle="modal" data-target="#myModal"  data-backdrop="static"') ?>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('new_feature_smig'); ?></p>

                <div class="table-responsive">
                    <table id="QUData" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr class="active">
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkft" type="checkbox" name="check" />
                                </th>
                                <th><?= lang("date"); ?></th>
                                <th><?= lang("So_Number"); ?></th>
                                <th><?= lang("Do_Number"); ?></th>
                                <th><?= lang("Spj_Number"); ?></th>
                                <th><?= lang("Quantity"); ?></th>
                                <th><?= lang("Police_No"); ?></th>
                                <th><?= lang("Driver_Name"); ?></th>
                                <th><?= lang("Status"); ?></th>
                                <th style="width:115px; text-align:center;"><?= lang("actions"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="10" class="dataTables_empty"><?= lang("loading_data"); ?></td>
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
                                <th style="width:115px; text-align:center;"><?= lang("actions"); ?></th>
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