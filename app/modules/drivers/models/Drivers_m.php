<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Drivers_m extends MY_Model {

	protected $_table = 'users';

	protected $params_search = array(
		'id',
		'first_name',
		'last_name',
		'phone',
		'is_validated',
		'email'
	);

	public function __construct(){
		parent::__construct();
		$this->load->dbforge();
		$this->install();
	}
	
	

}
