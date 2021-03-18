<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    // $(document).ready(function () {
        // var oTable = $('#TargetTable').dataTable({
            // "aaSorting": [[1, "asc"], [2, "asc"]],
            // "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            // "iDisplayLength": <?= $Settings->rows_per_page ?>,
            // 'bProcessing': true, 'bServerSide': true,
            // 'sAjaxSource': '<?= site_url('tampiltarget/getTarget') ?>',
            // 'fnServerData': function (sSource, aoData, fnCallback) {
                // aoData.push({
                    // "name": "<?= $this->security->get_csrf_token_name() ?>",
                    // "value": "<?= $this->security->get_csrf_hash() ?>"
                // });
                // $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            // },
            // "aoColumns": [null, {"mRender": fld}, {"mRender": fld}, {"mRender": fld}, {"bSortable": false}]
        // });
    // });
    $(document).ready(function () {
        var oTable = $('#TargetTable').dataTable({
            "aaSorting": [[0, "asc"], [1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?=site_url('tampiltarget/getTarget' . ($warehouse_id ? '/' . $warehouse_id : ''))?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?=$this->security->get_csrf_token_name()?>",
                    "value": "<?=$this->security->get_csrf_hash()?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                //$("td:first", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                nRow.id = aData[0];
                nRow.setAttribute('data-return-id', aData[11]);
                nRow.className = "invoice_link re"+aData[11];
                //if(aData[7] > aData[9]){ nRow.className = "product_link warning"; } else { nRow.className = "product_link"; }
                return nRow;
            },
            "aoColumns": [null,null],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
               
            }
	});
	
	});
</script>

<div class="box">
    <div class="box-header">
<!--        <h2 class="blue"><i class="fa-fw fa fa-info-circle"></i><?= lang('notifications'); ?></h2>-->
        <h2 class="blue"><i class="fa-fw fa fa-info-circle"></i>Tampil Target</h2>

    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('list_results'); ?></p>
				
				<div class="col-md-4">
					<div class="form-group">
						<?= lang("product_type", "type") ?>
                        <?php
						$opts=$allproduk;
						// foreach($allproduk as $a){
							// echo $a;
						// }
                        // $opts = array('2010'=>'2010', '2011'=>'2011', '2012'=>'2012', '2013'=>'2013', '2014'=>'2014', '2015'=>'2015', '2016'=>'2016', '2017'=>'2017');
                        echo form_dropdown('type', $opts, (isset($_POST['type']) ? $_POST['type'] : ($product ? $product->type : '')), 'class="form-control" id="type" required="required"');
                        ?>
					</div>
				</div>

				<div class="col-md-4">
					<div class="form-group">
						<?= lang("product_type", "type") ?>
                        <?php
                        $opts = array('januari' => 'Januari', 'februari'=>'Februari', 'maret'=>'Maret', 'april'=>'April', 'mei'=>'Mei', 'juni'=>'Juni', 'juli'=>'Juli','agustus'=>'Agustus','september'=>'September','oktober'=>'Oktober');
                        echo form_dropdown('type', $opts, (isset($_POST['type']) ? $_POST['type'] : ($product ? $product->type : '')), 'class="form-control" id="type" required="required"');
                        ?>
					</div>
				</div>
				
				<div class="col-md-4">
					<div class="form-group">
						<?= lang("product_type", "type") ?>
                        <?php
                        $opts = array('2010'=>'2010', '2011'=>'2011', '2012'=>'2012', '2013'=>'2013', '2014'=>'2014', '2015'=>'2015', '2016'=>'2016', '2017'=>'2017');
                        echo form_dropdown('type', $opts, (isset($_POST['type']) ? $_POST['type'] : ($product ? $product->type : '')), 'class="form-control" id="type" required="required"');
                        ?>
					</div>
				</div>

                <div class="table-responsive">
                    <table id="TargetTable" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr>
 <!--                           <th><?php echo $this->lang->line("notification"); ?></th>
                            <th style="width: 140px;"><?php echo $this->lang->line("submitted_at"); ?></th>
                            <th style="width: 140px;"><?php echo $this->lang->line("from"); ?></th>
                            <th style="width: 140px;"><?php echo $this->lang->line("till"); ?></th>
                            <th style="width:80px;"><?php echo $this->lang->line("actions"); ?></th>-->
							<th>Year</th>
							<th>Product Code</th>
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

