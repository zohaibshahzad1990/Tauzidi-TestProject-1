<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Public_Controller extends CI_Controller{

    public $settings;

	public function __construct(){
		parent::__construct();
		$this->config->set_item('csrf_protection',TRUE);
		$this->load->model('settings/settings_m');
        $this->settings = $this->settings_m->get_settings(1)?:'';
        $theme_name = 'metronic';
        if (defined('THEME')){

        }else{
            define('THEME',$theme_name);
        }
        $this->asset->set_theme($theme_name);
        $this->template->enable_parser(TRUE)->set_theme($theme_name)->set_layout('public/default.html');
	}
	
}
