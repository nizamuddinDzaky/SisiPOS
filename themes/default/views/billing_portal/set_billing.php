<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<!-- Form Examples area start-->
<div id="loader" style="height: 100%;left: 0;position: fixed; top: 0; width: 100%; z-index: 999999; background: rgba(0, 0, 0, 0.5);">
    <div  class="dots-loader" style="margin-left: 50%; margin-top: 25%;"></div>
</div>

<div class="form-example-area">
    <div class="container">
        <div class="row">
            <form id="myFormSet" data-action="<?= site_url('billing_portal/subscription/set_billing_add')?>">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="form-example-wrap">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <h3><?= lang("set_billing") ?></h3>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12" style="text-align: right;">
                            <div class="summernote-clickable">
                                <a class="btn btn-primary btn-sm hec-button import" >Import Billing</a>
                            </div>
                        </div>
                    </div><hr>

                    <div class="data-table-list" style="box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2); border-radius: 5px; padding-top: 20px; padding-bottom: 20px;">
                        <div class="row" >
                            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12" >
                                <div class="nk-int-mk sl-dp-mn sm-res-mg-t-10">
                                    <h2>Plan</h2>
                                </div>
                                <div class="bootstrap-select fm-cmp-mg">
                                    <select class="selectpicker" data-live-search="true" name="plan_id" id="plan_id">
                                        <?php foreach ($plan as $k => $v) {
                                        echo '<option value="'.$v->id.'"> '.$v->name.'</option>';
                                        } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12 row_user" >
                                <div class="nk-int-mk sl-dp-mn sm-res-mg-t-10">
                                    <h2>Additional User</h2>
                                </div>
                                <div class="form-group nk-datapk-ctm form-elet-mg ">
                                    <div class="input-group nk-int-st">
                                        <span class="input-group-addon"></span>
                                        <input type="number" name="user" class="form-control nk-datapk-ctm text-center number-only" placeholder="User" >
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12 row_warehouse" >
                                <div class="nk-int-mk sl-dp-mn sm-res-mg-t-10">
                                    <h2>Additional Warehouse</h2>
                                </div>
                                <div class="form-group nk-datapk-ctm form-elet-mg">
                                    <div class="input-group  nk-int-st">
                                        <span class="input-group-addon"></span>
                                        <input type="number" name="warehouse" class="form-control nk-datapk-ctm text-center number-only" placeholder="Warehouse" >
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-2">
                                <div class="form-example-int form-example-st">
                                    <div class="fm-checkbox">
                                        <h4><input type="checkbox" class="i-checks" value="send_email" name="send_email"> &nbsp; Send Email</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br><br>
                        <div class="row row_basic" >
                            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12 row_start" >
                                <div class="nk-int-mk sl-dp-mn sm-res-mg-t-10">
                                    <h2>Start Date</h2>
                                </div>
                                <div class="form-group nk-datapk-ctm form-elet-mg datepicker">
                                    <div class="form-ic-cmp">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                    <div class="input-group date nk-int-st">
                                        <span class="input-group-addon"></span>
                                        <input type="text" name="start_date" id="start_date" class="form-control nk-datapk-ctm text-center" placeholder="Start Date" >
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12 row_period" >
                                <div class="nk-int-mk sl-dp-mn sm-res-mg-t-10">
                                    <h2>Payment Period</h2>
                                </div>
                                <div class="bootstrap-select fm-cmp-mg">
                                    <select class="selectpicker" data-live-search="true" name="payment" id="payment">
                                        <option value="1"> Monthly </option>
                                        <option value="6"> Per 6 Month </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12 row_end" >
                                <div class="nk-int-mk sl-dp-mn sm-res-mg-t-10">
                                    <h2>End Date</h2>
                                </div>
                                <div class="form-group nk-datapk-ctm form-elet-mg">
                                    <div class="input-group date nk-int-st">
                                        <span class="input-group-addon"></span>
                                        <input type="text" name="end_date" id="end_date" class="form-control nk-datapk-ctm text-center" placeholder="End Date" value="aaaa" readonly="">
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-2 row_payment">
                                <div class="form-example-int form-example-st">
                                    <div class="fm-checkbox">
                                        <h4><input type="checkbox" class="i-checks" value="payment_done" name="payment_done"> &nbsp; Payment (done)</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div><br>
                    </div>
                    <br><br>
                    <div class="row">
                        <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12" >
                            <div class="data-table-list"  style="box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2); border-radius: 5px; padding-top: 20px; padding-bottom: 20px;">
                                <div class="basic-tb-hd">
                                    <div class="row">
                                        <div class="col-md-9"><h2>Distributor</h2></div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="myTable" class="table ">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Kode</th>
                                                <th>Company</th>
                                                <th>Email</th>
                                                <th>Created at</th>
                                                <th>Start Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tfoot class="dtFilter">
                                            <tr class="active">
                                                <th>No</th>
                                                <th>Kode</th>
                                                <th>Company</th>
                                                <th>Email</th>
                                                <th>Created at</th>
                                                <th>Start Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5 col-md-5 col-sm-5 col-xs-12" >

                            <div class="data-table-list"  style="box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2); border-radius: 5px; padding-top: 20px; padding-bottom: 20px;">
                                <div class="basic-tb-hd">
                                    <div class="row">
                                        <div class="col-md-9"><h2>Distributor Terpilih</h2></div>
                                        <div class="col-md-3" ><a class="btn btn-danger btn-sm hec-button del_all" >Delete All</a></div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="myTable2" class="table ">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Company</th>
                                                <th>Email</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tfoot class="dtFilter">
                                            <tr class="active">
                                                <th>No</th>
                                                <th>Company</th>
                                                <th>Email</th>
                                                <th>Action</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br><hr>
                    <div class="form-example-int mg-t-15">
                        <div class="row">
                            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12"></div>
                            <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12" style="float: right;">
                                <button id="submit_button" type="submit" class="btn btn-success notika-btn-success">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </form>
        </div>
        
    </div>
</div>
<!-- Form Examples area End-->

<div class="modal fade import_modal" role="dialog" data-backdrop="static">
    <div class="modal-dialog modals-default">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <?php  $attribute = array('data-toggle' => 'validator', 'role' => 'form');
                echo form_open_multipart("billing_portal/subscription/import_billing/", $attribute);
            ?>
            <div class="modal-body">
                <center><a class="btn btn-primary btn-sm hec-button" style="box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2); border-radius: 5px;" href="<?= site_url('billing_portal/subscription/export_company_author') ?>">Get Sample File</a><br><br></center>
                <h2>Upload File</h2>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <input id="import_file" type="file" data-browse-label="<?= lang('browse'); ?>" name="import_file" data-show-upload="false" data-show-preview="false" class="form-control file"  required>
                            <span id="InfoImageBrand"><i style="color:red;"><sup><strong>* File Type : </strong>(.xlsx / .xls)</sup></i></span> 
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" id="import_button" class="btn btn-default">Upload</button>
                <button type="button" class="btn" data-dismiss="modal">Cancel</button>
            </div>
            <?php  echo form_close(); ?>
        </div>
    </div>
</div>

<div class="modal fade del_prev_modal" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            
            <div class="modal-body">
                <h2>Delete All</h2>
                <p>Are You Sure ?</p>
            </div>
            <div class="modal-footer">
                <button type="submit" id="del_prev_button" class="btn btn-default">Yes</button>
                <button type="button" class="btn" data-dismiss="modal">Cancel</button>
            </div>
            <?php  echo form_close(); ?>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#loader').hide();

        let oTable = $('#myTable').dataTable({
            "aaSorting": [
                [2, "desc"]
            ],
            "aLengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "<?= lang('all') ?>"]
            ],
            "iDisplayLength": 10,
            'bProcessing': true,
            'bServerSide': true,
            'sAjaxSource': '<?= site_url('billing_portal/subscription/getBillerAktif') ?>',
            'fnServerData': function(sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({
                    'dataType': 'json',
                    'type': 'POST',
                    'url': sSource,
                    'data': aoData,
                    'success': fnCallback
                });
            },
            'fnRowCallback': function(nRow, aData, iDisplayIndex) {
                act_add =   "<div class='text-center row'>";
                act_add +=  "<a onclick='add_set_button("+aData[0]+")' class='tip btn btn-info notika-btn-primary btn-xs' title='Add to Bill'><i class='fa fa-plus'></i></a>";

                $('td:eq(5)', nRow).html(act_add);
                return nRow;
            },
            "fnCreatedRow": function (row, data, index) {
                //$('td', row).eq(0).html(index + 1);
            },
            "aoColumns": [{"bVisible": false}, null, null, null, {"mRender": fldd}, {"mRender": fldd}, {"bSearchable": false, "bSortable": false}]
        });

        let oTable2 = $('#myTable2').dataTable({
            "aaSorting": [
                [1, "desc"]
            ],
            "aLengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "<?= lang('all') ?>"]
            ],
            "iDisplayLength": 10,
            'bProcessing': true,
            'bServerSide': true,
            'sAjaxSource': '<?= site_url('billing_portal/subscription/getSetCompany') ?>',
            'fnServerData': function(sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({
                    'dataType': 'json',
                    'type': 'POST',
                    'url': sSource,
                    'data': aoData,
                    'success': fnCallback
                });
            },
            'fnRowCallback': function(nRow, aData, iDisplayIndex) {
                act_del =   "<div class='text-center row'>";
                act_del +=  "<a onclick='del_set_button("+aData[0]+")' class='tip btn btn-danger notika-btn-primary btn-xs' title='Delete company'><i class='fa fa-trash'></i></a>";
                if(aData[2] == null){
                    $('td:eq(1)', nRow).html('null');
                }
                $('td:eq(2)', nRow).html(act_del);
                return nRow;
            },
            "fnCreatedRow": function (row, data, index) {
                // $('td', row).eq(0).html(index + 1);
            },
            "aoColumns": [{"bVisible": false}, null, null, {"bSearchable": false, "bSortable": false}]
        });
    });

    function add_set_button(id) {
        let csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
        let csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';
        let mydata = {id:id, [csrfName]:csrfHash};
        let action = site.base_url +"billing_portal/subscription/set_distributor";

        $.ajax({
            type: "POST",
            dataType: 'json',
            data : mydata,
            url: action,
            success: function (res) {
                notify(res.notif, res.message);
                reload_table('#myTable2');
            }
        });
        return false;
    };

    function del_set_button(id) {
        let csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
        let csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';
        let mydata = {id:id, [csrfName]:csrfHash};
        let action = site.base_url +"billing_portal/subscription/delete_distributor";

        $.ajax({
            type: "POST",
            dataType: 'json',
            data : mydata,
            url: action,
            success: function (res) {
                notify(res.notif, res.message);
                reload_table('#myTable2');
            }
        });
        return false;
    };

    $('#myFormSet').off('submit').on('submit', function(){
        let start = $('#start_date').val();
        let plan_id = $('#plan_id').val();
        if(plan_id != 1 && start == ''){
            notify('danger', 'Start Date Harus terisi');
        }
        else{
            $('#submit_button').attr('disabled','disabled');
            var action = $('#myFormSet').attr("data-action");
            var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
            var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';
            var myData = $('#myFormSet').serialize();
            $.ajax({
                type: "POST",
                data : myData + '&'+[csrfName]+'='+csrfHash,
                dataType: 'json',
                url: action,
                beforeSend: function () {
                    $('#loader').show();
                },
                success: function (res) {
                    $('#loader').hide();
                    notify(res.notif, res.message);
                    if(res.to_link && res.to_link != ''){
                        href_page(res.to_link);
                    }else{
                        reload_page();
                    }
                },
                error: function(xhr, status, error) { 
                    $('#loader').hide();
                    notify('danger', error);
                    // reload_page();
                } 
            }).fail(function (xhr, status, error) {
                $('#loader').hide();
                notify('danger', error);
                // reload_page();
            });
        }
        return false;
    });

    $(".import").click(function (e) {
        e.preventDefault();
        let param = 'import';
        $('.'+param+'_modal').modal('show'); 
        $('#'+param+'_button').off('click').click(function(){ 
            $('#'+param+'_button').attr('disabled',false);
            $('#loader').show();
            // $('.'+param+'_modal').modal('hide'); 
        });
    });

    $(".del_all").click(function (e) {
        e.preventDefault();
        let param = 'del_prev';
        $('.'+param+'_modal').modal('show'); 
        $('#'+param+'_button').off('click').click(function(){ 
            let action = site.base_url +"billing_portal/subscription/delete_distributor";
            $.ajax({
                type: "GET",
                dataType: 'json',
                url: action,
                success: function (res) {
                    $('.'+param+'_modal').modal('hide'); 
                    notify(res.notif, res.message);
                    reload_table('#myTable2');
                }
            });
            return false;
        });
    });

    $('.row_end').hide();
    $('#plan_id').on('change', function(){
        let plan_id = $('#plan_id').val();
        if(plan_id != 1){
            $('.row_user').show();
            $('.row_warehouse').show();
            $('.row_basic').show();
        }
        else{
            $('.row_user').hide();
            $('.row_warehouse').hide();
            $('.row_basic').hide();
        }
    });

    $('#payment').on('change', function(){
        let payment = $('#payment').val();
        let start_date = $('#start_date').val();
        if(start_date == ''){
            notify('danger', 'Start Date Harus diisi');
        }
        else{
            let action = site.base_url +"billing_portal/subscription/get_endDate_byPeriod/";
            $.ajax({
                type: "POST",
                data : {start_date:start_date, payment:payment},
                url: action,
                success: function (res) {
                    $('#end_date').val(res);
                }
            });
            $('.row_end').show();
            return false;
        }
    });

    $('input.number-only').bind('keypress', function (e) {
        return !(e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57) && e.which != 46);
    });
</script>