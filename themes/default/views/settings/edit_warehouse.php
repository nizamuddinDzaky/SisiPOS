<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
 $(document).ready(function(){
    <?php if (!$this->Principal) {?>
    $("#name").keyup(function(){
         var code = $(this).val().substr(0,30); 
        code = code.replace(/\s+/g, '-');
        code = code.replace(/[0-9]+/,'');
        $("#code").val(code);
    });
    <?php }?>
  });
 </script>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_warehouse'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("system_settings/edit_warehouse/" . $id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            
            <?php if ($this->Principal) {?>
            <div class="form-group">
                <label class="control-label" for="code"><?php echo $this->lang->line("code"); ?></label>
                <?php echo form_input('code', $warehouse->code, 'class="form-control" id="code" required="required"'); ?>
            </div>
            <?php }?>
            <div class="form-group">
                <label class="control-label" for="name"><?php echo $this->lang->line("name"); ?></label>
                <?php echo form_input('name', $warehouse->name, 'class="form-control" id="name" required="required"'); ?>
            </div>

            <?php if ($this->Principal) {?>
            <div class="form-group">
                <label class="control-label" for="code"><?php echo $this->lang->line("distributor"); ?></label>
                <?php
                $dist = array();
                foreach ($distributors as $key => $distributor) {
                    $dist[$distributor->id] = $distributor->name;
                }
                echo form_dropdown('distrbutor', $dist, $warehouse->company_id, 'class="form-control select" id="w_distrbutor" style="width:100%;" required="required" readonly="true"');
                ?>
            </div>
            <?php }?>
            
            <div class="form-group">
                <label class="control-label" for="price_group"><?php echo $this->lang->line("price_group"); ?></label>
                <?php
                $pgs[''] = lang('select').' '.lang('price_group');
                if (!$this->Principal) {
                    foreach ($price_groups as $price_group) {
                        $pgs[$price_group->id] = $price_group->name;
                    }
                }
                echo form_dropdown('price_group', $pgs, $warehouse->price_group_id, 'class="form-control tip select" id="price_group" style="width:100%;"');
                ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="shipment_price_group"><?php echo $this->lang->line("shipment_price_group"); ?></label>
                <?php
                $sgp = [];
                $sgp[''] = lang('select').' '.lang('shipment_price_group');
                if (!$this->Principal) {
                    foreach ($shipment_group_prices as $shipment_group_price) {
                        $sgp[$shipment_group_price->id] = $shipment_group_price->name;
                    }
                }
                echo form_dropdown('shipment_price_group', $sgp,$warehouse->shipment_price_group_id, 'class="form-control tip select" id="shipment_price_group" style="width:100%;"');
                ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="phone"><?php echo $this->lang->line("phone"); ?></label>
                <?php echo form_input('phone', $warehouse->phone, 'class="form-control" id="phone"'); ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="email"><?php echo $this->lang->line("email"); ?></label>
                <?php echo form_input('email', $warehouse->email, 'class="form-control" id="email"'); ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="address"><?php echo $this->lang->line("address"); ?></label>
                <?php echo form_textarea('address', $warehouse->address, 'class="form-control skip" id="address" required="required"'); ?>
            </div>
            <div class="form-group">
                <?= lang("warehouse_map", "image") ?>
                <input id="image" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false"
                       class="form-control file">
            </div>
            <div class="form-group">
                <label class="control-label" for="status"><?php echo $this->lang->line("status"); ?></label>
                <?php
                $opt = array(null => lang('active'), 0 => lang('inactive'));
                echo form_dropdown('status', $opt, (isset($_POST['status']) ? $_POST['status'] : $warehouse->active), 'id="status"  class="form-control input-tip select" style="width:100%;"');
                ?>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_warehouse', lang('edit_warehouse'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?=FORCAPOS_VERSION?>"></script>
<?= $modal_js ?>
<script>
$(document).ready(function () {
    $('select.select').select2({
        minimumResultsForSearch: 7
    });

    $("#w_distrbutor").select2("readonly", true);
    $('#w_distrbutor').change(function () {
        $('#modal-loading').show();
        
        $("#price_group").val('').trigger('change');
        $("#shipment_price_group").val('').trigger('change');

        var urlPriceGroup = "<?=base_url()?>/system_settings/getPriceGroup/" + $(this).val();
        var outputPriceGroup = "";
        outputPriceGroup += '<option value="" data-foo="">Choose</option>';

        $.getJSON(urlPriceGroup, function(data) {
            $.each(data, function(key, val) {
                outputPriceGroup += '<option value="' + val.id + '" data-foo="" >' + val.name + '</option>';
            });
            
            $("#price_group").html(outputPriceGroup);
            $("#price_group").val('<?=$warehouse->price_group_id?>').trigger('change');
            $('#modal-loading').hide();
        });

        var urlShipmentGroupPrice = "<?=base_url()?>/system_settings/getShipmentGroupPriceByCompanyId/" + $(this).val();
        var outputShipmentGroupPrice = "";
        outputShipmentGroupPrice += '<option value="" data-foo="">Choose</option>';

        $.getJSON(urlShipmentGroupPrice, function(data) {
            $.each(data, function(key, val) {
                outputShipmentGroupPrice += '<option value="' + val.id + '" data-foo="" >' + val.name + '</option>';
            });
            
            $("#shipment_price_group").html(outputShipmentGroupPrice);
            $("#shipment_price_group").val('<?=$warehouse->shipment_price_group_id?>').trigger('change');
            $('#modal-loading').hide();
        });
    }).change();
});
</script>