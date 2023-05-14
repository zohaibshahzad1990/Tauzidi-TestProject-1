<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Statements_m extends MY_Model {

	protected $_table = 'invoices';

	function __construct(){
		parent::__construct();
		$this->load->dbforge();
		$this->install();
	}

	public function install(){
		$this->db->query("
		create table if not exists statements(
			id int not null auto_increment primary key,
			`transaction_type` int,
			`transaction_date` varchar(200),
			`user_id` int,
			`invoice_id` int,
			`payment_id` int,
			`amount` varchar(200),
			`balance` varchar(200),
			`active` int,
			`created_by` int,
			created_on varchar(200),
			modified_on varchar(200),
			modified_by int
		)");
	}

	function insert($input = array(),$skip_value = FALSE){
		return $this->insert_secure_data('statements',$input);
	}

	function update($id,$input,$val=FALSE){
    	return $this->update_secure_data($id,'statements',$input);
    }   
	

	function insert_statements_batch($input=array(),$SKIP_VALIDATION = FALSE){
		return $this->insert_chunked_batch_secure_data('statements',$input);
	}

    function update_where($where = "",$input = array()){
    	return $this->update_secure_where($where,'statements',$input);
    }

    function count_user_statements($user_id = 0,$date = 0){
		$this->select_all_secure('statements');
		if($user_id){
			$this->db->where($this->dx('user_id').' = "'.$user_id.'"',NULL,FALSE);
		}else{
			$this->db->where($this->dx('user_id').' = "'.$this->user->id.'"',NULL,FALSE);
		}
		return $this->db->count_all_results('statements');
	}

    function get($id=0){
		$this->select_all_secure('statements');
		$this->db->where('id',$id);
		return $this->db->get('statements')->row();
	}

    function get_user_latest_statement_entries($user_id = 0,$date = 0){
    	if($date){
	    	$this->db->select(
				array(
					" MAX(id) as id "
				)
			);
			$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
			if(empty($this->transactions->paid_transaction_types_array)||empty($this->transactions->payable_transaction_types_array)){
				$this->db->where($this->dx('transaction_type')." IN (0) ",NULL,FALSE);
			}else{
				$transaction_types_array = array_merge($this->transactions->paid_transaction_types_array,$this->transactions->payable_transaction_types_array);
				$this->db->where($this->dx('transaction_type')." IN (".implode(',',$transaction_types_array).") ",NULL,FALSE);
			}
			$this->db->group_by(
	        	array(
	        		$this->dx('user_id'),
	        	)
	        );
	        $this->where($this->dx('user_id').'="'.$user_id.'"',NULL,FALSE);
	        if($date){
				$date -= (86400*30);
				$date = strtotime(date('d-m-Y',$date));
				$this->db->where($this->dx('transaction_date').' > '.$date .' ',NULL,FALSE);
			}else{
				$this->db->where(' id = 0 ',NULL,FALSE);
			}
	        //$this->db->order_by('id','DESC',FALSE);

	        $statements = $this->db->get('statements')->result();

	        $statement_ids_array = array();

	        foreach($statements as $statement):
	        	$statement_ids_array[] = $statement->id;
	        endforeach;

			$this->select_all_secure('statements');
			if(empty($statement_ids_array)){
				$this->db->where_in('id','0');
			}else{
				$this->db->where_in('id',$statement_ids_array);
			}
			$statement_entries = $this->db->get('statements')->result();
			$arr = array();

			foreach($statement_entries as $statement_entry):
				$arr[$statement_entry->user_id] = $statement_entry;
			endforeach;
			return $arr;
		}else{
			return array();
		}
    }

    function get_user_subscription_statements($user_id=0,$date = 0){
		$this->select_all_secure('statements');		
		$this->db->where($this->dx('active').' = "1"',NULL,FALSE);      
		if(empty($this->transactions->payable_transaction_types_array)||empty($this->transactions->paid_transaction_types_array)){
			$this->db->where($this->dx('transaction_type')." IN (0) ",NULL,FALSE);
		}else{
			$transaction_types_array = array_merge($this->transactions->payable_transaction_types_array,$this->transactions->paid_transaction_types_array);
			$this->db->where($this->dx('transaction_type')." IN (".implode(',',$transaction_types_array).") ",NULL,FALSE);
		}
		$this->where($this->dx('user_id').'="'.$user_id.'"',NULL,FALSE);
		if($date){
			$date -= (86400*30);
			$date = strtotime(date('d-m-Y',$date));
			$this->db->where($this->dx('transaction_date').' >= '.$date .' ',NULL,FALSE);
		}
		//$this->db->where($this->dx('transaction_type').' IN (1,2) ',NULL,FALSE);
        $this->db->order_by($this->dx('transaction_date'), 'ASC', FALSE);
        $this->db->order_by('id','ASC',FALSE);
        return $this->db->get('statements')->result();
	}

	function void_user_statements_by_ids_array($ids_array = array()){
    	$input = array(
            'active' => 0,
            'modified_on' => time()
        );
        if(empty($this->transactions->payable_transaction_types_array)||empty($this->transactions->paid_transaction_types_array)){
			$transaction_type_list = '0';
		}else{
			$transaction_types_array = array_merge($this->transactions->payable_transaction_types_array,$this->transactions->paid_transaction_types_array);
			$transaction_type_list = implode(",",$transaction_types_array);
		}
		if(empty($ids_array)){
            $id_list = "0";
        }else{
            $id_list = implode(",",$ids_array);
        }
        $where = " id IN (".$id_list.") AND ".$this->dx('active')." = 1  AND ".$this->dx('transaction_type')." IN (".$transaction_type_list.") ";
        return $this->update_secure_where($where,'statements',$input);
    }

    function get_user_statements($user_id = 0,$date = 0){
		$this->select_all_secure('statements');
		/*if($user_id){
			$this->db->where($this->dx('user_id').' = "'.$user_id.'"',NULL,FALSE);
		}else{
			$this->db->where($this->dx('user_id').' = "'.$this->user->id.'"',NULL,FALSE);
		}*/
		$this->db->where($this->dx('active').' = "1"',NULL,FALSE);      
		if(empty($this->transactions->payable_transaction_types_array)||empty($this->transactions->paid_transaction_types_array)){
			$this->db->where($this->dx('transaction_type')." IN (0) ",NULL,FALSE);
		}else{
			$transaction_types_array = array_merge($this->transactions->payable_transaction_types_array,$this->transactions->paid_transaction_types_array);
			$this->db->where($this->dx('transaction_type')." IN (".implode(',',$transaction_types_array).") ",NULL,FALSE);
		}

		if($date){
			$date -= (86400*30);
			$date = strtotime(date('d-m-Y',$date));
			$this->db->where($this->dx('transaction_date').' >= '.$date .' ',NULL,FALSE);
		}
		//$this->db->where($this->dx('transaction_type').' IN (1,2) ',NULL,FALSE);
        $this->db->order_by($this->dx('transaction_date'), 'ASC', FALSE);
        $this->db->order_by('id','ASC',FALSE);
        return $this->db->get('statements')->result();
	}

    function delete_voided_statements(){
    	//$this->db->where($this->dx('modified_on').' >="'.strtotime('10-07-2019 16:30:00').'"',NULL,FALSE);
    	//$this->db->where($this->dx('modified_on').' <="'.strtotime('11-07-2019 14:30:00').'"',NULL,FALSE);
		$this->db->where($this->dx('active')." = '0' ",NULL,FALSE);
		return $this->db->delete('statements');
	}
}