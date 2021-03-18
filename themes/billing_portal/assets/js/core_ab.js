/*!
 * accounting.js v0.4.1, copyright 2014 Open Exchange Rates, MIT license, http://openexchangerates.github.io/accounting.js
 */
(function(p,z){function q(a){return!!(""===a||a&&a.charCodeAt&&a.substr)}function m(a){return u?u(a):"[object Array]"===v.call(a)}function r(a){return"[object Object]"===v.call(a)}function s(a,b){var d,a=a||{},b=b||{};for(d in b)b.hasOwnProperty(d)&&null==a[d]&&(a[d]=b[d]);return a}function j(a,b,d){var c=[],e,h;if(!a)return c;if(w&&a.map===w)return a.map(b,d);for(e=0,h=a.length;e<h;e++)c[e]=b.call(d,a[e],e,a);return c}function n(a,b){a=Math.round(Math.abs(a));return isNaN(a)?b:a}function x(a){var b=c.settings.currency.format;"function"===typeof a&&(a=a());return q(a)&&a.match("%v")?{pos:a,neg:a.replace("-","").replace("%v","-%v"),zero:a}:!a||!a.pos||!a.pos.match("%v")?!q(b)?b:c.settings.currency.format={pos:b,neg:b.replace("%v","-%v"),zero:b}:a}var c={version:"0.4.1",settings:{currency:{symbol:"$",format:"%s%v",decimal:".",thousand:",",precision:2,grouping:3},number:{precision:0,grouping:3,thousand:",",decimal:"."}}},w=Array.prototype.map,u=Array.isArray,v=Object.prototype.toString,o=c.unformat=c.parse=function(a,b){if(m(a))return j(a,function(a){return o(a,b)});a=a||0;if("number"===typeof a)return a;var b=b||".",c=RegExp("[^0-9-"+b+"]",["g"]),c=parseFloat((""+a).replace(/\((.*)\)/,"-$1").replace(c,"").replace(b,"."));return!isNaN(c)?c:0},y=c.toFixed=function(a,b){var b=n(b,c.settings.number.precision),d=Math.pow(10,b);return(Math.round(c.unformat(a)*d)/d).toFixed(b)},t=c.formatNumber=c.format=function(a,b,d,i){if(m(a))return j(a,function(a){return t(a,b,d,i)});var a=o(a),e=s(r(b)?b:{precision:b,thousand:d,decimal:i},c.settings.number),h=n(e.precision),f=0>a?"-":"",g=parseInt(y(Math.abs(a||0),h),10)+"",l=3<g.length?g.length%3:0;return f+(l?g.substr(0,l)+e.thousand:"")+g.substr(l).replace(/(\d{3})(?=\d)/g,"$1"+e.thousand)+(h?e.decimal+y(Math.abs(a),h).split(".")[1]:"")},A=c.formatMoney=function(a,b,d,i,e,h){if(m(a))return j(a,function(a){return A(a,b,d,i,e,h)});var a=o(a),f=s(r(b)?b:{symbol:b,precision:d,thousand:i,decimal:e,format:h},c.settings.currency),g=x(f.format);return(0<a?g.pos:0>a?g.neg:g.zero).replace("%s",f.symbol).replace("%v",t(Math.abs(a),n(f.precision),f.thousand,f.decimal))};c.formatColumn=function(a,b,d,i,e,h){if(!a)return[];var f=s(r(b)?b:{symbol:b,precision:d,thousand:i,decimal:e,format:h},c.settings.currency),g=x(f.format),l=g.pos.indexOf("%s")<g.pos.indexOf("%v")?!0:!1,k=0,a=j(a,function(a){if(m(a))return c.formatColumn(a,f);a=o(a);a=(0<a?g.pos:0>a?g.neg:g.zero).replace("%s",f.symbol).replace("%v",t(Math.abs(a),n(f.precision),f.thousand,f.decimal));if(a.length>k)k=a.length;return a});return j(a,function(a){return q(a)&&a.length<k?l?a.replace(f.symbol,f.symbol+Array(k-a.length+1).join(" ")):Array(k-a.length+1).join(" ")+a:a})};if("undefined"!==typeof exports){if("undefined"!==typeof module&&module.exports)exports=module.exports=c;exports.accounting=c}else"function"===typeof define&&define.amd?define([],function(){return c}):(c.noConflict=function(a){return function(){p.accounting=a;c.noConflict=z;return c}}(p.accounting),p.accounting=c)})(this);


function isInt(number) {
    number = parseFloat(number);
    return number % 1 === 0;
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

function fdn(oObj) {
    if (oObj != null) {
        var aDate = oObj.split('-');
        return aDate[1] + "/" + aDate[2] + "/" + aDate[0];
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

function dateday(oObj) {
    if (oObj != null) {
        if (oObj == '01')
            return '1';
        else if (oObj == '02')
            return '2';
        else if (oObj == '03')
            return '3';
         else if (oObj == '04')
            return '4';
         else if (oObj == '05')
            return '5';
         else if (oObj == '06')
            return '6';
         else if (oObj == '07')
            return '7';
         else if (oObj == '08')
            return '8';
         else if (oObj == '09')
            return '9';
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
        return '<div class="text-center"><span class="label label-danger">disabled</span></div>';
    } else {
        return '<div class="text-center"><span class="label label-success">active</span></div>';
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
    return '<div class="text-center"><input class="checkbox multi-select i-checks" type="checkbox" name="val[]" value="' + x + '" /></div>';
}

function decode_html(value) {
    return $('<div/>').html(value).text();
}

function img_hl(x) {
    // return x == null ? '' : '<div class="text-center"><ul class="enlarge"><li><img src="'+site.base_url+'assets/uploads/thumbs/' + x + '" alt="' + x + '" style="width:30px; height:30px;" class="img-circle" /><span><a href="'+site.base_url+'assets/uploads/' + x + '" data-toggle="lightbox"><img src="'+site.base_url+'assets/uploads/' + x + '" alt="' + x + '" style="width:200px;" class="img-thumbnail" /></a></span></li></ul></div>';
    var image_link = (x == null || x == '') ? 'no_image.png' : x;
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

function user_status(x) {
    var y = x.split("__");
    return y[0] == 1 ?
        '<a href="' + site.base_url + 'auth/deactivate/' + y[1] + '" data-toggle="modal" data-target="#myModal"  data-backdrop="static"><span class="label label-success"><i class="fa fa-check"></i> ' + lang['active'] + '</span></a>' :
        '<a href="' + site.base_url + 'auth/activate/' + y[1] + '"><span class="label label-danger"><i class="fa fa-times"></i> ' + lang['inactive'] + '</span><a/>';
}

function sales_person_status(x) {
    var y = x.split("__");
    return y[0] == 1 ?
        '<span class="label label-success"><i class="fa fa-check"></i> ' + lang['active'] + '</span>' :
        '<span class="label label-danger"><i class="fa fa-times"></i> ' + lang['inactive'] + '</span>';
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
    } else if (x == 'partial' || x == 'transferring' || x == 'ordered' || x == 'confirmed') {
        return '<div class="text-center"><span class="row_status label label-info">' + lang[x] + '</span></div>';
    } else if (x == 'due' || x == 'returned' || x == 'canceled' || x == 'close') {
        return '<div class="text-center"><span class="row_status label label-danger">' + lang[x] + '</span></div>';
    } else if (x == 'delivering') {
        return '<div class="text-center"><span class="row_status label label-info">' + lang[x] + '</span></div>'
    } else if (x == 'reserved') {
        return '<div class="text-center"><span class="row_status label label-primary">' + lang[x] + '</span></div>';
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
    if (x == 'pending') {
        return '<div class="text-center"><span class="payment_status label label-warning">' + lang[x] + '</span></div>';
    } else if (x == 'rejected') {
        return '<div class="text-center"><span class="payment_status label label-danger">' + x + '</span></div>';
    } else {
        return '<div class="text-center"><span class="payment_status label label-success"> ' + lang[x] + ' </span></div>';
    }
}

function billing_status(x) {
    if (x == 'pending' || x == 'pending renewal' ) {
        return '<div class="text-center"><span class="payment_status label label-warning">' + x + '</span></div>';
    } else if (x == 'active') {
        return '<div class="text-center"><span class="payment_status label label-success">' + x + '</span></div>';
    } else if (x == 'canceled' || x == 'rejected' ) {
        return '<div class="text-center"><span class="payment_status label label-danger">' + x + '</span></div>';
    } else if (x == 'expired') {
        return '<div class="text-center"><span class="payment_status label label-default">' + x + '</span></div>';
    } else {
        return '<div class="text-center"><span class="payment_status label label-info"> ' + x + ' </span></div>';
    }
}

function deliv_status(x) {
    if (x == null) {
        return '';
    } else if (x == 'done') {
        return '<div class="text-center"><span class="deliv_status label label-success">' + lang[x] + '</span></div>';
    } else if (x == 'partial') {
        return '<div class="text-center"><span class="deliv_status label label-info">' + lang[x] + '</span></div>';
    } else if (x == 'pending') {
        return '<div class="text-center"><span class="deliv_status label label-warning">' + lang[x] + '</span></div>';
    }
}

function status_kredit_pro(x){
    var status_ = x.split('|');
    
    let return_ = '';

    if (status_[1] == 'waiting') {
        return_ = '<div class="text-center"><span class="payment_status label label-warning">'+lang['credit_reviewed']+'</span></div>';
    }else if(status_[1] == 'accept'){
        return_ = '<div class="text-center"><span class="payment_status label label-info">'+lang['credit_received']+'</span></div>';
    }else if(status_[1] == 'reject'){
        return_ = '<div class="text-center"><span class="payment_status label label-danger">'+lang['credit_declined']+'</span></div>';
    }else if(status_[1] == 'partial'){
        return_ = '<div class="text-center"><span class="payment_status label label-primary">'+lang['kredit_partial']+'</span></div>';
    }else if(status_[1] == 'paid'){
        return_ = '<div class="text-center"><span class="payment_status label label-success">'+lang['already_paid']+'</span></div>';
    }else if (status_[1] == 'pending') {
        return_ = '-';
    }
    
    return return_;
}

function status_plugin(x) {
    if (x == '' || x == null) {
        return '<div class="text-center"><span class="status_plugin label label-danger"> Off </span></div>';
    } else if (x == 'on') {
        return '<div class="text-center"><span class="deliv_status label label-success"> On </span></div>';
    } 
}

function status_user_plugin(x) {
    if (x == '' || x == null || x == 'non_aktif') {
        return '<div class="text-center"><span class="status_plugin label label-danger"> Non Aktif </span></div>';
    } else if (x == 'aktif') {
        return '<div class="text-center"><span class="deliv_status label label-success"> Aktif </span></div>';
    } 
}

function action_warehouse(url){
    arrUrl = url.split("|");
    var strAction = 
        '<div class="text-center">'+
            '<a href="' + arrUrl[1]+'" class="tip" title="edit warehouse" data-toggle="modal" data-target="#myModal"  data-backdrop="static">'+
                '<i class="fa fa-edit"></i>'+
            '</a> ';
    if (arrUrl[0]=='1') {
        strAction += 
            '<a href="#" class="tip po" title="<b> recover warehouse</b>"  data-content="<p> are you sure </p> <a class=\'btn btn-danger po-delete\' href=\'' + arrUrl[3] +'\'> Iam Sure</a> <button class=\'btn po-close\'> no</button>"  rel="popover">' +
                '<i class="fa fa-recycle "></i>'+
            '</a>';
    }else{
        strAction +=
            '<a href="#" class="tip po" title="<b> delete warehouse</b>"  data-content="<p> are you sure </p> <a class=\'btn btn-danger po-delete\' href=\'' + arrUrl[2] + '\'> Iam Sure</a> <button class=\'btn po-close\'> no</button>"  rel="popover">' +
                '<i class="fa fa-trash-o  "></i>' +
            '</a>';
    }
    strAction +='</div>';
    return strAction;
}

function pay_method(x){
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

function reload_page(){
    setTimeout(function(){ location.reload(); }, 2000);    
}

function href_page(link){
    setTimeout(function(){ window.location = link; }, 2000); 
}

function reload_table(id){
    $(id).dataTable().fnDraw();
    // $(id).DataTable().ajax.reload();
}

function notify(type, message, align, icon, from, animIn, animOut){
    $.growl({
        icon: icon, title: '', message: message, url: ''
    },{
        type: type,
        allow_dismiss: true,
        placement: {from: from, align: align},
        offset: {x: 20, y: 85},
        spacing: 10,
        z_index: 1031,
        delay: 3000,
        timer: 3000,
        mouse_over: false,
        animate: {enter: animIn, exit: animOut},
        icon_type: 'class'
    });
};

function modal(id, param) {
    $('.'+param+'_modal').modal('show'); 
    var action = $('#'+param+'_id_'+id).attr('data-action');

    $('#'+param+'_button').off('click').click(function(){ 
        $('#'+param+'_button').attr('disabled','disabled');
        $('.'+param+'_modal').modal('hide'); 
        $.ajax({
            type: "GET",
            dataType: 'json',
            url: action,
            success: function (res) {
                notify(res.notif, res.message);
                reload_page();
                $('#'+param+'_button').attr('disabled',false);
            }
        });
        return false;
    });
}

function modal_post(id, param) {
    $('.'+param+'_modal').modal('show'); 
    var action = $('#'+param+'_id_'+id).attr('data-action');

    $('#'+param+'_button').off('click').click(function(){ 
        $('#'+param+'_button').attr('disabled','disabled');
        $('.'+param+'_modal').modal('hide'); 
        let csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
        let csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';
        let mydata = {id:id, [csrfName]:csrfHash};
        $.ajax({
            type: "POST",
            dataType: 'json',
            data : mydata,
            url: action,
            success: function (res) {
                notify(res.notif, res.message);
                reload_page();
                $('#'+param+'_button').attr('disabled',false);
            }
        });
        return false;
    });
}

function delete_button(id) {
    modal_post(id,'delete');
};

function pay_button(id) {
    modal_post(id,'pay');
};

function cancel_button(id) {
    modal_post(id,'cancel');
};

function reject_button(id) {
    modal_post(id,'reject');
};

function renew_button(id) {
    $.ajax({
        type: "GET",
        dataType: 'json',
        url: site.base_url + "billing_portal/subscription/cek_pending_billing",
        success: function (res) {
            if(res.message != ''){
                notify(res.notif, res.message);
            }
            else{
                var param = 'renew';
                $('.'+param+'_modal').modal('show'); 
                var action = $('#'+param+'_id_'+id).attr('data-action');

                $('#'+param+'_button').off('click').click(function(){ 
                    $('#'+param+'_button').attr('disabled','disabled');
                    $('.'+param+'_modal').modal('hide'); 
                    window.location = action; 
                });
            }
        }
    });
};

function image_button(id) {
    let param = 'image';
    var action = $('#'+param+'_id_'+id).attr('data-action');
    $('#company_payment').html('');
    $('#ref_payment').html('');
    $('#img_payment').html('');
    $.ajax({
        type: "GET",
        dataType: 'json',
        url: action,
        data: {id:id},
        success: function (res) {
            $('.'+param+'_modal').modal('show'); 
            $('#company_payment').html(res.company_name); 
            $('#ref_payment').html('No reff : '+res.reference_no); 
            $('#img_payment').attr('src', res.image); 
        }
    });
    return false;
};

function edit_user_plugin(id) {
    $('.edit_plugin_modal').modal('show'); 
    var action = $('#edit_plugin_id_'+id).attr('data-action');

    $.ajax({
        type: "GET",
        dataType: 'json',
        url: action,
        success: function (date) {
            if(date == '0000-00-00'){
                var tgl = '';
            }else{
                var tgl = date;
            }
            $('#date_edit_plugin').val(tgl).datepicker({
                format: "yyyy-mm-dd",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true
            });
        }
    });

    $('input.datetime').off('click').click(function(){ 
        $('td').removeClass('active');
        var tgl = $('#date_edit_plugin').val();
        var res = tgl.substring(tgl.length - 2, tgl.length);
        var day = $('td.day').text();
        $("td.day").text(function(i, v) {
            if(v == dateday(res)){
                var day = $(this).addClass('active');
            }
        });
    });

    $('#edit_plugin_button').off('click').click(function(){ 
        $('#edit_plugin_button').attr('disabled','disabled');
        $('.edit_plugin_modal').modal('hide'); 
        let csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
        let csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';
        let start_date = $('#date_edit_plugin').val();
        $.ajax({
            type: "POST",
            dataType: 'json',
            data : {start_date:start_date, [csrfName]:csrfHash},
            url: action,
            success: function (res) {
                notify(res.notif, res.message);
                reload_page();
                $('#edit_plugin_button').attr('disabled',false);
            }
        });
        return false;
    });
};

$('#myForm').off('submit').on('submit', function(){
    $('#submit_button').attr('disabled','disabled');
    var action = $('#myForm').attr("data-action");
    var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
    var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';
    var myData = $('#myForm').serialize();
    $.ajax({
        type: "POST",
        data : myData + '&'+[csrfName]+'='+csrfHash,
        dataType: 'json',
        url: action,
        success: function (res) {
            notify(res.notif, res.message);
            if(res.to_link == ''){
                reload_page();
            }else{
                href_page(res.to_link);
            }
        }
    });
    return false;
});

