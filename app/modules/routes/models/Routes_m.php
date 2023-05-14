<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Routes_m extends MY_Model {

	protected $_table = 'routes';

	protected $params_search = array(
		'id',
		'name'
	);

	public function __construct(){
		parent::__construct();
		$this->load->dbforge();
		$this->install();
	}


	public function install(){

		$this->db->query('
			create table if not exists routes(
			id int not null auto_increment primary key,
			`name` varchar(255),
			`start_point` varchar(255),
			`end_point` varchar(255),
			`start_longitude` varchar(255),
			`start_latitude` varchar(255),
			`destination_longitude` varchar(255),
			`destination_latitude` varchar(255),
			`distance` varchar(255),
			`duration` varchar(255),
			`active` varchar(200),
			`created_on` varchar(200),
			`created_by` varchar(200),
			`modified_on` varchar(200),
			`modified_by` varchar(200)
			)
		');

		$this->db->query('
			create table if not exists route_points(
			id int not null auto_increment primary key,
			`name` varchar(255),
			`route_id` int,
			`longitude` varchar(255),
			`latitude` varchar(255),			
			`active` varchar(200),
			`created_on` varchar(200),
			`created_by` varchar(200),
			`modified_on` varchar(200),
			`modified_by` varchar(200)
			)
		');

	}

	function insert($input = array(),$skip_value = FALSE){
		return $this->insert_secure_data('routes',$input);
	}

	function update($id,$input,$val=FALSE){
    	return $this->update_secure_data($id,'routes',$input);
    }

    function get_all(){
		$this->select_all_secure('routes');
		return $this->db->get('routes')->result();
	}

   function get($id=0){
    	$this->select_all_secure('routes');
		$this->db->where('id',$id);
		return $this->db->get('routes')->row();
	}

	function insert_points($input = array(),$skip_value = FALSE){
		return $this->insert_secure_data('route_points',$input);
	}

	function update_points($id,$input,$val=FALSE){
    	return $this->update_secure_data($id,'route_points',$input);
    }

    public function count_routes($value=''){
    	$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		return $this->count_all_results('routes');
    }

    function get_points($id=0){
    	$this->select_all_secure('route_points');
		$this->db->where('id',$id);
		return $this->db->get('route_points')->row();
	}

	function get_routes_by_ids_array($ids = array()){
		$this->select_all_secure('routes');
		if(empty($ids)){
			$this->db->where($this->dx('id')." IN (0) ",NULL,FALSE);
		}else{
			$this->db->where($this->dx('id')." IN (".implode(',',$ids).") ",NULL,FALSE);
		}
		$this->db->where($this->dx('active')."= '1'",NULL,FALSE);
		$results = $this->db->get('routes')->result();
		$arr = array();
		foreach ($results as $key => $result) {
			$arr[$result->id] = $result;
		}
		return $arr;
	}

	function get_routes_option(){
		$this->select_all_secure('routes');
		$this->db->where($this->dx('active')."= '1'",NULL,FALSE);
		$results = $this->db->get('routes')->result();
		$arr = array();
		foreach ($results as $key => $result) {
			$arr[$result->id] = $result->name;
		}
		return $arr;
	}

	function get_routes_points_option(){
		$this->select_all_secure('route_points');
		$this->db->where($this->dx('active')."= '1'",NULL,FALSE);
		$results = $this->db->get('route_points')->result();
		$arr = array();
		foreach ($results as $key => $result) {
			$arr[$result->id] = $result->name;
		}
		return $arr;
	}


	function get_route_by_vehicle($parent_id =0){
		$this->db->select(
    		array('users.id as id',
	    		$this->dx('first_name').' as first_name',
	    		$this->dx('middle_name').' as middle_name',
	    		$this->dx('last_name').' as last_name',
	    		$this->dx('email').' as email',
	    		$this->dx('last_login').' as last_login',
	    		$this->dx('avatar').' as avatar',	    		
	    		$this->dx('users.active').' as active',
	    		$this->dx('users.is_active').' as is_active',	    		
	    		$this->dx('phone').' as phone',	
	    		$this->dx('user_student_pairings.id').' as student_id',
	    		$this->dx('user_student_pairings.parent_id').' as parent_id',
	    		$this->dx('user_student_pairings.user_parent_id').' as user_parent_id',
	    		$this->dx('user_student_pairings.vehicle_id').' as vehicle_id',
	    		$this->dx('user_student_pairings.school_id').' as school_id',  
	    		$this->dx('user_student_pairings.point_id').' as point_id', 
	    		$this->dx('user_student_pairings.registration_no').' as registration_no',    		
	    	)
	    );
	    $this->db->where($this->dx('user_student_pairings.parent_id').' = "'.$parent_id.'"',NULL,FALSE);
		$this->db->where($this->dx('users.active')." = 1 ",NULL,FALSE);
		$this->db->join('users','user_student_pairings.user_id = users.id ');
		//print_r($this->db->get_compiled_select()); die();
		return $this->db->get('user_student_pairings')->result();
	}

	function get_routes__full_option(){
		$this->select_all_secure('routes');
		$this->db->where($this->dx('active')."= '1'",NULL,FALSE);
		$results = $this->db->get('routes')->result();
		$arr = array();
		foreach ($results as $key => $result) {
			$arr[$result->id] = $result;
		}
		return $arr;
	}

	function get_points_by_route_id($route_id = 0){
		$this->select_all_secure('route_points');
		$this->db->where($this->dx('active')."= '1'",NULL,FALSE);	
	    $this->db->where($this->dx('route_id').' = "'.$route_id.'"',NULL,FALSE);
		return $this->db->get('route_points')->result();
	}

	function get_points_by_ids_array($ids = array()){
		$this->select_all_secure('route_points');
		if(empty($ids)){
			$this->db->where($this->dx('id')." IN (0) ",NULL,FALSE);
		}else{
			$this->db->where($this->dx('id')." IN (".implode(',',$ids).") ",NULL,FALSE);
		}
		$this->db->where($this->dx('active')."= '1'",NULL,FALSE);
		$results = $this->db->get('route_points')->result();
		$arr = array();
		foreach ($results as $key => $result) {
			$arr[$result->id] = $result;
		}
		return $arr;
	}

	function get_points_by_route_ids_array($ids = array()){
		$this->select_all_secure('route_points');
		if(empty($ids)){
			$this->db->where($this->dx('route_id')." IN (0) ",NULL,FALSE);
		}else{
			$this->db->where($this->dx('route_id')." IN (".implode(',',$ids).") ",NULL,FALSE);
		}
		$this->db->where($this->dx('active')."= '1'",NULL,FALSE);
		$results = $this->db->get('route_points')->result();
		$arr = array();
		foreach ($results as $key => $result) {
			$arr[$result->id] = $result;
		}
		return $arr;
	}

	function get_points_by_route_ids_array_as_route_array($ids = array()){
		$this->select_all_secure('route_points');
		if(empty($ids)){
			$this->db->where($this->dx('route_id')." IN (0) ",NULL,FALSE);
		}else{
			$this->db->where($this->dx('route_id')." IN (".implode(',',$ids).") ",NULL,FALSE);
		}
		$this->db->where($this->dx('active')."= '1'",NULL,FALSE);
		$results = $this->db->get('route_points')->result();
		$arr = array();
		foreach ($results as $key => $result) {
			$arr[$result->route_id][] = $result;
		}
		return $arr;
	}

	function get_drop_by_slug($slug = ''){
		$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('name').' as name'
	    	)
	    );
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);	
		$this->db->where($this->dx('slug')." = ".$this->db->escape($slug),NULL,FALSE);
		$this->db->limit(1);
		return $this->db->get('route_points')->row();
	} 
	

}
