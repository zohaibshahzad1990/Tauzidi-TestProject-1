<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends Admin_Controller{
	
	protected $data = array();

	function __construct(){
        parent::__construct();
        $this->load->model('transport_m'); 
        $this->load->model('user_groups/user_groups_m');  
    }

    protected $validation_rules = array(
		array(
			'field' => 'first_name',
			'label' => 'First Name',
			'rules' => 'required|trim',
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

    function create(){
    	$post = new StdClass();
        //print_r($_POST); die();
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
            $national_id = $this->input->post('national_id');
            $confirmation_code = rand(1000,9999);

            $group = $this->ion_auth->get_group_by_name('manager');
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
                'id_number'=>$this->input->post('id_number'),
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
                //$this->schools_manager->driver_school($driver);
                //$this->messaging_manager->queue_invite_sms($invitation_object);
    			$this->session->set_flashdata('success',$this->ion_auth->messages());
    		}else{
    			$this->session->set_flashdata('error',$this->ion_auth->errors()); 
    		}
    		redirect('admin/transport/listing','refresh');
    	}else{
    		foreach ($this->validation_rules as $key => $field){
                $field_name = $field['field'];
                $post->$field_name = set_value($field['field']);
            }
    	}
        $name = 'manager';
    	$this->data['groups'] = $this->users_m->get_user_group_options($name);
    	$this->data['post'] = $post;
        $this->data['id'] = '';
    	$this->data['selected_groups'] = array();
    	$this->template->title('Create Transport Manager')->build('admin/form',$this->data);
    }

    function edit($id=0){
        $id OR redirect('admin/transport/listing');
        $post = $this->ion_auth->get_user($id);
        if(!$post){
            $this->session->set_flashdata('error','Sorry, the user does not exist');
            redirect('admin/transport/listing');
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
                'id_number'=>$this->input->post('id_number'),
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
                //$this->schools_manager->driver_school($driver);
                //$this->ion_auth->remove_from_group($selected_groups, $post->id);
                //$this->ion_auth->add_to_group($groups, $post->id);
                $this->session->set_flashdata('success',$this->ion_auth->messages());
            }else{
                $this->session->set_flashdata('error',$this->ion_auth->errors()); 
            }
            redirect('admin/transport/listing','refresh');
        }else{
            foreach (array_keys($this->validation_rules) as $field){
                if (isset($_POST[$field])){
                    $post->$field = $this->form_validation->$field;
                }
            }
        }
        //$this->data['school_id'] = $school_id;
        $this->data['groups'] = $this->users_m->get_user_group_options();
        $this->data['post'] = $post;
        $this->data['id'] = $post->id;
        $this->data['selected_groups'] = $selected_groups;
        $this->template->title('Edit '.ucwords($post->first_name))->build('admin/form',$this->data);
    }

    function listing(){
        $groups = $this->user_groups_m->get_user_group_by_slug('manager');
        $group_id = $groups->id;
    	$total_rows = $this ->user_groups_m->count_by_user_groups($group_id);
        $pagination = create_pagination('admin/drivers/listing/pages', $total_rows,50,5,TRUE);
        $this->data['groups'] = $this->users_m->get_user_group_options();
        $this->data['posts'] = $this->users_m->limit($pagination['limit'])->get_all_users_in_user_groups([$group_id]);
        $this->data['pagination'] = $pagination;
    	$this->template->title('Transport Manager Listing')->build('admin/listing',$this->data);
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

    function activate($id=0,$redirect = TRUE){
        if(!$id){
            if($redirect){
                redirect('admin/transport/listing');
            }else{
                return FALSE;
            }
        }
        $post = $this->ion_auth->get_user($id);
        if(!$post){
            $this->session->set_flashdata('error','Sorry, the user does not exist');
            if($redirect){
                redirect('admin/transport/listing');
            }else{
                return FALSE;
            }
        }

        if($post->active){
            $this->session->set_flashdata('error','Sorry, the user account is already active');
            if($redirect){
                redirect('admin/transport/listing');
            }else{
                return FALSE;
            }
        }else{
            if($this->ion_auth->update($post->id, array('active'=>1,'modified_on'=>time(),'modified_by'=>$this->user->id))){
                $this->session->set_flashdata('success','User account successfuly activated');
                if($redirect){
                    redirect('admin/transport/listing');
                }else{
                    return TRUE;
                }
            }else{
                $this->session->set_flashdata('error','could not update user details');
                if($redirect){
                    redirect('admin/transport/listing');
                }else{
                    return TRUE;
                }
            }
            
        }
    }

    function disable($id=0,$redirect = TRUE){
        if(!$id){
            if($redirect){
                redirect('admin/transport/listing');
            }else{
                return FALSE;
            }
        }
        $post = $this->ion_auth->get_user($id);
        if(!$post){
            $this->session->set_flashdata('error','Sorry, the user does not exist');
            if($redirect){
                redirect('admin/transport/listing');
            }else{
                return FALSE;
            }
        }

        if(!$post->active){
            $this->session->set_flashdata('error','Sorry, the user is already disabled');
            if($redirect){
                redirect('admin/transport/listing');
            }else{
                return FALSE;
            }
        }else{
            if($this->ion_auth->update($post->id, array('active'=>0,'modified_on'=>time(),'modified_by'=>$this->user->id))){
                $this->session->set_flashdata('success','User successfuly disabled');
                if($redirect){
                    redirect('admin/transport/listing');
                }else{
                    return TRUE;
                }
            }else{
                $this->session->set_flashdata('error','could not update user details');
                if($redirect){
                    redirect('admin/transport/listing');
                }else{
                    return TRUE;
                }
            }
            
        }
    }

} ?>