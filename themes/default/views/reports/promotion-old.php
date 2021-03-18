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
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getPromotionReport?v=1'.$v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [null, null, null, {"mRender": fld}, {"mRender": fld}]
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
            <ul class="btn-tasks">

            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo $this->lang->line("list_results"); ?></p>
                <div id="filter-form">
                    <?php echo form_open("reports/promotion"); ?>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <?= lang("type", "type") ?>
                                <?php
                                $types=array(0=>'all', 'bonus'=>lang('bonus'), 'multiple_discount'=>lang('discounts'), 'gross'=>lang('gross'));
                                echo form_dropdown('type', $types, (isset($_POST['type']) ? $_POST['type'] : ''), 'class="form-control select" id="type" placeholder="' . lang("select") . " " . lang("type") . '" style="width:100%"')
                                ?>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="control-label" for="validity"><?= lang("validity"); ?></label>
                                <?php
                                $valid=array(0=>'non-expired',1=>'expired');
                                echo form_dropdown('validity', $valid, (isset($_POST['validity']) ? $_POST['validity'] : ""), 'class="form-control" id="validity" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("validity") . '"');
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
                <div class="table-responsive">
                    <table id="PromData" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr>
                            <th><?php echo $this->lang->line("type_promo"); ?></th>
                            <th><?php echo $this->lang->line("product"); ?></th>
                            <th><?php echo $this->lang->line("warehouse"); ?></th>
                            <th><?php echo $this->lang->line("start_date"); ?></th>
                            <th><?php echo $this->lang->line("end_date"); ?></th>
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

