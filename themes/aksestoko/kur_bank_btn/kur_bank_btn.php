<?php
$mapStatusTempat = [
    1 => 'Milik Sendiri',
    2 => 'Rumah Keluarga',
    3 => 'Kontrak / Sewa',
];

$mapTujuanKur = [
    1 => 'Modal Kerja',
    2 => 'Investasi'
];
?>
<div class="container pt-4 pb-2">
    <h3 class="input-group">
        <a href="<?= base_url(aksestoko_route("aksestoko/home/programs")) ?>" class="btn btn-back mr-3"><i class="fal fa-angle-left"></i></a>
    </h3>
</div>

<section class="py-main section-account-header animated fadeInUp">
    <div class="container">
        <div class="heading text-center">
            <h2>KUR BANK BTN</h2>
        </div>

        <div class="content">
            <div class="row mt-5">
                <div class="col-md-12">
                    <div class="box p-box mb-3">
                        <div class="row">
                            <div class="col-md-12">
                                <h5>KUR hadir untuk memberikan solusi pembiayaan modal kerja dan investasi untuk meningkatkan kemampuan usaha skala Mikro, Kecil, dan Menengah (UMKM) dengan benefit sebagai berikut:</h5>
                                <ol type="a">
                                    <li>Syarat Kredit Ringan dan Mudah</li>
                                    <li>Suku Bunga 6% pa</li>
                                    <li>Biaya Provisi 0%</li>
                                    <li>Biaya Admin Ringan 0,25%</li>
                                </ol>
                                <h5>Produk, Plafon dan Jangka Waktu Kredit Usaha Rakyat (KUR)</h5>
                                <ol type="a">
                                    <li>
                                        Produk KUR
                                        <ul>
                                            <li>KUR Mikro (Plafon kredit 5 Juta – 50 Juta)</li>
                                            <li>KUR Kecil (Plafon Kredit 51 Juta – 500 Juta)</li>
                                        </ul>
                                    </li>
                                    <li>
                                        Tujuan Kredit
                                        <ul>
                                            <li>Modal Kerja (penambahan stok barang)</li>
                                            <li>Investasi (renovasi, pembelian tempat usaha, pembelian kendaraan operasional usaha)</li>
                                        </ul>
                                    </li>
                                    <li>
                                        Jangka Waktu
                                        <ul>
                                            <li>Modal Kerja (Maks. 4 Tahun)</li>
                                            <li>Investasi (Maks. 5 Tahun)</li>
                                        </ul>
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="box p-box mb-3">
                        <div class="row">
                            <div class="col-md-12">
                                <h5>Syarat-syarat pengajuan KUR BTN</h5>
                                <ol>
                                    <li>Telah berhasil melakukan transaksi AksesToko sebanyak 10 kali atau lebih. <span class="<?=$syarat['jumlah'] >= 10 ? 'text-green' : 'text-danger'?> font-weight-bold"> <i class="fa <?=$syarat['jumlah'] >= 10 ? 'fa-check' : 'fa-times'?>"></i> (<?=$syarat['jumlah']?> Transaksi)</span></li>
                                    <li>Volume transaksi AksesToko telah mencapai 100 Ton atau lebih. <span class="<?=$syarat['tonase'] >= 100 ? 'text-green' : 'text-danger'?> font-weight-bold"> <i class="fa <?=$syarat['tonase'] >= 100 ? 'fa-check' : 'fa-times'?>"></i> (<?=$syarat['tonase']?> Ton) </span> </li>
                                    <li>Memiliki dokumen-dokumen berikut:
                                        <ul>
                                            <li>E-KTP</li>
                                            <li>NPWP</li>
                                            <li>Legalitas Pendirian Usaha</li>
                                            <li>Izin Usaha(SIUP/TDP/SKDU)</li>
                                            <li>Surat izin usaha dari kantor pemerintah setempat (untuk usaha Mikro)</li>
                                            <li>Legalitas tempat usaha</li>
                                            <li>Laporan Keuangan</li>
                                            <li>Copy Rekening Koran / Tabungan</li>
                                            <li>Legalitas Agunan (untuk KUR Kecil)</li>
                                            <li>Agunan : SHM/BPKB Kendaraan</li>
                                            <li>Dokumen lainnya yang dibutuhkan</li>
                                        </ul>
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($syaratMemenuhi): ?>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="box p-box mb-3">
                            <div class="row mb-4">
                                <div class="col-md-2 d-flex align-items-center justify-content-center">
                                    <img src="<?= base_url('assets/uploads/bank_logo/btn.png') ?>" style="max-width: 100px;">
                                </div>
                                <div class="col-md-5">
                                    <label>Status Pengajuan</label>
                                    <h5><?= $status ?></h5>
                                </div>
                                <div class="col-md-5">
                                    <label>Nomor KUR Bank BTN</label>
                                    <h5><?= $pengajuan->respon_id ?? '-' ?></h5>
                                </div>
                            </div>
                            <div class="footer-kredit-mandiri">
                                <div class="row">
                                    <div class="col-md-8 col-12 align-self-center">
                                        <small id="notifKreditMandiri" class="form-text text-sans-serif font-italic <?=@$notif_type?>"><?=@$notif?></small>
                                    </div>
                                    <div class="col-md-4 col-12">
                                        <div class="buttonKreditMandiri">
                                            <a href="<?= base_url(aksestoko_route('aksestoko/home/form_kur_bank_btn')) ?>" class="btn btn-outline-primary btn-sm mr-2 font-button <?= @$button_perbarui ?>">Perbarui Data</a>
                                            <a href="#confirmKredit" data-toggle="modal" class="btn btn-primary btn-sm font-button <?= @$button_ajukan ?>">Ajukan KUR</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <div class="box p-box mb-3">
                            <div class="row">
                                <div class="col-12">
                                    <h5>Berkas Penunjang</h5>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Foto Selfie</label>
                                        <div class="thumnail-files box ">
                                            <img class="img-files" src="<?= $pengajuan->foto_debitur ?: '' ?>" onerror="this.src='<?= base_url('assets/uploads/no_image.png') ?>'" width="100%">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Foto KTP</label>
                                        <div class="thumnail-files box ">
                                            <img class="img-files" src="<?= $pengajuan->foto_ktp ?: '' ?>" onerror="this.src='<?= base_url('assets/uploads/no_image.png') ?>'" width="100%">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Foto NPWP</label>
                                        <div class="thumnail-files box ">
                                            <img class="img-files" src="<?= $pengajuan->foto_npwp ?: '' ?>" onerror="this.src='<?= base_url('assets/uploads/no_image.png') ?>'" width="100%">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Foto Izin Usaha</label>
                                        <div class="thumnail-files box ">
                                            <img class="img-files" src="<?= $pengajuan->foto_izinUsaha ?: '' ?>" onerror="this.src='<?= base_url('assets/uploads/no_image.png') ?>'" width="100%">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <hr>
                                    <h5>Data Pengajuan KUR</h5>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Cabang</label>
                                        <input type="text" class="form-control  text-dark bg-white" name="cabang" value="<?= $pengajuan->cabang->name ?>"   readonly>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Pengajuan Plafon KUR</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Rp</span>
                                            </div>
                                            <input type="text" class="form-control text-dark bg-white" name="plafon_kur" value="<?= $pengajuan->plafon_kur == '' ? '' : number_format($pengajuan->plafon_kur, 0, ',', '.'); ?>" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Tujuan KUR</label>
                                        <input type="text" class="form-control  text-dark bg-white" name="tujuan_kur" value="<?= $mapTujuanKur[$pengajuan->tujuan_kur] ?>"   readonly>
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Jangka Waktu Pengajuan KUR</label>
                                        <div class="input-group mb-3">
                                            <input type="number" class="form-control text-dark bg-white" name="jangka_waktu" value="<?= $pengajuan->jangka_waktu ?>"   readonly>
                                            <div class="input-group-append">
                                                <span class="input-group-text">Tahun</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Tujuan Detail Pengajuan KUR</label>
                                        <textarea id="tujuan_detail" type="text" name="tujuan_detail" class="form-control text-dark bg-white" id=""
                                                    readonly rows="2"  style="resize: none;" ><?= $pengajuan->tujuan_detail ?></textarea>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <hr>
                                    <h5>Data Diri</h5>
                                </div>

                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="">No KTP</label>
                                        <input type="number" class="form-control text-dark bg-white" name="ktp" value="<?= $pengajuan->ktp ?>" readonly >
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Nama</label>
                                        <input type="text" class="form-control  text-dark bg-white" name="nama" value="<?= $pengajuan->nama ?>" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Tempat Lahir</label>
                                        <input type="text" class="form-control text-dark bg-white" name="tempat_lahir" value="<?= $pengajuan->tempat_lahir ?>"   readonly>
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Tanggal Lahir</label>
                                        <input type="text" id="date" data-format="YYYY-MM-DD" readonly class="form-control text-dark bg-white" data-template="DD MMMM YYYY" name="tanggal_lahir" value="<?= $pengajuan->tanggal_lahir ?>" >
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Jenis Kelamin</label>
                                        <input type="text" class="form-control text-dark bg-white" name="jenis_kelamin" readonly
                                                value="<?= $pengajuan->jenis_kelamin == '1' ? 'Laki - laki' : ($pengajuan->jenis_kelamin == '0' ? 'Perempuan' : '')  ?>" >
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Nomor Handphone</label>
                                        <input type="text" class="form-control text-dark bg-white" name="hp" value="<?= $pengajuan->hp ?>"   readonly>
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Email</label>
                                        <input type="text" class="form-control text-dark bg-white" name="email" value="<?= $pengajuan->email ?>"   readonly>
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Status Pernikahan</label>
                                        <input type="text" class="form-control text-dark bg-white" name="status_menikah" readonly
                                                value="<?= $pengajuan->status_menikah == '1' ? 'Sudah Menikah' : ($pengajuan->status_menikah == '0' ? 'Belum Menikah' : '')  ?>" >
                                    </div>
                                </div>


                                <div class="col-md-12 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Alamat Tempat Tinggal</label>
                                        <textarea id="AlamatKTP" type="text" name="alamat_tt" class="form-control text-dark bg-white" id=""
                                                    readonly rows="2"  style="resize: none;" required><?= $pengajuan->alamat_tt ?></textarea>
                                    </div>
                                </div>

                                <div class="col-md-4 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Kode Pos Tempat Tinggal</label>
                                        <input id="kodepos_tt" type="text" class="form-control text-dark bg-white" name="kodepos_tt" maxlength="5" minlength="5" value="<?= $pengajuan->kodepos_tt ?>" readonly>
                                    </div>
                                </div>

                                <div class="col-md-4 col-12">
                                    <div class="form-group">
                                        <label class="requied" for="">RT Tempat Tinggal</label>
                                        <input type="text" id="" class="form-control text-dark bg-white" name="rt_tt" value="<?= $pengajuan->rt_tt ?>"   readonly>
                                    </div>
                                </div>

                                <div class="col-md-4 col-12">
                                    <div class="form-group">
                                        <label class="" for="">RW Tempat Tinggal</label>
                                        <input type="text" id="" class="form-control text-dark bg-white" name="rw_tt" value="<?= $pengajuan->rw_tt ?>"   readonly>
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Provinsi Tempat Tinggal</label>
                                        <input type="text" id="provinsi_tt" class="form-control bg-white text-dark" name="provinsi_tt"  value="<?= $pengajuan->provinsi_tt ?>" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Kabupaten/Kota Tempat Tinggal</label>
                                        <input type="text" id="kota_tt" class="form-control bg-white text-dark" name="kota_tt"  value="<?= $pengajuan->kota_tt ?>" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Kecamatan Tempat Tinggal</label>
                                        <input type="text" id="kecamatan_tt" class="form-control bg-white text-dark" name="kecamatan_tt" value="<?= $pengajuan->kecamatan_tt ?>" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="kelurahan_tt">Kelurahan Tempat Tinggal</label>
                                        <input type="text" id="kelurahan_tt" class="form-control bg-white text-dark" name="kelurahan_tt" value="<?= $pengajuan->kelurahan_tt ?>" readonly>
                                    </div>
                                </div>

                                <div class="col-md-12 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Status Tempat Tinggal</label>
                                        <input id="status_tt" class="form-control bg-white text-dark" name="status_tt" value="<?= $mapStatusTempat[$pengajuan->status_tt] ?>" readonly>
                                    </div>
                                </div>

                                <?php if($pengajuan->status_menikah == '1') { ?>

                                <div class="col-12">
                                    <hr>
                                    <h5>Data Pasangan</h5>
                                </div>

                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="">No KTP Pasangan</label>
                                        <input type="number" class="form-control text-dark bg-white" name="ktp_pasangan" value="<?= $pengajuan->ktp_pasangan ?>" readonly >
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Nama Pasangan</label>
                                        <input type="text" class="form-control  text-dark bg-white" name="nama_pasangan" value="<?= $pengajuan->nama_pasangan ?>" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Tempat Lahir Pasangan</label>
                                        <input type="text" class="form-control text-dark bg-white" name="tmptlhr_pasangan" value="<?= $pengajuan->tmptlhr_pasangan ?>"   readonly>
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Tanggal Lahir Pasangan</label>
                                        <input type="text" id="tgllhr_pasangan" data-format="YYYY-MM-DD" readonly class="form-control text-dark bg-white" data-template="DD MMMM YYYY" name="tgllhr_pasangan" value="<?= $pengajuan->tgllhr_pasangan ?>" >
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Nomor Handphone Pasangan</label>
                                        <input type="text" class="form-control text-dark bg-white" name="hp_pasangan" value="<?= $pengajuan->hp_pasangan ?>"   readonly>
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Email Pasangan</label>
                                        <input type="text" class="form-control text-dark bg-white" name="email_pasangan" value="<?= $pengajuan->email_pasangan ?>"   readonly>
                                    </div>
                                </div>

                                <?php } ?>
                                <div class="col-12">
                                    <hr>
                                    <h5>Data Tempat Usaha</h5>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Nama Usaha Anda</label>
                                        <input type="text" class="form-control  text-dark bg-white" name="nama_u" value="<?= $pengajuan->nama_u ?>"   readonly>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Lama Usaha Anda</label>
                                        <div class="input-group mb-3">
                                            <input type="number" class="form-control text-dark bg-white" name="lama_u" value="<?= $pengajuan->lama_u ?>" readonly>
                                            <div class="input-group-append">
                                                <span class="input-group-text">Tahun</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Sektor Usaha</label>
                                        <input type="text" class="form-control  text-dark bg-white" name="sektor_u" value="<?= $pengajuan->sektor_u ?>"   readonly>
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Omset Perbulan</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Rp</span>
                                            </div>
                                            <input type="text" class="form-control text-dark bg-white" name="omset_u" readonly value="<?= $pengajuan->omset_u == '' ? '' : number_format($pengajuan->omset_u, 0, ',', '.');?>" >
                                        </div>
                                    </div>
                                </div>

                                
                                <!-- Tempat Usaha -->
                                <div class="col-md-12 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Alamat Tempat Usaha</label>
                                        <textarea id="alamat_u" type="text" name="alamat_u" class="form-control bg-white text-dark" id=""
                                                    readonly rows="2"  style="resize: none;" required><?= $pengajuan->alamat_u ?></textarea>
                                    </div>
                                </div>

                                <div class="col-md-4 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Kode Pos Tempat Usaha</label>
                                        <input id="kodepos_u" type="text" class="form-control  bg-white text-dark" name="kodepos_u" maxlength="5" minlength="5" value="<?= $pengajuan->kodepos_u ?>" readonly>
                                    </div>
                                </div>

                                <div class="col-md-4 col-12">
                                    <div class="form-group">
                                        <label class="" for="">RT Tempat Usaha Anda</label>
                                        <input type="text" id="" class="form-control bg-white text-dark" name="rt_u" value="<?= $pengajuan->rt_u ?>"   readonly>
                                    </div>
                                </div>

                                <div class="col-md-4 col-12">
                                    <div class="form-group">
                                        <label class="" for="">RW Tempat Usaha Anda</label>
                                        <input type="text" id="" class="form-control  bg-white text-dark" name="rw_u" value="<?= $pengajuan->rw_u ?>"   readonly>
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Provinsi Tempat Usaha Anda</label>
                                        <input type="text" id="provinsi_u" class="form-control bg-white text-dark" readonly name="provinsi_u"  value="<?= $pengajuan->provinsi_u ?>" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Kabupaten/Kota Tempat Usaha Anda</label>
                                        <input type="text" id="kota_u" class="form-control bg-white text-dark" readonly name="kota_u"  value="<?= $pengajuan->kota_u ?>" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Kecamatan Tempat Usaha Anda</label>
                                        <input type="text" id="kecamatan_u" class="form-control bg-white text-dark" name="kecamatan_u" readonly value="<?= $pengajuan->kecamatan_u ?>" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="" for="kelurahan_u">Kelurahan Tempat Usaha</label>
                                        <input type="text" id="kelurahan_u" class="form-control bg-white text-dark" name="kelurahan_u" value="<?= $pengajuan->kelurahan_u ?>" readonly>
                                    </div>
                                </div>


                                <div class="col-md-12 col-12">
                                    <div class="form-group">
                                        <label class="" for="">Status Tempat Usaha</label>
                                        <input id="status_tu" class="form-control bg-white text-dark" name="status_tu" value="<?= $mapStatusTempat[$pengajuan->status_tu] ?>" readonly>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
<?php else: ?>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="box p-box mb-3">
                            <div class="row">
                                <div class="col-md-2 d-flex align-items-center justify-content-center">
                                    <img src="<?= base_url('assets/uploads/bank_logo/btn.png') ?>" style="max-width: 100px;">
                                </div>
                                <div class="col-md-10 d-flex align-items-center justify-content-center">
                                    <h5 class="text-center">Anda belum memenuhi syarat-syarat pengajuan KUR Bank BTN</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
<?php endif; ?>



        </div>
    </div>
</section>

<div class="modal fade" id="confirmKredit" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title text-center mobile-title" id="myModalLabel">Konfirmasi Ajukan KUR</h4>
            </div>
            <div class="modal-body">
                <div class="">
                    <div class="row">
                        <div class="col-12">
                            <div class="card-body text-center">
                                <p>Apakah Anda yakin bahwa data sesuai dengan situasi dan kondisi saat ini?</p>
                                <br>
                                <small class="text-danger">Data tidak dapat diedit kembali apabila telah disetujui</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <form action="" id="form_ajukan" method="POST">
                    <input type="hidden" value="<?= $pengajuan->id ?>" name="pengajuanID">
                    <button type="button" class="btn btn-default font-button" data-dismiss="modal">Tidak</button>
                    <input type="submit" name="btn_ajukan" class="btn btn-primary font-button" value="Iya" id="ajukan">
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#form_ajukan").submit(function () {
            $("#ajukan").val('Memuat...');
            $("#ajukan").addClass('disabled');
        });
        let dateBirth = moment($('#date').val()).format('DD MMMM YYYY');
        $('#date').val(dateBirth != 'Invalid date' ? dateBirth : '');
        let dateBirthCouple = moment($('#tgllhr_pasangan').val()).format('DD MMMM YYYY');
        $('#tgllhr_pasangan').val(dateBirthCouple != 'Invalid date' ? dateBirthCouple : '');
    });

</script>

