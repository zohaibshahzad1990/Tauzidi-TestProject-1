<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Code here is run before admin controllers
class Admin_Controller extends Authentication_Controller{
  public function __construct(){
        parent::__construct();
        $time_start = microtime(true);
    $this->load->model('users/users_m');
        $theme_name = 'metronic';
        if (!defined('THEME')){
            define('THEME',$theme_name);
        }
        $this->asset->set_theme($theme_name);
        if($this->ion_auth->is_in_group($this->user->id,2)){

        }else{
            //redirect("/");
        }
        //print_r(uri_string()); die();
        if(preg_match('/log_visit/', $_SERVER['REQUEST_URI'])){
            $action = array(
                'action'=>isset($this->activity_log_options[uri_string()])?$this->activity_log_options[uri_string()]['action']:'',
                'description'=>isset($this->activity_log_options[uri_string()])?$this->activity_log_options[uri_string()]['description']:'Via web portal',
                'user_id'=> isset($this->user)?$this->user->id:'',
                'url'=>$_SERVER['REQUEST_URI']?:'',
                'request_method'=>$_SERVER['REQUEST_METHOD']?:'',
                'ip_address'=>$_SERVER['REMOTE_ADDR']?:'',
                'execution_time'=>"Process took ". number_format(microtime(true) - $time_start, 4). " seconds.",
                'created_on'=>time(),
            );
            $this->activity_log->log_action($action);
        }
        $this->template->enable_parser(TRUE)->set_theme($theme_name)->set_layout('admin/default.html');
    }
}
