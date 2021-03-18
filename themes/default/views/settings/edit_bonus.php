<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_bonus'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("system_settings/edit_bonus/".$id, $attrib); ?>
        <div class="modal-body ui-front">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="warehouse"><?php echo $this->lang->line("warehouse"); ?></label>
                        <?php $wh=array();
                        foreach ($warehouses as $warehouse) {
                            $wh[$warehouse->id] = $warehouse->name;
                        }
                        echo form_dropdown('warehouse', $wh, $bonus->warehouse_id, 'id="warehouse" class="form-control select" required="required" style="width:100%;" ');?>
                    </div>
                    <div class="form-group">
                        <label for="product"><?php echo $this->lang->line("product"); ?></label>
                        <?php echo form_input('product', '', 'class="form-control input-tip" id="product" required="required"'); ?>
                        <!--<input type="hidden" name="product_id" id="product_id">-->
                    </div>
                    <div class="form-group">
                        <label for="product_bonus"><?php echo $this->lang->line("bonus"); ?></label>
                        <?php echo form_input('bonus', '', 'class="form-control input-tip" id="product_bonus" required="required"');?>
                        <!--<input type="hidden" name="product_bonus" id="product_bonus">-->
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="quantity"><?php echo $this->lang->line("quantity"); ?></label>
                        <?php echo form_input('quantity', $this->sma->formatQuantity($bonus->quantity), 'class="form-control" id="quantity" required="required" pattern="\d+"'); ?>
                    </div>
                    <div class="form-group">
                        <label for="start_date"><?php echo $this->lang->line("start_date"); ?></label>
                        <?php echo form_input('start_date', $this->sma->hrsd($bonus->start_date), 'class="form-control input-tip date" id="start_date" required="required"'); ?>
                    </div>
                    <div class="form-group">
                        <label for="end_date"><?php echo $this->lang->line("end_date"); ?></label>
                        <?php echo form_input('end_date', (($bonus->end_date!='0000-00-00 00:00:00' && isset($bonus->end_date))?$this->sma->hrsd($bonus->end_date):''), 'class="form-control input-tip date" id="end_date"'); ?>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="checkbox" for="multiply">
                <input type="checkbox" class="checkbox" <?php echo ($bonus->multiply?'checked="checked"':'');?>name="multiply" id="multiply"/><?= lang('applies_multiply') ?></label>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_bonus', lang('edit_bonus'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script>
var $product=$("#product");
var $bonus=$("#product_bonus");
selectDropdown($product,<?=$bonus->product_id?>);
selectDropdown($bonus,<?=$bonus->product_bonus?>);
function selectDropdown($tags,value){
    $tags.val(value).select2({
        minimumInputLength: 1,
        data: [],
        initSelection: function (element, callback) {
            $.ajax({
                type: "get", async: false,
                url: site.base_url+"products/getProduct/" + $(element).val(),
                dataType: "json",
                success: function (data) {
                    callback(data[0]);
                }
            });
        },
        ajax: {
            type: 'get',
            url: site.base_url + "sales/suggestions",
            dataType: 'json',
            quietMillis: 15,
            data: function (term, page) {
                return {
                    term: term,
//                    limit: 10
                    warehouse_id: $("#warehouse").val()
                };
            },
            results: function (data, page) {
                new_data=[];
                $.each(data,function(){
                    item={id:this.item_id,text:this.label};
                    new_data.push(item);
                });
                if (new_data != null) {
                    return {results: new_data};
                } else {
                    return {results: [{id: '', text: 'No Match Found'}]};
                }
            }
        }
    });
}
</script>
<?= $modal_js ?>