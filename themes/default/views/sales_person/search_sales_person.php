<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="modal-dialog modal-md">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('synchron_data_sales'); ?></h4>
        </div>
        <form id="form-search-sales" action="<?= base_url('sales_person/search_sales_person') ?>" method="post">
            <div class="modal-body">
                <p><?= lang('info'); ?><?= $cf1->cf1 ? $cf1->cf1 : ($this->session->userdata('company_id') ? $this->session->userdata('company_id') : '-') ?></b></p>
                <p><?= lang('info_2'); ?></p>
                <input type="hidden" name="kode" value="<?= $cf1->cf1 ? $cf1->cf1 : ($this->session->userdata('company_id') ? $this->session->userdata('company_id') : '-') ?>">
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
                <button type="submit" name="search_data_sales" class="btn btn-primary" id="search_data_sales">
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
        $("#search_data_sales").click(function(e) {
            $(this).attr('disabled', 'disabled');
            $(this).html(`<i class="fa fa-circle-o-notch fa-spin"></i>&nbsp;&nbsp;Loading...`);
            e.preventDefault();
            $("#form-search-sales").submit();
        });
        $('select.select').select2({
            minimumResultsForSearch: -1
        });
    });
</script>