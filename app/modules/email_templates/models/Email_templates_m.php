<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Email_templates_m extends MY_Model {

	protected $_table = 'email_templates';

	function __construct(){
		$this->load->dbforge();
		$this->install();
	}

	function install(){
		$this->db->query("
		create table if not exists email_templates(
			id int not null auto_increment primary key,
			`title` varchar(200),
			`slug` varchar(200),
			`description` varchar(200),
			`content` text,
			`active` varchar(200),
			created_by varchar(200),
			created_on varchar(200),
			modified_on varchar(200),
			modified_by varchar(200)
		)");
	}

	function insert($input,$skip_validation=FALSE){
		return $this->insert_secure_data('email_templates',$input);
	}

	function get_all(){
		$this->select_all_secure('email_templates');
		$this->db->order_by($this->dx('created_on'), 'DESC',FALSE);
		return $this->db->get('email_templates')->result();
	}

	function get_all_array(){
		$arr = array();
		$this->db->order_by('created_on', 'DESC');
		$email_templates = $this->db->get('email_templates')->result();
		foreach($email_templates as $email_template){
			$arr[$email_template->slug] = $email_template->title;
		}
		return $arr;
	}

	function get($id = 0){
		$this->select_all_secure('email_templates');
		$this->db->where(array('id' => $id));
		return $this->db->get('email_templates')->row();
	}

	function get_by_slug($slug = '',$id = ''){
		$this->select_all_secure('email_templates');
		$this->db->where($this->dx('slug').'="'.$slug.'"',NULL,FALSE);
		if($id){
			$this->db->where('id !=',$id);
		}
		return $this->db->get('email_templates')->row();
	}

	function update($id, $input,$skip_validation = false){
		return $this->update_secure_data($id,'email_templates',$input);
	}

	function delete($id = 0){		
		$this->db->where('id', $id);
    	$del=$this->db->delete('email_templates');   
    	return $del;
	}

	function build_message(){
		
	}

}
