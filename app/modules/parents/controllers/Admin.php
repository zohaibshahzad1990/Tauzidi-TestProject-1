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
            'rules' => 'trim|numeric',
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
            'rules' => 'required|trim|valid_phone|callback__is_unique_phone_number',
        ),
        array(
            'field' => 'email',
            'label' => 'User Email',
            'rules' => 'trim|valid_email|callback__is_unique_email',
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
            'rules' => 'trim|xss_clean|numeric',
        ),
    );

	function __construct(){
        parent::__construct();
        $this->load->model('users/users_m');
        $this->load->model('user_groups/user_groups_m');
        $this->load->model('parents/parents_m'); 
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
    	$post = new StdClass();
    	$this->form_validation->set_rules($this->validation_create_rules);
    	if($this->form_validation->run()){
    		$first_name = $this->input->post('first_name');
    		$middle_name = $this->input->post('middle_name');
    		$last_name = $this->input->post('last_name');
    		$password = $this->input->post('password');
    		$phone = valid_phone($this->input->post('phone'));
    		$email = $this->input->post('email');
            $phone = valid_phone($this->input->post('phone'));
            $national_id = $this->input->post('national_id');
            $confirmation_code = rand(1000,9999);
            $group = $this->ion_auth->get_group_by_name('parent');
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
                $parent = array(
                    'user_id'=>$id,
                    'school_id'=>$this->input->post('school_id'),
                    'vehicle_id'=>$this->input->post('vehicle_id'),
                    'created_on'=>time(),
                    'created_by'=>$this->user->id,
                    'active'=>1,
                );
                $this->parents_m->insert($parent);
    			$this->session->set_flashdata('success',$this->ion_auth->messages());
    		}else{
    			$this->session->set_flashdata('error',$this->ion_auth->errors()); 
    		}
    		redirect('admin/parents/listing','refresh');
    	}else{
    		foreach ($this->validation_create_rules as $key => $field){
                $field_name = $field['field'];
                $post->$field_name = set_value($field['field']);
            }
    	}
    	$this->data['post'] = $post;
    	$this->data['id'] = "";
    	$this->template->title('Add new Parent')->build('admin/form',$this->data);
    }

    function edit($id=0){
        $id OR redirect('admin/parents/listing');
        $post = $this->ion_auth->get_user($id);
        if(!$post){
            $this->session->set_flashdata('error','Sorry, the user does not exist');
            redirect('admin/parents/listing');
        }
        $selected_groups = $this->ion_auth->get_user_groups($post->id);
        $this->form_validation->set_rules($this->validation_create_rules);
        if($this->form_validation->run()){
            $groups = $this->input->post('groups');
            $input = array(
                'first_name'    => $this->input->post('first_name'),
                'last_name'     => $this->input->post('last_name'),
                'middle_name'   => $this->input->post('middle_name'),
                'phone'         => valid_phone($this->input->post('phone')),
                'email'         => $this->input->post('email'),
                'loan_limit'         => $this->input->post('loan_limit'),
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
                $parent = array(
                    'user_id'=>$post->id,
                    'created_on'=>time(),
                    'created_by'=>$this->user->id,
                    'active'=>1,
                );
                $this->add_remove_parent($parent);
                $this->session->set_flashdata('success',$this->ion_auth->messages());
            }else{
                $this->session->set_flashdata('error',$this->ion_auth->errors()); 
            }
            redirect('admin/parents/listing','refresh');
        }else{
            foreach (array_keys($this->validation_create_rules) as $field){
                if (isset($_POST[$field])){
                    $post->$field = $this->form_validation->$field;
                }
            }
        }
        $this->data['post'] = $post;
        $this->data['id'] = $post->id;
        $this->template->title('Edit '.ucwords($post->first_name))->build('admin/form',$this->data);
    }

    function add_remove_parent($parent = array()){
        $parent = (object)$parent;
        $exist = $this->parents_m->get_user_by_parent_user_id($parent->user_id);
        if(!$exist){
            $parent_new = array(
                'user_id'=>$parent->user_id,
                'created_on'=>time(),
                'created_by'=>$this->user->id,
                'active'=>1,
            );
            $this->parents_m->insert($parent_new);
        }
    }

    public function listing(){
        $first_name = $this->input->post_get('first_name');
        $phone = $this->input->post_get('phone');
        $filter_parameters = array();
        if($this->input->post_get('filter') == 'filter'){
            $filter_parameters = array(
                'first_name' => $first_name,
                'phone' =>$phone
            );
        }
        /*$total_rows = $this->parents_m->count_all_active_parents();
        $pagination = create_pagination('admin/parents/listing', $total_rows,250);
        $posts = $this->parents_m->limit($pagination['limit'])->get_all($filter_parameters);
        $user_ids = [];
        foreach ($posts as $key => $post) {
        	$user_ids[] = $post->user_id;
        }*/

        $groups = $this->user_groups_m->get_user_group_by_slug('parent');
        $group_id = $groups->id;
        $total_rows = $this ->user_groups_m->count_by_user_groups($group_id ,$filter_parameters);
        $pagination = create_pagination('admin/parents/listing/pages', $total_rows,50,5,TRUE);
        $this->data['groups'] = $this->users_m->get_user_group_options();
        $this->data['posts'] = $this->users_m->limit($pagination['limit'])->get_users_in_user_groups([$group_id] ,$filter_parameters);
        $this->data['pagination'] = $pagination;


        //$this->data['posts'] = $this->users_m->get_user_array_options($user_ids);
        //$this->data['pagination'] = $pagination;
        $this->template->title('List Parents')->build('admin/listing', $this->data);
    }

	function _is_unique_phone_number(){
        
        $phone = valid_phone($this->input->post('phone'));
        $id = $this->input->post('id');
        if($user = $this->users_m->get_user_by_phone_number($phone)){
            if($user->id == $id){
                return TRUE;
            }else{
               $this->form_validation->set_message('_is_unique_phone_number','The Phone Number is already registered to another account in the system');
                return FALSE;
            }
            
        }else{
            return TRUE;
        }
    }

	function _is_unique_email(){
	    $email = strtolower($this->input->post('email'));
        $id = $this->input->post('id');
	    if($email){
	      if($user = $this->users_m->get_user_by_email($email)){
            if($user->id == $id){
                return TRUE;
            }else{
	        
                $this->form_validation->set_message('_is_unique_email','The Email Address is already registered to another account in the system');
    	        return false;
            }
	      }elseif(!valid_email($email)){
	        $this->form_validation->set_message('_is_unique_email','The Email Address submitted not a valid email address');
	        return false;
	      }else{
	        return true;
	      }
	    }else{
	      return TRUE;
	    }
	}

} ?>