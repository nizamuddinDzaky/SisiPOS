<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
    .table td:first-child {
        font-weight: bold;
    }

    label {
        margin-right: 10px;
    }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-folder-open"></i><?= lang('edit_plans'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('update_info'); ?></p>

                <?php if (!empty($plan)) {
                    echo form_open("system_settings/edit_plans/" . $id); ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped">

                            <thead>
                                <tr>
                                    <th colspan="6" class="text-center"><?php echo $plan->name .' '. $this->lang->line("feature"); ?></th>
                                </tr>
                                <tr>
                                    <th class="text-center"><?= lang("module_name"); ?></th>
                                    <th class="text-center"><?= lang("privilege"); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center"><?= lang("master_data"); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="master_data" <?php echo $plan->master ? "checked" : ''; ?>>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="text-center"><?= lang("pos_sales"); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="pos" <?php echo $plan->pos ? "checked" : ''; ?>>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="text-center"><?= lang("purchases"); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="purchases" <?php echo $plan->purchases ? "checked" : ''; ?>>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="text-center"><?= lang("sales"); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales" <?php echo $plan->sales ? "checked" : ''; ?>>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="text-center"><?= lang("quotes"); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="quotes" <?php echo $plan->quotes ? "checked" : ''; ?>>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="text-center"><?= lang("expenses"); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="expenses" <?php echo $plan->expenses ? "checked" : ''; ?>>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="text-center"><?= lang("reports"); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="reports" <?php echo $plan->reports ? "checked" : ''; ?>>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="text-center"><?= lang("transfers"); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="transfers" <?php echo $plan->transfers ? "checked" : ''; ?>>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center"><?= lang("limitation"); ?></td>
                                    <td class="text-center">
                                        <input type="text" class="form-control number-only" name="limitation" value="<?php echo $plan->limitation ? $plan->limitation : NULL; ?>" >
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center"><?= lang("user"); ?></td>
                                    <td class="text-center">
                                        <input type="text" class="form-control number-only" name="users" value="<?php echo $plan->users ? $plan->users : NULL; ?>" >
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center"><?= lang("warehouse"); ?></td>
                                    <td class="text-center">
                                        <input type="text" class="form-control number-only" name="warehouses" value="<?php echo $plan->warehouses ? $plan->warehouses : NULL; ?>" >
                                    </td>
                                </tr>
                                <tr>
                                    <td><?= lang("price"); ?></td>
                                    <td class="text-center">
                                        <input type="text" class="form-control number-only" name="price" value="<?php echo $plan->price ? $this->sma->formatMoney($plan->price) : NULL; ?>" >
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><?=lang('update')?></button>
                    </div>
                    <?php echo form_close();
                } else {
                    echo $this->lang->line("group_x_allowed");
                } ?>


            </div>
        </div>
    </div>
</div>