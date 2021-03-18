<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
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
            'bProcessing': true,
            'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/get_sales_person/?') ?>',
            'fnServerData': function(sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                url_ = sSource;
                // arrurl=url_.split("/");
                // let month = $('#monthly').val();
                // let year = $('#annually').val();
                //
                // if (month == '') {
                //     month = '-';
                // }
                //
                // if (year == '') {
                //     year = '-';
                // }

                // URLtemp = sSource;
                // arrayURL = URLtemp.split("/");
                // for (var i = 0; i < arrurl.length; i++) {
                //     if (arrurl[i] == 'get_sale_transaction') {
                //         arrurl[i + 1] = month;
                //         arrurl[i + 2] = year;
                //     }
                // }
                // url_=arrurl.join().replace(/,/g,"/");
                $.ajax({
                    'dataType': 'json',
                    'type': 'POST',
                    'url': url_,
                    'data': aoData,
                    'success': fnCallback
                });

                $('#dropdown-distributor, #dropdown-sales-person').change(function() {
                    var dist = $('#dropdown-distributor').val();
                    sales_person_id = $('#dropdown-sales-person').val();
                    // url_ = url_+;

                    arrayURL = url_.split("/");
                    for (var i = 0; i < arrayURL.length; i++) {
                        if (arrayURL[i] == 'get_sales_person') {
                            arrayURL[i + 1] = '?&distributor_id=' + dist + '&sales_person_id=' + sales_person_id;
                            // arrayURL[i + 2] = ;
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
                    // console.log(url_);
                })

            },

            "aoColumns": [null, null, null, null, {
                "mRender": fld
            }],
        });

    });
</script>

<?php echo form_open('reports/get_sales_person', 'id="action-form"'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-shopping-cart"></i><?= $page_title ?> </h2>
        <div class="box-icon">
            <ul class="btn-tasks">
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

                <div class="" id="form">
                    <p class="introtext"><?php echo $this->lang->line("list_results"); ?></p>
                    <div class="col-sm-6" style="padding-left:inherit; margin-bottom:10px;">
                        <div class="form-group">
                            <?= lang("distributor", "distributor"); ?>
                            <select class="form-control" id="dropdown-distributor" name="distributor_id">
                                <option value=""><?= lang('all') ?></option>
                                <?php foreach ($distributor as $key => $dist) { ?>
                                    <option value="<?= $dist->id ?>"><?= $dist->company ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6" style="padding-right:inherit">
                        <div class="form-group">
                            <?= lang("sales_person", "distributor"); ?>
                            <select class="form-control" id="dropdown-sales-person" name="sales_person_id">
                                <option value=""><?= lang('all') ?></option>
                                <?php foreach ($sales_person as $key => $sp) { ?>
                                    <option value="<?= $sp->id ?>"><?= $sp->name ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">


                    <table id="SLData" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr class="active">
                                <th><?= lang("referal_code"); ?></th>
                                <th style=""><?= lang("distributor"); ?></th>
                                <th style=""><?= lang("customer") ?></th>
                                <th style=""><?= lang("sales_person"); ?></th>
                                <th style=""><?= lang("time"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                            </tr>
                        </tbody>

                    </table>
                </div>

            </div>

        </div>
    </div>
</div>
<?php if ($Owner || $GP['bulk_actions'] || $Principal) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action" />
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>
<?php if ($action && $action == 'add') {
    echo '<script>$(document).ready(function(){$("#add").trigger("click");});</script>';
}
?>