<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$v = "";
$show_form = false;
if ($this->input->post('provinsi')) {
    $v .= "&provinsi=" . $this->input->post('provinsi');
    $show_form = true;
}
if ($this->input->post('kabupaten')) {
    $v .= "&kabupaten=" . $this->input->post('kabupaten');
    $show_form = true;
}
if ($this->input->post('kecamatan')) {
    $v .= "&kecamatan=" . $this->input->post('kecamatan');
    $show_form = true;
}
?>
<script>
    $(document).ready(function() {
        var cTable = $('#CusData').dataTable({
            "aaSorting": [
                [1, "asc"]
            ],
            "aLengthMenu": [
                [10, 25, 50, 100, 200, 500],
                [10, 25, 50, 100, 200, 500]
            ],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true,
            'bServerSide': true,
            'sAjaxSource': '<?= site_url('customers/getCustomers') ?>',
            'fnServerData': function(sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                URLtemp = sSource;
                <?php if ($v == null) : ?>
                    $.ajax({
                        'dataType': 'json',
                        'type': 'POST',
                        'url': sSource,
                        'data': aoData,
                        'success': fnCallback
                    });
                <?php else : ?>
                    URLtemp = URLtemp + '<?= '?v=1' . $v ?>';
                    $.ajax({
                        'dataType': 'json',
                        'type': 'POST',
                        'url': URLtemp,
                        'data': aoData,
                        'success': fnCallback
                    });
                <?php endif; ?>

                $('#select_distributor').change(function() {
                    var distributor = $('#select_distributor').val();
                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });

                    $.ajax({
                        "type": "POST",
                        "dataType": 'json',
                        "url": sSource + '/' + distributor, //sending server side status and filtering table
                        "data": aoData,
                        "success": fnCallback
                    });
                });

            },
            'fnRowCallback': function(nRow, aData, iDisplayIndex) {
                nRow.id = aData[0];
                nRow.className = "customer_details_link";
                return nRow;
            },
            "aoColumns": [{
                "bSortable": false,
                "mRender": checkbox
            }, null, null, null, null, null, null, null, null, null, null, {
                "mRender": currencyFormat
            }, null, null, {
                "bSortable": false
            }]
        }).dtFilter([{
                column_number: 1,
                filter_default_label: "[<?= lang('company'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 2,
                filter_default_label: "[<?= lang('name'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 3,
                filter_default_label: "[<?= lang('email_address'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 4,
                filter_default_label: "[<?= lang('phone'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 5,
                filter_default_label: "[<?= lang('price_group'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 6,
                filter_default_label: "[<?= lang('provinsi'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 7,
                filter_default_label: "[<?= lang('city'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 8,
                filter_default_label: "[<?= lang('state'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 9,
                filter_default_label: "[<?= lang('customer_group'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 10,
                filter_default_label: "[<?= lang('vat_no'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 11,
                filter_default_label: "[<?= lang('deposit'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 12,
                filter_default_label: "[<?= lang('award_points'); ?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 13,
                filter_default_label: "[<?= lang('customers_code'); ?>]",
                filter_type: "text",
                data: []
            },
        ], "footer");
        $('.toggle_up').click(function() {
            $("#form").slideUp();
            return false;
        });
        $('.toggle_down').click(function() {
            $("#form").slideDown();
            return false;
        });
        if (localStorage.getItem('tour-sign_up')) {
            localStorage.removeItem('tour-sign_up');
        }
        <?php if ($show_form) { ?>
            setTimeout(function() {
                $("#form").slideDown();
            }, 100);
        <?php } ?>
        $('#form').hide();
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('customers'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?= site_url('customers/add'); ?>" data-toggle="modal" data-target="#myModal" data-backdrop="static" id="add">
                                <i class="fa fa-plus-circle"></i> <?= lang("add_customer"); ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= site_url('customers/import_csv'); ?>" data-toggle="modal" data-target="#myModal" data-backdrop="static">
                                <i class="fa fa-plus-circle"></i> <?= lang("import_by_csv"); ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= site_url('customers/update_by_excel'); ?>" data-toggle="modal" data-target="#myModal" data-backdrop="static">
                                <i class="fa fa-file-excel-o"></i> <?= lang("update_by_excel"); ?>
                            </a>
                        </li>
                        <?php if ($Owner || $Admin || $Principal || $GP['bulk_actions']) { ?>
                            <li>
                                <a href="#" id="excel" data-action="export_excel">
                                    <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                                </a>
                            </li>
                            <li>
                                <a href="#" id="pdf" data-action="export_pdf">
                                    <i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </li>
                <li class="dropdown">
                    <?php echo anchor('customers/search_toko_aktif', '<i class="icon fa fa-refresh tip" data-placement="left" title="' . lang("synchron") . '"></i> ', 'data-toggle="modal" data-target="#myModal"  data-backdrop="static"') ?>
                </li>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-book tip" data-placement="left" title="<?= lang("manual_book") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= $mb_customers ?>" target="_blank"><i class="fa fa-book tip"></i> <?= lang('customers') ?></a></li>
                        <li><a href="<?= $mb_add_customer ?>" target="_blank"><i class="fa fa-book tip"></i> <?= lang('add_customer') ?></a></li>
                        <li><a href="<?= $mb_edit_customer ?>" target="_blank"><i class="fa fa-book tip"></i> <?= lang('edit_customer') ?></a></li>
                    </ul>
                </li>
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('list_results'); ?></p>
                <form action="<?= base_url('customers'); ?>" method="post" accept-charset="utf-8">
                <div id="form">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="provinsi_customer_filter"><?= lang("provinsi"); ?></label>
                                <select name="provinsi" id="provinsi_customer_filter" onchange="setProvinsi_customer(this.value,this.options[this.selectedIndex].innerHTML)" class="form-control select">
                                    <?php if ($this->input->post('provinsi') != '') { ?>
                                        <option value="<?= $this->input->post('provinsi') ?>" selected><?= $this->input->post('provinsi') ?></option>
                                    <?php } else { ?>
                                        <option value="" selected="selected"><?= lang('choose_province') ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="city_cutomer_filter"><?= lang("city"); ?></label>
                                <select name="kabupaten" id="city_cutomer_filter" onchange="setKabupaten_customer(this.value,this.options[this.selectedIndex].innerHTML)" class="form-control select">
                                    <?php if ($this->input->post('kabupaten') != '') { ?>
                                        <option value="<?= $this->input->post('kabupaten') ?>" selected><?= $this->input->post('kabupaten') ?></option>
                                    <?php } else { ?>
                                        <option value="" selected="selected"><?= lang('choose_city') ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="state_customer_filter"><?= lang("state"); ?></label>
                                <select name="kecamatan" id="state_customer_filter" class="form-control select">
                                    <?php if ($this->input->post('kecamatan') != '') { ?>
                                        <option value="<?= $this->input->post('kecamatan') ?>" selected><?= $this->input->post('kecamatan') ?></option>
                                    <?php } else { ?>
                                        <option value="" selected="selected"><?= lang('choose_district') ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group" style="float: right;">
                        <div class="controls"><?php echo form_submit('submit', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <div class="form-group" style="float: right; margin-right: 10px;">
                        <div class="controls"><a class="btn btn-danger" id="reset" href=<?= base_url('customers') ?>><?= lang('reset') ?></a></div>
                    </div>
                </div>
                </form>

                <?php if ($Owner || $Admin || $Principal || $GP['bulk_actions']) {
                    echo form_open('customers/customer_actions');
                } ?>
                <div class="table-responsive">
                    <table id="CusData" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                            <tr class="primary">
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check" />
                                </th>
                                <th><?= lang("company"); ?></th>
                                <th><?= lang("name"); ?></th>
                                <th><?= lang("email_address"); ?></th>
                                <th><?= lang("phone"); ?></th>
                                <th><?= lang("price_group"); ?></th>
                                <th><?= lang("provinsi"); ?></th>
                                <th><?= lang("city"); ?></th>
                                <th><?= lang("state"); ?></th>
                                <th><?= lang("customer_group"); ?></th>
                                <th><?= lang("vat_no"); ?></th>
                                <th><?= lang("deposit"); ?></th>
                                <th><?= lang("award_points"); ?></th>
                                <th><?= lang("customers_code"); ?></th>
                                <th style="min-width:135px !important;"><?= lang("actions"); ?></th>
                            </tr>
                        </thead>
                        <tbody id="tb_cust">
                            <tr>
                                <td colspan="15" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                            </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                            <tr class="active">
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkft" type="checkbox" name="check" />
                                </th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th style="min-width:135px !important;" class="text-center"><?= lang("actions"); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <?php if ($Owner || $Admin || $GP['bulk_actions'] || $Principal) { ?>
                    <div style="display: none;">
                        <input type="hidden" name="form_action" value="" id="form_action" />
                        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
                    </div>
                    <?= form_close(); ?>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="statusmodal">
    <div class="modal-dialog" role="document" style="width: 20%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" aria-label="Close" id="close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <center>
                    <p><?= lang('status_tracking') ?></p>
                </center>
            </div>
            <div class="modal-footer">
                <center>
                    <button type="button" class="btn btn-primary" id="ok">Ok</button>
                </center>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<?php if ($action && $action == 'add') {
    echo '<script>$(document).ready(function(){$("#add").trigger("click");});</script>';
}
?>
<script type="text/javascript">
    $(document).ready(function() {
        $.getJSON('<?php echo base_url(); ?>daerah/getProvinsi', function(data) {
            var output = "";
            <?php if ($this->input->post('provinsi') == '') { ?>
                output += '<option value="" data-foo="">Choose Province</option>';
            <?php } ?>
            $.each(data, function(key, val) {
                selected = '';
                if (val.province_name == '<?= $this->input->post('provinsi') ?>') {
                    selected = 'selected';
                }
                output += '<option value="' + val.province_name + '" data-foo="" ' + selected + '>' + val.province_name + '</option>';
            });
            $("#provinsi_customer_filter").html(output);

        });
        <?php if ($this->input->post('provinsi') != '') { ?>
            $('#provinsi_customer_filter').change();
        <?php } ?>
    });

    function setProvinsi_customer(id, text) {
        $('#modal-loading').show();
        var urlProvinsi = base_url + "/daerah/getKabupaten/" + text.replace(/\s+/g, '_') + "/";
        var output = "";
        <?php if ($this->input->post('kabupaten') == '') { ?>
            output += '<option value="" data-foo="">Choose City</option>';
        <?php } ?>
        $.getJSON(urlProvinsi, function(data) {
            $.each(data, function(key, val) {
                selected = '';
                if (val.kabupaten_name == '<?= $this->input->post('kabupaten') ?>') {
                    selected = 'selected';
                }
                output += '<option value="' + val.kabupaten_name + '" data-foo="" ' + selected + '>' + val.kabupaten_name + '</option>';
            });

            $("#city_cutomer_filter").html(output);
            $('#modal-loading').hide();
        });
    }

    function setKabupaten_customer(id, text) {
        $('#modal-loading').show();
        var urlProvinsi = base_url + "/daerah/getKecamatan/" + text.replace(/\s+/g, '_') + "/";
        var output = "";
        <?php if ($this->input->post('kecamatan') == '') { ?>
            output += '<option value="" data-foo="">Choose District</option>';
        <?php } ?>
        $("#state_customer_filter").html(output);
        $('select[name=kecamatan]').val('').change();
        $.getJSON(urlProvinsi, function(data) {
            $.each(data, function(key, val) {
                selected = '';
                if (val.kecamatan_name == '<?= $this->input->post('kecamatan') ?>') {
                    selected = 'selected';
                }
                output += '<option value="' + val.kecamatan_name + '" data-foo="">' + val.kecamatan_name + '</option>';
            });
            $("#state_customer_filter").html(output);
            $('#modal-loading').hide();
        });
        <?php if ($this->input->post('kecamatan') != '') { ?>
            $('#state_customer_filter').change();
        <?php } ?>
    }
</script>