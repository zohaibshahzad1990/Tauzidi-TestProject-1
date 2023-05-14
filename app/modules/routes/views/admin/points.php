<div class="kt-portlet" id="portlet_body">
    <div class="kt-portlet__head">
        <div class="kt-portlet__head-label">
            <h3 class="kt-portlet__head-title">
                {metronic:template:title}
            </h3>
        </div>
        <div class="kt-portlet__head-toolbar">
			
            <button id="add_drop_points" data-title="Create Drop Off/Pick up Point" data-pay-bill-id="" data-content="#till-number-form-holder" class="btn btn-default">
				<i class="fa fa-map-marker-alt"></i> Add Drop Off Points 
			</button>
		</div>
    </div>
    <div class="form-drop-form" id="drop_form" style="display:none;">
	    <?php echo form_open(current_url(),'class="submit-form m-form m-form--label-align-right"  id="submitroute" role="form"');?>
	    <div class="kt-alert kt--alert--outline alert alert-success alert-dismissible fade show" id="success-alert" role="alert" style="display: none;">		
			</div>
			<div class="kt--alert kt--alert--outline alert alert-danger alert-dismissible fade show" id="error-alert" role="alert" style="display: none;">				
			</div>
			<div class="kt-portlet__body">
			    <div class="row">
					<div class="col-md-6">
						<div class="form-group m-form__group">
							<label for="">
								Start Point <span class="required">*</span>
							</label>
							<?php echo form_input('drop_point','','class="form-control m-input m-input--air" id="drop_point" placeholder="Point Name"');?>
							<span class="m-form__help">
							</span>
						</div>
						<div id="map-cordinates"></div>
					</div>
					<div class="col-md-6">
						<div class="kt-portlet__body">
							<div class="kt-portlet">
								<div class="kt-portlet__body kt-portlet__body--fit">
									<div class="row row-no-padding row-col-separator-xl">
										<div class="col-md-12 col-lg-12">
											<div class="z-depth-1" style="height: 150px">
												<div id="map-wrapper">
								                	<div id="map-canvas"></div>
								            	</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>	
			
				<input type="hidden" name="start_point" id="start_point" />
				<input type="hidden" name="drop_longitude" id="start_longitude" />
			  	<input type="hidden" name="drop_latitude"  id="start_latitude" />
				<input type="hidden" name="distance" id="distance" />
				<input type="hidden" name="duration" id="duration" />
				<input type="hidden" name="id" id="id" />
				<input type="hidden" name="route_id" id="route_id" />		  	
				
				<div class="kt-portlet__foot">
				    <div class="kt-form__actions">
				        <button class="btn btn-sm btn-primary" id="btn_route">Save</button>
				        <button type="submit" disabled="disabled" class="btn btn-sm btn-primary processing-button"><i class="fas fa-circle-notch fa-spin"></i> Processing</button> 
                		&nbsp;&nbsp;&nbsp;
                		<button type="button" class="btn-sm btn btn-default" id="cancel_btn">
		                    Cancel            
		                </button>
				    </div>
				</div>
			</div>
		<?php echo form_close(); ?>	
	</div>

	<div class="form-drop-form" id="drop_point_list" >
		<div class="kt-portlet__body" id="points_table">
		   
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
		$( window ).on("load", function() {
		    load_drop_points();
		});

		$('#add_drop_points').click(function(){
			$('#drop_point').val('');
            $('#start_longitude').val('');
            $('#start_latitude').val('');
            $('#distance').val('');
            $('#duration').val('');
            $('#route_id').val('');
            $('#map-cordinates').html('')
			$('#drop_form').show();
			$('#drop_point_list').hide();

		})

		$('#cancel_btn').click(function(){
			$('#drop_point').val('');
            $('#start_longitude').val('');
            $('#start_latitude').val('');
            $('#distance').val('');
            $('#duration').val('');
            $('#route_id').val('');
            $('#map-cordinates').html('')
			$('#drop_form').hide();
			$('#drop_point_list').show();
			load_drop_points();
		})

		
		var id = "<?php echo $id;?>";
		var start_latitiude = "<?php echo $route->start_latitude?>";
		var start_longitude = "<?php echo $route->start_longitude?>";
		var destination_longitude = "<?php echo $route->destination_longitude?>";
		var destination_latitude = "<?php echo $route->destination_latitude?>";
		var start_point = "<?php echo $route->start_point?>";
		var end_point = "<?php echo $route->end_point?>";
		if(id){
			$('#id').val(id);
			$('#start_point').val(start_point);
		}

		$('body').on('click','#edit_point',function(){
		    var id = $(this).data("id");
		    KTApp.block('#drop_point',{
	            overlayColor: 'grey',
	            animate: true,
	            type: 'loader',
	            state: 'primary',
	            message: 'Fetching data...'
	        });
		    $.ajax({
                url : base_url+"/ajax/routes/get_point",
                method : "POST",
                data:{ id: id},
                success: function (response) {
                    var result =$.parseJSON(JSON.stringify(response));
                    if(result.result_code == 200){
                        //toastr.success("Drop point found", 'success!', {timeOut: 5000});
                        //drop_point
                        $('#drop_point').val(result.data.name);
                        $('#start_longitude').val(result.data.longitude);
                        $('#start_latitude').val(result.data.latitude);
                        $('#distance').val(result.data.distance);
                        $('#duration').val(result.data.duration);
                        $('#route_id').val(result.data.id);
                        $('#drop_form').show();
						$('#drop_point_list').hide();
						calculateDistance();
						KTApp.unblock('#portlet_body');
                    }else{
                        toastr.error("Route not found, try again", 'Oops!', {timeOut: 5000})
                        KTApp.unblock('#portlet_body');
                    }
                },
                error: function (data) {
                	KTApp.unblock('#portlet_body');
                },
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
			//types: ["(cities)"],
			componentRestrictions: { country: "ke" },
		};

		var input1 = document.getElementById("drop_point");
		console.log(input1)
		var autocomplete1 = new google.maps.places.Autocomplete(input1, options);

		google.maps.event.addListener(autocomplete1, 'place_changed', function () {
	        var place = autocomplete1.getPlace();         
	        document.getElementById('drop_point').value = place.name;
	        document.getElementById('start_latitude').value = place.geometry.location.lat();
	        document.getElementById('start_longitude').value = place.geometry.location.lng();
	        calculateDistance();
	        
	   });
		var directionsService = new google.maps.DirectionsService();
		var directionsDisplay = new google.maps.DirectionsRenderer();
		directionsDisplay.setMap(map);

	    function calculateDistance(){
		    /**
		     * Creating a new request
		     */
		     console.log(document.getElementById("start_point").value)
		    var request = {
		        origin: document.getElementById("start_point").value,
		        destination: document.getElementById("drop_point").value,
		        travelMode: google.maps.TravelMode.DRIVING, //WALKING, BYCYCLING, TRANSIT
		        unitSystem: google.maps.UnitSystem.IMPERIAL
		    }

		    /**
		     * Pass the created request to the route method
		     */

		     directionsService.route(request, function (result, status) {
		        if (status == google.maps.DirectionsStatus.OK) {
							console.log(result)
		            /**
		             * Get distance and time then display on the map
		             */
		            const output = document.querySelector('#map-cordinates');
		            document.getElementById('duration').value = result.routes[0].legs[0].duration.text;
		            document.getElementById('distance').value = result.routes[0].legs[0].distance.text;
		            output.innerHTML = "<p class='alert-success'>From: " + document.getElementById("start_point").value + "</br>" +"To: " + document.getElementById("drop_point").value + "</br>"+"Driving distance <i class='fas fa-road'></i> : " + result.routes[0].legs[0].distance.text +"</br>"+ " Duration <i class='fas fa-clock'></i> : " + result.routes[0].legs[0].duration.text + ".</p>";

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
		            //output.innerHTML = "<div class='alert-danger'><i class='fas fa-exclamation-triangle'></i> Could not retrieve driving distance.</div>";
		        }
		    });
		}


		 $('#submitroute').submit(function(e){
        e.preventDefault();
        var submitButton = $('#btn_route');
        $("#btn_route").hide();
    		$('.processing-button').show().css("display","inline-block");
    		KTApp.block('#submitroute',{
            overlayColor: 'grey',
            animate: true,
            type: 'loader',
            state: 'primary',
            message: 'submitting data...'
        });
        $.ajax({
            url:base_url+'/ajax/routes/create_drop',
            type:"post",
            data:new FormData(this),
            processData:false,
            contentType:false,
            cache:false,
            async:false,
            beforeSend: function () {
                $("#error-alert").html('').hide();
                $("#success-alert").html('').hide();
                $("#btn_route").hide();
				$('.processing-button').show(); 
            },
            success: function(response){
                if(isJson(response)){
                    if(response.result_code == 200){
	                		toastr.options = {
							  	"closeButton": true,
							  	"debug": false,
							  	"newestOnTop": true,
							  	"progressBar": true,
							  	"positionClass": "toast-bottom-right",
							  	"preventDuplicates": false,
							  	"showDuration": "5000",
							  	"hideDuration": "1000",
							  	"timeOut": "5000",
							  	"extendedTimeOut": "1000",
							  	"showEasing": "swing",
							  	"hideEasing": "linear",
							  	"showMethod": "fadeIn",
							  	"hideMethod": "fadeOut"
							};
							toastr.success(response.message);
							//setTimeout(function(){ window.location.href=base_url+'/admin/routes/listing'; }, 3000);
			              }else{
	                		 var message = response.message;
                            var validation_errors = '';
                            if(response.hasOwnProperty('validation_errors')){
                                validation_errors = response.validation_errors;
                            }
                            var error_message = [];
                            if(validation_errors){ 
								error_message.push('<div class="alert-text">');
								$.each(validation_errors, function( key, value ) {
								    error_message.push('<p>' +value + '</p>');
								});
								error_message.push('</div>');
								$("#error-alert").append(error_message.join('')).show();                               
                            }
                            
                     
	                	}
	                	KTApp.unblock('#submitroute');
							    $("#btn_route").show();
							    $('.processing-button').hide(); 
                }else{
                    submitButton.attr('disabled', false);
                    submitButton.find('.fas').remove();
                }
            }
        });
    });

	function load_drop_points(){
		KTApp.block('#drop_point_list');
	    var load_drop_points = $.ajax({
            type: "GET",
            url:base_url+'/ajax/routes/get_points/'+id,
            dataType : "html",
                success: function(response) {
                	$('#points_table').html(response);
                    KTApp.unblock('#drop_point_list');
                }
            }
        );
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
