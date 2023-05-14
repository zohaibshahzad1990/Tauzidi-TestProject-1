<div class="kt-portlet">
    <div class="kt-portlet__head">
        <div class="kt-portlet__head-label">
            <h3 class="kt-portlet__head-title">
                {metronic:template:title}
            </h3>
        </div>
    </div>
    <!--begin::Form-->
    <?php echo form_open_multipart(current_url(),'class="kt-form" role="form" id="guardian_form"');?>    
        <div class="kt-portlet__body" style="padding: 1px !important;">
            
        	<div class="container">
        		<span class="error"></span>
        	</div>
            <div class="table-responsive">
            	
                <table class="table table-condensed guardians-table">
                    <thead>
                        <tr> 
                            <th width="1%">
                                #
                            </th>
                            <th width="20%">
                                <?php echo 'Full Name';?>
                                <span class='required'>*</span>
                            </th>
                            <th width="20%">
                                <?php echo 'Phone Number';?>
                                <span class='required'>*</span>
                            </th>
                            <th width="20%">
                                <?php echo 'Id Number';?>
                                <span class='required'>*</span>
                            </th>
                            <th width="20%">
                                <?php echo 'Email Address';?>
                            </th>
                            <th width="3%">
                               &nbsp;
                            </th>
                        </tr>
                    </thead>
                    <tbody id='append-place-holder'>
                        <tr>
                            <th scope="row" class="count">
                                1
                            </th>
                            <td>
                                <?php echo form_input('full_names[0]','','class="form-control full_name input-sm " id="full_name" placeholder="Full name"');?>
                            </td>
                            <td>
                                <?php echo form_input('phones[0]','','class="form-control phone input-sm " id="phone" placeholder="Phone"');?>
                            </td>
                            <td>
                                <span class="m-select2-sm m-input--air">
                                    <?php echo form_input('id_numbers[0]','','class="form-control id_number input-sm " id="id_number" placeholder=" ID Number"');?>
                                </span>
                            </td>

                            <td>
                                 <?php echo form_input('emails[0]','','class="form-control draw input-sm " id="email" placeholder="Email"');?>
                            </td>

                            <td >
                                <a title="Delete" class="btn btn-sm btn-clean btn-icon btn-icon-md remove-line" id="remove-line">                            
                                    <i class="text-danger la la-trash"></i>                     
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <?php echo form_hidden('parent_id', $parent->id);?>
            </div>
            <div class="ro11w" style="padding-bottom: 10px;">
                <div class="col-md-12">
                    <button type="button" class="btn btn-default btn-sm add-new-line" id="add-new-line">
                        <i class="la la-plus"></i><?php echo 'Add New Guardian';?>
                    </button>
                </div>
            </div>

        </div>
        <div class="kt-portlet__foot">
            <div class="kt-form__actions">
                <!-- <button type="submit" class="btn btn-primary submit-button">Save</button> -->
                <button class="btn btn-primary m-btn m-btn--custom m-btn--icon btn-sm submit_form_button" id="" type="submit">
                    Submit                          
                </button>
                <button type="submit" disabled="disabled" class="btn btn-primary processing-button"><i class="fas fa-circle-notch fa-spin"></i> Processing</button>
            </div>
        </div>
    <?php echo form_close(); ?>
    <!--end::Form-->
</div>

<div id='append-new-line' class="d-none">
    <table>
        <tbody>
            <tr>
                <th scope="row" class="count">
                    1
                </th>
                <td>
                    <?php echo form_input('full_names[0]','','class="form-control full_name input-sm " id="full_name" placeholder="Full name"');?>
                </td>
                <td>
                    <?php echo form_input('phones[0]','','class="form-control phone input-sm " id="phone" placeholder="Phone"');?>
                </td>
                <td>
                    <span class="m-select2-sm m-input--air">
                        <?php echo form_input('id_numbers[0]','','class="form-control id_number input-sm " id="id_number" placeholder=" ID Number"');?>
                    </span>
                </td>

                <td>
                     <?php echo form_input('emails[0]','','class="form-control draw input-sm " id="email" placeholder="Email"');?>
                </td>

                <td >
                    <a title="Delete" class="btn btn-sm btn-clean btn-icon btn-icon-md remove-line" id="remove-line">
                        <i class="text-danger la la-trash"></i>                     
                    </a>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<script type="text/javascript">
    var base_url = window.location.origin;  

    $(document).ready(function(){        
        $(".fixtures-table  .m-select2").select2({
            placeholder:{
                id: '-1',
                text: "--Select option--",
            }, 
        });
    });

    $(document).on('click','#guardian_form .add-new-line',function(e){
        var html = $('#append-new-line tbody').html();
        //html = html.replace_all('checker','');
        $('#append-place-holder').append(html);
        $('.tooltips').tooltip();
        var number = 1;
        $('.count').each(function(){
            $(this).text(number);
            $(this).parent().find('.phone').attr('name','phones['+(number-1)+']');
            $(this).parent().find('.id_number').attr('name','id_numbers['+(number-1)+']');
            $(this).parent().find('.full_name').attr('name','full_names['+(number-1)+']');
            $(this).parent().find('.email').attr('name','email['+(number-1)+']');
            number++;
        });
        $('.fixtures-table .m-select2-append').select2({
            placeholder:{
                id: '-1',
                text: "--Select option--",
            }, 
        });
       // FormInputMask.init();
    });

    $(document).on('click','.remove-line',function(event){
        $(this).parent().parent().remove();
        var number = 1;
        $('.count').each(function(){
            $(this).text(number);
            number++;
        });
    });

    $(document).on('submit','#guardian_form',function(e){
        e.preventDefault();
        var a = $('#guardian_form');
        var e = $('#guardian_form .submit_form_button');
        KTApp.block('#guardian_form', {
            overlayColor: 'grey',
            animate: true,
            type: 'loader',
            state: 'primary',
            message: 'Processing...'
        });
        RemoveDangerClass();            
        $('#guardian_form .submit_form_button').addClass('m-loader m-loader--light m-loader--left').attr('disabled',true);
        if(validate_form()){
          var form = $('#guardian_form');
          //console.log(form)
          $.ajax({
                type: "POST",
                url: '<?php echo base_url("ajax/guardians/create"); ?>',
                data: form.serialize(),
                success: function(data) {
                    var response = $.parseJSON(JSON.stringify(data));
                    if(response.status == 1){
                        toastr['success'](response.message);
                        window.location.href = response.refer;
                    }else{
                        var message = response.message;
                        var validation_errors = '';
                        if(response.hasOwnProperty('validation_errors')){
                            validation_errors = response.validation_errors;
                        }

                        setTimeout(function () {
                            e.removeClass("m-loader m-loader--right m-loader--light").attr("disabled", !1),$(".cancel_form").removeAttr("disabled"), KTApp.unblock(a),
                            function (t, e, a) {
                                var i = $('<div class="m-alert--air mb-5 m-alert alert alert-' + e + ' alert-dismissible" role="alert">\t\t\t<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>\t\t\t<span></span>\t\t</div>');
                                t.find(".alert").remove(), i.prependTo(t), KTUtil.animateClass(i[0], "fadeIn animated"), i.find("span").html(a)
                            }(a, "danger", message);
                            if(validation_errors){
                                $.each(validation_errors, function( key, value ) {
                                    if(value){
                                        $.each(value,function(keyval, valueval){
                                            var error_message ='<div class="error invalid-feedback">'+valueval+'</div>';
                                            $('select[name="'+keyval+"["+key+']"]').next().find('.select2-selection').addClass('is-invalid');
                                            $('input[name="'+keyval+"["+key+']"]').parent().addClass('has-danger').append(error_message);
                                            $('select[name="'+keyval+"["+key+']"]').parent().addClass('is-invalid').append(error_message);
                                            $('textarea[name="'+keyval+"["+key+']"]').parent().addClass('has-danger').append(error_message);
                                        });
                                    }
                                });
                            }
                            KTUtil.scrollTop();
                        }, 2e3)
                    }
                    KTApp.unblock('#guardian_form');
                    $('#guardian_form .submit_form_button').removeClass('m-loader m-loader--light m-loader--left').attr('disabled',false);
                },error: function(){
                    setTimeout(function () {
                        KTApp.unblock(a);
                        $('#guardian_form .submit_form_button').removeClass("m-loader m-loader--right m-loader--light").attr("disabled", !1),$('#guardian_form .submit_form_button').removeClass("m-loader m-loader--right m-loader--light").attr("disabled", !1),$(".cancel_form").removeAttr("disabled"),
                            function (t, e, a) {
                                var i = $('<div class="m-alert--air mb-5 m-alert alert alert-' + e + ' alert-dismissible" role="alert">\t\t\t<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>\t\t\t<span></span>\t\t</div>');
                                t.find(".alert").remove(), i.prependTo(t), KTUtil.animateClass(i[0], "fadeIn animated"), i.find("span").html(a)
                            }(a, "danger", "Could not complete processing the request at the moment. You can refresh the page or try again later.")
                    }, 2e3)
                },
                always: function(){
                    setTimeout(function () {
                        KTApp.unblock(a);
                        $('#guardian_form .submit_form_button').removeClass("m-loader m-loader--right m-loader--light").attr("disabled", !1),$('#guardian_form .submit_form_button').removeClass("m-loader m-loader--right m-loader--light").attr("disabled", !1),$(".cancel_form").removeAttr("disabled"),
                            function (t, e, a) {
                                var i = $('<div class="m-alert--air mb-5 m-alert alert alert-' + e + ' alert-dismissible" role="alert">\t\t\t<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>\t\t\t<span></span>\t\t</div>');
                                t.find(".alert").remove(), i.prependTo(t), KTUtil.animateClass(i[0], "fadeIn animated"), i.find("span").html(a)
                            }(a, "danger", "Could not complete processing the request at the moment. You can refresh the page or try again later.")
                    }, 2e3)
                }
            });
        }else{
            $('#guardian_form .error').html('<div class="alert alert-danger alert-dismissible fade show" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"></button><strong>Sorry!</strong> There are errors on the form, please review the highlighted fields and try submitting again.</div>').slideDown();
            KTApp.unblock('#guardian_form');
            $('#guardian_form .submit_form_button').removeClass('m-loader m-loader--light m-loader--left').attr('disabled',false);
        }
        
    });

	function RemoveDangerClass(form=''){
        var dangerclasses = $('.kt-form input, .kt-form select ,  .kt-form textarea');
        $.each(dangerclasses,function(){
            if(($(this)).next().find('.select2-selection').hasClass('is-invalid')){

                ($(this).next()).find('.select2-selection').removeClass('is-invalid');
            }
                
        });
        $('.kt-form').find(".invalid-feedback").remove();
        $('.m-form.m-form--state').find(".alert").html('').slideUp();
        $('.m-form.m-form--state').find(".cancel_form").attr("disabled","disabled");
        if(form){
            form.find(".alert").html('').slideUp();
            form.find(".cancel_form").attr("disabled","disabled");
        }
    }

    function validate_form(){
        var entries_are_valid = true;        
        

        $('.guardians-table input.full_name').each(function(){
            if($(this).val()==''){
                $(this).addClass('is-invalid');
                $(this).parent().parent().addClass('has-danger');
                entries_are_valid = false;
            }else{
                $(this).parent().removeClass('is-invalid');
            }
        });

        $('.guardians-table input.id_number').each(function(){
            if($(this).val()==''){
                $(this).addClass('is-invalid');
                $(this).parent().parent().addClass('has-danger');
                entries_are_valid = false;
            }else{
                $(this).parent().removeClass('is-invalid');
            }
        });

        $('.guardians-table input.phone').each(function(){
            if($(this).val()==''){
                $(this).addClass('is-invalid');
                $(this).parent().parent().addClass('has-danger');
                entries_are_valid = false;
            }else{
                $(this).parent().removeClass('is-invalid');
            }
        });

        if(entries_are_valid){            
            return true;
        }else{
            error_message = "Sorry! There are errors on the form, please review the highlighted fields and try submitting again.";
            return false;
        }
    }

</script>