<?php if(!defined('BASEPATH')) exit('You are not allowed to view this script');
class Api extends Api_Controller{

    public $response = array(
        'result_code' => 0,
        'result_description' => 'Default Response'
    );

    


    function __construct(){
        parent::__construct();
        $this->load->model('deposits_m');
        $this->load->model('invoices/invoices_m');
        $this->load->model('billing/billing_m');
        $this->load->model('safaricom/safaricom_m');
        $this->load->library('mpesa');
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

    public function subscribe(){
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        } 
        $billing_package_id = $this->input->post('billing_package_id');
        if($this->user->id){
            $package = array(
                'billing_package_id'=>$billing_package_id,
                'user'=>$this->user
            );
            if($this->transactions->subscribe($package)){
                $response = array(
                    'status' => 1,
                    'message' => 'Sucessfully subscribed: '.$this->session->warning
                ); 
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'Could not subscribe to that resource: '.$this->session->warning
                ); 
            }
        }else{
            $response = array(
                'status' => 0,
                'message' => 'User details missing'
            );
        } 
        echo json_encode($response);
    }

    function initiate_payment(){
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
                'field' =>  'invoice_id',
                'label' =>  'Invoice Id',
                'rules' =>  'trim',
            ),
            array(
                'field' =>  'amount',
                'label' =>  'Amount',
                'rules' =>  'trim|required',
            ),
            array(
                'field' =>  'phone',
                'label' =>  'Phone number',
                'rules' =>  'trim|required',
            ),
        );
        $this->form_validation->set_rules($validation_rules);
        if($this->form_validation->run()){
            $invoice_id = $this->input->post('invoice_id');
            $amount = $this->input->post('amount');
            $description = $this->input->post('description');
            $phone = $this->input->post('phone')?$this->input->post('phone'):$this->user->phone;
            if(valid_phone($phone)){
                if($amount){
                	if(TRUE){
                    //if($invoice = $this->invoices_m->get($invoice_id)){
                        $transactions = new StdClass();
                        $transactions->amount = $amount;
                        $transactions->invoice_id = $invoice_id;
                        $transactions->description = $description;
                        $transactions->phone = valid_phone($phone);
                        if($result = $this->transactions->make_online_payment($this->user,$transactions,1,$phone)){
                            if($result){
                                $response = array(
                                    'status' => 1,
                                    'message' => 'Payment in progress. Please wait to enter pin',
                                    'result'=>json_decode($result)
                                );
                            }else{
                                $response = array(
                                    'status' => 0,
                                    "message" => $result,
                                );
                            }
                        }else{
                            $response = array(
                                'status' => 0,
                                'message' => "Server error: ".$this->session->flashdata('message'),
                            );
                        }
                    }else{
                        $response = array(
                            'status' => 0,
                            'message' => "Invoice details missing",
                        );   
                    }
                }else{
                    $response = array(
                        'status' => 0,
                        'message' => "Amount must be greater than zero",
                    );
                }
            }else{
                $response = array(
                    'status' => 0,
                    'message' => "Kindly enter a valid phone number to make payment",
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
                'time' => time(),
            );
        }
        echo json_encode($response);
    }

    function generate_token(){
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        } 
        if($this->user->id){
            $particulars = array(
                'short_code'=>$this->input->post('ShortCode'),
                'response_type'=>$this->input->post('ResponseType'),
                'confirm_url'=>$this->input->post('ConfirmationURL'),
                'validate_url'=>$this->input->post('ValidationURL')
            );
            $response = array(
                'status' => 1,
                'endpoint' => $this->mpesa->generate_token($particulars)
            );
        }else{
            $response = array(
                'status' => 0,
                'message' => 'User details missing'
            );
        } 
        echo json_encode($response);
    }

    function mpesa_test(){
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        } 
        if($this->user->id){
            $particulars = array(
                'short_code'=>$this->input->post('ShortCode'),
                'response_type'=>$this->input->post('ResponseType'),
                'confirm_url'=>$this->input->post('ConfirmationURL'),
                'validate_url'=>$this->input->post('ValidationURL')
            );
            $response = array(
                'status' => 1,
                'endpoint' => $this->mpesa->register_url($particulars)
            );
        }else{
            $response = array(
                'status' => 0,
                'message' => 'User details missing'
            );
        } 
        echo json_encode($response);
    }

    function c2b(){
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        } 
        if($this->user->id){
            $particulars = array(
                'short_code'=>$this->input->post('ShortCode'),
                'Msisdn'=>$this->input->post('phone'),
                'CommandID'=>$this->input->post('command_id'),
                'Amount'=>$this->input->post('amount'),
                'BillRefNumber'=>$this->input->post('bill_ref_number'),
            );
            
            $response = array(
                'status' => 1,
                'endpoint' => $this->mpesa->c2b($particulars)
            );
        }else{
            $response = array(
                'status' => 0,
                'message' => 'User details missing'
            );
        } 
        echo json_encode($response);
    }

    function stk_push(){
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        } 
        if($this->user->id){
            $particulars = array(
                'BusinessShortCode'=>$this->input->post('BusinessShortCode'),
                'PhoneNumber'=>$this->input->post('PhoneNumber'),
                'TransactionType'=>$this->input->post('TransactionType'),
                'Amount'=>$this->input->post('Amount'),
                'PartyA'=>$this->input->post('PartyA'),
                'PartyB'=>$this->input->post('PartyB'),
                'CallBackURL'=>$this->input->post('CallBackURL'),
                'AccountReference'=>$this->input->post('AccountReference'),
                'TransactionDesc'=>$this->input->post('TransactionDesc')
            );
            
            $response = array(
                'status' => 1,
                'endpoint' => $this->mpesa->stk_push_simulation($particulars)
            );
        }else{
            $response = array(
                'status' => 0,
                'message' => 'User details missing'
            );
        } 
        echo json_encode($response);
    }

    public function validate_payment(){
        $callbackJSONData = file_get_contents('php://input');
        $callbackData = json_decode($callbackJSONData);
        $transactionType = $callbackData->TransactionType;
        $transID = $callbackData->TransID;
        $transTime = $callbackData->TransTime;
        $transAmount = $callbackData->TransAmount;
        $businessShortCode = $callbackData->BusinessShortCode;
        $billRefNumber = $callbackData->BillRefNumber;
        $invoiceNumber = $callbackData->InvoiceNumber;
        $orgAccountBalance = $callbackData->OrgAccountBalance;
        $thirdPartyTransID = $callbackData->ThirdPartyTransID;
        $MSISDN = $callbackData->MSISDN;
        $firstName = $callbackData->FirstName;
        $middleName = $callbackData->MiddleName;
        $lastName = $callbackData->LastName;

        $result = [
            "transTime" => $transTime,
            "transAmount" => $transAmount,
            "businessShortCode" => $businessShortCode,
            "billRefNumber" => $billRefNumber,
            "invoiceNumber" => $invoiceNumber,
            "orgAccountBalance" => $orgAccountBalance,
            "thirdPartyTransID" => $thirdPartyTransID,
            "MSISDN" => $MSISDN,
            "firstName" => $firstName,
            "lastName" => $lastName,
            "middleName" => $middleName,
            "transID" => $transID,
            "transactionType" => $transactionType,

        ];

        return json_encode($result);
    }

    public function confirm_payment(){
        $callbackJSONData = file_get_contents('php://input');
        $callbackData = json_decode($callbackJSONData);
        $transactionType = $callbackData->TransactionType;
        $transID = $callbackData->TransID;
        $transTime = $callbackData->TransTime;
        $transAmount = $callbackData->TransAmount;
        $businessShortCode = $callbackData->BusinessShortCode;
        $billRefNumber = $callbackData->BillRefNumber;
        $invoiceNumber = $callbackData->InvoiceNumber;
        $orgAccountBalance = $callbackData->OrgAccountBalance;
        $thirdPartyTransID = $callbackData->ThirdPartyTransID;
        $MSISDN = $callbackData->MSISDN;
        $firstName = $callbackData->FirstName;
        $middleName = $callbackData->MiddleName;
        $lastName = $callbackData->LastName;

        $result = [
            "transTime" => $transTime,
            "transAmount" => $transAmount,
            "businessShortCode" => $businessShortCode,
            "billRefNumber" => $billRefNumber,
            "invoiceNumber" => $invoiceNumber,
            "orgAccountBalance" => $orgAccountBalance,
            "thirdPartyTraMSISDNnsID" => $thirdPartyTransID,
            "MSISDN" => $MSISDN,
            "firstName" => $firstName,
            "lastName" => $lastName,
            "middleName" => $middleName,
            "transID" => $transID,
            "transactionType" => $transactionType,

        ];
        return json_encode($result);
    }

    public function process_stkpush_cequest_callback() {
        $callbackJSONData = file_get_contents('php://input');
        $callbackData = json_decode($callbackJSONData);
        $resultCode = $callbackData->Body->stkCallback->ResultCode;
        $resultDesc = $callbackData->Body->stkCallback->ResultDesc;
        $merchantRequestID = $callbackData->Body->stkCallback->MerchantRequestID;
        $checkoutRequestID = $callbackData->Body->stkCallback->CheckoutRequestID;

        $callbackMeta = $callbackData->Body->stkCallback->CallbackMetadata->Item;

        $amount = $callbackMeta[0]->Value;
        $mpesaReceiptNumber = $callbackMeta[1]->Value;
        $balance = $callbackMeta[2]->Name;
        $transactionDate = $callbackMeta[3]->Value;
        $phoneNumber = $callbackMeta[4]->Value;

        $result = [
            "resultDesc" => $resultDesc,
            "resultCode" => $resultCode,
            "merchantRequestID" => $merchantRequestID,
            "checkoutRequestID" => $checkoutRequestID,
            "amount" => $amount,
            "mpesaReceiptNumber" => $mpesaReceiptNumber,
            "balance" => $balance,
            "transactionDate" => $transactionDate,
            "phoneNumber" => $phoneNumber,
        ];

        return json_encode($result);
    }

    public function get_my_recent_payments(){
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        if($this->user){ 
            $page_number = isset($this->data->page)?$this->data->page:0;
            $page_size = 5;
            $start = 0;
            $end = 5;
            $start = $page_number * $page_size;
            $end = $start + $page_size;
            $total_rows = $this->deposits_m->count_my_active_deposits($this->user->id);
            $pagination = create_custom_pagination('api',$total_rows,$page_size,$start,TRUE);
            $posts = $this->deposits_m->limit($pagination['limit'])->get_my_active_deposits($this->user->id);
            if($posts){
                $deposits_array = array();
                $count = $start+1;
                foreach ($posts as $key => $post):
                    if($post->deposit_method == 1){
                        $method = "M-PESA";
                    }else{
                        $method = "M-PESA";
                    }
                    $deposits_array[] = array(
                        'id'=>$count++,
                        '_id'=>$post->id,
                        'deposit_date'=>$post->deposit_date,
                        'description'=>"Subscription Payment",
                        'amount'=> number_to_currency($post->amount),
                        'deposit_method'=>$post->deposit_method,
                        "method"=> $method,
                        'transaction_id'=>$post->transaction_id,
                        'phone'=>$post->phone,
                    );
                endforeach;
                $response = array(
                    'itemsCount' => $total_rows,
                    'items' => $deposits_array,
                );
            }else{
                $response = array(
                    'itemsCount' => $total_rows,
                    'items' => array(),
                ); 
            }
        }else{
            $response = array(
                'status' => 0,
                'message' => 'User details missing'
            );
        } 
        echo json_encode($response);
    }

    public function check_if_payment_success(){
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        if($this->user){
            $CheckoutRequestID = $this->data->CheckoutRequestID;
            $MerchantRequestID = $this->data->MerchantRequestID;
            if($CheckoutRequestID && $MerchantRequestID){
                if($request = $this->safaricom_m->get_stk_request_by_merchant_request_id_and_checkout_request_id($CheckoutRequestID,$MerchantRequestID)){
                    if($request->result_code === 0){
                        $response = array(
                            'status' => 1,
                            'message' => 'Sucessfully Reconciled'
                        );
                    }else{
                        $response = array(
                            'status' => 2,
                            'message' => 'Waiting for callback response'
                        );   
                    }
                }else{
                    $response = array(
                        'status' => 0,
                        'message' => 'Request not Sucessfull'
                    );   
                }
            }else{
                $response = array(
                    'status' => 0,
                    'message' => 'CheckoutRequestID and  MerchantRequestID is required'
                );  
            }
        }else{
            $response = array(
                'status' => 0,
                'message' => 'User details missing'
            );
        } 
        echo json_encode($response);
    }

    public function get_transactions(){
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }

        $field_name = '';
        $sort_order = '';
        $sort_field = '';
        $search_field = '';
        $filter_type = '';
        $filter_request_status = '';
        $from = strtotime("first day of last month");
        $to = time();
        $page_number = 0;
        $page_size = 10;
        $start = 0;
        $end = 10;
        $filter_parameters = array();
        if (isset($this->filter_params)) {
            $update = $this->filter_params;
            if(isset($update->filter)){
                $search_field = $update->filter;
            }          
            if(isset($update->sortOrder)){                    
                $sort_order = $update->sortOrder;
            }
            if(isset($update->sortField)){
                $sort_field = $update->sortField;
            }

            if(isset($update->pageNumber)){
                $page_number = $update->pageNumber;
            }
            if(isset($update->pageSize)){
                $page_size = $update->pageSize;
            }
            if(isset($update->options)){
                $from = isset($update->options->from)?$update->options->from:strtotime("first day of last month");
                $to = isset($update->options->to)?$update->options->to:time();
            }
            $start = $page_number * $page_size;
            $end = $start + $page_size;
            
            
            $filter_parameters = array(
                "search_fields"=>$search_field,
                "sort_order"=>$sort_order,
                "sort_field"=>$sort_field,
                "filter_request_status"=>$filter_request_status,
                'from'=>$from?$from: strtotime("first day of last month"),
                'to'=>$to?$to:time()
            );      
        } 
        //print_r($filter_parameters);die();
        $total_rows = $this->deposits_m->count_active_deposits($filter_parameters);
        $pagination = create_custom_pagination('api',$total_rows,$page_size,$start,TRUE);
        $posts = $this->deposits_m->limit($pagination['limit'])->get_active_deposits($filter_parameters);
        if($posts){
            $deposits_array = array();
            $count = $start+1;
            foreach ($posts as $key => $post):
                if($post->deposit_method == 1){
                    $method = "M-PESA";
                }else{
                    $method = "M-PESA";
                }
                $deposits_array[] = array(
                    'id'=>$count++,
                    '_id'=>$post->id,
                    'deposit_date'=>$post->deposit_date,
                    'description'=>"Subscription Payment",
                    'amount'=> number_to_currency($post->amount),
                    'deposit_method'=>$post->deposit_method,
                    "method"=> $method,
                    'transaction_id'=>$post->transaction_id,
                    'phone'=>$post->phone,
                    'currency'=>'KES'
                );
            endforeach;
            $response = array(
                'totalCount' => $total_rows,
                'items' => $deposits_array,
            );
        }else{
            $response = array(
                'totalCount' => $total_rows,
                'items' => array(),
            ); 
        }
        echo json_encode($response);
    }

    public function get_safaricom_stk_requests(){
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $field_name = '';
        $sort_order = '';
        $sort_field = '';
        $search_field = '';
        $filter_type = '';
        $filter_request_status = '';
        $from = strtotime("first day of last month");
        $to = time();
        $page_number = 0;
        $page_size = 10;
        $start = 0;
        $end = 10;
        $filter_parameters = array();
        if (isset($this->filter_params)) {
            $update = $this->filter_params;
            if(isset($update->filter)){
                $search_field = $update->filter;
            }          
            if(isset($update->sortOrder)){                    
                $sort_order = $update->sortOrder;
            }
            if(isset($update->sortField)){
                $sort_field = $update->sortField;
            }

            if(isset($update->pageNumber)){
                $page_number = $update->pageNumber;
            }
            if(isset($update->pageSize)){
                $page_size = $update->pageSize;
            }
            if(isset($update->options)){
                $from = isset($update->options->from)?$update->options->from:$from;
               // print_r($from); die();
                $to = isset($update->options->to)?$update->options->to:time();
            }
            $start = $page_number * $page_size;
            $end = $start + $page_size;
            
            $filter_parameters = array(
                "search_fields"=>$search_field,
                "sort_order"=>$sort_order,
                "sort_field"=>$sort_field,
                "filter_request_status"=>$filter_request_status,
                'from'=>$from?$from: strtotime("first day of last month"),
                'to'=>$to?$to:time()
            );      
        } 
        $total_rows = $this->safaricom_m->count_all_stk_push_requests($filter_parameters);
      //  print_r($filter_parameters); die();
        $pagination = create_custom_pagination('api',$total_rows,$page_size,$start,TRUE);
        $posts = $this->safaricom_m->limit($pagination['limit'])->get_all_stk_push_requests($filter_parameters);
        if($posts){
            $deposits_array = array();
            $count = $start+1;
            foreach ($posts as $key => $post):
                $deposits_array[] = array(
                    'id'=>$count++,
                    '_id'=>$post->id,
                    'shortcode'=> $post->shortcode,
                    'phone'=> $post->phone,
                    'request_id'=> $post->request_id,
                    'amount'=> $post->amount,
                    'request_callback_url'=> $post->request_callback_url,
                    'response_code'=> $post->response_code,
                    'response_description'=> $post->response_description,
                    'checkout_request_id'=> $post->checkout_request_id,
                    'customer_message'=> $post->customer_message,
                    'result_code'=> $post->result_code,
                    'result_description'=> $post->result_description,
                    'created_on'=> $post->created_on,
                    'modified_on'=> $post->modified_on,
                    'modified_on'=> $post->modified_on,
                    'reference_number'=> $post->reference_number,
                    'merchant_request_id'=> $post->merchant_request_id,
                    'transaction_id'=> $post->transaction_id,
                    'organization_balance'=> $post->organization_balance,
                    'transaction_date'=> $post->transaction_date,
                    'request_reconcilled'=>$post->request_reconcilled, 
                    'currency'=>'KES'               
                );
            endforeach;
            $response = array(
                'totalCount' => $total_rows,
                'items' => $deposits_array,
            );
        }else{
            $response = array(
                'totalCount' => $total_rows,
                'items' => array(),
            ); 
        }
        echo json_encode($response);
    }

    
}