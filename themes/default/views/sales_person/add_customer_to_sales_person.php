<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
// print_r($this->input->post());die;
$v = "";
/* if($this->input->post('name')){
  $v .= "&product=".$this->input->post('product');
  } */
if ($this->input->post('provinsi')) {
    $v .= "&provinsi=" . $this->input->post('provinsi');
}
if ($this->input->post('kabupaten')) {
    $v .= "&kabupaten=" . $this->input->post('kabupaten');
}
?>
<script>
    $(document).ready(function () {
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#StCaRData').dataTable({
            "aaSorting": [[0, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": 25,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('sales_person/getCustomers_sp?id_sp='.$id.$v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"bSortable": false,"mRender": checkbox_cpg}, null, null, null, null]
        });

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
        <h2 class="blue"><i class="fa-fw fa fa-plus-square-o"></i><?= lang('add_customer_to_sales_person'); ?> (<?= strtoupper($sales_person->name)?>)</h2>
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
    <div id="form">
        <div class="box-content">
            <div class="row">
                <div class="col-lg-12">
                    <?php echo form_open("sales_person/add_customer_to_sales_person/".$id); ?>
                    <!-- <div id="form"> -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang("Province", "provinsi"); ?>
                                    <div class="controls">
                                        <select name="provinsi" id="pg-provinsi" onchange="setProvinsi_pg(this.value,this.options[this.selectedIndex].innerHTML)" class="form-control select" required>
                                            <?php if ($this->input->post('provinsi') != '') {?>
                                            <option value="<?=$this->input->post('provinsi')?>" selected><?=$this->input->post('provinsi')?></option>
                                            <?php } else {?>
                                            <option value="">Choose Province</option>
                                            <?php }?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang("city", "kabupaten"); ?>
                                    <div class="controls">
                                        <select name="kabupaten" id="kabupaten" class="form-control select">
                                            <?php if ($this->input->post('kabupaten') != '') {?>
                                            <option value="<?=$this->input->post('kabupaten')?>" selected><?=$this->input->post('kabupaten')?></option>
                                            <?php } else {?>
                                            <option value="">Choose City</option>
                                            <?php }?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <?php echo form_submit('filter_type', $this->lang->line("submit"), 'class="btn btn-primary pull-right"');?>
                        </div>
                    <!-- </div> -->
                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-8">
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('list_customers'); ?>
                </h2>
            </div>
            <div class="box-content"  style=" height:70vh; overflow-y: scroll;">
                <div class="table-responsive">
                    <table id="StCaRData" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check" />
                                </th>
                                <th><?php echo $this->lang->line("company"); ?></th>
                                <th><?php echo $this->lang->line("name"); ?></th>
                                <th><?php echo $this->lang->line("phone"); ?></th>
                                <th><?php echo $this->lang->line("customers_code"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                            </tr>
                        </tbody>
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
                <?php echo form_open("sales_person/save_customer_to_sales_person/".$id); ?>
                    <input type="hidden" value="<?=$sales_person->reference_no?>" name="sp_ref">
                    <div style="width: 100%; height:52vh; overflow-y: scroll;">
                        <ul id="list-toko-selected" class="list-group" style="text-align: center; width: 100%; height: 100%; background-color: #dedede;">
                            <?php
                                if ($customer_of_sales_person) {
                                    foreach ($customer_of_sales_person as $key => $csp) {
                                        echo '<li id="toko_selected_'.$csp->id.'" class="list-group-item d-flex justify-content-between align-items-center" style="text-align: left;">'.$csp->company;
                                        echo '<input type="hidden" value="'.$csp->id.'" name="list_toko[]">';
                                        echo '<a href="javascript:void(0)" onclick="unselect_toko(\'toko_selected_'.$csp->id.'\', \''.$csp->custom_id.'\')" class="badge badge-primary badge-pill">';
                                        echo '<i class="fa-fw fa fa-close"></i>';
                                        echo '</a>';
                                        echo '</li>';
                                    }
                                }
                            ?>
                        </ul>
                    </div>
                    <div class="form-group">
                        <input type="submit" style="width: 100%; margin-top: 28px;" name="submit_report" value="Submit" class="btn btn-primary input-xs">
                    </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
var data_csp = JSON.parse(`<?= json_encode($customer_of_sales_person)?>`);
    $(document).ready(function () {
        if (data_csp == false) {
            data_csp = []
        }
        console.log(data_csp);

        $.getJSON('<?php echo base_url(); ?>daerah/getProvinsi', function(data) {
            var output = "";
            <?php if ($this->input->post('provinsi')=='') {?>
             output += '<option value="" data-foo="">Choose Province</option>';
            <?php }?>
            $.each(data, function(key, val) {
                selected = '';
                if (val.province_name == '<?=$this->input->post('provinsi')?>') {
                    selected = 'selected';
                }
                output += '<option value="' + val.province_name + '" data-foo="" '+selected+'>' + val.province_name + '</option>';
            });
            $("#pg-provinsi").html(output);

        });
        <?php if ($this->input->post('provinsi')!='') {?>
        $('#pg-provinsi').change();
        <?php }?>
    });

    function setProvinsi_pg(id, text) {
        $('#modal-loading').show();
        var urlProvinsi = "<?=base_url();?>/daerah/getKabupaten/" + text.replace(/\s+/g, '_') + "/";
        var output = "";
        output += '<option value="" data-foo="">Choose</option>';

        $.getJSON(urlProvinsi, function(data) {
            $.each(data, function(key, val) {
                selected = '';
                if (val.kabupaten_name == '<?=$this->input->post('kabupaten')?>') {
                    selected = 'selected';
                }
                output += '<option value="' + val.kabupaten_name + '" data-foo="" '+selected+'>' + val.kabupaten_name + '</option>';
            });
            
            $("#kabupaten").html(output);
            $('#modal-loading').hide();
        });
    }

    function checkbox_cpg(x) {
        let company_id      = x.split('~')[0];
        let company_name    = x.split('~')[1];
        
        var result = false;
        for( var i = 0, len = data_csp.length; i < len; i++ ) {            
            if( data_csp[i]['id'] === company_id ) {
                result = true;
                break;
            }
        }
        
        let checked = '';
        if(result)
            checked='checked';

        return '<div class="text-center"><input class="checkbox multi-select" type="checkbox" name="val[]" value="' + x + '" '+checked+'/></div>';
    }

    function unselect_toko(id, value){
        var checkbox = $('.checkbox');
        $('#'+id).remove();
        for(var i = 0; i < checkbox.length; i++){
            if(checkbox[i].value == value){
                $(checkbox[i]).iCheck('uncheck');
            }
        }
    }

    $(document).on('ifChecked', '.checkbox', function(event) {
        var checkbox = $('.checkbox');
        for(var i = 0; i < checkbox.length; i++){
            if(checkbox[i] == this && this.value != 'on'){
                var company_id      = this.value.split('~')[0];
                var company_name    = this.value.split('~')[1];                

                let data = {
                    'id': company_id,
                    'custom_id' : this.value,
                    'company' : company_name
                };
                data_csp.push(data)
                console.log(data_csp);
                $('#list-toko-selected').prepend('<li id="toko_selected_'+company_id+'" class="list-group-item d-flex justify-content-between align-items-center" style="text-align: left;">'+
                                        company_name+
                                        '<input type="hidden" value="'+company_id+'" name="list_toko[]">'+
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
                var company_id      = this.value.split('~')[0];
                var company_name    = this.value.split('~')[1];

                $('#toko_selected_'+company_id).remove();
            }
        }
    });
</script>