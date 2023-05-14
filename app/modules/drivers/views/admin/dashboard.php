
<div class="kt-portlet kt-portlet--mobile">
	
	<div class="kt-portlet__body kt-portlet__body--fit">

		<!--begin: Datatable -->
		<div class="kt-portlet kt-portlet--tabs kt-portlet--height-fluid">
			<div class="kt-portlet__head">
				<div class="kt-portlet__head-label">
					<span class="kt-portlet__head-icon">
						<i class="kt-font-brand flaticon2-line-chart"></i>
					</span>
					<h3 class="kt-portlet__head-title">
						{metronic:template:title} Trips
					</h3>
				</div>
				<div class="kt-portlet__head-toolbar">
					<ul class="nav nav-tabs nav-tabs-line nav-tabs-bold nav-tabs-line-brand" role="tablist">
						<li class="nav-item">
							<a class="nav-link active" data-toggle="tab" id="trips" href="#kt_widget4_driver_trips" role="tab">
								Assigned
							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" data-toggle="tab" id="ongoing" href="#kt_widget4_ongoing_trips" role="tab">
								Ongoing
							</a>
						</li>
					</ul>
				</div>
			</div>
			<div class="kt-portlet__body">
				<div class="tab-content">
					<div class="tab-pane active" id="kt_widget4_driver_trips">
						
					</div>
					<div class="tab-pane" id="kt_widget4_ongoing_trips">
						
					</div>
				</div>
			</div>
		</div>

		<!--end: Datatable -->
	</div>
</div>

<script type="text/javascript">
	var base_url = window.origin;
	var driver_id = "<?php echo $driver->id;?>";
	var vehicle_id = "<?php echo $driver->vehicle_id;?>";
	
	$(document).ready(function(){

		$( window ).on("load", function() {
		    load_driver_vehicle_trips(driver_id);
		});
		
		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
		  	var target = $(e.target).attr("id") // activated tab
		  	//console.log(target)
		  	if(target == "trips"){
		  		load_driver_vehicle_trips(driver_id);
		  	}

		  	if(target == "ongoing"){
		  		load_driver_vehicle_ongoing_trips(driver_id);
		  	}
		});

		function load_driver_vehicle_trips(driver_id){
			KTApp.block('#kt_widget4_driver_trips',{
	            overlayColor: 'grey',
	            animate: true,
	            type: 'loader',
	            state: 'primary',
	            message: 'Fetching data...'
	        });

		    var load_drop_points = $.ajax({
	            type: "GET",
	            url:base_url+'/ajax/vehicles/get_my_trips/'+vehicle_id,
	            dataType : "html",
	                success: function(response) {
	                	$('#kt_widget4_driver_trips').html(response);
	                    KTApp.unblock('#kt_widget4_driver_trips');
	                }
	            }
	        );
		}

		function load_driver_vehicle_ongoing_trips(driver_id){
			KTApp.block('#kt_widget4_ongoing_trips',{
	            overlayColor: 'grey',
	            animate: true,
	            type: 'loader',
	            state: 'primary',
	            message: 'Fetching data...'
	        });

		    var load_drop_points = $.ajax({
	            type: "GET",
	            url:base_url+'/ajax/trips/get_driver_active_trips/'+driver_id,
	            dataType : "html",
	                success: function(response) {
	                	$('#kt_widget4_ongoing_trips').html(response);
	                    KTApp.unblock('#kt_widget4_ongoing_trips');
	                }
	            }
	        );
		}

	});

</script>