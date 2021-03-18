<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    
     $(document).ready(function(){
       
    $("#brn_name").keyup(function(){
         var code = $(this).val().substr(0,30); 
        code = code.replace(/\s+/g, '-');
        code = code.replace(/[0-9]+/,'');
        $("#brn_code").val(code);
    });
    $('#brandForm').submit( function( e ) {
        $.ajax({
          url: 'products/add_brand',
          type: 'POST',
          data: new FormData( this ),
          processData: false,
          contentType: false,
          success: function(data){
              try {
                    LoadBrand();
                    var datas = $.parseJSON(data);
                    $('select[name=brand]').val(datas.BrandID).change();
                    $("#InfoBrand").html(datas.message);
                    $('#myModal').modal('hide');
                } catch(e) {
                    alert("Data Tidak Bisa Di Proses");
                    //JSON parse error, this is not json (or JSON isn't in your browser)
                }

          }
        });
    e.preventDefault();
  });
var _URL = window.URL || window.webkitURL;
$('#imageBrand').bind('change', function() {
     
 var file, img;
    if ((file = this.files[0])) {
        img = new Image();
        var maxWidth = <?= $this->Settings->twidth ?>;
        var maxHeight = <?= $this->Settings->theight ?>;
         var maxSize = <?= $this->allowed_file_size ?>;
        img.onload = function () {
            if(this.width > maxWidth && this.height > maxHeight){
                alert("Exceed Max Limit");
                $("#InfoImageBrand").html('<i style="color:red;"><sup><strong>*Recomended : </strong><?= "Width:".$this->Settings->twidth."px, Height:".$this->Settings->theight."px, File Size:".$this->allowed_file_size ?>Kb</sup></i>');
                    $(".file-input").addClass("file-input-new");
                    $(".file-caption-name").empty();
            }else{
               $("#InfoImageBrand").html('<i style="color:green;"><sup><strong>Image Recomended </strong></sup></i>');
            }

   
        };

        img.src = _URL.createObjectURL(file);
    }
  //this.files[0].size gets the size of your file.

});

});
</script>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_brand'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form','id'=>'brandForm');
        echo form_open_multipart("javascript:void(0);", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
                <?= lang('name', 'name'); ?>
                <?= form_input('name', '', 'class="form-control" id="brn_name" required="required"'); ?>
                 <input name="code" id="brn_code" type="hidden"/>
            </div>

            <div class="form-group">
                <?= lang("image", "image") ?>
                <input id="imageBrand" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false" class="form-control file" accept="image/*">
                <span id="InfoImageBrand"><i style="color:red;"><sup><strong>*Recomended : </strong><?= "Width:".$this->Settings->twidth."px, Height:".$this->Settings->theight."px, Max File Size:".$this->allowed_file_size ?>Kb</sup></i></span>
            </div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_brand', lang('add_brand'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?=FORCAPOS_VERSION?>"></script>
<?= $modal_js ?>