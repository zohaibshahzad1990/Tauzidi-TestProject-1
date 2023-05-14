<?php if(!defined('BASEPATH')) exit('You are not allowed to view this script');
class Api extends Api_Controller{

	public $response = array(
		'result_code' => 0,
		'result_description' => 'Default Response'
	);

    public $payment_status = [
        1 => "Phone Number",
        2 => "Email Address"
    ];
    public $time_start;

	function __construct(){
        parent::__construct();
        $this->load->model('parents_m');
        $this->load->model('users/users_m');
        $this->load->model('students/students_m');
        $this->load->model('vehicles/vehicles_m');
        $this->load->model('schools/schools_m');
        $this->load->model('trips/trips_m');
        $this->load->model('routes/routes_m');
        $this->load->model('guardians/guardians_m');
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

    function get_my_students(){
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
      
        $user_id = $this->token_user->_id;
        if($user_id){
            $parent = $this->parents_m->get_user_by_parent_user_id($user_id);
            if(!$parent){
                $parent = $this->guardians_m->get_user_parent_by_guardian_parent_pairings($user_id);
            }
            //print_r($parent);die;
            if($parent){
                $my_students = $this->students_m->get_user_by_students_by_parent_id($parent->parent_id);
                $school_ids = [];
                $student_ids = [];
                $vehicle_ids = [];
                $point_ids = [];
                foreach ($my_students as $key => $student) {
                    $student_ids[] = $student->student_id;
                    $school_ids[] = $student->school_id;
                    $vehicle_ids[] = $student->vehicle_id;
                    if($student->point_id){
                        $point_ids[] = $student->point_id;
                    }
                }
                $schools = $this->schools_m->get_school_options_by_ids($school_ids);
                $vehicles = $this->vehicles_m->get_vehicle_by_ids_options($vehicle_ids);
                $points_details = $this->routes_m->get_points_by_ids_array($point_ids);
                $student_arr = [];
                foreach ($my_students as $key => $student) {
                    $school_name = "";
                    $vehicle_reg = "";
                    if(array_key_exists($student->school_id, $schools)){
                        $school_name = $schools[$student->school_id];
                    }
                    if(array_key_exists($student->vehicle_id, $vehicles)){
                        $vehicle_reg = $vehicles[$student->vehicle_id];
                    }
                    $point_name = "";
                    $longitude = "";
                    $latitude = "";
                    if(array_key_exists($student->point_id, $points_details)){
                        $point_name = $points_details[$student->point_id]->name; 
                        $longitude = $points_details[$student->point_id]->longitude; 
                        $latitude = $points_details[$student->point_id]->latitude; 
                    }

                    $student_arr[] = array(
                        'user_id' => $student->id,
                        'student_id' => $student->student_id,
                        'first_name' => $student->first_name,
                        'last_name' => $student->last_name,
                        'school' => $school_name,
                        'vehicle' => $vehicle_reg,
                        'point' => $point_name,
                        'point_longitude' => $longitude,
                        'point_latitude' => $latitude,

                    );
                }
                $response = array(
                    'status' => TRUE,
                    'message'=> "Operation successfully",
                    'data' => $student_arr
                );
            }else{
                $response = array(
                    'status' => FALSE,
                    'message' =>'Parent details not found',
                );
            }
        }else{
            $post = array();
            $form_errors = $this->form_validation->error_array();
            foreach ($form_errors as $key => $value) {
              $post[$key] = $value;
            }
        } 
        $this->activity_log->logActivity($response,'get_my_students',$user_id,$this->time_start);
        echo json_encode($response);
    }

    function get_my_students_trips(){
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $user_id = $this->token_user->_id;
        if($user_id){
            $parent = $this->parents_m->get_user_by_parent_user_id($user_id);
            if(!$parent){
                $parent = $this->guardians_m->get_user_parent_by_guardian_parent_pairings($user_id);
            }
            if($parent){
                $my_students = $this->students_m->get_user_by_students_by_parent_id($parent->parent_id);
                $student_ids = [];
                foreach ($my_students as $key => $student) {
                    $student_ids[] = $student->student_id;
                }
                $trips = $this->students_m->get_students_paired_trips_details_options($student_ids);
                $trip_ids = [];
                foreach ($my_students as $key => $student) {
                    if(array_key_exists($student->student_id , $trips)){
                        foreach ($trips[$student->student_id] as $key => $trip) {
                            $trip_ids[] = $trip->trip_id;
                        }
                    }
                }
                $trip_details = $this->trips_m->get_trips_by_ids_array_details($trip_ids);
                $student_arr = [];
                $parent_arr = (object)[
                    'id' => $parent->id,
                    'parent_name' => $parent->first_name .' ' .$parent->last_name,
                    'parent_phone' => $parent->phone,
                ];
                foreach ($my_students as $key => $student) {
                    $trip_name = "";
                    $trip_time = "";
                    $trip_arr = [];
                    if(array_key_exists($student->student_id , $trips)){
                        foreach ($trips[$student->student_id] as $key => $trip) {
                            if(array_key_exists($trip->trip_id , $trip_details)){
                                $trip_name = $trip_details[$trip->trip_id]->name;
                                $trip_time = $trip_details[$trip->trip_id]->trip_time;
                                $trip_arr[] = [
                                    'trip_id' => $trip->trip_id,
                                    'name' => $trip_name,
                                    'time' => $trip_time,

                                ];
                            }
                        }
                    }
                    $student_arr[] = array(
                        
                        'user_id' => $student->id,
                        'student_id' => $student->student_id,
                        'first_name' => $student->first_name,
                        'registration_number' => $student->registration_no,
                        'last_name' => $student->last_name,
                        'trips' => $trip_arr
                    );
                }
                $response = array(
                    'status' => TRUE,
                    'message'=> "Operation successfully",
                    'data' => $student_arr,
                    'parent' => $parent_arr
                );
            }else{
                $response = array(
                    'status' => FALSE,
                    'message' =>'Parent details not found',
                );
            }
        }else{
            $post = array();
            $form_errors = $this->form_validation->error_array();
            foreach ($form_errors as $key => $value) {
              $post[$key] = $value;
            }
        } 
        $this->activity_log->logActivity($response,'get_my_students_trips',$user_id,$this->time_start);
        echo json_encode($response);
    }

    function get_student_active_trips(){
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
                'field' => 'student_id',
                'label' => 'Student Id',
                'rules' => 'trim|required',
            ),

        );
        $post = new stdClass();
        $student_id = $this->input->post('student_id');
        $this->form_validation->set_rules($validation_rules);
        $user_id = $this->token_user->_id;
        if($this->form_validation->run()){
            $student_id = $this->input->post('student_id');
            $student = $this->students_m->get_user_by_student_id($student_id);
            if($student){
                $journey = $this->students_m->get_ongoing_student_journey($student_id);
                if($journey){
                    $route = $this->routes_m->get($journey->route_id);
                    $journey_cordinates = $this->trips_m->get_last_ten_cordinates_by_journey_id($journey->journey_id);
                    $cordinates_arr = [];
                    foreach ($journey_cordinates as $key => $cordinates) {
                        $cordinates_arr[] = [
                            'longitude' => $cordinates->longitude,
                            'latitude' => $cordinates->latitude,
                            'distance' => $cordinates->distance,
                            'duration' => $cordinates->duration
                        ];
                    }
                    $journey_details = (object)[
                        'journey_id' => $journey->journey_id,
                        'name'   => $route->name,
                    ];
                    $response = array(
                        'status' => TRUE,
                        'message'=> "Operation successfully",
                        'data' => $journey_details,
                        'cordinates'=>$cordinates_arr
                    );
                }else{
                    $response = array(
                        'status' => FALSE,
                        'message' =>'You have no ongoing journey',
                    );
                }
            }else{
                $response = array(
                    'status' => FALSE,
                    'message' =>'Student details not found',
                );
            }
        }else{
            $post = array();
            $form_errors = $this->form_validation->error_array();
            foreach ($form_errors as $key => $value) {
                $post[$key] = $value;
            }
            //print_r($form_errors); die();
            $response = array(
                'status' => FALSE,
                'message' => 'Form validation failed',
                'validation_errors' => $post,
            );
        } 

        $this->activity_log->logActivity($response,'get_student_active_trips',$user_id,$this->time_start); 
        echo json_encode($response);
    }

    function check_student_active_trips(){
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
                'field' => 'student_ids[]',
                'label' => 'Student Ids',
                'rules' => 'trim|required',
            )
        );
        $post = new stdClass();
        $student_ids = $this->input->post('student_ids');
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            //stud id
            //en_route true / false
            $active_journies = [];
            $journies = $this->students_m->get_ongoing_students_by_student_ids($student_ids);
            foreach ($student_ids as $key => $student_id) {
                if(array_key_exists($student_id, $journies )){
                    $journey = $journies[$student_id];
                    $active_journies[] = (object)[
                        'student_id' => (int) $student_id,
                        'journey_id' => (int) $journey->journey_id,
                        'en_route' => TRUE
                    ];
                }else{
                    $active_journies[] = (object)[
                        'student_id' => (int) $student_id,
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
        echo json_encode($response);
    }


} ?>