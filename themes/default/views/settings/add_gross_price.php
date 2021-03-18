<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_gross_price'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("system_settings/add_gross_price", $attrib); ?>
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
                        echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $this->session->userdata('warehouse_id')), 'id="warehouse" class="form-control select" required="required" style="width:100%;" readonly');?>
                    </div>
                    <div class="form-group">
                        <label for="product"><?php echo $this->lang->line("product"); ?></label>
                        <?php
                        echo form_input('product', '', 'class="form-control input-tip" id="product" required="required"');?>
                    </div>
                    <div class="form-group">
                        <label for="operator"><?php echo $this->lang->line("operator"); ?></label>
                        <?php 
                        $ops = array('>' => '>', '<' => '<', '<=' => '<=', '>=' => '>=', '=' => '=');
                        echo form_dropdown('operator', $ops, null, 'id="operator" class="form-control select" required="required" style="width:100%;" ');?>
                    </div>
                    <div class="form-group">
                        <label for="quantity"><?php echo $this->lang->line("quantity"); ?></label>
                        <?php echo form_input('quantity', '', 'class="form-control tip number-only" id="quantity" required="required"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="price"><?php echo $this->lang->line("price"); ?></label>
                        <?php echo form_input('price', '', 'class="form-control number-only" id="price" required="required"'); ?>
                    </div>
                    <div class="form-group">
                        <label for="start_date"><?php echo $this->lang->line("start_date"); ?></label>
                        <?php echo form_input('start_date', '', 'class="form-control input-tip date" id="start_date" required="required"'); ?>
                    </div>
                    <div class="form-group">
                        <label for="end_date"><?php echo $this->lang->line("end_date"); ?></label>
                        <?php echo form_input('end_date', '', 'class="form-control input-tip date" id="end_date"'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_gross_price', lang('add_gross_price'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script>
    $("#product").select2({
        minimumInputLength: 1,
        ajax: {
            type: 'get',
//            url: site.base_url + "sales/suggestions",
            url: site.base_url + "products/qa_suggestions",
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

//    $("#product").autocomplete({
//        source: function (request, response) {
//            $.ajax({
//                type: 'get',
//                url: '<?= site_url('sales/suggestions'); ?>',
//                dataType: "json",
//                data: {
//                    term: request.term,
//                    warehouse_id: $("#warehouse").val()
//                },
//                success: function (data) {
//                    response(data);
//                }
//            });
//        },
//        minLength: 1,
//        autoFocus: false,
//        delay: 250,
//        select: function (event, ui) {
//            event.preventDefault();
//            if (ui.item.id !== 0) {
//                $("#product_id").val(ui.item.item_id);
//            } else {
//            }
//        }
//    });
        $('input.number-only').bind('keypress', function (e) {
                    return !(e.which != 8 && e.which != 0 &&
                    (e.which < 48 || e.which > 57) && e.which != 46);
		});        
</script>
<?= $modal_js ?>