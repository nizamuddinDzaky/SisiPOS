<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    .delete1 {
        color: #cd0505;
        background-color: #fccfcf !important;
        border-color: #ebccd1;
    }
</style>
<script>
    $(document).ready(function() {
        var cTable = null;
        
        function create_datatable(site_url){
            if(cTable)
                cTable.fnDestroy();

            cTable = $('#CusData').dataTable({
                        "aaSorting": [
                            [2, "asc"]
                        ],
                        "aLengthMenu": [
                            [10, 25, 50, 100, 200, 500],
                            [10, 25, 50, 100, 200, 500]
                        ],
                        "iDisplayLength": <?= $Settings->rows_per_page ?>,
                        'bProcessing': true,
                        'bServerSide': true,
                        'sAjaxSource': site_url,
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
                        'fnRowCallback': function(nRow, aData, iDisplayIndex) {
                            nRow.id = aData[0];
                            if (aData[16] == '1') {
                                $('td', nRow).addClass('delete1');
                                nRow.className = "customer_details_link";
                                $('td:eq(16)', nRow).html(aData[19]);
                            } else {
                                nRow.className = "customer_details_link";
                                $('td:eq(16)', nRow).html(aData[18]);
                            }
                            return nRow;
                        },
                        "aoColumns": [{
                            "bSortable": false,
                            "mRender": checkbox
                        }, null, null, null, null, null, null, null, null, null, null, {
                            "mRender": currencyFormat
                        }, null, null, null, null, {
                            "bVisible": false
                        }, {
                            "bSortable": false
                        }, {
                            "bVisible": false
                        }, {
                            "bVisible": false
                        }]
                    }).dtFilter([{
                            column_number: 1,
                            filter_default_label: "[<?= lang('company'); ?>]",
                            filter_type: "text",
                            data: []
                        },
                        {
                            column_number: 2,
                            filter_default_label: "[<?= lang('name'); ?>]",
                            filter_type: "text",
                            data: []
                        },
                        {
                            column_number: 3,
                            filter_default_label: "[<?= lang('email_address'); ?>]",
                            filter_type: "text",
                            data: []
                        },
                        {
                            column_number: 4,
                            filter_default_label: "[<?= lang('phone'); ?>]",
                            filter_type: "text",
                            data: []
                        },
                        {
                            column_number: 5,
                            filter_default_label: "[<?= lang('price_group'); ?>]",
                            filter_type: "text",
                            data: []
                        },
                        {
                            column_number: 6,
                            filter_default_label: "[<?= lang('provinsi'); ?>]",
                            filter_type: "text",
                            data: []
                        },
                        {
                            column_number: 7,
                            filter_default_label: "[<?= lang('city'); ?>]",
                            filter_type: "text",
                            data: []
                        },
                        {
                            column_number: 8,
                            filter_default_label: "[<?= lang('state'); ?>]",
                            filter_type: "text",
                            data: []
                        },
                        {
                            column_number: 9,
                            filter_default_label: "[<?= lang('customer_group'); ?>]",
                            filter_type: "text",
                            data: []
                        },
                        {
                            column_number: 10,
                            filter_default_label: "[<?= lang('vat_no'); ?>]",
                            filter_type: "text",
                            data: []
                        },
                        {
                            column_number: 11,
                            filter_default_label: "[<?= lang('deposit'); ?>]",
                            filter_type: "text",
                            data: []
                        },
                        {
                            column_number: 12,
                            filter_default_label: "[<?= lang('award_points'); ?>]",
                            filter_type: "text",
                            data: []
                        },
                        {
                            column_number: 13,
                            filter_default_label: "[<?= lang('customers_code'); ?>]",
                            filter_type: "text",
                            data: []
                        },
                        {
                            column_number: 14,
                            filter_default_label: "[<?= lang('distributor_code'); ?>]",
                            filter_type: "text",
                            data: []
                        },
                        {
                            column_number: 15,
                            filter_default_label: "[<?= lang('distributor_name'); ?>]",
                            filter_type: "text",
                            data: []
                        },
                    ], "footer");
        }
        
        create_datatable('<?= site_url('customers/getCustomers') ?>'); 
        $('#select_distributor').change(function() {
            create_datatable('<?= site_url('customers/getCustomers') ?>/'+$('#select_distributor').val());
        });
    });

</script>
<?php if ($Owner || $Admin || $Principal || $GP['bulk_actions']) {
    echo form_open('customers/customer_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('customers'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?= site_url('customers/add'); ?>" data-toggle="modal" data-target="#myModal" data-backdrop="static" id="add">
                                <i class="fa fa-plus-circle"></i> <?= lang("add_customer"); ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= site_url('customers/import_csv'); ?>" data-toggle="modal" data-target="#myModal" data-backdrop="static">
                                <i class="fa fa-plus-circle"></i> <?= lang("import_by_csv"); ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= site_url('customers/update_by_excel'); ?>" data-toggle="modal" data-target="#myModal" data-backdrop="static">
                                <i class="fa fa-file-excel-o"></i> <?= lang("update_by_excel"); ?>
                            </a>
                        </li>
                        <?php if ($Owner || $Admin || $Principal || $GP['bulk_actions']) { ?>
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
                                <a href="#" class="bpo" title="<b><?= $this->lang->line("delete_customers") ?></b>" data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" data-html="true" data-placement="left">
                                    <i class="fa fa-trash-o"></i> <?= lang('delete_customers') ?>
                                </a>
                            </li>
                            <?php if ($Principal) { ?>
                                <li>
                                    <a href="#" class="bpo" title="<b><?= $this->lang->line("recover_customer") ?></b>" data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-success' id='recover' data-action='recover'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" data-html="true" data-placement="left">
                                        <i class="fa fa-recycle"></i> <?= lang('recover_customer') ?>
                                    </a>
                                </li>
                            <?php } ?>
                        <?php } ?>
                    </ul>
                </li>
                
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-book tip" data-placement="left" title="<?= lang("manual_book") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= $mb_customers ?>" target="_blank"><i class="fa fa-book tip"></i> <?= lang('customers') ?></a></li>
                        <li><a href="<?= $mb_add_customer ?>" target="_blank"><i class="fa fa-book tip"></i> <?= lang('add_customer') ?></a></li>
                        <li><a href="<?= $mb_edit_customer ?>" target="_blank"><i class="fa fa-book tip"></i> <?= lang('edit_customer') ?></a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('list_results'); ?></p>

                <?php
                echo lang("Distributor", "distributor");
                echo form_input('distributor', (isset($_POST['distributor']) ? $_POST['distributor'] : 1), 'id="select_distributor" data-placeholder="' . lang("select") . ' ' . lang("Distributor") . '" required="required" class="form-control input-tip" style="width:100%; margin-bottom:20px;"');
                ?>

                <div class="table-responsive">
                    <table id="CusData" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                            <tr class="primary">
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check" />
                                </th>
                                <th><?= lang("company"); ?></th>
                                <th><?= lang("name"); ?></th>
                                <th><?= lang("email_address"); ?></th>
                                <th><?= lang("phone"); ?></th>
                                <th><?= lang("price_group"); ?></th>
                                <th><?= lang("provinsi"); ?></th>
                                <th><?= lang("city"); ?></th>
                                <th><?= lang("state"); ?></th>
                                <th><?= lang("customer_group"); ?></th>
                                <th><?= lang("vat_no"); ?></th>
                                <th><?= lang("deposit"); ?></th>
                                <th><?= lang("award_points"); ?></th>
                                <th><?= lang("customers_code"); ?></th>
                                <th><?= lang("kode"); ?></th>
                                <th><?= lang("distributor_name"); ?></th>
                                <th><?= lang("is_deleted"); ?></th>
                                <th style="min-width:135px !important;"><?= lang("actions"); ?></th>
                                <th style="min-width:135px !important;"><?= lang("actions"); ?></th>
                                <th style="min-width:135px !important;"><?= lang("actions"); ?></th>
                            </tr>
                        </thead>
                        <tbody id="tb_cust">
                            <tr>
                                <td colspan="20" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                            </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                            <tr class="active">
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkft" type="checkbox" name="check" />
                                </th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th style="min-width:135px !important;" class="text-center"><?= lang("actions"); ?></th>
                                <th style="min-width:135px !important;" class="text-center"><?= lang("actions"); ?></th>
                                <th style="min-width:135px !important;" class="text-center"><?= lang("actions"); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" tabindex="-1" role="dialog" id="statusmodal">
    <div class="modal-dialog" role="document" style="width: 20%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" aria-label="Close" id="close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <center>
                    <p><?= lang('status_tracking') ?></p>
                </center>
            </div>
            <div class="modal-footer">
                <center>
                    <button type="button" class="btn btn-primary" id="ok">Ok</button>
                </center>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
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
<script type="text/javascript">
    $(document).ready(function() {
        suggestionsBillerAktif();
    });

    function suggestionsBillerAktif() {
        var url = "<?php echo site_url() . 'customers/suggestionsBillerAktif' ?>";
        $('#select_distributor').select2({
            minimumInputLength: 1,
            ajax: {
                url: url,
                dataType: 'json',
                quietMillis: 15,
                data: function(term, page) {
                    return {
                        term: term,
                        limit: 20
                    };
                },
                results: function(data, page) {
                    if (data.results != null) {
                        return {
                            results: data.results
                        };
                    } else {
                        return {
                            results: [{
                                id: '',
                                text: 'No Match Found'
                            }]
                        };
                    }
                }
            },
            formatResult: formatAddress
        });
    }

    function formatAddress(items) {
        if (!items.id) {
            return items.text;
        }
        return items.text + "<br><span style='font-size:12px;color:#1E1E1E'>" + items.code + "</span>";
    }
</script>