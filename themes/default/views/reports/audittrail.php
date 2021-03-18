<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-shopping-cart"></i><?= $page_title ?> <?= (isset($_POST['start_date']) ? $_POST['start_date'] : $start_date) ?> <?= (isset($_POST['end_date']) && $_POST['end_date'] != "" ? $_POST['end_date'] : $end_date) ?></h2>
    </div>
    <?php echo form_open("reports/sale_delivered"); ?>
    <div class="box-content">
        <div class="row">
            <div class="col-md-6">
                <div class="form">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon1"><i class="fal fa-money-check-alt text-primary"></i></span>
                    </div>
                    <select id="dropdown-type-audittrail" class="form-control" name="type_audittrail" required>
                        <?php foreach ($dataTypeAudittrail as $kyetypeAudittrail => $typeAudittrail) { ?>
                            <option value="<?= $kyetypeAudittrail ?>"><?= $typeAudittrail ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="input-group mb-3 col-md-6">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon1"><i class="fal fa-money-check-alt text-primary"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="response">
</div>
<script>
    $('#dropdown-type-audittrail').change(function() {

        $.ajax({
            type: "POST",
            url: "<?= base_url("reports/load_view_auditrail") ?>",
            data: {
                page: $(this).val(),
            },
            success: function(data) {
                console.log(data);
                // alert('asdsa');
                $("#response").html(data.output);
            },
            error: function(xhr, error) {
                console.debug(xhr);
                console.debug(error);
            },
            dataType: "json"
        });
    }).change();
    $('#xls').click(function(event) {

        event.preventDefault();
        // console.log(asd);
        <?php if ($v == null) : ?>
            window.location.href = "<?= site_url('reports/get_sale_delivered/') ?>" + "?form_action=export_excel";
        <?php else : ?>
            window.location.href = "<?= site_url('reports/get_sale_delivered/') ?>" + "?form_action=export_excel<?= $v ?>";
        <?php endif; ?>
        return false;
    });
</script>