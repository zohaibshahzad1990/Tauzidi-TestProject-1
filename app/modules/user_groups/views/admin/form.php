<div class="kt-portlet">
<?php echo form_open(current_url(),'class="submit-form m-form m-form--fit m-form--label-align-right" role="form"');?>
<div class="kt-portlet__body">
	<div class="form-group m-form__group name">
		<label for="">
			Name
		</label>
		<?php echo form_input('name',$post->name?$post->name:"",'class="form-control m-input m-input--air" placeholder="Name"');?>
		<span class="m-form__help">
		</span>
	</div>
	<div class="form-group m-form__group description">
		<label for="">
			Description
		</label>
		<?php echo form_input('description',$post->description?$post->description:"",'class="form-control m-input m-input--air" placeholder="Description"');?>
		<span class="m-form__help">
		</span>
	</div>
	<div class="kt-portlet__foot">
      <div class="kt-form__actions">
          <button type="submit" class="btn btn-primary submit-button">Save</button>
          <button type="submit" disabled="disabled" class="btn btn-primary processing-button"><i class="fas fa-circle-notch fa-spin"></i> Processing</button>
      </div>
  </div>
</div>
<?php echo form_close(); ?>
</div>
