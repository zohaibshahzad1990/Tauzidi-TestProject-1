<div class="kt-portlet">
	<div class="kt-alert kt--alert--outline alert alert-success alert-dismissible fade show" id="success-alert" role="alert" style="display: none;">
		
	</div>

	<div class="kt--alert kt--alert--outline alert alert-danger alert-dismissible fade show" id="error-alert" role="alert" style="display: none;">
		
	</div>
	

	<style>
    /* Always set the map height explicitly to define the size of the div
   element that contains the map. */

    #map {
        height: 100%;
    }

    /* Optional: Makes the sample page fill the window. */

    html,
    body {
        height: 100%;
        margin: 0;
        padding: 0;
    }

    #locationField,
    #controls {
        position: relative;
        width: 480px;
    }

    #autocomplete {
        position: absolute;
        top: 0px;
        left: 0px;
        width: 99%;
    }

    .label {
        text-align: right;
        font-weight: bold;
        width: 100px;
        color: #303030;
    }

    #address {
        border: 1px solid #000090;
        background-color: #f0f0ff;
        width: 480px;
        padding-right: 2px;
    }

    #address td {
        font-size: 10pt;
    }

    .field {
        width: 99%;
    }

    .slimField {
        width: 80px;
    }

    .wideField {
        width: 200px;
    }

    #locationField {
        height: 20px;
        margin-bottom: 2px;
    }
</style>

</head>
<body>
<br><br>
<div id="locationField">
    <input id="autocomplete" placeholder="Enter your address" type="text"></input>
</div>
<table id="address">
    <tr>
        <td class="label">Street address</td>
        <td class="slimField"><input class="field" id="street_number" disabled="true" name="streetnumber"></input>
        </td>
        <td class="wideField" colspan="2"><input class="field" id="route" disabled="true" name="route"></input>
        </td>
    </tr>
    <tr>
        <td class="label">City</td>
        <!-- Note: Selection of address components in this example is typical.
     You may need to adjust it for the locations relevant to your app. See
     https://developers.google.com/maps/documentation/javascript/examples/places-autocomplete-addressform    
     -->
        <td class="wideField" colspan="3"><input class="field" id="locality" disabled="true" name="locality"></input>
        </td>
    </tr>
    <tr>
        <td class="label">State</td>
        <td class="slimField"><input class="field" id="administrative_area_level_1" disabled="true" name="state"></input>
        </td>
        <td class="label">Zip code</td>
        <td class="wideField"><input class="field" id="postal_code" disabled="true" name="postal_code"></input>
        </td>
    </tr>
    <tr>
        <td class="label">Country</td>
        <td class="wideField" colspan="3"><input class="field" id="country" disabled="true" name="country"></input>
        </td>
    </tr>
</table>
<div id="map" style="width: 50%; height: 250px;"></div>
<div id="infowindow-content">
    <img src="" width="16" height="16" id="place-icon">
    <span id="place-name" class="title"></span><br>
    <span id="place-address"></span>
</div>

</div>

<div class="kt-portlet">
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
<script type="text/javascript">
	var base_url = window.origin;
	$(document).ready(function(){
		
    var componentForm = {
        street_number: 'short_name',
        route: 'long_name',
        locality: 'long_name',
        administrative_area_level_1: 'short_name',
        country: 'long_name',
        postal_code: 'short_name'
    };



    var input = document.getElementById('autocomplete');

    function initMap() {
        var geocoder;
        var autocomplete;

        geocoder = new google.maps.Geocoder();
        var map = new google.maps.Map(document.getElementById('map'), {
            center: {
                lat: -33.8688,
                lng: 151.2195
            },
            zoom: 13
        });
        var card = document.getElementById('locationField');
        autocomplete = new google.maps.places.Autocomplete(input);

        // Bind the map's bounds (viewport) property to the autocomplete object,
        // so that the autocomplete requests use the current map bounds for the
        // bounds option in the request.
        autocomplete.bindTo('bounds', map);

        var infowindow = new google.maps.InfoWindow();
        var infowindowContent = document.getElementById('infowindow-content');
        infowindow.setContent(infowindowContent);
        var marker = new google.maps.Marker({
            map: map,
            anchorPoint: new google.maps.Point(0, -29),
            draggable: true
        });

        autocomplete.addListener('place_changed', function() {
            infowindow.close();
            marker.setVisible(false);
            var place = autocomplete.getPlace();
            console.log(place);

            if (!place.geometry) {
                // User entered the name of a Place that was not suggested and
                // pressed the Enter key, or the Place Details request failed.
                window.alert("No details available for input: '" + place.name + "'");
                return;
            }

            // If the place has a geometry, then present it on a map.
            if (place.geometry.viewport) {
                map.fitBounds(place.geometry.viewport);
            } else {
                map.setCenter(place.geometry.location);
                map.setZoom(17); // Why 17? Because it looks good.
            }
            marker.setPosition(place.geometry.location);
            marker.setVisible(true);

            var address = '';
            if (place.address_components) {
                address = [
                    (place.address_components[0] && place.address_components[0].short_name || ''),
                    (place.address_components[1] && place.address_components[1].short_name || ''),
                    (place.address_components[2] && place.address_components[2].short_name || '')
                ].join(' ');
            }

            infowindowContent.children['place-icon'].src = place.icon;
            infowindowContent.children['place-name'].textContent = place.name;
            infowindowContent.children['place-address'].textContent = address;
            infowindow.open(map, marker);
            fillInAddress();

        });

        function fillInAddress(new_address) { // optional parameter
            if (typeof new_address == 'undefined') {
                var place = autocomplete.getPlace(input);
            } else {
                place = new_address;
            }
            //console.log(place);
            for (var component in componentForm) {
                document.getElementById(component).value = '';
                document.getElementById(component).disabled = false;
            }

            for (var i = 0; i < place.address_components.length; i++) {
                var addressType = place.address_components[i].types[0];
                if (componentForm[addressType]) {
                    var val = place.address_components[i][componentForm[addressType]];
                    document.getElementById(addressType).value = val;
                }
            }
        }

        google.maps.event.addListener(marker, 'dragend', function() {
            geocoder.geocode({
                'latLng': marker.getPosition()
            }, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    if (results[0]) {
                        console.log(autocomplete);
                        $('#autocomplete').val(results[0].formatted_address);
                        $('#latitude').val(marker.getPosition().lat());
                        $('#longitude').val(marker.getPosition().lng());
                        infowindow.setContent(results[0].formatted_address);
                        infowindow.open(map, marker);
                        // google.maps.event.trigger(autocomplete, 'place_changed');
                        fillInAddress(results[0]);
                    }
                }
            });
        });
    }

	});

function isJson(str) {
        try {
            JSON.parse(JSON.stringify(str))
            // /JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    }

</script>
