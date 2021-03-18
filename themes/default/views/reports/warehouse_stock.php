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
    if ($this->input->post('monthly')) {
        $v .= "&monthly=" . $this->input->post('monthly');
        $show_filter_form = true;
    }
    if ($this->input->post('annually')) {
        $v .= "&annually=" . $this->input->post('annually');
        $show_filter_form = true;
    }
?>
<script src="<?= $assets; ?>js/hc/highcharts.js"></script>
<script type="text/javascript">
    $(function () {
        Highcharts.getOptions().colors = Highcharts.map(Highcharts.getOptions().colors, function (color) {
            return {
                radialGradient: {cx: 0.5, cy: 0.3, r: 0.7},
                stops: [[0, color], [1, Highcharts.Color(color).brighten(-0.3).get('rgb')]]
            };
        });
        $('#chart').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {text: ''},
            credits: {enabled: false},
            tooltip: {
                formatter: function () {
                    return '<div class="tooltip-inner hc-tip" style="margin-bottom:0;">' + this.key + '<br><strong>' + currencyFormat(this.y) + '</strong> (' + formatNumber(this.percentage) + '%)';
                },
                followPointer: true,
                useHTML: true,
                borderWidth: 0,
                shadow: false,
                valueDecimals: site.settings.decimals,
                style: {fontSize: '14px', padding: '0', color: '#000000'}
            },
            plotOptions: {
                pie: {
                    dataLabels: {
                        enabled: true,
                        formatter: function () {
                            return '<h3 style="margin:-15px 0 0 0;"><b>' + this.point.name + '</b>:<br><b> ' + currencyFormat(this.y) + '</b></h3>';
                        },
                        useHTML: true
                    }
                }
            },
            series: [{
                type: 'pie',
                name: '<?php echo $this->lang->line("stock_value"); ?>',
                data: [
                    ['<?php echo $this->lang->line("stock_value_by_price"); ?>', <?php echo $stock->stock_by_price; ?>],
                    ['<?php echo $this->lang->line("stock_value_by_cost"); ?>', <?php echo $stock->stock_by_cost; ?>],
                    ['<?php echo $this->lang->line("profit_estimate"); ?>', <?php echo ($stock->stock_by_price - $stock->stock_by_cost); ?>],
                ]

            }]
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

    $(document).ready(function () {
        $('#StCaRData').dataTable({
            "aaSorting": [[0, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getWarehouseStockCard?v=1'.$v ) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"mRender": fldd}, null, null, null, {"mRender": formatQuantity}, {"mRender": formatQuantity}, {"mRender": formatQuantity},{"mRender": formatQuantity}]
        });
    });
</script>

<?php if ($Owner || $Admin) { ?>
    <div class="box" style="margin-top: 15px;">
        <div class="box-header">
            <h2 class="blue"><i
                    class="fa-fw fa fa-bar-chart-o"></i><?= lang('warehouse_stock') . ' (' . ($warehouse ? $warehouse->name : lang('all_warehouses')) . ')'; ?>
            </h2>

            <div class="box-icon">
                <ul class="btn-tasks">
                    <?php if (!empty($warehouses) && ($Owner || $Admin)) { ?>
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
                            <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i
                                    class="icon fa fa-building-o tip" data-placement="left"
                                    title="<?= lang("warehouses") ?>"></i></a>
                            <ul class="dropdown-menu pull-right tasks-menus" role="menu"
                                aria-labelledby="dLabel">
                                <li><a href="<?= site_url('reports/warehouse_stock') ?>"><i
                                            class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                                <li class="divider"></li>
                                <?php
                                foreach ($warehouses as $warehouse) {
                                    echo '<li ' . ($warehouse_id && $warehouse_id == $warehouse->id ? 'class="active"' : '') . '><a href="' . site_url('reports/warehouse_stock/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                                }
                                ?>
                            </ul>
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
                    <?php } ?>
                </ul>
            </div>
        </div>
        <div class="box-content">
            <div class="row">
                <div class="col-lg-12">
                    <p class="introtext"><?php echo lang('warehouse_stock_heading'); ?></p>
                    <div id="filter-form">
                        <?php echo form_open("reports/warehouse_stock"); ?>
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
                                        <label class="control-label"><?= lang("month"); ?></label>
                                        <?php
                                            $opts = array('1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April', '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus', '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember');
                                            echo form_dropdown('monthly', $opts, (isset($_POST['monthly']) ? $_POST['monthly'] : date('m')), 'class="form-control" id="monthly" required="required"');
                                        ?>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label class="control-label"><?= lang("year"); ?></label>
                                        <?php
                                            for ($y = date('Y'); $y >= 1990; $y--) {
                                                $opts_year[$y] = $y;
                                            }
                                            echo form_dropdown('annually', $opts_year, (isset($_POST['annually']) ? $_POST['annually'] : date('Y')), 'class="form-control" id="annually" required="required"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div
                                    class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                            </div>
                        <?php echo form_close(); ?>
                    </div>

                    <?php if ($totals) { ?>
                        <div class="small-box padding1010 col-sm-6 bblue">
                            <div class="inner clearfix">
                                <a>
                                    <h3><?= $this->sma->formatQuantity($totals->total_items) ?></h3>

                                    <p><?= lang('total_items') ?></p>
                                </a>
                            </div>
                        </div>

                        <div class="small-box padding1010 col-sm-6 bdarkGreen">
                            <div class="inner clearfix">
                                <a>
                                    <h3><?= $this->sma->formatQuantity($totals->total_quantity) ?></h3>

                                    <p><?= lang('total_quantity') ?></p>
                                </a>
                            </div>
                        </div>
                        <div class="clearfix" style="margin-top:20px;"></div>
                    <?php } ?>
                    <div id="chart" style="width:100%; height:450px;"></div>
                    <div class="table-responsive" style="margin-top:80px;">
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
    </div>
<?php } ?>

<script>
$(document).ready(function () {
    $('#pdf').click(function (event) {
        event.preventDefault();

        var a = "<?=site_url('reports/getExportWarehouseStockCard/pdf') ?>?v=1<?= $v ?>";
        window.location.href = a;
        console.log(a);
        return false;   
    });

    $('#xls').click(function (event) {
        event.preventDefault();

        var a = "<?=site_url('reports/getExportWarehouseStockCard/xls') ?>?v=1<?= $v ?>";
        window.location.href = a;
        console.log(a);
        return false;   
    });
});
</script>
