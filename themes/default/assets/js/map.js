/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var sign;
var placeSearch, place;
var cust_group;


function getDataCompany(id){
    var detail;
    $.ajax({
        type: "get", async: false,
        url: site.base_url+"customers/get_customer_details/" + id,
        dataType: "json",
        success: function (data) {
            detail=data;
            if(data.group_name=='customer'){
                cust_group=data.customer_group_name;
            }
        }
    });
    return detail;
}

/*          G E T   L A T - L N G   C U S T O M E R   B Y   A D D R E S S           */
function getCoordinates(city,street,province){
    var coordinate;
    jl=encodeURIComponent(city+','+street+','+province);
    $.ajax({
        type:'get', async:false,
        url: 'https://maps.googleapis.com/maps/api/geocode/json?address='+jl+'&sensor=true&region=Indonesia&key='+site.key_maps,
        dataType: "json",
        success: function(data){
            try{
//                if(data.results[0]){
                    coordinate=data.results[0].geometry.location;
//                }else{
//                    coordinate = FALSE;
//                }
            }catch (exception) {
                bootbox.alert('Alamat Tidak Bisa Diproses');
            }
        }
    });
    return coordinate;
}

function getDistance(lat_source,lng_source,lat_distance,lng_distance){
//    var dist;
    $.ajax({
        type:'get', 
//        async:false,
        url: site.base_url+"sales/getCharges",
        data: {
            sourceLat: lat_source,
            sourceLng: lng_source,
            destinationLat: lat_distance,
            destinationLng: lng_distance
        },
        dataType: "json",
        beforeSend: function(){
            $('#modal-loading').show();
        },
        success: function(data){
            if(cust_group=='Member'){
                dist=(data?data.cost_member:0);
            }else{
                dist=(data?data.cost:0);
            }
            
            if((typeof dist == 'string') && (dist.indexOf('Undefined') != -1)){
                bootbox.alert(dist);
            }

            $('#shipping').val(formatDecimal(dist));
            $('#modal-loading').hide();
        }
    });
//    return dist;
}

///////////////////////////////////////    A D D R E S S   M A P S    ///////////////////////////////////////
function initMap() {
    var fieldsuggestion=document.getElementById('address') ? document.getElementById('address') : document.getElementById('destination');
    bounds = new google.maps.LatLngBounds();
    var opts = {
        center: new google.maps.LatLng(-6.175392,106.827153),
        zoom: 8,
        streetViewControl: false,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
    };

    map = new google.maps.Map(document.getElementById('map-canvas'),opts);
    geocoder = new google.maps.Geocoder();
    
    google.maps.event.addListener(map, 'click', function(event) {
//        dropMarker(event.latLng.lat(), event.latLng.lng());
        geocoder.geocode({
            'latLng':event.latLng
        }, function(results, status){
            if (status == google.maps.GeocoderStatus.OK) {
                if (results[0]) {
                    fieldsuggestion.value=(results[0].formatted_address);
                    setFieldLatLng(event.latLng.lat(), event.latLng.lng());
                    dropMarker(event.latLng.lat(), event.latLng.lng());
                    if(document.getElementById('destination')){
                        getDistance(position_user.lat, position_user.lng, event.latLng.lat(), event.latLng.lng());
                    }
                }
            }
        });
    });
    
//    Autocomplete
    autocomplete = new google.maps.places.Autocomplete((fieldsuggestion));
    autocomplete.bindTo('bounds', map);
    places = new google.maps.places.PlacesService(map);
    autocomplete.addListener('place_changed', fillInAddress);
}

/*               S T A R T   C A L C U L A T E   B Y   A D D R E S S   [I N P U T   D I R E C T]            */
function placeMarker(location) {
    var marker = new google.maps.Marker({
        position: location, 
        map: map
    });
}

function calculateDistances(address, city, destinate=null) {
    origin = address+', '+city;
    destination = document.getElementById('destination').value;
    var service = new google.maps.DistanceMatrixService();
    service.getDistanceMatrix(
    {
        origins: [origin],
        destinations: [destination],
        travelMode: google.maps.TravelMode.DRIVING,
        unitSystem: google.maps.UnitSystem.METRIC
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
/*           E N D   C A L C U L A T E   B Y   A D D R E S S            */

function getShippingCost(distance){
    var dist;
    $.ajax({
        type:'get', async:false,
        url: site.base_url+"sales/getShippingCostMap",
        data: {
            distance: distance
        },
        dataType: "json",
        success: function(data){
            if(cust_group=='Member'){
                dist=(data?data.cost_member:0);
            }else{
                dist=(data?data.cost:0);
            }
            $('#shipping').val(formatDecimal(dist));
        }
    });
}

/**                 S U G G E S T I O N S   M A P S                   */
function fillInAddress() {
    place = autocomplete.getPlace();
    var lati, longi;

    if (place.geometry) {
        if(document.getElementById('address')){
            document.getElementById('address').value=place.formatted_address;
        }
        lati=place.geometry.location.lat();
        longi=place.geometry.location.lng();
        
        map.panTo(place.geometry.location);
        map.setZoom(15);
        
        setFieldLatLng(lati,longi);
        
        if(document.getElementById('destination')){
            getDistance(position_user.lat, position_user.lng, lati, longi);
        }
        
        dropMarker(lati,longi);
    } else {
        bootbox.alert("Not Allowed");
    }
}

function dropMarker(lat, lng){
    if(sign)
        sign.setMap(null);
    
    sign = new google.maps.Marker({
        position: new google.maps.LatLng(lat, lng),
        animation: google.maps.Animation.DROP
    });
    sign.setMap(map);
}

function setFieldLatLng(lat, lng){
    if(document.getElementById('latitude') && document.getElementById('longitude')){
        document.getElementById('latitude').value=lat;
        document.getElementById('longitude').value=lng;
    }
}

function geolocate() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            var geolocation = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };
            
            var circle = new google.maps.Circle({
                center: geolocation,
                radius: position.coords.accuracy
            });
            autocomplete.setBounds(circle.getBounds());
        });
    }
}

function moveMap(address,coordinate){
    $.getJSON(site.base_url+"daerah/getLocation/"+address.country+"/"+address.city+"/"+address.state, function(data) {
        if(!data){
            data=[{
                lat:-6.175392,
                lng:106.827153
            }]
        }
        map.setCenter({lat:parseFloat(data[0].lat),lng:parseFloat(data[0].lng)});
        map.setZoom(14);
        if(coordinate.latitude && coordinate.longitude){
            dropMarker(coordinate.latitude, coordinate.longitude);
        }
    });
}
