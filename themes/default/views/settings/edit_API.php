<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" tabindex="-1" role="dialog" id="statusmodal">
    <div class="modal-dialog" role="document" style="width: 20%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" aria-label="Close" id="close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <center>
                    <p><?= lang('Max_Reached') ?></p>
                </center>
            </div>
            <div class="modal-footer">
                <center>
                    <button type="button" class="btn btn-primary" id="ok">Ok</button>
                </center>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script type="text/javascript">
    var cfield = 1;
    for (var i = 1; i <= 10; i++) {
        $('#row_cf' + i).slideUp();
    }

    function add_cf(row) {
        row.closest('.input-group').classList.add('controls');
        row.closest('.input-group').classList.remove('input-group');
        row.remove();
        if (cfield <= 9) {
            cfield++;
            $('#row_cf' + cfield).slideDown();
        } else {
            $('.tip').tooltip();
            $("#statusmodal").show();
            $('#statusmodal').modal({
                backdrop: false
            });
            $("#ok").click(function() {
                $("#statusmodal").hide();
            });
            $("#close").click(function() {
                $("#statusmodal").hide();
            });
        }
    }
    $(document).ready(function() {
        <?php
        if ($api->cf1) { ?>
            add_cf($(".cf1")[0]);
        <?php }
        if ($api->cf2) { ?>
            add_cf($(".cf2")[0]);
        <?php }
        if ($api->cf3) { ?>
            add_cf($(".cf3")[0]);
        <?php }
        if ($api->cf4) { ?>
            add_cf($(".cf4")[0]);
        <?php }
        if ($api->cf5) { ?>
            add_cf($(".cf5")[0]);
        <?php }
        if ($api->cf6) { ?>
            add_cf($(".cf6")[0]);
        <?php }
        if ($api->cf7) { ?>
            add_cf($(".cf7")[0]);
        <?php }
        if ($api->cf8) { ?>
            add_cf($(".cf8")[0]);
        <?php }
        if ($api->cf9) { ?>
            add_cf($(".cf9")[0]);
        <?php }
        if ($api->cf10) { ?>
            add_cf($(".cf10")[0]);
        <?php } ?>
        $('#row_cf1').slideDown();
    });
</script>

<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('edit_API') ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'APIForm');
        echo form_open_multipart("system_settings/edit_API/" . $api->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
                <label class="control-label" for="uri"><?php echo $this->lang->line("uri"); ?></label>
                <?= form_input('uri', $api->uri, 'class="form-control" id="uri" required="required"'); ?>
                <input name="code" id="uri" type="hidden" />
            </div>

            <div class="form-group">
                <label class="control-label" for="token"><?php echo $this->lang->line("token"); ?></label>
                <?= form_input('token', $api->token, 'class="form-control" id="token" required="required"'); ?>
                <input name="code" id="token" type="hidden" />
            </div>

            <div class="">
                <div class="form-group">
                    <label class="control-label" for="username"><?php echo $this->lang->line("username"); ?></label>
                    <?= form_input('username', $api->username, 'class="form-control" id="username" required="required"'); ?>
                    <input name="code" id="username" type="hidden" />
                </div>
                <div class="form-group">
                    <label class="control-label" for="password"><?php echo $this->lang->line("password"); ?></label>
                    <?= form_input('password', $api->password, 'class="form-control" id="password" required="required"'); ?>
                    <input name="code" id="password" type="hidden" />
                </div>
            </div>

            <div class="">
                <div class="form-group">
                    <label class="control-label" for="supplier_id"><?php echo $this->lang->line("supplier_id"); ?></label>
                    <?= form_input('supplier_id', $api->supplier_id, 'class="form-control" id="supplier_id" required="required"'); ?>
                    <input name="code" id="supplier_id" type="hidden" />
                </div>

                <div class="form-group">
                    <label class="control-label" for="type"><?php echo $this->lang->line("type"); ?></label>
                    <?= form_input('type', $api->type, 'class="form-control" id="type" required="required"'); ?>
                    <input name="code" id="type" type="hidden" />
                </div>
            </div>

            <div class="">
                <div class="" id="row_cf1">
                    <div class="form-group">
                        <label class="control-label" for="cf1"><?php echo $this->lang->line("cf1"); ?></label>
                        <div class="input-group">
                            <?= form_input('cf1', $api->cf1, 'class="form-control" id="cf1"'); ?>
                            <span class="input-group-addon pointer cf1" onclick="add_cf(this)" style="padding: 1px 10px;">
                                <i class="fa fa-plus-square"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="" id="row_cf2">
                    <div class="form-group">
                        <label class="control-label" for="cf2"><?php echo $this->lang->line("cf2"); ?></label>
                        <div class="input-group">
                            <?= form_input('cf2', $api->cf2, 'class="form-control" id="cf2"'); ?>
                            <span class="input-group-addon pointer cf2" onclick="add_cf(this)" style="padding: 1px 10px;">
                                <i class="fa fa-plus-square"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="" id="row_cf3">
                    <div class="form-group">
                        <label class="control-label" for="cf3"><?php echo $this->lang->line("cf3"); ?></label>
                        <div class="input-group">
                            <?= form_input('cf3', $api->cf3, 'class="form-control" id="cf3"'); ?>
                            <span class="input-group-addon pointer cf3" onclick="add_cf(this)" style="padding: 1px 10px;">
                                <i class="fa fa-plus-square"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="" id="row_cf4">
                    <div class="form-group">
                        <label class="control-label" for="cf4"><?php echo $this->lang->line("cf4"); ?></label>
                        <div class="input-group">
                            <?= form_input('cf4', $api->cf4, 'class="form-control" id="cf4"'); ?>
                            <span class="input-group-addon pointer cf4" onclick="add_cf(this)" style="padding: 1px 10px;">
                                <i class="fa fa-plus-square"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="" id="row_cf5">
                    <div class="form-group">
                        <label class="control-label" for="cf5"><?php echo $this->lang->line("cf5"); ?></label>
                        <div class="input-group">
                            <?= form_input('cf5', $api->cf5, 'class="form-control" id="cf5"'); ?>
                            <span class="input-group-addon pointer cf5" onclick="add_cf(this)" style="padding: 1px 10px;">
                                <i class="fa fa-plus-square"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="" id="row_cf6">
                    <div class="form-group">
                        <label class="control-label" for="cf6"><?php echo $this->lang->line("cf6"); ?></label>
                        <div class="input-group">
                            <?= form_input('cf6', $api->cf6, 'class="form-control" id="cf6"'); ?>
                            <span class="input-group-addon pointer cf6" onclick="add_cf(this)" style="padding: 1px 10px;">
                                <i class="fa fa-plus-square"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="" id="row_cf7">
                    <div class="form-group">
                        <label class="control-label" for="cf7"><?php echo $this->lang->line("cf7"); ?></label>
                        <div class="input-group">
                            <?= form_input('cf7', $api->cf7, 'class="form-control" id="cf7"'); ?>
                            <span class="input-group-addon pointer cf7" onclick="add_cf(this)" style="padding: 1px 10px;">
                                <i class="fa fa-plus-square"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="" id="row_cf8">
                    <div class="form-group">
                        <label class="control-label" for="cf8"><?php echo $this->lang->line("cf8"); ?></label>
                        <div class="input-group">
                            <?= form_input('cf8', $api->cf8, 'class="form-control" id="cf8"'); ?>
                            <span class="input-group-addon pointer cf8" onclick="add_cf(this)" style="padding: 1px 10px;">
                                <i class="fa fa-plus-square"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="" id="row_cf9">
                    <div class="form-group">
                        <label class="control-label" for="cf9"><?php echo $this->lang->line("cf9"); ?></label>
                        <div class="input-group">
                            <?= form_input('cf9', $api->cf9, 'class="form-control" id="cf9"'); ?>
                            <span class="input-group-addon pointer cf9" onclick="add_cf(this)" style="padding: 1px 10px;">
                                <i class="fa fa-plus-square"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="" id="row_cf10">
                    <div class="form-group">
                        <label class="control-label" for="cf10"><?php echo $this->lang->line("cf10"); ?></label>
                        <div class="input-group">
                            <?= form_input('cf10', $api->cf10, 'class="form-control" id="cf10"'); ?>
                            <span class="input-group-addon pointer cf10" onclick="add_cf(this)" style="padding: 1px 10px;">
                                <i class="fa fa-plus-square"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_API', lang('edit_API'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
</div>
<?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?= FORCAPOS_VERSION ?>"></script>
<?= $modal_js ?>