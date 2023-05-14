<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends Admin_Controller{

    protected $data = array();

    protected $validation_rules = array(
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
                'field' =>  'sms_template',
                'label' =>  'SMS Template Content',
                'rules' =>  'trim|required|max_length[300]',
        ),
    );

    protected $edit_validation_rules = array(
        array(
                'field' =>  'title',
                'label' =>  'SMS Template Title',
                'rules' =>  'required|trim',
            ),
        array(
                'field' =>  'description',
                'label' =>  'SMS Template Description',
                'rules' =>  'trim',
            ),
        array(
                'field' =>  'sms_template',
                'label' =>  'SMS Template Content',
                'rules' =>  'trim|required|max_length[300]',
        ),
    );

    protected $sms_template = 'Welcome [FIRST_NAME]';

    public function __construct(){
        parent::__construct();
        $this->load->model('sms_templates_m');
    }

    public function index(){
        $this->template->title('SMS Templates Dashboard')->build('admin/index', $this->data);
    }

    public function listing(){
        //die;
        $total_rows = $this->sms_templates_m->count_all();
        $pagination = create_pagination('admin/sms_templates/listing', $total_rows,250);
        $posts = $this->sms_templates_m->limit($pagination['limit'])->get_all();
        $this->data['posts'] = $posts;
        $this->data['pagination'] = $pagination;
        $this->template->title('List SMS Templates')->build('admin/listing', $this->data);
    }

    function _is_unique_template(){
        $slug = generate_slug($this->input->post('title'));
        $id = $this->input->post('id');
        if(empty($slug)){
            $this->form_validation->set_message('_is_unique_template','SMS Template slug is required');
            return FALSE;
        }else{
            $res = $this->sms_templates_m->get_by_slug($slug,$id);
            if($res){
                $this->form_validation->set_message('_is_unique_template','SMS Template already exists');
                return FALSE;
            }else{
                return TRUE;
            }
        }
    }

    public function create(){

        $post = new stdClass();
        $this->form_validation->set_rules($this->validation_rules);
        if ($this->form_validation->run()){
            $id = $this->sms_templates_m->insert(array(
                'title' => $this->input->post('title'), 
                'slug' => generate_slug($this->input->post('title')), 
                'description' => $this->input->post('description'), 
                'sms_template' => $this->input->post('sms_template'),
                'created_on' => time(),
                'created_by' => $this->user->id,
            ));
            if($id){
                $this->session->set_flashdata('success',$this->input->post('title').' SMS template successfully created');
                if($this->input->post('new_item')){
                    redirect('admin/sms_templates/create','refresh');
                }else{
                    redirect('admin/sms_templates/edit/'.$id,'refresh');
                }
            }else{
                $this->session->set_flashdata('error', 'Error adding SMS Templates');
                redirect('admin/sms_templates/listing','refresh');
            }
        }else{
            // Go through all the known fields and get the post values
            foreach ($this->validation_rules as $key => $field){
                
                $field_name = $field['field'];
                $post->$field_name = set_value($field['field']);
                
            }
        }
        $this->data['post'] = $post;
        $this->data['sms_template'] = $this->sms_template;
        $this->data['id'] = '';
        $this->template->title('Create SMS Template')->build('admin/form', $this->data);
    }



    public function edit($id = 0){
        $id OR redirect('admin/sms_templates/listing');
        $post = new stdClass();
        $post = $this->sms_templates_m->get($id);
        if(empty($post)){
            $this->session->set_flashdata('error','Sorry the template does not exist');
            redirect('admin/sms_templates/listing','refresh');
        }
        $this->form_validation->set_rules($this->edit_validation_rules);
        if($this->form_validation->run()){
            $result = $this->sms_templates_m->update($id, 
                array(
                    'title' => $this->input->post('title'), 
                    'slug' => generate_slug($this->input->post('title')), 
                    'description' => $this->input->post('description'), 
                    'sms_template' => $this->input->post('sms_template'),
                    'modified_on' => time(),
                    'modified_by' => $this->user->id,
                )
            );

            if($result){
                $this->session->set_flashdata('success' ,$this->input->post('title').' successfully updated');
                if($this->input->post('new_item')){
                    redirect('admin/sms_templates/create');
                }else{
                    redirect('admin/sms_templates/listing');
                }
            }else{
                $this->session->set_flashdata('error','Error  Editing '.$post->title.' SMS Templates');
            }
            // Redirect back to the form or main page
            redirect('admin/sms_templates/listing');
        }
        // Go through all the known fields and get the post values
        foreach ($this->validation_rules as $key => $field){
            $field_name = $field['field'];
            $post->$field_name = set_value($field['field'])?set_value($field['field']):$post->$field_name;
        }
        $this->data['post'] = $post;
        $this->data['id'] = $id;
        $this->data['sms_template'] = $this->sms_template;
        // Load WYSIWYG editor
        $this->template->title('Edit Template')->build('admin/form',$this->data);
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
        // Delete one
        $ids = ($id) ? array($id) : $this->input->post('action_to');
        // Go through the array of slugs to delete
        if (!empty($ids)){
            $post_titles = array();
            foreach ($ids as $id){
                // Get the current page so we can grab the id too
                if ($post = $this->sms_templates_m->get($id)){
                    $this->sms_templates_m->delete($id);
                    // Wipe cache for this model, the content has changed
                    $post_titles[] = $post->title;
                }
            }
        }
        // Some pages have been deleted

        if (!empty($post_titles)){
            // Only deleting one page
            if (count($post_titles) == 1){
                $this->session->set_flashdata('success', sprintf(' SMS Templates Deleted', $post_titles[0]));
            }else{
                $this->session->set_flashdata('success', sprintf(' SMS Templates Deleted', implode('", "', $post_titles)));
            }
        }else{
            $this->session->set_flashdata('info', 'Items: SMS Templates Deleted');
        }
        redirect('admin/sms_templates/listing');
    }


}

