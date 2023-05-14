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
			<div class="form-group">
				<label>Application Name</label>
				<?php echo form_input('application_name',$this->input->post('application_name')?$this->input->post('application_name'):$post->application_name,'class="form-control" placeholder="Application Name"');?>
				<span class="form-text text-muted"></span>
			</div>

			<div class="form-group">
				<label for="">
					<?php echo $post->application_name; ?> Favicon
				</label>
				<br/>
				<div class="fileinput fileinput-new" data-provides="fileinput">
						<div class="fileinput-new thumbnail" style="max-width: 100px;">
							<img src="<?php echo $post->favicon?site_url($path.'/'.$post->favicon):site_url('templates/metronic/img/no_image.png'); ?>" alt="" /> </div>
						<div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 100px; max-height: 50px;"> </div>
						<div>
							<span class="btn btn-info btn-file">
								<input type="file" name="favicon">
							</span>
						</div>
				</div>
				<span class="m-form__help">
				</span>
			</div>

			<div class="form-group m-form__group">
				<label for="">
					<?php echo $post->application_name; ?> Logo
				</label>
				<br/>
				<div class="fileinput fileinput-new" data-provides="fileinput">
						<div class="fileinput-new thumbnail" style="max-width: 150px;">
							<img src="<?php echo $post->logo?site_url($path.'/'.$post->logo):site_url('templates/metronic/img/no_image.png'); ?>" alt="" /> </div>
						<div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 100px; max-height: 50px;"> </div>
						<div>
							<span class="btn btn-info btn-file">
								<input type="file" name="logo"> 
							</span>
						</div>
				</div>
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
	<!--end::Form-->
</div>

