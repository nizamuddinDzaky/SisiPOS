<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?= FORCAPOS_VERSION ?>"></script>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('Edit_Limit', 'Edit Limit'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("system_settings/edit_limit/" . $id, $attrib); ?>
        <div class="modal-body">
            <!-- <p><?= lang('enter_info'); ?></p> -->

            <input type="hidden" value="<?= $limit->id ?>" name="loanID">

            <div class="form-group">
                <?= lang("customer", "slcustomer"); ?> <span></span>
                <?php
                echo form_input('company_id', $limit->company . '  (' . $limit->name . ')', 'id="slcustomer" data-placeholder="' . lang("select") . ' ' . lang("customer") . '" required="required" class="form-control input-tip" style="width:100%;" disabled readonly');
                ?>
            </div>

            <div class="form-group">
                <?= lang("No_Rek_Mandiri", "no_rek_mandiri"); ?>
                <?= form_input('NoRekMandiri', $limit->NoRekMandiri, 'onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,\'\')" class="form-control" id="no_rek_mandiri" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("KTP", "KTP"); ?>
                <?= form_input('NoKTP', $limit->NoKTP, 'onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,\'\')" class="form-control" id="KTP" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Tenor", "tenor"); ?>
                <?= form_input('tenor', (float)$limit->Tenor, 'onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,\'\')" class="form-control" id="tenor" autocomplete="off" required="required" ' . $disable . ' '); ?>
            </div>

            <div class="form-group">
                <?= lang("Limit", "Limit"); ?>
                <?= form_input('limit', (float)$limit->Limit, 'onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,\'\')" class="form-control" id="Limit" autocomplete="off" required="required" ' . $disable . ' '); ?>
            </div>

            <div class="form-group">
                <?= lang("Nama_Lengkap", "nama_lengkap"); ?>
                <?= form_input('NamaLengkap', $limit->NamaLengkap, 'class="form-control" id="nama_lengkap" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Jenis_Kelamin", "JenisKelamin"); ?>
                <div class="input-group col-md-12">
                    <div class="col-md-6">
                        <input type="radio" id="rd_1" name="JenisKelamin" class="custom-control-input " value="M" <?= ($limit->JenisKelamin == 'M' ? 'checked' : '') ?> autocomplete="off"> &nbsp; Laki-laki
                    </div>
                    <div class="col-md-6">
                        <input type="radio" id="rd_2" name="JenisKelamin" class="custom-control-input " value="F" <?= ($limit->JenisKelamin == 'F' ? 'checked' : '') ?> autocomplete="off"> &nbsp; Perempuan
                    </div>
                </div>
            </div>

            <div class="form-group">
                <?= lang("Tempat_Lahir", "TempatLahir"); ?>
                <?= form_input('TempatLahir', $limit->TempatLahir, 'class="form-control" id="TempatLahir" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Tanggal_Lahir", "TanggalLahir"); ?>
                <?= form_input('TanggalLahir', $limit->TanggalLahir, 'class="form-control datepicker" id="TanggalLahir" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("No_HP", "NoHP"); ?>
                <?= form_input('NoHP', $limit->NoHP, 'type="number" class="form-control" id="NoHP" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("email", "Email"); ?>
                <?= form_input('Email', $limit->Email, 'class="form-control" id="Email" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("NPWP", "NPWP"); ?>
                <?= form_input('NPWP', $limit->NPWP, 'class="form-control" id="NPWP" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Nama_Ibu_Kandung", "NamaIbuKandung"); ?>
                <?= form_input('NamaIbuKandung', $limit->NamaIbuKandung, ' class="form-control" id="NamaIbuKandung" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Masa_Berlaku", "MasaBerlakuKTP"); ?>
                <?= form_input('MasaBerlakuKTP', $limit->MasaBerlakuKTP, ' class="form-control" id="MasaBerlakuKTP" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Alamat_KTP", "AlamatKTP"); ?>
                <?= form_input('AlamatKTP', $limit->AlamatKTP, 'class="form-control" id="AlamatKTP" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Kode_Pos_KTP", "KodePosKTP"); ?>
                <?= form_input('KodePosKTP', $limit->KodePosKTP, 'onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,\'\')"  class="form-control" id="KodePosKTP" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Provinsi_KTP", "ProvinsiKTP"); ?>
                <?= form_input('ProvinsiKTP', $limit->ProvinsiKTP, 'class="form-control" id="ProvinsiKTP" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Kabupaten_KTP", "KabupatenKotaKTP"); ?>
                <?= form_input('KabupatenKotaKTP', $limit->KabupatenKotaKTP, ' class="form-control" id="KabupatenKotaKTP" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Kecamatan_KTP", "KecamatanKTP"); ?>
                <?= form_input('KecamatanKTP', $limit->KecamatanKTP, 'class="form-control" id="KecamatanKTP" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Kelurahan_KTP", "KelurahanKTP"); ?>
                <?= form_input('KelurahanKTP', $limit->KelurahanKTP, ' class="form-control" id="KelurahanKTP" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("RT_KTP", "RTKTP"); ?>
                <?= form_input('RTKTP', $limit->RTKTP, 'onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,\'\')" class="form-control" id="RTKTP" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("RW_KTP", "RWKTP"); ?>
                <?= form_input('RWKTP', $limit->RWKTP, 'onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,\'\')"  class="form-control" id="RWKTP" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Alamat_Tinggal", "AlamatTinggal"); ?>
                <?= form_input('AlamatTinggal', $limit->AlamatTinggal, 'class="form-control" id="AlamatTinggal" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Kode_Pos_Tinggal", "KodePosTinggal"); ?>
                <?= form_input('KodeposTinggal', $limit->KodeposTinggal, 'onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,\'\')"  class="form-control" id="KodePosTinggal" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Provinsi_Tinggal", "ProvinsiTinggal"); ?>
                <?= form_input('ProvinsiTinggal', $limit->ProvinsiTinggal, 'class="form-control" id="ProvinsiTinggal" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Kabupaten_Tinggal", "KabupatenKotaTinggal"); ?>
                <?= form_input('KabupatenKotaTinggal', $limit->KabupatenKotaTinggal, ' class="form-control" id="KabupatenKotaTinggal" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Kecamatan_Tinggal", "KecamatanTinggal"); ?>
                <?= form_input('KecamatanTinggal', $limit->KecamatanTinggal, 'class="form-control" id="KecamatanTinggal" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Kelurahan_Tinggal", "KelurahanTinggal"); ?>
                <?= form_input('KelurahanTinggal', $limit->KelurahanTinggal, ' class="form-control" id="KelurahanTinggal" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("RT_Tinggal", "RTTinggal"); ?>
                <?= form_input('RTTinggal', $limit->RTTinggal, 'onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,\'\')" class="form-control" id="RTTinggal" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("RW_Tinggal", "RWTinggal"); ?>
                <?= form_input('RWTinggal', $limit->RWTinggal, 'onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,\'\')"  class="form-control" id="RWTinggal" autocomplete="off" '); ?>
            </div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_limit', lang('Edit Limit'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
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

    });
</script>