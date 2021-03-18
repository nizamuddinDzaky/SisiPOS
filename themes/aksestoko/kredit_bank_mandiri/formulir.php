<div class="container pt-4 pb-2">
  <h3 class="input-group">
    <a href="<?=base_url(aksestoko_route("aksestoko/home/kredit_bank_mandiri"))?>" class="btn btn-back mr-3"><i class="fal fa-angle-left"></i></a> 
  </h3>
</div>

    <section class="py-main section-account-header">
      <div class="container">
        <div class="heading text-center">
          <h2>Formulir Pengajuan Kredit Bank Mandiri</h2>
        </div>

        <div class="content">
          <form class="needs-validation mt-5" enctype="multipart/form-data" action="<?=base_url(aksestoko_route("aksestoko/home/form_kredit_bank_mandiri"))?>" method="POST">
            <div class="row">
              <div class="col-12">
                <div class="box p-box mb-3">
                  <div class="row">
                    <input type="hidden" value="<?=$loan->id ?>" name="loanID">

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">Seller ID</label>
                          <input type="text" class="form-control bg-white text-dark" name="SellerID" value="<?=$loan->SellerID?>" placeholder="Masukkan Seller ID" readonly required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">Limit</label>
                          <input type="text" class="form-control bg-white text-dark" name="Limit" placeholder="Masukkan Limit" value="<?=(float)$loan->Limit?>" readonly >
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">Tenor</label>
                          <input type="text" class="form-control bg-white text-dark" name="Tenor" placeholder="Masukkan Tenor"  value="<?=(float)$loan->Tenor?>" readonly required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">No Rekening Mandiri</label>
                          <input type="text" class="form-control" name="NoRekMandiri" value="<?=$loan->NoRekMandiri?>" placeholder="Masukkan Nomor Rekening Mandiri" required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">No KTP</label>
                          <input type="text" class="form-control" name="NoKTP" placeholder="Masukkan Nomor KTP"  value="<?=$loan->NoKTP?>" required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">Masa Berlaku KTP</label> 
                          <div class="input-group-prepend">
                            <input type="text" class="form-control form-control-custom text-sans-serif datepicker" style="border-right: 0;border-top-right-radius: 0;border-bottom-right-radius: 0;" name="MasaBerlakuKTP" id="masa_ktp"  value="<?=$loan->MasaBerlakuKTP?>" placeholder="Masukkan Masa Berlaku KTP Anda" required>
                            <span id="lifetimeGenerate" style="border-left: 0;border-bottom-left-radius: 0;border-top-left-radius: 0; cursor: pointer;" class="btn input-group-text-v2">Seumur Hidup</span>
                          </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">Nama Lengkap</label>
                          <input type="text" class="form-control" name="NamaLengkap"  value="<?=$loan->NamaLengkap?>" placeholder="Masukkan Nama Lengkap Anda" required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">Jenis Kelamin</label>
                          <div class="custom-control custom-radio custom-control-inline" style="display: table;">
                            <input type="radio" id="rd_1" name="JenisKelamin" class="custom-control-input radio_rb"  value="M" <?=($loan->JenisKelamin == 'M' ? 'checked':'')?> required>
                            <label class="custom-control-label red text-sans-serif" for="rd_1" style="display: table-cell;text-align: center;vertical-align: middle; padding-left:10px;">
                              Laki - laki</label>
                          </div>
                          <div class="custom-control custom-radio custom-control-inline" style="display: table;">
                            <input type="radio" id="rd_2" name="JenisKelamin" class="custom-control-input radio_rb" value="F" <?=($loan->JenisKelamin == 'F' ? 'checked':'')?> required>
                            <label class="custom-control-label red text-sans-serif" for="rd_2" style="display: table-cell;text-align: center;vertical-align: middle; padding-left:10px;">
                              Perempuan
                            </label>
                          </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">Tempat Lahir</label>
                          <input type="text" class="form-control" name="TempatLahir"  value="<?=$loan->TempatLahir?>" placeholder="Masukkan Tempat Lahir Anda" required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">Tanggal Lahir</label>
                          <input type="text" id="date" data-format="YYYY-MM-DD" class="form-control" data-template="DD MMMM YYYY" name="TanggalLahir" value="<?=$loan->TanggalLahir?>">
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">No HP</label>
                          <input type="text" class="form-control" name="NoHP"  value="<?=$loan->NoHP?>" placeholder="Masukkan No HP Anda" required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">Email</label>
                          <input type="text" class="form-control" name="Email"  value="<?=$loan->Email?>" placeholder="Masukkan Email Anda" required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">NPWP</label>
                          <input type="text" class="form-control" name="NPWP"  value="<?=$loan->NPWP?>" placeholder="Masukkan NPWP Anda" required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">Nama Ibu Kandung</label>
                          <input type="text" class="form-control" name="NamaIbuKandung"  value="<?=$loan->NamaIbuKandung?>" placeholder="Masukkan Nama Ibu Kandung Anda" required>
                        </div>
                    </div>

                    <div class="col-md-12 col-12">
                        <div class="form-group">
                          <label class="required" for="">Alamat KTP</label>
                          <textarea id="AlamatKTP" type="text" name="AlamatKTP" class="form-control" id="" placeholder="Masukkan Alamat sesuai KTP Anda" rows="2" value=""  style="resize: none;" required><?=$loan->AlamatKTP?></textarea> 
                        </div>
                    </div>

                    <div class="col-md-4 col-12">
                        <div class="form-group">
                          <label class="required" for="">Kode Pos KTP</label>
                          <input id="KodePosKTP" type="text" class="form-control" name="KodePosKTP" maxlength="5" minlength="5" value="<?=$loan->KodePosKTP?>" placeholder="Masukkan Kode Pos sesuai KTP Anda" required>
                        </div>
                    </div>

                    <div class="col-md-4 col-12">
                        <div class="form-group">
                          <label class="required" for="">RT KTP</label>
                          <input type="text" id="RTKTP" class="form-control" name="RTKTP"  value="<?=$loan->RTKTP?>" placeholder="Masukkan RT sesuai KTP Anda" required>
                        </div>
                    </div>

                    <div class="col-md-4 col-12">
                        <div class="form-group">
                          <label class="required" for="">RW KTP</label>
                          <input type="text" id="RWKTP" class="form-control" name="RWKTP"  value="<?=$loan->RWKTP?>" placeholder="Masukkan RW sesuai KTP Anda" required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">Provinsi KTP</label>
                          <input type="text" id="ProvinsiKTP" class="form-control bg-white text-dark" readonly name="ProvinsiKTP"  value="<?=$loan->ProvinsiKTP?>" placeholder="Masukkan Provinsi sesuai KTP Anda" required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">Kabupaten/Kota KTP</label>
                          <input type="text" id="KabupatenKotaKTP" class="form-control bg-white text-dark" readonly name="KabupatenKotaKTP"  value="<?=$loan->KabupatenKotaKTP?>" placeholder="Masukkan Kabupaten Kota sesuai KTP Anda" required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">Kecamatan KTP</label>
                          <input type="text" id="KecamatanKTP" class="form-control bg-white text-dark" name="KecamatanKTP" readonly value="<?=$loan->KecamatanKTP?>" placeholder="Masukkan Kecamatan sesuai KTP Anda" required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="KelurahanKTP">Kelurahan KTP</label>
                          <select name="KelurahanKTP" id="KelurahanKTP" class="form-control" required>
                            <option value="" disabled <?=$loan->KelurahanKTP && $loan->KelurahanKTP != '' ? '' : 'selected'?>>Masukkan Kelurahan sesuai KTP Anda</option>
                            <?php if($loan->KelurahanKTP && $loan->KelurahanKTP != '') { ?>
                              <option selected><?=$loan->KelurahanKTP?></option>
                            <?php }?>
                          </select>
                        </div>
                    </div>

                    <div class="col-md-12 mb-4 mt-4">
                      <label class="customCheck h6"> Apakah alamat tinggal sama dengan alamat KTP ?
                        <input id="approve-address" type="checkbox" name="sameAddress" <?=$loan->sameAddress == 'on' ? 'checked' : ''?>>
                        <span class="checkmark"></span>
                      </label>
                    </div>

                    <div class="col-12 row" id="detailTinggal">
                    
                    <div class="col-md-12 col-12">
                        <div class="form-group">
                          <label class="required" for="">Alamat Tinggal</label>
                          <textarea id="AlamatTinggal" type="text" name="AlamatTinggal" class="form-control bg-white text-dark" id="" placeholder="Alamat Tinggal Anda" rows="2" value="" style="resize: none;" required><?=$loan->AlamatTinggal?></textarea> 
                        </div>
                    </div>

                    <div class="col-md-4 col-12">
                        <div class="form-group">
                          <label class="required" for="KodeposTinggal">Kode Pos Tinggal</label>
                          <input id="KodeposTinggal" type="text" class="form-control bg-white text-dark" name="KodeposTinggal"  maxlength="5" minlength="5"  value="<?=$loan->KodeposTinggal?>" placeholder="Masukkan Kode POS Anda Tinggal" required>
                        </div>
                    </div>

                    <div class="col-md-4 col-12">
                        <div class="form-group">
                          <label class="required" for="">RT Tinggal</label>
                          <input type="text" id="RTTinggal" class="form-control bg-white text-dark" name="RTTinggal" value="<?=$loan->RTTinggal?>" placeholder="Masukkan RT Anda Tinggal" required>
                        </div>
                    </div>

                    <div class="col-md-4 col-12">
                        <div class="form-group">
                          <label class="required" for="">RW Tinggal</label>
                          <input type="text" id="RWTinggal" class="form-control bg-white text-dark" name="RWTinggal" value="<?=$loan->RWTinggal?>" placeholder="Masukkan RW Anda Tinggal" required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">Provinsi Tinggal</label>
                          <input id="ProvinsiTinggal" type="text" class="form-control bg-white text-dark" readonly name="ProvinsiTinggal" value="<?=$loan->ProvinsiTinggal?>" placeholder="Masukkan Provinsi Anda Tinggal" required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">Kabupaten Kota Tinggal</label>
                          <input type="text" id="KabupatenKotaTinggal" class="form-control bg-white text-dark" readonly name="KabupatenKotaTinggal" value="<?=$loan->KabupatenKotaTinggal?>" placeholder="Masukkan Kabupaten Kota Anda Tinggal" required>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="">Kecamatan Tinggal</label>
                          <input type="text" id="KecamatanTinggal" class="form-control bg-white text-dark" readonly name="KecamatanTinggal" value="<?=$loan->KecamatanTinggal?>" placeholder="Masukkan Kecamatan Anda Tinggal" required>
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label class="required" for="KelurahanTinggal">Kelurahan Tinggal</label>
                          <select name="KelurahanTinggal" id="KelurahanTinggal" class="form-control bg-white text-dark" required>
                            <option value="" disabled <?=$loan->KelurahanTinggal && $loan->KelurahanTinggal != '' ? '' : 'selected'?>>Masukkan Kelurahan Anda Tinggal</option>
                            <?php if($loan->KelurahanTinggal && $loan->KelurahanTinggal != '') { ?>
                              <option selected><?=$loan->KelurahanTinggal?></option>
                            <?php }?>
                          </select>
                        </div>
                    </div>

                    </div>

                    <div class="col-md-4 col-12">
                      <label>Unggah Foto Selfie bersama KTP</label>

                      <div class="custom-file" >
                        <label for="uploadFoto" class="custom-file-upload" style="margin:0;">
                          <i class="fas fa-copy"></i> Pilih File
                        </label>
                        <input type="file" accept=".jpg , .png , .JPEG" class="uploadFoto" id="uploadFoto" name="uploadFoto" style="width: 1px;">
                        <span id="valFoto" style="font-size: 14px; color: #8B8D8E;"><i>Silakan unggah file Anda</i></span>
                      </div>
                      <small class="font-italic text-danger">Pastikan foto wajah dan KTP terlihat jelas. Disarankan : ekstensi file .jpg - ukuran &lt; 15mb</small>
                    </div>

                    <div class="col-md-4 col-12">
                      <label>Unggah KTP</label>

                      <div class="custom-file">
                        <label for="uploadKTP" class="custom-file-upload" style="margin:0;">
                          <i class="fas fa-copy"></i> Pilih File
                        </label>
                        <input type="file" accept=".jpg , .png , .JPEG" class="uploadKTP" id="uploadKTP" name="uploadKTP" style="width: 1px;">
                        <span id="valKTP" style="font-size: 14px; color: #8B8D8E;"><i>Silakan unggah file Anda</i></span>
                      </div>
                      <small class="font-italic text-danger">Disarankan : ekstensi file .jpg - ukuran &lt; 15mb</small>
                    </div>

                    <div class="col-md-4 col-12">
                      <label>Unggah NPWP</label>

                      <div class="custom-file">
                        <label for="uploadNPWP" class="custom-file-upload" style="margin:0;">
                          <i class="fas fa-copy"></i> Pilih File
                        </label>
                        <input type="file" accept=".jpg , .png , .JPEG" class="uploadNPWP" id="uploadNPWP" name="uploadNPWP" style="width: 1px;">
                        <span id="valNPWP" style="font-size: 14px; color: #8B8D8E;"><i>Silakan unggah file Anda</i></span>
                      </div>
                      <small class="font-italic text-danger">Disarankan : ekstensi file .jpg - ukuran &lt; 15mb</small>
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
  $(document).ready(function(){
      $('#loader-section').fadeOut(200);

      $('#lifetimeGenerate').click(function(){
        $("#masa_ktp").val('Seumur Hidup');
      });

      $(".datepicker").datepicker({
          dateFormat: 'yy-mm-dd'
      });

      $("#approve-address").on('change', function(){
        if($("#approve-address").is(':checked')){
          $('#detailTinggal').fadeOut(200);
          $('#AlamatTinggal').removeAttr('required', '');
          $('#KodeposTinggal').removeAttr('required', '');
          $('#ProvinsiTinggal').removeAttr('required', '');
          $('#KabupatenKotaTinggal').removeAttr('required', '');
          $('#KecamatanTinggal').removeAttr('required', '');
          $('#KelurahanTinggal').removeAttr('required', '');
          $('#RTTinggal').removeAttr('required', '');
          $('#RWTinggal').removeAttr('required', '');

        }else{
          $('#detailTinggal').fadeIn(200);
          $('#AlamatTinggal').attr('required', '');
          $('#KodeposTinggal').attr('required', '');
          $('#ProvinsiTinggal').attr('required', '');
          $('#KabupatenKotaTinggal').attr('required', '');
          $('#KecamatanTinggal').attr('required', '');
          $('#KelurahanTinggal').attr('required', '');
          $('#RTTinggal').attr('required', '');
          $('#RWTinggal').attr('required', '');

        }
      }).change();

      $(".uploadFoto").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings("#valFoto").addClass("selected").html(fileName);
      });

      $(".uploadKTP").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings("#valKTP").addClass("selected").html(fileName);
      });

      $(".uploadNPWP").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings("#valNPWP").addClass("selected").html(fileName);
      });

      $('#date').combodate({
        customClass: 'form-control formulir',
        minYear: 1945,
        maxYear: (new Date()).getFullYear()
      });
      $('.formulir').attr('required', true); 

      function setKosongDetailKTP() {
        $('#ProvinsiKTP').val('');
        $('#KabupatenKotaKTP').val('');
        $('#KecamatanKTP').val('');
        $('#KelurahanKTP').html(`<option value="" disabled selected >Masukkan Kelurahan sesuai KTP Anda</option>`);
        $('#loader-section').fadeOut(200);
      }

      var lastKodePOSKTP = '';
      var kelurahanValue = `<?=trim($loan->KelurahanKTP)?>`;
      $("#KodePosKTP").on("keyup change", function () {
        if(lastKodePOSKTP === this.value) {
          return;
        }
        lastKodePOSKTP = this.value;
        if(this.value.length != 5){
          setKosongDetailKTP();
          return
        }
        $("#loader-section").show();
        let option = `<option value="" disabled>Masukkan Kelurahan sesuai KTP Anda</option>`;
        
        $.get(`<?=base_url(aksestoko_route('aksestoko/home/kodepos/'))?>${this.value}`, function(data, status){
          let daerah = (JSON.parse(data));
          if(!daerah || daerah.kodepos[0].provinsi == 'provinsi') {
            setKosongDetailKTP();
            alertCustom('Kode POS Tidak Ditemukan', 'danger', 10001);
            return
          }
          for (const d of daerah.kodepos) {
            option += `<option value="${d.kelurahan}">${d.kelurahan}</option>`;
          }
          $('#ProvinsiKTP').val(daerah.kodepos[0].provinsi);
          $('#KabupatenKotaKTP').val(daerah.kodepos[0].kabupaten);
          $('#KecamatanKTP').val(daerah.kodepos[0].kecamatan);
          $('#KelurahanKTP').html(option);
          $('#KelurahanKTP').val(kelurahanValue && kelurahanValue != '' ? kelurahanValue : daerah.kodepos[0].kelurahan);
          kelurahanValue = ''
          $('#loader-section').fadeOut(200);
        });
      }).change();

      function setKosongDetailTinggal() {
        $('#ProvinsiTinggal').val('');
        $('#KabupatenKotaTinggal').val('');
        $('#KecamatanTinggal').val('');
        $('#KelurahanTinggal').html(`<option value="" disabled selected >Masukkan Kelurahan Anda Tinggal</option>`);
        $('#loader-section').fadeOut(200);
      }

      var lastKodePOSTinggal = $("#KodePosKTP").val();
      var kelurahanTinggalValue = `<?=trim($loan->KelurahanTinggal)?>`;
      $("#KodeposTinggal").on("keyup change", function () {
        if(lastKodePOSTinggal === this.value) {
          return;
        }
        lastKodePOSTinggal = this.value;
        if(this.value.length != 5){
          setKosongDetailTinggal();
          return
        }
        $("#loader-section").show();
        let option = `<option value="" disabled>Masukkan Kelurahan Anda Tinggal</option>`;
        
        $.get(`<?=base_url(aksestoko_route('aksestoko/home/kodepos/'))?>${this.value}`, function(data, status){
          let daerah = (JSON.parse(data));
          if(!daerah || daerah.kodepos[0].provinsi == 'provinsi') {
            setKosongDetailTinggal();
            alertCustom('Kode POS Tidak Ditemukan', 'danger', 10001);
            return
          }
          for (const d of daerah.kodepos) {
            option += `<option value="${d.kelurahan}">${d.kelurahan}</option>`;
          }
          $('#ProvinsiTinggal').val(daerah.kodepos[0].provinsi);
          $('#KabupatenKotaTinggal').val(daerah.kodepos[0].kabupaten);
          $('#KecamatanTinggal').val(daerah.kodepos[0].kecamatan);
          $('#KelurahanTinggal').html(option);
          $('#KelurahanTinggal').val(kelurahanTinggalValue && kelurahanTinggalValue != '' ? kelurahanTinggalValue : daerah.kodepos[0].kelurahan);
          kelurahanTinggalValue = ''
          $('#loader-section').fadeOut(200);
        });
      }).change();
  });
</script>
