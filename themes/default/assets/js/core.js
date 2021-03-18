// $(window).load(function() {
//     $("#loading").fadeOut("slow");
// });

function complete_guide(col) {
    $.ajax({
        url: site.base_url + 'welcome/update_guide',
        type: 'POST',
        data: {
            column: col
        },
        success: function (data) { }
    });
}

// Responsive Mobile
$("#burger-menu-mobile").click(function () {
    $(this).find('#content').css(function (i, v) {
        return v === "'margin-left': '240px'" ? "'margin-left' : 'none'" : "'margin-left' : '240px'"
    });
});

$('#burger-menu-mobile').click(function () {
    // var $this = $(this);
    var $content = $('#content');
    var $sdsd = $('#sidebar-left');
    if ($content.hasClass('margin_left')) {
        $content.removeClass('margin_left');
        $sdsd.addClass('hideMobile');

    } else {
        $content.addClass('margin_left');
        $sdsd.removeClass('hideMobile');

    }
});


function cssStyle() {
    if ($.cookie('sma_style') == 'light') {
        $('link[href="' + site.base_url + 'themes/default/assets/styles/blue.css"]').attr('disabled', 'disabled');
        $('link[href="' + site.base_url + 'themes/default/assets/styles/pink.css"]').attr('disabled', 'disabled');
        $('link[href="' + site.base_url + 'themes/default/assets/styles/blue.css"]').remove();
        $('link[href="' + site.base_url + 'themes/default/assets/styles/pink.css"]').remove();
        $('<link>')
            .appendTo('head')
            .attr({
                type: 'text/css',
                rel: 'stylesheet'
            })
            .attr('href', site.base_url + 'themes/default/assets/styles/light.css');
    } else if ($.cookie('sma_style') == 'blue') {
        $('link[href="' + site.base_url + 'themes/default/assets/styles/light.css"]').attr('disabled', 'disabled');
        $('link[href="' + site.base_url + 'themes/default/assets/styles/pink.css"]').attr('disabled', 'disabled');
        $('link[href="' + site.base_url + 'themes/default/assets/styles/light.css"]').remove();
        $('link[href="' + site.base_url + 'themes/default/assets/styles/pink.css"]').remove();
        $('<link>')
            .appendTo('head')
            .attr({
                type: 'text/css',
                rel: 'stylesheet'
            })
            .attr('href', '' + site.base_url + 'themes/default/assets/styles/blue.css');
    } else if ($.cookie('sma_style') == 'pink') {
        $('link[href="' + site.base_url + 'themes/default/assets/styles/light.css"]').attr('disabled', 'disabled');
        $('link[href="' + site.base_url + 'themes/default/assets/styles/blue.css"]').attr('disabled', 'disabled');
        $('link[href="' + site.base_url + 'themes/default/assets/styles/light.css"]').remove();
        $('link[href="' + site.base_url + 'themes/default/assets/styles/blue.css"]').remove();
        $('<link>')
            .appendTo('head')
            .attr({
                type: 'text/css',
                rel: 'stylesheet'
            })
            .attr('href', '' + site.base_url + 'themes/default/assets/styles/pink.css');
    } else {
        $('link[href="' + site.base_url + 'themes/default/assets/styles/light.css"]').attr('disabled', 'disabled');
        $('link[href="' + site.base_url + 'themes/default/assets/styles/blue.css"]').attr('disabled', 'disabled');
        $('link[href="' + site.base_url + 'themes/default/assets/styles/pink.css"]').attr('disabled', 'disabled');
        $('link[href="' + site.base_url + 'themes/default/assets/styles/light.css"]').remove();
        $('link[href="' + site.base_url + 'themes/default/assets/styles/blue.css"]').remove();
        $('link[href="' + site.base_url + 'themes/default/assets/styles/pink.css"]').remove();
    }

    if ($('#sidebar-left').hasClass('minified')) {
        $.cookie('sma_theme_fixed', 'no', {
            path: '/'
        });
        $('#content, #sidebar-left, #header').removeAttr("style");
        $('#sidebar-left').removeClass('sidebar-fixed');
        $('#content').removeClass('content-with-fixed');
        $('#fixedText').text('Fixed');
        $('#main-menu-act').addClass('full visible-md visible-lg').show();
        $('#fixed').removeClass('fixed');
    } else {
        if (site.settings.rtl == 1) {
            $.cookie('sma_theme_fixed', 'no', {
                path: '/'
            });
        }
        if ($.cookie('sma_theme_fixed') == 'yes') {
            // $('#content').css('margin-left', $('#sidebar-left').outerWidth(true)).css('margin-top', '40px');
            $('#content').addClass('content-with-fixed');
            $('#sidebar-left').addClass('sidebar-fixed').css('height', $(window).height() - 80);
            $('#header').css('position', 'fixed').css('top', '0').css('width', '100%');
            $('#fixedText').text('Static');
            $('#main-menu-act').removeAttr("class").hide();
            $('#fixed').addClass('fixed');
            $("#sidebar-left").css("overflow", "hidden");
            $('#sidebar-left').perfectScrollbar({
                suppressScrollX: true
            });
        } else {
            $('#content, #sidebar-left, #header').removeAttr("style");
            $('#sidebar-left').removeClass('sidebar-fixed');
            $('#content').removeClass('content-with-fixed');
            $('#fixedText').text('Fixed');
            $('#main-menu-act').addClass('full visible-md visible-lg').show();
            $('#fixed').removeClass('fixed');
            $('#sidebar-left').perfectScrollbar('destroy');
        }
    }
    widthFunctions();
}
$('#csv_file').change(function (e) {
    v = $(this).val();
    if (v != '') {
        var validExts = new Array(".csv");
        var fileExt = v;
        fileExt = fileExt.substring(fileExt.lastIndexOf('.'));
        if (validExts.indexOf(fileExt) < 0) {
            e.preventDefault();
            bootbox.alert("Invalid file selected. Only .csv file is allowed.");
            $(this).val('');
            $(this).fileinput('clear');
            $('form[data-toggle="validator"]').bootstrapValidator('updateStatus', 'csv_file', 'NOT_VALIDATED');
            return false;
        } else
            return true;
    }
});

$(document).ready(function () {
    $("#suggest_product").autocomplete({
        source: site.base_url + 'reports/suggestions',
        select: function (event, ui) {
            $('#report_product_id').val(ui.item.id);
        },
        minLength: 1,
        autoFocus: false,
        delay: 250,
        response: function (event, ui) {
            if (ui.content.length == 1 && ui.content[0].id != 0) {
                ui.item = ui.content[0];
                $(this).val(ui.item.label);
                $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                $(this).autocomplete('close');
                $(this).removeClass('ui-autocomplete-loading');
            }
        },
    });
    $(document).on('blur', '#suggest_product', function (e) {
        if (!$(this).val()) {
            $('#report_product_id').val('');
        }
    });
    $('#random_num').click(function () {
        $(this).parent('.input-group').children('input').val(generateCardNo(8));
    });
    $('#num_random').click(function () {
        $(this).parent('.form-group').children('input').val(generateCardNo(8));
    });
    $('#toogle-customer-read-attr').click(function () {
        var icus = $(this).closest('.input-group').find("input[name='customer']");
        var nst = icus.is('[readonly]') ? false : true;
        icus.select2("readonly", nst);
        return false;
    });
    $('.top-menu-scroll').perfectScrollbar();
    $('#fixed').click(function (e) {
        e.preventDefault();
        if ($('#sidebar-left').hasClass('minified')) {
            bootbox.alert('Unable to fix minified sidebar');
        } else {
            if ($(this).hasClass('fixed')) {
                $.cookie('sma_theme_fixed', 'no', {
                    path: '/'
                });
            } else {
                $.cookie('sma_theme_fixed', 'yes', {
                    path: '/'
                });
            }
            cssStyle();
        }
    });

    $.ajax({
        type: 'get',
        url: site.base_url + 'auth/check_limitation_free',
        dataType: "json",
        success: function (data) {
            //            try{
            if (data && data.authorized && data.authorized.expired_date) {
                var aDate = data.authorized.expired_date.split('-');
                var bDate = aDate[2].split(' ');
                year = aDate[0], month = aDate[1], day = bDate[0];

                var expDate = month + '/' + day + '/' + year;
                var now = moment().format('MM/DD/YYYY');

                if (data.authorized.plan_name == 'Free') {
                    if (now > expDate) {
                        returnToFree();
                    }
                    //                    console.log('this is free');
                    //                    
                    //                    $('#expModal').modal({remote: site.base_url + 'welcome/exp_account/', backdrop: 'static'});
                    //                    $('#expModal').modal('show');
                    //                    if(data.total_trx>data.plan.limitation){
                    //                        console.log('more than limit');
                    //                    }
                } else {
                    if (data.authorized.plan_name != 'Free' && now > expDate) {
                        returnToFree();
                    }
                }
            }

            //            }catch (exception) {
            //                console.log(exception);
            //            }
        }
    });
});

function returnToFree() {
    $.ajax({
        type: "POST",
        url: site.base_url + 'auth/return_to_free',
        success: function (response) {
            if (!response) {
                return response;
            }
        }
    });
}

function widthFunctions(e) {
    var l = $("#sidebar-left").outerHeight(true),
        c = $("#content").height(),
        co = $("#content").outerHeight(),
        h = $("header").height(),
        f = $("footer").height(),
        wh = $(window).height(),
        ww = $(window).width();
    if (ww < 992) {
        $("#main-menu-act").removeClass("minified").addClass("full").find("i").removeClass("fa-angle-double-right").addClass("fa-angle-double-left");
        $("body").removeClass("sidebar-minified");
        $("#content").removeClass("sidebar-minified");
        $("#sidebar-left").removeClass("minified")
        if ($.cookie('sma_theme_fixed') == 'yes') {
            $.cookie('sma_theme_fixed', 'no', {
                path: '/'
            });
            $('#content, #sidebar-left, #header').removeAttr("style");
            $("#sidebar-left").css("overflow-y", "visible");
            $('#fixedText').text('Fixed');
            $('#main-menu-act').addClass('full visible-md visible-lg').show();
            $('#fixed').removeClass('fixed');
            $('#sidebar-left').perfectScrollbar('destroy');
        }
    }
    if (ww < 998 && ww > 750) {
        $('#main-menu-act').hide();
        $("body").addClass("sidebar-minified");
        $("#content").addClass("sidebar-minified");
        $("#sidebar-left").addClass("minified");
        $(".dropmenu > .chevron").removeClass("opened").addClass("closed");
        $(".dropmenu").parent().find("ul").hide();
        $("#sidebar-left > div > ul > li > a > .chevron").removeClass("closed").addClass("opened");
        $("#sidebar-left > div > ul > li > a").addClass("open");
        $('#fixed').hide();
    }
    if (ww > 1024 && $.cookie('sma_sidebar') != 'minified') {
        $('#main-menu-act').removeClass("minified").addClass("full").find("i").removeClass("fa-angle-double-right").addClass("fa-angle-double-left");
        $("body").removeClass("sidebar-minified");
        $("#content").removeClass("sidebar-minified");
        $("#sidebar-left").removeClass("minified");
        $("#sidebar-left > div > ul > li > a > .chevron").removeClass("opened").addClass("closed");
        $("#sidebar-left > div > ul > li > a").removeClass("open");
        $('#fixed').show();
    }
    if ($.cookie('sma_theme_fixed') == 'yes') {
        $('#content').addClass('content-with-fixed');
        $('#sidebar-left').addClass('sidebar-fixed').css('height', $(window).height() - 80);
    }
    if (ww > 767) {
        wh - 80 > l && $("#sidebar-left").css("min-height", wh - h - f - 30);
        wh - 80 > c && $("#content").css("min-height", wh - h - f - 30);
    } else {
        $("#sidebar-left").css("min-height", "0px");
        $(".content-con").css("max-width", ww);
    }
    //$(window).scrollTop($(window).scrollTop() + 1);
}

jQuery(document).ready(function (e) {
    window.location.hash ? e('#myTab a[href="' + window.location.hash + '"]').tab('show') : e("#myTab a:first").tab("show");
    e("#myTab2 a:first, #dbTab a:first").tab("show");
    e("#myTab a, #myTab2 a, #dbTab a").click(function (t) {
        t.preventDefault();
        e(this).tab("show");
    });
    e('[rel="popover"],[data-rel="popover"],[data-toggle="popover"]').popover();
    e("#toggle-fullscreen").button().click(function () {
        var t = e(this),
            n = document.documentElement;
        if (!t.hasClass("active")) {
            e("#thumbnails").addClass("modal-fullscreen");
            n.webkitRequestFullScreen ? n.webkitRequestFullScreen(window.Element.ALLOW_KEYBOARD_INPUT) : n.mozRequestFullScreen && n.mozRequestFullScreen()
        } else {
            e("#thumbnails").removeClass("modal-fullscreen");
            (document.webkitCancelFullScreen || document.mozCancelFullScreen || e.noop).apply(document)
        }
    });
    e(".btn-close").click(function (t) {
        t.preventDefault();
        e(this).parent().parent().parent().fadeOut()
    });
    e(".btn-minimize").click(function (t) {
        t.preventDefault();
        var n = e(this).parent().parent().next(".box-content");
        n.is(":visible") ? e("i", e(this)).removeClass("fa-chevron-up").addClass("fa-chevron-down") : e("i", e(this)).removeClass("fa-chevron-down").addClass("fa-chevron-up");
        n.slideToggle("slow", function () {
            widthFunctions();
        })
    });
});

jQuery(document).ready(function (e) {
    e("#main-menu-act").click(function () {
        if (e(this).hasClass("full")) {
            $.cookie('sma_sidebar', 'minified', {
                path: '/'
            });
            e(this).removeClass("full").addClass("minified").find("i").removeClass("fa-angle-double-left").addClass("fa-angle-double-right");
            e("body").addClass("sidebar-minified");
            e("#content").addClass("sidebar-minified");
            e("#sidebar-left").addClass("minified");
            e(".dropmenu > .chevron").removeClass("opened").addClass("closed");
            e(".dropmenu").parent().find("ul").hide();
            e("#sidebar-left > div > ul > li > a > .chevron").removeClass("closed").addClass("opened");
            e("#sidebar-left > div > ul > li > a").addClass("open");
            $("#sidebar-left > div > ul > li > ul > li > a").removeClass("have_left_padding");
            $("#sidebar-left > div > ul > li > ul > li > a").addClass("no_left_padding");
            $('#fixed').hide();
        } else {
            $.cookie('sma_sidebar', 'full', {
                path: '/'
            });
            e(this).removeClass("minified").addClass("full").find("i").removeClass("fa-angle-double-right").addClass("fa-angle-double-left");
            e("body").removeClass("sidebar-minified");
            e("#content").removeClass("sidebar-minified");
            e("#sidebar-left").removeClass("minified");
            e("#sidebar-left > div > ul > li > a > .chevron").removeClass("opened").addClass("closed");
            e("#sidebar-left > div > ul > li > a").removeClass("open");
            $("#sidebar-left > div > ul > li > ul > li > a").addClass("have_left_padding");
            $("#sidebar-left > div > ul > li > ul > li > a").removeClass("no_left_padding");
            $('#fixed').show();
        }
        return false;
    });
    e(".dropmenu").click(function (t) {
        t.preventDefault();
        if (e("#sidebar-left").hasClass("minified")) {
            if (!e(this).hasClass("open")) {
                e(this).parent().find("ul").first().slideToggle();
                e(this).find(".chevron").hasClass("closed") ? e(this).find(".chevron").removeClass("closed").addClass("opened") : e(this).find(".chevron").removeClass("opened").addClass("closed")
            }
        } else {
            e(this).parent().find("ul").first().slideToggle();
            e(this).find(".chevron").hasClass("closed") ? e(this).find(".chevron").removeClass("closed").addClass("opened") : e(this).find(".chevron").removeClass("opened").addClass("closed")
        }
    });
    if (e("#sidebar-left").hasClass("minified")) {
        e("#sidebar-left > div > ul > li > a > .chevron").removeClass("closed").addClass("opened");
        e("#sidebar-left > div > ul > li > a").addClass("open");
        e("body").addClass("sidebar-minified")
    }
});

$(document).ready(function () {
    cssStyle();
    $('select, .select').select2({
        minimumResultsForSearch: 7
    });
    $('#customer, #rcustomer').select2({
        minimumInputLength: 1,
        ajax: {
            url: site.base_url + "customers/suggestions",
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
                    return {
                        results: data.results
                    };
                } else {
                    return {
                        results: [{
                            id: '',
                            text: 'No Match Found'
                        }]
                    };
                }
            }
        }
    });
    $('#supplier, #rsupplier, .rsupplier').select2({
        minimumInputLength: 0,
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
                    return {
                        results: data.results
                    };
                } else {
                    return {
                        results: [{
                            id: '',
                            text: 'No Match Found'
                        }]
                    };
                }
            }
        }
    });
    $("#supplier").select2('data', {
        id: 1,
        text: 'Tunai (Undefined)'
    });
    $('.input-tip').tooltip({
        placement: 'top',
        html: true,
        trigger: 'hover focus',
        container: 'body',
        title: function () {
            return $(this).attr('data-tip');
        }
    });
    $('.input-pop').popover({
        placement: 'top',
        html: true,
        trigger: 'hover',
        container: 'body',
        content: function () {
            return $(this).attr('data-tip');
        },
        title: function () {
            return '<b>' + $('label[for="' + $(this).attr('id') + '"]').text() + '</b>';
        }
    });
});

$(document).on('click', '*[data-toggle="lightbox"]', function (event) {
    event.preventDefault();
    $(this).ekkoLightbox();
});
$(document).on('click', '*[data-toggle="popover"]', function (event) {
    event.preventDefault();
    $(this).popover();
});

$(document).ajaxStart(function () {
    $('#ajaxCall').show();
}).ajaxStop(function () {
    $('#ajaxCall').hide();
});

$(document).ready(function () {
    $('input[type="checkbox"],[type="radio"]').not('.skip').iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'iradio_square-blue',
        increaseArea: '20%'
    });
    $('textarea').not('.skip').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', /*'image', 'video',*/ 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var editor = this.$editor.next('textarea');
            if ($(editor).attr('required')) {
                $('form[data-toggle="validator"]').bootstrapValidator('revalidateField', $(editor).attr('name'));
            }
        }
    });
    $(document).on('click', '.file-caption', function () {
        $(this).next('.input-group-btn').children('.btn-file').children('input.file').trigger('click');
    });
});

function suppliers(ele) {
    $(ele).select2({
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
                    return {
                        results: data.results
                    };
                } else {
                    return {
                        results: [{
                            id: '',
                            text: 'No Match Found'
                        }]
                    };
                }
            }
        }
    });
}

$(function () {
    $('.datetime').datetimepicker({
        format: site.dateFormats.js_ldate,
        fontAwesome: true,
        language: 'sma',
        weekStart: 1,
        todayBtn: 1,
        autoclose: 1,
        todayHighlight: 1,
        startView: 2,
        forceParse: 0
    });
    $('.date').datetimepicker({
        format: site.dateFormats.js_sdate,
        fontAwesome: true,
        language: 'sma',
        todayBtn: 1,
        autoclose: 1,
        minView: 2,
    });
    $(document).on('focus', '.date', function (t) {
        $(this).datetimepicker({
            format: site.dateFormats.js_sdate,
            fontAwesome: true,
            todayBtn: 1,
            autoclose: 1,
            minView: 2,
        });
    });
    $(document).on('focus', '.datetime', function () {
        $(this).datetimepicker({
            format: site.dateFormats.js_ldate,
            fontAwesome: true,
            weekStart: 1,
            todayBtn: 1,
            autoclose: 1,
            todayHighlight: 1,
            startView: 2,
            forceParse: 0
        });
    });
});

$(document).ready(function () {
    $('#dbTab a').on('shown.bs.tab', function (e) {
        var newt = $(e.target).attr('href');
        var oldt = $(e.relatedTarget).attr('href');
        $(oldt).hide();
        //$(newt).hide().fadeIn('slow');
        $(newt).hide().slideDown('slow');
    });
    $('.dropdown').on('show.bs.dropdown', function (e) {
        $(this).find('.dropdown-menu').first().stop(true, true).slideDown('fast');
    });
    $('.dropdown').on('hide.bs.dropdown', function (e) {
        $(this).find('.dropdown-menu').first().stop(true, true).slideUp('fast');
    });
    $('.hideComment').click(function () {
        $.ajax({
            url: site.base_url + 'welcome/hideNotification/' + $(this).attr('id')
        });
    });
    $('.tip').tooltip({
        html: true,
        placement: 'top',
    });
    $('body').on('click', '#delete', function (e) {
        e.preventDefault();
        $('#form_action').val($(this).attr('data-action'));
        $('#action-form').submit();
    });
    $('body').on('click', '#sync_quantity', function (e) {
        e.preventDefault();
        $('#form_action').val($(this).attr('data-action'));
        $('#action-form-submit').trigger('click');
    });
    $('body').on('click', '#sync_quantity_booking', function (e) {
        e.preventDefault();
        $('#form_action').val($(this).attr('data-action'));
        $('#action-form-submit').trigger('click');
    });
    $('body').on('click', '#excel', function (e) {
        e.preventDefault();
        $('#form_action').val($(this).attr('data-action'));
        $('#action-form-submit').trigger('click');
    });
    $('body').on('click', '#pdf', function (e) {
        e.preventDefault();
        $('#form_action').val($(this).attr('data-action'));
        $('#action-form-submit').trigger('click');
    });
    $('body').on('click', '#labelProducts', function (e) {
        e.preventDefault();
        $('#form_action').val($(this).attr('data-action'));
        $('#action-form-submit').trigger('click');
    });
    $('body').on('click', '#barcodeProducts', function (e) {
        e.preventDefault();
        $('#form_action').val($(this).attr('data-action'));
        $('#action-form-submit').trigger('click');
    });
    $('body').on('click', '#combine', function (e) {
        e.preventDefault();
        $('#form_action').val($(this).attr('data-action'));
        $('#action-form-submit').trigger('click');
    });
    $('body').on('click', '#excel_all', function (e) {
        e.preventDefault();
        $('#form_action').val($(this).attr('data-action'));
        $('#action-form-submit').trigger('click');
    });
    $('body').on('click', '#recover', function (e) {
        e.preventDefault();
        $('#form_action').val($(this).attr('data-action'));
        $('#action-form-submit').trigger('click');
    });
});

$(document).ready(function () {
    $('#product-search').click(function () {
        $('#product-search-form').submit();
    });
    //feedbackIcons:{valid: 'fa fa-check',invalid: 'fa fa-times',validating: 'fa fa-refresh'},
    $('form[data-toggle="validator"]').bootstrapValidator({
        message: 'Please enter/select a value',
        submitButtons: 'input[type="submit"]'
    });
    fields = $('.form-control');
    $.each(fields, function () {
        var id = $(this).attr('id');
        var iname = $(this).attr('name');
        var iid = '#' + id;
        if (!!$(this).attr('data-bv-notempty') || !!$(this).attr('required')) {
            $("label[for='" + id + "']").append(' *');
            $(document).on('change', iid, function () {
                $('form[data-toggle="validator"]').bootstrapValidator('revalidateField', iname);
            });
        }
    });
    $('body').on('click', 'label', function (e) {
        var field_id = $(this).attr('for');
        if (field_id) {
            if ($("#" + field_id).hasClass('select')) {
                $("#" + field_id).select2("open");
                return false;
            }
        }
    });
    $('body').on('focus', 'select', function (e) {
        var field_id = $(this).attr('id');
        if (field_id) {
            if ($("#" + field_id).hasClass('select')) {
                $("#" + field_id).select2("open");
                return false;
            }
        }
    });
    $('#myModal').on('hidden.bs.modal', function () {
        $(this).find('.modal-dialog').empty();
        //$(this).find('#myModalLabel').empty().html('&nbsp;');
        //$(this).find('.modal-body').empty().text('Loading...');
        //$(this).find('.modal-footer').empty().html('&nbsp;');
        $(this).removeData('bs.modal');
    });
    $('#myModal2').on('hidden.bs.modal', function () {
        $(this).find('.modal-dialog').empty();
        //$(this).find('#myModalLabel').empty().html('&nbsp;');
        //$(this).find('.modal-body').empty().text('Loading...');
        //$(this).find('.modal-footer').empty().html('&nbsp;');
        $(this).removeData('bs.modal');
        $('#myModal').css('zIndex', '1050');
        $('#myModal').css('overflow-y', 'scroll');
    });
    $('#myModal2').on('show.bs.modal', function () {
        $('#myModal').css('zIndex', '1040');
    });
    $('.modal').on('show.bs.modal', function () {
        $('#modal-loading').show();
        $('.blackbg').css('zIndex', '1041');
        $('.loader').css('zIndex', '1042');
    }).on('hide.bs.modal', function () {
        $('#modal-loading').hide();
        $('.blackbg').css('zIndex', '3');
        $('.loader').css('zIndex', '4');
    });
    $(document).on('click', '.po', function (e) {
        e.preventDefault();
        $('.po').popover({
            html: true,
            placement: 'left',
            trigger: 'manual'
        }).popover('show').not(this).popover('hide');
        return false;
    });
    $(document).on('click', '.po-close', function () {
        $('.po').popover('hide');
        return false;
    });
    $(document).on('click', '.po-delete', function (e) {
        var row = $(this).closest('tr');
        e.preventDefault();
        $('.po').popover('hide');
        var link = $(this).attr('href');
        var return_id = $(this).attr('data-return-id');
        $.ajax({
            type: "get",
            url: link,
            success: function (data) {
                $('#' + return_id).remove();
                row.remove();
                if (data) {
                    addAlert(data, 'success');
                }
            },
            error: function (data) {
                addAlert('Failed', 'danger');
            }
        });
        return false;
    });

    $(document).on('click', '.promo-delete', function (e) {
        e.preventDefault();
        $('.po').popover('hide');
        var link = $(this).attr('href');
        var return_id = $(this).attr('data-return-id');
        $.ajax({
            type: "get",
            url: link,
            success: function (data) {
                $('#' + return_id).remove();
                $('#PData').DataTable().fnClearTable();
                if (data) {
                    addAlert(data, 'success');
                }
            },
            error: function (data) {
                addAlert('Failed', 'danger');
            }
        });
        return false;
    });
    $(document).on('click', '.po-delete1', function (e) {
        e.preventDefault();
        $('.po').popover('hide');
        var link = $(this).attr('href');
        var s = $(this).attr('id');
        var sp = s.split('__')
        $.ajax({
            type: "get",
            url: link,
            success: function (data) {
                if (data) {
                    addAlert(data, 'success');
                }
                $('#' + sp[1]).remove();
            },
            error: function (data) {
                addAlert('Failed', 'danger');
            }
        });
        return false;
    });
    $('body').on('click', '.bpo', function (e) {
        e.preventDefault();
        $(this).popover({
            html: true,
            trigger: 'manual'
        }).popover('toggle');
        return false;
    });
    $('body').on('click', '.bpo-close', function (e) {
        $('.bpo').popover('hide');
        return false;
    });
    $('#genNo').click(function () {
        var no = generateCardNo();
        $(this).parent().parent('.input-group').children('input').val(no);
        return false;
    });
    $('#inlineCalc').calculator({
        layout: ['_%+-CABS', '_7_8_9_/', '_4_5_6_*', '_1_2_3_-', '_0_._=_+'],
        showFormula: true
    });
    $('.calc').click(function (e) {
        e.stopPropagation();
    });
    $(document).on('click', '.sname', function (e) {
        var row = $(this).closest('tr');
        var itemid = row.find('.rid').val();
        $('#myModal').modal({
            remote: site.base_url + 'products/modal_view/' + itemid
        });
        $('#myModal').modal('show');
    });
});

function addAlert(message, type) {
    $('.alerts-con').empty().append(
        '<div class="alert alert-' + type + '">' +
        '<button type="button" class="close" data-dismiss="alert">' +
        '&times;</button>' + message + '</div>');
}

$(document).ready(function () {
    if ($.cookie('sma_sidebar') == 'minified') {
        $('#main-menu-act').removeClass("full").addClass("minified").find("i").removeClass("fa-angle-double-left").addClass("fa-angle-double-right");
        $("body").addClass("sidebar-minified");
        $("#content").addClass("sidebar-minified");
        $("#sidebar-left").addClass("minified");
        $(".dropmenu > .chevron").removeClass("opened").addClass("closed");
        $(".dropmenu").parent().find("ul").hide();
        $("#sidebar-left > div > ul > li > a > .chevron").removeClass("closed").addClass("opened");
        $("#sidebar-left > div > ul > li > a").addClass("open");
        $("#sidebar-left > div > ul > li > ul > li > a").removeClass("have_left_padding");
        $("#sidebar-left > div > ul > li > ul > li > a").addClass("no_left_padding");
        $('#fixed').hide();
    } else {

        $('#main-menu-act').removeClass("minified").addClass("full").find("i").removeClass("fa-angle-double-right").addClass("fa-angle-double-left");
        $("body").removeClass("sidebar-minified");
        $("#content").removeClass("sidebar-minified");
        $("#sidebar-left").removeClass("minified");
        $("#sidebar-left > div > ul > li > a > .chevron").removeClass("opened").addClass("closed");
        $("#sidebar-left > div > ul > li > a").removeClass("open");
        $("#sidebar-left > div > ul > li > ul > li > a").addClass("have_left_padding");
        $("#sidebar-left > div > ul > li > ul > li > a").removeClass("no_left_padding");
        $('#fixed').show();
    }
});

$(document).ready(function () {
    $('#daterange').daterangepicker({
        timePicker: true,
        format: (site.dateFormats.js_sdate).toUpperCase() + ' HH:mm',
        ranges: {
            'Today': [moment().hours(0).minutes(0).seconds(0), moment()],
            'Yesterday': [moment().subtract('days', 1).hours(0).minutes(0).seconds(0), moment().subtract('days', 1).hours(23).minutes(59).seconds(59)],
            'Last 7 Days': [moment().subtract('days', 6).hours(0).minutes(0).seconds(0), moment().hours(23).minutes(59).seconds(59)],
            'Last 30 Days': [moment().subtract('days', 29).hours(0).minutes(0).seconds(0), moment().hours(23).minutes(59).seconds(59)],
            'This Month': [moment().startOf('month').hours(0).minutes(0).seconds(0), moment().endOf('month').hours(23).minutes(59).seconds(59)],
            'Last Month': [moment().subtract('month', 1).startOf('month').hours(0).minutes(0).seconds(0), moment().subtract('month', 1).endOf('month').hours(23).minutes(59).seconds(59)]
        }
    },
        function (start, end) {
            refreshPage(start.format('YYYY-MM-DD HH:mm'), end.format('YYYY-MM-DD HH:mm'));
        });
});

function refreshPage(start, end) {
    window.location.replace(CURI + '/' + encodeURIComponent(start) + '/' + encodeURIComponent(end));
}

function retina() {
    retinaMode = window.devicePixelRatio > 1;
    return retinaMode
}

$(document).ready(function () {
    $('#cssLight').click(function (e) {
        e.preventDefault();
        $.cookie('sma_style', 'light', {
            path: '/'
        });
        cssStyle();
        return true;
    });
    $('#cssBlue').click(function (e) {
        e.preventDefault();
        $.cookie('sma_style', 'blue', {
            path: '/'
        });
        cssStyle();
        return true;
    });
    $('#cssBlack').click(function (e) {
        e.preventDefault();
        $.cookie('sma_style', 'black', {
            path: '/'
        });
        cssStyle();
        return true;
    });
    $('#cssHotPink').click(function (e) {
        e.preventDefault();
        $.cookie('sma_style', 'pink', {
            path: '/'
        });
        cssStyle();
        return true;
    });
    $("#toTop").click(function (e) {
        e.preventDefault();
        $("html, body").animate({
            scrollTop: 0
        }, 100);
    });
    $(document).on('click', '.delimg', function (e) {
        e.preventDefault();
        var ele = $(this),
            id = $(this).attr('data-item-id');
        bootbox.confirm(lang.r_u_sure, function (result) {
            if (result == true) {
                $.get(site.base_url + 'products/delete_image/' + id, function (data) {
                    if (data.error === 0) {
                        addAlert(data.msg, 'success');
                        ele.parent('.gallery-image').remove();
                    }
                });
            }
        });
        return false;
    });
});
$(document).ready(function () {
    $(document).on('click', '.row_status', function (e) {
        e.preventDefault;
        var row = $(this).closest('tr');
        var id = row.attr('id');
        if (row.hasClass('invoice_link')) {
            $('#myModal').modal({
                remote: site.base_url + 'sales/update_status/' + id,
                backdrop: 'static'
            });
            $('#myModal').modal('show');
        } else if (row.hasClass('purchase_link')) {
            $('#myModal').modal({
                remote: site.base_url + 'purchases/update_status/' + id,
                backdrop: 'static'
            });
            $('#myModal').modal('show');
        } else if (row.hasClass('quote_link')) {
            $('#myModal').modal({
                remote: site.base_url + 'quotes/update_status/' + id,
                backdrop: 'static'
            });
            $('#myModal').modal('show');
        } else if (row.hasClass('transfer_link')) {
            $('#myModal').modal({
                remote: site.base_url + 'transfers/update_status/' + id,
                backdrop: 'static'
            });
            $('#myModal').modal('show');
        }
        return false;
    });

    $(document).on('click', '.payment_status', function (e) {
        e.preventDefault;
        var row = $(this).closest('tr');
        var id = row.attr('id');
        if (row.hasClass('invoice_link')) {
            $('#myModal').modal({
                remote: site.base_url + 'sales/payments/' + id,
                backdrop: 'static'
            });
            $('#myModal').modal('show');
        }
        return false;
    });

    $(document).on('click', '.deliv_status', function (e) {
        e.preventDefault;
        var row = $(this).closest('tr');
        var id = row.attr('id');
        if (row.hasClass('invoice_link')) {
            $('#myModal').modal({
                remote: site.base_url + 'sales/add_delivery/' + id,
                backdrop: 'static'
            });
            $('#myModal').modal('show');
        }
        return false;
    });
});
/*
 $(window).scroll(function() {
    if ($(this).scrollTop()) {
        $('#toTop').fadeIn();
    } else {
        $('#toTop').fadeOut();
    }
 });
*/
$(document).on('ifChecked', '.checkth, .checkft', function (event) {
    $('.checkth, .checkft').iCheck('check');
    $('.multi-select').each(function () {
        $(this).iCheck('check');
    });
});
$(document).on('ifUnchecked', '.checkth, .checkft', function (event) {
    $('.checkth, .checkft').iCheck('uncheck');
    $('.multi-select').each(function () {
        $(this).iCheck('uncheck');
    });
});
$(document).on('ifUnchecked', '.multi-select', function (event) {
    $('.checkth, .checkft').attr('checked', false);
    $('.checkth, .checkft').iCheck('update');
});

function check_add_item_val() {
    $('#add_item').bind('keypress', function (e) {
        if (e.keyCode == 13 || e.keyCode == 9) {
            e.preventDefault();
            $(this).autocomplete("search");
        }
    });
}

function fld(oObj) {
    if (oObj != null) {
        var date = new Date(oObj);
        var hours = date.getHours();
        var minutes = date.getMinutes();
        hours = hours < 10 && hours > 0 ? '0' + hours : hours;
        minutes = minutes < 10 ? '0' + minutes : minutes;
        var _time = hours + ':' + minutes;
        var aDate = oObj.split('-');
        var bDate = aDate[2].split(' ');
        year = aDate[0], month = aDate[1], day = bDate[0], time = bDate[1];
        if (site.dateFormats.js_sdate == 'dd-mm-yyyy')
            return day + "-" + month + "-" + year + " " + _time;
        else if (site.dateFormats.js_sdate === 'dd/mm/yyyy')
            return day + "/" + month + "/" + year + " " + _time;
        else if (site.dateFormats.js_sdate == 'dd.mm.yyyy')
            return day + "." + month + "." + year + " " + _time;
        else if (site.dateFormats.js_sdate == 'mm/dd/yyyy')
            return month + "/" + day + "/" + year + " " + _time;
        else if (site.dateFormats.js_sdate == 'mm-dd-yyyy')
            return month + "-" + day + "-" + year + " " + _time;
        else if (site.dateFormats.js_sdate == 'mm.dd.yyyy')
            return month + "." + day + "." + year + " " + _time;
        else
            return oObj;
    } else {
        return '';
    }
}

function fldd(oObj) {
    if (oObj != null) {
        var aDate = oObj.split('-');
        var bDate = aDate[2].split(' ');
        year = aDate[0], month = aDate[1], day = bDate[0];
        if (site.dateFormats.js_sdate == 'dd-mm-yyyy')
            return day + "-" + month + "-" + year;
        else if (site.dateFormats.js_sdate === 'dd/mm/yyyy')
            return day + "/" + month + "/" + year;
        else if (site.dateFormats.js_sdate == 'dd.mm.yyyy')
            return day + "." + month + "." + year;
        else if (site.dateFormats.js_sdate == 'mm/dd/yyyy')
            return month + "/" + day + "/" + year;
        else if (site.dateFormats.js_sdate == 'mm-dd-yyyy')
            return month + "-" + day + "-" + year;
        else if (site.dateFormats.js_sdate == 'mm.dd.yyyy')
            return month + "." + day + "." + year;
        else
            return oObj;
    } else {
        return '';
    }
}

function fsd(oObj) {
    if (oObj != null) {
        var aDate = oObj.split('-');
        if (site.dateFormats.js_sdate == 'dd-mm-yyyy')
            return aDate[2] + "-" + aDate[1] + "-" + aDate[0];
        else if (site.dateFormats.js_sdate === 'dd/mm/yyyy')
            return aDate[2] + "/" + aDate[1] + "/" + aDate[0];
        else if (site.dateFormats.js_sdate == 'dd.mm.yyyy')
            return aDate[2] + "." + aDate[1] + "." + aDate[0];
        else if (site.dateFormats.js_sdate == 'mm/dd/yyyy')
            return aDate[1] + "/" + aDate[2] + "/" + aDate[0];
        else if (site.dateFormats.js_sdate == 'mm-dd-yyyy')
            return aDate[1] + "-" + aDate[2] + "-" + aDate[0];
        else if (site.dateFormats.js_sdate == 'mm.dd.yyyy')
            return aDate[1] + "." + aDate[2] + "." + aDate[0];
        else
            return oObj;
    } else {
        return '';
    }
}

function mount(oObj) {
    if (oObj != null) {
        if (oObj == '1')
            return '<div class="text-center"><span class="label label-info">Januari</span></div>';
        else if (oObj == '2')
            return '<div class="text-center"><span class="label label-info">Februari</span></div>';
        else if (oObj == '3')
            return '<div class="text-center"><span class="label label-info">Maret</span></div>';
        else if (oObj == '4')
            return '<div class="text-center"><span class="label label-info">April</span></div>';
        else if (oObj == '5')
            return '<div class="text-center"><span class="label label-info">Mei</span></div>';
        else if (oObj == '6')
            return '<div class="text-center"><span class="label label-info">Juni</span></div>';
        else if (oObj == '7')
            return '<div class="text-center"><span class="label label-info">Juli</span></div>';
        else if (oObj == '8')
            return '<div class="text-center"><span class="label label-info">Agustus</span></div>';
        else if (oObj == '9')
            return '<div class="text-center"><span class="label label-info">September</span></div>';
        else if (oObj == '10')
            return '<div class="text-center"><span class="label label-info">Oktober</span></div>';
        else if (oObj == '11')
            return '<div class="text-center"><span class="label label-info">November</span></div>';
        else if (oObj == '12')
            return '<div class="text-center"><span class="label label-info">December</span></div>';
        else
            return oObj;
    } else {
        return '';
    }
}

function generateCardNo(x) {
    if (!x) {
        x = 16;
    }
    chars = "1234567890";
    no = "";
    for (var i = 0; i < x; i++) {
        var rnum = Math.floor(Math.random() * chars.length);
        no += chars.substring(rnum, rnum + 1);
    }
    return no;
}

function itd(oObj) {
    if (oObj != null) {
        var date = new Date(1000 * oObj);
        return date.toLocaleString();
    } else {
        return '';
    }
}

function roundNumber(num, nearest) {
    if (!nearest) {
        nearest = 0.05;
    }
    return Math.round((num / nearest) * nearest);
}

function getNumber(x) {
    return accounting.unformat(x);
}

function formatQuantity(x) {
    return (x != null) ? '<div class="text-center">' + formatNumber(x, site.settings.qty_decimals) + '</div>' : '';
}

function formatQuantity2(x) {
    return (x != null) ? formatNumber(x, site.settings.qty_decimals) : '';
}

function formatNumber(x, d) {
    if (!d && d != 0) {
        d = site.settings.decimals;
    }
    if (site.settings.sac == 1) {
        return formatSA(parseFloat(x).toFixed(d));
    }
    return accounting.formatNumber(x, d, site.settings.thousands_sep == 0 ? ' ' : site.settings.thousands_sep, site.settings.decimals_sep);
}

function formatMoney(x, symbol) {
    if (!symbol) {
        symbol = "";
    }
    if (site.settings.sac == 1) {
        return (site.settings.display_symbol == 1 ? site.settings.symbol : '') +
            '' + formatSA(parseFloat(x).toFixed(site.settings.decimals)) +
            (site.settings.display_symbol == 2 ? site.settings.symbol : '');
    }
    var fmoney = accounting.formatMoney(x, symbol, site.settings.decimals, site.settings.thousands_sep == 0 ? ' ' : site.settings.thousands_sep, site.settings.decimals_sep, "%s%v");
    return (site.settings.display_symbol == 1 ? site.settings.symbol : '') +
        fmoney +
        (site.settings.display_symbol == 2 ? site.settings.symbol : '');
}

function is_valid_discount(mixed_var) {
    return (is_numeric(mixed_var) || (/([0-9]%)/i.test(mixed_var))) ? true : false;
}

function promo_status(stat) {
    if (stat === '0') {
        return '<div class="text-center"><span class="label label-danger">inactive</span></div>';
    } else {
        return '<div class="text-center"><span class="label label-success">active</span></div>';
    }
}

function type_news(stat) {
    if (stat === 'promo') {
        return '<div class="text-center"><span class="label label-warning">Promotion</span></div>';
    } else {
        return '<div class="text-center"><span class="label label-info">Information</span></div>';
    }
}

function card_no(stat) {
    if (stat === '-1') {
        return '<p style="text-align: center;">-</p>';
    } else {
        return stat;
    }
}

function is_numeric(mixed_var) {
    var whitespace =
        " \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000";
    return (typeof mixed_var === 'number' || (typeof mixed_var === 'string' && whitespace.indexOf(mixed_var.slice(-1)) === -
        1)) && mixed_var !== '' && !isNaN(mixed_var);
}

function is_float(mixed_var) {
    return +mixed_var === mixed_var && (!isFinite(mixed_var) || !!(mixed_var % 1));
}

function decimalFormat(x) {
    if (x != null) {
        return '<div class="text-center">' + formatNumber(x) + '</div>';
    } else {
        return '<div class="text-center">0</div>';
    }
}

function currencyFormat(x) {
    // if(x < 0)
    //     return '<p style="text-align: center;">-</p>';

    if (x != null) {
        return '<div class="text-right">' + formatMoney(x) + '</div>';
    } else {
        return '<div class="text-right">0</div>';
    }
}

function formatDecimal(x, d) {
    if (!d) {
        d = site.settings.decimals;
    }
    return parseFloat(accounting.formatNumber(x, d, '', '.'));
}

function formatDecimals(x, d) {
    if (!d) {
        d = site.settings.decimals;
    }
    return parseFloat(accounting.formatNumber(x, d, '', '.')).toFixed(d);
}

function pqFormat(x) {
    if (x != null) {
        var d = '',
            pqc = x.split("___");
        for (index = 0; index < pqc.length; ++index) {
            var pq = pqc[index];
            var v = pq.split("__");
            d += v[0] + ' (' + formatQuantity2(v[1]) + ')<br>';
        }
        return d;
    } else {
        return '';
    }
}

function checkbox(x) {
    return '<div class="text-center"><input class="checkbox multi-select" type="checkbox" name="val[]" value="' + x + '" /></div>';
}

function decode_html(value) {
    return $('<div/>').html(value).text();
}

function img_hl(x) {
    // return x == null ? '' : '<div class="text-center"><ul class="enlarge"><li><img src="'+site.base_url+'assets/uploads/thumbs/' + x + '" alt="' + x + '" style="width:30px; height:30px;" class="img-circle" /><span><a href="'+site.base_url+'assets/uploads/' + x + '" data-toggle="lightbox"><img src="'+site.base_url+'assets/uploads/' + x + '" alt="' + x + '" style="width:200px;" class="img-thumbnail" /></a></span></li></ul></div>';
    var image_link = (x == null || x == '') ? 'no_image.png' : x;
    // return '<div class="text-center"><a href="' + site.base_url + 'assets/uploads/' + image_link + '" data-toggle="lightbox"><img src="' + site.base_url + 'assets/uploads/thumbs/' + image_link + '" alt="" style="width:30px; height:30px;" /></a></div>';

    var urlregex = new RegExp(
        "^((http|https|ftp)\://)*([a-zA-Z0-9\.\-]+(\:[a-zA-Z0-9\.&amp;%\$\-]+)*@)*((25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])|([a-zA-Z0-9\-]+\.)*[a-zA-Z0-9\-]+\.(com|edu|gov|int|mil|net|org|biz|arpa|info|name|pro|aero|coop|museum|[a-zA-Z]{2}))(\:[0-9]+)*(/($|[a-zA-Z0-9\.\,\?\'\\\+&amp;%\$#\=~_\-]+))*$");
    if(urlregex.test(x)){
        return '<div class="text-center"><a href="' + x + '" data-toggle="lightbox"><img src="' + x + '" alt="" style="width:30px; height:30px;" /></a></div>';
    }
    return '<div class="text-center"><a href="' + site.base_url + 'assets/uploads/' + image_link + '" data-toggle="lightbox"><img src="' + site.base_url + 'assets/uploads/thumbs/' + image_link + '" alt="" style="width:30px; height:30px;" /></a></div>';
}

function img_product(x) {
    var url = x.split('___');
    var image_link = (url[1] == null || url[1] == '') ? 'no_image.png' : url[1];
    var image_thumb = (url[0] == null || url[0] == '') ? 'no_image.png' : url[0];
    return '<div class="text-center"><a href="' + image_link + '" data-toggle="lightbox"><img src="' + image_thumb + '" alt="" style="width:30px; height:30px;" /></a></div>';
}

function attachment(x) {
    return x == null ? '' : '<div class="text-center"><a href="' + site.base_url + 'welcome/download/' + x + '" class="tip" title="' + lang.download + '"><i class="fa fa-file"></i></a></div>';
}

function attachment2(x) {
    return x == null ? '' : '<div class="text-center"><a href="' + site.base_url + 'welcome/download/' + x + '" class="tip" title="' + lang.download + '"><i class="fa fa-file-o"></i></a></div>';
}

function attachment3(x) {
    return x == null ? '' : '<div class="text-center"><a href="' + x + '" class="tip" title="' + lang.download + '"><i class="fa fa-file-o"></i></a></div>';
}

function validate_url(value) {
    var urlregex = new RegExp(
        "^((http|https|ftp)\://)*([a-zA-Z0-9\.\-]+(\:[a-zA-Z0-9\.&amp;%\$\-]+)*@)*((25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])|([a-zA-Z0-9\-]+\.)*[a-zA-Z0-9\-]+\.(com|edu|gov|int|mil|net|org|biz|arpa|info|name|pro|aero|coop|museum|[a-zA-Z]{2}))(\:[0-9]+)*(/($|[a-zA-Z0-9\.\,\?\'\\\+&amp;%\$#\=~_\-]+))*$");
    if(urlregex.test(value)){
        return(attachment3(value));
    }
    return(attachment(value));
}

function user_status(x) {
    var y = x.split("__");
    return y[0] == 1 ?
        '<a href="' + site.base_url + 'auth/deactivate/' + y[1] + '" data-toggle="modal" data-target="#myModal"  data-backdrop="static"><span class="label label-success"><i class="fa fa-check"></i> ' + lang['active'] + '</span></a>' :
        '<a href="' + site.base_url + 'auth/activate/' + y[1] + '"><span class="label label-danger"><i class="fa fa-times"></i> ' + lang['inactive'] + '</span><a/>';
}

function all_user_status(x) {
    var y = x.split("__");
    return y[0] == 1 ?
        '<a href="' + site.base_url + 'auth/deactivate_users/' + y[1] + '" data-toggle="modal" data-target="#myModal"  data-backdrop="static"><span class="label label-success"><i class="fa fa-check"></i> ' + lang['active'] + '</span></a>' :
        '<a href="' + site.base_url + 'auth/activate_users/' + y[1] + '"><span class="label label-danger"><i class="fa fa-times"></i> ' + lang['inactive'] + '</span><a/>';
}

function user_type(x) {
    return x == 1 ? '<span class="label label-danger"> AksesToko </span>' : '<span class="label label-info"> ForcaPOS </span>';
}

function feedback_flag(x) {
    return x == 1 ? '<span class="label label-success text-center"> AksesToko </span>' : '<span class="label label-info text-center"> ForcaPOS </span>';
}

function sales_person_status(x) {
    var y = x.split("__");
    return y[0] == 1 ?
        `<div style="text-align: center;"><span class="label label-success"><i class="fa fa-check"></i> ` + lang['active'] + `</span></div>` :
        `<div style="text-align: center;"><span class="label label-danger"><i class="fa fa-times"></i> ` + lang['inactive'] + `</span></div>`;
}

function ds(x) {
    if (x == 'delivered') {
        return '<div class="text-center"><span class="label label-success">' + (dss[x] ? dss[x] : x) + '</span></div>';
    } else if (x == 'delivering') {
        return '<div class="text-center"><span class="label label-primary">' + (dss[x] ? dss[x] : x) + '</span></div>';
    } else if (x == 'packing') {
        return '<div class="text-center"><span class="label label-warning">' + (dss[x] ? dss[x] : x) + '</span></div>';
    } else if (x == 'returned') {
        return '<div class="text-center"><span class="label label-danger">' + (dss[x] ? dss[x] : x) + '</span></div>';
    }
    return x;
    return (x != null) ? (dss[x] ? dss[x] : x) : x;
}

function row_status(x) {

    if (x == null) {
        return '';
    } else if (x == 'pending') {
        return '<div class="text-center"><span class="row_status label label-warning">' + lang[x] + '</span></div>';
    } else if (x == 'completed' || x == 'paid' || x == 'sent' || x == 'received' || x == 'closed') {
        return '<div class="text-center"><span class="row_status label label-success">' + lang[x] + '</span></div>';
    } else if (x == 'transferring' || x == 'ordered' || x == 'confirmed') {
        return '<div class="text-center"><span class="row_status label label-info">' + lang[x] + '</span></div>';
    } else if (x == 'due' || x == 'returned' || x == 'canceled' || x == 'close') {
        return '<div class="text-center"><span class="row_status label label-danger">' + lang[x] + '</span></div>';
    } else if (x == 'delivering' || x == 'partial' || x == 'reserved') {
        return '<div class="text-center"><span class="row_status label label-primary">' + lang[x] + '</span></div>'
    } else {
        let status = x.split("-", 2);
        if (status[1] === "unwatched") {
            return '<div class="text-center"><span class="row_status label label-info">' + lang[status[0]] + '<span class="dot"></span></span></div>';
        }
        return '<div class="text-center"><span class="row_status label label-default">' + lang[x] + '</span></div>';
    }
}

function user_aksestoko_status(x) {
    var y = x.split("__");
    return y[0] == 1 || y[0] == '' ?
        '<div class="text-center"><span class="label label-success"><i class="fa fa-check"></i> ' + lang['active'] + '</span></div>' :
        '<div class="text-center"><span class="label label-danger"><i class="fa fa-times"></i> ' + lang['inactive'] + '</span></div>';
}

function phone_aksestoko_status(x) {
    return x == 1 ?
        '<div class="text-center"><span class="label label-success"><i class="fa fa-check"></i> ' + lang['verified'] + '</span></div>' :
        '<div class="text-center"><span class="label label-danger"><i class="fa fa-times"></i> ' + lang['unverified'] + '</span></div>';
}

function pay_status(x) {
    if (x == null) {
        return '';
    } else if (x == 'pending') {
        return '<div class="text-center"><span class="payment_status label label-warning">' + lang[x] + '</span></div>';
    } else if (x == 'completed' || x == 'paid' || x == 'sent' || x == 'received') {
        return '<div class="text-center"><span class="payment_status label label-success">' + lang[x] + '</span></div>';
    } else if (x == 'partial' || x == 'transferring' || x == 'ordered') {
        return '<div class="text-center"><span class="payment_status label label-primary">' + lang[x] + '</span></div>';
    } else if (x == 'waiting') {
        return '<div class="text-center"><span class="payment_status label label-info">' + lang[x] + '</span></div>';
    } else if (x == 'due' || x == 'returned' || x == 'canceled') {
        return '<div class="text-center"><span class="payment_status label label-danger">' + lang[x] + '</span></div>';
    } else {
        return '<div class="text-center"><span class="payment_status label label-default">' + lang[x] + '</span></div>';
    }
}

function deliv_status(x) {
    if (x == null) {
        return '';
    } else if (x == 'done') {
        return '<div class="text-center"><span class="deliv_status label label-success">' + lang[x] + '</span></div>';
    } else if (x == 'partial') {
        return '<div class="text-center"><span class="deliv_status label label-primary">' + lang[x] + '</span></div>';
    } else if (x == 'pending') {
        return '<div class="text-center"><span class="deliv_status label label-warning">' + lang[x] + '</span></div>';
    }
}

function status_kredit_pro(x) {
    var status_ = x.split('|');
    let status = x.includes('|') ? status_[1] : x;

    let return_ = '';

    if (status == 'waiting') {
        return_ = '<div class="text-center"><span class="payment_status label label-warning">' + lang['credit_reviewed'] + '</span></div>';
    } else if (status == 'accept') {
        return_ = '<div class="text-center"><span class="payment_status label label-info">' + lang['credit_received'] + '</span></div>';
    } else if (status == 'reject') {
        return_ = '<div class="text-center"><span class="payment_status label label-danger">' + lang['credit_declined'] + '</span></div>';
    } else if (status == 'partial') {
        return_ = '<div class="text-center"><span class="payment_status label label-primary">' + lang['kredit_partial'] + '</span></div>';
    } else if (status == 'paid') {
        return_ = '<div class="text-center"><span class="payment_status label label-success">' + lang['already_paid'] + '</span></div>';
    } else if (status == 'pending') {
        return_ = '-';
    }

    return return_;
}

function status_user_plugin(x) {
    if (x == '' || x == null || x == 'non_aktif') {
        return '<div class="text-center"><span class="status_plugin label label-danger"> Non Aktif </span></div>';
    } else if (x == 'aktif') {
        return '<div class="text-center"><span class="deliv_status label label-success"> Aktif </span></div>';
    }
}

function action_warehouse(url) {
    arrUrl = url.split("|");
    var strAction =
        '<div class="text-center">' +
        '<a href="' + arrUrl[1] + '" class="tip" title="edit warehouse" data-toggle="modal" data-target="#myModal"  data-backdrop="static">' +
        '<i class="fa fa-edit"></i>' +
        '</a> ';
    if (arrUrl[0] == '1') {
        strAction +=
            '<a href="#" class="tip po" title="<b> recover warehouse</b>"  data-content="<p> are you sure </p> <a class=\'btn btn-danger po-delete\' href=\'' + arrUrl[3] + '\'> Iam Sure</a> <button class=\'btn po-close\'> no</button>"  rel="popover">' +
            '<i class="fa fa-recycle "></i>' +
            '</a>';
    } else {
        strAction +=
            '<a href="#" class="tip po" title="<b> delete warehouse</b>"  data-content="<p> are you sure </p> <a class=\'btn btn-danger po-delete\' href=\'' + arrUrl[2] + '\'> Iam Sure</a> <button class=\'btn po-close\'> no</button>"  rel="popover">' +
            '<i class="fa fa-trash-o  "></i>' +
            '</a>';
    }
    strAction += '</div>';
    return strAction;
}

function action_sales_person(url) {
    arrUrl = url.split("||");
    var strAction =
        '<div class="text-center">' +
        '<a href="' + arrUrl[1] + '" class="tip" title="' + lang['edit_sales_person'] + '" data-toggle="modal" data-target="#myModal"  data-backdrop="static" id="salesPersonEdit">' +
        '<i class="fa fa-edit"></i>' +
        '</a> ';
    // if (arrUrl[0] == '1') {
    strAction +=
        '<a href="' + arrUrl[2] + '" class="tip" title="' + lang['add_customer_to_sales_person'] + '">' +
        '<i class="fa fa-plus"></i>' +
        '</a>';
    // }
    strAction += '</div>';
    return strAction;
}

function pay_method(x) {
    return x ? lang[x] : '';
}

function top_status(x) {
    var y = x.split("__");
    return y[0] == 1 ?
        '<div class="text-center"><span class="label label-success"><i class="fa fa-check"></i> ' + lang['active'] + '</span></div>' :
        '<div class="text-center"><span class="label label-danger"><i class="fa fa-times"></i> ' + lang['inactive'] + '</span></div>';
}

function formatSA(x) {
    x = x.toString();
    var afterPoint = '';
    if (x.indexOf('.') > 0)
        afterPoint = x.substring(x.indexOf('.'), x.length);
    x = Math.floor(x);
    x = x.toString();
    var lastThree = x.substring(x.length - 3);
    var otherNumbers = x.substring(0, x.length - 3);
    if (otherNumbers != '')
        lastThree = ',' + lastThree;
    var res = otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ",") + lastThree + afterPoint;

    return res;
}

function unitToBaseQty(qty, unitObj) {
    switch (unitObj.operator) {
        case '*':
            return parseFloat(qty) * parseFloat(unitObj.operation_value);
            break;
        case '/':
            return parseFloat(qty) / parseFloat(unitObj.operation_value);
            break;
        case '+':
            return parseFloat(qty) + parseFloat(unitObj.operation_value);
            break;
        case '-':
            return parseFloat(qty) - parseFloat(unitObj.operation_value);
            break;
        default:
            return parseFloat(qty);
    }
}

function baseToUnitQty(qty, unitObj) {
    switch (unitObj.operator) {
        case '*':
            return parseFloat(qty) / parseFloat(unitObj.operation_value);
            break;
        case '/':
            return parseFloat(qty) * parseFloat(unitObj.operation_value);
            break;
        case '+':
            return parseFloat(qty) - parseFloat(unitObj.operation_value);
            break;
        case '-':
            return parseFloat(qty) + parseFloat(unitObj.operation_value);
            break;
        default:
            return parseFloat(qty);
    }
}

function set_page_focus() {
    if (site.settings.set_focus == 1) {
        $('#add_item').attr('tabindex', an);
        $('[tabindex=' + (an - 1) + ']').focus().select();
    } else {
        $('#add_item').attr('tabindex', 1);
        $('#add_item').focus();
    }
    $('.rquantity').bind('keypress', function (e) {
        if (e.keyCode == 13) {
            $('#add_item').focus();
        }
    });
}

$(document).ready(function () {
    $('#view-customer').click(function () {
        $('#myModal').modal({
            remote: site.base_url + 'customers/view/' + $("input[name=customer]").val(),
            backdrop: 'static'
        });
        $('#myModal').modal('show');
    });
    $('#view-supplier').click(function () {
        $('#myModal').modal({
            remote: site.base_url + 'suppliers/view/' + $("input[name=supplier]").val(),
            backdrop: 'static'
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.customer_details_link td:not(:first-child, :last-child)', function () {
        $('#myModal').modal({
            remote: site.base_url + 'customers/view/' + $(this).parent('.customer_details_link').attr('id'),
            backdrop: 'static'
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.supplier_details_link td:not(:first-child, :last-child)', function () {
        $('#myModal').modal({
            remote: site.base_url + 'suppliers/view/' + $(this).parent('.supplier_details_link').attr('id'),
            backdrop: 'static'
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.product_link td:not(:first-child, :nth-child(2), :last-child)', function () {
        $('#myModal').modal({
            remote: site.base_url + 'products/modal_view/' + $(this).parent('.product_link').attr('id'),
            backdrop: 'static'
        });
        $('#myModal').modal('show');
        //window.location.href = site.base_url + 'products/view/' + $(this).parent('.product_link').attr('id'), backdrop: 'static' ;
    });
    $('body').on('click', '.product_link2 td:first-child, .product_link2 td:nth-child(2)', function () {
        $('#myModal').modal({
            remote: site.base_url + 'products/modal_view/' + $(this).closest('tr').attr('id'),
            backdrop: 'static'
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.purchase_link td:not(:first-child, :nth-child(5), :nth-last-child(2), :last-child)', function () {
        $('#myModal').modal({
            remote: site.base_url + 'purchases/modal_view/' + $(this).parent('.purchase_link').attr('id'),
            backdrop: 'static'
        });
        $('#myModal').modal('show');
        //window.location.href = site.base_url + 'purchases/view/' + $(this).parent('.purchase_link').attr('id'), backdrop: 'static' ;
    });
    $('body').on('click', '.purchase_link2 td', function () {
        $('#myModal').modal({
            remote: site.base_url + 'purchases/modal_view/' + $(this).closest('tr').attr('id'),
            backdrop: 'static'
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.transfer_link td:not(:first-child, :nth-last-child(3), :nth-last-child(2), :last-child)', function () {
        $('#myModal').modal({
            remote: site.base_url + 'transfers/view/' + $(this).parent('.transfer_link').attr('id'),
            backdrop: 'static'
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.transfer_link2', function () {
        $('#myModal').modal({
            remote: site.base_url + 'transfers/view/' + $(this).attr('id'),
            backdrop: 'static'
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.invoice_link td:not(:first-child, :nth-child(6), :nth-last-child(2),:nth-last-child(4), :nth-last-child(3), :last-child)', function () {
        $('#myModal').modal({
            remote: site.base_url + 'sales/modal_view/' + $(this).parent('.invoice_link').attr('id'),
            backdrop: 'static'
        });
        $('#myModal').modal('show');
        //window.location.href = site.base_url + 'sales/view/' + $(this).parent('.invoice_link').attr('id'), backdrop: 'static' ;
    });
    $('body').on('click', '.invoice_link2 td:not(:first-child, :last-child)', function () {
        $('#myModal').modal({
            remote: site.base_url + 'sales/modal_view/' + $(this).closest('tr').attr('id'),
            backdrop: 'static'
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.receipt_link td:not(:first-child, :last-child)', function () {
        $('#myModal').modal({
            remote: site.base_url + 'pos/view/' + $(this).parent('.receipt_link').attr('id') + '/1',
            backdrop: 'static'
        });
    });
    $('body').on('click', '.return_link td', function () {
        // window.location.href = site.base_url + 'sales/view_return/' + $(this).parent('.return_link').attr('id'), backdrop: 'static' ;
        $('#myModal').modal({
            remote: site.base_url + 'sales/view_return/' + $(this).parent('.return_link').attr('id'),
            backdrop: 'static'
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.return_purchase_link td', function () {
        $('#myModal').modal({
            remote: site.base_url + 'purchases/view_return/' + $(this).parent('.return_purchase_link').attr('id'),
            backdrop: 'static'
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.payment_link td', function () {
        $('#myModal').modal({
            remote: site.base_url + 'sales/payment_note/' + $(this).parent('.payment_link').attr('id'),
            backdrop: 'static'
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.payment_link2 td', function () {
        $('#myModal').modal({
            remote: site.base_url + 'purchases/payment_note/' + $(this).parent('.payment_link2').attr('id'),
            backdrop: 'static'
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.expense_link2 td:not(:last-child)', function () {
        $('#myModal').modal({
            remote: site.base_url + 'purchases/expense_note/' + $(this).closest('tr').attr('id'),
            backdrop: 'static'
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.quote_link td:not(:first-child, :nth-last-child(3), :nth-last-child(2), :last-child)', function () {
        $('#myModal').modal({
            remote: site.base_url + 'quotes/modal_view/' + $(this).parent('.quote_link').attr('id'),
            backdrop: 'static'
        });
        $('#myModal').modal('show');
        //window.location.href = site.base_url + 'quotes/view/' + $(this).parent('.quote_link').attr('id'), backdrop: 'static' ;
    });
    $('body').on('click', '.deliveries_smig_link td:not(:first-child, :last-child)', function () {
        $('#myModal').modal({
            remote: site.base_url + 'deliveries_smig/modal_view/' + $(this).parent('.deliveries_smig_link').attr('id'),
            backdrop: 'static'
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.quote_link2', function () {
        $('#myModal').modal({
            remote: site.base_url + 'quotes/modal_view/' + $(this).attr('id'),
            backdrop: 'static'
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.delivery_link td:not(:first-child, :nth-last-child(2), :nth-last-child(3), :last-child)', function () {
        $('#myModal').modal({
            remote: site.base_url + 'sales/view_delivery/' + $(this).parent('.delivery_link').attr('id'),
            backdrop: 'static'
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.delivery_link2', function () {
        $('#myModal').modal({
            remote: site.base_url + 'sales/view_delivery/' + $(this).attr('id'),
            backdrop: 'static'
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.customer_link td:not(:first-child)', function () {
        $('#myModal').modal({
            remote: site.base_url + 'customers/edit/' + $(this).parent('.customer_link').attr('id'),
            backdrop: 'static'
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.supplier_link td:not(:first-child)', function () {
        $('#myModal').modal({
            remote: site.base_url + 'suppliers/edit/' + $(this).parent('.supplier_link').attr('id'),
            backdrop: 'static'
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.adjustment_link td:not(:first-child, :nth-last-child(2), :last-child)', function () {
        $('#myModal').modal({
            remote: site.base_url + 'products/view_adjustment/' + $(this).parent('.adjustment_link').attr('id'),
            backdrop: 'static'
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.adjustment_link2', function () {
        $('#myModal').modal({
            remote: site.base_url + 'products/view_adjustment/' + $(this).attr('id'),
            backdrop: 'static'
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.consignment_link td:not(:first-child, :nth-last-child(2), :last-child)', function () {
        $('#myModal').modal({
            remote: site.base_url + 'products/view_consignment/' + $(this).parent('.consignment_link').attr('id'),
            backdrop: 'static'
        });
        $('#myModal').modal('show');
    });
    $('body').on('click', '.sales_person_details_link td:not(:first-child, :last-child)', function () {
        $('#myModal').modal({
            remote: site.base_url + 'sales_person/view/' + $(this).parent('.sales_person_details_link').attr('id'),
            backdrop: 'static'
        });
        $('#myModal').modal('show');
    });
    $('#clearLS').click(function (event) {
        bootbox.confirm(lang.r_u_sure, function (result) {
            if (result == true) {
                localStorage.clear();
                location.reload();
            }
        });
        return false;
    });
    $(document).on('click', '[data-toggle="ajax"]', function (e) {
        e.preventDefault();
        var href = $(this).attr('href');
        $.get(href, function (data) {
            $("#myModal").html(data).modal();
        });
    });
    $(".sortable_rows").sortable({
        items: "> tr",
        appendTo: "parent",
        helper: "clone",
        placeholder: "ui-sort-placeholder",
        axis: "x",
        update: function (event, ui) {
            var item_id = $(ui.item).attr('data-item-id');
        }
    }).disableSelection();
});

function fixAddItemnTotals() {
    var ai = $("#sticker");
    var aiTop = (ai.position().top) + 250;
    var bt = $("#bottom-total");
    $(window).scroll(function () {
        var windowpos = $(window).scrollTop();
        if (windowpos >= aiTop) {
            ai.addClass("stick").css('width', ai.parent('form').width()).css('zIndex', 2);
            if ($.cookie('sma_theme_fixed') == 'yes') {
                ai.css('top', '40px');
            } else {
                ai.css('top', 0);
            }
            $('#add_item').removeClass('input-lg');
            $('.addIcon').removeClass('fa-2x');
        } else {
            ai.removeClass("stick").css('width', bt.parent('form').width()).css('zIndex', 2);
            if ($.cookie('sma_theme_fixed') == 'yes') {
                ai.css('top', 0);
            }
            $('#add_item').addClass('input-lg');
            $('.addIcon').addClass('fa-2x');
        }
        if (windowpos <= ($(document).height() - $(window).height() - 120)) {
            bt.css('position', 'fixed').css('bottom', 0).css('width', bt.parent('form').width()).css('zIndex', 2);
        } else {
            bt.css('position', 'static').css('width', ai.parent('form').width()).css('zIndex', 2);
        }
    });
}

function isInt(number) {
    number = parseFloat(number);
    return number % 1 === 0;
}

function ItemnTotals() {
    fixAddItemnTotals();
    $(window).bind("resize", fixAddItemnTotals);
}

if (site.settings.auto_detect_barcode == 1) {
    $(document).ready(function () {
        var pressed = false;
        var chars = [];
        $(window).keypress(function (e) {
            chars.push(String.fromCharCode(e.which));
            if (pressed == false) {
                setTimeout(function () {
                    if (chars.length >= 8) {
                        var barcode = chars.join("");
                        $("#add_item").focus().autocomplete("search", barcode);
                    }
                    chars = [];
                    pressed = false;
                }, 200);
            }
            pressed = true;
        });
    });
}
$('.sortable_table tbody').sortable({
    containerSelector: 'tr'
});
$('input.number-only').bind('keypress', function (e) {
    return !(e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57) && e.which != 46);
});
$(window).bind("resize", widthFunctions);
$(window).load(widthFunctions);

// Notif
$(document).mouseup(function (e) {
    var container = $("#dropdown-notif");
    var tombol = $("#notif");
    let ikon = $('#loceng');
    // kondisi gawe target tombol & cont notif
    if (!container.is(e.target) && !ikon.is(e.target) && !tombol.is(e.target) && container.has(e.target).length === 0) {
        container.slideUp();
    }
});

$('#notif').click(function (e) {
    $('#dropdown-notif').slideToggle();
    e.preventDefault();
});