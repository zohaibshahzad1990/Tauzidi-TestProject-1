<?php if(!defined('BASEPATH')) exit('You are not allowed to view this script');
class Api extends Api_Controller{

	public $response = array(
		'result_code' => 0,
		'result_description' => 'Default Response'
	);

     public $time_start;

	function __construct(){
        parent::__construct();
        $this->load->model('trips_m');
        $this->load->model('schools/schools_m');
        $this->load->model('vehicles/vehicles_m');
        $this->load->model('routes/routes_m');
        $this->load->model('sms/sms_m');
        $this->load->model('parents/parents_m');
        $this->time_start = microtime(true);
        $this->load->library('trips_manager'); 
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

    function get_my_assigned_trips(){
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
                $vehicle_trips = $this->vehicles_m->get_trips_per_vehicle($vehicle->id);

                $route_ids = [];
                $trip_ids = [];
                foreach($vehicle_trips as $trip){
                    $route_ids[] = $trip->route_id;
                    $trip_ids[] = $trip->trip_id;
                }
                $driver_details = [
                    'name'=> $driver->first_name .' '. $driver->last_name,
                    'vehicle_registration' => $vehicle->registration,
                ];
                $routes = $this->routes_m->get_routes_by_ids_array($route_ids);
                $trips = $this->trips_m->get_trips_by_ids_array($trip_ids);
                $points = $this->routes_m->get_points_by_route_ids_array_as_route_array($route_ids);
                $results = [];
                foreach ($vehicle_trips as $key => $trip) {
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
                    if(array_key_exists($trip->trip_id,$trips)){
                        $trip_name = $trips[$trip->trip_id]->name;
                        $trip_time =  $trips[$trip->trip_id]->trip_time;
                        $is_reverse = $trips[$trip->trip_id]->is_reverse;
                    }
                    
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
                        'trip_id' =>$trip->id,
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
                    'data' => $results,
                    'driver' => $driver_details
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

    function get_my_active_trips(){
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
                $active_trip = $this->trips_m->get_top_active_journey_by_driver_vehicle($driver->driver_id,$vehicle->id);
                if($active_trip){
            		$route_ids = [];
                    $trip_ids = [];
                    $route_ids[] = $active_trip->route_id;
                    $trip_ids[] = $active_trip->trip_id;

            		$routes = $this->routes_m->get_routes_by_ids_array($route_ids);
                    $trips = $this->trips_m->get_trips_by_ids_array($trip_ids);
                    $points = $this->routes_m->get_points_by_route_ids_array_as_route_array($route_ids);

            		$results = [];
                    $trip =  $active_trip;
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
        			if(array_key_exists($trip->trip_id,$trips)){
                        $trip_name = $trips[$trip->trip_id]->name;
                        $trip_time =  $trips[$trip->trip_id]->trip_time;
                        $is_reverse = $trips[$trip->trip_id]->is_reverse;
                    }
                    
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


        			$results = array(
                        'journey_id' => (int)$trip->id,
                        'trip_id' =>(int)$trip->trip_id,
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
            		$response = array(
                        'status' => TRUE,
                        'message' => 'Operation successfully',
                        'data' => $results
                    );
                }else{
                    $response = array(
                        'status' =>FALSE,
                        'message' => 'You dont have an active trip ',
                        'data' => []
                    );
                }
        	}else{
        		$response = array(
	                'status' =>FALSE,
	                'message' => 'Error fetching vehicle details: ',
	            );
        	}
        }else{
			$response = array(
                'status' =>FALSE,
                'message' => 'Error fetching driver details: '.$this->session->warning,
            );
        }
        $this->activity_log->logActivity($response,'',$user_id,$this->time_start);
        echo json_encode($response);
    }

    function start_journey(){
       foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
       
        $validation_rules = array(
            array(
                'field' => 'id',
                'label' => 'Trip id',
                'rules' => 'trim|required',
            ),
            array(
                'field' => 'longitude',
                'label' => 'Current vehicle longitude',
                'rules' => 'trim|required',
            )
            ,array(
                'field' => 'latitude',
                'label' => 'Current vehicle latitude',
                'rules' => 'trim|required',
            )
        );
        $user_id = $this->token_user->_id;
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            
            $trip_id = $this->input->post('id');
            $latitude = $this->input->post('latitude');
            $longitude = $this->input->post('longitude');
            $trip = $this->vehicles_m->get_trip_vehicle_trips_pairings_details($trip_id);
            if($trip){
                $trip->on_start_latitude = $latitude;
                $trip->on_start_longitude = $longitude;
                $trip->user_id = $user_id;
                if($journey_id = $this->trips_manager->start_journey($trip, $user_id)){
                    $response = array(
                        'status' => TRUE,
                        'message' => 'Journey Successfully initiated',
                        'data' => $journey_id
                    ); 
                }else{
                    $response = array(
                        'status' => FALSE,
                        'message' => 'Could not start a journey: '.$this->session->warning,
                    );
                }

            }else{
                $response = array(
                    'status' => FALSE,
                    'message' => "Could not get a trip ",
                );
            }
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

    function trip_cordinates(){
    	foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $validation_rules = array(
            array(
                'field' => 'journey_id',
                'label' => 'Journey',
                'rules' => 'trim|required',
            ),
            array(
                'field' => 'longitude',
                'label' => 'Journey longitude',
                'rules' => 'trim|required',
            ),
            array(
                'field' => 'latitude',
                'label' => 'Journey latitude',
                'rules' => 'trim|required',
            ),

        );
        $journey_id = $this->input->post('journey_id');
        $user_id = $this->token_user->_id;
        $this->form_validation->set_rules($validation_rules);

        if($this->form_validation->run()){
        	
        	$longitude = $this->input->post('longitude');
        	$latitude = $this->input->post('latitude');
            $distance = $this->input->post('distance');
            $distance_value = $this->input->post('distance_value');
            $duration = $this->input->post('duration');
            $duration_value = $this->input->post('duration_value');
            $input = (object)[
        		'journey_id'=> $journey_id,
        		'longitude'=> $longitude,
        		'latitude' => $latitude,
                'distance' => $distance,
                'accuracy' => $distance,
                'heading' => $this->input->post('heading'),
                'speed' => $this->input->post('speed'),
                'speed_accuracy' => $this->input->post('speed_accuracy'),
                'journey_time' => $this->input->post('journey_time'),
                'vertical_accuracy' => $this->input->post('vertical_accuracy'),
                'distance_value' => $this->input->post('distance_value'),
                'duration' => $this->input->post('duration'),
                'duration_value' => $this->input->post('duration_value'),
        		'user_id' => $user_id
        	];            
            if($cordinates = $this->trips_manager->journey_cordinates($input)){
                $response = array(
                    'status' =>TRUE,
                    'message' => 'Cordinates saved successfully '.$this->session->warning,
                );
            }else{
				$response = array(
                    'status' =>FALSE,
                    'message' => 'Error occured creating journey history: '.$this->session->warning,
                );
            }
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

    function active_journey_cordinates(){
       foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
       
        $validation_rules = array(
            array(
                'field' => 'journey_id',
                'label' => 'Journey id',
                'rules' => 'trim|required',
            )
        );
        $user_id = $this->token_user->_id;
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            $user_id = $this->token_user->_id;
            $journey_id = $this->input->post('journey_id');
            $journey = $this->trips_m->get_journey($journey_id);
            if($journey){
                
                $driver = $this->users_m->get_user_by_driver_id($journey->driver_id);
                if($driver){   
                    $trip = $this->trips_m->get($journey->trip_id);
                    $route = $this->routes_m->get($journey->route_id);

                    $route_ids[] = $journey->route_id;
                    $points = $this->routes_m->get_points_by_route_ids_array_as_route_array($route_ids);
                    
                    $results = [];
                    $from = '';
                    $destination = "";
                    $start_longitude = "";
                    $start_latitude = "";
                    $destination_longitude = "";
                    $destination_latitude = "";
                    $distance = "";
                    $duration = "";

                    $trip_name = $trip->name;
                    $trip_time =  $trip->trip_time;
                    $is_reverse = $trip->is_reverse;
                    if($is_reverse == 0){
                        $from = $route->start_point;
                        $destination = $route->end_point;
                        $start_longitude = $route->start_longitude;
                        $start_latitude = $route->start_latitude;
                        $destination_longitude = $route->destination_longitude;
                        $destination_latitude = $route->destination_latitude;
                    }else{
                        $from = $route->end_point;
                        $destination = $route->start_point;
                        $start_longitude = $route->destination_longitude;
                        $start_latitude = $route->destination_latitude;
                        $destination_longitude = $route->start_longitude;
                        $destination_latitude = $route->start_latitude;
                    }
                    $distance = $route->distance;
                    $duration = $route->duration;


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

                    $trip_details = (object)[
                        'trip_id' => (int)$trip->id,
                        'journey_id' => (int) $journey_id,
                        'name' => $route->name,
                        'start_latitude' => $route->start_latitude,
                        'start_longitude' => $route->start_longitude,
                        'destination_longitude' => $route->destination_longitude,
                        'destination_latitude' => $route->destination_latitude,
                        'distance' => $route->distance,
                        'duration' => $route->duration,
                        'points' => array_merge_recursive($merged_arr,$end_point_arr)
                    ]; 
                                    
                    $cordinate = $this->trips_m->get_lastest_cordinates($journey_id,$driver->driver_id);
                    $cordinate_resp = (object)array(
                        'id'=> (int) $cordinate->id,
                        'longitude'=>$cordinate->longitude,
                        'latitude'=>$cordinate->latitude,
                        'distance' => $cordinate->distance,
                        'heading' => $cordinate->heading,
                        'speed' => $cordinate->speed,
                        'distance_value' => $cordinate->distance_value,
                        'duration' => $cordinate->duration,
                        'duration_value' => $cordinate->duration_value
                    ); 
                    $res = (object)[
                        "trip_information" => $trip_details,
                        "current_cordinates"=> $cordinate_resp
                    ];
                    $response = array(
                        'status' => TRUE,
                        'message' => 'Operation successfully',
                        'data' => $res
                    );
                    
                }else{
                    $response = array(
                        'status' => FALSE,
                        'message' => "Could not get driver details ",
                    );
                }
            }else{
                $response = array(
                    'status' => FALSE,
                    'message' => "Could not get journey details ",
                );
            }
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

    function end_journey(){
       foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
       
        $validation_rules = array(
            array(
                'field' => 'journey_id',
                'label' => 'Journey id',
                'rules' => 'trim|required',
            ),
            array(
                'field' => 'longitude',
                'label' => 'Current vehicle longitude',
                'rules' => 'trim|required',
            )
            ,array(
                'field' => 'latitude',
                'label' => 'Current vehicle latitude',
                'rules' => 'trim|required',
            )
        );
        $user_id = $this->token_user->_id;
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            $user_id = $this->token_user->_id;
            $journey_id = $this->input->post('journey_id');
            $journey = $this->trips_m->get_active_journey($journey_id);
            $latitude = $this->input->post('latitude');
            $longitude = $this->input->post('longitude');
            if($journey){
                $input = array(
                    'status'=> 2,
                    'on_end_longitude'=>$longitude,
                    'on_end_latitude'=>$latitude,
                    'modified_on'=> time(),
                    'modified_by'=> $user_id
                );

                if($update = $this->trips_m->update_journey($journey_id,$input)){
                    $student_journeys = $this->students_m->get_ongoing_journeys_active_ids_to_finish_journey_details($journey_id);
                    if(count($student_journeys) > 0 ){
                        $push_notification = [];
                        $sms_notification = [];
                        $notifications_array = [];
                        $notifications_array_driver = [];
                        $user_parent_ids = [];
                        $vehicle = $this->vehicles_m->get($journey->vehicle_id);
                        foreach ($student_journeys as $key => $active) {
                            $user_parent_ids[] = $active->user_parent_id;
                        }
                        $parents = $this->parents_m->get_parent_full_options_by_user_ids($user_parent_ids);
                        foreach ($student_journeys as $key => $active) {
                            $input_journey = array(
                                'is_journey_end'=> 1,
                                //'is_onborded'=> 2,
                                'on_end_longitude'=>$longitude,
                                'on_end_latitude'=>$latitude,
                                'modified_on'=> time(),
                                'modified_by'=> $user_id
                            );
                            $message = $this->sms_m->build_sms_message('journey-end',array(
                                'REGISTRATION' => $vehicle->registration
                            ));
                            
                            $fcm_token = '';
                            $phone = '';
                            if(array_key_exists($active->user_parent_id, $parents)){
                                $fcm_token = $parents[$active->user_parent_id]->fcm_token;
                                $phone = $parents[$active->user_parent_id]->phone;
                            }               
                            $push_notification[$active->user_parent_id] = array(
                                'is_push' =>1,
                                'fcm_token' =>$fcm_token,
                                'user_id'=>$active->user_parent_id,
                                'message'=>$message,
                                'created_on'=>time(),
                                'created_by'=>$user_id
                            );

                            $sms_notification[$active->user_parent_id] = array(
                                'is_push' =>0,
                                'fcm_token' =>$fcm_token,
                                'user_id'=>$active->user_parent_id,
                                'sms_to' => $phone,
                                'message'=>$message,
                                'created_on'=>time(),
                                'created_by'=>$user_id
                            );
                            $this->students_m->update_student_journey($active->id,$input_journey);
                        }

                        //print_r($push_notification);
                        //print_r($sms_notification); die();
                        if(count($push_notification) > 0){
                            $this->sms_m->insert_sms_queue_batch($push_notification);
                        }
                        if(count($sms_notification)){
                            $this->sms_m->insert_sms_queue_batch($sms_notification);
                        }


                    }

                    $response = array(
                        'status' => TRUE,
                        'message' => 'You have successfully ended the trip'
                    );
                }else{
                    $response = array(
                        'status' => FALSE,
                        'message' => "Could not end journey ",
                    );
                }
            }else{
                $response = array(
                    'status' => FALSE,
                    'message' => "Could not get journey details ",
                );
            }
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

    function runInParrallel(){
        die('i am in');
    }

    function get_my_past_trips(){
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
                $active_trips = $this->trips_m->get_driver_vehicle_finished_journey($driver->driver_id,$vehicle->id);
                $route_ids = [];
                $trip_ids = [];
                foreach ($active_trips as $key => $trip) {
                    $route_ids[] = $trip->route_id;
                    $trip_ids[] = $trip->trip_id;
                }
                $routes = $this->routes_m->get_routes_by_ids_array($route_ids);
                $trips = $this->trips_m->get_trips_by_ids_array($trip_ids);
                $results = [];
                foreach ($active_trips as $key => $trip) {
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
                    if(array_key_exists($trip->route_id,$routes)){
                        $from = $routes[$trip->route_id]->start_point;
                        $destination =  $routes[$trip->route_id]->end_point;
                        $start_longitude = $routes[$trip->route_id]->start_longitude;
                        $start_latitude = $routes[$trip->route_id]->start_latitude;
                        $destination_longitude = $routes[$trip->route_id]->destination_longitude;
                        $destination_latitude = $routes[$trip->route_id]->destination_latitude;
                        $distance = $routes[$trip->route_id]->distance;
                        $duration = $routes[$trip->route_id]->duration;
                    }
                    if(array_key_exists($trip->trip_id,$trips)){
                        $trip_name = $trips[$trip->trip_id]->name;
                        $trip_time =  $trips[$trip->trip_id]->trip_time;
                        $is_reverse =  $trips[$trip->trip_id]->is_reverse;
                    }
                    $results[] = array(
                        'journey_id' =>$trip->id,
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
                    );
                }
                $response = array(
                    'status' => TRUE,
                    'message' => 'Operation successfully',
                    'data' => $results
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
                'message' => 'Error fetching driver details: '.$this->session->warning,
            );
        }
        $this->activity_log->logActivity($response,'',$user_id,$this->time_start);
        echo json_encode($response);
    }

    function get_all_active_trips(){
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
        $total_rows = $this->trips_m->count_all_filetered_active_trips($filter_parameters);
        $pagination = create_custom_pagination('api',$total_rows,$page_size,$start,TRUE);
        $posts = $this->trips_m->limit($pagination['limit'])->get_all_filetered_active_trips($filter_parameters);
        //print_r($posts); die();
        $driver_ids = [];

        $route_ids = [];
        $trip_ids = [];
        $vehicle_ids = [];
        $driver_ids = [];
        foreach ($posts as $key => $post) {
            $route_ids[] = $post->route_id;
            $trip_ids[] = $post->trip_id;
            $vehicle_ids[] = $post->vehicle_id;
            $driver_ids[] = $post->driver_id;
        }

        $routes = $this->routes_m->get_routes_by_ids_array($route_ids);
        $trips = $this->trips_m->get_trips_by_ids_array($trip_ids);
        $points = $this->routes_m->get_points_by_route_ids_array_as_route_array($route_ids);
        $vehicles = $this->vehicles_m->get_full_vehicle_by_ids_options($vehicle_ids);
        $drivers = $this->users_m->get_user_options_by_driver_ids($driver_ids);
        $count = 0;
        $results = [];
        foreach ($posts as $key => $post) {
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
            $driver_details = (object)[];
            $vehicle_details = (object)[];
            if(array_key_exists($trip->trip_id,$trips)){
                $trip_name = $trips[$trip->trip_id]->name;
                $trip_time =  $trips[$trip->trip_id]->trip_time;
                $is_reverse = $trips[$trip->trip_id]->is_reverse;
            }

            if(array_key_exists($trip->driver_id,$drivers)){
                $driver_obj = $drivers[$trip->driver_id];
                $driver_details = (object)[
                    'id' => $driver_obj->id,
                    'name' => $driver_obj->first_name .' '. $driver_obj->last_name,
                    'phone' => $driver_obj->phone
                ];
            }

            if(array_key_exists($trip->vehicle_id,$vehicles)){
                $vehicle_obj = $vehicles[$trip->vehicle_id];
                $vehicle_details = (object)[
                    'id' => $vehicle_obj->id,
                    'registration' => $vehicle_obj->registration ,
                    'capacity' => $vehicle_obj->capacity
                ];
            }
            
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
                'trip_id' =>(int)$trip->trip_id,
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
                'driver' => $driver_details,
                'vehicle' => $vehicle_details,
                'points' => array_merge_recursive($merged_arr,$end_point_arr)
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

    function _check_journey_exist($id =0){
    	$journey = $this->trips_m->get_journey($id);
        if($journey){
        	return $journey;
        }else{
            return FALSE;
        }
    }

} ?>