<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$v = "";
if ($this->input->post('type')) {
    $v .= "&type=" . $this->input->post('type');
}
if ($this->input->post('validity')) {
    $v .= "&validity=" . $this->input->post('validity');
}
//if ($this->input->post('warehouse')) {
//    $v .= "&warehouse=" . $this->input->post('warehouse');
//}
//if ($this->input->post('start_date')) {
//    $v .= "&start_date=" . $this->input->post('start_date');
//}
?>
<script>
    $(document).ready(function () {
        $('#PromData').dataTable({
            "aaSorting": [[3, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getPromotionReport') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                // $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
                URLtemp = sSource;
                arrayURL = URLtemp.split("/");
                for (var i = 0; i < arrayURL.length; i++) {
                    if (arrayURL[i] == 'getPromotionReport') {
                        arrayURL[i + 1] = $("#annually").val();
                        arrayURL[i + 2] = $("#monthly").val();
                    }
                }
                
                URLtemp = arrayURL.join().replace(/,/g, "/");
                $.ajax({
                    'dataType': 'json', 
                    'type': 'POST', 
                    'url': URLtemp, 
                    'data': aoData, 
                    'success': fnCallback
                });
                
                $('#annually').change(function () {
                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });
                    arrayURL = URLtemp.split("/");
                    for (var i = 0; i < arrayURL.length; i++) {
                        if (arrayURL[i] == 'getPromotionReport') {
                            arrayURL[i + 1] = $(this).val();
                        }
                    }
                    URLtemp = arrayURL.join().replace(/,/g, "/");
                    $.ajax({
                        "type": "POST",
                        "dataType": 'json',
                        "url": URLtemp, //sending server side status and filtering table
                        "data": aoData,
                        "success": function (data) {
                            fnCallback(data);
                        }
                    });
                });
                $('#monthly').change(function () {
                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });
                    arrayURL = URLtemp.split("/");
                    for (var i = 0; i < arrayURL.length; i++) {
                        if (arrayURL[i] == 'getPromotionReport') {
                            arrayURL[i + 2] = $(this).val();
                        }
                    }
                    URLtemp = arrayURL.join().replace(/,/g, "/");
                    $.ajax({
                        "type": "POST",
                        "dataType": 'json',
                        "url": URLtemp, //sending server side status and filtering table
                        "data": aoData,
                        "success": function (data) {
                            fnCallback(data);
                        }
                    });
                });
            },
            "aoColumns": [null, null,null,{"mRender": fld}]
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
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
        <h2 class="blue"><i class="fa-fw fa fa-gift"></i><?=$page_title;?>
        </h2>

        <div class="box-icon">
            <!-- <ul class="btn-tasks">
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
            </ul> -->
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="excel-all" data-action="export_all_excel">
                                <i class="fa fa-file-pdf-o"></i> <?= lang('export_to_excel_all') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="pdf" data-action="export_pdf">
                                <i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="pdf-all" data-action="export_all_pdf">
                                <i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf_all') ?>
                            </a>
                        </li>
                        <!--                        <li class="divider"></li>
                                                <li>
                                                    <a href="#" class="bpo"
                                                    title="<b><?= lang("delete_sales") ?></b>"
                                                    data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>"
                                                    data-html="true" data-placement="left">
                                                    <i class="fa fa-trash-o"></i> <?= lang('delete_sales') ?>
                                                </a>-->
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo $this->lang->line("list_results"); ?></p>
                <!-- <div id="filter-form"> -->
                <?= form_open('reports/promotionReportAction', 'id="action-form"') ?>
                    <div class="row">
                        <div class="col-lg-7">
                            <?php
                            $opts = array('1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April', '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus', '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember');
                            echo form_dropdown('monthly', $opts, (isset($_POST['monthly']) ? $_POST['monthly'] : date('m')), 'class="form-control" id="monthly" required="required"');
                            ?>
                        </div>
                        <div class="col-lg-5">
                            <?php
                            for ($y = date('Y'); $y >= 1990; $y--) {
                                $opts_year[$y] = $y;
                            }
                            echo form_dropdown('annually', $opts_year, (isset($_POST['annually']) ? $_POST['annually'] : date('Y')), 'class="form-control" id="annually" required="required"');
                            ?>
                        </div>
                    </div>
                   
                    

                <!-- </div> -->
                <div class="table-responsive">
                    <table id="PromData" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr>
                            <th><?php echo $this->lang->line("Promo Name"); ?></th>
                            <th><?php echo $this->lang->line("Toko"); ?></th>
                            <th><?php echo $this->lang->line("Distibutor"); ?></th>
                            <th><?php echo $this->lang->line("Date"); ?></th>
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
<div style="display: none;">
    <input type="hidden" name="form_action" value="" id="form_action"/>
    <?= form_submit('submit', 'submit', 'id="action-form-submit"') ?>
</div>
<?= form_close() ?>

<script type="text/javascript">
    $('body').on('click', '#pdf-all', function(e) {
        e.preventDefault();
        $('#form_action').val($(this).attr('data-action'));
        $('#action-form-submit').trigger('click');
    });

    $('body').on('click', '#excel-all', function(e) {
        e.preventDefault();
        $('#form_action').val($(this).attr('data-action'));
        $('#action-form-submit').trigger('click');
    });
</script>