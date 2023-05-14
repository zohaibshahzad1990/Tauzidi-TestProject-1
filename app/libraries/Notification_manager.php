<?php 
if (!defined('BASEPATH')) exit('No direct script access allowed');
class Notification_manager{
	protected $ci;
	public $educationKe_settings;
	/**
		Notification Categories
		1. Profile update 
	**/

	protected $category_options_letters = array(
		1 =>'P', 
		2 =>'C',
		3 =>'L',
		4 =>'F',
		5 =>'P',
		6 =>'P',
		7 => 'M',
		8 =>'R',
		9 =>'L',
		10 =>'B',
		11 =>'B',
		12 =>'S',
		13 =>'D',
		14 =>'W',
		15 => 'G',
		16 => 'D',
		17 => "W",
		18 => 'W',
		19 => 'P'
	);

	public function __construct(){
		//die;
		$this->ci= & get_instance();
		$this->ci->load->model('notifications/notifications_m');
		$this->ci->load->model('settings/settings_m');
		$this->ci->load->library('Curl');
	}

	public function create_bulk($notifications = array()){
		$notification_values_are_valid = FALSE;
		$notifications = (array)$notifications;
		if(empty($notifications)){
			$this->ci->session->set_flashdata('error','Notifications empty');
			return FALSE;
		}else{
			$notification_entries = array();
			$admins = $this->ci->users_m->get_system_admins();			
			foreach ($notifications as $key => $notification) {
				$enable_notifications = TRUE;
				$group_is_valid = TRUE;
				if(is_object($notification)){

				}else{
					$notification = (object)$notification;
				}				
				$subject = isset($notification->subject)?$notification->subject: '';
				$message = isset($notification->message)?$notification->message:'';
				$from_user = isset($notification->from_user)?$notification->from_user:array();
				$to_user_id = isset($notification->to_user_id)?$notification->to_user_id:0;
				$call_to_action = isset($notification->call_to_action)?$notification->call_to_action:'';
				$call_to_action_link = isset($notification->call_to_action_link)?$notification->call_to_action_link:'';
				$file_size = isset($notification->file_size)?$notification->file_size:0;
				$file_path = isset($notification->file_path)?$notification->file_path:'';
				$file_type = isset($notification->file_type)?$notification->file_type:1;
				$resource_id = isset($notification->resource_id)?$notification->resource_id:'';		
				if($subject&&$message&&$from_user&&$to_user_id){

					if($to_user_id == "admin"){
						if($admins){
							foreach ($admins as $key => $admin) {
								$notification_entries[] = array(
									'from_user_id' => $from_user,
									'to_user_id' => $admin->id,
									'subject' => $subject,
									'message' => $message,
									'call_to_action' => $call_to_action,
									'call_to_action_link' => $call_to_action_link,
									'is_read' => 0,
									'active' => 1,
									'created_by' => $from_user,
									'created_on' => time(),
									'resource_id' => $resource_id,
									'file_size' => $file_size,
									'file_path' => $file_path,
									'file_type' => $file_type,
								);
							}
							$notification_values_are_valid = TRUE;
						}
					}else{
						$notification_entries[] = array(
							'from_user_id' => $from_user,
							'to_user_id' => $to_user_id,
							'subject' => $subject,
							'message' => $message,
							'call_to_action' => $call_to_action,
							'call_to_action_link' => $call_to_action_link,
							'is_read' => 0,
							'active' => 1,
							'created_by' => $from_user,
							'created_on' => time(),
							'resource_id' => $resource_id,
							'file_size' => $file_size,
							'file_path' => $file_path,
							'file_type' => $file_type,
						);
						$notification_values_are_valid = TRUE;
					}
				}
				
			}
			if($notification_values_are_valid){
				if(empty($notification_entries)){
					return TRUE;
				}else{
					if($this->ci->notifications_m->insert_batch($notification_entries)){
						//$this->send_batch_push_notification($notification_entries);
						return TRUE;
					}else{
						$this->ci->session->set_flashdata('error','Could not insert notification');
						return FALSE;
					}
				}
				
			}else{
				return FALSE;
			}
		}
	}

	public function mark_member_notification_as_read($unread_member_notifications_array = array(),$url = '',$member_id=0){
		if(isset($unread_member_notifications_array[trim($url)][$member_id])){
			$notification_id = $unread_member_notifications_array[trim($url)][$member_id];
			$input = array(
				'is_read'=>1,
				'modified_on'=>time()
			);
			if($result = $this->ci->notifications_m->update($notification_id,$input)){

			}else{
				$this->ci->session->set_flashdata('error','Notification could not be marked as read/');
			}
		}
	}
}