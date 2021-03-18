<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-sm-2">
        <div class="row">
            <div class="col-sm-12 text-center">
                <div style="max-width:200px; margin: 0 auto;">
                    <img alt="" src="<?= avatar_image($user->avatar, $user->gender)?>" class="avatar">
                </div>
                <h4><?= lang('login_email'); ?></h4>

                <p><i class="fa fa-envelope"></i> <?= $user->email; ?></p>
            </div>
        </div>
    </div>

    <div class="col-sm-10">
        <?php if((($this->Admin || $this->LT || $this->GP) && $id == $this->session->userdata('user_id')) || $this->Owner){?>
            <ul id="myTab" class="nav nav-tabs">
                <?php if($Owner || $Admin || $LT){?>
                <li class=""><a href="#edit" class="tab-grey"><?= lang('edit') ?></a></li> 
            <?php }?>
                <li class=""><a href="#cpassword" class="tab-grey"><?= lang('change_password') ?></a></li>
                <li class=""><a href="#avatar" class="tab-grey"><?= lang('avatar') ?></a></li>
                <!-- <li class=""><a href="#subscription" class="tab-grey"><?= lang('subscription') ?></a></li> --> 
            </ul>
        <?php } ?>

        <div class="tab-content">
            <div id="edit" class="tab-pane fade in active">

                <div class="box">
                    <div class="box-header">
                        <h2 class="blue"><i class="fa-fw fa fa-edit nb"></i><?= lang('edit_profile'); ?></h2>
                        <div class="box-icon">
                            <?php echo anchor($mb_edit_profile, '<i class="icon fa fa-book tip" data-placement="left" title="'.lang("manual_book").'"></i> ', 'target="_blank"') ?>
                        </div>
                    </div>
                    <div class="box-content">
                        <div class="row">
                            <div class="col-lg-12">

                                <?php $attrib = array('class' => 'form-horizontal', 'data-toggle' => 'validator', 'role' => 'form');
                                echo form_open('auth/edit_user/' . $user->id, $attrib);
                                ?>

                                <div class="row">
                                        <div class="col-md-5" style="padding-left: 30px;">
                                            <div class="form-group">
                                                    <?php echo lang('first_name', 'first_name'); ?>
                                                    <div class="controls">
                                                        <?php echo form_input('first_name', $user->first_name, 'class="form-control" id="first_name" required="required"'); ?>
                                                    </div>
                                                </div>
                                        </div>
                                        <div class="col-md-5" style="padding-left: 30px;">
                                            <div class="form-group">
                                                <?php echo lang('last_name', 'last_name'); ?>

                                                <div class="controls">
                                                    <?php echo form_input('last_name', $user->last_name, 'class="form-control" id="last_name" required="required"'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    <div class="clearfix"></div>
                                        <div class="col-md-5" style="padding-left: 30px;">
                                            <div class="form-group">
                                                    <?php echo lang('email', 'email'); ?>
                                                    <input type="email" name="email" class="form-control" id="email"
                                                           value="<?= $user->email ?>" required="required"/>
                                            </div>
                                        </div>
                                        <div class="col-md-5" style="padding-left: 30px;">
                                            <?php if (!$this->ion_auth->in_group('customer', $id) && !$this->ion_auth->in_group('supplier', $id)) { ?>
                                                <div class="form-group">
                                                    <?php echo lang('company', 'company_user'); ?>
                                                    <div class="controls">
                                                        <?php echo form_input('company', $user->company, 'class="form-control" id="company_user" required="required"'); ?>
                                                    </div>
                                                </div>
                                            <?php } else {
                                                echo form_hidden('company', $user->company);
                                            } ?>
                                        </div>
                                    <div class="clearfix"></div>
                                        <div class="col-md-5" style="padding-left: 30px;">
                                            <div class="form-group">
                                                <?php echo lang('phone', 'phone_user'); ?>
                                                <div class="controls">
                                                    <input type="tel" name="phone" class="form-control" id="phone_user"
                                                           required="required" value="<?= $user->phone ?>"/>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-5" style="padding-left: 30px;">
                                            <div class="form-group">
                                                <?= lang('gender', 'gender'); ?>
                                                <div class="controls">  <?php
                                                    $ge[''] = array('male' => lang('male'), 'female' => lang('female'));
                                                    echo form_dropdown('gender', $ge, (isset($_POST['gender']) ? $_POST['gender'] : $user->gender), 'class="tip form-control" id="gender" required="required"');
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    <div class="clearfix"></div>

                                    <?php if(($this->Admin || $this->LT) && $id != $this->session->userdata('user_id')){?>
                                        <div class="col-md-5" style="padding-left: 30px;">
                                            <div class="form-group">
                                                <?= lang("group", "group"); ?>
                                                <?php
                                                $gp[""] = "";
                                                if ($this->Admin) {
                                                    foreach ($groups as $group) {
                                                         if ($group['name'] == 'kasir' || $group['name'] == 'admin gudang') {
                                                            $gp[$group['id']] = lang($group['name']);
                                                         }
                                                    }
                                                }else if ($this->Owner) {
                                                    foreach ($groups as $group) {
                                                         if ($group['name'] != 'customer' && $group['name'] != 'supplier'  && $group['name'] != 'areamanager' && $group['name'] != 'owner' && $group['name'] != 'admin') {
                                                            $gp[$group['id']] = $group['name'];
                                                         }
                                                    }
                                                }
                                                echo form_dropdown('group', $gp, (isset($_POST['group']) ? $_POST['group'] : $user->group_id), 'id="group" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("group") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                                ?>
                                            </div>
                                        </div>

                                        <div class="col-md-5" style="padding-left: 30px;">
                                            <div class="form-group">
                                                <?= lang('status', 'status'); ?>
                                                <?php
                                                $opt = array(1 => lang('active'), 0 => lang('inactive'));
                                                echo form_dropdown('status', $opt, (isset($_POST['status']) ? $_POST['status'] : $user->active), 'id="status" required="required" class="form-control input-tip select" style="width:100%;"');
                                                ?>
                                            </div>
                                        </div>

                                        <div class="col-md-5" style="padding-left: 30px;">
                                            <div class="form-group">
                                                <?= lang("warehouse", "warehouse"); ?>
                                                <?php
                                                $wh[''] = lang('select').' '.lang('warehouse');
                                                foreach ($warehouses as $warehouse) {
                                                    $wh[$warehouse->id] = $warehouse->name;
                                                }
                                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $user->warehouse_id), 'id="warehouse" class="form-control select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("warehouse") . '" style="width:100%;" ');
                                                ?>
                                            </div>
                                        </div>

                                        <div class="col-md-5" style="padding-left: 30px;">
                                            <div class="form-group">
                                                <?= lang("view_right", "view_right"); ?>
                                                <?php
                                                $vropts = array(1 => lang('all_records'), 0 => lang('own_records'));
                                                echo form_dropdown('view_right', $vropts, (isset($_POST['view_right']) ? $_POST['view_right'] : $user->view_right), 'id="view_right" class="form-control select" style="width:100%;"');
                                                ?>
                                            </div>
                                        </div>

                                        <div class="col-md-5" style="padding-left: 30px;">
                                            <div class="form-group">
                                                <?= lang("edit_right", "edit_right"); ?>
                                                <?php
                                                $opts = array(1 => lang('yes'), 0 => lang('no'));
                                                echo form_dropdown('edit_right', $opts, (isset($_POST['edit_right']) ? $_POST['edit_right'] : $user->edit_right), 'id="edit_right" class="form-control select" style="width:100%;"');
                                                ?>
                                            </div>
                                        </div>

                                        <div class="col-md-5" style="padding-left: 30px;">
                                            <div class="form-group">
                                                <?= lang("allow_discount", "allow_discount"); ?>
                                                <?= form_dropdown('allow_discount', $opts, (isset($_POST['allow_discount']) ? $_POST['allow_discount'] : $user->allow_discount), 'id="allow_discount" class="form-control select" style="width:100%;"'); ?>
                                            </div>
                                        </div>
                                    <?php }?>
                                    <div class="clearfix"></div>
                                    <div class="col-md-5" style="padding-left: 30px;">
                                        <script type="text/javascript" src="<?= $assets ?>js/daerah.js"></script>
                                        <div class="form-group">
                                            <?= lang('Province*', 'Province*'); ?>
                                            <div class="controls">
                                                <select name="provinsi" id="provinsi" onchange="setProvinsi(this.value,this.options[this.selectedIndex].innerHTML)" class="form-control select" required>
                                                    <option value="">Choose Province</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-5" style="padding-left: 30px;">
                                        <div class="form-group">
                                            <?= lang('City*', 'City*'); ?>
                                            <div class="controls">
                                                <select name="kabupaten" id="kabupaten" onchange="setKabupaten(this.value,this.options[this.selectedIndex].innerHTML)" class="form-control select" required>
                                                    <option value="">Choose City</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-5" style="padding-left: 30px;">
                                        <div class="form-group">
                                            <?= lang('District*', 'District*'); ?>
                                            <div class="controls">
                                                <select name="kecamatan" id="kecamatan" onchange="setKecamatan(this.value,this.options[this.selectedIndex].innerHTML)" class="form-control select">
                                                    <option value="">Choose District</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-5" style="padding-left: 30px;">
                                        <div class="form-group">
                                            <?= lang('Address*', 'Address*'); ?>
                                            <div class="controls">
                                                <input type="text" name="address" id="address" class="form-control skip" required value="<?= $user->address ?>">
                                                <!--<textarea name="address" id="address" class="form-control skip" rows="3" required style="resize:none"><?= $user->address ?></textarea>-->
                                            </div>
                                        </div>
                                    </div>
                                    <?php if(($this->Admin || $this->LT)&&$id == $this->session->userdata('user_id')){?>
                                        <div class="col-md-5" style="padding-left: 30px;">
                                            <div class="form-group">
                                                <?= lang('postal_code', 'postalcode'); ?>
                                                <div class="controls">
                                                    <input name="postalcode" id="postalcode" value="<?= $billers[0]->postal_code ?>" class="form-control number-only" required/>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-5" style="padding-left: 30px;">
                                            <div class="" id="row_cf1">
                                                <div class="form-group">
                                                    <?= lang('cf1', 'cf1'); ?>
                                                    <div class="input-group">
                                                        <?php echo form_input('cf1', $company->cf1, 'class="form-control" id="cf1"'); ?>
                                                        <span class="input-group-addon pointer cf1" onclick="add_cf(this)" style="padding: 1px 10px;">
                                                            <i class="fa fa-plus-square"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-5" style="padding-left: 30px;">
                                            <div class="" id="row_cf2">
                                                <div class="form-group">
                                                    <?= lang('cf2', 'cf2'); ?>
                                                    <div class="input-group">
                                                        <?php echo form_input('cf2', $company->cf2, 'class="form-control" id="cf2"'); ?>
                                                        <span class="input-group-addon pointer cf2" onclick="add_cf(this)" style="padding: 1px 10px;">
                                                            <i class="fa fa-plus-square"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-5" style="padding-left: 30px;">
                                            <div class="" id="row_cf3">
                                                <div class="form-group">
                                                    <?= lang('cf3', 'cf3'); ?>
                                                    <div class="input-group">
                                                        <?php echo form_input('cf3', $company->cf3, 'class="form-control" id="cf3"'); ?>
                                                        <span class="input-group-addon pointer cf3" onclick="add_cf(this)" style="padding: 1px 10px;">
                                                            <i class="fa fa-plus-square"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-5" style="padding-left: 30px;">
                                            <div class="" id="row_cf4">
                                                <div class="form-group">
                                                    <?= lang('cf4', 'cf4'); ?>
                                                    <div class="input-group">
                                                        <?php echo form_input('cf4', $company->cf4, 'class="form-control" id="cf4"'); ?>
                                                        <span class="input-group-addon pointer cf4" onclick="add_cf(this)" style="padding: 1px 10px;">
                                                            <i class="fa fa-plus-square"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-5" style="padding-left: 30px;">
                                            <div class="" id="row_cf5">
                                                <div class="form-group">
                                                    <?= lang('cf5', 'cf5'); ?>
                                                    <div class="controls">
                                                        <?php echo form_input('cf5', $company->cf5, 'class="form-control" id="cf5"'); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php }?>

                                    <div class="clearfix"></div>
                                        <input id="latitude" name="latitude" type="hidden" value="<?= $company->latitude ?>">
                                        <input id="longitude" name="longitude" type="hidden" value="<?= $company->longitude ?>">
                                    <div class="clearfix"></div>
                                        <!-- <div class="col-md-12">
                                            <div id="map-canvas" style="width: 100%; height: 19em"></div>
                                        </div> -->
                                    <div class="col-md-12">
                                        <div class="col-md-5">
                                            <?php if (($Owner || $Admin) && $id != $this->session->userdata('user_id')) { ?>
                                            <div class="form-group">
                                                <?= lang('award_points', 'award_points'); ?>
                                                <?= form_input('award_points', set_value('award_points', $user->award_points), 'class="form-control tip" id="award_points"  required="required"'); ?>
                                            </div>
                                            <?php } ?>

                                            <?php if ($Owner && $id != $this->session->userdata('user_id')) { ?>
                                                <div class="form-group">
                                                    <?php echo lang('username', 'username'); ?>
                                                    <input type="text" name="username" class="form-control"
                                                           id="username" value="<?= $user->username ?>"
                                                           required="required"/>
                                                </div>
                                                <div class="row">
                                                    <div class="panel panel-warning">
                                                        <div
                                                            class="panel-heading"><?= lang('if_you_need_to_rest_password_for_user') ?></div>
                                                        <div class="panel-body" style="padding: 5px;">
                                                            <div class="col-md-12">
                                                                <div class="col-md-12">
                                                                    <div class="form-group">
                                                                        <?php echo lang('password', 'password'); ?>
                                                                        <?php echo form_input($password); ?>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <?php echo lang('confirm_password', 'password_confirm'); ?>
                                                                        <?php echo form_input($password_confirm); ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            <?php } ?>

                                        </div>
                                        <div class="col-md-6 col-md-offset-1">
                                            <?php if ($Owner && $id != $this->session->userdata('user_id')) { ?>

                                                    <div class="row">
                                                        <div class="panel panel-warning">
                                                            <div class="panel-heading"><?= lang('user_options') ?></div>
                                                            <div class="panel-body" style="padding: 5px;">
                                                                <div class="col-md-12">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group">
                                                                            <?= lang('status', 'status'); ?>
                                                                            <?php
                                                                            $opt = array(1 => lang('active'), 0 => lang('inactive'));
                                                                            echo form_dropdown('status', $opt, (isset($_POST['status']) ? $_POST['status'] : $user->active), 'id="status" required="required" class="form-control input-tip select" style="width:100%;"');
                                                                            ?>
                                                                        </div>
                                                                        <?php if (!$this->ion_auth->in_group('customer', $id) && !$this->ion_auth->in_group('supplier', $id)) { ?>
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
                                                                        <div class="clearfix"></div>
                                                                        <div class="no">
                                                                            <div class="form-group">
                                                                                <?= lang("biller", "biller"); ?>
                                                                                <?php
                                                                                $bl[""] = lang('select').' '.lang('biller');
                                                                                foreach ($billers as $biller) {
                                                                                    $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                                                                }
                                                                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $user->biller_id), 'id="biller" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("biller") . '" class="form-control select" style="width:100%;"');
                                                                                ?>
                                                                            </div>

                                                                            <div class="form-group">
                                                                                <?= lang("warehouse", "warehouse"); ?>
                                                                                <?php
                                                                                $wh[''] = lang('select').' '.lang('warehouse');
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
                                                                            <?php } ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                            <?php } ?>
                                            <?php echo form_hidden('id', $id); ?>
                                            <?php echo form_hidden($csrf); ?>
                                        </div>
                                    </div>
                                </div>
                                <p><?php echo form_submit('update', lang('update'), 'class="btn btn-primary" id="btn-update-profile"'); ?></p>
                                <?php echo form_close(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="cpassword" class="tab-pane fade">
                <div class="box">
                    <div class="box-header">
                        <h2 class="blue"><i class="fa-fw fa fa-key nb"></i><?= lang('change_password'); ?></h2>
                        <div class="box-icon">
                            <?php echo anchor($mb_change_password, '<i class="icon fa fa-book tip" data-placement="left" title="'.lang("manual_book").'"></i> ', 'target="_blank"') ?>
                        </div>
                    </div>
                    <div class="box-content">
                        <div class="row">
                            <div class="col-lg-12">
                                <?php echo form_open("auth/change_password", 'id="change-password-form"'); ?>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <?php echo lang('old_password', 'curr_password'); ?> <br/>
                                                <?php echo form_password('old_password', '', 'class="form-control" id="curr_password" required="required"'); ?>
                                                <i id="show-password-old" class="fa fa-eye"></i>
                                            </div>

                                            <div class="form-group">
                                                <label
                                                    for="new_password"><?php echo sprintf(lang('new_password'), $min_password_length); ?></label>
                                                <br/>
                                                <?php echo form_password('new_password', '', 'class="form-control" id="new_password" required="required" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" data-bv-regexp-message="'.lang('pasword_hint').'"'); ?>
                                                <i id="show-password-new" class="fa fa-eye"></i>
                                                <span class="help-block"><?= lang('pasword_hint') ?></span>
                                            </div>

                                            <div class="form-group">
                                                <?php echo lang('confirm_password', 'new_password_confirm'); ?> <br/>
                                                <?php echo form_password('new_password_confirm', '', 'class="form-control" id="new_password_confirm" required="required" data-bv-identical="true" data-bv-identical-field="new_password" data-bv-identical-message="' . lang('pw_not_same') . '"'); ?>
                                                <i id="show-password-retype" class="fa fa-eye"></i>
                                                <small id="password-true" class="help-block" style="display:none; color:#4CAF50;"><?=lang('pw_is_same');?></small>

                                            </div>
                                            <?php echo form_input($user_id); ?>
                                            <p><?php echo form_submit('change_password', lang('change_password'), 'class="btn btn-primary"'); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <?php echo form_close(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="avatar" class="tab-pane fade">
                <div class="box">
                    <div class="box-header">
                        <h2 class="blue"><i class="fa-fw fa fa-file-picture-o nb"></i><?= lang('change_avatar'); ?></h2>
                        <div class="box-icon">
                            <?php echo anchor($mb_change_avatar, '<i class="icon fa fa-book tip" data-placement="left" title="'.lang("manual_book").'"></i> ', 'target="_blank"') ?>
                        </div>
                    </div>
                    <div class="box-content">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="col-md-5">
                                    <div style="position: relative;">
                                        <?php if ($user->avatar) { ?>
                                            <img alt=""
                                                 src="<?= avatar_image($user->avatar, $user->gender) ?>"
                                                 class="profile-image img-thumbnail">
                                            <a href="#" class="btn btn-danger btn-xs po"
                                               style="position: absolute; top: 0;" title="<?= lang('delete_avatar') ?>"
                                               data-content="<p><?= lang('r_u_sure') ?></p><a class='btn btn-block btn-danger' href='<?= site_url('auth/delete_avatar/' . $id . '/' . $user->avatar) ?>'> <?= lang('i_m_sure') ?></a> <button class='btn btn-block po-close'> <?= lang('no') ?></button>"
                                               data-html="true" rel="popover"><i class="fa fa-trash-o"></i></a><br>
                                            <br><?php } ?>
                                    </div>
                                    <?php echo form_open_multipart("auth/update_avatar"); ?>
                                    <div class="form-group">
                                        <?= lang("change_avatar", "change_avatar"); ?>
                                        <input type="file" data-browse-label="<?= lang('browse'); ?>" name="avatar" id="product_image" required="required"
                                               data-show-upload="false" data-show-preview="false" accept="image/*"
                                               class="form-control file"/>
                                    </div>
                                    <div class="form-group">
                                        <?php echo form_hidden('id', $id); ?>
                                        <?php echo form_hidden($csrf); ?>
                                        <?php echo form_submit('update_avatar', lang('update_avatar'), 'class="btn btn-primary"'); ?>
                                        <?php echo form_close(); ?>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="subscription" class="tab-pane fade">
                <script type="text/javascript">
                    $(document).ready(function () {
                        $('#BillAccountData').dataTable({
                            "aaSorting": [[1, "desc"]],
                            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                            "iDisplayLength": <?= $Settings->rows_per_page ?>,
                            'bProcessing': true, 'bServerSide': true,
                            'sAjaxSource': '<?= site_url('auth/get_subscription_record') ?>',
                            'fnServerData': function (sSource, aoData, fnCallback) {
                                aoData.push({
                                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                                    "value": "<?= $this->security->get_csrf_hash() ?>"
                                });
                                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
                            },
                            "aoColumns": [{"mRender": fld}, null, null, null, null, {"mRender": fsd}, {"mRender": fsd}, {"mRender": currencyFormat}, {"bSortable": false}]
                        });
                    });
                </script>
                <div class="box">
                    <div class="box-header">
                        <h2 class="blue"><i class="fa-fw fa fa-heart nb"></i><?= lang('subscription'); ?></h2>
                    </div>
                    <div class="box-content">
                        <div class="row">
                            <div class="col-lg-12">
                                <p class="introtext"><?php echo lang('list_results'); ?></p>

                                <div class="well">
                                    <div class="col-md-6">
                                        <h2><?=lang('current_plan')?> :</h2>
                                    </div>
                                    <div class="col-md-6">
                                        <?php // echo lang('pricing_plans','plans_pricing')?>
                                        <div class="form-group pull-right">
                                            <a href='<?= site_url('auth/view_plans_pricing');?>' name="plans_pricing" id="plans_pricing" class="btn btn-primary" data-toggle="modal" data-target="#myModal"  data-backdrop="static"><?=lang('view_pricing_plans')?></a>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-hover table-striped  table-condensed">
                                                    <thead>
                                                    <tr>
                                                        <th><?php echo $this->lang->line("type"); ?></th>
                                                        <th><?php echo $this->lang->line("status"); ?></th>
                                                        <th><?php echo $this->lang->line("start_date"); ?></th>
                                                        <th><?php echo $this->lang->line("expired_date"); ?></th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php ?>
                                                        <tr>
                                                            <td class="text-center"><?=$authorized->plan_name?></td>
                                                            <td class="text-center"><?=$authorized->status?></td>
                                                            <td class="text-center" style="width:25%;"><?=$this->sma->hrsd($authorized->start_date)?></td>
                                                            <td class="text-center" style="width:25%;"><?=$this->sma->hrsd($authorized->expired_date)?></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="table-responsive">
                                    <table id="BillAccountData" class="table table-bordered table-hover table-striped  table-condensed">
                                        <thead>
                                        <tr>
                                            <th><?php echo $this->lang->line("date"); ?></th>
                                            <th><?php echo $this->lang->line("reference_no"); ?></th>
                                            <th><?php echo $this->lang->line("payment_status"); ?></th>
                                            <th><?php echo $this->lang->line("company"); ?></th>
                                            <th><?php echo $this->lang->line("type"); ?></th>
                                            <th><?php echo $this->lang->line("start_date"); ?></th>
                                            <th><?php echo $this->lang->line("expired_date"); ?></th>
                                            <th><?php echo $this->lang->line("grand_total"); ?></th>
                                            <th style="max-width:85px;"><?php echo $this->lang->line("actions"); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="9" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<form id="snap_form" method="post" action="<?=site_url()?>snap/finish">
    <input type="hidden" name="result_type" id="result-type" value="">
    <input type="hidden" name="result_data" id="result-data" value="">
    <input type="hidden" name="billing_id" id="billing_id" value="">
</form>
    <script>
        var address = {
            country:'<?= str_replace(" ","-",strtoupper($user->country))?>',
            city:'<?= str_replace("KAB. ","",strtoupper($user->city)) ?>',
            state:'<?= strtoupper($user->state) ?>',
        };
        var coordinate = {
            latitude: `<?php echo ($company->latitude?$company->latitude:0) ?>`,
            longitude: `<?php echo ($company->longitude?$company->longitude:0) ?>`,
        };
        var cfield = 1;
        for(var i=1;i<=5;i++){
            $('#row_cf'+i).slideUp();
        }
        function add_cf(row){
            row.closest('.input-group').classList.add('controls');
            row.closest('.input-group').classList.remove('input-group');
            row.remove();
            if (cfield <= 4) {
                cfield++;
                $('#row_cf'+cfield).slideDown();
            } else {
                bootbox.alert('<?= lang('max_reached') ?>');
                return false;
            }
        }
        $(document).ready(function () {
            <?php if(($this->Admin || $this->LT)&&$id == $this->session->userdata('user_id')){?>
                <?php if($company->cf1){?>
                    add_cf($(".cf1")[0]);
                <?php } if($company->cf2){ ?>
                    add_cf($(".cf2")[0]);
                <?php } if($company->cf3){ ?>
                    add_cf($(".cf3")[0]);
                <?php } if($company->cf4){?>
                    add_cf($(".cf4")[0]);
                <?php } ?>
            <?php } ?>
            $('#cf1').change(function(){
                $.ajax({
                    url: "<?= base_url("auth/check_cf1_distributor/")?>" + $(this).val(),
                    method : "GET",
                    dataType : "json",
                    success : function(data){
                        if (data==0) {
                            bootbox.alert('<?=lang('duplicate_cf1')?>');
                            $('#btn-update-profile').attr('disabled', true);
                            return false
                        }else{
                            $('#btn-update-profile').removeAttr('disabled');
                        }
                    }
                })
            });
            $('#row_cf1').slideDown();
            $('#change-password-form').bootstrapValidator({
                message: 'Please enter/select a value',
                submitButtons: 'input[type="submit"]'
            });

            $.getJSON('<?php echo base_url(); ?>daerah/getProvinsi', function(data) {
                var output = "";
                output += '<option value="" data-foo="">Choose Province</option>';
                $.each(data, function(key, val) {
                    output += '<option value="' + val.province_name + '" data-foo="">' + val.province_name + '</option>';
                });
                $("#provinsi").html(output);
                <?php if(!empty($user->country)) {?>
                    $('select[name=provinsi]').val('<?= strtoupper($user->country) ?>').change();
                    setTimeout(function() {
                        $('select[name=kabupaten]').val('<?= str_replace("KAB. ","",strtoupper($user->city)) ?>').change();
                    }, 500);
                    setTimeout(function() {
                        $('select[name=kecamatan]').val('<?= strtoupper($user->state)  ?>').change();
                    },1000);
                    <?php if(!empty($user->city) && !empty($user->state)){?>
                        // moveMap(address,coordinate);
                    <?php }
                }?>
            });

        });
        $('#kecamatan').click(function(){
            var street = {
                country:$('#provinsi').val().replace(/\s+/g, '-'),
                city:$('#kabupaten').val().replace(/\s+/g, '-').replace('KAB.',''),
                state:$(this).val().replace(/\s+/g, '-')
            }
            moveMap(street,coordinate);
        });
    </script>
    <?php if ($Owner && $id != $this->session->userdata('user_id')) { ?>
    <script type="text/javascript" charset="utf-8">
        $(document).ready(function () {
            $('#group').change(function (event) {
                var group = $(this).val();
                if (group == 1 || group == 2) {
                    $('.no').slideUp();
                } else {
                    $('.no').slideDown();
                }
            });
            var group = <?=$user->group_id?>;
            if (group == 1 || group == 2) {
                $('.no').slideUp();
            } else {
                $('.no').slideDown();
            }
        });
    </script>
<?php } ?>
    <script>
    function pay(id){
        $.ajax({
            type: 'get',
            url: '<?=site_url()?>auth/get_data_billing_by_id/'+id,
            cache:false,
            success:function(data){
                if(data.payment_status == null){
                    show_snap(id);
                }else if(data.payment_status == 'pending'){
                    $('#myModal').modal({remote: site.base_url + 'snap/confirm_payment/' + id});
                    $('#myModal').modal('show');
                }else{
                    bootbox.alert('<?= lang("billing_paid") ?>');
                }
            }
        });
    }

    function changeResult(type,data){
        $("#result-type").val(type);
        $("#result-data").val(JSON.stringify(data));
        $("#billing_id").val(id);
    }

    function show_snap(id){
        $.ajax({
            type: 'get',
            url: '<?=site_url()?>snap/token',
            cache: false,
            data: {
                id: id,
            },
            success: function(data) {
                console.log(data)
//                if(!data.billing_invoice){
                    /*snap.pay(data.token, {
                        onSuccess: function(result){
                            changeResult('success', result);
                            console.log(result.status_message);
                            console.log(result);
                            $("#snap_form").submit();
                        },
                        onPending: function(result){
                            changeResult('pending', result);
                            console.log(result.status_message);
                            $("#snap_form").submit();
                        },
                        onError: function(result){
                            changeResult('error', result);
                            console.log(result.status_message);
                            $("#snap_form").submit();
                        }
                    });*/
//                }else{
//                    bootbox.alert('<?= lang('billing_paid') ?>');
//                }
            }
        });
    }
    </script>

<script>
    // SHOW HIDE PASSWORD 
$("#curr_password").on("keyup",function(){
    if($(this).val())
        $("#show-password-old").show();
    else
        $("#show-password-old").hide();
    });
$("#show-password-old").mousedown(function(){
                $("#curr_password").attr('type','text');
            })
            .mouseup(function(){
                $("#curr_password").attr('type','password');
            })
            .mouseout(function(){
                $("#curr_password").attr('type','password');
            });

// SHOW PASS NEW PASSWORD
$("#new_password").on("keyup",function(){
    if($(this).val())
        $("#show-password-new").show();
    else
        $("#show-password-new").hide();
    });
$("#show-password-new").mousedown(function(){
                $("#new_password").attr('type','text');
            })
            .mouseup(function(){
                $("#new_password").attr('type','password');
            })
            .mouseout(function(){
                $("#new_password").attr('type','password');
            });


// SHOW PASS RETYPE   PASSWORD
$("#new_password_confirm").on("keyup",function(){
    if($(this).val())
        $("#show-password-retype").show();
    else
        $("#show-password-retype").hide();
    
    let newPass = $("#new_password").val();
    let $this = $("#new_password_confirm").val();
    if( $this == newPass){
        $("#password-true").css("display","block");
    }else{
        $("#password-true").css("display","none");
    }
    
});

$("#show-password-retype").mousedown(function(){
                $("#new_password_confirm").attr('type','text');
            })
            .mouseup(function(){
                $("#new_password_confirm").attr('type','password');
            })
            .mouseout(function(){
                $("#new_password_confirm").attr('type','password');
            });
// END
</script>