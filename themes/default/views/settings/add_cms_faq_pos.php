<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    
$(document).ready(function(){
       
});


</script>

<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel">Add Faq</h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form','id'=>'bankForm');
        echo form_open_multipart("system_settings/add_cms_faq_pos", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="form-group">
                <label class="control-label" for="title"><?= lang('title') ?></label>
                 <input id="title" type="text" placeholder="Title FAQ" name="title" class="form-control file" value="" required>
            </div>
            <div class="form-group">
                <label class="control-label" for="menu"><?= lang('menu') ?></label>
                 <input id="menu" type="text" placeholder="Nama Menu" name="menu" class="form-control file" value="" required>
            </div>
            <div class="form-group">
                <label class="control-label" for="category"><?= lang('category') ?></label>
                <?php
                $m = array();
                $m[''] = lang("select") . ' ' . lang("category");
                foreach ($category as $cat) {
                    $m[$cat->parent_id] = $cat->menu;
                }
                echo form_dropdown('parent_id', $m, '', 'id="category" class="form-control input-tip select" style="width:100%;" required ');
                ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="caption"><?= lang('captions') ?></label>
                 <textarea id="caption" class="form-control" placeholder="Caption FAQ" name="caption" required>
                </textarea>
            </div>
            <input type="checkbox" name="active" class="checkbox multi-select"  style="padding:2px;height:auto;" id="cms-active">
            <label for="cms-active">Active</label>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('tambah', "Tambah", 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?=FORCAPOS_VERSION?>"></script>
<?= $modal_js ?>