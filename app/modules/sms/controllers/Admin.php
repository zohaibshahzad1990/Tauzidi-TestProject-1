<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Admin extends Admin_Controller{

	protected $data=array();
	protected $validation_rules = array(
		array(
			'field' => 'send_to',
			'label' => 'Send message to',
			'rules' => 'required|trim'
		),
		array(
			'field' => 'message',
			'label' => 'message',
			'rules' => 'required|trim'
		),
	);

	private $message_type = array(
		 "All Users", " Individual Users"
	);

	function __construct(){
        parent::__construct();
        $this->load->model('sms_m');
    }

    function _additional_validation_rules(){
    	$send_to = $this->input->post('send_to');
    	if($send_to==1){
    		//do nothing
    	}else {
    		$this->validation_rules[] = array(
    			'field' => 'user_ids',
				'label' => 'Users',
				'rules' => 'callback__required_user_ids'
    		);
    	}
    }

    function _required_user_ids(){
    	$user_ids = $this->input->post('user_ids');
    	if(empty($user_ids)){
    		$this->form_validation->set_message('_required_user_ids','Users field to send to can not be empty.');
    		return FALSE;
    	}else{
    		return TRUE;
    	}
    }

	function index(){
		$this->template->title('SMS')->build('admin/index');
	}

	function compose(){
		$post = new StdClass();
		$this->_additional_validation_rules();
		$this->form_validation->set_rules($this->validation_rules);
		if($this->form_validation->run()){
			$send_to = $this->input->post('send_to');
			$message = $this->input->post('message');
			$phones_array = array();
			$message_array = array();
			$user_id_array = array();
			$created_by_array = array();
			$created_on_array = array();
			$input = array();
			if($send_to==1){
				$users = $this->users_m->get_system_admins();
				foreach ($users as $user_id => $user_phone) {
					$input[] = array(
						'sms_to' => valid_phone($user_phone),
						'message' => $message,
						'user_id' => $user_id,
						'created_by' => $this->user->id,
						'created_on' => time(),
					);
				}
			}else {
				$user_ids = $this->input->post('user_ids');
				if(in_array("all", $user_ids)){
					$user_groups = $this->input->post('send_to');
					$send_to_users = $this->users_m->get_users_in_user_groups($user_groups);
					
					foreach ($send_to_users as $user) {
						$input[] = array(
							'sms_to' => valid_phone($user->phone),
							'message' => $message,
							'user_id' => $user->id,
							'created_by' => $this->user->id,
							'created_on' => time(),
						);
					}
				}else{
					foreach ($user_ids as $key => $user_id) {
						$user = $this->ion_auth->get_user($user_id);
						$input[] = array(
							'sms_to' => valid_phone($user->phone),
							'message' => $message,
							'user_id' => $user->id,
							'created_by' => $this->user->id,
							'created_on' => time(),
						);
					}
				}
			}
			//print_r($input); die();
			$id = $this->sms_m->insert_batch_sms_queue($input);
			if($id){
				$this->session->set_flashdata('success','Successfully queued smses');
			}else{
				$this->session->set_flashdata('error','Error occured while queueing smses');
			}
			redirect('admin/sms/queued_smses','refresh');
		}
		foreach ($this->validation_rules as $key => $field){
            $field_name = $field['field'];
            $post->$field_name = set_value($field['field']);
        }
        
        $user_ids = $this->users_m->get_user_options();
		$this->data['user_ids'] = $user_ids;
		$this->data['send_to_options'] = $this->users_m->get_user_group_options();
		$this->data['user_groups'] = $this->users_m->get_user_group_options();
		$this->data['message_type'] = $this->message_type;
		//print_r($this->data); die();
		$this->template->title('Compose SMS')->build('admin/form',$this->data);
	}

	function queued_smses(){
		$this->data['posts'] = $this->sms_m->get_all_queued_smses();
		$this->template->title('Queued SMSs')->build('admin/queued_smses',$this->data);
	}

	function listing(){
		$total_rows = $this->sms_m->count_all();
        $pagination = create_pagination('admin/sms/listing', $total_rows,100,4,TRUE);
        $this->data['posts'] = $this->sms_m->limit($pagination['limit'])->get_all();
        $this->data['pagination'] = $pagination;
		$this->template->title('Sent SMSes')->build('admin/listing',$this->data);
	}

	function delete($id=0, $redirect = TRUE){
		if(!$id){ 
            if($redirect){
                redirect('admin/sms/queued_smses');
            }else{
                return FALSE;
            }
        }
        $post = $this->sms_m->get_queued_sms($id);
        if(!$post){
            $this->session->set_flashdata('error','Sorry, the queued sms does not exist');
            if($redirect){
                redirect('admin/sms/queued_smses');
            }else{
                return FALSE;
            }
        }
        
	    if($this->sms_m->delete($post->id)){
	        $this->session->set_flashdata('success','Queued SMS deleted');
	        if($redirect){
	            redirect('admin/sms/queued_smses');
	        }else{
	            return TRUE;
	        }
	    }else{
	        $this->session->set_flashdata('error','could not delete queued SMS');
	        if($redirect){
	            redirect('admin/sms/queued_smses');
	        }else{
	            return TRUE;
	        }
	    }
	}

	function action(){
		$action_to = $this->input->post('action_to');
        $action = $this->input->post('btnAction');
        if($action == 'bulk_delete'){
            for($i=0;$i<count($action_to);$i++){
                $this->delete($action_to[$i],FALSE);
            }
        }
        redirect('admin/sms/queued_smses');
	}

}?>