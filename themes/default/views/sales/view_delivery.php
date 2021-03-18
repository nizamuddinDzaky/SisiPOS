<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg no-modal-header">
    <div class="modal-content">
        <div class="modal-body">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>

            <a href="<?= site_url('sales/view_delivery_print/' . $delivery->id); ?>" type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" target="_blank">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </a>
            <!--  <?php if ($logo) { ?>
                <div class="text-center" style="margin-bottom:20px;">
                    <img src="<?= base_url() . 'assets/uploads/logos/' . $biller->logo; ?>"
                         alt="<?= $biller->company != '-' ? $biller->company : $biller->name; ?>">
                </div>
            <?php } ?> -->
            <div class="table-responsive">
                <table class="table table-bordered">

                    <tbody>
                        <tr>
                            <td width="30%"><?= lang("date"); ?></td>
                            <td width="70%"><?= $this->sma->hrld($delivery->date); ?></td>
                        </tr>
                        <?php if ($delivery->status == "returned") { ?>
                            <tr>
                                <td><?= lang("return_reference_no"); ?></td>
                                <td><?= $delivery->do_reference_no; ?></td>
                            </tr>

                            <tr>
                                <td><?= lang("do_reference_no"); ?></td>
                                <td><?= $delivery->return_reference_no; ?></td>
                            </tr>
                        <?php } else { ?>
                            <tr>
                                <td><?= lang("do_reference_no"); ?></td>
                                <td><?= $delivery->do_reference_no; ?></td>
                            </tr>
                        <?php
                        }

                        if ($delivery->status != "returned" && $delivery->return_reference_no) { ?>
                            <tr>
                                <td><?= lang("return_reference_no"); ?></td>
                                <td><?= $delivery->return_reference_no; ?></td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td><?= lang("sale_reference_no"); ?></td>
                            <td><?= $delivery->sale_reference_no; ?></td>
                        </tr>
                        <tr>
                            <td><?= lang("customer"); ?></td>
                            <td><?= $delivery->customer; ?></td>
                        </tr>
                        <tr>
                            <td><?= lang("address"); ?></td>
                            <td><?= $delivery->address; ?></td>
                        </tr>
                        <tr>
                            <td><?= lang("status"); ?></td>
                            <td><?= lang($delivery->status); ?></td>
                        </tr>
                        <!-- need lang -->
                        <?php if ($delivery->status == "delivered" || $delivery->status == "delivering") { ?>
                            <tr>
                                <td width="30%"><?= lang("delivering_date"); ?></td>
                                <td width="70%"><?= $this->sma->hrld($delivery->delivering_date); ?></td>
                            </tr>
                        <?php } ?>
                        <?php if ($delivery->status == "delivered") { ?>
                            <tr>
                                <td width="30%"><?= lang("delivered_date"); ?></td>
                                <td width="70%"><?= $this->sma->hrld($delivery->delivered_date); ?></td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td><?= lang("shipping"); ?></td>
                            <td><?= $shipping != '0' ? ($shipping ? $this->sma->formatDecimal($shipping) : '-') : '-' ?></td>
                        </tr>
                        <?php if ($delivery->spj_file) { ?>
                            <tr>
                                <td><?= lang("view_documents"); ?></td>
                                <td>
                                    <a href="<?= $delivery->spj_file ?>" target="_blank">SPJ Document</a>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if ($delivery->note) { ?>
                            <tr>
                                <td><?= lang("note"); ?></td>
                                <td><?= $this->sma->decode_html($delivery->note); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>

                </table>
            </div>
            <?php if ($delivery->status == "returned") { ?>
                <h3><?= lang("return_items"); ?></h3>
            <?php } else { ?>
                <h3><?= lang("items"); ?></h3>
            <?php } ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead>
                        <tr>
                            <th style="text-align:center; vertical-align:middle;">No</th>
                            <th style="vertical-align:middle;"><?= lang("description"); ?></th>
                            <th style="text-align:center; vertical-align:middle;"><?= lang("quantity"); ?></th>
                            <!-- need lang  -->
                            <?php if (in_array($delivery->client_id, $new_view) && $delivery->status != "returned") { ?>
                                <th style="text-align:center; vertical-align:middle;"><?= lang("good"); ?></th>
                                <th style="text-align:center; vertical-align:middle;"><?= lang("bad"); ?></th>
                            <?php } ?>
                        </tr>
                    </thead>

                    <tbody>

                        <?php
                        if (!in_array($delivery->client_id, $new_view)) {
                            $r = 1;
                            foreach ($rows as $row) : ?>
                                <tr>
                                    <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                    <td style="vertical-align:middle;">
                                        <?= $row->product_code . " - " . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : '');
                                        if ($row->details) {
                                            echo '<br><strong>' . lang("product_details") . '</strong> ' .
                                                html_entity_decode($row->details);
                                        }
                                        ?>
                                    </td>
                                    <td style="width: 150px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->unit_quantity) . ' ' . $row->product_unit_code; ?></td>
                                </tr>
                            <?php
                                $r++;
                            endforeach; ?>
                            <?php
                        } else {
                            $r = 1;
                            $bad = [];
                            foreach ($delivery_items as $i => $delivery_item) :
                                if ((int) $delivery_item->bad_quantity > 0) {
                                    $bad[] = (int) $delivery_item->bad_quantity;
                                } ?>
                                <tr>
                                    <td style="text-align:center; width:40px; vertical-align:middle;"><?= $i + 1; ?></td>
                                    <td style="vertical-align:middle;">
                                        <?= $delivery_item->product_code . " - " . $delivery_item->product_name ?>
                                    </td>
                                    <td style="width: 150px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($delivery_item->quantity_sent) . ' ' . $delivery_item->product_unit_code; ?></td>
                                    <?php if (in_array($delivery->client_id, $new_view) && $delivery->status != "returned") { ?>
                                        <td style="text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($delivery_item->good_quantity) . ' ' . $delivery_item->product_unit_code; ?></td>
                                        <td style="text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($delivery_item->bad_quantity) . ' ' . $delivery_item->product_unit_code; ?></td>
                                    <?php } ?>
                                </tr>
                        <?php
                                $r++;
                            endforeach;
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <?php
            if ($sale->sale_type == "booking" && $sale->client_id == "aksestoko") {
                if (array_sum($bad) > 0 && is_null($delivery->is_approval) && is_null($delivery->is_reject) && is_null($delivery->is_confirm) && $delivery->status != 'returned') {
                    echo '<div class="fprom-group text-center"><span >Approval Bad Quantity</span><br>';
                    echo form_button(['content' => lang("approve")], null, 'class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;" onclick="approve()"');
                    echo "&nbsp;&nbsp;";
                    echo anchor('sales_booking/reject_bad_quantity/' . $delivery->id, lang('reject'), 'class="btn btn-danger"  style="padding: 6px 15px; margin:15px 0;" ');
                    echo '</div>';
                } elseif (array_sum($bad) > 0 && $delivery->is_reject == 2 && $delivery->is_confirm == 1 && $delivery->status != 'returned') {
                    echo '<div class="fprom-group text-center"><span >Approval Bad Quantity</span><br>';
                    echo form_button(['content' => lang("approve")], null, 'class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;" onclick="approve()"');
                    echo "&nbsp;&nbsp;";
                    echo anchor('sales_booking/reject_bad_quantity/' . $delivery->id, lang('reject'), 'class="btn btn-danger"  style="padding: 6px 15px; margin:15px 0;" ');
                    echo '</div>';
                } elseif ($delivery->is_reject == 3 || $delivery->is_reject == 1) {
                    echo '<div class="alert alert-danger text-center">Bad Quantity is Rejected</div>';
                } elseif ($delivery->is_approval == 1) {
                    echo '<div class="alert alert-success text-center">Bad Quantity is Approved</div>';
                }
            }
            ?>

            <div class="clearfix"></div>

            <?php if ($delivery->status == 'delivered') { ?>
                <div class="row">
                    <div class="col-xs-4">
                        <p><?= lang("prepared_by"); ?>:<br> <?= $user->first_name . ' ' . $user->last_name; ?> </p>
                    </div>
                    <div class="col-xs-4">
                        <p><?= lang("delivered_by"); ?>:<br> <?= $delivery->delivered_by; ?></p>
                    </div>
                    <div class="col-xs-4">
                        <p><?= lang("received_by"); ?>:<br> <?= $delivery->received_by; ?></p>
                    </div>
                </div>
            <?php } else { ?>
                <div class="row">
                    <div class="col-xs-4">
                        <p style="height:80px;"><?= lang("prepared_by"); ?>
                            : <?= $user->first_name . ' ' . $user->last_name; ?> </p>
                        <hr>
                        <p><?= lang("stamp_sign"); ?></p>
                    </div>
                    <div class="col-xs-4">
                        <p style="height:80px;"><?= lang("delivered_by"); ?>: </p>
                        <hr>
                        <p><?= lang("stamp_sign"); ?></p>
                    </div>
                    <div class="col-xs-4">
                        <p style="height:80px;"><?= lang("received_by"); ?>: </p>
                        <hr>
                        <p><?= lang("stamp_sign"); ?></p>
                    </div>
                </div>
            <?php } ?>

        </div>
    </div>
</div>


<!-- modal jika approve bad quantity-->
<div class="modal fade" tabindex="-1" role="dialog" id="approvemodal">
    <div class="modal-dialog modal-lg" role="document" style="width: 65%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" aria-hidden="true" id="close"><i class="fa fa-2x">&times;</i>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?= lang('return_delivery'); ?></h4>
            </div>
            <div class="modal-body" id="view_return"></div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<!-- modal jika reject bad quantity-->
<div class="modal bootbox  fade bootbox-confirm in" id="rejectmodal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="bootbox-close-button close" onclick="close_reject()" aria-hidden="true" style="margin-top: -10px;">
                    <i class="fa fa-2x">×</i></button>
                <br>
                <div class="bootbox-body">Are You Sure Want to Reject ?</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="close_reject()" aria-hidden="true">Cancel</button>
                <button type="button" class="btn btn-success" id="submit-sale" onclick="reject();">Ok</button>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $('#approve').click(function() {
            var no = generateCardNo();
            $(this).parent().parent('.input-group').children('input').val(no);
            return false;
        });

        $('#reject').click(function() {
            $("#rejectmodal").show();
            $('#rejectmodal').modal({
                backdrop: false
            });
        });

        function close_reject() {
            $("#rejectmodal").hide();
        }

        function reject() {
            $.ajax({
                type: "post",
                url: "<?= site_url('sales_booking/reject_bad_quantity/' . $delivery->id); ?>",
                success: function(data) {
                    window.location.reload();
                }
            });
        }

        function approve() {
            $("#approvemodal").show();
            $('#approvemodal').modal({
                backdrop: false
            });

            $("#close").click(function() {
                $("#approvemodal").hide();
            });

            $.ajax({
                type: "get",
                url: "<?= site_url('sales/return_delivery/' . $delivery->id . '/approval'); ?>",
                success: function(data) {
                    $('#view_return').html(data);
                }
            })
        }
    </script>