<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('update_status'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'formfield');
        echo form_open_multipart("sales/update_status/" . $inv->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <?= lang('sale_details'); ?>
                </div>
                <div class="panel-body">
                    <table class="table table-condensed table-striped table-borderless" style="margin-bottom:0;">
                        <tbody>
                            <tr>
                                <td><?= lang('reference_no'); ?></td>
                                <td><?= $inv->reference_no; ?></td>
                            </tr>
                            <tr style="display:none">
                                <td id="real_stock"><?= $real_stock->quantity; ?></td>
                                <td id="quantity"><?= $sales_item->quantity; ?></td>
                            </tr>
                            <tr>
                                <td><?= lang('biller'); ?></td>
                                <td><?= $inv->biller; ?></td>
                            </tr>
                            <tr>
                                <td><?= lang('customer'); ?></td>
                                <td><?= $inv->customer; ?></td>
                            </tr>
                            <tr>
                                <td><?= lang('status'); ?></td>
                                <td><strong><?= lang($inv->sale_status); ?></strong></td>
                            </tr>
                            <tr>
                                <td><?= lang('payment_status'); ?></td>
                                <td><?= lang($inv->payment_status); ?></td>
                            </tr>
                            <?php if (!$po && $inv->payment_status == 'pending') { ?>
                                <tr>
                                    <td><?= lang('remaining_credit_limit'); ?></td>
                                    <td>
                                        <?= number_format($kredit_limit, 0, ',', '.'); ?>
                                        <br>
                                        -<?= number_format($inv->total, 0, ',', '.'); ?>
                                        <hr style="border-top: 1px solid #000; margin-top: 5px; margin-bottom: 5px;">
                                        <b id="remain-credit"><?= number_format($kredit_limit - $inv->total, 0, ',', '.'); ?></b>

                                    </td>
                                </tr>
                            <?php } else { ?>
                                <?php if ($po->payment_method == 'kredit') { ?>
                                    <tr>
                                        <td><?= lang('remaining_credit_limit'); ?></td>
                                        <td>
                                            <?= number_format($kredit_limit, 0, ',', '.'); ?>
                                            <br>
                                            <?php if (strtolower($po->status) != 'canceled') { ?>
                                                - <?= number_format($po->grand_total, 0, ',', '.'); ?>
                                                <hr style="border-top: 1px solid #000; margin-top: 5px; margin-bottom: 5px;">
                                                <b id="remain-credit"><?= number_format($kredit_limit - $po->grand_total, 0, ',', '.'); ?></b>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($po) { ?>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        AksesToko Order Detail
                    </div>
                    <div class="panel-body">
                        <table class="table table-condensed table-striped table-borderless" style="margin-bottom:0;">
                            <tbody>
                                <tr>
                                    <td><?= lang('purchase_order_ref'); ?></td>
                                    <td><?= $po->reference_no; ?></td>
                                </tr>
                                <tr>
                                    <td><?= lang('expected_delivery_date'); ?></td>
                                    <td><?= convert_date($po->shipping_date); ?></td>
                                </tr>
                                <tr>
                                    <td><?= lang('payment_method'); ?></td>
                                    <td><?= ucwords($po->payment_method); ?></td>
                                </tr>
                                <?php
                                if ($po->payment_method == 'kredit_pro') { ?>
                                    <tr>
                                        <td>Status Kredit Pro</td>
                                        <td>
                                            <?php
                                                if ($po->payment_status == 'waiting') {
                                                    echo lang('credit_reviewed');
                                                } else if ($po->payment_status == 'accept') {
                                                    echo lang('credit_received');
                                                } else if ($po->payment_status == 'reject') {
                                                    echo lang('credit_declined');
                                                } else if ($po->payment_status == 'partial') {
                                                    echo lang('kredit_partial');
                                                } else if ($po->payment_status == 'paid') {
                                                    echo lang('already_paid');
                                                } else if ($po->payment_status == 'pending') {
                                                    echo '-';
                                                }
                                            ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php } ?>

            <?php if ($returned || $inv->sale_status == "closed" || $inv->sale_status == "canceled") {
                if ($inv->sale_status == "closed") { ?>
                    <h4><?= lang('close_sale_x_action'); ?></h4>
                <?php } else { ?>
                    <h4><?= lang('sale_x_action'); ?></h4>

                <?php }
            } else { ?>
                <div class="form-group">
                    <?= lang('status', 'status'); ?>
                    <?php if ($inv->is_updated_price != 1) { ?>
                        <?php
                        if ($inv->sale_type == 'booking') {
                            $opts = array('pending' => lang('pending'), 'reserved' => lang('reserved'));
                            if ($po) {
                                $opts['confirmed'] = lang('confirmed_not_min_stock');
                                $opts['canceled'] = lang('canceled');
                            }
                            if($inv->client_id == 'atl') {
                                $opts['canceled'] = lang('canceled');
                            }
                        } else {
                            $opts = array('pending' => lang('pending'), 'completed' => lang('completed_min_stock'));
                            if ($po) {
                                $opts['confirmed'] = lang('confirmed_not_min_stock');
                                $opts['canceled'] = lang('canceled');
                            }
                        }
                        ?>
                        <?= form_dropdown('status', $opts, $inv->sale_status, 'class="form-control sale_status" id="status" required="required" style="width:100%;"'); ?>
                    <?php } else { ?>
                        <p>Tidak dapat mengganti status, harus menunggu konfirmasi dari toko.</p>
                    <?php } ?>
                </div>
                <?php if ($po) { ?>
                    <div class="form-group">
                        <?php if ($user->phone_is_verified == 1) { ?>
                            <div class="col-md-6">
                                <input type="checkbox" name="sms" id="sms">
                                <label for="sms" class="padding05">Send SMS Notification</label>
                            </div>
                            <div class="col-md-6">
                                <input type="checkbox" name="whatssapp" id="whatssapp">
                                <label for="whatssapp" class="padding05">Send WA Notification</label>
                            </div>

                        <?php } else { ?>
                            <small class="ml-2"> <i class="text-danger"> <i class="fa fa-times"></i> Nomor Customer Belum Terverifikasi, Tidak Dapat Mengirim Notifikasi SMS atau WhatsApp </i> </small>
                        <?php } ?>
                    </div>
                <?php } ?>
                <div class="form-group Reason" style="display:none;">
                    <?= lang("Canceled Reason", "reason"); ?>
                    <?php echo form_textarea('reason', (isset($_POST['reason']) ? $_POST['reason'] : $this->sma->decode_html($inv->reason)), 'class="form-control" id="reason" style="width: 100%; height: 100px; resize: vertical;"'); ?>
                </div>
                <div class="form-group Notes">
                    <?= lang("note", "note"); ?>
                    <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : $this->sma->decode_html($inv->note)), 'class="form-control" id="note" style="width: 100%; height: 100px; resize: vertical;"'); ?>
                </div>
            <?php } ?>

        </div>
        <?php if (!$returned && $inv->sale_status != 'closed' && $inv->sale_status != 'canceled') { ?>
            <div class="modal-footer">
                <?php echo form_button('update', lang('update'), ' id="update_sale" class="btn btn-primary"'); ?>
            </div>
        <?php } ?>
    </div>
    <?php echo form_close(); ?>
</div>

<div class="modal bootbox  fade bootbox-confirm in" id="modal-confirm" tabindex="-2" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="bootbox-close-button close" id="close-sale" style="margin-top: -10px;">
                    <i class="fa fa-2x">×</i></button>
                <br>
                <div class="bootbox-body">
                    <div id="notifcredit">
                        <?= lang('notif_credit_limit'); ?>
                    </div>
                    <div id="notifbooking">
                        <?= lang('notif_booking'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" id="cancel-sale">Cancel</button>
                <button type="button" class="btn btn-primary" id="submit-sale">Ok</button>
            </div>
        </div>
    </div>

    <?= $modal_js ?>
    <script type="text/javascript" charset="UTF-8">
        $(document).ready(function() {
            $(document).on('change', '.sale_status', function() {
                var val = $(this).val();
                if (val === 'canceled') {
                    $('.Reason').show()
                    $('.Notes').hide()
                } else {
                    $('.Reason').hide()
                    $('.Notes').show()
                }
            });

            $('#update_sale').click(function() {
                /* when the button in the form, display the entered values in the modal */
                var status_sale = $('#status').val();
                if(status_sale == 'canceled'){
                    document.getElementById('formfield').submit();              
                }else{
                    quantity = parseInt($('#quantity').text());
                    real_stock = parseInt($('#real_stock').text());
                    if (real_stock < quantity) {
                        $('#notifbooking').show();
                        $('#notifcredit').hide();
                        $("#modal-confirm").show();
                        $('#modal-confirm').modal({
                            backdrop: false
                        });
                    } else {
                        credit = parseInt($('#remain-credit').text().split('.').join(""));
                        if (credit < 0) {
                            $('#notifbooking').hide();
                            $('#notifcredit').show();
                            $("#modal-confirm").show();
                            $('#modal-confirm').modal({
                                backdrop: false
                            });
                        } else {
                            document.getElementById('formfield').submit();
                        }
                    }
                }
            });
            $('#cancel-sale').click(function() {
                /* when the button in the form, display the entered values in the modal */
                // $('#modal-confirm').modal('hide');
                $('#modal-confirm').hide();
                $('.modal-backdrop').remove();

            });
            $('#close-sale').click(function() {
                /* when the button in the form, display the entered values in the modal */
                // $('#modal-confirm').modal('hide');
                $('#modal-confirm').hide();
                $('.modal-backdrop').remove();

            });
            $('#submit-sale').click(function() {
                var x = document.getElementById('notifbooking');
                if (x.style.display === 'none') {
                    document.getElementById('formfield').submit();
                } else {
                    $('#notifbooking').hide();
                    $('#notifcredit').show();
                }
            });
        });
    </script>