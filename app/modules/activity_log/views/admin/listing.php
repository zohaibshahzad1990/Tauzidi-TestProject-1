<?php if(empty($posts)){ ?>

	<div class="row">
    <div class="col-md-12">
        <!--begin::Portlet-->
        <div class="kt-portlet kt-portlet--tab">

			<div class="kt-portlet__body" style="padding:0px!important;">
				<div class="kt-alert kt-alert--icon m-alert--icon-solid kt-alert--outline alert alert-brand alert-dismissible fade show" role="alert">
					<!-- <div class="kt-alert__icon">
						<i class="flaticon-exclamation-1"></i>
						<span></span>
					</div> -->
					<div class="kt-alert__text">
						<!-- <strong>
							Heads up!
						</strong> -->
						There are no logs created
					</div>
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
        </div><br>
        <table class="table table-striped table-bordered table-hover table-header-fixed table-condensed table-searchable no-footer">
         	<thead class="thead-inverse">
				<tr>						
					<th>
						#
					</th>
					<th>
						User
					</th>
					<th>
						url
					</th>
					<th>
						Execution Time
					</th>					
					<th>
						Time
					</th>
				</tr>
			</thead>
            <tbody>
				<?php
					$count = $pagination['from'];
					foreach($posts as $post):
						$name = "";
						if(array_key_exists($post->user_id,$user_options)){
							$name = $user_options[$post->user_id]->first_name .' '. $user_options[$post->user_id]->last_name;
						}
				?>
					<tr>
						<td>
							<?php echo $count++.'.'; ?>
						</td>
						<td>
							<?php echo $name .'<br>'. $post->ip_address; ?>
						</td>
						<td>
							<?php echo '['.$post->request_method.'] '. $post->url . '<br>'. $post->description; ?>
						</td>
						<td>
							<?php echo $post->execution_time; ?>
						</td>
						<td>
							<?php echo timestamp_to_date_and_time($post->created_on); ?>
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
