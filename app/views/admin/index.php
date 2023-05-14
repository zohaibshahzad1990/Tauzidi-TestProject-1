<div class="kt-portlet">
	<div class="kt-portlet__body  kt-portlet__body--fit">
		<div class="row row-no-padding row-col-separator-xl">
			<div class="col-md-12 col-lg-6 col-xl-3">

				<!--begin::Total Profit-->
				<div class="kt-widget24">
					<div class="kt-widget24__details">
						<div class="kt-widget24__info">
							<h4 class="kt-widget24__title">
							<a href="<?php  echo base_url('admin/vehicles/listing') ?>">Vehicles</a>
							</h4>
							<span class="kt-widget24__desc">
							Total Vehicles
							</span>
						</div>
						<span class="kt-widget24__stats kt-font-brand">
							<?php echo $vehicles; ?>
						</span>
					</div>
					<div class="progress progress--sm">
						<div class="progress-bar kt-bg-brand" role="progressbar" style="width: 78%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
					</div>
				</div>

				<!--end::Total Profit-->
			</div>
			<div class="col-md-12 col-lg-6 col-xl-3">

				<!--begin::New Feedbacks-->
				<div class="kt-widget24">
					<div class="kt-widget24__details">
						<div class="kt-widget24__info">
							<h4 class="kt-widget24__title">
								
							<a href="<?php  echo base_url('admin/incidents/listing') ?>">New Incidents</a>

							</h4>
							<span class="kt-widget24__desc">
								 Unread Incidents

							</span>
						</div>
						<span class="kt-widget24__stats kt-font-warning">
							<?php echo $incidents; ?>
						</span>
					</div>
					<div class="progress progress--sm">
						<div class="progress-bar kt-bg-warning" role="progressbar" style="width: 84%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
					</div>
				</div>

				<!--end::New Feedbacks-->
			</div>
			<div class="col-md-12 col-lg-6 col-xl-3">

				<!--begin::New Orders-->
				<div class="kt-widget24">
					<div class="kt-widget24__details">
						<div class="kt-widget24__info">
							<h4 class="kt-widget24__title">
								
							<a href="<?php  echo base_url('admin/trips/listing') ?>">Total  Trips</a>

							</h4>
							<span class="kt-widget24__desc">
								Trips Created
							</span>
						</div>
						<span class="kt-widget24__stats kt-font-danger">
							<?php echo $trips; ?>
						</span>
					</div>
					<div class="progress progress--sm">
						<div class="progress-bar kt-bg-danger" role="progressbar" style="width: 69%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
					</div>
				</div>

				<!--end::New Orders-->
			</div>
			<div class="col-md-12 col-lg-6 col-xl-3">

				<!--begin::New Users-->
				<div class="kt-widget24">
					<div class="kt-widget24__details">
						<div class="kt-widget24__info">
							<h4 class="kt-widget24__title">
								
							<a href="">Total Users</a>

							</h4>
							<span class="kt-widget24__desc">
								Total System Users
							</span>
						</div>
						<span class="kt-widget24__stats kt-font-success">
							<?php echo $users ; ?>
						</span>
					</div>
					<div class="progress progress--sm">
						<div class="progress-bar kt-bg-success" role="progressbar" style="width: 90%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
					</div>
				</div>

				<!--end::New Users-->
			</div>
		</div>
	</div>
</div>

<div class="row">
	
	<div class="col-xl-4 col-lg-6 order-lg-3 order-xl-1">

		<!--begin:: Widgets/New Users-->
		<div class="kt-portlet kt-portlet--tabs kt-portlet--height-fluid">
			<div class="kt-portlet__head">
				<div class="kt-portlet__head-label">
					<h3 class="kt-portlet__head-title">
						New Users
					</h3>
				</div>
				<div class="kt-portlet__head-toolbar">
					<ul class="nav nav-tabs nav-tabs-line nav-tabs-bold nav-tabs-line-brand" role="tablist">
						<li class="nav-item">
							<a class="nav-link active" data-toggle="tab" href="#kt_widget4_tab1_content" role="tab">
								Days
							</a>
						</li>
					</ul>
				</div>
			</div>
			<div class="kt-portlet__body">
				<div class="tab-content">
					<div class="tab-pane active" id="kt_widget4_tab1_content">
						<div class="kt-widget4">
							<?php 
								foreach ($new_users as $key => $user) { ?>
									<div class="kt-widget4__item">
										<div class="kt-widget4__info">
											<a  class="kt-widget4__username">
												<?php echo $user->first_name . ' '. $user->last_name; ?>
											</a>
										</div>
										<a class="btn btn-sm btn-label-brand btn-bold"><?php echo daysAgo($user->created_on); ?></a>
									</div>
								<?php }
							?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!--end:: Widgets/New Users-->
	</div>

	
	<div class="col-lg-6 col-xl-4 order-lg-1 order-xl-1">

		<!--Begin::Portlet-->
		<div class="kt-portlet kt-portlet--height-fluid">
			<div class="kt-portlet__head">
				<div class="kt-portlet__head-label">
					<h3 class="kt-portlet__head-title">
						Vehicles
					</h3>
				</div>
			</div>
			<div class="kt-portlet__body">

				<!--Begin::Timeline 3 -->
				<div class="kt-timeline">
					<div class="kt-timeline-v2__items  kt-padding-top-25 kt-padding-bottom-30">
						<div class="kt-widget4">
							<?php 
								foreach ($new_vehicles as $key => $vehicle) { ?>
									<div class="kt-widget4__item">
										<div class="kt-widget4__info">
											<a  class="kt-widget4__username">
												<?php echo $vehicle->registration  ?>
											</a>
										</div>
										<a class="btn btn-sm btn-label-brand btn-bold"><?php echo daysAgo($vehicle->created_on); ?></a>
									</div>
								<?php }
							?>
						</div>
					</div>
				</div>

				<!--End::Timeline 3 -->
			</div>
		</div>

		<!--End::Portlet-->
	</div>

	<div class="col-xl-4 col-lg-4 order-lg-2 order-xl-1">

		<!--begin:: Widgets/Revenue Change-->
		<div class="kt-portlet kt-portlet--height-fluid">
			<div class="kt-widget14">
				<div class="kt-widget14__header">
					<h3 class="kt-widget14__title">
						Distance Breakdown
					</h3>
					<span class="kt-widget14__desc">
						Distance change breakdown between vehicles
					</span>
				</div>
				<div class="kt-widget14__content">
					<div class="kt-widget14__chart">
						<div id="kt_chart_revenue_change" style="height: 150px; width: 150px;"></div>
					</div>
					<div class="kt-widget14__legends" id="number_charts">
						
					</div>
				</div>
			</div>
		</div>

		<!--end:: Widgets/Revenue Change-->
	</div>
</div>

<script type="text/javascript">
	var base_url = window.origin;
	$(document).ready(function(){


		$( window ).on("load", function() {
		   load_stats();
		});

		function load_stats(){
			KTApp.block('#number_charts',{
	            overlayColor: 'grey',
	            animate: true,
	            type: 'loader',
	            state: 'primary',
	            message: 'Fetching data...'
	        });
            $.ajax({
                type: "POST",
                url: base_url+'/ajax/trips/get_dashboard_stats',
                dataType: "json",
                success: function(data){
                    if(isJson(data)){
                        if(data.status == 200){
                            //console.log(data.data)
                            var response = data.data;
                            $('#number_charts').html('');
                            let one ="";
                            let two = "";
                            var three ='';
                            var html ='';
                            $.each(response, function (key, value) {
                            	if(key == 0){
                            		one =value.count +' '+ value.vehicle ;
                            		console.log(one)
                            		html +='<div class="kt-widget14__legend"><span class="kt-widget14__bullet kt-bg-success"></span><span class="kt-widget14__stats">'+String(one)+'</span></div>'
                            	}
                            	if(key == 1){
                            		two =value.count +' '+ value.vehicle ;
                            		html +='<div class="kt-widget14__legend"><span class="kt-widget14__bullet kt-bg-warning"></span><span class="kt-widget14__stats">'+two+'</span></div>'
                            	}
                            	if(key == 2){
                            		three =value.count +' '+ value.vehicle ;
                            		html+='<div class="kt-widget14__legend"><span class="kt-widget14__bullet kt-bg-brand"></span><span class="kt-widget14__stats">'+three+'</span></div>'
                            	}
                            })

							$('#number_charts').html(html);
                        }                    
                    }else{
                    }
                },
                failure: function(errMsg) {
                    alert(errMsg);
                },complete: function() {
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