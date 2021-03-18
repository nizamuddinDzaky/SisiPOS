<div class="container pt-4 pb-2">
  <h3 class="input-group">
    <a href="<?=base_url(aksestoko_route("aksestoko/home/main"))?>" class="btn btn-back mr-3"><i class="fal fa-angle-left"></i></a> 
  </h3>
</div>

    <section class="py-main section-account-header">
      <div class="container">
        <div class="account-header clearfix">
          <div class="profile-img">
            <!-- <img src="<?=base_url('assets/uploads/avatars/thumbs/') . $profile->avatar?>" class="account-header-img img-fluid" alt="Profile">  -->
          </div>
          <div class="account-header-info">
            <h4 class="nama-profile"><?= $profile->first_name . " " . $profile->last_name?></h4>
            <p><?=$profile->email?></p>
            <!-- <p>Mitra Jaya</p> -->
          </div>
        </div>
      </div>
      <img src="../assets/img/brand/logo_holcim-icon_dark.png" class="bg-brand" alt="Holcim Icon">
    </section>

    <section class="section-account-edit">
      <div class="container">

        <div class="account-edit-content">
          <div class="row row-0">
            <div class="col-md-4">
              <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="<?=isset($_GET['profile']) || (!isset($_GET['address']) && !isset($_GET['password']) && !isset($_GET['terms']) && !isset($_GET['tooltip']) && !isset($_GET['salesperson'])) ? 'active' : ''?>">
                  <a href="#editProfile" aria-controls="editProfile" role="tab" data-toggle="tab" id="editProfileMobile" aria-expanded="false">Perbarui Profil</a>
                </li>
                <li role="presentation" class="<?=isset($_GET['address']) ? 'active' : ''?>">
                  <a href="#editAddress" aria-controls="editAddress" role="tab" data-toggle="tab" id="editAddressMobile" aria-expanded="true">Daftar Alamat</a>
                </li>
                
                <li role="presentation" class="<?=isset($_GET['salesperson']) ? 'active' : ''?>">
                  <a href="#salesPerson" aria-controls="salesPerson" role="tab" data-toggle="tab" id="salesPersonMobile" aria-expanded="true">Salesperson</a>
                </li>

                <li role="presentation" class="<?=isset($_GET['password']) ? 'active' : ''?>">
                  <a href="#editPassword" aria-controls="editPassword" role="tab" data-toggle="tab" id="editPasswordMobile" aria-expanded="false">Ganti Password</a>
                </li>
                <li role="presentation" class="<?=isset($_GET['tooltip']) ? 'active' : ''?>">
                  <a href="#editTooltip" aria-controls="tooltip" role="tab" data-toggle="tab" id="tooltipMobile" aria-expanded="false">Petunjuk Penggunaan</a>
                </li>
                <!-- <li role="presentation">
                  <a href="#pushNotification" aria-controls="pushNotification" role="tab" data-toggle="tab" id="pushNotificationMobile">Push Notification</a>
                </li> -->
                <li role="presentation" class="<?=isset($_GET['terms']) ? 'active' : ''?>">
                  <a href="#termsOfUse" aria-controls="termsOfUse" role="tab" data-toggle="tab" id="termOfUseMobile">Syarat dan Ketentuan</a>
                </li>
                <li role="presentation">
                  <a href="<?=base_url(aksestoko_route('aksestoko/auth/logout'))?>">Keluar</a>
                </li>
              </ul>
            </div>
            <div class="col-md-8">
              <div class="tab-content">
                <div role="tabpanel" class="tab-pane <?=isset($_GET['profile']) || (!isset($_GET['address']) && !isset($_GET['password']) && !isset($_GET['terms']) && !isset($_GET['tooltip']) && !isset($_GET['salesperson'])) ? 'active' : ''?>" id="editProfile">
                  <div class="header d-block d-md-none clearfix">
                    <a class="btn btn-back mb-4" id="editProfileMobileBack">
                      <i class="fal fa-chevron-left"></i>
                    </a>
                    <div class="subheading">
                      <h2>Perbarui Profil</h2>
                    </div>
                  </div>
                  <div class="subheading">
                    <h2 class="d-none d-md-block">Perbarui Profil</h2>
                    <p>Memperbarui data pribadi</p>
                  </div>
                  <form class="needs-validation mt-4" method="POST" action="<?=base_url(aksestoko_route('aksestoko/auth/update_profile'))?>">
                    <div class="row">
                      <div class="col-12 col-md-6">
                        <div class="form-group">
                          <label for="">ID Bisnis Kokoh</label>
                          <h5><?=$profile->username?></h5>
                          <!-- <small class="text-danger font-italic">Hubungi kami untuk memperbarui ID Bisnis Kokoh</small> -->
                         
                        </div>
                      </div>
                      <div class="col-12 col-md-6">
                        <div class="form-group">
                          <label for="">Nama Toko</label>
                          <h5><?=$profile->company?></h5>
                          <input type="hidden" name="store_name" value="<?=$profile->company?>">
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-12 col-md-6">
                        <div class="form-group">
                          <label for="" class="required">Nama Depan</label>
                          <input type="text" class="form-control"  name="first_name" placeholder="Masukkan nama depan" value="<?=$profile->first_name?>" required="">
                        </div>
                      </div>
                      <div class="col-12 col-md-6">
                        <div class="form-group">
                          <label for="" class="required">Nama Belakang</label>
                          <input type="text" class="form-control"  name="last_name" placeholder="Masukkan nama belakang" value="<?=$profile->last_name?>" required="">
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-12 col-md-6">
                        <div class="form-group">
                          <label for="" class="required">Email</label>
                          <input type="email" class="form-control" name="email" placeholder="Masukkan email" value="<?=$profile->email?>">
                        </div>
                      </div>
                      <div class="col-12 col-md-6">
                        <div class="form-group">
                          <label for="" class="required">No. Telepon</label> 
                          <?php if($profile->phone_is_verified){ ?>
                            <small class="ml-2"> <i class="text-success"> <i class="fa fa-check"></i> Terverifikasi </i> </small>
                          <?php } else { ?>
                            <small class="ml-2"> <i class="text-danger"> <i class="fa fa-times"></i> Belum Terverifikasi </i> </small>
                          <?php } ?>

                          <div class="input-group-prepend">

                              <span class="input-group-text" style="border-right: 0;"><img src="<?=$assets_at?>img/common/flag_indonesia.png" class="img-flag-phone">+62</span>
                              <input id="phoneUpdateProfile" style="border-left: 0;border-top-left-radius: 0;border-bottom-left-radius: 0;" maxlength="16" type="text" class="form-control phoneFormat" placeholder="No Telepon" value="<?=$profile->phone?>" required="">
                              <!-- <input type="text" name="" id="phoneUser" value="<?=$profile->phone?>" style="display: none;"> -->
                            </div>
                          <div style="display: none;">
                            <input type="text" class="form-control phoneFormatHidden" name="phone" placeholder="No Telepon" required="">
                          </div>
                          <div class="validationPhone">
                            <span id="notifValidationPhone"></span>
                          </div>
                          <?php if(!$profile->phone_is_verified){ ?>
                            <small> <a href="#phoneVerifier" data-toggle="modal">Verifikasi No Telepon?</a> </small>
                          <?php } else { ?>
                            <small class="text-danger" style="display: none" id="phoneChange"> <i> Jika Anda mengganti No Telepon maka dibutuhkan verifikasi ulang </i> </small>
                          <?php } ?>
                        </div>
                      </div>
                    </div>
                    <div class="clearfix mt-4">
                      <button id="btnSimpan" class="btn btn-primary float-right" type="submit">Simpan</button>
                    </div>
                  </form>
                  <hr class="my-5">
                  <h5>Informasi KTP</h5>
                  <form class="needs-validation mt-4" method="POST" action="<?=base_url(aksestoko_route('aksestoko/auth/update_ktp'))?>" enctype="multipart/form-data">
                    <div class="row">
                      <div class="col-md-6 col-12 mb-3">
                        <label>KTP</label>
                        <img src="<?=$profile->photo_ktp ?? $assets_at . '/img/ktp-none.png'?>" alt="KTP" id="ktp-img" class="rounded mx-auto d-block" style="width: 96%">
                      </div>

                      <div class="col-md-6 col-12">
                        <label for="uploadKTP" class="required">Unggah KTP</label>

                        <div class="custom-file">
                          <label for="uploadKTP" class="custom-file-upload" style="margin:0;">
                            <i class="fas fa-copy"></i> Pilih File
                          </label>
                          <input type="file" accept=".jpg , .png , .JPEG" class="uploadKTP" id="uploadKTP" name="uploadKTP" style="width: 1px;" required>
                          <span id="valKTP" style="font-size: 14px; color: #8B8D8E;"><i>Silakan unggah file Anda</i></span>
                        </div>
                        <small class="font-italic text-danger">Disarankan : ekstensi file .jpg - ukuran &lt; 15mb</small>
                      </div>
                    </div>
                    <div class="clearfix mt-4">
                      <button id="btnSimpan" class="btn btn-primary float-right" type="submit">Simpan</button>
                    </div>
                  </form>
                </div>

                <div role="tabpanel" class="tab-pane <?=isset($_GET['salesperson']) ? 'active' : ''?>" id="salesPerson">
                  <div class="header d-block d-md-none clearfix">
                    <a class="btn btn-back mb-4" id="salesPersonMobileBack">
                      <i class="fal fa-chevron-left"></i>
                    </a>
                    <div class="subheading">
                      <h2>Salesperson</h2>
                    </div>
                  </div>
                  <div class="subheading">
                    <h2 class="d-none d-md-block">Salesperson</h2>
                    <p>Data Salesperson yang mendaftarkan toko</p>
                  </div>
                    <?php if ($profile->sales_person_ref) { ?>
                    <div class="row">
                      <div class="col-12 col-md-6">
                        <div class="form-group">
                          <label for="">Nama</label>
                          <h5><?=$sales_person->name?></h5>
                          <!-- <small class="text-danger font-italic">Hubungi kami untuk memperbarui ID Bisnis Kokoh</small> -->
                         
                        </div>
                      </div>
                      <div class="col-12 col-md-6">
                        <div class="form-group">
                          <label for="">Reference No</label>
                          <h5><?=$sales_person->reference_no?></h5>
                          
                          <input type="hidden" name="store_name" value="<?=$profile->company?>">
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-12 col-md-6">
                        <div class="form-group">
                          <label for="">Email</label>
                          <h5><?=$sales_person->email?></h5>
                          <!-- <small class="text-danger font-italic">Hubungi kami untuk memperbarui ID Bisnis Kokoh</small> -->
                         
                        </div>
                      </div>
                      <div class="col-12 col-md-6">
                        <div class="form-group">
                          <label for="">No HP</label>
                          <h5><?=$sales_person->phone?></h5>
                          <input type="hidden" name="store_name" value="<?=$profile->company?>">
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-12 col-md-12">
                        <div class="form-group">
                          <label for="">Alamat</label>
                          <h5><?=$sales_person->address?>, <?=$sales_person->state?>, <?=$sales_person->city?>, <?=$sales_person->country?></h5>
                          <!-- <small class="text-danger font-italic">Hubungi kami untuk memperbarui ID Bisnis Kokoh</small> -->
                         
                        </div>
                      </div>
                      
                    </div>
                    <?php }else{ ?>
                    <form class="needs-validation mt-4" method="POST" action="<?=base_url(aksestoko_route('aksestoko/auth/update_sales_person'))?>">
                      <div class="row">
                        <div class="col-12 col-md-6">
                          <div class="form-group">
                            <label for="" class="required">Kode Referal</label>
                            <input type="text" class="form-control"  name="sales_person" placeholder="Kode Referal" required="true">
                          </div>
                        </div>
                      </div>
                      <div class="clearfix mt-4">
                        <button id="btnSimpan" class="btn btn-primary float-right" type="submit">Simpan</button>
                      </div>
                    </form>
                    <?php } ?>
                </div>

                <div role="tabpanel" class="tab-pane <?=isset($_GET['address']) ? 'active' : ''?>" id="editAddress">
                  <div class="header d-block d-md-none clearfix">
                    <a class="btn btn-back mb-4" id="editAddressMobileBack">
                      <i class="fal fa-chevron-left"></i>
                    </a>
                    <div class="subheading">
                      <h2>Daftar Alamat Toko</h2> 
                    </div>
                  </div>
                  <div class="subheading">
                    <h2 class="d-none d-md-block">Daftar Alamat Toko</h2>
                    <p>Daftar alamat toko yang dimiliki atau menambah alamat baru</p>
                  </div>

                  <input type="hidden" id="addr-length" value="<?php echo sizeof($addresses); ?>">
                  <?php foreach ($addresses as $i => $address) { ?>
                  
                  <div class="address-box box">
                    <div class="row">
                      <div class="col-md-9">
                        <div class="heading">
                          <i class="fal fa-home"></i>
                          <h4><?=$address->company?></h4>
                        </div> 
                      </div>

                      <?php if ($i != 0) { ?> 
                      <div class="col-md-3">
                        <div class="text-right">
                          <i class="fal fa-trash-alt delete-cart text-primary delete-alamat" data-id="<?=$address->id?>" data-name="<?=$address->company?>"></i>
                        </div>
                      </div>
                      <?php } ?>
                    </div>

                    <p><?=$address->name?>, <?=$address->phone?> <br> <?=trim($address->address)?>, <?=ucwords(strtolower($address->village))?>, <?=ucwords(strtolower($address->state))?>, <?=ucwords(strtolower($address->city))?>, <?=ucwords(strtolower($address->country))?> - <?=$address->postal_code?></p>

                    <div class="text-right">
                      <a href="javascript:void(0)" class="btn perbarui-alamat" onclick="$('#updateAddress<?=$address->id?>').toggle(1000)">Perbarui Alamat</a>                    
                    </div>

                    <div class="my-3" id="updateAddress<?=$address->id?>" style="display: none;">
                      <h5>Perbarui Alamat Toko</h5>
                      <!-- START: First Step -->
                      <form class="needs-validation mt-4" action="<?=base_url(aksestoko_route('aksestoko/auth/update_address/')) . $address->id?>" method="POST">
                        <div class="row">
                          <div class="col-12 col-md-12" style="display: none;">
                            <div class="form-group">
                              <label for="" class="required">Nama Toko/Proyek</label>
                              <input type="text" class="form-control" name="company" placeholder="Nama Toko/Proyek" value="<?=$address->company?>" required="" readonly>
                            </div>
                          </div>
                          <div class="col-12 col-md-12">
                            <div class="form-group">
                              <label for="" class="required">Nama Penerima</label>
                              <input type="text" class="form-control" name="name" placeholder="Nama Penerima" value="<?=$address->name?>" required="">
                            </div>
                          </div>
                          <div class="col-12 col-md-6">
                            <div class="form-group">
                              <label for="">Email</label>
                              <input type="email" class="form-control" name="email" placeholder="Email" value="<?=$address->email?>">
                            </div>
                          </div>
                          <div class="col-12 col-md-6">
                            <div class="form-group">
                              <label for="" class="required">No. Telepon</label>
                              <div class="input-group-prepend">

                                <span class="input-group-text" style="border-right: 0;">
                                  <img src="<?=$assets_at?>img/common/flag_indonesia.png" class="img-flag-phone">+62</span>
                                  <input style="border-left: 0;border-top-left-radius: 0;border-bottom-left-radius: 0;" type="text" maxlength="16" class="form-control phoneFormat phoneAddress" placeholder="No Telepon" value="<?=$address->phone?>" required="">
                              </div>
                              <div class="validationPhone">
                                <span class="notifValidationPhoneAddress"></span>
                              </div>
                              <div style="display: none;">
                                <input type="text" class="form-control phoneFormatHidden" name="phone" placeholder="No Telepon" value="<?=$address->phone?>" required="">
                              </div>
                            </div>
                          </div>
                          
                          <div class="col-12 col-md-12">
                            <div class="form-group">
                              <label for="" class="required">Alamat</label>
                              <input type="textarea" class="form-control" name="address" placeholder="Alamat" rows="1" value="<?=$address->address?>">
                            </div>
                          </div>
                        
                          <div class="col-lg-6 col-md-12 col-sx-12">
                            <div class="form-group">
                              <label for="" class="required">Provinsi</label>
                              <select id="provinsiAreaUpdate<?=$address->id?>" name="provinsiAreaUpdate" data-id="<?=$address->id?>" class="form-control provUpdate" data-value="<?=$address->country?>" required>
                                
                              </select>
                              <input type="hidden" class="form-control" id="provinsi_update_<?=$address->id?>" name="country" value="<?=$address->country?>">
                            </div>
                          </div>
                          
                          <div class="col-lg-6 col-md-12 col-sx-12">
                            <div class="form-group">
                              <label for="" class="required">Kabupaten</label>
                              <select id="kabupatenAreaUpdate<?=$address->id?>" name="kabupatenAreaUpdate" class="form-control kabUpdate" data-id="<?=$address->id?>" data-value="<?=$address->city?>" required></select>
                              <input type="hidden" class="form-control" id="city_update_<?=$address->id?>" name="city" value="<?=$address->city?>">
                            </div>
                          </div>

                          <div class="col-lg-4 col-md-12 col-sx-12">
                            <div class="form-group">
                              <label for="" class="required">Kecamatan</label>
                              <select id="kecamatanAreaUpdate<?=$address->id?>" class="form-control kecUpdate" data-id="<?=$address->id?>" data-value="<?=$address->state?>" required>
                              </select>
                              <input type="hidden" class="form-control" id="state_update_<?=$address->id?>" name="state" value="<?=$address->state?>">
                            </div>
                          </div>

                          <div class="col-lg-4 col-md-12 col-sx-12">
                            <div class="form-group">
                              <label for="" class="required">Desa</label>
                              <select id="desaAreaUpdate<?=$address->id?>" class="form-control desUpdate" data-id="<?=$address->id?>" data-value="<?=$address->village?>" required>
                              </select>
                              <input type="hidden" class="form-control" id="village_update_<?=$address->id?>" name="village" value="<?=$address->village?>">
                            </div>
                          </div>

                          <div class="col-lg-4 col-md-12 col-sx-12">
                            <div class="form-group">
                              <label for="" class="required">Kode Pos</label>
                              <input type="text" class="form-control" name="postal_code" placeholder="Kode Pos" value="<?=$address->postal_code?>" required="">
                            </div>
                          </div>
                        </div>



                        <div class="clearfix mt-4">
                          <button id="perbaruiAlamat" class="btn btn-primary float-right perbaruiAlamat" type="submit">Perbarui Alamat</button>
                        </div>
                      </form>
                      <!-- END: First Step -->
                    </div>
                  </div>
                  
                  <?php } ?>
                  
                  <?php if(count($addresses) <= 3){ ?>
                    <div class="text-right mt-4">
                      <a href="javascript:void(0)" onclick="$('#newAddress').toggle(1000)" >Tambah Alamat</a>                    
                    </div>
                  <?php } ?>

                  <div class="mt-3" id="newAddress" style="display: none">
                    <h5>Tambahkan Alamat Baru</h5>
                    <!-- START: First Step -->
                    <form class="needs-validation mt-4" action="<?=base_url(aksestoko_route('aksestoko/auth/add_address'))?>" method="POST">
                      <div class="row">
                        <div class="col-12 col-md-12" style="display: none;">
                          <div class="form-group">
                            <label for="" class="required">Nama Toko/Proyek</label>
                            <input type="text" class="form-control" name="company" placeholder="Nama Toko/Proyek" value="<?=$profile->company?>" readonly required="">
                          </div>
                        </div>
                        <div class="col-12 col-md-12">
                          <div class="form-group">
                            <label for="" class="required">Nama Penerima</label>
                            <input type="text" class="form-control" name="name" placeholder="Nama Penerima" value="" required="">
                          </div>
                        </div>
                        <div class="col-12 col-md-6">
                          <div class="form-group">
                            <label for="">Email</label>
                            <input type="email" class="form-control" name="email" placeholder="Email" value="">
                          </div>
                        </div>
                        <div class="col-12 col-md-6">
                          <div class="form-group">
                            <label for="" class="required">No. Telepon</label>
                            <div class="input-group-prepend">

                              <span style="border-right: 0;" class="input-group-text"><img src="<?=$assets_at?>img/common/flag_indonesia.png" class="img-flag-phone">+62</span>
                              <input style="border-left: 0;border-top-left-radius: 0;border-bottom-left-radius: 0;" maxlength="16" type="text" class="form-control phoneFormat phoneAddress" id="phoneFormatAdd" placeholder="No Telepon" value="" required="">
                            </div>
                          </div>
                          <div class="validationPhone">
                            <span class="notifValidationPhoneAddress"></span>
                          </div>
                          <div style="display: none;">
                            <input type="text" class="form-control phoneFormatHidden" name="phone" placeholder="No Telepon" value="<?=$address->phone?>" required="">
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-lg-12 col-md-12">
                          <div class="form-group">
                            <label for="" class="required">Alamat</label>
                            <input type="textarea" class="form-control" name="address" placeholder="Alamat" rows="1">
                          </div>
                        </div>
                        <div class="col-lg-6 col-md-12 col-xs-12">
                          <div class="form-group">
                            <label for="" class="required">Provinsi</label>
                            <select id="provinsiArea" class="form-control" required=""></select>
                            <input type="hidden" class="form-control" id="country_add" name="country" value="">
                          </div>
                        </div>
                        <div class="col-lg-6 col-md-12 col-xs-12">
                          <div class="form-group">
                            <label for="" class="required">Kabupaten</label>
                            <select id="kabupatenArea" class="form-control" required="">
                              <option selected value="">Pilih Provinsi Terlebih Dahulu</option>
                            </select>
                            <input type="hidden" class="form-control" id="city_add" name="city" placeholder="Kota/Kabupaten" value="">
                          </div>
                        </div>

                        <div class="col-lg-4 col-md-12 col-xs-12">
                          <div class="form-group">
                            <label for="" class="required">Kecamatan</label>
                            <select id="kecamatanArea" class="form-control" required="">
                               <option selected value="">Pilih Kabupaten Terlebih Dahulu</option>
                            </select>
                            <input type="hidden" class="form-control" id="state_add" name="state" placeholder="Kecamatan" value="">
                          </div>
                        </div>

                        <div class="col-lg-4 col-md-12 col-xs-12">
                          <div class="form-group">
                            <label for="" class="required">Desa</label>
                            <select id="desaArea" class="form-control" required="">
                               <option selected value="">Pilih Kecamatan Terlebih Dahulu</option>
                            </select>
                            <input type="hidden" class="form-control" id="village_add" name="village" placeholder="Desa/Kelurahan" value="">
                          </div>
                        </div>

                        <div class="col-lg-4 col-md-12 col-xs-12">
                          <div class="form-group">
                            <label for="" class="required">Kode Pos</label>
                            <input type="text" class="form-control"  name="postal_code" placeholder="Kode Pos" value="" required="">
                          </div>
                        </div>
                      </div>

                      <div class="clearfix mt-4">
                        <button class="btn btn-primary float-right perbaruiAlamat" type="submit">Tambah Alamat</button>
                      </div>
                    </form>
                    <!-- END: First Step -->
                  </div>
                </div>
                <div role="tabpanel" class="tab-pane <?=isset($_GET['password']) ? 'active' : ''?>" id="editPassword">
                  <div class="header d-block d-md-none clearfix">
                    <a class="btn btn-back mb-4" id="editPasswordMobileBack">
                      <i class="fal fa-chevron-left"></i>
                    </a>
                    <div class="subheading">
                      <h2>Ganti Password</h2>
                      <p>Mengganti kata sandi untuk login</p>
                    </div>
                  </div>
                  <div class="subheading d-none d-md-block">
                    <h2>Ganti Password</h2>
                    <p>Mengganti kata sandi untuk login</p>
                  </div>
                  <form class="needs-validation mt-4" action="<?=base_url(aksestoko_route('aksestoko/auth/update_password'))?>" method="POST">
                    <div class="row">
                      <div class="col-12">
                        <div class="form-group">
                          <input type="password" class="form-control" id="old_password" name="old_password" placeholder="Password Lama" required="">
                          <i id="show-password-old" class="fa fa-eye"></i>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-12">
                        <div class="form-group">
                          <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Password Baru" required="">
                          <i id="show-password-new" class="fa fa-eye"></i>
                          <small id="passwordlHelp" class="form-text text-left text-sans-serif text-danger font-italic">Kata Sandi minimal 8 karakter kombinasi dari huruf besar, huruf kecil dan angka</small>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-12">
                        <div class="form-group">
                          <input type="password" class="form-control" id="retype_new_password" name="retype_new_password" placeholder="Ulangi Password Baru" required="">
                          <i id="show-password-retype" class="fa fa-eye"></i>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-12">
                        <div class="clearfix mt-4">
                          <button class="btn btn-primary float-right" type="submit">Simpan</button>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>

                <!-- Tooltip -->
                <div role="tabpanel" class="tab-pane <?=isset($_GET['tooltip']) ? 'active' : ''?>" id="editTooltip">
                  <div class="header d-block d-md-none clearfix">
                    <a class="btn btn-back mb-4" id="tooltipMobileBack">
                      <i class="fal fa-chevron-left"></i>
                    </a>
                    <div class="subheading">
                      <h2>Petunjuk Penggunaan</h2>
                    </div>
                  </div>
                  <div class="subheading">
                    <h2 class="d-none d-md-block">Petunjuk Penggunaan</h2>
                    <p>Tekan switch pada setiap halaman untuk menyalakan/mematikan petunjuk penggunaan (tooltip)</p>
                  </div>

                  <div class="tooltip-box box">
                    <div class="row">
                      <div class="col-9">
                        <div class="heading">
                          <h4 class="tooltip-setting">Select Distributor</h4>
                        </div> 
                      </div>
                      <div class="col-3">
                        <label class="switch">
                          <input type="checkbox" class="switcher-tooltip" data-switch="select_distributor" <?=$guide->select_distributor == 0 ? 'checked' : '' ?>>
                          <span class="slider round"></span>
                        </label>
                      </div>
                    </div>
                  </div>

                  <div class="tooltip-box box">
                    <div class="row">
                      <div class="col-9">
                        <div class="heading">
                          <h4 class="tooltip-setting">Dashboard</h4>
                        </div> 
                      </div>
                      <div class="col-3">
                        <label class="switch">
                          <input type="checkbox" class="switcher-tooltip" data-switch="dashboard" <?=$guide->dashboard == 0 ? 'checked' : '' ?>>
                          <span class="slider round"></span>
                        </label>
                      </div>
                    </div>
                  </div>

                  <div class="tooltip-box box">
                    <div class="row">
                      <div class="col-9">
                        <div class="heading">
                          <h4 class="tooltip-setting">Halaman Keranjang</h4>
                        </div> 
                      </div>
                      <div class="col-3">
                        <label class="switch">
                          <input type="checkbox" class="switcher-tooltip" data-switch="cart" <?=$guide->cart == 0 ? 'checked' : '' ?>>
                          <span class="slider round"></span>
                        </label>
                      </div>
                    </div>
                  </div>

                  <div class="tooltip-box box">
                    <div class="row">
                      <div class="col-9">
                        <div class="heading">
                          <h4 class="tooltip-setting">Halaman Checkout</h4>
                        </div> 
                      </div>
                      <div class="col-3">
                        <label class="switch">
                          <input type="checkbox" class="switcher-tooltip" data-switch="checkout" <?=$guide->checkout == 0 ? 'checked' : '' ?>>
                          <span class="slider round"></span>
                        </label>
                      </div>
                    </div>
                  </div>

                  <div class="tooltip-box box">
                    <div class="row">
                      <div class="col-9">
                        <div class="heading">
                          <h4 class="tooltip-setting">Halaman Order</h4>
                        </div> 
                      </div>
                      <div class="col-3">
                        <label class="switch">
                          <input type="checkbox" class="switcher-tooltip" data-switch="order" <?=$guide->order == 0 ? 'checked' : '' ?>>
                          <span class="slider round"></span>
                        </label>
                      </div>
                    </div>
                  </div>

                  <div class="tooltip-box box">
                    <div class="row">
                      <div class="col-9">
                        <div class="heading">
                          <h4 class="tooltip-setting">Detail Order</h4>
                        </div> 
                      </div>
                      <div class="col-3">
                        <label class="switch">
                          <input type="checkbox" class="switcher-tooltip" data-switch="order_detail" <?=$guide->order_detail == 0 ? 'checked' : '' ?>>
                          <span class="slider round"></span>
                        </label>
                      </div>
                    </div>
                  </div>

                  <div class="tooltip-box box">
                    <div class="row">
                      <div class="col-9">
                        <div class="heading">
                          <h4 class="tooltip-setting">Payment</h4>
                        </div> 
                      </div>
                      <div class="col-3">
                        <label class="switch">
                          <input type="checkbox" class="switcher-tooltip" data-switch="payment" <?=$guide->payment == 0 ? 'checked' : '' ?>>
                          <span class="slider round"></span>
                        </label>
                      </div>
                    </div>
                  </div>


                  <div class="tooltip-box box">
                    <div class="row">
                      <div class="col-9">
                        <div class="heading">
                          <h4 class="tooltip-setting">Halaman Terima Barang</h4>
                        </div> 
                      </div>
                      <div class="col-3">
                        <label class="switch">
                          <input type="checkbox" class="switcher-tooltip" data-switch="goods_receive" <?=$guide->goods_receive == 0 ? 'checked' : '' ?>>
                          <span class="slider round"></span>
                        </label>
                      </div>
                    </div>
                  </div>

                </div>
                <!-- End Tooltip -->

                <div role="tabpanel" class="tab-pane <?=isset($_GET['terms']) ? 'active' : ''?>" id="termsOfUse">
                  <div class="header d-block d-md-none clearfix">
                    <a class="btn btn-back mb-4" id="termOfUseMobileBack">
                      <i class="fal fa-chevron-left"></i>
                    </a>
                    <div class="subheading">
                      <h2>Syarat dan Ketentuan</h2>
                    </div>
                  </div>
                  <div class="subheading d-none d-md-block">
                    <h2>Syarat dan Ketentuan</h2>
                  </div>
                  <div class="content font-size-md text-muted">
                    <p>Baca Syarat dan Ketentuan dengan mengunduh <a href="<?=base_url('assets/aksestoko/Syarat%20&%20Ketentuan%20AksesToko.pdf')?>" target="_blank"> dokumen ini </a></p>
                    <p>Baca Kebijakan Privasi dengan mengunduh <a href="<?=base_url('assets/aksestoko/Kebijakan%20Privasi%20AksesToko.pdf')?>" target="_blank"> dokumen ini</a></p>
                  </div>
                </div>


              </div>
            </div>
          </div>


      </div>
    </div>
  </section>


      <!-- Modal Delete Address -->
    <div class="modal fade modal-delete" id="deleteAlamat" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title text-center" id="myModalLabel">Hapus Alamat</h4>
          </div>
          <div class="modal-body">
            <div class="">
              <div class="row">
                <div class="col-12">
                  <div class="card-body text-center">
                    <p>Apakah Anda yakin menghapus alamat berikut?</p>
                    <h4 class="card-title" id="nameAddress">---</h4>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Tidak</button>
            <a id="okAddress" class="btn btn-primary" href="javascript:void(0)">Iya</a>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Verifikasi Phone -->
    <div class="modal modal-check-phone fade modal-delete" id="phoneVerifier" tabindex="-1" role="dialog" aria-labelledby="phoneVerifierTitle">
      <form action="<?=base_url(aksestoko_route("aksestoko/auth/verify_phone_otp"))?>" method="POST">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title text-center" id="phoneVerifierTitle">Verifikasi No Telepon</h4>
            </div>
            <div class="modal-body">
              <div class="">
                <div class="row">
                  <div class="col-12">
                    <div class="card-body text-center">
                      <p>Masukkan Kode Verifikasi</p>
                      <input type="text" name="phone_otp" required class="form-control text-center mb-3" style="width:150px;margin: auto;" maxlength="5">
                      <small id="kirimKode">Tekan <a href="javascript:void(0)" id="kirimKodeA">Kirim Kode</a> untuk menerima kode verifikasi No Telepon</small>
                      <small id="timeleft" style="display: none">Tunggu <span class="text-primary"></span> untuk dapat mengirim kode verifikasi lagi.</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
              <button type="submit" class="btn btn-primary">Kirim</button>              
            </div>
          </div>
        </div>
      </form>
    </div>
   
<script type="text/javascript">

  $(document).ready(function(){
    $(".phoneFormat").keyup(function(event){
      // $(this).val(format($(this).val()));  
      $(this).val(format($(this).val()));

    }).keyup();

    // Perbarui Profile
    $('#phoneUpdateProfile').keyup(function(e) {
      if (validatePhone('phoneUpdateProfile')) {
          $("#btnSimpan").removeAttr('disabled');
          $('#notifValidationPhone').html('');
      }
      else {
          $('#notifValidationPhone').html('Masukkan no telepon yang valid');
          $('#notifValidationPhone').css('color', 'red');
          $('#btnSimpan').attr('disabled', 'disabled');
      }
    });

    // Array 
    $('.phoneAddress').keyup(function(e) {
      let index = $('.phoneAddress').index(this);
      if (validatePhone('phoneAddress', index)) {
        $('.perbaruiAlamat').eq(index).removeAttr('disabled');
          $('.notifValidationPhoneAddress').eq(index).html('');
      }
      else {
          $('.notifValidationPhoneAddress').eq(index).html('Masukkan no telepon yang valid');
          $('.notifValidationPhoneAddress').eq(index).css('color', 'red');
          $('.perbaruiAlamat').eq(index).attr('disabled', 'disabled');
      }
    });


    $(".phoneFormatHidden").keyup(function(event){
      // $(this).val(format($(this).val()));  
      let a =  $(this).val();
      if( a.charAt(0) == '0' ){
        a=a.substr(1);
      }else if (a.charAt(0) == '6' && a.charAt(1) == '2'){
        a=a.substr(2);
      } 
      $(this).val(a).replace(/-/g , '');

    }).keyup();

  });
  
  function readURL(input) {
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      
      reader.onload = function(e) {
        $('#ktp-img').attr('src', e.target.result);
      }
      
      reader.readAsDataURL(input.files[0]); // convert to base64 string
    }
  }

  function validatePhone(phoneUpdateProfile, idx = -1) {
    if (idx != -1) {
      var a = document.getElementsByClassName(phoneUpdateProfile)[idx].value;
    } else {
      var a = document.getElementById(phoneUpdateProfile).value;
    }
    // var filter = /^((\+[1-9]{1,4}[ \-]*)|(\([0-9]{2,3}\)[ \-]*)|([0-9]{2,4})[ \-]*)*?[0-9]{3,4}?[ \-]*[0-9]{3,4}?$/;
      var filter = /^(([1-9]{1,4}[ \-]*)|(\([0-9]{2,3}\)[ \-]*)|([0-9]{2,4})[ \-]*)*?[0-9]{3,4}?[ \-]*[0-9]{3,4}?$/;
    if (filter.test(a)) {
        return true;
    }else {
      return false;
    }
  }


    var format = function(num){
      let a = num;
      if( a.charAt(0) == '0' ){
        a=a.substr(1);
      }else if (a.charAt(0) == '6' && a.charAt(1) == '2'){
        a=a.substr(2);
      }
      num=a;
      var str = num.toString().replace("", ""), parts = false, output = [], i = 1, formatted = null;
      if(str.indexOf(".") > 0) {
        parts = str.split(".");
        str = parts[0];
      }
      str = str.split("").reverse();
      for(var j = 0, len = str.length; j < len; j++) {
        if(str[j] != "-") {
          output.push(str[j]);
          if(i%3 == 0 && j < (len - 1)) {
            output.push("-");
          }
          i++;
        }
      }
      formatted = output.reverse().join("");
      return("" + formatted + ((parts) ? "." + parts[1].substr(0, 2) : ""));
    };


    $(".phoneFormat").bind("keyup tap change paste", function() {
      var phoneFormatHidden = $('.phoneFormatHidden');
      var phoneFormat = $('.phoneFormat');
      for (var i = 0; i < phoneFormatHidden.length; i++) {
        if (this == phoneFormat[i]) {
          let a =  phoneFormatHidden[i].value;
          if( a.charAt(0) == '0' ){
            a=a.substr(1);
          }else if (a.charAt(0) == '6' && a.charAt(1) == '2'){
            a=a.substr(2);
          }
          phoneFormatHidden[i].value = a;
          phoneFormatHidden[i].value = ("62" + $(this).val());
          phoneFormatHidden[i].value = (phoneFormatHidden[i].value.replace(/-/g , ''));
          break;
        }
      }
    });
    

    var token;
    function getToken()
    {
        $.ajax({
            type:'GET',
            url:"https://x.rajaapi.com/poe",
            success: function(data)
            { 
              
              token=data.token;
              loadProvinsi();
              loadProvinsiUpdate();
            }

        }); 
    }

    function loadProvinsi()
    {

      $.ajax({
        type:'GET',
        url:`https://x.rajaapi.com/MeP7c5ne${token}/m/wilayah/provinsi`,
        success: function(data)
        { 
          if (data.success){
            let provinsi = '<option selected value="">Pilih Provinsi</option>';
          
            for(let prov of data.data){
              provinsi += `<option value="${prov.id}">${prov.name}</option>`;
            } 
           $("#provinsiArea").html(provinsi);
          }        
        }  
      });  

    }
    function loadKabupaten()
    {
        var provinsi = $("#provinsiArea").val();
        $.ajax({
            type:'GET',
            url:`https://x.rajaapi.com/MeP7c5ne${token}/m/wilayah/kabupaten?idpropinsi=${provinsi}`,
            success: function(data)
            { 
              if (data.success){
                let kabupaten = '<option selected value="">Pilih Kabupaten</option>';
                for(let kab of data.data){
                  kabupaten += `<option value="${kab.id}">${kab.name}</option>`;
                }  
                $("#kabupatenArea").html(kabupaten);
              }
            }
        }); 
    }

    function loadKecamatan()
    {
        var kabupaten = $("#kabupatenArea").val();
        $.ajax({
            type:'GET',
            url:`https://x.rajaapi.com/MeP7c5ne${token}/m/wilayah/kecamatan?idkabupaten=${kabupaten}`,
            success: function(data)
            { 
              if (data.success){
                let kecamatan = '<option selected value="">Pilih Kecamatan</option>';
                for(let kec of data.data){
                  kecamatan += `<option value="${kec.id}">${kec.name}</option>`;
                }  
                $("#kecamatanArea").html(kecamatan);
            
              }
            }
        }); 
    }

    function loadDesa()
    {
        var kecamatan = $("#kecamatanArea").val();
        $.ajax({
            type:'GET',
            url:`https://x.rajaapi.com/MeP7c5ne${token}/m/wilayah/kelurahan?idkecamatan=${kecamatan}`,
            success: function(data)
            { 
              if (data.success){
                let desa = '<option selected value="">Pilih Desa</option>';
                for(let des of data.data){
                  desa += `<option value="${des.id}">${des.name}</option>`;
                }  
                $("#desaArea").html(desa);
              }
            }
        }); 
    }

// Update
  function loadProvinsiUpdate()
    {
      let $provUpdate = $('.provUpdate');
      $.ajax({
        type:'GET',
        url:`https://x.rajaapi.com/MeP7c5ne${token}/m/wilayah/provinsi`,
        success: function(data)
        { 
          for (const $prov of $provUpdate) {
            let provinsi = '<option value="">Pilih Provinsi</option>';
            let value = $($prov).data('value');
            for(let pr of data.data){
              if(pr.name === value){
                provinsi += `<option selected value="${pr.id}">${pr.name}</option>`;
              } else {
                provinsi += `<option value="${pr.id}">${pr.name}</option>`;
              }  
            }
            $($prov).html(provinsi);
            loadKabupatenUpdate($($prov).data('id'));
          }
        }  
      });
    }

  function loadKabupatenUpdate(id)
    {
      let idProv = $(`#provinsiAreaUpdate${id}`).val();
      let $element = $(`#kabupatenAreaUpdate${id}`);
      $.ajax({
          type:'GET',
          url:`https://x.rajaapi.com/MeP7c5ne${token}/m/wilayah/kabupaten?idpropinsi=${idProv}`,
          success: function(data)
          { 
            if (data.success){
              let value = $($element).data('value');
              let kabupaten = '<option value="">Pilih Kabupaten</option>';
              for(let kab of data.data){
                if(kab.name === value){
                  kabupaten += `<option selected value="${kab.id}">${kab.name}</option>`;
                } else {
                  kabupaten += `<option value="${kab.id}">${kab.name}</option>`;
                }  
              }
              $($element).html(kabupaten);
              loadKecamatanUpdate($($element).data('id'));
            }
          }
      }); 
    }

  function loadKecamatanUpdate(id)
    {
      let idKab = $(`#kabupatenAreaUpdate${id}`).val();
      let $element = $(`#kecamatanAreaUpdate${id}`);
      $.ajax({
          type:'GET',
          url:`https://x.rajaapi.com/MeP7c5ne${token}/m/wilayah/kecamatan?idkabupaten=${idKab}`,
          success: function(data)
          { 
            if (data.success){
              let value = $($element).data('value');
              let kecamatan = '<option value="">Pilih Kecamatan</option>';
              for(let kec of data.data){
                if(kec.name == value){
                  kecamatan += `<option selected value="${kec.id}">${kec.name}</option>`;
                }else {
                  kecamatan += `<option value="${kec.id}">${kec.name}</option>`
                }
            }
           $($element).html(kecamatan);
           loadDesaUpdate($($element).data('id')); 
          }
        } 
    }); 
  }


  function loadDesaUpdate(id)
    {
      let idKec = $(`#kecamatanAreaUpdate${id}`).val();
      
      let $element = $(`#desaAreaUpdate${id}`);
      $.ajax({
          type:'GET',
          url:`https://x.rajaapi.com/MeP7c5ne${token}/m/wilayah/kelurahan?idkecamatan=${idKec}`,
          success: function(data)
          { 
            if (data.success){
              let value = $($element).data('value');
              let desa = '<option value="">Pilih Desa</option>';
              for (let des of data.data){
                if(des.name == value){
                  desa += `<option selected value="${des.id}">${des.name}</option>`;
                }else {
                  desa += `<option value="${des.id}">${des.name}</option>`;
                }
              }
              $($element).html(desa);
              
            }
          }
      }); 
    }

</script>

<script>

  // Tooltip
  $(".switcher-tooltip").on('change', function() {
    var namaHalaman = $(this).data('switch');
      if ($(this).is(':checked')) {
          $.ajax({
            url      : '<?= base_url(aksestoko_route('aksestoko/auth/set_guide/')); ?>'+ namaHalaman + '/0',
            type     : 'GET',
          }) 
      }
      else {
        $.ajax({
            url      : '<?= base_url(aksestoko_route('aksestoko/auth/set_guide/')); ?>'+ namaHalaman + '/1',
            type     : 'GET',
          }) 
      }
  });
  // End Tooltip




    $(document).ready(function() {

      $(".uploadKTP").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings("#valKTP").addClass("selected").html(fileName);
        readURL(this);
      });
      
      getToken();
      $("#provinsiArea").change(function(){
        $("#country_add").val($(this).children("option:selected").html())
        $("#kabupatenArea").html(`<option selected value="">Pilih Provinsi Terlebih Dahulu</option>`);
        $("#kecamatanArea").html(`<option selected value="">Pilih Kabupaten Terlebih Dahulu</option>`);
        $("#desaArea").html(`<option selected value="">Pilih Kecamatan Terlebih Dahulu</option>`);
        loadKabupaten();
      });
      $("#kabupatenArea").change(function(){
        $("#city_add").val($(this).children("option:selected").html())
        $("#kecamatanArea").html(`<option selected value="">Pilih Kabupaten Terlebih Dahulu</option>`);
        $("#desaArea").html(`<option selected value="">Pilih Kecamatan Terlebih Dahulu</option>`);
        loadKecamatan();
      });
      $("#kecamatanArea").change(function(){
        $("#state_add").val($(this).children("option:selected").html()) 
        $("#desaArea").html(`<option selected value="">Pilih Kecamatan Terlebih Dahulu</option>`);
        loadDesa();
      });
      $("#desaArea").change(function(){
        $("#village_add").val($(this).children("option:selected").html())
      });


      // Update
      $(".provUpdate").change(function(){
        let id = $(this).data('id');
        $(`#provinsi_update_${id}`).val($(this).children("option:selected").html())
        $(`#kabupatenAreaUpdate${id}`).html(`<option selected value="">Pilih Provinsi Terlebih Dahulu</option>`);
        $(`#kecamatanAreaUpdate${id}`).html(`<option selected value="">Pilih Kabupaten Terlebih Dahulu</option>`);
        $(`#desaAreaUpdate${id}`).html(`<option selected value="">Pilih Kecamatan Terlebih Dahulu</option>`);
        loadKabupatenUpdate(id);
      })

      $(".kabUpdate").change(function(){
        let id = $(this).data('id');
        $(`#city_update_${id}`).val($(this).children("option:selected").html())
        $(`#kecamatanAreaUpdate${id}`).html(`<option selected value="">Pilih Kabupaten Terlebih Dahulu</option>`);
        $(`#desaAreaUpdate${id}`).html(`<option selected value="">Pilih Kecamatan Terlebih Dahulu</option>`);
        loadKecamatanUpdate(id);
      });

      $(".kecUpdate").change(function(){
        let id = $(this).data('id');
        $(`#state_update_${id}`).val($(this).children("option:selected").html())
        $(`#desaAreaUpdate${id}`).html(`<option selected value="">Pilih Kecamatan Terlebih Dahulu</option>`);
        loadDesaUpdate(id);
      });

      $(".desUpdate").change(function(){
        let id = $(this).data('id');
        $(`#village_update_${id}`).val($(this).children("option:selected").html())
      });

      $(".delete-alamat").click(function(){
        let id = $(this).data('id')
        let name = $(this).data('name')
        $("#nameAddress").html(name)
        $("#deleteAlamat").modal('show')
        $("#okAddress").attr("href", "<?=base_url(aksestoko_route('aksestoko/auth/delete_address/'))?>" + id)
      })


      $("#kirimKodeA").click(function () {
        $.ajax({
            type: "GET",
            dataType: "json",
            url: '<?=base_url(aksestoko_route('aksestoko/auth/generate_phone_otp'))?>',
            success: function(response){
              counter++
              alertCustom(response.message, response.type, 10001)
              timeleft = response.timeleft
              // $("#timeleft").show()
            }
        });
      })

      var phoneUserDefault = $("#phoneUpdateProfile").val()
      $("#phoneUpdateProfile").keyup(function (e) {
        if($(this).val() != phoneUserDefault){
          $("#phoneChange").show()
        }else{
          $("#phoneChange").hide()
        }
      })
    })


// SHOW HIDE PASSWORD 
$("#old_password").on("keyup",function(){
    if($(this).val())
        $("#show-password-old").show();
    else
        $("#show-password-old").hide();
    });

$('#show-password-old').click(function(){
      if ($(this).hasClass('fa-eye')) {
      $('#old_password').attr('type','text');
      $(this).removeClass('fa-eye');
      $(this).addClass('fa-eye-slash');

    }else{
      $('#old_password').attr('type','password');
      $(this).removeClass('fa-eye-slash');
      $(this).addClass('fa-eye');
    }
  })

// SHOW PASS NEW PASSWORD
$("#new_password").on("keyup",function(){
    if($(this).val())
        $("#show-password-new").show();
    else
        $("#show-password-new").hide();
    });
$('#show-password-new').click(function(){
      if ($(this).hasClass('fa-eye')) {
      $('#new_password').attr('type','text');
      $(this).removeClass('fa-eye');
      $(this).addClass('fa-eye-slash');

    }else{
      $('#new_password').attr('type','password');
      $(this).removeClass('fa-eye-slash');
      $(this).addClass('fa-eye');
    }
  })


// SHOW PASS RETYPE   PASSWORD
$("#retype_new_password").on("keyup",function(){
    if($(this).val())
        $("#show-password-retype").show();
    else
        $("#show-password-retype").hide();
    });
$('#show-password-retype').click(function(){
      if ($(this).hasClass('fa-eye')) {
      $('#retype_new_password').attr('type','text');
      $(this).removeClass('fa-eye');
      $(this).addClass('fa-eye-slash');

    }else{
      $('#retype_new_password').attr('type','password');
      $(this).removeClass('fa-eye-slash');
      $(this).addClass('fa-eye');
    }
  })
// END
  function convertTime(second) {
    let minutes = parseInt(second / 60).toString().padStart(2, '0');
    let seconds = parseInt(second % 60).toString().padStart(2, '0');
    return `${minutes}:${seconds}`
  }

  var timeleft = <?=$left_time ? $left_time : "0"?>;
  var first = false;

  setInterval(function(){
    timeleft = timeleft > 0 ? timeleft-1 : 0;
    if(timeleft > 0){
      if($("#kirimKode").is(":visible")){
        $("#kirimKode").hide()
        $("#timeleft").show()
      }
      $("#timeleft span").html(`${convertTime(timeleft)}`)
    } else {
      if($("#kirimKode").is(":hidden")){
        $("#kirimKode").show()
        $("#timeleft").hide()
      }
    }
  }, 1000);
  

</script>
