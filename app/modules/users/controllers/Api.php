<?php if(!defined('BASEPATH')) exit('You are not allowed to view this script');
class Api extends Api_Controller{

	public $response = array(
		'result_code' => 0,
		'result_description' => 'Default Response'
	);

	function __construct(){
        parent::__construct();
        $this->load->model('user_groups/user_groups_m');
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
        echo json_encode($response);
    }

    public function update_fcm_token(){
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
                'field' =>  'fcm_token',
                'label' =>  'Firebase Token',
                'rules' =>  'trim|required',
            )
        );
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            $user_id = $this->token_user->_id;
            if($user_id){
                $user = $this->users_m->get($user_id);
                $sel_groups =  $this->users_m->get_user_groups_array_option($user_id);
                $groups = $this->users_m->get_group_options();
                if($user){
                    $fcm_token = $this->input->post('fcm_token');

                    $input = array(
                        'fcm_token' => $fcm_token,
                        'modified_on' => time(),
                        'modified_by'=> $this->token_user->_id,
                    );           
                    if($update = $this->ion_auth->update($this->token_user->_id, $input)){
                        $user_details = (object) array(
                            'first_name'=> ucwords($user->first_name),
                            'middle_name'=>ucwords($user->middle_name),
                            'last_name'=>ucwords($user->last_name),
                            'email'=>$user->email,
                            'last_login'=>$user->last_login,
                            'active'=>$user->active,
                            'fcm_token'=>$fcm_token,
                            'phone'=>$user->phone,
                        );
                        $response = array(
                            'status' => TRUE,
                            'message' =>"Success details successfuly updated",
                            'data'=>$user_details,
                        );
                    }else{
                        $response = array(
                            'status' =>FALSE,
                            'message' => 'User details is empty(var)',
                        );
                    }
                }else{
                   $response = array(
                        'status' => FALSE,
                        'message' => 'User details is empty(var)',
                    ); 
                }
            }else{
                $response = array(
                    'status' => FALSE,
                    'message' => 'User id variable is not sent in JSON Payload',
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
        $user_id = $this->input->post('id');
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
                    'status' => TRUE,
                    'message' =>"Success",
                    'data'=>$user_details,
                );
            }else{
                $response = array(
                    'status' => FALSE,
                    'message' => 'User details is empty(var)',
                ); 
            }
        }else{
            $response = array(
                'status' => FALSE,
                'message' => 'User id variable is not sent in JSON Payload',
            );
        }
        echo json_encode($response);
    }

    public function update_user_details(){
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
                    'field' =>  'id',
                    'label' =>  'User id variable',
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
                    'field' =>  'email',
                    'label' =>  'Email Address',
                    'rules' =>  'xss_clean|trim|valid_email',
                ),
            array(
                    'field' =>  'ussd_pin',
                    'label' =>  'USSD PIN',
                    'rules' =>  'xss_clean|trim|numeric|min_length[4]|max_length[4]',
            ),
        );        
        $first_name = $this->input->post('first_name');
        $middle_name = $this->input->post('middle_name');
        $last_name = $this->input->post('last_name');
        $email = $this->input->post('email');
        $phone = $this->input->post('phone');
        $password = $this->input->post('password');
        $group_id = $this->input->post('roles');
        $user_id = $this->input->post('id');
        $sel_groups =  $this->users_m->get_user_groups_option($user_id);
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            if($user = $this->users_m->get($user_id)){
                $groups = $this->input->post('roles');
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
                $update = $this->ion_auth->update($user_id, $input);
                if($update){
                    $user_details = (object) array(
                        'first_name'=> $first_name ? ucwords($first_name) : ucwords($user->first_name),
                        'middle_name'=> $middle_name ? ucwords($middle_name) : ucwords($user->middle_name),
                        'last_name'=> $last_name ? ucwords($last_name) : ucwords($user->last_name),
                        'email'=> $email ? $email : $user->email,
                        'last_login'=>$user->last_login,
                        'active'=>$user->active,
                        'is_active'=>$user->is_active,
                        'phone'=>$user->phone,
                        'access_token'=>$user->access_token,
                        'is_validated'=>$user->is_validated?1:0,
                    );
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
        echo json_encode($response);
    }

    public function update_my_profile(){
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
                    'field' =>  'phone',
                    'label' =>  'Phone Number',
                    'rules' =>  'xss_clean|trim|required|callback__valid_phone',
                ),
            array(
                    'field' =>  'email',
                    'label' =>  'Email Address',
                    'rules' =>  'xss_clean|trim|valid_email',
                ),
            array(
                    'field' =>  'ussd_pin',
                    'label' =>  'USSD PIN',
                    'rules' =>  'xss_clean|trim|numeric|min_length[4]|max_length[4]',
            ),
        );        
        $first_name = $this->input->post('first_name');
        $middle_name = $this->input->post('middle_name');
        $last_name = $this->input->post('last_name');
        $email = strtolower($this->input->post('email'));
        $phone = $this->input->post('phone');
        $password = $this->input->post('password');
        $group_id = $this->input->post('group_id');
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            if($user = $this->users_m->get($this->token_user->_id)){
                $groups = $this->input->post('group_id');
                $input = array(
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'middle_name' => $middle_name,
                    'phone' => valid_phone($phone),
                    'email' => $email,
                    'ussd_pin' => $this->input->post('ussd_pin'),
                    'modified_on' => time(),
                    'modified_by'=> $this->token_user->_id,
                );
                if($this->input->post('password')){
                    $input = array_merge($input,array('password'=>$this->input->post('password')));
                }            
                $update = $this->ion_auth->update($this->token_user->_id, $input);
                if($update){
                    $user = $this->users_m->get($this->token_user->_id);
                    $user_details = (object) array(
                        'first_name'=> ucwords($user->first_name),
                        'middle_name'=>ucwords($user->middle_name),
                        'last_name'=>ucwords($user->last_name),
                        'email'=>$user->email,
                        'last_login'=>$user->last_login,
                        'active'=>$user->active,
                        'is_active'=>$user->is_active,
                        'phone'=>$user->phone,
                        'access_token'=>$user->access_token,
                        'is_validated'=>$user->is_validated?1:0
                    );
                    $response = array(
                        'status' => 1,
                        'message' =>'Success'.$this->ion_auth->messages(),
                        'data'=>$user_details,
                    );
                }else{
                    $response = array(
                        'status' => 0,
                        'message' => $this->ion_auth->errors(),
                    );
                }
            }else{
                $response = array(
                    'status' => 0,
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
                'status' => 0,
                'message' => 'Form validation failed',
                'validation_errors' => $post,
            );
        } 
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

    public function delete_user(){
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
                'field' =>  'phone',
                'label' =>  'Phone number',
                'rules' =>  'trim|required',
            ),            
        ); 
        $phone = $this->input->post('phone');
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){            
            if($user = $this->ion_auth->get_user_by_phone(valid_phone($phone))){ 
                if($user->id == 1){
                    $response = array(
                        'status' => FALSE,
                        'message' => 'Action forbidden for that Msisdn',
                    );
                }else{
                    $input = array(
                        'active' => 0,
                        'modified_on' => time(),
                        'modified_by'=> $user->id,
                    );           
                    $update = $this->users_m->did_delete_row($user->id, $input);
                    $response = array(
                        'status' => TRUE,
                        'message' =>'Success '.$this->ion_auth->messages(),
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
        echo json_encode($response);
    }

    public function delete_users(){
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
                'field' =>  'user_ids[]',
                'label' =>  'User id',
                'rules' =>  'required',
            ),            
        ); 
        $user_ids = $this->input->post('_ids'); 
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            if($result = $this->users_m->update_users_bulk($user_ids)){
                $input = array(
                    'active' => 0,
                    'modified_on' => time(),
                    'modified_by'=> $this->token_user->_id,
                );           
                $response = array(
                    'status' => 1,
                    'message' =>'Success ',
                );
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'Could not delete users',
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
            );
        } 
        echo json_encode($response);
    }

    public function dismiss_dialogue(){
        if($user = $this->users_m->get($this->user->id)){
            $input = array(
                'is_dismiss_dialogue' => 1,
                'modified_on' => time(),
                'modified_by'=> $this->token_user->_id,
            );           
            $update = $this->ion_auth->update($user->id, $input);
            if($update){
                $user = $this->users_m->get($this->user->id);
                $response = array(
                    'status' => 1,
                    'message' =>'Success '.$this->ion_auth->messages(),
                    'user'=>$user,
                );
            }else{
                $response = array(
                    'status' => 0,
                    'message' => $this->ion_auth->errors(),
                );
            }
        }else{
            $response = array(
                'status' => 0,
                'message' => 'User details is not found',
            );
        }
        echo json_encode($response);
    }

   
   

}