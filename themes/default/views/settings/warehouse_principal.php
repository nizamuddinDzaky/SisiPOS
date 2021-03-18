<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        function tax_type(x) {
            return (x == 1) ? "<?=lang('percentage')?>" : "<?=lang('fixed')?>";
        }

        $('#CusData').dataTable({
            "aaSorting": [[2, "asc"], [3, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'fnRowCallback' : function(nRow, aData, iDisplayIndex) {
                nRow.id = aData[0];
                if (aData[10] == '1') {
                     $('td', nRow).addClass('danger');
                }
            },
            'sAjaxSource': '<?= site_url('system_settings/getWarehouses') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"bSortable": false, "mRender": checkbox}, { "bSortable": false, "mRender": img_hl }, null, null, null, null, null, null,null,null, {'bVisible': false},{"bSortable": false, "mRender":action_warehouse}]
        });
    });
</script>
<?= form_open('system_settings/warehouse_actions', 'id="action-form"') ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-building-o"></i><?= $page_title ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?= site_url('system_settings/import_warehouse'); ?>" data-toggle="modal" data-target="#myModal" data-backdrop="static">
                                <i class="fa fa-plus-circle"></i> <?= lang("import_by_csv"); ?>
                            </a>
                        </li>

                        <li>
                            <a href="<?php echo site_url('system_settings/add_warehouse'); ?>" data-toggle="modal" data-target="#myModal"  data-backdrop="static">
                                <i class="fa fa-plus"></i> <?= lang('add_warehouse') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo site_url('system_settings/update_warehouse_by_excel'); ?>" data-toggle="modal" data-target="#myModal"  data-backdrop="static">
                                <i class="fa fa-edit"></i> <?= lang('update_warehouse') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="excel_all" data-action="export_excel_all">
                                <i class="fa fa-file-excel-o"></i> <?= lang('export_all_to_excel') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="pdf" data-action="export_pdf">
                                <i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#" id="delete" data-action="delete">
                                <i class="fa fa-trash-o"></i> <?= lang('delete_warehouses') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="recover" data-action="recover">
                                <i class="fa fa-recycle"></i> <?= lang('recover_warehouses') ?>
                            </a>
                        </li>

                    </ul>
                </li>
                <?php echo anchor($mb_warehouses, '<i class="icon fa fa-book tip" data-placement="left" title="'.lang("manual_book").'"></i> ', 'target="_blank"') ?>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang("list_results"); ?></p>

                <div class="table-responsive">
                    <table id="CusData" class="table table-bordered table-hover table-striped table-condensed">
                        <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
                            <th style="min-width:40px; width: 40px; text-align: center;"><?= lang("map"); ?></th>
                            <th class="col-xs-1"><?= lang("code"); ?></th>
                            <th class="col-xs-2"><?= lang("name"); ?></th>
                            <th class="col-xs-2"><?= lang("price_group"); ?></th>
                            <th class="col-xs-2"><?= lang("phone"); ?></th>
                            <th class="col-xs-2"><?= lang("email"); ?></th>
                            <th class="col-xs-3"><?= lang("address"); ?></th>
                            <th class="col-xs-3"><?= lang("deistributor_code"); ?></th>
                            <th class="col-xs-3"><?= lang("distributor_name"); ?></th>
                            <th class="col-xs-3"><?= lang("address"); ?></th>
                            <th style="width:65px;"><?= lang("actions"); ?></th>
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

        $('#recover').click(function (e) {
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

