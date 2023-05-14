<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Notifications_m extends MY_Model {

	protected $_table = 'notifications';

	protected $params_search = array(
		'id',
		'subject',
		'is_read'
	);

	function __construct(){
		$this->load->dbforge();
		$this->install();
	}

	public function install(){
		$this->db->query("
		create table if not exists notifications(
			id int not null auto_increment primary key,
		  	`from_user_id` int,
		  	`to_user_id` int,
		  	`subject` varchar(200),
		  	`message` varchar(200),
		  	`is_read` varchar(200),
		  	`call_to_action` varchar(200),
			`call_to_action_link` varchar(200),
			`file_size` varchar(200),
			`file_path` varchar(200),
			`file_type` varchar(200),
		  	`active` varchar(200),
		  	`created_by` varchar(200),
		  	`created_on` varchar(200),
		  	`modified_by` varchar(200),
			modified_on varchar(200)
		)");
	}

	public function insert($input = array(),$key=FALSE){
		return $this->insert_secure_data('notifications', $input);
	}

	public function insert_batch($input = array(),$key=FALSE){
		return $this->insert_chunked_batch_secure_data('notifications', $input);
	}

	function get($id=0){
		$this->select_all_secure('notifications');
		$this->db->where('id',$id);
		return $this->db->get('notifications')->row();
	}

	function count_all_filetered_active_notifications($user_id = 0,$filter_parameters = array()){
    	$this->db->select(array(
			'id as id',
			$this->dx('from_user_id').' as from_user_id',
			$this->dx('to_user_id').' as to_user_id',
			$this->dx('subject').' as subject',
			$this->dx('message').' as message',
			$this->dx('is_read').' as is_read',
			$this->dx('active').' as active',
			$this->dx('created_by').' as created_by',
			$this->dx('created_on').' as created_on',
			$this->dx('modified_by').' as modified_by',
			$this->dx('modified_on').' as modified_on',
		));  	
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
		$this->db->where($this->dx('to_user_id')." = '".$user_id."' ",NULL,FALSE);
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		return $this->count_all_results('notifications');
	}

	function get_my_notifications($user_id = 0,$filter_parameters = array()){
    	$this->db->select(array(
			'id as id',
			$this->dx('from_user_id').' as from_user_id',
			$this->dx('to_user_id').' as to_user_id',
			$this->dx('subject').' as subject',
			$this->dx('message').' as message',
			$this->dx('is_read').' as is_read',
			$this->dx('active').' as active',
			$this->dx('created_by').' as created_by',
			$this->dx('created_on').' as created_on',
			$this->dx('modified_by').' as modified_by',
			$this->dx('modified_on').' as modified_on',
		));  	
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
		$this->db->where($this->dx('to_user_id')." = '".$user_id."' ",NULL,FALSE);
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		$this->db->order_by($this->dx('created_on'),'DESC',FALSE);
		return $this->db->get('notifications')->result();
	}

	public function count_unread_user_notifications($user_id=0){
		if($user_id){
			$this->db->where($this->dx('to_user_id').' = '.$user_id,NULL,FALSE);
		}else{
			$this->db->where($this->dx('to_user_id').' = '.$this->user->id,NULL,FALSE);
		}
		$this->db->where($this->dx('active').' = "1" ',NULL,FALSE);
		$this->db->where($this->dx('is_read').' = "0" ',NULL,FALSE);
		$this->db->order_by($this->dx('created_on'),'DESC',FALSE);
		return $this->db->count_all_results('notifications');
	}

	function update($id,$input,$val=FALSE){
    	return $this->update_secure_data($id,'notifications',$input);
    }

	function delete_old_notifications(){
		$this->db->where($this->dx('created_on')." < '".strtotime('-1 month')."' ",NULL,FALSE);
		return $this->db->delete('notifications');
	}

	function get_old_notifications_by_date($date = 0){
		$this->select_all_secure('notifications');		
		$this->db->where($this->dx('active').' = "1"',NULL,FALSE); 
		$date = strtotime(date('d-m-Y',$date));
		$this->db->limit(50);
		$this->db->where($this->dx('created_on').' <= '.$date .' ',NULL,FALSE);
        return $this->db->get('notifications')->result();
	}

	function mark_as_read_notifications_bulk($ids = array()){
		if(empty($ids)){
	    	$where = " id = 0 ;";
    	}else{
	    	$where = " id IN (".implode(',',array_filter($ids)).") AND ".$this->dx('active')." = 1 ;";
    	}
		$input = array(
			'is_read'=>1,
			'modified_on' => time(),
		);
		$this->update_secure_where($where,'notifications',$input);
		return $this->db->affected_rows();
	}

	function delete_notifications_in_bulk($ids = array()){
		foreach ($ids as $key => $id) {
			$this->db->where('id',$id);
			$this->db->delete('notifications');
		}
		return TRUE;
	}

}