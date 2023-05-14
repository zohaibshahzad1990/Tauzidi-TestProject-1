<?php if(empty($posts)){  $count = 1; ?>

	<div class="row">
    <div class="col-md-12">
        <!--begin::Portlet-->
        <div class="kt-portlet kt-portlet--tab">

			<div class="kt-portlet__body" style="padding:0px!important;">
				<div class="kt-alert kt-alert--icon m-alert--icon-solid kt-alert--outline alert alert-brand alert-dismissible fade show" role="alert">
					<!-- <div class="kt-alert__icon">
						<i class="flaticon-exclamation-1"></i>
						<span></span>
					</div> -->
					<div class="kt-alert__text">
						<!-- <strong>
							Heads up!
						</strong> -->
						There are no Students 
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
            <?php echo $pagination['links'];  ?>
            <div class="kt-pagination__toolbar">
                <span class="pagination__desc">
                    Displaying <?php echo $pagination['from']; $count = $pagination['from']; ?> of <?php echo $pagination['total']; ?> records
                </span>
            </div>
        </div>

        
        <p></p>

        
        <div class="kt-separator  kt-separator--border-dashed" style="margin-top: 2px!important; margin-bottom: 4px!important;"></div>
        <table class="table table-md table-head-bg-brand">
         <thead class="thead-inverse">
					<tr>						
						<th>
							#
						</th>
						<th>
							Name
						</th>
						<th>
							Registration Number
						</th>
						<th>
							Parent
						</th>
						<th>
							School
						</th>
						<th>
							Vehicle
						</th>
					</tr>
				</thead>
            <tbody>
				<?php
					
					foreach($posts as $post):
						$vehicle = "";
						$school = "";
						$student_id = "";
						$registration = "";
						$parent = "";
						$name = "";
						//print_r($user_students); die();
						if(array_key_exists($post->user_id,$user_students)){
							$student = $user_students[$post->user_id];
							$student_id = $student->id;
							$registration = $student->registration_no;
							$name = $student->first_name . ' ' .$student->last_name;

							if(array_key_exists($student->user_parent_id, $parents)){
								$parent = $parents[$student->user_parent_id];
							}

							if(array_key_exists($student->school_id, $schools)){
								$school = $schools[$student->school_id];
							}
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
							<?php echo $name; ?>
						</td>
						<td>
							<?php echo $registration; ?>
						</td>
						<td>
							<?php echo $parent; ?>
						</td>
						<td>
							<?php echo $school; ?>
						</td>
						<td>
							<?php echo $vehicle; ?>
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
