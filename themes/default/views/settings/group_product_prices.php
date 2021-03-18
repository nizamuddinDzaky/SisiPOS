<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function() {
        var ti = 0;
        $(document).on('change', '.price', function() {
            var row = $(this).closest('tr');
            row.first('td').find('input[type="checkbox"]').iCheck('check');
        });
        $(document).on('click', '.form-submit', function() {
            var btn = $(this);
            btn.html('<i class="fa fa-circle-o-notch fa-spin fa-fw"></i>');
            var row = btn.closest('tr');
            var product_id = row.attr('id');
            var price = row.find('.price').val();
            var price_kredit = row.find('.price_kredit').val();
            var min_order = row.find('.min_order').val();
            var is_multiple = row.find('.is_multiple').is(":checked");
            // console.log(is_multiple);
            $.ajax({
                type: 'post',
                url: '<?= site_url('system_settings/update_product_group_price/' . $price_group->id); ?>',
                dataType: "json",
                data: {
                    <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>',
                    product_id: product_id,
                    price: price,
                    price_kredit: price_kredit,
                    min_order: min_order,
                    is_multiple: is_multiple
                },
                success: function(data) {
                    if (data.status != 1)
                        btn.removeClass('btn-primary').addClass('btn-danger').html('<i class="fa fa-times"></i>');
                    else
                        btn.removeClass('btn-primary').removeClass('btn-danger').addClass('btn-success').html('<i class="fa fa-check"></i>');
                },
                error: function(data) {
                    btn.removeClass('btn-primary').addClass('btn-danger').html('<i class="fa fa-times"></i>');
                }
            });
            // btn.html('<i class="fa fa-check"></i>');
        });

        function price_input(x) {
            ti = ti + 1;
            var v = x.split('__');
            return "<div class=\"text-center\" style='display:flex;'><input type=\"text\" name=\"price" + v[0] + "\" value=\"" + ((v[1] != '' && v[1] != 0) ? formatDecimals(v[1]) : '') + "\" class=\"form-control text-center price width-100\" tabindex=\"" + (ti) + "\" style=\"padding:2px;height:auto;\"><span class='unit-product-price'>" + v[2] + "</span></div>"; // onclick=\"this.select();\"
        }

        function price_kredit_input(x) {
            ti = ti + 1;
            var v = x.split('__');
            return "<div class=\"text-center\" style='display:flex;'><input type=\"text\" name=\"price_kredit" + v[0] + "\" value=\"" + ((v[1] != '' && v[1] != 0) ? formatDecimals(v[1]) : '') + "\" class=\"form-control text-center width-100 price_kredit\" tabindex=\"" + (ti) + "\" style=\"padding:2px;height:auto;\"><span class='unit-product-price'>" + v[2] + "</span></div>"; // onclick=\"this.select();\"
        }

        function min_order_input(x) {
            ti = ti + 1;
            var v = x.split('__');

            return "<div class=\"text-center\" style='display:flex;'><input type=\"text\" name=\"min_order" + v[0] + "\" value=\"" + ((v[1] != '' && v[1] != 0) ? formatDecimals(v[1]) : '') + "\" class=\"form-control text-center width-100 min_order\" tabindex=\"" + (ti) + "\" style=\"padding:2px;height:auto;\"><span class='unit-product-price'>" + v[2] + "</span></div>"; // onclick=\"this.select();\"
        }

        function is_multiple(x) {
            ti = ti + 1;
            var v = x;
            var val = 0;
            var checked = '';
            if (x == 1) {
                val = 1;
                checked = 'checked';
            }
            return "<div class=\"text-center\"><input type=\"checkbox\" name=\"is_multiple\" value=\"" + val + "\" class=\"checkbox multi-select is_multiple\" tabindex=\"" + (ti) + "\" style=\"padding:2px;height:auto;\" " + checked + "></div>";
        }


        $('#CGData').dataTable({
            "aaSorting": [
                [1, "asc"]
            ],
            "aLengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "<?= lang('all') ?>"]
            ],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true,
            'bServerSide': true,
            'sAjaxSource': '<?= site_url('system_settings/getProductPrices/' . $price_group->id) ?>',
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
                nRow.id = aData[0];
                nRow.className = "product_group_price_id";
                return nRow;
            },
            "aoColumns": [{
                "bSortable": false,
                "mRender": checkbox
            }, null, null, {
                "bSortable": false,
                "mRender": price_input
            }, {
                "bSortable": false,
                "mRender": price_kredit_input
            }, {
                "bSortable": false,
                "mRender": min_order_input
            }, {
                "bSortable": false,
                "mRender": is_multiple
            }, {
                "bVisible": false
            }, {
                "bVisible": false
            }, {
                "bSortable": false
            }]
        }).fnSetFilteringDelay();
    });
</script>
<?= form_open('system_settings/product_group_price_actions/' . $price_group->id, 'id="action-form"') ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-barcode"></i><?= $page_title ?> (<?= $price_group->name; ?>)</h2>

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
                            <a href="<?php echo site_url('system_settings/update_prices_csv/' . $price_group->id); ?>" data-toggle="modal" data-target="#myModal" data-backdrop="static">
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
                                    <input class="checkbox checkth" type="checkbox" name="check" />
                                </th>
                                <th class="col-xs-3"><?= lang("product_code"); ?></th>
                                <th class="col-xs-4"><?= lang("product_name"); ?></th>
                                <th><?= lang("price"); ?></th>
                                <th><?= lang("price_kredit"); ?></th>
                                <th><?= lang("min_order"); ?></th>
                                <th><?= lang("multiple"); ?></th>
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
    <input type="hidden" name="form_action" value="" id="form_action" />
    <?= form_submit('submit', 'submit', 'id="action-form-submit"') ?>
</div>
<?= form_close() ?>
<script language="javascript">
    $(document).ready(function() {

        $('#delete').click(function(e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#excel').click(function(e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#pdf').click(function(e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#update_price').click(function(e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

    });
</script>