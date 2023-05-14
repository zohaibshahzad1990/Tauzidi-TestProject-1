<div class="kt-login__form">

	<div class="kt-login__title" style="text-align:left;">
		<h3>Forgot Password</h3>
		<p>Your phone number is required.</p>
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

	<!--begin::Form-->

	<?php echo form_open(current_url(),' class="submit-form kt-form " id="" '); ?>
		<div class="form-group">
			<?php echo form_input('identity','','class="form-control m-input" placeholder="Enter Phone Number or Email Address" autocomplete="off"'); ?>
		</div>
		<!--begin::Action-->
		<div class="kt-login__actions">
			<a href="<?php echo site_url('login')?>" class="kt-link kt-login__link-forgot">
				Back To Login?
			</a>
			<button id="" type="submit" class="submit-button btn btn-primary btn-elevate kt-login__btn-primary">
				Continue
			</button>
			<button id="" type="" class="processing-button btn btn-primary btn-elevate kt-login__btn-primary" disabled="disabled">
				<i class="fas fa-circle-notch fa-spin"></i> Processing
			</button>
		</div>
		<!--end::Action-->
	<?php echo form_close(); ?>
	
</div>
<script>
	$(document).ready(function(){
		var form = $('#forgot_password-form');
		form.validate({
			errorElement: 'div', //default input error message container
            errorClass: 'form-control-feedback m--font-danger', // default input error message class
            focusInvalid: true, // do not focus the last invalid input
            ignore: "", // validate all fields including form hidden input
            messages: {
            	identity : {
            		required : "Kindly key in your phone number to reset your password"
            	},
            },
            rules:{
            	identity: {
            		required : true,
            	},      	
            },

		});
	});
</script>