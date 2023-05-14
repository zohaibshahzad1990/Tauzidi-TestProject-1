<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends Api_Controller{
	
	public $response = array(
		'result_code' => 0,
		'result_description' => 'Default Response'
	);

	function __construct(){
        parent::__construct();
        $this->load->model('countries/countries_m');
        header("Content-Type: application/json");
    }

    function get_all(){
        $countries = $this->countries_m->get_all();
        if($countries){
            $this->response = array(
                'result_code' => 200,
                'result_description' => 'Success',
                'data' => $countries,
            );
        }else{
            $this->response = array(
                'result_code' => 'CH4032',
                'result_description' => 'No countries records found',
            );
        }
        echo json_encode($this->response);
    }

    function get(){
        $country_id = isset($this->data->country_id)?$this->data->country_id:'';
        $country_code = isset($this->data->country_code)?$this->data->country_code:'';
        if($country_id || $country_code || $calling_code || $currency_code){
            $countries = $this->countries_m->get($country_id, $country_code, $calling_code, $currency_code);
            if($countries){
                $this->response = array(
                    'result_code' => 200,
                    'result_description' => 'Success',
                    'data' => $countries,
                );
            }else{
                $this->response = array(
                    'result_code' => 'CH4032',
                    'result_description' => 'No countries records found',
                );
            }
        }else{
            $this->response = array(
                'result_code' => 'CH4033',
                'result_description' => 'Kindly set atleast one search parameter',
            );
        }
        echo json_encode($this->response);
        
    }

    function get_country_by_calling_code(){
        $calling_code = isset($this->data->calling_code)?$this->data->calling_code:'';
        if($calling_code){
            $countries = $this->countries_m->get_country_by_calling_code($calling_code);
            if($countries){
                $this->response = array(
                    'result_code' => 200,
                    'result_description' => 'Success',
                    'data' => $countries,
                );
            }else{
                $this->response = array(
                    'result_code' => 'CH4032',
                    'result_description' => 'No countries records found',
                );
            }
        }else{
            $this->response = array(
                'result_code' => 'CH4041',
                'result_description' => 'Calling code not set in JSON Payload',
            );
        }
        echo json_encode($this->response);
    }
}