<div class="row">
	<div class="col-md-12">
        <div class="kt-portlet">
            <div class="kt-portlet__body">
                <div class="portlet-body form">
                    <?php echo form_open(current_url(),'class="form_submit" role="form"');?>
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Choose Category:<span class="required">*</span></label>
                                        <?php echo form_dropdown('send_to',array(''=>'--Select Option--')+$send_to_options,$this->input->post('send_to')?:'','class="form-control kt-select2 send_to" id="send_to"');?>
                                    </div>
                                </div>
                                <!--<div class="col-md-6">
                                    <div class="form-group">
                                        <label>Send message to:<span class="required">*</span></label>
                                        <?php echo form_dropdown('send_type',array(''=>'--Select Option--')+$message_type,$this->input->post('send_type')?:'','class="form-control kt-select2 send_type" id="send_type"');?>
                                    </div>
                                </div>-->
                                <div class="col-md-12">
                                    <div class="individual-users-settings">
                                        <div class="form-group">
                                            <label>Select users to send to:</label>
                                            <?php echo form_dropdown('user_ids[]',"",$this->input->post('user_ids'),'class="form-control kt-select2 user-search" multiple="multiple" id="user_search"');?>                             
                                            <span class='help-text'><small>Enter user name or phone number</small></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Message<span class="required">*</span></label>
                                        <div class="input-group">
                                            <!--
                                            <span class="input-group-addon">
                                                <i class="fa fa-envelope"></i>
                                            </span>
                                            -->
                                            <?php
                                                $textarea = array(
                                                    'name'=>'message',
                                                    'id'=>'',
                                                    'value'=> $this->input->post('message')?:'',
                                                    'cols'=>40,
                                                    'rows'=>8,
                                                    'maxlength'=>200,
                                                    'class'=>'form-control maxlength',
                                                    'placeholder'=>'Compose SMS to send'
                                                ); 
                                                echo form_textarea($textarea); 
                                            ?>
                                        </div>
                                        <span class="help-block">Type message to send.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary submit-button">Save</button>
                            <button type="button" class="btn btn-md blue processing-button disabled" name="processing" value="Processing"><i class="fa fa-spinner fa-spin"></i> Processing</button> 
                            
                        </div>
                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        var base_url = window.origin;
        $('.individual-users-settings').slideUp();
        $('.user-groups-settings').slideUp();

        $(document).on('change','select[name="send_to"]',function(){
            var element = $(this).val();
            $('.individual-users-settings').slideDown();
            $('#user_search').empty();
            var fixture_data ={
                "id":element 
            };
            KTApp.block('.individual-users-settings',{
                overlayColor: 'grey',
                animate: true,
                type: 'loader',
                state: 'primary',
                message: 'Fetching data...'
            });
            
            $.ajax({
                type: "POST",
                url: base_url+'/ajax/users/get_users_per_category',
                data: { id: element },
                dataType: "json",
                success: function(data){
                    if(isJson(data)){
                        if(data.result_code == 200){
                            var response = data.data;
                            $('#user_search').empty();
                            $('#user_search').append("<option value='0'>---Search User---</option>");
                            $('#user_search').append("<option value='all'> Send To All </option>");
                            $.each(response, function (key, value) {
                                $('#user_search').append($("<option></option>").val(value.id).html(value.name));
                            });
                            KTApp.unblock('.individual-users-settings');
                        }                    
                    }else{
                    }
                },
                failure: function(errMsg) {
                    alert(errMsg);
                },complete: function() {
                }
            });
            KTApp.unblock('.individual-users-settings');
        });
        /*$(document).on('change','select[name="send_to"]',function(){
            var send_to = $(this).val();
            if(send_to==1){
                $('.user-groups-settings').slideUp();
                 $('.individual-users-settings').slideDown();
            }else if(send_to==2){
                $('.user-groups-settings').slideUp();
                $('.individual-users-settings').slideDown();
            }else if(send_to==3){
                 $('.individual-users-settings').slideDown();
            }else{
                 $('.individual-users-settings').slideDown();
            }
        });

        var send_to = "<?php echo $this->input->post('send_to')?>";
        if(send_to == 1 || ($('select[name="send_to"]').val()) == 1){
            $('.individual-users-settings').slideDown();
        }else if(send_to == 2 || ($('select[name="send_to"]').val()) == 2){
            $('.individual-users-settings').slideDown();
        }else if(send_to == 3 || ($('select[name="send_to"]').val()) == 3){
            $('.individual-users-settings').slideDown();
        }  */     

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