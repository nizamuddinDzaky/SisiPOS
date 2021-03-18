<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_gross_price'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("system_settings/edit_gross_price/" . $id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="warehouse"><?php echo $this->lang->line("warehouse"); ?></label>
                        <?php $wh=array();
                                foreach ($warehouses as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->name;
                                }
                                echo form_dropdown('warehouse', $wh, $gross_price->warehouse_id, 'id="warehouse" class="form-control select" required="required" style="width:100%;" ');?>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="product"><?php echo $this->lang->line("product"); ?></label>
                        <?php // echo form_input('product', $gross_price->product_id, 'class="form-control" id="product" required="required"'); 
                        echo form_input('product', '', 'class="form-control input-tip" id="product" required="required"');?>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="operator"><?php echo $this->lang->line("operator"); ?></label>
                        <?php
                        $ops = array('>' => '>', '<' => '<', '<=' => '<=', '>=' => '>=', '=' => '=');
                        echo form_dropdown('operator', $ops, $gross_price->operation, 'class="form-control select" id="operator" required="required"'); ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="quantity"><?php echo $this->lang->line("quantity"); ?></label>
                        <?php echo form_input('quantity', $this->sma->formatDecimal($gross_price->quantity), 'class="form-control" id="quantity" required="required"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="price"><?php echo $this->lang->line("price"); ?></label>
                        <?php echo form_input('price', $this->sma->formatDecimal($gross_price->price), 'class="form-control" id="price" required="required"'); ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="start_date"><?php echo $this->lang->line("start_date"); ?></label>
                        <?php echo form_input('start_date', $this->sma->hrsd($gross_price->start_date), 'class="form-control input-tip date" id="start_date" required="required"'); ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="end_date"><?php echo $this->lang->line("end_date"); ?></label>
                        <?php echo form_input('end_date', (($gross_price->end_date=='0000-00-00 00:00:00' && !isset($gross_price->end_date))?'':$this->sma->hrsd($gross_price->end_date)), 'class="form-control input-tip date" id="end_date"'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_gross_price', lang('edit_gross_price'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script>
    $("#product").val(<?php echo $gross_price->product_id;?>).select2({
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
//    if(){
//        
//    }
</script>
<?= $modal_js ?>