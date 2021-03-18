<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    
$(document).ready(function(){
       
    $("#brn_name").keyup(function(){
         var code = $(this).val().substr(0,30); 
        code = code.replace(/\s+/g, '-');
        code = code.replace(/[0-9]+/,'');
        // $("#brn_code").val(code);
        // console.log($('#is-other-bank').checked);
    });
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
      //this.files[0].size gets the size of your file.

    });


    $('#dropdown-bank').change(function(){
        // console.log();
        if ($('#dropdown-bank').val() == 'other') {
            $('#manual-input-bank').removeClass('hide');
        }else{
            $('#manual-input-bank').addClass('hide');
        }
    });
});


</script>

<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel">Add Bank</h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form','id'=>'bankForm');
        echo form_open_multipart("system_settings/add_bank", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            
            <div id="auto-input-bank" class="">
                <div class="form-group">
                    Bank Name
                    <?= form_dropdown('dropdown_bank_name', $options_bank,'', 'class="form-control" id="dropdown-bank" required="required"'); ?>
                     <input name="code" id="brn_code" type="hidden"/>
                </div>
            </div>

            <div id="manual-input-bank" class="hide">
                <div class="form-group">
                    <?= form_input('input_bank_name', '', 'class="form-control" id="bank_name" '); ?>
                </div>
                <div class="form-group">
                    Logo
                    <input id="imageBank" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false" class="form-control file" accept="image/*">
                    <span id="InfoImageBank"><i style="color:red;"><sup><strong>*Recomended : </strong><?= "Width:".$this->Settings->twidth."px, Height:".$this->Settings->theight."px, Max File Size:".$this->allowed_file_size ?>Kb</sup></i></span>
                </div>
            </div>

            <div class="form-group">
                Rekening Number
                <?= form_input('rekening_number', '', 'class="form-control" id="brn_name" required="required"'); ?>
                 <input name="code" id="brn_code" type="hidden"/>
            </div>

            <div class="form-group">
                Pemilik
                <?= form_input('user', '', 'class="form-control" id="brn_name" required="required"'); ?>
                 <input name="code" id="brn_code" type="hidden"/>
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
                        <input type="checkbox" class="checkbox" value="1" name="is_third_party" id="is_third_party" <?= $this->input->post() ? ($this->input->post('is_third_party') ? 'checked="checked"' : '') : 'checked="checked"'; ?>>
                        <label for="is_third_party" class="padding05"><?= lang('Active_Third_Party') ?></label>
                    </div>
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_bank', "addBank", 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?=FORCAPOS_VERSION?>"></script>
<?= $modal_js ?>