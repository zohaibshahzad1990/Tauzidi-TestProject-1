"use strict";

// Class definition
var KTDashboard = function() {
    var base_url = window.origin;
    // Revenue Change.
    // Based on Morris plugin - http://morrisjs.github.io/morris.js/
    var revenueChange = function() {
        if ($('#kt_chart_revenue_change').length == 0) {
            return;
        }

        $.ajax({
                type: "POST",
                url: base_url+'/ajax/trips/get_dashboard_stats',
                dataType: "json",
                success: function(data){
                    if(isJson(data)){
                        if(data.status == 200){
                            var response = data.data;
                            var results = []; 

                            $.each(response, function (key, value) {
                                results.push({
                                    label: value.vehicle,
                                    value: value.count
                                })
                            })

                            Morris.Donut({
                                element: 'kt_chart_revenue_change',
                                data: results,
                                colors: [
                                    KTApp.getStateColor('success'),
                                    KTApp.getStateColor('danger'),
                                    KTApp.getStateColor('brand')
                                ],
                            });
                        }                    
                    }else{
                    }
                },
                failure: function(errMsg) {
                    alert(errMsg);
                },complete: function() {
                }
            });

       
    }

    return {
        // Init demos
        init: function() {
            revenueChange();
            
            
            // demo loading
            var loading = new KTDialog({'type': 'loader', 'placement': 'top center', 'message': 'Loading ...'});
            loading.show();

            setTimeout(function() {
                loading.hide();
            }, 3000);
        }
    };

    function isJson(str) {
        try {
            JSON.parse(JSON.stringify(str))
            // /JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    }
}();

// Class initialization on page load
jQuery(document).ready(function() {
    KTDashboard.init();
});