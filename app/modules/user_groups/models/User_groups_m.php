<?php defined('BASEPATH') OR exit('No direct script access allowed');

class User_groups_m extends MY_Model {

	protected $_table = 'groups';
	protected $params_search = array(
		'id',
		'name',
	);

	public function __construct(){
		parent::__construct();
		$this->load->dbforge();
	}
	function get($id = 0){
		$this->select_all_secure('groups');
		$this->db->where('id',$id,TRUE);
		$this->db->limit(1);
		return $this->db->get('groups')->row();
	}

	function insert($input = array(),$SKIP_VALIDATION=FALSE){
        return $this->insert_secure_data('groups', $input);
	}

	function update($id,$input,$SKIP_VALIDATION=FALSE){
		return $this->update_secure_data($id,'groups',$input);
	}

	function delete($id = 0){
	$this->db->where('id',$id,TRUE);
	return $this->db->delete('groups');
	}

	function get_user_groups($filter_parameters = array()){
		$this->select_all_secure('groups');
		if(isset($filter_parameters['search_fields'])){
    		$query_array = array();
    		if($filter_parameters['search_fields']){
	    		foreach ($filter_parameters['search_fields'] as $key => $value) {
	                if(in_array($key, $this->params_search)){
	                	if($key &&$value){
	                		$query_array[] = "CONVERT(".$this->dx($key)." using 'latin1') = '".$this->escape_str($value)."'";
	                	}
		    		}
	            }
	        }
            if(count($query_array) > 0){
            	$this->db->where(implode( ' OR ', $query_array ),NULL,FALSE);
            }
		}
		$this->db->where($this->dx('active').' ="1"',NULL,FALSE);
		return $this->db->get('groups')->result();
	}

	function get_user_groups_by_filter($filter_parameters = array()){
		$query_array = array();
		foreach ($filter_parameters as $key => $value) {
			$keySlug = "slug";
            $query_array[] = "CONVERT(".$this->dx($keySlug)." using 'latin1') = '".$this->escape_str($value)."'";
        }
        //print_r($query_array); die();
        if(count($query_array) > 0){
        	$this->db->where(implode( ' OR ', $query_array ),NULL,FALSE);
        }
        $this->db->where($this->dx('active').' ="1"',NULL,FALSE);
		$results = $this->db->get('groups')->result();
		foreach ($results as $key => $result) {
			$arr[$result->id] = $result->name;
		}
		return $arr;
	}

	function get_user_group_options($slug = ""){
		$this->db->select(
    		array('id as id',
	    		$this->dx('name').' as name'
	    	)
	    );
	   $arr = array();
		if($slug){
			$this->db->where($this->dx('slug').' = "'.$slug.'" ',NULL,FALSE);
		}
		$results =  $this->db->get('groups')->result();
		foreach ($results as $key => $result) {
			$arr[$result->id] = $result->name;
		}
		return $arr;
	}

	function get_user_group_name_slug_options(){
		$this->db->select(
    		array('id as id',
	    		$this->dx('name').' as name'
	    	)
	    );
	   $arr = array();
		$results =  $this->db->get('groups')->result();
		foreach ($results as $key => $result) {
			$arr[$result->slug] = $result;
		}
		return $arr;
	}

	function count_user_groups(){
		return $this->db->count_all_results('groups');
	}

	function count_by_user_groups($group_id = 0){
		$this->db->where($this->dx('group_id')." = ". $group_id,NULL,FALSE);
		return $this->db->count_all_results('users_groups');
	}

	function get_user_group_by_slug($slug = ''){
		$this->select_all_secure('groups');
		$this->db->where($this->dx('active').' ="1"',NULL,FALSE);
		$this->db->where($this->dx('slug')." = ".$this->db->escape($slug),NULL,FALSE);
		$this->db->limit(1);
		return $this->db->get('groups')->row();
	}

	function get_roles_by_user_ids($ids = array()){
		$this->db->select(
    		array('id as id',
	    		$this->dx('user_id').' as user_id',
	    		$this->dx('group_id').' as group_id'
	    	)
	    );	
		if(empty($ids)){
			$this->db->where('id'.' IN ( 0 ) ',NULL,FALSE);
		}else{
			$this->db->where('user_id'.' IN ('.implode(',',$ids).') ',NULL,FALSE);
		}
		$arr = array();
		$results = $this->db->get('users_groups')->result();
		foreach ($results as $key => $result) {
			$arr[$result->user_id] = array($result->group_id);
		}
		return $arr;
	}

	function get_roles_by_user_id($id  = ''){
		$this->db->select(
    		array('id as id',
	    		$this->dx('user_id').' as user_id',
	    		$this->dx('group_id').' as group_id'
	    	)
	    );	
		$this->db->where($this->dx('user_id')." = ".$user_id,NULL,FALSE);
		$arr = array();
		$results = $this->db->get('users_groups')->result();
		foreach ($results as $key => $result) {
			$arr[$result->user_id] = $result->group_id;
		}
		return $arr;
	}

	function check_if_role_exist($user_id = 0){
		$this->db->select(
    		array('id as id',
	    		$this->dx('user_id').' as user_id',
	    		$this->dx('group_id').' as group_id'
	    	)
	    );
	    $this->db->where($this->dx('user_id')." = ".$user_id,NULL,FALSE);
		$this->db->limit(1);
	    return $this->db->get('users_groups')->row();
	    
	}

	function get_role_details($user_id = 0){
		$this->db->select(
    		array('id as id',
	    		$this->dx('user_id').' as user_id',
	    		$this->dx('group_id').' as group_id'
	    	)
	    );
	    $this->db->where($this->dx('user_id')." = ".$user_id,NULL,FALSE);
		$this->db->limit(1);
	    return $this->db->get('users_groups')->row();
	    
	}


	function delete_group_by_user_id($user_id = 0){
		$this->db->where($this->dx('user_id').' = "'.$user_id.'"',NULL,FALSE);
		return $this->db->delete('users_groups');
	}
}
