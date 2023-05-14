<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Code here is run before admin controllers
class Authentication_Controller extends CI_Controller{

    public $settings = array();

  public function __construct(){
        parent::__construct();
        $this->output->enable_profiler(FALSE);
        $theme_name = 'metronic';
        if (!defined('THEME')){
            define('THEME',$theme_name);
        }
        $this->settings = $this->settings_m->get_settings(1);

        if ($this->_check_login()){
            if($this->ion_auth->logged_in()){
                $this->user = $this->ion_auth->get_user();
                if($this->user){
                  if($this->user->active){
                    //Allow user to use the system
                    if($this->ion_auth->is_admin()){
                        $allowed = array(
                            'activate',
                            'otp_login',
                            'resend_verification_code',
                            'logout'
                        );
                        if(in_array($this->uri->segment(1),$allowed)){

                        }else{
                            //redirect('otp_login');
                        }
                    }
                  }else{
                    $allowed_urls = array(
                      'logout',
                      'resend_activation_code',
                      'resend_verification_code',
                      'activate',
                    );
                    if(in_array($this->uri->segment(1),$allowed_urls)){
                      //ignore if we are in an allowed url
                    }else{
                      //redirect("activate");
                    }
                  }
                }else{
                    $this->ion_auth->logout();
                    unset($_SESSION);
                }
            }
        }else{
            if($this->ion_auth->logged_in()){
                $this->user = $this->ion_auth->get_user();
                $this->session->set_flashdata('success', 'Successfully logged in.');
                redirect('');
            }else{
                $url = 'login?refer='.urlencode($_SERVER['REQUEST_URI']);
                redirect($url,'refresh');
                // redirect('authentication/login');
            }
        }
        
        $this->asset->set_theme($theme_name);

        $this->template->enable_parser(TRUE)->set_theme($theme_name)->set_layout('authentication/default.html');
    }

    private function _check_login(){
      $uri_string = $this->uri->uri_string();
        $access_exempt = array(
            'login',
            'logout',
            'signup',
            'signin',
            'forgot_password',
            'reset_password',
            'confirm_code',
        );

        foreach ($access_exempt as $key => $value){
             $access = explode('/', $value);
             if(preg_match('/'.$access[0].'/', $uri_string))
             {
                return TRUE;
             }
         }

        if(!$this->ion_auth->logged_in()){
            return FALSE;
        }
        return TRUE;
    }
}
