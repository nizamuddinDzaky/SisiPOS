<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
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
            <img src="<?= $payments_tmp_atl->image ?>" width="100%" height="100%">
        </div>
    </div>
</div>