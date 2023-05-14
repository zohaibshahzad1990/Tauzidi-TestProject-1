<div>
	<div class="m-portlet m-portlet--bordered-semi m-portlet--full-height  m-portlet--rounded-force cust_card">
		<div class="m-portlet__head m-portlet__head--fit">
			<div class="m-portlet__head-caption">
				<div class="m-portlet__head-action">
					<a href="<?php echo site_url("users/users/view/".$user->id); ?>" class="btn btn-sm m-btn--pill m-btn--air btn-info mr-2">Profile</a>
					<a href="<?php echo site_url("users/users/view/".$user->id); ?>" class="btn btn-sm m-btn--pill m-btn--air btn-info mr-2"><?php echo $posts_count; ?> Posts</a>
					<a href="<?php echo site_url("users/users/offers/".$user->id); ?>" class="btn btn-sm m-btn--pill m-btn--air btn-info mr-2"><?php echo $offers_count; ?> Offers</a>
					<button type="button" class="btn btn-sm m-btn--pill m-btn--air btn-brand mr-2"><?php echo $recommendations_count; ?> Recommendations</button>
				</div>
			</div>
		</div>
		<div class="m-portlet__body">
			<div class="m-widget19">
				<div class="m-widget19__pic m-portlet-fit--top m-portlet-fit--sides" style="min-height-: 286px">
					<img src="<?php echo site_url('templates\metronic_v5.3\img\backgrounds\blog3.jpg'); ?>" alt="">
					<h3 class="m-widget19__title m--font-light">
						<?php echo ucwords(strtolower($user->first_name." ".$user->last_name)); ?>
					</h3>
					<div class="m-widget19__shadow"></div>
				</div>
				<div class="m-widget19__content">
					<div class="m-widget19__header">
						<div class="m-widget19__user-img">
							<img class="m-widget19__img" src="
							<?php
								if($user->image_url){
									echo $user->image_url;
								}else{
									echo site_url('uploads/users/default_.jpg');
								}
							?>
							" alt="">
						</div>
						<div class="m-widget19__info">
							<span class="m-widget19__username ">
								<?php echo '<strong>'.$user->first_name." ".$user->last_name.'</strong>'; ?>
								<?php if(valid_phone($user->phone)){ ?>
									<?php echo ", ".$user->phone; ?>
								<?php } ?>
							</span>
							<br>
							<span class="m-widget19__time">
								<?php echo $user->email; ?>
							</span>
						</div>
						<div class="m-widget19__stats" title="Total posts">
							<span class="m-widget19__number m--font-brand">
								<?php echo $posts_count; ?>
							</span>
							<span class="m-widget19__comment">
								<a class="m-link" href="<?php echo site_url("users/users/view/".$user->id); ?>">
									Posts
								</a>
							</span>
						</div>
						<div  class="ml-3 m-widget19__stats">
							<span class="m-widget19__number m--font-brand">
								&nbsp;
							</span>
							<span class="m-widget19__comment">
								&nbsp;
							</span>
						</div>
						<div class="m-widget19__stats" title="Offers made">
							<span class="m-widget19__number m--font-brand">
								<?php echo $offers_count; ?>
							</span>
							<span class="m-widget19__comment">
								<a class="m-link" href="<?php echo site_url("users/users/offers/".$user->id); ?>">
									Offers
								</a>
							</span>
						</div>
						<div  class="ml-3 m-widget19__stats">
							<span class="m-widget19__number m--font-brand">
								&nbsp;
							</span>
							<span class="m-widget19__comment">
								&nbsp;
							</span>
						</div>
						<div class="m-widget19__stats" title="Recommendations made">
							<span class="m-widget19__number m--font-brand">
								<?php echo $recommendations_count; ?>
							</span>
							<span class="m-widget19__comment">
								<a class="m-link" href="<?php echo site_url("users/users/recommendations/".$user->id); ?>">
									Recom..
								</a>
							</span>
						</div>
					</div>
					<div class="m-widget19__body">
						
					</div>
				</div>
				<div class="m-widget19__action">
					<div id="message-button-holder">
						<?php 
							if($this->ion_auth->logged_in()){ 
								if($this->user->id == $user->id){
							?>
							<!--
							<a href="<?php echo site_url('users/users/edit/'.$user->id); ?>" class="btn m-btn--pill btn-secondary m-btn m-btn--hover-info m-btn--custom"><i class="mdi mdi-account-edit icon_m"></i> Update Profile</a>
						-->
						<?php 
								}else{
						?>
							<a href="<?php echo site_url('messages/inbox/'.$user->id); ?>" class="btn m-btn--pill btn-secondary m-btn m-btn--hover-info m-btn--custom"><i class="mdi mdi-email-plus icon_m"></i> Message <?php echo $user->first_name ?></a>
						<?php
								}
							}else{
						?>
							<a href="#" class="open-authentication-modal btn m-btn--pill btn-secondary m-btn m-btn--hover-info m-btn--custom"><i class="mdi mdi-email-plus icon_m"></i> Message <?php echo $user->first_name ?></a>
						<?php
							}
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<h3 class="fancy_title my-3">
		<?php echo $user->first_name." ".$user->last_name; ?> - Recommendations made on posts on <?php echo $this->settings->application_name; ?>
	</h3>

	<div id="posts_holder">
		<!-- loading placeholder -->
		<div class="card cust_card loading_placeholder" style="border:none;margin-bottom:30px;">
			<div class="card-block mb-3">
				<div class="card-body">
					<div class="card-text">
						<div style="width:10%;float:left;">
							<div class="left-round-image content-placeholder" style="width:50px;height:50px;border-radius:100%;">&nbsp;</div>
						</div>
						<div class="pl-4" style="width:90%;float:right;">
							<span class="content-placeholder mb-2" style="width:60%;border-radius:10px;">&nbsp;</span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="content-placeholder mb-1" style="width:15%;border-radius:10px;float:right;">&nbsp;</span>
							<span class="content-placeholder" style="width:30%;border-radius:10px;">&nbsp;</span>&nbsp;&nbsp;<span class="content-placeholder" style="width:10%;border-radius:10px;">&nbsp;</span>
						</div>
						<span class="content-placeholder mt-3" style="width:100%;border-radius:10px;">&nbsp;</span>
						<span class="content-placeholder mt-2" style="width:100%;border-radius:10px;">&nbsp;</span>
						<span class="content-placeholder mt-2" style="width:40%;border-radius:10px;">&nbsp;</span>
						<span class="content-placeholder mt-4 p-3" style="width:100%;border-radius:6px;">&nbsp;</span>
						<span class="content-placeholder mt-4 p-2" style="width:20%;border-radius:100px;">&nbsp;</span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="content-placeholder mt-4 p-2" style="width:20%;border-radius:100px;">&nbsp;</span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="content-placeholder mt-4 p-2" style="width:15%;border-radius:100px;">&nbsp;</span>
					</div>
				</div>
			</div>
		</div>
		<!-- end loading placeholder -->
		
	</div>

</div>
<script>
	$(document).ready(function(){
		//console.log("Am in");
  		$('.modal').on('shown.bs.modal',function(){

  		});
  		$('#buyers_post_input').on('focus',function(){
  			$('#buyers_post_button').trigger('click');
  		});
	});
	$(window).on('load',function(){
		var first_uri_segment = "<?php echo $this->uri->segment(4, 0); ?>";
		var second_uri_segment = "<?php echo $this->uri->segment(5, 0); ?>";
		var get_string = "<?php echo $_SERVER['QUERY_STRING']; ?>";
		var user_id = "<?php echo $user->id; ?>";
    	load_users_posts_with_recommendations_made(user_id,first_uri_segment,second_uri_segment,get_string);
	});
</script>