<?php if(!defined('BASEPATH')) exit('You are not allowed to view this script');
class Authentication extends Authentication_Controller{

  protected $data = array();


  protected $login_rules = array(
    array(
      'field' => 'identity',
      'label' => 'Identity',
      'rules' => 'required|trim|valid_identity'
    ),
    array(
      'field' => 'password',
      'label' => 'Password',
      'rules' => 'required'
    ),
    array(
      'field' => 'remember',
      'label' => 'Remember Me',
      'rules' => 'trim'
    ),
  );

  protected $signup_rules=array(
    array(
      'field' => 'full_name',
      'label' => 'First Name',
      'rules' => 'required|trim|callback__check_full_name',
    ),
    array(
      'field' => 'identification_document_type',
      'label' => 'Identification Document Type',
      'rules' => 'required|trim|numeric',
    ),
    array(
      'field' => 'identification_document_number',
      'label' => 'Identification Document Number',
      'rules' => 'required|trim|numeric|callback__is_unique_id_number',
    ),
    array(
      'field' => 'phone',
      'label' => 'Phone Number',
      'rules' => 'required|trim|valid_phone|callback__is_unique_phone_number',
    ),
    array(
      'field' => 'email',
      'label' => 'Email',
      'rules' => 'trim|callback__is_unique_email',
    ),
    array(
      'field' => 'date_of_birth',
      'label' => 'Date of Birth',
      'rules' => 'required|trim|date',
    ),
    array(
      'field' => 'confirm_password',
      'label' => 'Confirm Password',
      'rules' => 'required',
    ),
    array(
      'field' => 'password',
      'label' => 'Password',
      'rules' => 'required|min_length[4]|matches[confirm_password]',
    ),
    array(
      'field' => 'agree',
      'label' => 'Terms and Conditions',
      'rules' => 'required',
    ),
  );

  protected $activation_rules = array(
    array(
      'field' => 'activation_code',
      'label' => 'Activation Code',
      'rules' => 'required|trim|numeric'
    ),
  );

  protected $verification_rules = array(
    array(
      'field' => 'otp_code',
      'label' => 'Verification Code',
      'rules' => 'required|trim|numeric'
    ),
  );

  protected $forgot_password_rules = array(
    array(
      'field' => 'identity',
      'label' => 'Identity',
      'rules' => 'required|trim|callback__valid_identity',
    )
  );

  protected $confirm_code_rules = array(
    array(
      'field' => 'otp_code',
      'label' => 'Password reset code',
      'rules' => 'required|trim|numeric',
    ),
    array(
      'field' => 'identity',
      'label' => 'Phone/Email Address',
      'rules' => 'required|trim',
    ),
  );

  protected $reset_password_rules = array(
    array(
      'field' => 'confirm_password',
      'label' => 'Confirm Password',
      'rules' => 'required|trim'
    ),
    array(
      'field' => 'password',
      'label' => 'Password',
      'rules' => 'required|trim|min_length[8]|matches[confirm_password]',
    ),
  );

  function __construct(){
    parent::__construct();
    $this->load->model('users/users_m');
    $this->load->model('settings/settings_m');
    $this->load->library('messaging_manager');
    $this->load->library('files_uploader');
    $this->load->library('image_lib');
  

  }

  function _is_unique_id_number(){
    $identification_document_number = $this->input->post('identification_document_number');
    if($this->users_m->get_user_by_id_number($identification_document_number)){
      $this->form_validation->set_message('_is_unique_id_number','The Identification Document is already registered to another account in the system');
      return FALSE;
    }else{
      return TRUE;
    }
  }

  function _is_unique_phone_number(){
    $phone = valid_phone($this->input->post('phone'));
    if($this->users_m->get_user_by_phone_number($phone)){
      $this->form_validation->set_message('_is_unique_phone_number','The Phone Number is already registered to another account in the system');
      return FALSE;
    }else{
      return TRUE;
    }
  }

  function _is_unique_email(){
    $email = strtolower($this->input->post('email'));
    if($email){
      if($this->users_m->get_user_by_email($email)){
        $this->form_validation->set_message('_is_unique_email','The Email Address is already registered to another account in the system');
        return false;
      }elseif(!valid_email($email)){
        $this->form_validation->set_message('_is_unique_email','The Email Address submitted not a valid email address');
        return false;
      }else{
        return true;
      }
    }else{
      return TRUE;
    }
  }

  function _valid_identity(){
      $identity = $this->input->post('identity');
      if(valid_phone($identity) || valid_email($identity)){
        if(valid_phone($identity)){
         if($this->ion_auth->get_user_by_phone(valid_phone($identity))){
            return TRUE;
          }else{
            $this->form_validation->set_message('_valid_identity','Phone Number is not registered to any user on the system');
            return FALSE;
          }
        }else{
          if($this->ion_auth->get_user_by_email($identity)){
              return TRUE;
          }else{
            $this->form_validation->set_message('_valid_identity','Email Address is not registered to any user on the system');
            return FALSE;
          }
        }
      }else{
        $this->form_validation->set_message('_valid_identity','Enter a valid Phone Number or Email Address');
        return FALSE;
      }
  }

    function _check_full_name(){
      $full_name = $this->input->post('full_name');
      $full_name_array = explode(' ',$full_name);
      if(count($full_name_array) < 2){
        $this->form_validation->set_message('_check_fullname','Please Enter Atleast Two Names');
          return FALSE;
      }else{
        return TRUE;
      }
    }

  function activate(){
    $data = new stdClass();
    $this->form_validation->set_rules($this->activation_rules);
    if($this->form_validation->run()){
      $activation_code = $this->input->post('activation_code');
      if($this->user->activation_code == $activation_code){
        if($this->users_m->activate($this->user->id)){
          redirect('admin');
        }else{
          $this->session->set_flashdata('error','Error activating account');
        }
      }else{
        $this->session->set_flashdata('error','The activation code you have entered does not match the code that we sent you. Kindly recheck the code sent to you and try again.');
      }
    }
    $data->phone = $this->user->phone;
    $this->template->title('Activate Account')->build('authentication/activate',$data);
  }

  function index(){
    
    redirect('admin');
    
    //$this->template->title('Authentication Dashboard')->build('authentication/index',$this->data);
  }

  function resend_activation_code(){
    if($this->messaging->send_activation_code($this->user)){
      // $this->session->set_flashdata('success','Activation code resent to '.$this->user->phone);
      redirect('activate');
    }else{
      $this->session->set_flashdata('error','Error resending activation code');
      // redirect('activate');
    }
  }

  function signup(){
    if($this->ion_auth->logged_in()){
        redirect('admin');
      }
    $post = new stdClass();
    $this->form_validation->set_rules($this->signup_rules);
    if($this->form_validation->run()){
      
      
      $full_name = $this->input->post('full_name');
      $full_name_array = explode(' ',$full_name);
      if(count($full_name_array) == 2){
        $first_name = $full_name_array[0];
        $last_name = $full_name_array[1];
      }else{
        $first_name = $full_name_array[0];
        $middle_name = $full_name_array[1];
        $last_name_array = array();
        for($i = 2; $i < count($full_name_array); $i++){
          $last_name_array[] = $full_name_array[$i].' ';
        }
        $last_name = implode(' ',$last_name_array);
      }
      $additional_data = array(
        'first_name' => $first_name,
        'middle_name' => isset($middle_name)?$middle_name:'',
        'last_name' => $last_name,
        'email' => $this->input->post('email'),
        'identification_document_type' => $this->input->post('identification_document_type'),
        'identification_document_number' => $this->input->post('identification_document_number'),
        'date_of_birth' => $this->input->post('date_of_birth'),
        'phone' => $this->input->post('phone'),
        'currency' => 'KES',
        'is_active' => 0,
        'activation_code' => rand(1000,9999),
      );
      $user_groups = array(1);
      $phone = $this->input->post('phone');
      $password = $this->input->post('password');
      $identity = valid_phone($phone);
      if($user_id = $this->ion_auth->register($identity,$password,'', $additional_data,$user_groups,TRUE)){
        
      }else{
        $this->session->set_flashdata('error','Could not register the customer');
        //redirect('signup');
      }
    }else{
        foreach($this->signup_rules as $key => $field){
          $field_name = $field['field'];
            $post->$field_name = set_value($field['field']);
        }
      }
      $data['post'] = $post;
    $this->template->title('Sign Up'.$this->settings->application_name." Account ")->build('authentication/signup',$data);
  }

  function signin(){
    $this->template->title('Sign In')->build('authentication/signin',$this->data);
  }

  function login(){
    
    if($this->ion_auth->logged_in()){
        redirect('admin');
      }
    $post = new stdClass();
    $this->form_validation->set_rules($this->login_rules);
    if($this->form_validation->run()){
      $identity = $this->input->post('identity');
        $password = $this->input->post('password');
        $remember = (bool) $this->input->post('remember');
        if($user = $this->ion_auth->login($identity, $password, $remember)){
          //check if user wallet exists
            $refer = $this->input->get_post('refer');
            $this->session->set_flashdata('success',$this->ion_auth->messages());
            if($refer){
                // redirect($refer);
                redirect('authentication/login');

            }else{
                if($this->ion_auth->is_admin()){
                  $user = $this->ion_auth->get_user();
                  $user->otp_code = rand(1000,9999);
                if($this->users_m->update($user->id,array('otp_code' => $user->otp_code))){
                  
                    //$this->session->set_flashdata('error','Error Sending Verification Code');
                    redirect('admin');
                  
                } 
                }else{
                    redirect('admin');
                }
            }
        }else{
            $this->session->set_flashdata('error', $this->ion_auth->errors());
            //redirect('login');
            foreach ($this->login_rules as $key => $field){
              $field_name = $field['field'];
              $post->$field_name = set_value($field['field']);
            }
        }
    }else{
      foreach($this->login_rules as $key => $field){
        $field_name = $field['field'];
            $post->$field_name = set_value($field['field']);
          }
    }
    $this->data['post'] = $post;
    $this->template->title('Log into Your '.$this->settings->application_name." Account ")->build('authentication/login',$this->data);
  }

  function otp_login(){
    $this->form_validation->set_rules($this->verification_rules);
      if($this->form_validation->run()){
      $otp_code = $this->input->post('otp_code');
      if($this->user->otp_code == $otp_code){
        $input = array(
          'otp_code' => '',
          'modified_on' => time(),
          'modified_by' => $this->user->id,
        );
        if($this->users_m->update($this->user->id,$input)){
          redirect('admin');
        }else{
          $this->session->set_flashdata('error','Error processing verification code');
        }
      }else{
        $this->session->set_flashdata('error','The verification code you have entered does not match the code that we sent you. Kindly recheck the code sent to you and try again.');
        redirect('otp_login');
      }
    }else{
      $data = new stdClass();
      $data->phone = $this->user->phone;
      $this->template->title('Verification Code')->build('authentication/otp_login',$data);
    }
  }

  function resend_verification_code(){
    if($this->messaging->send_otp_code($this->user)){
      // $this->session->set_flashdata('success','verification code resent to '.$this->user->phone);
      redirect('otp_login');
    }else{
      $this->session->set_flashdata('error','Error resending activation code');
      // redirect('activate');
    }
  }

  function forgot_password(){
    if($this->ion_auth->logged_in()){
      redirect('admin');
    }
    $identity = $this->input->post('identity');
    $this->form_validation->set_rules($this->forgot_password_rules);
    if($this->form_validation->run()){
      $user = $this->ion_auth->get_user_by_identity($identity);
      if($user){
        if($this->ion_auth->is_in_group($user->id,1)){
          if($this->ion_auth->forgotten_password($identity)){
            redirect('confirm_code?identity='.$identity);
          }  
        }else{
          $this->session->set_flashdata('error','Action is forbidden for this account, contact your system admin for assistance');
        }
      }else{
        $this->session->set_flashdata('error','Details not found, check your Phone/Email address');
      }
      
    }
    $this->template->title('Forgot Password')->build('authentication/forgot_password');
  }

  function confirm_code($identity = ''){
    if($this->ion_auth->logged_in()){
      redirect('admin');
    }
    $post = new StdClass(); 
    $identity = $identity ? $identity :$this->input->post_get('identity');

    $this->form_validation->set_rules($this->confirm_code_rules);
    if($this->form_validation->run()){
      $otp_code = $this->input->post('otp_code'); 
      $identity = $this->input->post('identity'); 
      if($user = $this->ion_auth_model->confirm_code($identity,$otp_code)){
        if($forgotten_password_code = $this->ion_auth->confirm_code($identity,$otp_code)){
          $this->session->set_flashdata('success', "Code confirmed successful, set new password");
          redirect('reset_password?code='.$forgotten_password_code);
        }else{ 
          $this->session->set_flashdata('error',"Could not confirm code try again ");      
          redirect('confirm_code?identity='.$identity);
        }
      }else{
        $this->session->set_flashdata('error',"Invalid email/phone and code combination");      
        redirect('confirm_code?identity='.$identity);
      }
    }else{
      foreach($this->signup_rules as $key => $field){
          $field_name = $field['field'];
          $post->$field_name = set_value($field['field']);
      }
    }
    $this->data['identity'] = $identity;
    $this->data['post'] = $post;
    $this->template->title('Password Reset Code')->build('authentication/confirm_code',$this->data);
  }

  function reset_password(){
    if($this->ion_auth->logged_in()){
      redirect('admin');
    }
    $post = new StdClass();
    $forgotten_password_code = $this->input->post_get('code');
    $user_profile = $this->ion_auth->forgotten_password_check($forgotten_password_code);
    $this->form_validation->set_rules($this->reset_password_rules);
    if($this->form_validation->run()){
      if($user_profile){
        $password = $this->input->post('password');
        if($user = $this->ion_auth->forgotten_password_complete($forgotten_password_code,$password)){
          if($this->ion_auth->reset_password($user['identity'],$password)){
            $this->ion_auth->clear_forgotten_password_code($forgotten_password_code);
            $this->session->set_flashdata('error', $this->ion_auth->errors());
            $this->session->set_flashdata('success','Password change successful. Kindly login using your new password');
            redirect('login');
          }
        }
      }else{
        $this->session->set_flashdata('error','Error resetting your password');
        redirect("forgot_password?code=".$forgotten_password_code);
      }        
    }else{
      /*foreach ($this->reset_password_rules as $key => $field){
          $field_name = $field['field'];
          $post->$field_name = set_value($field['field']);
      }*/
    }    
    $this->template->title('Set New Password')->build('authentication/reset_password');
  }
  

  function logout(){
    @$this->ion_auth->logout();
    if(isset($_COOKIE)){
          unset($_COOKIE);
    }
    if(isset($_SESSION)){
          unset($_SESSION);
    }
        $this->session->set_flashdata('success', 'You have Successfully Logged Out');
        redirect('/','refresh');
  }

  function signout(){
    @$this->ion_auth->logout();
        if(isset($_COOKIE)){
          unset($_COOKIE);
    }
    if(isset($_SESSION)){
          unset($_SESSION);
    }
        $this->session->set_flashdata('success', 'You have Successfully Logged Out');
       redirect('/','refresh');
  }

}
