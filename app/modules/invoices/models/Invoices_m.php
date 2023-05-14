<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Invoices_m extends MY_Model {

	protected $_table = 'invoices';

	protected $params_search = array(
		'id',
		'invoice_date'
	);

	function __construct(){
		parent::__construct();
		$this->load->dbforge();
		$this->load->model('deposits/deposits_m');
		$this->load->model('billing/billing_m');		
		$this->install();
	}

	public function install(){
		$this->db->query("
		create table if not exists invoices(
			id int not null auto_increment primary key,
			`type` int,
			`invoice_number` varchar(200),
			`parent_id` int,
			`user_id` int,
			`billing_package_id` int,
			`resource_id` int,
			`school_id` int,
			`class_id` int,
			`subject_id` int,
			`invoice_date` varchar(200),
			`due_date` varchar(200),
			`subcription_end_date` varchar(200),
			`amount_payable` varchar(200),
			`amount_paid` varchar(200),
			`description` varchar(200),
			`active` int,
			`created_by` int,
			created_on int,
			modified_on int,
			modified_by int
		)");

        $this->db->query("
        create table if not exists invoice_to_pay(
            id int not null auto_increment primary key,
            `invoice_id` int,
            `active` int,
            `created_by` int,
            created_on int,
            modified_on int,
            modified_by int
        )");
	}

	function insert($input = array(),$skip_value = FALSE){
		return $this->insert_secure_data('invoices',$input);
	}

    function insert_invoice_to_pay($input = array(),$skip_value = FALSE){
        return $this->insert_secure_data('invoice_to_pay',$input);
    }

	function update($id,$input,$val=FALSE){
    	return $this->update_secure_data($id,'invoices',$input);
    }

    function update_where($where = "",$input = array()){
    	return $this->update_secure_where($where,'invoices',$input);
    }

    function get($id=0){
		$this->select_all_secure('invoices');
		$this->db->where('id',$id);
		return $this->db->get('invoices')->row();
	}

    function get_invoice_to_pay($id=0){
        $this->select_all_secure('invoice_to_pay');
        $this->db->where('id',$id);
        return $this->db->get('invoice_to_pay')->row();
    }

    function count_user_invoices($user_id=0,$filter_parameters = array()){
		if($user_id){
			$this->db->where($this->dx('user_id').' = "'.$user_id.'"',NULL,FALSE);
		}else{
			$this->db->where($this->dx('user_id').' = "'.$this->user->id.'"',NULL,FALSE);
		}
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
		$this->db->where($this->dx('active').' = "1"',NULL,FALSE);
		return $this->db->count_all_results('invoices');
	}

    function get_user_invoices($user_id=0 ,$filter_parameters = array()){
		$this->select_all_secure('invoices');
		if($user_id){
			$this->db->where($this->dx('user_id').' = "'.$user_id.'"',NULL,FALSE);
		}else{
			$this->db->where($this->dx('user_id').' = "'.$this->user->id.'"',NULL,FALSE);
		}
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
		$this->db->where($this->dx('active').' = "1"',NULL,FALSE);
		$this->db->order_by($this->dx('invoice_date'),'DESC',FALSE);
		return $this->db->get('invoices')->result();
	}

    function calculate_billing_invoice_number($user_id=0){
        $this->db->where($this->dx('user_id').'="'.$user_id.'"',NULL,FALSE);
        $this->db->from('invoices');
        $count = $this->db->count_all_results();
        $count = $count+1;
        if($count>=100){
        }else if($count>=10){
            $count = '0'.$user_id.''.$count;
        }else{
            $count='00'.$user_id.''.$count;
        }
        return $count;
    }

    function get_user_account_arrears($user_id = 0){
        if($user_id){
        	$deposit_amount = $this->deposits_m->get_user_total_deposits_amount($user_id);
            $amount_paid = $this->billing_m->get_user_billing_paid_amount($user_id);
            $amount_payable = $this->get_user_subscription_amount_payable($user_id);        
            $arrears = $amount_payable - $amount_paid;
            /*print_r($deposit_amount);
            echo "<br>";
            print_r($amount_paid);
            echo "<br>";
            print_r($arrears);
            echo "<br>";
            print_r($amount_payable); die()*/;
            return $arrears;
        }else{
            return FALSE;
        }
    }

    function get_user_subscription_amount_payable($user_id=0){
        $this->db->select('sum('.$this->dx('amount_payable').') as amount_payable');
        $this->db->where($this->dx('active').'="1"',NULL,FALSE);
        $this->db->where($this->dx('user_id').'="'.$user_id.'"',NULL,FALSE);
        $amount_payable = $this->db->get('invoices')->row();
        if($amount_payable){
            return $amount_payable->amount_payable;
        }else{
            return 0;
        }
    }

    function get_user_unpaid_invoices($user_id=0){
        $this->select_all_secure('invoices');
        $this->db->where($this->dx('active').'="1"',NULL,FALSE);
        $this->db->where('('.$this->dx('status').' IS NULL OR '.$this->dx('status').' ="" OR '.$this->dx('status').' =" " OR '.$this->dx('status').'="0" )',NULL,FALSE);
        $this->db->where($this->dx('user_id').'="'.$user_id.'"',NULL,FALSE);
        return $this->db->get('invoices')->result();
    }

    function get_latest_invoice($user_id=0){
        $this->select_all_secure('invoices');
        $this->db->where($this->dx('active').'="1"',NULL,FALSE);
        $this->db->limit(1);
        $this->db->where($this->dx('user_id').'="'.$user_id.'"',NULL,FALSE);
        $this->db->order_by($this->dx('created_on'),'DESC',FALSE);
        return $this->db->get('invoices')->row();
    }

    function get_invoices_to_pay_generated_today($date = 0 ,$limit = 0){
    	if($date){

   		}else{
   			$date = time();
   		}if($limit && is_numeric($limit)){

   		}else{
   			$limit=20;
   		}
   		$this->select_all_secure('invoice_to_pay');
   		$this->db->where($this->dx('active').'="1"',NULL,FALSE);
   		$this->db->where("DATE_FORMAT(FROM_UNIXTIME(".$this->dx('created_on')."),'%Y %d %m') = '" . date('Y d m',$date) . "'", NULL, FALSE); 	
   		$this->db->limit($limit);
   		return $this->db->get('invoice_to_pay')->result();
    }

    function delete_invoice_to_pay($id = 0){
        $this->db->where('id', $id);
        $this->db->delete('invoice_to_pay');
    }




}