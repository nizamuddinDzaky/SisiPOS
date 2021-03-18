<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
    .reject1 {
        color: #cd0505;
        background-color: #fccfcf !important;
        border-color: #ebccd1;
    }

    .reject2 {
        color: #fefdfd;
        background-color: #ea5c5cd9 !important;
        border-color: #ebccd1;
    }
</style>
<script>
    $(document).ready(function() {
        var dss = <?= json_encode(array('packing' => lang('packing'), 'delivering' => lang('delivering'), 'delivered' => lang('delivered'), 'returned' => lang('returned'))); ?>;

        function ds(x) {
            if (x == 'delivered') {
                return '<div class="text-center"><span class="label label-success">' + (dss[x] ? dss[x] : x) + '</span></div>';
            } else if (x == 'delivering') {
                return '<div class="text-center"><span class="label label-primary">' + (dss[x] ? dss[x] : x) + '</span></div>';
            } else if (x == 'packing') {
                return '<div class="text-center"><span class="label label-warning">' + (dss[x] ? dss[x] : x) + '</span></div>';
            } else if (x == 'returned') {
                return '<div class="text-center"><span class="label label-danger">' + (dss[x] ? dss[x] : x) + '</span></div>';
            }
            return x;
            return (x != null) ? (dss[x] ? dss[x] : x) : x;
        }
        
        var oTable = $('#DOData').dataTable({
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
                        'sAjaxSource': '<?= site_url('sales_booking/getDeliveries_booking') ?>',
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
                            var oSettings = oTable.fnSettings();
                            nRow.id = aData[0];
                            if (parseInt(aData[8]) > 0 && aData[9] == null && aData[10] == null && aData[6] != 'returned') {
                                $('td', nRow).addClass('reject1');
                                nRow.className = "delivery_link";
                            } else if (parseInt(aData[8]) > 0 && aData[9] == 2 && aData[10] == null && aData[6] != 'returned') {
                                $('td', nRow).addClass('reject2');
                                nRow.className = "delivery_link";
                            } else if (aData[9] == 3 || (aData[9] == 2 && aData[10] == 1)) {
                                nRow.className = "delivery_link";
                            } else {
                                nRow.className = "delivery_link";
                            }
                            return nRow;
                        },
                        "aoColumns": [{
                            "bSortable": false,
                            "mRender": checkbox
                        }, {
                            "mRender": fld
                        }, null, null, null, {
                            "bSearchable": false
                        }, {
                            "mRender": ds
                        }, {
                            "bSortable": false,
                            "mRender": attachment
                        }, {
                            "bSearchable": false,
                            "bVisible": false
                        }, {
                            "bSearchable": false,
                            "bVisible": false
                        }, {
                            "bSearchable": false,
                            "bVisible": false
                        }, {
                            "bSearchable": false,
                            "bSortable": false
                        }]
                    }).fnSetFilteringDelay().dtFilter([{
                            column_number: 1,
                            filter_default_label: "[<?= lang('date'); ?> (yyyy-mm-dd)]",
                            filter_type: "text",
                            data: []
                        },
                        {
                            column_number: 2,
                            filter_default_label: "[<?= lang('do_reference_no'); ?>]",
                            filter_type: "text",
                            data: []
                        },
                        {
                            column_number: 3,
                            filter_default_label: "[<?= lang('sale_reference_no'); ?>]",
                            filter_type: "text",
                            data: []
                        },
                        {
                            column_number: 4,
                            filter_default_label: "[<?= lang('customer'); ?>]",
                            filter_type: "text",
                            data: []
                        },
                        {
                            column_number: 5,
                            filter_default_label: "[<?= lang('address'); ?>]",
                            filter_type: "text",
                            data: []
                        },
                        // {column_number: 6, filter_default_label: "[<?= lang('status'); ?>]", filter_type: "text", data: []},
                        {
                            column_number: 6,
                            select_type: 'select2',
                            select_type_options: {
                                placeholder: '<?= lang('status'); ?>',
                                width: '100%',
                                style: 'width:100%;',
                                minimumResultsForSearch: -1,
                                allowClear: true
                            },
                            data: [{
                                value: 'packing',
                                label: '<?= lang('packing'); ?>'
                            }, {
                                value: 'delivering',
                                label: '<?= lang('delivering'); ?>'
                            }, {
                                value: 'delivered',
                                label: '<?= lang('delivered'); ?>'
                            }, {
                                value: 'returned',
                                label: '<?= lang('returned'); ?>'
                            }]
                        }
                    ], "footer");

        $('input[name=filter_date_range_deliveries]').daterangepicker({
            format: 'DD/MM/YYYY',
            fontAwesome: true,
            language: 'sma',
            todayBtn: 1,
            autoclose: 1,
            minView: 2
        }, function(start, end, label) {
            $('input[name=filter_date_range_deliveries]').val(start.format('DD/MM/YYYY')+" - "+end.format('DD/MM/YYYY'));

            var oneDay = 24*60*60*1000;
            var firstDate = new Date(start);
            var secondDate = new Date(end);
            var diffDays = Math.round(Math.round((secondDate.getTime() - firstDate.getTime()) / (oneDay)));
            
            if(diffDays <= 30){
                $('#SLData_processing').css('visibility', 'visible');
                $('#filter_out_of_range').hide();
                $.post('<?= base_url('sales_booking/set_session_filter_deliveries/') ?>',{
                    filter_date_range_deliveries: $('input[name=filter_date_range_deliveries]').val()
                }, function(data, status){
                    oTable.fnDraw();
                });
            }else{
                $('#filter_out_of_range').show();
            }
        });
    });
</script>
<?php if ($Owner || $Admin) { ?><?= form_open('sales/delivery_actions', 'id="action-form"') ?><?php } ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-truck"></i><?= lang('deliveries_booking'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="#" id="excel" data-action="export_excel"><i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?></a></li>
                        <li><a href="#" id="pdf" data-action="export_pdf"><i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?></a></li>
                        <!-- Tombol delete sementara disembunyikan
                        <li class="divider"></li>
                        <li>
                            <a href="#" class="bpo" title="<b><?= $this->lang->line("delete_deliveries") ?></b>" 
                                data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" 
                                data-html="true" data-placement="left">
                                <i class="fa fa-trash-o"></i> <?= lang('delete_deliveries') ?>
                            </a>
                        </li>
                        !-->

                    </ul>
                </li>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-book tip" data-placement="left" title="<?= lang("manual_book") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= $mb_delivery_order ?>" target="_blank"><i class="fa fa-book tip"></i> <?= lang('delivery_order') ?></a></li>
                        <li><a href="<?= $mb_add_delivery_order ?>" target="_blank"><i class="fa fa-book tip"></i> <?= lang('add_delivery_order') ?></a></li>
                        <li><a href="<?= $mb_edit_delivery_order ?>" target="_blank"><i class="fa fa-book tip"></i> <?= lang('edit_delivery_order') ?></a></li>
                        <li><a href="<?= $mb_export_excel_delivery_order ?>" target="_blank"><i class="fa fa-book tip"></i> <?= lang('export_excel_delivery_order') ?></a></li>
                        <li><a href="<?= $mb_export_pdf_delivery_order ?>" target="_blank"><i class="fa fa-book tip"></i> <?= lang('export_pdf_delivery_order') ?></a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>

                <div class="form-group">
                    <label><?= lang("date"); ?></label>
                    <input type="text" name="filter_date_range_deliveries" class="form-control input-tip" value="<?= ($this->session->userdata('filter_date_range_deliveries') ? $this->session->userdata('filter_date_range_deliveries') : date('d/m/Y', strtotime('-7 days')).' - '.date('d/m/Y')) ?>">
                    <span id="filter_out_of_range" style="color:red; display: none;"><?= lang('filter_out_of_range') ?></span>
                </div>

                <table id="DOData" class="table table-bordered table-hover table-striped table-condensed">
                    <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check" />
                            </th>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("do_reference_no"); ?></th>
                            <th><?= lang("sale_reference_no"); ?></th>
                            <th><?= lang("customer"); ?></th>
                            <th><?= lang("address"); ?></th>
                            <th><?= lang("status"); ?></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th style="width:100px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="9" class="dataTables_empty"><?= lang("loading_data"); ?></td>
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
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th style="width:100px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $Admin) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action" />
        <?= form_submit('perform_action', 'perform_action', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
    <script type="text/javascript" charset="utf-8">
        $(document).ready(function() {
            $(document).on('click', '#delete', function(e) {
                e.preventDefault();
                $('#form_action').val($(this).attr('data-action'));
                //$('#action-form').submit();
                $('#action-form-submit').click();
            });
        });
    </script>
<?php } ?>