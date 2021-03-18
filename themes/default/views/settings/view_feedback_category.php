<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg no-modal-header">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= $category->category; ?></h4>
        </div>
        <div class="modal-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th><?= lang("statement"); ?></p></th>
                            <th><?= lang("status"); ?></p></th>
                        </tr>
                        <?php foreach($question as $row){ ?>
                        <tr>
                            <td><p><?= $row->question; ?></p></td>
                            <?php if($row->is_active){ ?>
                                <td><div class="text-center"><span class="deliv_status label label-success"><?= lang("active"); ?></span></div></td>
                            <?php }else{?>
                                <td><div class="text-center"><span class="deliv_status label label-danger"><?= lang("inactive"); ?></span></div></td>
                            <?php } ?>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?= FORCAPOS_VERSION ?>"></script>
<?= $modal_js ?>