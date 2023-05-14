<?php if(empty($posts)){ $count = 1;  ?>

	<div class="row">
    <div class="col-md-12">
        <!--begin::Portlet-->
        <div class="m-portlet m-portlet--tab">

			<div class="m-portlet__body" style="padding:0px!important;">
				<div class="m-alert m-alert--icon m-alert--icon-solid m-alert--outline alert alert-brand alert-dismissible fade show" role="alert">
					<!-- <div class="m-alert__icon">
						<i class="flaticon-exclamation-1"></i>
						<span></span>
					</div> -->
					<div class="m-alert__text">
						<!-- <strong>
							Heads up!
						</strong> -->
						There are no {metronic:template:title}
					</div>
					
				<!--
					<div class="m-alert__close">
						<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
					</div>
				-->
				</div>
			</div>

  		</div>
		<!--end::Portlet-->
	</div>
</div>

<?php }else{ ?>


	<div class="kt-portlet">
    <div class="kt-portlet__head">
        <div class="kt-portlet__head-label">
            <h3 class="kt-portlet__head-title">
                {metronic:template:title}
            </h3>
        </div>
    </div>
    <div class="kt-portlet__body">
        <div class="kt-pagination kt-pagination--brand ">
            <?php  echo $pagination['links'];  
            	$count = $pagination['from'];
             ?>
            <div class="kt-pagination__toolbar">
                <span class="pagination__desc">
                    Displaying <?php echo $pagination['from']; ?> of <?php echo $pagination['total']; ?> records
                </span>
            </div>
        </div>
        <p></p>
        <div class="kt-separator  kt-separator--border-dashed" style="margin-top: 2px!important; margin-bottom: 4px!important;"></div>
        <table class="table table-sm table-head-bg-brand">
         <thead class="thead-inverse">
					<tr>						
						<th>
							#
						</th>
						<th>
							Driver
						</th>
						<th>
							Trip Name
						</th>
						<th>
							Vehicle
						</th>

						<th>
							Distance
						</th>
						<th>
							Start Time
						</th>
						<th>
							End Time
						</th>
						<th>
							Actions
						</th>
					</tr>
				</thead>
            <tbody>
				<?php
					foreach($posts as $post):
						//print_r($post); die();
						$trip_name = "";
						$driver = "";
						$vehicle = "";
						if(array_key_exists($post->trip_id, $trips)){
							$trip_name = $trips[$post->trip_id]->name;
						}
						if(array_key_exists($post->driver_id, $drivers)){
							$driver = $drivers[$post->driver_id]->first_name . ' '.$drivers[$post->driver_id]->last_name ;
						}

						if(array_key_exists($post->vehicle_id, $vehicles)){
							$vehicle = $vehicles[$post->vehicle_id];
						}
				?>
					<tr>
						<td>
							<?php echo $count++.'.'; ?>
						</td>
						<td>
							<?php echo $driver; ?>
						</td>
						<td>
							<?php echo $trip_name; ?>
						</td>
						<td>
							<?php echo $vehicle; ?>
						</td>
						<td>

							<?php echo str_replace('mi','Kilometers',$post->distance ); ?>
						</td>
						
						<?php 
						if($this->router->fetch_method() == 'completed_trips'){ ?>
							<td>
								<?php echo timestamp_to_date_and_time($post->start_time) ; ?>
							</td>
							<td>
								<?php echo timestamp_to_date_and_time($post->modified_on) ; ?>
							</td>
						<?php }else{ ?>
							<td>
								<?php echo timestamp_to_date_and_time($post->start_time) ;?>
							</td>
							<td>
								
							</td>
						<?php }?>
						<td>
							<?php 
								if($this->router->fetch_method() == 'ongoing_trips'){
									echo "<button class='btn m-btn--pill m-btn--air btn-danger btn-sm prompt_confirmation_message_link' id='".$post->id."'><i class='fa fa-map'></i>End Journey</span></button>&nbsp;&nbsp;";
								}
                                echo "&nbsp;&nbsp<a class='btn m-btn--pill m-btn--air btn-success btn-sm ' href='".site_url('admin/trips/history/'.$post->id)."'><i class='fa fa-map'></i>Track</span></a>&nbsp;&nbsp;";
                            ?>	
						</td>
					</tr>
				<?php
					endforeach;
				?>
			</tbody>
        </table>
    </div>
</div>
<?php } ?>

<script type="text/javascript">
	var base_url = window.origin;


	$(document).on('click','.prompt_confirmation_message_link',function(){
        var id = $(this).attr('id');
        swal.fire({
            title: "Are you sure?", text: "You won't be able to revert this!", type: "warning", showCancelButton: !0, confirmButtonText: "Yes, End Journey !", cancelButtonText: "No, cancel!", reverseButtons: !0
        }).then(function(e) {
            if(e.value == true){

            	//window.location.href = base_url+'/ajax/trips/end_trip/'+id;
            	$.ajax({
	                type: "POST",
	                url: base_url+'/ajax/trips/end_trip',
	                data: {id:id},
	                success: function(response) {
	                    var data = $.parseJSON(JSON.stringify(response));
	                    if(data.status == 200){
	                        toastr['success']('You have successfully ended a journey.','Journey Ended');
	                        var delay = 3000;
							var urlD = base_url+'/admin/drivers/completed_trips';

							var timeoutID = setTimeout(function() {
							    window.location.href = urlD;
							}, delay);

                        }else{
	                    	swal.fire("Cancelled", "Journey still in progress :)", "error") 
	                    }
	                    //KTApp.unblock('.modal-body', {});
	                }
	            });
            }else{
                swal.fire("Cancelled", "Journey still in progress :)", "error")
            }
        })
    });

</script>
