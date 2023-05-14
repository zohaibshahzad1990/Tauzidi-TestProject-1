<div class="kt-portlet">
<?php echo form_open(current_url(),'class="submit-form m-form m-form--label-align-right" role="form"');?>
	<div class="kt-portlet__body">
		<div class="row">
			<div class="col-md-4">
				<div class="form-group m-form__group">
					<label for="">
						First Name
					</label>
					<?php echo form_input('first_name',$post->first_name,'class="form-control m-input m-input--air" placeholder="First Name"');?>
					<span class="m-form__help">
					</span>
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group m-form__group">
					<label for="">
						Middle Name
					</label>
					<?php echo form_input('middle_name',$post->middle_name,'class="form-control m-input m-input--air" placeholder="Middle Name"');?>
					<span class="m-form__help">
					</span>
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group m-form__group">
					<label for="">
						Last Name
					</label>
					<?php echo form_input('last_name',$post->last_name,'class="form-control m-input m-input--air" placeholder="Last Name"');?>
					<span class="m-form__help">
					</span>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="form-group m-form__group">
					<label for="">
						Phone Number
					</label>
					<?php echo form_input('phone',$post->phone,'class="form-control m-input m-input--air" placeholder="Phone Number"');?>
					<span class="m-form__help">
					</span>
				</div>
			</div>

			<div class="col-md-6">
				<div class="form-group m-form__group">
					<label for="">
						Email Address
					</label>
					<?php echo form_input('email',$post->email,'class="form-control m-input m-input--air" placeholder="Email Address"');?>
					<span class="m-form__help">
					</span>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="form-group m-form__group">
					<label for="">
						Password
					</label>
					<?php echo form_password('password','','class="form-control m-input m-input--air" placeholder="Password"');?>
					<span class="m-form__help">
					</span>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group m-form__group">
					<label for="">
						Confirm Password
					</label>
					<?php echo form_password('confirm_password','','class="form-control m-input m-input--air" placeholder="Confirm Password"');?>
					<span class="m-form__help">
					</span>
				</div>
			</div>
		</div>
		<div class="form-group m-form__group">
			<label for="">
				User Groups
			</label>
			<?php echo form_dropdown('groups[]',$groups,$this->input->post('groups')?:($selected_groups?:''),' multiple="multiple" class="form-control m-input m-input--air kt-select2" placeholder="Confirm Password"');?>
			<span class="m-form__help">
			</span>
		</div>
	</div>
	<div class="kt-portlet__foot">
      <div class="kt-form__actions">
          <button type="submit" class="btn btn-primary submit-button">Save</button>
          <button type="submit" disabled="disabled" class="btn btn-primary processing-button"><i class="fas fa-circle-notch fa-spin"></i> Processing</button>
      </div>
  </div>
<?php echo form_close(); ?>
</div>

