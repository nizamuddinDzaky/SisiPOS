<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa fa-credit-card"></i><?= $page_title ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo $this->lang->line("list_results"); ?></p>
                <div class="table-responsive">
                    <table id="GData" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Duration</th>
                                <th style="max-width:85px;"><?php echo lang("active"); ?></th>
                                <th style="max-width:85px;"><?php echo lang("update"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($duration as $key => $value) {?>
                            <tr>
                                <?php
                                    $checked = '';
                                    $keyPermission = array_search($key, array_column($term_kreditpro, 'term'));
                                    if (gettype($keyPermission) != 'boolean') {
                                        if ($term_kreditpro[$keyPermission]->is_active == 1) {
                                            $checked = 'checked';
                                        }
                                    }
                                ?>
                                <td style="text-align: center;"><?=$value?></td>
                                <td><div class="text-center"><input type="checkbox" name="is_multiple" class="checkbox multi-select duration" value="<?=$key?>" <?= $checked ?> style="padding:2px;height:auto;"></div></td>
                                <td><div class="text-center"><button class="btn btn-primary btn-xs form-submit" type="button"><i class="fa fa-pencil"></i></button></div></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>
</div>

<div class="modal bootbox  fade bootbox-confirm in" id="modal-confirm" tabindex="-1" role="dialog"   aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="bootbox-close-button close" data-dismiss="modal" aria-hidden="true" style="margin-top: -10px;">
                <i class="fa fa-2x">×</i></button>
                <br>
                <div class="bootbox-body"><?= lang('notif_credit_limit'); ?> 
                </div>
            </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="submit-sale" onclick="submitsale();">Ok</button>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    // $( ".duration" ).prop( "checked", true );
    $('.bootbox').on('hidden.bs.modal', function (e) {
            $('#add_item').focus();
        });

    $(document).on('click', '.form-submit', function () {
        
        var btn = $(this);
        btn.html('<i class="fa fa-circle-o-notch fa-spin fa-fw"></i>');
        var row = btn.closest('tr');
        var duration = row.find('.duration').val();
        var is_active = row.find('.duration').is(":checked");
        
        $.ajax({
            type: 'post',
            url: '<?= site_url('system_settings/term_payment_kreditpro'); ?>',
            dataType: "json",
            data: {
                <?= $this->security->get_csrf_token_name() ?> : '<?= $this->security->get_csrf_hash() ?>',
                duration: duration,
                is_active : is_active    
            },
            success: function (data) {
                if (data.status != 1){
                    btn.removeClass('btn-primary').addClass('btn-danger').html('<i class="fa fa-times"></i>');
                    bootbox.alert('Minimal harus ada satu yang di aktifkan');
                }
                else{
                    btn.removeClass('btn-primary').removeClass('btn-danger').addClass('btn-success').html('<i class="fa fa-check"></i>');
                }
            },
            error: function (data) {
                btn.removeClass('btn-primary').addClass('btn-danger').html('<i class="fa fa-times"></i>');
            }
        });
    });
})
</script>