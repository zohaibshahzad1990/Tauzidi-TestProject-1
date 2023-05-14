<?php if(empty($journies)){ ?>

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
					<!-- 	<strong>
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
							Trip Name
						</th>
						<th>
							Duration
						</th>
						<th>
							Distance
						</th>
						<th>
							Actions
						</th>
					</tr>
				</thead>
            <tbody>
				<?php
					$count = 1;
					foreach($journies as $post):
						$name = "";
						$duration = "";
						$distance = "";
						$name = "";
						if(array_key_exists($post->route_id, $routes)){
							$name = $routes[$post->route_id]->name;
							$duration = $routes[$post->route_id]->duration;
							$distance = $routes[$post->route_id]->distance;
						}else{
							
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
							<?php echo $duration; ?>
						</td>
						<td>
							<?php echo $distance; ?>
						</td>
						<td>
							<?php 
                                    echo "<a class='btn m-btn--pill m-btn--air btn-primary btn-sm ' href='".site_url('admin/trips/history/'.$post->id)."'><i class='fa fa-edit'></i>View History</span>&nbsp;&nbsp;";
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
