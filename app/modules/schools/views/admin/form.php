<div class="kt-portlet">
<?php echo form_open(current_url(),'class="submit-form m-form m-form--label-align-right" role="form"');?>
	<div class="kt-portlet__body">
		<div class="row">
			<div class="col-md-12">
				<div class="form-group m-form__group">
					<label for="">
						School Name <span class="required">*</span>
					</label>
					<?php echo form_input('name',$post->name,'class="form-control m-input m-input--air" placeholder="School Name"');?>
					<span class="m-form__help">
					</span>
				</div>
			</div>
		</div>
		<?php echo form_hidden('id',$id) ?>
		<div class="row">
			<div class="col-md-12">
				<div class="form-group m-form__group">
					<label for="">
						Description <span class="required">*</span>
					</label>
					<?php echo form_textarea('description',$post->description,'class="form-control m-input m-input--air" placeholder="School Description"');?>
					<span class="m-form__help">
					</span>
				</div>
			</div>
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

