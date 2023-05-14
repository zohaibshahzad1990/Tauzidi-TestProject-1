<?php if(!defined('BASEPATH')) exit('You are not allowed to view this script');
class Api extends Api_Controller{

	public $response = array(
		'result_code' => 0,
		'result_description' => 'Default Response'
	);

	function __construct(){
        parent::__construct();
        $this->load->model('user_groups/user_groups_m');
    }

    public function _remap($method, $params = array()){
        if(method_exists($this, $method)){
            return call_user_func_array(array($this, $method), $params);
        }
       $this->output->set_status_header('404');
       header('Content-Type: application/json');
       $file = file_get_contents('php://input')?(array)json_decode(file_get_contents('php://input')):array();
       echo json_encode(
        array(
            'status' =>  404,
            'message' =>  'The endpoint cannot be found: '.$this->uri->uri_string(),
        ));
    }

    public function create_mail(){        
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $validation_rules = array(
            array(
                'field' => 'user_id_to[]',
                'label' => 'User Name',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'subject',
                'label' => 'Subject',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'message',
                'label' => 'Message',
                'rules' => 'required|trim',
            ),
        ); 
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            $cc = array();
            $bcc = array();
            $attachment = array();
            $user = array();            
            $user_id_to = $this->input->post('user_id_to');
            $user_id_cc = $this->input->post('user_id_cc');
            $user_id_bcc = $this->input->post('user_id_bcc');
            $user_ids = array_merge($user_id_to,$user_id_cc,$user_id_bcc);
            if($user_ids){
                $user_details = $this->users_m->get($this->token_user->_id);
                $users_email_option = $this->users_m->get_user_email_array_options($user_ids);
                if(in_array('all',$user_id_to)){
                    foreach($user_id_to as $key => $value){
                        $result = isset($users_email_option[$value])?$users_email_option[$value]:'';
                        if($result){
                            $user[] = $result;
                        }
                    }  
                }else{
                    foreach($user_id_to as $key => $value) 
                    {
                       
                        $user[] = isset($users_email_option[$value])?$users_email_option[$value]:'';
                    }


                    if(is_array($user_id_cc)){
                        foreach ($user_id_cc as $key => $value) {
                            if(!in_array($value, $user)){
                                $cc[] = isset($users_email_option[$value])?$users_email_option[$value]:'';
                            }
                        }
                    }

                    if(is_array($user_id_bcc)){
                        foreach($user_id_bcc as $key => $value) {
                            if(!in_array($value, $user) && !in_array($value, $cc))
                            {
                                $bcc[] = isset($users_email_option[$value])?$users_email_option[$value]:'';
                            }
                        }
                    }
                }
                $message = $this->input->post('message');
                $subject = $this->input->post('subject');
                $attachment = $this->input->post('file_names');
                $send = $this->input->post('send');
                $attach = array();
                if($attachment){
                    foreach ($attachment as $value) {
                        $attach[] = 'uploads/emails/'.$value;
                    }
                }
                $email_id = $this->messaging_manager->create_and_queue_email($user,$message,$user_details,$subject,$attach,$cc,$bcc,'',$send);
            }
        }else{
            $post = array();
            $form_errors = $this->form_validation->error_array();
            foreach ($form_errors as $key => $value) {
                $post[$key] = $value;
            }
            $response = array(
                'status' => 0,
                'message' => 'Form validation failed',
                'validation_errors' => $post,
            );
        } 
        echo json_encode($response);
    }

   
   

}