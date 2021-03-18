<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script>
    $(document).ready(function () {
        oTable = $('#csgData').dataTable({
            "aaSorting": [[1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('products/getConsignments/'.($warehouse ? $warehouse->id : '')); ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"bSortable": false, "mRender": checkbox}, {"mRender": fld}, null, null, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": pay_status}, null],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[0];
                nRow.className = "consignment_link";
                return nRow;
            },"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var gtotal = 0, paid = 0, balance = 0;
                for (var i = 0; i < aaData.length; i++) {
                    gtotal += parseFloat(aaData[aiDisplay[i]][4]);
                    paid += parseFloat(aaData[aiDisplay[i]][5]);
                    balance += parseFloat(aaData[aiDisplay[i]][6]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[4].innerHTML = currencyFormat(parseFloat(gtotal));
                nCells[5].innerHTML = currencyFormat(parseFloat(paid));
                nCells[6].innerHTML = currencyFormat(parseFloat(balance));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('warehouse');?>]", filter_type: "text", data: []},
            {
                column_number: 7, select_type: 'select2',
                select_type_options: {
                    placeholder: '<?= lang('payment_status'); ?>',
                    width: '100%',
                    style: 'width:100%;',
                    minimumResultsForSearch: -1,
                    allowClear: true
                },
                data: [{value: 'pending', label: '<?=lang('pending');?>'}, {value: 'partial', label: '<?=lang('partial');?>'}, {value: 'paid', label: '<?=lang('paid');?>'}, {value: 'waiting', label: '<?=lang('waiting');?>'}, {value: 'due', label: '<?=lang('due');?>'}]
            },
        ], "footer");

        if (localStorage.getItem('remove_csgls')) {
            if (localStorage.getItem('csgitems')) {
                localStorage.removeItem('csgitems');
            }
            if (localStorage.getItem('csgref')) {
                localStorage.removeItem('csgref');
            }
            if (localStorage.getItem('csgwarehouse')) {
                localStorage.removeItem('csgwarehouse');
            }
            if (localStorage.getItem('csgnote')) {
                localStorage.removeItem('csgnote');
            }
            if (localStorage.getItem('csgdate')) {
                localStorage.removeItem('csgdate');
            }
            localStorage.removeItem('remove_csgls');
        }

        <?php if ($this->session->userdata('remove_csgls')) { ?>
            if (localStorage.getItem('csgitems')) {
                localStorage.removeItem('csgitems');
            }
            if (localStorage.getItem('csgref')) {
                localStorage.removeItem('csgref');
            }
            if (localStorage.getItem('csgwarehouse')) {
                localStorage.removeItem('csgwarehouse');
            }
            if (localStorage.getItem('csgnote')) {
                localStorage.removeItem('csgnote');
            }
            if (localStorage.getItem('csgdate')) {
                localStorage.removeItem('csgdate');
            }
        <?php $this->sma->unset_data('remove_csgls');}
        ?>
    });
</script>

<?php if ($Owner || $GP['bulk_actions']) {
//        echo form_open('products/adjustment_actions', 'id="action-form"');
    }
?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-filter"></i><?= lang('consignments').' ('.($warehouse ? $warehouse->name : lang('all_warehouses')).')'; ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
<!--                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?= site_url('products/add_adjustment') ?>">
                                <i class="fa fa-plus-circle"></i> <?= lang('add_adjustment') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= site_url('products/add_adjustment_by_csv') ?>">
                                <i class="fa fa-plus-circle"></i> <?= lang('add_adjustment_by_csv') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="pdf" data-action="export_pdf">
                                <i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#" class="bpo" title="<b><?= $this->lang->line("delete_products") ?></b>"
                                data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>"
                                data-html="true" data-placement="left">
                            <i class="fa fa-trash-o"></i> <?= lang('delete_products') ?>
                             </a>
                         </li>
                    </ul>
                </li>-->
                <?php if (!empty($warehouses)) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= site_url('products/consignments') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($warehouses as $warehouse) {
                                echo '<li><a href="' . site_url('products/consignments/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>

                <div class="table-responsive">
                    <table id="csgData" class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("reference_no"); ?></th>
                            <th><?= lang("warehouse"); ?></th>
                            <th><?= lang("total"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            <th><?= lang("payment_status"); ?></th>
                            <th><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="9" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th><th></th><th></th>
                            <th><?= lang("total"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            <th></th>
                            <th style="width:80px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($Owner || $GP['bulk_actions']) {?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?php // echo form_submit('performAction', 'performAction', 'id="action-form-submit"')?>
    </div>
    <?php // echo form_close()?>
<?php }
?>