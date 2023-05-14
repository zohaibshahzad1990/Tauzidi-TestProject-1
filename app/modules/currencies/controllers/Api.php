<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends Api_Controller{
	
	public $response = array(
		'result_code' => 0,
		'result_description' => 'Default Response'
	);
	function __construct(){
        parent::__construct();
        $this->load->model('currencies/currencies_m');
        header("Content-Type: application/json");
    }

    function get_all(){
        $currencies = $this->currencies_m->get_all();
        if($currencies){
            $this->response = array(
                'result_code' => 200,
                'result_description' => 'Success',
                'data' => $currencies,
            );
        }else{
            $this->response = array(
                'result_code' => 'CH4038',
                'result_description' => 'No currency records found',
            );
        }
        echo json_encode($this->response);
    }

    function get(){
        $currency_id = isset($this->data->currency_id)?$this->data->currency_id:'';
        if($currency_id){
            $currency = $this->currencies_m->get($currency_id, $country_id, $currency_code);
            if($currency){
                $this->response = array(
                    'result_code' => 200,
                    'result_description' => 'Success',
                    'data' => $currency,
                );
            }else{
                $this->response = array(
                    'result_code' => 'CH4038',
                    'result_description' => 'No currency records found',
                );
            }
        }else{
            $this->response = array(
                'result_code' => 'CH4043',
                'result_description' => 'Currency ID not set in JSON Payload ',
            );
        }
        echo json_encode($this->response);
        
    }

    function get_currency_by_country_id(){
        $country_id = isset($this->data->country_id)?$this->data->country_id:'';
        if($country_id){
            $currency = $this->currencies_m->get_currency_by_country_id($country_id);
            if($currency){
                $this->response = array(
                    'result_code' => 200,
                    'result_description' => 'Success',
                    'data' => $currency,
                );
            }else{
                $this->response = array(
                    'result_code' => 'CH4038',
                    'result_description' => 'No currency records found',
                );
            }
        }else{
            $this->response = array(
                'result_code' => 'CH4033',
                'result_description' => 'Country ID not set in JSON Payload',
            );
        }
        echo json_encode($this->response);
        
    }

    function get_currency_by_currency_code(){
        $currency_code = isset($this->data->currency_code)?$this->data->currency_code:'';
        if($currency_code){
            $currency = $this->currencies_m->get_currency_by_currency_code($currency_code);
            if($currency){
                $this->response = array(
                    'result_code' => 200,
                    'result_description' => 'Success',
                    'data' => $currency,
                );
            }else{
                $this->response = array(
                    'result_code' => 'CH4038',
                    'result_description' => 'No currency records found',
                );
            }
        }else{
            $this->response = array(
                'result_code' => 'CH4042',
                'result_description' => 'Currency code not set in JSON Payload',
            );
        }
        echo json_encode($this->response);
        
    }
}