<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $v = ""; ?>
<style type="text/css">
#DeliveryData th:nth-child(5) {
    width: 12%;
}
#DeliveryData th:nth-child(6) {
    width: 20%;
}
#DeliveryData th:nth-child(7) {
    width: 8%;
}
</style>
<script>
    $(document).ready(function () {
        var dss = <?= json_encode(array('packing' => lang('packing'), 'delivering' => lang('delivering'), 'delivered' => lang('delivered'))); ?>;
        function ds(x) {
            if (x == 'delivered') {
                return '<div class="text-center"><span class="label label-success">'+(dss[x] ? dss[x] : x)+'</span></div>';
            } else if (x == 'delivering') {
                return '<div class="text-center"><span class="label label-primary">'+(dss[x] ? dss[x] : x)+'</span></div>';
            } else if (x == 'packing') {
                return '<div class="text-center"><span class="label label-warning">'+(dss[x] ? dss[x] : x)+'</span></div>';
            } else if (x == 'returned') {
                return '<div class="text-center"><span class="label label-danger">'+(dss[x] ? dss[x] : x)+'</span></div>';
            }
            return x;
            return (x != null) ? (dss[x] ? dss[x] : x) : x;
        }
        var oTable = $('#DeliveryData').dataTable({
            "aaSorting": [[1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getDeliveriesReport/') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });


                URLtemp = sSource;
                arrayURL = URLtemp.split("/");

                // jika value filter form kosong
                <?php if ($v == null): ?>
                for (var i = 0; i < arrayURL.length; i++) {
                    if (arrayURL[i] == 'getDeliveriesReport') {
                        arrayURL[i + 1] = $("#annually").val();
                        arrayURL[i + 2] = $("#monthly").val();
                    }
                }

                URLtemp = arrayURL.join().replace(/,/g, "/");

                $.ajax({
                    'dataType': 'json',
                    'type': 'POST',
                    'url': URLtemp,
                    'data': aoData,
                    'success': fnCallback
                });

                $('#annually').change(function () {
                    $('#DeliveryData_processing').css('visibility', 'visible');
                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });
                    arrayURL = URLtemp.split("/");
                    for (var i = 0; i < arrayURL.length; i++) {
                        if (arrayURL[i] == 'getDeliveriesReport') {
                            arrayURL[i + 1] = $(this).val();
                        }
                    }
                    URLtemp = arrayURL.join().replace(/,/g, "/");
                    $.ajax({
                        "type": "POST",
                        "dataType": 'json',
                        "url": URLtemp, //sending server side status and filtering table
                        "data": aoData,
                        "success": function (data) {
                            fnCallback(data);
                        }
                    });
                });

                $('#monthly').change(function () {
                    $('#DeliveryData_processing').css('visibility', 'visible');
                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });
                    arrayURL = URLtemp.split("/");
                    for (var i = 0; i < arrayURL.length; i++) {
                        if (arrayURL[i] == 'getDeliveriesReport') {
                            arrayURL[i + 2] = $(this).val();
                        }
                    }
                    URLtemp = arrayURL.join().replace(/,/g, "/");
                    //URLtemp = URLtemp+'<?//= '?v=1'.$v?>//';
                    $.ajax({
                        "type": "POST",
                        "dataType": 'json',
                        "url": URLtemp, //sending server side status and filtering table
                        "data": aoData,
                        "success": function (data) {
                            fnCallback(data);
                        }
                    });
                });
                // akhir value filter kosong

                // jika value filter tidak kosong
                <?php else: ?>
                URLtemp = arrayURL.join().replace(/,/g, "/");

                URLtemp = URLtemp+'<?= '?v=1'.$v?>';

                $.ajax({
                    'dataType': 'json',
                    'type': 'POST',
                    'url': URLtemp,
                    'data': aoData,
                    'success': fnCallback
                });

                URLtemp = sSource;

                // mereset url menjadi default lagi
                for (var i = 0; i < arrayURL.length; i++) {
                    if (arrayURL[i] == 'getDeliveriesReport') {
                        arrayURL[i + 1] = $("#annually").val();
                        arrayURL[i + 2] = $("#monthly").val();
                    }
                }

                URLtemp = arrayURL.join().replace(/,/g, "/");

                // apabila bulan dan tahun di isi
                $('#annually').change(function () {
                    $('#DeliveryData_processing').css('visibility', 'visible');

                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });
                    arrayURL = URLtemp.split("/");
                    for (var i = 0; i < arrayURL.length; i++) {
                        if (arrayURL[i] == 'getDeliveriesReport') {
                            arrayURL[i + 1] = $(this).val();
                        }
                    }
                    URLtemp = arrayURL.join().replace(/,/g, "/");
                    $.ajax({
                        "type": "POST",
                        "dataType": 'json',
                        "url": URLtemp, //sending server side status and filtering table
                        "data": aoData,
                        "success": function (data) {
                            fnCallback(data);
                        }
                    });
                });

                $('#monthly').change(function () {
                    $('#DeliveryData_processing').css('visibility', 'visible');
                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });
                    arrayURL = URLtemp.split("/");
                    for (var i = 0; i < arrayURL.length; i++) {
                        if (arrayURL[i] == 'getDeliveriesReport') {
                            arrayURL[i + 2] = $(this).val();
                        }
                    }
                    URLtemp = arrayURL.join().replace(/,/g, "/");
                    //URLtemp = URLtemp+'<?//= '?v=1'.$v?>//';
                    $.ajax({
                        "type": "POST",
                        "dataType": 'json',
                        "url": URLtemp, //sending server side status and filtering table
                        "data": aoData,
                        "success": function (data) {
                            fnCallback(data);
                        }
                    });
                });

                <?php endif; ?>

                // $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.className = "delivery_link2";
                return nRow;
            },
            "aoColumns": [{"bSortable": false, "bVisible": false, "mRender": checkbox}, {"mRender": fld}, null, null, null, {"bSearchable": true,"mRender": pqFormat}, {"bSortable": false}, {"mRender": ds}]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('do_reference_no');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('sale_reference_no');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('product_qty');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('address');?>]", filter_type: "text", data: []},
            // {column_number: 7, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
            {
                column_number: 7, select_type: 'select2',
                select_type_options: {
                    placeholder: '<?=lang('status');?>',
                    width: '100%',
                    style: 'width:100%;',
                    minimumResultsForSearch: -1,
                    allowClear: true
                },
                data: [{value: 'packing', label: '<?=lang('packing');?>'}, {value: 'delivering', label: '<?=lang('delivering');?>'}, {value: 'delivered', label: '<?=lang('delivered');?>'}]
            }
        ], "footer");
    });
</script>
<input type="text" class="hidden" id="fieldname" value="">
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-truck"></i><?= lang('deliveries_report'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" id="pdf" class="tip" title="<?= lang('download_pdf') ?>">
                        <i class="icon fa fa-file-pdf-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
<?php echo form_open("reports/deliveries"); ?>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div class="form-group">
                    <div class="col-lg-9" style="padding-left:inherit; margin-bottom:10px;">
                        <?php
                        $opts = array('1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April', '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus', '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember');
                        echo form_dropdown('monthly', $opts, (isset($_POST['monthly']) ? $_POST['monthly'] : date('m')), 'class="form-control" id="monthly" required="required"');
                        ?>
                    </div>
                    <div class="col-lg-3" style="padding-right:inherit">
                        <?php
                        for ($y = date('Y'); $y >= 1990; $y--) {
                            $opts_year[$y] = $y;
                        }
                        echo form_dropdown('annually', $opts_year, (isset($_POST['annually']) ? $_POST['annually'] : date('Y')), 'class="form-control" id="annually" required="required"');
                        ?>
                    </div>
                </div>
                <table id="DeliveryData" class="table table-bordered table-hover table-striped table-condensed">
                    <thead>
                    <tr>
                        <th style="min-width:30px; width: 30px; text-align: center;">
                            <input class="checkbox checkft" type="checkbox" name="check"/>
                        </th>
                        <th><?= lang("date"); ?></th>
                        <th><?= lang("do_reference_no"); ?></th>
                        <th><?= lang("sale_reference_no"); ?></th>
                        <th><?= lang("customer"); ?></th>
                        <th><?= lang("product_qty"); ?></th>
                        <th><?= lang("address"); ?></th>
                        <th><?= lang("status"); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td colspan="7" class="dataTables_empty"><?= lang("loading_data"); ?></td>
                    </tr>
                    </tbody>
                    <tfoot class="dtFilter">
                    <tr class="active">
                        <th style="min-width:30px; width: 30px; text-align: center;">
                            <input class="checkbox checkft" type="checkbox" name="check"/>
                        </th>
                        <th></th><th></th><th></th><th></th><th></th><th></th><th></th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
<?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#monthly').change(function () {
            document.getElementById("fieldname").value = "ihjkh";
            console.log('monthly')
        });
        $('#annually').change(function () {
            document.getElementById("fieldname").value = "ihjkh";
            console.log('annualy')
        });

        $('#pdf').click(function (event) {
            var change = document.getElementById("fieldname").value;
            var year = $('#annually').val();
            var month = $('#monthly').val();
            event.preventDefault();

            <?php if ($v == null): ?>
            window.location.href = "<?=site_url('reports/getDeliveriesReport/')?>"+year+"/"+month+"/pdf/?v=1";
            <?php else: ?>
            if (!change){
            window.location.href = "<?=site_url('reports/getDeliveriesReport/')?>"+"-/-/pdf/?v=1<?=$v?>";
            }else {
                window.location.href = "<?=site_url('reports/getDeliveriesReport/')?>"+year+"/"+month+"/pdf/?v=1";
            }
            <?php endif; ?>
            console.log(asd);
            return false;
            
        });
        $('#xls').click(function (event) {
            var change = document.getElementById("fieldname").value;
            var year = $('#annually').val();
            var month = $('#monthly').val();
            event.preventDefault();
            // console.log(asd);
            <?php if ($v == null): ?>
            window.location.href = "<?=site_url('reports/getDeliveriesReport/')?>"+year+"/"+month+"/0/pdf/?v=1";
            <?php else: ?>
            if (!change){
            window.location.href = "<?=site_url('reports/getDeliveriesReport/')?>"+"-/-/0/pdf/?v=1<?=$v?>";
            }else {
                window.location.href = "<?=site_url('reports/getDeliveriesReport/')?>"+year+"/"+month+"/0/pdf/?v=1";
            }
            <?php endif; ?>
            return false;
        });
    });
</script>
