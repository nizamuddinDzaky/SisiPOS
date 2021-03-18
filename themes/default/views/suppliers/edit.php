<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_supplier'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("suppliers/edit/" . $supplier->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <!--<div class="form-group">
                    <?= lang("type", "type"); ?>
                    <?php // $types = array('company' => lang('company'), 'person' => lang('person'));  echo form_dropdown('type', $types, $supplier->type, 'class="form-control select" id="type" required="required"'); ?>
                </div> -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group company">
                        <?= lang("company", "company"); ?>
                        <?php echo form_input('company', $supplier->company, 'class="form-control tip" id="company" required="required"'); ?>
                    </div>
                    <div class="form-group person">
                        <?= lang("name", "name"); ?>
                        <?php echo form_input('name', $supplier->name, 'class="form-control tip" id="name" required="required"'); ?>
                    </div>
                    <!--<div class="form-group company">
                    <?= lang("contact_person", "contact_person"); ?>
                    <?php // echo form_input('contact_person', $supplier->contact_person, 'class="form-control" id="contact_person" required="required"'); ?>
                </div> -->
                    <div class="form-group">
                        <?= lang("email_address", "email_address"); ?>
                        <input type="email" name="email" class="form-control" required="required" id="email_address"
                               value="<?= $supplier->email ?>"/>
                    </div>
                    <div class="form-group">
                        <?= lang("phone", "phone"); ?>
                        <input type="tel" name="phone" class="form-control" required="required" id="phone"
                               value="<?= $supplier->phone ?>"/>
                    </div>
                    <div class="form-group">
                        <?= lang("address", "address"); ?>
                        <?php echo form_input('address', $supplier->address, 'class="form-control" id="address" required="required"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("country", "country"); ?>
                        <?php // echo form_input('country', $supplier->country, 'class="form-control" id="country"'); ?>
                        <select name="country" id="provinsi" onchange="setProvinsi(this.value,this.options[this.selectedIndex].innerHTML)" class="form-control select">
                            <option value="<?php echo $supplier->country;?>"><?php echo $supplier->country;?></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <?= lang("city", "city"); ?>
                        <?php // echo form_input('city', $supplier->city, 'class="form-control" id="city" required="required"'); ?>
                        <select name="city" id="kabupaten" onchange="setKabupaten(this.value,this.options[this.selectedIndex].innerHTML)" class="form-control select" required="required">
                            <option value="<?php echo $supplier->city?>"><?php echo $supplier->city?></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <?= lang("state", "state"); ?>
                        <?php // echo form_input('state', $supplier->state, 'class="form-control" id="state"'); ?>
                        <select name="state" id="kecamatan" onchange="setKecamatan(this.value,this.options[this.selectedIndex].innerHTML)" class="form-control select">
                                <option value="<?php echo $supplier->state?>"><?php echo $supplier->state?></option>
                        </select>
                    </div>

                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("postal_code", "postal_code"); ?>
                        <?php echo form_input('postal_code', $supplier->postal_code, 'class="form-control" id="postal_code"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("vat_no", "vat_no"); ?>
                        <?php echo form_input('vat_no', $supplier->vat_no, 'class="form-control" id="vat_no"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("scf1", "cf1"); ?>
                        <?php echo form_input('cf1', $supplier->cf1, 'class="form-control" id="cf1"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("scf2", "cf2"); ?>
                        <?php echo form_input('cf2', $supplier->cf2, 'class="form-control" id="cf2"'); ?>

                    </div>
                    <div class="form-group">
                        <?= lang("scf3", "cf3"); ?>
                        <?php echo form_input('cf3', $supplier->cf3, 'class="form-control" id="cf3"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("scf4", "cf4"); ?>
                        <?php echo form_input('cf4', $supplier->cf4, 'class="form-control" id="cf4"'); ?>

                    </div>
                    <div class="form-group">
                        <?= lang("scf5", "cf5"); ?>
                        <?php echo form_input('cf5', $supplier->cf5, 'class="form-control" id="cf5"'); ?>

                    </div>
                    <div class="form-group">
                        <?= lang("scf6", "cf6"); ?>
                        <?php echo form_input('cf6', $supplier->cf6, 'class="form-control" id="cf6"'); ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("logo", "logo"); ?>
                        <input id="logo" type="file" data-browse-label="<?= lang('browse'); ?>" name="logo" data-show-upload="false"
                               data-show-preview="false" accept="image/*" class="form-control file">
                    </div>
                </div>

                <div class="col-md-6">
                    <div id="logo-con" class="text-center"><img
                            src="<?= base_url('assets/uploads/avatars/thumbs/' . $supplier->logo) ?>" alt=""></div>
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_supplier', lang('edit_supplier'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/daerah.js?v=<?=FORCAPOS_VERSION?>"></script>
<script type="text/javascript">
    $.getJSON('<?php echo base_url(); ?>daerah/getProvinsi', function(data) {
        var output = "";
        output += '<option value="<?php echo $supplier->country?>" data-foo=""><?php echo $supplier->country?></option>';
        $.each(data, function(key, val) {
            output += '<option value="' + val.province_name + '" data-foo="">' + val.province_name + '</option>';
        });
        $("#provinsi").html(output);
    });
//if(localStorage.getItem('flag_editSuppliers')){
//    localStorage.removeItem('flag_editSuppliers');
//}
</script>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?=FORCAPOS_VERSION?>"></script>
<?= $modal_js ?>
