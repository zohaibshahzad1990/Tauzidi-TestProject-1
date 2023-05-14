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
							Name
						</th>
						<th>
							Start Point
						</th>
						<th>
							Destination
						</th>
						<th>
							Distance(M)
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
				?>
					<tr>
						<td>
							<?php echo $count++.'.'; ?>
						</td>
						<td>
							<?php echo $post->name;?>
						</td>
						<td>
							<?php echo $post->start_point; ?>
						</td>
						<td>
							<?php echo $post->end_point; ?>
						</td>
						<td>
							<?php echo $post->distance; ?>
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
							<a class="btn m-btn--pill m-btn--air btn-primary btn-sm " href="<?php echo site_url('admin/routes/edit/'.$post->id); ?>">
								<i class='fa fa-edit'></i> Edit
							</a>
							<?php 														
								echo '<a class="btn m-btn--pill m-btn--air btn-info btn-sm " href="'.site_url('admin/routes/points/'.$post->id).'"><i class="la la-map"></i> Drop Points </a>';
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
