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
			<?php echo form_hidden('student_id',$post->student_id);?>
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
						Student Registartion number <span class="required">*</span>
					</label>
					<?php echo form_input('registration_no',$this->input->post('registration_no')?$this->input->post('registration_no'):$post->registration_no,'class="form-control m-input m-input--air" placeholder="Registration Number"');?>
					<span class="m-form__help">
					</span>
				</div>
			</div>					
			<div class="col-md-4">
				<div class="form-group m-form__group">
					<label for="">
						School
					</label>
						<?php echo form_dropdown('school_id',array(''=>'Select School')+$schools,$this->input->post('school_id')?$this->input->post('school_id'):$post->school_id,'id="school_id" class="form-control kt-select2" placeholder="Select School"');?>
					<span class="m-form__help">
					</span>
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group m-form__group">
					<label for="">
						Vehicle Assigned
					</label>
					<?php echo form_dropdown('vehicle_id',array(''=>'Select Vehicle')+$vehicles,$this->input->post('vehicle_id')?$this->input->post('vehicle_id'):$post->vehicle_id, ' class="form-control m-input m-input--air kt-select2" id ="vehicle_holder" placeholder="Vehicle Id"');?>
					<span class="m-form__help">
					</span>
				</div>
			</div>

			<div class="col-md-4">
				<div class="form-group m-form__group">
					<label for="">
						Trips Assigned
					</label>
					<?php echo form_dropdown('trip_ids[]',array(''=>'Choose Trip')+$trip_details,$trip_ids, ' class="form-control m-input m-input--air kt-select2" id ="trip_holder" multiple="multiple"');?>
					<span class="m-form__help">
					</span>
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group m-form__group">
					<label for="">
						Parent
					</label>
						<?php echo form_dropdown('user_parent_id',array(''=>'Choose Parent')+$parents,$this->input->post('user_parent_id')?$this->input->post('user_parent_id'):$post->user_parent_id,'id="user_parent_id" class="form-control kt-select2" placeholder="Choose Parent"');?>
					<span class="m-form__help">
					</span>
				</div>
			</div>
			<div class="col-md-4">
                <div class="form-group m-form__group pt-0">
                    <label>
                        Pick Up/Drop Off Point <span class="required">*</span>
                    </label>                        
                    <?php echo form_dropdown('point_id',array(''=>'Select Drop/Pick Up Point')+$points,$this->input->post('point_id')?$this->input->post('point_id'):$post->point_id,'class="form-control m-input m-input--air kt-select2" id ="point_holder" placeholder="Vehicle Id"'); ?>
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

	$(document).on('change','select[name="school_id"]',function(){
        var element = $(this).val();
        load_vehicle(element);
	})

	$(document).on('change','select[name="vehicle_id"]',function(e){
            var element = $(this).val();
            //console.log(e +" trips")
            var fixture_data ={
                "id":element 
            };
            KTApp.block('#student-trip-form',{
                overlayColor: 'grey',
                animate: true,
                type: 'loader',
                state: 'primary',
                message: 'Fetching data...'
            });
            $.ajax({
                type: "POST",
                url: base_url+'/ajax/vehicles/get_my_trips_by_vehicle',
                data: { id: element },
                dataType: "json",
                success: function(data){
                    if(isJson(data)){
                        if(data.result_code == 200){
                            //console.log(data.data)
                            var response = data.data;
                            $('#trip_holder').empty();
                            $('#trip_holder').append("<option value='0'>---Select Trips---</option>");
                            $.each(response, function (key, value) {
                                $('#trip_holder').append($("<option></option>").val(value.id).html(value.name));
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
            KTApp.unblock('#student-trip-form');
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

