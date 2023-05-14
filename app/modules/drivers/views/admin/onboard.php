<div class="kt-portlet kt-portlet--mobile">
	<div class="kt-portlet__head kt-portlet__head--lg">
		<div class="kt-portlet__head-label">
			<span class="kt-portlet__head-icon">
				<i class="kt-font-brand flaticon2-line-chart"></i>
			</span>
			<h3 class="kt-portlet__head-title">
				{metronic:template:title}
			</h3>
		</div>
		<div class="kt-portlet__head-toolbar">
			
		</div>
	</div>
	<div class="kt-portlet__body kt-portlet__body--fit">
		<?php echo form_open(current_url(),'class="submit-form m-form m-form--label-align-right" role="form"');?>
			<div class="kt-portlet__body">
				<div class="row">
					<div class="col-md-4">
						<div class="form-group m-form__group">
							<label for="">
								First Name <span class="required">*</span>
							</label>
							<?php echo form_input('first_name',$post->first_name,'class="form-control m-input m-input--air" placeholder="First Name"');?>
							<span class="m-form__help">
							</span>
						</div>
					</div>
					<?php echo form_hidden('user_id',$post->id) ?>
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
								Last Name <span class="required">*</span>
							</label>
							<?php echo form_input('last_name',$post->last_name,'class="form-control m-input m-input--air" placeholder="Last Name"');?>
							<span class="m-form__help">
							</span>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-4">
						<div class="form-group m-form__group">
							<label for="">
								Phone Number <span class="required">*</span>
							</label>
							<?php echo form_input('phone',$post->phone,'class="form-control m-input m-input--air" placeholder="Phone Number"');?>
							<span class="m-form__help">
							</span>
						</div>
					</div>

					<div class="col-md-4">
						<div class="form-group m-form__group">
							<label for="">
								Email Address
							</label>
							<?php echo form_input('email',$post->email,'class="form-control m-input m-input--air" placeholder="Email Address"');?>
							<span class="m-form__help">
							</span>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group m-form__group">
							<label for="">
								Id Number <span class="required">*</span>
							</label>
							<?php echo form_input('id_number',$post->id_number,'class="form-control m-input m-input--air" placeholder="ID Number"');?>
							<span class="m-form__help">
							</span>
						</div>
					</div>
				</div>
				<div class="row">					
					<div class="col-md-6">
						<div class="form-group m-form__group">
							<label for="">
								School
							</label>
								<?php echo form_dropdown('school_id',array(''=>'Select School')+$schools,$this->input->post('school_id')?$this->input->post('school_id'):$school_id,'id="school_id" class="form-control kt-select2" placeholder="Select School"');?>
							<span class="m-form__help">
							</span>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group m-form__group">
							<label for="">
								Vehicle Assigned
							</label>
							<?php echo form_dropdown('vehicle_id',array(''=>'Select Vehicle')+$vehicles,$driver ? $driver->vehicle_id  : "", ' class="form-control m-input m-input--air kt-select2" id ="vehicle_holder" placeholder="Vehicle Id"');?>
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
</div>


<script type="text/javascript">
	var base_url = window.origin;
	$(document).ready(function(){

		$(document).on('change','select[name="school_id"]',function(){
            var element = $(this).val();

            var fixture_data ={
                "id":element 
            };
	    	KTApp.block('#vehicle_holder',{
	            overlayColor: 'grey',
	            animate: true,
	            type: 'loader',
	            state: 'primary',
	            message: 'Fetching data...'
	        });
            $.ajax({
                type: "POST",
                url: base_url+'/ajax/vehicles/get_vehicle_per_school',
                data: { id: element },
                dataType: "json",
                success: function(data){
                    if(isJson(data)){
                        if(data.result_code == 200){
                            //console.log(data.data)
                            var response = data.data;
                            $('#vehicle_holder').empty();
                            $('#vehicle_holder').append("<option value='0'>---Select Vehicle---</option>");
                            $.each(response, function (key, value) {
                                $('#vehicle_holder').append($("<option></option>").val(value.id).html(value.name));
                            });
                        }                    
                    }else{
                    }
                },
                failure: function(errMsg) {
                    alert(errMsg);
                },complete: function() {
                }
        	});
        });
	});

	function isJson(str) {
        try {
            JSON.parse(JSON.stringify(str))
            // /JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    }
</script>