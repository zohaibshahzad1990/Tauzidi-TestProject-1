<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Guardians_m extends MY_Model {

	protected $_table = 'guardians';

	protected $params_search = array(
		'id',
		'name'
	);

	public function __construct(){
		parent::__construct();
		$this->load->dbforge();
		$this->install();
	}

	function install(){
		$this->db->query('
			create table if not exists guardians(
			id int not null auto_increment primary key,
			`user_id` int,
			`parent_id` int,
			`active` varchar(200),
			`created_on` varchar(200),
			`created_by` varchar(200),
			`modified_on` varchar(200),
			`modified_by` varchar(200)
			)
		');
	}

	function insert($input = array(),$SKIP_VALIDATION=FALSE){
        return $this->insert_secure_data('guardians', $input);
	}

	function get_all(){
		$this->select_all_secure('guardians');
		return $this->db->get('guardians')->result();
	}

	function get($id=0){
		$this->select_all_secure('guardians');
		$this->db->where('id',$id);
		return $this->db->get('guardians')->row();
	}

	function insert_batch($input = array(),$SKIP_VALIDATION=FALSE){
        return $this->insert_chunked_batch_secure_data('guardians',$input);
	}
	
	function update($id,$input,$SKIP_VALIDATION=FALSE){
    	return $this->update_secure_data($id,'guardians',$input);
    }

    function count_all_active_guardians(){
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		return $this->count_all_results('guardians');
	}

	function get_guardian_by_id($parent_id =0){
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
	    		$this->dx('guardians.id').' as guardian_id',  
	    		$this->dx('guardians.parent_id').' as parent_id',  	
	    		$this->dx('guardians.user_parent_id').' as user_parent_id', 	
	    	)
	    );
	    $this->db->where($this->dx('guardians.id').' = "'.$parent_id.'"',NULL,FALSE);
		$this->db->where($this->dx('users.active')." = 1 ",NULL,FALSE);
		$this->db->join('users','guardians.user_id = users.id ');
		//print_r($this->db->get_compiled_select()); die();
		return $this->db->get('guardians')->row();
	}

	function get_guardian_by_parent_id($parent_id =0){
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
	    		$this->dx('guardians.id').' as guardian_id',  
	    		$this->dx('guardians.parent_id').' as parent_id',  		
	    	)
	    );
	    $this->db->where($this->dx('guardians.user_parent_id').' = "'.$parent_id.'"',NULL,FALSE);
		$this->db->where($this->dx('users.active')." = 1 ",NULL,FALSE);
		$this->db->join('users','guardians.user_id = users.id ');
		//print_r($this->db->get_compiled_select()); die();
		return $this->db->get('guardians')->row();
	}

	function get_user_by_parent_id($id =0){
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
	    		$this->dx('guardians.id').' as parent_id',
	    		$this->dx('guardians.vehicle_id').' as school_id',    		
	    	)
	    );
	    $this->db->where('guardians.id',$id);
		$this->db->where($this->dx('users.active')." = 1 ",NULL,FALSE);
		$this->db->join('users','guardians.user_id = users.id ');
		//print_r($this->db->get_compiled_select()); die();
		return $this->db->get('guardians')->row();
	}

	function get_user_by_guardian_user_id($id =0){
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
	    		$this->dx('guardians.parent_id').' as parent_id',   		
	    	)
	    );
	    $this->db->where($this->dx('guardians.user_id').' = "'.$id.'"',NULL,FALSE);
		$this->db->where($this->dx('users.active')." = 1 ",NULL,FALSE);
		$this->db->join('users','guardians.user_id = users.id ');
		return $this->db->get('guardians')->row();
	}

	function get_parent_options_by_user_ids($ids = array()){
		$this->db->select(
    		array('users.id as id',
	    		$this->dx('users.first_name').' as first_name',
	    		$this->dx('users.last_name').' as last_name',  
	    		$this->dx('users.fcm_token').' as fcm_token', 		
	    	)
	    );
	    if(empty($ids)){
			$this->db->where('guardians.user_id'.' = 0 ',NULL,FALSE);
    	}else{
			$this->db->where('guardians.user_id'.' IN ('.implode(',',$ids).') ',NULL,FALSE);
    	}
		$this->db->where($this->dx('users.active')." = 1 ",NULL,FALSE);
		$this->db->join('users','guardians.user_id = users.id ');
		$results =  $this->db->get('guardians')->result();
		$arr = [];
		foreach ($results as $key => $result) {
			$arr[$result->id] = $result->first_name .' '. $result->last_name;
		}
		return $arr;
	}

	function get_guardian_parent_full_options_by_user_ids($ids = array()){
		$this->db->select(
    		array('users.id as id',
	    		$this->dx('users.first_name').' as first_name',
	    		$this->dx('users.last_name').' as last_name',  
	    		$this->dx('users.fcm_token').' as fcm_token', 
	    		$this->dx('users.phone').' as phone', 
	    		$this->dx('guardians.user_id').' as user_id', 			
	    	)
	    );
	    if(empty($ids)){
			$this->db->where('guardians.user_id'.' = 0 ',NULL,FALSE);
    	}else{
			$this->db->where('guardians.user_id'.' IN ('.implode(',',$ids).') ',NULL,FALSE);
    	}
		$this->db->where($this->dx('users.active')." = 1 ",NULL,FALSE);
		$this->db->join('users','guardians.user_parent_id = users.id ');
		$results =  $this->db->get('guardians')->result();
		$arr = [];
		foreach ($results as $key => $result) {
			$arr[$result->user_id] = $result;
		}
		return $arr;
	}

	function get_user_parent_by_guardian_parent_pairings($id =0){
		//print_r($id); die();
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
	    		$this->dx('guardians.parent_id').' as parent_id',
	    		$this->dx('guardians.user_parent_id').' as user_parent_id'   		
	    	)
	    );
	    $this->db->where($this->dx('guardians.user_id').' = "'.$id.'"',NULL,FALSE);
		$this->db->where($this->dx('users.active')." = 1 ",NULL,FALSE);
		$this->db->join('users','guardians.user_parent_id = users.id ');
		return $this->db->get('guardians')->row();
	}

	
	

	



} ?>