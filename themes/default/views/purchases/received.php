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
            <h4 class="modal-title" id="myModalLabel">Received History</h4>
        </div>
        <div class="modal-body">
            <div class="table-responsive">
                <table id="CompTable" class="table table-bordered table-hover table-striped">
                    <thead>
                    <tr>
                        <th>PO Reference</th>
                        <th>Product Code</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Quantity Received</th>
                        <th>DO Reference</th>
                        <th>Created At</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($received)) {
                        foreach ($received as $r) { ?>
                            <tr>
                                <td><?=$r->reference_no?></td>
                                <td><?=$r->product_code?></td>
                                <td><?=$r->product_name?></td>
                                <td><?=(int) $r->quantity?></td>
                                <td><?=(int) $r->quantity_received?></td>
                                <td><?=$r->sino_do?></td>
                                <td><?=$r->created_at?></td>
                            </tr>
                        <?php }
                    } else {
                        echo "<tr><td colspan='8'>" . lang('no_data_available') . "</td></tr>";
                    } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>    
<?= $modal_js ?>