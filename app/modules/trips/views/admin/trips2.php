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

		
		window.onload = function() {
		  var mapOptions = {
		    center: new google.maps.LatLng(markers[0].latitude, markers[0].longitude),
		    zoom: 10,
		    mapTypeId: google.maps.MapTypeId.ROADMAP
		  };
		  var map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);
		  var infoWindow = new google.maps.InfoWindow();
		  var lat_lng = new Array();
		  var latlngbounds = new google.maps.LatLngBounds();
		  for (i = 0; i < markers.length; i++) {
		    var data = markers[i]
		    var myLatlng = new google.maps.LatLng(data.latitude, data.longitude);
		    lat_lng.push(myLatlng);
		    var marker = new google.maps.Marker({
		      position: myLatlng,
		      map: map,
		      title: data.timestamp
		    });
		    // console.log(i)

		    latlngbounds.extend(marker.position);
		    (function(marker, data) {
		      google.maps.event.addListener(marker, "click", function(e) {
		        infoWindow.setContent(data.timestamp);
		        infoWindow.open(map, marker);
		      });
		    })(marker, data);
		  }
		  map.setCenter(latlngbounds.getCenter());
		  map.fitBounds(latlngbounds);

		  //***********ROUTING****************//

		  //Initialize the Path Array
		  var path = new google.maps.MVCArray();

		  //Initialize the Direction Service
		  var service = new google.maps.DirectionsService();

		  //Set the Path Stroke Color
		  var poly = new google.maps.Polyline({
		    map: map,
		    strokeColor: '#4986E7'
		  });
		  var waypts = [];
		  var checkboxArray = markers; //document.getElementById('waypoints');
		  	console.log(checkboxArray)
		  	for (var i = 0; i < checkboxArray.length; i++) {
		    //if (checkboxArray.options[i].selected == true) {
		      waypts.push({
		        location:checkboxArray[i].description,
		        stopover:true});
		    //}
		  	}

		  //Loop and Draw Path Route between the Points on MAP
		  for (var i = 0; i < lat_lng.length; i++) {
		    if ((i + 1) < lat_lng.length) {
		      var src = lat_lng[i];
		      var des = lat_lng[i + 1];
		      // path.push(src);
		      poly.setPath(path);
		      service.route({
		        origin: src,
		        destination: des,
		        waypoints: waypts,
		        optimizeWaypoints: true,
			      travelMode: google.maps.TravelMode.DRIVING
		      }, function(result, status) {
		        if (status == google.maps.DirectionsStatus.OK) {
		          for (var i = 0, len = result.routes[0].overview_path.length; i < len; i++) {
		            path.push(result.routes[0].overview_path[i]);
		          }
		        }
		      });
		    }
		  }
		}
	});




</script>