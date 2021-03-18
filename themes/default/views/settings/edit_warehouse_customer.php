<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
    $v = "";
    if ($this->input->post('provinsi')) {
        $v .= "&provinsi=" . $this->input->post('provinsi');
    }
    if ($this->input->post('kabupaten')) {
        $v .= "&kabupaten=" . $this->input->post('kabupaten');
    }
?>

<style>

</style>

<style>
/* The container */
.custom_check {
  display: block;
  position: relative;
  padding-left: 25px;
    margin-bottom: 1px;
    margin-top: 0px;
  cursor: pointer;
  /* font-size: 22px; */
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  font-weight: 500;
}

/* Hide the browser's default checkbox */
.custom_check input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  height: 0;
  width: 0;
}

/* Create a custom checkbox */
.checkmark_custom {
  position: absolute;
  top: 0;
  left: 0;
  height: 22px;
  width: 22px;
  background-color: #fff;
    border: 1px solid #eee;
}

/* On mouse-over, add a grey background color */
.custom_check:hover input ~ .checkmark_custom {
  border-color: #428bca;
}

/* When the checkbox is checked, add a blue background */
.custom_check input:checked ~ .checkmark_custom {
  background-color: #428bca;
  border: 0px solid #428bca;
}

/* Create the checkmark_custom/indicator (hidden when not checked) */
.checkmark_custom:after {
  content: "";
  position: absolute;
  display: none;
}

/* Show the checkmark_custom when checked */
.custom_check input:checked ~ .checkmark_custom:after {
  display: block;
}

/* Style the checkmark_custom/indicator */
.custom_check .checkmark_custom:after {
  left: 8px;
  top: 4px;
  width: 6px;
  height: 10px;
  border: solid white;
  border-width: 0 3px 3px 0;
  -webkit-transform: rotate(45deg);
  -ms-transform: rotate(45deg);
  transform: rotate(45deg);
}
</style>

<script type="text/javascript">
    var dataCustomer = JSON.parse(`<?= json_encode($warehouse_customer)?>`);
    $(document).ready(function () {
        $('#customer_data').dataTable({
            "aaSorting": [[0, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": 25,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('system_settings/get_warehouse_customer?id_pg='.$warehouse->id.$v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"bSortable": false,"mRender": checkbox_cpg}, null, null, null, null, null, null]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?= lang('company'); ?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?= lang('name'); ?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?= lang('province'); ?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?= lang('city'); ?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?= lang('state'); ?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?= lang('customer_code'); ?>]", filter_type: "text", data: []}
        ], "footer");

        <?php if ($this->input->post('provinsi') == '' && $this->input->post('kabupaten') == '') {?>
            $('#form').hide();
        <?php }?>
        
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
    });
</script>

<input type="text" class="hidden" id="fieldname" value="">
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus-square-o"></i><?= lang('warehouse_customer'); ?> (<?= strtoupper($warehouse->name)?>)</h2>
    </div>
</div>

<div class="row">
    <div class="col-sm-8">
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('list_customers'); ?>
                </h2>
            </div>
            <div class="box-content">
                <div class="table-responsive">
                    <table id="customer_data" class="table table-bordered table-hover table-striped" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check" />
                                </th>
                                <th><?php echo $this->lang->line("company"); ?></th>
                                <th><?php echo $this->lang->line("name"); ?></th>
                                <th><?php echo $this->lang->line("province"); ?></th>
                                <th><?php echo $this->lang->line("city"); ?></th>
                                <th><?php echo $this->lang->line("state"); ?></th>
                                <th><?php echo $this->lang->line("customer_code"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                            </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                            <tr class="active">
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkft" type="checkbox" name="check"/>
                                </th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('selected_customer'); ?>
                </h2>
            </div>
            <div class="box-content">
                <?php echo form_open("system_settings/save_warehouse_customer/".$warehouse->id); ?>
                    <div style="width: 100%; height:52vh; overflow-y: scroll;">
                        <ul id="list-toko-selected" class="list-group" style="text-align: center; width: 100%; height: 100%; background-color: #dedede;">
                            <?php if(count($warehouse_customer) > 0) : ?>
                                <?php foreach ($list_wh_cust as $i => $lwc) { ?>

                                    <li id="customer_selected_<?= $lwc['all_cust_id'] ?>" class="list-group-item d-flex justify-content-between align-items-center" style="text-align: left;">
                                        <?= $lwc['all_cust_company'] ?>
                                        <br><input type="checkbox" id="warehouse_default_<?= $lwc['all_cust_id'] ?>" name="warehouse_default_<?= $lwc['all_cust_id'] ?>" <?= $lwc['checked'] ?> <?= $lwc['readonly'] ?>>
                                        <?= lang('set_default') ?>
                                        <p style="font-size:12px"><?= lang('default_warehouse') ?> : <?= $lwc['default_warehouse'] ?></p>
                                        <input type="hidden" value="<?= $lwc['all_cust_id'] ?>" name="customer_list[]">
                                        <input type="hidden" value="<?= $lwc['all_cust_company'] ?>" name="customer_name[]">
                                        <input type="hidden" value="<?= $lwc['value1_default'] ?>" name="customer_default[]">
                                    </li>

                                <?php } ?>
                            <?php else: ?>
                                <div style="padding: 70px;"><?= lang('no_data') ?></div>
                            <?php endif ?>
                        </ul>
                    </div>
                    <?= lang('total_toko_selected', 'Total Store Selected'); ?> : <span id="total-seleced"><?= count($list_wh_cust) ?></span>
                    <div class="form-group">
                        <input type="submit" style="width: 100%; margin-top: 28px;" name="submit_warehouse_customer" value="<?= lang('submit') ?>" class="btn btn-primary input-xs">
                    </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    function checkbox_cpg(x) {
        let customer_id = x.split('~')[0];
        var result = false;
        for( var i = 0, len = dataCustomer.length; i < len; i++ ) {  
            if( dataCustomer[i]['customer_id'] === customer_id ) {
                result = true;
                break;
            }
        }
        
        let checked = '';
        if(result) checked ='checked';
        return '<div class="text-center"><input class="checkbox multi-select" type="checkbox" name="val[]" value="' + x + '" '+checked+'/></div>';
    }

    function unselect_toko(id, value){
        var checkbox = $('.checkbox');
        var is_checkbox = false;
        var getIndex = 0;
        for(var i = 0; i < checkbox.length; i++){
            if(checkbox[i].value == value){
                getIndex = i;
                is_checkbox = true;
            }
        }

        if(is_checkbox){
            $(checkbox[getIndex]).iCheck('uncheck');
        } else {
            for(var i = 0; i < dataCustomer.length; i++){
                if (dataCustomer[i]['customer_id'] == id) {
                    dataCustomer.splice(i, 1);
                    i = dataCustomer.length;
                }
            }
        }

        $('#customer_selected_'+id).remove();
        $("#total-seleced").html(dataCustomer.length);
        if(dataCustomer.length <= 0)
            $("#list-toko-selected").html('<div style="padding: 70px;"><?= lang('no_selected_store') ?></div>');
    }

    $(document).on('ifChecked', '.checkbox', function(event) {
        var id_default_warehouse = '<?= lang('unassigned') ?>';
        var default_warehouse = '<?= lang('unassigned') ?>';
        var readonly = 'onclick="return false;"';
        var checked = 'checked';
        <?php foreach ($warehouse_default as $key => $value2) { ?>
                if (<?= $value2->customer_id ?> == this.value.split('~')[0]) {
                    id_default_warehouse = '<?= $value2->warehouse_id ?>';
                    default_warehouse = '<?= $value2->warehouse_name ?>';
                    checked = '';
                    if (<?= $value2->warehouse_id ?> == <?= $warehouse->id ?>) {
                        checked = 'checked';
                    }
                }
        <?php } ?>
        if (default_warehouse != '<?= lang('unassigned') ?>') {
            readonly = '';
        }

        var checkbox = $('.checkbox');
        for(var i = 0; i < checkbox.length; i++){
            if(checkbox[i] == this && this.value != 'on'){
                var company_id      = this.value.split('~')[0];
                var company_name    = this.value.split('~')[1];
                
                if(dataCustomer.length <= 0)
                    $("#list-toko-selected").html("");

                dataCustomer.push({
                    "customer_id":company_id,
                    "customer_name":company_name,
                    });
                $("#total-seleced").html(dataCustomer.length);
                $('#list-toko-selected').prepend(   '<li id="customer_selected_'+company_id+'" class="list-group-item d-flex justify-content-between align-items-center" style="text-align: left;">'+
                                                        company_name+
                                                        '<br>'+
                                                        '<label class="custom_check"><?= lang('set_default') ?>'+
                                                            '<input type="checkbox" id="warehouse_default_'+company_id+'" name="warehouse_default_'+company_id+'" '+checked+' '+readonly+'>'+
                                                            '<span class="checkmark_custom"></span>'+
                                                        '</label>'+
                                                        // '<a href="javascript:void(0)" onclick="unselect_toko(\''+company_id+'\', \''+this.value+'\')" class="badge badge-primary badge-pill">'+
                                                        //     '<i class="fa-fw fa fa-close"></i>'+
                                                        // '</a>'+
                                                        '<p style="font-size:12px;"><?= lang('default_warehouse') ?> : '+default_warehouse+
                                                        '<input type="hidden" value="'+company_id+'" name="customer_list[]">'+
                                                        '<input type="hidden" value="'+company_name+'" name="customer_name[]">'+
                                                        '<input type="hidden" value="'+id_default_warehouse+'" name="customer_default[]">'+
                                                    '</li>');
            }
        }
    });

    $(document).on('ifUnchecked', '.checkbox', function(event) {
        var checkbox = $('.checkbox');
        for(var i = 0; i < checkbox.length; i++){
            if(checkbox[i] == this){
                var company_id      = this.value.split('~')[0];
                var company_name    = this.value.split('~')[1];


                dataCustomer.splice(dataCustomer.indexOf(this.value), 1);
                $("#total-seleced").html(dataCustomer.length);
                $('#customer_selected_'+company_id).remove();
                
                if(dataCustomer.length <= 0)
                    $("#list-toko-selected").html('<div style="padding: 70px;"><?= lang('no_selected_store') ?></div>');

            }
        }
    });
</script>