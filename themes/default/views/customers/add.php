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
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_customer'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'add-customer-form');
        echo form_open_multipart("customers/add", $attrib); ?>
        <input id="latitude" name="latitude" type="hidden" />
        <input id="longitude" name="longitude" type="hidden" />
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
                        echo form_dropdown('customer_group', $cgs,'', 'class="form-control select" id="customer_group" style="width:100%;" required="required"');
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
                        echo form_dropdown('price_group', $pgs, $Settings->price_group, 'class="form-control select" id="price_group" style="width:100%;"');
                        ?>
                    </div>
                </div>
            </div>

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
                        <input type="email" name="email" class="form-control" id="email_address" />
                    </div>
                    <div class="form-group">
                        <?= lang("phone", "phone"); ?>
                        <input type="tel" name="phone" class="form-control" required="required" id="phone" />
                    </div>
                    <div class="form-group">
                        <?= lang("address", "address"); ?>
                        <?php echo form_input('address', '', 'class="form-control" id="address" required="required"'); ?>
                    </div>
                    
                    <?php if ($Principal) { ?>
                        <div class="form-group">
                            <?= lang('Distributor', 'Distributor'); ?> *
                            <div class="controls">
                                <?php echo form_input('distributor', (isset($_POST['distributor']) ? $_POST['distributor'] : 1), 'id="select_distributor_add" data-placeholder="' . lang("select") . ' ' . lang("Distributor") . '" required="required" class="form-control input-tip" style="width:100%; margin-bottom:20px;"'); ?>
                            </div>
                        </div>
                    <?php }?>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="sales_person"><?php echo $this->lang->line("sales_person"); ?></label>
                        <?php
                        $sp[''] = lang('select') . ' ' . lang('sales_person');
                        foreach ($sales_persons as $sales_person) {
                            $sp[$sales_person->id] = $sales_person->reference_no . " ~ " . $sales_person->name;
                        }
                        echo form_dropdown('sales_person', $sp,'', 'class="form-control select" id="sales_person" style="width:100%;"');
                        ?>
                    </div>
                    <div class="form-group">
                        <?= lang("postal_code", "postal_code"); ?>
                        <?php echo form_input('postal_code', '', 'class="form-control" id="postal_code" '); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("vat_no", "vat_no"); ?>
                        <?php echo form_input('vat_no', '', 'class="form-control" id="vat_no"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("provinsi", "prov_add"); ?>
                        <div class="controls">
                            <select name="provinsi" id="prov_add" onchange="setProvinsi_add(this.value,this.options[this.selectedIndex].innerHTML)" class="form-control select" required>
                                <option value=""><?= lang("choose_province"); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <?= lang("city", "city_add"); ?>
                        <div class="controls">
                            <select name="kabupaten" id="city_add" onchange="setKabupaten_add(this.value,this.options[this.selectedIndex].innerHTML)" class="form-control select" required>
                                <option value=""><?= lang("choose_city"); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <?= lang("state", "state_add"); ?>
                        <div class="controls">
                            <select name="kecamatan" id="state_add" class="form-control select" required>
                                <option value=""><?= lang("choose_district"); ?></option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <input type="checkbox" class="checkbox" value="1" name="is_active" id="is_active" <?= $this->input->post() ? ($this->input->post('is_active') ? 'checked="checked"' : '') : 'checked="checked"'; ?>>
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
                    <div class="text-center" id="logo-con">
                        <img id="preview-img" src="<?= base_url('assets/uploads/logos/logo.png') ?>" alt="<?= lang('your_logo') ?>" style="max-width: 150px">
                    </div>
                </div>
            </div>
            
            <?php if($Principal || $Owner){ ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <input type="checkbox" class="checkbox" value="1" name="custom_fields" id="check_ccf">
                        <label for="check_ccf" class="padding05"><?= lang('custom_fields') ?></label>
                    </div>
                </div>
            </div>

            <div class="row" style="display: none!important;" id="field_ccf">
                <div class="col-md-6">
                    <div class="form-group">
                    <?= lang("ccf1", "cf1"); ?>
                        <?php echo form_input('cf1', '', 'class="form-control" id="cf1"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("ccf2", "cf2"); ?>
                        <?php echo form_input('cf2', '', 'class="form-control" id="cf2"'); ?>
                    </div>        
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("ccf3", "cf3"); ?>
                        <?php echo form_input('cf3', '', 'class="form-control" id="cf3"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("ccf4", "cf4"); ?>
                        <?php echo form_input('cf4', '', 'class="form-control" id="cf4"'); ?>
                    </div>      
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("ccf5", "cf5"); ?>
                        <?php echo form_input('cf5', '', 'class="form-control" id="cf5"'); ?>
                    </div>
                </div>
                <div class="col-md-6">            
                    <div class="form-group">
                        <?= lang("ccf6", "cf6"); ?>
                        <?php echo form_input('cf6', '', 'class="form-control" id="cf6"'); ?>
                    </div>
                </div>
            </div>
            <?php } ?>

            <label><?= lang("warehouses"); ?></label>
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
                        <?php if($Principal || $Owner){ ?>
                            <td colspan="3" style="text-align:center;"><?= lang("please_select_a_distributor_first"); ?></td>
                        <?php } else {?>
                            <?php foreach($warehouses as $row){ ?>
                                <tr>
                                    <td style="text-align:center;"><input type="checkbox" id="warehouse_<?= $row->id ?>" name="warehouses[]" value="<?= $row->id ?>" checked></td>
                                    <td><?= $row->code; ?></td>
                                    <td><?= $row->name; ?></td>
                                    <td style="text-align:center;"><input type="radio" id="default_<?= $row->id ?>" name="default" value="<?= $row->id ?>" checked></td>
                                </tr>
                            <?php } ?>
                        <?php }?>
                    </tbody>
                </table>
            </div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_customer', lang('add_customer'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript">
    function suggestionsBillerAktif() {
        var url = "<?php echo site_url() . 'customers/suggestionsBillerAktif' ?>";
        $('#select_distributor_add').val(null);
        $('#select_distributor_add').select2({
            minimumInputLength: 1,
            ajax: {
                url: url,
                dataType: 'json',
                quietMillis: 15,
                data: function(term, page) {
                    return {
                        term: term,
                        limit: 20
                    };
                },
                results: function(data, page) {
                    if (data.results != null) {
                        return {
                            results: data.results
                        };
                    } else {
                        return {
                            results: [{
                                id: '',
                                text: 'No Match Found'
                            }]
                        };
                    }
                }
            },
            formatResult: formatAddress
        });
    }

    function formatAddress(items) {
        if (!items.id) {
            return items.text;
        }
        return items.text + "<br><span style='font-size:12px;color:#1E1E1E'>" + items.code + "</span>";
    }

    $(document).ready(function(e) {
        suggestionsBillerAktif();

        $('#add-customer-form').bootstrapValidator({
            feedbackIcons: {
                valid: 'fa fa-check',
                invalid: 'fa fa-times',
                validating: 'fa fa-refresh'
            },
            excluded: [':disabled']
        });
        $('select.select').select2({
            minimumResultsForSearch: 7
        });

        $("#customer_group").val('<?= $default_customer_groups->id?>').trigger('change');

        $.ajax({
            type: 'get',
            url: site.base_url + 'welcome/experience_guide',
            dataType: "json",
            success: function(data) {
                if (!data["customers-add"]) {
                    hopscotch.startTour(tour);
                }
            }
        });
    });

    $("#logo").change(function() {
        if (this.files && this.files[0]) {
            var reader = new FileReader();

            reader.onload = function(e) {
                $('#preview-img').attr('src', e.target.result);
            };

            reader.readAsDataURL(this.files[0]);
        }
    });

    var tour = {
        id: "guide-customers-add",
        onClose: function() {
            complete_guide('customers-add');
        },
        onEnd: function() {
            complete_guide('customers-add');
        },
        steps: [{
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
<script type="text/javascript">
    $(document).ready(function() {
        <?php foreach($warehouses as $row){ ?>
            $('#warehouse_<?= $row->id ?>').on('ifChecked', function () {
                $('#default_<?= $row->id ?>').parent().show();
            });
            $('#warehouse_<?= $row->id ?>').on('ifUnchecked', function () {
                $("#default_<?= $row->id ?>").iCheck("uncheck");
                $('#default_<?= $row->id ?>').parent().hide();
            });
        <?php } ?>

        $.getJSON('<?= base_url(); ?>daerah/getProvinsi', function(data) {
            var output = "";
            output += '<option value="" data-foo="">Choose Province</option>';
            $.each(data, function(key, val) {
                output += '<option value="' + val.province_name + '" data-foo="">' + val.province_name + '</option>';
            });
            $("#prov_add").html(output);

        });

        $('#check_ccf').on('ifChecked', function () {
            $('#field_ccf').slideDown();
        });
        $('#check_ccf').on('ifUnchecked', function () {
            $('#field_ccf').slideUp();
        });  
    });

    function setProvinsi_add(id, text) {
        $('#modal-loading').show();
        var urlProvinsi = "<?= base_url(); ?>daerah/getKabupaten/" + text.replace(/\s+/g, '_') + "/";
        var output = "";
        output += '<option value="" data-foo="">Choose City</option>';
        $("#city_add").html(output);
        $('select[name=kabupaten]').val('').change();
        $.getJSON(urlProvinsi, function(data) {
            $.each(data, function(key, val) {
                output += '<option value="' + val.kabupaten_name + '" data-foo="">' + val.kabupaten_name + '</option>';
            });
            $("#city_add").html(output);
            $('#modal-loading').hide();
        });
    }

    function setKabupaten_add(id, text) {
        $('#modal-loading').show();
        var urlProvinsi = "<?= base_url(); ?>daerah/getKecamatan/" + text.replace(/\s+/g, '_') + "/";
        var output = "";
        output += '<option value="" data-foo="">Choose District</option>';
        $("#state_add").html(output);
        $('select[name=kecamatan]').val('').change();
        $.getJSON(urlProvinsi, function(data) {
            $.each(data, function(key, val) {
                output += '<option value="' + val.kecamatan_name + '" data-foo="">' + val.kecamatan_name + '</option>';
            });
            $("#state_add").html(output);
            $('#modal-loading').hide();
        });
    }

    $('#select_distributor_add').change(function() {
        setWarehouse(this.value);
    }).change();
    function setWarehouse(distributor_id) {
        $('#modal-loading').show();
        var urlGetWarehouse = "<?= base_url(); ?>customers/getWarehousesByBiller/" + distributor_id + "/";
        var output = "<td colspan='3' style='text-align:center; font-size:17px;'><?= lang("please_select_a_distributor_first") ?></td>";
        $("#warehouse_list").html(output);
        $.getJSON(urlGetWarehouse, function(data) {
            output = "";
            $.each(data, function(key, val) {
                output += "<tr>   <td style='text-align:center;'><input type='checkbox' class='icheckbox_square-blue' name='warehouses[]' value='"+val.id+"' checked></td><td>"+val.code+"</td><td>"+val.name+"</td><td style='text-align:center;'><input type='radio' class='iradio_square-blue' id='default_"+val.id+"' name='default' value='"+val.id+"' checked></td>   </tr>";
            });
            $("#warehouse_list").html(output);
            $('#modal-loading').hide();
        });
    }
</script>
<?= $modal_js ?>