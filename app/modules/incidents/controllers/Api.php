<?php if(!defined('BASEPATH')) exit('You are not allowed to view this script');
class Api extends Api_Controller{

	public $response = array(
		'result_code' => 0,
		'result_description' => 'Default Response'
	);

    public $time_start;

    public $payment_status = [
        1 => "Phone Number",
        2 => "Email Address"
    ];

	function __construct(){
        parent::__construct();
        $this->load->model('incidents_m');
        $this->load->model('users/users_m');
        $this->load->library('notification_manager');
        $this->time_start = microtime(true);
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

    public function report(){
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
                    'field' =>  'name',
                    'label' =>  'Incident Name',
                    'rules' =>  'required|required',
                ),
            array(
                    'field' =>  'description',
                    'label' =>  'Incident description',
                    'rules' =>  'trim|required',
                )
        );        
        $name = $this->input->post('name');
        $description = $this->input->post('description');
        $user_id = $this->token_user->_id;
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            if($user = $this->users_m->get($user_id)){
                $groups = $this->input->post('group_id');
                $incident_code = $this->incidents_m->calculate_incident_number();
                $input = array(
                    'name' => $name,
                    'description' => $description,
                    'reported_by' => $user_id,
                    'incident_code' => $incident_code,
                    'is_resolved' => 0,
                    'active' => 1,
                    'created_on' => time(),
                    'created_by'=> $user_id,
                );
                if($id =  $this->incidents_m->insert($input)){
                    $notification[] = [
                       'subject' => $name .' - '. $incident_code,
                        'message' =>  $description,
                        'from_user' =>  $user_id,
                        'to_user_id' =>  'admin',
                        'call_to_action' =>  '',
                        'call_to_action_link' =>  '',
                        'file_size' =>  0,
                        'file_path' =>  '',
                        'file_type' => 1,
                        'resource_id' => $id 
                    ];
                    $this->notification_manager->create_bulk($notification);
                    $response = array(
                        'status' => TRUE,
                        'message' => 'Operation Successful',
                    );
                }else{
                    $response = array(
                        'status' => FALSE,
                        'message' => 'Could not create incident details',
                    );
                }
            }else{
                $response = array(
                    'status' => FALSE,
                    'message' => 'User details is not found',
                );
            }
        }else{
            $post = array();
            $form_errors = $this->form_validation->error_array();
            foreach ($form_errors as $key => $value) {
                $post[$key] = $value;
            }
            $response = array(
                'status' => FALSE,
                'message' => 'Form validation failed',
                'validation_errors' => $post,
            );
        } 
        $this->activity_log->logActivity($response,'',$user_id,$this->time_start);
        echo json_encode($response);
    }

    public function get_my_incidents(){
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $field_name = '';
        $status = 0;
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
            if(isset($update->status)){                    
                $status = $update->status;
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
                "is_resolved" => $status
            );       
        } 
        $user_id = $this->token_user->_id; 
        $total_rows = $this->incidents_m->count_all_user_filetered_active_incidents($user_id,$filter_parameters);
        $pagination = create_custom_pagination('api',$total_rows,$page_size,$start,TRUE);
        $posts = $this->incidents_m->limit($pagination['limit'])->get_my_incidents($user_id,$filter_parameters);
        $incident_array = [];
        if($posts){
            $count = $start+1;
            $_new = 0;
            foreach ($posts as $key => $post):
                $is_resolved = $post->is_resolved;
                $incident_array[] = (object) array(
                    'id'=> $count++,
                    '_id'=>(int) $post->id,
                    'name' =>$post->name,
                    'incident_code' =>$post->incident_code,
                     'is_resolved' =>(int) $post->is_resolved,
                    'status' =>$is_resolved== 1 ? "Resolved": "Pending",
                    'action_taken'=>'',
                    'days_ago'=>facebook_time_ago($post->created_on),
                    'description' =>$post->description,
                    'created_by' =>$post->created_by,
                    'created_on'=> $post->created_on
                );
            endforeach;
            $response = array(
                'status'=>TRUE,
                'count'=> $total_rows,
                'incidents'=> $incident_array
            );
        }else{
            $response = array(
                'status'=>TRUE,
                'count'=> 0,
                'incidents'=> array()
            );
        }
        $this->activity_log->logActivity($response,'',$user_id,$this->time_start);
        echo json_encode($response);
    }

    public function get_all_incidents(){
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $field_name = '';
        $status = 0;
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
            if(isset($update->status)){                    
                $status = $update->status;
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
                "is_resolved" => $status
            );       
        } 
        $user_id = $this->token_user->_id; 
        $total_rows = $this->incidents_m->count_all_filetered_active_incidents($filter_parameters);
        $pagination = create_custom_pagination('api',$total_rows,$page_size,$start,TRUE);
        $posts = $this->incidents_m->limit($pagination['limit'])->get_all_incidents($filter_parameters);
        $incident_array = [];
        if($posts){
            $count = $start+1;
            $_new = 0;
            $reported_by_ids = [];
            foreach ($posts as $key => $post) {
                $reported_by_ids[] = $post->reported_by;
            }
            $users_options = $this->users_m->get_user_array_options($reported_by_ids);
            foreach ($posts as $key => $post):
                $name = "";
                if(array_key_exists($post->reported_by, $users_options)){
                    $name = $users_options[$post->reported_by]->first_name .' '. $users_options[$post->reported_by]->last_name;
                }
                $is_resolved = $post->is_resolved;
                $incident_array[] = (object) array(
                    'id'=> $count++,
                    '_id'=>(int) $post->id,
                    'name' =>$post->name,
                    'incident_code' =>$post->incident_code,
                    'reported_by' => $name,
                    'is_resolved' =>(int) $post->is_resolved,
                    'status' =>$is_resolved== 1 ? "Resolved": "Pending",
                    'days_ago'=>facebook_time_ago($post->created_on),
                    'action_taken'=>'',
                    'description' =>$post->description,
                    'created_by' =>$post->created_by,
                    'created_on'=> $post->created_on
                );
            endforeach;
            $response = array(
                'status'=>TRUE,
                'count'=> $total_rows,
                'incidents'=> $incident_array
            );
        }else{
            $response = array(
                'status'=>TRUE,
                'count'=> 0,
                'incidents'=> array()
            );
        }
        $this->activity_log->logActivity($response,'',$user_id,$this->time_start);
        echo json_encode($response);
    }
}