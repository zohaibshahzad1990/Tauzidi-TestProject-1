<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Sms_templates_m extends MY_Model {

	protected $_table = 'sms_templates';

	function __construct()
	{
		$this->load->dbforge();
		$this->install();
	}

	function install()
	{
		$this->db->query("
		create table if not exists sms_templates(
			id int not null auto_increment primary key,
			`title` varchar(200),
			`slug` varchar(200),
			`description` text,
			`sms_template` varchar(200),
			`active` varchar(200),
			created_by varchar(200),
			created_on varchar(200),
			modified_on varchar(200),
			modified_by varchar(200)
		)");
	}

	function insert($input,$skip_validation=FALSE)
	{
		return $this->insert_secure_data('sms_templates',$input);
	}

	function get_all()
	{
		$this->select_all_secure('sms_templates');
		$this->db->order_by($this->dx('created_on'), 'DESC',FALSE);
		return $this->db->get('sms_templates')->result();
	}

	function get_all_array()
	{
		$arr = array();
		$this->db->order_by('created_on', 'DESC');
		$sms_templates = $this->db->get('sms_templates')->result();
		foreach($sms_templates as $sms_template){
			$arr[$sms_template->slug] = $sms_template->title;
		}
		return $arr;
	}


	function get($id)
	{
		$this->select_all_secure('sms_templates');
		$this->db->where(array('id' => $id));
		return $this->db->get('sms_templates')->row();
	}

	function get_by_slug($slug,$id='')
	{
		$this->select_all_secure('sms_templates');
		$this->db->where($this->dx('slug').'="'.$slug.'"',NULL,FALSE);
		if($id)
		{
			$this->db->where('id !=',$id);
		}
		return $this->db->get('sms_templates')->row();
	}

	function get_many_by($params = array())
	{
		$this->select_all_secure('sms_templates');
		if (!empty($params['status']))
		{
			// If it's all, then show whatever the status
			if ($params['status'] != 'all')
			{
				// Otherwise, show only the specific status
				$this->db->where($this->dx('status').'="'.$params['status'].'"',NULL,FALSE);
			}
		}
		// Nothing mentioned, show live only (general frontend stuff)
		else
		{
			$this->db->where($this->dx('status').'="live"',NULL,FALSE);
		}
		// By default, dont show future email_templates

		if (!isset($params['show_future']) || (isset($params['show_future']) && $params['show_future'] == FALSE))
		{
			$this->db->where($this->dx('created_on').' <='.time(),NULL,FALSE);
		}

		// Limit the results based on 1 number or 2 (2nd is offset)

		//echo print_r($params); die;
		if (isset($params['limit']) && is_array($params['limit']))
			$this->db->limit($params['limit'][0], $params['limit'][1]);
		elseif (isset($params['limit']))

			$this->db->limit($params['limit']);
		return $this->get_all();

	}



	function count_all($params = array())
	{
		return $this->db->count_all_results('sms_templates');
	}



	function update($id, $input,$skip_validation = false)
	{
		return $this->update_secure_data($id,'sms_templates',$input);
	}

	function delete($id = 0){		
		$this->db->where('id', $id);
    	$del=$this->db->delete('sms_templates');   
    	return $del;
	}

}
