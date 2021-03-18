<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$url = site_url('sales_booking/getSalesBooking/' . ($warehouse_id ? $warehouse_id : ''))
?>
<script>
    var URLtemp;
    
    $(document).ready(function () { 
        var oTable = $('#SLData').dataTable({
                        "aaSorting": [[0, "asc"], [1, "desc"]],
                        "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                        "iDisplayLength": <?= $Settings->rows_per_page ?>,
                        'bProcessing': true, 'bServerSide': true,
                        'sAjaxSource': '<?= $url ?>',
                        'fnServerData': function (sSource, aoData, fnCallback) {

                            aoData.push({
                                "name": "<?= $this->security->get_csrf_token_name() ?>",
                                "value": "<?= $this->security->get_csrf_hash() ?>"
                            });
                            
                            URLtemp = sSource;
                            $.ajax({
                                'dataType': 'json', 
                                'type': 'POST', 
                                'url': URLtemp, 
                                'data': aoData, 
                                'success': fnCallback
                            });
                        },
                        'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                            var oSettings = oTable.fnSettings();
                            // $("td:first", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                            nRow.id = aData[0];
                            nRow.setAttribute('data-return-id', aData[11]);
                            nRow.className = "invoice_link re" + aData[11];
                            // nRow.className = "receipt_link";
                            // if(aData[7] > aData[9]){ nRow.className = "product_link warning"; } else { nRow.className = "product_link"; }
                            return nRow;
                        },
                        "aoColumns": [{"bSortable": false, "mRender": checkbox}, {"mRender": fld}, null, null, null, {"mRender": row_status}, {"mRender": pay_method}, {"mRender": status_kredit_pro, "bSearchable":false}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": pay_status},{"mRender": deliv_status}, {"bSortable": false, "mRender": validate_url},{"bVisible": false}, {"bSortable": false}],
                        "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                            var gtotal = 0, paid = 0, balance = 0;
                            for (var i = 0; i < aaData.length; i++) {
                                gtotal += parseFloat(aaData[aiDisplay[i]][8]);
                                paid += parseFloat(aaData[aiDisplay[i]][9]);
                                balance += parseFloat(aaData[aiDisplay[i]][10]);
                            }
                            var nCells = nRow.getElementsByTagName('th');
                            nCells[8].innerHTML = currencyFormat(parseFloat(gtotal));
                            nCells[9].innerHTML = currencyFormat(parseFloat(paid));
                            nCells[10].innerHTML = currencyFormat(parseFloat(balance));
                            setTimeout(function(){
                                setHiddenColumn();
                            }, 1000);
                        }
                    }).fnSetFilteringDelay().dtFilter([
                        {column_number: 1, filter_default_label: "[<?= lang('date'); ?> (yyyy-mm-dd)]", filter_type: "text", data: []},
                        {column_number: 2, filter_default_label: "[<?= lang('reference_no'); ?>]", filter_type: "text", data: []},
                        {column_number: 3, filter_default_label: "[<?= lang('customer'); ?>]", filter_type: "text", data: []},
                        {column_number: 4, filter_default_label: "[<?= lang('created_by'); ?>]", filter_type: "text", data: []},
                        {
                            column_number: 5, select_type: 'select2',
                            select_type_options: {
                                placeholder: '<?= lang('sale_status'); ?>',
                                width: '100%',
                                style: 'width:100%;',
                                minimumResultsForSearch: -1,
                                allowClear: true
                            },
                            data: [{value: 'pending', label: '<?=lang('pending');?>'}, {value: 'confirmed', label: '<?=lang('confirmed');?>'}, {value: 'reserved', label: '<?=lang('reserved');?>'}, {value: 'closed', label: '<?=lang('closed');?>'}, {value: 'canceled', label: '<?=lang('canceled');?>'}]
                        },

                        {
                            column_number: 6, select_type: 'select2',
                            select_type_options: {
                                placeholder: '<?= lang('payment_method'); ?>',
                                width: '100%',
                                style: 'width:100%;',
                                minimumResultsForSearch: -1,
                                allowClear: true
                            },
                            data: [{value: 'cash on delivery', label: '<?=lang('cash on delivery');?>'}, {value: 'kredit_pro', label: '<?=lang('kredit_pro');?>'}, {value: 'kredit', label: '<?=lang('kredit');?>'}, {value: 'cash before delivery', label: '<?=lang('cash before delivery');?>'}, {value: 'kredit_mandiri', label: '<?=lang('kredit_mandiri');?>'}]
                        },

                        {
                            column_number: 11, select_type: 'select2',
                            select_type_options: {
                                placeholder: '<?= lang('payment_status'); ?>',
                                width: '100%',
                                style: 'width:100%;',
                                minimumResultsForSearch: -1,
                                allowClear: true
                            },
                            data: [{value: 'pending', label: '<?=lang('pending');?>'}, {value: 'partial', label: '<?=lang('partial');?>'}, {value: 'paid', label: '<?=lang('paid');?>'}, {value: 'waiting', label: '<?=lang('waiting');?>'}, {value: 'due', label: '<?=lang('due');?>'}]
                        },
                        // {column_number: 10, filter_default_label: "[<?= lang('delivery_status'); ?>]", filter_type: "text", data: []},
                        {
                            column_number: 12, select_type: 'select2',
                            select_type_options: {
                                placeholder: '<?= lang('delivery_status'); ?>',
                                width: '100%',
                                style: 'width:100%;',
                                minimumResultsForSearch: -1,
                                allowClear: true
                            },
                            data: [{value: 'pending', label: '<?=lang('pending');?>'}, {value: 'partial', label: '<?=lang('partial');?>'}, {value: 'done', label: '<?=lang('done');?>'}]
                        },

                        // {
                        //     column_number: 12, select_type: 'select2',
                        //     select_type_options: {
                        //         placeholder: 'Status KreditPro',
                        //         width: '100%',
                        //         style: 'width:100%;',
                        //         minimumResultsForSearch: -1,
                        //         allowClear: true
                        //     },
                        //     data: [{value: 'credit_reviewed', label: '<?=lang('credit_reviewed');?>'}, {value: 'credit_received', label: '<?=lang('credit_received');?>'}, {value: 'credit_declined', label: '<?=lang('credit_declined');?>'}, {value: 'kredit_partial', label: '<?=lang('kredit_partial');?>'}, {value: 'already_paid', label: '<?=lang('already_paid');?>'}]
                        // },

                    ], "footer");

        $('input[name=filter_date_range]').daterangepicker({
            format: 'DD/MM/YYYY',
            fontAwesome: true,
            language: 'sma',
            todayBtn: 1,
            autoclose: 1,
            minView: 2
        }, function(start, end, label) {
            $('input[name=filter_date_range]').val(start.format('DD/MM/YYYY')+" - "+end.format('DD/MM/YYYY'));

            var oneDay = 24*60*60*1000;
            var firstDate = new Date(start);
            var secondDate = new Date(end);
            var diffDays = Math.round(Math.round((secondDate.getTime() - firstDate.getTime()) / (oneDay)));
            
            if(diffDays <= 60){
                $('#SLData_processing').css('visibility', 'visible');
                $('#filter_out_of_range').hide();
                $.post('<?= base_url('sales_booking/set_session_filter/') ?>',{
                    filter_date_range: $('input[name=filter_date_range]').val()
                }, function(data, status){
                    oTable.fnDraw();
                });
            }else{
                $('#filter_out_of_range').show();
            }
        });

        if (localStorage.getItem('remove_slls')) {
            if (localStorage.getItem('slitems')) {
                localStorage.removeItem('slitems');
            }
            if (localStorage.getItem('sldiscount')) {
                localStorage.removeItem('sldiscount');
            }
            if (localStorage.getItem('sltax2')) {
                localStorage.removeItem('sltax2');
            }
            if (localStorage.getItem('slref')) {
                localStorage.removeItem('slref');
            }
            if (localStorage.getItem('slshipping')) {
                localStorage.removeItem('slshipping');
            }
            if (localStorage.getItem('slwarehouse')) {
                localStorage.removeItem('slwarehouse');
            }
            if (localStorage.getItem('slnote')) {
                localStorage.removeItem('slnote');
            }
            if (localStorage.getItem('slinnote')) {
                localStorage.removeItem('slinnote');
            }
            if (localStorage.getItem('slcustomer')) {
                localStorage.removeItem('slcustomer');
            }
            if (localStorage.getItem('slbiller')) {
                localStorage.removeItem('slbiller');
            }
            if (localStorage.getItem('slcurrency')) {
                localStorage.removeItem('slcurrency');
            }
            if (localStorage.getItem('sldate')) {
                localStorage.removeItem('sldate');
            }
            if (localStorage.getItem('slsale_status')) {
                localStorage.removeItem('slsale_status');
            }
            if (localStorage.getItem('slpayment_status')) {
                localStorage.removeItem('slpayment_status');
            }
            if (localStorage.getItem('paid_by')) {
                localStorage.removeItem('paid_by');
            }
            if (localStorage.getItem('amount_1')) {
                localStorage.removeItem('amount_1');
            }
            if (localStorage.getItem('paid_by_1')) {
                localStorage.removeItem('paid_by_1');
            }
            if (localStorage.getItem('pcc_holder_1')) {
                localStorage.removeItem('pcc_holder_1');
            }
            if (localStorage.getItem('pcc_type_1')) {
                localStorage.removeItem('pcc_type_1');
            }
            if (localStorage.getItem('pcc_month_1')) {
                localStorage.removeItem('pcc_month_1');
            }
            if (localStorage.getItem('pcc_year_1')) {
                localStorage.removeItem('pcc_year_1');
            }
            if (localStorage.getItem('pcc_no_1')) {
                localStorage.removeItem('pcc_no_1');
            }
            if (localStorage.getItem('cheque_no_1')) {
                localStorage.removeItem('cheque_no_1');
            }
            if (localStorage.getItem('slpayment_term')) {
                localStorage.removeItem('slpayment_term');
            }
            localStorage.removeItem('remove_slls');
        }

        <?php if ($this->session->userdata('remove_slls')) { ?>
            if (localStorage.getItem('slitems')) {
                localStorage.removeItem('slitems');
            }
            if (localStorage.getItem('sldiscount')) {
                localStorage.removeItem('sldiscount');
            }
            if (localStorage.getItem('sltax2')) {
                localStorage.removeItem('sltax2');
            }
            if (localStorage.getItem('slref')) {
                localStorage.removeItem('slref');
            }
            if (localStorage.getItem('slshipping')) {
                localStorage.removeItem('slshipping');
            }
            if (localStorage.getItem('slwarehouse')) {
                localStorage.removeItem('slwarehouse');
            }
            if (localStorage.getItem('slnote')) {
                localStorage.removeItem('slnote');
            }
            if (localStorage.getItem('slinnote')) {
                localStorage.removeItem('slinnote');
            }
            if (localStorage.getItem('slcustomer')) {
                localStorage.removeItem('slcustomer');
            }
            if (localStorage.getItem('slbiller')) {
                localStorage.removeItem('slbiller');
            }
            if (localStorage.getItem('slcurrency')) {
                localStorage.removeItem('slcurrency');
            }
            if (localStorage.getItem('sldate')) {
                localStorage.removeItem('sldate');
            }
            if (localStorage.getItem('slsale_status')) {
                localStorage.removeItem('slsale_status');
            }
            if (localStorage.getItem('slpayment_status')) {
                localStorage.removeItem('slpayment_status');
            }
            if (localStorage.getItem('paid_by')) {
                localStorage.removeItem('paid_by');
            }
            if (localStorage.getItem('amount_1')) {
                localStorage.removeItem('amount_1');
            }
            if (localStorage.getItem('paid_by_1')) {
                localStorage.removeItem('paid_by_1');
            }
            if (localStorage.getItem('pcc_holder_1')) {
                localStorage.removeItem('pcc_holder_1');
            }
            if (localStorage.getItem('pcc_type_1')) {
                localStorage.removeItem('pcc_type_1');
            }
            if (localStorage.getItem('pcc_month_1')) {
                localStorage.removeItem('pcc_month_1');
            }
            if (localStorage.getItem('pcc_year_1')) {
                localStorage.removeItem('pcc_year_1');
            }
            if (localStorage.getItem('pcc_no_1')) {
                localStorage.removeItem('pcc_no_1');
            }
            if (localStorage.getItem('cheque_no_1')) {
                localStorage.removeItem('cheque_no_1');
            }
            if (localStorage.getItem('slpayment_term')) {
                localStorage.removeItem('slpayment_term');
            }
            <?php $this->sma->unset_data('remove_slls');
        }
        ?>

        $(document).on('click', '.sledit', function (e) {
            if (localStorage.getItem('slitems')) {
                e.preventDefault();
                var href = $(this).attr('href');
                bootbox.confirm("<?= lang('you_will_loss_sale_data') ?>", function (result) {
                    if (result) {
                        window.location.href = href;
                    }
                });
            }
        });

        var filter_table_version = '<?= getenv('FORCAPOS_VERSION') ?>';
        var filter_table = {
            sb_checkbox : {
                field : "checkbox",
                label : "Checkbox",
                status:1
            },
            sb_date : {
                field : "date",
                label : "<?= lang("date"); ?>",
                status:1
            },
            sb_reference_no : {
                field : "reference_no",
                label : "<?= lang("reference_no"); ?>",
                status:1
            },
            sb_customer : {
                field : "customer",
                label : "<?= lang("customer"); ?>",
                status:1
            },
            sb_created_by : {
                field : "created_by",
                label : "<?= lang("created_by"); ?>",
                status:1
            },
            sb_sale_status : {
                field : "sale_status",
                label : "<?= lang("sale_status"); ?>",
                status:1
            },
            sb_payment_method : {
                field : "payment_method",
                label : "<?= lang("payment_method"); ?>",
                status:1
            },
            sb_status_kredit_pro : {
                field : "status_kredit_pro",
                label : "Status Kredit Pro",
                status:1
            },
            sb_grand_total : {
                field : "grand_total",
                label : "<?= lang("grand_total"); ?>",
                status:1
            },
            sb_paid : {
                field : "paid",
                label : "<?= lang("paid"); ?>",
                status:1
            },
            sb_balance : {
                field : "balance",
                label : "<?= lang("balance"); ?>",
                status:1
            },
            sb_payment_status : {
                field : "payment_status",
                label : "<?= lang("payment_status"); ?>",
                status:1
            },
            sb_delivery_status : {
                field : "delivery_status",
                label : "<?= lang("delivery_status"); ?>",
                status:1
            },
            sb_attachment : {
                field : "attachment",
                label : "<?= lang("attachment"); ?>",
                status:1
            },
            sb_empty : {
                field : "empty",
                label : "<?= lang("empty"); ?>",
                status:0
            },
            sb_actions : {
                field : "actions",
                label : "<?= lang("actions"); ?>",
                status:1
            }
        };
        
        var filter_table_sales_booking = JSON.parse(localStorage.getItem('filter_table_sales_booking'));
        var filter_table_version_local = localStorage.getItem('filter_table_version');
        if((filter_table_sales_booking == null ||  Object.keys(filter_table_sales_booking).length != Object.keys(filter_table).length) || (filter_table_version_local == null || filter_table_version != filter_table_version_local)){
            localStorage.setItem('filter_table_sales_booking', JSON.stringify(filter_table));
            localStorage.setItem('filter_table_version', filter_table_version);
        }
        
        filter_table = JSON.parse(localStorage.getItem('filter_table_sales_booking'));
        var field_filter_key = Object.keys(filter_table);

        function setHiddenColumn(){
            for (var i = 0; i < field_filter_key.length; i++) {
                var check = filter_table[field_filter_key[i]];
                if(check.status == 1){
                    $(`#${field_filter_key[i]}`).iCheck('check');
                    oTable.fnSetColumnVis(i, true, false);
                }else{
                    $(`#${field_filter_key[i]}`).iCheck('uncheck');
                    oTable.fnSetColumnVis(i, false, false);
                }
            }
        }

        $(document).on('ifChecked', '.check_filter_datatable', function(event) {
            var checkbox = $('.check_filter_datatable');
            for(var i = 0; i < checkbox.length; i++){
                if(checkbox[i] == this){
                    oTable.fnSetColumnVis(i, true, false);
                    filter_table[this.id].status = 1;
                    localStorage.setItem('filter_table_sales_booking', JSON.stringify(filter_table));
                }
            }
        });

        $(document).on('ifUnchecked', '.check_filter_datatable', function(event) {
            var checkbox = $('.check_filter_datatable');
            for(var i = 0; i < checkbox.length; i++){
                if(checkbox[i] == this){
                    oTable.fnSetColumnVis(i, false, false);
                    filter_table[this.id].status = 0;
                    localStorage.setItem('filter_table_sales_booking', JSON.stringify(filter_table));
                }
            }
        });
    });

</script>

<?php
if ($Owner || $Admin || $GP['bulk_actions']) {
    echo form_open('sales_booking/sale_actions', 'id="action-form"');
}
?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i
            class="fa-fw fa fa-heart"></i><?= lang('list_booking_sales') . ' (' . ($warehouse_id ? $warehouse->name : lang('all_warehouses')) . ')'; ?>
        </h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?= site_url('sales_booking/add_booking_sale') ?>">
                                <i class="fa fa-plus-circle"></i> <?= lang('add_booking_sale') ?>
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
                        <!-- <li class="divider"></li>
                        <li>
                            <a href="#" class="bpo"
                            title="<b><?= lang("delete_sales") ?></b>"
                            data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>"
                            data-html="true" data-placement="left">
                            <i class="fa fa-trash-o"></i> <?= lang('delete_sales') ?>
                        </a>-->
                    </li>
                </ul>
            </li>
            <?php if (!empty($warehouses)) {
                ?>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= site_url('sales_booking/list_booking_sales') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                        <li class="divider"></li>
                        <?php
                        foreach ($warehouses as $warehouse) {
                            echo '<li><a href="' . site_url('sales_booking/list_booking_sales/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                        }
                        ?>
                    </ul>
                </li>
            <?php }
            ?>

            <li class="dropdown">
                <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                    <i class="icon fa fa-columns tip" data-placement="left" title="<?= lang("column_filter") ?>"></i>
                </a>
                <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel" style="height: 250px; overflow-y: scroll;">
                    <li style="display: none;">
                        <a href="javascript:void(0)">
                            <label for="sb_checkbox" style="width: 100%;">
                                <input id="sb_checkbox" class="checkbox check_filter_datatable" type="checkbox" checked/> <?= lang("checkbox"); ?>
                            </label>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)">
                            <label for="sb_date" style="width: 100%;">
                                <input id="sb_date" class="checkbox check_filter_datatable" type="checkbox" checked/> <?= lang("date"); ?>
                            </label>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)">
                            <label for="sb_reference_no" style="width: 100%;">
                                <input id="sb_reference_no" class="checkbox check_filter_datatable" type="checkbox" checked/> <?= lang("reference_no"); ?>
                            </label>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)">
                            <label for="sb_customer" style="width: 100%;">
                                <input id="sb_customer" class="checkbox check_filter_datatable" type="checkbox" checked/> <?= lang("customer"); ?>
                            </label>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)">
                            <label for="sb_created_by" style="width: 100%;">
                                <input id="sb_created_by" class="checkbox check_filter_datatable" type="checkbox" checked/> <?= lang("created_by"); ?>
                            </label>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)">
                            <label for="sb_sale_status" style="width: 100%;">
                                <input id="sb_sale_status" class="checkbox check_filter_datatable" type="checkbox" checked/> <?= lang("sale_status"); ?>
                            </label>
                        </a>
                    </li>

                    <li>
                        <a href="javascript:void(0)">
                            <label for="sb_payment_method" style="width: 100%;">
                                <input id="sb_payment_method" class="checkbox check_filter_datatable" type="checkbox" checked/> <?= lang("payment_method"); ?>
                            </label>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)">
                            <label for="sb_status_kredit_pro" style="width: 100%;">
                                <input id="sb_status_kredit_pro" class="checkbox check_filter_datatable" type="checkbox" checked/> Status Kredit Pro
                            </label>
                        </a>
                    </li>

                    <li>
                        <a href="javascript:void(0)">
                            <label for="sb_grand_total" style="width: 100%;">
                                <input id="sb_grand_total" class="checkbox check_filter_datatable" type="checkbox" checked/> <?= lang("grand_total"); ?>
                            </label>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)">
                            <label for="sb_paid" style="width: 100%;">
                                <input id="sb_paid" class="checkbox check_filter_datatable" type="checkbox" checked/> <?= lang("paid"); ?>
                            </label>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)">
                            <label for="sb_balance" style="width: 100%;">
                                <input id="sb_balance" class="checkbox check_filter_datatable" type="checkbox" checked/> <?= lang("balance"); ?>
                            </label>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)">
                            <label for="sb_payment_status" style="width: 100%;">
                                <input id="sb_payment_status" class="checkbox check_filter_datatable" type="checkbox" checked/> <?= lang("payment_status"); ?>
                            </label>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)">
                            <label for="sb_delivery_status" style="width: 100%;">
                                <input id="sb_delivery_status" class="checkbox check_filter_datatable" type="checkbox" checked/> <?= lang("delivery_status"); ?>
                            </label>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)">
                            <label for="sb_attachment" style="width: 100%;">
                                <input id="sb_attachment" class="checkbox check_filter_datatable" type="checkbox" checked/> <?= lang("attachment"); ?>
                            </label>
                        </a>
                    </li>
                    <li style="display: none">
                        <a href="javascript:void(0)">
                            <label for="sb_empty" style="width: 100%;">
                                <input id="sb_empty" class="checkbox check_filter_datatable" type="checkbox" checked/> <?= lang("empty"); ?>
                            </label>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)">
                            <label for="sb_actions" style="width: 100%;">
                                <input id="sb_actions" class="checkbox check_filter_datatable" type="checkbox" checked/> <?= lang("actions"); ?>
                            </label>
                        </a>
                    </li>
                </ul>
            </li>

            <li class="dropdown">
                <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                    <i class="icon fa fa-book tip" data-placement="left" title="<?= lang("manual_book") ?>"></i>
                </a>
                <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                    <li><a href="<?= $mb_sales_booking ?>" target="_blank"><i class="fa fa-book tip"></i> <?= lang('sales_booking') ?></a></li>
                    <li><a href="<?= $mb_edit_booking_sale ?>" target="_blank"><i class="fa fa-book tip"></i> <?= lang('edit_booking_sale') ?></a></li>
                    <li><a href="<?= $mb_export_excel_sales_booking ?>" target="_blank"><i class="fa fa-book tip"></i> <?= lang('export_excel_sales_booking') ?></a></li>
                    <li><a href="<?= $mb_export_pdf_sales_booking ?>" target="_blank"><i class="fa fa-book tip"></i> <?= lang('export_pdf_sales_booking') ?></a></li>
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
                <input type="text" name="filter_date_range" class="form-control input-tip" value="<?= ($this->session->userdata('filter_date_range') ? $this->session->userdata('filter_date_range') : date('d/m/Y', strtotime('-7 days')).' - '.date('d/m/Y')) ?>">
                <span id="filter_out_of_range" style="color:red; display: none;"><?= lang('filter_out_of_range') ?></span>
            </div>

            <div class="table-responsive">
                <table id="SLData" class="table table-bordered table-hover table-striped unix">
                    <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("reference_no"); ?></th>
                            <th><?= lang("customer"); ?></th>
                            <th><?= lang("created_by"); ?></th>
                            <th><?= lang("sale_status"); ?></th>
                            <th><?= lang('payment_method') ?></th>
                            <th>Status KreditPro</th>
                            <th><?= lang("grand_total"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            <th><?= lang("payment_status"); ?></th>
                            <th><?= lang("delivery_status"); ?></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th></th>
                            <th style="width:80px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                            <!-- <tr>
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
                                
                            </tr> -->
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="15" class="dataTables_empty"><?= lang("loading_data"); ?></td>
                            </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                            <tr class="active">
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkft" type="checkbox" name="check"/>
                                </th>
                                <th></th><th></th><th></th><th></th><th></th>
                                <th></th>
                                <th>Status KreditPro</th>
                                <th><?= lang("grand_total"); ?></th>
                                <th><?= lang("paid"); ?></th>
                                <th><?= lang("balance"); ?></th>
                                <th></th>
                                <th></th>
                                <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
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
<?php $sale_id = $this->session->flashdata('create_delivery'); if($sale_id){ ?>
    <a id="create_delivery" style="display:none;" href="<?=base_url("sales/add_delivery/".$sale_id) ?>" data-toggle="modal" data-target="#myModal" data-backdrop="static">Add Delivery</a>

    <script>
        $(document).ready(function() {
            $('#create_delivery').click();
        });
    </script>
<?php } ?>
<?php if ($Owner || $Admin || $GP['bulk_actions']) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php }
?>