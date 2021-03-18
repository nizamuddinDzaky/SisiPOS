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
        } else if(new_sent < 0){
            $(this).val(1);
            return;
        }
    });

    $("#date").datetimepicker({
        format:'dd/mm/yyyy HH:mm',
        startDate: "<?= $sale_date ?>"
    });
</script>

<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('return_delivery'); ?></h4> 
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form','id' =>'form_delivery');
        echo form_open_multipart("sales/return_delivery/" . $delivery->id, $attrib); ?>
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
                <?= form_input('sale_reference_no', (isset($_POST['sale_reference_no']) ? $_POST['sale_reference_no'] : $delivery->sale_reference_no), 'class="form-control tip" id="sale_reference_no" required="required"'); ?>
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
                    <?= form_input('return', 'Return', 'class="form-control" id="status"  readonly="readonly"'); ?>
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
                            <th>Delivered Quantity</th>
                            <th>Return Quantity</th>
                        </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($delivery_items as $i => $delivery_item) {
                                if($delivery_item->bad_quantity > 0){ $qty_return = (int)$delivery_item->bad_quantity; }
                                else if($delivery_item->quantity_sent > 0){ 
                                    $qty_return = (int)$delivery_item->quantity_sent;
                                }else{ $qty_return = 0; }
                            ?>
                            <tr>
                                <td class="text-center"><?=$i+1?></td>
                                <td><?=$delivery_item->product_code?></td>
                                <td><?=$delivery_item->product_name?></td>
                                <td class="text-center">
                                    <?=(int)$delivery_item->quantity_sent?>
                                    <input type="hidden" name="sent_quantity[]" value="<?=(int)$delivery_item->quantity_sent?>">
                                </td>
                                <td style="width: 15%">
                                    <input type="hidden" name="delivery_items_id[]" value="<?=$delivery_item->id?>">
                                    <input type="text" class="form-control text-center sent" data-remaining="<?=$qty_return?>" name="return_quantity[]" style="padding: 5px;" value="<?= $qty_return; ?>" <?= $delivery_item->bad_quantity > 0 ? "readonly" : "" ?>>
                                </td>
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
        <button type="button" class="btn btn-primary" id="return_delivery" name="return_delivery"><?= lang('return_delivery') ?></button>
            <!-- <?= form_submit('return_delivery', lang('return_delivery'), 'class="btn btn-primary"'); ?> -->
        </div>
        <?= form_close(); ?>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['sma'] = <?=$dp_lang?>;
</script>
<?= $modal_js ?>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function () {
        $.fn.datetimepicker.dates['sma'] = <?=$dp_lang?>;
    });
    $("#return_delivery").click(function(){
        $(this).attr("disabled", "disabled");
        $(this).html("Memuat...");
        document.getElementById('form_delivery').submit();
        
    });
</script>
