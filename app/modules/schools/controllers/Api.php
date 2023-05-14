<?php if(!defined('BASEPATH')) exit('You are not allowed to view this script');
class Api extends Api_Controller{

    public $response = array(
        'result_code' => 0,
        'result_description' => 'Default Response'
    );

    public $time_start;

    function __construct(){
        parent::__construct();
        $this->load->model('schools_m');
        $this->load->model('teachers/teachers_m');
        $this->load->model('students/students_m');
        $this->load->library('schools_manager');
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

    public function create_school(){
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
                'label' =>  'School name',
                'rules' =>  'trim|required|callback__slug_is_unique',
            ),
            array(
                'field' =>  'description',
                'label' =>  'Description',
                'rules' =>  'strip_tags|trim',
            )
        );        
        $name = $this->input->post('name');
        $slug = generate_slug($this->input->post('name'));
        $description = $this->input->post('description');
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            $school = array(
              'name'=>$name,
              'slug'=>generate_slug($name),
              'description'=>$description,
              'user_id'=>$this->token_user->_id,
              'active'=>1,
              'created_on'=>time(),
              'created_by'=>$this->token_user->_id,
            );
            if($school_id = $this->schools_manager->create_school($school)){
                $response = array(
                    'status' => 1,
                    'message' => 'You have sucessfully created a school '.$name
                );                
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'Could not create school: '.$this->session->warning,
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

    public function update_school(){
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
                'label' =>  'School name',
                'rules' =>  'trim|required|callback__slug_is_unique',
            ),
            array(
                'field' =>  '_id',
                'label' =>  'School id',
                'rules' =>  'trim|required|numeric',
            ),
            array(
                'field' =>  'description',
                'label' =>  'Description',
                'rules' =>  'strip_tags|trim',
            )
        );        
        $name = $this->input->post('name');
        $slug = generate_slug($this->input->post('name'));
        $id = $this->input->post('_id');
        $description = $this->input->post('description');
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            $school = array(
              'name'=>$name,
              'school_id'=>$id,
              'slug'=>generate_slug($name),
              'description'=>$description,
              'user_id'=>$this->token_user->_id,
              'active'=>1,
              'modified_on'=>time(),
              'modified_by'=>$this->token_user->_id,
            );
            if($school_id = $this->schools_manager->update_school($school)){
                $response = array(
                    'status' => 1,
                    'message' => 'You have sucessfully updated a school '.$name
                );                
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'Could not update the school: '.$this->session->warning,
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

    public function get_all_schools(){        
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $field_name = '';
        $sort_order = '';
        $sort_field = '';
        $fetch_all = '';
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
            $start = $page_number * $page_size;
            $end = $start + $page_size;

            if($fetch_all == TRUE){ 
            }else{
                $filter_parameters = array(
                    "search_fields"=>$search_field,
                    "sort_order"=>$sort_order,
                    "sort_field"=>$sort_field,
                );
            }      
        } 
        $total_rows = $this->schools_m->count_all_filetered_active_schools($filter_parameters);
        $pagination = create_custom_pagination('api',$total_rows,$page_size,$start,TRUE);
        $posts = $this->schools_m->limit($pagination['limit'])->get_active_schools($filter_parameters);
        $schools_array = array();
        if($posts){
            $count = $start+1;
            $users_ids = array();
            foreach ($posts as $key => $post) {
                $users_ids[] = $post->id;
            }
            $users_array = $this->users_m->get_user_array_options($users_ids);
            foreach ($posts as $key => $post) {
                $first_name = isset($users_array[$post->id])?$users_array[$post->id]->first_name:'';
                $last_name = isset($users_array[$post->id])?$users_array[$post->id]->last_name:'';
                $schools_array[] = (object) array(
                    'id'=> $count++,
                    '_id'=>$post->id,
                    'name'=>$post->name,
                    'teacher'=>$first_name .' '.$last_name,
                    'description'=>$post->description,
                    'created_on'=>$post->created_on
                );
            }
            $response = array(
                'totalCount'=>$total_rows,
                'items'=> $schools_array
            );
        }else{
            $response = array(
                'totalCount'=>$total_rows,
                'items'=> []
            );
        }
        echo json_encode($response);
    }

    public function get_my_schools(){        
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
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
            if(isset($update->options->fetchAll)){
                $fetch_all = $update->options->fetchAll;
            }
            $start = $page_number * $page_size;
            $end = $start + $page_size;
            if($fetch_all){
                
            }else{
                $filter_parameters = array(
                    "search_fields"=>$search_field,
                    "sort_order"=>$sort_order,
                    "sort_field"=>$sort_field,
                ); 
            }      
        } 
        $total_rows = $this->schools_m->count_schools_by_user_id($this->token_user->_id,$filter_parameters);
        $pagination = create_custom_pagination('api',$total_rows,$page_size,$start,TRUE);
        $posts = $this->schools_m->limit($pagination['limit'])->get_schools_by_user_id($this->token_user->_id,$filter_parameters);
        $schools_array = array();
        if($posts){
            $count = $start+1;
            $users_ids = array();
            foreach ($posts as $key => $post) {
                $users_ids[] = $post->id;
            }
            $users_array = $this->users_m->get_user_array_options($users_ids);
            foreach ($posts as $key => $post) {
                $first_name = isset($users_array[$post->id])?$users_array[$post->id]->first_name:'';
                $last_name = isset($users_array[$post->id])?$users_array[$post->id]->last_name:'';
                $schools_array[] = (object) array(
                    'id'=> $count++,
                    '_id'=>$post->id,
                    'name'=>$post->name,
                    'teacher'=>$first_name .' '.$last_name,
                    'description'=>$post->description,
                    'created_on'=>$post->created_on
                );
            }
            $response = array(
                'totalCount'=>$total_rows,
                'items'=> $schools_array
            );
        }else{
            $response = array(
                'totalCount'=>$total_rows,
                'items'=> []
            );
        }
        echo json_encode($response);
    }

    public function get_my_school(){
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $school_id = $this->input->post('_id');
        if($school_id){
            if($this->user->id){
                $school = $this->schools_m->get_my_school($school_id,$this->user->id);
                if($school){
                    $school_details = (object)array(
                        'id'=> 1,
                        '_id'=>$school->id,
                        'name'=>$school->name,
                        'teacher'=>$this->user->first_name .' '.$this->user->last_name,
                        'description'=>$school->description,
                        'created_on'=>$school->created_on,
                        'modified_on'=>$school->modified_on,
                    );
                    $response = array(
                        'status' => 1,
                        'message' =>"Success",
                        'data'=>$school_details,
                    );
                }else{
                    $response = (object)array(
                        'status' => 0,
                        'message' =>"Success",
                        'data'=>[]
                    ); 
                }
            }else{
                $response = (object)array(
                    'status' => 0,
                    'message' =>"Success",
                    'data'=>[]
                );
            }
        }else{
            $response = array(
                'status' => 0,
                'message' => 'School id variable is not sent in JSON Payload',
            );
        }
        echo json_encode($response);
    }

    public function delete_my_school(){
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
                'field' =>  '_id',
                'label' =>  'School id',
                'rules' =>  'required',
            ),            
        ); 
        $school_id = $this->input->post('_id');
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            if($school = $this->schools_m->get($school_id)){
                $input = array(
                    'active' => 0,
                    'modified_on' => time(),
                    'modified_by'=> $this->token_user->_id,
                );                          
                $update = $this->schools_m->update($school->id, $input);
                if($update){
                    $response = array(
                        'status' => 1,
                        'message' =>'Success',
                    );
                }else{
                    $response = array(
                        'status' => 0,
                        'message' => "Could not delete the school",
                    );
                }
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'School details not found',
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

    public function create_class(){
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
                'label' =>  'Class name',
                'rules' =>  'trim|required|callback__class_slug_is_unique',
            ),
            array(
                'field' =>  'school_id',
                'label' =>  'School id',
                'rules' =>  'trim|required|numeric',
            ),
            array(
                'field' =>  'description',
                'label' =>  'Description',
                'rules' =>  'strip_tags|trim',
            )
        );        
        $name = $this->input->post('name');
        $slug = generate_slug($this->input->post('name'));
        $description = $this->input->post('description');
        $school_id = $this->input->post('school_id');
        $education_levels = $this->input->post('education_levels');
        
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            $class = array(
              'name'=>$name,
              'school_id'=>$school_id,
              'slug'=>generate_slug($name),
              'description'=>$description,
              'education_level_ids'=> serialize($education_levels),
              'user_id'=>$this->user->id,
              'active'=>1,
              'created_on'=>time(),
              'created_by'=>$this->user->id,
            );
            if($class_id = $this->schools_manager->create_class($class)){
                $response = array(
                    'status' => 1,
                    'message' => 'You have sucessfully created a class '.$name
                );                
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'Could not create class: '.$this->session->warning,
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

    public function update_class(){
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
                'label' =>  'Class name',
                'rules' =>  'trim|required|callback__class_slug_is_unique',
            ),
            array(
                'field' =>  '_id',
                'label' =>  'Class id',
                'rules' =>  'trim|required|numeric',
            ),
            array(
                'field' =>  'school_id',
                'label' =>  'School id',
                'rules' =>  'trim|required|numeric',
            ),
            array(
                'field' =>  'description',
                'label' =>  'Description',
                'rules' =>  'strip_tags|trim',
            )
        );       
        $name = $this->input->post('name');
        $slug = generate_slug($this->input->post('name'));
        $id = $this->input->post('_id');
        $school_id = $this->input->post('school_id');
        $description = $this->input->post('description');
        $education_levels = $this->input->post('education_levels');
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            $class = array(
              'class_id'=>$id,
              'name'=>$name,
              'school_id'=>$school_id,
              'slug'=>generate_slug($name),
              'education_level_ids'=> serialize($education_levels),
              'description'=>$description,
              'user_id'=>$this->token_user->_id,
              'active'=>1,
              'modified_on'=>time(),
              'modified_by'=>$this->token_user->_id,
            );
            if($result = $this->schools_manager->update_class($class)){
                $response = array(
                    'status' => 1,
                    'message' => 'You have sucessfully updated a class '.$name
                );                
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'Could not update a class: '.$this->session->warning,
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

    public function get_classes(){
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
                'field' =>  'school_id',
                'label' =>  'School id',
                'rules' =>  'trim|required|numeric',
            )
        );
        $school_id = $this->input->post('school_id');
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            $classes = $this->schools_m->get_classes_by_school_id($school_id);
            $school = $this->schools_m->get($school_id);
            $school_details = (object)array(
                '_id'=>$school->id,
                'name'=>$school->name,
                'description'=>$school->description,
                'created_on' =>$school->created_on,
            );
            if($classes){
                $class_array = array();
                $count = 1;
                foreach ($classes as $key => $class):
                    $subject_ids = $this->schools_m->get_subjects_by_class_ids_array($class->id);
                    $class_array[] = (object)array(
                        'id'=>$count++,
                        '_id'=>$class->id,
                        'name'=>$class->name,
                        'education_levels'=>unserialize($class->education_level_ids),
                        'subjects'=>count($subject_ids),
                        'students'=>$this->students_m->count_students_in_a_class_by_subject_ids($subject_ids),
                        'teachers'=>2,
                        'description'=>$class->description,
                        'created_on' =>$class->created_on,
                        'school_id'=>$class->school_id,
                        
                    );
                endforeach;               
                $response = array(
                    'totalCount'=>count($class_array),
                    'school'=>$school_details,
                    'classes'=> $class_array,
                );
            }else{
                $response = array(
                    'totalCount'=>0,
                    'school'=>$school_details,
                    'classes'=> []
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

    public function get_class(){
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $class_id = $this->input->post('_id');
        if($class_id){            
            if($this->user->id){
                $class = $this->schools_m->get_my_class($class_id,$this->user->id);
                if($class){
                    $class_details = (object)array(
                        'id'=> 1,
                        '_id'=>$class->id,
                        'school_id'=>$class->school_id,
                        'name'=>$class->name,
                        'description'=>$class->description,
                        'created_on'=>$class->created_on,
                        'modified_on'=>$class->modified_on,
                        'education_levels'=>unserialize($class->education_level_ids),
                    );
                    $response = array(
                        'status' => 1,
                        'message' =>"Success",
                        'data'=>$class_details,
                    );
                }else{
                    $response = (object)array(
                        'status' => 0,
                        'message' =>"Success",
                        'data'=>[]
                    ); 
                }
            }else{
                $response = (object)array(
                    'status' => 0,
                    'message' =>"Success",
                    'data'=>[]
                );
            }
        }else{
            $response = array(
                'status' => 0,
                'message' => 'Class_id id variable is not sent in JSON Payload',
            );
        }
        echo json_encode($response);
    }

    public function get_my_class_by_school_ids(){
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
                'field' =>  'school_ids[]',
                'label' =>  'School ids array',
                'rules' =>  'trim|required',
            )
        );
        $school_ids = $this->input->post('school_ids');
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            $classes = $this->schools_m->get_classes_by_school_ids_array($school_ids);
            $schools = $this->schools_m->get_school_by_ids($school_ids);
            $school_details = array();
            if(!empty($schools)){
                foreach ($schools as $key => $school):
                    $school_details[$school->id] = (object)array(
                        '_id'=>$school->id,
                        'name'=>$school->name,
                        'description'=>$school->description,
                        'created_on' =>$school->created_on,
                    );
                endforeach;
            }
            if($classes){
                $class_array = array();
                $count = 1;
                foreach ($classes as $key => $class):
                    $class_array[] = (object)array(
                        'id'=>$count++,
                        '_id'=>$class->id,
                        'name'=>$class->name,
                        'school_details'=>isset($school_details[$class->school_id])?$school_details[$class->school_id]:object,
                        'subjects'=>3,
                        'students'=>30,
                        'teachers'=>2,
                        'description'=>$class->description,
                        'created_on' =>$class->created_on,
                        'school_id'=>$class->school_id,
                    );
                endforeach;               
                $response = array(
                    'totalCount'=>count($class_array),
                    //'school'=>$school_details,
                    'classes'=> $class_array
                );
            }else{
                $response = array(
                    'totalCount'=>0,
                    'school'=>$school_details,
                    'classes'=> []
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

    public function delete_class(){
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
                'field' =>  '_id',
                'label' =>  'Class id',
                'rules' =>  'required',
            ),            
        ); 
        $class_id = $this->input->post('_id');
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            if($class = $this->schools_m->get_class($class_id)){
                $input = array(
                    'active' => 0,
                    'modified_on' => time(),
                    'modified_by'=> $this->token_user->_id,
                );                          
                $update = $this->schools_m->update_class($class->id, $input);
                if($update){
                    $response = array(
                        'status' => 1,
                        'message' =>'Success',
                    );
                }else{
                    $response = array(
                        'status' => 0,
                        'message' => "Could not delete the class",
                    );
                }
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'Class details not found',
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

    public function create_subject(){
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
                'label' =>  'Class name',
                'rules' =>  'trim|required',
            ),
            array(
                'field' =>  'school_id',
                'label' =>  'School id',
                'rules' =>  'trim|required|numeric',
            ),
            array(
                'field' =>  'class_id',
                'label' =>  'Class id',
                'rules' =>  'trim|required|numeric|callback__subject_slug_is_unique',
            ),
            array(
                'field' =>  'description',
                'label' =>  'Description',
                'rules' =>  'strip_tags|trim',
            )
        );        
        $name = $this->input->post('name');
        $slug = generate_slug($this->input->post('name'));
        $description = $this->input->post('description');
        $school_id = $this->input->post('school_id');
        $class_id = $this->input->post('class_id');
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            $subject = array(
              'name'=>$name,
              'school_id'=>$school_id,
              'class_id'=>$class_id,
              'slug'=>generate_slug($name),
              'description'=>$description,
              'user_id'=>$this->user->id,
              'active'=>1,
              'created_on'=>time(),
              'created_by'=>$this->user->id,
            );
            if($subject_id = $this->schools_manager->create_subject($subject)){
                $response = array(
                    'status' => 1,
                    'message' => 'You have sucessfully created a subject '.$name
                );                
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'Could not create class: '.$this->session->warning,
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

    public function get_my_subjects_by_class_ids(){        
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $class_ids = $this->input->post('class_ids');
        $school_ids = $this->input->post('school_ids');
        if(!empty($school_ids)){            
            if(!empty($class_ids)){            
                if($this->user->id){
                    $schools = $this->schools_m->get_school_by_ids($school_ids,$this->user->id);                   
                    if($schools){
                       $school_details = array();
                        if(!empty($schools)){
                            foreach ($schools as $key => $school):
                                $school_details[$school->id] = (object)array(
                                    '_id'=>$school->id,
                                    'name'=>$school->name,
                                    'description'=>$school->description,
                                    'created_on' =>$school->created_on,
                                );
                            endforeach;
                        }
                        $classes = $this->schools_m->get_my_class_by_ids($class_ids,$this->user->id);
                        if($classes){
                            $class_details = array();
                            foreach ($classes as $key => $class):
                                $class_details[$class->id] = (object)array(
                                    'id'=> 1,
                                    '_id'=>$class->id,
                                    'school_id'=>$class->school_id,
                                    'name'=>$class->name,
                                    'description'=>$class->description,
                                    'created_on'=>$class->created_on,
                                    'modified_on'=>$class->modified_on,
                                );
                            endforeach;
                            $subjects = $this->schools_m->get_school_class_subjects_ids_array($school_ids,$class_ids);
                            $subjects_array = array();
                            if($subjects){
                                $count = 1;
                                foreach ($subjects as $key => $subject):
                                    $subjects_array[] = (object)array(
                                        'id'=> $count++,
                                        '_id'=>$subject->id,
                                        'school_details'=>isset($school_details[$subject->school_id])?$school_details[$subject->school_id]:object,
                                        'school_id'=>$subject->school_id,
                                        'class_details'=>isset($class_details[$subject->class_id])?$class_details[$subject->class_id]:'',
                                        'class_id'=>$subject->class_id,
                                        'name'=>$subject->name,
                                        'resources'=>3,
                                        'students'=>30,
                                        'teachers'=>2,
                                        'description'=>$subject->description,
                                        'created_on'=>$subject->created_on,
                                        'modified_on'=>$subject->modified_on,
                                    );
                                endforeach;
                                $response = array(
                                    'status' => 1,
                                    'message' =>"Success",
                                    'subjects'=>$subjects_array
                                );
                            }else{
                               $response =array(
                                    'status' => 0,
                                    'message' =>"Success",
                                    'school'=>$school_details,
                                    'class'=>$class_details,
                                    'subjects'=>[]
                                ); 
                            }
                        }else{
                            $response =array(
                                'status' => 0,
                                'message' =>"Success",
                                'school'=>$school_details,
                                'class'=>[],
                                'subjects'=>[]
                            ); 
                        }
                    }else{
                        $response =array(
                            'status' => 0,
                            'message' =>"Success",
                            'school'=>[],
                            'class'=>[],
                            'subjects'=>[]
                        ); 
                    }
                }else{
                    $response = array(
                        'status' => 0,
                        'message' =>"Success",
                        'data'=>[]
                    );
                }
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'Class id variable is not sent in JSON Payload',
                );
            }
        }else{
           $response = array(
                'status' => 0,
                'message' => 'School id variable is not sent in JSON Payload',
            ); 
        }
        echo json_encode($response);
    }

    public function get_subjects(){
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $class_id = $this->input->post('class_id');
        $school_id = $this->input->post('school_id');
        if($school_id){            
            if($class_id){            
                if($this->user->id){
                    $school = $this->schools_m->get_my_school($school_id,$this->user->id);                   
                    if($school){
                        $school_details = (object)array(
                            'id'=> 1,
                            '_id'=>$school->id,
                            'name'=>$school->name,
                            'description'=>$school->description,
                            'created_on'=>$school->created_on,
                            'modified_on'=>$school->modified_on,
                        );
                        $class = $this->schools_m->get_my_class($class_id,$this->user->id);
                        if($class){
                            $class_details = (object)array(
                                'id'=> 1,
                                '_id'=>$class->id,
                                'school_id'=>$class->school_id,
                                'name'=>$class->name,
                                'description'=>$class->description,
                                'created_on'=>$class->created_on,
                                'modified_on'=>$class->modified_on,
                            );
                            $subjects = $this->schools_m->get_school_class_subjects($school_id,$class_id);
                            $subjects_array = array();
                            if($subjects){
                                $count = 1;
                                foreach ($subjects as $key => $subject):
                                    $subjects_array[] = (object)array(
                                        'id'=> $count++,
                                        '_id'=>$subject->id,
                                        'school_id'=>$subject->school_id,
                                        'class_id'=>$subject->class_id,
                                        'name'=>$subject->name,
                                        'resources'=>3,
                                        'students'=>30,
                                        'teachers'=>2,
                                        'description'=>$subject->description,
                                        'created_on'=>$subject->created_on,
                                        'modified_on'=>$subject->modified_on,
                                    );
                                endforeach;
                                $response = array(
                                    'status' => 1,
                                    'message' =>"Success",
                                    'school'=>$school_details,
                                    'class'=>$class_details,
                                    'subjects'=>$subjects_array
                                );
                            }else{
                               $response =array(
                                    'status' => 0,
                                    'message' =>"Success",
                                    'school'=>$school_details,
                                    'class'=>$class_details,
                                    'subjects'=>[]
                                ); 
                            }
                        }else{
                            $response =array(
                                'status' => 0,
                                'message' =>"Success",
                                'school'=>$school_details,
                                'class'=>[],
                                'subjects'=>[]
                            ); 
                        }
                    }else{
                        $response =array(
                            'status' => 0,
                            'message' =>"Success",
                            'school'=>[],
                            'class'=>[],
                            'subjects'=>[]
                        ); 
                    }
                }else{
                    $response = array(
                        'status' => 0,
                        'message' =>"Success",
                        'data'=>[]
                    );
                }
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'Class id variable is not sent in JSON Payload',
                );
            }
        }else{
           $response = array(
                'status' => 0,
                'message' => 'School id variable is not sent in JSON Payload',
            ); 
        }
        echo json_encode($response);
    }

    public function get_subject(){        
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $subject_id = $this->input->post('_id');
        if($subject_id){            
            if($this->user->id){
                $subject = $this->schools_m->get_subject($subject_id,$this->user->id);
                if($subject){
                    $subject_details = (object)array(
                        'id'=> 1,
                        '_id'=>$subject->id,
                        'school_id'=>$subject->school_id,
                        'class_id'=>$subject->class_id,
                        'name'=>$subject->name,
                        'description'=>$subject->description,
                        'created_on'=>$subject->created_on,
                        'modified_on'=>$subject->modified_on,
                    );
                    $response = array(
                        'status' => 1,
                        'message' =>"Success",
                        'data'=>$subject_details,
                    );
                }else{
                    $response = (object)array(
                        'status' => 0,
                        'message' =>"Success",
                        'data'=>[]
                    ); 
                }
            }else{
                $response = (object)array(
                    'status' => 0,
                    'message' =>"Success",
                    'data'=>[]
                );
            }
        }else{
            $response = array(
                'status' => 0,
                'message' => 'Subject id variable is not sent in JSON Payload',
            );
        }
        echo json_encode($response);
    }

    public function update_subject(){
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
                'label' =>  'Class name',
                'rules' =>  'trim|required|callback__subject_slug_is_unique',
            ),
            array(
                'field' =>  '_id',
                'label' =>  'subject id',
                'rules' =>  'trim|required|numeric',
            ),
             array(
                'field' =>  'school_id',
                'label' =>  'School id',
                'rules' =>  'trim|required|numeric',
            ),
            array(
                'field' =>  'class_id',
                'label' =>  'Class id',
                'rules' =>  'trim|required|numeric',
            ),
            array(
                'field' =>  'description',
                'label' =>  'Description',
                'rules' =>  'strip_tags|trim',
            )
        );       
        $name = $this->input->post('name');
        $slug = generate_slug($this->input->post('name'));
        $id = $this->input->post('_id');
        $school_id = $this->input->post('school_id');
        $class_id = $this->input->post('class_id');
        $description = $this->input->post('description');
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            $subject = array(
              'subject_id'=>$id,
              'name'=>$name,
              'school_id'=>$school_id,
              'class_id'=>$class_id,
              'slug'=>generate_slug($name),
              'description'=>$description,
              'user_id'=>$this->user->id,
              'active'=>1,
              'modified_on'=>time(),
              'modified_by'=>$this->user->id,
            );
            if($result = $this->schools_manager->update_subject($subject)){
                $response = array(
                    'status' => 1,
                    'message' => 'You have sucessfully updated a subject '.$name
                );                
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'Could not update a subject: '.$this->session->warning,
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

    public function delete_subject(){
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
                'field' =>  '_id',
                'label' =>  'Subject id',
                'rules' =>  'required',
            ),  
        ); 
        $subject_id = $this->input->post('_id');
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            if($subject = $this->schools_m->get_subject($subject_id , $this->user->id)){
                if($subject->user_id == $this->user->id){
                    $input = array(
                        'active' => 0,
                        'modified_on' => time(),
                        'modified_by'=> $this->token_user->_id,
                    );                          
                    $update = $this->schools_m->update_subject($subject->id, $input);
                    if($update){
                        $response = array(
                            'status' => 1,
                            'message' =>'Success',
                        );
                    }else{
                        $response = array(
                            'status' => 0,
                            'message' => "Could not delete the class",
                        );
                    }
                }else{
                    $response = array(
                        'status' => 0,
                        'message' => "You are not allowed to perform this action",
                    );
                }
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'Class details not found',
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

    public function get_my_students(){
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }   
        $field_name = '';
        $sort_order = '';
        $sort_field = '';
        $sort_role = 0;
        $search_field = '';
        $where_fields = '';
        $page_number = 0;
        $page_size = 10;
        $start = 0;
        $end = 10;
        $filter_user_ids = array();
        $filter_class_ids = array();
        $filter_school_ids = array();
        $filter_parameters = array();
        if (isset($this->filter_params)) {
            $update = $this->filter_params;
            if(isset($update->filter)){
                $search_field = $update->filter;
                if($search_field->name || $search_field->email){
                    $filter_user_ids = $this->users_m->get_filter_user_ids_students(array("search_fields"=>$update->filter));
                }
                if($search_field->school){
                    $filter_school_ids = $this->schools_m->get_filtered_school_ids(array("search_fields"=>$update->filter));
                }
                if($search_field->class){
                    $filter_class_ids = $this->schools_m->get_filtered_class_ids(array("search_fields"=>$update->filter));
                }
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
            if(isset($update->options)){
                $where_fields = $update->options;
            }
            $start = $page_number * $page_size;
            $end = $start + $page_size;
            $filter_parameters = array(
                "search_fields"=>$search_field,
                "sort_order"=>$sort_order,
                "where_fields"=>$where_fields,
                "sort_field"=>$sort_field,
            );       
        }
               
        if($this->user->id){
            $total_rows = $this->teachers_m->count_my_students_by_user_id($this->token_user->_id,$filter_parameters);            
            $pagination = create_custom_pagination('api',$total_rows,$page_size,$start,TRUE);
            $students = $this->teachers_m->limit($pagination['limit'])->get_my_students($this->user->id,$filter_parameters);
            if($students){
                $class_ids = array();
                $school_ids = array();
                $user_ids = array();               
                foreach ($students as $key => $student) {
                    if(empty($filter_user_ids)){
                        $user_ids[] = $student->user_id;
                    }else{
                       $user_ids = $filter_user_ids;  
                    }

                    if(empty($filter_school_ids)){
                        $school_ids[]  = $student->school_id;
                    }else{
                       $school_ids = $filter_school_ids;  
                    }

                    if(empty($filter_class_ids)){
                        $class_ids[]  = $student->class_id;
                    }else{
                       $class_ids = $filter_class_ids;  
                    }
                   
                }
                $users = $this->users_m->get_user_array_options($user_ids);
                $schools = $this->schools_m->get_school_by_id_options_array($school_ids);
                $classes = $this->schools_m->get_class_by_id_options_array($class_ids);
                $student_array = array();
                $count = 1;
                foreach ($students as $key => $student) {
                    $user = isset($users[$student->user_id])?$users[$student->user_id]:'';
                    $school = isset($schools[$student->school_id])?$schools[$student->school_id]->name:'';
                    $class = isset($classes[$student->class_id])?$classes[$student->class_id]->name:'';
                    if($user){
                        if($school){
                            if($class){
                                $student_array[] = array(
                                    'id'=>$count++,
                                    '_id'=>$student->id,
                                    'name'=> $user->first_name. ' '.$user->last_name,
                                    'email' => $user->email,
                                    'last_login'=>$user->last_login?$user->last_login:'Never',
                                    'is_accepted'=>$student->is_accepted?intval($student->is_accepted):0,
                                    'accepted_on'=>$student->accepted_on,
                                    'school' =>$school,
                                    'class' =>$class,

                                );
                            }
                        }
                    }
                }
                $response = array(
                    'itemCount' =>count($student_array),
                    'items'=>$student_array,
                );
            }else{
                $response = (object)array(
                    'itemCount' => 0,
                    'items' =>array()
                ); 
            }
        }else{
            $response = (object)array(
                'itemCount' => 0,
                'items' =>array()
            );  
        }
        echo json_encode($response);
    }

    public function delete_student(){
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
                'field' =>  '_id',
                'label' =>  'student id',
                'rules' =>  'required',
            ),            
        ); 
        $student_id = $this->input->post('_id');
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            if($student = $this->teachers_m->get($class_id)){
                $input = array(
                    'active' => 0,
                    'modified_on' => time(),
                    'modified_by'=> $this->token_user->_id,
                );                          
                $update = $this->teachers_m->update($student->id, $input);
                if($update){
                    $response = array(
                        'status' => 1,
                        'message' =>'Success',
                    );
                }else{
                    $response = array(
                        'status' => 0,
                        'message' => "Could not delete the student",
                    );
                }
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'student details not found',
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

    public function get_schools_options(){
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $posts = $this->schools_m->get_school_options();
        if($posts){ 
            $count = 0;
            $arr = array();
            foreach ($posts as $key => $post) {
                $post = (object) $post;
                $arr[] = array(
                    'id'=>++$count,
                    '_id'=>$post->id,
                    'name'=>$post->name
                );
            }
            $response = array(
                'totalCount'=>count($arr),
                'items'=>$arr
            );
        }else{
           $response = array(
                'totalCount'=>0,
                'items'=>[]
            ); 
        }
        echo json_encode($response);
    }

    function _slug_is_unique(){
        $slug = generate_slug($this->input->post('name'));
        $user_id = $this->token_user->_id;
        $name = $this->input->post('name');
        $id = $this->input->post('_id');
        if($user_group = $this->schools_m->get_school_by_slug($slug,$user_id)){
            if($user_group->id == $id){
                return TRUE;
            }else{
                $this->form_validation->set_message('_slug_is_unique','The school '.$name.' already exists.');
                return FALSE;
            }
        }else{
            return TRUE;
        }
    }

    function _class_slug_is_unique(){
        $slug = generate_slug($this->input->post('name'));
        $user_id = $this->token_user->_id;
        $school_id = $this->input->post('school_id');
        $name = $this->input->post('name');
        $id = $this->input->post('_id');
        if($class = $this->schools_m->get_class_by_slug($slug,$school_id)){
            if($class->id == $id){
                return TRUE;
            }else{
                $this->form_validation->set_message('_class_slug_is_unique','The class '.$name.' already exists.');
                return FALSE;
            }
        }else{
            return TRUE;
        }
    }

    function _subject_slug_is_unique(){
        $slug = generate_slug($this->input->post('name'));
        $user_id = $this->token_user->_id;
        $school_id = $this->input->post('school_id');
        $class_id = $this->input->post('class_id');
        $name = $this->input->post('name');
        $id = $this->input->post('_id');
        if($subject = $this->schools_m->get_subject_by_slug($slug,$class_id)){
            if($subject->id == $id){
                return TRUE;
            }else{
                $this->form_validation->set_message('_subject_slug_is_unique','The subject '.$name.' already exists in this class.');
                return FALSE;
            }
        }else{
            return TRUE;
        }
    }

}