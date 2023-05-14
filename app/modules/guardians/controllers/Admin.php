<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends Admin_Controller{
	
	protected $data = array();


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
            'rules' => 'required|trim',
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
        $this->load->model('guardians/guardians_m');  
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

    function add_guardian($parent_id = 0){
        if(!$parent_id){
            redirect("/admin/parents/listing");
        }
        $parent = $this->parents_m->get_user_by_parent_user_id($parent_id);
        $post = $this->guardians_m->get_guardian_by_parent_id($parent_id);

        $this->data['post'] = $post;
        $this->data['id'] = "";
        $this->data['parent'] = $parent;
        $this->template->title($parent->first_name .' '.$parent->last_name. ' - Guardian')->build('admin/form2',$this->data);
    }

    function create($parent_id = 0){
        if(!$parent_id){
            redirect("/admin/parents/listing");
        }
        $parent = $this->parents_m->get_user_by_parent_user_id($parent_id);
        $post = $this->guardians_m->get_guardian_by_parent_id($parent_id);

        if($post){
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
                $update = $this->ion_auth->update($post->id, $input);
                if($update){
                    $guardian_input = array(
                        'user_id'=>$post->id,
                        'guardian_id'=>$post->guardian_id,
                        'user_parent_id'=>$parent->user_parent_id,
                        'parent_id'=>$parent->parent_id,
                        'created_on'=>time(),
                        'created_by'=>$this->user->id,
                        'active'=>1,
                    );
                    $this->add_remove_guardian($guardian_input);
                    $this->session->set_flashdata('success',$this->ion_auth->messages());
                }else{
                    $this->session->set_flashdata('error',$this->ion_auth->errors()); 
                }
                redirect('admin/guardians/listing','refresh');
            }else{
                foreach (array_keys($this->validation_create_rules) as $field){
                    if (isset($_POST[$field])){
                        $post->$field = $this->form_validation->$field;
                    }
                }
            }
            $this->data['post'] = $post;
            $this->data['id'] = $post->id;
            $this->data['parent'] = $parent;
            $this->template->title('Edit guardian '.ucwords($post->first_name))->build('admin/form',$this->data);

        }else{


            $post = new StdClass();
            //print_r($parent);
            //print_r($post); die(" am in 2");
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
                $group = $this->ion_auth->get_group_by_name('guardian');
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
                $user = $this->users_m->get_user_by_phone_number($phone);
                if(!$user){
                    $id = $this->ion_auth->register($phone,$password,$email, $additional_data,$groups);
                }else{
                    $id = $user->id;
                }
                if($id){
                    $guardian_input = array(
                        'user_id'=>$id,
                        'parent_id'=>$parent->parent_id,
                        'user_parent_id'=>$parent->user_parent_id,
                        'created_on'=>time(),
                        'created_by'=>$this->user->id,
                        'active'=>1,
                    );
                    $this->guardians_m->insert($guardian_input);
                    $this->session->set_flashdata('success',$this->ion_auth->messages());
                }else{
                    $this->session->set_flashdata('error',$this->ion_auth->errors()); 
                }
                redirect('admin/guardians/listing','refresh');
            }else{
                foreach ($this->validation_create_rules as $key => $field){
                    $field_name = $field['field'];
                    $post->$field_name = set_value($field['field']);
                }
            }
            $this->data['post'] = $post;
            $this->data['id'] = "";
            $this->data['parent'] = $parent;
            $this->template->title($parent->first_name .' '.$parent->last_name. ' - Guardian')->build('admin/form',$this->data);
           
        }

        
    }

    function edit($guardian_id = 0){
        if(!$guardian_id){
            redirect("/admin/guardians/listing");
        }
        //$parent = $this->parents_m->get_user_by_parent_user_id($parent_id);
        $post = $this->guardians_m->get_guardian_by_id($guardian_id);
        if(!$post){
            $this->session->set_flashdata('error','Sorry, the user does not exist');
            redirect('admin/guardians/listing');
        }
        $parent = $this->parents_m->get_user_by_parent_user_id($post->user_parent_id);
        //print_r($parent); die();
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
            $update = $this->ion_auth->update($post->id, $input);
            if($update){
                $guardian_input = array(
                    'user_id'=>$post->id,
                    'guardian_id'=>$post->guardian_id,
                    'user_parent_id'=>$parent->user_parent_id,
                    'parent_id'=>$parent->parent_id,
                    'created_on'=>time(),
                    'created_by'=>$this->user->id,
                    'active'=>1,
                );
                $this->add_remove_guardian($guardian_input);
                $this->session->set_flashdata('success',$this->ion_auth->messages());
            }else{
                $this->session->set_flashdata('error',$this->ion_auth->errors()); 
            }
            redirect('admin/guardians/listing','refresh');
        }else{
            foreach (array_keys($this->validation_create_rules) as $field){
                if (isset($_POST[$field])){
                    $post->$field = $this->form_validation->$field;
                }
            }
        }
        $this->data['post'] = $post;
        $this->data['id'] = $post->id;
        $this->data['parent'] = $parent;
        $this->template->title('Edit guardian '.ucwords($post->first_name))->build('admin/form',$this->data);
    }

    public function listing(){
        $first_name = $this->input->post_get('first_name');
        $phone = $this->input->post_get('phone');
        $filter_parameters = array(
            'first_name' => str_replace(' ', '',$first_name),
            'phone' =>str_replace(' ', '',$phone)
        );

        //$groups = $this->user_groups_m->get_user_group_by_slug('guardian');
        //$group_id = $groups->id;
        $total_rows = $this ->guardians_m->count_all_active_guardians($filter_parameters);
        $pagination = create_pagination('admin/guardians/listing/pages', $total_rows,50,5,TRUE);
        $posts = $this->guardians_m->limit($pagination['limit'])->get_all();
        
        $user_ids = [];
        foreach ($posts as $key => $post) {
            $user_ids[] = $post->user_id;
        }

        $users = $this->users_m->limit($pagination['limit'])->get_user_filter_user_ids_by_user_ids($user_ids ,$filter_parameters);

        $guardian_user_ids = [];
        foreach ($users as $key => $post) {
            $guardian_user_ids[] = $post->id;
        }

        $parent_options = $this->guardians_m->get_guardian_parent_full_options_by_user_ids($guardian_user_ids);
        //print_r($parent_options);
        //print_r($users);
        //print_r($posts); die();
        $this->data['posts'] = $posts;
        $this->data['parent_options'] = $parent_options;
        $this->data['guardians'] = $users;
        $this->data['pagination'] = $pagination;
        $this->template->title('List Guardian')->build('admin/listing', $this->data);
    }

    function add_remove_guardian($guardian = array()){
        $guardian = (object)$guardian;
        $exist = $this->guardians_m->get_user_by_guardian_user_id($guardian->user_id);
        if(!$exist){
            $parent_new = array(
                'user_id'=>$guardian->user_id,
                'user_parent_id' => $guardian->user_parent_id,
                'parent_id'=>$guardian->parent_id,
                'created_on'=>time(),
                'created_by'=>$this->user->id,
                'active'=>1,
            );
            $this->guardians_m->insert($parent_new);
        }else{
           $guardian_update = array(
                'user_id'=>$guardian->user_id,
                'parent_id'=>$guardian->parent_id,
                'user_parent_id' => $guardian->user_parent_id,
                'modified_on'=>time(),
                'modified_by'=>$this->user->id,
                'active'=>1,
            );
           //print_r($guardian); die();
            $this->guardians_m->update($guardian->guardian_id,$guardian_update); 
        }
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