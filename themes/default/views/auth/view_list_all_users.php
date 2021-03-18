<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang("detail_user"); ?></h4>
        </div>
        <div class="modal-body">
            <div style="max-width:100px; margin:auto; margin-bottom:3%;">
                <img alt="avatar" src=" <?= avatar_image($users->avatar, $users->gender ?? 'male') ?>" class="avatar">
            </div>
            <table class="table table-striped table-bordered" style="margin-bottom:0;">
                <tbody>
                    <tr>
                        <td colspan="2"><strong><?= lang("general"); ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("last_ip_address"); ?></strong></td>
                        <td><?= $users->last_ip_address ?? '-'; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("username"); ?></strong></td>
                        <td><?= $users->username ?? '-'; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("email_address"); ?></strong></td>
                        <td><?= $users->email ?? '-'; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("created_on"); ?></strong></td>
                        <td><?= $users->created_on ? date("d/m/Y H:i:s", $users->created_on) : '-'; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("last_login"); ?></strong></td>
                        <td><?= $users->last_login ? date("d/m/Y H:i:s", $users->last_login) : '-'; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("status"); ?></strong></td>
                        <td>
                            <?php if ($users->active == 1) { ?>
                                <span class="label label-success"><i class="fa fa-check"></i> <?= lang('active') ?></span>
                            <?php } else { ?>
                                <span class="label label-danger"><i class="fa fa-times"></i> <?= lang('inactive') ?></span>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("first_name"); ?></strong></td>
                        <td><?= $users->first_name ? strtoupper($users->first_name) : '-'; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("last_name"); ?></strong></td>
                        <td><?= $users->last_name ? strtoupper($users->last_name) : '-'; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("gender"); ?></strong></td>
                        <td><?= $users->gender ? strtoupper($users->gender) : '-'; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("group"); ?></strong></td>
                        <td><?= $users->group ? strtoupper($users->group) : '-'; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("phone"); ?></strong></td>
                        <td><?= $users->phone ?? '-'; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("company"); ?></strong></td>
                        <td><?= $users->company ?? '-'; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("warehouses"); ?></strong></td>
                        <td><?= $users->warehouses ?? '-'; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("province"); ?></strong></td>
                        <td><?= $users->country ?? '-'; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("city"); ?></strong></td>
                        <td><?= $users->city ?? '-'; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("state"); ?></strong></td>
                        <td><?= $users->state ?? '-'; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("address"); ?></strong></td>
                        <td><?= $users->address ?? '-'; ?></strong></td>
                    </tr>
                </tbody>
            </table>

            <?php if ($users->client_id == 'aksestoko') { ?>
                <table class="table table-striped table-bordered" style="margin-bottom:0; margin-top:5%;">
                    <tbody>
                        <tr>
                            <td colspan="2"><strong><?= lang("akses_toko_detail"); ?></strong></td>
                        </tr>
                        <tr>
                            <td><strong><?= lang("sales_person"); ?></strong></td>
                            <td><?= $sales_person->name ?? '<center> - </center>'; ?></strong></td>
                        </tr>
                        <tr>
                            <td><strong><?= lang("phone_is_verified"); ?></strong></td>
                            <td>
                                <?php if ($users->phone_is_verified == 1) { ?>
                                    <center><span class="label label-success"><i class="fa fa-check"></i> <?= lang('verified') ?></span></center>
                                <?php } else { ?>
                                    <center><span class="label label-danger"><i class="fa fa-times"></i> <?= lang('not_verified') ?></span></center>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?= lang("phone_otp"); ?></strong></td>
                            <td><?= $users->phone_otp ?? '<center> - </center>'; ?></strong></td>
                        </tr>
                        <tr>
                            <td><strong><?= lang("activated_at"); ?></strong></td>
                            <td><?= $users->activated_at ?? '<center> - </center>'; ?></strong></td>
                        </tr>
                        <tr>
                            <td><strong><?= lang("registered_by"); ?></strong></td>
                            <td><?= $users->registered_by ? strtoupper($users->registered_by) : '<center> - </center>'; ?></strong></td>
                        </tr>
                        <tr>
                            <td><strong><?= lang("last_sent_activation_code_at"); ?></strong></td>
                            <td><?= $users->last_sent_activation_code_at ?? '<center> - </center>'; ?></strong></td>
                        </tr>
                        <tr>
                            <td><strong><?= lang("recovery_code"); ?></strong></td>
                            <td><?= $users->recovery_code ?? '<center> - </center>'; ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            <?php } ?>

        </div>
        <div class="modal-footer no-print">
            <button type="button" class="btn btn-default pull-right" data-dismiss="modal"><?= lang('close'); ?></button>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
</div>