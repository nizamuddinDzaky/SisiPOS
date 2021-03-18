<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$v="";
$show_filter_form = false;
if ($this->input->post('product')) {
    $v .= "&product=" . $this->input->post('product');
    $show_filter_form = true;
}
if ($this->input->post('warehouse')) {
    $v .= "&warehouse=" . $this->input->post('warehouse');
    $show_filter_form = true;
}
if ($this->input->post('start_date') && $this->input->post('end_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
    $v .= "&end_date=" . $this->input->post('end_date');
    $show_filter_form = true;
}
?>
<script>
    $(document).ready(function () {
        $('#StCaRData').dataTable({
            "aaSorting": [[0, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getStockCard?v=1'.$v ) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"mRender": fld}, null, null, null, {"mRender": formatQuantity}, {"mRender": formatQuantity}, {"mRender": formatQuantity},{"mRender": formatQuantity}]
        });

        <?php if($show_filter_form){ ?>
            setTimeout(function(){$("#filter-form").slideDown();}, 100);
        <?php } ?>
        
        $('#filter-form').hide();
        $('.toggle_down').click(function () {
            $("#filter-form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#filter-form").slideUp();
            return false;
        });

    });
    
</script>

<div class="box">
    <div class="box-header">
        <h2 id="title_warehouse" class="blue"><i class="fa-fw fa fa-gift"></i><?= $page_title ?><?php
        if ($this->input->post('start_date')) {
            echo ' (' . ($warehouse_id ? $warehouse->name : lang('all_warehouses')) . ')'.lang('from').$this->sma->hrsd($this->input->post('start_date')).lang('to').$this->sma->hrsd($this->input->post('end_date'));
        } else {
            echo ' (' . ($warehouse_id ? $warehouse->name : lang('all_warehouses')) . ')';
        }
        ?>
        </h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="javascript:void(0)" id="pdf" class="tip" title="<?= lang('download_pdf') ?>">
                        <i class="icon fa fa-file-pdf-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="javascript:void(0)" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
                <?php if (!empty($warehouses)) {
                    ?>
<!--                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?=lang("warehouses")?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?=site_url('reports/stock_card')?>"><i class="fa fa-building-o"></i> <?=lang('all_warehouses')?></a></li>
                            <li class="divider"></li>
                            <?php
                            	foreach ($warehouses as $warehouse) {
                            	        echo '<li ' . ($warehouse_id && $warehouse_id == $warehouse->id ? 'class="active"' : '') . '><a href="' . site_url('purchases/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                            	    }
                                ?>
                        </ul>
                    </li>-->
                <?php }?>
            </ul>
        </div>
    </div>
    <?php echo form_open("reports/stock_card"); ?>
        <div class="box-content">
            <div class="row">
                <div class="col-lg-12">
                    <p class="introtext"><?php echo $this->lang->line("list_results"); ?></p>
                    <div id="filter-form">
                        <div class="row">
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <?= lang("product", "suggest_product"); ?>
                                    <?php echo form_input('sproduct', (isset($_POST['sproduct']) ? $_POST['sproduct'] : ""), 'class="form-control" id="suggest_product"'); ?>
                                    <input type="hidden" name="product" value="<?= isset($_POST['product']) ? $_POST['product'] : "" ?>" id="report_product_id"/>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                                    <?php
                                    $wh[""] = lang('select').' '.lang('warehouse');
                                    foreach ($warehouses as $warehouse) {
                                        $wh[$warehouse->id] = $warehouse->name;
                                    }
                                    echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("validity") . '"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <?= lang("start_date", "start_date"); ?>
                                    <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : date('d/m/Y')), 'class="form-control date" id="start_date"'); ?>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <?= lang("end_date", "end_date"); ?>
                                    <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : date('d/m/Y')), 'class="form-control date" id="end_date"'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div
                                class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="StCaRData" class="table table-bordered table-hover table-striped">
                            <thead>
                            <tr>
                                <th><?php echo $this->lang->line("date"); ?></th>
                                <th><?php echo $this->lang->line("report_stock_card_total_transaksi"); ?></th>
                                <th><?php echo $this->lang->line("report_stock_card_warehouse_name"); ?></th>
                                <th><?php echo $this->lang->line("report_stock_card_product_name"); ?></th>
                                <th><?php echo $this->lang->line("report_stock_card_stock_first"); ?></th>
                                <th><?php echo $this->lang->line("report_stock_card_stock_in"); ?></th>
                                <th><?php echo $this->lang->line("report_stock_card_stock_out"); ?></th>
                                <th><?php echo $this->lang->line("report_stock_card_stock_last"); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                </div>

            </div>
        </div>
    <?php echo form_close(); ?>
</div>

<script>
$(document).ready(function () {
    if($("#warehouse").val()){
        $('#title_warehouse').html("<i class=\"fa-fw fa fa-gift\"></i> Stock Card Report ("+$("#warehouse option:selected").text()+")");
    }

    $('#pdf').click(function (event) {
        // var product = $('#report_product_id').val() != '' ? $('#report_product_id').val() : '-';
        // var warehouse = $('#warehouse').val() != '' ? $('#warehouse').val() : '-';
        // var start_date = $('#start_date').val() != '' ? $('#start_date').val().split('/').join('-') : '-';
        // var end_date = $('#end_date').val() != '' ? $('#end_date').val().split('/').join('-') : '-';
        event.preventDefault();

        var a = "<?=site_url('reports/getExportStockCard/pdf') ?>?v=1<?= $v ?>"; //"<?=site_url('reports/getExportStockCard/')?>"+product+"/"+warehouse+"/"+start_date+"/"+end_date+"/pdf/?v=1";
        window.location.href = a;
        console.log(a);
        return false;   
    });

    $('#xls').click(function (event) {
        // var product = $('#report_product_id').val() != '' ? $('#report_product_id').val() : '-';
        // var warehouse = $('#warehouse').val() != '' ? $('#warehouse').val() : '-';
        // var start_date = $('#start_date').val() != '' ? $('#start_date').val().split('/').join('-') : '-';
        // var end_date = $('#end_date').val() != '' ? $('#end_date').val().split('/').join('-') : '-';
        event.preventDefault();

        var a = "<?=site_url('reports/getExportStockCard/xls') ?>?v=1<?= $v ?>"; //"<?=site_url('reports/getExportStockCard/')?>"+product+"/"+warehouse+"/"+start_date+"/"+end_date+"/xls/?v=1";
        window.location.href = a;
        console.log(a);
        return false;   
    });
});
</script>