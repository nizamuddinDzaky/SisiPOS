<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function() {
        var oTable = $('#BLData').dataTable({
            "aaSorting": [
                [4, "desc"]
            ],
            "aLengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "<?= lang('all') ?>"]
            ],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true,
            'bServerSide': true,
            'sAjaxSource': '<?= base_url('reports/getBillingReport') ?>',
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
            "aoColumns": [null, null, null, {
                "mRender": fsd
            }, {
                "mRender": fsd
            }, {
                "bSearchable": false
            }, {
                "bSearchable": false
            }, {
                "bSearchable": false
            }, {
                "bSearchable": false,
                "mRender": currencyFormat
            }, {
                "mRender": mount
            }],
            "fnFooterCallback": function(nRow, aaData, iStart, iEnd, aiDisplay) {
                var gtotal = 0;
                for (var i = 0; i < aaData.length; i++) {
                    gtotal += parseFloat(aaData[aiDisplay[i]][8]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[8].innerHTML = currencyFormat(parseFloat(gtotal));
                // nCells[7].innerHTML = currencyFormat(parseFloat(balance));
            }
        }).fnSetFilteringDelay().dtFilter([{
                column_number: 0,
                filter_default_label: "[<?= lang('distributor_code'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 1,
                filter_default_label: "[<?= lang('company'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 2,
                filter_default_label: "[<?= lang('plan_name'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 3,
                filter_default_label: "[<?= lang('start_date'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 4,
                filter_default_label: "[<?= lang('expired_date'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 5,
                filter_default_label: "[<?= lang('jumlah_gudang'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 6,
                filter_default_label: "[<?= lang('jumlah_pengguna'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 7,
                filter_default_label: "[<?= lang('jumlah_transaksi'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 9,
                filter_default_label: "[<?= lang('bulan_tagihan'); ?>]",
                filter_type: "text",
                data: []
            },
        ], "footer");
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-line-chart"></i><?= lang('billing_report'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="table-responsive display nowrap">
                <table id="BLData" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-striped table-condensed">
                    <thead>
                        <tr>
                            <th><?= lang("kode_distributor"); ?></th>
                            <th width="40%"><?= lang("company"); ?></th>
                            <th><?= lang("plan_name"); ?></th>
                            <th><?= lang("start_date"); ?></th>
                            <th><?= lang("expired_date"); ?></th>
                            <th width="10%"><?= lang("jumlah_gudang"); ?></th>
                            <th width="10%"><?= lang("jumlah_pengguna"); ?></th>
                            <th width="10%"><?= lang("jumlah_transaksi"); ?></th>
                            <th><?= lang("price"); ?></th>
                            <th><?= lang("bulan_tagihan"); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="10" class="dataTables_empty"><?= lang("loading_data"); ?></td>
                        </tr>
                    </tbody>
                    <tfoot class="dtFilter">
                        <tr class="active">
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th><?= lang("paid"); ?></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('#xls').click(function(event) {
            window.location.href = "<?= site_url('reports/getBillingReport/') ?>" + "pdf";
            return false;
        });
    });
</script>