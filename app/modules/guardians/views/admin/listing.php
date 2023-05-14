<div class="kt-portlet">
    <div class="kt-portlet__head">
        <div class="kt-portlet__head-label">
            <h3 class="kt-portlet__head-title">
                {metronic:template:title}
            </h3>
        </div>
    </div>
    <div class="kt-portlet__body">

    	<div class="col-md-12">

			<button class="btn kt-dropdown__toggle btn-sm btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	      		Filter Records
	      	</button>

			<div class="dropdown-menu" style="width:500px!important; margin-top: 10px;">
				<div class="container">

					<div class="m-dropdown__wrapper" style="z-index: 101;">
					   <span class="m-dropdown__arrow m-dropdown__arrow--left"></span>
					   <div class="m-dropdown__inner">
					      <div class="m-dropdown__body">
					         <div class="m-dropdown__content">
					            <form action="<?php echo site_url('/admin/guardians/listing')?>" method="GET" class="filter m-form m-form--label-align-right" accept-charset="utf-8" autocomplete="off">
					               <div class="form-group m-form__group row">
					                  <div class="col-lg-12">
					                     <div class="form-group ">
											<label>Name</label>
											<div class="input-group">
												<div class="input-group-prepend"><span class="input-group-text" id="basic-addon1"><i class="fa fa-book kt-font-brand"></i></span></div>
												<input type="text" name="first_name"  class="form-control" placeholder="Guardian First Name" aria-describedby="basic-addon1">
											</div>
										</div>
					                  </div>
					                  <div class="col-lg-12">
					                     <div class="form-group ">
											<label>Phone</label>
											<div class="input-group">
												<div class="input-group-prepend"><span class="input-group-text" id="basic-addon1"><i class="fa fa-book kt-font-brand"></i></span></div>
												<input type="text"  name="phone" class="form-control" placeholder="Phone Number" aria-describedby="basic-addon1">
											</div>
										</div>
					                  </div>
					               </div>
					               <div class="m-form__actions m--align-right p-0">
					                  <button name="filter" value="filter" type="submit" class="btn btn-primary btn-sm">
					                  <i class="fa fa-filter"></i>
					                  Filter                                            </button>
					                  <button class="btn btn-sm btn-danger close-filter d-none" type="reset">
					                  <i class="fa fa-close"></i>
					                  Reset                                            </button>
					               </div>
					            </form>
					         </div>
					      </div>
					   </div>
					</div>

	            </div>

			</div>

		</div>
		<div class="kt-separator kt-separator--space-lg kt-separator--border-dashed"></div>
		<?php if(empty($posts)){ ?>

				<div class="row">
				    <div class="col-md-12">
				        <!--begin::Portlet-->
				        <div class="kt-portlet kt-portlet--tab">

							<div class="kt-portlet__body" style="padding:0px!important;">
								<div class="kt-alert kt-alert--icon m-alert--icon-solid kt-alert--outline alert alert-brand alert-dismissible fade show" role="alert">
								<!-- 	<div class="kt-alert__icon">
										<i class="flaticon-exclamation-1"></i>
										<span></span>
									</div> -->
									<div class="kt-alert__text">
										<!-- <strong>
											Heads up!
										</strong> -->
										There are no guardians created
									</div>
								</div>
							</div>

				  		</div>
						<!--end::Portlet-->
					</div>
				</div>

			<?php }else{ ?>

		        <div class="kt-pagination kt-pagination--brand ">
		            <?php echo $pagination['links'];  ?>
		            <div class="kt-pagination__toolbar">
		                <span class="pagination__desc">
		                    Displaying <?php echo $pagination['from']; ?> of <?php echo $pagination['total']; ?> records
		                </span>
		            </div>
		        </div>
		        <p></p>
		        <div class="kt-separator  kt-separator--border-dashed" style="margin-top: 2px!important; margin-bottom: 4px!important;">
		        	
		        </div>
		        <table class="table table-sm table-head-bg-brand">
		        	<thead class="thead-inverse">
							<tr>						
								<th>
									#
								</th>
								<th>
									Delegates
								</th>
								<th>
									Parent Name
								</th>
								<th>
									Contact
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
							  $parent_name = "";
							  $phone = ""; 
							  $guardian_name = ""; 
							  $parent_id = "";
							  if(array_key_exists($post->user_id, $guardians)){
							  	$guardian_name = $guardians[$post->user_id]->first_name .' '. $guardians[$post->user_id]->last_name;
							  	$phone = $guardians[$post->user_id]->phone;
							  }
							  if(array_key_exists($post->user_id, $parent_options)){
							  	$parent_id = $parent_options[$post->user_id]->id;
							  	$parent_name = $parent_options[$post->user_id]->first_name .' '. $parent_options[$post->user_id]->last_name;
							  }

							?>
							<tr>
								<td>
									<?php echo $count++.'.'; ?>
								</td>
								<td>
									<?php echo ucwords($guardian_name); ?>
								</td>
								<td>
									<?php echo ucwords($parent_name); ?>
								</td>
								<td>
									<?php echo $phone; ?>
								</td>
								<td>
									<a class="btn m-btn--pill m-btn--air btn-info btn-sm " href="<?php echo site_url('admin/guardians/edit/'.$post->id); ?>">
										<i class='fa fa-users'></i> Edit
									</a>

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

