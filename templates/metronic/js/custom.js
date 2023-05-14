$(document).ready(function(){

	$('.submit-button').on('click',function(){
		$(this).hide();
		$('.processing-button').show();
	});

    $('.kt-select2').select2({});

    

	$('.confirm').on('click',function(){
	    var element = $(this);
	    bootbox.confirm("Are you sure, you want to proceed?", function(result) {
	      if(result==true){
	        var href = element.attr('href');
	        window.location = href;
	      }else{
	        return true;
	      }
	    });
	    return false;
	});

	var MypaUsers = function() {
		// Private functions

		// basic demo
		var fetchMypaUsers = function() {

		var datatable = $('#mypa-users').KTDatatable({
			// datasource definition
			data: {
				type: 'remote',
				source: {
					read: {
						url: 'https://mypa.co.ke:8443/api/users',
						//sample custom headers
						//headers: {'x-my-custokt-header': 'some value', 'x-test-header': 'the value'},
						map: function(raw) {
							// sample data mapping
							var dataSet = raw;
							if (typeof raw.data !== 'undefined') {
								dataSet = raw.data;
							}
							return dataSet;
						},
					},
				},
				pageSize: 10,
				serverPaging: true,
				serverFiltering: true,
				serverSorting: true,
			},

			// layout definition
			layout: {
				scroll: false,
				footer: false,
			},

			// column sorting
			sortable: true,

			pagination: true,

			search: {
				input: $('#generalSearch'),
			},

			// columns definition
			columns: [
				{
					field: 'pic',
					title: 'Avatar',
					width: 70,
					sortable: 'asc',
					template: function(row) {
						return '<div class="kt-widget kt-widget--user-profile-3"><div class="kt-widget__top"><div class="kt-widget__media kt-hidden-"><img src="'+row.pic+'" alt="image"></div></div></div>';
						//row.firstName + ' ' + row.lastName;
					}
				}, {
					field: 'fullName',
					title: 'Name',
					template: function(row) {
						return row.firstName + ' ' + row.lastName;
					}
				},{
					field: 'email',
					title: 'Email Address',
				},{
					field: 'last_seen_on',
					title: 'Last Seen On',
					template: function(row) {
						if(row.last_seen_on){
							return timeDifference(row.last_seen_on);
						}
					}
				},{
					field: 'Actions',
					title: 'Actions',
					sortable: false,
					width: 110,
					overflow: 'visible',
					autoHide: false,
					template: function() {
						return '\
						<div class="dropdown">\
							<a href="javascript:;" class="btn btn-sm btn-clean btn-icon btn-icon-sm" data-toggle="dropdown">\
	                            <i class="flaticon2-gear"></i>\
	                        </a>\
						  	<div class="dropdown-menu dropdown-menu-right">\
						    	<a class="dropdown-item" href="#"><i class="la la-edit"></i> Edit Details</a>\
						    	<a class="dropdown-item" href="#"><i class="la la-leaf"></i> Update Status</a>\
						    	<a class="dropdown-item" href="#"><i class="la la-print"></i> Generate Report</a>\
						  	</div>\
						</div>\
						<a href="javascript:;" class="btn btn-sm btn-clean btn-icon btn-icon-sm" title="Edit details">\
							<i class="flaticon2-paper"></i>\
						</a>\
						<a href="javascript:;" class="btn btn-sm btn-clean btn-icon btn-icon-sm" title="Delete">\
							<i class="flaticon2-trash"></i>\
						</a>\
					';
					},
				}],

		});

	    $('#generalSearch').on('keyup', function() {
	      datatable.search($(this).val().toLowerCase(), 'firstName');
	    });

	    $('#kt_form_status,#kt_form_type').selectpicker();

		};

		return {
			// public functions
			init: function() {
				fetchMypaUsers();
			},
		};
	}();
		
	MypaUsers.init();

	var UpangajiUsers = function() {
		// Private functions

		// basic demo
		var fetchUpangajiUsers = function() {

		var datatable = $('#upangaji-users').KTDatatable({
			// datasource definition
			data: {
				type: 'remote',
				source: {
					read: {
						url: 'https://api.upangaji.com:8444/api/users',
						//sample custom headers
						//headers: {'x-my-custokt-header': 'some value', 'x-test-header': 'the value'},
						map: function(raw) {
							// sample data mapping
							var dataSet = raw;
							if (typeof raw.data !== 'undefined') {
								dataSet = raw.data;
							}
							return dataSet;
						},
					},
				},
				pageSize: 10,
				serverPaging: true,
				serverFiltering: true,
				serverSorting: true,
			},

			// layout definition
			layout: {
				scroll: false,
				footer: false,
			},

			// column sorting
			sortable: true,

			pagination: true,

			search: {
				input: $('#generalSearch'),
			},

			// columns definition
			columns: [
				{
					field: 'pic',
					title: 'Avatar',
					width: 70,
					sortable: 'asc',
					template: function(row) {
						return '<div class="kt-widget kt-widget--user-profile-3"><div class="kt-widget__top"><div class="kt-widget__media kt-hidden-"><img src="'+row.pic+'" alt="image"></div></div></div>';
						//row.firstName + ' ' + row.lastName;
					}
				}, {
					field: 'fullName',
					title: 'Name',
					template: function(row) {
						return row.firstName + ' ' + row.lastName;
					}
				},{
					field: 'email',
					title: 'Email Address',
				},{
					field: 'last_seen_on',
					title: 'Last Seen On',
					template: function(row) {
						if(row.last_seen_on){
							return timeDifference(row.last_seen_on);
						}
					}
				},{
					field: 'Actions',
					title: 'Actions',
					sortable: false,
					width: 110,
					overflow: 'visible',
					autoHide: false,
					template: function() {
						return '\
						<div class="dropdown">\
							<a href="javascript:;" class="btn btn-sm btn-clean btn-icon btn-icon-sm" data-toggle="dropdown">\
	                            <i class="flaticon2-gear"></i>\
	                        </a>\
						  	<div class="dropdown-menu dropdown-menu-right">\
						    	<a class="dropdown-item" href="#"><i class="la la-edit"></i> Edit Details</a>\
						    	<a class="dropdown-item" href="#"><i class="la la-leaf"></i> Update Status</a>\
						    	<a class="dropdown-item" href="#"><i class="la la-print"></i> Generate Report</a>\
						  	</div>\
						</div>\
						<a href="javascript:;" class="btn btn-sm btn-clean btn-icon btn-icon-sm" title="Edit details">\
							<i class="flaticon2-paper"></i>\
						</a>\
						<a href="javascript:;" class="btn btn-sm btn-clean btn-icon btn-icon-sm" title="Delete">\
							<i class="flaticon2-trash"></i>\
						</a>\
					';
					},
				}],

		});

	    $('#generalSearch').on('keyup', function() {
	      datatable.search($(this).val().toLowerCase(), 'firstName');
	    });

	    $('#kt_form_status,#kt_form_type').selectpicker();

		};

		return {
			// public functions
			init: function() {
				fetchUpangajiUsers();
			},
		};
	}();
		
	UpangajiUsers.init();

	var PayBills = function() {
		// Private functions

		// basic demo
		var fetchPayBills = function() {

		var datatable = $('#pay-bills').KTDatatable({
			// datasource definition
			data: {
				type: 'remote',
				source: {
					read: {
						url: 'https://mypa.co.ke:8443/api/pay_bills',
						//sample custom headers
						//headers: {'x-my-custokt-header': 'some value', 'x-test-header': 'the value'},
						map: function(raw) {
							// sample data mapping
							var dataSet = raw;
							if (typeof raw.data !== 'undefined') {
								dataSet = raw.data;
							}
							return dataSet;
						},
					},
				},
				pageSize: 10,
				serverPaging: true,
				serverFiltering: true,
				serverSorting: true,
			},

			// layout definition
			layout: {
				scroll: false,
				footer: false,
			},

			// column sorting
			sortable: true,

			pagination: true,

			search: {
				input: $('#generalSearch'),
			},

			// columns definition
			columns: [
				 {
					field: 'name',
					title: 'Name'
				},{
					field: 'category_name',
					title: 'Category Name',
				},{
					field: 'Actions',
					title: 'Actions',
					sortable: false,
					width: 110,
					overflow: 'visible',
					autoHide: false,
					template: function(row) {
						return '\
						<a href="#" class="btn btn-sm btn-clean btn-icon btn-icon-sm edit-pay-bill" data-title="Edit Pay Bill" data-pay-bill-id="'+row._id+'" data-content="#pay-bill-form-holder" title="Edit Pay Bill">\
							<i class="la la-edit"></i>\
						</a>\
					';
					},
				}],

		});

	    $('#generalSearch').on('keyup', function() {
	      datatable.search($(this).val().toLowerCase(), 'firstName');
	    });

	    $('#kt_form_status,#kt_form_type').selectpicker();

		};

		return {
			// public functions
			init: function() {
				fetchPayBills();
			},
		};
	}();
		
	PayBills.init();

	var TillNumbers = function() {
		// Private functions

		// basic demo
		var fetchTillNumbers = function() {

		var datatable = $('#till-numbers').KTDatatable({
			// datasource definition
			data: {
				type: 'remote',
				source: {
					read: {
						url: 'https://mypa.co.ke:8443/api/till_numbers',
						//sample custom headers
						//headers: {'x-my-custokt-header': 'some value', 'x-test-header': 'the value'},
						map: function(raw) {
							// sample data mapping
							var dataSet = raw;
							if (typeof raw.data !== 'undefined') {
								dataSet = raw.data;
							}
							return dataSet;
						},
					},
				},
				pageSize: 10,
				serverPaging: true,
				serverFiltering: true,
				serverSorting: true,
			},

			// layout definition
			layout: {
				scroll: false,
				footer: false,
			},

			// column sorting
			sortable: true,

			pagination: true,

			search: {
				input: $('#generalSearch'),
			},

			// columns definition
			columns: [
				 {
					field: 'name',
					title: 'Name'
				},{
					field: 'category_name',
					title: 'Category Name',
				},{
					field: 'Actions',
					title: 'Actions',
					sortable: false,
					width: 110,
					overflow: 'visible',
					autoHide: false,
					template: function(row) {
						return '\
						<a href="#" class="btn btn-sm btn-clean btn-icon btn-icon-sm edit-till-number" data-till-number-id="'+row._id+'" data-content="#till-number-form-holder" data-title="Edit Till Number">\
							<i class="la la-edit"></i>\
						</a>\
					';
					},
				}],

		});

	    $('#generalSearch').on('keyup', function() {
	      datatable.search($(this).val().toLowerCase(), 'firstName');
	    });

	    $('#kt_form_status,#kt_form_type').selectpicker();

		};

		return {
			// public functions
			init: function() {
				fetchTillNumbers();
			},
		};
	}();
		
	TillNumbers.init();

	function timeDifference(previous) {

	    var msPerMinute = 60 * 1000;
	    var msPerHour = msPerMinute * 60;
	    var msPerDay = msPerHour * 24;
	    var msPerMonth = msPerDay * 30;
	    var msPerYear = msPerDay * 365;
	    var current = Date.now();
	    var elapsed = current - previous;

	    if (elapsed < msPerMinute) {
	         return '<span class="kt-badge kt-badge--success kt-badge--inline kt-badge--pill">' + Math.round(elapsed/1000) + ' seconds ago </span> ';   
	    }

	    else if (elapsed < msPerHour) {
	         return '<span class="kt-badge kt-badge--success kt-badge--inline kt-badge--pill">' + Math.round(elapsed/msPerMinute) + ' minutes ago </span>';   
	    }

	    else if (elapsed < msPerDay ) {
	         return '<span class="kt-badge kt-badge--brand kt-badge--inline kt-badge--pill">' +Math.round(elapsed/msPerHour ) + ' hours ago </span>';   
	    }

	    else if (elapsed < msPerMonth) {
	        return '<span class="kt-badge kt-badge--info kt-badge--inline kt-badge--pill">' +' approximately ' + Math.round(elapsed/msPerDay) + ' days ago </span>';   
	    }

	    else if (elapsed < msPerYear) {
	        return '<span class="kt-badge kt-badge--warning kt-badge--inline kt-badge--pill">' +' approximately ' + Math.round(elapsed/msPerMonth) + ' months ago </span';   
	    }

	    else {
	        return '<span class="kt-badge kt-badge--danger kt-badge--inline kt-badge--pill">' + ' approximately ' + Math.round(elapsed/msPerYear ) + ' years ago </span>';   
	    }
	}


  	$(document).on('click','.edit-pay-bill',function(e){
  		var base_url = window.location.origin;
	    $('.processing').hide();
	    $('.submit').show();
	    var content = $(this).data('content');
	    var form_id = $(this).data('id');
	    var pay_bill_id = $(this).data('pay-bill-id');
	    KTApp.block('.modal-body',{});
    	$.ajax({
            type: "GET",
            url: 'https://mypa.co.ke:8443/api/pay_bills/get/'+pay_bill_id,
            data: {},
            success: function(response) {
            	var name = response.name;
            	var category_name = response.category_name;
            	$('.modal-body').find('input[name=name]').val(name);
            	$('.modal-body').find('input[name=category_name]').val(category_name);
	            KTApp.unblock('.modal-body');
            }
        });
	    $('input[name="process_title"]').val(form_id);
	    $('.modal-title').html($(this).data('title'));            
	    $('.modal-body').html($(content).html());
	    $('.modal-body').find('input[name=id]').val(pay_bill_id);
	    $('#modal-submit-button').html($(this).data('submit-button'));
	    $('#modal').modal({show:true});
	    $('.modal-body .modal_select2').select2({width:'100%'});
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


  	$(document).on('click','.edit-till-number',function(e){
  		var base_url = window.location.origin;
	    $('.processing').hide();
	    $('.submit').show();
	    var content = $(this).data('content');
	    var form_id = $(this).data('id');
	    var till_number_id = $(this).data('till-number-id');
	    KTApp.block('.modal-body',{});
    	$.ajax({
            type: "GET",
            url: 'https://mypa.co.ke:8443/api/till_numbers/get/'+till_number_id,
            data: {},
            success: function(response) {
            	var name = response.name;
            	var category_name = response.category_name;
            	$('.modal-body').find('input[name=name]').val(name);
            	$('.modal-body').find('input[name=category_name]').val(category_name);
	            KTApp.unblock('.modal-body');
            }
        });
	    $('input[name="process_title"]').val(form_id);
	    $('.modal-title').html($(this).data('title'));            
	    $('.modal-body').html($(content).html());
	    $('.modal-body').find('input[name=id]').val(till_number_id);
	    $('#modal-submit-button').html($(this).data('submit-button'));
	    $('#modal').modal({show:true});
	    $('.modal-body .modal_select2').select2({width:'100%'});
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


  	$(document).on('click','.launch-modal',function(e){
  		var base_url = window.location.origin;
	    $('.processing').hide();
	    $('.submit').show();
	    var content = $(this).data('content');
	    var form_id = $(this).data('id');
	    $('input[name="process_title"]').val(form_id);
	    $('.modal-title').html($(this).data('title'));            
	    $('.modal-body').html($(content).html());
	    $('#modal-submit-button').html($(this).data('submit-button'));
	    $('#modal').modal({show:true});
	   $('.modal-body #vehicle-type-form #type_id').val();
	    $('select:not(.normal)').each(function () {
            $(this).select2({
                width:'100%',
                dropdownParent: $(this).parent()
            });
        });
         $('.modal_select2').select2({width:'100%'});
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
	    $('#modal').modal({show:true});
	    //$('.modal-body .modal_select2').select2({width:'100%'});
	    $('select:not(.normal)').each(function () {
            $(this).select2({
                width:'100%',
                dropdownParent: $(this).parent()
            });
        });
        $('.modal-body').find('#data_error').html('');
        $('.modal-body #data_error').html('');
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

  	


  	$(document).on('submit','#modal-form',function(e){
  		//alert("Am in");
		$('.modal-submit-button').hide();
    	$('.modal-processing-button').show().css("display","inline-block");
    	var base_url = window.location.origin;
        var form = $(this);
        if(form.find('#pay-bill-form').is(':visible')){
        	var pay_bill_id = $('input[name=id]').val();
        	if(pay_bill_id){
	            KTApp.block('.modal-body', {});
	        	$.ajax({
	                type: "POST",
	                url: 'https://mypa.co.ke:8443/api/pay_bills/edit/'+pay_bill_id,
	                data: form.serialize(),
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
							toastr.success("You have edited the pay bill");
							PayBills.init();
	                        $('.modal').modal('hide');
	                	}else{
	                		//console.log(response.result_description);
	                		$('.data_error').each(function(){
	                            $(this).slideDown('fast',function(){
	                            	var element = $(this).find('#error-description');
	                            	element.html('Something went wrong');
	                            });
	                        });
	                	}
	                	KTApp.unblock('.modal-body');
					    $('.modal-submit-button').show();
					    $('.modal-processing-button').hide();
	                }
	            });
	       	}else{
	       		KTApp.block('.modal-body', {});
	        	$.ajax({
	                type: "POST",
	                url: 'https://mypa.co.ke:8443/api/pay_bills/create',
	                data: form.serialize(),
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
							toastr.success("You have created a new paybill");
	                        $('.modal').modal('hide');
	                	}else{
	                		console.log(response.result_description);
	                		$('.data_error').each(function(){
	                            $(this).slideDown('fast',function(){
	                            	var element = $(this).find('#error-description');
	                            	element.html("Something went wrong");
	                            });
	                        });
	                	}
	                	KTApp.unblock('.modal-body');
					    $('.modal-submit-button').show();
					    $('.modal-processing-button').hide();
	                }
	            });
	       	}
        }else if(form.find('#till-number-form').is(':visible')){
			var till_number_id = $('input[name=id]').val();
			if(till_number_id){
			    KTApp.block('.modal-body', {});
				$.ajax({
			        type: "POST",
			        url: 'https://mypa.co.ke:8443/api/till_numbers/edit/'+till_number_id,
			        data: form.serialize(),
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
							toastr.success("You have edited the till number");
							PayBills.init();
			                $('.modal').modal('hide');
			        	}else{
			        		//console.log(response.result_description);
			        		$('.data_error').each(function(){
			                    $(this).slideDown('fast',function(){
			                    	var element = $(this).find('#error-description');
			                    	element.html('Something went wrong');
			                    });
			                });
			        	}
			        	KTApp.unblock('.modal-body');
					    $('.modal-submit-button').show();
					    $('.modal-processing-button').hide();
			        }
			    });
			}else{
				KTApp.block('.modal-body', {});
				$.ajax({
			        type: "POST",
			        url: 'https://mypa.co.ke:8443/api/till_numbers/create',
			        data: form.serialize(),
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
							toastr.success("You have created a new till number");
			                $('.modal').modal('hide');
			        	}else{
			        		console.log(response.result_description);
			        		$('.data_error').each(function(){
			                    $(this).slideDown('fast',function(){
			                    	var element = $(this).find('#error-description');
			                    	element.html("Something went wrong");
			                    });
			                });
			        	}
			        	KTApp.unblock('.modal-body');
					    $('.modal-submit-button').show();
					    $('.modal-processing-button').hide();
			        }
			    });
			}
		}
		e.preventDefault();
	});
  	
  	$('.modal').on('hidden.bs.modal', function () {
  		$('.modal-body #vehicle-type-form #type_id').val();
	    $('.modal-submit-button').show();
	    $('.modal-processing-button').hide();
	    $('.modal-body #student-trip-form #data_error').html('');
	    $('.modal-body #vehicle-type-form #type_id').val();
	});

	var KTDashboard1 = function() {
			var revenueChange = function() {
		        if ($('#kt_chart_revenue_change1').length == 0) {
		            return;
		        }

		        Morris.Donut({
		            element: 'kt_chart_revenue_change',
		            data: [{
		                    label: "New York",
		                    value: 10
		                },
		                {
		                    label: "London",
		                    value: 7
		                },
		                {
		                    label: "Paris",
		                    value: 20
		                }
		            ],
		            colors: [
		                KTApp.getStateColor('success'),
		                KTApp.getStateColor('danger'),
		                KTApp.getStateColor('brand')
		            ],
		        });
		    }

		    return {
		        // Init demos
		        init: function() {
		            // init charts
		            revenueChange();
		            
		            // demo loading
		            var loading = new KTDialog({'type': 'loader', 'placement': 'top center', 'message': 'Loading ...'});
		            loading.show();

		            setTimeout(function() {
		                loading.hide();
		            }, 3000);
		        }
		    };
		}();
		KTDashboard1.init();
	
});