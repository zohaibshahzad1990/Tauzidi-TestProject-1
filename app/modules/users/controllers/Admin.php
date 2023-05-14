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
            'field' => 'loan_limit',
            'label' => 'Loan Limit',
            'rules' => 'trim|currency|numeric',
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
			'rules' => 'trim|valid_phone',
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
			'rules' => 'callback__validate_user_groups',
		),
	);

	function __construct(){
        parent::__construct();
        $this->load->model('users_m');
        $this->load->model('parents/parents_m');
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

    function index(){
        $this->template->title('User Admin Panel')->build('admin/index');
    }

    function create(){
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

    		$additional_data = array(
                'username'          =>      $this->input->post('first_name'),
                'active'            =>      1, 
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
    			$this->session->set_flashdata('success',$this->ion_auth->messages());
    		}else{
    			$this->session->set_flashdata('error',$this->ion_auth->errors()); 
    		}
    		redirect('admin/users/listing','refresh');
    	}else{
    		foreach ($this->validation_rules as $key => $field){
                $post->$field['field'] = set_value($field['field']);
            }
    	}
    	$this->data['groups'] = $this->users_m->get_user_group_options();
    	$this->data['post'] = $post;
    	$this->data['selected_groups'] = array();
    	$this->template->title('Create User')->build('admin/form',$this->data);
    }

    function edit($id=0){
        $id OR redirect('admin/users/listing');
        $post = $this->ion_auth->get_user($id);
        if(!$post){
            $this->session->set_flashdata('error','Sorry, the user does not exist');
            redirect('admin/users/listing');
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
                $this->ion_auth->remove_from_group($selected_groups, $post->id);
                $this->ion_auth->add_to_group($groups, $post->id);
                $group = $this->ion_auth->get_user_group_option_details($post->id);
                //print_r($group); die();
                if(array_key_exists('parent',$group)){
                    $parent = array(
                        'user_id'=>$post->id,
                        'created_on'=>time(),
                        'created_by'=>$this->user->id,
                        'active'=>1,
                    );
                    $this->add_remove_parent($parent);
                }
                
                $this->session->set_flashdata('success',$this->ion_auth->messages());
            }else{
                $this->session->set_flashdata('error',$this->ion_auth->errors()); 
            }
            redirect('admin/users/listing','refresh');
        }else{
            foreach (array_keys($this->validation_rules) as $field){
                if (isset($_POST[$field])){
                    $post->$field = $this->form_validation->$field;
                }
            }
        }
        $this->data['groups'] = $this->users_m->get_user_group_options();
        $this->data['post'] = $post;
        $this->data['selected_groups'] = $selected_groups;
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

    function listing(){
    	$total_rows = $this ->users_m->count_all_active_users();
        $pagination = create_pagination('admin/users/listing/pages', $total_rows,100,5,TRUE);
        $this->data['groups'] = $this->users_m->get_user_group_options();
        $this->data['posts'] = $this->users_m->limit($pagination['limit'])->get_all_users();
        $this->data['pagination'] = $pagination;
        //print_r($total_rows);
        //print_r($this->data); die();
    	$this->template->title('User Listing')->build('admin/listing',$this->data);
    }

    function disable($id=0,$redirect = TRUE){
        if(!$id){
            if($redirect){
                redirect('admin/users/listing');
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
                redirect('admin/users/listing');
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

        if($post->active){
            $this->session->set_flashdata('error','Sorry, the user account is already active');
            if($redirect){
                redirect('admin/users/listing');
            }else{
                return FALSE;
            }
        }else{
            if($this->ion_auth->update($post->id, array('active'=>1,'modified_on'=>time(),'modified_by'=>$this->user->id))){
                $this->session->set_flashdata('success','User account successfuly activated');
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
        redirect('admin/users/listing');
    }

    function ajax_search_options(){
        $this->users_m->get_search_options();
    }

    function mypa(){
        $this->template->title('myPA Users')->build('admin/mypa',$this->data);
    }

    function upangaji(){
        $this->template->title('Upangaji Users')->build('admin/upangaji',$this->data);
    }

}