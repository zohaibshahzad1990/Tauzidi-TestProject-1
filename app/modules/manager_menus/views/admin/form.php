<div class="kt-portlet">
	<div class="kt-portlet__head">
		<div class="kt-portlet__head-label">
			<h3 class="kt-portlet__head-title">
				{metronic:template:title}
			</h3>
		</div>
	</div>
	<!--begin::Form-->
	<?php echo form_open_multipart(current_url(),'class="kt-form" role="form"');?>

		<div class="kt-portlet__body">
			<div class="row">
				<div class="col-md-6"> 
					<div class="form-group">
						<label>Menu Name</label>
						<?php echo form_input('name',$this->input->post('name')?$this->input->post('name'):$post->name,'class="form-control" placeholder=" Name"');?>
						<span class="form-text text-muted">This will appear as the menu item label</span>
					</div>
				</div>
				<div class="col-md-6"> 
				<div class="form-group">
					<label>Parent Menu</label>
					<?php echo form_dropdown('parent_id',array(''=>'Select Parent Menu')+$menus,$this->input->post('parent_id')?$this->input->post('parent_id'):$post->parent_id,'id="parent_id" class="form-control kt-select2" placeholder="Select Menu Parent"');?>
					<span class="form-text text-muted"></span>
				</div>
				</div>
				<div class="col-md-6"> 
					<div class="form-group">
						<label>Menu URL</label>
						<?php echo form_input('url',$this->input->post('url')?$this->input->post('url'):$post->url,'class="form-control" placeholder=" URL "');?>
						<span class="form-text text-muted"></span>
					</div>
				</div>
				<div class="col-md-6"> 
					<div class="form-group">
						<label>Menu Icon</label>
						<?php echo form_input('icon',$this->input->post('icon')?$this->input->post('icon'):$post->icon,'class="form-control" placeholder=" Icon "');?>
						<span class="form-text text-muted"></span>
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
	<!--end::Form-->
</div>
