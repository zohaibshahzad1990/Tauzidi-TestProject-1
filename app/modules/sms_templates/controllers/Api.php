<?php if(!defined('BASEPATH')) exit('You are not allowed to view this script');
class Api extends Api_Controller{

    public $response = array(
        'result_code' => 0,
        'result_description' => 'Default Response'
    );

    function __construct(){
        parent::__construct();
        $this->load->model('email_templates_m');
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

    public function create_sms_template(){
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
                'label' =>  'SMS Template Title',
                'rules' =>  'required|trim|callback__is_unique_template',
            ),
            array(
                'field' =>  'description',
                'label' =>  'SMS Template Description',
                'rules' =>  'trim',
            ),
            array(
                'field' =>  'content',
                'label' =>  'SMS Template Content',
                'rules' =>  'trim|required|max_length[300]',
            ),
        );
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            /*$id = $this->sms_templates_m->insert(array(
                    'title'         =>      $this->input->post('title'), 
                    'slug'          =>      $this->input->post('slug'), 
                    'description'   =>      $this->input->post('description'), 
                    'sms_template'  =>      $this->input->post('sms_template'),
                    'created_on'    =>      time(),
                    'created_by'    =>      $this->ion_auth->get_user()->id,
            ));
            if ($id)
            {
                $this->session->set_flashdata('success',$this->input->post('title').' SMS template successfully created');
                if($this->input->post('new_item'))
                {
                    redirect('admin/sms_templates/create','refresh');
                }
                else
                {
                    redirect('admin/sms_templates/edit/'.$id,'refresh');
                }
            }
            else
            {
                $this->session->set_flashdata('error', 'Error adding SMS Templates');
                redirect('admin/sms_templates/listing','refresh');

            }*/
            $input = array(
                'title' => $this->input->post('title'),
                'slug' => generate_slug($this->input->post('title')),
                'description' => $this->input->post('description'),
                'sms_template' => $this->input->post('content'),
                'created_on' => time(),
                'created_by' => $this->token_user->_id,
            );
            if($id = $this->sms_templates_m->insert($input)){
                $response = array(
                    'status' => 1,
                    'message' => 'Sms template created Successfully '
                );
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'Could not create sms template',
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

    public function get_sms_templates(){
       $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }           
        if($this->user->id){
            $total_rows = $this->sms_templates_m->count_all();
            //$pagination = create_pagination('api',$total_rows,20);
            //create_custom_pagination('api',$total_rows,$page_size,$start,TRUE);
           // $posts = $this->sms_templates_m->limit($pagination['limit'])->get_all();
            $posts = $this->sms_templates_m->get_all();
            if($posts){
                $template_array = array();
                $count = 1;
                foreach ($posts as $key => $template) {
                    $template_array[] = (object)array(
                        'id'=> $count++,
                        '_id'=>$template->id,
                        'title'=>$template->title,
                        'slug'=>$template->slug,
                        'description'=>$template->description,
                        'content'=>$template->sms_template,
                        'created_on'=>$template->created_on,
                        'modified_on'=>$template->modified_on,
                    );
                }                
                $response = array(
                    'itemCount' => count($template_array),
                    'items' =>$template_array,
                );
            }else{
                $response = (object)array(
                    'itemCount' => 0,
                    'itmes' =>array()
                ); 
            }
        }else{
            $response = (object)array(
                'status' => 0,
                'message' =>"User details not found",
            );
        }
        echo json_encode($response);
    }

    public function update_sms_template(){
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        } 
        $id = $this->input->post('_id');          
        if($this->user->id){
            $post = $this->sms_templates_m->get($id);
            if($post){
                $result = $this->sms_templates_m->update($post->id, 
                    array(
                        'title'         => $this->input->post('title'), 
                        'slug'          =>  generate_slug($this->input->post('title')), 
                        'description'   => $this->input->post('description'), 
                        'sms_template'  => $this->input->post('content'),
                        'modified_on'   => time(),
                        'modified_by'   => $this->user->id,
                    )
                );
                if($result){
                    $response = (object)array(
                        'status' => 1,
                        'message' =>$this->input->post('title').' successfully updated',
                    );
                }else{
                    $response = (object)array(
                        'status' => 0,
                        'message' =>'Error  Editing '.$post->title.' Sms Templates',
                    );
                }
            }else{
                $response = (object)array(
                    'status' => 0,
                    'message' =>"Email template does not exist"
                ); 
            }
        }else{
            $response = (object)array(
                'status' => 0,
                'message' =>"User details not found",
            );
        }
        echo json_encode($response);
    }

    public function get_sms_template_by_id(){
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        } 
        $id = $this->input->post('_id');          
        if($this->user->id){
            $post = $this->sms_templates_m->get($id);
            if($post){
                $post = array(
                    'id'=>1,
                    '_id'=>$post->id,
                    'title'  => $post->title, 
                    'slug' => $post->slug, 
                    'description' => $post->description, 
                    'content'=> $post->sms_template,
                    'modified_on' => $post->modified_on,
                    'modified_by' => $post->modified_by,
                );
                $response = (object)array(
                    'status' => 1,
                    'message' => "success",
                    'data' =>$post,
                );
            }else{
                $response = (object)array(
                    'status' => 0,
                    'message' =>"Sms template does not exist"
                ); 
            }
        }else{
            $response = (object)array(
                'status' => 0,
                'message' =>"User details not found",
            );
        }
        echo json_encode($response);
    }

    public function delete_sms_template(){
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
                'label' =>  'Sms template id',
                'rules' =>  'required',
            ),            
        ); 
        $sms_template_id = $this->input->post('_id');
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            if($template = $this->sms_templates_m->get($sms_template_id)){
                $input = array(
                    'active' => 0,
                    'modified_on' => time(),
                    'modified_by'=> $this->token_user->_id,
                );                          
                $update = $this->sms_templates_m->delete($template->id, $input);
                if($update){
                    $response = array(
                        'status' => 1,
                        'message' =>'Success',
                    );
                }else{
                    $response = array(
                        'status' => 0,
                        'message' => "Could not delete the sms template",
                    );
                }
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'Sms template details not found',
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

    function _is_unique_template(){
        $slug = generate_slug($this->input->post('title'));
        $id = $this->input->post('id');
        if(empty($slug)){
            $this->form_validation->set_message('_is_unique_template','SMS Template slug is required');
            return FALSE;
        }else{
            $res = $this->sms_templates_m->get_by_slug($slug,$id);
            if($res)
            {
                $this->form_validation->set_message('_is_unique_template','SMS Template already exists');
                return FALSE;
            }
            else
            {
                return TRUE;
            }
        }
    }

}