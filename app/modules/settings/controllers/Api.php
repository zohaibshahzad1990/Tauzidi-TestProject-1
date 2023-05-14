<?php if(!defined('BASEPATH')) exit('You are not allowed to view this script');
class Api extends Api_Controller{

    public $response = array(
        'result_code' => 0,
        'result_description' => 'Default Response'
    );

    function __construct(){
        parent::__construct();
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

    public function currency_options(){
        $currencies = $this->countries_m->get_currency_options();
        $curreny_options = array();
        foreach ($currencies as $key => $value) {
            $curreny_options[] = array('id'=>$key,'name'=>$value);
        }
        $response = array(
            'status' => 1,
            'currencies' => $curreny_options,
        );
        echo json_encode($response);
    }

    public function create_school_settings(){
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
                'field' =>  'schools',
                'label' =>  'Number of school',
                'rules' =>  'trim|required|numeric|callback__check_if_resource_settings_exist',
            ),
            array(
                'field' =>  'classes',
                'label' =>  'Number of classes',
                'rules' =>  'trim|required|numeric',
            ),array(
                'field' =>  'subjects',
                'label' =>  'Number of subjects',
                'rules' =>  'trim|required|numeric',
            ),
        );        
        $schools = $this->input->post('schools');
        $classes = $this->input->post('classes');
        $subjects = $this->input->post('subjects');
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            $input = array(
                'schools'=>$schools,
                'classes'=>$classes,
                'subjects'=>$subjects,
                'active'=>1,
                'user_id'=>$this->user->id,
                'created_on'=>time(),
                'created_by'=>$this->user->id,
            );
            if($this->settings_m->insert_resource_settings($input)){
                $response = array(
                    'status' => 1,
                    'message' => 'The resource settings has been created successfully'
                );
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'The resource settings could not be created try again'
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

    public function update_school_settings(){
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
                'field' =>  'schools',
                'label' =>  'Number of school',
                'rules' =>  'trim|required|numeric|callback__check_if_resource_settings_exist',
            ),
            array(
                'field' =>  'classes',
                'label' =>  'Number of classes',
                'rules' =>  'trim|required|numeric',
            ),array(
                'field' =>  'subjects',
                'label' =>  'Number of subjects',
                'rules' =>  'trim|required|numeric',
            ),
        );        
        $schools = $this->input->post('schools');
        $classes = $this->input->post('classes');
        $subjects = $this->input->post('subjects');
        $id = $this->input->post('_id');
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            if($resource_settings = $this->settings_m->get_resource_settings()){
                $input = array(
                    'schools'=>$schools,
                    'classes'=>$classes,
                    'subjects'=>$subjects,
                    'active'=>1,
                    'user_id'=>$this->user->id,
                    'modified_on'=>time(),
                    'modified_by'=>$this->user->id,
                );
                if($this->settings_m->update_resource_settings($resource_settings->id,$input)){
                    $response = array(
                        'status' => 1,
                        'message' => 'The resource settings has been updated successfully'
                    );
                }else{
                    $response = array(
                        'status' => 0,
                        'message' => 'The resource settings could not be updated try again'
                    ); 
                }
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'The resource settings details not found'
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

    public function get_school_settings(){
        if($resource_settings = $this->settings_m->get_resource_settings()){
            $resource_setting = array(
                'id'=>1,
                '_id'=>$resource_settings->id,
                'schools'=> intval($resource_settings->schools),
                'classes'=>intval($resource_settings->classes),
                'subjects'=>intval($resource_settings->subjects),
                'active'=>1,
                'user_id'=>intval($resource_settings->user_id),
                'modified_on'=>$resource_settings->modified_on,
                'created_on'=>$resource_settings->created_on,
            );
            $response = array(
                'itemCount' => 1,
                'items' => $resource_setting
            );
        }else{
            $response = array(
                'status' => 0,
                'message' => 'The resource settings details not found'
            );  
        }
        echo json_encode($response);
    }

    public function create_syllabus(){
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
                'label' =>  'Number of syllabus',
                'rules' =>  'trim|required|callback__check_if_syallabus_exist',
            )
        );        
        $name = $this->input->post('name');
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            $input = array(
                'name'=>$name,
                'slug'=>generate_slug($name),
                'active'=>1,
                'user_id'=>$this->user->id,
                'created_on'=>time(),
                'created_by'=>$this->user->id,
            );
            if($this->settings_m->insert_syllabus($input)){
                $response = array(
                    'status' => 1,
                    'message' => 'The syllabus has been created successfully'
                );
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'The syllabus could not be created try again'
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

    public function update_syllabus(){
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
                'label' =>  'Syllabus id',
                'rules' =>  'xss_clean|strip_tags|required',
            ),
            array(
                'field' =>  'name',
                'label' =>  'Number of syllabus',
                'rules' =>  'trim|required|callback__check_if_syallabus_exist',
            )
        );        
        $name = $this->input->post('name');
        $slug = generate_slug($name);
        $user_id = $this->token_user->_id;
        $_id = $this->input->post('_id');
        $parent_id= $this->input->post('parent_id')?$this->input->post('parent_id'):0;
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            $post = $this->settings_m->get_syllabus($_id);
            if($post){
                $input = array( 
                    'name'=>$name, 
                    'slug'=> generate_slug($name),                    
                    'active'=>1,
                    'user_id'=>$user_id,
                    'modified_on'=>time(),
                    'modified_by'=>$this->user->id,
                );
                if($this->settings_m->update_syllabus($_id,$input)){
                    $response = array(
                        'status' => 1,
                        'message' => 'The syallabus been updated successfully'
                    );
                }else{
                    $response = array(
                        'status' => 0,
                        'message' => 'The syallabus  could not be updated try again'
                    ); 
                }
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'The syallabus details could not be found'
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

    public function update_syllabus_bulk(){
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
                'field' =>  'syllabus[]',
                'label' =>  'Syllabus names',
                'rules' =>  'xss_clean|strip_tags',
            )
        );
        //$this->form_validation->set_rules($validation_rules);
        //$syllabus = $this->input->post('syllabus');
        $user_id = $this->token_user->_id;
        if($_POST['syllabus']){
            $posts = $this->settings_m->get_syllabus_by_slug_array();
            $syllabus = $_POST['syllabus'];
            $new_syllabus_arr = [];            
            foreach ($syllabus as $key => $syllabal) {
                if(array_key_exists(generate_slug($syllabal->name) , $posts)){
                    $input = array( 
                        'name'=>$syllabal->name, 
                        'slug'=> generate_slug($syllabal->name),                    
                        'active'=>1,
                        'user_id'=>$user_id,
                        'modified_on'=>time(),
                        'modified_by'=>$this->user->id,
                    );
                    $_id = $syllabal->id;
                    $this->settings_m->update_syllabus($_id,$input);
                }else{
                    $new_syllabus_arr[] = [
                        'name'=>$syllabal->name, 
                        'slug'=> generate_slug($syllabal->name),                    
                        'active'=>1,
                        'user_id'=>$user_id,
                        'created_on'=>time(),
                        'created_by'=>$this->user->id,
                    ];
                }
            }
            if(sizeof($new_syllabus_arr) > 0){
                $this->settings_m->insert_chunked_batch_secure_data($new_syllabus_arr);
            }
            $response = array(
                'status' => 1,
                'message' => 'The syallabus been updated successfully'
            );
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

    public function get_syllabus(){
        if($syllabuses = $this->settings_m->get_syllabus_settings()){
            $syllabus_array = array();
            $count = 1;
            foreach ($syllabuses as $key => $val):
                $syllabus_array[] = array(
                    'id'=>$count++,
                    '_id'=>$val->id,
                    'name'=> ucwords($val->name),
                    'slug'=>generate_slug($val->name),
                    'active'=>1,
                    'modified_on'=>$val->modified_on,
                    'created_on'=>$val->created_on
                );
            endforeach; 
            $response = array(
                'itemCount' => count($syllabus_array),
                'items' => $syllabus_array
            );
        }else{
            $response = array(
                'status' => 0,
                'message' => 'The resource settings details not found'
            );  
        }
        echo json_encode($response);
    }


    public function create_education_level(){
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
                'label' =>  'Education level name',
                'rules' =>  'xss_clean|strip_tags|required|callback__check_if_resource_settings_exist',
            ),
            array(
                'field' =>  'syllabus_level_id',
                'label' =>  'Syllabus Level',
                'rules' =>  'trim|required|numeric',
            )
        );        
        $name = $this->input->post('name');
        $syllabus_level_id = $this->input->post('syllabus_level_id');
        $slug = generate_slug($name);
        $user_id = $this->token_user->_id;       
        $classes = $this->input->post('classes');
        $subjects = $this->input->post('subjects');
        $parent_id= $this->input->post('parent_id')?$this->input->post('parent_id'):0;
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            $resource = $this->settings_m->get_resource_settings();
            $input = array( 
                'name'=>$name, 
                'slug'=> generate_slug($name),                      
                'active'=>1,
                'syllabus_level_id'=>$syllabus_level_id,
                'user_id'=>$user_id,
                'parent_id'=>$parent_id,
                'created_on'=>time(),
                'created_by'=>$this->user->id,
            );
            if($this->settings_m->insert_education_level($input)){
                $response = array(
                    'status' => 1,
                    'message' => 'The education levels has been created successfully'
                );
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'The education levels  could not be created try again'
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

    public function update_education_level(){
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
                'label' =>  'Education level id',
                'rules' =>  'xss_clean|strip_tags|required',
            ),
            array(
                'field' =>  'name',
                'label' =>  'Education level name',
                'rules' =>  'xss_clean|strip_tags|required|callback__check_if_resource_settings_exist',
            ),
            array(
                'field' =>  'syllabus_level_id',
                'label' =>  'Syllabus Level',
                'rules' =>  'trim|required|numeric',
            ),
            array(
                'field' =>  'parent_id',
                'label' =>  'Education Parent id',
                'rules' =>  'xss_clean|strip_tags',
            )
        );        
        $name = $this->input->post('name');
        $slug = generate_slug($name);
        $user_id = $this->token_user->_id;
        $_id = $this->input->post('_id');
        $parent_id= $this->input->post('parent_id')?$this->input->post('parent_id'):0;
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            $post = $this->settings_m->get_education_level($_id);
            if($post){
                $input = array( 
                    'name'=>$name, 
                    'slug'=> generate_slug($name),
                    'syllabus_level_id'=>$syllabus_level_id,                      
                    'active'=>1,
                    'user_id'=>$user_id,
                    'parent_id'=>$parent_id,
                    'modified_on'=>time(),
                    'modified_by'=>$this->user->id,
                );
                if($this->settings_m->update_education_level($_id,$input)){
                    $response = array(
                        'status' => 1,
                        'message' => 'The education levels has been updated successfully'
                    );
                }else{
                    $response = array(
                        'status' => 0,
                        'message' => 'The education levels  could not be updated try again'
                    ); 
                }
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'The education levels details could not be found'
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

    public function update_education_levels(){
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
                'field' =>  'education_levels[]',
                'label' =>  'Education level name',
                'rules' =>  'xss_clean|strip_tags|required',
            ),
            array(
                'field' =>  'syllabus_id',
                'label' =>  'Syllabus level id',
                'rules' =>  'xss_clean|strip_tags|required',
            )
        );

        $education_levels = $_POST['education_levels'];
        $user_id = $this->user->id;
        $syllabus_id = $this->input->post("syllabus_id");
        if($education_levels){
            $posts = $this->settings_m->get_education_level_slug_by_syllabus_id_array($syllabus_id);    

            $new_education_level = array();
            $new_education_level_slugs = [];
            $deleted_ids = [];
            foreach ($education_levels as $key => $education) {
                $new_education_level_slugs[generate_slug($education->name)] = $education->id;
            }

            foreach ($posts as $key => $post) {
                if(array_key_exists($key,$education_levels)){

                }else{
                    $deleted_ids[] = $post;
                }
            }
            $new_syllabus_arr = [];
            foreach ($education_levels as $key => $level) {
                if(array_key_exists(generate_slug($level->name) , $posts)){
                    $input = array( 
                        'name'=>$level->name, 
                        'slug'=> generate_slug($level->name),
                        'syllabus_level_id'=>$syllabus_id,                      
                        'active'=>1,
                        'user_id'=>$user_id,
                        'modified_on'=>time(),
                        'modified_by'=>$this->user->id,
                    );
                    $_id = $level->id;
                    $this->settings_m->update_education_level($_id,$input);
                }else{
                    $new_syllabus_arr[] = [
                        'name'=>$level->name, 
                        'slug'=> generate_slug($level->name),
                        'syllabus_level_id'=>$syllabus_id,                   
                        'active'=>1,
                        'user_id'=>$user_id,
                        'created_on'=>time(),
                        'created_by'=>$this->user->id,
                    ];
                }
            }

            if(sizeof($new_syllabus_arr) > 0){
                if($this->settings_m->insert_education_level_batch($new_syllabus_arr)){
                    
                }
            }

            if(sizeof($deleted_ids) > 0){
                $this->settings_m->void_education_levels($deleted_ids);
            }            

            $response = array(
                'status' => 1,
                'message' => 'Education level updated successfully'
            );
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

    public function get_education_levels(){
        if($education_levels = $this->settings_m->get_parent_education_level_settings()){
            $education_levels_array = array();
            $count = 1;
            $parent_ids = array();
            foreach ($education_levels as $key => $education_level):
                $parent_ids[] = $education_level->id;
            endforeach;
            $child_levels = $this->settings_m->get_children_education_level_settings($parent_ids);
            //print_r($child_levels); die();
            foreach ($education_levels as $key => $val):
                $education_levels_array[] = array(
                    'id'=>$count++,
                    '_id'=>$val->id,
                    'name'=> ucwords($val->name),
                    'slug'=>generate_slug($val->name),
                    'parent_id' => $val->parent_id,
                    'active'=>1,
                    'user_id'=>intval($val->user_id),
                    'modified_on'=>$val->modified_on,
                    'created_on'=>$val->created_on,
                    'sub_levels'=> isset($child_levels[$val->id])?$child_levels[$val->id]:array()
                );
            endforeach; 
            $response = array(
                'itemCount' => count($education_levels_array),
                'items' => $education_levels_array
            );
        }else{
            $response = array(
                'status' => 0,
                'message' => 'The resource settings details not found'
            );  
        }
        echo json_encode($response);
    }

    public function create_exam_category(){
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
                'label' =>  'Exam category name',
                'rules' =>  'xss_clean|strip_tags|required|callback__check_if_exam_category_exist',
            )
        );        
        $name = $this->input->post('name');
        $slug = generate_slug($name);
        $user_id = $this->token_user->_id; 
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            $input = array( 
                'name'=>ucwords($name), 
                'slug'=>$slug,                        
                'active'=>1,
                'user_id'=>$user_id,
                'created_on'=>time(),
                'created_by'=>$this->user->id,
            );
            if($this->settings_m->insert_exam_category($input)){
                $response = array(
                    'status' => 1,
                    'message' => 'Exam category created successfully'
                );
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'Exam category could not be created try again'
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

    public function update_exams_categories_settings(){
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
                'field' =>  'exams_categories[]',
                'label' =>  'Exam category name',
                'rules' =>  'strip_tags|required',
            )
        );        
        $exams_categories = $this->input->post('exams_categories');
        $id = $this->input->post('_id');
        //$slug = generate_slug($name);
        $user_id = $this->token_user->_id; 
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            $slug_array = array();
            $all_categories_slugs = array();
            foreach ($exams_categories as $key => $val) {
                $all_categories_slugs[generate_slug($val)] = $val; 
                $slug_array[] = generate_slug($val);
            }
            $posts = $this->settings_m->get_exam_categories_by_slug_array($slug_array);
            $update_array = array();
            $input_array = array();
            $category_ids = array();
            if($posts){                
                $fliped_posts = array_flip($posts);              
                foreach ($slug_array as $key => $slug):
                    if(array_key_exists($slug, $fliped_posts)){
                        $id = $fliped_posts[$slug];
                        $category_ids[] = $id;
                        $update_array[$id] = $slug;
                    }else{
                        $input_array[] = $slug;
                    }
               endforeach;
            }
            if(!empty($update_array)){
                foreach ($update_array as $key => $update) {
                    $name = isset($all_categories_slugs[$update])?$all_categories_slugs[$update]:'';
                    $input = array( 
                        'name'=>ucwords($name), 
                        'slug'=>generate_slug($name),                        
                        'active'=>1,
                        'user_id'=>$user_id,
                        'modified_on'=>time(),
                        'modified_by'=>$this->user->id,
                    );                    
                    if($this->settings_m->update_exam_category($key,$input)){                        
                        $response = array(
                            'status' => 1,
                            'message' => 'Exam category updated successfully'
                        );
                    }
                }
            }
            if(!empty($category_ids)){
                $this->settings_m->void_exam_categories($category_ids);
            }
            $create_array = array();
            if(!empty($input_array)){
                foreach ($input_array as $key => $update) {
                    $name = isset($all_categories_slugs[$update])?$all_categories_slugs[$update]:'';
                    $create_array[] = array( 
                        'name'=>ucwords($name),
                        'slug'=>generate_slug($name),                        
                        'active'=>1,
                        'user_id'=>$user_id,
                        'modified_on'=>time(),
                        'modified_by'=>$this->user->id,
                    );                 
                }
                if($this->settings_m->insert_exam_category_batch($create_array)){
                    $response = array(
                        'status' => 1,
                        'message' => 'Exam category updated successfully'
                    );
                }
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

    public function get_exams_categories_settings(){        
        if($exam_categories = $this->settings_m->get_exam_categories()){
            $exam_category_array = array();
            $count = 1;
            foreach ($exam_categories as $key => $category):
                $exam_category_array[] = array(
                    'id'=>$count++,
                    '_id'=>$category->id,
                    'name'=> $category->name,
                    'slug'=>$category->slug,
                    'active'=>1,
                    'user_id'=>intval($category->user_id),
                    'modified_on'=>$category->modified_on,
                    'created_on'=>$category->created_on,
                );
            endforeach; 
            $response = array(
                'itemCount' => count($exam_category_array),
                'items' => $exam_category_array
            );
        }else{
            $response = array(
                'itemCount' => 0,
                'items' => array()
            );  
        }
        echo json_encode($response);
    }

    public function _check_if_syallabus_exist(){
        $id = $this->input->post('_id');
        $slug = generate_slug($this->input->post('name'));
        if($syllabus = $this->settings_m->get_syllabus_by_slug($slug)){
            if($syllabus->id == $id){
                return TRUE;
            }else{
                $this->form_validation->set_message('_check_if_syallabus_exist','The syllabus name already exists.');
                return FALSE;
            }
        }else{
            return TRUE;
        }
    }

    public function _check_if_school_education_level_exist(){
        $id = $this->input->post('_id');
        $slug = generate_slug($this->input->post('name'));
        if($resource = $this->settings_m->get_school_education_level_by_slug($slug)){
            if($resource->id == $id){
                return TRUE;
            }else{
                $this->form_validation->set_message('_check_if_school_education_level_exist','The school level name already exists.');
                return FALSE;
            }
        }else{
            return TRUE;
        } 
    }

    public function _check_if_resource_settings_exist(){
        $id = $this->input->post('_id');
        $parent_id = $this->input->post('parent_id');
        $slug = generate_slug($this->input->post('name'));
        if($resource = $this->settings_m->get_education_by_slug($slug,$parent_id)){
            if($resource->id == 1){
                return TRUE;
            }else{
                $this->form_validation->set_message('_check_if_resource_settings_exist','The resource settings already exists.');
                return FALSE;
            }
        }else{
            return TRUE;
        }
    }

    public function _check_if_exam_category_exist(){
        $slug = generate_slug($this->input->post('name'));
        $user_id = $this->token_user->_id;
        $name = $this->input->post('name');
        $id = $this->input->post('_id');
        if($exam_category = $this->settings_m->get_exam_category_by_slug($slug,$user_id)){
            if($exam_category->id == $id){
                return TRUE;
            }else{
                $this->form_validation->set_message('_check_if_exam_category_exist','The exam category '.$name.' already exists.');
                return FALSE;
            }
        }else{
            return TRUE;
        }
    }

    public function _class_slug_is_unique(){
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

}