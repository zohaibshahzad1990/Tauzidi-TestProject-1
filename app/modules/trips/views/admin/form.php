<div class="kt-portlet" id="portlet_body">
    <div class="kt-portlet__head">
        <div class="kt-portlet__head-label">
            <h3 class="kt-portlet__head-title">
                {metronic:template:title}
            </h3>
        </div>
    </div>
    <div class="form-drop-form" id="drop_form">
	    <?php echo form_open(current_url(),'class="submit-form m-form m-form--label-align-right"  id="submitroute" role="form"');?>
			<div class="kt-portlet__body">
			    <div class="row">
			    	<div class="col-md-6">
						<div class="form-group">
							<label>Trip Type</label><span class="required">*</span>
							<?php echo form_dropdown('trip_type_id',array(''=>'Select Trip Type')+$trip_type,$this->input->post('trip_type_id')?$this->input->post('trip_type_id'):$post->trip_type_id,'id="trip_type_id" class="form-control kt-select2" ');?>
							<span class="form-text text-muted"></span>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group m-form__group">
							<label for="">
								Trip Name <span class="required">*</span>
							</label>
							<?php echo form_input('name',$this->input->post('name')?$this->input->post('name'):$post->name,'class="form-control" placeholder=" Trip Name"');?>
							<span class="m-form__help">
							</span>
						</div>
					</div>
					<div class="col-md-6" id="return_type_name" style="display:none">
						<div class="form-group m-form__group">
							<label for="">
								Return Trip Name <span class="required">*</span>
							</label>
							<?php echo form_input('return_name',$this->input->post('return_name')?$this->input->post('return_name'):$post->return_name,'class="form-control" placeholder="Return Trip Name"');?>
							<span class="m-form__help">
							</span>
						</div>
						<div id="map-cordinates"></div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label>Select Route</label><span class="required">*</span>
							<?php echo form_dropdown('route_id',array(''=>'Select Route')+$routes,$this->input->post('route_id')?$this->input->post('route_id'):$post->route_id,'id="route_id" class="form-control kt-select2" placeholder="Select Route"');?>
							<span class="form-text text-muted"></span>
						</div>
					</div>
					<?php echo form_hidden('id',$id) ?>
					<?php echo form_hidden('return_trip_id',$return_trip_id) ?>
					
					
					<div class="col-md-6" id="start_trip_time">
						<div class="form-group m-form__group">
							<label for="">
								Tentative Start Time <span class="required">*</span>
							</label> 
							<input type="time" name="trip_time" value="" id="trip_time" class="form-control" placeholder=" Trip  Time">
							<span class="m-form__help">

							</span>
						</div>
					</div>
					<div class="col-md-6" id="return_trip_time_holder" style="display:none">
						<div class="form-group m-form__group">
							<label for="">
								Return Trip Start Time <span class="required">*</span>
							</label> 
							<input type="time" name="return_trip_time" value="" id="return_trip_time" class="form-control" placeholder=" Trip  Time">
							<span class="m-form__help">

							</span>
						</div>
					</div>
				</div>			  	
				
				<div class="kt-portlet__foot row">
					<div class="kt-form__actions">
						<button type="submit" class="btn btn-primary submit-button">Save</button>
						<button type="submit" disabled="disabled" class="btn btn-primary processing-button"><i class="fas fa-circle-notch fa-spin"></i> Processing</button>
					</div>
				</div>
			</div>
		<?php echo form_close(); ?>	
	</div>
</div>


<script type="text/javascript">
	var base_url = window.origin;
	$(document).ready(function(){
		console.log("<?php echo $this->input->post('return_trip_time')?$this->input->post('return_trip_time'):$post->return_trip_time;?>")
		

		var id = '<?php echo $id;?>';
		var vehicle_id = '<?php echo $post->vehicle_id;?>';
		var trip_time = '<?php echo $post->trip_time;?>';

		if(id){
			load_vehicle($('#school_id').trigger('change').val());
			$("#trip_time").val(trip_time)

		}

		var type_id = "<?php echo $this->input->post('trip_type_id') ?$this->input->post('trip_type_id'):$post->trip_type_id ?>";
		if(type_id){
			if(type_id == 2){
				$("#return_type_name").removeClass("d-none").show();
				$("#start_trip_time").removeClass('col-md-12').addClass('col-md-6').show();
				$("#return_trip_time_holder").show();
			}else{
				$("#return_type_name").removeClass("d-none").hide();
				$("#return_trip_time_holder").hide();
			}
		}
		
		$("#trip_time").val("<?php echo $this->input->post('trip_time')?$this->input->post('trip_time'):$post->trip_time;?>")
		$("#return_trip_time").val("<?php echo $this->input->post('return_trip_time')?$this->input->post('return_trip_time'):$post->return_trip_time;?>")
		
		$(document).on('change','#trip_type_id',function(){
            var element = $(this).val();
            //load_vehicle(element);
			//console.log(element)
			if(element == 2){
				$("#return_type_name").removeClass("d-none").show();
				$("#start_trip_time").removeClass('col-md-12').addClass('col-md-6').show();
				$("#return_trip_time_holder").show();
			}else{
				$("#return_type_name").removeClass("d-none").hide();
				$("#return_trip_time_holder").hide();
			}
	    	
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
	                            if(vehicle_id){
									console.log(vehicle_id)
									$('#vehicle_holder').val(vehicle_id);
								}
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