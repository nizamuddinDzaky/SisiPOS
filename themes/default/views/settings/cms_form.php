<!-- <?php

/* 
 * Copyright (c) 2017 adminSISI.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    adminSISI - initial API and implementation and/or initial documentation
 */
?>
  <-- Nav tabs -->


<style type="text/css">
.imagePreview {
    width: 100%;
    height: 180px;
    background-position: center center;
/*  background:url(http://cliquecities.com/assets/no-image-e3699ae23f866f6cbdf8ba2443ee5c4e.jpg);*/
  background-color:#cecece;
    background-size: contain;
  background-repeat:no-repeat;
    display: inline-block;
  box-shadow:0px -3px 6px 2px rgba(0,0,0,0.2);
}
.imagePreviewBanner {
    width: 100%;
    height: 500px !important;
    background-position: center center;
/*  background:url(http://cliquecities.com/assets/no-image-e3699ae23f866f6cbdf8ba2443ee5c4e.jpg);*/
  background-color:#fff;
    background-size: contain;
  background-repeat:no-repeat;
    display: inline-block;
  box-shadow:0px -3px 6px 2px rgba(0,0,0,0.2);
}
.btn-primary
{
  display:block;
  border-radius:0px;
  box-shadow:0px 4px 6px 2px rgba(0,0,0,0.2);
  margin-top:-5px;
}
.imgUp
{
  margin-bottom:15px;
    margin-top: 10px;
        border: 1px solid #d8d8d8;
}
.del
{
  position:absolute;
  top:0px;
  right:15px;
  width:30px;
  height:30px;
  text-align:center;
  line-height:30px;
  background-color:rgba(255,255,255,0.6);
  cursor:pointer;
}
.imgAdd
{
  width:30px;
  height:30px;
  border-radius:50%;
  background-color:#4bd7ef;
  color:#fff;
  box-shadow:0px 0px 2px 1px rgba(0,0,0,0.2);
  text-align:center;
  line-height:30px;
  margin-top:0px;
  cursor:pointer;
  font-size:15px;
}
li.active>a.klik-tebal {
    font-weight: 700;
}

.batas-element {
        padding: 20px;
    border: 1px solid #dbdee0;
    margin-bottom: 15px;
    background-color: #f3f3f3;
}
</style>  

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('cms');?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo $this->lang->line("list_results"); ?></p>

            </div>
        </div>


      <ul class="nav nav-tabs" >
        <li class="active">
          <a class="klik-tebal" data-toggle="tab" class="active" href="#header-landing"><?= lang('header_cms');?></a>
        </li>
        <li >
          <a class="klik-tebal" data-toggle="tab" href="#about-us"><?= lang('about_us_cms');?></a>
        </li>
        <li >
          <a class="klik-tebal" data-toggle="tab" href="#cara_penggunaan"><?= lang('how_to_use_cms');?></a>
        </li>
        <li >
          <a class="klik-tebal" data-toggle="tab" class="nav-link" href="#keuntungan"><?= lang('profit_cms');?></a>
        </li>
        <li>
          <a class="klik-tebal" data-toggle="tab" href="#footer"><?= lang('footer_cms');?></a>
        </li>
        
      </ul>

      <!-- Tab panes -->
      <div class="tab-content border mb-3">

        <div id="header-landing" class="tab-pane fade in active">
            <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form','id'=>'bankForm');
            echo form_open_multipart("system_settings/cms_update_header/".$cms->id, $attrib); ?>
            <div class="form-group all">
                <label for="title-header"><?= lang('title_header_cms');?></label>
                <input id="product_image" type="text" name="header-title" class="form-control file" value="<?=$cms->header_title?>">
            </div>
            <div class="form-group all">
                <label for="caption-header"><?= lang('caption_header_cms');?></label>
                <input class="form-control hehe" placeholder="Caption" name="header-caption" value="<?=$cms->header_caption?>">
            </div>
            <div class="form-group all">
                <label for="header_image"><?= lang('banner_cms');?></label>
                <div class="imgUp">
                    <div class="imagePreview imagePreviewBanner" style="background-image:url(
                    <?php 
                        if ($cms->header_bg == '') {
                            echo "http://cliquecities.com/assets/no-image-e3699ae23f866f6cbdf8ba2443ee5c4e.jpg";
                        }else{
                            echo base_url()."assets/uploads/cms/".$cms->header_bg;
                        }
                    ?>
                    );"></div>
                    <label class="btn btn-primary">
                        <?= lang('select_image');?><input type="file" name="header-image" class="uploadFile img" accept=".jpg , .png , .JPEG" value="Upload Photo" style="width: 0px;height: 0px;overflow: hidden;">
                    </label>
                    <!-- Tombol Plus -->
                    <!-- <i class="fa fa-plus imgAdd"></i> -->
                </div>
                <i style="color:red;"><sup><strong>*Recomended : </strong>
                                            Width:2000px, Height:1000px, Max File Size:1024Kb</sup></i>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <label for="logo-hitam"><?= lang('logo_cms');?></label>
                    <div class="imgUp">
                                
                        <div class="imagePreview" style="background-image:url(
                        <?php 
                            if ($cms->logo_1 == '') {
                                echo "http://cliquecities.com/assets/no-image-e3699ae23f866f6cbdf8ba2443ee5c4e.jpg";
                            }else{
                                echo base_url()."assets/uploads/cms/".$cms->logo_1;
                            }
                        ?>

                        );"></div>
                        <label class="btn btn-primary">
                            <?= lang('select_image');?><input type="file" name="logo-berwarna" class="uploadFile img" accept=".jpg , .png , .JPEG" value="Upload Photo" style="width: 0px;height: 0px;overflow: hidden;">
                        </label>
                        <!-- Tombol Plus -->
                        <!-- <i class="fa fa-plus imgAdd"></i> -->
                    </div>
                    <i style="color:red;"><sup><strong>*Recomended : </strong>
                                            Width:584px, Height:199px, Max File Size:500kb</sup></i>
                </div>

                <div class="col-md-6">
                    <label for="logo-hitam"><?= lang('logo_white_cms');?></label>
                    <div class="imgUp">
                                
                        <div class="imagePreview" style="background-image:url(
                        <?php 
                            if ($cms->logo_2 == '') {
                                echo "http://cliquecities.com/assets/no-image-e3699ae23f866f6cbdf8ba2443ee5c4e.jpg";
                            }else{
                                echo base_url()."assets/uploads/cms/".$cms->logo_2;
                            }
                        ?>

                        );"></div>
                        <label class="btn btn-primary">
                            <?= lang('select_image');?><input type="file" name="logo-putih" class="uploadFile img" accept=".jpg , .png , .JPEG" value="Upload Photo" style="width: 0px;height: 0px;overflow: hidden;">
                        </label>
                        <!-- Tombol Plus -->
                        <!-- <i class="fa fa-plus imgAdd"></i> -->
                    </div>
                    <i style="color:red;"><sup><strong>*Recomended : </strong>
                                            PNG, Width:584px, Height:199px, Max File Size:500kb</sup></i>
                </div>

                <div class="col-md-12" style="margin-top: 10px;">
                    <div class="form-group-all">
                        <input type="submit" name="save_cms" value="<?= lang('update_cms')?>" class="btn btn-primary" id="save_cms">
                    </div>
                </div>
                
            </div>
            <?php echo form_close(); ?>
        </div>


        <div id="about-us" class="tab-pane fade"><br>
            <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form','id'=>'bankForm');
            echo form_open_multipart("system_settings/cms_update_about/".$cms->id, $attrib); ?>
            <div class="batas-element">
                <div class="form-group all">
                    <label for="title-about-us"><?= lang('title_element_cms');?></label>
                    <input id="product_image" type="text" name="about-title" class="form-control file" value="<?=$cms->about_title?>">
                </div>
            </div>
            <div class="form-group all">
                <label for="content-about-us"><?= lang('caption_cms');?></label>
                <textarea class="form-control" placeholder="Caption" name="about-caption" > <?=$cms->about_caption?></textarea>
            </div>
            <div class="form-group-all" style="margin-top: 10px;">
                <input type="submit" name="save_about_us" value="<?= lang('update_cms')?>" class="btn btn-primary" id="ave_about_us">
            </div>
            <?php echo form_close(); ?>
        </div>


        <div id="cara_penggunaan" class="tab-pane fade"><br>
          <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form','id'=>'bankForm');
          echo form_open_multipart("system_settings/cms_update_cara_penggunaan/".$cms->id, $attrib); ?>
          <div class="row">
            <div class="col-md-12">
                <div class="batas-element">
                    <div class="form-group all">
                        <label for="title_cara_penggunaan"><?= lang('title_element_cms');?></label>
                        <input id="title_cara_penggunaan" type="text" name="title-cara-penggunaan" class="form-control" value="<?=$cms->how_title?>">
                    </div>
                </div>
            </div>
              <div class="col-md-4">
                <div style="padding: 20px;border: 1px solid #dbdee0; margin-bottom: 15px; background-color: #f3f3f3;">
                    <div class="form-group all">
                        <label for="header_image">Icon 1</label>
                        <div class="imgUp">        
                                <div class="imagePreview" style="background-image:url(
                                <?php 
                                    if ($cms->how_image_1 == '') {
                                        echo "http://cliquecities.com/assets/no-image-e3699ae23f866f6cbdf8ba2443ee5c4e.jpg";
                                    }else{
                                        echo base_url()."assets/uploads/cms/".$cms->how_image_1;
                                    }
                                ?>
                                );"></div>
                                <label class="btn btn-primary">
                                    <?= lang('select_image');?><input type="file" name="icon-penggunaan-1" accept=".jpg , .png , .JPEG" class="uploadFile img" value="Upload Photo" style="width: 0px;height: 0px;overflow: hidden;">
                                </label>
                        </div>
                        <i style="color:red;"><sup><strong>*Recomended : </strong>
                                            PNG, Width:300px, Height:300px, Max File Size:1024Kb</sup></i>
                    </div>
                    <div class="form-group all">
                        <label for="header_image"><?= lang('title_cms');?></label>
                        <input id="product_image" type="text" name="cara-penggunaan-title-1" class="form-control" value="<?=$cms->how_title_1?>">
                    </div>
                    <div class="form-group all">
                        <label for="header_image"><?= lang('caption_cms');?></label>
                        <input class="form-control" placeholder="Caption" name="caption-penggunaan-1" value="<?=$cms->how_caption_1?>">
                    </div>
                    
                </div>   

              </div>
              <div class="col-md-4">
                <div style="padding: 20px;border: 1px solid #dbdee0; margin-bottom: 15px; background-color: #f3f3f3;">
                    <div class="form-group all">
                        <label for="header_image">Icon 2</label>
                        <div class="imgUp">        
                                <div class="imagePreview" style="background-image:url(
                                <?php 
                                    if ($cms->how_image_2 == '') {
                                        echo "http://cliquecities.com/assets/no-image-e3699ae23f866f6cbdf8ba2443ee5c4e.jpg";
                                    }else{
                                        echo base_url()."assets/uploads/cms/".$cms->how_image_2;
                                    }
                                ?>
                                );"></div>
                                <label class="btn btn-primary">
                                    <?= lang('select_image');?><input type="file" name="icon-penggunaan-2" accept=".jpg , .png , .JPEG" class="uploadFile img" value="Upload Photo" style="width: 0px;height: 0px;overflow: hidden;">
                                </label>
                        </div>
                        <i style="color:red;"><sup><strong>*Recomended : </strong>
                                            PNG, Width:300px, Height:300px, Max File Size:1024Kb</sup></i>
                    </div>
                    <div class="form-group all">
                        <label for="header_image"><?= lang('title_cms');?></label>
                        <input id="product_image" type="text" name="cara-penggunaan-title-2" class="form-control" value="<?=$cms->how_title_2?>">
                    </div>
                    <div class="form-group all">
                        <label for="header_image"><?= lang('caption_cms');?></label>
                        <input class="form-control" placeholder="Caption" name="caption-penggunaan-2" value="<?=$cms->how_caption_2?>">
                    </div>
                    
                </div>
              </div>
              <div class="col-md-4">
                <div style="padding: 20px;border: 1px solid #dbdee0; margin-bottom: 15px; background-color: #f3f3f3;">
                    <div class="form-group all">
                        <label for="header_image">Icon 3</label>
                        <div class="imgUp">        
                                <div class="imagePreview" style="background-image:url(
                                <?php 
                                    if ($cms->how_image_3 == '') {
                                        echo "http://cliquecities.com/assets/no-image-e3699ae23f866f6cbdf8ba2443ee5c4e.jpg";
                                    }else{
                                        echo base_url()."assets/uploads/cms/".$cms->how_image_3;
                                    }
                                ?>
                                );"></div>
                                <label class="btn btn-primary">
                                    <?= lang('select_image');?><input type="file" name="icon-penggunaan-3" accept=".jpg , .png , .JPEG" class="uploadFile img" value="Upload Photo" style="width: 0px;height: 0px;overflow: hidden;">
                                </label>
                        </div>
                        <i style="color:red;"><sup><strong>*Recomended : </strong>
                                            PNG, Width:300px, Height:300px, Max File Size:1024Kb</sup></i>
                    </div>
                    <div class="form-group all">
                        <label for="header_image"><?= lang('title_cms');?></label>
                        <input id="product_image" type="text" name="cara-penggunaan-title-3" class="form-control" value="<?=$cms->how_title_3?>">
                    </div>
                    <div class="form-group all">
                        <label for="header_image"><?= lang('caption_cms');?></label>
                        <input class="form-control" placeholder="Caption" name="caption-penggunaan-3" value="<?=$cms->how_caption_3?>">
                    </div>
                    
                </div>
              </div>

              <!-- Tombol Update -->
                <div class="col-md-12" style="margin-top: 10px;">
                    <div class="form-group-all">
                        <input type="submit" name="save_penggunaan" value="<?= lang('update_cms')?>" class="btn btn-primary" id="save_penggunaan">
                    </div>
                </div>

          </div>
          <?php echo form_close(); ?>
        </div>
        

        <div id="keuntungan" class="tab-pane fade"><br>
            <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form','id'=>'bankForm');
            echo form_open_multipart("system_settings/cms_update_benefit/".$cms->id, $attrib); ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="batas-element">
                        <div class="form-group all">
                            <label for="title_keuntungan"><?= lang('title_element_cms');?></label>
                            <input id="title-keuntungan-element" type="text" name="title-keuntungan-element" class="form-control" value="<?=$cms->benefit_title?>">
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div style="padding: 20px;border: 1px solid #dbdee0; margin-bottom: 15px; background-color: #f3f3f3;">
                        <div class="form-group all">
                            <label for="Icon-1-keuntungan">Icon 1</label>

                            <div class="imgUp">
                                
                                <div class="imagePreview" style="background-image:url(<?php 
                                    if ($cms->benefit_image_1 == '') {
                                        echo "http://cliquecities.com/assets/no-image-e3699ae23f866f6cbdf8ba2443ee5c4e.jpg";
                                    }else{
                                        echo base_url()."assets/uploads/cms/".$cms->benefit_image_1;
                                    }
                                ?>);"></div>
                                <label class="btn btn-primary">
                                    <?= lang('select_image');?><input type="file" accept=".jpg , .png , .JPEG" name="icon-keuntungan-1" class="uploadFile img" value="Upload Photo" style="width: 0px;height: 0px;overflow: hidden;">
                                </label>
                                <!-- Tombol Plus -->
                                <!-- <i class="fa fa-plus imgAdd"></i> -->
                            </div>
                            <i style="color:red;"><sup><strong>*Recomended : </strong>
                                            PNG, Width:200px, Height:200px, Max File Size:1024Kb</sup></i>        

                        </div>
                        <div class="form-group all">
                            <label for="title-1-keuntungan"><?= lang('title_cms');?></label>
                            <input id="title-1-keuntungan" type="text" name="benefit-title-1" class="form-control" value="<?=$cms->benefit_title_1?>">
                        </div>
                        <div class="form-group all">
                            <label for="caption-1-keuntungan"><?= lang('caption_cms');?></label>
                            <input class="form-control" placeholder="Caption" name="caption-keuntungan-1" value="<?=$cms->benefit_caption_1?>">
                        </div>
                        
                    </div>
                </div>
                <div class="col-md-4">
                    <div style="padding: 20px;border: 1px solid #dbdee0; margin-bottom: 15px; background-color: #f3f3f3;">
                        <div class="form-group all">
                            <label for="Icon-2-keuntungan">Icon 2</label>
                            <div class="imgUp">
                                
                                <div class="imagePreview" style="background-image:url(<?php 
                                    if ($cms->benefit_image_2 == '') {
                                        echo "http://cliquecities.com/assets/no-image-e3699ae23f866f6cbdf8ba2443ee5c4e.jpg";
                                    }else{
                                        echo base_url()."assets/uploads/cms/".$cms->benefit_image_2;
                                    }
                                ?>);"></div>
                                <label class="btn btn-primary">
                                    <?= lang('select_image');?><input type="file" accept=".jpg , .png , .JPEG" class="uploadFile img" name="icon-keuntungan-2" value="Upload Photo" style="width: 0px;height: 0px;overflow: hidden;">
                                </label>
                            </div>
                            <i style="color:red;"><sup><strong>*Recomended : </strong>
                                            PNG, Width:200px, Height:200px, Max File Size:1024Kb</sup></i>
                        </div>
                        <div class="form-group all">
                            <label for="title-2-keuntungan"><?= lang('title_cms');?></label>
                            <input id="title-2-keuntungan" type="text" name="benefit-title-2" class="form-control" value="<?=$cms->benefit_title_2?>">
                        </div>
                        <div class="form-group all">
                            <label for="caption-2-keuntungan"><?= lang('caption_cms');?></label>
                            <input class="form-control" placeholder="Caption" name="caption-keuntungan-2" value="<?=$cms->benefit_caption_2?>">
                        </div>
                       
                    </div>
                </div>
                <div class="col-md-4">
                    <div style="padding: 20px;border: 1px solid #dbdee0; margin-bottom: 15px; background-color: #f3f3f3;">
                        <div class="form-group all">
                            <label for="Icon-3-keuntungan">Icon 3</label>
                            <div class="imgUp">
                                
                                <div class="imagePreview" style="background-image:url(<?php 
                                    if ($cms->benefit_image_3 == '') {
                                        echo "http://cliquecities.com/assets/no-image-e3699ae23f866f6cbdf8ba2443ee5c4e.jpg";
                                    }else{
                                        echo base_url()."assets/uploads/cms/".$cms->benefit_image_3;
                                    }
                                ?>);"></div>
                                <label class="btn btn-primary">
                                    <?= lang('select_image');?><input type="file" accept=".jpg , .png , .JPEG" name="icon-keuntungan-3" class="uploadFile img" value="Upload Photo" style="width: 0px;height: 0px;overflow: hidden;">
                                </label>
                            </div>
                            <i style="color:red;"><sup><strong>*Recomended : </strong>
                                            PNG, Width:200px, Height:200px, Max File Size:1024Kb</sup></i>
                        </div>
                        <div class="form-group all">
                            <label for="title-keuntungan-3"><?= lang('title_cms');?></label>
                            <input id="product_image" type="text" name="benefit-title-3" class="form-control" value="<?=$cms->benefit_title_3?>">
                        </div>
                        <div class="form-group all">
                            <label for="caption-keuntungan-3"><?= lang('caption_cms');?></label>
                            <input class="form-control" placeholder="Caption" name="caption-keuntungan-3" value="<?=$cms->benefit_caption_3?>">
                        </div>
                        
                    </div>
                </div>
                <!-- Tombol Update -->
                <div class="col-md-12" style="margin-top: 10px;">
                    <div class="form-group-all">
                        <input type="submit" name="save_keuntungan-2" value="<?= lang('update_cms')?>" class="btn btn-primary" id="save_keuntungan-2">
                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>


        <div id="footer" class="tab-pane fade"><br>
            <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form','id'=>'bankForm');
            echo form_open_multipart("system_settings/cms_update_footer/".$cms->id, $attrib); ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group all">
                        <label for="fb-footer">Whatsapp</label>
                        <input id="link-wa" type="text" name="link-wa" class="form-control" placeholder="https://example.com/" value="<?=$cms->footer_link_wa?>">

                    </div>
                    <div class="form-group all">
                        <label for="Icon-3-footer">Facebook</label>
                        <input id="facebook" type="text" name="facebook" class="form-control" placeholder="https://example.com/" value="<?=$cms->footer_link_fb?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group all">
                        <label for="twitter-footer">Twitter</label>
                        <input id="twitter" type="text" name="twitter" class="form-control" placeholder="https://example.com/" value="<?=$cms->footer_link_twitter?>">
                    </div>
                    <div class="form-group all">
                        <label for="instagram-footer">Instagram</label>
                        <input id="instagram" type="text" name="instagram" class="form-control" placeholder="https://example.com/" value="<?=$cms->footer_link_ig?>">
                    </div>
                </div>


                <div class="col-md-12">
                    <div class="form-group all">
                            <label for="wa-footer">No Whatsapp</label>
                            <input id="no-wa" type="text" name="no-wa" class="form-control" value="<?=$cms->footer_cs_wa?>">
                            <!-- <input type="number" name="telp" id="telp">
                            <input type="submit" onClick="validasi()" value="Submit"> -->
                            
                    </div>
                    <div class="form-group all">
                            <label for="about-us-footer">About Us</label>
                            <textarea class="form-control" placeholder="About us" name="about-us-footer">
                                <?=$cms->footer_right?>
                            </textarea>
                    </div>

                </div>
                <div class="col-md-12" style="margin-top: 10px;">
                    <div class="form-group-all">
                        <input type="submit" name="save_footer" value="<?= lang('update_cms')?>" class="btn btn-primary" id="save_footer" >
                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>



    </div> <!-- End Div Box Content -->

</div>


<script>
// fungsi number
// function Number(nilai, pesan) {
//    var numberExp = /\+?([ -]?\d+)+|\(\d+\)([ -]\d+)/;
//    if(nilai.value.match(numberExp)) {
//      return true;
//    }
//    else {
//      alert(pesan);
//      nilai.focus();
//      return false;
//    }
// }


// function validasi() {
//   Number(document.getElementById('telp'), 'Telp. hanya ber isi Number!!');
//   event.preventDefault()
// }
</script>


<script>
    $(".imgAdd").click(function(){
  $(this).closest(".row").find('.imgAdd').before('<div class="imgUp"><div class="imagePreview"></div><label class="btn btn-primary"><?= lang('select_image');?><input type="file" class="uploadFile img" value="Upload Photo" style="width:0px;height:0px;overflow:hidden;"></label><i class="fa fa-times del"></i></div>');
});
$(document).on("click", "i.del" , function() {
    $(this).parent().remove();
});
$(function() {
    $(document).on("change",".uploadFile", function()
    {
            var uploadFile = $(this);
        var files = !!this.files ? this.files : [];
        if (!files.length || !window.FileReader) return; // no file selected, or no FileReader support
 
        if (/^image/.test( files[0].type)){ // only image file
            var reader = new FileReader(); // instance of the FileReader
            reader.readAsDataURL(files[0]); // read the local file
 
            reader.onloadend = function(){ // set image data as background of div
                //alert(uploadFile.closest(".upimage").find('.imagePreview').length);
uploadFile.closest(".imgUp").find('.imagePreview').css("background-image", "url("+this.result+")");
            }
        }
      
    });
});
</script>

<script type="text/javascript">
$R('#content', {
    toolbar: false
});
</script>