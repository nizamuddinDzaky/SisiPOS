<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
    .bred p{
        font-size: 18px;
    }
    .bred h1{
        margin-top: -5px;
    }
    .highcharts-exporting-group{
        display: none;
    }
    /*.highcharts-text-outline{*/
        /*stroke-width: 0;*/
    /*}*/
    .highcharts-credits{
        display: none;
    }
    .no-button{
        cursor: auto;
    }
    .select2-container-multi{
        overflow: auto;
    }
    .daterangepicker_start_input, .daterangepicker_end_input{
        display: none;
    }
</style>

<script src="<?= $assets ?>js/highchart/highcharts.js"></script>
<script src="<?= $assets ?>js/highchart/modules/data.js"></script>
<script src="<?= $assets ?>js/highchart/modules/exporting.js"></script>
<script src="<?= $assets ?>js/highchart/modules/export-data.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/daterange_picker/moment.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/daterange_picker/daterangepicker.min.js"></script>


<link rel="stylesheet" type="text/css" href="<?= $assets ?>js/daterange_picker/daterangepicker.css" />

<script type="text/javascript">
    $(function () {
        var nowDate = new Date();
        var today = new Date(nowDate.getFullYear(), nowDate.getMonth(), nowDate.getDate(), 0, 0, 0, 0);
        var maxLimitDate = new Date(nowDate.getFullYear() + 1, nowDate.getMonth(), nowDate.getDate(), 0, 0, 0, 0);
        const months = ["Jan", "Feb", "Mar","Apr", "May", "Jun", "Jul", "Agust", "Sept", "Ockt", "Nov", "Dec"];
        var Colors=new Array("#4285f4","#ad1457");
        $(document).ready(function() {

            var line = $.ajax({
                'dataType': 'json',
                'url': '<?= site_url('welcome/getDashboardByDate/') ?>',
                'success': function (data) {
                    let datas = data;

                    var categories = []; //creating array for storing browser type in array.
                    var series_data = [];
                    var series_data1 = [];

                    for (var i = 0; i < datas.length; i++) {
                        var CDate = new Date(datas[i]['tanggal_transaksi']);
                        var date = CDate.getDate()+' '+months[CDate.getMonth()];
                        categories.push(date);
                        series_data.push(parseInt(datas[i]['registrasi'])||0);
                        series_data1.push(parseInt(datas[i]['transaksi'])||0);
                    }
                    lineChart(categories, series_data,series_data1);
                }
            });
            var bar1 = $.ajax({
                'dataType': 'json',
                'url': '<?= site_url('welcome/getDashboardByDist/') ?>',
                'success': function (data) {
                    let datas = data;

                    var categories = []; //creating array for storing browser type in array.
                    var series_data = [];
                    var series_data1 = [];

                    for (var i = 0; i < datas.length; i++) {
                        categories.push(datas[i]['distributor']);
                        series_data.push(parseInt(datas[i]['registrasi'])||0);
                        series_data1.push(parseInt(datas[i]['transaksi'])||0);
                    }
                    barChart1(categories, series_data,series_data1);
                }
            });
            var bar2 = $.ajax({
                'dataType': 'json',
                'url': '<?= site_url('welcome/getDashboardByProvince/')?>',
                'success': function (data) {
                    let datas = data;

                    var categories = []; //creating array for storing browser type in array.
                    var series_data = [];
                    var series_data1 = [];

                    for (var i = 0; i < datas.length; i++) {
                        categories.push(datas[i]['provinsi']);
                        series_data.push(parseInt(datas[i]['registrasi'])||0);
                        series_data1.push(parseInt(datas[i]['transaksi'])||0);
                    }
                    barChart2(categories, series_data,series_data1);
                }
            });

            $("#province").click(function () {
                var el = document.getElementsByName('provinsi')[0];
                var result = [];
                var options = el && el.options;
                var opt;

                for (var i = 0, iLen = options.length; i < iLen; i++) {
                    opt = options[i];

                    if (opt.selected) {
                        result.push(opt.value || opt.text);
                    }
                }
                $.ajax({
                    'dataType': 'json',
                    'url': '<?= site_url('welcome/getDashboardByProvince/')?>?provinsi='+result,
                    'success': function (data) {
                        let datas = data;

                        var categories = []; //creating array for storing browser type in array.
                        var series_data = [];
                        var series_data1 = [];

                        for (var i = 0; i < datas.length; i++) {
                            categories.push(datas[i]['provinsi']);
                            series_data.push(parseInt(datas[i]['registrasi'])||0);
                            series_data1.push(parseInt(datas[i]['transaksi'])||0);
                        }
                        barChart2(categories, series_data,series_data1);
                    }
                });
            });
            $("#distri").click(function () {
                var el = document.getElementsByName('distrib')[0];
                var result = [];
                var options = el && el.options;
                var opt;

                for (var i = 0, iLen = options.length; i < iLen; i++) {
                    opt = options[i];

                    if (opt.selected) {
                        result.push(opt.value || opt.text);
                    }
                }
                $.ajax({
                    'dataType': 'json',
                    'url': '<?= site_url('welcome/getDashboardByDist/')?>?distrib='+result,
                    'success': function (data) {
                        let datas = data;

                        var categories = []; //creating array for storing browser type in array.
                        var series_data = [];
                        var series_data1 = [];

                        for (var i = 0; i < datas.length; i++) {
                            categories.push(datas[i]['distributor']);
                            series_data.push(parseInt(datas[i]['registrasi'])||0);
                            series_data1.push(parseInt(datas[i]['transaksi'])||0);
                        }
                        barChart1(categories, series_data,series_data1);
                    }
                });
            });
            $("#prod_map").click(function () {
                var date = document.getElementById('dateranges2').value;
                var product = document.getElementById('product').value;
                date = date.split(" - ");
                var start = date[0];
                var end = date[1];

                console.log(start+end+product);
            });

            $("#dateranges").daterangepicker({
                maxDate: today,
                opens: 'left',
                autoUpdateInput: true,
                locale: {
                    format: 'DD/MM/YYYY',
                }
            },function(start, end) {
                $.ajax({
                    'dataType': 'json',
                    'url': '<?= site_url('welcome/getDashboardByDate/') ?>?start='+start.format('YYYY-MM-DD')+'&end='+end.format('YYYY-MM-DD'),
                    'success': function (data) {
                        let datas = data;

                        var categories = []; //creating array for storing browser type in array.
                        var series_data = [];
                        var series_data1 = [];

                        for (var i = 0; i < datas.length; i++) {
                            var CDate = new Date(datas[i]['tanggal_transaksi']);
                            var date = CDate.getDate()+' '+months[CDate.getMonth()];
                            categories.push(date);
                            series_data.push(parseInt(datas[i]['registrasi'])||0);
                            series_data1.push(parseInt(datas[i]['transaksi'])||0);
                        }
                        lineChart(categories, series_data,series_data1);
                    }
                });
            });
            $("#dateranges2").daterangepicker({
                maxDate: today,
                opens: 'left',
                autoUpdateInput: true,
                locale: {
                    format: 'DD/MM/YYYY',
                }
            });

            //script untuk json product
            $.get("<?= site_url('welcome/getDashboardByDate/') ?>", function(data) {
                console.log(data);
                $('#product').empty();
                $.each(data, function(index,subCatObj){
                    // $('#kelurahan').append(''+subCatObj.name+'');
                    $('#product').append('<option value="' + subCatObj.id + '">' + subCatObj.name + '</option>');
                });
            });
            //end script untuk json product

            $("#resetdate").click(function(){
                $('#dateranges').val("").daterangepicker("update");
                $.ajax({
                    'dataType': 'json',
                    'url': '<?= site_url('welcome/getDashboardByDate/') ?>',
                    'success': function (data) {
                        let datas = data;

                        var categories = []; //creating array for storing browser type in array.
                        var series_data = [];
                        var series_data1 = [];

                        for (var i = 0; i < datas.length; i++) {
                            var CDate = new Date(datas[i]['tanggal_transaksi']);
                            var date = CDate.getDate()+' '+months[CDate.getMonth()];
                            categories.push(date);
                            series_data.push(parseInt(datas[i]['registrasi'])||0);
                            series_data1.push(parseInt(datas[i]['transaksi'])||0);
                        }
                        lineChart(categories, series_data,series_data1);
                    }
                });
            });

            function lineChart(categories, series_data,series_data1) {
                Highcharts.chart('line-chart', {
                    title: {
                        text: ' '
                    },
                    colors:Colors,
                    xAxis: {
                        categories: categories
                    },
                    yAxis: {
                        title: {
                            text: 'Total Transaksi',
                        },
                        align:'left'
                    },
                    legend: {
                        align: 'left',
                        verticalAlign: 'top',
                        borderWidth: 0
                    },

                    plotOptions: {
                        line: {
                            dataLabels: {
                                enabled: true
                            },
                        }
                    },
                    series: [{
                        name: 'Registrasi',
                        data: series_data
                    },{
                        name: 'Transaksi',
                        data: series_data1
                    }],
                });
            }
            function barChart1(categories, series_data,series_data1) {
                Highcharts.chart('bar_chart_1', {
                    chart: {
                        type: 'column'
                    },
                    legend: {
                        align: 'left',
                        verticalAlign: 'top',
                        symbolRadius: 0
                    },
                    plotOptions: {
                        column: {
                            dataLabels: {
                                enabled: true,
                                color: '#FFFFFF',
                                y: 25,
                                style: {
                                    fontSize: '13px',
                                    fontFamily: 'Verdana, sans-serif'
                                }
                            },
                            pointPadding: 0.01,
                        }
                    },
                    colors:Colors,
                    title: {
                        text: 'AksesToko User Registration & Transaction by Distributor'
                    },
                    xAxis: {
                        categories: categories
                    },
                    yAxis: {
                        allowDecimals: false,
                        title: {
                            text: 'Total Transaksi'
                        }
                    },
                    series: [{
                        name: 'Registrasi',
                        data: series_data
                    },{
                        name: 'Toko Transaksi',
                        data: series_data1
                    }],
                });
            }
            function barChart2(categories, series_data,series_data1) {
                Highcharts.chart('bar_chart_2', {
                    chart: {
                        type: 'column'
                    },
                    legend: {
                        align: 'left',
                        verticalAlign: 'top',
                        symbolRadius: 0
                    },
                    plotOptions: {
                        column: {
                            dataLabels: {
                                enabled: true,
                                color: '#FFFFFF',
                                y: 25,
                                style: {
                                    fontSize: '13px',
                                    fontFamily: 'Verdana, sans-serif'
                                }
                            },
                            pointPadding: 0.01,
                        }
                    },
                    colors:Colors,
                    title: {
                        text: 'AksesToko User Registration & Transaction by Province'
                    },
                    xAxis: {
                        categories: categories
                    },
                    yAxis: {
                        allowDecimals: false,
                        title: {
                            text: 'Total Transaksi'
                        }
                    },
                    series: [{
                        name: 'Registrasi',
                        data: series_data
                    },{
                        name: 'Toko Transaksi',
                        data: series_data1
                    }],
                });
            }
        });
    });
</script>
<div class="row">
    <div class="col-md-4"></div>
    <div class="col-md-2">
        <div class="bred white no-button quick-button small" >
            <p>Total Registrasi</p>
            <h1><?php echo $total_aktivasi[0]->jumlah; ?></h1>
        </div>
    </div>
    <div class="col-md-2">
        <div class="bred white no-button quick-button small">
            <p>Total Transaksi</p>
            <h1><?php echo $total_transaksi[0]->jumlah; ?></h1>
        </div>

    </div>
    <div class="col-md-2">
        <div class="bred white no-button quick-button small">
            <p>Toko Transaksi</p>

            <h1><?php  echo $toko_transaksi[0]->jumlah; ?></h1>
        </div>

    </div>
    <div class="col-md-2">
        <div class="bred white no-button quick-button small">
            <p>Toko Repeat</p>
            <h1><?php echo $toko_repeat[0]->jumlah; ?></h1>
        </div>
    </div>
</div>
<div class="row" style="margin-top: 20px">

    <div class="col-md-12">
        <div class="row" style="margin-bottom: 20px">
            <div class="col-md-4">
                <input id="dateranges" class="form-control" type="text" name="dateranges" />
            </div>
            <div class="col-md-1">
                <button class="btn btn-success" id="resetdate">Reset Date</button>
            </div>
        </div>
        <div id="line-chart" style="height: 400px; margin: 0 auto"></div>
    </div>
    <div class="col-md-12" style="margin-top: 20px">
        <div class="row">
            <div class="col-md-10">
                <div class="form-group">
                    <select name="distrib" class="form-control" id="distrib" multiple >
                        <?php foreach ($distrib as $row) {
                            echo '<option value="'.$row->distributor.'">'.$row->distributor.'</option>';
                        } ?>
                    </select>

                </div>
            </div>
            <div class="col-md-1">
                <button class="btn btn-success" id="distri" >Change Distributor</button>
            </div>
        </div>
        <div id="bar_chart_1" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
    </div>
    <div class="col-md-12" style="margin-top: 20px;margin-bottom: 20px">
        <div class="row">
            <div class="col-md-10">
                <div class="form-group">
                    <select name="provinsi" class="form-control" id="provinsi" multiple >
                        <?php foreach ($province as $row) {
                            echo '<option value="'.$row->province_name.'">'.$row->province_name.'</option>';
                        } ?>
                    </select>

                </div>
            </div>
            <div class="col-md-1">
                <button class="btn btn-success" id="province" >Change Province</button>
            </div>
        </div>

        <div id="bar_chart_2" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
    </div>
</div>



