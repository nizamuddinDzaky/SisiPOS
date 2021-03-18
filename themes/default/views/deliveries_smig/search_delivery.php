<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<script>
    $('select.select').select2({
        minimumResultsForSearch: 7
    });
    var old_sent;
    $(document).on("focus", '.sent', function() {
        old_sent = $(this).val();
    }).on("change", '.sent', function() {
        var new_sent = $(this).val() ? $(this).val() : 1;
        if (!is_numeric(new_sent)) {
            $(this).val(old_sent);
            return;
        } else if (new_sent > $(this).data('remaining')) {
            $(this).val($(this).data('remaining'));
            return;
        } else if (new_sent < 1) {
            $(this).val(1);
            return;
        }
    });
</script>

<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('synchron_data'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("deliveries_smig/search_delivery"); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("So_Number", "so_number"); ?>
                        <?php echo form_input('so_number', (isset($_POST['so_number'])), 'class="form-control input-tip" id="so_number"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("Spj_Number", "spj_number"); ?>
                        <?php echo form_input('spj_number', (isset($_POST['spj_number'])), 'class="form-control input-tip" id="spj_number" required="required"'); ?>
                    </div>

                    <div class="form-group">
                        <?= lang("ekspeditor", "ekspeditor"); ?>
                        <?php echo form_input('ekspeditor', (isset($_POST['ekspeditor'])), 'class="form-control input-tip" id="ekspeditor"'); ?>
                    </div>
                </div>
                <div class="col-md-6">

                    <div class="form-group">
                        <?= lang('plant', 'plant'); ?>
                        <select class="form-control input-tip select" id="plant" name="plant" required="required">
                            <option value=""></option>
                            <?php foreach ($plant as $k => $v) : ?>
                                <option value="<?php echo $v['plant'] ?>"><?php echo $v['name'] ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <?= lang("date_do", "bulan"); ?>
                        <?php echo form_input('bulan', (isset($_POST['bulan']) ? $_POST['bulan'] : ""), 'class="form-control input-tip date" id="bulan" required="required"'); ?>
                    </div>

                    <div class="form-group">
                        <?= lang("Distrik", "distrik"); ?>
                        <select class="form-control input-tip select" id="distrik" name="distrik">
                            <option value=""></option>
                            <?php foreach ($distrik as $k => $v) : ?>
                                <option value="<?php echo $v['kode_distrik'] ?>"><?php echo $v['distrik'] ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?= form_submit('search_delivery', lang('synchron'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?= form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['sma'] = <?= $dp_lang ?>;
</script>
<?= $modal_js ?>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function() {
        $.fn.datetimepicker.dates['sma'] = <?= $dp_lang ?>;
    });
</script>