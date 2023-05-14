<?php if(empty($posts)){ ?>

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
						There are no Students created
					</div>
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
							Registarion
						</th>
						<th>
							Capacity
						</th>
						<th>
							Type
						</th>
						<th>
							Driver
						</th>
						<th>
							Status
						</th>
						<th>
							Actions
						</th>
					</tr>
				</thead>
            <tbody>
				<?php
					$count = 1;
					foreach($posts as $post):
						$is_validated = $post->active;
						$class = "admin";
						if($this->router->fetch_class() == "manager"){
							$class = "manager";
						}
						//print_r($this->router->fetch_class());
						$driver = "No Driver";
						if(array_key_exists($post->id, $vehicles)){
							$driver = $vehicles[$post->id]->first_name. ' '. $vehicles[$post->id]->last_name;
						}	
				?>
					<tr>
						<td>
							<?php echo $count++.'.'; ?>
						</td>
						<td>
							<?php echo $post->registration; ?>
						</td>
						<td>
							<?php echo $post->capacity; ?>
						</td>
						<td>
							<?php echo $post->type; ?>
						</td>
						<td>
							<?php echo ucfirst($driver);?>
						</td>
						<td>
                            <?php 
                                if($post->active){
                                    echo "<span class='kt-badge kt-badge--success kt-badge--inline'>&nbsp;Active&nbsp;</span>";
                                }else{
                                    echo "<span class='kt-badge kt-badge--danger kt-badge--inline'>&nbsp;Inactive&nbsp;</span>";
                                }
                            ?>
                        </td>
						<td>
							<a class="btn m-btn--pill m-btn--air btn-success btn-sm " href="<?php echo site_url($class.'/vehicles/edit/'.$post->id); ?>">
								<i class='fa fa-edit'></i> Edit
							</a>
							<a class="btn m-btn--pill m-btn--air btn-info btn-sm " href="<?php echo site_url($class.'/vehicles/trips/'.$post->id); ?>">
								<i class='flaticon-placeholder-1'></i> Trips
							</a>
							<a class="btn m-btn--pill m-btn--air btn-info btn-sm " href="<?php echo site_url($class.'/students/student_options?vehicle_id='.$post->id.''); ?>">
								<i class='fa fa-users'></i> Students
							</a>
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
