<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        var oTable = $('#CusDataAll').dataTable({
            "aaSorting": [[0, "asc"], [1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getpiutangall') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            // "deferRender":    true,
            // "scrollX": true,
            // "scroller":       true,
            "aoColumns": [null, null, null, null,
                {"mRender": decimalFormat,"bSearchable": false},
                {"mRender": currencyFormat, "bSearchable": false},
                {"mRender": currencyFormat,"bSearchable": false},
                {"mRender": currencyFormat, "bSearchable": false},
                {"mRender": currencyFormat, "bSearchable": false},
                {"mRender": currencyFormat, "bSearchable": false},
                {"mRender": currencyFormat, "bSearchable": false},
                {"mRender": currencyFormat, "bSearchable": false},
                {"bSortable": false}
            ],

            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var purchases = 0, total = 0, paid = 0, balance = 0, semua = 0, h15 = 0, h1530 = 0, h30 = 0 ;
                for (var i = 0; i < aaData.length; i++) {
                    purchases += parseFloat(aaData[aiDisplay[i]][4]);
                    total += parseFloat(aaData[aiDisplay[i]][5]);
                    paid += parseFloat(aaData[aiDisplay[i]][6]);
                    balance += parseFloat(aaData[aiDisplay[i]][7]);
                    semua += parseFloat(aaData[aiDisplay[i]][8]);
                    h15 += parseFloat(aaData[aiDisplay[i]][9]);
                    h1530 += parseFloat(aaData[aiDisplay[i]][10]);
                    h30 += parseFloat(aaData[aiDisplay[i]][11]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[4].innerHTML = decimalFormat(parseFloat(purchases));
                nCells[5].innerHTML = currencyFormat(parseFloat(total));
                nCells[6].innerHTML = currencyFormat(parseFloat(paid));
                nCells[7].innerHTML = currencyFormat(parseFloat(balance));
                nCells[8].innerHTML = currencyFormat(parseFloat(semua));
                nCells[9].innerHTML = currencyFormat(parseFloat(h15));
                nCells[10].innerHTML = currencyFormat(parseFloat(h1530));
                nCells[11].innerHTML = currencyFormat(parseFloat(h30));
                // nCells[8].innerHTML = currencyFormat(parseFloat(balance));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('company');?>]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('phone');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('email_address');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>

<ul  class="nav nav-tabs no-print">
    <li class="active"><a href="#" class="tab-grey"><?= lang('standard_sale') ?></a></li>
    <li class=""><a href="<?= site_url('reports/piutang/') ?>" class="tab-grey"><?= lang('aksestoko_sale') ?></a></li>
</ul>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('piutang_report'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="#" id="pdfall" class="tip" title="<?= lang('download_pdf') ?>"><i
                            class="icon fa fa-file-pdf-o"></i></a></li>
                <li class="dropdown"><a href="#" id="xlsall" class="tip" title="<?= lang('download_xls') ?>"><i
                            class="icon fa fa-file-excel-o"></i></a></li>
                <li class="dropdown"><a href="#" id="imageall" class="tip" title="<?= lang('save_image') ?>"><i
                            class="icon fa fa-file-picture-o"></i></a></li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('view_report_customer'); ?></p>
                <!--table-scroll-->
                <div class="table-responsive display nowrap" >
                    <table id="CusDataAll" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-condensed table-hover table-striped reports-table">
                        <thead>
                        <tr class="primary">
                            <th><?= lang("company"); ?></th>
                            <th><?= lang("name"); ?></th>
                            <th><?= lang("phone"); ?></th>
                            <th><?= lang("email_address"); ?></th>
                            <th><?= lang("total_sales"); ?></th>
                            <th><?= lang("total_amount"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            <th><?= lang("semua"); ?></th>
                            <th><?= lang("h15"); ?></th>
                            <th><?= lang("h1530"); ?></th>
                            <th><?= lang("h30"); ?></th>
                            <th style="width:85px;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th class="text-center"><?= lang("total_sales"); ?></th>
                            <th class="text-center"><?= lang("total_amount"); ?></th>
                            <th class="text-center"><?= lang("paid"); ?></th>
                            <th class="text-center"><?= lang("balance"); ?></th>
                            <th class="text-center"><?= lang("total_jt"); ?></th>
                            <th class="text-center"><?= lang("total_h15"); ?></th>
                            <th class="text-center"><?= lang("total_h1530"); ?></th>
                            <th class="text-center"><?= lang("total_h30"); ?></th>
                            <th style="width:85px;"><?= lang("actions"); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getpiutang/pdf')?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getpiutang/0/xls')?>";
            return false;
        });
        $('#pdfall').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getpiutangall/pdf')?>";
            return false;
        });
        $('#xlsall').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getpiutangall/0/xls')?>";
            return false;
        });

    });
</script>