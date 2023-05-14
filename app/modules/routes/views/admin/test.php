<div class="kt-portlet">
	<div class="kt-alert kt--alert--outline alert alert-success alert-dismissible fade show" id="success-alert" role="alert" style="display: none;">
		
	</div>

	<div class="kt--alert kt--alert--outline alert alert-danger alert-dismissible fade show" id="error-alert" role="alert" style="display: none;">
		
	</div>
	<?php echo form_open_multipart(current_url(),'class="kt-form" role="form" id="drop_points_form"');?> 
		<div class="kt-portlet__body">
		    <span class="error"></span>
		    <div class="table-responsive">
		        <table class="table table-condensed contribution-table multiple_payment_entries" id="">
		            <thead>
		                <tr> 
		                    <th width="1%">
		                        #
		                    </th>
		                    <th width="23%">
		                        Drop Point <span class='required'>*</span>
		                    </th>
		                    <th width="18%">
		                        Longitude <span class='required'>*</span>
		                    </th>
		                    <th width="18%">
		                        Latitide <span class='required'>*</span>
		                    </th>
		                    <th width="18%">
		                        Distance <span class='required'>*</span>
		                    </th>
		                    <th width="18%">
		                        Duration <span class='required'>*</span>
		                    </th>
		                    <th width="4%">
		                       &nbsp;
		                    </th>
		                </tr>
		            </thead>
		            <tbody id='append-place-holder'>
		                <tr>
		                    <th scope="row" class="count">
		                        1
		                    </th>
		                    <td>
		                        <?php echo form_input('drop_name[0]',$post->drop_name,'class="form-control form-control-sm m-input m-input--air" id="drop_name" placeholder="Drop Point"');?>
                            </td>
                            <td>
		                        <?php echo form_input('drop_longitude[0]','','class="form-control form-control-sm m-input m-input--air" id="longitude" placeholder="Longitude"');?>
                            </td>
		                    <td>
		                        <?php echo form_input('drop_latitude[0]','','class="form-control form-control-sm m-input m-input--air" id="latitude" placeholder="Latitude"');?>
                            </td>
                            <td>
		                        <?php echo form_input('drop_distance[0]','','class="form-control form-control-sm m-input m-input--air" id="distance" placeholder="Distance"');?>
                            </td>
                            <td>
		                        <?php echo form_input('drop_duration[0]','','class="form-control form-control-sm m-input m-input--air" id="duration" placeholder="Duration"');?>
                            </td>
		                    <td class="text-right">
		                        <a class="remove-line">
		                            <i class="text-danger la la-trash"></i>
		                        </a>
		                    </td>
		                </tr>
		            </tbody>
		        </table>
		    </div>
		    <div class="row">
		        <div class="col-md-12">
		            <a class="btn btn-default btn-sm add-new-line" id="add-new-line">
		                <i class="la la-plus"></i>Add New Drop Point </a>
		        </div>
		    </div>
		    <input type="hidden" name="start_point" id="start_point" />
		    <input type="hidden" name="end_point" id="end_point" />
		    <input type="hidden" name="start_longitude" id="start_longitude" />
	      	<input type="hidden" name="start_latitude"  id="start_latitude" />
			<input type="hidden" name="destination_longitude" id="destination_longitude" />
			<input type="hidden" name="destination_latitude" id="destination_latitude" />
			<input type="hidden" name="distance" id="distance" />
			<input type="hidden" name="duration" id="duration" />
			<input type="hidden" name="id" id="id" />
		    <div class="kt-form__actions kt-form__actions p-0 pt-5 kt--margin-top-10">                            
		        <div class="row">
		            <div class="col-md-12">
		                <span class="float-lg-right float-md-left float-sm-left float-xl-right">
		                    <button class="btn btn-primary kt-btn kt-btn--custom kt-btn--icon btn-sm submit_form_button" id="" type="submit">
		                       Record Contribution Payments                              
		                    </button>
		                    &nbsp;&nbsp;
		                    <button class="btn btn-metal m-btn kt-btn--custom kt-btn--icon btn-sm cancel_form" type="button" id="">
		                        Cancel                              
		                    </button>
		                </span>
		            </div>
		        </div>
		    </div>


	      	<div id="map-cordinates" style="margin-top:10px;"></div>
		</div>		
	<?php echo form_close(); ?>
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
</div>

<div id='append-new-line' class="d-none">
    <table>
        <tbody>
            <tr>
                <th scope="row" class="count">
                    1
                </th>
                <td>
                    <?php echo form_input('drop_name[0]','','class="form-control drop_name form-control-sm m-input m-input--air" id="drop_name" placeholder="Drop Point"');?>
                </td>
                <td>
	                <?php echo form_input('drop_longitude[0]','','class="form-control form-control-sm m-input m-input--air" id="longitude" placeholder="Longitude"');?>
	            </td>
	            <td>
	                <?php echo form_input('drop_latitude[0]','','class="form-control form-control-sm m-input m-input--air" id="latitude" placeholder="Latitude"');?>
	            </td>
	            <td>
	                <?php echo form_input('drop_distance[0]','','class="form-control form-control-sm m-input m-input--air" id="distance" placeholder="Distance"');?>
	            </td>
	            <td>
	                <?php echo form_input('drop_duration[0]','','class="form-control form-control-sm m-input m-input--air" id="duration" placeholder="Duration"');?>
	            </td>
                <td >
                    <a title="Delete" class="btn btn-sm btn-clean btn-icon btn-icon-md remove-line" id="remove-line">
                        <i class="text-danger la la-trash"></i>                     
                    </a>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<script type="text/javascript">
	var base_url = window.origin;
	$(document).ready(function(){

		var id = "<?php echo $id;?>";
		var start_latitiude = "<?php echo $route->start_latitude?>";
		var start_longitude = "<?php echo $route->start_longitude?>";
		var destination_longitude = "<?php echo $route->destination_longitude?>";
		var destination_latitude = "<?php echo $route->destination_latitude?>";
		var start_point = "<?php echo $route->start_point?>";
		var end_point = "<?php echo $route->end_point?>";

		

		$(document).on('click','#drop_points_form .add-new-line',function(e){

            var html = $('#append-new-line tbody').html();
            //html = html.replace_all('checker','');
            $('#append-place-holder').append(html);
            $('.tooltips').tooltip();
            $('.date-picker').datetimepicker({ 
                pickerPosition: 'bottom-left',
                todayHighlight: true,
                autoclose: true,
                format: 'yyyy.mm.dd hh:ii'
            });
            var number = 1;
            $('.count').each(function(){
                $(this).text(number);
                $(this).parent().find('.drop_name').attr('name','drop_name['+(number-1)+']');
                $(this).parent().find('.leagues').attr('name','competition_id['+(number-1)+']');
                $(this).parent().find('.seasons').attr('name','season_ids['+(number-1)+']');
                $(this).parent().find('.home_teams').attr('name','home_team_ids['+(number-1)+']');
                $(this).parent().find('.away_teams').attr('name','away_team_ids['+(number-1)+']');
                number++;
            });
            $('.fixtures-table .m-select2-append').select2({
                placeholder:{
                    id: '-1',
                    text: "--Select option--",
                }, 
            });
        });

        $(document).on('click','.remove-line',function(event){
            $(this).parent().parent().remove();
            var number = 1;
            $('.count').each(function(){
                $(this).text(number);
                number++;
            });
        });

        var myLatLng = { lat: 1.2921, lng: 36.8219 };
		var mapOptions = {
			center: myLatLng,
			zoom: 7,
			mapTypeId: google.maps.MapTypeId.ROADMAP,
		};

		var map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);

		var directionsDisplay = new google.maps.DirectionsRenderer();

		directionsDisplay.setMap(map);


		var options = {
			types: ["(cities)"],
			componentRestrictions: { country: "ke" },
		};

		var input1 = document.getElementById("start_point");
		var autocomplete1 = new google.maps.places.Autocomplete(input1, options);

		google.maps.event.addListener(autocomplete1, 'place_changed', function () {
	        var place = autocomplete1.getPlace();      
	        console.log(place)          
	        document.getElementById('start_point').value = place.name;
	        document.getElementById('start_latitude').value = place.geometry.location.lat();
	        document.getElementById('start_longitude').value = place.geometry.location.lng();
	        
	   });
		var input2 = document.getElementById("end_point");
		var autocomplete2 = new google.maps.places.Autocomplete(input2, options);
		google.maps.event.addListener(autocomplete2, 'place_changed', function () {
	        var place = autocomplete2.getPlace();                
	        document.getElementById('end_point').value = place.name;
	        document.getElementById('destination_latitude').value = place.geometry.location.lat();
	        document.getElementById('destination_longitude').value = place.geometry.location.lng();
	        calculateDistance();
	  	});

	  	var directionsService = new google.maps.DirectionsService();
		var directionsDisplay = new google.maps.DirectionsRenderer();
		directionsDisplay.setMap(map);

		if(id){
			$('#id').val(id);
			$('#end_point').val(end_point);
			$('#start_point').val(start_point);
			$('#start_latitude').val(start_latitude);
			$('#start_longitude').val(start_longitude);
			$('#destination_latitude').val(destination_latitude);
			$('#destination_longitude').val(destination_longitude);
			calculateDistance();
		}

		$(document).on('keyup','#drop_name',function(event){    
            console.log($(this).val());
            var start = $('#start_point').val();
            var toPlace = $(this).val();
            console.log(toPlace)

            var options = {
				types: ["(cities)"],
				componentRestrictions: { country: "ke" },
			};
			var autocomplete1 = new google.maps.places.Autocomplete(toPlace, options);
			console.log(autocomplete1)
			google.maps.event.addListener(autocomplete1, 'place_changed', function () {
		        var place = autocomplete1.getPlace();      
		        console.log(place)          
		        //document.getElementById('start_point').value = place.name;
		        //document.getElementById('start_latitude').value = place.geometry.location.lat();
		        //document.getElementById('start_longitude').value = place.geometry.location.lng();
		        
		    });

        });

        function calculateDistance(){
		    /**
		     * Creating a new request
		     */
		    var request = {
		        origin: document.getElementById("start_point").value,
		        destination: document.getElementById("end_point").value,
		        travelMode: google.maps.TravelMode.DRIVING, //WALKING, BYCYCLING, TRANSIT
		        unitSystem: google.maps.UnitSystem.IMPERIAL
		    }

		    /**
		     * Pass the created request to the route method
		     */

		    directionsService.route(request, function (result, status) {
		        if (status == google.maps.DirectionsStatus.OK) {
		            /**
		             * Get distance and time then display on the map
		             */
		            const output = document.querySelector('#map-cordinates');
		            document.getElementById('duration').value = result.routes[0].legs[0].duration.text;
		            document.getElementById('distance').value = result.routes[0].legs[0].distance.text;
		            output.innerHTML = "<p class='alert-success'>From: " + document.getElementById("start_point").value + "</br>" +"To: " + document.getElementById("end_point").value + "</br>"+"Driving distance <i class='fas fa-road'></i> : " + result.routes[0].legs[0].distance.text +"</br>"+ " Duration <i class='fas fa-clock'></i> : " + result.routes[0].legs[0].duration.text + ".</p>";

		            /**
		             * Display the obtained route
		             */
		            directionsDisplay.setDirections(result);
		        } else {
		            /**
		             * Eliminate route from the map
		             */
		            directionsDisplay.setDirections({ routes: [] });
		            
		            /**
		             * Centre the map to my current location
		             */
		            map.setCenter(myLatLng);

		            /**
		             * show error message in case there is any
		             */
		            output.innerHTML = "<div class='alert-danger'><i class='fas fa-exclamation-triangle'></i> Could not retrieve driving distance.</div>";
		        }
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
