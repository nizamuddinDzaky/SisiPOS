<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript">
    <?php if ($this->session->userdata('remove_pols')) { ?>
        if (localStorage.getItem('poitems')) {
            localStorage.removeItem('poitems');
        }
        if (localStorage.getItem('podiscount')) {
            localStorage.removeItem('podiscount');
        }
        if (localStorage.getItem('potax2')) {
            localStorage.removeItem('potax2');
        }
        if (localStorage.getItem('poshipping')) {
            localStorage.removeItem('poshipping');
        }
        if (localStorage.getItem('no_si_spj')) {
            localStorage.removeItem('no_si_spj');
        }
        if (localStorage.getItem('no_si_do')) {
            localStorage.removeItem('no_si_do');
        }
        if (localStorage.getItem('no_si_so')) {
            localStorage.removeItem('no_si_so');
        }
        if (localStorage.getItem('no_si_billing')) {
            localStorage.removeItem('no_si_billing');
        }
        if (localStorage.getItem('poref')) {
            localStorage.removeItem('poref');
        }
        if (localStorage.getItem('powarehouse')) {
            localStorage.removeItem('powarehouse');
        }
        if (localStorage.getItem('ponote')) {
            localStorage.removeItem('ponote');
        }
        if (localStorage.getItem('posupplier')) {
            localStorage.removeItem('posupplier');
        }
        if (localStorage.getItem('pocurrency')) {
            localStorage.removeItem('pocurrency');
        }
        if (localStorage.getItem('poextras')) {
            localStorage.removeItem('poextras');
        }
        if (localStorage.getItem('podate')) {
            localStorage.removeItem('podate');
        }
        if (localStorage.getItem('postatus')) {
            localStorage.removeItem('postatus');
        }
        if (localStorage.getItem('popayment_term')) {
            localStorage.removeItem('popayment_term');
        }
        if (localStorage.getItem('delivery_date')) {
            localStorage.removeItem('delivery_date');
        }
    <?php $this->sma->unset_data('remove_pols');
    } ?>
    //    poitems_temp = {
    //        "data":[]
    //    };
    <?php if ($quote_id) { ?>
        localStorage.setItem('powarehouse', '<?= $quote->warehouse_id ?>');
        localStorage.setItem('ponote', '<?= str_replace(array("\r", "\n"), "", $this->sma->decode_html($quote->note)); ?>');
        localStorage.setItem('podiscount', '<?= $quote->order_discount_id ?>');
        localStorage.setItem('potax2', '<?= $quote->order_tax_id ?>');
        localStorage.setItem('poshipping', '<?= $quote->shipping ?>');
        localStorage.setItem('no_si_spj', '<?= $quote->no_spj ?>');
        localStorage.setItem('no_si_do', '<?= $quote->no_do ?>');
        localStorage.setItem('no_si_so', '<?= $quote->no_so ?>');
        localStorage.setItem('delivery_date', '<?= date('d/m/Y', strtotime($quote->tanggal_do)) ?>');
        <?php if ($quote->supplier_id) { ?>
            localStorage.setItem('posupplier', '<?= $quote->supplier_id ?>');
        <?php } ?>
        localStorage.setItem('poitems', JSON.stringify(<?= $quote_items; ?>));

        //    localStorage.setItem('potrash',JSON.stringify(<?= $rand_id ?>));
        //    var d=JSON.parse(localStorage.getItem('potrash'));
        //    for (var i = 0; i < Object.keys(d).length; i++) {
        //        slitems_temp.data.push({
        //            "trx_id"        : d[i].trx_id,
        //            "product_id"    : d[i].product_id
        //        });
        //    }
        //    localStorage.setItem('poitems_temp', JSON.stringify(slitems_temp));
    <?php } ?>

    var count = 1,
        an = 1,
        po_edit = false,
        product_variant = 0,
        DT = <?= $Settings->default_tax_rate ?>,
        DC = '<?= $default_currency->code ?>',
        shipping = 0,
        product_tax = 0,
        invoice_tax = 0,
        total_discount = 0,
        total = 0,
        tax_rates = <?php echo json_encode($tax_rates); ?>,
        poitems = {},
        audio_success = new Audio('<?= $assets ?>sounds/sound2.mp3'),
        audio_error = new Audio('<?= $assets ?>sounds/sound3.mp3');
    $(document).ready(function() {
        if (!localStorage.getItem('podiscount') ||
            !localStorage.getItem('potax2') ||
            !localStorage.getItem('poshipping') ||
            !localStorage.getItem('no_si_spj') ||
            !localStorage.getItem('no_si_do') ||
            !localStorage.getItem('no_si_so') ||
            !localStorage.getItem('delivery_date')) {
            localStorage.setItem('poextras', 1);
            $('#extras-con').slideDown();
        } else {
            localStorage.removeItem("poextras");
            $('#extras-con').slideUp();
        }
        <?php if ($this->input->get('supplier')) { ?>
            if (!localStorage.getItem('poitems')) {
                localStorage.setItem('posupplier', <?= $this->input->get('supplier'); ?>);
            }
        <?php } ?>
        <?php if ($Owner || $Admin || $LT) { ?>
            if (!localStorage.getItem('podate')) {
                $("#podate").datetimepicker({
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
            }
            $(document).on('change', '#podate', function(e) {
                localStorage.setItem('podate', $(this).val());
            });
            if (podate = localStorage.getItem('podate')) {
                $('#podate').val(podate);
            }
        <?php } ?>
        if (!localStorage.getItem('potax2')) {
            localStorage.setItem('potax2', <?= $Settings->default_tax_rate2; ?>);
            setTimeout(function() {
                $('#extras').iCheck('check');
            }, 1000);
        }
        ItemnTotals();
        $("#add_item").autocomplete({
            // source: '<?= site_url('purchases/suggestions'); ?>',
            source: function(request, response) {
                //                if (!$('#posupplier').val()) {
                //                    $('#add_item').val('').removeClass('ui-autocomplete-loading');
                //                    bootbox.alert('<?= lang('select_above'); ?>');
                //                    $('#add_item').focus();
                //                    return false;
                //                }
                $.ajax({
                    type: 'get',
                    //                    url: '<?= site_url('purchases/suggestions'); ?>/'+ $('#posupplier').val(),
                    url: '<?= site_url('purchases/suggestions'); ?>/' + ($('#posupplier').val() ? $('#posupplier').val() : ''),
                    dataType: "json",
                    data: {
                        term: request.term,
                        supplier_id: $("#posupplier").val()
                    },
                    success: function(data) {
                        response(data);
                    }
                });
            },
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function(event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_match_found') ?>', function() {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                } else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                } else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_match_found') ?>', function() {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                }
            },
            select: function(event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    // let compared = compareProduct(ui.item)
                    // if(compared){
                    var row = add_purchase_item(ui.item);
                    if (row)
                        $(this).val('');
                    // }else{
                    //     bootbox.alert('Product in supplier not found');
                    //     $(this).val('');
                    // }
                } else {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });

        $(document).on('click', '#addItemManually', function(e) {
            if (!$('#mcode').val()) {
                $('#mError').text('<?= lang('product_code_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#mname').val()) {
                $('#mError').text('<?= lang('product_name_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#mcategory').val()) {
                $('#mError').text('<?= lang('product_category_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#munit').val()) {
                $('#mError').text('<?= lang('product_unit_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#mcost').val()) {
                $('#mError').text('<?= lang('product_cost_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#mprice').val()) {
                $('#mError').text('<?= lang('product_price_is_required') ?>');
                $('#mError-con').show();
                return false;
            }

            var msg, row = null,
                product = {
                    type: 'standard',
                    code: $('#mcode').val(),
                    name: $('#mname').val(),
                    tax_rate: $('#mtax').val(),
                    tax_method: $('#mtax_method').val(),
                    category_id: $('#mcategory').val(),
                    unit: $('#munit').val(),
                    cost: $('#mcost').val(),
                    price: $('#mprice').val()
                };

            $.ajax({
                type: "get",
                async: false,
                url: site.base_url + "products/addByAjax",
                data: {
                    token: "<?= $csrf; ?>",
                    product: product
                },
                dataType: "json",
                success: function(data) {
                    if (data.msg == 'success') {
                        row = add_purchase_item(data.result);
                    } else {
                        msg = data.msg;
                    }
                }
            });
            if (row) {
                $('#mModal').modal('hide');
                //audio_success.play();
            } else {
                $('#mError').text(msg);
                $('#mError-con').show();
            }
            return false;

        });

        $.ajax({
            type: 'get',
            url: site.base_url + 'welcome/experience_guide',
            dataType: "json",
            success: function(data) {
                if (!data["purchases-add"]) {
                    hopscotch.startTour(tour);
                }
            }
        });

        // START Integrasi (On Ready Func) POS -> CSMS, APIX

        //         $("#posupplier" )
        //             .change(function () {
        //               statusCSMS($(this).val())
        //             })
        //             .change();

        //         $("#delivery_order_date" )
        //             .change(function () {
        // //                console.log($(this).val())

        //                 let date = $(this).val()
        //                 date = date.split("/").join("-")
        //                 if(date.length > 0) setRemainingQuota(date)
        //                 else $("#remaining_quota").val("0").change()
        //             });

        //         $('#poorderapix').on('ifChecked', function(event){  
        //             $('#postatus').val("ordered").change()
        //             $('#postatus').attr("readonly", "readonly")
        //             $('#add_pruchase').attr("disabled", "disabled")
        //             $('#submitAPIX').show()

        //             $('#custom_fields').show()
        //             $('#cf2').val("POS Supplier").change()

        //             localStorage.setItem("poitems", "{}")
        //             loadItems()

        //             getProductsApix(localStorage.getItem("apixToken"))
        //         })

        //         $('#poorderapix').on('ifUnchecked', function(event){
        //             $('#postatus').val("received").change()
        //             $('#postatus').removeAttr("readonly")
        //             $('#add_pruchase').removeAttr("disabled")
        //             $('#submitAPIX').hide()

        //             $('#custom_fields').hide()
        //             $('#cf2').val("").change()

        //             productsFromSupplier = []
        //         });

        //         $('#poorder').on('ifChecked', function(event){            
        //             $('#postatus').val("ordered").change()
        //             $('#postatus').attr("readonly", "readonly")

        //             localStorage.setItem("powarehousetemp", $("#powarehouse").val())
        //             $("#powarehouse").attr("readonly", "readonly")

        //             $('#add_pruchase').attr("disabled", "disabled")
        //             $('#submitCSMS').show()

        //             $('#custom_fields').show()
        //             $('#cf2').val("CSMS").change()

        //             $('#csmsMessage').hide()
        //             $("#csmsModal").modal("show")

        //             localStorage.setItem("poitems", "{}")
        //             loadItems()

        //             getRequestCSMS()

        //         });

        //         $('#poorder').on('ifUnchecked', function(event){
        //             $('#postatus').val("received").change()
        //             $('#postatus').removeAttr("readonly")
        //             $("#powarehouse").val(localStorage.getItem("powarehousetemp", "")).change()
        //             $("#powarehouse").removeAttr("readonly")
        //             $('#add_pruchase').removeAttr("disabled")
        //             $('#submitCSMS').hide()

        //             $('#custom_fields').hide()
        //             $('#cf2').val("").change()

        //             productsFromSupplier = []
        //             csmsResponse = {}

        //             $("#newOrderExtrasGroup").slideUp()
        //         });

        //         $("#orderCancel").click(function() {
        //             $('#poorder').iCheck("uncheck")
        //             $("#csmsModal").modal("hide")
        //         })

        //         $("#orderSubmit").click(function() {
        //             $('#csmsMessage').hide()
        //             if (
        //                 $("#order_type").val() == "nodata" ||
        //                 $("#shipping_type").val() == "nodata" ||
        //                 $("#packaging_type").val() == "nodata" ||
        //                 $("#plant").val() == "nodata" ||
        //                 $("#city_province").val() == "nodata" ||
        //                 $("#price_list").val() == "nodata"
        //             ){
        //                 $('#csmsMessage').html('<strong>Error!</strong> Every field must be filled.')
        //                 $('#csmsMessage').show()
        //             }else{
        // //                console.log($("#order_type").find('option:selected').text())
        //                 setPaymentMethod()
        //                 setIntegratedWarehouse()
        //                 setProducts()
        //                 // $("#delivery_order_date").val("").change()
        //                 $("#newOrderExtrasGroup").slideDown()
        //                 $("#csmsModal").modal("hide")
        //             }
        //         })

        //         var freeToLeave = true;
        //         $('#submitCSMS').click(function() {
        //             createOrderCSMS()
        //         })

        //         $('#submitAPIX').click(function() {
        //             createOrderApix()
        //         })

        //         window.onbeforeunload = function() {
        //             if (freeToLeave == false) {
        //                 return "Do you really want to leave?";
        //             } else {
        //                 return;
        //             }
        //          };

        //         statusCSMS(localStorage.getItem('posupplier'))

        //         getWarehouses()

        //         $(window).on('load', function () {
        //             if(!$.isEmptyObject(JSON.parse(localStorage.getItem('poitems'))) && localStorage.getItem('posupplier')){
        //                 $('#posupplier').select2("readonly", true);
        //                 $('#poorder').iCheck("disable")
        //                 $('#poorderapix').iCheck("disable")
        //             }
        //         });
        // END Integrasi (On Ready Func) POS -> CSMS, APIX

    });

    // START Integrasi POS -> CSMS, APIX

    // var productsFromSupplier = []
    // var warehouses = []
    // var csmsResponse = {}
    // var supplierCSMS = {}

    /* function statusCSMS(supplierId) {
        hideNewOrderCSMS()
        hideNewOrderApix()

        switch (supplierId) {
            case "3":
            case "4":
            case "5":
            case 3:
            case 4:
            case 5:
                getSupplierData("<?= trim($company->id) ?>", "CSMS")
                break;
            default:            
                getSupplierData(supplierId, "APIX")
                break;
        }
    } */

    // function getInitialSupplier(supplierId){
    //     switch (supplierId) {
    //         case "3":
    //         case 3:
    //             return "SI"
    //         case "4":
    //         case 4:
    //             return "SP"
    //         case "5":
    //         case 5:
    //             return "ST"
    //     }
    //     return "SI"
    // }

    // function getSupplierData(supplierId, source){
    //     $.ajax({
    //         type: 'get',
    //         url: site.base_url + "suppliers/getSupplierById/" + supplierId,
    //         dataType: "json",
    //         timeout: 3000,
    //         success: function (data) {
    //             if(data){
    //                 if(data.cf1 != ""){
    //                     if(source == "CSMS"){
    //                         loginCSMS(data.cf1)
    //                     } else {
    //                         loginApix(data.cf1);
    //                     }
    //                 }
    //             }
    //         },
    //         error: function (request, status, error) {
    //             console.error('Something wrong : ' + error)
    //             hideNewOrderCSMS()
    //             hideNewOrderApix()
    //         }
    //     });
    // }

    // function loginCSMS(supplierCode){
    //     supplierCSMS = {}

    //     supplierCode = supplierCode.toString().padStart(10, 0);

    //     let body = {
    //         ORG : getInitialSupplier($("#posupplier" ).val()),
    //         REQUEST_TIME : getDateNow()
    //     }

    //     $.ajax({
    //         type: 'post',
    //         url: 'http://10.15.5.150/dev/sd/sdonline/conn_api/list_dist.php',
    //         dataType: "json",
    //         data: JSON.stringify(body),
    //         timeout: 3000,
    //         success: function (data) {
    //             let listSuppliers = data.data
    //             let index = listSuppliers.findIndex((x) => {
    //                 return x.KODE == supplierCode
    //             })
    //             if (index == -1){
    //                 hideNewOrderCSMS()
    //             } else {
    //                 supplierCSMS = listSuppliers[index]
    //                 showNewOrderCSMS()
    //             }
    //         },
    //         error: function (request, status, error) {
    //             bootbox.alert('Cannot connect to CSMS right now');
    //             hideNewOrderCSMS()
    //         }
    //     });
    // }

        /* function getRequestCSMS(){
            let body = {
                ORG : getInitialSupplier($("#posupplier" ).val()),
                DIST : "<?= sprintf('%010d', $company->cf1); ?>",
                REQUEST_TIME : getDateNow()
            }

            $.ajax({
                type: 'post',
                url: 'http://10.15.5.150/dev/sd/sdonline/conn_api/mst_datapp.php',
                dataType: "json",
                data: JSON.stringify(body),
                timeout: 3000,
                success: function (data) {
    //                    console.log(data)
                    csmsResponse = data 
                    setOrderType(data.data.TIPE_ORDER)
                    setShippingType(data.data.JENIS_PENGIRIMAN)
                    setPackagingType(data.data.JENIS_KEMASAN)
                    setPriceList(data.data.PRICE_LIST)
                },
                error: function (request, status, error) {
                    csmsResponse = {}
                    $('#csmsMessage').html('<strong>Error!</strong> Cannot connected to CSMS server.')
                    $('#csmsMessage').show()
                    $("#orderSubmit").attr("disabled", "disabled")
                }
            });
        } */

    //     function setProducts(){
    //         productsFromSupplier = []
    //         let iPackageType = $("#packaging_type").find('option:selected').index();
    //         let iPlant = $("#plant").find('option:selected').index()
    //         let iCityProvince = $("#city_province").find('option:selected').index()
    // //        console.log(iPackageType, iPlant, iCityProvince)
    //         if (csmsResponse.data.JENIS_KEMASAN){
    //             if(csmsResponse.data.JENIS_KEMASAN[iPackageType].PLANT){
    //                 if(csmsResponse.data.JENIS_KEMASAN[iPackageType].PLANT[iPlant].KOTA_PROVINSI){
    //                     if(csmsResponse.data.JENIS_KEMASAN[iPackageType].PLANT[iPlant].KOTA_PROVINSI[iCityProvince].DAFTAR_PRODUK){
    //                         productsFromSupplier = csmsResponse.data.JENIS_KEMASAN[iPackageType].PLANT[iPlant].KOTA_PROVINSI[iCityProvince].DAFTAR_PRODUK
    //                     }
    //                 }
    //             }
    //         }
    //     }

    /* function setRemainingQuota(date){
        let body = {
            ORG : getInitialSupplier($("#posupplier" ).val()),
            JKEMASAN : $("#packaging_type" ).val(),
            DIST : "<?= sprintf('%010d', $company->cf1); ?>",
            DISTRIK : $("#city_province" ).val(),
            PLANT : $("#plant" ).val(),
            TGL_KIRIM : date,
            REQUEST_TIME : getDateNow(),
        }
           console.log("data rem : ", body)
    $.ajax({
        type: 'post',
        url: 'http://10.15.5.150/dev/sd/sdonline/conn_api/mst_sisa_jatah.php',
        dataType: "json",
        data: JSON.stringify(body),
        timeout: 3000,
        success: function (data) {
                   console.log(":sisa jatah", data)
                if(data.data){
                    let quota = data.data[0].SISA_JATAH
                    $("#remaining_quota").val(quota).change()
                }else{
                    $("#remaining_quota").val("0").change()
                }
            },
            error: function (request, status, error) {
                console.error("remaining quota : ", error)
                $("#remaining_quota").val("0").change()
            }
        });
    } */

    /* function setIntegratedWarehouse (){
        let body = {
            ORG : getInitialSupplier($("#posupplier" ).val()),
            KODE_PRICELIST : $("#price_list" ).val(),
            DIST : "<?= sprintf('%010d', $company->cf1); ?>",
            DISTRIK : $("#city_province" ).val(),
            PLANT : $("#plant").val(),
            REQUEST_TIME : getDateNow(),
        }
       console.log("data toko : ", body)
        $.ajax({
            type: 'post',
            url: 'http://10.15.5.150/dev/sd/sdonline/conn_api/list_toko.php',
            dataType: "json",
            data: JSON.stringify(body),
            timeout: 3000,
            success: function (data) {
               console.log(":toko", data)
                if(data.data){
                    let integratedWarehouses = data.data
                    var html = ''
                    for(iw of integratedWarehouses){
                        let index = warehouses.findIndex((x) => {
                            return x.code === iw.KODE_TOKO
                        })
                        if (index != -1){
                            html += '<option value="'+iw.KODE_TOKO+'">'+iw.NAMA_TOKO+' - ' + iw.ALAMAT_TOKO + '</option>'
                        }
                    }
                    $("#integrated_warehouse").html(html);
                    $("#integrated_warehouse").change(function() {
                        let code = $(this).val()
                        let index = warehouses.findIndex((x) => {
                            return x.code === code
                        })
                        if (index != -1){
                            $("#powarehouse").val(warehouses[index].id).change()
                        }
                    })
                    $("#integrated_warehouse").val(integratedWarehouses[0].KODE_TOKO).change()
                }else{
                    $("#integrated_warehouse").html('<option value="nodata">No data</option>');
                    $("#integrated_warehouse").val('nodata').change()
                }

            },
            error: function (request, status, error) {
                console.error("remaining quota : ", error)
                $("#remaining_quota").val("0").change()
            }
        });
    } */

   /*  function loginApix(supplier_code){
        $.ajax({
            type: 'post',
            url: "<?= $this->APIXLink ?>api/v1/login",
            data : {
                'username' : "<?= $company->cf1 ?>",
                'password' : supplier_code
            },
            timeout: 3000,
            success: function (data) {
                showNewOrderApix()
                localStorage.setItem("apixToken", data.data.token)
            },
            error: function (request, status, error) {
                console.error(request, ' Login something wrong : ', error)
                if(request.status != 400)
                bootbox.alert('Cannot connect to APIX right now');
                hideNewOrderApix()
            }
        });
    } */

    /* function getProductsApix(token){
        $.ajax({
            type: 'get',
            url: "<?= $this->APIXLink ?>api/v1/product",
            timeout: 3000,
            headers: {
                'Authorization' : 'Bearer ' + token,
            },
            success: function (data) {
                productsFromSupplier = data.data.product_detail
            },
            error: function (request, status, error) {
                console.error('Get products something wrong : ' + error)
                alert("Cannot get product")
                $('#poorderapix').iCheck("uncheck")
            }
        });
    }
 */
    // function compareProduct(addedItem){
    //     if($('#poorderapix').is(':checked')){
    //         let code = addedItem.row.code
    //         let index = productsFromSupplier.findIndex((x) => {
    //             return x.product_code == code
    //         })
    //         if (index == -1){
    //             return false
    //         }

    //         return true
    //     } else if($('#poorder').is(':checked')){
    //         let code = addedItem.row.code
    //         let index = productsFromSupplier.findIndex((x) => {
    //             return x.KD_PRODUK == code
    //         })
    //         if (index == -1){
    //             return false
    //         }

    //         addedItem.csms = productsFromSupplier[index]

    //         return true
    //     }

    //     return true
    // }

    /* function createOrderCSMS(){
        freeToLeave = false;

        let body = {
            request_time : getDateNow(),
            org  : getInitialSupplier($("#posupplier" ).val()),
            dist_name  : "DIST<?= trim($company->cf1) ?>",
            jenis_kirim  : $("#shipping_type").val(),
            kode_dist  : supplierCSMS.KODE,
            nama_dist  : supplierCSMS.NAMA,
            route  : $("#shipping_routes").val(),
            kode_plant  : $("#plant").val(),
            nama_plant  : $("#plant").find(":selected").text(),
            so_type : $("#order_type").val(),
            nama_so_type : $("#order_type").find(":selected").text(),
            kode_top  : $("#payment_method").val(),
            nama_top : $("#payment_method").find(":selected").text(),
            kode_pricelist  : $("#price_list").val(),
            nama_pricelist  : $("#price_list").find(":selected").text(),
            jumlah : "0",
        }
        let counter = 0
        let qtyCounter = 0

        for (var key in poitems) {
            if (poitems.hasOwnProperty(key)) {
                counter++
                let toko = ($("#integrated_warehouse").find(":selected").text()).split(" - ", 2)
                let item = poitems[key]
                body["kode_toko"+counter] = $("#integrated_warehouse").val()
                body["nama_toko"+counter] = toko[0]
                body["alamat_toko"+counter] = toko[1]
                body["kode_distrik"+counter] = $("#city_province").val()
                body["nama_distrik"+counter] = $("#city_province").find(":selected").text()
                body["kode_produk"+counter] = item.csms.KD_PRODUK
                body["nama_produk"+counter] = item.csms.NM_PRODUK
                body["qty"+counter] = item.row.qty
                body["uom"+counter] = item.csms.UNIT
                qtyCounter += item.row.qty
            }
        }
        body.jumlah = counter.toString()

        console.log(body)
        if(qtyCounter > $("#remaining_quota").val()){
            bootbox.alert('Purchased quantity can not higher than Remaining Quota');
        }else if (counter == 0) {
            bootbox.alert('Add items please.');
        }else{
            $("#csmsLoaderModal").modal("show")
            $.ajax({
                type: 'post',
                url: "http://10.15.5.150/dev/sd/sdonline/conn_api/mst_insertpp.php",
                timeout: 3000,
                dataType: "json",
                data: JSON.stringify(body),
                success: function (data) {
                    freeToLeave = true
                    let message = (data.data[0].Message_hdr[0]).split(" No. ", 2)
                    $("#csmsLoaderModal").modal("hide")                    
                    $("#cf1").val(message[1]).change()                    
                    $('#add_pruchase').removeAttr("disabled")      
                    $('#add_pruchase').click()
                },
                error: function (request, status, error) {
                    freeToLeave = false
                    $("#csmsLoaderModal").modal("hide")
                    console.error('Order CSMS something wrong : ' + error)
                }
            });    
        } */


    // }

    /* function createOrderApix(){
        freeToLeave = false;

        let body = {
            order_code : getOrderCodeUnique(),
            buyer_name : "<?= trim($company->name) ?>",
            buyer_address : "<?= trim($company->address) ?>",
            request_time : getDateNow(),
            delivery_time : getDateNow(),
            total_price : 0,
            payment_method : "Cash On Delivery",
            product_details : []
        }

        let counter = 0

        for (var key in poitems) {
            if (poitems.hasOwnProperty(key)) {
                counter++
                let item = poitems[key]
                body.product_details.push({
                    product_name : item.row.name,
                    sku : item.row.code,
                    unit_price : 0,
                    quantity : item.row.qty
                })
            }
        }

        if(counter == 0) {
            bootbox.alert('Add items please.');
        } else {
            $("#apixLoaderModal").modal("show")
            $.ajax({
                type: 'post',
                url: "<?= $this->APIXLink ?>api/v1/order",
                timeout: 3000,
                headers: {
                    'Authorization' : 'Bearer ' + localStorage.getItem("apixToken"),
                },
                dataType: "json",
                data: body,
                success: function (data) {
                    freeToLeave = true
                    $("#apixLoaderModal").modal("hide")                    
                    $("#cf1").val(data.data.order_code).change()                    
                    $('#add_pruchase').removeAttr("disabled")      
                    $('#add_pruchase').click()
                },
                error: function (request, status, error) {
                    freeToLeave = false
                    $("#apixLoaderModal").modal("hide")
                    console.error('Order something wrong : ' + error)
                }
            });
        } */


    // }

    // function getDateNow(){
    //     let date = new Date()
    //     let now = date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate() + " "
    //     now += date.getHours() + ":" + date.getMinutes() + ":" + date.getSeconds()
    //     return now;
    // }

    /* function getOrderCodeUnique(){
        let date = new Date()
        let code = "PO_POS_<?= $company->company_id ?>_" + date.getTime()
        return code
    } */


    // function hideNewOrderCSMS(){
    //     $('#newOrderGroup').hide()
    //     $('#poorder').iCheck("uncheck")
    //     $('#poorder').iCheck("disable")
    // }

    // function showNewOrderCSMS(){
    //     $('#newOrderGroup').show()
    //     $('#poorder').iCheck("enable")
    // }

    // function hideNewOrderApix(){
    //     $('#newOrderApixGroup').hide()
    //     $('#poorderapix').iCheck("uncheck")
    //     $('#poorderapix').iCheck("disable")
    // }

    // function showNewOrderApix(){
    //     $('#newOrderApixGroup').show()
    //     $('#poorderapix').iCheck("enable")
    // }

    // function setOrderType(orderType) {
    //     if(orderType){
    //         var html = ''
    //         for(ot of orderType){
    //             html += '<option value="'+ot.KD_TIPEORDER+'">'+ot.NM_TIPEORDER+'</option>'
    //         }
    //         $("#order_type").html(html);
    //         $("#order_type").val(orderType[0].KD_TIPEORDER).change()
    //     }else{
    //         $("#order_type").html('<option value="nodata">No data</option>');
    //         $("#order_type").val('nodata').change()
    //     }

    // }

    // function setShippingType(shippingType) {
    //     if(shippingType){
    //         var html = ''
    //         for(st of shippingType){
    //             html += '<option value="'+st.KODE_JNSPENGIRIMAN+'">'+st.NAMA_JNSPENGIRIMAN+'</option>'
    //         }
    //         $("#shipping_type").html(html);
    //         $("#shipping_type").change(function() {
    //             setShippingRoutes(shippingType[$(this).find('option:selected').index()].ROUTE)
    //         })
    //         $("#shipping_type").val(shippingType[0].KODE_JNSPENGIRIMAN).change()
    //     }else{
    //         $("#shipping_type").html('<option value="nodata">No data</option>');
    //         $("#shipping_type").val('nodata').change()

    //         setShippingRoutes(null)
    //     }

    // }

    // function setShippingRoutes(shippingRoutes) {
    //     if(shippingRoutes){
    //         var html = ''
    //         for(sr of shippingRoutes){
    //             html += '<option value="'+sr.KODE_ROUTE+'">'+sr.NAMA_ROUTE+'</option>'
    //         }
    //         $("#shipping_routes").html(html);
    //         $("#shipping_routes").val(shippingRoutes[0].KODE_ROUTE).change()       
    //     }else{
    //         $("#shipping_routes").html('<option value="nodata">No data</option>');
    //         $("#shipping_routes").val('nodata').change()
    //     }

    // }

    // function setPackagingType(packagingType) {
    //     if(packagingType){
    //         var html = ''
    //         for(pt of packagingType){
    //             html += '<option value="'+pt.KD_KEMASAN+'">'+pt.NM_KEMASAN+'</option>'
    //         }
    //         $("#packaging_type").html(html);
    //         $("#packaging_type").change(function() {
    //             setPlant(packagingType[$(this).find('option:selected').index()].PLANT)
    //         })            
    //         $("#packaging_type").val(packagingType[0].KD_KEMASAN).change()
    //     }else{
    //         $("#packaging_type").html('<option value="nodata">No data</option>');
    //         $("#packaging_type").val('nodata').change()

    //         setPlant(null)
    //     }
    // }

    // function setPlant(plant) {
    //     if(plant){
    //         var html = ''
    //         for(p of plant){
    //             html += '<option value="'+p.KODE_PLANT+'">'+p.NAMA_PLANT+'</option>'
    //         }
    //         $("#plant").html(html);
    //         $("#plant").change(function() {
    //             setCityProvince(plant[$(this).find('option:selected').index()].KOTA_PROVINSI)
    //         })
    //         $("#plant").val(plant[0].KODE_PLANT).change()
    //     }else{
    //         $("#plant").html('<option value="nodata">No data</option>');
    //         $("#plant").val('nodata').change()

    //         setCityProvince(null)
    //     }
    // }

    // function setCityProvince(cityProvince) {
    //     if(cityProvince){
    //         var html = ''
    //         for(cp of cityProvince){
    //             html += '<option value="'+cp.KD_KOTA+'">'+cp.NM_KOTA+'</option>'
    //         }
    //         $("#city_province").html(html);
    //         $("#city_province").val(cityProvince[0].KD_KOTA).change()

    //     }else{
    //         $("#city_province").html('<option value="nodata">No data</option>');
    //         $("#city_province").val('nodata').change()
    //     }
    // }

    // function setPriceList(priceList){
    //     if(priceList){
    //         var html = ''
    //         for(pl of priceList){
    //             html += '<option value="'+pl.HARGA_KEY+'">'+pl.HARGA_DESC+'</option>'
    //         }
    //         $("#price_list").html(html);
    //         $("#price_list").val(priceList[0].HARGA_KEY).change()
    //     } else {
    //         $("#price_list").html('<option value="nodata">No data</option>');
    //         $("#price_list").val('nodata').change()
    //     }
    // }

    /* function setPaymentMethod(){

        let supplierCode = "<?= $company->cf1 ?>".padStart(10, 0);

        let body = {
            ORG : getInitialSupplier($("#posupplier" ).val()),
            JKEMASAN : $("#packaging_type" ).val(),
            DIST : supplierCode,
            DISTRIK : $("#city_province" ).val(),
            ROUTE : $("#shipping_routes" ).val(),
            SHIPTO : "",
            PLANT : $("#plant" ).val(),
            REQUEST_TIME : getDateNow(),
        }
        $.ajax({
            type: 'POST',
            url: "http://10.15.5.150/dev/sd/sdonline/conn_api/mst_top.php",
            timeout: 3000,
            dataType: "json",
            data: JSON.stringify(body),
            success: function (data) {
                let paymentMethod = data.data
                if(paymentMethod){
                    var html = ''
                    for(pm of paymentMethod){
                        html += '<option value="'+pm.KODE_TOP+'">'+pm.DESC+'</option>'
                    }
                    $("#payment_method").html(html);
                    $("#payment_method").val(paymentMethod[0].KODE_TOP).change()
                } else {
                    $("#payment_method").html('<option value="nodata">No data</option>');
                    $("#payment_method").val('nodata').change()
                }
            },
            error: function (request, status, error) {
                $("#payment_method").html('<option value="nodata">No data</option>');
                $("#payment_method").val('nodata').change()
                console.error('Method payment something wrong : ' + error)
            }
        });
    } */

    // function getWarehouses(){
    //     $.ajax({
    //         type: 'GET',
    //         url: site.base_url + "system_settings/getWarehousesJson",
    //         timeout: 3000,
    //         dataType: "json",
    //         success: function (data) {
    //             warehouses = data
    //         },
    //         error: function (request, status, error) {
    //             warehouses = []
    //         }
    //     });
    // }

    // END Integrasi POS -> CSMS, APIX

    var tour = {
        id: "guide-purchases-add",
        onClose: function() {
            complete_guide('purchases-add');
        },
        onEnd: function() {
            complete_guide('purchases-add');
        },
        steps: [
            <?php if ($Owner || $Admin || $LT) { ?> {
                    title: "Tanggal",
                    content: "Silahkan isi tanggal transaksi",
                    target: "podate",
                    placement: "top"
                },
            <?php } ?> {
                title: "Status",
                content: "<b>Ordered</b> :Produk belum dikirim, stok belum bertambah<br><b>Received</b> :Produk datang, stok sudah bertambah",
                target: "s2id_postatus",
                placement: "top"
            },
            {
                title: "Pilih Produk",
                content: "Ketikkan nama atau kode produk yang ingin dijual",
                target: "add_item",
                placement: "top"
            },
            {
                title: "Jumlah Produk",
                content: "Sesuaikan jumlah produk yang dijual",
                target: "qty_item",
                placement: "top"
            },
            {
                title: "Surat Jalan",
                content: "Silahkan isi surat jalan disini",
                target: "no_si_spj",
                placement: "top"
            },
            {
                title: "Nomor Pengiriman",
                content: "Isikan nomor pengiriman",
                target: "no_si_do",
                placement: "top"
            },
            {
                title: "Nomor Sales Order",
                content: "Isikan nomor sales order",
                target: "no_si_so",
                placement: "top"
            },
            {
                title: "Nomor Billing",
                content: "Isikan nomor billing",
                target: "no_si_billing",
                placement: "top"
            },
            {
                title: "Tanggal Pengiriman",
                content: "Isikan Tanggal pengiriman",
                target: "delivery_date",
                placement: "top"
            },
        ]
    };
</script>

<style>
    .selectFullWidth {
        width: 100% !important;
    }
</style>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_purchase'); ?></h2>
        <div class="box-icon">
            <?php echo anchor($mb_add_purchase, '<i class="icon fa fa-book tip" data-placement="left" title="' . lang("manual_book") . '"></i> ', 'target="_blank"') ?>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo form_open_multipart("purchases/add", $attrib)
                ?>


                <div class="row">
                    <div class="col-lg-12">

                        <?php if ($Owner || $Admin || $LT) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("date", "podate"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="podate" required="required"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("reference_no", "poref"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $ponumber), 'class="form-control input-tip" id="poref" readonly="readonly"'); ?>
                            </div>
                        </div>
                        <?php if ($Owner || $Admin || !$this->session->userdata('warehouse_id') || $LT) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("warehouse", "powarehouse"); ?>
                                    <?php
                                    $wh[''] = '';
                                    foreach ($warehouses as $warehouse) {
                                        $wh[$warehouse->id] = $warehouse->name.'|'.$warehouse->code.'|'.$warehouse->address;
                                    }
                                    echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $this->session->userdata('warehouse_id')), 'id="powarehouse" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("warehouse") . '" required="required" style="width:100%;" ');
                                    ?>
                                </div>
                            </div>
                        <?php } else {
                            $warehouse_input = array(
                                'type' => 'hidden',
                                'name' => 'warehouse',
                                'id' => 'slwarehouse',
                                'value' => $this->session->userdata('warehouse_id'),
                            );

                            echo form_input($warehouse_input);
                        } ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("status", "postatus"); ?>
                                <?php
                                // $post = array('received' => lang('received'), 'pending' => lang('pending'), 'ordered' => lang('ordered'));
                                $post = array('received' => lang('received'), 'pending' => lang('pending'));
                                echo form_dropdown('status', $post, (isset($_POST['status']) ? $_POST['status'] : ''), 'id="postatus" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("status") . '" required="required" style="width:100%;" ');
                                ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("document", "document") ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false" data-show-preview="false" class="form-control file">
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="panel panel-warning">
                                <div class="panel-heading"><?= lang('please_select_these_before_adding_product') ?></div>
                                <div class="panel-body" style="padding: 5px;">
                                    <div class="row" style="padding: 0 10px;">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang("supplier", "posupplier"); ?>
                                                <?php if ($Owner || $Admin || $GP['suppliers-add'] || $GP['suppliers-index']) { ?><div class="input-group"><?php } ?>
                                                    <input type="hidden" name="supplier" value="" id="posupplier" class="form-control" style="width:100%;" placeholder="<?= lang("select") . ' ' . lang("supplier") ?>">
                                                    <input type="hidden" name="supplier_id" value="" id="supplier_id" class="form-control">
                                                    <?php if ($Owner || $Admin || $GP['suppliers-index']) { ?>
                                                        <div class="input-group-addon no-print" style="padding: 2px 5px; border-left: 0;">
                                                            <a href="#" id="view-supplier" class="external" data-toggle="modal" data-target="#myModal" data-backdrop="static">
                                                                <i class="fa fa-2x fa-user" id="addIcon"></i>
                                                            </a>
                                                        </div>
                                                    <?php } ?>
                                                    <?php if ($Owner || $Admin || $GP['suppliers-add']) { ?>
                                                        <div class="input-group-addon no-print" style="padding: 2px 5px;">
                                                            <a href="<?= site_url('suppliers/add'); ?>" id="add-supplier" class="external" data-toggle="modal" data-target="#myModal" data-backdrop="static">
                                                                <i class="fa fa-2x fa-plus-circle" id="addIcon"></i>
                                                            </a>
                                                        </div>
                                                    <?php } ?>
                                                    <?php if ($Owner || $Admin || $GP['suppliers-add'] || $GP['suppliers-index']) { ?></div><?php } ?>
                                            </div>
                                            <!-- <div class="form-group" id="newOrderGroup">
                                                <input type="checkbox" class="checkbox" name="poorder" id="poorder" value="" />
                                                <label for="poorder" class="padding05"> New Order to CSMS </label>
                                            </div>
                                            <div class="form-group" id="newOrderApixGroup">
                                                <input type="checkbox" class="checkbox" name="poorderapix" id="poorderapix" value="" />
                                                <label for="poorderapix" class="padding05"> New Order to POS Supplier</label>
                                            </div> -->
                                        </div>
                                    </div>
                                    <div class="row" id="newOrderExtrasGroup" style="padding: 0 10px; display: none;">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="payment_method" class="control-label">Payment Method *</label>
                                                <select name="payment_method" id="payment_method" class="selectFullWidth">
                                                    <option value="nodata">No data</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="delivery_order_date" class="control-label">Delivery Order Date *</label>
                                                <input type="text" name="delivery_order_date" id="delivery_order_date" class="form-control input-tip date selectFullWidth" data-trigger="focus" data-placement="top" title="Delivery Order Date">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="remaining_quota" class="control-label">Remaining Quota</label>
                                                <input type="text" value="0" name="remaining_quota" readonly id="remaining_quota" class="form-control selectFullWidth">
                                                <span><i style="color:red;"><sup>Set Delivery Order Date to set this</sup></i></span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="integrated_warehouse" class="control-label">Integrated Warehouse *</label>
                                                <select name="integrated_warehouse" id="integrated_warehouse" class="selectFullWidth">
                                                    <option value="nodata">No data</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>


                        <div class="col-md-12" id="sticker">
                            <div class="well well-sm">
                                <div class="form-group" style="margin-bottom:0;">
                                    <div class="input-group wide-tip">
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <i class="fa fa-2x fa-barcode addIcon"></i></a></div>
                                        <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . $this->lang->line("add_product_to_order") . '"'); ?>
                                        <!-- <?php if ($Owner || $Admin || $GP['products-add']) { ?>
                                            <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                                <a href="<?= site_url('products/add') ?>" id="addManually1"><i class="fa fa-2x fa-plus-circle addIcon" id="addIcon"></i></a></div>
                                        <?php } ?> -->
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("order_items"); ?></label>

                                <div class="controls table-controls">
                                    <table id="poTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                        <thead>
                                            <tr>
                                                <th class="col-md-4"><?= lang("product_name") . " (" . $this->lang->line("product_code") . ")"; ?></th>
                                                <?php
                                                if ($Settings->product_expiry) {
                                                    echo '<th class="col-md-2">' . $this->lang->line("expiry_date") . '</th>';
                                                }
                                                ?>
                                                <th class="col-md-1"><?= lang("net_unit_cost"); ?></th>
                                                <th class="col-md-1" id="qty_item"><?= lang("quantity"); ?></th>
                                                <?php
                                                if ($Settings->product_discount) {
                                                    echo '<th class="col-md-1">' . $this->lang->line("discount") . '</th>';
                                                }
                                                ?>
                                                <?php
                                                if ($Settings->tax1) {
                                                    echo '<th class="col-md-1">' . $this->lang->line("product_tax") . '</th>';
                                                }
                                                ?>
                                                <th><?= lang("subtotal"); ?> (<span class="currency"><?= $default_currency->code ?></span>)
                                                </th>
                                                <th style="width: 30px !important; text-align: center;"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot></tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <input type="hidden" name="total_items" value="" id="total_items" required="required" />
                        <?php if ($quote_id) { ?>
                            <div class="col-md-12" style="margin-bottom:15px;">
                                <?php echo lang("* Note") . " : The price may be not appropriate, Because data prices are adjusted to prices in FORCA POS <br>"; ?>
                            </div>
                        <?php } ?>
                        <div class="col-md-12">
                            <div class="form-group">
                                <input type="checkbox" class="checkbox" id="extras" value="" />
                                <label for="extras" class="padding05"><?= lang('more_options') ?></label>
                            </div>
                            <div class="row" id="extras-con" style="display: none;">
                                <?php if ($Settings->tax1) { ?>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang('order_tax', 'potax2') ?>
                                            <?php
                                            $tr[""] = "";
                                            foreach ($tax_rates as $tax) {
                                                $tr[$tax->id] = $tax->name;
                                            }
                                            echo form_dropdown('order_tax', $tr, "", 'id="potax2" class="form-control input-tip select" style="width:100%;"');
                                            ?>
                                        </div>
                                    </div>
                                <?php } ?>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang("discount_label", "podiscount"); ?>
                                        <?php echo form_input('discount', '', 'class="form-control input-tip" id="podiscount"'); ?>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang("Shipping_price", "poshipping"); ?>
                                        <?php echo form_input('shipping_price', '', 'class="form-control input-tip" id="poshipping"'); ?>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang("payment_term", "popayment_term"); ?>
                                        <?php echo form_input('payment_term', '', 'class="form-control tip" data-trigger="focus" data-placement="top" title="' . lang('payment_term_tip') . '" id="popayment_term"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">

                                    <div class="form-group">
                                        <?= lang("no_si_spj", "no_si_spj"); ?>
                                        <?php echo form_input('no_si_spj', '', 'class="form-control tip" data-trigger="focus" data-placement="top" title="' . lang('no_si_spj') . '" id="no_si_spj" placeholder="000"'); ?>
                                        <span><i style="color:red;"><sup>Contoh :2004123350</sup></i></span>
                                    </div>

                                </div>

                                <div class="col-md-4">

                                    <div class="form-group">
                                        <?= lang("no_si_do", "no_si_do"); ?>
                                        <?php echo form_input('no_si_do', '', 'class="form-control tip" data-trigger="focus" data-placement="top" title="' . lang('no_si_do') . '" id="no_si_do" placeholder="000"'); ?>
                                        <span><i style="color:red;"><sup>Contoh : 0704361940</sup></i></span>
                                    </div>

                                </div>
                                <div class="col-md-4">

                                    <div class="form-group">
                                        <?= lang("no_si_so", "no_si_so"); ?>
                                        <?php echo form_input('no_si_so', '', 'class="form-control tip" data-trigger="focus" data-placement="top" title="' . lang('no_si_so') . '" id="no_si_so" placeholder="000"'); ?>
                                        <span><i style="color:red;"><sup>Contoh : 0055017439</sup></i></span>
                                    </div>

                                </div>
                                <div class="col-md-4">

                                    <div class="form-group">
                                        <?= lang("no_si_billing", "no_si_billing"); ?>
                                        <?php echo form_input('no_si_billing', '', 'class="form-control tip" data-trigger="focus" data-placement="top" title="' . lang('no_si_billing') . '" id="no_si_billing" placeholder="000"'); ?>
                                        <span><i style="color:red;"><sup>Contoh : 0012345678</sup></i></span>
                                    </div>

                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang("delivery_date", "delivery_date"); ?>
                                        <?php echo form_input('delivery_date', '', 'class="form-control input-tip datetime" data-trigger="focus" data-placement="top" title="' . lang('delivery_date') . '" id="delivery_date" placeholder=""'); ?>
                                        <span><i style="color:red;"><sup>&nbsp;</sup></i></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang("receiver", "acceptor"); ?>
                                        <?php echo form_input('acceptor', '', 'class="form-control tip" data-trigger="focus" data-placement="top" title="' . lang('receiver') . '" id="receiver" placeholder=""'); ?>
                                        <span><i style="color:red;"><sup>&nbsp;</sup></i></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang("license_plate", "license_plate_label"); ?>
                                        <?php echo form_input('license_plate', '', 'class="form-control tip" data-trigger="focus" data-placement="top" title="' . lang('license_plate') . '" id="license_plate" placeholder=""'); ?>
                                        <span><i style="color:red;"><sup>Example: B 1234 QW </sup></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="form-group">
                                <?= lang("note", "ponote"); ?>
                                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="ponote" style="margin-top: 10px; height: 100px;"'); ?>
                            </div>

                        </div>
                        <div id="custom_fields" style="display: none;">
                            <div class="col-md-6">
                                <div class="from-group">
                                    <label for="cf1">No. Reference from Supplier</label>
                                    <input type="text" readonly class="form-control" value="" id="cf1" name="cf1">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="from-group">
                                    <label for="cf2">Supplier Source</label>
                                    <input type="text" readonly class="form-control" value="" id="cf2" name="cf2">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">

                            <div class="from-group">
                                <button type="button" class="btn btn-warning" id="submitCSMS" style="display: none;">Send to CSMS</button>
                                <button type="button" class="btn btn-warning" id="submitAPIX" style="display: none;">Send to POS Supplier</button>
                                <?php echo form_submit('add_pruchase', $this->lang->line("submit"), 'id="add_pruchase" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="bottom-total" class="well well-sm" style="margin-bottom: 0;">
                    <table class="table table-bordered table-condensed totals" style="margin-bottom:0;">
                        <tr class="warning">
                            <td><?= lang('items') ?> <span class="totals_val pull-right" id="titems">0</span></td>
                            <td><?= lang('total') ?> <span class="totals_val pull-right" id="total">0.00</span></td>
                            <td><?= lang('order_discount') ?> <span class="totals_val pull-right" id="tds">0.00</span></td>
                            <?php if ($Settings->tax2) { ?>
                                <td><?= lang('order_tax') ?> <span class="totals_val pull-right" id="ttax2">0.00</span></td>
                            <?php } ?>
                            <td><?= lang('shipping') ?> <span class="totals_val pull-right" id="tship">0.00</span></td>
                            <td><?= lang('grand_total') ?> <span class="totals_val pull-right" id="gtotal">0.00</span></td>
                        </tr>
                    </table>
                </div>

                <?php echo form_close(); ?>

            </div>

        </div>
    </div>
</div>

<div class="modal" id="prModal" tabindex="-1" role="dialog" aria-labelledby="prModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-2x">&times;</i></span><span class="sr-only"><?= lang('close'); ?></span></button>
                <h4 class="modal-title" id="prModalLabel"></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                    <?php if ($Settings->tax1) { ?>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?= lang('product_tax') ?></label>
                            <div class="col-sm-8">
                                <?php
                                $tr[""] = "";
                                foreach ($tax_rates as $tax) {
                                    $tr[$tax->id] = $tax->name;
                                }
                                echo form_dropdown('ptax', $tr, "", 'id="ptax" class="form-control pos-input-tip" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="pquantity" class="col-sm-4 control-label"><?= lang('quantity') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pquantity">
                        </div>
                    </div>
                    <?php if ($Settings->product_expiry) { ?>
                        <div class="form-group">
                            <label for="pexpiry" class="col-sm-4 control-label"><?= lang('product_expiry') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control date" id="pexpiry">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="punit" class="col-sm-4 control-label"><?= lang('product_unit') ?></label>
                        <div class="col-sm-8">
                            <div id="punits-div"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="poption" class="col-sm-4 control-label"><?= lang('product_option') ?></label>
                        <div class="col-sm-8">
                            <div id="poptions-div"></div>
                        </div>
                    </div>
                    <?php if ($Settings->product_discount) { ?>
                        <div class="form-group">
                            <label for="pdiscount" class="col-sm-4 control-label"><?= lang('product_discount') ?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pdiscount">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="pcost" class="col-sm-4 control-label"><?= lang('unit_cost') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pcost">
                        </div>
                    </div>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?= lang('net_unit_cost'); ?></th>
                            <th style="width:25%;"><span id="net_cost"></span></th>
                            <th style="width:25%;"><?= lang('product_tax'); ?></th>
                            <th style="width:25%;"><span id="pro_tax"></span></th>
                        </tr>
                    </table>
                    <div class="panel panel-default">
                        <div class="panel-heading"><?= lang('calculate_unit_cost'); ?></div>
                        <div class="panel-body">

                            <div class="form-group">
                                <label for="pcost" class="col-sm-4 control-label"><?= lang('subtotal') ?></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="psubtotal">
                                        <div class="input-group-addon" style="padding: 2px 8px;">
                                            <a href="#" id="calculate_unit_price" class="tip" title="<?= lang('calculate_unit_cost'); ?>">
                                                <i class="fa fa-calculator"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="punit_cost" value="" />
                    <input type="hidden" id="old_tax" value="" />
                    <input type="hidden" id="old_qty" value="" />
                    <input type="hidden" id="old_cost" value="" />
                    <input type="hidden" id="row_id" value="" />
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editItem"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="mModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-2x">&times;</i></span><span class="sr-only"><?= lang('close'); ?></span></button>
                <h4 class="modal-title" id="mModalLabel"><?= lang('add_standard_product') ?></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <div class="alert alert-danger" id="mError-con" style="display: none;">
                    <!--<button data-dismiss="alert" class="close" type="button">&times;</button>-->
                    <span id="mError"></span>
                </div>
                <div class="row">
                    <div class="col-md-6 col-sm-6">
                        <div class="form-group">
                            <?= lang('product_code', 'mcode') ?> *
                            <input type="text" class="form-control" id="mcode">
                        </div>
                        <div class="form-group">
                            <?= lang('product_name', 'mname') ?> *
                            <input type="text" class="form-control" id="mname">
                        </div>
                        <div class="form-group">
                            <?= lang('category', 'mcategory') ?> *
                            <?php
                            $cat[''] = "";
                            foreach ($categories as $category) {
                                $cat[$category->id] = $category->name;
                            }
                            echo form_dropdown('category', $cat, '', 'class="form-control select" id="mcategory" placeholder="' . lang("select") . " " . lang("category") . '" style="width:100%"')
                            ?>
                        </div>
                        <div class="form-group">
                            <?= lang('unit', 'munit') ?> *
                            <input type="text" class="form-control" id="munit">
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <div class="form-group">
                            <?= lang('cost', 'mcost') ?> *
                            <input type="text" class="form-control" id="mcost">
                        </div>
                        <div class="form-group">
                            <?= lang('price', 'mprice') ?> *
                            <input type="text" class="form-control" id="mprice">
                        </div>

                        <?php if ($Settings->tax1) { ?>
                            <div class="form-group">
                                <?= lang('product_tax', 'mtax') ?>
                                <?php
                                $tr[""] = "";
                                foreach ($tax_rates as $tax) {
                                    $tr[$tax->id] = $tax->name;
                                }
                                echo form_dropdown('mtax', $tr, "", 'id="mtax" class="form-control input-tip select" style="width:100%;"');
                                ?>
                            </div>
                            <div class="form-group all">
                                <?= lang("tax_method", "mtax_method") ?>
                                <?php
                                $tm = array('0' => lang('inclusive'), '1' => lang('exclusive'));
                                echo form_dropdown('tax_method', $tm, '', 'class="form-control select" id="mtax_method" placeholder="' . lang("select") . ' ' . lang("tax_method") . '" style="width:100%"')
                                ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="addItemManually"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade in" id="expModal" tabindex="-1" role="dialog" aria-labelledby="expModalLabel" aria-hidden="true"></div>

<!-- <div class="modal" id="csmsModal" tabindex="-1" role="dialog" aria-labelledby="csmsModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="csmsModalLabel">New Order to CSMS</h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <div class="alert alert-danger" id="csmsMessage">
                    <strong>Error!</strong> Cannot connected to CSMS server.
                </div>
                <form class="form-horizontal" role="form">
                    <div class="form-group">
                        <label for="order_type" class="col-sm-4 control-label">Order Type *</label>
                        <div class="col-sm-8"> -->
<!--<input type="text" name="order_type" id="order_type" class="selectFullWidth"/>-->
<!-- <select name="order_type" id="order_type" class="selectFullWidth">
                                <option value="nodata">No data</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="shipping_type" class="col-sm-4 control-label">Shipping Type *</label>
                        <div class="col-sm-4">
                            <select name="shipping_type" id="shipping_type" class="selectFullWidth">
                                <option value="nodata">No data</option>
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <select name="shipping_routes" id="shipping_routes" class="selectFullWidth">
                                <option value="nodata">No data</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="packaging_type" class="col-sm-4 control-label">Packaging Type *</label>
                        <div class="col-sm-8">
                            <select name="packaging_type" id="packaging_type" class="selectFullWidth">
                                <option value="nodata">No data</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="plant" class="col-sm-4 control-label">Plant *</label>
                        <div class="col-sm-8">
                            <select name="plant" id="plant" class="selectFullWidth">
                                <option value="nodata">No data</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="city_province" class="col-sm-4 control-label">City/Province *</label>
                        <div class="col-sm-8">
                            <select name="city_province" id="city_province" class="selectFullWidth">
                                <option value="nodata">No data</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="price_list" class="col-sm-4 control-label">Price List *</label>
                        <div class="col-sm-8">
                            <select name="price_list" id="price_list" class="selectFullWidth">
                                <option value="nodata">No data</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="orderCancel">Cancel</button>
                <button type="button" class="btn btn-success" id="orderSubmit">Apply</button>
            </div>
        </div>
    </div>
</div> -->

<!-- <div class="modal" id="csmsLoaderModal" tabindex="-1" role="dialog" aria-labelledby="csmsLoaderModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body" style="text-align: center;">
                <?= '<img src="' . base_url() . 'themes/default/assets/images/loader-csms.gif" alt="Loader CSMS" width="100"' ?> -->
<!-- <br>
                <h4 class="modal-title" id="sendingCSMS" style="margin-top: 20px">Sending Order to CSMS . . .</h4>
                <br>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="apixLoaderModal" tabindex="-1" role="dialog" aria-labelledby="apixLoaderModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body" style="text-align: center;">
                <?= '<img src="' . base_url() . 'themes/default/assets/images/loader-csms.gif" alt="Loader APIX" width="100"' ?>
                <br>
                <h4 class="modal-title" id="sendingCSMS" style="margin-top: 20px">Sending Order to POS Supplier . . .</h4>
                <br>
            </div>
        </div>
    </div>
</div> -->