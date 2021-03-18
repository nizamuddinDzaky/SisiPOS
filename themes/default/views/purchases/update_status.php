<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    var old_received;
     $(document).on("focus", '.received', function () {
        old_received = $(this).val();
    }).on("change", '.received', function () {
        var new_received = $(this).val() ? $(this).val() : 0;
        if (!is_numeric(new_received)) {
            $(this).val(old_received);
            return;
        } else if (new_received > $(this).data('remaining')){
            $(this).val($(this).data('remaining'));
            return;
        } else if(new_received < -$(this).data('rqty')){
            $(this).val(-$(this).data('rqty'));
            return;
        }
    });
    $("#status" )
        .change(function () {
            if($(this).val() == "received") {
                $("#received-detail").slideDown()
            }else{
                $("#received-detail").slideUp()
            }
        })
        .change();
</script>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('update_status'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("purchases/update_status/" . $inv->id, $attrib);
        ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <?= lang('purchase_details'); ?>
                </div>
                <div class="panel-body">
                    <table class="table table-condensed table-striped table-borderless" style="margin-bottom:0;">
                        <tbody>
                            <tr>
                                <td><?= lang('reference_no'); ?></td>
                                <td><?= $inv->reference_no; ?></td>
                            </tr>
                            <tr>
                                <td><?= lang('supplier'); ?></td>
                                <td><?= $inv->supplier; ?></td>
                            </tr>
                            <tr>
                                <td><?= lang('warehouse'); ?></td>
                                <td><?= $inv->warehouse_id; ?></td>
                            </tr>
                            <tr>
                                <td><?= lang('status'); ?></td>
                                <td><strong><?= lang($inv->status); ?></strong></td>
                            </tr>
                            <tr>
                                <td><?= lang('payment_status'); ?></td>
                                <td><?= lang($inv->payment_status); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if ($returned) { ?>
                <h4><?= lang('purchase_x_action'); ?></h4>
                <?php } else if($inv->status == 'received') { ?>
                <h4><?= lang('purchase_has_been_received'); ?></h4>
                <?php } else { ?>
                <div class="form-group">
                    <?= lang('status', 'status'); ?>
                    <?php
                    $opts = array('received' => lang('received'), 'pending' => lang('pending'));
                    
                    if($inv->status == 'received' || $inv->status == 'partial'){
                        $opts = array('received' => lang('received'));
                    }
                    // $opts = array('received' => lang('received'), 'pending' => lang('pending'), 'ordered' => lang('ordered'));
                    ?>
                    <?= form_dropdown('status', $opts, $inv->status, 'class="form-control" id="status" required="required" style="width:100%;"'); ?>
                </div>

                <div id="received-detail" style="display: none">
                    <div class="form-group">
                        <!--<?php var_dump($items) ?>-->
                        <table class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Order Quantity</th>
                                    <th>Unreceived</th>
                                    <th>Receive Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item) {
                                    $remaining = (int) ($item->quantity - $item->quantity_received);
                                ?>
                                <tr>
                                    <td><?=$item->product_code." - ".$item->product_name?></td>
                                    <td class="text-center"><?=(int) $item->quantity ?></td>
                                    <td class="text-center"><?=$remaining?></td>
                                    <td>
                                        <input type="hidden" name="id[]" value="<?=$item->id?>">
                                        <input type="text" class="form-control text-center received" data-rqty="<?=(int)$item->quantity_received?>" data-remaining="<?=$remaining?>" name="received_amount[]" style="padding: 5px;" value="0">
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="form-group">
                        <b>DO Reference</b>
                        <input type="text" class="form-control" name="do_reference">
                    </div>
                </div>

                <div class="form-group">
                    <?= lang("note", "note"); ?>
                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : $this->sma->decode_html($inv->note)), 'class="form-control" id="note"'); ?>
                </div>
                <?php } ?>
        </div>
            <?php if (!($returned || $inv->status == 'received')) { ?>
            <div class="modal-footer">
                <?php echo form_submit('update', lang('update'), 'class="btn btn-primary"'); ?>
            </div>
            <?php } ?>
        </div>
<?php echo form_close(); ?>
</div>
<?= $modal_js ?>
