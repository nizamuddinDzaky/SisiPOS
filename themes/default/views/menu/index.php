
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
        function menu(x) {
            console.log(x);
            return '<div class="text-center"><i class="'+x+'"></i></div>';
        }

        $(document).on('click','.btn-update-cms', function(){
            console.log($(this).data('id'));
        });
        var dataTable = $('#GData').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('menu_permissions/getMenu') ?>',
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
            "aoColumns": [null, null,{"mRender": menu, "bSortable": false}, {"bVisible": false}, {"bSortable": false}]
        });

    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa fa-credit-card"></i><?= $page_title ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">

                        <li>
                            <a title="<?=lang('add_module')?>" class="submenu" href="<?php echo site_url('menu_permissions/add_module'); ?>" data-toggle="modal" data-target="#myModal"  data-backdrop="static">
                                <i class="fa fa-plus-circle"></i><?=lang('add_module')?>
                            </a>
                        </li>
                        <li>
                            <a title="<?=lang('add_menu')?>" class="submenu" href="<?php echo site_url('menu_permissions/add_menu'); ?>" data-toggle="modal" data-target="#myModal"  data-backdrop="static">
                                <i class="fa fa-plus-circle"></i><?=lang('add_menu')?>
                            </a>
                        </li>
                    </ul>
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
                            <th><?= lang('module');?></th>
                            <th style="max-width:85px;"><?php echo lang("name"); ?></th>
                            <th style="max-width:85px;"><?php echo lang("icon"); ?></th>
                            <th style="max-width:85px;"><?php echo lang("action"); ?></th>
                            <th style="max-width:85px;"><?php echo lang("action"); ?></th>
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
