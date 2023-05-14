<div class="kt-portlet">
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
			<?php echo form_hidden('id',$id); ?>
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
			<div class="col-md-6">
				<div class="form-group m-form__group">
					<label for="">
						Phone Number<span class="required">*</span>
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
	</div>
	<div class="kt-portlet__foot">
      <div class="kt-form__actions">
          <button type="submit" class="btn btn-primary submit-button">Save</button>
          <button type="submit" disabled="disabled" class="btn btn-primary processing-button"><i class="fas fa-circle-notch fa-spin"></i> Processing</button>
      </div>
  </div>
<?php echo form_close(); ?>
</div>

<script type="text/javascript">
	
	var base_url = window.origin;
	$(document).ready(function(){
		$(document).on('change','#school_id',function(){
      var element = $(this).val();
      load_vehicle(element);
	    	
    });

    function load_vehicle(id){
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
            data: { id: id },
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
                            /*if(vehicle_id){
															console.log(vehicle_id)
															$('#vehicle_holder').val(vehicle_id);
														}*/
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
		}

		function isJson(str) {
        try {
            JSON.parse(JSON.stringify(str))
            // /JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    }

	});

</script>

