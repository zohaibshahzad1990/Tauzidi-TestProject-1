<?php if(!defined('BASEPATH')) exit('You are not allowed to view this script');
class Api extends Api_Controller{

	public $response = array(
		'result_code' => 0,
		'result_description' => 'Default Response'
	);

	function __construct(){
        parent::__construct();
        $this->load->model('notifications_m');
        $this->load->library('messaging_manager');
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
    

    public function my_notifications(){
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $user_id = $this->token_user->_id;
        if($user_id){
            $field_name = '';
            $sort_order = '';
            $sort_field = '';
            $sort_role = 0;
            $search_field = '';
            $page_number = 0;
            $page_size = 10;
            $start = 0;
            $end = 20;
            $filter_parameters = array();
            if (isset($this->filter_params)) {
                $update = $this->filter_params;
                if(isset($update->filter)){
                    $search_field = $update->filter;
                }          
                if(isset($update->sortOrder)){                    
                    $sort_order = $update->sortOrder;
                }
                if(isset($update->sortField)){
                    $sort_field = $update->sortField;
                }

                if(isset($update->pageNumber)){
                    $page_number = $update->pageNumber;
                }
                if(isset($update->pageSize)){
                    $page_size = $update->pageSize;
                }
                if(isset($update->options->sortRole)){
                    $sort_role = $update->options->sortRole;
                }
                $start = $page_number * $page_size;
                $end = $start + $page_size;

                $filter_parameters = array(
                    "search_fields"=>$search_field,
                    "sort_order"=>$sort_order,
                    "sort_field"=>$sort_field,
                );       
            } 
            $total_rows = $this->notifications_m->count_all_filetered_active_notifications($user_id,$filter_parameters);
            $pagination = create_custom_pagination('api',$total_rows,$page_size,$start,TRUE);
            $posts = $this->notifications_m->limit($pagination['limit'])->get_my_notifications($user_id,$filter_parameters);
            $notification_array = array();
            if($posts){
                $count = $start+1;
                $_new = 0;
                foreach ($posts as $key => $post):
                    $notification_array[] = (object) array(
                        'id'=> $count++,
                        '_id'=>(int) $post->id,
                        'subject' =>$post->subject,
                        'body' =>$post->message,
                        'is_read' =>intval($post->is_read),
                        'active' =>$post->active,
                        'days_ago'=>facebook_time_ago($post->created_on),
                        'to_user_id'=>$post->to_user_id,
                        'created_by' =>$post->created_by,
                        'created_on'=> $post->created_on
                    );
                endforeach;
                $response = array(
                    'status'=>TRUE,
                    'count'=> $total_rows,
                    'unread'=> $this->notifications_m->count_unread_user_notifications($user_id),
                    'notifications'=> $notification_array
                );
            }else{
                $response = array(
                    'status'=>TRUE,
                    'count'=> 0,
                    'unread'=>0,
                    'notifications'=> array()
                );
            }
        }else{
            $post = array();
            $form_errors = $this->form_validation->error_array();
            foreach ($form_errors as $key => $value) {
              $post[$key] = $value;
            }
        } 
        echo json_encode($response);
    }

    public function get_my_notifications(){
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $user_id = $this->user->id;
        if($user_id){
            $field_name = '';
            $sort_order = '';
            $sort_field = '';
            $sort_role = 0;
            $search_field = '';
            $page_number = 0;
            $page_size = 10;
            $start = 0;
            $end = 10;
            $filter_parameters = array();
            if (isset($this->filter_params)) {
                $update = $this->filter_params;
                if(isset($update->filter)){
                    $search_field = $update->filter;
                }          
                if(isset($update->sortOrder)){                    
                    $sort_order = $update->sortOrder;
                }
                if(isset($update->sortField)){
                    $sort_field = $update->sortField;
                }

                if(isset($update->pageNumber)){
                    $page_number = $update->pageNumber;
                }
                if(isset($update->pageSize)){
                    $page_size = $update->pageSize;
                }
                if(isset($update->options->sortRole)){
                    $sort_role = $update->options->sortRole;
                }
                $start = $page_number * $page_size;
                $end = $start + $page_size;

                $filter_parameters = array(
                    "search_fields"=>$search_field,
                    "sort_order"=>$sort_order,
                    "sort_field"=>$sort_field,
                );       
            } 
            $total_rows = $this->notifications_m->count_all_filetered_active_notifications($user_id,$filter_parameters);
            $pagination = create_custom_pagination('api',$total_rows,$page_size,$start,TRUE);
            $posts = $this->notifications_m->limit($pagination['limit'])->get_my_notifications($user_id,$filter_parameters);
            $notification_array = array();
            if($posts){
                $count = $start+1;
                foreach ($posts as $key => $post):
                    $notification_array[] = (object) array(
                        'id'=> $count++,
                        '_id'=>(int) $post->id,
                        'subject' =>$post->subject,
                        'message' =>$post->message,
                        'is_read' => intval($post->is_read),
                        'active' =>$post->active,
                        'days_ago'=>facebook_time_ago($post->created_on),
                        'created_by' =>$post->created_by,
                        'created_on'=> $post->created_on
                    );
                endforeach;
                $response = array(
                    'totalCount'=>$total_rows,
                    'items'=> $notification_array
                );
            }else{
                $response = array(
                    'totalCount'=>$total_rows,
                    'items'=> []
                );
            }
        }else{
            $post = array();
            $form_errors = $this->form_validation->error_array();
            foreach ($form_errors as $key => $value) {
              $post[$key] = $value;
            }
        } 
        echo json_encode($response);
    }

    public function mark_as_read(){
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
                'field' =>  '_ids[]',
                'label' =>  'Notification ids',
                'rules' =>  'required',
            ),            
        ); 
        $notification_ids = $this->input->post('_ids'); 
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            if($result = $this->notifications_m->mark_as_read_notifications_bulk($notification_ids)){           
                $response = array(
                    'status' => TRUE,
                    'message' =>'Successufully mark as read',
                );
            }else{
                $response = array(
                    'status' => FALSE,
                    'message' => 'Could not mark as read notifications',
                );
            }
        }else{
            $post = array();
            $form_errors = $this->form_validation->error_array();
            foreach ($form_errors as $key => $value) {
                $post[$key] = $value;
            }
            $response = array(
                'status' => TRUE,
                'message' => 'Form validation failed',
                'validation_errors' => $post,
            );
        } 
        echo json_encode($response);
    }

    public function delete_notifications(){
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
                'field' =>  '_ids[]',
                'label' =>  'Notification ids',
                'rules' =>  'required',
            ),            
        ); 
        $notification_ids = $this->input->post('_ids'); 
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            if($result = $this->notifications_m->delete_notifications_in_bulk($notification_ids)){           
                $response = array(
                    'status' => 1,
                    'message' =>'Successufully deleted',
                );
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'Could not delete notifications',
                );
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

    function initiate_push_notification(){
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
                'field' =>  'to_user_id',
                'label' =>  'to_user_id',
                'rules' =>  'required|trim',
            ),
            array(
                'field' =>  'message',
                'label' =>  'message',
                'rules' =>  'required|trim',
            ),              
        ); 
        $to_user_id = $this->input->post('to_user_id'); 
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            $user_id = $this->token_user->_id;
            if($user_id){
                $to_user_id = $this->input->post('to_user_id');
                $user = $this->users_m->get($to_user_id);
                $from_user = $this->users_m->get($user_id);
                $message = $this->input->post('message');
                $sms_particulars =  array(
                    'token'=>$user->fcm_token,
                    'title'=> $this->input->post('title'),
                    'to'=>$user->phone,
                    'message'=>$message,
                    'vibrate'       => $this->input->post('vibrate'),
                    'sound'         => $this->input->post('sound'),
                    'badge'         => $this->input->post('badge'),
                    'largeIcon'     => $this->input->post('largeIcon'),
                    'smallIcon'     => $this->input->post('smallIcon')
                );


                $result = $this->messaging_manager->send_push_notification_via_fcm($sms_particulars);
                //echo $result->success;
                $response = array(
                    'status' => TRUE,
                    'message' =>'Operation Successufull',
                    'result' => $result
                );
            }else{
                $response = array(
                    'status' => FALSE,
                    'message' => 'Could not get user id details',
                );
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