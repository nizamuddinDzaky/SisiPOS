<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="clearfix"></div>
<?= '</div></div></div></div></div></div></div></div>'; ?>
<!-- </div></div></div></td></tr></table></div></div> -->
<div class="clearfix"></div>
<footer>
<a href="#" id="toTop" class="blue" style="position: fixed; bottom: 30px; right: 30px; font-size: 30px; display: none;">
    <i class="fa fa-chevron-circle-up"></i>
</a>

    <p style="text-align:center;">&copy; <?= date('Y') . " " . $Settings->site_name; ?>  <?php if ($_SERVER["REMOTE_ADDR"] == '127.0.0.1') {
            echo ' - Page rendered in <strong>{elapsed_time}</strong> seconds';
        } ?> - <?=FORCAPOS_VERSION?></p>
</footer>
<?= '</div>'; ?>

<a data-toggle="modal" id="hidden-a" style="display: none;" data-target="#myModal"  data-backdrop="static" href=""></a>
<div class="modal fade in" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
<div class="modal fade in" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2" aria-hidden="true"></div>
<div id="modal-loading" style="display: none;">
    <div class="blackbg"></div>
    <div class="loader"></div>
</div>
<!--<div id="ajaxCall" style="-->
<!---->
<!--    top: 0;-->
<!--    bottom: 0;-->
<!--    right: 0;-->
<!--border-radius: 0">-->
<!--    -->
<!--    <div class="hexdots-loader" style="margin-top: 20%"></div>-->
<!--</div>-->
<?php unset($Settings->setting_id, $Settings->smtp_user, $Settings->smtp_pass, $Settings->smtp_port, $Settings->update, $Settings->reg_ver, $Settings->allow_reg, $Settings->default_email, $Settings->mmode, $Settings->timezone, $Settings->restrict_calendar, $Settings->restrict_user, $Settings->auto_reg, $Settings->reg_notification, $Settings->protocol, $Settings->mailpath, $Settings->smtp_crypto, $Settings->corn, $Settings->customer_group, $Settings->envato_username, $Settings->purchase_code); ?>
<script type="text/javascript">
var dt_lang = <?=$dt_lang?>, dp_lang = <?=$dp_lang?>, site = <?=json_encode(array('base_url' => base_url(), 'settings' => $Settings, 'dateFormats' => $dateFormats, 'm'=>$m, 'v'=>$v))?>;
var lang = {
        paid: '<?=lang('paid');?>', 
        pending: '<?=lang('pending');?>', 
        canceled: '<?=lang('canceled');?>', 
        completed: '<?=lang('completed');?>', 
        ordered: '<?=lang('ordered');?>', 
        confirmed: '<?=lang('confirmed');?>', 
        received: '<?=lang('received');?>', 
        partial: '<?=lang('partial');?>', 
        sent: '<?=lang('sent');?>', 
        r_u_sure: '<?=lang('r_u_sure');?>', 
        due: '<?=lang('due');?>', 
        returned: '<?=lang('returned');?>', 
        transferring: '<?=lang('transferring');?>', 
        active: '<?=lang('active');?>', 
        inactive: '<?=lang('inactive');?>', 
        unexpected_value: '<?=lang('unexpected_value');?>', 
        select_above: '<?=lang('select_above');?>', 
        download: '<?=lang('download');?>', 
        waiting: '<?=lang('waiting');?>' , 
        done: '<?=lang('done');?>', 
        verified: '<?=lang('verified');?>', 
        unverified: '<?=lang('unverified');?>', 
        delivering: '<?=lang('delivering');?>', 
        packing: '<?=lang('packing')?>',
        delivered: '<?=lang('delivered')?>',
        reserved: '<?=lang('reserved');?>', 
        closed: '<?=lang('closed');?>',
        item_sale_duplicated: '<?=lang('item_sale_duplicated')?>',
        item_purchase_duplicated: '<?=lang('item_purchase_duplicated')?>',
        item_quotation_duplicated: '<?=lang('item_quotation_duplicated')?>',
        item_transfer_duplicated: '<?=lang('item_transfer_duplicated')?>',
        item_adjusment_duplicated: '<?=lang('item_adjusment_duplicated')?>',
        credit_reviewed : '<?= lang('credit_reviewed'); ?>',
        credit_received : '<?= lang('credit_received'); ?>',
        credit_declined : '<?= lang('credit_declined'); ?>',
        kredit_partial : '<?= lang('kredit_partial'); ?>',
        already_paid : '<?= lang('already_paid'); ?>',
        'cash on delivery' : '<?=lang('cash on delivery')?>',
        kredit_pro : '<?=lang('kredit_pro')?>',
        kredit_mandiri : '<?=lang('kredit_mandiri')?>',
        kredit : '<?=lang('kredit')?>',
        'cash before delivery' : '<?=lang('cash before delivery')?>',
        edit_customer_group : '<?=lang('edit_customer_group')?>',
        add_customer_to_customer_group : '<?=lang('add_customer_to_customer_group')?>',
        edit_sales_person : '<?=lang("edit_sales_person")?>',
        add_customer_to_sales_person : '<?=lang('add_customer_to_sales_person')?>',
    };
var dss = <?= json_encode(array('packing' => lang('packing'), 'delivering' => lang('delivering'), 'delivered' => lang('delivered'),'returned' => lang('returned'))); ?>;
</script>
<?php
$s2_lang_file = read_file('./assets/config_dumps/s2_lang.js?v="'.FORCAPOS_VERSION.'"');
foreach (lang('select2_lang') as $s2_key => $s2_line) {
    $s2_data[$s2_key] = str_replace(array('{', '}'), array('"+', '+"'), $s2_line);
}
$s2_file_date = $this->parser->parse_string($s2_lang_file, $s2_data, true);
?>
<!-- <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key=<?= SB_CLIENT ?>></script> -->
<!-- <script async defer src="https://maps.googleapis.com/maps/api/js?callback=initMap&libraries=places&key=<?= _KEY_MAP ?>" type="text/javascript"></script> -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/datepicker/0.6.5/datepicker.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/jquery.dataTables.min.js"></script>
<!--<script type="text/javascript" src="--><?//= $assets ?><!--js/datatable10/jquery.dataTables.min.js"></script>-->
<script type="text/javascript" src="<?= $assets ?>js/jquery.dataTables.dtFilter.min.js"></script>
<!-- <script type="text/javascript" src="<?= $assets ?>js/datatable10/dataTables.scroller.min.js"></script> -->
<script type="text/javascript" src="<?= $assets ?>js/select2.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/jquery-ui.min.js"></script>
<!-- <script type="text/javascript" src="<?= $assets ?>js/jquery.masknumber.js"></script> -->
<script type="text/javascript" src="<?= $assets ?>js/bootstrapValidator.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/custom.js?v=<?=FORCAPOS_VERSION?>"></script>
<script type="text/javascript" src="<?= $assets ?>js/jquery.calculator.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/core.js?v=<?=FORCAPOS_VERSION?>"></script>
<script type="text/javascript" src="<?= $assets ?>js/search_menu.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/perfect-scrollbar.min.js"></script>
<?= ($m == 'purchases' && ($v == 'add' || $v == 'edit' || $v == 'purchase_by_csv')) ? '<script type="text/javascript" src="' . $assets . 'js/purchases.js?v="'.FORCAPOS_VERSION.'"></script>' : ''; ?>
<?= ($m == 'transfers' && ($v == 'add' || $v == 'edit')) ? '<script type="text/javascript" src="' . $assets . 'js/transfers.js?v="'.FORCAPOS_VERSION.'"></script>' : ''; ?>
<?= ($m == 'sales' && ($v == 'add' || $v == 'edit') ||
     $m == 'sales_booking' && ($v == 'add_booking_sale' || $v == 'edit_booking_sale')) ? '<script type="text/javascript" src="' . $assets . 'js/sales.js?v="'.FORCAPOS_VERSION.'"></script>' : ''; ?>
<?= ($m == 'quotes' && ($v == 'add' || $v == 'edit')) ? '<script type="text/javascript" src="' . $assets . 'js/quotes.js?v="'.FORCAPOS_VERSION.'"></script>' : ''; ?>
<?= ($m == 'products' && ($v == 'add_adjustment' || $v == 'edit_adjustment')) ? '<script type="text/javascript" src="' . $assets . 'js/adjustments.js?v="'.FORCAPOS_VERSION.'"></script>' : ''; ?>
<?= ($m == 'products' && ($v == 'add_consignment' || $v == 'edit_consignment')) ? '<script type="text/javascript" src="' . $assets . 'js/consignments.js?v="'.FORCAPOS_VERSION.'"></script>' : ''; ?>

<script type="text/javascript" charset="UTF-8">var r_u_sure = "<?=lang('r_u_sure')?>";
    <?=$s2_file_date?>
    $.extend(true, $.fn.dataTable.defaults, {"oLanguage":<?=$dt_lang?>});
    $.fn.datetimepicker.dates['sma'] = <?=$dp_lang?>;
    $(window).load(function () {
        $('.mm_<?=$m?>').addClass('active');
        $('.mm_<?=$m?>').find("ul").first().slideToggle();
        $('#<?=$m?>_<?=$v?>').addClass('active');
        $('.mm_<?=$m?> a .chevron').removeClass("closed").addClass("opened");
    });
</script>

<?php if(SOCKET_NOTIFICATION) { ?>
    <!-- SOCKET Notification Start -->
    <script src="<?= $assets ?>js/toastr/toastr.js"></script>
    <script>
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": false,
            "progressBar": true,
            "positionClass": "toast-bottom-right",
            "preventDuplicates": false,
            "onclick": true,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "15000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut",
            "tapToDismiss": false
        }
    </script>
    <link rel="stylesheet" href="<?= $assets ?>js/toastr/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/push.js/1.0.5/push.min.js"></script>

    <script>
        var get_next_notifikasi = '<?= site_url() ?>socket_notifications/getNotifications?company_id=<?= $this->session->userdata('company_id');?>';
        $(document).ready(function() {
            Push.Permission.request(function(){console.log('allow')}, function(){console.log('denied')});

            <?php if ($this->session->flashdata('new_notif') || $this->socket_notification_model->new_notif) { ?>
            socket.emit('new_notif', {
                company_id: '<?= $this->session->userdata("send_to_company_id") ?? $this->socket_notification_model->send_to_company_id ?>',
                socket_notification_id : '<?= $this->session->userdata("socket_notification_id") ?? $this->socket_notification_model->socket_notification_id ?>'
            });
            <?php } ?>

            // $('<audio id="soundNotif"><source src="<?= $assets ?>/sounds/notif.mp3" type="audio/mpeg"></audio>').appendTo('body');
            socket.on('new_notif', function(data) {
                get_next_notifikasi = '<?= site_url() ?>socket_notifications/getNotifications?company_id=<?= $this->session->userdata('company_id');?>';
                reg_notifications(data);
            });

            reg_notifications('next');
            $("#tampil_lebih").on('click', function(){
                reg_notifications('next');
            });
        });

        function reg_notifications(data = null){
            var socket_notification_id = data != 'next' ? data['data']['socket_notification_id'] : '';
            
            $.ajax({
                url: get_next_notifikasi,
                type: 'GET',
                data: {},
                success: function(data_notifications) {
                    var parse_data_notifications = JSON.parse(data_notifications);
                    var socket_notification_list = parse_data_notifications['data'];
                    
                    get_next_notifikasi = parse_data_notifications['next_url'];
                    $(".total_new_notification").html(parse_data_notifications['total_unread']);
                    if(data != 'next'){
                        $("#list_notifications").html('');
                    }
                    
                    for(var i = 0; i < socket_notification_list.length; i++){
                        if(data != 'next' && socket_notification_id == socket_notification_list[i]['id']){
                            Push.Permission.request(
                                function(){
                                    Push.create(socket_notification_list[i]['title'], {
                                        body: socket_notification_list[i]['message'],
                                        icon: 'icon.png',
                                        timeout: 30000,
                                        onClick: function () {
                                            window.location.href = getTypeSaleUrl(socket_notification_list[i]);
                                        }
                                    });
                                }, 
                                function(){
                                    toastr.info(
                                        socket_notification_list[i]['message'], 
                                        socket_notification_list[i]['title'], 
                                        {
                                            "saleId" : socket_notification_list[i]['transaction_id'],
                                            "link" : getTypeSaleUrl(socket_notification_list[i])
                                        }
                                    );
                                }
                            );

                            //$("#soundNotif")[0].play();
                        }
                        $("#list_notifications").append('<div class="col-md-12">'+
                                '<div class="data_notif">'+
                                    '<div id="bodyNotif_'+socket_notification_list[i]['id']+'" class="body_notif '+(parseInt(socket_notification_list[i]['is_read']) != 0 ? '' : 'unread')+'">'+
                                    socket_notification_list[i]['transaction_type'] +
                                    getTypeSaleLink(socket_notification_list[i]) +   
                                    ' <i class="fa fa-heart"></i>'+
                                        '<span style="margin-left:5px;">'+socket_notification_list[i]['message']+'</span>'+
                                    '</a>'+ 
                                        '<div class="row" style=" margin-top: 10px;">'+
                                            '<div class="col-md-6">'+
                                                '<span style="color: #888888;">'+socket_notification_list[i]['date']+'</span>'+
                                            '</div>'+
                                            '<div class="col-md-6" style="text-align:right;">'+
                                                '<button id="readNotif" onclick="set_read_notification(this, \''+socket_notification_list[i]['id']+'\')" class="readNotif">'+
                                                    (parseInt(socket_notification_list[i]['is_read']) != 0 ? '' : '<span style="color: #428bca;">Read</span>')+
                                                '</button>'+
                                            '</div>'+
                                        '</div>'+
                                    '</div>'+
                                '</div>'+
                                    
                            '</div>');
                    }
                }
            });
        }

        function getTypeSaleLink(notif){
            switch (notif['transaction_type']) {
                case "SALE":
                    return `<a href="<?=base_url("sales_booking/view/")?>${notif['transaction_id']}">`;
                    break;
                case "DO":
                    return `<a data-toggle="modal" data-target="#myModal"  data-backdrop="static" href="<?=base_url("sales/view_delivery/")?>${notif['transaction_id']}">`;
                    break;
                case "PAY":
                    return `<a data-toggle="modal" data-target="#myModal"  data-backdrop="static" href="<?=base_url("sales/payments/")?>${notif['transaction_id']}">`;
                    break;
                default:
                    return "";
                    break;
            }
        }

        function getTypeSaleUrl(notif){
            switch (notif['transaction_type']) {
                case "SALE":
                    return `<?=base_url("sales_booking/view/")?>${notif['transaction_id']}|-`;
                    break;
                case "DO":
                    return `<?=base_url("sales/view_delivery/")?>${notif['transaction_id']}|modal`;
                    break;
                case "PAY":
                    return `<?=base_url("sales/payments/")?>${notif['transaction_id']}|modal`;
                    break;
                default:
                    return "";
                    break;
            }
        }

        function set_read_notification(this_data, notification_id){
            $.ajax({
                url: '<?= site_url() ?>socket_notifications/setReadNotification?id='+notification_id,
                type: 'GET',
                data: {},
                success: function(data_notifications) {
                    this_data.innerHTML = '';
                    $('#bodyNotif_'+notification_id).removeClass('unread');

                    var total_unread = $('.total_new_notification').html();
                    $('.total_new_notification').html(parseInt(total_unread-1));
                }
            });
        }

        function set_read_all_notification(){
            $.ajax({
                url: '<?= site_url() ?>socket_notifications/setReadAllNotification?company_id=<?= $this->session->userdata('company_id');?>',
                type: 'GET',
                data: {},
                success: function(data_notifications) {
                    $('.unread').removeClass('unread');
                    $('.total_new_notification').html(0);
                }
            });
        }
    </script>
    <!-- SOCKET Notification End -->
<?php } ?>
</body>
</html>
