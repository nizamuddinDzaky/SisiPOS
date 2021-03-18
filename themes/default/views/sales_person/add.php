<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_sales_person'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'add-sales_person-form');
        echo form_open_multipart("sales_person/add", $attrib); ?>
        <input id="latitude" name="latitude" type="hidden"/>
        <input id="longitude" name="longitude" type="hidden"/>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <div class="col-md-6">
                    
                    <div class="form-group person">
                        <?= lang("name", "name"); ?>
                        <?php echo form_input('name', '', 'class="form-control tip" id="name" data-bv-notempty="true" required="required"'); ?>
                    </div>
                    <!--<div class="form-group company">
                    <?= lang("contact_person", "contact_person"); ?>
                    <?php echo form_input('contact_person', '', 'class="form-control" id="contact_person" data-bv-notempty="true"'); ?>
                </div>-->
                    <div class="form-group">
                        <?= lang("email_address", "email_address"); ?>
                        <input type="email" name="email" class="form-control" id="email_address" required="required"/>
                    </div>
                    <div class="form-group">
                        <?= lang("phone", "phone"); ?>
                        <input type="tel" name="phone" class="form-control" required="required" id="phone"/>
                    </div>
                    <div class="form-group">
                        <?= lang("postal_code", "postal_code"); ?>
                        <?php echo form_input('postal_code', '', 'class="form-control" id="postal_code" '); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("vat_no", "vat_no"); ?>
                        <?php echo form_input('vat_no', '', 'class="form-control" id="vat_no"'); ?>
                    </div>
                    
                    

                </div>
                <div class="col-md-6">
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
                        <select name="kecamatan" id="kecamatan" onchange="setKecamatan(this.value,this.options[this.selectedIndex].innerHTML)" class="form-control select" required>
                                <option value="">Choose District</option>
                        </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <?= lang("referral_code", "referral_code"); ?>
                        <div class="input-group">
                            <?php echo form_input('reference_no', $reference_no, 'class="form-control" id="reference_no" required="required" readonly="true"'); ?>
                            <div class="input-group-addon no-print" style="padding: 2px 8px; border-left: 0;">
                                <a href="javascript:void(0)" id="toogle-sales-person-rc" class="external" tabindex="-1">
                                    <i class="fa fa-pencil" style="font-size: 1.2em;"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <input type="checkbox" class="checkbox" value="1" name="is_active" checked>
                        <label for="is_active" class="padding05"><?= lang('active') ?></label>
                    </div>
                    <div class="form-group">
                        <?= lang("logo", "logo"); ?>
                        <input id="logo" type="file" data-browse-label="<?= lang('browse'); ?>" name="photo" data-show-upload="false"
                               data-show-preview="false" accept="image/*" class="form-control file">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_sales_person', lang('add_sales_person'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/daerah.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript">
    $(document).ready(function (e) {
        $("#toogle-sales-person-rc").click(function(){

            var attr = $("#reference_no").attr('readonly');
            
            if (typeof attr !== typeof undefined && attr !== false) {
                $("#reference_no").removeAttr('readonly');
            }else{
                $("#reference_no").attr('readonly', 'readonly');
            }
        });
        
        // initMap();
         $.getJSON('<?php echo base_url(); ?>daerah/getProvinsi', function(data) {
            var output = "";
             output += '<option value="" data-foo="">Choose Province</option>';
            $.each(data, function(key, val) {
               
                output += '<option value="' + val.province_name + '" data-foo="">' + val.province_name + '</option>';
            });
            $("#provinsi").html(output);

        });
        $('#add-customer-form').bootstrapValidator({
            feedbackIcons: {
                valid: 'fa fa-check',
                invalid: 'fa fa-times',
                validating: 'fa fa-refresh'
            }, excluded: [':disabled']
        });
        $('select.select').select2({minimumResultsForSearch: 7});
        
        $.ajax({
            type: 'get',
            url: site.base_url+'welcome/experience_guide',
            dataType: "json",
            success: function (data) {
                if(!data["customers-add"]){
                    hopscotch.startTour(tour);
                }
            }
        });
    });
    
    $("#logo").change(function(){
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function (e) {
                $('#preview-img').attr('src', e.target.result);
            };
            
            reader.readAsDataURL(this.files[0]);
        }
    });
    
    var tour = {
    id: "guide-customers-add",
    onClose: function(){
        complete_guide('customers-add');
    },
    onEnd:function(){
        complete_guide('customers-add');
    },   
    steps: [
        {
            title: "Nama Perusahaan/Toko",
            content: "Silahkan nama perusahaan/toko",
            target: "company",
            placement: "top"
        },
        {
            title: "Nama Pelanggan",
            content: "Silahkan isi nama pelanggan",
            target: "name",
            placement: "top"
        },
        {
            title: "Alamat E-mail",
            content: "Silahkan isi alamat e-mail",
            target: "email_address",
            placement: "top"
        },
        {
            title: "Telepon",
            content: "Silahkan isi nomor telepon yang dapat dihubungi",
            target: "phone",
            placement: "top"
        },
        {
            title: "Alamat",
            content: "Silahkan isi alamat pelanggan",
            target: "address",
            placement: "top"
        },
        {
            title: "Provinsi",
            content: "Silahkan pilih provinsi",
            target: "s2id_provinsi",
            placement: "top"
        },
        {
            title: "Kota",
            content: "Silahkan pilih kota",
            target: "s2id_kabupaten",
            placement: "top"
        },
        {
            title: "Kecamatan",
            content: "Silahkan pilih kecamatan",
            target: "s2id_kecamatan",
            placement: "top"
        },
        {
            title: "Kode Customer",
            content: "Silahkan isi kode customer",
            target: "cf1",
            placement: "top"
        },
        {
            title: "Kode SAP",
            content: "Silahkan isi kode SAP",
            target: "cf2",
            placement: "top"
        },
        {
            title: "Kode ForcaERP",
            content: "Silahkan isi kode ForcaERP",
            target: "cf3",
            placement: "top"
        }
        ]
    };
    
</script>
<?= $modal_js ?>