<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript">
    $(document).ready(function() {

    });
</script>

<div class="modal-dialog modal-md no-modal-header">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('detail_updates_notif') ?></h4>
        </div>
        <div class="modal-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <td><strong><?php echo $this->lang->line("id"); ?></strong></td>
                            <td>
                                <p id="id"><?= $updates_notif->id; ?><?= $this->session->userdata('last_update'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo $this->lang->line("type"); ?></strong></td>
                            <td>
                                <p id="type"><?= $updates_notif->type; ?></p>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo $this->lang->line("name"); ?></strong></td>
                            <td>
                                <p id="name"><?= $updates_notif->name; ?></p>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo $this->lang->line("version"); ?></strong></td>
                            <td>
                                <p id="version"><?= $updates_notif->version; ?></p>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo $this->lang->line("version_num"); ?></strong></td>
                            <td>
                                <p id="version_number"><?= $updates_notif->version_num; ?></p>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo $this->lang->line("release_date"); ?></strong></td>
                            <td>
                                <p id="release_at"><?= $updates_notif->release_at; ?></p>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo $this->lang->line("status"); ?></strong></td>
                            <td>
                                <p id="is_active">
                                    <?php if($updates_notif->is_active == 1){ ?>
                                        <span class="label label-success"><i class="fa fa-check"></i> <?= lang('active') ?></span>
                                    <?php }else{ ?>
                                        <span class="label label-danger"><i class="fa fa-times"></i> <?= lang('inactive') ?></span>
                                    <?php } ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo $this->lang->line("link"); ?></strong></td>
                            <td>
                                <p id="link"><?= $updates_notif->link; ?></p>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo $this->lang->line("description"); ?></strong></td>
                            <td>
                                <p id="description"><?= $updates_notif->desc; ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?= FORCAPOS_VERSION ?>"></script>
<?= $modal_js ?>