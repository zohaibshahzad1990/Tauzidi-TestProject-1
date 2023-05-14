<div class="m-login__wrapper-1 m-portlet-full-height">
					<div class="m-login__wrapper-1-1">
						<div class="m-login__contanier">
							<div class="m-login__content">
								<div class="m-login__logo">
									<a href="#">
										<img src="<?php echo $this->settings?site_url('uploads/logos/'.$this->settings->logo):site_url("/templates/metronic_v5.1.1/img/logo.png"); ?>">
									</a>
								</div>
								<div class="m-login__title" style="text-align:left;">
									<h3>
										Welcome to <?php echo $this->settings->application_name; ?>
									</h3>
								</div>
								<div class="m-login__desc">
								Convenient and affordable loans. Straight to your mobile phone.
								</div>



								<div class="m-login__form-action">
									<button type="button" class="btn btn-outline-focus m-btn--pill" onclick="location.href='login';">
										Log in
									</button>
								</div>
							</div>
						</div>
						<div class="m-login__border">
							<div></div>
						</div>
					</div>
				</div>
				<div class="m-login__wrapper-2 m-portlet-full-height">
					<div class="m-login__contanier">
						<div class="m-login__forget-passwords">
							<div class="m-login__head">
								<h3 class="m-login__title">
									Two-step Verification
								</h3>
								<div class="m-login__desc">
									Enter code sent via SMS sent to <?php echo $phone; ?> below to access your account:
								</div>
							</div>
							<?php if($this->session->flashdata('warning')){?>
              <div id="error-message-holder" class="m-portlet__body">
                <div class="m-alert m-alert--outline alert alert-warning alert-dismissible fade show" role="alert">
    							<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
    							<strong>
    								Warning!
    							</strong>
    							<?php echo strip_tags($this->session->flashdata('warning')); ?>
    						</div>
              </div>
            <?php } ?>

            <?php if($this->session->flashdata('success')){?>
              <div id="error-message-holder" class="m-portlet__body">
                <div class="m-alert m-alert--outline alert alert-success alert-dismissible fade show" role="alert">
    							<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
    							<strong>
    								Well done!
    							</strong>
    							<?php echo strip_tags($this->session->flashdata('success')); ?>
    						</div>
              </div>
            <?php } ?>

            <?php if($this->session->flashdata('info')){?>
              <div id="error-message-holder" class="m-portlet__body">
                <div class="m-alert m-alert--outline alert alert-accent alert-dismissible fade show" role="alert">
    							<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
    						<!-- 	<strong>
    								Heads up!
    							</strong> -->
    							<?php echo strip_tags($this->session->flashdata('info')); ?>
    						</div>
              </div>
            <?php } ?>

            <?php if($this->session->flashdata('message')){?>
              <div id="error-message-holder" class="m-portlet__body">
                <div class="m-alert m-alert--outline alert alert-accent alert-dismissible fade show" role="alert">
    							<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
    						<!-- 	<strong>
    								Heads up!
    							</strong> -->
    							<?php echo strip_tags($this->session->flashdata('message')); ?>
    						</div>
              </div>
            <?php } ?>

            <?php if($this->session->flashdata('error')){?>

							<div class="m-alert m-alert--icon m-alert--icon-solid m-alert--outline alert alert-danger alert-dismissible fade show" role="alert">
								<div class="m-alert__icon">
									<i class="flaticon-exclamation-1"></i>
									<span></span>
								</div>
								<div class="m-alert__text">
									<strong>
										Uh oh!
									</strong>
									<?php echo ($this->session->flashdata('error')); ?>
								</div>
								<div class="m-alert__close">
									<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
								</div>
							</div>

            <?php }?>

            <?php if(validation_errors()){?>
              <div class="m-alert m-alert--icon m-alert--icon-solid m-alert--outline alert alert-danger alert-dismissible fade show" role="alert">
								<div class="m-alert__icon">
									<i class="flaticon-exclamation-1"></i>
									<span></span>
								</div>
								<div class="m-alert__text">
									<strong>
										Uh oh!
									</strong>
									<?php echo (validation_errors()); ?>
								</div>
								<div class="m-alert__close">
									<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
								</div>
							</div>
            <?php } ?>
							<?php echo form_open(current_url(),'class="m-login__form m-form" id="otp_login-form"'); ?>
								<div class="form-group m-form__group">
									<?php echo form_input('otp_code','','class="form-control m-input" placeholder="Verification Code" autocomplete="off"'); ?>
								</div>
								<div class="row m-login__form-sub">

									<div class="col m--align-right">
										<a href="resend_verification_code" class="m-link">
											Resend Verification Code
										</a>
									</div>
								</div>
								<div class="m-login__form-action">
									<button class="btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air">
										Verify
									</button>

								</div>
						<?php echo form_close(); ?>
						</div>
					</div>
				</div>
<script>
	$(document).ready(function(){
		var form = $('#otp_login-form');
		form.validate({
			errorElement: 'div', //default input error message container
            errorClass: 'form-control-feedback m--font-danger', // default input error message class
            focusInvalid: true, // do not focus the last invalid input
            ignore: "", // validate all fields including form hidden input
            messages: {
            	otp_code : {
            		required : "Kindly enter the code sent to you"
            	},
            },
            rules:{
            	otp_code: {
            		required : true,
            	},      	
            },

		});
	});
</script>