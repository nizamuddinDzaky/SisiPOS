<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript">
    $(document).ready(function() {

    });
</script>

<div class="modal-dialog modal-lg no-modal-header">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('detail_API') ?></h4>
        </div>
        <div class="modal-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <td><strong><?php echo $this->lang->line("uri"); ?></strong></td>
                            <td>
                                <p id="uri"><?= $api->uri; ?></p>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo $this->lang->line("token"); ?></strong></td>
                            <td>
                                <p id="token"><?= $api->token; ?></p>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo $this->lang->line("username"); ?></strong></td>
                            <td>
                                <p id="username"><?= $api->username; ?></p>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo $this->lang->line("password"); ?></strong></td>
                            <td>
                                <p id="password"><?= $api->password; ?></p>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo $this->lang->line("supplier_id"); ?></strong></td>
                            <td>
                                <p id="supplier_id"><?= $api->supplier_id; ?></p>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo $this->lang->line("type"); ?></strong></td>
                            <td>
                                <p id="type"><?= $api->type; ?></p>
                            </td>
                        </tr>
                        <?php if ($api->cf1) { ?>
                            <tr>
                                <td><strong><?php echo $this->lang->line("cf1"); ?></strong></td>
                                <td>
                                    <p id="cf1"><?= $api->cf1; ?></p>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if ($api->cf2) { ?>
                            <tr>
                                <td><strong><?php echo $this->lang->line("cf2"); ?></strong></td>
                                <td>
                                    <p id="cf2"><?= $api->cf2; ?></p>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if ($api->cf3) { ?>
                            <tr class="form-group">
                                <td><strong><?php echo $this->lang->line("cf3"); ?></strong></td>
                                <td>
                                    <p id="cf3"><?= $api->cf3; ?></p>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if ($api->cf4) { ?>
                            <tr>
                                <td><strong><?php echo $this->lang->line("cf4"); ?></strong></td>
                                <td>
                                    <p id="cf4"><?= $api->cf4; ?></p>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if ($api->cf5) { ?>
                            <tr>
                                <td><strong><?php echo $this->lang->line("cf5"); ?></strong></td>
                                <td>
                                    <p id="cf5"><?= $api->cf5; ?></p>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if ($api->cf6) { ?>
                            <tr>
                                <td><strong><?php echo $this->lang->line("cf6"); ?></strong></td>
                                <td>
                                    <p id="cf6"><?= $api->cf6; ?></p>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if ($api->cf7) { ?>
                            <tr>
                                <td><strong><?php echo $this->lang->line("cf7"); ?></strong></td>
                                <td>
                                    <p id="cf7"><?= $api->cf7; ?></p>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if ($api->cf8) { ?>
                            <tr>
                                <td><strong><?php echo $this->lang->line("cf8"); ?></strong></td>
                                <td>
                                    <p id="cf8"><?= $api->cf8; ?></p>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if ($api->cf9) { ?>
                            <tr>
                                <td><strong><?php echo $this->lang->line("cf9"); ?></strong></td>
                                <td>
                                    <p id="cf9"><?= $api->cf9; ?></p>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if ($api->cf10) { ?>
                            <tr>
                                <td><strong><?php echo $this->lang->line("cf10"); ?></strong></td>
                                <td>
                                    <p id="cf10"><?= $api->cf10; ?></p>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?= FORCAPOS_VERSION ?>"></script>
<?= $modal_js ?>