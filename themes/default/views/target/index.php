<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$y=date('Y');
$m=date('m');
$thn=$_POST['year']? $_POST['year'] : null;
$bln=$_POST['month']? $_POST['month'] : null;
$prod=$_POST['product']? $_POST['product'] : null;?>
<script>
    $(document).ready(function () {
        var oTable = $('#TargetTable').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'serverSide': true,
            'sAjaxSource': '<?=site_url('target/getTarget/'. ($thn ? '/' . $thn : $y) .'/'. ($bln ? '/' . $bln : $m) .'/'.$prod )?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?=$this->security->get_csrf_token_name()?>",
                    "value": "<?=$this->security->get_csrf_hash()?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
/*                $("#output").print(aData[2]);*/
                var oSettings = oTable.fnSettings();
                //$("td:first", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                nRow.id = aData[0];
/*                nRow.setAttribute('data-return-id', aData[11]);
                nRow.className = "invoice_link re"+aData[11];*/

                //if(aData[7] > aData[9]){ nRow.className = "product_link warning"; } else { nRow.className = "product_link"; }
                return nRow;
            },
            "aoColumns": [{"bSortable": false,"mRender":function ( data, type, full ) {
                return '<b>Jumlah</b>';
            }},null,null,null,null,null],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
               
            }
        });
	
	});
</script>


<div class="box">
<div id="output" name="output"></div>
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-info-circle"></i>Target</h2> 
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('list_results'); ?></p>
 				<?php
                    $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                    echo form_open("target/", $attrib)
                ?>
				<div class="col-sm-4">
					<div class="form-group">
						<?= lang("product_type", "type") ?>
                        <!-- Produk -->

                        <?php
						foreach($allproduk as $a){
                            if(($a->id==1) || ($a->id==6) || ($a->id==7)){
                                $opts[]=$a->name;
                            }
						}
                        echo form_dropdown('product', $opts, (isset($_POST['product']) ? $_POST['product'] : ($allproduk ? $allproduk->name : '')), 'class="form-control" id="type" required="required"');
                        ?>
					</div>
				</div>

				<div class="col-sm-4">
					<div class="form-group">
						<?= lang("product_type", "type") ?>
<!--                         Bulan -->
                        <?php
                        $opts = array('1' => 'Januari', '2'=>'Februari', '3'=>'Maret', '4'=>'April', '5'=>'Mei', '6'=>'Juni', '7'=>'Juli','8'=>'Agustus','9'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember');
                        echo form_dropdown('month', $opts, (isset($_POST['month']) ? $_POST['month'] : date('m')), 'class="form-control" id="type" required="required"');
                        ?>
					</div>
				</div>
				
				<div class="col-sm-4">
					<div class="form-group">
                        <?= lang("product_type", "type") ?>
                        <!-- Tahun -->
                        <?php
                        $opts = array('2005'=>'2005','2006'=>'2006','2007'=>'2007','2008'=>'2008','2009'=>'2009','2010'=>'2010', '2011'=>'2011', '2012'=>'2012', '2013'=>'2013', '2014'=>'2014', '2015'=>'2015', '2016'=>'2016', '2017'=>'2017', '2018'=>'2018', '2019'=>'2019', '2020'=>'2020', '2021'=>'2021');
                        echo form_dropdown('year', $opts, (isset($_POST['year']) ? $_POST['year'] : date('Y')), 'class="form-control" id="type" required="required"');
                        ?>
					</div>
				</div>

                <div class="col-md-3">
                    <div class="form-group">
                            <?php echo form_submit('show_target', 'Show', 'class="btn btn-primary"'); ?>
                    </div>
                </div>

                 <?php
                    form_close();
                ?>
                <div class="table-responsive">
                    <table id="TargetTable" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr>
							<th>       </th>
							<th>Minggu1</th>
                            <th>Minggu2</th>
                            <th>Minggu3</th>
                            <th>Minggu4</th>
                            <th>Minggu5</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="5" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

