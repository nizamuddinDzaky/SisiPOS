<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel"></h4>
        </div>
        <div class="modal-body">
            <label>List Address</label>
            <div class="table-responsive">
                <table id="CompTable" cellpadding="0" cellspacing="0" border="0"
                       class="table table-bordered table-hover table-striped">
                    <thead>
                    <tr>
                        <th style="width:30%;"><?= $this->lang->line("name"); ?> </th>
                        <th><?= $this->lang->line("phone"); ?></th>
                        <th style="width:30%;"><?= $this->lang->line("address"); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($addresses)) {
                        foreach ($addresses as $address) { ?>
                            <tr>
                                <td><?=$address->name?></td>
                                <td><?=$address->phone?></td>
                                <td><?=trim($address->address)?>, <?=ucwords(strtolower($address->village))?>, <?=ucwords(strtolower($address->state))?>, <?=ucwords(strtolower($address->city))?>, <?=ucwords(strtolower($address->country))?> - <?=$address->postal_code?></td>
                            </tr>
                        <?php }
                    } else {
                        echo "<tr><td colspan='5'>" . lang('no_data_available') . "</td></tr>";
                    } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>