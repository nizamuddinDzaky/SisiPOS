<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style type="text/css" media="screen">
    #PRData td:nth-child(7) {
        text-align: right;
    }
    <?php if($Owner || $Admin || $this->session->userdata('show_cost')) { ?>
    #PRData td:nth-child(9) {
        text-align: right;
    }
    <?php } if($Owner || $Admin || $this->session->userdata('show_price')) { ?>
    #PRData td:nth-child(8) {
        text-align: right;
    }
    <?php } ?>
</style>
<?php
?>
<script>
    var oTable; var url_;
    $(document).ready(function () {
        oTable = $('#PRData').dataTable({
            "aaSorting": [[2, "asc"], [3, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('products/getProducts/no'.($warehouse_id ? '/'.$warehouse_id : '')) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });

                url_=sSource;
                
                arrurl=url_.split("/");
                for(var i=0;i<arrurl.length;i++){
                    if(arrurl[i]=='getProducts'){
                        arrurl[i+1]=$('#consign').val();
                    }
                }
                url_=arrurl.join().replace(/,/g,"/");
                $.ajax({
                    'dataType': 'json', 
                    'type': 'POST', 
                    'url': url_, 
                    'data': aoData, 
                    'success': fnCallback
                });
                
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.className = "product_link";
                //if(aData[7] > aData[9]){ nRow.className = "product_link warning"; } else { nRow.className = "product_link"; }
                return nRow;
            },
            "aoColumns": [
                {"bSortable": false, "mRender": checkbox}, {"bSortable": false,"mRender": img_product}, null, null, null, null, <?php $uri2=$this->uri->segment(2); if($Owner || $Admin) { echo '{"mRender": currencyFormat},'; if(!$uri2||$uri2=='index'){echo'{"bVisible": false},';}else{echo'{"mRender": currencyFormat},';} echo '{"mRender": currencyFormat},'; } else { if($this->session->userdata('show_cost')) { echo '{"mRender": currencyFormat},';  } if(!$uri2||$uri2=='index'){echo'{"bVisible": false},';}else{echo'{"mRender": currencyFormat},';} if($this->session->userdata('show_price')) { echo '{"mRender": currencyFormat},';  } } ?> {"mRender": formatQuantity}, null, <?php if(!$warehouse_id || !$Settings->racks) { echo '{"bVisible": false},'; } else { echo '{"bSortable": true},'; } ?> {"mRender": formatQuantity}, {"mRender": formatQuantity}, {"bVisible": false},{"bSortable": false}
            ]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 2, filter_default_label: "[<?=lang('code');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('brand');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('category');?>]", filter_type: "text", data: []},
            <?php $col = 5;
            if($Owner || $Admin) {
                echo '{column_number : 6, filter_default_label: "['.lang('cost').']", filter_type: "text", data: [] },';
                echo '{column_number : 7, filter_default_label: "[Avg_cost]", filter_type: "text", data: [] },';
                echo '{column_number : 8, filter_default_label: "['.lang('price').']", filter_type: "text", data: [] },';
                $col += 3;
            } else {
                if($this->session->userdata('show_cost')) { $col++; echo '{column_number : '.$col.', filter_default_label: "['.lang('cost').']", filter_type: "text", data: [] },'; }
                echo '{column_number : '.$col.', filter_default_label: "[Avg_cost]", filter_type: "text", data: [] },';
                if($this->session->userdata('show_price')) { $col++; echo '{column_number : '.$col.', filter_default_label: "['.lang('price').']", filter_type: "text, data: []" },'; }
            }
            ?>
            {column_number: <?php $col++; echo $col; ?>, filter_default_label: "[<?=lang('quantity');?>]", filter_type: "text", data: []},
            {column_number: <?php $col++; echo $col; ?>, filter_default_label: "[<?=lang('unit');?>]", filter_type: "text", data: []},
            <?php $col++; if($warehouse_id && $Settings->racks) { echo '{column_number : '. $col.', filter_default_label: "['.lang('rack').']", filter_type: "text", data: [] },'; } ?>
            {column_number: <?php $col++; echo $col; ?>, filter_default_label: "[<?=lang('alert_quantity');?>]",  filter_type: "text", data: []},
            {column_number: <?php $col++; echo $col; ?>, filter_default_label: "[<?=lang('quantity_booking');?>]",  filter_type: "text", data: []},
        ], "footer");

    });
</script>
<?php if ($Owner  || $Admin || $GP['bulk_actions']) {
    echo form_open('products/product_actions'.($warehouse_id ? '/'.$warehouse_id : '/'.$this->session->userdata('warehouse_id')), 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-barcode"></i><?= lang('products') . ' (' . ($warehouse_id ? $warehouse->name : lang('all_warehouses')) . ')'; ?>
        </h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?= site_url('products/add') ?>">
                                <i class="fa fa-plus-circle"></i> <?= lang('add_product') ?>
                            </a>
                        </li>
                        <?php if(!$warehouse_id) { ?>
                        <li>
                            <a href="<?= site_url('products/update_price') ?>" data-toggle="modal" data-target="#myModal"  data-backdrop="static">
                                <i class="fa fa-file-excel-o"></i> <?= lang('update_price') ?>
                            </a>
                        </li>
                        <?php } ?>
                        <li>
                            <a href="#" id="labelProducts" data-action="labels">
                                <i class="fa fa-print"></i> <?= lang('print_barcode_label') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="sync_quantity" data-action="sync_quantity">
                                <i class="fa fa-arrows-v"></i> <?= lang('sync_quantity') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="sync_quantity_booking" data-action="sync_quantity_booking">
                                <i class="fa fa-arrows-v"></i> <?= lang('sync_quantity_booking') ?>
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
                        <!-- Tombol delete sementara disembunyikan
                        <li class="divider"></li>
                        <li>
                            <a href="#" class="bpo" title="<b><?= $this->lang->line("delete_products") ?></b>"
                                data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>"
                                data-html="true" data-placement="left">
                            <i class="fa fa-trash-o"></i> <?= lang('delete_products') ?>
                             </a>
                         </li> -->
                    </ul>
                </li>
                <?php if (!empty($warehouses)) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= site_url('products') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($warehouses as $warehouse) {
                                echo '<li><a href="' . site_url('products/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                <?php } ?>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-book tip" data-placement="left" title="<?= lang("manual_book") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= $mb_product ?>" target="_blank"><i class="fa fa-book tip"></i> <?= lang('product') ?></a></li>
                        <li><a href="<?= $mb_edit_product ?>" target="_blank"><i class="fa fa-book tip"></i> <?= lang('edit_product') ?></a></li>
                        <li><a href="<?= $mb_import_csv_product ?>" target="_blank"><i class="fa fa-book tip"></i> <?= lang('import_csv_product') ?></a></li>
                        <li><a href="<?= $mb_export_excel_product ?>" target="_blank"><i class="fa fa-book tip"></i> <?= lang('export_excel_product') ?></a></li>
                        <li><a href="<?= $mb_export_pdf_product ?>" target="_blank"><i class="fa fa-book tip"></i> <?= lang('export_pdf_product') ?></a></li>
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
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo form_open("".$warehouse_id, $attrib);
                ?>
                <div class="form-group">
                    <div class="col-lg-12" style="padding-left:inherit; padding-right:inherit; margin-bottom:10px;">
                    <?php
                    $opts = array('no' => lang('no-consignment'), 'yes' => lang('consignment'), 'all' => lang('all'));
                    echo form_dropdown('consign', $opts, (isset($_POST['consign']) ? $_POST['consign'] : false), 'class="form-control" id="consign" required="required"');?>
                    </div>
                </div>
                <?php
                form_close();
                ?>
                <div class="table-responsive">
                    <table id="PRData" class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr class="primary">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
                            <th style="min-width:40px; width: 40px; text-align: center;"><?php echo $this->lang->line("image"); ?></th>
                            <th><?= lang("code") ?></th>
                            <th><?= lang("name") ?></th>
                            <th><?= lang("brand") ?></th>
                            <th><?= lang("category") ?></th>
                            <?php
                            if ($Owner || $Admin) {
                                echo '<th>' . lang("cost") . '</th>';
                                echo '<th>Avg_cost</th>';
                                echo '<th>' . lang("selling_price") . '</th>';
                            } else {
                                if ($this->session->userdata('show_cost')) {
                                    echo '<th>' . lang("cost") . '</th>';
                                }
                                echo '<th>Avg_cost</th>';
                                if ($this->session->userdata('show_price')) {
                                    echo '<th>' . lang("selling_price") . '</th>';
                                }
                            }
                            ?>
                            <th><?= lang("quantity") ?></th>
                            <th><?= lang("unit") ?></th>
                            <th><?= lang("rack") ?></th>
                            <th><?= lang("alert_quantity") ?></th>
                            <th><?= lang("quantity_booking") ?></th>
                            <th style="min-width:65px; text-align:center;"><?= lang("actions") ?></th>
                            <th style="min-width:65px; text-align:center;"><?= lang("actions") ?></th>
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
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th style="min-width:40px; width: 40px; text-align: center;"><?php echo $this->lang->line("image"); ?></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <?php
                            if ($Owner || $Admin) {
                                echo '<th></th>';
                                echo '<th></th>';
                            } else {
                                if ($this->session->userdata('show_cost')) {
                                    echo '<th></th>';
                                }
                                if ($this->session->userdata('show_price')) {
                                    echo '<th></th>';
                                }
                            }
                            ?>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th style="width:65px; text-align:center;"><?= lang("actions") ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner  || $Admin || $GP['bulk_actions']) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>
