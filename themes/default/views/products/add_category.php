<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
 $(document).ready(function(){
    var getCategory = '<?= $sub_category ?>';
    $("#cat_name").keyup(function(){
         var code = $(this).val().substr(0,30); 
        code = code.replace(/\s+/g, '-');
        code = code.replace(/[0-9]+/,'');
        $("#cat_code").val(code);
    });
    $('#CategoryForm').submit( function( e ) {
        $.ajax({
          url: 'products/add_category',
          type: 'POST',
          data: new FormData( this ),
          processData: false,
          contentType: false,
          success: function(data){
                try {
                    var datas = $.parseJSON(data);
                    var idcategory = datas.CategoryID;
                    var parentCategory = datas.SubCategoryID;
                      if(getCategory != 1 ){
                          LoadCategory(idcategory);
                      }else{
                          loadSubcategory(parentCategory,idcategory);
                      }
      //             $('select[name=category]').val().change();
                      $(".InfoCategory").html(datas.message);
                        $('#myModal').modal('hide');
                } catch(e) {
                    alert("Data Tidak Bisa Di Proses");
                    //JSON parse error, this is not json (or JSON isn't in your browser)
                }

          }
        });
    e.preventDefault();
  });
if(getCategory == 1 ){
    var idCategory = $('select[name=category]').val();
    $('select[name=parent]').val(idCategory).change();
}
var _URL = window.URL || window.webkitURL;
$('#imageCategory').bind('change', function() {
     
 var file, img;
    if ((file = this.files[0])) {
        img = new Image();
        var maxWidth = <?= $this->Settings->twidth ?>;
        var maxHeight = <?= $this->Settings->theight ?>;
         var maxSize = <?= $this->allowed_file_size ?>;
        img.onload = function () { 
            if(this.width > maxWidth && this.height > maxHeight){
                alert("Exceed Max Limit");
                $("#InfoImageCategory").html('<i style="color:red;"><sup><strong>*Recomended : </strong><?= "Width:".$this->Settings->twidth."px, Height:".$this->Settings->theight."px, Max File Size:".$this->allowed_file_size ?>Kb</sup></i>');
                    $(".file-input").addClass("file-input-new");
                    $(".file-caption-name").empty();
            }else{
               $("#InfoImageCategory").html('<i style="color:green;"><sup><strong>Image Recomended </strong></sup></i>');
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
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_category'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form','id'=>'CategoryForm');
        echo form_open_multipart("javascript:void(0);", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="form-group">
                <?= lang('category_name', 'name'); ?>
                <?= form_input('name', set_value('name'), 'class="form-control" id="cat_name" required="required"'); ?>
                <input name="code" id="cat_code" type="hidden"/>
            </div>

            <div class="form-group">
                <?= lang("category_image", "image") ?>
                <input id="imageCategory" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false"
                       class="form-control file">
                <span id="InfoImageCategory"><i style="color:red;"><sup><strong>*Recomended : </strong><?= "Width:".$this->Settings->twidth."px, Height:".$this->Settings->theight."px, Max File Size:".$this->allowed_file_size ?>Kb</sup></i></span>
            </div>
        <?php if($sub_category == 1 ){?>    
            <div class="form-group">
                <?= lang("parent_category", "parent") ?>
                <?php
                $cat[''] = lang('select').' '.lang('parent_category');
                foreach ($categories as $pcat) {
                    $cat[$pcat->id] = $pcat->name;
                }
                echo form_dropdown('parent', $cat, (isset($_POST['parent']) ? $_POST['parent'] : ''), 'class="form-control select" id="parent" style="width:100%"')
                ?>
            </div>
        <?php } ?>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_category', lang('add_category'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?=FORCAPOS_VERSION?>"></script>
<?= $modal_js ?>