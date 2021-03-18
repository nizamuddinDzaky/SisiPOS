<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$v = "";
if ($this->input->post('category')) {
    $v .= "&category=" . $this->input->post('category');
}
if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}
if ($this->input->post('company')) {
    $v .= "&company=" . $this->input->post('company');
}
if ($this->input->post('responden')) {
    $v .= "&responden=" . $this->input->post('responden');
}

?>
<script>
    $(document).ready(function() {
        function menu(x) {
            console.log(x);
            return '<div class="text-center"><i class="' + x + '"></i></div>';
        }

        $(document).on('click', '.btn-update-cms', function() {
            console.log($(this).data('id'));
        });
        var dataTable = $('#GData').dataTable({
            "aaSorting": [[1, "asc"]],
            buttons: [ 'copy', 'csv', 'excel', 'pdf', 'print' ],
            "aLengthMenu": [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('Reports/getCustomerResponse') ?>',
            'fnServerData': function(sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                url_ = sSource;
                arrurl = url_.split("/");
                url_ = arrurl.join().replace(/,/g, "/");
                url_ = url_+'<?= '?v=1'.$v?>';
                $.ajax({
                    'dataType': 'json',
                    'type': 'POST',
                    'url': url_,
                    'data': aoData,
                    'success': fnCallback
                });
            },
            "aoColumns": [{ "bVisible": false }, null, null, null, null, { "bSortable": false }]
        });

    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
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
        <h2 class="blue"><i class="fa fa-puzzle-piece"></i><?= $page_title ?>
            <?php if ($this->input->post('start_date')) {
                echo lang('from').($this->input->post('start_date')).lang('to').($this->input->post('end_date'));
            } ?>
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
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" id="pdf" class="tip" title="<?= lang('download_pdf') ?>">
                        <i class="icon fa fa-file-pdf-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                        <i class="icon fa fa-file-picture-o"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <?php echo form_open("reports/customer_response"); ?>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang("list_results"); ?></p>
                <div id="form">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="category"><?= lang("category"); ?></label>
                                <?php   
                                    $list[""] = lang('all_categories');
                                    foreach ($categories as $category) {
                                        $list[$category->id] = $category->category;
                                    }
                                    echo form_dropdown('category', $list, (isset($_POST['category']) ? $_POST['category'] : ""), 'class="form-control" id="category" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("category") . '"');
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
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="control-label" for="company"><?= lang("company"); ?></label>
                                <?php
                                    $co[""] = lang('all_companies');
                                    foreach ($companies as $company) {
                                        $co[$company->f_company_id] =  $company->company . " (" . $company->cf1 . ") ";
                                    }
                                    echo form_dropdown('company', $co, (isset($_POST['company']) ? $_POST['company'] : ""), 'class="form-control" id="company" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("company") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="control-label" for="responden"><?= lang("responden"); ?></label>
                                <?php
                                    $us[""] = lang('all_respondents');
                                    foreach ($respondens as $responden) {
                                        $us[$responden->id] =  $responden->username . " (" . $responden->first_name . " " . $responden->last_name . ") ";
                                    }
                                    echo form_dropdown('responden', $us, (isset($_POST['responden']) ? $_POST['responden'] : ""), 'class="form-control" id="responden" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("responden") . '"');
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary pull-right"'); ?> </div>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="table-responsive">
                    <table id="GData" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th><?php echo lang("id"); ?></th>
                                <th><?php echo lang("date"); ?></th>
                                <th><?php echo lang("category"); ?></th>
                                <th><?php echo lang("company"); ?></th>
                                <th><?php echo lang("responden"); ?></th>
                                <th><?php echo lang("action"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="4" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#pdf').click(function (event) {
            event.preventDefault();
            <?php if ($v == null): ?>
                window.location.href = "<?=site_url('reports/getSalesReport/')?>"+"/pdf/?v=1";
            <?php else: ?>
                window.location.href = "<?=site_url('reports/getSalesReport/')?>"+"/pdf/?v=1<?=$v?>";
            <?php endif; ?>
            return false;
            
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            <?php if ($v == null): ?>
                window.location.href = "<?=site_url('reports/getCustomerResponse/')?>"+"/0/pdf/?v=1";
            <?php else: ?>
                window.location.href = "<?=site_url('reports/getCustomerResponse/')?>"+"/0/pdf/?v=1<?=$v?>";
            <?php endif; ?>
            return false;
        });
        $('#start_date').change(function(){
            if($('#start_date').val()){
                $('#end_date').attr('required',true);
            }else{
                $('#end_date').attr('required',false);
            }
        });
    });
</script>