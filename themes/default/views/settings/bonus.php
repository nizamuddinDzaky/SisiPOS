<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function() {
        $('#BData').dataTable({
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
            'sAjaxSource': '<?= site_url('system_settings/getBonus') ?>',
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
                "bSortable": false,
                "mRender": checkbox
            }, null, null, null, {
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
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a title="<?= lang('add_bonus') ?>" class="tip" href="<?php echo site_url('system_settings/add_bonus'); ?>" data-toggle="modal" data-target="#myModal" data-backdrop="static">
                        <i class="icon fa fa-plus"></i>
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
                    <table id="BData" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check" />
                                </th>
                                <th><?php echo $this->lang->line("warehouse"); ?></th>
                                <th><?php echo $this->lang->line("product"); ?></th>
                                <th><?php echo $this->lang->line("bonus"); ?></th>
                                <th><?php echo $this->lang->line("quantity"); ?></th>
                                <th style="max-width:85px;"><?php echo $this->lang->line("actions"); ?></th>
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