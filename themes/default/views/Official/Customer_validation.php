<!DOCTYPE html>
<!--
Copyright (c) 2018 adminSISI.
All rights reserved. This program and the accompanying materials
are made available under the terms of the Eclipse Public License v1.0
which accompanies this distribution, and is available at
http://www.eclipse.org/legal/epl-v10.html

Contributors:
   adminSISI - initial API and implementation and/or initial documentation
-->
<script type="text/javascript">
    $(document).ready(function(){
        $('#intregister_suppliers').trigger("reset");
        $('#intregister_suppliers').bootstrapValidator({
            fields: {
                reference_kode1: {
                    message: 'Reference not valid',
                    validators: {
                        notEmpty: {
                            message: 'reference is required and cannot be empty'
                        },
                        stringLength: {
                            min: 6,
                            max: 15,
                            message: 'reference must be more than 6 and less than 15 characters long'
                        },
                        regexp: {
                            regexp: /^[a-zA-Z0-9_]+$/,
                            message: 'reference can only consist of alphabetical, number and underscore'
                        }
                    }
                }
            }
        });
    });
    $('#intregister_suppliers').unbind('submit').submit( function( e ) {
        e.preventDefault();
        $.ajax({
            url: 'Official/add_registered',
            type: 'POST',
            data: new FormData( this ),
            cache : false,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function(data, textstatus, request){
                console.log(data);
                try {
                    $('#pesanModal').html(data.message);
                    $('#MyAlertModal').modal('show');
                    $('#myModal').modal('hide');
                } catch(e) {
                    console.log(e);
                    //JSON parse error, this is not json (or JSON isn't in your browser)
                }
            },
            error:function(){
                $('#myModal2').html("Error?!");
                $('#myModal2').modal("show");
            }   
        });
        return false;
    });
</script>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i> </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('Register_Supplier'); ?></h4>
        </div>
        <div class="modal-body">
             <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form','id'=>'intregister_suppliers');
			 echo form_open("javascript:void(0);", $attrib); ?>
             <div class="row">
                 <div class="col-md-6">
                    <div class="form-group">
                        <?= lang('reference_kode', 'reference_kode1'); ?>
                        <?= form_input('reference_kode1', $reference[0], 'class="form-control" id="reference_kode1" required="required" minlength="6" '); ?>             
                    </div>
                      <?= form_hidden('read_supplier', $vsupplier , 'class="form-control" id="supplier" required="required"'); ?>
                </div>
            </div>
            <p>*Pastikan profile perusahaan terisi, Untuk mengubah masuk ke profile</p>
            <p>**isi text "daftar" di reference code untuk mendaftar</p>
            <div class="modal-footer">
                <!-- <button type="submit" class="btn btn-primary" >Confirm</button> -->
				<?php echo form_submit('submit_validation', lang('Confirm'), 'class="btn btn-primary"'); ?>
				<!--<button type="button" class="btn btn-primary" >Confirm</button>-->
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?=FORCAPOS_VERSION?>"></script>
<?= $modal_js ?>
