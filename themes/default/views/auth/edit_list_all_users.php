<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
    .pac-container {
        z-index: 10000 !important;
    }
</style>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_users'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("auth/edit_list_all_users/" . $user->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group person">
                        <?= lang("username", "username"); ?>
                        <?php echo form_input('username', $user->username, 'class="form-control tip" id="username" required="required"'); ?>
                        <p style="margin-top: 10px; color: #a94442; font-size: smaller;" id="message_unique_username"></p>
                    </div>
                    <div class="form-group">
                        <?= lang("email", "email"); ?>
                        <?php echo form_input('email', $user->email, 'class="form-control tip" id="email" required="required"'); ?>
                        <p style="margin-top: 10px; color: #a94442; font-size: smaller;" id="message_unique_email"></p>
                    </div>
                    <div class="form-group">
                        <?= lang("first_name", "first_name"); ?>
                        <?php echo form_input('first_name', $user->first_name, 'class="form-control tip" id="first_name" required="required"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("last_name", "last_name"); ?>
                        <?php echo form_input('last_name', $user->last_name, 'class="form-control tip" id="last_name" required="required"'); ?>
                    </div>
                    <div class="form-group company">
                        <?= lang("company", "company"); ?>
                        <?php if (!$this->ion_auth->in_group('customer', $id) && !$this->ion_auth->in_group('supplier', $id)) { ?>
                            <?php echo form_input('company', $user->company, 'class="form-control" id="company" required="required"'); ?>
                        <?php } else {
                            echo form_hidden('company', $user->company);
                        } ?>
                    </div>
                    <div class="form-group">
                        <?= lang("phone", "phone"); ?>
                        <input type="tel" name="phone" class="form-control" required="required" id="phone" value="<?= $user->phone ?>" />
                    </div>
                    <div class="form-group">
                        <?= lang("gender", "gender"); ?>
                        <?php
                        $ge[''] = array('male' => lang('male'), 'female' => lang('female'));
                        echo form_dropdown('gender', $ge, (isset($_POST['gender']) ? $_POST['gender'] : $user->gender), 'class="form-control" id="gender" required="required"');
                        ?>
                    </div>
                    <div class="form-group">
                        <?= lang("address", "address"); ?>
                        <?php echo form_input('address', $user->address, 'class="form-control" id="address" required="required"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("award_points", "award_points"); ?>
                        <?= form_input('award_points', set_value('award_points', $user->award_points), 'class="form-control tip" id="award_points"  required="required"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("postal_code", "postal_code"); ?>
                        <?php echo form_input('postal_code', $company->postal_code, 'class="form-control" id="postal_code"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("vat_no", "vat_no"); ?>
                        <?php echo form_input('vat_no', $company->vat_no, 'class="form-control" id="vat_no"'); ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("Province", "prov"); ?>
                        <select name="country" id="prov" onchange="setProvinsi_edit(this.value,this.options[this.selectedIndex].innerHTML)" class="form-control" required>
                        </select>
                    </div>
                    <div class="form-group">
                        <?= lang("city", "city"); ?>
                        <select name="city" id="city" onchange="setKabupaten_edit(this.value,this.options[this.selectedIndex].innerHTML)" class="form-control" required>
                        </select>
                    </div>
                    <div class="form-group">
                        <?= lang("state", "state"); ?>
                        <select name="state" id="state" onchange="setKecamatan_edit(this.value,this.options[this.selectedIndex].innerHTML)" class="form-control" required>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="sales_person"><?php echo $this->lang->line("sales_person"); ?></label>
                        <?php
                        $sp[''] = lang('select') . ' ' . lang('sales_person');
                        foreach ($sales_persons as $sales_person) {
                            $sp[$sales_person->id] = $sales_person->reference_no . " ~ " . $sales_person->name;
                        }
                        if ($salesperson) {
                            $sp[$salesperson->id] = $salesperson->reference_no . " ~ " . $salesperson->name . " " . ($salesperson->is_active != 1 ? '[' . lang('inactive') . ']' : '');
                        }
                        echo form_dropdown('sales_person', $sp, $user->sales_person_id, 'class="form-control select" id="sales_person" style="width:100%;"');
                        ?>
                    </div>
                    <div class="form-group">
                        <?= lang("group", "group"); ?>
                        <?php
                        $gp[""] = "";
                        foreach ($groups as $group) {
                            if ($group['name'] != 'customer' && $group['name'] != 'supplier') {
                                $gp[$group['id']] = $group['name'];
                            }
                        }
                        echo form_dropdown('group', $gp, (isset($_POST['group']) ? $_POST['group'] : $user->group_id), 'id="group" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("group") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                        ?>
                    </div>
                    <div class="form-group">
                        <?= lang("biller", "biller"); ?>
                        <?php
                        $bl[""] = lang('select') . ' ' . lang('biller');
                        foreach ($billers as $biller) {
                            $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                        }
                        echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $user->biller_id), 'id="biller" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("biller") . '" class="form-control select" style="width:100%;"');
                        ?>
                    </div>
                    <div class="form-group">
                        <?= lang("warehouse", "warehouse"); ?>
                        <?php
                        $wh[''] = lang('select') . ' ' . lang('warehouse');
                        foreach ($warehouses as $warehouse) {
                            $wh[$warehouse->id] = $warehouse->name;
                        }
                        echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $user->warehouse_id), 'id="warehouse" class="form-control select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("warehouse") . '" style="width:100%;" ');
                        ?>
                    </div>
                    <div class="form-group">
                        <?= lang("view_right", "view_right"); ?>
                        <?php
                        $vropts = array(1 => lang('all_records'), 0 => lang('own_records'));
                        echo form_dropdown('view_right', $vropts, (isset($_POST['view_right']) ? $_POST['view_right'] : $user->view_right), 'id="view_right" class="form-control select" style="width:100%;"');
                        ?>
                    </div>
                    <div class="form-group">
                        <?= lang("edit_right", "edit_right"); ?>
                        <?php
                        $opts = array(1 => lang('yes'), 0 => lang('no'));
                        echo form_dropdown('edit_right', $opts, (isset($_POST['edit_right']) ? $_POST['edit_right'] : $user->edit_right), 'id="edit_right" class="form-control select" style="width:100%;"');
                        ?>
                    </div>
                    <div class="form-group">
                        <?= lang("allow_discount", "allow_discount"); ?>
                        <?= form_dropdown('allow_discount', $opts, (isset($_POST['allow_discount']) ? $_POST['allow_discount'] : $user->allow_discount), 'id="allow_discount" class="form-control select" style="width:100%;"'); ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div style="position: relative;">
                        <?php if ($user->avatar) { ?>
                            <img alt="" src="<?= avatar_image($user->avatar, $user->gender) ?>" class="profile-image img-thumbnail">
                            <a href="#" class="btn btn-danger btn-xs po" style="position: absolute; top: 0;" title="<?= lang('delete_avatar') ?>" data-content="<p><?= lang('r_u_sure') ?></p><a class='btn btn-block btn-danger' href='<?= site_url('auth/delete_avatar/' . $user->id . '/' . $user->avatar) ?>'> <?= lang('i_m_sure') ?></a> <button class='btn btn-block po-close'> <?= lang('no') ?></button>" data-html="true" rel="popover"><i class="fa fa-trash-o"></i></a><br>
                            <br><?php } ?>
                    </div>
                    <div class="form-group">
                        <?= lang("change_avatar", "change_avatar"); ?>
                        <input type="file" data-browse-label="<?= lang('browse'); ?>" name="avatar" id="avatar" data-show-upload="false" data-show-preview="false" accept="image/*" class="form-control file" />
                    </div>
                    <div class="form-group">
                        <?php echo form_hidden($csrf); ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <input type="checkbox" class="checkbox" name="is_active" id="is_active" <?= $user->active ? 'checked="checked"' : ''; ?>>
                        <label for="is_active" class="padding05"><?= lang('active') ?></label>
                    </div>
                    <div class="form-group">
                        <input type="checkbox" class="checkbox" value="1" name="custom_fields" id="check_ccf_edit">
                        <label for="check_ccf_edit" class="padding05"><?= lang('custom_fields') ?></label>
                    </div>
                </div>
            </div>
            <div class="row" id="field_ccf_edit" style="display: none!important;">
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("ccf1", "cf1"); ?>
                        <?php echo form_input('cf1', $company->cf1, 'class="form-control" id="cf1"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("ccf2", "cf2"); ?>
                        <?php echo form_input('cf2', $company->cf2, 'class="form-control" id="cf2"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("ccf3", "cf3"); ?>
                        <?php echo form_input('cf3', $company->cf3, 'class="form-control" id="cf3"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("ccf4", "cf4"); ?>
                        <?php echo form_input('cf4', $company->cf4, 'class="form-control" id="cf4"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("ccf5", "cf5"); ?>
                        <?php echo form_input('cf5', $company->cf5, 'class="form-control" id="cf5"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("ccf6", "cf6"); ?>
                        <?php echo form_input('cf6', $company->cf6, 'class="form-control" id="cf6"'); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <?php echo form_submit('edit_users', lang('submit'), 'class="btn btn-primary" id = "edit_users"'); ?>
        </div>

    </div>
    <?php echo form_close(); ?>
</div>

<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $.getJSON('<?= base_url(); ?>daerah/getProvinsi', function(data) {
            var output = `<option value="" data-foo="">Choose Province</option>`;
            var user_country = "<?= trim($user->country) ?>";
            var province_exist = false;
            $.each(data, function(key, val) {
                output += '<option value="' + val.province_name + '" data-foo="" ' + (user_country.toUpperCase() == val.province_name.toUpperCase() ? 'selected' : '') + '>' + val.province_name + '</option>';
                if (user_country.toUpperCase() == val.province_name.toUpperCase()) {
                    province_exist = true;
                }
            });
            if (!province_exist) {
                output += '<option value="' + user_country + '" data-foo="" selected>' + user_country + '</option>';
            }
            $("#prov").html(output).change();
        });
        <?php if ($company->cf1 || $company->cf2 || $company->cf3 || $company->cf4 || $company->cf5 || $company->cf6) { ?>
            $('#field_ccf_edit').show();
            $("#check_ccf_edit").iCheck("check");
        <?php } ?>
        $('#check_ccf_edit').on('ifChecked', function() {
            $('#field_ccf_edit').slideDown();
        });
        $('#check_ccf_edit').on('ifUnchecked', function() {
            $('#field_ccf_edit').slideUp();
        });
    });

    function setProvinsi_edit(id, text) {
        $('#modal-loading').show();
        var urlProvinsi = "<?= base_url(); ?>daerah/getKabupaten/" + text.replace(/\s+/g, '_') + "/";
        var output = "";
        var user_city = `<?= trim($user->city) ?>`;
        output += '<option value="" data-foo="">Choose City</option>';
        $("#city").html(output);
        $('select[name=kabupaten]').val('').change();
        var city_exist = false;
        $.getJSON(urlProvinsi, function(data) {
            $.each(data, function(key, val) {
                output += '<option value="' + val.kabupaten_name + '" data-foo="" ' + (user_city.toUpperCase() == val.kabupaten_name.toUpperCase() ? 'selected' : '') + '>' + val.kabupaten_name + '</option>';
                if (user_city.toUpperCase() == val.kabupaten_name.toUpperCase()) {
                    city_exist = true;
                }
            });

            if (!city_exist) {
                output += '<option value="' + user_city + '" data-foo="" selected>' + user_city + '</option>';
            }

            $("#city").html(output).change();
            $('#modal-loading').hide();
        });
    }

    function setKabupaten_edit(id, text) {
        $('#modal-loading').show();
        var urlProvinsi = "<?= base_url(); ?>daerah/getKecamatan/" + text.replace(/\s+/g, '_') + "/";
        var output = "";
        var user_state = `<?= trim($user->state) ?>`;
        output += '<option value="" data-foo="">Choose District</option>';
        $("#state").html(output);
        $('select[name=kecamatan]').val('').change();
        var state_exist = false;
        $.getJSON(urlProvinsi, function(data) {
            $.each(data, function(key, val) {
                output += '<option value="' + val.kecamatan_name + '" data-foo="" ' + (user_state.toUpperCase() == val.kecamatan_name.toUpperCase() ? 'selected' : '') + '>' + val.kecamatan_name + '</option>';
                if (user_state.toUpperCase() == val.kecamatan_name.toUpperCase()) {
                    state_exist = true;
                }
            });
            if (!state_exist) {
                output += '<option value="' + user_state + '" data-foo="" selected>' + user_state + '</option>';
            }
            $("#state").html(output).change();
            $('#modal-loading').hide();
        });
    }
</script>
<script>
    var cekusername = false;
    var users_username = `<?= trim($user->username) ?>`;
    $("#username").on("keyup input change blur", function() {
        var username = document.getElementById("username").value;
        if (users_username != username) {
            $.ajax({
                url: "<?= base_url(); ?>auth/getUsername/" + username.replace(/@/g, '_') + "/",
                type: 'GET',
                cache: false,
                success: function(result) {
                    var data = JSON.parse(result);
                    if (data) {
                        document.getElementById("message_unique_username").innerHTML = "The username must be unique";
                        cekusername = false;
                    } else {
                        $("#message_unique_username").empty();
                        cekusername = true;
                    }
                }
            });
        } else {
            $("#message_unique_username").empty();
            cekusername = true;
        }
        checkSubmitButton();
    }).keyup();

    var cekemail = false;
    var users_email = `<?= trim($user->email) ?>`;
    $("#email").on("keyup input change blur", function() {
        var email = document.getElementById("email").value;
        if (users_email != email) {
            $.ajax({
                url: "<?= base_url(); ?>auth/getEmail/" + email.replace(/@/g, '_') + "/",
                type: 'GET',
                cache: false,
                success: function(result) {
                    var data = JSON.parse(result);
                    if (data) {
                        document.getElementById("message_unique_email").innerHTML = "The email must be unique";
                        cekemail = false;
                    } else {
                        $("#message_unique_email").empty();
                        cekemail = true;
                    }
                }
            });
        } else {
            $("#message_unique_email").empty();
            cekemail = true;
        }
        checkSubmitButton();
    }).keyup();

    function checkSubmitButton() {
        if (cekusername && cekemail) {
            $('#edit_users').removeAttr('disabled');
        } else {
            $('#edit_users').attr('disabled', 'disabled');
        }
    }
</script>
<?= $modal_js ?>