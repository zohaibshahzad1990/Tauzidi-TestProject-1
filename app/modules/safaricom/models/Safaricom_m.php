<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Safaricom_m extends MY_Model {

	protected $_table = 'safaricomb2crequest';

	protected $special_url_segments = array("/edit/","/view/",'/statement/',"/listing\/page/");

	protected $starting_response_request_id = '100000001';

	protected $params_safaricomstkpushes_search = array(
		'phone',
		'transaction_id',
		'response_description',
		'amount',
	);

	public function __construct()
	{
		parent::__construct();

		$this->load->dbforge();
		$this->install();
	}


	public function install()
	{
		

		$this->db->query("
		create table if not exists safaricomc2bpayments(
			id int not null auto_increment primary key,
			`transaction_id` varchar(200),
			`reference_number` varchar(200),
			`transaction_date` varchar(200),
			`amount` varchar(200),
			`active` varchar(200),
			`currency` varchar(200),
			`transaction_type` varchar(200),
			`transaction_particulars` varchar(200),
			`phone` varchar(200),
			`account` varchar(200),
			`customer_name` varchar(200),
			`status` varchar(200),
			`shortcode` varchar(200),
			`organization_balance` varchar(200),
			created_on varchar(200),
			modified_on varchar(200),
			modified_by varchar(200)
		)");
	

		$this->db->query("
		create table if not exists safaricomstkpushrequests(
			id int not null auto_increment primary key,
			`shortcode` varchar(200),
			`phone` varchar(200),
			`request_id` varchar(200),
			`group_id` varchar(200),
			`user_id` varchar(200),
			`amount` varchar(200),
			`request_callback_url` varchar(200),
			`response_code` varchar(200),
			`response_description` varchar(200),
			`checkout_request_id` varchar(200),
			`customer_message` varchar(200),
			`result_code` varchar(200),
			`result_description` varchar(200),
			created_on varchar(200),
			modified_on varchar(200),
			modified_by varchar(200)
		)");

		$this->db->query("
		create table if not exists safaricom_configurations(
			id int not null auto_increment primary key,
			`username` varchar(200),
			`password` varchar(200),
			`api_key` varchar(200),
			`access_token` varchar(200),
			`access_token_expires_at` varchar(200),
			`access_token_type` varchar(200),
			`is_default` varchar(200),
			`active` varchar(200),
			created_by varchar(200),
			created_on varchar(200),
			modified_on varchar(200),
			modified_by varchar(200)
		)");
	}


	/**************************C2B Requests******************/
	function insert_c2b($input = array(),$skip_value = FALSE)
	{
		return $this->insert_secure_data('safaricomc2bpayments',$input);
	}

	function update_c2b_payment($id,$input,$val=FALSE){
    	return $this->update_secure_data($id,'safaricomc2bpayments',$input);
    }

    function get_c2b_request($id=0){
    	$this->select_all_secure('safaricomc2bpayments');
		$this->db->where('id',$id);
		return $this->db->get('safaricomc2bpayments')->row();
	}

    function update_c2b_by_transaction_id($transaction_id=0,$organization_balance=0,$transaction_type=''){
    	if($transaction_id && $organization_balance){
    		$this->db->select(array(
    			'id',
    			$this->dxa('transaction_type'),
    		));
    		$this->db->where($this->dx('transaction_id').'="'.$transaction_id.'"',NULL,FALSE);
    		$this->db->where($this->dx('active').'="1"',NULL,FALSE);
    		$result = $this->db->get('safaricomc2bpayments')->row();
    		if($result){
    			 if($this->update_c2b_payment($result->id,array(
    					'organization_balance' => $organization_balance,
    					'transaction_type' => $transaction_type?:$result->transaction_type,
    					'status'	=>	1,
    					'modified_on' => time(),
    					'modified_by' => 1,
    				))){
    			 	return $result->id;
    			 }else{
    			 	return FALSE;
    			 }
    		}else{
    			return FALSE;
    		}
    	}else{
    		return FALSE;
    	}
    }

    function is_transaction_dublicate($transaction_id=0){
		$this->db->where($this->dx('transaction_id').'="'.$transaction_id.'"',NULL,FALSE);
		$this->db->where($this->dx('active').'="1"',NULL,FALSE);
		return $this->db->count_all_results('safaricomc2bpayments')?:0;
	}

	function is_account_number_recognized($account=0)
	{
		if($account ==102224){
			return TRUE;
		}else{
			return FALSE;
		}
		
	}


	function count_all_c2b_requests(){
		return $this->db->count_all_results('safaricomc2bpayments')?:0;
	}

	function get_all_c2b_requests(){
		$this->select_all_secure('safaricomc2bpayments');
		$this->db->order_by($this->dx('transaction_date'),'DESC',FALSE);
		return $this->db->get('safaricomc2bpayments')->result();
	}

	function delete_c2b($id=0){
		$this->db->where('id',$id);
		return $this->db->delete('safaricomc2bpayments');
	}

	function get_unsent_c2b_notifications(){
		$this->select_all_secure('safaricomc2bpayments');
		$this->db->where('('.$this->dx('transaction_send_status').' IS NULL OR '.$this->dx('transaction_send_status').' ="0" OR '.$this->dx('transaction_send_status').' ="" OR '.$this->dx('transaction_send_status').' =" " )',NULL,FALSE);
		$this->db->order_by($this->dx('created_on'),'DESC',FALSE);
		return $this->db->get('safaricomc2bpayments')->result();
	}

	function get_c2b_payment_by_transaaction_id($transaction_id = 0){
		$this->select_all_secure('safaricomc2bpayments');
		$this->db->where($this->dx('transaction_id').'="'.$transaction_id.'"',NULL,FALSE);
		$this->db->where($this->dx('active').'="1"',NULL,FALSE);
		return $this->db->get('safaricomc2bpayments')->row();
	}


	function get_c2b_payment_by_account($account=0){
		$this->select_all_secure('safaricomc2bpayments');
		$this->db->where($this->dx('account').'="'.$account.'"',NULL,FALSE);
		$this->db->where($this->dx('active').'="1"',NULL,FALSE);
		return $this->db->get('safaricomc2bpayments')->row();
	}

	
	/*******************STK Push Requests**********************/

	function generate_stkpush_request_id(){
		$this->db->select(array(
            $this->dx('request_id').' as request_id',
        ));
        $this->db->order_by($this->dx('created_on'),'DESC',FALSE);
        $this->db->limit(1);
        $res = $this->db->get('safaricomstkpushrequests')->row();
        if($res){
            return substr(chunk_split((str_replace('-','',$res->request_id)+1), 5, '-'), 0, -1);
        }else{
            return $this->starting_response_request_id;
        }
	}

	function insert_stk_push_request($input = array(),$skip_value = FALSE){
		return $this->insert_secure_data('safaricomstkpushrequests',$input);
	}

	function get_stk_request_by_request_id($request_id = 0,$get_result_code = TRUE){
		$this->select_all_secure('safaricomstkpushrequests');
		$this->db->where($this->dx('request_id').' = "'.$request_id.'"',NULL,FALSE);
		if($get_result_code){
			$this->db->where($this->dx('result_code').' iS NULL',NULL,FALSE);	
		}
		return $this->db->get('safaricomstkpushrequests')->row();
	}

	function get_stk_request_by_merchant_request_id_and_checkout_request_id($checkout_request_id=0,$merchant_request_id=0){
		$this->select_all_secure('safaricomstkpushrequests');
		$this->db->where($this->dx('checkout_request_id').' = "'.$checkout_request_id.'"',NULL,FALSE);
		$this->db->where($this->dx('merchant_request_id').' = "'.$merchant_request_id.'"',NULL,FALSE);
		return $this->db->get('safaricomstkpushrequests')->row();
	}

	function get_stk_request_by_checkout_request_id($checkout_request_id=0){
		$this->select_all_secure('safaricomstkpushrequests');
		$this->db->where($this->dx('checkout_request_id').' = "'.$checkout_request_id.'"',NULL,FALSE);
		return $this->db->get('safaricomstkpushrequests')->row();
	}

	function get_stk_request($id = 0){
		$this->select_all_secure('safaricomstkpushrequests');
		$this->db->where('id',$id);
		return $this->db->get('safaricomstkpushrequests')->row();
	}

	function update_stkpushrequest($id,$input,$val=FALSE){
    	return $this->update_secure_data($id,'safaricomstkpushrequests',$input);
    }

    function count_all_stk_push_requests($filter_parameters = array()){
    	if(isset($filter_parameters['search_fields'])){
    		$query_array = array();
    		if($filter_parameters['search_fields']){
	    		foreach ($filter_parameters['search_fields'] as $key => $value) {
	                if(in_array($key, $this->params_safaricomstkpushes_search)){
	                	if($key &&$value){
	                		$query_array[] ="CONVERT(" . $this->dx($key) . " USING 'latin1')  like '%" . $this->escape_str($value) . "%'";
	                	}
		    		}
	            }
	        }
            if(count($query_array) > 0){
            	$this->db->where(implode( ' OR ', $query_array ),NULL,FALSE);
            }
    		if(in_array($filter_parameters['sort_field'], $this->params_safaricomstkpushes_search)){
    			$this->db->order_by($this->dx($filter_parameters['sort_field']),''.$filter_parameters['sort_order'].'',FALSE);
    		}
		}
    	if(isset($filter_parameters['filter_request_status'])){
			if($filter_parameters['filter_request_status']){
				$this->db->where($this->dx('result_code'),''.$filter_parameters['filter_request_status'].'',NULL,FALSE);
			}
		}
		if(isset($filter_parameters['to'])){
			if($filter_parameters['to']){
				$this->db->where($this->dx('created_on').' <= "'.$filter_parameters['to'].'"',NULL,FALSE);
			}
		}
		if(isset($filter_parameters['from'])){

			if($filter_parameters['from']){
				//die('am in');
				$this->db->where($this->dx('created_on').' >= "'.$filter_parameters['from'].'"',NULL,FALSE);
			}
		}
    	return $this->db->count_all_results('safaricomstkpushrequests')?:0;
    }

    function get_all_stk_push_requests($filter_parameters = array()){
    	$this->select_all_secure('safaricomstkpushrequests');
    	if(isset($filter_parameters['search_fields'])){
    		$query_array = array();
    		if($filter_parameters['search_fields']){
	    		foreach ($filter_parameters['search_fields'] as $key => $value) {
	                if(in_array($key, $this->params_safaricomstkpushes_search)){
	                	if($key &&$value){
	                		$query_array[] ="CONVERT(" . $this->dx($key) . " USING 'latin1')  like '%" . $this->escape_str($value) . "%'";
	                	}
		    		}
	            }
	        }
            if(count($query_array) > 0){
            	$this->db->where(implode( ' OR ', $query_array ),NULL,FALSE);
            }
    		if(in_array($filter_parameters['sort_field'], $this->params_safaricomstkpushes_search)){
    			$this->db->order_by($this->dx($filter_parameters['sort_field']),''.$filter_parameters['sort_order'].'',FALSE);
    		}
		}
    	if(isset($filter_parameters['filter_request_status'])){
    		if($filter_parameters['filter_request_status']){
				$this->db->where($this->dx('result_code'),''.$filter_parameters['filter_request_status'].'',NULL,FALSE);
			}
		}
		if(isset($filter_parameters['to'])){
			$this->db->where($this->dx('created_on').' <= "'.$filter_parameters['to'].'"',NULL,FALSE);
		}
		if(isset($filter_parameters['from'])){
			$this->db->where($this->dx('created_on').' >= "'.$filter_parameters['from'].'"',NULL,FALSE);
		}
    	$this->db->order_by($this->dx('created_on'),'DESC',FALSE);
    	//print_r($this->db->get_compiled_select()); die();
    	return $this->db->get('safaricomstkpushrequests')->result();
    }

    function get_all_stk_push_requests_pending_results(){
    	$this->select_all_secure('safaricomstkpushrequests');
    	$this->db->where($this->dx('response_code').' ="0"',NULL,FALSE);
    	$this->db->where('('.$this->dx('result_code').' =""  OR '.$this->dx('result_code').' =" " OR '.$this->dx('result_code').' IS NULL )',NULL,FALSE);
    	$this->db->where("DATE_FORMAT(FROM_UNIXTIME(".$this->dx('created_on')."),'%Y %D %M') = '" . date('Y jS F',time()) . "'", NULL, FALSE);
    	$this->db->where($this->dx('account_id').' > "0"',NULL,FALSE);
    	$this->db->order_by($this->dx('created_on'),'DESC',FALSE);
    	return $this->db->get('safaricomstkpushrequests')->result();
    }

    function get_request_where_reference_number($reference_number=0){
    	$this->select_all_secure('safaricomstkpushrequests');
		$this->db->where($this->dx('reference_number').' = "'.$reference_number.'"',NULL,FALSE);
		return $this->db->get('safaricomstkpushrequests')->row();
    }

    function get_stk_payment_by_transaaction_id($transaction_id=0){
    	$this->select_all_secure('safaricomstkpushrequests');
		$this->db->where($this->dx('transaction_id').' = "'.$transaction_id.'"',NULL,FALSE);
		return $this->db->get('safaricomstkpushrequests')->row();
    }

    function get_uncomplete_payments(){
    	echo date('Y F',time());
    	$this->select_all_secure('safaricomstkpushrequests');
    	$this->db->select(array(
    		"DATE_FORMAT(FROM_UNIXTIME(".$this->dx('created_on')."),'%Y %M') as created_on2"
    	));
    	$this->db->where($this->dx('response_code'). ' ="0"',NULL,FALSE);
    	$this->db->where('('.$this->dx('result_code'). 'IS NULL OR '.$this->dx('result_code').' = "" OR '.$this->dx('result_code').' =" " )',NULL,FALSE);
    	$this->db->where($this->dx('reference_number').' > "0"',NULL,FALSE);
    	$this->db->where($this->dx('account_id').' > "0"',NULL,FALSE);
		$this->db->order_by($this->dx('created_on'),'DESC',FALSE);
		$this->db->where("DATE_FORMAT(FROM_UNIXTIME(".$this->dx('created_on')."),'%Y %M') = '" . date('Y F',time()) . "'", NULL, FALSE);
		$this->db->limit(5);
		return $this->db->get('safaricomstkpushrequests')->result();
    }   

    
}?>