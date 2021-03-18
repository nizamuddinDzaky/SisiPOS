<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php

?>
<script>
    $(document).ready(function () {
        $('#SLData').dataTable({
            "aaSorting": [[0, "asc"], [1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/get_products_warehouse_by_date') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                url_=sSource;
                arrurl=url_.split("/");
                let end_date = $('#end_date').val();
                let start_date = $('#start_date').val();
                
                if (end_date == '') {
                    end_date = '-';
                }else{
                    end_date = end_date.replace(/\//g, '-');
                }

                if (start_date == '') {
                    start_date = '-';
                }else{
                    start_date = $('#start_date').val().replace(/\//g, '-');
                }

                arrurl=url_.split("/");
                for (var i = 0; i < arrurl.length; i++) {
                    if (arrurl[i] == 'get_products_warehouse_by_date') {
                        arrurl[i + 1] = start_date;
                        arrurl[i + 2] = end_date;
                        arrurl[i + 3] = $("#warehouse").val();
                    }
                }
                url_=arrurl.join().replace(/,/g,"/");

                $.ajax({'dataType': 'json', 'type': 'POST', 'url': url_, 'data': aoData, 'success': fnCallback});
                

                $('#start_date').change(function(){
                    let start_date = $(this).val().replace(/\//g, '-');
                    if  (start_date==''){
                        start_date = '-';
                    }
                    let end_date = $('#end_date').val();
                    if (end_date == '') {
                        end_date = '-';
                    }else{
                        end_date = end_date.replace(/\//g, '-');
                    }

                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });
                    arrayURL = url_.split("/");
                    for (var i = 0; i < arrayURL.length; i++) {
                        if (arrayURL[i] == 'get_products_warehouse_by_date') {
                            arrayURL[i + 1] = start_date;
                            arrayURL[i + 2] = end_date;
                            arrayURL[i + 3] = $('#warehouse').val();
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

                $('#end_date').change(function(){
                    let end_date = $(this).val().replace(/\//g, '-');
                    if  (end_date==''){
                        end_date = '-';
                    }
                    let start_date = $('#start_date').val();
                    if (start_date == '') {
                        start_date = '-';
                    }else{
                        start_date = $('#start_date').val().replace(/\//g, '-');
                    }

                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });
                    arrayURL = url_.split("/");
                    for (var i = 0; i < arrayURL.length; i++) {
                        if (arrayURL[i] == 'get_products_warehouse_by_date') {
                            arrayURL[i + 2] = end_date;
                            arrayURL[i + 1] = start_date;
                            arrayURL[i + 3] = $('#warehouse').val();
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

                $('#warehouse').change(function () {
                    let end_date = $('#end_date').val();
                    let start_date = $('#start_date').val();

                    if (end_date == '') {
                        end_date = '-';
                    }else{
                        end_date = end_date.replace(/\//g, '-');
                    }

                    if (start_date == '') {
                        start_date = '-';
                    }else{
                        start_date = $('#start_date').val().replace(/\//g, '-');
                    }


                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });
                    arrayURL = url_.split("/");
                    for (var i = 0; i < arrayURL.length; i++) {
                        if (arrayURL[i] == 'get_products_warehouse_by_date') {
                            arrayURL[i + 1] = start_date;
                            arrayURL[i + 2] = end_date;
                            arrayURL[i + 3] = $(this).val();
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
            },
            
            "aoColumns": [{"mRender": fldd},null,null,null,null,null,null,null,null],
        });

    });

</script>

<?php echo form_open("reports/get_products_warehouse_by_date"); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-shopping-cart"></i><?= $page_title ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" id="pdf" class="tip" title="<?= lang('download_pdf') ?>" data-action="export_pdf">
                        <i class="icon fa fa-file-pdf-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" id="excel" class="tip" title="<?= lang('download_xls') ?>" data-action="export_excel">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                

                <p class="introtext"><?php echo $this->lang->line("list_results"); ?></p>
                <div class="table-responsive">

                    <div class="form-group">

                        <div class="col-lg-12"  style="padding-left:inherit;padding-right:inherit;">
                            <label class="control-label" for="warehouse"><?= lang("Distributor"); ?></label>
                                <?php
                                $wh[""] = lang('select').' '.lang('warehouse');
                                foreach ($companies as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->company;
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("Distributor") . '"');
                                ?>
                        </div>
                        <div class="col-sm-6" style="padding-left:inherit;">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control date" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-6" style="padding-right:inherit">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control date" id="end_date"'); ?>
                            </div>
                        </div>
                    </div>
                    <table id="SLData" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr class="active">
                                <th><?= lang("date") ?></th>
                                <th><?= lang("warehouse_code"); ?></th>
                                <th><?= lang("provinsi"); ?></th>
                                <th><?= lang("distributor"); ?></th>
                                <th><?= lang("warehouse"); ?></th>
                                <th><?= lang("address"); ?></th>
                                <th><?= lang("product_code"); ?></th>
                                <th><?= lang("product_name"); ?></th>
                                <th><?= lang("Stock"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                            </tr>
                        </tbody>
                        
                    </table>
                </div>

            </div>

        </div>
    </div>
</div>
<div style="display: none;">
    <input type="hidden" name="form_action" value="" id="form_action"/>
    <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
</div>
<?= form_close() ?>