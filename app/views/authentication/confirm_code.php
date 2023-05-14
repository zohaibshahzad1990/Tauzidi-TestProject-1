<div class="kt-login__wrapper-1 m-portlet-full-height">
	<div class="kt-login__wrapper-2 m-portlet-full-height">
		<div class="kt-login__contanier">
			<div class="kt-login__forget-passwords">
				<div class="kt-login__head" style="text-align: left!important;">
					<h3 class="m-login__title">
						Reset Password Code
					</h3>
					<div class="kt-login__desc" style="margin-bottom: 20px;">
						Enter code sent via SMS below to reset your password:
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
							<!-- <strong>
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
							<!-- <strong>
								Heads up!
							</strong> -->
							<?php echo strip_tags($this->session->flashdata('message')); ?>
						</div>
					</div>
				<?php } ?>

				<?php if($this->session->flashdata('error')){ ?>
					<div class="alert alert-outline-danger fade show" role="alert">
						<div class="alert-icon"><i class="flaticon-questions-circular-button"></i></div>
						<div class="alert-text"><?php echo ($this->session->flashdata('error')); ?></div>
						<div class="alert-close">
							<button type="button" class="close" data-dismiss="alert" aria-label="Close">
								<span aria-hidden="true"><i class="la la-close"></i></span>
							</button>
						</div>
					</div>
				<?php } ?>

				<?php if(validation_errors()){?>
					<div class="alert alert-solid-danger alert-bold" role="alert">
						<div class="alert-text"><?php echo (validation_errors()); ?></div>
					</div>
				<?php } ?>

				<?php echo form_open(current_url(),'class="kt-login__form m-form" id="confirm_code-form"'); ?>
					<div class="form-group m-form__group">
						<?php echo form_input('identity',$this->input->post('identity')?$this->input->post('identity'):$identity,'class="form-control m-input" disable="disabled" placeholder="Phone or Email Address" autocomplete="off"'); ?>
					</div>
					<div class="form-group m-form__group">
						<?php echo form_input('otp_code','','class="form-control m-input" placeholder="Confirmation Code" autocomplete="off"'); ?>
					</div>
					<!--begin::Action-->
			        <div class="kt-login__actions">
			            <button type="submit" id="recover_password_submit_btn" class="submit-button1 btn cust_checkin_btn btn-success kt-btn kt-btn--outline-2x mt-3 mr-2">Confirm <i class="la la-angle-right"></i></button>
			            <button id="" type="" class="processing-button btn btn-primary btn-sm btn-elevate kt-login__btn-primary kt-btn kt-btn--outline-2x mt-3 mr-2" disabled="disabled">
			                <i class="fas fa-circle-notch fa-spin"></i> Processing

			            </button>
			            <a href="<?php echo site_url('/login')?>" class="btn cust_checkin_btn btn-outline-success m-btn m-btn--outline-2x mt-3 second_button">Back to login</a>
			        </div>
			        <!--end::Action-->
			<?php echo form_close(); ?>
			</div>
		</div>
	</div>
</div>

<script>
	$(document).ready(function(){
		var form = $('#confirm_code-form');
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
            success: function(error){
	            $('.submit-button1').on('click',function(){
					$(this).hide();
					$('.processing-button').show();
				});                        
	        },

		});

		

	});
</script>