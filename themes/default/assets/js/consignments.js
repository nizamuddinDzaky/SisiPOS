$(document).ready(function () {

    if (!localStorage.getItem('csgref')) {
        localStorage.setItem('csgref', '');
    }

    ItemnTotals();
    $('.bootbox').on('hidden.bs.modal', function (e) {
        $('#add_item').focus();
    });
    $('body a, body button').attr('tabindex', -1);
    check_add_item_val();
    if (site.settings.set_focus != 1) {
        $('#add_item').focus();
    }

    //localStorage.clear();
    // If there is any item in localStorage
    if (localStorage.getItem('csgitems')) {
        loadItems();
    }

    // clear localStorage and reload
    $('#reset').click(function (e) {
        bootbox.confirm(lang.r_u_sure, function (result) {
            if (result) {
                if (localStorage.getItem('csgitems')) {
                    localStorage.removeItem('csgitems');
                }
                if (localStorage.getItem('csgref')) {
                    localStorage.removeItem('csgref');
                }
                if (localStorage.getItem('csgwarehouse')) {
                    localStorage.removeItem('csgwarehouse');
                }
                if (localStorage.getItem('csgnote')) {
                    localStorage.removeItem('csgnote');
                }
                if (localStorage.getItem('csgdate')) {
                    localStorage.removeItem('csgdate');
                }

                $('#modal-loading').show();
                location.reload();
            }
        });
    });

    // save and load the fields in and/or from localStorage
    $('#csgref').change(function (e) {
        localStorage.setItem('csgref', $(this).val());
    });
    if (csgref = localStorage.getItem('csgref')) {
        $('#csgref').val(csgref);
    }
    $('#csgwarehouse').change(function (e) {
        localStorage.setItem('csgwarehouse', $(this).val());
    });
    if (csgwarehouse = localStorage.getItem('csgwarehouse')) {
        $('#csgwarehouse').select2("val", csgwarehouse);
    }
    $('#csgsupplier').change(function (e) {
        localStorage.setItem('csgsupplier', $(this).val());
        $('#supplier_id').val($(this).val());
    });
    if (csgsupplier = localStorage.getItem('csgsupplier')) {
        setSupplier(csgsupplier);
    } else {
        nsSupplier();
    }
    //$(document).on('change', '#qanote', function (e) {
        $('#csgnote').redactor('destroy');
        $('#csgnote').redactor({
            buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
            formattingTags: ['p', 'pre', 'h3', 'h4'],
            minHeight: 100,
            changeCallback: function (e) {
                var v = this.get();
                localStorage.setItem('csgnote', v);
            }
        });
        if (csgnote = localStorage.getItem('csgnote')) {
            $('#csgnote').redactor('set', csgnote);
        }

    // prevent default action upon enter
    $('body').bind('keypress', function (e) {
        if ($(e.target).hasClass('redactor_editor')) {
            return true;
        }
        if (e.keyCode == 13) {
            e.preventDefault();
            return false;
        }
    });


    /* ---------------------- 
     * Delete Row Method 
     * ---------------------- */

    $(document).on('click', '.csgdel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete csgitems[item_id];
        row.remove();
        if(csgitems.hasOwnProperty(item_id)) { } else {
            localStorage.setItem('csgitems', JSON.stringify(csgitems));
            item=JSON.parse(localStorage.getItem('csgitems'));
            if($.isEmptyObject(item) && localStorage.getItem('csgsupplier')){
                localStorage.removeItem('csgsupplier');
                $('#csgsupplier').select2('readonly', false);
                setSupplier('');
                $('#supplier_id').val('');
            }
            loadItems();
            return;
        }
    });

    /* --------------------------
     * Edit Row Quantity Method 
     -------------------------- */
    var old_row_qty;
    $(document).on("focus", '.rquantity', function () {
        old_row_qty = $(this).val();
    })
    $(document).on("change", '.rquantity', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var new_qty = parseFloat($(this).val()),
        item_id = row.attr('data-item-id');
        csgitems[item_id].row.qty = new_qty;
        localStorage.setItem('csgitems', JSON.stringify(csgitems));
        loadItems();
    });

    $(document).on("change", '.rtype', function () {
        var row = $(this).closest('tr');
        var new_type = $(this).val(),
        item_id = row.attr('data-item-id');
        csgitems[item_id].row.type = new_type;
        localStorage.setItem('csgitems', JSON.stringify(csgitems));
    });

    $(document).on("change", '.rvariant', function () {
        var row = $(this).closest('tr');
        var new_opt = $(this).val(),
        item_id = row.attr('data-item-id');
        csgitems[item_id].row.option = new_opt;
        localStorage.setItem('csgitems', JSON.stringify(csgitems));
    });
        

});

// hellper function for supplier if no localStorage value
function nsSupplier() {
    $('#csgsupplier').select2({
        minimumInputLength: 1,
        ajax: {
            url: site.base_url + "suppliers/suggestions",
            dataType: 'json',
            quietMillis: 15,
            data: function (term, page) {
                return {
                    term: term,
                    limit: 20
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


/* -----------------------
 * Load Items to table
 ----------------------- */

function loadItems() {

    if (localStorage.getItem('csgitems')) {
        count = 1;
        an = 1;
        $("#csgTable tbody").empty();
        csgitems = JSON.parse(localStorage.getItem('csgitems'));
        sortedItems = (site.settings.item_addition == 1) ? _.sortBy(csgitems, function(o){return [parseInt(o.order)];}) : csgitems;
        $.each(sortedItems, function () {
            var item = this;
            var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
            item.order = item.order ? item.order : new Date().getTime();
            var product_id = item.row.id, item_qty = item.row.qty, item_option = item.row.option, item_code = item.row.code, item_expiry=item.row.expiry, item_price=item.row.supplier1price, item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
            var type = item.row.type ? item.row.type : '';
            
            var opt = $("<select id=\"poption\" name=\"variant\[\]\" class=\"form-control select rvariant\" />");
            if(item.options !== false) {
                $.each(item.options, function () {
                    if (item.row.option == this.id)
                        $("<option />", {value: this.id, text: this.name, selected: 'selected'}).appendTo(opt);
                    else
                        $("<option />", {value: this.id, text: this.name}).appendTo(opt);
                });
            } else {
                $("<option />", {value: 0, text: 'n/a'}).appendTo(opt);
                opt = opt.hide();
            }

            var row_no = (new Date).getTime();
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td><input name="product_id[]" type="hidden" class="rid" value="' + product_id + '"><input name="item_code[]" type="hidden" class="rcode" value="' + item_code + '"><span class="sname" id="name_' + row_no + '">' + item_code +' - ' + item_name +'</span><i class="pull-right fa fa-edit tip edit" id="' + row_no + '" data-item="' + item_id + '" title="Edit" style="cursor:pointer;"></i></td>';
            tr_html += '<td>'+(opt.get(0).outerHTML)+'</td>';
//            tr_html += '<td><input class="form-control input-sm text-right price" name="price[]" type="hidden" id="price_' + row_no + '" value="' + item_price + '"><span class="text-right sprice" id="sprice_' + row_no + '">' + formatMoney(item_price) + '</span></td>';
            tr_html += '<td><input class="form-control text-right price" name="price[]" type="text" id="price_' + row_no + '" value="' + formatDecimal(item_price) + '"></td>';
//            tr_html += '<td><select name="type[]" class="form-contol select rtype" style="width:100%;"><option value="addition"'+(type == 'addition' ? ' selected' : '')+'>'+type_opt.addition+'</option><option value="subtraction"'+(type == 'subtraction' ? ' selected' : '')+'>'+type_opt.subtraction+'</option></select></td>';
            tr_html += '<td><input class="form-control text-center rquantity" tabindex="'+((site.settings.set_focus == 1) ? an : (an+1))+'" name="quantity[]" type="text" value="' + formatDecimal(item_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();"></td>';
            if (site.settings.product_expiry == 1) {
                tr_html += '<td class="text-right"><input class="form-control date rexpiry" name="expiry[]" type="text" id="expiry_' + row_no + '" value="'+item_expiry+'"></td>';
            }
            tr_html += '<td class="text-center"><i class="fa fa-times tip csgdel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';

            newTr.html(tr_html);
            newTr.prependTo("#csgTable");
            count += parseFloat(item_qty);
            an++;
            
        });

        var col = 3;
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total</th><th class="text-center">' + formatNumber(parseFloat(count) - 1) + '</th>';
        if (site.settings.product_serial == 1) { tfoot += '<th></th>'; }
        tfoot += '<th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#csgTable tfoot').html(tfoot);
        $('select.select').select2({minimumResultsForSearch: 7});
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $("html, body").animate({scrollTop: $('#sticker').offset().top}, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }
        set_page_focus();
    }
}

    $(document).on('click', '.edit', function () {
        var row = $(this).closest('tr');
        var row_id = row.attr('id');
        item_id = row.attr('data-item-id');
        item = csgitems[item_id];
        var qty = row.children().children('.rquantity').val(),
        product_option = row.children().children('.roption').val(),
        unit_price = formatDecimal(row.children().children('.price').val()),
        discount = row.children().children('.rdiscount').val();
        $('#prModalLabel').text(item.row.name + ' (' + item.row.code + ')');
        var real_unit_price = item.row.supplier1price;
        var net_cost = real_unit_price;
//        if (site.settings.tax1) {
//            $('#ptax').select2('val', item.row.tax_rate);
//            $('#old_tax').val(item.row.tax_rate);
//            var item_discount = 0, ds = discount ? discount : '0';
//            if (ds.indexOf("%") !== -1) {
//                var pds = ds.split("%");
//                if (!isNaN(pds[0])) {
//                    item_discount = parseFloat(((real_unit_cost) * parseFloat(pds[0])) / 100);
//                } else {
//                    item_discount = parseFloat(ds);
//                }
//            } else {
//                item_discount = parseFloat(ds);
//            }
//            net_cost -= item_discount;
//            var pr_tax = item.row.tax_rate, pr_tax_val = 0;
////            if (pr_tax !== null && pr_tax != 0) {
////                $.each(tax_rates, function () {
////                    if(this.id == pr_tax){
////                        if (this.type == 1) {
////
////                            if (csgitems[item_id].row.tax_method == 0) {
////                                pr_tax_val = formatDecimal((((real_unit_cost-item_discount) * parseFloat(this.rate)) / (100 + parseFloat(this.rate))), 4);
////                                pr_tax_rate = formatDecimal(this.rate) + '%';
////                                net_cost -= pr_tax_val;
////                            } else {
////                                pr_tax_val = formatDecimal((((real_unit_cost-item_discount) * parseFloat(this.rate)) / 100), 4);
////                                pr_tax_rate = formatDecimal(this.rate) + '%';
////                            }
////
////                        } else if (this.type == 2) {
////
////                            pr_tax_val = parseFloat(this.rate);
////                            pr_tax_rate = this.rate;
////
////                        }
////                    }
////                });
////            }
//        }
//        if (site.settings.product_serial !== 0) {
//            $('#pserial').val(row.children().children('.rserial').val());
//        }
        var propt = '<p style="margin: 12px 0 0 0;">n/a</p>';
        if(item.options !== false) {
            var o = 1;
            opt = $("<select id=\"poption\" name=\"poption\" class=\"form-control select\" />");
            $.each(item.options, function () {
                if(o == 1) {
                    if(product_option == '') { product_variant = this.id; } else { product_variant = product_option; }
                }
                $("<option />", {value: this.id, text: this.name}).appendTo(propt);
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

console.log(item);
        $('#coptions-div').html(propt);
        $('#cunits-div').html(uopt);
        $('select.select').select2({minimumResultsForSearch: 7});
        $('#cquantity').val(qty);$('#old_qty').val(qty);
        $('#cprice').val(unit_price);$('#cunit_cost').val(formatDecimal(parseFloat(unit_price)));
        $('#coption').select2('val', item.row.option);
        $('#old_cost').val(unit_price);
        $('#row_id').val(row_id);
        $('#item_id').val(item_id);
        $('#cexpiry').val(row.children().children('.rexpiry').val());
        $('#net_price').text(formatMoney(net_cost));
        $('#csubtotal').val('');
        $('#prModal').appendTo("body").modal('show');
    });
    
     $(document).on('click', '#editItem', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
//        var new_pr_tax = $('#ptax').val(), new_pr_tax_rate = {};
//        if (new_pr_tax) {
//            $.each(tax_rates, function () {
//                if (this.id == new_pr_tax) {
//                    new_pr_tax_rate = this;
//                }
//            });
//        }

        if (!is_numeric($('#cquantity').val()) || parseFloat($('#cquantity').val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }

        var unit = $('#cunit').val();
        var base_quantity = parseFloat($('#cquantity').val());
        if(unit != csgitems[item_id].row.base_unit) {
            $.each(csgitems[item_id].units, function(){
                if (this.id == unit) {
                    base_quantity = unitToBaseQty($('#cquantity').val(), this);
                }
            });
        }

        csgitems[item_id].row.fup = 1,
        csgitems[item_id].row.qty = parseFloat($('#cquantity').val()),
        csgitems[item_id].row.base_quantity = parseFloat(base_quantity),
        csgitems[item_id].row.unit = unit,
        csgitems[item_id].row.supplier1price = parseFloat($('#cprice').val()),
        csgitems[item_id].row.discount = $('#cdiscount').val() ? $('#cdiscount').val() : '0',
        csgitems[item_id].row.option = $('#coption').val(),
        csgitems[item_id].row.expiry = $('#cexpiry').val() ? $('#cexpiry').val() : '';
        localStorage.setItem('csgitems', JSON.stringify(csgitems));
        $('#prModal').modal('hide');
        loadItems();
        return;
    });
//    $('#prModal').on('shown.bs.modal', function (e) {
//        if($('#poption').select2('val') != '') {
//            $('#poption').select2('val', product_variant);
//            product_variant = 0;
//        }
//    });

//    $(document).on('change', '#cprice, #ctax, #cdiscount', function () {
//        var row = $('#' + $('#row_id').val());
//        var item_id = row.attr('data-item-id');
//        var unit_cost = parseFloat($('#cprice').val());
//        var item = csgitems[item_id];
//        var ds = $('#cprice').val() ? $('#cprice').val() : '0';
//        if (ds.indexOf("%") !== -1) {
//            var pds = ds.split("%");
//            if (!isNaN(pds[0])) {
//                item_discount = parseFloat(((unit_cost) * parseFloat(pds[0])) / 100);
//            } else {
//                item_discount = parseFloat(ds);
//            }
//        } else {
//            item_discount = parseFloat(ds);
//        }
//        
//        unit_cost -= item_discount;
//        var pr_tax = $('#ctax').val(), item_tax_method = item.row.tax_method;
//        var pr_tax_val = 0, pr_tax_rate = 0;
//
//        $('#net_price').text(formatMoney(unit_cost));
//    });


/* -----------------------------
 * Add Purchase Item Function
 * @param {json} item
 * @returns {Boolean}
 ---------------------------- */
function add_consignment_item(item) {
    setSupplier(item.row.supplier1);
    if (!localStorage.getItem('csgsupplier')) {
        localStorage.setItem('csgsupplier', $('#csgsupplier').val());
        $('#supplier_id').val($('#csgsupplier').val());
    }
    
    if (count == 1) {
        csgitems = {};
        if ($('#csgsupplier').val()) {
            $('#csgsupplier').select2("readonly", true);
        } 
    }
    if (item == null)
        return;

    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    if (csgitems[item_id]) {
        csgitems[item_id].row.qty = parseFloat(csgitems[item_id].row.qty) + 1;
    } else {
        csgitems[item_id] = item;
    }
    csgitems[item_id].order = new Date().getTime();
    localStorage.setItem('csgitems', JSON.stringify(csgitems));
    loadItems();
    return true;
}

function setSupplier(id){
    $('#csgsupplier').val(id).select2({
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
}

if (typeof (Storage) === "undefined") {
    $(window).bind('beforeunload', function (e) {
        if (count > 1) {
            var message = "You will loss data!";
            return message;
        }
    });
}