<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Sms_m extends MY_Model {

	protected $_table = 'sms';

	public function __construct(){
		parent::__construct();
		$this->load->dbforge();
		$this->load->model('settings/settings_m');
		$this->load->model('sms_templates/sms_templates_m');
		$this->load->library('messaging_manager');
		$this->install();
	}

	function install(){
		$this->db->query("
		create table if not exists sms(
			id int not null auto_increment primary key,
			`sms_to` varchar(200),
			`sms_result_id` int,
			`message` varchar(200),
			`user_id` varchar(200),
			`system_sms` varchar(200),
			`sms_result` varchar(200),
			created_by varchar(200),
			created_on varchar(200),
			modified_on varchar(200),
			modified_by varchar(200)
		)");

		$this->db->query("
		create table if not exists sms_queue(
			id int not null auto_increment primary key,
			`sms_to` varchar(200),
			`message` varchar(200),		
			`user_id` int,
			created_by varchar(200),
			created_on varchar(200),
			modified_on varchar(200),
			modified_by varchar(200)
		)");
		$this->db->query("
		create table if not exists sms_result(
			id int not null auto_increment primary key,
			`sms_id` int,
			`sms_number` varchar(200),
			`sms_status` varchar(200),
			`message_id` int,
			`sms_cost` varchar(200),
			created_by varchar(200),
			created_on varchar(200),
			modified_on varchar(200),
			modified_by varchar(200)
		)");
	}

	function insert_sms_result($input=array(),$SKIP_VALIDATION=FALSE){
		return $this->insert_secure_data('sms_result', $input);
	}

	function insert_sms_queue($input=array(),$SKIP_VALIDATION=FALSE){
		return $this->insert_secure_data('sms_queue', $input);
	}

	function insert_sms_queue_batch($input=array(),$SKIP_VALIDATION=FALSE){
		return $this->insert_chunked_batch_secure_data('sms_queue', $input);
	}

	function build_sms_message($slug = '',$fields,$use_template = FALSE){
		$data = '';
		if($use_template){
			$sms_template = $slug;
			if($sms_template){
				$data = $sms_template;
			}
		}else{
			$sms_template = $this->sms_templates_m->get_by_slug($slug);
			if($sms_template){
				$data = $sms_template->sms_template;
			}
		}

        foreach ($fields as $k => $v)
        {
            $data= preg_replace('/\[' . $k . '\]/', $v, $data);
        }
        return $data;
	}

	function send_system_sms($phone_number='',$message='',$created_by='',$is_push = '', $fcm_token = '',$user_id =0){
		$from = isset($this->settings->sender_id)?$this->settings->sender_id:'';
		/*messaging_manager->send_password_recovery_email($identity,$subject,$message);*/
		
		if($is_push == 1){
			$sms_particulars =  array(
				'token'=>$fcm_token,
				'title'=>"Pick Up",
				'to'=>$phone_number,
				'message'=>$message,
				'vibrate'       => '',
                'sound'         => '',
                'badge'         => '',
                'largeIcon'     => '',
                'smallIcon'     => ''
			);
			//print_r($sms_particulars); die();
			$res = $this->messaging_manager->send_push_notification_via_fcm($sms_particulars);
			//print_r($res); die();
			$res = json_decode($res);
			if($res->success){
				$result = 1;
			}else{
				$result = 0;
			}
			//print_r($result->success); die;
		}else{
	    	$result = $this->messaging_manager->send_system_sms($phone_number,$message);
	    	//print_r($result); die();
	    }
        if($result){
			$id = $this->insert(
				array(
					'sms_to'	=>	$phone_number,
					'sms_result_id'	=>	$result,
					'message'	=>	$message,
					'system_sms'=>	1,
					'user_id'=>$user_id,
					'is_push'=>	$is_push,
					'fcm_token'=> $fcm_token,
					'created_by'=>	$created_by?$created_by:1,
					'created_on'=>  time(),
				)
			);
			if($id){
				return TRUE;
			}else{
				return FALSE;
			}
		}else{
			return FALSE;
		}
	}


	function insert($input = array(),$SKIP_VALIDATION=FALSE){
        return $this->insert_secure_data('sms', $input);
	}

	function get_all(){
		$this->select_all_secure('sms');
		$this->db->order_by($this->dx('created_on'),'DESC',FALSE);
		return $this->db->get('sms')->result();
	}
	
	function insert_batch_sms_queue($input = array(),$SKIP_VALIDATION=FALSE){
        return $this->insert_chunked_batch_secure_data('sms_queue', $input);
	}

	function get_queued_sms($id=0){
		$this->select_all_secure('sms_queue');
		$this->db->where('id',$id);
		return $this->db->get('sms_queue')->row();
	}

	function update($id,$input,$SKIP_VALIDATION=FALSE){
    	return $this->update_secure_data($id,'sms_queue',$input);
    }

    function delete($id=0){
		$this->db->where('id',$id);
		return $this->db->delete('sms_queue');
    }

    function delete_sms_queue($id=0){
		$this->db->where('id',$id);
		return $this->db->delete('sms_queue');
    }

	function get_all_queued_smses(){
		$this->select_all_secure('sms_queue');
		return $this->db->get('sms_queue')->result();
	}

	function get_queued_smses_for_sending($limit=50){
		$this->select_all_secure('sms_queue');
		$this->db->order_by($this->dx('created_on'),'ASC',FALSE);
		$this->db->limit($limit);
		return $this->db->get('sms_queue')->result();
	}
	public function count_all(){
		$this->select_all_secure('sms');
		$this->db->order_by($this->dx('created_on'),'ASC',FALSE);
		return count($this->db->get('sms')->result());
	}
}?>