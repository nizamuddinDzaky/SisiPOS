<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<script>
    var dTable = null;
    var user_promotion_selected = <?= json_encode($list_company_id_toko_selected) ?>;

    $(document).ready(function () {
        load_datatables('<?= site_url('system_settings/getAllCustomers?promo_id='.$detail_promo->id) ?>');

        function load_datatables(init_url){
            if(dTable)
                dTable.fnDestroy();
            
            dTable = $('#StCaRData').dataTable({
                        "aaSorting": [[0, "asc"]],
                        "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                        "iDisplayLength": 25,
                        'bProcessing': true, 'bServerSide': true,
                        'sAjaxSource': init_url,
                        'fnServerData': function (sSource, aoData, fnCallback) {
                            aoData.push({
                                "name": "<?= $this->security->get_csrf_token_name() ?>",
                                "value": "<?= $this->security->get_csrf_hash() ?>"
                            });
                            $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
                        },
                        "aoColumns": [{"bSortable": false,"mRender": checkbox_auto_checked}, null, null, null, null, null]
                    });
        }

        $('#submit').on('click', function(){
            load_datatables("system_settings/getAllCustomers?promo_id=<?= $detail_promo->id ?>&provinsi="+$('#provinsi').val()+"&kabupaten="+$('#kabupaten').val()+"&company_id="+$('#distributor').val());
        });

        $("#reset-filter").on("click", function(){
            $('#provinsi').val(null).trigger('change');
            $('#kabupaten').val(null).trigger('change');
            <?php if($detail_promo->supplier_id <= 0) : ?>
                $('#distributor').val(null).trigger('change');
            <?php endif ?>

            load_datatables("system_settings/getAllCustomers?promo_id=<?= $detail_promo->id ?>");
        });

        function checkbox_auto_checked(value) {
            if(user_promotion_selected.indexOf(value) != -1)
                return '<div class="text-center"><input checked="checked" class="checkbox multi-select" type="checkbox" name="val[]" value="' + value + '" /></div>';
            else
                return '<div class="text-center"><input class="checkbox multi-select" type="checkbox" name="val[]" value="' + value + '" /></div>';
        }
        
        $('#filter-form').hide();
        $('.toggle_down').click(function () {
            $("#filter-form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#filter-form").slideUp();
            return false;
        });

    });
</script>

<div class="row">
    <div class="col-sm-8">
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-gift"></i><?= $page_title ?> (<?= $detail_promo->name ?>)</h2>
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
                        <div id="filter-form">
                            <div class="row">
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <?= lang('Province', 'Province'); ?>
                                        <select name="provinsi" id="provinsi" class="form-control select">
                                            <option value="">Choose Province</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <?= lang('City', 'City'); ?>
                                    <select name="kabupaten" id="kabupaten" class="form-control select">
                                        <option value="">Choose City</option>
                                    </select>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label class="control-label"><?= lang("distributor"); ?></label>
                                        <input type="text" id="distributor" class="form-control select" name="company_id" <?= ($detail_promo->supplier_id > 0) ? 'disabled="disabled"' : '' ?> value="<?= $distributor->company ?>">
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="row">
                                        <div class="col-sm-6" style="padding-right: 2px;">
                                            <input type="submit" style="width: 100%; margin-top: 28px;" id="submit" name="submit" value="Submit" class="btn btn-primary input-xs">
                                        </div>
                                        <div class="col-sm-6" style="padding-left: 2px;">
                                            <input type="reset" style="width: 100%; margin-top: 28px;" id="reset-filter" name="reset-filter" value="Reset" class="btn btn-warning input-xs">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row" style="height: 480px; overflow-y: scroll;">
                            <div class="col-sm-12">
                                <div class="table-responsive">
                                    <table id="StCaRData" class="table table-bordered table-hover table-striped">
                                        <thead>
                                            <tr>
                                                <th style="min-width:30px; width: 30px; text-align: center;">
                                                    <input class="checkbox checkth" type="checkbox" name="check" />
                                                </th>
                                                <th><?php echo $this->lang->line("company"); ?></th>
                                                <th><?php echo $this->lang->line("name"); ?></th>
                                                <th><?php echo $this->lang->line("distributor"); ?></th>
                                                <th><?php echo $this->lang->line("phone"); ?></th>
                                                <th><?php echo $this->lang->line("customers_code"); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="6" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-list"></i><?= lang('toko_selected', 'Store Selected'); ?></h2>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                        <?php echo form_open("system_settings/save_selected_toko/".$id); ?>
                            <div style="width: 100%; height: 373px; overflow-y: scroll;">
                                <ul id="list-toko-selected" class="list-group" style="text-align: center; width: 100%; height: 100%; background-color: #dedede;">
                                    <?php if(count($list_toko_selected) <= 0) : ?>
                                        <div style="padding: 70px;"><?= lang('no_selected_store') ?></div>
                                    <?php else: ?>
                                        <?php foreach ($list_toko_selected as $row) { ?>
                                            <li id="toko_selected_<?= $row['company_id'] ?>" class="list-group-item d-flex justify-content-between align-items-center" style="text-align: left;">
                                                <?= $row['company'] ?>
                                                <input type="hidden" value="<?= $row['company_id'] ?>~<?= $row['supplier_id'] ?>" name="list_toko[]">
                                                <a href="javascript:void(0)" onclick="unselect_toko('toko_selected_<?= $row['company_id'] ?>', '<?= $row['company_id'] ?>~<?= $row['company'] ?>~<?= $row['supplier_id'] ?>')" class="badge badge-primary badge-pill">
                                                    <i class="fa-fw fa fa-close"></i>
                                                </a>
                                            </li>
                                        <?php } ?>
                                    <?php endif ?>
                                </ul>
                            </div>
                            <?= lang('total_toko_selected', 'Total Store Selected'); ?> : <span id="total-seleced"><?= count($list_company_id_toko_selected) ?></span>
                            <div class="form-group">
                                <input type="submit" style="width: 100%; margin-top: 28px;" name="submit_report" value="Submit" class="btn btn-primary input-xs">
                            </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $.getJSON('<?php echo base_url(); ?>daerah/getProvinsi', function(data) {
            var output = '<option value="" selected data-foo="">Choose Province</option>';
            $.each(data, function(key, val) {
                output += '<option value="' + val.province_name + '" data-foo="">' + val.province_name + '</option>';
            });
            
            $("#provinsi").html(output);
            $('#StCaRData_processing').css('visibility', 'hidden');
        });

        $('#provinsi').on('change', function(){
            $('#kabupaten').val(null).trigger('change');
            $.getJSON('<?php echo base_url(); ?>daerah/getKabupaten/'+$("#provinsi").val(), function(data) {
                var output = '<option value="" selected data-foo="">Choose City</option>';
                $.each(data, function(key, val) {
                    output += '<option value="' + val.kabupaten_name + '" data-foo="">' + val.kabupaten_name + '</option>';
                });
                $("#kabupaten").html(output);
                $('#StCaRData_processing').css('visibility', 'hidden');
            });
        });
        <?php if($detail_promo->supplier_id <= 0) : ?>
            $('#distributor').select2({
                minimumInputLength: 1,
                data: [],
                initSelection: function (element, callback) {
                    $.ajax({
                        type: "get", async: false,
                        url: site.base_url + "system_settings/getDistributorPerdaerah?provinsi="+$('#provinsi').val()+"&kabupaten="+$('#kabupaten').val()+"&term="+$(element).val()+"&limit=10",
                        dataType: "json",
                        success: function (data) {
                            callback(data.results[0]);
                        }
                    });
                },
                ajax: {
                    url: site.base_url + "system_settings/getDistributorPerdaerah",
                    dataType: 'json',
                    quietMillis: 15,
                    data: function (term, page) {
                        return {
                            provinsi: $('#provinsi').val(),
                            kabupaten: $('#kabupaten').val(),
                            term: term,
                            limit: 10
                        };
                    },
                    results: function (data, page) {
                        $('#StCaRData_processing').css('visibility', 'hidden');
                        if (data.results != null) {
                            return {results: data.results};
                        } else {
                            return {results: [{id: '', text: 'No Match Found'}]};
                        }
                    }
                }
            });
        <?php endif ?>
    });

    function unselect_toko(id, value){
        var checkbox = $('.checkbox');
        $('#'+id).remove();
        var is_checkbox = true;
        for(var i = 0; i < checkbox.length; i++){
            if(checkbox[i].value == value){
                is_checkbox = false;
                $(checkbox[i]).iCheck('uncheck');
            }
        }

        if(is_checkbox){
            user_promotion_selected.splice(user_promotion_selected.indexOf(this.value), 1);
            $("#total-seleced").html(user_promotion_selected.length);
        }
    }

    $(document).on('ifChecked', '.checkbox', function(event) {
        var checkbox = $('.checkbox');
        for(var i = 0; i < checkbox.length; i++){
            if(checkbox[i] == this && this.value != 'on'){
                var company_id         = this.value.split('~')[0];
                var company_name    = this.value.split('~')[1];
                var supplier_id     = this.value.split('~')[2];

                if(user_promotion_selected.length <= 0)
                    $("#list-toko-selected").html("");

                user_promotion_selected.push(this.value);
                $("#total-seleced").html(user_promotion_selected.length);
                $('#list-toko-selected').prepend('<li id="toko_selected_'+company_id+'" class="list-group-item d-flex justify-content-between align-items-center" style="text-align: left;">'+
                                        company_name+
                                        '<input type="hidden" value="'+company_id+'~'+supplier_id+'" name="list_toko[]">'+
                                        '<a href="javascript:void(0)" onclick="unselect_toko(\'toko_selected_'+company_id+'\', \''+this.value+'\')" class="badge badge-primary badge-pill">'+
                                            '<i class="fa-fw fa fa-close"></i>'+
                                        '</a>'+
                                    '</li>');
            }
        }
    });

    $(document).on('ifUnchecked', '.checkbox', function(event) {
        var checkbox = $('.checkbox');
        for(var i = 0; i < checkbox.length; i++){
            if(checkbox[i] == this){
                var company_id         = this.value.split('~')[0];
                var company_name    = this.value.split('~')[1];
                var supplier_id     = this.value.split('~')[2];

                user_promotion_selected.splice(user_promotion_selected.indexOf(this.value), 1);
                $("#total-seleced").html(user_promotion_selected.length);
                $('#toko_selected_'+company_id).remove();

                if(user_promotion_selected.length <= 0)
                    $("#list-toko-selected").html('<div style="padding: 70px;"><?= lang('no_selected_store') ?></div>');

            }
        }
    });
</script>