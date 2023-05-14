<div class="kt-portlet">
<?php echo form_open(current_url(),'class="submit-form m-form m-form--label-align-right" role="form"');?>
	<div class="kt-portlet__body">
		<div class="row">
			<div class="col-md-6">
				<div class="form-group m-form__group">
					<label for="">
						Vehicle Registration Number (Use This Format KCB 111K)<span class="required">*</span>
					</label>
					<?php echo form_input('registration',$post->registration,'class="form-control m-input m-input--air" placeholder="Registration number i.e KCB 111K"');?>
					<span class="m-form__help">
					</span>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group m-form__group">
					<label for="">
						School <span class="required">*</span>
					</label>
						<?php echo form_dropdown('school_id',array(''=>'Select School')+$schools,$this->input->post('school_id')?$this->input->post('school_id'):$post->school_id,'id="school_id" class="form-control kt-select2" placeholder="Select School"');?>
					<span class="m-form__help">
					</span>
				</div>
			</div>
		</div>
		<?php echo form_hidden('id',$id)?>
		<div class="row">
			<div class="col-md-6">
				<div class="form-group m-form__group">
            <label>
                Capacity<span class="required">*</span>
            </label>
            <?php echo form_input('capacity',$this->input->post('capacity') ? $this->input->post('capacity') : $post->capacity,'class="form-control m-input m-input--air" placeholder="i.e 33"');?>
        </div>
			</div>
			<div class="col-md-6">
				<div class="form-group m-form__group">
					<label for="">
						Vehicle Type <span class="required">*</span>
					</label>
					<?php echo form_dropdown('type_id',array(''=>'Select Vehicle Type')+$types,$this->input->post('type_id')?$this->input->post('type_id'):$post->type_id,'id="type_id" class="form-control kt-select2"');?>
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

