<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php

$v = "";
/* if($this->input->post('name')){
  $v .= "&product=".$this->input->post('product');
  } */
if ($this->input->post('product')) {
    $v .= "&product=" . $this->input->post('product');
}
if ($this->input->post('reference_no')) {
    $v .= "&reference_no=" . $this->input->post('reference_no');
}
if ($this->input->post('customer')) {
    $v .= "&customer=" . $this->input->post('customer');
}
if ($this->input->post('biller')) {
    $v .= "&biller=" . $this->input->post('biller');
}
if ($this->input->post('warehouse')) {
    $v .= "&warehouse=" . $this->input->post('warehouse');
}
if ($this->input->post('user')) {
    $v .= "&user=" . $this->input->post('user');
}
if ($this->input->post('serial')) {
    $v .= "&serial=" . $this->input->post('serial');
}
if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}

?>
<script>
    $(document).ready(function () {
        var oTable = $('#SlRData').dataTable({
            "aaSorting": [[0, "desc"]],
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            // "oLanguage": {
            //     "sProcessing": '<span class="wobblebar-loader">Loading�</span>'},
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getSalesReport/') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });


                URLtemp = sSource;
                arrayURL = URLtemp.split("/");

                <?php if ($v == null): ?>
                for (var i = 0; i < arrayURL.length; i++) {
                    if (arrayURL[i] == 'getSalesReport') {
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
                    $('#SlRData_processing').css('visibility', 'visible');
                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });
                    arrayURL = URLtemp.split("/");
                    for (var i = 0; i < arrayURL.length; i++) {
                        if (arrayURL[i] == 'getSalesReport') {
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
                    $('#SlRData_processing').css('visibility', 'visible');
                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });
                    arrayURL = URLtemp.split("/");
                    for (var i = 0; i < arrayURL.length; i++) {
                        if (arrayURL[i] == 'getSalesReport') {
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
                    if (arrayURL[i] == 'getSalesReport') {
                        arrayURL[i + 1] = $("#annually").val();
                        arrayURL[i + 2] = $("#monthly").val();
                    }
                }

                URLtemp = arrayURL.join().replace(/,/g, "/");

                // apabila bulan dan tahun di isi
                $('#annually').change(function () {
                    $('#SlRData_processing').css('visibility', 'visible');

                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });
                    arrayURL = URLtemp.split("/");
                    for (var i = 0; i < arrayURL.length; i++) {
                        if (arrayURL[i] == 'getSalesReport') {
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
                    $('#SlRData_processing').css('visibility', 'visible');
                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });
                    arrayURL = URLtemp.split("/");
                    for (var i = 0; i < arrayURL.length; i++) {
                        if (arrayURL[i] == 'getSalesReport') {
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
                //akhir jika value filter tidak kosong

                // $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[14]; 
                nRow.className = (aData[5] > 0) ? "invoice_link" : "invoice_link warning";
                return nRow;
            },
            "aoColumns": [{"mRender": fld}, null, null, null, null, null, null, null,{"bSearchable": false,"mRender": pqFormat}, {"mRender": row_status},{"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": row_status}],
            
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var gtotal = 0, paid = 0, balance = 0;
                for (var i = 0; i < aaData.length; i++) {
                    gtotal += parseFloat(aaData[aiDisplay[i]][10]);
                    paid += parseFloat(aaData[aiDisplay[i]][11]);
                    balance += parseFloat(aaData[aiDisplay[i]][12]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[10].innerHTML = currencyFormat(parseFloat(gtotal));
                nCells[11].innerHTML = currencyFormat(parseFloat(paid));
                nCells[12].innerHTML = currencyFormat(parseFloat(balance));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('warehouse_code');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('warehouse');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('customer_code');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
            {column_number: 7, filter_default_label: "[<?=lang('created_by');?>]", filter_type: "text", data: []},
            {column_number: 8, filter_default_label: "[<?=lang('product_qty');?>]", filter_type: "text", data: []},
            {
                column_number: 9, select_type: 'select2',
                select_type_options: {
                    placeholder: '<?= lang('sale_status'); ?>',
                    width: '100%',
                    style: 'width:100%;',
                    minimumResultsForSearch: -1,
                    allowClear: true
                },
                data: [{value: 'returned', label: '<?=lang('returned');?>'}, {value: 'pending', label: '<?=lang('pending');?>'}, {value: 'reserved', label: '<?=lang('reserved');?>'}, {value: 'confirmed', label: '<?=lang('confirmed');?>'}, {value: 'completed', label: '<?=lang('completed');?>'}, {value: 'closed', label: '<?=lang('closed');?>'}]
            },
            {
                column_number: 13, select_type: 'select2',
                select_type_options: {
                    placeholder: '<?= lang('payment_status'); ?>',
                    width: '100%',
                    style: 'width:100%;',
                    minimumResultsForSearch: -1,
                    allowClear: true
                },
                data: [{value: 'pending', label: '<?=lang('pending');?>'}, {value: 'partial', label: '<?=lang('partial');?>'}, {value: 'paid', label: '<?=lang('paid');?>'}, {value: 'waiting', label: '<?=lang('waiting');?>'}, {value: 'due', label: '<?=lang('due');?>'}]
            },
        ], "footer");
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#form').hide();
        <?php if ($this->input->post('customer')) { ?>
        $('#customer').val(<?= $this->input->post('customer') ?>).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url + "customers/suggestions/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        callback(data.results[0]);
                    }
                });
            },
            ajax: {
                url: site.base_url + "customers/suggestions",
                dataType: 'json',
                quietMillis: 15,
                data: function (term, page) {
                    return {
                        term: term,
                        limit: 10
                    };
                },
                results: function (data, page) {
                    if (data.results != null) {
                        return {results: data.results};
                    } else {
                        return {results: [{id: '', text: 'No Match Found'}]};
                    }
                }
            }
        });

        $('#customer').val(<?= $this->input->post('customer') ?>);
        <?php } ?>
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
    });
</script>

<input type="text" class="hidden" id="fieldname" value="">
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-heart"></i><?= lang('sales_report'); ?> <?php
            if ($this->input->post('start_date')) {
                echo lang('from').$this->sma->hrld($this->input->post('start_date')).lang('to').$this->sma->hrld($this->input->post('end_date'));
            }
            ?>
        </h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
            </ul>
        </div>
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
                <!-- <li class="dropdown">
                    <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                        <i class="icon fa fa-file-picture-o"></i>
                    </a>
                </li> -->
            </ul>
        </div>
    </div>
<?php echo form_open("reports/sales"); ?>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('customize_report'); ?></p>
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
                <div id="form">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("product", "suggest_product"); ?>
                                <?php echo form_input('sproduct', (isset($_POST['sproduct']) ? $_POST['sproduct'] : ""), 'class="form-control" id="suggest_product"'); ?>
                                <input type="hidden" name="product" value="<?= isset($_POST['product']) ? $_POST['product'] : "" ?>" id="report_product_id"/>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="reference_no"><?= lang("reference_no"); ?></label>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ""), 'class="form-control tip" id="reference_no"'); ?>

                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("created_by"); ?></label>
                                <?php
                                $us[""] = lang('select').' '.lang('user');
                                foreach ($users as $user) {
                                    $us[$user->id] = $user->first_name . " " . $user->last_name;
                                }
                                echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ""), 'class="form-control" id="user" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                                <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'class="form-control" id="customer" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="biller"><?= lang("biller"); ?></label>
                                <?php
                                $bl[""] = lang('select').' '.lang('biller');
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                }
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                                <?php
                                $wh[""] = lang('select').' '.lang('warehouse');
                                foreach ($warehouses as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->name;
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
                                ?>
                            </div>
                        </div>
                        <?php if($Settings->product_serial) { ?>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <?= lang('serial_no', 'serial'); ?>
                                    <?= form_input('serial', '', 'class="form-control tip" id="serial"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control datetime" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control datetime" id="end_date"'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div
                            class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    

                </div>
                <div class="clearfix"></div>

                <div class="table-responsive">
                    <div class="dataTables_wrapper form-inline" style="overflow:auto;">
                    <table id="SlRData"
                           class="table table-bordered table-hover table-striped table-condensed reports-table">
                        <thead>
                        <tr>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("reference_no"); ?></th>
                            <th><?= lang("biller"); ?></th>
                            <th><?= lang("warehouse_code"); ?></th>
                            <th><?= lang("warehouse"); ?></th>
                            <th><?= lang("customer_code"); ?></th>
                            <th><?= lang("customer"); ?></th>
                            <th><?= lang("created_by"); ?></th>
                            <th><?= lang("product_qty"); ?></th>
                            <th><?= lang("sale_status"); ?></th>
                            <th><?= lang("grand_total"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            <th><?= lang("payment_status"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="9" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
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
                            <th></th>
                            <th></th>
                            <th><?= lang("grand_total"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            <th></th>
                        </tr>
                        </tfoot>
                    </table>
                    </div>
                </div>
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
            window.location.href = "<?=site_url('reports/getSalesReport/')?>"+year+"/"+month+"/pdf/?v=1";
            <?php else: ?>
            if (!change){
            window.location.href = "<?=site_url('reports/getSalesReport/')?>"+"-/-/pdf/?v=1<?=$v?>";
            }else {
                window.location.href = "<?=site_url('reports/getSalesReport/')?>"+year+"/"+month+"/pdf/?v=1";
            }
            <?php endif; ?>
            return false;
            
        });
        $('#xls').click(function (event) {
            var change = document.getElementById("fieldname").value;
            var year = $('#annually').val();
            var month = $('#monthly').val();
            event.preventDefault();
            // console.log(asd);
            <?php if ($v == null): ?>
            window.location.href = "<?=site_url('reports/getSalesReport/')?>"+year+"/"+month+"/0/pdf/?v=1";
            <?php else: ?>
            if (!change){
            window.location.href = "<?=site_url('reports/getSalesReport/')?>"+"-/-/0/pdf/?v=1<?=$v?>";
            }else {
                window.location.href = "<?=site_url('reports/getSalesReport/')?>"+year+"/"+month+"/0/pdf/?v=1";
            }
            <?php endif; ?>
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
        $('#start_date').change(function(){
            if($('#start_date').val()){
                $('#end_date').attr('required',true);
            }else{
                $('#end_date').attr('required',false);
            }
        });
    });
</script>