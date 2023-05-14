<?php defined('BASEPATH') OR exit('No direct script access allowed');
// Code here is run before frontend controllers
class Api_Controller extends Public_Controller{
    public $settings;
    public $soap_header = '';
    public $wsdl;
    public $data;
    public $user;
    public $token_user;
    public $request_id;
    public $resource;
    public $filter_params = '';
    public $request_headers;
    public $token_key = 'd8ng63ttyjp88cnjpkme65efgz6b2gwg';
    public $activity_log_options = array();
    public function __construct(){
        $time_start = microtime(true);
        parent::__construct();
        $this->config->set_item('csrf_protection', TRUE);
        $this->load->model('users/users_m');
        $this->load->model('countries/countries_m');
        $origin = base_url();
        $origin = str_replace("/api", "", $origin);        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET,POST,OPTIONS,DELETE,PUT');
        header('Access-Control-Allow-Headers: Content-Type,Accept,Authorization');
        $this->request_headers = apache_request_headers();
        header("Content-type: application/json");          

        $params = array();
        $method = $_SERVER['REQUEST_METHOD'];
        $file = file_get_contents('php://input');
        if($this->token_user = $this->_verify_authentication($file)){
            if($file){
                $request = json_decode($file);
                if($request){
                    if(!is_dir('logs')){
                        mkdir("./logs",0777,TRUE);
                    }
                    file_put_contents("logs/api_request_logs-".date('d-m-Y').".dat","\n".date("d-M-Y h:i A")."\t".current_url()."\t".serialize(file_get_contents('php://input'))."\t Headers are:".serialize(apache_request_headers()). "\n",FILE_APPEND);
                    $this->data = isset($request->request->data)?$request->request->data:'';
                    $this->filter_params = isset($request->request->params)?$request->request->params:'';
                    if($this->data){
                        $user_id = isset($this->token_user->_id)?$this->token_user->_id:'';
                        $this->user = $user_id?$this->users_m->get($user_id):'';
                        if($this->user){
                            $access_token = "";
                            if(isset($this->request_headers['Authorization'])){
                               $access_token = $this->request_headers['Authorization']; 
                            }else{
                                $access_token = $this->request_headers['authorization'];
                            }
                            
                            $access_token = trim(str_replace("Bearer","",$access_token));
                            $access_token = trim(str_replace("Basic","",$access_token)); 
                            if(!$access_token){
                                $access_token = isset($this->request->accessToken) ? $this->request->accessToken :"";
                            }
                            if($this->user->access_token == $access_token ){
                                if($this->token_user->_id){
                                    //if(preg_match('/log_visit/', $_SERVER['REQUEST_URI'])){
                                        $action = array(
                                            'action'=>isset($this->activity_log_options[uri_string()])?$this->activity_log_options[uri_string()]['action']:'',
                                            'description'=>isset($this->activity_log_options[uri_string()])?$this->activity_log_options[uri_string()]['description']:'Via mobile app',
                                            'user_id'=> isset($this->token_user->_id)?$this->token_user->_id:'',
                                            'url'=>$_SERVER['REQUEST_URI']?:'',
                                            'request_method'=>$_SERVER['REQUEST_METHOD']?:'',
                                            'ip_address'=>$_SERVER['REMOTE_ADDR']?:'',
                                            'execution_time'=>"Process took ". number_format(microtime(true) - $time_start, 4). " seconds.",
                                            'created_on'=>time(),
                                        );
                                        $this->activity_log->log_action($action);
                                    //}
                                }
                            }else{
                                $response = array(
                                    'status' => FALSE,
                                    'description' => "Token invalid generate new token.",
                                );
                                echo json_encode($response);
                                die;
                            }
                        }
                    }else{
                        $response = array(
                            'status' => FALSE,
                            'description' => "Missing request: data file.",
                        );
                        echo json_encode($response);
                        die;
                    }
                }else{
                    $response = array(
                        'status' => FALSE,
                        'description' => "Invalid json file request sent.",
                    );
                    echo json_encode($response);
                    die;
                }
            }else{
                $response = array(
                    'status' => FALSE,
                    'description' => "Empty file sent.",
                );
                echo json_encode($response);
                die;
            }
        }else{
            echo json_encode(array(
                'status' => FALSE,
                'message' => 'Authentication token expired',
            ) );
            die;
        }
        $this->template->set_metadata('canonical', site_url($this->uri->uri_string()), 'link');
    }

    public function _remap($method, $params = array()){
       if(method_exists($this, $method)){
           return call_user_func_array(array($this, $method), $params);
       }
       $this->output->set_status_header('404');
       header('Content-Type: application/json');
       $file = file_get_contents('php://input')?(array)json_decode(file_get_contents('php://input')):array();
       $request = $_REQUEST+$file;
       echo json_encode(
        array(
            'status'    =>  FALSE,
            'description' => '404 Method Not Found for URI: '.$this->uri->uri_string(),
        ));
    }

    function _generate_access_token($user_id = 0 , $time = 0){
        if($user_id){
            if($time > 0){
                $time = $time;
            }else{
                $time = 86400;
            }
            $token = AUTHORIZATION::generateToken(['_id' => $user_id,'iat' => time(),'exp' => time() + $time,]);
            //$access_token = random_string('alnum', 48);
            if($token){
                $input = array(                    
                    'access_token' => $token,
                    'last_login'=> time(),
                    'modified_on' => time(),
                    'modified_by' => 1,
                );
                if($id = $this->users_m->update($user_id,$input)){
                    $input  = $input + array(
                        'user_id' => $user_id,
                    );
                   return (object)$input;
                }else{
                    return FALSE;
                }
            }else{
                return FALSE;
            }
        }else{
            return FALSE;
        }
    }

    function _verify_authentication($file = ''){
        if($this->_ignore_token_check()){
            return $this->token_key;
        }else{            
            if($this->request_headers){ 
                //print_r($this->request_headers); 
                if(isset($this->request_headers['Authorization']) || isset($this->request_headers['authorization']) ){
                    
                    if(isset($this->request_headers['Authorization'])){
                       $access_token = $this->request_headers['Authorization']; 
                    }else{
                        $access_token = $this->request_headers['authorization'];
                    }
                    $access_token = trim(str_replace("Bearer","",$access_token));
                    $access_token = trim(str_replace("Basic","",$access_token));               
                    try {
                        $data = AUTHORIZATION::validateToken($access_token);                                    
                        if ($data === false) {
                            echo json_encode(array(
                                'status' => 403,
                                'message' => 'Authorization token not valid',
                            ));
                            die;
                        }else {
                            $exp = isset($data->exp)?$data->exp:0;
                            if ($exp >= time()) {
                                return $data;
                            }else{
                                echo json_encode(array(
                                    'status' => 403,
                                    'message' => 'Authorization token expired. Login to proceed',
                                ));
                                die;
                            }
                        }
                    } catch (Exception $e) {
                        echo json_encode(array(
                            'status' => 403,
                            'message' => 'Authorization token expired. Login to proceed',
                        ));
                        die;
                    }     
                }else if($file){
                    $request = json_decode($file);
                    if($request){  
                        $this->request = isset($request->request)?$request->request:'';                        
                        if($this->request){
                            $access_token = isset($this->request->accessToken)?$this->request->accessToken:"";                            
                            if($access_token){           
                                try {
                                    $data = AUTHORIZATION::validateToken($access_token);                                    
                                    if ($data === false) {
                                        echo json_encode(array(
                                            'status' => 403,
                                            'message' => 'Authorization token not valid',
                                        ));
                                        die;
                                    }else {
                                        $exp = isset($data->exp)?$data->exp:0;
                                        if ($exp >= time()) {
                                            return $data;
                                        }else{
                                            echo json_encode(array(
                                                'status' => 403,
                                                'message' => 'Authorization token expired. Login to proceed',
                                            ));
                                            die;
                                        }
                                    }
                                } catch (Exception $e) {
                                    echo json_encode(array(
                                        'status' => 403,
                                        'message' => 'Authorization token expired. Login to proceed',
                                    ));
                                    die;
                                }
                            }else{
                                if(preg_match('/log_visit/', $_SERVER['REQUEST_URI'])){
                                    $action = array(
                                        'action'=>isset($this->activity_log_options[uri_string()])?$this->activity_log_options[uri_string()]['action']:'',
                                        'description'=>isset($this->activity_log_options[uri_string()])?$this->activity_log_options[uri_string()]['description']:'',
                                        'user_id'=> isset($this->token_user->_id)?$this->token_user->_id:'',
                                        'url'=>$_SERVER['REQUEST_URI']?:'',
                                        'request_method'=>$_SERVER['REQUEST_METHOD']?:'',
                                        'ip_address'=>$_SERVER['REMOTE_ADDR']?:'',
                                        'created_on'=>time(),
                                    );
                                    $this->activity_log->log_action($action);
                                    $response = array(
                                        'status' => TRUE,
                                        'description' => "Success",
                                    );
                                    echo json_encode($response);
                                    die; 
                                }else{
                                    $response = array(
                                        'status' => 403,
                                        'description' => "Access token is missing in JSON payload.",
                                    );
                                    echo json_encode($response);
                                    die; 
                                } 
                            }
                        }else{
                            $response = array(
                                'status' => FALSE,
                                'description' => "Missing request: data file.",
                            );
                            echo json_encode($response);
                            die;
                        }
                    }else{
                        $response = array(
                            'status' => FALSE,
                            'description' => "Invalid json file request sent.",
                        );
                        echo json_encode($response);
                        die;
                    }
                }else{
                    echo json_encode(array(
                        'status' => FALSE,
                        'message' => 'Your request authorization header is invalid',
                    ));
                    die;
                }
            }else{
                echo json_encode(array(
                    'status' => FALSE,
                    'message' => 'Your request authorization header is empty',
                ));
                die;
            }
        }
        return $result;
    }

    function _verify_token($token =''){
        if($this->_ignore_token_check()){
            return $this->token_key;
        }else{ 
            try {
                $data = AUTHORIZATION::validateToken($token);                                    
                if ($data === false) {
                    echo json_encode(array(
                        'status' => FALSE,
                        'message' => 'Authorization token not valid',
                    ));
                    die;
                }else {
                    $exp = isset($data->exp)?$data->exp:0;
                    if ($exp >= time()) {
                        return $data;
                    }else{
                        echo json_encode(array(
                            'status' => FALSE,
                            'message' => 'Authorization token expired. Login to proceed',
                        ));
                        die;
                    }
                }
            } catch (Exception $e) {
                echo json_encode(array(
                    'status' => FALSE,
                    'message' => 'Authorization token expired. Login to proceed',
                ));
                die;
            }
        }
    }

    function _ignore_token_check(){
        $uri_string = $this->uri->uri_string();

        $url_exceptions = array(
            'api/v1/auth/index',
            'api/v1/auth/login',
            'api/v1/auth/authenticate',
            'api/v1/auth/register',
            'api/v1/auth/forgot_password',
            'api/v1/auth/reset_password',
            'api/v1/auth/confirm_code',
            'api/v1/auth/roles',
            'api/v1/auth/verify_email',
            'api/v1/auth/verify_pin', 
            'api/v1/auth/resend_otp',
            'api/v1/auth/resend_verification', 
            'api/v1/auth/onboarding_get_basic_info',
            'api/v1/auth/onboarding_get_system_usage',
            'api/v1/auth/onboarding_get_payment_info',
            'api/v1/auth/onboarding_confirm',
            'api/v1/auth/get_content_options',
            'api/v1/auth/upload_avatar', 
            'api/v1/auth/onboarding_confirm_account_type',
            'api/v1/auth/onboarding_update_user',    
            'api/v1/content/content_user_pairings',
            'api/v1/auth/onboarding_delete_child', 
            'api/v1/auth/onboarding_fetch_payments', 
            'api/v1/auth/onboarding_update_payments',  
            'api/v1/auth/onboarding_confirm_details',
            'api/list_db_fields',
            'api/v1/users/delete_user',
            'api/v1/auth/social_auth',
            'api/v1/resources/get_all_resources',
            'api/v1/resources/get_resource_education_level_count',
            'api/v1/settings/system_settings',
            'api/v1/deposits/confirm_payment',
            'api/v1/deposits/validate_payment',
            'api/v1/deposits/stk_push_simulation',
            'api/v1/transactions/online_payment',  
            'api/v1/resources/resource_resource_type_count',
            'api/v1/resources/get_resources_by_resource_type',
            'api/v1/resources/get_resource_education_level_count',
            'api/v1/resources/get_public_resource_by_education_levels',
            'api/v1/resources/get_resource_count_per_subject', 
            'api/v1/resources/get_popular_resources',
            'api/v1/billing/get_default_billing_package',
            'api/v1/schools/get_schools_options', 
            'api/v1/resources/resource_fine_search',            
             
        );
        $result = FALSE;
        if(in_array(trim($uri_string), $url_exceptions)){
            $result = TRUE;
            $this->ignore_secret_key = TRUE;
        }
        return $result;
    }
}
