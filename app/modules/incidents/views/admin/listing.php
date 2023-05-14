<?php if(empty($posts)){ ?>

	<div class="row">
    <div class="col-md-12">
        <!--begin::Portlet-->
        <div class="m-portlet m-portlet--tab">

			<div class="m-portlet__body" style="padding:0px!important;">
				<div class="m-alert m-alert--icon m-alert--icon-solid m-alert--outline alert alert-brand alert-dismissible fade show" role="alert">
					<!-- <div class="m-alert__icon">
						<i class="flaticon-exclamation-1"></i>
						<span></span>
					</div> -->
					<div class="m-alert__text">
						<!-- <strong>
							Heads up!
						</strong> -->
						There are no Incidents Reported
					</div>
				<!--
					<div class="m-alert__close">
						<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
					</div>
				-->
				</div>
			</div>

  		</div>
		<!--end::Portlet-->
	</div>
</div>

<?php }else{ ?>


	<div class="kt-portlet">
    <div class="kt-portlet__head">
        <div class="kt-portlet__head-label">
            <h3 class="kt-portlet__head-title">
                {metronic:template:title}
            </h3>
        </div>
    </div>
    <div class="kt-portlet__body">
        <div class="kt-pagination kt-pagination--brand ">
            <?php echo $pagination['links'];  ?>
            <div class="kt-pagination__toolbar">
                <span class="pagination__desc">
                    Displaying <?php echo $pagination['from']; ?> of <?php echo $pagination['total']; ?> records
                </span>
            </div>
        </div>
        <p></p>
        <div class="kt-separator  kt-separator--border-dashed" style="margin-top: 2px!important; margin-bottom: 4px!important;"></div>
        <table class="table table-sm table-head-bg-brand">
         <thead class="thead-inverse">
					<tr>						
						<th>
							#
						</th>
						<th>
							Reported By
						</th>
						<th>
							Code
						</th>
						<th>
							Subject
						</th>
						<th>
							Description
						</th>
						<th>
							Actions
						</th>
					</tr>
				</thead>
            <tbody>
				<?php
					$count = 1;
					foreach($posts as $post):
						$name = "";
						if(array_key_exists($post->reported_by ,$users)){
							$name = $users[$post->reported_by]->first_name .' '. $users[$post->reported_by]->last_name;
						}
				?>
					<tr class="<?php echo $post->id.'_active_row' ?>">
						<td>
							<?php echo $count++.'.'; ?>
						</td>
						<td>
							<small><?php echo $name; ?></small>
						</td>
						<td>
							<small><?php echo $post->incident_code; ?></small>
						</td>
						<td>
							<small><?php echo $post->name; ?></small>
						</td>
						<td>
							<small><?php echo $post->description; ?></small>
						</td>
						<td>
							<?php 
                            	echo '
                            	<button type="button" class="btn btn-success btn-icon prompt_confirmation_message_link" data-id="'.$post->id.'" id="'.$post->id.'">
                                    <i class="fa fa-edit"></i>
                                </button> ';
                            ?>	
						</td>
					</tr>
				<?php
					endforeach;
				?>
			</tbody>
        </table>
    </div>
</div>
<?php } ?>

<script type="text/javascript">
	
	var base_url = window.origin;


	$(document).ready(function(){

		$(document).on('click','.prompt_confirmation_message_link',function(){
	        var id = $(this).attr('id');
	        swal.fire({
	            title: "Are you sure?", text: "You won't be able to revert this!", type: "warning", showCancelButton: !0, confirmButtonText: "Yes, Mark as read!", cancelButtonText: "No, cancel!", reverseButtons: !0
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
	                    url:base_url+'/ajax/incidents/mark_as_read',
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
	                            swal.fire("Cancelled", "Could not mark as read :)", "error")   
	                        }
	                    },
	                    error: function(){
	                        KTApp.unblock('.'+id+'_active_row');
	                        swal.fire("Cancelled", "Could not mark as read :)", "error")
	                    },
	                });
	            }else{
	                swal.fire("Cancelled", "Your record is safe :)", "error")
	            }
	        })
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
    
	});


</script>
