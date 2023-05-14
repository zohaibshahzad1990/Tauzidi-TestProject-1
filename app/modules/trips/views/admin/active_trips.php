<div class="kt-portlet" id="portlet_body">
    <div class="kt-portlet__head">
        <div class="kt-portlet__head-label">
            <h3 class="kt-portlet__head-title">
                Trip: <?php echo $route->name;?>
            </h3>
        </div>
        <div class="kt-portlet__head-toolbar">
            <a href="#" data-title="Start <?php echo $trip->name;?> Journey" data-pay-bill-id="" data-content="#start-trip-form-holder" class="btn btn-default launch-modal">
                <i class="la la-cart-plus"></i> Start Trip
            </a>
		</div>
    </div>
    <div class="kt-portlet__body">

        <?php if(!$journey){ ?>

            <div class="row">
                <div class="col-md-12">
                    <!--begin::Portlet-->
                    <div class="kt-portlet kt-portlet--tab">

                        <div class="kt-portlet__body" style="padding:0px!important;">
                            <div class="kt-alert kt-alert--icon m-alert--icon-solid kt-alert--outline alert alert-brand alert-dismissible fade show" role="alert">
                               <!--  <div class="kt-alert__icon">
                                    <i class="flaticon-exclamation-1"></i>
                                    <span></span>
                                </div> -->
                                <div class="kt-alert__text">
                                    <!-- <strong>
                                        Heads up! &nbsp;
                                    </strong> -->
                                    There are no Active Journeys for that vehicle
                                </div>
                            </div>
                        </div>

                    </div>
                    <!--end::Portlet-->
                </div>
            </div>

        <?php }else{ ?>

            <div class="kt-portlet">
                <div class="kt-portlet__body">
                    <div class="kt-widget kt-widget--user-profile-3">
                        <div class="kt-widget__top">
                            <div class="kt-widget__content">
                                <div class="kt-widget__head">
                                    <a class="kt-widget__username">
                                        <?php echo $driver->first_name .' '. $driver->last_name?>
                                    </a>
                                    <div class="kt-widget__action d-none">
                                        <button type="button" class="btn btn-label-success btn-sm btn-upper">ask</button>&nbsp;
                                        <button type="button" class="btn btn-brand btn-sm btn-upper">hire</button>
                                    </div>
                                </div>
                                <div class="kt-widget__subhead">
                                    <a href="#"><i class="flaticon2-new-email"></i> <?php echo $driver->phone;?></a>
                                    <a href="#"><i class="flaticon2-calendar-3"></i>Driver</a>
                                    <a href="#"><i class="flaticon2-placeholder"></i>Kenya</a>
                                </div>
                                <div class="kt-widget__info d-none">
                                    <div class="kt-widget__progress">
                                        <div class="kt-widget__text">
                                            Progress
                                        </div>
                                        <div class="progress" style="height: 5px;width: 100%;">
                                            <div class="progress-bar kt-bg-brand" role="progressbar" style="width: 45%;" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <div class="kt-widget__stats">
                                            46%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="kt-widget__bottom">
                            <div class="kt-widget__item">
                                <div class="kt-widget__icon">
                                    <i class="la la-car"></i>
                                </div>
                                <div class="kt-widget__details">
                                    <span class="kt-widget__title">Registration</span>
                                    <span class="kt-widget__value"><span><?php echo $vehicle->registration;?></span>
                                </div>
                            </div>
                            <div class="kt-widget__item">
                                <div class="kt-widget__icon">
                                    <i class="flaticon-map-location"></i>
                                </div>
                                <div class="kt-widget__details">
                                    <span class="kt-widget__title">Distance</span>
                                    <span class="kt-widget__value"><?php echo $route->distance;?></span>
                                </div>
                            </div>
                            <div class="kt-widget__item">
                                <div class="kt-widget__icon">
                                    <i class="la la-clock-o"></i>
                                </div>
                                <div class="kt-widget__details">
                                    <span class="kt-widget__title">Estimated Time</span>
                                    <span class="kt-widget__value"><?php echo $route->duration;?></span>
                                </div>
                            </div>
                            <div class="kt-widget__item">
                                <div class="kt-widget__icon">
                                    <i class="flaticon-user-settings"></i>
                                </div>
                                <div class="kt-widget__details">
                                    <span class="kt-widget__title">Capacity</span>
                                    <span class="kt-widget__value"><?php echo $vehicle->capacity;?></span>
                                </div>
                            </div>
                        </div>
                    </div>
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
            </div>


        <?php } ?>

    </div>

</div>



<div class='d-none' id="start-trip-form-holder">
    <div id="start-trip-form">
        <div class="alert alert-solid-danger alert-bold data_error" role="alert" style="display:none;">
            <div class="alert-text">    
                <p><strong> Oh oh! we have a problem. </strong></p>
                <div id="error-description">
                </div>  
            </div>
        </div>
        <div id="" class="kt-portlet__body">
            <div class="form-group form-group-last">
                <div class="alert alert-secondary" role="alert">
                    <div class="alert-icon"><i class="flaticon-warning kt-font-brand"></i></div>
                    <div class="alert-text">
                        Start journey for <strong><?php echo $vehicle->registration;?></strong> from <strong> <?php echo $route->start_point;?> </strong> to <strong><?php echo $route->end_point;?> </strong>a distance of <strong><?php echo $route->distance;?></strong>
                    </div>
                </div>
            </div>
            <?php echo form_hidden('id',$trip->id)?>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group m-form__group">
                        <label>From</label>
                        <?php echo form_input('start_point',$this->input->post('start_point')?$this->input->post('start_point'):$route->start_point,'class="form-control" disabled="disabled" placeholder=" Route From"');?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>To</label>
                        <?php echo form_input('start_point',$this->input->post('start_point')?$this->input->post('start_point'):$route->start_point,'class="form-control" disabled="disabled" placeholder=" Route From"');?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).on('submit','#modal-form',function(e){
        var base_url = window.origin;


        $('.modal-submit-button').hide();
        $('.modal-processing-button').show().css("display","inline-block");
        var base_url = window.location.origin;
        var form = $(this);
        if(form.find('#start-trip-form').is(':visible')){
            var pay_bill_id = $('input[name=id]').val();
            KTApp.block('.modal-body', {});
                $.ajax({
                    type: "POST",
                    url: base_url+'/ajax/trips/start',
                    data: form.serialize(),
                    dataType: "json",
                    success: function(response) {
                        if(response.status == 200){
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
                            toastr.success("You have started a new journey");
                            $('.modal').modal('hide');
                        }else{
                            
                            var message = response.message;
                            var validation_errors = '';
                            if(response.hasOwnProperty('validation_errors')){
                                validation_errors = response.validation_errors;
                            }
                            var error_message = [];
                            if(validation_errors){ 
                                //error_message.push('<div class="alert-text">');
                                $.each(validation_errors, function( key, value ) {
                                    error_message.push('<p>' +value + '</p>');
                                });
                                $('.data_error').each(function(){
                                    $(this).slideDown('fast',function(){
                                        var element = $(this).find('#error-description');
                                         element.html(error_message.join(''));
                                    });
                                });                              
                            }
                        }
                        KTApp.unblock('.modal-body');
                        $('.modal-submit-button').show();
                        $('.modal-processing-button').hide();
                    }
                });
        }
        e.preventDefault();

        
    });

    $(document).ready(function(){

        var myLatLng = { lat: 1.2921, lng: 36.8219 };
        var mapOptions = {
            center: myLatLng,
            zoom: 7,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
        };

        var map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);

        var directionsDisplay = new google.maps.DirectionsRenderer();

        directionsDisplay.setMap(map);

        var directionsService = new google.maps.DirectionsService();
        var directionsDisplay = new google.maps.DirectionsRenderer();
        directionsDisplay.setMap(map);

        function calculateDistance(){

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
                            console.log(result)
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
                }else {
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
</script>