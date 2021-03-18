
<!-- <?php

/* 
 * Copyright (c) 2017 adminSISI.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    adminSISI - initial API and implementation and/or initial documentation
 */
?>
  <-- Nav tabs -->

<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        function is_active(x) {
            var x = x.split('__');
            if (x[1] == '' || x[1] == null || x[1] == 0) {
                return '<div class="text-center"><span class="label label-danger">inactive</span></div>';
            }else{
                return '<div class="text-center"><span class="label label-success">active</span></div>';
            }
        }

        $(document).on('click','.btn-update-cms', function(){
            console.log($(this).data('id'));
        });
        var dataTable = $('#GData').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('system_settings/get_category_faq') ?>',
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
            "aoColumns": [{"bSortable": false, "mRender": checkbox}, null,{"bSortable": false, "mRender": is_active}, {"bSortable": false}]
        });

    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa fa-credit-card"></i><?= $page_title ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a title="Add Category" class="tip" href="<?php echo site_url('system_settings/add_category_faq'); ?>" data-toggle="modal" data-target="#myModal"  data-backdrop="static">
                    <i class="icon fa fa-plus"></i>
                    </a>
                </li>
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
                                <th>
                                    <?= lang('Title');?>
                                </th>
                                <th style="max-width:85px;">
                                    <?php echo $this->lang->line("Status"); ?>
                                </th>
                                <th style="max-width:85px;">
                                    <?php echo $this->lang->line("actions"); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="2" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>
</div>
