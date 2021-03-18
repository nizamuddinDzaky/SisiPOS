<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        $('#CGData').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('system_settings/getTempo') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name()?> " ,
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"bVisible": false}, {"bVisible": false}, null, null, {"mRender": top_status}, {"bSortable": false}],
            }).fnSetFilteringDelay().dtFilter([
                {column_number: 2, filter_default_label: "[<?=lang('tempo_duration');?>]", filter_type: "text", data: []},
                {column_number: 3, filter_default_label: "[<?=lang('description');?>]", filter_type: "text", data: []},
                {
                    column_number: 4, select_type: 'select2',
                    select_type_options: {
                        placeholder: '<?= lang('status'); ?>',
                        width: '100%',
                        style: 'width:100%;',
                        minimumResultsForSearch: -1,
                        allowClear: true
                    },
                    data: [{value: '1', label: '<?=lang('active');?>'}, {value: '0', label: '<?=lang('inactive');?>'}]
                }
                // {column_number: 3, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []}
            ], "footer");
    });
</script>
<?= form_open('system_settings/tempo_actions', 'id="action-form"') ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-building"></i><?= $page_title ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a title="<?= lang('add_tempo') ?>" class="tip" href="<?php echo site_url('system_settings/add_tempo'); ?>" data-toggle="modal" data-target="#myModal"  data-backdrop="static">
                    <i class="icon fa fa-plus"></i>
                    </a>
                </li>
                <?php echo anchor($mb_tempo, '<i class="icon fa fa-book tip" data-placement="left" title="'.lang("manual_book").'"></i> ', 'target="_blank"') ?>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <!-- <p class="introtext"><?php echo $this->lang->line("list_results"); ?></p> -->
                <div class="table-responsive">
                    <table id="CGData" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
                            <th></th>
                            <th style="max-width:100px;"><?php echo $this->lang->line("tempo_duration"); ?></th>
                            <th><?php echo $this->lang->line("description"); ?></th>
                            <th style="max-width:100px;"><?php echo $this->lang->line("status"); ?></th>
                            <th style="max-width:85px;"><?php echo $this->lang->line("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="4" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                            </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                            <tr class="active">
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check"/>
                                </th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th style="max-width:85px;"><?php echo $this->lang->line("actions"); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>

        </div>
    </div>
</div>

<div style="display: none;">
    <input type="hidden" name="form_action" value="" id="form_action"/>
    <?= form_submit('submit', 'submit', 'id="action-form-submit"') ?>
</div>
<?= form_close() ?>
<script language="javascript">
    $(document).ready(function () {

        $('#delete').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#excel').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#pdf').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

    });
</script>

