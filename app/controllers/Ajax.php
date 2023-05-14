<?php if(!defined('BASEPATH')) exit('You are not allowed to view this script');

class Ajax extends Ajax_Controller{

	function __construct(){
		parent::__construct();
		$this->load->model('users/users_m');
	}

	public function index(){

	}
	

}