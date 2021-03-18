<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
$v = "&customer=" . $user_id;
if ($this->input->get('aksestoko')) {
    $v .= "&aksestoko=" . $this->input->get('aksestoko');
}
//echo $v;
?>

<script>
    $(document).ready(function () {
        var oTable = $('#CusData').dataTable({
            "aaSorting": [[0, "asc"], [1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getpiutangdetails/?' . $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            // "scrollX": true,
            "aoColumns": [{"mRender" : fldd},
                null,
                {"mRender": currencyFormat,"bSearchable": false},
                {"mRender": currencyFormat,"bSearchable": false},
                {"mRender": currencyFormat, "bSearchable": false},
                {"bSortable": false},{"mRender" : fldd},null
            ],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var purchases = 0, total = 0, paid = 0, balance = 0;
                for (var i = 0; i < aaData.length; i++) {
                    total += parseFloat(aaData[aiDisplay[i]][2]);
                    paid += parseFloat(aaData[aiDisplay[i]][3]);
                    balance += parseFloat(aaData[aiDisplay[i]][4]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[2].innerHTML = currencyFormat(parseFloat(total));
                nCells[3].innerHTML = currencyFormat(parseFloat(paid));
                nCells[4].innerHTML = currencyFormat(parseFloat(balance));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('date');?>]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('top');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('tanggal_jt');?>]", filter_type: "text", data: []},
            {column_number: 7, filter_default_label: "[<?=lang('tanggal_jt');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('piutang_report'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="#" id="pdf" class="tip" title="<?= lang('download_pdf') ?>"><i
                            class="icon fa fa-file-pdf-o"></i></a></li>
                <li class="dropdown"><a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i
                            class="icon fa fa-file-excel-o"></i></a></li>
                <li class="dropdown"><a href="#" id="image" class="tip" title="<?= lang('save_image') ?>"><i
                            class="icon fa fa-file-picture-o"></i></a></li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('piutang_report'); ?></p>

                        <div class="border-report">
                            <table>
                                <tr>
                                    <td width="100"><?= lang('name') ?></td>
                                    <td width="20">:</td>
                                    <td width="200"><?= $name_company?></td>
                                </tr>
                                <tr>
                                    <td width="100"><?= lang('company') ?></td>
                                    <td width="20">:</td>
                                    <td width="200"><?= $compann_company?></td>
                                </tr>
                                <tr>
                                    <td width="100"><?= lang('phone') ?></td>
                                    <td width="20">:</td>
                                    <td width="200"><?= $phone_company?></td>
                                </tr>
                                <tr>
                                    <td width="100"><?= lang('date_now') ?></td>
                                    <td width="20">:</td>
                                    <td width="200"><?= date("d/m/Y")?></td>
                                </tr>
                            </table>

                        </div>


                <!--table-scroll-->
                <div class="table-responsive display nowrap">
                    <table id="CusData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-condensed table-hover table-striped reports-table">
                        <thead>
                        <tr class="primary">
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("reference_no"); ?></th>
                            <th><?= lang("total_amount"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            <th><?= lang("top"); ?></th>
                            <th><?= lang("tanggal_jt"); ?></th>
                            <th><?= lang("sejak"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="12" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th></th>
                            <th></th>
                            <th class="text-center"><?= lang("total_amount"); ?></th>
                            <th class="text-center"><?= lang("paid"); ?></th>
                            <th class="text-center"><?= lang("balance"); ?></th>
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
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?= site_url('reports/getpiutangdetails/pdf/?' . $v) ?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?= site_url('reports/getpiutangdetails/0/excel/?' . $v) ?>";
            return false;
        });
        $('#image').click(function (event) {
            event.preventDefault();
            html2canvas($('.box'), {
                onrendered: function (canvas) {
                    var img = canvas.toDataURL()
                    window.open(img);
                }
            });
            return false;
        });
    });
</script>