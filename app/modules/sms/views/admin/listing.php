<?php if(empty($posts)){ ?>
	<div class="row">
	    <div class="col-md-12">
			<div class="m-alert m-alert--icon m-alert--icon-solid m-alert--outline alert alert-brand alert-dismissible fade show" role="alert">
				<!-- <div class="m-alert__icon">
					<i class="flaticon-exclamation-1"></i>
					<span></span>
				</div> -->
				<div class="m-alert__text">
					<!-- <strong>
						Heads up!
					</strong> -->
					There are no SMSes
				</div>
			</div>
		</div>
	</div>

<?php }else{ ?>
	<div class="row">
	    <div class="col-md-12">
	        <div class="kt-portlet kt-portlet--tab">
				<div class="kt-portlet__body">
					<div class="kt-accordion kt-accordion--bordered" id="kt_accordion_2" role="tablist">
						<?php 
							$count = $pagination['from']; 
							foreach($posts as $post): 
						?>
							<div class="kt-accordion__item">
								<div class="kt-accordion__item-head collapsed" role="tab" id="kt_accordion_2_item_<?php echo $count + 1; ?>_head" data-toggle="collapse" href="#kt_accordion_2_item_<?php echo $count + 1; ?>_body" aria-expanded="false">
									<span style="font-weight:700;margin-right:20px;"><?php echo $count;  ?>.&nbsp;</span>
									<span class="kt-accordion__item-icon">
										<i class="la la-money"></i>
									</span>
									<span class="kt-accordion__item-title">
										<span style="font-size:16px;color:#333;">
											<?php 
												$user = $this->ion_auth->get_user($post->user_id);
												if($user){
													echo $user->first_name.' '.$user->middle_name.' '.$user->last_name;
												}else{
													echo $post->sms_to;
												}
											?>
										</span>
										&nbsp;
										
										<span class="m-badge m-badge--metal m-badge--wide m-badge--rounded" style="font-size:12px;margin-right:20px;">
											Phone <strong><?php echo $post->sms_to; ?></strong>
										</span>
										<?php if($post->sms_result_id){ ?>
											<span class="m-badge m-badge--success m-badge--wide" style="float:right;margin-right:20px;">
												Sent
											</span>
										<?php }else{ ?>
											<span class="m-badge m-badge--danger m-badge--wide" style="float:right;margin-right:20px;">
												Sending Failed
											</span>
										<?php } ?>
										
									</span>
									<span class="kt-accordion__item-mode"></span>
								</div>
								<div class="kt-accordion__item-body collapse" id="kt_accordion_2_item_<?php echo $count + 1; ?>_body" role="tabpanel" aria-labelledby="kt_accordion_2_item_<?php echo $count + 1; ?>_head" data-parent="#kt_accordion_2" style="">
									<div class="kt-accordion__item-content">
										<div class="kt-widget6">
											<div class="kt-widget6__body">
												<div class="kt-widget6__item">
													<span class="kt-widget6__text">
														Message:
													</span>
													<span class="kt-widget6__text m--align-right m--font-boldest">
														<?php 
															echo nl2br($post->message); 
														?>
													</span>
												</div>
												<div class="kt-widget6__item">
													<span class="kt-widget6__text">
														Sent On:
													</span>
													<span class="kt-widget6__text m--align-right m--font-boldest">
														<?php 
															echo timestamp_to_datetime($post->created_on); 
														?>
													</span>
												</div>
												<div class="kt-widget6__foot">
														<!--
															<div class="kt-widget6__action m--align-right">
																<a href="<?php echo site_url("admin/loans/statement/".$post->loan_id); ?>" class="btn btn-accent btn-md m-btn  m-btn m-btn--icon">
																	<span>
																		<i class="la la-file-text"></i>
																		<span>View Loan Statement &nbsp;</span>
																	</span>
																</a>
															</div>
														-->
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						<?php 
							$count++; 
							endforeach; 
						?>
						<?php if ( ! empty($pagination['links'])): ?>
					        <p>&nbsp;</p>
					        <div class="row">
					            <div class="col-md-6">
					                <p class="paging" style="margin-top:6px;">Showing <span class="greyishBtn m--font-boldest"><?php echo $pagination['from']; ?></span> to <span class="greyishBtn m--font-boldest"><?php echo $pagination['to']; ?></span> of <span class="greyishBtn"><?php echo $pagination['total']; ?></span> SMSes</p>
					            </div>
					            <div class="col-md-6">
					                <?php
					                    echo '<div class ="pagination" style="float:right;">';
					                    echo $pagination['links']; 
					                    echo '</div>';
					                ?>
					            </div>
					        </div>
				        <?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php } ?>