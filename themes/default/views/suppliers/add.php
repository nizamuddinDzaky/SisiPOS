<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
  $(document).ready(function() {
          $('input.number-only').bind('keypress', function (e) {
                    return !(e.which != 8 && e.which != 0 &&
                    (e.which < 48 || e.which > 57) && e.which != 46);
		}); 
        $.getJSON('<?php echo base_url(); ?>daerah/getProvinsi', function(data) {
            var output = "";
             output += '<option value="" data-foo="">Choose Province</option>';
            $.each(data, function(key, val) {
               
                output += '<option value="' + val.province_name + '" data-foo="">' + val.province_name + '</option>';
            });
            $("#provinsi").html(output);

        });
 });
</script>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_supplier'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("suppliers/add", $attrib); ?>
        <div class="modal-body ui-front">
            <p><?= lang('enter_info'); ?></p>

            <!--<div class="form-group">
                    <?= lang("type", "type"); ?>
                    <?php $types = array('company' => lang('company'), 'person' => lang('person'));
            echo form_dropdown('type', $types, '', 'class="form-control select" id="type" required="required"'); ?>
                </div> -->

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group company">
                        <?= lang("company", "company"); ?>
                        <?php echo form_input('company', '', 'class="form-control tip" id="company" data-bv-notempty="true"'); ?>
                    </div>
                    <div class="form-group person">
                        <?= lang("name", "name"); ?>
                        <?php echo form_input('name', '', 'class="form-control tip" id="name" data-bv-notempty="true"'); ?>
                    </div>
                    <!--<div class="form-group company">
                    <?= lang("contact_person", "contact_person"); ?>
                    <?php echo form_input('contact_person', '', 'class="form-control" id="contact_person" data-bv-notempty="true"'); ?>
                </div>-->
                    <div class="form-group">
                        <?= lang("email_address", "email_address"); ?>
                        <input type="email" name="email" class="form-control" required="required" id="email_address"/>
                    </div>
                    <div class="form-group">
                        <?= lang("phone", "phone"); ?>
                        <input type="tel" name="phone" class="form-control" required="required" id="phone"/>
                    </div>
                    <div class="form-group">
                        <?= lang("address", "address"); ?>
                        <?php echo form_input('address', '', 'class="form-control" id="address" required="required"'); ?>
                    </div>
                    <div class="form-group">
                            <?= lang("Province", "provinsi"); ?>
                            <div class="controls">
                                    <select name="provinsi" id="provinsi" onchange="setProvinsi(this.value,this.options[this.selectedIndex].innerHTML)" class="form-control select" required>
                                            <option value="">Choose Province</option>
                                    </select>
                            </div>
                    </div>
                    <div class="form-group">
                            <?= lang("city", "kabupaten"); ?>
                            <div class="controls">
                                    <select name="kabupaten" id="kabupaten" onchange="setKabupaten(this.value,this.options[this.selectedIndex].innerHTML)" class="form-control select" required>
                                            <option value="">Choose City</option>
                                    </select>
                            </div>
                    </div>

                    <div class="form-group">
                            <?= lang("state", "kecamatan"); ?>
                            <div class="controls">
                            <select name="kecamatan" id="kecamatan" onchange="setKecamatan(this.value,this.options[this.selectedIndex].innerHTML)" class="form-control select">
                                    <option value="">Choose District</option>
                            </select>
                            </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("postal_code", "postal_code"); ?>
                        <?php echo form_input('postal_code', '', 'class="form-control" id="postal_code"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("vat_no", "vat_no"); ?>
                        <?php echo form_input('vat_no', '', 'class="form-control" id="vat_no"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("scf1", "cf1"); ?>
                        <?php echo form_input('cf1', '', 'class="form-control" id="cf1"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("scf2", "cf2"); ?>
                        <?php echo form_input('cf2', '', 'class="form-control" id="cf2"'); ?>

                    </div>
                    <div class="form-group">
                        <?= lang("scf3", "cf3"); ?>
                        <?php echo form_input('cf3', '', 'class="form-control" id="cf3"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("scf4", "cf4"); ?>
                        <?php echo form_input('cf4', '', 'class="form-control" id="cf4"'); ?>

                    </div>
                    <div class="form-group">
                        <?= lang("scf5", "cf5"); ?>
                        <?php echo form_input('cf5', '', 'class="form-control" id="cf5"'); ?>

                    </div>
                    <div class="form-group">
                        <?= lang("scf6", "cf6"); ?>
                        <?php echo form_input('cf6', '', 'class="form-control" id="cf6"'); ?>
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
                    <div class="text-center" id="logo-con">
                        <img id="preview-img" src="#" alt="<?=lang('your_logo')?>" style="max-width: 150px">
                    </div>
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_supplier', lang('add_supplier'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/daerah.js?v=<?=FORCAPOS_VERSION?>"></script>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?=FORCAPOS_VERSION?>"></script>
<script type="text/javascript">
    $("#logo").change(function(){
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function (e) {
                $('#preview-img').attr('src', e.target.result);
            };
            
            reader.readAsDataURL(this.files[0]);
        }
    });
    
</script>
<?= $modal_js ?>
