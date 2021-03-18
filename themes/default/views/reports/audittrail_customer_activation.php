<script>
    $(document).ready(function() {
        $('#SLData').dataTable({
            "aaSorting": [
                [0, "desc"]
            ],
            "aLengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "<?= lang('all') ?>"]
            ],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': false,
            'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/get_audittrail_customer_activation/') ?>',
            'fnServerData': function(sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                url_ = sSource;

                let end_date = $('#end_date').val();
                let start_date = $('#start_date').val();

                if (end_date == '') {
                    end_date = '-';
                } else {
                    end_date = end_date.replace(/\//g, '-');
                }

                if (start_date == '') {
                    start_date = '-';
                } else {
                    start_date = $('#start_date').val().replace(/\//g, '-');
                }

                arrurl = url_.split("/");
                for (var i = 0; i < arrurl.length; i++) {
                    if (arrurl[i] == 'get_audittrail_customer_activation') {
                        arrurl[i + 1] = start_date;
                        arrurl[i + 2] = end_date;
                        arrurl[i + 3] = $("#warehouse").val();
                    }
                }
                url_ = arrurl.join().replace(/,/g, "/");

                $.ajax({
                    'dataType': 'json',
                    'type': 'POST',
                    'url': url_,
                    'data': aoData,
                    'success': fnCallback
                });

                $('#start_date').change(function() {
                    let start_date = $(this).val().replace(/\//g, '-');
                    let end_date = $('#end_date').val();
                    if (end_date == '') {
                        end_date = '-';
                    } else {
                        end_date = end_date.replace(/\//g, '-');
                    }

                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });
                    arrayURL = url_.split("/");
                    for (var i = 0; i < arrayURL.length; i++) {
                        if (arrayURL[i] == 'get_audittrail_customer_activation') {
                            arrayURL[i + 1] = start_date;
                            arrayURL[i + 2] = end_date;
                        }
                    }
                    URLtemp = arrayURL.join().replace(/,/g, "/");
                    $.ajax({
                        "type": "POST",
                        "dataType": 'json',
                        "url": URLtemp, //sending server side status and filtering table
                        "data": aoData,
                        "success": function(data) {
                            fnCallback(data);
                        }
                    });
                });

                $('#end_date').change(function() {
                    let end_date = $(this).val().replace(/\//g, '-');
                    let start_date = $('#start_date').val();
                    if (start_date == '') {
                        start_date = '-';
                    } else {
                        start_date = $('#start_date').val().replace(/\//g, '-');
                    }

                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });
                    arrayURL = url_.split("/");
                    for (var i = 0; i < arrayURL.length; i++) {
                        if (arrayURL[i] == 'get_audittrail_customer_activation') {
                            arrayURL[i + 2] = end_date;
                            arrayURL[i + 1] = start_date;
                        }
                    }
                    URLtemp = arrayURL.join().replace(/,/g, "/");
                    $.ajax({
                        "type": "POST",
                        "dataType": 'json',
                        "url": URLtemp, //sending server side status and filtering table
                        "data": aoData,
                        "success": function(data) {
                            fnCallback(data);
                        }
                    });
                });

            },

            "aoColumns": [{
                "mRender": fld
            }, null, null, null, null],
        });

    });
</script>

<?php echo form_open("reports/audittrailcustomer_activation_action"); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-shopping-cart"></i><?= $page_title ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?= lang('export') . ' ' . $page_title ?>
                            </a>
                        </li>
                        <li>
                            <a class="submenu" href="<?= site_url('reports/filter_tanggal_audittrail'); ?>" data-toggle="modal" data-target="#myModal" data-backdrop="static">
                                <i class="fa fa-file-excel-o"></i> <?= lang('export_all') ?>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <div class="form-group">

                    <div class="col-sm-6" style="padding-left:inherit; margin-bottom:10px;">
                        <div class="form-group">
                            <?= lang("start_date", "start_date"); ?>
                            <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : date('d/m/Y')), 'class="form-control date" id="start_date"'); ?>
                        </div>
                    </div>
                    <div class="col-sm-6" style="padding-right:inherit">
                        <div class="form-group">
                            <?= lang("end_date", "end_date"); ?>
                            <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : date('d/m/Y')), 'class="form-control date" id="end_date"'); ?>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="SLData" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr class="active">
                                <th><?= lang("date"); ?></th>
                                <th><?= lang("ip_address") ?></th>
                                <th><?= lang("ibk"); ?></th>
                                <th><?= lang("customer"); ?></th>
                                <th><?= lang("audittrail_activity"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="12" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                            </tr>
                        </tbody>

                    </table>
                </div>

            </div>

        </div>
    </div>
</div>
<div style="display: none;">
    <input type="hidden" name="form_action" value="" id="form_action" />
    <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
</div>
<?= form_close() ?>