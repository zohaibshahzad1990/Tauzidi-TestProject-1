<div class="kt-portlet kt-portlet--mobile">
	<div class="kt-portlet__head kt-portlet__head--lg">
		<div class="kt-portlet__head-label">
			<span class="kt-portlet__head-icon">
				<i class="kt-font-brand flaticon2-line-chart"></i>
			</span>
			<h3 class="kt-portlet__head-title">
				{metronic:template:title}
			</h3>
		</div>
		<div class="kt-portlet__head-toolbar">
			<a href="#" data-title="Create Pay Bill" data-pay-bill-id="" data-content="#pay-bill-form-holder" class="btn btn-default launch-modal">
				<i class="la la-cart-plus"></i> Create Pay Bill
			</a>
		</div>
	</div>
	<div class="kt-portlet__body">

		<!--begin: Search Form -->
		<div class="kt-form kt-form--label-right">
			<div class="row align-items-center">
				<div class="col-xl-8 order-2 order-xl-1">
					<div class="row align-items-center">
						<div class="col-md-4 kt-margin-b-20-tablet-and-mobile">
							<div class="kt-input-icon kt-input-icon--left">
								<input type="text" class="form-control" placeholder="Search..." id="generalSearch">
								<span class="kt-input-icon__icon kt-input-icon__icon--left">
									<span><i class="la la-search"></i></span>
								</span>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-4 order-1 order-xl-2 kt-align-right">
					<a href="#" class="btn btn-default kt-hidden">
						<i class="la la-cart-plus"></i> New Order
					</a>
					<div class="kt-separator kt-separator--border-dashed kt-separator--space-lg d-xl-none"></div>
				</div>
			</div>
		</div>

		<!--end: Search Form -->
	</div>
	<div class="kt-portlet__body kt-portlet__body--fit">

		<!--begin: Datatable -->
		<div class="kt-datatable" id="pay-bills"></div>

		<!--end: Datatable -->
	</div>
</div>

<script>

</script>