<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Settings_m extends MY_Model {

	protected $_table = 'settings';

	function __construct(){
		parent::__construct();
		$this->load->dbforge();
		$this->install();
	}

	public function install(){
		$this->db->query("
		create table if not exists settings(
			id int not null auto_increment primary key,
			`application_name` varchar(200),
			`url` varchar(200),
			`protocol` varchar(200),
			`favicon` varchar(200),
			`responsive_logo` varchar(200),
			`logo` varchar(200),
			`admin_login_logo` varchar(200),
			`paper_header_logo` varchar(200),
			`paper_footer_logo` varchar(200),
			`sender_id` varchar(200),
			`active` varchar(200),
			created_by varchar(200),
			created_on varchar(200),
			modified_on varchar(200),
			modified_by varchar(200)
		)");
		
	}

	public function insert($input = array(),$skip_value = FALSE){
		return $this->insert_secure_data('settings',$input);
	}

	function update($id,$input,$val=FALSE){
  	return $this->update_secure_data($id,'settings',$input);
  }    

	public function count_all(){
		return $this->count_all_results('settings');
	}
	
	public function get_all(){	
		$this->select_all_secure('settings');
		return $this->db->get('settings')->result();
	}

	public function get($id = 0){	
		$this->select_all_secure('settings');
		$this->db->where('id',$id);
		return $this->db->get('settings')->row();
	}

	function get_settings($id = 0){
		$this->select_all_secure('settings');
		$this->db->where('id',$id);
		$this->db->limit(1);
		$setting = $this->db->get('settings')->row();
		if($setting){
			$setting_details = new stdClass();
			$setting_details->id = $setting->id;
		    $setting_details->application_name = $setting->application_name;
		    $setting_details->url = $setting->url;
		    $setting_details->protocol = $setting->protocol;
		    $setting_details->favicon = $setting->favicon;
		    $setting_details->responsive_logo = $setting->responsive_logo;
		    $setting_details->logo = $setting->logo?$setting->logo:'templates/metronic/img/no_image.png';
		    $setting_details->active = $setting->active;
		    $setting_details->created_by = $setting->created_by;
		    $setting_details->created_on = $setting->created_on;
		    $setting_details->modified_on = $setting->modified_on;
		    $setting_details->modified_by = $setting->modified_by;
		    //$setting_details->country = $setting->country;
		    //$setting_details->currency_code =  isset($currencies[$setting->currency_code])?$currencies[$setting->currency_code]:"KES";
			return $setting_details;
		}else{
			$setting = new stdClass();
			$setting->application_name = "Application";
			$setting->favicon = "";
			$setting->logo = 'templates/metronic/img/no_image.png';
			$input = array(
				'application_name' => $setting->application_name,
				'logo'=>$setting->logo
			);
			$this->insert($input);
			return $setting;
		}
	}
  

}