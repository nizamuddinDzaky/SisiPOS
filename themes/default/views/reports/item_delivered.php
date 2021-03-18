<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        function set_date_title(){
            page_title = '<?=$page_title?>';
            s_date = '<?="01/".date('m/Y')?>';
            e_date = '<?=date('d/m/Y')?>';
            start_date = $('#start_date').val();
            end_date = $('#end_date').val();

            if (start_date != '') {
                
                s_date = start_date;
            }

            if (end_date != '') {
                e_date = end_date;
            }
            icon = '<i class="fa-fw fa fa-shopping-cart"></i>';
            $('#page-title').html(icon + '' + page_title +' '+ s_date + ' - ' +e_date);
        }
        $('#SLData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/get_item_delivered/?') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                url_=sSource;
                
                let end_date = $('#end_date').val();
                let start_date = $('#start_date').val();
                
                if (end_date == '') {
                    end_date = '-';
                }else{
                    end_date = end_date.replace(/\//g, '-');
                }

                if (start_date == '') {
                    start_date = '-';
                }else{
                    start_date = $('#start_date').val().replace(/\//g, '-');
                }

                arrurl=url_.split("/");
                for (var i = 0; i < arrurl.length; i++) {
                    if (arrurl[i] == 'get_item_delivered') {
                        arrurl[i + 1] = start_date;
                        arrurl[i + 2] = end_date;
                    }
                }
                url_=arrurl.join().replace(/,/g,"/");
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': url_, 'data': aoData, 'success': fnCallback});
                
                $('#start_date').change(function () {
                    var str = $(this).val();/**/
                    str = str.replace(/\//g, "-");
                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });
                    arrayURL = url_.split("/");
                    for (var i = 0; i < arrayURL.length; i++) {
                        if (arrayURL[i] == 'get_item_delivered') {
                            arrayURL[i + 1] = str;
                        }
                    }
                    url_ = arrayURL.join().replace(/,/g, "/");
                    $.ajax({
                        "type": "POST",
                        "dataType": 'json',
                        "url": url_, //sending server side status and filtering table
                        "data": aoData,
                        "success": function (data) {
                            fnCallback(data);
                            set_date_title();
                        }
                    });
                });

                $('#end_date').change(function () {
                    var str = $(this).val();/**/
                    str = str.replace(/\//g, "-");

                    // start = $('#start_date').val();
                    // str = start.replace(/\//g, "-");
                    // if (start == '') {
                    //     start = '-';
                    // }

                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });
                    arrayURL = url_.split("/");
                    for (var i = 0; i < arrayURL.length; i++) {
                        if (arrayURL[i] == 'get_item_delivered') {
                            // arrayURL[i + 1] = start;
                            arrayURL[i + 2] = str;
                        }
                    }
                    url_ = arrayURL.join().replace(/,/g, "/");
                    $.ajax({
                        "type": "POST",
                        "dataType": 'json',
                        "url": url_, //sending server side status and filtering table
                        "data": aoData,
                        "success": function (data) {
                            fnCallback(data);
                            set_date_title();

                        }
                    });
                });
                
            },
            
            "aoColumns": [{"mRender": fld},null,null,null,null,null,null,null, {"mRender": row_status}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": pay_status},null,null, {"mRender": formatQuantity},null, {"mRender": fld},null, {"mRender": formatQuantity},{"mRender": ds},null],
        });

    });

</script>

<?php echo form_open('reports/get_item_delivered', 'id="action-form"'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue" id='page-title'><i class="fa-fw fa fa-shopping-cart"></i><?= $page_title ?> <?='01/'.date('m/Y')?> - <?=date('d/m/Y')?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" id="excel" class="tip" title="<?= lang('download_xls') ?>" data-action="export_excel">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <div class="" id="form">
                    <p class="introtext"><?php echo $this->lang->line("list_results"); ?></p>
                    <div class="col-sm-6" style="padding-left:inherit;">
                        <div class="form-group">
                            <?= lang("start_date", "start_date"); ?>
                            <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : '01/'.date('m/Y')), 'class="form-control date" id="start_date"'); ?>
                        </div>
                    </div>
                    <div class="col-sm-6" style="padding-right:inherit">
                        <div class="form-group">
                            <?= lang("end_date", "end_date"); ?>
                            <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : date('d/m/Y')), 'class="form-control date" id="end_date"'); ?>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="SLData" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr class="active">
                                <th><?= lang("sale_date"); ?></th>
                                <th><?= lang("sale_reference_no"); ?></th>
                                <th><?= lang("distributor_code") ?></th>
                                <th><?= lang("distributor"); ?></th>
                                <th><?= lang("warehouse_code"); ?></th>
                                <th><?= lang("warehouse"); ?></th>
                                <th><?= lang("customer_code"); ?></th>
                                <th><?= lang("customer") ?></th>
                                <th><?= lang("sale_status"); ?></th>
                                <th><?= lang("grand_total"); ?></th>
                                <th><?= lang("total_paid"); ?></th>
                                <th><?= lang("payment_status"); ?></th>
                                <th><?= lang("product_code") ?></th>
                                <th><?= lang("product"); ?></th>
                                <th><?= lang("total_quantity"); ?></th>
                                <th><?= lang("created_by"); ?></th>
                                <th><?= lang("delivery_date"); ?></th>
                                <th><?= lang("do_reference_no") ?></th>
                                <th><?= lang("quantity_sent"); ?></th>
                                <th><?= lang("delivery_status"); ?></th>
                                <th> Delivery <?= lang("created_by") ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="20" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                            </tr>
                        </tbody>
                        
                    </table>
                </div>

            </div>

        </div>
    </div>
</div>
<?php if ($Owner || $GP['bulk_actions'] || $Principal) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>
<?php if ($action && $action == 'add') {
    echo '<script>$(document).ready(function(){$("#add").trigger("click");});</script>';
}
?>