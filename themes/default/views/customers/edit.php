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
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_customer'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("customers/edit/" . $customer->id, $attrib); ?>
        <input id="latitude" name="latitude" type="hidden" value="<?= $customer->latitude; ?>" />
        <input id="longitude" name="longitude" type="hidden" value="<?= $customer->longitude; ?>" />
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="customer_group"><?php echo $this->lang->line("customer_group"); ?></label>
                        <?php
                        foreach ($customer_groups as $customer_group) {
                            $cgs[$customer_group->id] = $customer_group->name;
                        }
                        echo form_dropdown('customer_group', $cgs, $customer->customer_group_id, 'class="form-control select" id="customer_group" style="width:100%;" required="required"');
                        ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="price_group"><?php echo $this->lang->line("price_group"); ?></label>
                        <?php
                        $pgs[''] = lang('select') . ' ' . lang('price_group');
                        foreach ($price_groups as $price_group) {
                            $pgs[$price_group->id] = $price_group->name;
                        }
                        echo form_dropdown('price_group', $pgs, $customer->price_group_id, 'class="form-control select" id="price_group" style="width:100%;"');
                        ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group company">
                        <?= lang("company", "company"); ?>
                        <?php echo form_input('company', $customer->company, 'class="form-control tip" id="company" required="required"'); ?>
                    </div>
                    <div class="form-group person">
                        <?= lang("name", "name"); ?>
                        <?php echo form_input('name', $customer->name, 'class="form-control tip" id="name" required="required"'); ?>
                    </div>
                    <!--<div class="form-group company">
                    <?= lang("contact_person", "contact_person"); ?>
                    <?php //echo form_input('contact_person', $customer->contact_person, 'class="form-control" id="contact_person" required="required"'); 
                    ?>
                </div> -->
                    <div class="form-group">
                        <?= lang("email_address", "email_address"); ?>
                        <input type="email" name="email" class="form-control" id="email_address" value="<?= $customer->email ?>" />
                    </div>
                    <div class="form-group">
                        <?= lang("phone", "phone"); ?>
                        <input type="tel" name="phone" class="form-control" required="required" id="phone" value="<?= $customer->phone ?>" />
                    </div>
                    <div class="form-group">
                        <?= lang("address", "address"); ?>
                        <?php echo form_input('address', $customer->address, 'class="form-control" id="address" required="required"'); ?>
                    </div>
                    
                    <?php if ($Principal) { ?>
                        <div class="form-group">
                            <?= lang('Distributor', 'Distributor'); ?>
                            <div class="controls">
                                <select name="distributor" class="form-control select" readonly>
                                    <option value="<?= $distributor->id ?>"><?= $distributor->company ?></option>
                                </select>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="sales_person"><?php echo $this->lang->line("sales_person"); ?></label>
                        <?php
                        $sp[''] = lang('select') . ' ' . lang('sales_person');
                        foreach ($sales_persons as $sales_person) {
                            $sp[$sales_person->id] = $sales_person->reference_no . " ~ " . $sales_person->name;
                        }
                        if($salesperson){
                            $sp[$salesperson->id] = $salesperson->reference_no . " ~ " . $salesperson->name . " " . ($salesperson->is_active != 1 ? '['.lang('inactive').']' : '');
                        }
                        echo form_dropdown('sales_person', $sp, $customer->sales_person_id, 'class="form-control select" id="sales_person" style="width:100%;"');
                        ?>
                    </div>
                    <div class="form-group">
                        <?= lang("postal_code", "postal_code"); ?>
                        <?php echo form_input('postal_code', $customer->postal_code, 'class="form-control" id="postal_code"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("vat_no", "vat_no"); ?>
                        <?php echo form_input('vat_no', $customer->vat_no, 'class="form-control" id="vat_no"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("Province", "prov"); ?>
                        <select name="country" id="prov" onchange="setProvinsi_edit(this.value,this.options[this.selectedIndex].innerHTML)" class="form-control select" required>
                        </select>
                    </div>
                    <div class="form-group">
                        <?= lang("city", "city"); ?>
                        <select name="city" id="city" onchange="setKabupaten_edit(this.value,this.options[this.selectedIndex].innerHTML)" class="form-control select" required="required">
                            <!-- <option value="<?php echo $customer->city ?>"><?php echo $customer->city ?></option> -->
                        </select>
                    </div>
                    <div class="form-group">
                        <?= lang("state", "state"); ?>
                        <!-- onchange="setKecamatan_edit(this.value,this.options[this.selectedIndex].innerHTML)" -->
                        <select name="state" id="state" class="form-control select" required>
                            <!-- <option value="<?php echo $customer->state ?>"><?php echo $customer->state ?></option> -->
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <?= lang('award_points', 'award_points'); ?>
                <?= form_input('award_points', set_value('award_points', $customer->award_points), 'class="form-control tip" id="award_points"  required="required"'); ?>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <input type="checkbox" class="checkbox" value="1" name="is_active" id="is_active" <?= $customer->is_active ? 'checked="checked"' : ''; ?>>
                        <label for="is_active" class="padding05"><?= lang('active') ?></label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("logo", "logo"); ?>
                        <input id="logo" type="file" data-browse-label="<?= lang('browse'); ?>" name="logo" data-show-upload="false" data-show-preview="false" accept="image/*" class="form-control file">
                    </div>
                </div>

                <div class="col-md-6">
                    <div id="logo-con" class="text-center">
                        <img src="<?= customer_logo_thumb($customer->logo) ?>" alt="">
                    </div>
                </div>
            </div>

            <?php if($Principal || $Owner){ ?>
            <div class="row">
                <div class="col-md-12">
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
                        <?php echo form_input('cf1', $customer->cf1, 'class="form-control" id="cf1"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("ccf2", "cf2"); ?>
                        <?php echo form_input('cf2', $customer->cf2, 'class="form-control" id="cf2"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("ccf3", "cf3"); ?>
                        <?php echo form_input('cf3', $customer->cf3, 'class="form-control" id="cf3"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("ccf4", "cf4"); ?>
                        <?php echo form_input('cf4', $customer->cf4, 'class="form-control" id="cf4"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("ccf5", "cf5"); ?>
                        <?php echo form_input('cf5', $customer->cf5, 'class="form-control" id="cf5"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("ccf6", "cf6"); ?>
                        <?php echo form_input('cf6', $customer->cf6, 'class="form-control" id="cf6"'); ?>
                    </div>
                </div>
            </div>
            <?php } ?>

            <?php 
            $w_default = null; 
            foreach($warehouse_default as $row1){
                $w_default = $row1->warehouse_name;
            } 
            ?>
            <p id="t_warehouse_list"><label><?= lang("warehouses"); ?></label><br>
            <?= lang("default_warehouse"); ?> : <label><?php if($w_default){  echo $w_default;  }else{  echo lang('unassigned');}  ?></label></p>
            <div class="table-responsive">
                <table id="CompTable" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-striped">
                    <thead>
                    <tr>
                        <th style="width:5%;"><?= $this->lang->line("assign_to_warehouse"); ?></th>
                        <th style="width:40%;"><?= $this->lang->line("code"); ?></th>
                        <th style="width:40%;"><?= $this->lang->line("name"); ?></th>
                        <th style="width:5%;"><?= $this->lang->line("default_warehouse"); ?></th>
                    </tr>
                    </thead>
                    <tbody name="warehouse_list" id="warehouse_list">
                        <?php $hidden = true; if($Owner){ ?>
                            <td colspan="4" style="text-align:center;"><?= lang("please_select_a_distributor_first"); ?></td>
                        <?php } else {?>
                            <?php foreach($warehouses as $row){ ?>
                                <tr>
                                    <td style="text-align:center;">
                                        <?php   
                                        $checked='';
                                        foreach($warehousesCustomer as $row1){
                                            if($checked==''){
                                                if($row->id==$row1->warehouse_id && $row1->is_deleted == 0){
                                                    $checked='checked';
                                                }
                                            }
                                        }   
                                        ?>
                                        <input type="checkbox" id="warehouse_<?= $row->id ?>" name="warehouses[]" value="<?= $row->id ?>" <?= $checked ?>>
                                    </td>
                                    <td><?= $row->code; ?></td>
                                    <td><?= $row->name; ?></td>
                                    <td style="text-align:center;">
                                        <?php   $checked='';
                                                foreach($warehousesCustomer as $row1){
                                                    if($checked==''){
                                                        if($row1->default == $row->id){
                                                            $checked='checked';
                                                        }
                                                    }
                                                }   ?>
                                        <input type="radio" id="default_<?= $row->id ?>" name="default" value="<?= $row->id ?>" <?= $checked ?>>
                                    </td>
                            <?php } ?>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <!-- <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                    <div id="map-canvas" style="width: 100%; height: 19em"></div>
                    </div>
                </div>
            </div> -->
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_customer', lang('submit'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/daerah.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript">
    var address = {
        country: '<?= str_replace(" ", "-", strtoupper($customer->country)) ?>',
        city: '<?= str_replace(" ", "-", str_replace("KAB. ", "", strtoupper($customer->city))) ?>',
        state: '<?= strtoupper($customer->state) ?>',
    };
    var coordinate = {
        latitude: `<?php echo ($customer->latitude ? $customer->latitude : 0) ?>`,
        longitude: `<?php echo ($customer->longitude ? $customer->longitude : 0) ?>`,
    };
</script>
<script type="text/javascript">
    $(document).ready(function() {
        <?php foreach($warehouses as $row){ ?>
            $(".modal-dialog").one( "mouseenter", function() {
                if (!$("#warehouse_<?= $row->id ?>").is(":checked")) {
                    $('#default_<?= $row->id ?>').parent().hide();
                }
            });
            $('#warehouse_<?= $row->id ?>').on('ifChecked', function () {
                $('#default_<?= $row->id ?>').parent().show();
            });
            $('#warehouse_<?= $row->id ?>').on('ifUnchecked', function () {
                $("#default_<?= $row->id ?>").iCheck("uncheck");
                $('#default_<?= $row->id ?>').parent().hide();
            });
        <?php } ?>

        $.getJSON('<?= base_url(); ?>daerah/getProvinsi', function(data) {
            var output = `<option value="" data-foo="">Choose Province</option>`;
            var customer_country = "<?= trim($customer->country) ?>";
            // output += '<option value="<?php echo $customer->country ?>" data-foo=""><?php echo $customer->country ?></option>';
            var province_exist = false;
            $.each(data, function(key, val) {
                output += '<option value="' + val.province_name + '" data-foo="" '+ (customer_country.toUpperCase() == val.province_name.toUpperCase() ? 'selected' : '' )  +'>' + val.province_name + '</option>';
                if(customer_country.toUpperCase() == val.province_name.toUpperCase()){
                    province_exist = true;
                }
            });
            if(!province_exist){
                output += '<option value="' + customer_country + '" data-foo="" selected>' + customer_country + '</option>';
            }
            $("#prov").html(output).change();

        });

        <?php if($customer->cf1 || $customer->cf2 || $customer->cf3 || $customer->cf4 || $customer->cf5 || $customer->cf6){ ?>
            $('#field_ccf_edit').show();
            $("#check_ccf_edit").iCheck("check");
        <?php } ?>
        $('#check_ccf_edit').on('ifChecked', function () {
            $('#field_ccf_edit').slideDown();
        });
        $('#check_ccf_edit').on('ifUnchecked', function () {
            $('#field_ccf_edit').slideUp();
        });  
    });

    function hideRadio(params) {
        
    }

    function setProvinsi_edit(id, text) {
        $('#modal-loading').show();
        var urlProvinsi = "<?= base_url(); ?>daerah/getKabupaten/" + text.replace(/\s+/g, '_') + "/";
        var output = "";
        var customer_city = `<?= trim($customer->city) ?>`;
        output += '<option value="" data-foo="">Choose City</option>';
        $("#city").html(output);
        $('select[name=kabupaten]').val('').change();
        var city_exist = false;
        $.getJSON(urlProvinsi, function(data) {
            $.each(data, function(key, val) {
                output += '<option value="' + val.kabupaten_name + '" data-foo="" '+ (customer_city.toUpperCase() == val.kabupaten_name.toUpperCase() ? 'selected' : '' )  +'>' + val.kabupaten_name + '</option>';
                if(customer_city.toUpperCase() == val.kabupaten_name.toUpperCase()){
                    city_exist = true;
                }
            });

            if(!city_exist){
                output += '<option value="' + customer_city + '" data-foo="" selected>' + customer_city + '</option>';
            }

            $("#city").html(output).change();
            $('#modal-loading').hide();
        });
    }

    function setKabupaten_edit(id, text) {
        $('#modal-loading').show();
        var urlProvinsi = "<?= base_url(); ?>daerah/getKecamatan/" + text.replace(/\s+/g, '_') + "/";
        var output = "";
        var customer_state = `<?= trim($customer->state) ?>`;
        output += '<option value="" data-foo="">Choose District</option>';
        $("#state").html(output);
        $('select[name=kecamatan]').val('').change();
        var state_exist = false;
        $.getJSON(urlProvinsi, function(data) {

            $.each(data, function(key, val) {
                output += '<option value="' + val.kecamatan_name + '" data-foo="" '+ (customer_state.toUpperCase() == val.kecamatan_name.toUpperCase() ? 'selected' : '' )  +'>' + val.kecamatan_name + '</option>';
                if(customer_state.toUpperCase() == val.kecamatan_name.toUpperCase()){
                    state_exist = true;
                }
            });

            if(!state_exist){
                output += '<option value="' + customer_state + '" data-foo="" selected>' + customer_state + '</option>';
            }
            $("#state").html(output).change();
            $('#modal-loading').hide();

        });
    }

</script>
<?= $modal_js ?>