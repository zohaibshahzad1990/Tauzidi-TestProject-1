<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends Admin_Controller{
	
	protected $data = array();

	protected $validation_rules = array(
		array(
			'field' => 'first_name',
			'label' => 'First Name',
			'rules' => 'required|trim',
		),
        array(
            'field' => 'school_id',
            'label' => 'School details',
            'rules' => 'trim|numeric|required',
        ),
		array(
			'field' => 'middle_name',
			'label' => 'Middle Name',
			'rules' => 'trim',
		),
		array(
			'field' => 'last_name',
			'label' => 'Last Name',
			'rules' => 'required|trim',
		),
        array(
            'field' => 'vehicle_id',
            'label' => 'Vehicle details',
            'rules' => 'trim|required',
        ),
        array(
            'field' => 'user_parent_id',
            'label' => 'Parent details',
            'rules' => 'trim|required',
        ),
        array(
            'field' => 'point_id',
            'label' => 'Drop/Pickup Point',
            'rules' => 'trim|required',
        ),
        array(
            'field' => 'registration_no',
            'label' => 'Student Registration Number',
            'rules' => 'trim|required|callback__check_if_student_number_is_unique',
        ),
        array(
            'field' => 'trip_ids[]',
            'label' => 'Trip Details',
            'rules' => 'trim|required',
        ),
	);

	function __construct(){
        parent::__construct();
        $this->load->model('users_m');
        $this->load->model('user_groups/user_groups_m');
        $this->load->model('schools/schools_m'); 
        $this->load->model('students/students_m');
        $this->load->model('parents/parents_m');  
        $this->load->model('trips/trips_m'); 
        $this->load->model('routes/routes_m'); 
        $this->load->model('vehicles/vehicles_m'); 
        $this->load->library('schools_manager');   
    }

    function _validate_user_groups(){
    	$groups = $this->input->post('groups');
    	if($groups){
    		return TRUE;
    	}else{
    		$this->form_validation->set_message('_validate_user_groups','User groups is required.');
    		return FALSE;
    	}
    }


    function create(){
        redirect('admin/students/listing');
    	$post = new StdClass();
    	$this->form_validation->set_rules($this->validation_rules);
    	if($this->form_validation->run()){
    		$first_name = $this->input->post('first_name');
    		$middle_name = $this->input->post('middle_name');
    		$last_name = $this->input->post('last_name');
    		$password = $this->input->post('password');
    		$phone = valid_phone($this->input->post('phone'));
    		$email = $this->input->post('email');
    		$groups = $this->input->post('groups');
            $phone = valid_phone($this->input->post('phone'));
            $confirmation_code = rand(1000,9999);
    		$additional_data = array(
                'username'          =>      $this->input->post('first_name'),
                'active'            =>      1, 
                'user_account_activation_code'=>$confirmation_code,
                'ussd_pin'          =>      rand(1000,9999),
                'first_name'        =>      $this->input->post('first_name'), 
                'middle_name'       =>      $this->input->post('middle_name'), 
                'last_name'         =>      $this->input->post('last_name'),
                'ussd_pin'          =>      $this->input->post('ussd_pin'),
                'created_on'        =>      time(),
                'created_by'        =>      $this->user->id,
            );
    		$id = $this->ion_auth->register($phone,$password,$email, $additional_data,$groups);
    		if($id){
                $invitation_object = (object)array(
                    'first_name'=>$this->input->post('first_name'),
                    'confirmation_code'=>$confirmation_code,
                    'user_id'=>$id,
                    'sms_to'=> $phone,
                    'message' =>'',
                    'created_by' => $this->user->id,
                    'created_on'=>time()
                );
                $this->messaging_manager->queue_invite_sms($invitation_object);
    			$this->session->set_flashdata('success',$this->ion_auth->messages());
    		}else{
    			$this->session->set_flashdata('error',$this->ion_auth->errors()); 
    		}
    		redirect('admin/students/listing','refresh');
    	}else{
    		foreach ($this->validation_rules as $key => $field){
                $field_name = $field['field'];
                $post->$field_name = set_value($field['field']);
            }
    	}
        $name = 'student';
        $this->data['school_id'] = 0;
        $this->data['schools'] = $this->schools_m->get_school_options();
    	$this->data['groups'] = $this->users_m->get_user_group_options($name);
    	$this->data['post'] = $post;
    	$this->data['selected_groups'] = array();
    	$this->template->title('Create Student')->build('admin/form',$this->data);
    }

    function edit($id=0){
        $id OR redirect('admin/parents/listing');
        $post = $this->students_m->get_user_by_student_id($id);
        if(!$post){
            $this->session->set_flashdata('error','Sorry, the student does not exist');
            redirect('admin/parents/listing');
        }


        $trips = $this->students_m->get_trips_by_student_id($id);
        $trip_ids = [];

        foreach ($trips as $key => $trip) {
            $trip_ids[] = $trip->trip_id;
        }
       
        $trip_details = $this->trips_m->get_trips_by_ids_array_options($trip_ids);
        $asiigned_trips = $this->students_m->get_student_trips_by_id_trip_id_as_key($post->student_id);
        $this->form_validation->set_rules($this->validation_rules);
        if($this->form_validation->run()){
            $input = array(
                'first_name'    => $this->input->post('first_name'),
                'last_name'     => $this->input->post('last_name'),
                'middle_name'   => $this->input->post('middle_name'),
                'phone'         => valid_phone($this->input->post('phone')),
                'email'         => $this->input->post('email'),
                'ussd_pin'      => $this->input->post('ussd_pin'),
                'modified_on'   => time(),
                'modified_by'   => $this->ion_auth->get_user()->id,
            );
            
            $update = $this->ion_auth->update($post->id, $input);
            if($update){
                $school_id = $this->input->post('school_id');
                $vehicle_id = $this->input->post('vehicle_id');
                $user_parent_id = $this->input->post('user_parent_id');
                $registration_no = $this->input->post('registration_no');
                $parent = $this->parents_m->get_user_by_parent_user_id($user_parent_id);
                $student_update = array(
                    'parent_id' => $parent ? $parent->parent_id : $post->parent_id,
                    'user_parent_id' =>$user_parent_id ? $user_parent_id  :$post->user_parent_id,
                    'school_id'=> $this->input->post('school_id'),
                    'vehicle_id'=>$this->input->post('vehicle_id'),  
                    'registration_no' => $registration_no ? $registration_no : $post->registration_no,            
                    'active' =>1,
                    'point_id' => $this->input->post('point_id'),
                    'modified_on'=>time(),
                    'modified_by'=> $this->user->id,
                );
                if($update =  $this->students_m->update($post->student_id, $student_update)){ 
                    $trips_post = $this->input->post('trip_ids');
                    if(count($trips_post) > 0 ){
                        foreach ($trips_post as $key => $trip_id) {
                            if(array_key_exists($trip_id, $asiigned_trips)){
                                //update
                                $update_id = $asiigned_trips[$trip_id]->id;
                                $update_student_trip = array(
                                    'trip_id' => $trip_id,
                                    'student_id' =>$post->student_id,
                                    'school_id' =>$school_id,
                                    'vehicle_id' =>$vehicle_id,
                                    'route_id' =>'',
                                    'parent_id' => $parent ? $parent->parent_id : $post->parent_id,
                                    //'user_parent_id' =>$user_parent_id ? $user_parent_id  :$post->user_parent_id,
                                    'active' =>1,
                                    'modified_on' => time(),
                                    'modified_by' => $this->user->id,
                                );
                                $this->students_m->update_student_trips($update_id,$update_student_trip);
                            }else{
                                $student_trips = array(
                                    'trip_id' => $trip_id,
                                    'student_id' =>$post->student_id,
                                    'school_id' =>$school_id,
                                    'vehicle_id' =>$vehicle_id,
                                    'route_id' =>'',
                                    'point_id' =>$this->input->post('point_id'),
                                    'parent_id' => $parent ? $parent->parent_id : $post->parent_id,
                                    //'user_parent_id' =>$user_parent_id ? $user_parent_id  :$post->user_parent_id,
                                    'active' =>1,
                                    'created_on' => time(),
                                    'created_by' => $this->user->id,
                                );
                                $this->students_m->insert_trips($student_trips);
                            }
                        }
                    }
                    $this->session->set_flashdata('success','Student details '.$this->input->post('first_name').' successfuly updated');
                    redirect('admin/students/students/'.$post->user_parent_id); 
                }else{
                    $this->session->set_flashdata('error','could not update student user details'); 
                    redirect('admin/students/edit/'.$id);               
                }
                $this->session->set_flashdata('success',$this->ion_auth->messages());
            }else{
                $this->session->set_flashdata('error',$this->ion_auth->errors()); 
            }
            redirect('admin/students/students/'.$post->parent_id,'refresh');
        }else{
            foreach (array_keys($this->validation_rules) as $field){
                if (isset($_POST[$field])){
                    $post->$field = $this->form_validation->$field;
                }
            }
        }


        
        $this->data['trip_details'] = $trip_details;
        $this->data['vehicles'] = $this->vehicles_m->get_vehicle_school_by_school_id_options($post->school_id);
        $this->data['schools'] = $this->schools_m->get_school_options();
        $this->data['post'] = $post;
        $this->data['points'] = $this->routes_m->get_routes_points_option();
        $this->data['parents'] = $this->students_m->get_parent_options_by_per_school($post->school_id);
        $this->data['trip_ids'] = $trip_ids;
       
        $this->template->title('Edit '.ucwords($post->first_name))->build('admin/form',$this->data);
    }

    function view($id=0){
        $id OR redirect('admin/students/listing');
        $post = $this->students_m->get_user_by_student_id($id);
        if(!$post){
            $this->session->set_flashdata('error','Sorry, the student does not exist');
            redirect('admin/parents/listing');
        }

        $trips = $this->students_m->get_trips_by_student_id($id);
        $trip_ids = [];

        foreach ($trips as $key => $trip) {
            $trip_ids[] = $trip->trip_id;
        }
        $trip_details = $this->trips_m->get_trips_by_ids_array_options($trip_ids);
        $asiigned_trips = $this->students_m->get_student_trips_by_id_trip_id_as_key($post->student_id);
        $this->data['trip_details'] = $trip_details;
        $this->data['vehicles'] = $this->vehicles_m->get_vehicle_school_by_school_id_options($post->school_id);
        $this->data['schools'] = $this->schools_m->get_school_options();
        $this->data['post'] = $post;
        $this->data['points'] = $this->routes_m->get_routes_points_option();
        $this->data['parents'] = $this->students_m->get_parent_options_by_per_school($post->school_id);
        $this->data['trip_ids'] = $trip_ids;
        $this->data['posts'] =$asiigned_trips;
        $this->template->title($post->first_name . ' Trips')->build('admin/student_trips',$this->data);
       // print_r($this->data); die();

    }

    function listing(){

        $first_name = $this->input->post_get('first_name');
        $filter_parameters = array();
        if($this->input->post_get('filter') == 'filter'){
            $filter_parameters = array(
                'first_name' => $first_name
            );
        }

        $groups = $this->user_groups_m->get_user_group_by_slug('student');
        $group_id = 0;
        if($groups){
           $group_id = $groups->id; 
        }
    	$total_rows = $this ->user_groups_m->count_by_user_groups($group_id,$filter_parameters);
        $pagination = create_pagination('admin/students/listing/pages', $total_rows,50,5,TRUE);
        $this->data['groups'] = $this->users_m->get_user_group_options();
        $students = $this->users_m->limit($pagination['limit'])->get_users_in_user_groups([$group_id],$filter_parameters);
        $student_user_ids = [];
        foreach ($students as $key => $student) {
            $student_user_ids[] = $student->id;
        }
        $user_students = $this->students_m->get_students_by_student_user_ids($student_user_ids);
        //print_r($user_students); die();
        $vehicle_ids = [];
        $school_ids = [];
        $user_parent_ids = [];
        foreach ($user_students as $key => $user_student) {
            $vehicle_ids[] = $user_student->vehicle_id;
            $school_ids[] = $user_student->school_id;
            if($user_student->user_parent_id){
                $user_parent_ids[] = $user_student->user_parent_id;
            }
        }
        $this->data['schools'] = $this->schools_m->get_school_options_by_ids($school_ids);
        $this->data['vehicles'] = $this->vehicles_m->get_vehicle_by_ids_options($vehicle_ids);
        $this->data['posts'] = $students;
        $this->data['user_students'] = $user_students;
        $this->data['pagination'] = $pagination;
        $this->data['parents'] = $this->parents_m->get_parent_options_by_user_ids($user_parent_ids);
    	$this->template->title('Students')->build('admin/listing',$this->data);
    }

    function students($id =0){
        $parent_user = $this->parents_m->get_user_by_parent_user_id($id);
        if(!$parent_user){
            redirect('admin/students/listing');
        } 
        $total_rows = 20;
        $pagination = create_pagination('admin/students/listing/pages', $total_rows,100,5,TRUE);
        $this->data['posts'] = array();
        $this->data['pagination'] = $pagination;
        
        $this->data['parent_id'] = $parent_user->parent_id;
        $this->data['parent_user'] = $parent_user;
        $this->data['schools'] = $this->schools_m->get_school_options();
        $this->data['vehicles'] = array();
        $this->data['trips'] = array();
        $this->data['points'] = array();
        $this->template->title('Students')->build('admin/students',$this->data);
    }


    function student_options(){
        $vehicle_id = $this->input->post_get('vehicle_id');
        $total_rows = $this->students_m->count_vehicles_per_vehicle($vehicle_id);;
        $pagination = create_pagination('admin/students/student_options/pages', $total_rows,100,5,TRUE);
        $vehicle_ids = [];
        $students = $this->students_m->limit($pagination['limit'])->get_students_per_vehicle($vehicle_id);
        $student_user_ids = [];
        $vehicle = $this->vehicles_m->get($vehicle_id);
        $school_ids = [];
        $user_parent_ids = [];

        foreach ($students as $key => $student) {
            $student_user_ids[] = $student->user_id;
            $school_ids[] = $student->school_id;
            if($student->user_parent_id){
                $user_parent_ids[] = $student->user_parent_id;
            }
            $vehicle_ids[] = $student->vehicle_id;
        }
        $user_students = $this->students_m->get_user_options_by_student_ids($student_user_ids);
        $this->data['schools'] = $this->schools_m->get_school_options_by_ids($school_ids);
        $vehicles = $this->vehicles_m->get_vehicle_by_ids_options($vehicle_ids);
        $this->data['vehicles'] = $vehicles;
        $this->data['posts'] = $students;
        $this->data['user_students'] = $user_students;
        $this->data['pagination'] = $pagination;
        $this->data['parents'] = $this->parents_m->get_parent_options_by_user_ids($user_parent_ids);
        $this->template->title('Students')->build('admin/student_options',$this->data);
    }

    function disable($id=0,$redirect = TRUE){
        if(!$id){
            if($redirect){
                redirect('admin/students/listing');
            }else{
                return FALSE;
            }
        }
        $post = $this->ion_auth->get_user($id);
        if(!$post){
            $this->session->set_flashdata('error','Sorry, the user does not exist');
            if($redirect){
                redirect('admin/users/listing');
            }else{
                return FALSE;
            }
        }

        if(!$post->active){
            $this->session->set_flashdata('error','Sorry, the user is already disabled');
            if($redirect){
                redirect('admin/users/listing');
            }else{
                return FALSE;
            }
        }else{
            if($this->ion_auth->update($post->id, array('active'=>0,'modified_on'=>time(),'modified_by'=>$this->user->id))){
                $this->session->set_flashdata('success','User successfuly disabled');
                if($redirect){
                    redirect('admin/users/listing');
                }else{
                    return TRUE;
                }
            }else{
                $this->session->set_flashdata('error','could not update user details');
                if($redirect){
                    redirect('admin/users/listing');
                }else{
                    return TRUE;
                }
            }
            
        }
    }

    function activate($id=0,$redirect = TRUE){
        if(!$id){
            if($redirect){
                redirect('admin/students/listing');
            }else{
                return FALSE;
            }
        }
        $post = $this->ion_auth->get_user($id);
        if(!$post){
            $this->session->set_flashdata('error','Sorry, the user does not exist');
            if($redirect){
                redirect('admin/students/listing');
            }else{
                return FALSE;
            }
        }

        if($post->active){
            $this->session->set_flashdata('error','Sorry, the user account is already active');
            if($redirect){
                redirect('admin/students/listing');
            }else{
                return FALSE;
            }
        }else{
            if($this->ion_auth->update($post->id, array('active'=>1,'modified_on'=>time(),'modified_by'=>$this->user->id))){
                $this->session->set_flashdata('success','User account successfuly activated');
                if($redirect){
                    redirect('admin/students/listing');
                }else{
                    return TRUE;
                }
            }else{
                $this->session->set_flashdata('error','could not update user details');
                if($redirect){
                    redirect('admin/students/listing');
                }else{
                    return TRUE;
                }
            }
            
        }
    }

    function action(){
        $btnAction = $this->input->post('btnAction');
        $action_to = $this->input->post('action_to');
        if($action_to){
            foreach ($action_to as $id) {
                if($btnAction=='bulk_disable'){
                    $this->disable($id,FALSE);
                }else if ($btnAction=='bulk_activate') {
                    $this->activate($id,FALSE);
                }
            }
        }
        redirect('admin/students/listing');
    }

    function ajax_search_options(){
        $this->users_m->get_search_options();
    }

    function _check_if_student_number_is_unique(){
        $registration_no = $this->input->post('registration_no');
        $student_id = $this->input->post('student_id');
        if($student = $this->students_m->get_student_by_registration_number($registration_no)){
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