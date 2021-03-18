<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('view_payments').' ('.lang('purcahse').' '.lang('reference').': '.$inv->reference_no.')'; ?></h4>
        </div>
        <div class="modal-body">
            <div class="table-responsive">
                <table id="CompTable" class="table table-bordered table-hover table-striped">
                    <thead>
                    <tr>
                        <th style="width:25%;"><?= $this->lang->line("date"); ?></th>
                        <th style="width:25%;"><?= $this->lang->line("reference_no"); ?></th>
                        <?php if($Official){ ?>
                        <th style="width:15%;"><?= $this->lang->line("date_partner"); ?></th>
                        <th style="width:20%;"><?= $this->lang->line("reference_partner"); ?></th>
                        <?php } ?>
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
                                <td><?= $payment->reference_no; ?></td>
                                <?php  if($Official){ ?>
                                <td><?= $this->sma->hrsd($payment->date_dist); ?></td>
                                <td><?= $payment->reference_dist; ?></td>
                                <?php } ?>
                                <td><?= $this->sma->formatMoney($payment->amount) . ' ' . (($payment->attachment) ? '<a href="' . site_url('welcome/download/' . $payment->attachment) . '"><i class="fa fa-chain"></i></a>' : ''); ?></td>
                                <td><?= lang($payment->paid_by); ?></td>
                                <td>
                                    <div class="text-center">
                                        <a href="<?= site_url('purchases/payment_note/' . $payment->id) ?>"
                                           data-toggle="modal" data-target="#myModal2"><i class="fa fa-file-text-o"></i></a>
                                        <a href="<?= site_url('purchases/edit_payment/' . $payment->id) ?>"
                                           data-toggle="modal" data-target="#myModal2"><i class="fa fa-edit"></i></a>
                                        <a href="#" class="po" title="<b><?= $this->lang->line("delete_payment") ?></b>"
                                           data-content="<p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' id='<?= $payment->id ?>' href='<?= site_url('purchases/delete_payment/' . $payment->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn po-close'><?= lang('no') ?></button>"
                                           rel="popover"><i class="fa fa-trash-o"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php }
                    } else {
                        echo "<tr><td colspan='4'>" . lang('no_data_available') . "</td></tr>";
                    } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function () {
        $(document).on('click', '.po-delete', function () {
            var id = $(this).attr('id');
            $(this).closest('tr').remove();
        });
    });
</script>    