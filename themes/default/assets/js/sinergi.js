/* 
 * Copyright (c) 2017 adminSISI.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    adminSISI - initial API and implementation and/or initial documentation
 */
var $supplier = $('#mosupplier'), $currency = $('#pocurrency');
        $(document).ready(function (){
            $('body a, body button').attr('tabindex', -1);
                check_add_item_val();
                if (site.settings.set_focus != 1) {
                    $('#add_item').focus();
                }
                // If there is any item in localStorage
                if (localStorage.getItem('moitems')) {
                    loadItems();
                }   
                // clear localStorage and reload
                $('#reset').click(function (e) {
                    bootbox.confirm(lang.r_u_sure, function (result) {
                        if (result) {
                            if (localStorage.getItem('moitems')) {
                                localStorage.removeItem('moitems');
                            }
                            if (localStorage.getItem('modiscount')) {
                                localStorage.removeItem('modiscount');
                            }
                            if (localStorage.getItem('motax2')) {
                                localStorage.removeItem('motax2');
                            }
                            if (localStorage.getItem('ponote')) {
                                localStorage.removeItem('ponote');
                            }
                            if (localStorage.getItem('mosupplier')) {
                                localStorage.removeItem('mosupplier');
                            }
                            if (localStorage.getItem('mocurrency')) {
                                localStorage.removeItem('mocurrency');
                            }
                            if (localStorage.getItem('moextras')) {
                                localStorage.removeItem('moextras');
                            }

                            $('#modal-loading').show();
                            location.reload();
                        }
                    });
                });
        // save and load the fields in and/or from localStorage
        var $supplier = $('#mosupplier'), $currency = $('#mocurrency');
                    $supplier.change(function (e) {
                        localStorage.setItem('mosupplier', $(this).val());
                        $('#supplier_id').val($(this).val());
                    });
                    if (mosupplier = localStorage.getItem('mosupplier')) {
                        $supplier.val(mosupplier).select2({
                            minimumInputLength: 1,
                            data: [],
                            initSelection: function (element, callback) {
                                $.ajax({
                                    type: "get", async: false,
                                    url: site.base_url+"suppliers/getSupplier/" + $(element).val(),
                                    dataType: "json",
                                    success: function (data) {
                                        callback(data[0]);
                                    }
                                });
                            },
                            ajax: {
                                url: site.base_url + "suppliers/suggestions",
                                dataType: 'json',
                                quietMillis: 15,
                                data: function (term, page) {
                                    return {
                                        term: term,
                                        limit: 10
                                    };
                                },
                                results: function (data, page) {
                                    if (data.results != null) {
                                        return {results: data.results};
                                    } else {
                                        return {results: [{id: '', text: 'No Match Found'}]};
                                    }
                                }
                            }
                        });

                    } else {
                        nsSupplier();
                    }
                    $(document).on('change', '.rexpiry', function () {
                        var item_id = $(this).closest('tr').attr('data-item-id');
                        moitems[item_id].row.expiry = $(this).val();
                        localStorage.setItem('moitems', JSON.stringify(moitems));
                    });
                       /* ----------------------
                        * Delete Row Method
                        * ---------------------- */

                    $(document).on('click', '.podel', function () {
                           var row = $(this).closest('tr');
                           var item_id = row.attr('data-item-id');
                           delete moitems[item_id];
                           row.remove();
                           if(moitems.hasOwnProperty(item_id)) { } else {
                               localStorage.setItem('moitems', JSON.stringify(moitems));
                               loadItems();
                               return;
                           }
                       });
                        /* -----------------------
                        * Edit Row Modal Hanlder
                        ----------------------- */
                        $(document).on('click', '.edit', function () {
                           var row = $(this).closest('tr');
                           var row_id = row.attr('id');
                           item_id = row.attr('data-item-id');
                           item = moitems[item_id];
                           var qty = row.children().children('.rquantity').val(),
                           product_option = row.children().children('.roption').val(),
                           unit_cost = formatDecimal(row.children().children('.rucost').val()),
                           discount = row.children().children('.rdiscount').val();
                           $('#prModalLabel').text(item.row.name + ' (' + item.row.code + ')');
                           var real_unit_cost = item.row.real_unit_cost;
                           var net_cost = real_unit_cost;
                           if (site.settings.tax1) {
                               $('#ptax').select2('val', item.row.tax_rate);
                               $('#old_tax').val(item.row.tax_rate);
                               var item_discount = 0, ds = discount ? discount : '0';
                               if (ds.indexOf("%") !== -1) {
                                   var pds = ds.split("%");
                                   if (!isNaN(pds[0])) {
                                       item_discount = parseFloat(((real_unit_cost) * parseFloat(pds[0])) / 100);
                                   } else {
                                       item_discount = parseFloat(ds);
                                   }
                               } else {
                                   item_discount = parseFloat(ds);
                               }
                               net_cost -= item_discount;
                               var pr_tax = item.row.tax_rate, pr_tax_val = 0;
                               if (pr_tax !== null && pr_tax != 0) {
                                   $.each(tax_rates, function () {
                                       if(this.id == pr_tax){
                                           if (this.type == 1) {

                                               if (moitems[item_id].row.tax_method == 0) {
                                                   pr_tax_val = formatDecimal((((real_unit_cost-item_discount) * parseFloat(this.rate)) / (100 + parseFloat(this.rate))), 4);
                                                   pr_tax_rate = formatDecimal(this.rate) + '%';
                                                   net_cost -= pr_tax_val;
                                               } else {
                                                   pr_tax_val = formatDecimal((((real_unit_cost-item_discount) * parseFloat(this.rate)) / 100), 4);
                                                   pr_tax_rate = formatDecimal(this.rate) + '%';
                                               }

                                           } else if (this.type == 2) {

                                               pr_tax_val = parseFloat(this.rate);
                                               pr_tax_rate = this.rate;

                                           }
                                       }
                                   });
                               }
                           }
                           if (site.settings.product_serial !== 0) {
                               $('#pserial').val(row.children().children('.rserial').val());
                           }
                           var opt = '<p style="margin: 12px 0 0 0;">n/a</p>';
                           if(item.options !== false) {
                               var o = 1;
                               opt = $("<select id=\"mooption\" name=\"mooption\" class=\"form-control select\" />");
                               $.each(item.options, function () {
                                   if(o == 1) {
                                       if(product_option == '') { product_variant = this.id; } else { product_variant = product_option; }
                                   }
                                   $("<option />", {value: this.id, text: this.name}).appendTo(opt);
                                   o++;
                               });
                           }

                           uopt = $("<select id=\"punit\" name=\"punit\" class=\"form-control select\" />");
                           $.each(item.units, function () {
                               if(this.id == item.row.unit) {
                                   $("<option />", {value: this.id, text: this.name, selected:true}).appendTo(uopt);
                               } else {
                                   $("<option />", {value: this.id, text: this.name}).appendTo(uopt);
                               }
                           });

                           $('#mooptions-div').html(opt);
                           $('#punits-div').html(uopt);
                           $('select.select').select2({minimumResultsForSearch: 7});
                           $('#pquantity').val(qty);
                           $('#old_qty').val(qty);
                           $('#pcost').val(unit_cost);
                           $('#punit_cost').val(formatDecimal(parseFloat(unit_cost)+parseFloat(pr_tax_val)));
                           $('#mooption').select2('val', item.row.option);
                           $('#old_cost').val(unit_cost);
                           $('#row_id').val(row_id);
                           $('#item_id').val(item_id);
                           $('#pexpiry').val(row.children().children('.rexpiry').val());
                           $('#pdiscount').val(discount);
                           $('#net_cost').text(formatMoney(net_cost));
                           $('#pro_tax').text(formatMoney(pr_tax_val));
                           $('#psubtotal').val('');
                           $('#prModal').appendTo("body").modal('show');

                       });
                         $('#prModal').on('shown.bs.modal', function (e) {
                            if($('#mooption').select2('val') != '') {
                                $('#mooption').select2('val', product_variant);
                                product_variant = 0;
                            }
                        });
                        $(document).on('change', '#pcost, #ptax, #pdiscount', function () {
                            var row = $('#' + $('#row_id').val());
                            var item_id = row.attr('data-item-id');
                            var unit_cost = parseFloat($('#pcost').val());
                            var item = moitems[item_id];
                            var ds = $('#pdiscount').val() ? $('#pdiscount').val() : '0';
                            if (ds.indexOf("%") !== -1) {
                                var pds = ds.split("%");
                                if (!isNaN(pds[0])) {
                                    item_discount = parseFloat(((unit_cost) * parseFloat(pds[0])) / 100);
                                } else {
                                    item_discount = parseFloat(ds);
                                }
                            } else {
                                item_discount = parseFloat(ds);
                            }
                            unit_cost -= item_discount;
                            var pr_tax = $('#ptax').val(), item_tax_method = item.row.tax_method;
                            var pr_tax_val = 0, pr_tax_rate = 0;
                            if (pr_tax !== null && pr_tax != 0) {
                                $.each(tax_rates, function () {
                                    if(this.id == pr_tax){
                                        if (this.type == 1) {

                                            if (item_tax_method == 0) {
                                                pr_tax_val = formatDecimal((((unit_cost) * parseFloat(this.rate)) / (100 + parseFloat(this.rate))), 4);
                                                pr_tax_rate = formatDecimal(this.rate) + '%';
                                                unit_cost -= pr_tax_val;
                                            } else {
                                                pr_tax_val = formatDecimal((((unit_cost) * parseFloat(this.rate)) / 100), 4);
                                                pr_tax_rate = formatDecimal(this.rate) + '%';
                                            }

                                        } else if (this.type == 2) {

                                            pr_tax_val = parseFloat(this.rate);
                                            pr_tax_rate = this.rate;

                                        }
                                    }
                                });
                            }

                            $('#net_cost').text(formatMoney(unit_cost));
                            $('#pro_tax').text(formatMoney(pr_tax_val));
                        });
                            /* -----------------------
                            * Edit Row Method
                            ----------------------- */
                            $(document).on('click', '#editItem', function () {
                               var row = $('#' + $('#row_id').val());
                               var item_id = row.attr('data-item-id'), new_pr_tax = $('#ptax').val(), new_pr_tax_rate = {};
                               if (new_pr_tax) {
                                   $.each(tax_rates, function () {
                                       if (this.id == new_pr_tax) {
                                           new_pr_tax_rate = this;
                                       }
                                   });
                               }

                               if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
                                   $(this).val(old_row_qty);
                                   bootbox.alert(lang.unexpected_value);
                                   return;
                               }

                               var unit = $('#punit').val();
                               var base_quantity = parseFloat($('#pquantity').val());
                               if(unit != moitems[item_id].row.base_unit) {
                                   $.each(moitems[item_id].units, function(){
                                       if (this.id == unit) {
                                           base_quantity = unitToBaseQty($('#pquantity').val(), this);
                                       }
                                   });
                               }

                               moitems[item_id].row.fup = 1,
                               moitems[item_id].row.qty = parseFloat($('#pquantity').val()),
                               moitems[item_id].row.base_quantity = parseFloat(base_quantity),
                               moitems[item_id].row.unit = unit,
                               moitems[item_id].row.real_unit_cost = parseFloat($('#pcost').val()),
                               moitems[item_id].row.tax_rate = new_pr_tax,
                               moitems[item_id].tax_rate = new_pr_tax_rate,
                               moitems[item_id].row.discount = $('#pdiscount').val() ? $('#pdiscount').val() : '0',
                               moitems[item_id].row.option = $('#mooption').val();
                               localStorage.setItem('moitems', JSON.stringify(moitems));
                               $('#prModal').modal('hide');
                               loadItems();
                               return;
                           });
                        /* ------------------------------
                        * Show manual item addition modal
                        ------------------------------- */
                        $(document).on('click', '#addManually', function (e) {
                           $('#mModal').appendTo("body").modal('show');
                           return false;
                       });
        });
function nsSupplier() {
    $('#mosupplier').select2({
        minimumInputLength: 1,
        ajax: {
            url: site.base_url + "suppliers/suggestions",
            dataType: 'json',
            quietMillis: 15,
            data: function (term, page) {
                return {
                    term: term,
                    limit: 10
                };
            },
            results: function (data, page) {
                if (data.results != null) {
                    return {results: data.results};
                } else {
                    return {results: [{id: '', text: 'No Match Found'}]};
                }
            }
        }
    });
}
function loadItems() {

    if (localStorage.getItem('moitems')) {
        total = 0;
        count = 1;
        an = 1;
        product_tax = 0;
        invoice_tax = 0;
        product_discount = 0;
        order_discount = 0;
        total_discount = 0;
        $("#moTable tbody").empty();
        moitems = JSON.parse(localStorage.getItem('moitems'));
        sortedItems = (site.settings.item_addition == 1) ? _.sortBy(moitems, function(o){return [parseInt(o.order)];}) : moitems;

        var order_no = new Date().getTime();
        $.each(sortedItems, function () {
            var item = this;
            var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
            item.order = item.order ? item.order : order_no++;
            var product_id = item.row.id, item_type = item.row.type, combo_items = item.combo_items, item_cost = item.row.cost, item_oqty = item.row.oqty, item_qty = item.row.qty, item_bqty = item.row.quantity_balance, item_expiry = item.row.expiry, item_tax_method = item.row.tax_method, item_ds = item.row.discount, item_discount = 0, item_option = item.row.option, item_code = item.row.code, item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
            var qty_received = (item.row.received >= 0) ? item.row.received : item.row.qty;
            var item_supplier_part_no = item.row.supplier_part_no ? item.row.supplier_part_no : '';
            if (item.row.new_entry == 1) { item_bqty = item_qty; item_oqty = item_qty; }
            var unit_cost = item.row.real_unit_cost;
            var product_unit = item.row.unit, base_quantity = item.row.base_quantity;
            var supplier = localStorage.getItem('mosupplier'), belong = false;

                if (supplier == item.row.supplier1) {
                    belong = true;
                } else
                if (supplier == item.row.supplier2) {
                    belong = true;
                } else
                if (supplier == item.row.supplier3) {
                    belong = true;
                } else
                if (supplier == item.row.supplier4) {
                    belong = true;
                } else
                if (supplier == item.row.supplier5) {
                    belong = true;
                }
                var unit_qty_received = qty_received;
                if(item.row.fup != 1 && product_unit != item.row.base_unit) {
                    $.each(item.units, function(){
                        if (this.id == product_unit) {
                            base_quantity = formatDecimal(unitToBaseQty(item.row.qty, this), 4);
                            unit_qty_received = item.row.unit_received ? item.row.unit_received : formatDecimal(baseToUnitQty(qty_received, this), 4);
                            unit_cost = formatDecimal((parseFloat(item.row.base_unit_cost)*(unitToBaseQty(1, this))), 4);
                        }
                    });
                }
                var ds = item_ds ? item_ds : '0';
                if (ds.indexOf("%") !== -1) {
                    var pds = ds.split("%");
                    if (!isNaN(pds[0])) {
                        item_discount = formatDecimal((parseFloat(((unit_cost) * parseFloat(pds[0])) / 100)), 4);
                    } else {
                        item_discount = formatDecimal(ds);
                    }
                } else {
                     item_discount = formatDecimal(ds);
                }
                product_discount += parseFloat(item_discount * item_qty);

                unit_cost = formatDecimal(unit_cost-item_discount);
                var pr_tax = item.tax_rate;
                var pr_tax_val = 0, pr_tax_rate = 0;
                if (site.settings.tax1 == 1) {
                    if (pr_tax !== false) {
                        if (pr_tax.type == 1) {

                            if (item_tax_method == '0') {
                                pr_tax_val = formatDecimal(((unit_cost) * parseFloat(pr_tax.rate)) / (100 + parseFloat(pr_tax.rate)), 4);
                                pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
                            } else {
                                pr_tax_val = formatDecimal(((unit_cost) * parseFloat(pr_tax.rate)) / 100, 4);
                                pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
                            }

                        } else if (pr_tax.type == 2) {

                            pr_tax_val = parseFloat(pr_tax.rate);
                            pr_tax_rate = pr_tax.rate;

                        }
                        product_tax += pr_tax_val * item_qty;
                    }
                }
                item_cost = item_tax_method == 0 ? formatDecimal(unit_cost-pr_tax_val, 4) : formatDecimal(unit_cost);
                unit_cost = formatDecimal(unit_cost+item_discount, 4);
                var sel_opt = '';
                $.each(item.options, function () {
                    if(this.id == item_option) {
                        sel_opt = this.name;
                    }
                });

            var row_no = (new Date).getTime();
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td><input name="product_id[]" type="hidden" class="rid" value="' + product_id + '"><input name="product[]" type="hidden" class="rcode" value="' + item_code + '"><input name="product_name[]" type="hidden" class="rname" value="' + item_name + '"><input name="product_option[]" type="hidden" class="roption" value="' + item_option + '"><input name="part_no[]" type="hidden" class="rpart_no" value="' + item_supplier_part_no + '"><span class="sname" id="name_' + row_no + '">' + item_code +' - '+ item_name +(sel_opt != '' ? ' ('+sel_opt+')' : '')+' <span class="label label-default">'+item_supplier_part_no+'</span></span> <i class="pull-right fa fa-edit tip edit" id="' + row_no + '" data-item="' + item_id + '" title="Edit" style="cursor:pointer;"></i></td>';
            tr_html += '<td class="text-right"><input class="form-control input-sm text-right rcost" name="net_cost[]" type="hidden" id="cost_' + row_no + '" value="' + item_cost + '"><input class="rucost" name="unit_cost[]" type="hidden" value="' + unit_cost + '"><input class="realucost" name="real_unit_cost[]" type="hidden" value="' + item.row.real_unit_cost + '"><span class="text-right scost" id="scost_' + row_no + '">' + formatMoney(item_cost) + '</span></td>';
            tr_html += '<td><input name="quantity_balance[]" type="hidden" class="rbqty" value="' + item_bqty + '"><input class="form-control text-center rquantity" name="quantity[]" type="text" tabindex="'+((site.settings.set_focus == 1) ? an : (an+1))+'" value="' + formatDecimal(item_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();"><input name="product_unit[]" type="hidden" class="runit" value="' + product_unit + '"><input name="product_base_quantity[]" type="hidden" class="rbase_quantity" value="' + base_quantity + '"></td>';
            if (site.settings.product_discount == 1) {
                tr_html += '<td class="text-right"><input class="form-control input-sm rdiscount" name="product_discount[]" type="hidden" id="discount_' + row_no + '" value="' + item_ds + '"><span class="text-right sdiscount text-danger" id="sdiscount_' + row_no + '">' + formatMoney(0 - (item_discount * item_qty)) + '</span></td>';
            }
            if (site.settings.tax1 == 1) {
                tr_html += '<td class="text-right"><input class="form-control input-sm text-right rproduct_tax" name="product_tax[]" type="hidden" id="product_tax_' + row_no + '" value="' + pr_tax.id + '"><span class="text-right sproduct_tax" id="sproduct_tax_' + row_no + '">' + (pr_tax_rate ? '(' + pr_tax_rate + ')' : '') + ' ' + formatMoney(pr_tax_val * item_qty) + '</span></td>';
            }
            tr_html += '<td class="text-center"><i class="fa fa-times tip podel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';

            console.log(base_quantity);            
            $(document).on('click', 'i', function () {
                var str_itemId=$(this).closest('tr').attr('data-item-id');
                var a=JSON.parse(localStorage.getItem('moitems_temp'));
                if(a.data){
                    for(var i=0;i<Object.keys(a.data).length;i++){
                        var obj=a.data[i];
                        if(obj==null){
                            continue;
                        }
                        else{
                            if(str_itemId==obj.trx_id){
                                delete a.data[i];
                                localStorage.setItem('moitems_temp', JSON.stringify(a));
                            }
                        }
                    }
                }
            });

            newTr.html(tr_html);
            newTr.prependTo("#moTable");
            total += formatDecimal(((parseFloat(item_cost) + parseFloat(pr_tax_val)) * parseFloat(item_qty)), 4);
            count += parseFloat(item_qty);
            an++;
            if(!belong)
                $('#row_' + row_no).addClass('warning');
        });

        var col = 2;
        if (site.settings.product_expiry == 1) { col++; }
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total</th><th class="text-center">' + formatNumber(parseFloat(count) - 1) + '</th>';
        if (po_edit) {
            tfoot += '<th class="rec_con"></th>';
        }
        if (site.settings.product_discount == 1) {
            tfoot += '<th class="text-right">'+formatMoney(product_discount)+'</th>';
        }
        if (site.settings.tax1 == 1) {
            tfoot += '<th class="text-right">'+formatMoney(product_tax)+'</th>';
        }
        tfoot += '<th class="text-right">'+formatMoney(total)+'</th><th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#poTable tfoot').html(tfoot);

        // Order level discount calculations
        if (podiscount = localStorage.getItem('podiscount')) {
            var ds = podiscount;
            if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                    order_discount = formatDecimal(((total * parseFloat(pds[0])) / 100), 4);
                } else {
                    order_discount = formatDecimal(ds);
                }
            } else {
                order_discount = formatDecimal(ds);
            }
        }

        // Order level tax calculations
        if (site.settings.tax2 != 0) {
            if (potax2 = localStorage.getItem('potax2')) {
                $.each(tax_rates, function () {
                    if (this.id == potax2) {
                        if (this.type == 2) {
                            invoice_tax = formatDecimal(this.rate);
                        }
                        if (this.type == 1) {
                            invoice_tax = formatDecimal((((total - order_discount) * this.rate) / 100), 4);
                        }
                    }
                });
            }
        }
        total_discount = parseFloat(order_discount + product_discount);
        // Totals calculations after item addition
        var gtotal = ((total + invoice_tax) - order_discount) + shipping;
        $('#total').text(formatMoney(total));
        $('#titems').text((an-1)+' ('+(parseFloat(count)-1)+')');
        $('#tds').text(formatMoney(order_discount));
        if (site.settings.tax1) {
            $('#ttax1').text(formatMoney(product_tax));
        }
        if (site.settings.tax2 != 0) {
            $('#ttax2').text(formatMoney(invoice_tax));
        }
        $('#gtotal').text(formatMoney(gtotal));
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $("html, body").animate({scrollTop: $('#sticker').offset().top}, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }
        set_page_focus();
    }
}

/* -----------------------------
 * Add Purchase Iten Function
 * @param {json} item
 * @returns {Boolean}
 ---------------------------- */
 moitems_temp={
    "data":[]
 };
 moitems = {};
 function add_purchase_item(item) {

    if (count == 1) {
        if ($('#mosupplier').val()) {
            $('#mosupplier').select2("readonly", true);
        } else {
            bootbox.alert(lang.select_above);
            item = null;
            return;
        }
    }
    if (item == null)
        return;

    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    if (moitems[item_id]) {
        moitems[item_id].row.qty = parseFloat(moitems[item_id].row.qty) + 1;
    } else {
        moitems[item_id] = item;
        moitems[item_id].order = new Date().getTime();
    }

    if(localStorage.getItem('moitems_temp')){
        moitems_temp=JSON.parse(localStorage.getItem('moitems_temp'));
    }

    var flag=0;
    var count=Object.keys(moitems_temp.data).length;
    if(count==0){
        moitems_temp.data.push({ 
            "trx_id"        : item.id,
            "product_id"    : item.item_id
        });
    }
    else{
        for(var i=0;i<count;i++){
            var obj=moitems_temp.data[i];
            if(obj==null){
                continue;
            }
            else{
                if(obj.product_id==item.item_id){
                    moitems[obj.trx_id].row.qty = parseFloat(moitems[obj.trx_id].row.qty) + 1;
                    delete moitems[item_id];
                    flag=0;
                    break;
                }
                else{
                    flag=1;
                }
            }
        }
        if(flag==1){
            moitems_temp.data.push({ 
                "trx_id"        : item.id,
                "product_id"    : item.item_id
            });
        }
    }

    localStorage.setItem('moitems', JSON.stringify(moitems));
    localStorage.setItem('moitems_temp', JSON.stringify(moitems_temp));
    loadItems();
    return true;
}

if (typeof (Storage) === "undefined") {
    $(window).bind('beforeunload', function (e) {
        if (count > 1) {
            var message = "You will loss data!";
            return message;
        }
    });
}
