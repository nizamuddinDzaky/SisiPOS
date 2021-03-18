<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    div.fill {
        position: relative;
        display: flex;
        justify-content: center;
        align-items: center;
        overflow: hidden;
        background: silver;
        border-radius: 50%
    }

    div.fill img {
        flex-shrink: 0;
        min-width: 100%;
        min-height: 100%
    }
</style>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('view_payments') . ' (' . lang('sale') . ' ' . lang('reference') . ': ' . $inv->reference_no . ')'; ?></h4>
        </div>
        <div class="modal-body">
            <label>Paid Payment</label>
            <div class="table-responsive">
                <table id="CompTable" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-striped">
                    <thead>
                        <tr>
                            <th style="width:20%;"><?= $this->lang->line("date"); ?></th>
                            <th style="width:10%;"><?= $this->lang->line("image"); ?></th>
                            <th style="width:30%;"><?= $this->lang->line("reference_no"); ?></th>
                            <th style="width:15%;"><?= $this->lang->line("amount"); ?></th>
                            <th style="width:15%;"><?= $this->lang->line("paid_by"); ?></th>
                            <th style="width:10%;"><?= $this->lang->line("actions"); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($payments)) {
                            foreach ($payments as $payment) { ?>
                                <tr class="row<?= $payment->id ?>">
                                    <td><?= $this->sma->hrld($payment->date); ?></td>
                                    <td>
                                        <center>
                                            <?php if ($payment->url_image || $payment->image) { ?>
                                                    <?php if ($payment->url_image) { ?>
                                                        <a href="<?= site_url('sales/payments_tmp_image/' . $payment->id_temp) ?>" data-toggle="modal" data-target="#myModal2">
                                                        <div class=fill style="width:50px; height:50px">
                                                        <img src="<?= $payment->url_image ?>" style="width:100%; height:100%; object-fit: contain;">    
                                                    <?php } else if($payment->image) {?>
                                                        <a href="<?= site_url('sales/payments_tmp_atl_image/' . $payment->id_tmp_atl) ?>" data-toggle="modal" data-target="#myModal2">
                                                        <div class=fill style="width:50px; height:50px">
                                                        <img src="<?= $payment->image ?>" style="width:100%; height:100%; object-fit: contain;">
                                                    <?php } ?>
                                                    </div>
                                                </a>
                                            <?php } else { ?>
                                                <?= lang('no_image'); ?>
                                            <?php } ?>
                                        </center>
                                    </td>
                                    <td><?= $payment->reference_no; ?></td>
                                    <td><?= $this->sma->formatMoney($payment->amount) . ' ' . (($payment->attachment) ? '<a href="' . site_url('welcome/download/' . $payment->attachment) . '"><i class="fa fa-chain"></i></a>' : ''); ?></td>
                                    <td><?= lang($payment->paid_by); ?></td>
                                    <td>
                                        <div class="text-center">
                                            <a href="<?= site_url('sales/payment_note/' . $payment->id) ?>" data-toggle="modal" data-target="#myModal2"><i class="fa fa-file-text-o"></i></a>
                                            <?php if ($payment->paid_by != 'gift_card') { ?>
                                                <a href="<?= site_url('sales/edit_payment/' . $payment->id) ?>" data-toggle="modal" data-target="#myModal2"><i class="fa fa-edit"></i></a>
                                                <!-- <a href="#" class="po"
                                               title="<b><?= $this->lang->line("delete_payment") ?></b>"
                                               data-content="<p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' id='<?= $payment->id ?>' href='<?= site_url('sales/delete_payment/' . $payment->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn po-close'><?= lang('no') ?></button>"
                                               rel="popover"><i class="fa fa-trash-o"></i></a> -->
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                        <?php }
                        } else {
                            echo "<tr><td colspan='6'>" . lang('no_data_available') . "</td></tr>";
                        } ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($payments_tmp)) { ?>
                <label>Pending Payment</label>
                <div class="table-responsive">
                    <table id="CompTable" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th style="width:20%;"><?= $this->lang->line("date"); ?></th>
                                <th style="width:10%;">Image</th>
                                <th style="width:30%;">Amount</th>
                                <th style="width:40%;" colspan="2"><?= $this->lang->line("actions"); ?></th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($payments_tmp)) {
                                foreach ($payments_tmp as $payment) { ?>
                                    <tr class="row<?= $payment->id ?>">
                                        <td><?= $this->sma->hrld($payment->created_at); ?></td>
                                        <td>
                                            <?php if ($payment->url_image) { ?>
                                                <center>
                                                    <a href="<?= site_url('sales/payments_tmp_image/' . $payment->id) ?>" data-toggle="modal" data-target="#myModal2">
                                                        <div class=fill style="width:50px; height:50px">
                                                            <img class="img-circle" src="<?= $payment->url_image ?>" style="width:50%;height:50%;">
                                                    </a>
                </div>
                </center>
            <?php } else { ?>
                <?= lang('no_image'); ?>
            <?php } ?>
            </td>
            <td>
                <center>
                    Rp <?= number_format($payment->nominal, 2, ',', '.'); ?>
                </center>
            </td>
            <?php if ($payment->status == 'pending') { ?>
                <td>
                    <div class="text-center">
                        <a class="btn btn-success btn-confirm" href="<?= site_url('sales/confirm_payment/' . $payment->id) ?>"><i class="fa fa-check"></i> Confirm</a>
                    </div>
                </td>
                <?php if ($purchase->payment_method == 'kredit_pro'||$purchase->payment_method == 'kredit_mandiri'){}else { ?>
                <td>
                    <div class="text-center">
                        <a class="btn btn-danger btn-confirm" href="<?= site_url('sales/reject_payment/' . $payment->id) ?>"><i class="fa fa-close"></i> Reject</a>
                    </div>
                </td>
                <?php } ?>
            <?php } else { ?>
                <td colspan="2">
                    <div class="text-center">
                        <span class="label label-danger"><?= $payment->status ?></span>
                    </div>
                </td>
            <?php } ?>
            </tr>
    <?php }
                            } else {
                                echo "<tr><td colspan='5'>" . lang('no_data_available') . "</td></tr>";
                            } ?>
    </tbody>
    </table>
        </div>
    <?php } ?>

    <?php if (!empty($payments_atl_tmp)) { ?>
        <label>Pending Payment For AksesToko Liferay</label>
        <div class="table-responsive">
            <table id="CompTable" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-striped">
                <thead>
                    <tr>
                        <th style="width:20%;"><?= $this->lang->line("date"); ?></th>
                        <th style="width:10%;">Image</th>
                        <th style="width:30%;">Amount</th>
                        <th style="width:40%;" colspan="2"><?= $this->lang->line("actions"); ?></th>

                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($payments_atl_tmp)) {
                        foreach ($payments_atl_tmp as $payment_atl) { ?>
                            <tr class="row<?= $payment_atl->id ?>">
                                <td><?= $this->sma->hrld($payment_atl->createddate); ?></td>
                                <td>
                                    <?php if ($payment_atl->image) { ?>
                                        <center>
                                            <a href="<?= site_url('sales/payments_tmp_atl_image/' . $payment_atl->id) ?>" data-toggle="modal" data-target="#myModal2">
                                                <div class=fill style="width:50px; height:50px">
                                                    <img class="img-circle" src="<?= $payment_atl->image ?>" style="width:50%;height:50%;">
                                            </a>
        </div>
        </center>
    <?php } else { ?>
        <?= lang('no_image'); ?>
    <?php } ?>
    </td>
    <td>
        <center>
            Rp <?= number_format($payment_atl->paymentamount, 2, ',', '.'); ?>
        </center>
    </td>
    <?php if ($payment_atl->status == 'pending') { ?>
        <td>
            <div class="text-center">
                <a class="btn btn-success btn-confirm" href="<?= site_url('sales/confirm_payment_atl/' . $payment_atl->id) ?>"><i class="fa fa-check"></i> Confirm</a>
            </div>
        </td>
        <td>
            <div class="text-center">
                <a class="btn btn-danger btn-confirm" href="<?= site_url('sales/reject_payment_atl/' . $payment_atl->id) ?>"><i class="fa fa-close"></i> Reject</a>
            </div>
        </td>
    <?php } else { ?>
        <td colspan="2">
            <div class="text-center">
                <span class="label label-danger"><?= $payment_atl->status ?></span>
            </div>
        </td>
    <?php } ?>
    </tr>
<?php }
                    } else {
                        echo "<tr><td colspan='5'>" . lang('no_data_available') . "</td></tr>";
                    } ?>
</tbody>
</table>
    </div>
<?php } ?>
</div>
</div>
</div>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function() {
        $(document).on('click', '.po-delete', function() {
            var id = $(this).attr('id');
            $(this).closest('tr').remove();
        });

        $(".btn-confirm").click(function(e) {
            $(".btn-confirm").attr('disabled', 'disabled');
            $(this).html(`<i class="fa fa-circle-o-notch fa-spin"></i> Loading...`);
        });
    });
</script>