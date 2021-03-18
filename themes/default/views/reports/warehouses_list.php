<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        $('#StCaRData').dataTable({
            "aaSorting": [[0, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getWarehousesList?v=1') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [null, null, null, null, null, null]
        });
    });
    
</script>

<div class="box">
    <div class="box-header">
        <h2 id="title_warehouse" class="blue"><i class="fa-fw fa fa-gift"></i><?= $page_title ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
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
            </ul>
        </div>
    </div>
    <?php echo form_open("reports/warehouses_list"); ?>
        <div class="box-content">
            <div class="row">
                <div class="col-lg-12">
                    <div class="table-responsive">
                        <table id="StCaRData" class="table table-bordered table-hover table-striped">
                            <thead>
                            <tr>
                                <th><?php echo $this->lang->line("warehouse_code"); ?></th>
                                <th><?php echo $this->lang->line("warehouse_name"); ?></th>
                                <th><?php echo $this->lang->line("warehouse_address"); ?></th>
                                <th><?php echo $this->lang->line("distributor_code"); ?></th>
                                <th><?php echo $this->lang->line("distributor_name"); ?></th>
                                <th><?php echo $this->lang->line("distributor_province"); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td colspan="6" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
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
    $('#pdf').click(function (event) {
       event.preventDefault();

        var a = "<?=site_url('reports/getExportWarehousesList/pdf') ?>?v=1";
        window.location.href = a;
        console.log(a);
        return false;   
    });

    $('#xls').click(function (event) {
       event.preventDefault();

        var a = "<?=site_url('reports/getExportWarehousesList/xls') ?>?v=1";
        window.location.href = a;
        console.log(a);
        return false;   
    });
});
</script>