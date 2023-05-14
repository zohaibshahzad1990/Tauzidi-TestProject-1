<?php if(empty($posts)){ ?>

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
							&nbsp;&nbsp; Heads up! &nbsp;&nbsp;
						</strong> -->
						There are No Transport Managers created
					</div>
					<div class="m-alert__close">
						<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
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
							Contact
						</th>
						<th>
							National Id
						</th>
						<th>
							User Groups
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
						$is_validated = $post->is_validated;
				?>
					<tr>
						<td>
							<?php echo $count++.'.'; ?>
						</td>
						<td>
							<?php echo $post->first_name." ".$post->middle_name." ".$post->last_name; ?>
						</td>
						<td>
							<?php echo $post->phone.' <br/>'.$post->email; ?>
						</td>
						<td>
							<?php echo $post->id_number; ?>
						</td>
						<td>
							<?php
								$user_groups = $this->ion_auth->get_user_groups($post->id);
								foreach ($user_groups as $key=>$user_group) {
									if (isset($groups[$user_group])) {
										echo '<span class="kt-badge kt-badge--primary  kt-badge--inline kt-badge--pill">'. $groups[$user_group]   .'</span>';
										echo '&nbsp;&nbsp;';
									}
									
								}
							?>
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
							<a class="btn m-btn--pill m-btn--air btn-primary btn-sm " href="<?php echo site_url('admin/transport/edit/'.$post->id); ?>">
								<i class='fa fa-edit'></i> Edit
							</a>
							<?php if($post->active){														
								echo '<a class="btn m-btn--pill m-btn--air btn-danger btn-sm " href="'.site_url('admin/transport/disable/'.$post->id).'"><i class="la la-ban"></i> Disable </a>';
							}else{

								echo '<a class="btn m-btn--pill m-btn--air btn-success btn-sm" href="'.site_url('admin/transport/activate/'.$post->id).'"><i class="la la-edit"></i> Enable </a>';
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
