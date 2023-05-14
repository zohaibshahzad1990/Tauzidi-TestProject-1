<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notifications extends Public_Controller{

	protected $data = array();

	public function __construct(){
	    parent::__construct();
	    $this->load->model('notifications_m');
	}

 	public function delete($days = 5){
 		$days_ago = strtotime('-'.$days.' days', time());
 		$notifications = $this->notifications_m->get_old_notifications_by_date($days_ago);
 		//print_r($notifications);
 		$ids = [];
 		$count = 0;
 		foreach ($notifications as $key => $notification) {
 			$ids[] = $notification->id;
 			$count++;
 		}

 		if(count($ids) > 0){
 			$this->notifications_m->delete_notifications_in_bulk($ids);
 		}
 		echo $count . " notifications deleted";
 	}



}