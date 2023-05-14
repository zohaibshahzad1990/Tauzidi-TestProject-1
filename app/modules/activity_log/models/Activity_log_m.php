<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Activity_log_m extends MY_Model {

	protected $_table = 'activity_log';

	public function __construct(){
		parent::__construct();
		$this->load->dbforge();
		$this->install();
	}

	public function install()
	{
		$this->db->query("
			create table if not exists activity_log(
				id int not null auto_increment primary key,
				`user_id` int,
				`url` varchar(200),
				action varchar(200),
				description varchar(200),
				`ip_address` varchar(200),
				`request_method` varchar(200),
				created_by int,
				created_on varchar(200),
				modified_on varchar(200),
				modified_by int
			)"
		);

		$this->db->query("
			create table if not exists logins_log(
				id int not null auto_increment primary key,
				`user_id` int,
				`ip_address` varchar(200),
				`request_method` varchar(200),
				created_by int,
				created_on varchar(200),
				modified_on varchar(200),
				modified_by int
			)"
		);
	}

	function insert($input = array(),$skip_value = FALSE){
		return $this->insert_secure_data('activity_log',$input);
	}

	function insert_logins($input = array(),$skip_value = FALSE){
		return $this->insert_secure_data('logins_log',$input);
	}

	public function count_all($to='',$from='',$group_ids=array()){
		$group_id_list = '';
		if($group_ids){
			if(is_array($group_ids)){
				foreach ($group_ids as $group_id) {
					if($group_id_list){
						$group_id_list.=','.$group_id;
					}else{
						$group_id_list = $group_id;
					}
				}
			}else{
				$group_id_list = $group_ids;
			}

			$group_id_list = str_replace('=','',$group_id_list);
		}
		if($to && $from){
			$this->db->where($this->dx('created_on').' >="'.$from.'"',NULL,FALSE);
			$this->db->where($this->dx('created_on').' <="'.$to.'"',NULL,FALSE);
		}
		if($group_id_list){
			$this->db->where($this->dx('group_id').' IN('.$group_id_list.')',NULL,FALSE);
		}
		return $this->count_all_results('activity_log');
	}

	function count_all_activty_logs($filter_parameters = array()){
    	$this->db->select(array(
			'id as id',
			$this->dx('url').' as url',
			$this->dx('description').' as description',
			$this->dx('created_on').' as created_on',
			$this->dx('action').' as action',
			$this->dx('user_id').' as user_id',
			$this->dx('created_by').' as created_by',
			$this->dx('created_on').' as created_on',
			$this->dx('modified_by').' as modified_by',
			$this->dx('modified_on').' as modified_on',
		));
		//$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		$this->db->order_by('created_on','DESC',FALSE);
		return $this->count_all_results('activity_log');
	}

	function get_latest_activity_log(){
		$this->db->select(array(
			'id as id',
			$this->dx('url').' as url',
			$this->dx('description').' as description',
			$this->dx('created_on').' as created_on',
			"DATE_FORMAT(FROM_UNIXTIME(".$this->dx('created_on')." ),'%Y') as year ",
			"DATE_FORMAT(FROM_UNIXTIME(".$this->dx('created_on')." ),'%c') as month ",
			$this->dx('action').' as action',
			$this->dx('user_id').' as user_id',
			'COUNT(*) as count'
		));
		$this->db->where($this->dx('created_on').' >="'.strtotime("-12 month", time()).'"',NULL,FALSE);
    	$this->db->order_by('created_on','ASC',FALSE);
    	$this->db->group_by(
        	array(
        		'year',
        		'month'
        	)
        );
        $this->db->where($this->dx('user_id')." = 0 ",NULL,FALSE);
    	$activities = $this->db->get('activity_log')->result();
		return $activities;
	}

	function get_latest_logins_log(){
		$this->db->select(array(
			'id as id',
			$this->dx('user_id').' as user_id',
			$this->dx('request_method').' as request_method',
			$this->dx('created_on').' as created_on',
			"DATE_FORMAT(FROM_UNIXTIME(".$this->dx('created_on')." ),'%Y') as year ",
			"DATE_FORMAT(FROM_UNIXTIME(".$this->dx('created_on')." ),'%c') as month ",
			'COUNT(*) as count'
		));
		$this->db->where($this->dx('user_id')." != 0 ",NULL,FALSE);
		$this->db->where($this->dx('created_on').' >="'.strtotime("-12 month", time()).'"',NULL,FALSE);
    	$this->db->order_by('created_on','ASC',FALSE);
    	$this->db->group_by(
        	array(
        		'year',
        		'month'
        	)
        );
    	$activities = $this->db->get('activity_log')->result();
		return $activities;
	}

	function get_activty_logs($filter_parameters = array()){
    	$this->db->select(array(
			'id as id',
			$this->dx('url').' as url',
			$this->dx('description').' as description',
			$this->dx('created_on').' as created_on',
			$this->dx('action').' as action',
			$this->dx('user_id').' as user_id',
			$this->dx('ip_address').' as ip_address',
			$this->dx('request_method').' as request_method',
			$this->dx('created_by').' as created_by',
			$this->dx('execution_time').' as execution_time',
			$this->dx('created_on').' as created_on',
			$this->dx('modified_by').' as modified_by',
			$this->dx('modified_on').' as modified_on',
		));
		$this->db->order_by('created_on','DESC',FALSE);
		return $this->db->get('activity_log')->result();
	}

}