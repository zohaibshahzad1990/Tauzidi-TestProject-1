<div class="kt-portlet" id="portlet_body">
    <div class="kt-portlet__head">
        <div class="kt-portlet__head-label">
            <h3 class="kt-portlet__head-title">
                {metronic:template:title}
            </h3>
        </div>
        <div class="kt-portlet__head-toolbar">
			
            <button id="add_drop_points" data-title="Add Student" data-pay-bill-id="" data-content="#assign-trip-form-holder" class="btn btn-default launch-modal-lg">
				<i class="fa fa-car"></i> Add Student
			</button>
		</div>
    </div>

	<div class="form-drop-form" id="drop_point_list" >
		<div class="kt-portlet__body" id="points_table">
		   
		</div>
	</div>

</div>

<div class='d-none' id="assign-trip-form-holder">
    <div id="student-trip-form">
        <div class="alert alert-solid-danger alert-bold data_error" id="data_error" role="alert" style="display:none;">
            <div class="alert-text">    
                <p><strong> Oh oh! we have a problem. </strong></p>
                <div id="error-description">
                </div>  
            </div>
        </div>
        <div id="" class="kt-portlet__body">
        	<?php echo form_hidden('parent_id',$parent_id);?>
            <?php echo form_hidden('user_parent_id',$parent_user->id);?>
        	<div class="form-body">
            	<div class="row">
                    <div class="col-md-6">
                        <div class="form-group m-form__group">
                            <label>
                                Full Name<span class="required">*</span>
                            </label>
                            <input type="text" name="name" value="" class="form-control m-input--air" placeholder="Full Name" id="name">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group m-form__group">
                            <label>
                                Student Number<span class="required">*</span>
                            </label>
                            <input type="text" name="registration_no" value="" class="form-control m-input--air" placeholder="Student Number" id="registration_no">
                        </div>
                    </div>

                    <input type="hidden" name="student_id" value="" class="form-control m-input--air" id="student_id">
                    <div class="col-md-6 ">
                        <div class="form-group m-form__group pt-0">
                            <label>
                                Choose School<span class="required">*</span>
                            </label>
                            <?php echo form_dropdown('school_id',array(''=>'Select School')+$schools,'','class="form-control m-input--air modal_select2" id="school_id"');?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group m-form__group pt-0" id="vehicle_form">
                            <label>
                                Vehicle <span class="required">*</span>
                            </label>                        
                            <?php echo form_dropdown('vehicle_id',array(''=>'Select Vehicle')+$vehicles,'',' disabled class="form-control m-input m-input--air modal_select2" id ="vehicle_holder" placeholder="Vehicle Id" '); ?>
                        </div>
                    </div>

                    <div class="col-md-6"  id="trips_form">
                        <div class="form-group m-form__group pt-0" >
                            <label>
                                Choose Trip <span class="required">*</span>
                            </label>                        
                            <?php echo form_dropdown('trip_id[]',array(''=>'Select Trip')+$trips,'','disabled class="form-control m-input m-input--air modal_select2" id ="trip_holder" multiple="multiple" placeholder="Trips"'); ?>
                        </div>
                    </div>

                    <div class="col-md-6"  id="points_form">
                        <div class="form-group m-form__group pt-0">
                            <label>
                                Pick Up/Drop Off Point <span class="required">*</span>
                            </label>                        
                            <?php echo form_dropdown('point_id',array(''=>'Select Drop/Pick Up Point')+$points,'','disabled class="form-control m-input m-input--air modal_select2" id ="point_holder" placeholder="Vehicle Id"'); ?>
                        </div>
                    </div>

                </div>
                
                
            </div>
        	
        </div>
    </div>
</div>


<!--end::Modal-->

<script type="text/javascript">
	var base_url = window.origin;

    $('.modal').on('hidden.bs.modal', function () {
        $('.modal-submit-button').show();
        $('.modal-processing-button').hide();
        $('.modal-body #student-trip-form #data_error').html('');
    });

	$(document).on('click','.launch-modal-lg',function(e){

 		var base_url = window.location.origin;
	    $('.processing').hide();

	    $('.submit').show();
	    var content = $(this).data('content');
	    var form_id = $(this).data('id');
	    $('input[name="process_title"]').val(form_id);
	    $('.modal-title').html($(this).data('title'));            
	    $('.modal-body').html($(content).html());
	    $('#modal-submit-button').html($(this).data('submit-button'));
	    $('#modal-lg').modal({show:true});
        $('select:not(.normal)').each(function () {
            $(this).select2({
                width:'100%',
                dropdownParent: $(this).parent()
            });
        });
        $('.modal-body').find('#data_error').html('');
        $('.modal-body #student-trip-form #vehicle_holder').attr('disabled', true);
	    $(".currency").inputmask('decimal',{
	      radixPoint:".", 
	      groupSeparator: ",", 
	      digits: 12,
	      autoGroup: true,
	      greedy: false,
	      prefix: '',
	      rightAlign: false
	    }).attr('autocomplete','off'); 
	    $.fn.modal.Constructor.prototype.enforceFocus = function() {};
	    e.preventDefault();
  	});


	$(document).ready(function(){
		$( window ).on("load", function() {
		    load_students();
		});


        $(document).off().on('change','#school_id',function(e,vehicle_id,trips){
            e.preventDefault();
            var element = $(this).val();
            var fixture_data ={
                "id":element 
            };
            $('#student-trip-form #vehicle_holder').empty().append("<option value='0'>---Select Vehicle---</option>");
            $('#student-trip-form #vehicle_holder').attr('disabled', true);
            $('#student-trip-form #trip_holder').empty().append("<option value=''>---Select Trips---</option>");
            $('#student-trip-form #point_holder').attr('disabled', true);

            $('#student-trip-form #trip_holder').attr('disabled', true);
            $('#student-trip-form #trip_holder').empty();
            KTApp.block('#student-trip-form #vehicle_form',{
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
                            $('#student-trip-form #vehicle_holder').empty();
                            $('#student-trip-form #vehicle_holder').append("<option value='0'>---Select Vehicle---</option>");
                            $.each(response, function (key, value) {
                                $('#student-trip-form #vehicle_holder').append($("<option></option>").val(value.id).html(value.name));
                            });
                            if(vehicle_id){
                                $("#student-trip-form #vehicle_holder").val(vehicle_id);
                            }
                            $('#student-trip-form #vehicle_holder').attr('disabled', false);
                        }                    
                    }else{
                    }
                },
                failure: function(errMsg) {
                    alert(errMsg);
                },complete: function() {
                }
            });
             KTApp.unblock('#student-trip-form #vehicle_form');
        });

        $(document).on('change','select[name="vehicle_id"]',function(e){
            var element = $(this).val();
            //console.log(e +" trips")
            var fixture_data ={
                "id":element 
            };
            $('#student-trip-form #trip_holder').empty().append("<option value=''>---Select Trips---</option>");
            $('#student-trip-form #trip_holder').attr('disabled', true);
            $('#student-trip-form #trip_holder').attr('disabled', true);
            $('#student-trip-form #trip_holder').empty();
            KTApp.block('#student-trip-form #trips_form #points_form',{
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
                            $('#student-trip-form #trip_holder').empty();
                            $('#student-trip-form #trip_holder').append("<option value='0'>---Select Trips---</option>");
                            $.each(response, function (key, value) {
                                $('#student-trip-form #trip_holder').append($("<option></option>").val(value.id).html(value.name));
                            });
                            $('#student-trip-form #trip_holder').attr('disabled', false);
                            var points = data.points;
                            $('#student-trip-form #point_holder').empty();
                            $('#student-trip-form #point_holder').append("<option value='0'>---Select Pickup/Drop off ---</option>");
                             $.each(points, function (key, value) {
                                $('#student-trip-form #point_holder').append($("<option></option>").val(value.id).html(value.name));
                            });
                             $('#student-trip-form #point_holder').attr('disabled', false);
                        }                    
                    }else{
                    }
                },
                failure: function(errMsg) {
                    alert(errMsg);
                },complete: function() {
                }
            });
            KTApp.unblock('#student-trip-form #trips_form #points_form');
        });

        //$(document).on('click','select[name="trip_id[]"]', function(e){
        
        
    	$("#add_drop_points").on('click', function(){
    		 $("#student-trip-form #vehicle_assigned_id").val("");
    	})

	$(document).on('submit','#modal-form',function(e){
        var base_url = window.origin;

        $('.data_error').hide();
        $('#error-description').html("");
        $('.modal-submit-button').hide();
        $('.modal-processing-button').show().css("display","inline-block");
        var base_url = window.location.origin;
        var form = $(this);
        if(form.find('#student-trip-form').is(':visible')){
            var pay_bill_id = $('input[name=id]').val();
	            KTApp.block('#drop_point_list',{
		            overlayColor: 'grey',
		            animate: true,
		            type: 'loader',
		            state: 'primary',
		            message: 'Fetching data...'
		        });
                $.ajax({
                    type: "POST",
                    url: base_url+'/ajax/students/create',
                    data: form.serialize(),
                    dataType: "json",
                    success: function(response) {
                        if(response.result_code == 200){
                            toastr.options = {
                                "closeButton": true,
                                "debug": false,
                                "newestOnTop": true,
                                "progressBar": true,
                                "positionClass": "toast-bottom-right",
                                "preventDuplicates": false,
                                "showDuration": "5000",
                                "hideDuration": "1000",
                                "timeOut": "5000",
                                "extendedTimeOut": "1000",
                                "showEasing": "swing",
                                "hideEasing": "linear",
                                "showMethod": "fadeIn",
                                "hideMethod": "fadeOut"
                            };
                            toastr.success(response.message);
                            $('.modal').modal('hide');
                            load_students();
                        }else{
                            
                            var message = response.message;
                            var validation_errors = '';
                            if(response.hasOwnProperty('validation_errors')){
                                validation_errors = response.validation_errors;
                            }
                            var error_message = [];
                            if(validation_errors){ 
                                error_message.push("<ul>");
                                $.each(validation_errors, function( key, value ) {
                                    error_message.push('<li>' +value + '</li>');
                                });
                                error_message.push("</ul>");
                                $('.data_error').each(function(){
                                    $(this).slideDown('fast',function(){
                                        var element = $("#error-description"); //$(this).find().find('#error-description');

                                        $(this).html(error_message.join(''));
                                    });
                                });                              
                            }
                        }
                        KTApp.unblock('#drop_point_list');
                        $('.modal-submit-button').show();
                        $('.modal-processing-button').hide();
                    }
                });
        }
        e.preventDefault();
    });

    $('body').on('click','#edit_point',function(e){
       $("#add_drop_points").click();
	    var id = $(this).data("id");
	    KTApp.block('#assign-trip-form-holder',{
            overlayColor: 'grey',
            animate: true,
            type: 'loader',
            state: 'primary',
            message: 'Fetching data...'
        });
	    $.ajax({
            url : base_url+"/ajax/students/get_my_student",
            method : "POST",
            data:{ id: id},
            success: function (response) {
                var result =$.parseJSON(JSON.stringify(response));
                if(result.result_code == 200){
                    var data = result.data;
                    var id = data.trip_id
                    console.log(result.trips)
                    

                    $('#modal-lg').modal({show:true});
			        $("#student-trip-form #name").val(data.first_name + ' '+data.last_name);
			        $("#student-trip-form #student_id").val(data.student_id);
                    $("#student-trip-form select[name='school_id']").val(data.school_id).trigger("change",[data.vehicle_id,[result.trips]]);
                    $("#student-trip-form #vehicle_holder").val(data.vehicle_id).trigger("change",result.trips);

					KTApp.unblock('#assign-trip-form-holder');
                }else{
                    toastr.error("Student not found, try again", 'Oops!', {timeOut: 5000})
                    KTApp.unblock('#assign-trip-form-holder');
                }
            },
            error: function (data) {
            	KTApp.unblock('#assign-trip-form-holder');
            },
        });
         e.preventDefault();
	});

    $(document).on('click','.prompt_confirmation_message_link',function(){
        var id = $(this).attr('id');
        swal.fire({
            title: "Are you sure?", text: "You won't be able to revert this!", type: "warning", showCancelButton: !0, confirmButtonText: "Yes, Delete it!", cancelButtonText: "No, cancel!", reverseButtons: !0
        }).then(function(e) {
            if(e.value == true){
                KTApp.block('.'+id+'_active_row', {
                    overlayColor: 'grey',
                    animate: true,
                    type: 'loader',
                    state: 'primary',
                    message: 'processing..'
                });
                $.ajax({
                    type:'POST',
                    url:base_url+'/ajax/students/delete_student',
                    data:{'id':id},
                    dataType: "json",
                    success: function(response){
                        if(isJson(response)){
                            var data = response;
                            if(data.result_code == '200'){
                                KTApp.unblock('.'+id+'_active_row');
                                $('.'+id+'_active_row').hide();
                                swal.fire("success",data.message, "success")
                            }else{
                                KTApp.unblock('.'+id+'_active_row');
                                swal.fire("Cancelled",data.message, "error")
                            }
                        }else{
                            KTApp.unblock('.'+id+'_active_row');
                            swal.fire("Cancelled", "Could not delete student :)", "error")   
                        }
                    },
                    error: function(){
                        KTApp.unblock('.'+id+'_active_row');
                        swal.fire("Cancelled", "Could not delete student :)", "error")
                    },
                });
            }else{
                swal.fire("Cancelled", "Your student is safe :)", "error")
            }
        })
    });


	function load_students(){
		var id = "<?php echo $parent_id;?>"
		KTApp.block('#drop_point_list');
	    var load_drop_points = $.ajax({
            type: "GET",
            url:base_url+'/ajax/students/get_my_students/'+id,
            dataType : "html",
                success: function(response) {
                	$('#points_table').html(response);
                    KTApp.unblock('#drop_point_list');
                }
            }
        );
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
