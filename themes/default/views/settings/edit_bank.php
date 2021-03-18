<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    
     $(document).ready(function(){
       
    $("#brn_name").on('change', function(){
         var code = $(this).val().substr(0,30); 
        code = code.replace(/\s+/g, '-');
        code = code.replace(/[0-9]+/,'');
        $("#brn_code").val(code);
    });
//    $('#brandForm').submit( function( e ) {
//        $.ajax({
//          url: 'products/add_brand',
//         // contentType: "application/json",
//          type: 'POST',
//          data: new FormData( this ),
//          processData: false,
//          contentType: false,
//          success: function(data){
//              try {
//                    LoadBrand();
//                   var datas = $.parseJSON(data);
//                   console.log("datas", datas);
//                    $('select[name=brand]').val(datas.BrandID).change();
//                    $("#InfoBrand").html(datas.message);
//                    $('#myModal').modal('hide');
//                } catch(e) {
//                    
//                    alert(e);
//                    //JSON parse error, this is not json (or JSON isn't in your browser)
//               }
//         }
//          
//        });
//    e.preventDefault();
//  });
var _URL = window.URL || window.webkitURL;
$('#imageBank').bind('change', function() {
     
 var file, img;
    if ((file = this.files[0])) {
        img = new Image();
        var maxWidth = <?= $this->Settings->twidth ?>;
        var maxHeight = <?= $this->Settings->theight ?>;
         var maxSize = <?= $this->allowed_file_size ?>;
        img.src = _URL.createObjectURL(file);
    }
});

});
</script>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel">Edit Bank</h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form','id'=>'brandForm');
        echo form_open_multipart("system_settings/edit_bank/".$bank->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
                Bank Name
                <?php if($options_bank[$bank->bank_name]){ ?>
                    <select name="bank_name_tmp_view" class="form-control" id="brn_name" required="required" disabled>
                        <?php 
                            foreach ($options_bank as $key => $value) {
                        ?>
                            <option <?= $key == $bank->bank_name ? 'selected' : '' ?>  value="<?= $key ?>"><?= $value ?></option>
                        <?php
                            }
                        ?>
                    </select>
                <?php }else{ ?>
                    <input name="bank_name" id="brn_code" value="<?= $bank->bank_name ?>" type="text" class="form-control" id="brn_name" required="required" disabled/>
                <?php } ?>
                <input name="bank_name" id="brn_code" value="<?= $bank->bank_name ?>" type="hidden"/>
                <input name="code" id="brn_code" type="hidden"/>
            </div>

            <div class="form-group">
                Rekening Number
                <?= form_input('rekening_number', $bank->no_rekening, 'class="form-control" id="brn_name" required="required"'); ?>
                 <input name="code" id="brn_code" type="hidden"/>
            </div>

            <div class="form-group">
                Pemilik
                <?= form_input('user', $bank->name, 'class="form-control" id="brn_name" required="required"'); ?>
                 <input name="code" id="brn_code" type="hidden"/>
            </div>

            <div class="form-group">
                Logo
                <input id="imageBank" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false" class="form-control file" accept="image/*">
                <span id="InfoImageBank"><i style="color:red;"><sup><strong>*Recomended : </strong><?= "Width:".$this->Settings->twidth."px, Height:".$this->Settings->theight."px, Max File Size:".$this->allowed_file_size ?>Kb</sup></i></span>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <input type="checkbox" class="checkbox" value="1" name="is_active" id="is_active" <?= $this->input->post() ? ($this->input->post('is_active') ? 'checked="checked"' : '') : 'checked="checked"'; ?>>
                        <label for="is_active" class="padding05"><?= lang('active') ?></label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <input type="checkbox" class="checkbox" name="is_third_party" id="is_third_party" <?= ($bank->is_third_party == '1' ? 'checked="checked"' : ''); ?>>
                        <label for="is_third_party" class="padding05"><?= lang('Active_Third_Party') ?></label>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_bank', "editBank", 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?=FORCAPOS_VERSION?>"></script>
<?= $modal_js ?>