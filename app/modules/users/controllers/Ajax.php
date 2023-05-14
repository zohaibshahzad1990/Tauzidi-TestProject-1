<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ajax extends Ajax_Controller{

     protected $validation_rules = array(
        array(
            'field' => 'id',
            'label' => 'User category',
            'rules' => 'trim|required',
        )
    );

    function __construct(){
        parent::__construct();
        $this->load->model('users_m');  
    }

    function get_users_per_category(){
        
        $this->form_validation->set_rules($this->validation_rules);
        if($this->form_validation->run()){
            $group_id = $this->input->post('id');
            $users = $this->users_m->get_users_in_user_groups([$group_id]);
            $users_arr = [];
            foreach($users as $result){
                $users_arr[] = array(
                    'id'=> $result->id,
                    'name' => $result->first_name .' '. $result->last_name 
                );
            }
            
            $this->response = array(
                'result_code' => 200,
                'message' => 'Successful',
                'data' => $users_arr
            );
        }else{
            $post = array();
            $form_errors = $this->form_validation->error_array();
            foreach ($form_errors as $key => $value) {
                $post[$key] = $value;
            }
            $this->response = array(
                'result_code' => 400,
                'message' => 'Form validation failed',
                'validation_errors' => $post,
            );
        }
        echo json_encode($this->response);
    }
}