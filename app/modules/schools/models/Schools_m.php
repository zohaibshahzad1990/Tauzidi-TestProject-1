<?php defined('BASEPATH') OR exit('No direct script access allowed');

class schools_m extends MY_Model {

	protected $_table = 'schools';

	protected $params_search = array(
		'id',
		'name'
	);

	public function __construct(){
		parent::__construct();
		$this->load->dbforge();
		$this->install();
	}

	function install(){
		$this->db->query("create table if not exists schools(
			id int not null auto_increment primary key,
			`name` varchar(200),
			`slug` varchar(200),
			`description` varchar(200),
			`user_id` int,
			`active` int,
			created_by varchar(200),
			created_on varchar(200),
			modified_on varchar(200),
			modified_by varchar(200)
		)");

		$this->db->query("create table if not exists school_class_pairings(
			id int not null auto_increment primary key,
			`name` varchar(200),
			`description` varchar(200),
			`school_id` int,
			`user_id` int,
			`active` int,
			created_by varchar(200),
			created_on varchar(200),
			modified_on varchar(200),
			modified_by varchar(200)
		)");

		$this->db->query("create table if not exists class_subject_pairings(
			id int not null auto_increment primary key,
			`name` varchar(200),
			`description` varchar(200),
			`class_id` int,
			`school_id` int,
			`user_id` int,
			`active` int,
			created_by varchar(200),
			created_on varchar(200),
			modified_on varchar(200),
			modified_by varchar(200)
		)");
	}

	function insert($input = array(),$SKIP_VALIDATION=FALSE){
        return $this->insert_secure_data('schools', $input);
	}

	function insert_classes($input = array(),$SKIP_VALIDATION=FALSE){
        return $this->insert_secure_data('school_class_pairings', $input);
	}

	function insert_subjects($input = array(),$SKIP_VALIDATION=FALSE){
        return $this->insert_secure_data('class_subject_pairings', $input);
	}

	function get_all(){
		$this->select_all_secure('schools');
		return $this->db->get('schools')->result();
	}

	function insert_batch($input = array(),$SKIP_VALIDATION=FALSE){
        return $this->insert_batch_secure_data('schools', $input);
	}

	function insert_batch_schools($input = array(),$SKIP_VALIDATION=FALSE){
        return $this->insert_batch_secure_data('schools', $input);
	}

	function insert_batch_classes($input = array(),$SKIP_VALIDATION=FALSE){
        return $this->insert_batch_secure_data('school_class_pairings', $input);
	}

	function update($id,$input,$SKIP_VALIDATION=FALSE){
    	return $this->update_secure_data($id,'schools',$input);
    }

    function update_class($id,$input,$SKIP_VALIDATION=FALSE){
    	return $this->update_secure_data($id,'school_class_pairings',$input);
    }

    function update_subject($id,$input,$SKIP_VALIDATION=FALSE){
    	return $this->update_secure_data($id,'class_subject_pairings',$input);
    }

    function count_subjects_by_class_id($class_id){
    	$this->db->where($this->dx('class_id').' = "'.$class_id.'"',NULL,FALSE);
    	$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		return $this->count_all_results('class_subject_pairings');
    }

   function count_all_active_schools(){
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		return $this->count_all_results('schools');
	}

    function get_subjects_by_class_ids_array($class_id){
    	$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('name').' as name',
	    		$this->dx('user_id').' as user_id',
	    		$this->dx('description').' as description'
	    	)
	    ); 
    	$this->db->where($this->dx('class_id').' = "'.$class_id.'"',NULL,FALSE);
    	$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		$results =  $this->db->get('class_subject_pairings')->result();
		$arr = array();
		foreach ($results as $key => $result) {
			$arr[] = $result->id;
		}
		return $arr;
    }

    function count_all_filetered_active_schools($filter_parameters = array()){
    	$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('name').' as name',
	    		$this->dx('user_id').' as user_id',
	    		$this->dx('description').' as description'
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
    		if(in_array($filter_parameters['sort_field'], $this->params_search)){
    			$this->db->order_by($this->dx($filter_parameters['sort_field']),''.$filter_parameters['sort_order'].'',FALSE);
    		}
		}
    	if(isset($filter_parameters['sort_order']) && isset($filter_parameters['sort_field'])){
    		if(in_array($filter_parameters['sort_field'], $this->params_search)){
    			$this->db->order_by($this->dx($filter_parameters['sort_field']),''.$filter_parameters['sort_order'].'',FALSE);
    		}
		}
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		return $this->count_all_results('schools');
	}

	function count_schools_by_user_id($user_id = 0,$filter_parameters = array()){
    	$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('name').' as name',
	    		$this->dx('user_id').' as user_id',
	    		$this->dx('description').' as description'
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
    		if(in_array($filter_parameters['sort_field'], $this->params_search)){
    			$this->db->order_by($this->dx($filter_parameters['sort_field']),''.$filter_parameters['sort_order'].'',FALSE);
    		}
		}
    	if(isset($filter_parameters['sort_order']) && isset($filter_parameters['sort_field'])){
    		if(in_array($filter_parameters['sort_field'], $this->params_search)){
    			$this->db->order_by($this->dx($filter_parameters['sort_field']),''.$filter_parameters['sort_order'].'',FALSE);
    		}
		}
		$this->db->where($this->dx('user_id').' = "'.$user_id.'"',NULL,FALSE);
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		return $this->count_all_results('schools');
	}

	function get($id = 0){
		$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('name').' as name',
	    		$this->dx('active').' as active',
	    		$this->dx('user_id').' as user_id',
	    		$this->dx('description').' as description',
	    		$this->dx('created_on').' as created_on',
	    		$this->dx('created_by').' as created_by',
	    		$this->dx('modified_on').' as modified_on',
	    		$this->dx('modified_by').' as modified_by',
	    	)
	    );
	    $this->db->where('id',$id);
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);
    	return $this->db->get('schools')->row();
	}

	function get_school_by_ids($school_ids = array()){
		$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('name').' as name',
	    		$this->dx('active').' as active',
	    		$this->dx('user_id').' as user_id',
	    		$this->dx('description').' as description',
	    		$this->dx('created_on').' as created_on',
	    		$this->dx('created_by').' as created_by',
	    		$this->dx('modified_on').' as modified_on',
	    		$this->dx('modified_by').' as modified_by',
	    	)
	    );
	    if(empty($school_ids)){
			$this->db->where('id'.' = 0 ',NULL,FALSE);
    	}else{
			$this->db->where('id'.' IN ('.implode(',',$school_ids).') ',NULL,FALSE);
    	}
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);
    	return $this->db->get('schools')->result();
		
	}

	function get_school_options_by_ids($school_ids = array()){
		$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('name').' as name',
	    		$this->dx('active').' as active',
	    		$this->dx('user_id').' as user_id',
	    		$this->dx('description').' as description',
	    		$this->dx('created_on').' as created_on',
	    		$this->dx('created_by').' as created_by',
	    		$this->dx('modified_on').' as modified_on',
	    		$this->dx('modified_by').' as modified_by',
	    	)
	    );
	    if(empty($school_ids)){
			$this->db->where('id'.' = 0 ',NULL,FALSE);
    	}else{
			$this->db->where('id'.' IN ('.implode(',',$school_ids).') ',NULL,FALSE);
    	}
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);
    	$arr = array();
    	$results =  $this->db->get('schools')->result();
    	foreach ($results as $key => $result) {
    		$arr[$result->id] = $result->name;
    	}
    	return $arr;
		
	}

	function get_school_options(){
		$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('name').' as name',
	    		$this->dx('active').' as active',
	    		$this->dx('user_id').' as user_id',
	    		$this->dx('description').' as description',
	    		$this->dx('created_on').' as created_on',
	    		$this->dx('created_by').' as created_by',
	    		$this->dx('modified_on').' as modified_on',
	    		$this->dx('modified_by').' as modified_by',
	    	)
	    );
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);
	    $arr = array();
    	$results =  $this->db->get('schools')->result();
    	foreach ($results as $key => $result) {
    		$arr[$result->id] = $result->name;
    	}
    	return $arr;
		
	}

	function get_school_by_id_options_array($school_ids = array()){
		$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('name').' as name',
	    		$this->dx('active').' as active',
	    		$this->dx('user_id').' as user_id',
	    		$this->dx('description').' as description',
	    		$this->dx('created_on').' as created_on',
	    		$this->dx('created_by').' as created_by',
	    		$this->dx('modified_on').' as modified_on',
	    		$this->dx('modified_by').' as modified_by',
	    	)
	    );
	    if(empty($school_ids)){
			$this->db->where('id'.' = 0 ',NULL,FALSE);
    	}else{
			$this->db->where('id'.' IN ('.implode(',',$school_ids).') ',NULL,FALSE);
    	}
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);
    	$results = $this->db->get('schools')->result();
    	$arr = array();
    	foreach ($results as $key => $result) {
    		$arr[$result->id] = $result;
    	}
    	return $arr;
		
	}

	function get_class($id = 0){
		$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('name').' as name',
	    		$this->dx('active').' as active',
	    		$this->dx('user_id').' as user_id',
	    		$this->dx('school_id').' as school_id',
	    		$this->dx('description').' as description',
	    		$this->dx('created_on').' as created_on',
	    		$this->dx('created_by').' as created_by',
	    		$this->dx('modified_on').' as modified_on',
	    		$this->dx('modified_by').' as modified_by',
	    	)
	    );
	    $this->db->where('id',$id);
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);
    	return $this->db->get('school_class_pairings')->row();
	}

	function get_class_by_ids($class_ids = array()){
		$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('name').' as name',
	    		$this->dx('user_id').' as user_id',
	    		$this->dx('description').' as description',
	    		$this->dx('school_id').' as school_id',
	    		$this->dx('created_on').' as created_on',
	    		$this->dx('created_by').' as created_by',
	    		$this->dx('modified_on').' as modified_on',
	    		$this->dx('modified_by').' as modified_by',
	    	)
	    );
	    if(empty($class_ids)){
			$this->db->where('id'.' = 0 ',NULL,FALSE);
    	}else{
			$this->db->where('id'.' IN ('.implode(',',$class_ids).') ',NULL,FALSE);
    	}
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);
    	return $this->db->get('school_class_pairings')->result();
		
	}

	function get_class_option_by_ids($class_ids = array()){
		$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('name').' as name',
	    		$this->dx('user_id').' as user_id',
	    		$this->dx('description').' as description',
	    		$this->dx('school_id').' as school_id',
	    		$this->dx('created_on').' as created_on',
	    		$this->dx('created_by').' as created_by',
	    		$this->dx('modified_on').' as modified_on',
	    		$this->dx('modified_by').' as modified_by',
	    	)
	    );
	    if(empty($class_ids)){
			$this->db->where('id'.' = 0 ',NULL,FALSE);
    	}else{
			$this->db->where('id'.' IN ('.implode(',',$class_ids).') ',NULL,FALSE);
    	}
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);
    	$results =  $this->db->get('school_class_pairings')->result();
    	$arr = array();
    	foreach ($results as $key => $result) {
    		$arr[$result->id] = $result->name;
    	}
    	return $arr;
		
	}

	function get_class_by_id_options_array($class_ids = array()){
		$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('name').' as name',
	    		$this->dx('user_id').' as user_id',
	    		$this->dx('description').' as description',
	    		$this->dx('school_id').' as school_id',
	    		$this->dx('created_on').' as created_on',
	    		$this->dx('created_by').' as created_by',
	    		$this->dx('modified_on').' as modified_on',
	    		$this->dx('modified_by').' as modified_by',
	    	)
	    );
	    if(empty($class_ids)){
			$this->db->where('id'.' = 0 ',NULL,FALSE);
    	}else{
			$this->db->where('id'.' IN ('.implode(',',$class_ids).') ',NULL,FALSE);
    	}
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);
    	$results = $this->db->get('school_class_pairings')->result();
    	$arr = array();
    	foreach ($results as $key => $result) {
    		$arr[$result->id] = $result;
    	}
    	return $arr;
		
	}

	/*function get_subject($id = 0){
		$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('name').' as name',
	    		$this->dx('active').' as active',
	    		$this->dx('user_id').' as user_id',
	    		$this->dx('school_id').' as school_id',
	    		$this->dx('class_id').' as class_id',
	    		$this->dx('description').' as description',
	    		$this->dx('created_on').' as created_on',
	    		$this->dx('created_by').' as created_by',
	    		$this->dx('modified_on').' as modified_on',
	    		$this->dx('modified_by').' as modified_by',
	    	)
	    );
	    $this->db->where('id',$id);
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);
    	return $this->db->get('class_subject_pairings')->row();
	}*/

	function get_my_school($id =0 ,$user_id =0){
		$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('name').' as name',
	    		$this->dx('user_id').' as user_id',
	    		$this->dx('description').' as description',
	    		$this->dx('created_on').' as created_on',
	    		$this->dx('created_by').' as created_by',
	    		$this->dx('modified_on').' as modified_on',
	    		$this->dx('modified_by').' as modified_by',
	    	)
	    );
	    $this->db->where('id',$id);
	    $this->db->where($this->dx('user_id').' = "'.$user_id.'"',NULL,FALSE);
    	$this->db->where($this->dx('active')."= '1'",NULL,FALSE);
    	return $this->db->get('schools')->row();
	}

	function get_my_class_by_ids($class_ids = array() ,$user_id =0){
		$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('name').' as name',
	    		$this->dx('user_id').' as user_id',
	    		$this->dx('description').' as description',
	    		$this->dx('school_id').' as school_id',
	    		$this->dx('created_on').' as created_on',
	    		$this->dx('created_by').' as created_by',
	    		$this->dx('modified_on').' as modified_on',
	    		$this->dx('modified_by').' as modified_by',
	    	)
	    );
	    if(empty($class_ids)){
			$this->db->where('id'.' = 0 ',NULL,FALSE);
    	}else{
			$this->db->where('id'.' IN ('.implode(',',$class_ids).') ',NULL,FALSE);
    	}
	    $this->db->where($this->dx('user_id').' = "'.$user_id.'"',NULL,FALSE);
    	$this->db->where($this->dx('active')."= '1'",NULL,FALSE);
    	return $this->db->get('school_class_pairings')->result();
	}

	function get_my_class($id =0 ,$user_id =0){
		$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('name').' as name',
	    		$this->dx('user_id').' as user_id',
	    		$this->dx('description').' as description',
	    		$this->dx('education_level_ids').' as education_level_ids',	    		
	    		$this->dx('school_id').' as school_id',
	    		$this->dx('created_on').' as created_on',
	    		$this->dx('created_by').' as created_by',
	    		$this->dx('modified_on').' as modified_on',
	    		$this->dx('modified_by').' as modified_by',
	    	)
	    );
	    $this->db->where('id',$id);
	    $this->db->where($this->dx('user_id').' = "'.$user_id.'"',NULL,FALSE);
    	$this->db->where($this->dx('active')."= '1'",NULL,FALSE);
    	return $this->db->get('school_class_pairings')->row();
	}

	function get_active_schools($filter_parameters = array()){
    	$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('user_id').' as user_id',
	    		$this->dx('name').' as name',
	    		$this->dx('description').' as description',
	    		$this->dx('created_on').' as created_on'
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
    		if(in_array($filter_parameters['sort_field'], $this->params_search)){
    			$this->db->order_by($this->dx($filter_parameters['sort_field']),''.$filter_parameters['sort_order'].'',FALSE);
    		}
		}
    	if(isset($filter_parameters['sort_order']) && isset($filter_parameters['sort_field'])){
    		if(in_array($filter_parameters['sort_field'], $this->params_search)){
    			$this->db->order_by($this->dx($filter_parameters['sort_field']),''.$filter_parameters['sort_order'].'',FALSE);
    		}
		}
    	$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
    	return $this->db->get('schools')->result();
    }

    function get_schools_by_user_id($user_id = 0, $filter_parameters = array()){
    	$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('user_id').' as user_id',
	    		$this->dx('name').' as name',
	    		$this->dx('description').' as description',
	    		$this->dx('created_on').' as created_on'
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
    		if(in_array($filter_parameters['sort_field'], $this->params_search)){
    			$this->db->order_by($this->dx($filter_parameters['sort_field']),''.$filter_parameters['sort_order'].'',FALSE);
    		}
		}
    	if(isset($filter_parameters['sort_order']) && isset($filter_parameters['sort_field'])){
    		if(in_array($filter_parameters['sort_field'], $this->params_search)){
    			$this->db->order_by($this->dx($filter_parameters['sort_field']),''.$filter_parameters['sort_order'].'',FALSE);
    		}
		}
		$this->db->where($this->dx('user_id').' = "'.$user_id.'"',NULL,FALSE);
    	$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
    	return $this->db->get('schools')->result();
    }

    function get_filtered_school_ids($filter_parameters = array()){
		$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('name').' as name',
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
		$this->db->where($this->dx('active')."= '1'",NULL,FALSE);
		$results = $this->db->get('schools')->result();
    	$arr = array();
    	foreach ($results as $key => $result) {
    		$arr[$result->id] = $result->id;
    	}
    	return $arr;
	}

    function get_classes_by_school_ids_array($school_ids= array(), $filter_parameters = array()){
    	$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('user_id').' as user_id',
	    		$this->dx('school_id').' as school_id',
	    		$this->dx('name').' as name',
	    		$this->dx('description').' as description',
	    		$this->dx('created_on').' as created_on'
	    	)
	    );
	    if(empty($school_ids)){
			$this->db->where($this->dx('school_id').' = 0 ',NULL,FALSE);
    	}else{
			$this->db->where($this->dx('school_id').' IN ('.implode(',',$school_ids).') ',NULL,FALSE);
    	}
    	$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
    	return $this->db->get('school_class_pairings')->result();
    }

    function get_classes_by_school_id($school_id = 0, $filter_parameters = array()){
    	$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('user_id').' as user_id',
	    		$this->dx('school_id').' as school_id',
	    		$this->dx('name').' as name',
	    		$this->dx('education_level_ids').' as education_level_ids',	    		
	    		$this->dx('description').' as description',
	    		$this->dx('created_on').' as created_on'
	    	)
	    );
		$this->db->where($this->dx('school_id').' = "'.$school_id.'"',NULL,FALSE);
    	$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
    	return $this->db->get('school_class_pairings')->result();
    }

    function get_subject_by_ids($ids =0){
		$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('name').' as name',
	    		$this->dx('user_id').' as user_id',
	    		$this->dx('description').' as description',
	    		$this->dx('school_id').' as school_id',
	    		$this->dx('class_id').' as class_id',	    		
	    		$this->dx('created_on').' as created_on',
	    		$this->dx('created_by').' as created_by',
	    		$this->dx('modified_on').' as modified_on',
	    		$this->dx('modified_by').' as modified_by',
	    	)
	    );
	    if(empty($ids)){
			$this->db->where('id'.' = 0 ',NULL,FALSE);
    	}else{
			$this->db->where('id'.' IN ('.implode(',',$ids).') ',NULL,FALSE);
    	}
    	$this->db->where($this->dx('active')."= '1'",NULL,FALSE);
    	return $this->db->get('class_subject_pairings')->result();
	}

	function get_subject_ids(){
		$this->db->select(
    		array('id as id',
    			$this->dx('name').' as name',
	    		$this->dx('created_by').' as created_by',
	    		$this->dx('modified_on').' as modified_on',
	    		$this->dx('modified_by').' as modified_by',
	    	)
	    );
    	$this->db->where($this->dx('active')."= '1'",NULL,FALSE);
    	$results = $this->db->get('class_subject_pairings')->result();
    	$arr = array();
    	foreach ($results as $key => $result) {
    		$arr[$result->id] = $result->name;
    	}
    	return $arr;
	}

	function get_class_subject_ids(){
		$this->db->select(
    		array('id as id',
    			$this->dx('name').' as name',
    			$this->dx('class_id').' as class_id',
	    		$this->dx('created_by').' as created_by',
	    		$this->dx('modified_on').' as modified_on',
	    		$this->dx('modified_by').' as modified_by',
	    	)
	    );
    	$this->db->where($this->dx('active')."= '1'",NULL,FALSE);
    	$results = $this->db->get('class_subject_pairings')->result();
    	$arr = array();
    	foreach ($results as $key => $result) {
    		$arr[$result->id] = array(
    			"subject"=>$result->name,
    			"class_id"=>$result->class_id
    		);
    	}
    	return $arr;
	}

    function get_subject($id =0 ,$user_id =0){
		$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('name').' as name',
	    		$this->dx('user_id').' as user_id',
	    		$this->dx('description').' as description',
	    		$this->dx('school_id').' as school_id',
	    		$this->dx('class_id').' as class_id',	    		
	    		$this->dx('created_on').' as created_on',
	    		$this->dx('created_by').' as created_by',
	    		$this->dx('modified_on').' as modified_on',
	    		$this->dx('modified_by').' as modified_by',
	    	)
	    );
	    $this->db->where('id',$id);
	    $this->db->where($this->dx('user_id').' = "'.$user_id.'"',NULL,FALSE);
    	$this->db->where($this->dx('active')."= '1'",NULL,FALSE);
    	return $this->db->get('class_subject_pairings')->row();
	}

	function get_school_class_subjects_ids_array($school_ids = array(),$class_ids = array()){
    	$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('name').' as name',
	    		$this->dx('user_id').' as user_id',
	    		$this->dx('description').' as description',
	    		$this->dx('class_id').' as class_id',
	    		$this->dx('school_id').' as school_id',
	    		$this->dx('created_on').' as created_on',
	    		$this->dx('created_by').' as created_by',
	    		$this->dx('modified_on').' as modified_on',
	    		$this->dx('modified_by').' as modified_by',
	    	)
	    );
	    if($this->user->id){
	   	 	$this->db->where($this->dx('user_id').' = "'.$this->user->id.'"',NULL,FALSE);
	   	}else{
	   		//$this->db->where($this->dx('user_id').' = "'.$user_id.'"',NULL,FALSE);
	   	}
	   	if(empty($school_ids)){
			$this->db->where('id'.' = 0 ',NULL,FALSE);
    	}else{
			$this->db->where($this->dx('school_id').' IN ('.implode(',',$school_ids).') ',NULL,FALSE);
    	}
	   	if(empty($class_ids)){
			$this->db->where('id'.' = 0 ',NULL,FALSE);
    	}else{
			$this->db->where($this->dx('class_id').' IN ('.implode(',',$class_ids).') ',NULL,FALSE);
    	}
	   	//$this->db->where($this->dx('school_id').' = "'.$school_id.'"',NULL,FALSE);
	   	//$this->db->where($this->dx('class_id').' = "'.$class_id.'"',NULL,FALSE);
    	$this->db->where($this->dx('active')."= '1'",NULL,FALSE);
    	return $this->db->get('class_subject_pairings')->result();
    }

    function get_school_class_subjects($school_id = 0,$class_id = 0){
    	$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('name').' as name',
	    		$this->dx('user_id').' as user_id',
	    		$this->dx('description').' as description',
	    		$this->dx('class_id').' as class_id',
	    		$this->dx('school_id').' as school_id',
	    		$this->dx('created_on').' as created_on',
	    		$this->dx('created_by').' as created_by',
	    		$this->dx('modified_on').' as modified_on',
	    		$this->dx('modified_by').' as modified_by',
	    	)
	    );
	    if($this->user->id){
	   	 	$this->db->where($this->dx('user_id').' = "'.$this->user->id.'"',NULL,FALSE);
	   	}else{
	   		//$this->db->where($this->dx('user_id').' = "'.$user_id.'"',NULL,FALSE);
	   	}
	   	$this->db->where($this->dx('school_id').' = "'.$school_id.'"',NULL,FALSE);
	   	$this->db->where($this->dx('class_id').' = "'.$class_id.'"',NULL,FALSE);
    	$this->db->where($this->dx('active')."= '1'",NULL,FALSE);
    	return $this->db->get('class_subject_pairings')->result();
    }



    function delete($id=0){
		$this->db->where('id',$id);
		return $this->db->delete('schools');
    } 

    function get_school_by_slug($slug = ''){
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
		return $this->db->get('schools')->row();
	}  

	function get_filtered_class_ids($filter_parameters = array()){
		$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('name').' as name',
	    		$this->dx('school_id').' as school_id',
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
		$this->db->where($this->dx('active')."= '1'",NULL,FALSE);
		$results = $this->db->get('school_class_pairings')->result();
    	$arr = array();
    	foreach ($results as $key => $result) {
    		$arr[$result->id] = $result->id;
    	}
    	return $arr;
	}

	function get_class_by_slug($slug = '',$school_id =0){
		$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('name').' as name',
	    		$this->dx('school_id').' as school_id',
	    	)
	    );
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);	
	    $this->db->where($this->dx('school_id').' = "'.$school_id.'"',NULL,FALSE);
		$this->db->where($this->dx('slug')." = ".$this->db->escape($slug),NULL,FALSE);
		$this->db->limit(1);
		return $this->db->get('school_class_pairings')->row();
	}

	function get_subject_by_slug($slug = '',$class_id =0){
		$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('name').' as name',
	    		$this->dx('school_id').' as school_id',
	    		$this->dx('class_id').' as class_id',
	    	)
	    );	
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);
	    $this->db->where($this->dx('class_id').' = "'.$class_id.'"',NULL,FALSE);
		$this->db->where($this->dx('slug')." = ".$this->db->escape($slug),NULL,FALSE);
		$this->db->limit(1);
		return $this->db->get('class_subject_pairings')->row();		
	}




}?>
