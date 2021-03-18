=<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="<?= $assets_ab ?>css/notification/notification.css">

<script>
    $(document).ready(function() {

        var oTable = $('#markupTable').dataTable({
            "aaSorting": [
                [1, "asc"]
            ],
            "aLengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "<?= lang('all') ?>"]
            ],
            "iDisplayLength": 10,
            'bProcessing': true,
            'bServerSide': true,
            'sAjaxSource': '<?= site_url('system_settings/get_markup/') ?>',
            'fnServerData': function(sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>",
                });
                URLtemp = sSource;
                arrayURL = URLtemp.split("/");
                // jika value filter form kosong
                for (var i = 0; i < arrayURL.length; i++) {
                    if (arrayURL[i] == 'get_markup') {
                        arrayURL[i + 1] = $("#warehouse_id").val();
                    }
                }
                URLtemp = arrayURL.join().replace(/,/g, "/");
                $.ajax({
                    'dataType': 'json',
                    'type': 'POST',
                    'url': URLtemp,
                    'data': aoData,
                    'success': fnCallback
                });

                $('#warehouse_id').change(function() {
                    $('#markupTable_processing').css('visibility', 'visible');
                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });
                    arrayURL = URLtemp.split("/");
                    for (var i = 0; i < arrayURL.length; i++) {
                        if (arrayURL[i] == 'get_markup') {
                            arrayURL[i + 1] = $(this).val();
                        }
                    }
                    URLtemp = arrayURL.join().replace(/,/g, "/");
                    $.ajax({
                        "type": "POST",
                        "dataType": 'json',
                        "url": URLtemp, //sending server side status and filtering table
                        "data": aoData,
                        "success": function(data) {
                            fnCallback(data);
                        }
                    });
                });
            },
            'fnRowCallback': function(nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                var avg_cost = aData[4];
                var db_markup = aData[5];
                if (db_markup == null) {
                    db_markup = '0';
                }
                if (db_markup.indexOf('%') > -1) {
                    var price = parseInt(((parseFloat(db_markup) / 100) * parseFloat(avg_cost)) + parseFloat(avg_cost));
                    var profit = parseInt((parseFloat(db_markup) / 100) * parseFloat(avg_cost));
                } else {
                    var price = parseInt(parseFloat(db_markup) + parseFloat(avg_cost));
                    var profit = parseInt(db_markup);
                }
                var markup = "<div class='text-center row'>";
                markup += "<input class='form-control markup_val' onchange='cek(this.value, " + aData[0] + ")' value='" + db_markup + "' ></div>";
                $('td:eq(5)', nRow).html(markup);
                $('td:eq(6)', nRow).html(currencyFormat(price));
                $('td:eq(7)', nRow).html(currencyFormat(profit));

                return nRow;
            },
            "aoColumns": [{
                "bSortable": false,
                "mRender": checkbox
            }, null, null, {
                "mRender": currencyFormat
            }, {
                "mRender": currencyFormat
            }, null, {
                "mRender": currencyFormat
            }, {
                "bSortable": false
            }]
        });

        $('#markup_all').on('keyup', function(e) {
            var regex = /[0-9%]/g;
            var key = String.fromCharCode(e.which);
            if (!regex.test(key)) {
                e.preventDefault();
            }
        });
        $('#markup_val').hide();
        $('#save_all').hide();
        $('input[name=markup_type]').on('ifChecked', function(event) {
            var markup_type = $(this).val();
            if (markup_type == 'all') {
                $('#mark_type').val(markup_type);
                $('#product_table').hide();
                $('#save_all').show();
                $('#markup_val').show();
                $('#markup_all').removeAttr('disabled');
            } else {
                $('#mark_type').val(markup_type);
                $('#product_table').show();
                $('#save_all').hide();
                $('#markup_val').hide();
                $('#markup_all').attr('disabled', 'disabled');
            }
        });
        $('button#save_all').on('click', function(e) {
            var markup = $('#markup_all').val();
            cek(markup);
        });
    });

    function cek(markup, id_wp = null) {
        var regex = new RegExp(/^[0-9%]/g);
        if (!regex.test(markup)) {
            notify('danger', 'Harus berupa angka');
        } else {
            var type = $('#mark_type').val();
            var wh_id = $('#warehouse_id').val();
            let csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
            let csrfHash = '<?= $this->security->get_csrf_hash(); ?>';
            var aoData = {
                type: type,
                id_wp: id_wp,
                wh_id: wh_id,
                markup: markup,
                [csrfName]: csrfHash
            };
            var myUrl = '<?= site_url('system_settings/update_markup/') ?>';
            $.ajax({
                "type": "POST",
                "dataType": 'json',
                "url": myUrl,
                "data": aoData,
                "success": function(res) {
                    notify(res.notif, res.message);
                    $('#markupTable').dataTable().fnDraw();
                }
            });
        }
    }
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-dollar"></i><?= $page_title ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a title="<?= lang('add_bonus') ?>" class="tip" href="<?php echo site_url('system_settings/add_bonus'); ?>" data-toggle="modal" data-target="#myModal" data-backdrop="static">
                        <i class="icon fa fa-plus"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo $this->lang->line("list_results"); ?></p>
                <div class="form-group">
                    <label for="awarehouse" class="col-sm-2 control-label"><?= lang('warehouse') ?></label>
                    <div class="col-sm-10">
                        <?php
                        $wh[''] = '';
                        foreach ($warehouses as $warehouse) {
                            $wh[$warehouse->id] = $warehouse->name;
                        }
                        echo form_dropdown('warehouse_id', $wh, (isset($_POST['warehouse_id']) ? $_POST['warehouse_id'] : $this->session->userdata('warehouse_id')), 'id="warehouse_id" class="form-control"');
                        ?>
                    </div><br><br>
                </div>
                <div class="form-group">
                    <label for="markup" class="col-sm-2 control-label">Mark Up type</label>
                    <div class="input-group col-md-8">
                        <div class="col-md-2">
                            <input type="radio" name="markup_type" value="each" checked="checked"> Each
                        </div>
                        <div class="col-md-3">
                            <input type="radio" name="markup_type" value="all"> All
                        </div>
                        <div id="markup_val">
                            <input hidden id="mark_type" value="each">
                            <div class="col-md-5">
                                <label for="markup_product" class="control-label">Mark Up :</label>
                                <input type="text" name="markup" class="form-control" id="markup_all">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-3">
                        <button id="save_all" class="btn btn-success">Save</button>
                    </div>
                </div>
                <div class="table-responsive" id="product_table">
                    <table id="markupTable" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check" />
                                </th>
                                <th><?= lang("code") ?></th>
                                <th><?= lang("name") ?></th>
                                <th><?= lang("cost") ?></th>
                                <th><?= lang("Avg_Cost") ?></th>
                                <th><?= lang("markup") ?></th>
                                <th><?= lang("price") ?></th>
                                <th><?= lang("profit") ?></th>
                                <!-- <th style="max-width:85px;"><?php echo $this->lang->line("actions"); ?></th> -->
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


<script>
    function notify(type, message, align, icon, from, animIn, animOut) {
        $.growl({
            icon: icon,
            title: '',
            message: message,
            url: ''
        }, {
            type: type,
            allow_dismiss: true,
            placement: {
                from: from,
                align: align
            },
            offset: {
                x: 20,
                y: 85
            },
            spacing: 10,
            z_index: 1031,
            delay: 1500,
            timer: 1500,
            mouse_over: false,
            animate: {
                enter: animIn,
                exit: animOut
            },
            icon_type: 'class'
        });
    };
</script>
<script src="<?= $assets_ab ?>js/notification/bootstrap-growl.min.js"></script>