<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends Admin_Controller{

    protected $data = array();

    protected $validation_rules = array(
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


    public function __construct(){
        parent::__construct();
        $this->load->model('schools_m');
        $this->load->library('schools_manager');
    }    

    public function create(){

        $post = new stdClass();
        $this->form_validation->set_rules($this->validation_rules);
        $name = $this->input->post('name');
        $description = $this->input->post('description');
        if ($this->form_validation->run()){
             $school = array(
              'name'=>$name,
              'slug'=>generate_slug($name),
              'description'=>$description,
              'user_id'=>$this->user->id,
              'active'=>1,
              'created_on'=>time(),
              'created_by'=>$this->user->id,
            );
            if($school_id = $this->schools_manager->create_school($school)){
                $this->session->set_flashdata('success','You have sucessfully created a school '.$name);
                redirect('admin/schools/edit/'.$school_id);               
            }else{
                $this->session->set_flashdata('error','Could not create school: '.$this->session->warning);
                redirect('admin/schools/create');
            }
        }else{
            // Go through all the known fields and get the post values
            foreach ($this->validation_rules as $key => $field){                
                $field_name = $field['field'];
                $post->$field_name = set_value($field['field']);                
            }
        }
        $this->data['post'] = $post;
        $this->data['id'] = '';
        $this->template->title('Create School')->build('admin/form', $this->data);
    }

    public function edit($id = 0){
        $id OR redirect('admin/schools/listing');
        $post = new stdClass();
        $post = $this->schools_m->get($id);
        if(empty($post)){
            $this->session->set_flashdata('error','Sorry the school does not exist');
            redirect('admin/schools/listing','refresh');
        }
        $this->form_validation->set_rules($this->validation_rules);
        if($this->form_validation->run()){
            $name = $this->input->post('name');
            $description = $this->input->post('description');
            $school = array(
              'name'=>$name,
              'school_id'=>$id,
              'slug'=>generate_slug($name),
              'description'=>$description,
              'user_id'=>$this->user->id,
              'active'=>1,
              'modified_on'=>time(),
              'modified_by'=>$this->user->id,
            );
            if($school_id = $this->schools_manager->update_school($school)){
                $this->session->set_flashdata('success','You have sucessfully updated a school '.$name);
                redirect('admin/schools/listing');               
            }else{
                $this->session->set_flashdata('error','Could not update the school: '.$this->session->warning);
                redirect('admin/schools/create');
            }
            redirect('admin/schools/listing');
        }
        // Go through all the known fields and get the post values
        foreach ($this->validation_rules as $key => $field){
            $field_name = $field['field'];
            $post->$field_name = set_value($field['field'])?set_value($field['field']):$post->$field_name;
        }
        $this->data['post'] = $post;
        $this->data['id'] = $id;
        // Load WYSIWYG editor
        $this->template->title('Edit School')->build('admin/form',$this->data);
    }

    

    public function listing(){
        $total_rows = $this->schools_m->count_all_active_schools();
        $pagination = create_pagination('admin/schools/listing', $total_rows,250);
        $posts = $this->schools_m->limit($pagination['limit'])->get_all();
        $this->data['posts'] = $posts;
        $this->data['pagination'] = $pagination;
        $this->template->title('List Schools')->build('admin/listing', $this->data);
    }


    function _slug_is_unique(){
        $slug = generate_slug($this->input->post('name'));
        $user_id = $this->user->id;
        $name = $this->input->post('name');
        $id = $this->input->post('id');
        if($school = $this->schools_m->get_school_by_slug($slug)){
            if($school->id == $id){
                return TRUE;
            }else{
                $this->form_validation->set_message('_slug_is_unique','The school '.$name.' already exists.');
                return FALSE;
            }
        }else{
            return TRUE;
        }
    }

    public function action(){   
        switch ($this->input->post('btnAction')){ 
            case 'publish':
                $this->publish();
                break;
            case 'bulk_delete':
                $this->delete();
                break;
            default:
                redirect('admin/sms_templates/listing');
                break;
        }
    }

    public function delete($id = 0){
        if ($post = $this->schools_m->get($id)){
            //$this->sms_templates_m->delete($id);
            $input = array(
                'active' => 1,
                'modified_on' => time(),
                'modified_by' => $this->user->id
            );
            $this->schools_m->update($id,$input);
            $this->session->set_flashdata('warning','Could deleted school');

        }else{
             $this->session->set_flashdata('danger','Could not get school details  ');
        }
        redirect('admin/schools/listing');
    }


}

