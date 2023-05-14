<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ajax extends Ajax_Controller{

    protected $validation_rules = array(
        array(
            'field' => 'id',
            'label' => 'Incident Id',
            'rules' => 'trim|required',
        )
    );

    function __construct(){
        parent::__construct();
        $this->load->model('incidents_m');   
    }

    function mark_as_read(){
        $this->form_validation->set_rules($this->validation_rules);
        if($this->form_validation->run()){
            $id = $this->input->post('id');
            $incident = $this->incidents_m->get($id);
            if($incident){
                $input = array(
                    'active' => 1,
                    'is_resolved'=>1,
                    'modified_on' => time(),
                    'modified_by' => $this->user->id,
                );
                if($this->incidents_m->update($incident->id,$input)){
                    $this->response = array(
                        'result_code' => 200,
                        'message' => 'Successful marked as reolved'
                    );
                }else{
                    $this->response = array(
                        'result_code' => 400,
                        'message' => 'Could not mark as resolved',
                    );
                }
                
            }else{
                $this->response = array(
                    'result_code' => 400,
                    'message' => 'Could not get incident',
                );
            }
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

} ?>