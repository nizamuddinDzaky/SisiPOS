    <!-- Start Footer area-->
    <div class="footer-copyright-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="footer-copy-right">
                        <p>Hak Cipta © <?=FORCAPOS_COPYRIGHT?>. Dikembangkan oleh <a href="https://sisi.id">PT. Sinergi Informatika Semen Indonesia</a>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade delete_modal" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <h2>Delete</h2>
                    <p>Are You Sure ?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" id="delete_button" class="btn btn-default">Yes</button>
                    <button type="button" class="btn" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade pay_modal" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <h2>Confirm Pay Subscription</h2>
                    <p>Are You Sure ?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" id="pay_button" class="btn btn-default">Yes</button>
                    <button type="button" class="btn" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade cancel_modal" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <h2>Cancel Subscription</h2>
                    <p>Are You Sure ?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" id="cancel_button" class="btn btn-default">Yes</button>
                    <button type="button" class="btn" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade renew_modal" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <h2>Renewal Subscription</h2>
                    <p>Are You Sure ?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" id="renew_button" class="btn btn-default">Yes</button>
                    <button type="button" class="btn" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade reject_modal" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <h2>Reject Payment</h2>
                    <p>Are You Sure ?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" id="reject_button" class="btn btn-default">Yes</button>
                    <button type="button" class="btn" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade edit_plugin_modal" role="dialog" data-backdrop="static">
        <div class="modal-dialog modals-default">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="datepicker-int mg-t-10">
                        <div class="form-group nk-datapk-ctm form-elet-mg">
                            <label>Start Date</label>
                            <div class="input-group date nk-int-st">
                                <span class="input-group-addon"></span>
                                <input type="text" class="form-control datetime" placeholder="Start Date" id="date_edit_plugin" required="">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="edit_plugin_button" class="btn btn-default">Yes</button>
                    <button type="button" class="btn" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade image_modal" role="dialog" data-backdrop="static">
        <div class="modal-dialog modals-default">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <center>
                        <h2 id="company_payment"></h2>
                        <h2 id="ref_payment"></h2>
                    </center>
                    
                    <div class="datepicker-int mg-t-12" style="text-align: center;">
                        <div class="form-group nk-datapk-ctm form-elet-mg">
                            <img id="img_payment" style="max-height: 400px; max-height: 300px">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- End Footer area-->
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
                waiting: 'Waiting' , 
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
                kredit : '<?=lang('kredit')?>',
                'cash before delivery' : '<?=lang('cash before delivery')?>',
            };
        var dss = <?= json_encode(array('packing' => lang('packing'), 'delivering' => lang('delivering'), 'delivered' => lang('delivered'),'returned' => lang('returned'))); ?>;
    </script>
    <script src="<?=$assets_ab?>js/core_ab.js"></script>
    <!-- jquery
        ============================================ -->
    <script src="<?=$assets_ab?>js/vendor/jquery-1.12.4.min.js"></script>
    <!-- bootstrap JS
        ============================================ -->
    <script src="<?=$assets_ab?>js/bootstrap.min.js"></script>
    <!-- wow JS
        ============================================ -->
    <script src="<?=$assets_ab?>js/wow.min.js"></script>
    <!-- price-slider JS
        ============================================ -->
    <script src="<?=$assets_ab?>js/jquery-price-slider.js"></script>
    <!-- owl.carousel JS
        ============================================ -->
    <script src="<?=$assets_ab?>js/owl.carousel.min.js"></script>
    <!-- scrollUp JS
        ============================================ -->
    <script src="<?=$assets_ab?>js/jquery.scrollUp.min.js"></script>
    <!-- meanmenu JS
        ============================================ -->
    <script src="<?=$assets_ab?>js/meanmenu/jquery.meanmenu.js"></script>
    <!-- counterup JS
        ============================================ -->
    <script src="<?=$assets_ab?>js/counterup/jquery.counterup.min.js"></script>
    <script src="<?=$assets_ab?>js/counterup/waypoints.min.js"></script>
    <script src="<?=$assets_ab?>js/counterup/counterup-active.js"></script>
    <!-- mCustomScrollbar JS
        ============================================ -->
    <script src="<?=$assets_ab?>js/scrollbar/jquery.mCustomScrollbar.concat.min.js"></script>
    <!-- jvectormap JS
        ============================================ -->
    <!-- <script src="<?=$assets_ab?>js/jvectormap/jquery-jvectormap-2.0.2.min.js"></script>
    <script src="<?=$assets_ab?>js/jvectormap/jquery-jvectormap-world-mill-en.js"></script>
    <script src="<?=$assets_ab?>js/jvectormap/jvectormap-active.js"></script> -->
    <!-- sparkline JS
        ============================================ -->
    <script src="<?=$assets_ab?>js/sparkline/jquery.sparkline.min.js"></script>
    <script src="<?=$assets_ab?>js/sparkline/sparkline-active.js"></script>
    <!-- sparkline JS
        ============================================ -->
    <script src="<?=$assets_ab?>js/flot/jquery.flot.js"></script>
    <script src="<?=$assets_ab?>js/flot/jquery.flot.resize.js"></script>
    <script src="<?=$assets_ab?>js/flot/curvedLines.js"></script>
    <script src="<?=$assets_ab?>js/flot/flot-active.js"></script>
    <!-- knob JS
        ============================================ -->
    <script src="<?=$assets_ab?>js/knob/jquery.knob.js"></script>
    <script src="<?=$assets_ab?>js/knob/jquery.appear.js"></script>
    <script src="<?=$assets_ab?>js/knob/knob-active.js"></script>
    <!-- datapicker JS
        ============================================ -->
    <script src="<?=$assets_ab?>js/datapicker/bootstrap-datepicker.js"></script>
    <script src="<?=$assets_ab?>js/datapicker/datepicker-active.js"></script>
    <!-- bootstrap select JS
        ============================================ -->
    <script src="<?=$assets_ab?>js/bootstrap-select/bootstrap-select.js"></script>
    <!--  chosen JS
        ============================================ -->
    <script src="<?=$assets_ab?>js/chosen/chosen.jquery.js"></script>
    <!--  notification JS
        ============================================ -->
    <script src="<?=$assets_ab?>js/notification/bootstrap-growl.min.js"></script>
    <!--  wave JS
        ============================================ -->
    <script src="<?=$assets_ab?>js/wave/waves.min.js"></script>
    <script src="<?=$assets_ab?>js/wave/wave-active.js"></script>
    <!--  todo JS
        ============================================ -->
    <script src="<?=$assets_ab?>js/todo/jquery.todo.js"></script>
    <!-- plugins JS
        ============================================ -->
    <script src="<?=$assets_ab?>js/plugins.js"></script>
    <!--  Chat JS
        ============================================ -->
    <script src="<?=$assets_ab?>js/chat/moment.min.js"></script>
    <script src="<?=$assets_ab?>js/chat/jquery.chat.js"></script>
    <!-- Data Table JS
        ============================================ -->
    <!-- <script type="text/javascript" src="<?= $assets_ab ?>js/data-table/jquery.dataTables.min.js"></script> -->
    <script type="text/javascript" src="<?= $assets ?>js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/jquery.dataTables.dtFilter.min.js"></script> 
    <!-- icheck JS
        ============================================ -->
    <script src="<?= $assets_ab ?>js/icheck/icheck.min.js"></script>
    <script src="<?= $assets_ab ?>js/icheck/icheck-active.js"></script>
    <!-- main JS
        ============================================ -->
    <script src="<?=$assets_ab?>js/main.js"></script>
    <!-- tawk chat JS
        ============================================ -->
    <!-- <script src="<?=$assets_ab?>js/tawk-chat.js"></script> -->
    <script type="text/javascript">
        $('.datepicker .input-group.date').datepicker({
            format: "yyyy-mm-dd",
            keyboardNavigation: false,
            forceParse: false,
            calendarWeeks: true,
            autoclose: true
        });

        $('.datepicker .input-group.date #start_date').datepicker({
            format: "yyyy-mm-dd",
            keyboardNavigation: false,
            forceParse: false,
            calendarWeeks: true,
            autoclose: true
        }).on("change", function (e) {
            let payment = $('#payment').val();
            let start_date = $('#start_date').val();
            let action = site.base_url +"billing_portal/subscription/get_endDate_byPeriod/";
            $.ajax({
                type: "POST",
                data : {start_date:start_date, payment:payment},
                url: action,
                success: function (res) {
                    $('#end_date').val(res);
                    $('.row_end').show();
                }
            });
            return false;
        });
    </script>

</body>

</html>