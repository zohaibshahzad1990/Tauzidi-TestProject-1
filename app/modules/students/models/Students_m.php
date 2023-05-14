<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Students_m extends MY_Model {

	protected $_table = 'user_student_trips';

	protected $params_search = array(
		'id',
		'vehicle_id'
	);

	public function __construct(){
		parent::__construct();
		$this->load->dbforge();
		$this->install();
	}
	

	public function install(){

		$this->db->query('
			create table if not exists user_student_pairings(
			id int not null auto_increment primary key,
			`user_id` int,
			`school_id` int,
			`vehicle_id` int,
			`route_id` int,
			`active` varchar(200),
			`created_on` varchar(200),
			`created_by` varchar(200),
			`modified_on` varchar(200),
			`modified_by` varchar(200)
			)
		');

		$this->db->query('
			create table if not exists user_student_trips(
			id int not null auto_increment primary key,
			`trip_id` int,
			`student_id` int,
			`school_id` int,
			`vehicle_id` int,
			`route_id` int,
			`parent_id` int,
			`active` varchar(200),
			`created_on` varchar(200),
			`created_by` varchar(200),
			`modified_on` varchar(200),
			`modified_by` varchar(200)
			)
		');

		$this->db->query("
		create table if not exists students_active_journey(
			id int not null auto_increment primary key,
			`journey_id` int,
			`trip_id` int,
			`vehicle_id` int,
			`driver_id` int,
			`route_id` int,
			`school_id` int,
			`student_id` int,
			`start_longitude` varchar(255),
			`start_latitude` varchar(255),
			`is_onborded` int,
			`destination_longitude` varchar(255),
			`destination_latitude` varchar(255),
			`distance` varchar(200),
			`is_journey_end` int,
			`active` varchar(200),
			created_by varchar(200),
			created_on varchar(200),
			modified_on varchar(200),
			modified_by varchar(200)
		)");

	}

	function insert($input,$skip_validation=FALSE){
		return $this->insert_secure_data('user_student_pairings',$input);
	}

	function insert_trips($input,$skip_validation=FALSE){
		return $this->insert_secure_data('user_student_trips',$input);
	}

	function insert_batch_trips($input = array(),$SKIP_VALIDATION=FALSE){
        return $this->insert_chunked_batch_secure_data('user_student_trips', $input);
	}

	function insert_student_journey($input,$skip_validation=FALSE){
		return $this->insert_secure_data('students_active_journey',$input);
	}

	function insert_batch_student_journies($input = array(),$SKIP_VALIDATION=FALSE){
        return $this->insert_chunked_batch_secure_data('students_active_journey', $input);
	}

	function update($id = '', $data = array(), $skip_validation = false){
		return $this->update_secure_data($id,'user_student_pairings',$data);
	}

	function update_student_trips($id = '', $data = array(), $skip_validation = false){
		return $this->update_secure_data($id,'user_student_trips',$data);
	}

	function update_student_journey($id = '', $data = array(), $skip_validation = false){
		return $this->update_secure_data($id,'students_active_journey',$data);
	}

	public function count_vehicles_per_vehicle($vehicle_id = 0){
    	$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);

    	if($vehicle_id){
    		$this->db->where($this->dx('vehicle_id').' = '.$vehicle_id.' ',NULL,FALSE);
    	}
		return $this->count_all_results('user_student_pairings');
    }

    public function count_students_per_vehicle($vehicle_id = 0 ,$filter_parameters = array()){
    	$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
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
    	if($vehicle_id){
    		$this->db->where($this->dx('vehicle_id').' = '.$vehicle_id.' ',NULL,FALSE);
    	}
		return $this->count_all_results('user_student_pairings');
    }
	
	function get($id=0){
    	$this->select_all_secure('user_student_pairings');
		$this->db->where('id',$id);
		return $this->db->get('user_student_pairings')->row();
	}

	function get_students_per_parent($parent_id = 0){
		$this->select_all_secure('user_student_pairings');
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);	
	    $this->db->where($this->dx('parent_id').' = "'.$parent_id.'"',NULL,FALSE);
		return $this->db->get('user_student_pairings')->result();
	}

	function get_students_per_vehicle($vehicle_id = 0){
		$this->select_all_secure('user_student_pairings');
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);	
	    if($vehicle_id){
    		$this->db->where($this->dx('vehicle_id').' = '.$vehicle_id.' ',NULL,FALSE);
    	}
		return $this->db->get('user_student_pairings')->result();
	}

	function get_user_by_student_id($id =0){
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
	    $this->db->where('user_student_pairings.id',$id);
		$this->db->where($this->dx('users.active')." = 1 ",NULL,FALSE);
		$this->db->join('users','user_student_pairings.user_id = users.id ');
		//print_r($this->db->get_compiled_select()); die();
		return $this->db->get('user_student_pairings')->row();
	}

	function get_user_by_students_by_parent_id($parent_id =0){
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

	function get_parent_details_by_student_ids_student_as_key($student_ids = array()){
		$this->db->select(
    		array('users.id as id',
	    		$this->dx('users.first_name').' as first_name',
	    		$this->dx('users.middle_name').' as middle_name',
	    		$this->dx('users.last_name').' as last_name',
	    		$this->dx('users.email').' as email',
	    		$this->dx('users.last_login').' as last_login',
	    		$this->dx('users.avatar').' as avatar',	    		
	    		$this->dx('users.active').' as active',
	    		$this->dx('users.is_active').' as is_active',
	    		$this->dx('users.fcm_token').' as fcm_token',	    		
	    		$this->dx('users.phone').' as phone',	
	    		$this->dx('user_student_pairings.user_parent_id').' as user_parent_id',
	    		$this->dx('user_student_pairings.id').' as student_id',    		
	    	)
	    );
	    if(empty($student_ids)){
			$this->db->where('user_student_pairings.id'.' = 0 ',NULL,FALSE);
    	}else{
			$this->db->where('user_student_pairings.id'.' IN ('.implode(',',$student_ids).') ',NULL,FALSE);
    	}
		$this->db->where($this->dx('users.active')." = 1 ",NULL,FALSE);
		$this->db->join('users','user_student_pairings.user_parent_id = users.id ');
		$results =  $this->db->get('user_student_pairings')->result();
		$arr = [];
		foreach ($results as $key => $result) {
			$arr[$result->student_id] = $result;
		}
		return $arr;
	}

	function get_student_details_by_school_id($school_id = 0){
		$this->db->select(
    		array('user_student_pairings.id as id',
	    		$this->dx('user_student_pairings.user_id').' as user_id',
	    		$this->dx('user_student_pairings.school_id').' as school_id',
	    		$this->dx('user_student_pairings.vehicle_id').' as vehicle_id',
	    		$this->dx('user_student_pairings.user_parent_id').' as user_parent_id',
	    		$this->dx('user_student_pairings.route_id').' as route_id',
	    		$this->dx('users.fcm_token').' as fcm_token',
	    		$this->dx('users.first_name').' as first_name',
	    		$this->dx('users.middle_name').' as middle_name',
	    		$this->dx('users.last_name').' as last_name',
	    	)
	    );
		$this->db->where($this->dx('user_student_pairings.active')." = 1 ",NULL,FALSE);
		$this->db->where($this->dx('user_student_pairings.school_id').' = "'.$school_id.'"',NULL,FALSE);
		$this->db->join('users','user_student_pairings.user_id = users.id ');
		//print_r($this->db->get_compiled_select()); die();
		return $this->db->get('user_student_pairings')->result();
	}

	function get_student_details_by_vehicle_id($vehicle_id = 0){
		$this->db->select(
    		array('user_student_pairings.id as id',
	    		$this->dx('user_student_pairings.user_id').' as user_id',
	    		$this->dx('user_student_pairings.school_id').' as school_id',
	    		$this->dx('user_student_pairings.vehicle_id').' as vehicle_id',
	    		$this->dx('user_student_pairings.user_parent_id').' as user_parent_id',
	    		$this->dx('user_student_pairings.route_id').' as route_id',
	    		$this->dx('users.fcm_token').' as fcm_token',
	    		$this->dx('users.first_name').' as first_name',
	    		$this->dx('users.middle_name').' as middle_name',
	    		$this->dx('users.last_name').' as last_name',
	    	)
	    );
		$this->db->where($this->dx('user_student_pairings.active')." = 1 ",NULL,FALSE);
		$this->db->where($this->dx('user_student_pairings.vehicle_id').' = "'.$vehicle_id.'"',NULL,FALSE);
		$this->db->join('users','user_student_pairings.user_id = users.id ');
		//print_r($this->db->get_compiled_select()); die();
		return $this->db->get('user_student_pairings')->result();
	}

	function get_trips_by_student_id($student_id = 0){
		$this->db->select(
    		array('id as id',
	    		$this->dx('trip_id').' as trip_id',
	    		$this->dx('school_id').' as school_id',
	    		$this->dx('vehicle_id').' as vehicle_id',
	    		$this->dx('route_id').' as route_id',
	    		$this->dx('parent_id').' as parent_id',
	    		$this->dx('student_id').' as student_id',

	    	)
	    );
		$this->db->where($this->dx('active')." = 1 ",NULL,FALSE);
		$this->db->where($this->dx('student_id').' = "'.$student_id.'"',NULL,FALSE);
		//print_r($this->db->get_compiled_select()); die();
		return $this->db->get('user_student_trips')->result();

	}


	function get_student_trips_by_id_trip_id_as_key($student_id = 0){
		$this->db->select(
    		array('id as id',
	    		$this->dx('trip_id').' as trip_id',
	    		$this->dx('school_id').' as school_id',
	    		$this->dx('vehicle_id').' as vehicle_id',
	    		$this->dx('active').' as active',
	    		$this->dx('route_id').' as route_id',
	    		$this->dx('parent_id').' as parent_id',
	    		$this->dx('student_id').' as student_id',
	    		$this->dx('created_on').' as create_on',
	    		$this->dx('created_by').' as created_by',
	    		$this->dx('modified_on').' as modified_on',
	    		$this->dx('modified_by').' as modified_by',

	    	)
	    );
		$this->db->where($this->dx('active')."= '1'",NULL,FALSE);
		$this->db->where($this->dx('student_id').' = "'.$student_id.'"',NULL,FALSE);
		$results = $this->db->get('user_student_trips')->result();
		$arr = array();
		foreach ($results as $key => $result) {
			$arr[$result->trip_id] = $result;
		}
		return $arr;
	}


	function get_ongoing_journeys_student_ids($journey_id = 0){
		$this->db->select(
    		array('id as id',
	    		$this->dx('student_id').' as student_id',
	    		$this->dx('is_onborded').' as is_onborded',

	    	)
	    );
		$this->db->where($this->dx('is_onborded')."= '0'",NULL,FALSE);
		$this->db->where($this->dx('active')."= '1'",NULL,FALSE);
		$this->db->where($this->dx('journey_id').' = "'.$journey_id.'"',NULL,FALSE);
		$results = $this->db->get('students_active_journey')->result();
		$arr = array();
		foreach ($results as $key => $result) {
			$arr[$result->student_id] = $result->student_id;
		}
		return $arr;
	}

	
	function get_ongoing_journeys_active_ids_to_finish_journey($journey_id = 0){
		$this->db->select(
    		array('id as id',
	    		$this->dx('student_id').' as student_id',

	    	)
	    );
		$this->db->where($this->dx('is_onborded')."= '0'",NULL,FALSE);
		$this->db->where($this->dx('active')."= '1'",NULL,FALSE);
		$this->db->where($this->dx('journey_id').' = "'.$journey_id.'"',NULL,FALSE);
		$results = $this->db->get('students_active_journey')->result();
		$arr = array();
		foreach ($results as $key => $result) {
			$arr[] = $result->id;
		}
		return $arr;
	}

	function get_ongoing_journeys_active_ids_to_finish_journey_details($journey_id = 0){
		$this->db->select(
    		array('students_active_journey.id as id',
	    		$this->dx('students_active_journey.student_id').' as student_id',
	    		$this->dx('user_student_pairings.user_parent_id').' as user_parent_id',

	    	)
	    );
		$this->db->where($this->dx('students_active_journey.is_onborded')."= '0'",NULL,FALSE);
		$this->db->where($this->dx('students_active_journey.active')."= '1'",NULL,FALSE);
		$this->db->where($this->dx('students_active_journey.journey_id').' = "'.$journey_id.'"',NULL,FALSE);
		$this->db->join('user_student_pairings','user_student_pairings.id = students_active_journey.student_id');
		$results = $this->db->get('students_active_journey')->result();
		// $arr = array();
		// foreach ($results as $key => $result) {
		// 	$arr[] = $result;
		// }
		return $results;
	}

	function get_students_paired_trips_details($student_ids = array()){
		$this->select_all_secure('user_student_trips');
	    if(empty($student_ids)){
			$this->db->where('student_id'.' = 0 ',NULL,FALSE);
    	}else{
			$this->db->where('student_id'.' IN ('.implode(',',$student_ids).') ',NULL,FALSE);
    	}
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);
    	$results =  $this->db->get('user_student_trips')->result();
    	return $results;
	}

	function get_students_by_student_user_ids($student_user_ids = array()){
		$this->select_all_secure('user_student_pairings');
	    if(empty($student_user_ids)){
			$this->db->where('user_id'.' = 0 ',NULL,FALSE);
    	}else{
			$this->db->where('user_id'.' IN ('.implode(',',$student_user_ids).') ',NULL,FALSE);
    	}
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);
    	$results =  $this->db->get('user_student_pairings')->result();
    	$arr = [];
    	foreach ($results as $key => $result) {
    		$arr[$result->user_id] = $result;
    	}
    	return $arr;
	}

	function get_user_options_by_student_ids($student_user_ids = array()){
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
	    if(empty($student_user_ids)){
			$this->db->where('user_student_pairings.user_id'.' = 0 ',NULL,FALSE);
    	}else{
			$this->db->where('user_student_pairings.user_id'.' IN ('.implode(',',$student_user_ids).') ',NULL,FALSE);
    	}
		$this->db->where($this->dx('users.active')." = 1 ",NULL,FALSE);
		$this->db->join('users','user_student_pairings.user_id = users.id ');
		//print_r($this->db->get_compiled_select()); die();
		$results =  $this->db->get('user_student_pairings')->result();
    	$arr = [];
    	foreach ($results as $key => $result) {
    		$arr[$result->id] = $result;
    	}
    	return $arr;
	}

	function get_user_student_id_options_by_student_ids($student_ids = array()){
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
	    if(empty($student_ids)){
			$this->db->where('user_student_pairings.id'.' = 0 ',NULL,FALSE);
    	}else{
			$this->db->where('user_student_pairings.id'.' IN ('.implode(',',$student_ids).') ',NULL,FALSE);
    	}
		$this->db->where($this->dx('users.active')." = 1 ",NULL,FALSE);
		$this->db->join('users','user_student_pairings.user_id = users.id ');
		//print_r($this->db->get_compiled_select()); die();
		$results =  $this->db->get('user_student_pairings')->result();
    	$arr = [];
    	foreach ($results as $key => $result) {
    		$arr[$result->student_id] = $result;
    	}
    	return $arr;
	}

	function get_students_details_per_vehicle($vehicle_id = 0,$filter_parameters = array()){
    	$this->db->select(
    		array('user_student_trips.id as id',
	    		$this->dx('user_student_pairings.id').' as student_id',
	    		$this->dx('user_student_pairings.parent_id').' as parent_id',
	    		$this->dx('user_student_pairings.user_parent_id').' as user_parent_id',
	    		$this->dx('user_student_pairings.vehicle_id').' as vehicle_id',
	    		$this->dx('user_student_pairings.school_id').' as school_id',  
	    		$this->dx('user_student_pairings.point_id').' as point_id',
	    		$this->dx('user_student_pairings.registration_no').' as registration_no',    		
	    	)
	    );  	
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
		$this->db->where($this->dx('user_student_trips.vehicle_id').' = '.$vehicle_id.' ',NULL,FALSE);
		$this->db->where($this->dx('user_student_trips.active')." = '1' ",NULL,FALSE);
		$this->db->join('user_student_pairings','user_student_trips.student_id = user_student_pairings.id ');
		return $this->db->get('user_student_trips')->result();
	}

	function get_students_paired_trips_details_options($student_ids = array()){
		$this->select_all_secure('user_student_trips');
	    if(empty($student_ids)){
			$this->db->where('student_id'.' = 0 ',NULL,FALSE);
    	}else{
			$this->db->where('student_id'.' IN ('.implode(',',$student_ids).') ',NULL,FALSE);
    	}
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);
    	$results =  $this->db->get('user_student_trips')->result();
    	$arr = [];
    	foreach ($results as $key => $result) {
			$arr[$result->student_id][] = $result;
		}
		return $arr;
	}

	function get_ongoing_journeys_student_pair_details($journey_id = 0){
		$this->db->select(
    		array('id as id',
	    		$this->dx('student_id').' as student_id',
	    		$this->dx('is_onborded').' as is_onborded',

	    	)
	    );
		//$this->db->where($this->dx('is_onborded')."= '0'",NULL,FALSE);
		$this->db->where($this->dx('active')."= '1'",NULL,FALSE);
		$this->db->where($this->dx('journey_id').' = "'.$journey_id.'"',NULL,FALSE);
		$results = $this->db->get('students_active_journey')->result();
		return $results;
	}


	function get_ongoing_journeys_student_details($student_ids = array()){
		$this->db->select(
    		array('user_student_pairings.id as id',
	    		$this->dx('users.first_name').' as first_name',
	    		$this->dx('users.middle_name').' as middle_name',
	    		$this->dx('users.last_name').' as last_name'
	    	)
	    );
	    if(empty($student_ids)){
			$this->db->where('user_student_pairings.id'.' = 0 ',NULL,FALSE);
    	}else{
			$this->db->where('user_student_pairings.id'.' IN ('.implode(',',$student_ids).') ',NULL,FALSE);
    	}
	    $this->db->where($this->dx('user_student_pairings.active')."= '1'",NULL,FALSE);
	    $this->db->join('users','user_student_pairings.user_id = users.id ');
	    //print_r($this->db->get_compiled_select()); die();
    	$results =  $this->db->get('user_student_pairings')->result();
    	return $results;
	}

	function get_ongoing_journeys_student_detail_options($student_ids = array()){
		$this->db->select(
    		array('user_student_pairings.id as id',
	    		$this->dx('users.first_name').' as first_name',
	    		$this->dx('users.middle_name').' as middle_name',
	    		$this->dx('users.last_name').' as last_name',
	    		$this->dx('user_student_pairings.point_id').' as point_id',
	    		$this->dx('user_student_pairings.registration_no').' as registration_no'
	    	)
	    );
	    if(empty($student_ids)){
			$this->db->where('user_student_pairings.id'.' = 0 ',NULL,FALSE);
    	}else{
			$this->db->where('user_student_pairings.id'.' IN ('.implode(',',$student_ids).') ',NULL,FALSE);
    	}
	    $this->db->where($this->dx('user_student_pairings.active')."= '1'",NULL,FALSE);
	    $this->db->join('users','user_student_pairings.user_id = users.id ');
	    //print_r($this->db->get_compiled_select()); die();
    	$results =  $this->db->get('user_student_pairings')->result();
    	$arr = [];
		foreach ($results as $key => $result) {
			$arr[$result->id] = $result;
		}
		return $arr;
	}

	function get_ongoing_students_by_student_ids($student_ids = array()){
	    $this->select_all_secure('students_active_journey');
		$this->db->where($this->dx('is_journey_end')."= '0'",NULL,FALSE);
		$this->db->where($this->dx('active')."= '1'",NULL,FALSE);
		if(empty($student_ids)){
			$this->db->where('student_id'.' = 0 ',NULL,FALSE);
    	}else{
			$this->db->where('student_id'.' IN ('.implode(',',$student_ids).') ',NULL,FALSE);
    	}
		$results = $this->db->get('students_active_journey')->result();
		$arr = [];
		foreach ($results as $key => $result) {
			$arr[$result->student_id] = $result;
		}
		return $arr;
	}

	function get_ongoing_student_journey($student_id = 0){
	    $this->select_all_secure('students_active_journey');
		$this->db->where($this->dx('is_onborded')."= '0'",NULL,FALSE);
		$this->db->where($this->dx('active')."= '1'",NULL,FALSE);
		$this->db->where($this->dx('student_id').' = "'.$student_id.'"',NULL,FALSE);
		$this->db->limit(1);
		return $results = $this->db->get('students_active_journey')->row();
	}

	function get_ongoing_students_journies($student_ids = array() , $journey_id = 0){
	    $this->select_all_secure('students_active_journey');
		$this->db->where($this->dx('is_onborded')."= '0'",NULL,FALSE);
		$this->db->where($this->dx('active')."= '1'",NULL,FALSE);
		if(empty($student_ids)){
			$this->db->where('student_id'.' = 0 ',NULL,FALSE);
    	}else{
			$this->db->where('student_id'.' IN ('.implode(',',$student_ids).') ',NULL,FALSE);
    	}
    	$this->db->where($this->dx('journey_id').' = "'.$journey_id.'"',NULL,FALSE);
		return $results = $this->db->get('students_active_journey')->result();
	}

	function get_parent_options_by_per_school($school_id =0){
		$this->db->select(
    		array('users.id as id',
	    		$this->dx('users.first_name').' as first_name',
	    		$this->dx('users.last_name').' as last_name',   		
	    	)
	    );
	    $this->db->where($this->dx('user_student_pairings.school_id').' = "'.$school_id.'"',NULL,FALSE);
		$this->db->where($this->dx('users.active')." = 1 ",NULL,FALSE);
		$this->db->join('users','user_student_pairings.user_parent_id = users.id ');
		$results =  $this->db->get('user_student_pairings')->result();
		$arr = [];
		foreach ($results as $key => $result) {
			$arr[$result->id] = $result->first_name .' '. $result->last_name;
		}
		return $arr;
	}

	function get_student_by_registration_number($registration_no = "'"){
		$this->select_all_secure('user_student_pairings');
		$this->db->where('registration_no',$registration_no);
		return $this->db->get('user_student_pairings')->row();
	}

	function get_past_not_active_journeys(){
		//$this->select_all_secure('students_active_journey');

		$this->db->select(
    		array('students_active_journey.id as id',
	    		 $this->dx('journeys.on_end_longitude').' as longitude',
	    		 $this->dx('journeys.on_end_latitude').' as latitude',
	    		// $this->dx('users.last_name').' as last_name',
	    		// $this->dx('user_student_pairings.point_id').' as point_id',
	    		// $this->dx('user_student_pairings.registration_no').' as registration_no'
	    	)
	    );
		$this->db->where($this->dx('students_active_journey.is_journey_end')." = '0'",NULL,FALSE);
		$this->db->where($this->dx('journeys.active')."= '1'",NULL,FALSE);
		$this->db->where($this->dx('journeys.status')."= '2'",NULL,FALSE);
		$this->db->limit(50);
		$this->db->join('journeys','students_active_journey.journey_id = journeys.id');
		return $this->db->get('students_active_journey')->result();
	}
	

}
