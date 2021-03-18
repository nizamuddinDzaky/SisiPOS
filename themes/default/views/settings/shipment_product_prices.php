<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        var ti = 0;
        $(document).on('change', '.price_pickup', function () {
            var row = $(this).closest('tr');
            row.first('td').find('input[type="checkbox"]').iCheck('check');
        });
        $(document).on('change', '.price_delivery', function () {
            var row = $(this).closest('tr');
            row.first('td').find('input[type="checkbox"]').iCheck('check');
        });
        $(document).on('click', '.form-submit', function () {
            var btn = $(this);
            btn.html('<i class="fa fa-circle-o-notch fa-spin fa-fw"></i>');
            var row = btn.closest('tr');
            var product_id = row.attr('id');
            var price_pickup = row.find('.price_pickup').val();
            var price_delivery = row.find('.price_delivery').val();
            var min_order = row.find('.min_order').val();
            var is_multiple = row.find('.is_multiple').is(":checked");
            // console.log(is_multiple);
            $.ajax({
                type: 'post',
                url: '<?= site_url('system_settings/update_shipment_product_price/'.$price_group->id); ?>',
                dataType: "json",
                data: {
                    <?= $this->security->get_csrf_token_name() ?> : '<?= $this->security->get_csrf_hash() ?>',
                    product_id: product_id,
                    price_pickup: price_pickup,
                    price_delivery:price_delivery
                },
                success: function (data) {
                    if (data.status != 1)
                        btn.removeClass('btn-primary').addClass('btn-danger').html('<i class="fa fa-times"></i>');
                    else
                        btn.removeClass('btn-primary').removeClass('btn-danger').addClass('btn-success').html('<i class="fa fa-check"></i>');
                },
                error: function (data) {
                    btn.removeClass('btn-primary').addClass('btn-danger').html('<i class="fa fa-times"></i>');
                }
            });
            // btn.html('<i class="fa fa-check"></i>');
        });
        $(document).on('change', '.input-price', function(){
            price = parseInt($(this).data('price'));
            if(isNaN($(this).val())){
                $(this).val('');
            }
            if($(this).val() < (price*-1))
                $(this).val('');
            if(!isInt($(this).val())){
                $(this).val('');
            }
            
        });
        function price_pickup_input(x) {
            ti = ti+1;
            var v = x.split('__');
            return "<div class=\"text-center\" style='display:flex;'><input data-price=\""+v[3]+"\" type=\"text\" name=\"price_pickup"+v[0]+"\" value=\""+((v[1] != '' && v[1] != 0) ? formatDecimals(v[1]) : '')+"\" class=\"form-control text-center price_pickup input-price width-100\" tabindex=\""+(ti)+"\" style=\"padding:2px;height:auto;\"><span class='unit-product-price'>"+v[2]+"</span></div>"; // onclick=\"this.select();\"
        }
        function price_delivery_input(x) {
            ti = ti+1;
            var v = x.split('__');
            return "<div class=\"text-center\" style='display:flex;'><input data-price=\""+v[3]+"\" type=\"text\" name=\"price_delivery"+v[0]+"\" value=\""+((v[1] != '' && v[1] != 0) ? formatDecimals(v[1]) : '')+"\" class=\"form-control text-center price_delivery input-price width-100\" tabindex=\""+(ti)+"\" style=\"padding:2px;height:auto;\"><span class='unit-product-price'>"+v[2]+"</span></div>"; // onclick=\"this.select();\"
        }
        $('#CGData').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('system_settings/get_shipment_product_prices/'.$price_group->id) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[0];
                nRow.className = "product_group_price_id";
                return nRow;
            },
            "aoColumns": [{"bSortable": false, "mRender": checkbox}, null, null, {"bSortable": false, "mRender": price_pickup_input},{"bSortable": false, "mRender": price_delivery_input},{"bVisible": false},{"bVisible": false},{"bVisible": false}, {"bSortable": false}]
        }).fnSetFilteringDelay();
    });
</script>
<?= form_open('system_settings/shipment_product_prices_actions/'.$price_group->id, 'id="action-form"') ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-building"></i><?= $page_title ?> (<?= $price_group->name; ?>)</h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="#" id="update_price" data-action="update_price">
                                <i class="fa fa-dollar"></i> <?= lang('update_price') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo site_url('system_settings/update_prices_csv/'.$price_group->id); ?>" data-toggle="modal" data-target="#myModal"  data-backdrop="static">
                                <i class="fa fa-upload"></i> <?= lang('update_prices_csv') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="pdf" data-action="export_pdf">
                                <i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <!-- <li>
                            <a href="#" id="delete" data-action="delete">
                                <i class="fa fa-trash-o"></i> <?= lang('delete_product_group_prices') ?>
                            </a>
                        </li> -->
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang("list_results"); ?></p>

                <div class="table-responsive">
                    <table id="CGData" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
                            <th class="col-xs-3"><?= lang("product_code"); ?></th>
                            <th class="col-xs-4"><?= lang("product_name"); ?></th>
                            <th><?= lang("price_pickup"); ?></th>
                            <th><?= lang("price_delivery"); ?></th>
                            <th style="width:85px;"><?= lang("update"); ?></th>
                            <th style="width:85px;"><?= lang("update"); ?></th>
                            <th style="width:85px;"><?= lang("update"); ?></th>
                            <th style="width:85px;"><?= lang("update"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
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
    <?= form_submit('submit', 'submit', 'id="action-form-submit"') ?>
</div>
<?= form_close() ?>
<script language="javascript">
    $(document).ready(function () {

        $('#delete').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#excel').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#pdf').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#update_price').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

    });
</script>

