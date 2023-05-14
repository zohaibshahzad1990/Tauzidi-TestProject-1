<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Billing_m extends MY_Model {

	protected $_table = 'settings';

	function __construct(){
		parent::__construct();
		$this->load->dbforge();
		$this->install();
	}

	public function install(){
		$this->db->query("
            create table if not exists billing_packages(
                id int not null auto_increment primary key,
                `name` varchar(200),
                `slug` varchar(200),
                `billing_type_frequency` int,
                `rate` int,
                `rate_on` int,
                `amount` varchar(200),
                `currency` varchar(200),
                `trial_days` varchar(200),
                `enable_tax` int,
                `percentage_tax` int,
                `active` int,
                `is_default` int,
                `created_by` int,
                `created_on` varchar(200),
                `modified_on` varchar(200),
                `modified_by` int
        )");

        $this->db->query("
            create table if not exists billing_payments(
                id int not null auto_increment primary key,
                `user_id`  varchar(200),
                `transaction_code`  varchar(200),
                `receipt_date`  varchar(200),
                `amount`  varchar(200),
                `tax`  varchar(200),
                `payment_method`  varchar(200),
                `billing_invoice_id`  varchar(200),
                `description`  varchar(200),
                `active`  varchar(200),
                `created_by`  varchar(200),
                `created_on`  varchar(200),
                `modified_on`  varchar(200),
                `modified_by`  varchar(200)
        )");
	}

	public function insert($input = array(),$skip_value = FALSE){
		return $this->insert_secure_data('billing_packages',$input);
	}

	public function insert_billing_payments($input = array(),$skip_value = FALSE){
		return $this->insert_secure_data('billing_payments',$input);
	}

	function update($id,$input,$val=FALSE){
    	return $this->update_secure_data($id,'billing_packages',$input);
    }

    function update_billing_payments($id,$input,$val=FALSE){
    	return $this->update_secure_data($id,'billing_payments',$input);
    }
    

	public function count_all(){
		return $this->count_all_results('billing_packages');
	}
	
	public function get_all(){	
		$this->select_all_secure('billing_packages');
		$this->where($this->dx('active').'="1"',NULL,FALSE);
		return $this->db->get('billing_packages')->result();
	}

	

	function check_if_default($slug=0,$id=0){
        $this->select_all_secure('billing_packages');
        $this->where($this->dx('active').'="1"',NULL,FALSE);
        if($id){
           $this->where('id !=',$id); 
        }
        return $this->db->get('billing_packages')->row();
    }

    function default_packages(){
        $this->select_all_secure('billing_packages');
        $this->where($this->dx('active').'="1"',NULL,FALSE);
        $this->where($this->dx('is_default').'="1"',NULL,FALSE);
        $results =  $this->db->get('billing_packages')->result();
        $arr = array();
        foreach ($results as $key => $result) {
        	$arr[$result->id] = $result->id;
        }
        return $arr;
    }

    function get_default_packages(){
        $this->select_all_secure('billing_packages');
        $this->where($this->dx('active').'="1"',NULL,FALSE);
        $this->where($this->dx('is_default').'="1"',NULL,FALSE);
        $this->db->limit(1);
        $results =  $this->db->get('billing_packages')->row();
        return $results;
    }

    function void_if_default_exist(){
    	$ids = $this->default_packages();
    	if($ids){
	    	if(empty($ids)){
		    	$where = " id = 0 ;";
	    	}else{
		    	$where = " id IN (".implode(',',array_filter($ids)).") AND ".$this->dx('active')." = 1 AND ".$this->dx('is_default')." = 1;";
	    	}
			$input = array(
				'is_default' => 0,
				'modified_on' => time(),
			);
			$this->update_secure_where($where,'billing_packages',$input);
			return $this->db->affected_rows();
		}
    }

	function count_default($slug=0,$id=0){
        $this->where($this->dx('slug').'="'.$slug.'"',NULL,FALSE);
        $this->where($this->dx('active').'="1"',NULL,FALSE);
        if($id){
           $this->where('id !=',$id); 
        }
        return $this->db->count_all_results('billing_packages')?:0;
    }

	public function get($id = 0){	
		$this->select_all_secure('billing_packages');
		$this->db->where('id',$id);
		return $this->db->get('billing_packages')->row();
	}

	function get_package_by_slug($slug = ''){
		$this->db->select(
    		array('id as id',
	    		$this->dx('slug').' as slug',
	    		$this->dx('name').' as name',
	    	)
	    );
	    $this->db->where($this->dx('active')."= '1'",NULL,FALSE);	
		$this->db->where($this->dx('slug')." = ".$this->db->escape($slug),NULL,FALSE);
		$this->db->limit(1);
		return $this->db->get('billing_packages')->row();
	}

	function get_user_billing_paid_amount($user_id=0){
        $this->db->select('sum('.$this->dx('amount').') as amount_paid');
        $this->db->where($this->dx('active').'="1"',NULL,FALSE);
        $this->db->where($this->dx('user_id').'="'.$user_id.'"',NULL,FALSE);
        $amount_paid = $this->db->get('billing_payments')->row();
        //print_r($amount_paid); die();
        if($amount_paid){
            return $amount_paid->amount_paid?$amount_paid->amount_paid:0;
        }else{
            return 0;
        }
    }

    function calculate_billing_receipt_number($user_id=0)
    {
        $this->db->where($this->dx('billing_payments.user_id').'="'.$user_id.'"',NULL,FALSE);
        $this->db->from('billing_payments');
        $count = $this->db->count_all_results();
        $count = $count+1;
        if($count>=100){
        }else if($count>=10){
            $count = '0'.$count;
        }else{
            $count='00'.$count;
        }
        return $count;
    }

}