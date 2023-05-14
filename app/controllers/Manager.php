<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Manager extends Manager_Controller{
	
	protected $data = array();

	function __construct(){
		parent::__construct();
		$this->load->model('users/users_m');
	}

	function index(){
		//$this->data['country_options'] = $this->countries_m->get_country_options();
		$this->template->title('Manager Account')->build('manager/index',$this->data);
	}

}
?>