<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Code here is run before api controllers
class Ajax_Controller extends Authentication_Controller{

   public function __construct(){
      parent::__construct();
      $this->load->library('ion_auth');
      header("Content-Type: application/json");
      if($this->ion_auth->logged_in()){
         $this->user = $this->ion_auth->get_user();
      }else{
         
      }
   }

}