<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Trips_m extends MY_Model {

	protected $_table = 'trips';

	function __construct()
	{
		$this->load->dbforge();
		$this->install();
	}

	protected $params_search = array(
        'phone',
        'transaction_id',
        'amount',
        'status'
    );

	function install()
	{
		$this->db->query("
		create table if not exists trips(
			id int not null auto_increment primary key,
			`name` varchar(255),
			`slug` varchar(255),
			`route_id` int,
			`school_id` int,
			`start_time` varchar(200),
			`description` text,
			`active` varchar(200),
			created_by varchar(200),
			created_on varchar(200),
			modified_on varchar(200),
			modified_by varchar(200)
		)");

		$this->db->query("
		create table if not exists journeys(
			id int not null auto_increment primary key,
			`trip_id` int,
			`vehicle_id` int,
			`driver_id` int,
			`route_id` int,
			`school_id` int,
			`status` int,
			`start_longitude` varchar(255),
			`start_latitude` varchar(255),
			`destination_longitude` varchar(255),
			`destination_latitude` varchar(255),
			`distance` varchar(255),
			`tentative_start_time` varchar(200),
			`start_time` varchar(200),
			`description` text,
			`active` varchar(200),
			created_by varchar(200),
			created_on varchar(200),
			modified_on varchar(200),
			modified_by varchar(200)
		)");

		$this->db->query("
		create table if not exists journey_cordinates(
			id int not null auto_increment primary key,
			`journey_id` int,
			`trip_id` int,
			`vehicle_id` int,
			`driver_id` int,
			`route_id` int,
			`school_id` int,
			`longitude` varchar(255),
			`latitude` varchar(255),
			`description` text,
			`active` varchar(200),
			created_by varchar(200),
			created_on varchar(200),
			modified_on varchar(200),
			modified_by varchar(200)
		)");

		$this->db->query("
		create table if not exists old_journey_cordinates(
		  `id` int not null auto_increment primary key,
		  `old_journey_cordinates_id` int(11) ,
		  `journey_id` int(11) ,
		  `trip_id` int(11) ,
		  `vehicle_id` int(11) ,
		  `driver_id` int(11) ,
		  `route_id` int(11) ,
		  `school_id` int(11) ,
		  `longitude` varchar(255) ,
		  `latitude` varchar(255) ,
		  `description` text,
		  `active` varchar(200) ,
		  `created_by` varchar(200) ,
		  `created_on` varchar(200) ,
		  `modified_on` varchar(200) ,
		  `modified_by` varchar(200) ,
		  `distance` varchar(255) ,
		  `distance_value` varchar(255) ,
		  `duration` varchar(255) ,
		  `duration_value` varchar(255) ,
		  `accuracy` varchar(200) ,
		  `speed` varchar(200) ,
		  `speed_accuracy` varchar(200) ,
		  `journey_time` varchar(200) ,
		  `vertical_accuracy` varchar(200) ,
		  `heading` varchar(200) 
		)");
	}

	function insert($input,$skip_validation=FALSE){
		return $this->insert_secure_data('trips',$input);
	}

	function insert_journey($input,$skip_validation=FALSE){
		return $this->insert_secure_data('journeys',$input);
	}

	function insert_old_journey($input,$skip_validation=FALSE){
		return $this->insert_secure_data('old_journey_cordinates',$input);
	}

	function insert_old_journey_batch($input=array(),$SKIP_VALIDATION=FALSE){
		return $this->insert_chunked_batch_secure_data('old_journey_cordinates', $input);
	}

	function insert_journey_cordinates($input,$skip_validation=FALSE){
		return $this->insert_secure_data('journey_cordinates',$input);
	}

	function update($id = '', $data = array(), $skip_validation = false){
		return $this->update_secure_data($id,'trips',$data);
	}

	function update_journey($id = '', $data = array(), $skip_validation = false){
		return $this->update_secure_data($id,'journeys',$data);
	}
	
	function get($id=0){
    	$this->select_all_secure('trips');
		$this->db->where('id',$id);
		return $this->db->get('trips')->row();
	}

	function get_child_trip($parent_id=0){
    	$this->select_all_secure('trips');
		$this->db->where($this->dx('parent_id').' = "'.$parent_id.'"',NULL,FALSE);
		$this->db->where($this->dx('active').' = "1" ',NULL,FALSE);
		return $this->db->get('trips')->row();
	}

	function get_journey($id=0){
    	$this->select_all_secure('journeys');
		$this->db->where('id',$id);
		return $this->db->get('journeys')->row();
	}

	function get_active_journey($id=0){
    	$this->select_all_secure('journeys');
		$this->db->where('id',$id);
		$this->db->where($this->dx('status')." = '1' ",NULL,FALSE);
		return $this->db->get('journeys')->row();
	}

	function get_all(){
		$this->select_all_secure('trips');
		$this->db->order_by($this->dx('created_on'), 'DESC',FALSE);
		$this->db->where($this->dx('parent_id').' = "0" ',NULL,FALSE);
		return $this->db->get('trips')->result();
	}

	public function count_active_trips($value=''){
    	$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
    	$this->db->where($this->dx('parent_id').' = "0" ',NULL,FALSE);
		return $this->count_all_results('trips');
    }

    public function count_past_journeys_by_driver_id($driver_id = 0){
    	$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
    	$this->db->where($this->dx('driver_id').' = "'.$driver_id.'" ',NULL,FALSE);
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		$this->db->where($this->dx('status')." = '2' ",NULL,FALSE);
		return $this->count_all_results('journeys');
    }

    public function count_all_completed_journeys($value=''){
    	$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
    	$this->db->where($this->dx('status')." = '2' ",NULL,FALSE);
		return $this->count_all_results('journeys');
    }

    public function count_all_ongoing_journeys($value=''){
    	$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
    	$this->db->where($this->dx('status')." = '1' ",NULL,FALSE);
		return $this->count_all_results('journeys');
    }

    function count_all_filetered_active_trips($filter_parameters = array()){
    	$this->select_all_secure('journeys');  	
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
		if(isset($filter_parameters['status']) && isset($filter_parameters['status'])){
    		$this->db->where($this->dx('status'),''.$filter_parameters['status'].'',FALSE);
		}
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		return $this->count_all_results('journeys');
	}

	function get_all_filetered_active_trips($filter_parameters = array()){
    	$this->select_all_secure('journeys');  	
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
		if(isset($filter_parameters['status']) && isset($filter_parameters['status'])){
    		$this->db->where($this->dx('status'),''.$filter_parameters['status'].'',FALSE);
		}
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		return $this->db->get('journeys')->result();
	}

    function get_trip_by_slug($slug = ''){
		$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('name').' as name'
	    	)
	    );
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);	
	    //$this->db->where($this->dx('user_id').' = "'.$user_id.'"',NULL,FALSE);
		$this->db->where($this->dx('slug')." = ".$this->db->escape($slug),NULL,FALSE);
		$this->db->limit(1);
		return $this->db->get('trips')->row();
	}

	function get_trips_by_ids_array($ids = array()){
		$this->select_all_secure('trips');
		if(empty($ids)){
			$this->db->where($this->dx('id')." IN (0) ",NULL,FALSE);
		}else{
			$this->db->where($this->dx('id')." IN (".implode(',',$ids).") ",NULL,FALSE);
		}
		$this->db->where($this->dx('active')."= '1'",NULL,FALSE);
		$results = $this->db->get('trips')->result();
		$arr = array();
		foreach ($results as $key => $result) {
			$arr[$result->id] = $result;
		}
		return $arr;
	}

	function get_all_trips_options(){
		$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('name').' as name',
	    		$this->dx('route_id').' as route_id'
	    	)
	    );
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);
	    $arr = array();
    	$results =  $this->db->get('trips')->result();
    	foreach ($results as $key => $result) {
    		$arr[$result->id] = $result->name;
    	}
    	return $arr;
		
	}

	function get_trips_by_ids_array_options($ids = array()){
		$this->select_all_secure('trips');
		if(empty($ids)){
			$this->db->where($this->dx('id')." IN (0) ",NULL,FALSE);
		}else{
			$this->db->where($this->dx('id')." IN (".implode(',',$ids).") ",NULL,FALSE);
		}
		$this->db->where($this->dx('active')."= '1'",NULL,FALSE);
		$results = $this->db->get('trips')->result();
		$arr = array();
		foreach ($results as $key => $result) {
			$arr[$result->id] = $result->name;
		}
		return $arr;
	}

	function get_trips_by_ids_array_details($ids = array()){
		$this->select_all_secure('trips');
		if(empty($ids)){
			$this->db->where($this->dx('id')." IN (0) ",NULL,FALSE);
		}else{
			$this->db->where($this->dx('id')." IN (".implode(',',$ids).") ",NULL,FALSE);
		}
		$this->db->where($this->dx('active')."= '1'",NULL,FALSE);
		$results = $this->db->get('trips')->result();
		$arr = array();
		foreach ($results as $key => $result) {
			$arr[$result->id] = $result;
		}
		return $arr;
	}

	function get_trips_options(){
		$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('name').' as name',
	    		$this->dx('route_id').' as route_id'
	    	)
	    );
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);
	    $this->db->where($this->dx('parent_id')."= '0'",NULL,FALSE);
	    $arr = array();
    	$results =  $this->db->get('trips')->result();
    	foreach ($results as $key => $result) {
    		$arr[$result->id] = $result->name;
    	}
    	return $arr;
		
	}

	function get_trips_by_vehicle_and_school_id($vehicle_id =0, $school_id = 0){
		$this->select_all_secure('trips');
		$this->db->order_by($this->dx('created_on'), 'DESC',FALSE);
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		$this->db->where($this->dx('school_id').' = "'.$school_id.'" ',NULL,FALSE);
		$this->db->where($this->dx('vehicle_id').' = "'.$vehicle_id.'" ',NULL,FALSE);
		return $this->db->get('trips')->result();	
	} 

	 

	function get_journey_by_trip_vehicle_route($trip_id =0, $vehicle_id = 0 ,$route_id = 0){
		$this->select_all_secure('journeys');
		$this->db->order_by($this->dx('created_on'), 'DESC',FALSE);
		$this->db->where($this->dx('status')." = '1' ",NULL,FALSE);
		$this->db->where($this->dx('trip_id').' = "'.$trip_id.'" ',NULL,FALSE);
		$this->db->where($this->dx('vehicle_id').' = "'.$vehicle_id.'" ',NULL,FALSE);
		$this->db->where($this->dx('route_id').' = "'.$route_id.'" ',NULL,FALSE);
		return $this->db->get('journeys')->row();
	}

	function get_driver_vehicle_active_journey($driver_id=0, $vehicle_id = 0){
		$this->select_all_secure('journeys');
		$this->db->order_by($this->dx('created_on'), 'DESC',FALSE);
		$this->db->where($this->dx('status')." = '1' ",NULL,FALSE);
		$this->db->where($this->dx('vehicle_id').' = "'.$vehicle_id.'" ',NULL,FALSE);
		$this->db->where($this->dx('driver_id').' = "'.$driver_id.'" ',NULL,FALSE);
		return $this->db->get('journeys')->result();
	}

	function get_top_active_journey_by_driver_vehicle($driver_id=0, $vehicle_id = 0){
		$this->select_all_secure('journeys');
		$this->db->order_by($this->dx('created_on'), 'DESC',FALSE);
		$this->db->where($this->dx('status')." = '1' ",NULL,FALSE);
		$this->db->where($this->dx('vehicle_id').' = "'.$vehicle_id.'" ',NULL,FALSE);
		$this->db->where($this->dx('driver_id').' = "'.$driver_id.'" ',NULL,FALSE);
		return $this->db->get('journeys')->row();
	}

	function get_driver_vehicle_finished_journey($driver_id=0, $vehicle_id = 0){
		$this->select_all_secure('journeys');
		$this->db->order_by($this->dx('created_on'), 'DESC',FALSE);
		$this->db->where($this->dx('status')." = '2' ",NULL,FALSE);
		$this->db->where($this->dx('vehicle_id').' = "'.$vehicle_id.'" ',NULL,FALSE);
		$this->db->where($this->dx('driver_id').' = "'.$driver_id.'" ',NULL,FALSE);
		return $this->db->get('journeys')->result();
	}

	function get_active_journey_driver($journey_id =0, $driver_id = 0){
		$this->select_all_secure('journeys');
		$this->db->order_by($this->dx('created_on'), 'DESC',FALSE);
		$this->db->where($this->dx('status')." = '1' ",NULL,FALSE);
		$this->db->where('id',$journey_id);
		$this->db->where($this->dx('driver_id').' = "'.$driver_id.'" ',NULL,FALSE);
		return $this->db->get('journeys')->row();
	}

	function get_all_active_journeys(){
		$this->select_all_secure('journeys');
		$this->db->order_by($this->dx('created_on'), 'DESC',FALSE);
		$this->db->where($this->dx('status')." = '1' ",NULL,FALSE);
		return $this->db->get('journeys')->result();
	}

	
	public function count_active_journeys($value=''){
    	$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
    	$this->db->where($this->dx('status')." = '1' ",NULL,FALSE);
		return $this->count_all_results('journeys');
    }

    function get_all_completed_journeys(){
		$this->select_all_secure('journeys');
		$this->db->order_by($this->dx('created_on'), 'DESC',FALSE);
		$this->db->where($this->dx('status')." = '2' ",NULL,FALSE);
		return $this->db->get('journeys')->result();
	}

    public function get_last_ten_cordinates($journey_id =0, $driver_id = 0){
    	$this->select_all_secure('journey_cordinates');
		$this->db->where($this->dx('journey_id').' = "'.$journey_id.'" ',NULL,FALSE);
		$this->db->where($this->dx('driver_id').' = "'.$driver_id.'" ',NULL,FALSE);
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
    	$this->db->order_by($this->dx('created_on'), 'DESC',FALSE);
    	$this->db->limit(10);
		return $this->db->get('journey_cordinates')->result();
    }

    public function get_lastest_cordinates($journey_id =0, $driver_id = 0){
    	$this->select_all_secure('journey_cordinates');
		$this->db->where($this->dx('journey_id').' = "'.$journey_id.'" ',NULL,FALSE);
		$this->db->where($this->dx('driver_id').' = "'.$driver_id.'" ',NULL,FALSE);
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
    	$this->db->order_by($this->dx('created_on'), 'DESC',FALSE);
    	$this->db->limit(1);
		return $this->db->get('journey_cordinates')->row();
    }

    public function get_last_ten_cordinates_by_journey_id($journey_id = 0){
    	$this->select_all_secure('journey_cordinates');
		$this->db->where($this->dx('journey_id').' = "'.$journey_id.'" ',NULL,FALSE);
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
    	$this->db->order_by($this->dx('created_on'), 'DESC',FALSE);
    	$this->db->limit(10);
		return $this->db->get('journey_cordinates')->result();
    }

    public function get_cordinates_by_journey_id($journey_id = 0){
    	$this->select_all_secure('journey_cordinates');
		$this->db->where($this->dx('journey_id').' = "'.$journey_id.'" ',NULL,FALSE);
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
    	$this->db->order_by($this->dx('created_on'), 'DESC',FALSE);
    	$this->db->limit(500);
		return $this->db->get('journey_cordinates')->result();
    }

    public function get_all_cordinates_by_journey_id($journey_id = 0){
    	$this->select_all_secure('journey_cordinates');
		$this->db->where($this->dx('journey_id').' = "'.$journey_id.'" ',NULL,FALSE);
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
    	$this->db->order_by($this->dx('created_on'), 'DESC',FALSE);
		return $this->db->get('journey_cordinates')->result();
    }

    function get_cordinates_edges($journey_id = 0){
	    $this->db->select('MAX(id) as end, MIN(id) as start');
		$this->db->where($this->dx('journey_id').' = "'.$journey_id.'" ',NULL,FALSE);
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
    	$this->db->group_by($this->dx('journey_id'),FALSE);
    	//$this->db->group_by('journey_id'); 
    	/*Select journey_id, min(id) as start, max(id) as end
		from journey_cordinates GROUP BY journey_id;*/
		return $this->db->get('journey_cordinates')->row();
    }

    function get_cordinates_by_ids_array($ids = array()){
		$this->select_all_secure('journey_cordinates');
		if(empty($ids)){
			$this->db->where($this->dx('id')." IN (0) ",NULL,FALSE);
		}else{
			$this->db->where($this->dx('id')." IN (".implode(',',$ids).") ",NULL,FALSE);
		}
		$this->db->where($this->dx('active')."= '1'",NULL,FALSE);
		$results = $this->db->get('journey_cordinates')->result();
		$arr = array();
		foreach ($results as $key => $result) {
			$arr[$result->id] = $result;
		}
		return $arr;
	}

	function get_dashboard_stats(){
	    $this->db->select(array(
			$this->dx('vehicle_id').' as vehicle_id',
			'COUNT(*) as count'
		));
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);
	    $this->db->group_by('vehicle_id');
	    $this->db->order_by('count','DESC',FALSE);
		$results = $this->db->get('journeys')->result();
		$arr = [];
		$number = 0;
		foreach ($results as $key => $result) {
			if($number < 3){
				$arr[$result->vehicle_id] = $result->count;
			}
			$number++;
		}
		return $arr;
	} 

	function get_random_coridnates($journey_id = 0){
		//$this->db->where($this->dx('journey_id').' = "'.$journey_id.'" ',NULL,FALSE);
		$sql="SELECT * FROM old_journey_cordinates AS t1 JOIN (SELECT id FROM old_journey_cordinates WHERE journey_id= ".$journey_id." ORDER BY RAND() LIMIT 25) as t2 ON t1.id=t2.id ";    
	    $query = $this->db->query($sql);
	    return $query->result_array();
		
	}

	function get_active_journies__by_vehicle_ids($vehicle_ids = array()){
	    $this->select_all_secure('journeys');
		$this->db->where($this->dx('status')." = '1' ",NULL,FALSE);
		$this->db->where($this->dx('active')."= '1'",NULL,FALSE);
		if(empty($vehicle_ids)){
			$this->db->where('vehicle_id'.' = 0 ',NULL,FALSE);
    	}else{
			$this->db->where('vehicle_id'.' IN ('.implode(',',$vehicle_ids).') ',NULL,FALSE);
    	}
		$results = $this->db->get('journeys')->result();
		$arr = [];
		foreach ($results as $key => $result) {
			$arr[$result->vehicle_id] = $result;
		}
		return $arr;
	}

	function get_journies_by_vehicle_ids($vehicle_ids = array()){
	    $this->select_all_secure('journeys');
		if(empty($vehicle_ids)){
			$this->db->where('vehicle_id'.' = 0 ',NULL,FALSE);
    	}else{
			$this->db->where('vehicle_id'.' IN ('.implode(',',$vehicle_ids).') ',NULL,FALSE);
    	}
    	$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		$this->db->order_by($this->dx('created_on'), 'DESC',FALSE);
		$results = $this->db->get('journeys')->result();
		$arr = [];
		foreach ($results as $key => $result) {
			$arr[$result->vehicle_id] = $result;
		}
		return $arr;
	}

	function get_ended_journies_by_vehicle_ids($vehicle_ids = array()){
	    $this->select_all_secure('journeys');
		$this->db->where($this->dx('status')." = '2' ",NULL,FALSE);
		$this->db->where($this->dx('active')."= '1'",NULL,FALSE);
		if(empty($vehicle_ids)){
			$this->db->where('vehicle_id'.' = 0 ',NULL,FALSE);
    	}else{
			$this->db->where('vehicle_id'.' IN ('.implode(',',$vehicle_ids).') ',NULL,FALSE);
    	}
		$results = $this->db->get('journeys')->result();
		$arr = [];
		foreach ($results as $key => $result) {
			$arr[$result->vehicle_id] = $result;
		}
		return $arr;
	}

	function get_current_vehicle_cordinates_by_vehicle_ids($vehicle_ids = array()){
	    $this->db->select(
    		array('id as id',
    			//'MAX(id) as end', 
    			//'MIN(id) as start',
	    		$this->dx('journey_id').' as journey_id',
	    		$this->dx('trip_id').' as trip_id',
	    		$this->dx('vehicle_id').' as vehicle_id',
	    		$this->dx('driver_id').' as driver_id',
	    		$this->dx('longitude').' as longitude',
	    		$this->dx('latitude').' as latitude',	    		
	    		$this->dx('distance').' as distance',
	    		$this->dx('distance_value').' as distance_value',	    		
	    		$this->dx('heading').' as heading'   		
	    	)
	    );
		if(empty($vehicle_ids)){
			$this->db->where('vehicle_id'.' = 0 ',NULL,FALSE);
    	}else{
			$this->db->where('vehicle_id'.' IN ('.implode(',',$vehicle_ids).') ',NULL,FALSE);
    	}
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		$this->db->order_by($this->dx('created_on'), 'DESC',FALSE);
		$results = $this->db->get('journey_cordinates')->result();
		$arr = [];
		foreach ($results as $key => $result) {
			$arr[$result->vehicle_id] = $result;
		}
		return $arr;
    }

    function get_cordinates_to_archive(){
		$this->select_all_secure('journeys');
		$this->db->where($this->dx('status')." = '2' ",NULL,FALSE);
		$this->db->where('is_archived IS NULL', null, false);
		$this->db->limit(1);
		return $this->db->get('journeys')->row();
	}

	function delete_cordinatess_in_bulk($ids = array()){
		foreach ($ids as $key => $id) {
			$this->db->where('id',$id);
			$this->db->delete('journey_cordinates');
		}
		return TRUE;
	}

}