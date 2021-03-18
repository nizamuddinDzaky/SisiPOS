<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $url_API="https://maps.googleapis.com/maps/api/";?>

<!-- <script async defer src="<?php echo $url_API;?>js?callback=initMap&key=AIzaSyBP3-7OMYP0ZpTuc2Rj5m184oFwqprDC2o" type="text/javascript"></script>
<script>
var markersArray = [];
var destinationIcon = 'https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld=D|FF0000|000000';
var originIcon = 'https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld=O|FFFF00|000000';

function initMap() {
    bounds = new google.maps.LatLngBounds();
    var opts = {
        center: new google.maps.LatLng(-6.175392,106.827153),
        zoom: 8
    };
    map = new google.maps.Map(document.getElementById('map-canvas'),opts);
    geocoder = new google.maps.Geocoder();
}

function calculateDistances() {
    origin = '<?php echo $user->address.', '.$user->city?>';
    destination = document.getElementById('destination').value;
    var service = new google.maps.DistanceMatrixService();
    service.getDistanceMatrix(
    {
        origins: [origin],
        destinations: [destination],
        travelMode: google.maps.TravelMode.DRIVING,
        unitSystem: google.maps.UnitSystem.METRIC,
        avoidHighways: false,
        avoidTolls: false
    }, calcDistance);
}

function calcDistance(response, status) {
    if (status != google.maps.DistanceMatrixStatus.OK) {
        alert('Error was: ' + status);
    } else {
        var origins = response.originAddresses;
        var destinations = response.destinationAddresses;
        deleteOverlays();
        
        for (var i = 0; i < origins.length; i++) {
            var results = response.rows[i].elements;
            addMarker(origins[i], false);
            for (var j = 0; j < results.length; j++) {
                addMarker(destinations[j], true);
                spacing=results[j].distance.text.split(" ");
                getShippingCost(parseInt(spacing[0]));
            }
        }
    }
}
function addMarker(location, isDestination) {
    var icon;
    if (isDestination) {
        icon = destinationIcon;
    } else {
        icon = originIcon;
    }
    
    geocoder.geocode({'address': location}, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            bounds.extend(results[0].geometry.location);
            map.fitBounds(bounds);
            var marker = new google.maps.Marker({
                map: map,
                position: results[0].geometry.location,
                icon: icon
            });
            markersArray.push(marker);
        } else {
      alert('Geocode was not successful for the following reason: '
        + status);
        }
    });
}

function deleteOverlays() {
    for (var i = 0; i < markersArray.length; i++) {
        markersArray[i].setMap(null);
    }
    markersArray = [];
}
</script> -->

<script>
    var old_sent;
     $(document).on("focus", '.sent', function () {
        old_sent = $(this).val();
    }).on("change", '.sent', function () {
        var new_sent = $(this).val() ? $(this).val() : 0;
        if (!is_numeric(new_sent)) {
            $(this).val(old_sent);
            return;
        } else if (new_sent > $(this).data('remaining')){
            $(this).val($(this).data('remaining'));
            return;
        } else if(new_sent < 0){
            $(this).val(0);
            return;
        }
    });
</script>

<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_delivery'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form','id' =>'form_delivery');
        echo form_open_multipart("sales/add_delivery/".$inv->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="row">
                <div class="col-md-6">
                    <?php if ($Owner || $Admin) { ?>
                        <div class="form-group">
                            <?= lang("date", "date"); ?>
                            <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control datetime" id="date" required="required"'); ?>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <?= lang("do_reference_no", "do_reference_no"); ?>
                        <?= form_input('do_reference_no', (isset($_POST['do_reference_no']) ? $_POST['do_reference_no'] : $do_reference_no), 'class="form-control tip" id="do_reference_no"'); ?>
                    </div>

                    <div class="form-group">
                        <?= lang("sale_reference_no", "sale_reference_no"); ?>
                        <?= form_input('sale_reference_no', (isset($_POST['sale_reference_no']) ? $_POST['sale_reference_no'] : $inv->reference_no), 'class="form-control tip" id="sale_reference_no" required="required"'); ?>
                    </div>
                    <input type="hidden" value="<?php echo $inv->id; ?>" name="sale_id"/>

                    <div class="form-group">
                        <?= lang("customer", "customer"); ?>
                        <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : $inv->customer), 'class="form-control" id="customer" required="required" '); ?>
                    </div>
                    
                    <div class="form-group">
                        <?= lang("address", "address"); ?>
                        <?php
                            $address = $order_atl ? $order_atl->alamat_detail : ($customer->address . " " . $customer->city . " " . $customer->state . " " . $customer->postal_code . " " . $customer->country . "<br>Tel: " . $customer->phone . " Email: " . $customer->email);
                        ?>
                        <?php echo form_textarea('address', (isset($_POST['address']) ? $_POST['address'] : $address), 'class="form-control" id="address" required="required"'); ?>
                    </div>
                    
                    <!-- <div class="form-group">
                        <div id="map-canvas" style="height:20em;width:100%"></div>
                    </div> -->
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang('status', 'status'); ?>
                        <?php
                            if($sale_type == 'booking' && ($inv->client_id == 'aksestoko' || $inv->client_id == 'atl')) {
                                $opts = array('packing' => lang('packing'), 'delivering' => lang('delivering'));
                            }
                            else{
                                $opts = array('packing' => lang('packing'), 'delivering' => lang('delivering'), 'delivered' => lang('delivered'));
                            }
                        ?>
                        <?= form_dropdown('status', $opts, '', 'class="form-control" id="status" required="required" style="width:100%;"'); ?>
                    </div>

                    <div class="form-group">
                        <?= lang("delivered_by", "delivered_by"); ?>
                        <?= form_input('delivered_by', (isset($_POST['delivered_by']) ? $_POST['delivered_by'] : ''), 'class="form-control" id="delivered_by"'); ?>
                    </div>

                    <div class="form-group">
                        <?= lang("received_by", "received_by"); ?>
                        <?= form_input('received_by', (isset($_POST['received_by']) ? $_POST['received_by'] : ''), 'class="form-control" id="received_by"'); ?>
                    </div>

                    <div class="form-group">
                        <?= lang("attachment", "attachment") ?>
                        <input id="attachment" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false" data-show-preview="false" class="form-control file">
                    </div>

                    <div class="form-group">
                        <?= lang("note", "note"); ?>
                        <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="note"'); ?>
                    </div>
                    
                    <!-- <div class="form-group">
                        <?= lang("destination", "destination") ?>
                        <div class="input-group">
                            <input id="destination" type="text" name="destination" class="form-control">
                            <span class="input-group-addon pointer" id="browse_loc" onclick="calculateDistances();" style="padding: 1px 10px;">
                                <i class="fa fa-search"></i>
                            </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <?= lang("shipping_charges", "shipping_charges"); ?>
                        <?= form_input('shipping', (isset($_POST['shipping']) ? $_POST['shipping'] : ''), 'class="form-control" id="shipping" readonly'); ?>
                    </div> -->
                </div>
                
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="">Product</label>
                        <table class="table items table-striped table-bordered table-condensed table-hover sortable_table"> 
                            <thead>
                            <tr> 
                                <th>#</th>
                                <th>Product Code</th>
                                <th>Product Name</th>
                                <th>Unit</th>
                                <th>Remaining Stock</th>
                                <th>Quantity</th>
                                <th>Unsend Quantity</th>
                                <th>Quantity to Send</th>
                            </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    foreach ($sale_items as $i => $sale_item) { 
                                        $get_wh = $this->sales_model->getWarehouseProduct($sale_item->warehouse_id, $sale_item->product_id);

                                        if($inv->sale_type == 'booking'){
                                            $getdataqty = 'onblur="get_data('.$sale_item->product_id.','.$sale_item->warehouse_id.')"';
                                        }else{
                                            $getdataqty = '';
                                        }
                                        ?>
                                <tr>
                                    <td class="text-center"><?=$i+1?></td>
                                    <td><?=$sale_item->product_code?></td>
                                    <td><?=$sale_item->product_name?></td>
                                    <td><?=$sale_item->product_unit_code?></td>
                                    <td class="text-center"><?=(int) $get_wh->quantity?></td>
                                    <td class="text-center"><?=(int) $sale_item->quantity?></td>
                                    <td class="text-center"><?=(int) $sale_item->quantity - $sale_item->sent_quantity?></td>
                                    <td style="width: 15%">
                                        <input type="hidden" name="sale_items_id[]" value="<?=$sale_item->id?>">
                                        <input type="text" class="form-control text-center sent check_quantity" data-remaining="<?=(int) $sale_item->quantity - $sale_item->sent_quantity?>" name="sent_quantity[]" style="padding: 5px;" value="0" <?=$getdataqty;?> data-product-id="<?=$sale_item->product_id?>"  >
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                            <tfoot></tfoot>
                        </table>
                    </div>
                </div>
            </div>

        </div> 
        <input type="hidden" name="uuid" value="<?=getUuid()?>">
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="add_delivery" name="add_delivery"><?= lang('add_delivery') ?></button>
            <!-- <?php echo form_submit('add_delivery', lang('add_delivery'), 'class="btn btn-primary" id="add_delivery"'); ?> -->
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?=FORCAPOS_VERSION?>"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['sma'] = <?=$dp_lang?>;
</script>
<?= $modal_js ?>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function () {
        $('#add_delivery').attr('disabled', true);

        <?php if($sale_type != 'booking') { ?>
            $('.check_quantity').on('change', function(){
                var check_quantity = $('.check_quantity');
                var total_quantity = 0;

                for(var i=0; i < check_quantity.length; i++){
                    total_quantity = total_quantity + check_quantity[i].value;
                }

                if(total_quantity > 0){
                    $('#add_delivery').removeAttr('disabled');
                }else{
                    $('#add_delivery').attr('disabled', true);
                }
            });
        <?php } ?>

        $.fn.datetimepicker.dates['sma'] = <?=$dp_lang?>;
        $("#date").datetimepicker({
            format: site.dateFormats.js_ldate,
            fontAwesome: true,
            language: 'sma',
            weekStart: 1,
            todayBtn: 1,
            autoclose: 1,
            todayHighlight: 1,
            startView: 2,
            forceParse: 0
        }).datetimepicker('update', new Date());
        $("#add_delivery").click(function(){
            $(this).attr("disabled", "disabled");
            $(this).html("Memuat...");
            document.getElementById('form_delivery').submit();
            
        });
        /* position_customer=getCoordinates('<?=$customer->city?>','<?=$customer->address?>','<?=$customer->state?>');
        position_user=getCoordinates('<?=$user->city?>','<?=$user->address?>','<?=$user->state?>');
        getDistance(position_user.lat, position_user.lng, position_customer.lat, position_customer.lng); */
        
    });

    var arr = [];

function get_data(data_product_id,data_werehouse_id){
    $('#add_delivery').attr('disabled', true);
    $.ajax({
        type:'get', async:false,
        url: "<?= site_url('sales/getQtyProduct/'); ?>"+data_product_id+"/"+data_werehouse_id,
        dataType: "json",
        success: function(data){
            var qty_now = $("input[data-product-id ="+data_product_id+"]").val();
            if(qty_now > data.qty){
                $("input[data-product-id ="+data_product_id+"]").parents("tr").addClass("danger");
                arr.push(data_product_id);
            }
            else{
                $("input[data-product-id ="+data_product_id+"]").parents("tr").removeClass("danger");
                var index = arr.indexOf(data_product_id);
                if (index >= 0) {
                    arr.splice( index, 1 );
                }
            }
        }
    });
    if(arr.length > 0){
        $('#add_delivery').attr('disabled', true);
    }else{
        $('#add_delivery').removeAttr('disabled');
    }
}

function getCoordinates(city,street,province){
    var coordinate;
    jl=encodeURIComponent(city+','+street+','+province);
    $.ajax({
        type:'get', async:false,
        url: '<?php echo $url_API;?>geocode/json?address='+jl+'&sensor=true&region=Indonesia&key=AIzaSyBP3-7OMYP0ZpTuc2Rj5m184oFwqprDC2o',
        dataType: "json",
        success: function(data){
            if(data.results[0].geometry){
                coordinate=data.results[0].geometry.location;
            }else{
                coordinate = FALSE;
            }
        }
    });
    return coordinate;
}

function getDistance(lat_source,lng_source,lat_distance,lng_distance){
    var dist;
    $.ajax({
        type:'get', async:false,
        url: '<?= site_url('sales/getCharges'); ?>',
        data: {
            sourceLat: lat_source,
            sourceLng: lng_source,
            destinationLat: lat_distance,
            destinationLng: lng_distance
        },
        dataType: "json",
        success: function(data){
            if('<?=$customer->customer_group_name?>'=='Member'){
                dist=(data?data.cost_member:0);
            }else{
                dist=(data?data.cost:0);
            }
            $('#shipping').val(formatDecimal(dist));
        }
    });
    return dist;
}

function formatDecimal(x, d) {
    if (!d) { d = site.settings.decimals; }
    return parseFloat(accounting.formatNumber(x, d, '', '.'));
}
/*    $(document).ready(function(){
       $.ajax({
           type:'get',
           url: '<?= site_url('sales/getCharges'); ?>',
           data: {
               city: '<?=$customer->city?>',
               state: '<?=$customer->state?>',
               address: '<?=$customer->address?>'
           },
           dataType: "json",
           success: function(data){
               console.log(data);
           }
       });
   }); */



function getShippingCost(distance){
    var dist;
    $.ajax({
        type:'get', async:false,
        url: '<?= site_url('sales/getShippingCostMap'); ?>',
        data: {
            distance: distance
        },
        dataType: "json",
        success: function(data){
            if('<?=$customer->customer_group_name?>'=='Member'){
                dist=(data?data.cost_member:0);
            }else{
                dist=(data?data.cost:0);
            }
            $('#shipping').val(formatDecimal(dist));
        }
    });
}
</script>
