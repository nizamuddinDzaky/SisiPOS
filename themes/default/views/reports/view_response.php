<div class="modal-dialog modal-md no-modal-header">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('view_response');?></h4>
        </div>
        <div class="modal-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                <?php $quest = ""; $answer=0;
                    foreach($response as $row){?>
                        <?php if($quest != $row->question){ $answer++;?>
                            <tr style="background-color:#eee"><th>[<?= $answer; ?>] <?= $row->question; ?></th></tr>
                        <?php } ?>
                        <tr><td><?= $row->answer; ?></td></tr>
                        <?php $quest = $row->question; ?>
                <?php } ?>
                </table>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?= FORCAPOS_VERSION ?>"></script>
<?= $modal_js ?>