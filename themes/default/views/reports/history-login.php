<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function() {
        load_data();
    });

    function load_data() {
        $('#GCData').dataTable({
            "aaSorting": [
                [0, "desc"]
            ],
            "aLengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "<?= lang('all') ?>"]
            ],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true,
            'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getHistoryLogin') ?>',
            'fnServerData': function(sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                // console.log(sSource);
                URLtemp = sSource;
                arrayURL = URLtemp.split("/");
                for (var i = 0; i < arrayURL.length; i++) {
                    if (arrayURL[i] == 'getHistoryLogin') {
                        arrayURL[i + 1] = $("#start_date").val();
                        arrayURL[i + 2] = $("#end_date").val();
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

                $('#start_date').change(function() {
                    var str = $(this).val(); /**/
                    str = str.replace(/\//g, "-");
                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });
                    arrayURL = URLtemp.split("/");
                    for (var i = 0; i < arrayURL.length; i++) {
                        if (arrayURL[i] == 'getHistoryLogin') {
                            arrayURL[i + 1] = str;
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
                    var str = $(this).val(); /**/
                    str = str.replace(/\//g, "-");

                    // start = $('#start_date').val();
                    // str = start.replace(/\//g, "-");
                    // if (start == '') {
                    //     start = '-';
                    // }

                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });
                    arrayURL = URLtemp.split("/");
                    for (var i = 0; i < arrayURL.length; i++) {
                        if (arrayURL[i] == 'getHistoryLogin') {
                            // arrayURL[i + 1] = start;
                            arrayURL[i + 2] = str;
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
                // $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{
                "mRender": fld
            }, null, null, null, null]
        });
    }
</script>
<?= form_open('reports/history_login_actions', 'id="action-form"') ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-gift"></i><?= lang('history_login') ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" id="excel-user-login" data-action="export_excel">
                        <i class="icon fa fa-file-excel-o tip" data-placement="left" title="<?= lang("To Excel") ?>"></i>
                    </a>
                </li>
                <!-- <li class="dropdown">
                    <a href="#" class="dropdown-toggle" id="pdf-user-login" data-action="export_pdf">
                        <i class="icon fa fa-file-pdf-o tip"  data-placement="left" title="<?= lang("To PDF") ?>"></i>
                    </a>
                </li> -->
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo $this->lang->line("list_results"); ?></p>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="start_date"><?= lang("start_date"); ?></label>
                            <?php echo form_input('start_date', '', 'class="form-control input-tip date" id="start_date"'); ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="end_date"><?= lang("end_date"); ?></label>
                            <?php echo form_input('end_date', '', 'class="form-control input-tip date" id="end_date"'); ?>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="GCData" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th><?php echo $this->lang->line("time"); ?></th>
                                <th><?php echo $this->lang->line("ip_address"); ?></th>
                                <th><?php echo $this->lang->line("user"); ?></th>
                                <th><?php echo $this->lang->line("email"); ?></th>
                                <th><?php echo $this->lang->line("name"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
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
    <?= form_submit('submit', 'submit', 'id="action-form-submit"') ?>
</div>
<?= form_close() ?>
<script language="javascript">
    $(document).ready(function() {

        $('#delete').click(function(e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#excel-user-login').click(function(e) {
            start = $('#start_date').val();
            end = $('#end_date').val();
            // console.log();
            if (start != '' && end != '') {
                arrStart = start.split("/");
                arrEnd = end.split("/");
                start = arrStart[1] + '/' + arrStart[0] + '/' + arrStart[2];
                end = arrEnd[1] + '/' + arrEnd[0] + '/' + arrEnd[2];
                // console.log(arrStart);

                const date1 = new Date(start);
                const date2 = new Date(end);
                const diffTime = Math.abs(date2.getTime() - date1.getTime());
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                if (diffDays <= 365) {
                    $('#form_action').val($(this).attr('data-action'));
                    $('#action-form-submit').trigger('click');
                } else {
                    alert('Batas Waktu Harus Kurang dari Sama dengan 1 Tahun');
                }
            } else {
                alert('Mohon Isi Filter');
            }

            e.preventDefault();
        });

        $('#pdf-user-login').click(function(e) {
            start = $('#start_date').val();
            end = $('#end_date').val();
            if (start != '' && end != '') {
                arrStart = start.split("/");
                arrEnd = end.split("/");
                start = arrStart[1] + '/' + arrStart[0] + '/' + arrStart[2];
                end = arrEnd[1] + '/' + arrEnd[0] + '/' + arrEnd[2];
                const date1 = new Date(start);
                const date2 = new Date(end);
                const diffTime = Math.abs(date2.getTime() - date1.getTime());
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                if (diffDays <= 365) {
                    $('#form_action').val($(this).attr('data-action'));
                    $('#action-form-submit').trigger('click');
                } else {
                    alert('Batas Waktu Harus Kurang dari Sama dengan 1 Tahun');
                }
            } else {
                alert('Mohon Isi Filter');
            }

            e.preventDefault();
        });

    });
</script>