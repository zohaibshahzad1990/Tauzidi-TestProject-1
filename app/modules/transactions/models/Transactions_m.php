<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Transactions_m extends MY_Model {

	protected $_table = 'transaction_alerts';

	function __construct(){
		parent::__construct();
		$this->load->dbforge();
		$this->install();
	}

	public function install(){

		$this->db->query("
			create table if not exists transaction_alerts(
				id int not null auto_increment primary key,
				`type` varchar(200),
				`resource_id` varchar(200),
				`transaction_id` varchar(200),
				`account_number` varchar(200),
				`transaction_type` varchar(200),
				`transaction_date` varchar(200),
				`amount` varchar(200),
				`active` varchar(200),
				`description` varchar(200),
				`particulars` varchar(200),
				created_by varchar(200),
				created_on varchar(200),
				modified_on varchar(200),
				modified_by varchar(200)
		)");
		
	}

	function insert($input=array(),$SKIP_VALIDATION = FALSE){
		return $this->insert_secure_data('transaction_alerts',$input);
	}

	function update($id,$input=array(),$SKIP_VALIDATION=FALSE){
		return $this->update_secure_data($id,'transaction_alerts',$input);
	}

    function update_where($where = "",$input = array()){
    	return $this->update_secure_where($where,'transaction_alerts',$input);
    }

    
	function get($id = 0){
		$this->select_all_secure('transaction_alerts');
		$this->db->where('id',$id);
		return $this->db->get('transaction_alerts')->row();
	}

	
	function get_transaction_alerts(){
		$this->select_all_secure('transaction_alerts');
        $this->db->order_by($this->dx('transaction_date'),'DESC',FALSE);
        $this->db->order_by($this->dx('created_on'),'DESC',FALSE);
		return $this->db->get('transaction_alerts')->result();
	}	

	function count_transaction_alerts(){
		return $this->db->count_all_results('transaction_alerts');
	}

}