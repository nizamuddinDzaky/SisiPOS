var ajaxku = buatajax();

function setProvinsi(id, text) {
    $('#modal-loading').show();
    var urlProvinsi = base_url + "/daerah/getKabupaten/" + text.replace(/\s+/g, '_') + "/";
    var output = "";
    output += '<option value="" data-foo="">Choose City</option>';
    $("#kabupaten").html(output);
    $('select[name=kabupaten]').val('').change();
    $.getJSON(urlProvinsi, function (data) {
        $.each(data, function (key, val) {
            output += '<option value="' + val.kabupaten_name + '" data-foo="">' + val.kabupaten_name + '</option>';
        });

        $("#kabupaten").html(output);
        if (localStorage.getItem('tour-sign_up') != null) {
            $("#kabupaten").change(function () {
                hopscotch.nextStep()
                $("#kecamatan").change(function () {
                    hopscotch.nextStep()
                });
            });
        }
        $('#modal-loading').hide();
    });
}

function setKabupaten(id, text) {
    $('#modal-loading').show();
    var urlProvinsi = base_url + "/daerah/getKecamatan/" + text.replace(/\s+/g, '_') + "/";
    var output = "";
    output += '<option value="" data-foo="">Choose District</option>';
    $("#kecamatan").html(output);
    $('select[name=kecamatan]').val('').change();
    $.getJSON(urlProvinsi, function (data) {

        $.each(data, function (key, val) {
            output += '<option value="' + val.kecamatan_name + '" data-foo="">' + val.kecamatan_name + '</option>';
        });
        $("#kecamatan").html(output);
        $('#modal-loading').hide();

    });
}

function setKecamatan(id, text) {
    // $('#modal-loading').show();
    var kabupaten = $('#kabupaten').val();
    var provinsi = $('#provinsi').val();
    var urlProvinsi = base_url + "/daerah/getlocation/" + provinsi.replace(/\s+/g, '_') + "/" + kabupaten.replace(/\s+/g, '-') + "/" + text.replace(/\s+/g, '-') + "/";


    //     $.getJSON(urlProvinsi, function(data) {
    //        var jsonResult;
    //        try {
    //            jsonResult = JSON.parse(data);
    //            if (jsonResult != FALSE){
    //                $('#latitude').val('0');
    //                $('#longitude').val('0');
    //            }
    //        }
    //        catch (e) {
    //             $('#latitude').val('0');
    //              $('#longitude').val('0');
    //        };
    //        
    ////        $('#modal-loading').hide();
    //      });



}

function ajaxkota(id, text) {
    // var url="daerah/getKab/"+id+"/"+Math.random();
    var url = base_url + "/daerah/getKab/" + id + "/" + Math.random();
    document.getElementById("prop_hidden").value = text;
    ajaxku.onreadystatechange = stateChanged;
    ajaxku.open("GET", url, true);
    ajaxku.send(null);
}

function ajaxkec(id, text) {
    var url = base_url + "/daerah/getKec/" + id + "/" + Math.random();
    document.getElementById("kota_hidden").value = text;
    ajaxku.onreadystatechange = stateChangedKec;
    ajaxku.open("GET", url, true);
    ajaxku.send(null);
}

function ajaxkel(id, text) {
    var url = base_url + "/daerah/getKel/" + id + "/" + Math.random();
    document.getElementById("kec_hidden").value = text;
    ajaxku.onreadystatechange = stateChangedKel;
    ajaxku.open("GET", url, true);
    ajaxku.send(null);
}

function buatajax() {
    if (window.XMLHttpRequest) {
        return new XMLHttpRequest();
    }
    if (window.ActiveXObject) {
        return new ActiveXObject("Microsoft.XMLHTTP");
    }
    return null;
}

function stateChanged() {
    var data;
    if (ajaxku.readyState == 4) {
        data = ajaxku.responseText;
        if (data.length >= 0) {
            document.getElementById("kota").innerHTML = data
        } else {
            document.getElementById("kota").value = "<option selected>Choose City</option>";
        }
        // document.getElementById("kab_box").style.display='table-row';
        // document.getElementById("kec_box").style.display='none';
        // document.getElementById("kel_box").style.display='none';
        // document.getElementById("lat_box").style.display='none';
        // document.getElementById("lng_box").style.display='none';
    }
}

function stateChangedKec() {
    var data;
    if (ajaxku.readyState == 4) {
        data = ajaxku.responseText;
        if (data.length >= 0) {
            document.getElementById("kec").innerHTML = data
        } else {
            document.getElementById("kec").value = "<option selected>Pilih Kecamatan</option>";
        }
        // document.getElementById("kec_box").style.display='table-row';
        // document.getElementById("kel_box").style.display='none';
        // document.getElementById("lat_box").style.display='none';
        // document.getElementById("lng_box").style.display='none';
    }
}

function stateChangedKel() {
    var data;
    if (ajaxku.readyState == 4) {
        data = ajaxku.responseText;
        if (data.length >= 0) {
            document.getElementById("kel").innerHTML = data
        } else {
            document.getElementById("kel").value = "<option selected>Pilih Kelurahan/Desa</option>";
        }
        document.getElementById("kel_box").style.display = 'table-row';
        document.getElementById("lat_box").style.display = 'none';
        document.getElementById("lng_box").style.display = 'none';
    }
}

//var map;
//var geocoder;
//var marker;
//var markersArray = [];
//function initialize() {
//  geocoder = new google.maps.Geocoder();
//  var myLatlng =new google.maps.LatLng(-6.176655999999999, 106.83058389999997);
//  var mapOptions = {
//    center: myLatlng,
//    zoom: 14
//  };
//  map = new google.maps.Map(document.getElementById('map-canvas'),mapOptions);
//  marker = new google.maps.Marker({
//      position: myLatlng,
//      map: map,
//      title: 'Jakarta'
//  });  
//  markersArray.push(marker);
//  google.maps.event.addListener(marker,"click",function(){});  
//}
//
//function clearOverlays() {
//  for (var i = 0; i < markersArray.length; i++ ) {
//    markersArray[i].setMap(null);
//  }
//  markersArray.length = 0;
//}
//
//function showCoordinate(){
//  var prop = document.getElementById("prop");
//  var kab = document.getElementById("kota");
//  var kec = document.getElementById("kec");
//  var kel = document.getElementById("kel");
//  var s = kel.options[kel.selectedIndex].text
//          +', '
//          +kec.options[kec.selectedIndex].text;
//      s2= s
//          +', '
//          +kab.options[kab.selectedIndex].text
//          +', '
//          +prop.options[prop.selectedIndex].text;   
//  geocoder.geocode( { 'address': s}, function(results, status) {
//  document.getElementById("lat_box").style.display='table-row';
//  document.getElementById("lng_box").style.display='table-row';
//    if (status == google.maps.GeocoderStatus.OK) {
//      clearOverlays();
//      var position=results[0].geometry.location;
//      document.getElementById("lat").value=position.lat();
//      document.getElementById("lng").value=position.lng();
//      map.setCenter(results[0].geometry.location);
//      marker = new google.maps.Marker({
//          map: map,
//          position: results[0].geometry.location,
//          title:s2
//      });
//      markersArray.push(marker);
//      google.maps.event.addListener(marker,"click",function(){});
//    } else {
//      alert('Geocode was not successful for the following reason: ' + status);
//    }
//  });
//}
//google.maps.event.addDomListener(window, 'load', initialize);