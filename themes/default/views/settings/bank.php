<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>

    $(document).ready(function () {
        $('#GData').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('system_settings/get_bank') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                url_=sSource;
                
                arrurl=url_.split("/");
                url_=arrurl.join().replace(/,/g,"/");
                $.ajax({
                    'dataType': 'json',
                    'type': 'POST', 
                    'url': url_, 
                    'data': aoData, 
                    'success': fnCallback
                });
            },
            
            "aoColumns": [{"bSortable": false, "mRender": checkbox},{"bSortable": false,"mRender": img_hl},null, null, null,null,{"mRender": user_status},{"bSortable": false}]
        });
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa fa-credit-card"></i><?= $page_title ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a title="Add Bank" class="tip" href="<?php echo site_url('system_settings/add_bank'); ?>" data-toggle="modal" data-target="#myModal"  data-backdrop="static">
                    <i class="icon fa fa-plus"></i>
                    </a>
                </li>
                <?php echo anchor($mb_bank, '<i class="icon fa fa-book tip" data-placement="left" title="'.lang("manual_book").'"></i> ', 'target="_blank"') ?>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo $this->lang->line("list_results"); ?></p>
                <div class="table-responsive">
                    <table id="GData" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang('logo'); ?></th>
                            <th><?= lang('code');?></th>
                            <th><?= lang('bank_name');?></th>
                            <th><?= lang('account_number');?></th>
                            <th><?= lang('name_of');?></th>
                            <th><?= lang('status');?></th>
                            <th style="max-width:85px;"><?php echo $this->lang->line("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>

                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>
</div>
