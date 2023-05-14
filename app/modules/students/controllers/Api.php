<?php if(!defined('BASEPATH')) exit('You are not allowed to view this script');
class Api extends Api_Controller{

	public $response = array(
        'result_code' => 0,
        'result_description' => 'Default Response'
    );

     public $time_start;

	function __construct(){
        parent::__construct();
        $this->load->model('trips/trips_m');
        $this->load->model('students/students_m');
        $this->load->model('routes/routes_m');
        $this->load->model('parents/parents_m');
        $this->load->model('sms/sms_m');
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

    public function get_user_by_token(){
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
            $user = $this->users_m->get($user_id);
            $sel_groups =  $this->users_m->get_user_groups_array_option($user_id);
            $groups = $this->users_m->get_group_options();
            if($user){
                $group_array = array();
                foreach ($sel_groups as $key => $group) {
                    $group_array[] = intval($group);
                }
                $user_details = (object) array(
                    'first_name'=> ucwords($user->first_name),
                    'middle_name'=>ucwords($user->middle_name),
                    'last_name'=>ucwords($user->last_name),
                    'email'=>$user->email,
                    'last_login'=>$user->last_login,
                    'active'=>$user->active,
                    'referral_code'=>$user->refferal_code,
                    'is_active'=>$user->is_active,
                    'phone'=>$user->phone,
                    'access_token'=>$user->access_token,
                    'is_validated'=>$user->is_validated?1:0,
                    'is_dismiss_dialogue'=>$user->is_dismiss_dialogue?1:0,
                    'roles'=>$group_array
                );
                $response = array(
                    'status' => 1,
                    'message' =>"Success",
                    'data'=>$user_details,
                );
            }else{
               $response = array(
                    'status' => 0,
                    'message' => 'User details is empty(var)',
                ); 
            }
        }else{
            $response = array(
                'status' => 0,
                'message' => 'User id variable is not sent in JSON Payload',
            );
        }
        $this->activity_log->logActivity($response,'',$user_id,$this->time_start);
        echo json_encode($response);
    }

    public function get_pending_trip_students(){
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
                'field' =>  'journey_id',
                'label' =>  'Journey Id variable',
                'rules' =>  'required',
            )
        );
        $user_id = $this->token_user->_id;
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            $journey_id = $this->input->post('journey_id');
            $journey = $this->trips_m->get_journey($journey_id);
            if($journey){
                $student_ids = [];
                $point_ids = [];
                $students = $this->students_m->get_ongoing_journeys_student_pair_details($journey_id);
                foreach ($students as $key => $student) {
                    $student_ids[] = $student->student_id;
                }
                $student_ids = array_unique($student_ids);
                $student_details = $this->students_m->get_ongoing_journeys_student_detail_options($student_ids);
                foreach ($student_details as $key => $student) {
                    if($student->point_id){
                        $point_ids[] = $student->point_id;
                    }
                }
                //print_r($point_ids); print_r(array_unique($point_ids)); die();
                $parents = $this->students_m->get_parent_details_by_student_ids_student_as_key($student_ids);
                $points_details = $this->routes_m->get_points_by_ids_array($point_ids);
                $response_arr = [];
                foreach ($students as $key => $student) {

                    if(array_key_exists($student->student_id , $student_details)){
                        $student_obj = $student_details[$student->student_id];
                        $parent_name = "";
                        $phone = "";
                        $email = "";
                        $registration_no = '';
                        if(array_key_exists($student->student_id,$parents)){
                            $parent_name = $parents[$student->student_id]->first_name . ' '.  $parents[$student->student_id]->last_name;
                            $phone = $parents[$student->student_id]->phone; 
                            $email = $parents[$student->student_id]->email; 
                        }
                        $point_name = "";
                        $longitude = "";
                        $latitude = "";
                        if(array_key_exists($student_obj->point_id, $points_details)){
                            $point_name = $points_details[$student_obj->point_id]->name; 
                            $longitude = $points_details[$student_obj->point_id]->longitude; 
                            $latitude = $points_details[$student_obj->point_id]->latitude; 
                        }
                        $response_arr[] = (object)[
                            "id" => (int) $student_obj->id,
                            "first_name"=>  $student_obj->first_name,
                            "middle_name"=>  $student_obj->middle_name,
                            "last_name" =>  $student_obj->last_name,
                            "registration_number" =>$student_obj->registration_no,
                            'parent_name' => $parent_name,
                            'parent_phone' => $phone,
                            'parent_email' => $email,
                            'point' => $point_name,
                            'longitude' => $longitude,
                            'latitude' => $latitude,
                            "is_onborded" => $student->is_onborded ? TRUE : FALSE
                        ];
                    }
                }
                $response = array(
                    'status' => TRUE,
                    'message'=> "Operation successfully",
                    'data' => $response_arr
                );
            }else{
                $response = array(
                    'status' => FALSE,
                    'message' =>'Journey details not found',
                    'time' => time(),
                );
            }
        }else{
            $post = array();
            $form_errors = $this->form_validation->error_array();
            foreach ($form_errors as $key => $value) {
                $post[$key] = $value;
            }
            $response = array(
                'status' =>FALSE,
                'message' => 'Form validation failed',
                'validation_errors' => $post,
                'time' => time(),
            );
        }
        $this->activity_log->logActivity($response,'',$user_id,$this->time_start);
        echo json_encode($response);
    }

    public function get_students_per_vehicle(){
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
                'field' => 'vehicle_id',
                'label' => 'Vehicle Id',
                'rules' => 'trim|required',
            )
        );
        $user_id = $this->token_user->_id;
        $post = new stdClass();
        $vehicle_id = $this->input->post('vehicle_id');
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            $field_name = '';
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
                );       
            } 
            $total_rows = $this->students_m->count_vehicles_per_vehicle($vehicle_id,$filter_parameters);
            $pagination = create_custom_pagination('api',$total_rows,$page_size,$start,TRUE);
            $posts = $this->students_m->limit($pagination['limit'])->get_students_details_per_vehicle($vehicle_id,$filter_parameters);

            $point_ids = [];
            $student_ids = [];
            foreach ($posts as $key => $post) {
                $point_ids[] = $post->point_id;
                $student_ids[] = $post->student_id;
            }
            $student_user = $this->students_m->get_user_student_id_options_by_student_ids($student_ids);
            $points_details = $this->routes_m->get_points_by_ids_array($point_ids);
            $parents = $this->students_m->get_parent_details_by_student_ids_student_as_key($student_ids);

            $student_arr = [];
            foreach ($posts as $key => $post) {
                if(array_key_exists($post->student_id , $student_user)){
                    $student_obj = $student_user[$post->student_id];
                    $parent_name = "";
                    $phone = "";
                    $email = "";
                    $registration_no = '';
                    if(array_key_exists($post->student_id,$parents)){
                        $parent_name = $parents[$post->student_id]->first_name . ' '.  $parents[$post->student_id]->last_name;
                        $phone = $parents[$post->student_id]->phone; 
                        $email = $parents[$post->student_id]->email; 
                    }
                    $point_name = "";
                    $longitude = "";
                    $latitude = "";
                    if(array_key_exists($student_obj->point_id, $points_details)){
                        $point_name = $points_details[$student_obj->point_id]->name; 
                        $longitude = $points_details[$student_obj->point_id]->longitude; 
                        $latitude = $points_details[$student_obj->point_id]->latitude; 
                    }
                    $student_arr[] = (object)[
                        "id" => (int) $student_obj->id,
                        "first_name"=>  $student_obj->first_name,
                        "middle_name"=>  $student_obj->middle_name,
                        "last_name" =>  $student_obj->last_name,
                        "registration_number" =>$student_obj->registration_no,
                        'parent_name' => $parent_name,
                        'parent_phone' => $phone,
                        'parent_email' => $email,
                        'point' => $point_name,
                        'longitude' => $longitude,
                        'latitude' => $latitude
                    ];
                }
            }
            $response = array(
                'status' => TRUE,
                'message'=> "Operation successfully",
                'data' => $student_arr
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

    public function board_student(){
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
                'field' =>  'journey_id',
                'label' =>  'Journey Id variable',
                'rules' =>  'trim|required|numeric',
            ),array(
                'field' => 'student_ids[]',
                'label' => 'Student Ids',
                'rules' =>  'trim|required|numeric',
            )
        );
        $user_id = $this->token_user->_id;
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            $user_id = $this->token_user->_id;
            $journey_id = $this->input->post('journey_id');
            $student_id = $this->input->post('student_id');

            $student_ids = $this->input->post('student_ids');
            $journey = $this->trips_m->get_journey($journey_id);
            if($journey){
                $student_journey = $this->students_m->get_ongoing_students_journies($student_ids,$journey_id);
                $student_details = $this->students_m->get_user_student_id_options_by_student_ids($student_ids);
                $trip = $this->trips_m->get($journey->trip_id);
                $parents = $this->students_m->get_parent_details_by_student_ids_student_as_key($student_ids);
                $count = 0;
                if($student_journey){
                    $push_notification = [];
                    $sms_notification = [];
                    foreach ($student_journey as $key => $journey) {
                        $update = array(
                            'is_onborded' => 1,
                            'modified_on'=>time(),
                            'modified_by' => $user_id
                        );

                        if($update_data = $this->students_m->update_student_journey($journey->id,$update)){
                            $count++;
                            $name = "";
                            if(array_key_exists($journey->student_id ,$student_details)){
                                $name = $student_details[$journey->student_id ]->first_name;
                            }
                            $fcm_token = '';
                            $user_parent_id = "";
                            $phone = "";
                            if(array_key_exists($journey->student_id ,$parents)){
                                $user_parent_id = $parents[$journey->student_id ]->id;
                                $fcm_token = $parents[$journey->student_id ]->fcm_token;
                                $phone = $parents[$journey->student_id ]->phone;
                            }

                            if($trip->is_reverse == 1){
                                $message = $this->sms_m->build_sms_message('student-return-board',array(
                                    'REGISTRATION' => $name
                                ));
                            }else{
                                $message = $this->sms_m->build_sms_message('student-board',array(
                                    'REGISTRATION' => $name
                                ));
                            }
                            $push_notification[] = array(
                                'is_push' =>1,
                                'fcm_token' =>$fcm_token,
                                'user_id'=>$user_parent_id,
                                'message'=>$message,
                                'created_on'=>time(),
                                'created_by'=>$user_id
                            );
                            $sms_notification[] = array(
                                'is_push' =>0,
                                'fcm_token' =>$fcm_token,
                                'user_id'=>$user_parent_id,
                                'sms_to' => $phone,
                                'message'=>$message,
                                'created_on'=>time(),
                                'created_by'=>$user_id
                            );
                        }
                    }
                    if(count($sms_notification) > 0){
                        $this->sms_m->insert_sms_queue_batch($sms_notification);
                    }
                    if(count($push_notification) > 0){
                        $this->sms_m->insert_sms_queue_batch($push_notification);
                    }
                    $response = array(
                        'status' => TRUE,
                        'message'=> "Operation successfully, ".$count." students Onboarded",
                        'time' => time(),
                    );
                }else{
                    $response = array(
                        'status' => FALSE,
                        'message' =>'Student journey details not found',
                        'time' => time(),
                    );
                }
            }else{
                $response = array(
                    'status' => FALSE,
                    'message' =>'Journey details not found',
                    'time' => time(),
                );
            }
        }else{
            $post = array();
            $form_errors = $this->form_validation->error_array();
            foreach ($form_errors as $key => $value) {
                $post[$key] = $value;
            }
            $response = array(
                'status' =>FALSE,
                'message' => 'Form validation failed',
                'validation_errors' => $post,
                'time' => time(),
            );
        }
        $this->activity_log->logActivity($response,'',$user_id,$this->time_start);
        echo json_encode($response);
    }

    public function update_student_details(){
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
                    'field' =>  'student_id',
                    'label' =>  'Student id variable',
                    'rules' =>  'required|numeric',
                ),
            array(
                    'field' =>  'first_name',
                    'label' =>  'First Name',
                    'rules' =>  'required',
                ),
            array(
                    'field' =>  'middle_name',
                    'label' =>  'Middle Name',
                    'rules' =>  '',
                ),
            array(
                    'field' =>  'last_name',
                    'label' =>  'last Name',
                    'rules' =>  'xss_clean|trim|required',
            ),
            array(
                    'field' =>  'registration_number',
                    'label' =>  'Registration number',
                    'rules' =>  'xss_clean|trim|required|callback__check_if_student_number_is_unique',
            )
            
        ); 
        $user_id = $this->token_user->_id;       
        $first_name = $this->input->post('first_name');
        $middle_name = $this->input->post('middle_name');
        $last_name = $this->input->post('last_name');
        $email = $this->input->post('email');
        $phone = $this->input->post('phone');
        $registration_no = $this->input->post('registration_number');
        $student_id = $this->input->post('student_id');
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            if($user = $this->students_m->get_user_by_student_id($student_id)){
                //$groups = $this->input->post('roles');
                $input = array(
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'middle_name' => $middle_name,
                    'phone' => $user->phone,
                    'email' => $email ? $email : $user->email,
                    'ussd_pin' => $this->input->post('ussd_pin'),
                    'modified_on' => time(),
                    'modified_by'=> $this->token_user->_id,
                );          
                $update = $this->ion_auth->update($user->id, $input);
                if($update){
                    $parent = $this->parents_m->get_user_by_parent_user_id($user->user_parent_id);
                    $student_update = array(
                        'parent_id' => $parent ? $parent->parent_id : $user->parent_id,
                        'user_parent_id' => $user->user_parent_id, 
                        'registration_no' => $registration_no ? $registration_no : $user->registration_no,            
                        'active' =>1,
                        'modified_on'=>time(),
                        'modified_by'=> $this->token_user->_id,
                    );
                    $user_details = (object) array(
                        'first_name'=> $first_name ? ucwords($first_name) : ucwords($user->first_name),
                        'middle_name'=> $middle_name ? ucwords($middle_name) : ucwords($user->middle_name),
                        'last_name'=> $last_name ? ucwords($last_name) : ucwords($user->last_name),
                        
                    );
                    if($update_two =  $this->students_m->update($user->student_id, $student_update)){
                        $response = array(
                            'status' => TRUE,
                            'message' =>'Success'.$this->ion_auth->messages(),
                            'data'=>$user_details,
                        );
                    }else{
                        $response = array(
                            'status' => FALSE,
                            'message' => $this->ion_auth->errors(),
                        );
                    }
                    
                }else{
                    $response = array(
                        'status' => FALSE,
                        'message' => $this->ion_auth->errors(),
                    );
                }
            }else{
                $response = array(
                    'status' => FALSE,
                    'message' => 'User details is not found',
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

    public function get_all_users(){
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $field_name = '';
        $sort_order = '';
        $sort_field = '';
        $sort_role = 0;
        $search_field = '';
        $page_number = 0;
        $page_size = 10;
        $start = 0;
        $end = 10;
        $filter_parameters = array();
        if (isset($this->filter_params)) {
            $update = $this->filter_params;
            if(isset($update->filter)){
                $search_field = $update->filter;
            }          
            if(isset($update->sortOrder)){                    
                $sort_order = $update->sortOrder;
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
            );       
        } 
        $total_rows = $this->users_m->count_all_filetered_active_users($filter_parameters);
        $pagination = create_custom_pagination('api',$total_rows,$page_size,$start,TRUE);
        $user_groups = $this->user_groups_m->get_user_group_options();
        $posts = $this->users_m->limit($pagination['limit'])->get_active_users($filter_parameters);
        $users_array = array();
        if($posts){
            $count = $start+1;
            $users_ids = array();
            foreach ($posts as $key => $post) {
                $users_ids[] = $post->id;
            }
            $user_roles = $this->user_groups_m->get_roles_by_user_ids($users_ids);
            foreach ($posts as $key => $post) {
                $role = isset($user_groups[$post->id])?$user_groups[$post->id]:0;
                $roles = isset($user_roles[$post->id])?$user_roles[$post->id]:array();
                $roles_array = array();
                foreach ($roles as $key => $role_id) {
                    $roles_array[] = $user_groups[$role_id]; 
                }
                if($sort_role){
                    if(array_key_exists($sort_role, array_flip($roles))){
                        $users_array[] = (object) array(
                            'id'=> $count++,
                            '_id'=>$post->id,
                            'first_name'=>$post->first_name,
                            'last_name'=>$post->last_name,
                            'phone'=>$post->phone,
                            'email'=>$post->email,
                            'roles'=>$roles,
                            'start'=>$start,
                            'end'=>$end,
                            'referral_code'=>$post->refferal_code,
                            'is_validated'=>$post->is_validated?1:0,
                            'last_login'=>$post->last_login,
                            'created_on'=>$post->created_on
                        );
                    }
                }else{
                    $users_array[] = (object) array(
                        'id'=> $count++,
                        '_id'=>$post->id,
                        'first_name'=>$post->first_name,
                        'last_name'=>$post->last_name,
                        'phone'=>$post->phone,
                        'email'=>$post->email,
                        'roles'=>$roles,
                        'start'=>$start,
                        'end'=>$end,
                        'is_validated'=>$post->is_validated?1:0,
                        'last_login'=>$post->last_login,
                        'referral_code'=>$post->refferal_code,
                        'created_on'=>$post->created_on
                    );
                }
            }
            $response = array(
                'totalCount'=>$total_rows,
                'items'=> $users_array
            );
        }else{
            $response = array(
                'totalCount'=>$total_rows,
                'items'=> []
            );
        }
        echo json_encode($response);
    }

    public function get_user_by_id(){
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $user_id = $this->input->post('_id');
        if($user_id){
            $user = $this->users_m->get($user_id);
            $sel_groups =  $this->users_m->get_user_groups_option($user_id);
            $groups = $this->users_m->get_group_options();
            if($user){
                $group_array = array();
                foreach ($sel_groups as $key => $group) {
                    $group_array[] = intval($group);
                }
                $user_details = (object) array(
                    'id'=>1,
                    '_id'=>$user->id,
                    'first_name'=> ucwords($user->first_name),
                    'middle_name'=>ucwords($user->middle_name),
                    'last_name'=>ucwords($user->last_name),
                    'email'=>$user->email,
                    'last_login'=>$user->last_login,
                    'active'=>$user->active,
                    'is_active'=>$user->is_active,
                    'phone'=>$user->phone,
                    'access_token'=>$user->access_token,
                    'is_validated'=>$user->is_validated?1:0,
                    'is_dismiss_dialogue'=>$user->is_dismiss_dialogue?1:0,
                    'roles'=>$group_array
                );
                $response = array(
                    'status' => 1,
                    'message' =>"Success",
                    'data'=>$user_details,
                );
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'User details is empty(var)',
                ); 
            }
        }else{
            $response = array(
                'status' => 0,
                'message' => 'User id variable is not sent in JSON Payload',
            );
        }
        $this->activity_log->logActivity($response,'',$user_id,$this->time_start);
        echo json_encode($response);
    }

    public function update_my_password(){        
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $validation_rules = array(
            array(
                    'field' =>  'old_password',
                    'label' =>  'Old Password',
                    'rules' =>  'required',
                ),
            array(
                    'field' =>  'password',
                    'label' =>  'Password',
                    'rules' =>  'required|min_length[6]',
                ),
            array(
                    'field' =>  'confirm_password',
                    'label' =>  'Confirm Password',
                    'rules' =>  'required|matches[password]',
                )
        );
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            $user_id = $this->user->id;
            if($this->request_id ==1){
                $old_password = base64_decode(urldecode($this->input->post('old_password')));
                $password = base64_decode(urldecode($this->input->post('password')));
                $confirm_password = base64_decode(urldecode($this->input->post('confirm_password'))); 
            }else{
                $old_password = $this->input->post('old_password');
                $password = $this->input->post('password');
                $confirm_password = $this->input->post('confirm_password');
            }
            if($this->user = $this->ion_auth->get_user($user_id)){
                $identity = valid_email($this->user->email)?$this->user->email:(valid_phone($this->user->phone)?:0);
                if($this->ion_auth->login($identity,$old_password)){
                    if($this->ion_auth->change_password($identity,$old_password,$password)){                        
                        $notification_array[] = array(
                            'subject'=>'Password change',
                            'message'=>'You have successfully updated your password',
                            'from_user'=>$this->token_user->_id,
                            'to_user_id'=>$this->token_user->_id,
                        );                                                
                        $this->notification_manager->create_bulk($notification_array);
                        //functions.notification('Password change', 'You have successfully changed your password', user_id, user_id);
                        $response = array(
                            'status' => 1,
                            'message' => 'Password successfully changed',
                            'time' => time(),
                        );
                    }else{
                        $response = array(
                            'status' => 0,
                            'message' => strtolower(strip_tags($this->ion_auth->errors())),
                            'time' => time(),
                        );
                    }
                }else{
                    $response = array(
                        'status' => 0,
                        'message' => 'Password change failed: Wrong password entered',
                        'time' => time(),
                    );
                }
            }else{
                $response = array(
                    'status' => 4,
                    'message' =>'User not registered. Kindly counter check',
                    'time' => time(),
                );
            }
        }else{
            $post = array();
            $form_errors = $this->form_validation->error_array();
            foreach ($form_errors as $key => $value) {
                $post[$key] = $value;
            }
            $response = array(
                'status' => 0,
                'message' => 'Form validation failed',
                'validation_errors' => $post,
                'time' => time(),
            );
        }

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

    function _check_if_student_number_is_unique(){
        $registration_no = $this->input->post('registration_number');
        $student_id = $this->input->post('student_id');
        if($student = $this->students_m->get_student_by_registration_number($registration_no)){
            //print_r($student); die();
            if($student->id == $student_id){
                return TRUE;
            }else{
              $this->form_validation->set_message('_check_if_student_number_is_unique','The student number is already registered to another account in the system');
              return FALSE;
            }
        }else{
          return TRUE;
        }
    }

   
   

}