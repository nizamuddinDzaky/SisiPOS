<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?= FORCAPOS_VERSION ?>"></script>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('Add_Limit'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator');
        echo form_open_multipart("system_settings/add_limit", $attrib); ?>
        <div class="modal-body">
            <!-- <p><?= lang('enter_info'); ?></p> -->

            <!-- <div class="col-md-12"> -->
            <div class="form-group">
                <?= lang("customer", "slcustomer"); ?>
                <?php
                echo form_input('company_id', '', 'id="slcustomer" data-placeholder="' . lang("select") . ' ' . lang("customer") . '" required="required" class="form-control input-tip" style="width:100%;"');
                ?>
            </div>

            <div class="form-group">
                <?= lang("No_Rek_Mandiri", "no_rek_mandiri"); ?>
                <?= form_input('NoRekMandiri', '', 'onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,\'\')" class="form-control" id="no_rek_mandiri" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("KTP", "KTP"); ?>
                <?= form_input('NoKTP', '', 'onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,\'\')" class="form-control" id="KTP" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Tenor", "tenor"); ?>
                <?= form_input('tenor', '', 'onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,\'\')" class="form-control" id="tenor" autocomplete="off" required="required"'); ?>
            </div>

            <div class="form-group">
                <?= lang("Limit", "Limit"); ?>
                <?= form_input('limit', '', 'onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,\'\')" type="number" class="form-control" id="Limit" autocomplete="off" required="required"'); ?>
            </div>

            <div class="form-group">
                <?= lang("Nama_Lengkap", "nama_lengkap"); ?>
                <?= form_input('NamaLengkap', '', 'class="form-control" id="nama_lengkap" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Jenis_Kelamin", "JenisKelamin"); ?>
                <div class="input-group col-md-12">
                    <div class="col-md-6">
                        <input type="radio" id="rd_1" name="JenisKelamin" class="custom-control-input" value="M" autocomplete="off"> &nbsp; Laki-laki
                    </div>
                    <div class="col-md-6">
                        <input type="radio" id="rd_2" name="JenisKelamin" class="custom-control-input" value="F" autocomplete="off"> &nbsp; Perempuan
                    </div>
                </div>
            </div>

            <div class="form-group">
                <?= lang("Tempat_Lahir", "TempatLahir"); ?>
                <?= form_input('TempatLahir', '', 'class="form-control" id="TempatLahir" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Tanggal_Lahir", "TanggalLahir"); ?>
                <?= form_input('TanggalLahir', '', 'class="form-control datepicker" id="TanggalLahir" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("No_HP", "NoHP"); ?>
                <?= form_input('NoHP', '', 'type="number" class="form-control" id="NoHP" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("email", "Email"); ?>
                <?= form_input('Email', '', 'class="form-control" id="Email" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("NPWP", "NPWP"); ?>
                <?= form_input('NPWP', '', 'class="form-control" id="NPWP" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Nama_Ibu_Kandung", "NamaIbuKandung"); ?>
                <?= form_input('NamaIbuKandung', '', ' class="form-control" id="NamaIbuKandung" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Masa_Berlaku", "MasaBerlakuKTP"); ?>
                <?= form_input('MasaBerlakuKTP', '', 'class="form-control datepicker" id="MasaBerlakuKTP" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Alamat_KTP", "AlamatKTP"); ?>
                <?= form_input('AlamatKTP', '', 'class="form-control" id="AlamatKTP" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Kode_Pos_KTP", "KodePosKTP"); ?>
                <?= form_input('KodePosKTP', '', 'onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,\'\')" class="form-control" id="KodePosKTP" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Provinsi_KTP", "ProvinsiKTP"); ?>
                <?= form_input('ProvinsiKTP', '', 'class="form-control" id="ProvinsiKTP" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Kabupaten_KTP", "KabupatenKotaKTP"); ?>
                <?= form_input('KabupatenKotaKTP', '', 'class="form-control" id="KabupatenKotaKTP" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Kecamatan_KTP", "KecamatanKTP"); ?>
                <?= form_input('KecamatanKTP', '', 'class="form-control" id="KecamatanKTP" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Kelurahan_KTP", "KelurahanKTP"); ?>
                <?= form_input('KelurahanKTP', '', ' class="form-control" id="KelurahanKTP" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("RT_KTP", "RTKTP"); ?>
                <?= form_input('RTKTP', '', 'onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,\'\')" class="form-control" id="RTKTP" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("RW_KTP", "RWKTP"); ?>
                <?= form_input('RWKTP', '', 'onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,\'\')" class="form-control" id="RWKTP" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Alamat_Tinggal", "AlamatTinggal"); ?>
                <?= form_input('AlamatTinggal', '', 'class="form-control" id="AlamatTinggal" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Kode_Pos_Tinggal", "KodePosTinggal"); ?>
                <?= form_input('KodeposTinggal', '', 'onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,\'\')" class="form-control" id="KodePosTinggal" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Provinsi_Tinggal", "ProvinsiTinggal"); ?>
                <?= form_input('ProvinsiTinggal', '', 'class="form-control" id="ProvinsiTinggal" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Kabupaten_Tinggal", "KabupatenKotaTinggal"); ?>
                <?= form_input('KabupatenKotaTinggal', '', ' class="form-control" id="KabupatenKotaTinggal" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Kecamatan_Tinggal", "KecamatanTinggal"); ?>
                <?= form_input('KecamatanTinggal', '', 'class="form-control" id="KecamatanTinggal" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Kelurahan_Tinggal", "KelurahanTinggal"); ?>
                <?= form_input('KelurahanTinggal', '', ' class="form-control" id="KelurahanTinggal" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("RT_Tinggal", "RTTinggal"); ?>
                <?= form_input('RTTinggal', '', 'onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,\'\')" class="form-control" id="RTTinggal" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("RW_Tinggal", "RWTinggal"); ?>
                <?= form_input('RWTinggal', '', 'onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,\'\')" class="form-control" id="RWTinggal" autocomplete="off" '); ?>
            </div>



            <!-- </div> -->

        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_limit', lang('Add_Limit'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>

<?= $modal_js ?>
<script type="text/javascript">
    $(document).ready(function() {
        $('.datepicker').datetimepicker({
            format: 'yyyy-mm-dd',
            fontAwesome: true,
            language: 'sma',
            todayBtn: 1,
            autoclose: 1,
            minView: 2,
        });

        $('#slcustomer').select2({
            minimumInputLength: 1,
            ajax: {
                url: site.base_url + "system_settings/getCustomer",
                dataType: 'json',
                quietMillis: 15,
                data: function(term, page) {
                    return {
                        term: term,
                        limit: 10
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
            formatResult: formatAddress,
        });

        function formatAddress(items) {
            if (!items.id) {
                return items.text;
            }
            return items.text + "<br><span style='font-size:12px;color:#1E1E1E'>" + items.address + "</span>";
        }

    });
</script>