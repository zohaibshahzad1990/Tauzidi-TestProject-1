<?php if(!defined('BASEPATH')) exit('You are not allowed to view this script');
class Api extends Api_Controller{

	public $response = array(
		'result_code' => 0,
		'result_description' => 'Default Response'
	);
    public $time_start;

	function __construct(){
        parent::__construct();
        $this->load->model('user_groups/user_groups_m');
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

}