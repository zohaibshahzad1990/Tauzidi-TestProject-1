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
					            <form action="<?php echo site_url('/admin/parents/listing')?>" method="GET" class="filter m-form m-form--label-align-right" accept-charset="utf-8" autocomplete="off">
					               <div class="form-group m-form__group row">
					                  <div class="col-lg-12">
					                     <div class="form-group ">
											<label>Name</label>
											<div class="input-group">
												<div class="input-group-prepend"><span class="input-group-text" id="basic-addon1"><i class="fa fa-book kt-font-brand"></i></span></div>
												<input type="text" name="first_name"  class="form-control" placeholder="Parent First Name" aria-describedby="basic-addon1">
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
					                  Filter                                            
					              	</button>
					                <button name="reset" class="btn btn-sm btn-danger close-filter" type="submit">
						                <i class="fa fa-close"></i>
						                  Reset                        
						            </button>
					               </div>
					            </form>
					         </div>
					      </div>
					   </div>
					</div>

	            </div>

			</div>

		</div>
	<?php if(empty($posts)){ ?>
		<div class="kt-alert kt-alert--icon m-alert--icon-solid kt-alert--outline alert alert-brand alert-dismissible fade show" role="alert" style="margin-top:10px !important; ">
			<!-- <div class="kt-alert__icon">
				<i class="flaticon-exclamation-1"></i>
				<span></span>
			</div> -->
			<div class="kt-alert__text">
				<!-- <strong>
					Heads up!
				</strong> -->
				There are no Parents created
			</div>
		</div>

	<?php }else{ ?>




		<div class="kt-separator kt-separator--space-lg kt-separator--border-dashed"></div>

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
							Name
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
					foreach($posts as $post):  ?>
					<tr>
						<td>
							<?php echo $count++.'.'; ?>
						</td>
						<td>
							<?php echo ucwords($post->first_name)." ".ucwords($post->last_name); ?>
						</td>
						<td>
							<?php echo $post->phone.' <br/>'.$post->email; ?>
						</td>
						<td>
							<a class="btn m-btn--pill m-btn--air btn-primary btn-sm " href="<?php echo site_url('admin/parents/edit/'.$post->id); ?>">
								<i class='fa fa-edit'></i> Edit
							</a>  &nbsp;

							<a class="btn m-btn--pill m-btn--air btn-info btn-sm " href="<?php echo site_url('admin/students/students/'.$post->id); ?>">
								<i class='fa fa-users'></i> My Students
							</a> &nbsp;

							<a class="btn m-btn--pill m-btn--air btn-info btn-sm " href="<?php echo site_url('admin/guardians/add_guardian/'.$post->id); ?>">
								<i class='fa fa-users'></i> Add Guardian
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
