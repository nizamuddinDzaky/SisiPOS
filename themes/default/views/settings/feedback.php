<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function() {
        function menu(x) {
            console.log(x);
            return '<div class="text-center"><i class="' + x + '"></i></div>';
        }

        $(document).on('click', '.btn-update-cms', function() {
            console.log($(this).data('id'));
        });
        var dataTable = $('#GData').dataTable({
            "aaSorting": [
                [1, "asc"]
            ],
            "aLengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "<?= lang('all') ?>"]
            ],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true,
            'bServerSide': true,
            'sAjaxSource': '<?= site_url('System_settings/getFeedback') ?>',
            'fnServerData': function(sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                url_ = sSource;

                arrurl = url_.split("/");
                url_ = arrurl.join().replace(/,/g, "/");
                $.ajax({
                    'dataType': 'json',
                    'type': 'POST',
                    'url': url_,
                    'data': aoData,
                    'success': fnCallback
                });
            },
            "aoColumns": [{ "bVisible": false }, null, null, {"mRender": top_status}, { "bSortable": false }]
        });

    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa fa-puzzle-piece"></i><?= $page_title ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a title="<?= lang('add_feedback_statement') ?>" class="tip" href="<?php echo site_url('system_settings/add_feedback'); ?>" data-toggle="modal" data-target="#myModal" data-backdrop="static">
                        <i class="icon fa fa-plus"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang("list_results"); ?></p>
                <div class="table-responsive">
                    <table id="GData" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th style="max-width:85px;"><?php echo lang("id"); ?></th>
                                <th style="max-width:85px;"><?php echo lang("statement"); ?></th>
                                <th style="max-width:85px;"><?php echo lang("category"); ?></th>
                                <th style="max-width:85px;"><?php echo lang("status"); ?></th>
                                <th style="max-width:85px;"><?php echo lang("action"); ?></th>
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