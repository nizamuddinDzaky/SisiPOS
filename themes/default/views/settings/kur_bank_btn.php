<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function() {
        load_data();
    });

    function load_data() {
        $('#PData').dataTable({
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
            'sAjaxSource': '<?= site_url('system_settings/getKurBtn') ?>',
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
            }, null, null, null, null, null,{
                "bSortable": false,
                "mRender": currencyFormat
            },{
                "bSortable": false,
                "mRender": currencyFormat
            },{
                "bSortable": false,
                "mRender": status_credit
            },{"bSortable": false}]
        }).fnSetFilteringDelay().dtFilter([{
                column_number: 1,
                filter_default_label: "[<?= lang('name'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 2,
                filter_default_label: "[<?= lang('Limit'); ?>]",
                filter_type: "text",
                data: []
            }
        ], "footer");
    }
</script>
<?= form_open('sales/promotion_actions', 'id="action-form"') ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-gift"></i><?= lang('Kur_Bank_BTN') ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?php echo site_url('system_settings/add_kur_btn'); ?>" data-toggle="modal" data-target="#myModal" data-backdrop="static">
                                <i class="fa fa-plus"></i> <?= lang('Add_Kur_Btn') ?>
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

                <p class="introtext"><?php echo $this->lang->line("list_result"); ?></p>

                <div class="table-responsive">
                    <table id="PData" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check" />
                                </th>
                                <th><?php echo $this->lang->line("company"); ?></th>
                                <th><?php echo $this->lang->line("name"); ?></th>
                                <th><?php echo $this->lang->line("distributor"); ?></th>
                                <th><?php echo $this->lang->line("phone"); ?></th>
                                <th><?php echo $this->lang->line("customers_code"); ?></th>
                                <th><?php echo $this->lang->line("plafon_kur"); ?></th>
                                <th><?php echo $this->lang->line("jangka_waktu"); ?></th>
                                <th><?php echo $this->lang->line("Status Loan"); ?></th>
                                <th style="width:80px; text-align:center;"><?= lang("actions"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="10" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
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

    function status_credit(x) {
        if (x == null) {
            return '<div class="text-center">-</div>';
        } else if (x == 'on_process' || x == 'On Progress') {
            return '<div class="text-center"><span class="label label-info">' + x + '</span></div>';
        } else if (x == 'done' || x == 'Approve') {
            return '<div class="text-center"><span class="label label-success">' + x + '</span></div>';
        } else if (x == 'Invalid Data' || x == 'Reject by Bank' || x == 'Canceled by Bank') {
            return '<div class="text-center"><span class="label label-danger">' + x + '</span></div>';
        } else if (x == 'Request PK Signing' || x=='pending') {
            return '<div class="text-center"><span class="label label-warning">' + x + '</span></div>';
        } 
    }

    $(document).ready(function() {

        $('#delete').click(function(e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#excel').click(function(e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#pdf').click(function(e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

    });
</script>