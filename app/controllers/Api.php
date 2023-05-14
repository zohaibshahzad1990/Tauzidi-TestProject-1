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
    //$time_start = microtime(true);
    public $time_start;
	function __construct(){
        parent::__construct();
        $this->load->model('user_groups/user_groups_m');
        $this->load->library('billing_manager');
        $this->load->model('parents/parents_m');
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

    public function authenticate(){
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $validation_rules = array(
            array(
                'field' => 'phone',
                'label' => 'Phone',
                'rules' => 'trim|required',
            ),
            array(
                'field' => 'user_category',
                'label' => 'User Category',
                'rules' => 'trim|required',
            ),

        );
        $phone = $this->input->post('phone');
        $user_category = $this->input->post('user_category');
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            if($this->_check_user_by_phone($phone,$user_category)){               
                $response = array(
                    'status' => TRUE,
                    'is_exist'=> true,
                    'message' => 'Otp code sent, proceed to login'
                );
            }else{
                $is_onboarded = 1;
                $group_id = $this->ion_auth->get_group_by_name($user_category);
                if($group_id){
                    $groups = array($group_id->id);
                }else{
                    $group = $this->ion_auth->get_group_by_name('driver');
                    $is_onboarded = 0;
                    $groups = array($group->id);
                }
                $additional_data = array( 
                    'created_on' => time(),
                    'first_name' => "", 
                    'last_name'  => "",
                    'is_validated'=>0,
                    'is_onboarded'=>$is_onboarded,
                    'is_complete_setup'=>0,
                    'otp_expiry_time'=>strtotime('+5 minutes', time()),
                    'refferal_code' => generate_refferal_code(), 
                );
                $user_id = $this->ion_auth->register($phone,$phone,"", $additional_data,$groups,TRUE);
                if(generate_slug($user_category) == 'parent' || generate_slug($user_category) == 'guardian' ){
                    $exist = $this->parents_m->get_user_by_parent_user_id($user_id);
                    if(!$exist){
                        $parent_new = array(
                            'user_id'=>$user_id,
                            'created_on'=>time(),
                            'created_by'=>$user_id,
                            'active'=>1,
                        );
                        $this->parents_m->insert($parent_new);
                    }
                }
                if($this->_check_user_by_phone($phone,$user_category)){ 
                    $response = array(
                        'status' => TRUE,
                        'is_exist'=> true,
                        'message' => 'Otp code sent, proceed to verify account'
                    );
                }else{
                    $response = array(
                        'status' => FALSE,
                        'message' => 'Your not allowed to use this app',
                    );
                }
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
        $this->activity_log->logActivity($response,'authenticate','',$this->time_start);
        echo json_encode($response);
    }

    public function login(){
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $identity = $this->input->post('identity');
        if($this->request_id == 1){
           $password = base64_decode(urldecode($this->input->post('password'))); 
        }else{
            $password =$this->input->post('password');
        }
        $remember = (bool) $this->input->post('remember');
        if($identity && $password){
            if($this->ion_auth->login($identity,$password,$remember)){
                $this->user = $this->ion_auth->get_user_by_identity($identity);                
                if($this->user){
                    if($this->user->is_validated == 1){
                        if($token = $this->_generate_access_token($this->user->id)){
                            if($this->ion_auth->is_in_group($this->user->id,1)){
                                $group = 'admin';
                            }else if($this->ion_auth->is_in_group($this->user->id,2)){
                                $group = 'teacher';
                            }else if($this->ion_auth->is_in_group($this->user->id,3)){
                                $group = 'student';
                            }else{
                                $group = 'no_group';
                            }
                            $token_ = $this->_generate_and_get_token($this->user->id);
                            $user = array(
                                'first_name' => ucwords($this->user->first_name),
                                'last_name' => ucwords($this->user->last_name),
                                'phone' => $this->user->phone,
                                'id' => $this->user->id,
                                'avatar' => $this->user->avatar,
                                'email' => $this->user->email,
                                'access_token'=>$token->access_token,
                                'last_login'=>$this->user->last_login,
                                'auth'=>$group,
                            );
                            $response = array(
                                'status' => 1,
                                'message' => 'Successful',
                                'is_onboarded'=>$group != 'admin'?$this->user->is_onboarded?$this->user->is_onboarded:0:1,
                                'token_string'=>$token_,
                                'data' => array(
                                    'access_token' => $token->access_token,
                                    'user' => $user,
                                ),
                            );                            
                        }else{
                            $response = array(
                                'status' =>5,
                                'message' => 'Token generation failed, login again to proceed',
                            );
                        }
                    }else{
                       $response = array(
                            'status' =>3,
                            'message' => 'Account has not been verified, Kindly verify your account',
                        ); 
                    }
                }else{
                    $response = array(
                        'status' => 4,
                        'message' => 'Error occured getting user details',
                    );                    
                }
            }else{
                $error = strtolower($this->ion_auth->errors());
                if(preg_match('/incorrect login/', $error)){
                    $error = "Invalid password username combination. Use the correct password";
                }
                $response = array(
                    'status' => 0,
                    'message' => $error,
                );
            }
        }else{
            $response = array(
                'status' => 0,
                'message' => 'User information is invalid. Try again',
            );
        }
        $this->activity_log->logActivity($response,'login','',$this->time_start);
        echo json_encode($response);
    }

    public function register(){
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
                'field' => 'full_name',
                'label' => 'Full Name',
                'rules' => 'trim|required',
            ),
            array(
                'field' => 'email',
                'label' => 'Email Address',
                'rules' => 'trim|valid_email',
            ),
            array(
                'field' => 'phone',
                'label' => 'Phone',
                'rules' => 'trim|required',
            ),
            array(
                'field' => 'password',
                'label' => 'Password',
                'rules' => 'trim|required',
            )
        );
        $full_name = $this->input->post('full_name');
        $email = strtolower($this->input->post('email'));
        $phone = $this->input->post('phone');
        if($this->request_id ==1){
           $password = base64_decode(urldecode($this->input->post('password'))); 
        }else{
            $password =$this->input->post('password');
        }
        $user_category = $this->input->post('user_category');
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            $check_input = array(
                'full_name'=>$full_name,
                'email'=>$email,
                'phone'=>$phone,
                'password'=>$password,
                'email_verification_code'=>generate_email_verification_code(),
            );

            if($this->_check_if_exist($check_input)){               
                $response = array(
                    'status' => 1,
                    'is_exist'=> true,
                    'message' => 'Successfully registered proceed to verify your account',
                    'data' => array(
                        "is_validated" =>0
                    )
                );
            }else{
                $full_names =explode(' ', $full_name);
                if(count($full_names) > 1){
                    $count = count($full_names);
                    if($count == 2){
                        $first_name = $full_names[0];
                        $last_name = $full_names[1];
                    }else if($count == 3){
                        $first_name = $full_names[0];
                        $last_name = $full_names[1].' '.$full_names[2];
                    }else if($count == 4){
                        $first_name = $full_names[0];
                        $last_name = $full_names[1].' '.$full_names[2].' '.$full_names[3];
                    }
                    if($first_name&&$last_name){                    
                        $group_id = $this->ion_auth->get_group_by_name($user_category);
                        if($group_id){
                            $groups = array($group_id->id);
                        }else{
                            $group = $this->ion_auth->get_group_by_name('Member');
                            $groups = array($group->id);
                        }                    
                        $confirmation_code = rand(1000,9999);
                        $email_verification_code = generate_email_verification_code();
                        $billing = $this->billing_m->check_if_default();
                        if($billing){
                            if($billing->trial_days == 0){
                                $end_date = time();
                            }else{
                                $end_date = strtotime("+".$billing->trial_days." days");
                            }
                        }else{
                            $end_date = strtotime("+ 14 days");
                        }
                        $additional_data = array( 
                            'created_on' => time(),
                            'first_name' => $first_name, 
                            'last_name'  => $last_name,
                            'is_validated'=>0,
                            'otp_expiry_time'=>strtotime('+24 hours', time()),
                            'email_verification_code'=>$email_verification_code,
                            'email_verification_expire_time'=>strtotime('+24 hours', time()),
                            'refferal_code' => generate_refferal_code(),                      
                            'confirmation_code'=>$confirmation_code,
                            'subscription_status'=>2,
                            'subscription_end_date'=>$end_date,
                            'billing_date'=>$end_date,
                        );
                        //print_r($this->input->post('referral_code')); die();
                        $user_id = $this->ion_auth->register($phone,$password,$email, $additional_data,$groups,TRUE);
                        if($user_id){  
                            $otp_array = array(
                                'pin'=>$confirmation_code,
                                'phone'=>$phone,
                                'email_code'=>$email_verification_code,
                                'link'=> 'https://educationke.com/auth/verify-email/'.$email_verification_code,
                                'request_id'=>$this->request_id,
                                'email'=>$email,
                                'request_id'=>$this->request_id,
                                'first_name'=>ucwords($first_name),
                            );
                            $token_ = $this->_generate_and_get_token($user_id); 
                                               
                            if($sent_otp = $this->messaging_manager->send_user_otp($otp_array)){
                                $refferal_code = $this->input->post('referral_code');
                                if($refferal_code){
                                    $refferal_code_owner = $this->users_m->get_user_by_refferal_code($refferal_code);
                                    if($refferal_code_owner){
                                        $input = array(
                                            'owner_user_id'=>$refferal_code_owner->id,
                                            'recipient_user_id'=>$user_id,
                                            'refferal_code'=>$refferal_code,
                                            'active'=>1,
                                            'created_on'=>time(),
                                            'created_by'=>$user_id 
                                        );
                                        if($this->users_m->insert_refferal_code_pairings($input)){
                                            // do nothing for now
                                        }
                                    }
                                }
                                $response = array(
                                    'status' => 1,
                                    'is_onboarded'=>0,
                                    'is_exist'=> false,
                                    'token_string'=>$token_,
                                    'message' => 'Successfully registered proceed to verify your account',
                                    'data' => array(
                                        "is_validated" =>0
                                    )
                                );
                            }else{
                                $response = array(
                                    'status' => 0,
                                    'is_onboarded'=>0,
                                    'message' => 'Failed to send verification code',
                                    'token_string'=>$token_,
                                    'data' =>[],
                                );
                            } 
                        }else{
                            $user = $this->ion_auth->get_user_by_phone($phone);
                            if($user){
                                $token_ = $this->_generate_and_get_token($user->id);
                                $user_details = (object) array(
                                    'first_name'=>$user->first_name,
                                    'middle_name'=>$user->middle_name,
                                    'last_name'=>$user->last_name,
                                    'email'=>$user->email,
                                    'token_string'=>$token_,
                                    'avatar'=>'',
                                    'last_login'=>$user->last_login,
                                    'active'=>$user->active,
                                    'is_active'=>$user->is_active,
                                    'phone'=>$user->phone,
                                    'is_validated'=>$user->is_validated?1:0
                                );
                                $response = array(
                                    'status' => 1,
                                    'is_exist'=> true,
                                    'is_onboarded'=>0,
                                    'token_string'=>$token_,
                                    'message' => $this->ion_auth->errors(),
                                    'data'=>$user_details,
                                );
                            }else{
                                $response = array(
                                    'status' => 4,
                                    'message' => 'User details not found'
                                ); 
                            }
                        }
                    }else{
                       $response = array(
                            'status' => 0,
                            'message' => 'User Full Name is invalid',
                        ); 
                    }
                }else{
                    $response = array(
                        'status' => 0,
                        'message' => 'User Full Name is invalid',
                    );
                }
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
        $this->activity_log->logActivity($response,'register','',$this->time_start);
        echo json_encode($response);
    }

    public function _generate_and_get_token($id =0){
        if($id){
            $user = $this->users_m->get($id);
            if($user){
                $random = generate_random_string();
                $input = array( 
                    'random' => $random,  
                    'last_login'=> time(),
                    'modified_on' => time(),
                    'modified_by' => 1,
                );
                if($id = $this->users_m->update($id,$input)){
                    $input  = $input + array(
                        'user_id' => $id,
                    );
                   return $random;
                }else{
                    return FALSE;
                }
            }
        }else{
            return FALSE;
        }

        /*if($user_object = $this->_generate_access_token($id)){
            return $user_object->access_token;
        }
        return FALSE;*/
    }

    public function forgot_password(){
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $identity = $this->input->post('identity');
        if($this->user = $this->users_m->get_user_by_identity($identity)){
            $remember_code = rand(1111,9999);
            if(valid_phone($identity) == ('254703656970')){
                $remember_code = '1234';
            }
            $update = array(
                'remember_code' => $remember_code,
                'forgotten_password_time' => time(),
                'expiry_time' => strtotime("+1 hour",time()),
                'modified_by' => $this->user->id,
                'modified_on' => time(),
            );
            if($this->ion_auth->forgotten_password($identity)){
                $response = array(
                    'status' => 1,
                    'message' => 'Reset password code sent to '.$identity,
                    'time' => time(),
                );
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'Error occured requesting for password reset. Try again later',
                    'time' => time(),
                );
            }
        }else{
            $response = array(
                'status' => 4,
                'message' => $identity.' is not registered. Kindly counter check',
                'time' => time(),
            );
        }
        $this->activity_log->logActivity($response,'forgot_password',$identity,$this->time_start);
        echo json_encode($response);
    }

    public function reset_password(){
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
                'field' => 'code',
                'label' => 'Password Reset Code',
                'rules' => 'trim|required',
            ),
            array(
                'field' => 'password',
                'label' => 'New Password',
                'rules' => 'trim|required',
            )
        );
        $code = $this->input->post('code');
        $after_login = $this->input->post('after_join');
        if($this->request_id == 1){
           $password = base64_decode(urldecode($this->input->post('password'))); 
        }else{
            $password =$this->input->post('password');
        }
        if($after_login == 1){
            $student_invite = $this->teachers_m->get_student_by_invite_code($code);
            if($student_invite){
                $input = array(
                    'password'=>$this->ion_auth->hash_password($password),
                    'modified_on'=>time(),
                    'modified_by'=>$student_invite->user_id,
                );
                if($this->users_m->update($student_invite->user_id,$input)){
                    if($token = $this->_generate_access_token($student_invite->user_id)){                   
                        $response = array(
                            'status' => 1,
                            'message' => 'Successful',
                            'data' => array(
                                'access_token' => $token->access_token
                            ),
                        );
                    }else{
                        $response = array(
                            'status' =>5,
                            'message' => 'Token generation failed, login again to proceed',
                        );
                    }
                }else{
                    $response = array(
                        'status' =>0,
                        'message' => 'Unable to update details try again later',
                        'time' => time(),
                    );
                }
            }else{
                $response = array(
                    'status' =>0,
                    'message' => 'Student details not found',
                    'time' => time(),
                );
            }
        }else{
            if($user = $this->ion_auth->forgotten_password_check($code)){            
                $object = $this->ion_auth->forgotten_password_complete($code,$password);
                if($object){
                    $object = (object)$object;
                    if($this->ion_auth->reset_password($object->identity,$password)){
                        $this->ion_auth->clear_forgotten_password_code($code);
                        $this->messaging_manager->notify_user_password_change($user);
                        if($this->ion_auth->login($object->identity,$object->new_password,1)){
                            if($token = $this->_generate_access_token($user->id)){
                                if($this->ion_auth->is_in_group($user->id,1)){
                                    $group = 'admin';
                                }else if($this->ion_auth->is_in_group($user->id,2)){
                                    $group = 'teacher';
                                }else if($this->ion_auth->is_in_group($user->id,3)){
                                    $group = 'student';
                                }else{
                                    $group = 'no_group';
                                }
                                $user = array(
                                    'first_name' => ucwords($user->first_name),
                                    'last_name' => ucwords($user->last_name),
                                    'phone' => $user->phone,
                                    'id' => $user->id,
                                    'avatar' => $user->avatar,
                                    'email' => $user->email,
                                    'access_token'=> $token->access_token,
                                    'last_login'=> $user->last_login,
                                    'auth'=>$group,
                                );
                                $response = array(
                                    'status' => 1,
                                    'message' => 'Successful',
                                    'data' => array(
                                        'access_token' => $token->access_token,
                                        'user' => $user,
                                    ),
                                );
                            }else{
                                $response = array(
                                    'status' =>5,
                                    'message' => 'Token generation failed, login again to proceed',
                                );
                            }
                        }else{
                            $response = array(
                                'status' => 0,
                                'message' => 'We are unable to log you in, try again.',
                            );
                        }
                    }else{
                        $response = array(
                            'status' => 0,
                            'message' => $this->ion_auth->errors(),
                            'time' => time(),
                        );
                    }
                }else{
                    $response = array(
                        'status'=>0,
                        'message' => $this->ion_auth->errors(),
                        'time' => time(),
                    );
                }
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'Sorry the password reset code does not exist',
                    'time' => time(),
                );
            }
        }
        $this->activity_log->logActivity($response,'reset_password',"",$this->time_start);
        echo json_encode($response);
    }

    public function confirm_code(){
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
                'field' => 'code',
                'label' => 'Password Reset Code',
                'rules' => 'trim|required',
            ), 
            array(
                'field' => 'identity',
                'label' => 'Phone number',
                'rules' => 'trim|required',
            ),           
        );
        $code = $this->input->post('code');
        $identity = $this->input->post('identity');
        if($forgotten_password_code = $this->ion_auth->confirm_code($identity,$code)){
            $response = array(
                'status' => 1,
                'message' => 'Code is valid',
                'refer'=>site_url('reset_password?code='.$forgotten_password_code),
                'time' => time()
            );            
        }else{
            $response = array(
                'status' => 0,
                'message' => 'Sorry the password reset code does not exist',
                'time' => time(),
            );
        }
        $this->activity_log->logActivity($response,'confirm_code',$identity,$this->time_start);
        echo json_encode($response);
    }   

    public function roles(){
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        } 
        $posts = $this->user_groups_m->get_user_groups();
        $user_groups_array = array();
        if($posts){
            $count =1;            
            foreach ($posts as $key => $post) {
                $permission_array = array();
                
                if($post->active == 1){
                    //$permissions = array();
                    $core_role = '';
                    if(generate_slug($post->name) == 'admin' || generate_slug($post->name) == 'member'|| generate_slug($post->name) == 'teacher' || generate_slug($post->name) == 'student'){
                        $core_role = TRUE;
                        //$permissions = array(1,2,3,4,5,6,7,8,9,10,11,12);
                    }else{                    
                        $core_role = FALSE;
                    }
                    if($post->permissions){
                        $permissions = unserialize($post->permissions);
                        foreach ($permissions as $key => $permission) {
                            $permission_array[] = intval($permission);
                        }
                    }
                    $user_groups_array[] = array(
                        'id'=>intval($post->id), //$count++,
                        '_id'=>intval($post->id),
                        'title'=>$post->name,
                        'isCoreRole'=>$core_role,
                        'permissions'=> $permission_array,
                    );
                }
            }
            $response = $user_groups_array;
        }else{
            $response = [];
        }
        $this->activity_log->logActivity($response,'roles','',$this->time_start);
        echo json_encode($response);
    }

    public function find_roles(){
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
            $start = $page_number * $page_size;
            $end = $start + $page_size;

            $filter_parameters = array(
                "search_fields"=>$search_field,
                "sort_order"=>$sort_order,
                "sort_field"=>$sort_field,
            );       
        } 
        $posts = $this->user_groups_m->get_user_groups($filter_parameters);
        $user_groups_array = array();
        if($posts){
            $count =1;  
                      
            foreach ($posts as $key => $post) {
                $permission_array = array();
                //$permissions = array();
                $core_role = '';
                if(generate_slug($post->name) == 'admin' || generate_slug($post->name) == 'member'|| generate_slug($post->name) == 'teacher' || generate_slug($post->name) == 'student'){
                    $core_role = TRUE;
                    //$permissions = array(1,2,3,4,5,6,7,8,9,10,11,12);
                }else{                    
                    $core_role = FALSE;
                }

                if($post->permissions){
                    $permissions = unserialize($post->permissions);
                    foreach ($permissions as $key => $permission) {
                        $permission_array[] = intval($permission);
                    }
                }
                $user_groups_array[] = array(
                    'id'=> $count++,
                    '_id'=>$post->id,
                    'title'=>$post->name,
                    'created_on'=>$post->created_on,
                    'isCoreRole'=>$core_role,
                    'permissions'=> $permission_array,
                );
            }
            //print_r($user_groups_array); die();
            $response = array(
                'totalCount'=>count($user_groups_array),
                'items'=> $user_groups_array
            );
           // $response = $user_groups_array;
        }else{
            $response = array(
                'status' => 0,
                'message' => 'User group details empty(var)',
            );
        }
        $this->activity_log->logActivity($response,'find_roles',"",$this->time_start);
        echo json_encode($response);
    }

    public function verify_email(){
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
                'field' => 'code',
                'label' => 'Email verification code',
                'rules' => 'trim|required',
            ),
        );
        $code = $this->input->post('code');
        if($user = $this->users_m->get_user_by_email_verification_code($code)){
            if($user->email_verification_expire_time > time()){
                $input = array(
                    'is_validated'=>1,
                    'email_verification_expire_time'=>'',
                    'email_verification_code' =>'',
                    'modified_by'=>$user->id,
                    'modified_on'=>time()
                );
                if($this->users_m->update($user->id,$input)){
                    $response = array(
                        'status' => 1,
                        'message' => 'Email verified successfully',
                        'time' => time(),
                    ); 
                }else{
                    $response = array(
                        'status' => 0,
                        'message' => 'Email verification failed , try again',
                        'time' => time(),
                    );
                }
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'Email verification code has expired , genarate another code',
                    'time' => time(),
                );  
            }           
        }else{
            $response = array(
                'status' => 0,
                'message' => 'Sorry the email verification code does not exist',
                'time' => time(),
            );
        }
        echo json_encode($response);
    }    

    public function resend_verification(){
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
                'field' => 'email',
                'label' => 'Email Address',
                'rules' => 'trim|required',
            ),
        );
        $email = $this->input->post('email');
        if($user = $this->users_m->get_user_by_email($email)){
            $confirmation_code = rand(1000,9999);
            $email_verification_code = generate_email_verification_code();
            $otp_array = array(
                'pin'=>$confirmation_code,
                'phone'=>$user->phone,
                'email_code'=>$email_verification_code,
                'link'=> 'https://educationke.com/auth/verify-email/'.$email_verification_code,
                'request_id'=>$this->request_id,
                'email'=>$user->email,
                'first_name'=>ucwords($user->first_name),
            );
            $input = array(
                'is_validated'=>0,
                'email_verification_code'=>$email_verification_code,
                'email_verification_expire_time'=>strtotime('+24 hours', time()),
            );  
            if($this->users_m->update($user->id,$input)){                  
                if($sent_otp = $this->messaging_manager->send_user_otp($otp_array)){
                    $response = array(
                        'status' => 1,
                        'message' => 'A verification email has been sent to '.$user->email,
                        'data' =>(object)[],
                    );
                }else{
                    $response = array(
                        'status' => 0,
                        'message' => 'Failed to send verification code',
                        'data' =>(object)[],
                    );
                }
            }else{
                $response = array(
                    'status' =>0,
                    'message' => 'Could not update user details',
                    'time' => time(),
                );  
            }           
        }else{
            $response = array(
                'status' =>4,
                'message' => 'User details not found',
                'time' => time(),
            );
        }
        echo json_encode($response);
    }

    public function verify_pin(){
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
                'field' => 'code',
                'label' => 'Otp verification code',
                'rules' => 'trim|required',
            ),
        );
        $code = $this->input->post('code');
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            if(TRUE){
                if($user = $this->users_m->get_user_by_otp_code($code)){
                    if($user->otp_expiry_time > time()){
                        $input = array(
                            'is_validated'=>1,
                            'email_verification_expire_time'=>'',
                            'confirmation_code'=>'',
                            'email_verification_code' =>'',
                            'otp_code'=>'',
                            'otp_expiry_time'=>'',
                            'modified_by'=>$user->id,
                            'modified_on'=>time()
                        );
                        if($this->users_m->update($user->id,$input)){
                            $message = "";
                            $is_onboarded = $user->is_onboarded ? $user->is_onboarded : 0; // admin onboared
                            $is_onboarded_status = $is_onboarded ? TRUE : FALSE;
                            $is_complete_setup = $user->is_complete_setup ? TRUE:FALSE;
                            $status = 3;
                            $otp_time = 86400 * 24;
                            if($is_onboarded_status && $is_complete_setup){
                                $otp_time = 86400 * 24;
                                $status = 1;
                            }else if($is_complete_setup){
                                $otp_time = 86400 * 24;
                                $status = 2;
                            }
                            if($token = $this->_generate_access_token($user->id,$otp_time)){
                                $sel_groups =  $this->users_m->get_user_groups_array_option($user->id);
                                $groups = $this->users_m->get_group_options();
                                $group_array = array();
                                $count = 0;
                                foreach ($sel_groups as $key => $group) {
                                    $name = isset($groups[$group])?$groups[$group]:"";                                    
                                    $group_array[] = (object)[
                                        'id' =>++$count,
                                        '_id' =>$group,
                                        'name'=> $name
                                    ];
                                }
                                
                                //print_r(); die();
                                // parent 
                                // is_onboarded = 1 already onborded
                                // is_onboarded = 4 review details 
                                $user = array(
                                    'first_name' => $user->first_name ? ucwords($user->first_name) : "",
                                    'last_name' => $user->last_name ? ucwords($user->last_name) : "",
                                    'phone' => $user->phone ? "+".$user->phone: "",
                                    'id' => $user->id,
                                    'avatar' => $user->avatar ? : "",
                                    'email' => $user->email ? $user->email  : "",
                                    'access_token'=> $token->access_token,
                                    'auth'=>$group_array
                                );                               

                                $response = array(
                                    'status' => TRUE,
                                    'message' => 'Operation successfully',
                                    'data' => array(
                                        'access_token' => $token->access_token,
                                        'is_onboarded' => $status,
                                        'user' => $user,
                                    ),
                                );
                            }else{
                                $response = array(
                                    'status' =>FALSE,
                                    'message' => 'Token generation failed, login again to proceed',
                                );
                            }
                        }else{
                            $response = array(
                                'status' => FALSE,
                                'message' => 'Email verification failed , try again',
                                'time' => time(),
                            );
                        }
                    }else{
                        $response = array(
                            'status' => FALSE,
                            'message' => 'Otp verification code has expired , genarate another code',
                            'time' => time(),
                        );  
                    }           
                }else{
                    $response = array(
                        'status' => FALSE,
                        'message' => 'Sorry Otp verification code does not exist',
                        'time' => time(),
                    );
                }
            }else{
                $response = array(
                    'status' => FALSE,
                    'message' => 'Sorry you are not allowed to perform this action',
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
                'status' => FALSE,
                'message' => 'Form validation failed',
                'validation_errors' => $post,
            );
        }
        $this->activity_log->logActivity($response,'verify_pin',"",$this->time_start);
        echo json_encode($response);
    }

    public function resend_otp(){
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
                'field' => 'phone',
                'label' => 'Phone number',
                'rules' => 'trim|required',
            ),
        );
        $phone = $this->input->post('phone');
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            if($user = $this->users_m->get_user_by_phone_number($phone)){
                $confirmation_code = rand(1000,9999);
                $email_verification_code = generate_email_verification_code();
                $otp_array = array(
                    'pin'=>$confirmation_code,
                    'phone'=>$user->phone,
                    'request_id'=>$this->request_id,
                    'first_name'=>ucwords($user->first_name),
                );
                $input = array(
                    'otp_code'=>$confirmation_code,
                    'otp_expiry_time'=>strtotime('+5 minutes', time()),
                    'is_validated'=>1,
                    'confirmation_code'=>$confirmation_code,
                    'otp_expiry_time'=>strtotime('+5 minutes', time()),
                );  
                if($this->users_m->update($user->id,$input)){                  
                    if($sent_otp = $this->messaging_manager->send_user_otp($otp_array)){
                        $response = array(
                            'status' => 200,
                            'message' => 'Otp code has been sent to '.$user->phone,
                            'data' =>(object)[],
                        );
                    }else{
                        $response = array(
                            'status' => 400,
                            'message' => 'Failed to send verification code',
                            'data' =>(object)[],
                        );
                    }
                }else{
                    $response = array(
                        'status' =>400,
                        'message' => 'Could not update user details',
                        'time' => time(),
                    );  
                }           
            }else{
                $response = array(
                    'status' =>404,
                    'message' => 'User details not found',
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
                'status' => 400,
                'message' => 'Form validation failed',
                'validation_errors' => $post,
            );
        }
        $this->activity_log->logActivity($response,'resend_otp',$phone,$this->time_start);
        echo json_encode($response);
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
            if($user){
                $sel_groups =  $this->users_m->get_user_groups_array_option($user_id);
                $groups = $this->users_m->get_group_options();
                $group_array = array();
                $count = 0;
                foreach ($sel_groups as $key => $group) {
                    //print_r($groups); die();
                    $name = isset($groups[$group])?$groups[$group]:"";                                    
                    $group_array[] = (object)[
                        'id' =>++$count,
                        '_id' =>$group,
                        'name'=> $name
                    ];
                }
                $user_details = (object) array(
                    'id' =>$user->id,
                    '_id' =>$user->id,
                    'first_name'=> ucwords($user->first_name),
                    'middle_name'=>ucwords($user->middle_name),
                    'last_name'=>ucwords($user->last_name),
                    'email'=>$user->email,
                    'last_login'=>$user->last_login,
                    'referral_code'=>$user->refferal_code,
                    'active'=>$user->active,
                    'is_active'=>$user->active ? TRUE : FALSE,
                    'phone'=>$user->phone,
                    'avatar'=>$user->avatar ? $user->avatar : "",
                    'access_token'=>$user->access_token,
                    'is_validated'=> $user->is_validated ? 1 : 0,
                    'is_dismiss_dialogue'=>$user->is_dismiss_dialogue?1:0,
                    'roles'=>$group_array
                );
                $action = array(
                    'user_id'=> isset($this->user->id)?$this->user->id:'',
                    'request_method'=>$_SERVER['REQUEST_METHOD']?:'',
                    'ip_address'=>$_SERVER['REMOTE_ADDR']?:'',
                    'created_on'=>time(),
                );
                $this->activity_log->logins($action);
                $response = array(
                    'status' => 200,
                    'message' =>"Success",
                    'data'=>$user_details,
                );
            }else{
               $response = array(
                    'status' => 404,
                    'message' => 'User details is empty(var)',
                ); 
            }
        }else{
            $response = array(
                'status' => 400,
                'message' => 'User id variable is not sent in JSON Payload',
            );
        }
        $this->activity_log->logActivity($response,'get_user_by_token',$user_id,$this->time_start);
        echo json_encode($response);
    }

    public function onboarding_update_basic_info(){
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
                'field' =>'first_name',
                'label' => 'First Name',
                'rules' => 'xss_clean|trim|required',
            ),
            array(
                'field' =>'last_name',
                'label' => 'Last Name',
                'rules' => 'xss_clean|trim|required',
            ),
            array(
                'field' =>'middle_name',
                'label' => 'Last Name',
                'rules' => 'xss_clean|trim',
            ),
            array(
                'field' =>  'email',
                'label' =>  'Email Address',
                'rules' =>  'xss_clean|trim|callback_callback_valid_email',
            ),
            array(
                'field' =>'phone',
                'label' => 'Phone Number',
                'rules' => 'xss_clean|trim',
            )
        );
        $user_id = $this->token_user->_id;
        $first_name = $this->input->post('first_name');
        $last_name = $this->input->post('last_name');
        $email = strtolower($this->input->post('email'));
        $phone = $this->input->post('phone');
        $middle_name = $this->input->post('middle_name');
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            $user = $this->users_m->get($user_id);         
            if($user){

                $input = array(
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $user->email?$user->email: $email,
                    //'phone'=> $user->phone,
                    'is_complete_setup'=>1,
                    'modified_on' => time(),
                    'modified_by'=> $user->id,
                );
                $update = $this->ion_auth->update($user->id, $input);
                if($update){
                    if($token = $this->_generate_access_token($user->id)){
                        $sel_groups =  $this->users_m->get_user_groups_array_option($user->id);
                        $groups = $this->users_m->get_group_options();
                        $group_array = array();
                        $is_onboarded =  2;
                        $filter_parameters = array(
                            'parent','manager'
                        ); 
                        $groups_new = $this->user_groups_m->get_user_groups_by_filter($filter_parameters);
                        $count = 0;
                        foreach ($sel_groups as $key => $group) {
                            $name = isset($groups[$group])?$groups[$group]:"";                                    
                            $group_array[] = (object)[
                                'id' =>++$count,
                                '_id' =>$group,
                                'name'=> $name
                            ];
                            if(array_key_exists($group , $groups_new)){
                                $is_onboarded =  1;
                            }
                        }
                        
                        $user = array(
                            'first_name' => $user->first_name ? ucwords($user->first_name) : "",
                            'last_name' => $user->last_name ? ucwords($user->last_name) : "",
                            'phone' => $user->phone ? "+".$user->phone: "",
                            'id' => $user->id,
                            'avatar' => $user->avatar ? : "",
                            'email' => $user->email ? $user->email  : "",
                            'access_token'=> $token->access_token,
                            'auth'=>$group_array
                        );   
                        $response = array(
                            'status' => TRUE,
                            'message' => 'Operation successfully',
                            'data' => array(
                                'access_token' => $token,
                                'is_onboarded' => $is_onboarded,
                                'user' => $user,
                            ),
                        );
                    }else{
                        $response = array(
                            'status' =>FALSE,
                            'message' => 'Token generation failed, login again to proceed',
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
                    'message' => 'User details is empty(var)',
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
        $this->activity_log->logActivity($response,'onboarding_update_basic_info',$user_id,$this->time_start);
        echo json_encode($response);
    }

    public function check_status(){
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        } 
        $user_id = $this->token_user->_id;
        $user = $this->users_m->get($user_id);
        if($user){
            $sel_groups =  $this->users_m->get_user_groups_array_option($user->id);
            $groups = $this->users_m->get_group_options();
            $group_array = array();
            $count = 0;
            foreach ($sel_groups as $key => $group) {
                $name = isset($groups[$group])?$groups[$group]:"";                                    
                $group_array[] = (object)[
                    'id' =>++$count,
                    '_id' =>$group,
                    'name'=> $name
                ];
            }
              
            $status = 3;
            if($user->is_onboarded == 1){
                $status = 1;
            } else if($user->is_complete_setup == 1){
                $status = 2;
            }                         
            $user = array(
                'first_name' => $user->first_name ? ucwords($user->first_name) : "",
                'last_name' => $user->last_name ? ucwords($user->last_name) : "",
                'phone' => $user->phone ? "+".$user->phone: "",
                'id' => $user->id,
                'avatar' => $user->avatar ? : "",
                'email' => $user->email ? $user->email  : "",
                'auth'=>$group_array
            ); 
            $response = array(
                'status' => TRUE,
                'message' => 'Operation successfully',
                'data' => array(
                    'is_onboarded' => $status,
                    'user' => $user,
                ),
            );
        }else{
            $response = array(
                'status' => FALSE,
                'message' => 'User details is empty(var)',
            ); 
        }
        echo json_encode($response);
    }

    function social_auth(){
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $validation_rules = array(
            array(
                'field' => 'email',
                'label' => 'Email Address',
                'rules' => 'trim|required|callback_callback_valid_email',
            ),
            array(
                'field' => 'id',
                'label' => 'Facebook Id',
                'rules' => 'trim|required',
            ),
            array(
                'field' => 'name',
                'label' => 'Facebook name',
                'rules' => 'trim|required',
            ),

        );
        $email = $this->input->post('email');
        $id = $this->input->post('id');
        $user_category = $this->input->post('user_category');
        $name = $this->input->post('name');
        $id = $this->input->post('id');
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            if($user = $this->_check_user_social_loggin($email)){ 
                $is_onboarded = $user->is_onboarded; // admin onboared
                $is_onboarded_status = $is_onboarded ? TRUE : FALSE;
                $is_complete_setup = $user->is_complete_setup;
                $status = 2;
                $otp_time = 300;
                if($is_onboarded_status && $is_complete_setup){
                    $otp_time = 86400;
                    $status = 1;
                }else if($is_complete_setup){
                    $otp_time = 86400;
                    $status = 2;
                }              
                if($token = $this->_generate_access_token($user->id)){
                    $sel_groups =  $this->users_m->get_user_groups_array_option($user->id);
                    $groups = $this->users_m->get_group_options();
                    $group_array = array();
                    $count = 0;
                    foreach ($sel_groups as $key => $group) {
                        $name = isset($groups[$group])?$groups[$group]:"";                                    
                        $group_array[] = (object)[
                            'id' =>++$count,
                            '_id' =>$group,
                            'name'=> $name
                        ];
                    }
                    
                    $user = array(
                        'first_name' => $user->first_name ? ucwords($user->first_name) : "",
                        'last_name' => $user->last_name ? ucwords($user->last_name) : "",
                        'phone' => $user->phone ? "+".$user->phone: "",
                        'id' => $user->id,
                        'avatar' => $user->avatar ? : "",
                        'email' => $user->email ? $user->email  : "",
                        'access_token'=> $token->access_token,
                        'auth'=>$group_array
                    );                               

                    $response = array(
                        'status' => TRUE,
                        'message' => 'Operation successfully',
                        'data' => array(
                            'access_token' => $token->access_token,
                            'is_onboarded' => $status,
                            'user' => $user,
                        ),
                    );
                }else{
                    $response = array(
                        'status' =>FALSE,
                        'message' => 'Token generation failed, login again to proceed',
                    );
                } 
            }else{
                $group_id = $this->ion_auth->get_group_by_name($user_category);
                if($group_id){
                    $groups = array($group_id->id);
                }else{
                    $group = $this->ion_auth->get_group_by_name('driver');
                    $groups = array($group->id);
                }

                $full_names =explode(' ', $name);
                $count = count($full_names);
                if($count == 2){
                    $first_name = $full_names[0];
                    $last_name = $full_names[1];
                }else if($count == 3){
                    $first_name = $full_names[0];
                    $last_name = $full_names[1].' '.$full_names[2];
                }else if($count == 4){
                   $first_name = $full_names[0];
                    $last_name = $full_names[1].' '.$full_names[2].' '.$full_names[3];
                }

                
                $additional_data = array( 
                    'created_on' => time(),
                    'first_name' => $first_name, 
                    'last_name'  => $last_name,
                    'social_id'=>$id,
                    'email'=>$email,
                    'active'=>1,
                    'social_type' => 1,
                    'is_validated'=>1,
                    'is_onboarded'=>0,
                    'is_complete_setup'=>0,
                    'otp_expiry_time'=>strtotime('+5 minutes', time()),
                    'refferal_code' => generate_refferal_code(), 
                );
                $user_id = $this->ion_auth->register($email,$email,"", $additional_data,$groups,TRUE);

                $user = $this->users_m->get($user_id);
                if($user){
                    $is_onboarded = 0; // admin onboared
                    $is_onboarded_status = $is_onboarded ? TRUE : FALSE;
                    $is_complete_setup = FALSE;
                    $status = 2;
                    $otp_time = 86400;
                    if($is_onboarded_status && $is_complete_setup){
                        $otp_time = 86400;
                        $status = 1;
                    }else if($is_complete_setup){
                        $otp_time = 86400;
                        $status = 2;
                    }
                    if($token = $this->_generate_access_token($user->id,$otp_time)){
                        $sel_groups =  $this->users_m->get_user_groups_array_option($user->id);
                        $groups = $this->users_m->get_group_options();
                        $group_array = array();
                        $count = 0;
                        foreach ($sel_groups as $key => $group) {
                            $name = isset($groups[$group])?$groups[$group]:"";                                    
                            $group_array[] = (object)[
                                'id' =>++$count,
                                '_id' =>$group,
                                'name'=> $name
                            ];
                        }
                        
                        $user = array(
                            'first_name' => $user->first_name ? ucwords($user->first_name) : "",
                            'last_name' => $user->last_name ? ucwords($user->last_name) : "",
                            'phone' => $user->phone ? "+".$user->phone: "",
                            'id' => $user->id,
                            'avatar' => $user->avatar ? : "",
                            'email' => $user->email ? $user->email  : "",
                            'access_token'=> $token->access_token,
                            'auth'=>$group_array
                        );                               

                        $response = array(
                            'status' => TRUE,
                            'message' => 'Operation successfully',
                            'data' => array(
                                'access_token' => $token->access_token,
                                'is_onboarded' => $status,
                                'user' => $user,
                            ),
                        );
                    }else{
                        $response = array(
                            'status' =>FALSE,
                            'message' => 'Token generation failed, login again to proceed',
                        );
                    } 
                }else{
                    $response = array(
                        'status' => FALSE,
                        'message' => 'User details is empty(var)',
                    ); 
                } 
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

    public function onboarding_get_basic_info(){
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
                'field' => 'string',
                'label' => 'Token String',
                'rules' => 'trim|required',
            )
        );
        $string = $this->input->post('string');
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            $user = $this->users_m->get_user_by_random_string($string);
            if($user){
                $user_details = (object) array(
                    'id' =>$user->id,
                    '_id' =>$user->id,
                    'first_name'=> ucwords($user->first_name),
                    'middle_name'=>ucwords($user->middle_name),
                    'last_name'=>ucwords($user->last_name),
                    'email'=>$user->email?$user->email:"",
                    'is_active'=>$user->is_active?$user->is_active:0,
                    'phone'=>$user->phone?$user->phone:"",
                    'avatar'=>$user->avatar?$user->avatar:"",
                    'referral_code' => $user->refferal_code,
                    'is_validated'=> $user->is_validated ? 1 : 0,
                );
                $action = array(
                    'user_id'=> isset($this->user->id)?$this->user->id:'',
                    'request_method'=>$_SERVER['REQUEST_METHOD']?:'',
                    'ip_address'=>$_SERVER['REMOTE_ADDR']?:'',
                    'created_on'=>time(),
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

    public function get_user_roles(){
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
                $count = 0;
                $group_array = array();
                foreach ($sel_groups as $key => $group) {
                    $name = isset($groups[$group])?$groups[$group]:"";
                    
                    $group_array[] = (object)[
                        'id' =>++$count,
                        '_id' =>$group,
                        'name'=> $name
                    ];
                }
                $response = array(
                    'status' => 1,
                    'message' =>"Success",
                    'data'=>$group_array,
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

    function callback_valid_email(){
        $email = $this->input->post('email');
        if($email){
            if(valid_email($email)){
                return TRUE;
            }else{
                $this->form_validation->set_message('callback_valid_email','Enter a valid Email Address');
                return FALSE;
            }
        }
    }

    function _valid_email(){
        $email = $this->input->post('email');
        if($email){
            if(valid_email($email)){
                if($this->ion_auth->get_user_by_email($email)){
                    return TRUE;
                }else{
                    $this->form_validation->set_message('_valid_email','Email address is not registered to any user on the system');
                    return FALSE;
                }
            }else{
                $this->form_validation->set_message('_valid_email','Enter a valid Email Address');
                return FALSE;
            }
        }
    }

    public function _check_user_by_phone($phone = '' , $user_category =''){
        if($phone){
            if($user = $this->ion_auth->get_user_by_phone(valid_phone($phone))){ 
                $group = $this->ion_auth->get_group_by_name($user_category);
                $is_in_group = $this->ion_auth->is_in_group($user->id, $group->id);
                if(!$is_in_group ){
                    $group = $this->ion_auth->get_group_by_name('guardian');
                    $is_in_group = $this->ion_auth->is_in_group($user->id, $group->id);
                }
                if(!$is_in_group){
                    $response = array(
                        'status' => FALSE,
                        'is_exist'=> true,
                        'message' => "Your not allowed to use this app",
                    );
                }else{
                    $update = array();
                    if($user->active == 0){
                        $update = $update + array(
                            'active'=>1,
                            'is_validated'=>0,
                            'is_complete_setup'=>0,
                            'is_onboarded'=>0
                        );
                    } 
                    $confirmation_code = rand(1000,9999); 
                    if($user->phone == '254700000001'){
                        $confirmation_code = "1234";
                    }             
                    
                    $update = $update + array(
                        'active'=>1,
                        'otp_code'=>$confirmation_code,
                        'otp_expiry_time'=>strtotime('+24 hours', time()),
                        'email_verification_expire_time'=>strtotime('+24 hours', time()),
                        'modified_on' =>time(),
                        'modified_by'=> $user->id
                    );
                    $update_ = $this->ion_auth->update($user->id, $update);
                    if($update_){                                            
                        $otp_array = array(
                            'pin'=>$confirmation_code,
                            'phone'=>$phone,
                            'request_id'=>$this->request_id,
                            'first_name'=>ucwords($user->first_name),
                        ); 
                        $sent_otp = TRUE;

                        if($user->phone != '254700000001'){
                            $sent_otp = $this->messaging_manager->send_user_otp($otp_array);
                        }                  
                        if($sent_otp){
                            $response = array(
                                'status' => TRUE,
                                'is_exist'=> true,
                                'message' => 'Otp Code Sent',
                                'data' => array(
                                    "is_validated" =>0
                                )
                            );
                            return TRUE;
                        }else{
                            $response = array(
                                'status' => FALSE,
                                'is_exist'=> true,
                                'message' => 'Could not send otp code',
                            );
                        }
                    }else{
                        $response = array(
                            'status' => FALSE,
                            'is_exist'=> true,
                            'message' => $this->ion_auth->errors(),
                        );
                    }
                }                            
            }else{
                return FALSE;
            }
        }else{
            return FALSE;
        }
    }

    public function _check_user_social_loggin($email =''){
        if(valid_email($email)){
            if($user = $this->ion_auth->get_user_by_email($email)){ 
                $update = array();
                if($user->is_validated == 0){
                    $update = $update + array(
                        'active'=>1,
                        'is_validated'=>1
                    );
                }               
                $confirmation_code = rand(1000,9999);
                $update = $update + array(
                    'active'=>1,
                    'otp_code'=>$confirmation_code,
                    'otp_expiry_time'=>strtotime('+24 hours', time()),
                    'email_verification_expire_time'=>strtotime('+24 hours', time()),
                    'modified_on' =>time(),
                    'modified_by'=> $user->id
                );
                $update_ = $this->ion_auth->update($user->id, $update);
                if($update_){                    
                    return $user;
                }else{
                    return FALSE;
                }                            
            }else{
                return FALSE;
            }
        }else{
            return FALSE;
        }
    }

    public function _check_if_exist($input_array = array()){
        if(!empty($input_array)){
            $input_object = (object)$input_array;
            $email = $input_object->email;
            $phone = $input_object->phone;
            $full_name = $input_object->full_name;
            $password = $input_object->password;
            $email_verification_code = $input_object->email_verification_code;
            if($user = $this->users_m->get_user_by_email($email)){
                if($user->active != 1){
                    $update = array(
                        'active'=>1,
                        'is_validated'=>0,
                        'is_validated'=>0,
                        'otp_expiry_time'=>strtotime('+24 hours', time()),
                        'email_verification_code'=>$email_verification_code,
                        'email_verification_expire_time'=>strtotime('+24 hours', time()),
                        'password'=>$password,
                        'modified_on' =>time(),
                        'modified_by'=> $user->id
                    );
                    $update = $this->ion_auth->update($user->id, $update);
                    if($update){
                        $full_names =explode(' ', $full_name);
                        if(count($full_names) > 1){
                            $count = count($full_names);
                            if($count == 2){
                                $first_name = $full_names[0];
                                $last_name = $full_names[1];
                            }else if($count == 3){
                                $first_name = $full_names[0];
                                $last_name = $full_names[1].' '.$full_names[2];
                            }else if($count == 4){
                                $first_name = $full_names[0];
                                $last_name = $full_names[1].' '.$full_names[2].' '.$full_names[3];
                            }
                        }
                        $confirmation_code = rand(1000,9999);                        
                        $otp_array = array(
                            'pin'=>$confirmation_code,
                            'phone'=>$phone,
                            'email_code'=>$email_verification_code,
                            'link'=> 'https://educationke.com/auth/verify-email/'.$email_verification_code,
                            'request_id'=>$this->request_id,
                            'email'=>$email,
                            'request_id'=>$this->request_id,
                            'first_name'=>ucwords($first_name),
                        );                    
                        if($sent_otp = $this->messaging_manager->send_user_otp($otp_array)){
                            $response = array(
                                'status' => 1,
                                'is_exist'=> true,
                                'message' => 'Successfully registered proceed to verify your account',
                                'data' => array(
                                    "is_validated" =>0
                                )
                            );
                            return TRUE;
                        }else{
                            $response = array(
                                'status' => 0,
                                'is_exist'=> true,
                                'message' => 'Could not send verifaction email',
                            );
                        }
                    }else{
                        $response = array(
                            'status' => 0,
                            'is_exist'=> true,
                            'message' => $this->ion_auth->errors(),
                        );
                    }
                }            
            }else{
                return FALSE;
            }
        }else{
            $response = array(
                'status' =>0,
                'message' => 'Check if exist array empty(var)',
            );
        }
    }

    public function _validate_user_id(){
        $id = $this->input->post('id');
        if($id){
            if($user = $this->users_m->get($id)){
                return True;
            }else{
                $this->form_validation->set_message('_validate_user_id','User details missing');
                return FALSE;
            }
        }else{
            $this->form_validation->set_message('_validate_user_id','User id details missing');
            return FALSE;
        }
    }

}