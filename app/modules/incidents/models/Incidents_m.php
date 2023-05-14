<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Incidents_m extends MY_Model {

	protected $_table = 'incidents';

	function __construct(){
		parent::__construct();
		$this->load->dbforge();
		$this->install();
	}

    protected $params_search = array(
        'phone',
        'transaction_id',
        'amount',
        'is_resolved'
    );

	public function install()
	{
		$this->db->query("
		create table if not exists incidents(
			id int not null auto_increment primary key,
			`name` varchar(200),
			`category` varchar(200),
			`reported_by` int,
			`incident_code` varchar(200),
			`description` text,
			`is_resolved` int,
			`active` varchar(200),
			created_by int,
			created_on varchar(200),
			modified_on varchar(200),
			modified_by int
		)");
	}

	public function insert($input = array(),$skip_value = FALSE){
		return $this->insert_secure_data('incidents',$input);
	}


	function update($id,$input,$val=FALSE){
    	return $this->update_secure_data($id,'incidents',$input);
    }

    function is_unique_incident($transaction_id = 0){
    	$this->db->where($this->dx('transaction_id').' = "'.$transaction_id.'"',NULL,FALSE);
    	$this->db->where($this->dx('active').' = "1"',NULL,FALSE);
    	return $this->db->count_all_results('incidents')?0:1;
    }

    function get_all(){
    	$this->select_all_secure('incidents');
    	return $this->db->get('incidents')->result();
    }

    function get_all_active_unresolved(){
    	$this->select_all_secure('incidents');
    	$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
    	$this->db->where($this->dx('is_resolved')." = '0' ",NULL,FALSE);
    	return $this->db->get('incidents')->result();
    }

    function get($id=0){
    	$this->select_all_secure('incidents');
    	$this->db->where('id',$id);
    	return $this->db->get('incidents')->row();
    }

    function count_my_active_incidents($user_id = 0){    	
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		$this->db->where($this->dx('is_resolved')." = '0' ",NULL,FALSE);
		return $this->count_all_results('incidents');
	}

	function count_all_user_filetered_active_incidents($user_id = 0,$filter_parameters = array()){
    	$this->select_all_secure('incidents');  	
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
		if(isset($filter_parameters['is_resolved']) && isset($filter_parameters['is_resolved'])){
    		//if(in_array($filter_parameters['sort_field'], $this->params_search)){
    			//$this->db->where($this->dx('is_resolved'),''.$filter_parameters['is_resolved'].'',FALSE);
    		//}
		}
		$this->db->where($this->dx('reported_by')." = '".$user_id."' ",NULL,FALSE);
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		return $this->count_all_results('incidents');
	}

	function count_all_filetered_active_incidents($filter_parameters = array()){
    	$this->select_all_secure('incidents');  	
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
		if(isset($filter_parameters['is_resolved']) && isset($filter_parameters['is_resolved'])){
    		//if(in_array($filter_parameters['sort_field'], $this->params_search)){
    			//$this->db->where($this->dx('is_resolved'),''.$filter_parameters['is_resolved'].'',FALSE);
    		//}
		}
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		return $this->count_all_results('incidents');
	}

	function get_my_incidents($user_id = 0,$filter_parameters = array()){
    	$this->select_all_secure('incidents');   	
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
		if(isset($filter_parameters['is_resolved']) && isset($filter_parameters['is_resolved'])){
    		//if(in_array($filter_parameters['sort_field'], $this->params_search)){
    			//$this->db->where($this->dx('is_resolved'),''.$filter_parameters['is_resolved'].'',FALSE);
    		//}
		}
		$this->db->where($this->dx('reported_by')." = '".$user_id."' ",NULL,FALSE);
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		$this->db->order_by($this->dx('created_on'),'DESC',FALSE);
		return $this->db->get('incidents')->result();
	}

	function get_all_incidents($filter_parameters = array()){
    	$this->select_all_secure('incidents');   	
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
		if(isset($filter_parameters['is_resolved']) && isset($filter_parameters['is_resolved'])){
    		//if(in_array($filter_parameters['sort_field'], $this->params_search)){
    			//$this->db->where($this->dx('is_resolved'),''.$filter_parameters['is_resolved'].'',FALSE);
    		//}
		}
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		$this->db->order_by($this->dx('created_on'),'DESC',FALSE);
		return $this->db->get('incidents')->result();
	}

	function count_my_active_resolved_incidents($user_id = 0){    	
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		$this->db->where($this->dx('is_resolved')." = '1' ",NULL,FALSE);
		return $this->count_all_results('incidents');
	}

	function get_all_active_resolved(){
    	$this->select_all_secure('incidents');
    	$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
    	$this->db->where($this->dx('is_resolved')." = '1' ",NULL,FALSE);
    	return $this->db->get('incidents')->result();
    }

	function get_all_active_incidents($user_id = 0){
    	$this->select_all_secure('incidents');
    	$this->db->where($this->dx('is_resolved')." = '0' ",NULL,FALSE);
    	$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
    	return $this->db->get('incidents')->result();
    }

    function calculate_incident_number($user_id=0){
        //$this->db->where($this->dx('user_id').'="'.$user_id.'"',NULL,FALSE);
        $this->db->from('incidents');
        $count = $this->db->count_all_results();
        $count = $count+1;
        if($count>=100){
        }else if($count>=10){
            $count = 'INC00'.$user_id.''.$count;
        }else{
            $count='INC000'.$user_id.''.$count;
        }
        return $count;
    }
}