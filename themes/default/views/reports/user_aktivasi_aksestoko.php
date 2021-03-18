<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$v = "";
if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}
?>
<script>
    $(document).ready(function () {
        $('#SLData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/get_user_aktivasi_aksestoko/?v=1'.$v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                url_=sSource;
                arrurl=url_.split("/");
                url_=arrurl.join().replace(/,/g,"/");
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': url_, 'data': aoData, 'success': fnCallback});
            },

            "aoColumns": [{"mRender": fldd},null,null,null,null,null,null,null, null],
        });

    });

</script>

<?php echo form_open("reports/user_aktivasi_aksestoko"); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-shopping-cart"></i><?= $page_title ?> <?=(isset($_POST['start_date']) ? $_POST['start_date'] : $start_date)?> - <?=(isset($_POST['end_date']) && $_POST['end_date'] != "" ? $_POST['end_date'] : $end_date)?></h2>

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
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang("introtext_report_aktivasi_user_aksestoko"); ?></p>
                <?php echo form_open("reports/user_aktivasi_aksestoko"); ?>
                <div class="" id="form" style="margin-bottom:130px;">
                    <div class="col-sm-6" style="padding-left:inherit;">
                        <div class="form-group">
                            <?= lang("start_date", "start_date"); ?>
                            <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control date"'); ?>
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <div class="form-group">
                            <?= lang("end_date", "end_date"); ?>
                            <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control date"'); ?>
                        </div>
                    </div>
                    <div class="col-sm-1">
                        <div class="controls" style="text-align: right;  margin-top: 3rem"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                </div>
                <?php echo form_close(); ?>
                <div class="table-responsive">


                    <table id="SLData" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr class="active">
                                <th><?= lang("date") ?></th>
                                <th><?= lang("ibk"); ?></th>
                                <th><?= lang("nama_toko"); ?></th>
                                <th><?= lang("alamat"); ?></th>
                                <th><?= lang("phone"); ?></th>
                                <th><?= lang("distributor"); ?></th>
                                <th><?= lang("provinsi"); ?></th>
                                <th><?= lang("distributor"); ?></th>
                                <th><?= lang("registerd_by"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="9" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                            </tr>
                        </tbody>
                        
                    </table>
                </div>

            </div>

        </div>
    </div>
</div>
<script>
    $(document).ready(function(){
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
    $('#xls').click(function (event) {
        event.preventDefault();
        <?php if ($v == null): ?>
        window.location.href = "<?=site_url('reports/get_user_aktivasi_aksestoko/')?>"+"?form_action=export_excel";
        <?php else: ?>
        window.location.href = "<?=site_url('reports/get_user_aktivasi_aksestoko/')?>"+"?form_action=export_excel<?=$v?>";
        <?php endif; ?>
        return false;
    });
</script>
