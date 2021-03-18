<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
    div.fill {
    position:relative;
    display:flex;
    justify-content:center;
    align-items:center;
    overflow:hidden;
    background:silver;
    border-radius:50%
}
div.fill img {
    flex-shrink:0;
    min-width:100%;
    min-height:100%
}
</style>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= $sales_person->company ? $sales_person->company : '-' ; ?></h4>
        </div>
        <div class="modal-body">
            <div class="table-responsive">
                <?php if($sales_person->photo){ ?>
                    <center>
                        <div class = fill style = "width:150px; height:150px; margin-bottom:20px;">
                            <img src="<?= base_url('assets/uploads/avatars/thumbs/' . $sales_person->photo) ?>" alt="<?= $biller->company != '-' ? $biller->company : $biller->name; ?>">
                        </div>
                    </center>   
                <?php } ?>
                <table class="table table-striped table-bordered" style="margin-bottom:0;">
                    <tbody>
                    <tr>
                        <td><strong><?= lang("company"); ?></strong></td>
                        <td><?= $sales_person->company ? $sales_person->company : '-'; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("referal_code"); ?></strong></td>
                        <td><?= $sales_person->reference_no; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("name"); ?></strong></td>
                        <td><?= $sales_person->name; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("vat_no"); ?></strong></td>
                        <td><?= $sales_person->vat_no ? $sales_person->vat_no : '-'; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("email"); ?></strong></td>
                        <td><?= $sales_person->email ? $sales_person->email : '-'; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("phone"); ?></strong></td>
                        <td><?= $sales_person->phone; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("address"); ?></strong></td>
                        <td><?= $sales_person->address; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("city"); ?></strong></td>
                        <td><?= $sales_person->city; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("state"); ?></strong></td>
                        <td><?= $sales_person->state; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("country"); ?></strong></td>
                        <td><?= $sales_person->country; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("postal_code"); ?></strong></td>
                        <td><?= $sales_person->postal_code ? $sales_person->postal_code : '-'; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("status"); ?></strong></td>
                        <td><?= $sales_person->is_active == 1 ? 'Active' : 'Inactive'; ?></strong></td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer no-print">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= lang('close'); ?></button>
                <?php if ($Owner || $Admin) { ?>
                    <a href="<?=site_url('sales_person/edit/'.$sales_person->id);?>" data-toggle="modal" data-target="#myModal2" class="btn btn-primary"><?= lang('edit_sales_person'); ?></a>
                <?php } ?>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>