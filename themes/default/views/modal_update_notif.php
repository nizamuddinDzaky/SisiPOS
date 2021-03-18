<div class="modal-dialog modal-md">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h2 class="modal-title" id="myModalLabel"><?= lang('whats_new'); ?></h2>
        </div>
        <div class="modal-body" style="max-height: auto; max-height: 350px; overflow-y: auto;">
            <div style="padding:20px">
                <?php
                $last_version = $this->session->userdata('last_update');
                $kronologi = null;
                foreach($show_updates as $row){
                    if($row->release_at != $kronologi) { 
                        $kronologi = $row->release_at; ?>
                        <b style="font-size:18px">Update <?= $row->release_at ?></b> 
                    <?php }?>
                    <ul>
                        <li><div>
                            <?php if($row->type=='bugfix'){ ?> <span class="label label-danger"><?= lang('bugfix') ?></span>
                            <?php }else if($row->type=='new_feature'){ ?> <span class="label label-primary"><?= lang('new_feature') ?></span>
                            <?php }else if($row->type=='enhancement'){ ?> <span class="label label-success"><?= lang('enhancement') ?></span>
                            <?php }else if($row->type=='other'){ ?> <span class="label label-warning"><?= lang('other') ?></span> <?php } ?>
                            <b><?= $row->name ?></b> | <i class="fa fa-clock-o"></i> <?= $row->clock_at ?>
                        </div><div>
                            <?= $row->description ?>
                        </div></li>
                    </ul>
                    <?php 
                    if($last_version < $row->version_num){ 
                        $last_version = $row->version_num; 
                    } ?>
                <?php } ?>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" id="btnass" class="btn btn-primary" data-dismiss="modal" aria-hidden="true"><?= lang("im_understand");?></button>
        </div>
    </div>
</div>

<script type="text/javascript">
    $('#btnass').click(function(){
        $.ajax({
            url: "<?= base_url('welcome/user_last_update') ?>"+"/"+"<?= $last_version ?>",
            method: "GET",
            dataType: "json",
            success: function(data) {}
        });
    });
</script>