<?php if(!defined('BASEPATH')) exit('You are not allowed to view this script');
class Api extends Api_Controller{

	public $response = array(
		'result_code' => 0,
		'result_description' => 'Default Response'
	);
     public $time_start;

	function __construct(){
        parent::__construct();
        $this->load->model('vehicles/vehicles_m');
        $this->load->model('schools/schools_m');
        $this->load->model('trips/trips_m');
        $this->load->model('routes/routes_m');
        $this->load->model('trips/trips_m');
        $this->time_start = microtime(true);
    }

    public function _remap($method, $params = array()){
        if(method_exists($this, $method)){
            return call_user_func_array(array($this, $method), $params);
        }
       $this->output->set_status_header('404');
       header('Content-Type: application/json');
       $file = file_get_contents('php://input')?(array)json_decode(file_get_contents('php://input')):array();
       echo json_encode(
        array(
            'status' =>  404,
            'message' =>  'The endpoint cannot be found: '.$this->uri->uri_string(),
        ));
    }


    public function get_vehicle_details(){
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        
        $user_id = $this->token_user->_id;
        if($driver = $this->users_m->get_user_driver($user_id)){
            $vehicle = $this->vehicles_m->get($driver->vehicle_id);
            if($vehicle){
                $data = array(
                    'id' => $vehicle->id,
                    'registration' => $vehicle->registration,
                    'type' => $vehicle->type,
                    'capacity' => $vehicle->capacity
                );
                $response = array(
                        'status' => TRUE,
                        'message' => 'Operation successfully',
                        'data' => $data
                );
            }else{
                $response = array(
                    'status' =>FALSE,
                    'message' => 'Error fetching vehicle details: ',
                );
            }
        }else{
            $response = array(
                'status' =>FALSE,
                'message' => 'Error getting driver details: '.$this->session->warning,
            );
        }
        $this->activity_log->logActivity($response,'',$user_id,$this->time_start);
        echo json_encode($response);
    }

    public function get_vehicle_route_details(){
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        
        $user_id = $this->token_user->_id;
        $vehicle_id = $this->input->post('vehicle_id');
        if($vehicle_id){
            $vehicle = $this->vehicles_m->get($vehicle_id);
            if($vehicle){
                $driver = $this->users_m->get_user_by_vehicle_id($vehicle_id);
                
                if($driver){
                    $trip_vehicles = $this->vehicles_m->get_trips_per_vehicle($vehicle_id);
                    $trip_ids = [];
                    $route_ids = [];
                    foreach ($trip_vehicles as $key => $trip) {
                        $trip_ids[] = $trip->trip_id;
                        $route_ids[] = $trip->route_id;
                    }
                    $routes = $this->routes_m->get_routes_by_ids_array($route_ids);
                    $trips = $this->trips_m->get_trips_by_ids_array($trip_ids);
                    $points = $this->routes_m->get_points_by_route_ids_array_as_route_array($route_ids);

                    $driver_details = (object)[
                        'id' => $driver->id,
                        'name' => $driver->first_name .' '. $driver->last_name,
                        'phone' => $driver->phone
                    ];
                    //print_r($driver); 
                    //print_r($trips); 
                    //print_r($trip_vehicles); //die();
                    $count = 0;
                    $results = [];
                    foreach ($trip_vehicles as $key => $post) {
                        $count = 1+ $count;
                        $trip =  $post;
                        $from = '';
                        $destination = "";
                        $start_longitude = "";
                        $start_latitude = "";
                        $destination_longitude = "";
                        $destination_latitude = "";
                        $distance = "";
                        $duration = "";
                        $trip_name = '';
                        $trip_time = '';
                        $is_reverse = 0;
                        $vehicle_details = (object)[];
                        if(array_key_exists($trip->trip_id,$trips)){
                            $trip_name = $trips[$trip->trip_id]->name;
                            $trip_time =  $trips[$trip->trip_id]->trip_time;
                            $is_reverse = $trips[$trip->trip_id]->is_reverse;
                        }
                        //print_r($is_reverse); die();
                        if(array_key_exists($trip->route_id,$routes)){
                            if($is_reverse == 0){
                                $from = $routes[$trip->route_id]->start_point;
                                $destination = $routes[$trip->route_id]->end_point;
                                $start_longitude = $routes[$trip->route_id]->start_longitude;
                                $start_latitude = $routes[$trip->route_id]->start_latitude;
                                $destination_longitude = $routes[$trip->route_id]->destination_longitude;
                                $destination_latitude = $routes[$trip->route_id]->destination_latitude;
                            }else{
                                $from = $routes[$trip->route_id]->end_point;
                                $destination = $routes[$trip->route_id]->start_point;
                                $start_longitude = $routes[$trip->route_id]->destination_longitude;
                                $start_latitude = $routes[$trip->route_id]->destination_latitude;
                                $destination_longitude = $routes[$trip->route_id]->start_longitude;
                                $destination_latitude = $routes[$trip->route_id]->start_latitude;
                            }
                            $distance = $routes[$trip->route_id]->distance;
                            $duration = $routes[$trip->route_id]->duration;

                        }

                        $start_point_arr = array( (object)[
                            'lat'=>$start_latitude,
                            'lng'=>$start_longitude
                        ]);
                        $point_arr = array();
                        if(array_key_exists($trip->route_id,$points)){
                            foreach ($points[$trip->route_id] as $key => $point) {
                                $point_arr[] = (object)[
                                    'lat'=>$point->latitude,
                                    'lng'=>$point->longitude
                                ];
                            }
                        }
                        if($is_reverse == 1){
                            $point_arr = array_reverse($point_arr);
                        }
                        $merged_arr = array_merge($start_point_arr,$point_arr);
                        $end_point_arr = array((object)[
                            'lat'=>$destination_latitude,
                            'lng'=>$destination_longitude
                        ]);


                        $results[] = array(
                            'journey_id' => (int)$trip->id,
                            'trip_id' =>(int)$trip->id,
                            'name'=>$trip_name,
                            'trip_time' =>$trip_time,
                            'from' =>$from,
                            'destination' =>$destination,
                            'start_longitude' =>$start_longitude,
                            'start_latitude' =>$start_latitude,
                            'destination_longitude' =>$destination_longitude,
                            'destination_latitude' =>$destination_latitude,
                            'distance' =>$distance,
                            'duration' =>$duration,
                            'is_drop_off' => $is_reverse ? TRUE : FALSE,
                            'points' => array_merge_recursive($merged_arr,$end_point_arr)
                        );
                    }
                    $response = array(
                            'status' => TRUE,
                            'message' => 'Operation successfully',
                            'driver' => $driver_details,
                            'data' => $results,    
                            
                    );
                }else{
                    $response = array(
                        'status' =>FALSE,
                        'message' => 'Error fetching driver details: ',
                    );
                }
            }else{
                $response = array(
                    'status' =>FALSE,
                    'message' => 'Error getting vehicle details: '.$this->session->warning,
                );
            }
        }else{
            $response = array(
                'status' =>FALSE,
                'message' => 'Error getting vehicle id details: '.$this->session->warning,
            );
        }
        $this->activity_log->logActivity($response,'',$user_id,$this->time_start);
        echo json_encode($response);
    }

    public function get_all_vehicle_details(){
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $field_name = '';
        $status = 0;
        $sort_order = '';
        $sort_field = '';
        $sort_role = 0;
        $search_field = '';
        $page_number = 0;
        $page_size = 10;
        $start = 0;
        $end = 20;
        $filter_parameters = array();
        if (isset($this->filter_params)) {
            $update = $this->filter_params;
            if(isset($update->filter)){
                $search_field = $update->filter;
            }          
            if(isset($update->sortOrder)){                    
                $sort_order = $update->sortOrder;
            }
            if(isset($update->status)){                    
                $status = $update->status;
            }
            if(isset($update->sortField)){
                $sort_field = $update->sortField;
            }

            if(isset($update->pageNumber)){
                $page_number = $update->pageNumber;
            }
            if(isset($update->pageSize)){
                $page_size = $update->pageSize;
            }
            if(isset($update->options->sortRole)){
                $sort_role = $update->options->sortRole;
            }
            $start = $page_number * $page_size;
            $end = $start + $page_size;

            $filter_parameters = array(
                "search_fields"=>$search_field,
                "sort_order"=>$sort_order,
                "sort_field"=>$sort_field,
                "status" => $status,
            );       
        } 
        $user_id = $this->token_user->_id;
        $total_rows = $this->vehicles_m->count_all_filetered_active_assigned_vehicles($filter_parameters);
        $pagination = create_custom_pagination('api',$total_rows,$page_size,$start,TRUE);
        $posts = $this->vehicles_m->limit($pagination['limit'])->get_all_filetered_active_assigned_vehicles($filter_parameters);


        $driver_ids = [];
        $route_ids = [];
        $school_ids = [];
        $vehicle_ids = [];
        $driver_ids = [];
        foreach ($posts as $key => $post) {
            $school_ids[] = $post->school_id;
            $vehicle_ids[] = $post->vehicle_id;
            $driver_ids[] = $post->id;
        }

        $schools = $this->schools_m->get_school_options_by_ids($school_ids);
        $vehicles = $this->vehicles_m->get_full_vehicle_by_ids_options($vehicle_ids);
        $drivers = $this->users_m->get_user_options_by_driver_ids($driver_ids);
        $count = 0;
        $results = [];
        foreach ($posts as $key => $post) {
            $count = 1+ $count;
            $driver_name = "";
            $phone = "";
            $school_name = '';
            $trip_time = '';
            $driver_details = (object)[];
            $vehicle_details = (object)[];

            if(array_key_exists($post->id,$drivers)){
                $driver_obj = $drivers[$post->id];
                $driver_details = (object)[
                    'id' => $driver_obj->id,
                    'name' => $driver_obj->first_name .' '. $driver_obj->last_name,
                    'phone' => $driver_obj->phone
                ];
            }

            if(array_key_exists($post->vehicle_id,$vehicles)){
                $vehicle_obj = $vehicles[$post->vehicle_id];
                $vehicle_details = (object)[
                    'id' => $vehicle_obj->id,
                    'registration' => $vehicle_obj->registration ,
                    'capacity' => $vehicle_obj->capacity
                ];
            }

            if(array_key_exists($post->school_id,$schools)){
                $school_name = $schools[$post->school_id];
            }

            $results[] = array(
                'driver' => $driver_details,
                'vehicle' => $vehicle_details,
                'school' => $school_name
            );
        }

        $response = array(
            'status' => TRUE,
            'message' => 'Operation successfully',
            'count' => $count,
            'data' => $results
        );
        $this->activity_log->logActivity($response,'',$user_id,$this->time_start);
        echo json_encode($response);
    }

    public function check_vehicles_active_trips(){
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $validation_rules = array(
            array(
                'field' => 'vehicle_ids[]',
                'label' => 'Vehicle Ids',
                'rules' => 'trim|required',
            )
        );
        $post = new stdClass();
        $vehicle_ids = $this->input->post('vehicle_ids');
        $user_id = $this->token_user->_id;
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            $active_journies = [];
            $journies = $this->trips_m->get_active_journies__by_vehicle_ids($vehicle_ids);
            foreach ($vehicle_ids as $key => $vehicle_id) {
                if(array_key_exists($vehicle_id, $journies )){
                    $journey = $journies[$vehicle_id];
                    $active_journies[] = (object)[
                        'vehicle_id' => (int) $vehicle_id,
                        'journey_id' => (int) $journey->id,
                        'en_route' => TRUE
                    ];
                }else{
                    $active_journies[] = (object)[
                        'vehicle_id' => (int) $vehicle_id,
                        'journey_id' => (int) "",
                        'en_route' => FALSE
                    ];
                }
            }
            $response = array(
                'status' => TRUE,
                'message'=> "Operation successfully",
                'data' => $active_journies
            );
        }else{
            $post = array();
            $form_errors = $this->form_validation->error_array();
            foreach ($form_errors as $key => $value) {
                $post[$key] = $value;
            }
            $response = array(
                'status' => FALSE,
                'message' => 'Form validation failed',
                'validation_errors' => $post,
            );
        } 
        $this->activity_log->logActivity($response,'',$user_id,$this->time_start);
        echo json_encode($response);
    }

     public function check_vehicles_location(){
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $validation_rules = array(
            array(
                'field' => 'vehicle_ids[]',
                'label' => 'Vehicle Ids',
                'rules' => 'trim|required',
            )
        );
        $post = new stdClass();
        $user_id = $this->token_user->_id;
        $vehicle_ids = $this->input->post('vehicle_ids');
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            $active_journies = [];
            $journies = $this->trips_m->get_journies_by_vehicle_ids($vehicle_ids);
            $vehicles = $this->vehicles_m->get_full_vehicle_by_ids_options($vehicle_ids);
            $not_active_vehicle_ids = [];
            $active_vehicle_ids = [];
            foreach ($journies as $key => $journey) {
                if($journey->status == 1){
                    $active_vehicle_ids[] = $journey->vehicle_id;
                }
            }
            
            $current_location = $this->trips_m->get_current_vehicle_cordinates_by_vehicle_ids($active_vehicle_ids);
            $vehicle_arr = [];
            foreach ($journies as $key => $journey) {
                $vehicle = '';
                if(array_key_exists($journey->vehicle_id, $vehicles )){
                    $vehicle_obj = $vehicles[$journey->vehicle_id];
                    $vehicle = array(
                        'id' => $vehicle_obj->id,
                        'registration' => $vehicle_obj->registration,
                    );
                }
                if(array_key_exists($journey->vehicle_id, $current_location )){
                    $location = $current_location[$journey->vehicle_id];
                    $active_journies = (object)[
                        'vehicle_id' => (int) $location->vehicle_id,
                        'journey_id' => (int) $location->journey_id,
                        'trip_id' => $location->trip_id,
                        'longitude'=> $location->longitude,
                        'latitude'=> $location->latitude,
                        'distance'=> $location->distance,
                        'distance_value'=> $location->distance_value,
                        'active_trip' => TRUE
                    ];
                }else{
                    $active_journies = (object)[
                        'vehicle_id' => (int) $journey->vehicle_id,
                        'journey_id' => (int) $journey->id,
                        'trip_id' => $journey->trip_id,
                        'longitude'=> $journey->destination_longitude,
                        'latitude'=> $journey->destination_latitude,
                        'distance'=> $journey->distance,
                        'distance_value'=> "",
                        'active_trip' => FALSE
                    ];
                }
                $vehicle_arr[] = array(
                    'vehicle' => $vehicle,
                    'location' => $active_journies
                );
            }
            $response = array(
                'status' => TRUE,
                'message'=> "Operation successfully",
                'data' => $vehicle_arr
            );
        }else{
            $post = array();
            $form_errors = $this->form_validation->error_array();
            foreach ($form_errors as $key => $value) {
                $post[$key] = $value;
            }
            $response = array(
                'status' => FALSE,
                'message' => 'Form validation failed',
                'validation_errors' => $post,
            );
        } 
        $this->activity_log->logActivity($response,'',$user_id,$this->time_start);
        echo json_encode($response);
    }


    function _valid_phone(){
        $phone = $this->input->post('phone');
        if(valid_phone($phone)){
            if($this->ion_auth->logged_in()){
                return TRUE;
            }else{
                return TRUE;
                /*if($this->ion_auth->identity_check(valid_phone($phone))){
                    $this->form_validation->set_message('_valid_phone','The phone number you have entered is registered to another account.');
                    return FALSE;
                }else{
                    return TRUE;
                }*/
            }
        }else{
            $this->form_validation->set_message('_valid_phone','Enter a valid Phone Number');
            return FALSE;
        }
        return TRUE;
    }

   
   

}