<?php

/* 
 * Copyright (c) 2018 adminSISI.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    adminSISI - initial API and implementation and/or initial documentation
 */


?>

<script type="text/javascript">
	 $(document).ready(function(){
		$('#change-password-form').submit( function( e ) {
        $.ajax({
          url: 'welcome/addEcomerce',
          type: 'POST',
          data: new FormData( this ),
          processData: false,
          contentType: false,
          success: function(data){
                try {
					 var datas = $.parseJSON(data);
					 alert(datas.message);
					 $('#myModal').modal('hide');
                } catch(e) {
                    alert("Data Tidak Bisa Di Proses");
                    //JSON parse error, this is not json (or JSON isn't in your browser)
                }

          }
        });
    e.preventDefault();
  });
	 });
</script>
<?php  if(empty($ecomerce->mtid)){ ?>;
<div class="modal-dialog modal-xs">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel">Anda belum terdaftar ke ecomerce, Masukan password untuk melakukan pendaftaran di Ecomerce</h4>
        </div>
            <?php echo form_open("javascript:void(0);", 'id="change-password-form"'); ?>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-5">
                    <div class="form-group">
                        
                        <?php echo lang('password', 'curr_password'); ?> <br/>
                        <?php echo form_password('password', '', 'class="form-control" id="curr_password" required="required"'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
          <p><?php echo form_submit('add Ecomerce', lang('add Ecomerce'), 'class="btn btn-primary"'); ?></p>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?php }else{?>
<div class="modal-dialog modal-sm">
    <div class="modal-content">
        <div class="modal-body">
          <p>This page is asking you to confirm that you want to leave - data you have entered may not be saved.</p>
        </div>
        <div class="modal-footer">
          <a href="<?php echo $this->MateriaLink ?>" class="btn danger">Leave Page</a>
          <a href="javascript:void(0)" class="btn secondary" data-dismiss="modal">Stay on Page</a>
        </div>
    </div>
</div>
<?php } ?>