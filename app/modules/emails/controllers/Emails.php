<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Emails extends Public_Controller{
	function __construct(){
        parent::__construct();
        $this->load->model('emails_m');
        $this->load->model('users/users_m');
        $this->load->library('messaging_manager');
    }

		public function send_queued_emails($limit = 5){
        	$queued_emails = $this->emails_m->get_emails_to_send($limit);
        	$successes = 0;
        	$failures = 0;
            foreach ($queued_emails as $queued_email) {
                if($queued_email->message){
                    $email_array = array(
                        'subject' => $queued_email->subject,
                        'email_to' => $queued_email->email_to,
                        'message' => $queued_email->message,
                        'sending_email' => $queued_email->sending_email,
                        'attachments' => $queued_email->attachments,
                        'cc' => $queued_email->cc,
                        'bcc' => $queued_email->bcc,
                        'embeded_attachments' => $queued_email->embeded_attachments,
                        'user_id' => $queued_email->user_id,
                        'created_by' => $queued_email->created_by,
                        'email_header' => $queued_email->email_header
                    );
                    $result_id = $this->messaging_manager->send_email_via_sendgrid_api($email_array);
                    if($result_id){
                       $this->emails_m->delete_email_queue($queued_email->id);
                       ++$successes;
                    }else{
                        $failures++;
                    }
                }else{
                   $this->emails_m->delete_email_queue($queued_email->id); 
                }
            }

        echo $successes.' Successes.<br/>';
        echo $failures.' Failures.<br/>';
    }

}
?>
