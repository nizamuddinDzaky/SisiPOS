<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    .waiting {
        color: #cd0505;
        background-color: #cfddfc !important;
        border-color: #ebccd1;
    }
    .table_custom {
        border: 2px solid;
        padding: 100px;
        box-shadow: 5px 7px 5px #cfddfc;
    }
</style>
<!-- Form Examples area start-->
<div class="form-example-area">
    <div class="container">

        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="form-example-wrap">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <h3><?= lang("subscription") ?></h3>
                        </div>
                    </div><hr>

                    <?php if($this->AdminBilling || $this->Owner){}else{?>
                    <div class="col-md-6">
                        <h4><?=lang('current_plan')?> :</h4>
                    </div>
                    <div class="col-md-6">
                        <?php 
                            $getAuthor = $this->sma->getAuthorized(); 
                            $billing = $this->auth_model->getBillingWaitingPending();
                            if($getAuthor->status == 'activated' || $billing->billing_status == 'waiting confirmation' || $billing->billing_status == 'waiting confirmation renewal' || $billing->billing_status == 'pending'){}else{
                        ?>
                        <div class="form-group pull-right">
                            <a href='<?= site_url('billing_portal/subscription/view_plans_pricing');?>' name="plans_pricing" id="plans_pricing" class="btn btn-primary" ><?=lang('view_pricing_plans')?></a>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="data-table-list">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-striped table-condensed table_custom">
                                        <thead>
                                        <tr>
                                            <th class="text-center"><?php echo $this->lang->line("type"); ?></th>
                                            <th class="text-center"><?php echo $this->lang->line("status"); ?></th>
                                            <th class="text-center"><?php echo $this->lang->line("user"); ?></th>
                                            <th class="text-center"><?php echo $this->lang->line("warehouse"); ?></th>
                                            <th class="text-center"><?php echo $this->lang->line("start_date"); ?></th>
                                            <th class="text-center"><?php echo $this->lang->line("expired_date"); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            <?php ?>
                                            <tr>
                                                <td class="text-center"><?=$authorized->plan_name?></td>
                                                <td class="text-center"><?= ($authorized->plan_id == 1) ? 'activated' : $authorized->status?></td>
                                                <td class="text-center"><?=$authorized->users?></td>
                                                <td class="text-center"><?=$authorized->warehouses?></td>
                                                <td class="text-center" style="width:25%;"><?= (is_null($authorized->start_date)) ? '-' : $this->sma->hrsd($authorized->start_date) ?></td>
                                                <?php if($this->Admin && $expiredBill){ ?>
                                                    <td class="text-center spinnerbox spinner-box" style="width:19%;"><?=$this->sma->hrsd($authorized->expired_date)?></td>
                                                <?php }else{ ?>
                                                    <td class="text-center" style="width:25%;"><?= (is_null($authorized->expired_date)) ? '-' : $this->sma->hrsd($authorized->expired_date)?></td>
                                                <?php } ?>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="clearfix"></div><hr>
                    <?php } ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="data-table-list">
                                <div class="table-responsive">
                                    <div class="table-responsive">
                                    <table id="myTable" class="table table-bordered table-hover table-striped  table-condensed">
                                        <thead>
                                        <tr>
                                            <th></th>
                                            <th><?php echo $this->lang->line("date"); ?></th>
                                            <th><?php echo $this->lang->line("reference_no"); ?></th>
                                            <th><?php echo $this->lang->line("billing_status"); ?></th>
                                            <th><?php echo $this->lang->line("payment_status"); ?></th>
                                            <th><?php echo $this->lang->line("company"); ?></th>
                                            <th><?php echo $this->lang->line("type"); ?></th>
                                            <th><?php echo $this->lang->line("start_date"); ?></th>
                                            <th><?php echo $this->lang->line("expired_date"); ?></th>
                                            <th><?php echo $this->lang->line("grand_total"); ?></th>
                                            <th><?php echo $this->lang->line("created_by"); ?></th>
                                            <th></th>
                                            <th style="max-width:85px;"><?php echo $this->lang->line("actions"); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="12" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        
    </div>
</div>
<!-- Form Examples area End-->


<script type="text/javascript">
    $(document).ready(function () {
        $('#myTable').dataTable({
            "aaSorting": [[0, "desc"], [8, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": 10,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('billing_portal/subscription/get_subscription_record') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function(nRow, aData, iDisplayIndex) {
                var url_cancel = site.base_url +"billing_portal/subscription/cancel";
                var url_finish = site.base_url +"billing_portal/subscription/finish";
                var url_finishRenewal = site.base_url +"billing_portal/subscription/finishRenewal";
                var url_getBillingPayment = site.base_url +"billing_portal/subscription/getBillingPayment";
                var url_pdf = site.base_url +"billing_portal/subscription/pdf/";
                var url_renew = site.base_url +"billing_portal/subscription/view_plans_pricing/renew";
                var url_plan = site.base_url +"billing_portal/subscription/view_plans_pricing";
                var url_reupload = site.base_url +"billing_portal/subscription/view_plans_pricing/reupload";
                var url_reject = site.base_url +"billing_portal/subscription/reject";
                var url_pay_admin = site.base_url +"billing_portal/subscription/pay_from_admin";

                var cancel_subscription = '<?= lang("cancel_subscription")?>';
                var pay = '<?= lang("pay")?>';
                var payment_image = '<?= lang("payment_image")?>';
                var invoice = '<?= lang("invoice")?>';
                var renewal = '<?= lang("renewal")?>';
                var reject = '<?= lang("reject")?>';
                var next_to_payment = '<?= lang("next_to_payment")?>';
                var upload_payment = '<?= lang("reupload_payment")?>';

                var act_none =   "<div class='text-center row'></div>";

                act_pending_cancel =   "<div class='text-center row'>";
                act_pending_cancel +=  "<a onclick='cancel_button("+aData[0]+")' id='cancel_id_"+aData[0]+"' data-action='"+url_cancel+"'  class='tip btn btn-danger notika-btn-primary btn-sm' title='"+cancel_subscription+"'><i class='fa fa-trash'></i></a>&nbsp;";
                act_pending_cancel +=  "<a href ='"+url_plan+"' class='tip btn btn-success notika-btn-primary btn-sm' title='"+next_to_payment+"'><i class='fa fa-arrow-right'></i> </a></div>";

                act_payment_reject =   "<div class='text-center row'>";
                act_payment_reject +=  "<a href ='"+url_reupload+"' class='tip btn btn-success notika-btn-primary btn-sm' title='"+upload_payment+"'><i class='fa fa-arrow-right'></i> </a></div>";

                act_payment_cancel =   "<div class='text-center row'>";
                act_payment_cancel +=  "<a onclick='cancel_button("+aData[0]+")' id='cancel_id_"+aData[0]+"' data-action='"+url_cancel+"'  class='tip btn btn-danger notika-btn-primary btn-sm' title='"+cancel_subscription+"'><i class='fa fa-trash'></i></a>&nbsp;";
                act_payment_cancel +=  "<a onclick='pay_button("+aData[0]+")' id='pay_id_"+aData[0]+"' data-action='"+url_pay_admin+"'  class='tip btn btn-success notika-btn-primary btn-sm' title='"+pay+"'><i class='fa fa-arrow-right'></i> </a></div>";

                act_img_pay =   "<div class='text-center row'>";
                act_img_pay +=  "<a onclick='image_button("+aData[0]+")' id='image_id_"+aData[0]+"' data-action='"+url_getBillingPayment+"'  class='tip btn btn-warning notika-btn-primary btn-xs' title='"+payment_image+"'><i class='fa fa-picture-o'></i></a>&nbsp;";
                act_img_pay +=  "<a onclick='pay_button("+aData[0]+")' id='pay_id_"+aData[0]+"'  data-action='"+url_finish+"' class='tip btn btn-primary notika-btn-primary btn-xs' title='"+pay+"'><i class='fa fa-money'></i> </a>&nbsp;";
                act_img_pay +=  "<a onclick='reject_button("+aData[0]+")' id='reject_id_"+aData[0]+"' data-action='"+url_reject+"'  class='tip btn btn-danger notika-btn-danger btn-xs' title='"+reject+"'><i class='fa fa-close'></i></a></div>";

                act_img =   "<div class='text-center row'>";
                act_img +=  "<a onclick='image_button("+aData[0]+")' id='image_id_"+aData[0]+"' data-action='"+url_getBillingPayment+"'  class='tip btn btn-warning notika-btn-primary btn-sm' title='"+payment_image+"'><i class='fa fa-picture-o'></i></a></div>";

                act_img_inv =   "<div class='text-center row'>";
                act_img_inv +=  "<a href='"+url_pdf+aData[0]+"' class='tip btn btn-info notika-btn-primary btn-sm' title='"+invoice+"'><i class='fa fa-file'></i></a>&nbsp;";
                act_img_inv +=  "<a onclick='image_button("+aData[0]+")' id='image_id_"+aData[0]+"' data-action='"+url_getBillingPayment+"'  class='tip btn btn-warning notika-btn-primary btn-sm' title='"+payment_image+"'><i class='fa fa-picture-o'></i></a></div>";

                act_pay =   "<div class='text-center row'>";
                act_pay +=  "<a onclick='pay_button("+aData[0]+")' id='pay_id_"+aData[0]+"'  data-action='"+url_finish+"' class='tip btn btn-primary notika-btn-primary btn-sm' title='"+pay+"'><i class='fa fa-money'></i> </a></div>";

                act_pay_renewal =   "<div class='text-center row'>";
                act_pay_renewal +=  "<a onclick='image_button("+aData[0]+")' id='image_id_"+aData[0]+"' data-action='"+url_getBillingPayment+"'  class='tip btn btn-warning notika-btn-primary btn-xs' title='"+payment_image+"'><i class='fa fa-picture-o'></i></a>&nbsp;";
                act_pay_renewal +=  "<a onclick='pay_button("+aData[0]+")' id='pay_id_"+aData[0]+"'  data-action='"+url_finishRenewal+"' class='tip btn btn-primary notika-btn-primary btn-xs' title='"+pay+"'><i class='fa fa-money'></i> </a>&nbsp;";
                act_pay_renewal +=  "<a onclick='reject_button("+aData[0]+")' id='reject_id_"+aData[0]+"' data-action='"+url_reject+"'  class='tip btn btn-danger notika-btn-danger btn-xs' title='"+reject+"'><i class='fa fa-close'></i></a></div>";

                act_renew =   "<div class='text-center row'>";
                act_renew +=  "<a onclick='image_button("+aData[0]+")' id='image_id_"+aData[0]+"' data-action='"+url_getBillingPayment+"'  class='tip btn btn-warning notika-btn-primary btn-xs' title='"+payment_image+"'><i class='fa fa-picture-o'></i></a>&nbsp;";
                act_renew +=  "<a href='"+url_pdf+aData[0]+"' class='tip btn btn-info notika-btn-primary btn-xs' title='"+invoice+"'><i class='fa fa-file'></i></a>&nbsp;";
                act_renew +=  "<a onclick='renew_button("+aData[0]+")' id='renew_id_"+aData[0]+"' data-action='"+url_renew+"'  class='tip btn btn-success notika-btn-primary btn-xs' title='"+renewal+"'><i class='fa fa-plus-circle'></i></a></div>";

                act_exp =   "<div class='text-center row'>";
                act_exp +=  "<a onclick='image_button("+aData[0]+")' id='image_id_"+aData[0]+"' data-action='"+url_getBillingPayment+"'  class='tip btn btn-warning notika-btn-primary btn-sm' title='"+payment_image+"'><i class='fa fa-picture-o'></i></a>&nbsp;";
                act_exp +=  "<a href='"+url_pdf+aData[0]+"' class='tip btn btn-info notika-btn-primary btn-sm' title='"+invoice+"'><i class='fa fa-file'></i></a></div>";

                var AdminBilling = "<?= $this->AdminBilling ?>";
                var Admin = "<?= $this->Admin ?>";


                if(AdminBilling){
                    if((aData[3] == 'pending' || aData[3] == 'pending renewal') && aData[4] == 'pending'){
                        $('td:eq(10)', nRow).html(act_payment_cancel);
                    }
                    else if(aData[3] == 'waiting confirmation' && (aData[10] == 'waiting confirmation' || aData[10] == 'activated')){
                        nRow.className = "waiting";
                        $('td:eq(10)', nRow).html(act_img_pay);
                    }
                    else if(aData[3] == 'waiting confirmation renewal' && (aData[10] == 'waiting confirmation' || aData[10] == 'activated')){
                        nRow.className = "waiting";
                        $('td:eq(10)', nRow).html(act_pay_renewal);
                    }
                    else if(aData[4] == 'canceled'){
                        $('td:eq(10)', nRow).html('');
                    }
                    else if(aData[3] == 'active' && aData[4] == 'paid'){
                        $('td:eq(10)', nRow).html(act_img_inv);
                    }
                    else if(aData[3] == 'expired' && aData[4] == 'paid'){
                        $('td:eq(10)', nRow).html(act_exp);
                    }
                    else if(aData[3] == 'waiting confirmation' && aData[4] == 'rejected'){
                        $('td:eq(10)', nRow).html(act_img);
                    }
                }
                else{
                    if((aData[3] == 'pending' || aData[3] == 'pending renewal') && aData[4] == 'pending'){
                        $('td:eq(10)', nRow).html(act_pending_cancel);
                    }
                    else if(aData[3] == 'waiting confirmation' && (aData[10] == 'waiting confirmation' || aData[10] == 'activated')){
                        nRow.className = "waiting";
                        $('td:eq(10)', nRow).html(act_img);
                    }
                    else if(aData[3] == 'waiting confirmation renewal' && (aData[10] == 'waiting confirmation' || aData[10] == 'activated')){
                        nRow.className = "waiting";
                        $('td:eq(10)', nRow).html(act_img);
                    }
                    else if(aData[4] == 'canceled'){
                        $('td:eq(10)', nRow).html('');
                    }
                    else if(aData[3] == 'active' && aData[4] == 'paid'){
                        $('td:eq(10)', nRow).html(act_renew);
                    }
                    else if(aData[3] == 'expired' && aData[4] == 'paid'){
                        $('td:eq(10)', nRow).html(act_exp);
                    }
                    else if(aData[3] == 'pending' && aData[4] == 'rejected'){
                        $('td:eq(10)', nRow).html(act_payment_reject);
                        $('td:eq(3)', nRow).addClass("spinnerbox spinner-box");
                    }
                }
                if(aData[11] == 12){
                    $('td:eq(9)', nRow).html('Admin');
                }else{
                    $('td:eq(9)', nRow).html(aData[5]);
                }
                return nRow;
            },
            "aoColumns": [{"bVisible": false}, {"mRender": fld}, null, {"mRender": billing_status}, {"mRender": pay_status}, null, null, {"mRender": fsd}, {"mRender": fsd}, {"mRender": currencyFormat}, null, {"bVisible": false},{"bSortable": false}]
        });
    });
</script>