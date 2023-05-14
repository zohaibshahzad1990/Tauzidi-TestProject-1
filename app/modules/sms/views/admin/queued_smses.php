<div class="row">
	<div class="col-md-12">
        <div class="kt-portlet">
			<div class="kt-portlet__body">
				<div class="portlet-body form">
					<?php if(!empty($posts)){ ?>
						<?php echo form_open('admin/sms/action', ' id="form"  class="form-horizontal"'); ?> 
							
							<table class="table table-condensed table-striped table-hover">
								<thead>
									<tr>
										<th width='2%'>
											<label class="kt-checkbox kt-checkbox-outline">
												<input name='check' type="checkbox" class="check_all"  value="check_all"/>
												<span></span>
											</label>
										</th>
										<th width="20%">
											Send To
										</th>
										<th>
											Phone Number
										</th>
										<th width="25%">
											Message
										</th>
										<th width="20%">
											Sent By
										</th>
										<th width="15%">
											Created On
										</th>
										<th width="10%">
											Actions
										</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach($posts as $post): ?>
										<tr>
											<td>
												<label class="kt-checkbox kt-checkbox-outline">
													<input name='action_to[]' type="checkbox" class="checkboxes"  value="<?php echo $post->id; ?>"/>
													<span></span>
												</label>
											</td>
											<td>
												<?php 
													$user = $this->ion_auth->get_user($post->user_id);
													if($user){
														echo $user->first_name.' '.$user->middle_name.' '.$user->last_name;
													}
												?>
											</td>
											<td>
												<?php echo $post->sms_to;?>
											</td>
											
											<td>
												<?php echo nl2br($post->message);?>
											</td>
											<td>
												<?php $user = $this->ion_auth->get_user($post->created_by);
													if($user){
														echo $user->first_name.' '.$user->middle_name.' '.$user->last_name;
													}
												?>
											</td>
											<td>
												<?php echo timestamp_to_datetime($post->created_on);
												?>
											</td>
											<td>
												<a href="<?php echo site_url('admin/sms/delete/'.$post->id); ?>" class="btn btn-xs btn-danger confirmation_link">
														<i class="fa fa-trash"></i> Delete 
												</a>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>


							<div class="clearfix"></div>
							<?php if($posts):?>
								<button class="btn btn-sm btn-danger confirmation_bulk_action" name='btnAction' value='bulk_delete' data-toggle="confirmation" data-placement="top"> <i class='icon-trash'></i> Bulk Delete</button>
							<?php endif;?>
						<?php echo form_close(); ?>
					<?php }else{ ?>
						<div class="alert alert-info">
							<h4 class="block">No SMS queued to display</h4>
							<!-- <p>
								No SMS queued to display.
							</p> -->
						</div>
					<?php } ?>

				</div>
			</div>
		</div>
	</div>
</div>