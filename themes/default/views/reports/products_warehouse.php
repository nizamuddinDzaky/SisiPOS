<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php

?>
<script>
    $(document).ready(function() {
        $('#SLData').dataTable({
            "aaSorting": [
                [0, "asc"],
                [1, "desc"]
            ],
            "aLengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "<?= lang('all') ?>"]
            ],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true,
            'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/get_products_warehouse/') ?>' + $('#companies').val() + '/' + $('#warehouse').val(),
            'fnServerData': function(sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                URLtemp = sSource;
                arrayURL = URLtemp.split("/");

                URLtemp = arrayURL.join().replace(/,/g, "/");

                // URLtemp = URLtemp+'<?= '?v=1' . $v ?>';

                URLtemp = sSource;

                // mereset url menjadi default lagi
                for (var i = 0; i < arrayURL.length; i++) {
                    if (arrayURL[i] == 'get_products_warehouse') {
                        arrayURL[i + 1] = $("#companies").val();
                        arrayURL[i + 2] = $("#warehouse").val();
                    }
                }

                URLtemp = arrayURL.join().replace(/,/g, "/");

                $('#companies').change(function() {
                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });
                    arrayURL = URLtemp.split("/");
                    for (var i = 0; i < arrayURL.length; i++) {
                        if (arrayURL[i] == 'get_products_warehouse') {
                            arrayURL[i + 1] = $(this).val();
                            arrayURL[i + 2] = 0;
                        }
                    }
                    URLtemp = arrayURL.join().replace(/,/g, "/");
                    $.ajax({
                        "type": "POST",
                        "dataType": 'json',
                        "url": URLtemp,
                        "data": aoData,
                        "success": function(data) {
                            fnCallback(data);
                        }
                    });
                });

                $('#warehouse').change(function() {
                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });
                    arrayURL = URLtemp.split("/");
                    for (var i = 0; i < arrayURL.length; i++) {
                        if (arrayURL[i] == 'get_products_warehouse') {
                            arrayURL[i + 2] = $(this).val();
                        }
                    }
                    URLtemp = arrayURL.join().replace(/,/g, "/");
                    $.ajax({
                        "type": "POST",
                        "dataType": 'json',
                        "url": URLtemp,
                        "data": aoData,
                        "success": function(data) {
                            fnCallback(data);
                        }
                    });
                });
                $.ajax({
                    'dataType': 'json',
                    'type': 'POST',
                    'url': URLtemp,
                    'data': aoData,
                    'success': fnCallback
                });
            },
            "aoColumns": [null, null, null, null, null, null, null],
        });
    });
</script>
<?php echo form_open("reports/get_products_warehouse"); ?>
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
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>" data-action="export_excel">
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
                        <div class="col-lg-6" style="padding-left:inherit; margin-bottom:10px;">
                            <label class="control-label" for="distributor"><?= lang("distributor"); ?></label>
                            <?php
                            $dist[] = lang('select') . ' ' . lang('distributor');
                            foreach ($companies as $comp) {
                                $dist[$comp->id] = $comp->company;
                            }
                            echo form_dropdown('companies', $dist, (isset($_POST['companies']) ? $_POST['companies'] : ""), 'class="form-control" id="companies" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("companies") . '"');
                            ?>
                        </div>
                        <div class="col-lg-6" style="padding-right:inherit">
                            <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                            <?php
                            $wh[] = lang('select') . ' ' . lang('warehouse');
                            foreach ($warehouses as $warehouse) {
                                $wh[$warehouse->id] = $warehouse->name;
                            }
                            echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
                            ?>
                        </div>
                    </div>
                    <table id="SLData" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr class="active">
                                <th><?= lang("provinsi") ?></th>
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
                                <td colspan="7" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                            </tr>
                        </tbody>

                    </table>
                </div>

            </div>

        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('#companies').change(function() {
            $('#warehouse').val('').change();
            $.ajax({
                "type": "POST",
                "url": '<?= site_url('reports/get_warehouse_by_company/') ?>' + $('#companies').val() + '/0',
                "success": function(data) {
                    $('#warehouse').html(data);
                }
            });
        });
        $('#xls').click(function(event) {
            var companies = $('#companies').val();
            var warehouse = $('#warehouse').val();
            // window.location.href = "<?= site_url('reports/get_products_warehouse/') ?>"+companies+"/"+warehouse+"/0/pdf/?v=1";
            if (warehouse == '') {
                window.location.href = "<?= site_url('reports/get_products_warehouse/') ?>" + companies + "/0/0/pdf/?v=1";
            } else {
                window.location.href = "<?= site_url('reports/get_products_warehouse/') ?>" + companies + "/" + warehouse + "/0/pdf/?v=1";
            }
            return false;
        });
        $('#pdf').click(function(event) {
            var companies = $('#companies').val();
            var warehouse = $('#warehouse').val();
            // window.location.href = "<?= site_url('reports/get_products_warehouse/') ?>"+companies+"/"+warehouse+"/pdf/?v=1";
            if (warehouse == '') {
                window.location.href = "<?= site_url('reports/get_products_warehouse/') ?>" + companies + "/0/pdf/?v=1";
            } else {
                window.location.href = "<?= site_url('reports/get_products_warehouse/') ?>" + companies + "/" + warehouse + "/pdf/?v=1";
            }
            return false;
        });
    });
</script>
<!-- <div style="display: none;">
    <input type="hidden" name="form_action" value="" id="form_action"/>
    <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
</div> -->
<?= form_close() ?>