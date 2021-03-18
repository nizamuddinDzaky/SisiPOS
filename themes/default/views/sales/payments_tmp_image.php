<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style type="text/css">
    @media print {
        #myModal .modal-content {
            display: none !important;
        }
    }
</style>
<div class="modal-dialog modal-lg no-modal-header">
    <div class="modal-content">
        <div class="modal-body print">
           <img src="<?=$payments_tmp->url_image ?>" width = "100%" height= "100%"> 
        </div>
    </div>
</div>