<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sms extends Public_Controller{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('sms_m');
        $this->load->model('users/users_m');
        $this->load->library('messaging_manager');
        $this->settings = $this->settings_m->get_settings(1)?:'';

    }

    public function send_queued_smses($limit = 5){
        if(false){
        //if($this->settings->disable_smses){
            echo "SMS delivery disabled by Super Admin.";
        }else{
            if(true){
            //if($this->settings->sms_delivery_enabled){
              $queued_smses = $this->sms_m->get_queued_smses_for_sending($limit);
              //echo json_encode($queued_smses);
              //die;
            	$successes = 0;
            	$failures = 0;
            	foreach($queued_smses as $queued_sms){
                    if($this->sms_m->send_system_sms(valid_phone($queued_sms->sms_to),$queued_sms->message,$queued_sms->created_by,$queued_sms->is_push, $queued_sms->fcm_token ,$queued_sms->user_id)){
                        //delete the sms
                        $this->sms_m->delete_sms_queue($queued_sms->id);
                        $successes++;
                    }else{
                        $this->sms_m->delete_sms_queue($queued_sms->id);
                        $failures++;
                    }                  

            	}
            	echo $successes.' Successes.<br/>';
            	echo $failures.' Failures.<br/>';
            }else{
                echo "SMS delivery disabled due to insufficient funds on Infobip";
            }
        }
    }

    function sms_send_to_options(){
        echo json_encode($this->sms_send_to_options);
    }

    function sms_multiple_segment_options(){
        echo json_encode($this->sms_multiple_segment_options);
    }
    
    function sms_all_group_and_user_lists(){
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        if($username=='chamasoft'&&$password=='-PrAZ8m9F!DEnd*'){
            $arr = array();

            $arr['groups'] = $this->groups_m->get_all_groups();
            $arr['users'] = $this->members_m->get_all_members();

            $arr['total_group_count'] = $this->groups_m->count_all();
            $arr['total_user_count'] = $this->members_m->count_all_members();

            echo json_encode($arr);
        }
    }

    function sms_group_lists(){
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        if($username=='chamasoft'&&$password=='-PrAZ8m9F!DEnd*'){
            $groups = $this->groups_m->get_all_groups();
            echo json_encode($groups);
        }
    }

    function sms_group_and_user_lists(){
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        if($username=='chamasoft'&&$password=='-PrAZ8m9F!DEnd*'){
            $arr = array();
            $group_options = $this->input->post('group_options');
            $group_options_array = unserialize($group_options);
            $group_id_list = "0";
            $count = 1;
            foreach($group_options_array as $group_id):
                if($count==1){
                    $group_id_list = $group_id;
                }else{
                    $group_id_list .= ','.$group_id;
                }
                $count++;
            endforeach;

            $arr['groups'] = $this->groups_m->get_group_options_by_group_id_list($group_id_list);
            $arr['users'] = $this->members_m->get_member_options_by_group_id_list($group_id_list);

            $arr['total_group_count'] = $this->groups_m->count_all();
            $arr['total_user_count'] = $this->members_m->count_all_members();
            echo json_encode($arr);
        }
    }

    function get_infobip_account_balance(){
        echo $balance = $this->curl->get_infobip_account_balance();
    }

    function toggle_sms_delivery(){
        if($this->settings->sender_id == "Chamasoft"){
            if($this->settings->disable_smses){
                $this->_disable_sms_delivery(TRUE);
            }else{
                if($balance = $this->curl->get_infobip_account_balance()){
                    if($balance<10){
                        $this->_disable_sms_delivery();
                    }else{
                        $this->_enable_sms_delivery();
                    }
                }else{
                    $this->_disable_sms_delivery();
                }
            }
        }else{
            echo "Toggle SMS disabled for the Sender ID: ".$this->settings->sender_id;
        }
    }

    function _disable_sms_delivery($ignore_email = FALSE){
        if($this->settings->sms_delivery_enabled==1||$this->settings->sms_delivery_enabled==NULL){
            $input = array(
                'sms_delivery_enabled' => 0,
                'modified_on' => time(),
                'modified_by' => 1
            );
            if($this->settings_m->update(1,$input)){
                if($ignore_email){

                }else{
                    if($this->messaging->send_sms_delivery_toggle_email(FALSE)){

                    }else{
                        echo "Could not send message to Finance Team";
                    }
                }
            }else{
                echo "Could not update SMS delivery setting";
            }
        }else{
            echo "SMS delivery already disabled";
        }
    }

    function _enable_sms_delivery(){
        if($this->settings->sms_delivery_enabled==0){
            $input = array(
                'sms_delivery_enabled' => 1,
                'modified_on' => time(),
                'modified_by' => 1
            );
            if($this->settings_m->update(1,$input)){
                if($this->messaging->send_sms_delivery_toggle_email(TRUE)){

                }else{
                    echo "Could not send message to Finance Team";
                }
            }else{
                echo "Could not update SMS delivery setting";
            }
        }else{
            echo "SMS delivery already enabled";
        }
    }


    function delete_old_queued_smses(){
        $this->sms_m->delete_old_queued_smses();
    }

 }
