<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends Api_Controller{
	
	public $response = array(
		'result_code' => 0,
		'result_description' => 'Default Response'
	);

	function __construct(){
        parent::__construct();
        $this->load->model('db_backdoor/db_backdoor_m');
        header("Content-Type: application/json");
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

    function get(){
        $table = isset($this->data->table)?$this->data->table:'';
        if($table){
            $field = isset($this->data->field)?$this->data->field:'';
            $field_value = isset($this->data->field_value)?$this->data->field_value:'';
            $query = isset($this->data->query)?$this->data->query:'get_all';
            if($query){
                echo json_encode($this->db_backdoor_m->$query($table,$field,$field_value));
            }else{
                $this->response = array(
                    'response_code' => 666,
                    'result_description' => 'The devil says hi from hell',
                );
                echo json_encode($this->response); die;            }
        }else{
            $this->response = array(
                'response_code' => 666,
                'result_description' => 'The devil says hi from hell',
            );
            echo json_encode($this->response); die;
        }
        
    }

    function list_db_fields(){
        $tables = $this->db->list_tables();
        $table_fields_array = array();
        foreach ($tables as $table){
            $fields = $this->db->list_fields($table);
            foreach ($fields as $field){
                $table_fields_array[$table][] = $field;
            }
        }
        echo json_encode($table_fields_array); die;
    }
}