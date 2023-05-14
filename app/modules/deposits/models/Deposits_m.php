<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Deposits_m extends MY_Model {

	protected $_table = 'deposits';

	function __construct(){
		parent::__construct();
		$this->load->dbforge();
		$this->install();
	}

    protected $params_search = array(
        'phone',
        'transaction_id',
        'amount',
    );

	public function install()
	{
		$this->db->query("
		create table if not exists deposits(
			id int not null auto_increment primary key,
			`deposit_date` varchar(200),
			`account_id` int,
			`deposit_method` int,
			`description` varchar(200),
			`amount` varchar(200),
			`transaction_id` varchar(200),
			`stk_payment_id` varchar(200),
			`c2b_payment_id` varchar(200),
			`active` varchar(200),
			created_by int,
			created_on varchar(200),
			modified_on varchar(200),
			modified_by int
		)");

		$this->db->query("
			create table if not exists online_payment_requests(
				id int not null auto_increment primary key,
				`user_id` int,				
                `description` text,
                `amount` varchar(200),
                `payment_for` varchar(200),
                `reference_number` varchar(200),
                `phone` varchar(200),
                `status` int,
                `active` int,
                `response_code` varchar(200),
                `transaction_date` varchar(200),
                `response_description` varchar(200),
                `result_code` varchar(200),
                `result_description` varchar(200),
                `created_on` varchar(200),
                `created_by` int,
				`modified_by` int,
				`modified_on` varchar(200)
			)"
		);
	}

	public function insert($input = array(),$skip_value = FALSE){
		return $this->insert_secure_data('deposits',$input);
	}

	function insert_online_payment_request($input=array(),$SKIP_VALIDATION = FALSE){
		return $this->insert_secure_data('online_payment_requests',$input);
	}

	function update($id,$input,$val=FALSE){
    	return $this->update_secure_data($id,'deposits',$input);
    }

    function is_unique_deposit($transaction_id = 0){
    	$this->db->where($this->dx('transaction_id').' = "'.$transaction_id.'"',NULL,FALSE);
    	$this->db->where($this->dx('active').' = "1"',NULL,FALSE);
    	return $this->db->count_all_results('deposits')?0:1;
    }

    function get_all(){
    	$this->select_all_secure('deposits');
    	return $this->db->get('deposits')->result();
    }

    function get($id=0){
    	$this->select_all_secure('deposits');
    	$this->db->where('id',$id);
    	return $this->db->get('deposits')->row();
    }

    function count_active_deposits($filter_parameters = array()){ 
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
        if(isset($filter_parameters['to'])){
            $this->db->where($this->dx('created_on').' <= "'.$filter_parameters['to'].'"',NULL,FALSE);
        }
        if(isset($filter_parameters['from'])){
            $this->db->where($this->dx('created_on').' >= "'.$filter_parameters['from'].'"',NULL,FALSE);
        }       
        $this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
        return $this->count_all_results('deposits');
    }

    function count_my_active_deposits($user_id = 0){    	
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		$this->db->where($this->dx('user_id').'="'.$user_id.'"',NULL,FALSE);
		return $this->count_all_results('deposits');
	}

	function get_my_active_deposits($user_id = 0){
    	$this->select_all_secure('deposits');
    	$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		$this->db->where($this->dx('user_id').'="'.$user_id.'"',NULL,FALSE);
    	return $this->db->get('deposits')->result();
    }

    function get_active_deposits($filter_parameters = array()){
        $this->select_all_secure('deposits');
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
        if(isset($filter_parameters['to'])){
            $this->db->where($this->dx('created_on').' <= "'.$filter_parameters['to'].'"',NULL,FALSE);
        }
        if(isset($filter_parameters['from'])){
            $this->db->where($this->dx('created_on').' >= "'.$filter_parameters['from'].'"',NULL,FALSE);
        } 
        $this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
        $this->db->order_by('created_on','DESC');
        return $this->db->get('deposits')->result();
    }

    function get_user_total_deposits_amount($user_id=0){
        $this->db->select('sum('.$this->dx('amount').') as amount_paid');
        $this->db->where($this->dx('active').'="1"',NULL,FALSE);
        $this->db->where($this->dx('user_id').'="'.$user_id.'"',NULL,FALSE);
        $amount_paid = $this->db->get('deposits')->row();

        if($amount_paid){
            return $amount_paid->amount_paid;
        }else{
            return 0;
        }
    }

    function get_my_active_deposits_per_invoice_id($user_id = 0,$invoice_id = 0){
    	$this->select_all_secure('deposits');
    	$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
    	$this->db->where($this->dx('invoice_id').'="'.$invoice_id.'"',NULL,FALSE);
		$this->db->where($this->dx('user_id').'="'.$user_id.'"',NULL,FALSE);
    	return $this->db->get('deposits')->result();
    }

    function get_my_active_total_deposits_per_invoice_id($user_id = 0,$invoice_id = 0){
        $this->db->select('sum('.$this->dx('amount').') as amount_paid');
        $this->db->where($this->dx('active').'="1"',NULL,FALSE);
        $this->db->where($this->dx('invoice_id').'="'.$invoice_id.'"',NULL,FALSE);
		$this->db->where($this->dx('user_id').'="'.$user_id.'"',NULL,FALSE);
        $amount_paid = $this->db->get('deposits')->row();

        if($amount_paid){
            return $amount_paid->amount_paid;
        }else{
            return 0;
        }
    }

    function get_new_deposits(){
		$this->db->select(array(
			'id as id',
			$this->dx('deposit_date').' as deposit_date',
			$this->dx('deposit_method').' as deposit_method',
			$this->dx('description').' as description',
			$this->dx('amount').' as amount',
			$this->dx('transaction_id').' as transaction_id',
			$this->dx('stk_payment_id').' as stk_payment_id',
			$this->dx('c2b_payment_id').' as c2b_payment_id',
			$this->dx('active').' as active',
		));
    	$this->db->order_by('id','DESC',FALSE);
    	$this->db->limit(10);
    	$deposits = $this->db->get('deposits')->result();
		foreach ($deposits as $key => $deposit) {
			$arr[$deposit->id] = $deposit;
		}
		return $arr;
	}

    function get_total_deposits_per_user(){
        $this->db->select(array(
            'id as id',
            $this->dx('deposit_date').' as deposit_date',
            $this->dx('deposit_method').' as deposit_method',
            $this->dx('description').' as description',
            $this->dx('amount').' as amount',
            $this->dx('transaction_id').' as transaction_id',
            $this->dx('stk_payment_id').' as stk_payment_id',
            $this->dx('c2b_payment_id').' as c2b_payment_id',
            $this->dx('user_id').' as user_id',
            $this->dx('active').' as active',
        ));
        $this->db->order_by('id','DESC',FALSE);
        $deposits = $this->db->get('deposits')->result();
        $arr = array();
        foreach ($deposits as $key => $deposit) {
            $arr[$deposit->user_id] = 0;
        }
        foreach ($deposits as $key => $deposit) {
            $arr[$deposit->user_id] += $deposit->amount;
        }
        return $arr;
    }
}
?>