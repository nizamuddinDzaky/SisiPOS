<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="modal-dialog modal-md">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('synchron_data_toko'); ?></h4>
        </div>
        <form id="form-search-toko" action="<?= base_url('customers/search_toko_aktif') ?>" method="post">
            <div class="modal-body">
                <?php if (!$this->LT) { ?>
                    <?php if (strtoupper($cf1->cf6) == "SBI") { ?>
                        <p>
                            <?= lang('info1'); ?>
                            <?= strtoupper($cf1->cf6) ?>
                            <img src="<?= base_url('assets/uploads/logos/dynamix.png') ?>" style="width: 60px;height: 40px;" />
                            <?= lang('info_1'); ?>
                        </p>
                        <input type="hidden" name="supplier" value="<?= $cf1->cf6 ?>">
                    <?php } elseif (strtoupper($cf1->cf6) == "SMI") { ?>
                        <p>
                            <?= lang('info1'); ?>
                            <?= strtoupper($cf1->cf6) ?>
                            <img src="<?= base_url('assets/uploads/logos/sg-sp-st.png') ?>" style="height: 30px; margin-right:5px; margin-left:5px;" />
                            <?= lang('info_1'); ?>
                        </p>
                        <input type="hidden" name="supplier" value="<?= $cf1->cf6 ?>">
                    <?php } ?>
                    <p><?= lang('info'); ?><?= $cf1->cf1 ? $cf1->cf1 : ($this->session->userdata('company_id') ? $this->session->userdata('company_id') : '-') ?></b></p>
                    <p><?= lang('info_2'); ?></p>
                    <input type="hidden" name="kode" value="<?= $cf1->cf1 ? $cf1->cf1 : ($this->session->userdata('company_id') ? $this->session->userdata('company_id') : '-') ?>">
                <?php } else { ?>
                    <p><?= lang('info'); ?><?= $code ? $code : '-' ?></b></p>
                    <p><?= lang('info_2'); ?></p>
                    <input type="hidden" name="kode" value="<?= $code ? $code : '-' ?>">
                <?php } ?>
                <div class="form-group">
                    <label for="sync_strategy" class="control-label"><?= lang('sync_strategy') ?></label>
                    <select name="sync_strategy" id="sync_strategy" class="form-control select" required>
                        <option value="strategy_1"><?= lang('strategy_1') ?></option>
                        <option value="strategy_2"><?= lang('strategy_2') ?></option>
                        <option value="strategy_3"><?= lang('strategy_3') ?></option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="reset" class="btn btn-danger" data-dismiss="modal" aria-hidden="true">
                    <?= lang('cancel') ?>
                </button>
                <button type="submit" name="search_data_toko" class="btn btn-primary" id="search_data_toko">
                    <i class="icon fa fa-refresh tip"></i>
                    &nbsp;&nbsp;<?= lang('synchron') ?>
                </button>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script>
$(document).ready(function() {
    $("#search_data_toko").click(function (e) {
        $(this).attr('disabled','disabled');
        $(this).html(`<i class="fa fa-circle-o-notch fa-spin"></i>&nbsp;&nbsp;Loading...`);
        e.preventDefault();
        $("#form-search-toko").submit();
    });
    $('select.select').select2({
        minimumResultsForSearch: -1
    });
});
</script>