<?php if(!defined('BASEPATH')) exit('You are not allowed to view this script');
class Api extends Api_Controller{

	public $response = array(
		'result_code' => 0,
		'result_description' => 'Default Response'
	);

	function __construct(){
        parent::__construct();
        $this->load->model('user_groups_m');
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

    public function create_role(){
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
                'field' =>  'title',
                'label' =>  'Title',
                'rules' =>  'trim|required|callback__slug_is_unique',
            ),array(
                'field' =>  'slug',
                'label' =>  'Slug',
                'rules' =>  'trim|callback__slug_is_unique',
            ),array(
                'field' =>  'description',
                'label' =>  'Description',
                'rules' =>  'strip_tags|trim',
            )
        );        
        $name = $this->input->post('title');
        $description = $this->input->post('description');
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            $input = array(
                'name' => $this->input->post('title'),
                'slug' => generate_slug($this->input->post('title')),
                'description' => $this->input->post('description'),
                'permissions'=> serialize($this->input->post('permissions')),
                'active'=>1,
                'created_on' => time(),
                'created_by' => $this->token_user->_id,
            );
            if($id = $this->user_groups_m->insert($input)){
                $response = array(
                    'status' => 1,
                    'message' => 'User group created Successfully '
                );
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'Could not create user group',
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

    public function update_role(){
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
                'field' =>  'title',
                'label' =>  'Title',
                'rules' =>  'trim|required|callback__slug_is_unique',
            ),
            array(
                'field' =>  '_id',
                'label' =>  'User groupd id variable',
                'rules' =>  'trim|required',
            ),array(
                'field' =>  'slug',
                'label' =>  'Slug',
                'rules' =>  'trim|callback__slug_is_unique',
            ),array(
                'field' =>  'description',
                'label' =>  'Description',
                'rules' =>  'strip_tags|trim',
            )
        );        
        $name = $this->input->post('title');
        $id = $this->input->post('_id');
        $description = $this->input->post('description');
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            $input = array(
                'name' => $this->input->post('title'),
                'slug' => generate_slug($this->input->post('title')),
                'description' => $this->input->post('description'),
                'permissions'=> serialize($this->input->post('permissions')),
                'active'=>1,
                'modified_on' => time(),
                'modified_by' => $this->token_user->_id,
            );
            if($id = $this->user_groups_m->update($id,$input)){
                $response = array(
                    'status' => 1,
                    'message' => 'Role updated Successfully '
                );
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'Could not update role',
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

    public function delete_role(){
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
                'label' =>  'Role id',
                'rules' =>  'required',
            ),            
        ); 
        $role_id = $this->input->post('_id');
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            if($role = $this->user_groups_m->get($role_id)){
                if(generate_slug($role->name) == 'admin' || generate_slug($role->name) == 'member'|| generate_slug($role->name) == 'teacher' || generate_slug($role->name) == 'student'){
                    $core_role = TRUE;
                    //$permissions = array(1,2,3,4,5,6,7,8,9,10,11,12);
                }else{                    
                    $core_role = FALSE;
                }
                if($core_role){
                    $response = array(
                        'status' => 1,
                        'message' =>'You cannot delete a core role',
                    );
                }else{
                    $input = array(
                        'active' => 0,
                        'modified_on' => time(),
                        'modified_by'=> $this->token_user->_id,
                    );           
                    $update = $this->user_groups_m->update($role_id, $input);
                    if($update){
                        $response = array(
                            'status' => 1,
                            'message' =>'Role successfully delete',
                        );
                    }else{
                        $response = array(
                            'status' => 0,
                            'message' =>"Role could not be deleted",
                        );
                    }
                }
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'Role is not found',
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

    public function get_user_groups(){
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $name = $this->input->post('name');
        $filter_params = array("name"=>array($name));
        $lower_limit = $this->input->post('lower_limit')?:0;
        $upper_limit = $this->input->post('upper_limit')?:20;
        $records_per_page = $upper_limit - $lower_limit;
        $total_rows = $this->user_groups_m->count_user_groups();
        $pagination = create_pagination('',$total_rows,$records_per_page,$upper_limit
            ,TRUE);
        $posts = $this->user_groups_m->limit($pagination['limit'])->get_user_groups($filter_params);
        $user_groups_array = array();
        if($posts){
            $count =1;
            foreach ($posts as $key => $post) {
                $user_groups_array[] = (object) array(
                    'id'=> $count++,
                    '_id'=>$post->id,
                    'name'=>$post->name,
                    'slug'=>$post->slug,
                    'description'=>$post->description,
                    'created_on'=>$post->created_on
                );
            }
            $response = array(
                'status' => 1,
                'message' => 'Sucess',
                'data'=> $user_groups_array
            );
        }else{
            $response = array(
                'status' => 0,
                'message' => 'User group details empty(var)',
            );
        }
        echo json_encode($response);
    }

    function _slug_is_unique(){
        $slug = generate_slug($this->input->post('title'));
        $name = $this->input->post('title');
        $id = $this->input->post('_id');
        if($user_group = $this->user_groups_m->get_user_group_by_slug($slug)){
            if($user_group->id == $id){
                return TRUE;
            }else{
                $this->form_validation->set_message('_slug_is_unique','The role '.$name.' already exists.');
                return FALSE;
            }
        }else{
            return TRUE;
        }
    }

}