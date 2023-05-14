<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Vehicles_m extends MY_Model {

	protected $_table = 'vehicles';

	protected $params_search = array(
		'id'
	);

	public function __construct(){
		parent::__construct();
		$this->load->dbforge();
		$this->install();
	}
	

	public function install(){

		$this->db->query('
			create table if not exists vehicles(
			id int not null auto_increment primary key,
			`registration` varchar(200),
			`type` varchar(200),
			`slug` varchar(200),
			`capacity` int,
			`active` varchar(200),
			`created_on` varchar(200),
			`created_by` varchar(200),
			`modified_on` varchar(200),
			`modified_by` varchar(200)
			)
		');

		$this->db->query('
			create table if not exists vehicle_trips(
			id int not null auto_increment primary key,
			`vehicle_id` int,
			`trip_id` int,
			`route_id` int,
			`active` varchar(200),
			`created_on` varchar(200),
			`created_by` varchar(200),
			`modified_on` varchar(200),
			`modified_by` varchar(200)
			)
		');

		$this->db->query('
			create table if not exists vehicle_types(
			id int not null auto_increment primary key,
			`name` varchar(255),
			`slug` varchar(255),
			`capacity` int,
			`active` varchar(200),
			`created_on` varchar(200),
			`created_by` varchar(200),
			`modified_on` varchar(200),
			`modified_by` varchar(200)
			)
		');

		$this->db->query('
			create table if not exists vehicle_school_pairings(
			id int not null auto_increment primary key,
			`school_id` int,
			`vehicle_id` int,
			`active` varchar(200),
			`created_on` varchar(200),
			`created_by` varchar(200),
			`modified_on` varchar(200),
			`modified_by` varchar(200)
			)
		');

	}

	function insert($input = array(),$skip_value = FALSE){
		return $this->insert_secure_data('vehicles',$input);
	}

	function insert_types($input = array(),$skip_value = FALSE){
		return $this->insert_secure_data('vehicle_types',$input);
	}

	function insert_trips($input = array(),$skip_value = FALSE){
		return $this->insert_secure_data('vehicle_trips',$input);
	}

	function insert_vehicle_pairings($input = array(),$skip_value = FALSE){
		return $this->insert_secure_data('vehicle_school_pairings',$input);
	}

	function update($id,$input,$SKIP_VALIDATION=FALSE){
 		return $this->update_secure_data($id,'vehicles',$input);
 	}

 	function update_type($id,$input,$SKIP_VALIDATION=FALSE){
 		return $this->update_secure_data($id,'vehicle_types',$input);
 	}

 	function update_vehicle_trips($id,$input,$SKIP_VALIDATION=FALSE){
 		return $this->update_secure_data($id,'vehicle_trips',$input);
 	}

 	function update_vehicle_pairings($id,$input,$SKIP_VALIDATION=FALSE){
 		return $this->update_secure_data($id,'vehicle_school_pairings',$input);
 	}

	function get($id = 0){
		$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('registration').' as registration',
	    		$this->dx('active').' as active',
	    		$this->dx('type').' as type',
	    		$this->dx('capacity').' as capacity',
	    		$this->dx('school_id').' as school_id',
	    		$this->dx('created_on').' as created_on',
	    		$this->dx('created_by').' as created_by',
	    		$this->dx('modified_on').' as modified_on',
	    		$this->dx('modified_by').' as modified_by',
	    	)
	    );
	    $this->db->where('id',$id);
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);
    	return $this->db->get('vehicles')->row();
	}

	function get_vehicle_school_by_id($id =0){
		$this->db->select(
    		array('vehicles.id as id',
	    		$this->dx('vehicles.slug').' as slug',
	    		$this->dx('vehicles.type_id').' as type_id',
	    		$this->dx('vehicles.registration').' as registration',
	    		$this->dx('vehicles.active').' as active',
	    		$this->dx('vehicles.type').' as type',
	    		$this->dx('vehicles.capacity').' as capacity',
	    		$this->dx('vehicle_school_pairings.school_id').' as school_id',
	    		$this->dx('vehicle_school_pairings.id').' as vehicle_school_id',
	    		$this->dx('vehicles.created_on').' as created_on',
	    		$this->dx('vehicles.created_by').' as created_by',
	    		$this->dx('vehicles.modified_on').' as modified_on',
	    		$this->dx('vehicles.modified_by').' as modified_by',
	    	)
	    );
	    $this->db->where('vehicles.id',$id);
		$this->db->where($this->dx('vehicles.active')." = 1 ",NULL,FALSE);
		$this->db->join('vehicle_school_pairings','vehicles.id = vehicle_school_pairings.vehicle_id ','left');
		//print_r($this->db->get_compiled_select()); die();
		return $this->db->get('vehicles')->row();
	}

	function get_assigned_trip($id = 0){
		$this->select_all_secure('vehicle_trips');
	    $this->db->where('id',$id);
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);
    	return $this->db->get('vehicle_trips')->row();
	}

	function get_vehicle_type($id = 0){
		$this->select_all_secure('vehicle_types');
	    $this->db->where('id',$id);
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);
    	return $this->db->get('vehicle_types')->row();
	}

	function get_all_types(){
		$this->select_all_secure('vehicle_types');
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);	
		return $this->db->get('vehicle_types')->result();
	}

	function get_all(){
		$this->select_all_secure('vehicles');
		return $this->db->get('vehicles')->result();
	}

	function get_all_vehicle_trips(){
		$this->select_all_secure('vehicle_trips');
		return $this->db->get('vehicle_trips')->result();
	}

	function count_all_active_vehicless(){
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		return $this->count_all_results('vehicles');
	}

	function get_latest_five_vehicles(){
    	$this->select_all_secure('vehicles');
    	$this->db->order_by($this->dx('created_on'),'DESC',FALSE);
    	$this->db->limit(5);
    	return $this->db->get('vehicles')->result();
    }

	function count_all_active_vehicle_types(){
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		return $this->count_all_results('vehicle_types');
	}

	function count_all_filetered_active_assigned_vehicles($filter_parameters = array()){
    	$this->select_all_secure('user_school_driver_pairings');  	
    	if(isset($filter_parameters['search_fields'])){
    		$query_array = array();
    		if($filter_parameters['search_fields']){
	    		foreach ($filter_parameters['search_fields'] as $key => $value) {
	                if(in_array($key, $this->params_search)){
	                	if($key &&$value){
	                		$query_array[] ="CONVERT(" . $this->dx($key) . " USING 'latin1')  like '%" . $this->escape_str($value) . "%'";
	                	}
		    		}
	            }
	        }
            if(count($query_array) > 0){
            	$this->db->where(implode( ' OR ', $query_array ),NULL,FALSE);
            }
		}
    	if(isset($filter_parameters['sort_order']) && isset($filter_parameters['sort_field'])){
    		if(in_array($filter_parameters['sort_field'], $this->params_search)){
    			$this->db->order_by($this->dx($filter_parameters['sort_field']),''.$filter_parameters['sort_order'].'',FALSE);
    		}
		}
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		return $this->count_all_results('user_school_driver_pairings');
	}

	function get_all_filetered_active_assigned_vehicles($filter_parameters = array()){
    	$this->select_all_secure('user_school_driver_pairings');  	
    	if(isset($filter_parameters['search_fields'])){
    		$query_array = array();
    		if($filter_parameters['search_fields']){
	    		foreach ($filter_parameters['search_fields'] as $key => $value) {
	                if(in_array($key, $this->params_search)){
	                	if($key &&$value){
	                		$query_array[] ="CONVERT(" . $this->dx($key) . " USING 'latin1')  like '%" . $this->escape_str($value) . "%'";
	                	}
		    		}
	            }
	        }
            if(count($query_array) > 0){
            	$this->db->where(implode( ' OR ', $query_array ),NULL,FALSE);
            }
		}
    	if(isset($filter_parameters['sort_order']) && isset($filter_parameters['sort_field'])){
    		if(in_array($filter_parameters['sort_field'], $this->params_search)){
    			$this->db->order_by($this->dx($filter_parameters['sort_field']),''.$filter_parameters['sort_order'].'',FALSE);
    		}
		}
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		return $this->db->get('user_school_driver_pairings')->result();
	}

	function get_vehicle_by_slug($slug = ''){
		$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('registration').' as registration'
	    	)
	    );
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);	
	    //$this->db->where($this->dx('user_id').' = "'.$user_id.'"',NULL,FALSE);
		$this->db->where($this->dx('slug')." = ".$this->db->escape($slug),NULL,FALSE);
		$this->db->limit(1);
		return $this->db->get('vehicles')->row();
	} 
	

	function get_vehicle_type_by_slug($slug = ''){
		$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    	)
	    );
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);	
	    //$this->db->where($this->dx('user_id').' = "'.$user_id.'"',NULL,FALSE);
		$this->db->where($this->dx('slug')." = ".$this->db->escape($slug),NULL,FALSE);
		$this->db->limit(1);
		return $this->db->get('vehicle_types')->row();
	} 

	function get_school_veheicle_options($school_id = 0){
		$this->db->select(
    		array('vehicles.id as id',
	    		$this->dx('vehicles.slug').' as slug',
	    		$this->dx('vehicles.registration').' as registration',
	    		$this->dx('vehicles.active').' as active',
	    		$this->dx('vehicles.type').' as type',
	    		$this->dx('vehicles.capacity').' as capacity',
	    		$this->dx('vehicle_school_pairings.school_id').' as school_id',
	    		$this->dx('vehicle_school_pairings.id').' as vehicle_school_id',
	    		$this->dx('vehicles.created_on').' as created_on',
	    		$this->dx('vehicles.created_by').' as created_by',
	    		$this->dx('vehicles.modified_on').' as modified_on',
	    		$this->dx('vehicles.modified_by').' as modified_by',
	    	)
	    );

	    $this->db->where($this->dx('vehicles.active')."= '1'",NULL,FALSE);	
	    $this->db->where($this->dx('vehicle_school_pairings.school_id').' = "'.$school_id.'"',NULL,FALSE);
	    $this->db->join('vehicle_school_pairings','vehicles.id = vehicle_school_pairings.vehicle_id ');
		$results =  $this->db->get('vehicles')->result();
		$arr = [];
		foreach ($results as $key => $result) {
    		$arr[$result->id] = $result->registration;
    	}
    	return $arr;
	}

	function get_vehicle_by_ids_options($vehicles_ids = array()){
		$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('registration').' as registration',
	    		$this->dx('capacity').' as capacity'
	    	)
	    );

	    if(empty($vehicles_ids)){
			$this->db->where('id'.' = 0 ',NULL,FALSE);
    	}else{
			$this->db->where('id'.' IN ('.implode(',',$vehicles_ids).') ',NULL,FALSE);
		}
	    $this->db->where($this->dx('vehicles.active')."= '1'",NULL,FALSE);	
		$results =  $this->db->get('vehicles')->result();
		$arr = [];
		foreach ($results as $key => $result) {
    		$arr[$result->id] = $result->registration;
    	}
    	return $arr;
	}

	

	function get_full_vehicle_by_ids_options($vehicles_ids = array()){
		$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('registration').' as registration',
	    		$this->dx('capacity').' as capacity'
	    	)
	    );

	    if(empty($vehicles_ids)){
			$this->db->where('id'.' = 0 ',NULL,FALSE);
    	}else{
			$this->db->where('id'.' IN ('.implode(',',$vehicles_ids).') ',NULL,FALSE);
		}
	    $this->db->where($this->dx('vehicles.active')."= '1'",NULL,FALSE);	
		$results =  $this->db->get('vehicles')->result();
		$arr = [];
		foreach ($results as $key => $result) {
    		$arr[$result->id] = $result;
    	}
    	return $arr;
	}

	function get_vehicles_by_school_id_as_key($school_ids = array()){
		$this->db->select(
    		array('vehicles.id as id',
	    		$this->dx('vehicles.slug').' as slug',
	    		$this->dx('vehicles.registration').' as registration',
	    		$this->dx('vehicles.active').' as active',
	    		$this->dx('vehicles.type').' as type',
	    		$this->dx('vehicles.capacity').' as capacity',
	    		$this->dx('vehicle_school_pairings.school_id').' as school_id',
	    		$this->dx('vehicle_school_pairings.id').' as vehicle_school_id',
	    		$this->dx('vehicles.created_on').' as created_on',
	    		$this->dx('vehicles.created_by').' as created_by',
	    		$this->dx('vehicles.modified_on').' as modified_on',
	    		$this->dx('vehicles.modified_by').' as modified_by',
	    	)
	    );
	    if(empty($school_ids)){
			$this->db->where($this->dx('vehicle_school_pairings.school_id')." IN (0) ",NULL,FALSE);
		}else{
			$this->db->where($this->dx('vehicle_school_pairings.school_id')." IN (".implode(',',$school_ids).") ",NULL,FALSE);
		}
	    $this->db->where($this->dx('vehicles.active')."= '1'",NULL,FALSE);	
	    $this->db->join('vehicle_school_pairings','vehicles.id = vehicle_school_pairings.vehicle_id ');
		$results =  $this->db->get('vehicles')->result();
		$arr = [];
		foreach ($results as $key => $result) {
    		$arr[$result->school_id] = $result->registration;
    	}
    	return $arr;
	}

	function get_veheicle_options(){
		$this->db->select(
    		array('vehicles.id as id',
	    		$this->dx('vehicles.slug').' as slug',
	    		$this->dx('vehicles.registration').' as registration',
	    		$this->dx('vehicles.active').' as active',
	    		$this->dx('vehicles.type').' as type',
	    		$this->dx('vehicles.capacity').' as capacity',
	    		$this->dx('vehicle_school_pairings.school_id').' as school_id',
	    		$this->dx('vehicle_school_pairings.id').' as vehicle_school_id',
	    		$this->dx('vehicles.created_on').' as created_on',
	    		$this->dx('vehicles.created_by').' as created_by',
	    		$this->dx('vehicles.modified_on').' as modified_on',
	    		$this->dx('vehicles.modified_by').' as modified_by',
	    	)
	    );
	    $this->db->where($this->dx('vehicles.active')."= '1'",NULL,FALSE);	
	     $this->db->join('vehicle_school_pairings','vehicles.id = vehicle_school_pairings.vehicle_id ');
		$results =  $this->db->get('vehicles')->result();
		$arr = [];
		foreach ($results as $key => $result) {
    		$arr[$result->id] = $result->registration;
    	}
    	return $arr;
	}

	function get_type_options(){
		$this->db->select(
    		array('id as id',
	    		$this->dx('name').' as name'
	    	)
	    );
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);	
		$results =  $this->db->get('vehicle_types')->result();
		$arr = [];
		foreach ($results as $key => $result) {
    		$arr[$result->id] = $result->name;
    	}
    	return $arr;
	}

	function get_type_option_details(){
		$this->db->select(
    		array('id as id',
	    		$this->dx('name').' as name',
	    		$this->dx('capacity').' as capacity',
	    	)
	    );
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);	
		$results =  $this->db->get('vehicle_types')->result();
		$arr = [];
		foreach ($results as $key => $result) {
    		$arr[$result->id] = $result;
    	}
    	return $arr;
	}

	function get_school_veheicles($school_id = 0){
		$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('registration').' as registration',
	    		$this->dx('active').' as active',
	    		$this->dx('type').' as type',
	    		$this->dx('capacity').' as capacity',
	    		$this->dx('school_id').' as school_id',
	    		$this->dx('created_on').' as created_on',
	    		$this->dx('created_by').' as created_by',
	    		$this->dx('modified_on').' as modified_on',
	    		$this->dx('modified_by').' as modified_by',
	    	)
	    );
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);	
	    $this->db->where($this->dx('school_id').' = "'.$school_id.'"',NULL,FALSE);
		return $this->db->get('vehicles')->result();
	} 

	function get_vehicle_school_by_school_id($id =0){
		$this->db->select(
    		array('vehicles.id as id',
	    		$this->dx('vehicles.slug').' as slug',
	    		$this->dx('vehicles.registration').' as registration',
	    		$this->dx('vehicles.active').' as active',
	    		$this->dx('vehicles.type').' as type',
	    		$this->dx('vehicles.capacity').' as capacity',
	    		$this->dx('vehicle_school_pairings.school_id').' as school_id',
	    		$this->dx('vehicle_school_pairings.id').' as vehicle_school_id',
	    		$this->dx('vehicles.created_on').' as created_on',
	    		$this->dx('vehicles.created_by').' as created_by',
	    		$this->dx('vehicles.modified_on').' as modified_on',
	    		$this->dx('vehicles.modified_by').' as modified_by',
	    	)
	    );
		$this->db->where($this->dx('vehicles.active')." = 1 ",NULL,FALSE);
		$this->db->where($this->dx('vehicle_school_pairings.school_id').' = "'.$id.'"',NULL,FALSE);
		$this->db->join('vehicle_school_pairings','vehicles.id = vehicle_school_pairings.vehicle_id ');
		return $this->db->get('vehicles')->result();
	}

	function get_vehicle_school_by_school_id_options($id =0){
		$this->db->select(
    		array('vehicles.id as id',
	    		$this->dx('vehicles.slug').' as slug',
	    		$this->dx('vehicles.registration').' as registration',
	    		$this->dx('vehicles.active').' as active',
	    		$this->dx('vehicles.type').' as type',
	    		$this->dx('vehicles.capacity').' as capacity',
	    		$this->dx('vehicle_school_pairings.school_id').' as school_id',
	    		$this->dx('vehicle_school_pairings.id').' as vehicle_school_id',
	    		$this->dx('vehicles.created_on').' as created_on',
	    		$this->dx('vehicles.created_by').' as created_by',
	    		$this->dx('vehicles.modified_on').' as modified_on',
	    		$this->dx('vehicles.modified_by').' as modified_by',
	    	)
	    );
	    $this->db->where($this->dx('vehicles.active')." = 1 ",NULL,FALSE);
		$this->db->where($this->dx('vehicle_school_pairings.school_id').' = "'.$id.'"',NULL,FALSE);
		$this->db->join('vehicle_school_pairings','vehicles.id = vehicle_school_pairings.vehicle_id ');
		$results =  $this->db->get('vehicles')->result();
		$arr = [];
		foreach ($results as $key => $result) {
    		$arr[$result->id] = $result->registration;
    	}
    	return $arr;
	}

	

	function get_trip_vehicle_trips_pairings_details($id = 0){
		$this->db->select(
    		array(
    			'trips.id as id',
    			'vehicle_trips.id as vehicle_trip_id',
	    		$this->dx('trips.school_id').' as school_id',
	    		$this->dx('trips.name').' as name',
	    		$this->dx('trips.route_id').' as route_id',
	    		$this->dx('trips.trip_time').' as trip_time',
	    	)
	    );
	    $this->db->where($this->dx('trips.active')." = 1 ",NULL,FALSE);
		$this->db->where($this->dx('vehicle_trips.id').' = "'.$id.'"',NULL,FALSE);
		//$this->db->join('trips','trips.id = vehicle_trips.trip_id ');
		$this->db->join('trips','vehicle_trips.trip_id = trips.id ');
		return $this->db->get('vehicle_trips')->row();
	}

	function get_trips_per_vehicle($vehicle_id = 0){
		$this->select_all_secure('vehicle_trips');
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);	
	    $this->db->where($this->dx('vehicle_id').' = "'.$vehicle_id.'"',NULL,FALSE);
		return $this->db->get('vehicle_trips')->result();
	}

	function get_vehicle_trip($vehicle_id = 0,$trip_id = 0){
		$this->select_all_secure('vehicle_trips');
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);	
	    $this->db->where($this->dx('vehicle_id').' = "'.$vehicle_id.'"',NULL,FALSE);
	    $this->db->where($this->dx('trip_id').' = "'.$trip_id.'"',NULL,FALSE);
		$this->db->limit(1);
		return $this->db->get('vehicle_trips')->row();
	} 

	function get_vehicle_assigned_trip($vehicle_id = 0,$trip_id = 0){
		$this->select_all_secure('vehicle_trips');
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);	
	    //$this->db->where($this->dx('vehicle_id').' = "'.$vehicle_id.'"',NULL,FALSE);
	    $this->db->where($this->dx('trip_id').' = "'.$trip_id.'"',NULL,FALSE);
		$this->db->limit(1);
		return $this->db->get('vehicle_trips')->row();
	}

	function get_assigned_trip_by_parent_trip_and_vehicle_id($parent_trip_id = 0,$vehicle_id = 0){
		$this->select_all_secure('vehicle_trips');
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);	
	    $this->db->where($this->dx('vehicle_id').' = "'.$vehicle_id.'"',NULL,FALSE);
	    $this->db->where($this->dx('parent_trip_id').' = "'.$parent_trip_id.'"',NULL,FALSE);
		$this->db->limit(1);
		return $this->db->get('vehicle_trips')->row();
	} 



	

}
