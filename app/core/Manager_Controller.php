<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Code here is run before admin controllers
class Manager_Controller extends Authentication_Controller{
	public function __construct(){
        parent::__construct();
    $this->load->model('users/users_m');
    $this->load->model('user_groups/user_groups_m');
        $theme_name = 'metronic';
        if (!defined('THEME')){
            define('THEME',$theme_name);
        }
        $this->asset->set_theme($theme_name);
        $groups = $this->user_groups_m->get_user_group_by_slug('manager');
        $group_id = $groups->id;
        if($this->ion_auth->is_in_group($this->user->id,$group_id)){

        }else{
            //redirect("/");
        }
        $this->template->enable_parser(TRUE)->set_theme($theme_name)->set_layout('manager/default.html');
    }
}