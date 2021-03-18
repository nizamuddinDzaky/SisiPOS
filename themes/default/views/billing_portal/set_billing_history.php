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
                            <h3><?= lang("set_billing_history") ?></h3>
                        </div>
                    </div><hr>

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
                                            <th><?php echo $this->lang->line("company"); ?></th>
                                            <th><?php echo $this->lang->line("plan_id"); ?></th>
                                            <th><?php echo $this->lang->line("plan"); ?></th>
                                            <th><?php echo $this->lang->line("start_date"); ?></th>
                                            <th><?php echo $this->lang->line("expired_date"); ?></th>
                                            <th><?php echo $this->lang->line("reference_no"); ?></th>
                                            <th><?php echo $this->lang->line("billing_status"); ?></th>
                                            <th><?php echo $this->lang->line("payment_status"); ?></th>
                                            <th><?php echo $this->lang->line("grand_total"); ?></th>
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

<div class="modal fade detail_modal" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-large">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <center>
                    <h2 id="company_payment"></h2>
                    <h2 id="ref_payment"></h2>
                </center>
                <div class="row" >
                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12" style="margin-bottom: 40px;">
                        <div class="nk-int-mk sl-dp-mn sm-res-mg-t-10">
                            <h2>Company</h2>
                        </div>
                        <div class="form-group nk-datapk-ctm form-elet-mg ">
                            <div class="input-group nk-int-st">
                                <span class="input-group-addon"></span>
                                <input type="text" id="company_name" class="form-control nk-datapk-ctm text-center" readonly="" >
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12"  style="margin-bottom: 40px;">
                        <div class="nk-int-mk sl-dp-mn sm-res-mg-t-10">
                            <h2>Plan</h2>
                        </div>
                        <div class="form-group nk-datapk-ctm form-elet-mg ">
                            <div class="input-group nk-int-st">
                                <span class="input-group-addon"></span>
                                <input type="text" id="plan_name" class="form-control nk-datapk-ctm text-center" readonly=""  >
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12"  style="margin-bottom: 40px;">
                        <div class="nk-int-mk sl-dp-mn sm-res-mg-t-10">
                            <h2>Grand Total</h2>
                        </div>
                        <div class="form-group nk-datapk-ctm form-elet-mg ">
                            <div class="input-group nk-int-st">
                                <span class="input-group-addon"></span>
                                <input type="text" id="total" class="form-control nk-datapk-ctm text-center" readonly=""  >
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12"  style="margin-bottom: 40px;">
                        <div class="nk-int-mk sl-dp-mn sm-res-mg-t-10">
                            <h2>Reff No</h2>
                        </div>
                        <div class="form-group nk-datapk-ctm form-elet-mg ">
                            <div class="input-group nk-int-st">
                                <span class="input-group-addon"></span>
                                <input type="text" id="reference_no" class="form-control nk-datapk-ctm text-center" readonly=""  >
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12"  style="margin-bottom: 40px;">
                        <div class="nk-int-mk sl-dp-mn sm-res-mg-t-10">
                            <h2>Billing Status</h2>
                        </div>
                        <div class="form-group nk-datapk-ctm form-elet-mg ">
                            <div class="input-group nk-int-st">
                                <span class="input-group-addon"></span>
                                <input type="text" id="billing_status" class="form-control nk-datapk-ctm text-center" readonly=""  >
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12"  style="margin-bottom: 40px;">
                        <div class="nk-int-mk sl-dp-mn sm-res-mg-t-10">
                            <h2>Payment Status</h2>
                        </div>
                        <div class="form-group nk-datapk-ctm form-elet-mg ">
                            <div class="input-group nk-int-st">
                                <span class="input-group-addon"></span>
                                <input type="text" id="payment_status" class="form-control nk-datapk-ctm text-center" readonly=""  >
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 row_user"  style="margin-bottom: 40px;">
                        <div class="nk-int-mk sl-dp-mn sm-res-mg-t-10">
                            <h2>Additional User</h2>
                        </div>
                        <div class="form-group nk-datapk-ctm form-elet-mg ">
                            <div class="input-group nk-int-st">
                                <span class="input-group-addon"></span>
                                <input type="text" id="additional_user" class="form-control nk-datapk-ctm text-center" readonly=""  >
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 row_warehouse"  style="margin-bottom: 40px;">
                        <div class="nk-int-mk sl-dp-mn sm-res-mg-t-10">
                            <h2>Additional Warehouse</h2>
                        </div>
                        <div class="form-group nk-datapk-ctm form-elet-mg ">
                            <div class="input-group nk-int-st">
                                <span class="input-group-addon"></span>
                                <input type="text" id="additional_warehouse" class="form-control nk-datapk-ctm text-center" readonly=""  >
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12"  style="margin-bottom: 40px;">
                        <div class="nk-int-mk sl-dp-mn sm-res-mg-t-10">
                            <h2>Payment Period</h2>
                        </div>
                        <div class="form-group nk-datapk-ctm form-elet-mg ">
                            <div class="input-group nk-int-st">
                                <span class="input-group-addon"></span>
                                <input type="text" id="payment_period" class="form-control nk-datapk-ctm text-center" readonly=""  >
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('#myTable').dataTable({
            "aaSorting": [[0, "desc"], [8, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": 10,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('billing_portal/subscription/get_billing_history') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function(nRow, aData, iDisplayIndex) {
                let url_detail = site.base_url +"billing_portal/subscription/get_billing_history_detail";

                act_detail =   "<div class='text-center row'>";
                act_detail +=  "<a onclick='detail_button("+aData[0]+")' id='detail_id_"+aData[0]+"' data-action='"+url_detail+"'  class='tip btn btn-info notika-btn-primary btn-sm' title='Detail'><i class='fa fa-arrow-right'></i></a></div>";

                if(aData[3] == 1){
                        $('td:eq(6)', nRow).html('');
                        $('td:eq(7)', nRow).html('');
                }
                $('td:eq(9)', nRow).html(act_detail);

                return nRow;
            },
            "aoColumns": [{"bVisible": false}, {"mRender": fld}, null, {"bVisible": false}, null, {"mRender": fsd}, {"mRender": fsd}, null, {"mRender": billing_status}, {"mRender": pay_status}, {"mRender": currencyFormat}, {"bSortable": false}]
        });
    });

    function detail_button(id) {
        let param = 'detail';
        var action = $('#'+param+'_id_'+id).attr('data-action');
        $('.'+param+'_modal').modal('show'); 
        $.ajax({
            type: "GET",
            dataType: 'json',
            url: action,
            data: {id:id},
            success: function (res) {
                $.each(res, function () {
                    $('#company_name').val(this.company_name)
                    $('#plan_name').val(this.plan_name)
                    $('#reference_no').val(this.reference_no)
                    $('#billing_status').val(this.billing_status)
                    $('#payment_status').val(this.payment_status)
                    $('#total').val(formatMoney(this.total))
                    $('#additional_user').val(this.additional_user)
                    $('#additional_warehouse').val(this.additional_warehouse)
                    $('#payment_period').val(this.payment_period)
                });
            }
        });
        return false;
    };

</script>