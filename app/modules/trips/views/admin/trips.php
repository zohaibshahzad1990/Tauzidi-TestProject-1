<div class="kt-portlet" id="portlet_body">
    <div class="kt-portlet__head">
        <div class="kt-portlet__head-label">
            <h3 class="kt-portlet__head-title">
                {metronic:template:title}
            </h3>
        </div>
    </div>
    <div class="kt-portlet__body">
    	
    	<div class="kt-portlet__body kt-portlet__body--fit">
			<div class="row row-no-padding row-col-separator-xl">
				<div class="col-md-12 col-lg-12">
					<div class="z-depth-1" style="height: 400px">
						<div id="map-wrapper">
	                		<div id="map-canvas"></div>
	            		</div>
					</div>
				</div>
			</div>
		</div>
		<style type="text/css">
			html, body, #map-wrapper, #map-canvas {
		    margin: 0;
		    padding: 0;
		    height: 100%;
		    width: 100%;
		}
		</style>
		 <div id="directions_panel" style="margin:20px;background-color:#FFEE77;"></div>

    </div>
</div>

<input type="hidden" name="start_point" id="start_point" value="<?php echo $route->start_point; ?>" />
<input type="hidden" name="start_longitude" id="start_longitude" value="<?php echo $route->start_longitude; ?>"/>
<input type="hidden" name="start_latitude"  id="start_latitude" value="<?php echo $route->start_latitude; ?>"/>
<input type="hidden" name="destination_longitude" id="destination_longitude" value="<?php echo $route->destination_longitude; ?>"/>
<input type="hidden" name="destination_latitude" id="destination_latitude" value="<?php echo $route->destination_latitude; ?>"/>
<input type="hidden" name="end_point" id="end_point" value="<?php echo $route->end_point; ?>" />
<input type="hidden" name="distance" id="distance" />
<input type="hidden" name="duration" id="duration" />
<input type="hidden" name="id" id="id" />

<script type="text/javascript">
	var base_url = window.origin;
	$(document).ready(function(){
		var markers = <?php echo json_encode($journey_cordinates)?>;
		var directionsDisplay;
		var directionsService = new google.maps.DirectionsService();
		var map;
		var start_lat = "<?php echo $route->start_latitude; ?>";
		var start_long = "<?php echo $route->start_longitude; ?>";
		window.onload = function() {
			var mapOptions = {
			    center: new google.maps.LatLng(start_lat, start_long),
			    zoom: 10,
			    mapTypeId: google.maps.MapTypeId.ROADMAP
			};


			directionsDisplay = new google.maps.DirectionsRenderer();
		  	var chicago = new google.maps.LatLng(41.850033, -87.6500523);
		  	/*var mapOptions = {
		    	zoom: 6,
		    	center: chicago
		  	}*/
		 	map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
		  	directionsDisplay.setMap(map);
		  	calcRoute();
		}

		function calcRoute() {
		    var start = document.getElementById('start_point').value;
		    var start_longitude = document.getElementById('start_longitude').value;
		    var start_latitude = document.getElementById('start_latitude').value;
			var end = document.getElementById('end_point').value;
			var end_longitude = document.getElementById('destination_longitude').value;
		    var end_latitiude = document.getElementById('destination_latitude').value;
			var checkboxArray = markers;
		    var latLng = new google.maps.LatLng(parseFloat(start_latitude), parseFloat(start_longitude));
		    var startMarker = new google.maps.Marker({
		      position: latLng,
		      map: map
		    });

		    latLng = new google.maps.LatLng(parseFloat(end_latitiude), parseFloat(end_longitude));
		    var endMarker = new google.maps.Marker({
		      position: latLng,
		      map: map
		    });
		    var waypts = [];
		    
		    for (var i = 0; i < checkboxArray.length; i++) {
		      var latLng = new google.maps.LatLng(parseFloat(checkboxArray[i].latitude), parseFloat(checkboxArray[i].longitude));
		      var marker = new google.maps.Marker({
		        position: latLng,
		        map: map
		      });
		      waypts.push({
		        location: latLng,
		        stopover: true
		      });

		    };
		    var request = {
		      origin: start,
		      destination: end,
		      waypoints: waypts,
		      optimizeWaypoints: true,
		      travelMode: google.maps.TravelMode.DRIVING
		    };

		    directionsService.route(request, function(response, status) {
		      if (status == google.maps.DirectionsStatus.OK) {
		        directionsDisplay.setDirections(response);
		      }
		    })
		}

		function calcRoute12() {
			  var start = document.getElementById('start_point').value;
			  var end = document.getElementById('end_point').value;
			  console.log(start, end);
			  var waypts = [];
			  var checkboxArray = markers; //document.getElementById('waypoints');
			  console.log(checkboxArray)
			  for (var i = 0; i < checkboxArray.length; i++) {
			  		var latLng = new google.maps.LatLng(parseFloat(checkboxArray[i].latitude), parseFloat(checkboxArray[i].longitude));
			  		var marker = new google.maps.Marker({
		                position: latLng,
		                map: map
		            });
			        waypts.push({
			        	location: latLng,
				        stopover:true
				    });
			  }
			  console.log(waypts)
			  var request = {
			      origin: start,
			      destination: end,
			      waypoints: waypts,
			      optimizeWaypoints: true,
			      travelMode: google.maps.TravelMode.DRIVING
			  };

			  directionsService.route(request, function(response, status) {
			    if (status == google.maps.DirectionsStatus.OK) {
			      directionsDisplay.setDirections(response);
			      var route = response.routes[0];
			      var summaryPanel = document.getElementById('directions_panel');
			      summaryPanel.innerHTML = '';
			      // For each route, display summary information.
			      for (var i = 0; i < route.legs.length; i++) {
			        var routeSegment = i + 1;
			        summaryPanel.innerHTML += '<b>Route Segment: ' + routeSegment + '</b><br>';
			        summaryPanel.innerHTML += route.legs[i].start_address + ' to ';
			        summaryPanel.innerHTML += route.legs[i].end_address + '<br>';
			        summaryPanel.innerHTML += route.legs[i].distance.text + '<br><br>';
			      }
			    }
			  });
		}

	});




</script>