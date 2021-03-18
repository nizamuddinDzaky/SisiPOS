<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function() {
        var oTable = $('#myTable').dataTable({
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
            'sAjaxSource': '<?= site_url('billing_portal/plugin/getPlugin') ?>',
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
                return nRow;
            },
            "fnCreatedRow": function (row, data, index) {
                $('td', row).eq(0).html(index + 1);
            },
            "aoColumns": [{"bSortable": false}, null, {"mRender": currencyFormat}, {"mRender": status_plugin}, {"bSearchable": false, "bSortable": false}]
        });
    });
</script>
    <!-- Data Table area Start-->
    <div class="data-table-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="data-table-list">
                        <div class="basic-tb-hd">
                            <h2><?= $title ?></h2>
                        </div>
                        <div class="table-responsive">
                            <table id="myTable" class="table table-bordered table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th style="width: 10px;">No</th>
                                        <th>Name</th>
                                        <th>Price</th>
                                        <!-- <th>Type</th>
                                        <th>Unit</th>
                                        <th>Quota</th>
                                        <th>Link</th> -->
                                        <th>Active</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody><tr><td colspan="5" class="dataTables_empty"><?= lang("loading_data"); ?></td></tr></tbody>
                                <tfoot class="dtFilter">
                                    <tr class="active">
                                        <th>No</th>
                                        <th>Name</th>
                                        <th>Price</th>
                                        <!-- <th>Type</th>
                                        <th>Unit</th>
                                        <th>Quota</th>
                                        <th>Link</th> -->
                                        <th>Active</th>
                                        <th>Action</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Data Table area End-->