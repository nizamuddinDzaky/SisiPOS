<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function() {
        $('#PointData').dataTable({
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
            'sAjaxSource': '<?= site_url('system_settings/getPoints') ?>',
            'fnServerData': function(sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({
                    'dataType': 'json',
                    'type': 'POST',
                    'url': sSource,
                    'data': aoData,
                    'success': fnCallback
                });
            },
            "aoColumns": [{
                "mRender": currencyFormat
            }, {
                "mRender": formatQuantity
            }, {
                "mRender": currencyFormat
            }, {
                "mRender": formatQuantity
            }, {
                "bSortable": false
            }]
        });
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-gift"></i><?= $page_title ?></h2>

        <div class="box-icon">
            <?php if (!$pts) { ?>
                <ul class="btn-tasks">
                    <li class="dropdown">
                        <a title="<?= lang('add_points') ?>" class="tip" href="<?php echo site_url('system_settings/add_points'); ?>" data-toggle="modal" data-target="#myModal" data-backdrop="static">
                            <i class="icon fa fa-plus"></i>
                        </a>
                    </li>
                </ul>
            <?php } ?>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo $this->lang->line("list_result"); ?></p>
                <div class="table-responsive">
                    <table id="PointData" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th><?php echo $this->lang->line("spent"); ?></th>
                                <th><?php echo $this->lang->line("point_spent"); ?></th>
                                <th><?php echo $this->lang->line("sale"); ?></th>
                                <th><?php echo $this->lang->line("point_sale"); ?></th>
                                <th style="max-width:85px;"><?php echo $this->lang->line("actions"); ?></th>
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