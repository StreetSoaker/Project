//Function to get accurate position with minimum accuracy
navigator.geolocation.getAccurateCurrentPosition = function (geolocationSuccess, geolocationError, geoprogress, options) {
    var lastCheckedPosition;
    var locationEventCount = 0;
    
    if (geoprogress && geoprogress.constructor.name !== 'Function' && options === undefined){
        options = geoprogress;
        geoprogress = function(){};
    };

    options = options || {};
    options.context = options.context || this;

    var checkLocation = function(position) {
        lastCheckedPosition = position;
        ++locationEventCount;
        // We ignore the first event unless it's the only one received because some devices seem to send a cached
        // location even when maxaimumAge is set to zero
        if ((position.coords.accuracy <= options.desiredAccuracy) && (locationEventCount > 0)) {
            clearTimeout(timerID);
            navigator.geolocation.clearWatch(watchID);
            foundPosition(position);
        } else {
            geoprogress.call(options.context, position);
        }
    }

    var stopTrying = function() {
        navigator.geolocation.clearWatch(watchID);
        foundPosition(lastCheckedPosition);
    }

    var onError = function(error) {
        clearTimeout(timerID);
        navigator.geolocation.clearWatch(watchID);
        geolocationError.call(options.context, error);
    }

    var foundPosition = function(position) {
        geolocationSuccess.call(options.context, position);
    }

    if (!options.maxWait)            options.maxWait = 10000; // Default 10 seconds
    if (!options.desiredAccuracy)    options.desiredAccuracy = 20; // Default 20 meters
    if (!options.timeout)            options.timeout = options.maxWait; // Default to maxWait

    options.maximumAge = 0; // Force current locations only
    options.enableHighAccuracy = true; // Force high accuracy (otherwise, why are you using this function?)

    var watchID = navigator.geolocation.watchPosition(checkLocation, onError, options);
    var timerID = setTimeout(stopTrying, options.maxWait); // Set a timeout that will abandon the location loop
}

//Get location and send location to the database
function getPlayerLocation() {
    //Location options
    var geoOptions = {
        enableHighAccuracy: true,

        //TEMPORARY UNTIL LAUNCH
        desiredAccuracy: 100,
        maxWait: 1000,
    };

    //Called when location is returned successfully
    function geoSuccess(position) {
        socket.emit('location', {location : position});
    }

    //Called when an error has occured when trying to retrieve the location
    function geoError(error) {
        console.warn('ERROR(' + error.code + '): ' + error.message);
        socket.emit('locationError', {error : 'ERROR(' + error.code + '): ' + error.message});
    }

    //Get current location
    navigator.geolocation.getAccurateCurrentPosition(geoSuccess, geoError, geoOptions);
}

//Update the players marker location.
function updatePlayerMarker() {
    
    //Location options
    var geoOptions = {
        enableHighAccuracy: true,

        //TEMPORARY UNTIL LAUNCH
        desiredAccuracy: 1000000,
        maxWait: 10000,
    };

    //Called when location is returned successfully
    function geoSuccess(position) {
        var location = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);

        if (!playerMarker) {
            playerMarker = new google.maps.Marker({
                position: location,
                map: map,
                icon:'http://82.196.0.4/site/img/ingamepage/icon_yourplace.png',
            });

            map.setCenter(location);
        } else {
            playerMarker.setPosition(location);
        }
    }

    //Called when an error has occured when trying to retrieve the location
    function geoError(error) {
        console.warn('ERROR(' + error.code + '): ' + error.message);
    }

    //Get current location
    navigator.geolocation.getAccurateCurrentPosition(geoSuccess, geoError, geoOptions);
}

//Update the enemy marker location
function updateEnemyMarkers(data) {
    console.log(data);
    console.log(data[0]);

    for (x=0; x<markers.length; x++) {
        markers[x].setMap(null);
    }

    //Create markers for every user.
    for(i=0; i<data.length; i++) {
        var latitude = data[i].latitude;
        var longitude = data[i].longitude;
        var username = data[i].username;
  
        var location = new google.maps.LatLng(latitude,longitude);
        var marker = new MarkerWithLabel({
            position: location,
            map: map,
            labelContent: username,
            title: username,
            icon: 'http://82.196.0.4/site/img/ingamepage/icon_enemy.png',
            labelClass: 'maplabel',
            labelAnchor: new google.maps.Point(21, 0),
        });

        markers.push(marker);
    }
}

function killEnemy() {
    event.preventDefault();
    var enemyid           = $('input[name="enemyid"]').val();
    var killcode        = $('input[name="killcode"]').val();

    socket.emit('kill', {enemyid: enemyid, killcode: killcode});
}

google.maps.visualRefresh = true;

var map;
var markers = [];
var playerMarker;
var enemyMarker;
var userdata;


$.getJSON('pages/userdata.php', function(data) {
    userdata = data;
});


var socket = io.connect('http://82.196.0.4:8080');

socket.on('info', function(data) {
    $('#status').toggle();
    $('#status').text(data.message);
    setTimeout((function() {
        $('#status').toggle();
    }), 3000);
});

socket.on('connect', function() {
    setTimeout((function() {
        socket.emit('connect', userdata);
    }), 1500);
    $('#status').toggle();
    $('#status').text('Connected');
    setTimeout((function() {
        $('#status').toggle();
    }), 3000);
});

socket.on('updatePlayerLocation', function() {
    getPlayerLocation();
});

socket.on('updateEnemyMarkers', function(data) {
    updateEnemyMarkers(data);
});

socket.on('usersOnline', function(data) {
    $('#online').text(data);
});

socket.on('getDeathCode', function(data) {
    if (data == userdata.id) {
        socket.emit('getDeathCode', userdata);
    }
});

socket.on('updateDeathCode', function(data) {
    $('#deathcode').text(data);
    $('#playerid').text(userdata.userid);
});

socket.on('getStats', function(data){
    if (data == userdata.id) {
        socket.emit('getStats');
    }
}); 

socket.on('stats', function(data) {
    $('#kills').text(data.kills);
    $('#deaths').text(data.deaths);
    $('#kdratio').text(Math.round(data.kills / data.deaths * 1000) / 1000);
});

socket.on('invalidKillCode', function() {
    $('#invalidkillcode').toggle();
    $('#invalidkillcode').text('Invalid Kill code!');
    setTimeout((function() {
        $('#invalidkillode').toggle();
    }), 3000);
});

$(document).ready(function($) {

    var mapOptions = {
        zoom: 15,
        maxZoom: 16,
        disableDefaultUI: true,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };

    map = new google.maps.Map(document.getElementById('map'), mapOptions);
    
    updatePlayerMarker();
});