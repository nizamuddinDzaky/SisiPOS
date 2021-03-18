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
    $(function(){
        $('#MyAlertModal').modal('hide');
    });
    
    function open_partner(id){
        $('#myModal').modal({remote:'Official/registered/'+id});
        $("#myModal").modal('show');
       //<?=site_url('Official/registered/'+id);?> 
    }
</script>
<link rel="stylesheet" href="<?=$assets?>styles/helpers/promo.css?v=<?=FORCAPOS_VERSION?>" type="text/css"/>
<!-- Dialog Modal After Submit Customer Validation -->
<div class="modal fade in" id="MyAlertModal" tabindex="-1" role="dialog" aria-labelledby="MyAlertModal" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body">
              <div id="pesanModal"></div>
            </div>
            <div class="modal-footer">
              <a href="javascript:void(0)" class="btn secondary" data-dismiss="modal">Close</a>
            </div>
        </div>
    </div>
</div>


    <div class="row promo-row">
        <?php foreach($suppliers as $read){ 
            ?>      
        <div class="col-md-3 col-sm-6">
            <div class="promotion-box sticky-promo">
            <div class="promotion-image" style="margin-top:10px;">
                <div class="col-md-3"></div>
                <div class="col-md-6 col-sm-12">
                    <img src="<?= base_url()?>assets/uploads/<?= $read->logo ? 'avatars/'.$read->logo :'no_image.jpg' ?>" class="img-responsive img-full" alt="<?= $read->name?>" scale="0" >
                    <p align="center" style="margin-top:10px;"><?= $read->name?> </p>
                </div>
                <div class="col-md-3"></div>
            </div>
            <div class="promotion-cta">
              <a href="javascript:void(0);" onclick="return open_partner(<?= $read->id?>);" class="promotion__btn partner_md"><?php if(!empty($reference[$read->id])){ echo $reference[$read->id].' <i class="fa fa-edit"></i>';}else{ echo "Daftar / Validasi";}  ?></a>
            </div>
          </div>

        </div>
            <?php }
            ?>
    </div>

