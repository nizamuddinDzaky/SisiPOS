<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    
$(document).ready(function(){
       
    $("#brn_name").keyup(function(){
         var code = $(this).val().substr(0,30); 
        code = code.replace(/\s+/g, '-');
        code = code.replace(/[0-9]+/,'');
        // $("#brn_code").val(code);
        console.log($('#is-other-bank').checked);
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
            <h4 class="modal-title" id="myModalLabel">Add Template Name</h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form','id'=>'bankForm');
        echo form_open_multipart("system_settings/add_cms_name", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="form-group">
                Template Name
                <?= form_input('name', '', 'class="form-control" id="brn_name" required="required"'); ?>
                 <input name="code" id="brn_code" type="hidden"/>
            </div>
            <input type="checkbox" name="active" class="checkbox multi-select"  style="padding:2px;height:auto;" id="cms-active">
            <label for="cms-active">Active</label>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_bank', "addBank", 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?=FORCAPOS_VERSION?>"></script>
<?= $modal_js ?>