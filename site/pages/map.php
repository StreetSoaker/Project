<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="user-scalable=no,target-densitydpi=high-dpi" />
	<style type="text/css">
		html { 
			height: 100%;
			width: 100%; 
		}

		body { 
			height: 100%;
			width: 100%; 
			margin: 0; 
			padding: 0;
		}

		#map-canvas { 
			height: 100%;
			width: 100%;
		}
	</style>
	<script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAEsqx3E9ITguWtbjptJCm6QkIs4fra4Fo&sensor=false"></script>
	
</head>
<body>
	<div id="status"></div>
	<div id="map-canvas"></div>
	<script type="text/javascript">
		google.maps.visualRefresh = true;
		var map;
		var marker;
		var googlemaps;

		function getLocation() {
			var geoOptions = {
				'enableHighAccuracy': true,
				maximumAgex: 0,
			};

			function geoSuccess(position) {
				var location = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);

				$.post("pages/locations.php", { 
					'latitude': position.coords.latitude, 
					'longitude': position.coords.longitude, 
					'accuracy': position.coords.accuracy,
					'timestamp': position.timestamp,
				}, function(data) {
					var n;
					var json = $.parseJSON(data);
					console.log(json);
        			for(n=0; n<data.length; n++) {
            			var latitude = json[n].latitude;
            			var longitude = json[n].longitude;
            			var username = json[n].username;
          
            			var location = new google.maps.LatLng(latitude,longitude);
            			var marker = new google.maps.Marker({
                			position: location,
                			map: map,
                			title: username,
                			zIndex: 1,
          	  			});

        			}   
				});

				if (!googlemaps) {
					console.log('MAPS INIT');
					var mapOptions = {
						center: location,
						zoom: 16,
						maxZoom: 16,
						disableDefaultUI: true,
						mapTypeId: google.maps.MapTypeId.ROADMAP
					};

					map = new google.maps.Map(document.getElementById("map-canvas"),mapOptions);

					googlemaps = true;
				}

				$('#status').text('ACC: '+position.coords.accuracy+' LAT: '+position.coords.latitude+' LON: '+position.coords.longitude);
			}

			function geoError(error) {
				console.warn('ERROR(' + error.code + '): ' + error.message);
			}

			navigator.geolocation.getCurrentPosition(geoSuccess, geoError, geoOptions);
		}

		getLocation();

		$(document).ready(function($) {
			setInterval(function() {
				getLocation();
			}, 10000);
			

		});
	</script>
</body>
</html>
