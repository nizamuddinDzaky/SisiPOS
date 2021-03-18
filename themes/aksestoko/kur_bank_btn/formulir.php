<?php
function createOptions($items, $val = null){
    $result = [];
    foreach ($items as $key => $value) {
        $selected = $key == $val ? "selected='selected'" : "";
        $result[] = "<option value=\"$key\" $selected>$value</option>";
    }
    return implode("\n", $result);
}

$mapStatusTempat = [
    '' => 'Pilih Status',
    1 => 'Milik Sendiri',
    2 => 'Rumah Keluarga',
    3 => 'Kontrak / Sewa',
];

$mapTujuanKur = [
  '' => 'Pilih Tujuan KUR',
  1 => 'Modal Kerja (1 - 4 Tahun)',
  2 => 'Investasi (1 - 5 Tahun)'
];

?>

<div class="container pt-4 pb-2">
  <h3 class="input-group">
    <a href="<?=base_url(aksestoko_route("aksestoko/home/kur_bank_btn"))?>" class="btn btn-back mr-3"><i class="fal fa-angle-left"></i></a>
  </h3>
</div>

    <section class="py-main section-account-header animated fadeInUp">
      <div class="container">
        <div class="heading text-center">
          <h2>Formulir Pengajuan KUR Bank BTN</h2>
        </div>

        <div class="content">
          <form class="needs-validation mt-5" enctype="multipart/form-data" action="" method="POST">
            <div class="row">
              <div class="col-12">
                <div class="box p-box mb-3">
                  <input type="hidden" value="<?=$pengajuan->id ?? ''?>" name="loanID">
                  <div class="row">
                    <div class="col-12">
                      <h5>Data Pengajuan KUR</h5>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label class="required" for="">Cabang</label>
                            <select id="cabang" class="form-control" name="cabang" required>
                                <?= createOptions($branchs, $pengajuan->cabang ?? '')?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                      <div class="form-group">
                      <label class="required" for="plafon_kur">Pengajuan Plafon KUR</label>
                        <div class="input-group mb-3">
                          <div class="input-group-prepend">
                            <span class="input-group-text">Rp</span>
                          </div>
                          <input type="number" id="plafon_kur" class="form-control" name="plafon_kur" value="<?= @$pengajuan->plafon_kur ? (float) $pengajuan->plafon_kur : '' ?>" min="5000000" max="500000000"  placeholder="5.000.000 s/d Rp.500.000.000" required>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label class="required" for="">Tujuan KUR</label>
                            <select id="tujuan_kur" class="form-control" name="tujuan_kur" required>
                                <?= createOptions($mapTujuanKur, $pengajuan->tujuan_kur ?? '')?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                      <div class="form-group">
                        <label class="required" for="jangka_waktu">Jangka Waktu Pengajuan KUR</label>
                        
                        <div class="input-group mb-3">
                          <select name="jangka_waktu" id="jangka_waktu" class="form-control text-sans-serif" required>
                            <option value="" selected disabled>Pilih Tujuan KUR terlebih dahulu</option>
                          </select>
                          <div class="input-group-append">
                            <span class="input-group-text">Tahun</span>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-12 col-12">
                        <div class="form-group">
                          <label class="required" for="">Tujuan Detail Pengajuan KUR</label>
                          <textarea id="tujuan_detail" type="text" name="tujuan_detail" class="form-control" id="" 
                                    placeholder="Masukkan Tujuan Detail Pengajuan KUR" rows="2"  style="resize: none;" required><?= $pengajuan->tujuan_detail ?? ''?></textarea>
                        </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-12">
                      <hr>
                      <h5>Data Diri</h5>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">No KTP</label>
                          <input type="text" class="form-control text-dark" name="ktp" value="<?= $pengajuan->ktp ?? ''?>" placeholder="Nomor KTP Anda (16 digit angka)" required pattern="[0-9]{16}" maxlength="16" minlength="16">
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">Nama</label>
                          <input type="text" class="form-control" name="nama" value="<?= $pengajuan->nama ?? ''?>" placeholder="Masukkan Nama Lengkap Anda" required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">Tempat Lahir</label>
                          <input type="text" class="form-control" name="tempat_lahir" value="<?= $pengajuan->tempat_lahir ?? ''?>"   placeholder="Masukkan Tempat Lahir Anda" required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">Tanggal Lahir</label>
                          <input type="text" id="date" data-format="YYYY-MM-DD" class="form-control" data-template="DD MMMM YYYY" name="tanggal_lahir" value="<?= $pengajuan->tanggal_lahir ?? ''?>" required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">Jenis Kelamin</label>
                          <div class="custom-control custom-radio custom-control-inline" style="display: table;">
                            <input type="radio" id="rd_1" name="jenis_kelamin" class="custom-control-input radio_rb"  value="1" <?=(($pengajuan->jenis_kelamin ?? '') == '1' ? 'checked':'')?> required>
                            <label class="custom-control-label red text-sans-serif" for="rd_1" style="display: table-cell;text-align: center;vertical-align: middle; padding-left:10px;">
                              Laki - laki</label>
                          </div>
                          <div class="custom-control custom-radio custom-control-inline" style="display: table;">
                            <input type="radio" id="rd_2" name="jenis_kelamin" class="custom-control-input radio_rb" value="0" <?=(($pengajuan->jenis_kelamin ?? '') == '0' ? 'checked':'')?> required>
                            <label class="custom-control-label red text-sans-serif" for="rd_2" style="display: table-cell;text-align: center;vertical-align: middle; padding-left:10px;">
                              Perempuan
                            </label>
                          </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">Nomor Handphone</label>
                          <input type="number" class="form-control" name="hp" value="<?= $pengajuan->hp ?? ''?>" placeholder="Masukkan Nomor Handphone Anda" required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">Email</label>
                          <input type="email" class="form-control" name="email" value="<?= $pengajuan->email ?? ''?>"   placeholder="Masukkan Email Anda" required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="status_menikah">Status Pernikahan</label>
                          <div class="custom-control custom-radio custom-control-inline" style="display: table;">
                            <input type="radio" id="status_menikah_1" name="status_menikah" class="custom-control-input radio_rb"  value="0" <?=(($pengajuan->status_menikah ?? '') == '0' ? 'checked':'')?> required>
                            <label class="custom-control-label red text-sans-serif" for="status_menikah_1" style="display: table-cell;text-align: center;vertical-align: middle; padding-left:10px;">
                              Belum Menikah</label>
                          </div>
                          <div class="custom-control custom-radio custom-control-inline" style="display: table;">
                            <input type="radio" id="status_menikah_2" name="status_menikah" class="custom-control-input radio_rb" value="1" <?=(($pengajuan->status_menikah ?? '') == '1' ? 'checked':'')?> required>
                            <label class="custom-control-label red text-sans-serif" for="status_menikah_2" style="display: table-cell;text-align: center;vertical-align: middle; padding-left:10px;">
                              Sudah Menikah
                            </label>
                          </div>
                        </div>
                    </div>

                    <div class="col-md-12 col-12">
                        <div class="form-group">
                          <label class="required" for="">Alamat Tempat Tinggal</label>
                          <textarea id="AlamatKTP" type="text" name="alamat_tt" class="form-control" id="" 
                                    placeholder="Masukkan Alamat Tempat Tinggal Anda" rows="2"  style="resize: none;" required><?= $pengajuan->alamat_tt ?? ''?></textarea>
                        </div>
                    </div>

                    <div class="col-md-4 col-12">
                        <div class="form-group">
                          <label class="required" for="">Kode Pos Tempat Tinggal</label>
                          <input id="kodepos_tt" type="text" class="form-control" name="kodepos_tt" maxlength="5" minlength="5" value="<?=$pengajuan->kodepos_tt ?? ''?>" placeholder="Masukkan Kode Pos sesuai KTP Anda" required>
                        </div>
                    </div>

                    <div class="col-md-4 col-12">
                        <div class="form-group">
                          <label class="required" for="">RT Tempat Tinggal</label>
                          <input type="text" id="" class="form-control" name="rt_tt" value="<?= $pengajuan->rt_tt ?? ''?>"   placeholder="Masukkan RT Tempat Tinggal Anda" required>
                        </div>
                    </div>

                    <div class="col-md-4 col-12">
                        <div class="form-group">
                          <label class="required" for="">RW Tempat Tinggal</label>
                          <input type="text" id="" class="form-control" name="rw_tt" value="<?= $pengajuan->rw_tt ?? ''?>"   placeholder="Masukkan RW Tempat Tinggal Anda" required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">Provinsi Tempat Tinggal</label>
                          <input type="text" id="provinsi_tt" class="form-control bg-white text-dark" readonly name="provinsi_tt" value="<?=$pengajuan->provinsi_tt ?? ''?>" placeholder="Masukkan Provinsi Tempat Tinggal Anda" required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">Kabupaten/Kota Tempat Tinggal</label>
                          <input type="text" id="kota_tt" class="form-control bg-white text-dark" readonly name="kota_tt"  value="<?=$pengajuan->kota_tt ?? ''?>" placeholder="Masukkan Kabupaten/Kota Tempat Tinggal Anda" required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">Kecamatan Tempat Tinggal</label>
                          <input type="text" id="kecamatan_tt" class="form-control bg-white text-dark" name="kecamatan_tt" readonly value="<?=$pengajuan->kecamatan_tt ?? ''?>" placeholder="Masukkan Kecamatan Tempat Tinggal Anda" required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="kelurahan_tt">Kelurahan Tempat Tinggal</label>
                          <select name="kelurahan_tt" id="kelurahan_tt" class="form-control" required>
                            <option value="" disabled <?=@$pengajuan->kelurahan_tt && $pengajuan->kelurahan_tt != '' ? '' : 'selected'?>>Masukkan Kelurahan Tempat Tinggal Anda</option>
                            <?php if(@$pengajuan->kelurahan_tt && $pengajuan->kelurahan_tt != '') { ?>
                              <option selected><?=$pengajuan->kelurahan_tt?></option>
                            <?php }?>
                          </select>
                        </div>
                    </div>

                    <div class="col-md-12 col-12">
                        <div class="form-group">
                            <label class="required" for="">Status Tempat Tinggal</label>
                            <select id="" class="form-control" name="status_tt" required>
                                <?= createOptions($mapStatusTempat, $pengajuan->status_tt ?? '')?>
                            </select>
                        </div>
                    </div>
                  </div>
                  <div id="pasangan" class="row" style="display: none;">
                    <div class="col-12">
                      <hr>
                      <h5>Data Pasangan</h5>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="ktp_pasangan">No KTP Pasangan</label>
                          <input type="text" id="ktp_pasangan" class="form-control text-dark" name="ktp_pasangan" value="<?= $pengajuan->ktp_pasangan ?? ''?>" placeholder="Nomor KTP Pasangan Anda (16 digit angka)" required pattern="[0-9]{16}" maxlength="16" minlength="16">
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="nama_pasangan">Nama Pasangan</label>
                          <input type="text" class="form-control" name="nama_pasangan" value="<?= $pengajuan->nama_pasangan ?? ''?>" id="nama_pasangan" placeholder="Masukkan Nama Lengkap Pasangan Anda" required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">Tempat Lahir Pasangan</label>
                          <input type="text" class="form-control" id="tmptlhr_pasangan" name="tmptlhr_pasangan" value="<?= $pengajuan->tmptlhr_pasangan ?? ''?>"   placeholder="Masukkan Tempat Lahir Pasangan Anda" required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="tgllhr_pasangan">Tanggal Lahir Pasangan</label>
                          <input type="text" id="tgllhr_pasangan" data-format="YYYY-MM-DD" class="form-control" data-template="DD MMMM YYYY" name="tgllhr_pasangan" value="<?= $pengajuan->tgllhr_pasangan ?? ''?>" required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="hp_pasangan">No Handphone Pasangan</label>
                          <input type="number" class="form-control" name="hp_pasangan" id="hp_pasangan" value="<?= $pengajuan->hp_pasangan ?? ''?>" placeholder="Masukkan Nomor Handphone Anda" required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="" for="email_pasangan">Email Pasangan</label>
                          <input type="email" class="form-control" name="email_pasangan" value="<?= $pengajuan->email_pasangan ?? ''?>" id="email_pasangan" placeholder="Masukkan Email Pasangan Anda">
                        </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-12">
                      <hr>
                      <h5>Data Tempat Usaha</h5>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">Nama Usaha Anda</label>
                          <input type="text" class="form-control" name="nama_u" value="<?= $pengajuan->nama_u ?? ''?>"   placeholder="Masukkan Nama Usaha Anda" required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                      <div class="form-group">
                        <label class="required" for="lama_u">Lama Usaha Anda</label>

                        <div class="input-group mb-3">
                          <input type="number" class="form-control text-sans-serif" id="lama_u" name="lama_u" value="<?= $pengajuan->lama_u ?? ''?>" placeholder="Lama Usaha (Dalam Satuan Tahun)" required min="1">
                          <div class="input-group-append">
                            <span class="input-group-text">Tahun</span>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">Sektor Usaha</label>
                          <input type="text" class="form-control" name="sektor_u" value="<?= $pengajuan->sektor_u ?? ''?>"   placeholder="Masukkan Sektor Usaha Anda" required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                      <div class="form-group">
                        <label class="required" for="">Omset Perbulan</label>
                        <div class="input-group mb-3">
                          <div class="input-group-prepend">
                            <span class="input-group-text">Rp</span>
                          </div>
                          <input type="number" class="form-control" name="omset_u" placeholder="Masukkan Omset Perbulan Anda" required value="<?= @$pengajuan->omset_u ? (float) $pengajuan->omset_u : ''?>" min="1">
                        </div>
                      </div>
                    </div>

                    <!-- Tempat Usaha -->
                    <div class="col-md-12 col-12">
                        <div class="form-group">
                          <label class="required" for="">Alamat Tempat Usaha</label>
                          <textarea id="alamat_u" type="text" name="alamat_u" class="form-control" id="" placeholder="Masukkan Alamat Tempat Usaha Anda" rows="2"  style="resize: none;" required><?= $pengajuan->alamat_u ?? ''?></textarea>
                        </div>
                    </div>

                    <div class="col-md-4 col-12">
                        <div class="form-group">
                          <label class="required" for="">Kode Pos Tempat Usaha Anda</label>
                          <input id="kodepos_u" type="text" class="form-control" name="kodepos_u" maxlength="5" minlength="5" value="<?=$pengajuan->kodepos_u ?? ''?>" placeholder="Masukkan Kode Pos Tempat Usaha Anda" required>
                        </div>
                    </div>

                    <div class="col-md-4 col-12">
                        <div class="form-group">
                          <label class="required" for="">RT Tempat Usaha Anda</label>
                          <input type="text" id="" class="form-control" name="rt_u" value="<?= $pengajuan->rt_u ?? ''?>"   placeholder="Masukkan RT Tempat Usaha Anda" required>
                        </div>
                    </div>

                    <div class="col-md-4 col-12">
                        <div class="form-group">
                          <label class="required" for="">RW Tempat Usaha Anda</label>
                          <input type="text" id="" class="form-control" name="rw_u" value="<?= $pengajuan->rw_u ?? ''?>"   placeholder="Masukkan RW Tempat Usaha Anda" required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">Provinsi Tempat Usaha Anda</label>
                          <input type="text" id="provinsi_u" class="form-control bg-white text-dark" readonly name="provinsi_u"  value="<?=$pengajuan->provinsi_u ?? ''?>" placeholder="Masukkan Provinsi Usaha Anda" required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">Kabupaten/Kota Tempat Usaha Anda</label>
                          <input type="text" id="kota_u" class="form-control bg-white text-dark" readonly name="kota_u"  value="<?=$pengajuan->kota_u ?? ''?>" placeholder="Masukkan Kabupaten Kota Tempat Usaha Anda" required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">Kecamatan Tempat Usaha Anda</label>
                          <input type="text" id="kecamatan_u" class="form-control bg-white text-dark" name="kecamatan_u" readonly value="<?=$pengajuan->kecamatan_u ?? ''?>" placeholder="Masukkan Kecamatan Tempat Usaha Anda" required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="kelurahan_u">Kelurahan Tempat Usaha</label>
                          <select name="kelurahan_u" id="kelurahan_u" class="form-control" required>
                            <option value="" disabled <?=@$pengajuan->kelurahan_u && $pengajuan->kelurahan_u != '' ? '' : 'selected'?>>Masukkan Kelurahan Tempat Usaha Anda</option>
                            <?php if(@$pengajuan->kelurahan_u && $pengajuan->kelurahan_u != '') { ?>
                              <option selected><?=$pengajuan->kelurahan_u?></option>
                            <?php }?>
                          </select>
                        </div>
                    </div>


                    <div class="col-md-12 col-12">
                        <div class="form-group">
                            <label class="required" for="">Status Tempat Usaha</label>
                            <select id="" class="form-control" name="status_tu" required>
                                <?= createOptions($mapStatusTempat, $pengajuan->status_tu ?? '')?>
                            </select>
                        </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-12">
                      <hr>
                      <h5>Berkas Penunjang</h5>
                    </div>

                    <div class="col-md-6 col-12">
                      <label>Unggah Foto Selfie Anda</label>

                      <div class="custom-file" >
                        <label for="foto_debitur" class="custom-file-upload" style="margin:0;">
                          <i class="fas fa-copy"></i> Pilih File
                        </label>
                        <input type="file" accept=".jpg , .png , .JPEG" class="foto_debitur" id="foto_debitur" name="foto_debitur" style="width: 1px;">
                        <span id="val_foto_debitur" style="font-size: 14px; color: #8B8D8E;"><i>Silahkan unggah file Anda</i></span>
                      </div>
                      <small class="font-italic text-danger">Disarankan : ekstensi file .jpg - ukuran &lt;= 1MB</small>
                    </div>

                    <div class="col-md-6 col-12">
                      <label>Unggah KTP Anda</label>

                      <div class="custom-file" >
                        <label for="foto_ktp" class="custom-file-upload" style="margin:0;">
                          <i class="fas fa-copy"></i> Pilih File
                        </label>
                        <input type="file" accept=".jpg , .png , .JPEG" class="foto_ktp" id="foto_ktp" name="foto_ktp" style="width: 1px;">
                        <span id="val_foto_ktp" style="font-size: 14px; color: #8B8D8E;"><i>Silahkan unggah file Anda</i></span>
                      </div>
                      <small class="font-italic text-danger">Disarankan : ekstensi file .jpg - ukuran &lt; 1MB</small>
                    </div>
                  

                    <div class="col-md-6 col-12">
                      <label>Unggah Foto NPWP</label>

                      <div class="custom-file" >
                        <label for="foto_npwp" class="custom-file-upload" style="margin:0;">
                          <i class="fas fa-copy"></i> Pilih File
                        </label>
                        <input type="file" accept=".jpg , .png , .JPEG" class="foto_npwp" id="foto_npwp" name="foto_npwp" style="width: 1px;">
                        <span id="val_foto_npwp" style="font-size: 14px; color: #8B8D8E;"><i>Silahkan unggah file Anda</i></span>
                      </div>
                      <small class="font-italic text-danger">Disarankan : ekstensi file .jpg - ukuran &lt; 1MB</small>
                    </div>

                    <div class="col-md-6 col-12">
                      <label>Unggah Foto Izin Usaha</label>

                      <div class="custom-file">
                        <label for="foto_izin_usaha" class="custom-file-upload" style="margin:0;">
                          <i class="fas fa-copy"></i> Pilih File
                        </label>
                        <input type="file" accept=".jpg , .png , .JPEG" class="foto_izin_usaha" id="foto_izin_usaha" name="foto_izin_usaha" style="width: 1px;">
                        <span id="val_foto_izin_usaha" style="font-size: 14px; color: #8B8D8E;"><i>Silahkan unggah file Anda</i></span>
                      </div>
                      <small class="font-italic text-danger">Disarankan : ekstensi file .jpg - ukuran &lt; 1MB</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
              
            <div class="clearfix mt-5">
              <button type="submit" class="btn btn-primary btn-block"  >Kirim</button>
            </div>
          </form>
        
        </div>
      </div>
    </section>

<div id="loader-section">
  <div class="loaderBox">
    <div class="loaderKotak">
      <div class="loaderNew">   
        <span class="boxLoader"></span>   
          <span class="boxLoader"></span>  
          <div class="codeLoader"> 
            <img class="image-loader" src="<?=$assets_at?>loader/loader.png">
          </div>    
        <span class="txtLoader"><i>Ditunggu ya</i></span>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  var max_file = 1024000; //1 MB
  var jangka_waktu = `<?= (int) $pengajuan->jangka_waktu ?? ''?>`.trim();
  $(document).ready(function(){

    $("#tujuan_kur").change(function () {
      let length = 0;
      if(this.value == 1){
        length = 4
      } else if(this.value == 2) {
        length = 5
      }
      let options = `<option value="" disabled ${length == 0 ? 'selected' : ''}>${length == 0 ? 'Pilih Tujuan KUR terlebih dahulu' : 'Pilih Jangka Waktu Pengajuan KUR'}</option>`;
      for (let index = 1; index <= length; index++) {
        options += `<option value="${index}" ${jangka_waktu == index ? 'selected' : ''}>${index}</option>`;
      }
      $("#jangka_waktu").html(options);
    }).change();

    $("input[name='ktp']").inputFilter(function(value) {
      return /^\d*$/.test(value);    // Allow digits only, using a RegExp
    });
    $("input[name='ktp_pasangan']").inputFilter(function(value) {
      return /^\d*$/.test(value);    // Allow digits only, using a RegExp
    });
    $('#loader-section').fadeOut(200);

    $(".foto_debitur").on("change", function() {
      if(this.files[0].size > max_file) {
        $(this).val('')
        alert("Ukuran berkas melebihi 1MB");
        return;
      }
      var fileName = $(this).val().split("\\").pop();
      $(this).siblings("#val_foto_debitur").addClass("selected").html(fileName);
      
    });

    $(".foto_ktp").on("change", function() {
      if(this.files[0].size > max_file) {
        $(this).val('')
        alert("Ukuran berkas melebihi 1MB");
        return;
      }
      var fileName = $(this).val().split("\\").pop();
      $(this).siblings("#val_foto_ktp").addClass("selected").html(fileName);
    });

    $(".foto_npwp").on("change", function() {
      if(this.files[0].size > max_file) {
        $(this).val('')
        alert("Ukuran berkas melebihi 1MB");
        return;
      }
      var fileName = $(this).val().split("\\").pop();
      $(this).siblings("#val_foto_npwp").addClass("selected").html(fileName);
    });

    $(".foto_izin_usaha").on("change", function() {
      if(this.files[0].size > max_file) {
        $(this).val('')
        alert("Ukuran berkas melebihi 1MB");
        return;
      }
      var fileName = $(this).val().split("\\").pop();
      $(this).siblings("#val_foto_izin_usaha").addClass("selected").html(fileName);
    });


    $('#date').combodate({
      customClass: 'form-control date-birth',
      minYear: 1945,
      maxYear: (new Date()).getFullYear()
    });
    $('#tgllhr_pasangan').combodate({
      customClass: 'form-control date-birth-couple',
      minYear: 1945,
      maxYear: (new Date()).getFullYear()
    });
    $('.date-birth').attr('required', true);
    $('.date-birth-couple').attr('required', true);

    $('input:radio[name="status_menikah"]').change( function(){
      if ($(this).is(':checked') && $(this).val() == '0') {
        $("#pasangan").slideUp();
        $("#nama_pasangan").removeAttr('required');
        $("#ktp_pasangan").removeAttr('required');
        $("#tmptlhr_pasangan").removeAttr('required');
        $("#tgllhr_pasangan").removeAttr('required');
        $(".date-birth-couple").removeAttr('required')
        $("#hp_pasangan").removeAttr('required');

      } else if($(this).is(':checked') && $(this).val() == '1'){
        $("#pasangan").slideDown();
        $("#nama_pasangan").attr('required', 'required');
        $("#ktp_pasangan").attr('required');
        $("#tmptlhr_pasangan").attr('required');
        $("#tgllhr_pasangan").attr('required');
        $(".date-birth-couple").attr('required')
        $("#hp_pasangan").attr('required');
      }
    }).change();

    function setKosongTempatTinggal() {
      $('#provinsi_tt').val('');
      $('#kota_tt').val('');
      $('#kecamatan_tt').val('');
      $('#kelurahan_tt').html(`<option value="" disabled selected >Masukkan Kelurahan Tempat Tinggal Anda</option>`);
      $('#loader-section').fadeOut(200);
    }

    var lastKodePOSTinggal = '';
    var kelurahanValue = `<?=trim($pengajuan->kelurahan_tt ?? '')?>`;
    $("#kodepos_tt").on("keyup change", function () {
      if(lastKodePOSTinggal === this.value) {
        return;
      }
      lastKodePOSTinggal = this.value;
      if(this.value.length != 5){
        setKosongTempatTinggal();
        return
      }
      $("#loader-section").show();
      let option = `<option value="" disabled>Masukkan Kelurahan Tempat Tinggal Anda</option>`;
      
      $.get(`<?=base_url(aksestoko_route('aksestoko/home/kodepos/'))?>${this.value}`, function(data, status){
        let daerah = (JSON.parse(data));
        if(!daerah || daerah.kodepos[0].provinsi == 'provinsi') {
          setKosongTempatTinggal();
          alertCustom('Kode POS Tidak Ditemukan', 'danger', 10001);
          return
        }
        for (const d of daerah.kodepos) {
          option += `<option value="${d.kelurahan}">${d.kelurahan}</option>`;
        }
        $('#provinsi_tt').val(daerah.kodepos[0].provinsi);
        $('#kota_tt').val(daerah.kodepos[0].kabupaten);
        $('#kecamatan_tt').val(daerah.kodepos[0].kecamatan);
        $('#kelurahan_tt').html(option);
        $('#kelurahan_tt').val(kelurahanValue && kelurahanValue != '' ? kelurahanValue : daerah.kodepos[0].kelurahan);
        kelurahanValue = ''
        $('#loader-section').fadeOut(200);
      });
    }).change();


    function setKosongTempatUsaha() {
      $('#provinsi_u').val('');
      $('#kota_u').val('');
      $('#kecamatan_u').val('');
      $('#kelurahan_u').html(`<option value="" disabled selected >Masukkan Kelurahan Tempat Usaha Anda</option>`);
      $('#loader-section').fadeOut(200);
    }

    var lastKodePOSUsaha = '';
    var kelurahanUsahaValue = `<?=trim($pengajuan->kelurahan_u ?? '')?>`;
    $("#kodepos_u").on("keyup change", function () {
      if(lastKodePOSUsaha === this.value) {
        return;
      }
      lastKodePOSUsaha = this.value;
      if(this.value.length != 5){
        setKosongTempatUsaha();
        return
      }
      $("#loader-section").show();
      let option = `<option value="" disabled>Masukkan Kelurahan Tempat Usaha Anda</option>`;
      
      $.get(`<?=base_url(aksestoko_route('aksestoko/home/kodepos/'))?>${this.value}`, function(data, status){
        let daerah = (JSON.parse(data));
        if(!daerah || daerah.kodepos[0].provinsi == 'provinsi') {
          setKosongTempatUsaha();
          alertCustom('Kode POS Tidak Ditemukan', 'danger', 10001);
          return
        }
        for (const d of daerah.kodepos) {
          option += `<option value="${d.kelurahan}">${d.kelurahan}</option>`;
        }
        $('#provinsi_u').val(daerah.kodepos[0].provinsi);
        $('#kota_u').val(daerah.kodepos[0].kabupaten);
        $('#kecamatan_u').val(daerah.kodepos[0].kecamatan);
        $('#kelurahan_u').html(option);
        $('#kelurahan_u').val(kelurahanUsahaValue && kelurahanUsahaValue != '' ? kelurahanUsahaValue : daerah.kodepos[0].kelurahan);
        kelurahanUsahaValue = ''
        $('#loader-section').fadeOut(200);
      });
    }).change();

  });

  // Restricts input for the set of matched elements to the given inputFilter function.
  (function($) {
    $.fn.inputFilter = function(inputFilter) {
      return this.on("input keydown keyup mousedown mouseup select contextmenu drop", function() {
        if (inputFilter(this.value)) {
          this.oldValue = this.value;
          this.oldSelectionStart = this.selectionStart;
          this.oldSelectionEnd = this.selectionEnd;
        } else if (this.hasOwnProperty("oldValue")) {
          this.value = this.oldValue;
          this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
        } else {
          this.value = "";
        }
      });
    };
  }(jQuery));
</script>
