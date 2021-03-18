<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa fa-credit-card"></i><?= $page_title ?> ( <?= $group->name ?> )</h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo $this->lang->line("list_results"); ?></p>
                <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'bankForm');
                echo form_open_multipart("menu_permissions/group_permission/" . $group->id, $attrib); ?>
                <div class="table-responsive">
                    <table id="GData" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th style="max-width:85px;"><?php echo lang("module"); ?></th>
                                <th style="max-width:85px;"><?php echo lang("menu"); ?></th>
                                <th style="max-width:85px;"><?php echo lang("active"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $module = '';
                            foreach ($menus as $key => $menu) { ?>
                                <tr>
                                    <?php
                                    if ($module != $menu->module) {
                                        echo "<td rowspan='" . $rowspan[$menu->module] . "'>" . $menu->module . "</td>";
                                        $module = $menu->module;
                                    }
                                    ?>
                                    <td><?= $menu->name ?></td>
                                    <td class="text-center">
                                        <?php
                                        $checked = '';
                                        $keyPermission = array_search($menu->id, array_column($permission, 'menu_id'));
                                        if (gettype($keyPermission) != 'boolean') {
                                            if ($permission[$keyPermission]->is_active == 1) {
                                                $checked = 'checked';
                                            }
                                        }
                                        ?>
                                        <input type="checkbox" value="1" class="checkbox" name="active[<?= $menu->id ?>_<?= $menu->parent_id ?>]" <?= $checked ?> id="cb_<?= $menu->id ?>">
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <?php echo form_submit('tambah', lang('save'), 'class="btn btn-primary"'); ?>
                </div>
                <?php echo form_close(); ?>

            </div>

        </div>
    </div>
</div>