<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?= FORCAPOS_VERSION ?>"></script>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('Edit_Kur_Btn'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("system_settings/edit_kur_btn/" . $id, $attrib); ?>
        <div class="modal-body">
            <!-- <p><?= lang('enter_info'); ?></p> -->

            <input type="hidden" value="<?= $pengajuan->id ?>" name="loanID">

            <div class="form-group">
                <?= lang("customer", "slcustomer"); ?> <span></span>
                <?php
                echo form_input('company_id', $pengajuan->company . '  (' . $pengajuan->name . ')', 'id="slcustomer" data-placeholder="' . lang("select") . ' ' . lang("customer") . '" required="required" class="form-control input-tip" style="width:100%;" disabled readonly');
                ?>
            </div>

            <div class="form-group">
                <?= lang("KTP", "ktp"); ?>
                <?= form_input('ktp', $pengajuan->ktp, 'onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,\'\')" class="form-control" id="ktp" autocomplete="off" required="required" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Jangka_Waktu", "jangka_waktu"); ?>
                <?= form_input('jangka_waktu', (float)$pengajuan->jangka_waktu, 'onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,\'\')" class="form-control" id="jangka_waktu" autocomplete="off" ' . $disable . ' '); ?>
            </div>

            <div class="form-group">
                <?= lang("Plafon_Kur", "plafon_kur"); ?>
                <?= form_input('plafon_kur', (float)$pengajuan->plafon_kur, 'onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,\'\')" class="form-control" id="plafon_kur" autocomplete="off" ' . $disable . ' '); ?>
            </div>

            <div class="form-group">
                <?= lang("Nama_Lengkap", "nama"); ?>
                <?= form_input('nama', $pengajuan->nama, 'class="form-control" id="nama" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Jenis_Kelamin", "jenis_kelamin"); ?>
                <div class="input-group col-md-12">
                    <div class="col-md-6">
                        <input type="radio" id="rd_1" name="jenis_kelamin" class="custom-control-input " value="1" <?= ($pengajuan->jenis_kelamin == '1' ? 'checked' : '') ?> autocomplete="off"> &nbsp; Laki-laki
                    </div>
                    <div class="col-md-6">
                        <input type="radio" id="rd_2" name="jenis_kelamin" class="custom-control-input " value="0" <?= ($pengajuan->jenis_kelamin == '0' ? 'checked' : '') ?> autocomplete="off"> &nbsp; Perempuan
                    </div>
                </div>
            </div>

            <div class="form-group">
                <?= lang("Tempat_Lahir", "tempat_lahir"); ?>
                <?= form_input('tempat_lahir', $pengajuan->tempat_lahir, 'class="form-control" id="tempat_lahir" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Tanggal_Lahir", "tanggal_lahir"); ?>
                <?= form_input('tanggal_lahir', $pengajuan->tanggal_lahir, 'class="form-control datepicker" id="tanggal_lahir" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("No_HP", "hp"); ?>
                <?= form_input('hp', $pengajuan->hp, 'type="number" class="form-control" id="hp" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("email", "email"); ?>
                <?= form_input('email', $pengajuan->email, 'class="form-control" id="email" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Alamat_Tempat_Tinggal", "alamat_tt"); ?>
                <?= form_input('alamat_tt', $pengajuan->alamat_tt, 'class="form-control" id="alamat_tt" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Kode_Pos", "kodepos_tt"); ?>
                <?= form_input('kodepos_tt', $pengajuan->kodepos_tt, 'onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,\'\')"  class="form-control" id="kodepos_tt" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Provinsi", "provinsi_tt"); ?>
                <?= form_input('provinsi_tt', $pengajuan->provinsi_tt, 'class="form-control" id="provinsi_tt" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Kabupaten_Kota", "kota_tt"); ?>
                <?= form_input('kota_tt', $pengajuan->kota_tt, ' class="form-control" id="kota_tt" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Kecamatan", "kecamatan_tt"); ?>
                <?= form_input('kecamatan_tt', $pengajuan->kecamatan_tt, 'class="form-control" id="kecamatan_tt" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Kelurahan", "kelurahan_tt"); ?>
                <?= form_input('kelurahan_tt', $pengajuan->kelurahan_tt, ' class="form-control" id="kelurahan_tt" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("RT", "rt_tt"); ?>
                <?= form_input('rt_tt', $pengajuan->rt_tt, 'onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,\'\')" class="form-control" id="rt_tt" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("RW", "rw_tt"); ?>
                <?= form_input('rw_tt', $pengajuan->rw_tt, 'onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,\'\')"  class="form-control" id="rw_tt" autocomplete="off" '); ?>
            </div>


            <div class="form-group">
                <?= lang("Alamat_Usaha", "alamat_u"); ?>
                <?= form_input('alamat_u', $pengajuan->alamat_u, 'class="form-control" id="alamat_u" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Kode_Pos_Tempat_Usaha", "kodepos_u"); ?>
                <?= form_input('kodepos_u', $pengajuan->kodepos_u, 'onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,\'\')"  class="form-control" id="kodepos_u" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Provinsi_Tempat_Usaha", "provinsi_u"); ?>
                <?= form_input('provinsi_u', $pengajuan->provinsi_u, 'class="form-control" id="provinsi_u" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Kota_Tempat_Usaha", "kota_u"); ?>
                <?= form_input('kota_u', $pengajuan->kota_u, ' class="form-control" id="kota_u" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Kecamatan_Tempat_Usaha", "kecamatan_u"); ?>
                <?= form_input('kecamatan_u', $pengajuan->kecamatan_u, 'class="form-control" id="kecamatan_u" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("Kelurahan_Tempat_Usaha", "kelurahan_u"); ?>
                <?= form_input('kelurahan_u', $pengajuan->kelurahan_u, ' class="form-control" id="kelurahan_u" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("RT_Tempat_Usaha", "rt_u"); ?>
                <?= form_input('rt_u', $pengajuan->rt_u, 'onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,\'\')" class="form-control" id="rt_u" autocomplete="off" '); ?>
            </div>

            <div class="form-group">
                <?= lang("RW_Tempat_Usaha", "rw_u"); ?>
                <?= form_input('rw_u', $pengajuan->rw_u, 'onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,\'\')"  class="form-control" id="rw_u" autocomplete="off" '); ?>
            </div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_kur_btn', lang('Edit Kur Btn'), 'class="btn btn-primary"'); ?>
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