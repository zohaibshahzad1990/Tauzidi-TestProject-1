<?php if(empty($posts)){ ?>
<div class="row">
    <div class="col-md-12">
        <!--begin::Portlet-->
        <div class="m-portlet m-portlet--tab">

			<div class="m-portlet__body" style="padding:0px!important;">
				<div class="m-alert m-alert--icon m-alert--icon-solid m-alert--outline alert alert-brand alert-dismissible fade show" role="alert">
					
					<div class="m-alert__text">
						
						There are no Menus created
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
		<table class="table table-sm table-head-bg-brand">
			<thead class="thead-inverse">
				<tr>
					<th>
						#
					</th>
					<th>
						Menu Name
					</th>

					<th>
						URL
					</th>
					<th>
						Parent Menu
					</th>
					<th>
						Menu Icon
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
						<th scope="row"><?php echo $count++."."; ?></th>
						<td><?php echo $post->name; ?></td>
						<td><?php echo $post->url; ?></td>
						<td>
							<?php echo isset($side_bar_menu_options[$post->parent_id])?$side_bar_menu_options[$post->parent_id]:'--'; ?>
						</td>
						<td><i class="<?php echo $post->icon; ?>"></i></td>
						<td>
							<a class="btn  btn-primary btn-sm" href="<?php echo site_url('admin/menus/edit/'.$post->id); ?>">
								<i class='fa fa-edit'></i> Edit
							</a>
							<?php if($post->active){ ?>
								<a class="btn btn-default btn-sm confirm" href="<?php echo site_url('admin/menus/hide/'.$post->id); ?>">
									<i class='fa fa-eye-slash'></i> Hide
								</a>
							<?php }else{ ?>
								<a class="btn  btn-success btn-sm" href="<?php echo site_url('admin/menus/activate/'.$post->id); ?>">
									<i class='fa fa-eye'></i> Display
								</a>
							<?php } ?>
							<a class="btn btn-danger btn-sm confirm" href="<?php echo site_url('admin/menus/delete/'.$post->id); ?>">
								<i class='fa fa-trash'></i> Delete
							</a>
						</td>
					</tr>

					<?php
						$children = $this->menus_m->get_children_links($post->id);
							if(!empty($children)):
							foreach($children as $child):
					?>
							<tr>
								<td>
									<?php echo $count++.'.'; ?>
								</td>
								<td>
									<?php echo $child->name; ?>
								</td>
								<td>
									<?php echo $child->url; ?>
								</td>
								<td>
									<?php echo isset($side_bar_menu_options[$child->parent_id])?$side_bar_menu_options[$child->parent_id]:'--'; ?>
								</td>
								<td>
									<i class="<?php echo $child->icon; ?>"></i>
								</td>
								<td>
										<a class="btn m-btn--pill m-btn--air btn-primary btn-sm" href="<?php echo site_url('admin/menus/edit/'.$child->id); ?>">
											<i class='fa fa-edit'></i> Edit
										</a>

										<?php if($child->active){ ?>
											<a class="btn m-btn--pill m-btn--air btn-default btn-sm confirm" href="<?php echo site_url('admin/menus/hide/'.$child->id); ?>">
												<i class='fa fa-eye-slash'></i> Hide
											</a>
										<?php }else{ ?>
											<a class="btn m-btn--pill m-btn--air btn-success btn-sm" href="<?php echo site_url('admin/menus/activate/'.$child->id); ?>">
												<i class='fa fa-eye'></i> Display
											</a>
										<?php } ?>

										<a class="btn m-btn--pill m-btn--air btn-danger btn-sm confirm" href="<?php echo site_url('admin/menus/delete/'.$child->id); ?>">
											<i class='fa fa-trash'></i> Delete
										</a>
								</td>
							</tr>
					<?php
									$grand_children = $this->menus_m->get_children_links($child->id);
									if(!empty($grand_children)):
										foreach($grand_children as $grand_child):
											?>
												<tr>
													<td>
														<?php echo $count++.'.'; ?>
													</td>
													<td>
														<?php echo $grand_child->name; ?>
													</td>
													<td>
														<?php echo $grand_child->url; ?>
													</td>
													<td>
														<?php echo isset($side_bar_menu_options[$grand_child->parent_id])?$side_bar_menu_options[$grand_child->parent_id]:'--'; ?>
													</td>
													<td>
														<i class="<?php echo $grand_child->icon; ?>"></i>
													</td>
													<td>
														<a class="btn m-btn--pill m-btn--air btn-primary btn-sm" href="<?php echo site_url('admin/menus/edit/'.$grand_child->id); ?>">
															<i class='fa fa-edit'></i> Edit
														</a>
														<?php if($grand_child->active){ ?>
															<a class="btn m-btn--pill m-btn--air btn-default btn-sm confirm" href="<?php echo site_url('admin/menus/hide/'.$grand_child->id); ?>">
																<i class='fa fa-eye-slash'></i> Hide
															</a>
														<?php }else{ ?>
															<a class="btn m-btn--pill m-btn--air btn-success btn-sm" href="<?php echo site_url('admin/menus/activate/'.$grand_child->id); ?>">
																<i class='fa fa-eye'></i> Display
															</a>
														<?php } ?>
														<a class="btn m-btn--pill m-btn--air btn-danger btn-sm confirm" href="<?php echo site_url('admin/menus/delete/'.$grand_child->id); ?>">
															<i class='fa fa-trash'></i> Delete
														</a>
													</td>
												</tr>
											<?php
										endforeach;
									endif;
								endforeach;
							endif;
						endforeach;
				?>
			</tbody>
		</table>
	</div>
</div>
<?php } ?>
