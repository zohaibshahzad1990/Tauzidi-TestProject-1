<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Users_m extends MY_Model {

	protected $_table = 'users';

	protected $params_search = array(
		'id',
		'first_name',
		'last_name',
		'phone',
		'is_validated',
		'email'
	);

	public function __construct(){
		parent::__construct();
		$this->load->dbforge();
		$this->install();
	}
	
	public function install(){

		$this->db->query('
			create table if not exists phone_otp_pairings(
			id int not null auto_increment primary key,
			`phone` varchar(200),
			`otp_code` varchar(200),
			`active` varchar(200),
			`created_on` varchar(200),
			`created_by` varchar(200),
			`modified_on` varchar(200),
			`modified_by` varchar(200)
			)
		');

		$this->db->query('
			create table if not exists user_referral_code_pairings(
			id int not null auto_increment primary key,
			`owner_user_id` int,
			`recipient_user_id` int,
			`refferal_code` varchar(200),
			`active` varchar(200),
			`created_on` varchar(200),
			`created_by` varchar(200),
			`modified_on` varchar(200),
			`modified_by` varchar(200)
			)
		');

		$this->db->query('
			create table if not exists user_payment_info(
			id int not null auto_increment primary key,
			`user_id` int,
			`type` int,
			`identity` varchar(200),
			`active` varchar(200),
			`created_on` varchar(200),
			`created_by` varchar(200),
			`modified_on` varchar(200),
			`modified_by` varchar(200)
			)
		');

		$this->db->query('
			create table if not exists user_school_driver_pairings(
			id int not null auto_increment primary key,
			`user_id` int,
			`school_id` int,
			`active` varchar(200),
			`created_on` varchar(200),
			`created_by` varchar(200),
			`modified_on` varchar(200),
			`modified_by` varchar(200)
			)
		');

	}

    function get($id = 0){
    	$this->db->select(
    		array('users.id as id',
	    		$this->dx('first_name').' as first_name',
	    		$this->dx('middle_name').' as middle_name',
	    		$this->dx('last_name').' as last_name',
	    		$this->dx('email').' as email',
	    		$this->dx('last_login').' as last_login',
	    		$this->dx('users.active').' as active',
	    		$this->dx('users.is_active').' as is_active',	    		
	    		$this->dx('phone').' as phone',
	    		$this->dx('access_token').' as access_token',
	    		$this->dx('id_number').' as id_number',
	    		$this->dx('is_complete_setup').' as is_complete_setup',
	    		$this->dx('is_onboarded').' as is_onboarded',
	    		$this->dx('is_validated').' as is_validated',
	    		$this->dx('password').' as password',	    		
	    		$this->dx('username').' as username',
	    		$this->dx('avatar').' as avatar',
	    		$this->dx('fcm_token').' as fcm_token',
	    		$this->dx('arrears').' as arrears',
	    		$this->dx('billing_package_id').' as billing_package_id',	
	    		$this->dx('is_dismiss_dialogue').' as is_dismiss_dialogue',	    		    			    
	    		$this->dx('access_token').' as access_token',			
	    		$this->dx('created_on').' as created_on',
	    		$this->dx('refferal_code').' as refferal_code',
	    	)
	    );
    	$this->db->where('users.id',$id);
    	$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
    	return $this->db->get('users')->row();
    }

    function get_user_groups_option($id = 0){
		$groups = $this->ion_auth->get_user_groups($id);
		return $groups;
	}

	function get_user_groups_array_option($id = 0){
		$groups = $this->ion_auth->get_user_groups($id);
		$arr = array();
		foreach ($groups as $key => $group) {
			$arr[] = $group;
		}
		return $arr;
	}

    function count_todays_registered_customers($date = ''){
    	$this->db->select(array('users.id','users_groups.id'));
    	$this->db->join('users_groups','users_groups.user_id = users.id','left');
    	$this->db->where('users_groups.group_id = 2');
        $this->db->where("DATE_FORMAT(FROM_UNIXTIME(".$this->dx('users.created_on')."),'%Y %D %M') = '" . date('Y jS F',$date) . "'", NULL, FALSE);
    	return count($this->db->get('users')->result());
    }

    function count_todays_registered_customers_eligible_for_a_loan($date = ''){
    	$this->db->select(array('users.id','users_groups.id'));
    	$this->db->join('users_groups','users_groups.user_id = users.id','left');
    	$this->db->where('users_groups.group_id = 2');
    	$this->db->where($this->dx('loan_limit').' > 0');
        $this->db->where("DATE_FORMAT(FROM_UNIXTIME(".$this->dx('users.created_on')."),'%Y %D %M') = '" . date('Y jS F',$date) . "'", NULL, FALSE);
    	return count($this->db->get('users')->result());
    }

    function count_todays_registered_customers_ineligible_for_a_loan($date = ''){
    	$this->db->select(array('users.id','users_groups.id'));
    	$this->db->join('users_groups','users_groups.user_id = users.id','left');
    	$this->db->where('users_groups.group_id = 2');
    	$this->db->where($this->dx('loan_limit').' = 0');
        $this->db->where("DATE_FORMAT(FROM_UNIXTIME(".$this->dx('users.created_on')."),'%Y %D %M') = '" . date('Y jS F',$date) . "'", NULL, FALSE);
    	return count($this->db->get('users')->result());
    }

	function insert($user=array(),$SKIP_VALIDATION=FALSE){
		return $this->insert_secure_data('users',$user);
	}

	function insert_refferal_code_pairings($referral_code_pairing=array(),$SKIP_VALIDATION=FALSE){
		return $this->insert_secure_data('user_referral_code_pairings',$referral_code_pairing);

	}

	function insert_phone_otp_pairing($phone_otp_pairing=array(),$SKIP_VALIDATION=FALSE){
		return $this->insert_secure_data('phone_otp_pairings',$phone_otp_pairing);
	}

	function insert_payment_info($user=array(),$SKIP_VALIDATION=FALSE){
		return $this->insert_secure_data('user_payment_info',$user);
	}

	function insert_user_driver_pairing($user=array(),$SKIP_VALIDATION=FALSE){
		return $this->insert_secure_data('user_school_driver_pairings',$user);
	}

	function update_user_driver_pairing($id = 0, $input = array(), $skip_validation = false){
		return $this->update_secure_data($id,'user_school_driver_pairings',$input);
	}

	function activate($id = 0){
		return $this->update_secure_data($id,'users',array('is_active' => 1));
	}

	function update_phone_otp_pairing($id = 0, $input = array(), $skip_validation = false){
		return $this->update_secure_data($id,'phone_otp_pairings',$input);
	}

	function get_otp_phone_pairing($phone='',$otp_code=0){
		$this->select_all_secure('phone_otp_pairings');
		if($otp_code){
		$this->db->where($this->dx('phone')." = ".$phone." and ".$this->dx('otp_code')." = ".$otp_code." and ".$this->dx('active')." = 1");
		}else{
			$this->db->where($this->dx('phone')." = ".$phone." and ".$this->dx('active')." = 1");
		}
		return $this->db->get('phone_otp_pairings')->row();
	}

	function update($id = '', $data = array(), $skip_validation = false){
		return $this->update_secure_data($id,'users',$data);
	}

	function update_payment_info($id = '', $data = array(), $skip_validation = false){
		return $this->update_secure_data($id,'user_payment_info',$data);
	}

	function update_where($where = "",$input = array()){
		$this->_table = 'users';
    	return $this->update_secure_where($where,'users',$input);
    }
	

	function get_user_by_id_number($id_number=''){
		$this->select_all_secure('users');
		$this->db->where($this->dx('id_number'),$id_number);
		return $this->db->get('users')->row();
	}

	function get_user_driver_by_id_user_id($user_id=''){
		$this->select_all_secure('users');
		$this->db->where($this->dx('user_id'),$user_id);
		return $this->db->get('user_school_driver_pairings')->row();
	}

	function get_user_by_refferal_code($refferal_code =''){
		$this->select_all_secure('users');
		$this->db->where($this->dx('refferal_code'),$refferal_code);
		return $this->db->get('users')->row();
	}

	function get_user_by_email($email=''){
		$this->select_all_secure('users');
		$this->db->where($this->dx('email'),$email);
		return $this->db->get('users')->row();
	}
	
	function get_user_by_phone_number($phone_number=''){
		$this->select_all_secure('users');
		$this->db->where($this->dx('phone'),$phone_number);
		return $this->db->get('users')->row();
	}

	function get_user_by_phone_options($phones = array()){
		$this->select_all_secure('users');

		if(empty($phones)){
	    	$where = " phone = 0 ;";
    	}else{
	    	$this->db->where(" phone IN (".implode(',',array_filter($phones)).") AND ".$this->dx('active')." = 1");
    	}

		$groups = $this->db->get('users')->result();
		$arr = array();
		foreach ($groups as $key => $value) {
			$arr[$value->phone] = $value;
		}
		return $arr;
	}

	function get_user_by_identification_document_number($identification_document_type = 0,$identification_document_number = ""){
		$this->select_all_secure('users');
		$this->db->where($this->dx('identification_document_type'),$identification_document_type);
		$this->db->where($this->dx('identification_document_number'),$identification_document_number);
		return $this->db->get('users')->row();
	}

	function get_user_group_options($slug = ""){
		$this->select_all_secure('groups');
		if($slug){
			$this->db->where($this->dx('slug').' = "'.$slug.'" ',NULL,FALSE);
		}
			$this->db->where($this->dx('active').' = 1 ',NULL,FALSE);

		$groups = $this->db->get('groups')->result();
		$arr = array();
		foreach ($groups as $key => $value) {
			$arr[$value->id] = $value->name;
		}
		return $arr;
	}

	function get_group_options()
	{
		$this->select_all_secure('groups');
		$query = $this->db->get('groups')->result();

		$arr = array();

		foreach ($query as $value)
		{
			$arr[$value->id] = ucwords($value->name);
		}

		return $arr;
	}

	function get_user_phone_options(){
		$this->db->select(array(
			'users.id as id',
			$this->dx('users.phone').' as phone',
		));
    	$this->db->order_by($this->dx('first_name'),'ASC',FALSE);
    	$users = $this->db->get('users')->result();
		foreach ($users as $key => $user) {
			$arr[$user->id] = $user->phone;
		}
		return $arr;
	}

	function get_user_options(){
		$this->db->select(array(
			'users.id as id',
			$this->dx('users.first_name').' as first_name',
			$this->dx('users.middle_name').' as middle_name',
			$this->dx('users.last_name').' as last_name',
			$this->dx('users.phone').' as phone',
		));
    	$this->db->order_by($this->dx('first_name'),'ASC',FALSE);
    	$users = $this->db->get('users')->result();
		foreach ($users as $key => $user) {
			$arr[$user->id] = $user->first_name.' '.$user->middle_name.' '.$user->last_name.'-('.$user->phone.')';
		}
		return $arr;
	}

	function get_user_email_array_options($user_ids = array()){
		$this->db->select(array(
			'users.id as id',
			$this->dx('users.first_name').' as first_name',
			$this->dx('users.middle_name').' as middle_name',
			$this->dx('users.last_name').' as last_name',
			$this->dx('users.email').' as email'
		));
		if(empty($user_ids)){
	    	$where = " id = 0 ;";
    	}else{
	    	$this->db->where(" id IN (".implode(',',array_filter($user_ids)).") AND ".$this->dx('active')." = 1");
    	}
    	$this->db->order_by($this->dx('first_name'),'ASC',FALSE);
    	$users = $this->db->get('users')->result();
    	$arr = array();
		foreach ($users as $key => $user) {
			$arr[$user->id] = $user;
		}
		return $arr;
	}

	function get_user_array_options($user_ids = array()){
		$this->db->select(array(
			'users.id as id',
			$this->dx('users.first_name').' as first_name',
			$this->dx('users.phone').' as phone',
			$this->dx('users.last_name').' as last_name',
			$this->dx('users.middle_name').' as middle_name',
			$this->dx('users.email').' as email',
			$this->dx('users.last_login').' as last_login'
		));
		if(empty($user_ids)){
			$this->db->where('id'.' IN ( 0 ) ',NULL,FALSE);
		}else{
			$this->db->where(" id IN (".implode(',',array_filter($user_ids)).") AND ".$this->dx('active')." = 1");
		}
    	//$this->db->order_by($this->dx('first_name'),'ASC',FALSE);
    	$arr = array();
    	$users = $this->db->get('users')->result();
		foreach ($users as $key => $user) {
			$arr[$user->id] = $user;
		}
		return $arr;
	}

	function get_posts_user_options($posts = array()){
		if(empty($posts)){
			return array();
		}else{
			
			$user_id_array = array();
			foreach($posts as $post):
				if(in_array($post->user_id,$user_id_array)){

				}else{
					$user_id_array[] = $post->user_id;
				}
			endforeach;
			$this->db->select(
				array(
					'id',
					$this->dx('first_name')." as first_name ",
					$this->dx('middle_name')." as middle_name ",
					$this->dx('last_name')." as last_name ",
				)
			);
			$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
			$this->db->where_in('id',$user_id_array);
			$users = $this->db->get('users')->result();
			$arr = array();
			foreach($users as $user):
				$arr[$user->id] = $user->first_name." ".$user->middle_name." ".$user->last_name;
			endforeach;
			return $arr;
		}
	}

	function get_posts_user_image_url_options($posts = array()){
		if(empty($posts)){
			return array();
		}else{
			$user_id_array = array();
			foreach($posts as $post):
				if(in_array($post->user_id,$user_id_array)){

				}else{
					$user_id_array[] = $post->user_id;
				}
			endforeach;
			$this->db->select(
				array(
					'id',
					$this->dx('image_url')." as image_url ",
				)
			);
			$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
			$this->db->where_in('id',$user_id_array);
			$users = $this->db->get('users')->result();
			$arr = array();
			foreach($users as $user):
				$arr[$user->id] = $user->image_url;
			endforeach;
			return $arr;
		}
	}

	function get_user_email_options(){
		$this->db->select(array(
			'users.id as id',
			$this->dx('users.email').' as email',
		));
    	$this->db->order_by($this->dx('first_name'),'ASC',FALSE);
    	$users = $this->db->get('users')->result();
		foreach ($users as $key => $user) {
			$arr[$user->id] = $user->email;
		}
		return $arr;
	}

	function count_all_active_users(){
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		return $this->count_all_results('users');
	}

	function count_all_active_users_by_group($group_id = ''){
		$this->db->join('users_groups','users_groups.group_id = '.$group_id.'');
		//$this->db->join('users_groups','users_groups.group_id = '.$group_id.'','left');
		//$this->db->where($this->dx('users.active')." = '1' ",NULL,FALSE);
		print_r($this->db->get_compiled_select()); die();
		print_r($this->db->get('users')->result()); die();
		return $this->count_all_results('users');
	}

	function count_all_filetered_active_users($filter_parameters = array()){
    	$this->select_all_secure('users');    	
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
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		return $this->count_all_results('users');
	}

	function get_all_users(){
    	$this->select_all_secure('users');
    	$this->db->order_by($this->dx('first_name'),'ASC',FALSE);
    	return $this->db->get('users')->result();
    }

    function get_latest_five_users(){
    	$this->select_all_secure('users');
    	$this->db->order_by($this->dx('created_on'),'DESC',FALSE);
    	$this->db->where($this->dx('is_validated')." = '1' ",NULL,FALSE);
    	$this->db->limit(5);
    	return $this->db->get('users')->result();
    }

    function get_active_users($filter_parameters = array()){
    	$this->select_all_secure('users');    	
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
		$this->db->order_by($this->dx('created_on'),'DESC',FALSE);
    	$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
    	return $this->db->get('users')->result();
    }

    function get_filter_user_ids_students($filter_parameters = array()){    	
    	$this->db->select(array(
			'users.id as id',
			$this->dx('users.first_name').' as first_name',
			$this->dx('users.middle_name').' as middle_name',
			$this->dx('users.last_name').' as last_name',
			$this->dx('users.email').' as email'
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
    	$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
    	$results = $this->db->get('users')->result();
    	$arr = array();
    	foreach ($results as $key => $result) {
    		$arr[$result->id] = $result->id;
    	}
    	return $arr;
    }

    function get_search_options(){
		$query = trim($this->input->get("q"));
		$words_array = explode(" ",$query);
		$this->db->select(
			array(
				"id as id",
				"CONCAT(".$this->dx('first_name').",' ',".$this->dx('last_name').",' : ',IFNULL(".$this->dx('phone').",''),' - ',".$this->dx('email').") as text ",
				"CONCAT(".$this->dx('first_name').",' ',".$this->dx('last_name').") as full_name ",
				'CONCAT("'.$this->settings->protocol.$this->settings->url.'/uploads/groups/'.'",'.$this->dx('users.avatar').') as avatar_url ',
				'CONCAT("'.$this->settings->protocol.$this->settings->url.'/templates/metronic/img/avatar.png'.'") as default_avatar_url ',
				"IF(".$this->dx('users.phone')." = '', 'N/A',IFNULL(".$this->dx('users.phone').",'N/A')) as phone ",
				"IF(".$this->dx('users.email')." = '', 'N/A',IFNULL(".$this->dx('users.email').",'N/A')) as email ",
				"IF(".$this->dx('users.last_login')." = 0, 'Never Logged In',IFNULL(DATE_FORMAT(FROM_UNIXTIME(".$this->dx('users.last_login')."),'%d-%m-%Y'),'Never Logged In') ) as formatted_last_login_date ",
			)
		);
		if(count($words_array)==2){
			$this->db->where(" ( 
				CONVERT(" . $this->dx('first_name') . " USING 'latin1')  like '%" . $this->escape_str($words_array[0]) . "%' AND 
				CONVERT(" . $this->dx('last_name') . " USING 'latin1')  like '%" . $this->escape_str($words_array[1]) . "%' OR
				CONVERT(" . $this->dx('email') . " USING 'latin1')  like '%" . $this->escape_str($query) . "%' OR
				CONVERT(" . $this->dx('phone') . " USING 'latin1')  like '%" . $this->escape_str($query) . "%' 
				)", NULL, FALSE);
		}else{
			$this->db->where(" ( 
				CONVERT(" . $this->dx('first_name') . " USING 'latin1')  like '%" . $this->escape_str($query) . "%' OR 
				CONVERT(" . $this->dx('last_name') . " USING 'latin1')  like '%" . $this->escape_str($query) . "%' OR
				CONVERT(" . $this->dx('email') . " USING 'latin1')  like '%" . $this->escape_str($query) . "%' OR
				CONVERT(" . $this->dx('phone') . " USING 'latin1')  like '%" . $this->escape_str($query) . "%' 
				)", NULL, FALSE);
		}
		$this->db->order_by($this->dx('first_name'),'ASC',FALSE);
		$users = $this->db->get('users')->result();
		$result = new stdClass();
		$result->total_count = count($users);
		$result->incomplete_results = false;
		$result->items = $users;
		echo json_encode($result);
		die;
	}

	function get_users_in_user_groups($groups = NULL , $filter_parameters = array()){
		$this->select_all_secure('users');
    	if (isset($groups)){
            if (is_numeric($groups)){
                $groups = Array($groups);
            }
            if(isset($groups) && !empty($groups)){
                $this->db->distinct();
                $this->db->join(
                    'users_groups', 'users_groups' . '.user_id = ' . 'users'. '.id', 'inner'
                );
                $this->db->where_in('users_groups' . '.group_id', $groups);
            }
        }
        if(isset($filter_parameters['first_name']) || isset($filter_parameters['phone']) ){
    		$query_array = array();
    		if($filter_parameters){
	    		foreach ($filter_parameters as $key => $value) {
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
        $this->db->where($this->dx('users.active').' ="1"',NULL,FALSE);
        $this->db->order_by($this->dx('first_name'),'ASC',FALSE);
        return $this->db->get('users')->result();
	}

	function get_user_filter_user_ids_by_user_ids($user_ids = array(), $filter_parameters = array()){    	
    	$this->select_all_secure('users'); 	
    	if(isset($filter_parameters['first_name']) || isset($filter_parameters['phone']) ){
    		$query_array = array();
    		if($filter_parameters){
	    		foreach ($filter_parameters as $key => $value) {
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

    	if(empty($user_ids)){
			$this->db->where($this->dx('id')." IN (0) ",NULL,FALSE);
		}else{
			$this->db->where($this->dx('id')." IN (".implode(',',$user_ids).") ",NULL,FALSE);
		}
        $this->db->order_by($this->dx('first_name'),'ASC',FALSE);
        $users = $this->db->get('users')->result();
        $arr = [];
        foreach ($users as $key => $user) {
			$arr[$user->id] = $user;
		}
		return $arr;
    }

	function get_all_users_in_user_groups($groups = NULL){
		$this->select_all_secure('users');
    	if (isset($groups)){
            if (is_numeric($groups)){
                $groups = Array($groups);
            }
            if(isset($groups) && !empty($groups)){
                $this->db->distinct();
                $this->db->join(
                    'users_groups', 'users_groups' . '.user_id = ' . 'users'. '.id', 'inner'
                );
                $this->db->where_in('users_groups' . '.group_id', $groups);
            }
        }
        //$this->db->where($this->dx('users.active').' ="1"',NULL,FALSE);
        $this->db->order_by($this->dx('first_name'),'ASC',FALSE);
        return $this->db->get('users')->result();
	}

	function get_user_by_session_token($session_token = ""){
		$this->db->select(
			array(
				$this->dx('session_token')." as session_token ",
				$this->dx('session_expiry')." as session_expiry ",
			)
		);
        $this->db->where($this->dx('session_token').' = "'.$session_token.'" ',NULL,FALSE);
        $this->db->limit(1);
        return $this->db->get('users')->row();
	}

	function get_users_following_categories($category_ids = array()){
		$this->select_all_secure('category_user_follow_pairings');
		if(empty($category_ids)){
			$this->db->where($this->dx('category_id')." IN (0) ",NULL,FALSE);
		}else{
			$this->db->where($this->dx('category_id')." IN (".implode(',',$category_ids).") ",NULL,FALSE);
		}
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		$category_user_follow_pairings = $this->db->get('category_user_follow_pairings')->result();
		$user_id_list = '0';
		$count = 1;
		foreach($category_user_follow_pairings as $category_user_follow_pairing):
			if($count){
				$user_id_list = $category_user_follow_pairing->user_id;
			}else{
				$user_id_list .= ','.$category_user_follow_pairing->user_id;
			}
			$count++;
		endforeach;

		$this->select_all_secure('users');
		$this->db->where(' id IN ('.$user_id_list.') ',NULL,FALSE);
		return $this->db->get('users')->result();
	}

	function get_users_by_emails($emails = array()){
		$this->db->select(array(
			'id as id',
			$this->dx('email').' as email',
		));
		$user_email_list = '0';
		/*foreach($emails as $email):
			if($email){
				$user_email_list .= ','.$email;
			}
		endforeach;*/
    	if(empty($emails)){
			$this->db->where($this->dx('email')." IN (0) ",NULL,FALSE);
		}else{
			$this->db->where($this->dx('email')." IN (".implode(',',$emails).") ",NULL,FALSE);
		}
		
		/*if($user_email_list){
			$this->db->where($this->dx('email')." IN (".$user_email_list.") ",NULL,FALSE);
		}*/
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		$arr = array();
		$results =  $this->db->get('users')->result();
		print_r($results); die();
		foreach ($results as $key => $result) {
			$arr[$result->email] = $result->id;
		}
		return $arr;
	}

	function get_user_by_identity($identity = ''){
    	$this->select_all_secure('users');
    	if(valid_phone($identity)){
			$this->db->where('('.$this->dx('users.phone').'="'.$identity.'" OR '.$this->dx('users.phone').' = "'.valid_phone($identity).'"  OR '.$this->dx('users.phone').' = "+'.valid_phone($identity).'" OR '.$this->dx('users.phone').' ="+'.$identity.'" OR '.$this->dx('users.phone').' ="'.str_replace('+','', $identity).'" )',NULL,FALSE);
			return $this->db->get('users')->row();
		}else{
			// $this->db->where($this->dx('users.email').'="'.$identity.'"',NULL,FALSE);
			$this->db->where("CONVERT(".$this->dx('users.email')." using 'latin1') = '".$identity."'",NULL,FALSE);
			return $this->db->get('users')->row();
		}
    }

    function get_user_by_email_verification_code($email_verification_code =''){
		$this->db->select(
    		array('users.id as id',
	    		$this->dx('first_name').' as first_name',
	    		$this->dx('middle_name').' as middle_name',
	    		$this->dx('last_name').' as last_name',
	    		$this->dx('email').' as email',
	    		$this->dx('last_login').' as last_login',
	    		$this->dx('users.active').' as active',
	    		$this->dx('users.is_active').' as is_active',	    		
	    		$this->dx('phone').' as phone',
	    		$this->dx('access_token').' as access_token',
	    		$this->dx('id_number').' as id_number',
	    		$this->dx('username').' as username',
	    		$this->dx('email_verification_code').' as email_verification_code',
	    		$this->dx('email_verification_expire_time').' as email_verification_expire_time',
	    	)
	    );
		$this->db->where($this->dx('email_verification_code'),$email_verification_code);
		return $this->db->get('users')->row();
	}

	function get_user_by_otp_code($otp_code = 0){
		$this->db->select(
    		array('users.id as id',
	    		$this->dx('first_name').' as first_name',
	    		$this->dx('middle_name').' as middle_name',
	    		$this->dx('last_name').' as last_name',
	    		$this->dx('email').' as email',
	    		$this->dx('last_login').' as last_login',
	    		$this->dx('avatar').' as avatar',	    		
	    		$this->dx('users.active').' as active',
	    		$this->dx('users.is_active').' as is_active',	    		
	    		$this->dx('phone').' as phone',	    		
	    		$this->dx('confirmation_code').' as confirmation_code',
	    		$this->dx('access_token').' as access_token',
	    		$this->dx('is_onboarded').' as is_onboarded',
	    		$this->dx('is_complete_setup').' as is_complete_setup',
	    		$this->dx('id_number').' as id_number',
	    		$this->dx('username').' as username',
	    		$this->dx('otp_expiry_time').' as otp_expiry_time',	    		
	    		$this->dx('email_verification_code').' as email_verification_code',
	    		$this->dx('email_verification_expire_time').' as email_verification_expire_time',
	    	)
	    );
		$this->db->where($this->dx('otp_code'),$otp_code);
		return $this->db->get('users')->row();
	}

	function get_user_by_confirmation_code($confirmation_code = 0){
		$this->db->select(
    		array('users.id as id',
	    		$this->dx('first_name').' as first_name',
	    		$this->dx('middle_name').' as middle_name',
	    		$this->dx('last_name').' as last_name',
	    		$this->dx('email').' as email',
	    		$this->dx('last_login').' as last_login',
	    		$this->dx('avatar').' as avatar',	    		
	    		$this->dx('users.active').' as active',
	    		$this->dx('users.is_active').' as is_active',	    		
	    		$this->dx('phone').' as phone',	    		
	    		$this->dx('confirmation_code').' as confirmation_code',
	    		$this->dx('access_token').' as access_token',
	    		$this->dx('id_number').' as id_number',
	    		$this->dx('username').' as username',
	    		$this->dx('otp_expiry_time').' as otp_expiry_time',	    		
	    		$this->dx('email_verification_code').' as email_verification_code',
	    		$this->dx('email_verification_expire_time').' as email_verification_expire_time',
	    	)
	    );
		$this->db->where($this->dx('confirmation_code'),$confirmation_code);
		return $this->db->get('users')->row();
	}

	function update_users_bulk($user_ids = array()){
		if(empty($user_ids)){
	    	$where = " id = 0 ;";
    	}else{
	    	$where = " id IN (".implode(',',array_filter($user_ids)).") AND ".$this->dx('active')." = 1 ;";
    	}
		$input = array(
			'active' => 0,
			'modified_on' => time(),
		);
		$this->update_secure_where($where,'users',$input);
		return $this->db->affected_rows();
	}

	function get_system_admins(){
		$this->db->select(
    		array('users.id as id',
	    		$this->dx('first_name').' as first_name',
	    		$this->dx('middle_name').' as middle_name',
	    		$this->dx('last_name').' as last_name',
	    		$this->dx('email').' as email',
	    		$this->dx('last_login').' as last_login',
	    		$this->dx('avatar').' as avatar',	    		
	    		$this->dx('users.active').' as active',
	    		$this->dx('users.is_active').' as is_active',	    		
	    		$this->dx('phone').' as phone',	    		
	    		$this->dx('confirmation_code').' as confirmation_code',
	    		$this->dx('access_token').' as access_token',
	    		$this->dx('id_number').' as id_number',
	    		$this->dx('username').' as username',
	    		$this->dx('otp_expiry_time').' as otp_expiry_time',	    		
	    		$this->dx('email_verification_code').' as email_verification_code',
	    		$this->dx('email_verification_expire_time').' as email_verification_expire_time',
	    	)
	    );
		$this->db->where($this->dx('users.active')." = 1 ",NULL,FALSE);
		$this->db->where($this->dx('users_groups.group_id')." = 1 ",NULL,FALSE);
		$this->db->join('users_groups','users.id = users_groups.user_id ');
		$arr = array();
		$results =  $this->db->get('users')->result();
		foreach ($results as $key => $result) {
			$arr[$result->id] = $result;
		}
		return $arr;
	}

	function get_new_users(){
		$this->db->select(array(
			'users.id as id',
			$this->dx('users.first_name').' as first_name',
			$this->dx('users.middle_name').' as middle_name',
			$this->dx('users.last_name').' as last_name',
			$this->dx('users.phone').' as phone',
			$this->dx('users.email').' as email',
		));
    	$this->db->order_by('id','DESC',FALSE);
    	$this->db->limit(10);
    	$users = $this->db->get('users')->result();
		foreach ($users as $key => $user) {
			$arr[$user->id] = $user;
		}
		return $arr;
	}


    function get_user_by_random_string($random = ''){
		$this->db->select(
    		array('users.id as id',
	    		$this->dx('first_name').' as first_name',
	    		$this->dx('middle_name').' as middle_name',
	    		$this->dx('last_name').' as last_name',
	    		$this->dx('email').' as email',
	    		$this->dx('last_login').' as last_login',
	    		$this->dx('avatar').' as avatar',	    		
	    		$this->dx('users.active').' as active',
	    		$this->dx('users.is_active').' as is_active',	    		
	    		$this->dx('phone').' as phone',	    		
	    		$this->dx('confirmation_code').' as confirmation_code',
	    		$this->dx('access_token').' as access_token',
	    		$this->dx('id_number').' as id_number',
	    		$this->dx('is_validated').' as is_validated',
	    		$this->dx('username').' as username',
	    		$this->dx('curriculum_id').' as curriculum_id',
	    		$this->dx('level_id').' as level_id',
	    		$this->dx('class_id').' as class_id',  
	    		$this->dx('enable_sms').' as enable_sms',
	    		$this->dx('enable_stk_push').' as enable_stk_push',
	    		$this->dx('otp_expiry_time').' as otp_expiry_time',	  
	    		$this->dx('password').' as password', 
	    		$this->dx('refferal_code').' as refferal_code', 	    		 		
	    		$this->dx('email_verification_code').' as email_verification_code',
	    		$this->dx('email_verification_expire_time').' as email_verification_expire_time',
	    	)
	    );
		$this->db->where($this->dx('random'),$random);
		return $this->db->get('users')->row();
	}

	function get_user_payment_details($user_id = 0){
		$this->select_all_secure("user_payment_info");
        $this->db->where($this->dx('user_id').' = "'.$user_id.'" ',NULL,FALSE);
        $this->db->limit(1);
        return $this->db->get('user_payment_info')->row();
	}

	public function did_delete_row($id){
	    $this->db-> where('id', $id);
	    return $this->db-> delete('users');
	}

	function get_users_by_parent_id($user_id = 0){
		$this->select_all_secure("users");
        $this->db->where($this->dx('parent_id').' = "'.$user_id.'" ',NULL,FALSE);
        $this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
        return $this->db->get('users')->result();
	}

	function get_user_driver($id =0){
		$this->db->select(
    		array('users.id as id',
	    		$this->dx('first_name').' as first_name',
	    		$this->dx('middle_name').' as middle_name',
	    		$this->dx('last_name').' as last_name',
	    		$this->dx('email').' as email',
	    		$this->dx('last_login').' as last_login',
	    		$this->dx('avatar').' as avatar',	    		
	    		$this->dx('users.active').' as active',
	    		$this->dx('users.is_active').' as is_active',	    		
	    		$this->dx('phone').' as phone',	    		
	    		$this->dx('confirmation_code').' as confirmation_code',
	    		$this->dx('access_token').' as access_token',
	    		$this->dx('id_number').' as id_number',
	    		$this->dx('username').' as username',
	    		$this->dx('otp_expiry_time').' as otp_expiry_time',	    		
	    		$this->dx('email_verification_code').' as email_verification_code',
	    		$this->dx('email_verification_expire_time').' as email_verification_expire_time',
	    		$this->dx('user_school_driver_pairings.id').' as driver_id',
	    		$this->dx('user_school_driver_pairings.vehicle_id').' as vehicle_id',
	    	)
	    );
	    $this->db->where('users.id',$id);
		$this->db->where($this->dx('users.active')." = 1 ",NULL,FALSE);
		$this->db->join('user_school_driver_pairings','users.id = user_school_driver_pairings.user_id ');
		 return $this->db->get('users')->row();
	}

	function get_user_by_driver_id($id =0){
		$this->db->select(
    		array('users.id as id',
	    		$this->dx('first_name').' as first_name',
	    		$this->dx('middle_name').' as middle_name',
	    		$this->dx('last_name').' as last_name',
	    		$this->dx('email').' as email',
	    		$this->dx('last_login').' as last_login',
	    		$this->dx('avatar').' as avatar',	    		
	    		$this->dx('users.active').' as active',
	    		$this->dx('users.is_active').' as is_active',	    		
	    		$this->dx('phone').' as phone',	
	    		$this->dx('user_school_driver_pairings.id').' as driver_id',
	    		$this->dx('user_school_driver_pairings.vehicle_id').' as vehicle_id',    		
	    	)
	    );
	    $this->db->where('user_school_driver_pairings.id',$id);
		$this->db->where($this->dx('users.active')." = 1 ",NULL,FALSE);
		$this->db->join('users','user_school_driver_pairings.user_id = users.id ');
		//print_r($this->db->get_compiled_select()); die();
		return $this->db->get('user_school_driver_pairings')->row();
	}

	function get_user_by_vehicle_id($vehicle_id =0){
		$this->db->select(
    		array('users.id as id',
	    		$this->dx('first_name').' as first_name',
	    		$this->dx('middle_name').' as middle_name',
	    		$this->dx('last_name').' as last_name',
	    		$this->dx('email').' as email',
	    		$this->dx('last_login').' as last_login',
	    		$this->dx('avatar').' as avatar',	    		
	    		$this->dx('users.active').' as active',
	    		$this->dx('users.is_active').' as is_active',	    		
	    		$this->dx('phone').' as phone',	
	    		$this->dx('user_school_driver_pairings.id').' as driver_id',
	    		$this->dx('user_school_driver_pairings.vehicle_id').' as vehicle_id',  
	    		$this->dx('user_school_driver_pairings.school_id').' as school_id',    		
	    	)
	    );
	    $this->db->where($this->dx('user_school_driver_pairings.vehicle_id').' = "'.$vehicle_id.'" ',NULL,FALSE);
		$this->db->where($this->dx('users.active')." = 1 ",NULL,FALSE);
		$this->db->join('users','user_school_driver_pairings.user_id = users.id ');
		return $this->db->get('user_school_driver_pairings')->row();
	}

	function get_user_vehicle_id_options_by_vehicle_ids($vehicle_ids = array()){
		$this->db->select(
    		array('users.id as id',
	    		$this->dx('first_name').' as first_name',
	    		$this->dx('middle_name').' as middle_name',
	    		$this->dx('last_name').' as last_name',
	    		$this->dx('email').' as email',
	    		$this->dx('last_login').' as last_login',
	    		$this->dx('avatar').' as avatar',	    		
	    		$this->dx('users.active').' as active',
	    		$this->dx('users.is_active').' as is_active',	    		
	    		$this->dx('phone').' as phone',	
	    		$this->dx('user_school_driver_pairings.id').' as driver_id',
	    		$this->dx('user_school_driver_pairings.vehicle_id').' as vehicle_id',  
	    		$this->dx('user_school_driver_pairings.school_id').' as school_id',    		
	    	)
	    );
	    if(empty($vehicle_ids)){
			$this->db->where($this->dx('user_school_driver_pairings.vehicle_id')." IN (0) ",NULL,FALSE);
		}else{
			$this->db->where($this->dx('user_school_driver_pairings.vehicle_id')." IN (".implode(',',$vehicle_ids).") ",NULL,FALSE);
		}
		$this->db->where($this->dx('users.active')." = 1 ",NULL,FALSE);
		$this->db->join('users','user_school_driver_pairings.user_id = users.id ');
		//print_r($this->db->get_compiled_select()); die();
		$results = $this->db->get('user_school_driver_pairings')->result();
		$arr = [];
		//print_r($vehicle_ids);
		//print_r($results); die();
		foreach ($results as $key => $result) {
			$arr[$result->vehicle_id] = $result;
		}
		return $arr;
	}

	function get_user_options_by_driver_ids($driver_ids = array()){
		$this->db->select(
    		array('users.id as id',
	    		$this->dx('first_name').' as first_name',
	    		$this->dx('middle_name').' as middle_name',
	    		$this->dx('last_name').' as last_name',
	    		$this->dx('email').' as email',
	    		$this->dx('last_login').' as last_login',
	    		$this->dx('avatar').' as avatar',	    		
	    		$this->dx('users.active').' as active',
	    		$this->dx('users.is_active').' as is_active',	    		
	    		$this->dx('phone').' as phone',	
	    		$this->dx('user_school_driver_pairings.id').' as driver_id',
	    		$this->dx('user_school_driver_pairings.vehicle_id').' as vehicle_id',    		
	    	)
	    );
	    if(empty($driver_ids)){
			$this->db->where($this->dx('user_school_driver_pairings.id')." IN (0) ",NULL,FALSE);
		}else{
			$this->db->where($this->dx('user_school_driver_pairings.id')." IN (".implode(',',$driver_ids).") ",NULL,FALSE);
		}
		$this->db->where($this->dx('users.active')." = 1 ",NULL,FALSE);
		$this->db->join('users','user_school_driver_pairings.user_id = users.id ');
		//print_r($this->db->get_compiled_select()); die();
		$results = $this->db->get('user_school_driver_pairings')->result();
		$arr = [];
		foreach ($results as $key => $result) {
			$arr[$result->driver_id] = $result;
		}
		return $arr;
	}

	

	function get_user_driver_details($user_id = 0){
		$this->select_all_secure("user_school_driver_pairings");
        $this->db->where($this->dx('user_id').' = "'.$user_id.'" ',NULL,FALSE);
        $this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
        return $this->db->get('user_school_driver_pairings')->row();
	}

	function get_user_by_school_vehicle($school_id = 0, $vehicle_id =0){
		$this->select_all_secure("user_school_driver_pairings");
        $this->db->where($this->dx('vehicle_id').' = "'.$vehicle_id.'" ',NULL,FALSE);
        $this->db->where($this->dx('school_id').' = "'.$school_id.'" ',NULL,FALSE);
        $this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
        return $this->db->get('user_school_driver_pairings')->row();
	}

	

}
