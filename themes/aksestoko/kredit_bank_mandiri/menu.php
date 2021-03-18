
<div class="container pt-4 pb-2">
  <h3 class="input-group">
    <a href="<?=base_url(aksestoko_route("aksestoko/home/programs"))?>" class="btn btn-back mr-3"><i class="fal fa-angle-left"></i></a> 
  </h3>
</div>

    <section class="py-main section-account-header">
      <div class="container">
        <div class="heading text-center">
          <h2>Kredit Bank Mandiri</h2>
        </div>

        <div class="content">

          <?php if(!$loan){ ?>
          <div class="row mt-5">
            <div class="col-md-12">
              <div class="box p-box mb-3">
                <div class="row">
                <div class="col-md-2 d-flex align-items-center justify-content-center py-4">
                  <img src="<?= base_url('assets/uploads/bank_logo/mandiri.png') ?>" style="max-width: 100px;">
                </div>
                <div class="col-md-10 d-flex align-items-center justify-content-center">
                    <h5 class="text-center">Anda belum memenuhi syarat pengajuan Kredit Bank Mandiri</h5>
                </div>
                </div>
              </div>
            </div>
          </div>

          <?php }else{ ?>

          <div class="row mt-5">
          <div class="col-md-12">
              <div class="box p-box mb-3">
                <div class="row mb-4">
                  <div class="col-md-2 d-flex align-items-center justify-content-center">
                    <img src="<?= base_url('assets/uploads/bank_logo/mandiri.png') ?>" style="max-width: 100px;">
                  </div>
                  <div class="col-md-5">
                    <label>Status Pinjaman</label>
                    <h5><?= $statusLoan ?></h5>
                  </div>
                  <div class="col-md-5">
                    <div class="">
                      <label for="" class="label-limit">Limit</label>
                      <h5> <?= ($limit=='-' ? $limit : 'Rp '.number_format($limit, 0, ',', '.')) ?></h5>
                    </div>
                  <div>
                </div>
              </div>
            </div>
            <div class="footer-kredit-mandiri">
              <div class="row">
                <div class="col-md-8 col-12 align-self-center">
                  <small id="notifKreditMandiri" class="form-text text-sans-serif <?= $status['type']?> font-italic"><?= $status['message']?></small>
                </div>
                <div class="col-md-4 col-12">
                  <div class="buttonKreditMandiri">
                    <a href="<?= base_url(aksestoko_route('aksestoko/home/form_kredit_bank_mandiri')) ?>" class="btn btn-outline-primary btn-sm mr-2 font-button <?= $status['button_perbarui']?>">Perbarui Data</a>
                    <a href="#confirmKredit" data-toggle="modal" class="btn btn-primary btn-sm font-button <?= $status['button_ajukan']?>">Ajukan Kredit</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        
          
        </div>
        
      </div>
          

          <form class="needs-validation mt-4" action="<?=base_url(aksestoko_route("aksestoko/home/kredit_bank_mandiri"))?>" method="POST">
            <div class="row">
              <div class="col-12">
                <div class="box p-box mb-3">
                  <div class="row">

                    <div class="col-md-4 col-12">
                        <div class="form-group">
                          <label class="" for="">Foto Selfie Bersama KTP</label>
                          <div class="thumnail-files box ">
                            <img class="img-files" src="<?=$loan->foto?>" onerror="this.src='<?= base_url('assets/uploads/no_image.png')?>'" width="100%">
                          </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-12">
                        <div class="form-group">
                          <label class="" for="">Foto KTP</label>
                          <div class="thumnail-files box ">
                            <img class="img-files" src="<?=$loan->foto_ktp?>" onerror="this.src='<?= base_url('assets/uploads/no_image.png')?>'" width="100%">
                          </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-12">
                        <div class="form-group">
                          <label class="" for="">Foto NPWP</label>
                          <div class="thumnail-files box ">
                            <img class="img-files" src="<?=$loan->foto_npwp?>" onerror="this.src='<?= base_url('assets/uploads/no_image.png')?>'" width="100%">
                          </div>
                        </div>
                    </div>

                    <div class="col-md-12 mb-3"></div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label>Seller ID</label>
                          <input type="text" class="form-control bg-white" name="SellerID" value="<?= $loan->SellerID ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label>Limit</label>
                          <input type="text" class="form-control bg-white" name="Limit" value="<?=(float)$loan->Limit?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label>Tenor</label>
                          <input type="text" class="form-control bg-white" name="Tenor" value="<?= (float)$loan->Tenor ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label>No Rekening Mandiri</label>
                          <input type="text" class="form-control bg-white" name="NoRekMandiri" value="<?= $loan->NoRekMandiri ?>"  readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label>No KTP</label>
                          <input type="text" class="form-control bg-white" name="NoKTP"  value="<?= $loan->NoKTP ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label>Masa Berlaku KTP</label>
                          <input type="text" class="form-control  bg-white" name="MasaBerlakuKTP" id="masa_ktp"  value="<?= $loan->MasaBerlakuKTP ?>"  readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label>Nama Lengkap</label>
                          <input type="text" class="form-control bg-white" name="NamaLengkap"  value="<?= $loan->NamaLengkap ?>"  readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label>Jenis Kelamin</label>
                          <input type="text" class="form-control bg-white" name="JenisKelamin"  value="<?= ($loan->JenisKelamin=='M'?'Laki-laki': ($loan->JenisKelamin=='F' ? 'Perempuan' : '')) ?>"  readonly >
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label>Tempat Lahir</label>
                          <input type="text" class="form-control bg-white" name="TempatLahir"  value="<?= $loan->TempatLahir ?>"  readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label>Tanggal Lahir</label>
                          <input type="text" id="date" class="form-control bg-white" name="TanggalLahir" value="<?=$loan->TanggalLahir?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label>No HP</label>
                          <input type="text" class="form-control bg-white" name="NoHP"  value="<?= $loan->NoHP ?>"  readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label>Email</label>
                          <input type="text" class="form-control bg-white" name="Email"  value="<?= $loan->Email ?>"  readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label>NPWP</label>
                          <input type="text" class="form-control bg-white" name="NPWP"  value="<?= $loan->NPWP ?>"  readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label>Nama Ibu Kandung</label>
                          <input type="text" class="form-control bg-white" name="NamaIbuKandung"  value="<?= $loan->NamaIbuKandung ?>"  readonly>
                        </div>
                    </div>

                    <div class="col-md-12 col-12">
                        <div class="form-group">
                          <label>Alamat KTP</label>
                          <textarea id="AlamatKTP" type="text" name="AlamatKTP" class="form-control bg-white" id="" rows="2" value=""  style="resize: none;" readonly> <?= $loan->AlamatKTP?> </textarea> 
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label>Provinsi KTP</label>
                          <input type="text" id="ProvinsiKTP" class="form-control bg-white" name="ProvinsiKTP"  value="<?= $loan->ProvinsiKTP ?>"  readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label>Kabupaten/Kota KTP</label>
                          <input type="text" id="KabupatenKotaKTP" class="form-control bg-white" name="KabupatenKotaKTP"  value="<?= $loan->KabupatenKotaKTP ?>"  readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label>Kecamatan KTP</label>
                          <input type="text" id="KecamatanKTP" class="form-control bg-white" name="KecamatanKTP"  value="<?= $loan->KecamatanKTP ?>"  readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label>Kelurahan KTP</label>
                          <input type="text" id="KelurahanKTP" class="form-control bg-white" name="KelurahanKTP"  value="<?= $loan->KelurahanKTP ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-4 col-12">
                        <div class="form-group">
                          <label>RT KTP</label>
                          <input type="text" id="RTKTP" class="form-control bg-white" name="RTKTP"  value="<?= $loan->RTKTP ?>"  readonly>
                        </div>
                    </div>

                    <div class="col-md-4 col-12">
                        <div class="form-group">
                          <label>RW KTP</label>
                          <input type="text" id="RWKTP" class="form-control bg-white" name="RWKTP"  value="<?= $loan->RWKTP ?>"  readonly>
                        </div>
                    </div>

                    <div class="col-md-4 col-12">
                        <div class="form-group">
                          <label>Kode Pos KTP</label>
                          <input id="KodePosKTP" type="text" class="form-control bg-white" name="KodePosKTP"  value="<?= $loan->KodePosKTP ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-12 col-12">
                        <div class="form-group">
                          <label>Alamat Tinggal</label>
                          <textarea id="AlamatTinggal" type="text" name="AlamatTinggal" class="form-control bg-white" id="" rows="2"  style="resize: none;" readonly > <?= $loan->AlamatTinggal ?> </textarea> 
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label>Provinsi Tinggal</label>
                          <input id="ProvinsiTinggal" type="text" class="form-control bg-white" name="ProvinsiTinggal" value="<?= $loan->ProvinsiTinggal ?>"  readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label>Kabupaten/Kota Tinggal</label>
                          <input type="text" id="KabupatenKotaTinggal" class="form-control bg-white" name="KabupatenKotaTinggal"  value="<?= $loan->KabupatenKotaTinggal ?>"  readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label>Kecamatan Tinggal</label>
                          <input type="text" id="KecamatanTinggal" class="form-control bg-white" name="KecamatanTinggal"  value="<?= $loan->KecamatanTinggal ?>"  readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label>Kelurahan Tinggal</label>
                          <input type="text" id="KelurahanTinggal" class="form-control bg-white" name="KelurahanTinggal"  value="<?= $loan->KelurahanTinggal ?>"  readonly>
                        </div>
                    </div>

                    <div class="col-md-4 col-12">
                        <div class="form-group">
                          <label>RT Tinggal</label>
                          <input type="text" id="RTTinggal" class="form-control bg-white" name="RTTinggal" value="<?= $loan->RTTinggal ?>"  readonly>
                        </div>
                    </div>

                    <div class="col-md-4 col-12">
                        <div class="form-group">
                          <label>RW Tinggal</label>
                          <input type="text" id="RWTinggal" class="form-control bg-white" name="RWTinggal"  value="<?= $loan->RWTinggal ?>"  readonly>
                        </div>
                    </div>

                    <div class="col-md-4 col-12">
                        <div class="form-group">
                          <label>Kode POS Tinggal</label>
                          <input id="KodeposTinggal" type="text" class="form-control bg-white" name="KodeposTinggal"  value="<?= $loan->KodeposTinggal ?>" readonly >
                        </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </form>
           
          <?php } ?>
          
        </div>
      </div>
    </section>

<div class="modal fade" id="confirmKredit" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
        <h4 class="modal-title text-center mobile-title" id="myModalLabel">Konfirmasi Ajukan Kredit</h4>
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
        <form action="<?= base_url(aksestoko_route("aksestoko/home/kredit_bank_mandiri"))?>" id="form_ajukan" method="POST">
          <input type="hidden" value="<?= $loan->id?>" name="loanID">
          <button type="button" class="btn btn-default font-button" data-dismiss="modal">Tidak</button>
          <input type="submit" class="btn btn-primary font-button" value="Iya" id="ajukan">
        </form>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function(){
    $("#form_ajukan").submit(function() {
        $("#ajukan").val('Memuat...');
        $("#ajukan").addClass('disabled');
    });
    let dateBirth = moment($('#date').val()).format('DD MMMM YYYY');
    $('#date').val(dateBirth != 'Invalid date' ? dateBirth : '');
  });

</script>

