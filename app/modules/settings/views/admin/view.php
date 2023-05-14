
<!--Begin::Portlet-->
<div class="kt-portlet kt-portlet--height-fluid">
	<div class="kt-portlet__head kt-portlet__head--noborder">
		<div class="kt-portlet__head-label">
			<h3 class="kt-portlet__head-title">
			</h3>
		</div>
	</div>
	<div class="kt-portlet__body">

		<!--begin::Widget -->
		<div class="kt-widget kt-widget--user-profile-2">
			<div class="kt-widget__head">
				<div class="kt-widget__info">
					<a href="#" class="kt-widget__username">
						Application Name
					</a>
					<span class="kt-widget__desc">
						<?php echo $post->application_name; ?>
					</span>
				</div>
			</div>
			<div class="kt-widget__footer">
				<a type="button" href="<?php echo site_url("admin/settings/edit/1"); ?>" class="btn btn-label-warning btn-lg btn-upper">Edit</a>
			</div>
		</div>

		<!--end::Widget -->
	</div>
</div>