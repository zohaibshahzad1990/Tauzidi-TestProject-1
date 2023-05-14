<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends Admin_Controller{

	protected $data = array();

	protected $validation_rules = array(
		array(
            'field' =>  'name',
            'label' =>  'Name',
            'rules' =>  'trim|required|callback__slug_is_unique',
        ),array(
            'field' =>  'description',
            'label' =>  'Description',
            'rules' =>  'strip_tags|trim|required',
        )

	);

	function __construct(){
    parent::__construct();
    $this->load->model('user_groups_m');
	}

  function index(){
    
  }

  function _slug_is_unique(){
  	$slug = generate_slug($this->input->post('name'));
    $name = $this->input->post('name');
  	$id = $this->input->post('id');
  	if($user_group = $this->user_groups_m->get_user_group_by_slug($slug)){
  		if($user_group->id == $id){
  			return TRUE;
  		}else{
    		$this->form_validation->set_message('_slug_is_unique','The user group '.$name.' already exists.');
    		return FALSE;
    	}
  	}else{
  		return TRUE;
  	}
  }

  function create(){
  	$post = new stdClass();
  	$this->form_validation->set_rules($this->validation_rules);
  	if($this->form_validation->run()){
  		$input = array(
  			'name' => $this->input->post('name'),
        'slug' => generate_slug($this->input->post('name')),
        'active'=>1,
  			'description' => $this->input->post('description'),
  			'created_on' => time(),
  			'created_by' => $this->user->id,
  		);
  		if($id = $this->user_groups_m->insert($input)){
  			$this->session->set_flashdata('success',"User group created.");
  			redirect('admin/user_groups/listing');
  		}else{
  			$this->session->set_flashdata('error',"Could not insert user group.");
  		}
  	}else{
  		 foreach($this->validation_rules as $key => $field):
          $field_name = $field['field'];
          $post->$field_name = set_value($field['field']);
      endforeach;
    }
    $this->data['id'] = "";
  	$this->data['post'] = $post;
  	$this->template->title('Create User Group')->build('admin/form',$this->data);
  }

  function edit($id = 0){
  	$id OR redirect('admin/user_groups/listing');
  	$post = $this->user_groups_m->get($id);
  	$post OR redirect('admin/user_groups/listing');
  	$this->form_validation->set_rules($this->validation_rules);
  	if($this->form_validation->run()){
  		$input = array(
  			'name' => $this->input->post('name'),
  			'slug' => generate_slug($this->input->post('name')),
        'description' => $this->input->post('description'),
  			'created_on' => time(),
        'active'=>1,
  			'created_by' => $this->user->id,
  		);
  		if($id = $this->user_groups_m->update($id,$input)){
  			$this->session->set_flashdata('success',"User group updated.");
  			redirect('admin/user_groups/listing');
  		}else{
  			$this->session->set_flashdata('error',"Could not update user group.");
  		}
  	}else{
  		foreach ($this->validation_rules as $key => $field){
  			if(set_value($field['field'])){
              	//$post->$field['field'] = set_value($field['field']);
              }
          }
  	}
  	$this->data['id'] = $id;
  	$this->data['post'] = $post;
  	$this->template->title('Edit User Group')->build('admin/form',$this->data);
  }

  function listing(){
  	$total_rows = $this->user_groups_m->count_user_groups();
  	$pagination = create_pagination('admin/user_groups/listing/pages', $total_rows,100,5,TRUE);
  	$this->data['posts'] = $this->user_groups_m->limit($pagination['limit'])->get_user_groups();
  	$this->data['pagination'] = $pagination;
  	$this->template->title('List User Group')->build('admin/listing',$this->data);
  }

}
