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
						There are no Trips created
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
							Trip Details
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

						$start_point = "";
						$end_point = "";
						$distance = "";
						$eta = "";
						if(array_key_exists($post->route_id, $routes)){
							$start_point = $routes[$post->route_id]->start_point;
							$end_point = $routes[$post->route_id]->end_point;
							$distance = $routes[$post->route_id]->distance;
							$eta = $routes[$post->route_id]->duration;
						}
				?>
					<tr>
						<td>
							<?php echo $count++.'.'; ?>
						</td>
						<td>
							<?php echo $post->name; ?>
						</td>
						<td>
							<?php echo '<strong>'. $start_point.' </strong> - <strong>'.$end_point .' </strong><br/> Distance <strong>'  .$distance .' </strong><br/> Duration <strong>'  .$eta .' </strong>'; ?>
						</td>
						<td>
							<a class="btn m-btn--pill m-btn--air btn-primary btn-sm " href="<?php echo site_url('admin/trips/edit/'.$post->id); ?>">
								<i class='fa fa-edit'></i> Edit
							</a>
							<?php if($post->active){														
								echo '<a class="btn m-btn--pill m-btn--air btn-danger btn-sm " href="'.site_url('admin/trips/disable/'.$post->id).'"><i class="la la-ban"></i> Disable </a>';
							}else{

								echo '<a class="btn m-btn--pill m-btn--air btn-success btn-sm" href="'.site_url('admin/trips/activate/'.$post->id).'"><i class="la la-edit"></i> Enable </a>';
							}?>	
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
