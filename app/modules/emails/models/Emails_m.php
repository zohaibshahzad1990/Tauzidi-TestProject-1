<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Emails_m extends MY_Model {

	protected $_table = 'emails';

	public function __construct(){
		parent::__construct();
		$this->load->dbforge();
		$this->install();
		$this->load->model('email_templates/email_templates_m');
	}

	function install(){
		$this->db->query("create table if not exists emails_queue(
			id int not null auto_increment primary key,
			`email_to` varchar(200),
			`email_from` varchar(200),
			`sending_email` varchar(200),
			`cc` varchar(200),
			`bcc` varchar(200),
			`embeded_attachments` varchar(200),
			`user_id` varchar(200),
			`subject` varchar(200),
			`message` text,
			`attachments` varchar(200),
			`is_draft` varchar(200),
			`email_header` varchar(200),
			created_by varchar(200),
			created_on varchar(200),
			modified_on varchar(200),
			modified_by varchar(200)
		)");

		$this->db->query("create table if not exists emails(
			id int not null auto_increment primary key,
			`email_to` varchar(200),
			`email_from` varchar(200),
			`user_id` varchar(200),
			`subject` varchar(200),
			`message` text,
			`attachments` varchar(200),
			`is_read` varchar(200),
			`email_header` varchar(200),
			created_by varchar(200),
			created_on varchar(200),
			modified_on varchar(200),
			modified_by varchar(200)
		)");
	}

	function insert($input = array(),$SKIP_VALIDATION=FALSE){
        return $this->insert_secure_data('emails', $input);
	}

	function get_all(){
		$this->select_all_secure('emails');
		return $this->db->get('emails')->result();
	}

	function insert_batch($input = array(),$SKIP_VALIDATION=FALSE){
        return $this->insert_batch_secure_data('emails', $input);
	}

	function insert_batch_emails($input = array(),$SKIP_VALIDATION=FALSE){
        return $this->insert_batch_secure_data('emails', $input);
	}

	function update($id,$input,$SKIP_VALIDATION=FALSE){
    	return $this->update_secure_data($id,'emails',$input);
    }

    function delete($id=0){
		$this->db->where('id',$id);
		return $this->db->delete('emails');
    }

    function count_all_unread_emails(){
    	$this->db->where($this->dx('user_id').' = "'.$this->user->id.'"',NULL,FALSE);
    	$this->db->where($this->dx('is_read'). ' ="0"',NULL,FALSE);
    	return $this->db->count_all_results('emails')?:0;
    }

    function count_all_emails(){
    	$this->db->where($this->dx('user_id').' = "'.$this->user->id.'"',NULL,FALSE);
    	return $this->db->count_all_results('emails')?:0;
    }

    function get($id=0){
    	$this->select_all_secure('emails');
    	$this->db->where('id',$id);
    	return $this->db->get('emails')->row();
    }

    function insert_email_queue($input = array(),$SKIP_VALIDATION=FALSE){
        return $this->insert_secure_data('emails_queue', $input);
    }

    function count_all_queued_emails(){
    	$this->db->where($this->dx('created_by').' = "'.$this->user->id.'"',NULL,FALSE);
    	$this->db->where($this->dx('is_draft').' ="0"',NULL,FALSE);
    	return $this->db->count_all_results('emails_queue')?:0;
    }

    function get_all_emails(){
    	$this->select_all_secure('emails');
    	$this->db->where($this->dx('user_id').' = "'.$this->user->id.'"',NULL,FALSE);
    	$this->db->order_by($this->dx('created_on'),'DESC',FALSE);
    	return $this->db->get('emails')->result();
    }

    function get_all_queued_emails(){
    	$this->select_all_secure('emails_queue');
    	$this->db->where($this->dx('created_by').' = "'.$this->user->id.'"',NULL,FALSE);
    	$this->db->where($this->dx('is_draft').' ="0"',NULL,FALSE);
    	$this->db->order_by($this->dx('created_on'),'DESC',FALSE);
    	return $this->db->get('emails_queue')->result();
    }

    function count_all_draft_emails(){
		$this->db->where($this->dx('created_by').' = "'.$this->user->id.'"',NULL,FALSE);
    	$this->db->where($this->dx('is_draft').' ="1"',NULL,FALSE);
    	return $this->db->count_all_results('emails_queue')?:0;
	}

	function get_all_draft_emails(){
		$this->select_all_secure('emails_queue');
		$this->db->where($this->dx('created_by').' = "'.$this->user->id.'"',NULL,FALSE);
    	$this->db->where($this->dx('is_draft').' ="1"',NULL,FALSE);
    	return $this->db->get('emails_queue')->result();
	}

	function update_queued_mails($id,$input,$SKIP_VALIDATION=FALSE){
    	return $this->update_secure_data($id,'emails_queue',$input);
    }

	function delete_queued($id=0){
		$this->db->where('id',$id);
		return $this->db->delete('emails_queue');
    }

    function get_queued_emails_for_sending($limit=0){
    	$this->select_all_secure('emails_queue');
    	$this->db->where($this->dx('is_draft').' ="0"',NULL,FALSE);
    	$this->db->order_by($this->dx('created_on'),'DESC',FALSE);
    	$this->db->limit($limit);
    	return $this->db->get('emails_queue')->result();
    }



    function count_all_sent_emails(){
    	$this->db->where($this->dx('created_by').' = "'.$this->user->id.'"',NULL,FALSE);
    	return $this->db->count_all_results('emails')?:0;
    }

    function get_all_sent_emails(){
    	$this->select_all_secure('emails');
    	$this->db->where($this->dx('created_by').' = "'.$this->user->id.'"',NULL,FALSE);
    	return $this->db->get('emails')->result();
    }


		function build_email_message($slug = '',$fields,$passed_values=''){
			$data = '';
			$email_template = $this->email_templates_m->get_by_slug($slug);
			if($email_template){
				$data = $email_template->content;
			}
	        foreach ($fields as $k => $v)
	        {
	            $data= preg_replace('/\[' . $k . '\]/', $v, $data);
	        }
	        if($passed_values){

	        }
	        return $data;
		}


		function send_email($to='',$subject='',$message='',$email_from='',$sending_email='',$attachments='',$cc='',$bcc='',$embeded_attachments='',$group_id='',$member_id='',$user_id='',$created_by=0,$header = "",$donor_id=0)
		{
			$id = '';
			$mailer = $this->pmailer->send_mail($to,$subject,$message,$sending_email,unserialize($attachments),$cc,$bcc,unserialize($embeded_attachments),$header);
			if($mailer)
			{
				$id = $this->insert(array(
						'email_to'		=>	$to,
						'subject'		=>	$subject,
						'message'		=>	$message,
						'email_from'	=>	$email_from?$email_from:'System Admin',
						'sending_email'	=>	$sending_email,
						//'donor_id'		=>	$donor_id,
						'user_id'		=>	$user_id,
						'attachments'	=>	$attachments,
						'cc'			=>	$cc,
						'bcc'			=>	$bcc,
						'embeded_attachments'=> $embeded_attachments,
						'created_on'	=>	time(),
						'created_by'	=>	$created_by,
						'email_header'  =>	$header
					));
				if($id)
				{
					return $mailer;
				}
			}
			else
			{
				return $mailer;
			}
		}


			function get_emails_to_send($limit = 5){
				$this->select_all_secure('emails_queue');
				//$this->db->where($this->dx('is_draft').' IS NULL ',NULL,FALSE);
				//$this->db->where($this->dx('message').'!=""',NULL,FALSE);
				$this->db->limit($limit);
				$this->db->order_by('id','ASC');
				return $this->db->get('emails_queue')->result();
			}


			function delete_email_queue($id = 0){
				$this->db->where('id',$id);
				return $this->db->delete('emails_queue');
			}




}?>
