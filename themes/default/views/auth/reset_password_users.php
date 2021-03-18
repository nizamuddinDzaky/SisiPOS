<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
    .pac-container {
        z-index: 10000 !important;
    }

    .field-icon {
        float: right;
        margin-right: 10px;
        margin-top: -25px;
        position: relative;
        z-index: 2;
        cursor: pointer;
        color: #a3a3a3;
        z-index: 2;
        display: none;
    }
</style>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('reset_password'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("auth/reset_password_list_all_users/" . $user->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <?= lang("new_password", "new_password"); ?>
                        <input type="password" name="new_password" class="form-control" required="required" id="new_password" />
                        <span id="show_new_password" class="fa fa-fw fa-eye field-icon toggle-password"></span>
                        <p id="message_new_password" style="margin-top: 10px; color: #a94442; font-size: smaller;"></p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <?= lang("confirm_password", "confirm_password"); ?>
                        <input type="password" name="confirm_password" class="form-control" required="required" id="confirm_password" />
                        <span id="show_confirm_password" class="fa fa-fw fa-eye field-icon toggle-password"></span>
                        <p id="message" style="margin-top: 10px; color: #a94442; font-size: smaller;"></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <?php echo form_submit('reset_password', lang('submit'), 'class="btn btn-primary" id = "reset_password"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript">
    fields = $('.modal-content').find('.form-control');
    $.each(fields, function() {
        var id = $(this).attr('id');
        var iname = $(this).attr('name');
        var iid = '#' + id;
        if (!!$(this).attr('data-bv-notempty') || !!$(this).attr('required')) {
            $("label[for='" + id + "']").append(' *');
            $(document).on('change', iid, function() {
                $('form[data-toggle="validator"]').bootstrapValidator('revalidateField', iname);
            });
        }
    });
</script>
<script>
    var conpasswordStatus = false;
    $("#new_password").on("keyup input change blur", function() {
        conpasswordStatus = false;
        if ($(this).val()) {
            $("#show_new_password").show();
            var password = document.getElementById("new_password").value;
            var count = password.length;
            if (count < 7) {
                conpasswordStatus = false;
                document.getElementById("message_new_password").innerHTML = "The new passwords must be at least 8 characters long";
            } else if (count > 25) {
                conpasswordStatus = false;
                $("#message_new_password").empty();
                document.getElementById("message_new_password").innerHTML = "The new password has a maximum of 25 characters";
            } else {
                $("#message_new_password").empty();
            }
        } else {
            $("#show_new_password").hide();
        }
        checkSubmitButton();
    }).keyup();

    $("#confirm_password").on("keyup input change blur", function() {
        conpasswordStatus = false;
        if ($(this).val()) {
            $("#show_confirm_password").show();
            var x = document.getElementById("new_password").value;
            if (x != $(this).val()) {
                conpasswordStatus = false;
                document.getElementById("message").innerHTML = "The confirm password must be matching with new password";
            } else {
                $("#message").empty();
                conpasswordStatus = true;
            }
        } else {
            $("#show_confirm_password").hide();
        }
        checkSubmitButton();
    }).keyup();

    function checkSubmitButton() {
        if (conpasswordStatus) {
            $('#reset_password').removeAttr('disabled');
        } else {
            $('#reset_password').attr('disabled', 'disabled');
        }
    }

    $('#show_new_password').click(function() {
        if ($(this).hasClass('fa-eye')) {
            $('#new_password').attr('type', 'text');
            $(this).removeClass('fa-eye');
            $(this).addClass('fa-eye-slash');

        } else {
            $('#new_password').attr('type', 'password');
            $(this).removeClass('fa-eye-slash');
            $(this).addClass('fa-eye');
        }
    });

    $('#show_confirm_password').click(function() {
        if ($(this).hasClass('fa-eye')) {
            $('#confirm_password').attr('type', 'text');
            $(this).removeClass('fa-eye');
            $(this).addClass('fa-eye-slash');

        } else {
            $('#confirm_password').attr('type', 'password');
            $(this).removeClass('fa-eye-slash');
            $(this).addClass('fa-eye');
        }
    })
</script>