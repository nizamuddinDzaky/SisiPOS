<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        function menu(x) {
            console.log(x);
            return '<div class="text-center"><i class="'+x+'"></i></div>';
        }

        $(document).on('click','.btn-update-cms', function(){
            console.log($(this).data('id'));
        });
        var dataTable = $('#GData').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('system_settings/get_updates_notif') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                url_=sSource;
                
                arrurl=url_.split("/");
                url_=arrurl.join().replace(/,/g,"/");
                $.ajax({
                    'dataType': 'json',
                    'type': 'POST', 
                    'url': url_, 
                    'data': aoData, 
                    'success': fnCallback
                });
            },
            "aoColumns": [{"bVisible": false}, null, null, null, null, null, null, {"mRender": top_status}, {"bSortable": false}]
        });

    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa fa-book"></i><?= $page_title ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a title="<?= lang('add_updates_notif') ?>" class="tip" href="<?php echo site_url('system_settings/add_updates_notif'); ?>" data-toggle="modal" data-target="#myModal" data-backdrop="static">
                        <i class="icon fa fa-plus"></i>
                    </a>
        </div>
                </li>
            </ul>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo $this->lang->line("list_results"); ?></p>
                <div class="table-responsive">
                    <table id="GData" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr>
                            <th><?= lang('id');?></th>
                            <th><?= lang('type');?></th>
                            <th><?= lang('name');?></th>
                            <th><?= lang('link');?></th>
                            <th><?= lang('version');?></th>
                            <th><?= lang('version_num');?></th>
                            <th><?= lang('release_date');?></th>
                            <th><?= lang('status');?></th>
                            <th><?= lang('action'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="9" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>
</div>
