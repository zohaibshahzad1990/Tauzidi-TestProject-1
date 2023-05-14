<?php if(!defined('BASEPATH')) exit('You are not allowed to view this script');
class Admin extends Admin_Controller{

	protected $data = array();

	function __construct(){
		parent::__construct();
		$this->load->library('PhoneNumberUtillib');
		$this->load->model('vehicles/vehicles_m');
		$this->load->model('incidents/incidents_m');
		$this->load->model('trips/trips_m');
	}

	function index(){
		$this->data['vehicles'] = $this->vehicles_m->count_all_active_vehicless();
		$this->data['trips'] = $this->trips_m->count_active_trips();
		$this->data['incidents'] = $this->incidents_m->count_my_active_incidents();
		$this->data['users'] = $this->users_m->count_all_filetered_active_users();
		$this->data['new_users'] = $this->users_m->get_latest_five_users();
		$this->data['new_vehicles'] = $this->vehicles_m->get_latest_five_vehicles();
		$this->template->title('Admin Dashboard')->build('admin/index',$this->data);
	}

}