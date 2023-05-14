<div class="kt-portlet">
<?php if(empty($posts)){ ?>

<div class="row">
   <div class="col-md-12">
      <!--begin::Portlet-->
      <div class="kt-portlet kt-portlet--tab">
			<div class="kt-portlet__body" style="padding:0px!important;">
				<div class="kt-alert kt-alert--icon m-alert--icon-solid m-alert--outline alert alert-brand alert-dismissible fade show" role="alert">
					<div class="m-alert__icon">
						<i class="flaticon-exclamation-1"></i>
						<span></span>
					</div>
					<div class="kt-alert__text">
						<strong>
							Heads up!
						</strong>
						There are no Users groups created
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
	<div class="kt-portlet__body">
		<table class="table table-hover m-table">
			<thead>
				<tr>
					<th>
						#
					</th>
					<th>
						Group Name
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
				?>
					<tr>
						<td width=""><?php echo $count; ?>.</td>
						<td><?php echo $post->name; ?></td>
						<td>
							<a class="btn m-btn--pill m-btn--air btn-primary btn-sm" href="<?php echo site_url('admin/user_groups/edit/'.$post->id); ?>">
								<i class="fa fa-pencil"></i> Edit &nbsp;
							</a>
						</td>
					</tr>
				<?php
					$count++;
					endforeach;
				?>
			</tbody>
		</table>
	</div>
<?php } ?>
</div>