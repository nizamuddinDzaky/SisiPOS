<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function() {
        $('#SLData').dataTable({
            "aaSorting": [
                [3, "desc"]
            ],
            "aLengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "<?= lang('all') ?>"]
            ],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true,
            'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/get_exported_excel_reports/?') ?>',
            'fnServerData': function(sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                url_ = sSource;

                $.ajax({
                    'dataType': 'json',
                    'type': 'POST',
                    'url': url_,
                    'data': aoData,
                    'success': fnCallback
                });

            },

            "aoColumns": [null, null, null, {
                "mRender": fld
            }, {
                "bVisible": false
            }, {
                "bSortable": false,
                "mRender": attachment3
            }],
        });

    });
</script>

<?php echo form_open('reports/get_exported_excel_reports', 'id="action-form"'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-file-excel-o"></i><?= $page_title ?> </h2>
        <!-- <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" id="excel" class="tip" title="<?= lang('download_xls') ?>" data-action="export_excel">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
            </ul>
        </div> -->
    </div>

    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <div class="table-responsive">


                    <table id="SLData" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr class="active">
                                <th><?= lang("type"); ?></th>
                                <th style=""><?= lang("filename"); ?></th>
                                <th style=""><?= lang("filesize"); ?></th>
                                <th style=""><?= lang("created_at") ?></th>
                                <th style=""><?= lang("url") ?></th>
                                <th style="text-align: center"><?= lang("actions"); ?></th>
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