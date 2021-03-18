<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
//echo "<pre>";print_r($products);print_r($purchases);echo"</pre>";
//for($i=0;$i<sizeof($products);$i++){
//    if($products[$i]->company_id==1){
//        echo $products[$i]->name."<br>";
//        for($m=0;$m<sizeof($purchases[$i]);$m++){
//            echo $purchases[$i][$m]->qty." ".$purchases[$i][$m]->date."<br>";
//        }
//        echo "<br><br>";
//    }
//}die();?>
<script src="<?= $assets; ?>js/hc/highcharts.js"></script>
<script type="text/javascript">
    $(function () {
        Highcharts.getOptions().colors = Highcharts.map(Highcharts.getOptions().colors, function (color) {
            return {
                radialGradient: {cx: 0.5, cy: 0.3, r: 0.7},
                stops: [[0, color], [1, Highcharts.Color(color).brighten(-0.3).get('rgb')]]
            };
        });

        $('#sales').highcharts({
//            chart: {type: 'column'},
            chart: {zoomType:'xy'},
            title: {text: ''},
            credits: {enabled: false},
            xAxis: {type: 'category', labels: {rotation: -60, style: {fontSize: '13px'}}},
            yAxis: {min: 0, title: {text: ''}},
            legend: {enabled: true},
            series: [<?php for($i=0;$i<sizeof($products);$i++){ if($products[$i]->company_id==1){?>
                {name:'<?php echo $products[$i]->name; ?>',
                    type: 'line',
                    data:[<?php for($m=0;$m<sizeof($sales[$i]);$m++) {
                        if(!empty($sales[$i][$m]))
                        echo "['".$sales[$i][$m]->date."', ".$sales[$i][$m]->qty."],";
                    }?>]},
                <?php }}?>
//                {name:'SEMEN PPC 50KG',type: 'line',data: [<?php foreach ($sales1 as $r) {echo "['".$r->date."', ".$r->qty."],";}?>]},
//                {name:'SEMEN PPC 40KG',type:'line',data:[<?php foreach($sales2 as $r){echo "['".$r->date."', ".$r->qty."],";}?>]},
//                {name:'SEMEN PUTIH 40KG',type:'line',data:[<?php foreach($sales3 as $r){echo "['".$r->date."', ".$r->qty."],";}?>]},
//                {name:'SEMEN OPC 50KG',type:'line',data:[<?php foreach($sales4 as $r){echo "['".$r->date."', ".$r->qty."],";}?>]},
//                {name:'SEMEN PCC 40KG',type:'line',data:[<?php foreach($sales5 as $r){echo "['".$r->date."', ".$r->qty."],";}?>]},
//                {name:'SEMEN PCC 50KG',type:'line',data:[<?php foreach($sales6 as $r){echo "['".$r->date."', ".$r->qty."],";}?>]},
//                {name:'SEMEN OPC 40KG',type:'line',data:[<?php foreach($sales7 as $r){echo "['".$r->date."', ".$r->qty."],";}?>]}
            ]
        });

        $('#purchases').highcharts({
//            chart: {type: 'column'},
            chart:{zoomType: 'xy'},
            title: {text: ''},
            credits: {enabled: false},
            xAxis: {type: 'category', labels: {rotation: -60, style: {fontSize: '13px'}}},
            yAxis: {min: 0, title: {text: ''}},
            legend: {enabled: true},
            series: [<?php for($i=0;$i<sizeof($products);$i++){ if($products[$i]->company_id==1){?>
                {name:'<?php echo $products[$i]->name; ?>',
                    type: 'line',
                    data:[<?php for($m=0;$m<sizeof($purchases[$i]);$m++) {
                        if(!empty($purchases[$i][$m]))
                        echo "['".$purchases[$i][$m]->date."', ".$purchases[$i][$m]->qty."],";
                    }?>]},
                <?php }}?>
//                {name:'SEMEN PPC 50KG',type: 'line',data: [<?php foreach ($purchases1 as $r) {echo "['".$r->date."', ".$r->qty."],";}?>]},
//                {name:'SEMEN PPC 40KG',type:'line',data:[<?php foreach($purchases2 as $r){echo "['".$r->date."', ".$r->qty."],";}?>]},
//                {name:'SEMEN PUTIH 40KG',type:'line',data:[<?php foreach($purchases3 as $r){echo "['".$r->date."', ".$r->qty."],";}?>]},
//                {name:'SEMEN OPC 50KG',type:'line',data:[<?php foreach($purchases4 as $r){echo "['".$r->date."', ".$r->qty."],";}?>]},
//                {name:'SEMEN PCC 40KG',type:'line',data:[<?php foreach($purchases5 as $r){echo "['".$r->date."', ".$r->qty."],";}?>]},
//                {name:'SEMEN PCC 50KG',type:'line',data:[<?php foreach($purchases6 as $r){echo "['".$r->date."', ".$r->qty."],";}?>]},
//                {name:'SEMEN OPC 40KG',type:'line',data:[<?php foreach($purchases7 as $r){echo "['".$r->date."', ".$r->qty."],";}?>]}
            ]
        });

        $('#form').hide();
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue">
            <i class="fa-fw fa fa-line-chart"></i>

        </h2>
        <?php if (!empty($warehouses)) { ?>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= site_url('reports/best_sellers') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                        <li class="divider"></li>
                        <?php
                        foreach ($warehouses as $warehouse) {
                            echo '<li><a href="' . site_url('reports/best_sellers/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                        }
                        ?>
                    </ul>
                </li>
            </ul>
        </div>
        <?php } ?>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>"><i
                            class="icon fa fa-toggle-up"></i></a></li>
                <li class="dropdown"><a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>"><i
                            class="icon fa fa-toggle-down"></i></a></li>
            </ul>
        </div>
    </div>
    <div class="box-content">

    <div class="row" style="margin-bottom: 15px;">
        <div class="col-sm-12">
            <div id="form">
                <?php echo form_open("reports/monitoring"); ?>
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label class="control-label" for="biller"><?= lang("company"); ?></label>
                            <?php
                            $bill[""] = lang('select').' '.lang('company');
                            foreach ($billers as $biller) {
                                $bill[$biller->company_id] = $biller->company;
                            }
                            echo form_dropdown('biller', $bill, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("company") . '"');
                            ?>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <?= lang("start_date", "start_date"); ?>
                            <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control datetime" id="start_date"'); ?>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <?= lang("end_date", "end_date"); ?>
                            <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control datetime" id="end_date"'); ?>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div
                        class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                </div>
                <?php echo form_close(); ?>
            </div>
            <div class="clearfix"></div>
            <div class="box">
                <div class="box-header">
                    <h2 class="blue"><?= lang('sales');//$m1; ?>
                    </h2>
                </div>
                <div class="box-content">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="sales" style="width:100%; height:450px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="box">
                <div class="box-header">
                    <h2 class="blue"><?= lang('purchases');//$m3; ?>
                    </h2>
                </div>
                <div class="box-content">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="purchases" style="width:100%; height:450px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<!--        <div class="col-sm-6">
            <div class="box">
                <div class="box-header">
                    <h2 class="blue"><?= $m4; ?>
                    </h2>
                </div>
                <div class="box-content">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="m4bschart" style="width:100%; height:450px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>-->
    </div>
    </div>
</div>

