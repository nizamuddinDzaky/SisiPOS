<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<script>
    var old_sent;
     $(document).on("focus", '.sent', function () {
        old_sent = $(this).val();
    }).on("change", '.sent', function () {
        var new_sent = $(this).val() ? $(this).val() : 1;
        if (!is_numeric(new_sent)) {
            $(this).val(old_sent);
            return;
        } else if (new_sent > $(this).data('remaining')){
            $(this).val($(this).data('remaining'));
            return;
        } else if(new_sent < 1){
            $(this).val(1);
            return;
        }
    });

    $("#date").datetimepicker({ 
        format:'dd/mm/yyyy hh:ii',
        startDate: "<?= $sale_date ?>"
    });
</script>

<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('edit_delivery'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form','id' =>'form_delivery');
        echo form_open_multipart("sales/edit_delivery/" . $delivery->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
            <div class="col-md-6">
                
            <?php if ($Owner || $Admin) { ?>
                <div class="form-group">
                    <?= lang("date", "date"); ?>
                    <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : $this->sma->hrld($delivery->date)), 'class="form-control datetime" id="date" required="required"'); ?>
                </div>
            <?php } ?>
            <div class="form-group">
                <?= lang("do_reference_no", "do_reference_no"); ?>
                <?= form_input('do_reference_no', (isset($_POST['do_reference_no']) ? $_POST['do_reference_no'] : $delivery->do_reference_no), 'class="form-control tip" id="do_reference_no" required="required" readonly="readonly"'); ?>
            </div>

            <div class="form-group">
                <?= lang("sale_reference_no", "sale_reference_no"); ?>
                <?= form_input('sale_reference_no', (isset($_POST['sale_reference_no']) ? $_POST['sale_reference_no'] : $delivery->sale_reference_no), 'class="form-control tip" id="sale_reference_no" required="required" readonly'); ?>
            </div>
            <input type="hidden" value="<?= $delivery->sale_id; ?>" name="sale_id"/>

            <div class="form-group">
                <?= lang("customer", "customer"); ?>
                <?= form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : $delivery->customer), 'class="form-control" id="customer" required="required" '); ?>
            </div>

            <div class="form-group">
                <?= lang("address", "address"); ?>
                <?= form_textarea('address', (isset($_POST['address']) ? $_POST['address'] : $delivery->address), 'class="form-control" id="address" required="required"'); ?>
            </div>
            </div>
            <div class="col-md-6">

                <div class="form-group">
                    <?= lang('status', 'status'); ?>
                    <?php
                    if($delivery->status == 'delivering'){
                        if($sale_type == 'booking' && $sale->client_id == 'aksestoko') {
                            if($val_status == '1'){
                                $opts = array('delivering' => lang('delivering'));
                            }else{
                                $opts = array('delivering' => lang('delivering'), 'delivered' => lang('delivered'));
                            }
                        }else{
                            $opts = array('delivering' => lang('delivering'), 'delivered' => lang('delivered'));
                        }
                    }
                    elseif ($delivery->status == 'packing') {
                        if($sale_type == 'booking' && $sale->client_id == 'aksestoko') {
                            if($val_status == '1'){
                                $opts = array('packing' => lang('packing'), 'delivering' => lang('delivering'));
                            }else{
                                $opts = array('packing' => lang('packing'), 'delivering' => lang('delivering'), 'delivered' => lang('delivered'));
                            }
                        }else{
                            $opts = array('packing' => lang('packing'), 'delivering' => lang('delivering'), 'delivered' => lang('delivered'));
                        }
                    }
                    
                    
                    if($delivery->status == 'delivered'){
                        echo form_dropdown('status', $opts, (isset($_POST['status']) ? $_POST['status'] : $delivery->status), 'class="form-control" id="status" required="required"  style="width:100%;" disabled');
                    }
                    else{
                        echo form_dropdown('status', $opts, (isset($_POST['status']) ? $_POST['status'] : $delivery->status), 'class="form-control" id="status" required="required" style="width:100%;"');
                    }
                    ?>
                </div>

                <div class="form-group">
                    <?= lang("delivered_by", "delivered_by"); ?>
                    <?= form_input('delivered_by', (isset($_POST['delivered_by']) ? $_POST['delivered_by'] : $delivery->delivered_by), 'class="form-control" id="delivered_by"'); ?>
                </div>

                <div class="form-group">
                    <?= lang("received_by", "received_by"); ?>
                    <?= form_input('received_by', (isset($_POST['received_by']) ? $_POST['received_by'] : $delivery->received_by), 'class="form-control" id="received_by"'); ?>
                </div>

                <div class="form-group">
                    <?= lang("attachment", "attachment") ?>
                    <input id="attachment" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false" data-show-preview="false" class="form-control file">
                </div>

                <div class="form-group">
                    <?= lang("note", "note"); ?>
                    <?= form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : $delivery->note), 'class="form-control" id="note"'); ?>
                </div>
            </div>
            
            <div class="col-md-12">
                <div class="form-group">
                    <label for="">Product</label>
                    <table class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Product Code</th>
                            <th>Product Name</th>
                            <th>Remaining Stock</th>
                            <th>Quantity</th>
                            <th>Unsend Quantity</th>
                            <th>Quantity to Send</th>
                        </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($delivery_items as $i => $delivery_item) { 
                                $get_wh = $this->sales_model->getWarehouseProduct($delivery_item->warehouse_id, $delivery_item->product_id);
                                if($sale->sale_type == 'booking'){
                                    $getdataqty = 'onblur="get_data('.$delivery_item->product_id.','.$delivery_item->warehouse_id.','.$delivery_item->quantity_sent.')"';
                                }else{
                                    $getdataqty = '';
                                }
                                $data_remaining = (int) $delivery_item->quantity_ordered - $delivery_item->all_sent_qty + $delivery_item->quantity_sent;
                                ?>
                            <tr>
                                <td class="text-center"><?=$i+1?></td>
                                <td><?=$delivery_item->product_code?></td>
                                <td><?=$delivery_item->product_name?></td>
                                <td class="text-center"><?=(int) $get_wh->quantity?></td>
                                <td class="text-center"><?=(int) $delivery_item->quantity_ordered?></td>
                                <td class="text-center">(<?= (int) $delivery_item->quantity_ordered - $delivery_item->all_sent_qty?>) <?=$data_remaining?></td>
                                
                                <?php if($delivery->receive_status == "received") { ?>
                                    <td class="text-center"><?=(int)$delivery_item->quantity_sent?></td>
                                <?php } else { ?>
                                    <td style="width: 15%">
                                        <input type="hidden" name="delivery_items_id[]" value="<?=$delivery_item->id?>">
                                        <input type="text" class="form-control text-center sent" data-remaining="<?=$data_remaining?>" name="sent_quantity[]" style="padding: 5px;" value="<?=(int)$delivery_item->quantity_sent?>" <?=$getdataqty;?> data-product-id="<?=$delivery_item->product_id?>" data-qtysent="<?=(int)$delivery_item->quantity_sent?>">
                                    </td>
                                <?php }?>
                            </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot></tfoot>
                    </table>
                </div>
            </div>

            </div>

        </div>
        <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="update_delivery" name="update_delivery"><?= lang('edit_delivery') ?></button>
            <!-- <?= form_submit('edit_delivery', lang('edit_delivery'), 'class="btn btn-primary" id="update_delivery"' ); ?> -->
        </div>
    </div>
    <?= form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?=FORCAPOS_VERSION?>"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['sma'] = <?=$dp_lang?>;
</script>
<?= $modal_js ?>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function () {
        $.fn.datetimepicker.dates['sma'] = <?=$dp_lang?>;

        <?php if($sale_type == 'booking' && $sale->client_id == 'aksestoko') { ?>
        $('#date').on('change', function(){
            var date = $('#date').val();
            var split_date = date.split(" ");
            var myDate = split_date[0].split("/");
            var newDate = myDate[1]+","+myDate[0]+","+myDate[2];
            var newDate = new Date(newDate);
            var getDate = newDate.setDate(newDate.getDate() + 2);
            var datetime_input = new Date(getDate).getTime();
            var datetime_today = new Date().getTime();
            var delivery_status = '<?= $delivery->status ?>';

            if(datetime_input > datetime_today){
                if(delivery_status == 'delivering'){
                    var options = new Array("delivering");
                }else{
                    var options = new Array("packing", "delivering");
                }
            }
            else{
                if(delivery_status == 'delivering'){
                    var options = new Array("delivering", "delivered");
                }else{
                    var options = new Array("packing", "delivering", "delivered");
                }
            }

            $('#status').empty();
            $.each(options, function(i, p) {
                $('#status').append($('<option></option>').val(p).html(lang[p]));
            });
        });
        <?php } ?>

    });
    var arr = [];
    function get_data(data_product_id,data_werehouse_id,qty_sent){
        $('#update_delivery').attr('disabled', true);
        var status = "<?= $delivery->status ?>";
        $.ajax({
            type:'get', async:false,
            url: "<?= site_url('sales/getQtyProduct/'); ?>"+data_product_id+"/"+data_werehouse_id,
            dataType: "json",
            success: function(data){
                if(data.qty != null){
                    if(status != 'packing'){
                        var current = parseInt(data.qty)+ parseInt(qty_sent);
                    }else{
                        var current = parseInt(data.qty);
                    }
                    var qty_now = $("input[data-product-id ="+data_product_id+"]").val() ;

                    if(qty_now > current){
                        $("input[data-product-id ="+data_product_id+"]").parents("tr").addClass("danger");
                        arr.push(data_product_id);
                    }
                    else{
                        $("input[data-product-id ="+data_product_id+"]").parents("tr").removeClass("danger");
                        var index = arr.indexOf(data_product_id);
                        if (index >= 0) {
                            arr.splice( index, 1 );
                        }
                    }
                }
            }
        });
        if(arr.length > 0){
            $('#update_delivery').attr('disabled', true);
        }else{
            $('#update_delivery').removeAttr('disabled');
        }
    }
    $("#update_delivery").click(function(){
        $(this).attr("disabled", "disabled");
        $(this).html("Memuat...");
        document.getElementById('form_delivery').submit();
        
    });
</script>
