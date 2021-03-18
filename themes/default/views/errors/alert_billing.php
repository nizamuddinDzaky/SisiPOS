<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('warning'); ?></h4>
        </div>
        <div class="modal-body">
            <!--<p><?= lang('enter_info'); ?></p>-->
            <div class="form-group">
                <a class="btn btn-warning form-group" id="back_home" style="width: 100%" href="<?= site_url('welcome')?>"><?=lang('back')?></a>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?=FORCAPOS_VERSION?>"></script>
<?= $modal_js ?>