$(window).on("scroll", function() {
    if ($(window).scrollTop() > 10) {
        $(".navbar").addClass("w-shadow");
    } else {
        //remove the background property so it comes transparent again (defined in your css)
        $(".navbar").removeClass("w-shadow");
    }
});

// mobile menu slide from the left
$('.navbar-slide').hide();

// mobile menu slide from the left
$('.navbar-toggler').click(function() {
    $('.navbar-slide').toggle("slide");
});

$('.navbar-slide-close').click(function() {
    $('.navbar-slide').toggle("slide");
});

//cart dropdown
$('#cart-dropdown').hide();
$('#navShoppingCart').click(function(e) {
    $('#cart-dropdown').slideToggle();
    e.preventDefault();
    $('#dropdown-notif').slideUp();
});
// $(document).mouseup(function(e) 
// {
//     let container = $("#cart-dropdown") ;
//     let tombol = $("#navShoppingCart");
//     // kondisi gawe target tombol & cont notif
//     if (!container.is(e.target) && !tombol.is(e.target) && container.has(e.target).length === 0) 
//     {
//         container.slideUp();
//     }
// });

// Notif
$('#dropdown-notif').hide();
$('#notification').click(function(e){
    $('#dropdown-notif').slideToggle();
    e.preventDefault();
    $('#cart-dropdown').slideUp();
});

// $(document).mouseup(function(e) 
// {
//     let container = $("#dropdown-notif") ;
//     let tombol = $("#notification");
//     // kondisi gawe target tombol & cont notif
//     if (!container.is(e.target) && !tombol.is(e.target) && container.has(e.target).length === 0) 
//     {
//         container.slideUp();
//     }
// });



// / Viewport Checker Animation
// Trigger animation on scroll based on viewport
// Has dependency on Viewport Checker JS
$(document).ready(function() {
    $('.vp-fadeinleft').viewportChecker({
        classToAdd: 'visible animated fadeInLeft',
        offset: 100
    });
    $('.vp-fadeinright').viewportChecker({
        classToAdd: 'visible animated fadeInRight',
        offset: 100
    });
    $('.vp-fadein').viewportChecker({
        classToAdd: 'visible animated fadeIn',
        offset: 100
    });
    $('.vp-fadeindown').viewportChecker({
        classToAdd: 'visible animated fadeInDown',
        offset: 100
    });
    $('.vp-fadeinup').viewportChecker({
        classToAdd: 'visible animated fadeInUp',
        offset: 100
    });
    $('.vp-slideinleft').viewportChecker({
        classToAdd: 'visible animated slideInLeft',
        offset: 100
    });
    $('.vp-slideinright').viewportChecker({
        classToAdd: 'visible animated slideInRight',
        offset: 100
    });
    $('.vp-zoomin').viewportChecker({
        classToAdd: 'visible animated zoomIn',
        offset: 100
    });
    $('.vp-zoomindown').viewportChecker({
        classToAdd: 'visible animated zoomInDown',
        offset: 100
    });
    $('.vp-rotatein').viewportChecker({
        classToAdd: 'visible animated rotateIn',
        offset: 100
    });
    $('.vp-slideindown').viewportChecker({
        classToAdd: 'visible animated slideInDown',
        offset: 100
    });
    $('.parallax-window').parallax({ parallax: 'scroll' });

    $(".form-control-datepicker").datepicker({
        dateFormat: 'dd/mm/yy',
        minDate: (new Date()).getHours() >= 17 ? 1 : 0
    });
});



function addSeparatorsNF(nStr, inD, outD, sep) {
    nStr += '';
    var dpos = nStr.indexOf(inD);
    var nStrEnd = '';
    if (dpos != -1) {
        nStrEnd = outD + nStr.substring(dpos + 1, nStr.length);
        nStr = nStr.substring(0, dpos);
    }
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(nStr)) {
        nStr = nStr.replace(rgx, '$1' + sep + '$2');
    }
    return nStr + nStrEnd;
}

function isInt(n) {
    return n % 1 === 0;
}
