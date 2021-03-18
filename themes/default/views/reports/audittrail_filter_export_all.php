
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('filter_export_audittrail'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'add-customer-form');
        echo form_open_multipart("reports/audittrail_export_all", $attrib); ?>
        <input id="latitude" name="latitude" type="hidden"/>
        <input id="longitude" name="longitude" type="hidden"/>
        <div class="modal-body">
            <div class="row">
                <div class="col-lg-12">

                    <div class="form-group">

                        <div class="col-sm-6">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : date('d/m/Y')), 'class="form-control date" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : date('d/m/Y')), 'class="form-control date" id="end_date"'); ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>


        </div>
        <div class="modal-footer">
            <?php echo form_submit('excel', lang('export'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>