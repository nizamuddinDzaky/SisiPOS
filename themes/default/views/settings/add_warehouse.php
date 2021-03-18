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
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_warehouse'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("system_settings/add_warehouse", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="form-group" style="display:<?= $this->Principal ? '': 'none'?>;">
                <label class="control-label" for="code"><?php echo $this->lang->line("code"); ?></label>
                <?php echo form_input('code', '', 'class="form-control" id="code" required="required"'); ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="name"><?php echo $this->lang->line("name"); ?></label>
                <?php echo form_input('name', '', 'class="form-control" id="name" required="required"'); ?>
            </div>

            <?php if ($this->Principal) {?>
            <div class="form-group">
                <?= lang('Distributor', 'Distributor'); ?> *
                <div class="controls">
                    <?php echo form_input('distributor', (isset($_POST['distributor']) ? $_POST['distributor'] : 1), 'id="select_distributor_add" data-placeholder="' . lang("select") . ' ' . lang("Distributor") . '" required="required" class="form-control input-tip" style="width:100%; margin-bottom:20px;"'); ?>
                </div>
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
                echo form_dropdown('price_group', $pgs,'', 'class="form-control tip select" id="price_group" style="width:100%;"');
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
                echo form_dropdown('shipment_price_group', $sgp,'', 'class="form-control tip select" id="shipment_price_group" style="width:100%;"');
                ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="phone"><?php echo $this->lang->line("phone"); ?></label>
                <?php echo form_input('phone', '', 'class="form-control" id="phone"'); ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="email"><?php echo $this->lang->line("email"); ?></label>
                <?php echo form_input('email', '', 'class="form-control" id="email"'); ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="address"><?php echo $this->lang->line("address"); ?></label>
                <?php // echo form_textarea('address', '', 'class="form-control skip" id="address" required="required"'); 
                echo form_input('address', '', 'class="form-control" id="address" required');
                ?>
            </div>
            <div class="form-group">
                <?= lang("warehouse_map", "image") ?>
                <input id="image" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false"
                       class="form-control file">
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_warehouse', lang('add_warehouse'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?=FORCAPOS_VERSION?>"></script>
<?= $modal_js ?>
<script>

$(document).ready(function () {
    suggestionsBillerAktif();

    $('select.select').select2({
        minimumResultsForSearch: 7
    });

    $('#select_distributor_add').change(function () {
        $('#modal-loading').show();

        var urlPriceGroup = "<?=base_url()?>/system_settings/getPriceGroup/" + $(this).val();
        var outputPriceGroup = "";
        outputPriceGroup += '<option value="" data-foo="">Choose</option>';

        $.getJSON(urlPriceGroup, function(data) {
            $.each(data, function(key, val) {
                outputPriceGroup += '<option value="' + val.id + '" data-foo="" >' + val.name + '</option>';
            });
            
            $("#price_group").html(outputPriceGroup);
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
            $('#modal-loading').hide();
        });
    })
});

function suggestionsBillerAktif() {
    var url = "<?php echo site_url() . 'customers/suggestionsBillerAktif' ?>";
    $('#select_distributor_add').select2({
        minimumInputLength: 1,
        ajax: {
            url: url,
            dataType: 'json',
            quietMillis: 15,
            data: function(term, page) {
                return {
                    term: term,
                    limit: 20
                };
            },
            results: function(data, page) {
                if (data.results != null) {
                    return {
                        results: data.results
                    };
                } else {
                    return {
                        results: [{
                            id: '',
                            text: 'No Match Found'
                        }]
                    };
                }
            }
        },
        formatResult: formatAddress
    });
}

function formatAddress(items) {
    if (!items.id) {
        return items.text;
    }
    return items.text + "<br><span style='font-size:12px;color:#1E1E1E'>" + items.code + "</span>";
}
</script>