<div class="kt-portlet" id="portlet_body">
    <div class="kt-portlet__head">
        <div class="kt-portlet__head-label">
            <h3 class="kt-portlet__head-title">
                {metronic:template:title}
            </h3>
        </div>
        <div class="kt-portlet__head-toolbar">
			
            <button id="add_drop_points" data-title="New Vehicle Type" data-pay-bill-id="" data-content="#vehicle-type-form-holder" class="btn btn-default launch-modal">
				<i class="fa fa-car"></i> Add Type
			</button>
		</div>
    </div>

	<div class="form-drop-form" id="drop_point_list" >
		<div class="kt-portlet__body" id="points_table">
		   
		</div>
	</div>

</div>

<div class='d-none' id="vehicle-type-form-holder">
    <div id="vehicle-type-form">
        <div class="alert alert-solid-danger alert-bold data_error" id="data_error" role="alert" style="display:none;">
            <div class="alert-text">    
                <p><strong> Oh oh! we have a problem. </strong></p>
                <div id="error-description">
                </div>  
            </div>
        </div>
        <div id="" class="kt-portlet__body">
        	<div class="form-body">
            	<div class="row">
                    <div class="col-md-12">
                        <div class="form-group m-form__group">
                            <label>
                                Vehicle Type<span class="required">*</span>
                            </label>
                            <input type="text" name="type" value="" class="form-control m-input--air" placeholder="Vehicle Type" id="type">
                        </div>
                    </div>

                    <div class="col-md-12" style="display:none">
                        <div class="form-group m-form__group">
                            <label>
                                Capacity<span class="required">*</span>
                            </label>
                            <input type="text" name="capacity" value="" class="form-control m-input--air" placeholder="Vehicle Capacity " id="capacity">
                        </div>
                    </div>

                    <input type="hidden" name="type_id" value="" class="form-control m-input--air" id="type_id">
                    

                </div>
                
                
            </div>
        	
        </div>
    </div>
</div>


<!--end::Modal-->

<script type="text/javascript">
	var base_url = window.origin;


	$(document).ready(function(){
		$( window ).on("load", function() {
		    load_vehicle_types();
		});


    $('.modal').on('hidden.bs.modal', function () {
        $('.modal-body #vehicle-type-form #type_id').val('');
        var id = $('input[name=type_id]').val('');
    });

        $(document).on('submit','#modal-form',function(e){
            var base_url = window.origin;

            $('.data_error').hide();
            $('#error-description').html("");
            $('.modal-submit-button').hide();
            $('.modal-processing-button').show().css("display","inline-block");
            var base_url = window.location.origin;
            var form = $(this);
            if(form.find('#vehicle-type-form').is(':visible')){
                var pay_bill_id = $('input[name=type_id]').val();
                    KTApp.block('#drop_point_list',{
                        overlayColor: 'grey',
                        animate: true,
                        type: 'loader',
                        state: 'primary',
                        message: 'Fetching data...'
                    });
                    $.ajax({
                        type: "POST",
                        url: base_url+'/ajax/vehicles/type',
                        data: form.serialize(),
                        dataType: "json",
                        success: function(response) {
                            if(response.status == 200){
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
                                load_vehicle_types();
                            }else{
                                
                                var message = response.message;
                                var validation_errors = '';
                                if(response.hasOwnProperty('validation_errors')){
                                    validation_errors = response.validation_errors;
                                }
                                var error_message = [];
                                if(validation_errors){ 
                                    //error_message.push('<div class="alert-text">');
                                    $.each(validation_errors, function( key, value ) {
                                        error_message.push('<p>' +value + '</p>');
                                    });
                                    $('.data_error').each(function(){
                                        $(this).slideDown('fast',function(){
                                            var element = $(this).find('#error-description');
                                             element.html(error_message.join(''));
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
                        url:base_url+'/ajax/vehicles/delete_type',
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
                                swal.fire("Cancelled", "Could not delete your record :)", "error")   
                            }
                        },
                        error: function(){
                            KTApp.unblock('.'+id+'_active_row');
                            swal.fire("Cancelled", "Could not delete your record :)", "error")
                        },
                    });
                }else{
                    swal.fire("Cancelled", "Your record is safe :)", "error")
                }
            })
        });

        $('body').on('click','#edit_point',function(){
            var id = $(this).data("id");
            KTApp.block('#drop_point_list',{
                overlayColor: 'grey',
                animate: true,
                type: 'loader',
                state: 'primary',
                message: 'Fetching data...'
            });
            $.ajax({
                url : base_url+"/ajax/vehicles/get_vehicle_type",
                method : "POST",
                data:{ id: id},
                success: function (response) {
                    var result =$.parseJSON(JSON.stringify(response));
                    if(result.result_code == 200){
                        $("#add_drop_points").click();
                        var data = result.data;
                        var id = data.id
                        console.log(data.id)
                        $("#vehicle-type-form #type_id").val(id);
                        $("#vehicle-type-form #type").val(data.name);
                        $("#vehicle-type-form #capacity").val(data.capacity);

                        KTApp.unblock('#drop_point_list');
                    }else{
                        toastr.error("Route not found, try again", 'Oops!', {timeOut: 5000})
                        KTApp.unblock('#drop_point_list');
                    }
                },
                error: function (data) {
                    KTApp.unblock('#drop_point_list');
                },
            });

        });


        function load_vehicle_types(){
            var id = 1;
            KTApp.block('#drop_point_list');
            var load_drop_points = $.ajax({
                type: "GET",
                url:base_url+'/ajax/vehicles/get_vehicle_types/',
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
