<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pages extends Public_Controller{

	protected $data = array();

	public function __construct(){
	    parent::__construct();
	}

 	public function terms_and_conditions(){
    	$this->template->title('Terms & Conditions')->build('front/terms_and_conditions',$this->data);
 	}

 	public function privacy(){
    	$this->template->title('Privacy policy')->build('front/privacy',$this->data);
 	}

}