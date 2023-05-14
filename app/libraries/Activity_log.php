<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Activity_log{

	protected $ci;

	public function __construct(){
		$this->ci= & get_instance();
		$this->ci->load->model('activity_log/activity_log_m');
	}

	function logActivity($response = "",$user = "", $methodName ="" ,$timestart=""){
	    $activity_log_options = array();
	    $message = isset($response['message'])? $response['message'] :"Via API";
	    $action = array(
	        'action'=>isset($activity_log_options[uri_string()])?$activity_log_options[uri_string()]['action']:'',
	        'description'=>$message,
	        'user_id'=> $user,
	        'url'=>$_SERVER['REQUEST_URI']?:'',
	        'request_method'=>$_SERVER['REQUEST_METHOD']?:'',
	        'ip_address'=>$_SERVER['REMOTE_ADDR']?:'',
	        'execution_time'=>"Process took ". number_format(microtime(true) - $timestart, 4). " seconds.",
	        'created_on'=>time(),
	    );
	    $this->ci->activity_log_m->insert($action);
	}

	public function log_action($input = array()){
		return $this->ci->activity_log_m->insert($input);
	}
	

	public function logins($input = array()){
		return $this->ci->activity_log_m->insert_logins($input);
	}
	
}