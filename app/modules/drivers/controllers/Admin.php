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
            'label' => 'School Id',
            'rules' => 'trim|numeric|required',
        ),
        array(
            'field' => 'user_id',
            'label' => 'Driver Id',
            'rules' => 'trim|numeric|required',
        ),
        array(
            'field' => 'vehicle_id',
            'label' => 'Vehicle Id',
            'rules' => 'trim|numeric|required|callback__check_if_vehicle_allocated',
        ),
        array(
            'field' => 'phone',
            'label' => 'User Phone',
            'rules' => 'trim|valid_phone|required',
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
            'field' => 'id_number',
            'label' => 'Identity Number',
            'rules' => 'required|trim|numeric|callback__check_if_id_number_exist',
        ),
		array(
			'field' => 'phone',
			'label' => 'User Phone',
			'rules' => 'trim|valid_phone|required',
		),
		array(
			'field' => 'email',
			'label' => 'User Email',
			'rules' => 'trim|valid_email',
		),
		array(
			'field' => 'password',
			'label' => 'Password',
			'rules' => 'min_length[4]',
		),
		array(
			'field' => 'confirm_password',
			'label' => 'Confirm Password',
			'rules' => 'min_length[4]|matches[password]',
		),
		array(
			'field' => 'groups',
			'label' => 'User Groups',
			'rules' => '',
		),
	);

    protected $validation_create_rules = array(
        array(
            'field' => 'first_name',
            'label' => 'First Name',
            'rules' => 'required|trim',
        ),
        array(
            'field' => 'school_id',
            'label' => 'School Id',
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
            'field' => 'phone',
            'label' => 'User Phone',
            'rules' => 'trim|valid_phone|required',
        ),
        
        array(
            'field' => 'email',
            'label' => 'User Email',
            'rules' => 'trim|valid_email',
        ),
        array(
            'field' => 'password',
            'label' => 'Password',
            'rules' => 'min_length[4]',
        ),
        array(
            'field' => 'confirm_password',
            'label' => 'Confirm Password',
            'rules' => 'min_length[4]|matches[password]',
        ),
        array(
            'field' => 'national_id',
            'label' => 'National Identity',
            'rules' => 'trim|xss_clean|numeric',
        ),
        array(
            'field' => 'vehicle_id',
            'label' => 'Vehicle Id',
            'rules' => 'trim|xss_clean|numeric|required',
        ),
    );

	function __construct(){
        parent::__construct();
        $this->load->model('users_m');
        $this->load->model('user_groups/user_groups_m');
        $this->load->model('schools/schools_m'); 
        $this->load->model('vehicles/vehicles_m'); 
        $this->load->model('trips/trips_m');
        $this->load->model('routes/routes_m');  
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
    	$post = new StdClass();
        //print_r($_POST); die();
    	$this->form_validation->set_rules($this->validation_create_rules);
    	if($this->form_validation->run()){
    		$first_name = $this->input->post('first_name');
    		$middle_name = $this->input->post('middle_name');
    		$last_name = $this->input->post('last_name');
    		$password = $this->input->post('password');
    		$phone = valid_phone($this->input->post('phone'));
    		$email = $this->input->post('email');
    		$groups = $this->input->post('groups');
            $phone = valid_phone($this->input->post('phone'));
            $national_id = $this->input->post('national_id');
            $confirmation_code = rand(1000,9999);

            $group = $this->ion_auth->get_group_by_name('driver');
            $groups = array($group->id);

    		$additional_data = array(
                'username'          =>      $this->input->post('first_name'),
                'active'            =>      1, 
                'user_account_activation_code'=>$confirmation_code,
                'ussd_pin'          =>      rand(1000,9999),
                'is_validated'=>1,
                'is_onboarded'=>1,
                'is_complete_setup'=>1,
                'first_name'        =>      $this->input->post('first_name'), 
                'middle_name'       =>      $this->input->post('middle_name'), 
                'last_name'         =>      $this->input->post('last_name'),
                'ussd_pin'          =>      $this->input->post('ussd_pin'),
                'national_id'=>$this->input->post('national_id'),
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
                $driver = array(
                    'user_id'=>$id,
                    'school_id'=>$this->input->post('school_id'),
                    'vehicle_id'=>$this->input->post('vehicle_id'),
                    'created_on'=>time(),
                    'created_by'=>$this->user->id,
                    'active'=>1,
                );
                $this->schools_manager->driver_school($driver);
                $this->messaging_manager->queue_invite_sms($invitation_object);
    			$this->session->set_flashdata('success',$this->ion_auth->messages());
    		}else{
    			$this->session->set_flashdata('error',$this->ion_auth->errors()); 
    		}
    		redirect('admin/drivers/listing','refresh');
    	}else{
    		foreach ($this->validation_create_rules as $key => $field){
                $field_name = $field['field'];
                $post->$field_name = set_value($field['field']);
            }
    	}
        $name = 'driver';
        $this->data['school_id'] = 0;
        $this->data['schools'] = $this->schools_m->get_school_options();
    	$this->data['groups'] = $this->users_m->get_user_group_options($name);
    	$this->data['post'] = $post;
    	$this->data['selected_groups'] = array();
    	$this->template->title('Create Driver')->build('admin/form',$this->data);
    }

    function edit($id=0){
        $id OR redirect('admin/drivers/listing');
        $post = $this->ion_auth->get_user($id);
        if(!$post){
            $this->session->set_flashdata('error','Sorry, the user does not exist');
            redirect('admin/drivers/listing');
        }
        $selected_groups = $this->ion_auth->get_user_groups($post->id);
        $this->form_validation->set_rules($this->validation_rules);
        if($this->form_validation->run()){
            $groups = $this->input->post('groups');
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
            if($this->input->post('password')){
                $input = array_merge($input,array('password'=>$this->input->post('password')));
            }
            $update = $this->ion_auth->update($post->id, $input);
            if($update){
                $driver = array(
                    'user_id'=>$post->id,
                    'school_id'=>$this->input->post('school_id'),
                    'vehicle_id'=>$this->input->post('vehicle_id'),
                    'created_on'=>time(),
                    'created_by'=>$post->id,
                    'active'=>1,
                );
                $this->schools_manager->driver_school($driver);
                $this->ion_auth->remove_from_group($selected_groups, $post->id);
                $this->ion_auth->add_to_group($groups, $post->id);
                $this->session->set_flashdata('success',$this->ion_auth->messages());
            }else{
                $this->session->set_flashdata('error',$this->ion_auth->errors()); 
            }
            redirect('admin/drivers/listing','refresh');
        }else{
            foreach (array_keys($this->validation_rules) as $field){
                if (isset($_POST[$field])){
                    $post->$field = $this->form_validation->$field;
                }
            }
        }
        $this->data['schools'] = $this->schools_m->get_school_options();
        $driver = $this->users_m->get_user_driver_details($id);
        $school_id = '';
        if($driver){
            $school_id = $driver->school_id;
        }
        $this->data['school_id'] = $school_id;
        $this->data['groups'] = $this->users_m->get_user_group_options();
        $this->data['post'] = $post;
        
        $this->data['selected_groups'] = $selected_groups;
        $this->template->set_layout('admin/user.html')->title('Edit '.ucwords($post->first_name))->build('admin/form',$this->data);
    }

    function onboard($id=0){
        $id OR redirect('admin/drivers/listing');
        $post = $this->ion_auth->get_user($id);
        if(!$post){
            $this->session->set_flashdata('error','Sorry, the user does not exist');
            redirect('admin/drivers/listing');
        }
        $this->form_validation->set_rules($this->validation_rules);
        if($this->form_validation->run()){
            $input = array(
                'first_name'    => $this->input->post('first_name'),
                'last_name'     => $this->input->post('last_name'),
                'middle_name'   => $this->input->post('middle_name'),
                'id_number'=> $this->input->post('id_number'),
                'phone'         => valid_phone($this->input->post('phone')),
                'email'         => $this->input->post('email'),
                'is_onboarded' => 1,
                'is_complete_setup'=>1,
                'is_validated'=>1,
                'modified_on'   => time(),
                'modified_by'   => $this->ion_auth->get_user()->id,
            );
            $update = $this->ion_auth->update($post->id, $input);
            if($update){
                if($this->input->post('school_id')){
                    $driver = array(
                        'user_id'=>$post->id,
                        'school_id'=>$this->input->post('school_id'),
                        'vehicle_id'=>$this->input->post('vehicle_id'),
                        'created_on'=>time(),
                        'created_by'=>$post->id,
                        'active'=>1,
                    );
                    $this->schools_manager->driver_school($driver);
                }
                $this->session->set_flashdata('success',$this->ion_auth->messages());
            }else{
                $this->session->set_flashdata('error',$this->ion_auth->errors()); 
            }
            redirect('admin/drivers/listing','refresh');
        }else{
            foreach (array_keys($this->validation_rules) as $field){
                if (isset($_POST[$field])){
                    $post->$field = $this->form_validation->$field;
                }
            }
        }
        $this->data['schools'] = $this->schools_m->get_school_options();
        $driver = $this->users_m->get_user_driver_details($id);
        $school_id = '';
        if($driver){
            $school_id = $driver->school_id;
        }
        $this->data['school_id'] = $school_id;
        $this->data['groups'] = $this->users_m->get_user_group_options();
        $this->data['post'] = $post;
        $this->data['driver'] = $driver;
        $this->data['vehicles'] = $this->vehicles_m->get_school_veheicle_options($school_id);
        //print_r($this->data); die();
        $this->template->set_layout('admin/user.html')->title(' '.ucwords($post->first_name) .' Onboard Driver')->build('admin/onboard',$this->data);
    }

    function trips($id = 0){
        $id OR redirect('admin/drivers/listing');
        $driver = $this->users_m->get_user_driver($id);
        if(!$driver){
            $this->session->set_flashdata('error','Sorry, Driver does not exist');
            redirect('admin/drivers/listing');
        }
        //print_r($driver); die();
        $vehicle = $this->vehicles_m->get($driver->vehicle_id);
        $total_rows = $this ->trips_m->count_past_journeys_by_driver_id($driver->driver_id);
        $pagination = create_pagination('admin/drivers/trips/pages/'.$id, $total_rows,100,5,TRUE);

        $journies = $this->trips_m->limit($pagination['limit'])->get_driver_vehicle_finished_journey($driver->driver_id,$driver->vehicle_id);
        $route_ids = [];
        $trip_ids = [];
        foreach ($journies as $key => $journey) {
           $route_ids[] = $journey->route_id;
           $trip_ids[] = $journey->trip_id;
        }

        $this->data['routes'] = $this->routes_m->get_routes_by_ids_array($route_ids);
        //$this->data['trips'] = $this->trips_m->get_trips_by_ids_array($trip_ids);
        $this->data['pagination'] = $pagination;
        $this->data['vehicle'] = $vehicle;
        $this->data['driver'] = $driver;
        $this->data['journies'] = $journies;
        //print_r($this->data); die;
        $this->template->title('Drivers Past Trips')->build('admin/past_trips',$this->data);
    }

    

    function view($id=0){
        $id OR redirect('admin/drivers/listing');
        $post = $this->ion_auth->get_user($id);
        if(!$post){
            $this->session->set_flashdata('error','Sorry, the user does not exist');
            redirect('admin/drivers/listing');
        }
        $selected_groups = $this->ion_auth->get_user_groups($post->id);
        $this->form_validation->set_rules($this->validation_rules);
        if($this->form_validation->run()){
            $groups = $this->input->post('groups');
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
            if($this->input->post('password')){
                $input = array_merge($input,array('password'=>$this->input->post('password')));
            }
            $update = $this->ion_auth->update($post->id, $input);
            if($update){
                $driver = array(
                    'user_id'=>$post->id,
                    'school_id'=>$this->input->post('school_id'),
                    'created_on'=>time(),
                    'created_by'=>$post->id,
                    'active'=>1,
                );
                $this->schools_manager->driver_school($driver);
                $this->ion_auth->remove_from_group($selected_groups, $post->id);
                $this->ion_auth->add_to_group($groups, $post->id);
                $this->session->set_flashdata('success',$this->ion_auth->messages());
            }else{
                $this->session->set_flashdata('error',$this->ion_auth->errors()); 
            }
            redirect('admin/drivers/listing','refresh');
        }else{
            foreach (array_keys($this->validation_rules) as $field){
                if (isset($_POST[$field])){
                    $post->$field = $this->form_validation->$field;
                }
            }
        }
        $this->data['schools'] = $this->schools_m->get_school_options();
        $driver = $this->users_m->get_user_driver_details($id);
        $school_id = '';
        if($driver){
            $school_id = $driver->school_id;
        }
        $this->data['school_id'] = $school_id;
        $this->data['groups'] = $this->users_m->get_user_group_options();
        $this->data['vehicles'] = $this->vehicles_m->get_school_veheicle_options($school_id);
        $this->data['post'] = $post;
        $this->data['driver'] = $driver;
        $this->data['selected_groups'] = $selected_groups;
        //print_r($this->data);
        $this->template->set_layout('admin/user.html')->title(' '.ucwords($post->first_name))->build('admin/dashboard',$this->data);
    }

    function change_password($id = 0 ){
        $id OR redirect('admin/drivers/onboard/'.$id);
        $post = $this->ion_auth->get_user($id);
        if(!$post){
            $this->session->set_flashdata('error','Sorry, the driver does not exist');
            redirect('admin/drivers/onboard/'.$id);
        }
        $selected_groups = $this->ion_auth->get_user_groups($post->id);
        $this->form_validation->set_rules($this->validation_rules);
        if($this->form_validation->run()){
            $groups = $this->input->post('groups');
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
            if($this->input->post('password')){
                $input = array_merge($input,array('password'=>$this->input->post('password')));
            }
            $update = $this->ion_auth->update($post->id, $input);
            if($update){
                $driver = array(
                    'user_id'=>$post->id,
                    'school_id'=>$this->input->post('school_id'),
                    'created_on'=>time(),
                    'created_by'=>$post->id,
                    'active'=>1,
                );
                $this->schools_manager->driver_school($driver);
                $this->ion_auth->remove_from_group($selected_groups, $post->id);
                $this->ion_auth->add_to_group($groups, $post->id);
                $this->session->set_flashdata('success',$this->ion_auth->messages());
            }else{
                $this->session->set_flashdata('error',$this->ion_auth->errors()); 
            }
            redirect('admin/drivers/listing','refresh');
        }else{
            foreach (array_keys($this->validation_rules) as $field){
                if (isset($_POST[$field])){
                    $post->$field = $this->form_validation->$field;
                }
            }
        }
        $this->data['schools'] = $this->schools_m->get_school_options();
        $driver = $this->users_m->get_user_driver_details($id);
        $school_id = '';
        if($driver){
            $school_id = $driver->school_id;
        }
        $this->data['school_id'] = $school_id;
        $this->data['groups'] = $this->users_m->get_user_group_options();
        $this->data['vehicles'] = $this->vehicles_m->get_school_veheicle_options($school_id);
        $this->data['post'] = $post;
        $this->data['driver'] = $driver;
        $this->data['selected_groups'] = $selected_groups;
        $this->template->set_layout('admin/user.html')->title(' '.ucwords($post->first_name))->build('admin/dashboard',$this->data);
    }

    function listing(){
        $first_name = $this->input->post_get('first_name');
        $phone = $this->input->post_get('phone');
        $filter_parameters = array();
        if($this->input->post_get('filter') == 'filter'){
            $filter_parameters = array(
                'first_name' => $first_name,
                'phone' =>$phone
            );
        }
        //print_r($filter_parameters);
        //print_r($_GET); die();
        $groups = $this->user_groups_m->get_user_group_by_slug('driver');
        $group_id = $groups->id;
    	$total_rows = $this ->user_groups_m->count_by_user_groups($group_id ,$filter_parameters);
        $pagination = create_pagination('admin/drivers/listing/pages', $total_rows,50,5,TRUE);
        $this->data['groups'] = $this->users_m->get_user_group_options();
        $this->data['posts'] = $this->users_m->limit($pagination['limit'])->get_users_in_user_groups([$group_id] ,$filter_parameters);
        $this->data['pagination'] = $pagination;
    	$this->template->title('Drivers Listing')->build('admin/listing',$this->data);
    }


    function completed_trips(){
        //$groups = $this->user_groups_m->get_user_group_by_slug('driver');
        //$group_id = $groups->id;
        $total_rows = $this ->trips_m->count_all_completed_journeys();
        $pagination = create_pagination('admin/drivers/completed_trips/pages', $total_rows,50,5,TRUE);
        $journies = $this->trips_m->limit($pagination['limit'])->get_all_completed_journeys();
        $trip_ids = [];
        $vehicle_ids = [];
        $driver_ids = [];
        $route_ids = [];
        foreach ($journies as $key => $journey) {
            $trip_ids[] = $journey->trip_id;
            $vehicle_ids[] = $journey->vehicle_id;
            $driver_ids[] = $journey->driver_id;
            $route_ids[] = $journey->route_id;
        }
        $this->data['drivers'] = $this->users_m->get_user_options_by_driver_ids($driver_ids);
        $this->data['trips'] = $this->trips_m->get_trips_by_ids_array($trip_ids);
        $this->data['vehicles'] = $this->vehicles_m->get_vehicle_by_ids_options($vehicle_ids);
        $this->data['posts'] = $journies;
        $this->data['pagination'] = $pagination;
        //print_r($this->data); die();
        $this->template->title('Completed Trips')->build('admin/completed',$this->data);
    }

    function ongoing_trips(){
        $total_rows = $this ->trips_m->count_all_ongoing_journeys();
        $pagination = create_pagination('admin/drivers/ongoing_trips/pages', $total_rows,50,5,TRUE);
        $journies = $this->trips_m->limit($pagination['limit'])->get_all_active_journeys();
        $trip_ids = [];
        $vehicle_ids = [];
        $driver_ids = [];
        $route_ids = [];
        foreach ($journies as $key => $journey) {
            $trip_ids[] = $journey->trip_id;
            $vehicle_ids[] = $journey->vehicle_id;
            $driver_ids[] = $journey->driver_id;
            $route_ids[] = $journey->route_id;
        }
        $this->data['drivers'] = $this->users_m->get_user_options_by_driver_ids($driver_ids);
        $this->data['trips'] = $this->trips_m->get_trips_by_ids_array($trip_ids);
        $this->data['vehicles'] = $this->vehicles_m->get_vehicle_by_ids_options($vehicle_ids);
        $this->data['posts'] = $journies;
        $this->data['pagination'] = $pagination;
        //print_r($driver_ids);
        //print_r($this->data); die();
        $this->template->title('Ongoing Trips')->build('admin/completed',$this->data);
    }


    function _check_if_vehicle_allocated(){
        $school_id = $this->input->post('school_id');
        $vehicle_id = $this->input->post('vehicle_id');
        $user_id = $this->input->post('user_id');
        $name = $this->input->post('name');
        $id = $this->input->post('id');
        if($user = $this->users_m->get_user_by_school_vehicle($school_id,$vehicle_id)){
            if($user->user_id == $user_id){
                return TRUE;
            }else{
                $this->form_validation->set_message('_check_if_vehicle_allocated','The vehicle already assigned to another driver.');
                return FALSE;
            }
            
        }else{
            return TRUE;
        }
    }

    function _check_if_id_number_exist(){
        $id_number = $this->input->post('id_number');
        $id = $this->input->post('id');
        $user_id = $this->input->post('user_id');
        if($user = $this->users_m->get_user_by_id_number($id_number)){
            if($user->id == $user_id){
                return TRUE;
            }else{
                $this->form_validation->set_message('_check_if_id_number_exist','The Id number '.$id_number.' already exists.');
                return FALSE;
            }
            
        }else{
            return TRUE;
        }
    }

    function disable($id=0,$redirect = TRUE){
        if(!$id){
            if($redirect){
                redirect('admin/drivers/listing');
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
                redirect('admin/drivers/listing');
            }else{
                return FALSE;
            }
        }
        $post = $this->ion_auth->get_user($id);
        if(!$post){
            $this->session->set_flashdata('error','Sorry, the user does not exist');
            if($redirect){
                redirect('admin/drivers/listing');
            }else{
                return FALSE;
            }
        }

        if($post->active){
            $this->session->set_flashdata('error','Sorry, the user account is already active');
            if($redirect){
                redirect('admin/drivers/listing');
            }else{
                return FALSE;
            }
        }else{
            if($this->ion_auth->update($post->id, array('active'=>1,'modified_on'=>time(),'modified_by'=>$this->user->id))){
                $this->session->set_flashdata('success','User account successfuly activated');
                if($redirect){
                    redirect('admin/drivers/listing');
                }else{
                    return TRUE;
                }
            }else{
                $this->session->set_flashdata('error','could not update user details');
                if($redirect){
                    redirect('admin/drivers/listing');
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
        redirect('admin/drivers/listing');
    }

    function ajax_search_options(){
        $this->users_m->get_search_options();
    }


}