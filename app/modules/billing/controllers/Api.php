<?php if(!defined('BASEPATH')) exit('You are not allowed to view this script');
class Api extends Api_Controller{
 
    public $response = array(
        'result_code' => 0,
        'result_description' => 'Default Response'
    );

    function __construct(){
        parent::__construct();
        $this->load->model('billing_m');
        $this->load->model('invoices/invoices_m');
        $this->load->library('billing_manager');
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

    public function create_billing_package(){
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $validation_rules = array(
            array(
                'field' => 'name',
                'label' => 'Billing Package Name',
                'rules' => 'required|trim|callback__is_unique_package_name',
            ),
            array(
                'field' => 'billing_type_frequency',
                'label' => 'Billing Package Type',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'amount',
                'label' => 'Billing Package Amount',
                'rules' => 'required|trim|currency',
            ),
            
            array(
                'field' => 'currency',
                'label' => 'Billing package currency',
                'rules' => 'trim|required',
            ),
            array(
                'field' => 'trial_days',
                'label' => 'Billing package trial days',
                'rules' => 'trim|required',
            ),                
            array(
                'field' => 'enable_tax',
                'label' => 'Enable VAT Tax',
                'rules' => 'trim',
            ),
            array(
                'field' => 'percentage_tax',
                'label' => 'Percentage Tax',
                'rules' => 'trim',
            ),                
                    
        ); 
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            if($this->input->post('is_default')){
                if($result = $this->billing_m->void_if_default_exist()){
                }
            }            
            $data = array(
                'name' => $this->input->post('name'),
                'slug' => generate_slug($this->input->post('name')),
                'billing_type_frequency' => $this->input->post('billing_type_frequency'),
                'rate' => $this->input->post('rate'),
                'rate_on' => $this->input->post('rate_on'),
                'currency' => $this->input->post('currency'),
                'amount' => $this->input->post('amount'),           
                'enable_tax' => $this->input->post('enable_tax'),
                'percentage_tax' => $this->input->post('percentage_tax'),
                'trial_days'=> $this->input->post('trial_days'),
                'active' => 1,
                'is_default' => $this->input->post('is_default'),
                'created_by' => $this->user->id,
                'created_on' => time(),
            );
            if($this->billing_m->insert($data)){
                $response = array(
                    'status' => 1,
                    'message' => 'The billing package has been created successfully'
                );
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'The billing package could not be created try again'
                ); 
            }
        }else{
            $post = array();
            $form_errors = $this->form_validation->error_array();
            foreach ($form_errors as $key => $value) {
              $post[$key] = $value;
            }
            $response = array(
                'status' => 0,
                'message' => 'Form validation failed',
                'validation_errors' => $post,
            );
        } 
        echo json_encode($response);
    }

    public function billing_packages(){
        $packages = $this->billing_m->get_all();
        $count = 1;
        $currencies = $this->countries_m->get_currency_array();
        if($packages){
            $response = array();
            foreach ($packages as $key => $package):
                $currency_name = isset($currencies[$package->currency])?$currencies[$package->currency]->name.' ('.$currencies[$package->currency]->currency_code.')':'';
                $response[] = array(
                    '_id'=>$count++,
                    "id"=>$package->id,
                    'name' => $package->name,
                    'slug' => $package->slug,
                    'billing_type_frequency' =>intval($package->billing_type_frequency),
                    'currency' => intval($package->currency),
                    'currency_name'=>$currency_name,
                    'currency_code'=>isset($currencies[$package->currency])?$currencies[$package->currency]->currency_code:'',
                    'amount' => intval($package->amount),           
                    'enable_tax' => intval($package->enable_tax),
                    'percentage_tax' => intval($package->percentage_tax),
                    'trial_days'=> intval($package->trial_days),
                    'active' => intval($package->active),
                    'is_default' =>  intval($package->is_default),
                );
            endforeach;
            $_response = array(
                "totalCount"=>count($response),
                "items"=>$response
            );
        }else{
            $_response = array(
                "totalCount"=>0,
                "items"=>array()
            );  
        }
        echo json_encode($_response);
    }

    public function get_billing_package_by_id(){
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $package_id = $this->input->post('_id');
        $currencies = $this->countries_m->get_currency_array();
        if($package_id){
            $post = $this->billing_m->get($package_id);
            if($post){
                $currency_name = isset($currencies[$post->currency])?$currencies[$post->currency]->name.' ('.$currencies[$post->currency]->currency_code.')':'';
                $resource = (object) array(
                    'id'=> 1,
                    '_id'=> intval($post->id),
                    'name' => $post->name,
                    'slug' => $post->slug,
                    'billing_type_frequency' =>intval($post->billing_type_frequency),
                    'currency' => intval($post->currency),
                    'currency_name'=>$currency_name,
                    'currency_code'=>isset($currencies[$post->currency])?$currencies[$post->currency]->currency_code:'',
                    'amount' => intval($post->amount),           
                    'enable_tax' =>intval( $post->enable_tax),
                    'percentage_tax' => intval($post->percentage_tax),
                    'trial_days'=> intval($post->trial_days),
                    'active' => intval($post->active),
                    'is_default' =>  intval($post->is_default),
                );
                $response = array(
                    'status' => 1,
                    'message' =>"Success",
                    'data'=>$resource,
                );
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'Resource details is empty(var)',
                ); 
            }
        }else{
            $response = array(
                'status' => 0,
                'message' => 'Billing package id variable is not sent in JSON Payload',
            );
        }
        echo json_encode($response);
    }

    public function update_billing_package(){
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $validation_rules = array(
            array(
                'field' => '_id',
                'label' => 'Billing Package id',
                'rules' => 'required|trim|is_numeric',
            ),
            array(
                'field' => 'name',
                'label' => 'Billing Package Name',
                'rules' => 'required|trim|callback__is_unique_package_name',
            ),
            array(
                'field' => 'billing_type_frequency',
                'label' => 'Billing Package Type',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'amount',
                'label' => 'Billing Package Amount',
                'rules' => 'required|trim|currency',
            ),            
            array(
                'field' => 'currency',
                'label' => 'Billing package currency',
                'rules' => 'trim|required',
            ),
            array(
                'field' => 'trial_days',
                'label' => 'Billing package trial days',
                'rules' => 'trim|required',
            ),                
            array(
                'field' => 'enable_tax',
                'label' => 'Enable VAT Tax',
                'rules' => 'trim',
            ),
            array(
                'field' => 'percentage_tax',
                'label' => 'Percentage Tax',
                'rules' => 'trim',
            ),                
                    
        );
        $package_id = $this->input->post('_id');
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            if($post = $this->billing_m->get($package_id)){
                if($this->input->post('is_default')){
                    if($result = $this->billing_m->void_if_default_exist()){

                    }
                }            
                $data = array(
                    'name' => $this->input->post('name'),
                    'slug' => generate_slug($this->input->post('name')),
                    'billing_type_frequency' => $this->input->post('billing_type_frequency'),
                    'rate' => $this->input->post('rate'),
                    'rate_on' => $this->input->post('rate_on'),
                    'currency' => $this->input->post('currency'),
                    'amount' => $this->input->post('amount'),           
                    'enable_tax' => $this->input->post('enable_tax'),
                    'percentage_tax' => $this->input->post('percentage_tax'),
                    'trial_days'=> $this->input->post('trial_days'),
                    'active' => 1,
                    'is_default' =>  $this->input->post('is_default'),
                    'created_by' => $this->user->id,
                    'created_on' => time(),
                );
                if($this->billing_m->update($post->id,$data)){
                    $response = array(
                        'status' => 1,
                        'message' => 'The billing package has been updated successfully'
                    );
                }else{
                    $response = array(
                        'status' => 0,
                        'message' => 'The billing package could not be updated try again'
                    ); 
                }
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'Billing package details is missing',
                );
            }
        }else{
            $post = array();
            $form_errors = $this->form_validation->error_array();
            foreach ($form_errors as $key => $value) {
              $post[$key] = $value;
            }
            $response = array(
                'status' => 0,
                'message' => 'Form validation failed',
                'validation_errors' => $post,
            );
        } 
        echo json_encode($response);
    }

    public function delete_billing_package(){
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $package_id = $this->input->post('_id');
        if($package_id){
            if($post = $this->billing_m->get($package_id)){           
                $data = array(
                    'active' => 0,
                    'created_by' => $this->user->id,
                    'created_on' => time(),
                );
                if($this->billing_m->update($post->id,$data)){
                    $response = array(
                        'status' => 1,
                        'message' => 'The billing package has been deleted successfully'
                    );
                }else{
                    $response = array(
                        'status' => 0,
                        'message' => 'The billing package could not be deleted try again'
                    ); 
                }
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'Billing package details is missing',
                );
            }
        }else{
            $response = array(
                'status' => 0,
                'message' => 'Billing package id is required'
            );
        } 
        echo json_encode($response);
    }

    public function check_subscription_status(){
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        //if($response = $this->transactions->check_user_subscription_status($this->user)){ 
        $arrears = $this->invoices_m->get_user_account_arrears($this->user->id);
        if($arrears > 0){
            $response = array(
                'status'=>1,
                'is_subscribed' => 1,
                'on_trial' => 0,
                'has_arrears' => 1,
                'has_not_subscribed' => 0,
                'arrears' => number_to_currency($arrears),
            );
        }else{
            $plus_14_days = strtotime(" +14 days",$this->user->created_on);
            if($plus_14_days > time()){
                $response = array(
                    'status'=>1,
                    'is_subscribed' => 0,
                    'on_trial' => 1,
                    'has_arrears' => 0,
                    'has_not_subscribed' => 0,
                    'arrears' => 0,
                );
            }else{
                $last_invoice = $this->invoices_m->get_latest_invoice($this->user->id);
                $total_amount_paid = $this->deposits_m->get_user_total_deposits_amount($this->user->id);
                if($last_invoice){
                    $response = array(
                        'status'=>1,
                        'is_subscribed' => 1,
                        'on_trial' => 0,
                        'has_not_subscribed' => 0,
                        'has_arrears' => 0,
                        'arrears' => $arrears,
                    );
                }else{
                    $response = array(
                        'status'=>1,
                        'is_subscribed' => 0,
                        'on_trial' => 0,
                        'has_not_subscribed' => 1,
                        'has_arrears' => 0,
                        'arrears' => $arrears,
                    ); 
                }
            }
        }
        echo json_encode($response);
    }

    public function is_subscription_active(){
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        //if($response = $this->transactions->check_user_subscription_status($this->user)){ 
       // $arrears = $this->invoices_m->get_user_account_arrears($this->user->id);
        //print_r($this->user); die;
        if($this->user){
           if($this->user->arrears < 0){
                $response = array(
                    'status'=>1,
                    'is_subscribed' => 1,
                    'overpayment'=>abs($this->user->arrears),
                    'on_trial' => 0,
                    'has_arrears' =>0,
                    'arrears' => ceil($this->user->arrears),
                );
            }else{
                if($this->user->billing_date > time() && $this->user->subscription_status == 2){
                    $response = array(
                        'status'=>1,
                        'is_subscribed' =>1,
                        'overpayment'=>0,
                        'on_trial' => 1,
                        'has_arrears' =>1,
                        'arrears' => ceil($this->user->arrears),
                    );
                }else{
                    $response = array(
                        'status'=>1,
                        'is_subscribed' =>0,
                        'overpayment'=>0,
                        'on_trial' => 0,
                        'has_arrears' =>1,
                        'arrears' => ceil($this->user->arrears),
                    );
                }
            }
        }else{
            $response = array(
                'status' => 0,
                'message' => 'User details missing'
            );
        }
        echo json_encode($response);
    }

    public function get_subscription_status(){
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        if($this->user){    
            $last_invoice = $this->invoices_m->get_latest_invoice($this->user->id);
            if($this->user->billing_package_id){
                $package = $this->billing_m->get($this->user->billing_package_id);
            }else{
                $package = $this->billing_m->check_if_default();
            }
           
            //$package = $this->billing_m->check_if_default();
            if($this->user->arrears > 0){                
                $result = array(
                    'amount_payable' => ceil(currency($this->user->arrears)),
                    'remaining_days' => daysAgo($this->user->subscription_end_date),
                    'tax' => isset($last_invoice->tax)?$last_invoice->tax:'',
                    'currency'=>'KES',
                    'next_payment_date'=>$this->user->billing_date,
                    'invoice_id'=> isset($last_invoice->id)?$last_invoice->id:'',
                    'billing_package_id'=>$package->id,
                    'billing_package_name'=>$package->name,
                    'percentage_tax'=>$package->percentage_tax,
                    'is_subscribed'=>0,
                    'phone'=>$this->user->phone,
                    'arrears' => ceil($this->user->arrears),
                );
                $response = array(
                    'status' => 1,
                    'message' => 'Subscription details',
                    'data'=>$result
                );
            }else if($this->user->arrears <= 0){
                $tax = 0;
                if($package->enable_tax){
                    $percentage_tax = $package->percentage_tax;
                    if($percentage_tax){
                        $amount_paid = isset($package->amount)?$package->amount:0;
                        $tax = (($percentage_tax/100)*($amount_paid));
                        $tax = round($tax,2);
                    }else{
                        $tax = 0;
                    }
                }else{
                    $tax = 0;
                }
                if($package->id == 1){
                    $end_date = strtotime("+ 7 days", time());
                }else if($package->id == 2){
                    $end_date = strtotime("+ 30 days", time());
                }else if($package->id == 3){
                    $end_date = strtotime("+ 12 months", time());
                }else{
                    $end_date = strtotime("+ 7 days", time());
                }
                //print_r($package); die();
                $amount_payable = ceil($tax+$package->amount);
                $amount_remaining = 0;
                $result = array(
                    'amount_payable' => ceil($this->user->arrears?$this->user->arrears:$amount_payable),
                    'remaining_days' => daysAgo($this->user->subscription_end_date),
                    'tax' => isset($last_invoice->tax)?$last_invoice->tax:'',
                    'currency'=>'KES',
                    'next_payment_date'=>$this->user->billing_date,
                    'invoice_id'=> isset($last_invoice->id)?$last_invoice->id:'',
                    'billing_package_id'=>$package->id,
                    'billing_package_name'=>$package->name,
                    'percentage_tax'=>$package->percentage_tax,
                    'is_subscribed'=>1,
                    'phone'=>$this->user->phone,
                    'arrears' => ceil($this->user->arrears),
                    'default_billing_package_amount'=>$amount_payable,
                );
                $response = array(
                    'status' => 1,
                    'message' => 'Subscription details',
                    'data'=>$result
                );
            }else{
                $tax = 0;
                if($package->enable_tax){
                    $percentage_tax = $package->percentage_tax;
                    if($percentage_tax){
                        $amount_paid = isset($package->amount)?$package->amount:0;
                        $tax = (($percentage_tax/100)*($amount_paid));
                        $tax = round($tax,2);
                    }else{
                        $tax = 0;
                    }
                }else{
                    $tax = 0;
                }
                if($package->id == 1){
                    $end_date = strtotime("+ 7 days", time());
                }else if($package->id == 2){
                    $end_date = strtotime("+ 30 days", time());
                }else if($package->id == 3){
                    $end_date = strtotime("+ 12 months", time());
                }else{
                    $end_date = strtotime("+ 7 days", time());
                }
                $amount_payable = ceil($tax+$package->amount);
                $amount_remaining = 0;
                $result = array(
                    'amount_payable' =>ceil($this->user->arrears?$this->user->arrears:$amount_payable),
                    'remaining_days' => daysAgo($this->user->subscription_end_date),
                    'tax' => isset($last_invoice->tax)?$last_invoice->tax:'',
                    'currency'=>'KES',
                    'next_payment_date'=>$this->user->billing_date,
                    'invoice_id'=> isset($last_invoice->id)?$last_invoice->id:'',
                    'billing_package_id'=>$package->id,
                    'billing_package_name'=>$package->name,
                    'percentage_tax'=>$package->percentage_tax,
                    'is_subscribed'=>0,
                    'phone'=>$this->user->phone,
                    'arrears' => ceil($this->user->arrears),
                    'default_billing_package_amount'=>$amount_payable,
                );
                $response = array(
                    'status' => 1,
                    'message' => 'Subscription details',
                    'data'=>$result
                );
                //print_r($this->user); die();
                /*if($this->user->subscription_end_date > time()){
                    
                    $tax = 0;
                    if($package->enable_tax){
                        $percentage_tax = $package->percentage_tax;
                        if($percentage_tax){
                            $amount_paid = isset($last_invoice->amount_paid)?$last_invoice->amount_paid:0;
                            $tax = (($percentage_tax/100)*($amount_paid));
                            $tax = round($tax,2);
                        }else{
                            $tax = 0;
                        }
                    }else{
                        $tax = 0;
                    }
                    if($package->id == 1){
                        $end_date = strtotime("+ 7 days", time());
                    }else if($package->id == 2){
                        $end_date = strtotime("+ 30 days", time());
                    }else if($package->id == 3){
                        $end_date = strtotime("+ 12 months", time());
                    }else{
                        $end_date = strtotime("+ 7 days", time());
                    }
                    $amount_payable = ceil($tax+$package->amount);
                    $amount_remaining = 0;
                    $payment_dates = array();
                    if($this->user->arrears < 0){
                        //overpayment 
                        $absolute_arrears = abs($this->user->arrears);
                        if(abs($this->user->arrears) >= $amount_payable){
                            //can pay
                            $cycles = $absolute_arrears/$amount_payable;
                            if($cycles > 0){
                                $cast_cycle = ceil($cycles); //floor($cycles*100)/100; 
                                //die($cast_cycle);
                                if($package->id == 1){
                                    $days = 7 * (int)$cast_cycle; 
                                    $cycle_end_date = strtotime("+ ".$days." days", $this->user->subscription_end_date);
                                }else if($package->id == 2){
                                    //$cycle_end_date = strtotime("+ 30 days", time());
                                    $days = 30 *(int)$cast_cycle; 
                                    $cycle_end_date = strtotime("+ ".$days." days", $this->user->subscription_end_date);
                                }else if($package->id == 3){
                                    //$cycle_end_date = strtotime("+ 12 months", time());
                                    $days = 12 * (int)$cast_cycle; 
                                    $cycle_end_date = strtotime("+ ".$days." months", $this->user->subscription_end_date);
                                }else{
                                    //$cycle_end_date = strtotime("+ 7 days", time());
                                    $days = 7 *(int)$cast_cycle; 
                                    $cycle_end_date = strtotime("+ ".$days." days", $this->user->subscription_end_date);
                                }
                                $result = array(
                                    'is_subscribed'=>1,
                                    'billing_package_id'=>0,
                                    'phone'=>$this->user->phone,
                                    'invoice_id'=>isset($last_invoice->id)?$last_invoice->id:'',
                                    'billing_package_id'=>$package->id,
                                    'currency'=>'KES',
                                    'billing_package_name'=>$package->name,
                                    'percentage_tax'=>$package->percentage_tax, 
                                    'overpayment'=>abs($this->user->arrears),                  
                                    'arrears' => ceil(currency($this->user->arrears)),
                                    'next_payment_date'=>$cycle_end_date,
                                    'remaining_days' => daysAgo($cycle_end_date),
                                    'default_billing_package_amount'=>$amount_payable,
                                );
                                $response = array(
                                    'status' => 1,
                                    'message' => 'Subscription details',
                                    'data'=>$result
                                ); 
                            }else{
                                $cycle_end_date = strtotime("+ 7 days", $this->user->subscription_end_date);  
                                $result = array(
                                    'is_subscribed'=>1,
                                    'billing_package_id'=>0,
                                    'phone'=>$this->user->phone,
                                    'invoice_id'=>isset($last_invoice->id)?$last_invoice->id:'',
                                    'billing_package_id'=>$package->id,
                                    'currency'=>'KES',
                                    'overpayment'=>abs($this->user->arrears), 
                                    'billing_package_name'=>$package->name,
                                    'percentage_tax'=>$package->percentage_tax,                   
                                    'arrears' => ceil(currency($this->user->arrears)),
                                    'next_payment_date'=>$cycle_end_date,
                                    'remaining_days' => daysAgo($cycle_end_date),
                                    'default_billing_package_amount'=>$amount_payable,
                                );
                                $response = array(
                                    'status' => 1,
                                    'message' => 'Subscription details',
                                    'data'=>$result
                                );
                            }                           
                        }else{
                            $cycle_end_date = $this->user->subscription_end_date;
                            $result = array(
                                'is_subscribed'=>1,
                                'billing_package_id'=>0,
                                'phone'=>$this->user->phone,
                                'invoice_id'=>isset($last_invoice->id)?$last_invoice->id:'',
                                'billing_package_id'=>$package->id,
                                'currency'=>'KES',
                                'overpayment'=>abs($this->user->arrears),
                                'billing_package_name'=>$package->name,
                                'percentage_tax'=>$package->percentage_tax,                   
                                'arrears' => ceil(currency($this->user->arrears)),
                                'next_payment_date'=>$cycle_end_date,
                                'remaining_days' => daysAgo($cycle_end_date),
                                'default_billing_package_amount'=>$amount_payable,
                            );
                            $response = array(
                                'status' => 1,
                                'message' => 'Subscription details',
                                'data'=>$result
                            );
                        }                        
                        //print_r($amount_payable); 
                        //print_r(abs($this->user->arrears)); die();
                    }else{
                        $result = array(
                            'is_subscribed'=>1,
                            'billing_package_id'=>0,
                            'phone'=>$this->user->phone,
                            'invoice_id'=>isset($last_invoice->id)?$last_invoice->id:'',
                            'billing_package_id'=>$package->id,
                            'currency'=>'KES',
                            'billing_package_name'=>$package->name,
                            'percentage_tax'=>$package->percentage_tax,                   
                            'arrears' => ceil(currency($this->user->arrears)),
                            'next_payment_date'=>$this->user->subscription_end_date,
                            'remaining_days1' => daysAgo($this->user->subscription_end_date),
                            'default_billing_package_amount'=>$amount_payable,
                        );
                        $response = array(
                            'status' => 1,
                            'message' => 'Subscription details',
                            'data'=>$result
                        );
                    }
                }else{
                    if($this->billing_manager->_generate_user_billing_invoice($this->user->id)){
                        $user_new = $this->users_m->get($this->user->id);
                        $last_invoice = $this->invoices_m->get_latest_invoice($user_new->id);
                        $result = array(
                            'amount_payable' => ceil(currency($user_new->arrears)),
                            'remaining_days' => daysAgo($user_new->subscription_end_date),
                            'tax' => $last_invoice->tax,
                            'currency'=>'KES',
                            'next_payment_date'=>$user_new->subscription_end_date,
                            'invoice_id'=>isset($last_invoice->id)?$last_invoice->id:'',
                            'billing_package_id'=>$package->id,
                            'billing_package_name'=>$package->name,
                            'percentage_tax'=>$package->percentage_tax,
                            'is_subscribed'=>0,
                            'phone'=>$this->user->phone,
                            'arrears' => ceil($user_new->arrears),
                        );
                        $response = array(
                            'status' => 1,
                            'message' => 'Subscription details',
                            'data'=>$result
                        );
                    }else{
                        $result = array(
                            'is_subscribed'=>0,
                            'billing_package_id'=>0,
                            'phone'=>$this->user->phone,                  
                            'arrears' => ceil(currency($this->user->arrears)),
                            'next_payment_date'=>$this->user->subscription_end_date,
                            'remaining_days' => daysAgo($this->user->subscription_end_date),
                        );
                        $response = array(
                            'status' => 1,
                            'message' => 'Subscription details',
                            'data'=>$result
                        );
                    }
                }*/
            }
        }else{
            $response = array(
                'status' => 0,
                'message' => 'User details missing'
            );
        } 
        echo json_encode($response);
    }

    public function get_default_billing_package(){
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $package = $this->billing_m->get_default_packages();
        $count = 1;
        $currencies = $this->countries_m->get_currency_array();
        if($package){
            $currency_name = isset($currencies[$package->currency])?$currencies[$package->currency]->name.' ('.$currencies[$package->currency]->currency_code.')':'';
            $response = array(
                '_id'=>$count++,
                "id"=>$package->id,
                'name' => $package->name,
                'slug' => $package->slug,
                'billing_type_frequency' =>intval($package->billing_type_frequency),
                'currency' => intval($package->currency),
                'currency_name'=>$currency_name,
                'currency_code'=>isset($currencies[$package->currency])?$currencies[$package->currency]->currency_code:'',
                'amount' => intval($package->amount),           
                'enable_tax' => intval($package->enable_tax),
                'percentage_tax' => intval($package->percentage_tax),
                'trial_days'=> intval($package->trial_days),
                'active' => intval($package->active),
                'is_default' =>  intval($package->is_default),
            );
            $_response = array(
                "totalCount"=>count($response),
                "items"=>$response
            );
        }else{
            $_response = array(
                "totalCount"=>0,
                "items"=>array()
            );  
        }
        echo json_encode($response);
    }



    public function _is_unique_package_name(){
        $slug = generate_slug($this->input->post('name'));
        $user_id = $this->token_user->_id;
        $name = $this->input->post('name');
        $id = $this->input->post('_id');
        if($package = $this->billing_m->get_package_by_slug($slug)){
            if($package->id == $id){
                return TRUE;
            }else{
                $this->form_validation->set_message('_is_unique_package_name','The billing package '.$name.' already exists.');
                return FALSE;
            }
        }else{
            return TRUE;
        }
    }   

}