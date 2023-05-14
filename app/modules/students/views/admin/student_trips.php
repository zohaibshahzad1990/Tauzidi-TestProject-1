<div class="kt-portlet">
    <div class="kt-portlet__head">
        <div class="kt-portlet__head-label">
            <h3 class="kt-portlet__head-title">
                {metronic:template:title}
            </h3>
        </div>
    </div>
    <div class="kt-portlet__body">

	<?php if(empty($posts)){  $count = 1; ?>
		<div class="kt-alert kt-alert--icon m-alert--icon-solid kt-alert--outline alert alert-brand alert-dismissible fade show" role="alert" style="margin-top: 10px !important;">
			<!-- <div class="kt-alert__icon">
				<i class="flaticon-exclamation-1"></i>
				<span></span>
			</div> -->
			<div class="kt-alert__text">
				<!-- <strong>
					Heads up!
				</strong> -->
				There are no trips assigned
			</div>
			<!--
				<div class="m-alert__close">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
				</div>
			-->
		</div>
	<?php }else{ ?>

        <div class="kt-separator  kt-separator--border-dashed" style="margin-top: 2px!important; margin-bottom: 4px!important;"></div>
        <table class="table table-sm table-head-bg-brand">
         <thead class="thead-inverse">
					<tr>						
						<th>
							#
						</th>
						<th>
							Trip
						</th>
						<th>
							Vehicle
						</th>
						<th>
							Onboarded On
						</th>
						<th>
							Modified On
						</th>
					</tr>
				</thead>
            <tbody>
				<?php
					$count = 1;
					foreach($posts as $post):
						$vehicle = "";
						$school = "";
						$trip ="";
						$student_id = "";
						$registration = "";
						$parent = "";
						if(array_key_exists($post->trip_id,$trip_details)){
							$trip = $trip_details[$post->trip_id];
						}
						if(array_key_exists($post->vehicle_id,$vehicles)){
							$vehicle = $vehicles[$post->vehicle_id];
						}
				?>
					<tr>
						<td>
							<?php echo $count++.'.'; ?>
						</td>
						<td>
							<?php echo ucwords($trip); ?>
						</td>
						<td>
							<?php echo $vehicle; ?>
						</td>
						<td>
							<?php echo timestamp_to_date_and_time($post->create_on); ?>
						</td>
						<td>
							<?php echo timestamp_to_date_and_time($post->modified_on); ?>
						</td>
					</tr>
				<?php
					endforeach;
				?>
			</tbody>
        </table>
	<?php } ?>
    </div>
</div>
