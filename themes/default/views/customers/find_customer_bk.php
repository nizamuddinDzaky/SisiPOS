<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    .loader {
        border: 10px solid #d3d5d6;
        border-radius: 50%;
        border-top: 10px solid #3498db;
        width: 120px;
        height: 120px;
        -webkit-animation: spin 2s linear infinite;
        animation: spin 2s linear infinite;
    }

    @-webkit-keyframes spin {
        0% {
            -webkit-transform: rotate(0deg);
        }

        100% {
            -webkit-transform: rotate(360deg);
        }
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .my-custom-scrollbar {
        position: relative;
        height: auto;
        overflow: auto;
    }

    .table-wrapper-scroll-y {
        display: block;
    }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-search"></i><?= lang('find_customer_bk'); ?></h2>
    </div>
</div>
<div class="row">
    <div class="col-sm-3">
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('input_code_bk'); ?></h2>
            </div>
            <div class="box-content">
                <div style="width: 100%;">
                    <form role="form" action="<?php echo base_url('customers/find_bk') ?>" method="post" id="form-search-bk">
                        <div class="row">
                            <div class="col-sm-12" style="margin-top: -3%;">
                                <div class="form-group">
                                    <label class="control-label" for="metode"><?= lang("metode"); ?></label>
                                    <select id="metode" class="form-control" name="metode" required>
                                        <option value="kd_customer">Kode Pelanggan</option>
                                        <option value="kd_dist">Kode Distributor</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12" style="margin-bottom: 5%;">
                                <div class="form-group">
                                    <label class="control-label" for="kode_bk"><?= lang("kode_bk"); ?></label>
                                    <input type="number" class="form-control tip" id="kode_bk" name="kode_bk" required />
                                </div>
                            </div>
                            <div class="col-sm-12" style="margin-bottom: -11%;">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary input-xs" id="search_data_bk" style="width: 100%;"><?= lang("cek"); ?><button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class=" col-sm-9">
        <div id="messages">
            <img src="<?php echo base_url('assets/images/EmptySearch.png') ?>" alt="" style="width: 25%; margin-left: 30%; margin-top: 5%;">
            <label class="control-label" style="margin-left: 29%;font-size: 150%;color: #428bca;margin-top: 4%;">No data available</label>
            <p style="font-size: 110%; color: #333333; margin-left: 16%;">data not found please do a data search to get BK data</p>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $("#form-search-bk").unbind('submit').on('submit', function() {
            var form = $(this);
            $("#search_data_bk").attr('disabled', 'disabled');
            $("#messages").html(`<div class="loader" style="margin-left: 10%; margin-top: 17%;"></div>`);
            $.ajax({
                url: form.attr('action'),
                type: form.attr('method'),
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (!$.trim(response)) {
                        $("#messages").html('<img src="<?php echo base_url('assets/images/EmptySearch.png') ?>" alt="" style="width: 25%; margin-left: 30%; margin-top: 5%;">' +
                            '<label class="control-label" style="margin-left: 28%;font-size: 150%;color: #428bca;margin-top: 4%;">Sorry, no result found</label>' +
                            '<p style="font-size: 110%; color: #333333; margin-left: 17%;">data sought is not found or has not done searching data</p>');
                        $("#search_data_bk").attr('disabled', false);
                    } else {
                        $("#messages").html('<div class=" box">' +
                            '<div class="box-header">' +
                            '<h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('data_customer_in_bk'); ?></h2>' +
                            '</div><div class="box-content">' +
                            '<div class="table-responsive">' +
                            '<div class="table-wrapper-scroll-y my-custom-scrollbar">' +
                            '<table id="databk" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-condensed table-hover table-striped"">' +
                            '<thead>' +
                            '<tr class="primary">' +
                            '<th><?= lang("kode_customer"); ?></th> ' +
                            '<th style="min-width:150px !important;"><?= lang("nama_customer"); ?></th> ' +
                            '<th style="min-width:150px !important;"><?= lang("nama_toko"); ?></th> ' +
                            '<th><?= lang("kode_lt"); ?></th> ' +
                            '<th><?= lang("nama_lt"); ?></th> ' +
                            '<th><?= lang("no_hp"); ?></th> ' +
                            '<th style="min-width:100px !important;"><?= lang("distrik"); ?></th> ' +
                            '<th><?= lang("kecamatan"); ?></th> ' +
                            '<th style="min-width:100px !important;"><?= lang("provinsi"); ?></th> ' +
                            '<th style="min-width:150px !important;"><?= lang("alamat"); ?></th> ' +
                            '<th><?= lang("group"); ?></th> ' +
                            '<th style="min-width:150px !important;"><?= lang("distributor"); ?></th> ' +
                            '<th><?= lang("no_dist"); ?></th> ' +
                            '<th style="min-width:150px !important;"><?= lang("distributor2"); ?></th> ' +
                            '<th><?= lang("no_dist2"); ?></th> ' +
                            '<th style="min-width:150px !important;"><?= lang("distributor3"); ?></th> ' +
                            '<th><?= lang("no_dist3"); ?></th> ' +
                            '<th style="min-width:150px !important;"><?= lang("distributor4"); ?></th> ' +
                            '<th><?= lang("no_dist4"); ?></th> ' +
                            '<th><?= lang("status_smi"); ?></th> ' +
                            '<th><?= lang("status_sbi"); ?></th> ' +
                            '</tr>' +
                            '</thead>' +
                            '<tbody>' + response +
                            '</tbody>' +
                            '</table>' +
                            '</div>' +
                            '</div>' +
                            '</div>' +
                            '</div>');
                        $('#databk').dataTable({
                            "aaSorting": [
                                [0, "asc"]
                            ],
                            "aLengthMenu": [
                                [10, 25, 50, 100, -1],
                                [10, 25, 50, 100, "<?= lang('all') ?>"]
                            ],
                            "iDisplayLength": 10,
                        });
                        $('select.select').select2({
                            minimumResultsForSearch: -1
                        });
                        $("#search_data_bk").attr('disabled', false);
                    }
                }
            });
            return false;
        });
    });
</script>