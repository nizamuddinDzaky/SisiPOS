
<script>
    $(document).ready(function () {
        $('#SLData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('system_settings/get_payment_method/') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                url_=sSource;

                

                $.ajax({'dataType': 'json', 'type': 'POST', 'url': url_, 'data': aoData, 'success': fnCallback});

                
            },
            
            "aoColumns": [null, null, null,{"bVisible": false}, {"bSortable": false}],
        });

    });

</script>

<?php echo form_open("reports/audittrail_customer_approve_reject_price_action"); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-shopping-cart"></i><?= $page_title ?></h2>

    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table id="SLData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th><?= lang('username') ?></th>
                                <th><?= lang("name") ?></th>
                                <th><?= lang("distributor") ?></th>
                                <th><?= lang("distributor") ?></th>
                                <th><?= lang("actions"); ?></th>
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
<div style="display: none;">
    <input type="hidden" name="form_action" value="" id="form_action"/>
    <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
</div>
<?= form_close() ?>