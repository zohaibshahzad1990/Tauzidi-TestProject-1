<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Currencies_m extends MY_Model {

	protected $_table = 'currencies';

	function __construct(){
		parent::__construct();
		$this->load->dbforge();
		$this->install();
	}

	public function install()
	{
		$this->db->query("
		create table if not exists currencies(
			id int not null auto_increment primary key,
			`country_id` varchar(200),
			`name` varchar(200),
			`currency_code` varchar(200),
			`active` varchar(200),
			`created_by` varchar(200),
			`created_on` varchar(200),
			`modified_on` varchar(200),
			`modified_by` varchar(200)
		)");
	}

	public function insert($input = array(),$skip_value = FALSE){
		return $this->insert_secure_data('currencies',$input);
	}	
	function update($id,$input,$val=FALSE){
    	return $this->update_secure_data($id,'currencies',$input);
    }

	public function count_all($params=array()){
		foreach($params as $k => $v){
			if($v){
				$name = trim($this->db->escape_str($v));
            	$this->db->where(' CONVERT(' . $this->dx($k) . " USING 'latin1')  like '%" . $v . "%'", NULL, FALSE);
			}
		}
		return $this->count_all_results('currencies');
	}
	public function get_all($params=array()){	
		$this->select_all_secure('currencies');
		return $this->db->get('currencies')->result();
	}
	function get_currencies_by_country_id($country_id = ''){
		$this->select_all_secure('currencies');
		$this->db->where($this->dx('country_id'),$country_id);
		return $this->db->get('currencies')->row();
	}
	function get_currencies_by_currencies_code($currency_code = ''){
		$this->select_all_secure('currencies');
		$this->db->where($this->dx('currencies_code'),$currency_code);
		return $this->db->get('currencies')->row();
	}
	function get($id = ''){
		$this->select_all_secure('currencies');
		$this->db->where('id',$id);
		return $this->db->get('currencies')->row();
	}
	public function get_currency_options(){
		$arr = array();
		$this->db->select(array('id',$this->dx('name').' as currency',$this->dx('currency_code').' as currency_code'));
		$currencies = $this->db->get('currencies')->result();
		foreach($currencies as $currency){
			$arr[$currency->id] = $currency->currency;
		}
		return $arr;
	}
}