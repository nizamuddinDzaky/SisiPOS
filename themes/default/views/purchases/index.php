<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$url = site_url('purchases/getPurchases/' . date('Y') . '/' . date('m') . '/' . ($warehouse_id ?  $warehouse_id : ''));
?>
<script>
    var URLtemp;
    $(document).ready(function() {
        var oTable = $('#POData').dataTable({
            "aaSorting": [
                [1, "desc"]
            ],
            "aLengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "<?= lang('all') ?>"]
            ],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true,
            'bServerSide': true,
            // 'sAjaxSource': '<?= site_url('purchases/getPurchases' . ($warehouse_id ? '/' . $warehouse_id : '')) ?>',
            //            'sAjaxSource': '<?= site_url('purchases/getPurchases/' . ($_POST['annually'] ? $_POST['annually'] : date('Y')) . '/' . ($bln ? $bln : $m) . '/' . ($warehouse_id ?  $warehouse_id : '')) ?>',
            'sAjaxSource': '<?= $url ?>',
            'fnServerData': function(sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });

                URLtemp = sSource;
                arrayURL = URLtemp.split("/");
                for (var i = 0; i < arrayURL.length; i++) {
                    if (arrayURL[i] == 'getPurchases') {
                        arrayURL[i + 1] = $("#annually").val();
                        arrayURL[i + 2] = $("#monthly").val();
                    }
                }

                URLtemp = arrayURL.join().replace(/,/g, "/");

                $.ajax({
                    'dataType': 'json',
                    'type': 'POST',
                    'url': URLtemp,
                    'data': aoData,
                    'success': fnCallback
                });

                $('#annually').change(function() {
                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });
                    arrayURL = URLtemp.split("/");
                    for (var i = 0; i < arrayURL.length; i++) {
                        if (arrayURL[i] == 'getPurchases') {
                            arrayURL[i + 1] = $(this).val();
                        }
                    }
                    URLtemp = arrayURL.join().replace(/,/g, "/");
                    $.ajax({
                        "type": "POST",
                        "dataType": 'json',
                        "url": URLtemp, //sending server side status and filtering table
                        "data": aoData,
                        "success": function(data) {
                            fnCallback(data);
                        }
                    });
                });
                $('#monthly').change(function() {
                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });
                    arrayURL = URLtemp.split("/");
                    for (var i = 0; i < arrayURL.length; i++) {
                        if (arrayURL[i] == 'getPurchases') {
                            arrayURL[i + 2] = $(this).val();
                        }
                    }
                    URLtemp = arrayURL.join().replace(/,/g, "/");
                    $.ajax({
                        "type": "POST",
                        "dataType": 'json',
                        "url": URLtemp, //sending server side status and filtering table
                        "data": aoData,
                        "success": function(data) {
                            fnCallback(data);
                        }
                    });
                });
            },
            "aoColumns": [{
                "bSortable": false,
                "mRender": checkbox
            }, {
                "mRender": fld
            }, null, null, {
                "mRender": row_status
            }, {
                "mRender": currencyFormat
            }, {
                "mRender": currencyFormat
            }, {
                "mRender": currencyFormat
            }, null, {
                "mRender": pay_status
            }, {
                "bSortable": false,
                "mRender": validate_url
            }, {
                "bSortable": false
            }],
            'fnRowCallback': function(nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.className = "purchase_link";
                return nRow;
            },
            "fnFooterCallback": function(nRow, aaData, iStart, iEnd, aiDisplay) {
                var total = 0,
                    paid = 0,
                    balance = 0;
                for (var i = 0; i < aaData.length; i++) {
                    total += parseFloat(aaData[aiDisplay[i]][5]);
                    paid += parseFloat(aaData[aiDisplay[i]][6]);
                    balance += parseFloat(aaData[aiDisplay[i]][7]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[5].innerHTML = currencyFormat(total);
                nCells[6].innerHTML = currencyFormat(paid);
                nCells[7].innerHTML = currencyFormat(balance);
            }
        }).fnSetFilteringDelay().dtFilter([{
                column_number: 1,
                filter_default_label: "[<?= lang('date'); ?> (yyyy-mm-dd)]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 2,
                filter_default_label: "[<?= lang('ref_no'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 3,
                filter_default_label: "[<?= lang('supplier'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 4,
                select_type: 'select2',
                select_type_options: {
                    placeholder: '<?= lang('purchase_status'); ?>',
                    width: '100%',
                    style: 'width:100%;',
                    minimumResultsForSearch: -1,
                    allowClear: true
                },
                data: [{
                    value: 'returned',
                    label: '<?= lang('returned'); ?>'
                }, {
                    value: 'received',
                    label: '<?= lang('received'); ?>'
                }, {
                    value: 'partial',
                    label: '<?= lang('partial'); ?>'
                }, {
                    value: 'pending',
                    label: '<?= lang('pending'); ?>'
                }]
            },
            {
                column_number: 8,
                filter_default_label: "[<?= lang('no_si_so'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 9,
                select_type: 'select2',
                select_type_options: {
                    placeholder: '<?= lang('payment_status'); ?>',
                    width: '100%',
                    style: 'width:100%;',
                    minimumResultsForSearch: -1,
                    allowClear: true
                },
                data: [{
                    value: 'paid',
                    label: '<?= lang('paid'); ?>'
                }, {
                    value: 'partial',
                    label: '<?= lang('partial'); ?>'
                }, {
                    value: 'pending',
                    label: '<?= lang('pending'); ?>'
                }]
            },
        ], "footer");

        <?php if ($this->session->userdata('remove_pols')) { ?>
            if (localStorage.getItem('poitems')) {
                localStorage.removeItem('poitems');
            }
            if (localStorage.getItem('podiscount')) {
                localStorage.removeItem('podiscount');
            }
            if (localStorage.getItem('potax2')) {
                localStorage.removeItem('potax2');
            }
            if (localStorage.getItem('poshipping')) {
                localStorage.removeItem('poshipping');
            }
            if (localStorage.getItem('poref')) {
                localStorage.removeItem('poref');
            }
            if (localStorage.getItem('powarehouse')) {
                localStorage.removeItem('powarehouse');
            }
            if (localStorage.getItem('ponote')) {
                localStorage.removeItem('ponote');
            }
            if (localStorage.getItem('posupplier')) {
                localStorage.removeItem('posupplier');
            }
            if (localStorage.getItem('pocurrency')) {
                localStorage.removeItem('pocurrency');
            }
            if (localStorage.getItem('poextras')) {
                localStorage.removeItem('poextras');
            }
            if (localStorage.getItem('podate')) {
                localStorage.removeItem('podate');
            }
            if (localStorage.getItem('postatus')) {
                localStorage.removeItem('postatus');
            }
            if (localStorage.getItem('popayment_term')) {
                localStorage.removeItem('popayment_term');
            }
            if (localStorage.getItem('no_si_spj')) {
                localStorage.removeItem('no_si_spj');
            }
            if (localStorage.getItem('no_si_do')) {
                localStorage.removeItem('no_si_do');
            }
            if (localStorage.getItem('no_si_so')) {
                localStorage.removeItem('no_si_so');
            }
            if (localStorage.getItem('delivery_date')) {
                localStorage.removeItem('delivery_date');
            }
            if (localStorage.getItem('no_si_billing')) {
                localStorage.removeItem('no_si_billing');
            }
            if (localStorage.getItem('receiver')) {
                localStorage.removeItem('receiver');
            }
        <?php $this->sma->unset_data('remove_pols');
        }
        ?>
        if (parseFloat(localStorage.getItem('potax2')) <= 1 || localStorage.getItem('podiscount') <= 1 ||
            parseFloat(localStorage.getItem('poshipping')) <= 1 || localStorage.getItem('no_si_spj') <= 1 ||
            localStorage.getItem('no_si_do') <= 1 || localStorage.getItem('no_si_so') <= 1 ||
            localStorage.getItem('no_si_billing') <= 1) {
            localStorage.removeItem("poextras");
            $('#extras-con').slideUp();
        }
    });
</script>

<?php if ($Owner || $Admin || $GP['bulk_actions']) {
    echo form_open('purchases/purchase_actions', 'id="action-form"');
}
?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-star"></i><?= lang('purchases') . ' (' . ($warehouse_id ? $warehouse->name : lang('all_warehouses')) . ')'; ?>
        </h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i></a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?= site_url('purchases/add') ?>">
                                <i class="fa fa-plus-circle"></i> <?= lang('add_purchase') ?>
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
                        <li>
                            <a href="#" id="combine" data-action="combine">
                                <i class="fa fa-file-pdf-o"></i> <?= lang('combine_to_pdf') ?>
                            </a>
                        </li>
                        <!-- Tombol delete sementara disembunyikan
                        <li class="divider"></li>
                        <li>
                            <a href="#" class="bpo" title="<b><?= lang("delete_purchases") ?></b>"
                                data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>"
                                data-html="true" data-placement="left">
                                <i class="fa fa-trash-o"></i> <?= lang('delete_purchases') ?>
                            </a>
                        </li>
                        -->
                    </ul>
                </li>
                <?php if (!empty($warehouses)) {
                ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= site_url('purchases') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($warehouses as $warehouse) {
                                echo '<li ' . ($warehouse_id && $warehouse_id == $warehouse->id ? 'class="active"' : '') . '><a href="' . site_url('purchases/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                <?php }
                ?>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-book tip" data-placement="left" title="<?= lang("manual_book") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= $mb_purchases ?>" target="_blank"><i class="fa fa-book tip"></i> <?= lang('purchases') ?></a></li>
                        <li><a href="<?= $mb_edit_purchases ?>" target="_blank"><i class="fa fa-book tip"></i> <?= lang('edit_purchases') ?></a></li>
                        <li><a href="<?= $mb_export_excel_purchases ?>" target="_blank"><i class="fa fa-book tip"></i> <?= lang('export_excel_purchases') ?></a></li>
                        <li><a href="<?= $mb_export_pdf_purchases ?>" target="_blank"><i class="fa fa-book tip"></i> <?= lang('export_pdf_purchases') ?></a></li>
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
                $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'by_month_year');
                echo form_open("", $attrib);
                ?>
                <div class="form-group">
                    <div class="col-lg-9" style="padding-left:inherit; margin-bottom:10px;">
                        <?php
                        $opts = array('1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April', '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus', '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember');
                        echo form_dropdown('monthly', $opts, (isset($_POST['monthly']) ? $_POST['monthly'] : date('m')), 'class="form-control" id="monthly" required="required"'); ?>
                    </div>
                    <div class="col-lg-3" style="padding-right:inherit">
                        <?php
                        for ($y = date('Y'); $y >= 1990; $y--) {
                            $opts_year[$y] = $y;
                        }
                        echo form_dropdown('annually', $opts_year, (isset($_POST['annually']) ? $_POST['annually'] : date('Y')), 'class="form-control" id="annually" required="required"');
                        ?>
                    </div>
                </div>
                <?php
                form_close();
                ?>
                <div class="table-responsive">
                    <table id="POData" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr class="active">
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkft" type="checkbox" name="check" />
                                </th>
                                <th><?= lang("date"); ?></th>
                                <th><?= lang("ref_no"); ?></th>
                                <th><?= lang("supplier"); ?></th>
                                <th><?= lang("purchase_status"); ?></th>
                                <th><?= lang("grand_total"); ?></th>
                                <th><?= lang("paid"); ?></th>
                                <th><?= lang("balance"); ?></th>
                                <th><?= lang("no_si_so"); ?></th>
                                <th><?= lang("payment_status"); ?></th>
                                <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                                <th style="width:100px;"><?= lang("actions"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="11" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
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
                                <th><?= lang("grand_total"); ?></th>
                                <th><?= lang("paid"); ?></th>
                                <th><?= lang("balance"); ?></th>
                                <th></th>
                                <th></th>
                                <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                                <th style="width:100px; text-align: center;"><?= lang("actions"); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $Admin || $GP['bulk_actions']) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action" />
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php }
?>