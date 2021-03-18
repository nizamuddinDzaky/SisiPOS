<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('synchronize_product'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("products/sync_product/".$id, $attrib); ?>
        <div class="modal-body ui-front">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="your_product"><?php echo $this->lang->line("your_product"); ?></label>
                        <?php echo form_input('your_product', $product->name, 'class="form-control tip" id="your_product" readonly'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name"><?php echo $this->lang->line("name_product"); ?></label>
                        <?php echo form_input('name', '', 'class="form-control tip" id="name" required="required"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="supplier"><?php echo $this->lang->line("supplier"); ?></label>
                        <?php echo form_dropdown('supplier', $suppliers, (isset($_POST['supplier']) ? $_POST['supplier'] : NULL), 'id="supplier" class="form-control select" required="required" style="width:100%;" readonly');?>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('synchronize_product', lang('synchronize_product'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script>
    <?php if($product->uuid){ ?>
        var supplier = $("#supplier").val();
    $("#name").val(<?= $product->uuid ?>).select2({
        minimumInputLength: 1,
        data: [],
        initSelection: function (element, callback) {
            $.ajax({
                type: "get", async: false,
                url: site.base_url+"official/get_product/"+supplier+"/"+<?= $product->uuid ?>,
                dataType: "json",
                success: function (data) {
                    new_data=[];
                    $.each(data,function(){
                        item={id:this.m_product_id,text:this.name};
                        new_data.push(item);
                    });
                    callback(new_data[0]);
                }
            });
        },
        ajax: {
            type: 'get',
            url: site.base_url + "official/get_product",
            dataType: 'json',
            quietMillis: 15,
            data: function (term, page) {
                return {
                    term: term,
                    supplier: $("#supplier").val()
                };
            },
            results: function (data, page) {
                new_data=[];
                $.each(data,function(){
                    item={id:this.m_product_id,text:this.name};
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
    <?php } else{ ?>
    $("#name").select2({
        minimumInputLength: 1,
        ajax: {
            type: 'get',
            url: site.base_url + "official/get_product",
            dataType: 'json',
            quietMillis: 15,
            data: function (term, page) {
                return {
                    term: term,
                    supplier: $("#supplier").val()
                };
            },
            results: function (data, page) {
                new_data=[];
                $.each(data,function(){
                    item={id:this.m_product_id,text:this.name};
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
    <?php } ?>
</script>
<?= $modal_js ?>