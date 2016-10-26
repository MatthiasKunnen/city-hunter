var map;
var preyMarker;
var myMarker;
var center = new google.maps.LatLng(51.053468000000000000, 3.730379999999968200);
var position;
var tmrRefreshRate;
var positiontracker;
var markerLatLngList;

function getLatLngFromPos(position) {
    return new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
}

function fitAllMarkers() {
    var latlngbounds = new google.maps.LatLngBounds();
    $.each(markerLatLngList, function (index, value) {
        latlngbounds.extend(value);
    });
    map.setCenter(latlngbounds.getCenter());
    map.fitBounds(latlngbounds);
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
    }
    return "";
}

function error(error) {
    alert("We were unable to find your device. " + error);
}

// Add a marker to the map and push to the array.
function adjustMyMarker(location) {
    myMarker.position = location;
    map.zoom = 15;
}

function updatePreyMarker(latitude, longitude) {
    preyMarker = createMarker(map, new google.maps.LatLng(latitude, longitude));
    markerLatLngList.push(myMarker.position);
    markerLatLngList.push(preyMarker.position);
    fitAllMarkers();
}

function startTimer(duration) {
    var timer = duration, minutes, seconds;
    clearInterval(tmrRefreshRate);
    tmrRefreshRate = setInterval(function () {
        minutes = parseInt(timer / 60, 10);
        seconds = parseInt(timer % 60, 10);

        minutes = minutes < 10 ? "0" + minutes : minutes;
        seconds = seconds < 10 ? "0" + seconds : seconds;

        $("#time").text(minutes + ":" + seconds);

        if (--timer < 0) {
            clearInterval(tmrRefreshRate);
            myMarker = createMarker(map, myMarker.position, "green-dot");
            timer = 30;
            request(function () {
                updateCountdown();
            });
        }

    }, 1000);
}

function updateCountdown() {
    $.ajax({
        url: "feeder.php?mode=refresh",
        success: function (result) {
            var jResult = JSON.parse(result); //todo implement game speed changes
            startTimer(jResult.countdown);
        }
    });
}

function request(onFinish) {
    $.ajax({
        url: "feeder.php?mode=request",
        success: function (result) {
            var jResult = JSON.parse(result);
            updatePreyMarker(jResult.latitude, jResult.longitude);
            if (onFinish) {
                onFinish();
            }
        }
    });
}

function update(pos) {
    $.ajax({
        url: "feeder.php?mode=update&latitude=" + pos.coords.latitude + "&longitude=" + pos.coords.longitude + "&accuracy=" + pos.coords.accuracy + "&timestamp=" + pos.timestamp
    });
}

function initialize() {

    markerLatLngList = new Array();
    var mapOptions = {
        zoom: 14,
        center: center,
        mapTypeId: google.maps.MapTypeId.TERRAIN
    };
    map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
    myMarker = createMarker(map, center, "green-dot");
    if (!navigator.geolocation) { //browser ondersteunt geen geolocation
        alert('HTML5 Geolocation is not supported in your browser.');
        return;
    }

    positiontracker = navigator.geolocation.watchPosition(function (pos) {
        if (position == null || position.coords.latitude != pos.coords.latitude || position.coords.longitude != pos.coords.longitude) {
            adjustMyMarker(getLatLngFromPos(pos));
            update(pos);
            position = pos;
        }
    }, error, {
        enableHighAccuracy: true, //idk, maybe true
        timeout: 5000,
        maximumAge: 0
    });
    updateCountdown();
    setInterval(function () { //interval to update the refresh rate
        updateCountdown();
    }, 5000);
    request();
    var targetPos = JSON.parse(getCookie("prey_position"));
    if (targetPos.latitude !== null && targetPos.longitude !== null) {
        updatePreyMarker(targetPos.latitude, targetPos.longitude);
        fitAllMarkers();
    }
}

window.onload = function () {
    fitmap();
    $(window).resize(function () {
        fitmap()
    });
    initialize();
};

function fitmap() {
    $("#map-canvas").css("height", window.innerHeight - $("#overMap").height() - 4 + "px");
}

function createMarker(map, LatLng, icon) {
    return new google.maps.Marker({
        position: LatLng,
        map: map,
        icon: "http://maps.google.com/mapfiles/ms/icons/" + (icon ? icon : "red-dot") + ".png"
    });
}

/*
 function getPreyLocation() {
 var request = new XMLHttpRequest();
 var lat;
 var lng;

 if (this.request === null) {
 alert("Unable to create request");
 return;
 }

 var url = "feeder.php?mode=request&latitude=" + myMarker.get;
 request.open("GET", url, true);//default true
 request.onreadystatechange = function () {
 if (this.readyState === 4 && this.status === 200) {
 var json = this.responseText,
 obj = JSON.parse(json);

 //lat = ??;
 //lng = ??;


 }

 };
 this.request.send();


 return new google.maps.LatLng(lat, lng);
 }*/